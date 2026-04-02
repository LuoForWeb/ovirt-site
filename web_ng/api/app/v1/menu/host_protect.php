<?php

/**
* 这是 主机保护对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //文件保护
    [
        'name' => 'fileprotect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-fileprotect',
        'title' => 'UI_PLATFORM_FILE_PROTECT',
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 备份
            [
                'name' => 'filebackup',
                'path' => './content/fs/filebackup.php',
                'class' => 'viconfont vicon-backup',
                'title' => 'UI_PLATFORM_BACKUP',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_filebackup_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 恢复
            [
                'name' => 'filerecover',
                'path' => './content/fs/filerecover.php',
                'class' => 'viconfont vicon-recover',
                'title' => 'UI_PLATFORM_RECOVER',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_filerecover_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 备份数据
            [
                'name' => 'filedata',
                'path' => './content/fs/filedata.php',
                'class' => 'viconfont vicon-vmdata',
                'title' => 'UI_PLATFORM_BACKUPDATA',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_filedata_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => 'p_filedata_detele',
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 备注
                    [
                        'name' => 'p_filedata_remark',
                        'title' => 'UI_PUBLIC_REMARK',
                        'function'  => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 设置标记点
                    [
                        'name' => 'p_filedata_star',
                        'title' => 'WEB_PT_OP_GFS_FLAG',
                        'function'  => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
    //数据库保护
    [
        'name' => 'db_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-db_protect',
        'title' => 'UI_PLATFORM_DB_PROTECT',
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 备份
            [
                'name' => 'db_backup',
                'path' => './content/dbprotect/dbbackup.php',
                'class' => 'viconfont vicon-backup',
                'title' => 'UI_PLATFORM_BACKUP',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_db_backup_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 恢复
            [
                'name' => 'db_recovery',
                'path' => './content/dbprotect/dbrecover.php',
                'class' => 'viconfont vicon-recover',
                'title' => 'UI_PLATFORM_RECOVER',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_db_recovery_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 备份数据
            [
                'name' => 'db_data_manager',
                'path' => './content/dbprotect/db_data_manager.php',
                'class' => 'viconfont vicon-vmdata',
                'title' => 'UI_PLATFORM_BACKUPDATA',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_db_data_manager_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => 'p_dbdata_detele',
                        'title' => "UI_PUBLIC_DELETE",
                        'function'  => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 备注
                    [
                        'name' => 'p_dbdata_remark',
                        'title' => 'UI_PUBLIC_REMARK',
                        'function'  => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 设置标记点
                    [
                        'name' => 'p_dbdata_star',
                        'title' => 'WEB_PT_OP_GFS_FLAG',
                        'function'  => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
    //操作系统保护
    [
        'name' => 'os_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-os_protect',
        'title' => 'UI_PLATFORM_OS_PROTECT',
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 备份
            [
                'name' => 'osbackup',
                'path' => './content/os/osbackup.php',
                'class' => 'viconfont vicon-backup',
                'title' => 'UI_PLATFORM_BACKUP',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_osbackup_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 恢复
            [
                'name' => 'osrecover',
                'path' => './content/os/osrecover.php',
                'class' => 'viconfont vicon-recover',
                'title' => 'UI_PLATFORM_RECOVER',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_osrecover_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
             // 瞬时恢复
             [
                'name' => 'osinstantrecover',
                'path' => './content/os/osinstantrecover.php',
                'class' => 'iconfont vicon-vminstantrecover',
                'title' => 'WEB_VM_INSTANT_RECOVERY',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_osinstantrecover_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 备份数据
            [
                'name' => 'osdata',
                'path' => './content/os/osdata.php',
                'class' => 'viconfont vicon-vmdata',
                'title' => 'UI_PLATFORM_BACKUPDATA',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_osdata_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => 'p_osdata_detele',
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 备注
                    [
                        'name' => 'p_osdata_remark',
                        'title' => 'UI_PUBLIC_REMARK',
                        'function'  => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 设置标记点
                    [
                        'name' => 'p_osdata_star',
                        'title' => 'WEB_PT_OP_GFS_FLAG',
                        'function'  => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],

        ]
    ],
];
