var LunStorageManager = function () {
    var initFlag = false;
    var snap_shot_flag = true; //快照开关
    var sync_storage_flag = true; //同步存储开关
    var chap_flag = false; //chap认证开关
    var discovery_flag = false; //discovery认证开关
    var check_flag = false; //是否检查启动器
    var storageUuid;
    var editRow; //修改的哪条数据需要记录下以供修改时发送检查启动器消息使用
    var oldPass;
    var passTips = "";
    var tips = LANG.UI_STORAGE_LUN_CHAP_PWD_TIPS;
    var ipDom = $('#ip').parent().html();
    var storageName = $('#storage_name').parent().html();
    var nameDom = $('#name').parent().html();
    var pwdDom = $('#password').parent().html();
    var chapNameDom = $('#chap_name').parent().html();
    var chapPwdDom = $('#chap_password').parent().html();

    function initListeners() {
        $('#vin_lun_storage_toolbar').on('click', '.search-btn', ()=>{
            $('#lun_storage_table').bootstrapTable('refresh');
        })
        //只有iscsi有chapauth
        $('#protocol_type').on('change', function () {
            let protocol = $('#protocol_type').find('option:selected').val();
            $('#check_error').text('');
            $('.check-div').show();
            $('#storage_snap').parent().parent().parent().parent().parent().show();
            if (protocol != 1) {
                $('.chap-div').hide();
                clearChapValidate(); //FC没有chap认证
            } else {
                addChapValidate();
            }

            if (protocol == 3) {
                // VBS
                $('.check-div').hide();
                $('#storage_snap').parent().parent().parent().parent().parent().hide();
            }

            if (!chap_flag) {
                $('.chap-input-div').hide();
            }
        })
        //节点发送检查启动器消息，切换节点需要重新验证启动器
        /*$('#node_select').on('change', function () {
            check_flag = false;
        })*/
        $('#node_select').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            // 获取当前选中的值数组（多选时是数组）
            // var selectedValues = $(this).selectpicker('val');
            // console.log('当前选中:', selectedValues);
            // console.log('之前选中:', previousValue);
            check_flag = false;
            // 你可以在这里执行其他逻辑，比如提交表单、更新UI等
        });
        $('#storage_type').on('change', function () {
            if ($('#storage_type').val() == 14) {
                //HUAWEI OceanStor 协议只有ISCSI 和 FC
                $('#protocol_type').val('1');
                $('#protocol_type').trigger('change');
                $('#protocol_type option[value="2"]').show();
                $('#protocol_type option[value="3"]').hide();
            } else if ($('#storage_type').val() == 15) {
                //HUAWEI Fusion Storage 协议只有ISCSI 和 VBS
                $('#protocol_type option[value="3"]').show();
                $('#protocol_type option[value="2"]').hide();
            }
        })
        //chap开关操作
        $('#chap_auth').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                chap_flag = true;
                addChapValidate();
            } else {
                chap_flag = false;
                clearChapValidate();
            }
        })

        //discovery开关操作
        $('#discovery_auth').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                discovery_flag = true;
                addChapValidate();
            } else {
                discovery_flag = false;
                clearChapValidate(true);
            }
        })

        // 默认开关开启
        $('#storage_snap').bootstrapSwitch('state', false);

        $('#storage_snap').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                snap_shot_flag = true;
                $('#storage_snap_div').show();
                // $('.protocol-div').show();
                // $('.check-div').show();
                addChapValidate(true);
            } else {
                snap_shot_flag = false;
                $('#storage_snap_div').hide();
                // $('.protocol-div').hide();
                // $('.check-div').hide();
                clearChapValidate();
            }
        })

        // 默认开关开启
        $('#sync_freq').bootstrapSwitch('state', true);

        $('#sync_freq').on('switchChange.bootstrapSwitch', function () {
            if (this.checked) {
                sync_storage_flag = 1;
                $('#sync_div').show();
            } else {
                sync_storage_flag = 0;
                $('#sync_div').hide();
            }
        })

        $('#add_lun').on('click', function () {
            check_flag = false;
            $('#storagetype').hide();
            $('#storage_type').show();
            $('#add_storage_drawer .submit').prop('data-action', 'add');
            $('#drawer-1-title').html('<i class="viconfont vicon-danchuangtianjia1"></i> ' + LANG.UI_PUBLIC_ADD + '');
            $('#ip_div').show();
            $('#user_div').show();
            $('#pwd_div').show();
            resetForm();
            addValidate();
            $('#chap_name').prop('disabled', false);
            $('#chap_auth').bootstrapSwitch('state', false);
            $('#chap_auth').trigger('switchChange.bootstrapSwitch');
            //ip输入完成默认填充存储别名
            $('#ip').off().on('blur', function () {
                var ip = $('#ip').val();
                $('#storage_name').val(ip);
            })
            $('#storage_type').prop('disabled', false);
        })

        $('#add_storage_drawer .cancel').on('click', function () {
            $('#add_storage_drawer').drawer('hide');
        })

        initSpinner();

        $('#delsubmit').on('click', delLun);
        $('#delete_lun').on('click', ()=>{delLunCheck('')});
    }

    var checkInitiatorEvent = function () {
        checkInitiator();
    }

    //检查生产存储启动器启动器
    var checkInitiator = function () {
        var action = $('#add_storage_drawer .submit').prop('data-action');
        var params = {};
        params.storage_type = $('#storage_type').find('option:selected').val();
        params.nickname = $('#storage_name').val();
        params.ip = $('#ip').val();
        params.username = $('#name').val();
        params.password = $('#password').val();
        params.storage_net_protocol = $('#protocol_type').find('option:selected').val();
        params.storage_snapshot_flag = 2;
        params.snapshot_request_frequency = 0;
        params.cbr_refresh_flag = 2;
        params.cbr_refresh_interval = 0;
        params.chap_auth_flag = 2;
        params.chap_username = '';
        params.chap_password = '';
        params.discover_chap_username = '';
        params.discover_chap_password = '';
        // params.node_uuid =  $('#node_select').find('option:selected').val();
        params.node_uuid = $('#node_select').selectpicker('val');
        if (params.storage_net_protocol == 0 && snap_shot_flag) {
            return UIToastr.showWarning(LANG.UI_STORAGE_LUN_SEND_CHECK_INITIATOR_MSG, LANG.UI_STORAGE_LUN_PLEASE_SELECT_PROTOCOL_TYPE);
        }
        if (params.node_uuid.length <= 0 && snap_shot_flag) {
            return UIToastr.showWarning(LANG.UI_STORAGE_LUN_SEND_CHECK_INITIATOR_MSG, LANG.UI_STORAGE_LUN_PLEASE_SELECT_NODE);
        }
        if (snap_shot_flag) {
            params.storage_snapshot_flag = 1;
            params.snapshot_request_frequency = $('#snapNumInput').val();
        }

        if (sync_storage_flag) {
            params.cbr_refresh_flag = 1;
            params.cbr_refresh_interval = $('#syncNumInput').val() * 60;
        }

        if (chap_flag) {
            params.chap_auth_flag = 1;
            params.chap_username = $('#chap_name').val();
            params.chap_password = $('#chap_password').val();
        }

        if (discovery_flag) {
            params.discover_chap_username = $('#chap_name').val();
            params.discover_chap_password = $('#chap_password').val();
        }

        if (action == 'edit') {
            params.op_type = 1; //通过这个参数让php处理加密了的密码,分辨是修改还是添加
            params.ip = editRow.config.ip;
            params.username = editRow.config.username;
            params.password = editRow.config.password;
        }
        Metronic.blockUI({
            target: '#add_storage_drawer',
            animate: true
        });
        pAjaxRequest(params, '/api/v1/storages/check_initiator', 'POST', function (res) {
            Metronic.unblockUI('#add_storage_drawer');
            let op = LANG.UI_STORAGE_LUN_SEND_CHECK_INITIATOR_MSG;
            if (operateResponseList(res, op)) {
                $('#lun_storage_table').bootstrapTable('refresh');

                if (res.success) {
                    check_flag = true;
                    $('#check_error').text('');
                    var protocol = $('#protocol_type').find('option:selected').val();
                    var action = $('#add_storage_drawer .submit').prop('data-action');
                    if (protocol == 1) {
                        $('.chap-div').show();
                    }
                    if (action == 'edit' && protocol == 1) { //只有iSCSI才有chap认证
                        if (editRow.config.chap_username != '') {
                            $('#chap_auth').bootstrapSwitch('state', true);
                            $('#chap_auth').trigger('switchChange.bootstrapSwitch');
                        }
                    } else {
                        $('#chap_auth').bootstrapSwitch('state', false);
                        $('#chap_auth').trigger('switchChange.bootstrapSwitch');
                    }
                }
            } else {
                var content = "";
                for (var k in res.data) {
                    content += res.data[k] + '</br>';
                }

                $('#check_error').html(content);
            }
        })
    }

    //清除chap相关验证，关闭chap认证的时候不认证
    var clearChapValidate = function (keepopen) {
        //如果keepopen为true不隐藏chap用户名和密码输入
        if (!keepopen) {
            $('.chap-input-div').hide();
        }
        $('#chap_name').rules("remove", 'required');
        $('#chap_name').rules("remove", 'minlength');
        $('#chap_name').rules("remove", 'maxlength');
        $('#chap_name').rules("remove", 'noChinese');
        $('#chap_name').rules("remove", 'chapNameValidate');

        $('#chap_password').rules("remove", 'required');
        $('#chap_password').rules("remove", 'passcomplexity');
        $('#chap_password').rules("remove", 'notEqualToUsernameOrReverse');
        $('#chap_password').rules("remove", 'noChinese');
        $('#chap_password').rules("remove", 'chapPwdValidate');
    }

    //添加chap相关验证，关闭chap认证的时候不认证
    var addChapValidate = function (keep) {
        if (!keep) {
            $('.chap-input-div').show();
        }
        $('#chap_name').rules("add", {
            required: true
        });
        $('#chap_name').rules("add", {
            minlength: 4
        });
        $('#chap_name').rules("add", {
            maxlength: 223
        });
        $('#chap_name').rules("add", {
            noChinese: true
        });
        $('#chap_name').rules("add", {
            chapNameValidate: true
        });
        $('#chap_password').rules("add", {
            required: true
        });
        $('#chap_password').rules("add", {
            passcomplexity: true
        });
        $('#chap_password').rules("add", {
            notEqualToUsernameOrReverse: true
        });
        $('#chap_password').rules("add", {
            noChinese: true
        });
        $('#chap_password').rules("add", {
            chapPwdValidate: true
        });
    }

    //清除添加时需要验证但是修改时不需要填的表单验证
    var clearAddValidate = function () {
        $('#ip').rules("remove", 'required');
        $('#ip').rules("remove", 'ipv4Ordomain');
        $('#name').rules("remove", 'required');
        $('#password').rules("remove", 'required');
        $('#password').rules("remove", 'minlength');
    }

    //增加添加时需要验证但是修改时不需要填的表单验证
    var addValidate = function () {
        $('#ip').rules("add", {
            required: true
        });
        $('#ip').rules("add", {
            ipv4Ordomain: true
        });
        $('#name').rules("add", {
            required: true
        });
        $('#password').rules("add", {
            required: true
        });
        $('#password').rules("add", {
            minlength: CONF.PASS_LENGTH
        });
    }

    var tipDelete = function () {
        UIToastr.showInfo(LANG.UI_STORAGE_DELETE, LANG.UI_STORAGE_DELETE_TIPS1);
    }

    //清空重置表单
    var resetForm = function () {
        $('.chap-div').hide();
        $('.form-group').removeClass('has-success');
        $('.form-group').removeClass('has-error');

        $('#ip').parent().empty().html(ipDom);
        $('#storage_name').parent().empty().html(storageName);
        $('#name').parent().empty().html(nameDom);
        $('#password').parent().empty().html(pwdDom);
        $('#protocol_type').val('0');
        // $('#node_select').val('0');
        // 方法1：清空选中项（但保留选项）
        $('#node_select').selectpicker('deselectAll');
        $('#node_select').selectpicker('refresh');
        // 触发 onchange 事件
        $('#protocol_type').trigger('change');
        $('#storage_snap').bootstrapSwitch('state', true);
        $('#storage_snap').trigger('switchChange.bootstrapSwitch');
        $('#check_error').text('');
        $('#sync_freq').bootstrapSwitch('state', true);
        $('#chap_name').parent().empty().html(chapNameDom);
        $('#chap_password').parent().empty().html(chapPwdDom);

        $('#snapNumInput').val(20);
        $('#syncNumInput').val(30);
    }

    //删除存储检查
    function delLunCheck(uuid = '') {
        var uuids = [];
        var uuids2 = [];
        var select = $('#lun_storage_table').bootstrapTable('getSelections');
        if (uuid == '') {
            if(!select.length){
                return tipDeleteStorage();
            }
            for (let i = 0; i < select.length; i++) {
                if (select[i].storage_type == 10) {
                    return UIToastr.showError(LANG.UI_STORAGE_DELETE, LANG.UI_STORAGE_DELETE_TAPE_TIP);
                }
                uuids.push(select[i].storage_uuid);
                uuids2.push(select[i].user_uuid);
            }
        } else {
            uuids = uuid;
        }
        if (uuid == '') {
            checkOperateAuth(
                {
                    type: 1,
                    user_uuid: uuids2.join(','),
                    auth: 'resmanagement'
                },
                function (){
                    batchDel(uuids)
                }
            )
        } else {
            batchDel(uuids);
        }
    }

    var batchDel = function (uuids){
        bootbox.confirm({
            title: LANG.UI_STORAGE_DELETE,
            message: LANG.UI_STORAGE_DELETE_TIPS2,
            callback: debounce(function(r) {
                if(!r) return;
                Metronic.blockUI({target: '#lun_storage_table',animate: true});
                var data = {};
                data.uuids = uuids;
                pAjaxRequest(data, "/api/v1/storages", "DELETE", function (result) {
                    Metronic.unblockUI('#lun_storage_table');
                    if(result.data.hasOwnProperty('info') && result.data.info != ''){
                        //如果有备份点信息或任务依赖
                        initModal(result.data.info);
                        return;
                    }
                    if (result.code == 0) {
                        // expandIndex = null
                        $('#lun_storage_table').bootstrapTable('uncheckAll');
                        $('#lun_storage_table').bootstrapTable('refresh');
                        UIToastr.showSuccess(LANG.UI_STORAGE_DELETE, result.message);
                    } else {
                        UIToastr.showError(LANG.UI_STORAGE_DELETE, result.message);
                    }
                });
            }, 300)
        });
    }

    //初始化MODAL
	var initModal = function(data){
		$('#lunmodaldiv #timepointcount').html(data.timepoint_count);
		$('#lunmodaldiv #timepointsize').html(data.timepoint_size);
		$('#lunmodaldiv #taskcount').html(data.task_count);
		var html = '';

		for(var i=0; i<data.tasks.length; i++){
			html += data.tasks[i] + "<br>";
		}

		if(0 == data.tasks.length){
			html = '--';
		}
		$('#lunmodaldiv #taskname').html(html);
		$('#lunmodaldiv').modal({
			'width': '600px'
		});
	}

    //删除存储
    function delLun() {
        var selectRows = $('#lun_storage_table').bootstrapTable('getSelections');
        if (!selectRows.length) {
            return tipDelete();
        }
        bootbox.confirm({
            title: LANG.UI_STORAGE_DELETE,
            message: LANG.UI_STORAGE_DELETE_TIPS2,
            callback: debounce(function (r) {
                if (!r) return;
                var params = [];
                $.each(selectRows, function (index) {
                    params.push(selectRows[index].storage_uuid);
                })
                Metronic.blockUI({
                    target: '#lun_storage_table',
                    animate: true
                });
                pAjaxRequest({
                    "uuids": params,
                }, '/api/v1/storages', 'DELETE', function (data) {
                    Metronic.unblockUI('#lun_storage_table');
                    var op = LANG.UI_STORAGE_LUN_SEND_BATCH_DEL_STORAGE_MSG;
                    if (operateResponseList(data, op)) {
                        $("#lun_storage_table").bootstrapTable('refresh');
                        $("#lun_storage_table").bootstrapTable('hideLoading');
                    }
                });
            }, 300)
        });
    }

    //添加或修改存储
    function submit() {
        var action = $('#add_storage_drawer .submit').prop('data-action');

        if (action == 'add') {
            addSubmit();
        }
        if (action == 'edit') {
            editSubmit();
        }
    }

    //添加存储
    function addSubmit(params) {
        var params = {};
        params.storage_type = $('#storage_type').find('option:selected').val();
        params.nickname = $('#storage_name').val();
        params.ip = $('#ip').val();
        params.username = $('#name').val();
        params.password = $('#password').val();
        params.storage_net_protocol = $('#protocol_type').find('option:selected').val();
        // params.node_uuid =  $('#node_select').selectpicker('val');
        params.node_uuid =  $('#node_select').selectpicker('val');
        params.node_uuid_list =  params.node_uuid;
        if (params.node_uuid.length <= 0 && snap_shot_flag) {
            return UIToastr.showWarning(LANG.UI_STORAGE_LUN_ADD_FAILED, LANG.UI_STORAGE_LUN_PLEASE_SELECT_NODE);
        }
        if (snap_shot_flag) {
            params.storage_snapshot_flag = 1;
            params.snapshot_request_frequency = $('#snapNumInput').val();
        } else {
            params.storage_snapshot_flag = 2;
            params.snapshot_request_frequency = 0;
        }

        if (params.storage_net_protocol == 0) {
            return UIToastr.showWarning(LANG.UI_STORAGE_LUN_ADD_FAILED, LANG.UI_STORAGE_LUN_PLEASE_SELECT_PROTOCOL_TYPE);
        }
        if (sync_storage_flag) {
            params.cbr_refresh_flag = 1;
            params.cbr_refresh_interval = $('#syncNumInput').val() * 60;
        } else {
            params.cbr_refresh_flag = 2;
            params.cbr_refresh_interval = 0;
        }
        var syncTime = parseInt($('#syncNumInput').val());
		if (!syncTime || syncTime < 5) {
			UIToastr.showWarning(LANG.UI_STORAGE_LUN_ADD_FAILED, LANG.UI_STORAGE_LUN_ADD_FAILED_TIPS);
            $('#syncNumInput').val(30);
			return;
		}
        if (chap_flag) {
            params.chap_auth_flag = 1;
            params.chap_username = $('#chap_name').val();
            params.chap_password = $('#chap_password').val();
        } else {
            params.chap_auth_flag = 2;
            params.chap_username = '';
            params.chap_password = '';
        }

        if (discovery_flag) {
            params.discover_chap_username = $('#chap_name').val();
            params.discover_chap_password = $('#chap_password').val();
        } else {
            params.discover_chap_username = '';
            params.discover_chap_password = '';
        }

        Metronic.blockUI({
            target: '#lun_storage_list_div',
            animate: true
        });
        $('#add_storage_drawer .submit').prop('disabled', true);
        pAjaxRequest(params, '/api/v1/storages/lun/add', 'POST', function (res) {
            Metronic.unblockUI('#lun_storage_list_div');
            let op = LANG.UI_STORAGE_LUN_SEND_ADD_LUN_STORAGE_MSG;
            $('#add_storage_drawer .submit').prop('disabled', false);
            if (operateResponseList(res, op)) {
                $('#lun_storage_table').bootstrapTable('refresh');
                if (res.success) {
                    $('#add_storage_drawer').drawer('hide');
                    check_flag = false;
                }
            }
        })
    }
    //修改存储
    function editSubmit(params) {
        var params = {};
        params.storage_uuid = storageUuid;
        params.storage_type = $('#storage_type').find('option:selected').val();
        params.nickname = $('#storage_name').val();

        params.storage_net_protocol = $('#protocol_type').find('option:selected').val();
        params.node_uuid =  $('#node_select').selectpicker('val');
        params.node_uuid_list =  params.node_uuid;
        if (params.node_uuid <= 0 && snap_shot_flag) {
            return UIToastr.showWarning(LANG.UI_STORAGE_LUN_SEND_CHECK_INITIATOR_MSG, LANG.UI_STORAGE_LUN_PLEASE_SELECT_NODE);
        }
        if (snap_shot_flag) {
            params.storage_snapshot_flag = 1;
            params.snapshot_request_frequency = $('#snapNumInput').val();
        } else {
            params.storage_snapshot_flag = 2;
            params.snapshot_request_frequency = 0;
        }
        if (params.storage_net_protocol == 0) {
            return UIToastr.showWarning(LANG.UI_STORAGE_LUN_SEND_CHECK_INITIATOR_MSG, LANG.UI_STORAGE_LUN_PLEASE_SELECT_PROTOCOL_TYPE);
        }
        if (sync_storage_flag) {
            params.cbr_refresh_flag = 1;
            params.cbr_refresh_interval = $('#syncNumInput').val() * 60;
        } else {
            params.cbr_refresh_flag = 2;
            params.cbr_refresh_interval = 0;
        }
        var syncTime = parseInt($('#syncNumInput').val());
		if (!syncTime || syncTime < 5) {
			UIToastr.showWarning(LANG.UI_STORAGE_LUN_ADD_FAILED, LANG.UI_STORAGE_LUN_ADD_FAILED_TIPS);
            $('#syncNumInput').val(editRow.config.cbr_refresh_interval/60);
			return;
		}
        if (chap_flag) {
            params.chap_auth_flag = 1;
            params.chap_username = $('#chap_name').val();
            params.chap_password = $('#chap_password').val();
        } else {
            params.chap_auth_flag = 2;
            params.chap_username = '';
            params.chap_password = '';
        }

        if (discovery_flag) {
            params.discover_chap_username = $('#chap_name').val();
            params.discover_chap_password = $('#chap_password').val();
        } else {
            params.discover_chap_username = '';
            params.discover_chap_password = '';
        }

        Metronic.blockUI({
            target: '#lun_storage_list_div',
            animate: true
        });
        $('#add_storage_drawer .submit').prop('disabled', true);
        pAjaxRequest(params, '/api/v1/storages/lun', 'PUT', function (res) {
            var op = LANG.UI_STORAGE_LUN_SEND_MODIFY_LUN_STORAGE_MSG;
            if (operateResponseList(res, op)) {
                Metronic.unblockUI('#lun_storage_list_div');
                $('#lun_storage_table').bootstrapTable('refresh');
                $('#add_storage_drawer .submit').prop('disabled', false);
                if (res.success) {
                    $('#add_storage_drawer').drawer('hide');
                    check_flag = false;
                }
            }
        })
    }


    //初始化spinner
    var initSpinner = function () {
        //post 请求数据库备份存储总大小
        //初始化存储单位
        $('#syncTimeNum').spinner({
            value: 30,
            step: 5,
            min: 5,
            max: 9999
        });

        $('#snapNum').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 9999
        });
    }

    var checkEvent = function (tableId, btnId) {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $('' + btnId + ' i').addClass('icon-gray-delete');
            $('' + btnId + ' i').removeClass('icon-white-delete');
            $('' + btnId + '').css('background-color', '#F4F4F5');
        } else {
            $('' + btnId + ' i').removeClass('icon-gray-delete');
            $('' + btnId + ' i').addClass('icon-white-delete');
            $('' + btnId + '').css('background-color', '#0FBF98');
        }
    }

    function initDataTable() {
        var afterInput = ``;
        if ($.inArray('p_production_storage_manager_add', CONF.PERMISSION_ARR) !== -1) {
            afterInput = `<button class="btn dropdown-toggle btn-font flex_center btn-title p-lr8 table-toolbar-btn" id="add_lun" data-toggle="drawer" data-target="#add_storage_drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
        }
        var beforeInput = ``;
        if ($.inArray('p_production_storage_manager_delete', CONF.PERMISSION_ARR) !== -1) {
            beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_lun"></button></div>`;
        }
        var options = {
            toolbarId: '#vin_lun_storage_toolbar',
            buttonsToolbar: '#vin_lun_storage_toolbar .vin_btnToolbar',
            placeholder: LANG.UI_SEARCH_AS_STORAGE_NAME,
            vin_url: '/api/v1/storages/lun',
            vin_method: 'GET',
            vin_params: function () {
                let params = {};
                let search = $('#vin_lun_storage_toolbar .lunSearch').val();
                if (search) {
                    params.search = search;
                }
                params.source_type = 2;
                return params;
            },
            customTool: {
                beforeInput: beforeInput,
                afterInput: afterInput,
            },
            searchInput: true, //搜索框
            searchClass: 'lunSearch', //自定义的搜索框类名
            searchSelector: '.lunSearch', //选择使用自定义搜索框
            onCheck: function () {
                modifyDelStyle('lun_storage_table', 'delete_lun');
            },
            onUncheck: function () {
                modifyDelStyle('lun_storage_table', 'delete_lun');
            },
            onCheckAll: function () {
                modifyDelStyle('lun_storage_table', 'delete_lun');
            },
            onUncheckAll: function () {
                modifyDelStyle('lun_storage_table', 'delete_lun');
            },
            // showExport: false, //是否开启导出按钮
            // showColumns: false, //是否开启列选择按钮
            // onResetView: initTableHeight,
            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false
                },
                {
                    field: 'num', //字段名
                    title: LANG.UI_PUBLIC_TABLE_ID,
                },
                {
                    field: 'storage_nickname',
                    title: LANG.UI_SEARCH_NICKNAME,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'ip',
                    title: LANG.UI_CLIENT_IP_ADDRESS,
                },
                {
                    field: 'storage_type',
                    title: LANG.UI_SEARCH_STORAGE_TYPE,
                    formatter: storageFormatter
                },
                {
                    field: 'version',
                    title: LANG.UI_VCENTER_VM_VERSION,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'login_user',
                    title: LANG.UI_LUN_STORAGE_LOGIN_USER,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'sync_time',
                    title: LANG.UI_LUN_STORAGE_SYNC_TIME,
                },
                // {
                //     field: 'create_user',
                //     title: LANG.UI_LUN_STORAGE_CREATE_USER,
                // },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (index, row) {
                        if (row.status == 1) {
                            return '<span class="label label-sm label-success"> ' + LANG.UI_STORAGE_LUN_NORMAL + ' </span>'
                        }
                        if (row.status == 2) {
                            return '<span class="label label-sm label-danger"> ' + LANG.UI_STORAGE_LUN_ABNORMAL + ' </span>'
                        }
                    }
                },
                {
                    // field: 'login_user',
                    title: LANG.UI_PUBLIC_OPERATION,
                    formatter: operationFormatter,
                    events: storageOp,
                    opButton: true,
                    clickToSelect: false, //不可通过点击行选中
                    sortable: false, //默认可排序，禁用排序才写此项
                },
            ]
        }
        init(options);
    }
    //行内操作
    var storageOp = {
        'click .view': function (event, value, row, index) {
            let url = './content/platform/storage/lun_storage_detail.php?uuid=' + row.storage_uuid+'&type=' + row.storage_type;
            // let url = './content/platform/storage/lun_storage_detail.php';
            LOCATION(url,'infrastructure');
        },
        'click .sync': function (event, value, row, index) {
            let uuidList = [];
            let storage_uuid = {};
            storage_uuid.storage_uuid = row.storage_uuid;
            uuidList.push(storage_uuid);
            checkOperateAuth(
                {
                    type: 1,
                    user_uuid: row.user_uuid,
                    auth: 'resmanagement'
                },
                function (){
                    Metronic.blockUI({
                        target: '#lun_storage_table',
                        animate: true
                    });
                    pAjaxRequest({
                        'storage_uuid_list': uuidList
                    }, '/api/v1/storages/sync', 'POST', function (res) {
                        let op = LANG.UI_STORAGE_LUN_SEND_SYNC_LUN_STORAGE_MSG;
                        Metronic.unblockUI('#lun_storage_table');
                        if (operateResponseList(res, op)) {
                            $('#lun_storage_table').bootstrapTable('refresh');
                        }
                    })
                }
            )
        },
        'click .edit': function (event, value, row, index) {
            storageUuid = row.storage_uuid;
            checkOperateAuth(
                {
                    type: 1,
                    user_uuid: row.user_uuid,
                    auth: 'resmanagement'
                },
                function (){
                    editRow = row;
                    $('#add_storage_drawer').drawer('show');
                    $('#drawer-1-title').html('<i class="viconfont vicon-danchuangtianjia1"></i> ' + LANG.UI_JOB_MODIFY + '');
                    resetForm();
                    clearAddValidate();
                    $('#add_storage_drawer .submit').prop('data-action', 'edit');
                    $('#storage_type').val(row.storage_type).trigger('change');
                    $('#storage_name').val(row.storage_nickname);
                    $('#protocol_type').val(row.config.storage_net_protocol);
                    $('#protocol_type').trigger('change');
                    // $('#node_select').val(row.node_uuid);
                    if (editRow.config.node_uuid_list != undefined) {
                        $('#node_select').selectpicker('val', editRow.config.node_uuid_list);
                    } else {
                        $('#node_select').selectpicker('val', row.node_uuid);
                    }
                    oldPass = row.config.chap_password;
                    if (row.config.chap_username != '' && row.config.chap_password != '') {
                        $('#chap_auth').bootstrapSwitch('state', true);
                        $('#chap_auth').trigger('switchChange.bootstrapSwitch');
                        $('#chap_name').val(row.config.chap_username);
                        $('#chap_password').val(oldPass);
                    } else {
                        $('#chap_auth').bootstrapSwitch('state', false);
                        $('#chap_auth').trigger('switchChange.bootstrapSwitch');
                    }
                    if (row.config.storage_snapshot_flag == 1) {
                        $('#storage_snap').bootstrapSwitch('state', true);
                        $('#snapNumInput').val(row.config.snapshot_request_frequency);
                    } else {
                        $('#storage_snap').bootstrapSwitch('state', false);
                    }
                    if (row.config.cbr_refresh_flag == 1) {
                        $('#sync_freq').bootstrapSwitch('state', true);
                        $('#syncNumInput').val(row.config.cbr_refresh_interval/60);
                    } else {
                        $('#sync_freq').bootstrapSwitch('state', false);
                    }

                    if (row.config.discover_chap_username != '') {
                        $('#discovery_auth').bootstrapSwitch('state', true);
                    } else {
                        $('#discovery_auth').bootstrapSwitch('state', false);
                    }

                    $('#ip_div').hide();
                    $('#user_div').hide();
                    $('#pwd_div').hide();
                    $('#storage_type').prop('disabled', true);
                }
            )
        },
        'click .delete': function (event, value, row, index) {
            checkOperateAuth(
                {
                    type: 1,
                    user_uuid: row.user_uuid,
                    auth: 'resmanagement'
                },
                function (){
                    delLunCheck([row.storage_uuid])
                }
            )

        }
    }

    function operationFormatter(value, row, index, field) {
        var button = '<div class="btn-group">';
        if (index > 5) {
            button = '<div class="btn-group dropup">';
        }

        button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
            'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
            '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
            '</button>' +
            '<ul class="dropdown-menu min-width100" role="menu">';

        button += '<li class="view"><a href="javascript:;"><i class="viconfont vicon-a-Eyesyanjing"></i> ' + LANG.UI_PUBLIC_LOOK + ' </a></li>';
        if ($.inArray('p_production_storage_manager_sync', CONF.PERMISSION_ARR) !== -1) {
            button += '<li class="sync"><a href="javascript:;"><i class="viconfont vicon-tongbu"></i> ' + LANG.UI_VCENTER_SYNC + ' </a></li>';
        }
        if ($.inArray('p_production_storage_manager_edit', CONF.PERMISSION_ARR) !== -1) {
            button += '<li class="edit"><a href="javascript:;"><i class="viconfont vicon-edit-new"></i> ' + LANG.UI_JOB_MODIFY + ' </a></li>';
        }
        if ($.inArray('p_production_storage_manager_delete', CONF.PERMISSION_ARR) !== -1) {
            button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-a-Deleteshanchu"></i> ' + LANG.UI_PUBLIC_DELETE + ' </a></li>';
        }
        button += '</ul></div>';
        return button;
    }

    function storageFormatter(index, row) {
        // console.log(row);
        if (row.storage_type == 14) {
            return '<span>HUAWEI OceanStor</span>'
        } else if (row.storage_type == 15) {
            return '<span>HUAWEI Fusion Storage</span>'
        } else if (row.storage_type == 17) {
            return '<span>Inspur HF18000G6</span>'
        }

    }

    function init(options) {
        if (!initFlag) {
            $('#lun_storage_table').bootstrapTable('destroy');
            $('#lun_storage_table').baseTableConfig().init(options);
        } else {
            $('#lun_storage_table').bootstrapTable('refresh');
        }
    }

    function noChangeSnapShot() {
        var noChangeFlag = false; //快照配置是否有改动， true有，false没有
        //获取现在的配置
        var snapshot = 2;
        if ($('#storage_snap').get(0).checked) {
            snapshot = 1;
        }
        var protocol = $('#protocol_type').val();
        var snapShotFreq = $('#snapNumInput').val();

        var chapFlag = 2;
        if ($('#chap_auth').get(0).checked) {
            chapFlag = 1;
        }

        var discoveryFlag = 2;
        if ($('#discovery_auth').get(0).checked) {
            discoveryFlag = 1;
        }
        var chapName = $('#chap_name').val();
        var chapPassword = $('#chap_password').val();

        //比对旧的配置
        if (editRow.config.storage_snapshot_flag != snapshot) {
            noChangeFlag = true;
        }

        if (editRow.config.storage_net_protocol != protocol) {
            noChangeFlag = true;
        }

        if (editRow.config.snapshot_request_frequency != snapShotFreq && snapshot == 1) {
            noChangeFlag = true;
        }

        if (editRow.config.chap_username != chapName && chapFlag == 1) {
            noChangeFlag = true;
        }

        if (editRow.config.chap_password != chapPassword && chapFlag == 1) {
            noChangeFlag = true;
        }

        if (!noChangeFlag) {
            check_flag = true; //如果没有修改配置，则不验证启动器
        }
    }

    //初始化所有备份节点
    var initNodeSelect = function () {
        pAjaxRequest({
            'offset': 0,
            'limit': 50
        }, '/api/v1/nodes', 'GET', function (d) {
            var data = d;
            var nodeSelect = $('#node_select');
            nodeSelect.empty();
            var option = '';
            for (var i = 0; i < data.data.rows.length; i++) {
                option = $("<option>").text(data.data.rows[i].ip).val(data.data.rows[i].node_uuid);
                nodeSelect.append(option);
            }

        });
    }

    var handleAddValidation = function () {
        var form2 = $('#form_sample_2');
        var error2 = $('alert-danger', form2);
        var success2 = $('.alert-success', form2);
        var action = $('#add_storage_drawer .submit').prop('data-action');
        var rules = {};
        rules = {
            ip: {
                required: true,
                ipv4Ordomain: true,
            },
            storage_name :{
                required: true,
            },
            name: {
                required: true,
            },
            password: {
                minlength: CONF.PASS_LENGTH,
                required: true,
            },
            chap_name: {
                // required: chap_flag,
                noChinese:true,
                chapNameValidate: true,
                minlength: 4,
                maxlength: 223
            },
            chap_password: {
                // required: chap_flag,
                noChinese:true,
                chapPwdValidate: true,
                passcomplexity: true,
                // chapPwdLength:true,
                notEqualToUsernameOrReverse: true
            }
        };
        if (action == 'edit') {
            rules = {
                chap_name: {
                    // required: chap_flag,
                    noChinese:true,
                    chapNameValidate: true,
                    minlength: 4,
                    maxlength: 223
                },
                chap_password: {
                    // required: chap_flag,
                    passcomplexity: true,
                    noChinese:true,
                    chapPwdValidate: true,
                    // chapPwdLength:true,
                    notEqualToUsernameOrReverse: true
                }
            }
        }

        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "", // validate all fields including form hidden input
            rules: rules,

            invalidHandler: function (event, validator) { //display error alert on form submit
                success2.hide();
                error2.show();
                Metronic.scrollTo(error2, -200);
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({
                    'container': 'body'
                });
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight

            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {
                success2.show();
                error2.hide();
            }
        });

        //添加存储
        $('#add_storage_drawer .submit').unbind('click').click(function () {
            var action = $(this).prop('data-action');
            if (action == 'edit') {
                noChangeSnapShot();//需求#18019：快照相关的配置没有修改就把check_flag设置为true
            }
            if (!check_flag && snap_shot_flag) {
                return UIToastr.showWarning(LANG.UI_STORAGE_LUN_ADD, LANG.UI_STORAGE_LUN_ADD_TIPS);
            }
            submit();
        });

        //检查启动器
        $('#check').on('click', function () {
            clearChapValidate();
            if (form2.validate().form()) {
                checkInitiatorEvent();
            }
        })

        $.validator.addMethod("ipv4Ordomain", function(value, element) {
            var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
            var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
            //http|https
            var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
            var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
            return domain || ipv4 || domainHTTP || ipv4HTTP;
        }, LANG.UI_TOOLS_IP_OR_DOMAIN);

        $.validator.addMethod("chapNameValidate", function(value, element, params) {
            var regex = /^(?=[a-zA-Z0-9])[^\s'"\\/;?<>%]*$/;
            return this.optional(element) || regex.test(value);
        }, STRING_REGEX_CFG.tip);

        $.validator.addMethod("chapPwdValidate", function(value, element, params) {
            var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!"#$&%'()*+,-./:;<=>?@\[\\\]^{|}~ ]).{12,}$/;
            return this.optional(element) || regex.test(value);
        }, LANG.UI_STORAGE_LUN_CHAP_PWD_TIPS);

        $.validator.addMethod("noChinese", function(value, element, params) {
            var regex = /^[^\u4e00-\u9fa5]+$/;
            return this.optional(element) || regex.test(value);
        }, LANG.UI_VALIDATE_LIMIT_NO_CHINESE);

        $.validator.addMethod("passcomplexity", function (value, element) {
            if (oldPass == value) {
                return true;
            }
            var match = "";
            switch (CONF.PASS_COMPLEXITY) {
                case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
                    var pattern = "^[A-Za-z0-9!@#$%^&*,.]{" + CONF.PASS_LENGTH + ",}$";
                    match = this.optional(element) || new RegExp(pattern).test(value);
                    break;
                case 2: //中(必须包含字母(不区分大小写),数字,特称字符)
                    var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]{" + 12 + ",}$";
                    match = this.optional(element) || new RegExp(pattern).test(value);
                    break;
                case 3: //强(必须包含大小写字母,数字,特称字符)
                    var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\\.])[0-9a-zA-Z!@#$%^&*,\\\\.]{" + 12 + ",}$"
                    match = this.optional(element) || new RegExp(pattern).test(value);
                    break;
            }
            return match;
        }, tips);

        $.validator.addMethod("chapPwdLength", function (value, element) {
            if (oldPass == value) return true;
            if (value.length < 12 || value.length > 16) { //密码长度12-16字符
                return false;
            }
            return true;
        }, LANG.UI_STORAGE_LUN_CHAP_PWD_LENGTH);

        //不能和用户名以及其倒写相同
        $.validator.addMethod("notEqualToUsernameOrReverse", function (value, element, params) {
            if (!chap_flag) {
                return true;
            }
            var username = $("#chap_name").val();
            var reverseUsername = username.split("").reverse().join("");
            return value !== username && value !== reverseUsername;
        }, LANG.UI_STORAGE_LUN_CHECK_PASSWORD_TIPS);
    }

    //add storage 初始化
    var initPassComplexity = function () {
        switch (CONF.PASS_COMPLEXITY) {
            case 1: //弱(包含字母(不区分大小写),数字,特殊字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
            case 2: //中(必须包含字母(不区分大小写),数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                break;
            case 3: //强(必须包含大小写字母,数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                break;
            default:
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
        }
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 281;

        $("#lun_storage_list_div .fixed-table-body").css({
            "height": height
        });
    }

    return {
        init: function () {
            //初始化多选下拉框
            $(".selectpicker").selectpicker({
                noneSelectedText: LANG.BILLING_PLEASE_SELECT,
                deselectAllText: LANG.BILLING_DESELECT_ALL,
                selectAllText: LANG.BILLING_SELECT_ALL,
                liveSearchPlaceholder: LANG.BILLING_SEARCH,
                countSelectedText: function () {}
            });
            initDataTable();
            initListeners();
            initPassComplexity();
            handleAddValidation();
            initTableHeight();
            initNodeSelect();
        }
    };
}();

$(document).ready(function () {
    LunStorageManager.init();
});