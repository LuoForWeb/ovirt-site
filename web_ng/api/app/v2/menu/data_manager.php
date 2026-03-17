<?php

/**
* 这是 备份数据管理对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 备份数据管理
    [
        "name" => "backup_data",
        'path' => "backupData.html",
        'class' => "viconfont vicon-shuju",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_BACKUP_DATA",
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => "p_backup_data_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                'function' => [
                    'get-backup_data_jobs_list', // 获取任务列表
                    'get-backup_data_items_list', // 获取对象列表
                    'get-backup_data_vol_list', // 获取整机实时标签点
                    'get-backup_data_remote_list', // 获取异地副本数据列表
                    'get-backup_data_remote_tree', // 获取异地副本数据树
                    'get-backup_data_points', // 获取备份点
                    'get-backup_data_points_path', // 获取时间点备份的虚拟机路径、文件列表等
                ],
                'level' => 10,
            ],
            [
                'name' => "p_backup_data_operate",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'level' => 10,
                'is_operate' => 1,
                'function' => [
                    'delete-backup_data_vol_list', // 删除整机实时标签点
                    'get-backup_data_vol_remark', // 整机标签点备注
                    'delete-backup_data_points', // 删除备份点
                    'get-backup_data_points_remark', // 设置备份点备注
                    'get-backup_data_points_gfs_mark', // 设置备份点gfs标记
                    'get-backup_data_points_mark', // 设置永久标记
                    'delete-backup_data_points_mark', // 取消永久标记
                    'get-backup_data_points_depend', // 获取完备点的依赖非完备点
                    'get-backup_data_points_worm', // 配置worm时间
                ],
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_BACKUP_DATA_OPERATE_DESC',
            ]
        ]
    ],
    // *恢复 右侧为平铺导航页面，参考visor
    [
        "name" => "recovery",
        'path' => "recoveryCenter.html",
        'class' => "viconfont vicon-dbdataRecovery",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 1--- 备份 ---.虚拟化, 私有云, 公有云, 容器, 整机, 文件, NAS, 对象存储, Hadoop HDFS, 数据库, 应用
            // 2 --- 持续数保护&复制 --- 整机, 数据库
            // 虚拟化
            [
                'name' => "vmprotect_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-overview-vm",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_VM_PROTECT",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "vmrecover",
                        'path' => "vmRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vmrecover_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_VM_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 瞬时恢复
                    [
                        'name' => "vm_instant_recovery",
                        'path' => "instantaneous.html?recovery_type=vm&subtype=1",
                        'class' => "viconfont vicon-vminstantrecover",
                        'title' => "WEB_PAGE_COMMON_MENU_INSTANT_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_instant_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_VM_INSTANT_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 细粒度恢复
                    [
                        'name' => "vm_grain_recovery",
                        'path' => "graininess.html?recovery_type=vm&subtype=1",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_GRAIN_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_grain_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_VM_GRAIN_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 跨平台恢复
                    [
                        'name' => "vm_platform_recovery",
                        'path' => "platform.html?recovery_type=vm&subtype=1",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVERY",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_platform_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_VM_PLATFORM_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],
            // 私有云
            [
                'name' => "prcloud_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-recover",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_PRIVATE_CLOUD_PROTECT",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "vm_prcloud_recovery",
                        'path' => "vmRecover.html?sub_module_type=2",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_prcloud_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_PRCLOUD_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 瞬时恢复
                    [
                        'name' => "vm_prcloud_instant_recovery",
                        'path' => "instantaneous.html?recovery_type=vm&subtype=2",
                        'class' => "viconfont vicon-vminstantrecover",
                        'title' => "WEB_PAGE_COMMON_MENU_INSTANT_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_prcloud_instant_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_PRCLOUD_INSTANT_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 细粒度恢复
                    [
                        'name' => "vm_prcloud_graininess_recovery",
                        'path' => "graininess.html?recovery_type=vm&subtype=2",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_GRAIN_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_prcloud_graininess_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_PRCLOUD_GRAIN_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 跨平台恢复
                    [
                        'name' => "vm_prcloud_platform_recovery",
                        'path' => "platform.html?recovery_type=vm&subtype=2",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_prcloud_platform_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_PRCLOUD_PLATFORM_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],
            // 公有云
            [
                'name' => "awsprotect_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-recover",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_AWS_PROTECT",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "vm_awsprotect_recover",
                        'path' => "awsRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_awsprotect_recover_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_AWS_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 瞬时恢复
                    [
                        'name' => "vm_awsprotect_instant_recovery",
                        'path' => "instantaneous.html?recovery_type=vm&subtype=3",
                        'class' => "viconfont vicon-vminstantrecover",
                        'title' => "WEB_PAGE_COMMON_MENU_INSTANT_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_awsprotect_instant_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_AWS_INSTANT_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 细粒度恢复
                    [
                        'name' => "vm_awsprotect_grain_recovery",
                        'path' => "graininess.html?recovery_type=vm&subtype=3",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_GRAIN_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_awsprotect_grain_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_AWS_GRAIN_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 跨平台恢复
                    [
                        'name' => "vm_awsprotect_platform_recovery",
                        'path' => "platform.html?recovery_type=vm&subtype=3",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vm_awsprotect_platform_recover_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_AWS_PLATFORM_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],
            // 容器
            [
                'name' => "k8s_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-k8s",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_K8S",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "k8s_recovery",
                        'path' => "kubernetesRecovery.html", //recovery
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_k8s_recovery_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_K8S_OPERATE_DESC',
                            ],
                        ],
                    ],
                ]
            ],
            // 文件
            [
                'name' => "file_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-host_protect",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FILES",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "file_recovery",
                        'path' => "fileRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_file_recovery_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_FILE_OPERATE_DESC',
                            ],
                        ],
                    ],
                ]
            ],
            // NAS
            [
                'name' => "nasrecover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-nas_protect",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_NAS_PROTECT",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "nas_recovery",
                        'path' => "nasRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_nas_recovery_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_NAS_OPERATE_DESC',
                            ],
                        ],
                    ],
                ]
            ],
            // 对象存储
            [
                'name' => "obs_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-a-Group167-01",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_OBS_PROTECT",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "obs_recovery",
                        'path' => "obsRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_obs_recovery_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_OBS_OPERATE_DESC',
                            ],
                        ],
                    ],
                ]
            ],
            // Hadoop HDFS
            [
                'name' => "hadoop_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-a-Elephantdaxiang-01",
                'title' => "WEB_PAGE_COMMON_MENU_HADOOP_PROTECT",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "hadoop_recovery",
                        'path' => "hadoopRecovery.html", //recovery
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_hadoop_recovery_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_HADOOP_OPERATE_DESC',
                            ],
                        ],
                    ],
                ]
            ],
            // 数据库
            [
                'name' => "db_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-db_protect",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DB_PROTECT",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "db_recovery",
                        'path' => "dbRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_db_recovery_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                    'post-db_jobs_recovery', // 创建恢复任务
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_DB_OPERATE_DESC',
                            ],
                        ],
                    ],
                    // 演练
                    [
                        'name' => "db_drill",
                        'path' => "dbRecover.html?timepoint_recovery_type=2",
                        'class' => "viconfont vicon-huifu1",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DB_DRILL",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_db_drill_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                    'post-db_jobs_recovery',  // 创建演练
                                    'put-db_jobs_recovery',  // 修改演练
                                    'get-getDbRecoveryJob',  // 获取演练任务信息
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_DB_DRILL_OPERATE_DESC',
                            ],
                        ],
                    ],
                ]
            ],
            // 应用
            [
                'name' => "exchange_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-yingyong",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_APPLICATION",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "exchange_recovery",
                        'path' => "exchangeRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_exchange_recovery_list',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                    'post-exchange_jobs_restore',
                                    'get-exchange_jobs_restore_task_name',
                                    'get-exchange_restore_data',
                                    'get-exchange_restore_data_restore_points',
                                    'get-exchange_restore_data_restore_points_users',
                                    'post-exchange_restore_export_zip',
                                    'get-exchange_restore_export_data',
                                    'post-exchange_restore_send_email',
                                    'get-exchange_restore_advanced_search',
                                    'get-exchange_restore_normal_search',
                                    'get-exchange_restore_auth'
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_EXCHANGE_OPERATE_DESC',
                            ],
                        ],
                    ],
                ]
            ],
            // 整机 - 磁盘
            [
                'name' => "machine_complete_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-recover",
                'title' => "WEB_PAGE_COMMON_MENU_COMPLETE_TIMED_MACHINE",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "machine_complete_recovery",
                        'path' => "machineOsRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_machine_complete_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_MACHINE_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 瞬时恢复
                    [
                        'name' => "machine_complete_instant_recovery",
                        'path' => "instantaneous.html?recovery_type=os",
                        'class' => "viconfont vicon-vminstantrecover",
                        'title' => "WEB_PAGE_COMMON_MENU_INSTANT_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_machine_complete_instant_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_MACHINE_INSTANT_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 细粒度恢复
                    [
                        'name' => "machine_complete_grain_recovery",
                        'path' => "graininess.html?recovery_type=os",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_GRAIN_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_machine_complete_grain_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_MACHINE_GRAIN_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 跨平台恢复
                    [
                        'name' => "machine_complete_platform_recovery",
                        'path' => "platform.html?recovery_type=os",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_machine_complete_platform_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_MACHINE_PLATFORM_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],
            // 整机 - 卷
            [
                'name' => "osrecover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-recover",
                'title' => "WEB_PAGE_COMMON_MENU_COMPLETE_TIMED_VOLUME",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "os_recovery",
                        'path' => "osRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_os_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                    '16-getTimepointAllNode',
                                    '16-getNodeNetworkList',
                                    '34-getTimepointTree',
                                    '34-getOptionIP',
                                    '34-linkTest',
                                    '34-getTimepointTree',
                                    '34-getOSRecoverTaskName',
                                    '34-createOSRecoverJob',
                                    '5-getTimeCrowdList',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_OS_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],

            // 2 --- 持续数保护&复制 --- 整机 - 磁盘, 数据库
            // 整机 - 磁盘
            [
                'name' => "machine_complete_volcdp_recover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-recover",
                'title' => "WEB_PAGE_COMMON_MENU_COMPLETE_MACHINE_REAL_TIME",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "machine_complete_volcdp_recovery",
                        'path' => "cmVolCdpRecovery.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_machine_complete_volcdp_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_MACHINE_CDP_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 细粒度恢复
                    [
                        'name' => "machine_complete_volcdp_grain_recovery",
                        'path' => "graininess.html?recovery_type=cdp",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_GRAIN_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_machine_complete_volcdp_grain_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_MACHINE_CDP_GRAIN_OPERATE_DESC',
                            ],
                        ]
                    ],
                    // 跨平台恢复
                    [
                        'name' => "machine_complete_volcdp_platform_recovery",
                        'path' => "platform.html?recovery_type=cdp",
                        'class' => "viconfont vicon-vmrecovera",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVERY",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_machine_complete_volcdp_platform_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_MACHINE_CDP_PLATFORM_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],
            // 整机 - 卷
            [
                'name' => "vol_cdp_recovery",
                'path' => "javascript:;",
                'class' => "viconfont vicon-recover",
                'title' => "WEB_PAGE_COMMON_MENU_COMPLETE_REAL_TIME_VOLUMES",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "p_vol_cdp_recovery",
                        'path' => "volCdpRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_vol_cdp_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                    '31-getDataSourceHostInfo',
                                    '31-getRecoveryTargetHost',
                                    '31-getVolTagPointInfo',
                                    '31-getVolCdpRecoverTaskName',
                                    '31-createRecoverJob',
                                    '5-getTimeCrowdList',
                                    '16-getVolCdpTimepointAllNode',
                                    '16-getNodeNetworkList',
                                    '10-getBackupStorageList',
                                    '32-getClientBackupSetInfo',
                                    '33-getAgentBkTimelineData',
                                    '33-verifyTimepointisValid',
                                    '33-getAgentEventInfo',
                                    '2-checkFSEncryptPass',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_CDP_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],
            // 数据库
            [
                'name' => "dbcdprecover",
                'path' => "javascript:;",
                'class' => "viconfont vicon-shujukushishi",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DATABASE_COPY",
                'level' => 2,
                'is_operate' => 1,
                'showChild' => false,
                'child' => [
                    // 恢复
                    [
                        'name' => "dbcdp_recovery",
                        'path' => "dbCdpRecover.html",
                        'class' => "viconfont vicon-recover",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_FULL_RECOVER",
                        'level' => 3,
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_dbcdp_recovery_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                    'get-dbcdp_restore_instances',
                                    'post-dbcdp_jobs_restore'
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_RECOVERY_DB_CDP_OPERATE_DESC',
                            ],
                        ]
                    ],
                ]
            ],
        ]
    ],
    // *接管 改为应急容灾
    [
        'name' => "cdp_takeover",
        'path' => "takeover.html", //takeover
        'class' => "viconfont vicon-vol_cdp_takeover",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_TAKEOVER",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // *接管 磁盘
            [
                'name' => "vol_cdp_complete_takeover",
                'path' => "cmVolCdpTakeover.html", //takeover
                'class' => "viconfont vicon-vol_cdp_takeover",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_TAKEOVER_COMPLETE",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_vol_cdp_complete_takeover_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                            '31-getVolTagPointInfo',
                            '32-getDataSourceHostInfo',
                            '32-getClientBackupSetInfo',
                            '32-getTakeoverTargetHost',
                            '32-checkIpIsOnline',
                            '32-getVolCdpTakeoverTaskName',
                            '32-getClientTakeoverAppInfo',
                            '32-createTakeoverJob',
                            '33-verifyTimepointisValid',
                            '33-getAgentEventInfo',
                            '33-getAgentBkTimelineData',
                            '30-updateAgentInfo',
                            '30-checkIpExists',
                            '16-getVolCdpTimepointAllNode',
                            '16-getNodeNetworkList',
                            '10-getBackupStorageList',
                            '2-checkFSEncryptPass',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_TAKEOVER_CDP_OPERATE_DESC',
                    ],
                ]
            ],
            // *接管  卷
            [
                'name' => "vol_cdp_takeover",
                'path' => "volCdpTakeover.html", //takeover
                'class' => "viconfont vicon-vol_cdp_takeover",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_TAKEOVER_REEL",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_vol_cdp_takeover_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                            '31-getVolTagPointInfo',
                            '32-getDataSourceHostInfo',
                            '32-getClientBackupSetInfo',
                            '32-getTakeoverTargetHost',
                            '32-checkIpIsOnline',
                            '32-getVolCdpTakeoverTaskName',
                            '32-getClientTakeoverAppInfo',
                            '32-createTakeoverJob',
                            '33-verifyTimepointisValid',
                            '33-getAgentEventInfo',
                            '33-getAgentBkTimelineData',
                            '30-updateAgentInfo',
                            '30-checkIpExists',
                            '16-getVolCdpTimepointAllNode',
                            '16-getNodeNetworkList',
                            '10-getBackupStorageList',
                            '2-checkFSEncryptPass',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_TAKEOVER_VOL_CDP_OPERATE_DESC',
                    ],
                ]
            ],
        ]
    ],
    // 验证
    [
        'name' => "data_verification",
        'path' => "javascript:;",
        'class' => "viconfont vicon-data_verification",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_VERIFICATION",
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 数据验证
            [
                'name' => "add_verification_job",
                'path' => "verificationJob.html",
                'class' => "viconfont vicon-add_verification_job",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DATA_VERIFICATION",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_add_verification_job_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_VERIFICATION_OPERATE_DESC',
                    ],
                ]
            ],
            // 虚拟实验室
            [
                'name' => "virtual_lab_manager",
                'path' => "virtualLabManager.html",
                'class' => "viconfont vicon-virtual_lab_manager",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_VIRTUAL_LAB",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_virtual_lab_manager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            '14-getVirtualLabInfo',
                            '14-getVirtualLabInfo',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_virtual_lab_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_virtual_lab_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_virtual_lab_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 刷新
                    [
                        'name' => "p_virtual_lab_refesh",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_TOOLS_RELOAD",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 应用组
            [
                'name' => "appgroup",
                'path' => "appGroup.html",
                'class' => "viconfont vicon-yingyong",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_LAB_APP_GROUP",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_app_group_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_app_groupb_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_app_group_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_app_group_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
        ]
    ],
    // *副本 右侧为平铺导航页面，按模块显示，每个模块有副本，副本回传两个选项，注意模块
    [
        'name' => "copy_protect",
        'path' => "copyCenter.html",
        'class' => "viconfont vicon-vmcopy",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_COPY_PROTECT",
        'level' => 1,
        'is_operate' => 1,
        'child' => []
    ],
    // *归档：右侧为平铺导航页面，按模块显示，每个模块有归档，归档回传两个选项，注意模块类型
    [
        'name' => "archive_new",
        'path' => "archiveCenter.html",
        'class' => "viconfont vicon-data_archive",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ARCHIVE",
        'level' => 1,
        'is_operate' => 1,
        'child' => []
    ],
];
