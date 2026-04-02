<?php

// 定义XPHP根目录,可更改此目录
define('API_PATH', dirname(__DIR__) . '/');
// 定义一个版本
define('API_VERSION', '1.0-rev0');
// 定义一个版本号
define('API_VERSION_', 'v1');

// 加载基础文件
require_once API_PATH . 'xphp/base.php';

Xphp::start(true);
