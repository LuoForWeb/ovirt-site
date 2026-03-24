/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 16:15:15
 * @Description: 任务相关常量配置
 * @version: 1.0
 */

// 业务类型 - 模块类型 - 任务类型的级联树
const MODULE_TASK_TYPE_CASCADER_TREE_DATA = [
    {
        id: 'timing_backup',
        value: "timing_backup",
        label: '备份',
        children: [
            {
                id: 'timing_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_BACKUP_ALL,
                label: '所有'
            },
            {
                id: 'vmprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.VM,
                label: '虚拟化',
                children: [
                    {
                        id: 'vmprotect',
                        value: "2-1-0",
                        label: '所有'
                    },
                    {
                        id: 'vmprotect',
                        value: "2-1-1",
                        label: '备份',
                    },
                    {
                        id: 'vmrecover',
                        value: "2-1-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "2-1-17",
                        label: '副本',
                    },
                    {
                        id: 'archive_new',
                        value: "2-1-19",
                        label: '归档',
                    },
                    {
                        id: 'vm_instant_recovery',
                        value: "2-1-54",
                        label: '迁移',
                    },
                    {
                        id: 'vm_instant_recovery',
                        value: "2-1-53",
                        label: '瞬时恢复',
                    },
                    {
                        id: 'vm_grain_recovery',
                        value: "2-1-55",
                        label: '细粒度恢复',
                    },
                    {
                        id: 'data_verification',
                        value: "2-1-37",
                        label: '数据验证',
                    },
                    {
                        id: 'vm_platform_recovery',
                        value: "2-1-52",
                        label: '跨平台恢复',
                    }
                ]
            },
            {
                id: 'prcloud_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PRIVATE_CLOUD,
                label: '私有云',
                children: [
                    {
                        id: 'prcloud_protect',
                        value: "2-2-0",
                        label: '所有'
                    },
                    {
                        id: 'prcloud_protect',
                        value: "2-2-1",
                        label: '备份',
                    },
                    {
                        id: 'vm_prcloud_recovery',
                        value: "2-2-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "2-2-17",
                        label: '副本',
                    },
                    {
                        id: 'archive_new',
                        value: "2-2-19",
                        label: '归档',
                    },
                    {
                        id: 'vm_prcloud_instant_recovery',
                        value: "2-2-54",
                        label: '迁移',
                    },
                    {
                        id: 'vm_prcloud_instant_recovery',
                        value: "2-2-53",
                        label: '瞬时恢复',
                    },
                    {
                        id: 'vm_prcloud_graininess_recovery',
                        value: "2-2-55",
                        label: '细粒度恢复',
                    },
                    {
                        id: 'data_verification',
                        value: "2-2-37",
                        label: '数据验证',
                    },
                    {
                        id: 'vm_prcloud_platform_recovery',
                        value: "2-2-52",
                        label: '跨平台恢复',
                    }
                ]
            },
            {
                id: 'awsprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PUBLIC_CLOUD,
                label: '公有云',
                children: [
                    {
                        id: 'awsprotect',
                        value: "2-3-0",
                        label: '所有'
                    },
                    {
                        id: 'awsprotect',
                        value: "2-3-1",
                        label: '备份',
                    },
                    {
                        id: 'vm_awsprotect_recover',
                        value: "2-3-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "2-3-17",
                        label: '副本',
                    },
                    {
                        id: 'archive_new',
                        value: "2-3-19",
                        label: '归档',
                    },
                    {
                        id: 'vm_awsprotect_instant_recovery',
                        value: "2-3-54",
                        label: '迁移',
                    },
                    {
                        id: 'vm_awsprotect_instant_recovery',
                        value: "2-3-53",
                        label: '瞬时恢复',
                    },
                    {
                        id: 'vm_awsprotect_grain_recovery',
                        value: "2-3-55",
                        label: '细粒度恢复',
                    },
                    {
                        id: 'data_verification',
                        value: "2-3-37",
                        label: '数据验证',
                    },
                    {
                        id: 'vm_awsprotect_platform_recovery',
                        value: "2-3-52",
                        label: '跨平台恢复',
                    }
                ]
            },
            {
                id: 'complete_machine',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK,
                label: '整机',
                children: [
                    {
                        id: 'complete_machine',
                        value: "5-1-0",
                        label: '所有'
                    },
                    {
                        id: 'complete_machine',
                        value: "5-1-35",
                        label: '备份',
                    },
                    {
                        id: 'machine_complete_recovery',
                        value: "5-1-36",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "5-1-17",
                        label: '副本',
                    },
                    {
                        id: 'archive_new',
                        value: "5-1-19",
                        label: '归档',
                    },
                    {
                        id: 'machine_complete_instant_recovery',
                        value: "5-1-54",
                        label: '迁移',
                    },
                    {
                        id: 'machine_complete_instant_recovery',
                        value: "5-1-49",
                        label: '瞬时恢复',
                    },
                    {
                        id: 'machine_complete_grain_recovery',
                        value: "5-1-55",
                        label: '细粒度恢复',
                    },
                    {
                        id: 'data_verification',
                        value: "5-1-37",
                        label: '数据验证',
                    },
                    {
                        id: 'machine_complete_platform_recovery',
                        value: "5-1-52",
                        label: '跨平台恢复',
                    }
                ]
            },
            {
                id: 'osbackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_VOLUME,
                label: '卷',
                children: [
                    {
                        id: 'osbackup',
                        value: "5-0-0",
                        label: '所有'
                    },
                    {
                        id: 'osbackup',
                        value: "5-0-35",
                        label: '备份',
                    },
                    {
                        id: 'os_recovery',
                        value: "5-0-36",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "5-0-17",
                        label: '副本',
                    },
                    {
                        id: 'archive_new',
                        value: "5-0-19",
                        label: '归档',
                    },
                    // {
                    //     value: "5-0-54",
                    //     label: '迁移',
                    // },
                    // {
                    //     value: "5-0-53",
                    //     label: '瞬时恢复',
                    // }
                ]
            },
            {
                id: 'filebackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.FILE,
                label: '文件',
                children: [
                    {
                        id: 'filebackup',
                        value: "3-1-0",
                        label: '所有'
                    },
                    {
                        id: 'filebackup',
                        value: "3-1-1",
                        label: '备份',
                    },
                    {
                        id: 'file_recovery',
                        value: "3-1-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "3-1-17",
                        label: '副本',
                    },
                    {
                        id: 'data_verification',
                        value: "3-1-37",
                        label: '数据验证',
                    },
                ]
            },
            {
                id: 'nas_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.NAS,
                label: "NAS",
                children: [
                    {
                        id: 'nas_protect',
                        value: "11-2-0",
                        label: '所有'
                    },
                    {
                        id: 'nas_protect',
                        value: "11-2-1",
                        label: '备份',
                    },
                    {
                        id: 'nas_recovery',
                        value: "11-2-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "11-2-17",
                        label: '副本',
                    },
                    {
                        id: 'data_verification',
                        value: "11-2-37",
                        label: '数据验证',
                    },
                ]
            },
            {
                id: 'hadoop_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.HADOOP,
                label: "Hadoop",
                children: [
                    {
                        id: 'hadoop_protect',
                        value: "3-3-0",
                        label: '所有'
                    },
                    {
                        id: 'hadoop_protect',
                        value: "3-3-1",
                        label: '备份',
                    },
                    {
                        id: 'hadoop_recovery',
                        value: "3-3-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "3-3-17",
                        label: '副本',
                    },
                    {
                        id: 'data_verification',
                        value: "3-3-37",
                        label: '数据验证',
                    },
                ]
            },
            {
                id: 'obs_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.OBS,
                label: '对象存储',
                children: [
                    {
                        id: 'obs_protect',
                        value: "3-4-0",
                        label: '所有'
                    },
                    {
                        id: 'obs_protect',
                        value: "3-4-1",
                        label: '备份',
                    },
                    {
                        id: 'obs_recovery',
                        value: "3-4-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "3-4-17",
                        label: '副本',
                    },
                    {
                        id: 'data_verification',
                        value: "3-4-37",
                        label: '数据验证',
                    },
                ]
            },
            {
                id: 'db_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DB,
                label: '数据库',
                children: [
                    {
                        id: 'db_protect',
                        value: "4-0-0",
                        label: '所有'
                    },
                    {
                        id: 'db_protect',
                        value: "4-0-28",
                        label: '备份',
                    },
                    {
                        id: 'db_recovery',
                        value: "4-0-29",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "4-0-17",
                        label: '副本',
                    },
                    {
                        id: 'db_drill',
                        value: "4-0-64",
                        label: '演练'
                    },
                    {
                        id: 'data_verification',
                        value: "4-0-37",
                        label: '数据验证',
                    },
                ]
            },
            {
                id: 'office365_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.M365,
                label: 'Microsoft 365',
                children: [
                    {
                        id: 'office365_protect',
                        value: "14-0-0",
                        label: '所有'
                    },
                    {
                        id: 'office365_protect',
                        value: "14-0-1",
                        label: '备份',
                    },
                    {
                        id: 'exchange_recovery',
                        value: "14-0-2",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "14-0-17",
                        label: '副本',
                    },
                    {
                        id: 'data_verification',
                        value: "14-0-37",
                        label: '数据验证',
                    },
                ]
            },
            {
                id: 'k8s_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.KUBERNETES,
                label: 'Kubernetes',
                children: [
                    {
                        id: 'k8s_protect',
                        value: "28-0-0",
                        label: '所有'
                    },
                    {
                        id: 'k8s_protect',
                        value: "28-0-56",
                        label: '备份',
                    },
                    {
                        id: 'k8s_recovery',
                        value: "28-0-57",
                        label: '恢复',
                    },
                    {
                        id: 'copy_protect',
                        value: "28-0-17",
                        label: '副本',
                    },
                    // {
                    //     id: 'data_verification',
                    //     value: "28-37",
                    //     label: '数据验证',
                    // },
                ]
            }
        ]
    },
    {
        id: 'real_time_protect',
        value: "real_time_protect",
        label: '实时保护',
        children: [
            {
                id: 'real_time_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_DATA_PROTECT_ALL,
                label: '所有'
            },
            {
                id: 'complete_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_DISK,
                label: '整机',
                children: [
                    {
                        id: 'complete_cdp_backup',
                        value: "10-1-2-0",
                        label: '所有'
                    },
                    {
                        id: 'complete_cdp_backup',
                        value: "10-1-2-32",
                        label: '备份',
                    },
                    {
                        id: 'machine_complete_volcdp_recovery',
                        value: "10-1-2-33",
                        label: '恢复',
                    },
                    {
                        id: 'vol_cdp_complete_takeover',
                        value: "10-1-2-34",
                        label: '接管',
                    },
                    {
                        id: 'machine_complete_volcdp_grain_recovery',
                        value: "10-1-2-55",
                        label: '细粒度恢复',
                    },
                    {
                        id: 'data_verification',
                        value: "10-1-2-37",
                        label: '数据验证',
                    },
                    {
                        id: 'machine_complete_volcdp_platform_recovery',
                        value: "10-1-2-52",
                        label: '跨平台恢复',
                    }
                ]
            },
            {
                id: 'vol_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_VOLUME,
                label: '卷',
                children: [
                    {
                        id: 'vol_cdp_backup',
                        value: "10-1-1-0",
                        label: '所有'
                    },
                    {
                        id: 'vol_cdp_backup',
                        value: "10-1-1-32",
                        label: '备份',
                    },
                    {
                        id: 'p_vol_cdp_recovery',
                        value: "10-1-1-33",
                        label: '恢复',
                    },
                    {
                        id: 'vol_cdp_takeover',
                        value: "10-1-1-34",
                        label: '接管',
                    }
                ]
            }
        ]
    },
    {
        id: 'data_copy',
        value: "data_copy",
        label: '复制容灾',
        children: [
            {
                id: 'data_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_ALL,
                label: '所有'
            },
            {
                id: 'machine_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_DISK,
                label: '整机',
                children: [
                    {
                        id: 'machine_copy',
                        value: "10-3-4-0",
                        label: '所有'
                    },
                    {
                        id: 'machine_copy',
                        value: "10-3-4-65",
                        label: '复制',
                    },
                    {
                        id: 'machine_complete_volcdp_recovery',
                        value: "10-3-4-33",
                        label: '恢复',
                    },
                    {
                        id: 'machine_copy',
                        value: "10-3-4-34",
                        label: '接管',
                    },
                    {
                        id: 'machine_complete_volcdp_grain_recovery',
                        value: "10-3-4-55",
                        label: '细粒度恢复',
                    },
                    {
                        id: 'data_verification',
                        value: "10-3-4-37",
                        label: '数据验证',
                    },
                    {
                        id: 'machine_complete_volcdp_platform_recovery',
                        value: "10-3-4-52",
                        label: '跨平台恢复',
                    }
                ]
            },
            {
                id: 'vol_cdp_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_VOLUME,
                label: '卷',
                children: [
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-0",
                        label: '所有'
                    },
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-65",
                        label: '复制容灾',
                    },
                    {
                        id: 'p_vol_cdp_recovery',
                        value: "10-3-3-33",
                        label: '恢复',
                    },
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-34",
                        label: '接管',
                    }
                ]
            },
            {
                id: 'dbcdpcopy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_DB,
                label: '数据库',
                children: [
                    {
                        id: 'dbcdpcopy',
                        value: "12-0-0",
                        label: '所有'
                    },
                    {
                        id: 'dbcdpcopy',
                        value: "12-0-46",
                        label: '复制',
                    },
                    {
                        id: 'dbcdp_recovery',
                        value: "12-0-47",
                        label: '恢复',
                    },
                ]
            },
            {
                id: 'file_copy_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_FILE,
                label: '文件',
                children: [
                    {
                        id: 'file_copy_protect',
                        value: "26-0-0",
                        label: '所有'
                    },
                    {
                        id: 'file_copy_protect',
                        value: "26-0-62",
                        label: '复制容灾',
                    },
                    {
                        id: 'file_copy_protect',
                        value: "26-0-63",
                        label: '对比',
                    },
                ]
            }
        ]
    }
];

