/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 16:15:15
 * @Description: 任务相关常量配置
 * @version: 1.0
 */

// 用于 业务类型 - 模块类型 - 任务类型的级联树
const MODULE_TASK_TYPE_CASCADER_TREE_DATA = [
    {
        id: 'timing_backup',
        value: "timing_backup",
        label: LANG.UI_VISUAL_BACKUP,
        children: [
            {
                id: 'timing_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_BACKUP_ALL,
                label: LANG.UI_PUBLIC_ALL
            },
            {
                id: 'vmprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.VM,
                label: LANG.UI_BACKUP_DATA_MODULE_VM,
                children: [
                    {
                        id: 'vmprotect',
                        value: "2-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'vmprotect',
                        value: "2-1-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'vmrecover',
                        value: "2-1-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "2-1-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "2-1-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'vm_instant_recovery',
                        value: "2-1-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'vm_instant_recovery',
                        value: "2-1-53",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'vm_grain_recovery',
                        value: "2-1-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "2-1-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'vm_platform_recovery',
                        value: "2-1-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'prcloud_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PRIVATE_CLOUD,
                label: LANG.UI_PUBLIC_PRIVATE_CLOUD,
                children: [
                    {
                        id: 'prcloud_protect',
                        value: "2-2-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'prcloud_protect',
                        value: "2-2-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'vm_prcloud_recovery',
                        value: "2-2-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "2-2-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "2-2-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'vm_prcloud_instant_recovery',
                        value: "2-2-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'vm_prcloud_instant_recovery',
                        value: "2-2-53",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'vm_prcloud_graininess_recovery',
                        value: "2-2-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "2-2-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'vm_prcloud_platform_recovery',
                        value: "2-2-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'awsprotect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PUBLIC_CLOUD,
                label: LANG.UI_PUBLIC_PUBLIC_CLOUD,
                children: [
                    {
                        id: 'awsprotect',
                        value: "2-3-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'awsprotect',
                        value: "2-3-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'vm_awsprotect_recover',
                        value: "2-3-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "2-3-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "2-3-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'vm_awsprotect_instant_recovery',
                        value: "2-3-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'vm_awsprotect_instant_recovery',
                        value: "2-3-53",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'vm_awsprotect_grain_recovery',
                        value: "2-3-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "2-3-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'vm_awsprotect_platform_recovery',
                        value: "2-3-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'complete_machine',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK,
                label: LANG.UI_BACKUP_DATA_MODULE_OS,
                children: [
                    {
                        id: 'complete_machine',
                        value: "5-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'complete_machine',
                        value: "5-1-35",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'machine_complete_recovery',
                        value: "5-1-36",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "5-1-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "5-1-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    {
                        id: 'machine_complete_instant_recovery',
                        value: "5-1-54",
                        label: LANG.UI_VISUAL_MIGRATION,
                    },
                    {
                        id: 'machine_complete_instant_recovery',
                        value: "5-1-49",
                        label: LANG.UI_VISUAL_INSTANT_NAME,
                    },
                    {
                        id: 'machine_complete_grain_recovery',
                        value: "5-1-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "5-1-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'machine_complete_platform_recovery',
                        value: "5-1-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'osbackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_VOLUME,
                label: LANG.UI_VOL_CDP_RECOVER_VOL,
                children: [
                    {
                        id: 'osbackup',
                        value: "5-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'osbackup',
                        value: "5-0-35",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'os_recovery',
                        value: "5-0-36",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "5-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'archive_new',
                        value: "5-0-19",
                        label: LANG.UI_VISUAL_ARCHIVE,
                    },
                    // {
                    //     value: "5-0-54",
                    //     label: LANG.UI_VISUAL_MIGRATION,
                    // },
                    // {
                    //     value: "5-0-53",
                    //     label: LANG.UI_VISUAL_INSTANT_NAME,
                    // }
                ]
            },
            {
                id: 'filebackup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.FILE,
                label: LANG.UI_FILE_FILE,
                children: [
                    {
                        id: 'filebackup',
                        value: "3-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'filebackup',
                        value: "3-1-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'file_recovery',
                        value: "3-1-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "3-1-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "3-1-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
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
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'nas_protect',
                        value: "11-2-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'nas_recovery',
                        value: "11-2-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "11-2-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "11-2-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
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
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'hadoop_protect',
                        value: "3-3-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'hadoop_recovery',
                        value: "3-3-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "3-3-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "3-3-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'obs_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.OBS,
                label: LANG.UI_VISUAL_OBS,
                children: [
                    {
                        id: 'obs_protect',
                        value: "3-4-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'obs_protect',
                        value: "3-4-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'obs_recovery',
                        value: "3-4-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "3-4-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "3-4-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'db_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DB,
                label: LANG.UI_AGENT_MODULE_DB,
                children: [
                    {
                        id: 'db_protect',
                        value: "4-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'db_protect',
                        value: "4-0-28",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'db_recovery',
                        value: "4-0-29",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "4-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'db_drill',
                        value: "4-0-64",
                        label: LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL
                    },
                    {
                        id: 'data_verification',
                        value: "4-0-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'office365_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.M365,
                label: LANG.UI_BACKUP_DATA_MODULE_M365,
                children: [
                    {
                        id: 'office365_protect',
                        value: "14-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'office365_protect',
                        value: "14-0-1",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'exchange_recovery',
                        value: "14-0-2",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "14-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    {
                        id: 'data_verification',
                        value: "14-0-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                ]
            },
            {
                id: 'k8s_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.KUBERNETES,
                label: LANG.UI_BACKUP_DATA_MODULE_K8S,
                children: [
                    {
                        id: 'k8s_protect',
                        value: "28-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'k8s_protect',
                        value: "28-0-56",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'k8s_recovery',
                        value: "28-0-57",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'copy_protect',
                        value: "28-0-17",
                        label: LANG.UI_VISUAL_COPY,
                    },
                    // {
                    //     id: 'data_verification',
                    //     value: "28-37",
                    //     label: LANG.UI_VISUAL_DATA_VERTIFY,
                    // },
                ]
            }
        ]
    },
    {
        id: 'real_time_protect',
        value: "real_time_protect",
        label: LANG.UI_REPORY_CDP,
        children: [
            {
                id: 'real_time_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_DATA_PROTECT_ALL,
                label: LANG.UI_PUBLIC_ALL
            },
            {
                id: 'complete_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_DISK,
                label: LANG.UI_BACKUP_DATA_MODULE_OS,
                children: [
                    {
                        id: 'complete_cdp_backup',
                        value: "10-1-2-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'complete_cdp_backup',
                        value: "10-1-2-32",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'machine_complete_volcdp_recovery',
                        value: "10-1-2-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'vol_cdp_complete_takeover',
                        value: "10-1-2-34",
                        label: LANG.UI_VISUAL_TAKEOVER,
                    },
                    {
                        id: 'machine_complete_volcdp_grain_recovery',
                        value: "10-1-2-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "10-1-2-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'machine_complete_volcdp_platform_recovery',
                        value: "10-1-2-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'vol_cdp_backup',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_VOLUME,
                label: LANG.UI_VOL_CDP_RECOVER_VOL,
                children: [
                    {
                        id: 'vol_cdp_backup',
                        value: "10-1-1-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'vol_cdp_backup',
                        value: "10-1-1-32",
                        label: LANG.UI_VISUAL_BACKUP,
                    },
                    {
                        id: 'p_vol_cdp_recovery',
                        value: "10-1-1-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'vol_cdp_takeover',
                        value: "10-1-1-34",
                        label: LANG.UI_PUBLIC_TAKEOVER,
                    }
                ]
            }
        ]
    },
    {
        id: 'data_copy',
        value: "data_copy",
        label: LANG.UI_CM_CDP_REPLICATION,
        children: [
            {
                id: 'data_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_ALL,
                label: LANG.UI_PUBLIC_ALL
            },
            {
                id: 'machine_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_DISK,
                label: LANG.UI_BACKUP_DATA_MODULE_OS,
                children: [
                    {
                        id: 'machine_copy',
                        value: "10-3-4-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'machine_copy',
                        value: "10-3-4-65",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'machine_complete_volcdp_recovery',
                        value: "10-3-4-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'machine_copy',
                        value: "10-3-4-34",
                        label: LANG.UI_PUBLIC_TAKEOVER,
                    },
                    {
                        id: 'machine_complete_volcdp_grain_recovery',
                        value: "10-3-4-55",
                        label: LANG.UI_VISUAL_RECOVERY_GRAIN,
                    },
                    {
                        id: 'data_verification',
                        value: "10-3-4-37",
                        label: LANG.UI_VISUAL_DATA_VERTIFY,
                    },
                    {
                        id: 'machine_complete_volcdp_platform_recovery',
                        value: "10-3-4-52",
                        label: LANG.UI_FILE_CROSS_RESTORE,
                    }
                ]
            },
            {
                id: 'vol_cdp_copy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_VOLUME,
                label: LANG.UI_VOL_CDP_RECOVER_VOL,
                children: [
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-65",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'p_vol_cdp_recovery',
                        value: "10-3-3-33",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                    {
                        id: 'vol_cdp_copy',
                        value: "10-3-3-34",
                        label: LANG.UI_PUBLIC_TAKEOVER,
                    }
                ]
            },
            {
                id: 'dbcdpcopy',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_DB,
                label: LANG.UI_AGENT_MODULE_DB,
                children: [
                    {
                        id: 'dbcdpcopy',
                        value: "12-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'dbcdpcopy',
                        value: "12-0-46",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'dbcdp_recovery',
                        value: "12-0-47",
                        label: LANG.UI_VISUAL_RECOVERY,
                    },
                ]
            },
            {
                id: 'file_copy_protect',
                value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_FILE,
                label: LANG.UI_FILE_FILE,
                children: [
                    {
                        id: 'file_copy_protect',
                        value: "26-0-0",
                        label: LANG.UI_PUBLIC_ALL
                    },
                    {
                        id: 'file_copy_protect',
                        value: "26-0-62",
                        label: LANG.UI_CM_CDP_REPLICATION,
                    },
                    {
                        id: 'file_copy_protect',
                        value: "26-0-63",
                        label: LANG.UI_FILE_COPY_TASK_TYPE_COMPARE,
                    },
                ]
            }
        ]
    },
];