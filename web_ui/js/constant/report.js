/*
 * @Author: ChengJiaFu
 * @Date: 2026-01-28 16:15:15
 * @Description: 报表相关常量配置
 * @version: 1.0
 */

// 模板类型
const TEMPLATE_TYPE = {
    BACKUP_RESOURCE: 1,
    PRODUCTION_RESOURCE: 2,
    DATA_PROTECTION: 3,
    TASK: 4,
    USER: 5
};

// 模板类型描述
const TEMPLATE_TYPE_DES = {
    1: '备份资源',
    2: '生产资源',
    3: '数据保护',
    4: '任务',
    5: '用户'
};

const BACKUP_SOURCE_TYPE = {
    STORAGE: 1,
    TAPE: 2,
    NODE: 3
}; // 备份资源类型

// 运行时间类型
const RUNNING_TIME_TYPE = {
    LAST_DAY: 1,
    LAST_THREE_DAYS: 2,
    LAST_WEEK: 3,
    LAST_MONTH: 4,
    CUSTOM: 5
};

// 报表详情页路由配置
const REPORT_DETAIL_ROUTE = {
    '1-1': './platform/monitor-center/report/html/storage-report-detail.php',
    '1-2': './platform/monitor-center/report/html/tape-report-detail.php'
}

// 存储报表多选项数组
const STORAGE_REPORT_MULTIPLE_OPTIONS = [
    {
        id: 'name',
        text: '存储别名'
    },
    {
        id: 'type',
        text: '类型'
    },
    {
        id: 'totalCapacity',
        text: '总容量'
    },
    {
        id: 'freeCapacity',
        text: '可用容量'
    },
    {
        id: 'usedCapacity',
        text: '已用容量'
    },
    {
        id: 'storageStatus',
        text: '状态'
    },
    {
        id: 'addTime',
        text: '添加时间'
    },
    {
        id: 'node',
        text: '节点'
    },
    {
        id: 'nodeStatus',
        text: '节点状态'
    },
    {
        id: 'utilizationRate',
        text: '装载率'
    }
];

// 磁带报表多选项数组
const TAPE_REPORT_MULTIPLE_OPTIONS = [
    {
        id: 'tapeName',
        text: '磁带名'
    },
    {
        id: 'tapeType',
        text: '类型'
    },
    {
        id: 'libName',
        text: '磁带库'
    },
    {
        id: 'groupName',
        text: '磁带组'
    },
    {
        id: 'totalSize',
        text: '总容量'
    },
    {
        id: 'freeSize',
        text: '可用容量'
    },
    {
        id: 'usedSize',
        text: '已用容量'
    },
    {
        id: 'tapeStatus',
        text: '状态'
    },
    {
        id: 'driverPath',
        text: '驱动器'
    },
    {
        id: 'backupSetName',
        text: '备份集'
    },
];

// 模块类型级联复选框组数组
const MODULE_CASCADER_GROUPS = [
    {
        id: 'scheduled_backup',
        value: 'scheduled_backup_check',
        text: '备份',
        children: [
            {
                id: 'module_vm',
                value: '2-1',
                text: '虚拟机',
            },
            {
                id: 'module_private_cloud',
                value: '2-2',
                text: '私有云',
            },
            {
                id: 'module_public_cloud',
                value: '2-3',
                text: '公有云',
            },
            {
                id: 'module_file',
                value: '3-1',
                text: '文件',
            },
            {
                id: 'module_nas',
                value: '11-2',
                text: 'NAS',
            },
            {
                id: 'module_hadoop',
                value: '3-3',
                text: 'Hadoop',
            },
            {
                id: 'module_obs',
                value: '3-4',
                text: '对象存储',
            },
            {
                id: 'module_db',
                value: '4-0',
                text: '数据库',
            },
            {
                id: 'module_m365',
                value: '14-0',
                text: 'Microsoft 365',
            },
            {
                id: 'module_machine',
                value: '5-1',
                text: '整机',
            },
            {
                id: 'module_k8s',
                value: '28-0',
                text: 'Kubernetes',
            }
        ]
    },
    {
        id: 'real_time_backup',
        value: 'real_time_backup_check',
        text: '实时保护',
        children: [
            {
                id: 'col_cdp_machine_backup',
                value: '10-2',
                text: '整机',
            }
        ]
    }
];

