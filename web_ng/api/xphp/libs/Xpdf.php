<?php
// phpcs:ignoreFile -- 框架类
/*********************************************************************************
 *  扩展类库 - TCPDF生成类
 *  功能：基于 JSON 布局数据生成 PDF，支持 logo、二维码、富文本、水印等
 ***********************************************************************************/

namespace xphp;

use TCPDF;
use Exception;

class Xpdf extends TCPDF
{
    protected $config = [];
    protected $reportTitle = '';   // 存储页眉左侧标题
    protected $reportMd5 = '';     // 存储页眉右侧 MD5
    protected $water = '';     // 水印内容
    protected $waterType = '';     // 水印类型 1 文字，2图片

    protected $currentBasic = 0;       // 跟踪当前的Y坐标
    protected $currentBasicHeight = 0;  // 跟踪当前的y坐标
    protected $lastHeight = 20;        // 记录上一次写入的高度

    public function __construct()
    {
        $this->config = xphp_get_config('industry', 'tcpdf');

        parent::__construct(
            $this->config['orientation'],
            'mm',
            $this->config['format'],
            true,
            'UTF-8',
            false
        );

        // 设置文档信息
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('vinchin');
        $this->SetTitle('Verification Report');
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // 设置边距
        $this->SetMargins(
            $this->config['margin_left'],
            $this->config['margin_top'],
            $this->config['margin_right']
        );
        $this->SetHeaderMargin($this->config['margin_header']);
        $this->SetFooterMargin($this->config['margin_footer']);

        // 自动分页
        $this->SetAutoPageBreak(true, $this->config['margin_bottom']);

        if (!empty($this->config['diy_font'])) {
            $fontName = $this->setupCustomFont();
            $this->config['default_font'] = $fontName;
        } else {
            // 如果没有自定义字体，强制使用支持中文的 CID 字体
            $this->config['default_font'] = 'cid0cs'; // 或 'freesans' 如果你启用了它
        }

        // ⭐️ 关键：启用自定义 Header/Footer（必须为 true）
        $this->setPrintHeader(true);
        $this->setPrintFooter(true);

        // 设置默认字体（必须在 AddFont 之后）
        $this->SetFont($this->config['default_font'], '', $this->config['default_font_size']);
    }

    /**
     * 设置自定义字体
     */
    private function setupCustomFont()
    {
        $ttfFile = $this->config['diy_font'];

        // 自定义字体缓存目录（仅用于生成 .php 文件）
        $fontCacheDir = DATA_PATH . 'industry/fonts/';
        if (!is_dir($fontCacheDir)) {
            mkdir($fontCacheDir, 0755, true);
        }

        $fontName = pathinfo($ttfFile, PATHINFO_FILENAME);
        $fontPhpFile = $fontCacheDir . $fontName . '.php';

        if (!file_exists($fontPhpFile)) {
            // 生成字体到自定义目录
            $result = \TCPDF_FONTS::addTTFfont(
                $ttfFile,
                'TrueTypeUnicode',
                '',
                32,
                $fontCacheDir  // 👈 生成到这里
            );
            if ($result === false) {
                return 'cid0cs';
            }
            $fontName = $result;
        }

        // ✅ 关键：使用完整路径注册字体（不依赖 K_PATH_FONTS）
        $this->AddFont($fontName, '', $fontCacheDir . $fontName . '.php');
        $this->AddFont($fontName, 'B', $fontCacheDir . $fontName . 'b.php'); // 如果有粗体
        $this->AddFont($fontName, 'I', $fontCacheDir . $fontName . 'i.php');
        $this->AddFont($fontName, 'BI', $fontCacheDir . $fontName . 'bi.php');
        // ... 其他样式

        return $fontName;
    }

    /**
     * 确保字体已注册
     */
    protected function registerFontIfNeeded()
    {
        $fontName = $this->config['default_font'];

        // 检查是否内置字体
        $coreFonts = ['courier', 'helvetica', 'times', 'symbol', 'zapfdingbats', 'cid0cs', 'cid0ct', 'cid0jp', 'cid0kr'];

        if (!in_array($fontName, $coreFonts)) {
            try {
                // 尝试添加自定义字体
                $this->AddFont($fontName, '', '', false);
                $this->AddFont($fontName, 'B', '', false);
                $this->AddFont($fontName, 'I', '', false);
                $this->AddFont($fontName, 'BI', '', false);
            } catch (\Exception $e) {
                // 如果失败，回退到 cid0cs
                // dump("字体注册失败 {$fontName}: " . $e->getMessage());
                $this->config['default_font'] = 'cid0cs';
            }
        }
    }

