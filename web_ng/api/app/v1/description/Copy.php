<?php

return [
    'COPY_TASK_STATUS' => array(
		xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
		xphp_get_lang('WEB_PUBLIC_SUCCESS'),
		xphp_get_lang('WEB_PUBLIC_FAILURE'),
		xphp_get_lang('WEB_PLATFORM_DES_RUNNING'),
		xphp_get_lang('WEB_VM_WAITING'),
		xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL'),	
	),
    //模块类型
    'MODULE_TYPE_DES' => array(
        xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        xphp_get_lang('WEB_PLATFORM_DES_PT'),
        xphp_get_lang('WEB_PLATFORM_DES_VM'),
        xphp_get_lang('WEB_PLATFORM_DES_FS'),
        xphp_get_lang('WEB_PLATFORM_DES_DB'),
        xphp_get_lang('WEB_PLATFORM_DES_OS'),
        xphp_get_lang('WEB_PLATFORM_DES_CDP') . " Server",
        xphp_get_lang('WEB_PLATFORM_DES_CDP') . " Client",
    	xphp_get_lang('WEB_PLATFORM_DES_COPY'),
        xphp_get_lang('WEB_PLATFORM_DES_COPY') ,
        1000 => xphp_get_lang('WEB_PLATFORM_DES_NODE'),
        10000 => xphp_get_lang('UI_PLATFORM_DB_REALTIME_BACKUP'),
        10001 => xphp_get_lang('UI_PLATFORM_CDP_FILE_REALTIME_MODE_BACKUP')
    ),
    //任务类型
    'TASK_TYPE_DES' => array(
        xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        xphp_get_lang('WEB_PLATFORM_DES_BACKUP'),
        xphp_get_lang('WEB_PLATFORM_DES_RECOVERY'),
        xphp_get_lang('WEB_PLATFORM_DES_DISK_BAK'),
        xphp_get_lang('WEB_PLATFORM_DES_DISK_REC'),
        xphp_get_lang('WEB_PLATFORM_DES_FILE_BAK'),
        xphp_get_lang('WEB_VM_GRAIN_RECOVERY'),
        xphp_get_lang('WEB_PLATFORM_DES_INSTANT_RECOVERY'),
        xphp_get_lang('WEB_PLATFORM_DES_MOTION'),
        xphp_get_lang('WEB_PLATFORM_DES_COPY'),
        xphp_get_lang('WEB_PLATFORM_DES_SYNC'),//10
        xphp_get_lang('WEB_PLATFORM_DES_RECOVER_EMERGENCY_PLAN'),
		xphp_get_lang('WEB_PLATFORM_DES_BACKUP_DATE_EXPORT'),
        'CDP',
        xphp_get_lang('UI_PLATFORM_CDP_RECOVERY'),
        xphp_get_lang('UI_PLATFORM_CDP_INSTANT_RECOVERY'),
        xphp_get_lang('UI_PLATFORM_CDP_MOTION'),
        xphp_get_lang('UI_PLATFORM_COPY_VM'),
        xphp_get_lang('UI_PLATFORM_VM_COPY_FETCH'),
        xphp_get_lang('UI_PLATFORM_ARCHIVE'),
        xphp_get_lang('UI_PLATFORM_ARCHIVE_FETCH'),//20
        xphp_get_lang('UI_PLATFORM_DB_REALTIME_BACKUP'),
        xphp_get_lang('UI_PLATFORM_DB_DATA_RECOVERY'),
        xphp_get_lang('UI_SETTINGS_AUTH_CDP_TAKEOVER'),
        xphp_get_lang('UI_PLATFORM_CDP_FILE_REALTIME_BACKUP'),
        xphp_get_lang('UI_PLATFORM_CDP_FILE_DATA_RECOVERY'),
        xphp_get_lang('UI_PLATFORM_FS_COPY'),
        xphp_get_lang('UI_PLATFORM_FS_COPY_FETCH'),
        xphp_get_lang('UI_PLATFORM_DB_BACKUP'),
        xphp_get_lang('UI_PLATFORM_DB_RECOVERY'),
        xphp_get_lang('UI_PLATFORM_DB_COPY'),//30
        xphp_get_lang('UI_PLATFORM_DB_COPY_FETCH')
    ),
    'STORAGE_TYPE' => array(
        xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        xphp_get_lang('UI_STORAGE_TYPE1'),
        xphp_get_lang('UI_STORAGE_TYPE2'),
        xphp_get_lang('UI_STORAGE_TYPE3'),
        xphp_get_lang('UI_STORAGE_TYPE4'),
        xphp_get_lang('UI_STORAGE_TYPE5'),
        xphp_get_lang('UI_STORAGE_TYPE6'),
        xphp_get_lang('UI_STORAGE_TYPE7'),
        xphp_get_lang('UI_COPY_ALLOPATRIC_BACKUP_SYSTEM'),
        xphp_get_lang('UI_STORAGE_TYPE9'),
        xphp_get_lang('UI_STORAGE_TYPE10'),
        xphp_get_lang('UI_STORAGE_TYPE11'),
        101 => "Huawei OceanProtect(NFS)",
        102 => "Huawei OceanProtect(CIFS)",
    ),
    
    //任务状态
    'TASK_STATUS_DES' => array(
         xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
         xphp_get_lang('WEB_PLATFORM_DES_WAITING'),
         xphp_get_lang('WEB_PLATFORM_DES_RUNNING'),
         xphp_get_lang('WEB_PLATFORM_DES_PAUSE'),
         xphp_get_lang('WEB_PLATFORM_DES_STOP'),
         xphp_get_lang('WEB_PLATFORM_DES_STOPPING'),
         xphp_get_lang('WEB_PLATFORM_DES_NETWORK_ERROR'),
         xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL'),
         xphp_get_lang('WEB_PLATFORM_DES_ERROR'),
         xphp_get_lang('WEB_PLATFORM_DES_SYNC'),
         xphp_get_lang('WEB_PLATFORM_DES_PREPARE'), //10
         xphp_get_lang('WEB_PLATFORM_DES_PAUSEING'),
         xphp_get_lang('WEB_PLATFORM_DES_STARTING'),
         xphp_get_lang('WEB_PLATFORM_DES_FINISH'),
         xphp_get_lang('WEB_PLATFORM_DES_IN_TAKEOVER'),
         xphp_get_lang('WEB_PLATFORM_DES_TAKEOVER_STARTING'),
         xphp_get_lang('WEB_PLATFORM_DES_TAKEOVER_STOPPING'),
	     xphp_get_lang('WEB_PLATFORM_DES_SUCCESSED'),
    ),
    "COPY_MODE" => [
        1 => xphp_get_lang('UI_COPY_MODE_MIRROR'), 
        xphp_get_lang('UI_COPY_MODE_COMBINATION'), 
    ]
];