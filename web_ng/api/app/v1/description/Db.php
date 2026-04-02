<?php

/**
 *虚拟机模块描述定义
 *TODO 每一个定义都关联语言文件
 */

return [
    //数据库类型
    'DB_TYPE_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        'SQL Server',
        'Oracle',
        'MySQL',
        'DM',
        'PostgreSQL',   //PostgreSQL
        'KingbaseES', //人大金仓
        'UXDB',     //优炫
        'Highgo DB', //翰高,
        'MariaDB',
        'openGauss',
        'Vastbase'
    ),
    //认证类型
    'AUTH_TYPE_DES' => array(
        '--',
        ('WEB_DB_WINDOWS_AUTH'),
        ('WEB_DB_SQLSERVER_AUTH')
    ),
    //mysql认证类型
    'MYSQL_AUTH_TYPE_DES' => array(
        '--',
        ('WEB_DB_TCP_IP_AUTH'),
        ('WEB_DB_SOCK_FILE_AUTH')
    ),
    //数据库恢复类型
    'DB_RECOVERY_LEVEL_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_DB_ORIGINAL_COVERAGE_RECOVERY'),
        ('UI_DB_ADD_NEW_DATABASE_RECOVERY'),
        ('UI_DB_DM_FILE_DIR_RECOVERY'),
        ('UI_DB_DM_REDIRECT_RECOVERY'),
        ('UI_DB_EXPORT_REDIRECT_RECOVERY'),
        ('UI_DB_PDB_RECOVER'),
        ('UI_DB_RESTORE_ARCHIVELOG'),
        ('UI_DB_FULL_RECOVERY'),
        ('UI_DB_INCOMPLETE_RECOVERY'),
        // ('UI_DB_SPECIFY_DATABASE_RECOVERY'),
    ),
    //数据库任务运行状态
    'DB_TASK_STATUS_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_WAITING'),
        ('WEB_PLATFORM_DES_RUNNING'),
        ('WEB_PLATFORM_DES_FINISH'),
        ('WEB_PLATFORM_DES_ERROR'),

    ),
    // 备份点类型
    'DB_BACKUP_MODE_DES' => [
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_FULL'),
        ('UI_BACKUP_INCREMENT'),
        ('UI_BACKUP_DIFFERENCE'),
        ('UI_DB_BACKUP_LOG'),
    ]
];
