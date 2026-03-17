<?php

namespace app\v2\report\v0\logic;

use xphp\Xexcel;

/**
 * note          报表导出excel逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2026/1/5 11:19
 * @version      1.0.0
 * @copyright    Copyright 2026 vinchin.com
 */
class ReportExcel
{
    /**
    * @param array  $array = [$item]构造的位置数组
     *                     $item = [
     *                     'type' => 'text', // text和 image
     *                     'value' => '文字',// 字符串或者图片绝对路径
     *                     "row" => 1, // 第几行
     *                     "col" => A, // 第几列
     *                     "bg_color" => "blue", // 背景色(如 white 或 EEEEEE)
     *                     'color' => 'white', 字体色(如 white 或 EEEEEE)
     *                     'text_align' => 'left', 对齐方式（left right center）
     *                     'width' => 100, 内容宽度(px)
     *                     'height' => 20, 内容高度(px)
     *                     'font_size' => 12, 字体大小
     *                     'bold' => true, 加粗
     *                     'border' => [
     *                     'color' => '000000',
     *                     //'top' => 'thick', Xexcel类定义的$borderStyles 映射
     *                     //'top_color' => '000000',
     *                     //'bottom' => 'thick',
     *                     //'bottom_color' => '000000',
     *                     //'left' => 'dashed',
     *                     //'left_color' => '000000',
     *                     //'right' => '000000',
     *                     //'right_color' => 'medium',
     *                     ],
     *                     "border" => "thin" // 也支持整体定义
     *                     ]
     * @param int    $mode  模式
     *                      0输出浏览器，1保存到指定位置
     * @param string $name  下载名称或者保存的绝对位置
     * @return array|string
     */
    public function makeExcel(array $array, $mode = 0, $name = '')
    {

        $layoutManager = new Xexcel();
        $layoutManager->render($array);

        // 导出
        return $layoutManager->outputExcel($mode, $name);
    }
}
