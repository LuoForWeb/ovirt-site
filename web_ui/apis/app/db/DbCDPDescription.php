<?php 
/**
 *平台描述定义
 *TODO 每一个定义都关联语言文件 
 */

return array(
    //数据库类型
    'DB_TYPE_DES' => array(
        'SQL Server',
        'DB2',
        'Oracle',
        'Sybase',
        'HotFiles',
        'Mysql',
        'Interbase',
        'Informix',
        '人大金仓',
        '神通',
        '达梦',
        'Exchange',
        'Lotus Domino',
    ),
    //模块类型
    'HOST_TYPE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        '业务主机',
        '备份主机'
    ),
    //主机状态
    'HOST_STATUS_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        '正常',
        '离线',
        '异常'
    ),
    //操作系统服务状态
    'SERVICE_STATUS_DES' => array(
        '',
        '',
        '',
        '',
        '已启动',
        '',
        '',
    ),
    //操作系统服务启动方式
    'SERVICE_START_TYPE_DES' => array(
        '自动',
        '自动',
        '自动',
        '手动',
        '禁用',
    ),
    //实时备份单个数据库状态
    'BACKUP_DB_STATUS' => array(
        '等待中',
        '检查中',
        '准备中',
        '同步中',
        '同步完成',
        '实时监控',
        '停止',
        '等待中',
        '连接中',
    )
)
?>