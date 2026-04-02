var K8sJobDetails = function () {

    var logGrid;
    var initLogFlag = false;	//任务日志初始化标志
    var _scrollHeight = 0; //任务日志全局高度
    //控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
    var detailsIndex = 0,detailsInfo = null;
    //控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
    var detailsIndexLog = 0,detailsInfoLog = null;

    var initChartFlag = false; //任务曲线图初始化标志

    var osSelect = []; //主机选择

    var _taskStatus; //监控任务状态

    var jobParams = {}; //操作需要参数

    var initButFlag = false;

    var myChart;

    var refreshFlag = false;

    var task_uuid = $("#task_uuid").val();

    var job_type = parseInt($("#task_type").val());
    //56备份 57恢复

    var _BasicInfo = "";

    //k8s时间点type类型, 这个是由前端定义的展开层级的type类型,和后端没关系
    //集群object对象类型
	 var KUBE_OBJECT_TYPE ={
		KUBE_OBJECT_TYPE_UNKNOWN: 0,
		KUBE_OBJECT_TYPE_NAMESPACE: 1,
		KUBE_OBJECT_TYPE_APP: 2,
		KUBE_OBJECT_TYPE_PVC: 3,
		KUBE_OBJECT_TYPE_RESOURCE: 4,
		KUBE_OBJECT_TYPE_CLUSTER: 5,
	 }

     const TIME_BACKUP_STRATEGY_TYPE = {
        STRATEGY: 1,
        ONCETIME: 2,
        MANUAL: 3
    }


    //初始化展示与隐藏
    var initDisplay = function (){
        //得到任务类型
        if(job_type == 56){
            //如果是备份
            $(".backup_style").show();
            $(".recovery_style").hide();
            $(".current_object").html(LANG.UI_KUBE_CLUSTER_LABEL+"'"+_BasicInfo.cluster_name+"("+_BasicInfo.host+")'"+LANG.UI_KUBE_BACKING_UP_RESOURCES+": ");
        }else{
            //如果是恢复
            $(".backup_style").hide();
            $(".recovery_style").show();
            $(".current_object").html(LANG.UI_KUBE_CLUSTER_LABEL+"'"+_BasicInfo.cluster_name+"("+_BasicInfo.host+")'"+LANG.UI_KUBE_RESTORING_RESOURCES+": ");

        }

    }











//处理基本数据开始

    // //初始化基本信息
    // var initBasicInfo = function(){
    //     //得到任务uuid
    //     var requestBasicInfo = function (d){
    //         console.log(d);
    //         _BasicInfo = d.data;
    //         getBasicInfo(d.data,refreshFlag);
    //     }
    //     pAjaxRequest({}, "/api/v1/kubernetes/jobs/task/"+task_uuid+"/basicinfo", "GET", requestBasicInfo,true);
    // }


    var initBasicInfo = function(){
		var updateInterval = 3000;
		var init = function(){
			var requestBasicInfo = function (d){
                _BasicInfo = d.data;
                if(_BasicInfo.length == 0){
                    clearTimeout(timerTask.MachineOsJobDetails_taskRunningInfo);
                    UIToastr.showSuccess(LANG.UI_KUBE_PROMPT, LANG.UI_KUBE_RECOVERY_JOB_COMPLETED_REDIRECT);
                    setTimeout(function () {
                        LOCATION('./content/platform/jobs/jobs.php', 'task');
                    }, 3000);
                }
                initDisplay();//初始化展示与隐藏
                initRunListTable(); //初始化资源表格
                getBasicInfo(d.data,refreshFlag);
            }
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.MachineOsJobDetails_taskRunningInfo);
        		return;
        	}
			//初始化备份源
			pAjaxRequest({}, "/api/v1/kubernetes/jobs/task/"+task_uuid+"/basicinfo", "GET", requestBasicInfo,true);
			timerTask.MachineOsJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}


    //把拿到的数据装入进页面中
    //d为数据 refreshFlag为是否刷新部分数据 true为刷新
    var getBasicInfo = function(d,refreshFlag){
        //获取任务类型
        if(_BasicInfo.job_type == 57){
            //恢复
            $(".backup_div").hide();
            $(".recover_div").show();
        }else{
            $(".backup_div").show();
            $(".recover_div").hide();
        }

        if(refreshFlag){
            //任务状态
            if(_BasicInfo.job_status){
                $('#status').html('<span class="label ' + getStatusLevelClass(_BasicInfo.job_status)[0] + '" >' +  getStatusLevelClass(_BasicInfo.job_status)[1] + '</span>');
            }
           //任务总容量
           if(_BasicInfo.job_status == CONF.TASK_STATUS.RUNNING){
                $("#totalSize").html(_BasicInfo.total_size);
                //已处理容量
                $("#currentSize").html(_BasicInfo.complate_size);
            }else{
                $("#totalSize").html("----");
                //已处理容量
                $("#currentSize").html("----");
            }
            //开始时间
            $("#startTime").html(_BasicInfo.start_time);
            //持续时间
            $("#intervalTime").val(_BasicInfo.interval_time);
             //任务阶段
             $("#current_stage").html(_BasicInfo.current_stage_value);
            initOpButton(_BasicInfo);
            //进度条
            $('#total-progress').css({width: _BasicInfo.progress});
            if(_BasicInfo.progress == "--"){
                $('#total-progress').css({width: 50});
            }


        }else{
            if(_BasicInfo.length == 0) return;
            //任务名
            $("#taskName").html(_BasicInfo.job_name);
            //任务类型
            let taskTypeDes = "--";
            if(_BasicInfo.job_type == 56){
                taskTypeDes = LANG.UI_KUBE_BACKUP;
            }else{
                taskTypeDes = LANG.UI_KUBE_RECOVERY;
            }
            $("#taskType").html(taskTypeDes);
             //任务阶段
             $("#current_stage").html(_BasicInfo.current_stage_value);
            //任务状态
           //--任务状态
            if(_BasicInfo.job_status){
                $('#status').html('<span class="label ' + getStatusLevelClass(_BasicInfo.job_status)[0] + '" >' + getStatusLevelClass(_BasicInfo.job_status)[1] + '</span>');
            }
            //任务总容量
            if(_BasicInfo.job_status == CONF.TASK_STATUS.RUNNING){
                $("#totalSize").html(_BasicInfo.total_size);
                //已处理容量
                $("#currentSize").html(_BasicInfo.complate_size);
            }else{
                $("#totalSize").html("----");
                //已处理容量
                $("#currentSize").html("----");
            }

            //开始时间
            $("#startTime").html(_BasicInfo.start_time);
            //持续时间
            $("#intervalTime").html(_BasicInfo.interval_time);
            //存储相关
            if(_BasicInfo.storage_info.flag){
                var node = _BasicInfo.storage_info.node;
                var storage = _BasicInfo.storage_info.storage;
                var high = _BasicInfo.storage_info.high;
                var storage_info = '';
                if(!storage){
                    //没有存储信息,自动选择存储
                    storage_info = LANG.UI_JOB_AUTO_SELECT_STORAGE;
                }else{
                    storage_info = storage.name + "(" + storage.type + ")<br>";
                    if(!storage.quotaFlag){
                        storage_info += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
                            LANG.UI_JOB_FREE_SIZE + ":" + storage.free_size;
                    }else{
                        storage_info += storage.quotades;
                    }
                }
                $('#nodeinfo').html(_BasicInfo.storage_info.node_pool_nickname ?
                    _BasicInfo.storage_info.node_pool_nickname + ":<br/>" + node.name:
                    node.name);
                $('#storageinfo').html(_BasicInfo.storage_info.storage_pool_nickname ?
                    _BasicInfo.storage_info.storage_pool_nickname +":<br/>" + storage_info:
                    storage_info);
                //压缩存储
                $("#compressed").html(getFlagLevelInfo(high.compressed));
                //压缩等级
                // 压缩等级
                if (_BasicInfo.storage_info.high.compressed) {
                    var method = '';
                    switch (_BasicInfo.storage_info.high.compress_method) {
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
                    $('#compressed_method').html(method);
                } else {
                    $('.compressMethodDiv').hide();
                }



                //数据加密
                $("#encryptStorage").html(getFlagLevelInfo(high.encrypt_flag));
                // 存储加密算法
                if(high.encrypt_flag){
                    let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    if(high.encrypt_method == 2){
                        method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    }
                    $('.encrypt-method-div').show();
                    $('#encryptMethod').html(method);
                }else{
                    $('.encrypt-method-div').hide();
                }
                //数据块大小
                $("#block_size").html(high.blocksize);
                //自动密码
                $('#passwordAuto').html(getFlagLevelInfo(high.password_auto_flag));
            }

            //备份方式
            switch (_BasicInfo.timeStrategyBackupType) {
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
            if(_BasicInfo.task_orchestration_plan_flag){
                $('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
            }

            //创建/修改时间
            $("#createTime").html(_BasicInfo.create_time);

            //恢复时间策略
			if (_BasicInfo.job_type == 57 && _BasicInfo.time_strategy[0] && _BasicInfo.time_strategy[0].type == 4) {
                $("#time_recovery_strategy").html(LANG.UI_KUBE_RECOVER_SPECIFIED_TIME+"("+_BasicInfo.time_strategy[0].start_time+")")
			} else {
				$("#time_recovery_strategy").html(LANG.UI_KUBE_RECOVER_IMMEDIATELY)
			}

            //下次开始时间
            $("#nextTime").html(_BasicInfo.next_time);
            //完全备份
            var timeStrategy = getTimeStrategy(_BasicInfo.time_strategy,_BasicInfo.timeStrategyBackupType);
            $("#fulldes").html(timeStrategy.full);
            //增量备份
            $("#incdes").html(timeStrategy.incr);
            //保留策略
            $("#reservedStrategy").html(getReservedStrategy(_BasicInfo.reserve_strategy));
            //加密传输
            $("#encryptStrategy").html(getFlagLevelInfo(_BasicInfo.transport_strategy.encrypt_flag_value));
            // 传输加密算法
            if(_BasicInfo.transport_strategy.encrypt_flag_value){
                $('.transfer-encrypt-method-div').show();
                let encryptMethod = _BasicInfo.transport_strategy.encrypt_method;
                let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                if(encryptMethod == 2){
                    method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                }
                $('#transferEncryptMethod').html(method);
            }else{
                $('.transfer-encrypt-method-div').hide();
            }
            //传输网络
            if(_BasicInfo.transport_strategy.network != ""){
                $("#transport_network").html(_BasicInfo.transport_strategy.network);
                $('.transferNetworkdiv').show();
            }else{
                $('.transferNetworkdiv').hide();
            }


            //限速策略
            $('#speedlimit').html(_BasicInfo.speed_limit.value);
            $('#speedlimit').prop('title', _BasicInfo.speed_limit.des);
            //传输线程
            $("#threadCount").html(_BasicInfo.thread_num);
            //集群本地保留PVC快照数
            $("#pvc_copy").html(_BasicInfo.keep_snapshots);
            //忽略快照异常
            $("#ignoring_snapshot").html(getFlagLevelInfo(_BasicInfo.ignore_exception));
            //命名空间重定向
            if(_BasicInfo.ns_rename == null || _BasicInfo.ns_rename == ""){
                $("#cover_pvc").html(LANG.UI_KUBE_OVERRIDE_NAMESPACE);
            }else{
                let ns_rename_des = Object.entries(_BasicInfo.ns_rename)
                .map(([key, value]) => `${key}->${value}`)
                .join(';');
                $("#ns_redirect").html(ns_rename_des);
            }
            //安全策略
            if(_BasicInfo.safe_strategy.worm_flag){
                let wormDes = LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD+_BasicInfo.safe_strategy.worm_protection_time +LANG.UI_PUBLIC_UNIT_DAY;
                $("#worm").html(wormDes);
            }else{
                $("#worm").html(getFlagLevelInfo(_BasicInfo.safe_strategy.worm_flag));
            }
            // 完整性效验
            if(CONF.FUNCTIONS.includes('integrity')){
                if(_BasicInfo.job_type == 56){
                    //备份
                    $('#integrity').html($.fn.getCompleteDetectionBackupDes({
                        integrityCheckFlag: _BasicInfo.safe_strategy.integrity_check_flag,
                        integrityCheckConfig: _BasicInfo.safe_strategy.integrity_check_config,
                        isFullTimepointTitle: true,
                        showIncrErrorPolicy: true,
                        excludeTitleFlag: true,
                    }));
                }else{
                    $("#integrity").html(_BasicInfo.safe_strategy.integrity_check_config.recovery_error_policy_des);
                }
            }else{
                $(".integrity_div").hide();

            }

            //重试策略
            //网络重试次数
            $("#network_retry_times").html(_BasicInfo.retry_strategy.network_retry_times);
            //网络重连时间间隔
            $("#network_retry_interval").html(_BasicInfo.retry_strategy.network_retry_interval);
            //操作异常自动重试
            $("#op_retry_flag").html(getFlagLevelInfo(_BasicInfo.retry_strategy.op_retry_flag));
            if(_BasicInfo.retry_strategy.op_retry_flag){
                $('.op_retry_div').show();
                //操作异常重连次数
                $("#op_retry_times").html(_BasicInfo.retry_strategy.op_retry_times);
                //操作异常重连时间间隔
                $("#op_retry_interval").html(_BasicInfo.retry_strategy.op_retry_interval);
            }else{
                $('.op_retry_div').hide();
            }

            //任务自动重试
            $("#task_retry_flag").html(getFlagLevelInfo(_BasicInfo.retry_strategy.task_retry_flag));
            if(_BasicInfo.retry_strategy.task_retry_flag){
                $('.task_retry_div').show();
                //任务重连对象
                $('#task_retry_object').html(_BasicInfo.retry_strategy.task_retry_object == 1 ? LANG.UI_RETRY_FAILED_OBJS_IN_TASK : LANG.UI_RETRY_ALL_OBJS_IN_TASK);;
                //任务重连次数
                $('#task_retry_times').html(_BasicInfo.retry_strategy.task_retry_times);
            }else{
                $('.task_retry_div').hide();
            }
            //任务重连时间间隔
            $('#task_retry_interval').html(parseInt(_BasicInfo.retry_strategy.task_retry_interval)/60 );
            //忽略节点资源限制
            $("#ignoreResourceLimit").html(getFlagLevelInfo(_BasicInfo.ignore_resource_limiting_flag));






            //覆盖原PVC
            // $("#cover_pvc").html(_BasicInfo.hhhhh);
            //优先使用集群内本地快照
            $("#use_snapshoot").html(getFlagLevelInfo(_BasicInfo.local_snapshot_first));
            //跳过或覆盖资源
            switch(parseInt(_BasicInfo.skip_exists)){
                case 1:
                    $("#skip_resource").html(LANG.UI_KUBE_SKIP_EXISTING_RESOURCES);
                    break;
                case 2:
                    $("#skip_resource").html(LANG.UI_KUBE_SKIP_EXISTING_AND_UPDATED_RESOURCES);
                    break;
                case 3:
                    $("#skip_resource").html(LANG.UI_KUBE_OVERRIDE_EXISTING_RESOURCES);
                    break;
            }
            //解除工作负载节点限制
            //获取三个值
            let unset_affinity_des = "";
            if(_BasicInfo.job_type == 57){
                //恢复
                let unset_node_affinity = _BasicInfo.unset_affinity.unset_node_affinity
                let unset_node_name = _BasicInfo.unset_affinity.unset_node_name
                let unset_node_selector = _BasicInfo.unset_affinity.unset_node_selector
                if(unset_node_affinity ==1){
                    unset_affinity_des += LANG.UI_KUBE_REMOVE_NODE_AFFINITY+";<br>"
                }
                if(unset_node_selector ==1){
                    unset_affinity_des += LANG.UI_KUBE_REMOVE_NODE_SELECTOR+";<br>"
                }
                if(unset_node_name ==1){
                    unset_affinity_des += LANG.UI_KUBE_REMOVE_NODE_NAME+";<br>"
                }
                if(unset_node_affinity != 1 && unset_node_selector != 1 && unset_node_name != 1){
                    unset_affinity_des += LANG.UI_PUBLIC_NOTHING;
                }

            }


            $("#relieve_limit").html(unset_affinity_des);
            //Hook脚本
            $("#hook_script").html(_BasicInfo.hook_type);
            let hook_script_des = "";
            switch (parseInt(_BasicInfo.hook_type)){
                case 0:
                    hook_script_des = LANG.UI_PUBLIC_NOTHING;
                    break
                case 1:
                    hook_script_des = LANG.UI_KUBE_SH_SCRIPT;
                    break
                case 2:
                    hook_script_des = LANG.UI_KUBE_CUSTOM_SCRIPT;
                    break
            }
            $("#hook_script").html(hook_script_des);

            //进度条
            $('#total-progress').css({width: _BasicInfo.progress});
            if(_BasicInfo.progress == "--"){
                $('#total-progress').css({width: 0});
            }
            //自动生成密码
            if(_BasicInfo.storage_info.flag && _BasicInfo.storage_info.high.encrypt_flag){
                $(".passwordAutodiv").show();
            }else{
                $(".passwordAutodiv").hide();
            }
            // //初始化按钮
            initOpButton(_BasicInfo);
        }
    }

//------------得到时间策略的相关描述-----------------
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
            } else if (9 == strategy.mode) {
                timeInfo.pincr = des;
            }
        }
        return timeInfo;
    }

    var getEachStrategy = function(strategy){
        var desEach = '';
        desEach += strategy.startTime;
        //如果是英文版 需要加空格
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            desEach += " "; //策略开始时间
        }
        desEach += LANG.UI_STRATEGY_START + ", ";
        if(strategy.rollFlag){
            desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
        }else{
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }
        desEach += "<br>";
        return desEach;
    }

    //得到备份间隔描述
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


