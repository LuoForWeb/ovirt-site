<?php

return [
    'tenant' => [
        //租户管理
        'Tenant' => [
            'tenant' => [
                'post' => 'addTenant',//添加租户
                'put' => 'editTenant',//修改租户信息
                'get' => 'getTenant',  //租户列表/租户详情
                'delete' => 'deleteTenant' //删除租户
            ],
			'tenant_edit' => [
                'get' => 'getTenantEditInfo',//获取租户需要修改的信息
            ],
            'tenant_enable' => [
                'post' => 'enableTenant' //启用租户
            ],
            'tenant_disable' => [
                'post' => 'disableTenant' //禁用租户
            ],
            'tenant_host' => [
                'get' => 'getRecoverHost'//获取恢复目的地
            ],
            'tenant_host_sync' => [
                'get' => 'getSyncRecoveryVcenter'//异步获取vcenter信息
            ],
            'tenant_storage' => [
                'get' => 'getStorage'//得到备份存储
            ],
            'tenant_user_list' => [
                'get' => 'getTenantUserList'//获取租户拥有用户列表
            ],
            'tenant_auth_num' => [
                'get' => 'getModulesLisenceValid'//获取所有模块可用数量
            ],
            'tenant_check_name' => [
                'get' => 'tenantNameAvailable'//检查租户是否注册
            ],
            'tenant_free_size' => [
                'get' => 'getSystemFreeStorage'//获取可以给租户分配的剩余容量
            ],
        ],
        'TenantHomePage' => [
            'tenant_homepage_info' => [
                'get' => 'getHomePageInfo',
            ],

        ]
    ],

];
