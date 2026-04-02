<?php
/**
 * 系统备份配置文件
 */
return  array
(
    "BakTreeConf" => array(
        
        2 => array(
            "title" => Xphp::$_lang['UI_PLATFORM_SAFETY'],
            "name" => "safety",
            "bakFuntion" => "backupSecurityInfo",
            "recFunction" => "recoverySecurityInfo",
            "child" => array(
                201 => array("title" => Xphp::$_lang['UI_PLATFORM_USER'], "name" => "safety_user", "bakFuntion" => "backupSafetyUser", "recFunction" => "recoverySafetyUser"),
                array("title" => Xphp::$_lang['UI_PLATFORM_SAFETY_USER_GROUP'], "name" => "safety_usergroup", "bakFuntion" => "backupSafetyUserGroup", "recFunction" => "recoverySafetyUserGroup"),
                array("title" => Xphp::$_lang['UI_PLATFORM_SAFETY_ROLE'], "name" => "safety_role", "bakFuntion" => "backupSafetyRole", "recFunction" => "recoverySafetyRole"),
                array("title" => Xphp::$_lang['UI_PLATFORM_SAFETY_DOMAIN'], "name" => "safety_domain", "bakFuntion" => "backupSafetyDomain", "recFunction" => "recoverySafetyDomain"),
            ),
        ),
        
        1 => array(
            "title" => Xphp::$_lang['UI_PLATFORM_INFRASTRUCTURE'],
            "name" => "infrastructur",
            "bakFuntion" => "backupInfrastructure",         //备份调用的方法
            "recFunction" => "recoveryInfrastructure",      //恢复调用的方法
            "child" => array(
                101 => array("title" => Xphp::$_lang['UI_PALTFORM_NODE'], "name" => "node_manager", "bakFuntion" => "backupNodeManager", "recFunction" => "recoveryNodeManager"),
                array("title" => Xphp::$_lang['UI_DATACENTER_BACKUP_STORAGE'], "name" => "storage_manager", "bakFuntion" => "backupStorageManager", "recFunction" => "recoveryStorageManager"),
                array("title" => Xphp::$_lang['UI_PALTFORM_STORAGE_LANFREE'], "name" => "storage_lanfree", "bakFuntion" => "backupStorageLanFree", "recFunction" => "recoveryStorageLanFree"),
                array("title" => Xphp::$_lang['UI_GLOBAL_STRATEGY_GROUP'], "name" => "global_strategy", "bakFuntion" => "backupGlobalStrategy", "recFunction" => "recoveryGlobalStrategy"),
                array("title" => Xphp::$_lang['UI_PLATFORM_RESOURCE_GROUP'], "name" => "resource_group", "bakFuntion" => "backupResourceGroup", "recFunction" => "recoveryResourceGroup"),
            ),
        ),
        
        
        /*暂时先不做这个,后面来
        3 => array(
            "title" => "系统配置",
            "name" => "setting_manager",
            "bakFuntion" => "backupSystemInfo",
            "recFunction" => "recoverySystemInfo",
            "child" => array(
                301 => array("title" => "域名解析", "name" => "system_dns", "bakFuntion" => "backupSystemDns", "recFunction" => "recoverySystemDns"),
                array("title" => "网卡聚合", "name" => "nic_teaming", "bakFuntion" => "backupNicTeaming", "recFunction" => "recoveryNicTeaming"),
                array("title" => "账户安全", "name" => "account_safe", "bakFuntion" => "backupAccountSafe", "recFunction" => "recoveryAccountSafe"),
                array("title" => "存储安全", "name" => "storage_safe", "bakFuntion" => "backupStorageSage", "recFunction" => "recoveryStorageSage"),
                array("title" => "可视化配置", "name" => "visual_config", "bakFuntion" => "backupVisualConfig", "recFunction" => "recoveryVisualConfig"),
            ),
        ),
        */
        
        4 => array(
            "title" => Xphp::$_lang['UI_PLATFORM_MULTI_TENANT'],
            "name" => "multitenant",
            "bakFuntion" => "backupMultitenant",
            "recFunction" => "recoveryMultitenant",
            "child" => array(
                401 => array("title" => Xphp::$_lang['UI_PLATFORM_TENANT'], "name" => "tenant_manager", "bakFuntion" => "backupTenantManager", "recFunction" => "recoveryTenantManager"),
                array("title" => Xphp::$_lang['UI_PLATFORM_BILLING'], "name" => "billing_manager", "bakFuntion" => "backupBillingManager", "recFunction" => "recoveryBillingManager"),
            ),
        ),
        
        5 => array(
            "title" => Xphp::$_lang['UI_PLATFORM_BACKUP_RESOURCE'],
            "name" => "backup_resource",
            "bakFuntion" => "backupBackupResource",
            "recFunction" => "recoveryBackupResource",
            "child" => array(
                501 => array("title" => Xphp::$_lang['UI_PLATFORM_VCENTER'], "name" => "vcenter_manager", "bakFuntion" => "backupVcenterManager", "recFunction" => "recoveryVcenterManager"),
                array("title" => Xphp::$_lang['UI_PLATFORM_TENANT_DATABASE_AGENT'], "name" => "db_agent_manager", "bakFuntion" => "backupDbAgentManager", "recFunction" => "recoveryDbAgentManager"),
                array("title" => Xphp::$_lang['UI_PLATFORM_CDP_AGENT'], "name" => "cdp_agent_manager", "bakFuntion" => "backupCDPAgentManager", "recFunction" => "recoveryCDPAgentManager"),
                array("title" => Xphp::$_lang['UI_PLATFORM_APPLIANCE'], "name" => "appliance", "bakFuntion" => "backupApplianceManager", "recFunction" => "recoveryApplianceManager"),
            ),
        ),
        
        6 => array(
            "title" => Xphp::$_lang['UI_PLATFORM_JOB_MONITOR'],
            "name" => "task",
            "bakFuntion" => "backupJob",
            "recFunction" => "recoveryJob",
            "child" => array(
                601 => array("title" => Xphp::$_lang['UI_PLATFORM_CURRENT_JOB'], "name" => "current_job", "bakFuntion" => "backupCurrentJob", "recFunction" => "recoveryCurrentJob"),
                array("title" => Xphp::$_lang['UI_PLATFORM_HOSTORY_JOB'], "name" => "history_job", "bakFuntion" => "backupHistoryJob", "recFunction" => "recoveryHistoryJob"),
            ),
        ),
        
        7 => array(
            "title" => Xphp::$_lang['UI_PLATFORM_ALARM'],
            "name" => "alarm",
            "bakFuntion" => "backupAlarm",
            "recFunction" => "recoveryAlarm",
            "child" => array(
                701 => array("title" => Xphp::$_lang['UI_PLATFORM_ALARM_TASK'], "name" => "task_alarm", "bakFuntion" => "backupTaskAlarm", "recFunction" => "recoveryTaskAlarm"),
                array("title" => Xphp::$_lang['UI_PLATFORM_ALARM_SYSTEM'], "name" => "system_alarm", "bakFuntion" => "backupSystemAlarm", "recFunction" => "recoverySystemAlarm"),
            ),
        ),
        
        8 => array(
            "title" => Xphp::$_lang['UI_PLATFORM_LOG'],
            "name" => "log",
            "bakFuntion" => "backupLog",
            "recFunction" => "recoveryLog",
            "child" => array(
                801 => array("title" => Xphp::$_lang['UI_PLATFORM_JOB_LOG'], "name" => "job_log", "bakFuntion" => "backupJobLog", "recFunction" => "recoveryJobLog"),
                array("title" => Xphp::$_lang['UI_PLATFORM_SYSTEM_LOG'], "name" => "system_log", "bakFuntion" => "backupSystemLog", "recFunction" => "recoverySystemLog"),
            ),
        ),
    ),
    
    "RecSrcType" => array(
        'autoSrc' => 0,     //自动备份源
        'uploadSrc' => 1,   //手动上传源
    ),
);

?>