// 表单中存储设备表格options
const STORAGE_LIST_TABLE_OPTIONS = {
    url: 'report/storage_list',
    searchPlaceholder: '按存储别名搜索',
    columns: [
        {
            checkbox: true,
            sortable: false,
            width: 1,
            widthUnit: '%'
        },
        {
            field: 'name',
            title: '存储别名',
            sortable: true,
        },
        {
            field: 'type',
            title: '类型',
            sortable: true
        },
        {
            field: 'totalCapacity',
            title: '总容量',
            sortable: true
        },
        {
            field: 'freeCapacity',
            title: '可用容量',
            sortable: true
        },
        {
            field: 'storageStatus',
            title: '状态',
            sortable: true,
            formatter: function (value) {
                if(value) {
                    return `<span class="badge badge-success">正常</span>`;
                } else {
                    return `<span class="badge badge-secondary">异常</span>`;
                }
            }
        }
    ]
}

const TAPE_STATUS = {
    ONLINE: 1,
    OFFLINE: 2,
    MOVING: 3,
    READING: 4,
    WRITTING: 5,
    RETRIEVALING: 6,
    WAITING: 7,
    REWINDING: 8,
    READY: 9,
    SCANNING: 10,
    EXPORTING: 11,
    IMPORTING: 12,
}

// 表单中磁带设备表格options
const TAPE_LIST_TABLE_OPTIONS = {
    url: 'report/tape_details',
    searchPlaceholder: '按磁带名搜索',
    columns: [
        {
            checkbox: true,
            sortable: false,
            width: 1,
            widthUnit: '%'
        },
        {
            field: 'tapeName',
            title: '磁带名',
            sortable: true,
        },
        {
            field: 'groupName',
            title: '磁带组',
            sortable: true
        },
        {
            field: 'totalSize',
            title: '总容量',
            sortable: true
        },
        {
            field: 'freeSize',
            title: '可用容量',
            sortable: true
        },
        {
            field: 'tapeStatus',
            title: '状态',
            sortable: true,
            formatter: function (status) {
                switch (status) {
                    case TAPE_STATUS.ONLINE:
                        return '<span class="badge badge-success">' + '在线' + '</span>'
                    case TAPE_STATUS.OFFLINE:
                        return '<span class="badge badge-secondary">' + '离线' + '</span>'
                    case TAPE_STATUS.MOVING:
                        return '<span class="badge badge-danger">' + '移动中' + '</span>'
                    case TAPE_STATUS.READING:
                        return '<span class="badge badge-secondary">' + '读取中' + '</span>'
                    case TAPE_STATUS.WRITTING:
                        return '<span class="badge badge-secondary">' + '写入中' + '</span>'
                    case TAPE_STATUS.RETRIEVALING:
                        return '<span class="badge badge-secondary">' + '检索中' + '</span>'
                    case TAPE_STATUS.WAITING:
                        return '<span class="badge badge-secondary">' + '等待中' + '</span>'
                    case TAPE_STATUS.REWINDING:
                        return '<span class="badge badge-secondary">' + '倒带' + '</span>'
                    case TAPE_STATUS.READY:
                        return '<span class="badge badge-primary">' + '就绪' + '</span>'
                    case TAPE_STATUS.SCANNING:
                        return '<span class="badge badge-secondary">' + '扫描中' + '</span>'
                    case TAPE_STATUS.EXPORTING:
                        return '<span class="badge badge-secondary">' + '导出中' + '</span>'
                    case TAPE_STATUS.IMPORTING:
                        return '<span class="badge badge-secondary">' + '导入中' + '</span>'
                    default:
                        break;
                }
            }
        }
    ]
}