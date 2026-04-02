var Obsjobdetail = function () {
    const TIME_BACKUP_STRATEGY_TYPE = {
        STRATEGY: 1,
        ONCETIME: 2,
        MANUAL: 3
    }
    const AWS_VENDOR = 0; // AWS所属vendor
    const SUCCESS_TAG =
        '<div class="tag tag-success tag_status_en">' +
            '<span>' + LANG.UI_PUBLIC_SUCCESS +'</span>'
        '</div>';
    const ERROR_TAG =
        '<div class="tag tag-error tag_status_en">' +
            '<span>' + LANG.UI_DATACENTER_FAILURE + '</span>'
        '</div>';
    const ABORT_TAG =
        '<div class="tag tag-primary tag_status_en">' +
            '<span>' + LANG.UI_DATACENTER_ZHONGZHI + '</span>'
        '</div>';
    const ABNORMAL_TAG =
        '<div class="tag tag-unnormal tag_status_en">' +
            '<span>' + LANG.UI_NODE_ABNORMAL + '</span>'
        '</div>';
    const VERIFY_CYCLE_DESC_MAP = {
        0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_WEEK,
        1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_DAY,
        2: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_EVERY_TIME
    }; // 验证策略 - 校验周期描述
    const BACKUP_POINT_ABNORMAL_DESC_MAP = {
        0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE1,
        1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE2
    }; // 验证策略 - 备份点异常描述
    let TASK_UUID = ''; // task uuid
    let TASK_TYPE = 1; // task type
    let TASK_STATUS = 0; // task status
    let taskInfo = {}; // 任务信息
    let myChart;
    let initChartFlag = false; //任务曲线图初始化标志
    let fsnodeuuid = ''; //用于下载跳过文件
    let initObsListTableFlag = false; // 标记是否已初始化过对象存储列表
    let expandIndex = null;
    let searchVal = ''; // 对象存储列表搜索框输入值

    //<-----------------------------    BEGIN CHART INFO    ------------------------------>

    /**
     * 初始化流量信息
     */
    const initSpeed = () => {
        // 根据不同分辨率动态计算echart的高度和宽度
        let chartWidth = $('.portlet-charts__body__speedchart').width();
        let chartHeight = $('.portlet-charts__body__speedchart').height();
        $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

        // 基于准备好的dom，初始化echarts实例
        myChart = echarts.init(document.getElementById('speedchart'));
        let option = {
            tooltip : {
                trigger: 'axis',
                formatter: function (params, ticket, callback) {
                    let value = params[0].data;
                    if(value >= 1024){
                        return Math.round(value * 100 / 1024) / 100 + " MB/s";
                    }else{
                        return value + " KB/s";
                    }
                }
            },
            grid: {
                top: 15,
                left: 5,
                right: 5,
                bottom: 5,
                containLabel: true,
                show: false,
                borderWidth: 0,
            },
            xAxis:
                {
                    type: 'category',
                    boundaryGap: false,
                    splitNumber: 6,
                    splitLine: {
                        show: false
                    },
                    axisLabel: {
                        show: true,
                        interval: 12,
                        color: '#86909C'
                    },
                    axisLine: {
                        show: true,
                        lineStyle: {
                            color: '#C9CDD4',
                        }
                    },
                    data: []
                },
            yAxis :
                {
                    type : 'value',
                    splitLine: {
                        show: true,
                        lineStyle: {
                            type: 'dashed',
                        },
                    },
                    axisLine: {
                        show: false,
                    },
                    axisTick: {
                        show: false // Hide y-axis ticks
                    },
                    axisLabel : {
                        formatter: function(value, index){
                            //向上取整显示纵坐标
                            if(value >= 1024){
								return Math.ceil(value / 1024)+ "MB/s";
							}else{
                                if(value < 1){
                                    return value + "KB/s";
                                }else{
                                    return Math.ceil(value)+ "KB/s";
                                }
							}
                            
                        },
                        color: '#86909C',
                    },
                },
            series : [
                {
                    name:'net',
                    type:'line',
                    stack: 'total',
                    showSymbol: false,
                    hoverAnimation: false,
                    smoothMonotone: 'x',
                    animation: false,
                    smooth: true,
                    areaStyle: {normal: {
                            color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? '#2A87C8':new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                {
                                    offset: 0,
                                    color: 'rgba(59, 179, 70, 0.2)'
                                },
                                {
                                    offset: 1,
                                    color: 'rgba(59, 179, 70,0)'
                                }
                            ]),
                            opacity: CONF.VENDOR == CONF.VENDOR_LIST.gmp ? 0.2 : 1,
                        }},
                    data:[]
                },
            ],
            color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? ['#2A87C8']:['#44b6ae']
        };

        let updateInterval = 5000, data = [], nowTime = [];

        const parseNum = (num) => {
            num = parseInt(num);
            num = num >= 10 ? num : "0" + num;
            return num;
        }

        const getShowTime = (timeStamp) => {
            let myDate = new Date(parseInt(timeStamp));
            let date = myDate.toLocaleDateString();
            let hours = myDate.getHours();
            let minutes = myDate.getMinutes();
            let seconds = myDate.getSeconds();

            return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
        }

        //初始化任务进度曲线图
        const initTaskSpeed = function(d){

            if(initChartFlag) return; //初始化了就直接返回

            let serverTime = d.t * 1000;
            for (let i = 100; i > 0; i--) {
                data.push(0);
                nowTime.push(getShowTime(serverTime -  i * 3000));
            }
            option.series[0].data = data
            option.xAxis.data = nowTime;
            myChart.setOption(option);

            initChartFlag = true;
        }

        function update(){
            if(0 === $('#speedchart').size()){
                clearTimeout(timerTask.VMJobDetails_speed);
                return;
            }

            let params = {
                taskUUID: TASK_UUID
            }

            pAjaxRequest(params, '/api/v1/s3/jobs_speed', 'GET', (result) => {
                if (result.success) {
                    initTaskSpeed(result.data);

                    data.shift();
                    data.push(result.data.speed);
                    option.series[0].data = data;

                    nowTime.shift();
                    nowTime.push(result.data.nowTime);
                    option.xAxis.data = nowTime;

                    myChart.setOption(option);

                    timerTask.OBSJobDetails_speed = setTimeout(update, updateInterval);
                } else {
                    UIToastr.showWarning(LANG.UI_OBS_GET_TASK_TRAFIC_DATA_FAILED, result.message);
                }
            }, true);
        }
        update();

        window.onresize = function(){
            myChart.resize();
        }
    }

    //<-----------------------------    END CHART INFO    -------------------------------->


    //<-----------------------------    BEGIN BASIC INFO    ------------------------------>

    const getFlagLevelInfo = (flag) => {
        let html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';

        if (!flag){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }

        return html;
    }

    const setControlBtn = (id, available) =>{
        if (available){
            $("." + id).find('.btn').prop('disabled', false);
        } else {
            $("." + id).find('.btn').prop('disabled', true);
        }
    }

    /**
     * 设置下拉按钮状态
     * @param data
     */
    const setBtnStatus = (data) => {
        if (data.taskTypeFlag === 2) { // 恢复
            let button = "";
            button += '<li class="start"><button class="btn dropdown-menu__item" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
            button += '<li class="stop"><button class="btn dropdown-menu__item" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
            $('#fsOpList').html(button);
        }

        setControlBtn('start', true);
        setControlBtn('startFullTable', true);
        setControlBtn('startIncrTable', true);
        setControlBtn('startDiffTable', true);
        setControlBtn('startFull', true);
        setControlBtn('startIncr', true);
        setControlBtn('startDiff', true);
        setControlBtn('deletefs', true);

        switch (data.job_status) {
            case 5:
                //停止中
                setControlBtn('startFullTable', false);
                setControlBtn('startIncrTable', false);
                setControlBtn('startDiffTable', false);
                setControlBtn('start', false);
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('deletefs', false);
                $('.stop').html('<button class="btn dropdown-menu__item" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
                break;
            case 2:
            case 10:
            case 12:
                //运行、准备中停止中,禁用运行 12是启动中
                setControlBtn('startFullTable', false);
                setControlBtn('startIncrTable', false);
                setControlBtn('startDiffTable', false);
                setControlBtn('deletefs', false);
                setControlBtn('start', false);
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('stop', true);
                break;
            case 4:
                //停止,禁用停止
                $('.stop').html('<button class="btn dropdown-menu__item" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
                setControlBtn('start', true);
                setControlBtn('startIncr', true);
                setControlBtn('startDiff', true);
                setControlBtn('stop', false);
                break;
            case 19: // 挂起
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('startFullTable', false);
                setControlBtn('startIncrTable', false);
                setControlBtn('startDiffTable', false);
                setControlBtn('stop', true);
                break;
            default:
                //其他状态,开启控制
                setControlBtn('start', true);
                setControlBtn('startIncr', true);
                setControlBtn('startDiff', true);
                setControlBtn('stop', true);
                break;
        }

        if (data.timeStrategy && data.timeStrategy.length > 0) {
            let timeStrategy = data.timeStrategy;
            timeStrategy.forEach(item => {
                if (item.mode === 2 || item.mode === 9) { // 增量备份或永久增量禁用差备
                    setControlBtn('startDiff', false);
                    setControlBtn('startDiffTable', false);
                } else if (item.mode === 3) {
                    setControlBtn('startIncr', false);
                    setControlBtn('startIncrTable', false);
                }

                if (item.type === 4) { //如果为一次性备份 ,禁用增量和差异
                    setControlBtn('startIncrTable', false);
                    setControlBtn('startDiffTable', false);
                    setControlBtn('startIncr', false);
                    setControlBtn('startDiff', false);
                }
            })
        }
    }

    const startJobUnify = (type) => {
        let params = {'start_type': type};

        Metronic.blockUI({target: '#obs_job_detail', animate: true});
        pAjaxRequest(params, "/api/v1/jobs/start/" + TASK_UUID + "", 'POST', function (data) {
            Metronic.unblockUI('#obs_job_detail');
            let op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
            if (operateResponseList(data, op)) {
            }
        });
    }

    const stopJobUnify = (funName) => {
        if (TASK_TYPE === 2) { // 恢复任务停止前需校验密码
            bootbox.prompt({
                title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
                inputType: 'password',
                callback: debounce(function (r) {
                    if (r == null) return;
                    // 执行操作验证密码
                    Metronic.blockUI({target: '#obs_job_detail',animate: true,cenrerY: true});
                    let encrypt = new JSEncrypt();
                    encrypt.setPublicKey(CONF.PUBLIC_KEY);
                    let password = encrypt.encrypt(r);
                    let that = this; // 保留指向 bootbox 的this引用，用于在密码校验成功后关闭弹窗
                    pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
                        Metronic.unblockUI('#obs_job_detail');

                        if (result.code == 0) {
                            $(that).modal('hide');

                            // 执行停止操作
                            pAjaxRequest({}, "/api/v1/jobs/stop/" + TASK_UUID + "", "POST",function (res) {
                                Metronic.unblockUI('#obs_job_detail');
                                let op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                                if (operateResponseList(res, op)) {
                                }
                            });
                        } else {
                            UIToastr.showWarning(result.title, result.message);
                        }
                    });
                }, 300, false)
            })
        } else {
            Metronic.blockUI({target: '#obs_job_detail', animate: true});
            pAjaxRequest({}, "/api/v1/jobs/stop/" + TASK_UUID + "", "POST",function (res) {
                Metronic.unblockUI('#obs_job_detail');
                let op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                if (operateResponseList(res, op)) {
                }
            });
        }
    }

    /**
     * 初始化操作按钮
     * @param data
     */
    const initOpButton = (data) => {
        setBtnStatus(data);

        $('.start').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }

            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: taskInfo.user_uuid, auth: 'current_job' }, () => {
                startJobUnify(1);
            });
        });
        $('.startFull').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }

            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: taskInfo.user_uuid, auth: 'current_job' }, () => {
                startJobUnify(1);
            });
        });
        $('.startIncr').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }

            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: taskInfo.user_uuid, auth: 'current_job' }, () => {
                startJobUnify(2);
            });
        });
        $('.startDiff').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }

            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: taskInfo.user_uuid, auth: 'current_job' }, () => {
                startJobUnify(3);
            });
        });

        $('.stop').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }

            // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
            checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: taskInfo.user_uuid, auth: 'current_job' }, () => {
                stopJobUnify();
            });
        });
    }

    /**
     * 获取任务状态tag class
     * @param level
     * @returns {string}
     */
    const getStatusLevelClass = (level) =>{
        let levelClass = '';

        switch (level){
            case CONF.TASK_STATUS.WAITTING:
            case CONF.TASK_STATUS.STOPPING:
            case CONF.TASK_STATUS.PREPARING:
                levelClass = "label-info";
                break;
            case CONF.TASK_STATUS.RUNNING:
            case CONF.TASK_STATUS.PAUSED:
            case CONF.TASK_STATUS.SUCCESSED:
                levelClass = "label-success";
                break;
            case CONF.TASK_STATUS.STOPPED:
                levelClass = "label-default";
                break;
            case CONF.TASK_STATUS.ABNORMAL:
                levelClass = "label-warning";
                break;
            case CONF.TASK_STATUS.NETWORK_FAULT:
            case CONF.TASK_STATUS.ERROR:
                levelClass = "label-danger";
                break;
            default:
                levelClass = "label-info";
                break;
        }

        return levelClass;
    }

    const getScanSpeed = (level) => {
        let des = "";
        switch(level) {
            case 0:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_FIVE;
                break;
            case 1000:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_FOUR;
                break;
            case 800:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_THREE;
                break;
            case 600:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_TWO;
                break;
            case 400:
                des = LANG.UI_FILE_BAK_SCAN_SPEED_ONE;
                break;
        }

        return des;
    }

    const getSameNameStrategy = (flag) => {
        let des = '';
        switch (parseInt(flag)) {
            case 1:
                des = LANG.UI_FILE_PROCESS_SAME_FILE_COVER;
                break;
            case 2:
                des = LANG.UI_FILE_PROCESS_SAME_FILE_KEEP_LATEST;
                break;
            case 3:
                des = LANG.UI_FILE_PROCESS_SAME_FILE_ADD;
                break;
            case 4:
                des = LANG.UI_FILE_PROCESS_SAME_FILE_RENAME;
                break;
            case 5:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_REPLACE;
				break;
        }
        return des;
    }

    const getEachStrategy = (strategy) => {
        let desEach = '';
        desEach += strategy.start_time;
        //如果是英文版 需要加空格
        if(CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw"){
            desEach += " "; //策略开始时间
        }
        desEach += LANG.UI_STRATEGY_START + ", ";
        if(strategy.roll_flag){
            desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.end_time;
        }else{
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }
        desEach += "<br>";

        return desEach;
    }

    const getStrategyFrequency = (strategy) => {
        let frequency = "";
        let frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
        for(let i= 1; i <= 52; i++){
            if(strategy.frequency == "s" +i){
                if(i == 1){
                    frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
                } else {
                    frequency = frequencyLang.replace('x', i) + ",";
                }
            }
        }
        return frequency;
    }

    const getStrategyDays = (days) => {
        let desDays = '';

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

    const getStrategyWeek = (days) => {
        let desDays = '';
        $.each(days, function(i,d){
            if(1 == d){
                desDays += CONF.WEEK[i] + ", ";
            }
        });
        return desDays;
    }

    /**
     * 获取时间策略信息
     * @param timeStrategy
     * @returns {{diff: (string|*), full: (string|*), inc: (string|*)}}
     */
    const getTimeStrategy = (timeStrategy) => {
        let timeInfo = {
            full: LANG.UI_PUBLIC_NOTHING,
            inc: LANG.UI_PUBLIC_NOTHING,
            diff: LANG.UI_PUBLIC_NOTHING,
            permanentIncr: LANG.UI_PUBLIC_NOTHING
        };

        timeStrategy.forEach(item => {
            let des = '';

            switch (item.type) {
                case CONF.STRATEGY_TYPE.DAY:
                    des += LANG.UI_STRATEGY_DAY + getEachStrategy(item);
                    break;
                case CONF.STRATEGY_TYPE.WEEK:
                    des += getStrategyFrequency(item);

                    if (CONF.LANGUAGE === "zh-cn" || CONF.LANGUAGE === "zh-tw"){
                        des += LANG.UI_STRATEGY_WEEK + getStrategyDays(item.days) + getEachStrategy(item);
                    } else {
                        des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(item.days) + getEachStrategy(item);
                    }
                    break;
                case CONF.STRATEGY_TYPE.MONTH:
                    des += LANG.UI_STRATEGY_MONTH + getStrategyDays(item.days) + getEachStrategy(item);
                    break;
                case CONF.STRATEGY_TYPE.GLOBAL:
                    des += item.start_time;
                    break;
                default:
                    des += LANG.UI_PUBLIC_NOTHING;
                    break;
            }

            if (1 === item.mode) {
				if(item.full_backup_compensation_flag){
					des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_ON;
				} else {
					des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_OFF;
				}
                timeInfo.full = des;
            } else if (2 === item.mode) {
                timeInfo.inc = des;
            } else if (3 === item.mode) {
                timeInfo.diff = des;
            } else if (9 === item.mode) {
                timeInfo.permanentIncr = des;
            }
        });

        return timeInfo;
    }

    const getReservedStrategy = function(msg){
        let reservedStr = '';

        if(!msg){
            reservedStr = LANG.UI_PUBLIC_NOTHING;
            return reservedStr;
        }

        if (msg.strategy_mode === CONF.RESERVE_STRATEGY_MODE.POINT) { // 按备份点保留
            reservedStr += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
        } else { // 按备份链保留
            reservedStr += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
        }

        reservedStr += LANG.UI_RESERVE_RETENTION_MODE + '：';
        if(CONF.RESERVE_TYPE.NUM === msg.type){
            reservedStr += LANG.UI_STRATEGY_RESERVE_NUM;
            reservedStr += "<br>" + LANG.UI_STRATEGY_VALUE + '：' + msg.value;
        }else if(CONF.RESERVE_TYPE.DAY === msg.type){
            reservedStr += LANG.UI_STRATEGY_RESERVE_DAY;
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                reservedStr += "<br>"+LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY+": " + msg.value;
            }else{
                reservedStr += "<br>"+LANG.UI_STRATEGY_VALUE+": " + msg.value;
            }
        }else if(CONF.RESERVE_TYPE.PERMANENT === msg.type){
            reservedStr += LANG.UI_FILE_PERMANENT;
        }

        return reservedStr;
    }

    /**
     * 设置基本信息
     * @param data
     * @param timer
     */
    const setBasicInfo = (data, timer) => {
        taskInfo = { ...data };

        if (taskInfo.job_status !== 2) { // 非运行中的任务
            $('#total-progress').css({width: '0%'});
            $('#progressright').html('');
            taskInfo.total_size = '----';
            taskInfo.complate_size = '----';
        }

        if (!taskInfo.flag) {
            clearTimeout(timer);

            $('#total-progress').css({width: '100%'});
            $('#progressright').html('100%');

            UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);

            // 任务完成，5秒后跳转到当前任务页面
            setTimeout(function(){
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }, 5000);

            return;
        }

        // TASK FLOW RATE
        $('#total-progress').css({width: taskInfo.progress});
        $('#progressright').html(taskInfo.progress);

        // TAB PANEL ONE 概要信息
        $('#taskName').html(taskInfo.job_name); // 任务名
        $('#taskType').html(taskInfo.sub_module_type_des + taskInfo.job_type_des); // 任务类型
        $('#status').html('<span class="label ' + getStatusLevelClass(taskInfo.job_status) + '" >' + taskInfo.job_status_des + '</span>'); // 任务状态
        $('#task_stage').html(taskInfo.current_stage_value);
        $('#totalSize').html(taskInfo.total_size); // 任务已获取容量
        $('#currentSize').html(taskInfo.complate_size); // 已处理容量
        $('#startTime').html(taskInfo.startTime); // 开始时间
        $('#intervalTime').html(taskInfo.intervalTime); // 持续时间
        initOpButton(taskInfo); //初始化操作按钮


        if (taskInfo.taskTypeFlag === 1) { // 备份任务
            $('#createTime').html(taskInfo.createTime); // 创建/修改时间
            $('#nextTime').html(taskInfo.nextTime); // 下次开始时间
            $('#table_toolbar_search').show();

            switch (taskInfo.timeStrategyBackupType) {
                case TIME_BACKUP_STRATEGY_TYPE.STRATEGY:
                    $('#timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);

                    $('.static-info-backup-full').show();
                    $('.static-info-backup-increment').show();
                    $('.static-info-backup-diff').show();
                    $('.static-info-backup-permanent').show();

                    if (taskInfo.timeStrategy.length > 0) { // 时间策略
                        let timeStrategy = getTimeStrategy(taskInfo.timeStrategy);
                        $('#fulldes').html(timeStrategy.full);
                        $('#incdes').html(timeStrategy.inc);
                        $('#diffdes').html(timeStrategy.diff);
                        $('#permanent_incr').html(timeStrategy.permanentIncr);
                    }
                    break;
                case TIME_BACKUP_STRATEGY_TYPE.ONCETIME:
                    $('#timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);

                    $('.static-info-backup-full').hide();
                    $('.static-info-backup-increment').hide();
                    $('.static-info-backup-diff').hide();
                    $('.static-info-backup-permanent').hide();
                    break;
                case TIME_BACKUP_STRATEGY_TYPE.MANUAL:
                    $('#timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);

                    $('.static-info-backup-full').hide();
                    $('.static-info-backup-increment').hide();
                    $('.static-info-backup-diff').hide();
                    $('.static-info-backup-permanent').hide();
                    break;
                default:
                    break;
            }
            // 在编排中的任务，显示按编排策略执行（原时间策略不生效）
            if (data.task_orchestration_plan_flag) {
                $('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
            }

            // 源列表显示
            let des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_PLATFORM_DES_OBS_list;
            $('#srcList').html(des);
            $('.fsStartDiv').show(); // 对象存储列表显示

            $('.config-detail-drawer-recover').hide();
            $('.is-backup-static-info').show();
            $('.config-detail-drawer-backup').show();

            // <------  BEGIN SPEED LIMIT STRATEGY  ------>

            $('#speed_limit_backup').html(taskInfo.speedLimit.value);
            let taskPriority = taskInfo.speedLimit.task_priority;
            if (taskPriority) {
                let taskPriorityDes = '';
                switch (taskInfo.speedLimit.task_priority) {
                    case 1:
                        taskPriorityDes = LANG.UI_JOB_TASK_PRIORITY_PRIMARY;
                        break;
                    case 2:
                        taskPriorityDes = LANG.UI_JOB_TASK_PRIORITY_HIGH;
                        break;
                    case 3:
                        taskPriorityDes = LANG.UI_JOB_TASK_PRIORITY_HIGHEST;
                        break;
                }
                $('#task_priority_backup').html(taskPriorityDes); // 任务等级
            } else {
                $('.task-priority-form-item-backup').hide();
            }

            // <------  END SPEED LIMIT STRATEGY  ------>

            // <------  BEGIN STORAGE STRATEGY  ------>

            if (taskInfo.storageInfo.flag) {
                let storage = taskInfo.storageInfo.storage;
                let storageInfo = '';

                if (!storage) {
                    // 没有存储信息,自动选择存储
                    storageInfo = LANG.UI_JOB_AUTO_SELECT_STORAGE;
                } else {
                    if (taskInfo.storageInfo.storage_pool_uuid) {
                        storageInfo += taskInfo.storageInfo.storage_pool_nickname + '<br>';
                    }

                    storageInfo += storage.name + "(" + storage.type + ")<br>";
                    if (!storage.quota_flag) {
                        storageInfo += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
                            LANG.UI_JOB_FREE_SIZE + ":" + storage.free_size;
                    } else {
                        storageInfo += storage.quota_des;
                    }
                }

                let nodeDes = '';
                if (taskInfo.storageInfo.node_pool_uuid) { // 选了节点资源池
                    nodeDes += taskInfo.storageInfo.node_pool_nickname + '<br>';
                }

                if (taskInfo.storageInfo.node_uuid) {
                    nodeDes += taskInfo.storageInfo.node.name + '<br>';
                }

                $('#backup_node').html(nodeDes ? nodeDes : '--'); // 备份节点
                $('#storage_device').html(storageInfo); // 存储设备
            }

            $('#compress_storage').html(getFlagLevelInfo(taskInfo.storageInfo.high.compressed)); // 压缩存储
            if (taskInfo.storageInfo.high.compressed) {
                let method = '';

                switch (taskInfo.storageInfo.high.compress_method) {
                    case 1:
                        method = LANG.UI_JOB_COMPRESS_PRIORITY_FASTER;
                        break;
                    case 2:
                        method = LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL;
                        break;
                    case 3:
                        method = LANG.UI_JOB_COMPRESS_PRIORITY_BETTER;
                        break;
                    case 4:
                        method = LANG.UI_JOB_COMPRESS_PRIORITY_BEST;
                        break;
                }

                $('#compress_method').html(method); // 压缩等级
            } else {
                $('.compress-method-form-item').hide();
            }

            // 如果数据加密关闭隐藏自动生成密码开关描述显示
            if (!taskInfo.storageInfo.high.encrypt_flag) {
                $('.auto-password-form-item').hide();
            } else {
                $('.auto-password-form-item').show();
            }

            $('#encrypt_storage').html(getFlagLevelInfo(taskInfo.storageInfo.high.encrypt_flag)); // 数据加密

            if (taskInfo.storageInfo.high.encrypt_flag) {
                let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;

                if (taskInfo.storageInfo.high.encrypt_method === 2){
                    method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                }

                $('.storage-encrypt-method-form-item').show();
                $('#encrypt_storage_method').html(method); // 存储加密算法
            } else {
                $('.storage-encrypt-method-form-item').hide();
            }

            $('#storage_auto_password').html(getFlagLevelInfo(taskInfo.storageInfo.high.password_auto_flag)); // 自动生成密码

            // <------  END STORAGE STRATEGY  ------>

            // <------  BEGIN RESERVE STRATEGY  ------>

            if (taskInfo.storageInfo.storage && taskInfo.storageInfo.storage.type === LANG.UI_STORAGE_TYPE_TAPE) {
                $('.reserve-strategy-form-item .strategy-group__form__item__label').html(LANG.UI_TAPE_GROUP_STRATEGY);
                $('#reserve_strategy').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${taskInfo.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${taskInfo.tape_strategy.reserve_strategy_des}`);
            } else {
                $('.reserve-strategy-form-item .strategy-group__form__item__label').html(LANG.UI_STRATEGY_RESERVE);
                $('#reserve_strategy').html(getReservedStrategy(taskInfo.reserveStrategy)); // 保留策略
            }

            // <------  END RESERVE STRATEGY  ------>

            // <------  BEGIN ADVANCED STRATEGY  ------>

            $('#wildcardmode').html(getFlagLevelInfo(taskInfo.wildcardMode)); // 通配符备份方式

            if (taskInfo.storageInfo.storage && taskInfo.storageInfo.storage.type === LANG.UI_STORAGE_TYPE_TAPE) {
                $('.backup-transmit-thread-form-item').hide();
            } else {
                $('.backup-transmit-thread-form-item').show();
                $('#backup_transimit_thread_num').html(taskInfo.threadNum); // 传输线程数量
            }

            $('#backup_scan_thread_num').html(taskInfo.scanThreadNum); // 扫描线程数量
            if (taskInfo.scanThreadNum === 1) {
                $('.backup-scan-file-form-item').show();
                $('#backup_scan_file_speed').html(getScanSpeed(taskInfo.scan_file_num)); // 扫描文件速度
            } else {
                $('.backup-scan-file-form-item').hide();
            }

            // <------  END ADVANCED STRATEGY  ------>

            // <------  BEGIN TRANSMIT STRATEGY  ------>

            if (taskInfo.networkFlag) {
                $('.backup-transmit-network-form-item').show();
                $("#backup_transmit_network").html(taskInfo.transportStrategy.network); // 传输网络
            } else {
                $('.backup-transmit-network-form-item').hide();
            }

            $('#backup_appliance_agency_flag').html(getFlagLevelInfo(taskInfo.appliance_agency_flag));
            if (taskInfo.appliance_agency_flag) {
                $('.backup-appliance-agency-form-item').show();
                $('#backup_appliance_agency').html(taskInfo.appliance_agency); // 传输代理
            } else {
                $('.backup-appliance-agency-form-item').hide();
            }

            if (taskInfo.transportStrategy) {
                let flag = taskInfo.transportStrategy.encrypt_flag !== LANG.UI_PUBLIC_OFF_ONE;

                $('#backup_encrypt_transmit').html(getFlagLevelInfo(flag)); // 加密传输
            }

            if (taskInfo.transportStrategy.encrypt_flag_value) {
                $('.backup-transfer-encrypt-method-form-item').show();
                let encryptMethod = taskInfo.transportStrategy.encrypt_method;
                let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                if (encryptMethod === 2) {
                    method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                }
                $('#backup_transmit_encrypt_method').html(method); // 传输加密算法
            } else {
                $('.backup-transfer-encrypt-method-form-item').hide();
            }

            // <------  END TRANSMIT STRATEGY  ------>

            // <------  BEGIN SAFE STRATEGY  ------>
            // worm配置和完整性配置均为授权，直接隐藏 SAFE STRATEGY GROUP
            if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
                $('.config-detail-safe-strategy-wrapper').addClass('display-none');
            } else {
                $('.config-detail-safe-strategy-wrapper').removeClass('display-none');
            }

            // 选用的磁带存储隐藏安全策略
            if (taskInfo.storageInfo.storage.storage_type === CONF.BD_STORAGE_TYPE.TAPE) {
                $('.backup-safety-strategy-group').hide();
            } else {
                $('.backup-safety-strategy-group').show();

                // 未授权worm时隐藏worm配置选项及提示
                if (!CONF.FUNCTIONS.includes('worm')) {
                    $('.worm-config-wrapper').addClass('display-none');
                } else {
                    $('.worm-config-wrapper').removeClass('display-none');
                }

                // WORM防护
                let wormCheckFlag = taskInfo.safe_config_strategy.worm_flag === 1 ? true : false;
                $('#backup_worm_protect_check').html(getFlagLevelInfo(wormCheckFlag));
                if (wormCheckFlag) {
                    $('.backup-worm-protect-term-form-item').show();
                    $('#backup_worm_protect_term').html(`${taskInfo.safe_config_strategy.worm_protection_time}`);
                } else {
                    $('.backup-worm-protect-term-form-item').hide();
                }

                // 未授权完整性校验时隐藏完整性配置选项
                if (!CONF.FUNCTIONS.includes('integrity')) {
                    $('.integrity-check-wrapper').addClass('display-none');
                } else {
                    $('.integrity-check-wrapper').removeClass('display-none');
                }

                let integrityCheckFlag = taskInfo.safe_config_strategy.integrity_check_flag === 1 ? true : false;
                
                if (integrityCheckFlag) {
                    $('#backup_integrity_check').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_ABNORMAL}：${BACKUP_POINT_ABNORMAL_DESC_MAP[taskInfo.safe_config_strategy.integrity_check_config.full_error_policy]}`);
                } else {
                    $('.backup-verify-cycle-form-item').hide();
                    $('.backup-point-abnormal-form-item').hide();
                }
            }

            // <------  END SAFE STRATEGY  ------>

            // <------  BEGIN ABNOMAL HANDLE  ------>

            $('#pass_obj_alarm').html(getFlagLevelInfo(taskInfo.skip_file_alarm_flag)); // 跳过对象告警智能判断

            if (taskInfo.skip_file_alarm_flag) {
                $('.pass-obj-alarm-form-item').show();
                $('#pass_obj_alarm_num').html(taskInfo.skip_file_alarm_min_num); // 跳过对象告警个数
                $('#pass_obj_alarm_percent').html(taskInfo.skip_file_alarm_min_ratio); // 跳过对象告警比例
            } else {
                $('.pass-obj-alarm-form-item').hide();
            }

            // <------  END ABNOMAL HANDLE  ------>

            // <------  BEGIN RETRY STRATEGY  ------>

            $('#backup_network_retry_times').html(taskInfo.retry_strategy.network_retry_times); // 网络重连次数
            $('#backup_network_retry_interval').html(`${taskInfo.retry_strategy.network_retry_interval}${LANG.UI_PUBLIC_SECOND}`); // 网络重连间隔时间

            $('#backup_op_retry_flag').html(getFlagLevelInfo(taskInfo.retry_strategy.op_retry_flag)); // 操作异常重试
            if (taskInfo.retry_strategy.op_retry_flag) {
                $('.backup-op-retry-form-item').show();
                $('#backup_op_retry_times').html(taskInfo.retry_strategy.op_retry_times); // 操作重连次数
                $('#backup_op_retry_interval').html(`${taskInfo.retry_strategy.op_retry_interval}${LANG.UI_PUBLIC_SECOND}`); // 操作重连间隔时间
            } else {
                $('.backup-op-retry-form-item').hide();
            }

            $('#backup_task_retry_flag').html(getFlagLevelInfo(taskInfo.retry_strategy.task_retry_flag)); // 任务重试
            if (taskInfo.retry_strategy.task_retry_flag) {
                $('.backup-task-retry-form-item').show();
                $('#backup_task_retry_object').html(taskInfo.retry_strategy.task_retry_object === 1 ? LANG.UI_RETRY_FAILED_OBJS_IN_TASK : LANG.UI_RETRY_ALL_OBJS_IN_TASK); //任务重试对象
                $('#backup_task_retry_times').html(taskInfo.retry_strategy.task_retry_times); // 任务重试此时
                $('#backup_task_retry_interval').html(`${taskInfo.retry_strategy.task_retry_interval / 60}${LANG.UI_PUBLIC_MINUTE}`); // 任务重试间隔时间
            } else {
                $('.backup-task-retry-form-item').hide();
            }

            // <------  END RETRY STRATEGY  ------>

            $('#backup_ignore_resource_limit').html(getFlagLevelInfo(taskInfo.ignore_resource_limiting_flag));

            // <------  BEGIN OVERLOAD PROTECTION  ------>
        } else { // 恢复任务
            $('.fsStartDiv').hide();
            $('.startFull').hide();
            $('.startIncr').hide();
            $('.startDiff').hide();
            $('.is-backup-static-info').hide();
            $('.config-detail-drawer-backup').hide();
            $('#table_toolbar_search').hide();
            $('.config-detail-drawer-recover').show();

            // 判断源列表显示
            switch (parseInt(taskInfo.src_sub_module_type)) {
                case 1:   //fs
                    des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_COPY_DETAIL_FS_LIST;
                    break;
                case 2:   //nas
                    des = '<i class="viconfont vicon-nasmanager"></i>' + LANG.UI_COPY_DETAIL_NAS_LIST;
                    break;
                case 3:   //hadoop
                    des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_HADOOP_CLUSTER_LIST;
                    break;
                case 4:   //obs
                    des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_PLATFORM_DES_OBS_list;
                    break;
            }
            $('#srcList').html(des);

            // <------  BEGIN TIME STRATEGY  ------>

            if (taskInfo.timeStrategy.length === 0) { // 立即恢复
                $('#recover_type').html(`${LANG.UI_JOB_ONCE_TIME_RECOVER}`);
            } else {
                $('#recover_type').html(`${LANG.UI_JOB_SPECIFIED_TIME_RECOER}：${taskInfo.timeStrategy[0].start_time}`);
            }

            // <------  END TIME STRATEGY  ------>

            // <------  BEGIN SPEED LIMIT STRATEGY  ------>

            $('#speed_limit_recover').html(taskInfo.speedLimit.value);
            let taskPriority = taskInfo.speedLimit.task_priority;
            if (taskPriority) {
                let taskPriorityDes = '';
                switch (taskInfo.speedLimit.task_priority) {
                    case 1:
                        taskPriorityDes = LANG.UI_JOB_TASK_PRIORITY_PRIMARY;
                        break;
                    case 2:
                        taskPriorityDes = LANG.UI_JOB_TASK_PRIORITY_HIGH;
                        break;
                    case 3:
                        taskPriorityDes = LANG.UI_JOB_TASK_PRIORITY_HIGHEST;
                        break;
                }
                $('#task_priority_recover').html(taskPriorityDes); // 任务等级
            } else {
                $('.task-priority-form-item-recover').hide();
            }

            // <------  END SPEED LIMIT STRATEGY  ------>

            // <------  BEGIN ADVANCED STRATEGY  ------>

            if (taskInfo.storageType === CONF.BD_STORAGE_TYPE.TAPE) {
                $('.recover-transmit-thread-form-item').hide();
            } else {
                $('.recover-transmit-thread-form-item').show();
                $('#recover_transimit_thread_num').html(taskInfo.threadNum); // 传输线程数量
            }

            // <------  END ADVANCED STRATEGY  ------>

            // <------  BEGIN TRANSMIT STRATEGY  ------>

            if (taskInfo.networkFlag) {
                $('.recover-transmit-network-form-item').show();
                $("#recover_transmit_network").html(taskInfo.transportStrategy.network); // 传输网络
            } else {
                $('.recover-transmit-network-form-item').hide();
            }

            $('#recover_appliance_agency_flag').html(getFlagLevelInfo(taskInfo.appliance_agency_flag));
            if (taskInfo.appliance_agency_flag) {
                $('.recover-appliance-agency-form-item').show();
                $('#recover_appliance_agency').html(taskInfo.appliance_agency); // 传输代理
            } else {
                $('.recover-appliance-agency-form-item').hide();
            }

            if (taskInfo.transportStrategy) {
                let flag = taskInfo.transportStrategy.encrypt_flag !== LANG.UI_PUBLIC_OFF_ONE;

                $('#recover_encrypt_transmit').html(getFlagLevelInfo(flag)); // 加密传输
            }

            if (taskInfo.transportStrategy.encrypt_flag_value) {
                $('.recover-transfer-encrypt-method-form-item').show();
                let encryptMethod = taskInfo.transportStrategy.encrypt_method;
                let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                if (encryptMethod === 2) {
                    method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                }
                $('#recover_transmit_encrypt_method').html(method); // 传输加密算法
            } else {
                $('.recover-transfer-encrypt-method-form-item').hide();
            }

            // <------  END TRANSMIT STRATEGY  ------>

            // <------  BEGIN SAFETY STRATEGY  ------>

            if (taskInfo.storageType === CONF.BD_STORAGE_TYPE.TAPE) { // 磁带存储备份的时间点没有安全策略
                $('.recover-safety-strategy-group').hide();
            } else {
                // 未授权完整性校验隐藏其选项
                if (!CONF.FUNCTIONS.includes('integrity')) {
                    $('.recover-safety-strategy-group').hide();
                } else {
                    $('.recover-safety-strategy-group').show();

                    switch (taskInfo.safe_config_strategy.integrity_check_config.recovery_error_policy) {
                        case 0: // 中断恢复
                            $('#integrity_check_abnormal_handle').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY}`);
                            break;
                        case 1: // 继续恢复
                            $('#integrity_check_abnormal_handle').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY}`);
                            break;
                        case 2: // 恢复到无网络环境
                            $('#integrity_check_abnormal_handle').html(`${LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK}`);
                            break;
                        default:
                            break;
                    }
                }
            }

            // <------  END SAFETY STRATEGY  ------>

            // <------  BEGIN ADVANCED CONFIG  ------>

            if (taskInfo.newRootPath === LANG.UI_FILE_RECOVERY_ORIGINAL_PATH) {
                $('.remove-prefix-form-item').hide();
            } else {
                $('.remove-prefix-form-item').show();
                $('#remove_prefix').html(getFlagLevelInfo(taskInfo.dirTreeRecoveryFlag)); // 去除前缀
            }

            $('#same_obj_handle_type').html(getSameNameStrategy(taskInfo.sameFileStrategy)); // 同名对象处理

            // <------  BEGIN RETRY STRATEGY  ------>

            $('#recover_network_retry_times').html(taskInfo.retry_strategy.network_retry_times); // 网络重连次数
            $('#recover_network_retry_interval').html(`${taskInfo.retry_strategy.network_retry_interval}${LANG.UI_PUBLIC_SECOND}`); // 网络重连间隔时间

            $('#recover_op_retry_flag').html(getFlagLevelInfo(taskInfo.retry_strategy.op_retry_flag)); // 操作异常重试
            if (taskInfo.retry_strategy.op_retry_flag) {
                $('.recover-op-retry-form-item').show();
                $('#recover_op_retry_times').html(taskInfo.retry_strategy.op_retry_times); // 操作重连次数
                $('#recover_op_retry_interval').html(`${taskInfo.retry_strategy.op_retry_interval}${LANG.UI_PUBLIC_SECOND}`); // 操作重连间隔时间
            } else {
                $('.recover-op-retry-form-item').hide();
            }

            $('#recover_task_retry_flag').html(getFlagLevelInfo(taskInfo.retry_strategy.task_retry_flag)); // 任务重试
            if (taskInfo.retry_strategy.task_retry_flag) {
                $('.recover-task-retry-form-item').show();
                $('#recover_task_retry_object').html(taskInfo.retry_strategy.task_retry_object === 1 ? LANG.UI_RETRY_FAILED_OBJS_IN_TASK : LANG.UI_RETRY_ALL_OBJS_IN_TASK); //任务重试对象
                $('#recover_task_retry_times').html(taskInfo.retry_strategy.task_retry_times); // 任务重试此时
                $('#recover_task_retry_interval').html(`${taskInfo.retry_strategy.task_retry_interval / 60}${LANG.UI_PUBLIC_MINUTE}`); // 任务重试间隔时间
            } else {
                $('.recover-task-retry-form-item').hide();
            }

            // <------  END RETRY STRATEGY  ------>

            // <------  END RETRY STRATEGY  ------>

            $('#recover_ignore_resource_limit').html(getFlagLevelInfo(taskInfo.ignore_resource_limiting_flag));

            // <------  BEGIN OVERLOAD PROTECTION  ------>

            // <------  END ADVANCED CONFIG  ------>
        }
    }

    /**
     * 初始化基础信息
     */
    const initBasicInfo = () => {
        let updateInterval = 5000;

        /**
         * 使用闭包实现定时调用接口获取任务基本信息
         */
        const queryTaskBasicInfo = () => {
            if (0 === $('#task_uuid').size()){
                clearTimeout(timerTask.OBSJobDetails_basicInfo);
                return;
            }

            pAjaxRequest({task_uuid: TASK_UUID}, '/api/v1/s3/jobs_basic', 'GET', (result) => {
                if (result.success) {
                    setBasicInfo(result.data, timerTask.OBSJobDetails_basicInfo);

                    timerTask.OBSJobDetails_basicInfo = setTimeout(queryTaskBasicInfo, updateInterval);
                } else {
                    UIToastr.showWarning(LANG.UI_OBS_GET_BASIC_TASK_DATA_FAILED, result.message);
                }
            })
        }

        queryTaskBasicInfo();
    }

    /**
     * echart图自适应
     */
    const watchEchartSizeChange = () => {
        window.onresize = function() {
            var chartWidth = $('.portlet-charts__body__speedchart').width();
            var chartHeight = $('.portlet-charts__body__speedchart').height();
            $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            myChart.resize();
        }

        //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.portlet-charts__body__speedchart').width();
                let chartHeight = $('.portlet-charts__body__speedchart').height();

                $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                myChart.resize();
            }, 300);
        });
    }

    //<-----------------------------    END BASIC INFO    -------------------------------->


    //<-----------------------------    BEGIN RUNNING LOG    ----------------------------->

    /**
     * 初始化运行日志
     */
    const initLogGrid = () => {
        $('#runninglog').runningLog({
            job_uuid: $('#task_uuid').val()
        });
    }

    //<-----------------------------    END RUNNING LOG    ------------------------------->


    //<-----------------------------    BEGIN OBS LIST    -------------------------------->

    /**
     * 获取备份任务详情 - 通配符
     * @param row
     * @returns {{modes: string, type: string}}
     */
    const getWildcardModeCell = (row) => {
        let wildcardCell = {
            type: '',
            modes: ''
        }

        switch (parseInt(row.wildcardInfo.wildcard_mode)) {
            case 0:
                wildcardCell = {
                    type: LANG.UI_FILE_WILDCARD_RULES_NO_USE,
                    modes: LANG.UI_PUBLIC_NOTHING
                }
                break;
            case 1:
                wildcardCell = {
                    type: LANG.UI_FILE_WILDCARD_BAK_FILTER,
                    modes: row.wildcardInfo.wildcard.join('<br>')
                }
                break;
            case 2:
                wildcardCell = {
                    type: LANG.UI_FILE_WILDCARD_BAK_SELECT,
                    modes: row.wildcardInfo.wildcard.join('<br>')
                }
                break;
            default:
                break;
        }


        return wildcardCell;
    }

    const initFSGrid = () => {
        // 表格数据刷新修改
        let updateInterval = 5000;

        let obsListTableOptions = {
            pagination: true,
            pageList: [5, 10, 25, 50],
            vin_params: function () {
                return { jobs_uuid: TASK_UUID, task_type: TASK_TYPE, search: searchVal }
            },
            vin_url: "/api/v1/s3/jobs/detail",
            vin_method: "GET",
            detailView: true,
            detailFormatter: function (index, row) {
                // 匹配 | 和 / 之间的内容，并替换为空字符串，最后两个replace是针对由 <> 包裹起来的字串（如 <test>）会被渲染成HTML标签(<test></test>)的场景，此时要将 < 和 > 替换为HTML实体 &lt; 和 &gt;
                let pathNameCell = row.path_name.length > 0 ? row.path_name.map(i => { return `${replaceBetweenStartEnd(i, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}<br>`}) : ''; // 备份路径 | 恢复路径
                if (TASK_TYPE === 1) {
                    let wildcardCell = getWildcardModeCell(row);

                    let backupDetailView =
                        '<div class="overflow-x-hidden py-10">' +
                            '<div class="row detail-thead">' +
                                '<div class="col-md-4 detail-thead-cell" style="color: #666666">' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</div>' +
                                '<div class="col-md-4 detail-thead-cell ps-0" style="color: #666666">' + LANG.UI_FILE_WILDCARD + '</div>' +
                                '<div class="col-md-4 detail-thead-cell ps-0" style="color: #666666">' + LANG.UI_OBS_BACKUP_PATH + '</div>' +
                            '</div>' +
                            '<div class="row detail-tbody" style="margin: 10px 0 0 0">' +
                                '<div class="col-md-4 detail-tbody-cell ps-0">' + wildcardCell.type + '</div>' +
                                '<div class="col-md-4 detail-tbody-cell ps-0">' + wildcardCell.modes + '</div>' +
                                '<div class="col-md-4 detail-tbody-cell ps-0">' + '<div style="max-height:120px;overflow:auto;border: 1px solid #EBEBEB;padding:5px;word-break:break-all">' + pathNameCell.join('') + '</div>' + '</div>' +
                            '</div>'
                        '</div>';

                    return backupDetailView;
                } else {
                    let sourceClient = LANG.UI_FILE_DETAIL_SRC_NAME;
                    // switch (row.source_submodule_type) {
                    //     case 1: // 源端为文件客户端
                    //         sourceClient = LANG.UI_FILE_HISJOB_SOURCE_NAME_AND_IP;
                    //         break;
                    //     case 2: // 源端为nas设备
                    //         sourceClient = LANG.UI_OBS_SOURCE_DEVICE;
                    //         break;
                    //     case 3: // 源端为hadoop集群
                    //         sourceClient = LANG.UI_OBS_SOURCE_HADOOP_CLUSTER;
                    //         break;
                    //     case 4: // 源端为对象存储
                    //         sourceClient = LANG.UI_OBS_SOURCE_OBS;
                    //         break;
                    //     default:
                    //         break;
                    // }

                    let recoverDerailView = '';
                    if (row.cross_platform_flag) {
                        recoverDerailView =
                            '<div class="row detail-thead mt-10">' +
                                '<div class="col-md-2 detail-thead-cell" style="color: #666666">' + sourceClient + '</div>' +
                                '<div class="col-md-3 detail-thead-cell" style="color: #666666">' + LANG.UI_FILE_DETAIL_DES_NAME + '</div>' +
                                '<div class="col-md-2 detail-thead-cell" style="color: #666666">' + LANG.UI_FILE_CROSS_RESTORE + '</div>' +
                                '<div class="col-md-2 detail-thead-cell" style="color: #666666">' + LANG.UI_OBS_RECOVER_PATH + '</div>' +
                                '<div class="col-md-2 detail-thead-cell" style="color: #666666">' + LANG.UI_OBS_RECOVER_LIST + '</div>' +
                            '</div>' +
                            '<div class="row detail-tbody mt-10 me-10 mb-10">' +
                                '<div class="col-md-2 detail-thead-cell" style="word-break:break-all">' +  row.source_client + '</div>' +
                                '<div class="col-md-3 detail-thead-cell" style="word-break:break-all">' +  row.target_client + '</div>' +
                                '<div class="col-md-2 detail-thead-cell">' + row.cross_platform_des + '</div>' +
                                '<div class="col-md-2 detail-thead-cell" style="overflow-wrap:break-word">' + (!!row.new_root_path ? replaceBetweenStartEnd(row.new_root_path, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;') : LANG.UI_FILE_RECOVERY_ORIGINAL_PATH) + '</div>' +
                                '<div class="col-md-2 detail-thead-cell" style="max-height:120px;overflow:auto;border: 1px solid #EBEBEB;padding:5px;word-break:break-all">' + pathNameCell.join('') + '</div>' +
                            '</div>';
                    } else {
                        recoverDerailView =
                            '<div class="row detail-thead mt-10">' +
                                '<div class="col-md-3 detail-tbody-cell" style="color: #666666">' + sourceClient + '</div>' +
                                '<div class="col-md-3 detail-tbody-cell" style="color: #666666">' + LANG.UI_FILE_DETAIL_DES_NAME + '</div>' +
                                '<div class="col-md-3 detail-tbody-cell" style="color: #666666">' + LANG.UI_OBS_RECOVER_PATH + '</div>' +
                                '<div class="col-md-3 detail-tbody-cell" style="color: #666666">' + LANG.UI_OBS_RECOVER_LIST + '</div>' +
                            '</div>' +
                            '<div class="row detail-tbody mt-10 me-10 mb-10">' +
                                '<div class="col-md-3 detail-tbody-cell" style="word-break:break-all">' +  row.source_client + '</div>' +
                                '<div class="col-md-3 detail-tbody-cell" style="word-break:break-all">' + row.target_client + '</div>' +
                                '<div class="col-md-3 detail-tbody-cell" style="overflow-wrap:break-word">' + (!!row.new_root_path ? replaceBetweenStartEnd(row.new_root_path, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;') : LANG.UI_FILE_RECOVERY_ORIGINAL_PATH) + '</div>' +
                                '<div class="col-md-3 detail-tbody-cell" style="max-height:120px;overflow:auto;border: 1px solid #EBEBEB;padding:5px;word-break:break-all">' + pathNameCell.join('') + '</div>' +
                            '</div>';
                    }

                    return recoverDerailView;
                }
            },
            onRefresh: function (params) {
                $("#filestable").bootstrapTable('hideLoading');
				scroll = $("#filestable").bootstrapTable('getScrollPosition');
            },
            onPostBody: function() {
                if (null !== expandIndex) {
                    $("#filestable").bootstrapTable('expandRow', expandIndex);
                }
                $("#filestable").bootstrapTable('scrollTo', scroll);

                let tableData = $('#filestable').bootstrapTable('getData');

                if (tableData.length > 0) {
                    if (timerTask.OBSJobDetails_fsGrid) {
                        clearTimeout(timerTask.OBSJobDetails_fsGrid);
                    }
                    timerTask.OBSJobDetails_fsGrid = setTimeout(initFSGrid, updateInterval);

                    if (TASK_TYPE === 1) { // 备份任务
                        let arr = tableData.map(i => { return i.vendor });

                        if (arr.every(i => i === AWS_VENDOR)) { // 备份任务选的全是AWS的对象存储时，展示权限备份
                            $('.obj-permission-backup-form-item').show();
                            $('#obj_permission_backup').html(getFlagLevelInfo(taskInfo.permissionOperateFlag)); // 文件权限备份
                        } else {
                            $('.obj-permission-backup-form-item').hide();
                        }
                    } else { // 恢复任务
                        $('.obj-permission-recover-form-item').hide();
                    }
                }
            },
            onExpandRow: (index) => {
                if (null === expandIndex) {
                    expandIndex = index;
                } else if (index !== expandIndex) {
                    $("#filestable").bootstrapTable('collapseRow', expandIndex);
                    expandIndex = index;
                }
            },
			onCollapseRow: () => {
                expandIndex = null;
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false
                },
                {
                    field: 'source_client',
                    title: LANG.UI_FILE_DETAIL_SRC_NAME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'task_type',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'total_fs_count',
                    title: LANG.UI_HADOOP_TOTAL_NUM,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'current_fs_count',
                    title: LANG.UI_OBS_COMPLETED_OBJS,
                    sortable: true,
                    align: 'center',
                },
                // {
                //     field: 'current_dir_count',
                //     title: '目录个数',
                //     sortable: true,
                //     align: 'center',
                // },
                {
                    field: 'progress',
                    title: LANG.UI_PUBLIC_PROGRESS,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'agent_status',
                    title: LANG.UI_VISUAL_RESULT,
                    sortable: true,
                    align: 'center',
                }
            ]
        }

        if (!initObsListTableFlag) {
            $('#filestable').baseTableConfig().init(obsListTableOptions);
            initObsListTableFlag = true;
        } else {
            $('#filestable').bootstrapTable('refresh', { query: { search: searchVal }});
        }

    }

    const startFsJob = (mode) => {
        let selectedRows = $('#filestable').bootstrapTable('getSelections');

        if (selectedRows.length === 0) {
            return UIToastr.showInfo(LANG.UI_OBS_SELECT_OBJ_FOR_BACKUP, LANG.UI_OBS_SELECT_OBJ_FOR_BACKUP_TIP);
        }

        // 校验全局观察者操作权限，type为1表示校验非分配的权限，需要传数据本身所属user_uuid，以及对应权限标识 current_job
        checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: taskInfo.user_uuid, auth: 'current_job' }, () => {
            let params = {
                'task_uuid': TASK_UUID,
                'backup_mode': mode,
                'obs_uuids': selectedRows.map(i => { return i.obs_uuid})
            }

            Metronic.blockUI({target: '#obs_job_detail', animate: true});
            pAjaxRequest(params, '/api/v1/s3/jobs_start', 'post', (result) => {
                Metronic.unblockUI('#obs_job_detail');

                var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
                if (operateResponseList(data, op)) {
                }
            })
        });
    }

    /**
     * 对象存储列表操作按钮click监听
     */
    const initObsGridOpBtn = function () {
        $('.startFullTable').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startFsJob(1);
        });

        $('.startIncrTable').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startFsJob(2);
        });

        $('.startDiffTable').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startFsJob(3);
        });
    }

    //<-----------------------------    END OBS LIST    ---------------------------------->


    //<-----------------------------    BEGIN HISTORY TASK    ---------------------------->

    /**
     * 获取备份历史任务详情列表
     * @param {*} data
     * @returns
     */
    const getHistoryBackupDetailTable = (data, historyUUID) => {
        let html = '<table>';
        let thead = '';
        let tbody = '';

        if (data.list.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
            thead += `<tr>
                        <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                    </tr>`;

            let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                tbody +=
                `<tr>
                    <td>` + LANG.UI_PUBLIC_ON + `</td>
                    <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                    <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                </tr>`;
        } else {
            thead += '<tr>';
            thead += '<th>' + LANG.UI_OBS_SOURCE_OBS + '</th>';
            // thead += '<th>' + LANG.UI_OBS_PERMISSION_BACKUP + '</th>';
            thead += '<th>' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
            thead += '<th>' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th>';
            thead += '<th>' + LANG.UI_FILE_WILDCARD + '</th>';
            thead += '<th>' + LANG.UI_OBS_BACKUP_OBJ_TOTAL + '</th>';
            thead += '<th>' + LANG.UI_OBS_BACKUP_OBJ_LIST + '</th>';
            thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
            thead += '<th> ' + LANG.UI_OBS_BACKUP_SKIP_NUMS + ' </th>';
            // thead += '<th> ' + LANG.UI_FILE_COUNT_PASS_DIR+ ' </th>';
            thead += '<th> ' + LANG.UI_OBS_BACKUP_SKIP_LIST + ' </th></tr>';

            let wildcard, wildcard_mode = '';
            if (data.list.list.length > 0) {
                data.list.list.forEach(item => {
                    // 通配符
                    if (!item.wildcard_list) {
                        wildcard = LANG.UI_PUBLIC_NOTHING;
                        wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                    } else {
                        let wildcard_list = JSON.parse(item.wildcard_list);
                        let wildcardMode = parseInt(wildcard_list.wildcard_mode);

                        if(wildcardMode === 0) {
                            wildcard = LANG.UI_PUBLIC_NOTHING;
                            wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                        } else {
                            wildcard = wildcard_list.wildcard.join('<br>');
                            wildcardMode === 1 ?
                                wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER :
                                wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
                        }
                    }

                    tbody += '<tr>';
                    tbody += '<td>' + item.src_agent_name + '</td>'; // 源对象存储名
                    // tbody += '<td>' + data.list.permission_operate_flag + '</td>'; // 对象权限备份
                    tbody += '<td>' + item.backup_mode + '</td>'; // 任务类型
                    tbody += '<td>' + wildcard_mode + '</td>'; // 通配符有模式
                    tbody += '<td style="width: 8%;">' + wildcard + '</td>'; // 通配符
                    tbody += '<td>' + item.file_count + '</td>'; // 备份文件总数

                    // 备份文件列表
                    tbody += '<td style="padding-right: 30px"><div class="historyfilelisttext">';
                    let list = '';
                    if (!item.file_list) {
                        list += LANG.UI_PUBLIC_NOTHING;
                    } else {
                        item.file_list.map(item => {
                            return replaceBetweenStartEnd(item, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        }).forEach(i => {
                            list += i + "<br>";
                        })
                    }

                    tbody += list;
                    tbody += '</div></td>';

                    let passdir = typeof item.total_pass_dir_number === 'undefined' ? 0 : item.total_pass_dir_number;
                    tbody += '<td style="width: 8%;">' + item.description + '</td>'; // 描述
                    tbody += '<td>' + item.total_pass_number + '</td>'; // 跳过文件个数
                    // tbody += '<td>' + passdir + '</td>'; // 跳过目录个数

                    let elecontent = '',style = '';
                    if (parseInt(item.passfile_exist_flag) === 1) {
                        elecontent = LANG.UI_FILE_PASSFILE_DETAILS;

                        fsnodeuuid = item.node_uuid;
                        tbody += `<td><a value="${item.agent_uuid}" class="downloadPassFile_${item.agent_uuid}" ${style}>${elecontent}<span class="display-none">${item.passfile_file_path}</span></a></td>`;
                    } else {
                        elecontent = LANG.UI_PUBLIC_NOTHING;
                        style = 'style="color: #999999;pointer-events:none;"'
                        tbody += '<td>' + '<span '+ style +'>'+ elecontent +'</span>' + '</td>';
                    }

                    tbody += '</tr>';
                });
            }
        }

        return `${html}${thead}${tbody}</table>`;
    }

    /**
     * 获取恢复历史任务详情列表
     * @param {*} data
     * @returns
     */
    const getHistoryRecoverDetailTable = (data, historyUUID) => {
        let html = '<table>';
        let thead = '';
        let tbody = '';

        if (data.list.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
            thead += `<tr>
                        <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                    </tr>`;

            let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                tbody +=
                `<tr>
                    <td>` + LANG.UI_PUBLIC_ON + `</td>
                    <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                    <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                </tr>`;
        } else {
            thead += '<tr>';
            thead += '<th>' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>'; // 备份时间点
            thead += '<th>' + LANG.UI_OBS_SOURCE_OBS + '</th>'; // 源对象存储名
            thead += '<th>' + LANG.UI_OBS_TARGET_OBS + '</th>'; // 目的主机名
            thead += '<th>' + LANG.UI_OBS_RECOVER_OBJ_TOTAL + '</th>'; // 恢复文件总数
            thead += '<th>' + LANG.UI_JOB_HIS_DES_PATH + '</th>'; // 恢复路径
            thead += '<th>' + LANG.UI_OBS_RECOVER_OBJ_LIST + '</th>'; // 恢复文件列表
            thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>'; // 描述
            thead += '<th>' + LANG.UI_OBS_BACKUP_SKIP_NUMS + '</th>'; // 跳过文件个数
            // thead += '<th>' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>'; // 跳过目录个数
            thead += '<th>' + LANG.UI_OBS_BACKUP_SKIP_LIST + '</th></tr>'; // 跳过文件列表

            tbody += '<tr>';
            let recoveryWayDes = '';
            let pathWayDes = '';

            if (parseInt(data.list.recovery_way) !== 1) { // 恢复到源路径不显示目录树
                recoveryWayDes += LANG.UI_OBS_UNPREFIX + ': ' + data.list.dir_tree_recovery_flag + ';<br>';
                pathWayDes = LANG.UI_MICROSOFT365_RECOVERY_TYPE + ': ' + LANG.UI_FILE_HISJOB_RECOVERY_TO_NEW;
            } else {
                pathWayDes = LANG.UI_MICROSOFT365_RECOVERY_TYPE + ': ' + LANG.UI_FILE_HISJOB_RECOVERY_TO_OLD;
            }

            recoveryWayDes += LANG.UI_OBS_SAME_OBJ_PROCESS + ': ' + data.list.same_file_strategy + ';<br>';
            // recoveryWayDes += '无效快捷方式清理' + ': ' + data.list.link_file_pass_flag + ';<br>';
            // recoveryWayDes += '对象权限恢复' + ': ' + data.list.permission_operate_flag + ';<br>';
            recoveryWayDes += pathWayDes;

            tbody += '<td>' + data.list.timepoint + '(' + data.list.backup_mode + ')</td>'; // 备份时间点
            tbody += '<td>' + data.list.list[0].src_agent_name + '</td>'; // 源对象存储名
            tbody += '<td>' + data.list.des_agent_name + '</td>'; // 目的主机名
            tbody += '<td>' + data.list.file_count + '</td>'; // 恢复文件总数
            tbody += '<td style="width: 9%;">' + recoveryWayDes + '</td>'; // 恢复路径

            // 恢复文件列表
            tbody += '<td style="padding-right: 20px;"><div class="historyfilelisttext">';
            let list = '';

            if (data.list.file_list && data.list.file_list.length > 0) {
                data.list.file_list.map(item => {
                    return replaceBetweenStartEnd(item, '|', '/', '').replace('|', '').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                }).forEach(i => {
                    list += i + "<br>";
                });
            }

            tbody += list + '</div></td>';

            tbody += '<td style="width: 8%;">' + data.list.list[0].description + '</td>'; // 描述
            tbody += '<td>' + data.list.list[0].total_pass_number + '</td>'; // 跳过文件个数
            // tbody += '<td>' + data.list.list[0].total_pass_dir_number + '</td>'; // 跳过目录个数

            let elecontent = '',style = '';
            if (parseInt(data.list.list[0].passfile_exist_flag) === 1) {
                elecontent = LANG.UI_FILE_PASSFILE_DETAILS;

                fsnodeuuid = data.list.list[0].node_uuid;
                tbody += `<td><a class="downloadPassFile_${historyUUID}" ${style}>${elecontent}<span class="display-none">${data.list.list[0].passfile_file_path}</span></a></td>`;
            } else {
                elecontent = LANG.UI_PUBLIC_NOTHING;
                style = 'style="color: #999999;pointer-events:none;"';
                tbody += '<td>' + '<span '+ style +'>'+ elecontent +'</span>' + '</td>';
            }

            tbody += '</tr>';
        }

        return `${html}${thead}${tbody}</table>`;
    }

    const downloadPassFun = (history_uuid, agent_uuid) => {
        window.location.href = '/api/v1/s3/download_pass' + '?history_uuid=' + history_uuid + '&fsnodeuuid=' + fsnodeuuid + '&agent_uuid=' + agent_uuid + '&x-api-version=1.0-rev0';
    }

    const initHistoryGrid = () => {
        let initFlag = false;
        let lastIndex = [-1, -1];

        let historyTableOptions = {
            pagination: true,
            pageList: [5, 10, 25, 50],
            sortName: 'finish_time',
            sortOrder: 'desc',
            vin_params: function () {
                return { task_uuid: TASK_UUID, task_type: TASK_TYPE }
            },
            vin_url: '/api/v1/s3/jobs/history',
            vin_method: 'GET',
            detailView: true,
            detailFormatter: function (index, row, $detailView) {
                if (index !== lastIndex[1]) {//只展开一行
                    lastIndex.push(index);
                    $('#historytable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                    lastIndex.splice(0, 1);
                }

                Metronic.blockUI({ target: $detailView, animate: true });

                let params = {history_uuid: row.id};

                pAjaxRequest(params, '/api/v1/jobs/history', 'GET', (res) => {
                    if (res.success) {
                        let jobType = res.data.info.job_type;
                        let passFileList = res.data.list.list || [];
                        let historyUUID = res.data.list.history_uuid;

                        switch (jobType) {
                            case 1: // 备份
                               $($detailView).append(getHistoryBackupDetailTable(res.data, historyUUID));
                               break;
                            case 2: // 恢复
                                $($detailView).append(getHistoryRecoverDetailTable(res.data, historyUUID));
                                break;
                            default:
                                break;
                        }

                        if (passFileList && passFileList.length > 0) {
                            passFileList.forEach(item => {
                                if (parseInt(item.passfile_exist_flag) === 1) { // 有跳过文件详情
                                    if (jobType === 1) { // 备份任务
                                        $(`.downloadPassFile_${item.agent_uuid}`).off().click(function() {
                                            $('#passFileModal').modal({'width':"750px", 'height': "300px"});
                                            let agentUUID = $(this).attr('value');
                                            let passFileDetailData = [];
                                            let passFileReasonData = [];

                                            let total = parseInt(item.dir_count) + parseInt(item.all_scan_file_count);

                                            passFileDetailData.push({
                                                total_pass_number: item.total_pass_number,
                                                total_pass_ratio: total === 0 ? 0 : ((parseInt(item.total_pass_number) / total) * 100).toFixed(2) + '%',
                                                total_pass_dir_number: parseInt(item.total_pass_dir_number),
                                                total_pass_dir_ratio: total === 0 ? 0 : ((parseInt(item.total_pass_dir_number) / total) * 100).toFixed(2) + '%',
                                                passfile_number_limit: res.data.list.passfile_number_limit,
                                                passfile_ratio_limit: res.data.list.passfile_ratio_limit
                                            });

                                            passFileReasonData.push({
                                                passfile_file_number_occupy: item.passfile_file_number_occupy,
                                                passfile_file_number_delete: item.passfile_file_number_delete,
                                                passfile_file_number_reject: item.passfile_file_number_reject,
                                                passfile_dir_number_reject: item.passfile_dir_number_reject,
                                                passfile_dir_number_delete: item.passfile_dir_number_delete,
                                                passfile_dir_number_other: (parseInt(item.passfile_dir_number_other)+ parseInt(item.passfile_file_number_other))
                                            });

                                            let passFilesDetailsOption = {
                                                columns: [
                                                    {
                                                        field: 'total_pass_number',
                                                        title: LANG.UI_OBS_BACKUP_SKIP_NUMS,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'total_pass_ratio',
                                                        title: LANG.UI_OBS_BACKUP_SKIP_RATIO,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'total_pass_dir_number',
                                                        title: LANG.UI_FILE_COUNT_PASS_PREFIX,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'total_pass_dir_ratio',
                                                        title: LANG.UI_FILE_COUNT_PASS_PREFIX_RATIO,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'passfile_number_limit',
                                                        title: LANG.UI_OBS_SKIP_FILE_ALARM_NUM,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'passfile_ratio_limit',
                                                        title: LANG.UI_OBS_SKIP_FILE_ALARM_RATIO,
                                                        align: 'center',
                                                    }
                                                ],
                                                data: passFileDetailData
                                            }

                                            let passFileReasonsOption = {
                                                columns: [
                                                    {
                                                        field: 'passfile_file_number_occupy',
                                                        title: LANG.UI_OBS_OCCUPY_FILE_NUMBER,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'passfile_file_number_delete',
                                                        title: LANG.UI_OBS_DELETE_FILE_NUMBER,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'passfile_file_number_reject',
                                                        title: LANG.UI_OBS_NOPERMISSION_FILE_NUMBER,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'passfile_dir_number_reject',
                                                        title: LANG.UI_OBS_NOPERMISSION_BUCKET_NUMBER,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'passfile_dir_number_delete',
                                                        title: LANG.UI_OBS_DELETED_BUCKET_NUMBER,
                                                        align: 'center',
                                                    },
                                                    {
                                                        field: 'passfile_dir_number_other',
                                                        title: LANG.UI_OBS_OTHER_NUMBER,
                                                        align: 'center',
                                                    }
                                                ],
                                                data: passFileReasonData
                                            }

                                            $('#pass_files_details_table').bootstrapTable('destroy'); // 销毁上一次的table
                                            $('#pass_files_details_table').bootstrapTable(passFilesDetailsOption);

                                            $('#pass_files_reason_table').bootstrapTable('destroy'); // 销毁上一次的table
                                            $('#pass_files_reason_table').bootstrapTable(passFileReasonsOption);

                                            // 下载按钮click监听
                                            $('#downloadTxt').off().click(function() {
                                                downloadPassFun(historyUUID, agentUUID);
                                            });
                                        });
                                    } else {
                                        $(`.downloadPassFile_${historyUUID}`).off().click(function() {
                                            downloadPassFun(historyUUID, '');
                                        });
                                    }
                                }
                            })
                        }
                    } else {
                        UIToastr.showWarning(LANG.UI_OBS_GET_HISTORY_DETAIL_DATA_FAILED, res.message);
                    }

                    Metronic.unblockUI($detailView);
                });
            },
            columns: [
                {
                    checkbox: true,
                    sortable: false
                },
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: false,
                    align: 'center'
                },
                {
                    field: 'task_type',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: true,
                    align: 'center'
                },
                {
                    field: 'job_status',
                    title: LANG.UI_VISUAL_RESULT,
                    sortable: false,
                    align: 'center',
                    formatter: function (index, row) {
                        switch (row.job_status_value) {
                            case 0: //成功
                                return '<span class="label label-sm label-success  ">' + row.job_status + '</span>';
                            case 2: //中止
                            case 45:
                                return '<span class="label label-sm label-info  ">' + row.job_status + '</span>';
                            case 3: //异常
                            case 47:
                                return '<span class="label label-sm label-warning  ">' + row.job_status + '</span>';
                            case 1: //失败
                                return '<span class="label label-sm label-danger  ">' + row.job_status + '</span>';
                            default:
                                return '<span class="label label-sm label-danger  ">' + row.job_status + '</span>';
                        }
                    }
                },
                {
                    field: 'total_object_size',
                    title: LANG.UI_MICROSOFT365_ALL_SIZE,
                    sortable: true,
                    align: 'center'
                },
                {
                    field: 'total_object_completed_size',
                    title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                    sortable: true,
                    align: 'center'
                },
                {
                    field: 'total_object_write_size',
                    title: LANG.UI_JOB_HIS_REAL_SIZE,
                    sortable: true,
                    align: 'center'
                },
                {
                    field: 'start_time',
                    title: LANG.UI_PUBLIC_START_TIME,
                    sortable: true,
                    align: 'center'
                },
                {
                    field: 'finish_time',
                    title: LANG.UI_PUBLIC_END_TIME,
                    sortable: true,
                    align: 'center'
                }
            ]
        }

        const initHistoryTable = () => {
            if (!initFlag) {
                $('#historytable').baseTableConfig().init(historyTableOptions);
                initFlag = true;
            } else {
                $('#historytable').bootstrapTable('refresh');
            }
        }

        $('#job_detail_bottom_nav a[data-toggle="tab"]').on('show.bs.tab', (e) => {
            if (e.target.hash === '#history') {
                initHistoryTable();
            }
        })
    }

    //<-----------------------------    END HISTORY TASK    ------------------------------>

    /**
     * 初始化路由参数
     */
    const initRouteParams = () => {
        let route = History.getState();
        let paramStr = route.data.url.split('?')[1];
        let taskTypeStr = paramStr.split('&')[0];
        let uuidStr = paramStr.split('&')[1];

        TASK_UUID = uuidStr.split('=')[1];
        TASK_TYPE = parseInt(taskTypeStr.split('=')[1]);
    }

    const initListeners = () => {
        // 回车搜索事件
        $('#obs_detail_seach_ipt').keypress(function (e) {
            if (e.which == 13) {
                searchVal = $('#obs_detail_seach_ipt').val();

                if (searchVal) {
                    $('#filestable').bootstrapTable('refresh', { query: { search: searchVal }})
                }
            }
        });

        // 搜索对象存储列表
        $('#obs_detail_search_btn').off().on('click', () => {
            searchVal = $('#obs_detail_seach_ipt').val();

            if (searchVal) {
                $('#filestable').bootstrapTable('refresh', { query: { search: searchVal }})
            }
        });

        $('#obs_detail_seach_ipt').on('focus', () => {
            $('#obs_detail_clear_search').removeClass('hide');
        });

        // 清空搜索
        $('#obs_detail_clear_search').on('click', () => {
            $('#obs_detail_seach_ipt').val('');
            searchVal = '';
            $('#obs_detail_clear_search').addClass('hide');
            $('#filestable').bootstrapTable('refresh', { query: { search: searchVal } } );
        });
    }

	var initSwiper = function(){
		//先给swiper插件里面的元素加上class
		$('.swiper-detail').addClass('swiper');
		$('.swiper-detail').attr('style','overflow: hidden');
		$('.swiper-detail').find('ul.nav.nav-tabs ').addClass('swiper-wrapper');
		$('.swiper-detail').find('ul.nav.nav-tabs > li').addClass('swiper-slide widthauto');
		var mySwiper = new Swiper ('.swiper',{
			slidesPerView :'auto',
			freeMode: false,	//惯性滑动且不会贴合
            navigation: {
                nextEl: '.swiper-button-next_detail',
                prevEl: '.swiper-button-prev_detail',
				disabledClass: 'display-none',
            },
			allowTouchMove:false
		});
		var predisable = mySwiper.navigation.prevEl.ariaDisabled == 'true' ? true : false;
		var nextdisable = mySwiper.navigation.nextEl.ariaDisabled == 'true' ? true : false;
		if(predisable && nextdisable){
			$('.swiper-detail').addClass('swiper-no-swiping')
		}
	}
    return {
        init: function () {
            initRouteParams();
            initBasicInfo();
            initSpeed();
            watchEchartSizeChange();
            initLogGrid();
            initFSGrid();
            initObsGridOpBtn();
            initHistoryGrid();
            initListeners();
        }
    };
}();

jQuery(document).ready(function() {
    Obsjobdetail.init();
});