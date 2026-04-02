var OSJobDetails = function () {

	var logGrid;
	var  grid;
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

	var _BasicInfo = "";

	//初始化基本信息
	var initBasicInfo = function(){

		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.DBJobDetails_taskRunningInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getOSBasicInfo',p:data}, function(d){setBasicInfo(d, timerTask.DBJobDetails_taskRunningInfo)});
			timerTask.DBJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		data = JSON.parse(data);
		_BasicInfo = data;
		if(!data.flag){	//是否显示进度条
			clearTimeout(timeoutID);
			$('#total-progress').css({width: '100%'});
			$('#progressright').html('100%');
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function(){
				LOCATION('./content/platform/jobs/jobs.php','task');
			}, 5000);
		}


		//基本信息
		//--任务名
		$('#taskName').html(data.taskName);
		//--任务类型
		$('#taskType').html(data.taskType);
//		//--备份节点
//		$('#taskIP').html(data.db_hostname + "/" + data.db_ip);
		//--任务状态
		if(data.status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.status_num) + '" >' + data.status + '</span>');
		}
		//保存获取到的任务参数
		_taskStatus = data.status_num;
		jobParams.status = data.status_num;
		jobParams.taskType = data.task_type_num;
		//这里主机没有subModule  但是必须传值,后台没用到 ,这里传0;
		jobParams.subModule = 0;
		//--任务总容量
		$('#totalSize').html(data.totalSize);
		//--已处理容量
		$('#currentSize').html(data.currentSize);
		//--开始时间
		$('#startTime').html(data.startTime);
		//持续时间
		$('#intervalTime').html(data.intervalTime);
