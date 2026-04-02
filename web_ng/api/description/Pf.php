<?php

/**
 *平台描述定义
 *TODO 每一个定义都关联语言文件
 */

return array(
    //模块类型
    'MODULE_TYPE_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_PT'),
        ('UI_PLATFORM_VM_VIRTUAL'),
        ('WEB_PLATFORM_DES_FS'),
        ('WEB_PLATFORM_DES_DB'),
        ('UI_PLATFORM_MACHINE_TIMED'),
        ('WEB_PLATFORM_DES_CDP') . ' Server',
        ('WEB_PLATFORM_DES_CDP') . ' Client',
        ('WEB_PLATFORM_DES_COPY'),
        ('WEB_PLATFORM_DES_COPY'),
        ('UI_HOMEPAGE_SERVER_CDP'),
        ('WEB_PLATFORM_DES_NAS'),
        12 => ('UI_JOB_TYPE_CDP_DB_BACKUP'),
        13 => ('WEB_PLATFORM_DES_STORAGE_SERVER'),
        14 => 'Microsoft365',
        15 => ('UI_NODE_MANAGER'), //节点管理，对应后台枚举BD_MODULE_TYPE_NODE_MANAGER
        16 => ('WEB_PLATFORM_DES_COPY'),  //副本
        17 => ('WEB_PLATFORM_DES_PUBLIC_CLOUD'),
        19 => ('UI_JOB_STRATEGY'),
        20 => ('UI_PLATFORM_TENANT_DATABASE_AGENT'),
        21 => ('WEB_PLATFORM_DES_CLUSTER'),
        22 => ('WEB_PLATFORM_ELITE_RECOVERY'),  //高级恢复模块
        23 => ('UI_PLATFORM_DATA_MANAGER'),  //备份数据管理
        24 => ('WEB_PLATFORM_DES_TOOL'),
        25 => ('WEB_PLATFORM_DES_CLUSTER'),  //集群
        26 => ('UI_FILE_COPY'),
        28 => ('UI_PLATFORM_K8S'),
        30 => ('UI_PLATFORM_DATA_VERIFICATION'),
        40 => ('WEB_PLATFORM_DAEMON_DES_PT'),
        1000 => ('WEB_PLATFORM_DES_NODE'),  //节点
        10000 => ('UI_PLATFORM_DB_REALTIME_BACKUP'),
        10001 => ('UI_PLATFORM_CDP_FILE_REALTIME_MODE_BACKUP'), 
        999 => ('UI_PLATFORM_OBS'),
        998 => ('UI_PLATFORM_HADOOP_HDFS'),
    ),
    // 文件子模块类型
    'FS_SUBMODULE_TYPE_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_FS'),
        ('WEB_PLATFORM_DES_NAS'),
        ('WEB_PLATFORM_DES_HADOOP'),
        4 =>  ('WEB_PLATFORM_DES_OBS'),
    ),
    //任务类型
    'TASKTYPEDES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_BACKUP'),
        ('WEB_PLATFORM_DES_RECOVERY'),
        ('WEB_PLATFORM_DES_DISK_BAK'),
        ('WEB_PLATFORM_DES_DISK_REC'),
        ('WEB_PLATFORM_DES_FILE_BAK'),
        ('WEB_VM_GRAIN_RECOVERY'),
        ('WEB_PLATFORM_DES_INSTANT_RECOVERY'),
        ('WEB_PLATFORM_DES_MOTION'),
        ('WEB_PLATFORM_DES_COPY'),
        ('WEB_PLATFORM_DES_SYNC'),//10
        ('WEB_PLATFORM_DES_RECOVER_EMERGENCY_PLAN'),
        ('WEB_PLATFORM_DES_BACKUP_DATE_EXPORT'),
        'CDP',
        ('UI_PLATFORM_CDP_RECOVERY'),
        ('UI_PLATFORM_CDP_INSTANT_RECOVERY'),
        ('UI_PLATFORM_CDP_MOTION'),
        ('WEB_PLATFORM_DES_COPY'),
        ('UI_PLATFORM_VM_COPY_RECOVERY'),
        ('UI_PLATFORM_ARCHIVE'),
        ('UI_PLATFORM_ARCHIVE_FETCH'),//20
        ('UI_PLATFORM_DB_REALTIME_BACKUP'),
        ('UI_PLATFORM_DB_DATA_RECOVERY'),
        ('UI_SETTINGS_AUTH_CDP_TAKEOVER'),
        ('UI_PLATFORM_CDP_FILE_REALTIME_BACKUP'),
        ('UI_PLATFORM_CDP_FILE_DATA_RECOVERY'),
        ('UI_PLATFORM_FS_COPY'),
        ('UI_PLATFORM_FS_COPY_FETCH'),
        ('UI_PLATFORM_DB_BACKUP'),
        ('UI_PLATFORM_DB_RECOVERY'),
        ('UI_PLATFORM_DB_COPY'),//30
        ('UI_PLATFORM_DB_COPY_FETCH'),//31
        ('WEB_PLATFORM_DES_BACKUP'), //卷CDP备份
        ('WEB_PLATFORM_DES_RECOVERY'), //卷CDP恢复
        ('UI_PLATFORM_VOL_CDP_TAKEOVER'), //卷CDP接管
        ('WEB_PLATFORM_DES_BACKUP'),
        ('WEB_PLATFORM_DES_RECOVERY'),
        37 => ('UI_PLATFORM_DATA_VERIFICATION'),
        ('UI_PLATFORM_OS_COPY'),
        ('UI_PLATFORM_OS_COPY_FETCH'),
        ('UI_PLATFORM_OS_ARCHIVE'),//预留
        ('UI_PLATFORM_OS_ARCHIVE_FETCH'),//预留
        ('UI_NAS_BACKUP'),//预留
        ('UI_NAS_RECOVERY'),//预留
        ('UI_PLATFORM_NAS_COPY'),//44  nas副本
        ('UI_PLATFORM_NAS_COPY_FETCH'),//45
        46 => ('WEB_PLATFORM_DES_SYNC'),
        47 => ('UI_PLATFORM_RECOVER'),
        49 => ('WEB_PLATFORM_DES_INSTANT_RECOVERY'),
        50 => ('WEB_PLATFORM_DES_MOTION'),
        51 => ('UI_PLATFORM_SYNC_CBR'), //51 华为CBR同步
        52 => ('UI_PLATFORM_RECOVERY'), //52 跨平台恢复
        53 => ('WEB_PLATFORM_DES_INSTANT_RECOVERY'), //53 瞬时恢复
        54 => ('WEB_PLATFORM_DES_MOTION'), //54 迁移
        55 => ('UI_PLATFORM_GRAIN_RECOVERY'), //55 细粒度恢复
        56 => ('UI_PLATFORM_BACKUP'), //56 k8s备份
        57 => ('UI_PLATFORM_RECOVER'), //57 k8s恢复
        62 => ('UI_PLATFORM_COPY'), //文件复制
        63 => ('WEB_PLATFORM_DES_CONTRAST'), //文件对比
        64 => ('WEB_PLATFORM_DES_DRILL'), //演练
        65 => ('WEB_PLATFORM_REPLICATION'), //复制

    ),
    //任务状态
    'TASKSTATUSDES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_WAITING'),
        ('WEB_PLATFORM_DES_RUNNING'),
        ('WEB_PLATFORM_DES_PAUSE'),
        ('WEB_PLATFORM_DES_STOP'),
        ('WEB_PLATFORM_DES_STOPPING'),
        ('WEB_PLATFORM_DES_NETWORK_ERROR'),
        ('WEB_PLATFORM_DES_ABNORMAL'),
        ('WEB_PLATFORM_DES_ERROR'),
        ('WEB_PLATFORM_DES_SYNC'),
        ('WEB_PLATFORM_DES_PREPARE'), //10
        ('WEB_PLATFORM_DES_PAUSEING'),
        ('WEB_PLATFORM_DES_STARTING'),
        ('WEB_PLATFORM_DES_FINISH'),
        ('WEB_PLATFORM_DES_IN_TAKEOVER'),
        ('WEB_PLATFORM_DES_TAKEOVER_STARTING'),
        ('WEB_PLATFORM_DES_TAKEOVER_STOPPING'),
        ('WEB_PLATFORM_DES_SUCCESSED'),
        ('WEB_PLATFORM_DES_CREATING'),
        ('WEB_PLATFORM_DES_PENDING'),
        ('WEB_PLATFORM_DES_DELETING'),
    ),
    //任务类型
    'BACKUP_MODE_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_FULL'),
        ('WEB_PLATFORM_DES_INCRIMENT'),
        ('WEB_PLATFORM_DES_DIFFRENCE'),
        ('WEB_PLATFORM_DES_LOG'),
        ('WEB_PLATFORM_DES_LOG_FILING'),
    ),
    //日志类型
    'LOG_LEVEL_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_NORMAL'),
        ('WEB_PLATFORM_DES_WARNING'),
        ('WEB_PLATFORM_DES_ERROR'),
    ),
    //系统授权
    'LISENCE_STATUS_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_SYSTEM_LISENCE_AUTHORIZED'),
        ('WEB_SYSTEM_LISENCE_UNAUTHORIZED'),
        ('WEB_SYSTEM_LISENCE_EXPIRE'),
        ('WEB_SYSTEM_LISENCE_INVALID'),
    ),
    //代理端状态
    'AGENTSTATUSDES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_AGENT_STATUS_ONLINE_REGISTED'),
        ('WEB_AGENT_STATUS_ONLINE_UNREGISTED'),
        ('WEB_AGENT_STATUS_OFFLINE_REGISTED'),
        ('WEB_AGENT_STATUS_OFFLINE_UNREGISTED'),
    ),
    //在线离线
    'ONLINEDES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_AGENT_STATUS_ONLINE'),
        ('WEB_AGENT_STATUS_OFFLINE'),
    ),
    //存储类型
    'STORAGETYPE' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_STORAGE_TYPE1'),
        ('UI_STORAGE_TYPE2'),
        ('UI_STORAGE_TYPE3'),
        ('UI_STORAGE_TYPE4'),
        ('UI_STORAGE_TYPE5'),
        ('UI_STORAGE_TYPE6'),
        ('UI_STORAGE_TYPE7'),
        ('UI_COPY_ALLOPATRIC_BACKUP_SYSTEM'),
        ('UI_STORAGE_TYPE9'),
        ('UI_STORAGE_TYPE10'),
        ('UI_STORAGE_TYPE11'),
        'UI_STORAGE_TYPE12',
        'UI_STORAGE_TYPE_FILE',
        101 => 'Huawei OceanProtect(NFS)',
        102 => 'Huawei OceanProtect(CIFS)',
    ),
    //分区类型
    'PARTITIONTYPE' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_STORAGE_PARTITION_TYPE1'),
        ('UI_STORAGE_PARTITION_TYPE2'),
        ('UI_STORAGE_PARTITION_TYPE3'),
    ),
    //存储状态
    'STORAGESTATUS' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_STORAGE_STATUS_NORMAL'),        //正常
        ('WEB_STORAGE_STATUS_CREATING'),    //创建中
        ('WEB_AGENT_STATUS_OFFLINE'),       //离线
        ('WEB_STORAGE_STATUS_UNMOUNT'),     //未挂载
        ('WEB_STORAGE_STATUS_WARNING'),     //告警
        ('WEB_STORAGE_STATUS_SYNCING'),     //同步中
    ),
    //告警等级
    'ALARM_LEVEL_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_GENERAL'),
        ('WEB_PLATFORM_DES_WARNING'),
        ('WEB_PLATFORM_DES_ERROR'),
    ),
    //告警响应描述
    'ALARM_RESPOND_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_ALARM_HAS_RESPONSE'),
        ('WEB_ALARM_NO_RESPONOSE'),
    ),
    //告警发送描述
    'ALARM_NOTICE_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_ALARM_HAS_SEND'),
        ('WEB_ALARM_NO_SEND'),
    ),
    //演练代理状态
    'PROXY_STATUS_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_DEPLOY'),
        ('WEB_PLATFORM_DES_NORMAL'),
        ('WEB_AGENT_STATUS_OFFLINE'),
        ('WEB_PLATFORM_DES_ABNORMAL'),
        ('WEB_PLATFORM_DES_ERROR'),
    ),
    //代理类型
    'AGENT_TYPE_DES' => array(
        ('WEB_AGENT_VM_BACKUP_PLUGIN'),
        ('WEB_AGENT_BACKUP_NODE_EXTEND'),
        ('WEB_HOST_AGENT'),
        ('WEB_AGENT_VOL_CDP_CLIENT'),
        ('WEB_AGENT_DB_REAL_TIME'),
        ('UI_PLATFORM_DB_PROTECT_PLUG')
    ),
    //系统升级
    'UPDATE_PATCH_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_SETTINGS_UPDATE_ALREADY_UPLOAD'), //"已上传, 等待升级中",1
        ('UI_SETTINGS_UPDATE_FAILED'),        //"升级失败",2
        ('UI_SETTINGS_UPDATE_UPLOAD_FAILED'),//"上传失败",3
        ('UI_SETTINGS_UPDATE_SUCCESS'),    //"升级成功",4
        ('UI_SETTINGS_UPDATE_UPLOAD_TO_NODE'),//"升级包正在上传到节点",5
        ('UI_SETTINGS_UPDATE_WAITING'),       //"等待升级中",6
        ('UI_SETTINGS_UPDATING'),             //"升级中",7
        ('UI_SETTINGS_PATCH_INVALID'),         //"升级包无效",8
        ('UI_SETTINGS_PATCH_INVALID_ING'),         //"升级包检查中",9
        ('UI_SETTINGS_PATCH_INVALID_COMPLETE'),         //"升级包检查完成",10
    ),

    //存储用途
    'STORAGE_USE_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_BACKUP'),
        ('WEB_PLATFORM_DES_COPY'),
        ('UI_PLATFORM_ARCHIVE'),
        'NAS'

    ),
    //用户组类型user_group_type描述
    'USER_GROUP_TYPE_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_USER_GROUP_DEFAULT'),
        ('UI_USER_GROUP_GLOBAL'),
        ('UI_USER_GROUP_TENANT'),
    ),
    //用户组状态lock_flag描述
    'USER_GROUP_LOCK_FLAG_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_ENABLE'),
        ('WEB_PLATFORM_DISABLE'),
    ),
    //角色状态lock_flag描述
    'ROLE_LOCK_FLAG_DES' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_ENABLE'),
        ('WEB_PLATFORM_DISABLE'),
    ),


    //策略类型
    'BILLING_TYPE' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('BILLING_PAY_MONTH'),
        ('BILLING_PAY_YEAR'),
        ('BILLING_PAY_SIZE'),
    ),
    //策略模式
    'BILLING_MODE' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('BILLING_PAY_CAPACITY'),
        ('BILLING_PAY_NUM'),
    ),
    //数量类型
    'NUM_TYPE' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('BILLING_VM'),
        ('BILLING_FS'),
        ('BILLING_DB'),
    ),
    'INFORM_STATE_DES' => array(//是否通知
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_PUBLIC_YES'),
        ('WEB_PLATFORM_PUBLIC_NO'),
    ),
    'INFORM_PERIOD_DES' => array( //通知周期
        ('BILLING_NO_INFORM'),
        ('BILLING_INFORM_DAY'),
        ('BILLING_INFORM_WEEK'),
        ('BILLING_INFORM_MONTH'),
        ('BILLING_INFORM_YEAR'),
    ),
    'LOCK_DESC' => array( //锁定状态
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_ENABLE'),
        ('WEB_PLATFORM_DISABLE'),
    ),
    //任务运行阶段
    'CDP_TASK_RUNNING_STAGE' => array(
        0 => '--',
        1 => ('WEB_PLATFORM_DES_WAIT_EXECUTE'),
        20 => ('WEB_PLATFORM_DES_INITIAL_SYN'),
        21 => ('WEB_PLATFORM_DES_BACKUP_TIME_SYN'),
        40 => ('WEB_PLATFORM_DES_SERVERDATA_CONSIST_CHECK'),
        41 => ('WEB_PLATFORM_DES_STANDBY_CONSIST_CHECK'),
        60 => ('WEB_PLATFORM_DES_IN_TAKEOVER'),
        61 => ('WEB_PLATFORM_DES_TAKEOVER_STARTING'),
        80 => ('WEB_PLATFORM_DES_BACK_INITIAL_SYN'),
        81 => ('WEB_PLATFORM_DES_BACK_TIME_SYN'),
        82 => ('WEB_TASK_VOL_CDP_LOG_DESC_KEY_FAILBACK_STARTING'),
        83 => ('WEB_TASK_VOL_CDP_TASK_FAILBACK_WAIT_CONVERT_TO_CDP'),
        90 => ('WEB_TASK_VOL_CDP_TASK_FAILBACK_IN_SERVER_DATA_CONSISTENCY_CHECK'),
        91 => ('WEB_TASK_VOL_CDP_TASK_FAILBACK_IN_STANDBY_DATA_CONSISTENCY_CHECK'),
        92 => ('WEB_TASK_VOL_CDP_TASK_FAILBACK_IN_SERVER_REALTIME_CONSISTENCY_CHECK'),
    ),
    //客户端部署状态
    'AGENT_DEPLOY_STATUS' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_CLIENT_DEPLOYING'),
        ('WEB_CLIENT_DEPLOY_SUCCESS'),
        ('WEB_CLIENT_DEPLOY_FAILED'),
        ('WEB_CLIENT_UPGRADING'),
        ('WEB_CLIENT_UPGRADE_SUCCESS'),
        ('WEB_CLIENT_UPGRADE_FAILED'),
        ('WEB_CLIENT_INVALID_CODE'),
    ),
    //用于文件详情--多客户端状态
    'AGENT_TASK_STATUS' => array (
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_WAITING'),
        ('UI_FILE_JOB_SCANNING'),     //scanning
        ('WEB_PLATFORM_DES_RUNNING'),     //running
        ('WEB_PLATFORM_DES_SUCCESSED'),     //success
        ('WEB_PLATFORM_DES_ABNORMAL'),     //abnormal
        ('WEB_PLATFORM_DES_ERROR'),     //error
    ),
    //禁用/启用
    'LOCK_FLAG_DES' => array(
        'WEB_PLATFORM_PUBLIC_UNKNOWN',
        'WEB_PLATFORM_ENABLE',
        'WEB_PLATFORM_DISABLE',
    ),
    'SUB_MODULE_DES_VM' => [
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_PLATFORM_VM_VIRTUAL'),
        ('UI_PLATFORM_PRIVATE_CLOUD'),
        ('UI_PLATFORM_PUBLIC_CLOUD'),
    ],
    'SUB_MODULE_DES_FS' => [
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_FS'),
        ('WEB_PLATFORM_DES_NAS'),
        ('UI_PLATFORM_HADOOP_HDFS'),
        ('UI_PLATFORM_OBS_STORAGE'),
    ],
    // 高可用日志
    'HA_LOG' => [
        'BD_CLUSTER_TASK_HA_RESCHEDULE_SUCCESS' => 'WEB_BD_CLUSTER_TASK_HA_RESCHEDULE_SUCCESS',
        'BD_CLUSTER_TASK_NOT_HAVE_HA_CAPABILITY' => 'WEB_BD_CLUSTER_TASK_NOT_HAVE_HA_CAPABILITY',
        'BD_CLUSTER_TASK_HA_RESCHEDULE_FAILED' => 'WEB_BD_CLUSTER_TASK_HA_RESCHEDULE_FAILED',
        'BD_CLUSTER_HA_CAPABILITY_SWITCH_SUCCESS' => 'WEB_BD_CLUSTER_HA_CAPABILITY_SWITCH_SUCCESS',
        'BD_CLUSTER_HA_CAPABILITY_SWITCH_FAILURE' => 'WEB_BD_CLUSTER_HA_CAPABILITY_SWITCH_FAILURE',
    ],
    // 高可用日志 - 类型
    'HA_LOG_TYPE' => [
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_CLUSTER_HA_TYPE_1'),
        ('UI_CLUSTER_HA_TYPE_2'),
    ],
    // 高可用日志 - 资源调度类型
    'HA_LOG_RR_TYPE' => [
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_CLUSTER_HA_RR_TYPE_1'),
        ('UI_CLUSTER_HA_RR_TYPE_2'),
        ('UI_CLUSTER_HA_RR_TYPE_3'),
        ('UI_CLUSTER_HA_RR_TYPE_4'),
    ],


);
