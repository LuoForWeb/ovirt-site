<?php

/**
* 这是 主机保护对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 备份
    array(
        'name' => 'exchange_backup',
        'path' => './content/exchange/exchange_backup.php',
        'class' => 'viconfont vicon-backup',
        'title' => 'UI_PLATFORM_BACKUP',
        'level' => 1,
        'child' => array(
            // 操作
            array(
                'name' => 'p_exchange_backup_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => array(
                    'get-exchange_jobs_organization',
                    'put-exchange_jobs_organization',
                    'post-exchange_jobs_backup',
                    'put-exchange_jobs_backup',
                    'get-exchange_jobs_backup_info',
                    'get-exchange_jobs_backup_task_name',
                    'get-exchange_alarm'
                ),
                'level' => 10,
            ),
        )
    ),
    // 恢复
    array(
        'name' => 'exchange_recover',
        'path' => './content/exchange/exchange_recover.php',
        'class' => 'viconfont vicon-recover',
        'title' => 'UI_PLATFORM_RECOVER',
        'level' => 1,
        'child' => array(
            // 操作
            array(
                'name' => 'p_exchange_recover_list',
                'title' => 'UI_PUBLIC_OPERATION',
                'function' => array(
                    'post-exchange_jobs_restore',
                    'get-exchange_jobs_restore_task_name',
                    'get-exchange_restore_data',
                    'get-exchange_restore_data_restore_points',
                    'get-exchange_restore_data_restore_points_users',
                    'post-exchange_restore_export_zip',
                    'get-exchange_restore_export_data',
                    'post-exchange_restore_send_email',
                    'get-exchange_restore_advanced_search',
                    'get-exchange_restore_normal_search',
                ),
                'level' => 10,
            ),
        )
    ),
    // 备份数据
    array(
        'name' => 'exchange_data',
        'path' => './content/exchange/exchange_data.php',
        'class' => 'viconfont vicon-vmdata',
        'title' => 'UI_PLATFORM_BACKUPDATA',
        'level' => 1,
        'child' => array(
            // 删除
            array(
                'name' => 'p_exchange_data_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => array(
                    'delete-exchange_manage_data',
                    'get-exchange_manage_sync',

                ),
                'level' => 10,
            ),
            // 备注
            array(
                'name' => 'p_exchange_data_remark',
                'title' => 'UI_PUBLIC_REMARK',
                'function'  => array(
                    'put-exchange_manage_data',
                ),
                'level' => 10,
            ),

        )
    ),
    // office365组织管理
    array(
        'name' => 'exchange_organization',
        'path' => './content/exchange/exchange_organization.php',
        'class' => 'viconfont vicon-zuzhi',
        'title' => 'UI_PLATFORM_OFFICE365_ORGANIZATION',
        'level' => 1,
        'child' => array(
            array(
                'name' => 'p_exchange_organization_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => array(
                    'get-office365_organization',
                    'post-office365_organization',
                    'put-office365_organization',
                    'delete-office365_organization',
                    'get-office365_organization_refresh',
                    'put-office365_organization_refresh',
                    'get-office365_organization_agent',
                ),
                'level' => 10,
            ),
        )
    ),

];
