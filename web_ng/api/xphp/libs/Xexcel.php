<?php
// phpcs:ignoreFile -- 框架类

/*********************************************************************************
 *  扩展类库 - excle生成类
 *  功能：基于 JSON 布局数据生成
 ***********************************************************************************/

namespace xphp;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Xexcel
{
    private $spreadsheet;
    private $sheet;
    private $defaultFontSize = 12;

    // 配置参数
    private $config = [
        'pxPerCol' => 80,
        'pxPerRow' => 20,
        'colWidthUnit' => 8.43,
        'rowHeightUnit' => 15,
        'pxPerWidthUnit' => 7.6,
        'pxPerHeightUnit' => 1.333
    ];

    // 预定义颜色
    private $colors = [
        'blue' => '4F81BD',
        'light_blue' => 'DCE6F1',
        'green' => 'C5E0B4',
        'light_green' => 'E2F0D9',
        'orange' => 'F8CBAD',
        'light_orange' => 'FCE4D6',
        'gray' => 'D9D9D9',
        'light_gray' => 'F2F2F2',
        'yellow' => 'FFE699',
        'light_yellow' => 'FFF2CC',
        'red' => 'F4B084',
        'light_red' => 'FCE4D6',
        'purple' => 'B4C7E7',
        'light_purple' => 'E6E6F2',
        'white' => 'FFFFFF',
        'black' => '000000'
    ];

    // 边框样式映射
    private $borderStyles = [
        'thin' => Border::BORDER_THIN,
        'thick' => Border::BORDER_THICK,
        'medium' => Border::BORDER_MEDIUM,
        'dashed' => Border::BORDER_DASHED,
        'dotted' => Border::BORDER_DOTTED,
        'double' => Border::BORDER_DOUBLE,
        'hair' => Border::BORDER_HAIR,
        'medium_dashed' => Border::BORDER_MEDIUMDASHED,
        'dash_dot' => Border::BORDER_DASHDOT,
        'medium_dash_dot' => Border::BORDER_MEDIUMDASHDOT,
        'dash_dot_dot' => Border::BORDER_DASHDOTDOT,
        'medium_dash_dot_dot' => Border::BORDER_MEDIUMDASHDOTDOT,
        'slant_dash_dot' => Border::BORDER_SLANTDASHDOT,
        'none' => Border::BORDER_NONE
    ];

    public function __construct(array $config = [])
    {

        $spreadsheet = new Spreadsheet();
        $this->spreadsheet = $spreadsheet;
        $this->sheet = $spreadsheet->getActiveSheet();

        // 合并配置
        $this->config = array_merge($this->config, $config);

        // 设置默认字体
        $this->spreadsheet->getDefaultStyle()
            ->getFont()
            ->setName('Microsoft YaHei')
            ->setSize($this->defaultFontSize);

        // 设置默认列宽和行高
        $this->sheet->getDefaultColumnDimension()->setWidth($this->config['colWidthUnit']);
        $this->sheet->getDefaultRowDimension()->setRowHeight($this->config['rowHeightUnit']);
    }

    public function render(array $items)
    {
        foreach ($items as $item) {
            $this->renderItem($item);
        }
        return $this->spreadsheet;
    }

    private function renderItem(array $item): void
    {
        $type = $item['type'] ?? '';
        $value = $item['value'] ?? '';

        // 获取坐标
        if (isset($item['row']) && isset($item['col'])) {
            $row = (int)$item['row'];
            $col = (string)$item['col'];
        } else {
            $left = (int)($item['left'] ?? 0);
            $top = (int)($item['top'] ?? 0);
            $col = $this->pxToCol($left);
            $row = $this->pxToRow($top);
        }

        if (str_contains($type, 'image')) {
            $width = isset($item['width']) ? (int)$item['width'] : null;
            $height = isset($item['height']) ? (int)$item['height'] : null;
            $align = $item['align'] ?? 'center';
            $this->renderImage($value, $col, $row, $width, $height, $align, $item);
        } else {
            $this->renderText($value, $col, $row, 12, false, $item);
        }
    }

