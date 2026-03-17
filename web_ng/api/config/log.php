<?php

// +----------------------------------------------------------------------
// | 日志设置
// +----------------------------------------------------------------------
return [
    // 默认日志记录通道
    'default'      => 'file',
    // 记录日志错误类型 空表示所有的都记录
    //'level' => [E_ERROR, E_WARNING, E_PARSE, E_NOTICE, E_CORE_ERROR, E_CORE_WARNING,
    // E_COMPILE_ERROR, E_COMPILE_WARNING, E_USER_ERROR, E_USER_WARNING,
    // E_USER_NOTICE, E_STRICT, E_RECOVERABLE_ERROR, E_ALL],
    'level' => [],
    // 日志通道列表
    'channels'     => [
        'file' => [
            // 日志记录方式
            'type'           => 'File',
            // 日志保存目录
            'path'           => LOG_PATH . getXphpVersion() . '/',
            //单个日志文件的大小限制，超过后会自动记录到第二个文件
            'file_size'     => 2097152,
            //日志的时间格式，默认是` c `
            'time_format'   => 'c',
            // error和sql日志单独记录
            'apart_level'   =>  ['error','sql'],
        ],
        // 其它日志通道配置
    ],


];