//------------------------------------------------------------------------
    //得到保留策略描述信息
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
//-------------------------------------------------------
    //得到开启和关闭的HTML内容
    var getFlagLevelInfo = function(flag){
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if(!flag || flag == 0 || flag == 2){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }
//-----------------------------------------------------
    // //初始化操作按钮
    // var initOpButton = function(taskType, data){
    //     if(!initButFlag){
    //         var opCode = [];
    //         //主机
    //         if(taskType == 35){//备份
    //             opCode = [10,7,6,2];
    //         }else{//恢复
    //             opCode = [1, 2];
    //         }

    //         var button = "";
    //         $.each(opCode, function(i, d){
    //             switch(d){
    //                 case 1:
    //                     //启动任务
    //                     button += '<li class="startTask"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
    //                     break;
    //                 case 2:
    //                     //停止
    //                     button += '<li class="stopTask"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
    //                     break;
    //                 case 6:
    //                     //启用差异
    //                     button += '<li class="startDiffTask"><a href="javascript:;" ><i class="viconfont vicon-ge_differentia_backup"></i> ' + LANG.UI_JOB_START_DIFFRENCE + '</a></li>';
    //                     break;
    //                 case 7:
    //                     //启用增量
    //                     button += '<li class="startIncrTask"><a href="javascript:;" ><i class="viconfont vicon-ge_increment"></i> ' + LANG.UI_JOB_START_INCREMENT + '</a></li>';
    //                     break;
    //                 case 8:
    //                     //启用策略
    //                     button += '<li class="startStraTask"><a href="javascript:;" ><i class="viconfont vicon-ge_time_point"></i> ' + LANG.UI_JOB_START_STRATEGY + '</a></li>';
    //                     break;
    //                 case 10:
    //                     //启用完备
    //                     button += '<li class="startTask"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START_FULL + '</a></li>';
    //                     break;

    //             }
    //         });

    //         $('#osOpList').html(button);
    //         initButFlag = true;
    //         // initListener();
    //     }
    //     //初始化操作按钮
    //     setBtnStatus(data);


    //     //任务详情按钮
    //     $('.startTask').unbind().on('click', function () {
    //         if($(this).find('.btn').prop('disabled')){
    //             return true;
    //         }
    //         startJobUnify(1);
    //     });
    //     // $('.startTask').unbind().on('click', function () {
    //     //     if($(this).find('.btn').prop('disabled')){
    //     //         return true;
    //     //     }
    //     //     startJobUnify(1);
    //     // });
    //     $('.startIncrTask').unbind().on('click', function () {
    //         if($(this).find('.btn').prop('disabled')){
    //             return true;
    //         }
    //         startJobUnify(2);
    //     });
    //     $('.startDiffTask').unbind().on('click', function () {
    //         if($(this).find('.btn').prop('disabled')){
    //             return true;
    //         }
    //         startJobUnify(3);
    //     });

    //     $('.stopTask').unbind().on('click', function () {
    //         if($(this).find('.btn').prop('disabled')){
    //             return true;
    //         }
    //         opJob('stopJob');
    //     });
    //     var opJob = function (funName) {
    //         // Metronic.blockUI({target: '#jobDetail', animate: true});
    //         var job_uuid = $('#task_uuid').val();
    //         if (funName == 'stopJob') {
    //             pAjaxRequest({}, "/api/v1/jobs/stop/" + job_uuid + "", "POST",function (res) {
    //                 // Metronic.unblockUI('#jobDetail');
    //                 var op = LANG.UI_MACHINE_OS_STOP_JOB;
    //                 if (operateResponseList(res, op)) {
    //                 }
    //             });
    //         }
    //     }

    //     //启动任务
    //     var startJobUnify = function (type) {
    //         var params = {
    //             'start_type': type
    //         };
    //         var job_uuid = $('#task_uuid').val();
    //         // Metronic.blockUI({target: '#jobDetail', animate: true});
    //         pAjaxRequest(params, "/api/v1/jobs/start/" + job_uuid + "", 'POST', function (data) {
    //             // Metronic.unblockUI('#jobDetail');
    //             var op = LANG.UI_JOB_START;
    //             if (operateResponseList(data, op)) {
    //             }

    //         });
    //     }




    // }

    // //根据任务状态设置按钮权限
    // var setBtnStatus = function(data){
    //     //任务级
    //     setControlBtn('startTask', true);
    //     setControlBtn('startIncrTask', true);
    //     setControlBtn('startDiffTask', true);
    //     setControlBtn('startStraTask', true);
    //     setControlBtn('stopTask', true);
    //     //单独主机级
    //     setControlBtn('start', true);
    //     setControlBtn('startIncr', true);
    //     setControlBtn('startDiff', true);
    //     setControlBtn('startStra', true);
    //     setControlBtn('deleteos', true);
    //     var timeStrategy = data.time_strategy;
    //     switch(_taskStatus){
    //         case 2:
    //             //运行中
    //             //任务级
    //             setControlBtn('startTask', false);
    //             setControlBtn('startIncrTask', false);
    //             setControlBtn('startDiffTask', false);
    //             setControlBtn('stopTask', true);
    //             //单主机级
    //             setControlBtn('start', false);
    //             setControlBtn('startIncr', false);
    //             setControlBtn('startDiff', false);
    //             setControlBtn('deleteos', false);
    //             break;
    //         case 5:
    //         case 10:
    //             //准备中停止中,禁用运行
    //             //任务级
    //             setControlBtn('startTask', true);
    //             setControlBtn('startIncrTask', false);
    //             setControlBtn('startDiffTask', false);
    //             setControlBtn('stopTask', false);
    //             //单主机级
    //             setControlBtn('start', false);
    //             setControlBtn('startIncr', false);
    //             setControlBtn('startDiff', false);
    //             setControlBtn('deleteos', false);
    //             //停止中状态，变为强制停止
    //             $('#stopTask').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
    //             break;
    //         case 4:
    //             //停止,禁用停止
    //             //任务级
    //             setControlBtn('startTask', true);
    //             setControlBtn('startIncrTask', true);
    //             setControlBtn('startDiffTask', true);
    //             setControlBtn('stopTask', false);
    //             //单主机级
    //             setControlBtn('start', true);
    //             setControlBtn('startIncr', true);
    //             setControlBtn('startDiff', true);
    //             setControlBtn('deleteos', true);
    //             $('#stopTask').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a>');
    //             break;
    //         default:
    //             //其他状态,开启控制
    //             //任务级
    //             setControlBtn('startTask', true);
    //             setControlBtn('startIncrTask', true);
    //             setControlBtn('startDiffTask', true);
    //             setControlBtn('stopTask', true);
    //             //单主机级
    //             setControlBtn('start', true);
    //             setControlBtn('startIncr', true);
    //             setControlBtn('startDiff', true);
    //             setControlBtn('deleteos', true);
    //             break;
    //     }
    //     for(var i=0;i<timeStrategy.length; i++){
    //         if(timeStrategy[i].mode == 2){
    //             //任务级
    //             setControlBtn('startDiffTask', false);
    //             //单主机级
    //             setControlBtn('startDiff', false);
    //         }else if(timeStrategy[i].mode == 3){
    //             //任务级
    //             setControlBtn('startIncrTask', false);
    //             //单主机级
    //             setControlBtn('startIncr', false);
    //         }
    //         //如果为一次性备份 ,禁用增量和差异
    //         if(timeStrategy[i].type == 4){
    //             setControlBtn('startIncrTask', false);
    //             setControlBtn('startDiffTask', false);
    //             setControlBtn('startIncr', false);
    //             setControlBtn('startDiff', false);
    //         }
    //         if(timeStrategy[i].mode == 2  || timeStrategy[i].mode == 9){
    //             setControlBtn('startDiff', false);
    //             setControlBtn('startDiffTask', false);
    //         }else if(timeStrategy[i].mode == 3){
    //             setControlBtn('startIncr', false);
    //             setControlBtn('startIncrTask', false);
    //         }
    //     }
    // }

    // //设置按钮是否可用
    // var setControlBtn = function(id, available){
    //     if(available){
    //         $("." + id).find('a').removeClass('disablebtn');
    //         $("#" + id).find('a').removeClass('disablebtn');
    //     }else{
    //         $("." + id).find('a').addClass('disablebtn');
    //         $("#" + id).find('a').addClass('disablebtn');
    //     }
    // }

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
        //恢复
        if (57 == data.job_type) {
            var button = "";
            button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
            button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
            $('#k8sOpList').html(button);
        }
        setControlBtn('start', true);
        setControlBtn('startFull', true);
        setControlBtn('startIncr', true);
        setControlBtn('startDiff', true);
        setControlBtn('startFullList', true);
        setControlBtn('startIncrList', true);
        setControlBtn('startDiffList', true);
        var timeStrategy = data.time_strategy;
        switch (data.job_status) {
            case 5:
                //停止中
                setControlBtn('start', false);
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
                user_uuid: _BasicInfo.user_uuid,
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
                user_uuid: _BasicInfo.user_uuid,
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
                user_uuid: _BasicInfo.user_uuid,
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
                user_uuid: _BasicInfo.user_uuid,
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
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                if(_BasicInfo.job_type==CONF.TASK_TYPE.KUBE_RECOVERY){
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

//处理基本数据结束

//------------------------正在备份资源----------------
var initCurrentResource = function(){
    let updateInterval = 3000;
    var requestFlowInfo = function(d){
       $(".progressDiv_value").html(d.data.current_object);
    }
    function update(){
        if (0 == $('.progressDiv_value').size()) {
            clearTimeout(timerTask.JobDetails_currentResource);
            return;
        }
        pAjaxRequest({job_uuid:task_uuid}, "/api/v1/kubernetes/current_object", "GET", requestFlowInfo,true);
        timerTask.JobDetails_currentResource = setTimeout(update, updateInterval);
    }
    update();
}




//------------------------初始化流量图----------------
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
//----------------------------------运行日志--------------------------------------------------

 //初始化日志表格
 const initLogGrid = function(){ 
    $('#runninglog').runningLog({
        job_uuid: $('#task_uuid').val()
    });
}

//-------------------------资源列表------------
    var initRunListTable = function (){
        var initSecondRunTable = function (datas){
            if(_BasicInfo.job_type == 56){
                //备份不能展开
                return;
            }
            if(datas['data']['total'] == 0){
                return LANG.UI_KUBE_NO_DATA;
            }
            //-----------pvc
            var tableContent = "";
            var tableList = datas['data']['rows'];
            $.each(tableList, function (index, value) {
                tableContent += "<tr>" +
                    "<td>"+value.name+"</td>" +
                    "<td>"+value.kind+"</td>" +
                    "<td>"+value.version+"</td>" +
                    "<td>"+value.group+"</td>" +
                    "</tr>";
            });



            //开始封装表格
            var table = "<table>" +
                "<tr>" +
                "<th>"+LANG.UI_KUBE_RESOURCE_NAME+"(name)</th>" +
                "<th>"+LANG.UI_KUBE_RESOURCE_TYPE+"(kind)</th>" +
                "<th>"+LANG.UI_KUBE_RESOURCE_VERSION+"(version)</th>" +
                "<th>"+LANG.UI_KUBE_RESOURCE_GROUP+"(group)</th>" +
                "</tr>" +tableContent+
                "</table>";
            //-----------资源

            return table;

        }


        //获取资源时根据备份类型不同获取不同的资源 路径也不同
        var vin_url = "";
        var display_name = LANG.UI_KUBE_NAME;
        var vin_params = {};
        vin_url = "/api/v1/kubernetes/jobs/"+task_uuid+"/resource";
        if(_BasicInfo.by_type == KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_NAMESPACE){
            display_name = LANG.UI_KUBE_APPLICATION_NAME;
            //resource_type 0 未知 1namespace 2 app 3 pvc 4资源 5集群资源
            vin_params.resource_type = [KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_NAMESPACE,KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_CLUSTER];
        }else{
            display_name = LANG.UI_KUBE_NAMESPACE;
            //resource_type 0 未知 1namespace 2 app 3 pvc 4资源 5集群资源
            vin_params.resource_type = [KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_APP,KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_CLUSTER];
        }

        //获取备份类型,备份没有展开项
        var backup_type = _BasicInfo.job_type
        var detailViewFlag = false;
        if(backup_type == 57){
            //恢复是有详请的
            detailViewFlag = true;
        }


        var options = {
            vin_url:vin_url,
            vin_method:"GET",
            vin_params: function () {
                return vin_params;
            },
            showExport: false,
            showColumns: false,
            resizable: false,
            pagination: true,
            pageList:[5,10,25,50],
            detailView: detailViewFlag,
            detailFormatter:function (row,data,div) {
                var runSecondTable;
                var initSecondTableRun = function(d){
                    runSecondTable =  initSecondRunTable(d);
                    return runSecondTable;
                }
                var dataList = {};
                dataList.jobs_uuid = task_uuid;
                //resource_type 0 未知 1namespace 2 app 3 pvc 4资源 5集群资源
                dataList.resource_type = [KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_RESOURCE];

                //是否获取全部资源(不获取全部资源,只获取特定命名空间或应用下的资源)
                // dataList.all_flag = false;
                if(_BasicInfo.by_type == 1){
                    dataList.app = data.app;
                    dataList.app_type = data.app_type;
                    dataList.namespace = "";
                }else{
                    dataList.app = "";
                    dataList.app_type = "";
                    dataList.namespace = data.namespace;
                }
                pAjaxRequest(dataList, "/api/v1/kubernetes/jobs/"+task_uuid+"/resource", "GET", initSecondTableRun,false);
                return runSecondTable;

            },
            // addTaskBtn: true,






            columns:[

                // {
                //     field: 'num',
                //     title: '编号',
                //     sortable: true,
                //     align: 'center',
                // },
                {
                    field: 'name',
                    title: LANG.UI_KUBE_NAME,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, row, index, field) {
                        if(row.type == KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_CLUSTER){
                            return "CLUSTER"
                        }
                        var type = _BasicInfo.by_type;
                        switch (type){
                            case KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_NAMESPACE:
                                return row.namespace;
                            case KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_APP:
                                return row.app;
                        }
                    }
                },
                {
                    field: 'type',
                    title: LANG.UI_KUBE_TYPE,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, row, index, field) {
                        switch (value){
                            case KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_NAMESPACE:
                                return LANG.UI_KUBE_NAMESPACE;
                            case KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_APP:
                                return LANG.UI_KUBE_APPLICATION;
                            case KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_PVC:
                                return LANG.UI_KUBE_PERSISTENT_VOLUME;
                            case KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_RESOURCE:
                                return LANG.UI_USER_GROUP_RESOURCE;
                            case KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_CLUSTER:
                                return LANG.UI_KUBE_CLUSTER_RESOURCES;
                        }
                    }
                },
                // {
                //     field: 'backup_type',
                //     title: '备份类型',
                //     sortable: true,
                //     align: 'center',
                // },
                {
                    field: 'exec_resources_count_total',
                    title: LANG.UI_KUBE_RESOURCE_COUNT,
                    sortable: false,
                    align: 'center',
                   formatter: function (value, row, index, field) {
                        let count = parseInt(row.exec_resources_count_total);
                        if(row.type == KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_APP && count != 0){
                            count = count -1;
                        }
                         return count;
                    }
                },
                {
                    field: 'exec_status',
                    title: LANG.UI_KUBE_DEPLOYMENT_STATUS,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, row, index, field) {
                        let labelHtml = "";
                        //获取当前任务的状态
                        if(_BasicInfo.job_status != CONF.TASK_STATUS.RUNNING) return "--";
                        switch (value){
                            case 0://未开始
                                labelHtml = '<span class="label label-sm label-default status-icon">' + LANG.UI_KUBE_NOT_STARTED + '</span>';
                                break;
                            case 1://进行中
                                labelHtml = '<span class="label label-sm label-info status-icon">' + LANG.UI_KUBE_IN_PROGRESS + '</span>';
                                break;
                            case 2://成功
                                labelHtml = '<span class="label label-sm label-success status-icon">' + LANG.UI_PUBLIC_SUCCESS + '</span>';
                                break;
                            case 3://失败
                                labelHtml = '<span class="label label-sm label-danger status-icon">' + LANG.UI_DATACENTER_FAILURE + '</span>';
                                break;
                        }

                        return labelHtml;

                    }
                },


            ],


        }
        $('#resource_table').baseTableConfig().init(options);
}
//------------------------PVC列表-----------------------
    var initPvcTable = function (){
        var options = {
            vin_url:"/api/v1/kubernetes/jobs/"+task_uuid+"/resource",
            vin_method:"GET",
            vin_params: function () {
                let params = {};
                //resource_type 0 未知 1namespace 2 app 3 pvc 4资源 5集群资源
                params.resource_type = [KUBE_OBJECT_TYPE.KUBE_OBJECT_TYPE_PVC];
                return params;
            },
            showExport: false,
            showColumns: false,
            resizable: false,
            detailView: false,
            pagination: true,
            pageList:[5,10,25,50],

            columns:[

                // {
                //     field: 'num',
                //     title: '编号',
                //     sortable: true,
                //     align: 'center',
                // },
                {
                    field: 'name',
                    title: LANG.UI_k8S_PVC,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'namespace',
                    title: LANG.UI_k8S_NAMESPACE,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'meta',
                    title: LANG.UI_KUBE_STORAGE_TYPE,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, row, index) {
                        return value.pvc_volume_mode;
                    },
                },
                {
                    field: 'meta',
                    title: LANG.UI_KUBE_CAPACITY_SIZE,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, row, index) {
                        return value.pvc_size_with_unit;
                    },
                },


            ],


        }
        $('#pvc_table').baseTableConfig().init(options);
    }

