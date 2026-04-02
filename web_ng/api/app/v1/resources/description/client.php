<?php

/**
 * 描述信息
 */

return [
    // 客户端通信模式
    'AGENT_NET_MODEL_DES' => [
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_CLIENT_SERVER_TO_CLIENT'),
        ('UI_CLIENT_CLIENT_TO_SERVER'),
    ],
    // 代理类别
    'AGENT_TYPE_DES' => [
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_CLIENT_AGENT_TYPE_CLIENT'),
        ('UI_VOL_CDP_MEMORY_OS'),
        'NAS',
        ('UI_CLIENT_AGENT_TYPE_TRANSPORT'),
    ],
    // 授权模块
    'AUTH_MODULE_FUZZY' => [
        1 => 'file',
        2 => 'database',
        3 => 'os',
    ],
];
