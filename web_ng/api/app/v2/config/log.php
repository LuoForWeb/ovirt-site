<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
return [
    /**日志信息 系统原来的日志**/
    'LOG_INFO' => array(
        //是否开启日志,开启日志后不写入INFO,NOTICE,WARNING日志,ERROR日志会照常写入
        'DEBUG' => false,
        //日志路径
        'PATH' => '/var/log/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/web/log',
        //日志等级
        'INFO' => 1,
        'NOTICE' => 2,
        'WARNING' => 3,
        'ERROR' => 4,
    ),

    //日志类型
    'LOGTYPE' => array(
        'UNKNOWN' => 0,
        'TASK' => 1,
        'SYSTEM' => 2,
    ),

    //tmp path
	'TMP_PATH' => '/usr/share/nginx/' . (getEnvs() ? 'vinchin' : '@VENDOR@') . '/tmp/',

    //日志等级
    'LOGLEVEL' => array(
        'UNKNOWN' => 0,
        'NORMAL' => 1,
        'WARN' => 2,
        'ERROR' => 3,
    ),
    //后台系统日志
    'LOG_PATH' => '/var/log/' . (getEnvs() ? 'vinchin' : '@VENDOR@'),

];
