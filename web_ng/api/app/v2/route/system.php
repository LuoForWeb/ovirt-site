<?php

return [

    ### 系统管理
    'system' => [ // 模块名
        'Index' => [
            'system_config_base_info' => [
                'get' => 'getConfig', //获取系统的基本信息
            ],
        ],
        'Menu' => [
            // 菜单相关接口
            'system_menu' => [
                'get' => 'getMenuList', // 获取菜单列表
            ],
            // 系统首页url
            'system_homepage' => [
                'get' => 'getHomePage', // 获取系统首页url
            ],
            'system_menus' => [
                'get' => 'downMenuList', // export菜单列表
            ],
        ],
        'Network' => [
            // 网络配置
            'system_network_ip' => [
                'get' => 'getNetwork', // 获取所有网卡/单个网卡信息
                'put' => 'setNetwork', // 设置网卡信息
            ]
        ],
    ],

];
