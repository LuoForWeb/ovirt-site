<?php

namespace app\v1\db\v0\logic;

use xphp\Xpdf;

/**
 * note          数据库任务信息-继承xpdf基类
 * @author       chenchao@vinchin.com
 * @date         2025/12/31 17:06
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class DbJobInfoPdf extends Xpdf
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
        $this->reportTitle = $title;
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
        switch ($item['type']) {
            case 'base64_image':
                $this->Image(
                    '@' . base64_decode($item['value']),
                    $item['left'],
                    $item['top'],
                    $item['width'],
                    $item['height']
                );
                break;
            case 'text':
                $this->setCellHeightRatio(1.25);
                $this->setText(
                    $item['left'],
                    $item['top'],
                    $item['value'],
                    '',
                    $item['font_size'],
                    $item['align'] ?? 'L',
                    $item['font_style'],
                    $item['r'],
                    $item['g'],
                    $item['b'],
                    $item['width']
                );
                break;
            case 'html':
                $this->setXY($item['left'], $item['top']);
                if ($item['height']) {
                    $this->setCellHeightRatio($this->pxToMm($item['height']));
                }
                $this->writeHTML($item['value']);
                break;
            case 'html_cell':
                $this->setCellHeightRatio($this->pxToMm($item['height']));
                $this->writeHTMLCell($item['width'], $item['height'], $item['left'], $item['top'], $item['value'], '', 0, 0, false, 'L');
                break;
            case 'page':
                $this->AddPage();
                break;
        }
    }
}