    /**
     * 输出 Excel
     * @param int    $mode 0输出浏览器 1保存
     * @param string $name 输出名称或保存完整路径
     * @return array
     */
    public function outputExcel($mode = 0, $name = '')
    {
        $outputName = !empty($name) ? $name : 'excel_' . date('YmdHis') . '.xlsx';

        $writer = new Xlsx($this->spreadsheet);
        if ($mode == 1) {
            try {
                if (empty($name)) {
                    $basePath = DATA_PATH . 'industry/excel/';
                    if (!is_dir($basePath)) {
                        if (!mkdir($basePath, 0755, true) && !is_dir($basePath)) {
                            return ['code' => -1, 'msg' => "can not make dir : {$basePath}"];
                        }
                    }
                    $outputName = $basePath . $outputName;
                }

                $writer->save($outputName);
                return [
                    'code' => 0,
                    'msg' => 'excel make success',
                    'data' => [
                        'path' => realpath($outputName),
                        'name' => basename($outputName)
                    ]
                ];
            } catch (\Exception $e) {
                return [
                    'code' => 1,
                    'msg' => 'excel save fail: ' . $e->getMessage()
                ];
            }
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename={$outputName}");
            $writer->save('php://output');
            exit;
        }
    }

    /**
     * 渲染文字 - 增强边框功能
     */
    private function renderText(string $text, string $col, int $row, int $fontSize = 12, bool $isBold = false, array $item = []): void
    {
        $cell = $col . $row;

        // 写入单元格
        $this->sheet->setCellValue($cell, $text);

        // 获取样式对象
        $style = $this->sheet->getStyle($cell);

        // 设置字体
        $fontSize = $item['font_size'] ?? $fontSize;
        $isBold = $item['bold'] ?? $isBold;
        $style->getFont()->setSize($fontSize)->setBold($isBold);

        // 设置文字颜色
        if (isset($item['color'])) {
            $fontColor = $this->getColor($item['color']);
            $style->getFont()->getColor()->setRGB($fontColor);
        }

        // 设置对齐方式
        $align = $item['text_align'] ?? 'left';
        $verticalAlign = $item['vertical_align'] ?? 'center';
        $horizontal = $this->mapHorizontalAlign($align);
        $vertical = $this->mapVerticalAlign($verticalAlign);

        $style->getAlignment()
            ->setHorizontal($horizontal)
            ->setVertical($vertical);

        // 设置背景色
        if (isset($item['bg_color'])) {
            $bgColor = $this->getColor($item['bg_color']);
            $style->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bgColor);
        }

        // 设置边框
        if (isset($item['border'])) {
            $this->applyBorder($style, $item['border'], $cell);
        }

        // 设置列宽
        if (isset($item['width'])) {
            $colWidth = $this->pxToExcelWidth((int)$item['width']);
            $this->sheet->getColumnDimension($col)->setWidth($colWidth);
        } else {
            $textLength = mb_strlen($text, 'UTF-8');
            $colWidth = max($this->config['colWidthUnit'], $textLength * 1.2);
            $this->sheet->getColumnDimension($col)->setWidth($colWidth);
        }

