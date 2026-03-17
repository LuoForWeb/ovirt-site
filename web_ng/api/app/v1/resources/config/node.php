<?php

/**
 * 节点配置
 */

return [
    // 节点操作类别
    'NODE_OPERATE_TYPE' => [
        'add' => 1,     // 添加
        'modify' => 2,  // 修改
        'delete' => 3,  // 删除
    ],
    // 节点操作状态
    'NODE_OPERATE_STATUS' => [
        'UNKNOWN' => 0,  // 未操作
        'DELETING' => 1,  // 删除中
        'MODIFYING' => 2, // 修改中
        'UPGRADING' => 3, // 升级中
        'OFFLINE' => 4,   // 离线
        'UNREACHABLE' => 5, // 不可达，网络不可达
    ],
    // 节点缓存类别
    'NODE_CACHE_TYPE' => [
        'local_dir' => 1,  // 本地目录
        'storage_device' => 2,  // 存储设备
    ],
    // 节点缓存切换策略
    'NODE_CACHE_SWITCH_STRATEGY' => [
        'manual' => 1,  // 手动切换
        'auto' => 2,  // 自动切换
    ],
    // 节点网络类别
    'NETWORK_TYPE' => [
        'default' => 1, // 本地默认
        'map' => 2,  // 映射
    ],// 禁止时间类别
    'PROHIBIT_TIME_TYPE' => [
        'UNKNOWN' => 0,  // 为止
        'DAY' => 1,  // 每天
        'WEEK' => 2,  // 每周
        'MONTH' => 3,  // 每月
        'CUSTOM' => 4,  // 用户自定义
    ],
    'PROHIBIT_TIME_TYPE_DES' => [
        0 => 'unknown',
        1 => xphp_get_lang('UI_STRATEGY_EVERY_DAY'),
        2 => xphp_get_lang('UI_STRATEGY_EVERY_WEEK'),
        3 => xphp_get_lang('UI_STRATEGY_EVERY_MONTH'),
        4 => xphp_get_lang('UI_STRATEGY_CUSTOM'),
    ],
    // 节点功能
    'NODE_FUNCTION' => [
        'UNKNOWN' => 0,
        'MANAGEMENT' => 1, // 管理
        'CALCULATION' => 2, // 计算
        'MANAGEMENT_CALCULATION' => 3, // 管理+计算
    ],
];
