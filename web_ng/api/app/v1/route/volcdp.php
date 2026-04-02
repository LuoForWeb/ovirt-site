<?php
    return [
        'volcdp' => [   // 模块名
            'VolcdpBackUp'  => [  // 类名'
                'volcdp_agent_tree' => [
                    'get' => 'getVolCdpBackupAgentTree',
                ],
                'volcdp_system_auth' => [
                    'get' => 'getSysAuthFunc'
                ],
                'volcdp_update_agent' => [
                    'put' => 'updateAgentInfo'
                ],
                'volcdp_app_info' => [
                    'get' => 'getVolCdpAppTree'
                ],
                'volcdp_host_network_info' => [
                    'get' => 'getHostNetworkInfo'
                ],
                'volcdp_agent_cache_path' => [
                    'get' => 'getAgentCachePath',
                ],
                'volcdp_takeover_auth_num' => [
                    'get' => 'verifyVolCdpTakeoverAuthNum',
                ],
                'volcdp_standby_host_info' => [
                    'get' => 'getStandbyHostInfo'
                ],
                'volcdp_backup_job' => [
                    'post' => 'createBackupJob'
                ],
                'volcdp_auth_num' => [
                    'get' => 'verifyingVolCdpAuthNum'
                ],
                'volcdp_update_app' => [
                    'put' => 'updateAppInfo'
                ],
                'volcdp_check_ip_exist' => [
                    'get' => 'checkIpExists'
                ],
                'volcdp_host_app_tree' => [
                    'post' => 'getHostConfiguredAppTree'
                ],
                'volcdp_backup_task_name' => [
                    'get' => 'getVolCdpBackupTaskName'
                ],
                'volcdp_host_vol_info' => [
                    'get' => 'getHostVolinfo',
                ]
            ],
            'VolcdpRecover' => [
                'volcdp_client_recovery_backup_set_new_time' => [
                    'get' => 'getClientBackupSetNewTime',
                ],
                'volcdp_recovery_backup_set_vol_info_time' => [
                    'get' => 'getClientBackupSetVolInfo'
                ],
                'volcdp_agent_recovery_backup_set_time_range' => [
                    'get' => 'ClientBackupSetTimeRange'
                ],
                'volcdp_recovery_target_host' => [
                    'get' => 'getRecoveryTargetHost'
                ],
                'volcdp_agent_backup_set_info_by_time_range' => [
                    'get' => 'getAgentBkTimelineData'
                ],
                'volcdp_backup_set_time_point_valid' => [
                    'get' => 'verifyTimepointisValid'
                ],
                'volcdp_recover_job' => [
                    'post' => 'createRecoverJob'
                ],
                'volcdp_recover_task_name' => [
                    'get' => 'getVolCdpRecoverTaskName',
                ],
                'volcdp_takeover_data_source_vol_info' => [
                    'post' => 'getDataSourceVolInfo'
                ],
                'volcdp_datasource_host_info' => [
                    'get' => 'getDataSourceHostInfo'
                ],
                'volcdp_tag_point_info' => [
                    'get' => 'getVolTagPointInfo'
                ],
                'volcdp_agent_event_info' => [
                    'get' => 'getAgentEventInfo'
                ]
            ],
            'VolcdpTakeover' => [
                'volcdp_backup_agent_network_info' => [
                    'get' => 'getBackupAgentNetworkInfo',
                ],
                'volcdp_takeover_target_host' => [
                    'get' => 'getTakeoverTargetHost',
                ],
                'volcdp_agent_ip_online' => [
                    'get' => 'checkIpIsOnline'
                ],
                'volcdp_takeover_client_app_info' => [
                    'get' => 'getClientTakeoverAppInfo'
                ],
                'volcdp_takeover_job' => [
                    'post' => 'createTakeoverJob'
                ],
                'volcdp_takeover_task_name' => [
                    'get' => 'getVolCdpTakeoverTaskName'
                ],
                'volcdp_vol_backup_set' => [
                    'get' => 'getBackupSetGrid'
                ],
                'volcdp_vol_tag_point' => [
                    'get' => 'getBackupTagPointGrid'
                ],
                'volcdp_client_backup_info' => [
                    'get' => 'getClientBackupSetInfo'
                ]
            ],
            'VolcdpData' => [
                'volcdp_data_agent_tree' => [
                    'get' => 'getBackupSetTree',
               ],
                'volcdp_backup_set' => [
                    'delete' => 'deleteSelectBackupSet'
                ],
                'volcdp_remark_timepoint' => [
                    'put' => 'remarkTagPoint'
                ],
                'volcdp_event' => [
                    'delete' => 'deleteSelectEventInfo',
                ],
                'volcdp_tag_point' => [
                    'delete' => 'deleteSelectLablePoint',
                ]
            ],
            'VolcdpJobInfo' => [
                'volcdp_job_network_history_conf' => [
                    'get' => 'getTaskNetworkHistoryConf',
                ],
                'volcdp_job_task_map_info' => [
                    'get' => 'getVolCdpTaskMapInfo'
                ],
                'volcdp_basic_info' => [
                    'get' => 'getVolCdpBasicInfo'
                ],
                'volcdp_takeover_script_conf' => [
                    'get' => 'getTakeoveScriptConf'
                ],
                'volcdp_network_conf_info' => [
                    'get' => 'getTaskNetworkConfInfo'
                ],
                'volcdp_takeover_network' => [
                    'put' => 'modifyTaskTakeOverNetwork'
                ],
                'volcdp_failback_conf' => [
                    'get' => 'getVolCdpTaskHostConf',
                ],
                'volcdp_storage_node_select' => [
                    'get' => 'getStorageNodeSelect'
                ],
                'volcdp_agent_vol_info' => [
                    'get' => 'getAgentVolInfo'
                ],
                'volcdp_agent_disk_info' => [
                    'get' => 'getAgentDiskInfo'
                ],
                'volcdp_takeover_cutback' => [
                    'post' => 'takeOverCutBack'
                ],
                'volcdp_job_failback_info' => [
                    'get' => 'getTaskFailbackInfo'
                ],
                'volcdp_job_tag_point' => [
                    'post' => 'createLablePoint'
                ],
                'volcdp_job_object_info' => [
                    'get' => 'getStartTaskObjectInfo'
                ],
                'volcdp_job_detail_info' => [
                    'get' => 'getDetailsVolInfo'
                ],
                'volcdp_vm_template_conf' => [
                    'get' => 'getTaskVmTemplateConfig'
                ]
            ],
        ]
    ]

?>