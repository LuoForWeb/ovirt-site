<?php
/**
 * 所有页面层级关系,用于顶部和左边树形展示
 */
return array(
    //首页
    array(
        'name' => "homepage",
        'path' => "javascript:;",
        'class' => "fa fa-home",
        'title' => "UI_PLATFORM_HOMEPAGE",
        'level' => 0,
        'child' => array()
    ),
	//备份/恢复
	 array(
        'name' => "bakandrec",
        'path' => "javascript:;",
        'class' => "iconfont icon-14shujubeifen",
        'title' => "UI_PLATFORM_BACKUP_RECOVERY",
        'level' => 0,
        'child' => array(
			array(
                'name' => "newtask",
                'path' => "javascript:;",
                'class' => "icon-plus",
                'title' => "UI_BACKUP_NEW_TASK",
                'level' => 1,
                'child' => array(
					array(
                        'name' => "vmbackup",
                        'path' => "./content/vm/vmbackup.php",
                        'class' => "icon-action-redo",
                        'title' => "UI_PLATFORM_VMBACKUP",
                        'level' => 2,
                    ),
					array(
                        'name' => "vmrecover",
                        'path' => "./content/vm/vmrecover.php",
                        'class' => "icon-action-undo",
                        'title' => "UI_PLATFORM_VMRECOVER",
                        'level' => 2,
                    ),
					array(
                        'name' => "vminstantrecover",
                        'path' => "./content/vm/vminstantrecover.php",
                        'class' => "fa fa-bolt",
                        'title' => "UI_PLATFORM_VMINSTANTRECOVER",
                        'level' => 2,
                    ),
// 	                     array(
// 	                         'name' => "filebackup",
// 	                         'path' => "./content/fs/filebackup.php",
// 	                         'class' => "fa fa-file",
// 	                         'title' => "UI_PLATFORM_FILEBACKUP",
// 	                         'level' => 2,
// 	                     ),
// 	                     array(
// 	                         'name' => "filerecover",
// 	                         'path' => "./content/fs/filerecover.php",
// 	                         'class' => "fa fa-file",
// 	                         'title' => "UI_PLATFORM_FILERECOVER",
// 	                         'level' => 2,
// 	                     ),
				)
			),
			array(
                'name' => "taskmonitor",
                'path' => "javascript:;",
                'class' => "fa fa-laptop",
                'title' => "UI_PLATFORM_TASK_MONITOR",
                'level' => 1,
                'child' => array(
					array(
                        'name' => "current_job",
                        'path' => "./content/platform/jobs/current_job.php",
                        'class' => "fa fa-tachometer",
                        'title' => "UI_PLATFORM_CURRENT_JOB",
                        'level' => 2,
                    ),
					array(
                        'name' => "history_job",
                        'path' => "./content/platform/jobs/history_job.php",
                        'class' => "fa fa-history",
                        'title' => "UI_PLATFORM_HOSTORY_JOB",
                        'level' => 2,
                    ),
					array(
                        'name' => "vmdata",
                        'path' => "./content/vm/vmdata.php",
                        'class' => "fa fa-desktop",
                        'title' => "UI_PLATFORM_VM_DATA",
                        'level' => 2,
                    ),
                    array(
                        'name' => "vmdata_export",
                        'path' => "./content/vm/vmdata_export.php",
                        'class' => "fa fa-sign-out",
                        'title' => "UI_PLATFORM_VM_DATA_EXPORT",
                        'level' => 2,
                    	'child' => array(
                    		array(
                    			'name' => "new_vmdata_export",
                        		'path' => "./content/vm/new_vmdata_export.php",
                    			'level' => 3
                    		)
                    	)
                    ),
                    array(
                        'name' => "vmreport",
                        'path' => "./content/vm/vmreport.php",
                        'class' => "fa fa-newspaper-o",
                        'title' => "UI_PLATFORM_VM_REPORT",
                        'level' => 2,
                    ),
// 	                     array(
// 	                         'name' => "filedata",
// 	                         'path' => "./content/fs/filedata.php",
// 	                         'class' => "fa fa-file",
// 	                         'title' => "UI_PLATFORM_FILE_DATA",
// 	                         'level' => 2,
// 	                     ),
				)
			)
		)
	),
	//应急恢复演练
    array(
        'name' => "orch",
        'path' => "javascript:;",
        'class' => "iconfont icon-moniyanlian",
        'title' => "UI_PLATFORM_ORCHESTRATION",
        'level' => 1,
        'child' => array(
            array(
                'name' => "orch_environment",
                'path' => "./content/platform/manoeuvre/environment.php",
                'class' => "iconfont icon-fuwuqidizhiduixiang",
                'title' => "UI_PLATFORM_ORCH_ENVIRONMENT",
                'level' => 2,
                'child' => array(
                )
            ),
            array(
                'name' => "orch_plan",
                'path' => "./content/platform/manoeuvre/plan.php",
                'class' => "iconfont icon-yingjiyuan",
                'title' => "UI_PLATFORM_ORCH_PLAN",
                'level' => 2,
                'child' => array(
                )
            ),
            array(
                'name' => "orch_task",
                'path' => "./content/platform/manoeuvre/task.php",
                'class' => "iconfont icon-renwu",
                'title' => "UI_PLATFORM_ORCH_TASK",
                'level' => 2,
                'child' => array(
                	array(
                		'name' => "instant_recover",
                		'path' => "./content/platform/manoeuvre/instant_recover.php",
                		'level' => 3,
                	),
                )
            ),
            array(
                'name' => "orch_report",
                'path' => "./content/platform/manoeuvre/report.php",
                'class' => "iconfont icon-nreport",
                'title' => "UI_PLATFORM_ORCH_REPORT",
                'level' => 2,
                'child' => array(
                )
            ),
        )
    ),
	//资源配置
    array(
        'name' => "managerment",
        'path' => "javascript:;",
        'class' => "iconfont icon-guanli1",
        'title' => "UI_PLATFORM_SERVER_SRC",
        'level' => 1,
        'child' => array(
            array(
				'name' => "vcenter_manager",
				'path' => "./content/vm/vcenter_manager.php",
				'class' => "fa fa-codepen",
				'title' => "UI_PLATFORM_VCENTER_MANAGER",
				'level' => 2,
            	'child' => array(
            		array(
            			'name' => "add_vcenter",
            			'path' => "./content/vm/add_vcenter.php",
            			'level' => 3,
            				
            		),
//             		array(
//             			'name' => "modify_vcenter",
//             			'path' => "./content/vm/modify_vcenter.php",
//             			'level' => 3,
            			
//             		)
            	)
			),
			array(
				'name' => "storage_manager",
				'path' => "./content/platform/storage/storage_manager.php",
				'class' => "fa fa-cubes",
				'title' => "UI_PALTFORM_STORAGE_MANAGER",
				'level' => 2,
				'child' => array(
					array(
						'name' => "storage_data",
						'path' => "./content/platform/storage/storage_data.php",
						'level' => 3,
								 
					),
					array(
						'name' => "storage_add",
						'path' => "./content/platform/storage/storage_add.php",
						'level' => 3,
									
					),
				)
			),
			array(
				'name' => "storage_lanfree",
				'path' => "./content/platform/storage/storage_lanfree.php",
				'class' => "fa fa-gears",
				'title' => "UI_PALTFORM_STORAGE_LANFREE",
				'level' => 2,
				'child' => array(
					array(
						'name' => "storage_lanfree_add",
						'path' => "./content/platform/storage/storage_lanfree_add.php",
						'level' => 3,
								
					),
				)
			),
			array(
				'name' => "node_manager",
				'path' => "./content/platform/node/node_manager.php",
				'class' => "fa fa-sitemap",
				'title' => "UI_PALTFORM_NODE_MANAGER",
				'level' => 2,
			),
// 	             array(
// 	                 'name' => "agent_manager",
// 	                 'path' => "./content/platform/agent/agent_manager.php",
// 	                 'class' => "fa fa-puzzle-piece",
// 	                 'title' => "UI_PLATFORM_AGENT_MANAGER",
// 	                 'level' => 2,
// 	             ),
// 	             array(
// 	                 'name' => "my_agent",
// 	                 'path' => "./content/platform/agent/my_agent.php",
// 	                 'class' => "fa fa-puzzle-piece",
// 	                 'title' => "UI_PLATFORM_MY_AGENT",
// 	                 'level' => 2,
// 	             ),
			array(
				'name' => "users",
				'path' => "./content/platform/users/users.php",
				'class' => "icon-user",
				'title' => "UI_PALTFORM_MANAGER_USER",
				'level' => 2,
				'child' => array(
					array(
						'name' => "add_user",
						'path' => "./content/platform/users/add_user.php",
						'level' => 3,
					),
// 					array(
// 						'name' => "edit_user",
// 						'path' => "./content/platform/users/edit_user.php",
// 						'level' => 3,
// 					)
				)
			),
			array(
				'name' => "setting_manager",
				'path' => "./content/platform/settings/setting_manager.php",
				'class' => "fa fa-wrench",
				'title' => "UI_PLATFORM_SYSTEM_SET_MANAGER",
				'level' => 2,
			),
			array(
				'name' => "authorization_module",
				'path' => "./content/platform/settings/authorization_module.php",
				'class' => "icon-badge",
				'title' => "UI_PLATFORM_MODULE_AUTH",
				'level' => 2,
			),
        )
    ),
	//日志/告警
    array(
        'name' => "logalarm",
        'path' => "javascript:;",
        'class' => "icon-bell",
        'title' => "UI_PLATFORM_LOG_ALARM",
        'level' => 1,
        'child' => array(
            array(
				'name' => "job_log",
				'path' => "./content/platform/logs/job_log.php",
				'class' => "iconfont icon-tasklog",
				'title' => "UI_PLATFORM_JOB_LOG",
				'level' => 2,
			),
			array(
				'name' => "system_log",
				'path' => "./content/platform/logs/system_log.php",
				'class' => "iconfont icon-systemlog",
				'title' => "UI_PLATFORM_SYSTEM_LOG",
				'level' => 2,
			),
			array(
				'name' => "task_alarm",
				'path' => "./content/platform/alarm/task_alarm.php",
				'class' => "iconfont icon-taskalarm",
				'title' => "UI_PLATFORM_ALARM_TASK",
				'level' => 2,
			),
			array(
				'name' => "system_alarm",
				'path' => "./content/platform/alarm/system_alarm.php",
				'class' => "iconfont icon-systemalarm",
				'title' => "UI_PLATFORM_ALARM_SYSTEM",
				'level' => 2,
			),
        )
    ),

);