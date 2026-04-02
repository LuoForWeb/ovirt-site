<?php
/**
 * 所有页面层级关系,用于顶部和左边树形展示
 */
return array(
    //首页
    array(
        'name' => "homepage",
        'path' => "javascript:;",
        'class' => "iconfont icon-shouyexian",
        'title' => "UI_PLATFORM_HOMEPAGE",
        'level' => 0,
        'child' => array()
    ),
    array(
        'name' => "monitor",
        'path' => "javascript:;",
        'class' => "iconfont icon-v-jkzx",
        'title' => "UI_PLATFORM_MONITOR_CENTER",
        'level' => 0,
        'child' => array(
            array(
                'name' => "task",
                'path' => "./content/platform/jobs/jobs.php",
                'class' => "fa fa-tasks",
                'title' => "UI_PLATFORM_JOB_MONITOR",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "current_job",
                        'path' => "./content/platform/jobs/current_job.php",
                        'class' => "fa fa-tachometer",
                        'title' => "UI_PLATFORM_CURRENT_JOB",
                        'level' => 2,
                        'child' => array()
                    ),
                    array(
                        'name' => "history_job",
                        'path' => "./content/platform/jobs/history_job.php",
                        'class' => "fa fa-history",
                        'title' => "UI_PLATFORM_HOSTORY_JOB",
                        'level' => 2,
                        'child' => array()
                    ),
                )
            ),
            array(
                'name' => "alarm",
                'path' => "./content/platform/alarm/alarm.php",
                'class' => "icon-bell",
                'title' => "UI_PLATFORM_ALARM",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "task_alarm",
                        'path' => "./content/platform/alarm/task_alarm.php",
                        'class' => "iconfont icon-taskalarm",
                        'title' => "UI_PLATFORM_ALARM_TASK",
                        'level' => 2,
                        'child' => array()
                    ),
                    array(
                        'name' => "system_alarm",
                        'path' => "./content/platform/alarm/system_alarm.php",
                        'class' => "iconfont icon-systemalarm",
                        'title' => "UI_PLATFORM_ALARM_SYSTEM",
                        'level' => 2,
                        'child' => array()
                    ),
                )
            ),
            array(
                'name' => "log",
                'path' => "./content/platform/logs/logs.php",
                'class' => "fa fa-list-alt",
                'title' => "UI_PLATFORM_LOG",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "job_log",
                        'path' => "./content/platform/logs/job_log.php",
                        'class' => "iconfont icon-tasklog",
                        'title' => "UI_PLATFORM_JOB_LOG",
                        'level' => 2,
                        'child' => array()
                    ),
                    array(
                        'name' => "system_log",
                        'path' => "./content/platform/logs/system_log.php",
                        'class' => "iconfont icon-systemlog",
                        'title' => "UI_PLATFORM_SYSTEM_LOG",
                        'level' => 2,
                        'child' => array()
                    ),
                )
            ),
            array(
                'name' => "report",
                'path' => "./content/platform/reports/reports.php",
                'class' => "fa fa-newspaper-o",
                'title' => "UI_PLATFORM_REPORT",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "storage_report",
                        'path' => "./content/platform/reports/storage_report.php",
                        'class' => "fa fa-home",
                        'title' => "UI_PLATFORM_STORAGE_REPORT",
                        'level' => 2,
                        'child' => array()
                    ),
                    array(
                        'name' => "vm_report",
                        'path' => "./content/platform/reports/vm_report.php",
                        'class' => "fa fa-home",
                        'title' => "UI_PLATFORM_VM_REPORT_NOTICE",
                        'level' => 2,
                        'child' => array()
                    ),
                )
            ),
        )
    ),
    array(
        'name' => "vmprotect",
        'path' => "javascript:;",
        'class' => "iconfont icon-v-xnjbf",
        'title' => "UI_PLATFORM_VM_PROTECT",
        'level' => 0,
        'child' => array(
            array(
                'name' => "vm_overview",
//                 'path' => "./content/vm/vm_overview.php",
                'path' => "./content/vm/vmreport.php",
                'class' => "fa fa-list",
                'title' => "UI_PLATFORM_OVERVIEW",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "vmbackup",
                'path' => "./content/vm/vmbackup.php",
                'class' => "icon-action-redo",
                'title' => "UI_PLATFORM_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "vmrecover",
                'path' => "./content/vm/vmrecover.php",
                'class' => "icon-action-undo",
                'title' => "UI_PLATFORM_RECOVER",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "vminstantrecover",
                'path' => "./content/vm/vminstantrecover.php",
                'class' => "fa fa-bolt",
                'title' => "WEB_VM_INSTANT_RECOVERY",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "vmrecovera",
                'path' => "./content/vm/vmgrainrecover.php",
                'class' => "fa fa-reply-all",
                'title' => "UI_PLATFORM_GRAIN_RECOVERY",
                'level' => 1,
                'child' => array()
            ),
//             array(
//                 'name' => "vmcdp",
//                 'path' => "./content/vm/vmcdp.php",
//                 'class' => "fa fa-shield",
//                 'title' => "CDP",
//                 'level' => 1,
//                 'child' => array()
//             ),
            array(
                'name' => "vmdata",
                'path' => "./content/vm/vmdata.php",
                'class' => "fa fa-database",
                'title' => "UI_PLATFORM_BACKUPDATA",
                'level' => 1,
                'child' => array()
            ),
        )
    ),
    array(
        'name' => "dbtiming",
        'path' => "javascript:;",
        'class' => "iconfont icon-c-databackup",
        'title' => "UI_PLATFORM_DB_TIMING",
        'level' => 0,
        'child' => array(
            array(
                'name' => "dbtbackup",
                'path' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/#/protect/backups/work?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "icon-action-redo",
                'title' => "UI_PLATFORM_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtrecovery",
                'path' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/#/protect/recovery/job?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "icon-action-undo",
                'title' => "UI_PLATFORM_RECOVER",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtdata",
                'path' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/#/protect/administer?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "fa fa-database",
                'title' => "UI_PLATFORM_BACKUPDATA",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtagent",
                'path' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/#/client?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "icon-puzzle",
                'title' => "UI_PLATFORM_BACKUP_AGENT",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtstorage",
                'path' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/#/storage/manage?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "fa fa-cubes",
                'title' => "UI_JOB_STORAGE_DEV",
                'level' => 1,
                'child' => array()
            ),
            // array(
            //     'name' => "dbtlog",
            //     'path' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/#/storage/log?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
            //     'class' => "fa fa-list-alt",
            //     'title' => "UI_PLATFORM_LOG",
            //     'level' => 1,
            //     'child' => array()
            // ),
        )
    ),
    array(
        'name' => "dbprotect",
        'path' => "javascript:;",
        'class' => "iconfont icon-c-databackup",
        'title' => "UI_PLATFORM_DB_CDP",
        'level' => 0,
        'child' => array(
            array(
                'name' => "dbregularbackup",
                'path' => "http://" . $_SERVER['SERVER_ADDR'] . ":8088" . "/#/transfer?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "icon-action-redo",
                'title' => "UI_PLATFORM_DB_REGULAR_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbcdpbackup",
                'path' => "./content/db/dbcdp.php",
                'class' => "fa fa-shield",
                'title' => "UI_PLATFORM_DB_REALTIME_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbdataRecovery",
                'path' => "./content/db/dbrecovery.php",
                'class' => "iconfont icon-c-datarecovery",
                'title' => "UI_PLATFORM_DB_DATA_RECOVERY",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbdata",
                'path' => "./content/db/dbdata.php",
                'class' => "fa fa-database",
                'title' => "UI_PLATFORM_DB_BACKUP_DATA",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbhost",
                'path' => "./content/db/dbhost.php",
                'class' => "iconfont icon-xinicon02",
                'title' => "UI_PLATFORM_DB_HOST_MANAGER",
                'level' => 1,
                'child' => array()
            ),
        )
    ),
    array(
        'name' => "fileprotect",
        'path' => "javascript:;",
        'class' => "iconfont icon-v-wjbf",
        'title' => "UI_PLATFORM_FILE_PROTECT",
        'level' => 0,
        'child' => array(
            array(
                'name' => "filebackup",
                'path' => "./content/fs/filebackup.php",
                'class' => "icon-action-redo",
                'title' => "UI_PLATFORM_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "filerecover",
                'path' => "./content/fs/filerecover.php",
                'class' => "icon-action-undo",
                'title' => "UI_PLATFORM_RECOVER",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "filedata",
                'path' => "./content/fs/filedata.php",
                'class' => "fa fa-database",
                'title' => "UI_PLATFORM_BACKUPDATA",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "agent",
                'path' => "./content/platform/agent/agent.php",
                'class' => "icon-puzzle",
                'title' => "UI_PLATFORM_BACKUP_AGENT",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "agent_manager",
                        'path' => "./content/platform/agent/agent_manager.php",
                        'class' => "fa fa-puzzle-piece",
                        'title' => "UI_PLATFORM_AGENT_MANAGER",
                        'level' => 2,
                        'child' => array()
                    ),
                    array(
                        'name' => "my_agent",
                        'path' => "./content/platform/agent/my_agent.php",
                        'class' => "fa fa-puzzle-piece",
                        'title' => "UI_PLATFORM_MY_AGENT",
                        'level' => 2,
                        'child' => array()
                    ),
                )
            ),
        )
    ),
    array(
        'name' => "datacopy",
        'path' => "javascript:;",
        'class' => "iconfont icon-v-fuben",
        'title' => "UI_PLATFORM_VM_COPY",
        'level' => 0,
        'child' => array(
            array(
                'name' => "vmcopy",
                'path' => "./content/vm/vmcopy.php",
                'class' => "iconfont icon-copyoffsite",
                'title' => "UI_PLATFORM_VM_COPY_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "vmcopyback",
                'path' => "./content/vm/vmcopyback.php",
                'class' => "iconfont icon-copyback",
                'title' => "UI_PLATFORM_VM_COPY_RECOVERY",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "vmcopydata",
                'path' => "./content/vm/vmcopydata.php",
                'class' => "fa fa-database",
                'title' => "UI_COPY_DATA",
                'level' => 1,
                'child' => array()
            ),
        )
    ),
    array(
        'name' => "data_archive",
        'path' => "javascript:;",
        'class' => "fa fa-archive",
        'title' => "UI_PLATFORM_DATA_ARCHIVE",
        'level' => 0,
        'child' => array(
            array(
                'name' => "archive_add",
                'path' => "./content/platform/archive/addarchive.php",
                'class' => "icon-action-redo",
                'title' => "UI_ARCHIVE_DATA_ADD",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "archive_back",
                'path' => "./content/platform/archive/archiveback.php",
                'class' => "icon-action-undo",
                'title' => "UI_ARCHIVE_DATA_BACK",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "archive_data",
                'path' => "./content/platform/archive/archivedata.php",
                'class' => "fa fa-database",
                'title' => "UI_ARCHIVE_DATA_MANAGER",
                'level' => 1,
                'child' => array()
            ),
        )
    ),
