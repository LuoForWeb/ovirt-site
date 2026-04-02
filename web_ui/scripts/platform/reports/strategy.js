var Strategy = function () {
    const VM_REPORT_CHECKBOXES = [
        {
            id: 'report_vm_platform',
            value: 'vm_platform',
            text: LANG.UI_REPORY_VM_PLAY
        },
        {
            id: 'report_vm_number',
            value: 'vm_number',
            text: LANG.UI_REPORT_TOTAL_VM_NUM
        },
        {
            id: 'report_vm_protected_number',
            value: 'protected_number',
            text: LANG.UI_PUBLIC_PROTECTED_VM
        },
        {
            id: 'report_vm_backup_number',
            value: 'backup_number',
            text: LANG.UI_REPORT_BACKUP_COUNT
        },
        {
            id: 'report_vm_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // 虚拟机报表checkboxes
    const CLIENT_REPORT_CHECKBOXES = [
        {
            id: 'report_client_host_number',
            value: 'host_number',
            text: LANG.UI_REPORT_AGENT_ALL
        },
        {
            id: 'report_client_online_host',
            value: 'online_host',
            text: LANG.UI_REPORT_AGENT_ONLINE
        },
        {
            id: 'report_client_offline_host',
            value: 'offline_host',
            text: LANG.UI_REPORT_AGENT_OFFLINE
        },
        {
            id: 'report_client_protected_client',
            value: 'protected_client',
            text: LANG.UI_REPORT_AGENT_PROTECT
        },
        {
            id: 'report_client_unprotected_number',
            value: 'unprotected_number',
            text: LANG.UI_REPORT_AGENT_UNPROTECT
        },
        {
            id: 'report_client_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // 客户端报表checkboxes
    const VOL_REPORT_CHECKBOXES = [
         {
            id: 'report_vol_host_number',
            value: 'host_number',
            text: LANG.UI_REPORT_AGENT_ALL
        },
        {
            id: 'report_vol_protected_number',
            value: 'protected_number',
            text: LANG.UI_REPORT_CDP_AGENT_PROTECT
        },
        {
            id: 'report_vol_unprotected_number',
            value: 'unprotected_number',
            text: LANG.UI_REPORT_CDP_AGENT_UNPROTECT
        },
        {
            id: 'report_vol_task_number',
            value: 'task_number',
            text: LANG.UI_VIRTUAL_REAL_TIME_SYN_TASK_TOTAL
        },
        {
            id: 'report_vol_backup_set_number',
            value: 'backup_set_number',
            text: LANG.UI_REPORT_CDP_BACKUP_SET
        },
        {
            id: 'report_vol_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // 连续数据保护报表checkboxes
    const NAS_REPORT_CHECKBOXES = [
        {
            id: 'report_nas_device_number',
            value: 'device_number',
            text: LANG.UI_REPORT_DEVICE_ALL
        },
        {
            id: 'report_nas_online_number',
            value: 'online_number',
            text: LANG.UI_REPORT_DEVICE_ONLINE
        },
        {
            id: 'report_nas_offline_number',
            value: 'offline_number',
            text: LANG.UI_REPORT_DEVICE_OFFLINE
        },
        {
            id: 'report_nas_protected_number',
            value: 'protected_number',
            text: LANG.UI_REPORT_DEVICE_PROTECT
        },
        {
            id: 'report_nas_unprotected_number',
            value: 'unprotected_number',
            text: LANG.UI_REPORT_DEVICE_UNPROTECT
        },
        {
            id: 'report_nas_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // NAS报表checkboxes
    const STORAGE_REPORT_CHECKBOXES = [
        {
            id: 'report_storage_device_number',
            value: 'storage_device',
            text: LANG.UI_REPORT_DEVICE_ALL
        },
        {
            id: 'report_storage_online_number',
            value: 'online_number',
            text: LANG.UI_REPORT_DEVICE_ONLINE
        },
        {
            id: 'report_storage_offline_number',
            value: 'offline_number',
            text: LANG.UI_REPORT_DEVICE_OFFLINE
        },
        {
            id: 'report_storage_copy_data',
            value: 'copy_data',
            text: LANG.UI_VIRTUAL_COPY_DATA
        },
        {
            id: 'report_storage_archived_data',
            value: 'archived_data',
            text: LANG.UI_VIRTUAL_ARCHIVE_DATA
        },
        {
            id: 'report_storage_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // 存储报表checkboxes
    const TASK_REPORT_CHECKBOXES = [
        {
            id: 'report_task_num',
            value: 'task_num',
            text: LANG.UI_VIRTUAL_REAL_TIME_SYN_TASK_TOTAL
        },
        {
            id: 'report_success_task',
            value: 'success_task',
            text: LANG.UI_REPORT_SUCCESS_TASK
        },
        {
            id: 'report_abnormal_task',
            value: 'abnormal_task',
            text: LANG.UI_REPORT_ABNORMAL_TASK
        },
        {
            id: 'report_failed_task',
            value: 'failed_task',
            text: LANG.UI_REPORT_FAIL_TASK
        },
        {
            id: 'report_stop_task',
            value: 'stop_task',
            text: LANG.UI_REPORT_STOP_TASK
        }
    ]; // 任务报表checkboxes
    const APP_REPORT_CHECKBOXES = [
        {
            id: 'report_app_number',
            value: 'app_number',
            text: LANG.UI_REPORT_MANAGE_APP_TOTAL_NUM
        },
        {
            id: 'report_app_online',
            value: 'app_online',
            text: LANG.UI_REPORT_ONLINE_APP_TOTAL_NUM
        },
        {
            id: 'report_app_offline',
            value: 'app_offline',
            text: LANG.UI_REPORT_OFFLINE_APP_TOTAL_NUM
        },
        {
            id: 'report_app_protected',
            value: 'app_protected',
            text: LANG.UI_REPORT_PROTECTED_APP
        },
        {
            id: 'report_app_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // 应用报表checkboxes
    const PUBLIC_CLOUD_REPORT_CHECKBOXES = [
        {
            id: 'report_public_vm_platform',
            value: 'vm_platform',
            text: LANG.UI_REPORT_DATA_REPORT_NUMBER
        },
        {
            id: 'report_public_vm_number',
            value: 'vm_number',
            text: LANG.UI_VISUAL_PROTECT_EG
        },
        {
            id: 'report_public_protected_number',
            value: 'protected_number',
            text: LANG.UI_PLATFORM_VM_CLOUD_PLATFORM
        },
        {
            id: 'report_public_backup_number',
            value: 'backup_number',
            text: LANG.UI_REPORT_BACKUP_COUNT
        },
        {
            id: 'report_public_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // 公有云报表checkboxes
    const PRIVATE_CLOUD_REPORT_CHECKBOXES = [
        {
            id: 'report_private_vm_platform',
            value: 'vm_platform',
            text: LANG.UI_REPORT_DATA_REPORT_NUMBER
        },
        {
            id: 'report_private_vm_number',
            value: 'vm_number',
            text: LANG.UI_VISUAL_PROTECT_EG
        },
        {
            id: 'report_private_protected_number',
            value: 'protected_number',
            text: LANG.UI_PLATFORM_PRIVATE_CLOUD_PLATFORM
        },
        {
            id: 'report_private_backup_number',
            value: 'backup_number',
            text: LANG.UI_VIRTUAL_REAL_TIME_SYN_TASK_TOTAL
        },
        {
            id: 'report_private_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // 私有云报表checkboxes
    const OBS_REPORT_CHECKBOXES = [
        {
            id: 'report_obs_total',
            value: 'ob_total',
            text: LANG.UI_HOMEPAGE_OBS_TOTAL
        },
        {
            id: 'report_obs_normal',
            value: 'ob_normal',
            text: LANG.UI_REPORT_NORMAL_OBS
        },
        {
            id: 'report_obs_offliine',
            value: 'ob_offline',
            text: LANG.UI_REPORT_OFFLINE_OBS
        }
    ]; // 对象存储checkboxes
    const HADOOP_REPORT_CHECKBOXES = [
        {
            id: 'report_hadoop_total',
            value: 'hadoop_total',
            text: LANG.WEB_PLATFORM_DES_CLUSTER_ALL
        },
        {
            id: 'report_hadoop_auth',
            value: 'hadoop_auth',
            text: LANG.WEB_PLATFORM_DES_CLUSTER_AUTH
        },
        {
            id: 'report_hadoop_offliine',
            value: 'hadoop_offline',
            text: LANG.WEB_PLATFORM_DES_CLUSTER_ONLINE
        },
        {
            id: 'report_hadoop_online',
            value: 'hadoop_online',
            text: LANG.WEB_PLATFORM_DES_CLUSTER_OFFLINE
        }
    ]; // Hadoop checkboxes
    const FILE_COPY_REPORT_CHECKBOXES = [
        {
            id: 'report_file_copy_object',
            value: 'file_copy_object',
            text: LANG.UI_COPY_OBJECT
        },
        {
            id: 'report_file_copy_target',
            value: 'file_copy_target',
            text: LANG.UI_COPY_TARGET
        },
        {
            id: 'report_file_copy_protected',
            value: 'file_copy_protected',
            text: LANG.UI_PROTECTED_OHJECT
        },
        {
            id: 'report_file_copy_task_total',
            value: 'task_total',
            text: LANG.UI_VIRTUAL_REAL_TIME_SYN_TASK_TOTAL
        },
        {
            id: 'report_file_copy_data',
            value: 'copy_data',
            text: LANG.UI_COPY_DATA
        }
    ]; // 文件复制checkboxes
    const K8S_REPORT_CHECKBOXES = [
        {
            id: 'report_k8s_cluster_total',
            value: 'k8s_total',
            text: LANG.UI_KUBE_CLUSTER_LABEL
        },
        {
            id: 'report_k8s_online_cluster',
            value: 'k8s_online',
            text: LANG.UI_CLOUD_PLATFORM_ONLINE
        },
        {
            id: 'report_k8s_offline_cluster',
            value: 'k8s_offline',
            text: LANG.UI_CLOUD_PLATFORM_OFFLINE
        },
        {
            id: 'report_k8s_protected_cluster',
            value: 'k8s_protected',
            text: LANG.UI_CLIENT_PROTECTED
        },
        {
            id: 'report_k8s_backup_data',
            value: 'backup_data',
            text: LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA
        }
    ]; // K8S checkboxes
    const TEMPLATE_TYPE_TO_CHECKBOXES_MAP = {
        '1': VM_REPORT_CHECKBOXES,
        '2': CLIENT_REPORT_CHECKBOXES,
        '3': VOL_REPORT_CHECKBOXES,
        '4': NAS_REPORT_CHECKBOXES,
        '5': STORAGE_REPORT_CHECKBOXES,
        '6': TASK_REPORT_CHECKBOXES,
        '7': APP_REPORT_CHECKBOXES,
        '8': PUBLIC_CLOUD_REPORT_CHECKBOXES,
        '9': PRIVATE_CLOUD_REPORT_CHECKBOXES,
        '10': OBS_REPORT_CHECKBOXES,
        '11': HADOOP_REPORT_CHECKBOXES,
        '12': FILE_COPY_REPORT_CHECKBOXES,
        '13': K8S_REPORT_CHECKBOXES
    }; // 报表模版类型到checkboxes的映射
    let $customizeDataSelect = $('#customized_data_select');
    let ADD_CUSTOM_REPORT_FLAG = true; // 添加/修改自定义报表标记
    const VM_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'vm_ip',
            text: LANG.UI_REPORT_VM_IP
        },
        {
            id: 'ip',
            text: LANG.UI_VCENTER_VCENTER
        },
        {
            id: 'vm_name',
            text: LANG.UI_REPORT_VM_LIVING_EXAMPLE_NAME
        },
        {
            id: 'online',
            text: LANG.UI_REPORT_STATUS
        },
        {
            id: 'vm_type',
            text: LANG.UI_REPORT_VM_TYPE
        },
        {
            id: 'protect_status',
            text: LANG.UI_CLIENT_PROTECT
        },
        {
            id: 'last_backup_time',
            text: LANG.UI_CLIENT_CURRENT_TIME
        },
        {
            id: 'task',
            text: LANG.UI_REPORT_SET_TASK
        },
        {
            id: 'backup_number',
            text: LANG.UI_REPORT_BACKUP_COUNT
        },
        {
            id: 'total_object_size',
            text: LANG.UI_PUBLIC_VM_TOTAL_SIZE
        },
        {
            id: 'backup_data',
            text: LANG.UI_PUBLIC_VM_VALID_SIZE
        },
        {
            id: 'total_object_transport_size',
            text: LANG.UI_PUBLIC_TRANSFER_SIZE
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_PUBLIC_REAL_SIZE
        },
        {
            id: 'storage_nickname',
            text: LANG.UI_REPORT_STORAGE_NAME
        }
    ]; // 虚拟机报表多选项
    const CLIENT_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'name',
            text: LANG.UI_CLIENT_HOST_NICKNAME
        },
        {
            id: 'ip',
            text: LANG.UI_CLIENT_IP_ADDRESS
        },
        {
            id: 'os_type',
            text: LANG.UI_VM_SELECT_OS_TYPE
        },
        {
            id: 'module_type',
            text: LANG.UI_SEARCH_OBJ_TYPE
        },
        {
            id: 'user',
            text: LANG.UI_CLIENT_OWNER
        },
        {
            id: 'add_time',
            text: LANG.UI_JOB_CREATE_OR_MODIFI_TIME
        },
        {
            id: 'protect_status',
            text: LANG.UI_REPORT_PROTECT
        },
        {
            id: 'task',
            text: LANG.UI_REPORT_SET_TASK
        },
        {
            id: 'full_backup_number',
            text: LANG.UI_REPORT_FULL_BACKUP
        },
        {
            id: 'incre_backup_number',
            text: LANG.UI_REPORT_INCRE_BACKUP
        },
        {
            id: 'dif_backup_number',
            text: LANG.UI_REPORT_DIFF_BACKUP
        },
        {
            id: 'archived_log_number',
            text: LANG.UI_DATA_TYPE_ARCHIVELOG_NUM
        },
        {
            id: 'online',
            text: LANG.UI_MICROSOFT365_ONLINE_FLAG
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_DB_WRITE_SIZE
        },
        {
            id: 'total_object_valid_size',
            text: LANG.UI_PUBLIC_VM_VALID_SIZE
        },
        {
            id: 'backup_data',
            text: LANG.UI_GRAIN_JOB_TOTAL_SIZE
        },
    ]; // 客户端报表多选项
    const VOL_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'host_name',
            text: LANG.UI_COPY_DETAIL_HOST_NAME
        },
        {
            id: 'ip',
            text: LANG.UI_OS_DETAILS_IP
        },
        {
            id: 'os_type',
            text: LANG.UI_VM_OS_TYPE
        },
        {
            id: 'user',
            text: LANG.UI_REPORT_USER
        },
        {
            id: 'add_time',
            text: LANG.UI_PUBLIC_ADD_TIME
        },
        {
            id: 'protect',
            text: LANG.UI_REPORT_PROTECT
        },
        {
            id: 'protect_app',
            text: LANG.UI_REPORT_PROTECT_APP
        },
        {
            id: 'task',
            text: LANG.UI_PLATFORM_ASSOCIA_TASK
        },
        {
            id: 'last_backup_time',
            text: LANG.UI_REPORT_LAST_TIME
        },
        {
            id: 'backup_set',
            text: LANG.UI_REPORT_BACKUP_SET_COUNT
        },
        {
            id: 'backup_status',
            text: LANG.UI_SEARCH_BACKUP_STATUS
        },
        {
            id: 'backup_data',
            text: LANG.UI_HOMEPAGEPRO_BACKUP_DATA
        },
        {
            id: 'storage_nickname',
            text: LANG.UI_REPORT_STORAGE_NAME
        },
        {
            id: 'auto_takeover',
            text: LANG.UI_REPORT_TACKOVER
        }
    ]; // 连续数据保护报表多选项
    const NAS_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'ip',
            text:LANG.UI_DRILLS_IP_ADDRESS
        },
        {
            id: 'device_name',
            text: LANG.UI_REPORT_DEVICE_NAME
        },
        {
            id: 'shared_path',
            text: LANG.UI_REPORT_SHARE_PATH
        },
        {
            id: 'device_type',
            text: LANG.UI_SYSTEM_MONITOR_DEVICE_TYPE
        },
        {
            id: 'add_time',
            text: LANG.UI_JOB_CREATE_OR_MODIFI_TIME
        },
        {
            id: 'status',
            text: LANG.UI_REPORT_DEVICE_STATUS
        },
        {
            id: 'auth_status',
            text: LANG.UI_REPORT_AUTH_STATUS
        },
        {
            id: 'protect_status',
            text: LANG.UI_REPORT_PROTECT
        },
        {
            id: 'task',
            text: LANG.UI_REPORT_SET_TASK
        },
        {
            id: 'backup_data',
            text: LANG.UI_HOMEPAGEPRO_BACKUP_DATA
        }
    ]; // NAS报表多选项
    const STORAGE_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'name',
            text: LANG.UI_REPORT_STORAGE_NAME
        },
        {
            id: 'type',
            text: LANG.UI_SEARCH_STORAGE_TYPE
        },
        {
            id: 'total_capacity',
            text: LANG.UI_OS_PLUG_TOTAL_SIZE
        },
        {
            id: 'available_capacity',
            text: LANG.UI_PUBLIC_FREE_STORAGE_SIZE
        },
        {
            id: 'used_capacity',
            text: LANG.UI_HOMEPAGE_USED_STORAGE
        },
        {
            id: 'storage_status',
            text: LANG.UI_SEARCH_STORAGE_STATUS
        },
        {
            id: 'node',
            text: LANG.UI_PUBLIC_STORAGE_IN_NODE
        },
        {
            id: 'node_status',
            text: LANG.UI_NODE_NODE_STATUS
        }
    ]; // 存储报表多选项
    const TASK_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'task_name',
            text: LANG.UI_SEARCH_TASK_NAME
        },
        {
            id: 'task_type',
            text: LANG.UI_TASK_AWS_JOB_TYPE
        },
        {
            id: 'module_type',
            text: LANG.UI_SEARCH_MODE_TYPE
        },
        {
            id: 'task_status',
            text: LANG.UI_SEARCH_TASK_STATUS
        },
        {
            id: 'create_time',
            text: LANG.UI_JOB_CREATE_OR_MODIFI_TIME
        },
        {
            id: 'last_start_time',
            text: LANG.UI_REPORT_LAST_TIME
        },
        {
            id: 'uptime_time',
            text: LANG.UI_PUBLIC_CONTINUE_RUN_TIME
        },
        {
            id: 'finish_time',
            text: LANG.UI_PUBLIC_FINISH_TIME
        },
        {
            id: 'next_run_time',
            text: LANG.UI_REPORT_NEXT_RUNNING_TIME
        },
        {
            id: 'total_object_size',
            text: LANG.UI_HOMEPAGEPRO_BACKUP_DATA
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_PUBLIC_REAL_SIZE
        },
        {
            id: 'total_object_transport_size',
            text: LANG.UI_PUBLIC_TRANSFER_SIZE
        },
        {
            id: 'storage_nickname',
            text: LANG.UI_REPORT_BELONG_STORAGE
        },
        {
            id: 'create_user',
            text: LANG.UI_REPORT_CREATE_NAME
        }
    ]; // 任务报表多选项
    const APP_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'task_name',
            text: LANG.UI_SEARCH_TASK_NAME
        },
        {
            id: 'organization_name',
            text: LANG.UI_MICROSOFT365_ORGANIZATION_NAME
        },
        {
            id: 'start_time',
            text: LANG.UI_PUBLIC_START_TIME
        },
        {
            id: 'finish_time',
            text: LANG.UI_PUBLIC_FINISH_TIME
        },
        {
            id: 'total_object_size',
            text: LANG.UI_GRAIN_JOB_TOTAL_SIZE
        },
        {
            id: 'total_object_valid_size',
            text: LANG.UI_PUBLIC_VM_VALID_SIZE
        },
        {
            id: 'total_object_transport_size',
            text: LANG.UI_PUBLIC_TRANSFER_SIZE
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_PUBLIC_REAL_SIZE
        },
        {
            id: 'task_status',
            text: LANG.UI_PUBLIC_TASK_STATUS
        },
        {
            id: 'user',
            text: LANG.UI_MICROSOFT365_USER
        },
    ]; // 应用报表多选项
    const PUBLIC_CLOUD_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'vm_ip',
            text: LANG.UI_TENANT_AWS_IP
        },
        {
            id: 'ip',
            text: LANG.UI_PLATFORM_VM_CLOUD_PLATFORM
        },
        {
            id: 'vm_name',
            text: LANG.UI_DB_INSTANCE_NAME
        },
        {
            id: 'online',
            text: LANG.UI_REPORT_STATUS
        },
        {
            id: 'vm_type',
            text: LANG.UI_PLATFORM_VM_CLOUD_PLATFORM_TYPE
        },
        {
            id: 'protect_status',
            text: LANG.UI_CLIENT_PROTECT
        },
        {
            id: 'last_backup_time',
            text: LANG.UI_CLIENT_CURRENT_TIME
        },
        {
            id: 'task',
            text: LANG.UI_REPORT_SET_TASK
        },
        {
            id: 'backup_number',
            text: LANG.UI_REPORT_BACKUP_COUNT
        },
        {
            id: 'total_object_size',
            text: LANG.UI_PUBLIC_VM_TOTAL_SIZE
        },
        {
            id: 'backup_data',
            text: LANG.UI_PUBLIC_VM_VALID_SIZE
        },
        {
            id: 'total_object_transport_size',
            text: LANG.UI_PUBLIC_TRANSFER_SIZE
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_PUBLIC_REAL_SIZE
        },
        {
            id: 'storage_nickname',
            text: LANG.UI_REPORT_STORAGE_NAME
        }
    ]; // 公有云报表多选项
    const PRIVATE_CLOUD_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'vm_ip',
            text: LANG.UI_TENANT_AWS_IP
        },{
            id: 'ip',
            text: LANG.UI_PLATFORM_PRIVATE_CLOUD_PLATFORM
        },{
            id: 'vm_name',
            text: LANG.UI_DB_INSTANCE_NAME
        },{
            id: 'online',
            text: LANG.UI_REPORT_STATUS
        },{
            id: 'vm_type',
            text: LANG.UI_PLATFORM_PRIVATE_CLOUD_PLATFORM_TYPE
        },{
            id: 'protect_status',
            text: LANG.UI_CLIENT_PROTECT
        },{
            id: 'last_backup_time',
            text: LANG.UI_CLIENT_CURRENT_TIME
        },{
            id: 'task',
            text: LANG.UI_REPORT_SET_TASK
        },{
            id: 'backup_number',
            text: LANG.UI_REPORT_BACKUP_COUNT
        },{
            id: 'total_object_size',
            text: LANG.UI_PUBLIC_VM_TOTAL_SIZE
        },{
            id: 'backup_data',
            text: LANG.UI_PUBLIC_VM_VALID_SIZE
        },{
            id: 'total_object_transport_size',
            text: LANG.UI_PUBLIC_TRANSFER_SIZE
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_PUBLIC_REAL_SIZE
        },{
            id: 'storage_nickname',
            text: LANG.UI_REPORT_STORAGE_NAME
        }
    ]; // 私有云报表多选项
    const OBS_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'vendor',
            text: LANG.UI_STORAGE_CLOUD_VENDOR
        },{
            id: 'endpoint_override',
            text: LANG.UI_OBS_SERVER_ENDPOINT
        },{
            id: 'nickname',
            text: LANG.UI_PLATFORM_DES_OBS_NAME
        },{
            id: 'obs_create_time',
            text: LANG.UI_PUBLIC_ADD_TIME
        },{
            id: 'status',
            text: LANG.UI_PUBLIC_STATUS
        },{
            id: 'creator',
            text: LANG.UI_REPORT_BUILDER
        },{
            id: 'owner',
            text: LANG.UI_CLIENT_OWNER
        }
    ]; // 对象存储报表多选项
    const HADOOP_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'cluster_name',
            text: LANG.UI_HADOOP_CLUSTER_NAME
        },{
            id: 'node_number',
            text: LANG.UI_HADOOP_NODE_NUM
        },{
            id: 'add_time',
            text: LANG.UI_PUBLIC_ADD_TIME
        },{
            id: 'auth_status',
            text: LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS
        },{
            id: 'online_flag',
            text: LANG.UI_HADOOP_CLUSTER_STATUS
        }
    ]; // Hadoop报表多选项
    const FILE_COPY_REPORT_MULTIPLE_OPTIONS = [
        {
            id: 'task_name',
            text: LANG.UI_SEARCH_TASK_NAME
        },
        {
            id: 'source_object',
            text: LANG.UI_FILE_DETAIL_SRC_NAME
        },
        {
            id: 'target_object',
            text: LANG.UI_FILE_DETAIL_DES_NAME
        },
        {
            id: 'start_time',
            text: LANG.UI_PUBLIC_START_TIME
        },
        {
            id: 'finish_time',
            text: LANG.UI_PUBLIC_FINISH_TIME
        },
        {
            id: 'total_object_size',
            text: LANG.UI_GRAIN_JOB_TOTAL_SIZE
        },
        {
            id: 'total_object_valid_size',
            text: LANG.UI_PUBLIC_VM_VALID_SIZE
        },
        {
            id: 'total_object_transport_size',
            text: LANG.UI_PUBLIC_TRANSFER_SIZE
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_PUBLIC_REAL_SIZE
        },
        {
            id: 'task_status',
            text: LANG.UI_PUBLIC_TASK_STATUS
        },
        {
            id: 'user',
            text: LANG.UI_MICROSOFT365_USER
        },
    ]; // 文件复制报表多选项
    const K8S_REPORT_MUTIPLE_OPTIONS = [
        {
            id: 'task_name',
            text: LANG.UI_SEARCH_TASK_NAME
        },
        {
            id: 'start_time',
            text: LANG.UI_PUBLIC_START_TIME
        },
        {
            id: 'finish_time',
            text: LANG.UI_PUBLIC_FINISH_TIME
        },
        {
            id: 'total_object_size',
            text: LANG.UI_GRAIN_JOB_TOTAL_SIZE
        },
        {
            id: 'total_object_valid_size',
            text: LANG.UI_PUBLIC_VM_VALID_SIZE
        },
        {
            id: 'total_object_transport_size',
            text: LANG.UI_PUBLIC_TRANSFER_SIZE
        },
        {
            id: 'total_object_write_size',
            text: LANG.UI_PUBLIC_REAL_SIZE
        },
        {
            id: 'task_status',
            text: LANG.UI_PUBLIC_TASK_STATUS
        },
        {
            id: 'user',
            text: LANG.UI_MICROSOFT365_USER
        },
    ];
    const TEMPLATE_TYPE_TO_MULTIPLE_SELECT_MAP = {
        '1': VM_REPORT_MULTIPLE_OPTIONS,
        '2': CLIENT_REPORT_MULTIPLE_OPTIONS,
        '3': VOL_REPORT_MULTIPLE_OPTIONS,
        '4': NAS_REPORT_MULTIPLE_OPTIONS,
        '5': STORAGE_REPORT_MULTIPLE_OPTIONS,
        '6': TASK_REPORT_MULTIPLE_OPTIONS,
        '7': APP_REPORT_MULTIPLE_OPTIONS,
        '8': PUBLIC_CLOUD_REPORT_MULTIPLE_OPTIONS,
        '9': PRIVATE_CLOUD_REPORT_MULTIPLE_OPTIONS,
        '10': OBS_REPORT_MULTIPLE_OPTIONS,
        '11': HADOOP_REPORT_MULTIPLE_OPTIONS,
        '12': FILE_COPY_REPORT_MULTIPLE_OPTIONS,
        '13': K8S_REPORT_MUTIPLE_OPTIONS
    }; // 报表类型到multiple select的映射
    const WEEKLY_CHECKBOX_GROUP = [
        {
            id: 'weekly_checkbox_mon',
            value: '1',
            text: LANG.UI_STRATEGY_MONDAY
        },
        {
            id: 'weekly_checkbox_tue',
            value: '2',
            text: LANG.UI_STRATEGY_TUESDAY
        },
        {
            id: 'weekly_checkbox_wed',
            value: '3',
            text: LANG.UI_STRATEGY_WEDNESDAY
        },
        {
            id: 'weekly_checkbox_thu',
            value: '4',
            text: LANG.UI_STRATEGY_THURSDAY
        },
        {
            id: 'weekly_checkbox_fri',
            value: '5',
            text: LANG.UI_STRATEGY_FRIDAY
        },
        {
            id: 'weekly_checkbox_sat',
            value: '6',
            text: LANG.UI_STRATEGY_SATURDAY
        },
        {
            id: 'weekly_checkbox_sun',
            value: '7',
            text: LANG.UI_STRATEGY_SUNDAY
        }
    ];
    const HISTORY_RUNNING_CHECKBOX_GROUP = [
        {
            id: 'running_tendency',
            value: 'history',
            text: LANG.UI_HISTORY_RUNNING_TENDENCY
        }
    ];
    const NOTICE_STRATEGY_TYPE = {
        DAILY: 1,
        WEEKLY: 2,
        MONTHLY: 3,
        ANNUAL: 4
    };
    let MONTHLY_CHECKBOX_GROUP = [];
    let CURRENT_REPORT_TEMPLATE = {
        template_uuid: '',
        template_type: '',
        custom_field: []
    }

    let queryParams = {};
    let selectedRows = [];

    // <-------------------------  BEGIN CUSTOM STRATEGY FORM  ------------------------------>

    /**
     * 初始化添加自定义报表抽屉
     */
    const initAddReportDwawer = () => {
        $('#title').html(LANG.UI_ADD_CUSTOM_REPORT_TEMPLATE);
        $('#template_name').val('');
        $('.template-name-form-item').removeClass('has-error').removeClass('has-success');
        $('.template-name-form-item .input-icon .fa').removeClass('fa-warning').removeClass('fa-check');
        $('.validate-template-name-tip').addClass('display-none');

        cancelAllCheckboxGroup('overview_data_checkbox_group'); // 重置概览数据checkbox group
        cancelAllCheckboxGroup('running_tendency_checkbox_group'); // 重置历史运行checkbox

        $('#email_notice').bootstrapSwitch('state', false); // 重置邮件通知开关
        ADD_CUSTOM_REPORT_FLAG = true;

        $('#custom_report_drawer').drawer('show');
    }

    /**
     * 重置多选下拉
     * @param {*} templateType 模版类型
     * @param {*} options 模版类型对应的下拉数组
     * @returns 
     */
    const resetMultipleSelect = (templateType, options) => {
        if (!Array.isArray(options) || options.length === 0) {
            return;
        }

        if ($customizeDataSelect.children().length > 0) {
            // 清空上一个定制数据多选框
            $customizeDataSelect.empty();
        }

        let result = '';

        options.forEach((item, index) => {
            let html = '';
            if (ADD_CUSTOM_REPORT_FLAG) { // 添加报表
                if (index < 5) {
                    html = `<option value="${item.id}" selected="selected">${item.text}</option>`;
                } else {
                    html = `<option value="${item.id}">${item.text}</option>`;
                }
            } else { // 修改报表
                if (CURRENT_REPORT_TEMPLATE.template_type !== templateType && index < 5) { // 如果切换的不是当前修改的模版类型，那么要默认勾上前五项
                    html = `<option value="${item.id}" selected="selected">${item.text}</option>`;
                } else { // 如果切换到是当前修改类型，先不给selected，下面会做处理；或者indez >= 5 也不给selected
                    html = `<option value="${item.id}">${item.text}</option>`;
                }
            }
            
            result += html;
        });

        $customizeDataSelect.append(result);

        // 如果是修改报表，还要勾选上之前所勾选的
        if (CURRENT_REPORT_TEMPLATE.template_type === templateType) {
            CURRENT_REPORT_TEMPLATE.custom_field.forEach(item => {
                $customizeDataSelect.find(`option[value="${item}"]`).prop('selected', true);
            });
        }

        // 表示下拉菜单显示时最多显示 5 项，超出会出现滚动条
        $customizeDataSelect.selectpicker({ size: 5 });

        $customizeDataSelect.selectpicker('refresh');
    }

    /**
     * 取消勾选所有chexkbox
     * @param {*} checkboxGroupId 
     */
    const cancelAllCheckboxGroup = (checkboxGroupId) => {
        let formCheckInputs = $(`#${checkboxGroupId} .checkbox-wrapper__item`).children('.form-check-input');

        for (let i = 0; i < formCheckInputs.length; i++) {
            if ($(formCheckInputs[i]).prop('checked')) {
                $(formCheckInputs[i]).prop('checked', '');
            }
        }
    }

    /**
     * 初始化概览数据checkbox group
     * @param {*} boxes 
     * @returns 
     */
    const initOverviewCheckboxGroup = (boxes) => {
        if (!Array.isArray(boxes) || boxes.length === 0) {
            return;
        }

        if ($('#overview_data_checkbox_group').children().length > 0) {
            // 清空上一个checkbox group
            $('#overview_data_checkbox_group').empty();
        }

        let result = '';

        boxes.forEach(item => {
            const html = '<div class="checkbox-wrapper__item form-check">' +
                            '<input class="form-check-input" name="checkbox" value="' + item.value + '" type="checkbox" id="' + item.id + '">' +
                            '<label class="form-check-label" for="' + item.id + '" title="' + item.text + '">' +
                                item.text +
                            '</label>' +
                        '</div>';

            result += html;
        });

        $('#overview_data_checkbox_group').append(result);
    }

    /**
     * 切换模版类型
     */
    const handleTemplateTypeChange = () => {
        let templateType = $('#template_type').val();

        // 重新渲染概览统计checkbox group
        initOverviewCheckboxGroup(TEMPLATE_TYPE_TO_CHECKBOXES_MAP[templateType]);

        // 重新渲染定制数据多选框
        resetMultipleSelect(parseInt(templateType), TEMPLATE_TYPE_TO_MULTIPLE_SELECT_MAP[templateType]);
    }

    /**
     * 初始化运行趋势checkbox
     */
    const initRunningTendencyCheckbox = () => {
        let html = '<div class="checkbox-wrapper__item form-check">' +
                        '<input class="form-check-input" name="checkbox" value="history" type="checkbox" id="running_tendency">' +
                        '<label class="form-check-label" for="running_tendency">' +
                            LANG.UI_HISTORY_RUNNING_TENDENCY +
                        '</label>' +
                    '</div>';

        $('#running_tendency_checkbox_group').append(html);
    }

    /**
     * 初始化定制数据多选框 - 默认渲染虚拟机模块的
     */
    const initCustomizedDataSelect = (options) => {
        if (!Array.isArray(options) || options.length === 0) {
            return;
        }

        if ($customizeDataSelect.children().length > 0) {
            // 清空上一个定制数据多选框
            $customizeDataSelect.empty();
        }

        let result = '';

        options.forEach((item, index) => {
            let html = '';
            if (index < 5) {
                html = `<option value="${item.id}" selected="selected">${item.text}</option>`;
            } else {
                html = `<option value="${item.id}">${item.text}</option>`;
            }
            
            result += html;
        });

        $customizeDataSelect.append(result);

        // 表示下拉菜单显示时最多显示 5 项，超出会出现滚动条
        $customizeDataSelect.selectpicker({ size: 5 });

        $customizeDataSelect.selectpicker('refresh');
    }

    /**
     * 邮件通知策略开关
     */
    const handleEmailNoticeChange = () => {
        let emailNoticeFlag = $('#email_notice').get(0).checked;
        if (emailNoticeFlag) {
            $('.email-notice-strategy-form-item').removeClass('display-none');
            $('.email-address-form-item').removeClass('display-none');
        } else {
            $('.email-notice-strategy-form-item').addClass('display-none');
            $('.email-address-form-item').addClass('display-none');

            // 重置日报
            $('#daily_report_notice').bootstrapSwitch('state', false);

            // 重置周报
            $('#weekly_report_notice').bootstrapSwitch('state', false);

            // 重置月报
            $('#monthly_report_notice').bootstrapSwitch('state', false);

            // 重置年报
            $('#annual_report_notice').bootstrapSwitch('state', false);

            // 重置通知策略校验描述
            $('.validate-notice-strategy-tip').addClass('display-none');

            // 重置邮件通知策略tabs
            $('.nav-tab-radios').find('.nav-tabs-item.active').removeClass('active');
            $('#notice_strategy_content').find('.tab-pane.active').removeClass('active');
            $('.nav-tab-radios').find('.nav-tabs-item.daily-report-tabs-item').addClass('active');
            $('#daily_report_pane').addClass('active').addClass('in');

            // 清空邮箱地址
            $('#emails').val('');
            // 清空校验样式
            $('.email-address-form-item').removeClass('has-error').removeClass('has-success');
            $('.email-address-form-item .input-icon .fa').removeClass('fa-warning').removeClass('fa-check');
            $('.validate-emails-tip').addClass('display-none');
            $('.validate-emails-tip').html('');

            // 清空描述
            // $('#description').val('');
        }
    }

    /**
     * 重置nav-tab-radios高度，保持border样式统一，避免有时短有时长的情况
     */
    const resetNavTabRadisHeight = () => {
        const height = $('.custom-strategy-form-item .custom-strategy-form-item__content .tab-content').height();
        $('.custom-strategy-form-item .custom-strategy-form-item__content .nav-tab-radios').css({'height': height});
    }

    /**
     * 获取当前时间给时间选择器默认值
     */
    const getNowTime = () => {
        let now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        return `${hours}:${minutes}:${seconds}`;
    }

    /**
     * 配置日报通知策略开关
     */
    const handleDailyReportNoticeChange = () => {
        let dailyNoticeFlag = $('#daily_report_notice').get(0).checked;

        if (dailyNoticeFlag) {
            $('.daily-report-time-form-item').removeClass('display-none');
            $('#daily_report_i').removeClass('opacity-0');
            $('.daily-report-tabs-item').addClass('checked');
        } else {
            $('.daily-report-time-form-item').addClass('display-none');
            $('#daily_report_i').addClass('opacity-0');
            $('.daily-report-tabs-item').removeClass('checked');

            // 移除上一次校验样式
            $('.validate-daily-time-tip').addClass('display-none');
            // 赋默认现在时间
            $('#daily_report_time').val(getNowTime());
        }

        resetNavTabRadisHeight();
    }

    /**
     * 
     * @param {Array<string|number>} values - 需要勾选的项的值组成的数组
     */
    /**
     * 设置指定值的 checkbox 为选中状态
     * @param {*} values - 需要勾选的项的值组成的数组
     * @param {*} checkboxGroup - checkbox group
     * @returns 
     */
    const setCheckedItems = (values, checkboxGroup) => {
        // 确保传入的是数组
        if (!Array.isArray(values) || values.length === 0) {
            return;
        }

        // 遍历 WEEKLY_CHECKBOX_GROUP 数组
        checkboxGroup.forEach(item => {
            const checkbox = document.getElementById(item.id);

            // 如果 checkbox 存在，则根据传入的值设置 checked 状态
            if (checkbox) {
                checkbox.checked = values.includes(item.value);
            }
        });
    };

    /**
     * 初始化每周checkbox group
     */
    const initWeeklyCheckboxGroup = () => {
        let result = '';

        WEEKLY_CHECKBOX_GROUP.forEach(item => {
            const html = `  <div class="checkbox-wrapper__item form-check">
                                <input class="form-check-input" type="checkbox" name="checkbox" id="${item.id}" value="${item.value}" ${item.id === 'weekly_checkbox_fri' ? 'checked="true"' : ''}>
                                <label class="form-check-label" for="${item.id}">${item.text}</label>
                            </div>`;

            result += html;
        });

        $('#weekly_checkbox_group').append(result);
    }

    /**
     * 初始化每月checkbox group
     */
    const initMonthlyCheckboxGroup = () => {
        let result = '';

        for (let i = 1; i <= 31; i++) {
            const html = `  <div class="checkbox-wrapper__item form-check">
                                <input class="form-check-input" type="checkbox" name="checkbox" id="monthly_checkbox_${i}" value="${i}" ${i === 1 || i === 15 ? 'checked="true"' : ''}>
                                <label class="form-check-label" for="monthly_checkbox_${i}">${i}</label>
                            </div>`;

            result += html;

            MONTHLY_CHECKBOX_GROUP.push({
                id: `monthly_checkbox_${i}`,
                value: `${i}`,
                text: `${i}`
            });
        }

        $('#monthly_checkbox_group').append(result);
    }

    /**
     * 配置周报通知策略开关
     */
    const handleWeeklyReportNoticeChange = () => {
        let weeklyNoticeFlag = $('#weekly_report_notice').get(0).checked;

        if (weeklyNoticeFlag) {
            $('.weekly-checkbox-form-item').removeClass('display-none');
            $('.weekly-report-time-form-item').removeClass('display-none');
            $('#weekly_report_i').removeClass('opacity-0');
            $('.weekly-report-tabs-item').addClass('checked');
        } else {
            $('.weekly-checkbox-form-item').addClass('display-none');
            $('.weekly-report-time-form-item').addClass('display-none');
            $('#weekly_report_i').addClass('opacity-0');
            $('.weekly-report-tabs-item').removeClass('checked');

            // 移除上一次校验样式
            $('.validate-weekly-checkbox-tip').addClass('display-none');
            $('.validate-weekly-time-tip').addClass('display-none');
            // 默认勾选上周五
            setCheckedItems(['5'], WEEKLY_CHECKBOX_GROUP);
            // 赋默认现在时间
            $('#weekly_report_time').val(getNowTime());
        }

        resetNavTabRadisHeight();
    }

    /**
     * 配置月报通知策略开关
     */
    const handleMonthlyReportNoticeChange = () => {
        let monthlyNoticeFlag = $('#monthly_report_notice').get(0).checked;

        if (monthlyNoticeFlag) {
            $('.monthly-checkbox-form-item').removeClass('display-none');
            $('.monthly-report-time-form-item').removeClass('display-none');
            $('#monthly_report_i').removeClass('opacity-0');
            $('.monthly-report-tabs-item').addClass('checked');
        } else {
            $('.monthly-checkbox-form-item').addClass('display-none');
            $('.monthly-report-time-form-item').addClass('display-none');
            $('#monthly_report_i').addClass('opacity-0');
            $('.monthly-report-tabs-item').removeClass('checked');

            // 移除上一次校验样式
            $('.validate-monthly-checkbox-tip').addClass('display-none');
            $('.validate-monthly-time-tip').addClass('display-none');
            // 默认勾选上1号和15号
            setCheckedItems(['1', '15'], MONTHLY_CHECKBOX_GROUP);
            // 赋默认现在时间
            $('#monthly_report_time').val(getNowTime());
        }

        resetNavTabRadisHeight();
    }

    /**
     * 配置年报通知策略开关
     */
    const handleAnnualReportNoticeChange = () => {
        let annualNoticeFlag = $('#annual_report_notice').get(0).checked;

        if (annualNoticeFlag) {
            $('#annual_report_i').removeClass('opacity-0');
            $('.annual-report-tabs-item').addClass('checked');
        } else {
            $('#annual_report_i').addClass('opacity-0');
            $('.annual-report-tabs-item').removeClass('checked');
        }

        resetNavTabRadisHeight();
    }

    /**
     * 获取 checkbox group 选中的值
     * @param {*} checkboxGroupId 
     * @returns 
     */
    const getCheckedboxGroupValues = (checkboxGroupId) => {
        let checkboxItems = $(`#${checkboxGroupId}`).find('.checkbox-wrapper__item');

        let result = [];

        for (let i = 0; i < checkboxItems.length; i++) {
            let checkedBox = $(checkboxItems[i]).find('.form-check-input:checked');
            if (checkedBox.length > 0) {
                result.push($(checkedBox).prop('value'));
            }
        }

        return result;
    }

    /**
     * 校验通知策略是否有效
     */
    const checkNoticeStrategyIsValid = () => {
        let valid = true;
        let checkedTabItems = $('.nav-tab-radios').find('.nav-tabs-item.checked');

        $('.validate-notice-strategy-tip').empty();
        if (checkedTabItems.length === 0) { // 策略未配置
            $('.validate-notice-strategy-tip').html(LANG.UI_EMAIL_NOTICE_STRATEGY_CANNOT_EMPTY);
            $('.validate-notice-strategy-tip').removeClass('display-none');
            return false;
        } else {
            $('.validate-notice-strategy-tip').addClass('display-none');

            let checkedIcons = $('.nav-tab-radios').find('.nav-tabs-item.checked i').map(function() {
                return $(this).attr('id');
            }).get();

            const results = [];

            checkedIcons.forEach(item => {
                switch (item) {
                    case 'daily_report_i': // 校验日报
                        if (!$('#daily_report_time').val()) {
                            $('.validate-daily-time-tip').removeClass('display-none');
                            results.push(false);
                        } else {
                            $('.validate-daily-time-tip').addClass('display-none');
                            results.push(true);
                        }

                        break;
                    case 'weekly_report_i': // 校验周报
                        let checkedWeeklyBox = getCheckedboxGroupValues('weekly_checkbox_group');
                        let weeklyNoticeTime = $('#weekly_report_time').val();
                        let weeklyCheckBoxIsValid = checkedWeeklyBox.length > 0;
                        let weeklyNoticeTimeIsValid = !!weeklyNoticeTime;

                        $('.validate-weekly-checkbox-tip')[weeklyCheckBoxIsValid ? 'addClass' : 'removeClass']('display-none');
                        $('.validate-weekly-time-tip')[weeklyNoticeTimeIsValid ? 'addClass' : 'removeClass']('display-none');

                        results.push(weeklyCheckBoxIsValid && weeklyNoticeTimeIsValid);

                        break;
                    case 'monthly_report_i': // 校验月报
                        let checkedMonthlyBox = getCheckedboxGroupValues('monthly_checkbox_group');
                        let monthlyNoticeTime = $('#monthly_report_time').val();
                        let monthlyCheckBoxIsValid = checkedMonthlyBox.length > 0;
                        let monthlyNoticeTimeIsValid = !!monthlyNoticeTime;

                        $('.validate-monthly-checkbox-tip')[monthlyCheckBoxIsValid ? 'addClass' : 'removeClass']('display-none');
                        $('.validate-monthly-time-tip')[monthlyNoticeTimeIsValid ? 'addClass' : 'removeClass']('display-none');

                        results.push(monthlyCheckBoxIsValid && monthlyNoticeTimeIsValid);

                        break;
                    default:
                        break;
                }
            });

            // 只要有一个是 false，整体就无效
            valid = results.every(result => result === true);
        }

        if (!valid) {
            $('.validate-notice-strategy-tip').html(LANG.UI_EMAIL_NOTICE_STRATEGY_NOT_PERFECT);
            $('.validate-notice-strategy-tip').removeClass('display-none');
        }

        return valid;
    }

    /**
     * 自定义报表表单校验
     */
    const reportFormIsValid = () => {
        let templateName = $('#template_name').val();
        let customizedData = $("#customized_data_select").val();
        let emailNoticeFlag = $('#email_notice').get(0).checked;
        let templateNameValid = true; // 模版名称校验通过标记
        let customizedDataValid = true; // 定制数据多选框校验通过标记
        let noticeStrategyValid = true; // 邮件通知策略校验通过标记
        let emailsValidValid = true; // 邮箱地址校验通过标记

        if (!templateName) {
            $('.template-name-form-item').removeClass('has-success').addClass('has-error');
            $('.template-name-form-item .input-icon .fa').removeClass('fa-check').addClass('fa-warning');
            $('.validate-template-name-tip').removeClass('display-none');
            templateNameValid = false;
        } else {
            $('.template-name-form-item').removeClass('has-error').addClass('has-success');
            $('.template-name-form-item .input-icon .fa').removeClass('fa-warning').addClass('fa-check');
            $('.validate-template-name-tip').addClass('display-none');
            templateNameValid = true;
        }

        if (customizedData.length < 3) { // 校验定制数据是否小于3个
            $('.validate-customized-data-tip').removeClass('display-none');
            customizedDataValid = false;
        } else {
            $('.validate-customized-data-tip').addClass('display-none');
            customizedDataValid = true;
        }

        if (emailNoticeFlag) { // 开启了邮件通知再校验通知测量和邮箱地址
            noticeStrategyValid = checkNoticeStrategyIsValid();

            let emails = $('#emails').val();
            let result = illeagalEmailsCheck(emails);
            if (result.flag) {
                $('.email-address-form-item').removeClass('has-success').addClass('has-error');
                $('.email-address-form-item .input-icon .fa').removeClass('fa-check').addClass('fa-warning');
                $('.validate-emails-tip').removeClass('display-none');
                $('.validate-emails-tip').html(result.message);

                emailsValidValid = false;
            } else {
                $('.email-address-form-item').removeClass('has-error').addClass('has-success');
                $('.email-address-form-item .input-icon .fa').removeClass('fa-warning').addClass('fa-check');
                $('.validate-emails-tip').addClass('display-none');
                $('.validate-emails-tip').html('');

                emailsValidValid = true;
            }
        }

        return templateNameValid && customizedDataValid && noticeStrategyValid && emailsValidValid;
    }

    /**
     * 表单提交
     */
    const customReportSubmit = () => {
        let templateName = $('#template_name').val();
        let templateType = $('#template_type').val();;
        let overview = getCheckedboxGroupValues('overview_data_checkbox_group');
        let runningTendency = getCheckedboxGroupValues('running_tendency_checkbox_group');
        let customField = $("#customized_data_select").val();
        // 获取邮件通知策略
        let timeStrategy = [];
        let checkedIcons = $('.nav-tab-radios').find('.nav-tabs-item.checked i').map(function() {
            return $(this).attr('id');
        }).get();

        checkedIcons.forEach(item => {
            switch (item) {
                case 'daily_report_i': // 日报
                    timeStrategy.push({ type: 1, notice_time: $('#daily_report_time').val() });
                    break;
                case 'weekly_report_i': // 周报
                    timeStrategy.push({ type: 2, notice_time: $('#weekly_report_time').val(), days: getCheckedboxGroupValues('weekly_checkbox_group') });
                    break;
                case 'monthly_report_i': // 月报
                    timeStrategy.push({ type: 3, notice_time: $('#monthly_report_time').val(), days: getCheckedboxGroupValues('monthly_checkbox_group') });
                    break;
                case 'annual_report_i': // 年报
                    timeStrategy.push({ type: 4, notice_time: '' });
                    break;
                default:
                    break;
            }
        });

        let recEmail = $('#emails').val().split('\n');
        let remark = $('#description').val();

        let params = {
            templateName: templateName,
            templateType: templateType,
            overview: overview,
            runningTendency: runningTendency,
            customField: customField,
            timeStrategy: timeStrategy,
            recEmail: recEmail,
            remark: remark
        }

        if (ADD_CUSTOM_REPORT_FLAG) {
            Metronic.blockUI({target: '.custom-strategy-form',animate: true});

            pAjaxRequest(params, '/api/v1/report/template', 'post', function (res) {
                if (res.success) {
                    $('#custom_report_drawer').drawer('hide');
                    $('#report_table').bootstrapTable('refresh');
                    UIToastr.showSuccess(LANG.UI_GLOBAL_TEMPLATE_ADD, LANG.UI_GLOBAL_TEMPLATE_ADD_SUCCESS);
                } else {
                     UIToastr.showWarning(LANG.UI_GLOBAL_TEMPLATE_ADD, LANG.UI_GLOBAL_TEMPLATE_ADD_FAIL);
                }

                Metronic.unblockUI('.custom-strategy-form');
            });
        } else {
            params.templateUuid = CURRENT_REPORT_TEMPLATE.template_uuid;

            Metronic.blockUI({target: '.custom-strategy-form',animate: true});

             pAjaxRequest(params, '/api/v1/report/template', 'put', function (res) {
                if (res.success) {
                    $('#custom_report_drawer').drawer('hide');
                    $('#report_table').bootstrapTable('refresh');
                    UIToastr.showSuccess(LANG.UI_GLOBAL_TEMPLATE_EDIT, LANG.UI_GLOBAL_TEMPLATE_EDIT_SUCCESS);
                } else {
                    UIToastr.showWarning(LANG.UI_GLOBAL_TEMPLATE_EDIT, LANG.UI_GLOBAL_TEMPLATE_EDIT_FAIL);
                }

                Metronic.unblockUI('.custom-strategy-form');
            });
        }
    }

    // <-------------------------  END CUSTOM STRATEGY FORM  -------------------------------->
    
    // 初始化策略表格
    const initDataTable = () => {
        const showExport = CONF.PERMISSION_ARR.indexOf('p_storage_report_export') > -1; // 若未分配角色导出全选，则隐藏导出按钮
        const operateVisible = CONF.PERMISSION_ARR.indexOf('p_storage_report_edit') > -1 || CONF.PERMISSION_ARR.indexOf('p_storage_report_delete') > -1;

        let options = {
            vin_url: "/api/v1/report/template/list",
            vin_method: "GET",
            toolbarId: '#custom_report_toolbar',
            vin_toolbar: '.vin_toolbar',
            searchSelector: '.currentSearch',
            changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            detailView: true,
            sortName: 'create_time',
            sortOrder: 'desc',
            showExport: showExport,
            detailFormatter: current_detail,
            PostBody: function() {
                initCancle();

                if (CONF.PERMISSION_ARR.indexOf('p_storage_report_edit') === -1) {
                    $('.editStrategy').hide();
                } else {
                    $('.editStrategy').show();
                }

                if (CONF.PERMISSION_ARR.indexOf('p_storage_report_delete') === -1) {
                    $('.deleteStrategy').hide();
                } else {
                    $('.deleteStrategy').show();
                }

                let tableData = $('#report_table').bootstrapTable('getData');

                if (tableData.length === 0) {
                    $('#report_table').find('input[name=btSelectAll]').prop('disabled', true);
                    $('#delete').attr("disabled", true);
                } else {
                    $('#report_table').find('input[name=btSelectAll]').prop('disabled', false);
                    $('#delete').removeAttr("disabled");
                }
            },
            onCheck: function () {
                if ($('.btn.btn-toolbar-delete').hasClass('disabled')) {
                    $('#delete').removeAttr("disabled");
                    $('.btn.btn-toolbar-delete').removeClass('disabled');
                }
            },
            onUncheck: function () {
                selectedRows = $('#report_table').bootstrapTable('getSelections');
                if (selectedRows.length === 0) {
                    $('#delete').attr("disabled", true);
                    $('.btn.btn-toolbar-delete').addClass('disabled');
                }
            },
            onCheckAll: function () {
                if ($('.btn.btn-toolbar-delete').hasClass('disabled')) {
                    $('#delete').removeAttr("disabled");
                    $('.btn.btn-toolbar-delete').removeClass('disabled');
                }
            },
            onUncheckAll: function () {
                $('#delete').attr("disabled", true);
                $('.btn.btn-toolbar-delete').addClass('disabled');
            },
            resizable: true, //可变宽度
            customTool: {
                afterInput:'<button type = "button" id="add" class="dropdown-toggle btn-font btn-title btn table-toolbar-btn" style="margin-left: 12px" data-toggle="drawer">'+
                    '<i class="viconfont vicon-ge_add_task mr5 c0FBF98"></i>'+ LANG.UI_BACKUP_FILE_ADD +
                    '</button>'

            },
            columns: [
                {
                checkbox: true,
                sortable: false,
                forceHide: true,
                width: 1,
                widthUnit: '%'
                },
                {
                    field: 'template_name',
                    title: LANG.UI_SEARCH_TEMPLATE_NICKNAME,
                    sortable: false,
                    align: 'center',
                    events: operateEvents,
                    formatter: function (value,row) {
                        let type = row.template_type;
                        let nameStr = '';
                        switch (type) {
                            case 1:
                                nameStr = '<a class="vmclass" style="border: none">' + value + '</a>';
                                break;
                            case 2:
                                nameStr = '<a class="client" style="border: none">' + value + '</a>';
                                break;
                            case 3:
                                nameStr = '<a class="cdp" style="border: none">' + value + '</a>';
                                break;
                            case 4:
                                nameStr = '<a class="nas" style="border: none">' + value + '</a>';
                                break;
                            case 5:
                                nameStr = '<a class="storage" style="border: none">' + value + '</a>';
                                break;
                            case 6:
                                nameStr = '<a class="task" style="border: none">' + value + '</a>';
                                break;
                            case 7:
                                nameStr = '<a class="app" style="border: none">' + value + '</a>';
                                break;
                            case 8:
                                nameStr = '<a class="public" style="border: none">' + value + '</a>';
                                break;
                            case 9:
                                nameStr = '<a class="private" style="border: none">' + value + '</a>';
                                break;
                            case 10:
                                nameStr = '<a class="ob" style="border: none">' + value + '</a>';
                                break;
                            case 11:
                                nameStr = '<a class="hadoop" style="border: none">' + value + '</a>';
                                break;
                            case 12:
                                nameStr = '<a class="filecopy" style="border: none">' + value + '</a>';
                                break;
                            case 13:
                                nameStr = '<a class="k8s" style="border: none">' + value + '</a>';
                                break;
                            default:
                                break;
                        }
                        return nameStr
                    }
                },
                {
                    field: 'template_type',
                    title: LANG.UI_SEARCH_TYPE,
                    sortable: true,
                    align: 'center',
                    formatter: function (value) {
                        let strategyType;
                        switch (value) {
                            case 1:
                                strategyType = LANG.UI_REPORT_VM;
                                break;
                            case 2:
                                strategyType = LANG.UI_REPORT_AGENT;
                                break;
                            case 3:
                                strategyType = LANG.UI_ADD_TASK_TYPE_CDP_PROTECT;
                                break;
                            case 4:
                                strategyType = LANG.UI_REPORT_NAS;
                                break;
                            case 5:
                                strategyType = LANG.UI_VM_SETTING_STORAGE;
                                break;
                            case 6:
                                strategyType = LANG.UI_DATACENTER_JOB;
                                break;
                            case 7:
                                strategyType = LANG.UI_BACKUP_DATA_MODULE_M365;
                                break;
                            case 8:
                                strategyType = LANG. UI_PUBLIC_PUBLIC_CLOUD;
                                break;
                            case 9:
                                strategyType = LANG.UI_PUBLIC_PRIVATE_CLOUD;
                                break;
                            case 10:
                                strategyType = LANG.UI_PLATFORM_DES_OBS;
                                break;
                            case 11:
                                strategyType = LANG.UI_TENANT_HADOOP;
                                break;
                            case 12:
                                strategyType = LANG.UI_FILE_COPY_DES;
                                break;
                            case 13:
                                strategyType = LANG.UI_BACKUP_DATA_MODULE_K8S;
                                break;
                            default:
                                break;
                        }
                        return '<span title="' + strategyType + '">' + strategyType + '</span>';
                    }
                },
                {
                    field: 'create_time',
                    title: LANG.UI_PUBLIC_ADD_TIME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'user_name',
                    title: LANG.UI_NODE_POOL_TABLE_CREATOR,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'description',
                    title: LANG.UI_PUBLIC_DESCRIPTION,
                    sortable: false,
                    align: 'center',
                },

                {
                    field: 'strategy_actions',
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    align: 'center',
                    events: operateEvents,
                    // 生成修改和删除按钮
                    formatter: function () {
                        let button = '<div class="btn-group">';
                        button += '<div class="btn_operation_vicon">' +
                            '<a class="editStrategy" data-toggle="drawer"><i class="viconfont vicon-a-Editbianji"></i></a>' +
                            '<a class="deleteStrategy"><i class="viconfont vicon-a-Deleteshanchu1"></i></a>' +
                            '</div>';
                        button += '</div>';
                        return button;
                    },
                    visible: operateVisible
                },
            ],
        }

        $('#report_table').baseTableConfig().init(options);
    };

    /**
     * 过滤值为true的所有键，返回键的字符串数组
     * @param {*} obj 
     * @returns 
     */
    const getTrueKeys = (obj) => {
        return Object.keys(obj).filter(key => obj[key] === true);
    }

    //跳转事件
    const operateEvents = {
        'click .vmclass': function(e, value, row, index) {
            let url = './content/platform/reports/vmDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .client': function(e, value, row, index) {
            let url = './content/platform/reports/clientDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .nas': function(e, value, row, index) {
            let url = './content/platform/reports/nasDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .cdp': function(e, value, row, index) {
            let url = './content/platform/reports/cdpDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .storage': function(e, value, row, index) {
            let url = './content/platform/reports/storageDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .task': function(e, value, row, index) {
            let url = './content/platform/reports/taskDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .app': function(e, value, row, index) {
            let url = './content/platform/reports/appDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .public': function(e, value, row, index) {
            let url = './content/platform/reports/publicDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .private': function(e, value, row, index) {
            let url = './content/platform/reports/privateDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .ob': function(e, value, row, index) {
            let url = './content/platform/reports/obsDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .hadoop': function(e, value, row, index) {
            let url = './content/platform/reports/hadoopDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .filecopy': function(e, value, row, index) {
            let url = './content/platform/reports/filecopyDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .k8s': function(e, value, row, index) {
            let url = './content/platform/reports/k8sDetail.php?id=' + row.template_uuid;
            LOCATION(url,'storage_report');
        },
        'click .editStrategy': function (e, value, row, index) {
            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: '' }, () => {
                $('#title').html(LANG.UI_EDIT_CUSTOM_REPORT_TEMPLATE);
                ADD_CUSTOM_REPORT_FLAG = false;
                CURRENT_REPORT_TEMPLATE.template_uuid = row.template_uuid;
                CURRENT_REPORT_TEMPLATE.template_type = row.template_type;

                $('#template_name').val(row.template_name);
                $('.template-name-form-item').removeClass('has-error').removeClass('has-success');
                $('.template-name-form-item .input-icon .fa').removeClass('fa-warning').removeClass('fa-check');
                $('.validate-template-name-tip').addClass('display-none');

                $('#template_type').val(row.template_type);

                // 渲染模版类型对应的概览数据 check box
                initOverviewCheckboxGroup(TEMPLATE_TYPE_TO_CHECKBOXES_MAP[row.template_type]);

                let detail = JSON.parse(row.detail);
                let overviewData = getTrueKeys(detail.overview);
    0
                if (overviewData.length > 0) {
                    // 回显概览数据checkbox group
                    setCheckedItems(overviewData, TEMPLATE_TYPE_TO_CHECKBOXES_MAP[`${row.template_type}`]);
                }

                if (detail.running_tendency.history) {
                    // 回显历史任务运行checkbox
                    setCheckedItems(['history'], HISTORY_RUNNING_CHECKBOX_GROUP);
                }

                // 回显定制数据多选框
                let customFileds = getTrueKeys(detail.custom_field);
                CURRENT_REPORT_TEMPLATE.custom_field = customFileds;
                resetMultipleSelect(parseInt(row.template_type), TEMPLATE_TYPE_TO_MULTIPLE_SELECT_MAP[row.template_type]);

                // 回显邮件通知策略
                let reportConfig = JSON.parse(row.report_config);
                let receiveEmails = JSON.parse(row.receive_email);
                if (reportConfig.reportFlag) {
                    $('#email_notice').bootstrapSwitch('state', true);

                    reportConfig.timeStrategy.forEach(item => {
                        switch (item.type) {
                            case NOTICE_STRATEGY_TYPE.DAILY:
                                $('.nav-tab-radios').find('.nav-tabs-item.daily-report-tabs-item').addClass('checked');
                                $('#daily_report_i').removeClass('opacity-0');
                                $('#daily_report_notice').bootstrapSwitch('state', true);
                                $('#daily_report_time').val(item.notice_time);

                                break;
                            case NOTICE_STRATEGY_TYPE.WEEKLY:
                                $('.nav-tab-radios').find('.nav-tabs-item.weekly-report-tabs-item').addClass('checked');
                                $('#weekly_report_i').removeClass('opacity-0');
                                $('#weekly_report_notice').bootstrapSwitch('state', true);
                                $('#weekly_report_time').val(item.notice_time);

                                setCheckedItems(item.days, WEEKLY_CHECKBOX_GROUP);

                                break;
                            case NOTICE_STRATEGY_TYPE.MONTHLY:
                                $('.nav-tab-radios').find('.nav-tabs-item.monthly-report-tabs-item').addClass('checked');
                                $('#monthly_report_i').removeClass('opacity-0');
                                $('#monthly_report_notice').bootstrapSwitch('state', true);
                                $('#monthly_report_time').val(item.notice_time);

                                setCheckedItems(item.days, MONTHLY_CHECKBOX_GROUP);

                                break;
                            case NOTICE_STRATEGY_TYPE.ANNUAL:
                                $('.nav-tab-radios').find('.nav-tabs-item.annual-report-tabs-item').addClass('checked');
                                $('#annual_report_i').removeClass('opacity-0');
                                $('#annual_report_notice').bootstrapSwitch('state', true);

                                break;
                            default:
                                break;
                        }
                    });

                    // 回显邮箱地址
                    $('#emails').val(receiveEmails.join('\n'));
                } else {
                    $('#email_notice').bootstrapSwitch('state', false);
                    $('#emails').val('');
                }

                // 回显描述
                $('#description').val(row.description);

                $('#custom_report_drawer').drawer('show');
            }); 
        },
        //删除事件
        'click .deleteStrategy': function (e, value, row, index) {
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: '' }, () => {
                let params = row.template_uuid;
                let template_uuid = [];
                template_uuid.push(params); 

                //删除策略二次确认框
                bootbox.confirm({
                    title: LANG.UI_GLOBAL_REPORT_DELETE,
                    message: LANG.UI_GLOBAL_REPORT_DELETE_CONFIRM,
                    callback: function (r) {
                        if (!r) {
                            return;
                        }
                        //列表添加锁动画
                        Metronic.blockUI({ target: '.table-container', animate: true });
                        pAjaxRequest({template_uuid}, '/api/v1/report/template', 'delete', function (res) {
                            Metronic.unblockUI('.table-container');
                            if (res.success) {
                                $('#report_table').bootstrapTable('refresh');

                                // 重置删除按钮样式
                                $('#delete').attr("disabled", true);
                                $('.btn.btn-toolbar-delete').addClass('disabled');
                            }
                        });
                    }
                });
            });
        },
    };

    const initCancle = function() {
         $('#strategy .clear').off().on('click', function () {
             $('#strategy .customSearch').val('');
             queryParams.search = '';
             $('#strategy  .clear').removeClass('show');
             sessionStorage.removeItem("search");
             $('#strategy .search input').attr('placeholder',LANG.UI_BACKUP_EXPORT_ENTER_STRATEGY);
             $('#report_table').bootstrapTable('resetSearch');
         });
    }

    const current_detail = function (index, row, element) {
        let receive_email = JSON.parse(row.receive_email) || [];
        let report_config = JSON.parse(row.report_config) || [];
        report_config = report_config.timeStrategy
        if (!Array.isArray(receive_email)) {
            receive_email = []; // 设置为一个空数组以避免后续的错误
        }
        if (!Array.isArray(report_config)) {
            report_config = []; // 设置为一个空数组以避免后续的错误
        }
        let day = '';
        let week = '';
        let month = '';
        let year = '';
        let emailAddress = '';
        for(let i=0;i<receive_email.length;i++) {
            emailAddress += receive_email[i]+';'
        }
        if(!emailAddress) {
            emailAddress = '--'
        }
        let type1Exists = report_config.some(function(element) {
            return element.type === 1;
        });
        let type2Exists = report_config.some(function(element) {
            return element.type === 2;
        });
        let type3Exists = report_config.some(function(element) {
            return element.type === 3;
        });
        let type4Exists = report_config.some(function(element) {
            return element.type === 4;
        });
        let type1Index = report_config.findIndex(function(element) {
            return element.type === 1;
        });
        let type2Index = report_config.findIndex(function(element) {
            return element.type === 2;
        });
        let type3Index = report_config.findIndex(function(element) {
            return element.type === 3;
        });
        let type4Index = report_config.findIndex(function(element) {
            return element.type === 4;
        });
        if(type1Exists) {
            day = LANG.UI_REPORT_DAY_REPORT + report_config[type1Index].notice_time + LANG.UI_REPORT_SENT_REPORT
        }
        if(type2Exists) {
            const daysOfWeek = [LANG.UI_VISUAL_MONDAY, LANG.UI_VISUAL_TUESDAY, LANG.UI_VISUAL_WEDNESDAY, LANG.UI_VISUAL_THURSDAY, LANG.UI_VISUAL_FRIDAY, LANG.UI_VISUAL_SATURDAY, LANG.UI_VISUAL_SUNDAY];
            let a = report_config[type2Index].days;
            let selectedDays = [];

            a.forEach((day, index) => {
                if (day === 1) {
                    selectedDays.push(daysOfWeek[index]);
                }
            });

            let weekstr = ''
            for(let i=0;i<selectedDays.length;i++) {
               weekstr += selectedDays[i]+','
            }
            week = LANG.UI_REPORT_WEEK_REPORT + weekstr + report_config[type2Index].notice_time + LANG.UI_REPORT_SENT_WEEK_REPORT
        }
        if(type3Exists) {
            const daysOfWeek = [LANG.UI_REPORT_ONE, LANG.UI_REPORT_TWO, LANG.UI_REPORT_THREE, LANG.UI_REPORT_FOUR, LANG.UI_REPORT_FIVE, LANG.UI_REPORT_SIX, LANG.UI_REPORT_SEVEN,
                LANG.UI_REPORT_EIGHT, LANG.UI_REPORT_NINE, LANG.UI_REPORT_TEN, LANG.UI_REPORT_ELEVEN, LANG.UI_REPORT_TWVLEV, LANG.UI_REPORT_THIRTEEN, LANG.UI_REPORT_FOURTEEN,LANG.UI_REPORT_FIFTEEN,
                LANG.UI_REPORT_SIXTEEN, LANG.UI_REPORT_SEVENTEENM, LANG.UI_REPORT_EIGHTEEN, LANG.UI_REPORT_NINETEEN, LANG.UI_REPORT_TWENTY, LANG.UI_REPORT_TWENTY_ONE,LANG.UI_REPORT_TWENTY_TWO,
                LANG.UI_REPORT_TWENTY_THREE, LANG.UI_REPORT_TWENTY_FOUR, LANG.UI_REPORT_TWENTY_FIVE, LANG.UI_REPORT_TWENTY_SIX, LANG.UI_REPORT_TWENTY_SEVEN,LANG.UI_REPORT_TWENTY_EIGHT, LANG.UI_REPORT_TWENTY_NINE,
                LANG.UI_REPORT_THIRTY, LANG.UI_REPORT_THIRTEEN_ONE,];
            let a = report_config[type3Index].days;;
            let selectedDays = [];

            a.forEach((day, index) => {
                if (day === 1) {
                    selectedDays.push(daysOfWeek[index]);
                }
            });

            let monthStr = ''
            for(let i=0;i<selectedDays.length;i++) {
                monthStr += selectedDays[i]+','
            }
            month = LANG.UI_REPORT_MONTH_REPORT + monthStr + report_config[type3Index].notice_time + LANG.UI_REPORT_SENT_MONTH_REPORT
        }
        if(type4Exists && report_config[type4Index].notice_time) {
            year = LANG.UI_REPORT_YEAR_REPORT  + report_config[type4Index].notice_time + LANG.UI_REPORT_SENT_YEAR_REPORT
        }
        let html1 = `<div style="display: flex; align-items: flex-start; margin-bottom: 10px;margin-top: 12px"><b>`+ LANG.UI_REPORT_NOTICE+ `</b><div id="timeStrategy_info" style='display:inline-block;'>`;
        if(type1Exists){
            html1 += '<p style="margin-bottom: 5px;margin-top: 0px;color: rgba(126,130,153,0.8);";>' + day + '</p>';
        }
        if(type2Exists){
            html1 += '<p style="margin-bottom: 5px;margin-top: 0px;color: rgba(126,130,153,0.8);";>' + week + '</p>';
        }
        if(type3Exists){
            html1 += '<p style="margin-bottom: 5px;margin-top: 0px;color: rgba(126,130,153,0.8);";>' + month + '</p>';
        }
        if(type4Exists && report_config[type4Index].notice_time) {
            html1 += '<p style="margin-bottom: 5px;margin-top: 0px;color: rgba(126,130,153,0.8);";>' + year + '</p>';
        }
        if(!type1Exists && !type2Exists && !type3Exists && !type4Exists) {
            html1 += '<p style="margin-bottom: 5px;margin-top: 0px;color: rgba(126,130,153,0.8);";>' + '--' + '</p>';
        }
        html1 += `</div></div>`;
        $(element).append(html1);
        let html2 = `<div style="display: flex;margin-bottom: 12px; align-items: center"><b>`+ LANG.UI_REPORT_ADDRESS + `</b><div id="timeStrategy_info" style='display:inline-block'>`;
        html2 += '<p style="margin: 0px;color: rgba(126,130,153,0.8);">' + emailAddress + '</p>';
        html2 += `</div></div>`;
        $(element).append(html2);

    }

    /**
     * 批量删除自定义报表模版
     * @param {*} grid 
     * @returns 
     */
    const deleteStrategyBatchSubmit = function (grid) {
        if ($('#delete').is(':disabled') || $('.btn.btn-toolbar-delete').hasClass('disabled')) {
            return;
        }

        let template_uuid = getIdSelectedId('#report_table');

        let userUuids = $.map($('#report_table').bootstrapTable('getSelections'), function (row) {
            return row.user_uuid;
        });
        
        //策略必选
        if (template_uuid.length == 0) {
            return UIToastr.showInfo(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_TIPS);
        }

        checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: ''}, () => {
            bootbox.confirm({
                title: LANG.UI_GLOBAL_REPORT_DELETE,
                message: LANG.UI_REPORT_TEMPLATE_DELETE_CONFIRM,
                callback: function (r) {
                    if (!r) {
                        return;
                    }
                    Metronic.blockUI({target: '.table-container',animate: true});
                    pAjaxRequest({template_uuid}, '/api/v1/report/template', 'delete', function (res) {
                        Metronic.unblockUI('.table-container');
                        if (res.success) {
                            UIToastr.showSuccess(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_SUCCESS);
                            $('#report_table').bootstrapTable('refresh');

                            // 重置删除按钮样式
                            $('#delete').attr("disabled", true);
                            $('.btn.btn-toolbar-delete').addClass('disabled');
                        } else {
                            UIToastr.showWarning(LANG.UI_GLOBAL_TEMPLATE_DELETE, LANG.UI_GLOBAL_TEMPLATE_DELETE_FAIL);
                        }
                    });
                }
            });
        });
    }

    function getIdSelectedId(select)
    {
        return $.map($(select).bootstrapTable('getSelections'), function (row) {
            return row.template_uuid;
        })
    }

    const initListeners = () => {
        // <---------------------------- BEGIN CUSTOM REPORT TABLE LISTENERS ---------------------------->

        $('#strategy .search-btn').unbind("click").on('click', function (options) {
            queryParams = {}
            queryParams.search = $('#strategy .customSearch').val();
            queryParams.offset = 0
            $('#report_table').bootstrapTable('refresh', {query: queryParams});
        });

        $('#resetStartTime').on('click', function() {
            $('#yearTimeInput').val(''); // 清空输入框
        });

        $('#delete').on('click', deleteStrategyBatchSubmit);

        //修改确认
        $('#modify_close').unbind('click').on('click', function () {
            $('#report_table').bootstrapTable('uncheckAll');
        });

        // <---------------------------- END CUSTOM REPORT TABLE LISTENERS ---------------------------->


        // <---------------------------- BEGIN CUSTOM REPORT FORM LISTENERS ---------------------------->

        $('#template_type').on('change', handleTemplateTypeChange);

        // 全选
        $('#select_all').on('click', () => {
            let formCheckInputs = $('#overview_data_checkbox_group .checkbox-wrapper__item').children('.form-check-input');

            for (let i = 0; i < formCheckInputs.length; i++) {
                if (!$(formCheckInputs[i]).prop('checked')) {
                    $(formCheckInputs[i]).prop('checked', true);
                }
            }
        });

        // 清除
        $('#clean_all').on('click', () => {
            cancelAllCheckboxGroup('overview_data_checkbox_group');
        });

        $('#email_notice').on('switchChange.bootstrapSwitch', handleEmailNoticeChange);

        $('#daily_report_notice').on('switchChange.bootstrapSwitch', handleDailyReportNoticeChange);

        $('#weekly_report_notice').on('switchChange.bootstrapSwitch', handleWeeklyReportNoticeChange);

        $('#monthly_report_notice').on('switchChange.bootstrapSwitch', handleMonthlyReportNoticeChange);

        $('#annual_report_notice').on('switchChange.bootstrapSwitch', handleAnnualReportNoticeChange);

        $('#daily_report_time').timepicker({
            showMeridian: false,
            minuteStep: 1, // 每分钟增加一次
            showSeconds: true, // 显示秒
            secondStep: 1
        });

        $('#weekly_report_time').timepicker({
            showMeridian: false,
            minuteStep: 1, // 每分钟增加一次
            showSeconds: true, // 显示秒
            secondStep: 1
        });

        $('#monthly_report_time').timepicker({
            showMeridian: false,
            minuteStep: 1, // 每分钟增加一次
            showSeconds: true, // 显示秒
            secondStep: 1
        });

        $('#custom_report_submit').on('click', () => {
            if (reportFormIsValid()) {
                customReportSubmit();
            }
        });

        // <---------------------------- END CUSTOM REPORT FORM LISTENERS ---------------------------->

        $('#openReportDrawer').unbind('click').on('click', initAddReportDwawer);

        // 搜索模版名称
        $('#report_search').off().on('click', () => {
            let searchVal = $('#search').val();

            if (searchVal) {
                $('#report_table').bootstrapTable('refresh', {query: {search: searchVal}})
            }
        });

        // 清空搜索
        $('#report_clear_search').on('click', () => {
            $('#search').val('');
            $('#report_clear_search').addClass('hide');
            $('#report_table').bootstrapTable('refresh', {query: {search: ''}})
            $('#report_table').bootstrapTable('resetSearch');
        });
        

        // custom_report_tip 关闭时动态设置表格高度
        $('#custom_report_tip_close').on('click', () => {
            $('.custom-report-wrapper .custom-report-wrapper__content .table-container').css('height', 'calc(100% - 46px)');
        });
    }

    return{
        init: function () {
            initDataTable();
            initListeners();
            initOverviewCheckboxGroup(VM_REPORT_CHECKBOXES);
            initRunningTendencyCheckbox();
            initCustomizedDataSelect(VM_REPORT_MULTIPLE_OPTIONS);
            initWeeklyCheckboxGroup();
            initMonthlyCheckboxGroup();
        }
    };
}();
jQuery(document).ready(function () {
    Strategy.init();
});