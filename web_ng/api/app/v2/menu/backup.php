<?php

/**
* 这是 备份对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //虚拟化
    [
        'name' => "vmprotect",
        'path' => "vmBackup.html",
        'class' => "viconfont vicon-overview-vm",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_VM_VIRTUAL",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_vmbackup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_VM_OPERATE_DESC',
            ]
        ]
    ],
    // 私有云
    [
        'name' => "prcloud_protect",
        'path' => "vmBackup.html?sub_module_type=2",
        'class' => "viconfont vicon-overview-private-cloud",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_PRIVATE_CLOUD",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_prcloud_backup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_PRCLOUD_OPERATE_DESC',
            ],
        ],
    ],
    // 公有云
    [
        'name' => "awsprotect",
        'path' => "awsBackup.html",
        'class' => "viconfont vicon-overview-plubic-cloud",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_PUBLIC_CLOUD",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_awsbackup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_AWS_OPERATE_DESC',
            ]
        ],
    ],
    // *整机 如果只授权了整机-磁盘，那么语言包就显示成整机
    // 磁盘 ./content/complete_machine_os/machine_os_backup.php
    [
        'name' => "complete_machine",
        'path' => "completeMachineOs.html",
        'class' => "viconfont vicon-overview-complete-machine",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MACHINE_COMPLETE_BACKUP",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_complete_machine_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_MACHINE_OPERATE_DESC',
            ]
        ],
    ],
    // 卷 ./content/os/osbackup.php
    [
        'name' => "osbackup",
        'path' => "osBackup.html",
        'class' => "viconfont vicon-overview-volume",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MACHINE_REEL_BACKUP",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_osbackup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_OS_OPERATE_DESC',
            ]
        ],
    ],
    // 文件
    [
        'name' => "fileprotect",
        'path' => "javascript:;",
        'class' => "viconfont vicon-overview-file",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FILES_PAGE",
        'level' => 1,
        'is_operate' => 1,
        'showChild' => true,
        'child' => [
            // 文件
            [
                'name' => "filebackup",
                'path' => "fileBackup.html?sub_module_type=1",
                'class' => "viconfont vicon-overview-file",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FILES",
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => "p_filebackup_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_FILE_OPERATE_DESC',
                    ],
                ]
            ],
            // NAS
           [
                'name' => "nas_protect",
                'path' => "fileBackup.html?sub_module_type=2",
                'class' => "viconfont vicon-overview-nas",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_NAS",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_nasbackup_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_NAS_OPERATE_DESC',
                    ],
                ]
            ],
            // 对象存储
            [
                'name' => "obs_protect",
                'path' => "fileBackup.html?sub_module_type=4",
                'class' => "viconfont vicon-overview-obs",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_OBS_STORAGE",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_obsbackup_list',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_OBS_OPERATE_DESC',
                    ],
                ]
            ],
            // Hadoop HDFS
            [
                'name' => "hadoop_protect",
                'path' => "fileBackup.html?sub_module_type=3",
                'class' => "viconfont vicon-overview-hadoop",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_HADOOP_HDFS",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_hadoop_backup_list',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_HADOOP_OPERATE_DESC',
                    ],
                ]
            ],
        ]
    ],
    // 数据库
    [
        'name' => "db_protect",
        'path' => "dbBackup.html",
        'class' => "viconfont vicon-overview-database",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DATABASE",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_db_backup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_DB_OPERATE_DESC',
            ]
        ],
    ],
    // Microsoft 365
    [
        'name' => "office365_protect",
        'path' => "exchangeBackup.html",
        'class' => "viconfont vicon-windows-view",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MICROSOFT365",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_exchange_backup_list',
                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                'function' => [
                    'get-exchange_jobs_organization',
                    'put-exchange_jobs_organization',
                    'post-exchange_jobs_backup',
                    'put-exchange_jobs_backup',
                    'get-exchange_jobs_backup_info',
                    'get-exchange_jobs_backup_task_name',
                    'get-exchange_alarm'
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_M365_OPERATE_DESC',
            ],
        ]
    ],
    // 容器
    [
        'name' => "k8s_protect",
        'path' => "kubernetesBackup.html", //backup
        'class' => "viconfont vicon-overciew-k8s",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_K8S",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_k8s_backup_list',
                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                'function' => [
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_K8S_OPERATE_DESC',
            ],
        ],
    ],
    // ***云存储同步
    [
        'name' => "cbrbackup",
        'path' => "cbrBackup.html",
        'class' => "viconfont vicon-tongbu",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_STORAGE_SYNC",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_cbrbackup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_CBR_OPERATE_DESC',
            ],
        ]
    ],

];
