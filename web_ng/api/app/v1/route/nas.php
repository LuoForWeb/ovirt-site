<?php

/**
 * nas 路由
 */

return [

    ### 设备
    'resources' => [ // 模块名
        'Nas' => [ // 类名
            'nas' => [ // 路由名
                // 请求方式   => 方法名,
                'get' => 'getNasInfo',   // 获取nas设备列表
                'post' => 'addNasDevice',   // 添加nas设备
                'put' => 'editNasDevice',   // 修改nas设备
                'delete' => 'delNasDevice',   // 删除nas设备
            ],
            'nas_check' => [
                'post' => 'checkEditNasParams', // 检测是否修改了除了别名之外的信息
            ],
            'nas_auth' => [
                'post' => 'nasLisence', // 添加/取消授权
            ],
            'nas_opreate' => [
                'post' => 'opreateNas', // 挂载/解挂操作
            ],
        ],
    ],
    ### 模块
    'nas' => [
        ### nas备份
        'NasBackUp' => [
            'nas_mount_node' => [
                'get' => 'getAllMountNode',   // 得到所有挂载成功节点
            ],
            'nas_backup_task_name' => [
                'get' => 'getNasBackupTaskName',   // 得到nas备份任务名
            ],
            'nas_backup_tree' => [
                'get' => 'getNasBackupTree',    // 获取nas设备树
            ],
            'nas_backup_son_tree' => [
                'get' => 'getNasSonTree', // 获取取nas备份文件子树
            ],
            'nas_backup_info' => [
                'get' => 'getBackupTaskAllInfo', // 得到备份任务的所有信息，用于修改任务
            ],
            'nas_backup_old_tree' => [
                'get' => 'getBackupTreeOldInfo', // 得到修改nas备份任务nas设备树
            ],
            'nas_backup_job' => [
                'post' => 'createBackupJob',    // 创建备份任务
                'put' => 'editBackupJob',    // 修改备份任务
            ],
        ],
        ### nas恢复
        'NasRecover' => [ // 类名
            'nas_data_tree' => [
                'get' => 'getNasDataTree', // 得到nas恢复文件树
            ],
            'nas_recovery_dir' => [
                'get' => 'getRecoveryNasDir', // 得到时间点下的恢复文件目录树
            ],
            'nas_async_point' => [
                'get' => 'getSyncNasTimepoint', // 异步获取nas时间点
            ],
            'nas_recover_job' => [
                'post' => 'createRecoverJob', // 创建恢复任务
            ],
            'nas_recover_task_name' => [
                'post' => 'getNasRecoverTaskName', // 得到nas恢复任务名
            ],
            'nas_recover_path_tree' => [
                'get' => 'getRecoverPathTree', // 得到恢复文件目录树
            ],
            'nas_create_search_job' => [
                'post' => 'createSearchJob' // 创建搜索消息
            ],
            'nas_search_info' => [
                'get' => 'getSearchInfo' // 获取搜索结果
            ],
            'nas_stop_search' => [
                'post' => 'stopSearchJob' // 停止搜索消息
            ],
        ],
        ### nas数据管理
        'NasData' => [ // 类名
            'nas_timepoint_grid' => [
                'get' => 'getNasTimepointGrid',   // 获取nas时间点列表信息
            ],
            'nas_search_point' => [
                'get' => 'searchNasTimepoint',    // 搜索nas时间点
            ],
            'nas_batch_point' => [
                'delete' => 'deleteSelectTimepoint', // 批量删除备份时间点
            ],
            'nas_point' => [
                'delete' => 'deleteTimepoint', // 删除备份时间点
            ],
            // 任务详情相关
            'nas_basic_info' => [
                'get' => 'getNasBasicInfo', // 得到NAS模块任务基本信息
            ],
            'nas_detail_list' => [
                'get' => 'getDetailsNas', // 获取nas设备列表详情表格
            ],
            'nas_detail_history_list' => [
                'get' => 'getDetailsHistoryNas', // nas详情历史任务表格
            ],
        ],
        ### NAS任务详情
        'NasJobInfo' => [
            'nas_jobs_basic' => [
                //获取nas当前任务的某个任务的基本信息
                'get' => 'getBasicInfo',
            ],
            'nas_jobs_speed' => [
                //获取当前任务的某个任务的任务流量
                'get' => 'getTaskSpeed',
            ],
            'nas_jobs_detail' => [
                //获取任务详情表格信息
                'get' => 'getTaskDetail',
            ],
            'nas_jobs_history' => [
                //获取任务详情历史表格信息
                'get' => 'getNasHistory',
            ],
            'nas_jobs_start' => [
                // 启动任务
                'post' => 'startNasBackupJob',
            ],
            'nas_download_pass' => [
                'get' => 'downLoadPassFile', //  下载跳过文件
            ]
        ]
    ],

];