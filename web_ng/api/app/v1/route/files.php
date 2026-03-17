<?php

/**
* 文件模块
 */

return [

    'file' => [ // 模块名
        ### 文件备份
        'FileBackUp' => [ // 类名
            'files_agent_backup_tree' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getAgentGroupBackupTree',   // 获取备份任务文件备份客户端树
            ],
            'files_backup_job'  => [
                'post' => 'createBackupJob',    // 创建备份任务
                'put' => 'editBackupJob',    // 修改备份任务
            ],
            'files_agent_tree'  => [
                'post'  =>  'getFileDirTree', // 代理端文件树
            ],
            'files_agent_sontree'  => [
                'post'  =>  'getFileDirSonTree', // 代理端文件子树
            ],
            'files_info'  => [
                'get'  =>  'getBackupTaskAllInfo', // 获取任务基本信息
            ],
            'files_backup_old_tree'  => [
                'post'  =>  'getBackupTreeOldInfo', // 得到修改文件备份任务客户端树
            ],
            'files_backup_job_name'  => [
                'get'  =>  'getFileBackupTaskName', // 得到文件备份任务名
            ],
        ],
        ### 文件恢复
        'FileRecover' => [ // 类名
            'files_create_search' => [ // 路由名
                // 请求方式   => 方法名,
                'post'       => 'createSearchJob',   // 文件恢复搜索---创建搜索消息
            ],
            'files_stop_search'  => [
                'post' => 'stopSearchJob',    // 停止搜索消息
            ],
            'files_get_search'  => [
                'post'  =>  'getRecoSearchInfo', // 获取搜索结果
            ],
            'files_recover_job_name'  => [
                'get'  =>  'getFileRecoverTaskName', // 得到文件恢复任务名
            ],
            'files_get_data_tree'  => [
                'get'  =>  'getFileDataTree', // 得到文件备份时间点树
            ],
            'files_check_encrypt'  => [
                'post'  =>  'checkFSEncryptPass', // 校验文件数据加密密码正确性
            ],
            'files_get_backup_dir'  => [
                'post'  =>  'getBackupFileDir', // 得到备份文件列表
            ],
            'files_async_timepoint'  => [
                'post'  =>  'getSyncFileTimepoint', // 异步获取恢复时间点
            ],
            'files_recover_host_tree'  => [
                'get'  =>  'getRecoverHostTree', // 获取恢复任务文件备份树
            ],
            'files_recover_path_tree' => [
                'get' => 'getRecoverPathTree' // 获取恢复文件客户端下的目录树
            ],
            'files_recover_job'  => [
                'post'  =>  'createRecoverJob', // 创建恢复任务
            ],
        ],
        ### 文件数据管理
        'FileData' => [ // 类名
            'files_timepoint_grid' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getFsTimepointGrid',   // 获取文件时间点列表信息
            ],
            'files_search_timepoint'  => [
                'get' => 'searchFsTimepoint',    // 搜索文件时间点
            ],
            'files_batch_timepoint'  => [
                'delete'  =>  'deleteSelectTimepoint', // 删除批量备份时间点
            ],
            'files_timepoint'  => [
                'delete'  =>  'deleteTimepoint', // 删除备份时间点
            ],
            'files_detail_list' =>  [
                'get' => 'getDetailsFs', // 获取文件主机列表
            ],
            'files_select_fs'   => [
                'post'    =>  'startSelectFs', // 主机列表删除操作
                'delete'    =>  'deleteSelectFs', // 主机列表删除操作
            ],
            'files_download_pass'   =>  [
                'get'   =>  'downLoadPassFile', //  下载跳过文件
            ],
        ],
        ### 文件任务信息
        'FileJobInfo' => [ // 类名
            // 任务详情相关
            'files_basic_info' => [
                'get'   =>  'getFsBasicInfo', // 得到文件模块任务基本信息
            ],
        ],
    ],

];
