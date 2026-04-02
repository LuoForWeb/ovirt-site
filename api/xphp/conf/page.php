<?php
/**
 * 所有页面层级关系,用于顶部和左边树形展示
 * level 为10 表示最底层的执行方法
 * function 存放权限数组 如 0-getConfig 表示 0是请求的m，getConfig是请求的f， 0-* 其中*表示该m下的所有方法都可以访问
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */
return array(
    //系统首页
    array(
        'name' => "homepage",
        'path' => "javascript:;",
        'class' => "viconfont vicon-homepage",
        'title' => "UI_PLATFORM_HOMEPAGE",
        'level' => 0,
        'child' => array(
            // 查看
            array(
                'name' => "p_homepage",
                'title' => "UI_PUBLIC_LOOK",
                'function' => array(
                    '37-initDataFunc',
                ),
                'level' => 10,
            ),
            // 大屏权限
            array(
                'name' => "p_visual_screen",
                'title' => "UI_VISUAL_SCREEN",
                'function' => array(
                ),
                'level' => 10,
            ),
        )
    ),

    //监控中心
    array(
        'name' => "monitor",
        'path' => "javascript:;",
        'class' => "viconfont vicon-monitor",
        'title' => "UI_PLATFORM_MONITOR_CENTER",
        'level' => 0,
        'child' => array(
            //任务
            array(
                'name' => "task",
                'path' => "./content/platform/jobs/jobs.php",
                'class' => "viconfont vicon-task",
                'title' => "UI_PLATFORM_JOB_MONITOR",
                'level' => 1,
                'child' => array(
                    // 当前任务
                    array(
                        'name' => "current_job",
                        'path' => "./content/platform/jobs/current_job.php",
                        'class' => "viconfont vicon-pt_job_current_task",
                        'title' => "UI_PLATFORM_CURRENT_JOB",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_current_job_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '16-getNodeList',
                                    '15-getAllHypervisorType',
                                    '5-getCurrentJobs',
                                    '4-getAuthFunc',
                                    'get-jobs',
                                    'get-jobs_info',
                                    'get-jobs_flow',
                                    'get-logs_jobs_running_logs',
                                ),
                                'level' => 10,
                            ),
                            // 管理
                            array(
                                'name' => "p_current_job_manager",
                                'title' => "UI_PLATFORM_MANAGEMENT",
                                'function' => array(
                                    '5-stopVolCdpTakeover',
                                    '5-startVolCdpTaskCatback',
                                    '5-getCopyArchiveJobs',
                                    '5-getCurrentCDPJobs',
                                    '5-getCurrentDbJobs',
                                    '5-getCurrentFsJobs',
                                    '5-getCurrentNasJobs',
                                    '5-getCurrentOsJobs',
                                    '5-getCurrentVmJobs',
                                    '5-getTaskFailbackInfo',
                                    '5-getCurrentVolcdpJobs',
                                    '5-startJob',
                                    '5-pauseJob',
                                    '5-stopJob',
                                    '5-editJob',
                                    '5-deleteJob',
                                    '5-startDiff',
                                    '5-startIncr',
                                    '5-startLog',
                                    '5-startStra',
                                    '5-motion',
                                    '5-takeover',
                                    '5-stoptakeover',
                                    '5-startfailback',
                                    '5-stopfailback',
                                    'post-jobs_start',
                                    'post-jobs_stop',
                                    'delete-jobs',
                                    'post-jobs_start_takeover',
                                    'post-jobs_stop_takeover',
                                    'post-jobs_start_failback',
                                    'post-jobs_pause',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_JOB_OPERATE_DESC',
                            ),
                        )
                    ),
                    // 历史任务
                    array(
                        'name' => "history_job",
                        'path' => "./content/platform/jobs/history_job.php",
                        'class' => "viconfont vicon-pt_job_historical_task",
                        'title' => "UI_PLATFORM_HOSTORY_JOB",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_history_job_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '15-getAllHypervisorType',
                                    '16-getNodeList',
                                    '5-getHistoryJobs',
                                    'get-jobs_history',
                                ),
                                'level' => 10,
                            ),
                            // 删除
                            array(
                                'name' => "p_history_job_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '5-deleteHistoryTask',
                                    'delete-jobs_history',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 下载任务日志
                            array(
                                'name' => "p_history_job_download",
                                'title' => "UI_ALARM_LOG_DOWNLOAD",
                                'function' => array(
                                    '5-downLoadPassFile',
                                    '5-downloadHistoryCheck',
                                    '11-downLoadTaskLog',
                                    'get-jobs_log',
                                    'get-jobs_log_down',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 任务编排
                    array(
                        'name' => 'task_orchestration',
                        'path' => "./content/platform/orchestration/orchestration_manage.php",
                        'class' => "viconfont vicon-ge_advanced",
                        'title' => "UI_JOB_TASK_ORCHESTRATION",
                        'level' => 2,
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_task_orchestration_list',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => [
                                    'get-orchestration',
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_task_orchestration_manager",
                                'title' => "UI_PLATFORM_MANAGEMENT",
                                'function' => [
                                    'get-orchestration_operate',
                                    'delete-orchestration',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_JOB_ORA_OPERATE_DESC',
                            ],
                        ]
                    ),
                )
            ),
            //告警
            array(
                'name' => "alarm",
                'path' => "./content/platform/alarm/alarm.php",
                'class' => "viconfont vicon-alarm",
                'title' => "UI_PLATFORM_ALARM",
                'level' => 1,
                'child' => array(
                    // 任务告警
                    array(
                        'name' => "task_alarm",
                        'path' => "./content/platform/alarm/task_alarm.php",
                        'class' => "viconfont vicon-pt_alarm_task_alarms ",
                        'title' => "UI_PLATFORM_ALARM_TASK",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_task_alarm_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '16-getNodeList',
                                    '15-getAllHypervisorType',
                                    '11-getTaskAlarms',
                                    '11-getTaskAlarmDetails',
                                    '11-getTaskAlarmDetailsLogs',
                                    '11-getTaskAlarmDetailsDbInfo',
                                    '11-getTaskAlarmDetailsCopyInfo',
                                    '11-getBackupTaskInfo',
                                    '11-getTaskAlarmDetailsVmInfo',
                                    '5-getTaskAlarmDetailsVmInfo',
                                ),
                                'level' => 10,
                            ),
                            // 删除
                            array(
                                'name' => "p_task_alarm_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '11-deleteTaskAlarm'
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 响应
                            array(
                                'name' => "p_task_alarm_response",
                                'title' => "UI_PUBLIC_RESPONSE",
                                'function' => array(
                                    '11-solvedTaskAlarm',
                                    '11-notSolvedTaskAlarm',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 下载日志
                            array(
                                'name' => "p_task_alarm_download",
                                'title' => "UI_ALARM_LOG_DOWNLOAD",
                                'function' => array(
                                    '11-downLoadTaskLog',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 系统告警
                    array(
                        'name' => "system_alarm",
                        'path' => "./content/platform/alarm/system_alarm.php",
                        'class' => "viconfont vicon-pt_alarm_system_alarm ",
                        'title' => "UI_PLATFORM_ALARM_SYSTEM",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_system_alarm_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '16-getNodeList',
                                    '15-getAllHypervisorType',
                                    '11-getSystemAlarms',
                                    '11-getSystemAlarmDetails',
                                ),
                                'level' => 10,
                            ),
                            // 删除
                            array(
                                'name' => "p_system_alarm_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '11-deleteSystemAlarm'
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 响应
                            array(
                                'name' => "p_system_alarm_response",
                                'title' => "UI_PUBLIC_RESPONSE",
                                'function' => array(
                                    '11-solvedSystemAlarm',
                                    '11-notSolvedSystemAlarm',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
            //日志
            array(
                'name' => "log",
                'path' => "./content/platform/logs/logs.php",
                'class' => "viconfont vicon-log",
                'title' => "UI_PLATFORM_LOG",
                'level' => 1,
                'child' => array(
                    // 任务操作日志
                    array(
                        'name' => "job_log",
                        'path' => "./content/platform/logs/job_log.php",
                        'class' => "viconfont vicon-pt_log_task_operation_logs ",
                        'title' => "UI_PLATFORM_JOB_LOG",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_job_log_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '15-getAllHypervisorType',
                                    '7-getJobLogs',
                                    '16-getAddStorageNodeSelect',
                                ),
                                'level' => 10,
                            ),
                            // 删除
                            array(
                                'name' => "p_job_log_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '7-deleteTaskLog',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 导出
                            array(
                                'name' => "p_job_log_export",
                                'title' => "UI_PUBLIC_EXPORT",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 系统操作日志
                    array(
                        'name' => "system_log",
                        'path' => "./content/platform/logs/system_log.php",
                        'class' => "viconfont vicon-pt_log_system_operation_logs ",
                        'title' => "UI_PLATFORM_SYSTEM_LOG",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_system_log_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '15-getAllHypervisorType',
                                    '7-getSystemLogs',
                                    '16-getAddStorageNodeSelect',
                                ),
                                'level' => 10,
                            ),
                            // 删除
                            array(
                                'name' => "p_system_log_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '7-deleteSystemLog',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 导出
                            array(
                                'name' => "p_system_log_export",
                                'title' => "UI_PUBLIC_EXPORT",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 高可用操作日志
                    array(
                        'name' => "ha_log",
                        'path' => "./content/platform/logs/ha_log.php",
                        'class' => "viconfont vicon-gaokeyongrizhi ",
                        'title' => "UI_PLATFORM_HA_LOG",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_ha_log_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-logs_ha_logs',
                                ),
                                'level' => 10,
                            ),
                            // 删除
                            array(
                                'name' => "p_ha_log_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    'delete-logs_ha_logs',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 导出
                            array(
                                'name' => "p_ha_log_export",
                                'title' => "UI_PUBLIC_EXPORT",
                                'function' => array(
                                    'post-logs_ha_logs',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
            //报表
            array(
                'name' => "report",
                'path' => "javascript:;",
                'class' => "viconfont vicon-report",
                'title' => "UI_PLATFORM_REPORT",
                'level' => 1,
                'showChild' => true,
                'child' => array(
                    // 概览
                    array(
                        'name' => "vm_report",
                        'path' => "./content/platform/reports/overview.php",
                        'class' => "viconfont vicon-pt_report_shortage_report",
                        'title' => "UI_REPORT_OVERVIEW",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_vm_report_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-report_template_agentOverview',
                                    'get-report_template_vmOverview',
                                    'get-report_template_cloudOverview',
                                    'get-report_template_nasOverview',
                                    'get-report_template_cdpOverview',
                                    'get-report_template_appOverview',
                                    'get-report_template_nodeOverview',
                                    'get-report_template_storageOverview',
                                    'get-report_template_taskTendencyOverview',
                                    'get-report_template_alarmOverview',
                                ),
                                'level' => 10,
                            ),
                            // 导出
                            array(
                                'name' => "p_vm_report_export",
                                'title' => "UI_PUBLIC_EXPORT",
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 自定义报表
                    array(
                        'name' => "storage_report",
                        'path' => "./content/platform/reports/strategy.php",
                        'class' => "viconfont vicon-moban",
                        'title' => "UI_REPORT_TEMPLATE",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_storage_report_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-report_template_list',
                                    'get-report_template_get_addressInfo',
                                    'get-report_template_get_email',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_storage_report_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_storage_report_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_storage_report_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 导出
                            array(
                                'name' => "p_storage_report_export",
                                'title' => "UI_PUBLIC_EXPORT",
                                'level' => 10,
                                'function' => [
                                    'post-report_edit_template',
                                    'delete-report_template',
                                ],
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
            //系统
            array(
                'name' => "system",
                'path' => "./content/platform/system/system.php",
                'class' => "viconfont vicon-system",
                'title' => "UI_PLATFORM_SYSTEM",
                'level' => 1,
                'child' => array(
                    // 查看
                    array(
                        'name' => "p_system_list",
                        'title' => "UI_PUBLIC_LOOK",
                        'function' => array(
                            '37-initDataFunc',
                            '37-getAlarmVal',
                            '37-setAlarmVal',
                            '37-getBasicInfo',
                        ),
                        'level' => 10,
                    ),
                    // 下载系统日志
                    array(
                        'name' => "p_system_log_download",
                        'title' => "UI_LOG_SYSTEM_LOG_DOWNLOAD",
                        'function' => array(
                            '7-getSystemDownloadLogs',
                            '7-getDownLoadPackageName',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                    // 设置告警规则
                    array(
                        'name' => 'p_system_rule',
                        'title' => 'UI_SYSTEM_MONITOR_SET_ALARM',
                        'function' => array(
                            '37-initDataFunc',
                            '37-setAlarmVal',
                            '13-getStorageLine',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    )
                )
            ),
        )
    ),

    // 备份
    array(
        'name' => "backup",
        'path' => "javascript:;",
        'class' => "viconfont vicon-backup",
        'title' => "UI_PLATFORM_DATA_BACKUP",
        'level' => 0,
        'is_operate' => 1,
        'child' => array(
            // 虚拟化
            array(
                'name' => "vmprotect",
                'path' => "./content/vm/vmbackup.php",
                'class' => "viconfont vicon-overview-vm",
                'title' => "UI_PLATFORM_VM_VIRTUAL",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_vmbackup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '4-getAuthFunc',
                            '16-getAddStorageNodeSelect',
                            '16-getBackupServerIp',
                            '10-getBackupStorageList',
                            '8-getSystemTimeJSFormat',
                            '1-getVMBackupTaskName',
                            '1-createBackupJob',
                            '15-getVMDiskList',
                            '15-getBackupSyncVcenter',
                            '15-getVcenterDetailsTree',
                            '15-getBackupTreeSpeed',
                            '5-getTimeCrowdList',
                            '5-getStrategySelect',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_VM_OPERATE_DESC',
                    ),
                )
            ),
            // 私有云
            array(
                'name' => "prcloud_protect",
                'path' => "./content/vm/vmbackup.php?sub_module_type=2",
                'class' => "viconfont vicon-overview-private-cloud",
                'title' => "UI_PLATFORM_PRIVATE_CLOUD",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_prcloud_backup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '4-getAuthFunc',
                            '16-getAddStorageNodeSelect',
                            '16-getBackupServerIp',
                            '10-getBackupStorageList',
                            '8-getSystemTimeJSFormat',
                            '1-getVMBackupTaskName',
                            '1-createBackupJob',
                            '15-getVMDiskList',
                            '15-getBackupSyncVcenter',
                            '15-getVcenterDetailsTree',
                            '15-getBackupTreeSpeed',
                            '5-getTimeCrowdList',
                            '5-getStrategySelect',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_PRCLOUD_OPERATE_DESC',
                    ),
                )
            ),
            // 公有云
            array(
                'name' => "awsprotect",
                'path' => "./content/aws/awsbackup.php",
                'class' => "viconfont vicon-overview-plubic-cloud",
                'title' => "UI_PLATFORM_PUBLIC_CLOUD",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_awsbackup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '4-getAuthFunc',
                            '16-getAddStorageNodeSelect',
                            '16-getBackupServerIp',
                            '10-getBackupStorageList',
                            '8-getSystemTimeJSFormat',
                            '1-getVMBackupTaskName',
                            '1-createBackupJob',
                            '15-getVMDiskList',
                            '15-getBackupSyncVcenter',
                            '15-getVcenterDetailsTree',
                            '15-getBackupTreeSpeed',
                            '5-getTimeCrowdList',
                            '5-getStrategySelect',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_AWS_OPERATE_DESC',
                    ),
                )
            ),
            // *整机 如果只授权了整机-磁盘，那么语言包就显示成整机
            // 磁盘 ./content/complete_machine_os/machine_os_backup.php
            array(
                'name' => "complete_machine",
                'path' => "./content/complete_machine_os/machine_os_backup.php",
                'class' => "viconfont vicon-overview-complete-machine",
                'title' => "UI_PLATFORM_MACHINE_COMPLETE_BACKUP",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_complete_machine_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(

                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_MACHINE_OPERATE_DESC',
                    ),
                )
            ),
            // 卷 ./content/os/osbackup.php
            array(
                'name' => "osbackup",
                'path' => "./content/os/osbackup.php",
                'class' => "viconfont vicon-overview-volume",
                'title' => "UI_PLATFORM_MACHINE_REEL_BACKUP",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_osbackup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '34-getOsBackupTree',
                            '34-getOSBackupTaskName',
                            '34-createOSBackupJob',
                            '34-getOSBackupAgentInfo',
                            '5-getTimeCrowdList',
                            '5-getStrategySelect',
                            '10-getBackupStorageList',
                            '16-getAddStorageNodeSelect',
                            '16-getNodeNetworkList',
                            '2-getAgentFileDir',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_OS_OPERATE_DESC',
                    ),
                )
            ),
            // 文件
            array(
                'name' => "fileprotect",
                'path' => "javascript:;",
                'class' => "viconfont vicon-overview-file",
                'title' => "UI_PLATFORM_FILES_PAGE",
                'level' => 1,
                'is_operate' => 1,
                'showChild' => true,
                'child' => array(
                    // 文件
                    array(
                        'name' => "filebackup",
                        'path' => "./content/fs/filebackup.php",
                        'class' => "viconfont vicon-overview-file",
                        'title' => "UI_PLATFORM_FILES",
                        'level' => 2,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_filebackup_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '9-getAgentGroupBackupTree',
                                    '10-getBackupStorageList',
                                    '5-getTimeCrowdList',
                                    '5-getStrategySelect',
                                    '2-getFileBackupTaskName',
                                    '2-createBackupJob',
                                    /*'2-getFileDirTree',
                                    '2-getFileDirSonTree',*/
                                    '16-getAddStorageNodeSelect',
                                    '9-getAgentGroupBackupTree',
                                    '16-getNodeNetworkList',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_FILE_OPERATE_DESC',
                            ),
                        )
                    ),
                    // NAS
                    array(
                        'name' => "nas_protect",
                        'path' => "./content/nas/nasbackup.php",
                        'class' => "viconfont vicon-overview-nas",
                        'title' => "UI_PLATFORM_NAS",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_nasbackup_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '36-getNasBackupTree',
                                    '36-getAllMountNode',
                                    '36-getNasBackupTaskName',
                                    '36-createNasJob',
                                    '36-getNasBackupTree',
                                    '36-getNasSonTree',
                                    '16-getAddStorageNodeSelect',
                                    '10-getBackupStorageList',
                                    '5-getTimeCrowdList',
                                    '5-getStrategySelect',
                                    '8-getSystemLisenceInfo',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_NAS_OPERATE_DESC',
                            ),
                        )
                    ),
                    // 对象存储
                    array(
                        'name' => "obs_protect",
                        'path' => "./content/s3/obsbackup.php",
                        'class' => "viconfont vicon-overview-obs",
                        'title' => "UI_PLATFORM_OBS_STORAGE",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            [
                                'name' => 'p_obsbackup_list',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_OBS_OPERATE_DESC',
                            ],
                        )
                    ),
                    // Hadoop HDFS
                    array(
                        'name' => "hadoop_protect",
                        'path' => "./content/hadoop/hadoop_backup.php", //backup
                        'class' => "viconfont vicon-overview-hadoop",
                        'title' => "UI_PLATFORM_HADOOP_HDFS",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            [
                                'name' => 'p_hadoop_backup_list',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_HADOOP_OPERATE_DESC',
                            ],
                        )
                    ),
                )
            ),
            // 数据库
            array(
                'name' => "db_protect",
                'path' => "./content/dbprotect/dbbackup.php",
                'class' => "viconfont vicon-overview-database",
                'title' => "UI_PLATFORM_DATABASE",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_db_backup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '27-getBackupTypeList',
                            '16-getAddStorageNodeSelect',
                            '16-getNodeNetworkList',
                            '5-getStrategySelect',
                            '27-getDBBackupAgentTree',
                            '27-getDBBackupTaskName',
                            '27-createDBBackupJob',
                            '27-getBackupSyncAgent',
                            '10-getBackupStorageList',
                            '2-getAgentFileDir',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_DB_OPERATE_DESC',
                    ),
                )
            ),
            // Microsoft 365
            array(
                'name' => "office365_protect",
                'path' => "./content/exchange/exchange_backup.php",
                'class' => "viconfont vicon-windows-view",
                'title' => "UI_PLATFORM_MICROSOFT365",
                'level' => 1,
                'is_operate' => 1,
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
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_M365_OPERATE_DESC',
                    ),
                )
            ),
            // 容器
            array(
                'name' => "k8s_protect",
                'path' => "./content/kubernetes/kubernetes_backup.php", //backup
                'class' => "viconfont vicon-overciew-k8s",
                'title' => "UI_PLATFORM_K8S",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => 'p_k8s_backup_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => array(
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_K8S_OPERATE_DESC',
                    ),
                ),
            ),
            // ***云存储同步
            array(
                'name' => "cbrbackup",
                'path' => "./content/cbr/cbrbackup.php",
                'class' => "viconfont vicon-tongbu",
                'title' => "UI_PLATFORM_STORAGE_SYNC",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_cbrbackup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '1-createCBRSyncJob',
                            '1-getCBRDetailsTreeNew',
                            '1-getSyncCBR',
                            '1-getCBRMP',
                            '1-getCBRTP',
                            '8-getSystemTimeJSFormat',
                            '1-getCBRSyncTaskName',
                            '10-getBackupStorageListNew',
                            '16-getAddStorageNodeSelectNew',
                            '5-getTimeCrowdList',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_CBR_OPERATE_DESC',
                    ),
                )
            ),
        )
    ),

    // 连续数据保护
    array(
        'name' => "vol_cdp_protect",
        'path' => "javascript:;",
        'class' => "viconfont vicon-vol_cdp_protect",
        'title' => "UI_PLATFORM_CDP_PROTECT",
        'level' => 0,
        'child' => array(
            //  *整机 如果只授权了整机-磁盘，那么语言包就显示成整机
            // 磁盘 ./complete_machine_volcdp/cm_volcdp_backup.php
            array(
                'name' => "complete_cdp_backup",
                'path' => "./content/complete_machine_volcdp/cm_volcdp_backup.php?task_type=backup",
                'class' => "viconfont vicon-overview-complete-machine",
                'title' => "UI_PLATFORM_CDP_COMPLETE_BACKUP",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_vol_cdp_backup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '4-getAuthFunc',
                            '30-getVolCdpBackupAgentTree',
                            '30-createVolCDPBackupJob',
                            '30-verifyingVolCdpAuthNum',
                            '30-updateAgentInfo',
                            '30-updateAppInfo',
                            '30-getHostVolinfo',
                            '30-getVolCdpBackupAgentTree',
                            '30-verifyVolCdpTakeoverAuthNum',
                            '30-getStandbyHostInfo',
                            '30-checkIpExists',
                            '30-getVolCdpBackupTaskName',
                            '30-verifyingVolCdpAuthNum',
                            '30-createBackupJob',
                            '10-getBackupStorageList',
                            '16-getAddStorageNodeSelect',
                            '16-getNodeNetworkList',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_CDP_OPERATE_DESC',
                    ),
                )
            ),
            // 卷  ./content/volcdp/vol_cdp_backup.php
            array(
                'name' => "vol_cdp_backup",
                'path' => "./content/volcdp/vol_cdp_backup.php?task_type=backup",
                'class' => "viconfont vicon-overview-volume",
                'title' => "UI_PLATFORM_CDP_REEL_BACKUP",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_vol_cdp_backup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            '4-getAuthFunc',
                            '30-getVolCdpBackupAgentTree',
                            '30-createVolCDPBackupJob',
                            '30-verifyingVolCdpAuthNum',
                            '30-updateAgentInfo',
                            '30-updateAppInfo',
                            '30-getHostVolinfo',
                            '30-getVolCdpBackupAgentTree',
                            '30-verifyVolCdpTakeoverAuthNum',
                            '30-getStandbyHostInfo',
                            '30-checkIpExists',
                            '30-getVolCdpBackupTaskName',
                            '30-verifyingVolCdpAuthNum',
                            '30-createBackupJob',
                            '10-getBackupStorageList',
                            '16-getAddStorageNodeSelect',
                            '16-getNodeNetworkList',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_VOL_CDP_OPERATE_DESC',
                    ),
                )
            ),
            //数据库实时备份,永思
            array(
                'name' => "dbprotect",
                'path' => "javascript:;",
                'class' => "viconfont vicon-dbprotect",
                'title' => "UI_PLATFORM_DB_CDP",
                'level' => 1,
                'showChild' => true,
                'child' => array(
                    // 备份
                    array(
                        'name' => "dbcdpbackup",
                        'path' => "./content/db/dbcdp.php",
                        'class' => "viconfont vicon-dbcdpbackup",
                        'title' => "UI_PLATFORM_BACKUP",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_dbcdpbackup_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '19-gettHostInfoWithType',
                                    '19-getHostInstance',
                                    '19-scanHostDB',
                                    '19-getStandbyHostNetworkCard',
                                    '19-getHostService',
                                    '19-getTakeoverNetworkCardInfo',
                                    '19-createBackupJob',
                                    '19-getBackupDirInfo',
                                    '19-checkBackupHostIsServer',
                                    '19-getBackupTaskName',
                                    '19-standbyHostDirCheck',
                                    '19-takeoverEnvironmentCheck',
                                    '10-getBackupStorageList',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_DB_CDP_OPERATE_DESC',
                            ),
                        )
                    ),
                    // 恢复
                    array(
                        'name' => "dbdataRecovery",
                        'path' => "./content/db/dbrecovery.php",
                        'class' => "viconfont vicon-dbdataRecovery",
                        'title' => "UI_PLATFORM_RECOVER",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_dbdataRecovery_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '19-gettHostInfoWithType',
                                    '19-gettRecoveryHostInfo',
                                    '19-getRecoveryTaskName',
                                    '19-recoveryInstanceTestCon',
                                    '19-createRecoveryJob',
                                    '19-getHostInstance',
                                    '19-scanBackupTimepoint',
                                    '19-scanBackupDatabase',
                                    '19-gettHostInfoWithType',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_DB_CDP_RECOVER_OPERATE_DESC',
                            ),
                        )
                    ),
                    // 备份数据
                    array(
                        'name' => "dbdata",
                        'path' => "./content/db/dbdata.php",
                        'class' => "viconfont vicon-db_protect",
                        'title' => "UI_PLATFORM_BACKUPDATA",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_dbdata_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '19-getCDPDBTree',
                                    '19-getDatabaseSyncTree',
                                    '19-scanBackupTimepoint',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_DB_CDP_DATA_OPERATE_DESC',
                            ),
                        )
                    ),
                    // 主机管理
                    array(
                        'name' => "dbhost",
                        'path' => "./content/db/dbhost.php",
                        'class' => "viconfont vicon-dbhost",
                        'title' => "UI_PLATFORM_DB_HOST_MANAGER",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_dbhost_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '19-getDbHostInfo',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_dbhost_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '19-addHost',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_dbhost_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '19-editHost',
                                    '19-getEditHostInfo',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_dbhost_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '19-deleteHost',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 下载日志
                            array(
                                'name' => "p_dbhost_download",
                                'title' => "UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW",
                                'function' => array(
                                    '19-checkHostLog',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
        )
    ),

    // 复制容灾
    array(
        'name' => "copy",
        'path' => "javascript:;",
        'class' => "viconfont vicon-fuzhi",
        'title' => "UI_PLATFORM_DATA_COPY",
        'level' => 0,
        'is_operate' => 1,
        'child' => array(
            // *整机 如果只授权了整机-磁盘，那么语言包就显示成整机
            // 磁盘 ./complete_machine_volcdp/cm_volcdp_copy.php
            array(
                'name' => "machine_copy",
                'path' => "./content/complete_machine_volcdp/cm_volcdp_backup.php?task_type=copy",
                'class' => "viconfont vicon-overview-complete-machine",
                'title' => "UI_PLATFORM_COPY_COMPLETE_BACKUP",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_machine_os_backup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_COPY_CDP_MACHINE_OPERATE_DESC',
                    ),
                )
            ),
            // 卷  ./content/volcdp/vol_cdp_backup.php?type=reel
            array(
                'name' => "vol_cdp_copy",
                'path' => "./content/volcdp/vol_cdp_backup.php?task_type=copy",
                'class' => "viconfont vicon-overview-volume",
                'title' => "UI_PLATFORM_COPY_REEL_BACKUP",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_machine_os_backup_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_COPY_CDP_OPERATE_DESC',
                    ),
                )
            ),
            // 文件 如果后期有其他如NAS，Hadoop，通过选择内部选择类型来处理，统一流程
            array(
                'name' => "file_copy_protect",
                'path' => "./content/filecopy/file_copy.php",
                'class' => "viconfont vicon-filecdpdata",
                'title' => "UI_PLATFORM_FILES",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => "p_file_copy_list",
                        'title' => "UI_PUBLIC_OPERATION",
                        'function' => array(
                            'get-system_times_info',
                            'get-exchange_jobs_backup_task_name',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_COPY_FILE_OPERATE_DESC',
                    ),
                )
            ),
            // 数据库
            array(
                'name' => 'dbcdpcopy',
                'path' => "./content/dbcdp/dbcdp_backup.php",
                'class' => "viconfont vicon-overview-database",
                'title' => "UI_PLATFORM_DATABASE",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 操作
                    array(
                        'name' => 'p_dbcdp_backup_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => array(
                            'post-dbcdp_databases',
                            'get-dbcdp_jobs_networkInfo',
                            'post-dbcdp_jobs_scanIp',
                            'post-dbcdp_jobs_sync'
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'UI_PAGE_COPY_DB_OPERATE_DESC',
                    )
                )
            ),
        )
    ),

    // 备份数据管理
    array(
        'name' => "data_manager",
        'path' => "javascript:;",
        'class' => "viconfont vicon-shujuguanli",
        'title' => "UI_PLATFORM_DATA_MANAGER",
        'level' => 0,
        'child' => array(
            // 备份数据管理
            array(
                "name" => "backup_data",
                'path' => "./content/backupData/backupData.php",
                'class' => "viconfont vicon-shuju",
                'title' => "UI_PLATFORM_BACKUP_DATA",
                'level' => 1,
                'child' => array(
                    // 查看
                    array(
                        'name' => "p_backup_data_list",
                        'title' => "UI_PUBLIC_LOOK",
                        'function' => array(
                            'get-backup_data_jobs_list', // 获取任务列表
                            'get-backup_data_items_list', // 获取对象列表
                            'get-backup_data_vol_list', // 获取整机实时标签点
                            'get-backup_data_remote_list', // 获取异地副本数据列表
                            'get-backup_data_remote_tree', // 获取异地副本数据树
                            'get-backup_data_points', // 获取备份点
                            'get-backup_data_points_path', // 获取时间点备份的虚拟机路径、文件列表等
                        ),
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_backup_data_operate",
                        'title' => "UI_PUBLIC_OPERATION",
                        'level' => 10,
                        'is_operate' => 1,
                        'function' => array(
                            'delete-backup_data_vol_list', // 删除整机实时标签点
                            'get-backup_data_vol_remark', // 整机标签点备注
                            'delete-backup_data_points', // 删除备份点
                            'get-backup_data_points_remark', // 设置备份点备注
                            'get-backup_data_points_gfs_mark', // 设置备份点gfs标记
                            'get-backup_data_points_mark', // 设置永久标记
                            'delete-backup_data_points_mark', // 取消永久标记
                            'get-backup_data_points_depend', // 获取完备点的依赖非完备点
                            'get-backup_data_points_worm', // 配置worm时间
                        ),
                        'desc' => 'UI_PAGE_BACKUP_DATA_OPERATE_DESC',
                    )
                )
            ),
            // *恢复 右侧为平铺导航页面，参考visor
            array(
                "name" => "recovery",
                'path' => "./content/recovery/recoverycenter.php",
                'class' => "viconfont vicon-dbdataRecovery",
                'title' => "UI_PLATFORM_RECOVER",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // 1--- 备份 ---.虚拟化, 私有云, 公有云, 容器, 整机, 文件, NAS, 对象存储, Hadoop HDFS, 数据库, 应用
                    // 2 --- 持续数保护&复制 --- 整机, 数据库
                    // 虚拟化
                    array(
                        'name' => "vmprotect_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-overview-vm",
                        'title' => "UI_PLATFORM_VM_PROTECT",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "vmrecover",
                                'path' => "./content/vm/vmrecover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vmrecover_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '4-getAuthFunc',
                                            '16-getTimepointAllNode',
                                            '1-getTimepointTree',
                                            '5-getStrategySelect',
                                            '5-getTimeCrowdList',
                                            '16-getApplianceSelect',
                                            '15-veryfyVcenterGroupUser',
                                            '15-getSyncVcenterGroup',
                                            '15-getSyncRecoveryVcenter',
                                            '1-getRecoverHost',
                                            '1-getRecoverUserGroup',
                                            '1-getOpenStackNetworkAndStorage',
                                            '1-getNetworkAndStorage',
                                            '1-getVMConfigInfoV2',
                                            '1-getVMRecoverTaskName',
                                            '1-checkVMEncryptPass',
                                            '1-createRecoverJob',
                                            '1-getTimepointAllNode',
                                            '16-getBackupServerIp',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_VM_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复
                            array(
                                'name' => "vm_instant_recovery",
                                'path' => "./content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=1",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "WEB_VM_INSTANT_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_instant_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_VM_INSTANT_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复快照点
                            /*array(
                                'name' => "vm_instant_recovery_point",
                                'path' => "./content/platform/recovery/points.php?recovery_type=vm&subtype=1",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "UI_INSTANT_POINTS",
                                'level' => 3,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_instant_recovery_point_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                )
                            ),*/
                            // 细粒度恢复
                            array(
                                'name' => "vm_grain_recovery",
                                'path' => "./content/platform/recovery/graininess.php?recovery_type=vm&subtype=1",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_GRAIN_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_grain_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_VM_GRAIN_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 跨平台恢复
                            array(
                                'name' => "vm_platform_recovery",
                                'path' => "./content/platform/recovery/platform.php?recovery_type=vm&subtype=1",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_RECOVERY",
                                'level' => 2,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_platform_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_VM_PLATFORM_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),
                    // 私有云
                    array(
                        'name' => "prcloud_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-recover",
                        'title' => "UI_PLATFORM_PRIVATE_CLOUD_PROTECT",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "vm_prcloud_recovery",
                                'path' => "./content/vm/vmrecover.php?sub_module_type=2",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_prcloud_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '4-getAuthFunc',
                                            '16-getTimepointAllNode',
                                            '1-getTimepointTree',
                                            '5-getStrategySelect',
                                            '5-getTimeCrowdList',
                                            '16-getApplianceSelect',
                                            '15-veryfyVcenterGroupUser',
                                            '15-getSyncVcenterGroup',
                                            '15-getSyncRecoveryVcenter',
                                            '1-getRecoverHost',
                                            '1-getRecoverUserGroup',
                                            '1-getOpenStackNetworkAndStorage',
                                            '1-getNetworkAndStorage',
                                            '1-getVMConfigInfoV2',
                                            '1-getVMRecoverTaskName',
                                            '1-checkVMEncryptPass',
                                            '1-createRecoverJob',
                                            '1-getTimepointAllNode',
                                            '16-getBackupServerIp',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_PRCLOUD_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复
                            array(
                                'name' => "vm_prcloud_instant_recovery",
                                'path' => "./content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=2",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "WEB_VM_INSTANT_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_prcloud_instant_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_PRCLOUD_INSTANT_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复快照点
                            /*array(
                                'name' => "vm_prcloud_instant_recovery_point",
                                'path' => "./content/platform/recovery/points.php?recovery_type=vm&subtype=2",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "UI_INSTANT_POINTS",
                                'level' => 3,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_prcloud_instant_recovery_point_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                )
                            ),*/
                            // 细粒度恢复
                            array(
                                'name' => "vm_prcloud_graininess_recovery",
                                'path' => "./content/platform/recovery/graininess.php?recovery_type=vm&subtype=2",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_GRAIN_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_prcloud_graininess_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_PRCLOUD_GRAIN_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 跨平台恢复
                            array(
                                'name' => "vm_prcloud_platform_recovery",
                                'path' => "./content/platform/recovery/platform.php?recovery_type=vm&subtype=2",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_prcloud_platform_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_PRCLOUD_PLATFORM_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),
                    // 公有云
                    array(
                        'name' => "awsprotect_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-recover",
                        'title' => "UI_PLATFORM_AWS_PROTECT",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "vm_awsprotect_recover",
                                'path' => "./content/aws/awsrecover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_awsprotect_recover_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '4-getAuthFunc',
                                            '16-getTimepointAllNode',
                                            '1-getTimepointTree',
                                            '5-getStrategySelect',
                                            '5-getTimeCrowdList',
                                            '16-getApplianceSelect',
                                            '15-veryfyVcenterGroupUser',
                                            '15-getSyncVcenterGroup',
                                            '15-getSyncRecoveryVcenter',
                                            '1-getRecoverHost',
                                            '1-getRecoverUserGroup',
                                            '1-getOpenStackNetworkAndStorage',
                                            '1-getNetworkAndStorage',
                                            '1-getVMConfigInfoV2',
                                            '1-getVMRecoverTaskName',
                                            '1-checkVMEncryptPass',
                                            '1-createRecoverJob',
                                            '1-getTimepointAllNode',
                                            '16-getBackupServerIp',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_AWS_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复
                            array(
                                'name' => "vm_awsprotect_instant_recovery",
                                'path' => "./content/platform/recovery/instantaneous.php?recovery_type=vm&subtype=3",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "WEB_VM_INSTANT_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_awsprotect_instant_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_AWS_INSTANT_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复快照点
                            /*array(
                                'name' => "vm_awsprotect_instant_recovery_point",
                                'path' => "./content/platform/recovery/points.php?recovery_type=vm&subtype=3",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "UI_INSTANT_POINTS",
                                'level' => 3,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_awsprotect_instant_recovery_point_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                )
                            ),*/
                            // 细粒度恢复
                            array(
                                'name' => "vm_awsprotect_grain_recovery",
                                'path' => "./content/platform/recovery/graininess.php?recovery_type=vm&subtype=3",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_GRAIN_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_awsprotect_grain_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_AWS_GRAIN_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 跨平台恢复
                            array(
                                'name' => "vm_awsprotect_platform_recovery",
                                'path' => "./content/platform/recovery/platform.php?recovery_type=vm&subtype=3",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vm_awsprotect_platform_recover_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_AWS_PLATFORM_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),
                    // 容器
                    array(
                        'name' => "k8s_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-k8s",
                        'title' => "UI_PLATFORM_K8S",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "k8s_recovery",
                                'path' => "./content/kubernetes/kubernetes_recovery.php", //recovery
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_k8s_recovery_list',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_K8S_OPERATE_DESC',
                                    ),
                                ),
                            ),
                        )
                    ),
                    // 文件
                    array(
                        'name' => "file_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-host_protect",
                        'title' => "UI_PLATFORM_FILES",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "file_recovery",
                                'path' => "./content/fs/filerecover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_file_recovery_list',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => array(
                                            '16-getFSTimepointAllNode',
                                            '16-getNodeNetworkList',
                                            '2-getFileDataTree',
                                            '2-checkFSEncryptPass',
                                            '2-getBackupFileDir',
                                            '2-getSyncFileTimepoint',
                                            '2-getRecovHostTree',
                                            '2-getRecoverPathTree',
                                            '2-getFileRecoverTaskName',
                                            '36-getNasBackupTree',
                                            '36-getRecoverPathTree',
                                            '2-createRecoverJob',
                                            '36-createRecoverJob',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_FILE_OPERATE_DESC',
                                    ),
                                ),
                            ),
                        )
                    ),
                    // NAS
                    array(
                        'name' => "nasrecover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-nas_protect",
                        'title' => "UI_PLATFORM_NAS_PROTECT",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "nas_recovery",
                                'path' => "./content/nas/nasrecover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_nas_recovery_list',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => array(
                                            '16-getNASTimepointAllNode',
                                            '36-getNasDataTree',
                                            '36-getRecoveryNasDir',
                                            '36-getSyncNasTimepoint',
                                            '36-getRecoverPathTree',
                                            '36-getNasBackupTree',
                                            '36-getNasRecoverTaskName',
                                            '36-createRecoverJob',
                                            '2-checkFSEncryptPass',
                                            '2-getRecovHostTree',
                                            '2-getRecoverPathTree',
                                            '2-createRecoverJob',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_NAS_OPERATE_DESC',
                                    ),
                                ),
                            ),
                        )
                    ),
                    // 对象存储
                    array(
                        'name' => "obs_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-a-Group167-01",
                        'title' => "UI_PLATFORM_OBS_PROTECT",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "obs_recovery",
                                'path' => "./content/s3/obsrecover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_obs_recovery_list',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_OBS_OPERATE_DESC',
                                    ),
                                ),
                            ),
                        )
                    ),
                    // Hadoop HDFS
                    array(
                        'name' => "hadoop_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-a-Elephantdaxiang-01",
                        'title' => "WEB_HADOOP_PROTECT",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "hadoop_recovery",
                                'path' => "./content/hadoop/hadoop_recovery.php", //recovery
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_hadoop_recovery_list',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_HADOOP_OPERATE_DESC',
                                    ),
                                ),
                            ),
                        )
                    ),
                    // 数据库
                    array(
                        'name' => "db_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-db_protect",
                        'title' => "UI_PLATFORM_DB_PROTECT",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "db_recovery",
                                'path' => "./content/dbprotect/dbrecover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_db_recovery_list',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => array(
                                            '16-getTimepointAllNode',
                                            '16-getNodeNetworkList',
                                            '27-getTimepointTree',
                                            '27-getSyncRecoveryInstance',
                                            '27-getRecoverAgentTree',
                                            '27-getDBOldConifg',
                                            '27-getDBRecoveryTaskName',
                                            '27-checkDBEncryptPass',
                                            '27-createDBRecoveryJob',
                                            '15-getSyncRecoveryVcenter',
                                            '27-getRestoreArchivelogInfo',
                                            'post-db_jobs_recovery', // 创建恢复任务
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_DB_OPERATE_DESC',
                                    ),
                                ),
                            ),
                            // 演练
                            array(
                                'name' => "db_drill",
                                'path' => "./content/dbprotect/dbrecover.php?timepoint_recovery_type=2",
                                'class' => "viconfont vicon-huifu1",
                                'title' => "UI_PLATFORM_DB_DRILL",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_db_drill_list',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => array(
                                            'post-db_jobs_recovery',  // 创建演练
                                            'put-db_jobs_recovery',  // 修改演练
                                            'get-getDbRecoveryJob',  // 获取演练任务信息
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_DB_DRILL_OPERATE_DESC',
                                    ),
                                ),
                            ),
                        )
                    ),
                    // 应用
                    array(
                        'name' => "exchange_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-yingyong",
                        'title' => "UI_PLATFORM_APPLICATION",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "exchange_recovery",
                                'path' => "./content/exchange/exchange_recover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => 'p_exchange_recovery_list',
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
                                            'get-exchange_restore_auth'
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_EXCHANGE_OPERATE_DESC',
                                    ),
                                ),
                            ),
                        )
                    ),
                    // 整机 - 磁盘
                    array(
                        'name' => "machine_complete_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-recover",
                        'title' => "UI_COMPLETE_TIMED_MACHINE",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "machine_complete_recovery",
                                'path' => "./content/complete_machine_os/machine_os_recover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_MACHINE_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复
                            array(
                                'name' => "machine_complete_instant_recovery",
                                'path' => "./content/platform/recovery/instantaneous.php?recovery_type=os",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "WEB_VM_INSTANT_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_instant_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_MACHINE_INSTANT_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 瞬时恢复快照点
                            /*array(
                                'name' => "machine_complete_instant_recovery_point",
                                'path' => "./content/platform/recovery/points.php?recovery_type=agent",
                                'class' => "viconfont vicon-vminstantrecover",
                                'title' => "UI_INSTANT_POINTS",
                                'level' => 3,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_instant_recovery_point_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                )
                            ),*/
                            // 细粒度恢复
                            array(
                                'name' => "machine_complete_grain_recovery",
                                'path' => "./content/platform/recovery/graininess.php?recovery_type=os",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_GRAIN_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_grain_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_MACHINE_GRAIN_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 跨平台恢复
                            array(
                                'name' => "machine_complete_platform_recovery",
                                'path' => "./content/platform/recovery/platform.php?recovery_type=os",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_platform_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_MACHINE_PLATFORM_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),
                    // 整机 - 卷
                    array(
                        'name' => "osrecover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-recover",
                        'title' => "UI_COMPLETE_TIMED_VOLUME",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "os_recovery",
                                'path' => "./content/os/osrecover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_os_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '16-getTimepointAllNode',
                                            '16-getNodeNetworkList',
                                            '34-getTimepointTree',
                                            '34-getOptionIP',
                                            '34-linkTest',
                                            '34-getTimepointTree',
                                            '34-getOSRecoverTaskName',
                                            '34-createOSRecoverJob',
                                            '5-getTimeCrowdList',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_OS_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),

                    // 2 --- 持续数保护&复制 --- 整机 - 磁盘, 数据库
                    // 整机 - 磁盘
                    array(
                        'name' => "machine_complete_volcdp_recover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-recover",
                        'title' => "UI_COMPLETE_MACHINE_REAL_TIME",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "machine_complete_volcdp_recovery",
                                'path' => "./content/complete_machine_volcdp/cm_volcdp_recovery.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_volcdp_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_MACHINE_CDP_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 细粒度恢复
                            array(
                                'name' => "machine_complete_volcdp_grain_recovery",
                                'path' => "./content/platform/recovery/graininess.php?recovery_type=cdp",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_GRAIN_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_volcdp_grain_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_MACHINE_CDP_GRAIN_OPERATE_DESC',
                                    ),
                                )
                            ),
                            // 跨平台恢复
                            array(
                                'name' => "machine_complete_volcdp_platform_recovery",
                                'path' => "./content/platform/recovery/platform.php?recovery_type=cdp",
                                'class' => "viconfont vicon-vmrecovera",
                                'title' => "UI_PLATFORM_RECOVERY",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_machine_complete_volcdp_platform_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_MACHINE_CDP_PLATFORM_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),
                    // 整机 - 卷
                    array(
                        'name' => "vol_cdp_recovery",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-recover",
                        'title' => "UI_COMPLETE_REAL_TIME_VOLUMES",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "p_vol_cdp_recovery",
                                'path' => "./content/volcdp/vol_cdp_recover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_vol_cdp_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '31-getDataSourceHostInfo',
                                            '31-getRecoveryTargetHost',
                                            '31-getVolTagPointInfo',
                                            '31-getVolCdpRecoverTaskName',
                                            '31-createRecoverJob',
                                            '5-getTimeCrowdList',
                                            '16-getVolCdpTimepointAllNode',
                                            '16-getNodeNetworkList',
                                            '10-getBackupStorageList',
                                            '32-getClientBackupSetInfo',
                                            '33-getAgentBkTimelineData',
                                            '33-verifyTimepointisValid',
                                            '33-getAgentEventInfo',
                                            '2-checkFSEncryptPass',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_CDP_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),
                    // 数据库
                    array(
                        'name' => "dbcdprecover",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-shujukushishi",
                        'title' => "UI_PLATFORM_DATABASE_COPY",
                        'level' => 2,
                        'is_operate' => 1,
                        'showChild' => false,
                        'child' => array(
                            // 恢复
                            array(
                                'name' => "dbcdp_recovery",
                                'path' => "./content/dbcdp/dbcdp_recover.php",
                                'class' => "viconfont vicon-recover",
                                'title' => "UI_PLATFORM_FULL_RECOVER",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_dbcdp_recovery_list",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            'get-dbcdp_restore_instances',
                                            'post-dbcdp_jobs_restore'
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                        'desc' => 'UI_PAGE_RECOVERY_DB_CDP_OPERATE_DESC',
                                    ),
                                )
                            ),
                        )
                    ),
                )
            ),
            // *接管 改为应急容灾
            array(
                'name' => "cdp_takeover",
                'path' => "./content/volcdp/takeover.php", //takeover
                'class' => "viconfont vicon-vol_cdp_takeover",
                'title' => "UI_PLATFORM_TAKEOVER",
                'level' => 1,
                'is_operate' => 1,
                'child' => array(
                    // *接管 磁盘
                    array(
                        'name' => "vol_cdp_complete_takeover",
                        'path' => "./content/complete_machine_volcdp/cm_volcdp_takeover.php", //takeover
                        'class' => "viconfont vicon-vol_cdp_takeover",
                        'title' => "UI_PLATFORM_TAKEOVER_COMPLETE",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_vol_cdp_complete_takeover_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '31-getVolTagPointInfo',
                                    '32-getDataSourceHostInfo',
                                    '32-getClientBackupSetInfo',
                                    '32-getTakeoverTargetHost',
                                    '32-checkIpIsOnline',
                                    '32-getVolCdpTakeoverTaskName',
                                    '32-getClientTakeoverAppInfo',
                                    '32-createTakeoverJob',
                                    '33-verifyTimepointisValid',
                                    '33-getAgentEventInfo',
                                    '33-getAgentBkTimelineData',
                                    '30-updateAgentInfo',
                                    '30-checkIpExists',
                                    '16-getVolCdpTimepointAllNode',
                                    '16-getNodeNetworkList',
                                    '10-getBackupStorageList',
                                    '2-checkFSEncryptPass',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_TAKEOVER_CDP_OPERATE_DESC',
                            ),
                        )
                    ),
                    // *接管  卷
                    array(
                        'name' => "vol_cdp_takeover",
                        'path' => "./content/volcdp/vol_cdp_takeover.php", //takeover
                        'class' => "viconfont vicon-vol_cdp_takeover",
                        'title' => "UI_PLATFORM_TAKEOVER_REEL",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_vol_cdp_takeover_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '31-getVolTagPointInfo',
                                    '32-getDataSourceHostInfo',
                                    '32-getClientBackupSetInfo',
                                    '32-getTakeoverTargetHost',
                                    '32-checkIpIsOnline',
                                    '32-getVolCdpTakeoverTaskName',
                                    '32-getClientTakeoverAppInfo',
                                    '32-createTakeoverJob',
                                    '33-verifyTimepointisValid',
                                    '33-getAgentEventInfo',
                                    '33-getAgentBkTimelineData',
                                    '30-updateAgentInfo',
                                    '30-checkIpExists',
                                    '16-getVolCdpTimepointAllNode',
                                    '16-getNodeNetworkList',
                                    '10-getBackupStorageList',
                                    '2-checkFSEncryptPass',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_TAKEOVER_VOL_CDP_OPERATE_DESC',
                            ),
                        )
                    ),
                )
            ),
            // 验证
            array(
                'name' => "data_verification",
                'path' => "javascript:;",
                'class' => "viconfont vicon-data_verification",
                'title' => "UI_PLATFORM_VERIFICATION",
                'level' => 1,
                'showChild' => true,
                'child' => array(
                    // 数据验证
                    array(
                        'name' => "add_verification_job",
                        'path' => "./content/platform/dataverification/add_verification_job.php",
                        'class' => "viconfont vicon-add_verification_job",
                        'title' => "UI_PLATFORM_DATA_VERIFICATION",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_add_verification_job_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '14-getVerifyType',
                                    '14-getSelectLabList',
                                    '14-getVerifyTaskTree',
                                    '14-getVerifyVmTree',
                                    '14-getVerifyPointTree',
                                    '14-getVerifyTaskName',
                                    '14-createVerifyJob',
                                    '14-getVerifyTimepoint',
                                    '16-getAddStorageNodeSelect',
                                    '16-getNodeIPAddr',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                                'desc' => 'UI_PAGE_VERIFICATION_OPERATE_DESC',
                            ),
                        )
                    ),
                    // 虚拟实验室
                    array(
                        'name' => "virtual_lab_manager",
                        'path' => "./content/platform/dataverification/virtual_lab_manager.php",
                        'class' => "viconfont vicon-virtual_lab_manager",
                        'title' => "UI_PLATFORM_VIRTUAL_LAB",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_virtual_lab_manager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '14-getVirtualLabInfo',
                                    '14-getVirtualLabInfo',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_virtual_lab_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '14-getCreateLabName',
                                    '1-getVirtualLabHost',
                                    '14-getIsolatedAutoNetwork',
                                    '14-getVcenterNetworkList',
                                    '14-getCreateLabName',
                                    '14-createVirtualLab',
                                    '14-labnameAvailable',
                                    '15-getSyncVcenterGroup',
                                    '15-getSyncRecoveryVcenter',
                                    '1-getNetworkAndStorage',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_virtual_lab_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '14-getIsolatedAutoNetwork',
                                    '14-getSyncVcenterGroup',
                                    '14-getSyncRecoveryVcenter',
                                    '14-getVcenterNetworkList',
                                    '14-getCreateLabName',
                                    '14-editVirtualLab',
                                    '14-getLabEditAllInfo',
                                    '1-getNetworkAndStorage',
                                    '1-getVirtualLabHost',

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_virtual_lab_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '14-deleteVirtualLab',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 刷新
                            array(
                                'name' => "p_virtual_lab_refesh",
                                'title' => "UI_PUBLIC_TOOLS_RELOAD",
                                'function' => array(
                                    '14-refreshVirtualLab',
                                ),
                                'level' => 10,
                            ),
                        )
                    ),
                    // 应用组
                    array(
                        'name' => "appgroup",
                        'path' => "./content/platform/dataverification/appgroup.php",
                        'class' => "viconfont vicon-yingyong",
                        'title' => "UI_PLATFORM_LAB_APP_GROUP",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_app_group_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_app_groupb_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_app_group_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_app_group_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
            // *副本 右侧为平铺导航页面，按模块显示，每个模块有副本，副本回传两个选项，注意模块
            array(
                'name' => "copy_protect",
                'path' => "./content/copy/copycenter.php",
                'class' => "viconfont vicon-vmcopy",
                'title' => "UI_PLATFORM_COPY_PROTECT",
                'level' => 1,
                'is_operate' => 1,
                'child' => array()
            ),
            // *归档：右侧为平铺导航页面，按模块显示，每个模块有归档，归档回传两个选项，注意模块类型
            array(
                'name' => "archive_new",
                'path' => "./content/archive/archivecenter.php",
                'class' => "viconfont vicon-data_archive",
                'title' => "UI_PLATFORM_ARCHIVE",
                'level' => 1,
                'is_operate' => 1,
                'child' => array()
            ),
        )
    ),

    // 资源管理
    array(
        'name' => "resmanagement",
        'path' => "javascript:;",
        'class' => "viconfont vicon-resmanagement page-vm-infrastructure_en",
        'title' => "UI_PLATFORM_RESOURCE_MANAGER",
        'level' => 0,
        'child' => array(
            // *基础设施
            array(
                'name' => "infrastructure",
                'path' => "./content/platform/resource/infrastructure.php",
                'class' => "viconfont vicon-vmprotect_infrastructure page-infrastructure_en",
                'title' => "UI_PLATFORM_INFRASTRUCTURE",
                'level' => 1,
                'showChild' => false,
                'child' => array(
                    // 虚拟化中心
                    array(
                        'name' => "vcenter_manager",
                        'path' => "./content/vm/vcenter_manager.php",
                        'class' => "viconfont vicon-vcenter_manager",
                        'title' => "UI_PLATFORM_VCENTER",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_vcenter_manager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '15-getAllHypervisorType',
                                    '15-getVcenters',
                                    '15-getVcenterHostLisenceInfo',
                                    '8-getVMLisenceInfo',
                                    '15-getAllHypervisorType',
                                    '15-getSyncVcenter',
                                    '15-getVcenterDetailVms',
                                    '15-getVcenterDetailsTree',
                                    '15-getVcenterCurrentTask',
                                    '15-addVmToBackupTask',
                                ),
                                'level' => 10,
                            ),
                            // 添加
                            array(
                                'name' => "p_vcenter_manager_add",
                                'title' => "UI_PUBLIC_ADDNEW",
                                'function' => array(
                                    '4-getAuthFunc',
                                    '9-getAgentVersions',
                                    '10-getBackupStorageList',
                                    '15-getAllHypervisorType',
                                    '15-testEngine',
                                    '15-testHighsafeIp',
                                    '15-register',
                                    '15-syncVcenterOne',
                                    '16-getAddStorageNodeSelect',
                                    '16-getApplianceSelect',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_vcenter_manager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '4-getAuthFunc',
                                    '15-testEngine',
                                    '15-testHighsafeIp',
                                    '15-modify',
                                    '15-getAllHypervisorType',
                                    '15-getModifyVcenterInfo',
                                    '16-getAddStorageNodeSelect',
                                    '16-getApplianceSelect',
                                    '10-getBackupStorageList',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_vcenter_manager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '15-deleteVcenter',
                                    '8-deleteHostAuth',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 平台备份
                            array(
                                'name' => "p_vcenter_manager_backup",
                                'title' => "UI_VCENTER_ENGINE_BACKUP",
                                'function' => array(
                                    '15-addVmToBackupTask',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 自动刷新
                            array(
                                'name' => "p_vcenter_manager_refresh",
                                'title' => "UI_VCENTER_AUTO_REFRESH",
                                'function' => array(
                                    '8-refreshVcenterTime',
                                    '8-getrefreshVcenterTime',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 同步
                            array(
                                'name' => "p_vcenter_manager_sync",
                                'title' => "WEB_PLATFORM_DES_SYNC",
                                'function' => array(
                                    '15-syncVcenterOne',
                                    '15-syncVcenterMore',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 授权
                            array(
                                'name' => "p_vcenter_manager_license",
                                'title' => "UI_SETTINGS_AUTH",
                                'function' => array(
                                    '8-addHostAuth',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 分配备份节点
                            array(
                                'name' => "p_vcenter_manager_allocation_node",
                                'title' => "UI_VCENTER_ALLOCATION_NODE",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 虚拟机操作
                            array(
                                'name' => "p_vm_overview_vmoperate",
                                'title' => "WEB_VM_OPERATION",
                                'function' => array(
                                    '15-getEngineCount',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 私有云平台
                    array(
                        'name' => "cloud_platform_private",
                        'path' => "./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=private",
                        'class' => "viconfont vicon-cloud_platform",
                        'title' => "UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_cloud_platform_private_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '15-getAllHypervisorType',
                                    '15-getEngineCount',
                                    '15-getVcenters',
                                    '15-getVcenterHostLisenceInfo',
                                    '8-getrefreshVcenterTime',
                                    '8-getVMLisenceInfo',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_cloud_platform_private_manager_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '15-testEngine',
                                    '15-testHighsafeIp',
                                    '15-register',
                                    '15-syncVcenterOne',
                                    '15-getAllHypervisorType',
                                    '16-getAddStorageNodeSelect',
                                    '16-getApplianceSelect',
                                    '9-getAgentVersions',
                                    '10-getBackupStorageList',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_cloud_platform_private_manager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '15-testEngine',
                                    '15-testHighsafeIp',
                                    '15-modify',
                                    '15-getAllHypervisorType',
                                    '15-getModifyVcenterInfo',
                                    '16-getAddStorageNodeSelect',
                                    '16-getApplianceSelect',
                                    '10-getBackupStorageList',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_cloud_platform_private_manager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '15-deleteVcenter',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 平台备份
                            array(
                                'name' => "p_cloud_platform_private_manager_backup",
                                'title' => "UI_CLOUD_PLATFORM_ENGINE_BACKUP",
                                'function' => array(
                                    '15-getVcenterHostLisenceInfo',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 自动刷新
                            array(
                                'name' => "p_cloud_platform_private_manager_refresh",
                                'title' => "UI_CLOUD_PLATFORM_AUTO_REFRESH",
                                'function' => array(
                                    '8-refreshVcenterTime',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 同步
                            array(
                                'name' => "p_cloud_platform_private_manager_sync",
                                'title' => "WEB_PLATFORM_DES_SYNC",
                                'function' => array(
                                    '15-syncVcenterOne',
                                    '15-syncVcenterMore',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 授权
                            array(
                                'name' => "p_cloud_platform_private_manager_license",
                                'title' => "UI_SETTINGS_AUTH",
                                'function' => array(
                                    '8-addHostAuth',
                                    '8-deleteHostAuth',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 虚拟机操作
                            array(
                                'name' => "p_cloud_platform_private_vm_overview_vmoperate",
                                'title' => "WEB_VM_OPERATION",
                                'function' => array(
                                    '15-getSyncVcenter',
                                    '15-getVcenterDetailsTree',
                                    '15-getVcenterCurrentTask',
                                    '15-getVcenterDetailVms',
                                    '15-addVmToBackupTask',
                                    '15-startMachine',
                                    '15-pauseMachine',
                                    '15-stopMachine',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 公有云平台
                    array(
                        'name' => "cloud_platform",
                        'path' => "./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=public",
                        'class' => "viconfont vicon-yunpingtai",
                        'title' => "UI_PLATFORM_VM_CLOUD_PLATFORM",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_cloud_platform_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '15-getAllHypervisorType',
                                    '15-getEngineCount',
                                    '15-getVcenters',
                                    '15-getVcenterHostLisenceInfo',
                                    '8-getrefreshVcenterTime',
                                    '8-getVMLisenceInfo',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_cloud_platform_manager_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '15-testEngine',
                                    '15-testHighsafeIp',
                                    '15-register',
                                    '15-syncVcenterOne',
                                    '15-getAllHypervisorType',
                                    '16-getAddStorageNodeSelect',
                                    '16-getApplianceSelect',
                                    '9-getAgentVersions',
                                    '10-getBackupStorageList',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_cloud_platform_manager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '15-testEngine',
                                    '15-testHighsafeIp',
                                    '15-modify',
                                    '15-getAllHypervisorType',
                                    '15-getModifyVcenterInfo',
                                    '16-getAddStorageNodeSelect',
                                    '16-getApplianceSelect',
                                    '10-getBackupStorageList',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_cloud_platform_manager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '15-deleteVcenter',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 平台备份
                            array(
                                'name' => "p_cloud_platform_manager_backup",
                                'title' => "UI_CLOUD_PLATFORM_ENGINE_BACKUP",
                                'function' => array(
                                    '15-getVcenterHostLisenceInfo',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 自动刷新
                            array(
                                'name' => "p_cloud_platform_manager_refresh",
                                'title' => "UI_CLOUD_PLATFORM_AUTO_REFRESH",
                                'function' => array(
                                    '8-refreshVcenterTime',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 同步
                            array(
                                'name' => "p_cloud_platform_manager_sync",
                                'title' => "WEB_PLATFORM_DES_SYNC",
                                'function' => array(
                                    '15-syncVcenterOne',
                                    '15-syncVcenterMore',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 授权
                            array(
                                'name' => "p_cloud_platform_manager_license",
                                'title' => "UI_SETTINGS_AUTH",
                                'function' => array(
                                    '8-addHostAuth',
                                    '8-deleteHostAuth',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 虚拟机操作
                            array(
                                'name' => "p_cloud_platform_vm_overview_vmoperate",
                                'title' => "WEB_VM_OPERATION",
                                'function' => array(
                                    '15-getSyncVcenter',
                                    '15-getVcenterDetailsTree',
                                    '15-getVcenterCurrentTask',
                                    '15-getVcenterDetailVms',
                                    '15-addVmToBackupTask',
                                    '15-startMachine',
                                    '15-pauseMachine',
                                    '15-stopMachine',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 代理&客户端
                    array(
                        'name' => "client",
                        'path' => "./content/client/client.php",
                        'class' => "viconfont vicon-client",
                        'title' => "UI_CLIENT_MANAGER",
                        'level' => 2,
                        'child' => array(
                            // 客户端管理
                            array(
                                'name' => "agent_manager",
                                'path' => "./content/client/client_manager.php",
                                'class' => "viconfont vicon-client",
                                'title' => "UI_CLIENT_MANAGERS",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_agent_manager_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '35-getClientInfo',
                                            '35-getGroupClientList',
                                            '35-getClientGroupTree',
                                            '35-getClientNetworkList',
                                            '35-getBatchClientTable',
                                            '35-getClientLisenceInfo',
                                            '35-getDoloadAgentName',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_agent_manager_register",
                                        'title' => "UI_PUBLIC_ADDNEW",
                                        'function' => array(
                                            '35-addClient',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_agent_manager_modify",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            '35-editClient',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_agent_manager_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            '35-deleteClient',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 应用配置
                                    array(
                                        'name' => "p_agent_manager_application_config",
                                        'title' => "UI_CLIENT_APPLICATION_CONFIG",
                                        'function' => array(
                                            '35-refreshClient',
                                            '35-getApplicationConf',
                                            '27-getDBInstance',
                                            '27-AuthDBInstance',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 客户端升级
                                    array(
                                        'name' => 'p_agent_manager_upgrade',
                                        'title' => 'UI_CLIENT_UPGRADE',
                                        'function' => array(
                                            '35-upgradeClient',
                                            '35-getUpgradeClientTable',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 驱动安装
                                    array(
                                        'name' => 'p_agent_manager_driver_install',
                                        'title' => 'UI_CLIENT_DRIVER_INSTALL',
                                        'function' => array(
                                            'get-drivers_agent_install',
                                            'post-drivers_agent_install',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 客户端日志下载
                                    array(
                                        'name' => 'p_agent_log_download',
                                        'title' => 'UI_CLIENT_LOG_DOWNLOAD',
                                        'function' => array(
                                            '35-getClientLogList',
                                            '35-downloadClientLog',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 代理配置
                                    array(
                                        'name' => 'p_agent_manager_agent_config',
                                        'title' => 'UI_CLIENT_AGENT_CONFIG',
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 客户端分组管理
                            array(
                                'name' => "client_group",
                                'path' => "./content/client/client_group.php",
                                'class' => "viconfont vicon-client_groups",
                                'title' => "UI_CLIENT_GROUP_MANAGER",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_client_group_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '35-getGroupClientList',
                                            '35-getClientGroupTree',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_client_group_add",
                                        'title' => "UI_PUBLIC_ADDNEW",
                                        'function' => array(
                                            '35-addClientGroup',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_client_group_modify",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            '35-editClientGroup',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_client_group_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            '35-deleteClientGroup',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 移动到分组
                                    array(
                                        'name' => "p_client_group_add_to_group",
                                        'title' => "UI_CLIENT_GROUP_MOVE_TO",
                                        'function' => array(
                                            '35-moveClientToGroup',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 从分组移除
                                    array(
                                        'name' => "p_client_group_delete_from_group",
                                        'title' => "UI_CLIENT_GROUP_REMOVE_FROM",
                                        'function' => array(
                                            '35-removeClient',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 引导镜像下载
                            array(
                                'name' => "client_mirror",
                                'path' => "./content/client/client_mirror.php",
                                'class' => "viconfont vicon-yindaojingxiang",
                                'title' => "UI_CLIENT_MIRROR",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_client_mirror_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_client_mirror_add",
                                        'title' => "UI_PUBLIC_ADDNEW",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 清理
                                    array(
                                        'name' => "p_client_mirror_modify",
                                        'title' => "UI_CLIENT_MIRROR_CLEAN",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_client_mirror_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 下载
                                    array(
                                        'name' => "p_agent_mirror_download",
                                        'title' => "UI_PUBLIC_DOWNLOAD",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 传输代理资源池
                            array(
                                'name' => 'agent_pool',
                                'path' => './content/client/agent_pool.php',
                                'class' => 'viconfont vicon-client_groups',
                                'title' => 'UI_AGENT_POOL',
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_agent_pool_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            /*'get-agent_pools',*/
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_agent_pool_add",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(
                                            'post-agent_pools',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_agent_pool_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            'put-agent_pools',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_agent_pool_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            'delete-agent_pools',
                                            'delete-agent_pools_batch',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                ),
                            ),
                        )
                    ),
                    // NAS设备
                    array(
                        'name' => "nasmanager",
                        'path' => "./content/nas/nasmanager.php",
                        'class' => "viconfont vicon-nasmanager",
                        'title' => "UI_PLATFORM_NAS_MANAGER",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_nasmanager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '36-getNasInfo',
                                    '36-getLicenseIp',
                                    '36-getAllNodes',
                                ),
                                'level' => 10,
                            ),
                            // 添加
                            array(
                                'name' => "p_nasmanager_add",
                                'title' => "UI_PUBLIC_ADDNEW",
                                'function' => array(
                                    '36-addNasDevice',
                                    '36-checkEditNasParams',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_nasmanager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '36-checkEditNasParams',
                                    '36-editNasDevice',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_nasmanager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '36-delNasDevice',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 授权
                            array(
                                'name' => "p_nasmanager_licence",
                                'title' => "UI_SETTINGS_AUTH",
                                'function' => array(
                                    '36-nasLisence',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 挂载
                            array(
                                'name' => "p_nasmanager_device_mount",
                                'title' => "UI_PUBLIC_DEVICE_MOUNT",
                                'function' => array(
                                    '36-mountNas',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 解挂
                            array(
                                'name' => "p_nasmanager_device_unmount",
                                'title' => "UI_PUBLIC_DEVICE_UNMOUNT",
                                'function' => array(
                                    '36-umountNas',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 对象存储
                    array(
                        'name' => "obsmanager",
                        'path' => "./content/s3/obsmanager.php",
                        'class' => "viconfont vicon-a-Instructionzhiling-01",
                        'title' => "UI_PLATFORM_OBS_MANAGER",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_obsmanager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(

                                ),
                                'level' => 10,
                            ),
                            // 添加
                            array(
                                'name' => "p_obsmanager_add",
                                'title' => "UI_PUBLIC_ADDNEW",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_obsmanager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_obsmanager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '36-delNasDevice',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 授权
                            array(
                                'name' => "p_obsmanager_licence",
                                'title' => "UI_SETTINGS_AUTH",
                                'function' => array(
                                    '36-nasLisence',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 自动刷新
                            [
                                'name' => 'p_obsmanager_refresh',
                                'title' => 'UI_CLOUD_PLATFORM_AUTO_REFRESH',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            //同步
                            array(
                                'name' => 'p_obsmanager_sync',
                                'title' => 'WEB_PLATFORM_DES_SYNC',
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // Kubernetes集群
                    array(
                        'name' => "k8s_cluster",
                        'path' => "./content/kubernetes/kubernetes_cluster.php",
                        'class' => "viconfont vicon-jiqun",
                        'title' => "WEB_K8S_CLUSTER_PROTECT",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_k8s_cluster_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                ),
                                'level' => 10,
                            ),
                            //添加
                            array(
                                'name' => 'p_k8s_cluster_add',
                                'title' => 'UI_PUBLIC_ADDNEW',
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            //修改
                            array(
                                'name' => 'p_k8s_cluster_modify',
                                'title' => 'UI_PUBLIC_MODIFY',
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            //删除
                            array(
                                'name' => 'p_k8s_cluster_delete',
                                'title' => 'UI_PUBLIC_DELETE',
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // Microsoft365组织
                    array(
                        'name' => "exchange_organization",
                        'path' => "./content/exchange/exchange_organization.php",
                        'class' => "viconfont vicon-zuzhi",
                        'title' => "UI_PLATFORM_OFFICE365_ORGANIZATION",
                        'level' => 2,
                        'child' => array(
                            //查看
                            array(
                                'name' => 'p_exchange_organization_list',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => array(
                                    'get-office365_organization',
                                ),
                                'level' => 10,
                            ),
                            //添加
                            array(
                                'name' => 'p_exchange_organization_add',
                                'title' => 'UI_PUBLIC_ADDNEW',
                                'function' => array(
                                    'post-office365_organization',
                                    'get-office365_organization_auth_code',
                                    'get-office365_organization_agent',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            //修改
                            array(
                                'name' => 'p_exchange_organization_modify',
                                'title' => 'UI_PUBLIC_MODIFY',
                                'function' => array(
                                    'put-office365_organization',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            //自动刷新
                            array(
                                'name' => 'p_exchange_organization_refresh',
                                'title' => 'UI_VCENTER_AUTO_REFRESH',
                                'function' => array(
                                    'get-office365_organization_refresh',
                                    'put-office365_organization_refresh',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            //删除
                            array(
                                'name' => 'p_exchange_organization_delete',
                                'title' => 'UI_PUBLIC_DELETE',
                                'function' => array(
                                    'delete-office365_organization',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            //同步
                            array(
                                'name' => 'p_exchange_organization_sync',
                                'title' => 'WEB_PLATFORM_DES_SYNC',
                                'function' => array(
                                    'post-office365_organization_sync'
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // Hadoop集群
                    array(
                        'name' => "hadoop_cluster",
                        'path' => "./content/hadoop/hadoop_cluster.php",
                        'class' => "viconfont vicon-a-Elephantdaxiang-01",
                        'title' => "WEB_HADOOP_CLUSTER_PROTECT",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_hadoopmanager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(),
                                'level' => 10,
                            ),
                            // 添加
                            array(
                                'name' => "p_hadoopmanager_add",
                                'title' => "UI_PUBLIC_ADDNEW",
                                'function' => array(),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_hadoopmanager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_hadoopmanager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 自动刷新
                            [
                                'name' => 'p_hadoopmanager_refresh',
                                'title' => 'UI_CLOUD_PLATFORM_AUTO_REFRESH',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 同步
                            [
                                'name' => 'p_hadoopmanager_sync',
                                'title' => 'WEB_PLATFORM_DES_SYNC',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 授权
                            [
                                'name' => 'p_hadoopmanager_license',
                                'title' => 'UI_SETTINGS_AUTH',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        )
                    ),
                    // 生产存储
                    array(
                        'name' => "production_storage_manager",
                        'path' => "./content/platform/storage/lun_storage_manager.php",
                        'class' => "viconfont vicon-shengchancunchu",
                        'title' => "UI_PRODUCTION_STORAGE_NAME",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_production_storage_manager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '10-getLunStorageList'
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_production_storage_manager_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '10-addLunStorage',
                                    '10-checkInitiator'
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_production_storage_manager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '10-editLunStorage',
                                    '10-checkInitiator',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_production_storage_manager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '10-checkStorageInfo',
                                    '10-deleteStorage',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 同步
                            array(
                                'name' => "p_production_storage_manager_sync",
                                'title' => "WEB_PLATFORM_DES_SYNC",
                                'function' => array(
                                    '10-syncLunStorage',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // Lan-free配置
                    array(
                        'name' => "storage_lanfree",
                        'path' => "./content/platform/storage/storage_lanfree.php",
                        'class' => "viconfont vicon-storage_lanfree",
                        'title' => "UI_PALTFORM_STORAGE_LANFREE",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_storage_lanfree_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '10-getLanfreeStorageInfo',
                                    '10-getStorageNameWithuuid',
                                    '16-getNodeList',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_storage_lanfree_add",
                                'class' => "",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '16-getAddStorageNodeSelect',
                                    '10-addLanfreeStorage',
                                    '10-getLanfreeName',
                                    '10-getWwnNum',
                                    '10-getAddStorageTable',
                                    '10-getIscsiLunTable',
                                    '10-getIscsiName',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_storage_lanfree_edit",
                                'class' => "",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '10-editStorageName',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_storage_lanfree_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '10-deleteStorage',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
            // 存储管理
            array(
                'name' => "storage_manager",
                'path' => "./content/platform/storage/storagedevice.php",
                'class' => "viconfont vicon-storage_manager",
                'title' => "UI_PLATFORM_STORAGE",
                'level' => 1,
                'showChild' => false,
                'child' => array(
                    // 备份存储
                    array(
                        'name' => "manager",
                        'path' => "./content/platform/storage/storage_manager.php",
                        'class' => "viconfont vicon-beifencunchu",
                        'title' => "UI_PLATFORM_STORAGE_BACKUP",
                        'level' => 2,
                        'child' => array(
                            // 存储设备
                            array(
                                'name' => "storage_manager_list",
                                'path' => "./content/platform/storage/storage_manager.php",
                                'class' => "viconfont vicon-beifencunchu",
                                'title' => "UI_PLATFORM_STORAGE_BACKUP",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_storage_manager_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '4-getAuthFunc',
                                            '10-getStorageInfo',
                                            '16-getNodeList',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 新建
                                    array(
                                        'name' => "p_storage_manager_add",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(
                                            '16-getAddStorageNodeSelect',
                                            '10-getBucketFolder',
                                            '10-addNewStorage',
                                            '10-addNasStorage',
                                            '10-addCloudStorage',
                                            '10-addCopyStorage',
                                            '10-addCopyStorageConfirm',
                                            '10-addCloudStorageConfirm',
                                            '10-getCloudRegion',
                                            '10-getStorageName',
                                            '10-getWwnNum',
                                            '10-getAddStorageTable',
                                            '10-getIscsiLunTable',
                                            '10-getIscsiName',
                                            '4-getAuthFunc',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_storage_manager_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            '10-getStorageNameWithuuid',
                                            '10-editStorageName',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_storage_manager_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            '10-checkStorageInfo',
                                            '10-deleteStorage',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 导入数据管理
                                    array(
                                        'name' => "p_storage_manager_data",
                                        'title' => "UI_PALTFORM_STORAGE_DATA",
                                        'function' => array(
                                            '10-getImportDataList',
                                            '10-distributeOldData',
                                            '10-deleteImportData',
                                            '4-getOperatorUsers',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 自动导入时间点配置
                                    /* array(
                                         'name' => "p_storage_manager_timepoint_config",
                                         'title' => "UI_PALTFORM_STORAGE_TIMEPOINT_CONFIG",
                                         'function' => array(
                                         ),
                                         'level' => 10,
                                     ),*/
                                    // 手动同步
                                    array(
                                        'name' => "p_storage_manager_sync",
                                        'title' => "WEB_PLATFORM_DES_SYNC",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                ),
                            ),
                            // 存储资源池
                            array(
                                'name' => "storage_pool",
                                'path' => "./content/platform/storage/storage_pool.php",
                                'class' => "viconfont vicon-cunchuziyuanchi",
                                'title' => "UI_STORAGE_POOL",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_storage_pool_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            /*'get-storage_pools'*/
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_storage_pool_add",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(
                                            'post-storage_pools',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_storage_pool_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            'put-storage_pools',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_storage_pool_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            'delete-storage_pools',
                                            'delete-storage_pools_batch',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                ),
                            ),
                        )
                    ),
                    // 磁带
                    array(
                        'name' => "tape_manage",
                        'path' => "javascript:;",
                        'class' => "viconfont vicon-a-Tapecidai",
                        'title' => "UI_TAPE_MANAGE",
                        'level' => 2,
                        'showChild' => true,
                        'child' => array(
                            //磁带设备
                            array(
                                'name' => "tape_device",
                                'path' => "./content/platform/resource/tape_equipment.php",
                                'class' => "viconfont vicon-a-Tapecidai",
                                'title' => "UI_TAPE_EQUIPMENT",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_tape_device_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                    // 新建
                                    array(
                                        'name' => "p_tape_device_add",
                                        'class' => "",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_tape_device_edit",
                                        'class' => "",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_tape_device_delete",
                                        'class' => "",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 冻结
                                    array(
                                        'name' => "p_tape_device_freeze",
                                        'class' => "",
                                        'title' => "UI_TAPE_FREEZE",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                    // 解冻
                                    array(
                                        'name' => "p_tape_device_defrost",
                                        'class' => "",
                                        'title' => "UI_TAPE_DEFROSTD",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                    // 导入
                                    array(
                                        'name' => "p_tape_device_import",
                                        'class' => "",
                                        'title' => "UI_PUBLIC_IMPORT",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 数据检索
                                    array(
                                        'name' => "p_tape_device_retrieval",
                                        'class' => "",
                                        'title' => "UI_TAPE_CARRIAGE_RETRIEVAL",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 扫描
                                    array(
                                        'name' => "p_tape_device_scan",
                                        'class' => "",
                                        'title' => "UI_TAPE_SCAN",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 弹出
                                    array(
                                        'name' => "p_tape_device_export",
                                        'class' => "",
                                        'title' => "UI_TAPE_CARRIAGE_EXPORT",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 日志
                                    array(
                                        'name' => "p_tape_monitor_list",
                                        'title' => "UI_TAPE_MONITOR",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                ),
                            ),
                            //磁带任务
                            array(
                                'name' => "tape_task",
                                'path' => "./content/platform/resource/tape_jobs.php",
                                'class' => "viconfont vicon-a-Type-drivecidai",
                                'title' => "UI_TAPE_JOB",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_tape_task_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                ),
                            ),
                            // 磁带数据
                            array(
                                'name' => "tape_manage_data",
                                'path' => "./content/platform/resource/tape_data.php",
                                'class' => "viconfont vicon-shujuguanli",
                                'title' => "UI_TAPE_DATA",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_tape_manage_data_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                    // 导出
                                    array(
                                        'name' => "p_tape_manage_data_export",
                                        'title' => "UI_PUBLIC_EXPORT",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                ),
                            ),
                        )
                    ),

                )
            ),
            // 备份资源
            array(
                'name' => "backup_manager",
                'path' => "./content/platform/backup/backupresources.php",
                'class' => "viconfont vicon-cunchu1",
                'title' => "UI_PLATFORM_BACKUP_RESOURCE",
                'level' => 1,
                'showChild' => false,
                'child' => array(
                    // 备份节点
                    array(
                        'name' => 'node',
                        'path' => "./content/platform/node/node.php",
                        'class' => "viconfont vicon-node_manager",
                        'title' => "UI_PALTFORM_NODE",
                        'level' => 2,
                        'child' => array(
                            // 节点管理
                            array(
                                'name' => "node_manager",
                                'path' => "./content/platform/node/node_manager.php",
                                'class' => "viconfont vicon-node_manager",
                                'title' => "UI_PALTFORM_NODE",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_node_manager_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '16-getNodeInfo',
                                            '16-getNodeHostName',
                                            '16-checkAutoDeploy',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_node_manager_add",
                                        'title' => "UI_PUBLIC_ADDNEW",
                                        'function' => array(
                                            '16-addNode'
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_node_manager_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            '16-editNodeHostName',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_node_manager_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            '16-deleteNode',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 网络配置
                                    array(
                                        'name' => "p_node_manager_network",
                                        'title' => "UI_NODE_TRANSFER_NETWORK_CONFIG",
                                        'function' => array(
                                            '16-getNodeNetworkInfo',
                                            '16-addNodeNetwork',
                                            '16-editNodeNetwork',
                                            '16-deleteNodeNetwork',
                                            '16-orderNodeNetwork',
                                            '16-getNodeNetworkList',
                                            /*'get-nodes_network',  // 节点网络*/
                                            'post-nodes_network',
                                            'patch-nodes_network',
                                            'delete-nodes_network',
                                            'post-nodes_network_sort',
                                            'get-nodes_allocation',
                                            'post-nodes_allocation',
                                            /*'get-network_pools',  // 网络资源池*/
                                            'post-network_pools',
                                            'put-network_pools',
                                            'delete-network_pools',
                                            'delete-batchDeleteNetworkPool',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 缓存配置
                                    array(
                                        'name' => "p_node_manager_cache",
                                        'title' => "UI_NODE_CACHE_CONFIG",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 资源限制
                                    array(
                                        'name' => "p_node_manager_resource_limit",
                                        'title' => "UI_NODE_RESOURCE_LIMIT",
                                        'function' => array(
                                            'get-nodes_resources_limit',
                                            'put-nodes_resources_limit',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 计算资源池
                            array(
                                'name' => 'node_pool',
                                'path' => './content/platform/node/node_pool.php',
                                'class' => "viconfont vicon-node_manager",
                                'title' => "UI_PLATFORM_NODE_POOL",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_node_pool_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            /*'get-node_pools',*/
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_node_pool_add",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(
                                            'post-node_pools',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_node_pool_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            'put-node_pools',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_node_pool_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            'delete-node_pools',
                                            'delete-node_pools_batch',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                ),
                            ),
                        ),
                    ),

                    // 集群管理
                    array(
                        'name' => 'cluster_manager',
                        'path' => './content/platform/cluster/cluster_manager.php',
                        'class' => 'viconfont vicon-jiqun1',
                        'title' => 'UI_PLATFORM_CLUSTER_MANAGER',
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_cluster_manager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-cluster_config',  // 获取集群配置信息
                                    'get-getClusterOperateLog',  // 获取集群操作日志
                                ),
                                'level' => 10,
                            ),
                            // 集群配置
                            array(
                                'name' => "p_cluster_manager_config",
                                'title' => "UI_PLATFORM_MANAGEMENT",
                                'function' => array(
                                    'put-cluster_config',  // 配置集群信息
                                    'post-cluster_start',  // 启动集群
                                    'post-cluster_stop',  // 停止集群
                                    'post-setClusterMasterNode', // 设置主节点
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        ),
                    ),
                    // 驱动库管理
                    array(
                        'name' => 'driver_manager',
                        'path' => './content/driver/driver_manager.php',
                        'class' => 'viconfont vicon-a-Hunting-gearcongdongzhuangzhi',
                        'title' => 'WEB_DRIVER_MANAGER',
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => 'p_driver_manager_list',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => array(
                                    'get-drivers',
                                ),
                                'level' => 10,
                            ),
                            // 添加
                            array(
                                'name' => 'p_driver_manager_add',
                                'title' => 'UI_PUBLIC_ADD',
                                'function' => array(
                                    'post-drivers',
                                    'post-drivers_file'
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_driver_manager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    'delete-drivers',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        ),
                    ),

                    // 脚本管理
                    array(
                        'name' => "scripts_manager",
                        'path' => "./content/platform/scripts/scripts_manager.php",
                        'class' => "viconfont vicon-node_manager",
                        'title' => "WEB_k8s_SCRIPT_MANAGE",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_scripts_manager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                ),
                                'level' => 10,
                            ),
                            // 添加
                            array(
                                'name' => "p_scripts_manager_add",
                                'title' => "UI_PUBLIC_ADDNEW",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_scripts_manager_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_scripts_manager_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 备份策略
                    array(
                        'name' => "global_strategy",
                        'path' => "./content/platform/strategy/global_strategy.php",
                        'class' => "viconfont vicon-global_strategy",
                        'title' => "UI_PLATFORM_GLOBAL_STRATEGY",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_global_strategy_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '5-getStrategyList',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_global_strategy_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '5-addGlobalStrategy',
                                    '5-getStrategyName',
                                    '4-getAuthFunc',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_global_strategy_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '5-editGlobalStrategy',
                                    '5-getTaskStrategyList',
                                    '5-editGlobalStrategy',
                                    '5-editGlobalStrategyCheck',
                                    '5-getTaskStrategyList',
                                    '5-initOldStrategy',
                                    '4-getAuthFunc',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_global_strategy_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '5-checkTaskStrategy',
                                    '5-deleteStrategy',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 限速策略
                    array(
                        'name' => "global_speed_strategy",
                        'path' => "./content/platform/global_strategy/global_strategy.php",
                        'class' => "viconfont vicon-xiansucelve",
                        'title' => "UI_PLATFORM_GLOBAL_SPEED_STRATEGY",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_global_speed_strategy_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_global_speed_strategy_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_global_speed_strategy_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_global_speed_strategy_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 分发
                            array(
                                'name' => "p_global_speed_strategy_send",
                                'title' => "UI_GLOBAL_STRATEGY_DISTRIBUTE",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 资源组
                    array(
                        'name' => "resource_group",
                        'path' => "./content/platform/resource/resource_group.php",
                        'class' => "viconfont vicon-resource_group",
                        'title' => "UI_PLATFORM_RESOURCE_GROUP",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_resource_group_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '23-getResourceGroupLists',
                                    '23-getResourceGroupOldInfo',
                                    '4-getResourceGroupUser',
                                    '4-getResourceGroupUserGroup',
                                    'get-resources_group',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_resource_group_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '23-resourceGroupAvailable',
                                    '23-addResourceGroup',
                                    'post-resources_group_add',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_resource_group_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '23-editResourceGroup',
                                    'get-resources_groupDetail',
                                    'post-resources_group',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_resource_group_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '23-deleteResourceGroup',
                                    'delete-resources_group',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 病毒库管理
                    [
                        'name' => 'virus',
                        'path' => './content/virus/virus.php',
                        'class' => 'viconfont vicon-Frame1',
                        'title' => 'WEB_PLATFORM_VIRUS_MANAGE',
                        'level' => 2,
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_virus_list',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => [],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_virus_operate",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                )
            ),
            // 容灾演练平台
            array(
                'name' => "vm_machine_manager",
                'path' => "./content/platform/vm_machine/vm_manager.php",
                'class' => "viconfont vicon-ge_disaster_recovery",
                'title' => "UI_VM_MACHINE_MANAGER",
                'level' => 1,
                'showChild' => false,
                'child' => array(
                    // 概览
                    array(
                        'name' => "vm_machine_network",
                        'path' => "./content/platform/vm_machine/vm_network.php",
                        'class' => "viconfont vicon-vm_overview",
                        'title' => "UI_VM_MACHINE_NETWORK",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_vm_machine_network_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-virtual_network',
                                    'get-virtual',
                                ),
                                'level' => 10,
                            ),
                            // 添加
                            array(
                                'name' => "p_vm_machine_network_add",
                                'title' => "UI_PUBLIC_ADDNEW",
                                'function' => array(
                                    'post-virtual_network'
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            /*// 修改
                            array(
                                'name' => "p_vm_machine_network_modify",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    'post-virtual_network'
                                ),
                                'level' => 10,
                            ),*/
                            // 删除
                            array(
                                'name' => "p_vm_machine_network_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    'delete-virtual_network'
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 资源隔离
                            array(
                                'name' => "p_vm_machine_partition",
                                'title' => "UI_VM_MACHINE_PARTITION",
                                'function' => array(
                                    'get-virtual_resources',
                                    'post-virtual_resources',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 主机列表
                    array(
                        'name' => "vm_machine_list",
                        'path' => "./content/platform/vm_machine/vm_list.php",
                        'class' => "viconfont vicon-client",
                        'title' => "UI_VM_MACHINE_LIST",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_vm_machine_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-virtual',
                                ),
                                'level' => 10,
                            ),
                            // 修改
                            array(
                                'name' => "p_vm_machine_modify",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    'post-virtual',
                                    'get-virtual_tree',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_vm_machine_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    'delete-virtual',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 开机
                            array(
                                'name' => "p_vm_machine_on",
                                'title' => "WEB_VM_POWER_ON",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 关机
                            array(
                                'name' => "p_vm_machine_off",
                                'title' => "WEB_VM_POWER_OFF",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 重启
                            array(
                                'name' => "p_vm_machine_restart",
                                'title' => "UI_VM_MACHINE_POWER_RESTART",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 代理网关
                    array(
                        'name' => "vm_machine_proxy_gateway",
                        'path' => "./content/platform/vm_machine/proxy_gateway.php",
                        'class' => "viconfont vicon-client_groups",
                        'title' => "UI_DRILLS_AGENT_GATEWAY",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "vm_machine_proxy_gateway_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-virtual_proxy'
                                ),
                                'level' => 10,
                            ),
                            // 开机
                            array(
                                'name' => "p_vm_machine_proxy_gateway_on",
                                'title' => "WEB_VM_POWER_ON",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 关机
                            array(
                                'name' => "p_vm_machine_proxy_gateway_off",
                                'title' => "WEB_VM_POWER_OFF",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 重启
                            array(
                                'name' => "p_vm_machine_proxy_gateway_restart",
                                'title' => "UI_VM_MACHINE_POWER_RESTART",
                                'function' => array(
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 操作日志
                    array(
                        'name' => "vm_machine_operate_log",
                        'path' => "./content/platform/vm_machine/vm_log.php",
                        'class' => "viconfont vicon-client_groups",
                        'title' => "UI_VM_MACHINE_LOG",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "vm_machine_operate_log_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-virtual_logs'
                                ),
                                'level' => 10,
                            ),
                        )
                    ),
                )
            ),
        )
    ),

    //系统管理
    array(
        'name' => "sysmanagement",
        'path' => "javascript:;",
        'class' => "viconfont vicon-sysmanagement",
        'title' => "UI_PLATFORM_SYSTEM_MANAGER",
        'level' => 0,
        'child' => array(
            // 系统设置
            array(
                'name' => "setting_manager",
                'path' => "./content/platform/settings/setting_manager.php",
                'class' => "viconfont vicon-setting_manager",
                'title' => "UI_PLATFORM_SYSTEM_SET",
                'level' => 1,
                'child' => array(
                    // 网络配置
                    array(
                        'name' => "system_network",
                        'title' => "UI_PLATFORM_NETWORK_SETTING",
                        'class' => "iconfont icon-ipaddress",
                        'level' => 2,
                        'child' => array(
                            // IP地址
                            array(
                                'name' => "set_ip",
                                'title' => "UI_PUBLIC_IP_ADDRESS",
                                'class' => "iconfont icon-ipaddr",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_set_ip_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '8-getNetworkCardInfo',
                                            '8-getNicOldInfo',
                                            '8-getNetworkCardList',
                                            '8-getNodeDnsHosts',
                                            '16-getAddStorageNodeSelect',
                                            'get-system_network_info',
                                            'get-system_network_list',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_setting_manager_ip",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            'post-system_network_info',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 域名解析
                            array(
                                'name' => "system_dns",
                                'title' => "UI_PLATFORM_SYSTEM_DNS",
                                'class' => "viconfont vicon-pt_setting_dns",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_system_dns_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '16-getAddStorageNodeSelect',
                                            '8-getNodeDnsHosts',
                                            'get-system_network_host',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_system_dns_edit",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            'post-system_network_host',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 网卡聚合
                            array(
                                'name' => "nic_teaming",
                                'title' => "UI_PLATFORM_NIC_TEAMING",
                                'class' => "iconfont icon-wangkajihe",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_nic_teaming_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '8-getNetworkCardList',
                                            '8-getNicOldInfo',
                                            '16-getAddStorageNodeSelect',
                                            'get-system_network_nic',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_nic_teaming_edit",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '8-addNicTeaming',
                                            '8-cleanNicInfo',
                                            'post-system_network_nic',
                                            'delete-system_network_nic',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 网卡桥接
                            array(
                                'name' => "card_bridge",
                                'title' => "UI_PLATFORM_CARD_BRIDEG",
                                'class' => "iconfont icon-card_bridge",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_card_bridge_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            'get-system_network_bridge'
                                        ),
                                        'level' => 10,
                                    ),
                                    // 添加
                                    array(
                                        'name' => "p_card_bridge_add",
                                        'title' => "UI_PUBLIC_ADDNEW",
                                        'function' => array(
                                            'post-system_network_bridge'
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_card_bridge_delte",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            'delete-system_network_bridge'
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 隔离网段
                                    array(
                                        'name' => "p_card_bridge_isolate_network",
                                        'title' => "UI_PLATFORM_CARD_BRIDEG_ISOLATE",
                                        'function' => array(
                                            'get-system_network_isolate',
                                            'post-system_network_isolate'
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            )
                        )
                    ),
                    // 时间配置
                    array(
                        'name' => "set_time",
                        'title' => "UI_PLATFORM_SET_TIME",
                        'class' => "iconfont icon-shezhishijian",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_setting_manager_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    'get-system_times',
                                    'get-system_times_info',
                                ),
                                'level' => 10,
                            ),
                            // 操作
                            array(
                                'name' => "p_setting_manager_time",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    'post-system_times_info',
                                    'post-system_times_ntp',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 系统通知
                    array(
                        'name' => "system_notice",
                        'title' => "UI_PLATFORM_SYSTEM_NOTICE",
                        'class' => "iconfont icon-xitongtongzhi",
                        'level' => 2,
                        'child' => array(
                            // 邮件通知
                            array(
                                'name' => "emial_notice",
                                'title' => "UI_SETTINGS_NOTICE_EMAIL",
                                'class' => "icon-envelope-open",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "emial_notice_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_emial_notice",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 短信通知
                            array(
                                'name' => "sms_notice",
                                'title' => "UI_SETTINGS_NOTICE_SMS",
                                'class' => "viconfont vicon-pt_setting_short_note",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_sms_notice_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_sms_notice",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 微信通知
                            array(
                                'name' => "wechat_notice",
                                'title' => "UI_SETTINGS_NOTICE_WECHAT",
                                'class' => "viconfont vicon-pt_setting_wechat_notice",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_wechat_notice_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_wechat_notice",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 企业微信通知
                            array(
                                'name' => "enterprise_wechat_notice",
                                'title' => "UI_SETTINGS_NOTICE_WECHAT_INTERNET2",
                                'class' => "viconfont vicon-pt_setting_wechat2_notice",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_enterprise_wechat_notice_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_enterprise_wechat_notice",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                        )
                    ),
                    // 安全配置
                    array(
                        'name' => "system_safe",
                        'title' => "UI_PLATFORM_SAFE_SETTING",
                        'class' => "iconfont icon-anquanpeizhi",
                        'level' => 2,
                        'child' => array(
                            // 账户安全
                            array(
                                'name' => "account_safe",
                                'title' => "UI_PLATFORM_ACCOUNT_SAFE",
                                'class' => "iconfont icon-accountsafe",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_account_safe_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_account_safe",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 存储安全
                            array(
                                'name' => "storage_safe",
                                'title' => "UI_PLATFORM_STORAGE_SAFE",
                                'class' => "iconfont icon-storagesafe",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_storage_safe",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 系统安全
                            array(
                                'name' => "os_safe",
                                'title' => "UI_PLATFORM_OS_SAFE",
                                'class' => "iconfont icon-ossafe",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_os_safe_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_os_safe",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 数据安全
                            [
                                'name' => 'data_safe',
                                'title' => 'UI_PLATFORM_DATA_SAFE',
                                'class' => 'iconfont icon-ossafe',
                                'level' => 3,
                                'child' => [
                                    // 查看
                                    [
                                        'name' => 'p_data_safe_look',
                                        'title' => 'UI_PUBLIC_LOOK',
                                        'function' => [
                                        ],
                                        'level' => 10,
                                    ],
                                    // 操作
                                    [
                                        'name' => 'p_data_safe_operate',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => [
                                        ],
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ],
                                ]
                            ],
                        )
                    ),
                    // 关机/重启
                    array(
                        'name' => "system_poweroff",
                        'title' => "UI_PLATFORM_POWER",
                        'class' => "iconfont icon-offrestart",
                        'level' => 2,
                        'is_operate' => 1,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_setting_manager_power",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '16-getAddStorageNodeSelect',
                                    '16-getNodeRunningTaskList',
                                    '8-doRebootBackupNode',
                                    '8-doPoweroffBackupNode',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 系统升级
                    array(
                        'name' => "system_upgrade",
                        'title' => "UI_PLATFORM_SYSTEM_UPDATE",
                        'class' => "iconfont icon-upgrade",
                        'level' => 2,
                        'child' => array(
                            // 升级包管理
                            array(
                                'name' => "upgrade_manage",
                                'title' => "UI_SETTINGS_UPDATE_MANAGE",
                                'class' => "viconfont vicon-pt_setting_package",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_upgrade_manage_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '8-getDefaultNoticeConf',
                                            '8-setUpdateConf',
                                            '8-getStorageProtect',
                                            '8-getSelectPatch',
                                            '8-getPatches',
                                            '8-checkMasterUpdate',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 上传升级包
                                    array(
                                        'name' => "p_setting_manager_upload",
                                        'title' => "UI_SETTINGS_UPLOAD_UPGRADE_PATCH",
                                        'function' => array(
                                            '8-updatePatchList',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除升级包
                                    array(
                                        'name' => "p_setting_manager_delete",
                                        'title' => "UI_SETTINGS_DELETE_UPGRADE_PATCH",
                                        'function' => array(
                                            '8-deletePatch',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 系统升级
                                    array(
                                        'name' => "p_setting_manager_upgrade",
                                        'title' => "UI_PLATFORM_SYSTEM_UPDATE",
                                        'function' => array(
                                            '8-upgradeCheck',
                                            '8-upgradeSystem',
                                            '8-upgradeChildNode',
                                            '8-getUpgradeInfo',
                                            '8-checkIsUpgrading',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 升级历史
                            array(
                                'name' => "upgrade_history",
                                'title' => "UI_SETTINGS_UPDATE_HISTORY",
                                'class' => "viconfont vicon-pt_setting_history",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_upgrade_history_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '8-getPatchHistory',
                                            '8-getUpdateNodeList',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 删除失败日志
                                    array(
                                        'name' => "p_upgrade_history_delete",
                                        'title' => "UI_SETTINGS_UPDATE_DELETE_ERROR_HISTORY",
                                        'function' => array(
                                            '8-deletePatchHistory',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 检测更新
                            /*array(
                                'name' => "upgrade_check",
                                'title' => "WEB_SETTINGS_UPDATE_CHECK_ONLINE",
                                'class' => "icon-speech",
                                'level' => 3,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_upgrade_check_setting",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'level' => 10,
                                    ),
                                )
                            ),*/                            // 检测更新 先隐藏
                        )
                    ),
                    // 消息推送
                    array(
                        'name' => "message_push",
                        'title' => "UI_PLATFORM_MESSAGE_PUSH",
                        'class' => "iconfont icon-xiaoxituisong",
                        'level' => 2,
                        'child' => array(
                            // 第三方推送
                            array(
                                'name' => "message_push_third",
                                'title' => "UI_PLATFORM_THIRD_MESSAGE_PUSH",
                                'class' => "viconfont vicon-disanfangxiaoxituisong",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_message_push_third_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                    ),
                                    // 新建
                                    array(
                                        'name' => "p_message_push_third_add",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_message_push_third_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_message_push_third_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 启用
                                    array(
                                        'name' => "p_message_push_third_enable",
                                        'title' => "WEB_PLATFORM_ENABLE",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 禁用
                                    array(
                                        'name' => "p_message_push_third_disable",
                                        'title' => "WEB_PLATFORM_DISABLE",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 监控平台配置
                                    array(
                                        'name' => "p_message_push_third_config",
                                        'title' => "UI_PLATFORM_THIRD_MONITOR_CONFIG",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 消息推送
                            array(
                                'name' => "message_push_old",
                                'title' => "UI_PLATFORM_MESSAGE_PUSH",
                                'class' => "viconfont vicon-xiaoxituisong",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_message_push_old_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                    ),
                                    array(
                                        'name' => "p_message_push_old_operate",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                        )
                    ),
                    // 可视化配置
                    array(
                        'name' => "visual_config",
                        'title' => "UI_PUBLIC_VISUAL_CONFIG",
                        'class' => "iconfont icon-visual",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_setting_manager_visualization_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(

                                ),
                                'level' => 10,
                            ),
                            // 操作
                            array(
                                'name' => "p_setting_manager_visualization",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '8-setVisualInfo',
                                    '8-getDefaultVisualInfo',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 系统工具
                    array(
                        'name' => "system_service",
                        'title' => "UI_SETTINGS_SYSTEM_TOOL",
                        'class' => "iconfont icon-xitonggongju",
                        'level' => 2,
                        'child' => array(
                            // 服务管理
                            array(
                                'name' => "service_manage",
                                'title' => "UI_SETTINGS_SERVICE_MANAGE",
                                'class' => "viconfont vicon-pt_setting_service_management",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_setting_manager_tools",
                                        'class' => "",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            '8-getServiceList',
                                            '8-empty_filetmp',
                                            '8-startService',
                                            '8-stopService',
                                            '8-resetService',
                                            '16-getAddStorageNodeSelect',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 操作
                                    array(
                                        'name' => "p_setting_manager_operate",
                                        'class' => "",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(

                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 网络工具
                            array(
                                'name' => "network_tool",
                                'title' => "UI_SETTINGS_NETWORK_TOOL",
                                'class' => "viconfont vicon-pt_setting_service_network",
                                'level' => 3,
                                'child' => array(
                                    // 测试
                                    array(
                                        'name' => "p_network_tool_test",
                                        'class' => "",
                                        'title' => "UI_SETTINGS_NETWORK_TOOL_TEST",
                                        'function' => array(
                                            '8-getServiceList',
                                            '8-testConnectTool',
                                        ),
                                        'level' => 10,
                                    ),
                                )
                            ),
                            // 远程控制
                            array(
                                'name' => "remote_control",
                                'title' => "UI_PLATFORM_REMOTE_CONTROL",
                                'class' => "iconfont icon-yuanchengkongzhi",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_remote_control_list",
                                        'class' => "",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '8-getServiceList',
                                            '8-UploadToSystem',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                        )
                    ),
                    // 系统备份/恢复
                    array(
                        'name' => "system_br",
                        'title' => "UI_PLATFORM_SYSTEM_BAK_REC",
                        'class' => "iconfont icon-sysbakrec",
                        'level' => 2,
                        'child' => array(
                            // 手动备份
                            array(
                                'name' => "rc_oncebak",
                                'title' => "UI_PLATFORM_RC_ONCEBAK",
                                'class' => "iconfont icon-opbak",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_rc_oncebak",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '28-doOnceBackupSystem',
                                            '28-getBackupTree',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 自动备份
                            array(
                                'name' => "rc_autobak",
                                'title' => "UI_PLATFORM_RC_AUTOBAK",
                                'class' => "iconfont icon-autobak",
                                'level' => 3,
                                'child' => array(
                                    // 备份设置
                                    array(
                                        'name' => "rc_autobak_setting",
                                        'title' => "UI_PLATFORM_RC_AUTOBAK_SETTING",
                                        'class' => "iconfont icon-baksetting",
                                        'level' => 4,
                                        'is_operate' => 1,
                                        'child' => array(
                                            // 操作
                                            array(
                                                'name' => "p_rc_autobak_setting",
                                                'title' => "UI_PUBLIC_OPERATION",
                                                'function' => array(
                                                    '28-setAutoBakConfig',
                                                    '28-getAutoBakConfig',
                                                    '28-getBackupTree',
                                                    '16-getAddStorageNodeSelect',
                                                    '10-getBackupStorageList',
                                                ),
                                                'level' => 10,
                                                'is_operate' => 1,
                                            ),
                                        )
                                    ),
                                    // 备份点管理
                                    array(
                                        'name' => "rc_autobak_list",
                                        'title' => "UI_PLATFORM_RC_AUTOBAK_LIST",
                                        'class' => "iconfont icon-sysbakpoint",
                                        'level' => 4,
                                        'child' => array(
                                            // 查看
                                            array(
                                                'name' => "p_rc_autobak_list_list",
                                                'title' => "UI_PUBLIC_LOOK",
                                                'function' => array(
                                                    '28-getAutoBakDataList',
                                                ),
                                                'level' => 10,
                                            ),
                                            // 下载备份点
                                            array(
                                                'name' => "p_rc_autobak_list_download",
                                                'title' => "UI_PLATFORM_RC_AUTOBAK_POINT_DOWNLAOD",
                                                'function' => array(
                                                    '28-downloadAutoBakDataCheck',
                                                ),
                                                'level' => 10,
                                                'is_operate' => 1,
                                            ),
                                            // 删除
                                            array(
                                                'name' => "p_rc_autobak_list_delete",
                                                'title' => "UI_PUBLIC_DELETE",
                                                'function' => array(
                                                    '28-deleteAutoBakData',
                                                ),
                                                'level' => 10,
                                                'is_operate' => 1,
                                            ),
                                        )
                                    ),
                                )
                            ),
                            // 系统恢复
                            array(
                                'name' => "rc_recovery",
                                'title' => "UI_PLATFORM_RC_RECOVERY",
                                'class' => "iconfont icon-sysrecovery",
                                'level' => 3,
                                'is_operate' => 1,
                                'child' => array(
                                    // 操作
                                    array(
                                        'name' => "p_rc_recovery",
                                        'title' => "UI_PUBLIC_OPERATION",
                                        'function' => array(
                                            '28-checkRecData',
                                            '28-startRecoverySystemNow',
                                            '28-getRecoveryInfo',
                                            '28-getAutoBakDataListForRec',
                                            '28-getAutoBakSrcContent',
                                            '28-getUploadSrcContent',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                        )
                    ),
                    // 容灾演练平台
                    array(
                        'name' => "exercise_platform",
                        'title' => "UI_EXERCISE_PLATFORM",
                        'class' => "viconfont vicon-ge_disaster_recovery",
                        'level' => 2,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "p_exercise_platform_list",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(
                                    '8-getDefaultRecoveryInfo',
                                    '8-setRecoveryInfo',
                                ),
                                'level' => 10,
                            ),
                        )
                    ),
                    // 黑白名单
                    array(
                        'name' => "black_white_list",
                        'title' => "UI_PLATFORM_BLACKLIST_WHITELIST",
                        'class' => "viconfont vicon-heibaimingdan",
                        'level' => 2,
                        'child' => array(
                            // 操作
                            array(
                                'name' => "black_list",
                                'title' => "UI_PLATFORM_BLACKLIST",
                                'class' => "viconfont vicon-a-Wrong-usercuowuyonghu",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_black_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            'get-system_wblist',
                                            'get-system_wblist_compare',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 新建
                                    array(
                                        'name' => "p_black_add",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(
                                            'post-system_wblist',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_black_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            'put-system_wblist',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_black_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            'delete-system_wblist',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 启用
                                    array(
                                        'name' => "p_black_enable",
                                        'title' => "WEB_PLATFORM_ENABLE",
                                        'function' => array(
                                            'put-system_wblist_lock',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 禁用
                                    array(
                                        'name' => "p_black_disable",
                                        'title' => "WEB_PLATFORM_DISABLE",
                                        'function' => array(
                                            'put-system_wblist_unlock',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                            // 白名单
                            array(
                                'name' => "white_list",
                                'title' => "UI_PLATFORM_WHITELIST",
                                'class' => "viconfont vicon-a-Right-userzhengqueyonghu",
                                'level' => 3,
                                'child' => array(
                                    // 查看
                                    array(
                                        'name' => "p_white_list",
                                        'title' => "UI_PUBLIC_LOOK",
                                        'function' => array(
                                            'get-system_wblist',
                                            'get-system_wblist_compare',
                                        ),
                                        'level' => 10,
                                    ),
                                    // 新建
                                    array(
                                        'name' => "p_white_add",
                                        'title' => "UI_PUBLIC_ADD",
                                        'function' => array(
                                            'post-system_wblist',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 修改
                                    array(
                                        'name' => "p_white_edit",
                                        'title' => "UI_PUBLIC_MODIFY",
                                        'function' => array(
                                            'put-system_wblist',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 删除
                                    array(
                                        'name' => "p_white_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => array(
                                            'delete-system_wblist',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 启用
                                    array(
                                        'name' => "p_white_enable",
                                        'title' => "WEB_PLATFORM_ENABLE",
                                        'function' => array(
                                            'put-system_wblist_lock',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                    // 禁用
                                    array(
                                        'name' => "p_white_disable",
                                        'title' => "WEB_PLATFORM_DISABLE",
                                        'function' => array(
                                            'put-system_wblist_unlock',
                                        ),
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ),
                                )
                            ),
                        )
                    ),
                    //apikey
                    array(
                        'name' => "api_key",
                        'title' => "UI_API_KEY",
                        'class' => "viconfont vicon-apikey",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_api_key_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(

                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_api_key_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_api_key_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 启用
                            array(
                                'name' => "p_api_key_enable",
                                'title' => "WEB_PLATFORM_ENABLE",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 禁用
                            array(
                                'name' => "p_api_key_disable",
                                'title' => "WEB_PLATFORM_DISABLE",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    //能耗监控平台
                    array(
                        'name' => "carbon_monitor_platform",
                        'title' => "UI_SETTINGS_CARBON_MONITOR_PLATFORM",
                        'class' => "viconfont vicon-carbon-platform",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_carbon_monitor_platform_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                ),
                                'level' => 10,
                            ),
                            // 操作
                            array(
                                'name' => "p_carbon_monitor_platform_operate",
                                'title' => "UI_PUBLIC_OPERATION",
                                'function' => array(

                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
            // 用户管理
            array(
                'name' => "safety",
                'path' => "./content/platform/users/safety_manager.php",
                'class' => "viconfont vicon-safety",
                'title' => "UI_PLATFORM_SAFETY",
                'level' => 1,
                'child' => array(
                    // 用户
                    array(
                        'name' => "safety_user",
                        'path' => "./content/platform/users/users.php",
                        'class' => "viconfont vicon-pt_setting_user",
                        'title' => "UI_PLATFORM_SAFETY_USER",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_safety_user_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '4-getUserPermissionTree',
                                    '4-getUserUserGroup',
                                    '4-getUserRole',
                                    '4-getUserResourceGroup',
                                    '4-getUsersInfo',
                                    '4-addUserFileHost',
                                    '4-deleteUserFileHost',
                                    '4-addUserDbHost',
                                    '4-deleteUserDbHost',
                                    '4-addUserCDPHost',
                                    '4-deleteUserCDPHost',
                                    '4-addUserVM',
                                    '4-deleteUserVM',
                                    '4-addUserAppliance',
                                    '4-deleteUserAppliance',
                                    '4-addUserNode',
                                    '4-deleteUserNode',
                                    '4-addUserStorage',
                                    '4-deleteUserStorage',
                                    '4-addUserResourceGroup',
                                    '4-deleteUserResourceGroup',
                                    '15-getAllHypervisorType',
                                    '23-*',
                                    'get-users',
                                    'get-users_roles',
                                    'get-users_auth',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_safety_user_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '4-addUser',
                                    '4-usernameAvailable',
                                    '4-getAllTenant',
                                    '4-getRoleList',
                                    '4-getUsergroupList',
                                    '10-getMaxStorage',
                                    '24-getDomainUsers',
                                    '24-getDomainSelectList',
                                    'post-users',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_safety_user_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '4-editUser',
                                    '4-usernameAvailable',
                                    '4-getEditUserInfo',
                                    '4-getAllTenant',
                                    '4-getRoleList',
                                    '4-getUsergroupList',
                                    '10-getMaxStorage',
                                    '24-getDomainSelectList',
                                    'put-users',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_safety_user_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '4-deleteUser',
                                    'delete-users',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 启用
                            array(
                                'name' => "p_safety_user_enable",
                                'title' => "WEB_PLATFORM_ENABLE",
                                'function' => array(
                                    '4-unlockUser',
                                    'post-users_unlock',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 禁用
                            array(
                                'name' => "p_safety_user_disable",
                                'title' => "WEB_PLATFORM_DISABLE",
                                'function' => array(
                                    '4-lockUser',
                                    'post-users_lock',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 资源分配
                            array(
                                'name' => "p_safety_user_allocation_resource",
                                'title' => "UI_RESOURCE_GROUP_ALLOCATION_RESOURCE",
                                'function' => array(
                                    'post-users_allocation',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 解绑资源
                            array(
                                'name' => "p_safety_user_unbind_allocation_resource",
                                'title' => "UI_RESOURCE_GROUP_UNBIND_ALLOCATION_RESOURCE",
                                'function' => array(
                                    'delete-users_allocation',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 资源转移
                            array(
                                'name' => "p_safety_user_storage_transfer",
                                'title' => "UI_STORAGE_TRANSFER",
                                'function' => array(
                                    'post-users_transfer',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 分配管理用户
                            array(
                                'name' => "p_safety_user_manager_allocation",
                                'title' => "UI_USER_MANAGER_ALLOCATION",
                                'function' => array(
                                    'get-users_manager',
                                    'post-users_manager',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 分配角色
                            array(
                                'name' => "p_safety_user_role_allot",
                                'title' => "UI_PLATFORM_SAFETY_ROLE_ALLOT",
                                'function' => array(
                                    'post-users_roles',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 用户组
                    array(
                        'name' => "safety_usergroup",
                        'path' => "./content/platform/users/users_group.php",
                        'class' => "iconfont icon-usergroup",
                        'title' => "UI_PLATFORM_SAFETY_USER_GROUP",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_safety_usergroup_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '4-getUserGroupPermissionTree',
                                    '4-getUserGroupUser',
                                    '4-getUserGroupRole',
                                    '4-getUserGroupResourceGroup',
                                    '4-get_all_user_group',
                                    '4-addUserGroupFileHost',
                                    '4-deleteUserGroupFileHost',
                                    '4-addUserGroupDbHost',
                                    '4-deleteUserGroupDbHost',
                                    '4-addUserGroupCDPHost',
                                    '4-deleteUserGroupCDPHost',
                                    '4-addUserGroupVM',
                                    '4-deleteUserGroupVM',
                                    '4-addUserGroupAppliance',
                                    '4-deleteUserGroupAppliance',
                                    '4-addUserGroupNode',
                                    '4-deleteUserGroupNode',
                                    '4-addUserGroupStorage',
                                    '4-deleteUserGroupStorage',
                                    '4-addUserGroupResourceGroup',
                                    '4-deleteUserGroupResourceGroup',
                                    '15-getAllHypervisorType',
                                    '23-*',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_safety_usergroup_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '4-getAllTenant',
                                    '4-getUserList',
                                    '4-getRoleList',
                                    '4-addUserGroup',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_safety_usergroup_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '4-getAllTenant',
                                    '4-get_one_user_group_data',
                                    '4-getRoleList',
                                    '4-getUserList',
                                    '4-editUserGroup',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_safety_usergroup_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '4-deleteUserGroup',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 启用
                            array(
                                'name' => "p_safety_usergroup_enable",
                                'title' => "WEB_PLATFORM_ENABLE",
                                'function' => array(
                                    '4-unlock_usergroup',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 禁用
                            array(
                                'name' => "p_safety_usergroup_disable",
                                'title' => "WEB_PLATFORM_DISABLE",
                                'function' => array(
                                    '4-lock_usergroup',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                    // 角色
                    array(
                        'name' => "safety_role",
                        'path' => "./content/platform/users/role.php",
                        'class' => "iconfont icon-jiaose",
                        'title' => "UI_PLATFORM_SAFETY_ROLE",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_safety_role_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '22-getRoleLists',
                                    '22-getRolePermissionTree',
                                    '22-getRoleUser',
                                    '22-getRoleUserGroup',
                                    '22-getRoleLists',
                                    '4-initAllocationList',
                                    '4-getUserList',
                                    '4-getUsergroupList',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_safety_role_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '4-addRole',
                                    '22-addRole',
                                    '4-getUserPermission',
                                    '4-getAllTenant',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_safety_role_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '22-editRole',
                                    '4-getUserPermission',
                                    '4-getAllTenant',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_safety_role_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '22-deleteRole',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 启用
                            array(
                                'name' => "p_safety_role_enable",
                                'title' => "WEB_PLATFORM_ENABLE",
                                'function' => array(
                                    '22-unlockRole',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 禁用
                            array(
                                'name' => "p_safety_role_disable",
                                'title' => "WEB_PLATFORM_DISABLE",
                                'function' => array(
                                    '22-lockRole',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 分配角色
                            array(
                                'name' => "p_safety_role_allot",
                                'title' => "UI_ROLE_ALLOC_USER",
                                'function' => array(
                                    '4-initAllocationList',
                                    '4-getUserList',
                                    '4-getUsergroupList',
                                    '4-addUserRoleAllocation',
                                    '4-addUsergroupRoleAllocation',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            )
                        )
                    ),
                    // 域服务器
                    array(
                        'name' => "safety_domain",
                        'path' => "./content/platform/users/domain_server.php",
                        'class' => "iconfont icon-domain",
                        'title' => "UI_PLATFORM_SAFETY_DOMAIN",
                        'level' => 2,
                        'child' => array(
                            // 查看
                            array(
                                'name' => "p_safety_domain_list",
                                'title' => "UI_PUBLIC_LOOK",
                                'function' => array(
                                    '24-getDomainServerInfo',
                                ),
                                'level' => 10,
                            ),
                            // 新建
                            array(
                                'name' => "p_safety_domain_add",
                                'title' => "UI_PUBLIC_ADD",
                                'function' => array(
                                    '24-addDomainServer',
                                    '24-domainAvailable',
                                    '4-getAllTenant',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 修改
                            array(
                                'name' => "p_safety_domain_edit",
                                'title' => "UI_PUBLIC_MODIFY",
                                'function' => array(
                                    '24-editDomainServer',
                                    '24-domainAvailable',
                                    '24-getOldDomainServerInfo',
                                    '4-getAllTenant',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                            // 删除
                            array(
                                'name' => "p_safety_domain_delete",
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => array(
                                    '24-deleteDomainServer',
                                ),
                                'level' => 10,
                                'is_operate' => 1,
                            ),
                        )
                    ),
                )
            ),
            //租户管理
            array(
                'name' => "tenant_manager",
                'path' => "./content/platform/tenant/tenant_manager.php",
                'class' => "viconfont vicon-tenant",
                'title' => "UI_PLATFORM_MULTI_TENANT",
                'level' => 1,
                'child' => array(
                    // 查看
                    array(
                        'name' => "p_tenant_manager_list",
                        'title' => "UI_PUBLIC_LOOK",
                        'function' => array(

                        ),
                        'level' => 10,
                    ),
                    // 新建
                    array(
                        'name' => "p_tenant_manager_add",
                        'title' => "UI_PUBLIC_ADD",
                        'function' => array(
                            '21-addTenant',
                            '21-tenantnameAvailable',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                    // 修改
                    array(
                        'name' => "p_tenant_manager_edit",
                        'title' => "UI_PUBLIC_MODIFY",
                        'function' => array(
                            '21-editTenant',
                            '21-tenantnameAvailable',
                            '21-getTenantEditInfo',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                    // 删除
                    array(
                        'name' => "p_tenant_manager_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => array(
                            '21-deleteTenant',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                    // 启用
                    array(
                        'name' => "p_tenant_manager_enable",
                        'title' => "WEB_PLATFORM_ENABLE",
                        'function' => array(
                            '21-unlockTenant',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                    // 禁用
                    array(
                        'name' => "p_tenant_manager_disable",
                        'title' => "WEB_PLATFORM_DISABLE",
                        'function' => array(
                            '21-lockTenant',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                )
            ),
            // 系统授权
            array(
                'name' => "authorization_module",
                'path' => "./content/platform/settings/authorization_module.php",
                'class' => "viconfont vicon-authorization_module",
                'title' => "UI_PLATFORM_MODULE_AUTH",
                'level' => 1,
                'child' => array(
                    // 查看
                    array(
                        'name' => "p_authorization_module_list",
                        'title' => "UI_PUBLIC_LOOK",
                        'function' => array(
                            '8-getSystemLisenceInfo',
                            '8-getThumbprint',
                        ),
                        'level' => 10,
                    ),
                    // 下载指纹文件
                    array(
                        'name' => "p_authorization_module_download",
                        'title' => "UI_SETTINGS_DOWNLOAD_FILE",
                        'function' => array(
                            '8-getThumbprintFile',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                    // 上传授权文件
                    array(
                        'name' => "p_authorization_module_upload",
                        'title' => "UI_SETTINGS_UPLOAD_FILE",
                        'function' => array(
                            '8-uploadLisence',
                        ),
                        'level' => 10,
                        'is_operate' => 1,
                    ),
                )
            ),
        )
    ),

    // 全局观察者
    array(
        'name' => "global_observer",
        'path' => "",
        'class' => "fa fa-eye",
        'title' => "UI_PLATFORM_GLOBAL_OBSERBER",
        'level' => 0,
        'child' => array(
            // 只查看
            array(
                'name' => "global_read",
                'class' => "icon-doc",
                'title' => "UI_PLATFORM_GLOBAL_READ",
                'level' => 1,
            ),
            // 查看并操作
            array(
                'name' => "global_write",
                'class' => "icon-magic-wand",
                'title' => "UI_PLATFORM_GLOBAL_WRITE",
                'level' => 1,
            ),
        )
    )
);
