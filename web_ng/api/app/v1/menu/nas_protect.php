<?php

/**
 * 这是 NAS保护对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 备份
    [
        'name' => 'nasbackup',
        'path' => './content/nas/nasbackup.php',
        'class' => 'viconfont vicon-backup',
        'title' => 'UI_PLATFORM_BACKUP',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_nasbackup_list',
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
        'name' => 'nasrecover',
        'path' => './content/nas/nasrecover.php',
        'class' => 'viconfont vicon-recover',
        'title' => 'UI_PLATFORM_RECOVER',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_nasrecover_list',
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
        'name' => 'nasdata',
        'path' => './content/nas/nasdata.php',
        'class' => 'viconfont vicon-vmdata',
        'title' => 'UI_PLATFORM_BACKUPDATA',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_nasdata_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => 'p_nasdata_detele',
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 备注
            [
                'name' => 'p_nasdata_remark',
                'title' => 'UI_PUBLIC_REMARK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 设置标记点
            [
                'name' => 'p_nasdata_star',
                'title' => 'WEB_PT_OP_GFS_FLAG',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
];
