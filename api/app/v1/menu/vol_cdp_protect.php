<?php

/**
* 这是 实时容灾保护对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 备份
    [
        'name' => 'vol_cdp_backup',
        'path' => './content/volcdp/vol_cdp_backup.php', //backup
        'class' => 'viconfont vicon-backup',
        'title' => 'UI_PLATFORM_BACKUP',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_vol_cdp_backup_list',
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
        'name' => 'vol_cdp_recovery',
        'path' => './content/volcdp/vol_cdp_recover.php', //recovery
        'class' => 'viconfont vicon-recover',
        'title' => 'UI_PLATFORM_RECOVER',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_vol_cdp_recovery_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 接管&验证
    [
        'name' => 'vol_cdp_takeover',
        'path' => './content/volcdp/vol_cdp_takeover.php', //takeover
        'class' => 'viconfont vicon-vol_cdp_takeover',
        'title' => 'UI_PLATFORM_VOL_CDP_TAKEOVER_TITLE',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_vol_cdp_takeover_list',
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
        'name' => 'vol_cdp_backup_set',
        'path' => './content/volcdp/vol_cdp_backup_set.php', //backup set
        'class' => 'viconfont vicon-vmdata',
        'title' => 'UI_PLATFORM_VOL_CDP_BACKUPSET',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_vol_cdp_backup_set_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => 'p_volcdpdata_detele',
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 备注
            [
                'name' => 'p_volcdpdata_remark',
                'title' => 'UI_PUBLIC_REMARK',
                'function'  => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 设置标记点
            [
                'name' => 'p_volcdpdata_star',
                'title' => 'WEB_PT_OP_GFS_FLAG',
                'function'  => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ]
];
