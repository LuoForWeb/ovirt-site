var VerificationJobDetail = function () {
    var itemPlanEditorJob = null,itemResultEditorJob = null;
    var editorHeight = 150;// 编辑器的高度
    var planHeight = 750; // 方案的高度
    var isInitUrl = false;// 是否加载了iframe
    var vm_uuid = '';// 当前设备的内嵌机uuid
    var vnc_url = '';// 当前设备的内嵌机url
    let _taskStatus; //监控任务状态 只有 1（等待） 4（停止） 8（错误）可以启动设备的任务
    let objects; // 所有的设备列表
    let chooseItem = ''; // 当前选中的对象
    let _jobUuid;
    let report_uuid;
    let initScreenTag = 0;// 标识下当前的截图 0 是完整的，1只有生产 2只有验证
    let report_status; // 报告生成状态
    let currentPage = 1;// 当前选中的页码
    let isLoading = false; // 标记是否首次加载数据
    let isOperateIng = false; // 标记是否可以配置报告等配置
    let chooseItems = []; // 选中的设备列表
    let isReady = false; // 是否可以截屏操作
    let jobResultContent = ''; // 记录最新的结论编辑器的内容
    let checkIsScreenEmpty = false; // 记录当前的截屏是否是空的
    let maxBootTime = ''; // 单个设备的最大开机时间
    let screenFlag = ''; // 开机结果截屏验证
    let updateIntervals = 50;
    window.angle = 0; // 初始角度
    let interval1,interval2,interval3,interval4;
    let initTaskTableFlag = false;

    function addListener() {
        $('.lefts').html(' <label>\n' +
            '                            <input id="batchChoose" type="checkbox" class="input-check">\n' +
            '                        </label>');
        // 批量按钮事件
        $('#batchChoose').change(function (){
            const isChecked = $(this).is(':checked'); // 获取批量选择按钮的状态
            $('input[name="batchChoose[]"]').prop('checked', isChecked); // 设置所有复选框的状态
            if (isChecked) {
                chooseItems = getBatchValue();
            } else {
                chooseItems = [];
            }
            changeBtn();
        })

        // 使用事件委托监听单个复选框的变化事件
        $(document).on('change', 'input[name="batchChoose[]"]', function() {
            const allChecked = $('input[name="batchChoose[]"]').length === $('input[name="batchChoose[]"]:checked').length;
            chooseItems = getBatchValue();
            $('#batchChoose').prop('checked', allChecked);
            changeBtn();
        });

        // 检测批量启动按钮的
        function changeBtn(){
            if ($('input[name="batchChoose[]"]:checked').length == 0) {
                $('.batch-btn').removeClass('batch-active').addClass('batch-disabled');
            }
            $('input[name="batchChoose[]"]:checked').each(function() {
                if ($(this).data('status') == 1) {
                    $('.batch-btn').removeClass('batch-disabled').addClass('batch-active');
                } else {
                    $('.batch-btn').removeClass('batch-active').addClass('batch-disabled');
                }
            });
        }

        // 增加任务详情点击事件
        $('#open_job_detail').click(function (){
            // 显示抽屉
            $('#drawer-job_detail_config').drawer('show');
        })

        // 增加设备点击操作
        $('#item_operate_job').on('click', function (){
            var flag = $(this).attr('data-flag');
            if (flag == 1) {
                // 启动任务
                startJob([chooseItem]);
            } else {
                // 停止任务
                // 后台暂未支持
            }
        })

        function startJob(item_uuids) {
            // 启动任务
            Metronic.blockUI({
                target: '#jobDetail',
                animate: true
            });
            let params = {};
            params.item_uuids = item_uuids;
            params.task_uuid = _jobUuid;
            params.backup_mode =  1;
            pAjaxRequest(params, "/api/v1/verification/jobs/start_select", "POST", function (d) {
                Metronic.unblockUI('#jobDetail');
                let op = LANG.UI_VERIFY_START_JOB;
                if (operateResponseList(d, op)) {
                }
            }, false);
        }

        // 打开验证环境事件
        $('.open_verify_machine').off('click').on('click', function (){
            if (!isInitUrl) {
                opereate(vm_uuid, vnc_url, 'show_iframe_url');
                // 需要先请求接口，然后渲染
            } else {
                isInitUrl = true;
                $('#full-machine-block').show();
            }
        });

        // 增加验证方案点击事件
        $('#show_final_item_plan').off('click').on('click', function (){
            // 显示抽屉
            $('#drawer-item_plan_editor').drawer('show');
        })

        // 增加更多信息点击事件
        $('#show_final_item_cofnig').off('click').on('click', function (){
            let params = {};
            params.task_uuid = _jobUuid;
            params.object_uuid = chooseItem;
            pAjaxRequest(params, '/api/v1/verification/object_info', 'GET', (result) => {
                if (result.success){
                    let general_config = result.data.general_config;
                    let network_config = result.data.network_config;
                    let verify_config = result.data.verify_config;
                    $('#objectname').html(result.data.object_name);
                    //基本信息
                    // if(result.data.module_type == 2){
                    // 	$('.hypervisorDiv').show();
                    // 	$('#hypervisor').html('VMware Vsphere');
                    // }
                    $('#timepoint').html(verify_config.timepoint_des);

                    if (verify_config.timepoint_range == 2) {
                        $('#max_timepoint_verify').parent().hide();
                    }
                    $('#max_timepoint_verify').html(verify_config.max_timepoint_verify + LANG.UI_PUBLIC_NUM);

                    if(verify_config.timepoint_range == 3){
                        let des = "";
                        for (let i=0;i<verify_config.timepoint_list.length;i++){
                            des += verify_config.timepoint_list[i] + "<br>";
                        }
                        $('#timepointInfo').html(des);
                        $('.timepointDiv').show();
                    }else{
                        $('.timepointDiv').hide();
                    }

                    //主机配置
                    $('#cpuNum').html(general_config.cpu_num);
                    $('#coreNum').html(general_config.core_num);
                    $('#memorySize').html(general_config.memory_size + "GB");
                    $('#osType').html(general_config.os_type);
                    $('#maxBootTime').html(maxBootTime);
                    $('#screenFlag').html(getFlagLevelInfo(screenFlag));
                    //网络信息
                    if(!network_config){
                        $('#networkInfo').html('--');
                    }else{
                        let options = {
                            data: network_config.netcard_list,
                            pagination: false, //分页
                            columns: [
                                // {
                                // 	field: '',
                                // 	title: "网卡名",
                                // 	formatter: function (value, row, index, field) {
                                // 		return row.network_name;
                                // 	}
                                // },
                                {
                                    field: '',
                                    title: LANG.UI_PUBLIC_IP_ADDRESS,
                                    formatter: function (value, row, index, field) {
                                        return row.ip_address;
                                    }
                                },
                                {
                                    field: '',
                                    title: LANG.UI_PUBLIC_IP_GATEWAY,
                                    formatter: function (value, row, index, field) {
                                        return row.gateway;
                                    }
                                },
                                {
                                    field: '',
                                    title: LANG.UI_PUBLIC_IP_NETMASK,
                                    formatter: function (value, row, index, field) {
                                        return row.netmask;
                                    }
                                },
                            ],
                        }

                        if (!initTaskTableFlag) {
                            initTaskTableFlag = true;
                        } else {
                            // 如果已经初始化销毁表格再初始化新表
                            // 销毁表格
                            $('#networkTable').bootstrapTable('destroy');
                        }
                        $('#networkTable').baseTableConfig().init(options);
                    }
                    $('#drawer-item_cofnig').drawer('show');
                }
            });
        })

        // 验证结果
        $('#show_final_item_result').off('click').on('click', function (){
            // 显示抽屉 只显示部分信息预览
            JobReportDetail.init({'uuid': _jobUuid, 'agent_uuid': chooseItem, 'pre': 100, job_status: _taskStatus})
        })

        // 保存结论和方案的事件监听
        $('#save_result').off('click').on('click', function (){
            // 进行接口请求保存截图
            var params = {
                'field': 'item_result',
                'value': itemResultEditorJob.getContent()
            };
            saveFields(params);
        })

        $('#save_plan').off('click').on('click', function (){
            var params = {
                'field': 'item_plan',
                'value': itemPlanEditorJob.getContent()
            };
            saveFields(params);
        })

        // 预览报告
        $('#preReport').on('click', function (){
            JobReportDetail.init({'uuid': _jobUuid, 'agent_uuid': chooseItem, 'pre': 1})
        })

        // 提交报告
        $('#submitReport').on('click', function (){
            if (report_status != 9) {
                // 此时报告已经生成
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_GMP_JOB_SAVE_TIPS);
                return false;
            }
            if (!isOperateIng || !isReady) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_GMP_JOB_NOT_READY);
                return false;
            }
            JobReportDetail.init({'uuid': _jobUuid, 'agent_uuid': chooseItem, 'pre': 2})
        })

        // 批量启动
        $('.batch-btn').on('click', function (){
            var id = getBatchValue();
            if (id.length <= 0) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_VM_MACHINE_MODIFY_CHOOSE_ONE);
                return;
            }
            startJob(id);
        })
        //得到开启和关闭的HTML内容
        var getFlagLevelInfo = function(flag){
            var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
            if(flag == 2){
                html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
            }
            return html;
        }
        // 获取选中的值
        function getBatchValue(){
            return $('input[name="batchChoose[]"]:checked').map(function() {
                return $(this).val();
            }).get();
        }

        // 搜索事件
        $('#search_action').on('click', function (){
            initObjectGrid();
        })

        // 如果你需要在窗口大小改变时重新获取高度，可以这样做：
        $(window).resize(function() {
            resizeTemp();
        });
    }

    // 按钮绑定事件
    function buttonClickListener() {
        // 增加截图的鼠标移入事件
        $(".screen-item .products .div-img").mouseenter(function(){
            var height = $(this).closest('.products').height();
            $('.show-screen-item-shadow-box').show(); // 显示遮罩
            $('.show-screen-item-shadow-box .shadow-box').css({
                'height': height * 2 + 64
            });
            // 鼠标点击放大事件
            $('.icon-max').off('click').on('click', function (){
                var doms = $(this).closest('.screen-item-pic-job');
                var pic1 = doms.find('.product-job img').attr('src');
                var pic1_time = doms.find('.product-job .labels-r').html();
                var pic2 = doms.find('.verify-job img').attr('src');
                var pic2_time = doms.find('.verify-job .labels-r').html();
                var title = doms.find('.screen-time-value').html();
                $('#product_img').attr('src', pic1);
                $('#verify_img').attr('src', pic2);
                $('#product_img_title').html('生产截屏（'+pic1_time+'）');
                $('#verify_img_title').html('验证截屏（'+pic2_time+'）');
                $('#title_screen').html(title);
                $('#full-screen-block').show();
            });

        });
        $(".show-screen-item-shadow-box").mouseleave(function(){
            $(this).closest('.show-screen-item-shadow-box').hide(); // 隐藏遮罩
        });

        // 增加关闭事件
        $('#full-screen-block .close-btn').off('click').on('click', function() {
            $('#full-screen-block').hide();
        });
        $('#full-machine-block .close-btn').off('click').on('click', function() {
            $('#full-machine-block').hide();
        });
    }

    // 增加iframe
    function loadIframe(id, url) {
        var iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.title = LANG.UI_PLATFORM_GMP_JOB_VERIFY_VIR;
        iframe.frameBorder = '0';
        iframe.width = '100%';
        iframe.height = '100%';

        var container = document.getElementById(id);
        container.innerHTML = ''; // 清空容器
        container.appendChild(iframe);
    }

    function opereate(vmUuid = '', url = '', id) {
        if (vmUuid == '' || url == '') {
            UIToastr.showInfo(LANG.UI_VM_MACHINE_OPERATION, LANG.UI_PLATFORM_GMP_JOB_NOT_READY);
            return;
        }
        var type = 'look';
        Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
        pAjaxRequest({type:type}, "/api/v1/virtual/operate/"+vmUuid, "POST", function (result) {
            Metronic.unblockUI('#jobDetail');
            if (result.code == 0) {
                loadIframe(id, url);
                isInitUrl = true;
                $('#full-machine-block').show();
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
            }
        }, false);
    }

    // 编辑器监听事件
    function listenEditor(editor, id){
        // 等待编辑器加载完成
        var is_show = false;
        var height = editorHeight;
        if (id == 'item_plan_job') {
            height = planHeight;
        }
        editor.ready(function() {
            if (id == 'item_result_job') {
                if (report_status == 9) {
                    jobResultContent = editor.getContent();
                    // 结论的编辑器，需要监听鼠标离开事件，有变化则保存本次的修改内容
                    editor.on('blur', function (){
                        // 获取编辑器内容
                        var content = editor.getContent();
                        if (content != jobResultContent) {
                            // 请求接口保存结论内容
                            //console.log('请求接口保存:', content);
                            var params = {
                                'field': 'item_result',
                                'value': content
                            };
                            saveFields(params);
                        }
                        jobResultContent = content;
                        // console.log('当前编辑器内容:', content);
                    })
                }
            }
            // 鼠标离开编辑器区域时隐藏工具栏
            $('#' + id + '_editor').find('*').filter(function() {
                return this.id && this.id.endsWith('_toolbarbox');
            }).hide();
            $('#' + id + '_editor').find('*').filter(function() {
                return this.id && this.id.endsWith('_iframeholder');
            }).css({
                'height': height
            });

            // 获取工具栏 DOM 元素
            $('#'+id + ' .item-result-right').on('click', function (){
                if (is_show) {
                    // 鼠标离开编辑器区域时隐藏工具栏
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_toolbarbox');
                    }).hide();
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_iframeholder');
                    }).css({
                        'height': height
                    });
                    $(this).html('<i class="viconfont vicon-gongjuyincang"></i>');
                    $(this).attr('title', LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS);
                    is_show = false;
                } else {
                    // 鼠标进入编辑器区域时显示工具栏
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_toolbarbox');
                    }).show();
                    $('#' + id + '_editor').find('*').filter(function() {
                        return this.id && this.id.endsWith('_iframeholder');
                    }).css({
                        'height': height - 80
                    });
                    $(this).attr('title', LANG.UI_PLATFORM_INDUSTRY_EDITOR_TOOLS2);
                    $(this).html('<i class="viconfont vicon-gongju"></i>');
                    is_show = true;
                }
            })
        });
    }

    //初始化基本信息
    let initBasicInfo = function(){
        let updateInterval = 5000;
        let init = function(){
            if(0 == $('#task_uuid').size()){
                clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
                return;
            }
            !isLoading && Metronic.blockUI({target: '#jobDetail',animate: true});
            pAjaxRequest({}, "/api/v1/industry/job/"+_jobUuid, "GET", function (res){
                !isLoading && Metronic.unblockUI('#jobDetail');
                isLoading = true;
                setBasicInfo(res.data, timerTask.VMJobDetails_taskRunningInfo);
            });

            timerTask.VMJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
        }
        init();
    }

    //设置基本信息
    let setBasicInfo = function(data, timeoutID){
        if(data.length == 0){
            clearTimeout(timeoutID);
            UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
            setTimeout(function(){
                LOCATION('./content/platform/jobs/verify_jobs.php','verification_job');
            }, 3000);
        }

        //基本信息
        $('#taskName').html(data.basic_info.task_name);
        $('#taskName2').html(data.basic_info.task_name);
        $('#taskType').html(data.basic_info.task_type_des);
        if(data.basic_info.task_status){
            var txt = data.basic_info.task_status_des;
            if (data.basic_info.task_status == CONF.TASK_STATUS.RUNNING) {
                txt = LANG.UI_HOMEPAGEPRO_VERIFYING;
            }
            $('#status').html('<span class="label header-status ' + getStatusLevelClass(data.basic_info.task_status) + '" >' + txt + '</span>');
        }

        //保存获取到的任务参数
        _taskStatus = data.basic_info.task_status;

        //概要
        $('#startTime').html(data.basic_info.start_time);
        $('#intervalTime').html(data.basic_info.run_time);
        //策略
        $('#createTime').html(data.basic_info.create_time);
        $('#nextTime').html(data.basic_info.next_time);

        // 时间策略
        let timeDes;
        if (data.time_strategy.length) {
            let timeStrategy = getTimeStrategy(data.time_strategy);
            timeDes = timeStrategy.full;
        } else {
            timeDes = LANG.UI_RECOVERY_SURE_BACKUP_START_TYPE_NOW;
        }
        $('#timeStrategy').html(timeDes);

        //验证方式
        $('#verifyMode').html(data.basic_info.verify_mode_des);
        //验证类型
        $('#verifyType').html(data.basic_info.verify_type_des);
        // 备份节点IP地址
        $('#backup_server_ip').html(data.basic_info.backup_server_ip);
        // 并发验证对象数量
        $('#deal_vm_num').html(data.basic_info.limit_boot_vm_num);
        // 线程数量
        $('#doc_compare_thread').html(data.basic_info.doc_compare_thread);
        // 过载保护
        $('#ignore_resource_limit').html(getFlagLevelInfo2(data.basic_info.ignore_resource_limiting_flag));
        //备份服务器IP
        $('#serverIP').html(data.high_strategy.nfs_server_ip);
        //虚拟实验室
        $('#labname').html(data.high_strategy.virtual_lab_name);

        //初始化对象列表
        initObjectGrid();
    }

    //得到开启和关闭的HTML内容
    var getFlagLevelInfo2 = function(flag){
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if(!flag){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    //得到时间策略描述信息
    var getTimeStrategy = function(msg){
        var timeInfo = {full:LANG.UI_PUBLIC_NOTHING, inc:LANG.UI_PUBLIC_NOTHING, diff:LANG.UI_PUBLIC_NOTHING, pincr:LANG.UI_PUBLIC_NOTHING};
        if(!msg){
            return timeInfo;
        }
        for(var i=0; i<msg.length; i++){
            var strategy = msg[i];
            var des = "";
            if(CONF.STRATEGY_TYPE.DAY == strategy.type){
                des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
            }else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
                des += getStrategyFrequency(strategy);
                if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                    des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
                }else{
                    des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
                }
            }else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
                des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
            }else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
                des += strategy.startTime;
            }else{
                des += LANG.UI_PUBLIC_NOTHING;
            }

            if(1 == strategy.mode){
                timeInfo.full = des;
            }else if(2 == strategy.mode){
                timeInfo.inc = des;
            }else if(3 == strategy.mode){
                timeInfo.diff = des;
            }else if(9 == strategy.mode){
                timeInfo.pincr = des;
            }
        }
        return timeInfo;
    }

    var getStrategyDays = function(days){
        var desDays = '';
        $.each(days, function(i,d){
            if(1 == d){
                var day = i+1;
                if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                    desDays += day + ", ";
                }else{
                    desDays += "Day" + day + ", ";
                }

            }
        });
        return desDays;
    }

    //获取每周显示日期
    var getStrategyWeek = function(days){
        var desDays = '';
        $.each(days, function(i,d){
            if(1 == d){
                desDays += CONF.WEEK[i] + ", ";
            }
        });
        return desDays;
    }

    var getEachStrategy = function(strategy){
        var desEach = '';
        desEach += strategy.start_time;
        //如果是英文版 需要加空格
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            desEach += " "; //策略开始时间
        }
        /*desEach += LANG.UI_STRATEGY_START + ", ";
        if(strategy.roll_flag){
            if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
                desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.end_time;
            }else{
                desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + strategy.end_time + LANG.UI_STRATEGY_END;
            }

        }else{
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }*/
        desEach += "<br>";
        return desEach;
    }

    //得到间隔描述
    var getStrategyFrequency = function(strategy){
        var frequency = "";
        var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
        for(var i=1;i<=52;i++){
            if(strategy.frequency == "s" +i){
                if(i == 1){
                    frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
                }
                else{
                    frequency = frequencyLang.replace('x', i) + ",";
                }
            }
        }
        return frequency;
    }

    //得到状态的显示类型
    var getStatusLevelClass = function(level){
        var levelClass = '';
        switch(level){
            case 1:
                levelClass = "label-info";
                break;
            case 2:
            case 3:
                levelClass = "label-success";
                break;
            case 4:
                levelClass = "label-default";
                break;
            case 7:
                levelClass = "label-warning";
                break;
            case 6:
            case 8:
                levelClass = "label-danger";
                break;
            default:
                levelClass = "label-info";
                break;
        }
        return levelClass;
    }

    //初始化状态值显示
    const setStatusDes = function(status){
        let labelClass = "client-status-blue";
        switch(status){
            case 0:	//未知
                labelClass = "client-status-blue";
                break;
            case 1:	//等待
                labelClass = "client-status-blue";
                break;
            case 2:	//运行
                labelClass = "client-status-default";
                break;
            case 3:	//跳过
                labelClass = "client-status-blue";
                break;
            case 4:	//错误
                labelClass = "client-status-yellow";
                break;
            case 5:	//成功
                labelClass = "client-status-blues";
                break;
            case 6:	//完成
                labelClass = "client-status-gereen";
                break;
            case 7:	//异常
                labelClass = "client-status-yellow";
                break;
            default:
                labelClass = "client-status-default";
        }
        return labelClass;
    }

    // 编辑器事件
    var initEditor = function (){
        itemResultEditorJob = UE.getEditor('item_result_job_editor', {
            // 隐藏工具栏
            // toolbars: [],
            initialFrameHeight:editorHeight - 80,
            zIndex: 999, // 编辑器层级的基数
            elementPathEnabled: false, // 是否启用元素路径，默认是显示
            wordCount: false,          //是否开启字数统计
            //,maximumWords:10000       //允许的最大字符数
            //浮动时工具栏距离浏览器顶部的高度，用于某些具有固定头部的页面
            topOffset: 50,
            wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT + ' {#count} ' + LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
        }); //编辑器控件需要初始化
        listenEditor(itemResultEditorJob, 'item_result_job');

        itemPlanEditorJob = UE.getEditor('item_plan_job_editor', {
            // 隐藏工具栏
            // toolbars: [],
            elementPathEnabled: false,  // 是否启用元素路径，默认是显示
            wordCount: false,          //是否开启字数统计
            initialFrameHeight:planHeight - 80,
            zIndex: 10090, // 编辑器层级的基数
            //浮动时工具栏距离浏览器顶部的高度，用于某些具有固定头部的页面
            topOffset: 50,
            wordCountMsg: LANG.UI_PLATFORM_INDUSTRY_EDITOR_LEFT + ' {#count} ' + LANG.UI_PLATFORM_INDUSTRY_EDITOR_RIGHT
        }); //编辑器控件需要初始化
        listenEditor(itemPlanEditorJob, 'item_plan_job');
    }

    // 只保留截图验证的最后一个生产和验证的删除按钮
    // 显示最新的一组截屏，并且最下面的按钮生成事件，选中最后一个
    function calcDel(){
        // 使用jQuery选择页面上最后一个具有'screen-desc'类的'textarea'元素
        $('.screen-item-pic-job .product-job .titles').find('i').remove(); // 移除所有的
        $('.screen-item-pic-job .verify-job .titles').find('i').remove(); // 移除所有的
        $('.screen-item-pic-job .screen-time-job-del').find('i').remove(); // 移除所有的
        $('.screen-item-pic-job .screen-desc .result').find('i').removeClass('i-active'); // 移除所有的

        if (report_status == 9) {
            $('.screen-item-pic-job').last().find('.screen-time-job-del').append('<i class="viconfont vicon-zujianshanchu"></i>'); // 给最后一个增加删除图标
            $('.screen-item-pic-job').last().find('.product-job .titles .labels-l').append('<i class="viconfont vicon-zujianshanchu"></i>'); // 给最后一个增加删除图标
            $('.screen-item-pic-job').last().find('.verify-job .titles .labels-l').append('<i class="viconfont vicon-zujianshanchu"></i>'); // 给最后一个增加删除图标
            //  $('.screen-item-pic-job .screen-desc .result').find('i').addClass('i-active');
        } else {
            // 移除截图的所有删除
            $('.screen-item-pic-job .screen-time-job-del').find('i').remove(); // 移除所有的
            // 隐藏截屏的按钮
            $('#item_screen .screen-pic-btn').hide();
        }

        $('.screen-item-pic-job').hide();
        $('.screen-item-pic-job').last().show();

        // 增加截图的鼠标移入事件
        buttonClickListener();

        // 增加比对结果的更改事件监听
        // 监听每个组内的 input、textarea 和 radio 的变化
        $('.screen-item-pic-job').on('change input', 'input, textarea, input[type="radio"]', function () {
            // 找到当前元素所在的组
            let $group = $(this).closest('.screen-item-pic-job');

            // 修改该组内按钮的颜色
            $group.find('.screen-desc .result i').addClass('i-active'); // 改变按钮背景色为黄色
        });

        addListenerDel();
        // 这里计算下下面的按钮切换事件
        const totalItems = $('.screen-item-pic-job').length;
        currentPage = totalItems;
        const itemsPerPage = 1;
        renderPagination(totalItems, itemsPerPage, currentPage);
    }

    function renderPagination(totalItems, itemsPerPage, currentPage) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const paginationContainer = $('.screen-bottom');
        paginationContainer.empty();

        if (totalItems > 0) {
            if (currentPage > 1) {
                addPageButton(paginationContainer, -1, false);
            } else {
                addPageButton(paginationContainer, -1, false, true);
            }
        }

        // 如果总页数小于等于10，直接渲染所有页码
        if (totalPages <= 10) {
            for (let i = 1; i <= totalPages; i++) {
                addPageButton(paginationContainer, i, i === currentPage);
            }
        } else {
            // 渲染前3页
            for (let i = 1; i <= 3; i++) {
                addPageButton(paginationContainer, i, i === currentPage);
            }

            // 如果当前页在前3页内，不需要省略号
            if (currentPage > 4) {
                paginationContainer.append('<a href="javacsript:void(0)">...</a>');
            }

            // 渲染当前页附近的页码（前后各两页）
            const startPage = Math.max(4, currentPage - 2);
            const endPage = Math.min(totalPages - 2, currentPage + 2);

            for (let i = startPage; i <= endPage; i++) {
                addPageButton(paginationContainer, i, i === currentPage);
            }

            // 如果当前页在后3页内，不需要省略号
            if (currentPage < totalPages - 3) {
                paginationContainer.append('<a href="javacsript:void(0)">...</a>');
            }

            // 渲染最后3页
            for (let i = totalPages - 2; i <= totalPages; i++) {
                addPageButton(paginationContainer, i, i === currentPage);
            }
        }
        if (totalItems > 0) {
            if (currentPage < totalItems) {
                addPageButton(paginationContainer, -2, false);
            } else {
                addPageButton(paginationContainer, -2, false, true);
            }
        }

        // 添加点击事件
        paginationContainer.find('a').click(function(e) {
            e.preventDefault();
            let newPage = parseInt($(this).attr('data-num'));
            if (newPage == -1) {
                newPage = currentPage - 1;
            } else if (newPage == -2) {
                newPage = currentPage + 1;
            }

            renderPagination(totalItems, itemsPerPage, newPage);
            // 在这里可以添加其他操作，比如更新内容显示等
            $('.screen-item-pic-job').hide();
            $('.screen-item-pic-job').eq(newPage - 1).show();
        });
    }

    function addPageButton(container, pageNumber, isActive, isDisabled) {
        var number = pageNumber;
        var style = '';
        if (pageNumber == -1) {
            // 上一页
            pageNumber = '<i class="viconfont vicon-fanhui"></i>';
            style = 'style="border: 1px solid #F1F3F5;"';
        } else if (pageNumber == -2) {
            // 下一页
            pageNumber = '<i class="viconfont vicon-gengduo"></i>';
            style = 'style="border: 1px solid #F1F3F5;"';
        }
        let link = `<a href="javacript:void(0);" ${style} data-num="${number}">${pageNumber}</a>`;
        if (isActive) {
            link = `<a href="javacript:void(0);" ${style} data-num="${number}" class="active">${pageNumber}</a>`;
        } else if (isDisabled) {
            link = `<a href="javacript:void(0);" ${style} data-num="${number}" class="disabled">${pageNumber}</a>`;
        }
        container.append(link);
    }

    // 计算截屏是否删除完毕
    function checkIsEmpty(){
        const totalItems = $('.screen-item-pic-job').length;
        if (totalItems == 0) {
            // 如果是空的，需要给一个默认的内容
            var html = getEmptyScreen();
            checkIsScreenEmpty = true;
            $('.block-item-bottom .screen-bottom').before(html);
        }
    }
    // 删除按钮事件
    function addListenerDel(){
        // 绑定删除事件
        // 截屏时间那删除是删除整个，生产和验证删除是删除对应的
        $('.screen-item-pic-job .product-job .titles').find('i').off('click').on('click', function() {
            // 生产截屏删除事件
            // 需要判断下同级的验证截屏是否还存在，不存在就删除整个
            if ($(this).closest('.screen-item-pic-job').find('.verify-job').length > 0) {
                $(this).closest('.product_flag').removeClass('product-job');
                $(this).closest('.product_flag').find('img').attr('src', '/img/platform/screen_job.png');
                initScreenTag = 2;
                // console.log('initScreenTag', initScreenTag)
            } else {
                $(this).closest('.screen-item-pic-job').remove();
                calcDel();
                initScreenTag = 0;
                checkIsEmpty();
            }
        })
        $('.screen-item-pic-job .verify-job .titles').find('i').off('click').on('click', function() {
            // 验证截屏删除事件
            // 需要判断下同级的生产截屏是否还存在，不存在就删除整个
            if ($(this).closest('.screen-item-pic-job').find('.product-job').length > 0) {
                $(this).closest('.verify_flag').removeClass('verify-job');
                $(this).closest('.verify_flag').find('img').attr('src', '/img/platform/screen_job.png');
                initScreenTag = 1;
                // console.log('initScreenTag', initScreenTag)
            } else {
                $(this).closest('.screen-item-pic-job').remove();
                calcDel();
                initScreenTag = 0;
                checkIsEmpty();
            }
        })
        $('.screen-item-pic-job .screen-time-job-del>i').off('click').on('click', function() {
            // 整个截屏删除事件
            $(this).closest('.screen-item-pic-job').remove();
            initScreenTag = 0; // 只有最后一组才有全部删除
            calcDel();
            checkIsEmpty();
        })
        $('.screen-item-pic-job .screen-desc .result').find('i').off('click').on('click', function (){
            if (!$(this).hasClass('i-active')) {
                return false;
            }
            if (report_status != 9) {
                return false;
            }
            var screen_list = [];
            var checks = true;
            var msg = '';
            if ($('.screen-item-pic-job').length>0) {
                // 获取所有的截屏
                $('.block-item-bottom').find('.screen-item-pic-job').each(function() {
                    var create_time1 = $(this).find('.product-job .labels-r').html();
                    var product = $(this).find('.product-job img').attr('src');
                    if (product == undefined) {
                        product = '';
                    }
                    var verify = $(this).find('.verify-job img').attr('src');
                    var create_time2 = $(this).find('.verify-job .labels-r').html();
                    if (verify == undefined) {
                        verify = '';
                    }
                    var remark = $(this).find('.screen-desc textarea').val();
                    var result = $(this).find('.screen-desc input[type="radio"]:checked').val();
                    if (product == '' && verify == '') {
                        checks = false;
                        msg = LANG.UI_PLATFORM_GMP_JOB_SCREEN_TIPS;
                        return false;
                    }
                    if (result == '') {
                        msg = LANG.UI_PLATFORM_GMP_JOB_SCREEN_TIPS2;
                        return false;
                    }
                    screen_list.push(
                        {'product': product, 'verify': verify, 'create_time': create_time1, 'create_time2': create_time2, 'remark': remark, 'result': result}
                    );
                })
            }
            if (!checks) {
                return UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, msg);
            }
            // 进行接口请求保存截图
            var params = {
                'field': 'screen_list',
                'value': JSON.stringify(screen_list),
            };
            saveFields(params, screen_list);
        })
    }

    // 保存信息
    function saveFields(params, screen_list = []){
        var div = "#jobDetail";
        Metronic.blockUI({target: div,animate: true});
        pAjaxRequest(params, "/api/v1/industry/job/"+_jobUuid+"/client/"+chooseItem, "POST", function (d) {
            Metronic.unblockUI(div);
            let screen_total_compare_num;
            if (d.code == 0) {
                if (screen_list.length > 0) {
                    // update screen num
                    var screen_total_num = screen_total_compare_num = screen_list.length; // 总数
                    var screen_total_normal_num = 0; // 一致
                    var screen_total_error_num = 0; // 异常
                    for (var j in screen_list) {
                        if (parseInt(screen_list[j].result) == 1) {
                            screen_total_normal_num++;
                        } else {
                            screen_total_error_num++;
                        }
                    }
                    $('#screen_total_num').html(screen_total_num);
                    $('#screen_total_compare_num').html(screen_total_compare_num);
                    $('#screen_total_normal_num').html(screen_total_normal_num);
                    $('#screen_total_error_num').html(screen_total_error_num);
                    calcDel();
                }
                UIToastr.showSuccess(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_GMP_JOB_SAVE_SUCCESS);
                return;
            }
            UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, d.message);
        })
    }

    // 添加截屏按钮事件
    function addListenerScreen(){
        $('#productScreen_job').off('click').on('click', function() {
            if ((vm_uuid == '' || vnc_url == '') || !isReady) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_GMP_JOB_NOT_READY);
                return false;
            }
            // 生产截屏事件
            if (initScreenTag == 1) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCT, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS);
                return;
            }

            // 请求接口，组装返回数据
            Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
            pAjaxRequest({report_uuid: report_uuid}, "/api/v1/industry/screenshot/produce", "POST", function (result) {
                Metronic.unblockUI('#jobDetail');
                if (result.code == 0) {
                    // 数据渲染
                    var data = result.data;
                    var html = '';
                    if (initScreenTag == 2) {
                        // 存在验证截屏
                        initScreenTag = 0;
                        $('.screen-item-pic-job').last().find('.product_flag').addClass('product-job');
                        $('.screen-item-pic-job').last().find('.product_flag img').attr('src', data.url);
                        $('.screen-item-pic-job').last().find('.product_flag .labels-r').html(data.create_time);
                    } else {
                        // 都不存在，那么存在生产截屏
                        initScreenTag = 1;
                        var pa = {
                            'product': {
                                'url': data.url,
                                'create_time': data.create_time
                            },
                            'result': 0,
                            'remark': ''
                        };
                        html = getScreenHtml(pa, 2);
                        $('.block-item-bottom .screen-bottom').before(html);
                    }
                    calcDel();
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCT, result.message);
                }
            });
        })

        $('#verifyScreen_job').off('click').on('click', function() {
            // 验证截屏事件
            if ((vm_uuid == '' || vnc_url == '') || !isReady) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_GMP_JOB_NOT_READY);
                return false;
            }

            if (initScreenTag == 2) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFY, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS2);
                return;
            }

            // 请求接口，组装返回数据
            Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
            pAjaxRequest({report_uuid: report_uuid}, "/api/v1/industry/screenshot/verify", "POST", function (result) {
                Metronic.unblockUI('#jobDetail');
                if (result.code == 0) {
                    // 数据渲染
                    var data = result.data;
                    var html = '';
                    if (initScreenTag == 1) {
                        // 存在生产截屏
                        initScreenTag = 0;
                        $('.screen-item-pic-job').last().find('.verify_flag').addClass('verify-job');
                        $('.screen-item-pic-job').last().find('.verify_flag img').attr('src', data.url);
                        $('.screen-item-pic-job').last().find('.verify_flag .labels-r').html(data.create_time);
                    } else {
                        // 都不存在，那么存在验证截屏
                        initScreenTag = 2;
                        var pa = {
                            'verify': {
                                'url': data.url,
                                'create_time': data.create_time
                            },
                            'result': 0,
                            'remark': ''
                        };
                        html = getScreenHtml(pa, 1);
                        $('.block-item-bottom .screen-bottom').before(html);
                    }
                    calcDel();
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFY, result.message);
                }
            });
        })

        $('#allScreen_job').off('click').on('click', function() {
            // 一键截屏事件
            if ((vm_uuid == '' || vnc_url == '') || !isReady) {
                UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_GMP_JOB_NOT_READY);
                return false;
            }
            if (initScreenTag == 1) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_ALL, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS_ALL);
                return;
            }
            if (initScreenTag == 2) {
                UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_ALL, LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_EXISTS2_ALL);
                return;
            }
            // 请求接口，组装返回数据
            Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
            pAjaxRequest({report_uuid: report_uuid}, "/api/v1/industry/screenshot", "POST", function (result) {
                Metronic.unblockUI('#jobDetail');
                if (result.code == 0) {
                    // 数据渲染
                    var data = result.data;
                    var html = '';
                    var pa = [{
                        'verify': {
                            'url': data.verify_url,
                            'create_time': data.create_time
                        },
                        'product': {
                            'url': data.produce_url,
                            'create_time': data.create_time
                        },
                        'result': 0,
                        'remark': ''
                    }];
                    html = getScreenHtml(pa);
                    $('.block-item-bottom .screen-bottom').before(html);
                    calcDel();
                } else {
                    UIToastr.showError(LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_ALL, result.message);
                }
            });
        })
    }

    // 动态生成渲染完整的一组截屏html
    var getScreenHtml = function (list, type = 0) {
        // 这里进行数据渲染
        if (list.length > 0) {
            if (checkIsScreenEmpty) {
                // 原来是空的，需要清空下
                $('.screen-item-pic-job').remove();
            }
            checkIsScreenEmpty = false; // 不是空的
        }
        var html = '';
        // 渲染列表
        var k = $('.block-item-bottom .screen-item-pic-job').length;
        for (var j in list) {
            k = k + 1;
            var title = LANG.UI_EMERGENCY_RECOVERY_THE + k + LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_TITLE;
            var products = 'product-job',verigys = 'verify-job';

            if (type == 1) {
                // 只计算验证环境的
                var verify = list[j].verify;
                verigys = '';
                var product = {
                    'url': '/img/platform/screen_job.png',
                    'create_time': ''
                };
            } else if (type == 2) {
                // 只计算生产环境的
                var product = list[j].product;
                products = '';
                var verify = {
                    'url': '/img/platform/screen_job.png',
                    'create_time': ''
                };
            } else {
                var product = list[j].product;
                var verify = list[j].verify;
            }

            var names = getUuid();
            var checked1 = '',checked2 = '';
            if (list[j].result == 1) {
                checked1 = 'checked';
            }
            if (list[j].result == 2) {
                checked2 = 'checked';
            }
            html += `<div class="screen-block screen-item-pic-job">
                        <div class="show-screen-item-shadow-box display-none">
                            <label class="icon-del screen-time-job screen-time-job-del">
                                <i class="viconfont vicon-zujianshanchu"></i>
                            </label>
                            <label class="icon-max">
                                <i class="viconfont vicon-a-Zoom-infangda"></i>
                            </label>
                            <div class="shadow-box">
                            </div>
                        </div>
                        <div class="screen-item">
                            <div class="screen-time-job">
                                <div class="screen-time-value">`+title+`</div>
                            </div>
                            <div class="products product_flag ` + products+`">
                                <div class="div-img">
                                    <img src="`+product.url+`">
                                </div>
                                <div class="titles">
                                    <label class="labels-l">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCTS+`<i class="viconfont vicon-zujianshanchu"></i></label>
                                    <label class="labels-r">`+product.create_time+`</label>
                                </div>
                            </div>
                            <div class="products verify_flag `+verigys+`">
                                <div class="div-img">
                                     <img src="`+verify.url+`">
                                </div>
                                <div class="titles">
                                    <label class="labels-l">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFYS+`<i class="viconfont vicon-zujianshanchu"></i></label>
                                    <label class="labels-r">`+verify.create_time+`</label>
                                </div>
                            </div>
                        </div>
                        <div class="screen-desc" >
                            <div class="result">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT+`：
                                <label><input type="radio" name="`+names+`" `+checked1+` value="1"><span>`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME+`</span></label>
                                <label><input type="radio" name="`+names+`" `+checked2+` value="2"><span>`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME+`</span></label>
                                <i class="viconfont vicon-baocun"></i>
                            </div>
                            <textarea cols="50" rows="2" placeholder="`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_TIPS+`">`+list[j].remark+`</textarea>
                        </div>
                    </div>`;
        }
        return html;
    }

    // 生成uuid
    var getUuid = function () {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if (len) {
            for (i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }

    // 阶段事件
    var initStageAndResult = function (data){
        var k = 9;
        // 处理验证阶段
        if (!data.open_flag) {
            // 未设置开机截屏验证
            $('#stage_status2').parent().hide();
            $('#stage_status2_line').parent().hide();
            k = k - 2;
            $('.result-div .result-div-l').hide();
        } else {
            $('.result-div .result-div-l').show();
            var img_url = data.item_open_url != '' ? data.item_open_url : '/img/platform/open_job.png';
            $('#item_open_url').attr('src', img_url);
            var statusArr = {
                0:'等待',
                1:' <img src="/img/platform/industry/success.svg">通过',
                2:'跳过',
                3:' <img src="/img/platform/industry/fail.svg">异常'
            };
            $('#item_open_flag').html(statusArr[data.item_open_flag]);
            $('#item_network_flag').html(statusArr[data.item_network_flag]);
            $('#item_heartbeat_flag').html(statusArr[data.item_heartbeat_flag]);
        }
        if (!data.document_flag) {
            // 未设置文件比对验证
            $('#stage_status3').parent().hide();
            $('#stage_status3_line').parent().hide();
            k = k - 2;
            $('.result-div .result-div-m').hide();
        } else {
            $('.result-div .result-div-m').show();
            $('#file_total_num').html(data.file_total_num);
            $('#file_total_compare_num').html(data.file_total_compare_num);
            $('#file_total_normal_num').html(data.file_total_normal_num);
            $('#file_total_error_num').html(data.file_total_error_num);
        }
        if (!data.screen_flag) {
            // 未设置截屏比对验证
            $('#stage_status4').parent().hide();
            $('#stage_status4_line').parent().hide();
            k = k - 2;
            $('.result-div .result-div-r').hide();
            $('.main-job-right').hide();
            $('.main-job-content').css('width', '80%');
        } else {
            $('.main-job-right').show();
            $('.main-job-content').css('width', '52%');
            $('.result-div .result-div-r').show();
            $('#screen_total_num').html(data.screen_total_num);
            $('#screen_total_compare_num').html(data.screen_total_compare_num);
            $('#screen_total_normal_num').html(data.screen_total_normal_num);
            $('#screen_total_error_num').html(data.screen_total_error_num);
        }
        var percet = (100 / k) + '%';
        $('.content-verify-stege .stege-conetnt .stege-item').css('width', percet);

        $('#stage_status1').removeClass('stage-icon-active').removeClass('stage-icon-active-in');
        $('#stage_status1_line').removeClass('stage-lines');

        if (data.item_status == 2) {
            // 验证中
            $('#stage_status1').addClass('stage-icon-active-in');
        } else if (data.item_status != 1) {
            // 完成
            $('#stage_status1').addClass('stage-icon-active');
            $('#stage_status1_line').addClass('stage-lines');
        }

        if (data.item_status == 2 && !($.inArray(data.open_result, [1, 2, 3]) !== -1)) {
            // 验证中
            if (interval1 === undefined) {
                interval1 = 1;
                window.angle = 0;
                $('#stage_status1').find('img').show();
                operateLoading('#stage_status1', timerTask.VerifyJobDetails_taskDetails1)
            }
        } else {
            $('#stage_status1').find('img').hide();
            clearIntervals(1);
        }

        $('#stage_status2').removeClass('stage-icon-active').removeClass('stage-icon-active-in').removeClass('stage-icon-active-error');
        $('#stage_status2_line').removeClass('stage-lines').removeClass('stage-lines-error');

        if (data.open_result == 2) {
            // 验证中
            $('#stage_status2').addClass('stage-icon-active-in');

            $('#stage_status1').removeClass('stage-icon-active').removeClass('stage-icon-active-in').addClass('stage-icon-active');
            $('#stage_status1_line').addClass('stage-lines');
        } else if (data.open_result == 1) {
            // 完成
            $('#stage_status2').addClass('stage-icon-active');
            $('#stage_status2_line').addClass('stage-lines');

            $('#stage_status1').removeClass('stage-icon-active').removeClass('stage-icon-active-in').addClass('stage-icon-active');
            $('#stage_status1_line').addClass('stage-lines');
        } else if (data.open_result == 3) {
            // 异常
            $('#stage_status2').addClass('stage-icon-active-error');
            $('#stage_status2_line').addClass('stage-lines-error');

            $('#stage_status1').removeClass('stage-icon-active').removeClass('stage-icon-active-in').addClass('stage-icon-active');
            $('#stage_status1_line').addClass('stage-lines');
        }

        if (data.open_result == 2) {
            // 验证中
            if (interval2 === undefined) {
                interval2 = 1;
                window.angle = 0;
                $('#stage_status2').find('img').show();
                operateLoading('#stage_status2', timerTask.VerifyJobDetails_taskDetails2)
            }
        } else {
            $('#stage_status2').find('img').hide();
            clearIntervals(2);
        }

        $('#stage_status3').removeClass('stage-icon-active').removeClass('stage-icon-active-in').removeClass('stage-icon-active-error');
        $('#stage_status3_line').removeClass('stage-lines').removeClass('stage-lines-error');

        if (data.document_result == 2) {
            // 验证中
            $('#stage_status3').addClass('stage-icon-active-in');
        } else if (data.document_result == 1) {
            // 完成
            $('#stage_status3').addClass('stage-icon-active');
            $('#stage_status3_line').addClass('stage-lines');
        } else if (data.document_result == 3) {
            // 异常
            $('#stage_status3').addClass('stage-icon-active-error');
            $('#stage_status3_line').addClass('stage-lines-error');
        }

        if (data.document_result == 2) {
            // 验证中
            if ( interval3 == undefined) {
                interval3 = 1;
                window.angle = 0;
                $('#stage_status3').find('img').show();
                operateLoading('#stage_status3', timerTask.VerifyJobDetails_taskDetails3)
            }
        } else {
            $('#stage_status3').find('img').hide();
            clearIntervals(3);
        }

        $('#stage_status4').removeClass('stage-icon-active').removeClass('stage-icon-active-in').removeClass('stage-icon-active-error');
        $('#stage_status4_line').removeClass('stage-lines').removeClass('stage-lines-error');

        if (data.screen_result == 2) {
            // 验证中
            $('#stage_status4').addClass('stage-icon-active-in');
        } else if (data.screen_result == 1) {
            // 完成
            $('#stage_status4').addClass('stage-icon-active');
            $('#stage_status4_line').addClass('stage-lines');
        } else if (data.screen_result == 3) {
            // 异常
            $('#stage_status4').addClass('stage-icon-active-error');
            $('#stage_status4_line').addClass('stage-lines-error');
        }

        if (data.screen_result == 2) {
            // 验证中
            if (interval4 == undefined) {
                interval4 = 1;
                window.angle = 0;
                $('#stage_status4').find('img').show();
                operateLoading('#stage_status4', timerTask.VerifyJobDetails_taskDetails4)
            }
        } else {
            $('#stage_status4').find('img').hide();
            clearIntervals(4);
        }

        $('#stage_status5').removeClass('stage-icon-active').removeClass('stage-icon-active-in').removeClass('stage-icon-active-error');
        if (data.result_result == 1) {
            // 完成
            $('#stage_status5').addClass('stage-icon-active');
        } else if (data.result_result == 3) {
            // 异常
            $('#stage_status5').addClass('stage-icon-active-error');
        }

        if (data.open_result != 0 && ((data.document_result != 0 && data.document_flag) || !data.document_flag)) {
            // 此时才可以进行报告的一系列操作
            isOperateIng = true;
        }

        if (report_status != 9) {
            // 此时不能对报告进行任何操作了
            $('.block-item2').hide();
            $('#save_plan').hide();
            $('.screen-item-pic-job .result input').attr('disabled',  true);
        } else {
            $('.block-item2').show();
            $('#save_plan').show();
            $('.screen-item-pic-job .result input').attr('disabled',  false);
        }
    }

    var operateLoading = function(dom, intval){
        window.angle += 1; // 每次增加 1 度
        var angel = window.angle;
        $(dom).find('img').css('transform', `rotate(${angel}deg)`);

        intval = setTimeout(function (){
            operateLoading(dom, intval);
        }, updateIntervals);
    }

    //
    var clearIntervals = function (type = 0){
        if (type != 0) {
            switch (type) {
                case 1:
                    if (timerTask.VerifyJobDetails_taskDetails1 !== undefined) {
                        clearTimeout(timerTask.VerifyJobDetails_taskDetails1);
                        delete timerTask.VerifyJobDetails_taskDetails1;
                        $('#stage_status1').find('img').css('transform', `rotate(0deg)`);
                    }
                    interval1 = undefined;
                    break;
                case 2:
                    if (timerTask.VerifyJobDetails_taskDetails2 !== undefined) {
                        clearTimeout(timerTask.VerifyJobDetails_taskDetails2);
                        timerTask.VerifyJobDetails_taskDetails2;
                        $('#stage_status2').find('img').css('transform', `rotate(0deg)`);
                    }
                    interval2 = undefined;
                    break;
                case 3:
                    if (timerTask.VerifyJobDetails_taskDetails3 !== undefined) {
                        clearTimeout(timerTask.VerifyJobDetails_taskDetails3);
                        timerTask.VerifyJobDetails_taskDetails3;
                        $('#stage_status3').find('img').css('transform', `rotate(0deg)`);
                    }
                    interval3 = undefined;
                    break;
                case 4:
                    if (timerTask.VerifyJobDetails_taskDetails4 !== undefined) {
                        clearTimeout(timerTask.VerifyJobDetails_taskDetails4);
                        timerTask.VerifyJobDetails_taskDetails4;
                        $('#stage_status4').find('img').css('transform', `rotate(0deg)`);
                    }
                    interval4 = undefined;
                    break;
            }
            return;
        }

        if (timerTask.VerifyJobDetails_taskDetails1 !== undefined) {
            clearTimeout(timerTask.VerifyJobDetails_taskDetails1);
            delete timerTask.VerifyJobDetails_taskDetails1;
        }

        $('#stage_status1').find('img').css('transform', `rotate(0deg)`);

        if (timerTask.VerifyJobDetails_taskDetails2 !== undefined) {
            clearTimeout(timerTask.VerifyJobDetails_taskDetails2);
            timerTask.VerifyJobDetails_taskDetails2;
        }

        $('#stage_status2').find('img').css('transform', `rotate(0deg)`);


        if (timerTask.VerifyJobDetails_taskDetails3 !== undefined) {
            clearTimeout(timerTask.VerifyJobDetails_taskDetails3);
            timerTask.VerifyJobDetails_taskDetails3;
        }

        $('#stage_status3').find('img').css('transform', `rotate(0deg)`);

        if (timerTask.VerifyJobDetails_taskDetails4 !== undefined) {
            clearTimeout(timerTask.VerifyJobDetails_taskDetails4);
            timerTask.VerifyJobDetails_taskDetails4;
        }

        $('#stage_status4').find('img').css('transform', `rotate(0deg)`);
        interval1 = undefined;
        interval2 = undefined;
        interval3 = undefined;
        interval4 = undefined;
    }

    // 初始化验证对象列表
    var initObjectGrid = function (){
        var data = {
            'sort': 'ssi.item_status',
            'order': 'asc',
            'search': $('#search_job').val()
        };
        pAjaxRequest(data, "/api/v1/industry/job/"+_jobUuid+"/object", "GET", function (d) {
            var html = '';
            if (d.code == 0) {
                objects = d.data.rows;
                if (objects.length == 0) {
                    html = '<div>'+LANG.UI_RECOVERY_NO_DATA+'</div>';
                    $('.client-body').html(html);
                    return;
                }
                var indexs = 0;
                for (var i in objects) {
                    var actives = '';
                    var labelClass = setStatusDes(objects[i].status);

                    if (chooseItem != '' && objects[i].object_uuid == chooseItem) {
                        // 触发右边区域的选中事件后的绑定显示信息
                        showClientItem(objects[i].object_uuid, false);
                        maxBootTime = objects[i].max_boot_time;
                        screenFlag = objects[i].screen_flag;
                        actives = 'list-active';
                    } else if (chooseItem == '' && indexs == 0) {
                        actives = 'list-active';
                        // 触发右边区域的选中事件后的绑定显示信息
                        showClientItem(objects[i].object_uuid);
                        maxBootTime = objects[i].max_boot_time;
                        screenFlag = objects[i].screen_flag;
                    }
                    indexs ++;
                    html += `<div class="body-check">
                        <label>
                          <input name="batchChoose[]" type="checkbox" data-status="`+objects[i].status+`" value="`+objects[i].object_uuid+`" class="input-check">
                        </label>
                        <div class="client_block_item searchs ` + actives + `" data-screen_flag="`+objects[i].screen_flag+`"  data-boot_time="`+objects[i].max_boot_time+`" data-uuid="`+objects[i].object_uuid+`">
                            <label class="client-title" title="`+objects[i].object_name+`">`+objects[i].object_name+`</label>
                            <label class="client-status `+ labelClass +`">`+objects[i].status_des+`</label>
                        </div>
                    </div>`;
                }
            } else {
                UIToastr.showError(LANG.UI_JOB_OVER_TITLE, LANG.UI_COMPONENT_NOT_EXISTS);
                setTimeout(function(){
                    LOCATION('./content/platform/jobs/verify_jobs.php','verification_job');
                }, 3000);
            }
            $('.client-body').html(html);
            if (chooseItems.length > 0 && $.inArray(_taskStatus, [1, 4, 8]) !== -1) {
                // 遍历 chooseItems 数组
                for (var jj = 0; jj < chooseItems.length; jj++) {
                    // 根据值查找对应的复选框并设置为选中状态
                    $('input[name="batchChoose[]"][value="' + chooseItems[jj] + '"]').prop('checked', true);
                }
            } else {
                $('#batchChoose').prop('checked', false);
            }

            // 绑定切换事件
            $('.client_block_item').off('click').on('click', function (){
                var uuid = $(this).attr('data-uuid');
                showClientItem(uuid);
                maxBootTime = $(this).attr('data-boot_time');
                screenFlag = $(this).attr('data-screen_flag');
            });
        }, true);
    }

    // 根据左边的设备id，控制右边的显示区域的变化
    var showClientItem = function (object_uuid, flag = true){
        chooseItem = object_uuid; // 记录此时选中的

        // 缓存选择器结果
        var $items = $('.client_block_item');
        // 移除所有项目的 'list-active' 类
        $items.removeClass('list-active');
        // 使用 filter() 方法找到匹配的项目并添加 'list-active' 类
        $items.filter(function() {
            return $(this).attr('data-uuid') === object_uuid;
        }).addClass('list-active');

        // 需要根据设备uuid去获取所有的信息
        var div = "#jobDetail";
        flag && Metronic.blockUI({target: div,animate: true});
        pAjaxRequest({}, "/api/v1/industry/job/"+_jobUuid+"/client/"+object_uuid, "GET", function (d) {
            flag && Metronic.unblockUI(div);
            if (d.code == 0) {
                var data = d.data;
                vnc_url = data.vnc_url;
                vm_uuid = data.vm_uuid;
                report_uuid = data.report_uuid;
                report_status = data.report_status;
                isReady = data.is_ready;
                if (flag) {
                    isInitUrl = false; // 切换需要更改验证机器标识
                    clearIntervals();
                    currentPage = 1;
                    // 重新初始化编辑器，因为是手动切换
                    if (itemResultEditorJob != null) {
                        itemResultEditorJob.destroy();
                    }
                    $('#item_result_editor_parent').html('<textarea id="item_result_job_editor" placeholder="请输入结论内容">'+data.item_result+'</textarea>');
                    if (itemPlanEditorJob != null) {
                        itemPlanEditorJob.destroy();
                    }
                    $('#item_plan_editor_parent').html('<textarea id="item_plan_job_editor" placeholder="请输入计划内容">'+data.item_plan+'</textarea>');
                    initEditor();

                    // 截屏列表
                    var screen_list = data.screen_list
                    var pa = [];
                    for (var ks in screen_list) {
                        pa.push({
                            'verify': {
                                'url': screen_list[ks].product,
                                'create_time': screen_list[ks].create_time
                            },
                            'product': {
                                'url':screen_list[ks].verify,
                                'create_time': screen_list[ks].create_time2 == undefined ? '' : screen_list[ks].create_time2
                            },
                            'result': parseInt(screen_list[ks].result),
                            'remark': screen_list[ks].remark
                        });
                    }
                    checkIsScreenEmpty = true; // 默认设置为空
                    var html = getScreenHtml(pa);
                    if (checkIsScreenEmpty) {
                        // 如果是空的，需要给一个默认的内容
                        html = getEmptyScreen();
                    }
                    $('.screen-item-pic-job').remove();
                    $('.block-item-bottom .screen-bottom').before(html);
                    addListenerScreen();
                    if (!checkIsScreenEmpty) {
                        calcDel();
                    }
                }
                if (data.screen_list.length == 0 && _taskStatus != CONF.TASK_STATUS.RUNNING) {
                    checkIsScreenEmpty = true; // 默认设置为空
                    var html = getEmptyScreen();
                    $('.screen-item-pic-job').remove();
                    $('.block-item-bottom .screen-bottom').before(html);
                    addListenerScreen();
                }
                $('#item_host_name_job').html(data.item_host_name);
                $('#item_agent_name_job').html(data.item_agent_name);
                $('#user_name_job').html(data.user_name);
                $('#item_agent_uuid_job').html(data.item_agent_uuid);
                $('#item_ip_job').html(data.item_ip);
                $('#item_timestamp_job').html(data.item_timestamp);
                $('#file_total_num').html(data.file_total_num);
                $('#file_total_compare_num').html(data.file_total_compare_num);
                $('#file_total_normal_num').html(data.file_total_normal_num);
                $('#file_total_error_num').html(data.file_total_error_num);
                $('#screen_total_num').html(data.screen_total_num);
                $('#screen_total_compare_num').html(data.screen_total_compare_num);
                $('#screen_total_normal_num').html(data.screen_total_normal_num);
                $('#screen_total_error_num').html(data.screen_total_error_num);
                $('#verify_time').html(data.verify_time);
                $('#start_time').html(data.start_time);
                $('#end_time').html(data.end_time);
                $('#interval_time').html(data.interval_time);
                // 启动停止按钮

                if ($.inArray(_taskStatus, [1, 4, 8]) !== -1) {
                    $('#item_operate_job').attr('data-flag', 1).show();
                    $('#item_operate_job i').removeClass('vicon-tingzhi').addClass('vicon-qidong');
                } else {
                    $('#item_operate_job').attr('data-flag', 2).hide();
                    $('#item_operate_job i').removeClass('vicon-qidong').addClass('vicon-tingzhi');
                }

                // 阶段和验证结果判断
                initStageAndResult(data);

                if (report_status != 9) {
                    $('#save_result').attr('disabled', true);
                } else {
                    $('#save_result').attr('disabled', false);
                }
            }
        })
    }

    // empty screen
    var getEmptyScreen = function () {
        return  `<div class="screen-block screen-item-pic-job">
                            <div class="show-screen-item-shadow-box display-none">
                                <label class="icon-del screen-time-job-del">
                                    <i class="viconfont vicon-zujianshanchu"></i>
                                </label>
                                <label class="icon-max">
                                    <i class="viconfont vicon-a-Zoom-infangda"></i>
                                </label>
                                <div class="shadow-box">
                                </div>
                            </div>
                            <div class="screen-item">
                                <div class="screen-time-job">
                                    <span>`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_COMPARSION+`</span>
                                </div>
                                <div class="products product_flag">
                                    <div class="div-img">
                                        <div class="demo"><i class="viconfont vicon-tupian"></i><div>`+LANG.UI_PLATFORM_GMP_JOB_PIC_TIPS+`</div></div>
                                    </div>
                                    <div class="titles">
                                        <label class="labels-l">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_PRODUCTS+`</label>
                                        <label class="labels-r">xxxx-xx-xx</label>
                                    </div>
                                </div>
                                <div class="products verify_flag">
                                    <div class="div-img">
                                         <div class="demo"><i class="viconfont vicon-tupian"></i><div>`+LANG.UI_PLATFORM_GMP_JOB_PIC_TIPS+`</div></div>
                                    </div>
                                    <div class="titles">
                                        <label class="labels-l">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_VERIFYS+`</label>
                                        <label class="labels-r">xxxx-xx-xx</label>
                                    </div>
                                </div>
                            </div>
                            <div class="screen-desc" >
                                <div class="result">`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT+`：
                                    <label><input type="radio" disabled name="eca4699477628b85e8ae98b8bb5b4a5d194c" value="1"><span>`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_SAME+`</span></label>
                                    <label><input type="radio" disabled name="eca4699477628b85e8ae98b8bb5b4a5d194c" value="2"><span>`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_RESULT_NOTSAME+`</span></label>
                                    <i class="viconfont vicon-baocun"></i>
                                </div>
                                <textarea cols="50" rows="2" placeholder="`+LANG.UI_PLATFORM_INDUSTRY_SCREENSHOT_TIPS+`"></textarea>
                            </div>
                        </div>`;
    }
    //初始化日志表格
    var initLogGrid = function(){
        var updateInterval = 5000;
        var getLiInfo = function(d){
            var d = d.data;
            var info = "";
            for(var i=0; i<d.length; i++){
                info +=
                    '<li class="list-group-item__log">' +
                    '<div class="col1">' +
                    '<div class="cont contdetail">' +
                    '<div class="cont-col1">' + getIcon(d[i].level_value) + '</div>' +
                    '<div class="cont-col2">' +
                    '<div class="desc">' + d[i].description + '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col2 logtimecol"><div class="date">' + d[i].op_time + '</div></div></li>';
            }
            if(0 == d.length){
                info = '<li><div class="col textalignc">' + LANG.UI_TOOLS_NO_DATA + '</div></li>';
            }
            $('#runninglog').html(info);
        }

        //得到任务ICON CSS
        var getIcon = function(level){
            if(1 == level){
                //文件
                icon = '<div class="label label-success" style="background-color: transparent"><i class="viconfont vicon-wancheng1"></i></div>';
            }else if(3 == level){
                icon = '<div class="label label-danger" style="background-color: transparent"><i class="viconfont vicon-cuowu"></i></div>';
            }else{
                icon = '<div class="label label-warning" style="background-color: transparent"><i class="viconfont vicon-yichang"></i></div>';
            }
            return icon;
        }

        var getlog = function(){
            var data = {};
            data.jobs_uuid = _jobUuid;
            pAjaxRequest(data, "/api/v1/logs/jobs/running/logs", "GET", function (d) {
                getLiInfo(d);
            }, true);
            timerTask.VMJobDetails_logGrid = setTimeout(getlog, updateInterval);
        }
        getlog();
    }

    //初始化历史任务表格
    var initHistoryGrid = function(){
        let initFlag = false;
        let columns = [
            {
                field: 'num',
                title: LANG.UI_PUBLIC_TABLE_ID,
                sortable: false,
                align: 'center'
            },
            {
                field: 'job_type',
                title: LANG.UI_PUBLIC_TASK_TYPE,
                sortable: false,
                formatter: function (value, row, index, field) {
                    return row.job_type;
                }
            },
            {
                field: 'job_status_value',
                title: LANG.UI_PUBLIC_STATUS,
                sortable: true,
                align: 'center',
                formatter: function (value, row, index, field) {
                    if (0 == row.job_status_value) {
                        return '<span class="label label-sm label-success status-icon">' + row.job_status + '</span>';
                    } else if (45 == row.job_status_value) {
                        return '<span class="label label-sm label-info status-icon">' + row.job_status + '</span>';
                    } else {
                        return '<span class="label label-sm label-danger status-icon">' + row.job_status + '</span>';
                    }
                }
            },
            /*{
                field: 'all_size',
                title: LANG.UI_MICROSOFT365_ALL_SIZE,
                sortable: true,
                align: 'center',
            },
            {
                field: 'write_size',
                title: LANG.UI_PUBLIC_REAL_SIZE,
                sortable: true,
                align: 'center',
            },*/
            {
                field: 'start_time',
                title: LANG.UI_PUBLIC_START_TIME,
                sortable: true,
                align: 'center',
            },
            {
                field: 'finish_time',
                title: LANG.UI_PUBLIC_END_TIME,
                sortable: true,
                align: 'center',
            }
        ];

        let options = {
            vin_url: '/api/v1/jobs/' + _jobUuid + '/history',
            vin_method: "GET",
            sortName: 'start_time',
            sortOrder: 'desc',
            tableArea: '#history',
            // changeHeightBtn: true, //改变高度按钮
            pagination: true, //分页
            pageList: [5, 10, 25, 50], //每页数量
            toolbarId: '#history_toolbar',
            buttonsToolbar: '#history_toolbar .vin_btnToolbar',
            onRefresh: function (params) {
                $("#historyTable").bootstrapTable('hideLoading');
            },
            columns: columns,
        }

        let init = function () {
            if (!initFlag) {
                $('#historyTable').baseTableConfig().init(options);
                initFlag = true;
            } else {
                $('#historyTable').bootstrapTable('refresh');
            }
        }

        //切换到历史任务tab时初始化
        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            e.target // newly activated tab
            e.relatedTarget // previous active tab
            if ("#history_div" == e.target.hash) {
                init();
            }
        });
    }

    // 页面改变高度
    function resizeTemp() {
        if (itemPlanEditorJob != null) {
            $('#item_plan_job_editor>div').css({
                'width': '100%'
            })
            $('#item_plan_job_editor>div>div').css({
                'width': '100%'
            })
        }
        if (itemResultEditorJob != null) {
            $('#item_result_job_editor>div').css({
                'width': '100%'
            })
            $('#item_result_job_editor>div>div').css({
                'width': '100%'
            })
        }
    }

    return {
        //main function to initiate the module
        init: function () {
            planHeight = window.innerHeight - 180;
            _jobUuid = $("#task_uuid").val();
            clearIntervals();
            addListener();
            buttonClickListener();
            initBasicInfo(); //  任务基本信息
            initLogGrid();
            initHistoryGrid();
        }
    };

}();

jQuery(document).ready(function () {
    VerificationJobDetail.init();
});