<?php

header("Content-Type:text/html;charset=utf-8");  //设置系统的输出字符为utf-8

// 加载web_ng
$rootPath = dirname(__DIR__, 2) . '/';
require_once $rootPath .'web_ng/api/public/load.php';

require_once dirname(__DIR__) . '/monitor/Monitor.class.php';
// 完成实例化
$monitor = new Monitor();
$monitor->run(); // 不再在 __construct 里死循环
