<?php

/**
* 这是 备份数据管理对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //数据验证, 备份数据CDM
    array(
        'name' => 'data_verification',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-data_verification',
        'title' => 'UI_PLATFORM_VERIFICATION',
        'level' => 1,
        'showChild' => true,
        'child' => array(
            // 数据验证
            array(
                'name' => 'add_verification_job',
                'path' => './content/platform/dataverification/add_verification_job.php',
                'class' => 'viconfont vicon-add_verification_job',
                'title' => 'UI_PLATFORM_DATA_VERIFICATION',
                'level' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => 'p_add_verification_job_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => array(
                        ),
                        'level' => 10,
                    ),
                )
            ),
            // 虚拟实验室
            array(
                'name' => 'virtual_lab_manager',
                'path' => './content/platform/dataverification/virtual_lab_manager.php',
                'class' => 'viconfont vicon-virtual_lab_manager',
                'title' => 'UI_PLATFORM_VIRTUAL_LAB',
                'level' => 1,
                'child' => array(
                    // 查看
                    array(
                        'name' => 'p_virtual_lab_manager_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => array(
                        ),
                        'level' => 10,
                    ),
                    // 新建
                    array(
                        'name' => 'p_virtual_lab_add',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => array(
                        ),
                        'level' => 10,
                    ),
                    // 修改
                    array(
                        'name' => 'p_virtual_lab_edit',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => array(
                        ),
                        'level' => 10,
                    ),
                    // 删除
                    array(
                        'name' => "p_virtual_lab_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => array(
                        ),
                        'level' => 10,
                    ),
                    // 刷新
                    array(
                        'name' => 'p_virtual_lab_refesh',
                        'title' => 'UI_PUBLIC_TOOLS_RELOAD',
                        'function' => array(
                        ),
                        'level' => 10,
                    ),
                )
            ),

        )
    ),
    //副本容灾
    array(
        'name' => 'copy_protect',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-vmdatacopy',
        'title' => 'UI_PLATFORM_COPY_PROTECT',
        'level' => 1,
        'showChild' => true,
        'child' => array(
            array(
                'name' => 'copy',
                'path' => './content/copy/copy.php',
                'class' => 'viconfont vicon-vmcopy',
                'title' => 'UI_PLATFORM_VM_COPY_BACKUP',
                'level' => 2,
                'child' => array()
            ),
            array(
                'name' => 'copy_back',
                'path' => './content/copy/copyback.php',
                'class' => 'viconfont vicon-vmcopyback',
                'title' => 'UI_PLATFORM_VM_COPY_RECOVERY',
                'level' => 2,
                'child' => array()
            ),
            array(
                'name' => 'copy_data',
                'path' => './content/copy/copydata.php',
                'class' => 'viconfont vicon-vmdata',
                'title' => 'UI_COPY_DATA',
                'level' => 2,
                'child' => array(
                    array(
                        'name' => "p_vmcopydata_delete",
                        'class' => '',
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            )
        )
    ),

    // 数据归档
    array(
        'name' => 'archive_new',
        'path' => 'javascript:;',
        'class' => 'viconfont vicon-data_archive',
        'title' => 'UI_PLATFORM_ARCHIVE_PROTECT',
        'level' => 1,
        'showChild' => true,
        'child' => array(
            // 备份点归档
            array(
                'name' => 'archive_add_new',
                'path' => './content/archive/addarchive.php',
                'class' => 'viconfont vicon-archive_add',
                'title' => 'UI_ARCHIVE_DATA_ADD',
                'level' => 2,
                'child' => array(
                )
            ),
            // 归档数据回传
            array(
                'name' => 'archive_back_new',
                'path' => './content/archive/archiveback.php',
                'class' => 'viconfont vicon-archive_back',
                'title' => 'UI_ARCHIVE_DATA_BACK',
                'level' => 2,
                'child' => array(
                )
            ),
            // 归档数据
            array(
                'name' => 'archive_data_new',
                'path' => './content/archive/archivedata.php',
                'class' => 'viconfont vicon-vmdata',
                'title' => 'UI_ARCHIVE_DATA',
                'level' => 2,
                'child' => array(
                )
            )
        )
    ),
    // 备份数据管理
    [
        "name" => "backup_data",
        'path' => "./content/backupDataManage/backupDataManage.php",
        'class' => "viconfont vicon-shuju",
        'title' => "UI_PLATFORM_BACKUP_DATA",
        'level' => 1,
        'child' => [
            // 操作
            [
                'name' => 'p_backup_data_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => [
                    'get-backup_data_jobs_list', // 获取任务列表
                    'get-backup_data_items_list', // 获取对象列表
                    'get-backup_data_vol_list', // 获取整机实时标签点
                    'delete-backup_data_vol_list', // 删除整机实时标签点
                    'get-backup_data_vol_remark', // 整机标签点备注
                    'get-backup_data_remote_list', // 获取异地副本数据列表
                    'get-backup_data_remote_tree', // 获取异地副本数据树
                    'post-backup_data_points', // 获取备份点
                    'delete-backup_data_points', // 删除备份点
                    'get-backup_data_points_path', // 获取时间点备份的虚拟机路径、文件列表等
                    'get-backup_data_points_remark', // 设置备份点备注
                    'get-backup_data_points_gfs_mark', // 设置备份点gfs标记
                    'get-backup_data_points_mark', // 设置永久标记
                    'delete-backup_data_points_mark', // 取消永久标记
                    'get-backup_data_points_depend', // 获取完备点的依赖非完备点
                    'get-backup_data_points_worm', // 配置worm时间
                ],
                'level' => 10,
            ],
        ]
    ],
];
