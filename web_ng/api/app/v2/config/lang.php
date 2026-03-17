<?php 
/**
 * 语言包配置文件,每添加一种语言,在此添加一次语言包名
 * 
 */
return [
    
    //语言包名
    'lang' => [
        'zh-cn',    //简体中文
//         'zh-tw',    //繁体中文
         'en-us',    //英文
    ],
    
    //语言描述 
    'langDes' => [
        '简体中文',
//         '繁体中文',
         'English'
    ],

    // 前端标识和对应的语言包前缀
    'webPre' => [
        # 公共
        'common' => 'WEB_PAGE_COMMON_', // 登录、菜单
        # 模块
        'cbr' => 'WEB_PAGE_CBR_', // 云存储同步
        'complete-machine-os' => 'WEB_PAGE_OS_', // 定时整机、卷
        'complete-machine-volcdp' => 'WEB_PAGE_VOL_', // 实时整机、卷
        'db' => 'WEB_PAGE_DB_', // 数据库(定时数据库和复制)
        'dbcdp' => 'WEB_PAGE_DBCDP_', // 永思数据库
        'exchange' => 'WEB_PAGE_EXCHANGE_', // Microsoft365
        'files' => 'WEB_PAGE_FS_', // 文件系列（文件、nas、obs、hadoop）
        'kuberbetes' => 'WEB_PAGE_KBS_', // kuberbetes集群
        'vm' => 'WEB_PAGE_VM_', // 虚拟机、私有云和公有云
        # 平台
        'homepage' => 'WEB_PAGE_HOMEPAGE_', // 首页
        'monitor-center' => 'WEB_PAGE_MONITOR_', // 监控中心
        'recovery' => 'WEB_PAGE_RECOVERY_', // 跨平台、瞬时恢复和迁移以及恢复页面的入口配置
        'visualscreen' => 'WEB_PAGE_VSS_', // 大屏
        # 数据管理
        'archive-center' => 'WEB_PAGE_ARCHIVE_', // 归档
        'copy-center' => 'WEB_PAGE_COPY_', // 副本
        'backup-data' => 'WEB_PAGE_BACK_DATA_', // 备份数据
        'verification' => 'WEB_PAGE_VERIFY_', // 验证

        # 资源
        'backup' => 'WEB_PAGE_BACKUP_', // 备份资源
        'dr-platform' => 'WEB_PAGE_DR_', // 容灾演练平台
        'infrastructure' => 'WEB_PAGE_IFSTC_', // 基础设施
        'storage' => 'WEB_PAGE_STORAGE_', // 存储管理
        # 配置
        'authorization' => 'WEB_PAGE_AUTH_', // 授权
        'domain' => 'WEB_PAGE_DOMAIN_', // 域服务器
        'role' => 'WEB_PAGE_ROLE_', // 角色
        'settings' => 'WEB_PAGE_SET_', // 系统配置
        'tenant' => 'WEB_PAGE_TENANT_', // 租户管理
        'user' => 'WEB_PAGE_USER_', // 用户和用户管理入口
        'user-group' => 'WEB_PAGE_USER_GROUP_', // 用户组
    ],
];
