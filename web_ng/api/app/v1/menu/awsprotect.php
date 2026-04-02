<?php

/**
* 这是 公有云保护对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //概览
    [
        'name' => 'aws_overview',
        'path' => './content/aws/awsreport.php',
        'class' => 'viconfont vicon-vm_overview',
        'title' => 'UI_PLATFORM_OVERVIEW',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_aws_overview_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 导出
            [
                'name' => 'p_aws_overview_export',
                'title' => 'UI_PUBLIC_EXPORT',
                'level' => 10,
            ],
            // 打印
            [
                'name' => 'p_aws_overview_print',
                'title' => 'UI_PUBLIC_PRINT',
                'level' => 10,
            ],
        ]
    ],
    //备份
    [
        'name' => 'awsbackup',
        'path' => './content/aws/awsbackup.php',
        'class' => 'viconfont vicon-backup',
        'title' => 'UI_PLATFORM_BACKUP',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_awsbackup_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    //恢复
    [
        'name' => 'awsprotect_recover',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-recover',
        'title' => 'UI_PLATFORM_RECOVER',
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 恢复
            [
                'name' => 'awsrecover',
                'path' => './content/aws/awsrecover.php',
                'class' => 'viconfont vicon-recover',
                'title' => 'UI_PLATFORM_RECOVER',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_awsrecover_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 细粒度恢复
            [
                'name' => 'awsrecovera',
                'path' => './content/aws/awsgrainrecover.php',
                'class' => 'viconfont vicon-awsrecovera',
                'title' => 'UI_PLATFORM_GRAIN_RECOVERY',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_awsrecovera_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
    //备份数据
    [
        'name' => 'awsdata',
        'path' => './content/aws/awsdata.php',
        'class' => 'viconfont vicon-awsdata',
        'title' => 'UI_PLATFORM_BACKUPDATA',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_awsdata_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => 'p_awsdata_detele',
                'class' => '',
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 备注
            [
                'name' => 'p_awsdata_remark',
                'class' => '',
                'title' => 'UI_PUBLIC_REMARK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 设置标记点
            [
                'name' => 'p_awsdata_star',
                'class' => '',
                'title' => 'WEB_PT_OP_GFS_FLAG',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
];
