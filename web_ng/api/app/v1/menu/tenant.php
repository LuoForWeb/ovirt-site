<?php

/**
* 这是 租户管理对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 租户
    [
        'name' => 'tenant_manager',
        'path' => './content/platform/tenant/tenant_manager.php',
        'class' => 'viconfont vicon-tenant',
        'title' => 'UI_PLATFORM_TENANT',
        'level' => 1,
        'child' => [
            // 租户信息
            [
                'name' => 'p_tenant_view',
                'path' => './content/platform/tenant/tenant.php?uuid=' . xphp_get_user_info()['tenantuuid'],
                'class' => 'iconfont icon-zuhu',
                'title' => 'UI_PLATFORM_TENANT_INFO',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_p_tenant_view_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 新建
            [
                'name' => 'p_tenant_manager_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_tenant_manager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_tenant_manager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 启用
            [
                'name' => 'p_tenant_manager_enable',
                'title' => 'WEB_PLATFORM_ENABLE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 禁用
            [
                'name' => 'p_tenant_manager_disable',
                'title' => 'WEB_PLATFORM_DISABLE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 计费
    [
        'name' => 'billing_manager',
        'path' => './content/platform/billing/billing_manager.php',
        'class' => 'viconfont vicon-billing_manager',
        'title' => 'UI_PLATFORM_BILLING',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_billing_manager_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => 'p_billing_manager_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_billing_manager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_billing_manager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 启用
            [
                'name' => 'p_billing_manager_enable',
                'title' => 'WEB_PLATFORM_ENABLE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 禁用
            [
                'name' => 'p_billing_manager_disable',
                'title' => 'WEB_PLATFORM_DISABLE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 组织结构
    [
        'name' => 'organization_manager',
        'path' => './content/platform/organization/organization_manager.php',
        'class' => 'viconfont vicon-organization_manager',
        'title' => 'UI_PLATFORM_ORGANIZATION',
        'level' => 1,
        'child' => [
            // 组织管理
            [
                'name' => 'p_organization_manager_om',
                'title' => 'UI_ORGAN_MANAGE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 用户管理
            [
                'name' => 'p_organization_manager_um',
                'title' => 'UI_ORGAN_USER_MANAGE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]

    ],
];
