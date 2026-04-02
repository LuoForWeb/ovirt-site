var VolCdpJobDetails = function (){
	var logGrid,_takeoverCutBackVolTab,_volGrid;
	var initLogFlag = false;	//任务日志初始化标志
	var _scrollHeight = 0; //任务日志全局高度
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndexLog = 0,detailsInfoLog = null;
	var initChartFlag = false; //任务曲线图初始化标志
	var vmSelect = []; //虚拟机选择
	var _taskStatus; 	//监控任务状态
	var _taskType; //任务类型
	var mychart;
	var _taskCurrentRunningStage=0; //监控任务当前阶段
	var jobParams = {}; //操作需要参数
	var initButFlag = false;
	var _memoryCacheSize,_fileCacheSize = 0;
	var _takeoverConf = []; //任务接管配置信息
	var _masterAgentuuid = ""; //当前任务主机UUID
	var _catBackVolSet = []; //回切卷及映射关系
	var _selectMapVol = [];
	var _chartTabSelected = false;_dataFlowSelected = false;
	var _doubleHostMirrorFlag = 2; //是否开启双机镜像
	var authFun = [];
	var _transPortIp ="";
	var _failbackCationIp = "";
	var isInit = true;
	var _agentNetworkInfo = [];  //主机客户端对应的网卡信息
	var _standbyNetworkInfo = []; //备机客户端对应的网卡信息
	var _takeoverIpMapList =[];
	var _takeoverIpMapListView = [];
	var _standbyGatewayConf = [];
	var ipMapInfoList = [];
	var _agentOsType = '';
	var _takeoverHisBusinessIpMap = [];  //接管任务网卡历史配置
	var _failbackHisBusinessIpMap = [];	 //回切任务网卡历史配置
	var _modifyFailbackHisBusinessIpMap = []; //修改回切任务网卡历史配置
	var _failbackHisStorageuuid = ""; //回切任务历史配置的历史存储
	var _failTransferCompressValue = 1;  //回切传输压缩默认值
	var _failTransferEncryptValue = 0;  //回切传输加密默认值
	var _autoTtakeoverEnableFlag = 0; //自动接管当前可用状态
	var _tempConsoleUrl = "";
	var _vmUuid = "";
	var _takeoverApplicationRol = false;  //是否是接管验证任务
	var _vmTempStatus = 0; //内嵌虚拟主机当前状态 0：未创建，1：运行中，3：暂停，5：关闭
	var _VMPrefixStatus = 0; //虚拟机预处理的状态 0:未知状态，1，预处理中，2：预处理完成
	var _takeoverAgentType = 0; //接管备机类型
	var vmHypervisorTypeEmbedQemuKvm = 108;  //容灾演练平台

	//初始化详情流量图和数据流向tab
	var initTabShowEvent = function(){
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	        var tab = e.target;
	        if(tab.hash == "#tab_chart"){
	        	$('#tab_chart').show();
				$('#tab_task_map').hide();
				_chartTabSelected = true;
				_dataFlowSelected = false;
				// 根据不同分辨率动态计算echart的高度和宽度
				var chartWidth = $('.portlet-charts__body__speedchart').width();
				var chartHeight = $('.portlet-charts__body__speedchart').height();
				$('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
				myChart.resize();
	        }else if(tab.hash == "#tab_task_map"){
	        	$('#tab_chart').hide();
				$('#tab_task_map').show();
				_chartTabSelected = false;
				_dataFlowSelected = true;
	        }
	    });
	}

	//初始化基本信息
	var initBasicInfo = function(){
		var updateInterval = 3000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.VolCdpJobDetailsRunningInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getVolCdpBasicInfo',p:data}, function(d){
				setBasicInfo(d, timerTask.VolCdpJobDetailsRunningInfo)
			});
			timerTask.VolCdpJobDetailsRunningInfo = setTimeout(init, updateInterval);
		}
		init();
		$('#takeoverScriptConfDetail').unbind('click').click(takeoverScriptConfDetail);
		$('#takeoverFailbackConf').unbind('click').click(takeoverFailbackupConf);
		$('.takeovernetworkconf').unbind('click').click(takeoverFailbackNetworkConf);
		// $('.removeAutotakeover').unbind('click').click(autoTakeoverEnableAndDisableConf);
		$('#taskTakeoverNetworkConf').unbind('click').click(taskTakeoverNetworkConf);
		$('.tempagentconfherf').unbind('click').click(getTempAgentConfigInfo);
		setTimeout(function(){
			getTaskNetCardHistoryConf();
		},1000 );

	}
	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		data = JSON.parse(data);
		var currentTaskRunningStageValue = data.current_task_running_stage_value;  //当前任务运行阶段值
		var currentTaskRunningStage = data.current_task_running_stage;  //当前任务运行阶段String
		//如果是自动接管 英文版这里需要改任务阶段名字
		if(data.auto_takeover_flag){
			if(currentTaskRunningStageValue == 60 ){ //接管中
				currentTaskRunningStage = LANG.UI_VOL_CDP_AUTO_TAKEOVER;
			}else if(currentTaskRunningStageValue ==  61){ //接管启动中
				currentTaskRunningStage = LANG.UI_VOL_CDP_AUTO_START_TAKEOVER;
			}else if(currentTaskRunningStageValue ==  62){ //接管停止中
				currentTaskRunningStage = LANG.UI_VOL_CDP_AUTO_STOP_TAKEOVER;
			}
		}
 		if(!data.flag){
			clearTimeout(timeoutID);
			$('#total-progress').css({width: '100%'});
			$('#progressright').html('100%');
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function(){
				LOCATION('./content/platform/jobs/jobs.php','task');
			}, 5000);
			return;
		}
		if(data.statusValue==CONF.TASK_STATUS.STOPPING){
			$('.taskOperateButton').attr('disabled',true);
		}else{
			$('.taskOperateButton').attr('disabled',false);
		}
		//任务阶段值不为0、任务状态不为停止、任务类型不为恢复
		if(currentTaskRunningStageValue!=0 && data.statusValue!=CONF.TASK_STATUS.STOPPED && data.taskTypeFlag!=CONF.TASK_TYPE.VOL_CDP_RECOVERY){
			$('#taskStage').html(data.taskType + '（<span class = "label '
				+ getRunningStageClass(data.statusValue,currentTaskRunningStageValue) +'" >' + currentTaskRunningStage+ '</span>）');
		}else{
			$('#taskStage').html(data.taskType);
		}
		if(data.status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.statusValue) + '" >' + data.status + '</span>');
		}
		var network = data.transportStrategy.network; //传输网络
		_transPortIp = data.transportStrategy.transport_ip;
		if(!!network){
			$('#transportNetworkInfo').html(network);  //传输网络
			$('#backupServerIp').html(_transPortIp);
			$('#backupServerInfo').html(_transPortIp);
		}else{
			$('#transportNetworkInfo').html(LANG.UI_PUBLIC_NOTHING);  //传输网络
		}
		//保存获取到的任务参数
		_taskStatus = data.statusValue;
		_taskCurrentRunningStage = currentTaskRunningStageValue;  //当前运行阶段
		_masterAgentuuid = data.master_agent_uuid;
		_masterOsType = data.os_type;

		jobParams.status = data.statusValue;
		jobParams.taskType = data.taskTypeFlag;
		jobParams.subModule = data.hypervisor;
		//基本信息
		parseTaskBasicInfo(data);
		totalpmgressbarDivControl(data);  //任务总进度条显示控制；
		$('#taskName').html(data.taskName);
		$('#taskName').attr('title', data.taskName);
		$('#currentCheckVolume').html(data.consistency_check_vol); //校验卷

		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);
		$('#endTime').html(data.endTime);

		$('#transportReconnectTimes').html(data.transportStrategy.reconnect_times);  //重连次数
		$('#transportReconnectIntrval').html(data.transportStrategy.reconnect_interval);  //重连间隔
		var transSpeed = data.speed;
		if(transSpeed=="--"){
			transSpeed="";
		}
		$('#transSpeedNoStandby').html(transSpeed);
		$('#transSpeedHostToBs').html(transSpeed);
		$('#transSpeedStandbyToHost').html(transSpeed);
		$('#transSpeedBsToStandby').html(transSpeed);
		//存储信息
		if(data.storageInfo.flag){
			parseTaskStorageInfo(data);
		}
		var file_cach_path = data.cache_info.file_cach_path;
		if(file_cach_path==""){
			file_cach_path = LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
		}
		//高级配置部分
		$('#transportThreadNum').html(data.thread_num);
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
		if(file_cach_path) {
			file_cach_path = file_cach_path.replace(/\\\\/g, "\\");
		}
		$('#volCdpHostFileCachePath').html(file_cach_path);  //文件缓存路径
		$('#volCdpHostFileCacheSize').html(data.cache_info.agent_file_cache_des);  //文件缓存使用情况
		$("#volCdpMemoryCache").html(getFlagLevelInfo(data.cache_info.memory_cache_flag));  //内存缓存
		$('#volCdpMemoryCacheSize').html(data.cache_info.agent_memory_cache_des);  //内存缓存使用情况


		_taskType = data.taskTypeFlag;
		initOpButton(data,data.auto_takeover_flag);
	};
	/**
	 * 解析任务存储信息
	 */
	var parseTaskStorageInfo = function(data){
		var node = data.storageInfo.node;
		var storage = data.storageInfo.storage;
		var high = data.storageInfo.high;
		var storageInfo = LANG.UI_JOB_AUTO_SELECT_STORAGE;
		if(storage){	//没有存储信息,自动选择存储
			storageInfo = storage.name + "(" + storage.type + ")<br>";
			if(!storage.quotaFlag){
				storageInfo += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
				LANG.UI_JOB_FREE_SIZE + ":" + storage.freesize;
			}else{
				storageInfo += storage.quotades;
			}
		}
		$('#nodeinfo').html(node.name + "<br>" + node.ip);		//备份所用节点
		$('#nodeReInfo').html(node.name + "<br>" + node.ip);	//恢复所用节点
		$('#storageinfo').html(storageInfo);
		if(high){
			$('#deduplication').html(getFlagLevelInfo(high.deduplication));
			$('#compressed').html(getFlagLevelInfo(high.compressed));

			$('#blocksize').html(high.blocksize);
			//显示数据加密
			$('.encryptStoragediv').show();
			$('.passwordAutodiv').hide();
			$("#transportMode").html(data.transportStrategy.mode);
			$('#encryptStorage').html(getFlagLevelInfo(high.encrypt_flag));
			if(high.password_auto_flag){
				$('.passwordAutodiv').show();
				$('#passwordAuto').html(getFlagLevelInfo(high.password_auto_flag));
			}
			//传输线程
			$('#transportThreadNum').html(data.thread_num+LANG.UI_PUBLIC_NUM);
			//传输数据包大小
			$('#transportPacketSize').html(data.transportStrategy.transport_block_size);
			let reconnect_times = data.transportStrategy.reconnect_times + LANG.UI_VOL_CDP_BACKUP_TIMES;
			let reconnect_interval = data.transportStrategy.reconnect_interval + LANG.UI_PUBLIC_SECOND;
			// 重连次数
			if(parseInt(data.transportStrategy.reconnect_times) == 0){
				$("#reconnectTimes").html(LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE);
			}else{
				$('#reconnectTimes').html(reconnect_times);
			}
			// 重连时间间隔
			$('#reconnectInterval').html(reconnect_interval);
		}
	}
	/**
	 * 任务基础信息默认展示状态
	 */
	var taskDefaultBasicInfo = function(){
		$(".takeoverTypeDiv").hide();
		$('.heartbeatFailureTimeView').hide();
		$(".doublehostmirrorview").hide();
		$(".doubleMachineImgHostDiv").hide();
		// $('.mirrordataisbackupview').hide();
		$('.currentcheckvolumeDiv').hide();
	}
	/**
	 * 传输选项卡根据任务类型显示或隐藏:接管任务和恢复需要隐藏的项
	 */
	var hideTransPortLable = function(data){
		debugger;
		var taskType = data.taskTypeFlag;
		var currentRunningStage = _taskCurrentRunningStage;
		//传输相关
		$('.transportModediv').hide();
		$('.transportCompressdiv').hide();
		$('.transportEncryptdiv').hide();
		$('.transportThreadNumdiv').hide();
		$('.transportPacketSizediv').hide();
		$('.tagexecutetimediv').hide();

		$('.transport-reconnect-times-div').hide();
		$('.transport-reconnect-interval-div').hide();
		if(taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER &&
			(currentRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
			|| currentRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
			|| currentRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING))
		{
			$('.transportModediv').show();
			$('.transportCompressdiv').show();
			$('.transportEncryptdiv').show();
			$('.transportPacketSizediv').show();
			if (!CONF.FUNCTIONS.includes('multithread')) {
				$('.transportThreadNumdiv').hide();
			}else{
				$('.transportThreadNumdiv').show();
			}


			$('#transportCompress').html(getFlagLevelInfo(data.transportStrategy.compress));
			$("#transportEncrypt").html(getFlagLevelInfo(data.transportStrategy.encrypt));
			// 传输加密算法
			if(data.transportStrategy.encrypt){
				// $('.transfer-encrypt-method-div').show();
				let encryptMethod = data.transportStrategy.encrypt_method;
				let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
				if(encryptMethod == 2){
					method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
				}
				$('#transferEncryptMethod').html(method);
			}else{
				$('.transfer-encrypt-method-div').hide();
			}
			$('#transportPacketSize').html(data.transportStrategy.transport_block_size);
			$("#transportMode").html(data.transportStrategy.mode);
		}

	}
	/**
	 * 解析备份模式
	 * @param backupMode
	 */
	var parseBackupModeConf = function (backupMode){
		if(backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY){
			backupMode = CONF.CDP_BACKUP_MODE.REALTIME_COPY;  //备份模式为3是因为创建是备份模式主备复制时，开启了数据备份到备份服务器时，备份模式动态变更为主备复制 + 实时备份
		}
		$('.mirrordataisbackupview').hide();  //镜像数据是否备份
		switch (parseInt(backupMode)){
			case 1:
				$('#taskDetailBackupMode').html(LANG.UI_VOL_CDP_BACKUP_MODE_REAL_TIME_SYNC);
				break;
			case 2:
				$('#taskDetailBackupMode').html(LANG.UI_VOL_CDP_BACKUP_MODE_REAL_TIME_REPLICATION);
				$('.mirrordataisbackupview').show();  //镜像数据是否备份
				break;
			case 3:
				$('#taskDetailBackupMode').html(LANG.UI_VOL_CDP_BACKUP_MODE_REAL_TIME_SYNC_AND_REPLICATION);
				break;
			default:
				$('#taskDetailBackupMode').html('--');
				break;
		}
	}
	/**
	 * 解析策略：备份任务为标签策略，恢复任务为任务启动策略
	 */
	var parseStartStrategy = function(timeStrategyInfo,taskType){
		var timeStrategy = getTimeStrategy(timeStrategyInfo);
		if(timeStrategy==""){
			timeStrategy = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
		}
		switch(taskType){
			case CONF.TASK_TYPE.VOL_CDP_BACKUP:
			case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
				$('#tagdes').html(timeStrategy);
			    var arr = timeStrategy.split('<br>')
				var str = ''
				for(let i = 0; i<arr.length;i++){
					str=str+arr[i]+`\n`
				}
				$('#tagdes').prop('title', str);
				break;
			case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
				$('#recoverStartStrategy').html(timeStrategy);
				break;
		}
	}
	/**
	 * 根据任务运行阶段调整接管任务详情部分参数描述与值
	 */
	var takeoverBasicInfoShowByRunningStage = function(runningStage){
		if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
		|| runningStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
		|| runningStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
			$('#volCdpVolValidSizeTh').text(LANG.UI_VOL_CDP_JOB_DETAILS_VALID_DATA);
			$(".takeoverTypeDiv").hide();
			$("#currentSizeView").show();
			$("#takeoverDataView").hide();
			$('#taskFlowOrMapTabs').show();

			$('.volCdpHostFileCachePathDiv').show();
			$('.volCdpHostFileCacheSizeDiv').show();
			$('.volCdpMemoryCacheDiv').show();
			$('.volCdpMemoryCacheSizeDiv').show();

			//默认
			$('#tab_chart').hide();
			$('#tab_task_map').show();
			if(_dataFlowSelected){
				$('#tab_chart').hide();
				$('#tab_task_map').show();
			}
			if(_chartTabSelected){
				$('#tab_chart').show();
				$('#tab_task_map').hide();
			}
		}else{
			$('#tab_chart').hide();
			$('#tab_task_map').show();
		}

		$('#taskFlowOrMapNavTabs').show();
		$(".currentcheckvolumeDiv").hide();
		switch (runningStage){
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:  //逆向初始同步
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING: //逆向同步启动中（回切启动中）
				$("#currentSizeView").show();
				$(".takeoverTypeDiv").hide();
				$(".totalsizeDiv").show();
				$('.processeddata').html(LANG.UI_FILE_PROCESSED_CAPACITY+":");
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY);
				$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_DATA_VOLUME);
				$('.mirrordataisbackupview').show();  //镜像数据是否备份
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //逆向实时同步
				$("#currentSizeView").show();
				$(".takeoverTypeDiv").hide();
				$('.totalsizeDiv').hide();
				$('.processeddata').html(LANG.UI_VOL_CDP_JOB_DETAILS_REALTIME_SYN_DATA+":");
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY_DATA_VOLUME);
				$('#volCdpVolValidSizeTh').text(LANG.UI_VOL_CDP_JOB_DETAILS_REALTIME_SYN_DATA);
				$('.mirrordataisbackupview').show(); //镜像数据是否备份
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:  //服务端的数据一致性校验
			case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK: //备机的数据一致性校验
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK: //实时数据校验
				$(".currentcheckvolumeDiv").show();
				break;
		}

	}

	/**
	 * 根据任务运行阶段调整备份任务详情部分参数描述与值
	 */
	var btBasicInfoShowByRunningStage = function(runningStage,data){
		$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY);
		$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_DATA_VOLUME);
		$('.processeddata').html(LANG.UI_FILE_PROCESSED_CAPACITY+":");
		$("#bakcupAgentTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT);
		$("#backupVolumeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_VOL);
		$("#targetVolumeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_MAP_VOL);
		$("#otherAgentServerTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER);
		var takeoverDataSource = data.takeover_data_source;
		if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
				|| runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING
				|| runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){
			$('.mirrordataisbackupview').show(); //镜像数据是否备份
			// $('#mirrorBackupView').html(getFlagLevelInfo(data.mirror_backup_flag));
			$(".takeoverNetworConfDiv").hide(); //根据需求，任务回切配置在接管中隐藏
			$(".takeoverFailbackConfDiv").show();  //根据需求，调整回切配置在接管中才能配置
		}else{
			$(".takeoverFailbackConfDiv").hide();  //根据需求，调整回切配置在接管中才能配置
			$(".takeoverNetworConfDiv").show(); //根据需求，任务回切配置在接管中隐藏
		}
		// $(".takeoverFailbackConfDiv").hide();  //根据需求，调整回切配置在接管中才能配置
		// $(".takeoverNetworConfDiv").show(); //根据需求，任务回切配置在接管中隐藏
		switch (runningStage){
			case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:  //初始化同步
				$("#taskTotalSizeStr").text(LANG.UI_VOL_CDP_JOB_DETAILS_TASK_ALL_CAPACITY+":");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:  //备份实时同步
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC:  //准备切换至实时同步阶段
				$('.totalsizeDiv').hide();
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY_DATA_VOLUME);
				$('#volCdpVolValidSizeTh').text(LANG.UI_VOL_CDP_JOB_DETAILS_SYN_DATA_VOLUME);
				$('.processeddata').html(LANG.UI_VOL_CDP_JOB_DETAILS_REALTIME_SYN_DATA+":");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:  //逆向初始同步
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY);
				$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_DATA_VOLUME);
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:    //接管中
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
				$(".takeoverFailbackConfDiv").show();  //根据需求，调整回切配置在接管中才能配置
				$(".takeoverNetworConfDiv").hide(); //根据需求，任务回切配置在接管中隐藏

				$(".totalsizeDiv").hide();
				// $(".takeoverTypeDiv").show();  //接管类型
				$("#takeoverType").text(LANG.UI_VOL_CDP_JOB_DETAILS_AUTO_TAKEOVER);  //接管总容量 >> 接管类型；
				$("#currentSizeView").hide();
				// $("#takeoverDataView").show();

				$("#otherAgentServerTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER);
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_VOL_VOLUME);
				$("#targetVolumeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CONFIGURE_MOUNT);
				if(takeoverDataSource==1){
					$("#takeoverDataSource").html(LANG.UI_VOL_CDP_TAKEOVER_BACKUP_SERVER);
				}else if(takeoverDataSource==2){
					$("#takeoverDataSource").html(LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE);
				}
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //逆向实时同步
				$('.totalsizeDiv').hide();
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY_DATA_VOLUME);
				$('#volCdpVolValidSizeTh').text(LANG.UI_VOL_CDP_JOB_DETAILS_REALTIME_SYN_DATA);
				$('.processeddata').html(LANG.UI_VOL_CDP_JOB_DETAILS_REALTIME_SYN_DATA+":");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
			case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK: //备机的数据一致性校验
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK: //实时数据校验
				$("#taskTotalSizeStr").text(LANG.UI_VOL_CDP_JOB_DETAILS_CHECK_CAPACITY+":");
				$(".processeddata").text(LANG.UI_VOL_CDP_JOB_DETAILS_CHECKED_CAPACITY+":");
				//修改时间：2022-11-8 16:51:04l;和后台商定，该阶段下与实时同步保持一致；
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY_DATA_VOLUME);
				$('#volCdpVolValidSizeTh').text(LANG.UI_VOL_CDP_JOB_DETAILS_SYN_DATA_VOLUME);
				$(".currentcheckvolumeDiv").show();
				break;
		}
	}

	/**
	 * 解析任务基础信息,根据状态和阶段显示或影藏某些元素，替换卷信息表格title
	 */
	var parseTaskBasicInfo = function(data){
		autoChangesVolInfoTabTh(data);
		var takeoverDataSource = data.takeover_data_source;
		if(takeoverDataSource==1){
			$("#takeoverDataSource").html(LANG.UI_VOL_CDP_TAKEOVER_BACKUP_SERVER);
		}else if(takeoverDataSource==2){
			$("#takeoverDataSource").html(LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE);
		}
		taskDefaultBasicInfo();
		$('.takeoverNetworConfDiv').hide(); //备份任务，开启自动接管条件下才允许查看或修改接
		$('.task_backup_mode_view').hide(); //备份模式
		$('.removeAutotakeoverView').hide(); //自动接管可用状态配置
		$('.volCdpFaultDynamicRecoveryDiv').hide();  //故障自动恢复
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.transportThreadNumdiv').hide();
		}else{
			$('.transportThreadNumdiv').show();
		}
		switch(data.taskTypeFlag){
			case CONF.TASK_TYPE.VOL_CDP_BACKUP:
			case CONF.TASK_TYPE.VOL_CDP_REPLICATION:
				//暂时屏蔽,调整为按备份模式方式进行功能阐释 2024年3月19日15:34:19
				// if(authFun.doubleHostMirror){
				// 	$(".doublehostmirrorview").show();  //双机镜像
				// }
				$('.totalsizeDiv').show();
				$('.task_backup_mode_view').show();
				$('#totalsizeDiv').show();
				$("#currentSizeView").show();
				$("#takeoverDataView").hide();

				//卷CDP备份任务show
				$('.volCdpStoredDataBaseDiv').show();
				$('.volCdpHostFileCachePathDiv').show();
				$('.volCdpHostFileCacheSizeDiv').show();
				$('.volCdpMemoryCacheDiv').show();
				$('.volCdpMemoryCacheSizeDiv').show();
				$('.taskEncryptdiv').hide();
				$('.cdpAutoTakeoverConfDiv').show();  //是否启用接管配置
				$('.volCdpIoReplicationModeDiv').show();  //IO复制模式view
				$('.volCdpFaultDynamicRecoveryDiv').show();  //故障自动恢复
				$('.volCdpMasterIpSwitchDiv').hide();
				$(".standbyEmdVmRoleDiv").hide();

				// $('.transport-reconnect-times-div').show();  //Bug #14474 需求暂时屏蔽
				// $('.transport-reconnect-interval-div').show();  //Bug #14474 需求暂时屏蔽

				$('#ioReplicationMode').html(data.monitor_data_io_replication_mode);
				if(data.auto_takeover_flag == CONF.FLAG.SET && data.auto_takeover_enable_flag==CONF.FLAG.SET){
					$('#cdpAutoTakeoverConf').html(getFlagLevelInfo(CONF.FLAG.SET));
				}else{
					$('#cdpAutoTakeoverConf').html(getFlagLevelInfo(CONF.FLAG.UNSET));
				}

				if(data.auto_fault_resume_flag == CONF.FLAG.SET){
					$('#volCdpFaultDynamicRecovery').html(getFlagLevelInfo(CONF.FLAG.SET));
				}else{
					$('#volCdpFaultDynamicRecovery').html(getFlagLevelInfo(CONF.FLAG.UNSET));
				}

				var auto_takeover_info = data.auto_takeover_info;
				var tempStatus = CONF.FLAG.UNSET;
				var prefixStatus = CONF.FLAG.UNSET;
				var tempConsoleUrl = "";
				var takeoverStandbyuuid = "";
				_takeoverAgentType = 0;
				if(data.auto_takeover_flag==CONF.FLAG.SET){
					$('.autoTakeoverStandbyDiv').show();
					$('#autoTakeoverStandby').html(data.autoTakeoverStandby);
					_takeoverAgentType = data.auto_takeover_info.takeover_agent_type;
					tempStatus = data.auto_takeover_info.temp_status;
					prefixStatus = data.auto_takeover_info.prefix_status;
					tempConsoleUrl = data.auto_takeover_info.temp_console_url;
					takeoverStandbyuuid = data.auto_takeover_info.takeover_standby_agent_uuid;
					parseAutoTakeoverConf(data);
					$('.taskTakeoverTypeDiv').hide();  //  #22911 需求调整
					if(_takeoverAgentType  == vmHypervisorTypeEmbedQemuKvm){
						$('.tempagentconfherf').show();
					}else{
						$('.tempagentconfherf').hide();
					}
				}else{
					$('.autoTakeoverStandbyDiv').hide();
					$('.volCdpAppMonitorDiv').hide(); //应用故障监测div
					$('.volCdpAppTakeoverDiv').hide();
					$('#tab_takeover').hide();
					$('#takeoverli').hide();
					$('.taskTakeoverTypeDiv').hide();
				}
				_vmTempStatus = data.auto_takeover_info.temp_status;  //获取虚拟主机状态
				_VMPrefixStatus = prefixStatus;
				tempAgentControl(_takeoverAgentType,tempStatus,tempConsoleUrl,takeoverStandbyuuid);
				//双机镜像
				var doubleHostMirrorFlag = 0;
				if(data.standby_agent_uuid!='' && (data.backup_mode == CONF.CDP_BACKUP_MODE.REALTIME_COPY
					|| data.backup_mode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY)){
					var doubleHostMirrorFlag = 1;
					$(".doubleMachineImgHostDiv").show();
					$('#doubleMachineImgHost').html(data.standby_agent_info); //镜像备机，同接管备机
				}
				btBasicInfoShowByRunningStage(_taskCurrentRunningStage,data);  //根据任务运行阶段调整备份任务详情部分参数描述与值
				_doubleHostMirrorFlag = doubleHostMirrorFlag;
				$('#mirrorBackupView').html(getFlagLevelInfo(data.mirror_backup_flag));
				$('#doubleHostmirrorview').html(getFlagLevelInfo(doubleHostMirrorFlag));

				$('#createTime').html(data.createTime);
				$('#nextTime').html(data.nextTime);
				$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
				$('#transportCompress').html(getFlagLevelInfo(data.transportStrategy.compress));
				// 压缩等级
				if(data.transportStrategy.compress){
					var method = '';
					switch(data.transportStrategy.compress_method) {
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
					$('#transportCompressMethod').html(method);
					$('.transportCompressMethodDiv').show();
				}else{
					$('.transportCompressMethodDiv').hide();
				}
				$("#transportMode").html(data.transportStrategy.mode);
				$("#transportEncrypt").html(getFlagLevelInfo(data.transportStrategy.encrypt));
				// 传输加密算法
				if(data.transportStrategy.encrypt){
					// $('.transfer-encrypt-method-div').show();
					let encryptMethod = data.transportStrategy.encrypt_method;
					let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
					if(encryptMethod == 2){
						method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
					}
					$('#transferEncryptMethod').html(method);
				}else{
					$('.transfer-encrypt-method-div').hide();
				}
				parseStartStrategy(data.timeStrategy,data.taskTypeFlag);  //标签策略
				parseBackupModeConf(data.backup_mode);  //解析备份模式
				break;
			case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
				$('#hostname').text(LANG.UI_VOL_CDP_RECOVERY_HOST);
				$("#currentSizeView").show();
				$("#takeoverDataView").hide();
				$('#storageli').hide();
				$('#takeoverli').hide();
				$('#tagdesView').hide();
				$('#seniorli').hide();
				$('#recoverStartStrategyView').hide();  //恢复暂不支持任务策略
				$('#reservedStrategyView').hide();
				$('#createTime').html(data.createTime);

				$('.taskEncryptdiv').hide();
				$('.tagexecutetimediv').hide();
				$('.volCdpMasterIpSwitchDiv').hide();
				$("#taskThreadNum").html(data.thread_num);
				$(".standbyEmdVmRoleDiv").hide();
				//恢复任务新增传输相关配置  modify-time:2022-9-28 17:44:52
				$('#transportCompress').html(getFlagLevelInfo(data.transportStrategy.compress));
				$('.transportCompressMethodDiv').hide();
				if(data.transportStrategy.compress){
					$('.transportCompressMethodDiv').show();

                    var compressMethod = data.transportStrategy.compress_method;
					var compressMethodStr = "--";
					switch (compressMethod){
						case 1:
							compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
							break;
						case 2:
							compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
							break;
						case 3:
							compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
							break;
						case 4:
							compressMethodStr = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
							break;
					}
					$('#transportCompressMethod').html(compressMethodStr);
				}
				$("#transportEncrypt").html(getFlagLevelInfo(data.transportStrategy.encrypt));
				// 传输加密算法
				if(data.transportStrategy.encrypt){
					// $('.transfer-encrypt-method-div').show();
					let encryptMethod = data.transportStrategy.encrypt_method;
					let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
					if(encryptMethod == 2){
						method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
					}
					$('#transferEncryptMethod').html(method);
				}else{
					$('.transfer-encrypt-method-div').hide();
				}
				$('#transportPacketSize').html(data.transportStrategy.transport_block_size);
				$("#transportMode").html(data.transportStrategy.mode);

				parseStartStrategy(data.timeStrategy,data.taskTypeFlag);

				// $('.transport-reconnect-times-div').show();  //Bug #14474 需求暂时屏蔽
				// $('.transport-reconnect-interval-div').show();  //Bug #14474 需求暂时屏蔽
				break;
			case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
				$("#takeoverType").text(LANG.UI_VOL_CDP_JOB_DETAILS_MANUAL_TAKEOVER);
				// $(".takeoverTypeDiv").show();
				$(".totalsizeDiv").hide();
				$("#currentSizeView").hide();
				// $("#takeoverDataView").show();
				$(".standbyEmdVmRoleDiv").show();

				$('#storageli').hide();
				$('#strategyli').hide();
				$('#seniorli').hide();
				$('#takeoverli').show();
				$('.takeoverTimestampDiv').show();

				$('.autoTakeoverStandbyDiv').show();
				$('.volCdpAppTakeoverDiv').show();
				$('.takeoverFailbackConfDiv').show(); //接管回切配置
				$(".takeoverNetworConfDiv").hide(); //根据需求，任务回切配置在接管中隐藏
				$('.volCdpMasterIpSwitchDiv').show();

				$('#taskFlowOrMapTabs').hide();
				$('#tab_chart').hide();
				$('#taskFlowOrMapNavTabs').hide();
				$('#tab_task_map').show();
				if(data.auto_takeover_flag==1){
					$('#takeover_title').html(LANG.UI_VOL_CDP_AUTO_TAKEOVER_TITLE);
					$('.autoScriptDiv').show();
					$('.manualScriptDiv').hide();
					$('#netconfbtn').html(LANG.UI_VOL_CDP_JOB_DETAILS_FAILOVER_NET_CONF)
				}else{
					$('#takeover_title').html(LANG.UI_VOL_CDP_MANUAL_TAKEOVER_TITLE);
					$('.autoScriptDiv').hide();
					$('.manualScriptDiv').show();
					$('#netconfbtn').html(LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_NET_CONF)
				}

				takeoverBasicInfoShowByRunningStage(_taskCurrentRunningStage);
				taskFailbackStageViewShow(data);
				hideTransPortLable(data); //根据任务类型及运行阶段隐藏和显示传输配置项
				var handoverInfo = data.handover_info;
				_vmTempStatus = handoverInfo.temp_status;
				_VMPrefixStatus = handoverInfo.prefix_status;
				_takeoverAgentType = data.handover_info.takeover_agent_type;
				tempAgentControl(data.handover_info.takeover_agent_type,handoverInfo.temp_status,data.handover_info.temp_console_url,data.handover_info.takeover_standby_agent_uuid);

				$('#autoTakeoverStandby').html(handoverInfo['standby_agent']);
				$('#autoTakeoverCatbackIp').html(handoverInfo['failbackup_ip']);

				$('.customScriptDiv').show();	//接管自定义脚本
				// $('.takeoverNetworConfDiv').show();
				if(handoverInfo.script_info.length==0){
					$('#customScript').html(LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE);
				}
				takeoverTaskApplicationSceneReplace(data);
				// $('#standbyEmdVmRole').html(standbyEmdVmRoleStr);
				// $('#taskTakeoverType').html(takeoverTypeStr);



				$('#volCdpAppTakeover').html(LANG.UI_VOL_CDP_BACKUP_NO_ENABLED);
				if(handoverInfo.app_takeover_flag ==1){
					$('#volCdpAppTakeover').html(LANG.UI_RECOVERY_AWS_ENABLE);
				}
				$('#volCdpMasterIpSwitch').html(LANG.UI_FILE_DISABLE);
				if(handoverInfo.master_ip_switch==1){
					$('#volCdpMasterIpSwitch').html(LANG.UI_FILE_ENABLE);
				}
				$('#takeoverTimestamp').html(handoverInfo['takeover_timestamp']);
				$(".nostandbymap").hide();

				$('#mirrorBackupView').html(getFlagLevelInfo(data.mirror_backup_flag));
				$('.transportCompressMethodDiv').hide();
				// 传输压缩等级
				var taskStage =  data.current_task_running_stage_value;
				if(data.transportStrategy.compress &&
					(taskStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
					|| taskStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
					|| taskStage== CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING )){
					var method = '';
					switch(data.transportStrategy.compress_method) {
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
					$('#transportCompressMethod').html(method);
					$('.transportCompressMethodDiv').show();
				}else{
					$('.transportCompressMethodDiv').hide();
				}
				break;
		}
	}
	/**
	 * 接管任务应用场景特殊处理
	 * 对应需求号：18448
	 */
	var takeoverTaskApplicationSceneReplace = function(data){
		var handoverInfo = data.handover_info;
		var standbyEmdVmRoleStr = "";
		var takeoverTypeStr = "";
		var taskStageValue = data.current_task_running_stage_value;
		_takeoverApplicationRol = false;
		if(handoverInfo['takeover_agent_role'] == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){  //验证任务类型
			_takeoverApplicationRol = true;
			standbyEmdVmRoleStr = LANG.UI_VOL_CDP_VERIFY_DESC; //验证
			takeoverTypeStr = LANG.UI_VOL_CDP_MOUNT_VERIFY_DESC;  //挂载验证
			$('.takeoverFailbackConfDiv').hide();  //验证任务屏蔽回切配置
			$('.volCdpMasterIpSwitchDiv').hide(); //验证任务屏蔽主机IP漂移
			if(handoverInfo['takeover_agent_type'] == vmHypervisorTypeEmbedQemuKvm){  //内嵌虚拟化
				takeoverTypeStr = LANG.UI_VOL_CDP_COMPLETE_MACHINE_VERIFY_DESC; //整机验证
				$('.tempagentconfherf').show();
				$('.customScriptDiv').hide(); //自定义脚本

			}
			// 如果包含，使用replace()方法将其替换为"验证" ,对应需求18448
			if (data.current_task_running_stage.includes(LANG.UI_VOL_CDP_TAKEOVER_DESC)) {
				data.current_task_running_stage = data.current_task_running_stage.replace(LANG.UI_VOL_CDP_TAKEOVER_DESC, LANG.UI_VOL_CDP_VERIFY_DESC);
			}
			$('.volcdpapptakeoverstr').html(LANG.UI_VOL_CDP_APP_VERFI_STR + ": ");
			$('#takeover_title').html(LANG.UI_VOL_CDP_VERIFY_DESC);

		}else if(handoverInfo['takeover_agent_role'] == CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){  //接管任务类型
			standbyEmdVmRoleStr = LANG.UI_VOL_CDP_TAKEOVER_DESC;  //接管
			takeoverTypeStr = LANG.UI_VOL_CDP_MOUNT_TAKEOVER_DESC;  //挂载接管
			if(handoverInfo['takeover_agent_type'] == vmHypervisorTypeEmbedQemuKvm){  //内嵌虚拟化
				takeoverTypeStr = LANG.UI_VOL_CDP_COMPLETE_MACHINE_TAKEOVER_DESC;
				$('.tempagentconfherf').show();
				$('.customScriptDiv').hide(); //自定义脚本
			}
			$('.volcdpapptakeoverstr').html(LANG.UI_VOL_CDP_APP_TAKEOVER_STR + ": ");
			$('#takeover_title').html(LANG.UI_VOL_CDP_TAKEOVER_DESC);
		}
		if(taskStageValue !=0 && data.statusValue != CONF.TASK_STATUS.STOPPED ){
			$('#taskStage').html(standbyEmdVmRoleStr + '（<span class = "label '
				+ getRunningStageClass(data.statusValue,taskStageValue) +'" >' +  data.current_task_running_stage + '</span>）');
		}else{
			$('#taskStage').html(standbyEmdVmRoleStr);
		}
		$('#standbyEmdVmRole').html(standbyEmdVmRoleStr);
		$('#taskTakeoverType').html(takeoverTypeStr);
	}

	/**
	 * 控制模板机，需要判断接管备机是否为内嵌虚拟主机，虚拟机状态是否处于运行中
	 */
	var tempAgentControl = function (takeoverAgentType,tempStatus,tempConsoleUrl,vmUuid){
		//tempStatus：1（在线）2（离线）；_VMPrefixStatus:0未知，1执行预处理，2：执行预处理完成
		if(takeoverAgentType== vmHypervisorTypeEmbedQemuKvm && tempStatus ==CONF.FLAG.SET && _VMPrefixStatus == 2)
		{
			$("#standbyHostRemoteControl").css({
				"text-decoration": "underline",
				"pointer-events": "initial",
				"cursor": "pointer"
			});
			$("#takeoverStandbyRemoteControl").css({
				"text-decoration": "underline",
				"pointer-events": "initial",
				"cursor": "pointer"
			});

			$('.clickicon').show();
			$('#standbyIp').addClass('colorgreen');
			$('#autoTakeoverStandby').addClass('colorgreen');
			$('#takeoverStandbyRemoteControl').unbind('click').click(takeoverStandbyOpereate);
			$('#standbyHostRemoteControl').unbind('click').click(takeoverStandbyOpereate);
			_tempConsoleUrl = tempConsoleUrl;
			_vmUuid = vmUuid;

		}else{  //移除a标签点击属性及样式
			$('#takeoverStandbyRemoteControl').removeAttr('href').css({
				'pointer-events': 'none',
				'color':'#333333',
				'cursor': 'default',
				'text-decoration':'none'
			});
			$('.clickicon').hide();

			$('#takeoverStandbyRemoteControl').removeAttr('colorgreen');

			$("#takeoverStandbyRemoteControl").css("text-decoration","auto");  //移除下划线
			$("#standbyHostRemoteControl").css("text-decoration","auto");	//移除下划线

			$('#standbyIp').removeAttr('colorgreen');
			$('#autoTakeoverStandby').removeAttr('colorgreen');

			$('#standbyHostRemoteControl').removeAttr('href').css({
				'pointer-events': 'none',
				'cursor': 'default',
				'text-decoration':'none'
			});
			$('#standbyHostRemoteControl').removeAttr('colorgreen');

			_tempConsoleUrl = "";
			_vmUuid = "";
		}
	}
	/**
	 * 同步内嵌虚拟化平台vnc token
	 */
	var takeoverStandbyOpereate = function(){
		Metronic.blockUI({target: '#jobDetailDiv',animate: true,cenrerY: true});
		pAjaxRequest({type:'look'}, "/api/v1/virtual/operate/"+_vmUuid, "POST", function (result) {
			Metronic.unblockUI('#jobDetailDiv');
			if (result.code == 0) {
				window.open(_tempConsoleUrl, '_blank');
				return;
			} else {
				UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
			}
		});
	}
	/**
	 * 根据任务运行阶段控制任务基础新信息中存储、高级配置tab项是否显示或隐藏
	 */
	var taskFailbackStageViewShow = function(data){
		var currentRunningStage = _taskCurrentRunningStage;
		var taskType = data.taskTypeFlag;

		if(currentRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
			|| currentRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
			|| currentRunningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
			$('#storageli').show();
			$('#seniorli').show();
		}else{
			$('#storageli').hide();
			$('#seniorli').hide();
		}
		//手动接管任务存储选项卡,高级配置选项卡下参数无需显示与获取
		if(taskType==CONF.TASK_TYPE.VOL_CDP_TAKEOVER){
			$('.compressferDiv').hide();  //压缩配置
			$('.blocksizediv').hide();  //存储数据库快大小
			$('.encryptStoragediv').hide();  //数据加密配置
			$('.passwordAutodiv').hide();  //自动生成密码配置

			$('.speedDiv').hide();  //隐藏限速策略
			$('.volCdpIoReplicationModeDiv').show();
			$('#ioReplicationMode').html(data.monitor_data_io_replication_mode);
		}

	}
	/**
	 * 根据任务状态和任务阶段调整卷应用信息表格th的描述信息
	 */
	var autoChangesVolInfoTabTh = function(data){
		var runningStage = data.current_task_running_stage_value;  //任务运行阶段
		var taskType = data.taskTypeFlag;
		switch(taskType){
			case CONF.TASK_TYPE.VOL_CDP_BACKUP:  //备份
			case CONF.TASK_TYPE.VOL_CDP_REPLICATION:  //复制
				if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){  //自动接管备份任务处于接管中
					$("#bakcupAgentTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT);
					$("#backupVolumeTh").text(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_VOL);
					$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_VOL_VOLUME);
					$("#otherAgentServerTh").text(LANG. UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER);
					$("#volCdpVolTransferSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME_POINT);
					$("#taskWriteSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_IF_CONFIG_APPLICATION);
					$("#targetVolumeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CONFIGURE_MOUNT);

				}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
					$("#volCdpVolTransferSizeTh").text(LANG.UI_PUBLIC_TRANSFER_SIZE);
					$("#taskWriteSizeTh").text(LANG.UI_PUBLIC_REAL_SIZE);
					$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY);
					$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_DATA_VOLUME);

				}else if(runningStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){
					$("#volCdpVolTransferSizeTh").text(LANG.UI_PUBLIC_TRANSFER_SIZE);
					$("#taskWriteSizeTh").text(LANG.UI_PUBLIC_REAL_SIZE);
					$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY_DATA_VOLUME);
					$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_SYN_DATA_VOLUME);
				}
				break;
			case CONF.TASK_TYPE.VOL_CDP_RECOVERY:  //恢复
				$("#bakcupAgentTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_CLIENT);
				$("#backupVolumeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_VOL);
				$("#targetVolumeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_VOL);
				$("#otherAgentServerTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_SERVER);
				$("#taskWriteSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIME_POINT);
				$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY);
				$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME);
				$("#taskTotalSizeStr").text(LANG.UI_VOL_CDP_JOB_DETAILS_TASK_ALL_CAPACITY+":");
				break;
			case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:  //接管
				$("#bakcupAgentTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT);
				$("#volCdpVolTransferSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME_POINT);
				//如果是自动接管
				if(data.auto_takeover_flag==1){
					$("#backupVolumeTh").text(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_VOL);
				}else{
					//手动接管
					if(data.handover_info.takeover_agent_role ==CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){ //验证
						$("#backupVolumeTh").text(LANG.UI_VOL_CDP_VERIFY_VOL_DESC);
						$("#volCdpVolTransferSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_VERIF_TIME_POINT);
					}else{
						$("#backupVolumeTh").text(LANG.UI_VOL_CDP_BACKUP_MANUAL_TAKEOVER_VOL);
					}

				}
				$("#otherAgentServerTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER);

				$("#taskWriteSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_IF_CONFIG_APPLICATION);
				$("#targetVolumeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CONFIGURE_MOUNT);
				if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
					$("#volCdpVolTransferSizeTh").text(LANG.UI_PUBLIC_TRANSFER_SIZE);
					$("#taskWriteSizeTh").text(LANG.UI_PUBLIC_REAL_SIZE);
					$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY);
					$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_DATA_VOLUME);

				}else if(runningStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){
					$("#volCdpVolTransferSizeTh").text(LANG.UI_PUBLIC_TRANSFER_SIZE);
					$("#taskWriteSizeTh").text(LANG.UI_PUBLIC_REAL_SIZE);
					$("#volCdpVolSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_CAPACITY_DATA_VOLUME);
					$("#volCdpVolValidSizeTh").text(LANG.UI_VOL_CDP_JOB_DETAILS_SYN_DATA_VOLUME);
				}
				break;
		}
	}
	/**
	 * 解析和控制任务总进度条的显示与隐藏信息
	 */
	var totalpmgressbarDivControl = function(basicInfo){
		var taskTypeFlag= basicInfo.taskTypeFlag;
		var taskState = basicInfo.current_task_running_stage_value;
		var statusValue = basicInfo.statusValue;
		$('.progressDiv').hide();
		$('#total-progress').css({width: 0});
		$('#progressright').html('');
		$('.task_consistency_progress').hide();
		//备份任务处于运行中
		if((taskTypeFlag == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskTypeFlag ==CONF.TASK_TYPE.VOL_CDP_REPLICATION) && statusValue==CONF.TASK_STATUS.RUNNING){
			if(taskState ==	CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
				|| taskState ==	CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK
				|| taskState ==	CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
				|| taskState ==	CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
				|| taskState ==	CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
				$('.progressDiv').show();
				$('#total-progress').css({width: basicInfo.totalprogress});
				$('#progressright').html(basicInfo.progress);
				$('.task_consistency_progress').hide();
				$('.task_total_progress').show();
			}
			if(taskState == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK
				|| taskState == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
				|| taskState == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK
			){
				$('.task_consistency_progress').show();
				$('.task_total_progress').hide();
			}
		}else if(taskTypeFlag == CONF.TASK_TYPE.VOL_CDP_TAKEOVER && (taskState ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
			|| taskState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING)){
			$('.progressDiv').show();
			$('#total-progress').css({width: basicInfo.totalprogress});
			$('#progressright').html(basicInfo.progress);
		}else if(taskTypeFlag == CONF.TASK_TYPE.VOL_CDP_RECOVERY && statusValue==CONF.TASK_STATUS.RUNNING){  //恢复任务处于运行中
			$('.progressDiv').show();
			$('#total-progress').css({width: basicInfo.totalprogress});
			$('#progressright').html(basicInfo.progress);
		}
	}
	/**
	 * 解析CPU模式
	 */
	var parseCpuModeStr = function(cpuMode){
		let cpuModeStr = "";
		switch(cpuMode){
			case 1:
				cpuModeStr = LANG.UI_VERIFY_CPU_MODE_CUSTOM;
				break;
			case 2:
				cpuModeStr = "host_model";
				break;
			case 3:
				cpuModeStr = "host_passthrough";
				break;
			default:
				cpuModeStr = LANG.UI_VERIFY_CPU_MODE_CUSTOM;
		}
		return cpuModeStr;
	};
	/**
	 * 获取内嵌虚拟化配置信息
	 */
	var getTempAgentConfigInfo = function () {

        Metronic.blockUI({target: '#vm_list_table',animate: true,cenrerY: true,});
        var task_uuid = $("#task_uuid").val();
        pAjaxRequest({task_uuid:task_uuid}, "/api/v1/volcdp/vm_template_conf", "GET", function (result) {

            // Metronic.unblockUI('#');
            if (result.code == 0) {
                let jsonData = result.data;
				$('.taskTempAgentConfig').modal({'width':'800px', 'height':'380px'});
				let vmTempName = jsonData.vm_temp_name;
				let memory_size = jsonData.memory_size;
				let interfaces = jsonData.interfaces;
				let firmware = jsonData.firmware;
				let cpuCpre = jsonData.cpu_core;
				let bootMode = jsonData.boot_mode;
				let cpuMode = jsonData.cpu_mode;
				let cpuModeStr = parseCpuModeStr(cpuMode);  //解析CPU模式

				$('.tasktempvirtualname').html(vmTempName);
				$('.tasktempvirtualcpu').html(cpuCpre + LANG.UI_VM_MACHINE_CPU_UNIT);
				$('.tasktempvirtualmachinemems').html(memory_size);
				$('.tasktempvmmachinecpumode').html(cpuModeStr);
				//引导固件 1：BIOS，2：UEFI
				let firmwareStr = "";
				if(firmware == 1){
					firmwareStr = "BIOS";
				}else if(firmware == 2){
					 firmwareStr = "UEFI";
				}
				//启动方式 1：光盘，2:硬盘
				let bootModeStr = "";
				if(bootMode == 1){
					 bootModeStr = LANG.UI_VOL_CDP_CD;
				}else if(bootMode ==2){
					bootModeStr = LANG.UI_VOL_CDP_DISK;
				}
				$('.tasktempvmmachinebootfireware').html(firmwareStr);
				$('.tasktempvmmachinebootmode').html(bootModeStr);
				let virtualMachinenetStr = "";
				for(var i =0;i< interfaces.length;i++){
					let networkName = interfaces[i].network_name;
					let networkCardName = interfaces[i].name;
					let modelTypeName = interfaces[i].model_type_name;
					let sourceIpInfo = interfaces[i].source_ip_info;
					var dataSourceIp = parseVmTempIpInfo(sourceIpInfo);

					virtualMachinenetStr += "<span>"+LANG.UI_VOL_CDP_HOST_IP_SERVICE + "("+dataSourceIp+"); " + LANG.UI_VM_MACHINE_NETWORK_SET+"("+networkName+");  "+LANG.UI_CLIENT_NETWORK_NIC_NAME+"("+networkCardName+");  " + LANG.UI_VOL_CDP_VM_MACHINE_NETWORK_INTETFACE +"("+modelTypeName+"); </span><br>";
				}

				$('.tasktempvirtualmachinenet').html(virtualMachinenetStr);

            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_MANAGER, result.message);
                return;
            }
        });
    }
	/**
	 * 解析内嵌虚拟平台配置的网络信息
	 */
	var parseVmTempIpInfo = function(sourceIpInfo){
		var dataSourceIp = "";
		var vmNetCard = sourceIpInfo;
		for(var j=0;j<vmNetCard.length;j++){
			var ip_addr = vmNetCard[j].ip_addr;
			if(j !=0){ip_addr = ", "+ ip_addr;}
			dataSourceIp += ip_addr;
		}
		return dataSourceIp;

	}
	/**
	 * 获取一配置的自定义脚本信息
	 */
	var takeoverScriptConfDetail = function(){
		$('#takeoverScriptTaskModal').modal({'width':'800px', 'height':'380px'});
		$('#scriptExecuteOrderShow').hide();
		$('#scriptExecIntervalShow').hide();
		$('#scriptExecFailureShow').hide();

		if(_taskType==CONF.TASK_TYPE.VOL_CDP_TAKEOVER){
			$('#scriptExecuteOrderShow').show();
		}
		if(_taskType==CONF.TASK_TYPE.VOL_CDP_BACKUP || _taskType==CONF.TASK_TYPE.VOL_CDP_REPLICATION){
			$('#scriptExecIntervalShow').show();
			$('#scriptExecFailureShow').show();
		}

		var p = {};
    	p.uuid = $("#task_uuid").val();
    	p.task_type =_taskType;
    	p = JSON.stringify(p);
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getTakeoveScriptConf',p:p}, function(d){
    		var data = JSON.parse(d);
    		var info = "";

    		for(var i=0;i<data.length;i++){
    			var scriptType = data[i]['script_type'];
        		var scriptPath = data[i]['script_path'];
        		var execType = data[i]['exec_type'];
        		var execInterval = data[i]['exec_interval'];
        		var triggerFailNum = data[i]['trigger_fail_num'];

        		var execTypeStr = "";
        		if(execType==1){
        			execTypeStr = LANG.UI_VOL_CDP_BACKUP_EXECUTE_BEFORE_TAKEOVER;
        		}else if(execType==2){
        			execTypeStr = LANG.UI_VOL_CDP_BACKUP_EXECUTE_AFTER_TAKEOVER;
        		}
        		var execIntervalStr = "";
        		if (execInterval!=0 && scriptType==2){
        			execIntervalStr = LANG.UI_VOL_CDP_BACKUP_EVERY+execInterval+LANG.UI_VOL_CDP_JOB_DETAILS_EXECUTE_ONETIME;
        		}
        		var triggerFailStr = "";
        		if (triggerFailNum!=0 && scriptType==2){
        			triggerFailStr = LANG.UI_VOL_CDP_JOB_DETAILS_TOTAL_FAILURE_TIMES+triggerFailNum+LANG.UI_VOL_CDP_BACKUP_TIMES;
        		}
        		var scriptTypeStr = LANG.UI_VOL_CDP_BACKUP_TAKEOVER_EXECUTE_SCRIPT;
        		var scriptExecTerm = execTypeStr;
        		if(scriptType==2){
        			scriptTypeStr = LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_MONITOR_SCRIPT;
        			scriptExecTerm = execIntervalStr+","+triggerFailStr;
        		}
    			info += '<li style="border-bottom:1px solid #e8edf3;float:left;width:100%;margin-top:2%"><div class="col1"><div class="cont"><div class="cont-col1">'+
    				'<div style="width:50px;height:50px;padding-top:10%;float:left;margin:-2% 0 0 5%;background: url(./img/cdp/script.png) 0 no-repeat;"></div></div><div class="cont-col2">'+
    				'<div class="desc" style="margin:5px 0 0 -5px;"><span style="color: #40bca4;">'+LANG.UI_VOL_CDP_JOB_DETAILS_SCRIPT_TYPE+''+scriptTypeStr +';</span>&nbsp;&nbsp;<span style="color:#738aea">'+LANG.UI_VOL_CDP_JOB_DETAILS_EXECUTE_CONDITION+scriptExecTerm +'</span><br>'+
    				'<span>'+LANG.UI_VOL_CDP_JOB_DETAILS_SCRIPT_PATH+scriptPath +'</span></div></div></div></div></li>';
    		}
    		$('#taskTakeoverScriptList').html(info);
    	});
	}
	/**
	 * 获取任务历史网络配合
	 */
	var getTaskNetCardHistoryConf = function(){
		var p = {};
	   	p.uuid = $("#task_uuid").val();
    	p.task_type =_taskType;
    	if(_taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER || _taskType ==CONF.TASK_TYPE.VOL_CDP_BACKUP || _taskType == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
    		var params = JSON.stringify(p);
    		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getTaskNetworkHistoryConf',p:params}, function(data){  //获取任务主机网卡信息
    			var data = JSON.parse(data);
        		if(data){
        			_takeoverHisBusinessIpMap = data.takeover_business_ip_map; //任务接管网络配置
        			_failbackHisBusinessIpMap = data.failback_business_ip_map; //回切网络配置
        		}
    		});
    	}
	}
	/**
	 * 启用或禁用自动接管配置
	 */
	var autoTakeoverEnableAndDisableConf = function (){
		if(_autoTtakeoverEnableFlag==CONF.FLAG.SET){
			var enableFlag = CONF.FLAG.UNSET;
		}else if(_autoTtakeoverEnableFlag==CONF.FLAG.UNSET){
			var enableFlag = CONF.FLAG.SET;
		}else{
			var enableFlag = CONF.FLAG.SET;
		}
		var p = {};
		p.task_uuid = $("#task_uuid").val();
		p.task_type =_taskType;
		p.task_current_stage = _taskCurrentRunningStage; //运行阶段
		p.enableFlag = enableFlag;
		var params = JSON.stringify(p);
		Metronic.blockUI({target: '#jobDetailDiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'autoTakeoverOperation',p:params}, function(data) {  //获取任务主机网卡信息
			Metronic.unblockUI('#jobDetailDiv');
			if(OPREL(data)){
			}
		});
	}
	/**
	 * 任务接管网络配置
	 */
	var taskTakeoverNetworkConf = function(){
		var p = {};
	   	p.uuid = $("#task_uuid").val();
    	p.task_type =_taskType;
    	p.failbackhost = "";
    	var params = JSON.stringify(p);
    	Metronic.blockUI({target: '#jobDetailDiv',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getTaskNetworkConfInfo',p:params}, function(data){  //获取任务主机网卡信息
    		Metronic.unblockUI('#jobDetailDiv');
    		var data = JSON.parse(data);
    		if(data){

    			$('#taskTakeoverNetworkConfModal').modal({'width':'850px', 'height':'460px'});
    			_agentNetworkInfo = data['agent_nic_list'];
    			_standbyNetworkInfo = data['standby_nic_list'];

        		$('#task_takeover_ip_server_conf').takeoverIpServerMapConfig({
        			agentNetwork:_agentNetworkInfo,
        			standbyNetwork: _standbyNetworkInfo,
        			takeoverHisNetwork:_takeoverHisBusinessIpMap,
        			failbackHisNetwork:[],
					failbackupCationIp:_failbackCationIp,
					failbackupCationConf:false,
    	  		});

    	  		$('#task_standby_gateway_conf').takeoverStandbyGatewayConfig({
					agentNetwork:_agentNetworkInfo,
					standbyNetwork: _standbyNetworkInfo,
					takeoverHisNetwork:_takeoverHisBusinessIpMap,
					failbackHisNetwork:[],
					failbackupCationIp:'',
					failbackupCationConf:false,
    	  		});
    		}
    	});
    	$("#submit_task_takeover_network_conf").unbind('click').click(submitTaskHostNetworkConf);
	}
	/**
	 * 校验网关格式
	 */
	var checkIpFormat = function (){
		var ipVerify = true;
		for(var i=0;i<_standbyGatewayConf.length;i++){
			var gateway = _standbyGatewayConf[i].gateway;
			if(gateway=="" && !ipV4V6(gateway)){
				ipVerify = false;
				break;
			}
		}
		return ipVerify;
	}

	/**
	 * 修改任务网络配置
	 */
	var submitTaskHostNetworkConf = function(){
		var taskIpMapInfoList = [];
		var networCardConfInfo = {};
		var gateAwyConfDesList = [];
		// _takeoverIpMapListView = [];

		var standbyNetCardConfList = standbyNetCardConf();
		for(var i=0;i<_agentNetworkInfo.length;i++){
			var ipMapInfo = {};
			var nicIpList = [];
			var agentMapInfo  = {};
			ipMapInfo.nic_gateway = _agentNetworkInfo[i].gateway_address;
			ipMapInfo.nic_name = _agentNetworkInfo[i].name;
			ipMapInfo.nic_mac = _agentNetworkInfo[i].mac_address;

			var ipSet = _agentNetworkInfo[i].ip_set;
			if(!ipSet){
				break;
			}
			for(var j=0;j<ipSet.length;j++){
				var nicIpInfo = {};
				var standbyNetCardSelect =  "standby_network_card_"+i+j;
				var hostIp = $("#"+standbyNetCardSelect).find("option:selected").attr("host-ipdrr");
				var netMask = $("#"+standbyNetCardSelect).find("option:selected").attr("agent-netmask");
				var targetGateway = $("#"+standbyNetCardSelect).find("option:selected").attr("standby-gateway");
				var targetNicName = $("#"+standbyNetCardSelect).find("option:selected").text();
				var targetHostMac = $("#"+standbyNetCardSelect).val();
				if(hostIp){
					nicIpInfo.ip = hostIp;
					nicIpInfo.netmask = netMask;
					nicIpInfo.target_nic_name = targetNicName;
					nicIpInfo.target_nic_mac = targetHostMac;
					var result = standbyNetCardConfList.some(item=>{
						 if(item.selectNicname==targetNicName){
							 targetGateway = item.selectGateway;
						 }
					});
					nicIpInfo.target_gateway = targetGateway;
					nicIpList.push(nicIpInfo);
				}
			}
			if(nicIpList.length>0){
				ipMapInfo.nic_ip_info = nicIpList;
				agentMapInfo.agent_map_info = ipMapInfo;
				taskIpMapInfoList.push(agentMapInfo);
				// _takeoverIpMapListView.push(agentMapInfo);
			}
		}
		if(taskIpMapInfoList.length>0){
			_takeoverIpMapList = taskIpMapInfoList;
		}
		_standbyGatewayConf = getStandbyGateWayConf();
		var checkIp = checkIpFormat(_standbyGatewayConf);
		if(checkIp){  //校验IP正确性，需要同时满足IPV4和IPV6
			var takeoverBusinessIpMap = groupTakeoverBusinessIpMap();
			modifyTaskTakeoverNetwork(takeoverBusinessIpMap);
		}else{
			_takeoverIpMapList = [];
			UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP,LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
			return ;
		}
	}
	/**
	 * 修改任务接管网络配置
	 */
	var modifyTaskTakeoverNetwork = function(takeoverBusinessIpMap){
		var taskNetcardInfo = {};
		taskNetcardInfo.takeover_business_ip_map = takeoverBusinessIpMap;
		taskNetcardInfo.task_uuid = $("#task_uuid").val();
		var p = JSON.stringify(taskNetcardInfo);
		Metronic.blockUI({target: '#taskTakeoverNetworkConfModal',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'modifyTaskTakeOverNetwork',p:p}, function(d){
    		Metronic.unblockUI('#taskTakeoverNetworkConfModal');
    		var data = JSON.parse(d);
    		if(data.lev!="success"){
    			UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_MESSAGE5);
    			return;
    		}
    		if(OPREL(d)){
    			getTaskNetCardHistoryConf();
    			$('#taskTakeoverNetworkConfModal').modal('hide');
    		}
    	});
	}

	/**
	 * 接管回切网络配置
	 */
	var takeoverFailbackNetworkConf = function(){
		var failBackupHostuuid = $('#failBackHost').val();
		if(failBackupHostuuid=="0"){
			UIToastr.showWarning(LANG.UI_VOL_CDP_HOST_IP_SERVICE_MAP, LANG.UI_VOL_CDP_FAILBACK_TARGET_TIPS);
			return;
		}
		var p = {};
	   	p.uuid = $("#task_uuid").val();
    	p.task_type =_taskType;
    	p.failbackhost = failBackupHostuuid;

    	var params = JSON.stringify(p);
    	getTaskNetCardHistoryConf();
    	Metronic.blockUI({target: '#taskFailbackupModal',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getTaskNetworkConfInfo',p:params}, function(d){  //获取任务主机网卡信息
    		Metronic.unblockUI('#taskFailbackupModal');
    		var data = JSON.parse(d);
    		if(data.re==null){
    			$('#taskFailbackupModal').modal('hide');
    			$('#failbackTakeoverNetworkConfModal').modal({'width':'850px', 'height':'460px'});
    			$('.cancelnetworkconfmodal').unbind('click').click(closeNetworkConfModal);
    			$('.closenetworkconfmodal').unbind('click').click(closeNetworkConfModal);
    			_agentNetworkInfo = data['agent_nic_list'];
    			_standbyNetworkInfo = data['standby_nic_list'];
    			$('#failback_takeover_ip_server_conf').takeoverIpServerMapConfig({
					agentNetwork:_agentNetworkInfo,
					standbyNetwork: _standbyNetworkInfo,
					takeoverHisNetwork:[],
					failbackHisNetwork:_failbackHisBusinessIpMap,
					failbackupCationIp:_failbackCationIp,
					failbackupCationConf:true,
				});
    			$('#failback_standby_gateway_conf').takeoverStandbyGatewayConfig({
					agentNetwork:_agentNetworkInfo,
					standbyNetwork: _standbyNetworkInfo,
					takeoverHisNetwork:[],
					failbackHisNetwork:_failbackHisBusinessIpMap,
					failbackupCationIp:"",
					failbackupCationConf:true,
    			});
    		}else{
    			if(OPREL(d));
    		}
    	});
		$("#submit_failback_takeover_network_conf").unbind('click').click(submitHostNetworkConf);
	}

	/**
	 * 获取配置网卡描述信息，并渲染配置
	 */
	var submitHostNetworkConf = function(){
		var networCardConfInfo = {};
		var gateAwyConfDesList = [];

		_standbyGatewayConf = getStandbyGateWayConf();
		var checkIp = checkIpFormat(_standbyGatewayConf);
		if(!checkIp){
			_takeoverIpMapList = [];
			UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
			return false;
		}
		var standbyNetCardConfList = standbyNetCardConf();
		for(var i=0;i<_agentNetworkInfo.length;i++){
			var ipSetStr = "";
			var ipMapInfo = {};
			var nicIpList = [];
			var agentMapInfo  = {};
			ipMapInfo.nic_gateway = _agentNetworkInfo[i].gateway_address;
			ipMapInfo.nic_name = _agentNetworkInfo[i].name;
			ipMapInfo.nic_mac = _agentNetworkInfo[i].mac_address;
			var ipSet = _agentNetworkInfo[i].ip_set;
			if(!ipSet){break;}

			var liId = getUuid();
			var hostMac = $("#host_network_mac_"+i).text();  //遍历所有网卡
			//当前选中主机网卡名
			var activeNetCardText = $('#hostNetCardTab').find('li.active').filter(function() {
				return $(this).closest('ul').is('ul');
			}).text();

			if( $.trim(activeNetCardText) != _agentNetworkInfo[i].name){
				continue;
			}

			for(var j=0;j<ipSet.length;j++){
				var nicIpInfo = {};
				var standbyNetCardSelect =  "standby_network_card_"+i+j;
				var hostIp = $("#"+standbyNetCardSelect).find("option:selected").attr("host-ipdrr");
				var netMask = $("#"+standbyNetCardSelect).find("option:selected").attr("agent-netmask");
				var targetGateway = $("#"+standbyNetCardSelect).find("option:selected").attr("standby-gateway");
				var targetNicName = $("#"+standbyNetCardSelect).find("option:selected").text();
				var targetHostMac = $("#"+standbyNetCardSelect).val();
				var nicResult = false;
				nicResult = ipMapInfoList.some(item => {
					if (item.agent_map_info.nic_mac == _agentNetworkInfo[i].mac_address && hostMac == _agentNetworkInfo[i].mac_address ){
						return true;
					}else{
						return false;
					}
				});
				if(nicResult && $.trim(activeNetCardText) == _agentNetworkInfo[i].name){
					UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_HOST_IP_SERVICE_MAP_DP_TIPES);
					return;
				}
				if(hostIp){
					nicIpInfo.ip = hostIp;
					nicIpInfo.netmask = netMask;
					nicIpInfo.target_nic_name = targetNicName;
					nicIpInfo.target_nic_mac = targetHostMac;
					var result = standbyNetCardConfList.some(item=>{
						 if(item.selectNicname==targetNicName){
							 targetGateway = item.selectGateway;
						 }
					});
					var targetGatewayStr = targetGateway;
					if(targetGateway=="" || targetGateway==null){
						targetGatewayStr = "--"
					}
					nicIpInfo.target_gateway = targetGateway;
					nicIpList.push(nicIpInfo);
					var gatewayStr = "("+LANG.UI_DRILLS_NETWORK_WAY +":"+ targetGatewayStr +")";
					ipSetStr += hostIp + gatewayStr + LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF+ ":"+targetNicName+"  ";
				}
			}
			if(nicIpList.length>0){
				ipMapInfo.nic_ip_info = nicIpList;
				agentMapInfo.agent_map_info = ipMapInfo;
				agentMapInfo.conf_id = liId;
				agentMapInfo.conf_des = LANG.UI_VOL_CDP_STANDBY_NETCARD +": "+_agentNetworkInfo[i].name+" ["+ipSetStr+"] ";
				ipMapInfoList.push(agentMapInfo);
				_takeoverIpMapListView.push(agentMapInfo);
			}
		}

		if(ipMapInfoList.length>0){
			$('.failback_takeover_ip_map_list li').remove();
			_takeoverIpMapList = ipMapInfoList;
			hostGatewayConfDes();
		}
		closeNetworkConfModal();
	}
	/**
	 * 获取配置网卡描述信息，并渲染
	 */
	var hostGatewayConfDes = function(){
		var des = "";
		for(var i=0;i<_takeoverIpMapList.length;i++){
			var conf_id = _takeoverIpMapList[i].conf_id;
			var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ _takeoverIpMapList[i].conf_id
				+'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
				+ _takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
				+ _takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  _takeoverIpMapList[i].conf_id +'" >'
				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
			$('.failback_takeover_ip_map_list').append(des);
		    $('.takeoverNetcardTips').popover();	   //初始化tips

			$('.takeover_netcard_del'+ conf_id ).bind("click",{index:i},clickHandler);
		}
	}

	/**
	 * 为每一个<li>绑定点击事件，并处理移除数组操作
	 */
	var clickHandler = function(event){
		var className = this.className;
		var splitClassName = className.split('takeover_netcard_del');
		var eventConfID = splitClassName[1];
		$('.popover.in').remove();
		var index = event.data.index;
		var confId;
		var list = _takeoverIpMapListView;
		for(var i = 0;i<list.length;i++){
			confId =  list[i].conf_id;
			if(eventConfID == confId){
		        $('#takeover_netcard_' + confId).remove();
				_takeoverIpMapListView.splice($.inArray(_takeoverIpMapListView[i],_takeoverIpMapListView),1);
				break;
			}
		}
		for(var n=0;n<ipMapInfoList.length;n++){
			if(confId == ipMapInfoList[n].conf_id){
				ipMapInfoList.splice($.inArray(ipMapInfoList[n],ipMapInfoList),1);
				break;
			}
		}
        for(var j=0;j<_takeoverIpMapList.length; j++){
            if(confId == _takeoverIpMapList[j].conf_id){
            	_takeoverIpMapList.splice($.inArray(_takeoverIpMapList[j],_takeoverIpMapList),1);
            	break;
            }
        }
	}
	/**
	 * 获取备机网卡配置
	 */
	var getStandbyGateWayConf = function(){
		var list = [];
		for(var i = 0; i<_standbyNetworkInfo.length;i++){
			var gatewayInfo = {};
			var hostMac = _standbyNetworkInfo[i].mac_address
			hostMac = hostMac.replace(/:/g,'');

			gatewayInfo.netcard = _standbyNetworkInfo[i].name;
			var hostGateway = $('#standby_netcard_'+hostMac).val();
			if(hostGateway==""){
				hostGateway = "0.0.0.0";
			}
			gatewayInfo.gateway = hostGateway;
			list.push(gatewayInfo);
		}
		return list;
	}

	//获取备机网卡信息，用于替换主机映射网卡中的备机网关
	var standbyNetCardConf = function(){
		var standbyNetCardInfo = {};
		var standbyNetCardList = [];
		for(var i = 0;i<_standbyNetworkInfo.length;i++){
			var standbyGatewaySelectId = "standby_netcard_macaddress_"+_standbyNetworkInfo[i].name;
			var selectNicname = $("#"+standbyGatewaySelectId).find("option:selected").attr("agent-nicname");
			var selectGateway = $("#"+standbyGatewaySelectId).val();
			standbyNetCardInfo.selectNicname = selectNicname;
			standbyNetCardInfo.selectGateway = selectGateway;
			standbyNetCardList.push(standbyNetCardInfo);
		}
		return standbyNetCardList;
	}


	/**
	 * 关闭回切网络配置窗口
	 */
	var closeNetworkConfModal = function(){
		$('#failbackTakeoverNetworkConfModal').modal('hide');
		$('#taskFailbackupModal').modal({'width':'925px', 'height':'460px'});
	}

	/**
	 * 获取并展开接管回切相关配置View
	 */
	var takeoverFailbackupConf = function(){
		isInit = false;
		initVolInfoGrid();
		intmemorycache();//内存缓存开关切换事件
		transferSwitch();  //传输配置开关切换事件
		backupFailbackDataToBackupServer();  //回切数据到备份服务器

		getTaskNetCardHistoryConf(); //获取回切网卡映射历史配置

		$(".tasktransfernetworkDiv").hide();

		var p = {};
    	p.uuid = $("#task_uuid").val();
    	p.task_type =_taskType;
    	p = JSON.stringify(p);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getVolCdpTaskHostConf',p:p}, function(d){
			Metronic.unblockUI("#jobDetail");
    		var data = JSON.parse(d);
    		data = data[0];
    		_takeoverConf = data;
    		var task_master_uuid = data['task_master_uuid']; //当前任务主机客户端uuid
    		var failbackup_target_uuid = data['failbackup_target_uuid'];
    		var file_cache_alloc_space = data['file_cache_alloc_space'];
    		var file_cache_storage_path = data['file_cache_storage_path'];
    		var host_list = JSON.parse(data['host_list']);
    		_selectMapVol = [];
    		var memory_cache_alloc_space = data['memory_cache_alloc_space'];
    		var transport_compress_flag = data['transport_compress_flag'];
			var transport_compress_method = data['transport_compress_method'];  //压缩方式
			if(transport_compress_method!=0){
				_failTransferCompressValue = transport_compress_method;
			}
    		var mirror_backup_flag = data['mirror_backup_flag'];
			_agentOsType = data['standby_os_type'];
    		var transport_thread_num = data['transport_thread_num'];
    		var transport_block_size = data['transport_block_size'];

    		var transport_encrypt_flag = data['transport_encrypt_flag'];
			var transport_encrypt_method = data['transport_encrypt_method'];  //传输方式

    		var io_replication_mode = data['io_replication_mode']; //IO 复制模式
    		var node_uuid = data['node_uuid'];  //任务所在存储节点信息
    		var default_storage_uuid = data['storage_uuid']; //已配置存儲信息；如果已配置接管回切，獲取回切配置信息，未配置則获取任务配置信息
			_failbackHisStorageuuid = default_storage_uuid;
			var rebuildPartitionFlag = parseInt(data['rebuild_partition_flag']);
			var netModel = data['net_model'];
			if(netModel==2){  //网络模式为2
				$(".tasktransfernetworkDiv").show();
				initNetworkList(node_uuid);  //加载传输网络
			}else if(netModel ==1){  //网络模式为1
				$(".tasktransfernetworkDiv").hide();
				$('#taskTransferNetwork').empty();
			}else{
				$(".tasktransfernetworkDiv").hide();
				initNetworkList(node_uuid);  //加载传输网络
			}

    		if(io_replication_mode==0){  //未配置，默认复制为异步
    			io_replication_mode = 2;
    		}
    		if(file_cache_storage_path ==""){
    			file_cache_storage_path = LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
    		}
			initNetworkList(node_uuid);  //加载传输网络
    		if(host_list.length>0){
    			fileCachePathChange();
    			// loadStorageInfo(node_uuid,default_storage_uuid);
				getBackupServerNode(); //获取所有存储节点，默认选中回切任务关联存储所在节点
    			$('#taskFailbackupModal').modal({'width':'925px', 'height':'460px'});
    			_catBackVolSet = [];
    			var data = _volGrid.getDataTable().data();
    	    	loadTaskSourceVolInfo(data);
    	    	var unit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；
    	    	if(file_cache_alloc_space){
    	    		$("#file_cache_size").val(file_cache_alloc_space/unit);
    	    	}
    	    	if(memory_cache_alloc_space){
    	    		$('#memory_cache_size').val(memory_cache_alloc_space/unit);
    	    	}
				if(file_cache_storage_path ) {
					file_cache_storage_path = file_cache_storage_path.replace(/\\\\/g, "\\")
				}

        		$('#takeoverFileCachePath').val(file_cache_storage_path);
				$('#takeoverFileCachePath').prop('title', file_cache_storage_path);

        		$('#transfer_thread_number').val(transport_thread_num);
        		$('#transfer_datapackage_size').val(transport_block_size);

				$('#cutbackTransferEncryptMethod').val(transport_encrypt_method);
        		$('#cutBacktransferCompressGrade').val(transport_compress_method);

        		if(io_replication_mode==1){ //同步
        			$("#ioReplicationSyncModle").prop("checked","checked");
        			$.uniform.update()
        		}else if(io_replication_mode==2){ //异步
        			$("#ioReplicationAsyncModle").prop("checked","checked");
        			$.uniform.update()
        		}
        		//回切生产数据是否备份到服务端标志位
        		if(mirror_backup_flag==CONF.FLAG.SET){
        			$('#fbMirrorDataToServer').bootstrapSwitch('state', true);
					$('.selectstoragediv').show();
        		}else{
        			$('#fbMirrorDataToServer').bootstrapSwitch('state', false);
					$('.selectstoragediv').hide();
        		}

        		if(transport_compress_flag==CONF.FLAG.SET){
        			$('#takeoverTranCompressSwitch').bootstrapSwitch('state', true);
        		}else{
        			$('#takeoverTranCompressSwitch').bootstrapSwitch('state', false);
        		}

        		if(transport_encrypt_flag==CONF.FLAG.SET){
        			$('#takeoverTranEncryptSwitch').bootstrapSwitch('state', true);
        		}else{
        			$('#takeoverTranEncryptSwitch').bootstrapSwitch('state', false);
        		}
    			$('.failback_takeover_ip_map_list').html('');  //清空历史配置
				parseHostInfo(host_list,failbackup_target_uuid, rebuildPartitionFlag);
				parseFailBackHisNetCardConf();
				ipMapInfoList = [];  //清空主机IP映射
    		}else{
    			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_SELECT_DATABASE_MESSAGE);
    			return false;
    		}
    	});
	}

	//内存缓存开关切换事件
	var intmemorycache = function () {
		$('#memorycachechange').unbind('click').on('click', function () {
			if($('#memorycacheset').is(':checked')){
				$('.takeovermemorycachediv').show();
			}else{
				$('.takeovermemorycachediv').hide();
			}

		});
	}
	//回切传输配置开关
	var transferSwitch = function (){
		$('#takeoverTranEncryptSwitch').on('switchChange.bootstrapSwitch', takeoverTranEncryptChange);
		$('#takeoverTranCompressSwitch').on('switchChange.bootstrapSwitch', takeoverTranCompressChange);
		$('#cutBacktransferCompressGrade').unbind('change').bind('change',(transferCompressGradeChange));
	}
	/**
	 * 传输压缩切换事件
	 */
	var transferCompressGradeChange = function (){
		_failTransferCompressValue = $("#cutBacktransferCompressGrade").val();
	}
	//回切传输加密配置

	var takeoverTranEncryptChange = function (){
		if(this.checked){
			_failTransferEncryptValue = 1;
			$('.transfer-encrypt-method-form').hide();  //卷cdp模块暂时不支持国密加密方式，开启后传输加密方式的默认值未rsa
		}else{
			$('.transfer-encrypt-method-form').hide();
			_failTransferEncryptValue = 0;
		}
	}
	//回切传输数据压缩
	var takeoverTranCompressChange = function (){
		if(this.checked){
			$('.transferCompressGradeDiv').show();
			$('#cutBacktransferCompressGrade').val(_failTransferCompressValue);
		}else{
			$('.transferCompressGradeDiv').hide();
		}
	}
	/**
	 * 备份回切生成数据到备份服务器绑定事件
	 */
	var backupFailbackDataToBackupServer = function(){
		$('#fbMirrorDataToServer').on('switchChange.bootstrapSwitch', fbMirrorDataToServerChange);  //备份回切生成数据到备份服务器
	}
	/**
	 * 备份回切生成数据到备份服务器
	 */
	var fbMirrorDataToServerChange = function(){
		if(this.checked){
			$('.selectstoragediv').show();
		}else{
			$('.selectstoragediv').hide();
		}
	}
	/**
	 * 切换文件缓存路径方式
	 */
	var fileCachePathChange = function(){
		$('#customFileCachePath').click(function(){
			$('#defaultFileCachePath').show();
			$('#customFileCachePath').hide();
			$('#takeoverFileCachePath').removeAttr("readonly");
			$('#takeoverFileCachePath').val("");
		});

		$('#defaultFileCachePath').click(function(){
			$('#defaultFileCachePath').hide();
			$('#customFileCachePath').show();
			$('#takeoverFileCachePath').attr("readonly","readonly");
			$('#takeoverFileCachePath').val(LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT);
			$('#takeoverFileCachePath').prop('title', LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT);
		});

	}
	/**
	 * @function 获取存储信息，默认选中任务已配置或回切已配置存储信息
	 */
	var getBackupServerNode = function(){
		var info = {};
		info.default_storage_uuid = _failbackHisStorageuuid;
		var jsonData = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getStorageNodeSelect',p:jsonData}, function(d){
			var data = JSON.parse(d);
			var softselect = $('#selectnode');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				var selected = "";
				if(data[i].select){
					selected = 'selected="selected"';
				}
				var option = $("<option  "+selected+">").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
			loadStorageInfo();
		});
		$("#selectnode").unbind('change').bind('change',(nodeselectChange));
		//resetRebuildPartSwitch();
	}
	/**
	 * 切换目标节点
	 */
	var nodeselectChange = function (){
		var node_uuid = $('#selectnode').val();
		loadStorageInfo();
	};

	/**
	 * 加载节点所在存储，默认选中回切任务历史配置存储设备
	 */
	var loadStorageInfo = function(){
		var data = {};
		data.nodeuuid = $('#selectnode').val();
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
			var data = JSON.parse(d);
			var softselect = $('#selectstorage');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				if(data[i].type==CONF.BD_STORAGE_TYPE.CLOUD){
					continue;
				}
				var selected = '';
				if(_failbackHisStorageuuid == data[i].uuid){
					selected = "selected";
				}
				var option = $("<option "+ selected+">").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
		});
	}


	/**
	 * @function 填充任务数据源(卷)信息
	 * @prams: 当前任务对应的卷信息,此处只取自动接管任务和手动接管任务;
	 */
	var loadTaskSourceVolInfo = function(volData){
		if(_takeoverCutBackVolTab!=undefined){
			clearTable(_takeoverCutBackVolTab,"cutBackVolTable");
		}
		if(_takeoverCutBackVolTab == undefined){
			_takeoverCutBackVolTab =  $('#cutBackVolTable').DataTable(gettableDefaultsOpt());
		}
		var currentRunningStage = _taskCurrentRunningStage;  //任务执行阶段
		for(var i=0;i<volData.length;i++){
			var result = {};
    		var volName = volData[i][2];
    		var volUUID = volData[i][12];
    		var volSize = volData[i][13];
    		var taskType = volData[i][9];
    		var volSizeValue = volData[i][17];
    		if((taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskType ==CONF.TASK_TYPE.VOL_CDP_REPLICATION ) && currentRunningStage !=CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER && currentRunningStage !=CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){
    			volSizeValue = volData[i][15];
    		}
    		var failbackTargetVoluuid = volData[i][14];  // 回切目标卷
			var recoveryTargetDiskUuid = volData[i][18]; // 回切目标磁盘
    		//手动接管任务或任务处于自动接管阶段
    		if(_taskType==CONF.TASK_TYPE.VOL_CDP_TAKEOVER ||
				currentRunningStage== CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER ||
				currentRunningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING)
    		{
    			failbackTargetVoluuid = volData[i][15];
    		}
    		_takeoverCutBackVolTab.row.add([
    			volName,
    			volSize,
				'<select class="form-control input-sm" name="catbacktargetvol" id='+"catback_volume_"+i+' >\
				<option value = "'+volSizeValue+'" data-hostvoluuid="'+volUUID+'" data-option ="0" >'+LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_VOL+' </option></select>',
				failbackTargetVoluuid,
				volUUID,
				recoveryTargetDiskUuid
			]).draw();
    	}
		$("#failBackHost").unbind('change').bind('change',(failBackupHostChange));
	}
	/**
	 * 解析回切历史网卡映射配置
	 */
	var parseFailBackHisNetCardConf = function(){
		var failbackHisNetConf = _failbackHisBusinessIpMap;
		if(failbackHisNetConf.length>0){
			for(var i =0;i < failbackHisNetConf.length;i++){
				var nicName = failbackHisNetConf[i].nic_name;
				var nicMac = failbackHisNetConf[i].nic_mac;
				var nicGateway = failbackHisNetConf[i].nic_gateway;
				var targetNicInfo = failbackHisNetConf[i].nic_ip_info;
				var gatewayStr = "";
				for(var j= 0;j <targetNicInfo.length;j++){
					var hostIp = targetNicInfo[j].ip;
					var hostNetMask = targetNicInfo[j].netmask;
					var targetGateway = targetNicInfo[j].target_gateway;
					var targetNicName = targetNicInfo[j].target_nic_name;
					gatewayStr += hostIp+"("+LANG.UI_DRILLS_NETWORK_WAY +":"+ targetGateway +")" + LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF +":"+targetNicName;
				}
				var nicConfDes = LANG.UI_VOL_CDP_STANDBY_NETCARD +": " + nicName+"["+gatewayStr+"]";
				historyHostGatewayConfDes(nicName,failbackHisNetConf,nicConfDes);
			}
		}
	}
	/**
	 * 获取接管回切历史网卡网卡配置描述信息，并渲染
	 */
	var historyHostGatewayConfDes = function(nicName,failbackHisNetConf,nicConfDes){
		var des = "";
		var conf_id = nicName;
		var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+conf_id
			+'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
			+ nicConfDes + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
			+ nicConfDes + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  conf_id +'" >'
			+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
		$('.failback_takeover_ip_map_list').append(des);
		$('.takeoverNetcardTips').popover();	   //初始化tips
		_modifyFailbackHisBusinessIpMap = _failbackHisBusinessIpMap;
		$('.takeover_netcard_del'+ conf_id ).bind("click",clickFailBakHistConfDesc);
	}
	/**
	 * 删除回切历史网卡配置
	 */
	var clickFailBakHistConfDesc = function(){
		var className = this.className;
		var splitClassName = className.split('takeover_netcard_del');
		var eventConfID = splitClassName[1];
		for(var i=0;i<_modifyFailbackHisBusinessIpMap.length;i++){
			var confId =  _modifyFailbackHisBusinessIpMap[i].nic_name;
			if(eventConfID == confId){
				$('#takeover_netcard_' + confId).remove();
				_modifyFailbackHisBusinessIpMap.splice($.inArray(_modifyFailbackHisBusinessIpMap[i],_modifyFailbackHisBusinessIpMap),1);
				break;
			}
		}
	}

	/**
	 * @function:解析可供回切的目标主机及卷信息
	 */
	var parseHostInfo = function (host_list,failbackup_target_uuid, rebuildPartitionFlag){
		var hostSelect = $('#failBackHost');
		hostSelect.empty();
		var volInfo = [];
		var targetAgentType = 1;
		var option = $("<option>").text(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_HOST_SELECT).val(0);
		hostSelect.append(option);
		hostSelect.append(0);
		var selectIndex = -1;
		for(var i=0; i<host_list.length; i++){
			var hostUuid = host_list[i].uuid;
			var agentType = parseInt(host_list[i].agent_type);
			var taskIsRunning = host_list[i]['task_is_running'];
			var netModel = host_list[i]['net_model'];
			option = $("<option>").text(host_list[i].text).val(hostUuid)
				.attr('data-taskisrunning',taskIsRunning)
				.attr('agent-type', agentType)
				.attr('net-model',netModel);
			hostSelect.append(option);
			if(hostUuid == failbackup_target_uuid && taskIsRunning !=CONF.FLAG.SET){
				hostSelect.val(failbackup_target_uuid);  //默认选中任务所在客户端
				if (agentType === 2) {  // LiveCD显示重建分区，默认打开
					targetAgentType = 2;
					$('#rebuildPartDiv').show();
					if (rebuildPartitionFlag === CONF.FLAG.SET) {
						$('#rebuildPartSwitch').bootstrapSwitch('state', true);
						volInfo = host_list[i].host_disk_info;
					} else {
						$('#rebuildPartSwitch').bootstrapSwitch('state', false);
						volInfo = host_list[i].host_vol_info.vol_info;
					}
				} else {
					volInfo = host_list[i].host_vol_info.vol_info;
				}
			}
			selectIndex = $.inArray(failbackup_target_uuid, host_list[i]);
		}

		if (!volInfo.length && failbackup_target_uuid) {
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL_OR_DISK_MESSAGE);
			return;
		}

		var tab = $('#cutBackVolTable');
		var table = tab.dataTable();
		var rows = table.fnGetNodes();
		for (var j = 0; j < rows.length; j++) {
			var row = table.fnGetData(rows[j]);
			var confVolName = row[0];
			var hisFailBackupVoluuid = row[3];
			var taskVoluuid = row[4];
			var hisRecoveryTargetDiskUuid = row[5];  // 重建分区要使用5
			var selectElement = $('#catback_volume_' + j);
			if (targetAgentType === 2 && rebuildPartitionFlag === CONF.FLAG.SET) {  // 将卷改为磁盘
				$('#catback_volume_' + j + ' option').html(LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_DISK);
				$('#dataDiskVol').html(LANG.UI_VOL_CDP_TAKEOVER_TARGET_DISK);
			} else {
				$('#catback_volume_' + j + ' option').html(LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_VOL);
				$('#dataDiskVol').html(LANG.UI_VOL_CDP_TAKEOVER_TARGET_VOL);
			}

			if(!volInfo){volInfo =[];}
			for(var i=0;i<volInfo.length;i++){
				var displayName = volInfo[i]['display_name'];
				var capacity = volInfo[i]['capacity'];
				var volUuid = volInfo[i]["uuid"];
				var capacityValue = volInfo[i]["capacity_value"];
				var canSelect = volInfo[i]['can_select'];
				var opText = displayName+LANG.UI_VOL_CDP_RECOVER_TOTAL_CAPACITY+capacity+')';
				var isChecked = '';
				var catBackVolSetObj = {};
				if (targetAgentType === 2 && rebuildPartitionFlag === CONF.FLAG.SET) {  // LiveCD重建分区
					if (hisRecoveryTargetDiskUuid === volUuid) {
						isChecked = 'selected';
						catBackVolSetObj.vol_uuid = taskVoluuid;
						catBackVolSetObj.vol_size = parseInt(selectElement.val());
						catBackVolSetObj.failback_target_vol_uuid = '';
						catBackVolSetObj.failback_target_disk_uuid = hisFailBackupVoluuid;
						catBackVolSetObj.failback_target_disk_size = capacityValue;
						_catBackVolSet.push(catBackVolSetObj);
					}
				} else {
					if(hisFailBackupVoluuid === volUuid){
						isChecked = 'selected';
						catBackVolSetObj.vol_uuid = taskVoluuid;
						catBackVolSetObj.vol_size = parseInt(selectElement.val());
						catBackVolSetObj.failback_target_vol_uuid = hisFailBackupVoluuid;
						catBackVolSetObj.failback_target_disk_uuid = '';
						catBackVolSetObj.failback_target_disk_size = capacityValue;
						_catBackVolSet.push(catBackVolSetObj);
					}
				}
				option = $("<option " + isChecked + ">").text(opText).val(volUuid)
					.attr('data-capacity', capacityValue)
					.attr('data-option','1');
				if (targetAgentType !== 2 && !volInfo[i]['can_select']) {
					option.attr('disabled',"disabled");
				}

				selectElement.append(option);
				selectElement.unbind('change').bind('change',(catBackTargetVolChange));
			}
		}
	}

	//初始化节点传输网络列表
	var initNetworkList = function(nodeuuid){
		var data = {};
		data.nodeuuid = nodeuuid;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeNetworkList',p:p}, function(d){
			var data = JSON.parse(d);
			var transferNetwork = $('#taskTransferNetwork');
			transferNetwork.empty();
			for(var i=0; i<data.length; i++){
				var name = data[i].ip + ":" + data[i].port;
				if(data[i].alias_name != ""){
					name += "(" + data[i].alias_name +")";
				}
				var option = '<option  value="' + data[i].network_uuid + '">' + name + '</option>';
				transferNetwork.append(option);
			}

			oldNode = $('#selectnode').val();

		});
	}

	/**
	 * @function 写入以回切的主机卷信息，加载选择的备用服务器卷信息到表格的select
	 */
	var failBackupHostChange = function(){
		_catBackVolSet =[];
		var data = _volGrid.getDataTable().data();
		var taskIsRunning = $(this).find("option:selected").attr("data-taskisrunning");//主机对应卷uuid
		var agentType = parseInt($(this).find("option:selected").attr("agent-type"));//主机对应类型
		var agentNetModel = parseInt($(this).find("option:selected").attr("net-model"));//主机对应网络类型
		if(agentNetModel == 2){  //网络模式为2
			$(".tasktransfernetworkDiv").show();
		}else{
			$(".tasktransfernetworkDiv").hide();
		}
		$('#rebuildPartDiv').hide();  // 默认隐藏重建分区选项

		var options = $(this).find("option");
		if (parseInt(taskIsRunning) === CONF.FLAG.SET) {
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL,LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_MESSAGE6);
			options.first().prop("selected", true);
			return;
		}

		for(var j = 0;j<data.length;j++){
			$('#catback_volume_'+j+' option').not('option:first').remove();
			$("#catback_volume_"+j).unbind('change').bind('change',(catBackTargetVolChange));
		}

		var failBackupHostuuid = $('#failBackHost').val();
		if (failBackupHostuuid === '0') {  // 表示选中了<请选择回切目标主机>
			return;
		}

		var data = JSON.stringify({agent_uuid:failBackupHostuuid});
		Metronic.blockUI({target: "#taskFailbackupModal",animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'updateAgentVolInfo',p:data}, function(d){
			Metronic.unblockUI("#taskFailbackupModal");
			if(OPREL(d)){
				// getSelectHostVolTarget(failBackupHostuuid);
//				loadStandbyNetworkInfo(failBackupHostuuid); //获取备机网卡信息
				if (agentType === 2) {  // liveCD默认开启重建分区, 需要开启重建分区
					$('#rebuildPartDiv').show();
					$('#rebuildPartSwitch').bootstrapSwitch('state', true);  // 触发switchChange.bootstrapSwitch事件，但没有监听，需要手动调用获取数据
					loadTargetHostVolOrDisk(1);
				} else {
					$('#rebuildPartSwitch').bootstrapSwitch('state', false);
					loadTargetHostVolOrDisk(2);  // 手动调用加载目标卷信息
				}
			}
		});
	}

	//获取更新客户端的卷信息
	var getSelectHostVolTarget = function(hostuuid){
		var data = JSON.stringify({agent_uuid:hostuuid});
		Metronic.blockUI({target: "#failBackupVolMapdiv",animate: true});  // 加一个加载过程，避免下拉框因获取数据过程中为空
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getAgentVolInfo',p:data}, function(data){
			Metronic.unblockUI("#failBackupVolMapdiv");
			var hostVolInfo = JSON.parse(data);
			var volInfo = hostVolInfo.vol_info;
			loadFailbackVolTarget(volInfo);
		});
	}

	/**
	 * 填充选择回切目标客户端对应的卷信息
	 */
	var loadFailbackVolTarget = function(hostVolConf){
		var catbackTargetVol = $("select[name='catbacktargetvol']");
		$("select[name='catbacktargetvol'] option").html(LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_VOL);
		$('#dataDiskVol').html(LANG.UI_VOL_CDP_TAKEOVER_TARGET_VOL);
		for(var i=0;i<hostVolConf.length;i++){
			var displayName = hostVolConf[i]['display_name'];
			var capacity = hostVolConf[i]['capacity'];
			var volUuid = hostVolConf[i]["uuid"];
			var capacityValue = hostVolConf[i]["capacity_value"];
			var canSelect = hostVolConf[i]["can_select"];

			var opText = displayName+LANG.UI_VOL_CDP_RECOVER_TOTAL_CAPACITY+capacity+')';
			if(!canSelect){
				var option = $("<option>").text(opText).val(volUuid).attr('data-capacity', capacityValue).attr('data-option','1').attr('disabled',"disabled");
			}else{
				var option = $("<option>").text(opText).val(volUuid).attr('data-capacity', capacityValue).attr('data-option','1');
			}
			catbackTargetVol.append(option);
		}
	}

	var getSelectHostDiskTarget = function (hostUuid) {
		var data = JSON.stringify({agent_uuid: hostUuid});
		Metronic.blockUI({target: "#failBackupVolMapdiv",animate: true});  // 加一个加载过程，避免下拉框因获取数据过程中为空
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getAgentDiskInfo',p:data}, function(data){
			Metronic.unblockUI("#failBackupVolMapdiv");
			var diskInfo = JSON.parse(data);
			loadFailBackDiskTarget(diskInfo);
		});
	}

	var loadFailBackDiskTarget = function (diskInfo) {
		var catBackTargetVol = $("select[name='catbacktargetvol']");
		$("select[name='catbacktargetvol'] option").html(LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_DISK);
		$('#dataDiskVol').html(LANG.UI_VOL_CDP_TAKEOVER_TARGET_DISK);
		for (const disk of diskInfo) {
			var displayName = disk['display_name'];
			var capacity = disk['capacity'];
			var diskUuid = disk["uuid"];
			var capacityValue = disk["capacity_value"];
			var opText = displayName + LANG.UI_VOL_CDP_RECOVER_TOTAL_CAPACITY + capacity + ')';
			var option = $('<option>').text(opText).val(diskUuid).attr('data-capacity', capacityValue).attr('data-option', 1)
			catBackTargetVol.append(option);
		}
	}

	/**
	 * 计算回切卷/磁盘与目标卷的联系
	 */
	var catBackTargetVolChange = function(){
		var isRebuildPart = $('#rebuildPartSwitch').is(':checked');
		if (isRebuildPart) {  // 开启重建分区, 计算磁盘
			calculateFailBackTargetDisk(this);
		} else {  // 关闭重建分区, 计算分区
			calculateFailBackTargetVol(this);
		}
	}

	/**
	 * @function 选择回切目标卷信息,封装回切卷与目标卷的映射关系
	 * @param self 选择的目标卷select标签
	 */
	var calculateFailBackTargetVol = function (self) {
		var targetVolUuid = $(self).val();
		var capacity = $(self).find("option:selected").attr("data-capacity");
		var hostVolumeSize = $(self)[0][0].value;
		var hostVoluuid = $(self).find("option").attr("data-hostvoluuid");//主机对应卷uuid
		var options = $(self).find("option");
		var selectedIndex = $(self).prop('selectedIndex');
		if(Number(capacity)<Number(hostVolumeSize)){
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL_MESSAGE);
			options.first().prop("selected", true);

			var result = _catBackVolSet.some(item=>{
				if(item.vol_uuid==hostVoluuid){
					const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
					_catBackVolSet.splice(index,1);
				}
			});
			return;
		}
		if (_catBackVolSet.length>0 && selectedIndex!=0){
			var targetVolIsRepeat = true;  //目标卷是否被重复配置
			var result = _catBackVolSet.some(item=>{
				if(item.failback_target_vol_uuid==targetVolUuid){
					const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
					UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL_MESSAGE2);
					$(self).find("option").eq(0).prop("selected",true)
					if(index>=0){
						_catBackVolSet.splice(index, 1);  //根据下标移除指定元素
					}
					targetVolIsRepeat = false;
					return false;
				}
				if(item.vol_uuid==hostVoluuid && targetVolIsRepeat){  //该卷已配置目标卷，为其配置新的目标卷需要更改target_vol_uuid 的值
					const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
					if(selectedIndex!=0){
						_catBackVolSet[index].failback_target_vol_uuid = targetVolUuid;
						targetVolIsRepeat = false;
					}else{
						_catBackVolSet.splice(index, 1);  //根据下标移除指定元素
					}
					return;
				}
			});
			if(selectedIndex!=0 && targetVolIsRepeat){
				packageFbTargetVol(targetVolUuid,hostVoluuid, hostVolumeSize, capacity);
			}
		}else if(selectedIndex==0){
			var result = _catBackVolSet.some(item=>{
				if(item.vol_uuid==hostVoluuid){
					const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
					_catBackVolSet[index].failback_target_vol_uuid = targetVolUuid;
					_catBackVolSet.splice(index,1);
				}
			});
		}else{
			if(selectedIndex!=0){
				packageFbTargetVol(targetVolUuid,hostVoluuid, hostVolumeSize, capacity);
			}
		}
	}

	/**
	 * 封装组合符合条件的回切目标卷
	 */
	var packageFbTargetVol = function(targetVolUuid,hostVoluuid, sourceVolSize, targetSize, agentType = 1){
		var catBackVolSetObj = {};
		_selectMapVol.push(targetVolUuid);
		catBackVolSetObj.vol_uuid = hostVoluuid;
		catBackVolSetObj.vol_size = sourceVolSize;
		if (agentType === 2) {
			catBackVolSetObj.failback_target_vol_uuid = '';
			catBackVolSetObj.failback_target_disk_uuid = targetVolUuid;
		} else {
			catBackVolSetObj.failback_target_vol_uuid = targetVolUuid;
			catBackVolSetObj.failback_target_disk_uuid = '';
		}
		catBackVolSetObj.failback_target_disk_size = targetSize;
		_catBackVolSet.push(catBackVolSetObj);
	}

	var calculateFailBackTargetDisk = function (self) {
		// 数据源信息
		var hostVolumeSize = parseInt($(self)[0][0].value);
		var hostVolUuid = $(self).find("option").attr("data-hostvoluuid");  // 主机对应卷uuid

		// 回切机信息
		var targetDiskUuid = $(self).val(); // 回切磁盘uuid
		var capacity = parseInt($(self).find("option:selected").attr("data-capacity"));
		var options = $(self).find("option");
		var selectedIndex = parseInt($(self).prop('selectedIndex'));

		_catBackVolSet.some(item => {  // 移除之前选中的磁盘
			if (item.vol_uuid === hostVolUuid) { // 需要将当前卷移除
				const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid === hostVolUuid);
				_catBackVolSet.splice(index, 1);
			}
		});

		if (selectedIndex === 0) {  // 选中了<请选择映射磁盘>
			return;
		}

		if (capacity < hostVolumeSize) {  // 选择的磁盘容量小于当前的卷容量
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_DISK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL_MESSAGE);
			options.first().prop("selected", true);
			return;
		}

		// 磁盘可以被多个数据源选中, 因此这里计算已选中磁盘的数据源的总卷容量
		var allVolCapacity = hostVolumeSize;
		_catBackVolSet.some(item => {
			if (item.failback_target_disk_uuid === targetDiskUuid) {  // 选中了这个磁盘
				if (item.vol_uuid !== hostVolUuid) {  // 不能计算当前卷大小，因为上面已经加上了
					allVolCapacity += item.vol_size;
				}
			}
		});
		if (allVolCapacity > capacity) {  // 数据源总大小大于磁盘大小，需要提示错误
			UIToastr.showWarning(
				LANG.UI_VOL_CDP_RECOVER_CONFIGURE_DISK,
				LANG.UI_OS_PLUG_GOAL_RECOVERY + LANG.UI_VOL_CDP_RECOVER_DISK + LANG.UI_VOL_CDP_RECOVER_CAPACITY_GREATER_HOST
			);
			options.first().prop("selected", true);
			return;
		}
		packageFbTargetVol(targetDiskUuid, hostVolUuid, hostVolumeSize, capacity, 2);
	}
	/**
	 * @function 失去焦点对路径的判断
	 */
	var blurInput = function () {
		var takeoverFileCachePath = $("#takeoverFileCachePath").val();
		if(takeoverFileCachePath!=LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT){
			if(!checkFilePath(takeoverFileCachePath,_agentOsType)){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
				return false;
			}
		}
	}
	/**
	 * @function 提交接管回切相关配置
	 */
	var catBackTaskSubmit = function (){
		if(_taskCurrentRunningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC ||
			_taskCurrentRunningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC ||
			_taskCurrentRunningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_STAGE_COMIT_TIPS);
			return false;
		}
		var data = _volGrid.getDataTable().data();

		if(data.length>_catBackVolSet.length){
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_TARGET_VOL_MISMATCH);
			return false;
		}
		//目标主机
		var failBackHost = $("#failBackHost").val();
		if(failBackHost=="" || failBackHost==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_MESSAGE);
			return false;
		}
		//回切映射卷
		if(_catBackVolSet.length ==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_MESSAGE2);
			return false;
		}
		//文件缓存路径
		var takeoverFileCachePath = $("#takeoverFileCachePath").val(); //文件缓存路径,当前阶段填充默认值,后期自定义时需要校验路径是否存在
		if(takeoverFileCachePath==LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT){
			takeoverFileCachePath = "";
		} else {
			if(!checkFilePath(takeoverFileCachePath,_agentOsType)){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
				return false;
			}
		}


		var blockUnit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；
		//文件缓存大小
		var fileCacheAllocSpaceValue = parseInt($("#file_cache_size").val());
		var fileCacheAllocSpace = fileCacheAllocSpaceValue * blockUnit;
		//内存缓存大小
		var takeoverMemoryCacheSize = parseInt($('#memory_cache_size').val());
		var memoryCacheAllocSpace = takeoverMemoryCacheSize * blockUnit;

		var transportCompressFlag = CONF.FLAG.UNSET;
		if($('#takeoverTranCompressSwitch').is(':checked')){
			transportCompressFlag = CONF.FLAG.SET;
		}


		var transportEncryptFlag = CONF.FLAG.UNSET;
		if($('#takeoverTranEncryptSwitch').is(':checked')) {
			transportEncryptFlag = CONF.FLAG.SET;
		}


		//回切数据备份到服务器器
		var mirrorBackupFlag = CONF.FLAG.UNSET;
		if($('#fbMirrorDataToServer').is(':checked')){
			mirrorBackupFlag = CONF.FLAG.SET;
		}
		var ioReplication = $('input:radio[name="task_io_replication_modle"]:checked').val(); //IO 复制模式
		var storage_uuid = $('#selectstorage').val();

		var transferThreadNumber = $('#transfer_thread_number').val();  //传输线程个数
		if(transferThreadNumber>4){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE1);
			$("#volcdpThreadNum").val(1);
        	return false;
		}
		if(transferThreadNumber<1){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
			$("#volcdpThreadNum").val(1);
        	return false;
		}

		var transportBlockSize = $('#transfer_datapackage_size').val(); //传输数据包大小
		var transportBlockSizeValue = transportBlockSize * 1024*1024;
		// var transferEncryptMethod = $('#cutbackTransferEncryptMethod').val();  //传输加密方法,模块暂不支持国密，暂时屏蔽
		var transferEncryptMethod = _failTransferEncryptValue;
		var transferCompressGrade = $('#cutBacktransferCompressGrade').val();  //传输压缩方法

		//封装回切配置消息结构
		var catBackParams = {};
		catBackParams.task_uuid = $('#task_uuid').val();
		catBackParams.failback_target_agent_uuid = failBackHost;
		catBackParams.memory_cache_alloc_space = memoryCacheAllocSpace;
		catBackParams.file_cache_alloc_space = fileCacheAllocSpace;
		catBackParams.file_cache_storage_path = takeoverFileCachePath;
		catBackParams.monitor_data_io_replication_mode = ioReplication;
		catBackParams.transport_encrypt_flag = transportEncryptFlag;
		catBackParams.transport_compress_flag = transportCompressFlag;
		catBackParams.encrypt_method = transferEncryptMethod;
		catBackParams.compress_method = transferCompressGrade;

		catBackParams.rebuild_partition_flag = $('#rebuildPartSwitch').is(':checked') ? 1 : 2;  // 是否开启了重建分区
		catBackParams.vol_set = _catBackVolSet;
		catBackParams.vol_set.map((item, index) => {  // 删除多余的键
			delete catBackParams.vol_set[index].vol_size;
			delete catBackParams.vol_set[index].failback_target_disk_size;
		});
		catBackParams.storage_uuid = storage_uuid;
		catBackParams.transport_thread_num = transferThreadNumber;
		catBackParams.transport_block_size = transportBlockSizeValue;
		catBackParams.mirror_backup_flag = mirrorBackupFlag;  //回切数据是否备份
		catBackParams.network_uuid = $("#taskTransferNetwork").val();
		var businessIpMap = groupTakeoverBusinessIpMap();
		if(businessIpMap.length==0){
			businessIpMap = _modifyFailbackHisBusinessIpMap;
		}

		catBackParams.failback_business_ip_map = businessIpMap;
		catBackParams.takeover_business_ip_map = [];
		if(!$('#memorycacheset').is(':checked')) {
			catBackParams.memory_cache_alloc_space = 0;
		}
		var p = JSON.stringify(catBackParams);
		Metronic.blockUI({target: '#taskFailbackupModal',animate: true});

    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'takeOverCutBack',p:p}, function(d){
    		Metronic.unblockUI('#taskFailbackupModal');

    		var data = JSON.parse(d);
    		if(data.lev!="success"){
    			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_MESSAGE5);
    			return;
    		}
    		if(OPREL(d)){
    			$('#taskFailbackupModal').modal('hide');
				isInit = true;
				initVolInfoGrid();
    		}
    	});
	};
	var catBack= function (){
		$('#taskFailbackupModal').modal('hide');
		isInit = true;
		initVolInfoGrid();
	};

	/**
	 * 组合接管业务网络
	 */
	var groupTakeoverBusinessIpMap = function(){
		var takeoverIpMap = [];
		for(var i = 0;i<_takeoverIpMapList.length;i++){
			var ipMapConf = _takeoverIpMapList[i].agent_map_info;
			takeoverIpMap.push(ipMapConf);
		}

		for(var j=0;j<takeoverIpMap.length;j++){
			var nicIpInfo =  takeoverIpMap[j].nic_ip_info;
			for(var i =0;i<nicIpInfo.length;i++){
				var nicName = nicIpInfo[i].target_nic_name;
				var standbyGateway = replaceStandbyNetcard(nicName);
				if(standbyGateway!=""){
					nicIpInfo[i].target_gateway = standbyGateway;
				}
			}
		}
		return takeoverIpMap;
	}
	/**
	 * 因后台调整消息结构困难，这里web将备机配置的网卡对应网关赋值给主机映射的备机网关
	 */
	var replaceStandbyNetcard = function(hostCardName){
		var gateway = "";
		for(var i = 0;i<_standbyGatewayConf.length;i++){
			if(_standbyGatewayConf[i]["netcard"] == hostCardName){
				gateway = _standbyGatewayConf[i]["gateway"];
				break;
		    }
		}
		return gateway;
	}
	//获取当前任务配置的卷信息
	var writeInCutBackHostVol = function (){
		var takeoverConf = _takeoverConf;
		var host_list = JSON.parse(takeoverConf['host_list']);
		if(_takeoverCutBackVolTab == undefined){
			_takeoverCutBackVolTab =  $('#cutBackVolTable').DataTable(gettableDefaultsOpt());
		}
		var hostVolConf = host_list[0].host_vol_info;
		for(var i=0;i<hostVolConf.length;i++){
			var volSizeValue = hostVolConf[i]["capacity_value"];
			var volUuid = hostVolConf[i]["uuid"];
			_takeoverCutBackVolTab.row.add([
				hostVolConf[i]['display_name'],
				hostVolConf[i]['capacity'],
				'<select class="form-control input-sm" name="standbyvol" id='+"standby_volume_"+i+' >\
				<option value = "'+volSizeValue+'" data-hostvoluuid="'+volUuid+'">'+LANG.UI_VOL_CDP_BACKUP_MAP_SELECT+'</option></select>'
			]).draw();
		}
		//loadStandbyHost();
	}
	/**
	 * @function:存储单位换算
	 * @descript：将字节单位进行换算
	 * @return：seizeStr,unit
	 */
	var parsetUnit = function(size){
		var data = "";
		var unit_value = 0;
		var data_value = 0;
		var list = [];
	    if (size < 0.1 * 1024) { 	//如果小于0.1KB转化成B
	        data = size.toFixed(2) + LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_B;
	        data_value = size.toFixed(2)
	        unit_value = 0;
	    } else if (size < 0.1 * 1024 * 1024) {		//如果小于0.1MB转化成KB
	        data = (size / 1024).toFixed(2) + LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_KB;
	        unit_value = 1;
	        data_value =(size / 1024).toFixed(2);
	    } else if (size < 1 * 1024 * 1024 * 1024) { 	//如果小于0.1GB转化成MB
	        data = (size / (1024 * 1024)).toFixed(2) + LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_MB;
	        unit_value = 2;
	        data_value =  (size / (1024 * 1024)).toFixed(2);
	    } else if(size <1 * 1024 * 1024 * 1024*1024){
	    	data = (size /(1024*1024*1024)).toFixed(2) + LANG.UI_VOL_CDP_JOB_DETAILS_SIZE_GB;
	    	unit_value = 3;
	    	data_value = (size /(1024*1024*1024)).toFixed(2);
	    }
	    var sizestr = data + "";
	    var len = sizestr.indexOf("\.");
	    var dec = sizestr.substr(len + 1, 4);
	    if (dec == "00") {		//当小数点后为00时 去掉小数部分
	        return sizestr.substring(0, len) + sizestr.substr(len + 3, 2);
	    }
	    list.push(sizestr);
	    list.push(Number(data_value));
	    list.push(unit_value);
	    return list;
	}
	/**
     * @function 获取速度单位换算大小
     * @descript 根据选择的容量单位大小，输出以字节为单位的数值
     * @return byte number
     */
    var getUnit = function(divID){
        var type = parseInt($('#'+divID).val());
        var unit;
        switch(type){
        	case 0:
        		unit = 1;
        		break;
            case 1:
                unit = 1024;
                break;
            case 2:
                unit = 1024 * 1024;
                break;
            case 3:
                unit = 1024 * 1024 * 1024;
                break;
            case 4:
                unit = 1024 * 1024 * 1024*1024;
                break;
        }
        return unit;
	}
	//解析自动接管相关配置
	var parseAutoTakeoverConf = function(data){
		_failbackCationIp  = data.auto_takeover_info.failbackup_ip;
		$('.autoTakeoverStandbyDiv').show();
		$('.volCdpAppTakeoverDiv').show(); //应该故障接管view
		$('.autotakeovercatbackupipdiv').show();  //接管回切通讯IP

		$('#autoTakeoverStandby').html(data.auto_takeover_info.standby_agent);  //接管备机
		$('#autoTakeoverCatbackIp').html(data.auto_takeover_info.failbackup_ip);  //接管恢复IP
		$('#volCdpAppTakeover').html(getFlagLevelInfo(data.auto_takeover_info.app_takeover_flag));  //应该故障接管
		// $('.takeoverFailbackConfDiv').show();  //接管回切配置
		$('.takeoverNetworConfDiv').show();
		$('.heartbeatFailureTimeView').show();  // 心跳持续失效时间view
		$('#heartbeatFailureTime').html(data.auto_takeover_info.heartbeat_failure_time+LANG.UI_PUBLIC_SECOND);
		$('.volCdpAppMonitorDiv').hide();
		if(data.auto_takeover_info.app_takeover_flag==CONF.FLAG.SET){
			$('.cdpTakeoverAppDiv').hide();  //接管应用,暂时隐藏,后期考虑是否放开 modify time:2022-1-21 18:11:46
			$('.customScriptDiv').show();	//接管自定义脚本
			$('.volCdpAppMonitorDiv').show();
			$('#volCdpTaskAppMonitor').html(getFlagLevelInfo(CONF.FLAG.UNSET));
			$('.appConseFailureNumDiv').hide();  //连续故障次数view
			$('.appFaultDetectionInterDiv').hide();
			if(data.auto_takeover_info.app_consecutive_failure_num>0 && data.auto_takeover_info.app_fault_detection_interval>0){
				$('#volCdpTaskAppMonitor').html(getFlagLevelInfo(CONF.FLAG.SET));
				$('.appConseFailureNumDiv').show();  //连续故障次数view
				$('.appFaultDetectionInterDiv').show();  //故障监测间隔view
				$('#appConseFailureNum').html(data.auto_takeover_info.app_consecutive_failure_num+ LANG.UI_VOL_CDP_BACKUP_TIMES); //连续故障次数
				$('#appFaultDetectionInter').html(data.auto_takeover_info.app_fault_detection_interval+LANG.UI_PUBLIC_SECOND); //故障监测间隔，单位秒
			}
			$('#cdpTakeoverApp').html(data.takeover_app_info);

		}
		if(data.auto_takeover_info.script_info.length==0){
			$('#customScript').html(LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE);  //当前未添加自定义脚本的配置
		}

		var takeoverTypeStr = LANG.UI_VOL_CDP_MOUNT_TAKEOVER_DESC;
		if(data.auto_takeover_info.takeover_agent_type == vmHypervisorTypeEmbedQemuKvm){  //备机为类型虚拟化
			takeoverTypeStr = LANG.UI_VOL_CDP_COMPLETE_MACHINE_TAKEOVER_DESC;
			$('.autotakeovercatbackupipdiv').hide();  //接管回切通讯IP
		}
		$('#taskTakeoverType').html(takeoverTypeStr);


	}
	//初始化操作按钮
	var initOpButton = function(data,auto_takeover_flag){
		var taskType = data.taskTypeFlag;
		var opCode = [];
		 //启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切 /15 停止回切/16创建手动标签 /17-禁用或启用自动接管
		var disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_DISABLE; //停用自动接管
		var handoverInfo = data.handover_info;
		var isVerifTask = false;  //是否为验证任务
		if(taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskType == CONF.TASK_TYPE.VOL_CDP_REPLICATION){  //备份
			var autoTakeoverFlag = data.auto_takeover_flag;
			opCode = getBackupControlCode(autoTakeoverFlag);
			if(data.auto_takeover_enable_flag == CONF.FLAG.SET){
				disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_DISABLE;  //停用自动接管
				_autoTtakeoverEnableFlag = 1;
			}else if(data.auto_takeover_enable_flag == CONF.FLAG.UNSET){
				disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_ENABLE;  //启用自动接管
				_autoTtakeoverEnableFlag = 2;
			}
		}else if(taskType==CONF.TASK_TYPE.VOL_CDP_RECOVERY){  //恢复
			opCode = getRecoverControllCode();
		}else if(CONF.TASK_TYPE.VOL_CDP_TAKEOVER ==taskType){  //接管
			opCode = getTakeoverControllCode();
			if(handoverInfo['takeover_agent_role'] == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){ //验证
				isVerifTask = true;
			}
		}

		var button = "";
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
					break;
				case 2:
					button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
					break;
				case 5:
					button += '<li class="pause"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_PAUSE + '</a></li>';
					break;
				case 8:
					button += '<li class="startStra"><a href="javascript:;" ><i class="viconfont vicon-ge_time_point"></i> ' + LANG.UI_JOB_START_STRATEGY + '</a></li>';
					break;
				case 11:
					if(isVerifTask){
						button += '<li class="takeover "><a href="javascript:;" ><i class="viconfont vicon-vol_cdp_takeover"></i> ' + LANG.UI_JOB_START_VERIF + '</a></li>';
					}else{
						button += '<li class="takeover "><a href="javascript:;" ><i class="viconfont vicon-vol_cdp_takeover"></i> ' + LANG.UI_JOB_START_TAKEOVER + '</a></li>';
					}

					break;
				case 12:
					if(auto_takeover_flag){ //如果是自动接管
						button += '<li class="stoptakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_AUTO_TAKEOVER + '</a></li>';
					}else{
						if(isVerifTask){
							button += '<li class="stoptakeover "><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_VERIF + '</a></li>';
						}else{
							button += '<li class="stoptakeover "><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
						}

					}
					break;
				case 14:
					button += '<li class="startfailback"><a href="javascript:;" ><i class="viconfont vicon-ge_cutback"></i> '+LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK+' </a></li>';
					break;
				case 15:
					if(auto_takeover_flag){ //如果是自动接管
						button += '<li class="stoptakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_AUTO_TAKEOVER + '</a></li>';
					}else{
						if(isVerifTask){
							button += '<li class="stoptakeover "><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_VERIF + '</a></li>';
						}else{
							button += '<li class="stoptakeover "><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
						}

					}
					break;
				case 16:
					button += '<li class="createlable"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> '+LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABEL+' </a></li>';
					break;
				case 4:
					button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
					break;
				case 17:
					button += '<li class="disableSrEnable"><a href="javascript:;"><i class="viconfont vicon-ge_data_flow"></i> ' + disableSrEnableDesc +'</a></li>';
					break;

			}
		});

		$('#volCdpOpList').html(button);
		initButFlag = true;
		initListener();
		//初始化/更新操作按钮
		setBtnStatus();
	}
	//得到任务阶段的显示底色
	var getRunningStageClass = function(taskStatus,runningStage){
		var levelClass = '';
		switch(runningStage){
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
				levelClass = "label-info";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
				levelClass = "label-success";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC:
				levelClass = "label-success";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK:
				levelClass = "label-primary";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
				levelClass = "label-primary";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
				levelClass = "label-green";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:  //回切初始同步
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //回切實時同步
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:  //回切數據准备中
				levelClass = "label-blue-madison";
				break;
			default:
				levelClass = "label-default";
				break;
		}
		return levelClass;
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
	/**
	 * @function 获取恢复任务可控制项
	 * @return opCode Array
	 */
	var getRecoverControllCode = function(){
		var opCode = [];
		var runningStage = _taskCurrentRunningStage;
		switch(_taskStatus){
 			case CONF.TASK_STATUS.WAITTING:		//任务等待运行
 				opCode = [1,4];			//启动,删除
 				break;
 			case CONF.TASK_STATUS.RUNNING:		//任务正在运行
 				opCode = [2];			//停止
 				break;
 			case CONF.TASK_STATUS.STOPPED:	//任务停止
 				opCode = [1,3,4];
 				break;
			case CONF.TASK_STATUS.NETWORK_FAULT: //网络错误
				opCode = [2];  //停止
				break;
 			case CONF.TASK_STATUS.ERROR:	//任务出错
 				opCode = [1,3,4];
 				break;
 			case CONF.TASK_STATUS.SUCCESSED:
 				opCode = [1,2,4];
 				break;

		}
		return opCode;
	 }
	/**
	 * @function 获取接管任务可控制项
	 * @return opCode Array
	 */
	var getTakeoverControllCode = function(){
		var opCode = [];
		var runningStage = _taskCurrentRunningStage;
		// opCode:1启动/2停止/3修改/4删除/5暂停/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切 /15 停止回切/16继续-对应暂停
    	switch(_taskStatus){
    		case CONF.TASK_STATUS.WAITTING:		//任务等待运行
    			opCode = [11,4];	//启动,删除
    			break;
    		case CONF.TASK_STATUS.RUNNING:		//任务正在运行
    			if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){ //任务阶段:接管中,接管启动中
    				opCode = [12];			//停止接管,启动回切
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
    					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
    					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){  //任务阶段:逆向实时同步,初始同步，回切启动中
    				opCode = [15];
    			}
    			break;
    		case CONF.TASK_STATUS.STOPPED:  //任务停止
    			if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){ //任务阶段:接管
    				opCode = [11,3,4];
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){ //任务阶段:逆向实时同步
    				opCode = [11,4];
    			}else{
					opCode = [11,4];	//启动,删除
				}
 				break;
    		case CONF.TASK_STATUS.SUCCESSED:  //接管任务成功
    			if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER && _takeoverApplicationRol == false){ //任务阶段:接管
    				opCode = [12,14];
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER && _takeoverApplicationRol == true){
					opCode = [12];
				}
    			break;

			case CONF.TASK_STATUS.ABNORMAL:  //任务已完成但异常
				opCode = [12]; //停止接管
				break;
    		case CONF.TASK_STATUS.ERROR:  //任务出错
    			if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){ //任务阶段:接管中，接管启动中
    				opCode = [11,4,3];
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){ //任务阶段:逆向实时同步
    				opCode = [14,15];  //启动回切,停止接管
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
    					|| runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){ //任务阶段:逆向准备中/初始同步
    				opCode = [14,15];  //启动回切,停止接管
    			}
 				break;
    	}
    	return opCode;
	}
	/**
	 * @function 获取备份任务可控制项
	 * @description 为标签策略各项操作绑定初始事件函数根据任务状态和当前阶段获取当前可操作的控制项
	 * @return opCode array
	 */
	var getBackupControlCode = function(autoTakeoverFlag){
		var opCode = [];
		var runningStage = _taskCurrentRunningStage;
		var disOrEnableTakeoverNumber = 17; //启用或禁用自动接管
		// opCode:1启动/2停止/3修改/4删除/5暂停/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切 /15 停止回切/16继续-对应暂停/17-禁用或启用自动接管（接管和回切阶段不能执行该操作）
		switch(_taskStatus){
    		case CONF.TASK_STATUS.WAITTING:		//任务等待运行
				opCode = [1,4];			//启动,删除
    			break;
    		case CONF.TASK_STATUS.RUNNING:		//任务正在运行
    			if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC){ //任务阶段:运行阶段-等待
					opCode = [2];		//停止
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC){  //任务阶段:初始化同步 或实时同步
					opCode = [2];		//停止
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){  //任务阶段:实时同步,切换至实时同步阶段
					opCode = [2,16];	  //停止,暂停,创建标签
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
				){  //任务阶段:服务端的数据一致性校验 或 备机的数据一致性校验
					opCode = [2];		//停止
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER ){//任务阶段:接管
    				opCode = [12];		//接管
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
    					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
    					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){  //任务阶段:逆向实时同步,初始同步，回切启动中
    				opCode = [15]; 		//停止回切
    			}else{
					opCode = [2];		//停止

    			}
    			break;
    		case CONF.TASK_STATUS.PAUSED:	//任务暂停
    			if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                    || runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){ //初始同步、实时备份、准备切换至实时同步
					opCode = [2,16]; 	//停止,继续
    			}else{
    				opCode = [];
    			}
    			break;
    		case CONF.TASK_STATUS.STOPPED:	//任务停止
    			if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
					|| runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC
				){ //初始同步、实时备份、准备切换至实时同步
					opCode = [1,3,4];	//启动备份,修改,删除
    			}
    			//服务端数据一致性校验 或备机数据一致性校验
    			else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
				){
					opCode = [1,3,4];  	//启动备份,修改,删除
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){	//接管
    				opCode = [1,4,11];	//启动备份,删除,启动接管
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){ //逆向实时同步
    				opCode = [1,4];		//启动备份,删除
    			}else{
    				opCode = [1,4];
    			}
    			break;
    		case CONF.TASK_STATUS.ERROR :	//任务错误(失败)
    			if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                    || runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){ //初始同步、实时备份、准备切换至实时同步
    				opCode = [1,3,4];	//启动备份,修改,删除
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
				){	//任务阶段:服务端的数据一致性校验 或 备机的数据一致性校验
    				opCode = [1,4];		//启动，删除
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){	//接管
    				opCode = [1,3,4,11];	//启动备份,修改,删除,启动接管
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){	//任务阶段:逆向初始同步,逆向实时同步
    				opCode = [12,14];	//启动回切,停止接管
    			}else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC){ //暂时定义具有上述控制项
    				opCode = [1,3,4];	//启动备份,修改,删除
    			}else{
    				opCode = [1,3,4];
    			}
    			break;
    		case CONF.TASK_STATUS.NETWORK_FAULT:  //网络错误
				if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                    || runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
					|| runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){ //初始同步、实时备份、准备切换至实时
					opCode = [2]; //停止
				}else{
					opCode = [1,2]; //启动，停止
				}
				break;
			case CONF.TASK_STATUS.ABNORMAL:  //任务已完成但异常

				if(autoTakeoverFlag == CONF.FLAG.SET){  //备份任务，配置自动接管
					opCode = [12]; //停止接管
				}
				break;
    		case CONF.TASK_STATUS.SUCCESSED:	//任务成功
    			if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){  //接管状态
    				opCode = [12,14];	//启动回切,停止接管
    				break;
    			}
    	}
		//配置自动接管，并且任务阶段不处于接管中、接管启动中、逆向初始同步、逆向实时同步、回切启动汇总时可以操作禁用或启用自动接管
		if(autoTakeoverFlag == CONF.FLAG.SET
				&& runningStage !=CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER
				&& runningStage != CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING
				&& runningStage != CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
				&& runningStage != CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
				&& runningStage != CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING) {
			opCode.push(disOrEnableTakeoverNumber);
		}
    	return opCode;
	}
	//得到开启和关闭的HTML内容
	var getFlagLevelInfo = function(flag){
		var html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		if(flag==CONF.FLAG.SET){
			html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
		}
		return html;
	}
	//得到时间策略描述信息
	var getTimeStrategy = function(msg){
		var timeInfo = LANG.UI_PUBLIC_NOTHING;
		if(!msg){
			return timeInfo;
		}
		timeInfo = "";
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
			timeInfo += des;
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
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_NUM + "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value + LANG.UI_STRATEGY_RESERVE_NUM_VALUE;
		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_DAY + "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value + LANG.UI_STRATEGY_RESERVE_DAY_VALUE;
		}
		return reservedStr;
	}

	var getSpeedDes = function(value){
		if(value >= 1024){
			return Math.round(value * 100 / 1024) / 100 + LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB;
    	}else{
    		return value + LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB;
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
        	        		return Math.round(value * 100 / 1024) / 100 + LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB;
        	        	}else{
        	        		return value + LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB;
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
        	    xAxis :
    	        {
    	            type : 'category',
    	            boundaryGap : false,
    	            splitLine: {
    	                show: false
    	            },
    	            axisLabel : {
    	            	show:true,
    	                interval: 12
    	            },
    	            data:[]
    	        },
        	    yAxis :
    	        {
    	            type : 'value',
    	            splitLine: {
    	                show: true
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
						}
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
        	            	color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? '#2A87C8':'#44b6ae',
        	            	opacity: 0.2
        	            }},
        	            data:[]
        	        },
        	    ],
				color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? ['#2A87C8']:['#44b6ae']
    	};

        if(myChart != undefined){
        	myChart.clear(); //清空实例 否则会与上一个合并 数据残留
		}
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
	/**
	 * 判断内嵌虚拟机状态，根据电源状态动态显示不同对应示意图。非内嵌为默认值
	 * @returns
	 */
	var takeoverStandbyHostSate = function(){
		var hostState = ["standby-offline.png","standby.png","standby.png"];
		if(_takeoverAgentType == vmHypervisorTypeEmbedQemuKvm){
			switch(_vmTempStatus){
				case 0:  //未创建
					hostState = ["standby-nostate.png","standby-nostate.png","standby-nostate.png"];
					break;
				case 1:  //运行中
					hostState = ["standy-running.png","standy-running.png","standy-running.png"];
					break;
				case 3:  //暂停
					hostState = ["standby-pause.png","standby-pause.png","standby-pause.png"];
					break;
				case 5: //关闭
					hostState = ["standby-power-off.png","standby-power-off.png","standby-power-off.png"];
					break;
				default: //未创建
					hostState = ["standby-nostate.png","standby-nostate.png","standby-nostate.png"];
					break;

			}
		}
		return hostState;
	}

    //设置任务数据流向示意图
    var initMotionImg = function(){
    	var imgPath = "./img/cdp/";
		//数据连接状态
		var hostToBSTransStatusValue = {
			'connected_no_state':1, //连通无状态
			'data_transfer':2,  //连通有数据传输
			'network_anomaly':3, //连接异常(网络异常)
			'pause':4, // 连通任务暂停
			'network_deart_beat' :5, //连通有心跳
			'erorr' :6,  //任务出错
			'reverse_data_transfer':7, //反向数据(与2方向相反)
			'not_created':8, //备机未创建
		};
    	var hostState = ['master-offline.png','master-host.png','master-offline.png']; //主机

    	//[1. 连通无状态,2. 连通有数据传输;3. 连接异常(网络异常),4. 连通任务暂停;5. 连通有心跳;6.任务出错;7:反向数据(与2方向相反);]
    	var hostToBsTransferState = ['connected-no-state.png','connected-no-state.png','data-transfer.gif', 'network-anomaly.png',
    						'pause.png','network-deart-beat.gif','erorr.png','reverse-data-transfer.gif'];  //主机与备份服务器传输状态

    	var hostToBsTfStateNoStandby = ['connected-no-state.png','connected-no-state.png','data-transfer.gif', 'network-anomaly.png',
					'pause.png','network-deart-beat.gif','erorr.png','reverse-data-transfer.gif'];  //主机与备份服务器传输状态,未配置备机
    	var backupServerState = ['backup-server.png','backup-server-online.png'];  //备份服务器状态

    	//[1. 连通无状态,2. 连通有数据传输;3. 连接异常(网络异常),4. 连通任务暂停;5. 连通有心跳;6.任务出错;7:反向数据(与2方向相反);8:备机未创建]
    	var standbyToTransferState = ['connected-no-state.png','connected-no-state.png','data-transfer.gif', 'network-anomaly.png',
					'pause.png','network-deart-beat.gif','erorr.png','reverse-data-transfer.gif','not-created.png'];  //备份服务器到备机传输状态
    	var dataIsStandbyToHostState = ['failback-wait.png','data-failback.gif','failback-error.png'];  //数据从备机直接到主机流向示意图
    	var standbyState = takeoverStandbyHostSate(); //备机



    	var data = {};
    	data.taskuuid = $("#task_uuid").val();
    	var task_type = $("#task_type").val();
    	data.tasktype = task_type;
    	var jsonData = JSON.stringify(data);
    	var updateInterval = 4500;
		var update = function(){
			if(0 == $('#task_uuid').size() ){
        		clearTimeout(timerTask.volTaskDataMapUpdate);
        		return;
        	}
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getVolCdpTaskMapInfo',p:jsonData}, function(d){
				debugger;
				var info = JSON.parse(d);
				var nodeIp = info.nodeIp;
				var taskStage = info.currentTaskStage;
				var standby_agent_uuid = info.standby_agent_uuid;
				var backup_mode = info.backupMode;
				var re = new RegExp(_transPortIp);
				var agentType = info.agentType;
				_vmTempStatus = info.standbyAgentStatus;
				_takeoverAgentType = agentType;
				if(re.test(nodeIp)){
					nodeIp = _transPortIp;
				}
				if(nodeIp==""){
					nodeIp = info.nodeIp;
				}
				standbyState = takeoverStandbyHostSate(); //备机

				$('#backupServerIp').html(nodeIp);
				$('#backupServerIp').attr('title',info.nodeInfo);
				if(info.standbyIsConf==1){ //是否配置备机
					$('#transSpeedBsToStandbyDiv').hide();
					$('#transSpeedHostToBsDiv').hide();

					$(".cdptaskmap").show();
					$(".nostandbymap").hide();

					$('#hostServerIp').html(info.masterIp);
					$('#hostServerIp').attr('title',info.masterDesc);
					//回切的各个阶段
					if(taskStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || taskStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC || taskStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
						$('.maphostdesc').html(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_HOST);
					}else{
						$('.maphostdesc').html(LANG.UI_VOL_CDP_JOB_DETAILS_SOURCE_HOST);
					}
					if(agentType  == vmHypervisorTypeEmbedQemuKvm){
						$('#standbyIp').html(info.standbyDesc);
					}else{
						$('#standbyIp').html(info.standbyIp);
					}

					$('#standbyIp').attr('title',info.standbyDesc);
					$('#hostImg').attr('src', imgPath + hostState[info.masterAgentMapStatus]);  //主机状态
					$('#hostImg').attr({title:LANG.UI_VOL_CDP_JOB_DETAILS_HOST+'--'+parseOnlineStatusStr(info.masterAgentMapStatus)});
					var hostToBSTransStatus = info.hostToBSTransStatus;

					// 主机离线，主机到备份服务器的状态改变
					if(hostToBSTransStatus == hostToBSTransStatusValue['data_transfer'] && info.masterAgentMapStatus == CONF.FLAG.UNSET){
						hostToBSTransStatus = hostToBSTransStatusValue['network_anomaly'];  //连接异常(网络异常)
					}

					$('#hostToBsTransferImg').attr('src', imgPath + hostToBsTransferState[hostToBSTransStatus]);  //主机与备份服务器之间传输状态

					if(hostToBSTransStatus== hostToBSTransStatusValue['data_transfer']){ //连通有数据传输
						$('#hostToBsTransferImg').css('margin-top','-5px');
					}
					var bsToStandbyTransStatus  = info.bsToStandbyTransStatus;
					//虚拟机未创建
					if(_vmTempStatus== CONF.FLAG.UNKNOW && _takeoverAgentType == vmHypervisorTypeEmbedQemuKvm){
						bsToStandbyTransStatus = hostToBSTransStatusValue['not_created']; //备机未创建
					}
					$('#bsToStandbyTransferImg').attr('src', imgPath + standbyToTransferState[bsToStandbyTransStatus]);  //备份服务器与备机之间传输状态
					if(hostToBSTransStatus== hostToBSTransStatusValue['data_transfer']){  //连通有数据传输
						$('#bsToStandbyTransferImg').css('margin-top','-5px');
						$('#transSpeedHostToBsDiv').show();

						if(standby_agent_uuid!='' && (backup_mode == CONF.CDP_BACKUP_MODE.REALTIME_COPY
							|| backup_mode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY)){
							_doubleHostMirrorFlag = CONF.FLAG.SET;
						}

						if(_doubleHostMirrorFlag == CONF.FLAG.SET){
							$('#transSpeedBsToStandbyDiv').show();
						}
					}

					_takeoverAgentType;
					$('#standbyImg').attr('src', imgPath + standbyState[info.standbyMapStatus]);  //备机状态
					$('#standbyImg').attr({title:LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_MACHINE+'--' + parseStandbyStatusStr(info.standby_agent_status,info.standbyMapStatus)});

					$("#standbyToHostMap").hide();
					$("#transSpeedStandbyToHostDiv").hide();
					if(task_type==CONF.TASK_TYPE.VOL_CDP_RECOVERY){
						$("#standbyToHostMap").show();
						var dataIsStandbyToHost = info.dataIsStandbyToHost;
						$('#standbyToHostMap').attr('src', imgPath + dataIsStandbyToHostState[dataIsStandbyToHost]);
					}

					if(info.currentTaskStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
						|| info.currentTaskStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
						|| info.currentTaskStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING
					){
						$("#standbyToHostMap").show();
						$("#transSpeedStandbyToHostDiv").show();
						var dataIsStandbyToHost = info.dataIsStandbyToHost;
						$('#standbyToHostMap').attr('src', imgPath + dataIsStandbyToHostState[dataIsStandbyToHost]);

						if(dataIsStandbyToHost==0){
							$("#standbyToHostMap").addClass('mt5');
							$("#standbyToHostMap").addClass('ml45');
							$('.volcdpfailbacktransdiv').hide();
						}else{
							$("#standbyToHostMap").removeClass('mt5');
							$("#standbyToHostMap").removeClass('mt50');
							$("#standbyToHostMap").removeClass('ml45');
						}
					}

				}else{
					$(".nostandbymap").show();
					$(".cdptaskmap").hide();
					$('#masterServerInfo').html(info.masterIp);
					$('#masterServerInfo').attr('title',info.masterDesc);

					$('#backupServerInfo').html(nodeIp);
					$('#backupServerInfo').attr('title',info.nodeInfo);

					$('#hostImgNoStandby').attr('src', imgPath + hostState[info.masterAgentMapStatus]); //主机状态
					$('#hostImgNoStandby').attr({title:LANG.UI_VOL_CDP_JOB_DETAILS_HOST+'--'+parseOnlineStatusStr(info.masterAgentMapStatus)});
					var hostToBSTransStatus = info.hostToBSTransStatus;
					if(hostToBSTransStatus==2 || hostToBSTransStatus==7){
						$('#transSpeedNoStandbyDiv').show();
					}else{
						$('#transSpeedNoStandbyDiv').hide();
					}
					$('#noStandbyTrancImg').attr('src', imgPath + hostToBsTfStateNoStandby[hostToBSTransStatus]);
					noStandbyTrancImg

				}

				timerTask.volTaskDataMapUpdate = setTimeout(update, updateInterval);
			});
		}
		update();
	}
    /**
     * 解析离线状态,返回string
     * @descript:params 0:离线,1:在线;other:离线
     */
    var parseOnlineStatusStr = function(status){
    	var statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINE;
    	if(status==1){  //设备在线，服务在线
    		statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE;
    	}else if(status ==2){  //设备在线，服务离线
    		statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_APPLICATION_OFF_LINE;
    	}
    	return statusStr;
    }

	/**
     * 解析备机状态,返回string
     * @descript:params 0:离线,1:在线;other:离线
     */
	var parseStandbyStatusStr = function(stantdbyStatus,standbyMapStatus){
		var statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_OFF_LINE;
		if(_takeoverAgentType == vmHypervisorTypeEmbedQemuKvm){
			switch(_vmTempStatus){
				case 0:  //未创建
					statusStr = LANG.UI_VOL_CDP_EMD_VM_STATUS_NOSTATE;
					break;
				case 1:  //运行中
					statusStr = LANG.UI_VOL_CDP_EMD_VM_STATUS_RUNNING;
					break;
				case 3:  //暂停
					statusStr = LANG.UI_VOL_CDP_EMD_VM_STATUS_PAUSED;
					break;
				case 5: //关闭
					statusStr = LANG.UI_VOL_CDP_STATUS_SHUTOFF;
					break;
			}
		}else{
			if(standbyMapStatus==1){  //设备在线，服务在线
				statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_ONLINE;
			}else if(standbyMapStatus ==2){  //设备在线，服务离线
				statusStr = LANG.UI_VOL_CDP_JOB_DETAILS_APPLICATION_OFF_LINE;
			}
		}
		return statusStr;
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
    var initLogGrid = function(){
    	var updateInterval = 5000;
    	var getLiInfo = function(d){
    		var d = JSON.parse(d);
    		var info = "";
    		for(var i=0; i<d.length; i++){
				info +=
				'<li class="list-group-item__log">' +
					'<div class="col1">' +
						'<div class="cont contdetail">' +
							'<div class="cont-col1">' + getIcon(d[i][3].level) + '</div>' +
							'<div class="cont-col2">' +
								'<div class="desc">' + d[i][2] + '</div>' +
							'</div>' +
						'</div>' +
					'</div>' +
					'<div class="col2 logtimecol">' +
						'<div class="date">' + d[i][0] + '</div>' +
					'</div>' +
				'</li>';
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
		//获取任务运行日志,暂时使用虚拟机模块的获取函数
		var getlog = function(){
			var data = {};
			data.uuid = $("#task_uuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getVolCdpRunningJobLog',p:jsonData}, function(d){
				if(0 == $('#runninglog').size()){
            		clearTimeout(timerTask.VMJobDetails_logGrid);
            		return;
            	}
        		getLiInfo(d);
	    	})
	    	.complete(function() {timerTask.VMJobDetails_logGrid = setTimeout(getlog, updateInterval);});
		}
		getlog();
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
    		var labelClass = getLevelClass(data[7].level);
    		var content = '<span class="label ' + labelClass + '">' +
    			'<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="right" data-content="' +
    			data[7].popover + '" >'+ data[2] + '</a></span>';
    		$(div).html(content);
    	}
    	var dataTableOpt = {
    			'showLoading':false,
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0,3,4]
    			}],
    			"order": [
                    [5, "desc"]
                ],
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() || !tabShowFlag){
        		clearTimeout(timerTask.VolCdpJobDetailsHistoryGrid);
        		return;
        	}
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#task_uuid").val();
    			data = {m:CONF.M.JOB,f:'getVolCdpDetailsHistory',p:data};
    			grid.setAjaxParam(data);
    	    	grid.init({src: $("#historytable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:initHistoryRow});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.VolCdpJobDetailsHistoryGrid = setTimeout(init, updateInterval);
    	}

    	$('#historytable').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();
            var nTr = $(this).parents('tr')[0];
            if($(this).hasClass('row-details-open')){
            	//如果是展开的收起所有展开项
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
    		if(!data){ return; }
        	var sOut = '<tr class="details"><td class="details" colspan="13">';
        	sOut += '<table class="">';
            sOut += getVolCdpHisDetailsInfo(data[8]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}
    	//得到卷cdp任务历史任务详情
    	var getVolCdpHisDetailsInfo = function(data){
    		if(!data.info){
    			return LANG.UI_PUBLIC_NOTHING;
    		}
    		//备份任务历史作业详情
    		var getBackupDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
        		thead += '<th width="15%">' + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT + '</th>';
        		thead += '<th width="8%">' + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_VOL + '</th>';
        		thead += '<th width="10%">' + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY_FINISHED + '</th>';
        		thead += '<th width="10%">' + LANG.UI_VOL_CDP_JOB_DETAILS_VALID_DATA_FINISHED + '</th>';
        		thead += '<th width="10%">' + LANG.UI_VOL_CDP_JOB_DETAILS_AVERAGE_SPEED + '</th>';
        		thead += '<th width="15%" id = "hisStandbyHostInfo">' + LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE + '</th>';
        		thead += '<th width="10%">' + LANG.UI_VOL_CDP_JOB_DETAILS_MAP_VOL+ '</th>';
        		thead += '<th width="7%">' + LANG.UI_PUBLIC_STATUS + '</th>';
        		thead += '<th width="15%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';
        		var tbody = '<tbody>';
        		for(var i=0; i<data.info.length; i++){
        			if(i%2 == 0){
        				tbody += '<tr role="row" class="odd">';
        			}else{
        				tbody += '<tr role="row" class="even">';
        			}
        			var standbyHostInfo = "--";
        			if(!!data.info[i].standby_host_name){
        				standbyHostInfo = data.info[i].standby_host_name;
        			} else if(data.info[i].standby_host_ip=="" && data.info[i].takeover_host_ip!=""){
        				standbyHostInfo = data.info[i].takeover_host_name;
        			}

        			var standbyMountInfo = "--";
        			if(data.info[i].standby_map_mount_point !="" && data.info[i].takeover_target_mount_point ==""){
        				standbyMountInfo = data.info[i].standby_map_mount_point;
        			}else if(data.info[i].standby_map_mount_point =="" && data.info[i].takeover_target_mount_point !=""){
        				standbyMountInfo = data.info[i].takeover_target_mount_point;
        			}else if(data.info[i].standby_map_mount_point !="" && data.info[i].takeover_target_mount_point !=""){
        				standbyMountInfo = data.info[i].standby_map_mount_point;
        			}


        			tbody += '<td>' + data.info[i].agent_name + '</td>';
        			tbody += '<td>' + data.info[i].vol_display_name + '</td>';
        			tbody += '<td>' + data.info[i].vol_size+'/'+data.info[i].vol_complete_size + '</td>';
        			tbody += '<td>' + data.info[i].real_size+'/'+data.info[i].real_complete_size + '</td>';
        			tbody += '<td>' + data.info[i].draw_speed + '</td>';
        			tbody += '<td>' + standbyHostInfo + '</td>';
        			tbody += '<td>' + standbyMountInfo + '</td>';
        			tbody += '<td>' + data.info[i].task_status + '</td>';
        			tbody += '<td>' + data.info[i].description + '</td>';
        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		var details = thead + tbody;
        		return details;
    		}
    		//得到卷cdp 恢复任务历史任务详情
    		var getRecoveryDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
        		thead += '<th width="10%">' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>';
        		thead += '<th width="15%">' + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_CLIENT + '</th>';
        		thead += '<th width="8%">' + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_VOL + '</th>';
        		thead += '<th width="8%">' + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY + '</th>';
        		thead += '<th width="8%">' + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME + '</th>';
        		thead += '<th width="15%">' + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_SERVER+ '</th>';
        		thead += '<th width="10%">' + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_VOL+ '</th>';
        		thead += '<th width="5%">' + LANG.UI_PUBLIC_STATUS + '</th>';
        		thead += '<th width="20%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';
        		var tbody = '<tbody>';
        		for(var i=0; i<data.info.length; i++){
        			if(i%2 == 0){
        				tbody += '<tr role="row" class="odd">';
        			}else{
        				tbody += '<tr role="row" class="even">';
        			}
        			tbody += '<td>' + data.info[i].recovery_target_time + '</td>';
        			tbody += '<td>' + data.info[i].agent_name +'</td>';
        			tbody += '<td>' + data.info[i].vol_display_name +'</td>';
        			tbody += '<td>' + data.info[i].vol_size+'/'+data.info[i].vol_complete_size + '</td>';
        			tbody += '<td>' + data.info[i].real_size+'/'+data.info[i].real_complete_size + '</td>';
        			tbody += '<td>' + data.info[i].recovery_host_name+'</td>';
        			tbody += '<td>' + data.info[i].recovery_target_mount_point +'</td>'
        			tbody += '<td>' + data.info[i].task_status + '</td>';
        			tbody += '<td>' + data.info[i].description + '</td>';
        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		var details = thead + tbody;
        		return details;
    		}
    		//解析卷CDP接管任务历史运行详情
    		var getTakeoverDetails = function(){

    			var thead = '<thead><tr role="row" class="heading">';
    			thead += '<th width="15%">' + LANG.UI_EMERGENCY_RECOVERY_TIMEPOINT + '</th>';
        		thead += '<th width="15%">' + LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_CLIENT + '</th>';
				if(_takeoverApplicationRol){
					thead += '<th width="10%">' + LANG.UI_VOL_CDP_VERIFY_VOL_DESC + '</th>';
				}else{
					thead += '<th width="10%">' + LANG.UI_VOL_CDP_BACKUP_TAKEOVER_VOL + '</th>';
				}

        		thead += '<th width="15%">' + LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_SERVER + '</th>';
        		thead += '<th width="10%">' + LANG.UI_VOL_CDP_BACKUP_MOUNT_POINT + '</th>';
        		thead += '<th width="10%">' + LANG.UI_PUBLIC_STATUS + '</th>';
        		thead += '<th width="25%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';
        		var tbody = '<tbody>';
        		for(var i=0; i<data.info.length; i++){
        			if(i%2 == 0){
        				tbody += '<tr role="row" class="odd">';
        			}else{
        				tbody += '<tr role="row" class="even">';
        			}
					var mountPoint  =  data.info[i].takeover_target_mount_point;
					if(mountPoint == ""){
						mountPoint = "--";
					}
        			tbody += '<td>' + data.info[i].takeover_target_time + '</td>';
        			tbody += '<td>' + data.info[i].agent_name +'</td>';
        			tbody += '<td>' + data.info[i].vol_display_name + '</td>';
        			tbody += '<td>' + data.info[i].takeover_host_name +'</td>';
        			tbody += '<td>' + mountPoint + '</td>';
        			tbody += '<td>' + data.info[i].task_status + '</td>';
        			tbody += '<td>' + data.info[i].description + '</td>';
        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		var details = thead + tbody;
        		return details;
    		}
    		if(CONF.TASK_TYPE.VOL_CDP_BACKUP == data.taskType || CONF.TASK_TYPE.VOL_CDP_REPLICATION == data.taskType){  //备份
    			return getBackupDetails(data);
    		}else if(CONF.TASK_TYPE.VOL_CDP_RECOVERY == data.taskType){  //恢复
    			return getRecoveryDetails(data);
    		}else if(CONF.TASK_TYPE.VOL_CDP_TAKEOVER == data.taskType){  //接管
    			return getTakeoverDetails(data);
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

    var volCdpGridLoad = function(){
		if(detailsInfo){
			var openTr = $('#volinfo').find('tbody > tr')[detailsIndex];
			$(openTr).after(detailsInfo);
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
    }
    //初始化卷应用信息详情
    var initVolInfoGrid = function(){
    	var updateInterval = 3000;
    	var initFlag = false;
    	_volGrid = new Datatable();
    	var dataTableOpt = {
    			'showLoading':false,
    			"ordering": false,
                "paging":false,
                "info":false,
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() ){
        		clearTimeout(timerTask.VolJobDetails_volGrid);
        		return;
        	}
    		var data = {};
	    	var taskUuid = $("#task_uuid").val();
	    	var taskType = $("#task_type").val();
			data.uuid = taskUuid;
			data.task_type = taskType;
			data.task_current_stage = _taskCurrentRunningStage;
    		if(!initFlag){
    			data = {m:CONF.M.JOB,f:'getDetailsVolInfo',p:data};
    			_volGrid.setAjaxParam(data);
    			_volGrid.init({src: $("#volAppTable"), showDetail:true, onDataLoad:volCdpGridLoad, dataTable:dataTableOpt});
    	    	initFlag = true;
    		}else{
    			_volGrid.getRefresh(data);
    		}
			 if(isInit) {
				timerTask.VolJobDetails_volGrid = setTimeout(init, updateInterval);
			 } else {
			 	clearTimeout(timerTask.VolJobDetails_volGrid);
			 }

    	}
    	init();
    	$('#volAppTable').on('click', ' tbody td .row-details', function () {
        	var data = _volGrid.getDataTable().data();
            var nTr = $(this).parents('tr')[0];
            if($(this).hasClass('row-details-open')){
            	//如果是展开的,收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfo = null;
            }else{
            	//如果是收起的
            	$('#volinfo').find('tr .details').parent().remove();
            	$('#volinfo').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
        		addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('#volinfo').find('tbody tr .details').parents('tr')[0];
            }
            return;
        });
    	//添加详情信息
    	var addDetails = function(nTr, data){
    		if(!data){ return; }
        	var sOut = '<tr class="details" ><td class="details" colspan="13" ><div style="max-height:180px;overflow-y: auto;">';
        	sOut += '<table>';
            sOut += getTaskVolDetails(data);
            sOut += '</table></div></td></tr>';
    		$(nTr).after(sOut);
    		var task_type = data[9];
    		if(CONF.TASK_TYPE.VOL_CDP_BACKUP == task_type || CONF.TASK_TYPE.VOL_CDP_REPLICATION == task_type){
    			var treeId = "task_app_tree"+data[0];
        		setTaskAppTree(data[11],treeId);
    		}else if (CONF.TASK_TYPE.VOL_CDP_TAKEOVER == task_type){
    			var treeId = "task_app_tree"+data[0];
        		setTaskAppTree(data[11],treeId);
    		}
    	}
		/**
		 * 解析卷和应用详情
		 * @param data
		 * @returns {string|*}
		 */
    	var getTaskVolDetails = function(data){
    		var task_type = data[9];
    		if(CONF.TASK_TYPE.VOL_CDP_BACKUP == task_type || CONF.TASK_TYPE.VOL_CDP_REPLICATION == task_type){  //卷CDP备份任务
				return getBackupTaskVolAndAppDetails(data);

    		}else if(CONF.TASK_TYPE.VOL_CDP_RECOVERY == task_type){  //卷CDP恢复任务

    			return getRecoveryVolDetails(data);
    		}else if(CONF.TASK_TYPE.VOL_CDP_TAKEOVER == task_type){  //卷CDP接管任务
    			return getTakeoverVolAndAppDetails(data);
    		}
    	}
		//备份任务监控的卷和应用详情
		var getBackupTaskVolAndAppDetails = function (data){
			var taskCurrentStage = data[20];
			var failbackupAgentInfo = [];
			var appInfo = LANG.UI_VOL_CDP_JOB_DETAILS_NO_MONITOR;
			var details = "";
			var appTypeStr = LANG.UI_VOL_CDP_JOB_DETAILS_MONITOR_APP;
			if(taskCurrentStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || taskCurrentStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC|| taskCurrentStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
				failbackupAgentInfo = data[21];
				appTypeStr = LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_APP;
			}
			if(failbackupAgentInfo.length!=0){
				var agentInfo = failbackupAgentInfo['agent_info'];
				var targetVol = failbackupAgentInfo['target_vol'];
				var targetDisk = failbackupAgentInfo['target_disk'];
				var targetObjectInfo = targetVol;
				var targetObjectText = LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOLINFO;
				if(targetVol==""&& targetDisk !=""){
					targetObjectInfo = targetDisk;
					targetObjectText = LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_DISKINFO;
				}

				details = "<tr>&nbsp;&nbsp;<td class = 'ml25 min-width100'> "+LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_HOST+":  </td><td>"+agentInfo+"</td></tr>"+
					"<tr>&nbsp;&nbsp;<td class = 'ml25 min-width100'> "+ targetObjectText +":  </td><td>"+ targetObjectInfo +"</td></tr>";

			}
			if(data[11].length>0){
				var treeId = "task_app_tree"+data[0];
				details += "<tr><td><span>"+appTypeStr+": </span></td><td class='ml-25'><ul id='"+treeId+"' class='ztree ml-6' style='margin-left: -5px;'></ul></td>";

			}else{
				details += "<tr><td><span>"+appTypeStr+": </span></td><td><span>"+LANG.UI_VOL_CDP_JOB_DETAILS_NO_CONFIGURE_APPLICATION+"</span></td>";
			}
			return details;
		}

		//接管应用详情
		var getTakeoverVolAndAppDetails = function(data){
			var takeOverTime = data[14];
			var taskCurrentStage = data[19];
			var failbackupAgentInfo = [];
			var agentInfo = "";
			var details = "<tr><td class='ml25 min-width100'><span>"+LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME+"</span></td><td><span>"+takeOverTime+"</span></td></tr>";
			var appTypeStr = LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_APPLICATION;
			if(taskCurrentStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || taskCurrentStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC|| taskCurrentStage ==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
				failbackupAgentInfo = data[20]; //回切目标信息
				details = "";
				appTypeStr = LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_APP;
			}

			if(failbackupAgentInfo.length!=0){
				var agentInfo = failbackupAgentInfo['agent_info'];
				var targetVol = failbackupAgentInfo['target_vol'];
				var targetDisk = failbackupAgentInfo['target_disk'];
				var targetObjectInfo = targetVol;
				var targetObjectText = LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOLINFO;
				if(targetVol==""&& targetDisk !=""){
					targetObjectInfo = targetDisk;
					targetObjectText = LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_DISKINFO;
				}
				var agentInfo = "<tr>&nbsp;&nbsp;<td class = 'ml25 min-width100'> "+LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_HOST+":  </td><td>"+agentInfo+"</td></tr>"+
					"<tr>&nbsp;&nbsp;<td class = 'ml25 min-width100'> "+ targetObjectText +":  </td><td>"+targetObjectInfo+"</td></tr>" +
					"<tr>&nbsp;&nbsp;<td class = 'ml25 min-width100'> "+ LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME +":  </td><td>"+takeOverTime+"</td></tr>";
			}
			details += agentInfo;

			var treeId = "task_app_tree"+data[0];
			if(data[11].length>0){
				details += "<tr><td style = 'vertical-align:top;padding-top: 10px;'><span>"+appTypeStr+": </span></td><td class='ml-25'><ul id='"+treeId+"' class='ztree ml-6' style='margin-left: -5px;'></ul></td>";
			}else{
				details += "<tr><td><span>"+appTypeStr+": </span></td><td>"+LANG.UI_VOL_CDP_JOB_DETAILS_NO_CONFIGURE_APPLICATION+"</td>";
			}
			details += "</tr>";
			return details;
		}
    	//恢复数据库详情
    	var getRecoveryVolDetails = function(data){
    		var timepointDes = data[12];
    		var details = "<tr>&nbsp;&nbsp;<td class = 'ml25  min-width100'> "+LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIMEPOINT+""+data[11]+"</td><tr>&nbsp;&nbsp;<td> "+LANG.UI_VOL_CDP_JOB_DETAILS_TIME_POINT_DES+" "+timepointDes+"</td>";
    		details += "</tr>";
    		return details;
    	}
    }
    //获取任务对应的应用信息
    var setTaskAppTree = function(zNodes,treeId){
		var setting = {
			check: {
				enable: false,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					rootPId: 0
				},key: {
					title: "title"
				}
			},
			view: {
				fontCss: getFontCss,
			}
		};
		zTreeCdpApp = $.fn.zTree.init($("#"+treeId), setting, zNodes);
	}

	var getFontCss = function(treeId, treeNode) {
		var css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.inbackup){
			css = {color:"green", "font-weight":"bold"};
		}
		if(!!treeNode.highlight){
			//搜索使用的样式
			css = {color:"#A60000", "font-weight":"bold"};
		}
		return css;
	}

	//设置全局的滚动高度
	var setScrollHeight = function(scrollTop){
		_scrollHeight = scrollTop;
	}

	//根据任务状态设置按钮权限
    var setBtnStatus = function(){
    	var runningStage = _taskCurrentRunningStage;
    	$('.taskOperateButton').removeAttr("disabled");
    	switch(_taskStatus){
    		case 1:	  //task is waitting for running
    			setControlBtn('start', true);
	    		setControlBtn('stop', false);
	    		setControlBtn('pause', false);
	    		setControlBtn('createlable', false);
	    		setControlBtn('stoptakeover', false);
	    		setControlBtn('startfailback', false);
	    		setControlBtn('stoptfailback', false);
	    		setControlBtn('delete', true);
	    		if(_taskType==CONF.TASK_TYPE.VOL_CDP_TAKEOVER ){
	    			setControlBtn('takeover', true);
	    		}else{
	    			setControlBtn('takeover', false);
	    		}
	    		if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){
					setControlBtn('stoptakeover', false);
				}
	    		if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
	    			$('.taskOperateButton').attr("disabled","disabled");
	    		}
	    		break;
    		case 2:   //task is running
				setControlBtn('start', false);
				setControlBtn('startIncr', false);
				setControlBtn('startDiff', false);
				setControlBtn('startLog', false);
				setControlBtn('stop', true);
				//bug 18595需求，接管任务启动中允许停止接管任务
				// if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING ){
				// 	$('.taskOperateButton').attr("disabled","disabled");
				// }
				break;
	    	case 10:  //运行和准备中,禁用运行
	    		setControlBtn('start', false);
	    		setControlBtn('startIncr', false);
	    		setControlBtn('startDiff', false);
	    		setControlBtn('startLog', false);
	    		setControlBtn('stop', true);
	    		break;
	    	case 4: //停止,禁用停止
	    		setControlBtn('start', true);
	    		setControlBtn('startIncr', true);
	    		setControlBtn('startDiff', true);
	    		setControlBtn('startLog', true);
	    		setControlBtn('stop', false);
	    		break;
	    	case 5:  //任务停止中
	    		$('.taskOperateButton').attr("disabled","disabled");
	    		break;
    		default:
    			//其他状态,开启控制
    			setControlBtn('start', true);
	    		setControlBtn('startIncr', true);
	    		setControlBtn('startDiff', true);
	    		setControlBtn('startLog', true);
	    		setControlBtn('stop', true);
    			break;
    	}
    }

	//设置按钮是否可用
    var setControlBtn = function(id, available){
    	if(available){
    		$("." + id).find('a').removeClass('disablebtn');
    	}else{
    		$("." + id).find('a').addClass('disablebtn');
    	}
    }

    var initListener = function(){
    	$('.stop').unbind().on('click', stopJob);   //终止任务
    	$('.start').unbind().on('click', startJob); //启动完备任务
    	$('.startIncr').unbind().on('click', startIncr);//增量
		$('.startDiff').unbind().on('click', startDiff);//差异
		$('.startLog').unbind().on('click', startLog);//日志
		$('.takeover').unbind().on('click', startTakeover);  //启动接管
		$('.stoptakeover').unbind().on('click',stopTakeover); //停止接管
		$('.startfailback').unbind().on('click',startFailback); //启动回切
		$('.stoptfailback').unbind().on('click',stopFailback); //停止回切
		$('.createlable').unbind().on('click',createLable);  //创建标签点
		$('.delete').unbind().on('click',deleteTask);  //删除任务
		$('.disableSrEnable').unbind('click').click(autoTakeoverEnableAndDisableConf);
		$('#transfer_thread_number').off().blur(transferThreadNumberChange);
    }

    //校验输入的传线程个数是否符合规则
    var transferThreadNumberChange = function(){
    	var transferThreadNumber = $("#transfer_thread_number").val();
		if(transferThreadNumber>4){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE1);
			$("#transfer_thread_number").val(1);
        	return false;
		}
		if(transferThreadNumber<1){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
			$("#transfer_thread_number").val(1);
        	return false;
		}
    }

    //删除任务
    var deleteTask = function(){
    	var data = {};
		data = jobParams;
		data.taskuuid = $('#task_uuid').val();
		data.uuid = $('#task_uuid').val();
		data.taskType = _taskType;
		data.module = CONF.MODULE_TYPE.VOL_CDP;
		params = JSON.stringify(data);
		var deleteFlag = false;
		var message = LANG.UI_JOB_DELETE_JOB_TIPS;
		bootbox.confirm({
            title: LANG.UI_JOB_DELETE_JOB,
            message: message,
            callback: function(r) {
                if(!r) return;
                if(!deleteFlag){
                	Metronic.blockUI({target: '#jobDetail',animate: true});
                	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'deleteJob',p:params}, function(d){
                		Metronic.unblockUI('#jobDetail');
                		if(OPREL(d)){
                		}
                	});
                	deleteFlag = true;
                }
            }
        });
    }
    //启动接管
    var startTakeover = function (){
    	if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
    	startJobUnify('startJob', CONF.TASK_TYPE.VOL_CDP_TAKEOVER); //启动函数,模块类型
    }
    // 停止接管
    var stopTakeover = function (){
    	var initErrorFlag = false;
		getUserPassword();
		var bootBoxText = LANG.UI_VOL_CDP_JOB_STOP_TAKEOVER_CONFIRM;
		if(_taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){
			var bootBoxText = LANG.UI_VOL_CDP_JOB_STOP_TAKEOVER_AND_VERIF_CONFIRM;
		}
		//回切阶段下停止接管
		var currentState = _taskCurrentRunningStage;
		if(currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
			bootBoxText = LANG.UI_VOL_CDP_JOB_FAILBACK_STOP_TAKEOVER_CONFIRM;
		}

		bootbox.prompt({
		    title: bootBoxText,
		    inputType: 'password',
		    callback: function (result) {
		    	if(result == null) return;
		        if(hex_md5(result) == _UserPassword){
		        	_userIsVerify = true;
		        	stopTakeoverJob();
		        }else{
		        	$('.bootbox-input').css('border-color', "#a94442");
		        	if(!initErrorFlag){
		        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
		        		$('.bootbox-input').after(des);
		        		initErrorFlag = true;
		        	}
					return false;
	        	}
	        }
		});
    }
    //停止接管任务
    var stopTakeoverJob = function(){
    	var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = CONF.MODULE_TYPE.VOL_CDP;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'stopVolCdpTakeover',p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');

			if(_takeoverApplicationRol) { //接管验证任务
				var  jsonData = JSON.parse(d);
				if(jsonData.re){
					UIToastr.showSuccess(LANG.UI_VOL_CDP_JOB_DETAILS_STOP_VERIF_TASK,LANG.UI_VOL_CDP_JOB_DETAILS_STOP_VERIF_TASK + LANG.UI_VOL_CDP_OPERATION_SUCCESS);
				}else{
					UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_STOP_VERIF_TASK,LANG.UI_VOL_CDP_JOB_DETAILS_STOP_VERIF_TASK + LANG.UI_VOL_CDP_OPERATION_FAIL);
				}
				return;
			}
    		if(OPREL(d)){
    		}
    	});
    }

	//启动 接管回切，获取回切执行对象详细信息
	var startFailback = function (){
		var task_uuid = $('#task_uuid').val();
		var info = {};
		info.task_uuid = task_uuid;
		info.task_type = CONF.TASK_TYPE.VOL_CDP_BACKUP; //备份或接管任务
		info.start_type = 14; //启动回切
		var params = JSON.stringify(info);

		Metronic.blockUI({target: '#jobDetail',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:"getStartTaskObjectInfo",p:params}, function(d) {
			Metronic.unblockUI('#jobDetail');
			var data = JSON.parse(d);
			if(data.length==0){
				takeoverFailbackupConf();
				return;
			}
			var masterInfo = data['master_info'];
			var targetMachine = data['target_machine'];
			var targetVol = data['target_vol'];
			var volStr = $.map(targetVol, function(item) {
				var name = item.vol_name;
				return name;
			}).join(', ');

			var titleTips = "<span>" + LANG.UI_VOL_CDP_BACKUP_TIPS + "</span>:  ";
			var desSpan = " <span style='font-size: 14px;'>"
				+ LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS1 + "  "
				+ masterInfo + " " + LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS2 + "   "
				+ targetMachine + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS1
				+ "  [  " + volStr+" ],  "+LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS
				+ LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS +"</br></span>"
				+ "<span>"+LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS1+"</span>";
			var failBackDes = titleTips + desSpan;

			var initErrorFlag = false;
			getUserPassword();
			bootbox.prompt({
				title: failBackDes,
				inputType: 'password',
				callback: function (result) {
					if(result == null) return;
					if(hex_md5(result) == _UserPassword){
						_userIsVerify = true;
						startFailbackJob();
					}else{
						$('.bootbox-input').css('border-color', "#a94442");
						if(!initErrorFlag){
							var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
							$('.bootbox-input').after(des);
							initErrorFlag = true;
						}
						return false;
					}
				}
			});
		});
	}

   /**
    * 启动回切任务
    */
    var startFailbackJob = function (){
    	var task_uuid = $('#task_uuid').val();
    	var data = {};
		data = jobParams;
		data.uuid = task_uuid;
		data.module = CONF.MODULE_TYPE.VOL_CDP;
		var params = JSON.stringify(data);
    	Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getTaskFailbackInfo',p:params}, function(data){
    		Metronic.unblockUI('#jobDetail');
    		var data = JSON.parse(data);
    		if(data.length>0){
    			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startVolCdpTaskCatback',p:params}, function(d){
            		if(OPREL(d)){}
            	});
    		}else{
    			takeoverFailbackupConf();
    		}
    	})
    }

    //停止回切,发送停止接管控制码
    var stopFailback = function (){
    	stopTakeover();
    }

    //输入备注信息，创建标签点
    var createLable = function(){
    	bootbox.prompt({
            title: LANG.UI_VOL_CDP_JOB_DETAILS_ADD_LABEL_REMARK,
            callback: function(result) {
            	if(result=="") {
            		UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABELNODE, LANG.UI_VOL_CDP_JOB_DETAILS_LABELNODE_IS_NULL_MESSAGE);
        			return false;
            	}else if(!result){
            		return;
            	}
            	submitLableRemark($.trim(result));
            }
        });
    }

    //提交创建标签点
    var submitLableRemark = function(remark){
    	var byteLength = remark.replace(/[^\u0000-\u00ff]/g,"aa").length;
    	if(byteLength>256){
    		UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABELNODE, LANG.UI_VOL_CDP_JOB_DETAILS_LABELNODE_MESSAGE);
			return false;
    	}
    	Metronic.blockUI({target: '#jobDetail',animate: true});
    	var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = CONF.MODULE_TYPE.VOL_CDP;
		data.remark = remark;
		var params = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'createVolCdpLablePoint',p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
    }

    //停止
	var stopJob = function(){
		if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
		if(_taskType==CONF.TASK_TYPE.VOL_CDP_BACKUP || _taskType ==CONF.TASK_TYPE.VOL_CDP_REPLICATION || _taskType==CONF.TASK_TYPE.VOL_CDP_RECOVERY){
			var initErrorFlag = false;
			getUserPassword();
			bootbox.prompt({
			    title: LANG.UI_VOL_CDP_JOB_STOP_CONFIRM,
			    inputType: 'password',
			    callback: function (result) {
			    	if(result == null) return;
			        if(hex_md5(result) == _UserPassword){
			        	_userIsVerify = true;
			        	opJob('stopJob');
			        }else{
			        	$('.bootbox-input').css('border-color', "#a94442");
			        	if(!initErrorFlag){
			        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
			        		$('.bootbox-input').after(des);
			        		initErrorFlag = true;
			        	}
						return false;
		        	}
		        }
			});
		}else{
			opJob('stopJob');
		}
	}

	var opJob = function(funName){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = CONF.MODULE_TYPE.VOL_CDP;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
	}
	//初始化当前用户密码用于删除二次确认
	var getUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
    //启动完全
	var startJob = function(){
		if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
		if(_taskType==CONF.TASK_TYPE.VOL_CDP_RECOVERY){
			getStarTaskObjectInfo();
		}else{
			startJobUnify('startJob', 1);
		}
	}
	/**
	 * 获取启动任务主备机/源和目标机详情
	 */
	var getStarTaskObjectInfo = function(){
		var info = {};
		info.task_uuid = $('#task_uuid').val();;
		info.task_type = _taskType;
		info.start_type = 1;
		var paramsInfo = JSON.stringify(info);
		Metronic.blockUI({target: '#jobDetail',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:"getStartTaskObjectInfo",p:paramsInfo}, function(d){
			Metronic.unblockUI('#jobDetail');
			var data = JSON.parse(d);
			var masterInfo = data['master_info'];
			var targetMachine = data['target_machine'];
			var titleTips= "<span>"+LANG.UI_VOL_CDP_BACKUP_TIPS+"</span></br>";
			var desSpan = "<span style='font-size: 14px;'>"+LANG.UI_VOL_CDP_JOB_START_RECOVERY_OPERATE_TIPS1+ "： " + masterInfo +" " + LANG.UI_VOL_CDP_JOB_START_RECOVERY_OPERATE_TIPS2 + ": "+ targetMachine +" " +LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS+"</span>";
			var recoverDes = titleTips +desSpan ;

			var initErrorFlag = false;
			getUserPassword();
			bootbox.prompt({
				title: recoverDes,
				inputType: 'password',
				callback: function (result) {
					if(result == null) return;
					if(hex_md5(result) == _UserPassword){
						_userIsVerify = true;
						startJobUnify('startJob', 1);
					}else{
						$('.bootbox-input').css('border-color', "#a94442");
						if(!initErrorFlag){
							var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
							$('.bootbox-input').after(des);
							initErrorFlag = true;
						}
						return false;
					}
				}
			});
		});
	}
	//启动差异
	var startDiff = function(){
		if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
		startJobUnify('startJob', 3);
	}
	//启动增量
	var startIncr = function(){
		if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
		startJobUnify('startJob', 2);
	}

	//启动日志
	var startLog = function(){
		if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
		startJobUnify('startJob', 4);
	}

	//启动任务
	var startJobUnify = function(funName, type){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = CONF.MODULE_TYPE.VOL_CDP;
		data.startType = type;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
			if(type==CONF.TASK_TYPE.VOL_CDP_TAKEOVER && _takeoverApplicationRol) { //接管验证任务
				var jsonData = JSON.parse(d);
				if(jsonData.re){
					UIToastr.showSuccess(LANG.UI_VOL_CDP_JOB_DETAILS_START_VERIF_TASK,LANG.UI_VOL_CDP_JOB_DETAILS_START_VERIF_TASK + LANG.UI_VOL_CDP_OPERATION_SUCCESS);
				}else{
					UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_START_VERIF_TASK,LANG.UI_VOL_CDP_JOB_DETAILS_START_VERIF_TASK + LANG.UI_VOL_CDP_OPERATION_FAIL);
				}
				return;
			}
    		if(OPREL(d)){
    		}
    	});
	}
	  //初始化下对应控件下拉取值范围,默认值
	var initSpinner = function(){

		$('#memory_cache_div').spinner({value:_memoryCacheSize, step: 1, min: 1, max: 1024});
		$('#file_cache_div').spinner({value:_fileCacheSize, step: 1, min: 1, max: 1024});
		$('#transfer_thread_div').spinner({value:1, step: 1, min: 1, max: 4});

	}

	//清除表格数据
	var clearTable = function(table, id){
		var tr = $('#' + id + ' tbody tr');
		for(var i=0; i<tr.length; i++){
			table.row().remove();
		}
		table.row().draw();
	}
	/**
	 * 表格的配置
	 */
	var gettableDefaultsOpt = function(){
		var defaultsOpt = {
			"searching": false,
			"ordering": false,
			"paging":false,
			"info":false,
			"language": { // language settings
                "emptyTable": LANG.UI_TOOLS_NO_DATA,
                "zeroRecords": LANG.UI_TOOLS_NO_DATA,
            },
		};
		return defaultsOpt;
	}
	 /**
	 * 获取授权的细节功能
	 * @param unknown $params
	 */
	var getAuthFunc = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getSysAuthFunc',p:{}}, function(d){
			var data = JSON.parse(d);
			authFun = data;
			//双机镜像
			if(!data.doubleHostMirror){
				$('.doublehostmirrorview').hide();
			}

		});
	}
	 /**
     * @function 生成uuid
     */
    var getUuid = function() {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if(len) {
          for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
          var r;
          uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
          uuid[14] = '4';
          for(i = 0; i < 36; i++) {
            if(!uuid[i]) {
              r = 0 | Math.random() * 16;
              uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
            }
          }
        }
        return uuid.join('');
    }

	var addListeners = function () {
		$('#takeoverFileCachePath').blur(blurInput)
		$('#catBackTaskSubmit').click(catBackTaskSubmit);
		$('#closeCatBack').click(catBack);
		$('#closeBack').click(catBack);
		// 重建分区开关
		$('#rebuildPartChange .bootstrap-switch').on('click', function () {
			/**
			 * 1. 这里不监听#rebuildPartSwitch的switchChange.bootstrapSwitch事件，原因为手动修改开关会触发此事件，导致数据额外加载
			 * 2. 这里也直接不监听#rebuildPartChange，因为这个元素太大了，监听它会导致点击边缘也会触发事件，导致数据无故刷新
			 */
			loadTargetHostVolOrDisk($('#rebuildPartSwitch').is(':checked') ? 1 : 2);
		});
	}

	/**
	 * 加载<主备映射关系>的目标卷/磁盘信息
	 * @param {number} loadType 加载 [1磁盘 2卷]
	 */
	var loadTargetHostVolOrDisk = function (loadType = 2) {  // 重建分区开关变化
		// 清空除了第一项外的选项
		var data = _volGrid.getDataTable().data();
		for (var j = 0; j < data.length; j++) {
			$('#catback_volume_' + j + ' option').not('option:first').remove();
			$("#catback_volume_" + j).unbind('change').bind('change', (catBackTargetVolChange));
		}
		_catBackVolSet = [];

		var failBackupHostUuid = $('#failBackHost').val()
		if (loadType === 1) {  // 加载磁盘信息
			getSelectHostDiskTarget(failBackupHostUuid);
		} else if (loadType === 2) {  // 加载分区信息
			getSelectHostVolTarget(failBackupHostUuid);
		} else {
		}
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
	/**
	* 当有多个tab栏时，改成轮滑效果，不换行
	*/
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
        //main function to initiate the module
        init: function () {
			initSwiper();
        	initBasicInfo();
        	getAuthFunc();
        	initSpeed();
        	initVolInfoGrid();
        	initLogGrid();
        	initHistoryGrid();
        	initMotionImg();
        	initSpinner();
        	initTabShowEvent();
			addListeners();
			watchEchartSizeChange();
        }
    };

}();

jQuery(document).ready(function() {
	VolCdpJobDetails.init();
});