// 监控平台配置
var Settings_Monitor_Platform = function (){
    var ipFlag = false;   // 测试IP是否连通
    var urlFlag = false;  // 是否选择了协议类型为http
    var editRow = {};          // 修改的列
    var verifyCycleMode = 1; // 默认选中TCP协议
    var handleAddValidation = function () {
        var form1 = $('#form_sample_1');
        var success1 = $('.alert-success', form1);
        var error1 = $('.alert-danger', form1);

        // 初始化验证插件
        form1.validate({
            errorElement: 'span',
            errorClass: 'help-block help-block-error',
            focusInvalid: false,
            ignore: "", // validate all fields including form hidden input
            rules: {
                monitorPlatformName: {
                    required: true,
                    nicknameAvailable: true,
                },
                targetIp:{
                    required:true,
                    targetIpRule:true,
                    isSameIP:true
                },
            },
            invalidHandler: function (event, validator) { //display error alert on form submit
                success1.hide();
                error1.show();
            },
            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').removeClass("has-success").addClass('has-error');
            },
            success: function (label, element) {
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
            },
            submitHandler: function (form) {
                success1.show();
                error1.hide();
            }
        });

        // 添加自定义验证方法
        $.validator.addMethod('nicknameAvailable', function (value) {
            var edit_monitor_platform_name = editRow.platform_name??'';
            var data = {
                monitor_platform_name: value,
                edit_monitor_platform_name: edit_monitor_platform_name
            };
            var result = false;
            pAjaxRequest(data, '/api/v1/system/message/monitor_platform/check', 'GET', function (res) {
                result = res.data.result;
            }, false);
            return result;
        }, LANG.UI_PLATFORM_THIRD_MESSAGE_NICKNAME_EXIST);

        $.validator.addMethod('targetIpRule', function (value) {
            var result = ipV4V6(value);
            return result;
        }, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);

        $.validator.addMethod('isSameIP', function (value) {
            var edit_ip = '';
            if(editRow != {}){
                edit_ip = editRow.ip == '--'?'':editRow.ip;
            }
            var data = {
                ip:value,
                edit_ip:edit_ip
            }
            var result =false;
            pAjaxRequest(data,'/api/v1/system/message/same/ip','GET',(d)=>{
                result = d.data.result;
            },false);
            return result;
        },LANG.UI_PLATFORM_THIRD_MONITOR_IP_ALREADY_EXIST);

        $.validator.addMethod('isSameUrl', function (value) {
            var edit_url = '';
            if(editRow != {}){
                edit_url = editRow.http_api_url == '--'?'':editRow.http_api_url;
            }
            var data = {
                url:value,
                edit_url:edit_url
            }
            var result =false;
            pAjaxRequest(data,'/api/v1/system/message/same/url','GET',(d)=>{
                result = d.data.result;
            },false);
            return result;
        },LANG.UI_PLATFORM_THIRD_MONITOR_URL_ALREADY_EXIST);

        // 监听 select 元素的 change 事件，根据选择不同的选项动态更新验证规则
        $('#protocolType').on('change', function () {
            var selectedOption = $(this).val();
            var targetIpInput = $('input[name=targetIp]');
            var urlInput = $('input[name=url]');

            targetIpInput.val('');
            urlInput.val('');

            // 清除 targetIp 字段的错误信息（如果它是有效的）
            targetIpInput.valid();
            // 清除 url 字段的错误信息（如果它是有效的）
            urlInput.valid();

            targetIpInput.closest('.form-group').removeClass('has-error');
            urlInput.closest('.form-group').removeClass('has-error');

            removeMonitorPlatformNameRule();
            removeIPRule();
            removeUrlRule();

             if (selectedOption === '1') {
                // 如果选择的是需要 targetIp 的选项，则添加相关的验证规则
                 addIPRule();
            }
            if (selectedOption === '2') {
                // 如果选择的是需要 targetIp 的选项，则添加相关的验证规则
                addUrlRule();
            }
        });

        // 监听提交按钮的点击事件
        $('#add_submit').on('click', function () {
            if (form1.validate().form() && (ipFlag || urlFlag)) {
                // 提交表单
                addSubmit();
            } else {
                UIToastr.showWarning(LANG.UI_PLATFORM_THIRD_MONITOR_TEST_IP_TIPS,LANG.UI_PLATFORM_THIRD_MESSAGE_CHECK_INFO);
            }
        });

        // 监听修改按钮的点击事件
        $('#edit_submit').off('click').on('click', function(e) {
            if (form1.validate().form() && (ipFlag || urlFlag)) {
                // 提交表单
                editSubmit(e,editRow);
            } else {
                UIToastr.showWarning(LANG.UI_PLATFORM_THIRD_MONITOR_TEST_IP_TIPS,LANG.UI_PLATFORM_THIRD_MESSAGE_CHECK_INFO);
            }
        })
    };


    var addListeners = function (){
        // 协议类型不同，显示不同
        $('.agreementDiv').show();
        $('.ipDiv').show();
        $('.portDiv').show();
        $('.testDiv').show();
        $('select[name=protocolType]').on('change', function () {
            var protocolTypeValue = parseInt($(this).find('option:selected').val()); // 转换为整数
            if (protocolTypeValue === CONF.FLAG.SET) {
                urlFlag = false;
                $('.agreementDiv').show();
                $('.ipDiv').show();
                $('.portDiv').show();
                $('.testDiv').show();
                $('.urlDiv').hide();
            } else {
                urlFlag = true;
                $('.agreementDiv').hide();
                $('.ipDiv').hide();
                $('.portDiv').hide();
                $('.testDiv').hide();
                $('.urlDiv').show();
            }
        });

        // 取消按钮隐藏抽屉
        $('#add_monitor_platform_drawer .cancel').click(function (){
            editRow = {};
            $('#add_monitor_platform_drawer').drawer('hide');
        });
        // 添加监控平台
        $('#add_monitor_platform').on('click',initMonitorPlatform);
        // 删除监控平台
        $('#delete_monitor_platform').on('click',deleteMonitorPlatform);
        // 测试IP是否连通
        $('#testTool').on('click',testHandler);
        $('.agreementDiv input[type=radio]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });

        // radio group change
        $('#check_cycle').find('.icheck-verify-cycle').on('ifClicked', verifyModeChange);

    };

    var clickEffect = function (element) {
        $(element).addClass('btn-hover');
        setTimeout(function() {
            $(element).removeClass('btn-hover');
        }, 300); // 0.3秒后恢复原样
    }

    /**
     * 清除验证消息
     * @param event
     */
    var clearValidate = function (element){
        element.find('.form-group').removeClass('has-error').removeClass('has-success'); // 移除验证状态类名
        element.find('.help-block').remove(); // 移除验证消息
    }

    var clearAllOption = function (event){
        ipFlag = false;
        urlFlag = false;
        editRow = {};
        // 显示隐藏元素
        $('#add_submit').show();
        $('#edit_submit').hide();
        $('.add_monitor_platform').show();
        $('.edit_monitor_platform').hide();
        clearValidate($('#form_sample_1'));
        $('input[name=monitorPlatformName]').val('');
        document.getElementById('protocolType').value = '1';
        $('.agreementDiv').show();
        $('.ipDiv').show();
        $('.portDiv').show();
        $('.testDiv').show();
        $('.urlDiv').hide();
        $('input[name=targetIp]').val('');
        $('input[name=url]').val('');
        $('input[data-mode="1"]').iCheck('check');
        verifyCycleMode = 1;
    }

    var initMonitorPlatform = function (){
        // 默认名
        getMonitorDefaultName();
        clickEffect(this);
        // 清空
        clearAllOption();
        addMonitorPlatformNameRule();
        addIPRule();
    };

    function initTableHeight() {
        //拿到父窗口的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 381;

        $("#pushMangerDiv .fixed-table-body").css({
            "height": height
        });
    }

    var initNode = function (){
        var data = {
            offset: 0,
            limit: 1,
            node_type: 1,
        };
        pAjaxRequest(data,'/api/v1/nodes','GET',function (d){
                var rows = d.data.rows;
                var option = $('#node_value option');
                rows.forEach((item) => {
                    option.val(item.node_uuid);
                })
            }
        )
    }

    var initMonitorPlatformTable = function (){

        var operationFormatter = function (value, row, index, field) {
            var button = '<div class="btn-group">';
            if (index > 2) {
                button = '<div class="btn-group dropup">';
            }

            button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
                'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
                '' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
                '</button>' +
                '<ul class="dropdown-menu min-width100" role="menu">';

            // 查看&修改
            button += '<li class="edit"><a href="javascript:;"  data-toggle="drawer" data-target="#add_monitor_platform_drawer" aria-haspopup="true" aria-expanded="false" ><i class="viconfont vicon-edit-new"></i>'+ LANG.UI_PLATFORM_THIRD_MESSAGE_EDIT +'</a></li>';

            // 删除
            button += '<li class="delete"><a href="javascript:;"  data-toggle="" data-target="" aria-haspopup="true" aria-expanded="false" ><i class="viconfont vicon-a-Deleteshanchu2"></i>'+ LANG.UI_CLIENT_DELETE_NEW +'</a></li>';

            button += '</ul></div>';
            return button;
        }

        var op = {
            'click .edit':function (event, value, row, index){
                editRow = row;
                // 显示隐藏元素
                $('#edit_submit').show();
                $('#add_submit').hide();
                $('.add_monitor_platform').hide();
                $('.edit_monitor_platform').show();
                // 回填表格
                backfillForm(row);
                // 清除验证
                clearValidate($('#form_sample_1'));

                addMonitorPlatformNameRule();

                // 协议类型
                var selectedVal = $('select[name=protocolType]').val();
                // 协议
                if( selectedVal == 1){
                    // syslog
                    ipFlag = false;
                    urlFlag = false;
                    if(row.syslog_protocol_type == 2){
                        // 协议为UDP 不用校验IP
                        ipFlag = true;
                    }
                    removeUrlRule();
                }else if(selectedVal == 2){
                    // http
                    ipFlag = false;
                    urlFlag = true;
                    removeIPRule();
                }
            },
            'click .delete':function (event, value, row, index){
                var data = {};
                data.monitor_platform_uuid = [];
                data.monitor_platform_uuid.push(row.platform_uuid);
                pAjaxRequest(data,'/api/v1/system/message/monitor_platform','DELETE',function (res){
                    Metronic.unblockUI('#monitorPlatformTable');
                    if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_DELETE)) {
                        $('#monitorPlatformTable').bootstrapTable('refresh', {query: {offset:0}});
                    }
                });
            },
        };

        var beforeInput = `<button type="button" class="icon-gray-delete b-btn brr2 mr12" style="background-color:#F4F4F5" id="delete_monitor_platform"></button>`
        var afterInput = '';
        afterInput += `<div style="display:flex"><button type="button" class="dropdown-toggle btn-font flex_center btn-title p-lr8" id="add_monitor_platform" data-toggle="drawer" data-target="#add_monitor_platform_drawer" aria-haspopup="true" aria-expanded="false" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
        let options = {
            toolbarId: '#vin_monitor_platform_toolbar',
            buttonsToolbar: '#vin_monitor_platform_toolbar .vin_btnToolbar',
            vin_url: '/api/v1/system/message/monitor_platform',
            vin_params: function(){
                let params = {};
                params.search = $('#vin_monitor_platform_toolbar .platformSearch').val();
                return params;
            },
            searchOnEnterKey:false,
            vin_method: 'GET',
            placeholder: LANG.UI_SEARCH_NICKNAME, //搜索框的placeholder
            searchInput: true, //搜索框
            searchClass: 'platformSearch', //自定义的搜索框类名
            searchSelector: '.platformSearch', //选择使用自定义搜索框
            showColumns: true,
            showExport: true,
            paginationLoop: false,
            onResetView: initTableHeight,
            onCheck: function () {
                checkEvent('#monitorPlatformTable', '#delete_monitor_platform')
            },
            onUncheck: function () {
                checkEvent('#monitorPlatformTable', '#delete_monitor_platform')
            },
            onCheckAll: function () {
                checkEvent('#monitorPlatformTable', '#delete_monitor_platform')
            },
            onUncheckAll: function () {
                checkEvent('#monitorPlatformTable', '#delete_monitor_platform')
            },
            customTool: {
                beforeInput: beforeInput,
                afterInput: afterInput,
            },
            columns:[
                {
                    // field:'checked',
                    checkbox: true,
                    sortable: false, //默认可排序，禁用排序才写此项
                    formatter: function (value, row, index, field) {
                        if (row.checked === false) {
                            return {
                                disabled: true
                            };
                        }
                    }
                },
                {
                    field: 'platform_name',
                    title: LANG.UI_SEARCH_NICKNAME,
                },
                {
                    field: 'protocol_type',
                    title: LANG.UI_PLATFORM_THIRD_PROTOCOL_TYPE,
                },
                {
                    field: 'syslog_protocol_type',
                    title: LANG.UI_RECOVERY_PROTOCOL,
                    formatter:(index,row)=>{
                        if(row.protocol_type == 'syslog'){
                            // 协议类型 syslog
                            if(index == CONF.FLAG.SET){
                                return 'TCP';
                            }else{
                                return 'UDP';
                            }
                        }else{
                            return '--';
                        }
                    }
                },
                {
                    field: 'ip',
                    title: LANG.UI_DRILLS_IP_ADDRESS,
                },
                {
                    field: 'port',
                    title: LANG.UI_PLATFORM_THIRD_PORT,
                },
                {
                    field: 'http_api_url',
                    title: 'URL',
                },
                {
                    field: 'operation',
                    title: LANG.UI_PUBLIC_OPERATION,
                    formatter: operationFormatter,
                    events: op,
                    opButton: true,
                    clickToSelect: false, //不可通过点击行选中
                    sortable: false, //默认可排序，禁用排序才写此项
                }
            ],
        };
        $('#monitorPlatformTable').baseTableConfig().init(options);
    };

    var checkEvent = function (tableId, btnId) {
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
     * 回填表单
     */
    var backfillForm = function (row){
        pAjaxRequest({},'/api/v1/system/message/monitor_platform/'+row.platform_uuid,'GET',function (res){
            var data = res.data;

            if (data.protocol_type === CONF.FLAG.SET) {
                $('.agreementDiv').show();
                $('.ipDiv').show();
                $('.portDiv').show();
                $('.testDiv').show();
                $('.urlDiv').hide();
                if(res.data.syslog_protocol_type == CONF.FLAG.UNSET){
                    $('.testDiv').hide();
                }
            } else {
                $('.agreementDiv').hide();
                $('.ipDiv').hide();
                $('.portDiv').hide();
                $('.testDiv').hide();
                $('.urlDiv').show();
            }

            $('input[name=monitorPlatformName]').val(data.platform_name);
            var protocolTypeEle = document.getElementById('protocolType');
            for(var i =0;i<protocolTypeEle.options.length;i++){
                if(protocolTypeEle.options[i].value == data.protocol_type){
                    protocolTypeEle.options[i].selected = true;
                }
            }
            $('input[name=targetIp]').val(data.ip);
            $('input[name=url]').val(data.http_api_url);
            $(`input[data-mode="${data.syslog_protocol_type}"]`).iCheck('check');
        },false);
    }

    /**
     * 提示
     */
    var tipDeletePlatform = function () {
        UIToastr.showInfo(LANG.UI_PLATFORM_THIRD_MONITOR_DELETE, LANG.UI_PLATFORM_THIRD_MONITOR_DELETE_TIP);
    }

    /**
     * 添加提交
     */
    var addSubmit = function (){
        var data = {};
        // 别名
        data.nickname = $('input[name=monitorPlatformName]').val();
        // 协议类型
        data.protocol_type = $('#protocolType option:selected').val();
        // 目标IP
        data.ip = $('input[name=targetIp]').val();
        // 端口号
        if(parseInt($('select[name=protocolType]').val()) == CONF.FLAG.SET){
            data.port = parseInt($('input[name=port]').val());
            // 协议
            data.syslog_protocol_type = verifyCycleMode;
        }else{
            data.port = '';
            // 协议
            data.syslog_protocol_type = '';
        }
        // url
        data.url = $('input[name=url]').val();
        $('#add_monitor_platform_drawer').drawer('hide');
        Metronic.blockUI({
            target: '#monitorPlatformDiv',
            animate: true,
            cenrerY: true,
        });
        pAjaxRequest(data,'/api/v1/system/message/monitor_platform','POST',function (res){
            Metronic.unblockUI('#monitorPlatformDiv');
            if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_ADD)) {
                $('#monitorPlatformTable').bootstrapTable('refresh', {query: {offset:0}});
            }
        });
    }

    /**
     * 修改提交
     */
    var editSubmit = function (e,row){
        e.stopPropagation(); // 阻止事件冒泡
        var data = {};
        data.monitor_platform_uuid = row.platform_uuid;
        // 别名
        data.nickname = $('input[name=monitorPlatformName]').val();
        // 协议类型
        data.protocol_type = $('#protocolType option:selected').val();
        if(parseInt($('select[name=protocolType]').val()) == CONF.FLAG.SET){
            data.ip = $('input[name=targetIp]').val();
            data.port = parseInt($('input[name=port]').val());
            data.url = '';
            data.syslog_protocol_type = verifyCycleMode;
        }else{
            data.ip = '';
            data.port = '';
            data.url = $('input[name=url]').val();
            data.syslog_protocol_type = '';
        }
        $('#add_monitor_platform_drawer').drawer('hide');
        Metronic.blockUI({
            target: '#monitorPlatformDiv',
            animate: true,
            cenrerY: true,
        });
        pAjaxRequest(data,'/api/v1/system/message/monitor_platform','PUT',function (res){
            Metronic.unblockUI('#monitorPlatformDiv');
            if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_EDIT)) {
                $('#monitorPlatformTable').bootstrapTable('refresh', {query: {offset:0}});
            }
        });
}


    /**
     * 删除监控平台
     */
    var deleteMonitorPlatform = function (){
        clickEffect(this);
        let select = $('#monitorPlatformTable').bootstrapTable('getSelections');
        let ids = [];
        for (let i = 0; i < select.length; i++) {
            ids.push(select[i].platform_uuid);
        }
        if (!select.length) {
            return tipDeletePlatform();
        }
        var data = {};
        data.monitor_platform_uuid = ids;
        Metronic.blockUI({
            target: '#monitorPlatformDiv',
            animate: true,
            cenrerY: true,
        });
        pAjaxRequest(data,'/api/v1/system/message/monitor_platform','DELETE',function (res){
            Metronic.unblockUI('#monitorPlatformDiv');
            if (operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_DELETE)) {
                $('#monitorPlatformTable').bootstrapTable('refresh', {query: {offset:0}});
            }
        });
    }

    /**
     * IP测试
     */
    var testHandler = function (){
        var data ={};
        data.ip = $('input[name=targetIp]').val();
        data.node_uuid = $('#node_value option:selected').val();
        data.tool_type = 'telnet';
        data.port = $('input[name=port]').val();
        Metronic.blockUI({target:'#add_monitor_platform_drawer', animate: true});
        pAjaxRequest(data,'/api/v1/system/tools/connect','POST',function (d){
            Metronic.unblockUI('#add_monitor_platform_drawer');
            ipFlag= d.success;
            if (operateResponseList(d)){

            }
        });
    }

    /**
     * 协议 mode change
     */
    const verifyModeChange = function() {
        ipFlag = false;
        verifyCycleMode = parseInt($(this).data('mode'));
        if(parseInt(verifyCycleMode) == CONF.FLAG.SET){
            ipFlag = false;
            $('.testDiv').show();
        }else if(parseInt(verifyCycleMode) == CONF.FLAG.UNSET){
            ipFlag = true;
            $('.testDiv').hide();
        }
    }

    /**
     * 默认监控名
     */
    var getMonitorDefaultName = function (){
        var data = {};
        data.name = LANG.UI_PLATFORM_THIRD_MESSAGE_PUSH_MONITOR_PLATFORM;
        pAjaxRequest(data,'/api/v1/system/message/monitor/name','GET',function (res){
            $('input[name=monitorPlatformName]').val(res.data.name);
        });
    }

    /**
     * 移除监控平台名规则
     */
    var removeMonitorPlatformNameRule = function (e,row){
        var monitorPlatformNameInput = $('input[name=monitorPlatformName]');
        monitorPlatformNameInput.each(function() {
            $(this).rules('remove'); // 移除该元素上的所有验证规则
        });
    }

    /**
     * 添加监控平台名规则
     */
    var addMonitorPlatformNameRule = function (){
        var monitorPlatformNameInput = $('input[name=monitorPlatformName]');
        monitorPlatformNameInput.rules('add', {
            required: true,
            nicknameAvailable: true,
        });
    }

    /**
     * 移除IP规则
     */
    var removeIPRule = function (e,row){
        var targetIpInput = $('input[name=targetIp]');
        targetIpInput.each(function() {
            $(this).rules('remove'); // 移除该元素上的所有验证规则
        });
    }

    /**
     * 添加IP规则
     */
    var addIPRule = function (){
        var targetIpInput = $('input[name=targetIp]');
        targetIpInput.rules('add', {
            required: true,
            targetIpRule: true,
            isSameIP:true
        });
    }

    /**
     * 移除url规则
     */
    var removeUrlRule = function (e,row){
        var urlInput = $('input[name=url]');
        urlInput.each(function() {
            $(this).rules('remove'); // 移除该元素上的所有验证规则
        });
    }

    /**
     * 添加url规则
     */
    var addUrlRule = function (e,row){
        var urlInput = $('input[name=url]');
        urlInput.rules('add',{
            required:true,
            isSameUrl:true
        })
    }

    return {
        init:function(){
            initNode();
            initMonitorPlatformTable();
            addListeners();
            handleAddValidation();
        }
    };
}();

jQuery(document).ready(function(){
    Settings_Monitor_Platform.init();
});