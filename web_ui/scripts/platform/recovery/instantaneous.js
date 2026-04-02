var RecoverInstantaneous = function () {
    var vmRecoverConfig = vmRecoverConfigs();
    var data = {
        instant_target_info:{},
        instant_web_config:{},
        node_uuid: ''
    };
    let _selectTargetType = '';          // 选择目标类型
    var taskUuid = ''; // 表明是否是更改
    var openstackIP = false; // 是否是瞬时恢复到私有云判断
    var hypervisor_type = 0;
    var openstackIPValue = ''; // 记录此时设置的值
    var openstackIPResult = false;// 是否联通成功
    var pointType = 1; // 时间点类型
    var vmNameLimit = {len: '', limit: '', msg: '', dis_len: '', disk_limit: '', disk_msg: ''};
    var authConfig = {};
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
        // 输入验证
        let jobName2 = jobName.replace('/', '');
        if(!customInputValidate('string',jobName2, $('#job_name'))){
            return false;
        }
        data.job_name = jobName;
        if(data.job_name == ''){
            return UIToastr.showWarning(LANG.UI_INSTANT_NAME, LANG.UI_MOTION_INPUT_TASKNAME);
        }
        // 进行ajax请求
        var url = '/api/v1/recovery/instantaneous/agentlessis'; // 无代理
        if (_selectTargetType == 'completeMachineRecovery') {
            // 有代理
            url = '/api/v1/recovery/instantaneous/agent';
        }
        submittedFlag = true;
        Metronic.blockUI({target: '#vmrecovercontent',animate: true,cenrerY: true,});
        pAjaxRequest(data, url, "POST", function (result) {
            Metronic.unblockUI('#vmrecovercontent');
            if (result.code == -1) {
                submittedFlag = false;
                return UIToastr.showError(LANG.UI_INSTANT_NAME, result.msg);
            }
            if (operateResponseList(result, LANG.UI_INSTANT_NAME)){
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
                    newdiskname: true,
                    uniquename: true
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

        // 单个vm的磁盘名不能重复
        $.validator.addMethod("uniquename", function(value, element) {
            $.validator.messages.uniquename = LANG.UI_INSTANT_DISK_NAME_UNIQUE_TIPS;
            let _this = $(element);
            let _input = _this.closest(`.getHoststorage`).find(`input[name=diskname]`);
            let count = 0; // 记录相同磁盘名的个数
            $.each(_input, function () {
                if (value == $.trim($(this).val())) {
                    count++;
                }
            });
            return count < 2;
        });
    };

    //事件监听
    var initListener = function(){
        getAuth();
        // 切换目标类型
        $('#radio_group_1').on('change',function(e,oldValue, newValue){
            _selectTargetType = newValue;
            if(newValue == 'completeMachineRecovery'){
                // 整机
                $('#show_completeMachineRecovery').show();
                $('#show_vmRecovery').hide();
            }else{
                // 虚拟化 私有云 公有云
                $('#show_completeMachineRecovery').hide();
                $('#show_vmRecovery').show();

                // 清空下虚拟化的数据
                $('#host_tree').html('');
                $('#driverCheck_vm').html('');
                $('#driverCheck_vm_button').hide();
                $('#driverCheckFailContinue').hide();
                $('#accordionvm').html('');
                $('#vmconfigs').hide();
                $('#unifysettingdiv').hide();
                // 初始化虚拟化
                var inits = {showDriver: true, jobType: 2, pointType: pointType};
                if(newValue == 'vmRecovery3'){
                    // 公有云
                    inits.showType = 3;
                }else if(newValue == 'vmRecovery2'){
                    // 私有云
                    inits.showType = 2;
                    $(`.vm-text`).hide();
                    $(`.prcloud-text`).show();
                }else if(newValue == 'vmRecoverys'){
                    // 容灾演练平台
                    inits.showType = 4;
                }else {
                    // 虚拟化平台
                    inits.showType = 1;
                    $(`.vm-text`).show();
                    $(`.prcloud-text`).hide();
                }
                // 初始化虚拟化 不需要驱动检测
                vmRecoverConfig.init(inits);
            }
        })

        // 私有云控制节点ip测试连接
        $('input[name=openstackIP]').on('change', () => {
            var val = $('input[name=openstackIP]').val();
            if (openstackIP) {
                if (!openstackIPResult) {
                    // 直接没联通
                    $("#testIP").find("i").remove();
                } else if (openstackIPValue != val && openstackIPResult){
                    // 联通了，但是值后面又变了
                    $("#testIP").find("i").remove();
                } else {
                    var iTag = '<i class="levelchild viconfont vicon-a-Checkxiaoyan" style="margin-right: 5px;"></i>';
                    $("#testIP").html(iTag + $("#testIP").text());
                }
            }
        })

        $('#testIP').on('click', function(){
            var ip = $('input[name=openstackIP]').val();
            if (ip) {
                testCheck();
            }else{
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS,LANG.UI_VM_CONTROLLER_IP_INPUT_TIPS);
            }
        });

        var testCheck = function(){
            var ip = $('input[name=openstackIP]').val();
            openstackIPValue = ip;
            var datas = {
                'controller_ip': ip,
                'hypervisor_type': hypervisor_type
            };
            Metronic.blockUI({target: '.ipDiv',animate: true});
            pAjaxRequest(datas, "/api/v1/vm/test_controller_ip", "POST", function (d) {
                Metronic.unblockUI('.ipDiv');
                operateResponseList(d, LANG.UI_PUBLIC_TIPS);
                if (d.success) {
                    // 表示联通成功
                    openstackIPResult = true;
                    var iTag = '<i class="levelchild viconfont vicon-a-Checkxiaoyan" style="margin-right: 5px;"></i>';
                    $("#testIP").html(iTag + $("#testIP").text());
                } else {
                    $("#testIP").find("i").remove();
                    openstackIPResult = false;
                }
            });
        }

        if (!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity')) {
            // 既没有病毒查杀功能，也没有完整性校验功能，那么隐藏安全策略
            $('#safeShowDiv').hide(); // 最后一步显示
            $('#tab_safe').hide(); // 策略内容
            $('.safeLi').hide(); // 策略tab
        }
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

            if (!data.includes(1000)){
                // 不包含内嵌，那么去掉内嵌显示
                $('#radio_group_1').find('label[value="vmRecoverys"]').hide();
            }

            // 去除整机和内嵌的情况
            data = $.grep(data, function(value) {
                return value !== 1001 && value !== 1000;
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
       //  console.log('info', info)
        if(info.point_info == undefined || info.point_info.length <= 0){
            // 未选择时间点
            return false;
        }

        // 计算下可以跨平台到什么虚拟化 整机1001
        var choose = [];
        for (var c in info.point_info) {
            var hypervisor = info.point_info[c].hypervisor == 0 ? 1001 : info.point_info[c].hypervisor;
            choose.push(hypervisor);
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
            // 清空整机的数据
            $(".targetBox").html("");
            // 初始化虚拟化
            vmRecoverConfig.init({showDriver: true, jobType: 2, pointType: pointType});
            //if (pointType == 1 && $('#subtype').val() == 3) {
            // 公有云的类型 屏蔽整机
            if (pointType == 1) {
                // 虚拟化时间点不允许瞬时恢复到整机
                $('#radio_group_1').find('label[value="completeMachineRecovery"]').hide();
            } else {
                // 初始化整机
                clientRecoverConfig.init({jobType: 2, pointType: pointType});
            }
            // 瞬时恢复不支持到公有云平台
            $('#radio_group_1').find('label[value="vmRecovery3"]').hide();

            // 忽略节点限制默认打开
            $('#ignore_resource_limit').bootstrapSwitch('state', true);
            initResourceLimit([ info.node_uuid]); // 初始化忽略资源限制表格
        }
        data.point_info = info.point_info;
        data.timepoint_uuid = info.point_info[0].timepoint_uuid;
        data.type = info.type
        data.node_uuid = info.node_uuid
        showStep1(info.show_str);
        $('#vmrecovercontent').find('.button-next').prop('disabled', true);
        initTaskName(); // 初始化任务名称
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

        if (_selectTargetType == '') {
            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_RECOVERY_SOURCE_GOALS_TIPS);
            return false;
        }

        var init = {'node_uuid': data.node_uuid};
        $('.show_domain_ip_point').show();
        $('.show_domain_ip_point2').hide();
        $('.amountinfoshow').parent().parent().show();
        $('#tab_common').show();
        $('.commonLi').show();
        $('.commonLi').addClass('active');
        $('#tab_common').addClass('active');
        $('.safeLi').removeClass('active');
        $('.safeLi').attr('style', '');
        $('#tab_safe').removeClass('active');
        $('#tab_safe').attr('style', '');
        $('#tab_common').attr('style', '');

        $('.highLi').removeClass('active');
        $('.highLi').attr('style', '');
        $('#tab_high').removeClass('active');
        $('#tab_high').attr('style', '');

        if ((!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity'))) {
            // 既没有病毒查杀功能，也没有完整性校验功能，那么隐藏安全策略
            $('#safeShowDiv').hide(); // 最后一步显示
            $('#tab_safe').hide(); // 策略内容
            $('.safeLi').hide(); // 策略tab
        }

        $('.ipDiv').hide();
        openstackIP = false;
        var noNetwork = false;// 恢复到无网环境 ，拥有
        // 获取被点击的标签的 value 值
        // get all os_type
        var osTypeArr = [];
        if (_selectTargetType != 'completeMachineRecovery') {
            // 无代理
            var vmconfigs = vmRecoverConfig.getInfo();
            if (vmconfigs == false) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_CHOOSE_VIRT_TYPE);
                return false;
            }

            if (vmconfigs.config == false || vmconfigs.config.length == 0) {
                // 插件会有提醒
                return false;
            }

            if (vmconfigs.show_fail != undefined) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_COMPLETE_DRIVER);
                return false;
            }

            // 恢复目标配置
            data.instant_target_info = {
                'target_type': 1,
                'target_uuid': vmconfigs.node.vcuuid,
                'hypervisor_type': vmconfigs.node.hypervisor,
                'host_uuid': vmconfigs.node.id,
                'region': CONF.VMTYPE_GROUP.OPENSTACK.includes(vmconfigs.node.hypervisor) ? vmconfigs.node.name : '',
            };
            hypervisor_type = vmconfigs.node.hypervisor;
            data.instant_web_config.vmconfigs =  vmconfigs.config; // 虚拟机配置列表
            data.instant_web_config.node = vmconfigs.node;
            data.sub_module_type = vmconfigs.sub_module_type;
            if (vmconfigs.driverCheck != undefined) {
                data.driver_check = vmconfigs.driverCheck;
            } else {
                data.driver_check = [];
            }
            $('.recovershow').html(vmconfigs.title);

            if (vmconfigs.node.hypervisor == 108) {
                // 如果是内嵌的话 需要自定义挂载协议
                init.protocol = [{'name':'iSCSI', 'value': 2}];
                // 并且需要把那个挂载点IP或域名改改
                $('.show_domain_ip_point').hide();
                $('.show_domain_ip_point2').show();
                // 并且没得通用配置策略
                $('.commonLi').hide();
                $('#tab_common').hide();
                $('.amountinfoshow').parent().parent().hide();
                $('.commonLi').removeClass('active');
                $('#tab_common').removeClass('active');
                $('#tab_safe').addClass('active');
                $('.safeLi').addClass('active');

                if (!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity')) {
                    // 因为只有安全策略，所以这里可以把完整性校验显示下,并且都没得的情况下
                    $('#tab_high').attr('style', ''); // 策略内容
                    $('#tab_high').show(); // 策略内容
                    $('.highLi').addClass('active');
                }
            }

            if(CONF.VMTYPE_GROUP.OPENSTACK.includes(vmconfigs.node.hypervisor)){
                // 恢复到私有云，需要显示控制节点ip并连接通过
                $('.ipDiv').show();
                openstackIP = true;
            }

            // 不支持iscsi协议的虚拟化类型有
            // vmware、hperv、xenserver、华为kvm、浪潮kvm、深信服hci（kvm）、深信服scp（kvm）、smartx（kvm）。
            // 	字节火山（kvm）、openstack(私有云)、zstack（私有云）、aws（公有云）、华为（公有云）、
            if (!($.inArray(vmconfigs.node.hypervisor, CONF.SUPPORT_ISCSI_INSTANT) !== -1)) {
                init.protocol = [{'name':'NFS', 'value': 1}];
            }
            var netFlag = hypervisor_type == 47 || hypervisor_type == 12;
            noNetwork = netFlag;
            // 完整性校验组件
            if (CONF.FUNCTIONS.includes('integrity')) {
                // 有完整性校验功能
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
                $('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.VM, 0, backup_disable_flag, netFlag);
            }
            let configs = data.instant_web_config.vmconfigs;
            if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(hypervisor_type)) {
                // 公有云
                configs = data.instant_web_config.vmconfigs.instance_configs;
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
            var recover_info = clientRecoverConfig.getInfo();
            if (recover_info == false) {
                return false;
            }
            data.instant_target_info = {
                'target_type': 2,
                'target_uuid': recover_info[0].agent_uuid,
                'hypervisor_type': 0,
                'host_uuid': '',
            };

            data.instant_web_config = recover_info[0]; // 宿主机配置列表
            var recovershow = '';
            for (var j in recover_info) {
                recovershow += recover_info[j].agent_name + '</br>';
            }
            $('.recovershow').html(recovershow);

            // 有代理的情况下 需要自定义挂载协议
            init.protocol = [{'name':'iSCSI', 'value': 2}];
            init.showLimit = false;
            init.showDiy = false;
            init.instant = true;

            noNetwork = true; // 没有恢复到无网环境
            // 完整性校验组件
            if (CONF.FUNCTIONS.includes('integrity')) {
                // 有完整性校验功能
                // 时间点若有一个启用了完整性校验则恢复任务也启用
                let complete_backup = true;
                $.each(data.point_info, function (i, d) {
                    if (pointType == 1) {
                        if (d.integrity_check_flag) {
                            complete_backup = false;
                            return true;
                        }
                    } else if (d.integrity_check_flag == 1) {
                        complete_backup = false;
                        return true;
                    }
                });
                $('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.OS, 0, complete_backup);
            }

            $.each(recover_info, function (i, v) {
                var ostype = v.os_type;
                let osType = ostype.charAt(0).toUpperCase() + ostype.slice(1).toLowerCase();
                if ($.inArray(osType, osTypeArr) === -1) {
                    osTypeArr.push(osType);
                }
            });
        }
        // 定义病毒检测组件
        if (CONF.FUNCTIONS.includes('virusKill')) {
            // 有病毒查杀功能
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

        // 初始化存储挂载点配置
        storageMount.init(init);
        return true;
    }

    // 第3步恢复策略
    var step3Valid = function(){
        // 需要判断下是否是恢复到私有云，并且控制节点连接通过
        if (openstackIP) {
            if (!openstackIPResult) {
                // 直接没联通
                UIToastr.showWarning(LANG.UI_VM_CONTROLLER_IP_INPUT, LANG.UI_VM_CONTROLLER_IP_INPUT_EMPTY_TIPS);
                return false;
            } else if (openstackIPValue != $('input[name=openstackIP]').val() && openstackIPResult){
                // 联通了，但是值后面又变了
                UIToastr.showWarning(LANG.UI_VM_CONTROLLER_IP_INPUT, LANG.UI_VM_CONTROLLER_IP_INPUT_FAIL_TIPS);
                return false;
            }
            data.instant_web_config.controllerip = openstackIPValue;
        }
		// 获取配置信息
        var info = storageMount.getAmountInfo();
		if (info == false || info.ip == null) {
			return false;
		} 
        data.mount_point_config = info;
        // 病毒扫描策略
        if (CONF.FUNCTIONS.includes('virusKill')) {
            // 有病毒查杀功能
            let virus_detection = $('#virusConfig').getVirusDetectionCover();
            if (virus_detection === false) {// 验证病毒扫描配置内容是否符合要求
                return false;
            }
        }

        showStep3();
        return true;
    }

    var showStep3 = function(){
        var showStr1 = LANG.UI_PLATFORM_RECOVERY_MOUNT_IP + '：' + data.mount_point_config.ip_domain + ';'+LANG.UI_PLATFORM_RECOVERY_MOUNT_PROTOCOL+'：'+data.mount_point_config.service_txt;
        if (data.mount_point_config.limit_ip != '') {
            showStr1 += ';'+LANG.UI_PLATFORM_RECOVERY_MOUNT_LIMIT_IP+'：' + data.mount_point_config.limit_ip;
        }

        $('.amountinfoshow').html(showStr1);
        // 病毒扫描策略
        let virtus = '';
        if (CONF.FUNCTIONS.includes('virusKill')) {
            // 有病毒查杀功能
            virtus = $('#virusConfig').getVirusDetectionCover();
        }
        // 完整性校验
        let integrity = '';
        if (CONF.FUNCTIONS.includes('integrity')) {
            // 有完整性校验功能
            integrity = $('#completeConfig').getCompleteStrategyCovery();
        }

        // 显示病毒策略和完整性策略信息
        data.safe_config_strategy = safeData('',virtus, integrity);

        // 安全策略
        let safeInfo = '';
        if (CONF.FUNCTIONS.includes('virusKill')) {
            safeInfo = virtus.str;
        }
        if (CONF.FUNCTIONS.includes('integrity')) {
            // 有完整性校验功能
            safeInfo += integrity.str;
        }
        $('.safeStrategyShow').html(safeInfo);

        //获取高级策略----------------
        data.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;

        // 忽略资源限制
        let highstrategystr = $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes($('#ignore_resource_limit').get(0).checked);
        $('.highstrategyshow').html(highstrategystr);

        return true;
    }

    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function(check){
        if(check){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
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
                msg = 'UI_PLATFORM_INSTANT_RECOVERY_PRCLOUD_JOB_';
            } else if (subtype == 3) {
                // 公有云
                msg = 'UI_PLATFORM_INSTANT_RECOVERY_AWS_JOB_';
            } else {
                // 虚拟机
                msg = 'UI_PLATFORM_INSTANT_RECOVERY_VM_JOB_';
            }
        } else {
            // 整机
            msg = 'UI_PLATFORM_INSTANT_RECOVERY_MACHINE_JOB_';
        }
        //设置任务名
        pAjaxRequest({job_name: msg}, '/api/v1/jobs/name', 'GET', function (result){
            var job_name = LANG.UI_VISUAL_INSTANT_TASK_NAME + '1';
            if (result.success) {
                job_name = result.data.value;
            }
            $('#job_name').val(job_name);
        }, false)
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

    // 修改获取旧数据
    var initOldData = function (){
        // 1 获取数据
        // 2 .把时间点默认选中，
        // 3.触发恢复目标的默认事件
        // 4。填充恢复策略信息
        // 5. 填充任务名称
        var platform_uuid = '6159a95a-4aad-4ec8-972f-d92149007d04';
        var timepoint_uuid = 'b47fb993-1629-4cc9-9e25-d10ba88a679d';
        var treeId = 'pointtypetree';
        var treeObj = $.fn.zTree.getZTreeObj(treeId);

        async function backOpen(nodes){
            nodes.forEach(function(node) {
                if (node.platform_uuid == platform_uuid) {
                    treeObj.expandNode(node, true, false, false);
                    if (node.clickshow != undefined) {
                        // 触发异步加载时间点事件，并等待加载完成
                        if (RecoverTimepoint.clickPoint(treeId, node)) {
                            // 异步加载完成后查找并选中目标节点
                            selectTimepointNode();
                        }
                    } else {
                        backOpen(node.children);
                    }
                }
            });
        }
        // 查找并选中具有指定UUID的时间点节点
        function selectTimepointNode() {
            var targetNode = treeObj.getNodeByParam("timepoint_uuid", timepoint_uuid, null);
            if (targetNode) {
                // 展开目标节点的所有父节点
                /*while (targetNode.getParentNode()) {
                    treeObj.expandNode(targetNode.getParentNode(), true, false, false);
                    targetNode = targetNode.getParentNode();
                }*/
                // 选中目标节点
                RecoverTimepoint.checkPoint(treeObj, treeId, targetNode);
            } else {
                console.error("Target node not found");
            }
        }
        backOpen(treeObj.getNodes());
    }



    return {
        init: function () {
            pointType = 1;
            if ($('#recovery_type').val() == 'cdp') {
                pointType = 3;
            } else if ($('#recovery_type').val() == 'os') {
                pointType = 2;
            }
            // 初始化时间点
            RecoverTimepoint.init({
                jobType: 2,
                instantaneous: true // 是否包含瞬时恢复快照点
            });
            wizardInit();// 初始化上、下一步
            initListener();
            handleValidation(); // 验证

           // calcStyle();
            taskUuid = $('#task_uuid').val();
            if (taskUuid) {
                // 那么表示是修改
                initOldData();
            }
        },
    };
}();
jQuery(document).ready(function () {
    RecoverInstantaneous.init();
});
