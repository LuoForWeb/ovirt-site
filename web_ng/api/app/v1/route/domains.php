<?php

/**
 *  ### 用户管理 域服务器
 */

return [

    // 用户域服务器模块
    'user' => [ // 模块名
        'Domain' => [ // 类名
            'domains' => [ // 路由名
                // 请求方式   => 方法名,
                'get'  => 'getDomainList',   // 获取域服务器列表
                'post' => 'addDomainServer',  // 新建域服务器
                'put' => 'editDomainServer',  // 修改域服务器
                'delete' => 'deleteDomainServer',  // 删除域服务器
            ],
            'domains_list' => [
                'get' => 'getDomainSelectList',  // 获取活动目录列表
            ],
            'domains_info' => [
                'get' => 'getOldDomainServerInfo'
            ]
        ],
    ],
];
    ?>