//------------------------历史任务-----------------------

    //接收表格数据生成二级表格样式
    var initSecondTable = function(datas){
        if(datas['data'] == "" || datas['data'] == undefined || datas['data'] == null){
            return LANG.UI_KUBE_NO_DATA;
        }
        //-----------pvc
        var tableContentPvc = "";
        var tableListPvc = datas['data']['pvc_list'];
        $.each(tableListPvc, function (index, value) {
            tableContentPvc += "<tr>" +
                "<td>"+value.pvc_name+"</td>" +
                "<td>"+value.pvc_namespace+"</td>" +
                "<td>"+value.volume_mode+"</td>" +
                "<td>"+value.pvc_size_des+"</td>" +
                "<td>"+value.status+"</td>" +
                "<td>"+value.status_desc+"</td>" +
                "</tr>";
        });
        if (tableContentPvc == "") {
            tableContentPvc = "<tr><td colspan='4' class='text-center'>" + LANG.UI_KUBE_NO_DATA + "</td></tr>";
        }

        //开始封装表格
        var pvcTable = "<table>" +
            "<tr>" +
            "<th>"+LANG.UI_KUBE_PVC_PERSISTENT_VOLUME+"</th>" +
            "<th>"+LANG.UI_k8S_NAMESPACE+"</th>" +
            "<th>"+LANG.UI_KUBE_STORAGE_TYPE+"</th>" +
            "<th>"+LANG.UI_KUBE_CAPACITY_SIZE+"</th>" +
            "<th>"+LANG.UI_VISUAL_RESULT+"</th>" +
            "<th style='width:20%;'>"+LANG.UI_PUBLIC_DESCRIPTION+"</th>" +
            "</tr>" +tableContentPvc+
            "</table>";
        //-----------资源
        var tableContentResource = "";
        var tableListResource = datas['data']['resource_list'];
        $.each(tableListResource, function (index, value) {
            tableContentResource += "<tr>" +
                "<td>"+value.resource_name+"</td>" +
                "<td>"+value.resource_object_type+"</td>" +
                "<td>"+value.resource_namespace+"</td>" +
                "<td>"+value.status+"</td>" +
                "<td>"+value.status_desc+"</td>" +
                "</tr>";
        });
        if (tableContentResource == "") {
            tableContentResource = "<tr><td colspan='4' class='text-center'>" + LANG.UI_KUBE_NO_DATA + "</td></tr>";
        }


        //开始封装表格
        var resourceTable = "<table>" +
            "<tr>" +
            "<th>"+LANG.UI_KUBE_NAME+"</th>" +
            "<th>"+LANG.UI_KUBE_TYPE+"</th>" +
            "<th>"+LANG.UI_KUBE_NAMESPACE+"</th>" +
            "<th>"+LANG.UI_VISUAL_RESULT+"</th>" +
            "<th style='width:20%;'>"+LANG.UI_PUBLIC_DESCRIPTION+"</th>" +
            "</tr>" +tableContentResource+
            "</table>";
        // Metronic.unblockUI('#history_table');
        return pvcTable+resourceTable;
    }


    var initHistoryTable = function (){
        var options = {


            //              pagination: true,
            //             url: "/api/v1/kubernetes/scripts",
            vin_url:"/api/v1/jobs/"+task_uuid+"/history",
            vin_method:"GET",

            // showExport: false,
            // showColumns: false,
            // resizable: false,
            detailView: true,
            sortName: 'start_time',
            sortOrder: 'desc',
            searchInput: true,
            pagination: true,
            pageList:[5,10,25,50],
            advanceSearch:{module:'fs2'},
            changeHeightBtn: true,


            detailFormatter:function (row,data,element) {
                var table = "";
                Metronic.blockUI({
                    target: element,
                    animate: true
                });
                // Metronic.blockUI({target: '#history_table', animate: true});
                var initSecondTableInside = function(d){
                    table =  initSecondTable(d);
                    Metronic.unblockUI(element);
                    element.append(table);
                    // return table;
                }
                var dataList = {};
                dataList.task_uuid = task_uuid;
                dataList.history_uuid = data.history_uuid;
                pAjaxRequest(dataList, "/api/v1/kubernetes/jobs/"+task_uuid+"/history/details", "GET", initSecondTableInside);

                // return table;

            },
            // addTaskBtn: true,





            columns:[

                {
                    field: 'num',
                    title: LANG.UI_PUBLIC_TABLE_ID,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'job_type',
                    title: LANG.UI_PUBLIC_TASK_TYPE,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'job_status_value',
                    title: LANG.UI_VISUAL_RESULT,
                    sortable: true,
                    align: 'center',
                    formatter: function (value, row, index, field) {
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
                    field: 'all_size',
                    title: LANG.UI_MICROSOFT365_ALL_SIZE,
                    sortable: true,
                    align: 'center',
                },
                // {
                //     field: 'validate_size',
                //     title: LANG.UI_PUBLIC_VM_VALID_SIZE,
                //     sortable: true,
                //     align: 'center',
                //     formatter: function () {
                //         return "--";
                //     }
                // },
                {
                    field: 'speed_size',
                    title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                    sortable: true,
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
        $('#history_table').baseTableConfig().init(options);
    }

    var watchEchartSizeChange = function() {
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

    var initListner = function(){
         //初始化切换事件

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(event) {
            var target = $(event.target).attr('href'); // 获取当前激活的标签的href属性
            if (target === '#runList') {
                $('#resource_table').bootstrapTable('refresh');
            }else if(target === '#pvcList'){
                $('#pvc_table').bootstrapTable('refresh');
            }else if(target === '#history'){
                $('#history_table').bootstrapTable('refresh');
            }
        });

    }



    return {
        //main function to initiate the module
        init: function () {

            initBasicInfo(); //初始化基本数据
            initSpeed();//初始化流量图
            initCurrentResource();//初始化正在备份的资源
            initLogGrid();//初始化运行日志

            initPvcTable();//初始化持久卷表格
            initHistoryTable();//初始化历史表格
            initListner();
            watchEchartSizeChange();
        }

    };

}();

jQuery(document).ready(function() {
    K8sJobDetails.init();
});
