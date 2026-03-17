<?php

/**
 * 操作系统的路由定义
 */

return [

    //操作系统相关
    'os' => [//目录
        'OsBackUp' => [//文件类名
            'os_backup_tree' => [
                //获取备份中心树
                'post' => 'getOsBackupTree',
            ],
            'os_backup_agent_disk_info'=>[
                //获取备份代理卷以及磁盘信息
                'get' => 'getOSBackupAgentInfo',
            ],
            'os_backup_job_name' =>[
                //获取创建备份任务的任务名称
                'get' => 'getOSBackupTaskName',
            ],
            'os_backup_job' =>[
                //创建备份任务
                'post' => 'createOSBackupJob',
                'put' => 'editOSBackupJob',
            ],
        ],   
        'OsData' => [//文件类名
            'os_get_data_tree' =>[
                //获取时间点树
                'get' => 'getTimepointTree',
            ],
            'os_sync_timepoint' =>[
                //异步获取时间点
                'get' => 'getSyncTimepoint',
            ],
            'os_timepoint_grid' =>[
                 //异步获取时间点
                 'get' => 'getOsTimepointGrid',
            ],
            'os_search_timepoint' => [
                //搜索时间点
                'get' => 'searchTimepoint',
            ],
            'os_get_task_info' => [
                //得到修改任务的基本信息
                'get' => 'getBackupTaskAllInfo',
            ],


            
        ],   
        'OsRecover' => [//文件类名
            'os_recover_task_name' => [
                //获取备份中心树
                'get' => 'getOSRecoverTaskName',
            ],
            'os_option_ip' =>[
                //获取需要恢复的目的地主机IP集合
                'get' => 'getOptionIP',
            ],
            'os_link_test' =>[
                //获取恢复目的地中主机信息以及测试连通性
                'post' => 'linkTest',

            ],
            'os_recover_job' => [
                //创建操作系统恢复任务
                 //异步获取时间点
                 'post' => 'createOSRecoverJob',

            ]
            
        ],
        'OsInstantRecover' => [//文件类名
            'os_instantrecover_option_ip' => [
                //获取瞬时恢复目标主机IP集合
                'get' => 'getOptionIP',
            ],
            'os_instantrecover_catch_storage' => [
                //获取瞬时恢复缓存存储列表
                'get' => 'getCatchStorage',
            ],
            'os_instantrecover_task_name' => [
                //获取瞬时恢复任务名
                'get' => 'getTaskName',
            ],
            'os_instantrecover_job' => [
                //创建瞬时恢复任务
                'post' => 'createInstantOSRecoverJob',
            ],
            'os_motion_link_test' => [
                //迁移时连接测试（获取目标主机信息）
                'post' => 'osInstantlinkTest'
            ],
            'os_motion_net_list' => [
                //获取传输网络列表
                'get' => 'getNetList'
            ],
            'os_motion_job' => [
                //创建操作系统迁移任务
                'post' => 'createOSMotionJob',
            ]
        ],
        'OsJobInfo' => [ //文件类名
            'os_jobs_instantrecover_basic_info' => [
                //获取瞬时恢复任务基本信息
                'get' => 'getInstantOSRecoverJobInfo',
            ],
            'os_jobs_motion_basic_info' => [
                //获取迁移任务基本信息
                'get' => 'getOSMotionJobInfo',
            ],
            'os_jobs_detail_list' => [
                //获取主机列表详细信息
                'get' => 'getOSDetailList',
            ],
            'os_jobs_os_backup_info'=>[
                //获取备份任务基本信息
                'get' =>'getOSBasicInfo',
            ],
        ]



    ],







];
