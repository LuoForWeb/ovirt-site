<?php

/**
* 虚拟机管理
 */

return [

   ### 虚拟机管理
    'resources' => [ // 模块名
        'Machine' => [ // 类名
            ### 虚拟机
            'virtual' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getMachines',       // 获取主机列表、信息
                'post'      => 'editMachine',       // 修改主机信息
                'delete'    => 'delMachine',        // 删除主机
            ],
            'virtual_refresh' => [
                'get'       => 'refreshMachines',   // 刷新主机
            ],
            'virtual_operate' => [
                'post'       => 'operateMachines',   // 操作主机
            ],
            ### 资源隔离
            'virtual_resources' => [
                'get'       => 'getResources',       // 获取资源隔离
                'post'      => 'editResources',      // 保存资源隔离
            ],
            ### 网络管理
            'virtual_network' => [
                'get'       => 'getNetworks',        // 网络管理列表、信息
                'post'      => 'editNetworks',       // 添加、修改网络
                'delete'    => 'delNetworks',        // 删除网络
            ],
            'virtual_netcard' => [
                'get'       => 'getNetCard',   // 获取虚拟网络添加时的所有桥接网卡列表
            ],
            ### 日志管理
            'virtual_logs' => [
                'get'       => 'getLogs',           // 获取操作日志
                'delete'    => 'delLogs',           // 删除日志
            ],
            ### 获取备份系统iso/光盘列表
            'virtual_tree' => [
                'get'       => 'getDiskLists',       // 获取列表
            ],
            'virtual_uuid' => [
                'get'       => 'getUuid',       // 获取新的mac地址
            ],
            'virtual_default' => [
                'get'       => 'getDefault',       // 获取创建虚拟机默认的一些信息
            ],
            # 节点资源信息列表
            'virtual_node_source' => [
                'get'       => 'getNodeSource',       // 获取备份系统节点资源隔离列表
                'post'      => 'setNodeSourceVt',     // 更改资源列表的是否使用VT
            ],
            'virtual_statist' => [
                'get'       => 'getStatistInfo',       // 获取虚拟机和计算资源的一些统计信息
            ],
            'virtual_proxy' => [
                'get'       => 'getProxyList',       // 代理网关列表
            ],
        ],
    ]
];
