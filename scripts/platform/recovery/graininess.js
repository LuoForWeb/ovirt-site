var RecoverGraininess = function () {
    // 细粒度恢复任务
    var data = {point_info:{}, os_type:'', safe_config_strategy:{}};
    var pointType = 1; // 时间点类型
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
        if(data.job_name == ''){
            return UIToastr.showWarning(LANG.UI_VISUAL_GRAIN_TASK_NAME, LANG.UI_MOTION_INPUT_TASKNAME);
        }
        submittedFlag = true;
        // 进行ajax请求
        Metronic.blockUI({target: '#vmrecovercontent',animate: true,cenrerY: true,});
        pAjaxRequest(data, "/api/v1/recovery/graininess/agentlessis", "POST", function (result) {
            Metronic.unblockUI('#vmrecovercontent');
            if (result.code == -1) {
                submittedFlag = false;
                return UIToastr.showError(LANG.UI_VISUAL_GRAIN_TASK_NAME, result.msg);
            }
            if (operateResponseList(result, LANG.UI_VISUAL_GRAIN_TASK_NAME)){
                // 任务创建完成应该进入任务列表
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            } else {
                submittedFlag = false;
            }
        });
    }

    var handleValidation = function() {
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

    };

    //事件监听
    var initListener = function(){
        if (!CONF.FUNCTIONS.includes('virusKill') && (!CONF.FUNCTIONS.includes('integrity') || pointType == 3)) {
            // 既没有病毒查杀功能，也没有完整性校验功能，那么隐藏安全策略
            $('#safeShowDiv').hide(); // 最后一步显示
            $('#tab_safe').hide(); // 策略内容
            $('.transferLi').hide(); // 策略tab
        }
        getAuthItem('vm');
    }

    var getAuthItem = function (m) {
        var datas = {
            type: 'a',
            module: m
        };
        pAjaxRequest(datas, '/api/v1/system/auth/base_info', "GET", function (result) {
            isLicense = result.data.license_flag;
            if (!isLicense) {
                // 授权失效。不允许下一步
                return UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE,  LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
            }
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

        if (!arraysDeepEqual(data.point_info, info.point_info)) {
            // 有改变需要重新初始化
            $('#recoverostype').val('');
            initTaskName(); // 初始化任务名称
            // 请求接口获取当前时间点对应的操作系统类型
            if (info.point_info[0].os_type != undefined) {
                var os_type = info.point_info[0].os_type;
                var osType = parseInt(info.point_info[0].os_type);
                if (osType == 2) {
                    os_type = 'Linux';
                } else if (osType == 1 || osType == 3) {
                    os_type = 'Windows';
                }
                if (os_type == 'Linux' || os_type == 'Windows') {
                    $('#recoverostype').val(os_type);
                }
            } else {
                // 请求后台接口获取操作系统类型
                var params = {
                    agent_uuid: '',
                    timepoint_uuid:  info.point_info[0].timepoint_uuid
                };
                pAjaxRequest(params, "/api/v1/recovery/timepoint_config", "GET", function (d) {
                    if (d.success) {
                        var os_type = parseInt(d.data.os_type);
                        if (os_type == 2) {
                            $('#recoverostype').val('Windows');
                        } else if (os_type < 5 && os_type > 0) {
                            $('#recoverostype').val('Linux');
                        }
                    }
                }, false);
            }
            // 忽略节点限制默认打开
            $('#ignore_resource_limit').bootstrapSwitch('state', true);
            initResourceLimit([ info.node_uuid]); // 初始化忽略资源限制表格
        }

        data.point_info = info.point_info;
        data.type = info.type

        $('#vmrecovercontent').find('.button-next').prop('disabled', true);

        showStep1(info.show_str);
        return true;
    }

    var showStep1 = function(nodes){
        var str = '';
        $.each(nodes, function(i, d){
            str += d;
        });
        $('.vmtypeshow').html(str);
        // 完整性校验组件
        if (CONF.FUNCTIONS.includes('integrity') && pointType != 3) {
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
            // 细粒度，源的 scp 47 不能恢复到无网络环境
            var noNetFlag = false;
            if (pointType == 1 && data.point_info[0].hypervisor == 47) {
                // true隐藏，false不隐藏
                noNetFlag = true;
            }
            // 细粒度都没无网环境
            $('#completeConfig').completeStrategyCovery(
                //pointType == 1 ? CONF.MODULE_TYPE.VM : CONF.MODULE_TYPE.OS,
                CONF.MODULE_TYPE.OS,
                0,
                backup_disable_flag
            );
        }
        // 封装病毒检测
        makeVirus();
        // 初始化系统选项改变
        initOsChange();
    }

    // 监听系统选项改变
    var initOsChange = function (){
        $('#recoverostype').bind('change').on('change', function (){
            let ostype = $(this).val();
            if (ostype != '') {
                // 改变操作系统会影响病毒检测扫描/排除配置
                bootbox.confirm({
                    title: LANG.UI_PUBLIC_TIPS,
                    message: LANG.UI_GRAIN_JOB_CHANGE_OS_TIPS,
                    callback: debounce(function (r) {
                        if (!r) return;
                        makeVirus();
                    },300)
                })
            }
        });
    }

    // 封装病毒检测
    var makeVirus = function () {
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

            var os_type = $('#recoverostype').val();
            if (os_type == '') {
                os_type = 'Other';
            }
            $('#virusConfig').virusDetectionCover(
                virus_status.includes(1),
                virus_status.includes(2),
                virus_status.includes(3) || virus_status.includes(4),
                true,
                {
                    no_scan: {
                        os_type: os_type
                    },
                    healthy: {
                        os_type: os_type,
                    },
                    reflected: {
                        // 已感染但未完成扫描时状态改为4
                        virus_scan_status: virus_status.includes(4) ? $.fn.virusDefine.virus_scan_status.infected_but_unfinished : $.fn.virusDefine.virus_scan_status.infected,
                        os_type: os_type,
                    }
                }
            );
        }
    }

    // 第一步恢复方式验证
    var step2Valid = function(){
        // 选择操作系统和安全策略
        var osType = $('#recoverostype option:selected').text();
        data.os_type = $('#recoverostype').val();
        if (data.os_type == undefined || data.os_type == '') {
            let msg2 = LANG.UI_PLATFORM_RECOVERY_OS_TYPE_TIPS;
            let newString = msg2.replace(/s%/, data.point_info[0].host_name);
            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, newString);
            return false;
        }
        osType = LANG.UI_VM_OS_TYPE + '：' + osType;
        $('.reserveostypeshow').html(osType);

        if (pointType == 3) {
            // 实时的显示选择的 恢复时间点
            $('.div-vol-cdp-reovery-time').show();
            $('.vol-cdp-reovery-time').html(data.point_info[0].cdp_datetime);
        } else {
            $('.div-vol-cdp-reovery-time').hide();
        }

        // 病毒扫描策略
        let virtus = '';
        if (CONF.FUNCTIONS.includes('virusKill')) {
            // 有病毒查杀功能
            virtus = $('#virusConfig').getVirusDetectionCover();
            if (virtus === false) {  // 验证病毒扫描配置内容是否符合要求
                return false;
            }
        }
        // 完整性校验
        let integrity = '';
        if (CONF.FUNCTIONS.includes('integrity') && pointType != 3) {
            // 有完整性校验功能
            integrity = $('#completeConfig').getCompleteStrategyCovery();
        }

        // 显示病毒策略和完整性策略信息
        data.safe_config_strategy = safeData('',virtus, integrity)

        // 安全策略
        let safeInfo = '';
        if (CONF.FUNCTIONS.includes('virusKill')) {
            safeInfo = virtus.str;
        }
        if (CONF.FUNCTIONS.includes('integrity') && pointType != 3) {
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
                msg = 'UI_PLATFORM_GRAIN_RECOVERY_PRCLOUD_JOB_';
            } else if (subtype == 3) {
                // 公有云
                msg = 'UI_PLATFORM_GRAIN_RECOVERY_AWS_JOB_';
            } else {
                // 虚拟机
                msg = 'UI_PLATFORM_GRAIN_RECOVERY_VM_JOB_';
            }
        } else if (recovery_type == 'cdp') {
            // 实时
            msg = 'UI_PLATFORM_GRAIN_RECOVERY_VOL_MACHINE_JOB_';
        } else {
            // 整机
            msg = 'UI_PLATFORM_GRAIN_RECOVERY_MACHINE_JOB_';
        }
        //设置任务名
        pAjaxRequest({job_name: msg}, '/api/v1/jobs/name', 'GET', function (result){
            var job_name = LANG.UI_VISUAL_GRAIN_TASK_NAME + '1';
            if (result.success) {
                job_name = result.data.value;
            }
            $('#job_name').val(job_name);
        }, false)
    }

    return {
        init: function () {
            wizardInit();
            pointType = 1;
            if ($('#recovery_type').val() == 'cdp') {
                pointType = 3;
            } else if ($('#recovery_type').val() == 'os') {
                pointType = 2;
            }
            initListener();
            handleValidation();
           // calcStyle();
        },
    };
}();
jQuery(document).ready(function () {
    RecoverGraininess.init();
});
