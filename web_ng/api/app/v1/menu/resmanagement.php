<?php

/**
 * 这是 资源管理对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //云基础设施
    [
        'name' => 'vmprotect_infrastructure',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-vmprotect_infrastructure page-infrastructure_en',
        'title' => 'UI_PLATFORM_VM_INFRASTRUCTURE',
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 虚拟化中心
            [
                'name' => 'vcenter_manager',
                'path' => './content/vm/vcenter_manager.php',
                'class' => 'viconfont vicon-vcenter_manager',
                'title' => 'UI_PLATFORM_VCENTER',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_vcenter_manager_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => 'p_vcenter_manager_add',
                        'title' => 'UI_PUBLIC_ADDNEW',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_vcenter_manager_edit',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_vcenter_manager_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 平台备份
                    [
                        'name' => 'p_vcenter_manager_backup',
                        'title' => 'UI_VCENTER_ENGINE_BACKUP',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 自动刷新
                    [
                        'name' => 'p_vcenter_manager_refresh',
                        'title' => 'UI_VCENTER_AUTO_REFRESH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 同步
                    [
                        'name' => 'p_vcenter_manager_sync',
                        'title' => 'WEB_PLATFORM_DES_SYNC',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 授权
                    [
                        'name' => 'p_vcenter_manager_license',
                        'title' => 'UI_SETTINGS_AUTH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 虚拟机操作
                    [
                        'name' => 'p_vm_overview_vmoperate',
                        'title' => 'WEB_VM_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 云平台
            [
                'name' => 'cloud_platform',
                'path' => './content/vm/cloudplatform/cloud_platform_manager.php?cloudType=public',
                'class' => 'viconfont vicon-cloud_platform',
                'title' => 'UI_PLATFORM_VM_CLOUD_PLATFORM',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_cloud_platform_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => 'p_cloud_platform_manager_add',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_cloud_platform_manager_edit',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_cloud_platform_manager_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 平台备份
                    [
                        'name' => 'p_cloud_platform_manager_backup',
                        'title' => 'UI_CLOUD_PLATFORM_ENGINE_BACKUP',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 自动刷新
                    [
                        'name' => 'p_cloud_platform_manager_refresh',
                        'title' => 'UI_CLOUD_PLATFORM_AUTO_REFRESH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 同步
                    [
                        'name' => 'p_cloud_platform_manager_sync',
                        'title' => 'WEB_PLATFORM_DES_SYNC',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 授权
                    [
                        'name' => 'p_cloud_platform_manager_license',
                        'title' => 'UI_SETTINGS_AUTH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 虚拟机操作
                    [
                        'name' => 'p_cloud_platform_vm_overview_vmoperate',
                        'title' => 'WEB_VM_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 私有云平台
            [
                'name' => 'cloud_platform_private',
                'path' => './content/vm/cloudplatform/cloud_platform_manager.php?cloudType=private',
                'class' => 'viconfont vicon-cloud_platform',
                'title' => 'UI_PLATFORM_VM_CLOUD_PLATFORM',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_cloud_platform_private_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => 'p_cloud_platform_private_manager_add',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_cloud_platform_private_manager_edit',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_cloud_platform_private_manager_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 平台备份
                    [
                        'name' => 'p_cloud_platform_private_manager_backup',
                        'title' => 'UI_CLOUD_PLATFORM_ENGINE_BACKUP',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 自动刷新
                    [
                        'name' => 'p_cloud_platform_private_manager_refresh',
                        'title' => 'UI_CLOUD_PLATFORM_AUTO_REFRESH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 同步
                    [
                        'name' => 'p_cloud_platform_private_manager_sync',
                        'title' => 'WEB_PLATFORM_DES_SYNC',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 授权
                    [
                        'name' => 'p_cloud_platform_private_manager_license',
                        'title' => 'UI_SETTINGS_AUTH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 虚拟机操作
                    [
                        'name' => 'p_cloud_platform_private_vm_overview_vmoperate',
                        'title' => 'WEB_VM_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // LAN-Free配置
            [
                'name' => 'storage_lanfree',
                'path' => './content/platform/storage/storage_lanfree.php',
                'class' => 'viconfont vicon-storage_lanfree',
                'title' => 'UI_PALTFORM_STORAGE_LANFREE',
                'level' => 1,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_storage_lanfree_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => 'p_storage_lanfree_add',
                        'class' => '',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_storage_lanfree_edit',
                        'class' => '',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_storage_lanfree_delete",
                        'class' => '',
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
    //磁带管理
    [
        'name' => 'tape_manage',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-a-Tapecidai',
        'title' => 'UI_TAPE_MANAGE',
        'level' => 1,
        'showChild' => true,
        'child' => [
            //磁带设备
            [
                'name' => 'tape_device',
                'path' => './content/platform/resource/tape_equipment.php',
                'class' => 'viconfont vicon-a-Tapecidai',
                'title' => 'UI_TAPE_EQUIPMENT',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_tape_device_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => 'p_tape_device_add',
                        'class' => '',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_tape_device_edit',
                        'class' => '',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_tape_device_delete",
                        'class' => '',
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [],
                        'level' => 10,
                    ],
                ],
            ],
            //磁带任务
            [
                'name' => 'tape_task',
                'path' => './content/platform/resource/tape_jobs.php',
                'class' => 'viconfont vicon-a-Type-drivecidai',
                'title' => 'UI_TAPE_JOB',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_tape_task_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [],
                        'level' => 10,
                    ],
                ],
            ],
            //磁带监控
            [
                'name' => 'tape_monitor',
                'path' => './content/platform/resource/tape_monitor.php',
                'class' => 'viconfont vicon-a-Type-drivecidai',
                'title' => 'UI_TAPE_MONITOR',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_tape_monitor_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [],
                        'level' => 10,
                    ],
                ],
            ],
        ]
    ],
    //客户端管理
    [
        'name' => 'client',
        'path' => './content/client/client.php',
        'class' => 'viconfont vicon-client',
        'title' => 'UI_PLATFORM_CLIENT',
        'level' => 1,
        'child' => [
            // 客户端管理
            [
                'name' => 'agent_manager',
                'path' => './content/client/client_manager.php',
                'class' => 'viconfont vicon-client',
                'title' => 'UI_CLIENT_MANAGER',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_agent_manager_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => 'p_agent_manager_register',
                        'title' => 'UI_PUBLIC_ADDNEW',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_agent_manager_modify',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_agent_manager_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 授权
                    [
                        'name' => 'p_agent_manager_license',
                        'title' => 'UI_SETTINGS_AUTH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 应用配置
                    [
                        'name' => 'p_agent_manager_application_config',
                        'title' => 'UI_CLIENT_APPLICATION_CONFIG',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 客户端升级
                    [
                        'name' => 'p_agent_manager_upgrade',
                        'title' => 'UI_CLIENT_UPGRADE',
                        'function' => [],
                        'level' => 10,
                    ],
                    // 客户端日志下载
                    [
                        'name' => 'p_agent_log_download',
                        'title' => 'UI_CLIENT_LOG_DOWNLOAD',
                        'function' => [],
                        'level' => 10,
                    ],
                ]
            ],
            // 客户端分组管理
            [
                'name' => "client_group",
                'path' => "./content/client/client_group.php",
                'class' => "viconfont vicon-client_groups",
                'title' => "UI_CLIENT_GROUP_MANAGER",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_client_group_list",
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => "p_client_group_add",
                        'title' => 'UI_PUBLIC_ADDNEW',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => "p_client_group_modify",
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_client_group_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 移动到分组
                    [
                        'name' => "p_client_group_add_to_group",
                        'title' => "UI_CLIENT_GROUP_MOVE_TO",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 从分组移除
                    [
                        'name' => "p_client_group_delete_from_group",
                        'title' => "UI_CLIENT_GROUP_REMOVE_FROM",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
    //kubernetes集群管理
    [
        'name' => 'k8s_cluster',
        'path' => './content/kubernetes/kubernetes_cluster.php',
        'class' => 'viconfont vicon-jiqun',
        'title' => 'WEB_K8S_CLUSTER_PROTECT',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_k8s_cluster_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [],
                'level' => 10,
            ],
        ]
    ],
    //Hadoop集群管理
    [
        'name' => 'hadoop_cluster',
        'path' => './content/hadoop/hadoop_cluster.php',
        'class' => 'viconfont vicon-a-Elephantdaxiang-01',
        'title' => 'WEB_HADOOP_CLUSTER_PROTECT',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_hadoopmanager_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 添加
            [
                'name' => 'p_hadoopmanager_add',
                'title' => 'UI_PUBLIC_ADDNEW',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_hadoopmanager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_hadoopmanager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 自动刷新
            [
                'name' => 'p_hadoopmanager_refresh',
                'title' => 'UI_CLOUD_PLATFORM_AUTO_REFRESH',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 同步
            [
                'name' => 'p_hadoopmanager_sync',
                'title' => 'WEB_PLATFORM_DES_SYNC',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 授权
            [
                'name' => 'p_hadoopmanager_license',
                'title' => 'UI_SETTINGS_AUTH',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ],
    ],
    // NAS设备管理
    [
        'name' => 'nasmanager',
        'path' => './content/nas/nasmanager.php',
        'class' => 'viconfont vicon-nasmanager',
        'title' => 'UI_PLATFORM_NAS_MANAGER',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_nasmanager_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 添加
            [
                'name' => 'p_nasmanager_add',
                'title' => 'UI_PUBLIC_ADDNEW',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_nasmanager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_nasmanager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 授权
            [
                'name' => 'p_nasmanager_licence',
                'title' => 'UI_SETTINGS_AUTH',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 挂载
            [
                'name' => 'p_nasmanager_device_mount',
                'title' => 'UI_PUBLIC_DEVICE_MOUNT',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 解挂
            [
                'name' => 'p_nasmanager_device_unmount',
                'title' => 'UI_PUBLIC_DEVICE_UNMOUNT',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // OBS对象存储管理
    [
        'name' => 'obsmanager',
        'path' => './content/s3/obsmanager.php',
        'class' => 'viconfont vicon-a-Instructionzhiling-01',
        'title' => 'UI_PLATFORM_OBS_MANAGER',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_obsmanager_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 添加
            [
                'name' => 'p_obsmanager_add',
                'title' => 'UI_PUBLIC_ADDNEW',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_obsmanager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_obsmanager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 授权
            [
                'name' => 'p_obsmanager_licence',
                'title' => 'UI_SETTINGS_AUTH',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 自动刷新
            [
                'name' => 'p_obsmanager_refresh',
                'title' => 'UI_CLOUD_PLATFORM_AUTO_REFRESH',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // office365组织管理
    [
        'name' => 'exchange_organization',
        'path' => './content/exchange/exchange_organization.php',
        'class' => 'viconfont vicon-zuzhi',
        'title' => 'UI_PLATFORM_OFFICE365_ORGANIZATION',
        'level' => 1,
        'child' => [
            //查看
            [
                'name' => 'p_exchange_organization_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-office365_organization',
                ],
                'level' => 10,
            ],
            //添加
            [
                'name' => 'p_exchange_organization_add',
                'title' => 'UI_PUBLIC_ADDNEW',
                'function' => [
                    'post-office365_organization',
                    'get-office365_organization_agent',
                ],
                'level' => 10,
            ],
            //修改
            [
                'name' => 'p_exchange_organization_modify',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'put-office365_organization',
                ],
                'level' => 10,
            ],
            //自动刷新
            [
                'name' => 'p_exchange_organization_refresh',
                'title' => 'UI_VCENTER_AUTO_REFRESH',
                'function' => [
                    'get-office365_organization_refresh',
                    'put-office365_organization_refresh',
                ],
                'level' => 10,
            ],
            //删除
            [
                'name' => 'p_exchange_organization_delete',
                'title' => 'UI_PUBLIC_DELETE',
                'function' => [
                    'delete-office365_organization',
                ],
                'level' => 10,
            ],
            //同步
            [
                'name' => 'p_exchange_organization_sync',
                'title' => 'WEB_PLATFORM_DES_SYNC',
                'function' => [
                    'post-office365_organization_sync'
                ],
                'level' => 10,
            ],
        ]
    ],
    // 虚拟机管理
    [
        'name' => 'vm_machine_manager',
        'path' => './content/platform/vm_machine/vm_manager.php',
        'class' => 'viconfont vicon-storage_manager',
        'title' => 'UI_VM_MACHINE_MANAGER',
        'level' => 1,
        'child' => [
            // 概览
            [
                'name' => 'vm_machine_network',
                'path' => './content/platform/vm_machine/vm_network.php',
                'class' => 'viconfont vicon-vm_overview',
                'title' => 'UI_VM_MACHINE_NETWORK',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_vm_machine_network_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-virtual_network'
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => 'p_vm_machine_network_add',
                        'title' => 'UI_PUBLIC_ADDNEW',
                        'function' => [
                            'post-virtual_network'
                        ],
                        'level' => 10,
                    ],
                    /*// 修改
                    [
                        'name' => 'p_vm_machine_network_modify',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => array(
                            'post-virtual_network'
                        ),
                        'level' => 10,
                    ],*/
                    // 删除
                    [
                        'name' => "p_vm_machine_network_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'delete-virtual_network'
                        ],
                        'level' => 10,
                    ],
                    // 资源隔离
                    [
                        'name' => 'p_vm_machine_partition',
                        'title' => 'UI_VM_MACHINE_PARTITION',
                        'function' => [
                            'get-virtual_resources',
                            'post-virtual_resources',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 主机列表
            [
                'name' => 'vm_machine_list',
                'path' => './content/platform/vm_machine/vm_list.php',
                'class' => 'viconfont vicon-client',
                'title' => 'UI_VM_MACHINE_LIST',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_vm_machine_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-virtual',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_vm_machine_modify',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'post-virtual',
                            'post-virtual_operate',
                            'get-virtual_tree',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_vm_machine_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'delete-virtual',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 操作日志
            [
                'name' => 'vm_machine_operate_log',
                'path' => './content/platform/vm_machine/vm_log.php',
                'class' => "viconfont vicon-client_groups",
                'title' => "UI_CLIENT_GROUP_MANAGER",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'vm_machine_operate_log_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-virtual_logs'
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "vm_machine_operate_log_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'delete-virtual_logs'
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 代理网关
            [
                'name' => 'vm_machine_proxy_gateway',
                'path' => './content/platform/vm_machine/proxy_gateway.php',
                'class' => "viconfont vicon-client_groups",
                'title' => 'UI_DRILLS_AGENT_GATEWAY',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'vm_machine_proxy_gateway_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-virtual_proxy'
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
    // 存储设备
    [
        'name' => 'storage_manager',
        'path' => './content/platform/storage/storage_manager.php',
        'class' => 'viconfont vicon-storage_manager',
        'title' => 'UI_JOB_STORAGE_DEV',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_storage_manager_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => 'p_storage_manager_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_storage_manager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_storage_manager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 导入数据管理
            [
                'name' => 'p_storage_manager_data',
                'title' => 'UI_PALTFORM_STORAGE_DATA',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 自动导入时间点配置
            [
                'name' => 'p_storage_manager_timepoint_config',
                'title' => 'UI_PALTFORM_STORAGE_TIMEPOINT_CONFIG',
                'function' => [
                ],
                'level' => 10,
            ],
        ]
    ],
    // 备份节点
    [
        'name' => 'node_manager',
        'path' => './content/platform/node/node_manager.php',
        'class' => 'viconfont vicon-node_manager',
        'title' => 'UI_PALTFORM_NODE',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_node_manager_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 添加
            [
                'name' => 'p_node_manager_add',
                'title' => 'UI_PUBLIC_ADDNEW',
                'function' => [],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_node_manager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_node_manager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 网络配置
            [
                'name' => 'p_node_manager_network',
                'title' => 'UI_NODE_TRANSFER_NETWORK_CONFIG',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 缓存配置
            [
                'name' => 'p_node_manager_cache',
                'title' => 'UI_NODE_CACHE_CONFIG',
                'function' => [],
                'level' => 10,
            ],
        ]
    ],
    // 备份策略
    [
        'name' => 'global_strategy',
        'path' => './content/platform/strategy/global_strategy.php',
        'class' => 'viconfont vicon-global_strategy',
        'title' => 'UI_PLATFORM_GLOBAL_STRATEGY',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_global_strategy_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => 'p_global_strategy_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_global_strategy_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_global_strategy_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 全局限速策略
    [
        'name' => 'global_speed_strategy',
        'path' => './content/platform/gloabl_strategy/global_strategy.php',
        'class' => 'viconfont vicon-global_strategy',
        'title' => 'UI_PLATFORM_GLOBAL_STRATEGY',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_global_speed_strategy_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => 'p_global_speed_strategy_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_global_speed_strategy_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_global_speed_strategy_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 分发
            [
                'name' => 'p_global_speed_strategy_send',
                'title' => 'UI_GLOBAL_STRATEGY_DISTRIBUTE',
                'function' => [
                    'post-storages_global_speed_send',
                    'get-storages_global_speed_job'
                ],
                'level' => 10,
            ],
        ]
    ],
    // 资源组
    [
        'name' => "resource_group",
        'path' => "./content/platform/resource/resource_group.php",
        'class' => "viconfont vicon-resource_group",
        'title' => "UI_PLATFORM_RESOURCE_GROUP",
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => "p_resource_group_list",
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-resources_group',
                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => "p_resource_group_add",
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'post-resources_group_add',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => "p_resource_group_edit",
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-resources_groupDetail',
                    'post-resources_group',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_resource_group_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'delete-resources_group',
                ],
                'level' => 10,
            ],
        ]
    ],
];