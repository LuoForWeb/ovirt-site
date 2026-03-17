<?php

return [
    'dbtype' => 'mysql',
    // ProxySQL连接配置
    'proxy' => [
        'socket' => '/var/lib/proxysql/proxysql.sock', // ProxySQL的Unix Socket
        'host' => '127.0.0.1',                        // ProxySQL的TCP主机
        'port' => '3308',                             // ProxySQL的TCP端口
    ],
    // 直连数据库配置
    'direct' => [
        'socket' => '/var/lib/mysql/mysql.sock',      // 直连的Unix Socket
        'host' => '127.0.0.1',                        // 直连的TCP主机
        'port' => '3306',                             // 直连的TCP端口
    ],
    'user' => 'vinchin',
    'pass' => 'Database@2015',
    'dbname' => 'vinchin_db',
    'charset' => 'utf8mb4',
];