// 业务类型 - 模块类型的级联树
const BUSINESS_TYPE_MODULE_TYPE_TREE = [
    {
        id: 'timing_backup',
        value: "timing_backup",
        label: '备份',
        children: [
            {
                id: 'vmprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.VM,
                label: '虚拟化'
            },
            {
                id: 'prcloud_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PRIVATE_CLOUD,
                label: '私有云'
            },
            {
                id: 'awsprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PUBLIC_CLOUD,
                label: '公有云'
            },
            {
                id: 'complete_machine',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK,
                label: '整机'
            },
            {
                id: 'osbackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_VOLUME,
                label: '卷'
            },
            {
                id: 'filebackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.FILE,
                label: '文件'
            },
            {
                id: 'nas_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.NAS,
                label: "NAS"
            },
            {
                id: 'hadoop_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.HADOOP,
                label: "Hadoop"
            },
            {
                id: 'obs_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.OBS,
                label: '对象存储'
            },
            {
                id: 'db_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DB,
                label: '数据库'
            },
            {
                id: 'office365_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.M365,
                label: 'Microsoft 365'
            },
            {
                id: 'k8s_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.KUBERNETES,
                label: 'Kubernetes'
            }
        ]
    },
    {
        id: 'real_time_protect',
        value: "real_time_protect",
        label: '实时保护',
        children: [
            {
                id: 'complete_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_DISK,
                label: '整机'
            },
            {
                id: 'vol_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_VOLUME,
                label: '卷'
            }
        ]
    },
    {
        id: 'data_copy',
        value: "data_copy",
        label: '复制容灾',
        children: [
            {
                id: 'machine_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_DISK,
                label: '整机'
            },
            {
                id: 'vol_cdp_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_VOLUME,
                label: '卷'
            },
            {
                id: 'dbcdpcopy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_DB,
                label: '数据库'
            },
            {
                id: 'file_copy_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_FILE,
                label: '文件'
            }
        ]
    }
];

