<?php

// phpcs:ignoreFile -- 框架类

/*********************************************************************************
 *  扩展类库 - word生成类
 *  功能：基于 JSON 布局数据生成，支持同行元素自动并排
 ***********************************************************************************/
namespace xphp;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class Xword
{
    protected PhpWord $phpWord;
    protected $section;
    protected int $pageNumber = 1;

    // A4页面物理尺寸（px，96DPI）
    protected int $pageWidthPx = 794;
    protected int $pageHeightPx = 1123;

    // 页边距（px）
    protected int $marginLeftPx = 75;
    protected int $marginTopPx = 75;
    protected int $marginRightPx = 75;
    protected int $marginBottomPx = 75;

    // 可用区域（px）
    protected int $usableWidthPx;
    protected int $usableHeightPx;

    // Word内部单位（twips）
    protected int $pageWidthTwips = 11906;
    protected int $pageHeightTwips = 16838;
    protected int $marginLeftTwips = 1134;
    protected int $marginTopTwips = 1134;

    const PX_TO_TWIPS = 15;  // 1px = 15 twips

    private int $currentTopPx = 0;
    private array $rowElements = []; // 存储同一行的元素

    public function __construct()
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->setDefaultFontName('宋体');
        $this->phpWord->setDefaultFontSize(12);

        // 计算可用区域
        $this->usableWidthPx = $this->pageWidthPx - $this->marginLeftPx - $this->marginRightPx;
        $this->usableHeightPx = $this->pageHeightPx - $this->marginTopPx - $this->marginBottomPx;

