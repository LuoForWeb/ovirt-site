<?php

/**
 * 用户相关的配置信息
 */

return [

    //用户组类型
    "USER_GROUP_TYPE" => array(
        'UNKNOWN' => 0,
        'DEFAULT' => 1,
        'GLOBAL' => 2,
        'TENANT' => 3,
    ),

    //用户类型定义
    'USERTYPE' => array(
        'operator' => 1,
        'auditor' => 2,
        'manager' => 3,
        "administrator" => 4,
    ),
    //用户类型定义
    'USERTYPEINT' => array(
        1 => 'operator',
        2 => 'auditor',
        3 => 'manager',
        4 => "administrator",
    ),
    // 用户type定义
    'USER_TYPES' => [
        'USER_LOCATION' => 1, // 本地全局/普通用户
        'USER_EXTERNAL' => 2, // ad域用户
        'USER_ADMIN' => 3, // 本地/管理员
    ],

    // 用户权限重构的模块标识定义--管理用户
    // 说明：查看和操作的权限标识在uuid的后面分别跟上 _look 和 _operate 就行 name 是语言包键名
    'USER_AUTH_LIST' => [
        [
            'uuid' => 'vmprotect', // 虚拟机保护
            'name' => 'UI_PLATFORM_VM_VIRTUAL', // 语言包键名
            'permission' => 'vmprotect', // 对应的page权限
            'type' => 1, // 模块
            'val' => 1, // 对应的后台枚举
        ],
        [
            'uuid' => 'awsprotect', // 公有云保护
            'name' => 'UI_PLATFORM_PUBLIC_CLOUD', // 语言包键名
            'permission' => 'awsprotect',
            'type' => 1, // 模块
            'val' => 2,
        ],
        [
            'uuid' => 'prcloud_protect', // 私有云保护
            'name' => 'UI_PLATFORM_PRIVATE_CLOUD', // 语言包键名
            'permission' => 'prcloud_protect',
            'type' => 1, // 模块
            'val' => 3,
        ],
        [
            'uuid' => 'complete_machine', // 整机定时
            'name' => 'UI_REPORT_TIMING_COMPLETE_BACKUP', // 语言包键名
            'permission' => 'complete_machine',
            'type' => 1, // 模块
            'val' => 4,
        ],
        [
            'uuid' => 'osbackup', // 卷
            'name' => 'UI_REPORT_TIMING_MACHINE_REEL_BACKUP', // 语言包键名
            'permission' => 'osbackup',
            'type' => 1, // 模块
            'val' => 5,
        ],
        [
            'uuid' => 'fileprotect', // 文件保护
            'name' => 'UI_PLATFORM_FILES', // 语言包键名
            'permission' => 'filebackup',
            'type' => 1, // 模块
            'val' => 6,
        ],
        [
            'uuid' => 'nas_protect', // Nas保护
            'name' => 'UI_PLATFORM_NAS', // 语言包键名
            'permission' => 'nas_protect',
            'type' => 1, // 模块
            'val' => 7,
        ],
        [
            'uuid' => 'obs_protect', // 对象存储保护
            'name' => 'UI_PLATFORM_OBS_STORAGE', // 语言包键名
            'permission' => 'obs_protect',
            'type' => 1, // 模块
            'val' => 8,
        ],
        [
            'uuid' => 'hadoop_protect', // Hadoop保护
            'name' => 'UI_PLATFORM_HADOOP_HDFS', // 语言包键名
            'permission' => 'hadoop_protect',
            'type' => 1, // 模块
            'val' => 9,
        ],
        [
            'uuid' => 'db_protect', // 数据库保护
            'name' => 'UI_PLATFORM_DATABASE', // 语言包键名
            'permission' => 'db_protect',
            'type' => 1, // 模块
            'val' => 10,
        ],
        [
            'uuid' => 'application_protect', // Microsoft365 应用保护
            'name' => 'UI_PLATFORM_MICROSOFT365', // 语言包键名
            'permission' => 'office365_protect',
            'type' => 1, // 模块
            'val' => 11,
        ],
        [
            'uuid' => 'k8s_protect', // k8s 集群
            'name' => 'UI_PLATFORM_K8S', // 语言包键名
            'permission' => 'k8s_protect',
            'type' => 1, // 模块
            'val' => 12,
        ],
        [
            'uuid' => 'cbrbackup', // 云存储同步
            'name' => 'UI_PLATFORM_STORAGE_SYNC', // 语言包键名
            'permission' => 'cbrbackup',
            'type' => 1, // 模块
            'val' => 13,
        ],
        [
            'uuid' => 'complete_cdp_backup', // 实时容灾保护
            'name' => 'UI_REPORT_REAL_TIME_COMPLETE_BACKUP', // 语言包键名
            'permission' => 'complete_cdp_backup',
            'type' => 1, // 模块
            'val' => 14,
        ],
        [
            'uuid' => 'vol_cdp_backup', // 实时容灾保护-卷
            'name' => 'UI_REPORT_REAL_TIME_MACHINE_REEL_BACKUP', // 语言包键名
            'permission' => 'vol_cdp_backup',
            'type' => 1, // 模块
            'val' => 15,
        ],
        [
            'uuid' => 'dbprotect', // 数据库实时备份,永思
            'name' => 'UI_PLATFORM_DB_REALTIME_BACKUP', // 语言包键名
            'permission' => 'dbprotect',
            'type' => 1, // 模块
            'val' => 16,
        ],
        [
            'uuid' => 'machine_copy', // 整机复制
            'name' => 'UI_REPORT_COPY_COMPLETE_BACKUP', // 语言包键名
            'permission' => 'machine_copy',
            'type' => 1, // 模块
            'val' => 17,
        ],
        [
            'uuid' => 'vol_cdp_copy', // 卷复制
            'name' => 'UI_REPORT_COPY_MACHINE_REEL_BACKUP', // 语言包键名
            'permission' => 'vol_cdp_copy',
            'type' => 1, // 模块
            'val' => 18,
        ],
        [
            'uuid' => 'file_copy_protect', // 文件复制
            'name' => 'UI_FILE_COPY', // 语言包键名
            'permission' => 'file_copy_protect',
            'type' => 1, // 模块
            'val' => 19,
        ],
        [
            'uuid' => 'dbcdpcopy', // 数据库复制
            'name' => 'UI_DB_CDP_COPY', // 语言包键名
            'permission' => 'dbcdpcopy',
            'type' => 1, // 模块
            'val' => 20,
        ],
        [
            'uuid' => 'data_manager', // 备份数据管理
            'name' => 'UI_PLATFORM_BACKUP_DATA', // 语言包键名
            'permission' => 'backup_data',
            'type' => 2, // 资源
            'val' => 1,
        ],
        [
            'uuid' => 'current_job', // 当前任务
            'name' => 'UI_PLATFORM_CURRENT_JOB', // 语言包键名
            'permission' => 'current_job',
            'type' => 2, // 资源
            'val' => 2,
        ],
        [
            'uuid' => 'history_job', // 历史任务
            'name' => 'UI_PLATFORM_HOSTORY_JOB', // 语言包键名
            'permission' => 'history_job',
            'type' => 2, // 资源
            'val' => 3,
        ],
        [
            'uuid' => 'resmanagement', // 资源
            'name' => 'UI_PLATFORM_RESOURCE_MANAGER', // 语言包键名
            'permission' => 'resmanagement',
            'type' => 2, // 资源
            'val' => 4,
        ],
        [
            'uuid' => 'alarm', // 告警
            'name' => 'UI_PLATFORM_ALARM', // 语言包键名
            'permission' => 'alarm',
            'type' => 3, // 其它
            'val' => 5,
        ],
        [
            'uuid' => 'log', // 日志
            'name' => 'UI_PLATFORM_LOG', // 语言包键名
            'permission' => 'log',
            'type' => 3, // 其它
            'val' => 6,
        ],
    ],

    // 枚举定义 uuid --管理用户
    'USER_AUTH' => [
        'vm' => 'vmprotect', // 虚拟机保护
        'aws' => 'awsprotect', // 公有云保护
        'cloud' => 'prcloud_protect', // 私有云保护
        'complete' => 'complete_machine', // 整机定时
        'os' => 'osbackup', // 卷
        'fs' => 'fileprotect', // 文件保护
        'nas' => 'nas_protect', // Nas保护
        'obs' => 'obs_protect', // 对象存储保护
        'hadoop' => 'hadoop_protect', // Hadoop保护
        'db' => 'db_protect', // 数据库保护
        'm365' => 'application_protect', // Microsoft365 应用保护
        'k8s' => 'k8s_protect', // k8s 集群
        'cbrbackup' => 'cbrbackup', // 云存储同步
        'complete_cdp' => 'complete_cdp_backup', // 实时容灾保护
        'vol_cdp' => 'vol_cdp_backup', // 实时容灾保护-卷
        'db_protect' => 'dbprotect', // 数据库实时备份
        'machine_copy' => 'machine_copy', // 整机复制
        'vol_cdp_copy' => 'vol_cdp_copy', // 卷复制
        'file_copy_protect' => 'file_copy_protect', // 文件复制
        'dbcdpcopy' => 'dbcdpcopy', // 数据库复制
        'data' => 'data_manager', // 备份数据管理
        'current_job' => 'current_job', // 当前任务
        'history_job' => 'history_job', // 历史任务
        'source' => 'resmanagement', // 存储资源
        'alarm' => 'alarm', // 告警
        'log' => 'log', // 日志
    ],

];