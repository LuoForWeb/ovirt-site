<?php
header("Content-Type:text/html;charset=utf-8");  //设置系统的输出字符为utf-8

//TODO这里需要获取vinchin
defined('API_PATH') or define('API_PATH', dirname(dirname(__FILE__)) . "/");
defined('XPHP_PATH') or define('XPHP_PATH', API_PATH.'xphp/');	            //框架目录
defined('CONF_PATH') or define('CONF_PATH', XPHP_PATH."conf/");            //配置文件目录
defined('APP_PATH') or define('APP_PATH', API_PATH.'app/');					//应用程序目录
defined('MONITOR_PATH') or define('MONITOR_PATH', API_PATH.'billing_monitor/');//应用程序目录
defined('ROOT_PATH') or define('ROOT_PATH', substr(API_PATH, 0, -5));		//web根目录
defined('LANG_PATH') or define('LANG_PATH', ROOT_PATH.'/lang/');				//语言包目录

require_once API_PATH.'xphp/Xphp.class.php';


Xphp::billingMonitorStart();