        // 创建第一页
        $this->createNewPage();
    }

    /**
     * 创建新页面
     */
    protected function createNewPage(): void
    {
        $this->section = $this->phpWord->addSection([
            'pageSizeW' => $this->pageWidthTwips,
            'pageSizeH' => $this->pageHeightTwips,
            'marginLeft' => $this->marginLeftTwips,
            'marginTop' => $this->marginTopTwips,
            'marginRight' => $this->marginLeftTwips,
            'marginBottom' => $this->marginTopTwips,
        ]);

        $this->currentTopPx = 0;
        $this->rowElements = [];
    }

    public function render(array $elements): void
    {
        // 按top排序
        usort($elements, fn($a, $b) => ($a['top'] ?? 0) <=> ($b['top'] ?? 0));

        // 按行分组处理
        $groupedByTop = [];
        foreach ($elements as $element) {
            $top = (int)($element['top'] ?? 0);
            $groupedByTop[$top][] = $element;
        }

        // 处理每一行
        foreach ($groupedByTop as $top => $rowElements) {
            $this->renderRow($top, $rowElements);
        }
    }

    /**
     * 渲染一行中的多个元素
     */
    protected function renderRow(int $rowTopPx, array $elements): void
    {
        // 计算元素应该在哪一页
        $targetPage = floor($rowTopPx / $this->usableHeightPx) + 1;
        $pageTopPx = $rowTopPx % $this->usableHeightPx;

        // 如果目标页大于当前页，切换到目标页
        while ($this->pageNumber < $targetPage) {
            $this->pageNumber++;
            $this->createNewPage();
        }

        // 垂直定位到行起始位置
        if ($pageTopPx > $this->currentTopPx) {
            $spaceNeeded = $pageTopPx - $this->currentTopPx;
            $this->section->addText(
                '',
                ['name' => '宋体', 'size' => 1],
                [
                    'spaceBefore' => $spaceNeeded * self::PX_TO_TWIPS,
                    'spaceAfter' => 0,
                    'lineHeight' => 0.1
                ]
            );
        }

        // 找出这一行的最大高度
        $rowHeight = 0;
        foreach ($elements as $element) {
            $height = (int)($element['height'] ?? 40);
            $rowHeight = max($rowHeight, $height);
        }

        // 按left排序
        usort($elements, fn($a, $b) => ($a['left'] ?? 0) <=> ($b['left'] ?? 0));

        // 计算这一行实际需要的总宽度（最后一个元素的left+width）
        $lastElement = end($elements);
        $totalWidthPx = ($lastElement['left'] ?? 0) + ($lastElement['width'] ?? 200);
        // 确保不超过可用宽度
        $totalWidthPx = min($totalWidthPx, $this->usableWidthPx);

        // 检查这一行是否有元素需要边框
        $hasBorder = false;
        $borderSize = 0;
        $borderColor = '';
        foreach ($elements as $element) {
            if ($this->needsBorder($element)) {
                $hasBorder = true;
                $borderSize = max($borderSize, $element['border_size'] ?? 1);
                $borderColor = $element['border_color'] ?? '000000';
            }
        }

        // 创建行容器 - 宽度为实际需要的宽度
        $tableOptions = [
            'cellMargin' => 0,
            'width' => $totalWidthPx * self::PX_TO_TWIPS,  // 改为实际宽度
            'unit' => TblWidth::TWIP,
        ];

        $cellOptions = [];
        if ($hasBorder) {
            $tableOptions['borderSize'] = $borderSize;
            $tableOptions['borderColor'] = $borderColor;
            $cellOptions = [
                'borderSize' => $borderSize,
                'borderColor' => $borderColor,
            ];
        }

        $table = $this->section->addTable($tableOptions);
        $table->addRow($rowHeight * self::PX_TO_TWIPS);

        $currentLeft = 0;

        // 渲染行内的每个元素
        foreach ($elements as $element) {
            $leftPx = (int)($element['left'] ?? 0);
            $widthPx = (int)($element['width'] ?? 200);

            // 如果当前元素与上一个元素之间有间隙，添加空白单元格
            if ($leftPx > $currentLeft) {
                $gapWidth = ($leftPx - $currentLeft) * self::PX_TO_TWIPS;
                $gapCell = $table->addCell($gapWidth, $cellOptions);
                $gapCell->addText('', ['name' => '宋体', 'size' => 1]);
            }

            // 渲染当前元素
            if ($element['type'] === 'text') {
                $this->addCellText($table, $element, $widthPx, $rowHeight, $hasBorder, $borderSize, $borderColor);
            } elseif ($element['type'] === 'image') {
                $this->addCellImage($table, $element, $widthPx, $rowHeight, $hasBorder, $borderSize, $borderColor);
            }

            $currentLeft = $leftPx + $widthPx;
        }

        // 更新当前位置
        $this->currentTopPx = $pageTopPx + $rowHeight;
    }

    /**
     * 在单元格中添加文本
     */
    protected function addCellText($table, array $config, int $widthPx, int $rowHeight, bool $hasBorder, int $borderSize, string $borderColor): void
    {
        $text = $config['value'] ?? '';
        $fontSize = (int)($config['font_size'] ?? 12);
        $color = $config['color'] ?? '000000';
        $bold = ($config['bold'] ?? false);
        $bgColor = $config['bg_color'] ?? null;
        $align = $config['text_align'] ?? 'left';

        $cellOptions = [
            'vAlign' => 'center',
            'valign' => 'center',
        ];

        // 只有行有边框时，单元格才继承边框
        if ($hasBorder) {
            $cellOptions['borderSize'] = $borderSize;
            $cellOptions['borderColor'] = $borderColor;
        }

        if ($bgColor) {
            $cellOptions['bgColor'] = $bgColor;
        }

        $cell = $table->addCell($widthPx * self::PX_TO_TWIPS, $cellOptions);

        $fontStyle = [
            'name' => '宋体',
            'size' => $fontSize,
            'color' => $color,
            'bold' => $bold,
        ];

        $paragraphStyle = [
            'alignment' => $this->getAlignment($align),
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'lineHeight' => 1.0,
        ];

        $cell->addText($text, $fontStyle, $paragraphStyle);
    }

    /**
     * 在单元格中添加图片
     */
    protected function addCellImage($table, array $config, int $widthPx, int $rowHeight, bool $hasBorder, int $borderSize, string $borderColor): void
    {
        $path = $config['value'] ?? '';
        if (!file_exists($path)) {
            error_log("图片不存在: {$path}");
            return;
        }

        $heightPx = (int)($config['height'] ?? 100);

        // 图片高度不能超过行高
        $heightPx = min($heightPx, $rowHeight);

        $cellOptions = [
            'vAlign' => 'center',
            'valign' => 'center',
        ];

        if ($hasBorder) {
            $cellOptions['borderSize'] = $borderSize;
            $cellOptions['borderColor'] = $borderColor;
        }

        $cell = $table->addCell($widthPx * self::PX_TO_TWIPS, $cellOptions);

        $cell->addImage($path, [
            'width' => $widthPx,
            'height' => $heightPx,
            'alignment' => Jc::CENTER,
        ]);
    }

    protected function needsBorder(array $config): bool
    {
        return isset($config['border']) && $config['border'] === true;
    }

    protected function getAlignment(string $align): string
    {
        return match ($align) {
            'center' => Jc::CENTER,
            'right' => Jc::RIGHT,
            default => Jc::LEFT,
        };
    }

    public function saveToFile(string $filename): void
    {
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save($filename);
    }

    public function outputToBrowser(string $filename = 'document.docx'): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }
}