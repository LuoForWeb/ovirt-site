<?php

return [
    'resources' => [
        'NetworkPool' => [
            'network_pools' => [
                'get' => 'getNetworkPool',  // 获取网络资源池列表/获取网络资源池
                'post' => 'addNetworkPool',  // 添加网络资源池
                'put' => 'editNetworkPool',  // 修改网络资源池
                'delete' => 'deleteNetworkPool',  // 删除网络资源池
            ],
            'network_pools_batch' => [
                'delete' => 'batchDeleteNetworkPool', // 批量删除网络资源池
            ]
        ],
    ],
];
