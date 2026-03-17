<?php

/**
* 这是 实时容灾保护对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //  *整机 如果只授权了整机-磁盘，那么语言包就显示成整机
    // 磁盘 ./complete_machine_volcdp/cm_volcdp_backup.php
    [
        'name' => "complete_cdp_backup",
        'path' => "cmVolCdpBackup.html?task_type=backup",
        'class' => "viconfont vicon-overview-complete-machine",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_CDP_COMPLETE_BACKUP",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_vol_cdp_backup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [
                    
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_CDP_OPERATE_DESC',
            ],
        ]
    ],
    // 卷  ./content/volcdp/vol_cdp_backup.php
    [
        'name' => "vol_cdp_backup",
        'path' => "volCdpBackup.html?task_type=backup",
        'class' => "viconfont vicon-overview-volume",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_CDP_REEL_BACKUP",
        'level' => 1,
        'is_operate' => 1,
        'child' => [
            // 操作
            [
                'name' => "p_vol_cdp_backup_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                'function' => [
                    '4-getAuthFunc',
                    '30-getVolCdpBackupAgentTree',
                    '30-createVolCDPBackupJob',
                    '30-verifyingVolCdpAuthNum',
                    '30-updateAgentInfo',
                    '30-updateAppInfo',
                    '30-getHostVolinfo',
                    '30-getVolCdpBackupAgentTree',
                    '30-verifyVolCdpTakeoverAuthNum',
                    '30-getStandbyHostInfo',
                    '30-checkIpExists',
                    '30-getVolCdpBackupTaskName',
                    '30-verifyingVolCdpAuthNum',
                    '30-createBackupJob',
                    '10-getBackupStorageList',
                    '16-getAddStorageNodeSelect',
                    '16-getNodeNetworkList',
                ],
                'level' => 10,
                'is_operate' => 1,
                'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_VOL_CDP_OPERATE_DESC',
            ],
        ]
    ],
    //数据库实时备份,永思
    [
        'name' => "dbprotect",
        'path' => "javascript:;",
        'class' => "viconfont vicon-dbprotect",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DB_CDP",
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 备份
            [
                'name' => "dbcdpbackup",
                'path' => "dbCdp.html",
                'class' => "viconfont vicon-dbcdpbackup",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_BACKUP",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_dbcdpbackup_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                            '19-gettHostInfoWithType',
                            '19-getHostInstance',
                            '19-scanHostDB',
                            '19-getStandbyHostNetworkCard',
                            '19-getHostService',
                            '19-getTakeoverNetworkCardInfo',
                            '19-createBackupJob',
                            '19-getBackupDirInfo',
                            '19-checkBackupHostIsServer',
                            '19-getBackupTaskName',
                            '19-standbyHostDirCheck',
                            '19-takeoverEnvironmentCheck',
                            '10-getBackupStorageList',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_DB_CDP_OPERATE_DESC',
                    ],
                ]
            ],
            // 恢复
            [
                'name' => "dbdataRecovery",
                'path' => "dbCdpRecovery.html",
                'class' => "viconfont vicon-dbdataRecovery",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RECOVER",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_dbdataRecovery_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                            '19-gettHostInfoWithType',
                            '19-gettRecoveryHostInfo',
                            '19-getRecoveryTaskName',
                            '19-recoveryInstanceTestCon',
                            '19-createRecoveryJob',
                            '19-getHostInstance',
                            '19-scanBackupTimepoint',
                            '19-scanBackupDatabase',
                            '19-gettHostInfoWithType',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_DB_CDP_RECOVER_OPERATE_DESC',
                    ],
                ]
            ],
            // 备份数据
            [
                'name' => "dbdata",
                'path' => "dbCdpData.html",
                'class' => "viconfont vicon-db_protect",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_BACKUPDATA",
                'level' => 2,
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_dbdata_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                            '19-getCDPDBTree',
                            '19-getDatabaseSyncTree',
                            '19-scanBackupTimepoint',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_DB_CDP_DATA_OPERATE_DESC',
                    ],
                ]
            ],
            // 主机管理
            [
                'name' => "dbhost",
                'path' => "dbCdpHost.html",
                'class' => "viconfont vicon-dbhost",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DB_HOST_MANAGER",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_dbhost_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            '19-getDbHostInfo',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_dbhost_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                            '19-addHost',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_dbhost_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                            '19-editHost',
                            '19-getEditHostInfo',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_dbhost_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            '19-deleteHost',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 下载日志
                    [
                        'name' => "p_dbhost_download",
                        'title' => "WEB_PAGE_COMMON_MENU_LOG_SYSTEM_LOG_DOWNLOAD_NOW",
                        'function' => [
                            '19-checkHostLog',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
        ]
    ],
];
