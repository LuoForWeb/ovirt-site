<?php

/**
* 这是 复制容灾对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // *整机 如果只授权了整机-磁盘，那么语言包就显示成整机
    // 磁盘 ./complete_machine_volcdp/cm_volcdp_copy.php
    [
        'name' => "machine_copy",
        'path' => "cmVolCdpBackup.html?task_type=copy",
        'class' => "viconfont vicon-overview-complete-machine",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_COPY_COMPLETE_BACKUP",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_machine_os_backup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_COPY_CDP_MACHINE_OPERATE_DESC',
            ],
        ]
    ],
    // 卷  ./content/volcdp/vol_cdp_backup.php?type=reel
    [
        'name' => "vol_cdp_copy",
        'path' => "volCdpBackup.html?task_type=copy",
        'class' => "viconfont vicon-overview-volume",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_COPY_REEL_BACKUP",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_machine_os_backup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_COPY_CDP_OPERATE_DESC',
            ],
        ]
    ],
    // 文件 如果后期有其他如NAS，Hadoop，通过选择内部选择类型来处理，统一流程
    [
        'name' => "file_copy_protect",
        'path' => "fileCopy.html",
        'class' => "viconfont vicon-filecdpdata",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FILES",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_file_copy_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [
                    'get-system_times_info',
                    'get-exchange_jobs_backup_task_name',
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_COPY_FILE_OPERATE_DESC',
            ],
        ]
    ],
    // 数据库
    [
        'name' => 'dbcdpcopy',
        'path' => "dbCdpCopy.html",
        'class' => "viconfont vicon-overview-database",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DATABASE",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_dbcdp_backup_list',
                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                'function' => [
                    'post-dbcdp_databases',
                    'get-dbcdp_jobs_networkInfo',
                    'post-dbcdp_jobs_scanIp',
                    'post-dbcdp_jobs_sync'
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_COPY_DB_OPERATE_DESC',
            ]
        ]
    ],
];
