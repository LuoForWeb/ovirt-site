<?php

/**
* 这是 数据验证, 备份数据CDM对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 数据验证
    [
        'name' => 'add_verification_job',
        'path' => './content/platform/dataverification/add_verification_job.php',
        'class' => 'viconfont vicon-add_verification_job',
        'title' => 'UI_PLATFORM_DATA_VERIFICATION',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_add_verification_job_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 虚拟实验室
    [
        'name' => 'virtual_lab_manager',
        'path' => './content/platform/dataverification/virtual_lab_manager.php',
        'class' => 'viconfont vicon-virtual_lab_manager',
        'title' => 'UI_PLATFORM_VIRTUAL_LAB',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_virtual_lab_manager_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => 'p_virtual_lab_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_virtual_lab_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',

                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_virtual_lab_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 刷新
            [
                'name' => 'p_virtual_lab_refesh',
                'title' => 'UI_PUBLIC_TOOLS_RELOAD',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],

];
