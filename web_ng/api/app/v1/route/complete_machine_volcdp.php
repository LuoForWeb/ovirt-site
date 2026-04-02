<?php
    return [
        'complete_machine_volcdp' => [   // 模块名
            'CmBackup' => [  // 类名
                'complete_machine_volcdp_agent_tree' => [    // 路由
                    'get' => 'getCompleteMachineVolcdpAgentTree'
                ],
                'complete_machine_volcdp_auth_info' => [
                    'get' => 'verifyingCompleteMachineVolcdpAuthInfo'
                ],
                'complete_machine_volcdp_emergency_takeover_auth_info' => [
                    'get' => 'verfiyEmergencyTakeoverAuthInfo'
                ],
                'complete_machine_volcdp_update_agent' => [
                    'put' => 'updateAgentInfo',
                ],
                'complete_machine_volcdp_app_info' => [
                    'get' => 'getCompleteMachineVolcdpAppTree',
                ],
                'complete_machine_volcdp_update_app' => [
                    'put' => 'updateAppInfo'
                ],
                'complete_machine_volcdp_networkcard_info' => [
                    'get' => 'getHostNetworkInfo'
                ],
                'complete_machine_volcdp_agent_cache_path' => [
                    'get' => 'getAgentCachePath'
                ],
                'complete_machine_volcdp_node_network_list' => [
                    'get' => 'getNodeNetworkList'
                ],
                'complete_machine_volcdp_check_standby_app' =>[
                    'post' => 'checkStandbyApp'
                ],
                'complete_machine_volcdp_default_task_name' =>[
                    'get' => 'getDefaultTaskName'
                ],
                'complete_machine_volcdp_backup_create_job' => [
                    'post' => 'createBackupJob'
                ],
            ],
            'CmRecover' => [
                'complete_machine_volcdp_create_recover_job' => [
                    //创建恢复任务
                    'post' => 'createRecoverJob',
                ],
                'complete_machine_volcdp_agent_recovery_backup_set_time_range' => [
                    'get' => 'ClientBackupSetTimeRange',
                ],
                'complete_machine_volcdp_client_recovery_backup_set_new_time'  => [
                    'get' => 'getClientBackupSetNewTime'
                ],
                'complete_machine_volcdp_volcdp_agent_backup_set_info_by_time_range' => [
                    'get' => 'getAgentTimelineData',
                ],
                'complete_machine_volcdp_recovery_vol_target_host' => [
                    'get' => 'getRecoveryTargetHost'
                ],
                'complete_machine_volcdp_host_network_info' => [
                    'get' => 'getNetworkCardInfo'
                ],
                'complete_machine_volcdp_recovery_host' => [
                    'get' => 'getRecoveryTargetMachine'
                ],
                'complete_machine_volcdp_hardware_config' => [
                    'get' => 'getHardwareConfig'
                ],
            ],
            //备份集
            'CmBackupSetData' => [
                'complete_machine_volcdp_backup_set_tag_point_info' => [
                    'get' => 'getBackupSetTagPointInfo'
                ],
                'complete_machine_volcdp_backup_set_safe_point_info' => [
                    'get' => 'getBackupSetSafePointInfo'
                ],
                'complete_machine_volcdp_backup_set_event_point_info' => [
                    // 'get' => 'getBackupSetEventInfo'
                ],
                'complete_machine_volcdp_data_agent_tree' => [
                    'get' => 'getBackupSetTree',
                ],
                'complete_machine_volcdp_backup_set_time_range' => [
                    'get' => 'getBackupSetTimeRange',
                ],
                'complete_machine_volcdp_backup_set_timeline' => [
                    'get' => 'getBackupSetTimelineData',
                ],
                'complete_machine_volcdp_backup_set_verify_timepoint_is_valid' => [
                    'get' => 'verifyTimePointIsValid',
                ],
            ],
            //接管
            'CmTakeover' => [
                'complete_machine_volcdp_create_takeover_job'=> [
                    'post' => 'createTakeoverJob'
                ],
                'complete_machine_volcdp_takeover_target_host' => [
                    'get' => 'getTakeoverTargetHost'
                ],
                'complete_machine_volcdp_takeover_client_app_info' => [
                    'get' => 'getClientTakeoverAppInfo'
                ],
                'complete_machine_volcdp_takeover_data_source_vol_info' => [
                    'get' => 'getDataSourceVolInfo'
                ],
                'complete_machine_volcdp_recovery_backup_set_vol_info_time' => [
                    'get' => 'getClientBackupSetVolInfo'
                ],
                'complete_machine_volcdp_agent_network_info' => [
                    'get' => 'loadAgentNetworkInfo'
                ],
                'complete_machine_volcdp_standby_network_info' => [
                    'get' => 'loadStandbyNetworkInfo'
                ]

            ],
            //任务
             //任务信息
            'CmJobInfo' => [
                'complete_machine_volcdp_jobs_speed' => [
                    //获取当前任务的某个任务的任务流量
                    'get' => 'getTaskSpeed',
                ],
                'complete_machine_volcdp_jobs_basic_info' => [
                    //获取任务详请配置信息及任务当前基本信息
                    'get' => 'getBasicInfo',
                ],
                'complete_machine_volcdp_jobs_failback_devices_list' => [
                    //获取当前任务监控的的设备列表
                    'get' => 'getFailbackDevicesList',
                ],
                'complete_machine_volcdp_jobs_monitor_device_info' => [
                    //获取任务监控设备信息
                    'get' => 'getTaskMonitorDeviceInfo',
                ],
                'complete_machine_volcdp_jobs_failback_conf' => [
                    //获取回切配置相关信息
                    'get' => 'getCmCdpTaskHostConf',
                ],
                'complete_machine_volcdp_jobs_time_point_uuid' => [
                    //获取当前接管任务对应的timepointuid,用以加载备份集对应的磁盘信息
                    'get' => 'getTakeoverTaskTimePoint',
                ],
                'complete_machine_volcdp_jobs_submit_failback_task' =>[
                    'post' => 'submitFailbackTask',
                ],
				'complete_machine_volcdp_jobs_agent_status' => [
                    'get' => 'getCmCdpAgentStatus'
                ],
                'complete_machine_volcdp_jobs_monitor_device_details' => [
                    'get' => 'getMonitorDeviceDetails'
                ],
                'complete_machine_volcdp_jobs_object_info' => [
                    'get' => 'getStartTaskObjectInfo'
                ],
                'complete_machine_volcdp_jobs_network_conf' => [
                    'get' => 'getTaskNetworkConfInfo'
                ],
                'complete_machine_volcdp_jobs_modify_network_conf' => [
                    'put' => 'modifyTaskTakeOverNetwork'
                ],
                'complete_machine_volcdp_jobs_takeover_vm_config' => [
                    'get' => 'getTakeoverVmConfig'
                ],
                'complete_machine_volcdp_job_info' => [
                    // 获取任务信息回填修改
                    'get' => 'getJobInfo',
                ],
            ]
        ]
    ];
