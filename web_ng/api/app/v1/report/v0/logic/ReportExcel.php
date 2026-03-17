<?php

namespace app\v1\report\v0\logic;

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
    * @param array  $array 构造的位置数组
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
