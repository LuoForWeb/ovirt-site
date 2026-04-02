<?php

/**
 * 备份节点
 */

return [

    ### 备份节点
    'resources' => [ // 模块名
        ### 备份节点
        'Group' => [ // 类名
            'resources_group' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getResource',   // 获取资源组
                'delete'       => 'deleteResource',   // 获取资源组
                'post'       => 'editResource', //修改资源组
            ],
            'resources_source' => [
                'get'   => 'getAllResource', //获取全部资源
            ],
            'resources_groupDetail' => [
                'get'       => 'getDetail'//获取某个资源组详细资源
            ],
            'resources_group_add' => [
                'post'         => 'addResource', //添加资源组
            ],
        ],
    ]
];
