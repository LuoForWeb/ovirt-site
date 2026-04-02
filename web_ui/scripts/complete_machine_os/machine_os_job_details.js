var MachineOSJobDetails = function () {

	var logGrid;
	var initLogFlag = false;	//任务日志初始化标志
	var _scrollHeight = 0; //任务日志全局高度
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndexLog = 0,detailsInfoLog = null;
    var  _origin_data = {};

	var initChartFlag = false; //任务曲线图初始化标志

	var osSelect = []; //主机选择

	var _taskStatus; //监控任务状态

	var jobParams = {}; //操作需要参数

	var initGridFlag = false;
	var  expandIndex = null

	var initButFlag = false;

	var myChart;
    var hostChecked = [];
    var _UserPassword = "";


    const TIME_BACKUP_STRATEGY_TYPE = {
        STRATEGY: 1,
        ONCETIME: 2,
        MANUAL: 3
    }



	var initListener = function(){
        $('.startFullList').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                startJobHostUnify(1);
            })

        });
        $('.startIncrList').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                startJobHostUnify(2);
            })

        });
        $('.startDiffList').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                startJobHostUnify(3);
            })

        });




	}
    //启动任务主机
    var startJobHostUnify = function (type) {
        //获取hostlist表格勾选的数据
        var hostlist = $("#hostlist").bootstrapTable('getSelections');
        if(hostlist.length == 0){
            UIToastr.showWarning(LANG.UI_MACHINE_OS_START_TASK_HOST, LANG.UI_MACHINE_OS_START_TASK_HOST_TIPS);
            return false;
        }
        var hostlist_agent_uuids = [];
        for(var i = 0; i < hostlist.length; i++){
            hostlist_agent_uuids.push(hostlist[i].agent_uuid)
        }
        var params = {
            'backup_mode': type,
            'taskuuid': $('#task_uuid').val(),
            'os_uuids': hostlist_agent_uuids,
        };
        Metronic.blockUI({target: '#host', animate: true});
        pAjaxRequest(params, '/api/v1/complete_machine_os/start_host', 'POST', function (data) {
            Metronic.unblockUI('#host');
            if (data.success) {
                UIToastr.showSuccess(LANG.UI_JOB_START, data.message);
            } else {
                UIToastr.showWarning(LANG.UI_JOB_START, data.message);
            }

        });
    }

	var initBasicInfo = function(){
		var updateInterval = 3000;
		var init = function(){
			var requestData  = function(data){
				if(data.success){
                    _origin_data = data.data;;
					setBasicInfo(data.data);
				}else{
					UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_SOURCE_HOST, data.message);
				}
			}
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.MachineOsJobDetails_taskRunningInfo);
        		return;
        	}
			var data = {};
			data.job_uuid = $("#task_uuid").val();
			//初始化备份源
			pAjaxRequest(data, '/api/v1/complete_machine_os/get_basic_info', "GET", requestData, true);
			timerTask.MachineOsJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}




    //得到状态的显示类型
    var getStatusLevelClass = function(level){
        var levelClass = 'label-info';
        var statusDes = "--";
        switch(level){
            case CONF.TASK_STATUS.WAITTING:
                statusDes = LANG.UI_PUBLIC_WAIT
                levelClass = "label-info";
                break;
            case CONF.TASK_STATUS.STOPPING:
                statusDes = LANG.UI_PUBLIC_STOPPING
                levelClass = "label-info";
                break
            case CONF.TASK_STATUS.PREPARING:
                statusDes = LANG.UI_PUBLIC_READYING
                levelClass = "label-info";
                break;
            case CONF.TASK_STATUS.RUNNING:
                statusDes = LANG.UI_PUBLIC_RUNNING
                levelClass = "label-success";
                break;
            case CONF.TASK_STATUS.FINISHED:
                statusDes = LANG.UI_VISUAL_ALREADY_FINISH
                levelClass = "label-success";
                break;
            case CONF.TASK_STATUS.PAUSED:
                statusDes = LANG.UI_JOB_PAUSE
                levelClass = "label-success";
                break;
            case CONF.TASK_STATUS.SUCCESSED:
                statusDes = LANG.UI_PUBLIC_SUCCESS
                levelClass = "label-success";
                break;
            case CONF.TASK_STATUS.STARTING:
                statusDes = LANG.UI_PUBLIC_STARTING
                levelClass = "label-success";
                break;
            case CONF.TASK_STATUS.STOPPED:
                statusDes = LANG.UI_VISUAL_STOP
                levelClass = "label-default";
                break;
            case CONF.TASK_STATUS.ABNORMAL:
                statusDes = LANG.UI_NODE_ABNORMAL
                levelClass = "label-warning";
                break;
            case CONF.TASK_STATUS.NETWORK_FAULT:
                statusDes = LANG.UI_PUBLIC_NETWORK_ERROR
                levelClass = "label-danger";
                break;
            case CONF.TASK_STATUS.CREATING:
                statusDes = LANG.UI_PUBLIC_CREATING
                levelClass = "label-default";
                break;
            case CONF.TASK_STATUS.PENDING:
                statusDes = LANG.UI_PUBLIC_PENDING
                levelClass = "label-warning";
                break;
            case CONF.TASK_STATUS.ERROR:
                statusDes = LANG.UI_PUBLIC_FAILED
                levelClass = "label-danger";
                break;
            case CONF.TASK_STATUS.DELETING:
                statusDes = LANG.UI_NODE_STATUS_DELETING
                levelClass = "label-info";
                break;
            // case 16:
            //     statusDes = LANG.UI_JOB_STOP_TAKEOVER
            //     levelClass = "label-default";
            //     break;
            // case 17:
            //     statusDes = LANG.UI_VISUAL_SUCCESS
            //     levelClass = "label-success";
            //     break;
        }
        return [levelClass,statusDes];
    }

	 //得到开启和关闭的HTML内容
	 var getFlagLevelInfo = function (flag) {
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if (!flag || flag == LANG.UI_PUBLIC_OFF_ONE || flag == 2) {
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

	//得到时间策略描述信息
    var getTimeStrategy = function (msg, timeStrategyBackupType) {

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

		//得到备份间隔描述
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


        var timeInfo = {full:LANG.UI_PUBLIC_NOTHING, incr:LANG.UI_PUBLIC_NOTHING, diff:LANG.UI_PUBLIC_NOTHING, log:LANG.UI_PUBLIC_NOTHING, pincr:LANG.UI_PUBLIC_NOTHING};
        if (!msg) {
            return timeInfo;
        }
        for (var i = 0; i < msg.length; i++) {
            var strategy = msg[i];
            var des = "";
            if (CONF.STRATEGY_TYPE.DAY == strategy.type) {
                des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
            } else if (CONF.STRATEGY_TYPE.WEEK == strategy.type) {
                des += getStrategyFrequency(strategy);
                if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                    des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
                } else {
                    des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
                }
            } else if (CONF.STRATEGY_TYPE.MONTH == strategy.type) {
                des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
            } else if (CONF.STRATEGY_TYPE.GLOBAL == strategy.type) {
                des += strategy.start_time;
            } else {
                des += LANG.UI_PUBLIC_NOTHING;
            }
            if (1 == strategy.mode) {
                if (timeStrategyBackupType == 1) { //按策略备份才显示完备补偿
                    if(strategy.full_backup_compensation_flag){
                        des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_ON;
                    } else {
                        des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_OFF;
                    }
                 }
                timeInfo.full = des;
            } else if (2 == strategy.mode) {
                timeInfo.incr = des;
            } else if(3 == strategy.mode){
				timeInfo.diff = des;
			}else if (9 == strategy.mode) {
                timeInfo.pincr = des;
            }
        }
        return timeInfo;
    }

	//得到保留策略描述信息
    var getReservedStrategy = function (msg) {
        var reservedStr = '';
        if (!msg) {
            reservedStr = LANG.UI_PUBLIC_NOTHING;
            return reservedStr;
        }
        reservedStr += LANG.UI_RESERVE_RETENTION_TYPE+": ";
        if(msg.strategy_mode == 1){
            reservedStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT;
        }
        if(msg.strategy_mode == 2){
            reservedStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN;
        }
        reservedStr += "<br>"+LANG.UI_RESERVE_RETENTION_MODE+": ";
        if (CONF.RESERVE_TYPE.NUM == msg.type) {
            reservedStr += LANG.UI_BACKUP_NUM;
            reservedStr += "<br>"+LANG.UI_STRATEGY_VALUE+": " + msg.value;
        } else if (CONF.RESERVE_TYPE.DAY == msg.type) {
            reservedStr += LANG.UI_BACKUP_DAY;
            if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                reservedStr += "<br>"+LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY+": " + msg.value;
            }else{
                reservedStr += "<br>"+LANG.UI_STRATEGY_VALUE+": " + msg.value;
            }

        } else if (CONF.RESERVE_TYPE.PERMANENT == msg.type) {
            reservedStr += LANG.UI_FILE_PERMANENT;
        }
        return reservedStr;
    }



	var setBasicInfo = (data) =>{
        if(!data.flag){
            setTimeout(function(){
                LOCATION('./content/platform/jobs/jobs.php');
            }, 3000);
        }
		//保存获取到的任务参数
		_taskStatus = data.job_status;
        jobParams.status = data.job_status;
        jobParams.taskType = data.job_type;
		jobParams.subModule = data.submodle_type;

		//恢复
        if(data.job_type == 35){
            //备份
            $(".backup_div").show();
            $(".recover_div").hide();
        }else{
            //恢复
            $(".backup_div").hide();
            $(".recover_div").show();
        }


		//概要--------------------------------------------
		//--任务名
		$('#taskName').html(data.job_name);
		//--任务类型
		$('#taskType').html(data.module_type_des + data.job_type_des);
		//--任务状态
		if(data.job_status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.job_status)[0] + '" >' + getStatusLevelClass(data.job_status)[1] + '</span>');
		}
        //任务阶段
        $("#current_stage").html(data.current_stage_value);
        if(_taskStatus == 2){
            //--任务总容量
            $('#totalSize').html(data.total_size);
            //--已处理容量
            $('#currentSize').html(data.complate_size);
            //--开始时间
            $('#startTime').html(data.start_time);
            //持续时间
            $('#intervalTime').html(data.interval_time);
        }else{
            //--任务总容量
            $('#totalSize').html("--");
            //--已处理容量
            $('#currentSize').html("--");
            //--开始时间
            $('#startTime').html("--");
            //持续时间
            $('#intervalTime').html("--");
        }

        //策略--------------------------------------------
        //--创建/修改时间
		$('#createTime').html(data.create_time);
        //恢复时间策略
        if (data.job_type == 36 && data.time_strategy[0] && data.time_strategy[0].type == 4) {
            $("#time_recovery_strategy").html(LANG.UI_KUBE_RECOVER_SPECIFIED_TIME+"("+data.time_strategy[0].start_time+")")
        } else {
            $("#time_recovery_strategy").html(LANG.UI_KUBE_RECOVER_IMMEDIATELY)
        }


		//--下次开始时间
		$('#nextTime').html(data.next_time);
        //备份方式
        switch (data.timeStrategyBackupType) {
			case  TIME_BACKUP_STRATEGY_TYPE.STRATEGY:
				$('#timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);
				break;
			case TIME_BACKUP_STRATEGY_TYPE.ONCETIME:
				$('#timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);
				break;
			case TIME_BACKUP_STRATEGY_TYPE.MANUAL:
				$('#timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);
				$('.backup_time_strategy_div').hide();
				break;
            default:
                break;
		}
		// 在编排中的任务，显示按编排策略执行（原时间策略不生效）
		if(data.task_orchestration_plan_flag){
			$('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
		}
		//--时间策略
		var timeStrategy = getTimeStrategy(data.time_strategy,data.timeStrategyBackupType);
        //--完全备份
        $('#fulldes').html(timeStrategy.full);
        //--增量备份
        $('#incdes').html(timeStrategy.incr);
		//--差异备份
		$('#diffdes').html(timeStrategy.diff);
        //--永久增量
        $('#pincrdes').html(timeStrategy.pincr);
        //通用--------------------------------------------
        //限速策略---
        //--限速策略
		$('#speedlimit').html(data.speed_limit.text);
		$('#speedlimit').prop('title', data.speed_limit.des);
        //存储策略---
        if(data.storage_info.flag){
			var node = data.storage_info.node;
			var storage = data.storage_info.storage;
			var high = data.storage_info.high;
			var storage_info = '';
			if(!storage){
				//没有存储信息,自动选择存储
				storage_info = LANG.UI_JOB_AUTO_SELECT_STORAGE;
			}else{
				storage_info = storage.name + "(" + storage.type + ")<br>";
				if(!storage.quota_flag){
					storage_info += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
					LANG.UI_JOB_FREE_SIZE + ":" + storage.free_size;
				}else{
					storage_info += storage.quota_des;
				}
			}
            $('#nodeinfo').html(data.storage_info.node_pool_nickname ?
                data.storage_info.node_pool_nickname + ":<br/>" + node.name:
                node.name);
            $('#storageinfo').html(data.storage_info.storage_pool_nickname ?
                data.storage_info.storage_pool_nickname +":<br/>" + storage_info:
                storage_info);

            //获取存储类型, 如果是磁带屏蔽安全策略
            if(storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE){
                $(".safty_show").hide();

            }

			//--压缩存储
            $('#compressed').html(getFlagLevelInfo(high.compressed));
            //重复数据删除
            $('#deduplication').html(getFlagLevelInfo(high.deduplication));
			// 压缩等级
			if(high.compressed){
				var method = '';
				switch(high.compress_method) {
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
				$('#compressMethod').html(method);
			}else{
                $('.compressMethodDiv').hide();
            }
			//数据加密
			// 存储加密算法
			if(high.encrypt_flag){
				let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
				if(high.encrypt_method == 2){
					method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
				}
				$('#encryptStorage').html(method);
                $('.passwordAutodiv').show();
			}else{
                $('.passwordAutodiv').hide();
                $('#encryptStorage').html(getFlagLevelInfo(high.encrypt_flag));
			}
			//自动密码
			$('#passwordAuto').html(getFlagLevelInfo(high.password_auto_flag));
            //数据文件大小
            $("#data_container_size").html(high.data_container_size);
            //合并冗余数据比例
            $("#redundant_data_proportion").html(high.redundant_data_proportion);

		}
        //--保留策略
        if(data.reserve_strategy){
            if (data.storage_info.storage && data.storage_info.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                //磁带获取策略替换保留策略
                $('.reserve-strategy-form-item .strategy-group__form__item__label').html(LANG.UI_GLOBAL_STRATEGY_NAME);
                $('.reserve-strategy-form-item #reservedStrategy').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
            }else{
                if(data.reserve_strategy.type == 2){
                    //按链保留隐藏合并
                    $(".redundant_data_proportion_div").hide();
                }
                $('#reservedStrategy').html(getReservedStrategy(data.reserve_strategy));
            }
        }

        //传输策略--------------------------------------------
        //--加密传输
        // 传输加密算法
        if(data.transport_strategy.encrypt_flag_value){
            let encryptMethod = data.transport_strategy.encrypt_method;
            let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
            if(encryptMethod == 2){
                method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
            }
            $("#encryptStrategy").html(method);
        }else{
            $("#encryptStrategy").html(getFlagLevelInfo(data.transport_strategy.encrypt_flag_value));
        }
        //源端压缩

        if(data.transport_strategy.compress_flag_value){
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
				$("#original_compress_flag").html(method);
        }else{
            $("#original_compress_flag").html(getFlagLevelInfo(data.transport_strategy.compress_flag_value));
        }




        //传输网络
		if(data.transport_strategy.network != ""){
			$("#transferNetwork").html(data.transport_strategy.network);
			$('.transferNetworkdiv').show();
		}else{
			$('.transferNetworkdiv').hide();
		}
        //--传输线程
        if(CONF.FUNCTIONS.includes('multithread')){
            $('#threadCount').html(data.thread_num);
        }else{
            $(".threadCountDiv").hide();
        }
        //安全策略
        //如果三个都没授权则屏蔽安全策略
        //这里判断下是否3个都没授权
		// if(!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity') && !CONF.FUNCTIONS.includes('worm')){
		// 	//如果三个都没授权
		// 	$(".safty_show").hide();
		// }else if(!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity') && CONF.FUNCTIONS.includes('worm')){
		// 	//如果授权了没授权, 但是worm授权了
		// 	if(data.storage_info.flag){
		// 		//如果存储配置了worm
		// 		$(".safty_show").show();
		// 	}else{
		// 		$(".safty_show").hide();
		// 	}
		// }else{
		// 	//其他情况就是不显示
		// 	$(".safty_show").show();
		// }

        if(CONF.FUNCTIONS.includes('worm') && data.safe_strategy.worm_storage_flag){
            if(data.safe_strategy.worm_flag){
                let wormDes = LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD+data.safe_strategy.worm_protection_time +LANG.UI_PUBLIC_UNIT_DAY;
                $("#worm").html(wormDes);
            }else{
                $("#worm").html(getFlagLevelInfo(data.safe_strategy.worm_flag));
            }
        }else{
            $(".worm_show").hide();
        }



        //病毒检测
        if(CONF.FUNCTIONS.includes('virusKill')){
            if(!data.safe_strategy.virus_scan_flag){
                let virusDes = getFlagLevelInfo(data.safe_strategy.virus_scan_flag);
                $("#virus").html(virusDes);
            }else{
                if(data.job_type == 35){
                    $("#virus").html($.fn.getVirusConfigDes(data.safe_strategy, 'backup'));
                }else{
                    $("#virus").html($.fn.getVirusConfigDes(data.safe_strategy, 'recovery'));
                }
            }
        }else{
            $(".virus_show").hide()
        }



        //完整性效验
        if(CONF.FUNCTIONS.includes('integrity')){
            if(data.job_type == 35){
                $('#integrity').html($.fn.getCompleteDetectionBackupDes({
                    integrityCheckFlag: data.safe_strategy.integrity_check_flag,
                    integrityCheckConfig: data.safe_strategy.integrity_check_config,
                    isFullTimepointTitle: true,
                    showIncrErrorPolicy: true,
                    excludeTitleFlag: true,
                }));
            }else{
                $("#integrity").html(data.safe_recover_info.complete);
            }
        }else{
            $(".integrity_show").hide()
        }

        //静默快照
        $("#silentSnapshot").html(getFlagLevelInfo(data.os_config.silent_snapshot_flag));
        //cbt
        $("#CBT").html(getFlagLevelInfo(data.os_config.cbt_flag));
        //忽略节点资源限制
        $("#ignoreResourceLimit").html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
        //跳过坏块备份
        $("#skipBadBlock").html(getFlagLevelInfo(data.os_config.skip_bad_track_flag));
        //有效数据备份
        $("#validData").html(getFlagLevelInfo(data.os_config.full_backup));

        //重试策略
        //网络重试次数
        $("#network_retry_times").html(data.retry_strategy.network_retry_times);
        //网络重连时间间隔
        $("#network_retry_interval").html(data.retry_strategy.network_retry_interval);
        //操作异常自动重试
        $("#op_retry_flag").html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
        if(data.retry_strategy.op_retry_flag){
            $('.op_retry_div').show();
            //操作异常重连次数
            $("#op_retry_times").html(data.retry_strategy.op_retry_times);
            //操作异常重连时间间隔
            $("#op_retry_interval").html(data.retry_strategy.op_retry_interval);
        }else{
            $('.op_retry_div').hide();
        }

        //任务自动重试
        $("#task_retry_flag").html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
        if(data.retry_strategy.task_retry_flag){
            $('.task_retry_div').show();
            //任务重连对象
            $('#task_retry_object').html(data.retry_strategy.task_retry_object == 1 ? LANG.UI_RETRY_FAILED_OBJS_IN_TASK : LANG.UI_RETRY_ALL_OBJS_IN_TASK);;
            //任务重连次数
            $('#task_retry_times').html(data.retry_strategy.task_retry_times);
        }else{
            $('.task_retry_div').hide();
        }
        //任务重连时间间隔
        $('#task_retry_interval').html(parseInt(data.retry_strategy.task_retry_interval)/60 );


		//其他--------------------------------------------
		//进度条
		$('#total-progress').css({width: data.percent_progress});
		if(data.totalprogress == "--"){
			$('#total-progress').css({width: 0});
		}
        if(data.percent_progress == "0%"){
            $('#progressright').html("--");
        }else{
            $('#progressright').html(data.progress);
        }

        //恢复的时候完成了直接跳转
        // if(data.job_type == 35 && data.progress == "100.00%"){
        //     //备份
        //     if(data.timeStrategyBackupType == 2){
        //         //一次性备份
        //         UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
        //         setTimeout(function(){
        //             LOCATION('./content/platform/jobs/jobs.php');
        //         }, 3000);
        //     }

        // }else if(data.job_type == 36 && data.progress == "100.00%"){
        //     //恢复
        //     UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
        //     setTimeout(function(){
        //         LOCATION('./content/platform/jobs/jobs.php');
        //     }, 3000);
        // }

        initOpButton(data);
	}

    //按钮-------------------------------------------------
     //设置按钮是否可用
     var setControlBtn = function (id, available) {
        if (available){
            $("." + id).find('.btn').prop('disabled', false);
        } else {
            $("." + id).find('.btn').prop('disabled', true);
        }
    }
     //根据任务状态设置按钮权限
     var setBtnStatus = function (data) {

        //exchange恢复
        if (2 == data.job_type) {
            var button = "";
            button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
            button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
            $('#exchangeOpList').html(button);
        }
        setControlBtn('start', true);
        setControlBtn('startFull', true);
        setControlBtn('startIncr', true);
        setControlBtn('startDiff', true);
        setControlBtn('startFullList', true);
        setControlBtn('startIncrList', true);
        setControlBtn('startDiffList', true);
        var timeStrategy = data.time_strategy;
        switch (_taskStatus) {
            case 5:
                //停止中
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('startFullList', false);
                setControlBtn('startIncrList', false);
                setControlBtn('startDiffList', false);
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
                break;
            case 2:
            case 10:
            case 12:
                //运行、准备中停止中,禁用运行 12是启动中
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('startFullList', false);
                setControlBtn('startIncrList', false);
                setControlBtn('startDiffList', false);
                setControlBtn('start', false);
                setControlBtn('stop', true);
                break;
            case 4:
                //停止,禁用停止
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
                setControlBtn('start', true);
                setControlBtn('startIncr', true);
                setControlBtn('startDiff', true);
                setControlBtn('startIncrList', true);
                setControlBtn('startDiffList', true);
                setControlBtn('stop', false);
                break;
            case 19:
                //挂起
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('startFullList', false);
                setControlBtn('startIncrList', false);
                setControlBtn('startDiffList', false);
                setControlBtn('start', false);
                break;
            case 20:
                //挂起
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('startFullList', false);
                setControlBtn('startIncrList', false);
                setControlBtn('startDiffList', false);
                setControlBtn('start', false);
                setControlBtn('stop', false);
                break;
            default:
                //其他状态,开启控制
                setControlBtn('start', true);
                setControlBtn('startIncr', true);
                setControlBtn('startDiff', true);
                setControlBtn('startIncrList', true);
                setControlBtn('startDiffList', true);
                setControlBtn('stop', true);
                break;
        }
        for (var i = 0; i < timeStrategy.length; i++) {
            if (timeStrategy[i].mode == 2) {
                setControlBtn('startDiff', false);
                setControlBtn('startDiffList', false);
            } else if (timeStrategy[i].mode == 3) {
                setControlBtn('startIncr', false);
                setControlBtn('startIncrList', false);
            }
            //如果为一次性备份 ,禁用增量和差异
            if (timeStrategy[i].type == 4) {
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('startIncrList', false);
                setControlBtn('startDiffList', false);
            }
            if(timeStrategy[i].mode == 9){
                //如果是永久增量则屏蔽差异
                setControlBtn('startDiff', false);
                setControlBtn('startDiffList', false);
            }
        }
    }
    var initOpButton = function (data) {
        setBtnStatus(data);
        //任务详情按钮
        $('.start').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                startJobUnify(1);
            })

        });
        $('.startFull').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                startJobUnify(1);
            })

        });
        $('.startIncr').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                startJobUnify(2);
            })

        });
        $('.startDiff').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                startJobUnify(3);
            })

        });

        $('.stop').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            checkOperateAuth({type: 1,
                user_uuid: _origin_data.user_uuid,
                auth: "current_job",
            },function(){
                if(_origin_data.job_type==CONF.TASK_TYPE.OS_RECOVERY){
                    bootbox.prompt({
                        title: LANG.UI_PUBLIC_RECOVERY_STOP_TASK_TITLE,
                        inputType: 'password',
                        callback: debounce(function (result) {
                            if(result == null) return;
                            let encrypt = new JSEncrypt();
                                encrypt.setPublicKey(CONF.PUBLIC_KEY);
                            pAjaxRequest({password: encrypt.encrypt(result)}, `/api/v1/users/check/password`, `POST`, res => {
                                if (res.success) {
                                    opJob('stopJob');
                                    $(this).modal('hide');
                                } else {
                                    UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_PASSWORD_SUBMIT, LANG.UI_GLOBAL_STRATEGY_PASSWORD_SUBMIT_FAILD);
                                }
                            });
                        },300,false)
                    });
                }else{
                    opJob('stopJob');
                }
            })

        });
        var opJob = function (funName) {
            // Metronic.blockUI({target: '#jobDetail', animate: true});
            var job_uuid = $('#task_uuid').val();
            if (funName == 'stopJob') {
                pAjaxRequest({}, "/api/v1/jobs/stop/" + job_uuid + "", "POST",function (res) {
                    // Metronic.unblockUI('#jobDetail');
                    var op = LANG.UI_MACHINE_OS_STOP_JOB;
                    if (operateResponseList(res, op)) {
                    }
                });
            }
        }

        //启动任务
        var startJobUnify = function (type) {
            var params = {
                'start_type': type
            };
            var job_uuid = $('#task_uuid').val();
            // Metronic.blockUI({target: '#jobDetail', animate: true});
            pAjaxRequest(params, "/api/v1/jobs/start/" + job_uuid + "", 'POST', function (data) {
                // Metronic.unblockUI('#jobDetail');
                var op = LANG.UI_JOB_START;
                if (operateResponseList(data, op)) {
                }

            });
        }
    }






    //任务流量----------------------------------------------
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
                clearTimeout(timerTask.MachineOsSpeed);
                return;
            }
            pAjaxRequest(p, "/api/v1/complete_machine_os/jobs/speed", "GET", function (result) {
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
            timerTask.MachineOsSpeed = setTimeout(update, updateInterval);
        }

        update();

        window.onresize = function () {
            myChart.resize();
        }

    }


	  //运行日志---------------------------------------------
	  //初始化日志表格
      const initLogGrid = function(){ 
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
	}

	//主机列表---------------------------------------------
	var initHostGrid = function () {
        //表格option

        var jobs_uuid = $("#task_uuid").val();
        var job_type = $("#task_type").val();
        // 存储ztree配置和数据
        let ztreeDetails, ztreeId, zTreeSetting, zTreeData;
        var options = {
            detailFormatter: function (row, data, $element) {
                Metronic.blockUI({
                    target: '#hostlist',
                    animate: true
                });
                //获取磁盘信息
                let ztree_id = data.agent_uuid+'_ztree';
                let disk_name = job_type == 35 ? LANG.UI_MACHINE_OS_BACKUP_DISK : LANG.UI_MACHINE_OS_SOURCE_DISK;
                let des = '';
                //初始化为一个树形结构
                ztreeId = ztree_id;
                zTreeData = data.disk_info_list;

                //获取脚本
                let script_list = data.script_list;
                //获取备份前脚本
                let before_task_script = script_list.before_task_script;
                //获取备份后脚本
                let after_task_script = script_list.after_task_script;
                let before_script_des = job_type == 35 ? LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW +': ' : "";
                let after_script_des = job_type == 35 ? LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW+': ' : LANG.UI_MACHINE_OS_SCRIPT_AFTER_RECOVERY+': ';
                if(job_type == 35){
                    if(before_task_script.length == 0){
                        before_script_des += LANG.UI_PUBLIC_NOTHING;
                    }else{
                        for(let i = 0; i < before_task_script.length; i++){
                            let info = 'value_agent_uuid="'+data.agent_uuid+'"'+'value_script_name="'+before_task_script[i]+'"' +'value_script_position="'+1+'"';
                            before_script_des += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+'<a class="script_show" '+info+'>' + before_task_script[i] + '</a>' + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                    }
                }

                if(after_task_script.length == 0){
                    after_script_des += LANG.UI_PUBLIC_NOTHING;
                }else{
                    for(let i = 0; i < after_task_script.length; i++){
                        let info = 'value_agent_uuid="'+data.agent_uuid+'"'+'value_script_name="'+after_task_script[i]+'"' +'value_script_position="'+2+'"';
                        after_script_des += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+'<a class="script_show" '+info+'>' + after_task_script[i] + '</a>' + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                }

                //获取传输网络
                let network_list_str = LANG.UI_K8S_TRANSFER_NET+":" + data.transport_network.network_str;

                des += before_script_des + '<br>' + after_script_des +'<br>' + network_list_str;

                if(job_type == 36){
                    //恢复显示时间点
                    des += '<br>'+LANG.UI_JOB_HIS_BAK_TIMEPOINT+'：' + data.timepoint_name + '<br>';

                }

                des += '<div><div>'+disk_name+':</div> <div id="'+ztree_id+'" class="ztree"></div></div><br>';

                $element.append(des);



                //
                if(data.disk_info_list == "" || data.disk_info_list.length == 0){
                    $("#"+ztree_id).html("--");

                }else{
                     //初始化成一棵树
                    var setting = {
                        check: {
                            enable: true,
                            nocheckInherit: false//true 表示 新加入子节点时，自动继承父节点 nocheck = true 的属性。false 表示 新加入子节点时，不继承父节点 nocheck 的属性。
                        },
                        view: {
                            nameIsHTML: true,
                        },
                        data: {
                            simpleData: {
                                enable: true
                            },
                            // key:{
                            // 	title: "title"
                            // }
                        },
                    };
                    zTreeSetting = setting;
                    // $.fn.zTree.destroy($("."+ztree_id));
                    ztreeDetails = $.fn.zTree.init($("#"+ztree_id), setting, data.disk_info_list);
                    ztreeDetails.expandAll(true);
                }
            //
                // return des;
                //3秒后执行后续代码
                setTimeout(() => {
                    Metronic.unblockUI('#hostlist');
                }, 2000);


            },
            searchInput: false,
            pagination: true,
            resizable: false,
            pageList: [5, 10, 25, 50],
            detailView: true,
            uniqueId: 'id',
            // pa:{},
            vin_url: "/api/v1/complete_machine_os/jobs/" + jobs_uuid + "/detail",
            vin_method: "GET",
            vin_params: function () {
                let params = {};
                params.search_val = $('#searchVal').val();
                return params;
            },
            onRefresh: function () {
                $("#hostlist").bootstrapTable('hideLoading');
                scroll = $("#hostlist").bootstrapTable('getScrollPosition');
            },
            onCheck: function(){
                hostChecked = $("#hostlist").bootstrapTable('getSelections');
            },
            onUncheck: function(){
                hostChecked = $("#hostlist").bootstrapTable('getSelections');
            },
            onUncheckAll: function(){
                hostChecked = $("#hostlist").bootstrapTable('getSelections');
            },
            onCheckAll: function(){
                hostChecked = $("#hostlist").bootstrapTable('getSelections');
            },
            onPostBody: (data) => {
                // if (null !== expandIndex) {
                //     $("#hostlist").bootstrapTable('expandRow', expandIndex);
                // }
                $("#hostlist").bootstrapTable('scrollTo', scroll);

                checkRecord();

                if (ztreeDetails) {
                    var expandedNodeIds = [];
                    var allNodes = ztreeDetails.transformToArray(ztreeDetails.getNodes());
                    // 获取之前的展开节点
                    allNodes.forEach(function(node) {
                        if (node.open) {
                            expandedNodeIds.push(node.id); // 保存展开状态的节点 ID
                        }
                    });
                    // 给节点对象重新配置open属性
                    zTreeData.forEach(function(node) {
                        if ($.inArray(node.id, expandedNodeIds) != -1) {
                            node.open = true;
                        } else {
                            node.open = false;
                        }
                    });
                    ztreeDetails = $.fn.zTree.destroy(ztreeId);
                    ztreeDetails = $.fn.zTree.init($("#"+ztreeId), zTreeSetting, zTreeData);
                }
                    //先解除绑定再初始化点击事件
                    $(".script_show").off("click").on("click",function(){
                        /**
                         * 获取脚本描述
                         * @param {string} scriptType
                         */
                        const getScriptDesByType = scriptType => {
                            scriptType = parseInt(scriptType);
                            switch (scriptType) {
                                case 1:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE1;
                                case 2:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE2;
                                case 3:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE3;
                                case 4:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE4;
                                case 5:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE5;
                                case 6:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE6;
                                case 7:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE7;
                                case 8:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE8;
                                case 9:
                                    return LANG.UI_PUBLIC_SCRIPT_TYPE9;
                                default:
                                    return '--';
                            }
                        };

                        /**
                         * 设置脚本抽屉内容
                         * @param {string} title
                         * @param {string} scriptType
                         * @param {string} scriptContent
                         */
                        const setScriptDrawerContent = (title, scriptType, scriptContent) => {
                            $('#scriptContentDrawer .drawer-title .name').html(title);
                            $('#scriptContentType').html(getScriptDesByType(scriptType));
                            $('#scriptContent').html(scriptContent);
                            $('#scriptContentDrawer').drawer('show');
                        };
                        Metronic.blockUI({
                            target: '#host',
                            animate: true
                        });
                        let requestData = {};
                        //获取当前点击的a标签的value_agent_uuid值
                        requestData.value_agent_uuid = $(this).attr('value_agent_uuid');
                        //value_script_name
                        requestData.value_script_name = $(this).attr('value_script_name');
                        //获取脚本位置
                        requestData.value_script_position = $(this).attr('value_script_position');
                        //获取任务uuid
                        requestData.value_task_uuid = $('#task_uuid').val();
                        pAjaxRequest(requestData, "/api/v1/complete_machine_os/get_script", "GET",function (res) {
                            Metronic.unblockUI('#host');
                            setScriptDrawerContent(res.data.script_name, res.data.script_type,res.data.script_content)
                        });
                    });



            },
            onExpandRow: (index) => {
                // if (null === expandIndex) {
                //     expandIndex = index;
                // } else if (index !== expandIndex) {
                //     $("#hostlist").bootstrapTable('collapseRow', expandIndex);
                //     expandIndex = index;
                // }




            },
            // onCollapseRow: () => {
            //     expandIndex = null;
            // },
            columns: [{
                // field: '',
                checkbox: true,
                sortable: false,
            },
            //     {
            //         field: 'id_num',
            //         title: "编号",
            //         sortable: false,
            //         align: 'center',
            // },
                {
                    field: 'name',
                    title: LANG.UI_PLATFORM_THIRD_MESSAGE_PUSH_SYSTEM_NAME,
                    sortable: false,
                    align: 'center',
                    width: 15,
                    widthUnit: '%'
            },
                {
                    field: 'task_type_str',
                    title: LANG.UI_MACHINE_OS_BACKUP_TYPE,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'total_size',
                    title: LANG.UI_OS_HOST_SIZE,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'current_object_valid_size',
                    title: LANG.UI_PUBLIC_VM_VALID_SIZE,
                    sortable: false,
                    align: 'center',
            },
			{
				field: 'current_object_transport_size',
				title: LANG.UI_PUBLIC_TRANSFER_SIZE,
				sortable: false,
				align: 'center',
			},
			{
				field: 'current_object_write_size',
				title: LANG.UI_JOB_HIS_REAL_SIZE,
				sortable: false,
				align: 'center',
			},
			{
				field: 'speed',
				title: LANG.UI_DB_AVE_SPEED,
				sortable: false,
				align: 'center',
			},
			{
				field: 'Transmission_progress',
				title: LANG.UI_TASK_AWS_TRANS_PROGRESS,
				sortable: false,
				align: 'center',
			},
                {
                    field: 'status',
                    title:  LANG.UI_PUBLIC_STATUS,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, data, row) {
                        return '<span class="label ' + getStatusLevelClass(data.status_value)[0] + '" >' + value + '</span>'
                    }
            },
            ],
        }
        if (0 == $('#task_uuid').size()) {
            clearTimeout(timerTask.machine_os_timetask);
            return;
        }
        if (!initGridFlag) {
            $('#hostlist').baseTableConfig().init(options);
            initGridFlag = true;
        } else {
            $('#hostlist').bootstrapTable('refresh');


        }
        timerTask.machine_os_timetask = setTimeout(initHostGrid, 3000);


        $('#searchSubmit').unbind().on('click',function (){
			$('#searchVal').val();
            $("#hostlist").bootstrapTable('refresh');
        })


        $('#searchVal').unbind().on('keyup',function (event) {
            if (event.key === 'Enter') {
                $("#hostlist").bootstrapTable('refresh');
            }
        });

		$('#searchVal').unbind().on('blur', function () {
			$(this).prop('placeholder',LANG.UI_HADOOP_SEARCH_BY_NAME);
            if($('#searchVal').val() == ""){
                $('#clearSearchBtn').addClass('hide');
            }else{
                $('#clearSearchBtn').removeClass('hide');
            }
		});

		$('#clearSearchBtn').unbind().on('click', function () {
			$('#searchVal').val('');
            $('#clearSearchBtn').addClass('hide');

            $("#hostlist").bootstrapTable('refresh');
		});



	}

    //记录勾选
    var checkRecord = function () {
        var checkArr = [];

        $.each( hostChecked, function (index) {
            checkArr.push(hostChecked[index].agent_uuid);
        });
        $('#hostlist').bootstrapTable('checkBy', {
            field: 'agent_uuid',
            values: checkArr
        })
    }



	//历史任务---------------------------------------------
	 //初始化历史任务表格
	 var initHistoryGrid = function () {
		var jobs_uuid = $("#task_uuid").val();
		var options = {
            searchInput: false,
            pagination: true,
            pageList: [5, 10, 25, 50],
            sortName:'start_time',
            sortOrder:'desc',
            detailView: true,
            detailFormatter: history_detail,
            vin_url: '/api/v1/jobs/' + jobs_uuid + '/history',
            vin_method: "GET",
            onRefresh: function () {
                $("#historytable").bootstrapTable('hideLoading');
            },
            columns: [
            //     {
            //     checkbox: true,
            //     sortable: false,
            // },
                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'job_type',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: true,
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
                    field: 'validate_size',
                    title: LANG.UI_PUBLIC_VM_VALID_SIZE,
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

		$('#historytable').baseTableConfig().init(options);


	 }

     $('a[data-toggle="tab"]').on('shown.bs.tab', function(event) {
        var target = $(event.target).attr('href'); // 获取当前激活的标签的href属性
        if (target === '#history') {
            $('#historytable').bootstrapTable('refresh');
        }
    });

     var history_detail = function (index, row, element){
         var html = '<table>'
         Metronic.blockUI({
            target: element,
            animate: true
        });
         pAjaxRequest({}, '/api/v1/jobs/history/' + row.job_id + '', 'GET', function (res) {
            Metronic.unblockUI(element);
            var data = res.data;
            if (!res.success) {
                $(element).append(LANG.UI_PUBLIC_NOTHING);
                return;
            }
            if (data.list.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
                let thead = `<tr>
                            <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                            <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                            <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                        </tr>`;

                let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                let tbody =
                    `<tr>
                        <td>` + LANG.UI_PUBLIC_ON + `</td>
                        <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                        <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                    </tr>`;
                    $(element).append(`<table>${thead}${tbody}</table>`);
                    return;
            }else{
                  //组装标题
                if (_origin_data.job_type == 35) { //os备份
                    html += `
                    <th width="20%">` + LANG.UI_OS_DETAILS_HOST_NAME + `</th>
                    <th width="10%">` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                    <th width="10%">` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                    <th width="10%">` + LANG.UI_OS_HOST_SIZE + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                    <th width="10%">` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                    <th width="10%">` + LANG.UI_VISUAL_RESULT + `</th>
                    <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                    `
                } else { //os恢复
                        html += `
                        <th width="10%">` + LANG.UI_OS_DETAILS_HOST_NAME + `</th>
                        <th width="10%">` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
                        <th width="10%">` + LANG.UI_SEARCH_TASK_TYPE + `</th>
                        <th width="10%">` + LANG.UI_JOB_TRANSFER_SPEED + `</th>
                        <th width="10%">` + LANG.UI_OS_HOST_SIZE + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_VM_VALID_SIZE + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_TRANSFER_SIZE + `</th>
                        <th width="10%">` + LANG.UI_JOB_HIS_REAL_SIZE + `</th>
                        <th width="10%">` + LANG.UI_VISUAL_RESULT + `</th>
                        <th width="10%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                        `
                    }
                //组装内容
                for (let i = 0; i < data.list.length; i++) {
                    if (_origin_data.job_type == 35) { //os备份
                        html += `<tr>
                            <td>` + data.list[i].display_name + `</td>
                            <td>` + data.list[i].backup_mode + `</td>
                            <td>` + data.list[i].transfer_speed + `</td>
                            <td>` + data.list[i].os_size + `</td>
                            <td>` + data.list[i].os_valid_size + `</td>
                            <td>` + data.list[i].transport_size + `</td>
                            <td>` + data.list[i].write_size + `</td>
                            <td>` + data.list[i].task_status + `</td>
                            <td>` + data.list[i].error_code_des + `</td>
                    </tr>`
                    } else { //os恢复
                        html += `<tr>
                            <td>` + data.list[i].display_name + `</td>
                            <td>` + data.list[i].timepoint + `</td>
                            <td>` + data.list[i].backup_mode + `</td>
                            <td>` + data.list[i].transfer_speed + `</td>
                            <td>` + data.list[i].os_size + `</td>
                            <td>` + data.list[i].os_valid_size + `</td>
                            <td>` + data.list[i].transport_size + `</td>
                            <td>` + data.list[i].write_size + `</td>
                            <td>` + data.list[i].task_status + `</td>
                            <td>` + data.list[i].error_code_des + `</td>
                    </tr>`
                    }
                }
            html += '</table>'
            $(element).append(html);



            }


        })
     }






	var initSwiper = function(){
		//先给swiper插件里面的元素加上class
		$('.swiper-detail').addClass('swiper');
		$('.swiper-detail').attr('style','overflow: hidden');
		$('.swiper-detail').find('ul.nav.nav-tabs ').addClass('swiper-wrapper');
		$('.swiper-detail').find('ul.nav.nav-tabs > li').addClass('swiper-slide widthauto');
		var mySwiper = new Swiper ('.swiper',{
			slidesPerView :'auto',
			freeMode: true,	//惯性滑动且不会贴合
            navigation: {
                nextEl: '.swiper-button-next_detail',
                prevEl: '.swiper-button-prev_detail',
				disabledClass: 'display-none',
            },
		})
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
                let chartHeight = $('.C-charts__body__speedchart').height();

                $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                myChart.resize();
            }, 300);
        });
    }

    return {
        //main function to initiate the module
        init: function () {
			initListener();
			// initSwiper();
			//获取基本信息
			initBasicInfo();
			initSpeed();
			initHostGrid();
			initLogGrid();
			initHistoryGrid();
            watchEchartSizeChange();
        }

    };

}();

jQuery(document).ready(function() {
	MachineOSJobDetails.init();
});