//		//结束时间
//		$('#endTime').html(data.endTime);
		//存储信息
		if(data.task_type_num==35){
			var node = data.storageInfo.node;
			var storage = data.storageInfo.storage;
			var high = data.storageInfo.high;
			var storageInfo = '';
			if(!storage){
				//没有存储信息,自动选择存储
				storageInfo = LANG.UI_JOB_AUTO_SELECT_STORAGE;
			}else{
				storageInfo = storage.name + "(" + storage.type + ")<br>";
				if(!storage.quotaFlag){
					storageInfo += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
					LANG.UI_JOB_FREE_SIZE + ":" + storage.freesize;
				}else{
					storageInfo += storage.quotades;
				}
			}
			$('#nodeinfo').html(node.name + "<br>" + node.ip);		//备份所用节点
//			$('#nodeReInfo').html(node.name + "<br>" + node.ip);	//恢复所用节点
			$('#storageinfo').html(storageInfo);
			// // 存储
			// if (data.storageInfo.node_pool_uuid) {
			// 	$('#nodepool').html(data.storageInfo.node_pool_nickname);
			// } else {
			// 	$('#nodepool').html(`--`);
			// }
			// if (data.storageInfo.storage_pool_uuid) {
			// 	$('#storagepool').html(data.storageInfo.storage_pool_nickname);
			// } else {
			// 	$('#storagepool').html(`--`);
			// }
			//--重复数据删除
			$('#deduplication').html(getFlagLevelInfo(high.deduplication));
			//--压缩
			$('#compressed').html(getFlagLevelInfo(high.compressed));
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
			$('#encryptStorage').html(getFlagLevelInfo(high.encrypt_flag));
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
			//自动密码
			$('#passwordAuto').html(getFlagLevelInfo(high.password_auto_flag));

		}
		//--限速策略
		$('#speedlimit').html(data.speed_limit.value);
		$('#speedlimit').prop('title', data.speed_limit.des);
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
			$('#taskPriority').html(task_priority);
		}else{
			$('.taskPriorityDiv').hide();
		}
		// let reconnect_times = data.transportStrategy.reconnect_times + LANG.UI_VOL_CDP_BACKUP_TIMES;
		// let reconnect_interval = data.transportStrategy.reconnect_interval + LANG.UI_PUBLIC_SECOND;
		// // 重连次数
		// if(parseInt(data.transportStrategy.reconnect_times) == 0){
		// 	$("#reconnectTimes").html(LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE);
		// }else{
		// 	$('#reconnectTimes').html(reconnect_times);
		// }
		// // 重连时间间隔
		// $('#reconnectInterval').html(reconnect_interval);

		//进度条
		$('#total-progress').css({width: data.totalprogress});
		if(data.totalprogress == "--"){
			$('#total-progress').css({width: 0});
		}
		$('#progressright').html(data.progress);
		//--线程数
		$('#threadCount').html(data.highInfo.thread_num);
		//自动生成密码
		if(data.storageInfo.flag && data.storageInfo.high.encrypt_flag){
			$(".passwordAutodiv").show();
		}else{
			$(".passwordAutodiv").hide();
		}

		//传输网络
		if(data.networkFlag){
			let networkDes = ``;
			if (data.transportStrategy.network_pool_uuid) {
				networkDes += data.transportStrategy.network_pool_nickname + '<br>';
			}
			if (data.transportStrategy.network_uuid) {
				networkDes += data.transportStrategy.network + '<br>';
			}
			if (networkDes) {
				$("#transferNetwork").html(networkDes);
			} else {
				$("#transferNetwork").html(LANG.UI_NODE_NETWORK_MODE_AUTO_MATCH);
			}
			$('.transferNetworkdiv').show();
		}else{
			$('.transferNetworkdiv').hide();
		}

		//--传输加密
		$("#encryptStrategy").html(getFlagLevelInfo(data.transportStrategy.encrypt));
		// 传输加密算法
		if(data.transportStrategy.encrypt){
			$('.transfer-encrypt-method-div').show();
			let encryptMethod = data.transportStrategy.encrypt_method;
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if(encryptMethod == 2){
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$('#transferEncryptMethod').html(method);
		}else{
			$('.transfer-encrypt-method-div').hide();
		}
		//--重建分区(只有恢复有)
		$("#builtSize").html(getFlagLevelInfo(data.reparted_flag));

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
            $('#task_retry_object').html(data.retry_strategy.task_retry_object == 1 ? '仅重试任务中失败的对象' : '重试任务中所有的对象');
            $('#task_retry_times').html(data.retry_strategy.task_retry_times);
            $('#task_retry_interval').html(data.retry_strategy.task_retry_interval / 60);
        } else {
            $('.task-retry-form-item').hide();
        }
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));

		if(35 == data.task_type_num){//为主机备份时
			//备份策略
			//--创建/修改时间
			$('#createTime').html(data.createTime);
			//--下次开始时间
			$('#nextTime').html(data.nextTime);
			var timeStrategy = getTimeStrategy(data.timeStrategy,data.timeStrategyBackupType);
			//--完全备份
			$('#fulldes').html(timeStrategy.full);
			//--增量备份
			$('#incdes').html(timeStrategy.incr);
			//--差异备份
			$('#diffdes').html(timeStrategy.diff);
			//--永久增量
			$('#pincrdes').html(timeStrategy.pincr);
			//--保留策略

			//磁带隐藏部分信息
			if (data.storageInfo.storage_uuid && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.TAPE) {
				//磁带获取策略替换保留策略
				$('.reserved_strategy_div .name').html(LANG.UI_GLOBAL_STRATEGY_NAME+":");
				$('.reserved_strategy_div .value').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
			} else {
				$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
			}


			//高级
			//获取有效数据
			$('#valid_data').html(getFlagLevelInfo(data.highInfo.valid_data_flag));
			//获取静默快照
			$('#silentSnapshot').html(getFlagLevelInfo(data.highInfo.silent_snapshot_flag));
			//CBT
			$('#CBT').html(getFlagLevelInfo(data.highInfo.cbt_flag));



			//显示
			//主机列表按钮
			// $('.osStartDiv').show();
			$('.os-table-toolbar').show();
			//主机列表提示
			$('#osbackuptips').show();
			//操作按钮
			$('.taskOperateDiv').show();
			//永久增量判断是否需要屏蔽
			$('.backup_time_strategy_div').show();
			if(data.storageInfo.storage_uuid && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.CLOUD){
				$('.permanent-increment-div').hide();
			}else{
				$('.permanent-increment-div').show();
			}
			//如果存储类型是磁带，需要屏蔽保留策略和传输线程
			if((data.storageInfo.storage_uuid && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.TAPE) || !CONF.FUNCTIONS.includes('multithread')){
				$('.threadCountDiv').hide();
			}else{
				$('.threadCountDiv').show();
			}
			//手动启动屏蔽相关时间策略
			switch (data.timeStrategyBackupType) {
				case 'strategy':
					$('#timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);
					break;
				case 'oncetime':
					$('#timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);
					break;
				default:
					$('#timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);
					$('.backup_time_strategy_div').hide();
					break;
			}
			// 在编排中的任务，显示按编排策略执行（原时间策略不生效）
			if (data.task_orchestration_plan_flag) {
				$('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
			}

			$('.recovery_style').hide();





		}else{
			initOSRecoverShow();
			//如果是windows操作系统恢复 引导修复不显示
			//--引导恢复（只有恢复有）
			if(data.osType ==  "1"){
				//引导恢复
				$('.recoverOsFlag').hide();
			}else{
				$('.recoverOsFlag').show();
				$("#recoverOS").html(getFlagLevelInfo(data.repair_linux_flag));
			}
			//如果存储类型是磁带，需要屏蔽保留策略和传输线程
			if(data.storage_type == CONF.BD_STORAGE_TYPE.TAPE || !CONF.FUNCTIONS.includes('multithread')){
				$('.threadCountDiv').hide();
			}else{
				$('.threadCountDiv').show();
			}
			// 恢复时间策略
			if (data.timeStrategy[0] && data.timeStrategy[0].type == 4) {
				$('#recovery_type').html(LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME);
				$('#start_time').html(data.timeStrategy[0].startTime);
			} else {
				$('#recovery_type').html(LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE);
				$('.start_time_form').hide();
			}

		}

		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE || !CONF.FUNCTIONS.includes('dedupication')){
			//如果是标准版本,隐藏重复数据删除和深度有效数据提取显示
			//如果是没有授权重复数据删除 也会隐藏
			$('.deduplicationdiv').hide();
		};

		initOpButton(data.task_type_num, data);
	}


	//如果是恢复的话 则部分不显示
	var initOSRecoverShow = function(){
		//操作按钮
		$('.taskOperateDiv').hide();
		$('#storageli').hide();
//		$('#strategyli').hide();
		$('.storageDiv').hide();
		$('.backup_style').hide();
		//主机列表按钮
		$('.os-table-toolbar').hide();
		//主机列表提示
		$('#osbackuptips').hide();
		//重建分区
		$('.builtFlag').show();
		//隐藏获取有效数据
		$('.validDatadiv').hide();
		//隐藏静默快照
		$('.silentSnapshotdiv').hide();
		//隐藏CBT
		$('.cbtdiv').hide();


	}





	//初始化操作按钮
	var initOpButton = function(taskType, data){
		if(!initButFlag){
			var opCode = [];
			//主机
			if(taskType == 35){//备份
				opCode = [10,7,6,2];
			}else{//恢复
				opCode = [1, 2];
			}

			var button = "";
			$.each(opCode, function(i, d){
				switch(d){
					case 1:
						//启动任务
						button += '<li class="startTask"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
						break;
					case 2:
						//停止
						button += '<li class="stopTask"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
						break;
					case 6:
						//启用差异
						button += '<li class="startDiffTask"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_differentia_backup me-4"></i> ' + LANG.UI_JOB_START_DIFFRENCE + '</button></li>';
						break;
					case 7:
						//启用增量
						button += '<li class="startIncrTask"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_increment me-4"></i> ' + LANG.UI_JOB_START_INCREMENT + '</button></li>';
						break;
					case 8:
						//启用策略
						button += '<li class="startStraTask"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_time_point me-4"></i> ' + LANG.UI_JOB_START_STRATEGY + '</button></li>';
						break;
					case 10:
						//启用完备
						button += '<li class="startTask"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START_FULL + '</button></li>';
						break;

				}
			});

			$('#osOpList').html(button);
			initButFlag = true;
			initListener();
		}
		//初始化操作按钮
		setBtnStatus(data);
		//搜索按钮
		$(".search-btn").unbind().on('click', function(){
			if ($('.os-detail-search').val().trim() == "") {
				return;
			}
    		grid.getRefresh(getParams());
    	});
		//清除搜索
		$(".clear").unbind().on('click', function(){
    		$('.os-detail-search').val("");
			grid.getRefresh(getParams());
    	});
		// 输入框 keyup 和 input 事件监听输入内容变化
		$('.os-detail-search').on('input focus', function () {
		    // 如果输入框有内容，或者获得了焦点（即使内容为空）
		    if ($(this).val().trim() !== '' || $(this).is(':focus')) {
		        $('.clear').removeClass('hide');
		    } else {
		        $('.clear').addClass('hide');
		    }
		});
		// 当输入框失去焦点时，如果内容为空，可以隐藏 clear 按钮
		$('.os-detail-search').on('blur', function () {
		    if ($(this).val().trim() === '') {
		        $('.clear').addClass('hide');
		    }
		});
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
			case 19:
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

	//得到开启和关闭的HTML内容
	var getFlagLevelInfo = function(flag){
		var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
		if(!flag || flag == 0 || flag == 2){
			html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		}
		return html;
	}

	//得到时间策略描述信息
	var getTimeStrategy = function(msg, timeStrategyBackupType){
		var timeInfo = {full:LANG.UI_PUBLIC_NOTHING, incr:LANG.UI_PUBLIC_NOTHING, diff:LANG.UI_PUBLIC_NOTHING, log:LANG.UI_PUBLIC_NOTHING, pincr:LANG.UI_PUBLIC_NOTHING};
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
				if (timeStrategyBackupType == "strategy") { //按策略备份才显示完备补偿
					if(strategy.full_backup_compensation_flag){
						des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_ON;
					} else {
						des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_OFF;
					}
				}
				timeInfo.full = des;
			}else if(2 == strategy.mode){
				timeInfo.incr = des;
			}else if(3 == strategy.mode){
				timeInfo.diff = des;
			}else if(4 == strategy.mode){
				timeInfo.log = des;
			}else if(9 == strategy.mode){
				timeInfo.pincr = des;
			}
		}
		return timeInfo;
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

	var getModeDes = function(mode){
		var des = '';
		if(1 == mode){
			des = LANG.UI_STRATEGY_FULL;
		}else if(2 == mode){
			des = LANG.UI_STRATEGY_INCREMENT;
		}else if(3 == mode){
			des = LANG.UI_STRATEGY_DIFFRENCE;
		}
		return des;
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

	//得到保留策略描述信息
	var getReservedStrategy = function(msg){
		var reservedStr = '';
		if(!msg){
			reservedStr = LANG.UI_PUBLIC_NOTHING;
			return reservedStr;
		}
		// 保留类型
		reservedStr += LANG.UI_RESERVE_RETENTION_TYPE+": ";
		if(CONF.RESERVE_STRATEGY_MODE.POINT == msg.strategyMode){
			reservedStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT+';';
		}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == msg.strategyMode){
			reservedStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN+';';
		}
		reservedStr += "<br>"+LANG.UI_RESERVE_RETENTION_MODE+": ";
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			reservedStr += LANG.UI_BACKUP_NUM;
            reservedStr += "<br>"+LANG.UI_STRATEGY_VALUE+": " + msg.value;
		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr += LANG.UI_BACKUP_DAY;
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
                reservedStr += "<br>"+LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY+": " + msg.value;
            }else{
                reservedStr += "<br>"+LANG.UI_STRATEGY_VALUE+": " + msg.value;
            }
		}
		return reservedStr;
	}

	var getSpeedDes = function(value){
		if(value >= 1024){
			return Math.round(value * 100 / 1024) / 100 + " MB/s";
    	}else{
    		return value + "KB/s";
    	}
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
        	    tooltip : {
        	        trigger: 'axis',
        	        formatter: function (params, ticket, callback) {
        	        	var value = params[0].data;
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

        var updateInterval = 2000;
        var data = [], nowTime =[];

        var parseNum = function(num){
        	num = parseInt(num);
        	num = num >= 10 ? num : "0" + num;
        	return num;
        }

        var getShowTime = function(timeStamp){
        	var myDate = new Date(parseInt(timeStamp));
    		var date = myDate.toLocaleDateString();
    		var hours = myDate.getHours();
    		var minutes = myDate.getMinutes();
    		var seconds = myDate.getSeconds();
    		return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
        }

        //初始化任务进度曲线图
        var initTaskSpeed = function(d){

        	if(initChartFlag) return; //初始化了就直接返回

        	var serverTime = d.t * 1000;
        	for (var i = 100; i > 0; i--) {
                data.push(0);
                nowTime.push(getShowTime(serverTime -  i * 3000));
            }
        	option.series[0].data = data
        	option.xAxis.data = nowTime;
        	myChart.setOption(option);

        	initChartFlag = true;
        }
        var p = {};
    	p.uuid = $("#task_uuid").val();
    	p = JSON.stringify(p);
        function update(){
        	if(0 == $('#speedchart').size()){
        		clearTimeout(timerTask.VMJobDetails_speed);
        		return;
        	}
        	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getTaskSpeed',p:p}, function(d){
            	var dataNow = JSON.parse(d);
            	initTaskSpeed(dataNow);
                data.shift();
                data.push(dataNow.speed);
                option.series[0].data = data;

                nowTime.shift();
                nowTime.push(dataNow.nowTime);
                option.xAxis.data = nowTime;


            	myChart.setOption(option);
            })
            .complete(function() {timerTask.VMJobDetails_speed = setTimeout(update, updateInterval);});
        }
        update();

        window.onresize = function(){
        	myChart.resize();
        }

    }

	//得到日志的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			case 3:
				levelClass = "label-danger";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}

	//日志表格滚动到.. 并重新设置表格样式
	var scroll = function(scrollHeight){
		$('#log').find(".dataTables_scrollBody").scrollTop(scrollHeight);
		$('#logtable').find('tbody > tr > td').css({border: "0px solid #ddd"});
	}

	//设置全局的滚动高度
	var setScrollHeight = function(scrollTop){
		_scrollHeight = scrollTop;
	}

    //初始化日志表格
    const initLogGrid = function(){ 
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
	}


    //初始化历史任务表格
    var initHistoryGrid = function(){
    	var updateInterval = 10000;
    	var tabShowFlag = false;
    	var initFlag = false;
    	var grid = new Datatable();

    	var initHistoryRow = function(){
        	var data = grid.getDataTable().data();
        	var levelDiv = $('#historytable').find('tbody > tr');
        	var levelTrueDiv = [];
        	//这里先过滤掉.details
        	for(var i=0; i<levelDiv.length; i++){
        		if(!$(levelDiv[i]).hasClass('details'));
        		levelTrueDiv.push($(levelDiv[i]).find('td:eq(3)'));
        	}
    		for(var i=0; i<levelTrueDiv.length; i++){
    			setLevel(levelTrueDiv[i], data[i]);
    		}
    		$(".popovers").popover();
    		if(detailsInfoLog){
    			var tr = $('#historytable').find('tbody > tr');
    			var data = grid.getDataTable().data();
            	addDetails(tr[detailsIndexLog], data[detailsIndexLog]);
    			var openTr = $('#historytable').find('tbody > tr')[detailsIndexLog];
    			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
    		}
        }

    	var setLevel = function(div, data){
    		if(!data) return;
    		var labelClass = getLevelClass(data[9].level);
    		var content = '<span class="label ' + labelClass + '">' +
    			'<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="right" data-content="' +
    			data[9].popover + '" >'+ data[2] + '</a></span>';
    		$(div).html(content);
    	}
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [7, "desc"]
                ],
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() || !tabShowFlag){
        		clearTimeout(timerTask.DBJobDetails_historyGrid);
        		return;
        	}
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#task_uuid").val();
    			data = {m:CONF.M.JOB,f:'getOSDetailsHistory',p:data};
    			grid.setAjaxParam(data);
    	    	grid.init({src: $("#historytable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:initHistoryRow});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.DBJobDetails_historyGrid = setTimeout(init, updateInterval);
    	}

    	$('#historytable').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();

            var nTr = $(this).parents('tr')[0];
            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfoLog = null;
            }else{
            	//如果是收起的
            	$('#historytable').find('tr .details').parent().remove();
            	$('#historytable').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndexLog = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfoLog = $('#historytable').find('tbody tr .details').parents('tr')[0];
            }

            return;
        });

    	//添加详情信息
    	var addDetails = function(nTr, data){
    		if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="13">';
        	sOut += '<table class="detailstable">';
            sOut += getOSDetailsInfo(data[10]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}

    	//得到主机详情
    	var getOSDetailsInfo = function(data){
    		if(!data.info){
    			return LANG.UI_PUBLIC_NOTHING;
    		}

    		var getBackupDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
        		thead += '<th width="15%">' + LANG.UI_OS_DETAILS_HOST_NAME + '</th>';
        		thead += '<th width="10%">' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
        		thead += '<th width="10%">' + LANG.UI_DB_AVE_SPEED + '</th>';
        		thead += '<th width="10%">' + LANG.UI_OS_HOST_SIZE + '</th>';
        		thead += '<th width="10%">' + LANG.UI_PUBLIC_VM_VALID_SIZE + '</th>';
        		thead += '<th width="10%">' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>';
        		thead += '<th width="10%">' + LANG.UI_PUBLIC_REAL_SIZE + '</th>';
        		thead += '<th width="5%">' + LANG.UI_VISUAL_RESULT + '</th>';
        		thead += '<th width="20%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';

        		var tbody = '<tbody>';
        		for(var i=0; i<data.info.length; i++){
        			if(i%2 == 0){
        				tbody += '<tr role="row" class="odd">';
        			}else{
        				tbody += '<tr role="row" class="even">';
        			}
        			tbody += '<td>' + data.info[i].display_name+ '</td>';
        			tbody += '<td>' + data.info[i].backup_mode + '</td>';
        			tbody += '<td>' + data.info[i].transfer_speed + '</td>';
        			tbody += '<td>' + data.info[i].os_size + '</td>';
        			tbody += '<td>' + data.info[i].os_valid_size + '</td>';
        			tbody += '<td>' + data.info[i].transport_size + '</td>';
        			tbody += '<td>' + data.info[i].write_size + '</td>';
        			tbody += '<td>' + data.info[i].task_status + '</td>';
        			tbody += '<td>' + data.info[i].error_code_des + '</td>';


        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		var details = thead + tbody;
        		return details;
    		}

    		var getRecoveryDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
        		thead += '<th width="16%">' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>';
        		thead += '<th width="16%">' + LANG.UI_OS_DETAILS_IP + '</th>';
        		thead += '<th width="8%">' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>';
        		thead += '<th width="8%">' + LANG.UI_PUBLIC_REAL_SIZE + '</th>';
        		thead += '<th width="8%">' + LANG.UI_VISUAL_RESULT + '</th>';
        		thead += '<th width="8%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';
        		var tbody = '<tbody>';
        		for(var i=0; i<data.info.length; i++){
        			if(i%2 == 0){
        				tbody += '<tr role="row" class="odd">';
        			}else{
        				tbody += '<tr role="row" class="even">';
        			}
        			tbody += '<td>' + data.info[i].timepoint + '</td>';
        			tbody += '<td>' + data.info[i].agent_ip + '</td>';
        			tbody += '<td>' + data.info[i].transport_size + '</td>';
        			tbody += '<td>' + data.info[i].write_size + '</td>';
        			tbody += '<td>' + data.info[i].task_status + '</td>';
        			tbody += '<td>' + data.info[i].error_code_des + '</td>';
        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		var details = thead + tbody;
        		return details;
    		}
    		var getResourceLimitDetails = function(data){
				var thead = '<thead><tr role="row" class="heading">';
        		thead += '<th>' + LANG.UI_RESOURCE_LIMIT_CONFIG + '</th>';
        		thead += '<th>' + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + '</th>';
        		thead += '<th>' + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + '</th>';
        		thead += '</tr></thead>';

        		var tbody = '<tbody>';
				let resourceLimitingNodeConfig = data.info.resource_limiting_node_config;
                tbody +=
                `<tr role="row" class="odd">
                    <td>` + LANG.UI_PUBLIC_ON + `</td>
                    <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                    <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                </tr>`;
        		tbody += '<tbody>';

        		var details = thead + tbody;
        		return details;
			}
			if(data.info.resource_limiting_node_config){
				return getResourceLimitDetails(data);
			}else{
				if(35 == data.taskType){
					//备份
					return getBackupDetails(data);
				}else if(36 == data.taskType){
					//恢复
					return getRecoveryDetails(data);
				}
			}

    	}

    	$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
  		  e.target // newly activated tab
  		  e.relatedTarget // previous active tab
  		  if("#history" == e.target.hash){
  			  tabShowFlag = true;
  			  init();
  		  }
  		})
  		$('a[data-toggle="tab"]').on('hide.bs.tab', function (e) {
  			e.target // newly activated tab
  			e.relatedTarget // previous active tab
  			if("#history" == e.target.hash){
  				tabShowFlag = false;
  			}
  		})
    }

    var dbGridLoad = function(){
		if(detailsInfo){
			var openTr = $('#oslist').find('tbody > tr')[detailsIndex];
			$(openTr).after(detailsInfo);
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
    }
    //初始化主机表格
    var initOSGrid = function(){
    	var updateInterval = 10000;
    	var initFlag = false;
    	grid = new Datatable();
    	var dataTableOpt = {
    			"ordering": false,
                "paging":false,
                "info":false
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() ){
        		clearTimeout(timerTask.DBJobDetails_vmGrid);
        		return;
        	}
    		if(!initFlag){
    	    	// var data = {};
    			// data.uuid = $("#task_uuid").val();
    			data = {m:CONF.M.JOB,f:'getDetailsOS',p:getParams()};
    			grid.setAjaxParam(data);
    			grid.init({src: $("#oslist"), showDetail:true, onDataLoad:dbGridLoad, dataTable:dataTableOpt});
    	    	initFlag = true;
    		}else{
    			var select = grid.getSelectedRows();
    			osSelect = select;
    			var saveCheck = function(){
    				for(var i=0;i<osSelect.length;i++){
    					$("input[name=id"+osSelect[i]+"]").prop('checked', true);
        				$("input[name=id"+osSelect[i]+"]").parent().addClass('checked');
        			}
    			}
    			grid.getRefresh(getParams(), saveCheck, false);

    		}
    		timerTask.DBJobDetails_vmGrid = setTimeout(init, updateInterval);
    	}
    	init();
    	$('#oslist').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();

            var nTr = $(this).parents('tr')[0];

            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfo = null;
            }else{
            	//如果是收起的
            	$('#oslist').find('tr .details').parent().remove();
            	$('#oslist').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('#oslist').find('tbody tr .details').parents('tr')[0];
            }
            return;
        });

    	//添加详情信息
    	var addDetails = function(nTr, data){
    		if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="13">';
        	sOut += '<table>';

            sOut += getDBDetails(data[11]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}

    	var getDBDetails = function(data){
    		if(35 == data.task_type_num){
    			//主机备份
    			return getBackupOSDetails(data);
    		}else if(36 == data.task_type_num){
    			//主机恢复
    			return getRecoveryOSDetails(data);
    		}
    	}
    	//备份数据库详情
    	var getBackupOSDetails = function(data){
    		datas = data.msgVol;
//    		var details = "<tr><td>" + '备份分区' + ": " + data.msgVol + "</td>";
//    		details += "</tr>";
//    		return details;

			var thead = '<thead><tr role="row" class="heading">';
    		thead += '<th width="16%">' + LANG.UI_OS_DETAILS_VOL_NAME + '</th>';
    		thead += '<th width="16%">' + LANG.UI_OS_DETAILS_VOL_PATH + '</th>';
    		thead += '<th width="16%">' + LANG.UI_OS_DETAILS_VOL_SIZE + '</th>';
    		thead += '</tr></thead>';
    		var tbody = '<tbody>';
    		for(var i=0; i<datas.length; i++){
    			if(i%2 == 0){
    				tbody += '<tr role="row" class="odd">';
    			}else{
    				tbody += '<tr role="row" class="even">';
    			}
    			tbody += '<td>' + datas[i].name + '</td>';
    			tbody += '<td>' + datas[i].mount_path + '</td>';
    			tbody += '<td>' + datas[i].total_size +'</td>';
    			tbody += '</tr>';
    		}
    		tbody += '<tbody>';
    		var details = thead + tbody;
    		return details;
    	}

    	//恢复数据库详情
    	var getRecoveryOSDetails = function(data){
    		datas = data.msgVol;
//    		var details = "<tr><td>" + '备份分区' + ": " + data.msgVol + "</td>";
//    		details += "</tr>";
//    		return details;

			var thead = '<thead><tr role="row" class="heading">';
    		thead += '<th width="16%">' + LANG.UI_OS_DETAILS_VOL_SOURCE_NAME + '</th>';
    		thead += '<th width="16%">' + LANG.UI_OS_DETAILS_VOL_SOURCE_PATH + '</th>';
    		thead += '<th width="16%">' + LANG.UI_OS_DETAILS_VOL_SOURCE__TRANSFER_SIZE + '</th>';
    		thead += '</tr></thead>';
    		var tbody = '<tbody>';
    		for(var i=0; i<datas.length; i++){
    			if(i%2 == 0){
    				tbody += '<tr role="row" class="odd">';
    			}else{
    				tbody += '<tr role="row" class="even">';
    			}
    			tbody += '<td>' + datas[i].source_name + '</td>';
    			tbody += '<td>' + datas[i].source_mount_path + '</td>';
    			tbody += '<td>' + datas[i].transfer_size +'</td>';
    			tbody += '</tr>';
    		}
    		tbody += '<tr role="row" class="odd">';
    		tbody += '<td>' + LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT+data.timepoint +'</td>';
    		tbody += '<td>' + '</td>';
    		tbody += '<td>' + '</td>';
    		tbody += '</tr>';
    		tbody += '<tbody>';
    		var details = thead + tbody;
    		return details;
    	}


    	//设置主机列表中的按钮事件
    	$('#start').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                startOSJob(1);
            })

    	});
    	$('#startIncr').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                startOSJob(2);
            })

    	});
    	$('#startDiff').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                startOSJob(3);
            })

    	});

    	$('#deleteos').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                deleteOS();
            })
    	});

    var startOSJob = function(mode){
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_FILE_DETAILS_JOB_SELECT_HOST, LANG.UI_FILE_DETAILS_JOB_SELECT_HOST_YOUBACK);
		}
		var datatable = grid.getDataTable().data();
		var data = {};
		data.os_uuids = select;
		data.taskuuid = $("#task_uuid").val();
		data.backup_mode = mode;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startOSJob',p: p}, function(d){
			if(OPREL(d)){
    			grid.getRefresh({});
    		}
		});
	}

	var deleteOS = function(){
    	var select = grid.getSelectedRows();
    	//获取有多少条数据
    	var dataList = grid.getDataTable().rows();
    	if(select.length == dataList[0].length){
    		return UIToastr.showInfo(LANG.UI_FILE_DETAILS_JOB_SELECT_DELETE, LANG.UI_FILE_DETAILS_JOB_NO_ALL_DELETE);
    	}
    	if(!select.length){
			return UIToastr.showInfo(LANG.UI_FILE_DETAILS_JOB_SELECT_DELETE, LANG.UI_FILE_DETAILS_JOB_SELECT_HOST_TODEL);
		}
    	bootbox.confirm({
            title: LANG.UI_FILE_DETAILS_JOB_DEL_TASK_HOST,
            message: LANG.UI_FILE_DETAILS_JOB_DEL_TASK_HOST_CONFIRM,
            callback: function(r) {
                if(!r) return;
                submitDelete(select, grid);
            }
        });
    }

    var submitDelete = function(select, grid){
    	var data = {};
    	data.os_uuids = select;
    	data.taskuuid = $('#task_uuid').val();
    	var p = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:'deleteSelectOS', p: p}, function(d){
    		if(OPREL(d)){
    			grid.getRefresh({});
    		}
    	});
    }

}



	//根据任务状态设置按钮权限
    var setBtnStatus = function(data){
    	//任务级
    	setControlBtn('startTask', true);
		setControlBtn('startIncrTask', true);
		setControlBtn('startDiffTask', true);
		setControlBtn('startStraTask', true);
		setControlBtn('stopTask', true);
		//单独主机级
		setControlBtn('start', true);
		setControlBtn('startIncr', true);
		setControlBtn('startDiff', true);
		setControlBtn('startStra', true);
		setControlBtn('deleteos', true);
    	var timeStrategy = data.timeStrategy;
    	switch(_taskStatus){
	    	case 2:
	    		//运行中
	    		//任务级
	    		setControlBtn('startTask', false);
	    		setControlBtn('startIncrTask', false);
	    		setControlBtn('startDiffTask', false);
	    		setControlBtn('stopTask', true);
	    		//单主机级
	    		setControlBtn('start', false);
	    		setControlBtn('startIncr', false);
	    		setControlBtn('startDiff', false);
	    		setControlBtn('deleteos', false);
	    		break;
	    	case 5:
	    	case 10:
	    		//准备中停止中,禁用运行
	    		//任务级
	    		setControlBtn('startTask', false);
	    		setControlBtn('startIncrTask', false);
	    		setControlBtn('startDiffTask', false);
	    		setControlBtn('stopTask', true);
	    		//单主机级
	    		setControlBtn('start', false);
	    		setControlBtn('startIncr', false);
	    		setControlBtn('startDiff', false);
	    		setControlBtn('deleteos', false);
				//停止中状态，变为强制停止
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					if(CONF.TASK_STATUS.STOPPING == _taskStatus){
						$('.stopTask').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
					}
				}
	    		break;
	    	case 4:
	    		//停止,禁用停止
	    		//任务级
				$('.stopTask').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
	    		setControlBtn('startTask', true);
	    		setControlBtn('startIncrTask', true);
	    		setControlBtn('startDiffTask', true);
	    		setControlBtn('stopTask', false);
	    		//单主机级
	    		setControlBtn('start', true);
	    		setControlBtn('startIncr', true);
	    		setControlBtn('startDiff', true);
	    		setControlBtn('deleteos', true);

	    		break;
			case 19://挂起状态,只能进行停止操作
				setControlBtn('startTask', false);
				setControlBtn('startIncrTask', false);
				setControlBtn('startDiffTask', false);
				setControlBtn('stopTask', true);
				//单主机级
				setControlBtn('start', false);
				setControlBtn('startIncr', false);
				setControlBtn('startDiff', false);
				setControlBtn('deleteos', false);
			break;
    		default:
    			//其他状态,开启控制
    			//任务级
	    		setControlBtn('startTask', true);
	    		setControlBtn('startIncrTask', true);
	    		setControlBtn('startDiffTask', true);
	    		setControlBtn('stopTask', true);
	    		//单主机级
	    		setControlBtn('start', true);
	    		setControlBtn('startIncr', true);
	    		setControlBtn('startDiff', true);
	    		setControlBtn('deleteos', true);
    			break;
    	}
    	for(var i=0;i<timeStrategy.length; i++){
    		if(timeStrategy[i].mode == 2){
    			//任务级
        		setControlBtn('startDiffTask', false);
        		//单主机级
        		setControlBtn('startDiff', false);
        	}else if(timeStrategy[i].mode == 3){
        		//任务级
        		setControlBtn('startIncrTask', false);
        		//单主机级
        		setControlBtn('startIncr', false);
        	}
    		//如果为一次性备份 ,禁用增量和差异
			if(timeStrategy[i].type == 4){
    			setControlBtn('startIncrTask', false);
    			setControlBtn('startDiffTask', false);
    			setControlBtn('startIncr', false);
    			setControlBtn('startDiff', false);
    		}
			if(timeStrategy[i].mode == 2  || timeStrategy[i].mode == 9){
				setControlBtn('startDiff', false);
				setControlBtn('startDiffTask', false);
			}else if(timeStrategy[i].mode == 3){
				setControlBtn('startIncr', false);
				setControlBtn('startIncrTask', false);
			}
    	}
    }

	//设置按钮是否可用
    var setControlBtn = function(id, available){
    	if(available){
    		$("." + id).find('.btn').prop('disabled', false);
    		$("#" + id).find('.btn').prop('disabled', false);
    	}else{
    		$("." + id).find('.btn').prop('disabled', true);
    		$("#" + id).find('.btn').prop('disabled', true);
    	}
    }

    var initListener = function(){
    	$('.stopTask').unbind().on('click',function(){
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                stopJob();
            })
		});   //终止任务
    	$('.startTask').unbind().on('click',function(){
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                startJob();
            })
		}); //启动完备任务
    	$('.startIncrTask').unbind().on('click',function(){
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                startIncr();
            })
		}); //增量
		$('.startDiffTask').unbind().on('click',function(){
			checkOperateAuth({type: 1,
                user_uuid: _BasicInfo.user_uuid,
                auth: "current_job",
            },function(){
                startDiff();
            })
		});//差异
    }

    //停止
	var stopJob = function(){
		if($(this).find('.btn').prop('disabled')){
			return true;
		}
		opJob('stopJob');
	}

	var opJob = function(funName){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = 5;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
	}

    //启动完全
	var startJob = function(){
		if($(this).find('.btn').prop('disabled')){
			return true;
		}
		startJobUnify('startJob', 1);
	}

	//启动差异
	var startDiff = function(){
		if($(this).find('.btn').prop('disabled')){
			return true;
		}
		startJobUnify('startJob', 3);
	}
	//启动增量
	var startIncr = function(){
		if($(this).find('.btn').prop('disabled')){
			return true;
		}
		startJobUnify('startJob', 2);
	}

	//启动日志
	var startLog = function(){
		if($(this).find('.btn').prop('disabled')){
			return true;
		}
		startJobUnify('startJob', 4);
	}

	//启动任务
	var startJobUnify = function(funName, type){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = 5;
		data.startType = type;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
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

	//获取请求参数
	var getParams = function() {
		let data = {};
		data.uuid = $("#task_uuid").val();
		data.keyword = $(".os-detail-search").val().trim();
		return data;
	}


    return {
        //main function to initiate the module
        init: function () {
			// initSwiper();
        	initBasicInfo();
        	initSpeed();
            initOSGrid();
            initLogGrid();
            initHistoryGrid();
			watchEchartSizeChange();
        }

    };

}();

jQuery(document).ready(function() {
	OSJobDetails.init();
});
