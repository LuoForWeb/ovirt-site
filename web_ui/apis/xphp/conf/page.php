<?php
/**
 * 所有页面层级关系,用于顶部和左边树形展示
 */
$iplist = explode(":", $_SERVER['HTTP_HOST']);
$ipAddr = $iplist[0];
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
                        'child' => array(
                            array(
                                'name' => "p_current_job_manager",
                                'class' => "",
                                'title' => "UI_PLATFORM_MANAGEMENT",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "history_job",
                        'path' => "./content/platform/jobs/history_job.php",
                        'class' => "fa fa-history",
                        'title' => "UI_PLATFORM_HOSTORY_JOB",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_history_job_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_history_job_download",
                                'class' => "",
                                'title' => "UI_ALARM_LOG_DOWNLOAD",
                                'level' => 10,
                            ),
                        )
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
                        'child' => array(
                            array(
                                'name' => "p_task_alarm_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_task_alarm_response",
                                'class' => "",
                                'title' => "UI_PUBLIC_RESPONSE",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "system_alarm",
                        'path' => "./content/platform/alarm/system_alarm.php",
                        'class' => "iconfont icon-systemalarm",
                        'title' => "UI_PLATFORM_ALARM_SYSTEM",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_system_alarm_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_system_alarm_response",
                                'class' => "",
                                'title' => "UI_PUBLIC_RESPONSE",
                                'level' => 10,
                            ),
                        )
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
                        'child' => array(
                            array(
                                'name' => "p_job_log_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "system_log",
                        'path' => "./content/platform/logs/system_log.php",
                        'class' => "iconfont icon-systemlog",
                        'title' => "UI_PLATFORM_SYSTEM_LOG",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_system_log_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_system_log_download",
                                'class' => "",
                                'title' => "UI_PUBLIC_DOWNLOAD",
                                'level' => 10,
                            ),
                        )
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
                        'child' => array(
                            array(
                                'name' => "p_storage_report_export",
                                'class' => "",
                                'title' => "UI_PUBLIC_EXPORT",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "vm_report",
                        'path' => "./content/platform/reports/vm_report.php",
                        'class' => "fa fa-home",
                        'title' => "UI_PLATFORM_VM_REPORT_NOTICE",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_vm_report_export",
                                'class' => "",
                                'title' => "UI_PUBLIC_EXPORT",
                                'level' => 10,
                            ),
                        )
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
                'path' => "./content/vm/vmreport.php",
                'class' => "fa fa-list",
                'title' => "UI_PLATFORM_OVERVIEW",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_vm_overview_export",
                        'class' => "",
                        'title' => "UI_PUBLIC_EXPORT",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vm_overview_print",
                        'class' => "",
                        'title' => "UI_PUBLIC_PRINT",
                        'level' => 10,
                    ),
                )
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
            array(
                'name' => "vmdata",
                'path' => "./content/vm/vmdata.php",
                'class' => "fa fa-database",
                'title' => "UI_PLATFORM_BACKUPDATA",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_vmdata_detele",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vmdata_remark",
                        'class' => "",
                        'title' => "UI_PUBLIC_REMARK",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vmdata_star",
                        'class' => "",
                        'title' => "UI_DATA_STAR",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "vcenter_manager",
                'path' => "./content/vm/vcenter_manager.php",
                'class' => "fa fa-codepen",
                'title' => "UI_PLATFORM_VCENTER",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_vcenter_manager_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vcenter_manager_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vcenter_manager_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vcenter_manager_backup",
                        'class' => "",
                        'title' => "UI_VCENTER_ENGINE_BACKUP",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vcenter_manager_refresh",
                        'class' => "",
                        'title' => "UI_VCENTER_AUTO_REFRESH",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vcenter_manager_sync",
                        'class' => "",
                        'title' => "WEB_PLATFORM_DES_SYNC",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vcenter_manager_license",
                        'class' => "",
                        'title' => "UI_SETTINGS_AUTH",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_vm_overview_vmoperate",
                        'class' => "",
                        'title' => "WEB_VM_OPERATION",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "appliance_manager",
                'path' => "./content/appliance/appliance_manager.php",
                'class' => "iconfont icon-proxy",
                'title' => "UI_PLATFORM_APPLIANCE",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_appliance_manager_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_appliance_manager_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_appliance_manager_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
        )
    ),
    
    array(
        'name' => "db_protect",
        'path' => "javascript:;",
        'class' => "iconfont icon-c-databackup",
        'title' => "UI_PLATFORM_DB_PROTECT",
        'level' => 0,
        'child' => array(
            array(
                'name' => "db_backup",
                'path' => "./content/dbprotect/dbbackup.php",
                'class' => "icon-action-redo",
                'title' => "UI_PLATFORM_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "db_recovery",
                'path' => "./content/dbprotect/dbrecover.php",
                'class' => "icon-action-undo",
                'title' => "UI_PLATFORM_RECOVER",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "db_data_manager",
                'path' => "./content/dbprotect/db_data_manager.php",
                'class' => "fa fa-database",
                'title' => "UI_PLATFORM_BACKUPDATA",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_dbdata_detele",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_dbdata_remark",
                        'class' => "",
                        'title' => "UI_PUBLIC_REMARK",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_dbdata_star",
                        'class' => "",
                        'title' => "UI_DATA_STAR",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "db_agent_manager",
                'path' => "./content/dbprotect/db_agent_manager.php",
                'class' => "icon-puzzle",
                'title' => "UI_PLATFORM_DB_AGENT",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_db_agent_manager_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_db_agent_manager_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_db_agent_manager_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_db_agent_manager_certification",
                        'class' => "",
                        'title' => "UI_DB_INSTANCE_VERIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_db_agent_manager_license",
                        'class' => "",
                        'title' => "UI_SETTINGS_AUTH",
                        'level' => 10,
                    ),
                )
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
                'child' => array(
                    array(
                        'name' => "p_filedata_detele",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_filedata_star",
                        'class' => "",
                        'title' => "UI_DATA_STAR",
                        'level' => 10,
                    ),
                )
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
                        'child' => array(
                            array(
                                'name' => "p_agent_manager_register",
                                'class' => "",
                                'title' => "UI_AGENT_REGIST_TITLE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_agent_manager_modify",
                                'class' => "",
                                'title' => "UI_PUBLIC_MODIFY",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "my_agent",
                        'path' => "./content/platform/agent/my_agent.php",
                        'class' => "fa fa-puzzle-piece",
                        'title' => "UI_PLATFORM_MY_AGENT",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_my_agent_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                        )
                    ),
                )
            ),
        )
    ),
    array(
        'name' => "dbtiming",
        'path' => "javascript:;",
        'class' => "iconfont icon-zhujibaohu",
        'title' => "UI_PLATFORM_DB_TIMING",
        'level' => 0,
        'child' => array(
            array(
                'name' => "dbtbackup",
                'path' => "https://" . $ipAddr . ":8089" . "/#/protect/backups/work?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "icon-action-redo",
                'title' => "UI_PLATFORM_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtrecovery",
                'path' => "https://" . $ipAddr . ":8089" . "/#/protect/recovery/job?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "icon-action-undo",
                'title' => "UI_PLATFORM_RECOVER",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtdata",
                'path' => "https://" . $ipAddr . ":8089" . "/#/protect/administer?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "fa fa-database",
                'title' => "UI_PLATFORM_BACKUPDATA",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtagent",
                'path' => "https://" . $ipAddr . ":8089" . "/#/client?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "icon-puzzle",
                'title' => "UI_PLATFORM_BACKUP_AGENT",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbtstorage",
                'path' => "https://" . $ipAddr . ":8089" . "/#/storage/manage?auth=9a4fc91c5375ec4a1e2fe0a8cc781cd7",
                'class' => "viconfont vicon-storage_manager",
                'title' => "UI_JOB_STORAGE_DEV",
                'level' => 1,
                'child' => array()
            ),
        )
    ),
    array(
        'name' => "dbprotect",
        'path' => "javascript:;",
        'class' => "fa fa-shield",
        'title' => "UI_PLATFORM_DB_CDP",
        'level' => 0,
        'child' => array(
            array(
                'name' => "dbcdpbackup",
                'path' => "./content/db/dbcdp.php",
                'class' => "iconfont icon-dbcdp",
                'title' => "UI_PLATFORM_DB_REALTIME_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbdataRecovery",
                'path' => "./content/db/dbrecovery.php",
                'class' => "iconfont icon-dbcdprec",
                'title' => "UI_PLATFORM_DB_DATA_RECOVERY",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbdata",
                'path' => "./content/db/dbdata.php",
                'class' => "iconfont icon-dbdata",
                'title' => "UI_PLATFORM_DB_BACKUP_DATA",
                'level' => 1,
                'child' => array()
            ),
            
            array(
                'name' => "filecdpbackup",
                'path' => "./content/fs/filecdp.php",
                'class' => "iconfont icon-fscdp",
                'title' => "UI_PLATFORM_CDP_FILE_REALTIME_BACKUP",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "filecdpdata",
                'path' => "./content/fs/filecdpdata.php",
                'class' => "iconfont icon-fsdata",
                'title' => "UI_PLATFORM_CDP_FILE_BACKUP_DATA",
                'level' => 1,
                'child' => array()
            ),
            array(
                'name' => "dbhost",
                'path' => "./content/db/dbhost.php",
                'class' => "iconfont icon-xinicon02",
                'title' => "UI_PLATFORM_DB_HOST_MANAGER",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_dbhost_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_dbhost_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_dbhost_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_dbhost_download",
                        'class' => "",
                        'title' => "UI_LOG_SYSTEM_LOG_DOWNLOAD_NOW",
                        'level' => 10,
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
                'child' => array(
                    array(
                        'name' => "p_vmcopydata_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "remote_system",
                'path' => "./content/vm/remote_system.php",
                'class' => "viconfont vicon-storage_manager",
                'title' => "UI_COPY_ALLOPATRIC_BACKUP_SYSTEM",
                'level' => 1,
                'child' => array(
                    
                )
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
                'title' => "UI_ARCHIVE_DATA",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_archive_data_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "cloud_storage",
                'path' => "./content/platform/archive/cloud_storage.php",
                'class' => "fa fa-soundcloud",
                'title' => "UI_STORAGE_TYPE9",
                'level' => 1,
                'child' => array(
                    
                )
            ),
        )
    ),
    array(
        'name' => "orch",
        'path' => "javascript:;",
        'class' => "iconfont icon-v-znhfyl",
        'title' => "UI_PLATFORM_ORCHESTRATION",
        'level' => 0,
        'child' => array(
            array(
                'name' => "orch_environment",
                'path' => "./content/platform/manoeuvre/environment.php",
                'class' => "iconfont icon-fuwuqidizhiduixiang",
                'title' => "UI_PLATFORM_ORCH_ENVIRONMENT",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_orch_environment_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_orch_environment_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "orch_plan",
                'path' => "./content/platform/manoeuvre/plan.php",
                'class' => "iconfont icon-yingjiyuan",
                'title' => "UI_PLATFORM_ORCH_PLAN",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_orch_plan_add",
                        'class' => "",
                        'title' => "UI_EMERGENCY_ADD_PLAN",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_orch_plan_edit",
                        'class' => "",
                        'title' => "UI_EMERGENCY_EDIT_PLAN",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_orch_plan_delete",
                        'class' => "",
                        'title' => "UI_EMERGENCY_DELETE_PLAN",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_orch_plan_addvm",
                        'class' => "",
                        'title' => "WEB_DRILLS_ADD_PLAN_VM",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_orch_plan_deletevm",
                        'class' => "",
                        'title' => "WEB_DRILLS_DELETE_PLAN_VM",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "orch_task",
                'path' => "./content/platform/manoeuvre/task.php",
                'class' => "iconfont icon-renwu",
                'title' => "UI_PLATFORM_ORCH_TASK",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_orch_task_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_orch_task_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "orch_report",
                'path' => "./content/platform/manoeuvre/report.php",
                'class' => "iconfont icon-nreport",
                'title' => "UI_PLATFORM_ORCH_REPORT",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_orch_report_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
        )
    ),
    array(
        'name' => "resmanagement",
        'path' => "javascript:;",
        'class' => "iconfont icon-v-xtgl",
        'title' => "UI_PLATFORM_RESOURCE_MANAGER",
        'level' => 0,
        'child' => array(
            array(
                'name' => "node_manager",
                'path' => "./content/platform/node/node_manager.php",
                'class' => "fa fa-sitemap",
                'title' => "UI_PALTFORM_NODE",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_node_manager_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "storage_manager",
                'path' => "./content/platform/storage/storage_manager.php",
                'class' => "viconfont vicon-storage_manager",
                'title' => "UI_JOB_STORAGE_DEV",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_storage_manager_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_storage_manager_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_storage_manager_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_storage_manager_data",
                        'class' => "",
                        'title' => "UI_PALTFORM_STORAGE_DATA",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "storage_lanfree",
                'path' => "./content/platform/storage/storage_lanfree.php",
                'class' => "fa fa-gears",
                'title' => "UI_PALTFORM_STORAGE_LANFREE",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_storage_lanfree_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_storage_lanfree_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_storage_lanfree_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "global_strategy",
                'path' => "./content/platform/strategy/global_strategy.php",
                'class' => "iconfont icon-celvezu",
                'title' => "UI_PLATFORM_GLOBAL_STRATEGY",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_global_strategy_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_global_strategy_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_global_strategy_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "resource_group",
                'path' => "./content/platform/resource/resource_group.php",
                'class' => "viconfont vicon-resource_group",
                'title' => "UI_PLATFORM_RESOURCE_GROUP",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_resource_group_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_resource_group_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_resource_group_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_resource_group_manager",
                        'class' => "",
                        'title' => "UI_PLATFORM_RESOURCE_MANAGER",
                        'level' => 10,
                    ),
                )
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
                'child' => array(
                    array(
                        'name' => "system_network",
                        'title' => "UI_PLATFORM_NETWORK_SETTING",
                        'class' => "iconfont icon-ipaddress",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "set_ip",
                                'title' => "UI_PUBLIC_IP_ADDRESS",
                                'class' => "iconfont icon-ipaddr",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_setting_manager_ip",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_EDIT_IP",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "system_dns",
                                'title' => "UI_PLATFORM_SYSTEM_DNS",
                                'class' => "fa fa-retweet",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_system_dns_edit",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_SYSTEM_DNS",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "nic_teaming",
                                'title' => "UI_PLATFORM_NIC_TEAMING",
                                'class' => "iconfont icon-wangkajihe",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_nic_teaming_edit",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_NIC_TEAMING",
                                        'level' => 10,
                                    ),
                                )
                            )
                        )
                    ),
                    array(
                        'name' => "set_time",
                        'title' => "UI_PLATFORM_SET_TIME",
                        'class' => "iconfont icon-shezhishijian",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_setting_manager_time",
                                'class' => "",
                                'title' => "UI_PLATFORM_SET_TIME",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "system_notice",
                        'title' => "UI_PLATFORM_SYSTEM_NOTICE",
                        'class' => "iconfont icon-xitongtongzhi",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "emial_notice",
                                'title' => "UI_SETTINGS_NOTICE_EMAIL",
                                'class' => "icon-envelope-open",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_emial_notice",
                                        'class' => "",
                                        'title' => "UI_SETTINGS_NOTICE_EMAIL",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "sms_notice",
                                'title' => "UI_SETTINGS_NOTICE_SMS",
                                'class' => "icon-speech",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_sms_notice",
                                        'class' => "",
                                        'title' => "UI_SETTINGS_NOTICE_SMS",
                                        'level' => 10,
                                    ),
                                )
                            ),
                        )
                    ),
                    array(
                        'name' => "system_safe",
                        'title' => "UI_PLATFORM_SAFE_SETTING",
                        'class' => "iconfont icon-anquanpeizhi",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "account_safe",
                                'title' => "UI_PLATFORM_ACCOUNT_SAFE",
                                'class' => "iconfont icon-accountsafe",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_account_safe",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_ACCOUNT_SAFE",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "storage_safe",
                                'title' => "UI_PLATFORM_STORAGE_SAFE",
                                'class' => "iconfont icon-storagesafe",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_storage_safe",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_STORAGE_SAFE",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "os_safe",
                                'title' => "UI_PLATFORM_OS_SAFE",
                                'class' => "iconfont icon-ossafe",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_os_safe",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_OS_SAFE",
                                        'level' => 10,
                                    ),
                                )
                            ),
                        )
                    ),
                    array(
                        'name' => "system_poweroff",
                        'title' => "UI_PLATFORM_POWER",
                        'class' => "iconfont icon-offrestart",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_setting_manager_power",
                                'class' => "",
                                'title' => "UI_PLATFORM_POWER",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "system_upgrade",
                        'title' => "UI_PLATFORM_SYSTEM_UPDATE",
                        'class' => "iconfont icon-upgrade",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "upgrade_manage",
                                'title' => "UI_SETTINGS_UPDATE_MANAGE",
                                'class' => "fa fa-suitcase",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_setting_manager_upload",
                                        'class' => "",
                                        'title' => "UI_SETTINGS_UPLOAD_UPGRADE_PATCH",
                                        'level' => 10,
                                    ),
                                    array(
                                        'name' => "p_setting_manager_delete",
                                        'class' => "",
                                        'title' => "UI_SETTINGS_DELETE_UPGRADE_PATCH",
                                        'level' => 10,
                                    ),
                                    array(
                                        'name' => "p_setting_manager_upgrade",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_SYSTEM_UPDATE",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "upgrade_history",
                                'title' => "UI_SETTINGS_UPDATE_HISTORY",
                                'class' => "fa fa-history",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_upgrade_history_delete",
                                        'class' => "",
                                        'title' => "UI_SETTINGS_UPDATE_DELETE_ERROR_HISTORY",
                                        'level' => 10,
                                    ),
                                )
                            ),
                        )
                    ),
                    array(
                        'name' => "message_push",
                        'title' => "UI_PLATFORM_MESSAGE_PUSH",
                        'class' => "iconfont icon-xiaoxituisong",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_setting_manager_push",
                                'class' => "",
                                'title' => "UI_PLATFORM_MESSAGE_PUSH",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "visual_config",
                        'title' => "UI_PUBLIC_VISUAL_CONFIG",
                        'class' => "iconfont icon-visual",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_setting_manager_visualization",
                                'class' => "",
                                'title' => "UI_PUBLIC_VISUAL_CONFIG",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "system_service",
                        'title' => "UI_SETTINGS_SYSTEM_TOOL",
                        'class' => "iconfont icon-xitonggongju",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "service_manage",
                                'title' => "UI_SETTINGS_SERVICE_MANAGE",
                                'class' => "glyphicon glyphicon-phone",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_setting_manager_tools",
                                        'class' => "",
                                        'title' => "UI_SETTINGS_SYSTEM_TOOL",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "network_tool",
                                'title' => "UI_SETTINGS_NETWORK_TOOL",
                                'class' => "icon-globe",
                                'level' => 3,
                                'child' => array(
                                )
                            ),
                            array(
                                'name' => "remote_control",
                                'title' => "UI_PLATFORM_REMOTE_CONTROL",
                                'class' => "iconfont icon-yuanchengkongzhi",
                                'level' => 3,
                                'child' => array(
                                )
                            ),
                        )
                    ),
                    array(
                        'name' => "system_br",
                        'title' => "UI_PLATFORM_SYSTEM_BAK_REC",
                        'class' => "iconfont icon-sysbakrec",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "rc_oncebak",
                                'title' => "UI_PLATFORM_RC_ONCEBAK",
                                'class' => "iconfont icon-opbak",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_rc_oncebak",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_RC_ONCEBAK",
                                        'level' => 10,
                                    ),
                                )
                            ),
                            array(
                                'name' => "rc_autobak",
                                'title' => "UI_PLATFORM_RC_AUTOBAK",
                                'class' => "iconfont icon-autobak",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "rc_autobak_setting",
                                        'title' => "UI_PLATFORM_RC_AUTOBAK_SETTING",
                                        'class' => "iconfont icon-baksetting",
                                        'level' => 4,
                                        'child' => array(
                                            array(
                                                'name' => "p_rc_autobak_setting",
                                                'class' => "",
                                                'title' => "UI_PLATFORM_RC_AUTOBAK_SETTING",
                                                'level' => 10,
                                            ),
                                        )
                                    ),
                                    array(
                                        'name' => "rc_autobak_list",
                                        'title' => "UI_PLATFORM_RC_AUTOBAK_LIST",
                                        'class' => "iconfont icon-sysbakpoint",
                                        'level' => 4,
                                        'child' => array(
                                            array(
                                                'name' => "p_rc_autobak_list_download",
                                                'class' => "",
                                                'title' => "UI_PLATFORM_RC_AUTOBAK_POINT_DOWNLAOD",
                                                'level' => 10,
                                            ),
                                            array(
                                                'name' => "p_rc_autobak_list_delete",
                                                'class' => "",
                                                'title' => "UI_PUBLIC_DELETE",
                                                'level' => 10,
                                            ),
                                        )
                                    ),
                                )
                            ),
                            array(
                                'name' => "rc_recovery",
                                'title' => "UI_PLATFORM_RC_RECOVERY",
                                'class' => "iconfont icon-sysrecovery",
                                'level' => 3,
                                'child' => array(
                                    array(
                                        'name' => "p_rc_recovery",
                                        'class' => "",
                                        'title' => "UI_PLATFORM_RC_RECOVERY",
                                        'level' => 10,
                                    ),
                                )
                            ),
                        )
                    ),
                )
            ),
            array(
                'name' => "organization_manager",
                'path' => "./content/platform/organization/organization_manager.php",
                'class' => "fa fa-th",
                'title' => "UI_PLATFORM_ORGANIZATION",
                'level' => 1,
                'child' => array()
                
            ),
            array(
                'name' => "tenant_manager",
                'path' => "./content/platform/tenant/tenant_manager.php",
                'class' => "iconfont icon-zuhu",
                'title' => "UI_PLATFORM_TENANT",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_tenant_view",
                        'path' => "./content/platform/tenant/tenant.php?uuid=".$_SESSION['tenantuuid'],
                        'class' => "iconfont icon-zuhu",
                        'title' => "UI_PLATFORM_TENANT_INFO",
                        'level' => 2,
                        'child' => array()
                    ),
                    array(
                        'name' => "p_tenant_manager_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_tenant_manager_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_tenant_manager_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_tenant_manager_enable",
                        'class' => "",
                        'title' => "WEB_PLATFORM_ENABLE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_tenant_manager_disable",
                        'class' => "",
                        'title' => "WEB_PLATFORM_DISABLE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "billing_manager",
                'path' => "./content/platform/billing/billing_manager.php",
                'class' => "iconfont icon-feiyong",
                'title' => "UI_PLATFORM_BILLING",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_billing_manager_add",
                        'class' => "",
                        'title' => "UI_PUBLIC_ADD",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_billing_manager_edit",
                        'class' => "",
                        'title' => "UI_PUBLIC_MODIFY",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_billing_manager_delete",
                        'class' => "",
                        'title' => "UI_PUBLIC_DELETE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_billing_manager_enable",
                        'class' => "",
                        'title' => "WEB_PLATFORM_ENABLE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_billing_manager_disable",
                        'class' => "",
                        'title' => "WEB_PLATFORM_DISABLE",
                        'level' => 10,
                    ),
                )
            ),
            array(
                'name' => "safety",
                'path' => "./content/platform/users/safety_manager.php",
                'class' => "fa fa-users",
                'title' => "UI_PLATFORM_SAFETY",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "safety_user",
                        'path' => "./content/platform/users/users.php",
                        'class' => "viconfont vicon-pt_setting_user",
                        'title' => "UI_PLATFORM_SAFETY_USER",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_safety_user_add",
                                'class' => "",
                                'title' => "UI_PUBLIC_ADD",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_user_edit",
                                'class' => "",
                                'title' => "UI_PUBLIC_MODIFY",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_user_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_user_enable",
                                'class' => "",
                                'title' => "WEB_PLATFORM_ENABLE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_user_disable",
                                'class' => "",
                                'title' => "WEB_PLATFORM_DISABLE",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "safety_usergroup",
                        'path' => "./content/platform/users/users_group.php",
                        'class' => "iconfont icon-usergroup",
                        'title' => "UI_PLATFORM_SAFETY_USER_GROUP",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_safety_usergroup_add",
                                'class' => "",
                                'title' => "UI_PUBLIC_ADD",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_usergroup_edit",
                                'class' => "",
                                'title' => "UI_PUBLIC_MODIFY",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_usergroup_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_usergroup_enable",
                                'class' => "",
                                'title' => "WEB_PLATFORM_ENABLE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_usergroup_disable",
                                'class' => "",
                                'title' => "WEB_PLATFORM_DISABLE",
                                'level' => 10,
                            ),
                        )
                    ),
                    array(
                        'name' => "safety_role",
                        'path' => "./content/platform/users/role.php",
                        'class' => "iconfont icon-jiaose",
                        'title' => "UI_PLATFORM_SAFETY_ROLE",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_safety_role_add",
                                'class' => "",
                                'title' => "UI_PUBLIC_ADD",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_role_edit",
                                'class' => "",
                                'title' => "UI_PUBLIC_MODIFY",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_role_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_role_enable",
                                'class' => "",
                                'title' => "WEB_PLATFORM_ENABLE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_role_disable",
                                'class' => "",
                                'title' => "WEB_PLATFORM_DISABLE",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_role_allot",
                                'class' => "",
                                'title' => "UI_PLATFORM_SAFETY_ROLE_ALLOT",
                                'level' => 10,
                            )
                        )
                    ),
                    array(
                        'name' => "safety_domain",
                        'path' => "./content/platform/users/domain_server.php",
                        'class' => "iconfont icon-domain",
                        'title' => "UI_PLATFORM_SAFETY_DOMAIN",
                        'level' => 2,
                        'child' => array(
                            array(
                                'name' => "p_safety_domain_add",
                                'class' => "",
                                'title' => "UI_PUBLIC_ADD",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_domain_edit",
                                'class' => "",
                                'title' => "UI_PUBLIC_MODIFY",
                                'level' => 10,
                            ),
                            array(
                                'name' => "p_safety_domain_delete",
                                'class' => "",
                                'title' => "UI_PUBLIC_DELETE",
                                'level' => 10,
                            ),
                        )
                    ),
                )
            ),
            array(
                'name' => "authorization_module",
                'path' => "./content/platform/settings/authorization_module.php",
                'class' => "icon-badge",
                'title' => "UI_PLATFORM_MODULE_AUTH",
                'level' => 1,
                'child' => array(
                    array(
                        'name' => "p_authorization_module_download",
                        'class' => "",
                        'title' => "UI_SETTINGS_DOWNLOAD_FILE",
                        'level' => 10,
                    ),
                    array(
                        'name' => "p_authorization_module_upload",
                        'class' => "",
                        'title' => "UI_SETTINGS_UPLOAD_FILE",
                        'level' => 10,
                    ),
                )
            ),
        ),
    ),
    array(
        'name' => "global_observer",
        'path' => "",
        'class' => "fa fa-eye",
        'title' => "UI_PLATFORM_GLOBAL_OBSERBER",
        'level' => 0,
        'child' => array(
            array(
                'name' => "global_read",
                'class' => "icon-doc",
                'title' => "UI_PLATFORM_GLOBAL_READ",
                'level' => 1,
            ),
            array(
                'name' => "global_write",
                'class' => "icon-magic-wand",
                'title' => "UI_PLATFORM_GLOBAL_WRITE",
                'level' => 1,
            ),
        )
    )
    
    
);