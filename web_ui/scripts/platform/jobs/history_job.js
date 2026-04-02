var HistoryJob = function () {
    var fsnodeuuid = ''; //用于下载跳过文件
    var changeHeightFlag = false;
    var checkIndex;
    var accurateFlag = false;
    var queryParams = {};
    var _reportContent, _selectHistory;
    let ADVANCED_SEARCH_PARAMS = {}; // 高级搜索查询参数
    const ADVANCED_SEARCH_PARAM_TO_DES_MAP = {
        'job_name': LANG.UI_REPORT_TASKNAME,
        'user_name': LANG.UI_CLOUD_PLATFORM_USERNAME,
        'other_host_name': LANG.UI_BACKUP_FILE_HOSTNAME,
        'other_vm_name': LANG.UI_JOB_VM_NAME,
        'vm_type': LANG.UI_VM_SETTING_V2_HYPER_TYPE,
        'db_type': LANG.UI_COPY_SOURCE_DB_TYPE,
        'node_uuid': LANG.UI_PUBLIC_STORAGE_IN_NODE,
        'storage_uuid': LANG.UI_BACKUP_FILE_STORAGE,
        'module_type': LANG.UI_SEARCH_MODE_TYPE,
        'sub_module_type': LANG.UI_BACKUP_DATA_TABLE_LABEL_SUB_MODULE_TYPE,
        'job_type': LANG.UI_PUBLIC_TASK_TYPE
    };
    const HISTORY_JOB_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_BACKUP,
            field: 'timing_data_protect',
            value: [
                {
                    id: 'history-vmprotect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.VM,
                    text: LANG.UI_BACKUP_DATA_MODULE_VM,
                },
                {
                    id: 'history-prcloud_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PRIVATE_CLOUD,
                    text: LANG.UI_PUBLIC_PRIVATE_CLOUD,
                },
                {
                    id: 'history-awsprotect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PUBLIC_CLOUD,
                    text: LANG.UI_PUBLIC_PUBLIC_CLOUD,
                },
                {
                    id: 'history-complete_machine',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'history-osbackup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
                {
                    id: 'history-filebackup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.FILE,
                    text: LANG.UI_FILE_FILE,
                },
                {
                    id: 'history-nas_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.NAS,
                    text: LANG.UI_REPORT_NAS,
                },
                {
                    id: 'history-hadoop_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.HADOOP,
                    text: LANG.UI_PLATFORM_DES_HADOOP,
                },
                {
                    id: 'history-obs_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.OBS,
                    text: LANG.UI_VISUAL_OBS,
                },
                {
                    id: 'history-office365_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.M365,
                    text: LANG.UI_BACKUP_DATA_MODULE_M365,
                },
                {
                    id: 'history-k8s_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.KUBERNETES,
                    text: LANG.UI_BACKUP_DATA_MODULE_K8S,
                },
                {
                    id: 'history-db_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DB,
                    text: LANG.UI_AGENT_MODULE_DB,
                },
            ]
        },
        {
            label: LANG.UI_REPORY_CDP,
            field: 'real_time_data_protect',
            value: [
                {
                    id: 'history-complete_cdp_backup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'history-vol_cdp_backup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
            ]
        },
        {
            label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_REPLICATION,
            field: 'data_copy',
            value: [
                {
                    id: 'history-machine_copy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'history-vol_cdp_copy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
                {
                    id: 'history-dbcdpcopy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_DB,
                    text: LANG.UI_AGENT_MODULE_DB,
                },
                {
                    id: 'history-file_copy_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_FILE,
                    text: LANG.UI_FILE_FILE,
                },
            ]
        },
        {
            label: LANG.UI_PUBLIC_TASK_TYPE,
            field: 'task_type',
            value: [
                {
                    id: 'history_task_type_backup',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.BACKUP,
                    text: LANG.UI_VISUAL_BACKUP,
                },
                {
                    id: 'history_task_type_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.RECOVER,
                    text: LANG.UI_VISUAL_RECOVERY,
                },
                {
                    id: 'history_task_type_copy',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.COPY,
                    text: LANG.UI_VISUAL_COPY,
                },
                {
                    id: 'history_task_type_archive',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.ARCHIVE,
                    text: LANG.UI_VISUAL_ARCHIVE,
                },
                {
                    id: 'history_task_type_takeover_verify',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.TAKEOVER,
                    text: LANG.UI_VOL_CDP_TAKEOVER_DESC,
                },
                {
                    id: 'history_task_type_motion',
                    value: '54',
                    text: LANG.UI_MOTION_NAME,
                },
                {
                    id: 'history_task_type_instance_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.INSTANT_RECOVER,
                    text: LANG.UI_VISUAL_INSTANT_NAME,
                },
                {
                    id: 'history_task_type_grain_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.GRAIN_RECOVER,
                    text: LANG.UI_RECOVERY_GRAIN,
                },
                {
                    id: 'history_task_type_cross_platform_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.CROSS_PLATFORM_RECOVER,
                    text: LANG.UI_FILE_CROSS_RESTORE,
                },
                {
                    id: 'history_task_type_data_verify',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.DATA_VERIFY,
                    text: LANG.UI_JOB_DATA_VERTIFY,
                },
                {
                    id: 'history_task_type_data_copy',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.DATA_COPY,
                    text: LANG.UI_CM_CDP_REPLICATION,
                },
                {
                    id: 'history_task_type_file_compare',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.COMPARE,
                    text: LANG.UI_FILE_COPY_TASK_TYPE_COMPARE
                },
                {
                    id: 'history_task_type_db_drill',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.DB_DRILL,
                    text: LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL,
                },
                {
                    id: 'history_task_type_copy_fetch',
                    value: CONF.TASK_TYPE.BACKUP_COPY_FETCH,
                    text: LANG.UI_COPY_FETCH_MODE
                },
                {
                    id: 'history_task_type_archive_fetch',
                    value: CONF.TASK_TYPE.ARCHIVE_FETCH,
                    text: LANG.UI_COPY_ARCHIVE_FETCH_MODE
                }
            ]
        },
        {
            label: LANG.UI_VISUAL_RESULT,
            field: 'task_status',
            value: [
                {
                    id: 'history_task_status_success',
                    value: 1,
                    text: LANG.UI_PUBLIC_SUCCESS,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'history_task_status_suspend',
                    value: 2,
                    text: LANG.UI_VISUAL_SUSPEND,
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'history_task_status_abnormal',
                    value: 3,
                    text: LANG.UI_NODE_ABNORMAL,
                    tag: true,
                    type: 'warning'
                },
                {
                    id: 'history_task_status_error',
                    value: 4,
                    text: LANG.UI_VISUAL_FAIL,
                    tag: true,
                    type: 'danger'
                }
            ]
        }
    ]; // 历史任务表格过滤器数组
    let FILTER_PARAMS = {}; // 过滤搜索参数
    let startTime = '', endTime = '', searchVal = '';

    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#history_table>tbody>tr>td').css({
                'padding-top': '15.75px',
                'padding-bottom': '15.75px'
            })
            $('vin_history_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#history_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('vin_history_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }

    //获取参数
    var getParams = function (params) {
        // 取出 sessionStorage中的参数
        let sessionStorageParams = JSON.parse(sessionStorage.getItem('history_job_filter_params')) || {};

        queryParams.search = $('.historySearch').val();

        // 合并sessionStorage中的参数
        queryParams = Object.assign(queryParams, sessionStorageParams.filterData || {});

        // 合并过滤器搜索参数
        queryParams = Object.assign(queryParams, FILTER_PARAMS);

        // 合并高级搜索参数
        queryParams = Object.assign(queryParams, ADVANCED_SEARCH_PARAMS);

        if (CONF.VENDOR === CONF.VENDOR_LIST.gmp) {
            // gmp的不显示数据验证任务类型
            queryParams.no_surebackup_flag = true;
        }
        return queryParams;
    }

    //初始化虚拟化类型
    const initVMType = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/vm/platforms/hypervisors', 'GET', function (d) {
            var data = d;
            var vmSelect = $('#history_advanced_search_vm_type');
            vmSelect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
            vmSelect.append(option);
            for (var i = 0; i < data.data.hypervisors.length; i++) {
                option = $("<option>").text(data.data.hypervisors[i].text).val(data.data.hypervisors[i].value);
                vmSelect.append(option);
            }
            vmSelect.val('0');
        });
    }

    var tipDelete = function () {
        UIToastr.showInfo(LANG.UI_JOB_DELETE_HISTORY_JOB, LANG.UI_JOB_SELECT_HISTORY_TIPS);
    }

    //删除历史任务
    var deleteHistoryJOB = function () {
        var selectRows = $('#history_table').bootstrapTable('getSelections');
        if (!selectRows.length) {
            return tipDelete();
        }
        bootbox.confirm({
            title: LANG.UI_JOB_DELETE_HISTORY_JOB,
            message: LANG.UI_JOB_DELETE_HISTORY_JOB_CONFIRM_TIPS,
            callback: function (r) {
                if (!r) return;
                var params = [];
                $.each(selectRows, function (index) {
                    params.push(selectRows[index].job_uuid);
                })
                Metronic.blockUI({
                    target: '#history_table',
                    animate: true
                });
                pAjaxRequest({
                    "job_uuids": params,
                }, '/api/v1/jobs/history', 'DELETE', function (data) {
                    Metronic.unblockUI('#history_table');
                    var op = LANG.UI_JOB_SEND_BATCH_DELETE_TASK_MESSAGE;
                    if (!data.success && data.code == 200) {
                        // web提示 被日志安全限制住了
                        return UIToastr.showInfo(data.title, data.message);
                    }
                    if (operateResponseList(data, op)) {
                        $("#history_table").bootstrapTable('refresh');
                        $("#history_table").bootstrapTable('hideLoading');
                    }
                });
            }
        });
    }

    // 下载任务日志逻辑
    var downloadJobLog = function (params) {
        var uuid = $('#history_table').bootstrapTable('getSelections');
        if (uuid.length > 1) {
            return UIToastr.showInfo(LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG, LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG_SELECT_ONE_TIPS);
        } else if (uuid.length == 1) {
            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 history_job
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: uuid[0].user_uuid, auth: 'history_job' }, () => {
                pAjaxRequest({}, '/api/v1/jobs/log/' + uuid[0].job_uuid + '', 'GET', function (res) {
                    if (res.success) {
                        window.location.href = '/api/v1/jobs/log_down/' + res.data.info + '?x-api-version=1.0-rev0';
                    } else if (!operateResponseList(res)) {
                        return false;
                    }
                });
            });
        } else {
            return UIToastr.showInfo(LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG, LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG_NO_SELECT_TIPS);
        }
    }

    // 历史任务展开详情
    let lastIndex = [-1, -1];

    /**
     * 展开历史任务详情
     * @param {*} index
     * @param {*} row
     * @param {*} element
     */
    const history_detail = function (index, row, element) {
        if (index != lastIndex[1]) {
            lastIndex.push(index);
            $('#history_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
            lastIndex.splice(0, 1);
        }
        Metronic.blockUI({
            target: element,
            animate: true
        });

        let html = '<table>';

        pAjaxRequest({}, '/api/v1/jobs/history/' + row.job_uuid + '', 'GET', function (res) {
            let data = res.data;
            Metronic.unblockUI(element);

            if (!res.success) {
                $(element).append(LANG.UI_PUBLIC_NOTHING);
                return;
            }

            let job_type = data.info.job_type_value;

            if (data.list.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
                html += `
                    <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>`;

                let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                html +=
                `<tr>
                    <td>` + LANG.UI_PUBLIC_ON + `</td>
                    <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                    <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                </tr>`;
            } else { // 未开启则显示各自模块的内容详情
                // 副本、副本回传、归档、归档回传
                if (data.info.job_type == 17 || data.info.job_type == 18 || data.info.job_type == 19 || data.info.job_type == 20) {
                    switch (data.info.module_type) {
                        case CONF.MODULE_TYPE.VM:
                            if (data.info.sub_module_type_value == 3) {
                                html += `<th>` + LANG.UI_TASK_AWS_INSTANCE_NAME + `</th>`;
                            } else {
                                html += `<th>` + LANG.UI_JOB_VM_NAME + `</th>`;
                            }
                            break;
                        case CONF.MODULE_TYPE.FS:
                            if (data.info.sub_module_type_value == CONF.SUBMODULE_TYPE.FS) {
                                html += `<th>` + LANG.UI_JOB_HIS_SRC_HOST_NAME + `</th>`;
                            } else if (data.info.sub_module_type_value == CONF.SUBMODULE_TYPE.HADOOP) {
                                html += `<th>` + LANG.UI_HADOOP_CLUSTER_LIST_NAME + `</th>`;

                            } else if (data.info.sub_module_type_value == CONF.SUBMODULE_TYPE.OBS) {
                                html += `<th>` + LANG.UI_PLATFORM_DES_OBS_NAME + `</th>`;

                            }
                            break;
                        case CONF.MODULE_TYPE.NAS:
                        case CONF.MODULE_TYPE.OS:
                            html += `<th>` + LANG.UI_JOB_HIS_SRC_HOST_NAME + `</th>`;
                            break;
                        case CONF.MODULE_TYPE.DB:
                            html += `<th>` + LANG.UI_COPY_DETAIL_DB_NAME + `</th>`;
                            break;
                        case CONF.MODULE_TYPE.M365:
                            html += `<th>` + LANG.UI_MICROSOFT365_ORGANIZATION_NAME + `</th>`;
                            break;
                        case CONF.MODULE_TYPE.KUBERNETES:
                            html += `<th>` + LANG.UI_DB_CLUSTER + `</th>`;
                            break;
                    }
                    html += `
                        <th>` + LANG.UI_COPY_DETAILS_TIMEPOINT_NUM + `</th>
                        <th>` + LANG.UI_PUBLIC_START_TIME + `</th>
                        <th>` + LANG.UI_PUBLIC_END_TIME + `</th>
                        <th>` + LANG.UI_DB_AVE_SPEED + `</th>
                        <th>` + LANG.UI_JOB_HIS_TOTAL_SIZE + `</th>
                        <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                        <th>` + LANG.UI_PUBLIC_REAL_SIZE + `</th>
                        <th>` + LANG.UI_VISUAL_RESULT + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`

                    for (let i = 0; i < data.list.length; i++) {
                        html += `
                            <tr>
                                <td>` + data.list[i].item_name + `</td>
                                <td>` + data.list[i].timepoint_count + `</td>
                                <td>` + data.list[i].start_transfer_time + `</td>
                                <td>` + data.list[i].end_transfer_time + `</td>
                                <td>` + data.list[i].transfer_speed + `</td>
                                <td>` + data.list[i].total_size + `</td>
                                <td>` + data.list[i].transport_size + `</td>
                                <td>` + data.list[i].write_size + `</td>
                                <td>` + data.list[i].item_status + `</td>
                                <td>` + data.list[i].error_code + `</td>
                            </tr>
                        `
                    }
                } else {
                    switch (data.info.module_type) {
                        case 2: //虚拟机
                        case 17: // 公有云
                            if (undefined !== data.list.vms_details) {
                                data.list = data.list.vms_details;
                            }
                            var lang_vm_name = LANG.UI_JOB_VM_NAME;
                            var lang_vm_size = LANG.UI_PUBLIC_VM_TOTAL_SIZE;
                            if (17 == data.info.module_type) {
                                lang_vm_name = LANG.UI_TASK_AWS_INSTANCE_NAME;
                                lang_vm_size = LANG.UI_TASK_AWS_INSTANCE_SIZE;
                            }
                            if (3 == data.info.sub_module_type_value) {
                                lang_vm_name = LANG.UI_TASK_AWS_INSTANCE_NAME;
                                lang_vm_size = LANG.UI_TASK_AWS_INSTANCE_SIZE;
                            }
                            if (data.info.job_type == 1) { //备份
                                html += `
                                    <th>` + lang_vm_name + `</th>
                                    <th>` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                                    <th>` + LANG.UI_PUBLIC_START_TIME + `</th>
                                    <th>` + LANG.UI_PUBLIC_END_TIME + `</th>
                                    <th>` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                                    <th>` + lang_vm_size + `</th>
                                    <th>` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                                    <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                                    <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                                    <th>` + LANG.UI_VISUAL_RESULT + `</th>
                                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;
                                for (let i = 0; i < data.list.length; i++) {
                                    html += `                                <tr>
                                        <td>` + data.list[i].vm_name + `</td>
                                        <td>` + job_type + `</td>
                                        <td>` + data.list[i].start_transfer_time + `</td>
                                        <td>` + data.list[i].end_transfer_time + `</td>
                                        <td>` + data.list[i].transfer_speed + `</td>
                                        <td>` + data.list[i].vm_size + `</td>
                                        <td>` + data.list[i].vm_valid_size + `</td>
                                        <td>` + data.list[i].transport_size + `</td>
                                        <td>` + data.list[i].real_size + `</td>
                                        <td>` + data.list[i].task_status + `</td>
                                        <td>` + data.list[i].error_code + `</td>
                                    </tr>`
                                }
                            }
                            if (data.info.job_type == 2 || data.info.job_type == CONF.TASK_TYPE.PLATFORM_RECOVERY) { //恢复(跨平台)
                                if (undefined !== data.list[0].disk_list && data.list[0].disk_list.length) {
                                    lang_vm_size = LANG.UI_TASK_AWS_VOL_SIZE;
                                }
                                html += `
                                    <th>` + LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT + `</th>
                                    <th>` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                                    <th width="20%">` + LANG.UI_JOB_HIS_SRC_PATH + `</th>
                                    <th width="20%">` + LANG.UI_JOB_HIS_DES_PATH + `</th>
                                    <th>` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                                    <th>` + lang_vm_size + `</th>
                                    <th>` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                                    <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                                    <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                                    <th>` + LANG.UI_VISUAL_RESULT + `</th>
                                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                                    `
                                for (let i = 0; i < data.list.length; i++) {
                                    if (undefined !== data.list[i].disk_list && data.list[i].disk_list.length) {
                                        // 卷恢复
                                        $.each(data.list[i].disk_list, function (k, v) {
                                            html += `    <tr>
                                            <td>` + data.list[i].timepoint + `</td>
                                            <td>` + job_type + `</td>
                                            <td>` + (data.list[i].dir_path || '--') + `</td>
                                            <td>` + v.disk_dir_path + `</td>
                                            <td>` + data.list[i].transfer_speed + `</td>
                                            <td>` + v.total_size + `</td>
                                            <td>` + v.avalid_size + `</td>
                                            <td>` + v.transfer_size + `</td>
                                            <td>` + v.write_size + `</td>
                                            <td>` + data.list[i].task_status + `</td>
                                            <td>` + data.list[i].error_code + `</td>
                                            </tr>`
                                        })
                                    } else {
                                        // 实例恢复
                                        html += `    <tr>
                                        <td>` + data.list[i].timepoint + `</td>
                                        <td>` + job_type + `</td>
                                        <td>` + (data.list[i].dir_path || '--') + `</td>
                                        <td>` + data.list[i].vcenter_ip + '/' + data.list[i].host_ip + '/' + data.list[i].new_name + `</td>
                                        <td>` + data.list[i].transfer_speed + `</td>
                                        <td>` + data.list[i].vm_size + `</td>
                                        <td>` + data.list[i].vm_valid_size + `</td>
                                        <td>` + data.list[i].transport_size + `</td>
                                        <td>` + data.list[i].real_size + `</td>
                                        <td>` + data.list[i].task_status + `</td>
                                        <td>` + data.list[i].error_code + `</td>
                                        </tr>`
                                    }
                                }
                            }
                            if (data.info.job_type == CONF.TASK_TYPE.INSTANT_RECOVERY_MOTION) { // 虚拟机迁移
                                html += `
                                <th>` + LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT + `</th>
                                <th>` + LANG.UI_JOB_HIS_SRC_PATH + `</th>
                                <th>` + LANG.UI_JOB_MOTION_PATH + `</th>
                                <th>` + LANG.UI_PUBLIC_VM_TOTAL_SIZE + `</th>
                                <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                                <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                                <th>` + LANG.UI_VISUAL_RESULT + `</th>
                                <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                                    <tr>
                                        <td>` + data.list[0].timepoint + `</td>
                                        <td>` + (data.list[0].dir_path || '--') + `</td>
                                        <td style="word-break:break-word;">` + data.list[0].vcenter_ip + '/' + data.list[0].host_ip + '/' + data.list[0].new_name + `</td>
                                        <td>` + data.list[0].vm_size + `</td>
                                        <td>` + data.list[0].transport_size + `</td>
                                        <td>` + storageCalculateSize(parseInt(data.list[0].write_size)) + `</td>
                                        <td>` + data.list[0].task_status + `</td>
                                        <td>` + data.list[0].error_code + `</td>
                                    </tr>
                                `
                            }
                            if (data.info.job_type == CONF.TASK_TYPE.VM_HUAWEI_CBR_SYNC) { //下云同步
                                var syncType = data.list[0].synctype;
                                var syncpath = '';
                                if (syncType == 1) {
                                    syncpath = LANG.UI_SYNC_CBR_STORAGE_PATH
                                } else if (syncType == 2) {
                                    syncpath = LANG.UI_SYNC_CBR_RESOURCE_PATH
                                } else if (syncType == 3) {
                                    syncpath = LANG.UI_SYNC_CBR_TIMEPOINT_PATH
                                }
                                html += `
                                <th>` + syncpath + `</th>
                                <th>` + LANG.UI_SYNC_CBR_TIMEPOINT_NUM + `</th>
                                <th>` + LANG.UI_SYNC_CBR_TIMEPOINT_LIST + `</th>`;
                                for (var i = 0; i < data.list.length; i++) {
                                    var timepoints = "";
                                    if (data.list[i].count == 0 && data.list[i].backups == null) {
                                        data.list[i].count = "--";
                                        timepoints = "--";
                                    } else {
                                        var backuplist = [];
                                        for (let j = 0; j < data.list[i].backups.length; j++) {
                                            var backupname = data.list[i].backups[j].backup_name;
                                            backuplist.push(backupname);
                                        }
                                        timepoints ='<textarea disabled>' + backuplist.join('\n') + '</textarea>' ;

                                    }
                                    html += `<tr>`;
                                    html += `<td>` + data.list[i].path + `</td>`;
                                    html += `<td>` + data.list[i].count + `</td>`;
                                    html += `<td>` + timepoints + `</td>`;
                                    html += `</tr>`;
                                }
                            }

                            if (data.info.job_type == CONF.TASK_TYPE.INSTANT_RECOVERY) { // 瞬时恢复
                                html += `
                                <th>` + LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT + `</th>
                                <th>` + LANG.UI_SRC_OBJECT_NAME + `</th>
                                <th>` + LANG.UI_INSTANT_RECOVER_OBJECT_NAME + `</th>
                                <th>` + LANG.UI_JOB_HIS_SRC_PATH + `</th>
                                <th>` + LANG.UI_VISUAL_RESULT + `</th>
                                <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                                    <tr>
                                        <td>` + data.list[0].timepoint + `</td>
                                        <td>` + (data.list[0].vm_name || '--') + `</td>
                                        <td>` + (data.list[0].new_name || '--') + `</td>
                                        <td>` + data.list[0].task_status + `</td>
                                        <td>` + data.list[0].error_code + `</td>
                                    </tr>
                                `
                            }
                            break;
                        case 3: //文件
                            if (data.info.job_type == 1) { //备份
                                if(data.info.sub_module_type_value == 3){
                                    //hadoop备份
                                    var thead = '';
                                    thead += '<th>' + LANG.UI_HADOOP_HADOOP_CLUSTER_NAME + '</th>';
                                    thead += '<th>' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_WILDCARD + '</th>';
                                    //判断是否显示归档
                                    var contentTh = '';
                                    var contentTb = '';
                                    if(Object.keys($('#archiveflag')).length != 0) {
                                        contentTh = '<th>'+LANG.UI_ARCHIVE+'</th>';
                                        contentTb = '<td>' + getFlagInfo(data.list.file_archive_flag == undefined ? 2 : data.list.file_archive_flag) + '</td>';
                                    }
                                    thead += contentTh;
                                    thead += '<th>' + LANG.UI_JOB_HIS_BAK_FILE_TOTAL + '</th>';
                                    thead += '<th>' + LANG.UI_JOB_HIS_BAK_FILE_LIST + '</th>';
                                    thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                    thead += '<th> ' + LANG.UI_FILE_COUNT_PASS + ' </th>';
                                    thead += '<th> ' + LANG.UI_FILE_COUNT_PASS_DIR+ ' </th>';
                                    thead += '<th> ' + LANG.UI_FILE_PASSFILE_LISTS + ' </th>';
                                    var tbody = '';
                                    if(data.list.list == undefined) {
                                        tbody += "";
                                    }else {
                                        data.list.list.forEach(item=> {
                                            //通配符
                                            if(item.wildcard_list == undefined || item.wildcard_list == "") {
                                                wildcard = LANG.UI_PUBLIC_NOTHING;
                                                wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                            } else {
                                                var wildcard_list = JSON.parse(item.wildcard_list);
                                                var wildcard_mode,wildcard = [];
                                                if(wildcard_list.wildcard_mode != null && wildcard_list.wildcard_mode != 0) {
                                                    wildcard = wildcard_list.wildcard.join('<br>');
                                                    wildcard_list.wildcard_mode==1?wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER:wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
                                                }else {
                                                    wildcard = LANG.UI_PUBLIC_NOTHING;
                                                    wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                                }
                                            }
                                            //跳过目录个数
                                            var passdir = item.total_pass_dir_number == undefined ? 0 : item.total_pass_dir_number;
                                            tbody += '<tr>';
                                            tbody += '<td>' + item.src_agent_name + '(' + item.src_agent_ip + ')' +  '</td>';
                                            tbody += '<td>' + item.backup_mode + '</td>';
                                            tbody += '<td>' + wildcard_mode + '</td>';
                                            tbody += '<td style="width: 8%;">' + wildcard + '</td>';
                                            tbody += contentTb;
                                            tbody += '<td>' + item.file_count + '</td>';

                                            tbody += '<td style="padding-right: 30px"><div class="historyfilelisttext">';
                                            var list = '';
                                            if(!item.file_list){
                                                return LANG.UI_PUBLIC_NOTHING;
                                            }
                                            for(var i=0; i<item.file_list.length; i++){
                                                list += item.file_list[i] + "<br>";
                                            }
                                            tbody += list;
                                            tbody += '</div></td>';
                                            tbody += '<td style="width: 8%;">' + item.description + '</td>';
                                            tbody += '<td>' + item.total_pass_number + '</td>';
                                            tbody += '<td>' + passdir + '</td>';
                                            var elecontent = '',style = '';
                                            if(item.passfile_exist_flag==1) {
                                                elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                            }else {
                                                elecontent = LANG.UI_PUBLIC_NOTHING;
                                                style = 'style="color: black;pointer-events:none;"'
                                            }
                                            fsnodeuuid = item.node_uuid;
                                            var agent_uuid = item.agent_uuid
                                            tbody += '<td>' + '<a value="'+ agent_uuid +'" class="downloadPassFile" '+ style +'>'+ elecontent +'<span class="display-none">'+ item.passfile_file_path +'</span></a>' + '</td>';
                                            tbody += '</tr>';
                                        });
                                    }
                                    html += thead + tbody;
                                } else if(data.info.sub_module_type_value == 1 || data.info.sub_module_type_value == 2) { // 文件和NAS
                                    var thead = '';
                                    thead += '<th>' + LANG.UI_JOB_HIS_SRC_HOST_NAME + '/' + LANG.UI_JOB_HIS_SRC_HOST_IP + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_PERMISSION_BACKUP + '</th>';
                                    thead += '<th>' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_WILDCARD + '</th>';
                                    //判断是否显示归档
                                    var contentTh = '';
                                    var contentTb = '';
                                    if (Object.keys($('#archiveflag')).length != 0) {
                                        contentTh = '<th>' + LANG.UI_ARCHIVE + '</th>';
                                        contentTb = '<td>' + getFlagInfo(data.list.file_archive_flag == undefined ? 2 : data.list.file_archive_flag) + '</td>';
                                    }
                                    thead += contentTh;
                                    thead += '<th>' + LANG.UI_JOB_HIS_BAK_FILE_TOTAL + '</th>';
                                    thead += '<th>' + LANG.UI_JOB_HIS_BAK_FILE_LIST + '</th>';
                                    thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                    thead += '<th> ' + LANG.UI_FILE_COUNT_PASS + ' </th>';
                                    thead += '<th> ' + LANG.UI_FILE_COUNT_PASS_DIR + ' </th>';
                                    thead += '<th> ' + LANG.UI_FILE_PASSFILE_LISTS + ' </th>';
                                    var tbody = '';
                                    if (data.list.list == undefined) {
                                        tbody += "";
                                    } else {
                                        data.list.list.forEach(item => {
                                            //通配符
                                            if (item.wildcard_list == undefined || item.wildcard_list == "") {
                                                wildcard = LANG.UI_PUBLIC_NOTHING;
                                                wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                            } else {
                                                var wildcard_list = JSON.parse(item.wildcard_list);
                                                var wildcard_mode, wildcard = [];
                                                if (wildcard_list.wildcard_mode != null && wildcard_list.wildcard_mode != 0) {
                                                    wildcard = wildcard_list.wildcard.join('<br>');
                                                    wildcard_list.wildcard_mode == 1 ? wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER : wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
                                                } else {
                                                    wildcard = LANG.UI_PUBLIC_NOTHING;
                                                    wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                                }
                                            }
                                            //跳过目录个数
                                            var passdir = item.total_pass_dir_number == undefined ? 0 : item.total_pass_dir_number;
                                            tbody += '<tr>';
                                            tbody += '<td>' + item.src_agent_name + '(' + item.src_agent_ip + ')</td>';
                                            tbody += '<td>' + data.list.permission_operate_flag + '</td>';
                                            tbody += '<td>' + item.backup_mode + '</td>';
                                            tbody += '<td>' + wildcard_mode + '</td>';
                                            tbody += '<td style="width: 8%;">' + wildcard + '</td>';
                                            tbody += contentTb;
                                            tbody += '<td>' + item.file_count + '</td>';

                                            tbody += '<td style="padding-right: 30px"><div class="historyfilelisttext">';
                                            var list = '';
                                            if (!item.file_list) {
                                                return LANG.UI_PUBLIC_NOTHING;
                                            }
                                            for (var i = 0; i < item.file_list.length; i++) {
                                                list += item.file_list[i] + "<br>";
                                            }
                                            tbody += list;
                                            tbody += '</div></td>';
                                            tbody += '<td style="width: 8%;">' + item.description + '</td>';
                                            tbody += '<td>' + item.total_pass_number + '</td>';
                                            tbody += '<td>' + passdir + '</td>';
                                            var elecontent = '',
                                                style = '';
                                            if (item.passfile_exist_flag == 1) {
                                                elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                            } else {
                                                elecontent = LANG.UI_PUBLIC_NOTHING;
                                                style = 'style="color: black;pointer-events:none;"'
                                            }
                                            fsnodeuuid = item.node_uuid;
                                            var agent_uuid = item.agent_uuid
                                            tbody += '<td>' + '<a value="' + agent_uuid + '" class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + item.passfile_file_path + '</span></a>' + '</td>';
                                            tbody += '</tr>';
                                        });
                                    }
                                    html += thead + tbody;
                                } else if (data.info.sub_module_type_value == 4) { // 对象存储
                                    var thead = '';
                                    thead += '<th>' + LANG.UI_OBS_SOURCE_OBS + '</th>';
                                    thead += '<th>' + LANG.UI_OBS_PERMISSION_BACKUP + '</th>';
                                    thead += '<th>' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_WILDCARD + '</th>';
                                    //判断是否显示归档
                                    var contentTh = '';
                                    var contentTb = '';
                                    if (Object.keys($('#archiveflag')).length != 0) {
                                        contentTh = '<th>' + LANG.UI_ARCHIVE + '</th>';
                                        contentTb = '<td>' + getFlagInfo(data.list.file_archive_flag == undefined ? 2 : data.list.file_archive_flag) + '</td>';
                                    }
                                    thead += contentTh;
                                    thead += '<th>' + LANG.UI_OBS_BACKUP_OBJ_TOTAL + '</th>';
                                    thead += '<th>' + LANG.UI_OBS_BACKUP_OBJ_LIST + '</th>';
                                    thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                    thead += '<th> ' + LANG.UI_FILE_COUNT_PASS + ' </th>';
                                    // thead += '<th> ' + LANG.UI_FILE_COUNT_PASS_DIR + ' </th>';
                                    thead += '<th> ' + LANG.UI_FILE_PASSFILE_LISTS + ' </th>';
                                    var tbody = '';
                                    if (data.list.list == undefined) {
                                        tbody += "";
                                    } else {
                                        data.list.list.forEach(item => {
                                            //通配符
                                            if (item.wildcard_list == undefined || item.wildcard_list == "") {
                                                wildcard = LANG.UI_PUBLIC_NOTHING;
                                                wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                            } else {
                                                var wildcard_list = JSON.parse(item.wildcard_list);
                                                var wildcard_mode, wildcard = [];
                                                if (wildcard_list.wildcard_mode != null && wildcard_list.wildcard_mode != 0) {
                                                    wildcard = wildcard_list.wildcard.join('<br>');
                                                    wildcard_list.wildcard_mode == 1 ? wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER : wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
                                                } else {
                                                    wildcard = LANG.UI_PUBLIC_NOTHING;
                                                    wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                                }
                                            }
                                            //跳过目录个数
                                            var passdir = item.total_pass_dir_number == undefined ? 0 : item.total_pass_dir_number;
                                            tbody += '<tr>';
                                            tbody += '<td>' + item.src_agent_name + '</td>';
                                            tbody += '<td>' + data.list.permission_operate_flag + '</td>';
                                            tbody += '<td>' + item.backup_mode + '</td>';
                                            tbody += '<td>' + wildcard_mode + '</td>';
                                            tbody += '<td style="width: 8%;">' + wildcard + '</td>';
                                            tbody += contentTb;
                                            tbody += '<td>' + item.file_count + '</td>';

                                            tbody += '<td style="padding-right: 30px"><div class="historyfilelisttext">';
                                            var list = '';
                                            if (!item.file_list) {
                                                list += LANG.UI_PUBLIC_NOTHING;
                                            } else {
                                                item.file_list.map(item => {
                                                    return replaceBetweenStartEnd(item, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                                }).forEach(i => {
                                                    list += i + "<br>";
                                                })
                                            }
                                            tbody += list;
                                            tbody += '</div></td>';
                                            tbody += '<td style="width: 8%;">' + item.description + '</td>';
                                            tbody += '<td>' + item.total_pass_number + '</td>';
                                            // tbody += '<td>' + passdir + '</td>';
                                            var elecontent = '',
                                                style = '';
                                            if (item.passfile_exist_flag == 1) {
                                                elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                            } else {
                                                elecontent = LANG.UI_PUBLIC_NOTHING;
                                                style = 'style="color: black;pointer-events:none;"'
                                            }
                                            fsnodeuuid = item.node_uuid;
                                            var agent_uuid = item.agent_uuid
                                            tbody += '<td>' + '<a value="' + agent_uuid + '" class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + item.passfile_file_path + '</span></a>' + '</td>';
                                            tbody += '</tr>';
                                        });
                                    }
                                    html += thead + tbody;
                                }
                            }
                            if (data.info.job_type == 2) { //恢复
                                var agent_des_info = getHistorySrcDes(data);
                                if(data.info.sub_module_type_value == 3){ // hadoop
                                    var recoveryWayDes = '';
                                    var pathWayDes = ''
                                    if (1 != data.list.recovery_way) {//恢复到源路径不显示目录树
                                        recoveryWayDes += LANG.UI_HADOOP_DIR_TREE_RECOVERY + ':' + data.list.dir_tree_recovery_flag + ';<br>';
                                        pathWayDes = LANG.UI_MICROSOFT365_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_NEW;
                                    } else {
                                        pathWayDes = LANG.UI_MICROSOFT365_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_OLD;
                                    }
                                    recoveryWayDes += LANG.UI_FILE_PROCESS_SAME_FILE + ':' + data.list.same_file_strategy + ';<br>';
                                    // if (data.list.timepoint_submodule_type != CONF.SUBMODULE_TYPE.OBS) {
                                    //     recoveryWayDes += LANG.UI_FILE_INVALID_SHORTCUT_CLEAN + ':' + data.list.link_file_pass_flag + ';<br>';
                                    // }
                                    if (data.list.timepoint_submodule_type == CONF.SUBMODULE_TYPE.HADOOP) {
                                        recoveryWayDes += LANG.UI_FILE_PERMISSION_RECOVERY + ':' + data.list.permission_operate_flag + ';<br>';
                                    }
                                    recoveryWayDes += pathWayDes;
                                    var thead = '<thead>';
                                    thead += '<th>' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>';
                                    thead += '<th>' + agent_des_info.thead + '</th>';
                                    thead += '<th>' + LANG.UI_HADOOP_TARGET_CLUSTER_NAME + '</th>';
                                    thead += '<th>' + LANG.UI_JOB_HIS_REC_FILE_TOTAL + '</th>';
                                    // thead += '<th width="8%">' + LANG.UI_DB_RECOVERY_TYPE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_TASK_HIGH_CONFIG + '</th>';
                                    thead += '<th>' + LANG.UI_JOB_HIS_REC_FILE_LIST + '</th>';
                                    thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_COUNT_PASS + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_PASSFILE_LISTS + '</th>';

                                    var tbody = '<tr>';
                                    tbody += '<td>' + data.list.timepoint + '(' + data.list.backup_mode + ')</td>';
                                    tbody += '<td>' +  agent_des_info.sourceClientName + '</td>';
                                    tbody += '<td>' + data.list.des_agent_name + '(' + data.list.des_agent_ip + ')' + '</td>';
                                    tbody += '<td>' + data.list.file_count + '</td>';
                                    tbody += '<td style="width: 9%;">' + recoveryWayDes + '</td>';

                                    tbody += '<td style="padding-right: 20px;"><div class="historyfilelisttext">';
                                    var list = '';
                                    for(var i=0; i<data.list.file_list.length; i++){
                                        list += replaceBetweenStartEnd(data.list.file_list[i], '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;')  + "<br>";
                                    }
                                    tbody += list;
                                    tbody += '</div></td>';
                                    tbody += '<td style="width: 8%;">' + data.list.list[0].description + '</td>';
                                    tbody += '<td>' + data.list.list[0].total_pass_number + '</td>';
                                    tbody += '<td>' + data.list.list[0].total_pass_dir_number + '</td>';
                                    var elecontent = '',style = '';
                                    if(data.list.list[0].passfile_exist_flag==1) {
                                        elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                    }else {
                                        elecontent = LANG.UI_PUBLIC_NOTHING;
                                        style = 'style="color: black;pointer-events:none;"'
                                    }
                                    fsnodeuuid = data.list.list[0].node_uuid;
                                    tbody += '<td>' + '<a class="downloadPassFile" '+ style +'>'+ elecontent +'<span class="display-none">'+ data.list.list[0].passfile_file_path +'</span></a>' + '</td>';
                                    tbody += '</tr>';
                                    html += thead + tbody;
                                }else if (data.info.sub_module_type_value == 1 || data.info.sub_module_type_value == 2){ // 文件 nas
                                    var recoveryWayDes = '';
                                    var pathWayDes = ''
                                    if (1 != data.list.recovery_way) { //恢复到源路径不显示目录树
                                        recoveryWayDes += LANG.UI_FILE_SKIP_DIR_TREE_RECOVERY+ ':' + data.list.dir_tree_recovery_flag + ';<br>';
                                        pathWayDes = LANG.UI_DB_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_NEW;
                                    } else {
                                        pathWayDes = LANG.UI_DB_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_OLD;
                                    }
                                    recoveryWayDes += LANG.UI_FILE_PROCESS_SAME_FILE + ':' + data.list.same_file_strategy + ';<br>';
                                    if (data.list.timepoint_submodule_type != CONF.SUBMODULE_TYPE.OBS && data.list.timepoint_submodule_type != CONF.SUBMODULE_TYPE.HADOOP) {
                                        recoveryWayDes += LANG.UI_FILE_INVALID_SHORTCUT_CLEAN + ':' + data.list.link_file_pass_flag + ';<br>';
                                        recoveryWayDes += LANG.UI_FILE_PERMISSION_RECOVERY + ':' + data.list.permission_operate_flag + ';<br>';
                                    }

                                    recoveryWayDes += pathWayDes;
                                    var thead = '';
                                    thead += '<th width="9%">' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>';
                                    thead += '<th width="10%">' + agent_des_info.thead + '</th>';
                                    thead += '<th width="10%">' + LANG.UI_FILE_HISJOB_DES_NAME_AND_IP + '</th>';
                                    thead += '<th>' + LANG.UI_JOB_HIS_REC_FILE_TOTAL + '</th>';
                                    // thead += '<th width="8%">' + LANG.UI_DB_RECOVERY_TYPE + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_TASK_HIGH_CONFIG + '</th>';
                                    thead += '<th width="10%">' + LANG.UI_JOB_HIS_REC_FILE_LIST + '</th>';
                                    thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_COUNT_PASS + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>';
                                    thead += '<th>' + LANG.UI_FILE_PASSFILE_LISTS + '</th>';

                                    var tbody = '<tr>';
                                    tbody += '<td>' + data.list.timepoint + '(' + data.list.backup_mode + ')</td>';
                                    tbody += '<td>' + agent_des_info.sourceClientName + '</td>';
                                    tbody += '<td>' + data.list.des_agent_name + '(' + data.list.des_agent_ip + ')' + '</td>';
                                    tbody += '<td>' + data.list.file_count + '</td>';
                                    tbody += '<td style="width: 9%;">' + recoveryWayDes + '</td>';
                                    // tbody += '<td>' + LANG.UI_FILE_HISJOB_OVERWRITE_RECOVER + '</td>';//暂时只有覆盖恢复

                                    tbody += '<td style="padding-right: 20px;"><div class="historyfilelisttext">';
                                    var list = '';
                                    for (var i = 0; i < data.list.file_list.length; i++) {
                                        list += data.list.file_list[i] + "<br>";
                                    }
                                    tbody += list;
                                    tbody += '</div></td>';
                                    tbody += '<td style="width: 8%;">' + data.list.list[0].description + '</td>';
                                    tbody += '<td>' + data.list.list[0].total_pass_number + '</td>';
                                    tbody += '<td>' + data.list.list[0].total_pass_dir_number + '</td>';
                                    var elecontent = '',
                                        style = '';
                                    if (data.list.list[0].passfile_exist_flag == 1) {
                                        elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                    } else {
                                        elecontent = LANG.UI_PUBLIC_NOTHING;
                                        style = 'style="color: black;pointer-events:none;"'
                                    }
                                    fsnodeuuid = data.list.list[0].node_uuid;
                                    tbody += '<td>' + '<a class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + data.list.list[0].passfile_file_path + '</span></a>' + '</td>';
                                    tbody += '</tr>';
                                    html += thead + tbody;
                                } else if (data.info.sub_module_type_value == 4) { // 对象存储
                                    let sourceClientModuleType = parseInt(data.list.timepoint_submodule_type);

                                    var recoveryWayDes = '';
                                    var pathWayDes = ''
                                    if (1 != data.list.recovery_way) { //恢复到源路径不显示目录树
                                        recoveryWayDes += LANG.UI_OBS_UNPREFIX + ':' + data.list.dir_tree_recovery_flag + ';<br>';
                                        pathWayDes = LANG.UI_DB_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_NEW;
                                    } else {
                                        pathWayDes = LANG.UI_DB_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_OLD;
                                    }
                                    recoveryWayDes += LANG.UI_OBS_SAME_OBJ_PROCESS + ':' + data.list.same_file_strategy + ';<br>';
                                    // recoveryWayDes += LANG.UI_FILE_INVALID_SHORTCUT_CLEAN + ':' + data.list.link_file_pass_flag + ';<br>';
                                    // recoveryWayDes += LANG.UI_FILE_PERMISSION_RECOVERY + ':' + data.list.permission_operate_flag + ';<br>';
                                    recoveryWayDes += pathWayDes;
                                    var thead = '';
                                    thead += '<th>' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>';

                                    let sourceClientName = '', targetClientName = '';
                                    switch (sourceClientModuleType) {
                                        case 1: // 源设备为文件客户端
                                            thead += '<th>' + LANG.UI_FILE_HISJOB_SOURCE_NAME_AND_IP + '</th>';
                                            sourceClientName = data.list.list[0].src_agent_name + '(' + data.list.list[0].src_agent_ip + ')';
                                            targetClientName = data.list.des_agent_name + '(' + data.list.des_agent_ip + ')';

                                            break;
                                        case 2: // 源设备为NAS
                                            thead += '<th>' + LANG.UI_OBS_SOURCE_DEVICE + '</th>';
                                            sourceClientName = data.list.list[0].src_agent_name + '(' + data.list.list[0].src_agent_ip + ')';
                                            targetClientName = data.list.des_agent_name + '(' + data.list.des_agent_ip + ')';

                                            break;
                                        case 3: // 源设备为hadoop集群
                                            thead += '<th>' + LANG.UI_OBS_SOURCE_HADOOP_CLUSTER + '</th>';
                                            sourceClientName = data.list.list[0].src_agent_name + '(' + data.list.list[0].src_agent_ip + ')';
                                            targetClientName = data.list.des_agent_name + '(' + data.list.des_agent_ip + ')';

                                            break;
                                        case 4: // 源设备为对象存储
                                            thead += '<th>' + LANG.UI_OBS_SOURCE_OBS + '</th>';
                                            sourceClientName = data.list.list[0].src_agent_name;
                                            targetClientName = data.list.des_agent_name;

                                            break;
                                        default:
                                            break;
                                    }

                                    thead += '<th>' + LANG.UI_OBS_TARGET_OBS + '</th>';
                                    thead += '<th>' + LANG.UI_OBS_RECOVER_OBJ_TOTAL + '</th>';
                                    // thead += '<th width="8%">' + LANG.UI_DB_RECOVERY_TYPE + '</th>';
                                    thead += '<th>' + LANG.UI_JOB_HIS_DES_PATH + '</th>';
                                    thead += '<th>' + LANG.UI_OBS_RECOVER_OBJ_LIST + '</th>';
                                    thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                    thead += '<th>' + LANG.UI_OBS_BACKUP_SKIP_NUMS + '</th>';
                                    // thead += '<th>' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>';
                                    thead += '<th>' + LANG.UI_OBS_BACKUP_SKIP_LIST + '</th>';

                                    let tbody = '<tr>';
                                    tbody += '<td>' + data.list.timepoint + '(' + data.list.backup_mode + ')</td>';
                                    tbody += '<td>' + sourceClientName + '</td>';
                                    tbody += '<td>' + targetClientName + '</td>';
                                    tbody += '<td>' + data.list.file_count + '</td>';
                                    tbody += '<td style="width: 9%;">' + recoveryWayDes + '</td>';
                                    // tbody += '<td>' + LANG.UI_FILE_HISJOB_OVERWRITE_RECOVER + '</td>';//暂时只有覆盖恢复

                                    tbody += '<td style="padding-right: 20px;"><div class="historyfilelisttext">';
                                    let list = '';

                                    if (data.list.file_list && data.list.file_list.length > 0) {
                                        data.list.file_list.map(item => {
                                            return replaceBetweenStartEnd(item, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                                        }).forEach(i => {
                                            list += i + "<br>";
                                        });
                                    }

                                    tbody += list;
                                    tbody += '</div></td>';
                                    tbody += '<td style="width: 8%;">' + data.list.list[0].description + '</td>';
                                    tbody += '<td>' + data.list.list[0].total_pass_number + '</td>';
                                    // tbody += '<td>' + data.list.list[0].total_pass_dir_number + '</td>';
                                    var elecontent = '',
                                        style = '';
                                    if (data.list.list[0].passfile_exist_flag == 1) {
                                        elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                    } else {
                                        elecontent = LANG.UI_PUBLIC_NOTHING;
                                        style = 'style="color: black;pointer-events:none;"'
                                    }
                                    fsnodeuuid = data.list.list[0].node_uuid;
                                    tbody += '<td>' + '<a class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + data.list.list[0].passfile_file_path + '</span></a>' + '</td>';
                                    tbody += '</tr>';
                                    html += thead + tbody;
                                }
                            }
                            if (data.info.job_type == 51) { //下云同步
                                var syncType = data.list[0].synctype;
                                var syncpath = '';
                                if (syncType == 1) {
                                    syncpath = LANG.UI_SYNC_CBR_STORAGE_PATH
                                } else if (syncType == 2) {
                                    syncpath = LANG.UI_SYNC_CBR_RESOURCE_PATH
                                } else if (syncType == 3) {
                                    syncpath = LANG.UI_SYNC_CBR_TIMEPOINT_PATH
                                }
                                html += `
                                    <th>` + syncpath + `</th>
                                    <th>` + LANG.UI_SYNC_CBR_TIMEPOINT_NUM + `</th>
                                    <th>` + LANG.UI_SYNC_CBR_TIMEPOINT_LIST + `</th>`;
                                for (var i = 0; i < data.list.length; i++) {
                                    var timepoints = "";
                                    if (data.list[i].count == 0 && data.list[i].backups == null) {
                                        data.list[i].count = "--";
                                        timepoints = "--";
                                    } else {
                                        var backuplist = [];
                                        for (let j = 0; j < data.list[i].backups.length; j++) {
                                            var backupname = data.list[i].backups[j].backup_name;
                                            backuplist.push(backupname);
                                        }
                                        timepoints ='<textarea disabled>' + backuplist.join('<br>') + '</textarea>' ;
                                    }
                                    html += `<tr>
                                        <td>` + data.list[i].path + `</td>
                                        <td>` + data.list[i].count + `</td>
                                        <td>` + timepoints + `</td>
                                    </tr>`
                                }
                            }
                            break;
                        case 11: //NAS
                            if (data.info.job_type == 1) { //备份
                                var thead = '';
                                thead += '<th>' + LANG.UI_NAS_DETAIL_RESOURCE_DEVICE + '</th>';
                                thead += '<th>' + LANG.UI_FILE_PERMISSION_BACKUP + '</th>';
                                thead += '<th>' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
                                thead += '<th>' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th>';
                                thead += '<th>' + LANG.UI_FILE_WILDCARD + '</th>';
                                // thead += '<th width="5%">' + LANG.UI_ARCHIVE + '</th>';
                                thead += '<th>' + LANG.UI_JOB_HIS_BAK_FILE_TOTAL + '</th>';
                                thead += '<th>' + LANG.UI_JOB_HIS_BAK_FILE_LIST + '</th>';
                                thead += '<th width="10%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                thead += '<th>' + LANG.UI_FILE_COUNT_PASS + '</th>';
                                thead += '<th>' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>';
                                thead += '<th>' + LANG.UI_FILE_PASSFILE_LISTS + '</th>';

                                thead += '';
                                var tbody = '';
                                if (data.list.list == undefined) {
                                    tbody += "";
                                } else {
                                    data.list.list.forEach(item => {
                                        //通配符
                                        if (item.wildcard_list == undefined || item.wildcard_list == "") {
                                            wildcard = LANG.UI_PUBLIC_NOTHING;
                                            wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                        } else {
                                            var wildcard_list = JSON.parse(item.wildcard_list);
                                            var wildcard_mode, wildcard = [];
                                            if (wildcard_list.wildcard_mode != null && wildcard_list.wildcard_mode != 0) {
                                                wildcard = wildcard_list.wildcard.join('<br>');
                                                wildcard_list.wildcard_mode == 1 ? wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER : wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
                                            } else {
                                                wildcard = LANG.UI_PUBLIC_NOTHING;
                                                wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                                            }
                                        }
                                        tbody += '<tr>';
                                        tbody += '<td>' + item.src_agent_ip + "(" + item.src_agent_name + ')</td>';
                                        tbody += '<td>' + data.list.permission_operate_flag + '</td>';
                                        tbody += '<td>' + item.backup_mode + '</td>';
                                        tbody += '<td>' + wildcard_mode + '</td>';
                                        tbody += '<td>' + wildcard + '</td>';
                                        // tbody += '<td>' + getFlagInfo(data.info.file_archive_flag) + '</td>';
                                        tbody += '<td>' + item.file_count + '</td>';
                                        tbody += '<td style="width: 20%;padding-right: 20px;"><div class="historyfilelisttext" style="overflow-wrap: anywhere;">';
                                        var list = '';
                                        //跳过文件详情模态框表格里面的数据
                                        //备份文件总数
                                        if (item.all_scan_file_count == undefined) {
                                            $('.passdetailTr').html('<td>' + item.total_pass_number + '</td><td>--</td><td>' + item.total_pass_dir_number + '</td><td>--</td><td>0</td><td>0.00%</td>');
                                            $('.passreasonTr').html('<td>--</td><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td>')
                                        } else {
                                            var total = parseInt(item.dir_count) + parseInt(item.all_scan_file_count);
                                            $('.passdetailTr').html('<td>' + item.total_pass_number + '</td><td>' +
                                                ((item.total_pass_number / total) * 100).toFixed(2) + '%</td><td>' +
                                                item.total_pass_dir_number + '</td><td>' +
                                                ((item.total_pass_dir_number / total) * 100).toFixed(2) + '%</td><td>' +
                                                data.list.passfile_number_limit + '</td><td>' +
                                                data.list.passfile_ratio_limit + '%</td>');
                                            $('.passreasonTr').html('<td>' + item.passfile_file_number_occupy + '</td><td>' +
                                                item.passfile_file_number_delete + '</td><td>' +
                                                item.passfile_file_number_reject + '</td><td>' +
                                                item.passfile_dir_number_reject + '</td><td>' +
                                                item.passfile_dir_number_delete + '</td><td>' +
                                                (parseInt(item.passfile_dir_number_other) + parseInt(item.passfile_file_number_other)) + '</td>');
                                        }
                                        //跳过目录个数
                                        var passdir = item.total_pass_dir_number == undefined ? 0 : item.total_pass_dir_number;
                                        if (!item.file_list) {
                                            return LANG.UI_PUBLIC_NOTHING;
                                        }
                                        for (var i = 0; i < item.file_list.length; i++) {
                                            list += item.file_list[i] + "<br>";
                                        }
                                        tbody += list;
                                        tbody += '</div></td>';
                                        tbody += '<td>' + item.description + '</td>';
                                        tbody += '<td>' + item.total_pass_number + '</td>';
                                        tbody += '<td>' + passdir + '</td>';
                                        var elecontent = '',
                                            style = '';
                                        if (item.passfile_exist_flag == 1) {
                                            elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                        } else {
                                            elecontent = LANG.UI_PUBLIC_NOTHING;
                                            style = 'style="color: black;pointer-events:none;"'
                                        }
                                        fsnodeuuid = item.node_uuid;
                                        tbody += '<td>' + '<a class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + item.passfile_file_path + '</span></a>' + '</td>';
                                        tbody += '</tr>';
                                    });
                                }

                                html += thead + tbody;
                            }
                            if (data.info.job_type == 2) { //恢复
                                var recoveryWayDes = '';
                                var pathWayDes = ''
                                if (1 != data.list.recovery_way) { //恢复到源路径不显示目录树
                                    recoveryWayDes += LANG.UI_FILE_SKIP_DIR_TREE_RECOVERY + ':' + data.list.dir_tree_recovery_flag + ';<br>';
                                    pathWayDes = LANG.UI_DB_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_NEW;
                                } else {
                                    pathWayDes = LANG.UI_DB_RECOVERY_TYPE + ':' + LANG.UI_FILE_HISJOB_RECOVERY_TO_OLD;
                                }
                                recoveryWayDes += LANG.UI_FILE_PROCESS_SAME_FILE+ ':' + data.list.same_file_strategy + ';<br>';
                                if (data.list.timepoint_submodule_type != CONF.SUBMODULE_TYPE.OBS && data.list.timepoint_submodule_type != CONF.SUBMODULE_TYPE.HADOOP) {
                                    recoveryWayDes += LANG.UI_FILE_INVALID_SHORTCUT_CLEAN + ':' + data.list.link_file_pass_flag + ';<br>';
                                    recoveryWayDes += LANG.UI_FILE_PERMISSION_RECOVERY + ':' + data.list.permission_operate_flag + ';<br>';
                                }
                                recoveryWayDes += pathWayDes;
                                var agent_des_info = getHistorySrcDes(data);
                                var thead = '';
                                thead += '<th width=9%">' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>';
                                thead += '<th width="10%">' + agent_des_info.thead + '</th>';
                                thead += '<th width="10%">' + LANG.UI_NAS_DETAIL_DES_DEVICE + '</th>';
                                thead += '<th>' + LANG.UI_JOB_HIS_REC_FILE_TOTAL + '</th>';
                                // thead += '<th width="8%">' + LANG.UI_DB_RECOVERY_TYPE + '</th>';
                                thead += '<th>' + LANG.UI_FILE_TASK_HIGH_CONFIG + '</th>';
                                thead += '<th width="10%">' + LANG.UI_JOB_HIS_REC_FILE_LIST + '</th>';
                                thead += '<th width="6%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
                                thead += '<th>' + LANG.UI_FILE_COUNT_PASS + '</th>';
                                thead += '<th>' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>';
                                thead += '<th>' + LANG.UI_FILE_PASSFILE_LISTS + '</th>';
                                thead += '';

                                var tbody = '';
                                tbody += '<tr>';
                                tbody += '<td>' + data.list.timepoint + '(' + data.list.backup_mode + ')</td>';
                                tbody += '<td>' + agent_des_info.sourceClientName + '</td>';
                                tbody += '<td>' + data.list.des_agent_ip + "(" + data.list.des_agent_name + ')</td>';
                                tbody += '<td>' + data.list.file_count + '</td>';
                                tbody += '<td>' + recoveryWayDes + '</td>';
                                // tbody += '<td>' + LANG.UI_FILE_HISJOB_OVERWRITE_RECOVER + '</td>';//暂时只有覆盖恢复

                                tbody += '<td style="width: 20%;padding-right: 20px;"><div class="historyfilelisttext" style="overflow-wrap: anywhere;">';
                                var list = '';
                                for (var i = 0; i < data.list.file_list.length; i++) {
                                    list += data.list.file_list[i] + "<br>";
                                }
                                tbody += list;
                                tbody += '</div></td>';
                                tbody += '<td>' + data.list.list[0].description + '</td>';
                                tbody += '<td>' + data.list.list[0].total_pass_number + '</td>';
                                tbody += '<td>' + data.list.list[0].total_pass_dir_number + '</td>';
                                var elecontent = '',
                                    style = '';
                                if (data.list.list[0].passfile_exist_flag == 1) {
                                    elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                } else {
                                    elecontent = LANG.UI_PUBLIC_NOTHING;
                                    style = 'style="color: black;pointer-events:none;"'
                                }
                                fsnodeuuid = data.list.list[0].node_uuid;
                                tbody += '<td>' + '<a class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + data.list.list[0].passfile_file_path + '</span></a>' + '</td>';
                                tbody += '</tr>';
                                html += thead + tbody;
                            }
                            if (data.info.job_type == 51) { //下云同步
                                var syncType = data.list[0].synctype;
                                var syncpath = '';
                                if (syncType == 1) {
                                    syncpath = LANG.UI_SYNC_CBR_STORAGE_PATH
                                } else if (syncType == 2) {
                                    syncpath = LANG.UI_SYNC_CBR_RESOURCE_PATH
                                } else if (syncType == 3) {
                                    syncpath = LANG.UI_SYNC_CBR_TIMEPOINT_PATH
                                }

                                html += `
                                    <th>` + syncpath + `</th>
                                    <th>` + LANG.UI_SYNC_CBR_TIMEPOINT_NUM + `</th>
                                    <th>` + LANG.UI_SYNC_CBR_TIMEPOINT_LIST + `</th>`;
                                for (var i = 0; i < data.list.length; i++) {
                                    var timepoints = "";
                                    if (data.list[i].count == 0 && data.list[i].backups == null) {
                                        data.list[i].count = "--";
                                        timepoints = "--";
                                    } else {
                                        var backuplist = [];
                                        for (let j = 0; j < data.list[i].backups.length; j++) {
                                            var backupname = data.list[i].backups[j].backup_name;
                                            backuplist.push(backupname);
                                        }
                                        timepoints ='<textarea disabled>' + backuplist.join('<br>') + '</textarea>' ;
                                    }
                                    html += `<tr>
                                        <td>` + data.list[i].path + `</td>
                                        <td>` + data.list[i].count + `</td>
                                        <td>` + timepoints + `</td>
                                    </tr>`
                                }
                            }
                            break;
                        case 4: //数据库
                            html += getDBJobDetails(data);
                            break;
                        case 5: //操作系统
                            html += getTimingCompleteJobDetails(data);
                            break;
                        case 10: //卷cdp
                            html += getRealTimeCompleteJobDetails(data);

                            break;
                        case 14: //EXCHANGE
                            var pass_item_file_size = 0;
                            var pass_list_display = 'display-none';
                            if (data.list.pass_item_flag != 2) {
                                pass_item_file_size = data.list.total_pass_item_count;
                                pass_list_display = '';
                            }

                            if (data.info.job_type == 1) { //备份
                                if (data.list.backup_m365_object_info_list) {
                                    //排除目录
                                    var exclude_folders = getExcludeDirDes(data.list.exclude_folders);
                                    html += '<div class="col-md-3 detail-padding">' +
                                                '<div>'+ LANG.UI_MICROSOFT365_SOURCE_ORGANIZATION_NAME + data.list.organization_name + '</div>' +
                                                '<div>'+ LANG.UI_MICROSOFT365_BACKUP_DATA_NUM + data.list.current_complete_item_count + '</div>' +
                                                '<div>'+ LANG.UI_MICROSOFT365_EXCLUDE_DIR_NEW + exclude_folders + '</div>' +
                                            ' </div>';
                                    html += '<div class="col-md-3 detail-padding">' +
                                                '<div>'+ LANG.UI_MICROSOFT365_BACKUP_DATA_LIST +'：<textarea class="detail-textarea">' + data.list.backup_m365_object_info_list.join("\n") + '</textarea></div>' +
                                            '</div>';
                                    html += '<div class="col-md-4 detail-padding">' +
                                                '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_NUM + pass_item_file_size + '</div>' +
                                                '<div class="' + pass_list_display + '">' +
                                                '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_LIST +'<span class="downloadPassData exchange" href="#" name = "' +
                                                data.list.pass_item_path + '" node_uuid = "' + data.list.node_uuid + '">' + LANG.UI_MICROSOFT365_VIEW_DETAILS + '</span></div></div>' +
                                                '<div>'+ LANG.UI_PUBLIC_DESCRIPTION +'：' + data.list.error_code + '</div>' +
                                            ' </div>';
                                } else {
                                    html += '--';
                                }
                            } else { //恢复
                                var recovert_list = showSrcData(data.list.recovery_m365_object_info_list);
                                var overwrite = data.list.overwrite == 0 ? LANG.UI_MICROSOFT365_NEW_RECOVERY : LANG.UI_MICROSOFT365_COVER_RECOVERY;
                                var destination_mail = data.list.destination_mail == "" ? LANG.UI_MICROSOFT365_NOT_SPECIFY_USER : data.list.destination_mail;
                                html += '<div class="col-md-3 detail-padding">' +
                                            '<div>'+ LANG.UI_MICROSOFT365_BACKUP_TIMEPOINT + data.list.timepoint + '(' + data.list.backup_mode + ')' + '</div>' +
                                            '<div>'+ LANG.UI_MICROSOFT365_SOURCE_ORGANIZATION_NAME_NEW + data.list.organization_name + '</div>' +
                                            '<div>'+ LANG.UI_MICROSOFT365_TARGET_ORGANIZATION +'：' + data.list.destination_organization_name + '</div>' +
                                        ' </div>';
                                html += '<div class="col-md-2 detail-padding">' +
                                            '<div>'+ LANG.UI_MICROSOFT365_RECOVERY_DATA_LIST +'：' + recovert_list + '</div>' +
                                            '<div>'+ LANG.UI_MICROSOFT365_RECOVERY_DATA_ITEMS +'：' + data.list.current_complete_item_count + '</div>' +
                                            '<div>'+ LANG.UI_MICROSOFT365_RECOVERY_TYPE +'：' + overwrite + '</div>' +
                                        '</div>';
                                html += '<div class="col-md-2 detail-padding">' +
                                            '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_NUM + pass_item_file_size + '</div>' +
                                            '<div class="' + pass_list_display + '">' +
                                            '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_LIST +'<span class="downloadPassData exchange" href="#" name = "' +
                                            data.list.pass_item_path + '" node_uuid = "' + data.list.node_uuid + '">' + LANG.UI_MICROSOFT365_VIEW_DETAILS  + '</span></div></div>' +
                                            '<div>'+ LANG.UI_MICROSOFT365_SPECIFY_USER +'：' + destination_mail + '</div>' +
                                        ' </div>';
                                html += '<div class="col-md-2 detail-padding">' +
                                            '<div>'+ LANG.UI_PUBLIC_DESCRIPTION +'：' + data.list.error_code + '</div>' +
                                        '</div>';
                            }

                            break;
                        case 26: //文件复制
                            var job_type_des = LANG.UI_CM_CDP_REPLICATION;
                            if (data.info.job_type == CONF.TASK_TYPE.FILE_COMPARE) {
                                job_type_des = LANG.UI_FILE_COPY_TASK_TYPE_COMPARE;
                            }
                            var thead = '';
                            var tbody = '';
                            var elecontent = '',style = '';
                            thead = `<thead>` +
                                        `<th style="width: 7%;"> `+ LANG.UI_FILE_DETAIL_SRC_NAME +`</th>`+
                                        `<th style="width: 7%;"> `+ LANG.UI_FILE_DETAIL_DES_NAME +`</th>`+
                                        `<th style="width: 7%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_FILTER_BY_WILDCARD +`</th>`+
                                        `<th style="width: 10%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_FILTER_BY_TIME +`</th>`+
                                        `<th style="width: 6%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_COPY_FILE_COUNT +`</th>`+
                                        `<th style="width: 6%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_COPY_DIR_COUNT +`</th>`+
                                        `<th style="width: 8%;"> `+ job_type_des + LANG.UI_HISTORY_JOB_DETAIL_COPY_FILE_COUNT +`</th>`+
                                        `<th style="width: 8%;"> `+ job_type_des + LANG.UI_HISTORY_JOB_DETAIL_COPY_DIR_COUNT +`</th>`+
                                        `<th style="width: 7%;"> `+ LANG.UI_FILE_PASSFILE_LISTS +`</th>`+
                                        `<th style="width: 12%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_COPY_DATA_LIST +`</th>`+
                                        `<th style="width: 5%;"> `+ LANG.UI_PUBLIC_DESCRIPTION +`</th>`+
                                    `</thead>`;
                            tbody = `<tbody>`;
                            for (var i = 0; i< data.list.copy_list.length; i++) {
                                var total = parseInt(data.list.copy_list[i].total_pass_dir_number) + parseInt( data.list.copy_list[i].total_pass_file_number);
                                var path = data.list.copy_list[i].source + '->' + data.list.copy_list[i].target;
                                if(total > 0) {
                                    elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                                    style = 'style="color: #0fbf98;pointer-events:unset;"'
                                }else {
                                    elecontent = LANG.UI_PUBLIC_NOTHING;
                                    style = 'style="color: black;pointer-events:none;"'
                                }
                                tbody += `<tr>` +
                                            `<td> `+ data.list.source_name +`</td>`+
                                            `<td> `+ data.list.target_name +`</td>`+
                                            `<td><span class="file-copy-list-detail path-no-border">`+ getWildcardDes(data.list.wildcard_list) +`</span></td>`+
                                            `<td><span class="file-copy-list-detail path-no-border">`+ getTimeRangeDes(data.list.time_range_list) +`</span></td>`+
                                            `<td> `+ data.list.copy_list[i].file_scan_count +`</td>`+
                                            `<td> `+ data.list.copy_list[i].dir_scan_count +`</td>`+
                                            `<td> `+ data.list.copy_list[i].file_count +`</td>`+
                                            `<td> `+ data.list.copy_list[i].dir_count +`</td>`+
                                            //跳过文件下载
                                            `<td>` + `<a data-toggle="drawer" data-target="#passFileDrawer" value="`+ path +`" class="downloadPassFile" `+ style +`>`+ elecontent +`<span class="display-none">`+ data.list.copy_list[i].passfile_file_path +`</span></a>` + `</td>` +
                                            `<td class="path-no-border"> `+ $('<div>').text(path).html() +`</td>`+
                                            `<td> `+ data.list.copy_list[i].error_code +`</td>`+
                                        `</tr>`;
                            }
                            tbody += `</tbody>`;
                            html = `<table>`+ thead + tbody +`</table>`;
                            break;
                        case 28:
                            //接收表格数据生成二级表格样式
                            var k8sdatas = res.data.list;
                            var initSecondTable = function(datas){
                                if(datas['data'] == "" || datas['data'] == undefined || datas['data'] == null){
                                    return LANG.UI_KUBE_NO_DATA;
                                }
                                //-----------pvc
                                var tableContentPvc = "";
                                var tableListPvc = datas['data']['pvc_list'];
                                $.each(tableListPvc, function (index, value) {
                                    tableContentPvc += "<tr>" +
                                        "<td>"+value.pvc_name+"</td>" +
                                        "<td>"+value.pvc_namespace+"</td>" +
                                        "<td>"+value.volume_mode+"</td>" +
                                        "<td>"+value.pvc_size_des+"</td>" +
                                        "<td>"+value.status+"</td>" +
                                        "<td width='15%'>"+value.status_desc+"</td>" +
                                        "</tr>";
                                });
                                if (tableContentPvc == "") {
                                    tableContentPvc = "<tr><td colspan='4' class='text-center'>" + LANG.UI_KUBE_NO_DATA + "</td></tr>";
                                }

                                //开始封装表格
                                var pvcTable = "<table>" +
                                    "<tr>" +
                                    "<th>" + LANG.UI_KUBE_PVC_PERSISTENT_VOLUME + "</th>" +
                                    "<th>" + LANG.UI_k8S_NAMESPACE + "</th>" +
                                    "<th>" + LANG.UI_KUBE_STORAGE_TYPE + "</th>" +
                                    "<th>" + LANG.UI_KUBE_CAPACITY_SIZE + "</th>" +
                                    "<th>" + LANG.UI_VISUAL_RESULT + "</th>" +
                                    "<th>" + LANG.UI_PUBLIC_DESCRIPTION + "</th>" +
                                    "</tr>" +tableContentPvc+
                                    "</table>";
                                //-----------资源
                                var tableContentResource = "";
                                var tableListResource = datas['data']['resource_list'];
                                $.each(tableListResource, function (index, value) {
                                    tableContentResource += "<tr>" +
                                        "<td>"+value.resource_name+"</td>" +
                                        "<td>"+value.resource_object_type+"</td>" +
                                        "<td>"+value.resource_namespace+"</td>" +
                                        "<td>"+value.status+"</td>" +
                                        "<td width='15%'>"+value.status_desc+"</td>" +
                                        "</tr>";
                                });
                                if (tableContentResource == "") {
                                    tableContentResource = "<tr><td colspan='4' class='text-center'>" + LANG.UI_KUBE_NO_DATA + "</td></tr>";
                                }


                                //开始封装表格
                                var resourceTable = "<table>" +
                                    "<tr>" +
                                    "<th>" + LANG.UI_STORAGE_NAME + "</th>" +
                                    "<th>" + LANG.UI_SEARCH_TYPE + "</th>" +
                                    "<th>" + LANG.UI_KUBE_NAMESPACE + "</th>" +
                                    "<th>" + LANG.UI_VISUAL_RESULT + "</th>" +
                                    "<th>" + LANG.UI_PUBLIC_DESCRIPTION + "</th>" +
                                    "</tr>" +tableContentResource+
                                    "</table>";
                                // Metronic.unblockUI('#history_table');
                                return pvcTable+resourceTable;
                            }
                            html = initSecondTable(k8sdatas);
                            break;
                        case 12:
                        var task_type = data.info.job_type;
                        var tableList = data.list;
                        var tableContent = '';
                        var time_type_des = LANG.UI_MICROSOFT365_RECOVERY_TYPE;
                        var time_type = ['--',LANG.UI_DB_CDP_DETAILS_TIME_METHOD_LATEST_TIMEPOINT,LANG.UI_DB_CDP_DETAILS_TIME_METHOD_SPECIFIED_TIMEPOINT,LANG.UI_DB_CDP_DETAILS_TIME_METHOD_SPECIFIED_SCN];
                            $.each(tableList, function (index, value) {
                                tableContent += "<tr>" +
                                    "<td>"+value.source_app_info_name+"</td>" +
                                    "<td>"+value.target_app_info_name+"</td>";
                                if(task_type == CONF.TASK_TYPE.CDP_DB_BACKUP){
                                    tableContent += "<td>"+value.failback_app_info_name+"</td>";
                                }
                                tableContent += "<td>"+value.task_running_stage+"</td>";
                                var selectedUser = '';
                                for (const select_copy_users of value.select_copy_users) {
                                    if (select_copy_users.pdb_name !== '') {
                                        selectedUser += select_copy_users.pdb_name + ':';
                                    }
                                    selectedUser += select_copy_users.user_list.map(user => user.name).join(', ');
                                    selectedUser += '<br>';
                                }
                                tableContent += "<td class='w-200px word-break-break-all'>"+selectedUser+"</td>" +
                                    "<td>"+value.app_tablespace_dir+"</td>" +
                                    "<td>"+value.completed_dict_num+"</td>" +
                                    "<td>"+value.completed_table_num+"</td>" +
                                    "<td>"+value.full_sync_completed_size+"</td>" +
                                    "<td>"+value.sync_redo_log_size+"</td>" +
                                    "<td>"+value.log_sync_completed_size+"</td>";
                                if(value.timepoint_type !== null){
                                    var timepoint_type_des = '';
                                    if(value.timepoint_type == 2 || value.timepoint_type == 3){
                                        timepoint_type_des = value.target_time ? '(' + value.target_time +')' : '(' + value.target_scn +')';
                                    }
                                    tableContent += "<td>"+time_type[value.timepoint_type]+ timepoint_type_des +"</td>";
                                }else if (value.timepoint_type === null){
                                    tableContent += "<td>"+"--"+"</td>";
                                }
                                tableContent += "<td>"+value.task_status+"</td>" +
                                    "<td>"+value.description+"</td>" +
                                    "</tr>";
                            });
                            //开始封装表格
                            html += "<table>" +
                                "<tr>" +
                                "<th>"+LANG.UI_JOB_DETAIL_SOURCE_DB_INSTANCE+"</th>" +
                                "<th>"+LANG.UI_JOB_DETAIL_TARGET_DB_INSTANCE+"</th>";
                                if(task_type == CONF.TASK_TYPE.CDP_DB_BACKUP){
                                    html += "<th>"+LANG.UI_JOB_DETAIL_TAKEOVER_FAILBACK_DB_INSTANCE+"</th>";
                                    time_type_des = LANG.UI_DB_CDP_DETAILS_TIME_METHOD_TAKEOVER;
                                };
                            html += "<th>"+LANG.UI_DB_CDP_DETAILS_RUNNING_STAGE+"</th>" +
                                "<th>"+LANG.UI_DB_CDP_DETAILS_SELECTED_USER+"</th>" +
                                "<th>"+LANG.UI_DB_CDP_DETAILS_APP_TABLESPACE_DIR+"</th>" +
                                "<th>"+LANG.UI_DB_CDP_DETAILS_COMPLETED_DICT_NUM+"</th>" +
                                "<th>"+LANG.UI_DB_CDP_DETAILS_COMPLETED_TABLE_NUM+"</th>" +
                                "<th>"+LANG.UI_DB_CDP_DETAILS_FULL_SYNC_COMPLETED_SIZE+"</th>" +
                                "<th>"+LANG.UI_DB_CDP_DETAILS_SYNC_REDO_LOG_SIZE+"</th>" +
                                "<th>"+LANG.UI_DB_CDP_DETAILS_LOG_SYNC_COMPLETED_SIZE+"</th>" +
                                "<th>"+time_type_des+"</th>" +
                                "<th>"+LANG.UI_VISUAL_RESULT+"</th>" +
                                "<th>"+LANG.UI_PUBLIC_DESCRIPTION+"</th>" +
                                "</tr>" +tableContent;
                            break;
                    }
                }
            }

            html += '</table>'
            $(element).append(html);

            // 数据验证报告
            if (data.info.job_type_value == CONF.TASK_TYPE.SURE_BACKUP) {
                //手动验证不显示验证报告
    			if(data.info.verify_type == 1){

        			return $(element).append(`<td>${LANG.UI_PUBLIC_NOTHING}</td>`);
        		}
    			var content = '<tr><td><button type="button" class="btn btn-sm green-haze verifyReport" id="'+row.job_uuid+'"> ' + LANG.UI_JOB_VERTIFY_REPORT + '</button>'+'</tr></td>';
	    		$(element).append(content);
            }

            $('.verifyReport').off().on('click', function(){
                var id = $(this).attr("id");
                getReport(id);
            });

            // 数据验证任务下载报告
            $('#downloadReport').off().on('click', function () {
                downloadReport();
            })

            //发送报告到配置邮箱
            $('#reportEmail').off().on('click', function(){
        	    sendReportEmail();
            });

            // Metronic.unblockUI(element);
            // Oracle的自定义配置文件
            if (data.info.module_type == CONF.MODULE_TYPE.DB) {
                if (typeof data.list.resource_limiting_node_config === 'undefined') {
                    if (parseInt(data.list[0].db_type) === CONF.DB_TYPE.ORACLE) {
                        $('#oracleRecoveryContentBtn').unbind('click').on('click', function () {
                            $('#oracleRecoveryContentDrawer').drawer('show');
                        });
                    }
                }
            }

            //exchange跳过数据下载
            $('.downloadPassData').click(function () {
                var node_uuid = data.list.node_uuid;
                var storage_uuid = data.list.storage_uuid;
                var read_file_name = data.list.pass_item_path;
                var pass_item_file_size =  data.list.pass_item_file_size;
                if (pass_item_file_size == 0) {
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_SKIP_FILE_DOWNLOAD,LANG.UI_MICROSOFT365_FILE_NOT_EXIST);
                    return;
                }
                window.location.href = '/api/v1/exchange/jobs/download' + '?node_uuid=' + node_uuid + '&read_file_name=' + read_file_name + '&storage_uuid=' + storage_uuid + '&pass_item_file_size=' + pass_item_file_size + '&x-api-version=1.0-rev0';
            });

            //文件nas跳过文件下载
            $("a.downloadPassFile").off().click(function () {
                var agent_uuid = '';
                var history_uuid = data.list.history_uuid;

                switch (data.info.job_type) {
                    case 1: //备份
                        //跳过文件详情模态框表格里面的数据
                        if (data.info.module_type == 3) {
                            data.list.list.forEach(item => {
                                agent_uuid = $(this).attr('value');
                                if (item.all_scan_file_count == undefined && item.agent_uuid == $(this).attr('value')) {
                                    $('.passdetailTr').html('<td>' + item.total_pass_number + '</td><td>--</td><td>' + item.total_pass_dir_number + '</td><td>--</td><td>0</td><td>0.00%</td>');
                                    $('.passreasonTr').html('<td>--</td><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td>')
                                } else if (item.agent_uuid == $(this).attr('value')) {
                                    var total = parseInt(item.dir_count) + parseInt(item.all_scan_file_count);
                                    $('.passdetailTr').html('<td>' + item.total_pass_number + '</td><td>' +
                                        ((item.total_pass_number / total) * 100).toFixed(2) + '%</td><td>' +
                                        item.total_pass_dir_number + '</td><td>' +
                                        ((item.total_pass_dir_number / total) * 100).toFixed(2) + '%</td><td>' +
                                        data.list.passfile_number_limit + '</td><td>' +
                                        data.list.passfile_ratio_limit + '%</td>');
                                    $('.passreasonTr').html('<td>' + item.passfile_file_number_occupy + '</td><td>' +
                                        item.passfile_file_number_delete + '</td><td>' +
                                        item.passfile_file_number_reject + '</td><td>' +
                                        item.passfile_dir_number_reject + '</td><td>' +
                                        item.passfile_dir_number_delete + '</td><td>' +
                                        (parseInt(item.passfile_dir_number_other) + parseInt(item.passfile_file_number_other)) + '</td>');
                                }
                            });
                        }
                        $('#passFileModal').modal({
                            'width': "750px",
                            'height': "300px"
                        });
                        $('#downloadTxt').off().click(function () {
                            downloadPassFun(history_uuid, agent_uuid);
                        });
                        break;
                    case 2: //恢复
                        downloadPassFun(history_uuid, "");
                        break;
                    default://复制
                        var passfile_file_path = $(this).find(":first-child")[0].innerText;
                        let node_uuid;
                        let pass_file_size = 0//默认一个
                        data.list.copy_list.forEach(item=> {
                            let thisPathDes = item.source + '->' + item.target;
                            let passfile_number_limit = data.list.passfile_number_limit;
                            let passfile_ratio_limit = data.list.passfile_ratio_limit;
                            path = passfile_file_path;
                            node_uuid = item.node_uuid;
                            if(thisPathDes == $(this).attr('value')) {
                                pass_file_size = item.passfile_file_size ?? 1024;
                                $('#file_skip_num').html(item.total_pass_file_number);
                                $('#file_skip_radio').html(item.file_count == 0 ? '0.00%' : ((item.total_pass_file_number/item.file_count)*100).toFixed(2)+'%');
                                $('#dir_skip_num').html(item.total_pass_dir_number);
                                $('#dir_skip_radio').html(item.dir_count == 0? '0.00%' : ((item.total_pass_dir_number/item.dir_count)*100).toFixed(2)+'%');
                                $('#file_skip_alarm_num').html(passfile_number_limit);
                                $('#file_skip_alarm_radio').html( passfile_ratio_limit +'%');
                                $('#file_occupied_num').html(item.passfile_file_number_occupy);
                                $('#file_delete_num').html(item.passfile_file_number_delete);
                                $('#no_permission_file_num').html(item.passfile_file_number_reject);
                                $('#no_permission_dir_num').html(item.passfile_dir_number_reject);
                                $('#dir_delete_num').html(item.passfile_dir_number_delete);
                                $('#other_num').html(parseInt(item.passfile_dir_number_other)+ parseInt(item.passfile_file_number_other));
                            }
                        });
                        $('#downloadDrawer').off().click(function() {
                            downloadPassFileCopy(path,node_uuid,pass_file_size);
                        });
                        break;
                }
            });

            if (typeof data.list.resource_limiting_node_config === 'undefined') {
                // 注册打开错误详情
                $(element).find('.error-detail-link').off().on('click', function () {
                    $('#historyErrorDetailDrawer .portlet-body').html(``);
                    let index = $(this).data('index');
                    let agentUuid = $(this).data('agent-uuid');
                    let instanceName = $(this).data('instance-name');
                    let dbInfo = data.list[index];
                    let errorDetails = JSON.parse(dbInfo.error_details);
                    $('#historyErrorDetailDrawer').drawer('show');
                    $('#historyErrorDetailDrawer .drawer-header span.text').html(LANG.UI_PUBLIC_ERROR_DETAIL);
                    let errorDetail = null;
                    for (const key in errorDetails) {
                        let _errorDetail = errorDetails[key];
                        if (_errorDetail.agent_uuid === agentUuid && _errorDetail.object_name === instanceName) {
                            errorDetail = _errorDetail;
                            break;
                        }
                    }
                    let msg = ``;
                    // let title = ``;
                    if (errorDetail) {
                        for (const logError of errorDetail.info_list) {
                            msg += `${logError}<br>`;
                        }
                        // title = errorDetail.title + ' ------ ' + errorDetail.object_name
                    }
                    // $('#historyErrorDetailDrawer .drawer-header span.text').html(title);
                    $('#historyErrorDetailDrawer .portlet-body').html(msg);
                });

                // 注册数据库脚本内容
                for (const dbIndex in data.list) {
                    let dbInfo = data.list[dbIndex];
                    if (28 === parseInt(data.info.job_type)) {
                        let scriptContentList = [{
                            title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
                            script_list: dbInfo.before_task_script,
                        }, {
                            title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
                            script_list: dbInfo.after_task_script,
                        }];
                        registerScriptEvent(dbIndex, scriptContentList);
                    } else {
                        let scriptContentList = [{
                            title: LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT_SHOW,
                            script_list: dbInfo.before_task_script
                        }, {
                            title: LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT_SHOW,
                            script_list: dbInfo.after_task_script
                        }];
                        if (2 === parseInt(dbInfo.timepoint_recovery_type)) {
                            scriptContentList.push({
                                title: LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SHOW,
                                script_list: dbInfo.verification_script,
                            });
                        }
                        registerScriptEvent(dbIndex, scriptContentList);
                    }
                }

                // 注册数据库验证脚本
                if (
                    29 === parseInt(data.info.job_type) ||
                    64 === parseInt(data.info.job_type)
                ) {
                    if (2 === parseInt(data.list[0].timepoint_recovery_type)) {
                        $('.db-validate-report-chk').on('click', function () {
                            let dbIndex = $(this).data('db-index');
                            let dbInfo = data.list[dbIndex];
                            let reqData = {
                                job_uuid: row.job_uuid,
                                instance_name: dbInfo.instance_name,
                                db_name: dbInfo.db_name,
                                db_type: dbInfo.db_type,
                            };
                            Metronic.blockUI({target: '#dbValidateReportDrawer', animate: true});
                            pAjaxRequest(reqData, `/api/v1/db/jobs/validate_report`, 'GET', res => {
                                Metronic.unblockUI('#dbValidateReportDrawer');
                                if (!res.success) {
                                    UIToastr.showError(LANG.UI_PUBLIC_ERROR, res.msg);
                                    return;
                                }
                                $('#dbValidateReportDrawer').data('db-index', dbIndex).drawer('show');
                                $('#dbValidateReport').html(res.data.content);
                            });
                        });
                    }
                }

                // 注册数据库验证报告下载事件
                $('#dbValidateReportDrawer .drawer-footer button.download').off('click').on('click', () => {
                    let dbIndex = $('#dbValidateReportDrawer').data('db-index');
                    let dbInfo = data.list[dbIndex];
                    let reqData = {
                        job_uuid: row.job_uuid,
                        instance_name: dbInfo.instance_name,
                        db_name: dbInfo.db_name,
                        db_type: dbInfo.db_type,
                    };
                    Metronic.blockUI({target: '#dbValidateReportDrawer', animate: true});
                    pAjaxRequest(reqData, `/api/v1/db/jobs/validate_report`, 'POST', res => {
                        Metronic.unblockUI('#dbValidateReportDrawer');
                        if (!res.success) {
                            UIToastr.showError(LANG.UI_PUBLIC_ERROR, res.msg);
                            return;
                        }
                        window.location.href = res.data.report_url;
                    });
                });

                // 注册数据库验证报告发送事件
                $('#dbValidateReportDrawer .drawer-footer button.send-email').off('click').on('click', () => {
                    let dbIndex = $('#dbValidateReportDrawer').data('db-index');
                    let dbInfo = data.list[dbIndex];
                    let reqData = {
                        job_uuid: row.job_uuid,
                        instance_name: dbInfo.instance_name,
                        db_name: dbInfo.db_name,
                        db_type: dbInfo.db_type,
                    };
                    Metronic.blockUI({target: '#dbValidateReportDrawer', animate: true});
                    pAjaxRequest(reqData, `/api/v1/db/jobs/validate_report/email`, 'POST', res => {
                        Metronic.unblockUI('#dbValidateReportDrawer');
                        if (!res.success) {
                            if (!res.message) {
                                UIToastr.showError(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL_ERROR);
                            } else {
                                UIToastr.showError(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, res.message);
                            }
                            return;
                        }
                        UIToastr.showSuccess(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL_SUCCESS);
                    });
                });
            }
        })
    }

	/**
	 * 设置脚本内容
	 * @param {array} scriptContentList
	 */
	const renderScript = (dbIndex, scriptContentList) => {
		// 页面渲染
		let scriptContentHtml = `<div>`;
		let scriptContentHtmlList = [];
		for (const scriptTypeIndex in scriptContentList) {
			let scriptContentInfo = scriptContentList[scriptTypeIndex];
			let scriptHtmlList = [];
            if (Array.isArray(scriptContentInfo.script_list) && scriptContentInfo.script_list.length) {
                for (const scriptIndex in scriptContentInfo.script_list) {
                    let scriptInfo = scriptContentInfo.script_list[scriptIndex];
                    scriptHtmlList.push(`<a class="scriptItem" data-db-index="${dbIndex}" data-script-type-index="${scriptTypeIndex}" data-script-index="${scriptIndex}">${scriptInfo.script_name}</a>`);
                }
            } else {
                scriptHtmlList.push(LANG.UI_PUBLIC_NOTHING);
            }
			scriptContentHtmlList.push(`${scriptContentInfo.title}: ` + scriptHtmlList.join('、'));
		}
		scriptContentHtml += scriptContentHtmlList.join('<br>');
		scriptContentHtml += '</div>';
        return scriptContentHtml;
	};

	/**
	 * 注册脚本事件
	 */
	const registerScriptEvent = (dbIndex, scriptContentList) => {
		// 页面事件
		$(`.scriptItem[data-db-index="${dbIndex}"]`).off().on('click', function () {
			let scriptTypeIndex = $(this).data('script-type-index');
			let scriptIndex = $(this).data('script-index');
			let scriptInfo = scriptContentList[scriptTypeIndex].script_list[scriptIndex];
            setScriptDrawerContent(scriptInfo.script_name, scriptInfo.script_type, scriptInfo.error_code, scriptInfo.script_content);
		});
	};

    /**
     * 设置脚本抽屉内容
     * @param {string} title
     * @param {string} scriptType
     * @param {string} errorCode
     * @param {string} scriptContent
     */
    const setScriptDrawerContent = (title, scriptType, errorCode, scriptContent) => {
        $('#scriptContentDrawer .drawer-title .name').html(title);
        $('#scriptContentType').html(getScriptDesByType(scriptType));
        $('#scriptContent').text(scriptContent);
        $('#scriptContentResult').html(getScriptResult(errorCode));
        $('#scriptContentDrawer').drawer('show');
    };

	/**
	 * 获取脚本描述
	 * @param {string} scriptType
	 */
	const getScriptDesByType = scriptType => {
		scriptType = parseInt(scriptType);
		switch (scriptType) {
			case 1:
				return LANG.UI_PUBLIC_SCRIPT_TYPE1;
			case 2:
				return LANG.UI_PUBLIC_SCRIPT_TYPE2;
			case 3:
				return LANG.UI_PUBLIC_SCRIPT_TYPE3;
			case 4:
				return LANG.UI_PUBLIC_SCRIPT_TYPE4;
			case 5:
				return LANG.UI_PUBLIC_SCRIPT_TYPE5;
			case 6:
				return LANG.UI_PUBLIC_SCRIPT_TYPE6;
			case 7:
				return LANG.UI_PUBLIC_SCRIPT_TYPE7;
			case 8:
				return LANG.UI_PUBLIC_SCRIPT_TYPE8;
			case 9:
				return LANG.UI_PUBLIC_SCRIPT_TYPE9;
			default:
				return '--';
		}
	};

    /**
     * 获取脚本执行结果
     * @param {string} errorCode
     */
    const getScriptResult = errorCode => {
        errorCode = parseInt(errorCode);
        if (!errorCode) {
            return `<span class="label label-sm label-success ">${LANG.UI_PUBLIC_SUCCESS}</span>`;
        }
        return `<span class="label label-sm label-danger ">${LANG.UI_PUBLIC_FAILED}</span>`;
    };

    //组织列表展开展示的恢复列表
    var showSrcData = function (showArr) {
        var organizationNum = 0;
        var userNum = 0;
        var groupNum = 0;
        var dirNum = 0;
        var emailNum = 0;
        var calenderNum = 0;
        var contactNum = 0;
        var taskNum = 0;
        showArr.forEach(item => {
            switch (parseInt(item)) {
                case 10000:
                    organizationNum++;
                    break;
                case 1000:
                    userNum++;
                    break;
                case 1001:
                    groupNum++;
                    break;
                case 100:
                    dirNum++;
                    break;
                case 0:
                    emailNum++;
                    break;
                case 1:
                    calenderNum++;
                    break;
                case 2:
                    contactNum++;
                    break;
                case 3:
                    taskNum++;
                    break;
            }
        });
        var des = '';
        if (organizationNum != 0) {
            des += LANG.UI_MICROSOFT365_ORGANIZATION + organizationNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (userNum != 0) {
            des += LANG.UI_MICROSOFT365_USER + userNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (groupNum != 0) {
            des += LANG.UI_MICROSOFT365_USER_GROUP + groupNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (dirNum != 0) {
            des += LANG.UI_MICROSOFT365_DIR + dirNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (emailNum != 0) {
            des += LANG.UI_MICROSOFT365_EMAIL + emailNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (calenderNum != 0) {
            des += LANG.UI_MICROSOFT365_CALENDAR + calenderNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (contactNum != 0) {
            des += LANG.UI_MICROSOFT365_CONTACTS + contactNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (taskNum != 0) {
            des += LANG.UI_MICROSOFT365_TASK + taskNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        return des;
    }

    const downloadPassFun = function (history_uuid, agent_uuid) {
        window.location.href = '/api/v1/s3/download_pass' + '?history_uuid=' + history_uuid + '&fsnodeuuid=' + fsnodeuuid + '&agent_uuid=' + agent_uuid + '&x-api-version=1.0-rev0';
    }

    var downloadPassFileCopy = function(path,node_uuid,pass_file_size) {
        var data = {
            'node_uuid':node_uuid,
            'path':path,
            'pass_file_size':pass_file_size
        };
        Metronic.blockUI({target: '#passFileDrawer',animate: true});
        pAjaxRequest(data, "/api/v1/filecopy/download_pass", "GET", function (result) {
            Metronic.unblockUI('#passFileDrawer');
            window.location.href = '/api/v1/filecopy/download_pass' + '?node_uuid=' + node_uuid + '&pass_file_size=' + pass_file_size + '&path=' + path + '&x-api-version=1.0-rev0';
        });
    }

    const convertSpaceCharToSign = (text) => {
        return text.split('\n')
            .map(line => line.replace(/ /g, ' ').replace(/ /g, '&nbsp;'))
            .join('<br>');
    }

    /**
     * 获取数据库任务详情
     * @param {{info: {job_type, level, module_type}, list: []}} data
     * @return string
     */
    const getDBJobDetails = function (data) {
        var getBackupDetails = function (data) {
            let thead = `
                <thead>
                    <th width="12%">${LANG.UI_DB_NAME}</th>
                    <th width="10%">${LANG.UI_SEARCH_TASK_TYPE}</th>
                    <th width="14%">${LANG.UI_DB_PATH}</th>
                    <th width="10%">${LANG.UI_JOB_HIS_TOTAL_SIZE}</th>
                    <th width="12%">${LANG.UI_PUBLIC_TRANSFER_SIZE}</th>
                    <th width="12%">${LANG.UI_JOB_HIS_REAL_SIZE}</th>
                    <th width="10%">${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</th>
                    <th width="10%">${LANG.UI_VISUAL_RESULT}</th>
                    <th width="10%">${LANG.UI_PUBLIC_DESCRIPTION}</th>
                </thead>
            `;
            let tbody = `<tbody class="db-protected-table-detail">`;
            for (let i = 0; i < data.list.length; i++) {
                let dbInfo = data.list[i];
                let total_size;
                if (dbInfo.total_size === "0") {
                    total_size = 0
                } else {
                    total_size = storageCalculateSize(dbInfo.total_size);
                }
                if (!i % 2) {
                    tbody += '<tr role="row" class="odd">';
                } else {
                    tbody += '<tr role="row" class="even">';
                }
                tbody += `<td>${dbInfo.db_name}</td>`;
                tbody += `<td>${dbInfo.backup_mode}</td>`;
                tbody += `<td>${dbInfo.dir_path}</td>`;
                tbody += `<td>${total_size}</td>`;
                tbody += `<td>${dbInfo.transport_size}</td>`;
                tbody += `<td>${dbInfo.real_size}</td>`;
                // 脚本配置
                if (
                    (Array.isArray(dbInfo.before_task_script) && dbInfo.before_task_script.length > 0) ||
                    (Array.isArray(dbInfo.after_task_script) && dbInfo.after_task_script.length > 0)
                ) {
                    let beforeTaskScript = [];
                    let afterTaskScript = [];
                    if (Array.isArray(dbInfo.before_task_script) && dbInfo.before_task_script.length > 0) {
                        beforeTaskScript = dbInfo.before_task_script;
                    }
                    if (Array.isArray(dbInfo.after_task_script) && dbInfo.after_task_script.length > 0) {
                        afterTaskScript = dbInfo.after_task_script;
                    }
                    let scriptContentList = [{
                        title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
                        script_list: beforeTaskScript
                    }, {
                        title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
                        script_list: afterTaskScript
                    }];
                    tbody += `<td>${renderScript(i, scriptContentList)}</td>`;
                } else {
                    tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
                }
                tbody += `<td>${dbInfo.task_status}</td>`;
                if (dbInfo.error_details) {
                    try {
                        let errorDetails = JSON.parse(dbInfo.error_details);
                        if (Array.isArray(errorDetails) && errorDetails.length) {
                            let errorDetailFlag = false;
                            for (const errorDetail of errorDetails) {
                                if (errorDetail.agent_uuid == dbInfo.agent_uuid && errorDetail.object_name == dbInfo.instance_name) {
                                    errorDetailFlag = true;
                                    break;
                                }
                            }
                            if (errorDetailFlag) {
                                tbody += `<td>${dbInfo.error_code}, 
                                    <u class="text-success error-detail-link" style="cursor: pointer" data-index="${i}"
                                        data-agent-uuid="${dbInfo.agent_uuid}" data-instance-name="${dbInfo.instance_name}">
                                        ${LANG.UI_PUBLIC_ERROR_DETAIL_LINK}
                                    </u>
                                </td>`;
                            } else {
                                tbody += `<td>${dbInfo.error_code}</td>`;
                            }
                        } else {
                            tbody += `<td>${dbInfo.error_code}</td>`;
                        }
                    } catch (e) {
                        tbody += `<td>${dbInfo.error_code}</td>`;
                    }
                } else {
                    tbody += `<td>${dbInfo.error_code}</td>`;
                }
                tbody += '</tr>';
            }
            tbody += '</tbody>';
            return thead + tbody;
        }

        var getRecoveryDetails = function (data) {
            let thead = `
                <thead>
                    <th width="8%">${LANG.UI_JOB_HIS_BAK_TIMEPOINT}</th>
                    <th width="10%">${LANG.UI_JOB_HIS_SRC_PATH}</th>
                    <th width="10%">${LANG.UI_JOB_HIS_DES_PATH}</th>
            `;
            let recoveryMode = parseInt(data.list[0].recovery_mode);
            let dbType = parseInt(data.list[0].db_type)
            thead += `<th width="8%">${LANG.UI_DB_RECOVERY_TYPE}</th>`;

            switch (recoveryMode) {
                case 1: // 原数据库恢复
                    break;
                case 2: // 新建数据库恢复
                    if (dbType !== CONF.DB_TYPE.SAPHANA) {  // SAP HANA新建恢复发没有数据目录和日志目录
                        thead += `<th width="8%">${LANG.UI_DB_RECOVERY_DATA_FILE_PATH}</th>`;
                        thead += `<th width="8%">${LANG.UI_DB_RECOVERY_LOG_FILE_PATH}</th>`;
                    }
                    break;
                case 3: // 指定文件夹恢复
                    if (dbType === CONF.DB_TYPE.MONGODB) {
                        thead += `<th width="8%">${LANG.UI_DB_RECOVERY_MONGODB_INSTANCE_PATH}</th>`;
                    } else {
                        thead += `<th width="8%">${LANG.UI_DB_RECOVERY_DIRECT_FILE_PATH}</th>`;
                        if (
                            dbType === CONF.DB_TYPE.POSTGRE ||
                            dbType === CONF.DB_TYPE.ANTDB ||
                            dbType === CONF.DB_TYPE.KINGBASE ||
                            dbType === CONF.DB_TYPE.UXDB ||
                            dbType === CONF.DB_TYPE.HIGHGO ||
                            dbType === CONF.DB_TYPE.OPENGAUSS ||
                            dbType === CONF.DB_TYPE.VASTBASE
                        ) {
                            thead += `<th width="8%">${LANG.UI_DB_RECOVERY_CUSTOM_ARCHIVE_DIR}</th>`;
                        }
                    }
                    break;
                case 4: // 重定向恢复
                    thead += `<th width="8%">${LANG.UI_DB_RECOVERY_REDIRECT_PATH}</th>`;
                    break;
                case 5: // 导出恢复
                    thead += `<th width="8%">${LANG.UI_DB_RECOVERY_EXPORT_PATH}</th>`;
                    break;
                case 6: //  pdb数据库恢复
                    thead += `<th width="8%">${LANG.UI_DB_RECOVERY_PDB_FILE_PATH}</th>`;
                    break;
                case 7: // 还原归档日志恢复
                    thead += `<th width="8%">${LANG.UI_DB_RESTORE_ARCHIVELOG_FOLDER}</th>`;
                    thead += `<th width="8%">${LANG.UI_DB_RESTORE_ARCHIVELOG_RANGE_TIME}</th>`;
                    break;
                case 8:  // 完全恢复
                    break;
                case 9:  // 不完全恢复
                    break;
                default:
                    break;

            }

            if (dbType === CONF.DB_TYPE.SAPHANA) {  // SAP HANA显示恢复时间
                if ((data.list[0].recovery_time_flag) === 3) {
                    thead += `<th width="8%">${LANG.UI_DB_RECOVERY_RECOVERY_TIME}</th>`;
                }
            }
            if (dbType !== CONF.DB_TYPE.ORACLE && dbType !== CONF.DB_TYPE.SAPHANA) {
            //如果有日志回滚时间
            if (data.list[0].log_rollback_time !== "0000-00-00 00:00:00") {
                thead += `<th width="10%">${LANG.UI_DB_RECOVERY_LOG_ROLL_BACK_TIME}</th>`;
            }
            }
            // Oracle的PFile配置，自定义参数文件
            if (dbType === CONF.DB_TYPE.ORACLE && 7 !== recoveryMode && 5 !== recoveryMode) {
                if (data.list.length && parseInt(data.list[0].timepoint_recovery_type) === 1) {
                    thead += `<th width="10%">${LANG.UI_DB_RECOVERY_CONTENT}</th>`;
                }
            }
            thead += `<th width="10%">${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</th>`;
            if (2 === parseInt(data.list[0].timepoint_recovery_type)) {  // 恢复最新点有验证报告
                thead += `<th width="6%">${LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT}</th>`;
            }
            thead += `<th width="6%">${LANG.UI_VISUAL_RESULT}</th>`;
            thead += `<th width="10%">${LANG.UI_PUBLIC_DESCRIPTION}</th>`;
            thead += `</thead>`;

            let tbody = `<tbody class="db-protected-table-detail">`;
            for (let i = 0; i < data.list.length; i++) {
                let dbInfo = data.list[i];
                if (!i % 2) {
                    tbody += '<tr role="row" class="odd">';
                } else {
                    tbody += '<tr role="row" class="even">';
                }
                tbody += `<td>${dbInfo.timepoint_des}</td>`
                tbody += `<td style="word-break: break-word;padding-right: 8px;">${dbInfo.dir_path}</td>`;
                tbody += `<td style="word-break: break-word;padding-right: 8px;">${dbInfo.des_dir_path}</td>`;
                tbody += `<td>${dbInfo.recovery_mode_des}</td>`;
                switch (recoveryMode) {
                    case 1:
                        break;
                    case 2:
                        if (dbType !== CONF.DB_TYPE.SAPHANA) {  // SAP HANA新建恢复发没有数据目录和日志目录
                            tbody += `<td>${dbInfo.data_file_path}</td>`;
                            tbody += `<td>${dbInfo.log_file_path}</td>`;
                        }
                        break;
                    case 3:
                        tbody += `<td>${dbInfo.data_file_path}</td>`;
                        if (
                            dbType === CONF.DB_TYPE.POSTGRE ||
                            dbType === CONF.DB_TYPE.ANTDB ||
                            dbType === CONF.DB_TYPE.KINGBASE ||
                            dbType === CONF.DB_TYPE.UXDB ||
                            dbType === CONF.DB_TYPE.HIGHGO ||
                            dbType === CONF.DB_TYPE.OPENGAUSS ||
                            dbType === CONF.DB_TYPE.VASTBASE
                        ) { // 自定义归档目录
                            tbody += `<td>${dbInfo.log_file_path}</td>`;
                        }
                        break;
                    case 4:
                        tbody += `<td>${dbInfo.log_file_path}</td>`;
                        break;
                    case 5:
                        tbody += `<td>${dbInfo.data_file_path}</td>`;
                        break;
                    case 6:
                        tbody += `<td>${dbInfo.data_file_path}</td>`;
                        break;
                    case 7:
                        tbody += `<td>${dbInfo.log_file_path}</td>`;
                        tbody += `<td>${dbInfo.log_restore_start_time} ~ ${dbInfo.log_restore_end_time}</td>`;
                        break;
                    case 8:
                        break;
                    case 9:
                        break;
                }
                if (dbType === CONF.DB_TYPE.SAPHANA) {  // SAP HANA显示恢复时间
                    if (parseInt(dbInfo.recovery_time_flag) === 3) {
                        tbody += `<td>${dbInfo.recovery_time}</td>`;
                    }
                }
                if (dbType !== CONF.DB_TYPE.ORACLE && dbType !== CONF.DB_TYPE.SAPHANA) {
                // 如果有日志回滚时间
                if (dbInfo.log_rollback_time !== "0000-00-00 00:00:00") {
                    tbody += `<td>${dbInfo.log_rollback_time}</td>`;
                }
                }
                // ORACLE的自定义参数文件
                if (dbType === CONF.DB_TYPE.ORACLE && 7 !== recoveryMode && 5 !== recoveryMode) {
                    if (1 === parseInt(dbInfo.timepoint_recovery_type)) {
                        try {
                        let oracleDetail = JSON.parse(dbInfo.detail)
                        // 判断这些文件的内容、目录是否为空
                        let showPfileFlag = !!oracleDetail.pfile_info.file_content.length;
                        let showListenerFlag = !!oracleDetail.listener_info.file_content.length;
                        let showTnsnamesFlag = !!oracleDetail.tnsnames_info.file_content.length;
                        let showSqlnetFlag = !!oracleDetail.sqlnet_info.file_content.length;
                        tbody += `<td>
                            <a class="colorgreen" id="oracleRecoveryContentBtn">${LANG.UI_DB_RECOVERY_CONTENT_VIEW}</a>
                        </td>`;

                        // 恢复内容
                        initOracleRecoveryContentTree(oracleDetail.recovery_content);

                        // 文件内容
                        if (showPfileFlag) {
                            $('.pfileDiv').show();
                            $('#pfile-content').html(convertSpaceCharToSign(oracleDetail.pfile_info.file_content));
                        } else {
                            $('.pfileDiv').hide();
                        }

                        if (showListenerFlag) {
                            $('.listenerDiv').show();
                            $('#listener-content').html(convertSpaceCharToSign(oracleDetail.listener_info.file_content));
                        } else {
                            $('.listenerDiv').hide();
                        }
                        if (showTnsnamesFlag) {
                            $('.tnsnamesDiv').show();
                            $('#tnsnames-content').html(convertSpaceCharToSign(oracleDetail.tnsnames_info.file_content));
                        } else {
                            $('.tnsnamesDiv').hide();
                        }
                        if (showSqlnetFlag) {
                            $('.sqlnetDiv').show();
                            $('#sqlnet-content').html(convertSpaceCharToSign(oracleDetail.sqlnet_info.file_content));
                        } else {
                            $('.sqlnetDiv').hide();
                        }
                        } catch (e) {
                            tbody += `<td>--</td>`
                        }
                    }
                }
                // 脚本配置
                if (
                    (Array.isArray(dbInfo.before_task_script) && dbInfo.before_task_script.length > 0) ||
                    (Array.isArray(dbInfo.after_task_script) && dbInfo.after_task_script.length > 0) ||
                    (Array.isArray(dbInfo.verification_script) && dbInfo.verification_script.length > 0)
                ) {
                    let beforeTaskScript = [];
                    let afterTaskScript = [];
                    if (Array.isArray(dbInfo.before_task_script) && dbInfo.before_task_script.length > 0) {
                        beforeTaskScript = dbInfo.before_task_script;
                    }
                    if (Array.isArray(dbInfo.after_task_script) && dbInfo.after_task_script.length > 0) {
                        afterTaskScript = dbInfo.after_task_script;
                    }
                    let scriptContentList = [{
                        title: LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT_SHOW,
                        script_list: beforeTaskScript
                    }, {
                        title: LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT_SHOW,
                        script_list: afterTaskScript
                    }];
                    if (2 === parseInt(dbInfo.timepoint_recovery_type)) {
                        let verificationScript = [];
                        if (Array.isArray(dbInfo.verification_script) && dbInfo.verification_script.length > 0) {
                            verificationScript = dbInfo.verification_script;
                        }
                        scriptContentList.push({
                            title: LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SHOW,
                            script_list: verificationScript,
                        });
                    }
                    tbody += `<td>${renderScript(i, scriptContentList)}</td>`;
                } else {
                    tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
                }
                // 验证报告
                if (2 === parseInt(dbInfo.timepoint_recovery_type)) {
                    if (!dbInfo.error_code) {
                        tbody += `<td>
                            <a class="colorgreen db-validate-report-chk" data-db-index="${i}">${LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_VIEW}</a>
                        </td>`;
                    } else {
                        tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
                    }
                }
                tbody += `<td>${dbInfo.task_status}</td>`;
                if (dbInfo.error_details) {
                    try {
                        let errorDetails = JSON.parse(dbInfo.error_details);
                        if (Array.isArray(errorDetails) && errorDetails.length > 0) {
                            let errorDetailFlag = false;
                            for (const errorDetail of errorDetails) {
                                if (errorDetail.agent_uuid == dbInfo.agent_uuid && errorDetail.object_name == dbInfo.instance_name) {
                                    errorDetailFlag = true;
                                    break;
                                }
                            }
                            if (errorDetailFlag) {
                                tbody += `<td>${dbInfo.error_code}, 
                                    <u class="text-success error-detail-link" style="cursor: pointer" data-index="${i}"
                                        data-agent-uuid="${errorDetails[0].agent_uuid}" data-instance-name="${errorDetails[0].object_name}">
                                        ${LANG.UI_PUBLIC_ERROR_DETAIL_LINK}
                                    </u>
                                </td>`;
                            } else {
                                tbody += `<td>${dbInfo.error_code}</td>`;
                            }
                        } else {
                            tbody += `<td>${dbInfo.error_code}</td>`;
                        }
                    } catch (e) {
                        tbody += `<td>${dbInfo.error_code}</td>`;
                    }
                } else {
                    tbody += `<td>${dbInfo.error_code}</td>`;
                }
                tbody += `</tr>`;
            }
            tbody += `</tbody>`;
            return thead + tbody;
        }

        if (!data.list) {
            return LANG.UI_PUBLIC_NOTHING;
        }
        if (typeof data.list.resource_limiting_node_config !== 'undefined') { // 存在 resource_limiting_node_config 属性，表示由资源限制触发过任务失败
            let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
            return `
            <thead>
                <th>${LANG.UI_RESOURCE_LIMIT_CONFIG}</th>
                <th>${LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT}</th>
                <th>${LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD}</th>
            </thead>
            <tbody class="db-protected-table-detail">
                <tr>
                    <td>${LANG.UI_PUBLIC_ON}</td>
                    <td>${resourceLimitingNodeConfig[0].max_task_running_num}</td>
                    <td>${getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec)}</td>
                </tr>
            </tbody>
            `;
        } else {
            if (28 === parseInt(data.info.job_type)) { // 数据库备份
                return getBackupDetails(data);
            } else if (
                29 === parseInt(data.info.job_type) ||
                64 === parseInt(data.info.job_type)
            ) { // 数据库恢复
                return getRecoveryDetails(data);
            } else {
                return '';
            }
        }
    };

    /**
     * 获取定时整机任务详情
     * @param {*} data
     */
    const getTimingCompleteJobDetails = (data) => {
        let jobType = data.info.job_type;
        let resultHtml = '';

        switch (jobType) {
            case CONF.TASK_TYPE.OS_BACKUP: // 定时整机备份
                if (data.list.resource_limiting_node_config) { // 存在 resource_limiting_node_config 属性，表示由资源限制触发过任务失败
                    resultHtml += `
                        <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>`;

                    let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                    resultHtml +=
                    `<tr>
                        <td>` + LANG.UI_PUBLIC_ON + `</td>
                        <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                        <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                    </tr>`;
                } else { // 不存在 resource_limiting_node_config 属性，则展示之前的详情信息
                    resultHtml += `
                    <th>` + LANG.UI_OS_DETAILS_HOST_NAME + `</th>
                    <th>` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                    <th>` + LANG.UI_PUBLIC_START_TIME + `</th>
                    <th>` + LANG.UI_PUBLIC_END_TIME + `</th>
                    <th>` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                    <th>` + LANG.UI_OS_HOST_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                    <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                    <th>` + LANG.UI_VISUAL_RESULT + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                    for (let i = 0; i < data.list.length; i++) {
                        let volList = "--"; //分区列表名称
                        let vol = data.list[i].volume_list;
                        if (vol == "" || vol == undefined) {
                            volList = "--";
                        } else {
                            for (let x = 0; x < vol.length; x++) {
                                volList += vol[x] + "\n";
                            }
                        }

                        resultHtml += `<tr>
                            <td title="` + volList + `" style="width: 10%;overflow-wrap: anywhere;padding-right: 8px;">` + data.list[i].display_name + `</td>
                            <td width="10%">` + data.list[i].backup_mode + `</td>
                            <td width="10%">` + data.list[i].start_transfer_time + `</td>
                            <td width="10%">` + data.list[i].end_transfer_time + `</td>
                            <td width="10%">` + data.list[i].transfer_speed + `</td>
                            <td width="5%">` + data.list[i].os_size + `</td>
                            <td width="10%">` + data.list[i].os_valid_size + `</td>
                            <td width="10%">` + data.list[i].transport_size + `</td>
                            <td width="10%">` + data.list[i].write_size + `</td>
                            <td width="10%">` + data.list[i].task_status + `</td>
                            <td width="10%">` + data.list[i].error_code_des + `</td>
                        </tr>`;
                    }
                }
                break;
            case CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION: // 操作系统在线迁移
                resultHtml += `
                    <th>` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                    <th>` + LANG.UI_OS_MIGRATE_ORIGINAL_HOST + `</th>
                    <th>` + LANG.UI_OS_MIGRATE_TARGET_HOST + `</th>
                    <th>` + LANG.UI_OS_HOST_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                    <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                    <th>` + LANG.UI_VISUAL_RESULT + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                for (let i = 0; i < data.list.length; i++) {
                    resultHtml += `<tr>
                        <td>` + data.list[i].timepoint + `</td>
                        <td>` + data.list[i].sourcehost + `</td>
                        <td>` + data.list[i].targethost + `</td>
                        <td>` + data.list[i].os_size + `</td>
                        <td>` + data.list[i].transport_size + `</td>
                        <td>` + data.list[i].write_size + `</td>
                        <td>` + data.list[i].task_status + `</td>
                        <td>` + data.list[i].error_code_des + `</td>
                    </tr>`;
                }
                break;
            case CONF.TASK_TYPE.OS_RECOVERY: // 整机定时恢复
            case CONF.TASK_TYPE.PLATFORM_RECOVERY: // 整机跨平台恢复
                resultHtml += `
                    <th>` + LANG.UI_OS_DETAILS_HOST_NAME + `</th>
                    <th>` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                    <th>` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                    <th>` + LANG.UI_PUBLIC_START_TIME + `</th>
                    <th>` + LANG.UI_PUBLIC_END_TIME + `</th>
                    <th>` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                    <th>` + LANG.UI_OS_HOST_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                    <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                    <th>` + LANG.UI_VISUAL_RESULT + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                    for (let i = 0; i < data.list.length; i++) {
                        resultHtml += `<tr>
                            <td>` + data.list[i].display_name + `</td>
                            <td>` + data.list[i].timepoint + `</td>
                            <td>` + data.list[i].backup_mode + `</td>
                            <td>` + data.list[i].start_transfer_time + `</td>
                            <td>` + data.list[i].end_transfer_time + `</td>
                            <td>` + data.list[i].transfer_speed + `</td>
                            <td>` + data.list[i].os_size + `</td>
                            <td>` + data.list[i].os_valid_size + `</td>
                            <td>` + data.list[i].transport_size + `</td>
                            <td>` + data.list[i].write_size + `</td>
                            <td>` + data.list[i].task_status + `</td>
                            <td>` + data.list[i].error_code_des + `</td>
                        </tr>`;
                    }
                break;
            default:
                break;
        }

        return resultHtml;
    }

    /**
     * 获取实时整机任务详情
     * @param {*} data
     */
    const getRealTimeCompleteJobDetails = (data) => {
        let jobType = data.info.job_type;
        let resultHtml = '';
        let rowSpanNum = data.list.length; //相同的列只显示一个需要合并的行数

        switch (jobType) {
            case CONF.TASK_TYPE.VOL_CDP_BACKUP: // 实时整机备份
                resultHtml += `
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_VOL + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY_FINISHED + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VALID_DATA_FINISHED + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_AVERAGE_SPEED + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_MAP_VOL + `</th>
                    <th>` + LANG.UI_VISUAL_RESULT + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                $.each(data.list, function (k, v) {
                    let standbyHostInfo = "--";
                    if (!!v.standby_host_name) {
                        standbyHostInfo = v.standby_host_name;
                    } else if (v.standby_host_ip == "" && v.takeover_host_ip != "") {
                        standbyHostInfo = v.takeover_host_name;
                    }

                    let standbyMountInfo = "--";
                    if (v.standby_map_mount_point != "" && v.takeover_target_mount_point == "") {
                        standbyMountInfo = v.standby_map_mount_point;
                    } else if (v.standby_map_mount_point == "" && v.takeover_target_mount_point != "") {
                        standbyMountInfo = v.takeover_target_mount_point;
                    } else if (v.standby_map_mount_point != "" && v.takeover_target_mount_point != "") {
                        standbyMountInfo = v.standby_map_mount_point;
                    }

                    resultHtml += `<tr>`;

                    if (k == 0) {
                        // 第一条才添加合并的列，重复的行只显示一条
                        resultHtml += `<td rowspan="${rowSpanNum}">` + v.agent_name + `</td>`
                    }

                    resultHtml += `<td>` + v.vol_display_name + `</td>
                            <td>` + v.vol_size + `/` + v.vol_complete_size + `</td>
                            <td>` + v.real_size + `/ ` + v.real_complete_size + `</td>
                            <td>` + v.draw_speed + `</td>
                            <td>` + standbyHostInfo + `</td>
                            <td>` + standbyMountInfo + `</td>`

                    if (k == 0) {
                        resultHtml += `<td rowspan="${rowSpanNum}">` + v.task_status + `</td>
                                <td rowspan="${rowSpanNum}">` + v.description + `</td>`;
                    }

                    resultHtml += `</tr>`;
                });

                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY: // 实时整机恢复
                resultHtml += `<th>` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                            <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_DATA_CLIENT + `</th>
                            <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_VOL + `</th>
                            <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY + `</th>
                            <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME + `</th>
                            <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_SERVER + `</th>
                            <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_VOL + `</th>
                            <th>` + LANG.UI_VISUAL_RESULT + `</th>
                            <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                $.each(data.list, function (k, v) {
                    resultHtml += `<tr>`;

                    if (k == 0) {
                        // 第一条才添加合并的列，重复的行只显示一条
                        resultHtml += `<td rowspan="${rowSpanNum}">` + v.recovery_target_time + `</td>
                        <td rowspan="${rowSpanNum}">` + v.agent_name + `</td>`;
                    }

                    resultHtml += `<td>` + v.vol_display_name + `</td>
                    <td>` + v.vol_size + `/ ` + v.vol_complete_size + `</td>
                    <td>` + v.real_size + `/ ` + v.real_complete_size + `</td>`;

                    if (k == 0) {
                        // 第一条才添加合并的列，重复的行只显示一条
                        resultHtml += `<td rowspan="${rowSpanNum}">` + v.recovery_host_name + `</td>`; //恢复目标机
                    }

                    resultHtml += `<td>` + v.recovery_target_mount_point + `</td>`;

                    if (k == 0) {
                        // 第一条才添加合并的列，重复的行只显示一条
                        resultHtml += `<td rowspan="${rowSpanNum}">` + v.task_status + `</td>
                        <td rowspan="${rowSpanNum}">` + v.description + `</td>`;
                    }
                    resultHtml += `</tr>`;
                });

                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER: // 实时整机接管
                resultHtml += `
                    <th>` + LANG.UI_MOTION_TIMEPOINT + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_DATA_CLIENT + `</th>
                    <th>` + LANG.UI_PUBLIC_TAKEOVER + `</th>
                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER + `</th>
                    <th>` + LANG.UI_JOB_BACKUP_MOUNT_POINT + `</th>
                    <th>` + LANG.UI_VISUAL_RESULT + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                $.each(data.list, function (k, v) {
                    let mountPoint = v.takeover_target_mount_point;
                    let takeoverHostName = v.takeover_host_name;
                    if (mountPoint == '') {
                        mountPoint = LANG.UI_STORAGE_AUTO_ASSIGN;
                    }

                    if (!takeoverHostName) {
                        takeoverHostName = '--';
                        mountPoint = '--';
                    }

                    resultHtml += `<tr>`;

                    if (k == 0) {
                        // 第一条才添加合并的列，重复的行只显示一条
                        resultHtml += `<td rowspan="${rowSpanNum}">` + v.takeover_target_time + `</td>
                        <td rowspan="${rowSpanNum}">` + v.agent_name + `</td>`
                    }

                    resultHtml += `<td>` + v.vol_display_name + `</td>
                    <td>` + takeoverHostName + `</td>
                    <td>` + mountPoint + `</td>`;

                    if (k == 0) {
                        // 第一条才添加合并的列，重复的行只显示一条
                        resultHtml += `<td rowspan="${rowSpanNum}">` + v.task_status + `</td>
                        <td rowspan="${rowSpanNum}">` + v.description + `</td>`
                    }

                    resultHtml += `</tr>`;
                });

                break;
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION: // 实时整机复制
                resultHtml += `
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_VOL + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY_FINISHED + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VALID_DATA_FINISHED + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_AVERAGE_SPEED + `</th>
                        <th>` + LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_MAP_VOL + `</th>
                        <th>` + LANG.UI_VISUAL_RESULT + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                for (let i = 0; i < data.list.length; i++) {
                    resultHtml += `<tr>`;

                    let standbyHostInfo = "--";
        			if (!!data.list[i].standby_host_name) {
        				standbyHostInfo = data.list[i].standby_host_name;
        			} else if (data.list[i].standby_host_ip == "" && data.list[i].takeover_host_ip != "") {
        				standbyHostInfo = data.list[i].takeover_host_name;
        			}

        			let standbyMountInfo = "--";
        			if (data.list[i].standby_map_mount_point != "" && data.list[i].takeover_target_mount_point == "") {
        				standbyMountInfo = data.list[i].standby_map_mount_point;
        			} else if (data.list[i].standby_map_mount_point == "" && data.list[i].takeover_target_mount_point != ""){
        				standbyMountInfo = data.list[i].takeover_target_mount_point;
        			} else if (data.list[i].standby_map_mount_point != "" && data.list[i].takeover_target_mount_point != ""){
        				standbyMountInfo = data.list[i].standby_map_mount_point;
        			}

        			resultHtml += '<td>' + data.list[i].agent_name + '</td>';
        			resultHtml += '<td>' + data.list[i].vol_display_name + '</td>';
        			resultHtml += '<td>' + data.list[i].vol_size+'/'+ data.list[i].vol_complete_size + '</td>';
        			resultHtml += '<td>' + data.list[i].real_size+'/'+ data.list[i].real_complete_size + '</td>';
        			resultHtml += '<td>' + data.list[i].draw_speed + '</td>';
        			resultHtml += '<td>' + standbyHostInfo + '</td>';
        			resultHtml += '<td>' + standbyMountInfo + '</td>';
        			resultHtml += '<td>' + data.list[i].task_status + '</td>';
        			resultHtml += '<td>' + data.list[i].description + '</td>';
        			resultHtml += '</tr>';
                }

                break;
            default:
                break;
        }

        return resultHtml;
    }

    /**
     * 获取Oracle恢复内容树
     */
    const initOracleRecoveryContentTree = (recoveryContent) => {
        /**
         * @type {Array<Object>}
         */
        let nodes = [{
            id: 'oracle',
            pId: 0,
            name: recoveryContent.instance_name,
            title: recoveryContent.instance_name,
            isParent: true,
            open: true,
            nocheck: true,
            eventtype: 'oracle',
            icon: './img/db/oracle.png',
        }];

        // 数据库
        nodes.push({
            id: 'oracle_database',
            pId: 'oracle',
            name: LANG.UI_DB_RECOVERY_CONTENT_DB,
            title: LANG.UI_DB_RECOVERY_CONTENT_DB,
            isParent: true,
            open: true,
            eventtype: 'database',
            icon: './img/vm/host.png',
            nocheck: true,
        });

        // 实例
        nodes.push({
            id: 'oracle_database_instance',
            pId: 'oracle_database',
            name: recoveryContent.instance_name,
            title: recoveryContent.instance_name,
            isParent: true,
            open: true,
            eventtype: 'instance',
            icon: './img/vm/host.png',
            nocheck: true,
        });

        // 控制文件
        nodes.push({
            id: 'oracle_control',
            pId: 'oracle',
            name: LANG.UI_DB_RECOVERY_CONTENT_CONTROL_FILE,
            title: LANG.UI_DB_RECOVERY_CONTENT_CONTROL_FILE,
            isParent: false,
            eventtype: 'control_file',
            icon: './img/fs/wenjianjiaopen.png',
            nocheck: true,
        });

        // 配置文件
        nodes.push({
            id: 'oracle_config',
            pId: 'oracle',
            name: LANG.UI_DB_RECOVERY_CONTENT_CONFIG_FILE,
            title: LANG.UI_DB_RECOVERY_CONTENT_CONFIG_FILE,
            isParent: true,
            open: true,
            eventtype: 'config_file',
            icon: './img/fs/wenjianjiaopen.png',
            nocheck: true,
        });

        // 配置文件
        for (const configFileItem of recoveryContent.config_file_list) {
            let name = configFileItem.name;
            if (configFileItem.path) {
                name += '(' + configFileItem.path + ')';
            }
            nodes.push({
                id: 'oracle_config_' + name,
                pId: 'oracle_config',
                name: name,
                title: name,
                isParent: false,
                eventtype: 'config_file_item',
                icon: './img/fs/wenjian.png',
                nocheck: true,
            });
        }

        // 表空间
        for (const tablespaceInfo of recoveryContent.tablespace_list) {
			nodes.push({
				id: 'oracle_database_instance_' + tablespaceInfo.table_space_name,
				pId: 'oracle_database_instance',
				name: tablespaceInfo.table_space_name,
				title: tablespaceInfo.table_space_name,
				isParent: true,
				open: true,
				eventtype: 'table_space',
				icon: './img/platform/storage.png',
				nocheck: true,
			});
			if (!Array.isArray(tablespaceInfo.data_file_list)) {
				continue;
			}
			for (const datafileName of tablespaceInfo.data_file_list) {
				nodes.push({
					id: 'oracle_database_instance_' + tablespaceInfo.table_space_name + '_data_file_' + datafileName,
					pId: 'oracle_database_instance_' + tablespaceInfo.table_space_name,
					name: datafileName,
					title: datafileName,
					isParent: false,
					eventtype: 'data_file',
					icon: './img/fs/wenjian.png',
					nocheck: true,
				});
			}
        }

        $.fn.zTree.init($('#oracle-recovery-content-tree'), {
            data: {
                simpleData: {
                    enable: true,
                    idKey: 'id',
                    pIdKey: 'pId',
                    rootPId: 0,
                },
                key: {
                    title: 'title',
                },
            },
        }, nodes);
    };

    var getExcludeDirDes = function (excludeDir) {
        if (!excludeDir || excludeDir.length == 0) {
            return LANG.UI_PUBLIC_NOTHING;
        }
        var des = '';
        excludeDir.forEach(item => {
            switch (parseInt(item)) {
                case 1:
                    des += LANG.UI_MICROSOFT365_INBOX+';';
                    break;
                case 2:
                    des += LANG.UI_MICROSOFT365_DRAFT+';';
                    break;
                case 3:
                    des += LANG.UI_MICROSOFT365_SENT_EMAIL + ';';
                    break;
                case 4:
                    des += LANG.UI_MICROSOFT365_DELETED_EMAIL+ ';';
                    break;
                case 5:
                    des += LANG.UI_MICROSOFT365_SPAM+';';
                    break;
                case 6:
                    des += LANG.UI_MICROSOFT365_FILE+';';
                    break;
                case 7:
                    des += LANG.UI_MICROSOFT365_DIALOGUE_HISTORY+';';
                    break;
                case 100:
                    des += LANG.UI_MICROSOFT365_CALENDAR+';';
                    break;
                case 200:
                    des += LANG.UI_MICROSOFT365_CONTACTS+';';
                    break;
                case 300:
                    des += LANG.UI_MICROSOFT365_TASK+';';
                    break;
            };
        });
        return des;
    }

    /**
     * 构建 InterSystems Caché/IRIS以表空间方式恢复的详情
     * @param {{table_space_list: Array}} dbInfo
     * @param {Number} recoveryMode
     */
    var getCacheTableSpaceRecoveryDetails = function (dbInfo, recoveryMode) {
        let tbody = '';
        for (const i in dbInfo.table_space_list) {
            let tableSpace = dbInfo.table_space_list[i];
            if (!i % 2) {
                tbody += '<tr role="row" class="odd">';
            } else {
                tbody += '<tr role="row" class="even">';
            }
            tbody += `<td>${dbInfo.timepoint_des}</td>`
            tbody += `<td>${dbInfo.dir_path}/${tableSpace.table_name}</td>`
            if (2 === recoveryMode) {
                tbody += `<td>${dbInfo.agent_ip}/${dbInfo.instance_name}/${tableSpace.new_table_name}</td>`;
            } else {
                tbody += `<td>${dbInfo.agent_ip}/${dbInfo.instance_name}/${tableSpace.table_name}</td>`;
            }
            tbody += `<td>${dbInfo.recovery_mode_des}</td>`;
            if (2 === recoveryMode) {
                tbody += `<td>${tableSpace.data_file_path}</td>`;
            }
            // 如果有日志回滚时间
            if (tableSpace.log_rollback_time !== "0000-00-00 00:00:00") {
                tbody += `<td>${tableSpace.log_rollback_time}</td>`;
            }
            // 客户端并行数量
            tbody += `<td>${dbInfo.max_parallel_nums}</td>`;
            tbody += `<td>${dbInfo.task_status}</td>`;
            tbody += `<td>${dbInfo.error_code}</td>`;
            tbody += `</tr>`;
        }
        return tbody;
    }

         //获取历史任务报告
	var getReport = function(id){
        var p = {history_id:id, task_uuid: ''};
		pAjaxRequest(p, "/api/v1/verification/job/report" , "GET", function (d) {
			var data = d.data;
			_reportContent = data.report;
			_selectHistory = id;
			$('#reportContent').html(data.report);
			//使用本地图片链接，解决跨域图片获取的问题
			$('.verify-logo').attr("src", "/img/platform/logo-mini.png");
			$('.verify-clock').attr("src", "/img/platform/clock.png");
			$('#reportModal').modal({'width':'1050px', 'height':'600px'});
		});
    }

    //下载报告
	var downloadReport = function(){
		var element = $('#reportContent');
		var w = element.width();    // 获得该容器的宽
    	var h = element.height();    // 获得该容器的高
    	var offsetTop = element.offset().top;    // 获得该容器到文档顶部的距离
    	var offsetLeft = element.offset().left;    // 获得该容器到文档最左的距离
    	var canvas = document.createElement("canvas");
    	var abs = 0;
    	var win_i = $(window).width();    // 获得当前可视窗口的宽度（不包含滚动条）
    	var win_o = window.innerWidth;    // 获得当前窗口的宽度（包含滚动条）
    	if(win_o>win_i){
    		abs = (win_o - win_i)/2;    // 获得滚动条长度的一半
    	}
    	canvas.width = w;    // 将画布宽&&高放大
    	canvas.height = h;
    	var context = canvas.getContext("2d");
    	context.scale(1, 1);
    	context.translate(-offsetLeft-abs,-offsetTop);

    	element.css('background', "#fff");

    	html2canvas(element, {
            onrendered:function(canvas) {
                var contentWidth = canvas.width;
                var contentHeight = canvas.height;

                //一页pdf显示html页面生成的canvas高度;
                var pageHeight = contentWidth / 592.28 * 841.89;
                //未生成pdf的html页面高度
                var leftHeight = contentHeight;
                //pdf页面偏移
                var position = 0;
                //a4纸的尺寸[595.28,841.89]，html页面生成的canvas在pdf中图片的宽高
                var imgWidth = 595.28;
    			var imgHeight = 592.28/contentWidth * contentHeight;

                var pageData = canvas.toDataURL('image/jpeg', 1.0);

                var pdf = new jsPDF('', 'pt', 'a4');
                //有两个高度需要区分，一个是html页面的实际高度，和生成pdf的页面高度(841.89)
                //当内容未超过pdf一页显示的范围，无需分页
                if (leftHeight < pageHeight) {
                    pdf.addImage(pageData, 'JPEG', 0, 0, imgWidth, imgHeight );
                } else {
                    while(leftHeight > 0) {
                        pdf.addImage(pageData, 'JPEG', 0, position, imgWidth, imgHeight)
                        leftHeight -= pageHeight;
                        position -= 841.89;
                        //避免添加空白页
                        if(leftHeight > 0) {
                            pdf.addPage();
                        }
                    }
                }

                pdf.save('verify_report.pdf');
            },
            allowTaint: true,
            taintTest: false,
            canvas: canvas,
    		dpi: 172,//导出pdf清晰度

        });
	}

	//发送邮件
	var sendReportEmail = function(){
		var p = {history_id:_selectHistory};
		Metronic.blockUI({target: '#reportModal',animate: true});
		pAjaxRequest(p, "/api/v1/verification/job/report" , "POST", function (d) {
			Metronic.unblockUI('#reportModal');
			var op = LANG.UI_VERIFY_SEND_REPORT_EMAIL;
			operateResponseList(d, op);
		});
	}


    //记录勾选
    var checkRecord = function () {
        var checkArr = [];
        $.each(checkIndex, function (index) {
            checkArr.push(checkIndex[index].job_uuid);
        });
        $('#history_table').bootstrapTable('checkBy', {
            field: 'job_uuid',
            values: checkArr
        })
    }

    /**
     * 格式化总大小
     * @param {*} value
     * @param {*} row
     */
    const allSizeFormatter = (value, row) => {
        if (parseInt(row.job_type_value) !== CONF.TASK_TYPE.DB_RECOVERY) {
            return `<span title="${value}">${value}</span>`;
        }
        if (parseInt(row.sub_module_type_value) !== CONF.DB_TYPE.SAPHANA) {
            return `<span title="${value}">${value}</span>`;
        }
        // SAP HANA恢复任务的总大小为传输大小
        return `<span title="${row.speed_size}">${row.speed_size}</span>`;
    };

    var errorFormatter = function (index, row) {
        switch (row.job_status_value) {
            case 0: //成功
                return '<span class="label label-sm label-success  ">' + row.job_status + '</span>';
            case 2: //中止
            case 45:
                return '<span class="label label-sm label-info  ">' + row.job_status + '</span>';
            case 3: //异常
            case 47:
                return '<span class="label label-sm label-warning  ">' + row.job_status + '</span>';
            case 1: //失败
                return '<span class="label label-sm label-danger  ">' + row.job_status + '</span>';
            default:
                return '<span class="label label-sm label-danger  ">' + row.job_status + '</span>';
        }
    }

    // 查看日志按钮 
    const operateFormatter = function (index, row) {
        const HISTORY_UUID = row.history_uuid;

        let html = `<div class="btn-group">
                        <div class="btn_operation_vicon me-20">
                            <a class="view-logs" id="view_logs_${HISTORY_UUID}" aria-haspopup="true" aria-expanded="false">
                                <i class="viconfont vicon-log" data-toggle="tooltip" data-placement="bottom" data-trigger=\"hover\" title="${LANG.UI_LOG_VIEW}"></i>
                            </a>
                        </div>
                    </div>`;

        return html;
    }

    // 操作按钮事件
    const operates = {
        'click .view-logs': function (event, value, row, index) {
            $('#view_log_drawer').drawer('toggle');
            
            let uuid = row.history_uuid;

            // 重置状态
            isLoading = false;
            currentPage = 0;
            hasMoreData = true;
            currentUuid = uuid;

            $('#show_log_component').runningLog({
                job_uuid: currentUuid,
                history_flag: true
            });

            /*getHistoryRunningLogs(uuid);

            // 延迟添加滚动监听，确保DOM已更新
            setTimeout(() => {
                addScrollListener();
            }, 500);*/
        },
    }

    // 存储当前加载状态
    let isLoading = false;
    let currentPage = 0;
    let hasMoreData = true;
    let currentUuid = '';

    /**
     * 获取历史任务运行日志（支持无线滚动加载）
     * @param {*} uuid history_uuid
     * @param {*} offset offset
     * @param {*} isLoadMore 是否需要加载更多
     */
    const getHistoryRunningLogs = (uuid, offset = 0, isLoadMore = false) => {
        // 设置当前UUID
        if (!isLoadMore) {
            currentUuid = uuid;
            currentPage = 0;
            hasMoreData = true;
        }

        // 防止重复加载
        if (isLoading || !hasMoreData) return;
        
        isLoading = true;

        if (!isLoadMore) {
            Metronic.blockUI({target: '#history_logs_drawer_body', animate: true });
            $('#logs_container').empty();
        } else {
            // 显示加载指示器
            $('#logs_container').append(`<div class="infinite-scroll-loading">${LANG.UI_LOADING}</div>`);
        }

        let params = {
            history_uuid: uuid,
            offset: offset,
            limit: 50
        }

        pAjaxRequest(params, "/api/v1/logs/history/jobs/running_logs", "GET", function (res) {
            try {
                // 移除加载指示器
                $('.infinite-scroll-loading').remove();

                if (res.data.total === 0) {
                    $('#logs_container').addClass('nodata-container');
                    $('#logs_container').html(`<span class="nodata">${LANG.UI_TOOLS_NO_DATA}</span>`);
                    hasMoreData = false;
                    return;
                } else { 
                    // 无数据切换到有数据清除上次无数据时的特定class
                    $('#logs_container').removeClass('nodata-container');
                }

                let logs = res.data.rows;
                let html = '';

                logs.forEach(log => {
                    html += `
                        <div class="log-item">
                            <div class="log-item__icon">${getIcon(log.level_value)}</div>
                            <div class="log-item__content">${log.description}</div>
                            <div class="log-item__time">${log.op_time}</div>
                        </div>
                    `;
                });

                if (!isLoadMore) {
                    $('#logs_container').html(html);
                } else {
                    $('#logs_container').append(html);
                }

                const total = res.data.total;
                const currentCount = offset + logs.length;

                if (currentCount >= total) {
                    hasMoreData = false;
                } else {
                    currentPage = Math.floor(currentCount / 50);
                }
            } catch (error) {
                UIToastr.showWarning(LANG.UI_GET_HISTORY_RUNNING_LOG_FAILED);
                $('.infinite-scroll-loading').remove();
            } finally {
                isLoading = false;
                Metronic.unblockUI('#history_logs_drawer_body');
            }
        })
    }

    /**
     * 任务运行日志滚动监听
     */
    const addScrollListener = () => {
        $('#logs_container').off('scroll').on('scroll', function () { 
            // 防抖处理
            clearTimeout($.data(this, 'scrollTimer'));
            $.data(this, 'scrollTimer', setTimeout(function() {
                handleScroll();
            }, 100));
         });
    }

    /**
     * 处理任务运行日志滚动监听
     */
    const handleScroll = () => {
        const container = $('#logs_container');
        const scrollTop = container.scrollTop();
        const scrollHeight = container[0].scrollHeight;
        const containerHeight = container.height();
        
        // 当距离底部小于40px(logs_container的padding为上下左右20px)时加载更多
        if (scrollHeight - scrollTop - containerHeight <= 40) {
            loadMoreLogs();
        }
    }

    /**
     * 加载更多任务运行日志
     */
    const loadMoreLogs = () => {
        if (!isLoading && hasMoreData && currentUuid) {
            const nextOffset = currentPage * 50;
            getHistoryRunningLogs(currentUuid, nextOffset, true);
        }
    }

    //得到任务ICON CSS
    let getIcon = function (level) {
        if (1 == level) {
            //文件
            icon = '<div class="label label-success" style="background-color: transparent"><i class="viconfont vicon-wancheng1"></i></div>';
        } else if (3 == level) {
            icon = '<div class="label label-danger" style="background-color: transparent"><i class="viconfont vicon-cuowu"></i></div>';
        } else {
            icon = '<div class="label label-warning" style="background-color: transparent"><i class="viconfont vicon-yichang"></i></div>';
        }
        return icon;
    }

    var initHistoryTaskTable = function () {
        var options = {
            async: true,
            toolbarId: '#vin_history_toolbar',
            buttonsToolbar: '.vin_history_btnToolbar',
            vin_url: '/api/v1/jobs/history',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                params.accurateFlag = accurateFlag;
                params = $.extend(params, getParams());
                return params;
            },
            sortName: 'start_time',
            sortOrder: 'desc',
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            changeHeightBtn: true, //改变高度按钮
            batchOperation: true, // 批量操作
            filterBtnId: 'history_job_filter_btn', // 过滤器组件 button id
            dateRangePickerId: 'history_job_datepicker', // 日期选择器组件button id
            detailFormatter: history_detail,
            exportSettings: {
                showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
                custom: [
                    {
                        label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
                        class: 'export-all-excel' 
                    }
                ]
            },
            onCheck: function () {
                if ($('.btn.btn-toolbar-delete').hasClass('disabled')) {
                    $('#delete_history_task_btn').removeAttr("disabled");
                    $('.btn.btn-toolbar-delete').removeClass('disabled');
                }

                let selectedRow = $('#history_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                $('#history_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");

                if (selectedRow.length == 0) {
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                } else if (selectedRow.length > 0) {
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
                }
            },
            onUncheck: function () {
                let selectedRow = $('#history_table').bootstrapTable("getSelections");

                checkIndex = selectedRow;
                if (selectedRow.length == 0) {
                    $('#delete_history_task_btn').attr("disabled", true);
                    $('.btn.btn-toolbar-delete').addClass('disabled');

                    $('#history_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                    $('#history_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                } else if (selectedRow.length > 0) {
                    $('#history_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
                };
            },
            onUncheckAll: function () {
                $('#delete_history_task_btn').attr("disabled", true);
                $('.btn.btn-toolbar-delete').addClass('disabled');

                let selectedRow = $('#history_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;

                $('#history_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                $('#history_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");
                $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
            },
            onCheckAll: function () {
                if ($('.btn.btn-toolbar-delete').hasClass('disabled')) {
                    $('#delete_history_task_btn').removeAttr("disabled");
                    $('.btn.btn-toolbar-delete').removeClass('disabled');
                }

                let selectedRow = $('#history_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;

                $('#history_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-checked");

                $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
            },
            PostBody: function () {
                checkRecord();
                $('#historyJobModal .nodeDiv').hide();
                $('#historyJobModal .storageDiv').hide();
                $('#historyJobModal #vmTasktype option[value="6"]').hide();
                $('#historyJobModal #vmTasktype option[value="7"]').hide();
                $('#historyJobModal #awsTasktype option[value="6"]').hide();
                $('#history_table-grain').parent().parent().hide();

                if (changeHeightFlag == false) {
                    $('#history_table>tbody>tr>td').css({
                        'padding-top': '4.25px',
                        'padding-bottom': '4.25px'
                    })
                    $('#change-height button i').removeClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#history_table>tbody>tr>td').css({
                        'padding-top': '15.75px',
                        'padding-bottom': '15.75px'
                    });
                    $('#change-height button i').addClass('icon-auto-height2');
                }

                $('#history_table [data-toggle="tooltip"]').tooltip();

                const exportOptions = {
                    toolbarId: 'vin_history_toolbar',
                    url: '/api/v1/jobs/history_export',
                    fileName: LANG.UI_HOMEPAGEPRO_HISTORY_DES,
                    filterBtnId: 'history_job_filter_btn',
                    dateRangePickerId: 'history_job_datepicker'
                }

                // 监听导出全部数据
                exportAllTableData(exportOptions);
            },
            onPageChange: function (number, size) {
                let pageRecord = { offset: number - 1, limit: size };
                sessionStorage.setItem('history_table_pageRecord', JSON.stringify(pageRecord));
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false,
                    forceHide: true,
                },
                {
                    field: 'job_name',
                    title: LANG.UI_SEARCH_TASK_NAME,
                    formatter: function (value) {
                        return `<span data-toggle="tooltip" data-placement="bottom-start" title="${value}">${value}</span>`;
                    }
                },
                {
                    field: 'module_type',
                    title: LANG.UI_SEARCH_OBJ_TYPE,
                },
                {
                    field: 'job_type_value',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    formatter: function (index, row) {
                        if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_TAKEOVER) {
                            return `<span title="${LANG.UI_PUBLIC_TAKEOVER}">${LANG.UI_PUBLIC_TAKEOVER}</span>`;
                        } else {
                            return `<span title="${row.job_type}">${row.job_type}</span>`;
                        }
                    }
                },
                {
                    field: 'user_name',
                    title: LANG.UI_REPORT_BUILDER,
                    sortable: false,
                },
                {
                    field: 'start_time',
                    title: LANG.UI_PUBLIC_START_TIME,
                    formatter: function (value) {
                        return `<span data-toggle="tooltip" data-placement="bottom-start" title="${value}">${value}</span>`;
                    }
                    // width: '10%',
                },
                {
                    field: 'finish_time',
                    title: LANG.UI_PUBLIC_END_TIME,
                    formatter: function (value) {
                        return `<span data-toggle="tooltip" data-placement="bottom-start" title="${value}">${value}</span>`;
                    }
                    // width: '10%',
                },
                {
                    field: 'all_size',
                    title: LANG.UI_MICROSOFT365_ALL_SIZE,
                    // width: "36px",
                    formatter: allSizeFormatter,
                },
                {
                    field: 'validate_size',
                    title: LANG.UI_PUBLIC_VM_VALID_SIZE,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'speed_size',
                    title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                    sortable: false,
                },
                {
                    field: 'write_size',
                    title: LANG.UI_PUBLIC_REAL_SIZE,
                },
                {
                    field: 'execution_duration',
                    title: LANG.UI_EXECUTION_DURATION,
                    sortable: false,
                },
                {
                    field: 'job_status_value',
                    title: LANG.UI_VISUAL_RESULT,
                    formatter: errorFormatter,
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    formatter: operateFormatter,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    forceHide: true,
                    events: operates, //单元点击事件
                },
            ]
        }
        //清除offset再初始化
        var page = sessionStorage.getItem('history_table_pageRecord');
        if (page != null) {
            page = JSON.parse(page);
            page.offset = 0;
            page = JSON.stringify(page);
            sessionStorage.setItem('history_table_pageRecord', page);
        }
        $('#history_table').baseTableConfig().init(options);
    }

    function getHistorySrcDes(data) {
        let sourceClientModuleType = parseInt(data.list.timepoint_submodule_type);
        let info = {};
        switch (sourceClientModuleType) {
            case 1: // 源设备为文件客户端
                info.thead = LANG.UI_FILE_HISJOB_SOURCE_NAME_AND_IP;
                info.sourceClientName = data.list.list[0].src_agent_name + '(' + data.list.list[0].src_agent_ip + ')';
                break;
            case 2: // 源设备为NAS
                info.thead =LANG.UI_OBS_SOURCE_DEVICE;
                info.sourceClientName = data.list.list[0].src_agent_ip + '(' + data.list.list[0].src_agent_name + ')';
                break;
            case 3: // 源设备为hadoop集群
                info.thead = LANG.UI_OBS_SOURCE_HADOOP_CLUSTER;
                info.sourceClientName = data.list.list[0].src_agent_name + '(' + data.list.list[0].src_agent_ip + ')';
                break;
            case 4: // 源设备为对象存储
                info.thead =LANG.UI_HADOOP_SOURCE_S3_NAME;
                info.sourceClientName = data.list.list[0].src_agent_name;
                break;
            default:
                info.thead = LANG.UI_FILE_DETAIL_SRC_NAME;
                info.sourceClientName = '-';
                break;
        }
        return info;
    }

    // 文件复制模块，展开详情逻辑
    var getWildcardDes = function(info) {
        var des = '';
        var wildcardList = info.wildcard;
        if (info.wildcard_mode == 0) {
            return LANG.UI_JOB_FILTER_CLEAR;
        }
        var mode = getMatchMode(info.wildcard_mode);
        des += LANG.UI_HISTORY_JOB_DETAIL_COMPARE_CONDITION + ':'  + mode + '</br>';
        for(var i = 0; i < wildcardList.length; i++) {
            des += LANG.UI_HISTORY_JOB_DETAIL_WILDCARD_OBJECT + ':' + (wildcardList[i].wildcard_file_type == 1 ?  LANG.UI_FILE_FILE : LANG.UI_DATA_FILE_DIR) + ',';
            des += LANG.UI_FILE_WILDCARD + ':' + wildcardList[i].wildcard + '</br>'
        }
        return des;
    }

    // 文件复制模块，展开详情逻辑
    var getTimeRangeDes = function(info) {
        var des = '';
        var timeList = info.time_list;
        if (info.time_fliter_mode == 0) {
            return LANG.UI_PUBLIC_NOTHING;
        }
        var mode = getMatchMode(info.time_fliter_mode);
        des += LANG.UI_HISTORY_JOB_DETAIL_COMPARE_CONDITION + ':'  + mode + '</br>';
        if (info.last_days != 0) {
            des += LANG.UI_HISTORY_JOB_DETAIL_COPY_RECENT + info.last_days + LANG.UI_PUBLIC_UNIT_DAY + '</br>';
        } else {
            for(var i = 0; i < timeList.length; i++) {
                var start_time = parseInt(timeList[i].time_fliter_time_start);
                var end_time = parseInt(timeList[i].time_fliter_time_end);
                des += LANG.UI_HISTORY_JOB_DETAIL_FILE_EDIT_TIME_RANGE + ':' + formatDate(start_time) + '-' + formatDate(end_time) + '</br>'
            }
        }
        return des;
    }

    var formatDate = function(timestamp) {
        var date = new Date(timestamp * 1000);
        // 格式化日期和时间
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2); // 月份从0开始，需要加1
        var day = ('0' + date.getDate()).slice(-2);
        var hours = date.getHours();
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);
        // 格式化后的日期和时间字符串
        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    }

    var getMatchMode = function(mode) {
        var des = '';
        switch(parseInt(mode)) {
            case 1: //排除复制
                des = LANG.UI_HISTORY_JOB_DETAIL_EXCEPT_COPY;
                break;
            case 2: //选定复制
                des = LANG.UI_HISTORY_JOB_DETAIL_SELECT_COPY;
                break;
        }
        return des;
    }

    // <-------------------------   BEGIN ADVANCED SEARCH  --------------------------------->

    const initAdvancedSearch = () => {
        if ($('#history_job_advanced_search_wrapper').children().length === 0) {
            $('#history_job_advanced_search_wrapper').initAdvancedSearch({
                advancedSearchSlotId: 'history_job_advanced_search_wrapper', // 搜索组件插槽id
                advancedSearchBtnId: 'history_job_advanced_search_btn', // 搜索按钮id
                searchConditions: [], // 搜索条件数组（初始化时还未搜索传空数组）
                totalConditionNumbers: 9 // 高级搜索表单条件总数
            });
        }
    }

    /**
     * 处理业务类型选所有场景
     * @param {*} businessType
     */
    const handleSelectedBusinessType = (businessType) => {
        $('.history-advanced-search-vm-type').addClass('display-none');
        $('.history-advanced-search-vm-name').addClass('display-none');
        $('.history-advanced-search-db-type').addClass('display-none');

        switch (Number(businessType)) {
            case CONF.SYSTEM_BUSINESS_TYPE.DATA_BACKUP: // 数据备份所有的模块类型
                return { module_type: '2,5,3,4,11,14,28', sub_module_type: '', job_type: '' };
            case CONF.SYSTEM_BUSINESS_TYPE.CONTINUOUS_DATA_PROTECT: // 持续数据保护所有的模块类型
                return { module_type: '10', sub_module_type: '', job_type: '' };
            case CONF.SYSTEM_BUSINESS_TYPE.DATA_COPY: // 数据复制所有的模块类型
                return { module_type: '10,12,26', sub_module_type: '', job_type: '' };
        }
    }

    /**
     * 处理选择的任务类型参数
     * @param {*} taskType
     */
    const handleSelectedTaskType = (taskType) => {
        let moduleTaskData = taskType.value.split('-');

        switch (moduleTaskData.length) {
            case 2: // 不包含子模块的模块类型：数据备份 - NAS、数据库，数据复制 - 数据库，数据复制 - 文件
                $('.history-advanced-search-vm-type').addClass('display-none');
                $('.history-advanced-search-vm-name').addClass('display-none');

                if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB || Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB_CDP) {
                    $('.history-advanced-search-db-type').removeClass('display-none');
                } else {
                    $('.history-advanced-search-db-type').addClass('display-none');
                }

                return {  module_type: moduleTaskData[0], sub_module_type: '',  job_type: moduleTaskData[1] };
            case 3: // 包含子模块的模块类型（但不包括持续数据保护和数据复制的整机模块）：数据备份 - 虚拟化、私有云、公有云、整机、卷、文件、Hadoop、对象存储
                $('.history-advanced-search-db-type').addClass('display-none');

                if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.VM) {
                    $('.history-advanced-search-vm-name').removeClass('display-none');
                    $('.history-advanced-search-vm-type').removeClass('display-none');
                } else {
                    $('.history-advanced-search-vm-name').addClass('display-none');
                    $('.history-advanced-search-vm-type').addClass('display-none');
                }

                if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB || Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB_CDP) {
                    $('.history-advanced-search-db-type').removeClass('display-none');
                } else {
                    $('.history-advanced-search-db-type').addClass('display-none');
                }

                return {  module_type: moduleTaskData[0], sub_module_type: moduleTaskData[1],  job_type: moduleTaskData[2] };
            case 4: // 包含子模块的模块类型（并包括持续数据保护和数据复制的整机模块）：连续数据保护 - 整机、卷，数据复制 - 整机、卷
                $('.history-advanced-search-vm-type').addClass('display-none');
                $('.history-advanced-search-vm-name').addClass('display-none');
                $('.history-advanced-search-db-type').addClass('display-none');

                return { module_type: moduleTaskData[0], storage_location: moduleTaskData[1], dev_type: moduleTaskData[2], job_type: moduleTaskData[3] };
            default:
                return;
        }
    }

    /**
     * 初始化任务类型级联下拉框
     */
    const initCascader = () => {
        let permissions = [];

        // 若子用户是全局观察者且未获得模块授权，过滤器中的对象类型依旧需要全部展示
        if (CONF.PERMISSION.includes('global_observer')) {
            // 获取权限数组，并合并抽象出的第一层业务类型的id数组，即：备份、实时保护和复制
            permissions = CONF.GLOBAL_OBSERVER_CONFIG.concat(['timing_backup', 'data_copy', 'real_time_protect']);  
        } else {
            // 获取权限数组，并合并抽象出的第一层业务类型的id数组，即：备份、实时保护和复制
            permissions = CONF.PERMISSION.concat(['timing_backup', 'data_copy', 'real_time_protect']);
        }

        let treeData = filterMenuTree(MODULE_TASK_TYPE_CASCADER_TREE_DATA, permissions);

        if (CONF.VENDOR === CONF.VENDOR_LIST.gmp) {
            // GMP项目过滤掉数据验证的任务类型
            treeData = treeData.map(taskType => {
                if (taskType.value === "timing_backup") {
                    taskType.children = taskType.children.map(child => {
                        if (child.children && child.children.length > 0) {
                            child.children = child.children.filter(grandChild => grandChild.id !== "data_verification");
                        }
                        return child;
                    });
                }
                return taskType;
            });
        }

        const cascader = new Cascader({
            container: "#history_job_advanced_search_cascader",
            data: treeData,
            placeholder: LANG.UI_CASCADER_PLACEHOLDER,
            selectFn: (val) => {
                // 清空上一次选择的和对象类型有关的属性
                delete ADVANCED_SEARCH_PARAMS.module_type;
                delete ADVANCED_SEARCH_PARAMS.sub_module_type;
                delete ADVANCED_SEARCH_PARAMS.job_type;
                delete ADVANCED_SEARCH_PARAMS.storage_location;
                delete ADVANCED_SEARCH_PARAMS.dev_type;

                let len = val.length;
                let taskTypeParams = {};
                switch (len) {
                    case 2: // 模块类型选的所有
                        taskTypeParams = handleSelectedBusinessType(val[1].value.split('-')[0]);
                        ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, taskTypeParams);
                        break;
                    case 3: // 模块类型非所有
                        taskTypeParams = handleSelectedTaskType(val[2]);
                        ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, taskTypeParams);
                        break;
                    default:
                        break;
                }
            }
        });
    }

    /**
     * 获取数据库类型下拉列表
     */
    const initDbType = () => {
        if (CONF.AUTH_DB_TYPE && CONF.AUTH_DB_TYPE.length > 0) {
            let authedDbTypeData = CONF.AUTH_DB_TYPE.map(item => {
                return {
                    value: item,
                    label: CONF.DB_TYPE_MAP[item]
                }
            });

            let dbTypeSelect = $('#history_advanced_search_db_type');
            dbTypeSelect.empty();

            let option = $("<option>").text(LANG.UI_DB_RECOVERY_ALL_DB_TYPE).val('0');
            dbTypeSelect.append(option);

            authedDbTypeData.forEach(item => {
                option = $("<option>").text(item.label).val(item.value);
                dbTypeSelect.append(option);
            });
            dbTypeSelect.val('0');
        }
    }

    /**
     * 过滤掉对象中的空值或'0'
     * @param {*} obj
     * @returns
     */
    const filterObject = (obj) => {
        return Object.keys(obj).reduce((acc, key) => {
            if (obj[key] !== '' && obj[key] !== '0') {
                acc[key] = obj[key];
            }
            return acc;
        }, {});
    };

    /**
     * 给高级搜索条件展示组件传参前处理参数逻辑
     * @param {*} condition 高级搜索参数对象
     */
    const handleAdvancedSearchConditions = (condition) => {
        let searchConditions = [];
        let newCondition = filterObject(condition);

        Object.keys(newCondition).forEach(key => {
            switch (key) {
                case 'job_name': // 任务名
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: newCondition[key] });

                    break;
                case 'user_name': // 用户名
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: newCondition[key] });

                    break;
                case 'module_type': // 模块类型
                    if (newCondition['sub_module_type']) { // 存在子模块
                        let conditionValText = '';

                        // 如果上一次选的是定时备份所有，由于每次打开高级搜索弹窗未重置各个属性值（下面有备注原因），因此第二次时的module_type是'2,2,2,5,5,3,11,3,3,14,28,4',Number(newCondition[key])会是undefined，需特殊处理
                        if (newCondition[key] === '2,2,2,5,5,3,11,3,3,14,28,4') {
                            conditionValText = `${LANG.UI_PUBLIC_BACKUP}(${LANG.UI_PUBLIC_ALL})`;
                        } else {
                            switch (Number(newCondition[key])) {
                                case CONF.MODULE_TYPE.FS:
                                    conditionValText = CONF.FS_SUBMODULE_TYPE_DES[newCondition['sub_module_type']];
                                    break;
                                case CONF.MODULE_TYPE.VM:
                                    conditionValText = CONF.VM_SUBMODULE_TYPE_DES[newCondition['sub_module_type']];
                                    break;
                                default:
                                    conditionValText = CONF.MODULE_TYPE_DES[newCondition[key]];
                                    break;
                            }

                            if (!newCondition['job_type']) { // 如果不包含任务类型说明选的 所有
                                conditionValText += `(${LANG.UI_PUBLIC_ALL})`;
                            }
                        }

                        searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: `${newCondition[key]}-${newCondition['sub_module_type']}`, conditionValText: conditionValText });
                    } else { // 不存在子模块
                        let conditionValText = '';
                        if (newCondition[key].includes(',')) { // 如果是逗号拼接的模块类型，说明选择的是定时备份所有 或 复制容灾所有
                            let modules = newCondition[key].split(',');
                            if (modules.length === 7) { // 如果是选择的定时备份的所有，则手动传上所有模块的 module_type和sub_module_type，没有子模块的补0，保证和过滤器传参统一
                                conditionValText = `${LANG.UI_PUBLIC_BACKUP}(${LANG.UI_PUBLIC_ALL})`;
                                ADVANCED_SEARCH_PARAMS['module_type'] = '2,2,2,5,5,3,11,3,3,14,28,4';
                                ADVANCED_SEARCH_PARAMS['sub_module_type'] = '1,2,3,1,0,1,2,3,4,0,0,0'; // 手动拼接上对应模块的子模块类型，没有子模块的补0
                            } else {
                                conditionValText = `${LANG.UI_CM_CDP_REPLICATION}(${LANG.UI_PUBLIC_ALL})`;
                            }
                        } else { // 不存在子模块且非定时备份所有
                            switch (Number(newCondition[key])) {
                                case CONF.MODULE_TYPE.DB: // 数据库
                                case CONF.MODULE_TYPE.M365: // M365
                                case CONF.MODULE_TYPE.KUBERNETES: // Kubernetes
                                    conditionValText = CONF.MODULE_TYPE_DES[newCondition[key]];
                                    if (!newCondition['job_type']) { // 如果不包含任务类型说明选的 所有
                                        conditionValText += `(${LANG.UI_PUBLIC_ALL})`;
                                    }
                                    break;
                                case CONF.MODULE_TYPE.VOL_CDP: // 实时保护 | 复制容灾 整机卷
                                    if (!newCondition['dev_type']) { // 实时保护所有
                                        conditionValText = `${LANG.UI_REPORY_CDP}(${LANG.UI_PUBLIC_ALL})`;
                                    } else {
                                        if (!newCondition['job_type']) { // 没有选择任务类型
                                            switch (Number(newCondition['dev_type'])) {
                                                case 1:
                                                    conditionValText = `${LANG.UI_REPORY_CDP} - ${LANG.UI_COPY_MODULE_LABEL_REEL_OS}(${LANG.UI_PUBLIC_ALL})`;
                                                    break;
                                                case 2:
                                                    conditionValText = `${LANG.UI_REPORY_CDP} - ${LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS}(${LANG.UI_PUBLIC_ALL})`;
                                                    break;
                                                case 3:
                                                    conditionValText = `${LANG.UI_CM_CDP_REPLICATION} - ${LANG.UI_COPY_MODULE_LABEL_REEL_OS}(${LANG.UI_PUBLIC_ALL})`;
                                                    break;
                                                case 4:
                                                    conditionValText = `${LANG.UI_CM_CDP_REPLICATION} - ${LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS}(${LANG.UI_PUBLIC_ALL})`;
                                                    break;
                                                default:
                                                    break;
                                            }
                                        } else {  // 选择了任务类型
                                            if (newCondition['dev_type'] == 1 || newCondition['dev_type'] == 3) {
                                                conditionValText = `${LANG.UI_COPY_MODULE_LABEL_REEL_OS}`;
                                            } else if (newCondition['dev_type'] == 2 || newCondition['dev_type'] == 4) {
                                                conditionValText = `${LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS}`;
                                            }
                                        }
                                    }
                                    break;
                                default:
                                    conditionValText = CONF.MODULE_TYPE_DES[newCondition[key]];
                                    break;
                            }
                        }

                        searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: `${newCondition[key]}-0`, conditionValText: conditionValText });
                    }

                    break;
                case 'job_type': // 任务类型
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: CONF.TASK_TYPE_DES[newCondition[key]] });

                    break;
                case 'other_host_name': // 主机名
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: newCondition[key] });

                    break;
                case 'other_vm_name': // 虚拟机名
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: newCondition[key] });

                    break;
                case 'vm_type': // 虚拟机类型
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: CONF.VM_DES[newCondition[key]] });

                    break;
                case 'db_type': // 数据库类型
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: CONF.DB_DES[newCondition[key]] });
                    break;
                default:
                    break;
            }
        });

        return searchConditions;
    }

    /**
     * 初始化高级搜索表单
     */
    const initAdvancedSearchForm = () => {

        initCascader(); // 初始化高级搜索任务类型级联下拉列表

        initVMType(); // 初始化高级搜索虚拟化类型下拉列表

        initDbType(); // 初始化高级搜索数据库类型下拉列表

         // 打开高级搜索弹窗
         $('#history_job_advanced_search_btn').on('click', () => {
            // 重置表单
            // $('#history_advanced_search_task_name').val('');
            // $('#history_advanced_search_user_name').val('');
            // $('#history_advanced_search_host_name').val('');
            // $('#history_advanced_search_vm_name').val('');
            // $('#history_advanced_search_vm_type').val('0');
            // $('#history_advanced_search_db_type').val('0');

            // $('.history-advanced-search-vm-name').addClass('display-none');
            // $('.history-advanced-search-vm-type').addClass('display-none');
            // $('.history-advanced-search-db-type').addClass('display-none');
            // ADVANCED_SEARCH_PARAMS.module_type = '';
            // ADVANCED_SEARCH_PARAMS.sub_module_type = '';
            // ADVANCED_SEARCH_PARAMS.job_type = '';
            // ADVANCED_SEARCH_PARAMS.storage_location = '';
            // ADVANCED_SEARCH_PARAMS.dev_type = '';

            // initCascader();

            $('#history_advanced_search_modal').modal('show');
        });

        $('#history_job_advanced_search_submit').on('click', () => {
            // 获取最终的高级搜索参数
            ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, {
                job_name: $('#history_advanced_search_task_name').val(),
                user_name: $('#history_advanced_search_user_name').val(),
                other_host_name: $('#history_advanced_search_host_name').val(),
                other_vm_name: $('#history_advanced_search_vm_name').val(),
                vm_type: $('#history_advanced_search_vm_type').val(),
                db_type: $('#history_advanced_search_db_type').val()
            });

            let searchConditions = handleAdvancedSearchConditions(ADVANCED_SEARCH_PARAMS);

            // 更新高级搜索条件展示组件
            $('#history_job_advanced_search_wrapper').updateSearchConditons({
                searchConditions: searchConditions,
                advancedSearchBtnId: 'history_job_advanced_search_btn', // 高级搜索按钮id
            });

            $('#history_table').bootstrapTable('refresh', { pageNumber: 1, query: { ...ADVANCED_SEARCH_PARAMS } });
            $('#history_advanced_search_modal').modal('hide');
        });

        window.$off('history_job_advanced_search_btn-removeConditionEvent');

        // 监听当前任务高级搜索展示组件派发的数据，以更新表格
        window.$on('history_job_advanced_search_btn-removeConditionEvent', (removedCondition) => {

            let keys = Object.keys(removedCondition);

            if (keys.length > 0) {
                keys.forEach(key => {
                    // 清除对应高级搜索表单项
                    switch (key) {
                        case 'module_type':
                            initCascader();
                            break;
                        case 'job_name':
                            $('#history_advanced_search_task_name').val('');
                            break;
                        case 'user_name':
                            $('#history_advanced_search_user_name').val('');
                            break;
                        case 'other_host_name':
                            $('#history_advanced_search_host_name').val('');
                            break;
                        case 'other_vm_name':
                            $('#history_advanced_search_vm_name').val('');
                            break;
                        case 'vm_type':
                            $('#history_advanced_search_vm_type').val('0');
                            break;
                        case 'db_type':
                            $('#history_advanced_search_db_type').val('0');
                            break;
                        default:
                            break;
                    }
                    
                    // 从 ADVANCED_SEARCH_PARAMS 中赋空对应的键值
                    if (ADVANCED_SEARCH_PARAMS.hasOwnProperty(key)) {
                        ADVANCED_SEARCH_PARAMS[key] = '';
                    }
                });

                $('#history_table').bootstrapTable('refresh', { query: { ...ADVANCED_SEARCH_PARAMS } });
            }
        });
    };

    // <-------------------------   END ADVANCED SEARCH  ----------------------------------->

    const addListeners = function () {
        // 在绑定新的监听器之前，先移除旧的监听器
        window.$off('updateHistoryJobPage');
        
        window.$on('updateHistoryJobPage', () => {
            initHistoryJobTableFilter(); // 初始化表格过滤器

            initHistoryJobTableDaterangePicker(); // 初始化表格日期选择器

            initAdvancedSearch(); // 初始高级搜索组件

            initAdvancedSearchForm(); // 初始化高级搜索表单

            if ($('#history_table').children().length === 0) {
                initHistoryTaskTable();
            }

            // 清除搜索按钮由于是输入了搜索内容才出现，一开始并不在DOM文档流上，所以要在tab切换到历史任务页后再绑定click事件
            $('#history_job_seach_ipt').on('focus', () => {
                if ($('#history_job_clear_search').val()) {
                    $('#history_job_clear_search').removeClass('hide');
                }
            });

            $('#history_job_seach_ipt').on('input', () => {
                if ($('#history_job_seach_ipt').val()) {
                    $('#history_job_clear_search').removeClass('hide');
                } else {
                    $('#history_job_clear_search').addClass('hide');
                }
            });

             // 清空当前任务搜索
            $('#history_job_clear_search').off().on('click', () => {
                $('#history_job_seach_ipt').val('');

                searchVal = '';
                FILTER_PARAMS.search = '';
                $('#history_job_clear_search').addClass('hide');
                $('#history_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } } );
            });

            // 改变高度按钮
            $('#vin_history_toolbar .change_height').on('click', change_height);
        });

        // 删除历史任务
        $('#delete_history_task_btn').on('click', function () {
            let userUuids = $.map($('#history_table').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});

            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 history_job
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: 'history_job' }, () => {
                deleteHistoryJOB();
            });
        });

        // 下载任务日志
        $('#downloadHistory').on('click', downloadJobLog);

		$('#scriptContentDrawer .drawer-footer button.cancel').off('click').on('click', () => {
			$('#scriptContentDrawer').drawer('hide');
		});
		$('#dbValidateReportDrawer .drawer-footer button.cancel').off('click').on('click', () => {
			$('#dbValidateReportDrawer').drawer('hide');
		});


        // <--------------------- BEGIN TABLE TOOLBAR ------------------------->

        // 回车搜索事件
        $('#history_job_seach_ipt').keypress(function (e) {
            if (e.which == 13) {
                searchVal = $('#history_job_seach_ipt').val();
                FILTER_PARAMS.search = searchVal;

                $('#history_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS }});
            }
        });

        // 搜索当前任务
        $('#history_job_search_btn').off().on('click', () => {
            searchVal = $('#history_job_seach_ipt').val();
            FILTER_PARAMS.search = searchVal;

            $('#history_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS }});
        });

        window.$off('history_job_filter_btn-updateFilterEvent');

         // 监听历史任务表格 - 过滤器组件派发的数据，以更新表格
        window.$on('history_job_filter_btn-updateFilterEvent', (filterData) => {
            let checkedFilterParams = handleUpdateFilterParams(filterData);

            FILTER_PARAMS = {
                search: searchVal,
                module_type: checkedFilterParams.module_type.join(','),
                sub_module_type: checkedFilterParams.sub_module_type.join(','),
                job_type: checkedFilterParams.job_type.join(','),
                job_status: checkedFilterParams.job_status.join(','),
                storage_location: checkedFilterParams.storage_location.join(','),
                dev_type: checkedFilterParams.dev_type.join(','),
                start_time: startTime,
                end_time: endTime
            }

            // 将过滤器参数存到sessionStorage的filterData中(只存取通过过滤器得到的参数：module_type、sub_module_type、job_type、job_status、storage_location、dev_type);originalData保留所有原始数据，用于页面刷新时过滤器回显
            let sessionStorageParams = {
                originalData: filterData,
                filterData: {
                    module_type: FILTER_PARAMS.module_type,
                    sub_module_type: FILTER_PARAMS.sub_module_type,
                    job_type: FILTER_PARAMS.job_type,
                    job_status: FILTER_PARAMS.job_status,
                    storage_location: FILTER_PARAMS.storage_location,
                    dev_type: FILTER_PARAMS.dev_type
                }
            }

            sessionStorage.setItem('history_job_filter_params', JSON.stringify(sessionStorageParams));

            FILTER_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, FILTER_PARAMS);

            $('#history_table').bootstrapTable('refresh', { pageNumber: 1, query: { ...FILTER_PARAMS } });
        });

        window.$off('history_job_datepicker-updateDateRangeEvent');

        // 监听历史任务表格 - 日期范围选择组件派发的数据，以更新表格
        window.$on('history_job_datepicker-updateDateRangeEvent', (data) => {
            // 记录选择的开始时间和结束时间，用于过滤搜索的联动
            startTime = data.startTime;
            endTime = data.endTime;

            $('#history_table').bootstrapTable('refresh', { pageNumber: 1, query: { start_time: startTime, end_time: endTime } });
        });

        // <----------------------- END TABLE TOOLBAR -------------------------->

    }

    /**
     * 初始化历史任务表格过滤器
     * @returns
     */
    const initHistoryJobTableFilter = () => {
        let permissions = [];

        // 若子用户是全局观察者且未获得模块授权，过滤器中的对象类型依旧需要全部展示
        if (CONF.PERMISSION.includes('global_observer')) {
            permissions = CONF.GLOBAL_OBSERVER_CONFIG;  
        } else {
            permissions = CONF.PERMISSION;
        }

        // 需要根据对象权限过滤的字段
        const objectPermissionFields = ['timing_data_protect', 'real_time_data_protect', 'data_copy'];

        const filteredJobTableOptions = HISTORY_JOB_TABLE_FILTER_OPTIONS.reduce((acc, item) => {
            // 如果是需要按对象权限过滤的列
            if (objectPermissionFields.includes(item.field)) {
                // 对每个 item 的 value 进行过滤
                const filteredOptions = item.value.filter(option => permissions.includes(option.id.split('history-')[1]));

                // 如果过滤后的 value 数组不为空，则更新原对象的 value 并保留该对象
                if (filteredOptions.length > 0) {
                    item.value = filteredOptions;
                    acc.push(item);
                }
                // 如果过滤后为空，则不保留该项 (隐式实现)
            } else {
                // 其他列直接保留
                acc.push(item);
            }
            return acc;
        }, []);

        // 用过滤后的新数组完全替换旧数组的内容
        HISTORY_JOB_TABLE_FILTER_OPTIONS.length = 0;
        HISTORY_JOB_TABLE_FILTER_OPTIONS.push(...filteredJobTableOptions);

        /* ===============  任务类型按模块权限并集过滤  =============== */
        // 1. 收集所有已授权模块对应的任务类型
        const allowedTaskTypeSet = new Set();
        const moduleTypeToTaskTypeMap = CONF.MODULE_TYPE_TO_TASK_TYPE_MAP;
        for (const moduleKey in moduleTypeToTaskTypeMap) {
            // 如果该模块在权限列表里
            if (permissions.includes(moduleKey)) {
                moduleTypeToTaskTypeMap[moduleKey].forEach(taskTypeId => {
                    allowedTaskTypeSet.add(`history_${taskTypeId}`);
                });
            }
        }

        // 2. 找到“任务类型”过滤器这一项（最后一项）
        const taskTypeFilterIndex = HISTORY_JOB_TABLE_FILTER_OPTIONS.findIndex(item => item.field === 'task_type');
        if (taskTypeFilterIndex > -1) {
            const taskTypeFilterItem = HISTORY_JOB_TABLE_FILTER_OPTIONS[taskTypeFilterIndex];
            
            // 3. 过滤 value 数组
            taskTypeFilterItem.value = taskTypeFilterItem.value.filter(item => {
                // GMP 剔除数据验证
                if (CONF.VENDOR === CONF.VENDOR_LIST.gmp && item.id === 'history_task_type_data_verify') {
                    return false;
                }
                // 只保留并集中存在的任务类型
                return allowedTaskTypeSet.has(item.id);
            });

            // 副本、副本回传、归档、归档回传因为是单独授权，所以需要从persions中过滤
            taskTypeFilterItem.value = taskTypeFilterItem.value.filter(item => {
                // permissions中有 'copy_protect' 表示有副本权限，没有则要移除taskTypeFilterItem中id为task_type_copy和task_type_copy_fetch
                if ((!permissions.includes('copy_protect') && item.id === 'history_task_type_copy') || (!permissions.includes('copy_protect') && item.id === 'history_task_type_copy_fetch')) {
                    return false;
                }

                // permissions中有 ‘archive_new’ 表示有归档权限，没有则要移除taskTypeFilterItem中id为task_type_archive和task_type_archive_fetch
                if ((!permissions.includes('archive_new') && item.id === 'history_task_type_archive') || (!permissions.includes('archive_new') && item.id === 'history_task_type_archive_fetch')) {
                    return false;
                }
                
                return true;
            });

            // 4. 如果过滤后为空，则直接移除整个“任务类型”过滤器
            if (taskTypeFilterItem.value.length === 0) {
                HISTORY_JOB_TABLE_FILTER_OPTIONS.splice(taskTypeFilterIndex, 1);
            }

            HISTORY_JOB_TABLE_FILTER_OPTIONS[taskTypeFilterIndex] = {...taskTypeFilterItem}
        }

        if (CONF.VENDOR === CONF.VENDOR_LIST.gmp) {
            // GMP项目过滤掉 数据验证 任务类型
            let filterData = HISTORY_JOB_TABLE_FILTER_OPTIONS.map(option => {
                if (option.field === 'task_type') {
                    option.value = option.value.filter(item => item.id !== 'history_task_type_data_verify');
                }

                return option;
            });

            if ($('#history_job_filter_wrapper').children().length === 0) {
                $('#history_job_filter_wrapper').initFilter({
                    filterSlotId: 'history_job_filter_wrapper',
                    filterBtnId: 'history_job_filter_btn',
                    filters: filterData
                });
            }

            return;
        }

        // 获取 sesseionStorage中保存的过滤条件，需要回显到过滤器上，给HISTORY_JOB_TABLE_FILTER_OPTIONS中勾选的项加上 checked: true
        let sessionStorageParams = JSON.parse(sessionStorage.getItem('history_job_filter_params')) || {};
        if (sessionStorageParams && sessionStorageParams.originalData && sessionStorageParams.originalData.length > 0) {
            const storedFilters = sessionStorageParams.originalData;
            const filterMap = new Map();
            for (const filter of storedFilters) {
                filterMap.set(filter.key, new Set(filter.value));
            }

            HISTORY_JOB_TABLE_FILTER_OPTIONS.forEach(group => {
                if (filterMap.has(group.field)) {
                    const selectedValues = filterMap.get(group.field);
                    if (group.value && Array.isArray(group.value)) {
                        group.value.forEach(option => {
                            if (selectedValues.has(String(option.value))) {
                                option.checked = true;
                            }
                        });
                    }
                }
            });
        }

        if ($('#history_job_filter_wrapper').children().length === 0) {
            $('#history_job_filter_wrapper').initFilter({
                filterSlotId: 'history_job_filter_wrapper',
                filterBtnId: 'history_job_filter_btn',
                filters: HISTORY_JOB_TABLE_FILTER_OPTIONS
            });
        }
    }

    /**
     * 初始化历史任务表格日期选择器
     */
    const initHistoryJobTableDaterangePicker = () => {
        if ($('#history_job_daterangepicker_wrapper').children().length === 0) {
            $('#history_job_daterangepicker_wrapper').initDateRangePicker({
                slotId: 'history_job_daterangepicker_wrapper', // 日期范围组件在父组件插槽位置的id
                dateRangePickerId: 'history_job_datepicker', // 选择器button id
                startTime: '', // 开始时间
                endTime: '', // 结束时间
                maxDate: 'now', // 最大可用时间
                timePicker: true, // 是否显示时间,时分
                timePickerSeconds: true, // 是否显示秒
                timePicker24Hour: true, // 是否是24小时制
                alwaysShowCalendars: true, // 是否总是显示日期选择
            });
        }
    }

    return {
        init: function () {
            addListeners();
        }
    };

}();

jQuery(document).ready(function () {
    HistoryJob.init();
});