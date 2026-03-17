<?php

/**
 * 这是 资源管理对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // *基础设施
    [
        'name' => "infrastructure",
        'path' => "infrastructure.html",
        'class' => "viconfont vicon-vmprotect_infrastructure page-infrastructure_en",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_INFRASTRUCTURE",
        'level' => 1,
        'showChild' => false,
        'child' => [
            // 虚拟化中心
            [
                'name' => "vcenter_manager",
                'path' => "vcenterManager.html",
                'class' => "viconfont vicon-vcenter_manager",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_VCENTER",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_vcenter_manager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => "p_vcenter_manager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_vcenter_manager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_vcenter_manager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 平台备份
                    [
                        'name' => "p_vcenter_manager_backup",
                        'title' => "WEB_PAGE_COMMON_MENU_VCENTER_ENGINE_BACKUP",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 自动刷新
                    [
                        'name' => "p_vcenter_manager_refresh",
                        'title' => "WEB_PAGE_COMMON_MENU_VCENTER_AUTO_REFRESH",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 同步
                    [
                        'name' => "p_vcenter_manager_sync",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 授权
                    [
                        'name' => "p_vcenter_manager_license",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_AUTH",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 分配备份节点
                    [
                        'name' => "p_vcenter_manager_allocation_node",
                        'title' => "WEB_PAGE_COMMON_MENU_VCENTER_ALLOCATION_NODE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 虚拟机操作
                    [
                        'name' => "p_vm_overview_vmoperate",
                        'title' => "WEB_PAGE_COMMON_MENU_VM_OPERATION",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 私有云平台
            [
                'name' => "cloud_platform_private",
                'path' => "cloudPlatformManager.html?cloudType=private",
                'class' => "viconfont vicon-cloud_platform",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_cloud_platform_private_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_cloud_platform_private_manager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_cloud_platform_private_manager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_cloud_platform_private_manager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 平台备份
                    [
                        'name' => "p_cloud_platform_private_manager_backup",
                        'title' => "WEB_PAGE_COMMON_MENU_CLOUD_PLATFORM_ENGINE_BACKUP",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 自动刷新
                    [
                        'name' => "p_cloud_platform_private_manager_refresh",
                        'title' => "WEB_PAGE_COMMON_MENU_CLOUD_PLATFORM_AUTO_REFRESH",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 同步
                    [
                        'name' => "p_cloud_platform_private_manager_sync",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 授权
                    [
                        'name' => "p_cloud_platform_private_manager_license",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_AUTH",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 虚拟机操作
                    [
                        'name' => "p_cloud_platform_private_vm_overview_vmoperate",
                        'title' => "WEB_PAGE_COMMON_MENU_VM_OPERATION",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 公有云平台
            [
                'name' => "cloud_platform",
                'path' => "cloudPlatformManager.php?cloudType=public",
                'class' => "viconfont vicon-yunpingtai",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_VM_CLOUD_PLATFORM",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_cloud_platform_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_cloud_platform_manager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_cloud_platform_manager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_cloud_platform_manager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 平台备份
                    [
                        'name' => "p_cloud_platform_manager_backup",
                        'title' => "WEB_PAGE_COMMON_MENU_CLOUD_PLATFORM_ENGINE_BACKUP",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 自动刷新
                    [
                        'name' => "p_cloud_platform_manager_refresh",
                        'title' => "WEB_PAGE_COMMON_MENU_CLOUD_PLATFORM_AUTO_REFRESH",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 同步
                    [
                        'name' => "p_cloud_platform_manager_sync",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 授权
                    [
                        'name' => "p_cloud_platform_manager_license",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_AUTH",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 虚拟机操作
                    [
                        'name' => "p_cloud_platform_vm_overview_vmoperate",
                        'title' => "WEB_PAGE_COMMON_MENU_VM_OPERATION",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 代理&客户端
            [
                'name' => "client",
                'path' => "javascript:;",
                'class' => "viconfont vicon-client",
                'title' => "WEB_PAGE_COMMON_MENU_CLIENT_MANAGER",
                'level' => 2,
                'child' => [
                    // 客户端管理
                    [
                        'name' => "agent_manager",
                        'path' => "clientManager.html#client",
                        'class' => "viconfont vicon-client",
                        'title' => "WEB_PAGE_COMMON_MENU_CLIENT_MANAGERS",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_agent_manager_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_agent_manager_register",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_agent_manager_modify",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_agent_manager_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 应用配置
                            [
                                'name' => "p_agent_manager_application_config",
                                'title' => "WEB_PAGE_COMMON_MENU_CLIENT_APPLICATION_CONFIG",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 客户端升级
                            [
                                'name' => 'p_agent_manager_upgrade',
                                'title' => 'WEB_PAGE_COMMON_MENU_CLIENT_UPGRADE',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 驱动安装
                            [
                                'name' => 'p_agent_manager_driver_install',
                                'title' => 'WEB_PAGE_COMMON_MENU_CLIENT_DRIVER_INSTALL',
                                'function' => [
                                    'get-drivers_agent_install',
                                    'post-drivers_agent_install',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 客户端日志下载
                            [
                                'name' => 'p_agent_log_download',
                                'title' => 'WEB_PAGE_COMMON_MENU_CLIENT_LOG_DOWNLOAD',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 代理配置
                            [
                                'name' => 'p_agent_manager_agent_config',
                                'title' => 'WEB_PAGE_COMMON_MENU_CLIENT_AGENT_CONFIG',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 客户端分组管理
                    [
                        'name' => "client_group",
                        'path' => "clientManager.html#group",
                        'class' => "viconfont vicon-client_groups",
                        'title' => "WEB_PAGE_COMMON_MENU_CLIENT_GROUP_MANAGER",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_client_group_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_client_group_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_client_group_modify",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_client_group_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 移动到分组
                            [
                                'name' => "p_client_group_add_to_group",
                                'title' => "WEB_PAGE_COMMON_MENU_CLIENT_GROUP_MOVE_TO",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 从分组移除
                            [
                                'name' => "p_client_group_delete_from_group",
                                'title' => "WEB_PAGE_COMMON_MENU_CLIENT_GROUP_REMOVE_FROM",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 引导镜像下载
                    [
                        'name' => "client_mirror",
                        'path' => "clientManager.html#mirror",
                        'class' => "viconfont vicon-yindaojingxiang",
                        'title' => "WEB_PAGE_COMMON_MENU_CLIENT_MIRROR",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_client_mirror_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_client_mirror_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 清理
                            [
                                'name' => "p_client_mirror_modify",
                                'title' => "WEB_PAGE_COMMON_MENU_CLIENT_MIRROR_CLEAN",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_client_mirror_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 下载
                            [
                                'name' => "p_agent_mirror_download",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DOWNLOAD",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 传输代理资源池
                    [
                        'name' => 'agent_pool',
                        'path' => 'clientManager.html#pool',
                        'class' => 'viconfont vicon-client_groups',
                        'title' => 'WEB_PAGE_COMMON_MENU_AGENT_POOL',
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_agent_pool_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    /*'get-agent_pools',*/
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_agent_pool_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [
                                    'post-agent_pools',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_agent_pool_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                    'put-agent_pools',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_agent_pool_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                    'delete-agent_pools',
                                    'delete-agent_pools_batch',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ],
                    ],
                ]
            ],
            // NAS设备
            [
                'name' => "nasmanager",
                'path' => "nasManager.html",
                'class' => "viconfont vicon-nasmanager",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_NAS_MANAGER",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_nasmanager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => "p_nasmanager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_nasmanager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_nasmanager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 授权
                    [
                        'name' => "p_nasmanager_licence",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_AUTH",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 挂载
                    [
                        'name' => "p_nasmanager_device_mount",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DEVICE_MOUNT",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 解挂
                    [
                        'name' => "p_nasmanager_device_unmount",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DEVICE_UNMOUNT",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 对象存储
            [
                'name' => "obsmanager",
                'path' => "obsManager.html",
                'class' => "viconfont vicon-a-Instructionzhiling-01",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_OBS_MANAGER",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_obsmanager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => "p_obsmanager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_obsmanager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_obsmanager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 授权
                    [
                        'name' => "p_obsmanager_licence",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_AUTH",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 自动刷新
                    [
                        'name' => 'p_obsmanager_refresh',
                        'title' => 'WEB_PAGE_COMMON_MENU_CLOUD_PLATFORM_AUTO_REFRESH',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    //同步
                    [
                        'name' => 'p_obsmanager_sync',
                        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC',
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // Kubernetes集群
            [
                'name' => "k8s_cluster",
                'path' => "kubernetesCluster.html",
                'class' => "viconfont vicon-jiqun",
                'title' => "WEB_PAGE_COMMON_MENU_K8S_CLUSTER_PROTECT",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_k8s_cluster_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    //添加
                    [
                        'name' => 'p_k8s_cluster_add',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW',
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    //修改
                    [
                        'name' => 'p_k8s_cluster_modify',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY',
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    //删除
                    [
                        'name' => 'p_k8s_cluster_delete',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_DELETE',
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // Microsoft365组织
            [
                'name' => "exchange_organization",
                'path' => "exchangeOrganization.html",
                'class' => "viconfont vicon-zuzhi",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_OFFICE365_ORGANIZATION",
                'level' => 2,
                'child' => [
                    //查看
                    [
                        'name' => 'p_exchange_organization_list',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_LOOK',
                        'function' => [
                            'get-office365_organization',
                        ],
                        'level' => 10,
                    ],
                    //添加
                    [
                        'name' => 'p_exchange_organization_add',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW',
                        'function' => [
                            'post-office365_organization',
                            'get-office365_organization_auth_code',
                            'get-office365_organization_agent',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    //修改
                    [
                        'name' => 'p_exchange_organization_modify',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY',
                        'function' => [
                            'put-office365_organization',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    //自动刷新
                    [
                        'name' => 'p_exchange_organization_refresh',
                        'title' => 'WEB_PAGE_COMMON_MENU_VCENTER_AUTO_REFRESH',
                        'function' => [
                            'get-office365_organization_refresh',
                            'put-office365_organization_refresh',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    //删除
                    [
                        'name' => 'p_exchange_organization_delete',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_DELETE',
                        'function' => [
                            'delete-office365_organization',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    //同步
                    [
                        'name' => 'p_exchange_organization_sync',
                        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC',
                        'function' => [
                            'post-office365_organization_sync'
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // Hadoop集群
            [
                'name' => "hadoop_cluster",
                'path' => "hadoopCluster.html",
                'class' => "viconfont vicon-a-Elephantdaxiang-01",
                'title' => "WEB_PAGE_COMMON_MENU_HADOOP_CLUSTER_PROTECT",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_hadoopmanager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => "p_hadoopmanager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_hadoopmanager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_hadoopmanager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 自动刷新
                    [
                        'name' => 'p_hadoopmanager_refresh',
                        'title' => 'WEB_PAGE_COMMON_MENU_CLOUD_PLATFORM_AUTO_REFRESH',
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 同步
                    [
                        'name' => 'p_hadoopmanager_sync',
                        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC',
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 授权
                    [
                        'name' => 'p_hadoopmanager_license',
                        'title' => 'WEB_PAGE_COMMON_MENU_SETTINGS_AUTH',
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 生产存储
            [
                'name' => "production_storage_manager",
                'path' => "lunStorageManager.html",
                'class' => "viconfont vicon-shengchancunchu",
                'title' => "WEB_PAGE_COMMON_MENU_PRODUCTION_STORAGE_NAME",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_production_storage_manager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_production_storage_manager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_production_storage_manager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_production_storage_manager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 同步
                    [
                        'name' => "p_production_storage_manager_sync",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // Lan-free配置
            [
                'name' => "storage_lanfree",
                'path' => "storageLanFree.html",
                'class' => "viconfont vicon-storage_lanfree",
                'title' => "WEB_PAGE_COMMON_MENU_PALTFORM_STORAGE_LANFREE",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_storage_lanfree_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_storage_lanfree_add",
                        'class' => "",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_storage_lanfree_edit",
                        'class' => "",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_storage_lanfree_delete",
                        'class' => "",
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
    // 存储管理
    [
        'name' => "storage_manager",
        'path' => "storageDevice.html",
        'class' => "viconfont vicon-storage_manager",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_STORAGE",
        'level' => 1,
        'showChild' => false,
        'child' => [
            // 备份存储
            [
                'name' => "manager",
                'path' => "javascript:;",
                'class' => "viconfont vicon-beifencunchu",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_STORAGE_BACKUP",
                'level' => 2,
                'child' => [
                    // 存储设备
                    [
                        'name' => "storage_manager_list",
                        'path' => "storageManager.html#manager",
                        'class' => "viconfont vicon-beifencunchu",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_STORAGE_BACKUP",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_storage_manager_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 新建
                            [
                                'name' => "p_storage_manager_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_storage_manager_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_storage_manager_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 导入数据管理
                            [
                                'name' => "p_storage_manager_data",
                                'title' => "WEB_PAGE_COMMON_MENU_PALTFORM_STORAGE_DATA",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 自动导入时间点配置
                            /* [
                                 'name' => "p_storage_manager_timepoint_config",
                                 'title' => "WEB_PAGE_COMMON_MENU_PALTFORM_STORAGE_TIMEPOINT_CONFIG",
                                 'function' => [
                                 ],
                                 'level' => 10,
                             ],*/
                            // 手动同步
                            [
                                'name' => "p_storage_manager_sync",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DES_SYNC",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ],
                    ],
                    // 存储资源池
                    [
                        'name' => "storage_pool",
                        'path' => "storageManager.html#pool",
                        'class' => "viconfont vicon-cunchuziyuanchi",
                        'title' => "WEB_PAGE_COMMON_MENU_STORAGE_POOL",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_storage_pool_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    /*'get-storage_pools'*/
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_storage_pool_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [
                                    'post-storage_pools',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_storage_pool_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                    'put-storage_pools',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_storage_pool_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                    'delete-storage_pools',
                                    'delete-storage_pools_batch',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ],
                    ],
                ]
            ],
            // 磁带
            [
                'name' => "tape_manage",
                'path' => "javascript:;",
                'class' => "viconfont vicon-a-Tapecidai",
                'title' => "WEB_PAGE_COMMON_MENU_TAPE_MANAGE",
                'level' => 2,
                'showChild' => true,
                'child' => [
                    //磁带设备
                    [
                        'name' => "tape_device",
                        'path' => "tapeEquipment.html#manager",
                        'class' => "viconfont vicon-a-Tapecidai",
                        'title' => "WEB_PAGE_COMMON_MENU_TAPE_EQUIPMENT",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_tape_device_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 新建
                            [
                                'name' => "p_tape_device_add",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_tape_device_edit",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_tape_device_delete",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 冻结
                            [
                                'name' => "p_tape_device_freeze",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_TAPE_FREEZE",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 解冻
                            [
                                'name' => "p_tape_device_defrost",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_TAPE_DEFROSTD",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 导入
                            [
                                'name' => "p_tape_device_import",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_IMPORT",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 数据检索
                            [
                                'name' => "p_tape_device_retrieval",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_TAPE_CARRIAGE_RETRIEVAL",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 扫描
                            [
                                'name' => "p_tape_device_scan",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_TAPE_SCAN",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 弹出
                            [
                                'name' => "p_tape_device_export",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_TAPE_CARRIAGE_EXPORT",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 日志
                            [
                                'name' => "p_tape_monitor_list",
                                'title' => "WEB_PAGE_COMMON_MENU_TAPE_MONITOR",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                        ],
                    ],
                    //磁带任务
                    [
                        'name' => "tape_task",
                        'path' => "tapeEquipment.html#job",
                        'class' => "viconfont vicon-a-Type-drivecidai",
                        'title' => "WEB_PAGE_COMMON_MENU_TAPE_JOB",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_tape_task_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                        ],
                    ],
                    // 磁带数据
                    [
                        'name' => "tape_manage_data",
                        'path' => "tapeEquipment.html#data",
                        'class' => "viconfont vicon-shujuguanli",
                        'title' => "WEB_PAGE_COMMON_MENU_TAPE_DATA",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_tape_manage_data_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 导出
                            [
                                'name' => "p_tape_manage_data_export",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_EXPORT",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                        ],
                    ],
                ]
            ],

        ]
    ],
    // 备份资源
    [
        'name' => "backup_manager",
        'path' => "backupResources.html",
        'class' => "viconfont vicon-cunchu1",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_BACKUP_RESOURCE",
        'level' => 1,
        'showChild' => false,
        'child' => [
            // 备份节点
            [
                'name' => 'node',
                'path' => "javascript:;",
                'class' => "viconfont vicon-node_manager",
                'title' => "WEB_PAGE_COMMON_MENU_PALTFORM_NODE",
                'level' => 2,
                'child' => [
                    // 节点管理
                    [
                        'name' => "node_manager",
                        'path' => "node.html#manager",
                        'class' => "viconfont vicon-node_manager",
                        'title' => "WEB_PAGE_COMMON_MENU_PALTFORM_NODE",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_node_manager_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_node_manager_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_node_manager_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_node_manager_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 网络配置
                            [
                                'name' => "p_node_manager_network",
                                'title' => "WEB_PAGE_COMMON_MENU_NODE_TRANSFER_NETWORK_CONFIG",
                                'function' => [
                                    /*'get-nodes_network',  // 节点网络*/
                                    'post-nodes_network',
                                    'patch-nodes_network',
                                    'delete-nodes_network',
                                    'post-nodes_network_sort',
                                    'get-nodes_allocation',
                                    'post-nodes_allocation',
                                    /*'get-network_pools',  // 网络资源池*/
                                    'post-network_pools',
                                    'put-network_pools',
                                    'delete-network_pools',
                                    'delete-batchDeleteNetworkPool',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 缓存配置
                            [
                                'name' => "p_node_manager_cache",
                                'title' => "WEB_PAGE_COMMON_MENU_NODE_CACHE_CONFIG",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 资源限制
                            [
                                'name' => "p_node_manager_resource_limit",
                                'title' => "WEB_PAGE_COMMON_MENU_NODE_RESOURCE_LIMIT",
                                'function' => [
                                    'get-nodes_resources_limit',
                                    'put-nodes_resources_limit',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 计算资源池
                    [
                        'name' => 'node_pool',
                        'path' => 'node.html#pool',
                        'class' => "viconfont vicon-node_manager",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_NODE_POOL",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_node_pool_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    /*'get-node_pools',*/
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_node_pool_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [
                                    'post-node_pools',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_node_pool_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                    'put-node_pools',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_node_pool_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                    'delete-node_pools',
                                    'delete-node_pools_batch',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ],
                    ],
                ],
            ],

            // 集群管理
            [
                'name' => 'cluster_manager',
                'path' => 'clusterManager.html',
                'class' => 'viconfont vicon-jiqun1',
                'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_CLUSTER_MANAGER',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_cluster_manager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-cluster_config',  // 获取集群配置信息
                            'get-getClusterOperateLog',  // 获取集群操作日志
                        ],
                        'level' => 10,
                    ],
                    // 集群配置
                    [
                        'name' => "p_cluster_manager_config",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MANAGEMENT",
                        'function' => [
                            'put-cluster_config',  // 配置集群信息
                            'post-cluster_start',  // 启动集群
                            'post-cluster_stop',  // 停止集群
                            'post-setClusterMasterNode', // 设置主节点
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ],
            ],
            // 驱动库管理
            [
                'name' => 'driver_manager',
                'path' => 'driverManager.html',
                'class' => 'viconfont vicon-a-Hunting-gearcongdongzhuangzhi',
                'title' => 'WEB_PAGE_COMMON_MENU_DRIVER_MANAGER',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_driver_manager_list',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_LOOK',
                        'function' => [
                            'get-drivers',
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => 'p_driver_manager_add',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_ADD',
                        'function' => [
                            'post-drivers',
                            'post-drivers_file'
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_driver_manager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'delete-drivers',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ],
            ],

            // 脚本管理
            [
                'name' => "scripts_manager",
                'path' => "scriptsManager.html",
                'class' => "viconfont vicon-node_manager",
                'title' => "WEB_PAGE_COMMON_MENU_K8S_SCRIPT_MANAGE",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_scripts_manager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => "p_scripts_manager_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_scripts_manager_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_scripts_manager_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 备份策略
            [
                'name' => "global_strategy",
                'path' => "globalStrategy.html",
                'class' => "viconfont vicon-global_strategy",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_GLOBAL_STRATEGY",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_global_strategy_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_global_strategy_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_global_strategy_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_global_strategy_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 限速策略
            [
                'name' => "global_speed_strategy",
                'path' => "globalSpeed.html",
                'class' => "viconfont vicon-xiansucelve",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_GLOBAL_SPEED_STRATEGY",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_global_speed_strategy_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_global_speed_strategy_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_global_speed_strategy_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_global_speed_strategy_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 分发
                    [
                        'name' => "p_global_speed_strategy_send",
                        'title' => "WEB_PAGE_COMMON_MENU_GLOBAL_STRATEGY_DISTRIBUTE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 资源组
            [
                'name' => "resource_group",
                'path' => "resourceGroup.html",
                'class' => "viconfont vicon-resource_group",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RESOURCE_GROUP",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_resource_group_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-resources_group',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_resource_group_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                            'post-resources_group_add',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_resource_group_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                            'get-resources_groupDetail',
                            'post-resources_group',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_resource_group_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            '23-deleteResourceGroup',
                            'delete-resources_group',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 病毒库管理
            [
                'name' => 'virus',
                'path' => 'virus.html',
                'class' => 'viconfont vicon-Frame1',
                'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_VIRUS_MANAGE',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_virus_list',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_LOOK',
                        'function' => [],
                        'level' => 10,
                    ],
                    // 操作
                    [
                        'name' => "p_virus_operate",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
        ]
    ],
    // 容灾演练平台
    [
        'name' => "vm_machine_manager",
        'path' => "vmManager.html",
        'class' => "viconfont vicon-ge_disaster_recovery",
        'title' => "WEB_PAGE_COMMON_MENU_VM_MACHINE_MANAGER",
        'level' => 1,
        'showChild' => false,
        'child' => [
            // 概览
            [
                'name' => "vm_machine_network",
                'path' => "vmManager.html#network",
                'class' => "viconfont vicon-vm_overview",
                'title' => "WEB_PAGE_COMMON_MENU_VM_MACHINE_NETWORK",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_vm_machine_network_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-virtual_network',
                            'get-virtual',
                        ],
                        'level' => 10,
                    ],
                    // 添加
                    [
                        'name' => "p_vm_machine_network_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                        'function' => [
                            'post-virtual_network'
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    /*// 修改
                    [
                        'name' => "p_vm_machine_network_modify",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                            'post-virtual_network'
                        ],
                        'level' => 10,
                    ],*/
                    // 删除
                    [
                        'name' => "p_vm_machine_network_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'delete-virtual_network'
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 资源隔离
                    [
                        'name' => "p_vm_machine_partition",
                        'title' => "WEB_PAGE_COMMON_MENU_VM_MACHINE_PARTITION",
                        'function' => [
                            'get-virtual_resources',
                            'post-virtual_resources',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 主机列表
            [
                'name' => "vm_machine_list",
                'path' => "vmManager.html#list",
                'class' => "viconfont vicon-client",
                'title' => "WEB_PAGE_COMMON_MENU_VM_MACHINE_LIST",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_vm_machine_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-virtual',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => "p_vm_machine_modify",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                            'post-virtual',
                            'get-virtual_tree',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_vm_machine_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'delete-virtual',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 开机
                    [
                        'name' => "p_vm_machine_on",
                        'title' => "WEB_PAGE_COMMON_MENU_POWER_ON",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 关机
                    [
                        'name' => "p_vm_machine_off",
                        'title' => "WEB_PAGE_COMMON_MENU_POWER_OFF",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 重启
                    [
                        'name' => "p_vm_machine_restart",
                        'title' => "WEB_PAGE_COMMON_MENU_VM_MACHINE_POWER_RESTART",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 代理网关
            [
                'name' => "vm_machine_proxy_gateway",
                'path' => "vmManager.html#gateway",
                'class' => "viconfont vicon-client_groups",
                'title' => "WEB_PAGE_COMMON_MENU_DRILLS_AGENT_GATEWAY",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "vm_machine_proxy_gateway_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-virtual_proxy'
                        ],
                        'level' => 10,
                    ],
                    // 开机
                    [
                        'name' => "p_vm_machine_proxy_gateway_on",
                        'title' => "WEB_PAGE_COMMON_MENU_POWER_ON",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 关机
                    [
                        'name' => "p_vm_machine_proxy_gateway_off",
                        'title' => "WEB_PAGE_COMMON_MENU_POWER_OFF",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 重启
                    [
                        'name' => "p_vm_machine_proxy_gateway_restart",
                        'title' => "WEB_PAGE_COMMON_MENU_VM_MACHINE_POWER_RESTART",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 操作日志
            [
                'name' => "vm_machine_operate_log",
                'path' => "vmManager.html#log",
                'class' => "viconfont vicon-client_groups",
                'title' => "WEB_PAGE_COMMON_MENU_VM_MACHINE_LOG",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "vm_machine_operate_log_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-virtual_logs'
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
];