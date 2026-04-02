var HistoryJob = function () {
    var initHistory = false;
    var fsnodeuuid = ''; //用于下载跳过文件
    //时间选择器全局变量,方便提交搜索的时候直接使用
    var filterFlag = true;
    var totalCount = 0;
    var selectCount = 0;
    var changeHeightFlag = false;
    var checkIndex;
    var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
    var accurateFlag = false;
    var queryParams = {};
    var _reportContent, _selectHistory;
    const HISTORY_JOB_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_PUBLIC_TASK_STATUS,
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
                    id: 'history_task_status_aborded',
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
                    text: LANG.UI_DATACENTER_FAILURE,
                    tag: true,
                    type: 'danger'
                }
            ]
        }
    ]; // 当前任务表格过滤器数组
    let FILTER_PARAMS = {}; // 过滤搜索参数
    let startTime = '', endTime = '', searchVal = '';

    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#history_verify_table>tbody>tr>td').css({
                'padding-top': '15.75px',
                'padding-bottom': '15.75px'
            })
            $('vin_history_verify_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#history_verify_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('vin_history_verify_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }

    //获取参数
    var getParams = function (params) {
        queryParams.search = $('#history_job_seach_ipt').val();
        
        // 合并过滤器搜索参数
        queryParams = Object.assign(queryParams, FILTER_PARAMS);

        return queryParams;
    }

    //初始化虚拟化类型
    var initVMType = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/vm/platforms/hypervisors', 'GET', function (d) {
            var data = d;
            var vmSelect = $('#historyJobModal #vm_hypervisor');
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

    //初始化存储
    var initStorage = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5,
            'source_type': 1
        }, '/api/v1/storages', 'GET', function (d) {
            var data = d;
            var storageSelect = $('#historyJobModal #history_storage');
            storageSelect.empty();
            var option = $("<option>").text(LANG.UI_STORAGE_ALL).val('0');
            storageSelect.append(option);
            for (var i = 0; i < data.data.rows.length; i++) {
                option = $("<option>").text(data.data.rows[i].storage_nickname).val(data.data.rows[i].storage_uuid);
                storageSelect.append(option);
            }
            storageSelect.val('0');
        });
    }

    //根据授权版本差异屏蔽添加任务副本|归档创建任务跳转
    var initSoftwareVersionDiff = function () {
        $.post(CONF.AJAXPATH, {m:CONF.M.ROLE,f:'getTaskByPermission',p:{}}, function(d){
            var module_type_list = JSON.parse(d);
            var array = $.map(module_type_list, function(value, index){
                return [value];
            });
            if ($.inArray("vmprotect", array) == -1) { //不存在虚拟机授权，以下同理
                $('#history_verify_table-vm_backup').parent().parent().hide();
                $('#history_moduletype option[value=2]').hide();
            }
            if ($.inArray("fileprotect", array) == -1) {
                $('#history_verify_table-fs_backup').parent().parent().hide();
                $('#history_moduletype option[value=3]').hide();
            }
            if ($.inArray("db_protect", array) == -1) {
                $('#history_verify_table-db_backup').parent().parent().hide();
                $('#history_moduletype option[value=4]').hide();
            }
            if ($.inArray("os_protect", array) == -1) {
                $('#history_verify_table-os_backup').parent().parent().hide();
                $('#history_moduletype option[value=5]').hide();
            }
            if ($.inArray("vol_cdp_protect", array) == -1) {
                $('#history_verify_table-vol_cdp_backup').parent().parent().hide();
                $('#history_moduletype option[value=10]').hide();
            }
            if ($.inArray("nas_protect", array) == -1) {
                $('#history_verify_table-nas_backup').parent().parent().hide();
                $('#history_moduletype option[value=11]').hide();
            }
            if ($.inArray("office365_protect", array) == -1) {
                $('#history_verify_table-exchange').parent().parent().hide();
                $('#history_moduletype option[value=14]').hide();
            }
            if ($.inArray("awsprotect", array) == -1) {
                $('#history_verify_table-aws').parent().parent().hide();
                $('#history_moduletype option[value=17]').hide();
            }
            if ($.inArray("cloud_platform_private", array) == -1) {
                $('#history_verify_table-privatecloud').parent().parent().hide();
                $('#history_moduletype option[value=22]').hide();
            }
            if ($.inArray("dbprotect", array) == -1) { //数据库实时暂定
                //TODO
            }
            if ($.inArray("hadoop_protect", array) == -1) {
                $('#history_verify_table-hadoop').parent().parent().hide();
                $('#history_moduletype option[value=3-3]').hide();
            }

            //任务类型
            if ($.inArray("data_verification", CONF.PERMISSION) == -1) { // 数据验证任务
                $('#history_verify_table-verify').parent().parent().hide();
            }
        });
    }

    var historyCallback = function (params) {
        if (!initHistory) {
            initHistory = true;
            tableInit();
            addListeners();
            initNodeSelect();
            initStorage();
            initVMType();
            initSoftwareVersionDiff();
        } else {
            $('#history_verify_table').bootstrapTable('refresh');
        }
        initTableHeight();
    }

    
    var tipDelete = function () {
        UIToastr.showInfo(LANG.UI_JOB_DELETE_HISTORY_JOB, LANG.UI_JOB_SELECT_HISTORY_TIPS);
    }

    //删除历史任务
    var deleteHistoryJOB = function () {
        var selectRows = $('#history_verify_table').bootstrapTable('getSelections');
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
                    target: '#history_verify_table',
                    animate: true
                });
                pAjaxRequest({
                    "job_uuids": params,
                }, '/api/v1/jobs/history', 'DELETE', function (data) {
                    Metronic.unblockUI('#history_verify_table');
                    var op = LANG.UI_JOB_SEND_BATCH_DELETE_TASK_MESSAGE;
                    if (operateResponseList(data, op)) {
                        $("#history_verify_table").bootstrapTable('refresh');
                        $("#history_verify_table").bootstrapTable('hideLoading');
                    }
                });
            }
        });
    }

    //初始化所有备份节点
    var initNodeSelect = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/nodes', 'GET', function (d) {
            var data = d;
            var nodeSelect = $('#historyJobModal #history_node');
            nodeSelect.empty();
            var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
            nodeSelect.append(option);
            for (var i = 0; i < data.data.rows.length; i++) {
                option = $("<option>").text(data.data.rows[i].ip).val(data.data.rows[i].node_uuid);
                nodeSelect.append(option);
            }
            nodeSelect.val('0');
        });
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;
        //拿到提示框高度
        if ($('#historyjobdiv').find('.alert-info').length >= 1) {
            var tipHeight = $('.alert-info').height();
            height = panelH - 351 - tipHeight;
        } else {
            height = panelH - 271;
        }
        //计算表格container该设置的高度
        // var container =
        $("#historyjobdiv .fixed-table-body").css({
            "height": height
        });
    }

    var downloadJobLog = function (params) {
        var uuid = $('#history_verify_table').bootstrapTable('getSelections');
        if (uuid.length > 1) {
            return UIToastr.showInfo(LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG, LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG_SELECT_ONE_TIPS);
        } else if (uuid.length == 1) {
            pAjaxRequest({}, '/api/v1/jobs/log/' + uuid[0].job_uuid + '', 'GET', function (res) {
                if (res.success) {
                    window.location.href = '/api/v1/jobs/log_down/' + res.data.info + '?x-api-version=1.0-rev0';
                } else if (!operateResponseList(res)) {
                    return false;
                }
            });
        } else {
            return UIToastr.showInfo(LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG, LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG_NO_SELECT_TIPS);
        }
    }

    var lastIndex = [-1, -1];
    var history_detail = function (index, row, element) {
        if (index != lastIndex[1]) {
            lastIndex.push(index);
            $('#history_verify_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
            lastIndex.splice(0, 1);
        }
        Metronic.blockUI({
            target: element,
            animate: true
        });
        var html = '<table>'
        pAjaxRequest({}, '/api/v1/jobs/history/' + row.job_uuid + '', 'GET', function (res) {
            var data = res.data;
            Metronic.unblockUI(element);
            if (!res.success) {
                $(element).append(LANG.UI_PUBLIC_NOTHING);
                return;
            }
            var job_type;
            switch (data.info.job_type) {
                case 1:
                    job_type = LANG.UI_VISUAL_BACKUP;
                    break;
                case 2:
                    job_type = LANG.UI_VISUAL_RECOVERY;
                    break;
                case 8:
                    job_type = LANG.UI_VISUAL_MOTION_NAME;
                    break;
                case 17:
                    job_type = LANG.UI_VISUAL_VM_COPY;
                    break;
                case 18:
                    job_type = LANG.UI_VISUAL_COPY_CALLBACK;
                    break;
                case 20:
                    job_type = LANG.UI_VISUAL_ARCHIVE_CALLBACK;
                    break;
                case 19:
                    job_type = LANG.UI_VISUAL_ARCHIVE_CHART;
                    break;
                case 26:
                    job_type = LANG.UI_VISUAL_FILE_COPY;
                    break;
                case 27:
                    job_type = LANG.UI_VISUAL_FILE_COPY_CALLBACK;
                    break;
                case 30:
                    job_type = LANG.UI_VIRTUAL_DB_COPY;
                    break;
                case 31:
                    job_type = LANG.UI_VIRTUAL_DB_COPY_BACK;
                    break;
                case 32:
                    job_type = LANG.UI_VISUAL_CDP_BACKUP;
                    break;
                case 37:
                    job_type = LANG.UI_VISUAL_DATA_VERTIFY;
                    break;
                case 38:
                    job_type = LANG.UI_VISUAL_OS_COPY;
                    break;
                case 39:
                    job_type =  LANG.UI_VISUAL_OS_COPY_CALLBACK;
                    break;
                case 44:
                    job_type = LANG.UI_VISUAL_NAS_COPY;
                    break;
                case 45:
                    job_type = LANG.UI_VISUAL_NAS_COPY_CALLBACK;
                    break;
                case 49:
                    job_type = LANG.UI_VISUAL_OS_INSTANT_RECOVERY;
                    break;
                case 50:
                    job_type = LANG.UI_VISUAL_MOTION_NAME;
                    break;
                default:
                    break;
            }
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
                }
                html += `
                    <th>` + LANG.UI_COPY_DETAILS_TIMEPOINT_NUM + `</th>
                    <th>` + LANG.UI_PUBLIC_START_TIME + `</th>
                    <th>` + LANG.UI_PUBLIC_END_TIME + `</th>
                    <th>` + LANG.UI_DB_AVE_SPEED + `</th>
                    <th>` + LANG.UI_JOB_HIS_TOTAL_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_REAL_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_STATUS + `</th>
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
                                <th>` + LANG.UI_PUBLIC_STATUS + `</th>
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
                        if (data.info.job_type == 2) { //恢复
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
                                <th>` + LANG.UI_PUBLIC_STATUS + `</th>
                                <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                                `
                            for (let i = 0; i < data.list.length; i++) {
                                if (undefined !== data.list[i].disk_list && data.list[i].disk_list.length) {
                                    // 卷恢复
                                    $.each(data.list[i].disk_list, function (k, v) {
                                        html += `    <tr>
                                        <td>` + data.list[i].timepoint + `</td>
                                        <td>` + job_type + `</td>
                                        <td>` + data.list[i].dir_path + `</td>
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
                                    <td>` + data.list[i].dir_path + `</td>
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
                        if (data.info.job_type == 8) { // 虚拟机迁移
                            html += `
                            <th>` + LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT + `</th>
                            <th>` + LANG.UI_JOB_HIS_SRC_PATH + `</th>
                            <th>` + LANG.UI_JOB_MOTION_PATH + `</th>
                            <th>` + LANG.UI_PUBLIC_VM_TOTAL_SIZE + `</th>
                            <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                            <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                            <th>` + LANG.UI_PUBLIC_STATUS + `</th>
                            <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                                <tr>
                                    <td>` + data.list[0].timepoint + `</td>
                                    <td>` + data.list[0].dir_path + `</td>
                                    <td style="word-break:break-word;">` + data.list[0].vcenter_ip + '/' + data.list[0].host_ip + '/' + data.list[0].new_name + `</td>
                                    <td>` + data.list[0].vm_size + `</td>
                                    <td>` + data.list[0].transport_size + `</td>
                                    <td>` + storageCalculateSize(parseInt(data.list[0].write_size)) + `</td>
                                    <td>` + data.list[0].task_status + `</td>
                                    <td>` + data.list[0].error_code + `</td>
                                </tr>
                            `
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
                                    timepoints ='<textarea disabled>' + backuplist.join('\n') + '</textarea>' ;

                                }
                                html += `<tr>`;
                                html += `<td>` + data.list[i].path + `</td>`;
                                html += `<td>` + data.list[i].count + `</td>`;
                                html += `<td>` + timepoints + `</td>`;
                                html += `</tr>`;
                            }
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
                                                return replaceBetweenStartEnd(item, '|', '/', '').replace('|', '')
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
                                    list += replaceBetweenStartEnd(data.list.file_list[i], '|', '/', '').replace('|', '')  + "<br>";
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
                                        return replaceBetweenStartEnd(item, '|', '/', '').replace('|', '')
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
                        if (data.info.job_type == 35) { //os备份
                            html += `
                    <th>` + LANG.UI_OS_DETAILS_HOST_NAME + `</th>
                    <th>` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                    <th>` + LANG.UI_PUBLIC_START_TIME + `</th>
                    <th>` + LANG.UI_PUBLIC_END_TIME + `</th>
                    <th>` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                    <th>` + LANG.UI_OS_HOST_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                    <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                    <th>` + LANG.UI_PUBLIC_STATUS + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                    `
                        } else if (data.info.job_type == 50) { // os迁移
                            html += `
                            <th>` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                            <th>` + LANG.UI_OS_MIGRATE_ORIGINAL_HOST + `</th>
                            <th>` + LANG.UI_OS_MIGRATE_TARGET_HOST + `</th>
                            <th>` + LANG.UI_OS_HOST_SIZE + `</th>
                            <th>` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                            <th>` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                            <th>` + LANG.UI_PUBLIC_STATUS + `</th>
                            <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                            `
                        } else { //os恢复
                            html += `
                        <th width="15%">` + LANG.UI_OS_DETAILS_HOST_NAME + `</th>
                        <th width="10%">` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                        <th width="5%">` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_START_TIME + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_END_TIME + `</th>
                        <th width="5%">` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                        <th width="5%">` + LANG.UI_OS_HOST_SIZE + `</th>
                        <th width="5%">` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                        <th width="5%">` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                        <th width="5%">` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                        <th width="5%">` + LANG.UI_PUBLIC_STATUS + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                        `
                        }
                        for (let i = 0; i < data.list.length; i++) {
                            if (data.info.job_type == 35) { //os备份
                                var volList = "--"; //分区列表名称
                                var vol = data.list[i].volume_list;
                                if (vol == "" || vol == undefined) {
                                    volList = "--";
                                } else {
                                    for (let x = 0; x < vol.length; x++) {
                                        volList += vol[x] + "\n";
                                    }
                                }

                                html += `<tr>
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
                            </tr>`
                            } else if (data.info.job_type == 50) { // os迁移
                                html += `<tr>
                                <td>` + data.list[i].timepoint + `</td>
                                <td>` + data.list[i].sourcehost + `</td>
                                <td>` + data.list[i].targethost + `</td>
                                <td>` + data.list[i].os_size + `</td>
                                <td>` + data.list[i].transport_size + `</td>
                                <td>` + data.list[i].write_size + `</td>
                                <td>` + data.list[i].task_status + `</td>
                                <td>` + data.list[i].error_code_des + `</td>
                            </tr>`
                            } else { //os恢复
                                html += `<tr>
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
                            </tr>`
                            }
                        }
                        break;
                    case 10: //卷cdp
                        var rowSpanNum = data.list.length; //相同的列只显示一个需要合并的行数
                        if (data.info.job_type == 32) { //备份
                            html += `
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_VOL + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY_FINISHED + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VALID_DATA_FINISHED + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_AVERAGE_SPEED + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_MAP_VOL + `</th>
                        <th>` + LANG.UI_PUBLIC_STATUS + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;

                            $.each(data.list, function (k, v) {
                                var standbyHostInfo = "--";
                                if (!!v.standby_host_name) {
                                    standbyHostInfo = v.standby_host_name;
                                } else if (v.standby_host_ip == "" && v.takeover_host_ip != "") {
                                    standbyHostInfo = v.takeover_host_name;
                                }
                                var standbyMountInfo = "--";
                                if (v.standby_map_mount_point != "" && v.takeover_target_mount_point == "") {
                                    standbyMountInfo = v.standby_map_mount_point;
                                } else if (v.standby_map_mount_point == "" && v.takeover_target_mount_point != "") {
                                    standbyMountInfo = v.takeover_target_mount_point;
                                } else if (v.standby_map_mount_point != "" && v.takeover_target_mount_point != "") {
                                    standbyMountInfo = v.standby_map_mount_point;
                                }

                                html += `<tr>`;

                                if (k == 0) {
                                    // 第一条才添加合并的列，重复的行只显示一条
                                    html += `<td rowspan="${rowSpanNum}">` + v.agent_name + `</td>`
                                }

                                html += `<td>` + v.vol_display_name + `</td>
                                    <td>` + v.vol_size + `/` + v.vol_complete_size + `</td>
                                    <td>` + v.real_size + `/ ` + v.real_complete_size + `</td>
                                    <td>` + v.draw_speed + `</td>
                                    <td>` + standbyHostInfo + `</td>
                                    <td>` + standbyMountInfo + `</td>`

                                if (k == 0) {
                                    html += `<td rowspan="${rowSpanNum}">` + v.task_status + `</td>
                                        <td rowspan="${rowSpanNum}">` + v.description + `</td>`;
                                }
                                html += `</tr>`;
                            })
                        } else if (data.info.job_type == 33) { //恢复
                            html += `<th>` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_DATA_CLIENT + `</th>
                                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_VOL + `</th>
                                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY + `</th>
                                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME + `</th>
                                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_SERVER + `</th>
                                    <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_VOL + `</th>
                                    <th>` + LANG.UI_PUBLIC_STATUS + `</th>
                                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;
                            $.each(data.list, function (k, v) {
                                html += `<tr>`;

                                if (k == 0) {
                                    // 第一条才添加合并的列，重复的行只显示一条
                                    html += `<td rowspan="${rowSpanNum}">` + v.recovery_target_time + `</td>
                                    <td rowspan="${rowSpanNum}">` + v.agent_name + `</td>`;
                                }

                                html += `<td>` + v.vol_display_name + `</td>
                                <td>` + v.vol_size + `/ ` + v.vol_complete_size + `</td>
                                <td>` + v.real_size + `/ ` + v.real_complete_size + `</td>`;

                                if (k == 0) {
                                    // 第一条才添加合并的列，重复的行只显示一条
                                    html += `<td rowspan="${rowSpanNum}">` + v.recovery_host_name + `</td>`; //恢复目标机
                                }

                                html += `<td>` + v.recovery_target_mount_point + `</td>`;

                                if (k == 0) {
                                    // 第一条才添加合并的列，重复的行只显示一条
                                    html += `<td rowspan="${rowSpanNum}">` + v.task_status + `</td>
                                    <td rowspan="${rowSpanNum}">` + v.description + `</td>`;
                                }
                                html += `</tr>`;
                            })
                        } else if (data.info.job_type == 34) { //接管
                            var des = LANG.UI_VOL_CDP_VERIFY_AND_TAKEOVER_VOL_DESC;
                            html += `
                        <th>` + LANG.UI_MOTION_TIMEPOINT + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_DATA_CLIENT + `</th>
                        <th>` + des + `</th>
                        <th>` + LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER + `</th>
                        <th>` + LANG.UI_JOB_BACKUP_MOUNT_POINT + `</th>
                        <th>` + LANG.UI_PUBLIC_STATUS + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>`;
                            $.each(data.list, function (k, v) {
                                var mountPoint = v.takeover_target_mount_point;
                                if (mountPoint == '') {
                                    mountPoint = '--';
                                }
                                html += `<tr>`;

                                if (k == 0) {
                                    // 第一条才添加合并的列，重复的行只显示一条
                                    html += `<td rowspan="${rowSpanNum}">` + v.takeover_target_time + `</td>
                                    <td rowspan="${rowSpanNum}">` + v.agent_name + `</td>`
                                }

                                html += `<td>` + v.vol_display_name + `</td>
                                <td>` + v.takeover_host_name + `</td>
                                <td>` + mountPoint + `</td>`;

                                if (k == 0) {
                                    // 第一条才添加合并的列，重复的行只显示一条
                                    html += `<td rowspan="${rowSpanNum}">` + v.task_status + `</td>
                                    <td rowspan="${rowSpanNum}">` + v.description + `</td>`
                                }

                                html += `</tr>`;
                            })
                        }
                        break;
                    case 14: //EXCHANGE
                        var pass_item_file_size = 0;
                        var pass_list_display = 'display-none';
                        if (data.list.pass_item_flag != 2) {
                            pass_item_file_size = data.list.total_pass_item_count;
                            pass_list_display = '';
                        }
                        if (data.info.job_type == 1) { //备份
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
                    default:
                        break;
                }
            }

            html += '</table>'
            $(element).append(html);

            // 数据验证报告
            if (data.info.job_type == CONF.TASK_TYPE.SURE_BACKUP) {
                //手动验证不显示验证报告
                if(data.list[0].verify_way == 1){
                    return LANG.UI_PUBLIC_NOTHING;
                }
                var content = '<tr><td><button type="button" class="btn btn-sm green-haze verifyReport" id="'+row.job_uuid+'"> '+LANG.UI_JOB_VERTIFY_REPORT+'</button>'+'</tr></td>';
                $(element).append(content);
            }

            $('.verifyReport').off().on('click', function(){
                var id = $(this).attr("id");
                getReport(id);
            });

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
                if (parseInt(data.list[0].db_type) === CONF.DB_TYPE.ORACLE) {
                    $('#oracleRecoveryContentBtn').unbind('click').on('click', function () {
                        $('#oracleRecoveryContentModal').modal({'width':'850px', 'height':'460px'});
                    });
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
                if (data.info.job_type == 1) { //备份
                    //跳过文件详情模态框表格里面的数据
                    //备份文件总数
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
                } else { //恢复
                    downloadPassFun(history_uuid, "");
                }
            });

            // 注册打开错误详情
            $(element).find('.error-detail-link').off().on('click', function() {
                $('#historyErrorDetailModal .portlet-body').html(``);
                let index = $(this).data('index');
                let dbInfo = data.list[index];
                let errorDetails = JSON.parse(dbInfo.error_details);
                $('#historyErrorDetailModal').modal({width: '850px', height: '460px'});
                let content = ``;
                for (const key in errorDetails) {
                    let dbInfo = errorDetails[key];
                    let msg = ``;
                    for (const logError of dbInfo.info_list) {
                        msg += `${logError}<br>`;
                    }
                    content += `
                    <div class="accordion strategyOne">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle accordion-toggle-styled popovers"
                                        style="display: inline-block; width: 99%;text-decoration: none;" data-container="body"
                                        data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne"
                                        href="#history-error-detail_${key}" aria-expanded="true">
                                        <span class="font-green-seagreen">${dbInfo.title} ------ ${dbInfo.object_name}</span>
                                    </a>
                                </h4>
                            </div>
                            <div id="history-error-detail_${key}" class="panel-collapse collapse ${parseInt(key) === 0 ? 'in' : ''}"
                                style="" aria-expanded="true">
                                <div class="panel-body" style="max-height: 300px; overflow: auto; padding: 16px;">${msg}</div>
                            </div>
                        </div>
                    </div>
                    `;
                }
                $('#historyErrorDetailModal .portlet-body').html(content);
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
                        title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
                        script_list: dbInfo.before_task_script
                    }, {
                        title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
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
            if (29 === parseInt(data.info.job_type)) {
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
                        UIToastr.showError(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL_ERROR);
                        return;
                    }
                    UIToastr.showSuccess(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL_SUCCESS);
                });
            });
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
            for (const scriptIndex in scriptContentInfo.script_list) {
                let scriptInfo = scriptContentInfo.script_list[scriptIndex];
                scriptHtmlList.push(`<a class="scriptItem" data-db-index="${dbIndex}" data-script-type-index="${scriptTypeIndex}" data-script-index="${scriptIndex}">${scriptInfo.script_name}</a>`);
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
        $('#scriptContent').html(scriptContent);
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

    var downloadPassFun = function (history_uuid, agent_uuid) {
        var data = JSON.stringify({
            'history_uuid': history_uuid,
            'fsnodeuuid': fsnodeuuid,
            'agent_uuid': agent_uuid
        });
        Metronic.blockUI({
            target: '#historyjobdiv',
            animate: true
        });
        $.post(CONF.AJAXPATH, {
            m: CONF.M.JOB,
            f: 'downLoadPassFile',
            p: data
        }, function (d) {
            Metronic.unblockUI('#historyjobdiv');
            var jsonFlag = false;
            try {
                $.parseJSON(d);
                jsonFlag = true;
            } catch (e) {
                jsonFlag = false;
            }
            if (jsonFlag && !OPREL(d)) {
                return;
            } else {
                window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.JOB + '&f=downLoadPassFile&p=' + data;
            }

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
    var getDBJobDetails = function (data) {
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
                    <th width="10%">${LANG.UI_PUBLIC_STATUS}</th>
                    <th width="20%">${LANG.UI_PUBLIC_DESCRIPTION}</th>
                </thead>
            `;
            let tbody = `<tbody>`;
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
                    Array.isArray(dbInfo.before_task_script) && Array.isArray(dbInfo.after_task_script) &&
                    dbInfo.before_task_script.length > 0 && dbInfo.after_task_script.length > 0
                ) {
                    let scriptContentList = [{
                        title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
                        script_list: dbInfo.before_task_script
                    }, {
                        title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
                        script_list: dbInfo.after_task_script
                    }];
                    tbody += `<td>${renderScript(i, scriptContentList)}</td>`;
                } else {
                    tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
                }
                tbody += `<td>${dbInfo.task_status}</td>`;
                if (dbInfo.error_details) {
                    try {
                        let errorDetails = JSON.parse(dbInfo.error_details);
                        if (errorDetails) {
                            tbody += `<td>${dbInfo.error_code}, 
                                <u class="text-success error-detail-link" style="cursor: pointer" data-index="${i}">${LANG.UI_PUBLIC_ERROR_DETAIL_LINK}</u>
                            </td>`;
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
                    <th width="10%">${LANG.UI_DB_RECOVERY_TIMEPOINT_RECOVERY}</th>
                    <th width="10%">${LANG.UI_JOB_HIS_BAK_TIMEPOINT}</th>
                    <th width="10%">${LANG.UI_JOB_HIS_SRC_PATH}</th>
                    <th width="10%">${LANG.UI_JOB_HIS_DES_PATH}</th>
                    <th width="8%">${LANG.UI_DB_RECOVERY_TYPE}</th>
            `;
            let recoveryMode = parseInt(data.list[0].recovery_mode);
            let dbType = parseInt(data.list[0].db_type)

            switch (recoveryMode) {
                case 1: // 原数据库恢复
                    break;
                case 2: // 新建数据库恢复
                    if (dbType !== CONF.DB_TYPE.SAPHANA) {  // SAP HANA新建恢复发没有数据目录和日志目录
                        thead += `<th width="12%">${LANG.UI_DB_RECOVERY_DATA_FILE_PATH}</th>`;
                        thead += `<th width="12%">${LANG.UI_DB_RECOVERY_LOG_FILE_PATH}</th>`;
                    }
                    break;
                case 3: // 指定文件夹恢复
                    thead += `<th width="12%">${LANG.UI_DB_RECOVERY_DIRECT_FILE_PATH}</th>`;
                    if (
                        dbType === CONF.DB_TYPE.POSTGRE ||
                        dbType === CONF.DB_TYPE.ANTDB ||
                        dbType === CONF.DB_TYPE.KINGBASE ||
                        dbType === CONF.DB_TYPE.UXDB ||
                        dbType === CONF.DB_TYPE.HIGHGO ||
                        dbType === CONF.DB_TYPE.OPENGAUSS ||
                        dbType === CONF.DB_TYPE.VASTBASE
                    ) {
                        thead += `<th width="12%">${LANG.UI_DB_RECOVERY_CUSTOM_ARCHIVE_DIR}</th>`;
                    }
                    break;
                case 4: // 重定向恢复
                    thead += `<th width="12%">${LANG.UI_DB_RECOVERY_REDIRECT_PATH}</th>`;
                    break;
                case 5: // 导出恢复
                    thead += `<th width="12%">${LANG.UI_DB_RECOVERY_EXPORT_PATH}</th>`;
                    break;
                case 6: //  pdb数据库恢复
                    thead += `<th width="12%">${LANG.UI_DB_RECOVERY_PDB_FILE_PATH}</th>`;
                    break;
                case 7: // 还原归档日志恢复
                    thead += `<th width="12%">${LANG.UI_DB_RESTORE_ARCHIVELOG_FOLDER}</th>`;
                    thead += `<th width="12%">${LANG.UI_DB_RESTORE_ARCHIVELOG_RANGE_TIME}</th>`;
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
                    thead += `<th width="12%">${LANG.UI_DB_RECOVERY_RECOVERY_TIME}</th>`;
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
            // 客户端并行数量
            if (
                CONF.DB_TYPE.MONGODB === dbType ||
                CONF.DB_TYPE.CACHE === dbType ||
                CONF.DB_TYPE.IRIS === dbType
            ) {
                thead += `<th width="8%">${LANG.UI_DB_RECOVERY_MAX_PARALLEL_NUMS}</th>`;
            }
            thead += `<th width="10%">${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</th>`;
            if (2 === parseInt(data.list[0].timepoint_recovery_type)) {  // 恢复最新点有验证报告
                thead += `<th width="10%">${LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT}</th>`;
            }
            thead += `<th width="8%">${LANG.UI_PUBLIC_STATUS}</th>`;
            thead += `<th width="12%">${LANG.UI_PUBLIC_DESCRIPTION}</th>`;
            thead += `</thead>`;

            let tbody = `<tbody>`;
            for (let i = 0; i < data.list.length; i++) {
                let dbInfo = data.list[i];
                if (!i % 2) {
                    tbody += '<tr role="row" class="odd">';
                } else {
                    tbody += '<tr role="row" class="even">';
                }
                if (1 === parseInt(dbInfo.timepoint_recovery_type)) {
                    tbody += '<td>' + LANG.UI_DB_RECOVERY_TIMEPOINT_RECOVERY_TYPE1 + '</td>';
                } else {
                    tbody += '<td>' + LANG.UI_DB_RECOVERY_TIMEPOINT_RECOVERY_TYPE2 + '</td>';
                }
                tbody += `<td>${dbInfo.timepoint_des}</td>`
                tbody += `<td style="word-break: break-word;padding-right: 8px;">${dbInfo.dir_path}</td>`
                if (dbType === CONF.DB_TYPE.ORACLE) {
                    tbody += `<td style="word-break: break-word;padding-right: 8px;">${dbInfo.des_dir_path}</td>`;
                } else if (dbType === CONF.DB_TYPE.TIDB) {
                    tbody += `<td style="word-break: break-word;padding-right: 8px;">${dbInfo.des_dir_path}</td>`;
                } else {
                    tbody += `<td style="word-break: break-word;padding-right: 8px;">${dbInfo.agent_ip}/${dbInfo.instance_name}`;
                    // sqlserver才有新建数据库，其余都是恢复到实例
                    if (dbType === CONF.DB_TYPE.SQLSERVER || dbType === CONF.DB_TYPE.SAPHANA) {
                        tbody += `/${dbInfo.new_db_name}`;
                    }
                    tbody += `</td>`;
                }
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
                // 客户端并行数量
                if (
                    CONF.DB_TYPE.MONGODB === dbType ||
                    CONF.DB_TYPE.CACHE === dbType ||
                    CONF.DB_TYPE.IRIS === dbType
                ) {
                    tbody += `<td>${dbInfo.max_parallel_nums}</td>`;
                }
                // 脚本配置
                if (
                    Array.isArray(dbInfo.before_task_script) && Array.isArray(dbInfo.after_task_script) &&
                    dbInfo.before_task_script.length > 0 && dbInfo.after_task_script.length > 0
                ) {
                    let scriptContentList = [{
                        title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
                        script_list: dbInfo.before_task_script
                    }, {
                        title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
                        script_list: dbInfo.after_task_script
                    }];
                    if (2 === parseInt(dbInfo.timepoint_recovery_type)) {
                        scriptContentList.push({
                            title: LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SHOW,
                            script_list: dbInfo.verification_script,
                        });
                    }
                    tbody += `<td>${renderScript(i, scriptContentList)}</td>`;
                } else {
                    tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
                }
                // 验证报告
                if (2 === parseInt(dbInfo.timepoint_recovery_type)) {
                    if (Array.isArray(dbInfo.verification_script) && dbInfo.verification_script.length > 0) {
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
                        if (errorDetails) {
                            tbody += `<td>${dbInfo.error_code}, 
                                <u class="text-success error-detail-link" style="cursor: pointer" data-index="${i}">${LANG.UI_PUBLIC_ERROR_DETAIL_LINK}</u>
                            </td>`;
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

        if (28 === parseInt(data.info.job_type)) { // 数据库备份
            return getBackupDetails(data);
        } else if (29 === parseInt(data.info.job_type)) { // 数据库恢复
            return getRecoveryDetails(data);
        } else {
            return '';
        }
    };

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
        for (const tablespaceName of recoveryContent.tablespace_list) {
            nodes.push({
                id: 'oracle_database_instance_' + tablespaceName,
                pId: 'oracle_database_instance',
                name: tablespaceName,
                title: tablespaceName,
                isParent: false,
                eventtype: 'table_space',
                icon: './img/platform/storage.png',
                nocheck: true,
            });
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
        var p = JSON.stringify({history_uuid:id, taskuuid: ""});
        $.post(CONF.AJAXPATH,{m:CONF.M.MANOEUVRE,f:"getVerifyJobReport",p:p}, function(d){
            var data = JSON.parse(d);
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
        var data = {};
        data.report = _reportContent;
        data.history_uuid = _selectHistory;
        var p = JSON.stringify(data);
        Metronic.blockUI({target: '#reportModal',animate: true});
        $.post(CONF.AJAXPATH,{m:CONF.M.MANOEUVRE, f:"sendVerifyEmail", p:p},function(d){
            Metronic.unblockUI('#reportModal');
            if(OPREL(d)){

            }
        });
    }


    //记录勾选
    var checkRecord = function () {
        var checkArr = [];
        $.each(checkIndex, function (index) {
            checkArr.push(checkIndex[index].job_uuid);
        });
        $('#history_verify_table').bootstrapTable('checkBy', {
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

    var tableInit = function () {
        var options = {
            async: true,
            toolbarId: '#vin_history_verify_toolbar',
            buttonsToolbar: '.vin_history_verify_btnToolbar',
            vin_url: '/api/v1/jobs/history',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                params.accurateFlag = accurateFlag;
                params = $.extend(params, getParams());
                params.job_type = '37'; //默认传验证任务类型
                return params;
            },
            sortName: 'start_time',
            sortOrder: 'desc',
            onResetView: initTableHeight,
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            changeHeightBtn: true, //改变高度按钮
            batchOperation: true, // 批量操作
            detailFormatter: history_detail,
            customTool: {
                // GMP 隐藏历史验证任务的删除按钮
                // beforeInput: `<div class="flex_center">
                //                 <button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_historyjob"></button>
                //             </div>`,
                afterAdvance: `<button class="btn btn-primary b-btn btn-table brr2 mr6 download" id = "downloadHistory" title = "` + LANG.UI_JOB_DOWNLOAD_HISTORY_TASK_LOG + `"><i class = 'log_download'></i></button>`,
            },
            onCheck: function () {
                modifyDelStyle('history_verify_table', 'delete_historyjob');
                var selectedRow = $('#history_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                // $('#history_verify_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
                $('#history_verify_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");

                if (selectedRow.length == 0) {
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                } else if (selectedRow.length > 0) {
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
                    // $('#delete_historyjob i').removeClass('icon-gray-delete');
                    // $('#delete_historyjob i').addClass('icon-white-delete');
                    // $('#delete_historyjob').removeClass('btn-default');
                    // $('#delete_historyjob').addClass('btn-primary');
                }
            },
            onUncheck: function () {
                modifyDelStyle('history_verify_table', 'delete_historyjob');
                var selectedRow = $('#history_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                if (selectedRow.length == 0) {
                    // $('#history_verify_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
                    $('#history_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                    $('#history_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                    // $('#delete_historyjob i').removeClass('icon-white-delete');
                    // $('#delete_historyjob i').addClass('icon-gray-delete');
                    // $('#delete_historyjob').addClass('btn-default');
                    // $('#delete_historyjob').removeClass('btn-primary');
                } else if (selectedRow.length > 0) {
                    // $('#history_verify_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
                    $('#history_verify_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");
                    $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
                };
            },
            onUncheckAll: function () {
                modifyDelStyle('history_verify_table', 'delete_historyjob');
                var selectedRow = $('#history_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;

                // $('#history_verify_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
                $('#history_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                $('#history_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");
                $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                // $('#delete_historyjob i').removeClass('icon-white-delete');
                // $('#delete_historyjob i').addClass('icon-gray-delete');
                // $('#delete_historyjob').addClass('btn-default');
                // $('#delete_historyjob').removeClass('btn-primary');
            },
            onCheckAll: function () {
                modifyDelStyle('history_verify_table', 'delete_historyjob');
                var selectedRow = $('#history_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                // $('#history_verify_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checked.svg')");
                $('#history_verify_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-checked");

                // var selectedRow = $('#' + table_id + '').bootstrapTable("getSelections");
                $('#historyjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
                if ($('#table').find('.no-records-found').length > 0) {

                } else {
                    // $('#delete_historyjob i').removeClass('icon-gray-delete');
                    // $('#delete_historyjob i').addClass('icon-white-delete');
                    // $('#delete_historyjob').removeClass('btn-default');
                    // $('#delete_historyjob').addClass('btn-primary');
                }
            },
            PostBody: function () {
                var tableData = $('#history_verify_table').bootstrapTable('getData');
                // if (tableData.length == 0)return;
                checkRecord();
                $('#historyJobModal .nodeDiv').hide();
                $('#historyJobModal .storageDiv').hide();
                $('#historyJobModal #vmTasktype option[value="6"]').hide();
                $('#historyJobModal #vmTasktype option[value="7"]').hide();
                $('#historyJobModal #awsTasktype option[value="6"]').hide();
                $('#history_verify_table-grain').parent().parent().hide();
                var filtersStorageToArr = {};
                var filtersStorage = JSON.parse(sessionStorage.getItem('history_table_filters'));
                let selectedFilters = 0;
                let totalFilters = 0;
                if (filterFlag == true) {
                    if (filtersStorage != '{}' && filtersStorage != undefined) {
                        filtersStorageToArr = $.each(filtersStorage, function (k, v) {
                            $.each(v, function (index, value) {
                                $('#vin_history_verify_toolbar #filterDiv .filter-content #' + k + ' input[value=' + value + ']').prop("checked", true);
                            })
                        });
                    };

                    $('#vin_history_verify_toolbar #filterDiv .filter-content input[type=checkbox]').each(function () {
                        totalFilters += 1;
                        if ($(this).prop("checked")) {
                            selectedFilters += 1
                        }
                    });

                    if (selectedFilters > 0) {
                        $('#vin_history_verify_toolbar #filterBtn span').text(LANG.UI_JOB_FILTER+'(' + selectedFilters + '/' + totalFilters + ')');
                    } else {
                        $('#vin_history_verify_toolbar #filterBtn span').text(LANG.UI_JOB_FILTER+'('+ LANG.UI_JOB_FILTER_SELECTNONE +')');
                    };
                }

                if (changeHeightFlag == false) {
                    $('#current_table>tbody>tr>td').css({
                        'padding-top': '4.25px',
                        'padding-bottom': '4.25px'
                    })
                    $('#change-height button i').removeClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#current_table>tbody>tr>td').css({
                        'padding-top': '15.75px',
                        'padding-bottom': '15.75px'
                    });
                    $('#change-height button i').addClass('icon-auto-height2');
                }
            },

            columns: [{
                checkbox: true,
                sortable: false,
                forceHide: true,
            },
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: false,
                    width: "20px",
                },
                {
                    field: 'job_name',
                    title: LANG.UI_SEARCH_TASK_NAME,
                },
                {
                    field: 'module_type',
                    title: LANG.UI_SEARCH_MODE_TYPE
                },
                {
                    field: 'job_type_value',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    formatter: function (index, row) {
                        if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_TAKEOVER) { //历史任务，直接把接管任务（34）描述改为接管&验证
                            return `<span title="${LANG.UI_VOL_CDP_TAKEOVER_AND_VERIFY_DESC}">${LANG.UI_VOL_CDP_TAKEOVER_AND_VERIFY_DESC}</span>`;
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
                    // width: '10%',
                },
                {
                    field: 'finish_time',
                    title: LANG.UI_PUBLIC_END_TIME,
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
                    field: 'job_status_value',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: errorFormatter,
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
        $('#history_verify_table').baseTableConfig().init(options);
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

    const addListeners = function () {
        if ($('#historyLi').hasClass('active')) {
            tableInit();
        }
        // 初始化列表接口
        // $('#historyLi').off('click').on('click', function (e) {
        //     setTimeout(historyCallback, 0);
        // })

        $('#vin_history_verify_toolbar .change_height').on('click', change_height);

        $('#delete_historyjob').on('click', deleteHistoryJOB);
        $('#downloadHistory').on('click', downloadJobLog);
        $('#scriptContentDrawer .drawer-footer button.cancel').off('click').on('click', () => {
            $('#scriptContentDrawer').drawer('hide');
        });
        $('#dbValidateReportDrawer .drawer-footer button.cancel').off('click').on('click', () => {
            $('#dbValidateReportDrawer').drawer('hide');
        });

        // <------------------ BEGIN TABLE TOOLBAR ------------------->

         // 回车搜索事件
        $('#history_job_seach_ipt').keypress(function (e) {
            if (e.which == 13) {
                $('#history_verify_table').bootstrapTable('refresh', {
                    query: { ...FILTER_PARAMS }
                });
            }
        });

        // 搜索当前任务
        $('#history_job_search_btn').off().on('click', () => {
            searchVal = $('#history_job_seach_ipt').val();

            if (searchVal) {
                $('#history_verify_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS }})
            }
        });

        $('#history_job_seach_ipt').on('focus', () => {
            $('#history_job_clear_search').removeClass('hide');
        });

        // 清空当前任务搜索
        $('#history_job_clear_search').on('click', () => {
            $('#history_job_seach_ipt').val('');
            searchVal = '';
            $('#history_job_clear_search').addClass('hide');
            $('#history_verify_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } } );
        });

        // 监听当前任务表格 - 过滤器组件派发的数据，以更新表格
        window.$on('history_job_filter_btn-updateFilterEvent', (filterData) => {
            handleUpdateFilterParams(filterData);
        });

        // 监听当前任务表格 - 日期范围选择组件派发的数据，以更新表格
        window.$on('history_job_datepicker-updateDateRangeEvent', (data) => {
            console.log(data, '时间data');
            // 记录选择的开始时间和结束时间，用于过滤搜索的联动
            startTime = data.startTime;
            endTime = data.endTime;

            $('#history_verify_table').bootstrapTable('refresh', { query: { start_time: startTime, end_time: endTime } });
        });

        // <------------------ END TABLE TOOLBAR ------------------->
    }

    /**
     * 初始化当前任务表格过滤器
     * @param {*}  
     */
    const initHistoryJobTableFilter = () => {
        $('#history_job_filter_wrapper').initFilter({
            filterSlotId: 'history_job_filter_wrapper',
            filterBtnId: 'history_job_filter_btn',
            filters: HISTORY_JOB_TABLE_FILTER_OPTIONS
        });
    }

    /**
     * 处理过滤器组件传参
     * @param {*} filterData 
     */
    const handleUpdateFilterParams = (filterData) => {
        
        let checkedFilterParams = { job_status: [] };

        if (filterData.length > 0) {
            const taskStatusData = filterData.find(i => i.key === "task_status").value; // 任务状态勾选数据

            // 勾选了任务状态数据
            if (taskStatusData.length > 0) {
                checkedFilterParams.job_status = taskStatusData;
            }
        } else {
            checkedFilterParams = { job_status: [] }
        }

        FILTER_PARAMS = {
            search: searchVal,
            job_status: checkedFilterParams.job_status.join(','),
            start_time: startTime,
            end_time: endTime
        }

        $('#history_verify_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } });
    }

    /**
     * 初始化表格时间日期选择器
     */
    const initHistoryJobTableDaterangePicker = () => {
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

    return {
        //main function to initiate the module
        init: function () {
            initHistoryJobTableFilter(); // 初始化当前任务表格过滤器
            initHistoryJobTableDaterangePicker(); // 初始化当前任务表格日期范围选择器
            tableInit();
            addListeners();
            initNodeSelect();
            initStorage();
            initVMType();
            initSoftwareVersionDiff();
        }
    };

}();

jQuery(document).ready(function () {
    HistoryJob.init();
});