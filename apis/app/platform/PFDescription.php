<?php 
/**
 *平台描述定义
 *TODO 每一个定义都关联语言文件 
 */

return array(
    //模块类型
    'MODULE_TYPE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_PT'],
        Xphp::$_lang['WEB_PLATFORM_DES_VM'],
        Xphp::$_lang['WEB_PLATFORM_DES_FS'],
        Xphp::$_lang['WEB_PLATFORM_DES_DB'],
        Xphp::$_lang['WEB_PLATFORM_DES_OS'],
        Xphp::$_lang['WEB_PLATFORM_DES_CDP'] . " Server",
        Xphp::$_lang['WEB_PLATFORM_DES_CDP'] . " Client",
    	Xphp::$_lang['WEB_PLATFORM_DES_COPY'],
        Xphp::$_lang['WEB_PLATFORM_DES_COPY'] ,
        1000 => Xphp::$_lang['WEB_PLATFORM_DES_NODE'],
        10000 => Xphp::$_lang['UI_PLATFORM_DB_REALTIME_BACKUP'],
        10001 => Xphp::$_lang['UI_PLATFORM_CDP_FILE_REALTIME_MODE_BACKUP']
    ),
    //任务类型
    'TASKTYPEDES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'],
        Xphp::$_lang['WEB_PLATFORM_DES_RECOVERY'],
        Xphp::$_lang['WEB_PLATFORM_DES_DISK_BAK'],
        Xphp::$_lang['WEB_PLATFORM_DES_DISK_REC'],
        Xphp::$_lang['WEB_PLATFORM_DES_FILE_BAK'],
        Xphp::$_lang['WEB_VM_GRAIN_RECOVERY'],
        Xphp::$_lang['WEB_PLATFORM_DES_INSTANT_RECOVERY'],
        Xphp::$_lang['WEB_PLATFORM_DES_MOTION'],
        Xphp::$_lang['WEB_PLATFORM_DES_COPY'],
        Xphp::$_lang['WEB_PLATFORM_DES_SYNC'],//10
        Xphp::$_lang['WEB_PLATFORM_DES_RECOVER_EMERGENCY_PLAN'],
		Xphp::$_lang['WEB_PLATFORM_DES_BACKUP_DATE_EXPORT'],
        'CDP',
        Xphp::$_lang['UI_PLATFORM_CDP_RECOVERY'],
        Xphp::$_lang['UI_PLATFORM_CDP_INSTANT_RECOVERY'],
        Xphp::$_lang['UI_PLATFORM_CDP_MOTION'],
        Xphp::$_lang['UI_PLATFORM_COPY_VM'],
        Xphp::$_lang['UI_PLATFORM_VM_COPY_FETCH'],
        Xphp::$_lang['UI_PLATFORM_ARCHIVE'],
        Xphp::$_lang['UI_PLATFORM_ARCHIVE_FETCH'],//20
        Xphp::$_lang['UI_PLATFORM_DB_REALTIME_BACKUP'],
        Xphp::$_lang['UI_PLATFORM_DB_DATA_RECOVERY'],
        Xphp::$_lang['UI_SETTINGS_AUTH_CDP_TAKEOVER'],
        Xphp::$_lang['UI_PLATFORM_CDP_FILE_REALTIME_BACKUP'],
        Xphp::$_lang['UI_PLATFORM_CDP_FILE_DATA_RECOVERY'],
        Xphp::$_lang['UI_PLATFORM_FS_COPY'],
        Xphp::$_lang['UI_PLATFORM_FS_COPY_FETCH'],
        Xphp::$_lang['UI_PLATFORM_DB_BACKUP'],
        Xphp::$_lang['UI_PLATFORM_DB_RECOVERY'],
        Xphp::$_lang['UI_PLATFORM_DB_COPY'],//30
        Xphp::$_lang['UI_PLATFORM_DB_COPY_FETCH']
    ),
    //任务状态
    'TASKSTATUSDES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_WAITING'],
        Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'],
        Xphp::$_lang['WEB_PLATFORM_DES_PAUSE'],
        Xphp::$_lang['WEB_PLATFORM_DES_STOP'],
        Xphp::$_lang['WEB_PLATFORM_DES_STOPPING'],
        Xphp::$_lang['WEB_PLATFORM_DES_NETWORK_ERROR'],
        Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
        Xphp::$_lang['WEB_PLATFORM_DES_SYNC'],
        Xphp::$_lang['WEB_PLATFORM_DES_PREPARE'],
        Xphp::$_lang['WEB_PLATFORM_DES_PAUSEING'],
        Xphp::$_lang['WEB_PLATFORM_DES_STARTING'],
        Xphp::$_lang['WEB_PLATFORM_DES_FINISH'],
        Xphp::$_lang['WEB_PLATFORM_DES_IN_TAKEOVER'],
        Xphp::$_lang['WEB_PLATFORM_DES_TAKEOVER_STARTING'],
        Xphp::$_lang['WEB_PLATFORM_DES_TAKEOVER_STOPPING'],
    ),
    //任务类型
    'BACKUP_MODE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_FULL'],
        Xphp::$_lang['WEB_PLATFORM_DES_INCRIMENT'],
        Xphp::$_lang['WEB_PLATFORM_DES_DIFFRENCE'],
        Xphp::$_lang['WEB_PLATFORM_DES_LOG'],
        Xphp::$_lang['WEB_PLATFORM_DES_LOG_FILING'],
    ),
    //日志类型
    'LOG_LEVEL_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'],
        Xphp::$_lang['WEB_PLATFORM_DES_WARNING'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
    ),
    //系统授权
    'LISENCE_STATUS_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_SYSTEM_LISENCE_AUTHORIZED'],
        Xphp::$_lang['WEB_SYSTEM_LISENCE_UNAUTHORIZED'],
        Xphp::$_lang['WEB_SYSTEM_LISENCE_EXPIRE'],
        Xphp::$_lang['WEB_SYSTEM_LISENCE_INVALID'],
    ),
    //代理端状态
    'AGENTSTATUSDES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_AGENT_STATUS_ONLINE_REGISTED'],
        Xphp::$_lang['WEB_AGENT_STATUS_ONLINE_UNREGISTED'],
        Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE_REGISTED'],
        Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE_UNREGISTED'],
    ),
    //在线离线
    'ONLINEDES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'],
        Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'],
    ),
    //存储类型
    'STORAGETYPE' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_STORAGE_TYPE1'],
        Xphp::$_lang['UI_STORAGE_TYPE2'],
        Xphp::$_lang['UI_STORAGE_TYPE3'],
        Xphp::$_lang['UI_STORAGE_TYPE4'],
        Xphp::$_lang['UI_STORAGE_TYPE5'],
        Xphp::$_lang['UI_STORAGE_TYPE6'],
        Xphp::$_lang['UI_STORAGE_TYPE7'],
        Xphp::$_lang['UI_COPY_ALLOPATRIC_BACKUP_SYSTEM'],
        Xphp::$_lang['UI_STORAGE_TYPE9'],
        Xphp::$_lang['UI_STORAGE_TYPE10'],
        Xphp::$_lang['UI_STORAGE_TYPE11']
    ),
    //分区类型
    'PARTITIONTYPE' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_STORAGE_PARTITION_TYPE1'],
        Xphp::$_lang['UI_STORAGE_PARTITION_TYPE2'],
        Xphp::$_lang['UI_STORAGE_PARTITION_TYPE3'],
    ),
    //存储状态
    'STORAGESTATUS' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'],        //正常
        Xphp::$_lang['WEB_STORAGE_STATUS_CREATING'],    //创建中
        Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'],       //离线
        Xphp::$_lang['WEB_STORAGE_STATUS_UNMOUNT'],     //未挂载
    ),
    //告警等级
    'ALARM_LEVEL_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_GENERAL'],
        Xphp::$_lang['WEB_PLATFORM_DES_WARNING'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
    ),
    //告警响应描述
    'ALARM_RESPOND_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_ALARM_HAS_RESPONSE'],
        Xphp::$_lang['WEB_ALARM_NO_RESPONOSE'],
    ),
    //告警发送描述
    'ALARM_NOTICE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_ALARM_HAS_SEND'],
        Xphp::$_lang['WEB_ALARM_NO_SEND'],
    ),
    //演练代理状态
    'PROXY_STATUS_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_DEPLOY'],
        Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'],
        Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'],
        Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
    ),
    //代理类型
    'AGENT_TYPE_DES' => array(
        Xphp::$_lang['WEB_AGENT_VM_BACKUP_PLUGIN'],
        Xphp::$_lang['WEB_AGENT_BACKUP_NODE_EXTEND'],
        Xphp::$_lang['WEB_AGENT_FILE_BACKUP_PLUGIN'],
        Xphp::$_lang['WEB_AGENT_DB_TIMING'],
        Xphp::$_lang['WEB_AGENT_DB_REAL_TIME'],
        Xphp::$_lang['UI_PLATFORM_DB_PROTECT_PLUG']
    ),
	//系统升级
	'UPDATE_PATCH_DES' => array(
		Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
		Xphp::$_lang['UI_SETTINGS_UPDATE_ALREADY_UPLOAD'],
		Xphp::$_lang['UI_SETTINGS_UPDATE_FAILED'],
		Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_FAILED'],
		Xphp::$_lang['UI_SETTINGS_UPDATE_SUCCESS'],
		Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_TO_NODE'],
		Xphp::$_lang['UI_SETTINGS_UPDATE_WAITING'],
		Xphp::$_lang['UI_SETTINGS_UPDATING'],
		Xphp::$_lang['UI_SETTINGS_PATCH_INVALID']
			
	),
    //存储用途
    'STORAGE_USE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'],
        Xphp::$_lang['WEB_PLATFORM_DES_COPY'],
        Xphp::$_lang['UI_PLATFORM_ARCHIVE']
        
    ),
    //用户组类型user_group_type描述
    'USER_GROUP_TYPE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_USER_GROUP_DEFAULT'],
        Xphp::$_lang['UI_USER_GROUP_GLOBAL'],
        Xphp::$_lang['UI_USER_GROUP_TENANT'],
    ),
    //用户组状态lock_flag描述
    'USER_GROUP_LOCK_FLAG_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_ENABLE'],
        Xphp::$_lang['WEB_PLATFORM_DISABLE'],
    ),
    //角色状态lock_flag描述
    'ROLE_LOCK_FLAG_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_ENABLE'],
        Xphp::$_lang['WEB_PLATFORM_DISABLE'],
    ),
    
    
    //策略类型
    'BILLING_TYPE' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['BILLING_PAY_MONTH'],
        Xphp::$_lang['BILLING_PAY_YEAR'],
        Xphp::$_lang['BILLING_PAY_SIZE'],
    ),
    //策略模式
    'BILLING_MODE' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['BILLING_PAY_CAPACITY'],
        Xphp::$_lang['BILLING_PAY_NUM'],
    ),
    //数量类型
    'NUM_TYPE' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['BILLING_VM'],
        Xphp::$_lang['BILLING_FS'],
        Xphp::$_lang['BILLING_DB'],
    ),
    'INFORM_STATE_DES' => array(//是否通知
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_YES'],
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_NO'],
    ),
    'INFORM_PERIOD_DES' =>array( //通知周期
        Xphp::$_lang['BILLING_NO_INFORM'],
        Xphp::$_lang['BILLING_INFORM_DAY'],
        Xphp::$_lang['BILLING_INFORM_WEEK'],
        Xphp::$_lang['BILLING_INFORM_MONTH'],
        Xphp::$_lang['BILLING_INFORM_YEAR'],
    ),
    'LOCK_DESC' =>array( //锁定状态
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_ENABLE'],
        Xphp::$_lang['WEB_PLATFORM_DISABLE'],
    ),
    
   
);

?>