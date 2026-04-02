<?php

/**
 * 配置文件病毒库
 */

return [
    // 病毒库类型
    'VIRUS_TYPE' => [
        'UNKNOWN' => 0,  // 未知
        'KAV' => 1,  // 卡巴斯基
        'CLAMAV' => 2,  // clamav
    ],
    // 病毒库授权状态
    'VIRUS_AUTHORIZATION_STATUS' => [
        'UNKNOWN' => 0,  // 未知
        'AUTHORIZED' => 1,  // 已授权
        'UNAUTHORIZED' => 2,  // 未授权
        'EXPIRED' => 3,  // 已过期
    ],
    // 病毒库操作
    'VIRUS_OP_TYPE' => [
        'UNKNOWN' => 0,  // 未知
        'SET_DEFAULT' => 1,  // 设置默认
        'UNSET_DEFAULT' => 2,  // 取消默认
        'REFRESH' => 3,  // 刷新
    ],
];
