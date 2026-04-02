<?php

/**
* 数据库相关的配置信息
 */

return [
    /***↓数据库CDP配置***/

    //数据库类型
    'DB_CDP_VENDOR' => array(
        'SQLSERVER' => 0,
        'DB2' => 1,
        'ORACLE' => 2,
        'SYBASE' => 3,
        'HOTFILES' => 4,
        'MYSQL' => 5,
        'INTERBASE' => 6,
        'INFORMIX' => 7,
        'RENDAJINCANG' => 8,
        'SHENTONG' => 9,
        'DAMENG' => 10,
        'EXCHANGE' => 11,
        'LOTUSDOMINO' => 12,
    ),
    //主机类型
    'DB_CDP_HOST_TYPE' => array(
        'product' => 1,         //业务主机
        'standby' => 2          //备份主机
    ),

    //操作系统服务状态
    'DB_CDP_SERVICE_STATUS' => array(
        'STOPPED',          //停止
        'START_PENDING',    //启动中
        'STOP_PENDING',     //停止中
        'RUNNING',          //运行中
        'CONTINUE_PENDING', //继续中
        'PAUSE_PENDING',    //暂停中
        'PAUSED',           //暂停
    ),

    //操作系统类型
    'DB_CDP_OS_TYPE' => array(
        'UNKNOWN' => 0,
        'WINDOWS' => 1,
        'LINUX' => 2,
        'UNIX' => 3,
        'AIX' => 4,
        'HPUX' => 5,
        'SUNOS' => 6,
    ),

    //数据库备份类型
    'DB_CDP_BACKUP_TYPE' => array(
        'REALTIME_BACKUP' => 0,         //实时备份
        'TAKEOVER' => 2,                //业务接管
        'REALTIME_AND_TAKEOVER' => 3    //实时备份+业务接管
    ),

    //备份单个数据库状态
    'DB_CDP_BACKUP_DB_STATUS' => array(
        'WAITING' => 0,                 //等待
        'CHECKING' => 1,                //检查
        'PREPARATION' => 2,             //准备
        'SYNCING' => 3,                 //同步
        'SYNCOVER' => 4,                //同步完成
        'MONITOR' => 5,                 //监控
        'STOPPED' => 6,                 //停止
        'WAITNEXT' => 7,                //等待下次连接中
        'CONNING' => 8,                 //下次备份连接中
    ),

    //恢复workid,判断恢复状态 01342
    'DB_CDP_RECOVERY_WORDID' => array(
        'NOTSTARTED' => 0,              //未开始
        'CHECKING' => 1,                //检查中
        'FINISHED' => 2,                //完成
        'TRANSMITTING' => 3,            //传输
        'UNWRITE' => 4,                 //写入
    ),

    //接管状态 启动状态
    'DB_CDP_TAKEOVER_START_STATUS' => array(
        //             'UNWORK' => 0,                  //没有开始工作
        'S_PREPARAM' => 1,                //正在准备参数
        'S_PREPARAM_AUTO' => 2,           //正在准备自动接管参数
        'S_WAITCHKIF' => 3,               //正在等待和检查自动接管条件
        'S_STARTING' => 4,                //正在启动
        'S_STOPHOSTNAME' => 5,            //停止接管主机名
        'S_STOPHOSTIP' => 6,             //停止接管主站IP
        'S_STOPCFGTASK' => 7,            //停止接管配置的任务(服务)
        'S_HOSTNAME' => 8,                //启动接管主机名
        'S_HOSTIP' => 9,                  //启动接管主站IP
        'S_HNAMEBINDIP' => 10,             //启动接管主机名BINDIP
        'S_CFGTASK' => 11,                 //启动接管配置的任务(服务)
        'S_TASKOK' => 12,                 //启动接管流程完成
        'S_TASKEND' => 13,                //停止接管流程完成
    ),

    //接管状态 停止状态
    'DB_CDP_TAKEOVER_STOP_STATUS' => array(
        //             'UNWORK' => 0,                  //没有开始工作
        'P_PREPARAM' => 101,                //正在准备参数
        'P_PREPARAM_AUTO' => 102,           //正在准备自动接管参数
        'P_WAITCHKIF' => 103,               //正在等待和检查自动接管条件
        'P_STARTING' => 104,                //正在启动
        'P_STOPHOSTNAME' => 105,            //停止接管主机名
        'P_STOPHOSTIP' => 106,             //停止接管主站IP
        'P_STOPCFGTASK' => 107,            //停止接管配置的任务(服务)
        'P_HOSTNAME' => 108,                //启动接管主机名
        'P_HOSTIP' => 109,                  //启动接管主站IP
        'P_HNAMEBINDIP' => 110,             //启动接管主机名BINDIP
        'P_CFGTASK' => 111,                 //启动接管配置的任务(服务)
        'P_TASKOK' => 112,                 //启动接管流程完成
        'P_TASKEND' => 113,                //停止接管流程完成
    ),

    //主机监控睡眠时间
    'DB_CDP_HOST_MONITOR_SLEEP' => 10,
    //任务监控睡眠时间
    'DB_CDP_JOB_MONITOR_SLEEP' => 1,
    //数据库CDP授权ip地址 使用浏览器IP
    'DB_CDP_LICENSE_IP' => $_SERVER['SERVER_ADDR'],

    //日志大小管理方式:日志数据记录模式
    'DB_CDP_LOG_MODE' => array(
        'STEP' => 0,        //按步数
        'DISKSIZE' => 1,    //按磁盘空间大小
        'AUTODISK' => 2,    //自动按分配的磁盘自适应,磁盘有多大,就用多大
        'HOUR' => 3,        //按时间,单位小时
        'DISKHOUR' => 4,    //按指定小时和空间
    ),


    /***↑数据库CDP配置***/

    //数据库类型
    'DB_TYPE' => array(
        'UNKNOWN' => 0,
        'ORACLE' => 2,
        'SQLSERVER' => 1,
        'MYSQL' => 3,
        'DM' => 4,
        'POSTGRE' => 5,
        'KINGBASE' => 6,
        'UXDB' => 7,
        'HIGHGO' => 8,
        'MARIA' => 9,
        'OPENGAUSS' => 10,
        'VASTBASE' => 11,
        'ANTDB' => 12,
        // 'CACHE' => 15,
        // 'IRIS' => 16,
        'SAPHANA' => 13,
        'TIDB' => 14,
        'MONGODB' => 15,
    ),
    //数据库类型描述
    'DB_TYPE_DES' => array(
        'Unknown',
        2 => 'Oracle',
        1 => 'SQL Server',
        3 => 'MySQL',
        'DM',
        'PostgreSQL',   //PostgreSQL
        'KingbaseES', //人大金仓
        'UXDB',     //优炫
        'Highgo DB', //翰高
        'MariaDB', //
        'openGauss',
        'Vastbase',
        'AntDB',
        // 'InterSystems Caché',
        // 'InterSystems IRIS',
        'SAP HANA',
        'TiDB',
        'MongoDB',
        1000 => 'Exchange Server',
    ),
    'DB_TYPE_INDEX' => array(
        2 => 2,  //oracle
        1 => 1,  //sqlserver
        3 => 3,  //mysql
        4,   //DM
        5,  //PostgreSQL
        6,  //Kingbase
        7,  //UXDB
        8,   //Highgo DB
        9,  //MariaDB
        10, //openGauss
        11, // Vastbase
        12, // AntDB
        // 15, // Caché
        // 16, // IRIS
        13,
        14, // TiDB
        15, // MongoDB
    ),

    //数据库恢复方式
    'DB_RECOVERY_TYPE' => array(
        'UNKNOWN' => 0,
        'COVER' => 1,   //原机覆盖恢复
        "CREATE" => 2,  //新建恢复
        'SPECIFY_FOLDER' => 3, //指定文件夹恢复
        'REDIRECT_DIR' => 4,   //重定向目录恢复
        'EXPORT' => 5,  //导出恢复
        'PDB' => 6,  //PDB恢复
        'RESTORE_ARCHIVELOG' => 7,  //还原归档日志
        'FULL' => 8,  // 完全恢复
        'INCOMPLETE' => 9,  // 不完全恢复
    ),

    //数据库任务运行中状态
    'DB_TASK_STATUS' => array(
        'UNKNOWN' => 0, //未知
        'WAITING' => 1, //等待
        'RUNNING' => 2, //运行
        'FINISH' => 3, //完成
        'ERROR' => 4, //错误
    ),

    //数据库类型
    'DBTYPE' => array(
        'UNKNOWN' => 0,         // sub module type unknown
        'MSSQL' => 1,           // ms sql server
        'ORACLE' => 2,          // oracle
        'MYSQL' => 3,           // mysql
        'SYBASE' => 4,          // sybase
    ),

    //mssql类型
    'MSSQLTYPE' => array(
        'UNKNOWN' => 0,         // version type unknown
        '2000' => 1,            // sql server 2000
        '2005' => 2,            // sql server 2005
        '2008' => 3,            // sql server 2008
        '2008_R2' => 4,         // sql server 2008 R2
        '2012' => 5,            // sql server 2012
        '2014' => 6,            // sql server 2014
        '2016' => 7,            // sql server 2016
    ),
    //oracle类型
    'ORACLETYPE' => array(
        'UNKNOWN' => 0,         // oralce version type unknown
        '9I' => 1,              // oracle 9i
        '10G' => 2,             // oracle 10g
        '10G_R2' => 3,          // oracle 10g R2
        '11G' => 4,             // oracle 11g
        '11G_R2' => 5,          // oracle 11g R2
        '12C' => 6,             // oracle 12c
    ),

    //消息定义
    'DB_CDP_SYSCODE' => array(
        'producthost' => 257,   //主机消息
        'standbyhost' => 258,   //备机消息
        'takeover' => 1025,     //接管消息
        'fileproduct' => 513,   //文件主站
        'filestandby' => 514,   //文件从站
    ),

    //主机在线状态
    'DB_CDP_HOST_STATUS' => array(
        'ONLINE' => 1,      //在线
        'OFFLINE' => 2      //离线
    ),

    //备份/恢复 /接管系统启动状态
    'DB_CDP_HOST_START_STATUS' => array(
        'PREPARE' => 0,     //就绪
        'STARTED' => 1,     //启动
        'STARTING' => 2,    //启动中(停止无)
        'ERROR' => 3,       //启动错误(停止无)
        'WAITING' => 255,   //系统等待初始化
    ),
];
