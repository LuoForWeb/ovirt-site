var RecoverPlatform = function () {
    var data = {point_info:{},recover_info:{},speed_limit:{}, high_info:{},safe_config_strategy:{},type_info:{high:{trasfer:{}}},strategygroupuuid: ''};

    var _hypervisor;		//恢复目标虚拟化类型
    var globalStrategy = [];
    var editFlag = false;
    var selectIPFlag = true;
    var treeshowType  = null;

    var currentPlatformUuid = ""; //当前恢复目标虚拟化中心
    let _selectTargetType = '';          // 选择目标类型
    var authFun = [];
    var preNode = ''; // 记录上次选中的node
    var preNodeList = ''; // 记录上次选中的node对应的存储块
    var pointType = 1; // 时间点类型
    var vmNameLimit = {len: '', limit: '', msg: '', dis_len: '', disk_limit: '', disk_msg: ''};
    var vmRecoverConfig = vmRecoverConfigs();
    var authConfig = {};
    var isTape = false; // 标记下时间点的是不是磁带
    var isLicense = false; // 标记下授权是否有效
    var submittedFlag = false; // 是否已提交

    var submit = function(){
        if (submittedFlag) {
            return;
        }
        // 提交
        if('' == $.trim($("#job_name").val())){
            $('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
            return;
        }
        $('.jobnametip').hide();
        let jobName = $.trim($("#job_name").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        data.job_name = jobName;
        data.point_type = pointType;
        if(data.job_name == ''){
            return UIToastr.showWarning(LANG.UI_SEARCH_RECOVER_TASK, LANG.UI_MOTION_INPUT_TASKNAME);
        }

        data.storagepath  = [];
        if(treeshowType == null || treeshowType == ""){
            //所有存储
            data.storage_media = '';
        }else{
            //华为CBR或者其他特定存储类型
            data.storage_media  = treeshowType ;
        }
        for(var i = 0; i< data.point_info.length;i++){
            var nodeparam = {
                path:data.point_info[i].path,
                vmid:data.point_info[i].uuid
            }
            data.storagepath.push(nodeparam);
        }

        // 进行ajax请求
        var url = '/api/v1/recovery/agentlessis'; // 无代理
        if (_selectTargetType == 'completeMachineRecovery') {
            // 有代理
            url = '/api/v1/recovery/agent';
        }
        submittedFlag = true;
        Metronic.blockUI({target: '#vmrecovercontent',animate: true,cenrerY: true,});
        pAjaxRequest(data, url, "POST", function (result) {
            Metronic.unblockUI('#vmrecovercontent');
            if (result.code == -1) {
                submittedFlag = false;
                return UIToastr.showWarning(LANG.UI_SEARCH_RECOVER_TASK, result.message);
            }
            if (operateResponseList(result, LANG.UI_SEARCH_RECOVER_TASK)){
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            } else {
                submittedFlag = false;
            }
        });
    }

    var handleValidation = function() {
        var recoverForm = $('#submit_form');

        recoverForm.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "#searchname, #searchAgent, #job_name",  // validate all fields including form hidden input
            rules: {
                vmname: {
                    required: true,
                    newvmname: true,
                },
                diskname: {
                    required: true,
                    newdiskname: true
                },
            },

            invalidHandler: function (event, validator) { //display error alert on form submit
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                icon.removeClass('fa-check').addClass("fa-warning");
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs

            },

            unhighlight: function (element) { // revert the change done by hightlight

            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {

            }

        });

        $('#submitbtn').on('click',function(){
            //任务名
            if ('' === $.trim($('#job_name').val())) {
                UIToastr.showWarning(LANG.UI_INSTANT_NAME, LANG.UI_RECOVERY_INSTANT_RENAME);
                return false;
            }
            submit();
        });

        //取消按钮事件
        $('#cancelbtn').on('click',function(){
            LOCATION('./content/platform/recovery/graininess.php', 'graininess');
        });

        $.validator.addMethod("newvmname", function(value, element) {
            vmNameLimit = vmRecoverConfig.getVmNameLimit();
            $.validator.messages.newvmname = vmNameLimit.msg;
            if (vmNameLimit.len) {
                if (vmNameLimit.vmware_flag && value.replace(/[\\%/]/g, 'xxx').length > vmNameLimit.len) {
                    //vmware平台%/\算3个字符，其他算一个
                    return false;
                }
                if (vmNameLimit.scp_flag && value.replace(/[\u4e00-\u9fa5（）【】]/g, 'xxx').length > vmNameLimit.len) {
                    //scp平台中文和中文括号算3个字符，其他算一个
                    return false;
                }
                if (value.length > vmNameLimit.len) {
                    return false;
                }
            }
            value = $.trim(value);
            if ('' == value) {
                return false;
            }
            if (vmNameLimit.limit) {
                let reg = new RegExp(vmNameLimit.limit);
                return reg.test(value);
            }
            return true;
        });

        $.validator.addMethod("newdiskname", function(value, element) {
            vmNameLimit = vmRecoverConfig.getVmNameLimit();
            $.validator.messages.newdiskname = vmNameLimit.disk_msg;
            if (vmNameLimit.disk_len && value.length > vmNameLimit.disk_len) {
                return false;
            }
            value = $.trim(value);
            if ('' == value) {
                return false;
            }
            if (vmNameLimit.disk_limit) {
                let reg = new RegExp(vmNameLimit.disk_limit)
                return reg.test(value);
            }
            return true;
        });
    };

    //事件监听
    var initListener = function(){
        // 获取 虚拟机、私有云、公有云和整机的授权
        getAuth();
        // 切换目标类型
        $('#radio_group_1').on('change',function(e,oldValue, newValue){
            _selectTargetType = newValue;
            if(newValue == 'completeMachineRecovery'){
                // 整机
                $('#show_completeMachineRecovery').show();
                $('#show_vmRecovery').hide();
                // 传输策略切换
                $('.agent-transport-strategy').show();
                $('.vm-transport-strategy').hide();
                if (pointType == 3) {
                    // cdp 实时
                    $('.cdp-special').show();
                } else {
                    $('.cdp-special').hide();
                }
            }else{
                // 虚拟化 私有云 公有云
                $('#show_completeMachineRecovery').hide();
                $('#show_vmRecovery').show();
                // 传输策略切换
                $('.agent-transport-strategy').hide();
                $('.vm-transport-strategy').show();

                // 清空下虚拟化的数据
                $('#host_tree').html('');
                $('#driverCheck_vm').html('');
                $('#driverCheckFailContinue').hide();
                $('#driverCheck_vm_button').hide();
                $('#accordionvm').html('');
                $('#vmconfigs').hide();
                $('#awsconfigs').hide();
                $('#vmssetting').bootstrapSwitch('state', false); // 关闭统一配置
                $('#unifysettingdiv').hide();
                // 初始化虚拟化
                var inits = {jobType: 1, pointType: pointType};
                if(newValue == 'vmRecovery3'){
                    // 公有云
                    inits.showType = 3;
                }else if(newValue == 'vmRecovery2'){
                    // 私有云
                    inits.showType = 2;
                    $(`.vm-text`).hide();
                    $(`.prcloud-text`).show();
                }else {
                   // 虚拟化平台
                    inits.showType = 1;
                    $(`.vm-text`).show();
                    $(`.prcloud-text`).hide();
                }
                vmRecoverConfig.init(inits);
            }
        })
        //选择恢复方式
        $('#recovertype').on('change',function(){
            if('1' == this.value){
                data.type_info.strategy = {};
                $('.setOnceTime').hide();
            }else if("2" == this.value){
                $('.setOnceTime').show();
            }
            getTimeDes();
        });

        // 传输数据压缩
        $('#cdp_tran_compress_switch').on('switchChange.bootstrapSwitch',switchCompress);

        //自动选择IP
        $('#diysystemip').on('click', function(){
            $('.selectipdiv').hide();
            $('.inputipdiv').show();
            selectIPFlag = false;
        })

        //手动输入IP
        $('#selectsystemip').on('click', function(){
            $('.selectipdiv').show();
            $('.inputipdiv').hide();
            selectIPFlag = true;
        })

        if (!CONF.FUNCTIONS.includes('multithread')) {
            // 没得多线程，那么就隐藏页面的配置
           $('#agent_recoveryThreadDiv').parent().parent().hide();
        }

        if (!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity')) {
            // 既没有病毒查杀功能，也没有完整性校验功能，那么隐藏安全策略
            $('#safeShowDiv').hide(); // 最后一步显示
            $('#tab_safe').hide(); // 策略内容
            $('.safeLi').hide(); // 策略tab
        }

        //初始化重试策略
        $('#retry_config').retryStrategy();

        // 忽略节点限制默认打开
        $('#ignore_resource_limit').bootstrapSwitch('state', true);

        // 传输策略---加密传输
        $('#agent_encrypttransfer').on('switchChange.bootstrapSwitch', agenttransferEncryptChange);
    }

    // 获取授权信息
    var getAuth = function () {
        getAuthItem('vm');
        /*getAuthItem('private_cloud');
        getAuthItem('cloud');
        getAuthItem('os');
        if (!authConfig.vm) {
            // 隐藏虚拟机
            $('#radio_group_1').find('label[value="vmRecovery"]').hide();
        }
        if (!authConfig.private_cloud) {
            // 隐藏私有云
            $('#radio_group_1').find('label[value="vmRecovery2"]').hide();
        }
        if (!authConfig.cloud) {
            // 隐藏公有云
            $('#radio_group_1').find('label[value="vmRecovery3"]').hide();
        }
        if (!authConfig.os) {
            // 隐藏整机
            $('#radio_group_1').find('label[value="completeMachineRecovery"]').hide();
        }*/

        if (!isLicense) {
            // 授权已失效，给出提示
            UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE,  LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
        }
    }
    var getAuthItem = function (m) {
        var datas = {
            type: 'a',
            module: m
        };
        pAjaxRequest(datas, '/api/v1/system/auth/base_info', "GET", function (result) {
            if (result.code == 0 && result.data.license_flag) {
                authConfig[m] = result.data.total != 0;
            } else {
                authConfig[m] = false;
            }
            isLicense = result.data.license_flag;
        }, false);
    }

    // 获取时间策略信息描述
    var getTimeDes = function () {
        var des = '';
        des = $('#recovertype').find("option:selected").text();
        if ($('#recovertype').val() == 2) {
            des += "：" + $('#oncetime').val();
        }
        $('.recoveryTimeDes').html(des);
        $('.recoveryTimeDes').prop('title', des);
    }

    /**
     * 传输数据压缩
     */
    let switchCompress = function(){
        if(this.checked){
            $('.transferCompressGradeDiv').show();
        }else{
            $('.transferCompressGradeDiv').hide();
        }
    }

    //初始化appliance下拉框
    var initApplianceSelect = function(){
        $.fn.initApplianceSelect(currentPlatformUuid);
    }

    //初始化xhere块存储策略
    var initXhereVolumePolicyList = function (node) {
        if (preNode != '' && preNode.vcuuid == node.vcuuid) {
            // 那么不重复请求了
            var policy_id_list = preNodeList;
        } else {
            var policy_id_list = initXhereVolumePolicyLists(node);
        }
        preNode = node;

        var validFlag = true;
        $.each(data.recover_info.vmconfigs, function (i, v) {
            $.each(v.storage, function (idx, val) {
                // 没有块存储策略或原有策略不在现有策略中
                if (!val.policy_id || !policy_id_list.includes(parseInt(val.policy_id))) {
                    validFlag = false;
                    UIToastr.showWarning(LANG.UI_VM_SETTING_V2_SELECT_BLOCK_POLICY, LANG.UI_VM_SETTING_V2_SELECT_BLOCK_POLICY_TIPS);
                    return false;
                }
            });
            if (!validFlag) return false;
        });
        return validFlag;
    }

    // 初始化存储块的所有列表
    var initXhereVolumePolicyLists = function (node) {
        let p = {platform_uuid: node.vcuuid};
        pAjaxRequest(p, "/api/v1/vm/xhere/volume_policy", "GET", function (d) {
            var policy_list = d.data.rows;
            let policy_id_list = policy_list.map(item => parseInt(item.uuid));
            preNodeList = policy_id_list;
            return policy_id_list;
        }, false);
    }

    // 显示加密算法
    var agenttransferEncryptChange = function(){
        if (pointType == 3) {
            // cdp实时，不显示
            return;
        }
        if(this.checked){
            $('.transfer-agent-encrypt-method-form').show();
        }else{
            $('.transfer-agent-encrypt-method-form').hide();
        }
    }

    //修改颜色
    var initStrategyDesStyle = function(div, des, oldDes){
        if(oldDes != des){
            div.addClass('font-green-seagreen');
        }else{
            div.removeClass('font-green-seagreen');
        }
    }

    // 渲染上一步下一步
    var wizardInit = function(){
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            jQuery('li', $('#vmrecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#vmrecovercontent').find('.button-previous').css('visibility', 'hidden');
                $('#vmrecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#vmrecovercontent').find('.button-previous').css('visibility', 'visible');
                $('#vmrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#vmrecovercontent').find('.button-next').hide();
                $('#vmrecovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#vmrecovercontent').find('.button-next').show();
                $('#vmrecovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#vmrecovercontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
                    case 1:
                        if(step1Valid() == false){
                            return false;
                        }
                        break;
                    case 2:
                        if(step2Valid() == false){
                            return false;
                        }
                        break;
                    case 3:
                        if(step3Valid() == false){
                            return false;
                        }
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();
                // 还原tab-pane的高度
                $(".tab-pane__row").css('height', '100%');
                $('#vmrecovercontent').find('.button-next').prop('disabled', false);
                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#vmrecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#vmrecovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#vmrecovercontent .button-submit').click(submit).css('visibility', 'hidden');
    };

    // 获取跨平台恢复功能授权的目标虚拟化类型
    var getV2vAuth = function (choose){
        var p = {
            type: 'v2v',
            array: choose
        }
        pAjaxRequest(p, "/api/v1/system/auth/base_info", "GET", function (d) {
            var data = d.data
            if (!data.includes(1001) && pointType == 1){
                // 不包含整机 那么屏蔽整机显示, 并且不是整机的时间点
                $('#radio_group_1').find('label[value="completeMachineRecovery"]').hide();
            }
            if (getIntersection(CONF.VMTYPE_GROUP.PRIVATECLOUD, data).length <= 0) {
                // 没有私有云的虚拟化类型
                $('#radio_group_1').find('label[value="vmRecovery2"]').hide();
            }
            if (getIntersection(CONF.VMTYPE_GROUP.PUBLICCLOUD, data).length <= 0) {
                // 没有公有云的虚拟化类型
                $('#radio_group_1').find('label[value="vmRecovery3"]').hide();
            }

            // 去除整机的情况
            data = $.grep(data, function(value) {
                return value !== 1001;
            });

            if (getIntersection(CONF.VMTYPE_GROUP.PRIVATECLOUD, data).length + getIntersection(CONF.VMTYPE_GROUP.PUBLICCLOUD, data).length == data.length) {
                // 如果全是私有云和公有云，那么就表示没得虚拟化
                $('#radio_group_1').find('label[value="vmRecovery"]').hide();
            }
        })
    }
    function getIntersection(arr1, arr2) {
        var set = new Set(arr2);
        return arr1.filter(value => set.has(value));
    }

    // 第一步选择时间点验证
    var step1Valid = function(){
        if (!isLicense) {
            // 授权失效。不允许下一步
            UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE,  LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
            return false;
        }
        var info = RecoverTimepoint.getInfo();
        if(info.point_info == undefined || info.point_info.length <= 0){
            // 未选择时间点
            return false;
        }

        // 计算下可以跨平台到什么虚拟化 整机1001
        var choose = [];
        for (var c in info.point_info) {
            var hypervisor = info.point_info[c].hypervisor == 0 ? 1001 : info.point_info[c].hypervisor;
            choose.push(hypervisor);
            if (info.point_info[c].storage_type == 10) {
                isTape = true;
            }
        }
        getV2vAuth(choose.join(','));

        if (!arraysDeepEqual(data.point_info, info.point_info)) {
            // 有改变需要重新初始化
            // 清空下虚拟化的数据
            $('#host_tree').html('');
            $('#driverCheck_vm').html('');
            $('#driverCheckFailContinue').hide();
            $('#accordionvm').html('');
            $('#vmconfigs').hide();
            $('#awsconfigs').hide();
            $('#driverCheck_vm_button').hide();
            // 清空整机的数据
            $(".targetBox").html("");
            // 初始化虚拟化
            vmRecoverConfig.init({jobType: 1, pointType: pointType});
            // 初始化整机
            clientRecoverConfig.init({jobType: 1, pointType: pointType});
        }
        //设置storageuuid
        var storageuuid = $("#storageselect").val();
        data.point_info = info.point_info;
        data.storageuuid =  storageuuid;
        data.type = info.type

       // initHostTree();
        showStep1(info.show_str);
        data.node_uuid = data.point_info[0].node_uuid;
        // 根据时间点列表获取可用的节点
        let nodeUuid = getAvailableNodeByTimepoints(data.point_info);
        initBackupServerAddr(nodeUuid);	//初始化备份系统节点IP
        initResourceLimit([ data.node_uuid]); // 初始化忽略资源限制表格
        $('#vmrecovercontent').find('.button-next').prop('disabled', true);
        initTaskName(); // 初始化任务名称
        //设置重连时间和次数的默认值
        $('#agent_reconnect_time').val(5);
        $('#agent_reconnect_interval').val(5);

        $('#reconnect_time').val(5);
        $('#reconnect_interval').val(5);
        return true;
    }

    var showStep1 = function(nodes){
        var str = '';
        $.each(nodes, function(i, d){
            str += d;
        });
        $('.vmtypeshow').html(str);
    }
    // 第2步目标
    var step2Valid = function(){
        // 获取被点击的标签的 value 值
        var noNetwork = false;// 恢复到无网环境 ，拥有
        $('.retry_strategy').show();
        $('.overload_protect').removeClass('active');
        $('#retry_config').show();
        $('#tab_overload_protect').removeClass('active');
        $('.retryShow').show();
        $('.renameshowdiv').show();

        if (_selectTargetType == '') {
            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_RECOVERY_SOURCE_GOALS_TIPS);
            return false;
        }

        if (!isTape && (CONF.FUNCTIONS.includes('virusKill') || CONF.FUNCTIONS.includes('integrity'))) {
            // 有病毒查杀功能，或有完整性校验功能 显示安全策略
            $('#safeShowDiv').show(); // 最后一步显示
            $('#tab_safe').attr('style', ''); // 策略内容
            $('.safeLi').show(); // 策略tab
        }

        // get all os_type
        var osTypeArr = [];
        if (_selectTargetType != 'completeMachineRecovery') {
            // 无代理
            var vmconfigs = vmRecoverConfig.getInfo();
            if (vmconfigs == false) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_CHOOSE_VIRT_TYPE);
                return false;
            }

            if(CONF.VMTYPE_GROUP.OPENSTACK.includes(vmconfigs._hypervisor)){
                // 如果是私有云
                data.recover_info.vcenteruuid = vmconfigs.node.vcuuid;
                data.recover_info.groupname = vmconfigs.node.name;
                data.recover_info.groupuuid = vmconfigs.node.groupuuid;
                data.recover_info.username = vmconfigs.node.username;
                data.recover_info.password = vmconfigs.node.password;
                data.recover_info.hypervisor = vmconfigs.node.hypervisor;

                data.cross_platform_appliance_uuid = '';
            }
            data.recover_info.driver_replace_flag = vmconfigs.driver_replace_flag;

            data.recover_info.node = vmconfigs.node;
            if (vmconfigs.config == false || vmconfigs.config.length == 0) {
                // 插件会有提醒
                return false;
            }

            if (vmconfigs.show_fail != undefined) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_COMPLETE_DRIVER);
                return false;
            }

            data.recover_info.vmconfigs = vmconfigs.config; // 虚拟机配置列表
            $('.recovershow').html(vmconfigs.title);
            _hypervisor = vmconfigs._hypervisor
            data.sub_module_type = vmconfigs.sub_module_type;
            if (vmconfigs.driverCheck != undefined) {
                data.driver_check = vmconfigs.driverCheck;
            } else {
                data.driver_check = [];
            }

            // new
            data.recover_info.recover2 = 2;	//异机恢复,原机恢复去掉
            data.cross_platform_appliance_uuid = '';

            //xhere块存储策略必选
            if (CONF.VM_TYPE.XHERE == _hypervisor) {
                if (!initXhereVolumePolicyList(data.recover_info.node)) {
                    return false;
                }
            }

            data.recover_info.names = [];
            // 初始化还原
            $.fn.vmRecoveryResetOptions();
            if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(_hypervisor)) {
                // 公有云初始化传输策略
                $.fn.initAwsRecoveryTransferStrategy({
                    target_hypervisor: vmconfigs.config.hypervisor_type,
                    instance_configs: vmconfigs.config.instance_configs,
                    platform_uuid: vmconfigs.config.platform_uuid
                });
                $('.public_cloud-transport-strategy').show();
                $('.vm-transport-strategy').hide();
                // 没得重置主机名配置
                $('.renameshowdiv').hide();
            } else {
                // 私有云和虚拟机
                recoveryTypeShow({
                    _sourceHypervisor: data.point_info.type,
                    _hypervisor: _hypervisor,
                    authFun: authFun,
                    storage_type: vmconfigs.node.storage_type
                });
                $('.public_cloud-transport-strategy').hide();
                $('.vm-transport-strategy').show();

                // 重置主机名显示
                // 组装下时间点数组
                var points = [];
                for(var k in data.point_info) {
                    points[data.point_info[k].timepoint_uuid] = data.point_info[k].host_name;
                }
                var vmconfigs = data.recover_info.vmconfigs;
                var rename = '';
                for (var j in vmconfigs) {
                    rename += points[vmconfigs[j].timepointuuid] + '->';
                    if(vmconfigs[j].other.reset_hostname != undefined && vmconfigs[j].other.reset_hostname) {
                        rename += vmconfigs[j].other.new_hostname + '</br>';
                    } else {
                        rename += getSwitchDes(vmconfigs[j].other.reset_hostname) + '</br>';
                    }
                }
                $('.renameshow').html(rename);
            }
            $('.os_final_div').hide();
            $('.vm_final_div').show();

            let configs = data.recover_info.vmconfigs;
            if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(_hypervisor)) {
                // 公有云
                configs = data.recover_info.vmconfigs.instance_configs;
            }
            $.each(configs, function (i, v) {
                var ostype = v.os_type;
                let osType = ostype.charAt(0).toUpperCase() + ostype.slice(1).toLowerCase();
                if ($.inArray(osType, osTypeArr) === -1) {
                    osTypeArr.push(osType);
                }
            });
        } else {
            // 有代理
            data.recover_info = clientRecoverConfig.getInfo();
            // 如果一个磁盘都没选择的话，是不允许的
            if (data.recover_info[0].recovery_strategy == undefined) {
                //UIToastr.showError('目标设备配置', '请选择恢复的目标设备');
                return false;
            }
            if (data.recover_info[0].recovery_strategy.length <= 0) {
                UIToastr.showError(LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG, LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG_CHOOSE_ONE);
                return false;
            }
            if (data.recover_info == false) {
                return false;
            }
            var recovershow = '';
            for (var j in data.recover_info) {
                recovershow += data.recover_info[j].agent_name + '</br>';
            }
            $('.recovershow').html(recovershow);

            noNetwork = true; // 没有恢复到无网环境

            if (pointType == 3) {
                $('.retry_strategy').hide();
                $('.overload_protect').addClass('active');
                $('#retry_config').hide();
                $('#tab_overload_protect').addClass('active');
                $('.retryShow').hide();
            }

            // 重置主机名显示
            var rename = '';
            for (var j in data.recover_info) {
                rename += data.recover_info[j].point_name + '->';
                if(data.recover_info[j].rename_host) {
                    rename += data.recover_info[j].new_host_name + '</br>';
                } else {
                    rename += getSwitchDes(data.recover_info[j].rename_host) + '</br>';
                }
            }
            $('.renameshow').html(rename);
            $('.os_final_div').show();
            $('.vm_final_div').hide();
            // 显示到整机的目标信息
            showMachine()

            if ((!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity')) || pointType == 3 || isTape) {
                // 既没有病毒查杀功能，也没有完整性校验功能,或者是实时的点到整机,或者是磁带的存储，那么隐藏安全策略
                $('#safeShowDiv').hide(); // 最后一步显示
                $('#tab_safe').hide(); // 策略内容
                $('.safeLi').hide(); // 策略tab
                if ($('.safeLi').hasClass('active')) {
                    $('.safeLi').removeClass('active');
                    $('#tab_safe').removeClass('active');
                    $('#tab_safe').attr('style', '');
                    $('.commonLi').addClass('active');
                    $('#tab_common').addClass('active');
                    $('#tab_common').show();
                }
            }
            $.each(data.recover_info, function (i, v) {
                var ostype = v.os_type;
                let osType = ostype.charAt(0).toUpperCase() + ostype.slice(1).toLowerCase();
                if ($.inArray(osType, osTypeArr) === -1) {
                    osTypeArr.push(osType);
                }
            });
        }

        var noNetFlag = false;
        if (_selectTargetType != 'completeMachineRecovery') {
            // 无代理
            types = CONF.MODULE_TYPE.VM;
            // 判断下此时选择的虚拟化类型
            noNetFlag = _hypervisor == 47 || _hypervisor == 12;
            noNetwork = noNetFlag;
        }

        // 完整性校验组件
        if (CONF.FUNCTIONS.includes('integrity') && pointType != 3) {
            // 有完整性校验功能 并且时间点不是实时的
            // 时间点若有一个启用了完整性校验则恢复任务也启用
            let backup_disable_flag = true;
            $.each(data.point_info, function (i, d) {
                if (pointType == 1) {
                    if (d.integrity_check_flag) {
                        backup_disable_flag = false;
                        return true;
                    }
                } else if (d.integrity_check_flag == 1) {
                    backup_disable_flag = false;
                    return true;
                }
            });
            var types = CONF.MODULE_TYPE.OS;
            $('#completeConfig').completeStrategyCovery(types, 0, backup_disable_flag, noNetFlag);
        }

        // 定义病毒检测组件
        if (CONF.FUNCTIONS.includes('virusKill') && !(pointType == 3 && _selectTargetType == 'completeMachineRecovery') && !isTape) {
            // 有病毒查杀功能并且不是实时的点,并且是无代理,并且不是磁带的存储
            var virus_status = [];
            for (var k in data.point_info) {
                // 取出所有的备份点的健康状态
                var virus_scan_status = data.point_info[k].virus_scan_status;
                if (virus_scan_status < 2) {
                    virus_scan_status = 1;
                }
                virus_status.push(virus_scan_status);
            }

            // osTypeArr只有Linux则os_type=Linux，只有Windows则=Windows，其余情况=Other
            let os_types;
            if (osTypeArr.includes('Linux') && !osTypeArr.includes('Windows') && !osTypeArr.includes('Other')) {
                os_types = 'Linux';
            } else if (osTypeArr.includes('Windows') && !osTypeArr.includes('Linux') && !osTypeArr.includes('Other')) {
                os_types = 'Windows';
            } else {
                os_types = 'Other';
            }
            // 根据所有时间点的状态初始化安全策略
            $('#virusConfig').virusDetectionCover(
                virus_status.includes(1),
                virus_status.includes(2),
                virus_status.includes(3) || virus_status.includes(4),
                noNetwork,
                {
                    no_scan: {
                        os_type: os_types
                    },
                    healthy: {
                        os_type: os_types,
                    },
                    reflected: {
                        // 已感染但未完成扫描时状态改为4
                        virus_scan_status: virus_status.includes(4) ? $.fn.virusDefine.virus_scan_status.infected_but_unfinished : $.fn.virusDefine.virus_scan_status.infected,
                        os_type: os_types,
                    }
                }
            );
        }
        getTimeDes();
        return true;
    }

    // 有代理恢复目标显示
    var showMachine = function (){
        //恢复信息----
        let recoveryInfoDes = "";
        //得到恢复源信息
        let recovery_oss_info = data.recover_info;
        for (var i = 0; i < recovery_oss_info.length; i++) {
            //获取时间点名称
            recoveryInfoDes += ">>>"+recovery_oss_info[i].point_name +'<br>';
            //目标主机
            recoveryInfoDes += LANG.UI_OS_TARGET_HOST + ": "+recovery_oss_info[i].agent_name+'<br>';
            //恢复磁盘
            recoveryInfoDes += LANG.UI_MACHINE_OS_RECOVERT_DISK+": ";
            for(let j=0; j < recovery_oss_info[i].recovery_strategy.length; j++){
                recoveryInfoDes += recovery_oss_info[i].recovery_strategy[j].source_name+'->'+recovery_oss_info[i].recovery_strategy[j].destination_name+"; ";
            }
            recoveryInfoDes +="<br>";

            // 获取被点击的标签的 value 值
            let clickedValue =$('[name="recoverType"]').filter('.active').attr('value');
            if(clickedValue != 'dataVolumeRecovery'){
                //获取网络名称
                for(let j=0; j < recovery_oss_info[i].network_config.length; j++){
                    recoveryInfoDes += LANG.UI_CLIENT_NETWORK_NIC_NAME+": "+recovery_oss_info[i].network_config[j].net_name+';';
                }
                recoveryInfoDes += "<br>";
            }
            //获取引导模式
            let grub_method = recovery_oss_info[i].grub_method == 1 ? "BIOS" : "EFI";
            recoveryInfoDes += LANG.UI_VM_SETTING_V2_BOOT_TYPE +": "+ grub_method +'<br>';
            //获取主机重命名开关
            let rename_host = getSwitchDes(recovery_oss_info[i].rename_host);
            if(recovery_oss_info[i].rename_host){
                recoveryInfoDes += LANG.UI_MACHINE_OS_HOST_RENAME+": "+recovery_oss_info[i].new_host_name+'<br>';
            }else{
                recoveryInfoDes += LANG.UI_MACHINE_OS_HOST_RENAME+": "+rename_host+'<br>';
            }
            if(recovery_oss_info[i].network_name != ""){
                //获取传输网络
                recoveryInfoDes += LANG.UI_NODE_NETWORK_TRANSFER+": "+ recovery_oss_info[i].network_name +'<br>';
            }
            //恢复后执行脚本
            let script_info = recovery_oss_info[i].after_recovery_script_info;
            if(script_info.length == 0){
                recoveryInfoDes += LANG.UI_MACHINE_OS_EXECUTE_SCRIPT_RECOVERY+": "+LANG.UI_DB_BACKUP_UNSET+'<br>';
            }else{
                recoveryInfoDes += LANG.UI_MACHINE_OS_EXECUTE_SCRIPT_RECOVERY+": "+LANG.UI_DB_BACKUP_SET+'<br>';
            }
        }
        $(".recovershow2").html(recoveryInfoDes);
    }

    // 第3步恢复方式验证
    var step3Valid = function(){
        // 恢复方式
        data.type_info.type = parseInt($('#recovertype').val());
        data.type_info.strategy = {};
        data.type_info.strategy.start_time = $('#oncetime').val(); //定时恢复时间
        data.type_info.strategy.type = 4;
        if (data.type_info.type == 2 && data.type_info.strategy.start_time == '') {
            //1立即恢复  2定时恢复
            UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
            return false;
        }

        let reservetypeshow = '';
        reservetypeshow = $('.recoveryTimeDes').text();
        $('.reservetypeshow').html(reservetypeshow);

        //高级策略
        // 忽略资源限制
        let highstrategystr = $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes($('#ignore_resource_limit').get(0).checked);
        $('.highstrategyshow').html(highstrategystr);

        // 病毒扫描策略
        let virtus = '';
        if (CONF.FUNCTIONS.includes('virusKill') && !(pointType == 3 && _selectTargetType == 'completeMachineRecovery') && !isTape) {
            // 有病毒查杀功能 并且不是实时的点到整机,并且不是磁带的存储
            virtus = $('#virusConfig').getVirusDetectionCover();
            if (virtus === false) {  // 验证病毒扫描配置内容是否符合要求
                return false;
            }
        }

        // 完整性校验
        let integrity = '';
        if (CONF.FUNCTIONS.includes('integrity') && pointType != 3 && !isTape) {
            // 有完整性校验功能
            integrity = $('#completeConfig').getCompleteStrategyCovery();
        }

        // 显示病毒策略和完整性策略信息
        data.safe_config_strategy = safeData('',virtus, integrity)

        //获取高级策略----------------
        data.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;
        data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
        if (data.retry_strategy == false) {
            return false;
        }
        //限速策略
        data.speed_limit = getSpeedStrategyInfo();
        var speedlimitshow = $('.speedlimitshow');
        var speedLimitsStr = '';
        if (data.speed_limit.speedInfo.length != 0) {
            speedLimitsStr = $('.speedlimitDes').attr('title');
        }
        if (speedLimitsStr == '') {
            speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
        }
        speedlimitshow.html(speedLimitsStr);

        // 安全策略
        let safeInfo = '';
        if (CONF.FUNCTIONS.includes('virusKill') && !(pointType == 3 && _selectTargetType == 'completeMachineRecovery') && !isTape) {
            // 有病毒查杀功能 并且不是实时的点到整机，并且不是磁带的存储
            safeInfo = virtus.str;
        }
        if (CONF.FUNCTIONS.includes('integrity') && pointType != 3 && !isTape) {
            // 有完整性校验功能，并且不是实时的点，并且不是磁带的存储
            safeInfo += integrity.str;
        }
        $('.safeStrategyShow').html(safeInfo);

        if (_selectTargetType != 'completeMachineRecovery') {
            // 无代理
            if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(_hypervisor)) {
                // 公有云
                data.type_info.high.trasfer = $.fn.getAwsRecoveryTransferData();
                if (!data.type_info.high.trasfer) {
                    return false;
                }
                var des = $.fn.getAwsRecoveryTransferDes({storage_type:data.recover_info.node.storage_type});
                $('.transportinfoshow').html(des);
                return true;
            }

            // 从插件获取传输策略参数
            let transferData = $.fn.getVmRecoveryTransferData(data, {
                _hypervisor: _hypervisor,
                selectIPFlag: selectIPFlag
            });
            if (false === transferData) {
                return false;
            }
            data.type_info.high.trasfer.mode = transferData.mode;
            data.type_info.high.trasfer.encrypt = transferData.encrypt;
            data.type_info.high.trasfer.encrypt_method = transferData.encrypt_method;
            data.type_info.high.trasfer.reconnect_times = transferData.reconnect_times;
            data.type_info.high.trasfer.reconnect_interval = transferData.reconnect_interval;
            data.type_info.high.trasfer.transfer_compress = transferData.transfer_compress;
            data.appliancecheck = transferData.appliancecheck;
            data.high_info.threadnum = transferData.threadnum;
            data.agent_uuid = transferData.agent_uuid;
            data.agent_pool_uuid = transferData.agent_pool_uuid;
            data.backup_server_ip = transferData.backup_server_ip;
            data.transport_ip_segment = transferData.transport_ip_segment;
            data.type_info.high.trasfer.single_vm_parallel_disk_transfer_count = transferData.single_vm_parallel_disk_transfer_count;
            data.type_info.high.trasfer.vm_single_disk_parallel_transfer_count = transferData.vm_single_disk_parallel_transfer_count;
            data.keep_recovery_volumes = transferData.keep_recovery_volumes;
            data.typeInfo = data.type_info; // 因为组件里面需要取这个字段，所以冗余
            showStep3();
            // 传输策略
            $.fn.getVmRecoveryTransferDes(data);
            return true;
        }
        step3ValidAgent();
    }
    //第三步无代理恢复方式的显示
    var recoveryTypeShow = function(strategy){
        $.fn.vmRecoveryStep3Show(strategy);
    }
    var showStep3 = function(){
        data.type_info.type = $('#recovertype').val();

        var thread = $('#recoveryThreadNum').val();
        if (thread == "" || thread > 8 || thread <= 0) {
            $('#recoveryThreadDiv').spinner("value", 3);
            initHighStrategyDes();
            UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
            return false;
        }
    }

    // 单独的验证有代理的策略
    var step3ValidAgent = function (){
        //得到线程数量
        data.high_info.threadnum = $('#agent_recoveryThreadNum').val();
        if (!CONF.FUNCTIONS.includes('multithread')) {
            // 没得多线程，那么就默认为1
            data.high_info.threadnum = 1;
        }

        //得到传输加密
        data.high_info.transfer = {};
        data.high_info.transfer.encrypt = $('#agent_encrypttransfer').get(0).checked; //得到传输加密
        // 传输加密算法
        data.high_info.transfer.encrypt_method = parseInt($('#agent_transferEncryptMethod').val())
        if (pointType == 3) {
            // cdp 有额外的配置
            //得到传输加密
            data.high_info.transfer.compress = $('#cdp_tran_compress_switch').get(0).checked;
            // 获取压缩等级
            if(data.high_info.transfer.compress){
                data.high_info.transfer.compress_method = $("#cdp_transferCompressGrade option:selected").val();
            }
            // 获取传输包大小
            data.high_info.transfer.block_size = $("#transfer_datapackage_size option:selected").val() * 1024 * 1024;
        }
        showStep3agent();
        return true;
    }

    function isLatinCode(string) {
        var latin1Regex = /[^\x00-\xFF]/;
        if(latin1Regex.test(string)){
            return true;
        }
        return false;
    }

    // 单独的渲染有代理的策略
    var showStep3agent = function (){
        var des = LANG.UI_COPY_BACK_ENCRYPT + ": " + getSwitchDes(data.high_info.transfer.encrypt) + '<br>';
        // 传输加密算法 cdp暂时隐藏
        if($('#agent_encrypttransfer').get(0).checked && pointType != 3){
            let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#agent_transferEncryptMethod').val());
            let grade = '';
            switch (method) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    break;
            };
            des += encryptedMethodLabel + ": " + grade + "<br>";
        }
        if (pointType == 3) {
            // cdp 实时
            des += $('.cdp_transferlabel').html() + ':' + $('#cdp_transport_mode option:selected').text() + "<br>";
            des += $('.cdp_transfercompress').html() + ':' + getSwitchDes($('#cdp_tran_compress_switch').get(0).checked) + "<br>";
            if ($('#cdp_tran_compress_switch').get(0).checked) {
                des += $('.cdp_transfer-compress-grade-label').html() + ':' + $('#cdp_transferCompressGrade option:selected').text() + "<br>";
            }
            des += $('.cdp_transferdatapackage').html() + ':' + $('#transfer_datapackage_size option:selected').text() + "<br>";
        }

        if (CONF.FUNCTIONS.includes('multithread')) {
            // 有多线程
            des += $('.agent-transport-strategy .threadnumlabel').html() + ':' + data.high_info.threadnum;
        }

        $('.transportinfoshow').html(des);
        $('.applianceshow').hide();
        // $('#highstrategyshowdiv').hide();
    }

    const getAvailableNodeByTimepoints = timepoints => {
        let nodeUuid = '';
        let timepointUuids = timepoints.map(item => {return item.timepoint_uuid});
        pAjaxRequest({timepoints_uuid: timepointUuids}, "api/v1/nodes/timepoints/node", "GET", function (d) {
            nodeUuid = d.data.node_uuid;
        }, false);
        return nodeUuid;
    }

    //初始化备份系统节点IP
    var initBackupServerAddr = function(nodeuuid){
        var data = {};
        data.node_uuid = $('#nodeselect').val();
        //未获取到节点信息，直接获取时间点所在的节点
        if(data.node_uuid == "" || data.node_uuid == undefined || data.node_uuid == 0){
            data.node_uuid = nodeuuid;
        }
        pAjaxRequest(data, "/api/v1/nodes/all_ip", "GET", function (d) {
            var data = d.data.rows;
            var serveripaddr = $('#systemIp');
            serveripaddr.empty();
            for(var i=0; i<data.length; i++){
                var option = $("<option>").text(data[i]).val(data[i]);
                serveripaddr.append(option);
            }
        }, false);
        $('#inputIp').val(window.location.host);
    }
    //验证密码

    //初始化高级策略描述信息
    var initHighStrategyDes = function(){
        var des = "";
        des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#recoveryThreadNum').val();
        var strategyIndex = $('#strategySelect').val();
        if(strategyIndex && strategyIndex != "" && editFlag){
            var oldDes = globalStrategy[strategyIndex].high.des;
            initStrategyDesStyle($('.recoveryHighDes'), des, oldDes);
        }else{
            $('.recoveryHighDes').removeClass('font-green-seagreen');
        }
        $('.recoveryHighDes').html(des);
        $('.recoveryHighDes').prop('title', des);
    }

    //初始化时间策略
    var initStrategy = function(){
        pAjaxRequest({}, "/api/v1/jobs/time_crow_list", "GET", function (result) {
            var jsonData = result.data;
            if(jsonData.time_list.length != 0){
                $('#backupCrowd').taskCrowd({timeList:jsonData.time_list, showFlag: jsonData.show_flag});
            }
            var suggestInfo = jsonData.suggest_time;
            var strategy = [];
            strategy[0] = {
                mode: 7,
                strategy_type: 2,
                days: [0, 0, 0, 0, 1, 0, 0],
                start_time: suggestInfo.start_time,
                roll_flag: false,
                roll_interval: '01:00:00',
                roll_end_time: suggestInfo.roll_end_time,
                frequency: ''
            };
            //延迟设置,因为这里icheck会默认修改里面的选中事件
            setTimeout(function(){
                $('#recoveryTimestrategy').strategy({dom: $('#recoveryTimestrategy'), config: strategy, backup_flag: 2});
                $('.rollDiv').hide(); //隐藏滚动执行
            }, 2000);
        }, false);
    }

    // 获取确认配置的加密传输描述
    var getEncryptDes = function () {
        let encryptDes = $('.encrypttransferlabel').html() + ": " + getSwitchDes(data.type_info.high.trasfer.encrypt) + "<br>";
        // 传输加密算法
        if ($('#agent_encrypttransfer').get(0).checked) {
            let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#agent_transferEncryptMethod').val());
            let grade = '';
            switch (method) {
                case 1:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                    break;
                case 2:
                    grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                    break;
            }
            encryptDes += encryptedMethodLabel + ": " + grade + "<br>";
        }
        return encryptDes;
    }

    var initSpinner = function(){
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
        $('#recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
        $('#agent_recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
    }

    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function(check){
        if(check){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    //版本差异处理,主要是处理标准版本功能限制
    // 中文标准版 1
    // 中文企业版 2
    // 英文免费版 4
    // 英文基础版 9
    // 英文标准版 6
    // 英文企业版 7
    var initSoftwareVersionDiff = function(){
        pAjaxRequest({}, "/api/v1/users/auth_func", "GET", function (d) {
            let data = d.data;
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                authFun = data;
            }

            if(!data.lanfree){
                //传输模式只支持网络传输
                //删除transport_mode不可用项
                $("#transport_mode option[value='san']").remove();
                //删除xentransmode不可用项
                $("#xentransmode option[value='2']").remove();
                $("#xentransmode option[value='4']").remove();

                //删除类xen不可用项
                $("#leixentransmode option[value='2']").remove();

                //删除华为传输模式不可用项
                $("#huaweitransport_mode option[value='san']").remove();

                //华为KVM
                $("#huaweikvmtransport_mode option[value='2']").remove();
                //红帽传输模式
                $("#redhattransmode option[value='2']").remove();
            }

            if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
                //传输模式只支持网络传输
                //删除transport_mode不可用项
                $("#transport_mode option[value='san']").remove();
                $("#transport_mode option[value='hotadd']").remove();
                //删除xentransmode不可用项
                $("#xentransmode option[value='2']").remove();
                $("#xentransmode option[value='4']").remove();

                //删除类xen不可用项
                $("#leixentransmode option[value='2']").remove();

                //删除华为传输模式不可用项
                $("#huaweitransport_mode option[value='san']").remove();

            }
        }, false);
    }

    // 比较数组
    function deepEqual(obj1, obj2) {
        if (obj1 === obj2) return true;

        if (typeof obj1 !== 'object' || obj1 === null || typeof obj2 !== 'object' || obj2 === null) {
            return false;
        }

        const keys1 = Object.keys(obj1);
        const keys2 = Object.keys(obj2);

        if (keys1.length !== keys2.length) {
            return false;
        }

        for (let key of keys1) {
            if (!keys2.includes(key) || !deepEqual(obj1[key], obj2[key])) {
                return false;
            }
        }

        return true;
    }
    // 递归的去比较数组元素
    function arraysDeepEqual(arr1, arr2) {
        if (!Array.isArray(arr1) || !Array.isArray(arr2) || arr1.length !== arr2.length) {
            return false;
        }

        for (let i = 0; i < arr1.length; i++) {
            if (!deepEqual(arr1[i], arr2[i])) {
                return false;
            }
        }

        return true;
    }

    //初始化任务名
    var initTaskName = function(){
        if ($('#job_name').val() != '') {
            return;
        }
        var recovery_type = $('#recovery_type').val();
        var subtype = $('#subtype').val();
        var msg = '';
        if (recovery_type == 'vm') {
            // 虚拟化
            if (subtype == 2) {
                // 私有云
                msg = 'UI_PLATFORM_RECOVERY_PRCLOUD_JOB_';
            } else if (subtype == 3) {
                // 公有云
                msg = 'UI_PLATFORM_RECOVERY_AWS_JOB_';
            } else {
                // 虚拟机
                msg = 'UI_PLATFORM_RECOVERY_VM_JOB_';
            }
        } else if (recovery_type == 'cdp') {
            // 实时
            msg = 'UI_PLATFORM_RECOVERY_VOL_MACHINE_JOB_';
        } else {
            // 整机
            msg = 'UI_PLATFORM_RECOVERY_MACHINE_JOB_';
        }
        //设置任务名
        pAjaxRequest({job_name: msg}, '/api/v1/jobs/name', 'GET', function (result){
            var job_name = LANG.UI_PLATFORM_RECOVERY_NAME + '1';
            if (result.success) {
                job_name = result.data.value;
            }
            $('#job_name').val(job_name);
        }, false)
    }

    //初始化日期选择插件
    var inintDatatimePicker = function(){
        //设置时间
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            //英文独有的
            $(".form_datetime").datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }else{
            $(".form_datetime").datetimepicker({
                language:  'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }
        $('#resetdate').on('click', function () {
            $('#oncetime').val('');
            getTimeDes();
        });
        $('#oncetime').on('change', getTimeDes);
    }

    return {
        init: function () {
            wizardInit();// 初始化上、下一步
            pointType = 1;
            if ($('#recovery_type').val() == 'cdp') {
                pointType = 3;
            } else if ($('#recovery_type').val() == 'os') {
                pointType = 2;
            }
            initListener();
            handleValidation(); // 验证
            initStrategy(); // 初始化时间策略
            initApplianceSelect(); // 初始化appliance下拉框
            initSoftwareVersionDiff();
            initSpinner();
            inintDatatimePicker();
           // calcStyle();
        },
    };
}();
jQuery(document).ready(function () {
    RecoverPlatform.init();
});
