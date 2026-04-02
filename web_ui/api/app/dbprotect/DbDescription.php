<?php
/**
 *虚拟机模块描述定义
 *TODO 每一个定义都关联语言文件
 */

return array(
    //数据库类型
    'DB_TYPE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        2 => "Oracle",
        1 => 'SQL Server',
        3 => "MySQL",
        "DM",
        "PostgreSQL",   //PostgreSQL
        "KingbaseES", //人大金仓
        "UXDB",     //优炫
        "Highgo DB", //翰高,
        "MariaDB",
        "openGauss",
        "Vastbase",
        "AntDB",
        'SAP HANA',
        'TIDB',
        "MongoDB",
        // 'InterSystems Caché',  // InterSystems Cache
        // 'InterSystems IRIS',   // InterSystems IRIS
    ),
    //认证类型
    'AUTH_TYPE_DES' => array(
        '--',
        Xphp::$_lang['WEB_DB_WINDOWS_AUTH'],
        Xphp::$_lang['WEB_DB_SQLSERVER_AUTH']
    ),
    //mysql认证类型
    'MYSQL_AUTH_TYPE_DES' => array(
        '--',
        Xphp::$_lang['WEB_DB_TCP_IP_AUTH'],
        Xphp::$_lang['WEB_DB_SOCK_FILE_AUTH']
    ),
    //oracle认证类型
    //oracle恢复类别【1正常 2空实例】
    'ORACLE_AUTH_TYPE' => array(
        'UNKNOWN' => 0,
        'OS_AUTH' => 1,
        'DATABASE_AUTH' => 2,
    ),
    'ORACLE_AUTH_TYPE_DES' => array(
        '--',
        Xphp::$_lang['WEB_DB_OS_AUTH'],  // 操作系统认证
        Xphp::$_lang['WEB_DB_DATABASE_AUTH'],  // 数据库认证
    ),
    //oracle恢复类别【1正常 2空实例】
    'ORACLE_RECOVERY_TYPE' => array(
        'UNKNOWN' => 0,
        'NORMAL' => 1,
        'EMPTY_INSTANCE' => 2,
    ),
    //数据库恢复类型
    'DB_RECOVERY_LEVEL_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_DB_ORIGINAL_COVERAGE_RECOVERY'],
        Xphp::$_lang['UI_DB_ADD_NEW_DATABASE_RECOVERY'],
        Xphp::$_lang['UI_DB_DM_FILE_DIR_RECOVERY'],
        Xphp::$_lang['UI_DB_DM_REDIRECT_RECOVERY'],
        Xphp::$_lang['UI_DB_EXPORT_REDIRECT_RECOVERY'],
        Xphp::$_lang['UI_DB_PDB_RECOVER'],
    ),
    //数据库任务运行状态
    'DB_TASK_STATUS_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_WAITING'],
        Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'],
        Xphp::$_lang['WEB_PLATFORM_DES_FINISH'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],

    ),
    //SAP HANA恢复时间
    'SAPHANA_RECOVERY_TIME' => [
        'unknown' => 0,
        'newest' => 1,      // 最新点
        'timepoint' => 2,   // 选择备份点
        'time' => 3,        // 选择时间
    ],
);

?>