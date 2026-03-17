<?php

/**
 * 资源管理的一些配置
 */

return [
    //资源类型
    'RESOURCE_TYPE' => array(
        'UNKNOWN' => 0,
        'VM' => 3,              //虚拟机
        'FS' => 4,
        'DB' => 5,
        'OS' => 6,
        'CLIENT' => 10,        //客户端
        'APPLICE' => 2,       //传输代理
        'NODE' => 7,            //节点
        'STORAGE' => 8,         //存储
        'M365_EXCHANGE' => 56,         //M365组织
        'NAS' => 57,         //NAS设备
        'PUBLIC_CLOUD' => 58,         //公有云
        'OBS' => 59, // 对象存储
        'HADOOP' => 60, // hadoop集群
        'PRIVATE_CLOUD' => 61,    //私有云
        'K8S' => 62,    // Kubernetes集群

    ),
    'RESOURCE_FROM' => array(
        'UNKNOWN' => 0,
        'RESOURCE_GROUP' => 1,
        'USER' => 2,
        'USER_GROUP' => 3
    ),
    //操作系统类型对应键值用于获取插件
    'AGENT_OS_TYPE_DES' => array(
        'UNKONWN',
        'WINDOWS',
        'RHEL6',
        'RHEL7',
        'RHEL8',
        'UBUNTU',
        'DEBIAN',
        'KYLIN',
        'UNIONTECH',
    ),
    'AGENT_DEPLOY_STATUS' => array(
        'UNKONWN' => 0,
        'DEPLOY_WAITING' => 1,  // 部署中
        'DEPLOY_SUCCESS' => 2,  // 部署成功
        'DEPLOY_FAILED' => 3,   // 部署失败
        'UPGRADING' => 4,       // 升级中
        'UPGRADE_SUCCESS' => 5, // 升级成功
        'UPGRADE_FAILED' => 6,  // 升级失败
        'INVALID_CODE' => 7,    // Access Key无效
    ),
    //存储类型
    'BD_STORAGE_TYPE' => array(
        'UNKNOWN' => 0,
        'DISK' => 1,        //local disk
        'LVM' => 2,         //lvm
        'PARTITION' => 3,   //local partition
        'FC' => 4,          //storage area network FC
        'ISCSI' => 5,       //storage area network iSCSI
        'NFS' => 6,         //network attached storage NFS
        'CIFS' => 7,        //network attached storage CIFS
        'REMOTE' => 8,      //storage type remote system
        'CLOUD' => 9,       //cloud type storage
        'TAPE' => 10,       //tape storage
        'LOCALDIR' => 11,   //Local dir
        'HUAWEI_CBR' => 12,
        'FILE_SYSTEM' => 13,   //并行文件系统
        'HUAWEI_OCEAN' => 14,  //生产存储（目前只有华为ocean，以后可能会有其他）
        'DDDB' => 16,  // dddb
    ),
    //存储用途
    'BD_STORAGE_USE_MODE' => array(
        'UNKNOWN' => 0,
        'BACKUP' => 1,  //备份
        'COPY' => 2,    //副本
        'ARCHIVE' => 3, //归档
        'NAS' => 4, //NAS
        'READ_ONLY' => 5, // readonly
    ),
    //存储告警类型
    'STORAGEWARNINGTYPE' => array(
        'UNKNOWN' => 0,
        'PERCENT' => 1,
        'SIZE' => 2
    ),

    //存储worm类型
    'STORAGEWORMTYPE' => array(
        'UNKNOWN' => 0,
        'PERCENT' => 1, // 百分比
        'SIZE' => 2,    // 大小
        'NOT_USE' => 3  // 无限制
    ),

    //存储状态
    'STORAGE_STATUS' => array(
        'UNKNOWN' => 0,
        'ONLINE' => 1,  //在线
        'CREATING' => 2,//创建
        'OFFLINE' => 3, //离线
        'UNMOUNT' => 4, //未挂载
        'WARNING' => 5  //告警
    ),

    // 云服务商
    'STORAGE_VENDOR' => [
        1 => 'AWS S3',
        2 => 'Azure',
        3 => 'UI_STORAGE_CLOUD_VENDOR_ALI',   // 语言包键名
        4 => 'UI_STORAGE_CLOUD_VENDOR_HUAWEI',    // 语言包键名
        5 => 'UI_STORAGE_CLOUD_VENDOR_TENCENT',   // 语言包键名
        6 => 'Ceph S3',
        7 => 'Wasabi',
        8 => 'MinIO',
        9 => 'Huawei OceanStor Pacific',
    ],

    // 对象存储云服务商
    'OBJECT_STORAGE_VENDOR' => array(
        0 => 'AWS S3',
        1 => 'OSS',
        2 => 'COS',
        3 => 'OBS',
        4 => 'Ceph S3',
        5 => 'Wasabi',
        6 => 'MinIO',
        7 => 'Azure'
    ),

    // 资源分配类型
    'RESOURCE_DIS_TYPE' => [
        // key => 语言包键名
        2 => 'UI_PLATFORM_VM_APPLIANCE', // 传输代理
        3 => 'UI_VISUAL_VM', // 虚拟机
        7 => 'UI_PALTFORM_NODE', // 备份节点
        8 => 'UI_PLATFORM_STORAGE_RESOURCE', // 存储资源
        10 => 'UI_JOB_CLIENT', // 客户端
        56 => 'UI_PLATFORM_OFFICE365_ORGANIZATION', // 组织管理
        57 => 'UI_NAS_DEVICE_NAME', // nas设备
        58 => 'UI_PLATFORM_VM_CLOUD_PLATFORM', // 公有云平台
        59 => 'UI_PLATFORM_OBS', // 对象存储
        60 => 'WEB_HADOOP_CLUSTER', // Hadoop集群
        61 => 'UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM', // 私有云
        62 => 'WEB_K8S_CLUSTER_PROTECT', // kubernetes集群
    ],
    // 只能一对一的资源类型
    'RESOURCE_SELF_TYPE' => [
        3, 10, 56, 57, 58, 59, 60, 61
    ],
];
