<?php

/**
 * 虚拟机管理
 */

return [
    'vm' => [
        'VmPlatform' => [ // 虚拟化平台
            'vm_platforms' => [ // 路由名
                'post' => 'addVmPlatform', //添加虚拟化平台
                'put' => 'modifyVmPlatform', //修改虚拟化平台
                'get' => 'getVmPlatforms', //获取虚拟化平台列表
                'delete' => 'deleteVmPlatforms', //删除虚拟化平台列表
            ],
            'vm_platforms_detail' => [
                'get' => 'getVmPlatformDetail', //获取单个虚拟化平台详情
            ],
            'vm_platforms_tree' => [
                'get' => 'getVmPlatformsTree', //获取虚拟化平台树
            ],
            'vm_platforms_vm_tree' => [
                'get' => 'getVmPlatformsVmTree', //获取虚拟化平台的虚拟机树
            ],
            'vm_platforms_vms' => [
                'post' => 'getVmPlatformVms' //获取虚拟化平台下的虚拟机
            ],
            'vm_platforms_autorefresh' => [
                'get' => 'getRefreshVcenterTime', //获取虚拟化平台自动刷新配置
                'put' => 'vmPlatformAutoRefresh' //虚拟化平台自动刷新配置
            ],
            'vm_platforms_synchronization' => [
                'post' => 'syncVmPlatform' //同步虚拟化平台信息
            ],
            'vm_platforms_hosts' => [
                'get' => 'getVmPlatformHosts' //获取虚拟化平台主机列表
            ],
            'vm_platforms_authorization' => [
                'post' => 'addHostAuth', //添加虚拟化平台主机授权
            ],
            'vm_platforms_unauthorization' => [
                'post' => 'deleteHostAuth' //取消虚拟化平台主机授权
            ],
            'vm_platforms_hypervisors' => [
                'get' => 'getAllHypervisors' //获取所有虚拟化类型
            ],
            'vm_platforms_current_jobs' => [
                'get' => 'getPlatformCurrentJobs' //获取虚拟化平台当前所有备份任务
            ],
            'vm_platforms_engine' => [
                'get' => 'getEngineCount', // 获取平台备份数据管理的数据条数
                'post' => 'testEngine' // 平台备份测试连接虚拟化平台
            ],
            // /vm/jobs/speed_backup/tree
            'vm_jobs_speed_backup_tree' => [
                'get' => 'getBackupTreeSpeed', //获取快速创建备份任务的树
            ],
            'vm_jobs_backup_vm_tree' => [
                'get' => 'getBackupTreeOldInfo', //获取修改备份任务虚拟机树
            ],
            'vm_jobs_backup_platform_tree' => [
                'get' => 'getBackupTree', //获取修改备份任务虚拟化平台树
            ],
            'vm_platforms_group_user_verify' => [
                'post' => 'verifyVcenterGroupUser', //验证虚拟化平台分组用户
            ],
            'vm_platforms_groups' => [
                'get' => 'getSyncVcenterGroup', //获取虚拟化平台的用户分组
            ],
            'vm_test_controller_ip' => [
                'post' => 'testControllerIP', //测试Openstack控制IP连接
            ],
            'vm_platforms_support_info' => [
                'get' => 'getSupportInfo', // 获取虚拟化平台支持的配置（操作系统、cpu）
            ],
            'vm_platforms_user_roles' => [
                'get' => 'getUserRoleList', //获取用户角色列表
            ],
        ],

        'VmJobController' => [ //虚拟机操作
            'vm_vms_start' => [
                'post' => 'startVm' //启动虚拟机
            ],
            'vm_vms_stop' => [
                'post' => 'stopVm' //关闭虚拟机
            ],
            'vm_vms_pause' => [
                'post' => 'pauseVm' //挂起虚拟机
            ],
            'vm_vms_restart' => [
                'post' => 'restartVm' //重启虚拟机
            ],
            'vm_vms_terminate' => [
                'post' => 'terminateVm' //终止虚拟机（AWS实例）
            ],
            'vm_vms_disks' => [
                'get' => 'getVmDiskList' //获取虚拟机磁盘列表
            ],
            'vm_jobs_vms' => [
                'post' => 'addVmToBackupTask' //添加虚拟机到备份任务
            ],
            'vm_jobs_start' => [
                'post' => 'startJob' //启动任务
            ],
            'vm_jobs_stop' => [
                'post' => 'stopJob' //停止任务
            ],
            'vm_jobs_start_select' => [
                'post' => 'startVMJob' //选择虚拟机启动备份任务
            ],
            'vm_jobs_delete_select' => [
                'delete' => 'deleteVMFromJob' //选择虚拟机从备份任务标记删除
            ],
        ],

        'VmBackUp' => [ //虚拟机备份
            'vm_jobs_backup_job_name' => [
                'get' => 'getVMBackupTaskName', //获取虚拟机备份任务名
            ],
            'vm_jobs_backup' => [
                'post' => 'createBackupJob', //创建备份任务
                'put' => 'editBackupJob', //修改备份任务
            ],
            'vm_jobs_backup_all_info' => [
                'get' => 'getBackupTaskAllInfo' // 获取虚拟机备份任务信息(修改任务用)
            ],
            'vm_backup_configs' => [
                'get' => 'getBackupConfigs' // 获取虚拟化的备份配置
            ]
        ],

        'VmRecover' => [ //虚拟机恢复
            'vm_jobs_restore_platforms' => [
                'get' => 'getRecoverVcenter' //获取恢复目标虚拟化平台
            ],
            'vm_jobs_restore_hosts' => [
                'get' => 'getSyncRecoveryVcenter' //异步获取恢复目标主机/集群
            ],
            'vm_xhere_volume_policy' => [
                'get' => 'getXhereVolumePolicyList', //获取xhere块存储策略
            ],
            'vm_openstack_configs' => [
                'get' => 'getOpenStackNetworkAndStorage', //获取openstack的恢复配置
            ],
            'vm_openstack_available_zones' => [
                'get' => 'getOpenStackAvailableDomain', //获取openstack的可用域
            ],
            'vm_host_network_storage' => [
                'get' => 'getNetworkAndStorage', //获取宿主机网络和存储配置
            ],
            'vm_timepoints_config' => [
                'post' => 'getVMConfigInfo', //获取虚拟机备份时间点配置
            ],
            'vm_jobs_restore_job_name' => [
                'get' => 'getVMRecoverTaskName', //获取可用的恢复任务名
            ],
            'vm_jobs_restore' => [
                'post' => 'createRecoverJob', //创建恢复任务
            ],
            'vm_jobs_instant_restore' => [
                'post' => 'createInstantRecoverJob', //创建瞬时恢复任务
            ],
            'vm_jobs_motion_config' => [
                'get' => 'getMotionVMConfigs', //获取迁移虚拟机的配置信息
            ],
            'vm_jobs_restore_user_group' => [
                'get' => 'getRecoverUserGroup', //Openstack获取用户组的树
            ],
            'vm_jobs_motion' => [
                'post' => 'createMotionJob' //创建迁移任务
            ],
            'vm_jobs_granular_restore' => [
                'post' => 'createGrainRecoverJob' //创建细粒度恢复
            ],
            'vm_jobs_check_encrypt_password' => [
                'post' => 'checkEncryptPassword' //恢复任务校验数据加密密码正确性
            ],
            'vm_host_common_info' => [
                'get' => 'getHostCommonInfo' // 获取主机信息
            ],
            'vm_target_vm_config' => [
                'get' => 'getRecoverTargetVMConfigs' // 获取恢复目标虚拟机配置
            ],
        ],

        'VmData' => [ //虚拟机备份数据
            'vm_restore_data' => [
                'get' => 'getRestoreData', //获取备份数据虚拟机树
                'delete' => 'deleteRestoreData', //删除备份时间点数据
            ],
            'vm_restore_data_one' => [
                'delete' => 'deleteRestoreDataOne', //删除单个备份时间点数据
            ],
            'vm_restore_data_restore_points' => [
                'get' => 'getRestorePoints' //获取虚拟机备份时间点
            ],
            'vm_restore_points_remark' => [
                'post' => 'addRestorePointsRemark', //备份时间点添加备注
            ],
            'vm_restore_points_gfs_mark' => [
                'post' => 'setRestorePointsGfsMark', //备份时间点设置保留标记
            ],
            'vm_restore_points_star' => [
                'post' => 'addStar', //备份时间点添加永久标记
                'delete' => 'deleteStar' //备份时间点删除永久标记
            ]
        ],

        // 虚拟机概览
        'VmOverview' => [
            'vm_overview_count' => [
                'get' => 'getAllVms', // 获取所有虚拟机个数
            ],
            'vm_overview_list' => [
                'get' => 'getVMReport', // 获取虚拟机概览列表
                'post' => 'getVMReport', // 获取虚拟机概览列表
            ],
            'vm_overview_platforms' => [
                'get' => 'getVcenterList', // 获取已添加虚拟化中心列表
            ]
        ],

        // 任务详情
        'VmJobInfo' => [
            'vm_jobs_basic_info' => [
                'get' => 'getBasicInfo', // 获取任务详情基本信息
            ],
            'vm_jobs_log' => [
                'get' => 'getVMRunningJobLog', // 获取虚拟机任务监控运行日志
            ],
            'vm_jobs_vms' => [
                'get' => 'getDetailsVM', // 获取任务的虚拟机列表
            ],
            'vm_jobs_vms_export' => [
                'post' => 'exportDetailsVM', // 导出任务的虚拟机列表
            ],
            'vm_jobs_instant_restore_basic_info' => [
                'get' => 'getInstantBaseInfo', // 获取瞬时恢复任务的基本信息
            ],
            'vm_jobs_motion_basic_info' => [
                'get' => 'getMotionBaseInfo', // 获取迁移任务的基本信息
            ],
            'vm_jobs_speed' => [
                'get' => 'getTaskSpeed', //获取任务流量信息
            ],

            //细粒度
            'vm_jobs_granular_restore_details' => [
                'get' => 'getVMGrainJobDetails', // 获取细粒度恢复任务详情
            ],
            'vm_jobs_granular_restore_files' => [
                'get' => 'getVmGrainRecoveryFileDir', // 获取细粒度恢复文件列表/搜索/加载更多
            ],
            'vm_jobs_granular_restore_file' => [
                'post' => 'downloadGrainRecoveryFileCheck', // 检查细粒度恢复文件是否可以下载
                'get' => 'downloadGrainRecoveryFile', // 下载细粒度恢复文件
            ],
            'vm_jobs_granular_restore_dir' => [
                'post' => 'downloadGrainRecoveryDirCheck', // 检查细粒度恢复目录是否可以下载
                'get' => 'downloadGrainRecoveryDir', // 下载细粒度恢复目录
            ],
            'vm_jobs_granular_restore_show_type' => [
                'get' => 'getVmGrainRecoveryShowType', // 获取细粒度恢复任务展示模式
            ],
        ]
    ],
];
