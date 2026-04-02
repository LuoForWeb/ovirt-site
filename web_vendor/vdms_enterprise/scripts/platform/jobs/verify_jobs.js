var CurrentJob = function () {
    var resModule; //返回的模块列表
    var detailIndex;
    var deleteFlag = false;
    var checkIndex;
    var btnOpen;
    var filterFlag = true;
    var totalCount = 0;
    var selectCount = 0;
    var interval = null;
    var queryParams = {};
    var changeHeightFlag = false;
    var displayFlag = false;
    var accurateFlag = false;
    var advFlag = false;
    var initFlag = false;
    var fields = []; //需要查询的字段，优化接口速度
    const CURRENT_JOB_TABLE_FILTER_OPTIONS = [
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
                    text: LANG.UI_HOMEPAGEPRO_VERIFYING,
                    tag: true,
                    type: 'success'
                }
            ]
        }
    ]; // 当前任务表格过滤器数组
    let FILTER_PARAMS = {}; // 过滤搜索参数
    let startTime = '', endTime = '', searchVal = '';

    var nodeChange = function () {
        let node = $('#currentJobModal .nodeDiv').find('option:selected').val();
        if (node == '0') {
            initStorage();
        } else {
            pAjaxRequest({
                'node_uuid': node,
                'all_flag': 1 //使用这个参数返回包含云存储和副本归档存储的信息
            }, '/api/v1/storages/backup', 'GET', resetStorage);
        }
    }

    var resetStorage = function (d) {
        var data = d;
        var storageSelect = $('#currentJobModal #current_storage');
        storageSelect.empty();
        var option = $("<option>").text(LANG.UI_STORAGE_ALL).val('0');
        storageSelect.append(option);
        for (var i = 0; i < data.data.length; i++) {
            option = $("<option>").text(data.data[i].name).val(data.data[i].storage_uuid);
            storageSelect.append(option);
        }
        storageSelect.val('0');
    }

    var switchParams = function (module, job_type) {
        var new_job_type = [];
        $.each(module, function (index, value) {
            switch (value) {
                case "2": //VM
                    $.each(job_type, function (k, v) {
                        if (v == 1) { //虚拟机备份
                            new_job_type.push(1);
                        } else if (v == 2) {
                            new_job_type.push(2);
                        } else {
                            new_job_type.push(v);
                        }
                    })
                    break;
                case "3": //文件
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(1);
                        } else if (v == 2) {
                            new_job_type.push(2);
                        }
                    })
                    break;
                case "4": //数据库
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(28);
                        } else if (v == 2) {
                            new_job_type.push(29);
                        }
                    })
                    break;
                case "5": //OS
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(35);
                        } else if (v == 2) {
                            new_job_type.push(36);
                        } else if (v == CONF.TASK_TYPE.VM_INSTANT_RECOVERY_MOTION) { //迁移默认是8虚拟机迁移
                            new_job_type.push(CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION);
                        } else if (v == CONF.TASK_TYPE.VM_INSTANT_RECOVERY) { //瞬时恢复默认是7虚拟机瞬时恢复
                            new_job_type.push(CONF.TASK_TYPE.OS_INSTANT_RECOVERY);
                        }
                    })
                    break;
                case "10": //卷CDP
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(32);
                        } else if (v == 2) {
                            new_job_type.push(33);
                        } else if (v == 34) {
                            new_job_type.push(34);
                        }
                    })
                    break;
                case "11": //NAS
                    $.each(job_type, function (k, v) {
                        if (v == 1) {
                            new_job_type.push(1);
                        } else if (v == 2) {
                            new_job_type.push(2);
                        }
                    })
                    break;
                default:
                    $.each(job_type, function (k, v) {
                        new_job_type.push(v);
                    })
                    break;
            }
        });
        return new_job_type;
    }



    //当模块类型未选中时处理任务类型
    var switchJobType = function (job_type) {
        var new_job_type = [];
        $.each(job_type, function (k, v) {
            switch (v) {
                case '1': //备份
                    new_job_type.push(CONF.TASK_TYPE.BACKUP, CONF.TASK_TYPE.DB_BACKUP, CONF.TASK_TYPE.VOL_CDP_BACKUP, CONF.TASK_TYPE.OS_BACKUP);
                    break;
                case '2': //恢复
                    new_job_type.push(CONF.TASK_TYPE.RECOVERY, CONF.TASK_TYPE.DB_RECOVERY, CONF.TASK_TYPE.VOL_CDP_RECOVERY, CONF.TASK_TYPE.OS_RECOVERY);
                    break;
                case '17': //副本
                    new_job_type.push(CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.DB_BACKUP_COPY, CONF.TASK_TYPE.OS_BACKUP_COPY);
                    break;
                case '19': //归档
                    new_job_type.push(CONF.TASK_TYPE.ARCHIVE, CONF.TASK_TYPE.OS_BACKUP_ARCHIVE);
                    break;
                case '8': //迁移
                    new_job_type.push(CONF.TASK_TYPE.VM_INSTANT_RECOVERY_MOTION, CONF.TASK_TYPE.VM_CDP_INSTANT_RECOVERY_MOTION, CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION);
                    break;
                case '7': //瞬时恢复
                    new_job_type.push(CONF.TASK_TYPE.VM_INSTANT_RECOVERY, CONF.TASK_TYPE.VM_CDP_INSTANT_RECOVERY, CONF.TASK_TYPE.OS_INSTANT_RECOVERY);
                    break;
                default:
                    new_job_type = new_job_type.concat(job_type);
                    break;
            }
        })
        return new_job_type;
    }

    /**
     * @function 获取自定义参数
     * @param bool auto 自动刷新时暂停获取参数，默认false 为获取
     */
    var getParams = function (auto = false) {
        queryParams.search = $('#current_job_seach_ipt').val();

        // 合并过滤器搜索参数
        queryParams = Object.assign(queryParams, FILTER_PARAMS);

        return queryParams;
    }

    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#current_verify_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_current_verify_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#current_verify_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_current_verify_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }


    //记录勾选
    var checkRecord = function () {
        var checkArr = [];
        $.each(checkIndex, function (index) {
            checkArr.push(checkIndex[index].job_uuid);
        });
        $('#current_verify_table').bootstrapTable('checkBy', {
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
        $.post(CONF.AJAXPATH, {m:CONF.M.ROLE,f:'getTaskByPermission',p:{}}, function(d){
            var module_type_list = JSON.parse(d);
            var array = $.map(module_type_list, function(value, index){
                return [value];
            });
            if ($.inArray("vmprotect", array) == -1) { //不存在虚拟机授权，以下同理
                $('.addTaskList .vm_protected').hide();
                $('#current_verify_table-vm_backup').parent().parent().hide();
                $('#current_moduletype option[value=2]').hide();
            }
            if ($.inArray("fileprotect", array) == -1) {
                $('.addTaskList .fs_protected').hide();
                $('#current_verify_table-fs_backup').parent().parent().hide();
                $('#current_moduletype option[value=3]').hide();
            }
            if ($.inArray("db_protect", array) == -1) {
                $('.addTaskList .db_protect').hide();
                $('#current_verify_table-db_backup').parent().parent().hide();
                $('#current_moduletype option[value=4]').hide();
            }
            if ($.inArray("os_protect", array) == -1) {
                $('.addTaskList .os_protected').hide();
                $('#current_verify_table-os_backup').parent().parent().hide();
                $('#current_moduletype option[value=5]').hide();
            }
            if ($.inArray("vol_cdp_protect", array) == -1) {
                $('.addTaskList .real_time_protected').hide();
                $('#current_verify_table-vol_cdp_backup').parent().parent().hide();
                $('#current_moduletype option[value=10]').hide();
            }
            if ($.inArray("nas_protect", array) == -1) {
                $('.addTaskList .nas_protected').hide();
                $('#current_verify_table-nas_backup').parent().parent().hide();
                $('#current_moduletype option[value=11]').hide();
            }
            if ($.inArray("office365_protect", array) == -1) {
                $('.addTaskList .exchange_protected').hide();
                $('#current_verify_table-exchange').parent().parent().hide();
                $('#current_moduletype option[value=14]').hide();
            }
            if ($.inArray("awsprotect", array) == -1) {
                $('.addTaskList .aws_protected').hide();
                $('#current_verify_table-aws').parent().parent().hide();
                $('#current_moduletype option[value=17]').hide();
            }
            if ($.inArray("cloud_platform_private", array) == -1) {
                $('#current_verify_table-privatecloud').parent().parent().hide();
                $('#current_moduletype option[value=22]').hide();
            }
            if ($.inArray("dbprotect", array) == -1) { //数据库实时暂定
                //TODO
            }
            if ($.inArray("hadoop_protect", array) == -1) {
                $('.addTaskList .hadoop_protected').hide();
                $('#current_verify_table-hadoop').parent().parent().hide();
                $('#current_moduletype option[value=3-3]').hide();
            }

            // OBS
            if ($.inArray("obs_protect", array) == -1) {
                $('.addTaskList .obs_protected').hide();
                $('#current_verify_table-obs').parent().parent().hide();
                $('#current_moduletype option[value=3-4]').hide();
            }
            //任务类型
            if ($.inArray("data_verification", CONF.PERMISSION) == -1) { // 数据验证任务
                $('#current_verify_table-verify').parent().parent().hide();
            }
        });
    }

    //初始化存储
    var initStorage = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 100,
            'source_type': 1
        }, '/api/v1/storages', 'GET', function (d) {
            var data = d;
            var storageSelect = $('#currentJobModal #current_storage');
            storageSelect.empty();
            var option = $("<option>").text(LANG.UI_STORAGE_ALL).val('0');
            storageSelect.append(option);
            for (var i = 0; i < data.data.rows.length; i++) {
                option = $("<option>").text(data.data.rows[i].storage_nickname).val(data.data.rows[i].storage_uuid).attr('type', data.data.rows[i].storage_type);
                storageSelect.append(option);
            }
            storageSelect.val('0');
        });
    }

    //初始化虚拟化类型
    var initVMType = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/vm/platforms/hypervisors', 'GET', function (d) {
            var data = d;
            var vmSelect = $('#currentJobModal #vm_hypervisor');
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

    //初始化所有备份节点
    var initNodeSelect = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/nodes', 'GET', function (d) {
            var data = d;
            var nodeSelect = $('#currentJobModal #current_node');
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

    //暂停
    var pauseJob = function (row) {
        var uuids = [];
        uuids.push(row.job_uuid);
        Metronic.blockUI({
            target: '#current_verify_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': uuids
        }, "/api/v1/jobs/pause", "POST", function (res) {
            Metronic.unblockUI('#current_verify_table');
            var op = LANG.UI_COPY_SEND_PAUSE_JOB_MESSAGE;
            if (operateResponseList(res, op)) {
                $('#current_verify_table').bootstrapTable('refresh');
                $("#current_verify_table").bootstrapTable('hideLoading');
            }
        });
    }
    //停止
    var stopJob = function (row) {
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

                callback: function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                        function (res) {
                            Metronic.unblockUI('#current_verify_table');
                            var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                            if (operateResponseList(res, op)) {
                                $('#current_verify_table').bootstrapTable('refresh');
                                $("#current_verify_table").bootstrapTable('hideLoading');
                            }
                        });
                }
            });
        } else if (12 == row.module_type_value) { //数据库实时模块
            bootBoxText = LANG.UI_JOB_STOP_DBCDP_JOB;
            bootbox.prompt({
                title: LANG.UI_JOB_STOP_DBCDP_JOB_TITLE,
                message: bootBoxText,
                inputType: 'password',
                callback: function (result) {
                    if (result == null) return;

                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    getUserPassword();
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_verify_table',
                            animate: true
                        });
                        pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST", function (res) {
                            Metronic.unblockUI('#current_verify_table');
                            var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                            if (operateResponseList(res, op)) {
                                $('#current_verify_table').bootstrapTable('refresh');
                                $("#current_verify_table").bootstrapTable('hideLoading');
                            }
                        });
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }

                }
            });
        } else if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_BACKUP) {
            var initErrorFlag = false;
            getUserPassword();
            bootbox.prompt({
                title: LANG.UI_VOL_CDP_JOB_STOP_BACKUP_CONFIRM,
                inputType: 'password',
                callback: function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_verify_table',
                            animate: true
                        });
                        pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                            function (res) {
                                var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                                Metronic.unblockUI('#current_verify_table');
                                if (operateResponseList(res, op)) {
                                    $('#current_job').bootstrapTable('refresh');
                                    $("#current_verify_table").bootstrapTable('hideLoading');
                                }
                            });
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
        } else if (CONF.TASK_TYPE.OS_INSTANT_RECOVERY == row.job_type_value && (row.job_status_value != CONF.TASK_STATUS.SUCCESSED && row.job_status_value != CONF.TASK_STATUS.WAITTING)) {
            //如果是操作系统瞬时恢复任务,停止的时候需要提示
            //如果任务是成功状态或者是等待状态 无需提示
            bootbox.confirm({
                title: LANG.UI_JOB_STOP_JOB_TITLE,
                message: LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS1  + '<br>' +
                    LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS2,
                callback: function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                        function (res) {
                            Metronic.unblockUI('#current_verify_table');
                            if (operateResponseList(res)) {
                                $('#current_verify_table').bootstrapTable('refresh');
                                $("#current_verify_table").bootstrapTable('hideLoading');
                            }
                        });
                }
            });
        } else if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(row.vm_type) && CONF.TASK_STATUS.STOPPING == row.job_status_value) {
            //公有云任务强制停止需二次确认
            bootbox.confirm({
                title: LANG.UI_JOB_FORCE_STOP,
                message: LANG.UI_JOB_FORCE_STOP_PUBLIC_CLOUD_CONFIRM_TIPS,
                callback: function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                        function (res) {
                            var op = LANG.UI_OS_SEND_FORCED_STOP_TASK_MESSAGE;
                            Metronic.unblockUI('#current_verify_table');
                            if (operateResponseList(res, op)) {
                                $('#current_verify_table').bootstrapTable('refresh');
                                $("#current_verify_table").bootstrapTable('hideLoading');
                            }
                        });
                }
            });
        } else if (row.module_type_value == 10000){ //旧数据库实时停止
            opJob('', 'stopJob', row);
        } else {
            Metronic.blockUI({
                target: '#current_verify_table',
                animate: true
            });
            pAjaxRequest({}, "/api/v1/jobs/stop/" + row.job_uuid + "", "POST",
                function (res) {
                    var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                    Metronic.unblockUI('#current_verify_table');
                    if (operateResponseList(res, op)) {
                        $('#current_verify_table').bootstrapTable('refresh');
                        $("#current_verify_table").bootstrapTable('hideLoading');
                    }
                }
            );
        }
    }
    //删除
    var deleteJob = function (row) {
        var module = row.module_type_value; //模块类型
        var taskType = row.job_type_value; //任务类型
        var message = LANG.UI_JOB_DELETE_JOB_TIPS;
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
                callback: function (result) {
                    if (result == null) return;

                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    getUserPassword();
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_verify_table',
                            animate: true
                        });
                        pAjaxRequest({}, "/api/v1/jobs/" + row.job_uuid + "", "DELETE", function (data) {
                            Metronic.unblockUI('#current_verify_table');
                            var op = LANG.UI_OS_SEND_DELETE_TASK_MESSAGE;
                            if (operateResponseList(data, op)) {
                                $("#current_verify_table").bootstrapTable('refresh');
                                $("#current_verify_table").bootstrapTable('hideLoading');
                            }
                        })
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
        } else {
            bootbox.confirm({
                title: LANG.UI_JOB_DELETE_JOB,
                message: message,
                callback: function (r) {
                    if (!r) return;
                    if (!deleteFlag) {
                        pAjaxRequest({}, "/api/v1/jobs/" + row.job_uuid + "", "DELETE", function (data) {
                            Metronic.unblockUI('#current_verify_table');
                            var op = LANG.UI_OS_SEND_DELETE_TASK_MESSAGE;
                            if (operateResponseList(data, op)) {
                                $("#current_verify_table").bootstrapTable('refresh');
                                $("#current_verify_table").bootstrapTable('hideLoading');
                            }
                        })
                    }
                }
            });
        }
    }

    //临时做删除数据库实时任务
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
            callback: function(r) {
                if(!r) return;
                if(!deleteFlag){
                    opJob('', 'deleteJob', row);
                    deleteFlag = true;
                    detailsInfo = null;	//清空展开任务详情信息
                }
            }
        });
    }

    var opJob = function(button, funName, row){
        var params = {};
        params.module = row.module_type_value;		//模块类型
        params.taskType = row.job_type_value;	//任务类型
        params.uuid = row.job_uuid;
        params.dbcdp = row.dbcdp_info;
        params = JSON.stringify(params);
        Metronic.blockUI({target: '#current_verify_table',animate: true});
        $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
            Metronic.unblockUI('#current_verify_table');
            if(OPREL(data)){
                $('#current_verify_table').bootstrapTable('refresh');
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
        if (taskType == 17) {
            editCopyJob(taskType, uuid);
        } else if (taskType == 19) {
            editArchiveJob(taskType, uuid);
        } else if (taskType == 37) {
            editVerifyJob(taskType, uuid);
        } else {
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
                            editFileJob(taskType, uuid);
                            break;
                        case 2: // NAS
                            url = './content/nas/nas_job_details.php';
                            break;
                        case 3: // HADOOP
                            editHadoopJob(taskType,uuid);
                            break;
                        case 4: // 对象存储
                            editObsJob(taskType, uuid);
                            break;
                        default:
                            break;
                    }

                    break;
                case 11:
                    editNasJob(taskType, uuid);
                    break;
                case 14:
                    editExchangeJob(taskType, uuid);
                    break;
                case 4:
                    editDBJob(taskType, uuid);
                    break
                case 5:
                    editOSJob(taskType, uuid);
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
        LOCATION(url);
    }

    //修改AWS备份任务
    var editAWSJob = function (taskType, uuid) {
        LOCATION('./content/aws/awsbackup.php?uuid=' + uuid);
    }

    //修改操作系统备份任务
    var editOSJob = function (taskType, uuid) {
        var url = './content/os/osbackupedit.php?uuid=' + uuid;
        LOCATION(url);
    }

    //修改数据库备份任务
    var editDBJob = function (taskType, uuid) {
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
                }
                LOCATION(url);
            } else if (!operateResponseList(d)) {
                //返回代理离线错误描述
            }
        });
    }

    //修改副本任务
    var editCopyJob = function (taskType, uuid) {
        var url = '';
        url = './content/copy/copy.php?uuid=' + uuid;
        LOCATION(url);
    }

    //修改归档任务
    var editArchiveJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case 19:
                //归档
                url = './content/archive/addarchive.php?uuid=' + uuid;
                break;
        }
        LOCATION(url);
    }
    //修改数据验证任务
    var editVerifyJob = function (taskType, uuid) {
        var url = '';
        url = './content/platform/dataverification/add_verification_job.php?uuid=' + uuid;
        LOCATION(url);
    }
    //修改文件任务
    var editFileJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case 1:
                //备份
                url = './content/fs/filebackupedit.php?uuid=' + uuid;
                break;
        }
        LOCATION(url);
    }
    //修改nas任务
    var editNasJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case 1:
                //备份
                url = './content/nas/nasbackupedit.php?uuid=' + uuid;
                break;
        }
        LOCATION(url);
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
        LOCATION(url);
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
        LOCATION(url);
    }

    //修改数据库复制任务
    var editDbCopyJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case CONF.TASK_TYPE.DB_CDP_SYN:
                //实时备份
                url = './content/dbcdp/dbcdp_edit.php?uuid=' + uuid;
                break;
        }
        LOCATION(url);
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
        LOCATION(url);
    }

    //修改Hadoop任务
    var  editHadoopJob =  function(taskType, uuid){
        var url = '';
        switch (taskType) {
            case 1:
                //备份
                url = './content/hadoop/hadoop_backup.php?uuid=' + uuid + '&editflag=true';
                break;
        }
        LOCATION(url);
    }

    // 修改对象存储任务
    var editObsJob = function (taskType, uuid) {
        var url = '';
        switch (taskType) {
            case 1:
                //备份
                url = './content/s3/obsbackup.php?uuid=' + uuid;
                break;
        }
        LOCATION(url);
    }

    //启动完全
    var startJob = function (row) {
        if (row.module_type_value == 12 || row.job_type_value == 47) {
            //数据库实时恢复
            startJobUnify(row, 2);
        } else if (row.module_type_value == 10000) {
            startDBCDPJob(row);
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

        Metronic.blockUI({target: '#current_verify_table',animate: true});
        $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startJob',p:params}, function(data){
            Metronic.unblockUI('#current_verify_table');
            if(OPREL(data)){
                $('#current_verify_table').bootstrapTable('refresh');
            }
        });
    }

    //迁移
    var motion = function (row, type) {
        var params = row;
        var url = "";
        if (params.module_type_value == CONF.MODULE_TYPE.OS) {
            //如果是操作系统模块
            url = './content/os/osmotion.php?module=' + params.module + "&submodule=" + params.subModule +
                '&tasktype=' + params.job_type_value + '&uuid=' + params.job_uuid;
        } else {
            //scp虚拟化直接使用默认配置提交
            if (CONF.VM_TYPE.SANGFORVVDK == params.vm_type) {
                var jsonData = JSON.stringify({
                    instantTaskUUID: params.job_uuid
                });
                $.post(CONF.AJAXPATH, {
                    m: CONF.M.VM,
                    f: 'createSangforScpMotionJob',
                    p: jsonData
                }, function (d) {
                    if (OPREL(d)) {
                        url = './content/vm/vm_instant_job_details.php?type=' + params.job_type_value + '&uuid=' + params.job_uuid;
                        LOCATION(url);
                    }
                });
                return;
            }

            url = './content/vm/vmmotion.php?module=' + params.module_type_value + "&submodule=" + params.vm_type +
                '&tasktype=' + params.job_type_value + '&uuid=' + params.job_uuid;
        }
        LOCATION(url);
    }

    var startCDPFun = function (row, url, method) {
        var params = [row.job_uuid];
        var op = LANG.UI_OS_SEND_START_TAKEOVER_TASK_MESSAGE;
        if (row.takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL) { //验证任务
            op = LANG.UI_JOB_SEND_START_TAKEOVER_AND_VERIF_TASK_MESSAGE;
        }
        Metronic.blockUI({
            target: '#current_verify_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': params
        }, url, method, function (data) {
            Metronic.unblockUI('#current_verify_table');
            if (operateResponseList(data, op)) {
                $('#current_verify_table').bootstrapTable('refresh');
            }
        });
    }

    var start_vol_cdp_takeover = function (row, url, method) {
        var taskType = row.job_type_value;

        if (taskType == CONF.TASK_TYPE.VOL_CDP_RECOVERY) {
            var initErrorFlag = false;
            getUserPassword();
            bootbox.prompt({
                title: LANG.UI_VOL_CDP_JOB_START_RECOVERY_CONFIRM,
                inputType: 'password',
                callback: function (result) {
                    if (result == null) return;
                    // hex_md5(result) == 'Admin@3R'
                    if (result == '123456') {
                        _userIsVerify = true;
                        startCDPFun(row, url, method);
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
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
        if (taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER || taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP) {
            start_vol_cdp_takeover(row, '/api/v1/jobs/start_takeover', 'POST');
        } else {
            bootbox.confirm({
                title: title,
                message: LANG.UI_JOB_START_TAKEOVER_TIPS1 + '<br>' +
                    LANG.UI_JOB_START_TAKEOVER_TIPS2 + '<br>' +
                    LANG.UI_JOB_START_TAKEOVER_TIPS3,
                callback: function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    pAjaxRequest({
                        'job_uuids': params,
                    }, '/api/v1/jobs/start_takeover', 'POST', function (data) {
                        var op = LANG.UI_OS_SEND_START_TAKEOVER_TASK_MESSAGE;
                        Metronic.unblockUI('#current_verify_table');
                        if (operateResponseList(data, op)) {
                            $('#current_verify_table').bootstrapTable('refresh');
                            $("#current_verify_table").bootstrapTable('hideLoading');
                        }
                    });
                }
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
            callback: function (result) {
                if (result == null) return;
                if (hex_md5(result) == _UserPassword) {
                    _userIsVerify = true;
                    stopTakeoverJob(row);
                } else {
                    $('.bootbox-input').css('border-color', "#a94442");
                    if (!initErrorFlag) {
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }
            }
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
            target: '#current_verify_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': uuids
        }, '/api/v1/jobs/stop_takeover', 'POST', function (d) {
            Metronic.unblockUI('#current_verify_table');
            if (operateResponseList(d, op)) {
                $('#current_verify_table').bootstrapTable('refresh');
                $("#current_verify_table").bootstrapTable('hideLoading');
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
            target: '#current_verify_table',
            animate: true
        });
        pAjaxRequest({'job_uuid': uuids, 'enable_flag': autoTakeoverFlag}, '/api/v1/jobs/switch_autotakeover', 'POST', (res)=>{
            Metronic.unblockUI('#current_verify_table');
            if (operateResponseList(res)) {
                $('#current_verify_table').bootstrapTable('refresh');
                $("#current_verify_table").bootstrapTable('hideLoading');
            }
        });
    }

    //启动回切
    var startfailback = function (row) {
        var info = {};
        info.task_uuid = row.job_uuid;
        info.task_type = row.job_type_value;
        info.start_type = 14; //启动回切
        var paramsInfo = JSON.stringify(info);
        Metronic.blockUI({
            target: '#current_verify_table',
            animate: true
        });
        $.post(CONF.AJAXPATH, {
            m: CONF.M.JOB,
            f: "getStartTaskObjectInfo",
            p: paramsInfo
        }, function (d) {
            Metronic.unblockUI('#current_verify_table');
            var dataInfo = JSON.parse(d);
            if (dataInfo.length == 0) {
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK, LANG.UI_VOL_CDP_JOB_DEFAILS_FAILBACK_CONFIG_MESSAGE);
                return;
            }
            var masterInfo = dataInfo['master_info'];
            var targetMachine = dataInfo['target_machine'];
            var targetVol = dataInfo['target_vol'];
            var volStr = $.map(targetVol, function(item) {
                var name = item.vol_name;
                return name;
            }).join(', ');

            var titleTips = "<span>" + LANG.UI_VOL_CDP_BACKUP_TIPS + "</span>: ";
            var desSpan = "  <span style='font-size: 14px;'>"
                + LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS1 + "  "
                + masterInfo + " " + LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS2 + "  "
                + targetMachine + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS1
                + "  [  " + volStr+" ],  "+LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS
                + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS +"</br></span>"
                + "<span>"+LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS1+"</span>";
            var failBackDes = titleTips + desSpan;

            var initErrorFlag = false;
            getUserPassword();
            bootbox.prompt({
                title: failBackDes,
                inputType: 'password',
                callback: function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        startFailbackJob(row);
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
        });
    }
    /**
     * 启动回切任务执行函数
     */
    var startFailbackJob = function (row) {
        var params = [row.job_uuid];

        Metronic.blockUI({
            target: '#current_verify_table',
            animate: true
        });
        pAjaxRequest({
            'job_uuids': params
        }, '/api/v1/jobs/start_failback', 'POST', function (res) {
            Metronic.unblockUI('#current_verify_table');
            if (res.data.info.length > 0) {
                var op = LANG.UI_JOB_SEND_START_FAILBACK_MSG_TIPS;
                if (operateResponseList(res, op)) {
                    $('#current_verify_table').bootstrapTable('refresh');
                    $("#current_verify_table").bootstrapTable('hideLoading');
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
                callback: function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        startJobFunc(row, taskType, type);
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
        });
    }



    var startJobFunc = function (row, taskType, type) {
        Metronic.blockUI({
            target: '#current_verify_table',
            animate: true
        });

        var params = {
            'start_type': type
        };
        pAjaxRequest(params, "/api/v1/jobs/start/" + row.job_uuid + "", 'POST', function (data) {
            Metronic.unblockUI('#current_verify_table');
            var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
            if (operateResponseList(data, op)) {
                $("#current_verify_table").bootstrapTable('refresh');
                $("#current_verify_table").bootstrapTable('hideLoading');
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

    var batchStart = function () {
        var params = [];
        var row = $('#current_verify_table').bootstrapTable('getSelections');
        $.each(row, function (index) {
            params.push(row[index].job_uuid)
        })
        Metronic.blockUI({
            target: '#current_verify_table',
            animate: true
        });
        pAjaxRequest({
            "job_uuids": params,
            "start_type": 0
        }, '/api/v1/jobs/start', 'POST', function (data) {
            Metronic.unblockUI('#current_verify_table');
            var op = LANG.UI_JOB_SEND_BATCH_START_TASK_MESSAGE;
            if (operateResponseList(data, op)) {
                $("#current_verify_table").bootstrapTable('refresh');
                $("#current_verify_table").bootstrapTable('hideLoading');
            }
        });
    }

    //批量删除任务
    var batchDelete = function () {
        var message = LANG.UI_JOB_DELETE_JOB_TIPS;
        var params = [];
        var row = $('#current_verify_table').bootstrapTable('getSelections');
        if (21 == row.job_type_value) {
            //如果是数据库实时任务,换一下提示语TODO
            message = LANG.UI_JOB_DELETE_JOB_TIPS + LANG.UI_JOB_DEL_RTTASK_DEL_BAKDATA;
        }
        bootbox.confirm({
            title: LANG.UI_JOB_DELETE_JOB,
            message: message,
            callback: function (r) {
                if (!r) return;
                if (!deleteFlag) {
                    $.each(row, function (index) {
                        params.push(row[index].job_uuid);
                    })
                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    pAjaxRequest({
                        "job_uuids": params
                    }, '/api/v1/jobs', 'DELETE', function (data) {
                        Metronic.unblockUI('#current_verify_table');
                        $('#current_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                        $('#current_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");

                        var op = LANG.UI_JOB_SEND_BATCH_DELETE_TASK_MESSAGE;
                        if (operateResponseList(data, op)) {
                            deleteFlag = true;
                            $("#current_verify_table").bootstrapTable('refresh');
                            $("#current_verify_table").bootstrapTable('hideLoading');
                        }
                    });
                }
            }
        })


    }

    //批量停止
    var batchStop = function () {
        //设置批量删除时特殊情况处理flag
        var isVolCdp = false;
        var isOsInstant = false;
        var isInstantDbCdp = false;
        var isVolCdpTakeover = false;

        var params = [];
        var row = $('#current_verify_table').bootstrapTable('getSelections');
        $.each(row, function (index) {
            params.push(row[index].job_uuid)
        })
        for (let i = 0; i < row.length; i++) {
            if (7 == row[i].job_type_value || 12 == row[i].module_type_value) {
                //如果是瞬时恢复任务或数据库实时模块,停止的时候需要提示
                isInstantDbCdp = true;
            } else if (CONF.TASK_TYPE.VOL_CDP_BACKUP == row[i].job_type_value) {
                isVolCdp = true;
            } else if (CONF.TASK_TYPE.VOL_CDP_TAKEOVER == row[i].job_type_value) {
                isVolCdpTakeover = true;
            } else if (CONF.TASK_TYPE.OS_INSTANT_RECOVERY == row[i].job_type_value) {
                //如果是操作系统瞬时恢复任务,停止的时候需要提示
                isOsInstant = true;
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

                callback: function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    pAjaxRequest({
                        "job_uuids": params
                    }, '/api/v1/jobs/stop', 'POST', function (data) {
                        Metronic.unblockUI('#current_verify_table');
                        var op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                        if (operateResponseList(data, op)) {
                            $("#current_verify_table").bootstrapTable('refresh');
                            $("#current_verify_table").bootstrapTable('hideLoading');
                        }
                    });
                }
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
                callback: function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        _userIsVerify = true;
                        Metronic.blockUI({
                            target: '#current_verify_table',
                            animate: true
                        });
                        pAjaxRequest({
                            "job_uuids": params
                        }, '/api/v1/jobs/stop', 'POST', function (data) {
                            Metronic.unblockUI('#current_verify_table');
                            var op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                            if (operateResponseList(data, op)) {
                                $("#current_verify_table").bootstrapTable('refresh');
                                $("#current_verify_table").bootstrapTable('hideLoading');
                            }
                        });
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
        } else if (isOsInstant) {
            //如果是操作系统瞬时恢复任务,停止的时候需要提示
            bootbox.confirm({
                title: LANG.UI_JOB_STOP_JOB_TITLE,
                message: LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS1 + '<br>' +
                    LANG.UI_OS_STOP_INSTANT_RECOVERY_TIPS2,
                callback: function (r) {
                    if (!r) return;
                    Metronic.blockUI({
                        target: '#current_verify_table',
                        animate: true
                    });
                    pAjaxRequest({
                        "job_uuids": params
                    }, '/api/v1/jobs/stop', 'POST', function (data) {
                        Metronic.unblockUI('#current_verify_table');
                        var op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                        if (operateResponseList(data, op)) {
                            $("#current_verify_table").bootstrapTable('refresh');
                            $("#current_verify_table").bootstrapTable('hideLoading');
                        }
                    });
                }
            });
        } else {
            Metronic.blockUI({
                target: '#current_verify_table',
                animate: true
            });
            pAjaxRequest({
                "job_uuids": params
            }, '/api/v1/jobs/stop', 'POST', function (data) {
                Metronic.unblockUI('#current_verify_table');
                var op = LANG.UI_JOB_SEND_BATCH_STOP_TASK_MESSAGE;
                if (operateResponseList(data, op)) {
                    $("#current_verify_table").bootstrapTable('refresh');
                    $("#current_verify_table").bootstrapTable('hideLoading');
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

    var toVmBackup = function () {
        LOCATION('./content/vm/vmbackup.php', 'vmbackup');
    }

    var toVmRecover = function () {
        LOCATION('./content/vm/vmrecover.php', 'vmrecover');
    }

    var toVmInstantRecover = function () {
        LOCATION('./content/vm/vminstantrecover.php', 'vminstantrecover');
    }

    var toVmGrainRecover = function () {
        LOCATION('./content/vm/vmgrainrecover.php', 'vmrecovera');
    }

    var toVmCloudSync = function () {
        LOCATION('./content/cbr/cbrbackup.php', 'cbrbackup');
    }

    var toDatabaseBackup = function () {
        LOCATION('./content/dbprotect/dbbackup.php', 'db_backup');
    }

    var toDatabaseRecover = function () {
        LOCATION('./content/dbprotect/dbrecover.php', 'db_recovery');
    }

    var toOsBackup = function () {
        LOCATION('./content/os/osbackup.php', 'osbackup');
    }

    var toOsRecover = function () {
        LOCATION('./content/os/osrecover.php', 'osrecover');
    }

    var toOsInstant = function () {
        LOCATION('./content/os/osinstantrecover.php', 'osinstantrecover');
    }

    var toVolCDPBackup = function () {
        LOCATION('./content/volcdp/vol_cdp_backup.php', 'vol_cdp_backup');
    }

    var toVolCDPRecover = function () {
        LOCATION('./content/volcdp/vol_cdp_recover.php', 'vol_cdp_recovery');
    }

    var toVolCDPTakeover = function () {
        LOCATION('./content/volcdp/vol_cdp_takeover.php', 'vol_cdp_takeover');
    }

    var toFsBackup = function () {
        LOCATION('./content/fs/filebackup.php', 'filebackup');
    }

    var toFsRecover = function () {
        LOCATION('./content/fs/filerecover.php', 'filerecover');
    }

    var toNASBackup = function () {
        LOCATION('./content/nas/nasbackup.php', 'nasbackup');
    }

    var toNASRecover = function () {
        LOCATION('./content/nas/nasrecover.php', 'nasrecover');
    }

    var toOBSBackup = function () {
        LOCATION('./content/s3/obsbackup.php', 'obsbackup');
    }

    var toOBSRecover = function () {
        LOCATION('./content/s3/obsrecover.php', 'obsrecover');
    }

    var toExchangeBackup = function () {
        LOCATION('./content/exchange/exchange_backup.php', 'exchange_backup');
    }

    var toExchangeRecover = function () {
        LOCATION('./content/exchange/exchange_recover.php', 'exchange_recover');
    }

    var toAWSBackup = function () {
        LOCATION('./content/aws/awsbackup.php', 'awsbackup');
    }

    var toAWSRecover = function () {
        LOCATION('./content/aws/awsrecover.php', 'awsrecover');
    }

    var toAWSGrainRecover = function () {
        LOCATION('./content/aws/awsgrainrecover.php', 'awsrecovera');
    }

    var toArchiveBackup = function () {
        LOCATION('./content/platform/archive/addarchive.php', 'archive_add');
    }

    var toHadoopBackup = function(){
        LOCATION('./content/hadoop/hadoop_backup.php', 'hadoop_backup');
    }

    var toHadoopRecover = function(){
        LOCATION('./content/hadoop/hadoop_recovery.php', 'hadoop_recover');
    }
    // 模块切换
    var moduleHandler = function () {
        $('#currentJobModal #current_tasktype').hide();
        $('#currentJobModal #vmTasktype').hide();
        $('#currentJobModal #fsTasktype').hide();
        $('#currentJobModal #dbTasktype').hide();
        $('#currentJobModal #copyTasktype').hide();
        $('#currentJobModal #vmtypeDiv').hide();
        $('#currentJobModal #dbtypeDiv').hide();
        $('#currentJobModal #osTaskType').hide();
        $('#currentJobModal #nasTaskType').hide();
        $('#currentJobModal #dbCDPTaskType').hide();
        $('#currentJobModal #volCdpTaskType').hide();
        $('#currentJobModal #awsTasktype').hide();
        $('#currentJobModal #exchangeTaskType').hide();

        if (this.value == "0") {
            $('#currentJobModal #current_tasktype').show();
        } else if (this.value == "2") {
            $('#currentJobModal #vmTasktype').show();
            $('#currentJobModal #vmtypeDiv').show();
        } else if (this.value == "3-1" || this.value == "3-3" || this.value == "3-4") {
            $('#currentJobModal #fsTasktype').show();
        } else if (this.value == "4") {
            $('#currentJobModal #dbTasktype').show();
            $('#currentJobModal #dbtypeDiv').show();
        } else if (this.value == "9") {
            $('#currentJobModal #copyTasktype').show();
        } else if (this.value == "10000") {
            $('#currentJobModal #dbCDPTaskType').show();
        } else if (this.value == "10001") {
            $('#currentJobModal #fileCDPTaskType').show();
        } else if (this.value == "5") {
            $('#currentJobModal #osTaskType').show();
        } else if (this.value == "10") {
            $('#currentJobModal #volCdpTaskType').show();
        } else if (this.value == "11") {
            $('#currentJobModal #nasTaskType').show();
        } else if (this.value == "14") {
            $('#currentJobModal #fsTasktype').show();
        } else if (this.value == "17") {
            $('#currentJobModal #awsTasktype').show();
        } else if (this.value == "22") { //私有云和公有云任务类型一致
            $('#currentJobModal #awsTasktype').show();
        }
    }

    var dbTypeFormater = function (index, row) {
        let jobType = parseInt(row.job_type_value);
        if (CONF.TASK_TYPE['DB_BACKUP'] !== jobType && CONF.TASK_TYPE['DB_RECOVERY'] !== jobType) {
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
        $('#current_verify_table #' + uuid + ' .' + option + ' .btn').prop('disabled', true);
    }

    // 添加按钮隐藏的样式
    var addHiddenButton = function (uuid, option) {
        $('#current_verify_table #' + uuid + ' .' + option).hide();
    }

    //卷CDP任务运行中的任务控制
    var volCdpTaskRunningControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskType) {
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
                    case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
                    case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
                    case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "startfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "startfailback");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC: //逆向实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: //回切启动中
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "startfailback");
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

    //卷CDP任务错误状态下的任务控制
    var volcdpTaskErrorControlButton = function (uuid, taskType, taskCurrentStage) {
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
                        break;
                }
                break;
            case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
                addForbidButton(uuid, "stop");
                break;
            case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
                switch (taskCurrentStage) {
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER: //接管中
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING: //接管启动中
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //					addForbidButton(uuid, "stopfailback");
                        addForbidButton(uuid, "start");
                        addForbidButton(uuid, "stop");
                        break;
                    case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOFAILBACK_INIT_SYNCVER_STARTING: //逆向初始同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC: //逆向实时同步
                    case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: //回切启动中
                        addForbidButton(uuid, "takeover");
                        //					addForbidButton(uuid, "stopfailback");
                        addForbidButton(uuid, "edit");
                        addForbidButton(uuid, "delete");
                        break;
                }
        }
    }

    //卷CDP任务等待状态下的任务控制
    var volCdpTaskWaittingControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskType) {
            case CONF.TASK_TYPE.VOL_CDP_BACKUP:
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
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                $('#' + uuid + ' .stop').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
                break;
            case 41:
            case 51: //逆向实时同步
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "pause");
                addForbidButton(uuid, "takeover"); //启动接管
                addForbidButton(uuid, "stoptakeover");
                // addForbidButton(uuid, "startfailback");
                break;
        }
    }

    //数据库CDP在停止状态下的任务控制
    var dbCdpTaskStopControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskCurrentStage) {
            case 10:
            case 11:
            case 20: //初始同步
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
                addForbidButton(uuid, "edit");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
            case 31: //接管中
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "edit");
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

    //数据库CDP在错误状态下的任务控制
    var dbCdpTaskErrorControlButton = function (uuid, taskType, taskCurrentStage) {
        switch (taskCurrentStage) {
            case 0: //未知
                // addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");
                // addForbidButton(uuid, "edit");
                break;
            case 10:
            case 11:
            case 20: //初始同步
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");
                break;
            case 21: //实时同步
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");
                break;
            case 30: //接管启动中
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");
                break;
            case 31: //接管中
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "stop");
                break;
            case 40:
            case 50: //逆向初始同步
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "edit");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "delete");
                break;
            case 41:
            case 51: //逆向实时同步
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "edit");
                addForbidButton(uuid, "stopfailback");
                addForbidButton(uuid, "delete");
                break;
            case 53: //回切完成
                addForbidButton(uuid, "start");
                addForbidButton(uuid, "stop");
                addForbidButton(uuid, "takeover");
                addForbidButton(uuid, "edit");
                addForbidButton(uuid, "stoptakeover");
                addForbidButton(uuid, "startfailback");
                addForbidButton(uuid, "stopfailback");
                break;
        }
        if (taskType == 47) {
            //数据库恢复单独处理
            addForbidButton(uuid, "edit");
            addForbidButton(uuid, "stop");
        }
    }

    // 任务控制按钮
    var addOpButton = function () {
        var data = $("#current_verify_table").bootstrapTable("getData");
        //做各模块任务控制
        for (var i = 0; i < data.length; i++) {
            var uuid = data[i].job_uuid;
            var modules = data[i].module_type_value;
            var status = data[i].job_status_value;
            var strategy = data[i].mode_list;
            var taskType = data[i].job_type_value;
            var taskCurrentStage = data[i].job_stage;
            var dbcdpCurrentStage = data[i].job_dbcdp_stage;
            var uuid = data[i].job_uuid;
            if (modules == "14") { //exchange无任务
                addForbidButton(uuid, "startDiff");
            }
            if (CONF.MODULE_TYPE.VM == modules) {
                if (CONF.VM_TYPE.INSPURVVDK == data[i].vm_type || CONF.VM_TYPE.LENOVOAIO == data[i].vm_type) {
                    //无差异备份的虚拟化
                    addHiddenButton(uuid, "startDiff");
                }
            }
            switch (status) {
                case CONF.TASK_STATUS.WAITTING: //等待
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "motion");
                    //卷CDP任务无启动任务相关的时间策略，在等待状态下暂时不支持停止操作
                    if (modules == CONF.MODULE_TYPE.VOL_CDP) {
                        volCdpTaskWaittingControlButton(uuid, taskType, taskCurrentStage);
                    } else if (modules == 12) {
                        //数据库实时可以修改
                        addForbidButton(uuid, "stop");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                    } else {
                        addForbidButton(uuid, "edit");
                    }
                    //卷CDP任务无启动任务相关的时间策略，在等待状态实际处于新建状态，允许对其进行删除
                    if (modules != CONF.MODULE_TYPE.VOL_CDP && modules != 12) {
                        addForbidButton(uuid, "delete");
                    }
                    //数据库CDP在运行状态下的任务控制
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
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "startStra");
                    addForbidButton(uuid, "startDiff");
                    addForbidButton(uuid, "startIncr");
                    addForbidButton(uuid, "startLog");
                    volCdpTaskRunningControlButton(uuid, taskType, taskCurrentStage); //卷cdp任务在运行状态下的控制操作

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
                        dbCdpTaskRunningControlButton(uuid, taskType, dbcdpCurrentStage);
                    }
                    break;
                case CONF.TASK_STATUS.PAUSED: //暂停
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "startStra");
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
                    break;
                case CONF.TASK_STATUS.STOPPED: //停止
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "stop");
                    addForbidButton(uuid, "motion");
                    volcdpTaskStopControlButton(uuid, taskType, taskCurrentStage);
                    //数据库CDP在停止状态下的任务控制
                    if (modules == 12) {
                        dbCdpTaskStopControlButton(uuid, taskType, dbcdpCurrentStage);
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
                    //停止中状态，变为强制停止
                    $('#' + uuid + ' .stop').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
                    break;
                case CONF.TASK_STATUS.NETWORK_FAULT: //网络故障
                    addForbidButton(uuid, "pause");
                    addForbidButton(uuid, "delete");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "startStra");
                    if (modules == CONF.MODULE_TYPE.VOL_CDP) {
                        addForbidButton(uuid, "takeover");
                        addForbidButton(uuid, "stoptakeover");
                        addForbidButton(uuid, "startfailback");
                        //						addForbidButton(uuid, "stopfailback");
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
                    if (modules != CONF.MODULE_TYPE.VOL_CDP && modules != 12) {
                        addForbidButton(uuid, "edit");
                        addForbidButton(uuid, "delete");
                    }
                    volcdpTaskErrorControlButton(uuid, taskType, taskCurrentStage);
                    if (modules == 12) {
                        dbCdpTaskErrorControlButton(uuid, taskType, dbcdpCurrentStage);
                    }
                    break;
                case CONF.TASK_STATUS.SYNC: //任务同步
                    break;
                case CONF.TASK_STATUS.PREPARING: //准备中
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
                    if (modules != CONF.MODULE_TYPE.VOL_CDP) {
                        addForbidButton(uuid, "takeover"); //启动接管
                        addForbidButton(uuid, "stoptakeover"); //停止接管
                        addForbidButton(uuid, "startfailback"); //启动回切
                        //						addForbidButton(uuid, "stopfailback");  //停止回切
                    }
                    break;
                case CONF.TASK_STATUS.FINISHED: //已完成
                    addForbidButton(uuid, "stop");
                    break;
                case CONF.TASK_STATUS.TAKEOVER: //接管
                    addForbidButton(uuid, "start");
                    addForbidButton(uuid, "stop");
                    addForbidButton(uuid, "edit");
                    addForbidButton(uuid, "delete");
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
                        if (dbcdpCurrentStage == 52) {
                            addForbidButton(uuid, "start");
                            addForbidButton(uuid, "stop");
                            addForbidButton(uuid, "pause");
                            addForbidButton(uuid, "takeover");
                            addForbidButton(uuid, "startfailback"); //启动回切
                            addForbidButton(uuid, "stopfailback");
                            addForbidButton(uuid, "stoptakeover"); //停止接管
                            // addForbidButton(uuid, "delete");
                            addForbidButton(uuid, "edit");
                        } else {
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
                    break;
                default:
                    break;
                case 14: //exchange
                    addForbidButton(uuid, "startDiff");
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

            //卷cdp未开启自动接管配置，禁用切换操作
            if (data[i].auto_takeover_flag == CONF.FLAG.UNSET) {
                addForbidButton(uuid, "stopautotakeover");
                addForbidButton(uuid, "startautotakeover");
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

    var lastIndex = [-1, -1];
    var current_detail = function (index, row, element) {
        element = '.detail-view td'; // bug15605 因为自动刷新表格，导致原本的jquery对象已经不见了，所以找不到元素，将element 固定为.detail-view td，因为同时只会有一个.detail-view td 存在

        if (index != lastIndex[1]) {
            lastIndex.push(index);
            $('#current_verify_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
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
                html += '<p>' + LANG.UI_JOB_ONCE_TIME_RECOVER + '</p>';
                $(element).append(html);
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
                        case 'reserve_strategy':
                            var reserve_strategy_type;
                            let reserve_strategy_unit;
                            var des = '';
                            if (row.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                                // 磁带没有保留策略
                                return '';
                            }
                            // 虚拟机和操作系统需要新增保留类型模式
                            if(row.module_type_value == CONF.MODULE_TYPE.VM || row.module_type_value == CONF.MODULE_TYPE.OS){
                                if(CONF.RESERVE_STRATEGY_MODE.POINT == data.reserve_strategy.strategy_mode){
                                    des += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
                                }else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reserve_strategy.strategy_mode){
                                    des += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
                                }
                            }
                            if (data.reserve_strategy.type == 1) {
                                reserve_strategy_type = LANG.UI_STRATEGY_RESERVE_NUM;
                                reserve_strategy_unit = LANG.UI_STRATEGY_VALUE
                            } else if (data.reserve_strategy.type == 2) {
                                reserve_strategy_type = LANG.UI_STRATEGY_RESERVE_DAY;
                                reserve_strategy_unit = LANG.UI_STRATEGY_RETENTION_DAYS;
                            } else {
                                reserve_strategy_type = LANG.UI_STRATEGY_PERMANENT_RESERVE;
                            }
                            if (data.reserve_strategy.type == 3) {
                                des += reserve_strategy_type;
                            } else {
                                des += reserve_strategy_type + '，' + reserve_strategy_unit + '：' + data.reserve_strategy.value
                            }
                            if (data.reserve_strategy.value == 0) {
                                return;
                            }
                            $(element).append(
                                '<div style="display:flex; align-items:center"><b>'+ LANG.UI_STRATEGY_RESERVE+'：</b><div style="display:inline-block" id= "reserve_strategy_detail"><p>' + des + '</p></div></div>'
                            );
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
                                html += `<p>`+ LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_VOL +`：` + v.display_name + `， `+ LANG.UI_VOL_CDP_JOB_STORAGE +`：` + v.capacity + `</p>`
                            })

                            html += `</div></div>`;
                            $(element).append(html);
                            break;
                        case 'time_strategy_backup_type':  // 时间策略类别
                            var allowTaskType = [
                                CONF.TASK_TYPE.BACKUP,
                                CONF.TASK_TYPE.DB_BACKUP,
                                CONF.TASK_TYPE.OS_BACKUP,
                            ];
                            if (!allowTaskType.includes(data.task_type)) {
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
                            var timeStrategy_mode;
                            var timeStrategy_roll;
                            var frequency = "";

                            if (row.job_type_value == 32) { //卷CDP标签策略
                                var html = `<div style="display: flex; align-items: center"><b>` + LANG.UI_VOL_CDP_JOB_DETAILS_LABEL_STRATEGY + `：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                            } else {
                                var html = `<div style="display: flex; align-items: center"><b>` + LANG.UI_STRATEGY_TIME + `：</b><div id="timeStrategy_info" style='display:inline-block'>`;
                            }

                            $.each(data.time_strategy, function (k, v) {
                                var days = [];
                                /*if (v.roll_flag == true) {
                                    timeStrategy_roll = LANG.UI_STRATEGY_ROLL_INTERVAL+' ' + v.roll_interval + ', '+ LANG.UI_STRATEGY_ROLL_OVER_TIME + v.end_time + ')';
                                } else {
                                    timeStrategy_roll = LANG.UI_STRATEGY_ROLL_NO+')';
                                }*/
                                timeStrategy_roll = ')';
                                if (v.mode == 1) {
                                    timeStrategy_mode = LANG.UI_PUBLIC_BACKUP_FULL;
                                    //如果是CBR任务 需要改成同步
                                    if (tasktype == CONF.TASK_TYPE.VM_HUAWEI_CBR_SYNC) {
                                        timeStrategy_mode = LANG.UI_CLOUD_PLATFORM_SYNC;
                                    }
                                    if (tasktype === CONF.TASK_TYPE.DB_RECOVERY) {  // 恢复策略
                                        timeStrategy_mode = LANG.UI_STRATEGY_RECOVERY;
                                    }
                                } else if (v.mode == 2) {
                                    timeStrategy_mode = LANG.UI_STRATEGY_INCREMENT;
                                } else if (v.mode == 9) {
                                    timeStrategy_mode = LANG.UI_STRATEGY_PERMANENT_INCREMENT;
                                } else if (v.mode == 3) {
                                    timeStrategy_mode = LANG.UI_PUBLIC_BACKUP_DIFFRENCE;
                                } else if (v.mode == 5) {
                                    timeStrategy_mode = LANG.UI_COPY_TIME_STRATEGY;
                                } else if (v.mode == 6) {
                                    timeStrategy_mode = LANG.UI_ARCHIVE_TIME_STRATEGY;
                                } else {
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
                                    } else {
                                        timeStrategy_mode = LANG.UI_PUBLIC_BACKUP_ARCHIVE_LOG;
                                    }
                                }
                                if (row.job_type_value == 37) { //数据验证
                                    timeStrategy_mode = LANG.UI_STRATEGY_VERTIFY;
                                }
                                if (row.job_type_value == 2) { //虚拟机恢复
                                    timeStrategy_mode = LANG.UI_STRATEGY_RECOVERY;
                                }
                                if (row.job_type_value == 32) { //卷CDP标签策略
                                    timeStrategy_mode = '';
                                }
                                // 副本、归档直接显示策略
                                if (row.job_type_value == 17 || row.job_type_value == 18 || row.job_type_value == 19 || row.job_type_value == 20) {
                                    timeStrategy_mode = '';
                                    /*if (v.roll_flag == true) {
                                        timeStrategy_roll = LANG.UI_STRATEGY_ROLL_INTERVAL + ' ' + v.roll_interval + ', '+ LANG.UI_STRATEGY_ROLL_OVER_TIME + v.end_time + '';
                                    } else {
                                        timeStrategy_roll = LANG.UI_STRATEGY_ROLL_NO;
                                    }*/
                                    if (v.type == 2) {
                                        $.each(v.days, function (i, d) {
                                            if (d == 1) {
                                                days.push(i + 1);
                                            }
                                        })

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
                                        })
                                        html += `<p>` + timeStrategy_mode + LANG.UI_STRATEGY_MONTH_EN + days.join(', ') + `, ` + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `</p>`;
                                    } else if (v.type == 4) {
                                        html += `<p>` + LANG.UI_PUBLIC_START_TIME + '： ' + v.start_time + `</p>`;
                                    }
                                } else {
                                    if (v.type == 2) {
                                        $.each(v.days, function (i, d) {
                                            if (d == 1) {
                                                days.push(i + 1);
                                            }
                                        })

                                        // 间隔周数
                                        frequency = getStrategyFrequency(v.frequency);

                                        html += `<p>` + timeStrategy_mode + ` (` + frequency + LANG.UI_STRATEGY_WEEK_EN + days.join(', ') + `, ` + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `</p>`;
                                    } else if (v.type == 1) {
                                        html += `<p>` + timeStrategy_mode + ` (`+ LANG.UI_STRATEGY_DAY_EN + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `</p>`;
                                    } else if (v.type == 3) {
                                        $.each(v.days, function (i, d) {
                                            if (d == 1) {
                                                days.push(i + 1)
                                            }
                                        })
                                        html += `<p>` + timeStrategy_mode + ` (`+ LANG.UI_STRATEGY_MONTH_EN + days.join(', ') + `, ` + v.start_time + LANG.UI_STRATEGY_START + timeStrategy_roll + `</p>`;
                                    } else if (v.type == 4) {
                                        html += `<p>` + LANG.UI_PUBLIC_START_TIME + '： ' + v.start_time + `</p>`;
                                    }
                                }

                            })

                            html += `</div></div>`;
                            html += `<p><b>` + LANG.UI_PUBLIC_NEXT_RUN_TIME + '：</b>' + data.next_time + `</p>`;

                            $(element).append(html);
                            break;
                        case 'agent_info':
                            var des = LANG.UI_PUBLIC_CLIENT;
                            if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_RECOVERY) {
                                des = LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_CLIENT;
                            }
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
                            $(element).append(
                                '<p><b>'+ LANG.UI_JOB_HIS_BAK_TIMEPOINT +'：</b>' + data.time_point.timepoint[0] + '</p>',
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
                            if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(data.grain_detail.submoduletype)) {
                                //AWS的显示
                                html += "<p><b>" + LANG.UI_GRAIN_AWS_INSTANCE + ': </b>' + data.grain_detail.vmname + '</p>';
                            }else{
                                html += "<p><b>" + LANG.UI_VCENTER_VM + ': </b>' + data.grain_detail.vmname + '</p>';
                            }
                            html += "<p><b>" + LANG.UI_PUBLIC_TIMEPOINT + ': </b>' + data.grain_detail.timepoint + '</p>';
                            $(element).append(html);
                            break;
                        case 'instant_detail':
                            var detail = '';
                            // console.log(row);
                            var taskType = row.job_type_value; //任务类型
                            if (taskType == CONF.TASK_TYPE.OS_INSTANT_RECOVERY) {
                                //操作系统瞬时恢复
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.instant_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_OS_SOURCE_HOST + ': </b>' + data.instant_detail.sourcehost + '</p>';
                                detail += "<p><b>" + LANG.UI_OS_TARGET_HOST + ': </b>' + data.instant_detail.targethost + '</p>';
                                detail += "<p><b>" + LANG.UI_OS_CACHE_LOCATION + ': </b>' + data.instant_detail.cachetarget + '</p>';
                            }
                            if (taskType == CONF.TASK_TYPE.VM_INSTANT_RECOVERY) {
                                //虚拟机瞬时恢复
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.instant_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_OLD_NAME + ': </b>' + data.instant_detail.oldname + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_INSTANT_NEW_NAME + ': </b>' + data.instant_detail.newname + '</p>';
                            }
                            $(element).append(detail);
                            break;
                        case 'motion_detail':
                            var detail = '';
                            // console.log(row);
                            var taskType = row.job_type_value; //任务类型
                            if (taskType == CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION) {
                                //操作系统瞬时恢复
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.motion_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_OS_SOURCE_HOST + ': </b>' + data.motion_detail.sourcehost + '</p>';
                                detail += "<p><b>" + LANG.UI_OS_TARGET_HOST + ': </b>' + data.motion_detail.targethost + '</p>';
                            }
                            if (taskType == CONF.TASK_TYPE.VM_INSTANT_RECOVERY_MOTION) {
                                //vm瞬时恢复
                                detail += "<p><b>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ': </b>' + data.motion_detail.timepoint + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_OLD_NAME + ': </b>' + data.motion_detail.oldname + '</p>';
                                detail += "<p><b>" + LANG.UI_JOB_MOTION_NEW_NAME + ': </b>' + data.motion_detail.newname + '</p>';
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
                            var delayLoadTime = '--';
                            var autoTakeoverDes = '';
                            if (data.dbcdp_sync_info.delay_load_time != 0) {
                                delayLoadTime = data.dbcdp_sync_info.delay_load_time;
                            }

                            if (data.dbcdp_sync_info.auto_takeover_flag == CONF.FLAG.SET) {
                                autoTakeoverDes = LANG.UI_PUBLIC_ON;
                            } else {
                                autoTakeoverDes = LANG.UI_PUBLIC_OFF;
                            }
                            detail += "<p><b>" + LANG.UI_JOB_DELAY_LOAD_TIME + '： </b>' + delayLoadTime + 's</p>';
                            detail += "<p><b>" + `源数据库实例` + '： </b>' + data.dbcdp_sync_info.sourceDbInstance + '</p>';
                            detail += "<p><b>" + `目标数据库实例` + '： </b>' + data.dbcdp_sync_info.targetDbInstance + '</p>';
                            detail += "<p><b>" + `接管回切数据库实例` + '： </b>' + data.dbcdp_sync_info.failbackDbInstance + '</p>';
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
                                recoverTypeDes = `全量+增量`;
                            } else {
                                recoverTypeDes = `增量`;
                            }

                            detail += "<p><b>" + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIME_POINT + '： </b>' + timepoint + '</p>';
                            detail += "<p><b>" + `源数据库实例` + '： </b>' + data.dbcdp_recover_info.source_app_service_name + '</p>';
                            detail += "<p><b>" + `目标数据库实例` + '： </b>' + data.dbcdp_recover_info.target_app_service_name + '</p>';
                            detail += "<p><b>" + LANG.UI_VOL_CDP_JOB_DETAILS_AUTO_TAKEOVER + '： </b>' + recoverTypeDes + '</p>';
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

    //临时阻止数据库实时任务的批量操作
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

    // 卷实时批量停止判断
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

    var tableInit = function () {
        var operates = {
            'click .btn': function (event, value, row, index) {
                btnRecord(event.target);
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
                    deleteJob(row, 4);
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
        }

        let operateColumnVisible = CONF.PERMISSION_ARR.indexOf('p_verification_job_operate') > -1; // 验证任务是否分配管理权限标记

        var options = {
            toolbarId: '#vin_current_verify_toolbar',
            vin_toolbar: '.vin_current_toolbar',
            vin_url: '/api/v1/jobs',
            vin_method: 'GET',
            vin_params: function () {
                var params = {};
                params.accurateFlag = accurateFlag;
                if (JSON.parse(window.localStorage.getItem('current_table_BsTable'))) {
                    params.hiddenFields = JSON.parse(window.localStorage.getItem('current_table_BsTable')).join(); //如果有本地缓存，直接取本地缓存的列
                }
                params = $.extend(params, getParams());
                params.job_type = '37'; //默认传验证任务类型
                return params;
            },
            reorderableColumns:true,
            exportAllConfig: {  // 导出全部配置
                url: '/api/v1/jobs/export',
                fileName: LANG.UI_JOB_CURRENT_TASK,
            },
            sortName: 'create_time',
            sortOrder: 'desc',
            placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
            paginationLoop: false,
            hideColumns: "back_node,back_storage,running_time,vcenter_hypervisor,module_type_value,job_type_value,speed,vcenter_name,db_type,orchestration_name", //默认要隐藏的列，以“,”分割的字符串，没有就不写
            uniqueId: 'job_uuid',
            changeHeightBtn: false, //改变高度按钮
            showExport: false, //是否开启导出按钮
            // showColumns: false, //是否开启列选择按钮
            batchOperation: true, // 批量操作
            detailFormatter: current_detail,
            resizable: true,
            onRefresh: function (params) {
                $("#current_verify_table").bootstrapTable('hideLoading');
            },
            LoadSuccess: function (a, b, c) {
                let selectedRow = $('#current_verify_table').bootstrapTable("getSelections");
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
            onResetView: initTableHeight,
            onCheck: function (row, $element) {
                var selectedRow = $('#current_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;

                $('#current_verify_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");

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
                var selectedRow = $('#current_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                if (selectedRow.length == 0) {
                    $('#current_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                    $('#current_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");

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
                    $('#current_verify_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-half-checked");

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
                var selectedRow = $('#current_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;

                $('#current_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked");
                $('#current_verify_table thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-checked");

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
                var selectedRow = $('#current_verify_table').bootstrapTable("getSelections");
                checkIndex = selectedRow;
                $('#current_verify_table thead .bs-checkbox input[type=checkbox]').addClass("bootstrap-table-checked");

                $('#currentjobdiv .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>'+LANG.UI_JOB_SELECTED_ROWS+'' + selectedRow.length + '');
                if ($('#current_verify_table').find('.no-records-found').length > 0) {

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
                var tableData = $('#current_verify_table').bootstrapTable('getData');
                var selectedRow = $('#current_verify_table').bootstrapTable('getSelections');
                if (selectedRow.length == 0) {
                    resetBatchOp();
                };
                var filtersStorageToArr = {};
                var filtersStorage = JSON.parse(sessionStorage.getItem('current_table_filters'));
                let selectedFilters = 0;
                let totalFilters = 0;
                if (filterFlag == true) {
                    if (filtersStorage != '{}' && filtersStorage != undefined) {
                        filtersStorageToArr = $.each(filtersStorage, function (k, v) {
                            $.each(v, function (index, value) {
                                $('#vin_current_verify_toolbar #filterDiv .filter-content #' + k + ' input[value=' + value + ']').prop("checked", true);
                            })
                        });
                    };

                    $('#vin_current_verify_toolbar #filterDiv .filter-content input[type=checkbox]').each(function () {
                        totalFilters += 1;
                        if ($(this).prop("checked")) {
                            selectedFilters += 1
                        }
                    });

                    if (selectedFilters > 0) {
                        $('#vin_current_verify_toolbar #filterBtn span').text(LANG.UI_JOB_FILTER+'(' + selectedFilters + '/' + totalFilters + ')');
                    } else {
                        $('#vin_current_verify_toolbar #filterBtn span').text(LANG.UI_JOB_FILTER+'('+LANG.UI_JOB_FILTER_SELECTNONE+')');
                    };
                }
                if (tableData.length == 0)return;
                checkRecord();
                initTimer();
                $('#' + btnOpen + '').parent('.btn-group').addClass('open');
                if (!initFlag) {
                    $('#current_verify_table th[data-field="speed"]').css('width', '5%');
                    $('#current_verify_table th[data-field="progress"]').css('width', '5%');
                    $('#current_verify_table th[data-field="job_status_value"]').css('width', '5%');
                    $('#current_verify_table th[data-field="module_type_value"]').css('width', '6%');
                    $('#current_verify_table th[data-field="job_type_value"]').css('width', '6%');

                    $('#current_verify_table th[data-field="user_name"]').css('width', '8%');
                    $('#current_verify_table th[data-field="job_type_value"]').css('width', '8%');

                    $('#current_verify_table th[data-field="job_name"]').css('width', '30%');
                    initFlag = true;
                }

                if (changeHeightFlag == false) {
                    $('#current_verify_table>tbody>tr>td').css({
                        'padding-top': '4.25px',
                        'padding-bottom': '4.25px'
                    })
                    $('#vin_current_verify_toolbar .change_height i').removeClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#current_verify_table>tbody>tr>td').css({
                        'padding-top': '10.25px',
                        'padding-bottom': '10.25px'
                    })
                    $('#vin_current_verify_toolbar .change_height i').addClass('icon-auto-height2');
                }
                addOpButton();

                if (!operateColumnVisible) { // 角色无 验证任务 管理权限，隐藏 批量删除操作、 操作列 checkbox以及重置表格高度
                    $('.batchOperation').hide();
                    $('#vin_current_toolbar .rightTool .keep-open .dropdown-menu .dropdown-item-marker:last-child').hide();

                    $('.jobs-wrapper .table-container.current-job-table-container').css('height', 'calc(100% - 96px)');
                } else {
                    $('.batchOperation').show();

                    $('.jobs-wrapper .table-container.current-job-table-container').css('height', 'calc(100% - 126px)');
                }
            },
            columnsSwitch: function () {
                $('#current_verify_table').bootstrapTable('refresh');
            },
            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false, //默认可排序，禁用排序才写此项
                    forceHide: true,
                },
                {
                    field: 'job_name', //字段名
                    title: LANG.UI_SEARCH_TASK_NAME,
                    formatter: hrefFormatter,
                    events: operateEvents
                },
                {
                    field: 'module_type',
                    title: LANG.UI_SEARCH_MODE_TYPE
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
                    field: 'temp_name',
                    sortable: false, //默认可排序，禁用排序才写此项
                    title: LANG.UI_PLATFORM_JOB_TEMP_NAME,
                },
                {
                    field: 'approval_name',
                    sortable: false, //默认可排序，禁用排序才写此项
                    title: LANG.UI_PLATFORM_JOB_APPROVAL_NAME,
                },
                {
                    field: 'create_time',
                    title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
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
                    field: 'user_name',
                    title: LANG.UI_REPORT_BUILDER
                },
                {
                    field: 'back_node',
                    title: LANG.UI_BACKUP_NODE,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'back_storage',
                    title: LANG.UI_STORAGE,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'next_time',
                    title: LANG.UI_PUBLIC_NEXT_RUN_TIME,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (index, row) {
                        if(row.task_orchestration_plan_flag){
                            return `--`;
                        } else {
                            return row.next_time;
                        }
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
                    field: 'job_status_value',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (index, row){
                        switch (row.job_status_value) {
                            case CONF.TASK_STATUS.WAITTING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_PUBLIC_WAIT + '</span>';
                            case CONF.TASK_STATUS.STOPPING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_PUBLIC_STOPPING + '</span>';

                            case CONF.TASK_STATUS.PREPARING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_PUBLIC_READYING + '</span>';

                            case CONF.TASK_STATUS.RUNNING:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_HOMEPAGEPRO_VERIFYING + '</span>';
                            case CONF.TASK_STATUS.FINISHED:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_VISUAL_ALREADY_FINISH + '</span>';
                            case CONF.TASK_STATUS.PAUSED:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_JOB_PAUSE + '</span>';

                            case CONF.TASK_STATUS.SUCCESSED:
                                return '<span class="label label-sm label-success label-success_en">' + LANG.UI_PUBLIC_SUCCESS + '</span>';
                            case CONF.TASK_STATUS.STARTING:
                                return '<span class="label label-sm label-success label-success_en ">' + LANG.UI_PUBLIC_STARTING + '</span>';

                            case CONF.TASK_STATUS.STOPPED:
                                return '<span class="label label-sm label-default label-default_en">' + LANG.UI_VISUAL_STOP + '</span>';
                            case CONF.TASK_STATUS.ABNORMAL:
                                return '<span class="label label-sm label-warning label-warning_en">' + LANG.UI_NODE_ABNORMAL + '</span>';
                            case CONF.TASK_STATUS.NETWORK_FAULT:
                                return '<span class="label label-sm label-danger label-danger_en" style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_NETWORK_ERROR + '</span>';
                            case CONF.TASK_STATUS.CREATING:
                                return '<span class="label label-sm label-default label-default_en">' + LANG.UI_PUBLIC_CREATING + '</span>';
                            case CONF.TASK_STATUS.PENDING:
                                return '<span class="label label-sm label-warning label-warning_en">' + LANG.UI_PUBLIC_PENDING + '</span>';
                            case CONF.TASK_STATUS.ERROR:
                                return '<span class="label label-sm label-danger label-danger_en" style="width:auto; min-width:40px">' + LANG.UI_PUBLIC_FAILED + '</span>';
                            case CONF.TASK_STATUS.DELETING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_NODE_STATUS_DELETING + '</span>';
                            case CONF.TASK_STATUS.CLEANING:
                                return '<span class="label label-sm label-info label-info_en">' + LANG.UI_NODE_STATUS_CLEANING + '</span>';
                            default:
                                // return '<span class="label label-sm label-info  ">准备中</span>';
                                break;
                        }
                    }
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
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
            $('#current_verify_table').baseTableConfig().init(options);
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
            $('#current_verify_table').baseTableConfig().init(options);
        }
    }

    var hrefFormatter = function (value, row, index, field) {
        var url = getDetailsUrl(row.module_type_value, row.job_type_value, row.vm_type, row.sub_module_type_value, row.dev_type);

        if ([17, 18, 19, 20, 26, 27, 30, 31, 38, 39, 40, 41, 44, 45].includes(row.job_type_value)) {
            var nameStr = '<a href=./content/copy/copy_job_details.php?type=' + row.job_type_value + '&module=' + row.module_type_value + '&subType=' + row.sub_module_type_value + '&uuid=' + row.job_uuid +
                '" class="ajaxify" name="verification_job" title = "' + value + '">' + value + '</a>';
        } else {
            if (row.module_type_value == CONF.MODULE_TYPE.FS) {
                var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                    '" class="ajaxify" name="verification_job" title = "' + value + '">' + value + '</a>';
            } else if (row.module_type_value == CONF.MODULE_TYPE.VM && row.job_type == LANG.UI_INSTANT_NAME) {
                var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                    '" class="ajaxify" name="verification_job" title = "' + value + '">' + value + '</a>';
            } else if (row.module_type_value == CONF.MODULE_TYPE.COPY) {
                var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                    '" class="ajaxify" name="verification_job" title = "' + value + '">' + value + '</a>';
            } else if (row.module_type_value == CONF.MODULE_TYPE.M365) {
                var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                    '" class="ajaxify" name="verification_job" title = "' + value + '">' + value + '</a>';
            } else if (row.module_type_value == CONF.MODULE_TYPE.OS) {
                var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                    '&sub_module_type=' + row.sub_module_type_value +
                    '" class="ajaxify" name="verification_job" title = "' + value + '">' + value + '</a>';
            } else {
                var nameStr = '<a href="' + url + '?type=' + row.job_type_value + '&uuid=' + row.job_uuid +
                    '" class="ajaxify" name="verification_job" title = "' + value + '">' + value + '</a>';
            }
        }
        return nameStr;
    }

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
                    url = './content/fs/fs_job_details.php';
                    break;
                case 2: // NAS
                    url = './content/nas/nas_job_details.php';
                    break;
                case 3: // HADOOP
                    url = './content/hadoop/hadoop_job_details.php';
                    break;
                case 4: // 对象存储
                    url = './content/s3/obsjobdetail.php';
                    break;
                case 5: // 文件复制
                    url = './content/filecopy/file_copy_job_details.php';
                    break;
                default:
                    break;
            }
        } else if (11 == module) {
            //nas
            url = './content/nas/nas_job_details.php';
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
            if (devType == 1) { //旧版
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


        }

        //数据验证
        if (37 == taskType) {
            url = "./content/platform/dataverification/verification_job_details.php";
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
            sessionStorage.setItem('current_table_detailPage', 'true');
            $(window).unbind('resize'); //解绑表格插件中绑定的全局事件resize避免在没有表格的地方触发
        },
    };

    //更新表格数据
    var update = function () {
        getParams(true);
        $('#current_verify_table').bootstrapTable('refresh', {
            query: queryParams
        });
        // $('#current_verify_table').bootstrapTable('refresh');
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 371;

        $("#currentjobdiv .fixed-table-body").css({
            "height": height
        });
    }

    var initTimer = function () {
        if (interval != null) { //判断计时器是否为空
            clearTimeout(interval);
            // interval = null;
        }
        interval = setTimeout(update, 5000);
    }

    const addListeners = function () {

        $('.page-link').on('click', function () {
            $('#current_verify_table').bootstrapTable('showLoading');
        });

        // 改变表格高度
        $('#vin_current_verify_toolbar .change_height').on('click', change_height);

        // 批量启动策略
        $(".batchOperation").on('click', '.batch_start_active', function() {
            let userUuids = $.map($('#current_verify_table').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});

            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: 'current_job' }, () => {
                batchStart();
            });
        });

        // 批量停止
        $(".batchOperation").on('click', '.batch_stop_active', function() {
            let userUuids = $.map($('#current_verify_table').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});

            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: 'current_job' }, () => {
                batchStop();
            });
        });

        // 批量删除
        $(".batchOperation").on('click', '.batch_delete_active', function() {
            let userUuids = $.map($('#current_table').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});

            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: userUuids.join(','), auth: 'current_job' }, () => {
                batchDelete();
            });
        });

        //模块类型选择
        $('#currentJobModal #current_moduletype').on('change', moduleHandler);

        //高级搜索节点存储绑定
        $('#currentJobModal .nodeDiv').on('change', nodeChange);

        // <------------------ BEGIN TABLE TOOLBAR ------------------->

        $("#verify_new_task").on("click", function (e) {
            LOCATION('./content/platform/dataverification/add_verification_job.php', )
        });

        // 回车搜索事件
        $('#current_job_seach_ipt').keypress(function (e) {
            if (e.which == 13) {
                getParams();
                $('#current_verify_table').bootstrapTable('refresh', {
                    query: queryParams
                });
            }
        });

        // 搜索当前任务
        $('#current_job_search_btn').off().on('click', () => {
            searchVal = $('#current_job_seach_ipt').val();

            if (searchVal) {
                $('#current_verify_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS }})
            }
        });

        $('#current_job_seach_ipt').on('focus', () => {
            $('#current_job_clear_search').removeClass('hide');
        });

        // 清空当前任务搜索
        $('#current_job_clear_search').on('click', () => {
            $('#current_job_seach_ipt').val('');
            searchVal = '';
            $('#current_job_clear_search').addClass('hide');
            $('#current_verify_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } } );
        });

        // 监听当前任务表格 - 过滤器组件派发的数据，以更新表格
        window.$on('current_job_filter_btn-updateFilterEvent', (filterData) => {
            handleUpdateFilterParams(filterData);
        });

         // 监听当前任务表格 - 日期范围选择组件派发的数据，以更新表格
         window.$on('current_job_datepicker-updateDateRangeEvent', (data) => {
            // 记录选择的开始时间和结束时间，用于过滤搜索的联动
            startTime = data.startTime;
            endTime = data.endTime;

            $('#current_verify_table').bootstrapTable('refresh', { query: { start_time: startTime, end_time: endTime } });
        });

        // <------------------ END TABLE TOOLBAR ------------------->
    };

     /**
     * 初始化当前任务表格过滤器
     * @param {*}  
     */
     const initCurrentJobTableFilter = () => {
        $('#current_job_filter_wrapper').initFilter({
            filterSlotId: 'current_job_filter_wrapper',
            filterBtnId: 'current_job_filter_btn',
            filters: CURRENT_JOB_TABLE_FILTER_OPTIONS
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

        $('#current_verify_table').bootstrapTable('refresh', { query: { ...FILTER_PARAMS } });
    }

    /**
     * 初始化表格时间日期选择器
     */
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
            initCurrentJobTableFilter(); // 初始化当前任务表格过滤器
            initCurrentJobTableDaterangePicker(); // 初始化当前任务表格日期范围选择器
            tableInit();
            initTableHeight();
            initVMType(); //初始化虚拟化类型
            initStorage();
            initSoftwareVersionDiff();
            initNodeSelect(); //初始化所有备份节点
            addListeners(); //初始化监听事件
        }
    };
}();

jQuery(document).ready(function () {
    CurrentJob.init();
});