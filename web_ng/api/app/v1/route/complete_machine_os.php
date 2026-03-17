<?php

/**
 * 整机
 */

return [

    //整机
    'complete_machine_os' => [//目录
        'MachineOsBackup' => [//文件类名
            'complete_machine_os_backup_tree' => [
                //获取备份中心树
                'get' => 'getMachineOsBackupTree',
            ],
            'complete_machine_os_disk_tree' => [
                //获取定时/实时的磁盘树形结构
                'get' => 'getDiskTree',
            ],
            'complete_machine_os_backup_name' => [
                //获取整机定时任务名
                'get' => 'getMachineOsTaskName',
            ],
            'complete_machine_os_create_backup_job' => [
                //创建备份任务
                'post' => 'createBackupJob',
            ],
            'complete_machine_os_get_job_info' => [
                //获取整机定时备份任务数据,修改使用
                'get' => 'getJobInfo',
            ],
            'complete_machine_os_get_recover_target_info' => [
                //获取整机恢复目标信息
                'post' => 'getRecoverTargetInfo',
            ],
            'complete_machine_os_get_basic_info' => [
                //任务详请使用
                'get' => 'getBasicInfo',
            ],
            'complete_machine_os_get_details_host_list' => [
                //获取任务详请主机列表
                'get' => 'getDetailsHostList',
            ],
            'complete_machine_os_jobs_detail' => [
                //获取任务详请主机列表
                'get' => 'getMachineOsHostList',
            ],
            'complete_machine_os_jobs_speed' => [
                //获取当前任务的某个任务的任务流量
                'get' => 'getTaskSpeed',
            ],
            'complete_machine_os_get_script' => [
                //获取当前详请页面的脚本内容
                'get' => 'getScriptContent',
            ],
            'complete_machine_os_start_host' => [
                //启动各种策略
                'post' => 'startOSJob',
            ],
            'complete_machine_os_get_client_info' => [
                //获取生产主机的信息
                'get' => 'getClientInfo',
            ],



        ],

        'MachineOsRecover' => [//文件类名
            'complete_machine_os_timepoint_tree' => [
                //获取恢复时间点树
                'get' => 'getTimepointTree',
            ],
           'complete_machine_os_sync_timepoint_tree' => [
                //异步获取恢复时间点树
                'get' => 'getSyncTimepoint',
            ],
            'complete_machine_os_target_ip' => [
                //异步获取恢复时间点树
                'get' => 'getOptionIP',
            ],
            'complete_machine_os_create_recover_job' => [
                //创建恢复任务
                'post' => 'createRecoverJob',
            ],
            'complete_machine_os_recover_name' => [
                //获取整机定时任务名
                'get' => 'getMachineOsTaskName',
            ],
            'complete_machine_os_agent_network' =>[
                //获取主机网络信息
                'get' => 'getNetworkofAgent',
            ],

        ],


    ],







];
