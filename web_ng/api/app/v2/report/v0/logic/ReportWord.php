<?php

namespace app\v2\report\v0\logic;

use xphp\Xword;

/**
 * note          报表导出word逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2026/3/10 18:55
 * @version      1.0.0
 * @copyright    Copyright 2026 vinchin.com
 */
class ReportWord
{
    /**
    * @param array  $array = [$item] 构造的位置数组 宽度最大为 644（即left+width）
     *                     $item = [
     *                     'type' => 'text', text和 image
     *                     'value' => '文字', 字符串或者图片绝对路径
     *                     'left' => 120, 距离左边的距离(px)
     *                     'top' => 20, 距离顶部的绝对定位(px)
     *                     'width' => 100, 内容宽度(px)
     *                     'height' => 20, 内容高度(px)
     *                     'font_size' => 12, 字体大小
     *                     'bold' => true, 加粗
     *                     'bg_color' => 'EEEEEE', 背景色(如 white 或 EEEEEE)
     *                     'color' => 'white', 字体色(如 white 或 EEEEEE)
     *                     'text_align' => 'left', 对齐方式（left right center）
     *                     'border' => true, 是否启用边框（布尔）
     *                     'border_size' => 2,  // 边框粗细（可选，默认1）
     *                     'border_color' => 'FF0000' // 边框颜色（可选，默认黑色）
     *                     ]
     * @param int    $mode  模式
     *                      0输出浏览器，1保存到指定位置
     * @param string $name  下载名称或者保存的绝对位置
     * @return array|string
     */
    public function makeWord(array $array, $mode = 0, $name = '')
    {

        $layoutManager = new Xword();
        $layoutManager->render($array);
        if ($mode == 1) {
            // 保存到指定位置
            return $layoutManager->saveToFile($name);
        } else {
            // 直接输出到浏览器
            return $layoutManager->outputToBrowser($name);
        }
    }
}
