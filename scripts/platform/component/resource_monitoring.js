var ResourceMonitoring = function () {

    let osType = 'Linux';
    let oldInfo = {};
    let windowsMemory = 20;
    /**
     * 资源监测组件
     */

    /**
     * 初始化绑定事件
     */
    let initListener = function (){
        // 根据操作系统版本，显示隐藏高级策略-资源监测的选项
        if (osType == 'Linux') {
            // 显示新的
            $('.windows_cache_config').hide();
            $('.linux_cache_config').show();
        } else {
            // 显示之前的配置
            $('.windows_cache_config').show();
            $('.linux_cache_config').hide();
        }
        //持续数据保护降级
        $('#cmCdpDemotionSwitchResources').on('switchChange.bootstrapSwitch', cmCdpDemotionChange);
        //切换停止任务阈值类型
        $('#stopTaskThresholdTypeSelect').unbind('change').bind('change', stopTaskThresholdChange);
        //切换持续数据保护降级阈值类型
        $('#cdpDemotionThresholdValueType').unbind('change').bind('change', demotionThresholdType);
        //恢复到cdp时间间隔Add
        $('#cdpDemotionThValueAddButton').unbind('click').bind('click', stopOrRecoveryRealTimeTaskChange);
        //减少恢复到cdp的时间间隔
        $('#cdpDemotionThValueReduceButton').unbind('click').bind('click', stopOrRecoveryRealTimeTaskChange);
        //停止任务阈值Add
        $('#stopTaskThAddButton').unbind('click').click(cdpDemotionThresholChange);
        //停止任务阈值Reduce
        $('#stopTaskThReduceButton').unbind('click').click(cdpDemotionThresholChange);

        //固定时间段开关
        $('#cmCdpDemotionSwitchTime').on('switchChange.bootstrapSwitch', cmCdpStopTimeChange);

        // 监听资源监测复选框选择事件
        // stop_task_condition_source3 和 recover_task_condition_source2 是联动的
        // 联动：任选其一，另一个同步状态；取消其一，另一个也取消
        notExclusion($('#stop_task_condition_source3, #recover_task_condition_source2'));
        notExclusion($('#stop_task_condition_source4, #recover_task_condition_source3'));

        delTaskTime();

        // 固定时间段添加事件
        $('#addMoreTime').off('click').on('click', function (){
            makeAddTimes();
        })

        $('.stoptaskthreshold').spinner({value:30, step: 1, min: 1, max: 9999});
        $('.cdpdemotionthreshold').spinner({value:30, step: 1, min: 1, max: 9999});
        $('.recoverycdpinterval').spinner({value:10, step: 1, min: 5, max: 60});

        // 资源监测 linux
        $('.interval_time_item-4').spinner({value:30, step: 1, min: 1, max: 999});
        $('.stop-item-block1-percent').spinner({value:60, step: 1, min: 1, max: 99});
        $('.stop-item-block1-seconds').spinner({value:30, step: 1, min: 1, max: 9999});
        $('.stop-item-block1-div-3-1').spinner({value:30, step: 1, min: 1, max: 9999});

        // 限制缓存的输入
        initLimitCache();

        reSizeWindow();
        $(window).resize(() => {
            reSizeWindow();
        });
    }

    //停止任务阈值
    let stopTaskThresholdChange = function(){
        let stopTaskThresholdType = $('#stopTaskThresholdTypeSelect').val();
        $('#cdpDemotionThresholdValueType').val(stopTaskThresholdType);  //停止任务阈值需要与持续数据保护降级类型保持一致
        changeThresholdTypeDefaultConf(stopTaskThresholdType);
    }

    //切换阈值类型，填充相关阈阈值默认值，均以默认百分进行数值填充
    let changeThresholdTypeDefaultConf = function(thresholdType){
        //默认值调整
        let memorySize = windowsMemory;
        if(thresholdType ==2){ //MB
            let defaultMemorySize = memorySize/1024/1024;
            $("#stopTaskThresholdVal").val(Math.round(defaultMemorySize*0.1));
            $('#cdpDemotionThresholdValue').val(Math.round( defaultMemorySize*0.2));
        }else if(thresholdType ==3){  //GB
            let defaultMemorySize =memorySize/1024/1024/1024;
            $("#stopTaskThresholdVal").val(Math.round(defaultMemorySize*0.1));
            $('#cdpDemotionThresholdValue').val(Math.round( defaultMemorySize*0.2));
        }else{
            $("#stopTaskThresholdVal").val(10);
            $('#cdpDemotionThresholdValue').val(20);
        }
    }

    //持续数据保护讲降级
    let demotionThresholdType = function(){
        let demotionThresholdType = $('#cdpDemotionThresholdValueType').val();
        $('#stopTaskThresholdTypeSelect').val(demotionThresholdType);   //持续数据保护降级 ,停止任务阈值需要与持续数据保护降级类型保持一致
        changeThresholdTypeDefaultConf(demotionThresholdType);
    }

    //切换停止或恢复实时监控任务时间间隔
    let stopOrRecoveryRealTimeTaskChange = function(){
        let cdpDemotionThresholdValue = parseInt($('#cdpDemotionThresholdValue').val());
        let stopTaskThresholdValue = parseInt($('#stopTaskThresholdVal').val());
        let demotionThresholdType = $('#cdpDemotionThresholdValueType').val();
        if(cdpDemotionThresholdValue<= stopTaskThresholdValue ){
            osType != 'Linux' && UIToastr.showWarning(LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR, LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR_TIPS + "," + LANG.UI_CM_CDP_HOST_RESOURCE_DEFAULT_CONF);
            // $('#cdpDemotionThresholdValue').val(20);
            // $('#stopTaskThresholdVal').val(10);
            changeThresholdTypeDefaultConf(demotionThresholdType);
            return false;
        }
        return true
    }

    //持续数据保护降级间隔
    let cdpDemotionThresholChange = function(){
        let cdpDemotionThresholdValue = parseInt($('#cdpDemotionThresholdValue').val());
        let stopTaskThresholdValue = parseInt($('#stopTaskThresholdVal').val());
        let stopTaskThresholdType = $('#stopTaskThresholdTypeSelect').val();
        if(cdpDemotionThresholdValue<= stopTaskThresholdValue ){
            osType != 'Linux' && UIToastr.showWarning(LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR, LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR_STOP_TASK_TIPS+","+LANG.UI_CM_CDP_HOST_RESOURCE_DEFAULT_CONF);
            // $('#cdpDemotionThresholdValue').val(20);
            // $('#stopTaskThresholdVal').val(10);
            changeThresholdTypeDefaultConf(stopTaskThresholdType);
            return false;
        }
        return true
    }

    function reSizeWindow(){
        // 固定下高级策略-资源监测的高度
        var height;
        var panelH = window.innerHeight;

        height = panelH - 381;

        $(".high_source_config").css({
            "height": height
        });
    }

    /**
     * 限制缓存的输入
     * */
    function initLimitCache(){
        // 百分比
        limitMin('#stop_task_condition1_min', 1, 99);
        limitMin('#stop_task_condition3_num', 1, 99);
        limitMin('#stop_task_condition_source1_num', 1, 99);
        limitMin('#stop_task_condition_source2_num', 1, 98);
        limitMin('#stop_task_condition_source4_num', 1, 98);
        limitMin('#recover_task_condition_source1_num', 1, 97);
        limitMin('#recover_task_condition_source3_num', 1, 97);

        // 时间输入
        // 毫秒
        limitMin('#stop_task_condition2_num', 1, 9999);
        limitMin('#stop_task_condition_source3_num', 1, 9998);
        limitMin('#recover_task_condition_source2_num', 1, 9997);
        // 秒
        limitMin('#stop_task_condition1_interval', 1, 999);
        limitMin('#stop_task_condition2_interval', 1, 999);
        limitMin('#stop_task_condition3_interval', 1, 999);
        limitMin('#stop_task_condition_source2_interval', 1, 999);
        limitMin('#stop_task_condition_source3_interval', 1, 999);
        limitMin('#stop_task_condition_source4_interval', 1, 999);
        // 分钟
        limitMin('#recover_task_condition_source1_interval', 1, 999);
        limitMin('#recover_task_condition_source2_interval', 1, 999);
        limitMin('#recover_task_condition_source3_interval', 1, 999);

        // 停止任务 > 任务暂停 >= 任务恢复 输入限制
        // stop_task_condition1_min -> stop_task_condition_source2_num > recover_task_condition_source1_num
        // stop_task_condition2_num -> stop_task_condition_source3_num > recover_task_condition_source2_num
        // stop_task_condition3_num -> stop_task_condition_source4_num > recover_task_condition_source3_num
        checkInputMsg('#stop_task_condition1_min', '#stop_task_condition_source2_num', '#recover_task_condition_source1_num');
        checkInputMsg('#stop_task_condition2_num', '#stop_task_condition_source3_num', '#recover_task_condition_source2_num');
        checkInputMsg('#stop_task_condition3_num', '#stop_task_condition_source4_num', '#recover_task_condition_source3_num');
    }

    // 三个联动的,有可能第一个未选中，那么不判断第一个
    function checkInputMsg(dom1, dom2, dom3) {
        var $dom1 = $(dom1);
        var $dom2 = $(dom2);
        var $dom3 = $(dom3);
        var $dom1C = $dom1.closest('.stop-item-block1').find('input[type="checkbox"]');
        var $dom2C = $dom2.closest('.stop-item-block1').find('input[type="checkbox"]');

        // 安全转为数字，无效则返回 null
        function toNumber(val) {
            const num = parseFloat(val);
            return isNaN(num) ? null : num;
        }

        // 统一校验并修正三个值
        function validateAndFix() {
            let v1 = toNumber($dom1.val());
            let v2 = toNumber($dom2.val());
            let v3 = toNumber($dom3.val());

            let v2_check = $dom2C.is(':checked');
            if (!v2_check) {
                // 2 和 3是关联的，2没选中，那只有1，就不触发
                return;
            }
            // 如果任一为空，不处理（或可设默认值）
            if (v1 === null || v2 === null || v3 === null) {
                return;
            }

            let v1_check = $dom1C.is(':checked');
            // 强制满足：v1 > v2 > v3
            // 步骤1: 先确保 v3 < v2
            if (v3 >= v2) {
                v3 = v2 - 1;
            }
            // 步骤2: 确保 v2 < v1
            if (v1_check && v2 >= v1) {
                v2 = v1 - 1;
                // 修正后可能 v2 <= v3，需再调整 v3
                if (v3 >= v2) {
                    v3 = v2 - 1;
                }
            }

            if (v3 <= 0) {
                v3 = 1;
                v2 = 2;
                if (v1_check) {
                    v1 = 3;
                }
            }

            // 更新 DOM（避免触发 blur 事件）
            $dom1.off('blur.temp').val(v1).on('blur.temp', validateAndFix);
            $dom2.off('blur.temp').val(v2).on('blur.temp', validateAndFix);
            $dom3.off('blur.temp').val(v3).on('blur.temp', validateAndFix);
        }

        // 绑定 blur 事件（使用命名空间避免重复绑定）
        $dom1.on('blur.check', validateAndFix);
        $dom2.on('blur.check', validateAndFix);
        $dom3.on('blur.check', validateAndFix);
        $dom1C.on('ifChanged', function() {
            // iCheck 推荐用 ifChanged 事件
            validateAndFix();
        });
        $dom2C.on('ifChanged', function() {
            // iCheck 推荐用 ifChanged 事件
            validateAndFix();
        });
    }

    /**
     * 限制输入框的最大和最小值的输入
     * */
    function limitMin(dom, minVal, maxValue = 99) {
        let $doms = $(dom);

        $doms.each(function() {
            let $this = $(this);

            // 输入时只过滤非数字字符
            $this.off('input.limitMin').on('input.limitMin', function() {
                let value = $(this).val();
                let filteredValue = value.replace(/\D/g, '');

                // 限制长度
                let maxLength = maxValue.toString().length;
                if (filteredValue.length > maxLength) {
                    filteredValue = filteredValue.substring(0, maxLength);
                }

                if (value !== filteredValue) {
                    $(this).val(filteredValue);
                }
            });

            // 失去焦点时进行范围验证
            $this.off('blur.limitMin').on('blur.limitMin', function() {
                let value = $(this).val();

                if (value === '') {
                    $(this).val(minVal.toString());
                    $(this).trigger('change');
                    return;
                }

                let numValue = parseInt(value, 10);

                if (isNaN(numValue) || numValue < minVal) {
                    $(this).val(minVal.toString());
                } else if (numValue > maxValue) {
                    $(this).val(maxValue.toString());
                }

                $(this).trigger('change');
            });

            // 初始验证
            let currentValue = $this.val();
            if (currentValue === '' || parseInt(currentValue) < minVal) {
                $this.val(minVal.toString());
            }
        });

        return $doms;
    }

    // 封装添加时间段
    function makeAddTimes(val = '10:00', val2 = 30) {
        let html = `<div class="interval_time_item">
                        <span class="interval_time_item-1">`+LANG.UI_PUBLIC_START_TIME+`</span>
                        <div class="interval_time_item-2 form-group-content">
                            <div class="input-group">
                                <input type="text" name="stop_task_condition_time" value="`+val+`" class="form-control timepicker timepicker-24 stop_task_condition_time">
                                <span class="input-group-btn">
                                    <button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>
                                </span>
                            </div>
                        </div>
                        <span class="interval_time_item-3">`+LANG.UI_CM_CDP_INTERVAL_TIME+`</span>
                        <div class="interval_time_item-4">
                            <div class="input-group spinner-group">
                                <input type="number" name="stop_task_condition_time_interval" min="1" max="999" onkeyup="value=value.replace(/[^\\d]/g,'')" class="spinner-input form-control input-sm stop_task_condition_time_interval" value="`+val2+`">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="ms-8 interval_time_item-5">
                            <select class="form-control " name="stop_task_condition_time_type">
                                <option value=1 selected>`+LANG.UI_JOB_MINUTE+`</option>
                                <option value=2 >`+LANG.UI_PUBLIC_HOUR+`</option>
                            </select>
                        </div>
                        <button type="button" class="task_time_del btn-tooltip" data-placement="bottom" data-trigger="hover" data-toggle="tooltip" data-original-title="`+LANG.UI_CM_CDP_INTERVAL_TIME_REMOVE+`"><i class="viconfont vicon-a-Reduce-onejianshao"></i></button>
                    </div>`;
        $('.task_time_block').append(html);
        delTaskTime();
        $('.interval_time_item-4').spinner({value:30, step: 1, min: 1, max: 999});
    }

    // 互斥的两个checkbox
    function notExclusion($checkboxes) {
        $checkboxes.on('ifChanged', function() {
            // iCheck 推荐用 ifChanged 事件
            const $current = $(this);
            if ($current.is(':checked')) {
                // 如果当前被选中，则取消其他所有
                $checkboxes.not($current).iCheck('check');
            } else {
                $checkboxes.not($current).iCheck('uncheck');
            }
        });
    }

    // 封装移除时间段事件
    function delTaskTime() {
        // 初始化时间选择
        $('.stop_task_condition_time').timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false,
            //              defaultTime:'00:00:00'
        });
        $('.stop_task_condition_time').parent('.input-group').on('click', '.input-group-btn', function(e){
            e.preventDefault();
            $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
        });
        $('[data-toggle="tooltip"]').tooltip();
        // 删除固定时间段按钮事件
        $('.task_time_del').off('click').on('click', function (){
            let that = $(this);
            // 隐藏对应的tooltip
            $('[data-toggle="tooltip"]').tooltip('hide');
            that.parent().remove();
            return;
            bootbox.confirm({
                title: LANG.UI_VM_MACHINE_DEL_TIPS,
                message: LANG.UI_CM_CDP_INTERVAL_TIME_REMOVE_TIPS,
                callback: function (r) {
                    if (!r) return;
                    // 执行操作
                    that.parent().remove();
                }
            })
        });
    }

    /**
     * @function 得到开关的结果描述
     * @return 开启/关闭
     */
    let getSwitchDes = function(check){
        if(check==CONF.FLAG.SET){
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    //持续数据保护降级
    let cmCdpDemotionChange = function(){
        if (this.checked) {
            $('.cdpdemotionthresholdvaluediv').show();
            $('.recoverycdpintervaldiv').show();
            $('.cdpdemotiontimediv').show();
            $('.cdpdemotionresourcediv').show();
            $('.cdpdemotionresourcediv2').show();
            if (osType == 'Linux') {
                $('.linux_cache_config').show();
                $('.windows_cache_config').hide();
            } else {
                $('.linux_cache_config').hide();
                $('.windows_cache_config').show();
            }
        } else {
            $('.cdpdemotionthresholdvaluediv').hide();
            $('.recoverycdpintervaldiv').hide();
            $('.cdpdemotiontimediv').hide();
            $('.cdpdemotiontimesetdiv').hide();
            $('.cdpdemotionresourcediv').hide();
            $('.cdpdemotionresourcediv2').hide();
            $('#cmCdpDemotionSwitchTime').bootstrapSwitch('state', false);
        }
    }

    // 固定时间段开关
    let cmCdpStopTimeChange = function (){
        if (this.checked) {
            $('.cdpdemotiontimesetdiv').show();
        } else {
            $('.cdpdemotiontimesetdiv').hide();
        }
    }

    // 编辑回填信息
    let backOldInfo = function (){
        if (oldInfo.source_config != undefined) {
            // windows
            let source_config = oldInfo.source_config;
            $('#stopTaskThresholdVal').val(source_config.mem_threshold);
            // 单位
            $('#stopTaskThresholdTypeSelect').val(source_config.detect_type);
            $('#cdpDemotionThresholdValueType').val(source_config.detect_type);
            // 持续数据保护降级
            $('#cmCdpDemotionSwitchResources').bootstrapSwitch('state', source_config.cbt_enable_flag);
            // 手动触发 change 事件，通知监听者
            $('#cmCdpDemotionSwitchResources').trigger('switchChange.bootstrapSwitch', [source_config.cbt_enable_flag]);
            if (source_config.cbt_enable_flag) {
                // 降级内存阈值
                $('#cdpDemotionThresholdValue').val(source_config.cbt_mem_threshold);
                // 恢复持续数据保护检测间隔
                $('#recoveryCdpintervalValue').val(source_config.cbt_detect_interval);
            }
        }
        if (oldInfo.resource_protect_config != undefined) {
            // #回填下新加的资源监测信息 linux
            let resource_protect_config = oldInfo.resource_protect_config;
            if (resource_protect_config.memory_used_stop_percent != undefined) {
                // 做个兼容判断
                // 停止任务触发条件
                $('#stop_task_condition1_min').val(resource_protect_config.memory_used_stop_percent); // 已使用内存超过
                $('#stop_task_condition1_interval').val(resource_protect_config.memory_used_stop_detect_time); // 且持续时间超过
                let stop_io_flag = resource_protect_config.stop_io_flag;  // 磁盘I/O延迟 开关
                if (parseInt(stop_io_flag) === 1) {
                    $('#stop_task_condition2').iCheck('check');
                    $('#stop_task_condition2_num').val(resource_protect_config.stop_io_delay); // 磁盘I/O延迟超过
                    $('#stop_task_condition2_interval').val(resource_protect_config.io_stop_detect_time); // 且持续时间超过
                } else {
                    $('#stop_task_condition2').iCheck('uncheck');
                }
                let stop_cpu_flag = resource_protect_config.stop_cpu_flag;  // CPU占用率 开关
                if (parseInt(stop_cpu_flag) === 1) {
                    $('#stop_task_condition3').iCheck('check');
                    $('#stop_task_condition3_num').val(resource_protect_config.stop_used_cpu_percent); // CPU占用率超过
                    $('#stop_task_condition3_interval').val(resource_protect_config.stop_cpu_detect_time); // 且持续时间超过
                } else {
                    $('#stop_task_condition3').iCheck('uncheck');
                }

                // 持续数据保护降级
                let enable_cbt_flag = resource_protect_config.enable_cbt_flag;
                if (parseInt(enable_cbt_flag) === 1) {
                    $('#cmCdpDemotionSwitchResources').bootstrapSwitch('state', true);
                    // #任务暂停持续数据保护条件
                    $('#stop_task_condition_source1_num').val(resource_protect_config.driver_used_memory_percent); // 占用内存超过剩余内存
                    $('#stop_task_condition_source2_num').val(resource_protect_config.to_cbt_used_memory_percent); // 已使用内存超过
                    $('#stop_task_condition_source2_interval').val(resource_protect_config.to_cbt_memory_duration); // 持续时间超过
                    //  磁盘I/O延迟 暂停和恢复联动
                    let io_cbt_flag = resource_protect_config.io_cbt_flag;
                    if (parseInt(io_cbt_flag) === 1) {
                        $('#stop_task_condition_source3').iCheck('check');
                        $('#recover_task_condition_source2').iCheck('check');
                        // 暂停
                        $('#stop_task_condition_source3_num').val(resource_protect_config.to_cbt_io_delay); // 磁盘I/O延迟超过
                        $('#stop_task_condition_source3_interval').val(resource_protect_config.to_cbt_io_duration); // 且持续时间超过
                        // 恢复
                        $('#recover_task_condition_source2_num').val(resource_protect_config.to_cdp_io_delay); // 当磁盘I/O延迟低于
                        $('#recover_task_condition_source2_interval').val(parseInt(resource_protect_config.to_cdp_io_duration) / 60); // 且持续时间超过
                    } else {
                        $('#stop_task_condition_source3').iCheck('uncheck');
                        $('#recover_task_condition_source2').iCheck('uncheck');
                    }
                    //  CPU占用率 暂停和恢复联动
                    let cpu_cbt_flag = resource_protect_config.cpu_cbt_flag;
                    if (parseInt(cpu_cbt_flag) === 1) {
                        $('#stop_task_condition_source4').iCheck('check');
                        $('#recover_task_condition_source3').iCheck('check');
                        // 暂停
                        $('#stop_task_condition_source4_num').val(resource_protect_config.to_cbt_cpu_used_percent); // CPU占用率超过
                        $('#stop_task_condition_source4_interval').val(resource_protect_config.to_cbt_cpu_duration); // 且持续时间超过
                        // 恢复
                        $('#recover_task_condition_source3_num').val(resource_protect_config.to_cdp_cpu_used_percent); // 当CPU占用率低于
                        $('#recover_task_condition_source3_interval').val(parseInt(resource_protect_config.to_cdp_cpu_duration / 60)); // 且持续时间超过
                    } else {
                        $('#stop_task_condition_source4').iCheck('uncheck');
                        $('#recover_task_condition_source3').iCheck('uncheck');
                    }
                    // 恢复
                    $('#recover_task_condition_source1_num').val(resource_protect_config.to_cdp_used_memory); // 已使用内存低于
                    $('#recover_task_condition_source1_interval').val(parseInt(resource_protect_config.to_cdp_memory_duration / 60)); // 且持续时间超过

                    // 检测时间段
                    var time_windows_flag = resource_protect_config.time_windows_flag;
                    if (parseInt(time_windows_flag) === 1){
                        $('#cmCdpDemotionSwitchTime').bootstrapSwitch('state', true);
                        // 开启
                        // 手动触发 change 事件，通知监听者
                        $('#cmCdpDemotionSwitchTime').trigger('switchChange.bootstrapSwitch', [true]);
                        // 解析对应的时间段
                        let cbt_time_windows = resource_protect_config.cbt_time_windows;
                        for (var k in cbt_time_windows) {
                            if (k == 0) {
                                $('input[name="stop_task_condition_time"]').val(cbt_time_windows[k].hour + ':' + cbt_time_windows[k].minute);
                                $('input[name="stop_task_condition_time_interval"]').val(cbt_time_windows[k].duration);
                            } else {
                                makeAddTimes(cbt_time_windows[k].hour + ':' + cbt_time_windows[k].minute, cbt_time_windows[k].duration);
                            }
                        }
                    } else {
                        $('#cmCdpDemotionSwitchTime').bootstrapSwitch('state', false);
                    }
                } else {
                    $('#cmCdpDemotionSwitchResources').bootstrapSwitch('state', false);
                }
            }
        }
    }

    return {
        init: function (options) {
            if (options.osType != undefined) {
                // 是否多选
                osType = options.osType;
            }
            if (options.data != undefined) {
                oldInfo = options.data;
            } else {
                oldInfo = {};
            }
            if (options.windowsMemory != undefined) {
                windowsMemory = options.windowsMemory
            }
            initListener();
            backOldInfo();
        },
        // 提供一个对外获取配置
        getInfo: function (options) {
            osType = options.osType;
            let _createMsgdata = {};
            // 获取linux下高级策略下的资源监测信息
            _createMsgdata.resource_protect_config = {};
            /**紧急停止任务的资源最高上限阈值设置 linux**/
            // #紧急停止任务已使用内存最大阈值，单位为 %默认值暂设置为90%
            _createMsgdata.resource_protect_config.memory_used_stop_percent = parseInt($('#stop_task_condition1_min').val());
            // #达到紧急停止任务内存阈值最大持续时间，单位为s默认值暂设置为5s
            _createMsgdata.resource_protect_config.memory_used_stop_detect_time = parseInt($('#stop_task_condition1_interval').val());
            // #是否设置紧急停止任务的I0延时检测，0.关闭 1.开启 默认开启
            let stop_io_flag = $('#stop_task_condition2').is(':checked');
            _createMsgdata.resource_protect_config.stop_io_flag = stop_io_flag ? 1 : 0;
            // #紧急停止任务I0延迟最大阈值，单位为 ms，默认值暂设置为60ms
            _createMsgdata.resource_protect_config.stop_io_delay = parseInt($('#stop_task_condition2_num').val());
            // #达到紧急停止任务I0延迟阈值最大持续时间，单位为s，默认值暂设置为5s
            _createMsgdata.resource_protect_config.io_stop_detect_time = parseInt($('#stop_task_condition2_interval').val());
            // #是否设置紧急停止任务的cpu占用检测，0.关闭 1.开启 默认开启
            let stop_cpu_flag = $('#stop_task_condition3').is(':checked');
            _createMsgdata.resource_protect_config.stop_cpu_flag = stop_cpu_flag ? 1 : 0;
            // 紧急停止任务cpu占用率检测值，单位为%，默认值暂设置为99
            _createMsgdata.resource_protect_config.stop_used_cpu_percent = parseInt($('#stop_task_condition3_num').val());
            // #达到紧急停止任务cpu占用最大持续时间，单位为s，默认值暂设置为60s
            _createMsgdata.resource_protect_config.stop_cpu_detect_time = parseInt($('#stop_task_condition3_interval').val());

            /** 资源检测自动降级和恢复实时设置 **/
            // #是否开启资源检测自动降级功能 0.关闭 1.开启 默认开启
            _createMsgdata.resource_protect_config.enable_cbt_flag = $('#cmCdpDemotionSwitchResources').is(':checked') ? 1 : 0;
            // #实时过滤驱动占用剩余可用内存阈值， 单位为 对，默认值暂设置为50%
            _createMsgdata.resource_protect_config.driver_used_memory_percent = parseInt($('#stop_task_condition_source1_num').val());
            // #自动降级已使用内存阈值，单位为%，默认值暂设置为 80%
            _createMsgdata.resource_protect_config.to_cbt_used_memory_percent = parseInt($('#stop_task_condition_source2_num').val());
            // #达到自动降级内存阈值最大持续时间，单位为s，默认值暂设置为5s
            _createMsgdata.resource_protect_config.to_cbt_memory_duration = parseInt($('#stop_task_condition_source2_interval').val());
            // #自动降级io延时阈值，单位为ms，默认值暂设置为50ms
            _createMsgdata.resource_protect_config.to_cbt_io_delay = parseInt($('#stop_task_condition_source3_num').val());
            // #达到自动降级io延时阈值最大持续时间，单位为s，默认值暂设置为5s
            _createMsgdata.resource_protect_config.to_cbt_io_duration = parseInt($('#stop_task_condition_source3_interval').val());
            // #自动降级cpu占用率阈值，单位为8，默认值暂设置为908
            _createMsgdata.resource_protect_config.to_cbt_cpu_used_percent = parseInt($('#stop_task_condition_source4_num').val());
            // #达到降级cpu占用率阈值最大持续时间，单位为，默认值暂设置为10s
            _createMsgdata.resource_protect_config.to_cbt_cpu_duration = parseInt($('#stop_task_condition_source4_interval').val());

            // 恢复
            // #恢复实时备份内存检测阈值，单位为%默认值暂设置为60%
            _createMsgdata.resource_protect_config.to_cdp_used_memory = parseInt($('#recover_task_condition_source1_num').val());
            // #低于恢复实时已使用内存阈值持续时间，单位为s默认值暂设置为1800s(30min
            _createMsgdata.resource_protect_config.to_cdp_memory_duration = parseInt($('#recover_task_condition_source1_interval').val()) * 60;
            // #恢复实时备份io延时检测阈值，单位为ms，默认值咱设置为20ms
            _createMsgdata.resource_protect_config.to_cdp_io_delay = parseInt($('#recover_task_condition_source2_num').val());
            // #低于恢复实时io延时阈值持续时间，单位为:，默认值暂设置为1800s(30min)
            _createMsgdata.resource_protect_config.to_cdp_io_duration = parseInt($('#recover_task_condition_source2_interval').val()) * 60;
            // #是否开启io延时检测自动降级功能 0.关闭 1.开启，默认开启
            _createMsgdata.resource_protect_config.io_cbt_flag = $('#recover_task_condition_source2').is(':checked') ? 1 : 0;
            // stop_task_condition_source3 和 recover_task_condition_source2 是联动的
            // 恢复实时备份cpu占用率检测值，单位为%，默认值暂设置为60
            _createMsgdata.resource_protect_config.to_cdp_cpu_used_percent = parseInt($('#recover_task_condition_source3_num').val());
            // #低于恢复实时cpu占用阈值持续时间，单位为3，默认值暂设置为1800s(30min)
            _createMsgdata.resource_protect_config.to_cdp_cpu_duration = parseInt($('#recover_task_condition_source3_interval').val()) * 60;
            // 是否开启cpu阈值检测自动降级功能0.关闭1.开启默认开启
            _createMsgdata.resource_protect_config.cpu_cbt_flag = $('#recover_task_condition_source3').is(':checked') ? 1 : 0;

            /**自动降级窗口期设置 **/
            // 获取下固定时间段
            // #是否开启自动降级窗口功能，0.关闭 1.开启，默认关闭
            _createMsgdata.resource_protect_config.time_windows_flag = $('#cmCdpDemotionSwitchTime').is(':checked') ? 1 : 0;
            /**
             * *
             * cbt_time_windows: [{
             *     'hour':   // 时
             *     'minute': // 分
             *     'duration': // 间隔 min
             * }]
             * **/
            _createMsgdata.resource_protect_config.cbt_time_windows = [];
            if (_createMsgdata.resource_protect_config.time_windows_flag) {
                let cbt_time_windows = [];
                let $dom = $('.cdpdemotiontimesetdiv');

                if ($dom.find('.interval_time_item').length > 0) {
                    let hasOverlap = false;
                    const validatedWindows = [];

                    // 先收集所有时间段，然后按开始时间排序
                    const timeWindows = [];

                    $dom.find('.interval_time_item').each(function() {
                        const $item = $(this);
                        const time = $item.find('input[name="stop_task_condition_time"]').val();
                        const durationInput = $item.find('input[name="stop_task_condition_time_interval"]').val();
                        const type = $item.find('select[name="stop_task_condition_time_type"]').val();

                        // 解析 duration
                        let duration = parseInt(durationInput, 10) || 0;
                        if (type == '2') {
                            duration = duration * 60; // 转为分钟
                        }

                        // 解析时间
                        const timeArr = time.split(':');
                        const hour = parseInt(timeArr[0], 10) || 0;
                        const minute = parseInt(timeArr[1], 10) || 0;
                        const start = hour * 60 + minute;
                        const end = start + duration;

                        timeWindows.push({ start, end, hour, minute, duration, $item });
                    });

                    // 按开始时间排序
                    timeWindows.sort((a, b) => a.start - b.start);

                    // 检查重叠和间隔
                    for (let i = 0; i < timeWindows.length; i++) {
                        const current = timeWindows[i];

                        // 校验：不能超过 24 小时
                        // Bug #28417 放开跨天限制
                        if (current.end >= 48 * 60) {
                            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_CM_CDP_INTERVAL_TIME_CHECK_TIPS);
                            hasOverlap = true;
                            break;
                        }

                        // 检查与前一个时间段的间隔（最少10分钟）
                        if (i > 0) {
                            const previous = timeWindows[i - 1];

                            // 当前时间段的开始时间应该 ≥ 前一个时间段的结束时间 + 10分钟
                            if (current.start < previous.end + 10) {
                                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_CM_CDP_INTERVAL_TIME_CHECK_TIPS2);
                                hasOverlap = true;
                                break;
                            }
                        }

                        // 无冲突，保存数据
                        validatedWindows.push({ start: current.start, end: current.end });
                        cbt_time_windows.push({
                            hour: current.hour,
                            minute: current.minute,
                            duration: current.duration
                        });
                    }

                    if (hasOverlap) {
                        cbt_time_windows = [];

                        return false;
                    }
                    _createMsgdata.resource_protect_config.cbt_time_windows = cbt_time_windows;
                }
            }
            let stopTaskThresholdTypeSelect = $('#stopTaskThresholdTypeSelect').val();
            let stopTaskThreshold = parseInt($('#stopTaskThresholdVal').val());

            // windows下的资源限制
            _createMsgdata.high_pressure_strategy = {};
            let memThresholdVal = 0;
            if(stopTaskThresholdTypeSelect == 1){  //百分百
                memThresholdVal = stopTaskThreshold;
            }else if(stopTaskThresholdTypeSelect == 2){  //MB，需要转换成字节
                memThresholdVal = stopTaskThreshold * 1024 * 1024;
            }else if(stopTaskThresholdTypeSelect == 3){  //GM，需要转换成字节
                memThresholdVal = stopTaskThreshold * 1024 * 1024 * 1024;
            }
            _createMsgdata.high_pressure_strategy.detect_type = stopTaskThresholdTypeSelect;
            _createMsgdata.high_pressure_strategy.mem_threshold = memThresholdVal;
            // 是否开启CDP降级
            let cmCdpDemotionSwitchIsChecked = $('#cmCdpDemotionSwitchResources').is(':checked');
            if(cmCdpDemotionSwitchIsChecked){
                _createMsgdata.high_pressure_strategy.cbt_enable_flag = CONF.FLAG.SET;
            }else{
                _createMsgdata.high_pressure_strategy.cbt_enable_flag = CONF.FLAG.UNSET;
            }
            let memThresholdType = $('#cdpDemotionThresholdValueType').val();  //持续数据保护降级指标类型，需要痛停止任务阈值类型保持一致
            let memThresholdValue = parseInt($('#cdpDemotionThresholdValue').val());
            if(memThresholdType == 1){  //百分比
                _createMsgdata.high_pressure_strategy.cbt_mem_threshold = memThresholdValue;
                if(memThresholdValue>100){
                    if (osType == 'Linux') {
                        memThresholdValue = 20;
                        _createMsgdata.high_pressure_strategy.mem_threshold = 10;
                    } else {
                        UIToastr.showWarning(LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR, LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR_MAX_TIPS + ","+ LANG.UI_CM_CDP_HOST_RESOURCE_DEFAULT_CONF);
                        $('#cdpDemotionThresholdValue').val(20);
                        $('#stopTaskThresholdVal').val(10);
                        return false;
                    }
                }
            }else if(memThresholdType == 2){  //MB，需要转换成字节
                _createMsgdata.high_pressure_strategy.cbt_mem_threshold = memThresholdValue * 1024 * 1024;
            }else if(memThresholdType == 3){  //GB，需要转换成字节
                _createMsgdata.high_pressure_strategy.cbt_mem_threshold = memThresholdValue  * 1024 * 1024 * 1024;
            }
            if(stopTaskThreshold>=memThresholdValue && cmCdpDemotionSwitchIsChecked && osType != 'Linux'){
                // $('#cdpDemotionThresholdValue').val(20);
                UIToastr.showWarning(LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR, LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR_TIPS + "," + LANG.UI_CM_CDP_HOST_RESOURCE_DEFAULT_CONF);
                // $('#cdpDemotionThresholdValue').val(20);
                // $('#stopTaskThresholdVal').val(10);
                let thresholdType = $('#stopTaskThresholdTypeSelect').val();
                changeThresholdTypeDefaultConf(thresholdType);
                return false;
            }
            let recoveryCdpintervalValue = $('#recoveryCdpintervalValue').val();
            if((recoveryCdpintervalValue<5 || recoveryCdpintervalValue>60) && osType != 'Linux'){
                $('#recoveryCdpintervalValue').val(20);
                UIToastr.showWarning(LANG.UI_CM_CDP_HOST_RESOURCE_MONITOR, LANG.UI_CM_CDP_RESTORE_CDP_PROTECTION_INTERVAL + "," + LANG.UI_CM_CDP_HOST_RESOURCE_DEFAULT_CONF);
                return false;
            }
            _createMsgdata.high_pressure_strategy.cbt_detect_interval = recoveryCdpintervalValue;

            let resourceMonitorStr = "";
            // 资源监测，如果是linux就显示新的配置
            if (osType == 'Linux') {
                // 停止任务触发条件
                var stoptasklable = $('.stoptasklable2').html();  //停止任务label
                var title = [];
                $('.stop_task_div').find('.stop-item-block1').each(function (){
                    var check = $(this).find('input[type="checkbox"]').is(':checked');
                    if (check) {
                        // 选中了
                        var title1l = $(this).find('.stop-item-block1-div-1').html();
                        var val = $(this).find('.stop-item-block1-div-1-1 input[type="number"]').val();
                        var titles1r = $(this).find('.stop-item-block1-div-2').html();

                        var title2l = $(this).find('.stop-item-block1-div-3').html();
                        var val2 = $(this).find('.stop-item-block1-div-3-1 input[type="number"]').val();
                        var titles2r = $(this).find('.stop-item-block1-div-4').html();
                        title.push(title1l + ' ' +  val + titles1r + ' ' + title2l + ' ' +  val2 + titles2r);
                    }
                })
                if (title.length > 0) {
                    resourceMonitorStr += stoptasklable + ':<br> ' + '&nbsp;&nbsp;&nbsp;&nbsp;' + title.join('<br>&nbsp;&nbsp;&nbsp;&nbsp;') + '<br>';
                }
                var cdpdemotionthresholdvaluelable = $('.cmCdpdemotionlable').html();  //持续数据保护降级label
                var cbt_flag = $('#cmCdpDemotionSwitchResources').is(':checked');
                var des = getSwitchDes(cbt_flag ? 1 : 2);
                // resourceMonitorStr += cdpdemotionthresholdvaluelable + ': ' + des + '<br>';
                if (cbt_flag) {
                    // 开启

                    // 任务暂停持续数据保护条件
                    var stoptasklable = $('.cdpdemotionresourcediv-label').html();  // 任务暂停持续数据保护条件label
                    var title = [];
                    $('.cdpdemotionresourcediv').find('.stop-item-block1').each(function (){
                        var check = $(this).find('input[type="checkbox"]').is(':checked');
                        if (check) {
                            // 选中了
                            var title1l = $(this).find('.stop-item-block1-div-1').html();
                            var val = $(this).find('.stop-item-block1-div-1-1 input[type="number"]').val();
                            var titles1r = $(this).find('.stop-item-block1-div-2').html();
                            var tit = title1l + ' ' +  val + titles1r;
                            var title2l = $(this).find('.stop-item-block1-div-3').html();
                            if (title2l != undefined && title2l != '') {
                                var val2 = $(this).find('.stop-item-block1-div-3-1 input[type="number"]').val();
                                var titles2r = $(this).find('.stop-item-block1-div-4').html();
                                tit += ' ' + title2l + ' ' +  val2 + titles2r;
                            }
                            title.push(tit);
                        }
                    })
                    if (title.length > 0) {
                        resourceMonitorStr += stoptasklable + ':<br> ' + '&nbsp;&nbsp;&nbsp;&nbsp;' + title.join('<br>&nbsp;&nbsp;&nbsp;&nbsp;') + '<br>';
                    }

                    // 任务恢复持续数据保护条件
                    var stoptasklable = $('.cdpdemotionresourcediv2-label').html();  // 任务恢复持续数据保护条件label
                    var title = [];
                    $('.cdpdemotionresourcediv2').find('.stop-item-block1').each(function (){
                        var check = $(this).find('input[type="checkbox"]').is(':checked');
                        if (check) {
                            // 选中了
                            var title1l = $(this).find('.stop-item-block1-div-1').html();
                            var val = $(this).find('.stop-item-block1-div-1-1 input[type="number"]').val();
                            var titles1r = $(this).find('.stop-item-block1-div-2').html();
                            var tit = title1l + ' ' +  val + titles1r;
                            var title2l = $(this).find('.stop-item-block1-div-3').html();
                            if (title2l != undefined && title2l != '') {
                                var val2 = $(this).find('.stop-item-block1-div-3-1 input[type="number"]').val();
                                var titles2r = $(this).find('.stop-item-block1-div-4').html();
                                tit += ' ' + title2l + ' ' +  val2 + titles2r;
                            }
                            title.push(tit);
                        }
                    })
                    if (title.length > 0) {
                        resourceMonitorStr += stoptasklable + ': <br>' + '&nbsp;&nbsp;&nbsp;&nbsp;' + title.join('<br>&nbsp;&nbsp;&nbsp;&nbsp;') + '<br>';
                    }

                    // 检测时间设置
                    var time_label = $('.cdpdemotionresourcediv3-label').html(); // label
                    let switch_time_flag = $('#cmCdpDemotionSwitchTime').is(':checked');
                    var des = getSwitchDes(switch_time_flag ? 1 : 2);
                    resourceMonitorStr += time_label + ': ' + des + '<br>';
                    if (switch_time_flag) {
                        // 开启
                        var title = [];
                        $('.cdpdemotiontimesetdiv').find('.interval_time_item').each(function (){
                            var title1l = $(this).find('.interval_time_item-1').html();
                            var val = $(this).find('.interval_time_item-2 input[name="stop_task_condition_time"]').val();
                            var duration = $(this).find('.interval_time_item-3').html(); // 间隔
                            var val2 = $(this).find('.interval_time_item-4 input[name="stop_task_condition_time_interval"]').val();
                            var title1r = $(this).find('.interval_time_item-5 select[name="stop_task_condition_time_type"] option:selected').text();

                            title.push(title1l + ' ' + val + duration  + ' ' + val2 + title1r);
                        })
                        if (title.length > 0) {
                            resourceMonitorStr += '&nbsp;&nbsp;&nbsp;&nbsp;' + title.join('<br>&nbsp;&nbsp;&nbsp;&nbsp;') + '<br>';
                        }
                    }
                }
            } else {
                let stoptasklable = $('.stoptasklable').html();  //停止任务label
                let cmCdpdemotionlable = $('.cmCdpdemotionlable').html();  //持续数据保护降级label
                let cdpdemotionthresholdvaluelable = $('.cdpdemotionthresholdvaluelable').html();  //持续数据保护降级label
                let recoverycdpintervallable = $('.recoverycdpintervallable').html();  //恢复持续数据保护监控间隔label


                let stopTaskThresholdType = $('#stopTaskThresholdTypeSelect').val();
                let cdpDemotionThresholdType = $('#cdpDemotionThresholdValueType').val();

                let detectionThresholdTypeStr = "%";  //检测阈值类型
                if(stopTaskThresholdType ==2 || cdpDemotionThresholdType ==2){
                    detectionThresholdTypeStr = "MB";
                }else if(stopTaskThresholdType==3 || cdpDemotionThresholdType ==3){
                    detectionThresholdTypeStr = "GB";
                }

                resourceMonitorStr += stoptasklable + ": " + $('#stopTaskThresholdVal').val()+ detectionThresholdTypeStr + "<br>";
                resourceMonitorStr += cmCdpdemotionlable + ": " + getSwitchDes(_createMsgdata.high_pressure_strategy.cbt_enable_flag) + "<br>";
                if(_createMsgdata.high_pressure_strategy.cbt_enable_flag == CONF.FLAG.SET){
                    resourceMonitorStr += cdpdemotionthresholdvaluelable + ": " + $('#cdpDemotionThresholdValue').val() + detectionThresholdTypeStr + "<br>";
                    resourceMonitorStr += recoverycdpintervallable + ": " + $('#recoveryCdpintervalValue').val() + LANG.UI_JOB_MINUTE + "<br>";
                }
            }
            _createMsgdata.resourceMonitorStr = resourceMonitorStr;
            return _createMsgdata;
        }
    };
}();
