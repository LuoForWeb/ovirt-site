<?php

/**
 * 配置文件demo
 */

return [
    // 节点的角色
    'NODE_ROLE' => [
        'UNKNOWN' => 0,
        'MASTER' => 1,  // 主节点
        'BACKUP' => 2,  // 子节点
    ],
    // 集群的配置模式
    'CLUSTER_CONFIG_MODE' => [
        'UNKNOWN' => 0,
        'INIT' => 1,    // 初始化配置
        'MODIFY' => 2,  // 修改配置
    ],
    'NODE_ROLE_DES' => [
        0 => 'UNKNOWN',
        1 => xphp_get_lang('WEB_CLUSTER_ROLE_MASTER_NODE'),
        2 => xphp_get_lang('WEB_CLUSTER_ROLE_BACKUP_NODE')
    ],
    // 集群的状态
    'CLUSTER_STATUS' => [
        'UNKNOWN' => 0,
        'STOPPED' => 1,  // 已停止
        'RUNNING' => 2,  // 运行中
        'IN_TROUBLE' => 3,  // 故障
        'STARTING' => 4,  // 启动中
        'STOPPING' => 5,  // 停止中
        'SWITCHING' => 6,  // 切换中
        'TAKEOVER' => 7,  // 接管中
    ],
    'CLUSTER_STATUS_DES' => [
        0 => 'UNKNOWN',
        1 => xphp_get_lang('WEB_CLUSTER_STATUS_STOPPED'),
        2 => xphp_get_lang('WEB_CLUSTER_STATUS_RUNNING'),
        3 => xphp_get_lang('WEB_CLUSTER_STATUS_IN_TROUBLE'),
        4 => xphp_get_lang('WEB_CLUSTER_STATUS_STARTING'),
        5 => xphp_get_lang('WEB_CLUSTER_STATUS_STOPPING'),
        6 => xphp_get_lang('WEB_CLUSTER_STATUS_SWITCHING'),
        7 => xphp_get_lang('WEB_CLUSTER_STATUS_TAKEOVER'),
    ],
];
