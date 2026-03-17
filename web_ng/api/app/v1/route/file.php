<?php

/**
* 文件模块-文件合并
 */

return [

    'fileback' => [ // 模块名
        ### 文件备份
        'FileBackUp' => [ // 类名
            'file_backup_source' => [
                'get'       => 'getBackupSource',   // 获取备份源-客户端、nas设备、Hadoop集群、对象存储
            ],
            'file_dir_tree'  => [
                'post'  =>  'getFileDirTree', // 获取文件目录
            ],
            'file_create_job'  => [
                'post' => 'createBackupJob',    // 创建/修改备份任务
            ],
            'file_info'  => [
                'get'  =>  'getBackupTaskAllInfo', // 获取任务基本信息
            ],
            
        ],
        ### 文件恢复
        'FileRecover' => [
            'file_recovery_tree' => [
                'get' => 'getFileDataTree',//获取文件、nas、 hadoop 、obs恢复源树
            ],
            'file_recovery_timepoint_tree' => [
                'get' => 'getSyncFileTimepoint',//获取文件、nas、 hadoop 、obs时间点树
            ],
            'file_recovery_file_tree' => [
                'get' => 'getBackupFileDir',//获取文件、nas、 hadoop 、obs 文件树
            ],
            'file_recovery_data_search' => [
                'get' => 'searchTimepoint',//搜索文件、nas、 hadoop 、obs 时间点树
            ],
            'file_recovery_create_search' => [
                'post' => 'createSearchJob',//创建搜索任务
            ],
            'file_recovery_get_search' => [
                'post' => 'getRecoSearchInfo' //获取恢复搜索结果       
            ],
            'file_recovery_stop_search' => [
                'post' => 'stopSearchJob' //停止搜索       
            ],
            'file_recovery_host_tree' => [
                'get' => 'getRecoveryTarget' //获取恢复目标树
            ],
            'file_recovery_path_tree' => [
                'get' => 'getRecoverPathTree' //获取恢复目录树
            ],
            'file_recovery_taskname' => [
                'get' => 'getFileRecoverTaskName' //获取恢复任务名
            ],
            'file_recovery_job' => [
                'post' => 'createRecoverJob' //创建恢复任务
            ]
        ],
         ### 文件任务启动、停止等
         'FileJobController' => [ // 类名
            'file_operate_start'   => [
                'post'    =>  'startBackupJob', // 操作按钮-启动单个对象备份任务
            ],
        ],
        ### 文件任务信息
        'FileJobInfo' => [ // 类名
            'file_download_pass'   =>  [
                'get'   =>  'downLoadPassFile', //  下载跳过文件
            ],
            'file_jobs_basic_info'   =>  [
                'get'   =>  'getBasicInfo', //  得到文件模块任务基本信息
            ],
            'file_jobs_object_list'   =>  [
                'get'   =>  'getObjectList', //  获取对象列表-客户端列表、nas设备、hadoop集群、对象存储
            ],
        ],
    ],

];
