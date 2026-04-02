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
            'name' => 'task',
            'path' => './content/platform/jobs/jobs.php',
            'class' => 'viconfont vicon-task',
            'title' => 'UI_PLATFORM_JOB_MONITOR',
            'level' => 1,
            'child' => [
                // 当前任务
                [
                    'name' => 'current_job',
                    'path' => './content/platform/jobs/current_job.php',
                    'class' => 'viconfont vicon-pt_job_current_task',
                    'title' => 'UI_PLATFORM_CURRENT_JOB',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_current_job_list',
                            'title' => 'UI_PUBLIC_LOOK',
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
                            'name' => 'p_current_job_manager',
                            'title' => 'UI_PLATFORM_MANAGEMENT',
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
                        ],
                    ]
                ],
                // 历史任务
                [
                    'name' => 'history_job',
                    'path' => './content/platform/jobs/history_job.php',
                    'class' => 'viconfont vicon-pt_job_historical_task',
                    'title' => 'UI_PLATFORM_HOSTORY_JOB',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_history_job_list',
                            'title' => 'UI_PUBLIC_LOOK',
                            'function' => [
                                'get-jobs_history',
                            ],
                            'level' => 10,
                        ],
                        // 删除
                        [
                            'name' => "p_history_job_delete",
                            'title' => "UI_PUBLIC_DELETE",
                            'function' => [
                                'delete-jobs_history',
                            ],
                            'level' => 10,
                        ],
                        // 下载任务日志
                        [
                            'name' => 'p_history_job_download',
                            'title' => 'UI_ALARM_LOG_DOWNLOAD',
                            'function' => [
                                'get-jobs_log',
                                'get-jobs_log_down',
                            ],
                            'level' => 10,
                        ],
                    ]
                ],
                // 任务编排
                [
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
                        ],
                    ]
                ],
            ]
        ],
        //告警
        [
            'name' => 'alarm',
            'path' => './content/platform/alarm/alarm.php',
            'class' => 'viconfont vicon-alarm',
            'title' => 'UI_PLATFORM_ALARM',
            'level' => 1,
            'child' => [
                // 任务告警
                [
                    'name' => 'task_alarm',
                    'path' => './content/platform/alarm/task_alarm.php',
                    'class' => 'viconfont vicon-pt_alarm_task_alarms ',
                    'title' => 'UI_PLATFORM_ALARM_TASK',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_task_alarm_list',
                            'title' => 'UI_PUBLIC_LOOK',
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 删除
                        [
                            'name' => "p_task_alarm_delete",
                            'title' => "UI_PUBLIC_DELETE",
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 响应
                        [
                            'name' => 'p_task_alarm_response',
                            'title' => 'UI_PUBLIC_RESPONSE',
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 下载日志
                        [
                            'name' => 'p_task_alarm_download',
                            'title' => 'UI_ALARM_LOG_DOWNLOAD',
                            'function' => [],
                            'level' => 10,
                        ],
                    ]
                ],
                // 系统告警
                [
                    'name' => 'system_alarm',
                    'path' => './content/platform/alarm/system_alarm.php',
                    'class' => 'viconfont vicon-pt_alarm_system_alarm ',
                    'title' => 'UI_PLATFORM_ALARM_SYSTEM',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_system_alarm_list',
                            'title' => 'UI_PUBLIC_LOOK',
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 删除
                        [
                            'name' => "p_system_alarm_delete",
                            'title' => "UI_PUBLIC_DELETE",
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 响应
                        [
                            'name' => 'p_system_alarm_response',
                            'title' => 'UI_PUBLIC_RESPONSE',
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                    ]
                ],
            ]
        ],
        //日志
        [
            'name' => 'log',
            'path' => './content/platform/logs/logs.php',
            'class' => 'viconfont vicon-log',
            'title' => 'UI_PLATFORM_LOG',
            'level' => 1,
            'child' => [
                // 任务操作日志
                [
                    'name' => 'job_log',
                    'path' => './content/platform/logs/job_log.php',
                    'class' => 'viconfont vicon-pt_log_task_operation_logs ',
                    'title' => 'UI_PLATFORM_JOB_LOG',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_job_log_list',
                            'title' => 'UI_PUBLIC_LOOK',
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 删除
                        [
                            'name' => "p_job_log_delete",
                            'title' => "UI_PUBLIC_DELETE",
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                    ]
                ],
                // 系统操作日志
                [
                    'name' => 'system_log',
                    'path' => './content/platform/logs/system_log.php',
                    'class' => 'viconfont vicon-pt_log_system_operation_logs ',
                    'title' => 'UI_PLATFORM_SYSTEM_LOG',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_system_log_list',
                            'title' => 'UI_PUBLIC_LOOK',
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 删除
                        [
                            'name' => "p_system_log_delete",
                            'title' => "UI_PUBLIC_DELETE",
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                        // 下载系统日志
                        [
                            'name' => 'p_system_log_download',
                            'title' => 'UI_LOG_SYSTEM_LOG_DOWNLOAD',
                            'function' => [
                                'get-url',
                            ],
                            'level' => 10,
                        ],
                    ]
                ],
            ]
        ],
        //报表
        [
            'name' => 'report',
            'path' => './content/platform/reports/reports.php',
            'class' => 'viconfont vicon-report',
            'title' => 'UI_PLATFORM_REPORT',
            'level' => 1,
            'child' => [
                // 概览
                [
                    'name' => 'overview',
                    'path' => './content/platform/reports/overview.php',
                    'class' => 'viconfont vicon-pt_report_vm_report',
                    'title' => 'UI_REPORT_OVERVIEW',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_report_overview_list',
                            'title' => 'UI_PUBLIC_LOOK',
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
                    ]
                ],
                // 自定义报表
                [
                    'name' => 'strategy',
                    'path' => './content/platform/reports/strategy.php',
                    'class' => 'viconfont vicon-pt_report_shortage_report',
                    'title' => 'UI_REPORT_TEMPLATE',
                    'level' => 2,
                    'child' => [
                        // 查看
                        [
                            'name' => 'p_report_strategy_list',
                            'title' => 'UI_PUBLIC_LOOK',
                            'function' => [
                                'get-report_template_list',
                                'get-report_template_get_addressInfo',
                                'get-report_template_get_email',
                            ],
                            'level' => 10,
                        ],
                        // 管理
                        [
                            'name' => 'p_current_job_manager',
                            'title' => 'UI_PLATFORM_MANAGEMENT',
                            'function' => [
                                'post-report_edit_template',
                                'delete-report_template',
                            ],
                            'level' => 10,
                        ],
                    ]
                ],
            ]
        ],
        //系统
        [
            'name' => 'system',
            'path' => './content/platform/system/system.php',
            'class' => 'viconfont vicon-system',
            'title' => 'UI_PLATFORM_SYSTEM',
            'level' => 1,
            'child' => [
                // 查看
                [
                    'name' => 'p_system_list',
                    'title' => 'UI_PUBLIC_LOOK',
                    'function' => [
                        'get-url',
                    ],
                    'level' => 10,
                ],
                // 设置告警规则
                [
                    'name' => 'p_system_rule',
                    'title' => 'UI_SYSTEM_MONITOR_SET_ALARM',
                    'function' => [
                        'get-url',
                    ],
                    'level' => 10,
                ]
            ]
        ],

];
