var ListAjax = function (){
    let list_type = 2; //黑名单
    let permanent_access_flag = false;  // 默认是没有打开永久时效开关
    let modifyUuid = '';  // 修改的uuid
    const listValidate = () => {
        $('#listForm').validate({
            errorElement: 'span',
            rules: {
                start_ip: {
                    required: true,
                    startIpRules: true,
                },
                end_ip: {
                    required: true,
                    endIpRules: true,
                    IpCheck: true,
                    compareIp: true,
                },
                validDateRangePicker: {
                    dateRangePickerSelect: true
                },
            },

            messages: {
                start_ip: {
                    required: LANG.UI_BLACK_WHITE_INPUT_START_IP
                },
                end_ip: {
                    required: LANG.UI_BLACK_WHITE_INPUT_END_IP,
                },
            },
            //  自定义错误信息的位置
            errorPlacement:(error,element)=>{
                // error:错误信息元素
                // element:触发错误的元素
                let errorContainer = element.closest('.form-group').find('.error-div');
                error.appendTo(errorContainer);
                error.css('color', '#F1416C');
            }
        });

        // IP格式自定义验证方法
        $.validator.addMethod("startIpRules", (value, element) => {
            let startIpVal = $(element).val().trim();

            // 需要起始ip和结束ip的格式一致
            // 验证IPv4格式或IPv6格式
            const isValidIp = ipv4Regex(startIpVal) || ipv6Regex(startIpVal);
            return isValidIp;
        }, LANG.UI_BLACK_WHITE_INPUT_CORRECT_START_IP);

        $.validator.addMethod("endIpRules", (value, element) => {
            let startIPVal = $('input[name=start_ip]').val();
            let endIpVal = $(element).val();

            // 需要起始ip和结束ip的格式一致
            // 验证IPv4格式或IPv6格式
            const isValidIp = (ipv4Regex(startIPVal) && ipv4Regex(endIpVal)) || (ipv6Regex(startIPVal) && ipv6Regex(endIpVal));
            return isValidIp;
        },LANG.UI_BLACK_WHITE_INPUT_CORRECT_END_IP);

        // 对结束IP的格式要求
        // 结束IP须等于或者大于起始IP并且IP连续
        $.validator.addMethod("IpCheck", (value, element)=>{
            let startIPVal = $('input[name=start_ip]').val();
            let endIpVal = $(element).val();
            return checkIPV4Range(startIPVal,endIpVal) || checkIPV6Range(startIPVal,endIpVal);

        },LANG.UI_BLACK_WHITE_INPUT_IP_TIPS1);

        // 起始IP和结束IP不能和之前已经添加过的IP重复
        $.validator.addMethod("compareIp", (value, element) => {
            var modify_uuid = '';
            if(!!modifyUuid){
                modify_uuid = modifyUuid;
            }
            let data = {};
            data.start_ip = $('input[name=start_ip]').val();
            data.end_ip = $(element).val();
            data.modify_uuid = modify_uuid;
            let res = false;
            pAjaxRequest(data,'/api/v1/system/wblist/compare','GET',(d)=>{
                res = d.success;
            },async = false);
            return res;
        },LANG.UI_BLACK_WHITE_INPUT_IP_TIPS2);

        // 非永久时效时需要选择时间范围
        $.validator.addMethod("dateRangePickerSelect", (value, element) => {
            if(!permanent_access_flag){
                let dateRangePickerVal = $('#validDateRangePicker').val() !== '' ? true :false;
                return dateRangePickerVal;
            }
        },LANG.UI_BLACK_WHITE_INPUT_VALID_TIME);
    };


    const initListener = () => {
        $('#black_list').off().on('click',()=>{
            list_type = 2;
            initDataTable(list_type);
            $('.IPSearch').val('');
        });
        $('#white_list').off().on('click',()=>{
            list_type = 1;
            initDataTable(list_type);
            $('.IPSearch').val('');
        });

        // 禁用名单（批量）
        $('#lock').off().on('click', function (){
            if (checkAuth('lock')) {
                checkOperateAuth(checkAuth('lock'), lockList)
            }
        });
        // 启用名单（批量）
        $('#unlock').off().on('click',function (){
            if (checkAuth('unlock')) {
                checkOperateAuth(checkAuth('unlock'), unlockList)
            }
        })

        // 批量删除（批量）
        $('#delete').off().on('click',function (){
            if (checkAuth('delete')) {
                checkOperateAuth(checkAuth('delete'), deleteList)
            }
        })

        // 修改
        $('#modify').off().on('click',function (){
            if (checkAuth('edit')) {
                checkOperateAuth(checkAuth('edit'), editList)
            }
        })

        // 添加名单
        $('#add').off().on('click',function (){
            clickEffect(this);
            $('#list_detail').drawer('toggle');
            $('#titleDes').html(list_type == 2 ? LANG.UI_BLACK_WHITE_ADD_BLACK_LIST:LANG.UI_BLACK_WHITE_ADD_WHITE_LIST);
            if(list_type != 2){
                // 移除类
                $('.iconList').removeClass('vicon-a-Wrong-usercuowuyonghu');

                // 添加类
                $('.iconList').addClass('vicon-a-Right-userzhengqueyonghu');

            }else if(list_type == 2){
                // 移除类
                $('.iconList').removeClass('vicon-a-Wrong-usercuowuyonghu');
                $('.iconList').removeClass('vicon-a-Right-userzhengqueyonghu');

                // 添加类
                $('.iconList').addClass('vicon-a-Wrong-usercuowuyonghu');
            }
            $('#modifysubmit').hide();
            $('#addsubmit').show();
            $('input[name = start_ip]').attr('readonly',false);
            $('input[name = end_ip]').attr('readonly',false);
            // 清楚所有记录
            clearAllRecords();
        });

        // 点击确定按钮
        $('#addsubmit').off().on('click',(e)=>{
            e.preventDefault();
            if($('#listForm').valid()){
                addList();
            }else{
                // 阻止drawer的默认关闭行为
                return false;
            }
        });

        // 起始IP和结束IP联动
        $('input[name = start_ip]').off().on('input',()=>{
            let inputStartIP = document.getElementById('start_ip');
            let inputEndIP = document.getElementById('end_ip');
            inputStartIP.addEventListener('input', function() {
                // 设置第二个输入框的值为第一个输入框的值
                inputEndIP.value = inputStartIP.value;
            });

            $('input[name = end_ip]').value = $('input[name = start_ip]').value;
        });

        // 永久时效开关
        $('#permanentTimeCheck').bootstrapSwitch('onSwitchChange',(e,state)=>{
            if(state){
                permanent_access_flag = true;
                $('.selecTimeDiv').hide();
            }else{
                permanent_access_flag = false;
                $('.selecTimeDiv').show();
            }
        });
    };

    // 禁用名单（批量）
    var lockList = function (){
        clickEffect(this);
        let selectedRows = $('#black_list_table').bootstrapTable('getSelections');
        if(selectedRows.length == 0){
            if(list_type == 2){
                return UIToastr.showInfo(LANG.UI_BLACK_WHITE_DISABLE_BLACK_LIST, LANG.UI_BLACK_WHITE_DISABLE_LIST_TIPS);
            }else{
                return UIToastr.showInfo(LANG.UI_BLACK_WHITE_DISABLE_WHITE_LIST, LANG.UI_BLACK_WHITE_DISABLE_WHITE_LIST_TIPS);
            }

        }
        let data = {};
        data.list_uuids =[];
        selectedRows.forEach((item,index)=>{
            data.list_uuids.push(item.list_uuid);
        });
        pAjaxRequest({'list_uuids':data.list_uuids}, '/api/v1/system/wblist/lock', 'PUT', (d) => {
            Metronic.unblockUI('.page-content');
            // 刷新表格
            let op = list_type == 2 ? LANG.UI_BLACK_WHITE_DISABLE_BLACK_LIST:LANG.UI_BLACK_WHITE_DISABLE_WHITE_LIST;
            if (operateResponseList(d, op)) {
                $('#black_list_table') .bootstrapTable('refresh');
            }
        });
    }

    // 启用名单（批量）
    var unlockList = function (){
        clickEffect(this);
        let selectedRows = $('#black_list_table').bootstrapTable('getSelections');
        let data = {};
        data.list_uuids =[];
        selectedRows.forEach((item,index)=>{
            data.list_uuids.push(item.list_uuid);
        });
        pAjaxRequest({'list_uuids':data.list_uuids}, '/api/v1/system/wblist/unlock', 'PUT', (d) => {
            Metronic.unblockUI('.page-content');
            // 刷新表格
            let op = list_type == 2 ? LANG.UI_BLACK_WHITE_ENABLE_BLACK_LIST:LANG.UI_BLACK_WHITE_ENABLE_WHITE_LIST;
            if (operateResponseList(d, op)) {
                $('#black_list_table') .bootstrapTable('refresh');
            }
        });
    }

    // 删除名单（批量）
    var deleteList = function (){
        let selectedRows = $('#black_list_table').bootstrapTable('getSelections');
        if(selectedRows.length == 0){
            return UIToastr.showInfo(LANG.UI_BLACK_WHITE_DISABLE_LIST, LANG.UI_BLACK_WHITE_SELECT_DELETE_LIST_TIPS);
        }
        let data = {};
        data.list_uuids =[];
        selectedRows.forEach((item,index)=>{
            data.list_uuids.push(item.list_uuid);
        });
        pAjaxRequest({'list_uuids':data.list_uuids}, '/api/v1/system/wblist', 'DELETE', (d) => {
            Metronic.unblockUI('.page-content');
            // 刷新表格
            let op = LANG.UI_BLACK_WHITE_DELETE_LIST;
            if (operateResponseList(d, op)) {
                $('#black_list_table') .bootstrapTable('refresh');
            }
        });
    }

    // 修改名单
    var editList = function (){
        clickEffect(this);
        let selectedRows = $('#black_list_table').bootstrapTable('getSelections');
        if(selectedRows.length == 0){
            return UIToastr.showInfo(LANG.UI_BLACK_WHITE_DISABLE_LIST, LANG.UI_BLACK_WHITE_SELECT_DELETE_LIST_TIPS);
        }
        let data = {};
        data.list_uuids =[];
        selectedRows.forEach((item,index)=>{
            data.list_uuids.push(item.list_uuid);
        });
        if(selectedRows.length > 1) {
            return UIToastr.showInfo(LANG.UI_BLACK_WHITE_EDIT_LIST, LANG.UI_BLACK_WHITE_SELECT_LIST_TO_EDIT);
        }
        // 清除提示
        $('.error-div').empty();
        $('#list_detail').drawer('toggle');
        // 获取名单具体信息
        getListInfo(data.list_uuids[0]);
        if(list_type != 2){
            // 移除类
            $('.iconList').removeClass('vicon-a-Wrong-usercuowuyonghu');

            // 添加类
            $('.iconList').addClass('vicon-a-Right-userzhengqueyonghu');

        }else if(list_type == 2){
            // 移除类
            $('.iconList').removeClass('vicon-a-Right-userzhengqueyonghu');

            // 添加类
            $('.iconList').addClass('vicon-a-Wrong-usercuowuyonghu');
        }
    }


    var tiplockList = function(){
        UIToastr.showInfo(LANG.UI_BLACK_WHITE_DISABLE_LIST, LANG.UI_BLACK_WHITE_DISABLE_LIST_TIPS);
    }
    var tipunlockList = function (){
        UIToastr.showInfo(LANG.UI_BLACK_WHITE_ENABLE_LIST, LANG.UI_BLACK_WHITE_ENABLE_LIST_TIPS);
    }
    var tipEditList = function (){
        UIToastr.showInfo(LANG.UI_BLACK_WHITE_EDIT_LIST, LANG.UI_BLACK_WHITE_EDIT_LIST_TIPS);
    }
    var tipDeleteList = function (){
        UIToastr.showInfo(LANG.UI_BLACK_WHITE_DELETE_LIST, LANG.UI_BLACK_WHITE_SELECT_DELETE_LIST_TIPS);
    }

    // 操作权限校验
    var checkAuth = function(operationType,row) {
        var select = $('#black_list_table').bootstrapTable('getSelections');
        if (!select.length) {
            switch (operationType){
                case 'lock':
                    tiplockList();
                    break;
                case 'unlock':
                    tipunlockList();
                    break;
                case 'edit':
                    tipEditList();
                    break;
                case 'delete':
                    tipDeleteList();
                    break;
            }
            return false;
        }
        return {
            type: 1,
            user_uuid: [...new Set(select.map(item => item.user_uuid))].join(','),
            auth: ''
        };
    }

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 381;
        $("#listMangerDiv .fixed-table-body").css({
            "height": height
        });
    }

    const checkEvent = (tableId, btnId) => {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            $('' + btnId + '').addClass('icon-gray-delete');
            $('' + btnId + '').removeClass('icon-white-delete');
            $('' + btnId + '').removeClass('select-delete-btn');
            $('' + btnId + '').addClass('cancel-delete-btn');
        } else {
            $('' + btnId + '').removeClass('icon-gray-delete');
            $('' + btnId + '').addClass('icon-white-delete');
            $('' + btnId + '').removeClass('cancel-delete-btn');
            $('' + btnId + '').addClass('select-delete-btn');
        }
    }


    /**
     * 初始化表格
     */
    const initDataTable = (list_type = 2) => {
        let beforeDiv = '';
        let afterDiv = '';
        $('.customBtn1').empty();
        $('.customBtn2').empty();
        if(list_type == CONF.FLAG.SET){
            // 白名单
            if($.inArray("p_white_delete", CONF.PERMISSION_ARR)!= -1){
                beforeDiv += `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete"></button></div>`;
            }
            if ($.inArray("p_white_add", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="add" data-toggle="drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
            }
            if ($.inArray("p_white_edit", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="modify" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-xiugai mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY + `</span>
                            </button>`;
            }
            if ($.inArray("p_white_disable", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="lock" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.UI_RECOVERY_AWS_DISABLE + `</span>
                            </button>`;
            }
            if ($.inArray("p_white_enable", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="unlock" style="width:auto;height:34px;border:0px">
                                <i class="viconfont viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>`+ LANG.UI_RECOVERY_AWS_ENABLE + `</span>
                            </button>`;
            }
        }else if(list_type == CONF.FLAG.UNSET){
            // 黑名单
            if($.inArray("p_black_delete", CONF.PERMISSION_ARR) != -1){
                beforeDiv += `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete"></button></div>`;
            }
            if ($.inArray("p_black_add", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="add" data-toggle="drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
            }
            if ($.inArray("p_black_edit", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="modify" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-xiugai mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY + `</span>
                            </button>`;
            }
            if ($.inArray("p_black_disable", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="lock" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.UI_RECOVERY_AWS_DISABLE + `</span>
                            </button>`;
            }
            if ($.inArray("p_black_enable", CONF.PERMISSION_ARR)!= -1) {
                afterDiv += `<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="unlock" style="width:auto;height:34px;border:0px">
                                <i class="viconfont viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>`+ LANG.UI_RECOVERY_AWS_ENABLE + `</span>
                            </button>`;
            }
        }
        let options = {
            vin_url: '/api/v1/system/wblist',
            vin_method: 'GET',
            vin_params: function () {
                let param = {};
                param.list_type = list_type;
                return param;
            },
            customTool: {
                beforeInput: beforeDiv,
                afterInput: afterDiv,
            },
            toolbarId: '#vin_list_toolbar',
            searchInput: true,
            pagination:true,
            pageList: [5,10,25,50],
            hangeHeightBtn: true, //改变高度按钮
            resizable: true, //可变宽度
            search: true,
            changeHeightBtn: true, //改变高度按钮
            placeholder:  LANG.UI_BLACK_WHITE_SEARCH_IP, //搜索框的placeholder
            searchClass: 'IPSearch', //自定义的搜索框类名
            searchSelector: '.IPSearch', //选择使用自定义搜索框
            onResetView: initTableHeight,
            onCheck: function () {
                modifyDelStyle('black_list_table', 'delete');
            },
            onUncheck: function () {
                modifyDelStyle('black_list_table', 'delete');
            },
            onCheckAll: function () {
                modifyDelStyle('black_list_table', 'delete');
            },
            onUncheckAll: function () {
                modifyDelStyle('black_list_table', 'delete');
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false  //是否开启排序
                },
                {
                    field: 'ip_address',
                    title: LANG.UI_BLACK_WHITE_IP_ADDRESS,
                    sortable: false,
                    align: 'left',
                },
                {
                    field: 'valid_time',
                    title: LANG.UI_BLACK_WHITE_VALID_TIME,
                    sortable: true,
                    align: 'left',
                },
                {
                    field: 'lock_flag',
                    title: LANG.UI_BLACK_WHITE_STATUS,
                    sortable: false,
                    align: 'left',
                    formatter:(value,row)=>{
                        let status = '';
                        value == CONF.FLAG.SET ? (status = '<span class="label label-sm label-success status-icon">'+LANG.UI_BLACK_WHITE_ENABLE+'</span>') : (status = '<span class="label label-sm label-danger status-icon">'+LANG.UI_BLACK_WHITE_DISABLE+'</span>');
                        return status;
                    }
                },
                {
                    field: 'create_user_name',
                    title: LANG.UI_REPORT_BUILDER,
                    sortable: false,
                    align: 'left'
                },
                {
                    field: 'description',
                    title: LANG.UI_PUBLIC_REMARK,
                    sortable: false,
                    align: 'left'
                },
            ]
        };
        $('#black_list_table').bootstrapTable('destroy');
        $('#black_list_table').baseTableConfig().init(options);
        initListener();
    };

    /**
     * 初始化时间插件
     * 需要引入daterangepicker.js、daterangepicker.locales.js、bootstrap-switch.min.js
     */

    let inintDatatimePicker = function () {
        // 初始化日期时间选择控件
        let dataRangePicker = $('#validDateRangePicker');
        let startDate = moment().subtract(6, 'days').startOf('day');
        let endDate = moment({hour: 23, minute: 59});

        dataRangePicker.daterangepicker({
            "autoUpdateInput": true,
            "startDate": startDate,
            "endDate": endDate,
            // "maxDate": endDate,
            "timePicker": true,
            "timePicker24Hour": true,
            "alwaysShowCalendars": true,
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),
        }, function(start, end, label) {
        });

        // 手动填充默认时间到输入框
        dataRangePicker.val(startDate.format('YYYY-MM-DD HH:mm') + ' - ' + endDate.format('YYYY-MM-DD HH:mm'));

        // 如果是选择后自动填充 input(autoUpdateInput:true)，需要监听下面两个方法 apply.daterangepicker 和 cancel.daterangepicker
        dataRangePicker.on('apply.daterangepicker', function(ev, picker) {
            // 给全局变量赋值，然后设置input
            _dateRangePicker_startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            _dateRangePicker_endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            _dateRangePicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
        });

        // input右侧的图标事件
        $('.daterangepickerdiv i').click(function() {
            $(this).parent().find('input').click();
        });
    }

    /**
     * 添加名单
     */
    const addList = ()=>{
        let data = {};
        data.start_ip = $('input[name = start_ip]').val();
        data.end_ip = $('input[name = end_ip]').val();
        // 永久时效的状态
        // 如果开关打开则永久时效，关闭则需要选择时间段
        data.permanent_access_flag = $('#permanentTimeCheck').bootstrapSwitch('state');
        if(!data.permanent_access) {
            data.time_range = $('#validDateRangePicker').val();
        }
        data.description = $('textarea[name = description]').val();
        data.list_type = list_type;
        pAjaxRequest(data,'/api/v1/system/wblist','POST',(d)=>{
            Metronic.unblockUI('.page-content');
            // 添加名单
            let op = LANG.UI_BLACK_WHITE_ADD_LIST_SUCCESS;
            if(operateResponseList(d, op)){
                $('#black_list_table') .bootstrapTable('refresh');
            }
        });
    }

    /**
     * 获取名单具体信息
     */
    const getListInfo = (list_uuid)=>{
        $('#titleDes').html(list_type == 2 ? LANG.UI_BLACK_WHITE_EDIT_BLACK_LIST:LANG.UI_BLACK_WHITE_EDIT_WHITE_LIST);
        // 获取单个名单的信息
        let data = {};
        data.list_uuid = list_uuid;
        pAjaxRequest(data,'/api/v1/system/wblist','GET',(d)=>{
            $('input[name = start_ip]').val(d.data.start_ip);
            $('input[name = end_ip]').val(d.data.end_ip);
            if(d.data.permanent_access_flag){
                // 永久时效
                $('#permanentTimeCheck').bootstrapSwitch('state',true);
                $('.selecTimeDiv').hide();
            }else{
                $('#permanentTimeCheck').bootstrapSwitch('state',false);
                $('.selecTimeDiv').show();
                $('#validDateRangePicker').val(d.data.start_time+' - '+d.data.end_time);
            }
            $('textarea[name = description]').val(d.data.description);
            // 隐藏添加提交button
            // 显示修改提交button
            $('#addsubmit').hide();
            $('#modifysubmit').show();
            $('#modifysubmit').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // 阻止事件冒泡
                if($('#listForm').valid()){
                    modifyListInfo(list_uuid);
                }else{
                    // 阻止drawer的默认关闭行为
                    return false;
                }
            });

        },false);
    }

    /**
     * 修改名单信息
     */
    const modifyListInfo = (list_uuid)=>{
        let data = {};
        data.start_ip = $('input[name=start_ip]').val();
        data.end_ip = $('input[name=end_ip]').val();
        data.list_uuid = list_uuid;
        data.permanent_access_flag = $('#permanentTimeCheck').bootstrapSwitch('state');
        if(!data.permanent_access_flag) {
            data.time_range = $('#validDateRangePicker').val();
        }
        data.description = $('textarea[name = description]').val();
        pAjaxRequest(data,'/api/v1/system/wblist','PUT',(d)=>{
            Metronic.unblockUI('.page-content');
            // 修改名单信息
            let op = LANG.UI_BLACK_WHITE_EDIT_LIST_SUCCESS;
            if(operateResponseList(d, op)){
                $('#list_detail').drawer('toggle');
                $('#black_list_table') .bootstrapTable('refresh');
            }
        });
    }

    /**
     * 添加名单清除记录
     */
    const clearAllRecords = () => {
        $('input[name = start_ip]').val('');
        $('input[name = end_ip]').val('');
        $('#permanentTimeCheck').bootstrapSwitch('state',false);
        $('textarea[name = description]').val('');
        let errorDivs = document.getElementsByClassName("error-div");
        // 删除父元素的所有子元素
        // 检查是否至少有一个匹配的元素
        if (errorDivs.length > 0) {
            // 遍历每个匹配的元素
            for (let i = 0; i < errorDivs.length; i++) {
                let errorDiv = errorDivs[i];

                // 删除当前元素的所有子元素
                while (errorDiv.firstChild) {
                    errorDiv.removeChild(errorDiv.firstChild);
                }
            }
        }
    }

    /**
     * 校验ipv4合理范围
     * @param startIP
     * @param endIp
     * @return {boolean}
     */
    function checkIPV4Range(startIP, endIp) {
        if (!ipv4Regex(startIP) || !ipv4Regex(endIp)) {
            return false;
        }

        // 将 IP 地址转换为数字
        function ipToNumber(ip) {
            const parts = ip.split('.').map(Number);
            return parts[0] * 256 ** 3 + parts[1] * 256 ** 2 + parts[2] * 256 + parts[3];
        }

        // 检查起始 IP 是否小于等于结束 IP
        if (ipToNumber(startIP) > ipToNumber(endIp)) {
            return false;
        }

        // 起始 IP 小于等于结束 IP,且两者都是有效的 IPv4 地址,有效IP范围
        return true;
    }

    /**
     * 校验IPV6合理范围
     */
    function checkIPV6Range(startIP, endIP) {
        // 检查IPv6地址格式是否正确
        if(!ipv6Regex(startIP,endIP)){
            return false;
        }

        // 将起始IPv6地址和结束IPv6地址展开为标准形式
        startIP = expandIPv6(startIP);
        endIP = expandIPv6(endIP);

        // 拆分起始IPv6地址和结束IPv6地址为段数组
        const startSegments = startIP.split(':').map(segment => parseInt(segment, 16));
        const endSegments = endIP.split(':').map(segment => parseInt(segment, 16));

        // 比较IPv6地址段
        for (let i = 0; i < 8; i++) {
            if (startSegments[i] < endSegments[i]) {
                return true;
            } else if (startSegments[i] > endSegments[i]) {
                return false;
            }
        }

        // 如果所有段相等，则范围无效
        return true;
    }





    function expandIPv6(ip) {
        // 如果IPv6地址中包含双冒号 ::，则进行简写展开
        if (ip.includes('::')) {
            const blocks = ip.split('::');
            const firstHalf = blocks[0].split(':');
            const secondHalf = blocks[1].split(':');

            // 计算双冒号之前的块的个数
            const firstHalfLength = firstHalf.length;
            // 计算双冒号之后的块的个数
            const secondHalfLength = secondHalf.length;
            // 计算省略的0的个数
            const omittedZeroCount = 8 - (firstHalfLength + secondHalfLength);

            // 构建简写展开后的IPv6地址
            const expandedBlocks = [];
            for (let i = 0; i < firstHalfLength; i++) {
                expandedBlocks.push(parseInt(firstHalf[i], 16));
            }
            for (let i = 0; i < omittedZeroCount; i++) {
                expandedBlocks.push(0);
            }
            for (let i = 0; i < secondHalfLength; i++) {
                expandedBlocks.push(parseInt(secondHalf[i], 16));
            }

            // 将展开后的IPv6地址转换为标准形式
            return expandedBlocks.map(block => block.toString(16).padStart(4, '0')).join(':');
        } else {
            // 如果IPv6地址没有双冒号，则地址已经是标准形式，直接返回
            return ip;
        }
    }








    /**
     * ipv4格式校验
     */
    function ipv4Regex(val){
        const ipv4Regex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){2}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        return ipv4Regex.test(val);
    }

    /**
     * ipv6格式校验
     */
    function ipv6Regex(val){
        const ipv6Regex = /^(([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,6}:|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:))$/;
        return ipv6Regex.test(val);
    }

    // 按钮点击效果
    var clickEffect = function (element) {
        $(element).addClass('btn-hover');
        setTimeout(function() {
            $(element).removeClass('btn-hover');
        }, 300); // 0.3秒后恢复原样
    }




    return {
        //main function to initiate the module
        init: function () {
            listValidate();
            initDataTable();
            inintDatatimePicker();
            initTableHeight();
        }
    }
}();

jQuery(document).ready(function() {
    ListAjax.init();
});

