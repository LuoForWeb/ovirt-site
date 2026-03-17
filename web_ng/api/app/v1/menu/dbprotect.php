<?php

/**
* 这是 数据库实时备份,永思对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 备份
    [
        'name' => 'dbcdpbackup',
        'path' => './content/db/dbcdp.php',
        'class' => 'viconfont vicon-dbcdpbackup',
        'title' => 'UI_PLATFORM_BACKUP',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_dbcdpbackup_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 恢复
    [
        'name' => 'dbdataRecovery',
        'path' => './content/db/dbrecovery.php',
        'class' => 'viconfont vicon-dbdataRecovery',
        'title' => 'UI_PLATFORM_RECOVER',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_dbdataRecovery_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 备份数据
    [
        'name' => 'dbdata',
        'path' => './content/db/dbdata.php',
        'class' => 'viconfont vicon-db_protect',
        'title' => 'UI_PLATFORM_BACKUPDATA',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_dbdata_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 主机管理
    [
        'name' => 'dbhost',
        'path' => './content/db/dbhost.php',
        'class' => 'viconfont vicon-dbhost',
        'title' => 'UI_PLATFORM_DB_HOST_MANAGER',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_dbhost_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => 'p_dbhost_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_dbhost_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_dbhost_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 下载日志
            [
                'name' => 'p_dbhost_download',
                'title' => 'UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
];