        // 设置行高
        if (isset($item['height'])) {
            $rowHeight = $this->pxToExcelHeight((int)$item['height']);
            $this->sheet->getRowDimension($row)->setRowHeight($rowHeight);
        } else {
            $rowHeight = max($this->config['rowHeightUnit'], $fontSize * 1.3);
            $this->sheet->getRowDimension($row)->setRowHeight($rowHeight);
        }
    }

    /**
     * 渲染图片 - 增强边框功能
     */
    private function renderImage(string $imagePath, string $col, int $row, ?int $targetWidth = null, ?int $targetHeight = null, string $align = 'center', array $item = []): void
    {
        if (!file_exists($imagePath)) {
            $this->renderText("[pic]", $col, $row, 10, false, [
                'width' => 100,
                'height' => 80,
                'bg_color' => 'light_gray',
                'border' => ['all' => 'dashed', 'color' => '999999']
            ]);
            return;
        }

        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo) {
            $this->renderText("[pic]", $col, $row, 10, false, [
                'width' => 100,
                'height' => 80,
                'bg_color' => 'light_gray',
                'border' => ['all' => 'dashed', 'color' => '999999']
            ]);
            return;
        }

        list($origWidth, $origHeight) = $imageInfo;

        // 处理尺寸参数
        $specifiedWidth = null;
        $specifiedHeight = null;

        if (isset($item['width']) || isset($item['height'])) {
            $specifiedWidth = isset($item['width']) ? (int)$item['width'] : null;
            $specifiedHeight = isset($item['height']) ? (int)$item['height'] : null;
        } elseif ($targetWidth !== null || $targetHeight !== null) {
            $specifiedWidth = $targetWidth;
            $specifiedHeight = $targetHeight;
        }

        // 计算最终显示尺寸
        $displayWidth = $origWidth;
        $displayHeight = $origHeight;

        if ($specifiedWidth === null && $specifiedHeight === null) {
            $displayWidth = $origWidth;
            $displayHeight = $origHeight;
        } elseif ($specifiedWidth !== null && $specifiedHeight === null) {
            $displayWidth = $specifiedWidth;
            $displayHeight = (int)round($origHeight * ($specifiedWidth / $origWidth));
        } elseif ($specifiedHeight !== null && $specifiedWidth === null) {
            $displayHeight = $specifiedHeight;
            $displayWidth = (int)round($origWidth * ($specifiedHeight / $origHeight));
        } else {
            $displayWidth = $specifiedWidth;
            $displayHeight = $specifiedHeight;
        }

        // 计算需要的列数和行数
        $colsNeeded = $this->calculateColsNeeded($displayWidth);
        $rowsNeeded = $this->calculateRowsNeeded($displayHeight);

        $colNum = $this->colToNum($col);
        $startCell = $col . $row;

        // 先设置列宽和行高
        $colWidth = $this->calculateColWidth($displayWidth, $colsNeeded);
        $rowHeight = $this->calculateRowHeight($displayHeight, $rowsNeeded);

        // 设置列宽
        for ($i = 0; $i < $colsNeeded; $i++) {
            $currentCol = $this->numToCol($colNum + $i);
            $this->sheet->getColumnDimension($currentCol)->setWidth($colWidth);
        }

        // 设置行高
        for ($i = 0; $i < $rowsNeeded; $i++) {
            $currentRow = $row + $i;
            $this->sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);
        }

        // 合并单元格
        $mergeRange = $startCell;
        if ($colsNeeded > 1 || $rowsNeeded > 1) {
            $colEnd = $this->numToCol($colNum + $colsNeeded - 1);
            $rowEnd = $row + $rowsNeeded - 1;
            $mergeRange = $startCell . ':' . $colEnd . $rowEnd;
            $this->sheet->mergeCells($mergeRange);
        }

        // 设置合并区域的样式
        $style = $this->sheet->getStyle($mergeRange);

        // 设置背景色
        if (isset($item['bg_color'])) {
            $bgColor = $this->getColor($item['bg_color']);
            $style->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bgColor);
        }

        // 设置对齐方式
        $style->getAlignment()
            ->setHorizontal($this->mapHorizontalAlign($align))
            ->setVertical(Alignment::VERTICAL_CENTER);

        // 设置边框
        if (isset($item['border'])) {
            $this->applyBorder($style, $item['border'], $mergeRange);
        }

        // 计算单元格总像素尺寸
        $totalCellWidthPx = $colsNeeded * $colWidth * $this->config['pxPerWidthUnit'];
        $totalCellHeightPx = $rowsNeeded * $rowHeight * $this->config['pxPerHeightUnit'];

        // 计算偏移量
        $offsetX = 0;
        $offsetY = 0;

        // 水平对齐
        if ($totalCellWidthPx > $displayWidth) {
            switch ($align) {
                case 'left':
                    $offsetX = 5;
                    break;
                case 'right':
                    $offsetX = $totalCellWidthPx - $displayWidth - 5;
                    break;
                case 'center':
                default:
                    $offsetX = ($totalCellWidthPx - $displayWidth) / 2;
                    break;
            }
        } else {
            $neededColWidth = $displayWidth / ($colsNeeded * $this->config['pxPerWidthUnit']);
            for ($i = 0; $i < $colsNeeded; $i++) {
                $currentCol = $this->numToCol($colNum + $i);
                $this->sheet->getColumnDimension($currentCol)->setWidth($neededColWidth);
            }
            $totalCellWidthPx = $displayWidth;
        }

        // 垂直对齐
        if ($totalCellHeightPx > $displayHeight) {
            $offsetY = ($totalCellHeightPx - $displayHeight) / 2;
        } else {
            $neededRowHeight = $displayHeight / ($rowsNeeded * $this->config['pxPerHeightUnit']);
            for ($i = 0; $i < $rowsNeeded; $i++) {
                $currentRow = $row + $i;
                $this->sheet->getRowDimension($currentRow)->setRowHeight($neededRowHeight);
            }
            $totalCellHeightPx = $displayHeight;
        }

        // 确保偏移量为非负整数
        $offsetX = max(0, (int)round($offsetX));
        $offsetY = max(0, (int)round($offsetY));

        // 插入图片
        $drawing = new Drawing();
        $drawing->setName(basename($imagePath));
        $drawing->setPath($imagePath);
        $drawing->setCoordinates($startCell);
        $drawing->setOffsetX($offsetX);
        $drawing->setOffsetY($offsetY);
        $drawing->setWidth($displayWidth);
        $drawing->setHeight($displayHeight);

        $drawing->setWorksheet($this->sheet);
    }

    /**
     * 获取颜色值
     */
    private function getColor(string $color): string
    {
        // 如果是十六进制颜色码，直接返回
        if (preg_match('/^[0-9A-F]{6}$/i', $color)) {
            return strtoupper($color);
        }

        // 如果是预定义颜色名
        return $this->colors[$color] ?? 'FFFFFF'; // 默认白色
    }

    /**
     * 应用边框 - 增强版，支持单独设置上下左右边框
     */
    private function applyBorder($style, $borderConfig, string $range = ''): void
    {
        $borders = $style->getBorders();

        if (is_string($borderConfig)) {
            // 简单模式：统一边框
            $borderStyle = $this->getBorderStyle($borderConfig);
            $color = '000000'; // 默认黑色

            $borders->getTop()
                ->setBorderStyle($borderStyle)
                ->getColor()->setRGB($color);
            $borders->getRight()
                ->setBorderStyle($borderStyle)
                ->getColor()->setRGB($color);
            $borders->getBottom()
                ->setBorderStyle($borderStyle)
                ->getColor()->setRGB($color);
            $borders->getLeft()
                ->setBorderStyle($borderStyle)
                ->getColor()->setRGB($color);

        } elseif (is_array($borderConfig)) {
            // 详细模式：支持单独设置各边边框

            // 1. 设置全部边框
            if (isset($borderConfig['all'])) {
                $borderStyle = $this->getBorderStyle($borderConfig['all']);
                $color = $borderConfig['color'] ?? '000000';

                $borders->getTop()
                    ->setBorderStyle($borderStyle)
                    ->getColor()->setRGB($this->getColor($color));
                $borders->getRight()
                    ->setBorderStyle($borderStyle)
                    ->getColor()->setRGB($this->getColor($color));
                $borders->getBottom()
                    ->setBorderStyle($borderStyle)
                    ->getColor()->setRGB($this->getColor($color));
                $borders->getLeft()
                    ->setBorderStyle($borderStyle)
                    ->getColor()->setRGB($this->getColor($color));
            }

            // 2. 单独设置上边框
            if (isset($borderConfig['top'])) {
                $topStyle = $this->getBorderStyle($borderConfig['top']);
                $topColor = isset($borderConfig['top_color']) ?
                    $this->getColor($borderConfig['top_color']) :
                    ($borderConfig['color'] ?? '000000');

                $borders->getTop()
                    ->setBorderStyle($topStyle)
                    ->getColor()->setRGB($topColor);
            }

            // 3. 单独设置右边框
            if (isset($borderConfig['right'])) {
                $rightStyle = $this->getBorderStyle($borderConfig['right']);
                $rightColor = isset($borderConfig['right_color']) ?
                    $this->getColor($borderConfig['right_color']) :
                    ($borderConfig['color'] ?? '000000');

                $borders->getRight()
                    ->setBorderStyle($rightStyle)
                    ->getColor()->setRGB($rightColor);
            }

            // 4. 单独设置下边框
            if (isset($borderConfig['bottom'])) {
                $bottomStyle = $this->getBorderStyle($borderConfig['bottom']);
                $bottomColor = isset($borderConfig['bottom_color']) ?
                    $this->getColor($borderConfig['bottom_color']) :
                    ($borderConfig['color'] ?? '000000');

                $borders->getBottom()
                    ->setBorderStyle($bottomStyle)
                    ->getColor()->setRGB($bottomColor);
            }

            // 5. 单独设置左边框
            if (isset($borderConfig['left'])) {
                $leftStyle = $this->getBorderStyle($borderConfig['left']);
                $leftColor = isset($borderConfig['left_color']) ?
                    $this->getColor($borderConfig['left_color']) :
                    ($borderConfig['color'] ?? '000000');

                $borders->getLeft()
                    ->setBorderStyle($leftStyle)
                    ->getColor()->setRGB($leftColor);
            }

            // 6. 单独设置水平边框（上下）
            if (isset($borderConfig['horizontal'])) {
                $horizontalStyle = $this->getBorderStyle($borderConfig['horizontal']);
                $horizontalColor = isset($borderConfig['horizontal_color']) ?
                    $this->getColor($borderConfig['horizontal_color']) :
                    ($borderConfig['color'] ?? '000000');

                $borders->getTop()
                    ->setBorderStyle($horizontalStyle)
                    ->getColor()->setRGB($horizontalColor);
                $borders->getBottom()
                    ->setBorderStyle($horizontalStyle)
                    ->getColor()->setRGB($horizontalColor);
            }

            // 7. 单独设置垂直边框（左右）
            if (isset($borderConfig['vertical'])) {
                $verticalStyle = $this->getBorderStyle($borderConfig['vertical']);
                $verticalColor = isset($borderConfig['vertical_color']) ?
                    $this->getColor($borderConfig['vertical_color']) :
                    ($borderConfig['color'] ?? '000000');

                $borders->getRight()
                    ->setBorderStyle($verticalStyle)
                    ->getColor()->setRGB($verticalColor);
                $borders->getLeft()
                    ->setBorderStyle($verticalStyle)
                    ->getColor()->setRGB($verticalColor);
            }
        }
    }

    /**
     * 获取边框样式
     */
    private function getBorderStyle(string $style): string
    {
        return $this->borderStyles[$style] ?? Border::BORDER_THIN;
    }

    /**
     * 水平对齐映射
     */
    private function mapHorizontalAlign(string $align): string
    {
        $map = [
            'left' => Alignment::HORIZONTAL_LEFT,
            'center' => Alignment::HORIZONTAL_CENTER,
            'right' => Alignment::HORIZONTAL_RIGHT,
            'justify' => Alignment::HORIZONTAL_JUSTIFY,
            'fill' => Alignment::HORIZONTAL_FILL,
            'distributed' => Alignment::HORIZONTAL_DISTRIBUTED
        ];

        return $map[$align] ?? Alignment::HORIZONTAL_LEFT;
    }

    /**
     * 垂直对齐映射
     */
    private function mapVerticalAlign(string $align): string
    {
        $map = [
            'top' => Alignment::VERTICAL_TOP,
            'center' => Alignment::VERTICAL_CENTER,
            'bottom' => Alignment::VERTICAL_BOTTOM,
            'justify' => Alignment::VERTICAL_JUSTIFY,
            'distributed' => Alignment::VERTICAL_DISTRIBUTED
        ];

        return $map[$align] ?? Alignment::VERTICAL_CENTER;
    }

    // 其他辅助方法
    // 计算需要的行数
    private function calculateColsNeeded(int $widthPx): int
    {
        return max(1, ceil($widthPx / $this->config['pxPerCol']));
    }

    // 计算需要的列数
    private function calculateRowsNeeded(int $heightPx): int
    {
        return max(1, ceil($heightPx / $this->config['pxPerRow']));
    }

    // 设置行高
    private function calculateColWidth(int $widthPx, int $colsNeeded): float
    {
        $neededWidth = $widthPx / $this->config['pxPerWidthUnit'];
        return max($this->config['colWidthUnit'], $neededWidth / $colsNeeded);
    }

    // 设置列宽
    private function calculateRowHeight(int $heightPx, int $rowsNeeded): float
    {
        $neededHeight = $heightPx / $this->config['pxPerHeightUnit'];
        return max($this->config['rowHeightUnit'], $neededHeight / $rowsNeeded);
    }

    // px转excel宽度单元
    private function pxToExcelWidth(int $px): float
    {
        return $px / $this->config['pxPerWidthUnit'];
    }

    // px转excel高度单元
    private function pxToExcelHeight(int $px): float
    {
        return $px / $this->config['pxPerHeightUnit'];
    }

    // px转行数，具体第几行
    private function pxToCol(int $px): string
    {
        $colIndex = floor($px / $this->config['pxPerCol']) + 1;
        return $this->numToCol($colIndex);
    }

    // px转列数，具体第几列
    private function pxToRow(int $px): int
    {
        return floor($px / $this->config['pxPerRow']) + 1;
    }

    // 数字转excel 行数 1转A 2转B
    private function numToCol(int $num): string
    {
        $result = '';
        while ($num > 0) {
            $num--;
            $result = chr(65 + ($num % 26)) . $result;
            $num = intval($num / 26);
        }
        return $result ?: 'A';
    }

    // excel行数转数字  A转1 B转1
    private function colToNum(string $col): int
    {
        $num = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($col[$i]) - 64);
        }
        return $num;
    }
}
