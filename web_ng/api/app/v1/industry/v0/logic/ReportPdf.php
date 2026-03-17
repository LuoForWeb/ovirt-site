<?php

namespace app\v1\industry\v0\logic;

use xphp\Xpdf;

/**
 * note          报告导出类型-继承xpdf基类
 * @author       wanggongxi@vinchin.com
 * @date         2025/12/30 10:13
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class ReportPdf extends Xpdf
{
    private $isBasicFirst = true;
    private $isBasicFirstY = 0;

    /**
     * 继承基类
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 生成PDF
     * @param array  $array 元素的位置信息数组
     * @param int    $mode  模式0下载到浏览器 1生成文件，2在线预览
     * @param string $md5   报告右上角的唯一编码
     * @param string $name  报告名称、mode为1时则为保存的绝对路径
     * @param string $water 报告水印
     * @param int    $type  报告水印类型
     *                      1文字，2图片
     * @param string $title 报告左上角的描述
     * @return array|void
     */
    public function makePdf(array $array, $mode = 0, $md5 = '', $name = '', $water = '', $type = 1, $title = '')
    {
        // 保存标题和MD5，供 Header() 使用
        $this->reportTitle = $title ?: '验证报告';
        $this->reportMd5 = $md5;
        $this->water = $water;
        $this->waterType = $type;

        // 添加第一页（此时会自动触发 Header()）
        $this->AddPage();

        // 初始化当前Y坐标
        $this->currentY = $this->GetY();

        // 绘制内容
        foreach ($array as $item) {
            $this->drawItem($item);
        }

        // 强制完成文档构建（虽然 Output 会做，但显式调用更保险）
        $this->close();

        return $this->outputPdf($mode, $name);
    }

    /**
     * 绘制单个元素
     * @param array $item 一些定位的数组
     * @return void
     */
    protected function drawItem(array $item)
    {
        $type = $item['type'];
        $value = $item['value'] ?? '';
        $top = $item['top'] ?? 0;
        $left = $item['left'] ?? 0;

        $pageWidth = $this->getPageWidth();

        $x = $this->pxToMm($left);
        if ($left < 400) {
            $x += $this->config['margin_left'];
        }

        $y = $this->pxToMm($top) + $this->config['margin_top'] + $this->config['margin_header'];

        if (in_array($item['type'], ['item_basic_title', 'item_logo', 'item_qrcode', 'item_title'])) {
            // 这些是固定的，不参与
            // 基本信息、logo、二维码、标题
        } else {
            // 因为传的是绝对路径，需要转换为相对的
            if ($this->currentBasic == 0) {
                // 第一次
                $this->currentBasic = $y;
                $this->currentBasicHeight = $this->GetY();
            }

            if ($y > $this->currentBasic) {
                // 换行
                $this->currentBasic = $y;
                // 需要根据上一次写入的高度来给定
                $this->currentBasicHeight = $this->lastHeight + 4;
            }

            $y = $this->currentBasicHeight;

            // 如果有新页
            if ($item['type'] == 'item_description') {
                // 验证信息单独起一页
                $this->AddPage();// 新增一页
                $y = $this->currentBasicHeight = $this->config['margin_header'] + $this->config['margin_top'];
            } elseif ($y + 8 >= $this->getRemainingPageHeight()) {
                $this->AddPage();// 新增一页
                $y = $this->currentBasicHeight = $this->config['margin_header'] + $this->config['margin_top'];
            }
        }

        switch ($type) {
            case 'item_basic_title':
                // 基本信息
                $this->setText(10, 44, $value);

                if (!empty($item['en_value'])) {
                    // 存在英文
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($value);
                    // 右边增加一条竖线
                    $this->setLinev(13 + $textWidth, 45, 49);
                    // 接着右边放内容
                    $this->setText(
                        10 + $textWidth + 5,
                        44,
                        $item['en_value'],
                        '',
                        12,
                        'L',
                        '',
                        90,
                        90,
                        90
                    );
                }
                $this->lastHeight = $this->GetY() + 10;
                break;
            case 'item_logo':
                // 报告logo
                $this->setImgL(10, $y, $value, 120, 50);
                break;
            case 'item_qrcode':
                // 报告二维码
                if ($value && file_exists($value)) {
                    $md5Width = $this->pxToMm(50);
                    $rightX = $pageWidth - 10 - $md5Width; // 右边距 10mm
                    // 靠最右边的
                    $this->Image($value, $rightX, $y - 5, $md5Width, $md5Width);
                }
                break;
            case 'item_title':
                // 计算文本宽度
                $this->SetFont($this->config['default_font'], '', 18);
                $textWidth = $this->GetStringWidth($value);
                // 居中：页面宽度一半减去文本宽度一半
                $centerX = ($pageWidth - $textWidth) / 2;
                $this->setText(
                    $centerX,
                    22,
                    $value,
                    '',
                    18,
                    'C',
                    'B',
                    0,
                    0,
                    0,
                    $textWidth + 5
                );
                if (!empty($item['en_value'])) {
                    // 英文
                    $this->SetFont($this->config['default_font'], '', 12);
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($item['en_value']);
                    // 居中：页面宽度一半减去文本宽度一半
                    $centerX = ($pageWidth - $textWidth) / 2;
                    $this->setText(
                        $centerX,
                        30,
                        $item['en_value'],
                        '',
                        12,
                        'C',
                        'B',
                        102,
                        102,
                        102,
                        $textWidth + 5
                    );
                }
                // 下面在跟一根线
                $this->setLineL(10, $pageWidth - 10, 39, 1.0);
                break;
            case 'item_plan':  // 验证方案
            case 'item_result':  // 验证结论
                $this->setText($x, $y, $item['name']);
                if (!empty($item['en_name'])) {
                    // 存在英文
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($item['name']);
                    // 右边增加一条竖线
                    $this->setLinev(13 + $textWidth, $y + 1, $y + 5);
                    // 接着右边放内容
                    $this->setText(10 + $textWidth + 5, $y, $item['en_name'], '', 12, 'L', '', 90, 90, 90);
                }
                if (!empty($item['value'])) {
                    // 放html
                    // 下面在跟一根线
                    $this->setLineL(
                        10,
                        $pageWidth - 10,
                        $y + 10,
                        0.2,
                        $this->config['rgba_line']['r'],
                        $this->config['rgba_line']['g'],
                        $this->config['rgba_line']['b']
                    );
                    $this->setHtml(
                        15,
                        $y + 15,
                        $item['value'],
                        $this->config['default_font'],
                        10,
                        'L',
                        $pageWidth - 30
                    );
                    // 获取绘制后的Y坐标
                    $afterY = $this->GetY();
                    // 在HTML内容下面再画一根线
                    $this->setLineL(
                        10,
                        $pageWidth - 10,
                        $afterY + 5,
                        0.2,
                        $this->config['rgba_line']['r'],
                        $this->config['rgba_line']['g'],
                        $this->config['rgba_line']['b']
                    );
                }
                $this->lastHeight = $this->GetY() + 10;
                break;
            case 'item_approval':
                // 审批流程
                $y = $this->GetY() + 20;
                $this->setText($x, $y, $item['name']);
                if (!empty($item['en_name'])) {
                    // 存在英文
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($item['name']);
                    // 右边增加一条竖线
                    $this->setLinev(13 + $textWidth, $y + 1, $y + 5);
                    // 接着右边放内容
                    $this->setText(10 + $textWidth + 5, $y, $item['en_name'], '', 12, 'L', '', 90, 90, 90);
                }
                if (!empty($item['value'])) {
                    $y += 15;
                    // 放html
                    // 定义列宽（单位 mm）
                    $col1Width = 35;     // 时间列
                    $col2Width = 40;    // 用户/角色列
                    $col3Width = 30;     // 状态列
                    $col4Width = 90;     // 备注列

                    // 背景色（RGB）
                    $bgColor = [235, 235, 235]; // 背景
                    foreach ($value as $item) {
                        $createTime = htmlspecialchars($item['create_time'] ?? '');
                        $position = htmlspecialchars($item['position'] ?? '');
                        $userName = htmlspecialchars($item['user_name'] ?? '');
                        $desc = htmlspecialchars($item['desc'] ?? '');
                        $remark = htmlspecialchars($item['remark'] ?? '');
                        $changes = $item['change'] ?? [];

                        // 1. 写第一行：时间 + 用户 + 状态 + 备注
                        $text1 = $createTime . ' ';
                        $text2 = $position . ':<strong>' . $userName . '</strong>';
                        $text3 = $desc;
                        $text4 = $remark;
                        $this->SetFont($this->config['default_font'], '', 10);
                        $this->SetTextColor(0, 0, 0);

                        // 需要计算下备注的高度
                        $col4Height = $this->getStringHeight($col4Width, $text4);
                        if ($col4Height + 5 + $y > $this->getRemainingPageHeight()) {
                            // 换行
                            $this->AddPage();
                            $y = $this->currentBasicHeight =
                                $this->config['margin_header'] + $this->config['margin_top'];
                        }
                        // 计算备注高度（用于行高）
                        $rowHeight = max(6, $col4Height) + 5;
                        // 【绘制背景矩形】
                        $this->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
                        $this->Rect($x, $y - 2, $pageWidth - 20, $rowHeight + count($changes) * 5, 'F'); // 'F' = 填充

                        // 使用 writeHTMLCell 分列写入
                        $this->SetXY($x, $y);
                        $this->writeHTMLCell($col1Width, 0, '', '', $text1, 0, 0, false, true, 'L');

                        $this->SetXY($x + $col1Width, $y);
                        $this->writeHTMLCell($col2Width, 0, '', '', $text2, 0, 0, false, true, 'L');

                        $this->SetXY($x + $col1Width + $col2Width, $y);
                        $this->writeHTMLCell($col3Width, 0, '', '', $text3, 0, 0, false, true, 'L');

                        $this->SetXY($x + $col1Width + $col2Width + $col3Width, $y);
                        $this->writeHTMLCell($col4Width, 0, '', '', $text4, 0, 0, false, true, 'L');
                        // 2. 写变更记录（小字）
                        if (!empty($changes)) {
                            $this->SetFont($this->config['default_font'], '', 8);
                            $this->SetTextColor(102, 102, 102);
                            foreach ($changes as $c) {
                                $y += 5;
                                $this->SetXY($x + $col1Width, $y);
                                $this->Cell(
                                    $col2Width + $col3Width - 1,
                                    5,
                                    htmlspecialchars($c['time']) . ' ' . htmlspecialchars($c['des']),
                                    0,
                                    1,
                                    'L'
                                );
                            }
                        }

                        if ($col4Height > 5) {
                            $y += $col4Height - 5;
                        }
                        // 下一行
                        $y += 10;
                    }

                    $this->SetXY($x, $y);
                }
                $this->lastHeight = $this->GetY();
                break;
            case 'item_field': // 自定义字段
            case 'item_textarea': // 自定义文本
            case 'item_sys_code': // 系统编号
            case 'item_datetime': // 自定义日期
            case 'item_sys_name': // 系统名称
            case 'item_host': // 主机名
            case 'item_ip': // IP地址
            case 'item_operator': // 操作人员
            case 'item_results': // 最终结果
                if ($this->isBasicFirst) {
                    // 第一次出现距离上面多 5
                    $this->isBasicFirstY = $y;
                    $y += 5;
                    $this->isBasicFirst = false;
                } elseif ($this->isBasicFirstY == $y) {
                    // 和第一次出现的高度一致，那么同理多5个
                    $y += 5;
                }
                $name = $item['name'];
                $this->setText($x, $y, $name, $this->config['default_font'], 11, 'L', '', 102, 102, 102);

                if (!empty($item['en_name'])) {
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($name);
                    $this->setText(
                        $x + $textWidth + 1,
                        $y,
                        $item['en_name'],
                        $this->config['default_font'],
                        11,
                        'L',
                        '',
                        102,
                        102,
                        102
                    );
                }
                if (!empty($value)) {
                    $this->setText($x, $y + 7, $value, $this->config['default_font'], 12);
                }
                $this->lastHeight = $this->GetY();
                break;
            case 'item_operator_sign_sys':
                // 系统签字（操作员）
            case 'item_auditor_sign_sys':
                // 系统签字（审计员）
            case 'item_make_time':
                // 报告生成时间
            case 'item_download_time':
                // 报告下载时间
            case 'item_operator_sign':
                // 操作员签字
            case 'item_auditor_sign':
                // 审计员签字
                $name = $item['name'];
                $this->setText($x, $y, $name, '', 10);

                $textWidth = $this->GetStringWidth($name);
                if (!empty($item['en_name'])) {
                    $textWidth2 = $this->GetStringWidth($item['en_name']);
                    $textWidth = max($textWidth, $textWidth2);
                    $this->setText($x, $y + 5, $item['en_name'], '', 10, 'L', '', 102, 102, 102);
                }
                if (!empty($value)) {
                    $this->setText($x + $textWidth + 2, $y, $value);
                }
                $this->lastHeight = $this->GetY() + 10;
                break;
            case 'item_description':
                // 验证信息
                $this->setText($x, $y, $item['name']);
                if (!empty($item['en_name'])) {
                    // 存在英文
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($item['name']);
                    // 右边增加一条竖线
                    $this->setLinev(13 + $textWidth, $y + 1, $y + 5);
                    // 接着右边放内容
                    $this->setText(10 + $textWidth + 5, $y, $item['en_name'], '', 10, 'L', '', 90, 90, 90);
                }
                $this->lastHeight = $this->GetY();
                break;
            case 'item_module':
                // 设备名称
            case 'item_point':
                //  验证备份时间点
                // 背景色（RGB）
                $bgColor = [235, 235, 235];
                // 【绘制背景矩形】
                $this->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
                $this->Rect($x > 10 ? ($pageWidth - 20) / 2 : $x, $y - 3, ($pageWidth) / 2, 20, 'F'); // 'F' = 填充
                $x = $x <= 10 ? $x + 5 : $x;
                $name = $item['name'];
                $this->setText($x, $y, $name);

                if (!empty($item['en_name'])) {
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($name);
                    $this->setText($x + $textWidth + 1, $y, $item['en_name'], '', 10, 'L', '', 102, 102, 102);
                }
                if (!empty($value)) {
                    $this->setText($x, $y + 8, $value, '', 12, 'L', 'B', 0, 0, 0);
                }
                $this->lastHeight = $this->GetY() + 6;
                break;
            case 'item_integrality':
                // 开机验证结果
            case 'item_files':
                // 文件对比结果
            case 'item_screen_verify':
                //  截屏对比结果
                if ($x <= $this->config['margin_left']) {
                    // 再顶上添加一个虚线
                    // 设置虚线样式
                    $this->SetLineStyle([
                        'width' => 0.2,
                        'cap' => 'butt',
                        'join' => 'miter',
                        'dash' => '2,2', // 虚线模式：线段长度,间隔长度
                        'color' => [
                            $this->config['rgba_line']['r'],
                            $this->config['rgba_line']['g'],
                            $this->config['rgba_line']['b']
                        ]
                    ]);

                    // 绘制虚线
                    $this->Line(10, $y - 4, $pageWidth - 10, $y - 4);

                    // 恢复原始线型
                    $this->SetLineStyle(
                        ['width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => [0, 0, 0]]
                    );
                }
                $name = $item['name'];
                $this->setText($x, $y, $name, '', 10, 'L', '', 102, 102, 102);

                if (!empty($item['en_name'])) {
                    // 计算文本宽度
                    $y = $y + 5;
                    $this->setText($x, $y, $item['en_name'], '', 10, 'L', '', 102, 102, 102);
                }
                if (!empty($value)) {
                    $this->setText($x, $y + 7, $value, '', 10, 'L', '', 0, 0, 0);
                }
                $this->lastHeight = $this->GetY();
                break;
            case 'item_open':
                //  开机验证图片
                $this->setText($x, $y, $item['name']);
                if (!empty($item['en_name'])) {
                    // 存在英文
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($item['name']);
                    // 右边增加一条竖线
                    $this->setLinev(13 + $textWidth, $y + 1, $y + 5);
                    // 接着右边放内容
                    $this->setText(10 + $textWidth + 5, $y, $item['en_name'], '', 12, 'L', '', 90, 90, 90);
                }
                $this->lastHeight = $this->GetY();
                // 跟着图片
                if ($value && file_exists($value)) {
                    // 最左侧
                    // 获取图片原始尺寸
                    $imageInfo = @getimagesize($value);
                    if ($imageInfo) {
                        list($imgWidth, $imgHeight) = $imageInfo;
                        // 目标宽度（例如：40mm）
                        $targetWidth = $pageWidth - 20;
                        // 计算等比例高度
                        $targetHeight = ($imgHeight / $imgWidth) * $targetWidth;
                        // 居中显示
                        $this->Image($value, 10, $y + 10, $targetWidth, $targetHeight);
                        $this->lastHeight += $targetHeight;
                    }
                }
                break;
            case 'item_document':
                //  文件比对列表
                $this->AddPage();
                $y = $this->config['margin_header'] + $this->config['margin_top'];

                $this->setText($x, $y, $item['name']);

                if (!empty($item['en_name'])) {
                    // 存在英文
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($item['name']);
                    // 右边增加一条竖线
                    $this->setLinev($x + 3 + $textWidth, $y + 1, $y + 5);
                    // 接着右边放内容
                    $this->setText($x + $textWidth + 5, $y, $item['en_name'], '', 12, 'L', '', 90, 90, 90);
                }
                if (!empty($item['en_value'])) {
                    // 存在描述
                    $this->setText(
                        $x,
                        $y + 7,
                        $item['en_value'],
                        $this->config['default_font'],
                        10,
                        'L',
                        '',
                        102,
                        102,
                        102
                    );
                }
                // 下面的是表格列表
                if (!empty($value)) {
                    $y += 25;
                    // 取第一个数组判断有那些  最少3列，最多7列
                    // 定义列宽（单位 mm）
                    $col2Width = 50;    // MD5
                    $col3Width = 15;    // 结果
                    $col4Width = !empty($value[0]['verify']['size']) ? 20 : 0;     // 大小
                    $col5Width = !empty($value[0]['verify']['attribute']) ? 20 : 0;     // 权限
                    $col6Width = !empty($value[0]['verify']['create_time']) ? 20 : 0;     // 创建时间
                    $col7Width = !empty($value[0]['verify']['modify_time']) ? 20 : 0;     // 修改时间
                    $totalWidth = 190;
                    $col1Width = $totalWidth - $col2Width - $col3Width - $col4Width
                        - $col5Width - $col6Width - $col7Width;   // 文件名

                    $col1Header = '生产&验证环境文件';
                    $col1Header2 = 'Source files & backup files';
                    $col2Header = 'MD5验证值';
                    $col2Header2 = 'MD5 Verification value';
                    $col3Header = '结果';
                    $col3Header2 = 'Result';
                    $col4Header = '文件大小';
                    $col4Header2 = 'Files Size';
                    $col5Header = '权限';
                    $col5Header2 = 'Permission';
                    $col6Header = '创建时间';
                    $col6Header2 = 'Create Time';
                    $col7Header = '修改时间';
                    $col7Header2 = 'Modify Time';

                    // 添加表头
                    $this->SetFillColor(249, 250, 251);
                    $this->Rect($x, $y - 3, $pageWidth - 20, 18, 'F');
                    // 设置字体
                    $this->SetFont($this->config['default_font'], '', 9);
                    // 【写入内容】
                    $this->SetXY($x, $y);
                    $this->MultiCell($col1Width, 6, $col1Header, 0, 'L', false);
                    if (!empty($item['en_name'])) {
                        $this->SetXY($x, $y + 4);
                        $this->MultiCell($col1Width, 6, $col1Header2, 0, 'L', false);
                    }

                    $this->SetXY($x + $col1Width, $y);
                    $this->MultiCell($col2Width, 6, $col2Header, 0, 'L', false);
                    if (!empty($item['en_name'])) {
                        $this->SetXY($x + $col1Width, $y + 4);
                        $this->MultiCell($col2Width, 6, $col2Header2, 0, 'L', false);
                    }

                    $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width + $col7Width, $y);
                    $this->MultiCell($col3Width, 6, $col3Header, 0, 'R', false);
                    if (!empty($item['en_name'])) {
                        $this->SetXY(
                            $x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width + $col7Width,
                            $y + 4
                        );
                        $this->MultiCell($col3Width, 6, $col3Header2, 0, 'R', false);
                    }

                    if ($col4Width > 0) {
                        $this->SetXY($x + $col1Width + $col2Width, $y);
                        $this->MultiCell($col4Width, 6, $col4Header, 0, 'L', false);
                        if (!empty($item['en_name'])) {
                            $this->SetXY($x + $col1Width + $col2Width, $y + 4);
                            $this->MultiCell($col4Width, 6, $col4Header2, 0, 'L', false);
                        }
                    }

                    if ($col5Width > 0) {
                        $this->SetXY($x + $col1Width + $col2Width + $col4Width, $y);
                        $this->MultiCell($col5Width, 6, $col5Header, 0, 'L', false);
                        if (!empty($item['en_name'])) {
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width, $y + 4);
                            $this->MultiCell($col5Width, 6, $col5Header2, 0, 'L', false);
                        }
                    }

                    if ($col6Width > 0) {
                        $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width, $y);
                        $this->MultiCell($col6Width, 6, $col6Header, 0, 'L', false);
                        if (!empty($item['en_name'])) {
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width, $y + 4);
                            $this->MultiCell($col6Width, 6, $col6Header2, 0, 'L', false);
                        }
                    }

                    if ($col7Width > 0) {
                        $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width, $y);
                        $this->MultiCell($col7Width, 6, $col7Header, 0, 'L', false);
                        if (!empty($item['en_name'])) {
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width, $y + 4);
                            $this->MultiCell($col7Width, 6, $col7Header2, 0, 'L', false);
                        }
                    }

                    // 更新 Y 位置
                    $y += 15;

                    $bgColor1 = [255, 255, 255]; // 白色
                    $bgColor2 = [245, 247, 250]; // 浅蓝

                    foreach ($value as $idx => $file) {
                        $fileName = htmlspecialchars($file['produce']['name'] ?? '');
                        $fileName2 = htmlspecialchars($file['verify']['name'] ?? '');
                        $md5 = htmlspecialchars($file['produce']['encryption'] ?? '');
                        $md52 = htmlspecialchars($file['verify']['encryption'] ?? '');
                        $status = htmlspecialchars($file['status_value'] ?? '');

                        $size = htmlspecialchars($file['produce']['size'] ?? '');
                        $size2 = htmlspecialchars($file['verify']['size'] ?? '');

                        $attribute = htmlspecialchars($file['produce']['attribute'] ?? '');
                        $attribute2 = htmlspecialchars($file['verify']['attribute'] ?? '');

                        $createTime = htmlspecialchars($file['produce']['create_time'] ?? '');
                        $createTime2 = htmlspecialchars($file['verify']['create_time'] ?? '');

                        $modifyTime = htmlspecialchars($file['produce']['modify_time'] ?? '');
                        $modifyTime2 = htmlspecialchars($file['verify']['modify_time'] ?? '');

                        // 计算实际高度（考虑换行）
                        $nameHeight = max(
                            $this->getStringHeight($col1Width, $fileName),
                            $this->getStringHeight($col2Width, $md5)
                        );
                        $maxHeight = $nameHeight * 2 + 5; // 行高

                        // 检查是否需要分页
                        if ($y + $maxHeight + 5 > $this->getPageHeight() - $this->getBreakMargin()) {
                            $this->AddPage();
                            $y = $this->config['margin_header'] + $this->config['margin_top'];
                            // 需要重新添加表头
                            // 添加表头
                            $this->SetFillColor(249, 250, 251);
                            $this->Rect($x, $y - 3, $pageWidth - 20, 18, 'F');
                            $this->SetTextColor(102, 102, 102);
                            // 设置字体
                            $this->SetFont($this->config['default_font'], '', 9);

                            // 【写入内容】
                            $this->SetXY($x, $y);
                            $this->MultiCell($col1Width, 6, $col1Header, 0, 'L', false);
                            if (!empty($item['en_name'])) {
                                $this->SetXY($x, $y + 4);
                                $this->MultiCell($col1Width, 6, $col1Header2, 0, 'L', false);
                            }

                            $this->SetXY($x + $col1Width, $y);
                            $this->MultiCell($col2Width, 6, $col2Header, 0, 'L', false);
                            if (!empty($item['en_name'])) {
                                $this->SetXY($x + $col1Width, $y + 4);
                                $this->MultiCell($col2Width, 6, $col2Header2, 0, 'L', false);
                            }


                            $this->SetXY(
                                $x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width + $col7Width,
                                $y
                            );
                            $this->MultiCell($col3Width, 6, $col3Header, 0, 'R', false);
                            if (!empty($item['en_name'])) {
                                $this->SetXY(
                                    $x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width + $col7Width,
                                    $y + 4
                                );
                                $this->MultiCell($col3Width, 6, $col3Header2, 0, 'R', false);
                            }

                            if ($col4Width > 0) {
                                $this->SetXY($x + $col1Width + $col2Width, $y);
                                $this->MultiCell($col4Width, 6, $col4Header, 0, 'L', false);
                                if (!empty($item['en_name'])) {
                                    $this->SetXY($x + $col1Width + $col2Width, $y + 4);
                                    $this->MultiCell($col4Width, 6, $col4Header2, 0, 'L', false);
                                }
                            }

                            if ($col5Width > 0) {
                                $this->SetXY($x + $col1Width + $col2Width + $col4Width, $y);
                                $this->MultiCell($col5Width, 6, $col5Header, 0, 'L', false);
                                if (!empty($item['en_name'])) {
                                    $this->SetXY($x + $col1Width + $col2Width + $col4Width, $y + 4);
                                    $this->MultiCell($col5Width, 6, $col5Header2, 0, 'L', false);
                                }
                            }

                            if ($col6Width > 0) {
                                $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width, $y);
                                $this->MultiCell($col6Width, 6, $col6Header, 0, 'L', false);
                                if (!empty($item['en_name'])) {
                                    $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width, $y + 4);
                                    $this->MultiCell($col6Width, 6, $col6Header2, 0, 'L', false);
                                }
                            }

                            if ($col7Width > 0) {
                                $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width, $y);
                                $this->MultiCell($col7Width, 6, $col7Header, 0, 'L', false);
                                if (!empty($item['en_name'])) {
                                    $this->SetXY(
                                        $x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width,
                                        $y + 4
                                    );
                                    $this->MultiCell($col7Width, 6, $col7Header2, 0, 'L', false);
                                }
                            }

                            // 更新 Y 位置
                            $y += 15;
                        }

                        // 【绘制背景】
                        $bgColor = ($idx % 2 == 0) ? $bgColor1 : $bgColor2;
                        $this->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
                        $this->Rect($x, $y - 2, $pageWidth - 20, $maxHeight, 'F');

                        if ($file['status'] == 2) {
                            // 不一致。字体用黄色
                            $this->setTextColor(234, 164, 14);
                        } else {
                            $this->setTextColor(0, 0, 0);
                        }

                        // 【写入内容】
                        $this->SetXY($x, $y);
                        $this->MultiCell($col1Width, 6, $fileName, 0, 'L', false);
                        $this->SetXY($x, $y + $nameHeight + 2);
                        $this->MultiCell($col1Width, 6, $fileName2, 0, 'L', false);

                        $this->SetXY($x + $col1Width, $y);
                        $this->MultiCell($col2Width, 6, $md5, 0, 'L', false);
                        $this->SetXY($x + $col1Width, $y + $nameHeight + 2);
                        $this->MultiCell($col2Width, 6, $md52, 0, 'L', false);

                        $this->SetXY(
                            $x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width + $col7Width,
                            $y + $nameHeight - 2
                        );
                        $this->MultiCell($col3Width, 6, $status, 0, 'R', false);

                        if ($col4Width > 0) {
                            $this->SetXY($x + $col1Width + $col2Width, $y);
                            $this->MultiCell($col4Width, 6, $size, 0, 'L', false);
                            $this->SetXY($x + $col1Width + $col2Width, $y + $nameHeight);
                            $this->MultiCell($col4Width, 6, $size2, 0, 'L', false);
                        }

                        if ($col5Width > 0) {
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width, $y);
                            $this->MultiCell($col5Width, 6, $attribute, 0, 'L', false);
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width, $y + $nameHeight);
                            $this->MultiCell($col5Width, 6, $attribute2, 0, 'L', false);
                        }

                        if ($col6Width > 0) {
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width, $y);
                            $this->MultiCell($col6Width, 6, $createTime, 0, 'L', false);
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width, $y + $nameHeight);
                            $this->MultiCell($col6Width, 6, $createTime2, 0, 'L', false);
                        }

                        if ($col7Width > 0) {
                            $this->SetXY($x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width, $y);
                            $this->MultiCell($col7Width, 6, $modifyTime, 0, 'L', false);
                            $this->SetXY(
                                $x + $col1Width + $col2Width + $col4Width + $col5Width + $col6Width,
                                $y + $nameHeight
                            );
                            $this->MultiCell($col7Width, 6, $modifyTime2, 0, 'L', false);
                        }

                        // 更新 Y 位置
                        $y += $maxHeight + 5;
                    }
                }
                $this->lastHeight = $this->GetY();
                break;
            case 'item_screen':
                //  截屏比对
                $this->AddPage();
                $y = $this->config['margin_header'] + $this->config['margin_top'];
                $this->setText($x, $y, $item['name']);
                if (!empty($item['en_name'])) {
                    // 存在英文
                    // 计算文本宽度
                    $textWidth = $this->GetStringWidth($item['name']);
                    // 右边增加一条竖线
                    $this->setLinev($x + 3 + $textWidth, $y + 1, $y + 5);
                    // 接着右边放内容
                    $this->setText($x + $textWidth + 5, $y, $item['en_name'], '', 12, 'L', '', 90, 90, 90);
                }
                // 下面的是截屏列表
                if (!empty($value)) {
                    // 数组渲染部分
                    $y += 10;
                    foreach ($value as $ki => $img) {
                        $ki++;
                        // 先渲染标题
                        $title = '第' . $ki . '组截屏';
                        $titleWidth = $this->GetStringWidth($title);
                        $left = ($pageWidth - $titleWidth) / 2;
                        $this->setText(
                            $left,
                            $y,
                            $title,
                            $this->config['default_font'],
                            12,
                            'C',
                            '',
                            0,
                            0,
                            0,
                            $titleWidth + 5
                        );

                        if ($img['product'] && file_exists($img['product'])) {
                            // 生产环境截图
                            $product = $img['product'];
                            // 获取图片原始尺寸
                            $imageInfo = @getimagesize($product);
                            if ($imageInfo) {
                                list($imgWidth, $imgHeight) = $imageInfo;
                                // 目标高度（例如：40mm）
                                $targetHeight = ($this->getPageHeight() - 120) / 2;
                                // 计算等比例宽度
                                $targetWidth = ($imgWidth / $imgHeight) * $targetHeight;
                                // 居中显示
                                $y += 10;
                                $this->Image(
                                    $product,
                                    ($pageWidth - $targetWidth) / 2,
                                    $y,
                                    $targetWidth,
                                    $targetHeight
                                );
                                $y += $targetHeight + 3;
                                $title = '生产环境截屏';
                                $titleWidth = $this->GetStringWidth($title);
                                $left = ($pageWidth - $titleWidth) / 2;
                                $this->setText(
                                    $left,
                                    $y,
                                    $title,
                                    $this->config['default_font'],
                                    12,
                                    'C',
                                    '',
                                    102,
                                    102,
                                    102,
                                    $titleWidth + 5
                                );
                                $y += 10;
                            }
                        }

                        if ($img['verify'] && file_exists($img['verify'])) {
                            // 验证环境截图
                            $product = $img['verify'];
                            // 获取图片原始尺寸
                            $imageInfo = @getimagesize($product);
                            if ($imageInfo) {
                                list($imgWidth, $imgHeight) = $imageInfo;
                                // 目标高度（例如：40mm）
                                $targetHeight = ($this->getPageHeight() - 120) / 2;
                                // 计算等比例宽度
                                $targetWidth = ($imgWidth / $imgHeight) * $targetHeight;
                                // 居中显示
                                $y += 5;
                                $this->Image(
                                    $product,
                                    ($pageWidth - $targetWidth) / 2,
                                    $y,
                                    $targetWidth,
                                    $targetHeight
                                );
                                $y += $targetHeight  + 3;
                                $title = '验证环境截屏';
                                $titleWidth = $this->GetStringWidth($title);
                                $left = ($pageWidth - $titleWidth) / 2;
                                $this->setText(
                                    $left,
                                    $y,
                                    $title,
                                    $this->config['default_font'],
                                    12,
                                    'C',
                                    '',
                                    102,
                                    102,
                                    102,
                                    $titleWidth + 5
                                );
                            }
                        }
                        // 添加比对结果
                        $result = '比对结果：' . ( $img['result'] == 1 ? '一致' : '不一致');
                        $y += 10;
                        $this->setText($x, $y, $result);
                        $y += 7;
                        $this->setText($x, $y, $img['remark']);

                        $this->AddPage();
                        $y = $this->config['margin_header'] + $this->config['margin_top'];
                    }
                }
                $this->lastHeight = $this->GetY();
                break;
        }
    }
}
