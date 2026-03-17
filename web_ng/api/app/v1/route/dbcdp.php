<?php

 return [
     'dbcdp' => [ // 模块名
         'DbcdpBackUp'  => [ // 类名
             //创建同步任务
             //修改同步任务
             'dbcdp_jobs_sync' => [
                 'post' => 'createBackupJob',
                 'put' => 'editBackupJob',
                 'get' => 'editBackupJobConf',
             ],
             'dbcdp_jobs_networkInfo' => [
                 'get' => 'getHostNetworkInfo',
             ],
             'dbcdp_jobs_scanIp' => [
                'post' => 'scanIp',
             ],
             'dbcdp_jobs_sourcecluster_networkInfo' => [
                 'get' => 'getClusterNetworkInfo',
             ],
             'dbcdp_sync_intask_agent' => [
                 'get' => 'taskExist'
             ],
             'dbcdp_databases' => [
                 'post' => 'loadInstanceDatabase',  // 加载数据库应用（实例）的数据库信息
             ],
             'dbcdp_auth_remain_enough' => [
                 'get' => 'getAuthRemainEnough'     // 获取剩余授权是否足够
             ]
         ],
         'DbcdpJobInfo' => [
             //任务各个阶段的进度信息
             'dbcdp_jobs_progress' => [
                 'get' => 'getJobsProgress',
             ],
             //任务监控数据
             'dbcdp_jobs_monitor_data' => [
                 'get' => 'getJobsMonitorData',
             ],
             //获取指定任务的回切配置信息
             'dbcdp_jobs_failback' => [
                 'get' => 'getFailBackInfo',
                 'post' => 'editFailBackConf'
             ],
             //获取任务详情公共接口没有的信息
             'dbcdp_jobs_basicinfo' => [
                 'get' => 'getBasicInfo'
             ],
             'dbcdp_jobs_machineInfo' => [
                 'get' => 'getMachineInfo'
             ],
             'dbcdp_jobs_history_details' => [
                'get' => 'getHistoryDetails',
             ],
             'dbcdp_jobs_failback_source_cluster' => [
                 'get' => 'getFailbackSourceCluster',
             ],
             'dbcdp_jobs_takeover_info' => [
                 'get' => 'getTakeoverInfo',  // 获取手动接管信息
             ],
             'dbcdp_jobs_flow' => [
                 'get' => 'getDbcdpJobFlow',
             ],
             'dbcdp_import_export_details' => [
                 'get' => 'getImportExportDetails'
             ]
         ],
         //创建恢复任务
         'DbcdpRecover' => [
             'dbcdp_jobs_restore' => [
                 'post' => 'createRestoreJob'
             ],
             //获取可恢复时间范围
             'dbcdp_jobs_restore_data_time_range' => [
                 'get' => 'getRestoreTimeRange'
             ],
             //获取指定时间区间的数据流量信息
             'dbcdp_restore_data_data_flow' => [
                 'get' => 'getAgentBkTimelineData'
             ],
             //获取事务信息
             'dbcdp_restore_data_sync_transaction' => [
                 'get' => 'getTransactionInfo'
             ],
             //获取选定事件详情
             'dbcdp_job_restore_data_sync_events' => [
                 'get' => 'getEventDetail'
             ],
         ],
         //容灾数据-获取恢复数据源
         'DbcdpData' => [
             'dbcdp_restore_data'  => [
                 'get' => 'getRestoreData'
             ],
             'dbcdp_restore_data_agentInfo'  => [
                 'get' => 'getRestoreDataAgentInfo'
             ],
             'dbcdp_restore_data_sourceHostInfo' => [
                 'get' => 'getRestoreDataSourceInfo'
             ],
             'dbcdp_restore_instances' => [
                 'get' => 'getRestoreInstances'
             ]
         ],
         'DbcdpJobController' => [
             'dbcdp_switch_autotakeover' => [
                 // 禁用/启用自动接管
                 'post' => 'switchAutoTakeover'
             ],
             'dbcdp_edit_high_config' => [
                 'put' => 'editHighConfig'
             ]
         ]
     ],
 ];
