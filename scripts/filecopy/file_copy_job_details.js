var FileSyncJobDetails = function () {

    var initChartFlag = false; //任务曲线图初始化标志
    var _taskStatus; //监控任务状态
    var _jobType;//监控任务类型
    var initFileCopyGridFlag = false,initCompareGridFlag = false;
    var  expandIndex = null
    var myChart;
    var firstLocation = true;//恢复任务完成只弹出一次倒计时
    let FILE_NUM = 40;//一次性请求的文件数量
    let COMPARE_DETAIL_DATA;//对比展开详情数据
    let DIFF_LOAD_FLAG = 0; ////默认是0，打开时1，打开后就只会返回文件的差异子项
    let task_user_uuid = '';//任务关联的用户uuid
    //初始化基本信息
    var initBasicInfo = function () {
        var updateInterval = 2000;
        var init = function () {
            if (0 == $('#task_uuid').size()) {
                clearTimeout(timerTask.jobDetails_basicInfo);
                return;
            }
            var jobs_uuid = $("#task_uuid").val();
            pAjaxRequest({}, "/api/v1/filecopy/job/" + jobs_uuid + "/info", "GET", function (result) {
                setBasicInfo(result.data, timerTask.jobDetails_basicInfo)
            }, false);
            timerTask.jobDetails_basicInfo = setTimeout(init, updateInterval);
        }
        init();
    }

    //设置基本信息
    var setBasicInfo = function (data, timeoutID) {
		//任务完成等待状态
		// if(data.job_status!= LANG.UI_FILE_RUNNING){
		// 	$('#total-progress').css({width: '0%'});
		// 	$('#progressright').html('');
		// }
		_taskStatus = data.job_status;
        _jobType = data.job_type;
        task_user_uuid = data.task_user_uuid;
		// if(!data.flag){
		// 	clearTimeout(timeoutID);
		// 	$('#total-progress').css({width: '100%'});
		// 	$('#progressright').html('100%');
		// 	UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
		// 	setTimeout(function(){
		// 		LOCATION('./content/platform/jobs/jobs.php', 'task');
		// 	}, 5000);
		// }
		//初始化操作按钮
		initOpButton(data)
		$('#taskName').html(data.job_name);
		$('#taskType').html(data.job_type_des);
        if(data.job_status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.job_status) + '" >' + getStatusDes(data.job_status) + '</span>');
		}
        $('#task_stage').html(data.current_stage_value);
		$('#totalSize').html(data.total_size);
		$('#currentSize').html(data.current_size);
		$('#startTime').html(data.start_time);
		$('#intervalTime').html(data.interval_time);
		$('#speed').html(data.speed);
		$('#progress').html(data.progress);
		$('#compress_storage').html(getFlagLevelInfo(data.transport_strategy.compress_flag));
        $('#copyInterval').html(data.time_strategy[0] ? data.time_strategy[0].roll_interval : '--');
        $('#firstCopyTime').html(data.time_strategy[0] ? data.time_strategy[0].first_start_time : '--');
        // 快照
		$('#silentsnapshotcheck').html(getFlagLevelInfo(data.snap_shot_flag));
        // 文件权限复制
		$('#permission_flag').html(getFlagLevelInfo(data.permission_operate_flag));
         //复制勾选目录
		$('#sync_self_flag').html(getFlagLevelInfo(data.filter_info.sync_self_flag));
        //传输线程
        $('#transfer_thread').html(data.thread_num);
        // 扫描线程
        $('#scan_thread').html(data.scan_thread_number);
        if(data.scan_thread_number == 1) {
            // 扫描文件速度
            $('#scan_speed').html(getScanSpeed(data.scan_file_speed));
        } else {
            $('.scan-speed-form-item').hide();
        }
        //跳过文件告警智能判断
		$('#passfilealarmcheck').html(getFlagLevelInfo(data.skip_file_alarm_flag));
		if(data.skip_file_alarm_flag == CONF.FLAG.SET) {
			$('.pass-des-form-item').show();
			$('#passfilenum').html(data.skip_file_alarm_min_num);
			$('#warningpercent').html(data.skip_file_alarm_min_ratio + '%');
		} else {
			$('.pass-des-form-item').hide();
		}
        // 同名文件处理
		$('#file_same_name_handle').html(getSameNameStrategy(data.same_file_strategy));
        if (data.same_file_strategy == 1) {//同名文件处理是覆盖
            $('.recover-way-form-item').show();
            $('#file_check_mode').html(data.filter_info.check_mode == 1 ? LANG.UI_FILE_COPY_METADATA : LANG.UI_FILE_COPY_FILE_CONTENT);
            if (data.filter_info.check_mode == 2) {//校验方式是文件内容
                //校验算法
                $('.file_verification_algorithm-form-item').show();
                $('#file_verification_algorithm').html('MD5');
            } else {
                $('.file_verification_algorithm-form-item').hide();
            }
            //覆盖规则
            $('#sync_rule').html(data.filter_info.sync_rule == 1 ? LANG.UI_FILE_COPY_RULE1 : LANG.UI_FILE_COPY_RULE2);
        } else {
            $('.recover-way-form-item').hide();
        }
		$('#wildcardmode').html(getFlagLevelInfo(data.wildcardMode));
		//恢复传输线程-磁带不显示
		if (data.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
			$('.transfer-thread-num-form').hide();
		}
		//重试策略
        $('#network_retry_times').html(data.retry_strategy.network_retry_times);
        $('#network_retry_interval').html(data.retry_strategy.network_retry_interval);
        $('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
        $('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
        if (data.retry_strategy.op_retry_flag) {
            $('#op_retry_times').html(data.retry_strategy.op_retry_times);
            $('#op_retry_interval').html(data.retry_strategy.op_retry_interval);
        } else {
            $('.op-retry-form-item').hide();
        }
        if (data.retry_strategy.task_retry_flag) {
            // $('#task_retry_object').html(data.retry_strategy.task_retry_object == 1 ? '仅重试任务中失败的对象' : '重试任务中所有的对象');
            $('#task_retry_times').html(data.retry_strategy.task_retry_times);
            $('#task_retry_interval').html(data.retry_strategy.task_retry_interval / 60);
        } else {
            $('.task-retry-form-item').hide();
        }
        //过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
		$('#createTime').html(data.create_time);
		$('#nextTime').html(data.next_time);
		// 在编排中的任务，显示按编排策略执行（原时间策略不生效）
		if(data.task_orchestration_plan_flag){
			$('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
		}
        //压缩传输
        if (data.transport_strategy.compress_flag_value) {
            // 压缩等级
            var method = '';
			switch(data.transport_strategy.compress_method) {
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
			$('#transfer_compress').html(method);
        } else {
            $('#transfer_compress').html(getFlagLevelInfo(data.transport_strategy.compress_flag));
        }

		//加密传输
		if(data.transport_strategy){
			$('#transfer_encrypt').html(getFlagLevelInfo(data.transport_strategy.encrypt_flag_value));
		}
		// 传输加密算法
		if(data.transport_strategy.encrypt_flag_value){
			$('.transfer-encrypt-method-form-item').show();
			let encryptMethod = data.transport_strategy.encrypt_method;
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if(encryptMethod == 2){
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$('#transfer_encrypt_method').html(method);
		}else{
			$('.transfer-encrypt-method-form-item').hide();
		}
        // 通配符配置
        $('#wildcard').html(getWildcardDes(data.filter_info.wildcard_list));
        // 时间配置
		$('#time_range').html( getTimeRangeDes(data.filter_info.time_range_list));
		//限速策略
		$('#speed_limit_backup').html(data.speed_limit.value);
        $('#speed_limit_backup').prop('title', data.speed_limit.des);
		// 任务等级
		var task_priority = data.speed_limit.task_priority;
		if(task_priority){
			switch(data.speed_limit.task_priority) {
				case 1:
					task_priority = LANG.UI_JOB_TASK_PRIORITY_PRIMARY;
					break;
				case 2:
					task_priority = LANG.UI_JOB_TASK_PRIORITY_HIGH;
					break;
				case 3:
					task_priority = LANG.UI_JOB_TASK_PRIORITY_HIGHEST;
					break;
			}
			$('#task_priority_backup').html(task_priority);
		}else{
			$('.task-priority-form-item-backup').hide();
		}
        showCongig(data.source_type, data.target_type, data.src_os_type, data.des_os_type);
         //存储信息
         if (data.storage_info.node) {
            var node = data.storage_info.node;
            $('#backup_node').html(data.storage_info.node_pool_nickname ?
                                    data.storage_info.node_pool_nickname + ":<br/>" + node.name:
                                    node.name);
        }
    }

    const showCongig = function (source_type, target_type,src_os_type,des_os_type) {
        let permissionShowFlag = false;
        let snapshotShowFlag = false;
        //源端和目标端,只要有一端是文件或者hadoop就显示快照
        if (source_type == CONF.SUBMODULE_TYPE.FS || source_type == CONF.SUBMODULE_TYPE.HADOOP
            || target_type == CONF.SUBMODULE_TYPE.FS || target_type == CONF.SUBMODULE_TYPE.HADOOP) {
            $('.silentsnapshotcheck-form-item').show();
            snapshotShowFlag = true;
        } else {
            $('.silentsnapshotcheck-form-item').hide();
            snapshotShowFlag = false;
        }
        //操作系统类型不一样，屏蔽权限复制
        if (src_os_type != des_os_type || source_type != target_type) {
            $('.permission-form-item').hide();
            permissionShowFlag = false;
        } else {
            $('.permission-form-item').show();
            permissionShowFlag = true;
        }
        //源端和目标端都为nas设备时，屏蔽整个传输策略和网络重连配置
        if (source_type == CONF.SUBMODULE_TYPE.NAS && target_type == CONF.SUBMODULE_TYPE.NAS) {
            $('.transfer-strategy-show').hide();
            $('.network_retry_times_show').hide();
            $('.network_retry_interval_show').hide();
        } else {
            $('.transfer-strategy-show').show();
            $('.network_retry_times_show').show();
            $('.network_retry_interval_show').show();
        }
    }

    var getScanSpeed = function (level) {
		var des = "";
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
    var initOpButton = function (data) {
        setBtnStatus(data);
        //任务详情按钮
        $('.startFileCopy').unbind().on('click', function () {
            if ($(this).find('a').hasClass('disablebtn')) {
                return true;
            }
            startJobUnify(62);
        });
        $('.startCompare').unbind().on('click', function () {
            if ($(this).find('a').hasClass('disablebtn')) {
                return true;
            }
            startJobUnify(63);
        });

        $('.stop').unbind().on('click', function () {
            if ($(this).find('a').hasClass('disablebtn')) {
                return true;
            }
            opJob('stopJob');
        });
        var opJob = function (funName) {
            checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
                Metronic.blockUI({target: '#jobDetail', animate: true});
                var job_uuid = $('#task_uuid').val();
                if (funName == 'stopJob') {
                    pAjaxRequest({}, "/api/v1/jobs/stop/" + job_uuid + "", "POST",function (res) {
                        Metronic.unblockUI('#jobDetail');
                        var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                        if (operateResponseList(res, op)) {
                        }
                    });
                }
            });
        }

        //启动任务
        var startJobUnify = function (type) {
            checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
                var params = {
                    'start_type': type
                };
                var job_uuid = $('#task_uuid').val();
                Metronic.blockUI({target: '#jobDetail', animate: true});
                pAjaxRequest(params, "/api/v1/jobs/start/" + job_uuid + "", 'POST', function (data) {
                    Metronic.unblockUI('#jobDetail');
                    var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
                    if (operateResponseList(data, op)) {
                    }

                });
            });
        }
    }
    //得到开启和关闭的HTML内容
    var getFlagLevelInfo = function (flag) {
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if (!flag || flag == LANG.UI_PUBLIC_OFF_ONE || flag == 2) {
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    //得到同名文件处理策略
	var getSameNameStrategy = function (flag) {
		var des = '';
		switch (parseInt(flag)) {
			case 1:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_COVER;
				break;
			case 3:
				des = LANG.UI_RECOVERY_SKIP;
				break;
			case 4:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_RENAME;
				break;
		}
		return des;
	}

    //得到开启和关闭内容，无样式
    var getFlagInfo = function (flag) {
        var des = "";
        switch (parseInt(flag)) {
            case 1:
                des = LANG.UI_PUBLIC_ON;
                break;
            case 2:
                des = LANG.UI_PUBLIC_OFF;
                break;
        }
        return des;
    }

    //得到状态的显示类型
    var getStatusLevelClass = function (level) {
        var levelClass = '';
        switch (level) {
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
            case CONF.TASK_STATUS.PENDING:
            case CONF.TASK_STATUS.CLEANING:
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

    //得到任务状态描述
    var getStatusDes = function (status) {
        var des = '--';
        switch (status) {
            case 1:
                des = LANG.UI_VISUAL_WAIT;
                break;
            case 2:
                des = LANG.UI_VISUAL_RUN;
                break;
            case 3:
                des = LANG.UI_JOB_PAUSE;
                break;
            case 4:
                des = LANG.UI_JOB_STOP;
                break;
            case 5:
                des = LANG.UI_PUBLIC_STOPPING;
                break;
            case 7:
                des = LANG.UI_VISUAL_NODE_ABNORMAL;
                break;
            case 8:
                des = LANG.UI_PUBLIC_ERROR;
                break;
            case 12:
                des = LANG.UI_PUBLIC_STARTING;
                break;
            case 19:
                des = LANG.UI_PUBLIC_PENDING;
                break;
            case 21:
                des = LANG.UI_NODE_STATUS_CLEANING;
                break;
        }
        return des;
    }

    //得到复制间隔描述
    var getStrategyFrequency = function (strategy) {
        var frequency = "";
        var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
        for (var i = 1; i <= 52; i++) {
            if (strategy.frequency == "s" + i) {
                if (i == 1) {
                    frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
                } else {
                    frequency = frequencyLang.replace('x', i) + ",";
                }
            }
        }
        return frequency;
    }
    var getStrategyDays = function (days) {
        var desDays = '';
        $.each(days, function (i, d) {
            if (1 == d) {
                var day = i + 1;
                if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                    desDays += day + ", ";
                } else {
                    desDays += "Day" + day + ", ";
                }
            }
        });
        return desDays;
    }
    //获取每周显示日期
    var getStrategyWeek = function (days) {
        var desDays = '';
        $.each(days, function (i, d) {
            if (1 == d) {
                desDays += CONF.WEEK[i] + ", ";
            }
        });
        return desDays;
    }
    var getEachStrategy = function (strategy) {
        var desEach = '';
        desEach += strategy.start_time;
        //如果是英文版 需要加空格
        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
            desEach += " "; //策略开始时间
        }
        desEach += LANG.UI_STRATEGY_START + ", ";
        if (strategy.roll_flag) {
            desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.end_time;
        } else {
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }
        desEach += "<br>";
        return desEach;
    }

    //得到保留策略描述信息
    var getReservedStrategy = function (msg) {
        var reservedStr = '';
        if (!msg) {
            reservedStr = LANG.UI_PUBLIC_NOTHING;
            return reservedStr;
        }
        if (CONF.RESERVE_TYPE.NUM == msg.type) {
            reservedStr += LANG.UI_STRATEGY_RESERVE_NUM;
            reservedStr += "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value;
        } else if (CONF.RESERVE_TYPE.DAY == msg.type) {
            reservedStr += LANG.UI_STRATEGY_RESERVE_DAY;
            reservedStr += "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value;
        } else if (CONF.RESERVE_TYPE.PERMANENT == msg.type) {
            reservedStr += LANG.UI_FILE_PERMANENT;
        }
        return reservedStr;
    }

    //初始化流量
    var initSpeed = function () {
        // 根据不同分辨率动态计算echart的高度和宽度
        var chartWidth = $('.portlet-charts__body__speedchart').width();
        var chartHeight = $('.portlet-charts__body__speedchart').height();
        $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
        // 基于准备好的dom，初始化echarts实例
        myChart = echarts.init(document.getElementById('speedchart'));
        // 指定图表的配置项和数据
        var option = {
            tooltip: {
                trigger: 'axis',
                formatter: function (params, ticket, callback) {
                    var value = params[0].data;
                    if (value >= 1024) {
                        return Math.round(value * 100 / 1024) / 100 + " MB/s";
                    } else {
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

        var updateInterval = 2000;
        var data = [], nowTime = [];

        var parseNum = function (num) {
            num = parseInt(num);
            num = num >= 10 ? num : "0" + num;
            return num;
        }

        var getShowTime = function (timeStamp) {
            var myDate = new Date(parseInt(timeStamp));
            var date = myDate.toLocaleDateString();
            var hours = myDate.getHours();
            var minutes = myDate.getMinutes();
            var seconds = myDate.getSeconds();
            return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
        }

        //初始化任务进度曲线图
        var initTaskSpeed = function (d) {

            if (initChartFlag) {
                return; //初始化了就直接返回
            }

            var serverTime = d.t * 1000;
            for (var i = 100; i > 0; i--) {
                data.push(0);
                nowTime.push(getShowTime(serverTime - i * 3000));
            }
            option.series[0].data = data
            option.xAxis.data = nowTime;
            myChart.setOption(option);

            initChartFlag = true;
        }
        var p = {};
        p.jobs_uuid = $("#task_uuid").val();

        function update()
        {
            if (0 == $('#speedchart').size()) {
                clearTimeout(timerTask.jobDetails_speed);
                return;
            }
            pAjaxRequest(p, "/api/v1/exchange/jobs/speed", "GET", function (result) {
                initTaskSpeed(result.data);
                data.shift();
                data.push(result.data.speed);
                option.series[0].data = data;
                nowTime.shift();
                nowTime.push(result.data.nowTime);
                option.xAxis.data = nowTime;
                myChart.setOption(option);
            }, true)
            // .complete(function () {
            timerTask.jobDetails_speed = setTimeout(update, updateInterval);
        }

        update();

        window.onresize = function () {
            myChart.resize();
        }

    }
    //根据任务状态设置按钮权限
    var setBtnStatus = function (data) {
        setControlBtn('startFileCopy', true);
        setControlLookResult('label-click',true);
        setControlBtn('startCompare', true);
        var timeStrategy = data.time_strategy;
        switch (_taskStatus) {
            case 5:
                //停止中
                setControlBtn('startFileCopy', false);
                setControlLookResult('label-click',false);
                setControlBtn('startCompare', false);
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
                break;
            case 2:
            case 10:
            case 12:
                //运行、准备中停止中,禁用运行 12是启动中
                setControlBtn('startFileCopy', false);
                if (data.job_type == CONF.TASK_TYPE.FILE_COMPARE) {
                    setControlLookResult('label-click',false);
                }
                setControlBtn('startCompare', false);
                setControlBtn('stop', true);
                break;
            case 4:
                //停止,禁用停止
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
                setControlBtn('startFileCopy', true);
                setControlLookResult('label-click',true);
                setControlBtn('startCompare', true);
                setControlBtn('stop', false);
                break;
            case 19:
                //挂起
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
                setControlBtn('startFileCopy', false);
                setControlLookResult('label-click',false);
                setControlBtn('startCompare', false);
                break;
            case 20:
                //删除中
                setControlBtn('startFileCopy', false);
                setControlLookResult('label-click',false);
                setControlBtn('startCompare', false);
                setControlBtn('stop', false);
                break;
            default:
                //其他状态,开启控制
                setControlBtn('startFileCopy', true);
                setControlLookResult('label-click',true);
                setControlBtn('startCompare', true);
                setControlBtn('stop', true);
                break;
        }
    }
    //设置按钮是否可用
    var setControlBtn = function (id, available) {
        if (available){
            $("." + id).find('.btn').prop('disabled', false);
        } else {
            $("." + id).find('.btn').prop('disabled', true);
        }
    }

    //设置查看对比结果按钮是否可用
    var setControlLookResult = function (id, available) {
        if (available) {
            $("." + id).attr('data-toggle', 'drawer');
            $("." + id).addClass('lookResult');
            $('.label-click').css({
                'color': '#00A3FF',
                'cursor': 'pointer'
            });
        } else {
            $("." + id).attr('data-toggle', '');
            $("." + id).removeClass('lookResult');
            $('.label-click').css({
                'color': '#9f9e9e',
                'cursor': 'not-allowed'
            });
        }
    }


    //初始化日志表格
    var initLogGrid = function () {
        $('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
    }


    //初始化对象列表表格
    var initFileCopyGrid = function () {
        var jobs_uuid = $("#task_uuid").val();
        var options = {
            detailFormatter: function (row, data, div) {
                var thead = '';
                var tbody = '';
                var filter_info = data.filter_info;
                thead = `<thead>` +
                            `<th style="width: 20%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_FILTER_BY_WILDCARD +`</th>`+
                            `<th style="width: 20%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_FILTER_BY_TIME +`</th>`+
                            `<th style="width: 33%;"> `+ LANG.UI_FILE_COPY_PATH +`</th>`+
                            `<th style="width: 33%;"></th>`+
                        `</thead>`;
                tbody = `<tbody>` +
                            `<tr>` +
                                `<td><span class="file-copy-list-detail">`+ getWildcardDes(filter_info.wildcard_list) +`</span></td>`+
                                `<td><span class="file-copy-list-detail">`+ getTimeRangeDes(filter_info.time_range_list) +`</span></td>`+
                                `<td>`+ $('<textarea class="history-detail-area">').text(getFilelist(data.file_list))[0].outerHTML +`</td>`+
                                `<td></td>`+
                                `</tr>` +
                        `</tbody>`;
                return `<table>`+ thead + tbody +`</table>`;
            },
            searchInput: true,
            pagination: true,
            pageList: [5, 10, 25, 50],
            detailView: true,
            vin_url: "/api/v1/filecopy/job/" + jobs_uuid + "/list",
            vin_method: "GET",
            onRefresh: function () {
                $("#filecopyTable").bootstrapTable('hideLoading');
                scroll = $("#filecopyTable").bootstrapTable('getScrollPosition');
            },
            onPostBody: (data) => {
                if (null !== expandIndex) {
                    $("#filecopyTable").bootstrapTable('expandRow', expandIndex);
                }
                $("#filecopyTable").bootstrapTable('scrollTo', scroll);
            },
            onExpandRow: (index) => {
                if (null === expandIndex) {
                    expandIndex = index;
                } else if (index !== expandIndex) {
                    $("#filecopyTable").bootstrapTable('collapseRow', expandIndex);
                    expandIndex = index;
                }
            },
            onCollapseRow: () => {
                expandIndex = null;
            },
            columns: [
                {
                    field: 'source_name',
                    title: LANG.UI_FILE_DETAIL_SRC_NAME,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'target_name',
                    title: LANG.UI_FILE_DETAIL_DES_NAME,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'task_type_des',
                    title: LANG.UI_PUBLIC_TASK_TYPE,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'total_fs_count',
                    title: LANG.UI_HADOOP_TOTAL_NUM,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'current_fs_count',
                    title: LANG.UI_MICROSOFT365_COMPLETED_NUM,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'copy_speed',
                    title: LANG.UI_VISUAL_SPEED,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'copy_percent',
                    title: LANG.UI_VISUAL_PROGRESS,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'task_status_des',
                    title:  LANG.UI_VISUAL_RESULT,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, data, row) {
                        return '<span>' + data.task_status_des + '</span>';
                    }
            },
            ],
        }
        if (0 == $('#task_uuid').size()) {
            clearTimeout(timerTask.jobDetails_filecopyGrid);
            return;
        }
        if (!initFileCopyGridFlag) {
            $('#filecopyTable').baseTableConfig().init(options);
            initFileCopyGridFlag = true;
        } else {
            $('#filecopyTable').bootstrapTable('refresh');
        }
        timerTask.jobDetails_filecopyGrid = setTimeout(initFileCopyGrid, 2000);
        let data = $('#filecopyTable').bootstrapTable('getData');
        if (data.length != 0) {
            $('.file-copy-src').html(data[0].source_name);
            $('.file-copy-des').html(data[0].target_name);
        }
    }

    var getMatchMode = function(mode) {
        var des = '';
        switch(parseInt(mode)) {
            case 1: //排除复制
                des = LANG.UI_HISTORY_JOB_DETAIL_EXCEPT_COPY;
                break;
            case 2: //选定复制
                des = LANG.UI_HISTORY_JOB_DETAIL_SELECT_COPY;
                break;
        }
        return des;
    }

    var getWildcardDes = function(info) {
        var des = '';
        var wildcardList = info.wildcard;
        if (info.wildcard_mode == 0) {
            return LANG.UI_PUBLIC_NOTHING;
        }
        var mode = getMatchMode(info.wildcard_mode);
        des += LANG.UI_HISTORY_JOB_DETAIL_COMPARE_CONDITION + ':'  + mode + '</br>';
        for(var i = 0; i < wildcardList.length; i++) {
            des += LANG.UI_HISTORY_JOB_DETAIL_WILDCARD_OBJECT + ':' + (wildcardList[i].wildcard_file_type == 1 ?  LANG.UI_DATA_FILE_FILE : LANG.UI_DATA_FILE_DIR) + ',';
            des += LANG.UI_FILE_WILDCARD + ':' + wildcardList[i].wildcard + '</br>'
        }
        return des;
    }

    var getTimeRangeDes = function(info) {
        var des = '';
        var timeList = info.time_list;
        if (info.time_fliter_mode == 0) {
            return LANG.UI_PUBLIC_NOTHING;
        }
        var mode = getMatchMode(info.time_fliter_mode);
        des += LANG.UI_HISTORY_JOB_DETAIL_COMPARE_CONDITION + ':'  + mode + '</br>';
        if (info.last_days != 0) {
            des += LANG.UI_HISTORY_JOB_DETAIL_COPY_RECENT + info.last_days + LANG.UI_PUBLIC_UNIT_DAY + '</br>';
        } else {
            for(var i = 0; i < timeList.length; i++) {
                var start_time = parseInt(timeList[i].time_fliter_time_start);
                var end_time = parseInt(timeList[i].time_fliter_time_end);
                des += LANG.UI_HISTORY_JOB_DETAIL_FILE_EDIT_TIME_RANGE + ':' + formatDate(start_time) + '-' + formatDate(end_time) + '</br>'
            }
        }
        return des;
    }

    var getFilelist = function(fileList) {
        var des = '';
        for(var i = 0; i < fileList.length; i++) {
            des += fileList[i].path_name + '->' + fileList[i].target_path_name + '\n';
        }
        return des;
    }

    var formatDate = function(timestamp) {
        var date = new Date(timestamp * 1000);
        // 格式化日期和时间
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2); // 月份从0开始，需要加1
        var day = ('0' + date.getDate()).slice(-2);
        var hours = date.getHours();
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);
        // 格式化后的日期和时间字符串
        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    }

    var setTree = function (result) {
        Metronic.unblockUI('.diff-file-tree-content');
        if (result.success) {
            $('#diffFileTree').show();
            $("#no-file-tips").hide();
        } else {
            operateResponseList(result,result.title)
            $('#diffFileTree').hide();
            $("#no-file-tips").show();
            return;
        }

        var setting = {
            check: {
                enable: true,
                nocheckInherit: false
            },
            view: {
                showTitle: true,
                nameIsHTML: true
            },
            data: {
                simpleData: {
                    enable: true
                },
            },
            callback: {
                onCheck: nodeCheck,
                beforeClick: nodeClick,
                beforeExpand: nodeExpand
            },
            view: {
                fontCss: setFontCss,
            }
        };
        zTree = $.fn.zTree.init($("#diffFileTree"), setting, result.data.fileNode);
        $(".diff-total").html(result.data.total_diff);
    };

    //设置颜色文件对比结果颜色
    function setFontCss(treeId, treeNode)
    {
        var color = {};
        switch(parseInt(treeNode.level_flag)) {
            case 1://正常的不处理
                break;
            case 2://差异
                color = {"color" : "#e79500d9","font-weight" : "bold",};
                break;
            case 3://异常的
            case 4:
            case 5:
            case 6:
                color = {"color" : "#c70000e0","font-weight" : "bold",};
                break;

        }
        return color;
    };

    var nodeCheck = function() {

    }

    var nodeClick = function(treeId, treeNode) {
        if (treeNode.file_type == 1) {//文件
            return;
        }
        if (treeNode.more) {//加载更多
            getMoreNode(treeId, treeNode);
            return;
        }
        nodeExpand(treeId, treeNode);
    }

    var nodeExpand = function(treeId, treeNode) {
        let div = '.diff-file-tree-content'
        Metronic.blockUI({target: div,animate: true});
        let params = {info:{}};
        params.info.task_uuid = treeNode.job_uuid;
        params.info.path_uuid = treeNode.path_uuid;  //指向这个对比结果
        params.info.item_load_number = FILE_NUM;//一次展示的文件个数
        params.info.aim_id = treeNode.aim_id;//默认是0，后台给的父目录的id文件
        params.info.aim_site = treeNode.aim_site;//这个父目录的偏移位置，默认是0
        params.info.aim_item_load_offset = 0;//从第几个子项开始载入，这个参数是后台返回的，然后页面保留，开始的时候默认就是0
        params.info.dif_load_flag = DIFF_LOAD_FLAG;//默认是0，打开时1，打开后就只会返回文件的差异子项
        params.info.first_load_flag = 2;//界面上点击对比结果展示时候的载入，这个值设置成1后续的都是2
        params.node_uuid = COMPARE_DETAIL_DATA[0].node_uuid;
        pAjaxRequest(params, "/api/v1/filecopy/job/compare_result", "POST", function(result) {
            Metronic.unblockUI(div);
            if (result.success) {
                if (treeNode.checked) {
                    for(var i=0;i<result.data.fileNode.length;i++) {
                        result.data.fileNode[i].checked = true;
                    }
                }
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.data.fileNode, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
            } else {
                operateResponseList(result,result.title)
            }

        }, true)
    }

    var getMoreNode = function (treeId, treeNode) {
        let div = '.diff-file-tree-content'
        Metronic.blockUI({target: div,animate: true});
        let params = {info:{}};
        let pNode = treeNode.getParentNode();
        params.info.task_uuid = treeNode.job_uuid;
        params.info.path_uuid = treeNode.path_uuid; //指向这个对比结果
        params.info.item_load_number = FILE_NUM;//一次展示的文件个数
        params.info.aim_id = pNode.aim_id;//默认是0，后台给的父目录的id文件
        params.info.aim_site = pNode.aim_site;//这个父目录的偏移位置，默认是0
        params.info.aim_item_load_offset = treeNode.next_index;//从第几个子项开始载入，这个参数是后台返回的，然后页面保留，开始的时候默认就是0
        params.info.dif_load_flag = DIFF_LOAD_FLAG;//默认是0，打开时1，打开后就只会返回文件的差异子项
        params.info.first_load_flag = 2;//界面上点击对比结果展示时候的载入，这个值设置成1后续的都是2
        params.node_uuid = COMPARE_DETAIL_DATA[0].node_uuid;
        pAjaxRequest(params, "/api/v1/filecopy/job/compare_result", "POST", function(result) {
            Metronic.unblockUI(div);
            if (result.success) {
                if (pNode.check_Child_State == 2) {
                    for(var i=0;i<result.data.fileNode.length;i++) {
                        result.data.fileNode[i].checked = true;
                    }
                }
                $.fn.zTree.getZTreeObj(treeId).removeNode(treeNode);
		    	$.fn.zTree.getZTreeObj(treeId).addNodes(pNode, result.data.fileNode, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
            } else {
                operateResponseList(result,result.title)
            }

        }, true);

    }

    var lookResult = function() {
        var params = {info:{}};
        params.info.task_uuid = $("#task_uuid").val();
        params.info.path_uuid = '';  //指向这个对比结果
        params.info.item_load_number = FILE_NUM;//一次展示的文件个数
        params.info.aim_id = 0;//默认是0，后台给的父目录的id文件
        params.info.aim_site = 0;//这个父目录的偏移位置，默认是0
        params.info.aim_item_load_offset = 0;//从第几个子项开始载入，这个参数是后台返回的，然后页面保留，开始的时候默认就是0
        params.info.dif_load_flag = DIFF_LOAD_FLAG;//默认是0，打开时1，打开后就只会返回文件的差异子项
        params.info.first_load_flag = 1;//界面上点击对比结果展示时候的载入，这个值设置成1后续的都是2
        params.node_uuid = COMPARE_DETAIL_DATA[0].node_uuid;
        Metronic.blockUI({target: '.diff-file-tree-content', animate: true});
        pAjaxRequest(params, "/api/v1/filecopy/job/compare_result", "POST", setTree, true)
    }

    var initCompareGrid = function() {
        var operates = {
            'click .lookResult': function () {
                lookResult();
            }
        }
        var jobs_uuid = $("#task_uuid").val();
        var options = {
            detailFormatter: function (row, data, div) {
                let thead = '';
                let tbody = '';
                let trList = '';
                thead = `<thead>` +
                            `<th style="width: 18%;"> `+ LANG.UI_FILE_COPY_PATH +`</th>`+
                            `<th style="width: 15%;"> `+ LANG.UI_PUBLIC_START_TIME +`</th>`+
                            `<th style="width: 15%;"> `+ LANG.UI_PUBLIC_END_TIME +`</th>`+
                            `<th style="width: 10%;"> `+ LANG.UI_FILE_COPY_COMPARE_TOTAL +`</th>`+
                            `<th style="width: 10%;"> `+ LANG.UI_FILE_COPY_SAME_NUM +`</th>`+
                            `<th style="width: 10%;"> `+ LANG.UI_FILE_COPY_DIFF_NUM +`</th>`+
                            `<th style="width: 10%;"> `+ LANG.UI_FILE_COPY_ABNOEMAL_NUM +`</th>`+
                        `</thead>`;
                if(COMPARE_DETAIL_DATA.length == 0) {
                    trList += `<tr>` +
                                    `<td><span class="file-copy-list-detail">`+ `--` +`</span></td>`+
                                    `<td><span class="file-copy-list-detail">`+ `--` +`</span></td>`+
                                    `<td><span class="file-copy-list-detail">`+ `--` +`</span></td>`+
                                    `<td><span class="file-copy-list-detail">`+ `--` +`</span></td>`+
                                    `<td><span class="file-copy-list-detail">`+ `--` + `</span></td>`+
                                    `<td><span class="file-copy-list-detail">`+ `--` +`</span></td>`+
                                    `<td><span class="file-copy-list-detail">`+ `--` +`</span></td>`+
                                `</tr>`;
                } else {
                    COMPARE_DETAIL_DATA.forEach(item => {
                        let total_count = parseInt(item.same_object_count) + parseInt(item.dif_object_count) + parseInt(item.abnormal_object_count);
                        trList += `<tr>` +
                                      `<td><span class="file-copy-list-detail">`+ item.path +`</span></td>`+
                                      `<td><span class="file-copy-list-detail">`+ item.start_time +`</span></td>`+
                                      `<td><span class="file-copy-list-detail">`+ item.end_time +`</span></td>`+
                                      `<td><span class="file-copy-list-detail">`+ total_count +`</span></td>`+
                                      `<td><span class="file-copy-list-detail">`+ item.same_object_count + `</span></td>`+
                                      `<td><span class="file-copy-list-detail">`+ item.dif_object_count +`</span></td>`+
                                      `<td><span class="file-copy-list-detail">`+ item.abnormal_object_count +`</span></td>`+
                                  `</tr>`;
                    });
                }
                tbody = `<tbody>` + trList + `</tbody>`;
                return `<table>` + thead + tbody + `</table>`;
            },
            pagination: true,
            pageList: [5, 10, 25, 50],
            detailView: true,
            vin_params:function(){
                return {"detail_flag":false};
            },
            vin_url: "/api/v1/filecopy/job/" + jobs_uuid + "/compare_list",
            vin_method: "GET",
            onPostBody: (data) => {
                $('#compareTable th[data-field="operate"]').css('width', '10%');
            },
            columns: [
                {
                    field: 'task_type',
                    title: LANG.UI_PUBLIC_TASK_TYPE,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, data, row) {
                        return LANG.UI_FILE_COPY_COMPARE;
                    }
            },
                {
                    field: 'total_count',
                    title: LANG.UI_FILE_COPY_COMPARE_TOTAL,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, data, row) {
                        return parseInt(data.dif_object_count) + parseInt(data.same_object_count) + parseInt(data.abnormal_object_count);
                    }
            },
                {
                    field: 'same_object_count',
                    title: LANG.UI_FILE_COPY_SAME_NUM,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'dif_object_count',
                    title: LANG.UI_FILE_COPY_DIFF_NUM,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'abnormal_object_count',
                    title:  LANG.UI_FILE_COPY_ABNOEMAL_NUM,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'operate',
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    align: 'center',
                    opButton: true,
                    clickToSelect: false,//不可以通过点击列选中
                    events:operates,
                    formatter: function() {
                        var disableStatus = [19, 20, 2, 10, 12, 5];
                        var style = '';
                        if (_jobType == CONF.TASK_TYPE.FILE_COMPARE && disableStatus.includes(_taskStatus)) {
                            style = `style="color: #9f9e9e;cursor: not-allowed;"`
                        } else {
                            style = `style="color: #00A3FF;cursor: pointer;"`
                        }
                        return '<span '+ style +' class="lookResult label label-sm label-click" data-target="#compareDrawer">' + LANG.UI_FILE_COPY_COMPARE_RESULT_LOOK + '</span>';
                    }
            },
            ],
        }
        if (0 == $('#task_uuid').size()) {
            clearTimeout(timerTask.jobDetails_compareGrid);
            return;
        }
        var init = function() {
            if (!initCompareGridFlag) {
                $('#compareTable').baseTableConfig().init(options);
                initCompareGridFlag = true;
            } else {
                $('#compareTable').bootstrapTable('refresh');
            }
        }
        timerTask.jobDetails_compareGrid = setTimeout(initFileCopyGrid, 2000);
        //绑定事件
        toBindEvent();

        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            if ("#compareLi" == e.target.hash) {
                init();
                initCompareDetail();
            }

        })
    }

    var toBindEvent = function() {
        //复制对比结果
        $('#copyResult').on('click', function () {
            copyCompareResult();
        });
    }

    var initCompareDetail = function() {
        let params = {};
        params.detail_flag = true;
        params.job_uuid = $("#task_uuid").val();
        pAjaxRequest(params, "/api/v1/filecopy/job/compare_list", "GET", function(result) {
            COMPARE_DETAIL_DATA = [];
            if (result.success) {
                COMPARE_DETAIL_DATA = result.data;
            }
        });
    }

    var copyCompareResult = function() {
        var includeAbnormalFlag = false;//是否包含差异文件
        var checkedNodes = zTree.getCheckedNodes(true);
        if (checkedNodes.length == 0) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_COMPARE_RESULT,LANG.UI_FILE_COPY_COMPARE_RESULT_TIPS);
            return false;
        }
        checkedNodes = filterFile(checkedNodes);
        for(var i = 0; i < checkedNodes.length; i++) {
            if (checkedNodes[i].level_flag > 1) {
                includeAbnormalFlag = true;
                break;
            }
        }
        if (!includeAbnormalFlag) {//没有差异或异常文件
            UIToastr.showWarning(LANG.UI_FILE_COPY_COMPARE_RESULT,LANG.UI_FILE_COPY_COMPARE_RESULT_TIPS);
            return false;
        }
        var params = {path_list:[]};
        params.task_uuid = checkedNodes[0].job_uuid;
        var tempPathUuid = '';
        for(var i = 0; i < checkedNodes.length; i++) {
            //tempPathUuid未赋值时，是第一次进来，当tempPathUuid已赋值，并且当前这个已经和前面的
            //path_uuid不同了，加入下一组path_list
            if(tempPathUuid == ''
            || (tempPathUuid != '' && tempPathUuid != checkedNodes[i].path_uuid)) {
                params.path_list.push({
                    "path_uuid": checkedNodes[i].path_uuid,
                    "file_list": [{
                        "file_type": checkedNodes[i].file_type,
                        "aim_id": checkedNodes[i].aim_id,
                        "aim_site": checkedNodes[i].aim_site
                    }]
                });
            } else if(tempPathUuid == checkedNodes[i].path_uuid) {//同一组path_list
                params.path_list.forEach((item,i) => {
                    if(item.path_uuid == tempPathUuid) {
                        params.path_list[i].file_list.push({
                            "file_type": checkedNodes[i].file_type,
                            "aim_id": checkedNodes[i].aim_id,
                            "aim_site": checkedNodes[i].aim_site
                        });
                    }
                });
            }
            tempPathUuid = checkedNodes[i].path_uuid
        }
        params.dif_copy_force_flag = $('#dif_copy_force_flag').get(0).checked ? 1 : 2;
        pAjaxRequest({"info":params}, "/api/v1/filecopy/job/copy_compare_result", "POST", function(data) {
            if (data.success) {
                UIToastr.showSuccess(LANG.UI_COPY_SEND_START_JOB_MESSAGE,LANG.UI_FILE_COPY_COMPARE_RESULT_START + LANG.UI_PUBLIC_SUCCESS);
                return;
            }
            UIToastr.showWarning(LANG.UI_COPY_SEND_START_JOB_MESSAGE,LANG.UI_FILE_COPY_COMPARE_RESULT_START + LANG.UI_PUBLIC_FAILED);
        }, true)

    }

    //过滤文件
    var filterFile = function (allfileNodes) {
        var allCheckedNode = [];
        //得到所有勾选状态是全选中的节点
        for(var i=0; i<allfileNodes.length; i++){
            //check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
            if(allfileNodes[i].check_Child_State == 2 || allfileNodes[i].check_Child_State == -1) {
                allCheckedNode.push(allfileNodes[i]);
            }
        }
        //过滤掉重复的
        for(var m = 0;m<allCheckedNode.length;m++) {
            if(allCheckedNode[m].file_type != 1) {//磁盘或文件夹
                for(var n = 0;n<allCheckedNode.length;n++) {
                    var str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
                    if(str.includes(allCheckedNode[m].file_path) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
                        allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
                        n--;
                    }
                }
            }
        }
        return allCheckedNode;
    }

    //初始化历史任务表格
    var initHistoryGrid = function () {
        var initFlag = false;
        //表格option
        var jobs_uuid = $("#task_uuid").val();
        var lastIndex = [-1, -1];
        var options = {
            detailFormatter: function (row, data, div) {
                var job_type_des = LANG.UI_CM_CDP_REPLICATION;
                if (data.job_type_value == CONF.TASK_TYPE.FILE_COMPARE) {
                    job_type_des = LANG.UI_FILE_COPY_TASK_TYPE_COMPARE;
                }
                //只展开一行
                if (row != lastIndex[1]) {
                    lastIndex.push(row);
                    $('#historytable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                    lastIndex.splice(0, 1);
                }
                var thead = '';
                var tbody = '';
                var elecontent = '',style = '';
                if (data.detail.list.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
                    thead = `<tr>
                                <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                                <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                                <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                            </tr>`;

                    let resourceLimitingNodeConfig = data.detail.list.resource_limiting_node_config;
                    tbody =
                        `<tr>
                            <td>` + LANG.UI_PUBLIC_ON + `</td>
                            <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                            <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                        </tr>`;
                    return `<table>${thead}${tbody}</table>`;

                } else {//未开启资源限制
                    thead = `<thead>` +
                                `<th style="width: 7%;"> `+ LANG.UI_FILE_DETAIL_SRC_NAME +`</th>`+
                                `<th style="width: 7%;"> `+ LANG.UI_FILE_DETAIL_DES_NAME +`</th>`+
                                `<th style="width: 7%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_FILTER_BY_WILDCARD +`</th>`+
                                `<th style="width: 10%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_FILTER_BY_TIME +`</th>`+
                                `<th style="width: 6%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_COPY_FILE_COUNT +`</th>`+
                                `<th style="width: 6%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_COPY_DIR_COUNT +`</th>`+
                                `<th style="width: 8%;"> `+ job_type_des + LANG.UI_HISTORY_JOB_DETAIL_COPY_FILE_COUNT +`</th>`+
                                `<th style="width: 8%;"> `+ job_type_des + LANG.UI_HISTORY_JOB_DETAIL_COPY_DIR_COUNT +`</th>`+
                                `<th style="width: 7%;"> `+ LANG.UI_FILE_PASSFILE_LISTS +`</th>`+
                                `<th style="width: 12%;"> `+ LANG.UI_HISTORY_JOB_DETAIL_COPY_DATA_LIST +`</th>`+
                                `<th style="width: 5%;"> `+ LANG.UI_PUBLIC_DESCRIPTION +`</th>`+
                            `</thead>`;
                    tbody = `<tbody>`;
                    for (var i = 0; i< data.detail.list.copy_list.length; i++) {
                        let dir_count = data.detail.list.copy_list[i].dir_count;
                        if (data.job_type_value == CONF.TASK_TYPE.FILE_COMPARE) {
                            dir_count = data.detail.list.copy_list[i].dir_scan_count;
                        }
                        var total = parseInt( data.detail.list.copy_list[i].total_pass_dir_number) + parseInt( data.detail.list.copy_list[i].total_pass_file_number);
                        var path = data.detail.list.copy_list[i].source + '->' + data.detail.list.copy_list[i].target;
                        if(total > 0) {
                            elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                            style = 'style="color: #0fbf98;pointer-events:unset;"'
                        }else {
                            elecontent = LANG.UI_PUBLIC_NOTHING;
				    	    style = 'style="color: black;pointer-events:none;"'
                        }
                        tbody += `<tr>` +
                                    `<td> `+ data.detail.list.source_name +`</td>`+
                                    `<td> `+ data.detail.list.target_name +`</td>`+
                                    `<td><span class="file-copy-list-detail path-no-border">`+ getWildcardDes(data.detail.list.wildcard_list) +`</span></td>`+
                                    `<td><span class="file-copy-list-detail path-no-border">`+ getTimeRangeDes(data.detail.list.time_range_list) +`</span></td>`+
                                    `<td> `+ data.detail.list.copy_list[i].file_scan_count +`</td>`+
                                    `<td> `+ data.detail.list.copy_list[i].dir_scan_count +`</td>`+
                                    `<td> `+ data.detail.list.copy_list[i].file_count +`</td>`+
                                    `<td> `+ dir_count +`</td>`+
                                    //跳过文件下载
                                    `<td>` + `<a data-toggle="drawer" data-target="#passFileDrawer" value="`+ path +`" class="downloadPassFile" `+ style +`>`+ elecontent +`<span class="display-none">`+ data.detail.list.copy_list[i].passfile_file_path +`</span></a>` + `</td>` +
                                    `<td class="path-no-border"> `+ $('<div>').text(path).html() +`</td>`+
                                    `<td> `+ data.detail.list.copy_list[i].error_code +`</td>`+
                                `</tr>`;
                    }
                    tbody += `</tbody>`;
                }
                let html = `<table>`+ thead + tbody +`</table>`;
                $(div).append(html);
                $("a.downloadPassFile").off().click(function(){
                    var passfile_file_path = $(this).find(":first-child")[0].innerText;
                        //跳过文件详情模态框表格里面的数据
                        let node_uuid;
                        let pass_file_size = 0//默认一个
                        data.detail.list.copy_list.forEach(item=> {
                            let thisPathDes = item.source + '->' + item.target;
                            let passfile_number_limit = data.detail.list.passfile_number_limit;
                            let passfile_ratio_limit = data.detail.list.passfile_ratio_limit;
                            path = passfile_file_path;
                            node_uuid = item.node_uuid;
                            if(thisPathDes == $(this).attr('value')) {
                                pass_file_size = item.passfile_file_size ?? 1024;
                                $('#file_skip_num').html(item.total_pass_file_number);
                                $('#file_skip_radio').html(item.file_count == 0 ? '0.00%' : ((item.total_pass_file_number/item.file_count)*100).toFixed(2)+'%');
                                $('#dir_skip_num').html(item.total_pass_dir_number);
                                $('#dir_skip_radio').html(item.dir_count == 0? '0.00%' : ((item.total_pass_dir_number/item.dir_count)*100).toFixed(2)+'%');
                                $('#file_skip_alarm_num').html(passfile_number_limit);
                                $('#file_skip_alarm_radio').html( passfile_ratio_limit +'%');
                                $('#file_occupied_num').html(item.passfile_file_number_occupy);
                                $('#file_delete_num').html(item.passfile_file_number_delete);
                                $('#no_permission_file_num').html(item.passfile_file_number_reject);
                                $('#no_permission_dir_num').html(item.passfile_dir_number_reject);
                                $('#dir_delete_num').html(item.passfile_dir_number_delete);
                                $('#other_num').html(parseInt(item.passfile_dir_number_other)+ parseInt(item.passfile_file_number_other));
                            }
                        });
                        $('#downloadDrawer').off().click(function() {
                            downloadPassFileCopy(path,node_uuid,pass_file_size);
                        });
                });
            },
            searchInput: true,
            pagination: true,
            pageList: [5, 10, 25, 50],
            sortName:'start_time',
            sortOrder:'desc',
            detailView: true,
            vin_url: '/api/v1/jobs/' + jobs_uuid + '/history',
            vin_method: "GET",
            columns: [
                {
                    field: 'job_type',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'job_status',
                    title: LANG.UI_VISUAL_RESULT,
                    sortable: true,
                    align: 'center',
                    formatter: function (value, data, row) {
                        switch (data.job_status_value) {
                            case 0: //成功
                                return '<span class="label label-sm label-success  ">' + data.job_status + '</span>';
                            case 2: //中止
                            case 45:
                                return '<span class="label label-sm label-info  ">' + data.job_status + '</span>';
                            case 3: //异常
                            case 47:
                                return '<span class="label label-sm label-warning  ">' + data.job_status + '</span>';
                            case 1: //失败
                                return '<span class="label label-sm label-danger  ">' + data.job_status + '</span>';
                            default:
                                return '<span class="label label-sm label-danger  ">' + data.job_status + '</span>';
                        }
                    }
            },
                {
                    field: 'all_size',
                    title: LANG.UI_MICROSOFT365_ALL_SIZE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'speed_size',
                    title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'write_size',
                    title: LANG.UI_PUBLIC_REAL_SIZE,
                    sortable: true,
                    align: 'center',
            },
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
            },
            ],
        }

        var downloadPassFileCopy = function(path,node_uuid,pass_file_size) {
			var data = {
                'node_uuid':node_uuid,
                'path':path,
                'pass_file_size':pass_file_size
            };
			Metronic.blockUI({target: '#passFileDrawer',animate: true});
            pAjaxRequest(data, "/api/v1/filecopy/download_pass", "GET", function (result) {
                Metronic.unblockUI('#passFileDrawer');
                window.location.href = '/api/v1/filecopy/download_pass' + '?node_uuid=' + node_uuid + '&pass_file_size=' + pass_file_size + '&path=' + path + '&x-api-version=1.0-rev0';
            });
		}
        var init = function () {
            if (!initFlag) {
                $('#historytable').baseTableConfig().init(options);
                initFlag = true;
            } else {
                $('#historytable').bootstrapTable('refresh');
            }
        }

        //转换文件大小
        const filterSize = (size) => {
            if (!size) {
                return '';
            }
            return size < 1024 ? size + ' B' :
                size < pow1024(2) ? (size / 1024).toFixed(2) + ' KB' :
                    size < pow1024(3) ? (size / pow1024(2)).toFixed(2) + ' MB' :
                        size < pow1024(4) ? (size / pow1024(3)).toFixed(2) + ' GB' :
                            (size / pow1024(4)).toFixed(2) + ' TB'
        }

        // 求次幂
        function pow1024(num)
        {
            return Math.pow(1024, num)
        }

        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            e.target // newly activated tab
            e.relatedTarget // previous active tab
            if ("#history" == e.target.hash) {
                init();
            }
        })
    }
    var watchEchartSizeChange = function () {
        window.onresize = function () {
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

    var initListener = function () {
        $('#dif_load_flag').on('switchChange.bootstrapSwitch', difLoadChange);
    }

    var difLoadChange = function () {
        var flag = $('#dif_load_flag').get(0).checked;
        if (flag) {//只显示差异文件
            DIFF_LOAD_FLAG = 1;
        } else {
            DIFF_LOAD_FLAG = 0;
        }
        lookResult();
    }

    return {
        //main function to initiate the module
        init: function () {
            initBasicInfo();
            initSpeed();
            initLogGrid();
            initHistoryGrid();
            initFileCopyGrid();
            initCompareGrid();
            watchEchartSizeChange();
            initListener();
        }
    };
}();

jQuery(document).ready(function () {
    FileSyncJobDetails.init();
});