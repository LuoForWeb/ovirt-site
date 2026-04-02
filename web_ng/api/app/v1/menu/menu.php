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
        'title' => 'UI_PLATFORM_HOMEPAGE',
        'level' => 0,
    ],

    // 监控中心
    [
        'name' => 'monitor',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-monitor',
        'title' => 'UI_PLATFORM_MONITOR_CENTER',
        'level' => 0,
    ],

    // 虚拟机保护
    [
        'name' => 'vmprotect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-vmprotect',
        'title' => 'UI_PLATFORM_VM_PROTECT',
        'level' => 0,
    ],

    // 公有云保护
    [
        'name' => 'awsprotect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-yunpingtai',
        'title' => 'UI_PLATFORM_AWS_PROTECT',
        'level' => 0,
    ],

    // 私有云保护
    [
        'name' => 'prcloud_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-cloud_platform',
        'title' => 'UI_PLATFORM_PRIVATE_CLOUD_PROTECT',
        'level' => 0,
    ],

    // 主机保护
    [
        'name' => 'host_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-host_protect',
        'title' => 'UI_PLATFORM_HOST_PROTECT',
        'level' => 0,
    ],

    // m365
    [
        'name' => 'application_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-yingyong',
        'title' => 'UI_PLATFORM_APPLICATION',
        'level' => 0,
    ],

    // hadoop
    [
        'name' => 'hadoop_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-a-Elephantdaxiang-01',
        'title' => 'WEB_HADOOP_PROTECT',
        'level' => 0,
    ],
    // 实时容灾保护
    [
        'name' => 'vol_cdp_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-vol_cdp_protect',
        'title' => 'UI_PLATFORM_VOL_CDP',
        'level' => 0,
        'showChild' => true,
    ],

    // NAS保护
    [
        'name' => 'nas_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-nas_protect',
        'title' => 'UI_PLATFORM_NAS_PROTECT',
        'level' => 0,
    ],

    // 文件复制
    [
        'name' => 'file_copy_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-nas_protect',
        'title' => 'UI_FILE_COPY',
        'level' => 0,
    ],

    // S3对象存储
    [
        'name' => 'obs_protect',
        'path' => 'javascript:;',
        'class' => "viconfont vicon-a-Group167-01",
        'title' => 'UI_PLATFORM_OBS_PROTECT',
        'level' => 0,
    ],

    // 数据库实时备份
    [
        'name' => 'dbprotect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-dbprotect',
        'title' => 'UI_PLATFORM_DB_CDP',
        'level' => 0,
    ],

    // 数据验证, 备份数据CDM
    [
        'name' => 'data_verification',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-data_verification',
        'title' => 'UI_PLATFORM_CDM',
        'level' => 0,
    ],

    // 备份数据管理
    [
        'name' => 'data_manager',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-shujuguanli',
        'title' => 'UI_PLATFORM_DATA_MANAGER',
        'level' => 0,
    ],

    // 资源管理
    [
        'name' => 'resmanagement',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-resmanagement page-vm-infrastructure_en',
        'title' => 'UI_PLATFORM_RESOURCE_MANAGER',
        'level' => 0,
    ],

    // 行业合规
    [
        'name' => 'industry',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-hangyeheguijiancha',
        'title' => 'WEB_INDUSTRY',
        'level' => 0,
    ],

    // 系统管理
    [
        'name' => 'sysmanagement',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-sysmanagement',
        'title' => 'UI_PLATFORM_SYSTEM_MANAGER',
        'level' => 0,
    ],

    // 全局观察者
    [
        'name' => 'global_observer',
        'path' => '',
        'class' => 'fa fa-eye',
        'title' => 'UI_PLATFORM_GLOBAL_OBSERBER',
        'level' => 0,
    ]
];
