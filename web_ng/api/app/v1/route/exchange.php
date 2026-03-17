<?php

return [
    'exchange' => [
        //备份页面
        'ExchangeBackUp' => [
            'exchange_jobs_organization' => [
                'get' => 'getOrganizationInfo',
                //修改备份任务的组织树信息
                'put' => 'editOrganizationInfo',
            ],
            'exchange_jobs_organization_user' => [
                'get' => 'getUserInfo',//获取组织下的用户
            ],
            'exchange_jobs_backup' => [
                //创建备份任务
                'post' => 'createBackupJob',
                //修改备份任务
                'put' => 'editBackupJob',
            ],
            'exchange_jobs_backup_info' => [
                //得到备份任务的所有信息（用于修改备份任务）
                'get' => 'getBackupTaskInfo',
            ],
            'exchange_jobs_backup_task_name' => [
                //获取备份任务名
                'get' => 'getBackupTaskName',
            ],
            'exchange_alarm' => [
                //获取告警信息
                'get' => 'getM365Alarm',
            ],
            'exchange_user_num' => [
                //获取组织下本次要备份的用户数
                'get' => 'getM365UserNum',
            ],
        ],
        //恢复页面
        'ExchangeRecover' => [
            'exchange_jobs_restore' => [
                //创建恢复任务
                'post' => 'createRecoverJob',
            ],
            'exchange_jobs_restore_task_name' => [
                //获取恢复任务名
                'get' => 'getRestoreTaskName',
            ],
            'exchange_restore_data' => [
                //获取组织和任务备份数据
                'get' => 'getRestoreData',
            ],
            'exchange_restore_data_restore_points' => [
                //获取某个任务下的恢复点
                'get' => 'getRestorePoints',
            ],
            'exchange_restore_data_restore_points_users' => [
                //获取时间点下的用户列表/顶级目录/子目录/元数据/元数据详细信息
                'get' => 'getRestoreUsers',
            ],
            'exchange_restore_export_zip' => [
                //导出压缩包
                'post' => 'getRestoreZip',
            ],
            'exchange_restore_export_data' => [
                //导出压缩包中的数据
                'get' => 'getRestoreZipData',
            ],
            'exchange_restore_send_email' => [
                //发送邮件（单个/批量发送）
                'post' => 'sendRestoreEmail',
            ],
            'exchange_restore_advanced_search' => [
                //高级搜索
                'get' => 'getSearchInfo',
            ],
            'exchange_restore_normal_search' => [
                //普通全文搜索
                'get' => 'getNormalSearch',
            ],
            'exchange_restore_auth' => [
                //恢复时间点身份验证
                'get' => 'getAuthResult',
            ],
        ],
        //任务操作
        'ExchangeJobController' => [
        ],
        //任务信息
        'ExchangeJobInfo' => [
            'exchange_jobs' => [
                //获取exchange当前任务的某个任务的基本信息
                'get' => 'getBasicInfo',
            ],
            'exchange_jobs_speed' => [
                //获取当前任务的某个任务的任务流量
                'get' => 'getTaskSpeed',
            ],
            'exchange_jobs_detail' => [
                //获取任务详情表格信息
                'get' => 'getExchangeDetail',
            ],
            'exchange_jobs_history' => [
                //获取任务详情历史表格信息
                'get' => 'getExchangeHistory',
            ],
            'exchange_jobs_download' => [
                //下载跳过数据
                'get' => 'downLoadPassData',
            ],
        ],
        //数据管理
        'ExchangeData' => [
            'exchange_manage_data' => [
                //批量/单个删除时间点
                'delete' => 'deleteTimePoint',
                //添加备注
                'put' => 'remarkTimepoint',
            ],
            'exchange_manage_sync' => [
                //云存储上的完备点索引数据同步
                'get' => 'syncPointData',
            ],
            'exchange_star' => [
                //添加星标
                'post' => 'addStar',
                //删除星标
                'delete' => 'deleteStar',
            ],
        ],
    ],

];
