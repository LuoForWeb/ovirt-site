<?php

/**
 * 客户端配置
 */

return [
    // 操作系统类型
    'AGENT_OS_TYPE' => [
        'UNKONWN' => 0,
        'WINDOWS' => 1,
        'RHEL6' => 2,
        'RHEL7' => 3,
        'RHEL8' => 4,
        'UBUNTU' => 5,
        'DEBIAN' => 6,
        'KYLIN' => 7,
        'UNIONTECH' => 8,
        'KYLINX86' => 9,
        'ZKFD-V4' => 10,
        'UNIONTECHX86' => 11,
        'ANOLISOSX64' => 12
    ],
    // 客户端添加类型
    'AGENT_ADD_MODE' => [
        'manual' => 1, // 手动添加
        'deployment' => 2, // 远程部署
    ],
    // 客户端添加方式
    'AGENT_ADD_TYPE' => [
        'single' => 1, // 单个添加
        'multiple' => 2, // 多个添加
    ],
    // 客户端通信模式
    'AGENT_NET_MODEL' => [
        'server_to_client' => 1, // 服务端连接客户端
        'client_to_server' => 2, // 客户端连接服务端
    ],
    // 客户端分组类别
    'AGENT_GROUP_TYPE' => [
        'system' => 1, // 系统分组
        'custom' => 2, // 用户新建的分组
    ],
    // MySQL认证类别
    'MYSQL_AUTH_TYPE' => [
        'tcp' => 1, // tcp/ip认证
        'sock' => 2, // sock文件认证
    ],
    // SQL SERVER认证类型
    'SQL_SERVER_AUTH_TYPE' => [
        'windows' => 1, // windows认证
        'user' => 2, // 用户认证
    ],
    // Oracle认证类型
    'ORACLE_AUTH_TYPE' => [
        'UNKNOWN' => 0,
        'OS_AUTH' => 1,
        'DATABASE_AUTH' => 2,
    ],
    //代理类型
    'AGENT_TYPE' => [
        'UNKNOWN' => 0,
        'NORMAL' => 1,  //normal
        'MEMORY_OS' => 2,  //memory operating system(livecd/winpe)
        'NAS' => 3, //nas
        'APPLIANCE' => 4, // vm transport agent
        'KUBE' => 5, // kubernetes
    ],
    // 文件树选择模式
    'FILE_TREE_SELECT_MODE' => [
        'FILE' => 1,  // 文件
        'DIRECTORY' => 2,  // 目录
        'MIXED' => 3,  // 混合
    ],
];
