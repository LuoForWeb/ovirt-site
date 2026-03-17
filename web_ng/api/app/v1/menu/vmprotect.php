<?php

/**
* 这是 虚拟机保护对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //概览
    [
        'name' => 'vm_overview',
        'path' => './content/vm/vmreport.php',
        'class' => 'viconfont vicon-vm_overview',
        'title' => 'UI_PLATFORM_OVERVIEW',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_vm_overview_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 导出
            [
                'name' => 'p_vm_overview_export',
                'title' => 'UI_PUBLIC_EXPORT',
                'level' => 10,
            ],
            // 打印
            [
                'name' => 'p_vm_overview_print',
                'title' => 'UI_PUBLIC_PRINT',
                'level' => 10,
            ],
        ]
    ],
    //备份
    [
        'name' => 'vmbackup',
        'path' => './content/vm/vmbackup.php',
        'class' => 'viconfont vicon-backup',
        'title' => 'UI_PLATFORM_BACKUP',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_vmbackup_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    //云存储同步
    [
        'name' => 'cbrbackup',
        'path' => './content/cbr/cbrbackup.php',
        'class' => 'viconfont vicon-tongbu',
        'title' => 'UI_PLATFORM_HWCBR_STORAGE_SYNC',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_cbrbackup_list',
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
        'name' => 'vmprotect_recover',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-recover',
        'title' => 'UI_PLATFORM_RECOVER',
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 恢复
            [
                'name' => 'vmrecover',
                'path' => './content/vm/vmrecover.php',
                'class' => 'viconfont vicon-recover',
                'title' => 'UI_PLATFORM_RECOVER',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_vmrecover_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 瞬时恢复
            [
                'name' => "vminstantrecover",
                'path' => "./content/vm/vminstantrecover.php",
                'class' => "viconfont vicon-vminstantrecover",
                'title' => 'WEB_VM_INSTANT_RECOVERY',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => "p_vminstantrecover_list",
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
                'name' => 'vmrecovera',
                'path' => './content/vm/vmgrainrecover.php',
                'class' => 'viconfont vicon-vmrecovera',
                'title' => 'UI_PLATFORM_GRAIN_RECOVERY',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_vmrecovera_list',
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
        'name' => 'vmdata',
        'path' => './content/vm/vmdata.php',
        'class' => 'viconfont vicon-vmdata',
        'title' => 'UI_PLATFORM_BACKUPDATA',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_vmdata_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => 'p_vmdata_detele',
                'class' => '',
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 备注
            [
                'name' => 'p_vmdata_remark',
                'class' => '',
                'title' => 'UI_PUBLIC_REMARK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 设置标记点
            [
                'name' => 'p_vmdata_star',
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