//     array(
//         'name' => "orch",
//         'path' => "javascript:;",
//         'class' => "iconfont icon-v-znhfyl",
//         'title' => "UI_PLATFORM_ORCHESTRATION",
//         'level' => 0,
//         'child' => array(
//             array(
//                 'name' => "orch_environment",
//                 'path' => "./content/platform/manoeuvre/environment.php",
//                 'class' => "iconfont icon-fuwuqidizhiduixiang",
//                 'title' => "UI_PLATFORM_ORCH_ENVIRONMENT",
//                 'level' => 1,
//                 'child' => array()
//             ),
//             array(
//                 'name' => "orch_plan",
//                 'path' => "./content/platform/manoeuvre/plan.php",
//                 'class' => "iconfont icon-yingjiyuan",
//                 'title' => "UI_PLATFORM_ORCH_PLAN",
//                 'level' => 1,
//                 'child' => array()
//             ),
//             array(
//                 'name' => "orch_task",
//                 'path' => "./content/platform/manoeuvre/task.php",
//                 'class' => "iconfont icon-renwu",
//                 'title' => "UI_PLATFORM_ORCH_TASK",
//                 'level' => 1,
//                 'child' => array()
//             ),
//             array(
//                 'name' => "orch_report",
//                 'path' => "./content/platform/manoeuvre/report.php",
//                 'class' => "iconfont icon-nreport",
//                 'title' => "UI_PLATFORM_ORCH_REPORT",
//                 'level' => 1,
//                 'child' => array()
//             ),
//         )
//     ),
    array(
        'name' => "resmanagement",
        'path' => "javascript:;",
        'class' => "iconfont icon-v-xtgl",
        'title' => "UI_PLATFORM_RESOURCE_MANAGER",
        'level' => 0,
        'child' => array(
            array(
                'name' => "vcenter_manager",
                'path' => "./content/vm/vcenter_manager.php",
                'class' => "fa fa-codepen",
                'title' => "UI_PLATFORM_VCENTER",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "appliance_manager",
                'path' => "./content/appliance/appliance_manager.php",
                'class' => "iconfont icon-proxy",
                'title' => "UI_PLATFORM_APPLIANCE",
                'level' => 1,
                'child' => array()
            ),
//             array(
//                 'name' => "node_manager",
//                 'path' => "./content/platform/node/node_manager.php",
//                 'class' => "fa fa-sitemap",
//                 'title' => "UI_PALTFORM_NODE",
//                 'level' => 1,
//                 'child' => array()
//             ),
            array(
                'name' => "storage_manager",
                'path' => "./content/platform/storage/storage_manager.php",
                'class' => "fa fa-cubes",
                'title' => "UI_JOB_STORAGE_DEV",
                'level' => 1,
                'child' => array()
            ),
//             array(
//                 'name' => "storage_lanfree",
//                 'path' => "./content/platform/storage/storage_lanfree.php",
//                 'class' => "fa fa-gears",
//                 'title' => "UI_PALTFORM_STORAGE_LANFREE",
//                 'level' => 1,
//                 'child' => array()
//             ),
            array(
                'name' => "global_strategy",
                'path' => "./content/platform/strategy/global_strategy.php",
                'class' => "iconfont icon-celvezu",
                'title' => "UI_PLATFORM_GLOBAL_STRATEGY",
                'level' => 1,
                'child' => array()
            ),
        )
    ),
    array(
        'name' => "sysmanagement",
        'path' => "javascript:;",
        'class' => "icon-settings",
        'title' => "UI_PLATFORM_SYSTEM_MANAGER",
        'level' => 0,
        'child' => array(
            array(
                'name' => "setting_manager",
                'path' => "./content/platform/settings/setting_manager.php",
                'class' => "fa fa-wrench",
                'title' => "UI_PLATFORM_SYSTEM_SET",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "users",
                'path' => "./content/platform/users/users.php",
                'class' => "icon-user",
                'title' => "UI_PALTFORM_MANAGER_USER",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "authorization_module",
                'path' => "./content/platform/settings/authorization_module.php",
                'class' => "icon-badge",
                'title' => "UI_PLATFORM_MODULE_AUTH",
                'level' => 1,
                'child' => array()
            )
        )
    ),
	

);