    /**
     * 自定义页眉：左侧标题，右侧 MD5
     */
    public function Header()
    {
        $pageWidth = $this->getPageWidth();
        $y = 4; // 距顶部 4mm

        $this->SetFont($this->config['default_font'], '', 10);
        $this->SetTextColor($this->config['rgba_text']['r'], $this->config['rgba_text']['g'], $this->config['rgba_text']['b']);

        // 左侧：标题
        $this->SetXY(10, $y);
        $this->Cell(0, 6, $this->reportTitle, 0, 0, 'L');

        // 右侧：MD5（如果有）
        if (!empty($this->reportMd5)) {
            $md5Text = $this->reportMd5;
            $md5Width = $this->GetStringWidth($md5Text);
            $rightX = $pageWidth - 10 - $md5Width; // 右边距 10mm
            $this->SetXY($rightX, $y);
            $this->Cell($md5Width, 6, $md5Text, 0, 0, 'R');
        }

        // 可选：加一条分隔线
        $this->SetDrawColor($this->config['rgba_line']['r'], $this->config['rgba_line']['g'], $this->config['rgba_line']['b']);
        $this->SetLineWidth(0.2);
        $this->Line(10, $y + 9, $pageWidth - 10, $y + 9);

    }

    /**
     * 自定义页脚：第 X 页 / 共 Y 页
     */
    public function Footer()
    {
        $y = $this->getPageHeight() - 9;
        $pageWidth = $this->getPageWidth();

        // 分隔线
        $this->SetDrawColor($this->config['rgba_line']['r'], $this->config['rgba_line']['g'], $this->config['rgba_line']['b']);
        $this->SetLineWidth(0.2);
        $this->Line(10, $y - 2, $pageWidth - 10, $y - 2);

        // 设置字体和颜色
        $this->SetFont($this->config['default_font'], '', 9);
        $this->SetTextColor($this->config['rgba_text']['r'], $this->config['rgba_text']['g'], $this->config['rgba_text']['b']);

        // 页码 - 使用 TCPDF 的别名系统
        $pageNum = $this->PageNo();
        $totalPages = $this->getAliasNbPages();
        $footerText = "第 {$pageNum} 页 / 共 {$totalPages} 页";

        $this->SetFont($this->config['default_font'], '', 10);
        $md5Width = $this->GetStringWidth($footerText);
        $rightX = $pageWidth - $md5Width - 1; // 右边距 10mm
        $this->SetXY($rightX, $y);
        $this->Cell($md5Width, 6, $footerText, 0, 0, 'R');

        // 添加水印
        if ($this->water) {
            if ($this->waterType == 1) {
                $this->addTextWatermark($this->water);
            } else {
                $this->addImageWatermark($this->water);
            }
        }
    }

    /**
     * 像素转毫米
     */
    protected function pxToMm($px)
    {
        return ((float)$px) / 3.7795275591;
    }

    /**
     * 设置文字
     */
    protected function setText($x, $y, $value, $family = '', $size = 12, $align = 'L', $style = '', $r = 51, $g = 51, $b = 51, $width = 0)
    {
        $this->SetXY($x, $y);
        if (!$family) {
            $family = $this->config['default_font'];
        }
        $this->SetFont($family, $style, $size);
        $this->SetTextColor($r, $g, $b);
        $this->MultiCell($width, 8, $value, 0, $align, false);
    }

    /**
     * 设置html
     */
    protected function setHtml($x, $y, $value, $family = '', $size = 10, $align = 'L', $width = '', $style = '')
    {

        $this->SetXY($x, $y);
        if (!$family) {
            $family = $this->config['default_font'];
        }
        $this->SetFont($family, $style, $size);
        $this->writeHTMLCell($width, 0, '', '', $value, 0, 1, false, true, $align);
    }

    /**
     * 设置竖线
     */
    protected function setLinev($x, $y, $y2, $width = 0.4, $r = 42, $g = 135, $b = 200)
    {
        $this->SetDrawColor($r, $g, $b);
        $this->SetLineWidth($width);
        $this->Line($x, $y, $x, $y2);
    }

    /**
     * 设置横线
     */
    protected function setLineL($x, $x2, $y, $width = 1.0, $r = 42, $g = 135, $b = 200)
    {
        $this->SetDrawColor($r, $g, $b);
        $this->SetLineWidth($width);
        $this->Line($x, $y, $x2, $y);
    }

    /**
     * 设置logo
     */
    protected function setImgL($x, $y, $value, $width = 120, $height = 50)
    {
        if ($value && file_exists($value)) {
            // 最左侧
            // 获取图片原始尺寸
            $imageInfo = @getimagesize($value);
            if ($imageInfo) {
                list($imgWidth, $imgHeight) = $imageInfo;
                // 目标宽度（例如：40mm）
                $targetWidth = $this->pxToMm($width);
                // 计算等比例高度
                $targetHeight = ($imgHeight / $imgWidth) * $targetWidth;
                // 垂直居中计算（更清晰的方式）
                $verticalCenterY = $y - 5 + ($this->pxToMm($height) / 2) - ($targetHeight / 2);
                // 最左侧显示
                $this->Image($value, $x, $verticalCenterY, $targetWidth, $targetHeight);
            }
        }
    }

