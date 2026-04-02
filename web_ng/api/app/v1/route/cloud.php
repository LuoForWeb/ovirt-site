<?php

return [
    'cloud' => [
        'CloudPlatform' => [
            'cloud_platform_regions' => [
                'get' => 'getPlatformRegions', //获取云平台下的区域
            ],
            'cloud_platforms_availability_zones' => [
                'get' => 'getAvailabilityZones', //获取所有可用区
            ],
            'cloud_all_platforms' => [
                'get' => 'getAllPlatforms', //获取全部云平台
            ],
            'cloud_platform_nickname' => [
                'get' => 'getValidPlatformNickname', //获取可用的云平台别名
            ],
            'cloud_platform_sync_image' => [
                'post' => 'syncProxyImage', //云平台同步代理镜像
            ],
            'cloud_platform_shared_image' => [
                'delete' => 'deleteSharedImage', //云平台删除共享镜像
            ],
            'cloud_platform_ip_list' => [
                'get' => 'getPublicIpList' // 获取云平台某区域的公有IP列表
            ]
        ],
        'Instance' => [
            'cloud_instance_types' => [
                'get' => 'getInstanceTypes', //获取所有传输代理实例类型
            ],
            'cloud_instances_configs' => [
                'get' => 'getInstanceConfigs' //获取实例配置
            ],
            'cloud_instances_all_configs' => [
                'get' => 'getInstanceAllConfigs' //获取实例所有配置项
            ],
            'cloud_instances_type_configs' => [
                'get' => 'getInstanceTypeConfigs' //获取实例恢复的实例类型
            ],
            'cloud_instances_network_configs' => [
                'get' => 'getInstanceNetworkConfigs' //获取实例恢复的网络配置
            ],
            'cloud_kms_info' => [
                'get' => 'getKmsInfo' //获取卷恢复的kms密钥信息
            ],
            'cloud_timepoints_instance_config' => [
                'get' => 'getTimepointsInstanceConfig', //通过备份时间点获取实例配置
            ],
            'cloud_instance_disks' => [
                'get' => 'getInstanceDiskList' //获取实例磁盘列表
            ],
            'cloud_volume_types' => [
                'get' => 'getVolumeTypeList' //获取卷类型列表
            ],
        ],
        'CloudBackup' => [
            'cloud_jobs_backup' => [
                'post' => 'createBackupJob', //创建备份任务
                'put' => 'editBackupJob', //修改备份任务
            ]
        ],
        'CloudRecover' => [
            'cloud_storages' => [
                'get' => 'getStorageList', //获取存储列表
            ],
            'cloud_jobs_restore' => [
                'post' => 'createRecoverJob', //创建恢复任务
            ]
        ],
        'CloudJobInfo' => [
            'cloud_jobs_instances' => [
                'get' => 'getJobInstances' //获取任务的实例列表
            ],
            'cloud_jobs_basic_info' => [
                'get' => 'getJobBasicInfo' //获取任务基本信息
            ],
        ]
    ]
];
