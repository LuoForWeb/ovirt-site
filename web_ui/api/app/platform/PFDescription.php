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
        Xphp::$_lang['WEB_PLATFORM_DES_COPY'],
        Xphp::$_lang['UI_HOMEPAGE_SERVER_CDP'],
        Xphp::$_lang['WEB_PLATFORM_DES_NAS'],
        Xphp::$_lang['UI_HOMEPAGE_DB_CDP'],
        Xphp::$_lang['WEB_PLATFORM_DES_STORAGE_SERVER'],  //存储服务
//        14 => Xphp::$_lang['WEB_PLATFORM_DES_EXCHANGE'],
        Xphp::$_lang['WEB_PLATFORM_DES_M365'],  //M365
        Xphp::$_lang['UI_NODE_MANAGER'],  //节点管理
        Xphp::$_lang['WEB_PLATFORM_DES_COPY'],  //副本
        Xphp::$_lang['WEB_PLATFORM_DES_PUBLIC_CLOUD'],  //公有云
        26 => Xphp::$_lang['UI_FILE_COPY'],
        28 => Xphp::$_lang['UI_PLATFORM_K8S'],
        30 => Xphp::$_lang['WEB_PLATFORM_DES_SURE_BACKUP'], // 数据验证

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
        Xphp::$_lang['WEB_PLATFORM_DES_COPY'],
        Xphp::$_lang['UI_PLATFORM_VM_COPY_RECOVERY'],
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
        Xphp::$_lang['UI_PLATFORM_DB_COPY_FETCH'],//31
        Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'], //卷CDP备份
        Xphp::$_lang['WEB_PLATFORM_DES_RECOVERY'], //卷CDP恢复
        Xphp::$_lang['UI_PLATFORM_VOL_CDP_TAKEOVER_TITLE'], //卷CDP接管
        Xphp::$_lang['UI_PLATFORM_BACKUP'],  //35 操作系统备份
        Xphp::$_lang['UI_PLATFORM_RECOVER'],  // 36 操作系统恢复
        37 => Xphp::$_lang['UI_PLATFORM_DATA_VERIFICATION'],
        Xphp::$_lang['UI_PLATFORM_OS_COPY'],
        Xphp::$_lang['UI_PLATFORM_OS_COPY_FETCH'],
        Xphp::$_lang['UI_PLATFORM_OS_ARCHIVE'],//预留
        Xphp::$_lang['UI_PLATFORM_OS_ARCHIVE_FETCH'],//预留
        Xphp::$_lang['UI_NAS_BACKUP'],//预留
        Xphp::$_lang['UI_NAS_RECOVERY'],//预留
        Xphp::$_lang['UI_PLATFORM_NAS_COPY'],//44  nas副本
        Xphp::$_lang['UI_PLATFORM_NAS_COPY_FETCH'],//45
        Xphp::$_lang['UI_JOB_TYPE_CDP_DB_BACKUP'],//46
        Xphp::$_lang['UI_JOB_TYPE_CDP_DB_RECOVERY'],//47
        49 => Xphp::$_lang['WEB_PLATFORM_DES_INSTANT_RECOVERY'],
        50 => Xphp::$_lang['WEB_PLATFORM_DES_MOTION'],
        51 => Xphp::$_lang['UI_PLATFORM_SYNC_CBR'], //51 华为CBR同步
        52 => Xphp::$_lang['UI_PLATFORM_RECOVERY'], //52 跨平台恢复
        53 => Xphp::$_lang['WEB_PLATFORM_DES_INSTANT_RECOVERY'], //53 瞬时恢复
        54 => Xphp::$_lang['WEB_PLATFORM_DES_MOTION'], //54 迁移
        55 => Xphp::$_lang['UI_PLATFORM_GRAIN_RECOVERY'], //55 细粒度恢复
        56 => Xphp::$_lang['UI_PLATFORM_BACKUP'], //56 k8s备份
        57 => Xphp::$_lang['UI_PLATFORM_RECOVER'], //57 k8s恢复
        62 => Xphp::$_lang['UI_PLATFORM_COPY'], //文件复制
        63 => Xphp::$_lang['WEB_PLATFORM_DES_CONTRAST'], //文件对比
		64 => Xphp::$_lang['WEB_PLATFORM_DES_DRILL'], //演练
        65 => Xphp::$_lang['WEB_PLATFORM_REPLICATION'], //复制
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
        10 => Xphp::$_lang['WEB_PLATFORM_DES_PREPARE'], //10
        Xphp::$_lang['WEB_PLATFORM_DES_PAUSEING'],
        Xphp::$_lang['WEB_PLATFORM_DES_STARTING'],
        Xphp::$_lang['WEB_PLATFORM_DES_FINISH'],
        Xphp::$_lang['WEB_PLATFORM_DES_IN_TAKEOVER'],
        Xphp::$_lang['WEB_PLATFORM_DES_TAKEOVER_STARTING'],
        Xphp::$_lang['WEB_PLATFORM_DES_TAKEOVER_STOPPING'],
	    Xphp::$_lang['WEB_PLATFORM_DES_SUCCESSED'],
        Xphp::$_lang['WEB_PLATFORM_DES_CREATING'],
        19 => Xphp::$_lang['WEB_PLATFORM_DES_PENDING'],
        20 => Xphp::$_lang['WEB_PLATFORM_DES_DELETING'],
        21 => Xphp::$_lang['WEB_PLATFORM_DES_CLEANING'],
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
        Xphp::$_lang['UI_STORAGE_TYPE11'],
        Xphp::$_lang['UI_STORAGE_TYPE12'],
        13 => Xphp::$_lang['UI_STORAGE_TYPE_FILE'],
        101 => "Huawei OceanProtect(NFS)",
        102 => "Huawei OceanProtect(CIFS)",
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
        Xphp::$_lang['WEB_STORAGE_STATUS_NORMAL'],        //正常
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
        Xphp::$_lang['WEB_AGENT_DB_TIMING'],
        Xphp::$_lang['WEB_AGENT_VOL_CDP_CLIENT'],
        Xphp::$_lang['WEB_AGENT_DB_REAL_TIME'],
        Xphp::$_lang['UI_PLATFORM_DB_PROTECT_PLUG']
    ),
	//系统升级
	'UPDATE_PATCH_DES' => array(
		Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
	    Xphp::$_lang['UI_SETTINGS_UPDATE_ALREADY_UPLOAD'], //"已上传, 等待升级中",1
	    Xphp::$_lang['UI_SETTINGS_UPDATE_FAILED'],        //"升级失败",2
	    Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_FAILED'],//"上传失败",3
	    Xphp::$_lang['UI_SETTINGS_UPDATE_SUCCESS'],    //"升级成功",4
	    Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_TO_NODE'],//"升级包正在上传到节点",5
	    Xphp::$_lang['UI_SETTINGS_UPDATE_WAITING'],       //"等待升级中",6
	    Xphp::$_lang['UI_SETTINGS_UPDATING'],             //"升级中",7
	    Xphp::$_lang['UI_SETTINGS_PATCH_INVALID'],         //"升级包无效",8
	    Xphp::$_lang['UI_SETTINGS_PATCH_INVALID_ING'],         //"升级包检查中",9
	    Xphp::$_lang['UI_SETTINGS_PATCH_INVALID_COMPLETE'],         //"升级包检查完成",10
        Xphp::$_lang['UI_SETTINGS_PATCH_SPACE_NOT_ENOUGH_TO_UNCOMPRESS'],         //11 系统空间不足，无法解压升级包
        Xphp::$_lang['UI_SETTINGS_PATCH_SPACE_NOT_ENOUGH_TO_DOWNLOAD'],         //12 系统空间不足，无法下载升级包
	),

    //存储用途
    'STORAGE_USE_DES' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_BACKUP'],
        Xphp::$_lang['WEB_PLATFORM_DES_COPY'],
        Xphp::$_lang['UI_PLATFORM_ARCHIVE'],
        'NAS'

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
    //任务运行阶段
    'CDP_TASK_RUNNING_STAGE' => array(
        0 => "--",
        1 => Xphp::$_lang['WEB_PLATFORM_DES_WAIT_EXECUTE'],
        20 => Xphp::$_lang['WEB_PLATFORM_DES_INITIAL_SYN'],
        21 => Xphp::$_lang['WEB_PLATFORM_DES_BACKUP_TIME_SYN'],
        22 => Xphp::$_lang['WEB_VOL_CDP_TASK_BACKUP_WAIT_CONVERT_TO_CDP'],
        40 => Xphp::$_lang['WEB_PLATFORM_DES_SERVERDATA_CONSIST_CHECK'],
        41 => Xphp::$_lang['WEB_PLATFORM_DES_STANDBY_CONSIST_CHECK'],
        42 => Xphp::$_lang['WEB_PLATFORM_DES_SERVERDATA_REALTIME_CONSIST_CHECK'],
        60 => Xphp::$_lang['WEB_PLATFORM_DES_IN_TAKEOVER'],
        61 => Xphp::$_lang['WEB_PLATFORM_DES_TAKEOVER_STARTING'],
        80 => Xphp::$_lang['WEB_PLATFORM_DES_BACK_INITIAL_SYN'],
        81 => Xphp::$_lang['WEB_PLATFORM_DES_BACK_TIME_SYN'],
        82 => Xphp::$_lang['WEB_TASK_VOL_CDP_LOG_DESC_KEY_FAILBACK_STARTING'],
    ),
    //客户端部署状态
    "AGENT_DEPLOY_STATUS" => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_CLIENT_DEPLOYING'],
        Xphp::$_lang['WEB_CLIENT_DEPLOY_SUCCESS'],
        Xphp::$_lang['WEB_CLIENT_DEPLOY_FAILED'],
        Xphp::$_lang['WEB_CLIENT_UPGRADING'],
        Xphp::$_lang['WEB_CLIENT_UPGRADE_SUCCESS'],
        Xphp::$_lang['WEB_CLIENT_UPGRADE_FAILED'],
    ),
    //用于文件详情--多客户端状态
    "AGENT_TASK_STATUS" => array (
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_WAITING'],
        Xphp::$_lang['UI_FILE_JOB_SCANNING'],     //scanning
        Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'],     //running
        Xphp::$_lang['WEB_PLATFORM_DES_SUCCESSED'],     //success
        Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'],     //abnormal
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],     //error
        Xphp::$_lang['UI_FILE_AGENT_TASK_STATUS_DOCUMENTATION'],     //归档中
    ),
    //新增数据完整性校验
    "INTEGRITY_CHECK_DES"=>array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_PUBLIC_ON_TWO'], //开启
        Xphp::$_lang['UI_PUBLIC_OFF_TWO'], //关闭
    ),
    //数据完整性校验方法
    "INTEGRITY_CHECKTYPE_DES"=>array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        "MD5",
        "CRC"
    ),

    // 副本任务状态
    "COPY_TASK_STATUS" => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PUBLIC_SUCCESS'],
        Xphp::$_lang['WEB_PUBLIC_FAILURE'],
        Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'],
        Xphp::$_lang['WEB_VM_WAITING'],
        Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'],
    ),
    // 恢复类别
    'RECOVERY_TYPE_DES' => [
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_RECOVERY_SPECIFIC_TIMEPOINT'],
        Xphp::$_lang['UI_RECOVERY_LATEST_TIMEPOINT'],
    ],
    // 普通任务任务阶段
    'COMMON_STAGE' => [
        // 公共
        1 => Xphp::$_lang['WEB_PLATFORM_TYPE_PREPARE'], // 任务准备 同【运行中】状态，无特殊限制
        2 => Xphp::$_lang['WEB_PLATFORM_TYPE_DATA_TRANSPORT'], // 数据传输 同【运行中】状态，无特殊限制
        3 => Xphp::$_lang['WEB_PLATFORM_TYPE_INTEGRITY_CHECK'], // 完整性校验 同【运行中】状态，无特殊限制
        4 => Xphp::$_lang['WEB_PLATFORM_TYPE_VIRUS_CHECK'], // 病毒查杀 同【运行中】状态，无特殊限制
        // 虚拟机
        100 => Xphp::$_lang['WEB_PLATFORM_REMOVE_RESIDUAL_RESOURCES'], // 清除残留资源
        101 => Xphp::$_lang['WEB_PLATFORM_CREATE_SNAPSHOT'], // 创建快照
        102 => Xphp::$_lang['WEB_PLATFORM_DEL_SNAPSHOT'], // 删除快照
        103 => Xphp::$_lang['WEB_PLATFORM_CALCULCATE_EFFECTIVE_DATA'], // 计算有效数据
        104 => Xphp::$_lang['WEB_PLATFORM_PREPARE_BACKUP_RESOURCES'], // 准备备份资源
        105 => Xphp::$_lang['WEB_PLATFORM_DESTROY_BACKUP_RESOURCES'], // 销毁备份资源
        106 => Xphp::$_lang['WEB_PLATFORM_PREPARE_RECOVERY_RESOURCES'], // 准备恢复资源
        107 => Xphp::$_lang['WEB_PLATFORM_DESTROY_RECOVERY_RESOURCES'], // 销毁恢复资源
        108 => Xphp::$_lang['WEB_PLATFORM_VOLUME_MOUNTING'], // 卷挂载
        109 => Xphp::$_lang['WEB_PLATFORM_VOLUME_UNINSTALLATION'], // 卷卸载
        110 => Xphp::$_lang['WEB_PLATFORM_GENERATE_IMAGE'], // 生成镜像
        111 => Xphp::$_lang['WEB_PLATFORM_CONVERT_IMAGE_FORMAT'], // 转换镜像格式
        112 => Xphp::$_lang['WEB_PLATFORM_UPLOAD_IMAGE'], // 上传镜像
        113 => Xphp::$_lang['WEB_PLATFORM_GENERATE_TIME_POINT'], // 生成时间点
        114 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_RETENTION_POLICY'], // 执行保留策略
        115 => Xphp::$_lang['WEB_PLATFORM_CREATE_STORAGE'], // 创建存储
        116 => Xphp::$_lang['WEB_PLATFORM_DESTROY_STORAGE'], // 销毁存储
        117 => Xphp::$_lang['WEB_PLATFORM_CREATE_VIRTUAL_MACHINE'], // 创建虚拟机
        118 => Xphp::$_lang['WEB_PLATFORM_DESTROY_VIRTUAL_MACHINE'], // 销毁虚拟机
        119 => Xphp::$_lang['WEB_PLATFORM_ADVANCED_RECOVERY_OPERATIONS'], // 高级恢复操作
        120 => Xphp::$_lang['WEB_PLATFORM_GENERATE_DIRECTORY_TREE'], // 生成目录树结构
        121 => Xphp::$_lang['WEB_PLATFORM_CBT_PROCESSING'], // cbt处理
        122 => Xphp::$_lang['WEB_PLATFORM_TIMEPOINT_DATA_TRANSMISSION'], // 时间点数据传输
        123 => Xphp::$_lang['WEB_PLATFORM_TRANSMISSION_OF_METADATA'], // 元数据信息传输
        124 => Xphp::$_lang['WEB_PLATFORM_CACHE_DATA_TRANSMISSION'], // 缓存数据传输
        // 文件
        200 => Xphp::$_lang['WEB_PLATFORM_LOAD_TARGET_OBJECT'], // 加载目标对象信息
        201 => Xphp::$_lang['WEB_PLATFORM_INITIALIZE_TARGET_OBJECT'], // 初始化目标对象上下文
        202 => Xphp::$_lang['WEB_PLATFORM_START_SCAN'], // 启动扫描
        203 => Xphp::$_lang['WEB_PLATFORM_SYNCHRONIZE_DATA_POOL'], // 同步数据池
        204 => Xphp::$_lang['WEB_PLATFORM_GENERATE_TIME_POINT'], // 生成时间点
        205 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_RETENTION_POLICY'], // 执行保留策略
        206 => Xphp::$_lang['WEB_PLATFORM_CLEAN_RESOURCES'], // 清理资源
        207 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_ARCHIVING'], // 执行归档
        208 => Xphp::$_lang['WEB_PLATFORM_GENERATE_HISTORY_TASKS'], // 生成历史任务
        // 操作系统
        301 => Xphp::$_lang['WEB_PLATFORM_CLEAN_CLIENT_RESOURCES'], // 清理客户端资源
        302 => Xphp::$_lang['WEB_PLATFORM_CREATE_SNAPSHOT'], // 创建快照
        303 => Xphp::$_lang['WEB_PLATFORM_CALCULATE_EFFECTIVE_DATA'], // 计算有效数据
        304 => Xphp::$_lang['WEB_PLATFORM_GENERATE_TIME_POINT'], // 生成时间点
        305 => Xphp::$_lang['WEB_PLATFORM_DEL_SNAPSHOT'], // 删除快照
        306 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_RETENTION_POLICY'], // 执行保留策略
        307 => Xphp::$_lang['WEB_PLATFORM_GET_AGENT_OF_RECOVERY'], // 获取需要恢复的主机信息
        308 => Xphp::$_lang['WEB_PLATFORM_ADVANCED_RECOVERY_OPERATIONS'], // 高级恢复操作
        309 => Xphp::$_lang['WEB_PLATFORM_CREATE_STORAGE'], // 创建存储
        310 => Xphp::$_lang['WEB_PLATFORM_DESTROY_STORAGE'], // 销毁存储
        311 => Xphp::$_lang['WEB_PLATFORM_CACHE_DATA_TRANSMISSION'], // 缓存数据传输
        // 数据库
        400 => Xphp::$_lang['WEB_PLATFORM_CHECK_STATUS_OF_DATABASE'], // 检查数据库运行状态
        401 => Xphp::$_lang['WEB_PLATFORM_CHECK_STATUS_OF_LOG'], // 检查日志状态
        402 => Xphp::$_lang['WEB_PLATFORM_CREATE_SNAPSHOT'], // 创建快照
        403 => Xphp::$_lang['WEB_PLATFORM_DEL_SNAPSHOT'], // 删除快照
        404 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_BACKUP_COMMAND_OF_DATABASE'], // 执行备份数据库命令
        405 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_RECOVERY_COMMAND_OF_DATABASE'], // 执行恢复数据库命令
        406 => Xphp::$_lang['WEB_PLATFORM_CLEAN_CLIENT_RESOURCES'], // 清理客户端资源
        407 => Xphp::$_lang['WEB_PLATFORM_GENERATE_TIME_POINT'], // 生成时间点
        408 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_RETENTION_POLICY'], // 执行保留策略
        409 => Xphp::$_lang['WEB_PLATFORM_CREATE_DATABASE'], // 创建数据库/实例
        410 => Xphp::$_lang['WEB_PLATFORM_DEL_DATABASE'], // 删除数据库/实例
        411 => Xphp::$_lang['WEB_PLATFORM_BACKUP_CONFIG_OF_DATABASE'], // 备份数据库配置文件
        412 => Xphp::$_lang['WEB_PLATFORM_RECOVERY_CONFIG_OF_DATABASE'], // 恢复数据库配置文件
        413 => Xphp::$_lang['WEB_PLATFORM_BACKUP_FILE_OF_DATABASE'], // 备份数据库数据文件
        414 => Xphp::$_lang['WEB_PLATFORM_RECOVERY_FILE_OF_DATABASE'], // 恢复数据库数据文件
        415 => Xphp::$_lang['WEB_PLATFORM_BACKUP_LOG_OF_DATABASE'], // 备份数据库日志文件
        416 => Xphp::$_lang['WEB_PLATFORM_RECOVERY_LOG_OF_DATABASE'], // 备份数据库日志文件
        417 => Xphp::$_lang['WEB_PLATFORM_DB_TASK_STAGE_START_DATABASE'], // 启动数据库实例
        418 => Xphp::$_lang['WEB_PLATFORM_DB_TASK_STAGE_STOP_DATABASE'], // 停止数据库实例
        419 => Xphp::$_lang['WEB_PLATFORM_DB_TASK_STAGE_SYNCHRONIZE_CLUSTER'], // 同步集群数据
        420 => Xphp::$_lang['WEB_PLATFORM_DB_TASK_STAGE_CHECK_DATABASE'], // 备份前检查数据库
        421 => Xphp::$_lang['WEB_PLATFORM_DB_TASK_STAGE_CROSSCHECK_ARCHIVE_LOG'], // 校验归档日志
        // 应用
        500 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_SAVE_TIMEPOINT'], // 保存时间点
        501 => Xphp::$_lang['WEB_PLATFORM_EXECUTE_RETENTION_POLICY'], // 执行保留策略
        502 => Xphp::$_lang['WEB_PLATFORM_GENERATE_TIME_POINT'], // 生成时间点
        520 => Xphp::$_lang['WEB_PLATFORM_CLEAN_CLIENT_RESOURCES'], // 清理客户端资源
        521 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_CALCULATE_RECOVERY_OBJECTS'], // 计算需要恢复的对象
        522 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_GET_AVAILABLE_CLIENT'], // 获取可用客户端
        580 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_EXEC_PRE_HOOK'], // 执行任务前置HOOK脚本
        581 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_EXEC_POST_HOOK'], // 执行任务后置HOOK脚本
        582 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_EXEC_POST_FAILED_HOOK'], // 执行任务失败后置HOOK脚本
        583 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_BACKUP_RESOURCES'], // 资源备份
        584 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_RECOVER_RESOURCES'], // 资源恢复
        585 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_CREATE_PVC_SNAPSHOT'], // 创建PVC快照
        586 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_PREPARE_PVC_INSTANCES'], // 准备PVC实例
        587 => Xphp::$_lang['WEB_PLATFORM_APP_TASK_STAGE_TYPE_CLEAN_TMP_RESOURCES'], // 清理临时资源
    ],
);

?>