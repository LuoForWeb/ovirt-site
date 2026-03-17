<?php

/**
* cahce的一些配置
 */

return [

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'default' => 'file', // 默认缓存类型 file redis memcache memcached

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => CACHE_PATH,
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 3600,
        ],
        'redis'    => [
            'type'       => 'Redis',
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '',
            'select'     => 0, //redis库
            'timeout'    => 3600, //默认缓存时间(秒)
            'expire'     => 7200, // 缓存有效期 0表示永久缓存(秒)
            'persistent' => false, //是否长连接 false短连接
            'prefix'     => '', // 缓存前缀
        ],
        'memcache' => [
            'type'       => 'Memcache',
            'host'       => '127.0.0.1',
            'port'       => 11211,
            'expire'     => 0, // 缓存有效期 0表示永久缓存
            'timeout'    => 0, // 超时时间（单位：毫秒）
            'persistent' => false, //是否长连接 false短连接
            'prefix'     => '', // 缓存前缀
        ],
        'memcached' => [
            'type'       => 'Memcached',
            'host'       => '127.0.0.1',
            'port'       => 11211,
            'username'   => '', //账号
            'password'   => '', //密码
            'expire'     => 0, // 缓存有效期 0表示永久缓存
            'timeout'    => 0, // 超时时间（单位：毫秒）
            'prefix'     => '', // 缓存前缀
            'option'   => [],
        ],
    ],



];
