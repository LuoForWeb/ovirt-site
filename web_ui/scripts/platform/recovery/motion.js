var RecoverMotion = function () {
    // 迁移任务
    var data = {point_info:{}, migrate_web_config:{}, speed_limit:{}};
    var vmRecoverConfig = vmRecoverConfigs();
    var globalStrategy = [];
    var editFlag = false;
    let _selectTargetType = '';          // 选择目标类型
    var taskUuid; // 瞬时恢复任务uuid
    var nodeUuid = '';
    var pointType = 1;
    var pointInfo = {};
    var vmNameLimit = {len: '', limit: '', msg: '', dis_len: '', disk_limit: '', disk_msg: ''};
    var authConfig = {};
    var submittedFlag = false; // 是否已提交

    var submit = function(){
        if (submittedFlag) {
            return;
        }
        // 提交
        data.job_name = $('#job_name').val();
        data.task_uuid = taskUuid;
        if(data.job_name == ''){
            return UIToastr.showWarning(LANG.UI_SEARCH_RECOVER_TASK, LANG.UI_MOTION_INPUT_TASKNAME);
        }
        // 进行ajax请求
        var url = '/api/v1/recovery/migrates/agentlessis'; // 无代理
        if (_selectTargetType == 'completeMachineRecovery') {
            // 有代理
            url = '/api/v1/recovery/migrates/agent';
        }
        submittedFlag = true;
        Metronic.blockUI({target: '#vmrecovercontent',animate: true,cenrerY: true,});
        pAjaxRequest(data, url, "POST", function (result) {
            Metronic.unblockUI('#vmrecovercontent');
            if (result.code == -1) {
                submittedFlag = false;
                return UIToastr.showError(LANG.UI_SEARCH_RECOVER_TASK, result.msg);
            }
            if (operateResponseList(result, LANG.UI_SEARCH_RECOVER_TASK)){
                // 迁移任务创建完成应该进入任务列表
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
            ignore: "#searchvm",  // validate all fields including form hidden input
            rules: {
                vmname: {
                    required: true,
                    newvmname: true,
                },
                diskname: {
                    required: true,
                    newdiskname: true,
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

    // 根据任务uuid获取瞬时恢复任务选择的时间点信息和任务类型，有代理还是无代理
    var initPoint = function (){
        Metronic.blockUI({target: '#vmrecovercontent',animate: true,cenrerY: true,});
        // 根据瞬时恢复任务uuid获取信息 先阶段瞬时恢复不支持有代理
        let p = {job_uuid: taskUuid};
        pAjaxRequest(p, "/api/v1/recovery/job_points", "GET", function (d) {
            var points = d.data.rows;
            var info = points[0];
            info.vmOldName = info.vm_old_name; // 冗余字段，因为初始化虚拟机需要
            pointInfo = info;
            pointType = info['type']; // 时间点来源默认为无代理

            // 清空下虚拟化的数据
            $('#host_tree').html('');
            $('#driverCheck').html('');
            $('#accordionvm').html('');
            $('#vmconfigs').hide();
            // 清空整机的数据
            $(".targetBox").html("");

            // 计算下可以跨平台到什么虚拟化 整机1001
            var choose = [];
            for (var c in info.point_info) {
                var hypervisor = info.point_info[c].hypervisor_new == 0 ? 1001 : info.point_info[c].hypervisor_new;
                choose.push(hypervisor);
            }
            getV2vAuth(choose.join(','));

            if (info.point_info[0].hypervisor_new == 47) {
                // 瞬时恢复到深信服scp的机器1上面，那么迁移的时候也只能迁移到深信服的scp的机器1上面。
                // 屏蔽有代理
                $('#radio_group_1').find('label[value="completeMachineRecovery"]').hide();
                // 初始化虚拟化 .迁移到有代理需检测异构，无代理不需要
                vmRecoverConfig.init({'point': info, 'jobType': 3, pointType: pointType, showDriver: false, instantTaskUuid: taskUuid});
            } else {
                if (
                    ($.inArray(info.point_info[0].hypervisor_new, [0, 108]) !== -1 && pointType == 2)
                    || (info.point_info[0].hypervisor_new == 108 && info.point_info[0].hypervisor == 2)
                ) {
                    // 备份点是有代理，瞬时恢复的是内嵌和有代理,那么就只能是有代理, 需要隐藏掉无代理
                    // 如果备份点是hyper-v的虚拟化（2），瞬时恢复到内嵌，那么不能迁移到虚拟化，只能到整机
                    $('#radio_group_1').find('label[value="vmRecovery"]').hide();
                    $('#radio_group_1').find('label[value="vmRecovery2"]').hide();
                    $('#radio_group_1').find('label[value="vmRecovery3"]').hide();
                    //$('#radio_group_1').find('label[value="completeMachineRecovery"]').click();
                    // 初始化整机
                    clientRecoverConfig.init({'point': info, 'jobType': 3, pointType: pointType, taskUuid: taskUuid});
                } else {
                    // 初始化虚拟化 .迁移到有代理需检测异构，无代理不需要
                    vmRecoverConfig.init({showType: 1, 'point': info, 'jobType': 3, pointType: pointType, showDriver: false, instantTaskUuid: taskUuid});
                    // 初始化整机
                    clientRecoverConfig.init({'point': info, 'jobType': 3, pointType: pointType, taskUuid: taskUuid});
                }
            }

            Metronic.unblockUI('#vmrecovercontent');

            data.point_info = info.point_info;
            data.timepoint_uuid = info.point_info[0].timepoint_uuid;
            data.type = info.type
            data.node_uuid = info.node_uuid
            nodeUuid = info.node_uuid

            // 忽略节点限制默认打开
            $('#ignore_resource_limit').bootstrapSwitch('state', true);
            initResourceLimit([ info.node_uuid]); // 初始化忽略资源限制表格

            initTaskName(); // 初始化任务名称
            //设置重连时间和次数的默认值
            $('#reconnect_time').val(5);
            $('#reconnect_interval').val(5);
            showStep1(info.show_str);
        }, true);
    }

    var showStep1 = function(nodes){
        var str = '';
        $.each(nodes, function(i, d){
            str += d;
        });
        $('.vmtypeshow').html(str);
    }

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
                // 传输策略切换
                $('.agent-transport-strategy').show();
                $('.vm-transport-strategy').hide();
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
                // 初始化虚拟化
                var inits = {showType: 1, 'point': pointInfo, 'jobType': 3, pointType: pointType, showDriver: false, instantTaskUuid: taskUuid};
                if(newValue == 'vmRecovery3'){
                    // 公有云
                    inits.showType = 3;
                }else if(newValue == 'vmRecovery2'){
                    // 私有云
                    inits.showType = 2;
                }else {
                    // 虚拟化平台
                    inits.showType = 1;
                }
                // 初始化虚拟化 .迁移到有代理需检测异构，无代理不需要
                vmRecoverConfig.init(inits);
            }
        })
        //选择恢复方式
        $('#recovertype').on('change',function(){
            if('1' == this.value){
                data.time_strategy = {};
                $('#setstrategy').hide();
                $('.backupCrowd').hide();
            }else if("2" == this.value){
                $('#setstrategy').show();
                $('.backupCrowd').show();
            }
            initTimeStrategyDes();
        });

        if (!CONF.FUNCTIONS.includes('multithread')) {
            // 没得多线程，那么就隐藏页面的配置
            $('#agent_recoveryThreadDiv').parent().parent().hide();
        }

        //初始化重试策略
        $('#retry_config').retryStrategy();

        $('#agent_encrypttransfer').on('switchChange.bootstrapSwitch', agenttransferEncryptChange);

        $('#cache_data_sync_interval').spinner({value: 10, step: 10, min: 1,max: 9999999});
        //选择存储用途复选框
        $('#useMode').find('.icheck').on('ifClicked', useModeClick);
    }

    // 获取授权信息
    var getAuth = function () {
        getAuthItem('vm');
        getAuthItem('private_cloud');
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
        }
    }
    var getAuthItem = function (m) {
        var datas = {
            type: 'a',
            module: m
        };
        pAjaxRequest(datas, '/api/v1/system/auth/base_info', "GET", function (result) {
            if (result.code == 0) {
                authConfig[m] = result.data.total != 0;
            } else {
                authConfig[m] = false;
            }
        }, false);
    }

    var agenttransferEncryptChange = function(){
        if(this.checked){
            $('.transfer-agent-encrypt-method-form').show();
        }else{
            $('.transfer-agent-encrypt-method-form').hide();
        }
    }

    var initTimeStrategyDes = function(){
        var des = "";
        //备份
        var recoverytype = $('#recovertype').val();
        var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();
        if(!strategyConfig.recInfo) return;
        if(recoverytype == 1){
            des += LANG.UI_JOB_ONCE_TIME_RECOVER;
        }else if(recoverytype == 2){
            des += strategyConfig.recInfo.des;
        }
        var strategyIndex = $('#strategySelect').val();
        if(strategyIndex && strategyIndex != "" && editFlag){
            var oldDes = globalStrategy[strategyIndex].time.des;
            initStrategyDesStyle($('.recoveryTimeDes'), des, oldDes);
        }else{
            $('.recoveryTimeDes').removeClass('font-green-seagreen');
        }
        $('.recoveryTimeDes').html(des);
        $('.recoveryTimeDes').prop('title', des);
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
                        if(step2Valid() == false){
                            return false;
                        }
                        break;
                    case 2:
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

    // 第1步迁移方式
    var step2Valid = function(){

        if (_selectTargetType == '') {
            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_RECOVERY_SOURCE_GOALS_TIPS);
            return false;
        }
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

            if (data.host_info != vmconfigs.node) {
                VmMotionTransferStrategy.init({
                    hypervisor: vmconfigs.node.hypervisor,
                    vcenteruuid: vmconfigs.node.vcuuid,
                    nodeuuid: data.node_uuid
                }); // 初始化传输策略
            }
            data.migrate_web_config.node = vmconfigs.node;
            // 恢复目标配置
            data.migrate_target_info = {
                'target_type': 1,
                'target_uuid': vmconfigs.node.vcuuid,
                'hypervisor_type': vmconfigs.node.hypervisor,
                'host_uuid': vmconfigs.node.id,
            };
            data.migrate_web_config.vmconfigs =  vmconfigs.config; // 虚拟机配置列表
            if (vmconfigs.driverCheck != undefined) {
                data.driver_check = vmconfigs.driverCheck;
            } else {
                data.driver_check = [];
            }
            data.host_info = vmconfigs.node; // 迁移宿主机信息
            $('.recovershow').html(vmconfigs.title);
        } else {
            // 有代理
            var recover_info = clientRecoverConfig.getInfo();
            if (recover_info == false) {
                return false;
            }

            // 如果一个磁盘都没选择的话，是不允许的
            if (recover_info[0].recovery_strategy == undefined) {
                // UIToastr.showError('目标设备配置', '请选择恢复的目标设备');
                return false;
            }
            if (recover_info[0].recovery_strategy.length <= 0) {
                UIToastr.showError(LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG, LANG.UI_PLATFORM_RECOVERY_TARGET_CONFIG_CHOOSE_ONE);
                return false;
            }

            // 判断是否是客户端
            if (recover_info[0].agent_type == 1) {
                UIToastr.showInfo(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_MOTION_TIPS);
            }

            if (recover_info[0].net_model == 2) {
                //初始化传输网络信息
                $("#agent_transferNetworkTree").transferNetwork({node_uuid: nodeUuid});
                // 需要显示传输网络
                $('.transfernetworkDiv').show();
            } else {
                // 隐藏传输网络
                $('.transfernetworkDiv').hide();
            }

            data.migrate_target_info = {
                'target_type': 2,
                'target_uuid': recover_info[0].agent_uuid,
                'hypervisor_type': 0,
                'host_uuid': '',
            };
            data.migrate_web_config = recover_info; // 宿主机配置列表
            var recovershow = '';
            for (var j in recover_info) {
                recovershow += recover_info[j].agent_name + '</br>';
            }
            $('.recovershow').html(recovershow);
        }

        // 迁移需要禁用 任务自动重试
        $('#task_retry_flag').bootstrapSwitch('state', false);
        $('#task_retry_flag').bootstrapSwitch('disabled', true);
        // 直接隐藏显示
        $('#task_retry_flag').closest('.retry-strategy-content__group').hide();
        initStrategyDes();
        return true;
    }

    // 第2步迁移策略验证
    var step3Valid = function(){
        //获取重试策略----------------
        data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
        if (data.retry_strategy == false) {
            return false;
        }
        // 必须给个安全策略
        data.safe_config_strategy = safeData('','', '')

        if (_selectTargetType != 'completeMachineRecovery') {
            // 无代理
            data.transfer = VmMotionTransferStrategy.getInfo();
            if (data.transfer.length == 0) {
                return false;
            }
            showStep3();
            return true;
        }
        data.high_info = {};
        // 有代理
        //得到线程数量
        data.high_info.threadNum = parseInt($('#agent_recoveryThreadNum').val());
        if (!CONF.FUNCTIONS.includes('multithread')) {
            // 没得多线程，那么就默认为1
            data.high_info.threadNum = 1;
        }

        if (data.high_info.threadNum == 0) {
            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
            return false;
        }
        //得到传输加密
        data.high_info.transfer = {};
        data.high_info.transfer.encrypt = $('#agent_encrypttransfer').get(0).checked; //得到传输加密
        // 传输加密算法
        data.high_info.transfer.encrypt_method = parseInt($('#agent_transferEncryptMethod').val())

        if (data.migrate_web_config[0].net_model == 2) {
            //获取传输网络
            let network = $("#agent_transferNetworkTree").transferNetwork('getSelect');
            if(network == false){
                // UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_NODE_NETWORK_TRANSFER_EMPTY);
                return false;
            }
            data.high_info.transfer.network = network.network_uuid;
            data.high_info.transfer.network_name = network.name;
        } else {
            data.high_info.transfer.network = '';
            data.high_info.transfer.network_name = '';
        }
        if($('#handCheck').is(':checked')){
            // 手动完成
            if($('#limitsize').val() == '' || $('#limitsize').val() == 0) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_DATA_SYNC_INTERVAL_TIPS);
                return false;
            }
        }

        showStep3();
        return true;
    }

    var showStep3 = function(){
        // 高级策略
        // 迁移完成方式
        data.auto_migrate_flag = true;
        data.auto_migrate_time = 0;
        if($('#handCheck').is(':checked')){
            // 手动完成
            data.auto_migrate_flag = false;
            data.auto_migrate_time = parseInt($('#limitsize').val());
        }

        data.stop_instant_task_flag = $('#stopmotionaftercomplete').is(':checked');

        var autocompletemotionlabel = $('.autocompletemotionlabel').html();
        if (data.auto_migrate_flag) {
            autocompletemotionlabel += ':' + LANG.UI_PLATFORM_RECOVERY_AUTO_COMPLETE;
        } else {
            autocompletemotionlabel += ':' + LANG.UI_PLATFORM_RECOVERY_HAND_COMPLETE + ',' + LANG.UI_PLATFORM_RECOVERY_DATA_SYNC_INTERVAL + ':' + data.auto_migrate_time + LANG.UI_JOB_MINUTE;
        }
        var stopmotionaftercompletelabel = $('.stopmotionaftercompletelabel').html();
        if (data.stop_instant_task_flag) {
            stopmotionaftercompletelabel += ':' + LANG.UI_PUBLIC_YES;
        } else {
            stopmotionaftercompletelabel += ':' + LANG.UI_PUBLIC_NO;
        }

        //获取高级策略----------------
        data.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;

        // 忽略资源限制
        let highstrategystr = $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes($('#ignore_resource_limit').get(0).checked);

        $('.highstrategyshow').html(stopmotionaftercompletelabel + '</br>' + autocompletemotionlabel  + '</br>' + highstrategystr);

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

        // 脚本配置
        /* data.before_task_script = $("#tab_scripts").getVinScript('script_before');
         data.after_task_script = $("#tab_scripts").getVinScript('script_after');*/

        if (_selectTargetType != 'completeMachineRecovery') {
            // 无代理
            $('.transportinfoshow').html(VmMotionTransferStrategy.getDes(data));
        } else {
            var des = LANG.UI_COPY_BACK_ENCRYPT + ": " + getSwitchDes(data.high_info.transfer.encrypt) + '<br>';
            // 传输加密算法
            if($('#agent_encrypttransfer').get(0).checked){
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
                des += encryptedMethodLabel + ": " + grade + "<br>";
            }

            if (CONF.FUNCTIONS.includes('multithread')) {
                // 有多线程
                des += $('.agent-transport-strategy .threadnumlabel').html() + ':' + data.high_info.threadNum;
            }
            if (data.high_info.transfer.network_name != '') {
                des += '<br>' + LANG.UI_NODE_NETWORK_TRANSFER + ':' + data.high_info.transfer.network_name;
            }

            $('.transportinfoshow').html(des);
            $('.applianceshow').hide();
        }
        $('.task_retry_flag_show').hide();
    }

    //加载策略对应描述
    var initStrategyDes = function(){
        initTimeStrategyDes();
        initHighStrategyDes();
        if ($('.speedlimitDiv a').hasClass('collapsed')) {
            $('.speedlimitDiv a').click();
        }
    }

    var useModeClick = function(event){
        var mode = $(this).data('mode');
        var $this = $(this);
        if(event.target.checked){
            //如果是取消选中
            // $('#useMode').find('input').iCheck("uncheck");
            event.preventDefault();
            // 如果是取消选中状态，强制保持选中状态
            setTimeout(function() {
                $this.iCheck('check');
            }, 0);
        }else{
            $('#useMode').find('input').iCheck("uncheck");
            $('#useMode').find('input[data-mode='+ mode +']').iCheck("check");
            if (mode == 1) {
                // 自动完成 隐藏
                $('.autofinishSettingdiv').hide();
            } else {
                $('.autofinishSettingdiv').show();
            }
        }
    }

    var initTimeStrategyDes = function(){
        var des = "";
        //备份
        var recoverytype = $('#recovertype').val();
        var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();
        if(!strategyConfig.recInfo) return;
        if(recoverytype == 1){
            des += LANG.UI_JOB_ONCE_TIME_RECOVER;
        }else if(recoverytype == 2){
            des += strategyConfig.recInfo.des;
        }
        var strategyIndex = $('#strategySelect').val();
        if(strategyIndex && strategyIndex != "" && editFlag){
            var oldDes = globalStrategy[strategyIndex].time.des;
            initStrategyDesStyle($('.recoveryTimeDes'), des, oldDes);
        }else{
            $('.recoveryTimeDes').removeClass('font-green-seagreen');
        }
        $('.recoveryTimeDes').html(des);
        $('.recoveryTimeDes').prop('title', des);
    }

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

    var initSpinner = function(){
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
        $('#recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
        $('#agent_recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});

        // 失去焦点时（或按回车）才做范围校验和修正
        $('#agent_recoveryThreadNum').on('blur keyup', function(e) {
            if (e.type === 'keyup' && e.key !== 'Enter') return;

            let value = $(this).val();
            let num = parseInt(value, 10);

            // 如果为空，默认设为 1
            if (isNaN(num)) {
                num = 1;
            }

            // 限制在 1~8
            if (num < 1) num = 1;
            if (num > 8) num = 8;

            $(this).val(num);
        });
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
        //设置任务名
        pAjaxRequest({job_name: LANG.UI_PLATFORM_RECOVERY_MOTION_NAME}, '/api/v1/jobs/name', 'GET', function (result){
            var job_name = LANG.UI_PLATFORM_RECOVERY_MOTION_NAME + '1';
            if (result.success) {
                job_name = result.data.value;
            }
            $('#job_name').val(job_name);
        }, false)
    }

    return {
        init: function () {
            taskUuid = $('#taskuuid').val();
            Metronic.blockUI({target: '#submit_form',animate: true,cenrerY: true,});
            wizardInit();// 初始化上、下一步
            initPoint(); // 初始化时间点信息
            initListener();
            handleValidation(); // 验证
            initStrategy(); // 初始化时间策略
            initSpinner();
            Metronic.unblockUI('#submit_form');
        },
    };
}();
jQuery(document).ready(function () {
    RecoverMotion.init();
});
