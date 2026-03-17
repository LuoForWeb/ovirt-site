/*
 * @Author: ChengJiaFu
 * @Date: 2025-03-24 16:45:10
 * @Description:
 * @version: 1.0
 */
var CurrentJob = function () {
    var deleteFlag = false;
    var checkIndex;
    var btnOpen;
    var queryParams = {};
    var changeHeightFlag = false;
    var accurateFlag = false;
    var initFlag = false;
    let ADVANCED_SEARCH_PARAMS = {}; // 高级搜索查询参数
    const CURRENT_JOB_TABLE_FILTER_OPTIONS = [
        {
            label: LANG.UI_VISUAL_BACKUP,
            field: 'timing_data_protect',
            value: [
                {
                    id: 'vmprotect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.VM,
                    text: LANG.UI_BACKUP_DATA_MODULE_VM,
                },
                {
                    id: 'prcloud_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PRIVATE_CLOUD,
                    text: LANG.UI_PUBLIC_PRIVATE_CLOUD,
                },
                {
                    id: 'awsprotect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.PUBLIC_CLOUD,
                    text: LANG.UI_PUBLIC_PUBLIC_CLOUD,
                },
                {
                    id: 'complete_machine',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'osbackup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
                {
                    id: 'filebackup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.FILE,
                    text: LANG.UI_FILE_FILE,
                },
                {
                    id: 'nas_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.NAS,
                    text: LANG.UI_REPORT_NAS,
                },
                {
                    id: 'hadoop_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.HADOOP,
                    text: LANG.UI_PLATFORM_DES_HADOOP,
                },
                {
                    id: 'obs_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.OBS,
                    text: LANG.UI_VISUAL_OBS,
                },
                {
                    id: 'office365_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.M365,
                    text: LANG.UI_BACKUP_DATA_MODULE_M365,
                },
                {
                    id: 'k8s_protect',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.KUBERNETES,
                    text: LANG.UI_BACKUP_DATA_MODULE_K8S,
                },
                {
                    id: 'db_protect',
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
                    id: 'complete_cdp_backup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'vol_cdp_backup',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.REAL_TIME_COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
            ]
        },
        {
            label: LANG.UI_CM_CDP_REPLICATION,
            field: 'data_copy',
            value: [
                {
                    id: 'machine_copy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_DISK,
                    text: LANG.UI_BACKUP_DATA_MODULE_OS,
                },
                {
                    id: 'vol_cdp_copy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_COMPLETE_MACHINE_VOLUME,
                    text: LANG.UI_VOL_CDP_RECOVER_VOL,
                },
                {
                    id: 'dbcdpcopy',
                    value: CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.DATA_COPY_DB,
                    text: LANG.UI_AGENT_MODULE_DB,
                },
                {
                    id: 'file_copy_protect',
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
                    id: 'task_type_backup',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.BACKUP,
                    text: LANG.UI_VISUAL_BACKUP,
                },
                {
                    id: 'task_type_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.RECOVER,
                    text: LANG.UI_VISUAL_RECOVERY,
                },
                {
                    id: 'task_type_copy',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.COPY,
                    text: LANG.UI_VISUAL_COPY,
                },
                {
                    id: 'task_type_archive',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.ARCHIVE,
                    text: LANG.UI_VISUAL_ARCHIVE,
                },
                {
                    id: 'task_type_takeover_verify',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.TAKEOVER,
                    text: LANG.UI_VOL_CDP_TAKEOVER_DESC,
                },
                {
                    id: 'task_type_motion',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.MOTION,
                    text: LANG.UI_MOTION_NAME,
                },
                {
                    id: 'task_type_instance_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.INSTANT_RECOVER,
                    text: LANG.UI_VISUAL_INSTANT_NAME,
                },
                {
                    id: 'task_type_grain_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.GRAIN_RECOVER,
                    text: LANG.UI_RECOVERY_GRAIN,
                },
                {
                    id: 'task_type_cross_platform_recover',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.CROSS_PLATFORM_RECOVER,
                    text: LANG.UI_FILE_CROSS_RESTORE,
                },
                {
                    id: 'task_type_data_verify',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.DATA_VERIFY,
                    text: LANG.UI_JOB_DATA_VERTIFY,
                },
                {
                    id: 'task_type_data_copy',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.DATA_COPY,
                    text: LANG.UI_CM_CDP_REPLICATION,
                },
                {
                    id: 'task_type_file_compare',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.COMPARE,
                    text: LANG.UI_FILE_COPY_TASK_TYPE_COMPARE
                },
                {
                    id: 'task_type_db_drill',
                    value: CONF.SYSTEM_TASK_TYPE_TO_VALUE_MAP.DB_DRILL,
                    text: LANG.UI_RECOVERY_DB_PROTECT_CREATE_DRILL,
                },
                {
                    id: 'task_type_copy_fetch',
                    value: CONF.TASK_TYPE.BACKUP_COPY_FETCH,
                    text: LANG.UI_COPY_FETCH_MODE
                },
                {
                    id: 'task_type_archive_fetch',
                    value: CONF.TASK_TYPE.ARCHIVE_FETCH,
                    text: LANG.UI_COPY_ARCHIVE_FETCH_MODE
                }
            ]
        },
        {
            label: LANG.UI_PUBLIC_TASK_STATUS,
            field: 'task_status',
            value: [
                {
                    id: 'task_status_success',
                    value: CONF.TASK_STATUS.SUCCESSED,
                    text: LANG.UI_PUBLIC_SUCCESS,
                    tag: true,
                    type: 'success'
                },
                {
                    id: 'task_status_waiting',
                    value: CONF.TASK_STATUS.WAITTING,
                    text: LANG.UI_PUBLIC_WAIT,
                    tag: true,
                    type: 'primary'
                },
                {
                    id: 'task_status_error',
                    value: CONF.TASK_STATUS.ERROR,
                    text: LANG.UI_PLATFORM_DES_ERROR,
                    tag: true,
                    type: 'danger'
                },
                {
                    id: 'task_status_stopped',
                    value: CONF.TASK_STATUS.STOPPED,
                    text: LANG.UI_JOB_STOP,
                    tag: true,
                    type: 'secondary'
                },
                {
                    id: 'task_status_abnormal',
                    value: CONF.TASK_STATUS.ABNORMAL,
                    text: LANG.UI_NODE_ABNORMAL,
                    tag: true,
                    type: 'warning'
                },
                {
                    id: 'task_status_running',
                    value: CONF.TASK_STATUS.RUNNING,
                    text: LANG.UI_PUBLIC_RUNNING,
                    tag: true,
                    type: 'success'
                }
            ]
        }
    ]; // 当前任务表格过滤器数组
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
    // 需要输入密码校验的所有模块的恢复任务
    const needPasswordInputRecoverTasks = [
        CONF.TASK_TYPE.RECOVERY, // 定时模块的恢复
        CONF.TASK_TYPE.INSTANT_RECOVERY, // 定时模块虚拟化的瞬时恢复
        CONF.TASK_TYPE.GRAIN_RECOVERY, // 定时模块虚拟化、整机的细粒度恢复
        CONF.TASK_TYPE.INSTANT_RECOVERY_MOTION, // 虚拟化，定时整机的迁移
        CONF.TASK_TYPE.PLATFORM_RECOVERY, // 定时模块虚拟化、整机的跨平台恢复
        CONF.TASK_TYPE.OS_RECOVERY, // 定时模块整机、卷的恢复
        CONF.TASK_TYPE.VOL_CDP_RECOVERY, // 实时整机、卷的恢复，复制整机、卷的恢复
        CONF.TASK_TYPE.DB_CDP_RECOVER, // 复制数据库的恢复
        CONF.TASK_TYPE.DB_RECOVERY // 数据库恢复
    ];
    let ADVANCED_SEARCH_NODE_TO_DES_MAP = {}; // 高级搜索参数中节点uuid对应节点描述的map映射
    let ADVANCED_SEARCH_STORAGE_TO_DES_MAP = {}; // 高级搜索参数中存储uuid对应存储描述的map映射
    let FILTER_PARAMS = {}; // 过滤搜索参数
    let startTime = '', endTime = '', searchVal = '';

    /**
     * @function 获取自定义参数
     * @param bool auto 自动刷新时暂停获取参数，默认false 为获取
     */
    const getParams = function () {
        queryParams.search = $('#current_job_seach_ipt').val();

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

    // 表格高度改变按钮逻辑
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#current_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_current_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#current_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_current_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }


    //记录勾选
    var checkRecord = function () {
        var checkArr = [];
        $.each(checkIndex, function (index) {
            checkArr.push(checkIndex[index].job_uuid);
        });
        $('#current_table').bootstrapTable('checkBy', {
            field: 'job_uuid',
            values: checkArr
        })
    }

    //记录刷新按钮展开
    var btnRecord = function (target) {
        btnOpen = $(target).next('.dropdown-menu').prop('id');
    }

    //根据授权版本差异屏蔽添加任务副本|归档创建任务跳转
    var initSoftwareVersionDiff = function () {
        var array = CONF.PERMISSION;
        if ($.inArray("vmprotect", array) == -1) { //不存在虚拟机授权，以下同理
            $('.addTaskList .vm_protected').hide();
            $('#current_table-vm_backup').parent().parent().hide();
            $('#current_moduletype option[value=2]').hide();
        }
        if ($.inArray("awsprotect", array) == -1) {
            $('.addTaskList .aws_protected').hide();
            $('#current_table-aws').parent().parent().hide();
            $('#current_moduletype option[value=22]').hide();
        }
        if ($.inArray("prcloud_protect", array) == -1) {
            $('.addTaskList .pri_cloud_protected').hide();
            $('#current_table-aws').parent().parent().hide();
            $('#current_moduletype option[value=17]').hide();
        }
        if ($.inArray("filebackup", array) == -1) {
            $('.addTaskList .fs_protected').hide();
            $('#current_table-fs_backup').parent().parent().hide();
            $('#current_moduletype option[value=3-1]').hide();
        }
        if ($.inArray("db_protect", array) == -1) {
            $('.addTaskList .db_protect').hide();
            $('#current_table-db_backup').parent().parent().hide();
            $('#current_moduletype option[value=4]').hide();
        }
        if ($.inArray("nas_protect", array) == -1) {
            $('.addTaskList .nas_protected').hide();
            $('#current_table-nas_backup').parent().parent().hide();
            $('#current_moduletype option[value=11]').hide();
        }
        // OBS
        if ($.inArray("obs_protect", array) == -1) {
            $('.addTaskList .obs_protected').hide();
            $('#current_table-obs').parent().parent().hide();
            $('#current_moduletype option[value=3-4]').hide();
        }
        if ($.inArray("office365_protect", array) == -1) {
            $('.addTaskList .exchange_protected').hide();
            $('#current_table-exchange').parent().parent().hide();
            $('#current_moduletype option[value=14]').hide();
        }
        if ($.inArray("hadoop_protect", array) == -1) {
            $('.addTaskList .hadoop_protected').hide();
            $('#current_table-hadoop').parent().parent().hide();
            $('#current_moduletype option[value=3-3]').hide();
        }
        if ($.inArray("k8s_protect", array) == -1) {
            $('.addTaskList .k8s_protected').hide();
            $('#current_table-k8s').parent().parent().hide();
            $('#current_moduletype option[value=28]').hide();
        }
        if ($.inArray("complete_machine", array) == -1) {
            $('.addTaskList .machine_os_protected').hide();
        }
        if ($.inArray("osbackup", array) == -1) {
            $('.addTaskList .machine_vol_protected').hide();
        }
        if ($.inArray("complete_machine", array) == -1 && $.inArray("osbackup", array) == -1) {
            $('#current_table-machine_os').parent().parent().hide();
            $('#current_moduletype option[value=5]').hide();
        }
        if ($.inArray("data_verification", array) == -1) {
            $('.addTaskList .data_verify').hide();
            $('#current_table-verify').parent().parent().hide();
        }
        if ($.inArray("complete_cdp_backup", array) == -1) {
            $('.addTaskList .cdp_protected_machine').hide();
        }
        if ($.inArray("vol_cdp_backup", array) == -1) {
            $('.addTaskList .cdp_protected_machine_vol').hide();
        }
        if ($.inArray("vol_cdp_protect", array) == -1) {
            $('#current_table-vol_cdp_backup').parent().parent().hide();
            $('#current_moduletype option[value=10]').hide();
        }
        if ($.inArray("dbcdpbackup", array) == -1) {
            $('.addTaskList .db_cdp').hide();
            $('#current_table-db_cdp1').parent().parent().hide();
            $('#current_table-db_cdp2').parent().parent().hide();
            $('#current_moduletype option[value=12]').hide();
        }
        if ($.inArray("copy", array) == -1) {
            $('.addTaskList .data_copy').hide();
            $('#current_table-data_copy').parent().parent().hide();
            $('#current_moduletype option[value=26]').hide();
        }
    }


    //暂停
    var pauseJob = function (row) {
        var uuids = [];
        uuids.push(row.job_uuid);
        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': uuids
        }, "/api/v1/jobs/pause", "POST", function (res) {
            Metronic.unblockUI('#current_table');
            var op = LANG.UI_COPY_SEND_PAUSE_JOB_MESSAGE;
            if (operateResponseList(res, op)) {
                $('#current_table').bootstrapTable('refresh');
                $("#current_table").bootstrapTable('hideLoading');
            }
        });
    }

    /**
     * 停止当前需要输入密码二次校验的恢复任务
     * @param {*} uuid 任务uuid
     */
    const stopCurrentJobWithPassword = (confirmTitle, jobUUID) => {
        bootbox.prompt({
            title: confirmTitle,
            inputType: 'password',
            callback: debounce(function (r) {
                if (r == null) return;
                // 执行操作验证密码
                Metronic.blockUI({target: '#current_table',animate: true,cenrerY: true});
                let encrypt = new JSEncrypt();
                encrypt.setPublicKey(CONF.PUBLIC_KEY);
                let password = encrypt.encrypt(r);
                let that = this; // 保留指向 bootbox 的this引用，用于在密码校验成功后关闭弹窗
                pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
                    Metronic.unblockUI('#current_table');

                    if (result.code == 0) {
                        $(that).modal('hide');

                        // 执行停止操作
                        pAjaxRequest({}, "/api/v1/jobs/stop/" + jobUUID + "", "POST",function (res) {
                            Metronic.unblockUI('#current_table');
                            let op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                            if (operateResponseList(res, op)) {
                                $('#current_table').bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        });
                    } else {
                        UIToastr.showWarning(result.title, result.message);
                    }
                });
            }, 300, false)
        });
    }

    /**
     * 完成虚拟机迁移任务
     * @param {*} row
     */
    const finishMotionJob = (row) => {
        Metronic.blockUI({ target: '#current_table', animate: true });

        pAjaxRequest({}, "/api/v1/recovery/job/" + row.job_uuid + "/motion", "POST", function (result) {
            Metronic.unblockUI('#current_table');

            if (result.code == 0 || result.code == 200) {
                UIToastr.showSuccess(LANG.UI_PLATFORM_RECOVERY_JOB_STOP_INSTANT_OPERATION, result.message)
            } else {
                UIToastr.showError(LANG.UI_PLATFORM_RECOVERY_JOB_STOP_INSTANT_OPERATION, result.message);
            }
        })
    }

    //停止
    const stopJob = function (row) {
        var bootBoxText = '';

        if (7 == row.job_type_value) {
            //如果是瞬时恢复任务,停止的时候需要提示
            bootbox.confirm({
                title: LANG.UI_JOB_STOP_JOB_TITLE,
                message: LANG.UI_JOB_STOP_JOB_TIPS1 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS2 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS3 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS4 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS5,

                callback: debounce(function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_table',
                        animate: true
                    });
                    pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                        function (res) {
                            Metronic.unblockUI('#current_table');
                            var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                            if (operateResponseList(res, op)) {
                                $('#current_table').bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        });
                }, 300) // 设置 300 ms 防抖延迟
            });
        } else if (12 == row.module_type_value) { //数据库实时模块
            let title = '';
            if (row.job_type_value == CONF.TASK_TYPE.CDP_DB_BACKUP) {
                title = LANG.UI_DB_CDP_DETAILS_STOP_BACKUP_TASK_TIPS;
            } else if (row.job_type_value == CONF.TASK_TYPE.CDP_DB_RECOVERY) {
                title = LANG.UI_DB_CDP_DETAILS_STOP_RECOVER_TASK_TIPS;
            }

            bootBoxText = LANG.UI_JOB_STOP_DBCDP_JOB;
            bootbox.prompt({
                title: title,
                message: bootBoxText,
                inputType: 'password',
                callback: debounce(function (result) {
                    if (result == null) return;

                    getUserPassword();
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_table',
                            animate: true
                        });
                        pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST", function (res) {
                            Metronic.unblockUI('#current_table');
                            var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                            if (operateResponseList(res, op)) {
                                $('#current_table').bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        });

                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }, 300, false) // 设置 300 ms 防抖延迟
            });
        } else if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_BACKUP || row.job_type_value == CONF.TASK_TYPE.VOL_CDP_REPLICATION) {
            var initErrorFlag = false;
            getUserPassword();

            let title = row.job_type_value == CONF.TASK_TYPE.VOL_CDP_BACKUP ? LANG.UI_VOL_CDP_JOB_STOP_BACKUP_CONFIRM : LANG.UI_VOL_CDP_JOB_STOP_COPY_CONFIRM;

            bootbox.prompt({
                title: title,
                inputType: 'password',
                callback: debounce(function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_table',
                            animate: true
                        });
                        pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                            function (res) {
                                var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                                Metronic.unblockUI('#current_table');
                                if (operateResponseList(res, op)) {
                                    $('#current_job').bootstrapTable('refresh');
                                    $("#current_table").bootstrapTable('hideLoading');
                                }
                            });

                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }, 300, false) // 设置 300 ms 防抖延迟
            });
        } else if (CONF.TASK_TYPE.OS_INSTANT_RECOVERY == row.job_type_value && (row.job_status_value != CONF.TASK_STATUS.SUCCESSED && row.job_status_value != CONF.TASK_STATUS.WAITTING)) {
            //如果是操作系统瞬时恢复任务,停止的时候需要提示
            //如果任务是成功状态或者是等待状态 无需提示
            bootbox.prompt({
                title: `${LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS1}<br>${LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS2}<br>${LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM}`,
                inputType: 'password',
                callback: debounce(function (r) {
                    if (r == null) return;
                    // 执行操作验证密码
                    Metronic.blockUI({target: '#current_table',animate: true,cenrerY: true});
                    let encrypt = new JSEncrypt();
                    encrypt.setPublicKey(CONF.PUBLIC_KEY);
                    let password = encrypt.encrypt(r);
                    let that = this; // 保留指向 bootbox 的this引用，用于在密码校验成功后关闭弹窗
                    pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
                        Metronic.unblockUI('#current_table');

                        if (result.code == 0) {
                            $(that).modal('hide');

                            // 执行停止操作
                            pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",function (res) {
                                Metronic.unblockUI('#current_table');
                                let op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                                if (operateResponseList(res, op)) {
                                    $('#current_table').bootstrapTable('refresh');
                                    $("#current_table").bootstrapTable('hideLoading');
                                }
                            });
                        } else {
                            UIToastr.showWarning(result.title, result.message);
                        }
                    });
                }, 300, false)
            });
        } else if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(row.vm_type) && CONF.TASK_STATUS.STOPPING == row.job_status_value) {
            //公有云任务强制停止需二次确认
            bootbox.confirm({
                title: LANG.UI_JOB_FORCE_STOP,
                message: LANG.UI_JOB_FORCE_STOP_PUBLIC_CLOUD_CONFIRM_TIPS,
                callback: debounce(function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_table',
                        animate: true
                    });
                    pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                        function (res) {
                            var op = LANG.UI_OS_SEND_FORCED_STOP_TASK_MESSAGE;
                            Metronic.unblockUI('#current_table');
                            if (operateResponseList(res, op)) {
                                $('#current_table').bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        });
                }, 300) // 设置 300 ms 防抖延迟
            });
        } else if (row.module_type_value == 10000){ //旧数据库实时停止
            opJob('', 'stopJob', row);
        } else if (needPasswordInputRecoverTasks.indexOf(row.job_type_value) > -1) { // 需要二次密码校验的恢复任务
            let title = '';

            switch (row.job_type_value) {
                case CONF.TASK_TYPE.INSTANT_RECOVERY_MOTION: // 迁移任务
                    title = LANG.UI_PLATFORM_RECOVERY_STOP_JOB_MOTION_TIPS;
                    break;
                case CONF.TASK_TYPE.GRAIN_RECOVERY: // 细粒度恢复任务
                    title = row.grain_num_flag ? LANG.UI_PLATFORM_RECOVERY_STOP_JOB_TIPS2 : LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM;
                    break;
                case  CONF.TASK_TYPE.DB_CDP_RECOVER: // 数据库实时恢复
                    title = LANG.UI_JOB_STOP_DBCDP_JOB;
                    break;
                default:
                    title = LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM;
                    break;
            }

            stopCurrentJobWithPassword(title, row.job_uuid);
        } else {
            Metronic.blockUI({
                target: '#current_table',
                animate: true
            });
            pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                function (res) {
                    var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                    Metronic.unblockUI('#current_table');
                    if (operateResponseList(res, op)) {
                        $('#current_table').bootstrapTable('refresh');
                        $("#current_table").bootstrapTable('hideLoading');
                    }
                }
            );
        }
    }

    //删除
    var deleteJob = function (row, forceFlag = false) {
        var module = row.module_type_value; //模块类型
        var taskType = row.job_type_value; //任务类型
        var message = LANG.UI_JOB_DELETE_JOB_TIPS;
        var title = LANG.UI_JOB_DELETE_JOB;
        if (module == 10000) { //数据库实时
            delDBCDPJob(row);
            return;
        }
        if (21 == taskType) {
            //如果是数据库实时任务,换一下提示语TODO
            message = LANG.UI_JOB_DELETE_JOB_TIPS + LANG.UI_JOB_DEL_RTTASK_DEL_BAKDATA;
            bootbox.prompt({
                title: LANG.UI_JOB_DELETE_JOB,
                message: message,
                callback: debounce(function (result) {
                    if (result == null) return;

                    getUserPassword();
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_table',
                            animate: true
                        });
                        pAjaxRequest({}, "/api/v1/jobs/" + row.job_uuid + "", "DELETE", function (data) {
                            Metronic.unblockUI('#current_table');
                            var op = forceFlag ? LANG.UI_OS_SEND_FORCE_DELETE_TASK_MESSAGE : LANG.UI_OS_SEND_DELETE_TASK_MESSAGE;
                            if (operateResponseList(data, op)) {
                                $("#current_table").bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        })

                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }, 300, false) // 延迟300毫秒执行
            });
        } else {
            if (taskType == CONF.TASK_TYPE.BACKUP && module == CONF.MODULE_TYPE.VM && $.inArray(row.vm_type, CONF.HIGH_MODE_HYPERVISORS) != -1) {
                message = LANG.UI_JOB_DEL_VM_JOB_TIPS;
            }
            bootbox.confirm({
                title: title,
                message: message,
                callback: debounce(function (r) {
                    if (!r) return;
                    if (!deleteFlag) {
                        pAjaxRequest({}, "/api/v1/jobs/" + row.job_uuid + "", "DELETE", function (data) {
                            Metronic.unblockUI('#current_table');
                            var op = LANG.UI_OS_SEND_DELETE_TASK_MESSAGE;
                            if (operateResponseList(data, op)) {
                                $("#current_table").bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        })
                    }
                }, 300) // 延迟300毫秒执行
            });
        }
    }

    // 强制删除
    var deleteForceJob = function (row) {
        UIToastr.showInfo(LANG.UI_JOB_FORCE_DELETE, LANG.UI_JOB_FORCE_DELETE)
    }

    //临时做删除数据库实时任务（旧的dbcdp）
    var delDBCDPJob = function (row) {
        var params = {};
        var module = row.module_type_value;		//模块类型
        var taskType = params.job_type_value;	//任务类型
        var uuid = row.job_uuid;
        var message = LANG.UI_JOB_DELETE_JOB_TIPS;
        var deleteFlag = false;
        bootbox.confirm({
            title: LANG.UI_JOB_DELETE_JOB,
            message: message,
            callback: debounce(function(r) {
                if(!r) return;
                if(!deleteFlag){
                    opJob('', 'deleteJob', row);
                    deleteFlag = true;
                    detailsInfo = null;	//清空展开任务详情信息
                }
            }, 300)
        });
    }

    // 旧的dbcdp会用这个方法
    var opJob = function(button, funName, row){
        var params = {};
        params.module = row.module_type_value;		//模块类型
        params.taskType = row.job_type_value;	//任务类型
        params.uuid = row.job_uuid;
        params.dbcdp = row.dbcdp_info;
        params = JSON.stringify(params);
        Metronic.blockUI({target: '#current_table',animate: true});
        $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
            Metronic.unblockUI('#current_table');
            if(OPREL(data)){
                $('#current_table').bootstrapTable('refresh');
            }
        });
    }
    //修改
    var editJob = function (row) {
        var module = row.module_type_value; //模块类型
        var subModule = row.sub_module_type_value;
        var taskType = row.job_type_value; //任务类型
        var uuid = row.job_uuid;
        var tenantuuid = CONF.TENANTUUID;

        //副本
        if ([CONF.TASK_TYPE.BACKUP_COPY,CONF.TASK_TYPE.ARCHIVE].includes(taskType)) {
            editCopyJob(taskType, uuid, module, subModule);
        } else if (taskType == 37) {
            editVerifyJob(taskType, uuid);
        }else {
            switch (module) {
                case 2:
                    if (3 == subModule) {
                        editAWSJob(taskType, uuid);
                    } else {
                        editVMJob(taskType, uuid, tenantuuid);
                    }
                    break;
                case 3:
                    switch (subModule) {
                        case 1: // 文件
                            editFileJob(taskType, uuid, 1);
                            break;
                        case 2: // NAS
                            editFileJob(taskType, uuid, 2);
                            break;
                        case 3: // HADOOP
                            editFileJob(taskType, uuid, 3);
                            break;
                        case 4: // 对象存储
                            editFileJob(taskType, uuid, 4);
                            break;
                        default:
                            break;
                    }

                    break;
                case 10:
                    editCdpJob(taskType, uuid);
                    break;
                case 11:
                    editFileJob(taskType, uuid, 2);
                    break;
                case 14:
                    editExchangeJob(taskType, uuid);
                    break;
                case 4:
                    editDBJob(taskType, uuid, row);
                    break
                case 5:
                    editOSJob(taskType, uuid, subModule);
                    break
                case 17:
                    editAWSJob(taskType, uuid);
                    break;
                case CONF.MODULE_TYPE.DB_CDP:
                    editDbCopyJob(taskType, uuid);
                    break;
                case 10000:
                    editDbCDPJob(taskType, uuid);
                    break;
                case 10001:
                    //文件CDP
                    editFsCDPJob(taskType, uuid);
                    break;
                case 28:
                    editKubernetesJob(taskType, uuid);
                    break;
                case 26:
                    editFileCopyJob(taskType, uuid);
                    break;
            }
        }
    }

    //修改虚拟机任务
    var editVMJob = function (taskType, uuid, tenantuuid) {
        var url = '';
        switch (taskType) {
            case 1:
                //备份
                url = './content/vm/vmbackupedit.php?uuid=' + uuid;

                // if (tenantuuid != "") {
                //     url = './content/vm/vm_backupedit.php?uuid=' + uuid;
                // }
                break;
            case 51:
                //华为CBR同步
                url = './content/cbr/cbrbackupedit.php?taskuuid=' + uuid;
                break;
        }
        LOCATION(url, 'task');
    }

    //修改AWS备份任务
    var editAWSJob = function (taskType, uuid) {
        LOCATION('./content/aws/awsbackup.php?uuid=' + uuid, 'task');
    }

    //修改操作系统备份任务
    var editOSJob = function (taskType, uuid, subModule) {
        if(subModule == 1){
            url = './content/complete_machine_os/machine_os_backup.php?uuid=' + uuid;
        }else{
            url = './content/os/osbackupedit.php?uuid=' + uuid;
        }
        LOCATION(url, 'task');
    }

    // 修改k8s任务
    var editKubernetesJob = function(taskType, uuid){
        url = './content/kubernetes/kubernetes_backup.php?uuid=' + uuid;
        LOCATION(url, 'task');
    }
    // 修改文件复制任务
    var editFileCopyJob = function(taskType, uuid) {
        url = './content/filecopy/file_copy.php?uuid=' + uuid;
        LOCATION(url, 'task');
    }
    //修改数据库备份任务
    var editDBJob = function (taskType, uuid, row) {
        let dbType = parseInt(row.db_type);
        if (dbType === CONF.DB_TYPE.ORACLE) {
            if (row.db_associated_job_list.length) {  // 存在关联任务
                for (const dbAssociatedJobInfo of row.db_associated_job_list) {
                    if (parseInt(dbAssociatedJobInfo.db_job_status) !== CONF.TASK_STATUS.STOPPED) {
                        // 关联任务未停止，不能修改任务
                        UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_ORACLE_EDIT_BACKUP_TASK_ASSOCIATED_NOT_STOPPED.replace('%S', dbAssociatedJobInfo.db_job_name));
                        return false;
                    }
                }
            }
        } else if (dbType === CONF.DB_TYPE.TIDB) {
            if (row.db_associated_job_list.length && row.db_associated_job_list[0].db_job_type === 'slave') {  // 存在关联任务并且关联任务是子任务
                if (row.db_associated_job_list[0].db_job_status !== CONF.TASK_STATUS.STOPPED) {
                    // 关联任务未停止，不能修改任务
                    UIToastr.showWarning(LANG.UI_DB_BACKUP_EDIT_TITLE, LANG.UI_DB_TIDB_EDIT_BACKUP_TASK_ASSOCIATED_NOT_STOPPED.replace('%S', row.db_associated_job_list[0].db_job_name));
                    return false;
                }
            }
        }
        var params = JSON.stringify({
            taskuuid: uuid
        });
        $.post(CONF.AJAXPATH, {
            m: CONF.M.DBPROTECT,
            f: "checkAgentOnline",
            p: params
        }, function (d) {
            var jsonData = JSON.parse(d);
            if (jsonData.flag) {
                var url = '';
                switch (taskType) {
                    case 28:
                        //备份
                        url = './content/dbprotect/dbbackupedit.php?uuid=' + uuid;
                        break;
                    case 64:
                        //恢复
                        url = './content/dbprotect/dbrecover.php?task_uuid=' + uuid + '&edit_flag=1&timepoint_recovery_type=2';
                        break;
                }
                LOCATION(url, 'task');
            } else if (!operateResponseList(d)) {
                //返回代理离线错误描述
            }
        });
    }

    //修改副本任务
    var editCopyJob = function (taskType, uuid, module, sub_module) {
        switch (taskType) {
            case CONF.TASK_TYPE.BACKUP_COPY:
                LOCATION(`./content/copy/copy.php?uuid=${uuid}&module_type=${module}&sub_module_type=${sub_module}`, 'task');
                break;
            case CONF.TASK_TYPE.ARCHIVE:
                LOCATION(`./content/archive/addarchive.php?uuid=${uuid}&module_type=${module}&sub_module_type=${sub_module}`, 'task');
                break;
        }
    }

    //修改数据验证任务
    var editVerifyJob = function (taskType, uuid) {
        var url = '';
        url = '/module/verification/html/add_verification_job.php?uuid=' + uuid;
        LOCATION(url, 'task');
    }

    //修改文件任务
    var editFileJob = function (taskType, uuid, sub_module_type) {
        var url = '';
        switch (taskType) {
            case 1:
                //备份
                url = './content/file/filebackup.php?uuid=' + uuid + '&sub_module_type=' + sub_module_type;
                break;
        }
        LOCATION(url, 'task');
    }
    //修改实时备份任务
    var editCdpJob = function (taskType, uuid) {
        var url = './content/complete_machine_volcdp/cm_volcdp_backup.php?uuid=' + uuid;
        LOCATION(url, 'task');
    }


    //修改exchange任务
    var editExchangeJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case 1:
                //备份
                url = './content/exchange/exchange_backupedit.php?uuid=' + uuid;
                break;
        }
        LOCATION(url, 'task');
    }

    //修改数据库CDP任务
    var editDbCDPJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case 21:
                //实时备份
                url = './content/db/dbcdpedit.php?uuid=' + uuid;
                break;
        }
        LOCATION(url, 'task');
    }

    //修改数据库复制任务
    var editDbCopyJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case CONF.TASK_TYPE.CDP_DB_BACKUP:
                //实时备份
                url = './content/dbcdp/dbcdp_edit.php?uuid=' + uuid;
                break;
        }
        LOCATION(url, 'task');
    }

    //修改文件CDP任务
    var editFsCDPJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case 24:
                //实时备份
                url = './content/fs/filecdpedit.php?uuid=' + uuid;
                break;
        }
        LOCATION(url, 'task');
    }


    /**
     * 启动完备
     * @param {*} row
     */
    const startJob = function (row) {
        if (row.module_type_value == 12) {
            if(row.job_type_value == 46){
                startJobUnify(row, 2);
            }else if(row.job_type_value == 47){
                //数据库实时恢复
                var initErrorFlag = false;
                getUserPassword();
                var bootBoxText = LANG.UI_DB_CDP_JOB_START_RECOVERY_CONFIRM;
                bootbox.prompt({
                    title: bootBoxText,
                    inputType: 'password',
                    callback: debounce(function (result) {
                        if (result == null) return;

                        if (hex_md5(result) == _UserPassword) {
                            _userIsVerify = true;
                            Metronic.blockUI({
                                target: '#current_table',
                                animate: true
                            });
                            startJobUnify(row, 2);

                            $(this).modal('hide');
                        } else {
                            $('.bootbox-input').css('border-color', "#a94442");
                            if (!initErrorFlag) {
                                var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                                $('.bootbox-input').after(des);
                                initErrorFlag = true;
                            }
                            return false;
                        }
                    }, 300, false) // 延迟300毫秒执行
                });
            }
        } else if (row.module_type_value == 10000) {
            startDBCDPJob(row);
        } else if (row.job_type_value === 65) { // 实时容灾的复制任务
            startJobUnify(row, 1);
        } else {
            startJobUnify(row, 1);
        }
    }

    //旧数据库实时启动任务（临时）
    var startDBCDPJob = function(row){
        var params = {};
        params.uuid = row.job_uuid;
        params.dbcdp = row.dbcdp_info;
        params.startType = 1;
        params.taskType = row.job_type_value;
        params = JSON.stringify(params);

        Metronic.blockUI({target: '#current_table',animate: true});
        $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startJob',p:params}, function(data){
            Metronic.unblockUI('#current_table');
            if(OPREL(data)){
                $('#current_table').bootstrapTable('refresh');
            }
        });
    }

    //迁移
    var motion = function (row, type) {
        var params = row;
        // 如果是scp的话直接就接口请求了
        if (params.instant_hypervisor_type == 47) {
            // 进行ajax请求
            var data = {
                is_scp_motion: 1,
                task_uuid: params.job_uuid,
                timepoint_uuid: params.job_uuid,
                job_name: LANG.UI_PLATFORM_RECOVERY_MOTION_NAME
            };
            var url = '/api/v1/recovery/migrates/agentlessis'; // 无代理
            Metronic.blockUI({target: '#currentjobdiv',animate: true,cenrerY: true,});
            pAjaxRequest(data, url, "POST", function (result) {
                Metronic.unblockUI('#currentjobdiv');
                if (result.code == -1) {
                    return UIToastr.showError(LANG.UI_SEARCH_RECOVER_TASK, result.msg);
                }
                if (operateResponseList(result, LANG.UI_SEARCH_RECOVER_TASK)){
                    // 迁移任务创建完成
                }
            });
        } else {
            var url = './content/platform/recovery/motion.php?module=' + params.module_type_value + "&sub_module=" + params.vm_type +
                '&task_type=' + params.job_type_value + '&uuid=' + params.job_uuid;
            LOCATION(url, 'task');
        }
    }

    var startCDPFun = function (row, url, method) {
        var params = [row.job_uuid];
        var op = LANG.UI_OS_SEND_START_TAKEOVER_TASK_MESSAGE;
        if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //验证任务
            op = LANG.UI_JOB_SEND_START_TAKEOVER_AND_VERIF_TASK_MESSAGE;
        }
        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': params
        }, url, method, function (data) {
            Metronic.unblockUI('#current_table');
            if (operateResponseList(data, op)) {
                $('#current_table').bootstrapTable('refresh');
            }
        });
    }

    // 接管任务启动逻辑
    var start_vol_cdp_takeover = function (row, url, method) {
        var taskType = row.job_type_value;

        if (taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskType == CONF.TASK_TYPE.VOL_CDP_REPLICATION) { // 备份或者复制任务运行中手动接管需要输入密码
            var initErrorFlag = false;
            getUserPassword();
            bootbox.prompt({
                title: LANG.UI_VOL_CDP_JOB_START_RECOVERY_CONFIRM,
                inputType: 'password',
                callback: debounce( function (result) {
                    if(result == null) return;
                    if(hex_md5(result) == _UserPassword){
                        _userIsVerify = true;
                        startCDPFun(row, url, method);

                        $(this).modal('hide');
                    }else{
                        $('.bootbox-input').css('border-color', "#a94442");
                        if(!initErrorFlag){
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }, 300, false) // 延迟300毫秒执行
            });
        } else {
            startCDPFun(row, url, method);
        }
    }
    //接管
    var takeover = function (row) {
        var module = row.module_type_value; //模块类型
        var taskType = row.job_type_value; //任务类型
        var params = [row.job_uuid];
        var title = LANG.UI_JOB_START_TAKEOVER;
        if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //验证任务
            title = LANG.UI_VOL_CDP_JOB_DETAILS_START_VERIF_TASK;
        }

        // 整机的接管任务或备份任务或复制任务
        if (taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER || taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskType == CONF.TASK_TYPE.VOL_CDP_REPLICATION) {
            start_vol_cdp_takeover(row, '/api/v1/jobs/start_takeover', 'POST');
        } else {
            bootbox.confirm({
                title: title,
                message: LANG.UI_JOB_START_TAKEOVER_TIPS1 + '<br>' +
                    LANG.UI_JOB_START_TAKEOVER_TIPS2 + '<br>' +
                    LANG.UI_JOB_START_TAKEOVER_TIPS3,
                callback: debounce(function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_table',
                        animate: true
                    });
                    pAjaxRequest({
                        'job_uuids': params,
                    }, '/api/v1/jobs/start_takeover', 'POST', function (data) {
                        var op = LANG.UI_OS_SEND_START_TAKEOVER_TASK_MESSAGE;
                        Metronic.unblockUI('#current_table');
                        if (operateResponseList(data, op)) {
                            $('#current_table').bootstrapTable('refresh');
                            $("#current_table").bootstrapTable('hideLoading');
                        }
                    });
                }, 300) //延时300ms执行
            });
        }
    }
    //点击停止接管操作
    var stoptakeover = function (row) {
        var initErrorFlag = false;
        getUserPassword();
        var bootBoxText = LANG.UI_VOL_CDP_JOB_STOP_TAKEOVER_CONFIRM;
        if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //验证任务
            bootBoxText = LANG.UI_VOL_CDP_JOB_STOP_TAKEOVER_AND_VERIF_CONFIRM;
        }

        //回切阶段下停止接管
        var currentState = row.job_step;

        if (currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING) {
            bootBoxText = LANG.UI_VOL_CDP_JOB_FAILBACK_STOP_TAKEOVER_CONFIRM;
        }

        if (row.module_type_value == 12) { //数据库实时
            bootBoxText = LANG.UI_JOB_STOP_DBCDP_TAKEOVER_CONFIRM_TIPS;
        }
        bootbox.prompt({
            title: bootBoxText,
            inputType: 'password',
            callback: debounce(function (result) {
                if (result == null) return;
                if (hex_md5(result) == _UserPassword) {
                    _userIsVerify = true;
                    stopTakeoverJob(row);

                    $(this).modal('hide');
                } else {
                    $('.bootbox-input').css('border-color', "#a94442");
                    if (!initErrorFlag) {
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }
            }, 300, false) // 延迟300ms执行
        });
    }
    //发送停止接管任务控制
    var stopTakeoverJob = function (row) {
        var uuids = [];
        uuids.push(row.job_uuid);
        var op = LANG.UI_OS_SEND_STOP_TAKEOVER_TASK_MESSAGE;
        if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //验证任务
            op = LANG.UI_JOB_SEND_STOP_TAKEOVER_AND_VERIF_TASK_MESSAGE;
        }

        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': uuids
        }, '/api/v1/jobs/stop_takeover', 'POST', function (d) {
            Metronic.unblockUI('#current_table');
            if (operateResponseList(d, op)) {
                $('#current_table').bootstrapTable('refresh');
                $("#current_table").bootstrapTable('hideLoading');
            }
        })
    }

    //停止自动接管配置
    var stopautotakeover = function (row) {
        var uuid = row.job_uuid;
        var autoTakeoverFlag = 2;
        autoTakeover(uuid, autoTakeoverFlag);
    }

    //启用自动接管配置
    var startautotakeover = function (row) {
        var uuid = row.job_uuid;
        var autoTakeoverFlag = 1;
        autoTakeover(uuid, autoTakeoverFlag);
    }

    //发送自动接管配置消息
    var autoTakeover = function (uuid, autoTakeoverFlag) {
        var uuids = [];
        uuids.push(uuid);
        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });
        pAjaxRequest({'job_uuid': uuids, 'enable_flag': autoTakeoverFlag}, '/api/v1/jobs/switch_autotakeover', 'POST', (res)=>{
            Metronic.unblockUI('#current_table');
            if (operateResponseList(res)) {
                $('#current_table').bootstrapTable('refresh');
                $("#current_table").bootstrapTable('hideLoading');
            }
        });
    }

    //启动回切
    const startfailback = function (row) {
        let params = { task_uuid: row.job_uuid, task_type: row.job_type_value, start_type: 14 };

        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });

        pAjaxRequest(params, '/api/v1/volcdp/job/object_info', 'GET', function (res) {
            Metronic.unblockUI('#current_table');
            let dataInfo = res.data;

            if (dataInfo.length == 0) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK, LANG.UI_VOL_CDP_JOB_DEFAILS_FAILBACK_CONFIG_MESSAGE);
                return;
            }

            let titleTips = "<span>" + LANG.UI_VOL_CDP_BACKUP_TIPS + "</span>: ";
            let desSpan = '';

            if (row.module_type_value === 10 && row.current_stage === 60) { // 实时整机/卷模块且任务阶段为接管中时获取提示信息
                const { master_info, target_machine, target_dev } = dataInfo;

                desSpan = `<span style='font-size: 14px;'>
                                ${LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS1}' '${master_info}' '${LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS2}' '
                                ${target_machine}${LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS1}' [${target_dev.map(i => i.dev_name).join(',')}],
                                ${LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS}${LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS}
                                </br>
                            </span>
                            <span>${LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS1}</span>`;
            } else if (row.module_type_value === 12 && row.current_stage === 31) { // 数据库实时同步且任务阶段为接管中时获取提示信息
                const { source_failback_agent_name, target_failback_agent_name } = dataInfo;

                desSpan = `<span style='font-size: 14px;'>
                                ${LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS1}' '${source_failback_agent_name}' '
                                ${LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS2}' '${target_failback_agent_name}${LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS1}
                                </br>
                            </span>
                            <span>${LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS1}</span>`;
            }

            let failBackDes = titleTips + desSpan;

            let initErrorFlag = false;

            getUserPassword();

            bootbox.prompt({
                title: failBackDes,
                inputType: 'password',
                callback: debounce(function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        startFailbackJob(row);

                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }, 300, false) // 延迟300ms执行
            });
        })
    }
    /**
     * 启动回切任务执行函数
     */
    var startFailbackJob = function (row) {
        var params = [row.job_uuid];

        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': params
        }, '/api/v1/jobs/start_failback', 'POST', function (res) {
            Metronic.unblockUI('#current_table');
            if (res.data.info.length > 0) {
                var op = LANG.UI_JOB_SEND_START_FAILBACK_MSG_TIPS;
                if (operateResponseList(res, op)) {
                    $('#current_table').bootstrapTable('refresh');
                    $("#current_table").bootstrapTable('hideLoading');
                }
            } else {
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK, LANG.UI_VOL_CDP_JOB_DEFAILS_FAILBACK_CONFIG_MESSAGE);
                return;
            }
        })
    }

    // 停止回切，发送停止接管控制码
    var stopfailback = function () {
        stoptakeover();
    }

    var dbCDPOpJob = function (button, funName) {
        var data = grid.getDataTable().data();
        var row = $(button).parents('tr').get(0)._DT_RowIndex;
        var params = data[row][9];
        params.dbcdp = data[row][10].dbCDPDetail;
        params = JSON.stringify(params);
        Metronic.blockUI({
            target: '#current_job',
            animate: true
        });
        $.post(CONF.AJAXPATH, {
            m: CONF.M.DBCDP,
            f: funName,
            p: params
        }, function (data) {
            Metronic.unblockUI('#current_job');
            if (operateResponseList(data)) {
                grid.getRefresh(getParams());
            }
        });
    }

    //启动任务
    var startJobUnify = function (row, type) {
        var taskType = row.job_type_value;
        if (taskType == CONF.TASK_TYPE.VOL_CDP_RECOVERY) {
            getStarTaskObjectInfo(row, taskType, type);
        } else {
            startJobFunc(row, taskType, type)
        }
    }

    /**
     * 获取启动任务执行对象详细信息
     */
    var getStarTaskObjectInfo = function (row, taskType, type) {
        var info = {};
        info.task_uuid = row.job_uuid;
        info.task_type = taskType;
        info.start_type = type;

        var paramsInfo = JSON.stringify(info);
        Metronic.blockUI({
            target: '#current_job',
            animate: true
        });
        $.post(CONF.AJAXPATH, {
            m: CONF.M.JOB,
            f: "getStartTaskObjectInfo",
            p: paramsInfo
        }, function (d) {
            Metronic.unblockUI('#current_job');
            var data = JSON.parse(d);
            var masterInfo = data['master_info'];
            var targetMachine = data['target_machine'];
            var titleTips = "<span>" + LANG.UI_VOL_CDP_BACKUP_TIPS + "</span></br>";
            var desSpan = "<span style='font-size: 14px;'>" + LANG.UI_VOL_CDP_JOB_START_RECOVERY_OPERATE_TIPS1 + "： " + masterInfo + " " + LANG.UI_VOL_CDP_JOB_START_RECOVERY_OPERATE_TIPS2 + ": " + targetMachine + " " + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS + "</span>";
            var recoverDes = titleTips + desSpan;

            var initErrorFlag = false;
            getUserPassword();
            bootbox.prompt({
                title: recoverDes,
                inputType: 'password',
                callback: debounce(function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        startJobFunc(row, taskType, type);

                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }, 300, false)
            });
        });
    }

    var startJobFunc = function (row, taskType, type) {
        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });

        var params = {
            'start_type': type
        };
        pAjaxRequest(params, "/api/v1/jobs/start/" + row.job_uuid + "", 'POST', function (data) {
            Metronic.unblockUI('#current_table');
            var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
            if (operateResponseList(data, op)) {
                $("#current_table").bootstrapTable('refresh');
                $("#current_table").bootstrapTable('hideLoading');
            }

        });
    }

    function timestampToTime(timestamp) {
        timestamp = timestamp ? timestamp : null;
        let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
        let Y = date.getFullYear() + '-';
        let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
        let D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' ';
        let h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
        let m = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
        let s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();
        return Y + M + D + h + m + s;
    }

    // 判断勾选为零时置灰批量操作
    var resetBatchOp = function () {
        $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
        $('.batch-start').removeClass('batch_start_active');
        $('.batch-start i').removeClass('icon-start');
        $('.batch-start span').removeClass('bgb');

        $('.batch-stop').removeClass('batch_stop_active');
        $('.batch-stop i').removeClass('icon-stop');
        $('.batch-stop span').removeClass('bgb');

        $('.batch-delete').removeClass('batch_delete_active');
        $('.batch-delete i').removeClass('icon-delete');
        $('.batch-delete span').removeClass('bgb');
    }

    // 批量启动
    var batchStart = function () {
        var params = [];
        var row = $('#current_table').bootstrapTable('getSelections');
        $.each(row, function (index) {
            params.push(row[index].job_uuid)
        })
        Metronic.blockUI({
            target: '#current_table',
            animate: true
        });
        pAjaxRequest({
            "job_uuids": params,
            "start_type": 0
        }, '/api/v1/jobs/start', 'POST', function (data) {
            Metronic.unblockUI('#current_table');
            var op = LANG.UI_JOB_SEND_BATCH_START_TASK_MESSAGE;
            if (operateResponseList(data, op)) {
                $("#current_table").bootstrapTable('refresh');
                $("#current_table").bootstrapTable('hideLoading');
            }
        });
    }

    //批量删除任务
    var batchDelete = function () {
        var message = LANG.UI_JOB_DELETE_JOB_TIPS;
        var params = [];
        var row = $('#current_table').bootstrapTable('getSelections');
        if (21 == row.job_type_value) {
            //如果是数据库实时任务,换一下提示语TODO
            message = LANG.UI_JOB_DELETE_JOB_TIPS + LANG.UI_JOB_DEL_RTTASK_DEL_BAKDATA;
        }
        $.each(row, function (k, v) {
            // 有虚拟化备份，判断是不是支持高速模式的虚拟化，是的话换提示语
            if (v.job_type_value == CONF.TASK_TYPE.BACKUP && v.module_type_value == CONF.MODULE_TYPE.VM && $.inArray(v.vm_type, CONF.HIGH_MODE_HYPERVISORS) != -1) {
                message = LANG.UI_JOB_DEL_VM_JOB_TIPS;
            }
        });
        bootbox.confirm({
            title: LANG.UI_JOB_DELETE_JOB,
            message: message,
            callback: debounce(function (r) {
                if (!r) return;
                if (!deleteFlag) {
                    $.each(row, function (index) {
                        params.push(row[index].job_uuid);
                    })
                    Metronic.blockUI({
                        target: '#current_table',
                        animate: true
                    });
                    pAjaxRequest({
                        "job_uuids": params
                    }, '/api/v1/jobs', 'DELETE', function (data) {
                        Metronic.unblockUI('#current_table');
                        $('#current_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                        $('#current_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");

                        var op = LANG.UI_JOB_SEND_BATCH_DELETE_TASK_MESSAGE;
                        if (operateResponseList(data, op)) {
                            deleteFlag = true;
                            $("#current_table").bootstrapTable('refresh');
                            $("#current_table").bootstrapTable('hideLoading');
                        }
                    });
                }
            }, 300)
        })
    }

    /**
     * 批量停止当前需要输入密码二次校验的恢复任务
     * @param {*} confirmTitle
     * @param {*} uuids
     */
    const batchStopCurrentJobsWithPassowrd = (confirmTitle, uuids) => {
        bootbox.prompt({
            title: confirmTitle,
            inputType: 'password',
            callback: debounce(function (r) {
                if (r == null) return;
                // 执行操作验证密码
                Metronic.blockUI({target: '#current_table',animate: true,cenrerY: true});
                let encrypt = new JSEncrypt();
                encrypt.setPublicKey(CONF.PUBLIC_KEY);
                let password = encrypt.encrypt(r);
                let that = this; // 保留指向 bootbox 的this引用，用于在密码校验成功后关闭弹窗
                pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
                    Metronic.unblockUI('#current_table');

                    if (result.code == 0) {
                        $(that).modal('hide');

                        // 执行停止操作
                        pAjaxRequest({ job_uuids: uuids }, "/api/v1/jobs/stop/", "POST", function (res) {
                            Metronic.unblockUI('#current_table');
                            let op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                            if (operateResponseList(res, op)) {
                                $('#current_table').bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        });
                    } else {
                        UIToastr.showWarning(result.title, result.message);
                    }
                });
            }, 300, false)
        });
    }

    //批量停止
    var batchStop = function () {
        //设置批量删除时特殊情况处理flag
        let isVolCdp = false;
        let isOsInstant = false;
        let isInstantDbCdp = false;
        let isVolCdpTakeover = false;
        let needPasswordCheckTask = false;
        let params = [];
        let row = $('#current_table').bootstrapTable('getSelections');
        let confirmTile = '';

        $.each(row, function (index) {
            params.push(row[index].job_uuid)
        })

        for (let i = 0; i < row.length; i++) {
            if (7 == row[i].job_type_value) {
                //如果是瞬时恢复任务,停止的时候需要提示
                isInstantDbCdp = true;
            } else if (CONF.TASK_TYPE.VOL_CDP_BACKUP == row[i].job_type_value || CONF.TASK_TYPE.VOL_CDP_REPLICATION == row[i].job_type_value) {
                isVolCdp = true;
            } else if (CONF.TASK_TYPE.VOL_CDP_TAKEOVER == row[i].job_type_value) {
                isVolCdpTakeover = true;
            } else if (CONF.TASK_TYPE.OS_INSTANT_RECOVERY == row[i].job_type_value) {
                //如果是操作系统瞬时恢复任务,停止的时候需要提示
                isOsInstant = true;
            } else if (needPasswordInputRecoverTasks.indexOf(row[i].job_type_value) > -1) {
                needPasswordCheckTask = true;
            }
        }

        if (isInstantDbCdp) {
            //如果是瞬时恢复任务或数据库实时模块,停止的时候需要提示
            bootbox.confirm({
                title: LANG.UI_JOB_STOP_JOB_TITLE,
                message: LANG.UI_JOB_STOP_JOB_TIPS1 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS2 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS3 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS4 + '<br>' +
                    LANG.UI_JOB_STOP_JOB_TIPS5,

                callback: debounce(function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_table',
                        animate: true
                    });
                    pAjaxRequest({
                        "job_uuids": params
                    }, '/api/v1/jobs/stop', 'POST', function (data) {
                        Metronic.unblockUI('#current_table');
                        var op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                        if (operateResponseList(data, op)) {
                            $("#current_table").bootstrapTable('refresh');
                            $("#current_table").bootstrapTable('hideLoading');
                        }
                    });
                }, 300)
            });
        } else if (isVolCdp || isVolCdpTakeover) {
            var initErrorFlag = false;
            var title = LANG.UI_VOL_CDP_JOB_STOP_BACKUP_CONFIRM;
            if (isVolCdpTakeover) {
                title = LANG.UI_VOL_CDP_JOB_FAILBACK_STOP_TAKEOVER_CONFIRM;
            }
            getUserPassword();
            bootbox.prompt({
                title: title,
                inputType: 'password',
                callback: debounce(function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_table',
                            animate: true
                        });
                        pAjaxRequest({
                            "job_uuids": params
                        }, '/api/v1/jobs/stop', 'POST', function (data) {
                            Metronic.unblockUI('#current_table');
                            var op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                            if (operateResponseList(data, op)) {
                                $("#current_table").bootstrapTable('refresh');
                                $("#current_table").bootstrapTable('hideLoading');
                            }
                        });

                        $(this).modal('hide');
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }, 300, false)
            });
        } else if (isOsInstant) {
            confirmTile = `${LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS1}<br>${LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS2}<br>${LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM}`;
            batchStopCurrentJobsWithPassowrd(confirmTile, params);
        } else if (needPasswordCheckTask) {
            batchStopCurrentJobsWithPassowrd(LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM, params);
        } else {
            Metronic.blockUI({
                target: '#current_table',
                animate: true
            });
            pAjaxRequest({
                "job_uuids": params
            }, '/api/v1/jobs/stop', 'POST', function (data) {
                Metronic.unblockUI('#current_table');
                var op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                if (operateResponseList(data, op)) {
                    $("#current_table").bootstrapTable('refresh');
                    $("#current_table").bootstrapTable('hideLoading');
                }
            });
        }
    }

    //初始化当前用户密码用于删除二次确认
    var getUserPassword = function () {
        pAjaxRequest({}, '/api/v1/users/password', 'GET', function (res) {
            _UserPassword = res.data.password;
        })
    }

    var dbTypeFormater = function (index, row) {
        let jobType = parseInt(row.job_type_value);
        if (
            CONF.TASK_TYPE['DB_BACKUP'] !== jobType &&
            CONF.TASK_TYPE['DB_RECOVERY'] !== jobType &&
            CONF.TASK_TYPE['DRILL'] !== jobType
        ) {
            return '--';
        }
        let dbType = parseInt(row.db_type);
        if (undefined === CONF.DB_DES[dbType]) {
            return CONF.DB_DES[0]; // Unknown
        }
        return CONF.DB_DES[dbType];
    }

    var vmFormatter = function (index, row) {
        return row.module_type;
    }

    //添加禁止点击的按钮样式
    var addForbidButton = function (uuid, option) {
        $('#current_table #' + uuid + ' .' + option + ' .btn').prop('disabled', true);
    }

    //添加启用点击的按钮样式
    var addEnableButton = function (uuid, option) {
        $('#current_table #' + uuid + ' .' + option + ' .btn').prop('disabled', false);
    }

    // 添加按钮隐藏的样式
    var addHiddenButton = function (uuid, option) {
        $('#current_table #' + uuid + ' .' + option).hide();
    }

    /**
     * 整机/卷任务运行中的操作控制
     * @param {*} uuid
     * @param {*} taskType
     * @param {*} taskCurrentStage
     * @param {*} row
     */
    const volCdpTaskRunningControlButton = function (uuid, taskType, taskCurrentStage, row) {
        switch (taskType) {
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
                    case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        addEnableButton(uuid, "stopautotakeover");
                        addEnableButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
                        if (row.takeover_config_flag) { // 配置了接管
                            addEnableButton(uuid, "takeover");
                        } else {
                            addForbidButton(uuid, "takeover");
                        }

                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        // 实时备份任务或复制任务，且任务阶段为接管前，则启用  启用自动接管按钮 或 禁用自动接管按钮，其他禁用
                        addEnableButton(uuid, "stopautotakeover");
                        addEnableButton(uuid, "startautotakeover");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
                    case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "startfailback");

                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "startfailback");

                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC: //逆向实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: //回切启动中
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "startfailback");

                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");
                        break;
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
                addForbidButton(uuid, 'takeover');
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "edit");
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        //				addForbidButton(uuid, "stopfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        //				addForbidButton(uuid, "stopfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "takeover");
                        //				addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        break;
                }
                break;
        }
    }

    //卷CDP任务停止任务控制
    var volcdpTaskStopControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskType) {
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
                    case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC:
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //					addForbidButton(uuid, "stopfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //					addForbidButton(uuid, "stopfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER: // 接管中可操作 启动、删除
                        addEnableButton(uuid, "start");
                        addEnableButton(uuid, "delete");

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        break;
                    default:
                        break;
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                //				addForbidButton(uuid, "stopfailback");
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //					addForbidButton(uuid, "stopfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "edit");
                        break;
                    default:
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "edit");
                }
                break;
        }
    }

    // 卷CDP任务错误状态下的任务控制
    const volcdpTaskErrorControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskType) {
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC: //初始化同步
                    case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC: //备份实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC:  //准备切换至实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK: //备机的数据一致性校验
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC:  //准备切换至实时同步
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //				addForbidButton(uuid, "stopfailback");
                        addForbidButton(uuid, "stop");
                        addEnableButton(uuid, "delete");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK: //服务端的数据一致性校验
                        addForbidButton(uuid, "start");
                        addForbidButton(uuid, "delete");
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //				addForbidButton(uuid, "stopfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER: //接管中
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING: //接管启动中
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //				addForbidButton(uuid, "stopfailback");
                        addForbidButton(uuid, "stop");
                        addEnableButton(uuid, "delete");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOFAILBACK_INIT_SYNCVER_STARTING: //逆向初始同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC: //逆向实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: //回切启动中
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "start");
                        addForbidButton(uuid, "edit");
                        addForbidButton(uuid, "delete");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC: //等待执行
                    case CONF.CDP_TASK_RUNNING_STAGE.UNKNOWN: //未知状态
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stop");
                        addEnableButton(uuid, "delete");
                        break;
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
                addForbidButton(uuid, "stop");

                // 错误状态下可以删除
                addEnableButton(uuid, "delete");
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER: //接管中
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING: //接管启动中
                        // addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //					addForbidButton(uuid, "stopfailback");
                        addForbidButton(uuid, "start");
                        // addForbidButton(uuid, "stop");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOFAILBACK_INIT_SYNCVER_STARTING: //逆向初始同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC: //逆向实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: //回切启动中
                        addForbidButton(uuid, "takeover");
                        //					addForbidButton(uuid, "stopfailback");
                        addForbidButton(uuid, "edit");
                        // addForbidButton(uuid, "delete");
                        break;
                }
                // 错误状态下可以删除
                addEnableButton(uuid, "delete");
                break;
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION: // 整机复制出错的情况下启用 删除操作（因为实时任务出错时任务本身就已经处于停止状态）
                // 错误状态下可以删除
                addEnableButton(uuid, "delete");
                break;
            case CONF.TASK_TYPE.PLATFORM_RECOVERY: // 实时整机的跨平台恢复任务，错误状态下，禁用停止，启用删除
                addForbidButton(uuid, "stop");
                addEnableButton(uuid, "delete");
                break;
            default:
                break;
        }
    }

    //卷CDP任务等待状态下的任务控制
    var volCdpTaskWaittingControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskType) {
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:
                // 实时备份任务，且任务阶段为接管前，则启用  启用自动接管按钮 或 禁用自动接管按钮。同时禁用 启动接管、停止接管、启动回切
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.UNKNOWN: // 未知状态
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC: // 等待
                    case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC: // 初始化同步
                        addEnableButton(uuid, "stopautotakeover");
                        addEnableButton(uuid, "startautotakeover");

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC: // 备份实时同步
                        addEnableButton(uuid, "stopautotakeover");
                        addEnableButton(uuid, "startautotakeover");
                        addEnableButton(uuid, "takeover"); // 备份实时同步可以启用接管

                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC: // 等待任务切换到实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK: // 服务端的数据一致性校验
                    case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK: // 备机的数据一致性校验
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK: // 服务端实时数据校验
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER: // 接管中
                        addEnableButton(uuid, "startfailback"); // 启动回切只在接管中可用
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING: // 接管启动中
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC: // 逆向初始同步
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC: // 逆向实时同步
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: // 回切启动中
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    default:
                        break;
                }
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                //				addForbidButton(uuid, "stopfailback");
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                //				addForbidButton(uuid, "stopfailback");
                break;
            case CONF.TASK_TYPE.VOL_CDP_REPLICATION: // 整机复制
                // 等待中可操作删除，不可操作停止
                addForbidButton(uuid, "stop");
                addEnableButton(uuid, 'delete');

                // 接管 回切逻辑和实时备份一致
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.UNKNOWN: // 未知状态
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC: // 等待
                    case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC: // 初始化同步
                        addEnableButton(uuid, "stopautotakeover");
                        addEnableButton(uuid, "startautotakeover");

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC: // 备份实时同步
                        addEnableButton(uuid, "stopautotakeover");
                        addEnableButton(uuid, "startautotakeover");
                        addEnableButton(uuid, "takeover"); // 备份实时同步可以启用接管

                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC: // 等待任务切换到实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK: // 服务端的数据一致性校验
                    case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK: // 备机的数据一致性校验
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK: // 服务端实时数据校验
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER: // 接管中
                        addEnableButton(uuid, "startfailback"); // 启动回切只在接管中可用
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING: // 接管启动中
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC: // 逆向初始同步
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC: // 逆向实时同步
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: // 回切启动中
                        addEnableButton(uuid, "stoptakeover"); // 停止接管在接管之后阶段都可用

                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopautotakeover");
                        addForbidButton(uuid, "startautotakeover");

                        break;
                    default:
                        break;
                }

                break;
            default:
                break;
        }
    }

    //数据库CDP运行状态下任务控制
    var dbCdpTaskRunningControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskCurrentStage) {
            case 10:
            case 11:
            case 20: //初始同步
                addForbidButton(uuid, "pause");
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 21: //实时同步
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 30: //接管启动中
                addForbidButton(uuid, "pause");
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "takeover"); //启动接管
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 31: //接管中
                addForbidButton(uuid, "pause");
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "takeover"); //启动接管
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 40:
            case 50: //逆向初始同步
                addForbidButton(uuid, "pause");
                addForbidButton(uuid, "takeover"); //启动接管
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");
                break;
            case 41:
            case 51: //逆向实时同步
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "pause");
                addForbidButton(uuid, "takeover"); //启动接管
                addForbidButton(uuid, "startfailback");
                break;
        }
    }

    //数据库CDP在停止状态下的任务控制
    var dbCdpTaskStopControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskCurrentStage) {
            case 10:
            case 11:
            case 20: //初始同步
            case 22:
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 21: //实时同步
                // addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 30: //接管启动中
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 31: //接管中
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 41:
            case 51: //逆向实时同步
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "edit");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
        }
    }

    /**
     * 数据库实时复制任务在错误状态下的操作项控制
     * @param {*} uuid
     * @param {*} taskCurrentStage
     */
    const dbCdpCopyTaskErrorControlButton = function (uuid, taskCurrentStage) {
        switch (taskCurrentStage) {
            case CONF.DBCDP_TASK_RUNNING_STAGE.UNKNOWN: // 未知
            case CONF.DBCDP_TASK_RUNNING_STAGE.WAIT_EXEC: // 等待
            case CONF.DBCDP_TASK_RUNNING_STAGE.DICT_EXPORT: // 数据字典导出 初始同步
            case CONF.DBCDP_TASK_RUNNING_STAGE.DICT_IMPORT: // 数据字典导入 初始同步
            case CONF.DBCDP_TASK_RUNNING_STAGE.FULL_SYNC: // 全量数据同步 初始同步
            case CONF.DBCDP_TASK_RUNNING_STAGE.LOG_SYNC: // 日志数据同步 实时同步
            case CONF.DBCDP_TASK_RUNNING_STAGE.CONSTRAINT_IMPORT: // 日志数据同步 约束导入
                // 以上几种任务阶段可操作 启动任务、删除任务、修改任务
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");

                addEnableButton(uuid, "start");
                addEnableButton(uuid, "delete");
                addEnableButton(uuid, "edit");
                break;
            case CONF.DBCDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING: // 接管启动中
                // 接管启动中可操作：启动接管、启动同步、删除任务、修改任务
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");

                addEnableButton(uuid, "start");
                addEnableButton(uuid, "takeover");
                addEnableButton(uuid, "edit");
                addEnableButton(uuid, "delete");
                break;
            case CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_LOG_SYNC: // 回切日志数据同步 逆向实时同步
                // 可操作：启动回切、停止接管
                addForbidButton(uuid, "start");
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "edit");
                addForbidButton(uuid, "delete");
                addForbidButton(uuid, "stopfailback");

                addEnableButton(uuid, "startfailback");
                addEnableButton(uuid, "stoptakeover");
                break;
            default:
                break;
        }
    }

    // 任务控制按钮
    const addOpButton = function () {
        var data = $("#current_table").bootstrapTable("getData");
        //做各模块任务控制
        for (var i = 0; i < data.length; i++) {
            var modules = data[i].module_type_value;
            var status = data[i].job_status_value;
            var strategy = data[i].mode_list;
            var taskType = data[i].job_type_value;
            var taskCurrentStage = data[i].job_stage;
            var dbcdpCurrentStage = data[i].job_dbcdp_stage;
            var uuid = data[i].job_uuid;
            let dbType = parseInt(data[i].db_type);
            let copyAndArchiveTasktypes = [CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.ARCHIVE];

            if (modules == "14") { //exchange无任务
                addForbidButton(uuid, "startDiff");
            }
            if (CONF.MODULE_TYPE.VM == modules) {
                if (CONF.VM_TYPE.INSPURVVDK == data[i].vm_type || CONF.VM_TYPE.LENOVOAIO == data[i].vm_type || CONF.VM_TYPE.KSPHERE == data[i].vm_type) {
                    //无差异备份的虚拟化
                    addHiddenButton(uuid, "startDiff");
                }
            }

            switch (status) {
                case CONF.TASK_STATUS.WAITTING: //等待
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "motion");
                    addForbidButton(uuid, "finishMotion");
                    //卷CDP任务无启动任务相关的时间策略，在等待状态下暂时不支持停止操作
                    if (modules == CONF.MODULE_TYPE.VOL_CDP) {
                        volCdpTaskWaittingControlButton(uuid, taskType, taskCurrentStage);
                    } else if (modules == 12) {
                        //数据库实时可以修改
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                    } else {
                        if (CONF.MODULE_TYPE.VM == modules) {
                            // 虚拟机模块特殊处理 - 可修改非副本的任务
                            if (copyAndArchiveTasktypes.indexOf(Number(taskType)) === -1) {
                                addEnableButton(uuid, "edit");
                            } else {
                                addForbidButton(uuid, "edit");
                            }
                        } else {
                            // 非虚拟机模块不可修改
                            addForbidButton(uuid, "edit");
                        }
                    }
                    //卷CDP任务无启动任务相关的时间策略，在等待状态实际处于新建状态，允许对其进行删除
                    // 并且任务类型不是细粒度恢复
                    if ($.inArray(modules, [CONF.MODULE_TYPE.VOL_CDP, CONF.MODULE_TYPE.DB_CDP]) == -1 || taskType == CONF.TASK_TYPE.GRAIN_RECOVERY) {
                        addForbidButton(uuid, "delete");
                    }
                    //数据库CDP在等待状态下的任务控制
                    if (modules == 12) {
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "takeover"); //启动接管
                        addForbidButton(uuid, "stoptakeover"); //停止接管
                        addForbidButton(uuid, "startfailback"); //启动回切
                        addForbidButton(uuid, "stopfailback"); //停止回切
                    }
                    break;
                case CONF.TASK_STATUS.RUNNING: //运行
                case CONF.TASK_STATUS.STARTING: //启动中
                    addForbidButton(uuid, "delete");
                    if (CONF.MODULE_TYPE.VM != modules) {
                        // 虚拟机模块可修改
                        addForbidButton(uuid, "edit");
                    }
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "startDiff");
                    addForbidButton(uuid, "startIncr");
                    addForbidButton(uuid, "startLog");

                    volCdpTaskRunningControlButton(uuid, taskType, taskCurrentStage, data[i]); //卷cdp任务在运行状态下的控制操作

                    if (CONF.TASK_TYPE.INSTANT_RECOVERY == taskType && CONF.TASK_STATUS.STARTING == status) {
                        addForbidButton(uuid, "motion");
                    }
                    //瞬时恢复任务处于启动中状态时，屏蔽迁移按钮
                    if (CONF.TASK_TYPE.VM_INSTANT_RECOVERY == taskType && CONF.TASK_STATUS.STARTING == status) {
                        addForbidButton(uuid, "motion");
                    }

                    //瞬时恢复任务处于启动中状态时，屏蔽迁移按钮，还要屏蔽停止按钮
                    if (CONF.TASK_TYPE.OS_INSTANT_RECOVERY == taskType && CONF.TASK_STATUS.STARTING == status) {
                        addForbidButton(uuid, "motion");
                        addForbidButton(uuid, "stop");
                    }

                    //数据库CDP在运行状态下的任务控制
                    if (modules == 12) {
                        if (taskType == 46) { // 数据库实时复制
                            dbCdpTaskRunningControlButton(uuid, taskType, dbcdpCurrentStage);
                        } else if (taskType == 47) { // 数据库实时恢复
                            addEnableButton(uuid, 'stop');
                        }
                    }

                    // 文件复制任务或对比任务运行中时禁用启动复制和启动对比操作
                    if (modules == CONF.MODULE_TYPE.FILE_COPY) {
                        addForbidButton(uuid, "startcopy");
                        addForbidButton(uuid, "startcompare");
                    }

                    if (taskType == CONF.TASK_TYPE.INSTANT_RECOVERY_MOTION) { // 虚拟机迁移任务，迁移状态为完成时可启用 完成迁移 操作
                        let migrateStatus = data[i].migrate_status;

                        if (migrateStatus === CONF.FLAG.SET) {
                            addEnableButton(uuid, 'finishMotion');
                        } else {
                            addForbidButton(uuid, 'finishMotion')
                        }
                    }

                    break;
                case CONF.TASK_STATUS.PAUSED: //暂停
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "finishMotion");
                    if (modules == CONF.MODULE_TYPE.VOL_CDP) {
                        addForbidButton(uuid, "takeover"); //启动接管
                        addForbidButton(uuid, "stoptakeover"); //停止接管
                        addForbidButton(uuid, "startfailback"); //启动回切
                        //						addForbidButton(uuid, "stopfailback");  //停止回切
                        addForbidButton(uuid, "createlable"); //创建标签点
                    }
                    if (modules == 12) {
                        //数据库实时
                        addForbidButton(uuid, "stoptakeover"); //停止接管
                        addForbidButton(uuid, "startfailback"); //启动回切
                    }
                    if (CONF.MODULE_TYPE.VM != modules) {
                        // 虚拟机模块可修改
                        addForbidButton(uuid, "edit");
                    }
                    break;
                case CONF.TASK_STATUS.STOPPED: //停止
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "stop");
                    addForbidButton(uuid, "motion");
                    addForbidButton(uuid, "finishMotion");
                    volcdpTaskStopControlButton(uuid, taskType, taskCurrentStage);
                    //数据库CDP在停止状态下的任务控制
                    if (modules == 12) {
                        if (taskType == CONF.TASK_TYPE.CDP_DB_BACKUP) {
                            dbCdpTaskStopControlButton(uuid, taskType, dbcdpCurrentStage);
                        }
                        if (taskType == CONF.TASK_TYPE.CDP_DB_RECOVERY) {
                            addForbidButton(uuid, "edit");
                        }
                    }
                    if (CONF.MODULE_TYPE.VM == modules) {
                        // 虚拟机模块可修改
                        addEnableButton(uuid, "edit");
                    }

                    // 文件复制任务或对比任务停止中时可用启动复制和启动对比操作
                    if (modules == CONF.MODULE_TYPE.FILE_COPY) {
                        addEnableButton(uuid, "startcopy");
                        addEnableButton(uuid, "startcompare");
                    }

                    break;
                case CONF.TASK_STATUS.STOPPING: //停止中
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "startDiff");
                    addForbidButton(uuid, "startIncr");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "startLog");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "motion");
                    addForbidButton(uuid, "finishMotion");

                    if (modules == CONF.MODULE_TYPE.VOL_CDP) {
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopfailback");
                    }

                    if (modules == 12) { //数据库实时
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        addForbidButton(uuid, "stopfailback");
                    }

                    if (CONF.MODULE_TYPE.VM == modules) {
                        // 虚拟机模块可修改
                        addEnableButton(uuid, "edit");
                    }

                    // 文件复制任务或对比任务停止中时禁用启动复制和启动对比操作
                    if (modules == CONF.MODULE_TYPE.FILE_COPY) {
                        addForbidButton(uuid, "startcopy");
                        addForbidButton(uuid, "startcompare");
                    }

                    //停止中状态，变为强制停止
                    $('#' + uuid + ' .stop').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
                    break;
                case CONF.TASK_STATUS.NETWORK_FAULT: //网络故障
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "finishMotion");
                    if (modules == CONF.MODULE_TYPE.VOL_CDP) {
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");

                        // 整机/卷 实时备份 或 复制任务
                        if (taskType == CONF.TASK_TYPE.VOL_CDP_REPLICATION || taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP) {
                            let takeoverFlag = data[i].takeover_config_flag;
                            // 备份实时同步阶段且开启了接管  则 启用接管 按钮
                            if (taskCurrentStage == CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC && takeoverFlag) {
                                addEnableButton(uuid, 'takeover');
                            } else {
                                addForbidButton(uuid, "takeover");
                            }
                        }
                    }
                    if (taskType != CONF.TASK_TYPE.VOL_CDP_BACKUP && taskType != CONF.TASK_TYPE.OS_INSTANT_RECOVERY) {
                        addForbidButton(uuid, "start");
                    } else if (taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP) {
                        if (taskCurrentStage == CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                            || taskCurrentStage == CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
                            || taskCurrentStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC
                        ) { //初始同步或实时备份
                            addForbidButton(uuid, "start");
                        }
                    } else if (taskType == CONF.TASK_TYPE.OS_INSTANT_RECOVERY) {
                        addForbidButton(uuid, "motion");
                    }
                    if (CONF.MODULE_TYPE.VM == modules) {
                        // 虚拟机模块可修改
                        addEnableButton(uuid, "edit");
                    }
                    break;
                case CONF.TASK_STATUS.ABNORMAL: //异常
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "startDiff");
                    addForbidButton(uuid, "startIncr");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "startLog");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "motion");
                    addForbidButton(uuid, "finishMotion");
                    if (modules == CONF.MODULE_TYPE.VOL_CDP) {
                        if (taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP && data[i].auto_takeover_flag == CONF.FLAG.SET) {
                            // volcdp备份
                            addForbidButton(uuid, "stop");
                            addForbidButton(uuid, "takeover"); //启动接管
                            addForbidButton(uuid, "startfailback"); //启动回切
                        } else if (taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER) {
                            // volcdp接管
                            addForbidButton(uuid, "takeover"); //启动接管
                            addForbidButton(uuid, "startfailback"); //启动回切
                        } else {
                            addForbidButton(uuid, "takeover"); //启动接管
                            addForbidButton(uuid, "stoptakeover"); //停止接管
                            addForbidButton(uuid, "startfailback"); //启动回切
                            //						addForbidButton(uuid, "stopfailback");  //停止回切
                        }
                    }
                    break;
                case CONF.TASK_STATUS.ERROR: //错误
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "motion");
                    addForbidButton(uuid, "finishMotion");
                    addForbidButton(uuid, "delete");

                    if (modules != CONF.MODULE_TYPE.VOL_CDP && modules != 12) {
                        if (CONF.MODULE_TYPE.VM != modules) {
                            // 虚拟机模块可修改
                            addForbidButton(uuid, "edit");
                        }
                        addForbidButton(uuid, "delete");
                    }

                    if (modules == CONF.MODULE_TYPE.VOL_CDP) { // 整机/卷实时保护
                        volcdpTaskErrorControlButton(uuid, taskType, taskCurrentStage);
                    }

                    if (modules == CONF.MODULE_TYPE.DB_CDP) { // 数据库实时
                        if (taskType == 46) { // 复制任务
                            dbCdpCopyTaskErrorControlButton(uuid, dbcdpCurrentStage);
                        } else if (taskType == 47) { // 恢复任务
                            addForbidButton(uuid, "stop");
                            addForbidButton(uuid, "edit");

                            // 数据库实时恢复任务出错可以删除
                            addEnableButton(uuid, 'delete');
                        }
                    }

                    if (CONF.MODULE_TYPE.VM == modules) {
                        // 虚拟机模块可修改
                        addEnableButton(uuid, "edit");
                    }
                    break;
                case CONF.TASK_STATUS.SYNC: //任务同步
                    break;
                case CONF.TASK_STATUS.PREPARING: //准备中
                    addForbidButton(uuid, "finishMotion");
                    //细粒度恢复
                    if (taskType == CONF.TASK_TYPE.VM_FILE_RECOVERY) {
                        addForbidButton(uuid, "start");
                        addForbidButton(uuid, "delete");
                    }
                    //操作系统瞬时恢复
                    if (taskType == CONF.TASK_TYPE.OS_INSTANT_RECOVERY) {
                        addForbidButton(uuid, "start");
                        addForbidButton(uuid, "motion");
                        addForbidButton(uuid, "delete");
                        addForbidButton(uuid, "stop");
                    }
                    break;
                case CONF.TASK_STATUS.PAUSING: //暂停中
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "finishMotion");
                    if (modules != CONF.MODULE_TYPE.VOL_CDP) {
                        addForbidButton(uuid, "takeover"); //启动接管
                        addForbidButton(uuid, "stoptakeover"); //停止接管
                        addForbidButton(uuid, "startfailback"); //启动回切
                        //						addForbidButton(uuid, "stopfailback");  //停止回切
                    }
                    break;
                case CONF.TASK_STATUS.FINISHED: //已完成
                    addForbidButton(uuid, "stop");
                    addForbidButton(uuid, "finishMotion");
                    break;
                case CONF.TASK_STATUS.TAKEOVER: //接管
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "stop");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "finishMotion");
                    break;
                case CONF.TASK_STATUS.SUCCESSED: //成功
                    if (taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER || taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP) {
                        switch (taskCurrentStage) {
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                                addForbidButton(uuid, "start");
                                addForbidButton(uuid, "stop");

                                addForbidButton(uuid, "takeover");
                                //							addForbidButton(uuid,"stopfailback");
                                addForbidButton(uuid, "delete");
                                addForbidButton(uuid, "edit");
                                break;
                        }
                    } else if (modules == 12) {
                        if (dbcdpCurrentStage == 53) { //接管回切完成
                            addForbidButton(uuid, "start");
                            addForbidButton(uuid, "stop");
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "takeover");
                            addForbidButton(uuid, "startfailback"); //启动回切
                            addForbidButton(uuid, "stopfailback");
                            addForbidButton(uuid, "stoptakeover"); //停止接管
                            // addForbidButton(uuid, "delete");
                            addForbidButton(uuid, "edit");
                        } else { // 接管中（31）等其他状态
                            addForbidButton(uuid, "start");
                            addForbidButton(uuid, "stop");
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "takeover");
                            addForbidButton(uuid, "stopfailback");
                            addForbidButton(uuid, "delete");
                            addForbidButton(uuid, "edit");
                        }
                    } else if (taskType == CONF.TASK_TYPE.OS_INSTANT_RECOVERY) {
                        //如果是操作系统瞬时恢复任务
                        //允许停止和启动 不能删除 迁移
                        addForbidButton(uuid, "delete");
                        addForbidButton(uuid, "motion");
                    } else {
                        addForbidButton(uuid, "delete"); //正常的成功状态都不能删除
                        addForbidButton(uuid, "finishMotion");
                    }
                    break;
                case CONF.TASK_STATUS.PENDING: //挂起
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "startDiff");
                    addForbidButton(uuid, "startIncr");
                    addForbidButton(uuid, "startLog");
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "finishMotion");
                    addForbidButton(uuid, 'motion');

                    // 文件复制任务或对比任务挂起时禁用启动复制和启动对比操作
                    if (modules == CONF.MODULE_TYPE.FILE_COPY) {
                        addForbidButton(uuid, "startcopy");
                        addForbidButton(uuid, "startcompare");
                    }
                    break;
                case 14: //exchange
                    addForbidButton(uuid, "startDiff");
                    addForbidButton(uuid, "finishMotion");
                    switch (status) {
                        case 1:
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "delete");
                            if (taskType == 1) {
                                addForbidButton(uuid, "edit");
                                addForbidButton(uuid, "startStra");
                            }
                            break;
                        case 2:
                        case 12:
                            addForbidButton(uuid, "delete");
                            addForbidButton(uuid, "start");
                            addForbidButton(uuid, "startStra");
                            addForbidButton(uuid, "startIncr");
                            if (taskType == 1) {
                                addForbidButton(uuid, "edit");
                            }
                            break;
                        case 3:
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "delete");
                            if (taskType == 1) {
                                addForbidButton(uuid, "edit");
                                addForbidButton(uuid, "startStra");
                            }
                            break;
                        case 4:
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "stop");
                            break;
                        case 5:
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "delete");
                            addForbidButton(uuid, "start");
                            if (taskType == 1) {
                                addForbidButton(uuid, "startIncr");
                                addForbidButton(uuid, "startStra");
                            }
                            if (taskType == 1) {
                                addForbidButton(uuid, "edit");
                            }
                            //停止中状态，变为强制停止
                            $('#exhcnageJobTable #' + uuid + ' .stop').html('<a href="javascript:;"><i class="glyphicon glyphicon-stop"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
                            break;
                        case 6:
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "delete");
                            if (taskType == 1) {
                                addForbidButton(uuid, "edit");
                                addForbidButton(uuid, "startStra");
                            }
                            break;
                        case 7:
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "delete");
                            addForbidButton(uuid, "start");
                            if (taskType == 1) {
                                addForbidButton(uuid, "startIncr");
                                addForbidButton(uuid, "startStra");
                                addForbidButton(uuid, "edit");
                            }
                            break;
                        case 8:
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "delete");
                            if (taskType == 1) {
                                addForbidButton(uuid, "edit");
                            }
                            if (taskType == 1) {
                                addForbidButton(uuid, "startStra");
                            }
                            break;
                        case 9:

                            break;
                        case 10:

                            break;
                        case 11:
                            addForbidButton(uuid, "delete");
                            if (taskType == 1) {
                                addForbidButton(uuid, "edit");
                                addForbidButton(uuid, "startStra");
                            }
                            break;
                        case 13:
                            addForbidButton(uuid, "stop");
                            break;
                        case 14:
                            //接管
                            addForbidButton(uuid, "start");
                            addForbidButton(uuid, "stop");
                            addForbidButton(uuid, "edit");
                            addForbidButton(uuid, "delete");
                            //					addForbidButton(vmuuid, "takeover");
                            break;
                    }
                    break;
                case CONF.TASK_STATUS.DELETING: // 删除中，605版本中暂时禁用强制删除按钮，后续删除这段代码
                    // 文件复制任务或对比任务删除中时禁用启动复制和启动对比操作
                    if (modules == CONF.MODULE_TYPE.FILE_COPY) {
                        addForbidButton(uuid, "startcopy");
                        addForbidButton(uuid, "startcompare");
                    }
                    // addForbidButton(uuid, "deleteforce");
                    $('#current_table #dropdown_operate_' + uuid).prop('disabled', true);
                    break;
                default:
                    break;
            }
            //一次性备份|恢复操作
            if (strategy.type == 4) {
                addForbidButton(uuid, "startDiff");
                addForbidButton(uuid, "startIncr");
                addForbidButton(uuid, "startLog");
                if (strategy.timeout) {
                    addForbidButton(uuid, "startStra");
                }
            } else if (modules != CONF.MODULE_TYPE.DB) {
                //数据库模块启动增量和差异不冲突
                if ($.inArray(2, strategy.modeList) != -1 || $.inArray(9, strategy.modeList) != -1) {
                    //增量及永久增量
                    addForbidButton(uuid, "startDiff");
                } else if ($.inArray(3, strategy.modeList) != -1) {
                    addForbidButton(uuid, "startIncr");
                }
            }

            //如果开启归档，禁用增量和差异,暂时只有文件和nas
            if (data[i].archive_flag) {
                addForbidButton(uuid, "startDiff");
                addForbidButton(uuid, "startIncr");
            }

            // cdp备份任务并且配置接管且任务阶段为实时同步，那么此时可以启动接管,并且是运行中
            if (status == CONF.TASK_STATUS.RUNNING && CONF.TASK_TYPE.VOL_CDP_BACKUP == taskType && taskCurrentStage == CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC && data[i].takeover_config_flag) {
                addEnableButton(uuid, "takeover");
            }
        }
    }

    //得到备份间隔描述
    var getStrategyFrequency = function (frequency) {
        var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
        for (var i = 1; i <= 52; i++) {
            if (frequency == "s" + i) {
                if (i == 1) {
                    frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
                } else {
                    frequency = frequencyLang.replace('x', i) + ",";
                }
            }
        }
        return frequency;
    }

    // 新建任务
    var addtaskBtn = `<div class="btn-group" id="addList">
                        <label>
                            <button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn-whitespace addTask" id="addTask" aria-haspopup="true" aria-expanded="false" style="width:118px;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia"></i>
                                <span class="ml4">`+ LANG.UI_JOB_NEW_TASK +`</span>
                            </button>
                            <ul class="dropdown-menu addTaskList" data-stopPropagation="true">
                            <div class="module-tab-label tab-fixed-label">${LANG.UI_BACKUP_DATA_MODULE_FIXED_TIME}</div>
                                <li class="vm_protected"  data-stopPropagation="true"><span>` + LANG.UI_SETTING_VIRTUAL_MACHINE_PROTECTION + `</span><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                    <li class="vmInstantRecovery ">`+ LANG.UI_VISUAL_INSTANT_TASK_NAME +`</li>
                                    <li class="vmGrainRecover">`+ LANG.UI_VISUAL_GRAIN_TASK_NAME +`</li>
                                    <li class="crossRecover">`+ LANG.UI_FILE_CROSS_RESTORE +`</li>
                                    <!--<li class="cloudSync">` + LANG.UI_ADD_TASK_TYPE_CLOUD_SYNC + `</li>-->
                                </ul>
                                </div></li>
                                <li class="aws_protected" data-stopPropagation="true"><label for="" ><span> ` + LANG.UI_PLATFORM_DES_AWS_PROTECTED + ` </span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                    <li class="awsGrainRecover">`+ LANG.UI_VISUAL_GRAIN_TASK_NAME +`</li>
                                    <li class="crossRecover">`+ LANG.UI_FILE_CROSS_RESTORE +`</li>
                                </ul>
                                </div>
                                </li>
                                <li class="pri_cloud_protected" data-stopPropagation="true"><label for="" ><span> ` + LANG.UI_PUBLIC_PRIVATE_CLOUD_PROTECTED + ` </span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                    <li class="awsGrainRecover">`+ LANG.UI_VISUAL_GRAIN_TASK_NAME +`</li>
                                    <li class="crossRecover">`+ LANG.UI_FILE_CROSS_RESTORE +`</li>
                                    <li class="instant">` + LANG.UI_VISUAL_INSTANT_NAME + `</li>
                                    <!--<li class="instantRecover">`+ LANG.UI_VISUAL_INSTANT_NAME +`<i class = "icon-rightArrow"></i><div class="sub-subDiv display-hide">
                                    <ul> 
                                        <li class="instant">` + LANG.UI_VISUAL_INSTANT_NAME + `</li>
                                        <li class="timepoint">` + LANG.UI_INSTANT_TIMEPOINT_NAME + `</li>
                                    </ul>
                                    </div></li>-->
                                </ul>
                                </div>
                                </li>
                                <li class="fs_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_FILE_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                </ul></div></li>
                                <li class="db_protect" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_DATABASE_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                    <li class="drill">` + LANG.UI_DRILLS_TASK + `</li>
                                </ul>
                                </div></li>
                               
                                <li class="nas_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_NAS_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                </ul>
                                </div>
                                </li>
                                <li class="obs_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_PLATFORM_DES_OBS + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                </ul>
                                </div>
                                </li>
                                <li class="exchange_protected" data-stopPropagation="true"><label for="" ><span> `+ LANG.UI_BACKUP_DATA_MODULE_M365 +` </span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                </ul>
                                </div>
                                </li>
                                <li class="hadoop_protected" data-stopPropagation="true"><label for="" ><span> `+ LANG.UI_SETTING_HADOOP_PROTECT +` </span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                <ul> 
                                    <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                    <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                </ul>
                                </div>
                                </li>
                                <!-- <li class="real_time_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_SETTING_VOL_CDP_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                    <ul> 
                                        <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                        <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                        <li class="takeover">` + LANG.UI_ADD_TASK_TYPE_TAKEOVER + `</li>
                                    </ul></div>
                                </li> -->
                                <li class="k8s_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_BACKUP_DATA_MODULE_K8S + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                    <ul> 
                                        <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                        <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                    </ul></div>
                                </li>

                                <li class="machine_os_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_BACKUP_DATA_MODULE_OS + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                    <ul> 
                                        <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                        <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                        <li class="vmInstantRecovery ">`+ LANG.UI_VISUAL_INSTANT_TASK_NAME +`</li>
                                        <li class="vmGrainRecover">`+ LANG.UI_VISUAL_GRAIN_TASK_NAME +`</li>
                                        <li class="crossRecover">`+ LANG.UI_PLATFORM_RECOVERY_NAME +`</li>
                                    </ul></div>
                                </li>
                                
                                <li class="machine_vol_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_VOL_CDP_RECOVER_VOL + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                    <ul> 
                                        <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                        <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                    </ul></div>
                                </li>
                                <li class="data_verify" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_ADD_TASK_TYPE_DATA_VERIFY + `</span></label>
                                </li>
                                <div class="module-tab-label tab-fixed-label">${LANG.UI_BACKUP_DATA_MODULE_REAL_TIME}</div>
                                <li class="cdp_protected" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_ADD_TASK_TYPE_CDP_PROTECT + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                    <ul>
                                    
                                        <li class="cdp_protected_machine">`+ LANG.UI_BACKUP_DATA_MODULE_OS +`<i class = "icon-rightArrow"></i><div class="sub-subDiv display-hide">
                                            <ul> 
                                                <li class="machine_os">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                                <li class="recovery2">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                                <li class="recovery_platform">` + LANG.UI_FILE_CROSS_RESTORE + `</li>
                                                <li class="recovery_grain">` + LANG.UI_RECOVERY_GRAIN + `</li>
                                                <li class="takeover_machine">` + LANG.UI_VOL_CDP_AUTO_TAKEOVER_TITLE + `</li>
                                                <li class="cdp_protected_verify">` + LANG.UI_VOL_CDP_VERIFY_DESC + `</li>
                                            </ul>
                                            </div>
                                        </li>
                                        <li class="cdp_protected_machine_vol">`+ LANG.UI_VOL_CDP_RECOVER_VOL +`<i class = "icon-rightArrow"></i><div class="sub-subDiv display-hide">
                                            <ul> 
                                                <li class="machine_vol">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                                <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                                <li class="takeover_volumn">` + LANG.UI_VOL_CDP_AUTO_TAKEOVER_TITLE + `</li>
                                                <li class="cdp_protected_verify2">` + LANG.UI_VOL_CDP_VERIFY_DESC + `</li>
                                            </ul>
                                            </div>
                                        </li> 
                                    </ul></div>
                                </li>

                                <li class="db_cdp" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_VIRTUAL_DATABASE_REAL_TIME + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                    <ul> 
                                        <li class="backup">` + LANG.UI_SEARCH_BACKUP_TASK + `</li>
                                        <li class="recovery">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                    </ul></div>
                                </li>
                                <div class="module-tab-label tab-fixed-label">${LANG.UI_ADD_TASK_TYPE_DATA_COPY}</div>
                                <li class="data_copy" data-stopPropagation="true"><label for="" ><span>` + LANG.UI_ADD_TASK_TYPE_DATA_COPY + `</span></label><i class = "icon-rightArrow"></i><div class="subDiv">
                                    <ul> 
                                        <li class="machine_os">` + LANG.UI_BACKUP_DATA_MODULE_OS + `</li>
                                        <li class="machine_vol">` + LANG.UI_VOL_CDP_RECOVER_VOL + `</li>
                                        <li class="file">` + LANG.UI_FILE_FILE + `</li>
                                        <li class="dbcdp">` + LANG.UI_VISUAL_MODULE_DB + `<i class = "icon-rightArrow"></i>
                                            <div class="sub-subDiv display-hide">
                                                <ul> 
                                                    <li class="db_copy_task">` + LANG.UI_COPY_TASK + `</li>
                                                    <li class="db_recovery_task">` + LANG.UI_SEARCH_RECOVER_TASK + `</li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul></div>
                                </li>
                                
                            </ul>
                        </label>
                    </div>`

    /**
     * 获取当前任务详情的时间策略
     * @param {*} data
     * @param {*} tasktype
     * @param {*} row
     * @returns
     */
    const getCurrentDetailTimeStrategyHtml = (data, tasktype, row) => {
        let html = ``;
        var timeStrategy_mode;
        var timeStrategy_roll;
        var frequency = "";
        $.each(data.time_strategy, function (k, v) {
            var days = [];
            if (v.roll_flag == true) {
                timeStrategy_roll = '，' + LANG.UI_STRATEGY_ROLL_INTERVAL+' ' + v.roll_interval + ', '+ LANG.UI_STRATEGY_ROLL_OVER_TIME + v.end_time;
                if (tasktype === CONF.TASK_TYPE.DRILL) {  // 演练任务没有滚动配置
                    timeStrategy_roll = '';
                }
            } else {
                if (row.job_type_value == 37) {
                    // 数据验证任务不展示滚动
                    timeStrategy_roll = '';
                } else {
                    timeStrategy_roll = '，' + LANG.UI_STRATEGY_ROLL_NO;
                }
                if (tasktype === CONF.TASK_TYPE.DRILL) {  // 演练任务没有滚动配置
                    timeStrategy_roll = '';
                }
            }
            if (v.mode == 1) {
                timeStrategy_mode = LANG.UI_PUBLIC_BACKUP_FULL;
                //如果是CBR任务 需要改成同步
                if (tasktype == CONF.TASK_TYPE.VM_HUAWEI_CBR_SYNC) {
                    timeStrategy_mode = LANG.UI_CLOUD_PLATFORM_SYNC;
                }
                if (
                    tasktype === CONF.TASK_TYPE.DB_RECOVERY ||
                    tasktype === CONF.TASK_TYPE.DRILL
                ) {  // 恢复策略
                    timeStrategy_mode = LANG.UI_GLOBAL_STRATEGY_TYPE_BY_STRATEGY;
                }
            } else if (v.mode == 2) {
                timeStrategy_mode = LANG.UI_STRATEGY_INCREMENT;
                if (row.module_type_value == CONF.MODULE_TYPE.DB) {
                    let dbType = parseInt(row.db_type);
                    if (dbType === CONF.DB_TYPE.MONGODB) {
                        let hasFullBackupFlag = false;  // 是否有完备
                        for (const timeStrategyItem of data.time_strategy) {
                            if (parseInt(timeStrategyItem.mode) === 1) {
                                hasFullBackupFlag = true;
                                break;
                            }
                        }
                        if (!hasFullBackupFlag) {  // 如果没有完备，只有增量，那么是永久增量
                            timeStrategy_mode = LANG.UI_STRATEGY_PERMANENT_INCREMENT;
                        }
                    }
                }
            } else if (v.mode == 9) {
                timeStrategy_mode = LANG.UI_STRATEGY_PERMANENT_INCREMENT;
            } else if (v.mode == 3) {
                timeStrategy_mode = LANG.UI_PUBLIC_BACKUP_DIFFRENCE;
            } else if (v.mode == 5) {
                timeStrategy_mode = LANG.UI_COPY_TIME_STRATEGY;
            } else if (v.mode == 6) {
                timeStrategy_mode = LANG.UI_ARCHIVE_TIME_STRATEGY;
            } else if (v.mode == 4) {
                if (row.module_type_value == CONF.MODULE_TYPE.DB) {
                    let dbType = parseInt(row.db_type);
                    timeStrategy_mode = LANG.UI_PUBLIC_BACKUP_LOG;
                    switch (dbType) {
                        case CONF.DB_TYPE.ORACLE:
                        case CONF.DB_TYPE.DM:
                        case CONF.DB_TYPE.POSTGRE:
                        case CONF.DB_TYPE.ANTDB:
                        case CONF.DB_TYPE.KINGBASE:
                        case CONF.DB_TYPE.UXDB:
                        case CONF.DB_TYPE.HIGHGO:
                        case CONF.DB_TYPE.OPENGAUSS:
                        case CONF.DB_TYPE.VASTBASE:
                            timeStrategy_mode = LANG.UI_PUBLIC_BACKUP_ARCHIVE_LOG;
                            break;
                    }
                }
            }
            if (row.job_type_value == 37) { //数据验证
                timeStrategy_mode = LANG.UI_STRATEGY_VERTIFY;
            }
            if (row.job_type_value == 2) { //虚拟机恢复
                timeStrategy_mode = LANG.UI_STRATEGY_RECOVERY;
            }
            if (row.job_type_value == 52) { // 跨平台恢复
                timeStrategy_mode = LANG.UI_STRATEGY_RECOVERY;
            }
            if (row.job_type_value == 32) { //卷CDP标签策略
                timeStrategy_mode = '';
            }
            if (row.job_type_value == 65) { // 整机/卷复制
                timeStrategy_mode = '';
            }
            // 副本、归档直接显示策略
            if (row.job_type_value == 17 || row.job_type_value == 18 || row.job_type_value == 19 || row.job_type_value == 20) {
                timeStrategy_mode = '';
                if (v.roll_flag == true) {
                    timeStrategy_roll = '，' + LANG.UI_STRATEGY_ROLL_INTERVAL + ' ' + v.roll_interval + ', '+ LANG.UI_STRATEGY_ROLL_OVER_TIME + v.end_time + '';
                } else {
                    timeStrategy_roll = '，' + LANG.UI_STRATEGY_ROLL_NO;
                }
                if (v.type == 2) {
                    $.each(v.days, function (i, d) {
                        if (d == 1) {
                            days.push(i + 1);
                        }
                    });

                    // 间隔周数
                    frequency = getStrategyFrequency(v.frequency);

                    html += `<p>` + timeStrategy_mode + `` + frequency + LANG.UI_STRATEGY_WEEK_EN + days.join(', ') + `, ` + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `</p>`;
                } else if (v.type == 1) {
                    html += `<p>` + timeStrategy_mode + ` `+ LANG.UI_STRATEGY_DAY_EN + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `</p>`;
                } else if (v.type == 3) {
                    $.each(v.days, function (i, d) {
                        if (d == 1) {
                            days.push(i + 1)
                        }
                    });
                    html += `<p>` + timeStrategy_mode + LANG.UI_STRATEGY_MONTH_EN + days.join(', ') + `, ` + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `</p>`;
                } else if (v.type == 4) {
                    html += `<p>` + LANG.UI_PUBLIC_START_TIME + '： ' + v.start_time + `</p>`;
                }
            } else {
                if (
                    row.job_type_value == 28 &&
                    parseInt(row.db_type) === CONF.DB_TYPE.TIDB &&
                    v.mode == 4  // TiDB数据库备份没有日志备份
                ) {
                    return;
                }
                if (v.type == 2) {
                    $.each(v.days, function (i, d) {
                        if (d == 1) {
                            days.push(i + 1);
                        }
                    })

                    // 间隔周数
                    frequency = getStrategyFrequency(v.frequency);

                    html += `<p>` + timeStrategy_mode + ` (` + frequency + LANG.UI_STRATEGY_WEEK_EN + days.join(', ') + `, ` + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `)</p>`;
                } else if (v.type == 1) {
                    html += `<p>` + timeStrategy_mode + ` (`+ LANG.UI_STRATEGY_DAY_EN + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `)</p>`;
                } else if (v.type == 3) {
                    $.each(v.days, function (i, d) {
                        if (d == 1) {
                            days.push(i + 1)
                        }
                    })
                    html += `<p>` + timeStrategy_mode + ` (`+ LANG.UI_STRATEGY_MONTH_EN + days.join(', ') + `, ` + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `)</p>`;
                } else if (v.type == 4) {
                    // 数据库恢复
                    if (row.job_type_value == CONF.TASK_TYPE.DB_RECOVERY) {
                        html += `<p>` + LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME + ', ' + LANG.UI_JOB_TIMING_RECOVER_TIME + '： ' + v.start_time + `</p>`;
                    } else {
                        html += `<p>` + LANG.UI_PUBLIC_START_TIME + '： ' + v.start_time + `</p>`;
                    }
                }
            }
        });

        return html;
    };

    // 当前任务展开详情（根据接口返回字段是否为空判断是否显示）
    let lastIndex = [-1, -1];

    /**
     * 当前任务展开详情
     * @param {*} index 当前行index
     * @param {*} row 当前行
     * @param {*} element 当前行tr.detail-view td
     */
    const current_detail = function (index, row, element) {
        if (index != lastIndex[1]) {
            lastIndex.push(index);
            $('#current_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
            lastIndex.splice(0, 1);
        }

        Metronic.blockUI({
            target: $(element),
            animate: true,
            timeout: 100,
            allowMultiple: false
        });

        pAjaxRequest({}, '/api/v1/jobs/' + row.job_uuid + '', 'GET', function (res) {
            data = res.data;
            var tasktype = parseInt(data.task_type);

            if ((row.module_type_value == CONF.MODULE_TYPE.VM || row.module_type_value == CONF.MODULE_TYPE.PUBLIC_CLOUD) &&
                tasktype == CONF.TASK_TYPE.RECOVERY && data.time_strategy.length == 0) { //虚拟机立即恢复任务时间策略处理
                var html = `<div style="display: flex; align-items: center"><b>${LANG.UI_STRATEGY_TIME}：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                html += '<p>' + LANG.UI_JOB_ONCE_TIME_RECOVER + '</p>';
                html += `</div></div>`;
                $(element).append(html);
            }

            if (tasktype == CONF.TASK_TYPE.DB_RECOVERY && data.time_strategy.length === 0) { //数据库恢复只有立即恢复
                var html = `<div style="display: flex; align-items: center"><b>${LANG.UI_STRATEGY_TIME}：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                html += '<p>' + LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE + '</p>';
                $(element).append(html);
            }

            if (tasktype == CONF.TASK_TYPE.KUBE_RECOVERY) { // k8s
                if (data.time_strategy.length === 0) { // 立即恢复
                    var html = `<div style="display: flex; align-items: center"><b>${LANG.UI_STRATEGY_TIME}：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                    html += '<p>' + LANG.UI_JOB_ONCE_TIME_RECOVER + '</p>';
                    $(element).append(html);
                } else { // 定时恢复
                    var html = `<div style="display: flex; align-items: center"><b>${LANG.UI_STRATEGY_TIME}：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                    html += '<p>' + LANG.UI_JOB_TIMING_RECOVER + ' ' + '(' + LANG.UI_PUBLIC_START_TIME + '： ' + data.time_strategy[0].start_time + ')' + '</p>';
                    $(element).append(html);
                }
            }

            if (tasktype == CONF.TASK_TYPE.RECOVERY && data.time_strategy.length == 0) { // 定时模块 - 整机、文件的立即恢复

                if ([5, 3, 11].indexOf(row.module_type_value) > -1) {
                    var html = `<div style="display: flex; align-items: center"><b>${LANG.UI_STRATEGY_TIME}：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                    html += '<p>' + LANG.UI_JOB_ONCE_TIME_RECOVER + '</p>';
                    html += `</div></div>`;
                    $(element).append(html);
                }
            }

            $.each(data, function (key, value) {
                if (value != [] && value != '') {
                    switch (key) {
                        case 'backup_info':
                            $(element).append(``);
                            break;
                        case 'recovery_info':
                            $(element).append();
                            break;
                        case 'reserve_strategy': // 保留策略
                            var reserve_strategy_type;
                            let reserve_strategy_unit;
                            var des = '';
                            if (row.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                                // 磁带没有保留策略
                                return '';
                            }

                            // 定时备份模块展示保留策略 - 保留类型（按备份点保留还是按备份链保留）
                            if(CONF.TIMING_MODULE_TYPE_ARR.indexOf(row.module_type_value) > -1 ){
                                if (row.job_type_value === CONF.TASK_TYPE.BACKUP_COPY) { // 副本任务的保留策略类型
                                    switch (data.reserve_strategy.strategy_mode) {
                                        case CONF.RESERVE_STRATEGY_MODE.POINT: // 1 按副本点保留
                                            des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_COPY_STRATEGY_RESERVE_MODE_POINT }` + '<br>';
                                            break;
                                        case CONF.RESERVE_STRATEGY_MODE.CHIAN: // 2：按副本链保留
                                            des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_COPY_STRATEGY_RESERVE_MODE_CHAIN}` + '<br>';
                                            break;
                                        default:
                                            break;
                                    }
                                } else if (row.job_type_value === CONF.TASK_TYPE.ARCHIVE) { // 归档任务的保留策略类型
                                    switch (data.reserve_strategy.strategy_mode) {
                                        case CONF.RESERVE_STRATEGY_MODE.POINT: // 1 按归档点保留
                                            des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_ARCHIVE_STRATEGY_RESERVE_MODE_POINT }` + '<br>';
                                            break;
                                        case CONF.RESERVE_STRATEGY_MODE.CHIAN: // 2：按归档链保留
                                            des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_ARCHIVE_STRATEGY_RESERVE_MODE_CHAIN}` + '<br>';
                                            break;
                                        default:
                                            break;
                                    }
                                } else {
                                    switch (data.reserve_strategy.strategy_mode) {
                                        case CONF.RESERVE_STRATEGY_MODE.POINT: // 1 备份点保留
                                            des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT }` + '<br>';
                                            break;
                                        case CONF.RESERVE_STRATEGY_MODE.CHIAN: // 2：按备份链保留
                                            des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN}` + '<br>';
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }

                            // 数据库有保留类型模式
                            if(row.module_type_value == CONF.MODULE_TYPE.DB){
                                if (row.db_type == CONF.DB_TYPE.MONGODB) {  // MongoDB有保留类型模式
                                    if (CONF.RESERVE_STRATEGY_MODE.POINT == data.reserve_strategy.strategy_mode) {
                                        des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT }` + '<br>';
                                    } else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reserve_strategy.strategy_mode) {
                                        des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN}` + '<br>';
                                    }
                                } else if (row.db_type == CONF.DB_TYPE.ORACLE && row.db_job_type === 'slave') {  // Oracle归档日志任务没有保留策略
                                    return;
                                } else {  // 其他数据库为按链保留
                                    des += `${LANG.UI_RESERVE_RETENTION_TYPE}：${LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN}` + '<br>';
                                }
                            }

                            if (data.reserve_strategy.type == 1) {
                                reserve_strategy_type = `${LANG.UI_RESERVE_RETENTION_MODE}：${LANG.UI_STRATEGY_RESERVE_NUM}<br>`;;
                                reserve_strategy_unit = `${LANG.UI_STRATEGY_VALUE}：`;
                            } else if (data.reserve_strategy.type == 2) {
                                reserve_strategy_type = `${LANG.UI_RESERVE_RETENTION_MODE}：${LANG.UI_STRATEGY_RESERVE_DAY}<br>`;;
                                reserve_strategy_unit = `${LANG.UI_STRATEGY_VALUE}：`;;
                                if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                                    reserve_strategy_unit = `${LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY}：`;
                                }
                            } else {
                                reserve_strategy_type = `${LANG.UI_RESERVE_RETENTION_MODE}：${LANG.UI_STRATEGY_PERMANENT_RESERVE}`;
                            }

                            if (data.reserve_strategy.type == 3) {
                                des += reserve_strategy_type;
                            } else {
                                des += reserve_strategy_type + reserve_strategy_unit + data.reserve_strategy.value;
                            }

                            if (data.reserve_strategy.value == 0) {
                                return;
                            }

                            if (data.task_type === 17 && data.time_strategy_backup_type === 'oncetime') { // 一次性副本不显示保留策略
                                $(element).append('');
                            } else {
                                $(element).append(
                                    '<div style="display:flex; align-items:baseline"><b>'+ LANG.UI_STRATEGY_RESERVE+'：</b><div style="display:inline-block" id= "reserve_strategy_detail"><p>' + des + '</p></div></div>'
                                );
                            }

                            if (data.reserve_strategy.gfs_info.week || data.reserve_strategy.gfs_info.month || data.reserve_strategy.gfs_info.year) {
                                $(element).find('#reserve_strategy_detail').append(
                                    `<p>`+ LANG.UI_SETTING_VM_GFS_STRATEGY_ON +`</p>
                                    <p class="gfs-conf">` + LANG.UI_JOB_GFS_CONFIGURATION + `</p>`
                                );
                                if (data.reserve_strategy.gfs_info.week) {
                                    let GFSday = data.reserve_strategy.gfs_info.week[0];
                                    if (GFSday === 7) {
                                        GFSday = LANG.UI_STRATEGY_DAYS;
                                    }
                                    $(element).find('#reserve_strategy_detail').append(
                                        `<p>`+ LANG.UI_STRATEGY_EVERY_WEEK +`${GFSday}`+ LANG.UI_STRATEGY_START_RETENTION +`${data.reserve_strategy.gfs_info.week[1]}`+LANG.UI_SETTING_GFS_WEEKS +`</p>`
                                    );
                                }
                                if (data.reserve_strategy.gfs_info.month) {
                                    let GFSday = data.reserve_strategy.gfs_info.month[0];
                                    if (GFSday == 1) {
                                        $(element).find('#reserve_strategy_detail').append(
                                            `<p>`+ LANG.UI_STRATEGY_EVERY_MONTH_FIRST_WEEK + LANG.UI_STRATEGY_START_RETENTION + `${data.reserve_strategy.gfs_info.month[1]}`+LANG.UI_SETTING_GFS_MONTHS+`</p>`
                                        );
                                    } else if (GFSday == 2) {
                                        $(element).find('#reserve_strategy_detail').append(
                                            `<p>`+ LANG.UI_STRATEGY_EVERY_MONTH_LAST_WEEK + LANG.UI_STRATEGY_START_RETENTION +`${data.reserve_strategy.gfs_info.month[1]}`+LANG.UI_SETTING_GFS_MONTHS+`</p>`
                                        );
                                    }
                                }
                                if (data.reserve_strategy.gfs_info.year) {
                                    let GFSday = data.reserve_strategy.gfs_info.year[0];
                                    $(element).find('#reserve_strategy_detail').append(
                                        `<p>`+ LANG.UI_SETTING_VM_GFS_EVERY_YEAR +`${GFSday}`+LANG.UI_SETTING_VM_GFS_MONTHS+ LANG.UI_STRATEGY_START_RETENTION +`${data.reserve_strategy.gfs_info.year[1]}`+LANG.UI_SETTING_GFS_YEARS+`</p>`
                                    );
                                }
                            }
                            break;
                        case 'takeover_info':
                            var title = LANG.UI_VOL_CDP_JOB_TAKEOVER_INFO;
                            if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) {
                                title = LANG.UI_VOL_CDP_JOB_VERIFY_INFO;
                            }
                            var html = `<div style="display: flex; align-items: center"><b>`+ title +`：</b><div style='display:inline-block'>`;
                            $.each(data.takeover_info, function (k, v) {
                                html += `<p>`+ LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME +`：` + v.takeover_timestamp + `</p>`;
                            })

                            html += `</div></div>`;
                            $(element).append(html);
                            break;
                        case 'time_strategy_backup_type':  // 时间策略类别
                            var allowTaskType = [
                                CONF.TASK_TYPE.BACKUP,
                                CONF.TASK_TYPE.DB_BACKUP,
                                CONF.TASK_TYPE.OS_BACKUP,
                                CONF.TASK_TYPE.KUBE_BACKUP
                            ];
                            if (!allowTaskType.includes(data.task_type)) {
                                break;
                            }
                            if (
                                parseInt(data.task_type) === CONF.TASK_TYPE.DB_BACKUP &&  // 数据库备份任务
                                parseInt(data.sub_module_type) === CONF.DB_TYPE.TIDB &&  // TiDB数据库
                                typeof data.depend_task_uuid === 'string' &&  // TiDB数据库有关联任务
                                data.depend_task_uuid.length
                            ) {
                                break;
                            }
                            let typeDes = LANG.UI_BACKUP_USE_STRATEGY;
                            switch (data.time_strategy_backup_type) {
                                case 'oncetime':
                                    typeDes = LANG.UI_BACKUP_ONCE;
                                    break;
                                case 'manual':
                                    typeDes = LANG.UI_BACKUP_MANUAL;
                                    break;
                            }
                            var html = `<div style="display: flex; align-items: center">
                                <b>${LANG.UI_BACKUP_TYPE}：</b>
                                <div style="display: inline-block">
                                    <p>${typeDes}</p>
                                </div>
                            </div>`
                            $(element).append(html);
                            break;
                        case 'time_strategy': // 时间策略
                            var html = '';
                            if (row.job_type_value == 32 || row.job_type_value == 65) { //卷CDP或复制任务标签策略
                                html = `<div style="display: flex; align-items: center"><b>` + LANG.UI_VOL_CDP_JOB_DETAILS_LABEL_STRATEGY + `：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                            } else {
                                if (tasktype !== CONF.TASK_TYPE.KUBE_RECOVERY) { // K8S的恢复上面已处理，就不走下面
                                    html = `<div style="display: flex; align-items: center"><b>` + LANG.UI_STRATEGY_TIME + `：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                                }
                            }

                            if (
                                parseInt(data.task_type) === CONF.TASK_TYPE.DB_BACKUP &&  // 数据库备份任务
                                parseInt(data.sub_module_type) === CONF.DB_TYPE.TIDB &&  // TiDB数据库
                                typeof data.depend_task_uuid === 'string' &&  // TiDB数据库有关联任务
                                data.depend_task_uuid.length
                            ) {
                                // TiDB日志备份任务不显示时间策略
                                break;
                            } else {
                                if (tasktype !== CONF.TASK_TYPE.KUBE_RECOVERY) { // K8S的恢复上面已处理，就不走getCurrentDetailTimeStrategyHtml
                                    html += getCurrentDetailTimeStrategyHtml(data, tasktype, row);
                                }
                            }
                            html += `</div></div>`;
                            switch (data.task_type) {
                                case CONF.TASK_TYPE.FILE_COPY://文件复制
                                    html = `<p><b>` + LANG.UI_FILE_COPY_INTERVAL + '：</b>' + data.time_strategy[0].roll_interval + `</p>`;
                                    html += `<p><b>` + LANG.UI_FILE_COPY_FIRST_START_TIME + '：</b>' + data.time_strategy[0].first_start_time + `</p>`;
                                    break;
                            }

                            if (tasktype === CONF.TASK_TYPE.KUBE_RECOVERY) { //K8S的定时恢复的下次运行时间就是定时开始时间
                                html += `<p><b>` + LANG.UI_PUBLIC_NEXT_RUN_TIME + '：</b>' + data.time_strategy[0].start_time + `</p>`;
                            } else {
                                html += `<p><b>` + LANG.UI_PUBLIC_NEXT_RUN_TIME + '：</b>' + data.next_time + `</p>`;
                            }

                            $(element).append(html);
                            break;
                        case 'agent_info':
                            var des = LANG.UI_PUBLIC_CLIENT;

                            // 整机或卷恢复
                            if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_RECOVERY) {
                                des = LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_CLIENT;
                            }

                            // 整机或卷接管
                            if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_TAKEOVER) {
                                if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) {
                                    des = LANG.UI_VOL_CDP_JOB_DETAILS_VERIFY_CLIENT;
                                } else {
                                    des = LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_CLIENT;
                                }
                            }

                            $(element).append(
                                '<p><b>' + des + '：</b>' + data.agent_info + '</p>',
                            );
                            break;
                        case 'time_point':
                            let timepointStr = '';
                            let timepointNum = data.time_point.timepoint.length;
                            $.each(data.time_point.timepoint, function (i, v) {
                                if (i < timepointNum - 1) {
                                    timepointStr += v + ' / '
                                } else {
                                    timepointStr += v
                                }
                            });
                            $(element).append(
                                '<p><b>'+ LANG.UI_JOB_HIS_BAK_TIMEPOINT +'：</b>' + timepointStr + '</p>',
                            );
                            break;
                        case 'transport_strategy':
                            var compress_flag = '';
                            if (value.compress_flag) {
                                //是否有压缩
                                if (value.compress_flag == true) {
                                    compress_flag = LANG.UI_COPY_BACK_COMPRESS+ '：'+LANG.UI_PUBLIC_ON_ONE;
                                } else {
                                    compress_flag = LANG.UI_COPY_BACK_COMPRESS+ '：'+LANG.UI_PUBLIC_OFF_ONE;
                                }
                            }

                            $(element).append(`<p><b>`+ LANG.UI_STRATEGY_TRANSFER +`：</b> `+ LANG.UI_COPY_BACK_ENCRYPT +`：` + value.encrypt_flag + `， `+ LANG.UI_COPY_BACK_COMPRESS +`：` + value.compress_flag + ` </p>`);
                            break;
                        case 'grain_detail':
                            var html = "";
                            html += "<p><b>" + LANG.UI_VM_OS + ': </b>' + data.grain_detail.os_type + '</p>';
                            html += "<p><b>" + LANG.UI_PUBLIC_TIMEPOINT + ': </b>' + data.grain_detail.timepoint + '</p>';
                            $(element).append(html);
                            break;
                        case 'instant_detail':
                            var detail = '';
                            var taskType = row.job_type_value; //任务类型
                            if (taskType == CONF.TASK_TYPE.OS_INSTANT_RECOVERY) {
                                //操作系统瞬时恢复
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.instant_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT_NAME + ': </b>' + data.instant_detail.sourcehost + '</p>';
                                detail += "<p><b>" + LANG.UI_PLATFORM_RECOVERY_TARGET_OBJECT_NAME + ': </b>' + data.instant_detail.targethost + '</p>';
                                detail += "<p><b>" + LANG.UI_OS_CACHE_LOCATION + ': </b>' + data.instant_detail.cachetarget + '</p>';
                            }
                            if (taskType == CONF.TASK_TYPE.INSTANT_RECOVERY) {
                                //虚拟机瞬时恢复
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.instant_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_OLD_NAME + ': </b>' + data.instant_detail.sourcehost + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_INSTANT_NEW_NAME + ': </b>' + data.instant_detail.targethost + '</p>';
                            }
                            $(element).append(detail);
                            break;
                        case 'motion_detail':
                            var detail = '';
                            var taskType = row.job_type_value; //任务类型
                            if (taskType == CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION) {
                                //操作系统
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.motion_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT_NAME + ': </b>' + data.motion_detail.sourcehost + '</p>';
                                detail += "<p><b>" + LANG.UI_PLATFORM_RECOVERY_TARGET_OBJECT_NAME + ': </b>' + data.motion_detail.targethost + '</p>';
                            }
                            if (taskType == CONF.TASK_TYPE.INSTANT_RECOVERY_MOTION) {
                                //vm
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.motion_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_OLD_NAME + ': </b>' + data.motion_detail.sourcehost + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_MOTION_NEW_NAME + ': </b>' + data.motion_detail.targethost + '</p>';
                            }
                            $(element).append(detail);
                            break;
                        case 'dbcdp_detail':
                            var detail = '';
                            var firstName = LANG.UI_BACKUP_FILE_PRODUCTHOST, secondName = LANG.UI_BACKUP_FILE_BAKHOST;
                            detail += "<p><b>" + LANG.UI_RECOVERY_RESOURSE + ': </b>' + data.dbcdp_detail.productdes + '</p>';
                            detail += "<p><b>" + LANG.UI_RECOVERY_GOAL + ': </b>' + data.dbcdp_detail.standbydes + '</p>';
                            if(22 == data.dbcdp_detail.tasktype || 25 == data.dbcdp_detail.tasktype){
                                firstName = LANG.UI_RECOVERY_RESOURSE;
                                secondName = LANG.UI_RECOVERY_GOAL;
                                detail = "";
                                detail += "<p><b>" + LANG.UI_RECOVERY_RESOURSE + ': </b>' + data.dbcdp_detail.standbydes + '</p>';
                                detail += "<p><b>" + LANG.UI_RECOVERY_GOAL + ': </b>' + data.dbcdp_detail.productdes + '</p>';
                            }
                            detail += "</td></tr>";
                            $(element).append(detail);
                            break;
                        case 'sync_info':
                            var detail = '';
                            var delayLoadTime = '--';
                            if (data.sync_info.delay_load_time != 0) {
                                delayLoadTime = delayLoadTime;
                            }
                            detail += "<p><b>" + LANG.UI_JOB_DELAY_LOAD_TIME + '： </b>' + delayLoadTime + '</p>';
                            detail += "<p><b>" + LANG.UI_JOB_SOURCE_AGENT + '： </b>' + data.sync_info.hostname + '(' + data.sync_info.ip + ')' + '</p>';
                            $(element).append(detail);
                            break;
                        case 'dbcdp_sync_info':
                            var detail = '';
                            var delayLoadTitle = LANG.UI_DB_CDP_BACKUP_LOAD_TIME;
                            var delayLoadTime = LANG.UI_PUBLIC_OFF;
                            var autoTakeoverDes = '';
                            if (data.dbcdp_sync_info.delay_load_time != 0) {
                                delayLoadTitle = LANG.UI_JOB_DELAY_LOAD_TIME
                                delayLoadTime = data.dbcdp_sync_info.delay_load_time;
                            }

                            if (data.dbcdp_sync_info.auto_takeover_flag == CONF.FLAG.SET) {
                                autoTakeoverDes = LANG.UI_PUBLIC_ON;
                            } else {
                                autoTakeoverDes = LANG.UI_PUBLIC_OFF;
                            }
                            detail += "<p><b>" + delayLoadTitle + '： </b>' + delayLoadTime + '</p>';
                            detail += "<p><b>" + LANG.UI_JOB_DETAIL_SOURCE_DB_INSTANCE + '： </b>' + data.dbcdp_sync_info.sourceDbInstance + '</p>';
                            detail += "<p><b>" + LANG.UI_JOB_DETAIL_TARGET_DB_INSTANCE + '： </b>' + data.dbcdp_sync_info.targetDbInstance + '</p>';
                            detail += "<p><b>" + LANG.UI_JOB_DETAIL_TAKEOVER_FAILBACK_DB_INSTANCE + '： </b>' + data.dbcdp_sync_info.failbackDbInstance + '</p>';
                            detail += "<p><b>" + LANG.UI_VOL_CDP_JOB_DETAILS_AUTO_TAKEOVER + '： </b>' + autoTakeoverDes + '</p>';
                            $(element).append(detail);
                            break;
                        case 'dbcdp_recover_info':
                            var detail = '';
                            var timepoint = '--';
                            var recoverTypeDes = '';
                            if (data.dbcdp_recover_info.recovery_target_datetime != 0) {
                                timepoint = data.dbcdp_recover_info.recovery_target_datetime;
                            }

                            if (data.dbcdp_recover_info.recovery_type == CONF.FLAG.SET) {
                                recoverTypeDes = LANG.UI_JOB_DETAIL_ALL_AND_INCREASE;
                            } else {
                                recoverTypeDes = LANG.UI_JOB_DETAIL_INCREASE;
                            }

                            detail += "<p><b>" + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIME_POINT + '： </b>' + timepoint + '</p>';
                            detail += "<p><b>" + LANG.UI_JOB_DETAIL_SOURCE_DB_INSTANCE + '： </b>' + data.dbcdp_recover_info.source_app_service_name + '</p>';
                            detail += "<p><b>" + LANG.UI_JOB_DETAIL_TARGET_DB_INSTANCE + '： </b>' + data.dbcdp_recover_info.target_app_service_name + '</p>';
                            detail += "<p><b>" + LANG.UI_JOB_DETAIL_RECOVER_DATA_TYPE + '： </b>' + recoverTypeDes + '</p>';
                            $(element).append(detail);
                            break;
                    }
                } else {
                    if (row.job_type_value == CONF.TASK_TYPE.SURE_BACKUP && key == 'time_strategy') {
                        // 验证任务显示"时间策略：--"
                        var html = `<div style="display: flex; align-items: center"><b>${LANG.UI_STRATEGY_TIME}：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                        html += '--';
                        $(element).append(html);
                    }
                }
            });
            setTimeout(Metronic.unblockUI($(element)), 10);
        });
    }

    //临时阻止旧数据库实时任务的批量操作
    var preventDBCDPBatchOp = function (module) {
        if (module == 10000) {
            $('.batch-start').removeClass('batch_start_active');
            $('.batch-start i').removeClass('icon-start');
            $('.batch-start span').removeClass('bgb');

            $('.batch-stop').removeClass('batch_stop_active');
            $('.batch-stop i').removeClass('icon-stop');
            $('.batch-stop span').removeClass('bgb');

            $('.batch-delete').removeClass('batch_delete_active');
            $('.batch-delete i').removeClass('icon-delete');
            $('.batch-delete span').removeClass('bgb');
        }
    }

    // 卷实时某些阶段不可批量停止判断
    var preventVolCDPBatchOp = function (row) {
        let jobType = row.job_type_value;
        let stage = row.job_stage;
        if (jobType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER) {
            $('.batch-stop').removeClass('batch_stop_active');
            $('.batch-stop i').removeClass('icon-stop');
            $('.batch-stop span').removeClass('bgb');
        }
        if (jobType == CONF.TASK_TYPE.VOL_CDP_BACKUP) {
            if (stage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING || stage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER
                || stage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING || stage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING
                || stage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || stage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
            ) {
                $('.batch-stop').removeClass('batch_stop_active');
                $('.batch-stop i').removeClass('icon-stop');
                $('.batch-stop span').removeClass('bgb');
            }
        }
    }

    // 新数据库实时批量停止判断,在回切完成阶段（53）下不可批量停止，实时任务不能启用策略
    var preventNewDBCDPBatchOp = function (row) {
        let jobType = row.job_type_value;
        let stage = row.job_dbcdp_stage;

        if (jobType == CONF.TASK_TYPE.CDP_DB_BACKUP) {
            $('.batch-start').removeClass('batch_start_active');
            $('.batch-start i').removeClass('icon-start');
            $('.batch-start span').removeClass('bgb');
            if (stage == CONF.DBCDP_TASK_RUNNING_STAGE.FAILBACK_SUCCESSED) {
                $('.batch-stop').removeClass('batch_stop_active');
                $('.batch-stop i').removeClass('icon-stop');
                $('.batch-stop span').removeClass('bgb');
            }
        }
    }

    var tableInit = function () {
        //任务的操作事件
        var operates = {
            'click .btn': function (event, value, row, index) {
                btnRecord(event.currentTarget);
            },
            'click .start': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJob(row);
                });
            },
            'click .stop': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    stopJob(row);
                });
            },
            'click .startStra': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJobUnify(row, 0);
                });
            },
            'click .startDiff': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJobUnify(row, 3);
                });
            },
            'click .startIncr': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJobUnify(row, 2);
                });
            },
            'click .startLog': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJobUnify(row, 4);
                });
            },

            'click .createlable': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJobUnify(row, 4);
                });
            },
            'click .delete': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    deleteJob(row, false);
                });
            },
            'click .deleteforce': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    deleteJob(row, true);
                });
            },
            'click .pause': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    pauseJob(row, 4);
                });
            },
            'click .motion': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    motion(row, 4);
                });
            },
            'click .takeover': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    takeover(row, 4);
                });
            },
            'click .stoptakeover': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    stoptakeover(row, 4);
                });
            },
            'click .stopautotakeover': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    stopautotakeover(row);
                });
            },
            'click .startautotakeover': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startautotakeover(row);
                });
            },
            'click .startfailback': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startfailback(row, 4);
                });
            },
            'click .edit': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    editJob(row, 4);
                });
            },
            'click .startcopy': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJobUnify(row, 62);
                });
            },
            'click .startcompare': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    startJobUnify(row, 63);
                });
            },
            'click .finishMotion': function (event, value, row, index) {
                // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
                checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: row.user_uuid, auth: 'current_job' }, () => {
                    finishMotionJob(row, 63);
                });
            },
        }

        let operateColumnVisible = CONF.PERMISSION_ARR.indexOf('p_current_job_manager') > -1; // 当前任务是否分配管理权限标记

        var options = {
            toolbarId: '#vin_current_toolbar',
            vin_toolbar: '.vin_current_toolbar',
            vin_url: '/api/v1/jobs',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                params.accurateFlag = accurateFlag;
                if (JSON.parse(window.localStorage.getItem('current_table_BsTable'))) {
                    // 优化任务列表后，需要这个参数获取被隐藏的列，接口对应不显示也不查询这些列
                    params.hiddenFields = JSON.parse(window.localStorage.getItem('current_table_BsTable')).join(); //如果有本地缓存，直接取本地缓存的列
                }
                params = $.extend(params, getParams());
                return params;
            },
            exportSettings: {
                showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
                custom: [
                    {
                        label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
                        class: 'export-all-excel'
                    }
                ]
            },
            sortName: 'create_time',
            sortOrder: 'desc',
            placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            paginationLoop: false,
            hideColumns: "back_node,back_storage,running_time,vcenter_hypervisor,vcenter_name,db_type,orchestration_name,current_stage_value", //默认要隐藏的列，以“,”分割的字符串，没有就不写
            uniqueId: 'job_uuid',
            changeHeightBtn: true, //改变高度按钮
            batchOperation: true, // 批量操作
            detailFormatter: current_detail,
            filterBtnId: 'current_job_filter_btn', // 过滤器组件button id
            dateRangePickerId: 'current_job_datepicker', // 日期选择器组件button id
            resizable: true,
            onRefresh: function (params) {
                $("#current_table").bootstrapTable('hideLoading');
            },
            LoadSuccess: function (a, b, c) {
                // 加载成功执行的回调函数，主要是自动刷新判断批量操作按钮是否可操作
                let selectedRow = $('#current_table').bootstrapTable("getSelections");
                $.each(selectedRow, function (index) {
                    if (selectedRow[index].job_status_value != 4) {
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }
                    if (selectedRow[index].module_type_value == 10000) {
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }
                    if (selectedRow[index].job_status_value == 4 || selectedRow[index].job_status_value == 13 || selectedRow[index].job_status_value == 14) {
                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');
                        if ($.inArray(selectedRow[index].job_type_value, [2, 4, 6, 7, 8, 14, 15, 22, 25, 29, 33, 36, 43, 49]) !== -1) { //恢复任务不能批量启动策略
                            $('.batch-start').removeClass('batch_start_active');
                            $('.batch-start i').removeClass('icon-start');
                            $('.batch-start span').removeClass('bgb');
                        }
                    }
                    if (selectedRow[index].job_status_value == 1 || selectedRow[index].job_status_value == 2 || selectedRow[index].job_status_value == 12 ||
                        selectedRow[index].job_status_value == 3 || selectedRow[index].job_status_value == 5 || selectedRow[index].job_status_value == 6 ||
                        selectedRow[index].job_status_value == 7 || selectedRow[index].job_status_value == 8 || selectedRow[index].job_status_value == 11
                    ) {
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                    if (deleteFlag) {
                        $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');

                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');

                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }
                })
                deleteFlag = false;
            },
            // onCheck，onUncheck，onUncheckAll，onCheckAll 四个方法主要是为了控制勾选后批量操作的逻辑
            onCheck: function (row, $element) {
                var selectedRow = $('#current_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;

                $('#current_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");

                if (selectedRow.length == 0) {
                    $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                    $('.batch-start').removeClass('batch_start_active');
                    $('.batch-start i').removeClass('icon-start');
                    $('.batch-start span').removeClass('bgb');

                    $('.batch-stop').removeClass('batch_stop_active');
                    $('.batch-stop i').removeClass('icon-stop');
                    $('.batch-stop span').removeClass('bgb');

                    $('.batch-delete').removeClass('batch_delete_active');
                    $('.batch-delete i').removeClass('icon-delete');
                    $('.batch-delete span').removeClass('bgb');
                } else if (selectedRow.length > 0) {
                    $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>'+LANG.UI_JOB_SELECTED_ROWS+'' + selectedRow.length + '');
                    $('.batch-start').addClass('batch_start_active');
                    $('.batch-start i').addClass('icon-start');
                    $('.batch-start span').addClass('bgb');
                    $('.batch-stop').addClass('batch_stop_active');
                    $('.batch-stop i').addClass('icon-stop');
                    $('.batch-stop span').addClass('bgb');
                    $('.batch-delete').addClass('batch_delete_active');
                    $('.batch-delete i').addClass('icon-delete');
                    $('.batch-delete span').addClass('bgb');
                }
                $.each(selectedRow, function (index) {
                    preventDBCDPBatchOp(selectedRow[index].module_type_value);
                    preventVolCDPBatchOp(selectedRow[index]); //卷实时接管验证任务不可批量停止,备份任务要看阶段
                    preventNewDBCDPBatchOp(selectedRow[index]); //新数据库实时批量操作
                    if (selectedRow[index].job_status_value != 4) {
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }
                    if (selectedRow[index].job_status_value == 4 || selectedRow[index].job_status_value == 13 || selectedRow[index].job_status_value == 14) {
                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');
                    }
                    if (selectedRow[index].job_status_value == 1 || selectedRow[index].job_status_value == 2 || selectedRow[index].job_status_value == 12 ||
                        selectedRow[index].job_status_value == 3 || selectedRow[index].job_status_value == 5 || selectedRow[index].job_status_value == 6 ||
                        selectedRow[index].job_status_value == 7 || selectedRow[index].job_status_value == 8 || selectedRow[index].job_status_value == 11) {
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                    if (20 == selectedRow[index].job_status_value) {
                        // 删除中的状态，禁用所有批量操作
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }

                    if ($.inArray(selectedRow[index].job_type_value, [2, 4, 6, 7, 8, 14, 15, 22, 25, 29, 33, 36, 43, 49]) !== -1) { //恢复任务不能批量启动
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                    if ($.inArray(selectedRow[index].job_type_value, [32, 33, 34]) !== -1) { //卷cdp等待不能批量停止，不能批量启动策略
                        if (selectedRow[index].job_status_value == 1) { //等待
                            $('.batch-stop').removeClass('batch_stop_active');
                            $('.batch-stop i').removeClass('icon-stop');
                            $('.batch-stop span').removeClass('bgb');
                        }
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                })
            },
            onUncheck: function (row, $element) {
                var selectedRow = $('#current_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                if (selectedRow.length == 0) {
                    $('#current_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                    $('#current_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");

                    $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                    $('.batch-start').removeClass('batch_start_active');
                    $('.batch-start i').removeClass('icon-start');
                    $('.batch-start span').removeClass('bgb');

                    $('.batch-stop').removeClass('batch_stop_active');
                    $('.batch-stop i').removeClass('icon-stop');
                    $('.batch-stop span').removeClass('bgb');

                    $('.batch-delete').removeClass('batch_delete_active');
                    $('.batch-delete i').removeClass('icon-delete');
                    $('.batch-delete span').removeClass('bgb');
                } else if (selectedRow.length > 0) {
                    $('#current_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");

                    $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>'+LANG.UI_JOB_SELECTED_ROWS+'' + selectedRow.length + '');
                    $('.batch-start').addClass('batch_start_active');
                    $('.batch-start i').addClass('icon-start');
                    $('.batch-start span').addClass('bgb');
                    $('.batch-stop').addClass('batch_stop_active');
                    $('.batch-stop i').addClass('icon-stop');
                    $('.batch-stop span').addClass('bgb');
                    $('.batch-delete').addClass('batch_delete_active');
                    $('.batch-delete i').addClass('icon-delete');
                    $('.batch-delete span').addClass('bgb');
                };
                $.each(selectedRow, function (index) {
                    preventDBCDPBatchOp(selectedRow[index].module_type_value);
                    preventVolCDPBatchOp(selectedRow[index]); //卷实时接管验证任务不可批量停止
                    preventNewDBCDPBatchOp(selectedRow[index]); //新数据库实时批量操作
                    if (selectedRow[index].job_status_value != 4) {
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }
                    if (selectedRow[index].job_status_value == 4 || selectedRow[index].job_status_value == 13 || selectedRow[index].job_status_value == 14) {
                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');
                    }
                    if (selectedRow[index].job_status_value == 1 || selectedRow[index].job_status_value == 2 || selectedRow[index].job_status_value == 12 ||
                        selectedRow[index].job_status_value == 3 || selectedRow[index].job_status_value == 5 || selectedRow[index].job_status_value == 6 ||
                        selectedRow[index].job_status_value == 7 || selectedRow[index].job_status_value == 8 || selectedRow[index].job_status_value == 11) {
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                    if (20 == selectedRow[index].job_status_value) {
                        // 删除中的状态，禁用所有批量操作
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }

                    if ($.inArray(selectedRow[index].job_type_value, [32, 33, 34]) !== -1) { //卷cdp等待不能批量停止，停止不能批量启动策略
                        if (selectedRow[index].job_status_value == 1) { //等待
                            $('.batch-stop').removeClass('batch_stop_active');
                            $('.batch-stop i').removeClass('icon-stop');
                            $('.batch-stop span').removeClass('bgb');
                            $('.batch-start').removeClass('batch_start_active');
                            $('.batch-start i').removeClass('icon-start');
                            $('.batch-start span').removeClass('bgb');
                        }
                        if (selectedRow[index].job_status_value == 4) { // 停止
                            $('.batch-start').removeClass('batch_start_active');
                            $('.batch-start i').removeClass('icon-start');
                            $('.batch-start span').removeClass('bgb');
                        }

                    }
                })
            },
            onUncheckAll: function () {
                var selectedRow = $('#current_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;

                $('#current_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                $('#current_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");

                $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('');
                $('.batch-start').removeClass('batch_start_active');
                $('.batch-start i').removeClass('icon-start');
                $('.batch-start span').removeClass('bgb');

                $('.batch-stop').removeClass('batch_stop_active');
                $('.batch-stop i').removeClass('icon-stop');
                $('.batch-stop span').removeClass('bgb');


                $('.batch-delete').removeClass('batch_delete_active');
                $('.batch-delete i').removeClass('icon-delete');
                $('.batch-delete span').removeClass('bgb');

            },
            onCheckAll: function () {
                var selectedRow = $('#current_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                $('#current_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-checked");

                $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>'+LANG.UI_JOB_SELECTED_ROWS+'' + selectedRow.length + '');
                if ($('#current_table').find('.no-records-found').length > 0) {

                } else {
                    $('.batch-start').addClass('batch_start_active');
                    $('.batch-start i').addClass('icon-start');
                    $('.batch-start span').addClass('bgb');
                    $('.batch-stop').addClass('batch_stop_active');
                    $('.batch-stop i').addClass('icon-stop');
                    $('.batch-stop span').addClass('bgb');
                    $('.batch-delete').addClass('batch_delete_active');
                    $('.batch-delete i').addClass('icon-delete');
                    $('.batch-delete span').addClass('bgb');
                }

                $.each(selectedRow, function (index) {
                    preventDBCDPBatchOp(selectedRow[index].module_type_value);
                    preventVolCDPBatchOp(selectedRow[index]); //卷实时接管验证任务不可批量停止
                    preventNewDBCDPBatchOp(selectedRow[index]); //新数据库实时批量操作
                    if (selectedRow[index].job_status_value != 4) {
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }
                    if (selectedRow[index].job_status_value == 4 || selectedRow[index].job_status_value == 13 || selectedRow[index].job_status_value == 14) {
                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');
                    }
                    if (selectedRow[index].job_status_value == 1 || selectedRow[index].job_status_value == 2 || selectedRow[index].job_status_value == 12 ||
                        selectedRow[index].job_status_value == 3 || selectedRow[index].job_status_value == 5 || selectedRow[index].job_status_value == 6 ||
                        selectedRow[index].job_status_value == 7 || selectedRow[index].job_status_value == 8 || selectedRow[index].job_status_value == 11) {
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                    if (20 == selectedRow[index].job_status_value) {
                        // 删除中的状态，禁用所有批量操作
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                        $('.batch-stop').removeClass('batch_stop_active');
                        $('.batch-stop i').removeClass('icon-stop');
                        $('.batch-stop span').removeClass('bgb');
                        $('.batch-delete').removeClass('batch_delete_active');
                        $('.batch-delete i').removeClass('icon-delete');
                        $('.batch-delete span').removeClass('bgb');
                    }

                    if ($.inArray(selectedRow[index].job_type_value, [2, 4, 6, 7, 8, 14, 15, 22, 25, 29, 33, 36, 43, 49]) !== -1) { //恢复任务不能批量启动策略
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                    if ($.inArray(selectedRow[index].job_type_value, [32, 33, 34]) !== -1) { //卷cdp等待不能批量停止，不能批量启动策略
                        if (selectedRow[index].job_status_value == 1) { //等待
                            $('.batch-stop').removeClass('batch_stop_active');
                            $('.batch-stop i').removeClass('icon-stop');
                            $('.batch-stop span').removeClass('bgb');
                        }
                        $('.batch-start').removeClass('batch_start_active');
                        $('.batch-start i').removeClass('icon-start');
                        $('.batch-start span').removeClass('bgb');
                    }
                })
            },
            PostBody: function () {
                // 表格渲染完成后的回调
                var tableData = $('#current_table').bootstrapTable('getData');
                var selectedRow = $('#current_table').bootstrapTable('getSelections');
                if (selectedRow.length == 0) {
                    resetBatchOp();
                };
                if (tableData.length == 0)return;
                // 自动刷新时记录当前勾选回填
                checkRecord();
                initCurrentJobTimer();
                $(`#dropdown_operate_${btnOpen}`).parent('.btn-group').addClass('open');
                if (!initFlag) {
                    // 初始化更改列宽
                    $('#current_table th[data-field="job_name"]').css('width', '15%');
                    $('#current_table th[data-field="speed"]').css('width', '5%');
                    $('#current_table th[data-field="progress"]').css('width', '5%');
                    $('#current_table th[data-field="module_type_value"]').css('width', '6%');
                    $('#current_table th[data-field="job_type_value"]').css('width', '6%');
                    $('#current_table th[data-field="create_time"]').css('width', '10%');
                    $('#current_table th[data-field="next_time"]').css('width', '10%');
                    $('#current_table th[data-field="user_name"]').css('width', '5%');
                    $('#current_table th[data-field="job_status_value"]').css('width', '5%');
                    initFlag = true;
                }

                // 保持表格高度逻辑
                if (changeHeightFlag == false) {
                    $('#current_table>tbody>tr>td').css({
                        'padding-top': '4.25px',
                        'padding-bottom': '4.25px'
                    })
                    $('#vin_current_toolbar .change_height i').removeClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#current_table>tbody>tr>td').css({
                        'padding-top': '10.25px',
                        'padding-bottom': '10.25px'
                    })
                    $('#vin_current_toolbar .change_height i').addClass('icon-auto-height2');
                }
                // 添加任务控制（不同状态的任务部分操作禁用）
                addOpButton();

                if (!operateColumnVisible) { // 角色无 当前任务 管理权限，隐藏 批量删除操作、 操作列 checkbox以及重置表格高度
                    $('.batchOperation').hide();
                    $('#vin_current_toolbar .rightTool .keep-open .dropdown-menu .dropdown-item-marker:last-child').hide();

                    $('.jobs-wrapper .table-container.current-job-table-container').css('height', 'calc(100% - 46px)');
                } else {
                    $('.batchOperation').show();

                    $('.jobs-wrapper .table-container.current-job-table-container').css('height', 'calc(100% - 76px)');
                }

                $('#current_table [data-toggle="tooltip"]').tooltip();

                const exportOptions = {
                    toolbarId: 'vin_current_toolbar',
                    url: '/api/v1/jobs/export',
                    fileName: LANG.UI_JOB_CURRENT_TASK,
                    filterBtnId: 'current_job_filter_btn',
                    dateRangePickerId: 'current_job_datepicker'
                }

                // 监听导出全部数据
                exportAllTableData(exportOptions);
            },
            columnsSwitch: function () {
                // 优化任务列表需要的逻辑，指的是，在列表操作右上角显示/隐藏列的时候需要刷新一下表格，重新获取数据，重新渲染表格
                $('#current_table').bootstrapTable('refresh');
            },
            onPreBody: function () {
                $('#current_table [data-toggle="tooltip"]').tooltip('hide');
            },
            customTool: { //自定义工具栏
                afterInput: addtaskBtn, //新建任务html
            },
            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false, //默认可排序，禁用排序才写此项
                    forceHide: true,
                    width: 1,
                    widthUnit: '%'
                },
                {
                    field: 'job_name', //字段名
                    title: LANG.UI_SEARCH_TASK_NAME,
                    formatter: hrefFormatter,
                    events: operateEvents,
                },
                {
                    field: 'module_type',
                    title: LANG.UI_SEARCH_OBJ_TYPE
                },
                {
                    field: 'job_type_value',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    formatter: function (index, row) {
                        if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //vol_cdp验证任务特殊处理
                            return `<span title="${LANG.UI_VOL_CDP_VERIFY_DESC}">${LANG.UI_VOL_CDP_VERIFY_DESC}</span>`;
                        } else {
                            return `<span title="${row.job_type}">${row.job_type}</span>`;
                        }
                    }
                },
                {
                    field: 'create_time',
                    title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
                    formatter: function (value) {
                        return `<span data-toggle="tooltip" data-placement="bottom-start" title="${value}">${value}</span>`;
                    }
                },
                {
                    field: 'next_time',
                    title: LANG.UI_PUBLIC_NEXT_RUN_TIME,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (index, row) {
                        if(row.task_orchestration_plan_flag){
                            return `--`;
                        } else {
                            return `<span data-toggle="tooltip" data-placement="bottom-start" title="${row.next_time}">${row.next_time}</span>`;
                        }
                    }
                },
                {
                    field: 'speed',
                    title: LANG.UI_VISUAL_SPEED,
                },
                {
                    field: 'progress',
                    title: LANG.UI_VISUAL_PROGRESS,
                },
                {
                    field: 'current_stage_value',
                    title: LANG.UI_PLATFORM_JOB_STAGE,
                },
                {
                    field: 'user_name',
                    title: LANG.UI_REPORT_BUILDER
                },
                {
                    field: 'job_status_value',
                    title: LANG.UI_PUBLIC_STATUS,
                    // 所有的type: "label",都是在bs-table.js中定义的formatter
                    type: "label",
                },
                {
                    field: 'back_node',
                    title: LANG.UI_BACKUP_NODE,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (value) {
                        return `<span data-toggle="tooltip" data-placement="bottom-start" title="${value}">${value}</span>`;
                    }
                },
                {
                    field: 'back_storage',
                    title: LANG.UI_STORAGE,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (value) {
                        return `<span data-toggle="tooltip" data-placement="bottom-start" title="${value}">${value}</span>`;
                    }
                },
                {
                    field: 'running_time',
                    title: LANG.UI_PUBLIC_CONTINUE_RUN_TIME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'vcenter_hypervisor',
                    title: LANG.UI_REPORT_HYPERVISOR,
                    sortable: false, //默认可排序，禁用排序才写此项
                    // formatter: vmFormatter,
                },
                {
                    field: 'vcenter_name',
                    title: LANG.UI_VCENTER_VCENTER,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'db_type',
                    title: LANG.UI_DB_TYPE,
                    formatter: dbTypeFormater,
                },
                {
                    field: 'orchestration_name',
                    title: LANG.UI_JOB_ORCHESTRATION_NAME,
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    // 所有的type: "xxx",都是在bs-table.js中定义的formatter
                    type: "operation",
                    width: "125px",
                    events: operates, //单元点击事件
                    forceHide: true,
                    visible: operateColumnVisible
                }
            ]
        }
        let detailPage = JSON.parse(sessionStorage.getItem('current_table_detailPage'));
        if (detailPage) {
            //从详情页面或子页返回，先初始化，然后清除分页信息，保持之前的页数
            $('#current_table').baseTableConfig().init(options);
            sessionStorage.removeItem('current_table_pageRecord');
            sessionStorage.removeItem('current_table_detailPage');
        } else {
            //不是详情页或子页面，清除offset再初始化
            var page = sessionStorage.getItem('current_table_pageRecord');
            if (page != null) {
                page = JSON.parse(page);
                page.offset = 0;
                page = JSON.stringify(page);
                sessionStorage.setItem('current_table_pageRecord', page);
            }
            $('#current_table').baseTableConfig().init(options);
        }
    }

    // 任务名跳转详情
    const hrefFormatter = function (value, row, index, field) {
        // HTML 转义函数，防止 XSS
        const escapeHtml = (str) => {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return String(str).replace(/[&<>"']/g, s => map[s]);
        };

        let url = getDetailsUrl(
            row.module_type_value,
            row.job_type_value,
            row.vm_type,
            row.sub_module_type_value,
            row.dev_type
        );

        const escapedValue = escapeHtml(value);

        const separator = url.includes('?') ? '&' : '?';
        const baseHref = `${url}${separator}type=${encodeURIComponent(row.job_type_value)}&uuid=${encodeURIComponent(row.job_uuid)}`;
        let href = baseHref;

        if ([17, 18, 19, 20, 26, 27, 30, 31, 38, 39, 40, 41, 44, 45].includes(row.job_type_value)) {
            href = `./content/copy/copy_job_details.php?type=${encodeURIComponent(row.job_type_value)}&module=${encodeURIComponent(row.module_type_value)}&subType=${encodeURIComponent(row.sub_module_type_value)}&uuid=${encodeURIComponent(row.job_uuid)}`;
        } else {
            switch (row.module_type_value) {
                case CONF.MODULE_TYPE.OS:
                    href += `&sub_module_type=${encodeURIComponent(row.sub_module_type_value)}`;
                    break;
                // 其他模块类型保持默认 href
                default:
                    break;
            }
        }

        return `<a href="${href}" class="ajaxify" name="task" data-toggle="tooltip" data-placement="bottom-start" title="${escapedValue}">${escapedValue}</a>`;
    };

    //根据模块类型得到任务详情的页面地址
    var getDetailsUrl = function (module, taskType, hypervisorType, subModule, devType) {
        var url = '';

        if (2 == module) {
            if (!CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(hypervisorType)) {
                //虚拟机
                url = './content/vm/vm_job_details.php';
                if (7 == taskType) {
                    //瞬时恢复
                    url = "./content/vm/vm_instant_job_details.php";
                }
                if (8 == taskType) {
                    //迁移
                    url = "./content/vm/vm_motion_job_details.php";
                }
                if (6 == taskType) {
                    //细粒度恢复
                    url = "./content/vm/vm_grain_job_details.php";
                }

            } else {
                //AWS
                url = './content/aws/aws_job_details.php';
                if (6 == taskType) {
                    //细粒度恢复 todo 暂时用vm的
                    url = "./content/vm/vm_grain_job_details.php";
                }
            }
            //CBR同步
            if (51 == taskType) {
                url = "./content/cbr/cbr_job_details.php";
            }
        } else if (17 == module) {
            //公有云
            url = './content/aws/aws_job_details.php';
            if (6 == taskType) {
                //细粒度恢复 todo 暂时用vm的
                url = "./content/vm/vm_grain_job_details.php";
            }
        } else if (3 == module) {
            switch (subModule) {
                case 1: // 文件
                    url = './content/file/fs_job_details.php?sub_module_type=1';
                    break;
                case 2: // NAS
                    url = './content/file/fs_job_details.php?sub_module_type=2';
                    break;
                case 3: // HADOOP
                    url = './content/file/fs_job_details.php?sub_module_type=3';
                    break;
                case 4: // 对象存储
                    url = './content/file/fs_job_details.php?sub_module_type=4';
                    break;
                default:
                    break;
            }
        } else if (11 == module) {
            //nas
            url = './content/file/fs_job_details.php?sub_module_type=2';
        } else if (4 == module) {
            //数据库
            //			url = 'javascript:;';
            url = './content/dbprotect/db_job_details.php';
        } else if (16 == module) {
            //副本 虚拟机|文件|NAS
            if (taskType == 17 || taskType == 18 ||
                taskType == 26 || taskType == 27 ||
                taskType == 30 || taskType == 31 ||
                taskType == 38 || taskType == 39 ||
                taskType == 44 || taskType == 45) {
                url = './content/copy/copy_job_details.php';
            }
            //归档
            if (taskType == 19 || taskType == 20) {
                url = './content/archive/archive_job_details.php';
            }
        } else if (10 == module) {
            if (devType != 2) { //旧版
                url = './content/volcdp/cdp_job_details.php';
            } else {
                url = './content/complete_machine_volcdp/cm_cdp_job_details.php';
            }

        } else if (12 == module) {
            //数据库CDP
            url = "./content/dbcdp/dbcdp_job_details.php";
        } else if (14 == module) {
            url = './content/exchange/exchange_job_details.php';
        } else if(10000 == module){
            //数据库CDP（旧）
            if(21 == taskType){
                //实时备份
                url = "./content/db/db_cdp_job_details.php";
            }
            if(22 == taskType){
                //数据恢复
                url = "./content/db/db_recovery_job_details.php";
            }
        } else if (10001 == module) {
            //文件CDP
            if (24 == taskType) {
                //实时备份
                url = "./content/fs/fs_cdp_job_details.php";
            }
            if (25 == taskType) {
                //数据恢复
                url = "./content/fs/fs_recovery_job_details.php";
            }
        } else if (5 == module) {
            if(subModule == 1){
                //整机
                url = "./content/complete_machine_os/machine_os_job_details.php";

            }else{//以前旧的卷备份
                //操作系统
                if (49 == taskType) {
                    url = "./content/os/os_instant_job_details.php";
                } else if (50 == taskType) {
                    //迁移
                    url = "./content/os/os_motion_job_details.php";
                } else {
                    url = "./content/os/os_job_details.php";
                }
            }
        }else if( 28 == module){
            //整机
            url = "./content/kubernetes/kubernetes_job_details.php";
        } else if (26 == module) {
            url = './content/filecopy/file_copy_job_details.php';
        }

        if(37 == taskType) {
            //数据验证
            url = "/module/verification/html/verification_job_details.php";
        } else if (55 == taskType) {
            //细粒度恢复
            url = "./content/platform/recovery/graininess_job.php";
            //url = "./content/vm/vm_grain_job_details.php";
        } else if (53 == taskType || 49 == taskType) {
            // 瞬时恢复
            url = "./content/platform/recovery/instantaneous_job.php";
        } else if (52 == taskType) {
            // 跨平台恢复
            url = "./content/platform/recovery/platform_job.php";
        } else if (54 == taskType || 50 == taskType) {
            // 迁移
            url = "./content/platform/recovery/motion_job.php";
        }

        return url;
    }

    //点击任务名超链接事件
    var operateEvents = {
        'click .ajaxify': function (e, value, row, index) {
            // 设置该参数用于判断是否从详情页面返回
            sessionStorage.setItem('current_table_detailPage', 'true');
            $(window).unbind('resize'); //解绑表格插件中绑定的全局事件resize避免在没有表格的地方触发
        },
    };

    //更新表格数据
    var update = function () {
        $('#current_table').bootstrapTable('refresh', {
            query: getParams()
        });
    }

    /**
     * 初始化当前任务表格刷新定时器
     */
    const initCurrentJobTimer = function () {
        if (timerTask.CURRENT_JOB_TIMER) {
            clearTimeout(timerTask.CURRENT_JOB_TIMER);
        }

        timerTask.CURRENT_JOB_TIMER = setTimeout(update, 5000);
    }

    // <-------------------------   BEGIN ADVANCED SEARCH  --------------------------------->

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

                        searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: `${newCondition[key]}-${newCondition['sub_module_type']}`, conditionValText: conditionValText });
                    } else { // 不存在子模块
                        let conditionValText = '';
                        if (newCondition[key].includes(',')) { // 如果是逗号拼接的模块类型，说明选择的是定时备份所有 或 复制容灾所有
                            let modules = newCondition[key].split(',');
                            if (modules.length === 7) { // 如果是选择的定时备份的所有，则手动传上所有模块的 module_type和sub_module_type，没有子模块的补0，保证和过滤器传参统一
                                conditionValText = `${LANG.UI_TIMING_BACKUP}(${LANG.UI_PUBLIC_ALL})`;
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
                case 'node_uuid': // 所在节点
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: ADVANCED_SEARCH_NODE_TO_DES_MAP[newCondition[key]] });

                    break;
                case 'storage_uuid': // 所在存储
                    searchConditions.push({ conditionType: key, conditionTypeText: ADVANCED_SEARCH_PARAM_TO_DES_MAP[key], conditionVal: newCondition[key], conditionValText: ADVANCED_SEARCH_STORAGE_TO_DES_MAP[newCondition[key]] });

                    break;
                default:
                    break;
            }
        });

        return searchConditions;
    }

    const initAdvancedSearch = () => {
        $('#current_job_advanced_search_wrapper').initAdvancedSearch({
            advancedSearchSlotId: 'current_job_advanced_search_wrapper', // 高级搜索组件插槽id
            advancedSearchBtnId: 'current_job_advanced_search_btn', // 高级搜索按钮id
            searchConditions: [], // 搜索条件数组（初始化时还未搜索传空数组）
            totalConditionNumbers: 11 // 高级搜索表单条件总数
        });
    }

    /**
     * 初始化节点选择框
     */
    const initNodeSelect = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/nodes', 'GET', function (d) {
            let data = d;
            let nodeSelect = $('#advanced_search_current_nodes');
            nodeSelect.empty();
            let option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
            nodeSelect.append(option);
            for (let i = 0; i < data.data.rows.length; i++) {
                option = $("<option>").text(data.data.rows[i].ip).val(data.data.rows[i].node_uuid);

                // 赋值高级搜索参数中节点uuid对应节点描述的map映射
                ADVANCED_SEARCH_NODE_TO_DES_MAP[data.data.rows[i].node_uuid] = data.data.rows[i].ip;
                nodeSelect.append(option);
            }
            nodeSelect.val('0');
        });
    }

    /**
     * 获取不同节点下的存储
     * @param {*} d
     */
    const resetStorage = function (d) {
        let data = d;
        let storageSelect = $('#advanced_search_current_storages');
        storageSelect.empty();
        let option = $("<option>").text(LANG.UI_STORAGE_ALL).val('0');
        storageSelect.append(option);
        for (let i = 0; i < data.data.length; i++) {
            option = $("<option>").text(data.data[i].name).val(data.data[i].storage_uuid);
            storageSelect.append(option);
        }
        storageSelect.val('0');
    }

    /**
     * 节点 change 事件
     */
    const nodeChange = function () {
        let node = $('#advanced_search_current_nodes').val();
        if (node == '0') {
            initStorage();
        } else {
            pAjaxRequest({
                'node_uuid': node,
                'all_flag': 1 //使用这个参数返回包含云存储和副本归档存储的信息
            }, '/api/v1/storages/backup', 'GET', resetStorage);
        }
    }

    /**
     * 初始化存储选择框
     */
    const initStorage = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 100,
            'source_type': 1
        }, '/api/v1/storages', 'GET', function (d) {
            let data = d;
            let storageSelect = $('#advanced_search_current_storages');
            storageSelect.empty();
            let option = $("<option>").text(LANG.UI_STORAGE_ALL).val('0');
            storageSelect.append(option);
            for (let i = 0; i < data.data.rows.length; i++) {
                option = $("<option>").text(data.data.rows[i].storage_nickname).val(data.data.rows[i].storage_uuid).attr('type', data.data.rows[i].storage_type);

                // 赋值高级搜索参数中存储uuid对应存储描述的map映射
                ADVANCED_SEARCH_STORAGE_TO_DES_MAP[data.data.rows[i].storage_uuid] = data.data.rows[i].storage_nickname;
                storageSelect.append(option);
            }
            storageSelect.val('0');
        });
    }

    /**
     * 处理业务类型选所有场景
     * @param {*} businessType
     */
    const handleSelectedBusinessType = (businessType) => {
        $('.advanced-search-vm-type').addClass('display-none');
        $('.advanced-search-vm-name').addClass('display-none');
        $('.advanced-search-db-type').addClass('display-none');

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
            case 2: // 不包含子模块的模块类型：数据备份 - NAS、数据库，数据复制 - 数据库，数据复制 - 文件，M365，K8S
                $('.advanced-search-vm-type').addClass('display-none');
                $('.advanced-search-vm-name').addClass('display-none');

                if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB || Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB_CDP) {
                    $('.advanced-search-db-type').removeClass('display-none');
                } else {
                    $('.advanced-search-db-type').addClass('display-none');
                }

                return {  module_type: moduleTaskData[0], sub_module_type: '',  job_type: moduleTaskData[1] };
            case 3: // 包含子模块的模块类型（但不包括持续数据保护和数据复制的整机模块）：数据备份 - 虚拟化、私有云、公有云、整机、卷、文件、Hadoop、对象存储
                $('.advanced-search-db-type').addClass('display-none');

                if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.VM) {
                    $('.advanced-search-vm-name').removeClass('display-none');
                    $('.advanced-search-vm-type').removeClass('display-none');
                } else {
                    $('.advanced-search-vm-name').addClass('display-none');
                    $('.advanced-search-vm-type').addClass('display-none');
                }

                if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB || Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB_CDP) {
                    $('.advanced-search-db-type').removeClass('display-none');
                } else {
                    $('.advanced-search-db-type').addClass('display-none');
                }

                return {  module_type: moduleTaskData[0], sub_module_type: moduleTaskData[1],  job_type: moduleTaskData[2] };
            case 4: // 包含子模块的模块类型（并包括持续数据保护和数据复制的整机模块）：连续数据保护 - 整机、卷，数据复制 - 整机、卷
                $('.advanced-search-vm-type').addClass('display-none');
                $('.advanced-search-vm-name').addClass('display-none');
                $('.advanced-search-db-type').addClass('display-none');

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
            // GMP项目过滤掉定时整机中的 数据验证的任务类型
            treeData = treeData.map(taskType => {
                if (taskType.value === "timingDataProtect") {
                    taskType.children = taskType.children.map(child => {
                        if (child.value === CONF.SYSTEM_MODULE_TYPE_TO_VALUE_MAP.COMPLETE_MACHINE_DISK) {
                            child.children = child.children.filter(grandChild => grandChild.value !== "5-1-37");
                        }
                        return child;
                    });
                }
                return taskType;
            });
        }

        const cascader = new Cascader({
            container: "#current_job_advanced_search_cascader",
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

    const initVMType = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/vm/platforms/hypervisors', 'GET', function (d) {
            let data = d;
            let vmSelect = $('#advanced_search_vm_type');
            vmSelect.empty();
            let option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
            vmSelect.append(option);
            for (let i = 0; i < data.data.hypervisors.length; i++) {
                option = $("<option>").text(data.data.hypervisors[i].text).val(data.data.hypervisors[i].value);
                vmSelect.append(option);
            }
            vmSelect.val('0');
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

            let dbTypeSelect = $('#advanced_search_db_type');
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
     * 初始化高级搜索表单
     */
    const initAdvancedSearchForm = () => {
        initNodeSelect(); //初始化高级搜索节点下拉列表

        initStorage();  // 初始化高级搜索存储下拉列表

        initCascader(); // 初始化高级搜索任务类型级联下拉列表

        initVMType(); // 初始化高级搜索虚拟化类型下拉列表

        initDbType(); // 初始化高级搜索数据库类型下拉列表

    };

    // <-------------------------   END ADVANCED SEARCH  ----------------------------------->

    const addListeners = function () {
        // 点击挂起任务
        $('#current_job_task_btn').on('click', function (){
            CurrentPendingJOb.init();
            $('#current_pending_task_drawer').drawer('show');
        });

        // 翻页时要显示加载动画
        $('.page-link').on('click', function () {
            $('#current_table').bootstrapTable('showLoading');
        });

        // 改变表格高度
        $('#vin_current_toolbar .change_height').on('click', change_height);

        // 批量启动策略
        $('.batchOperation').on('click', '.batch_start_active', function () {
            let userUuids = $.map($('#current_table').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});

            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: 'current_job' }, () => {
                batchStart();
            });
        });

        // 批量停止
        $('.batchOperation').on('click', '.batch_stop_active', function () {
            let userUuids = $.map($('#current_table').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});

            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: 'current_job' }, () => {
                batchStop();
            });
        });

        // 批量删除
        $('.batchOperation').on('click', '.batch_delete_active', function () {
            let userUuids = $.map($('#current_table').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});

            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: 'current_job' }, () => {
                batchDelete();
            });
        });

        // <-------------   BEGIN TABLE TOOLBAR  -------------------->

        // 回车搜索事件
        $('#current_job_seach_ipt').keypress(function (e) {
            if (e.which == 13) {
                searchVal = $('#current_job_seach_ipt').val();
                FILTER_PARAMS.search = searchVal;

                $('#current_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS }});
            }
        });

        // 搜索当前任务
        $('#current_job_search_btn').off().on('click', () => {
            searchVal = $('#current_job_seach_ipt').val();
            FILTER_PARAMS.search = searchVal;

            $('#current_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS }});
        });

        $('#current_job_seach_ipt').on('focus', () => {
            if ($('#current_job_seach_ipt').val()) {
                $('#current_job_clear_search').removeClass('hide');
            }
        });

        $('#current_job_seach_ipt').on('input', () => {
            if ($('#current_job_seach_ipt').val()) {
                $('#current_job_clear_search').removeClass('hide');
            } else {
                $('#current_job_clear_search').addClass('hide');
            }
        });

        // 清空当前任务搜索
        $('#current_job_clear_search').on('click', () => {
            $('#current_job_seach_ipt').val('');
            searchVal = '';
            FILTER_PARAMS.search = '';
            $('#current_job_clear_search').addClass('hide');
            $('#current_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } } );
        });

        // 打开高级搜索弹窗
        $('#current_job_advanced_search_btn').on('click', () => {
            // 暂不重置表单，因为表格每5秒刷新一次，重置的话会造成高级搜素有了搜索条件但是一打开弹窗表格又重置数据了
            // $('#advanced_search_task_name').val('');
            // $('#advanced_search_user_name').val('');
            // $('#advanced_search_host_name').val('');
            // $('#advanced_search_vm_name').val('');
            // $('#advanced_search_vm_type').val('0');
            // $('#advanced_search_db_type').val('0');
            // $('#advanced_search_current_nodes').val('0');
            // $('#advanced_search_current_storages').val('0');
            // $('.advanced-search-vm-name').addClass('display-none');
            // $('.advanced-search-vm-type').addClass('display-none');
            // $('.advanced-search-db-type').addClass('display-none');
            // ADVANCED_SEARCH_PARAMS.module_type = '';
            // ADVANCED_SEARCH_PARAMS.sub_module_type = '';
            // ADVANCED_SEARCH_PARAMS.job_type = '';
            // ADVANCED_SEARCH_PARAMS.storage_location = '';
            // ADVANCED_SEARCH_PARAMS.dev_type = '';

            // initCascader();

            $('#advanced_search_modal').modal('show');
        });

        $('#current_job_advanced_search_submit').on('click', () => {
            // 重置过滤器 GMP项目过滤掉 数据验证 任务类型
            let filterData = CURRENT_JOB_TABLE_FILTER_OPTIONS.map(option => {
                if (option.field === 'task_type') {
                    option.value = option.value.filter(item => item.id !== 'task_type_data_verify');
                }
                return option;
            });

            $('#current_job_filter_wrapper').resetFilter({
                filterSlotId: 'current_job_filter_wrapper',
                filterBtnId: 'current_job_filter_btn',
                filters: filterData
            });

            // 获取最终的高级搜索参数
            ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, {
                job_name: $('#advanced_search_task_name').val(),
                user_name: $('#advanced_search_user_name').val(),
                other_host_name: $('#advanced_search_host_name').val(),
                other_vm_name: $('#advanced_search_vm_name').val(),
                vm_type: $('#advanced_search_vm_type').val(),
                db_type: $('#advanced_search_db_type').val(),
                node_uuid: $('#advanced_search_current_nodes').val(),
                storage_uuid: $('#advanced_search_current_storages').val()
            });

            let searchConditions = handleAdvancedSearchConditions(ADVANCED_SEARCH_PARAMS);

            // 更新高级搜索条件展示组件
            $('#current_job_advanced_search_wrapper').updateSearchConditons({
                searchConditions: searchConditions,
                advancedSearchBtnId: 'current_job_advanced_search_btn', // 高级搜索按钮id
            })

            $('#current_table').bootstrapTable('refresh', { query: { ...ADVANCED_SEARCH_PARAMS } });
            $('#advanced_search_modal').modal('hide');
        });

        $('#advanced_search_current_nodes').on('change', nodeChange);

        // 移除所有旧的过滤器监听
        window.$off('current_job_filter_btn-updateFilterEvent');

        // 监听当前任务表格 - 过滤器组件派发的数据，以更新表格
        window.$on('current_job_filter_btn-updateFilterEvent', (filterData) => {
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

            FILTER_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, FILTER_PARAMS);

            $('#current_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } });
        });

        // 移除所有旧的日期范围筛选监听
        window.$off('current_job_datepicker-updateDateRangeEvent');

        // 监听当前任务表格 - 日期范围选择组件派发的数据，以更新表格
        window.$on('current_job_datepicker-updateDateRangeEvent', (data) => {
            // 记录选择的开始时间和结束时间，用于过滤搜索的联动
            startTime = data.startTime;
            endTime = data.endTime;

            $('#current_table').bootstrapTable('refresh', { query: { start_time: startTime, end_time: endTime } });
        });

        // 移除所有旧的日期范围筛选监听
        window.$off('current_job_advanced_search_btn-removeConditionEvent');

        // 监听当前任务高级搜索展示组件派发的数据，以更新表格
        window.$on('current_job_advanced_search_btn-removeConditionEvent', (removedCondition) => {

            let keys = Object.keys(removedCondition);

            if (keys.length > 0) {
                keys.forEach(key => {
                    // 从 ADVANCED_SEARCH_PARAMS 中赋空对应的键值
                    if (ADVANCED_SEARCH_PARAMS.hasOwnProperty(key)) {
                        ADVANCED_SEARCH_PARAMS[key] = '';
                    }
                });

                $('#current_table').bootstrapTable('refresh', { query: { ...ADVANCED_SEARCH_PARAMS } });
            }
        });

        // <-------------   END TABLE TOOLBAR  ---------------------->

        $('#close_tips_btn').on('click', () => {
            // 关闭当前任务页面提示tips时，动态调整表格高度
            if (CONF.PERMISSION_ARR.indexOf('p_current_job_manager') > -1) { // 已授权当前任务管理权限，则有 批量删除 DOM
                $('.jobs-wrapper .table-container.current-job-table-container').css('height', 'calc(100% - 76px)');
            } else {
                $('.jobs-wrapper .table-container.current-job-table-container').css('height', 'calc(100% - 46px)');
            }
        });

        // 移除所有旧的日期范围筛选监听
        window.$off('updateCurrentJobPage');

        // 监听当前任务页面更新事件
        window.$on('updateCurrentJobPage', () => {
            // 重置表格定时器
            initCurrentJobTimer();
        });
    };

    /**
     * 初始化新建任务菜单组件
     */
    const initAddTaskMenu = () => {
        // 获取权限数组，并合并抽象出的第一层业务类型的id数组，即：备份、实时保护和复制
        let permissions = CONF.PERMISSION.concat(['timing_backup', 'data_copy', 'real_time_protect']);

        if (CONF.PERMISSION_ARR.indexOf('p_current_job_manager') > -1) { // 授权了才初始化新建任务组件
            if (CONF.VENDOR === CONF.VENDOR_LIST.gmp) { // GMP项目包含 新建备份 和 新建恢复
                $('.vinchin-toolbar-item').addClass('display-none');
                $('.gmp-toolbar-item').removeClass('display-none');

                $('#gmp_backup_job_menu_wrapper').initMenu({
                    menuSlotId: 'gmp_backup_job_menu_wrapper',
                    menuBtnId: 'gmp_backup_job_menu_btn',
                    operateBtnName: LANG.UI_JOB_NEW_BACKUP_TASK,
                    menuTreeData: filterTaskMenuTree(ROUTE.GMP_BACKUP_ROUTE_TREE_DATA, permissions)
                });

                $('#gmp_recover_job_menu_wrapper').initMenu({
                    menuSlotId: 'gmp_recover_job_menu_wrapper',
                    menuBtnId: 'gmp_recover_job_menu_btn',
                    operateBtnName: LANG.UI_JOB_NEW_RECOVER_TASK,
                    menuTreeData: filterTaskMenuTree(ROUTE.GMP_RECOVER_ROUTE_TREE_DATA, permissions)
                });

                return;
            }

            $('.vinchin-toolbar-item').removeClass('display-none');
            $('.gmp-toolbar-item').addClass('display-none');

            $('#current_job_task_menu_wrapper').initMenu({
                menuSlotId: 'current_job_task_menu_wrapper',
                menuBtnId: 'current_job_task_menu_btn',
                operateBtnName: LANG.UI_JOB_NEW_TASK,
                menuTreeData: filterTaskMenuTree(ROUTE.MODULE_TASK_ROUTE_TREE_DATA, permissions)
            });
        }
    }

    /**
     * 初始化当前任务表格过滤器
     * @param {*}
     */
    const initCurrentJobTableFilter = () => {
        let permissions = [];

        // 若子用户是全局观察者且未获得模块授权，过滤器中的对象类型依旧需要全部展示
        if (CONF.PERMISSION.includes('global_observer')) {
            permissions = CONF.GLOBAL_OBSERVER_CONFIG;
        } else {
            permissions = CONF.PERMISSION;
        }

        // 对过滤器选项数组前三列，即定时备份、实时备份和数据复制列进行过滤
        const filteredOptions = CURRENT_JOB_TABLE_FILTER_OPTIONS.slice(0, 3).filter(item => {
            // 对每个 item 的 value 进行过滤
            const filteredValues = item.value.filter(val => permissions.includes(val.id));

            // 如果过滤后的 value 数组不为空，则更新原对象的 value 并保留该对象
            if (filteredValues.length > 0) {
                item.value = filteredValues;
                return true;
            }
            return false; // 如果过滤后没有元素，则不保留该项
        });

        // 将前三项替换为过滤后的结果
        CURRENT_JOB_TABLE_FILTER_OPTIONS.splice(0, 3, ...filteredOptions);

        if (CONF.VENDOR === CONF.VENDOR_LIST.gmp) {
            // GMP项目过滤掉 数据验证 任务类型
            let filterData = CURRENT_JOB_TABLE_FILTER_OPTIONS.map(option => {
                if (option.field === 'task_type') {
                    option.value = option.value.filter(item => item.id !== 'task_type_data_verify');
                }
                return option;
            });

            $('#current_job_filter_wrapper').initFilter({
                filterSlotId: 'current_job_filter_wrapper',
                filterBtnId: 'current_job_filter_btn',
                filters: filterData
            });

            return;
        }

        $('#current_job_filter_wrapper').initFilter({
            filterSlotId: 'current_job_filter_wrapper',
            filterBtnId: 'current_job_filter_btn',
            filters: CURRENT_JOB_TABLE_FILTER_OPTIONS
        });
    }

    const initCurrentJobTableDaterangePicker = () => {
        $('#current_job_daterangepicker_wrapper').initDateRangePicker({
            slotId: 'current_job_daterangepicker_wrapper', // 日期范围组件在父组件插槽位置的id
            dateRangePickerId: 'current_job_datepicker', // 选择器button id
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
        init: function () {
            // initAddTaskMenu(); // 初始化新建任务菜单组件
            initCurrentJobTableFilter(); // 初始化当前任务表格过滤器
            // initCurrentJobTableDaterangePicker(); // 初始化当前任务表格日期范围选择器
            tableInit();
            initSoftwareVersionDiff();
            initAdvancedSearch(); // 初始高级搜索组件
            initAdvancedSearchForm(); // 初始化高级搜索表单
            addListeners(); //初始化监听事件
        }
    };
}();

jQuery(document).ready(function () {
    CurrentJob.init();
});