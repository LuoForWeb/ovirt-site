<?php

return [
    //存储类型
    'BD_STORAGE_TYPE' => [
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
        'HUAWEI_OS' => 14,   //HUAWEI OceanStor
        'HUAWEI_FS' => 15,   //HUAWEI Fusion Storage
        'DDDB' => 16,   //DELL Data Domain Boost
        'INSPUR_HF' => 17,   //Inspur HF18000G6
    ],

    'BD_STORAGE_CATEGORY' => [
        'UNKNOWN' => 0,
        'BACKUP' => 1,        // 备份存储
        'LUN' => 2,         // 生产存储
    ],

    'STORAGE_STATUS' => [
        'UNKNOWN' => 0,
        'ONLINE' => 1,  //在线
        'CREATING' => 2,//创建
        'OFFLINE' => 3, //离线
        'UNMOUNT' => 4, //未挂载
        'WARNING' => 5,  //告警
        'SYNCING' => 6  //同步中
    ],
    'STORAGE_POOL_TYPE' => [
        'UNKNOWN' => 0,
        'CENTRALIZED' => 1,  // 集中式存储
        'NAS' => 2,  // NAS存储
        'CLOUD' => 3, // 云存储
    ],
    'STORAGE_POOL_TYPE_DES' => [
        0 => 'unknown',
        1 => xphp_get_lang('WEB_STORAGE_POOL_TYPE_CREATING'),
        2 => xphp_get_lang('WEB_STORAGE_POOL_TYPE_NAS'),
        3 => xphp_get_lang('WEB_STORAGE_POOL_TYPE_CLOUD'),
    ],
    // 存储池类别具体是哪些设备
    'STORAGE_POOL_TYPE_MAP' => [
        /**
         * 8 远程存储、12 华为CBR只能用于副本、归档
         */
        1 => [
            1,  // 本地磁盘
            2,  // 本地逻辑卷(lvm)
            3,  // 本地分区(partition)
            4,  // FC
            5,  // iSCSI
            10, // 磁带
            11, // 本地目录
        ],
        2 => [
            6,  // NFS
            7,  // CIFS
        ],
        3 => [
            9,  // 云存储
        ],
    ],
];