// 报表 - 业务类型 - 模块类型的映射关系
const REPORT_MODULE_TYPE_MAP = {
    TIMING_BACKUP: {
        VM: '2-1',
        PRIVATE_CLOUD: '2-2',
        PUBLIC_CLOUD: '2-3',
        COMPLETE_MACHINE: '5-1',
        VOLUME: '5-0',
        FILE: '3-1',
        NAS: '3-2',
        HADOOP: '3-3',
        OBS: '3-4',
        DB: '4-0',
        M365: '14-0',
        KUBERNETES: '28-0'
    },
    REAL_TIME_BACKUP: {
        COMPLETE_MACHINE: '10-1-2',
        VOLUME: '10-1-1'
    },
    DATA_COPY: {
        COMPLETE_MACHINE: '10-0-4',
        VOLUME: '10-0-3',
        DB: '12-0',
        FILE: '26-0'
    }
};

// 报表 - 模块类型 - bd_report表中 sub_type 映射关系
const REPORT_MODULE_TYPE_TO_SUB_TYPE_MAP = {
    '2-1': 1,
    '2-2': 2,
    '2-3': 3,
    '5-1': 4,
    '5-0': 5,
    '3-1': 6,
    '3-2': 7,
    '3-3': 8,
    '3-4': 9,
    '4-0': 10,
    '14-0': 11,
    '28-0': 12,
    '10-1-2': 13,
    '10-1-1': 14,
    '10-0-4': 15,
    '10-0-3': 16,
    '12-0': 17,
    '26-0': 18
}