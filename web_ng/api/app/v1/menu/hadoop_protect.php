<?php

/**
 * 这是 hadoopb保护子类
 * level 为10 表示最底层的执行方法
 */

return [
    // 备份
    [
        'name' => 'hadoop_backup',
        'path' => './content/hadoop/hadoop_backup.php', //backup
        'class' => 'viconfont vicon-backup',
        'title' => 'UI_PLATFORM_BACKUP',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_hadoop_backup_list',
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
        'name' => 'hadoop_recover',
        'path' => './content/hadoop/hadoop_recovery.php', //recovery
        'class' => 'viconfont vicon-recover',
        'title' => 'UI_PLATFORM_RECOVER',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_hadoop_recover_list',
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
        'name' => 'hadoop_data',
        'path' => './content/hadoop/hadoop_data.php', //recovery
        'class' => 'viconfont vicon-vmdata',
        'title' => 'UI_PLATFORM_BACKUPDATA',
        'level' => 1,
        'child' =>[
            // 查看
            [
                'name' => 'p_hadoop_data_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => 'p_hadoop_data_delete',
                'class' => '',
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 备注
            [
                'name' => 'p_hadoop_data_remark',
                'class' => '',
                'title' => "UI_PUBLIC_REMARK",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 设置标记点
            [
                'name' => 'p_hadoop_data_star',
                'class' => '',
                'title' => "WEB_PT_OP_GFS_FLAG",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
];
