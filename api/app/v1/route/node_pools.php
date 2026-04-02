<?php

/**
 * 计算资源池
 */

return [
    'resources' => [
        'NodePool' => [  // 计算资源池
            'node_pools' => [
                'get' => 'getNodePool',  // 获取计算资源池列表/获取计算资源池详情
                'post' => 'addNodePool',  // 添加计算资源池
                'put' => 'editNodePool',  // 编辑计算资源池
                'delete' => 'deleteNodePool', // 删除计算资源池
            ],
            'node_pools_batch' => [
                'delete' => 'batchDeleteNodePool',  // 批量删除计算资源池
            ],
        ]
    ]
];
