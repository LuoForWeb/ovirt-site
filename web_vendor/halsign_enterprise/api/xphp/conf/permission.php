<?php
/**
 * 权限定义配置文件
 */
return array(
    //超级管理员,所有权限
    "administrator" => array(
        "homepage", 
        "bakandrec", 
            "newtask", 
                "vmbackup", 
                "vmrecover", 
                "vminstantrecover", 
// 					"filebackup",
// 					"filerecover",
            "taskmonitor", 
                "current_job", 
                "history_job", 
                "vmdata",
                "vmdata_export",
                "vmreport", 
// 					"filedata",
        "orch", 
            "orch_environment", 
            "orch_plan", 
            "orch_task", 
            "orch_report", 
        "managerment", 
            "vcenter_manager", 
            "storage_manager", 
            "storage_lanfree", 
            "node_manager",
// 				"agent_manager",
// 				"my_agent", 
            "users", 
            "setting_manager", 
            "authorization_module", 
        "logalarm", 
            "job_log", 
            "system_log", 
            "task_alarm", 
            "system_alarm"
    ),
    
    //审计员
    "auditor" => array(
        "homepage", 
        "logalarm", 
            "job_log", 
            "system_log", 
            "task_alarm", 
            "system_alarm"
    ),
    
    
    
    //管理员
    "manager" => array(
        "homepage", 
        "managerment", 
            "vcenter_manager", 
            "storage_manager", 
            "storage_lanfree", 
            "node_manager", 
// 				"filebackup",
// 				"filerecover",
// 				"filedata",
// 				"agent_manager",
// 				"my_agent", 
            "users", 
            "setting_manager", 
            "authorization_module", 
        "logalarm", 
            "job_log", 
            "system_log", 
            "task_alarm", 
            "system_alarm"
    ),
    
    //操作员
    "operator" => array(
        "homepage", 
        "bakandrec", 
            "newtask", 
                "vmbackup", 
                "vmrecover", 
                "vminstantrecover",
// 	                 "filebackup",
// 	                 "filerecover",
            "taskmonitor", 
                "current_job", 
                "history_job", 
                "vmdata", 
                "vmdata_export",
                "vmreport", 
//                 	 "filedata",
        "orch", 
            "orch_environment", 
            "orch_plan", 
            "orch_task", 
            "orch_report", 
        "managerment", 
            "vcenter_manager",
//                	"my_agent",
        "logalarm", 
            "job_log",
            "task_alarm", 
    ),
    
    //标准版admin用户权限
    "admin" => array(
        "homepage", 
        "bakandrec", 
            "newtask", 
                "vmbackup", 
                "vmrecover", 
                "vminstantrecover", 
            "taskmonitor", 
                "current_job", 
                "history_job", 
                "vmdata",
                "vmreport", 
        "managerment", 
            "vcenter_manager", 
            "storage_manager", 
            "node_manager",
            "setting_manager", 
            "authorization_module", 
        "logalarm", 
            "job_log", 
            "system_log", 
            "task_alarm", 
            "system_alarm",
    ),

);

