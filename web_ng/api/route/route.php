<?php

/**
 *  这里面存放的当前版本里面的路由的映射关系
 * 如 v1/vm/lists 说明：如果 v1版本后面跟着多个资源标识 那么就以 英文下划线 _ 隔开组合作为健名
 * 'vm_lists' => [
 *   'get' => ['module' => 'vm', 'class' => 'Index', 'method' => 'getData'],
 *  ],
 * homes表示url地址的资源，get是请求方式（统一小写,共有 get post put delete patch 5种方式 ）,
 * module 表示模块目录，class表示控制器名称（不含Controller那一部分），method表示具体的方法
 * 还有一种
 *
'demo' => [ // 模块名
    'Index' => [ // 类名
        'url' => [ // 路由名
            // 请求方式   => 方法名,
            'get'       => 'getDemo',   // 获取
        ],
        // ... 更多路由名称
    ],
    // ... 更多类名
],
 * 具体请求到哪个版本，需要看url地址栏的大版本号和请求头里面的 x-api-version 参数； 1.0-rev0，1.0表示v1的 v0版本，rev0是v1下面的v0版本的小版本号
 *
 *  1. GET（SELECT）：从服务器取出资源（一项或多项）；
    2. POST（CREATE）：在服务器新建一个资源；
    3. PUT（UPDATE）：在服务器更新资源（客户端提供改变后的完整资源）；
    4. PATCH（UPDATE）：在服务器更新资源（客户端提供改变的属性）；
    5. DELETE（DELETE）：从服务器删除资源；
 *
 */

return [

    'demo' => [ // 模块名
        'Index' => [ // 类名
            'url' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getDemo',   // 获取
            ],
            // ... 更多路由名称
        ],
        // ... 更多类名
    ],


    'url2' => [  // 路由名
        'get'  => [ // 请求方式
            'module' => 'demo',  // 模块名
            'class' => 'Index', // 类名
            'method' => 'getDemo' // 方法名
        ],
    ],


];
