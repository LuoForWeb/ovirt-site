<?php

/**
 * 配置的一些信息
 */

return [
    'PUSH_RESPONSE_TYPE' => [
        'AUTO' => 2,
        'MANUAL' => 3
    ],

    // 系统安全之数据安全的保留策略枚举
    'SYSTEM_CONFIG_ITEM' => [
        'ITEM_UNKNOWN' => 0,
        'ITEM_HISTORICAL_TASK' => 1, // 历史任务
        'ITEM_TASK_LOG' => 2, // 任务日志
        'ITEM_SYSTEM_LOG' => 3, // 系统日志
        'ITEM_CLUSTER_HA_LOG' => 4, // 集群高可用日志
        'ITEM_TASK_ALARM' => 5, // 任务告警
        'ITEM_SYSTEM_ALARM' => 6, // 系统告警
    ],
    // 保留类型
    'RESERVED_TYPE' => [
        'SYSTEM_RESERVED_STRATEGY_TYPE_UNKNOWN' => 0,
        'SYSTEM_RESERVED_STRATEGY_TYPE_NUM' => 1,      // reserved number type 数量
        'SYSTEM_RESERVED_STRATEGY_TYPE_DAY' => 2,           // reserved day type 天
        'SYSTEM_RESERVED_STRATEGY_TYPE_PERMANENT' => 3, // reserved permanent type 永久
    ],
    // 保留模式
    'RESERVED_STRATEGY_MODE' => [
        'UNKNOWN' => 0,
        'POINT' => 1,  // 按备份点保留
        'CHAIN' => 2,  // 按备份链保留
    ],

    "SYSTEM_BACKUP_CONFIG" => [
        [ // 任务
            "id" => "task_info",
            "title" => xphp_get_lang('UI_PUBLIC_JOB'),
            "child" => [
                [
                    "id" => "current_job", // 当前任务
                    "title" => xphp_get_lang('UI_PLATFORM_CURRENT_JOB'),
                    "database_table" => [
                        "bd_task", // 任务总表
                        "bd_task_safe_config",// 安全策略
                        "bd_running_info", // 任务运行表
                        "bd_strategy", // 策略总表
                        "bd_time_strategy", // 时间策略
                        "bd_reserved_strategy", // 保留策略
                        "bd_storage_strategy", // 存储策略
                        "bd_task_speed_limit_strategy", // 速度策略
                        "bd_transport_strategy", // 传输策略
                        "bd_task_gfs_retention_strategy", // GFS策略
                        "bd_retry_strategy", // 重试策略
                        "bd_gfs_entity_waiting_map", // GFS等待map表
                        "bd_node_network", // 节点网络
                        "bd_task_agent_list", // 备份/恢复代理列表
                        "bd_grain_recovery_task", // 细粒度恢复
                        "bd_instant_recovery_task", // 瞬时恢复
                        "bd_grain_recovery_transport", // 细粒度文件传输信息
                        "bd_grain_recovery_share", // 细粒度共享文件信息
                        "bd_task_agent_pool", // 任务代理池表
                        // 虚拟机任务相关表
                        "vm_task", // 虚拟机任务表
                        "vm_machine_list", // 虚拟机列表
                        "vm_object_list", // 虚拟机对象
                        // 文件模块任务相关表
                        "fs_task", // 文件模块任务表
                        "fs_running_info", // 文件模块运行表
                        "fs_path_list", // 文件模块路径
                        "nas_task", // nas 任务表
                        // 数据库任务相关表
                        "db_task", // 数据库任务表
                        // "db_instance", // 数据库实例(6.0已弃用)
                        "db_list", // 数据库列表
                        // 整机任务相关表
                        "os_task", // 整机任务表
                        "os_list", // 备份/恢复整机列表
                        // Microsoft 365任务相关表
                        "m365_task", // Microsoft 365任务表
                        "m365_object_list", // Microsoft 365列表
                        "m365_running_info", // Microsoft 365运行表
                        // kubernetes相关任务表
                        "kube_task", // kubernetes任务表
                        "kube_running_info", // kubernetes运行表
                        "kube_object_list", // kubernetes对象
                        // 整机实时相关任务表
                        "cdp_vol_task", // 整机实时任务表
                        "cdp_vol_task_cache_info", // 卷实时缓存信息表
                        "cdp_vol_task_data_consistency_check_info", // 卷任务数据一致性检查信息表
                        "cdp_vol_task_io_mapping_vol", // 卷实时I/O映射卷表 
                        "cdp_vol_task_progress_info", // 卷实时任务进度信息表
                        "cdp_vol_task_running_info", // 卷实时任务运行信息表
                        "cdp_vol_task_takeover_app", // 卷实时任务接管应用表
                        "cdp_vol_task_takeover_failback_info", // 卷实时接管回切信息表
                        "cdp_vol_task_takeover_info", // 卷实时接管信息表
                        "cdp_vol_task_takeover_lun", // 卷实时接管lun信息表
                        "cdp_vol_task_takeover_script", // 卷实时接管脚本表
                        "cdp_vol_task_takeover_target", // 卷实时接管target表
                        "cdp_vol_task_vol", // 卷实时任务卷表
                        "cdp_vol_task_exclude_vol", // 卷实时任务过滤卷
                        "cdp_vol_task_restore_info", // 卷实时恢复任务配置
                        "cdp_vol_task_high_pressure_strategy", // 卷实时高负载配置
                        "cdp_vol_task_common_script", // 卷实时任务通用脚本
                        "cdp_vol_task_disk", // 卷实时备份目标磁盘设备
                        "cdp_vol_task_takeover_part", // 卷实时接管part信息
                        "cdp_vol_backup_agent_invalid_period", // 卷实时无效备份时间段
                        // 副本任务相关表
                        "copy_task", // 副本任务表
                        "copy_list", // 副本对象列表
                        "copy_running_info", // 副本任务运行表
                        // 数据库、文件cdp模块任务相关表
                        "cdp_db_host", // 数据实时主机表
                        "cdp_db_task", // 数据实时任务表
                        "cdp_fs_task", // 文件实时任务表
                        // 文件复制相关任务表
                        "sync_task", // 文件复制任务表
                        "sync_running_info", // 文件复制运行表
                        "sync_task_path_list", // 文件复制任务路径清单
                        // 数据库实时任务相关表
                        "cdp_db_dr_task", // 数据实时任务表
                        "cdp_db_dr_task_app_info", // 数据实时任务应用信息表
                        "cdp_db_dr_task_cache_info", // 数据实时任务缓存信息表
                        "cdp_db_dr_task_progress_info", // 数据实时任务进度信息表
                        "cdp_db_dr_task_takeover_info", // 数据实时任务接管信息表
                        "cdp_db_dr_task_takeover_failback_info", // 数据实时任务回切信息表
                        // 验证任务相关表
                        "sr_surebackup", // 验证任务表
                        "sr_surebackup_item", // 验证任务对象表
                    ],
                ],
                [
                    "id" => "history_job", // 历史任务
                    "title" => xphp_get_lang('UI_PLATFORM_HOSTORY_JOB'),
                    "database_table"=>[
                        "bd_history_task", // 历史任务总表
                        "bd_storage_monitor", // 存储监控表
                        "sr_sure_backup_report", // 虚拟实验室自动验证报告表
                        "cdp_vol_history_task", // 卷CDP模块历史任务信息
                        "os_migration_history", // 操作系统迁移历史
                    ],
                ],
            ],
        ],
        [ // 告警
            "id" => "alarm_info",
            "title" => xphp_get_lang('UI_ALARM_TITLE'),
            "child" => [
                [
                    "id" => "task_alarm", // 任务告警
                    "title" => xphp_get_lang('UI_PLATFORM_ALARM_TASK'),
                    "database_table" => [
                        "bd_task_alarm",
                    ],
                ],
                [
                    "id" => "system_alarm", // 系统告警
                    "title" => xphp_get_lang('UI_PLATFORM_ALARM_SYSTEM'),
                    "database_table" => [
                        "bd_system_alarm",
                    ],
                ],
            ],
        ],
        [ // 日志
            "id" => "log_info",
            "title" => xphp_get_lang('UI_LOG_TITLE'),
            "child" => [
                [
                    "id" => "job_log", // 任务日志
                    "title" => xphp_get_lang('UI_PLATFORM_JOB_LOG'),
                    "database_table" => [
                        "bd_task_log",
                    ],
                ],
                [
                    "id" => "system_log", // 系统日志
                    "title" => xphp_get_lang('UI_PLATFORM_SYSTEM_LOG'),
                    "database_table" => [
                        "bd_system_log",
                    ],
                ],
                [
                    "id" => "cluster_ha_log", // 备份系统集群日志
                    "title" => xphp_get_lang('UI_PLATFORM_HA_LOG'),
                    "database_table" => [
                        "bd_cluster_ha_log",
                    ],
                ],
            ],
        ],
        [ // 用户管理
            "id" => "user_management",
            "title" => xphp_get_lang('UI_PALTFORM_MANAGER_USER'),
            "child" => [
                [
                    "id" => "user_info", // 用户
                    "title" => xphp_get_lang('UI_PLATFORM_USER'),
                    "database_table" => [
                        "bd_user",
                        "bd_user_extension",
                        "bd_user_resource_transfer",
                        "bd_account_safe",
                    ],
                ],
                [
                    "id" => "user_group", // 用户组
                    "title" => xphp_get_lang('UI_PLATFORM_SAFETY_USER_GROUP'),
                    "database_table" => [
                        "bd_user_group",
                        "mt_user_user_group",
                    ],
                ],
                [
                    "id" => "role_info", // 角色
                    "title" => xphp_get_lang('UI_PLATFORM_SAFETY_ROLE'),
                    "database_table" => [
                        "bd_role",
                        "mt_user_role",
                        "mt_user_group_role",
                        "bd_permission",
                    ],
                ],
                [
                    "id" => "domain_server", // 域服务器
                    "title" => xphp_get_lang('UI_PLATFORM_SAFETY_DOMAIN'),
                    "database_table" => [
                        "bd_domain_server",
                    ],
                ],
            ],
        ],
        [ // 基础设施
            "id" => "infrastructure_info",
            "title" => xphp_get_lang('UI_PLATFORM_INFRASTRUCTURE'),
            "child" => [
                [
                    "id" => "vcenter_Info", // 虚拟化中心
                    "title" => xphp_get_lang('UI_PLATFORM_VCENTER'),
                    "database_table" => [
                        "vm_vcenter", // 虚拟化中心
                        "vm_tree",
                        "vm_host",
                    ],
                ],
                [
                    "id" => "private_cloud", // 私有云
                    "title" => xphp_get_lang('UI_PLATFORM_PRIVATE_CLOUD'),
                    "database_table" => [
                        "vm_vcenter", // 虚拟化中心
                        "vm_tree",
                        "vm_host",
                    ],
                ],
                [
                    "id" => "public_cloud", // 公有云
                    "title" => xphp_get_lang('UI_PLATFORM_PUBLIC_CLOUD'),
                    "database_table" => [
                        "vm_vcenter", // 虚拟化中心
                        "vm_tree",
                        "vm_host",
                    ],
                ],
                [
                    "id" => "client_device", // 代理&客户端
                    "title" => xphp_get_lang('UI_CLIENT_MANAGER'),
                    "database_table" => [
                        "bd_agent", // 代理表 
                        "bd_agent_app", // 代理端应用表
                        "bd_agent_disk", // 代理端磁盘信息表
                        "bd_agent_group", // 代理组表
                        "bd_agent_vol", // 代理端卷信息表
                        "bd_agent_pool", // 代理资源池
                        "bd_agent_pool_list", // 代理资源池关联代理
                    ],
                ],
                [
                    "id" => "nas_device", // nas设备
                    "title" => xphp_get_lang('UI_PLATFORM_NAS_MANAGER'),
                    "database_table" => [
                        "nas_storage_resource", // NAS存储资源表
                        "nas_mount_list", // NAS挂载表
                    ],
                ],
                [
                    "id" => "obs_storage", // 对象存储
                    "title" => xphp_get_lang('UI_PLATFORM_OBS_STORAGE'),
                    "database_table" => [
                        "obs_resource", // 对象存储资源
                    ],
                ],
                [
                    "id" => "kubernets_cluster", // Kubernetes集群
                    "title" => xphp_get_lang('WEB_K8S_CLUSTER_PROTECT'),
                    "database_table" => [
                        "kube_cluster", // Kubernetes集群表
                        "kube_node", // Kubernetes真实全部Node节点表
                    ],
                ],
                [
                    "id" => "microsoft_365", // Microsoft 365
                    "title" => xphp_get_lang('UI_PLATFORM_MICROSOFT365'),
                    "database_table" => [
                        "m365_organization", // m365组织表
                        "m365_azure_ad_app", // m365 AD应用表
                        "m365_user", // m365用户表
                    ],
                ],
                [
                    "id" => "hadoop_cluster", // hadoop 集群
                    "title" => xphp_get_lang('WEB_HADOOP_CLUSTER_PROTECT'),
                    "database_table" => [
                        "hadoop_cluster", // hadoop 集群信息表
                        "hadoop_namenode", // namenode信息表
                    ],
                ],
                [
                    "id" => "lan_free", // LAN-Free 配置
                    "title" => xphp_get_lang('UI_PALTFORM_STORAGE_LANFREE'),
                    "database_table" => [
                        "bd_storage_resource", // 存储资源表
                    ],
                ],
                [
                    "id" => "virtual_lab", // 虚拟演练室
                    "title" => xphp_get_lang('UI_PLATFORM_VIRTUAL_LAB'),
                    "database_table" => [
                        "sr_network_map_list", // 自动验证网络映射表
                        "sr_virtual_lab", // 自动验证虚拟实验室
                    ],
                ],
                [
                    "id" => "lab_app_group", // 应用组
                    "title" => xphp_get_lang('UI_PLATFORM_LAB_APP_GROUP'),
                    "database_table" => [
                        "sr_application_group", // 自动验证应用组
                        "sr_application_item", // 自动验证应用组对象
                    ],
                ],
                [
                    "id" => "virus_lib", // 病毒库管理
                    "title" => xphp_get_lang('WEB_PLATFORM_VIRUS_MANAGE'),
                    "database_table" => [
                        "bd_virus_library", // 病毒库
                    ],
                ],
            ],
        ],
        [ // 备份资源
            "id" => "backup_resource",
            "title" => xphp_get_lang('UI_PLATFORM_BACKUP_RESOURCE'),
            "child" => [
                [
                    "id" => "node_info", // 节点管理
                    "title" => xphp_get_lang('UI_PALTFORM_NODE'),
                    "database_table" => [
                        "bd_node", // 节点表
                        "bd_system_cache_config", // 任务缓存配置表
                        "bd_node_network", // 节点网络表
                        "bd_node_network_pool", // 网络资源池表
                        "bd_node_network_pool_list", // 网络资源池网卡列表
                        "bd_node_pool", // 节点资源池表
                        "bd_node_pool_list", // 节点资源池节点列表
                        "bd_resource_limiting_strategy_node_config", // 节点的资源限制
                        "bd_module_server", // 节点服务列表
                    ],
                ],
                [
                    "id" => "cluster_info", // 集群管理
                    "title" => xphp_get_lang('UI_CLUSTER_MANAGER'),
                    "database_table" => [
                        "bd_cluster", // 集群信息
                        "bd_cluster_node", // 集群节点信息
                        "bd_cluster_node_network", // 集群节点网络
                        "bd_cluster_log",
                    ],
                ],
                [
                    "id" => "drive_manage", // 驱动库管理
                    "title" => xphp_get_lang('WEB_DRIVER_MANAGER'),
                    "database_table" => [
                        "bd_driver", // 驱动信息
                        "bd_driver_detail", // 驱动详情
                    ],
                ],
                [
                    "id" => "script_manage", // 脚本管理
                    "title" => xphp_get_lang('WEB_k8s_SCRIPT_MANAGE'),
                    "database_table" => [
                        "bd_script", // 脚本信息
                    ],
                ],
                [
                    "id" => "strategy_group", // 备份策略
                    "title" => xphp_get_lang('UI_JOB_STRATEGY_INFO'),
                    "database_table" => [
                        "bd_strategy_group", // 策略组
                    ],
                ],
                [
                    "id" => "speed_limit", // 限速策略
                    "title" => xphp_get_lang('UI_GLOBAL_STRATEGY_SPEED_LIMIT'),
                    "database_table" => [
                        "bd_global_speed_limit_strategy", // 全局限速策略
                    ],
                ],
                [
                    "id" => "resource_group", // 资源组
                    "title" => xphp_get_lang('UI_PLATFORM_RESOURCE_GROUP'),
                    "database_table" => [
                        "bd_resource_group", // 资源组表
                        "mt_resource_resource_group", // 资源组表
                        "mt_user_group_resource_group", // 用户组-资源组表
                        "mt_user_resource_group", // 用户-资源组表
                        "mt_user_resource", // 用户-资源组表
                    ],
                ],
                [
                    "id" => "storage_resource", // 存储设备
                    "title" => xphp_get_lang('UI_JOB_STORAGE_DEV'),
                    "database_table" => [
                        "bd_storage_resource", // 存储设备
                        "bd_tape_library", // 磁带库表
                        "bd_tape_carriage", // 磁带信息表
                        "bd_tape_driver", // 磁带驱动器表
                        "bd_tape_group", // 磁带组表
                        "bd_shared_storage_node_layout", // 共享存储挂载节点表
                        "bd_storage_resource_pool", // 存储资源池
                        "bd_storage_resource_pool_list", // 存储资源池存储表
                    ],
                ],
            ],
        ],
    ],
    
    //导出文件密码
    'ZIP_PASS' => 'Backup@4R',

    "SYSTEM_RECOVERY_TYPE" => [
        'AUTO' => 0,     //自动备份源
        'MANUAL' => 1,   //手动上传源
    ],
	'IMPORT_INFO' => [
		'uploadfile' => [
			'name' => 'files',
			'suffixes' => 'zip',
			'size' => 1048576000,
        ],
    ],

    // 消息发送类型
    'MESSAGE_TYPE' => [
        'DB_DRILL' => 2,  // 数据库演练任务
    ],
];
