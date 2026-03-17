<?php

/**
* 这是 监控中心对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    //任务
    [
        'name' => "task",
        'path' => "jobs.html",
        'class' => "viconfont vicon-task",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_JOB_MONITOR",
        'level' => 1,
        'child' => [
            // 当前任务
            [
                'name' => "current_job",
                'path' => "currentJob.html",
                'class' => "viconfont vicon-pt_job_current_task",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_CURRENT_JOB",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_current_job_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-jobs',
                            'get-jobs_info',
                            'get-jobs_flow',
                            'get-logs_jobs_running_logs',
                        ],
                        'level' => 10,
                    ],
                    // 管理
                    [
                        'name' => "p_current_job_manager",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MANAGEMENT",
                        'function' => [
                            'post-jobs_start',
                            'post-jobs_stop',
                            'delete-jobs',
                            'post-jobs_start_takeover',
                            'post-jobs_stop_takeover',
                            'post-jobs_start_failback',
                            'post-jobs_pause',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_JOB_OPERATE_DESC',
                    ],
                ]
            ],
            // 历史任务
            [
                'name' => "history_job",
                'path' => "historyJob.html",
                'class' => "viconfont vicon-pt_job_historical_task",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_HOSTORY_JOB",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_history_job_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-jobs_history',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_history_job_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'delete-jobs_history',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 下载任务日志
                    [
                        'name' => "p_history_job_download",
                        'title' => "WEB_PAGE_COMMON_MENU_ALARM_LOG_DOWNLOAD",
                        'function' => [
                            'get-jobs_log',
                            'get-jobs_log_down',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 任务编排
            [
                'name' => 'task_orchestration',
                'path' => "orchestration.html",
                'class' => "viconfont vicon-ge_advanced",
                'title' => "WEB_PAGE_COMMON_MENU_JOB_TASK_ORCHESTRATION",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_task_orchestration_list',
                        'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_LOOK',
                        'function' => [
                            'get-orchestration',
                        ],
                        'level' => 10,
                    ],
                    // 操作
                    [
                        'name' => "p_task_orchestration_manager",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MANAGEMENT",
                        'function' => [
                            'get-orchestration_operate',
                            'delete-orchestration',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                        'desc' => 'WEB_PAGE_COMMON_MENU_PAGE_JOB_ORA_OPERATE_DESC',
                    ],
                ]
            ],
        ]
    ],
    //告警
    [
        'name' => "alarm",
        'path' => "alarm.html",
        'class' => "viconfont vicon-alarm",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ALARM",
        'level' => 1,
        'child' => [
            // 任务告警
            [
                'name' => "task_alarm",
                'path' => "alarm.html#task",
                'class' => "viconfont vicon-pt_alarm_task_alarms ",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ALARM_TASK",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_task_alarm_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_task_alarm_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 响应
                    [
                        'name' => "p_task_alarm_response",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_RESPONSE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 下载日志
                    [
                        'name' => "p_task_alarm_download",
                        'title' => "WEB_PAGE_COMMON_MENU_ALARM_LOG_DOWNLOAD",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 系统告警
            [
                'name' => "system_alarm",
                'path' => "alarm.html#system",
                'class' => "viconfont vicon-pt_alarm_system_alarm ",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ALARM_SYSTEM",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_system_alarm_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_system_alarm_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 响应
                    [
                        'name' => "p_system_alarm_response",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_RESPONSE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
        ]
    ],
    //日志
    [
        'name' => "log",
        'path' => "log.html",
        'class' => "viconfont vicon-log",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_LOG",
        'level' => 1,
        'child' => [
            // 任务操作日志
            [
                'name' => "job_log",
                'path' => "log.html#task",
                'class' => "viconfont vicon-pt_log_task_operation_logs ",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_JOB_LOG",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_job_log_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_job_log_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 导出
                    [
                        'name' => "p_job_log_export",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_EXPORT",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                    ]
                ]
            ],
            // 系统操作日志
            [
                'name' => "system_log",
                'path' => "log.html#system",
                'class' => "viconfont vicon-pt_log_system_operation_logs ",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_LOG",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_system_log_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_system_log_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 导出
                    [
                        'name' => "p_system_log_export",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_EXPORT",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 高可用操作日志
            [
                'name' => "ha_log",
                'path' => "log.html#ha_log",
                'class' => "viconfont vicon-gaokeyongrizhi ",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_HA_LOG",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_ha_log_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-logs_ha_logs',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_ha_log_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'delete-logs_ha_logs',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 导出
                    [
                        'name' => "p_ha_log_export",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_EXPORT",
                        'function' => [
                            'post-logs_ha_logs',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ],
            ]
        ]
    ],
    //报表
    [
        'name' => 'report',
        'path' => "javascript:;",
        'class' => 'viconfont vicon-report',
        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_REPORT',
        'level' => 1,
        'showChild' => true,
        'child' => [
            // 概览
            [
                'name' => "vm_report",
                'path' => "reportView.html",
                'class' => "viconfont vicon-pt_report_shortage_report",
                'title' => "WEB_PAGE_COMMON_MENU_REPORT_OVERVIEW",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_vm_report_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
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
                        ],
                        'level' => 10,
                    ],
                    // 导出
                    [
                        'name' => "p_vm_report_export",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_EXPORT",
                        'level' => 10,
                        'function' => [],
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 自定义报表
            [
                'name' => 'storage_report',
                'path' => "report.html",
                'class' => "viconfont vicon-moban",
                'title' => "WEB_PAGE_COMMON_MENU_REPORT_TEMPLATE",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_storage_report_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' =>[
                            'get-report_template_list',
                            'get-report_template_get_addressInfo',
                            'get-report_template_get_email',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_storage_report_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_storage_report_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_storage_report_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 导出
                    [
                        'name' => "p_storage_report_export",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_EXPORT",
                        'level' => 10,
                        'function' => [
                            'post-report_edit_template',
                            'delete-report_template',
                        ],
                        'is_operate' => 1,
                    ],
                ]
            ],
        ]
    ],
    //系统
    [
        'name' => "system",
        'path' => "system.html",
        'class' => "viconfont vicon-system",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM",
        'level' => 1,
        'child' => [
            // 系统监控
            [
                'name' => "system_monitor",
                'path' => "system.html#monitor",
                'class' => "viconfont vicon-system",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_MONITOR",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_system_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 下载系统日志
                    [
                        'name' => "p_system_log_download",
                        'title' => "WEB_PAGE_COMMON_MENU_LOG_SYSTEM_LOG_DOWNLOAD",
                        'function' => [],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 设置告警规则
                    [
                        'name' => 'p_system_rule',
                        'title' => 'WEB_PAGE_COMMON_MENU_SYSTEM_MONITOR_SET_ALARM',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ]
                ]
            ],
            // 系统信息
            [
                'name' => "system_info",
                'path' => "system.html#info",
                'class' => "viconfont vicon-pt_system_information",
                'title' => "WEB_PAGE_COMMON_MENU_SYSTEM_INFO",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_system_info_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ]
        ]
    ],
];
