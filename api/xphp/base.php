<?php

header('Content-Type:text/html;charset=utf-8');  // 设置系统的输出字符为utf-8
// header('Access-Control-Allow-Origin: *'); // *代表允许任何网址请求
header('Access-Control-Allow-Credentials:true'); // 跨域资源共享
header("Access-Control-Allow-Methods: POST, GET, PATCH, PUT, DELETE, OPTIONS"); // 允许请求的方法
header('Access-Control-Allow-Headers: x-api-version, Authorization, X-Csrf-Token'); // 允许请求的header

if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
    die();
}

define('XPHP_START_TIME', microtime(true));
define('XPHP_START_MEM', memory_get_usage());
define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);
define('TIMESTAMP', time());

// 框架目录
defined('XPHP_PATH') or define('XPHP_PATH', API_PATH . 'xphp/');

// 配置文件目录
defined('CONF_PATH') or define('CONF_PATH', API_PATH . 'config/');

// 路由配置文件目录
defined('ROUTE_PATH') or define('ROUTE_PATH', API_PATH . 'route/');

// 应用程序目录
defined('APP_PATH') or define('APP_PATH', API_PATH . 'app/');

// 语言包目录
defined('LANG_PATH') or define('LANG_PATH', API_PATH . 'lang/');

// 定义data目录
defined('DATA_PATH') or define('DATA_PATH', API_PATH . 'data/');

// 定义缓存目录
defined('RUNTIME_PATH') or define('RUNTIME_PATH', DATA_PATH . 'runtime/');

// 定义日志目录
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log/');

// 定义缓存目录
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache/');

defined('ENV_PREFIX') or define('ENV_PREFIX', 'PHP_'); // 环境变量的配置前缀

// 加载环境变量配置文件
if (is_file(API_PATH . '.env')) {

    $env = parse_ini_file(API_PATH . '.env', true);
    foreach ($env as $key => $val) {
        $name = ENV_PREFIX . strtoupper($key);
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $item = $name . '_' . strtoupper($k);
                putenv("$item=$v");
            }
        } else {
            putenv("$name=$val");
        }
    }
}

// 加载composer扩展
require_once API_PATH . 'vendor/autoload.php';

require_once API_PATH . 'xphp/Xphp.class.php';
