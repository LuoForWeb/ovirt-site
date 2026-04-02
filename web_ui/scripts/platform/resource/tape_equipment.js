var TapeEquipment = function () {
    // var firstScanFlag = true;
    var tapeId;
    var nodeId;
    var modifyGroupUuid = "";
    var tapeGroupProperties = {};
    var addTapeSerialNumberSet = [];
    var removeTapeSerialNumberSet = [];
    var backupSetData = [];
    var groupNode;
    var toolBar = $('#vin_tape_toolbar').html();
    var initTapeTreeFlag = false;
    var _UserPassword = '';
    /**
     * @function 事件监听器
     */
    var addListeners = function () {
        $('#groupStrategyTips button.close').on('click', ()=>{
            $('#groupStrategyTips').hide();
            
            $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
        });

        $('#tapeTips button.close').on('click', () => {
            $('#tapeTips').hide();
            $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
        });

        $('#scan').on('click', () => {
            $('#scan').prop('disabled', true);
            $('#scan').html('<i class="viconfont vicon-a-Scanning-twosaomiao me-4"></i>'+LANG.UI_TAPE_SCAN + '');
            scanLib('');
        });

        $('#nodeselect').on('change', function (param) {
            nodeId = $(this).val();
            if (nodeId === '0') {
                nodeId = '';
            }
            zTree.destroy();
            setTapeTree();
            $("#tape_lib_table").bootstrapTable('refresh');
        })

        $('#generate_strategy').on('change', function () {
            $('#generate_strategy_days_div').hide();
            if (this.value === '3') {
                $('#generate_strategy_days_div').show();
            }
            if (this.value === '1') {
                $('#reserve_strategy').val('1');
                $('#reserve_strategy').prop('disabled', true);
                $('#reserve_strategy_days_div').hide();
            } else {
                $('#reserve_strategy').prop('disabled', false);
            }
        })

        $('#reserve_strategy').on('change', function () {
            $('#reserve_strategy_days_div').hide();
            if (this.value === '2') {
                $('#reserve_strategy_days_div').show();
            }
        })

        $('#edit_generate_strategy').on('change', function () {
            $('#edit_generate_days_div').hide();
            if (this.value === '3') {
                $('#edit_generate_days_div').show();
            }
            if (this.value === '1') {
                $('#edit_reserve_strategy').val('1');
                $('#edit_reserve_strategy').prop('disabled', true);
                $('#edit_reserve_days_div').hide();
            } else {
                $('#edit_reserve_strategy').prop('disabled', false);
            }
        })

        $('#edit_reserve_strategy').on('change', function () {
            $('#edit_reserve_days_div').hide();
            if (this.value === '2') {
                $('#edit_reserve_days_div').show();
                $('#edit_reserve_days_div').prop('disabled', false);
            }
        })

        initSpinner();

        //选择存储用途复选框
        $('#useMode').find('.icheck').on('ifClicked', useModeClick);
        $('#edituseMode').find('.icheck').on('ifClicked', editUseModeClick);

        //告警阈值
        $('#tape_group_modal #noticeswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
            if (data) {
                $('#tape_group_modal .warnningdiv').show(); //开
                noticeTypeChange('#tape_group_modal');
            } else {
                $('#tape_group_modal .warnningdiv').hide(); //关
            }
        });
        $('#editnoticeswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
            if (data) {
                $('#edit_tape_group_modal .warnningdiv').show(); //开
                noticeTypeChange('#edit_tape_group_modal');
            } else {
                $('#edit_tape_group_modal .warnningdiv').hide(); //关
            }
        });
        $('#tape_group_modal select[name=noticetype]').on('change', () => {
            noticeTypeChange('#tape_group_modal')
        });
        $('#edit_tape_group_modal select[name=noticetype]').on('change', () => {
            noticeTypeChange('#edit_tape_group_modal')
        });

        //提交添加磁带组
        $('#addsubmit').on('click', () => {
            var action = $('#addsubmit').attr('data-action');
            if (action === 'add') {
                addTapeGroup();
            }
            if (action === 'import') {
                importTapeGroup();
            }
        });
        //提交修改磁带组
        $('#editsubmit').on('click', editTapeGroup);
        //提交修改磁带
        $('#tapesubmit').on('click', editTape);

    }
    var calculateTapeTotalSize = function (tapeList) {
        if (!Array.isArray(tapeList) || tapeList.length === 0) return 0;
        // 直接累加字节数（新增：先提取GB数字，再转字节）
        return tapeList.reduce((total, tape) => {
            // 提取total_size中的纯数字
            let gbNum = 0;
            if (tape && tape.total_size) {
                //替换所有非数字字符，只保留数字
                const numStr = String(tape.total_size).replace(/[^0-9]/g, '');
                gbNum = Number(numStr) || 0;
            }
            // 把GB数转成字节数
            const size = gbNum * 1024 * 1024 * 1024;
            return total + size;
        }, 0);
    };

    var initSpinner = function () {
        $('#tape_group_modal #generate_div').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 9999
        });
        $('#tape_group_modal #reserve_div').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 9999
        });

        $('#tape_group_modal #spinnerpercent').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 99
        });
        $('#tape_group_modal #spinnersize').spinner({
            value: 10,
            step: 10,
            min: 1,
            max: 9999999
        });
        $('#edit_tape_group_modal #edit_generate_div').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 9999
        });
        $('#edit_tape_group_modal #edit_reserve_div').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 9999
        });
        $('#edit_tape_group_modal #editspinnerpercent').spinner({
            value: 20,
            step: 5,
            min: 1,
            max: 99
        });
        $('#edit_tape_group_modal #editspinnersize').spinner({
            value: 10,
            step: 10,
            min: 1,
            max: 9999999
        });
    }

    var noticeTypeChange = function (modalDiv) {
        var noticeType = $(modalDiv + ' select[name=noticetype]').val();
        if ('1' == noticeType) {
            $(modalDiv + ' .percentdiv').show();
            $(modalDiv + ' .sizediv').hide();
        } else if ('2' == noticeType) {
            $(modalDiv + ' .percentdiv').hide();
            $(modalDiv + ' .sizediv').show();
        }
    }

    var useModeClick = function (event) {
        var mode = $(this).data('mode');
        if (event.target.checked) {
            //如果是取消选中
            $('#useMode').find('input').iCheck("uncheck");
        } else {
            $('#useMode').find('input').iCheck("uncheck");
            $('#useMode').find('input[data-mode=' + mode + ']').iCheck("check");
        }
       // if (mode == 2) {
            // 副本只能选择默认的策略
         //   $('#generate_strategy').val('1').prop('disabled', true);
         //   $('#generate_strategy_days_div').hide();
          //  $('#reserve_strategy').val('1').prop('disabled', true);
          //  $('#reserve_strategy_days_div').hide();
       // } else {
            if ($('#generate_strategy').val() != 1) {
                $('#generate_strategy').val('1').prop('disabled', false);
                $('#reserve_strategy').val('1').prop('disabled', false);
            } else {
                $('#generate_strategy').val('1').prop('disabled', false);
            }
        //}
    }

    var editUseModeClick = function (event) {
        var mode = $(this).data('mode');
        if (event.target.checked) {
            //如果是取消选中
            $('#edituseMode').find('input').iCheck("uncheck");
        } else {
            $('#edituseMode').find('input').iCheck("uncheck");
            $('#edituseMode').find('input[data-mode=' + mode + ']').iCheck("check");
        }
    }

    /**
     * @function 初始化带库选择下拉框
     */
    function initTapeLibSelect() {
        pAjaxRequest({
            'offset': 0,
            'limit': 100
        }, '/api/v1/tapes', 'GET', (res) => {
            var libSelect = $('#tape_group_modal #select_tape_lib');
            libSelect.empty();
            for (let i = 0; i < res.data.rows.length; i++) {
                option = $("<option>").text(res.data.rows[i].name).val(res.data.rows[i].lib_name);
                libSelect.append(option);
            }
            let libName = libSelect.find('option:selected').val();

            initSelectTapeTable(libName);

            libSelect.off().on('change', function () {
                let currentLibName = libSelect.find('option:selected').val();
                tapeSelectHandle(currentLibName);
            });
        })
    }

    /**
     * @function 带库切换重新获取可添加磁带组的磁带
     */
    function tapeSelectHandle(currentLibName) {
        $('#selectTape').bootstrapTable('destroy');
        initSelectTapeTable(currentLibName);
    }

    /**
     * @function 初始化添加磁带组table
     * @param libName 带库名
     * @param isOpGroup 是否是磁带组操作
     */
    var initSelectTapeTable = function (libName) {
        // pAjaxRequest('')
        var tableArea = '#selectTapeDiv';
        let params = {
            'offset': 0,
            'limit': 1000,
            'lib_name': libName,
            'is_op_group': true
        };
        Metronic.blockUI({
            target: '#selectTapeDiv',
            animate: true
        });
        pAjaxRequest(params, '/api/v1/tapes/carriage_info', 'GET', (res) => {
            data = res.data;
            var option = {
                data: data,
                pagination: false,
                showExport: false, //是否开启导出按钮
                showColumns: false, //是否开启列选择按钮
                // onResetView: function () {
                //     initSmallTableHeight(tableArea)
                // },
                columns: [ //列定义
                    {
                        checkbox: true,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'name', //字段名
                        title: LANG.UI_TAPE_CARRIAGE_NAME,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'tape_type',
                        title: LANG.UI_TAPE_CARRIAGE_TYPE,
                        formatter: tapeTypeFormatter
                    },
                    {
                        field: 'serial_number',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'total_size',
                        title: LANG.UI_STORAGE_SIZE,
                    },
                    {
                        field: 'free_size',
                        title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                    },
                    {
                        field: 'tape_status',
                        title: LANG.UI_PUBLIC_STATUS,
                        formatter: statusFormatter
                    }
                ]
            }
            sessionStorage.removeItem('tape_lib_table_pageRecord');
            $('#selectTape').bootstrapTable('destroy');
            $('#selectTape').baseTableConfig().init(option);
            Metronic.unblockUI('#selectTapeDiv');
        })
    }

    /**
     * @function 初始化修改磁带组table（可添加的磁带）
     */
    function initEditSelectedTapeTable(libName) {
        var tableArea = '#edit_add_tapediv';
        let params = {
            'offset': 0,
            'lib_name': libName,
            'is_op_group': true
        };
        Metronic.blockUI({
            target: '#edit_add_tapediv',
            animate: true
        });
        pAjaxRequest(params, '/api/v1/tapes/carriage_info', 'GET', (res) => {
            data = res.data;
            var option = {
                data: data,
                pagination: false,
                showExport: false, //是否开启导出按钮
                showColumns: false, //是否开启列选择按钮
                // onResetView: function () {
                //     initSmallTableHeight(tableArea)
                // },
                columns: [ //列定义
                    {
                        checkbox: true,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'name', //字段名
                        title: LANG.UI_TAPE_CARRIAGE_NAME,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'tape_type',
                        title: LANG.UI_STORAGE_TYPE,
                        formatter: tapeTypeFormatter
                    },
                    {
                        field: 'serial_number',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'total_size',
                        title: LANG.UI_STORAGE_SIZE,
                    },
                    {
                        field: 'free_size',
                        title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                    },
                    {
                        field: 'tape_status',
                        title: LANG.UI_PUBLIC_STATUS,
                        formatter: statusFormatter
                    }
                ]
            }
            $('#edit_add_tape').baseTableConfig().init(option);
            Metronic.unblockUI('#edit_add_tapediv');
        })
    }

    /**
     * @function 扫描带库
     */
    var scanLib = function (name) {
        let libName = name;
        Metronic.blockUI({
            target: '.tree-wrapper',
            animate: true
        });
        pAjaxRequest({
            "tape_lib_name": libName,
            "node_uuid": nodeId,
        }, '/api/v1/tapes/scan', 'POST', function (data) {
            Metronic.unblockUI('.tree-wrapper');
            var op = LANG.UI_TAPE_SEND_SCAN_TAPE_LIB_MSG;
            if (operateResponseList(data, op)) {
                $("#tape_lib_table").bootstrapTable('refresh');
                initTapeTree();
                if (libName == '') {
                    statusHandle(data);
                }
            }
        });
    }

    var statusHandle = function (data) {
        var statusDes = '';
        var startTime = '';
        var startTimeDes = LANG.UI_PUBLIC_START_TIME;
        var des = '';
        switch (data.data.status) {
            case 1:
                statusDes = LANG.UI_TAPE_IS_RUNNING
                break;
            case 2:
                statusDes = LANG.UI_PUBLIC_SUCCESS
                break;
            case 3:
                statusDes = LANG.UI_PUBLIC_FAILED
                break;
        }
        startTime = data.data.start_time;
        
        if (data.data.status == 2 || data.data.status == 3) {
            startTimeDes = LANG.UI_TAPE_LAST_SCAN_TIME;
            $('#scan').prop('disabled', false);
            $('#scan').html('<i class="viconfont vicon-a-Scanning-twosaomiao me-4"></i>'+LANG.UI_TAPE_SCAN + '');
        } else if (data.data.status == 1) {
            $('#scan').html('<i class="viconfont vicon-a-Scanning-twosaomiao me-4"></i>'+LANG.UI_TAPE_STATUS_SCANNING + '');
            $('#scan').prop('disabled', true);
        }
        des = `<span>${LANG.UI_PUBLIC_STATUS}：${statusDes};${startTimeDes}: ${startTime}</span>`;
        if (startTime == null) {
            des = `<span>${LANG.UI_TAPE_NEVER_SCAN}</span>`;
        }
        $('#status_span').empty();
        $('#status_span').append(des);
    }

    var getWarningSettings = function (modalDiv) {
        var warningSettings = {};
        var noticeSwitch = '#noticeswitch';
        var warningPercent = '#warningpercent';
        var warningSize = '#warningsize';
        if (modalDiv == '#edit_tape_group_modal') {
            noticeSwitch = '#editnoticeswitch';
            warningPercent = '#editwarningpercent';
            warningSize = '#editwarningsize';
        }
        warningSettings.power = $(modalDiv + ' ' + noticeSwitch).get(0).checked;
        warningSettings.type = $(modalDiv + ' select[name=noticetype]').val();
        if ('1' == warningSettings.type) {
            warningSettings.value = $(modalDiv + ' ' + warningPercent).val();
        } else {
            warningSettings.value = $(modalDiv + ' ' + warningSize).val();
        }
        return warningSettings;
    }

    /**
     * @function 重置磁带组表单
     */
    var resetTapeGroupForm = function () {
        $('#tape_group_modal #group_name').val('');
        $('#tape_group_modal #reserve_strategy').val('1');
        $('#tape_group_modal #reserve_strategy_days').val(20);

        $('#tape_group_modal #generate_strategy').val('1');
        $('#tape_group_modal #generate_strategy_days').val(20);
        $('#tape_group_modal #generate_strategy').trigger('change');
        $('#tape_group_modal #reserve_strategy').trigger('change');

        $('#tape_group_modal #noticeswitch').bootstrapSwitch('state', true);
        $('#tape_group_modal select[name=noticetype]').val('1');
        $('#tape_group_modal select[name=noticetype]').val('1');
        $('#tape_group_modal #warningsize').val(10);
        $('#tape_group_modal #warningpercent').val(20);
        noticeTypeChange('#tape_group_modal');
        $('#backupCheck').iCheck('uncheck');
        $('#copyCheck').iCheck('uncheck');
        $('#tape_group_modal #group_des').val('');
    }

    /**
     * @function 初始化导入磁带组表格
     */
    var initImportTapeGroupModal = function () {
        $('#tape_group_modal #addTitle').text(LANG.UI_TAPE_IMPORT_GROUP);
        $('#select_tape_lib').parent().parent().parent().hide();
        initImportSelectTapeTable(modifyGroupUuid);
        $('#tape_group_modal #selectTapeDiv label.control-label').text(LANG.UI_TAPE_IMPORT_GROUP_CARRIAGE);
    }

    /**
     * @function 初始化导入磁带组table
     */
    function initImportSelectTapeTable(groupUuid) {
        let params = {
            'offset': 0,
            'group_uuid': groupUuid
        };
        let data;
        pAjaxRequest(params, '/api/v1/tapes/carriage_info', 'GET', (res) => {
            data = res.data;
            var option = {
                // vin_url: '/api/v1/tapes/carriage_info',
                // vin_method: 'GET',
                data: data,
                pagination: false,
                showExport: false, //是否开启导出按钮
                showColumns: false, //是否开启列选择按钮

                columns: [ //列定义
                    {
                        field: 'name', //字段名
                        title: LANG.UI_TAPE_CARRIAGE_NAME,
                        sortable: false, //默认可排序，禁用排序才写此项
                        // type: "href", //列的自定义type属性,值包括"href","label","operation",返回不同的模板
                    },
                    {
                        field: 'serial_number',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                        sortable: false,
                    },
                    {
                        field: 'total_size',
                        title: LANG.UI_STORAGE_SIZE,
                    },
                    {
                        field: 'free_size',
                        title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                    },
                    {
                        field: 'tape_status',
                        title: LANG.UI_PUBLIC_STATUS,
                        sortable: false,
                        formatter: statusFormatter
                    }
                ]
            }
            $('#selectTape').bootstrapTable('destroy');
            $('#selectTape').baseTableConfig().init(option);
        })
    }

    /**
     * @function 导入磁带组提交
     */
    var importTapeGroup = function () {
        let tapeGroupProperties = {};
        let useMode;
        let warningSettings = getWarningSettings('#tape_group_modal');
        tapeGroupProperties.group_uuid = modifyGroupUuid;
        tapeGroupProperties.name = $('#tape_group_modal #group_name').val();
        tapeGroupProperties.reserve_strategy_type = parseInt($('#tape_group_modal #reserve_strategy').val());
        if (tapeGroupProperties.reserve_strategy_type === 2) {
            tapeGroupProperties.reserve_days = $('#tape_group_modal #reserve_strategy_days').val();
        } else {
            tapeGroupProperties.reserve_days = 0;
        }

        tapeGroupProperties.backup_set_strategy_type = parseInt($('#tape_group_modal #generate_strategy').val());
        if (tapeGroupProperties.backup_set_strategy_type === 3) {
            tapeGroupProperties.backup_set_generated_days = $('#tape_group_modal #generate_strategy_days').val();
        } else {
            tapeGroupProperties.backup_set_generated_days = 0;
        }
        tapeGroupProperties.description = $('#group_des').val();
        tapeGroupProperties.detail = "";
        /*if ($('#tape_group_modal #backupCheck').is(':checked')) {
            useMode = 1;
        } else if ($('#tape_group_modal #copyCheck').is(':checked')) {
            useMode = 2;
        } else if ($('#tape_group_modal #archiveCheck').is(':checked')) {
            useMode = 3;
        } else if(!$('#backupCheck').is(':checked') && !$('#copyCheck').is(':checked')){
            UIToastr.showWarning(LANG.UI_STORAGE_USE_MODE_SELECT, LANG.UI_STORAGE_USE_MODE_SELECT_TIPS);
            return false;
        }*/

        useMode = 1;
        
        Metronic.blockUI({
            target: '#tape_lib_table',
            animate: true
        });
        pAjaxRequest({
            'tape_group_properties': tapeGroupProperties,
            'use_mode': useMode,
            'warning_settings': warningSettings,
        }, '/api/v1/tapes/group/import', 'POST', function (res) {
            if (res.success) {
                $('#tape_group_modal').modal('hide');
            }
            Metronic.unblockUI('.tree-wrapper');
            Metronic.unblockUI('#tape_lib_table');
            var op = LANG.UI_TAPE_SEND_ADD_GROUP_MSG;
            if (operateResponseList(res, op)) {
                $("#tape_lib_table").bootstrapTable('refresh');
                getTapeGroupNode(groupNode);
            }
        })
    }

    /**
     * @function 添加磁带组提交
     */
    var addTapeGroup = function () {
        let selectedTape = $('#selectTape').bootstrapTable('getSelections');
        let selectTapeType = 0; //选择的磁带类型。不能选择不同的类型
        if (selectedTape.length < 1 || $('#group_name').val() === '') {
            toastr['warning'](LANG.UI_TAPE_ADD_GROUP_TITLE, LANG.UI_TAPE_ADD_GROUP_TIP);
            return;
        }
        for (let i = 0; i < selectedTape.length; i++) {
            if (i != 0 && selectTapeType != selectedTape[i].tape_type) {
                return toastr['warning'](LANG.UI_TAPE_ADD_GROUP_TITLE2, LANG.UI_TAPE_ADD_GROUP_TIP);
            }
            selectTapeType = selectedTape[i].tape_type;
        }
        let tapeGroupProperties = {};
        let tapeSerialNumberSet = [];
        let useMode;
        let warningSettings = getWarningSettings('#tape_group_modal');
        // 计算选中磁带总空间 + 告警阈值对比校验

        const tapeTotalSizeBytes = calculateTapeTotalSize(selectedTape); // 总容量（字节，正确）
        const warningInputValue = parseInt( (warningSettings.value || '').trim(), 10 ) || 0;
        let warningValueBytes = 0;
        let isThresholdInvalid = false;

        // 按大小告警时type=2
        if (warningSettings.type === '2') {
            // 转字节
            warningValueBytes = Math.floor(warningInputValue * 1024 * 1024 * 1024);
            // 整数对比
            isThresholdInvalid = warningValueBytes > tapeTotalSizeBytes;
        } else if (warningSettings.type === '1') { // 百分比
            isThresholdInvalid = warningInputValue < 0 || warningInputValue > 100;
        }
        // 校验不通过：提示并阻止提交
        if (isThresholdInvalid) {
            const tipText = warningSettings.type === '2'
            toastr['warning'](LANG.UI_STORAGE_MODIFY_TIPS3, LANG.UI_STORAGE_MODIFY);
            return;
        }

        let libName = $('#select_tape_lib').val();
        tapeGroupProperties.group_uuid = "";
        tapeGroupProperties.name = $('#tape_group_modal #group_name').val();
        tapeGroupProperties.reserve_strategy_type = parseInt($('#tape_group_modal #reserve_strategy').val());
        if (tapeGroupProperties.reserve_strategy_type === 2) {
            tapeGroupProperties.reserve_days = $('#tape_group_modal #reserve_strategy_days').val();
        } else {
            tapeGroupProperties.reserve_days = 0;
        }

        tapeGroupProperties.backup_set_strategy_type = parseInt($('#tape_group_modal #generate_strategy').val());
        if (tapeGroupProperties.backup_set_strategy_type === 3) {
            tapeGroupProperties.backup_set_generated_days = $('#tape_group_modal #generate_strategy_days').val();
        } else {
            tapeGroupProperties.backup_set_generated_days = 0;
        }

        if (selectedTape.length == 1 && (tapeGroupProperties.backup_set_strategy_type != 1 || tapeGroupProperties.reserve_strategy_type != 1)) {
            toastr['warning'](LANG.UI_TAPE_ADD_GROUP_TITLE1, LANG.UI_TAPE_ADD_GROUP_TIP);
            return;
        }

        tapeGroupProperties.description = $('#group_des').val();
        tapeGroupProperties.detail = "";
        /*if ($('#tape_group_modal #backupCheck').is(':checked')) {
            useMode = 1;
        } else if ($('#tape_group_modal #copyCheck').is(':checked')) {
            useMode = 2;
        } else if ($('#tape_group_modal #archiveCheck').is(':checked')) {
            useMode = 3;
        } else if(!$('#backupCheck').is(':checked') && !$('#copyCheck').is(':checked')){
            UIToastr.showWarning(LANG.UI_STORAGE_USE_MODE_SELECT, LANG.UI_STORAGE_USE_MODE_SELECT_TIPS);
            return false;
        }*/

        useMode = 1;
        $.each(selectedTape, function (k, v) {
            tapeSerialNumberSet.push(v.serial_number);
        })
        Metronic.blockUI({
            target: '#tape_lib_table',
            animate: true
        });
        pAjaxRequest({
            'tape_group_properties': tapeGroupProperties,
            'tape_serial_number_set': tapeSerialNumberSet,
            'use_mode': useMode,
            'warning_settings': warningSettings,
            'lib_name': libName
        }, '/api/v1/tapes/group', 'POST', function (res) {
            if (res.success) {
                $('#tape_group_modal').modal('hide');
            }
            Metronic.unblockUI('.tree-wrapper');
            Metronic.unblockUI('#tape_lib_table');
            var op = LANG.UI_TAPE_SEND_ADD_GROUP_MSG;
            if (operateResponseList(res, op)) {
                $("#tape_lib_table").bootstrapTable('refresh');
                getTapeGroupNode(groupNode);
            }
        })
    }

    /**
     * @function 修改磁带组提交
     */
    function editTapeGroup() {
        if(!$('#editbackupCheck').is(':checked') && !$('#editcopyCheck').is(':checked')){
            UIToastr.showWarning(LANG.UI_STORAGE_USE_MODE_SELECT, LANG.UI_STORAGE_USE_MODE_SELECT_TIPS);
            return false;
        }

        var initErrorFlag = false;
        let selectedTape = $('#edit_select_tape').bootstrapTable('getSelections'); // 已有磁带
        let selectTape = $('#edit_add_tape').bootstrapTable('getSelections'); // 增加磁带
        const allSelectedTapes = [...selectedTape, ...selectTape];

        // 至少要有一个磁带和设置磁带组名
        if (allSelectedTapes.length === 0 || $('#edit_group_name').val() === '') {
            UIToastr.showWarning(LANG.UI_TAPE_MODIFY_GROUP_TIP, LANG.UI_TAPE_ADD_GROUP_TITLE);
            return false;
        }

        // 所有磁带类型必须一致
        const firstType = allSelectedTapes[0].tape_type;
        for (let i = 1; i < allSelectedTapes.length; i++) {
            if (allSelectedTapes[i].tape_type !== firstType) {
                UIToastr.showWarning(LANG.UI_TAPE_MODIFY_GROUP_TIP, LANG.UI_TAPE_ADD_GROUP_TITLE2);
                return false;
            }
        }

        let useMode;
        let warningSettings = getWarningSettings('#edit_tape_group_modal');
        // 计算现有+新增磁带总空间 + 告警阈值对比校验
        const allTapeList = [...selectedTape, ...selectTape]; //加上新增磁带
        const tapeTotalSizeBytes = calculateTapeTotalSize(allTapeList);// 总容量（字节，正确）
        const warningInputValue = parseInt( (warningSettings.value || '').trim(), 10 ) || 0;
        let warningValueBytes = 0;
        let isThresholdInvalid = false;

        // 按大小告警时type=2
        if (warningSettings.type === '2') {
            // 转字节
            warningValueBytes = Math.floor(warningInputValue * 1024 * 1024 * 1024);
            // 整数对比
            isThresholdInvalid = warningValueBytes > tapeTotalSizeBytes;
        } else if (warningSettings.type === '1') { // 百分比
            isThresholdInvalid = warningInputValue < 0 || warningInputValue > 100;
        }
        // 校验不通过：提示并阻止提交
        if (isThresholdInvalid) {
            const tipText = warningSettings.type === '2'
            toastr['warning'](LANG.UI_STORAGE_MODIFY_TIPS3, LANG.UI_STORAGE_MODIFY);
            return;
        }
        bootbox.prompt({
            title: LANG.UI_TAPE_MODIFY_GROUP_TIP_COMFIRM,
            inputType: 'password',
            callback: function (result) {
                if (result == null) return;
                if (hex_md5(result) == _UserPassword) {
                    _userIsVerify = true;
                    Metronic.blockUI({
                        target: '#current_table',
                        animate: true
                    });


                    tapeGroupProperties.group_uuid = modifyGroupUuid;
                    tapeGroupProperties.name = $('#edit_tape_group_modal #edit_group_name').val();
                    tapeGroupProperties.reserve_strategy_type = parseInt($('#edit_tape_group_modal #edit_reserve_strategy').val());
                    if (tapeGroupProperties.reserve_strategy_type === 2) {
                        tapeGroupProperties.reserve_days = $('#edit_tape_group_modal #edit_reserve_days').val();
                    } else {
                        tapeGroupProperties.reserve_days = 0;
                    }

                    tapeGroupProperties.backup_set_strategy_type = parseInt($('#edit_tape_group_modal #edit_generate_strategy').val());
                    if (tapeGroupProperties.backup_set_strategy_type === 3) {
                        tapeGroupProperties.backup_set_generated_days = $('#edit_tape_group_modal #edit_generate_days').val();
                    } else {
                        tapeGroupProperties.backup_set_generated_days = 0;
                    }

                    if (selectedTape.length + selectTape.length == 1 && (tapeGroupProperties.backup_set_strategy_type != 1 || tapeGroupProperties.reserve_strategy_type != 1)) {
                        toastr['warning'](LANG.UI_TAPE_ADD_GROUP_TITLE1, LANG.UI_TAPE_ADD_GROUP_TIP);
                        return;
                    }
                    
                    tapeGroupProperties.description = $('#edit_tape_group_modal #edit_group_des').val();
                    tapeGroupProperties.detail = "";

                    /*if ($('#edit_tape_group_modal #editbackupCheck').is(':checked')) {
                        useMode = 1;
                    } else if ($('#edit_tape_group_modal #editcopyCheck').is(':checked')) {
                        useMode = 2;
                    } else if(!$('#editbackupCheck').is(':checked') && !$('#editcopyCheck').is(':checked')){
                        UIToastr.showWarning(LANG.UI_STORAGE_USE_MODE_SELECT, LANG.UI_STORAGE_USE_MODE_SELECT_TIPS);
                        return false;
                    }*/

                    useMode = 1;

                    $.each(selectTape, function (k, v) {
                        addTapeSerialNumberSet.push(v.serial_number);
                    })

                    pAjaxRequest({
                        'tape_group_properties': tapeGroupProperties,
                        'add_tape_serial_number_set': addTapeSerialNumberSet,
                        'remove_tape_serial_number_set': removeTapeSerialNumberSet,
                        'use_mode': useMode,
                        'warning_settings': warningSettings
                    }, '/api/v1/tapes/group', 'PUT', function (res) {
                        Metronic.unblockUI('.tree-wrapper');
                        if (res.success) {
                            $('#edit_tape_group_modal').modal('hide');
                        }
                        var op = LANG.UI_TAPE_SEND_MODIFY_GROUP_MSG;
                        if (operateResponseList(res, op)) {
                            addTapeSerialNumberSet = [];
                            removeTapeSerialNumberSet = [];
                            $("#tape_lib_table").bootstrapTable('refresh');
                            getTapeGroupNode(groupNode);
                        }
                    })
                } else {
                    $('.bootbox-input').css('border-color', "#a94442");
                    if (!initErrorFlag) {
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_TAPE_DEL_GROUP_PASSWORD + '</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }

            }
        });
    }

    /**
     * @function 磁带修改提交
     */
    function editTape() {
        if ($('#edit_tape_name').val() === '') {
            toastr['warning'](LANG.UI_TAPE_MODIFY_GROUP_TITLE, LANG.UI_TAPE_MODIFY_GROUP_TIP);
            return;
        }
        let params = {};
        params.id = tapeId;
        params.new_name = $('#edit_tape_modal #edit_tape_name').val();
        params.description = $('#edit_tape_modal #edit_tape_des').val();
        $('#edit_tape_modal').modal('hide');
        pAjaxRequest(params, '/api/v1/tapes/carriage_info', 'PUT', function (res) {
            Metronic.unblockUI('.tree-wrapper');
            var op = LANG.UI_TAPE_SEND_MODIFY_CARRIAGE_MSG;
            if (operateResponseList(res, op)) {
                $('#tape_lib_table').bootstrapTable('refresh');
                // getEachTape();
            }
        })
    }

    /**
     * @function 删除备份集
     */
    function delBackupSet() {
        let selectRows = $('#tape_lib_table').bootstrapTable('getSelections');
        let backupsetUuid = [];
        var initErrorFlag = false;
        if (selectRows.length <= 0) {
            UIToastr.showInfo(LANG.UI_TAPE_DEL_BACKUP_SET_TITLE, LANG.UI_TAPE_DEL_BACKUP_SET_TIP);
        } else {
            bootbox.prompt({
                title: LANG.UI_TAPE_DEL_BACKUP_SET_CONFIRM,
                inputType: 'password',
                callback: function (result) {
                    if (result == null) return;
                    if (hex_md5(result) == _UserPassword) {
                        Metronic.blockUI({
                            target: '#tape_lib_table',
                            animate: true
                        });
                        for (let i = 0; i < selectRows.length; i++) {
                            backupsetUuid.push(selectRows[i].backup_set_uuid);
                        }
                        pAjaxRequest({
                            'backupset_uuid_list': backupsetUuid
                        }, '/api/v1/tapes/backup_set', 'DELETE', function (res) {
                            let op = LANG.UI_TAPE_SEND_DEL_BACKUP_SET_MSG;
                            if (operateResponseList(res, op)) {
                                $('#tape_lib_table').bootstrapTable('refresh');
                                if (res.success) {
                                    $('#tape_lib_table').bootstrapTable('uncheckAll');
                                }
                            }
                            Metronic.unblockUI('#tape_lib_table');
                        });
                    } else {
                        $('.bootbox-input').css('border-color', "#a94442");
                        if (!initErrorFlag) {
                            var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_TAPE_DEL_GROUP_PASSWORD + '</p>';
                            $('.bootbox-input').after(des);
                            initErrorFlag = true;
                        }
                        return false;
                    }
                }
            });
        }
    }

    /**
     * @function 设置磁带树
     * @param {data{rows[{}], total: num}} zNodes 
     */
    function setTapeTree(zNodes) {
        Metronic.unblockUI('.tree-wrapper');
        var zNodesObj = {
            "name": LANG.UI_TAPE_STRUCTURE,
            "open": true,
            "type": -1,
            "nocheck": true,
            "icon": './img/tape/tape-structure.svg',
            "children": [{
                "name": LANG.UI_TAPE_GROUP,
                "id": '1',
                "type": 1,
                "icon": './img/tape/tape-group.svg',
                "isParent": true
            }, {
                "name": LANG.UI_TAPE_LIB,
                "id": '2',
                "type": 2,
                "icon": './img/tape/tape-lib.svg',
                "isParent": true
            }]
        }

        var settings = {
            check: {
                enable: true,
                nocheckInherit: true,
            },
            data: {
                simpleData: {
                    enable: true
                },
                keep: {
                    parent: true
                },
                key: {

                }
            },
            callback: {
                beforeClick: nodeSelect,
                beforeExpand: getChildNode,
                // onclick: initTapeTable
            },
            view: {
                // showTitle: showTitleForTree ,
                dblClickExpand: true,
                nameIsHTML: true,
            }
        };
        zTree = $.fn.zTree.init($("#tape_tree"), settings, zNodesObj);
    }

    /**
     * @function 点击获取子节点
     * @param treeId 树的id 
     * @param treeNode 当前节点 
     */
    function nodeSelect(treeId, treeNode) {
        if (treeNode.type === 4 || treeNode.type === 9) {
            zTree.expandNode(treeNode, true);
            return;
        }
        getChildNode(treeId, treeNode);
    }

    /**
     * @function 展开获取子节点
     * @param treeId 树的id 
     * @param treeNode 当前节点 
     */
    function getChildNode(treeId, treeNode) {
        if (treeNode.type != -1 && treeNode.type != 9 && treeNode.type != 4 && treeNode.type != 7 && treeNode.type != 8) {
            //先隐藏所有
            $('#vin_tape_toolbar').empty().html(toolBar);
            $('#tape_lib_table').bootstrapTable('destroy');
        }
        if (treeNode.type === -1) return;

        tableChange(treeNode.type, treeNode.name, treeNode); //控制点击后页面展示
        if (treeNode.type === 1) { //磁带组
            zTree.removeChildNodes(treeNode);
            getTapeGroupNode(treeNode);
            zTree.expandNode(treeNode, true);
            return;
        }
        if (treeNode.type === 9) { //磁带组实例
            return;
        }
        if (treeNode.type === 10) { //磁带组里的磁带实例
            getEachGroupTape(treeNode);
            return;
        }
        if (treeNode.type === 11) { //备份集实例
            getTapeBackupSet(treeNode);
            return;
        }
        if (treeNode.type === 2) { //磁带库
            zTree.removeChildNodes(treeNode);
            getTapeLibNode(treeNode);
            zTree.expandNode(treeNode, true);
            return;
        }
        if (treeNode.type === 4) { //磁带库实例
            return;
        }
        if (treeNode.type === 5) { //磁带
            zTree.removeChildNodes(treeNode);
            getEachTape(treeNode);
            return;
        }
        if (treeNode.type === 6) { //驱动器
            getEachDriver(treeNode);
            return;
        }
        return true;
    }

    /**
     * @function 控制点击树节点后的页面展示
     * @param type 节点类型 
     */
    function tableChange (type, name, node) {
        if (type == -1 && type == 9 && type == 4 && type == 7 && type == 8) return;
        $('#groupStrategyTips').hide();
        $('#tapeTips').hide();
        $('#dbdataUrl').text('');
        var parentName = node.getParentNode().name;

        switch (type) {
            case 1: //磁带组
                $('#groupStrategyTips').show();
                $('#dbdataUrl').text(LANG.UI_TAPE_GROUP);
                
                if ($('#groupStrategyTips').length === 0) {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
                } else {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 334px)');
                }

                break;
            case 10: 
                $('#groupStrategyTips').show();
                $('#dbdataUrl').text(`${LANG.UI_TAPE_GROUP}(${parentName}) - ${name}`);
                
                if ($('#groupStrategyTips').length === 0) {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
                } else {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 334px)');
                }

                break;
            case 11: 
                $('#groupStrategyTips').show();
                $('#dbdataUrl').text(`${LANG.UI_TAPE_GROUP}(${parentName}) - ${name}`);
                
                if ($('#groupStrategyTips').length === 0) {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
                } else {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 334px)');
                }

                break;
            case 2: //磁带库
                $('#tapeTips').show();
                $('#dbdataUrl').text(LANG.UI_TAPE_LIB);
                
                if ($('#tapeTips').length === 0) {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
                } else {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 164px)');
                }

                break;
            case 5: 
                $('#tapeTips').show();
                $('#dbdataUrl').text(`${LANG.UI_TAPE_LIB}(${parentName}) - ${name}`);

                if ($('#tapeTips').length === 0) {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
                } else {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 164px)');
                }
                
                break;
            case 6: 
                $('#tapeTips').show();
                $('#dbdataUrl').text(`${LANG.UI_TAPE_LIB}(${parentName}) - ${name}`);
                
                if ($('#tapeTips').length === 0) {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 44px)');
                } else {
                    $('.list-management-wrapper .table-container').css('height', 'calc(100% - 164px)');
                }

                break;
            default:
                break;
        }
    }
    /**
     * @function 设置backupsetTree
     * @param {*} zNodes 
     */
    function setBackupSetTree(backup_set_uuid, backupsetName) {
        Metronic.blockUI({ target: '#backupset_tree', animate: true });
        pAjaxRequest({backup_set_id: backup_set_uuid}, "/api/v1/tapes/timepoint", "GET", function (d) {
            Metronic.unblockUI('#backupset_tree');
            if (!d.success) {
                return UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, d.message);
            }
            var zNodes = d.data;
            var dbNode = zNodes.db_type_info;
            // 定义 模块和子模块对应的展示名称和icon
            var mouduleConfig = {
                '2-0': {'name': LANG.UI_PUBLIC_VM, 'icon': './img/vm/vm.png'}, // 虚拟化
                '2-1': {'name': LANG.UI_PUBLIC_VM, 'icon': './img/vm/vm.png'}, // 虚拟化
                '2-2': {'name': LANG.UI_PUBLIC_VM, 'icon': './img/vm/vm.png'}, // 私有云
                '2-3': {'name': LANG.UI_PUBLIC_VM, 'icon': './img/vm/vm.png'}, // 公有云
                '5-0': {'name': LANG.UI_VISUAL_OS, 'icon': './img/os/os_protect.png'}, // 定时卷（数据库中submodule_type存的0）
                '5-1': {'name': LANG.UI_COPY_MODULE_LABEL_COMPLETE_OS, 'icon': './img/platform/host.png'}, // 定时整机
                '11-2': {'name': LANG.UI_REPORT_NAS, 'icon': './img/tape/NAS.svg'}, // 文件系列 nas
                '3-1': {'name': LANG.UI_FILE_FILE, 'icon': './img/fs/wenjian.png'}, // 文件系列 文件
                '3-3': {'name': LANG.UI_PLATFORM_DES_HADOOP, 'icon': './img/tape/hadoop.svg'}, // 文件系列 HADOOP
                '3-4': {'name': LANG.UI_PLATFORM_DES_HADOOP, 'icon': './img/tape/obs.svg'}, // 文件系列 OBS
                '4-0': {'name': LANG.UI_VISUAL_MODULE_DB, 'icon': './img/db/database.png'}, // 数据库
                '14-0': {'name': LANG.UI_VISUAL_M365, 'icon': '', 'iconSkin': 'iconSkin-exch-task'}, // Microsoft 365
                '14-1': {'name': LANG.UI_VISUAL_M365, 'icon': '', 'iconSkin': 'iconSkin-exch-task'}, // Microsoft 365
                '28-0': {'name': LANG.UI_BACKUP_DATA_MODULE_K8S, 'icon': '', 'iconSkin': 'iconSkin-exch-task'}, // 容器
            };

            var task_info = zNodes.task_info;
            var ztreeArr = [];
            var isOracle = false;
            for (var j in task_info) {
                var child = [];
                var modk = task_info[j].module_type + '-' + task_info[j].sub_module_type;
                var modules = mouduleConfig[modk];
                if (modules == undefined) {
                    continue;
                }
                child.name = modules['name'];
                child.isParent = true;
                child.open = true;
                child.type = -1;

                if (modules['icon'] != undefined) {
                    child.icon = modules['icon'];
                } else {
                    child.iconSkin = modules['iconSkin'];
                }
                var children = [];
                if (task_info[j].module_type == CONF.MODULE_TYPE.DB) {
                    if (isOracle) {
                        continue;
                    }
                    child.id = task_info[j].module_type;
                    // 数据库的单独算
                    children = getDBBackupPointChild(dbNode);
                    isOracle = true;
                } else {
                    child.id = task_info[j].task_uuid;
                    children = getBackupPointChild(zNodes, task_info[j].task_uuid);
                }
                child.children = children;
                ztreeArr.push(child);
            }

            var zNodesObj = {
                name: backupsetName,
                icon: '',
                open: true,
                children: ztreeArr
            }
            var settings = {
                check: {
                    enable: false,
                    nocheckInherit: true,
                },
                data: {
                    simpleData: {
                        enable: true
                    },
                    keep: {
                        parent: true
                    },
                    key: {
                        title: "title"
                    }
                },
                // callback: {
                //     beforeClick: nodeSelect,
                //     beforeExpand: getChildNode,
                //     // onclick: initTapeTable
                // },
                view: {
                    // showTitle: showTitleForTree ,
                    dblClickExpand: true,
                    nameIsHTML: true,
                    showTitle: true,
                }
            };
            zBackupSetTree = $.fn.zTree.init($("#backupset_tree"), settings, zNodesObj);
        })
    }

    /**
     * 根据模块类型获取备份点数据库类型层(数据库树结构不一样，特殊处理)
     * @param {string} moduleType 模块类型
     * @param {Object} dbNode 包含任务信息的对象
     * @param {string} subModuleType 子模块类型
     * @returns {Array} 任务对象数组
     */
    function getDBBackupPointChild(dbNode) {
        var nodes = JSON.parse(dbNode);
        var typeObj = [];
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].eventtype == 'db_type') {
                nodes[i].nocheck = false;
                nodes[i].children = getDBJobData(nodes[i].id, nodes);
                typeObj.push(nodes[i]);    
            }
        }
        return typeObj;
    }

    /**
     * 根据模块类型获取备份点任务层(数据库树结构不一样，特殊处理)
     * @param {string} moduleType 模块类型
     * @param {Object} dbNode 包含任务信息的对象
     * @param {string} subModuleType 子模块类型
     * @returns {Array} 任务对象数组
     */
    function getDBJobData(pId, nodes) {
        var jobObj = [];
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].pId == pId) {
                nodes[i].nocheck = false;
                nodes[i].children = getDBOrInstanceData(nodes[i].id, nodes, nodes[i].db_type, nodes[i].task_uuid);
                jobObj.push(nodes[i]);    
            }
        }
        return jobObj;
    }

    /**
     * 根据模块类型获取备份点实例或者数据库层(数据库树结构不一样，特殊处理)
     * @param {string} pId 父级id
     * @param {Object} nodes 数据库节点
     * @param {string} dbType 数据库类型
     * @returns {Array} 任务对象数组
     */
    function getDBOrInstanceData(pId, nodes, dbType, taskUuid) {
        var obj = [];
        if (dbType == CONF.DB_TYPE.SQLSERVER || dbType == CONF.DB_TYPE.SAPHANA) {
            // SQLSERVER SAPHANA 多显示一层实例
            for (let i = 0; i < nodes.length; i++) {
                if (nodes[i].pId == pId) {
                    nodes[i].nocheck = false;
                    nodes[i].children = getDB(nodes[i].id, nodes, taskUuid);
                    obj.push(nodes[i]);    
                }
            }
        } else {
            for (let i = 0; i < nodes.length; i++) {
                if (nodes[i].pId == pId) {
                    nodes[i].nocheck = false;
                    nodes[i].children = getDBData(nodes[i].id, nodes, taskUuid);
                    obj.push(nodes[i]);    
                }
            }
        }
        
        return obj;
    }

    /**
     * 根据模块类型获取备份点数据库层(数据库树结构不一样，特殊处理)
     * @param {string} pId 父级id
     * @param {Object} nodes 数据库节点
     * @returns {Array} 对象数组
     */
    function getDB(pId, nodes, taskUuid) {
        var obj = [];
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].pId == pId) {
                nodes[i].nocheck = false;
                nodes[i].children = getDBData(nodes[i].id, nodes, taskUuid);
                obj.push(nodes[i]);    
            }
        }
        return obj;
    }

    /**
     * 根据模块类型获取备份点完备点层(数据库树结构不一样，特殊处理)
     * @param {string} pId 父级id
     * @param {Object} nodes 数据库节点
     * @returns {Array} 对象数组
     */
    function getDBData(pId, nodes, taskUuid) {
        var obj = [];
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].pId == pId && taskUuid == nodes[i].task_uuid) {
                nodes[i].nocheck = false;
                nodes[i].children = getDBOtherData(nodes[i].id, nodes, taskUuid);
                obj.push(nodes[i]);    
            }
        }
        return obj;
    }

    /**
     * 根据模块类型获取备份点非完备点层(数据库树结构不一样，特殊处理)
     * @param {string} pId 父级id
     * @param {Object} nodes 数据库节点
     * @returns {Array} 对象数组
     */
    function getDBOtherData(pId, nodes, taskUuid) {
        var obj = [];
        
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].pId == pId && taskUuid == nodes[i].task_uuid) {
                nodes[i].nocheck = false;
                obj.push(nodes[i]);    
            }
        }
        return obj;
    }

    /**
     * 根据模块类型获取备份点子任务
     * @param {string} moduleType 模块类型
     * @param {Object} zNodes 包含任务信息的对象
     * @param {string} subModuleType 子模块类型
     * @returns {Array} 任务对象数组
     */
    function getBackupPointChild(zNodes, taskUuid) {
        var nodes = zNodes;

        var taskObj = [];
        
        for (let i = 0; i < nodes.task_info.length; i++) {
            if (taskUuid == nodes.task_info[i].task_uuid) {
                nodes.task_info[i].nocheck = false;
                nodes.task_info[i].children = getData(nodes, nodes.task_info[i].task_uuid);
                nodes.task_info[i].icon = './img/platform/flag.png';
                taskObj.push(nodes.task_info[i]);
            }
        }
        return taskObj;
    }

    /**
     * 根据指定的模块类型、节点和任务UUID获取对应的完全备份点数据
     * @param {Object} nodes - 包含时间点的节点对象
     * @param {string} taskUuid - 任务的唯一标识符
     * @returns {Array} - 符合条件的时间点数据数组
     */
    function getData(nodes, taskUuid) {
        var data = [];
        
        for (let i = 0; i < nodes.time_point.length; i++) {
            if (nodes.time_point[i].task_uuid == taskUuid) {
                if (nodes.time_point[i].backup_mode == 1) {
                    //完备点
                    nodes.time_point[i].icon = './img/platform/timepoint-f.png';
                    nodes.time_point[i].children = getDataChilds(nodes, nodes.time_point[i].timepoint_uuid);
                    data.push(nodes.time_point[i]);
                }

            }
        }

        return data;
    }

    /**
     * 获取对应的依赖备份点数据
     * @param {Object} nodes - 包含时间点的节点对象
     * @param {string} taskUuid - 任务的唯一标识符
     * @returns {Array} - 符合条件的时间点数据数组
     */
    function getDataChilds(nodes, timepointUuid) {
        var data = [];
        for (let i = 0; i < nodes.time_point.length; i++) {
            if (nodes.time_point[i].pId == timepointUuid) {
                if (nodes.time_point[i].backup_mode == 2) { //增量备份点
                    nodes.time_point[i].icon = './img/platform/timepoint-i.png';
                } else if (nodes.time_point[i].backup_mode == 3) { //差异备份点
                    nodes.time_point[i].icon = './img/platform/timepoint-d.png';
                } else if (nodes.time_point[i].backup_mode == 4) { //日志备份点
                    nodes.time_point[i].icon = './img/platform/timepoint.png';
                }
                data.push(nodes.time_point[i]);           
            }
        }

        return data;
    }


    /**
     * @function 获取磁带组子节点
     * @param treeNode 当前节点
     */
    function getTapeGroupNode(treeNode) {
        groupNode = treeNode;
        if (treeNode) {
            let data = {
                'offset': 0,
                'node_uuid': nodeId
            };
            Metronic.blockUI({
                target: '.tree-wrapper',
                animate: true
            });
            pAjaxRequest(data, '/api/v1/tapes/group', 'GET', (res) => {
                Metronic.unblockUI('.tree-wrapper');
                for (let i = 0; i < res.data.rows.length; i++) {
                    res.data.rows[i].isParent = true;
                    res.data.rows[i].icon = './img/tape/tape-group.svg';
                    res.data.rows[i].type = 9; //磁带组实例的type9
                    res.data.rows[i].children = [{
                            "name": LANG.UI_STORAGE_TYPE_TAPE,
                            "id": '3',
                            "type": 10, //磁带组下的磁带type10
                            "icon": './img/tape/tape-carriage.svg',
                            "isParent": false,
                            "open": false
                        },
                        {
                            "name": LANG.UI_DATA_TYPE_BACKUPSET,
                            "id": '4',
                            "type": 11, //备份集实例type11
                            "icon": './img/tape/tape-backupset.svg',
                            "isParent": false,
                            "open": false
                        }
                    ]
                }
                zTree.removeChildNodes(treeNode);
                zTree.addNodes(treeNode, res.data.rows);
                initGroupTable(res.data);
                initGroupFlag = true;
                // }
            });
        } else return;
    }


    //初始化当前用户密码用于删除二次确认
    var getUserPassword = function () {
        pAjaxRequest({}, '/api/v1/users/password', 'GET', function (res) {
            _UserPassword = res.data.password;
        })
    }

    /**
     * @function 初始化磁带组table
     */
    function initGroupTable(treeNode) {
        $('#addTask').show();
        $('#delete_backupset').parent().hide();

        function optionFormatter(value, row, index, field) {
            var button = '<div class="btn-group">';
            if (index > 5) {
                button = '<div class="btn-group dropup">';
            }
            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu" id="' + row.group_uuid + '">';
            if ($.inArray('p_tape_device_edit', CONF.PERMISSION_ARR) !== -1) {
                button += '<li class="edit"><a href="javascript:;" ><i class="viconfont vicon-a-Editbianji1"></i>'+LANG.UI_PUBLIC_EDIT+'</a></li>';
            }
            if ($.inArray('p_tape_device_delete', CONF.PERMISSION_ARR) !== -1) {
                button += '<li class="delete"><a href="javascript:;" ><i class="viconfont vicon-a-Deleteshanchu2"></i>'+LANG.UI_PUBLIC_DELETE+'</a></li>';
            }
            if ($.inArray('p_tape_device_import', CONF.PERMISSION_ARR) !== -1) {
                button += '<li class="import"><a href="javascript:;" ><i class="viconfont vicon-a-Afferent-threechuanru3"></i>'+LANG.UI_TAPE_CARRIAGE_IMPORT+'</a></li>';
            }
            button += '</ul></div>';
            if ($.inArray('p_tape_device_edit', CONF.PERMISSION_ARR) === -1 &&
                $.inArray('p_tape_device_delete', CONF.PERMISSION_ARR) === -1 &&
                $.inArray('p_tape_device_import', CONF.PERMISSION_ARR) === -1 ) {
                button = '--';
            }
            return button;
        };

        let lastIndex = [-1, -1];

        function groupDetail(index, row, element) {
            if (index != lastIndex[1]) {
                lastIndex.push(index);
                $('#tape_lib_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                lastIndex.splice(0, 1);
            }
            let reserveStrategyType;
            let backupSetStrategyType;
            let html = '';
            switch (row.backup_set_strategy_type) {
                case 0:
                    backupSetStrategyType = 'UNKNOWN';
                    html += '<p>' + LANG.UI_TAPE_GROUP_GENERATE_STRATEGY + '：' + backupSetStrategyType + '</p>';
                    break;
                case 1:
                    backupSetStrategyType = LANG.UI_TAPE_GROUP_GENERATE_STRATEGY1;
                    html += '<p>' + LANG.UI_TAPE_GROUP_GENERATE_STRATEGY + '：' + backupSetStrategyType + '</p>';
                    break;
                case 2:
                    backupSetStrategyType = LANG.UI_TAPE_GROUP_GENERATE_STRATEGY2;
                    html += '<p>' + LANG.UI_TAPE_GROUP_GENERATE_STRATEGY + '：' + backupSetStrategyType + '</p>';
                    break;
                case 3:
                    backupSetStrategyType = LANG.UI_TAPE_GROUP_GENERATE_STRATEGY3;
                    html += '<p>' + LANG.UI_TAPE_GROUP_GENERATE_STRATEGY + '：' + backupSetStrategyType + '， '+LANG.UI_TAPE_GROUP_EVERY+'' + row.backup_set_generated_days + ''+LANG.UI_TAPE_GROUP_DAYS_INTERVAL+'</p>';
                    break;
                default:
                    break;
            };
            switch (row.reserve_strategy_type) {
                case 0:
                    reserveStrategyType = 'UNKNOWN';
                    html += '<p>'+LANG.UI_TAPE_GROUP_RESERVE_STRATEGY+'：' + reserveStrategyType + '</p>';
                    break;
                case 1:
                    reserveStrategyType = LANG.UI_TAPE_GROUP_RESERVE_STRATEGY1;
                    html += '<p>'+LANG.UI_TAPE_GROUP_RESERVE_STRATEGY+'：' + reserveStrategyType + '</p>';
                    break;
                case 2:
                    reserveStrategyType = LANG.UI_TAPE_GROUP_RESERVE_STRATEGY2;
                    html += '<p>'+LANG.UI_TAPE_GROUP_RESERVE_STRATEGY+'：' + reserveStrategyType + '， '+LANG.UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS3_1+'' + row.reserve_days + ''+LANG.UI_PUBLIC_UNIT_DAY+'</p>';
                    break;
                case 3:
                    reserveStrategyType = LANG.UI_TAPE_GROUP_RESERVE_STRATEGY3;
                    html += '<p>'+LANG.UI_TAPE_GROUP_RESERVE_STRATEGY+'：' + reserveStrategyType + '</p>';
                    break;
                default:
                    break;
            };
            $(element).append(html);
        };

        //磁带组操作
        var handleGroup = {
            'click .edit': function (event, value, row, index) {
                modifyGroupUuid = row.group_uuid;
                $('#edit_select_tape').bootstrapTable('destroy');
                $('#edit_add_tape').bootstrapTable('destroy');
                editGroup(event, value, row, index);
               $('#edit_generate_strategy').trigger('change');
                
                // 根据是否有备份集设置是否可修改策略，有备份集不可修改
                $('#edithighconfig .spinner-up').prop('disabled', row.is_backupset);
                $('#edithighconfig .spinner-down').prop('disabled', row.is_backupset);
                $('#edit_generate_strategy').prop('disabled', row.is_backupset);
                $('#edit_generate_days').prop('disabled', row.is_backupset);
                if ($('#edit_generate_strategy').val() != '1') {
                    $('#edit_reserve_strategy').prop('disabled', row.is_backupset);
                    $('#edit_reserve_days').prop('disabled', row.is_backupset);
                }
               // if (row.use_mode == 2) {
                    // 副本只能选择默认的策略
                   // $('#edit_generate_strategy').val('1').prop('disabled', true);
                   // $('#edit_generate_days_div').hide();
                   // $('#edit_reserve_strategy').val('1').prop('disabled', true);
                   // $('#edit_reserve_days_div').hide();
               // } else {
                   if ($('#edit_generate_strategy').val() != 1) {
                        $('#edit_generate_strategy').prop('disabled', false);
                        $('#edit_reserve_strategy').prop('disabled', false);
                    } else {
                        $('#edit_generate_strategy').prop('disabled', false);
                    }
                //}
            },
            'click .delete': function (event, value, row, index) {
                delGroup(row.group_uuid);
            },
            'click .import': function (event, value, row, index) {
                modifyGroupUuid = row.group_uuid;
                $('#selectTape').bootstrapTable('destroy');

                $('#addsubmit').attr('data-action', 'import');
                resetTapeGroupForm();
                importGroup(event, value, row, index);
                $('#generate_strategy').trigger('change');

                $('#tape_group_modal').modal({
                    'width': '800px',
                    'height': '470px'
                });
                initImportTapeGroupModal();
            },
        }

        /**
         * @function 修改磁带组
         * @param {*} event 点击事件
         * @param {*} value 
         * @param {*} row 当前点击的行数据
         * @param {*} index 行序号
         */
        var editGroup = function (event, value, row, index) {
            var mode = row.use_mode;
            $('#edit_tape_group_modal #edit_group_name').val(row.name);
            $('#edit_tape_group_modal #edit_group_des').val(row.description);
            $('#edit_tape_group_modal #edit_generate_strategy').val(row.backup_set_strategy_type);
            if (row.backup_set_strategy_type == 3) {
                $('#edit_tape_group_modal #edit_generate_days_div').show();
                $('#edit_tape_group_modal #edit_generate_days').val(row.backup_set_generated_days);
            } else {
                $('#edit_tape_group_modal #edit_generate_days_div').hide();
                $('#edit_tape_group_modal #edit_generate_days').val(20);
            }

            $('#edit_tape_group_modal #edit_reserve_strategy').val(row.reserve_strategy_type);
            if (row.reserve_strategy_type == 2) {
                $('#edit_tape_group_modal #edit_reserve_days_div').show();
                $('#edit_tape_group_modal #edit_reserve_days').val(row.reserve_days);
            } else {
                $('#edit_tape_group_modal #edit_reserve_days_div').hide();
                $('#edit_tape_group_modal #edit_reserve_days').val(20);
            }

            setWarningSetting(row);

            $('#editbackupCheck').iCheck('uncheck');
            $('#editcopyCheck').iCheck('uncheck');
            switch(mode){
                case 1:
                    $('#editbackupCheck').iCheck('check');
                    break;
                case 2:
                    $('#editcopyCheck').iCheck('check');
                    break;
            }
            $('#editbackupCheck').iCheck('disable'); //修改不可改变存储用途
            $('#editcopyCheck').iCheck('disable');

            $('#edit_tape_group_modal').modal({
                'width': '800px',
                'height': '470px'
            });
            initEditSelectTapeTable(row.group_uuid);
            initEditSelectedTapeTable(row.lib_name);
        }

        /**
         * @function 导入磁带组
         * @param {*} event 点击事件
         * @param {*} value 
         * @param {*} row 当前点击的行数据
         * @param {*} index 行序号
         */
        var importGroup = function (event, value, row, index) {
            $('#tape_group_modal #group_name').val(row.name_value);

            $('#tape_group_modal #generate_strategy').val(row.backup_set_strategy_type);
            
            if (row.backup_set_strategy_type == 3) {
                $('#tape_group_modal #generate_days_div').show();
                $('#tape_group_modal #generate_days').val(row.backup_set_generated_days);
            } else {
                $('#tape_group_modal #generate_days_div').hide();
                $('#tape_group_modal #generate_days').val(20);
            }

            $('#tape_group_modal #reserve_strategy').val(row.reserve_strategy_type);
            if (row.reserve_strategy_type == 2) {
                $('#tape_group_modal #reserve_strategy_days_div').show();
                $('#tape_group_modal #reserve_strategy_days').val(row.reserve_days);
            } else {
                $('#tape_group_modal #reserve_strategy_days_div').hide();
                $('#tape_group_modal #reserve_strategy_days').val(20);
            }

            $('#backupCheck').iCheck('uncheck');
            $('#copyCheck').iCheck('uncheck');
            
            // $('#editbackupCheck').iCheck('disable'); //修改不可改变存储用途
            // $('#editcopyCheck').iCheck('disable');
        }

        /**
         * @function 回填警告设置
         * @param {*} row 行数据
         */
        var setWarningSetting = function (row) {
            $('#edit_tape_group_modal #editnoticeswitch').bootstrapSwitch('state', row.warning_flag);
            var warningType = row.warning_type;
            var warningValue = row.warning_value;
            if(warningType == 0){
                warningType = 1;
                warningValue = 20;
            }
            $('#edit_tape_group_modal select[name=noticetype]').val(warningType);
            
            //控制初始显示
            if(1 == warningType){
                $('#edit_tape_group_modal #editpercentdiv').show();
                $('#edit_tape_group_modal #editsizediv').hide();
                $('#edit_tape_group_modal #editwarningpercent').val(warningValue);
            }else{
                $('#edit_tape_group_modal #editpercentdiv').hide();
                $('#edit_tape_group_modal #editsizediv').show();
                $('#edit_tape_group_modal #editwarningsize').val(warningValue);
            }
            if(!row.warning_flag){
                $('#edit_tape_group_modal .warnningdiv').hide();
            }
        }

        /**
         * @function 删除磁带组
         * @param group_uuid 磁带组uuid
         */
        var delGroup = function (group_uuid) {
            var message = LANG.UI_TAPE_GROUP_DEL_TIPS;
            var initErrorFlag = false;
            bootbox.confirm({
                title: LANG.UI_TAPE_MONITOR_OP_DELETE_TAPE_GROUP,
                message: LANG.UI_TAPE_GROUP_DEL_TIPS,
                callback: debounce(function(r) {
                    if(!r) return;
                    initErrorFlag = false;
                    bootbox.prompt({
                        title: LANG.UI_TAPE_GROUP_DEL_CONFIRM,
                        inputType: 'password',
                        message: message,
                        callback: function (result) {
                            if (result == null) return;
                            if (hex_md5(result) == _UserPassword) {
                                Metronic.blockUI({
                                    target: '#tape_lib_table',
                                    animate: true
                                });
                                pAjaxRequest({
                                    'group_uuid': group_uuid
                                }, '/api/v1/tapes/group', 'DELETE', (res) => {
                                    // Metronic.unblockUI('#current_table');
                                    var op = LANG.UI_TAPE_SEND_DEL_GROUP_MSG;
                                    if (operateResponseList(res, op)) {
                                        Metronic.unblockUI('#tape_lib_table');
                                        $("#tape_lib_table").bootstrapTable('hideLoading');
                                        $("#tape_lib_table").bootstrapTable('refresh');
                                        getTapeGroupNode(groupNode);
                                    } else {
                                        Metronic.unblockUI('#tape_lib_table');
                                    }
                                });
                            } else {
                                $('.bootbox-input').css('border-color', "#a94442");
                                if (!initErrorFlag) {
                                    var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_TAPE_DEL_GROUP_PASSWORD + '</p>';
                                    $('.bootbox-input').after(des);
                                    initErrorFlag = true;
                                }
                                return false;
                            }
                        }
                    });
                }, 300)
            });
        }

        var options = {
            // data: treeNode,
            // sidePagination: "client",
            vin_url: "/api/v1/tapes/group",
            vin_method: "GET",
            vin_params: function () {
                let param = {};
                param.node_uuid = nodeId;
                return param;
            },
            detailView: true,
            detailFormatter: groupDetail,
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            onResetView: initTableHeight,
            PostBody: function (params) {
                // 初始化新建磁带组
                $('#vin_tape_toolbar #addTask').unbind('click').on('click', function (params) {
                    $('#addsubmit').attr('data-action', 'add');
                    $('#tape_group_modal #addTitle').text(LANG.UI_TAPE_ADD_GROUP);
                    $('#select_tape_lib').parent().parent().parent().show();
                    $('#tape_group_modal #selectTapeDiv label.control-label').text(LANG.UI_TAPE_SELECT_GROUP_CARRIAGE);
                    resetTapeGroupForm();
                    initTapeLibSelect();
                    $('#tape_group_modal').modal({
                        'width': '800px',
                        'height': '470px'
                    });
                    // initSelectTapeTable();
                });
                var data = $('#tape_lib_table').bootstrapTable('getData');
                for (let i = 0; i < data.length; i++) {
                    var status = data[i].status;
                    var uuid = data[i].group_uuid;
                    if (status != 2) {
                        // 状态不为2（待导入）时不可以导入磁带组
                        addForbidButton(uuid, 'import');
                    } else {
                        // 状态为2（待导入）时不可以修改和删除磁带组
                        addForbidButton(uuid, 'edit');
                        addForbidButton(uuid, 'delete');
                    }
                }
            },
            customTool: {
                beforeInput: ``,
            },

            columns: [ //列定义
                {
                    field: 'name', //字段名
                    title: LANG.UI_TAPE_GROUP_NAME,
                    formatter: function (value, row, index) {
                        if (row.import_flag) {
                            return `${value}<span style="color:#f19f00">(${LANG.UI_TAPE_WAIT_IMPORT})</span>`;
                        } else {
                            return `<span>${value}</span>`;
                        }
                    }
                },
                {
                    field: 'tape_count',
                    title: LANG.UI_TAPE_COUNT,
                    sortable: false,
                },
                {
                    field: 'total_size',
                    title: LANG.UI_STORAGE_SIZE,
                    sortable: false,
                },
                {
                    field: 'free_size',
                    title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                    sortable: false,
                },
                {
                    field: 'description',
                    title: LANG.UI_PUBLIC_DESCRIPTION
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    opButton: true,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    formatter: optionFormatter,
                    events: handleGroup //磁带组操作
                }
            ]
        }
        sessionStorage.removeItem('tape_lib_table_pageRecord');
        $('#tape_lib_table').bootstrapTable('destroy');
        $('#tape_lib_table').baseTableConfig().init(options);

    }

    /**
     * @function 初始化修改磁带组table（现有磁带）
     */
    function initEditSelectTapeTable(groupUuid) {
        let params = {
            'offset': 0,
            'group_uuid': groupUuid
        };
        let data;
        let tableArea = '#edit_select_tapediv';
        pAjaxRequest(params, '/api/v1/tapes/carriage_info', 'GET', (res) => {
            data = res.data;
            var option = {
                // vin_url: '/api/v1/tapes/carriage_info',
                // vin_method: 'GET',
                data: data,
                pagination: false,
                showExport: false, //是否开启导出按钮
                showColumns: false, //是否开启列选择按钮

                onCheck: function (row) {
                    var index = removeTapeSerialNumberSet.indexOf(row.serial_number); // 查找元素的索引
                    if (index !== -1) {
                        removeTapeSerialNumberSet.splice(index, 1); // 从数组中删除一个元素
                    }
                },
                onUncheck: function (row) {
                    removeTapeSerialNumberSet.push(row.serial_number);
                },
                onCheckAll: function (row) {
                    for (var i = 0; i < row.length; i++) {
                        var index = removeTapeSerialNumberSet.indexOf(row[i].serial_number); // 查找元素的索引
                        if (index != -1) {
                            removeTapeSerialNumberSet.splice(index, 1); // 从数组中删除一个元素
                        }
                    }
                },
                onUncheckAll: function (a, row) {
                    for (var i = 0; i < row.length; i++) {
                        var index = removeTapeSerialNumberSet.indexOf(row[i].serial_number); // 查找元素的索引
                        if (index == -1) {
                            removeTapeSerialNumberSet.push(row[i].serial_number)
                        }
                    }
                },


                columns: [ //列定义
                    {
                        checkbox: true,
                        sortable: false, //默认可排序，禁用排序才写此项
                        formatter: function (index, row) {
                            if (row.backup_set_uuid != '' && row.tape_status != 2) {
                                //关联备份集且不离线的磁带才可以移除
                                return {
                                    disabled: true,
                                    checked:true
                                }
                            }
                            return {
                                disabled: false,// 设置是否可用
                                checked: true // 设置选中
                            };
                        }
                    },
                    {
                        field: 'name', //字段名
                        title: LANG.UI_TAPE_CARRIAGE_NAME,
                        sortable: false, //默认可排序，禁用排序才写此项
                    },
                    {
                        field: 'tape_type',
                        title: LANG.UI_STORAGE_TYPE,
                        formatter: tapeTypeFormatter
                    },
                    {
                        field: 'serial_number',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                        sortable: false,
                    },
                    {
                        field: 'total_size',
                        title: LANG.UI_STORAGE_SIZE,
                    },
                    {
                        field: 'free_size',
                        title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                    },
                    {
                        field: 'tape_status',
                        title: LANG.UI_PUBLIC_STATUS,
                        sortable: false,
                        formatter: statusFormatter
                    }
                ]
            }
            $('#edit_select_tape').baseTableConfig().init(option);
        })
    }

    /**
     * @function 获取磁带库字节点
     * @param treeNode 当前节点
     */
    function getTapeLibNode(treeNode) {
        if (treeNode) {
            let data = {
                'offset': 0,
                'limit': 500,
                'node_uuid': nodeId
            };
            Metronic.blockUI({
                target: '.tree-wrapper',
                animate: true
            });
            pAjaxRequest(data, '/api/v1/tapes', 'GET', (res) => {
                Metronic.unblockUI('.tree-wrapper');

                for (let i = 0; i < res.data.rows.length; i++) {
                    res.data.rows[i].isParent = true;
                    res.data.rows[i].type = 4; //带库实例的type4
                    res.data.rows[i].icon = './img/tape/tape-lib.svg';
                    res.data.rows[i].children = [{
                            "name": LANG.UI_STORAGE_TYPE_TAPE,
                            "id": '3',
                            "type": 5,
                            "isParent": false,
                            "icon": './img/tape/tape-carriage.svg',
                            "open": false
                        },
                        {
                            "name": LANG.UI_TAPE_DRIVER,
                            "id": '4',
                            "type": 6,
                            "isParent": false,
                            "icon": './img/tape/tape-driver.svg',
                            "open": false
                        }
                    ]
                };
                zTree.addNodes(treeNode, res.data.rows);
                initLibTable(res.data);
                initLibFlag = true
            });
        }
    }

    /**
     * @function 获取磁带信息（按磁带库区分）
     * @param treeNode 当前节点
     */
    function getEachTape(treeNode) {
        if (treeNode) {
            var libName = zTree.getNodeByTId(treeNode.parentTId).lib_name; //获取父节点带库名
            var data = {
                'offset': 0,
                'limit': 1000,
                'lib_name': libName
            };
            Metronic.blockUI({
                target: '.tree-wrapper',
                animate: true
            });
            pAjaxRequest(data, '/api/v1/tapes/carriage_info', 'GET', (res) => {
                Metronic.unblockUI('.tree-wrapper');
                // if (initTapeFlag) {
                //     initTapeTable(res.data, libName);
                // } else {
                for (let i = 0; i < res.data.rows.length; i++) {
                    res.data.rows[i].isParent = false;
                    res.data.rows[i].nocheck = true;
                    res.data.rows[i].icon = './img/tape/tape-carriage.svg';
                    res.data.rows[i].type = 7; //磁带实例的type7
                }
                // zTree.addNodes(treeNode, res.data.rows);
                // zTree.expandNode(treeNode, true);
                initTapeTable(res.data, libName);
                initTapeFlag = true;
                // }
            });
        }
    }

    /**
     * @function 磁带状态转义
     * @param status 状态参数
     */
    function statusFormatter(status) {
        switch (status) {
            case 1:
                return '<span class="label label-sm label-success  ">' + LANG.UI_TAPE_STATUS_ONLINE + '</span>'
            case 2:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_OFFLINE + '</span>'
            case 3:
                return '<span class="label label-sm label-danger  ">' + LANG.UI_TAPE_STATUS_MOVING + '</span>'
            case 4:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_READING + '</span>'
            case 5:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_WRITTING + '</span>'
            case 6:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_RETRIEVALING + '</span>'
            case 7:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_WAITING + '</span>'
            case 8:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_REWINDING + '</span>'
            case 9:
                return '<span class="label label-sm label-info  ">' + LANG.UI_TAPE_STATUS_READY + '</span>'
            case 10:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_SCANNING + '</span>'
            case 11:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_EXPORTING + '</span>'
            case 12:
                return '<span class="label label-sm label-default  ">' + LANG.UI_TAPE_STATUS_IMPORTING + '</span>'
            default:
                break;
        }
    }

    /**
     * @function 驱动器状态转义
     * @param status 状态参数
     */
    function driverStatusFormatter(status) {
        switch (status) {
            case 0:
                return '<span class="label label-sm label-default  ">' + LANG.UI_PUBLIC_UNKNOWN + '</span>'
            case 1:
                return '<span class="label label-sm label-info  ">' + LANG.UI_JOB_CROWD_LEISURE + '</span>'
            case 2:
                return '<span class="label label-sm label-danger  ">' + LANG.UI_JOB_CROWD_BUSY + '</span>'
            case 3:
                return '<span class="label label-sm label-success  ">' + LANG.UI_TAPE_STATUS_ONLINE + '</span>'
            default:
                break;
        }
    }

    /**
     * @function 所属驱动器名称+路径
     */
    function driverFormatter(value, row, index) {
        if (row.driver_name == '--') {
            return '----';
        } else {
            var content =  row.driver_name + '(' + row.driver_path + ')';
            return `<span title='${content}'>${content}</span>`;
        }
    }

    //添加禁止点击的按钮样式
    var addForbidButton = function (serialNumber, option) {
        $('#tape_lib_table #' + serialNumber + ' .' + option).unbind();
        $('#tape_lib_table #' + serialNumber + ' .' + option + ' a').css("opacity", ".4");
        $('#tape_lib_table #' + serialNumber + ' .' + option + ' a').css("cursor", "not-allowed");
        $('#tape_lib_table #' + serialNumber).on("click", "." + option + " a", function (e) {
            e.stopPropagation();
        });
    }

    /**
     * @function 初始化磁带table
     * @param treeNode 当前节点
     */
    function initTapeTable(treeNode, libName) {
        $('#addTask').hide();
        $('#delete_backupset').parent().hide();
        //磁带数据检索
        let retrievalData = function (event, value, row, index) {
            let libName = row.lib_name;
            let tapeSerialNumber = row.serial_number;
            Metronic.blockUI({
                target: '.tree-wrapper',
                animate: true
            });
            Metronic.blockUI({
                target: '#tape_lib_table',
                animate: true
            });
            pAjaxRequest({
                'tape_lib_name': libName,
                'tape_serial_number': tapeSerialNumber
            }, '/api/v1/tapes/retrieval', 'POST', function (res) {
                var op = LANG.UI_TAPE_SEND_RETRIEVAL_MSG;
                Metronic.unblockUI('.tree-wrapper');
                Metronic.unblockUI('#tape_lib_table');
                if (operateResponseList(res, op)) {
                    $("#tape_lib_table").bootstrapTable('refresh');
                }
            })
        }
        //弹出磁带
        let exportTape = function (event, value, row, index) {
            let param = {};
            param.op_type = 2
            param.lib_name = row.lib_name;
            param.serial_number = row.serial_number;
            pAjaxRequest(param, '/api/v1/tapes/export', 'POST', function (res) {
                var op = LANG.UI_TAPE_SEND_EXPORT_MSG;
                if (operateResponseList(res, op)) {
                    $("#tape_lib_table").bootstrapTable('refresh');
                }
            });
        }
        //导入磁带
        let importTape = function (event, value, row, index) {
            let param = {};
            param.op_type = 1
            param.lib_name = row.lib_name;
            param.serial_number = row.serial_number;
            pAjaxRequest(param, '/api/v1/tapes/import', 'POST', function (res) {
                var op = LANG.UI_TAPE_SEND_IMPORT_MSG;
                if (operateResponseList(res, op)) {
                    $("#tape_lib_table").bootstrapTable('refresh');
                }
            });
        }
        //磁带操作
        let handleTape = {
            'click .edit': function (event, value, row, index) {
                let tapeName = row.name;
                let tapeDes = row.description;
                tapeId = row.id; //修改磁带的id，给全局变量赋值
                $('#edit_tape_modal').modal({
                    'width': '693px',
                    'height': '157px'
                });
                $('#edit_tape_name').val(tapeName);
                $('#edit_tape_des').val(tapeDes);
            },
            'click .retrieval': function (event, value, row, index) {
                retrievalData(event, value, row, index);
            },
            'click .export': function (event, value, row, index) {
                exportTape(event, value, row, index);
            },
            'click .import': function (event, value, row, index) {
                importTape(event, value, row, index);
            },
        }

        let options = {
            // data: treeNode,
            vin_url: "/api/v1/tapes/carriage_info",
            vin_method: 'GET',
            vin_params: function () {
                let param = {};
                if ($('#vin_tape_toolbar .tapeSearch').val()) {
                    param.search = $('#vin_tape_toolbar .tapeSearch').val();
                }
                param.lib_name = libName;
                return param;
            },
            toolbarId: '#vin_tape_toolbar',
            searchInput: true, //搜索框
            placeholder: LANG.UI_TAPE_SEARCH_AS_TAPE_NUM,
            searchClass: 'tapeSearch',
            searchSelector: '.tapeSearch',
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            onResetView: initTableHeight,
            onPostBody: function () {
                var data = $('#tape_lib_table').bootstrapTable('getData');
                for (let i = 0; i < data.length; i++) {
                    var serialNumber = data[i].serial_number;
                    var status = data[i].tape_status;
                    if (status == 1) { //在线的磁带不可导入导出
                        addForbidButton(serialNumber, 'export');
                        addForbidButton(serialNumber, 'import');
                    }
                    if (status == 9) { //就绪状态，只能导出
                        addForbidButton(serialNumber, 'import');
                    }
                    if (status == 2) { //状态，只能导出
                        addForbidButton(serialNumber, 'export');
                    }
                    if (status != 2 && status != 9) { //在线和就绪的磁带才能检索
                        addForbidButton(serialNumber, 'retrieval');
                    }
                }
                $('.search-btn').off().on('click', ()=>{
                    $("#tape_lib_table").bootstrapTable('refresh');
                })
            },
            columns: [ //列定义
                {
                    field: 'name', //字段名
                    title: LANG.UI_TAPE_CARRIAGE_NAME,
                    // type: "href", //列的自定义type属性,值包括"href","label","operation",返回不同的模板
                },
                {
                    field: 'serial_number',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                },
                {
                    field: 'tape_type',
                    title: LANG.UI_TAPE_CARRIAGE_TYPE,
                    formatter: tapeTypeFormatter
                },
                {
                    field: 'total_size',
                    title: LANG.UI_STORAGE_SIZE,
                    // formatter: storageFormatter
                },
                {
                    field: 'free_size',
                    title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                    // formatter: storageFormatter
                },
                {
                    field: 'tape_status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: statusFormatter
                },
                {
                    field: 'group_name',
                    title: LANG.UI_TAPE_OWNING_TAPE_GROUP,
                    sortable: false,
                },
                {
                    field: 'backup_set_name',
                    title: LANG.UI_TAPE_OWN_BACKUP_SET,
                    sortable: false,
                    formatter: function (index, row) {
                        if (row.backup_set_name == '') {
                            return '--'
                        } else {
                            return `<span title=${row.backup_set_name}>${row.backup_set_name}</span>`
                        }
                    },
                },
                {
                    field: 'driver_name',
                    title: LANG.UI_TAPE_OWN_DRIVER,
                    sortable: false,
                    formatter: driverFormatter
                },
                {
                    field: 'description',
                    sortable: false,
                    title: LANG.UI_PUBLIC_DESCRIPTION
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    opButton: true,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    events: handleTape,
                    formatter: (value, row, index, field) => {
                        var button = '<div class="btn-group">';
                        if (index > 5) {
                            button = '<div class="btn-group dropup">';
                        }
                        button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                            'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                            '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                            '</button>' +
                            '<ul class="dropdown-menu min-width100" role="menu"  id="' + row.serial_number + '">';
                        if ($.inArray('p_tape_device_edit', CONF.PERMISSION_ARR) !== -1) {
                            button += '<li class="edit"><a href="javascript:;" ><i class="viconfont vicon-a-Editbianji1"></i>'+LANG.UI_PUBLIC_EDIT+'</a></li>';
                        }
                        if ($.inArray('p_tape_device_retrieval', CONF.PERMISSION_ARR) !== -1) {
                            button += '<li class="retrieval"><a href="javascript:;" ><i class="viconfont vicon-a-Find-onesoucha"></i>'+LANG.UI_TAPE_CARRIAGE_RETRIEVAL+'</a></li>';
                        }
                        if ($.inArray('p_tape_device_export', CONF.PERMISSION_ARR) !== -1) {
                            button += '<li class="export"><a href="javascript:;" ><i class="viconfont vicon-a-Arrow-circle-upshang-jiantou"></i>'+LANG.UI_TAPE_CARRIAGE_EXPORT+'</a></li>';
                        }
                        if ($.inArray('p_tape_device_import', CONF.PERMISSION_ARR) !== -1) {
                            button += '<li class="import"><a href="javascript:;" ><i class="viconfont vicon-a-Afferent-threechuanru3"></i>'+LANG.UI_TAPE_CARRIAGE_IMPORT+'</a></li>';
                        }

                        button += '</ul></div>';
                        if (
                            $.inArray('p_tape_device_edit', CONF.PERMISSION_ARR) == -1 &&
                            $.inArray('p_tape_device_retrieval', CONF.PERMISSION_ARR) == -1 &&
                            $.inArray('p_tape_device_export', CONF.PERMISSION_ARR) == -1 &&
                            $.inArray('p_tape_device_import', CONF.PERMISSION_ARR) == -1
                        ) {
                            button = '';
                        }

                        return button;
                    }
                }
            ],
        };
        sessionStorage.removeItem('tape_lib_table_pageRecord');
        //$('#tape_lib_table').bootstrapTable('destroy');
        $('#tape_lib_table').baseTableConfig().init(options);
    }

    /**
     * @function 初始化磁带组中磁带信息
     * @param treeNode 当前节点
     */
    function getEachGroupTape(treeNode) {
        let parent = zTree.getNodeByTId(treeNode.parentTId);

        if (treeNode) {
            let params = {
                'offset': 0,
                'limit': 1000,
                'group_uuid': parent.group_uuid
            }
            Metronic.blockUI({
                target: '.tree-wrapper',
                animate: true
            });
            pAjaxRequest(params, '/api/v1/tapes/carriage_info', 'GET', (res) => {
                Metronic.unblockUI('.tree-wrapper');

                for (let i = 0; i < res.data.rows.length; i++) {
                    res.data.rows[i].isParent = false;
                    res.data.rows[i].icon = './img/tape/tape-carriage.svg';
                    res.data.rows[i].nocheck = true;
                    res.data.rows[i].type = 7; //磁带实例的type7
                }
                initGroupTapeTable(res.data);
            });
        };
    }

    /**
     * @function 初始化组中磁带列表
     * @param treeNode 当前节点
     */
    function initGroupTapeTable(treeNode) {
        $('#addTask').hide();
        $('#delete_backupset').parent().hide();
        let options = {
            data: treeNode,
            sidePagination: "client",
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            onResetView: initTableHeight,
            columns: [ //列定义
                {
                    field: 'name', //字段名
                    title: LANG.UI_TAPE_CARRIAGE_NAME,
                    // type: "href", //列的自定义type属性,值包括"href","label","operation",返回不同的模板
                },
                {
                    field: 'serial_number',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                },
                {
                    field: 'total_size',
                    title: LANG.UI_STORAGE_SIZE,
                    // formatter: storageFormatter
                },
                {
                    field: 'free_size',
                    title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
                    // formatter: storageFormatter
                },
                {
                    field: 'tape_status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: statusFormatter
                },
                {
                    field: 'group_name',
                    title: LANG.UI_TAPE_OWNING_TAPE_GROUP
                },
                {
                    field: 'backup_set_name',
                    title: LANG.UI_TAPE_OWN_BACKUP_SET
                },
                {
                    field: 'driver_name',
                    title: LANG.UI_TAPE_OWN_DRIVER,
                    formatter: driverFormatter
                },
                {
                    field: 'description',
                    title: LANG.UI_PUBLIC_DESCRIPTION
                }
            ],
        };
        sessionStorage.removeItem('tape_lib_table_pageRecord');
        $('#tape_lib_table').bootstrapTable('destroy');
        $('#tape_lib_table').baseTableConfig().init(options);
    }

    /**
     * @function 获取驱动器信息
     * @param treeNode 当前节点
     */
    function getEachDriver(treeNode) {

        if (treeNode) {
            var libName = zTree.getNodeByTId(treeNode.parentTId).lib_name; //获取父节点带库名
            var data = {
                'offset': 0,
                'lib_name': libName
            };
            Metronic.blockUI({
                target: '.tree-wrapper',
                animate: true
            });
            pAjaxRequest(data, '/api/v1/tapes/driver_info', 'GET', (res) => {
                Metronic.unblockUI('.tree-wrapper');
                // if (initDriverFlag) {
                //     initDriverTable(res.data, libName);
                //     return;
                // } else {
                for (let i = 0; i < res.data.rows.length; i++) {
                    res.data.rows[i].isParent = false;
                    res.data.rows[i].icon = './img/tape/tape-driver.svg';
                    res.data.rows[i].type = 8; //驱动器实例的type8
                }
                // zTree.addNodes(treeNode, res.data.rows);
                initDriverTable(res.data, libName);
                // }
            });
        }
    }

    /**
     * @function 初始化驱动器table
     * @param treeNode 当前节点
     */
    function initDriverTable(treeNode, libName) {
        $('#addTask').hide();
        $('#delete_backupset').parent().hide();
        let options = {
            // data: treeNode,
            // sidePagination: "client",
            vin_url: '/api/v1/tapes/driver_info',
            vin_method: "GET",
            vin_params: function () {
                let param = {};
                param.lib_name = libName;
                return param;
            },
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            onResetView: initTableHeight,
            // customTool: {
            //     beforeInput: `<button class="icon-gray-delete b-btn brr2 mr12" style="background-color:#F4F4F5" id="delete_backupset"></button>`
            // },
            columns: [ //列定义
                {
                    field: 'name', //字段名
                    title: LANG.UI_TAPE_DRIVER_NAME,
                    // type: "href", //列的自定义type属性,值包括"href","label","operation",返回不同的模板
                },
                {
                    field: 'firmware',
                    title: LANG.UI_TAPE_FIRMWARE,
                },
                {
                    field: 'status',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: driverStatusFormatter
                },
                {
                    field: 'load_tape_number',
                    title: LANG.UI_TAPE_LOADING_CARRIAGE,
                }
            ],
        };
        sessionStorage.removeItem('tape_lib_table_pageRecord');
        $('#tape_lib_table').bootstrapTable('destroy');
        $('#tape_lib_table').baseTableConfig().init(options);
    }

    /**
     * @function 获取备份集信息
     * @param treeNode 当前节点
     */
    function getTapeBackupSet(treeNode) {
        if (treeNode) {
            var groupUuid = zTree.getNodeByTId(treeNode.parentTId).group_uuid;
            initBackupSetTable(treeNode, groupUuid);
            return;
        }
    }

    /**
     * 操作磁带
     * */
    function opBackupSet(type = 'freeze', backupSetUuid, showTips = false){
        var msg ='<span style="color:#f79915">' +  LANG.UI_TAPE_DATA_SURE_UNFREEZE_BACKUP;
        if (type == 'defrost') {
            msg ='<span style="color:#f79915">' +  LANG.UI_TAPE_DATA_SURE_UNFREEZE_BACKUP,LANG.UI_TAPE_DATA_FROZEN_BACKUP_CANNOT_DELETED_RETENTION;
        }

        if (showTips) {
            msg = LANG.UI_TAPE_DATA_SURE_UNFREEZE_IMMEDIATELY_DELETE_BACKUP;
        }
        bootbox.confirm({
            title:'<i class="viconfont vicon-wuxiaoshijian me-8" style="color:#f79915"></i>' +
                '<span style="color:#f79915">' + LANG.UI_TAPE_DATA_OPERATION_PROMPT + '</span>',
            message: msg,
            callback: (function(r) {
                if(!r) return;
                Metronic.blockUI({target: '#tape_lib_table',animate: true});
                var data = {};
                data.backup_set_uuid = backupSetUuid;
                data.type = type;
                pAjaxRequest(data, "/api/v1/tapes/backup_set", "POST", function (result) {
                    Metronic.unblockUI('#tape_lib_table');
                    if (result.code == 0 || result.code == 200) {
                        $("#tape_lib_table").bootstrapTable('refresh');
                        UIToastr.showSuccess(LANG.UI_TAPE_DATA_OPERATION_PROMPT, result.message);
                    } else {
                        UIToastr.showWarning(LANG.UI_TAPE_DATA_OPERATION_PROMPT, result.message);
                    }
                });
            })
        });
    }

    /**
     * @function 初始化备份集table
     * @param data 节点数据
     */
    function initBackupSetTable(treeNode, groupUuid) {
        $('#addTask').hide();
        $('#delete_backupset').parent().show();
        var handleBackupSet = {
            'click .view': function (event, value, row, index) {
                $('#time_point_modal').modal({
                    'width': '693px',
                    'height': '400px'
                });
                // setBackupSetTree(row.time_point, row.name, row.time_point.db_type_info);
                setBackupSetTree(row.backup_set_uuid, row.name);
            },
            'click .edit': function (event, value, row, index) {
                let backupSetName = row.name;
                $('#edit_backup_set_modal').modal({
                    'width': '693px',
                    'height': '87px'
                });
                $('#edit_backup_set_name').val(backupSetName);

                $('#edit_backup_set_modal #backup_set_submit').off().on('click', () => {
                    editBackupSet(row.backup_set_uuid);
                })
            },
            'click .defrost': function (event, value, row, index) {
                let backupSetUuid = row.backup_set_uuid;
                // 冻结
                opBackupSet('defrost', backupSetUuid);
            },
            'click .freeze': function (event, value, row, index) {
                let backupSetUuid = row.backup_set_uuid;
                // 解冻
                // 判断磁带组保留策略是否是按天，如果是按天，在判断是否已经过期
                var showTips = false;
                if (row.reserve_strategy_type == 2 && row.expired_flag) {
                    // 给出提示  解冻后将会立即删除备份集
                    showTips = true;
                }
                opBackupSet('freeze', backupSetUuid, showTips);
            }
        }

        let options = {
            // data: treeNode,
            // sidePagination: "client",
            toolbarId: '#vin_tape_toolbar',
            buttonsToolbar: '#tape_lib .vin_btnToolbar',
            vin_url: "/api/v1/tapes/group/backup_set",
            vin_method: "GET",
            vin_params: function () {
                return {
                    "group_uuid": groupUuid
                };
            },
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            onResetView: initTableHeight,
            customTool: {
                beforeInput: ``
            },
            onCheck: function (row) {
                backupSetData.push(row.backup_set_uuid);
                modifyDelStyle('tape_lib_table', 'delete_backupset');
            },
            onUncheck: function (row) {
                var index = backupSetData.indexOf(row.backup_set_uuid); // 查找元素的索引
                if (index !== -1) {
                    backupSetData.splice(index, 1); // 从数组中删除一个元素
                }
                modifyDelStyle('tape_lib_table', 'delete_backupset');
            },
            onCheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = backupSetData.indexOf(row[i].backup_set_uuid); // 查找元素的索引
                    if (index == -1) {
                        backupSetData.push(row[i].backup_set_uuid)
                    }
                }
                modifyDelStyle('tape_lib_table', 'delete_backupset');
            },
            onUncheckAll: function (row) {
                for (var i = 0; i < row.length; i++) {
                    var index = backupSetData.indexOf(row[i].backup_set_uuid); // 查找元素的索引
                    if (index != -1) {
                        backupSetData.splice(index, 1); // 从数组中删除一个元素
                    }
                }
                modifyDelStyle('tape_lib_table', 'delete_backupset');
            },
            PostBody: function () {
                $('#vin_tape_toolbar').off().on('click', '#delete_backupset', delBackupSet);
                $('#tape_lib_table th[data-field="6"]').css('width', '6%');
                $('#tape_lib_table th[data-field="last_write_time"]').css('width', '18%');
                $('#tape_lib_table th[data-field="generated_time"]').css('width', '12%');
                $('#tape_lib_table th[data-field="valid_start_time"]').css('width', '12%');
                $('#tape_lib_table th[data-field="group_name"]').css('width', '15%');
                $('#tape_lib_table th[data-field="name"]').css('width', '19%');
                $('#tape_lib_table th[data-field="0"]').css('width', '3%');

                $('#addTask').hide();
                $('#delete_backupset').parent().show();
            },
            columns: [ //列定义
                {
                    checkbox: true,
                    sortable: false, //默认可排序，禁用排序才写此项
                },
                {
                    field: 'name', //字段名
                    title: LANG.UI_TAPE_BACKUP_SET_NAME,
                    sortable: false,
                },
                {
                    field: 'generated_time',
                    title: LANG.UI_TAPE_GENERATE_TIME,
                },
                {
                    field: 'valid_start_time',
                    title: LANG.UI_TAPE_VALIDATE_START_TIME,
                    sortable: false,
                },
                {
                    field: 'last_write_time',
                    title: LANG.UI_TAPE_LAST_WRITE_TIME,
                    formatter: function (value, row, index, field) {
                        if (row.expired_flag) {
                            return `<span title='${value}(${LANG.UI_TAPE_BACKUPSET_TIP})'>${value}(${LANG.UI_TAPE_BACKUPSET_TIP})</span>`;
                        } else {
                            return `<span title='${value}'>${value}</span>`;
                        }
                    }
                },
                {
                    field: 'group_name',
                    title: LANG.UI_TAPE_OWNING_TAPE_GROUP
                },
                {
                    field: 'freeze_flag',
                    title: LANG.UI_PUBLIC_STATUS,
                    formatter: function (value, row, index, field) {
                        if (row.freeze_flag == 0) {
                            return `<span class="label label-success"> ` + LANG.UI_PLATFORM_DES_NORMAL + `</span>`;
                        } else {
                            return `<span class="label label-warning"> ` + LANG.UI_TAPE_BACKUP_SET_STATUS_FREEZE + `</span>`;
                        }
                    }
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    opButton: true,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    events: handleBackupSet,
                    formatter: (value, row, index, field) => {
                        var button = '<div class="btn-group">';
                        if (index > 5) {
                            button = '<div class="btn-group dropup">';
                        }
                        button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                            'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                            '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                            '</button>' +
                            '<ul class="dropdown-menu min-width100" role="menu"  id="' + row.backup_set_uuid + '">';
                        button += '<li class="view"><a href="javascript:;" ><i class="viconfont viconfont vicon-a-Eyesyanjing"></i>'+LANG.UI_PUBLIC_LOOK+'</a></li>';
                        if ($.inArray('p_tape_device_edit', CONF.PERMISSION_ARR) !== -1) {
                            button += '<li class="edit"><a href="javascript:;" ><i class="viconfont vicon-a-Editbianji1"></i>'+LANG.UI_PUBLIC_EDIT+'</a></li>';
                        }
                        if ($.inArray('p_tape_device_freeze', CONF.PERMISSION_ARR) !== -1 && row.freeze_flag == 0) {
                            // 冻结
                            button += '<li class="defrost"><a href="javascript:;" ><i class="viconfont vicon-ge_lock"></i>' + LANG.UI_TAPE_BACKUP_SET_STATUS_FREEZE + '</a></li>';
                        }
                        if ($.inArray('p_tape_device_defrost', CONF.PERMISSION_ARR) !== -1 && row.freeze_flag == 1) {
                            // 解冻
                            button += '<li class="freeze"><a href="javascript:;" ><i class="viconfont vicon-ge_unlock"></i>' + LANG.UI_TAPE_BACKUP_SET_STATUS_DEFROST + '</a></li>';
                        }
                        button += '</ul></div>';
                        return button;
                    }
                }
            ]
        };
        sessionStorage.removeItem('tape_lib_table_pageRecord');
        $('#tape_lib_table').bootstrapTable('destroy');
        $('#tape_lib_table').baseTableConfig().init(options);
    }

    function editBackupSet(backupSetUuid) {
        let backupSetNewName = $.trim($('#edit_backup_set_name').val());
        Metronic.blockUI({
            target: '.tree-wrapper',
            animate: true
        });
        pAjaxRequest({
            "backup_set_uuid": backupSetUuid,
            "backup_set_new_name": backupSetNewName
        }, '/api/v1/tapes/group/backup_set', 'PUT', function (data) {
            Metronic.unblockUI('.tree-wrapper');
            var op = 'UI_TAPE_SEND_MODIFY_BACKUP_SET_MSG';
            if (operateResponseList(data, op)) {
                $("#tape_lib_table").bootstrapTable('refresh');
                initTapeTree();
            }
            $('#edit_backup_set_modal').modal('hide');
        });
    }

    /**
     * @function 初始化磁带树
     */
    function initTapeTree() {
        Metronic.blockUI({
            target: '.tree-wrapper',
            animate: true
        });
        var data = {
            'offset': 0,
            'limit': 10
        }
        pAjaxRequest(data, '/api/v1/tapes', 'GET', (res) => {
            Metronic.unblockUI('.tree-wrapper');
            if (res.data.rows.length === 0) {
                $('#notapetips').show();
                initTapeTreeFlag = false;
            } else {
                $('#notapetips').hide();
                setTapeTree();
                initTapeTreeFlag = true;
            }
        });
    }

    /**
     * @function 初始化磁带库table
     */
    function initLibTable(treeNode) {
        $('#addTask').hide();
        $('#delete_backupset').parent().hide();
        function optionFormatter(value, row, index, field) {
            var button = '<div class="btn-group">';
            if (index > 5) {
                button = '<div class="btn-group dropup">';
            }
            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu">';
            if ($.inArray('p_tape_device_scan', CONF.PERMISSION_ARR) !== -1) {
                button += '<li class="scan"><a href="javascript:;" ><i class="viconfont vicon-a-Scanning-twosaomiao me-4"></i>'+LANG.UI_TAPE_SCAN+'</a></li>';
            }
            if ($.inArray('p_tape_device_retrieval', CONF.PERMISSION_ARR) !== -1) {
                button += '<li class="retrieval"><a href="javascript:;" ><i class="viconfont vicon-a-Find-onesoucha me-4"></i>'+LANG.UI_TAPE_CARRIAGE_RETRIEVAL+'</a></li>';
            }

            button += '</ul></div>';
            if ($.inArray('p_tape_device_scan', CONF.PERMISSION_ARR) == -1 && $.inArray('p_tape_device_retrieval', CONF.PERMISSION_ARR) == -1) {
                button = '';
            }
            return button;
        }

        //磁带库数据检索
        var retrievalData = function (event, value, row, index) {
            let libName = row.lib_name;
            let tapeSerialNumber = ''; // 检索带库时磁带序列号为空
            Metronic.blockUI({
                target: '.tree-wrapper',
                animate: true
            });
            Metronic.blockUI({
                target: '#tape_lib_table',
                animate: true
            });
            pAjaxRequest({
                'tape_lib_name': libName,
                'tape_serial_number': tapeSerialNumber
            }, '/api/v1/tapes/retrieval', 'POST', function (res) {
                var op = LANG.UI_TAPE_SEND_RETRIEVAL_MSG;
                Metronic.unblockUI('.tree-wrapper');
                Metronic.unblockUI('#tape_lib_table');
                if (operateResponseList(res, op)) {
                    $("#tape_lib_table").bootstrapTable('refresh');
                }
            })
        }

        //磁带库操作
        var handleLib = {
            'click .scan': function (event, value, row, index) {
                scanLib(row.lib_name);
            },
            'click .retrieval': function (event, value, row, index) {
                retrievalData(event, value, row, index);
            }
        }

        var options = {
            // data: treeNode,
            // sidePagination: "client",
            vin_url: "/api/v1/tapes",
            vin_method: "GET",
            vin_params: function () {
                let param = {};
                param.node_uuid = nodeId;
                return param;
            },
            showExport: false, //是否开启导出按钮
            showColumns: false, //是否开启列选择按钮
            onResetView: initTableHeight,
            columns: [ //列定义
                // {
                //     checkbox: true,
                //     sortable: false, //默认可排序，禁用排序才写此项
                // },
                {
                    field: 'name', //字段名
                    title: LANG.UI_TAPE_LIB_NAME,
                    // formatter: libFormatter //暂时不用，从后端返回
                },
                {
                    field: 'lib_firmware',
                    title: LANG.UI_TAPE_FIRMWARE
                },
                {
                    field: 'slot_num',
                    title: LANG.UI_TAPE_SLOT_NUM,
                },
                {
                    field: 'ie_slot_num',
                    title: LANG.UI_TAPE_IE_SLOT_NUM,
                },
                {
                    field: 'vendor',
                    title: LANG.UI_TAPE_LIB_VENDOR
                },
                {
                    title: LANG.UI_PUBLIC_OPERATION,
                    opButton: true,
                    sortable: false,
                    clickToSelect: false, //不可通过点击行选中
                    events: handleLib,
                    formatter: optionFormatter
                }
            ],
        }
        sessionStorage.removeItem('tape_lib_table_pageRecord');
        $('#tape_lib_table').bootstrapTable('destroy');
        $('#tape_lib_table').baseTableConfig().init(options);
    }

    /**
     * @function 高度适配
     */
    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;
        height = panelH - 40 - 48 - 20 - 51 - 46 - 40.5 - 52 - 80;
        //拿到提示框高度
        if ($(document).find('.alert').is(':visible')) {
            var tipHeight = $('.alert:visible').outerHeight();
            height = height - tipHeight;
        }
        //计算表格container该设置的高度
        var container = $("#tape_lib .fixed-table-body").css({
            "height": height
        });
    }

    /**
     * @function 磁带类型转换
     */
    function tapeTypeFormatter(type) {
        switch (type) {
            case 0:
                return 'UNKNOWN';
            case 1:
                return 'LTO1';
            case 2:
                return 'LTO2';
            case 3:
                return 'LTO3';
            case 4:
                return 'LTO4';
            case 5:
                return 'LTO5';
            case 6:
                return 'LTO6';
            case 7:
                return 'LTO7';
            case 8:
                return 'LTO8';
            case 9:
                return 'LTO9';
            case 50:
                return 'LTO7_8';
            case 100:
                return LANG.UI_TAPE_TYPE_CLEAN;
            case 103:
                return 'WORM3';
            case 104:
                return 'WORM4';
            case 105:
                return 'WORM5';
            case 106:
                return 'WORM6';
            case 107:
                return 'WORM7';
            case 108:
                return 'WORM8';
            case 109:
                return 'WORM9';
            case 150:
                return 'WORM_3592+';
            case 151:
                return 'WORM_3592E05+';
            case 152:
                return 'WORM_3592E07+';
            case 200:
                return 'T10000';
            case 250:
                return '9840A';
            case 251:
                return '9840B';
            case 252:
                return '9840C';
            case 253:
                return '9840D';
            case 254:
                return '9940A';
            case 255:
                return '9940B';
            case 300:
                return '3592+';
            case 301:
                return '3592E05+';
            case 302:
                return '3592E06+';
            case 303:
                return '3592E07+';
            case 350:
                return 'DLT7';
            case 400:
                return 'AIT4';
            default:
                break;
        }
    }

    /**
     * @function 节点选择
     */
    //初始化所有备份节点
    function initNodeSelect() {
        pAjaxRequest({
            'offset': 0,
            'limit': 5
        }, '/api/v1/nodes', 'GET', function (d) {
            var data = d;
            var nodeSelect = $('#nodeselect');
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

    function updateScanInfo () {
        clearTimeout(timerTask.scanInfoTimer); // 清除之前的定时器（如果有的话）
        pAjaxRequest({'refresh_flag': true}, '/api/v1/tapes/monitor', 'GET', (res)=>{
            statusHandle(res);
            if (!initTapeTreeFlag && res.data.status == 2) {
                // 刷新扫描信息后如果是成功并且还未初始化磁带树，则初始化磁带树
                initTapeTree();
            }
            timerTask.scanInfoTimer = setTimeout(updateScanInfo, 5000);
        });
    }

    return {
        init: function () {
            initTapeTree();
            initNodeSelect();
            addListeners();
            updateScanInfo(); //定时刷新扫描状态以及信息
            getUserPassword();
        }
    }
}();

$(document).ready(function () {
    TapeEquipment.init();
});