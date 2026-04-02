<?php

/**
 * 这是 文件复制对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 复制
    [
        'name' => 'file_copy',
        'path' => './content/filecopy/file_copy.php',
        'class' => 'viconfont vicon-backup',
        'title' => 'UI_PUBLIC_COPY',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_file_copy_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 回收站
    [
        'name' => 'file_copy_recycle_bin',
        'path' => './content/filecopy/file_copy_recycle_bin.php',
        'class' => 'viconfont vicon-recover',
        'title' => 'UI_FILE_COPY_RECYCLE_BIN',
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_file_copy_recycle_bin_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
];
