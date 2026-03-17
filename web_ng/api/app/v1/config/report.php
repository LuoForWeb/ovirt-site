<?php
/**
 * 报表
 */

return [
    // 模板类型定义
    'TEMPLATE_TYPE' => [
        'BACKUP_RESOURCE' => 1, // 备份资源
        'PRODUCTION_RESOURCE' => 2, // 生产资源
        'DATA_PROTECT' => 3, // 数据保护
        'TASK' => 4, // 任务
        'USER' => 5, // 用户
    ],

    // 备份资源类型定义
    'BACKUP_RESOURCE_TYPE' => [
        'STORAGE' => 1, // 存储
        'TAPE' => 2, // 磁带
        'NODE' => 3, // 节点
    ],

    // 生产资源类型定义
    'PRODUCTION_RESOURCE_TYPE' => [
        'VM' => 1, // 虚拟化
        'PUBLIC_CLOUD' => 2, // 公有云
        'PRIVATE_CLOUD' => 3, // 私有云
        'CLIENT' => 4, // 客户端
        'NAS' => 5, // NAS
        'OBS' => 6, // 对象存储
        'HADOOP' => 7, // Hadoop
        'M365' => 8, // M365
        'KUBERNETES' => 9, // Kubernetes
    ],

    // 数据保护类型定义
    'DATA_PROTECT_TYPE' => [
        'VM' => 1, // 虚拟化保护
        'PUBLIC_CLOUD' => 2, // 公有云保护
        'PRIVATE_CLOUD' => 3, // 私有云保护
        'SCHEDULED_MACHINE' => 4, // 定时整机保护
        'SCHEDULED_VOLUME' => 5, // 定时卷保护
        'FILE' => 6, // 文件保护
        'NAS' => 7, // NAS保护
        'OBS' => 8, // 对象存储保护
        'HADOOP' => 9, // Hadoop保护
        'DB' => 10, // 数据库保护
        'M365' => 11, // M365保护
        'KUBERNETES' => 12, // Kubernetes保护
        'REAL_TIME_MACHINE' => 13, // 实时整机保护
        'REAL_TIME_VOLUME' => 14, // 实时卷保护
        'COPY_MACHINE' => 15, // 复制整机保护
        'COPY_VOLUME' => 16, // 复制卷保护
        'COPY_DB' => 17, // 复制数据库保护
        'COPY_FILE' => 18, // 复制文件保护
    ],

    // 任务报表模板类型定义
    'TASK_REPORT_TYPE' => [
        'TASK' => 1
    ],

    // 用户报表模板类型定义
    'USER_REPORT_TYPE' => [
        'USER' => 1
    ],

    // 报表数据统计时间间隔范围类型定义
    'TIME_INTERVAL' => [
        'UNKNOWN' => 0,
        'LAST_DAY' => 1, //最近一天
        'LAST_THREE_DAYS' => 2, //最近三天
        'LAST_WEEK' => 3, //最近一周
        'LAST_MONTH' => 4, //最近一月
        'RECENT_FORTEEN_DAYS' => 5, // 最近十四天
    ],
];
