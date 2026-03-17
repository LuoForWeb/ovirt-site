<?php

/**
 * 所有页面层级关系,用于顶部和左边树形展示
 */

return [

    // 系统首页
    [
        'name' => 'homepage',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-homepage',
        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_HOMEPAGE',
        'level' => 0,
    ],

    // 监控中心
    [
        'name' => 'monitor',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-monitor',
        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_MONITOR_CENTER',
        'level' => 0,
    ],

    // 备份
    [
        'name' => 'backup',
        'path' => 'javascript:;',
        'class' => "viconfont vicon-backup",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DATA_BACKUP",
        'level' => 0,
        'is_operate' => 1,
    ],
    // 连续数据保护
    [
        'name' => "vol_cdp_protect",
        'path' => "javascript:;",
        'class' => "viconfont vicon-vol_cdp_protect",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_CDP_PROTECT",
        'level' => 0,
    ],

    // 复制容灾
    [
        'name' => "copy",
        'path' => "javascript:;",
        'class' => "viconfont vicon-fuzhi",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DATA_COPY",
        'level' => 0,
        'is_operate' => 1,
    ],

    // 备份数据管理
    [
        'name' => "data_manager",
        'path' => "javascript:;",
        'class' => "viconfont vicon-shujuguanli",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DATA_MANAGER",
        'level' => 0,
    ],

    // 资源管理
    [
        'name' => "resmanagement",
        'path' => "javascript:;",
        'class' => "viconfont vicon-resmanagement page-vm-infrastructure_en",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RESOURCE_MANAGER",
        'level' => 0,
    ],

    // 系统管理
    [
        'name' => "sysmanagement",
        'path' => "javascript:;",
        'class' => "viconfont vicon-sysmanagement",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_MANAGER",
        'level' => 0,
    ],

    // 全局观察者
    [
        'name' => 'global_observer',
        'path' => '',
        'class' => 'fa fa-eye',
        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_GLOBAL_OBSERBER',
        'level' => 0,
    ]
];
