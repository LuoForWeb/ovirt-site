<?php

return [
    // 时间策略-备份策略
    'BACKUP_MODE' => [
        'full_backup' => 1,  // 完备
        'incr_backup' => 2,  // 增量备份
        'diff_backup' => 3,  // 差异备份
        'log_backup' => 4, // 日志备份
        'archivelog_backup' => 4, // 归档日志备份
    ],
    // 时间策略-时间类别
    'TIME_TYPE' => [
        'day' => 1,  // 每天
        'week' => 2,  // 每周
        'month' => 3,  // 每月
        'once' => 4,  // 一次性备份
    ],
    // 限速策略-时间类别
    'SPEED_TYPE' => [
        'day' => 1,  // 按天限速
        'week' => 2,  // 按周限速
        'month' => 3,  // 按月限速
        'forever' => 4,  // 永久限速
        'custom' => 5,  // 自定义
    ],
    // 保留策略-保留方式
    'REVERSED_TYPE' => [
        'nums' => 1,  // 按个数
        'days' => 2,  // 按天数
    ],
    // 传输策略-传输模式
    'TRANSPORT_MODE' => [
        'network' => 1,  // 网络传输
    ],
    // 高级配置-postgres-删除归档日志类别
    'DELETE_ARCHIVELOG_TYPE' => [
        'after_archive' => 1,  // 归档备份后删除
        'no_delete' => 2,  // 不删除
        'delete_all' => 3,  // 删除全部
    ],
    // 高级配置-postgres-告警指标
    'WARN_TYPE' => [
        'percent' => 1,  // 百分比
        'size' => 2,  // 大小
    ],
    // 高级配置-postgres-压缩类型
    'COMPRESS_TYPE' => [
        'gzip' => 1,
        'lzma2' => 2,
    ],
    // 高级配置-Oracle-归档日志保留策略
    'ARCHIVELOG_RESERVE_TYPE' => [
        'delete_all' => 1,  // 删除所有已备份的归档日志
        'not_delete' => 2,  // 不删除归档日志
        'reserve_days' => 3,  // 保留x天的归档日志不删除
    ],
    // 配置文件路径格式-Oracle
    'ORACLE_SPFILE_PATH' => '/backup_storage/%s/db/%s/spfile.file',
    'ORACLE_LISTENER_PATH' => '/backup_storage/%s/db/%s/listener.ora',
    'ORACLE_TNSNAMES_PATH' => '/backup_storage/%s/db/%s/tnsnames.ora',
    'ORACLE_SQLNET_PATH' => '/backup_storage/%s/db/%s/sqlnet.ora',
    'ORACLE_RESTORE_ARCHIVELOG_TYPE' => [
        'TIME_RANGE' => 1,  // 按时间范围还原
    ],
    // 恢复方式
    'BD_RECOVERY_TYPE' => [
        'UNKNOWN' => 0,
        'SPECIFIC_TIMEPOINT' => 1,  // 指定时间点恢复
        'LATEST_TIMEPOINT' => 2,  // 恢复最新时间点
    ],
    // SAP HANA-恢复时间
    'SAP_HANA_RECOVERY_TIME' => [
        'NEWEST' => 1,  // 恢复最新点
        'TIMEPOINT' => 2, // 恢复备份点
        'TIME' => 3,  // 指定时间恢复
    ],
    // SQL Server-恢复模式
    'SQL_SERVER_RECOVERY_MODE' => [
        'FULL' => 1,  // 完整
        'BULK_LOGGED' => 2,  // 大容量日志
        'SIMPLE' => 3,  // 日志
    ],
];