    /**
     * 添加文字水印
     */
    protected function addTextWatermark($water)
    {
        $originalFont = $this->getFontFamily();
        $originalSize = $this->getFontSizePt();

        $this->SetAlpha(0.2);
        $this->SetFont($this->config['default_font'], 'B', 14);
        $this->SetTextColor($this->config['rgba_text']['r'], $this->config['rgba_text']['g'], $this->config['rgba_text']['b']);

        $pageWidth = $this->getPageWidth();
        $pageHeight = $this->getPageHeight();

        // 直接重复水印内容直到足够长
        $extendedWater = $this->repeatWatermarkToFit($water, $pageWidth);

        $textWidth = $this->GetStringWidth($extendedWater);
        $x = ($pageWidth - $textWidth) / 2;
        $y = $pageHeight / 2;

        $this->StartTransform();
        $this->Rotate(45, $x + $textWidth / 2, $y);
        $this->Text($x, $y, $extendedWater);
        $this->Text($x, $y - 80, $extendedWater);
        $this->Text($x, $y + 80, $extendedWater);
        $this->StopTransform();

        $this->SetAlpha(1);
        $this->SetFont($originalFont, '', $originalSize);
    }

    /**
     * 重复水印直到适合页面宽度
     */
    protected function repeatWatermarkToFit($water, $pageWidth)
    {
        // 获取原始宽度
        $originalWidth = $this->GetStringWidth($water);

        // 如果已经很长，直接返回
        if ($originalWidth >= $pageWidth * 0.5) {
            return $water;
        }

        // 计算需要重复的次数（目标为页面宽度的70%）
        $targetWidth = $pageWidth * 0.7;
        $repeatTimes = ceil($targetWidth / $originalWidth);

        // 限制最大重复次数
        $repeatTimes = min($repeatTimes, 10);

        // 重复水印内容
        $repeated = str_repeat($water, $repeatTimes);

        // 添加分隔符美化
        if ($repeatTimes > 1) {
            $separator = $this->getSimpleSeparator($water);
            $repeated = implode($separator, array_fill(0, $repeatTimes, $water));
        }

        return $repeated;
    }

    /**
     * 获取简单分隔符
     */
    protected function getSimpleSeparator($water)
    {
        // 根据水印长度选择分隔符
        $len = mb_strlen($water, 'UTF-8');

        if ($len == 1) {
            return ' '; // 单个字符用空格
        } elseif ($len <= 3) {
            return '   '; // 短词用三个空格
        } else {
            return ' | '; // 长文字用竖线
        }
    }

    /**
     * 添加图片水印
     */
    protected function addImageWatermark($water)
    {
        if (file_exists($water)) {
            $pageWidth = $this->getPageWidth();
            $pageHeight = $this->getPageHeight();
            $imageInfo = @getimagesize($water);
            if ($imageInfo) {
                [$imgWidth, $imgHeight] = $imageInfo;
                $scale = 0.25;
                $displayWidth = $imgWidth * $scale;
                $displayHeight = $imgHeight * $scale;
                $x = ($pageWidth - $displayWidth) / 2;
                $y = ($pageHeight - $displayHeight) / 2;

                $this->SetAlpha(0.1);
                $this->Image($water, $x, $y, $displayWidth, $displayHeight);
                $this->SetAlpha(1);
            }
        }
    }

    /**
     * 获取当前页的最大可用高度（从当前位置到页面底部）
     */
    protected function getRemainingPageHeight()
    {
        // 获取页面总高度
        $pageHeight = $this->getPageHeight();

        // 获取底部边距
        $bottomMargin = $this->config['margin_bottom'];

        // 计算剩余高度
        return $pageHeight - $bottomMargin;
    }

    /**
     * 输出 PDF
     */
    protected function outputPdf($mode, $name)
    {
        $outputName = $name ?: 'report_' . date('YmdHis') . '.pdf';

        if ($mode == 1) {
            try {
                $this->Output($outputName, 'F');
                return [
                    'code' => 0,
                    'msg' => 'pdf make success',
                    'data' => [
                        'path' => realpath($outputName),
                        'name' => basename($outputName)
                    ]
                ];
            } catch (\Exception $e) {
                return [
                    'code' => 1,
                    'msg' => 'pdf save fail: ' . $e->getMessage()
                ];
            }
        } elseif ($mode == 2) {
            // 新增：在线预览模式
            $this->Output($outputName, 'I');
            exit;
        } else {
            $this->Output($outputName, 'D');
            exit;
        }
    }
}