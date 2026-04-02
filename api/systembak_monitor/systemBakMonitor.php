<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-04-14 10:14:14
 * @LastEditTime: 2025-05-07 10:55:08
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */
header("Content-Type:text/html;charset=utf-8");  //设置系统的输出字符为utf-8

// 加载web_ng
$rootPath = dirname(__DIR__, 2) . '/';
require_once '/usr/share/nginx/vinchin/web_ng/api/public/load.php';

require_once dirname(__DIR__) . '/systembak_monitor/SystemBakMonitor.class.php';

new SystemBakMonitor();