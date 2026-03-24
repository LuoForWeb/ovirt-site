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
    '1-2': './platform/monitor-center/report/html/tape-report-detail.php',
    '1-3': './platform/monitor-center/report/html/node-report-detail.php'
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

// 节点报表多选项数组
const NODE_REPORT_MULTIPLE_OPTIONS = [
    {
        id: 'host_name',
        text: '节点名'
    },
    {
        id: 'node_type',
        text: '节点类型'
    },
    {
        id: 'node_function',
        text: '节电功能'
    },
    {
        id: 'node_pool_list',
        text: '节点资源池'
    },
    {
        id: 'node_resource_limit_flag',
        text: '资源限制'
    },
    {
        id: 'version',
        text: '版本'
    },
    {
        id: 'deploy_flag',
        text: '部署状态'
    },
    {
        id: 'status',
        text: '节点状态'
    },
]

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
    url: 'report/tape_group',
    searchPlaceholder: '按磁带组名搜索',
    columns: [
        {
            checkbox: true,
            sortable: false,
            width: 1,
            widthUnit: '%'
        },
        {
            field: 'name',
            title: '磁带组名',
            sortable: true,
        },
        {
            field: 'totalCapacity',
            title: '总容量',
            sortable: true
        },
        {
            field: 'availableCapacity',
            title: '可用容量',
            sortable: true
        },
        {
            field: 'status',
            title: '状态',
            sortable: true,
            formatter: function (status) {
                if(status) {
                    return `<span class="badge badge-success">正常</span>`;
                } else {
                    return `<span class="badge badge-secondary">异常</span>`;
                }
            }
        }
    ]
}

// 表单中节点表格options
const NODE_LIST_TABLE_OPTIONS = {
    url: 'report/node_details',
    searchPlaceholder: '按节点名搜索',
    columns: [
        {
            checkbox: true,
            sortable: false,
            width: 1,
            widthUnit: '%'
        },
        {
            field: 'host_name',
            title: '节点名',
            sortable: true,
        },
        {
            field: 'ip',
            title: 'IP地址',
            sortable: true
        },
        {
            field: 'status',
            title: '状态',
            sortable: true,
            formatter: function (value, row) {
                /**
                 * 1、如果节点处于未部署状态(即bd_module_server里面没有该节点任何记录)，那么显示--
                 * 2、如果bd_node的status值为0，再判断如果该节点在线(在线判定为该节点在bd_module_server里面的所有记录都在线)，显示在线；否则显示异常，鼠标移上去显示离线的服务
                 * 3、如果bd_node的status值为1，那么显示删除中，此时选择该节点删除时显示“当前节点正在删除中，请稍后重试”
                 * 4、如果bd_node的status值为2，那么显示修改中，此时选择该节点删除时显示“当前节点正在修改中，请稍后重试”
                 * 5、如果bd_node的status值为其他，那么显示为--
                 */
                if (!!!row.deploy_flag) {
                    return '--';
                }

                let text = '';
                let badgeClass = 'badge-secondary';
                let des = '';

                switch (parseInt(value)) {
                    case NODE_OPERATE_STATUS_ENUM.UNKNOWN:
                        badgeClass = 'badge-warning';
                        text = '异常';
                        des = row.offline_module_des;
                        if (!!row.online_flag) {
                            badgeClass = 'badge-success';
                            text = '正常';
                        }
                        break;
                    case NODE_OPERATE_STATUS_ENUM.MODIFYING:
                        text = '修改中';
                        break;
                    case NODE_OPERATE_STATUS_ENUM.DELETING:
                        badgeClass = 'badge-danger';
                        text = '删除中';
                        break;
                    case NODE_OPERATE_STATUS_ENUM.UPGRADING:
                        text = '升级中';
                        break;
                    case NODE_OPERATE_STATUS_ENUM.OFFLINE:
                        badgeClass = 'badge-warning';
                        text = '异常';
                        des = '节点离线';
                        break;
                    default:
                        return '--';
                }

                return `<span class="badge ${badgeClass}" data-bs-toogle="tooltip" title="${des}">${text}</span>`
            }
        }
    ]
}

const WEEKS = [
    {
        id: 'checkbox_mon',
        label: '星期一',
        value: 1
    },
    {
        id: 'checkbox_tue',
        label: '星期二',
        value: 2
    },
    {
        id: 'checkbox_wed',
        label: '星期三',
        value: 3
    },
    {
        id: 'checkbox_thu',
        label: '星期四',
        value: 4
    },
    {
        id: 'checkbox_fri',
        label: '星期五',
        value: 5
    },
    {
        id: 'checkbox_stu',
        label: '星期六',
        value: 6
    },
    {
        id: 'checkbox_sun',
        label: '星期日',
        value: 7
    }
];

const MONTHS = Array.from({ length: 31 }, (_, i) => ({
    id: `checkbox_permonth_${i + 1}`,
    label: `${i + 1}`,
    value: i + 1
}));

// 通知内容类型数组
const NOTIFY_CONTENT_TYPES = [
    {
        id: 'checkbox_notify_content_overview',
        label: '数据概览',
        value: 1
    },
    {
        id: 'checkbox_notify_content_tendency',
        label: '备份趋势',
        value: 2
    },
    {
        id: 'checkbox_notify_content_detail',
        label: '数据明细',
        value: 3
    },
];

// 通知内容类型
const NOTIFY_CONTENT = {
    OVERVIEW: 1,
    TENDENCY: 2,
    DETAIL: 3
};

// 导出数据明细类型数组
const EXPORT_DETAIL_RADIO_TYPES = [
    {
        id: 'radio_export_detail_all',
        label: '全部',
        value: 1
    },
    {
        id: 'radio_export_detail_custom',
        label: '自定义条数',
        value: 2
    },
];

// 导出数据明细类型
const EXPORT_DETAIL_TYPE = {
    ALL: 1,
    CUSTOM: 2
}

// 导出历史记录类型数组
const EXPORT_HISTORY_RECOEDS_RADIO_TYPES = [
    {
        id: 'radio_export_history_records_all',
        label: '全部',
        value: 1
    },
    {
        id: 'radio_export_history_records_custom',
        label: '自定义条数',
        value: 2
    },
];

// 附件格式类型
const ATTACHMENT_FORMATS = [
    {
        id: 'checkbox_attachment_format_excel',
        label: 'Excel',
        value: 1
    },
    {
        id: 'checkbox_attachment_format_word',
        label: 'Word',
        value: 2
    },
    {
        id: 'checkbox_attachment_format_pdf',
        label: 'PDF',
        value: 3
    }
];

// 单任务对象详情类型
const SINGLE_TASK_OBJECT_TYPES = [
    {
        id: 'checkbox_attachment_storage_usage_tendency',
        label: '存储占用历史趋势',
        value: 1
    },
    {
        id: 'checkbox_attachment_history_run_record',
        label: '历史运行记录',
        value: 2
    },
];

// 单任务对象详情类型
const SINGLE_TASK_OBJECT_TYPE = {
    STORAGE_USAGE_TENDENCY: 1,
    HISTORY_RUN_RECORD: 2
};

// 通知时间策略类型
const NOTIFY_TIMESTRATEGY_TYPE = {
    DAILY: 1,
    WEEKLY: 2,
    MONTHLY: 3,
    YEARLY: 4
};

// 虚拟机平台类型
const VCENTER_PLATFORM_TYPE = {
    VM: 'virtualization',
    PRIVATE_CLOUD: 'private',
    PUBLIC_CLOUD: 'public'
}
