var VMJobDetails = function () {
	var subModuleType = CONF.VM_SUB_MODULE.VM;

	var _scrollHeight = 0; //任务日志全局高度

	var initChartFlag = false; //任务曲线图初始化标志

	var _taskStatus; //监控任务状态

	var jobParams = {}; //操作需要参数

	var recoveryConfig = {};

	var initVmTableFlag = false; //虚拟机列表初始化标志
	var initHistoryTableFlag = false; //历史任务列表初始化标志

	var authFun = [];

	var myChart;

	var _jobUuid = $("#task_uuid").val();
	var vmData = []; //记录虚拟机列表已勾选的
	var originalTotal = 0; // 记录初始化加载表格后的总条数
	var _userUuid; // 任务所属用户uuid
	var taskTypeFlag;

	const RECOVERY_INTEGRITY_POLICY_DESC_MAP = {
		0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY,
		1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY,
		2: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK,
	}; // 验证策略 - 完整性校验异常处理描述

	//初始化基本信息
	var initBasicInfo = function(){

		var updateInterval = 5000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
        		return;
        	}
			var data = {};
			data.job_uuid = $("#task_uuid").val();
			pAjaxRequest(data, "/api/v1/vm/jobs/basic_info", "GET", function (d) {
				setBasicInfo(d.data, timerTask.VMJobDetails_taskRunningInfo);
			}, false);
			timerTask.VMJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		if (!data.flag) {
			LOCATION('./content/platform/jobs/jobs.php', 'task');
		}
		taskTypeFlag = data.taskTypeFlag;

		// 显示为虚拟机列表还是实例列表
		if (CONF.VMTYPE_GROUP.OPENSTACK.includes(data.hypervisor)) {
			$(`#vmli span`).text(LANG.UI_CLOUD_PLATFORM_INSTANCE_LIST);
		}
		$(`#vmli`).show();

		if(data.taskTypeFlag == 1){
			$('.vmStartDiv').show();
			$('#vmbackuptips').find('li').text(CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType
				? LANG.UI_BACKUP_SELECT_INSTANCE_BACKUP_TIPS : LANG.UI_BACKUP_SELECT_VM_BACKUP_TIPS);
			$('#vmbackuptips').show();
			$('#taskOperateDivBackup').show();
		}else{
			$('.vmStartDiv').hide();
			$('#vmbackuptips').hide();
			$('#taskOperateDivRecovery').show();
			recoveryConfig = { "targets": [0], "visible": false };
		}

		//恢复任务的虚拟机列表表格信息处理
		if (data.taskTypeFlag == 2) {
			$('#th-tasktype').show();
			$('#th-backupmode').hide();
		} else {
			$('#th-tasktype').hide();
			$('#th-backupmode').show();
		}

		//基本信息
		$('#taskName').html(data.taskName);
		$('#taskName').attr('title', data.taskName);
		$('#taskType').html(data.taskType);
		if(data.status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.statusValue) + '" >' + data.status + '</span>');
		}

		//保存获取到的任务参数
		_userUuid = data.user_uuid;
		_taskStatus = data.statusValue;
		jobParams.status = data.statusValue;
		jobParams.taskType = data.taskTypeFlag;
		jobParams.subModule = data.hypervisor;
		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);
		$('#endTime').html(data.endTime);
		$('#taskStage').html(data.taskStage);
		//存储信息
		var node = data.storageInfo.node;
		var storage = data.storageInfo.storage;
		var high = data.storageInfo.high;
		var storageInfo = '';
		if(!storage){
			//没有存储信息,自动选择存储
			// storageInfo = LANG.UI_JOB_AUTO_SELECT_STORAGE;
			storageInfo = '--';
		}else{
			storageInfo = storage.name + "(" + storage.type + ")<br>";
			if(!storage.quota_flag){
				storageInfo += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
				LANG.UI_JOB_FREE_SIZE + ":" + storage.free_size;
			}else{
				storageInfo += storage.quota_des;
			}
		}

		$('#nodeinfo').html(data.storageInfo.node_pool_uuid
			? data.storageInfo.node_pool_nickname + ":<br/>" + node.name + "<br>" + node.ip
			: node.name + "<br>" + node.ip);
		$('#storageinfo').html(data.storageInfo.storage_pool_nickname
			? data.storageInfo.storage_pool_nickname + ":<br/>" + storageInfo
			: storageInfo);
		//高级信息
		if (high) {
			$('#deduplication').html(getFlagLevelInfo(high.deduplication));
			$('#compressed').html(getFlagLevelInfo(high.compressed));
			// 压缩等级
			if (high.compressed) {
				var method = '';
				switch (high.compress_method) {
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
				$('#compressed').html(method);
			}
			$('#blocksize').html(high.blocksize);
			$('#dataContainerSize').html(high.data_container_size);
			$('#redundantDataProportion').html(high.redundant_data_proportion);
			$('#encryptStorage').html(getFlagLevelInfo(high.encrypt_flag));
			// 存储加密算法
			if (high.encrypt_flag) {
				let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
				if (high.encrypt_method == 2) {
					method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
				}
				$('#encryptStorage').html(method);
			}
			$('#passwordAuto').html(getFlagLevelInfo(high.password_auto_flag));
		}

		//高级
		var serialSnapshot = "--";
		if(1 == data.modeStrategy.serialSnapshot){
			serialSnapshot = LANG.UI_JOB_SERIAL_SNAPSHOT;
			$('.presnapshotDiv').show();
		}else if(2 == data.modeStrategy.serialSnapshot){
			serialSnapshot = LANG.UI_JOB_PARALLEL_SNAPSHOT;
			$('.presnapshotDiv').hide();
		}
		$('#serialSnapshot').html(serialSnapshot);
		$('#storageSnapshot').html(getFlagLevelInfo(data.modeStrategy.storageSnapshot));
		if (CONF.VMTYPE_GROUP.HUAWEIKVM.includes(data.hypervisor)) {
			$('.snapshotDelSpeedDiv').show();
			if (0 == data.modeStrategy.snapshotDelSpeed) {
				$('#snapshotDelSpeed').html('--');
			} else {
				$('#snapshotDelSpeed').html(data.modeStrategy.snapshotDelSpeed + ' MB/s');
			}
		} else {
			$('.snapshotDelSpeedDiv').hide();
		}
		$('#CBT').html(getFlagLevelInfo(data.modeStrategy.cbtMode));
		$('#resetcbt').html(data.modeStrategy.resetCbt);
		if (CONF.VM_TYPE.HYPERV == data.hypervisor && 1 == data.modeStrategy.incModeValue) {
			// 增量模式为普通时显示为关闭
			$('#incMode').html(getFlagLevelInfo(false));
		} else {
			$('#incMode').html(data.modeStrategy.incMode);
		}
		$('#silentSnapshot').html(getFlagLevelInfo(data.modeStrategy.quiesceSnapshot));
		$('#highspeedDiskCbt').html(getFlagLevelInfo(data.modeStrategy.highspeedDiskCbt));

		$('#parseFs').html(getFlagLevelInfo(data.modeStrategy.parseFs));
		$('#noSwapFile').html(getFlagLevelInfo(data.modeStrategy.noSwapFile));
		$('#noDeletedFile').html(getFlagLevelInfo(data.modeStrategy.noDeletedFile));
		$('#noPartitionGap').html(getFlagLevelInfo(data.modeStrategy.noPartitionGap));
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
		$('#presnapshot').html(getFlagLevelInfo(data.modeStrategy.presnapshot));
		//设置完整性校验
		$('#verify').html('<span class="label ' + getVerifyClass(data.verifyValue) + '" >' + data.verify + '</span>');

		if(!data.modeStrategy.parseFs){
			$('.parseFsdiv').hide();
		}

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

		// 重试策略
		if (Array.isArray(data.retry_strategy) && !data.retry_strategy.length) {
			$('.retryDiv').hide();
		} else {
			$('#network_retry_times').html(data.retry_strategy.network_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
			$('#network_retry_interval').html(data.retry_strategy.network_retry_interval + LANG.UI_PUBLIC_SECOND);
			$('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
			if (data.retry_strategy.op_retry_flag) {
				$('.op-retry-form-item').show();
			} else {
				$('.op-retry-form-item').hide();
			}
			$('#op_retry_times').html(data.retry_strategy.op_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
			$('#op_retry_interval').html(data.retry_strategy.op_retry_interval + LANG.UI_PUBLIC_SECOND);
			$('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
			if (data.retry_strategy.task_retry_flag) {
				$('.task-retry-form-item').show();
			} else {
				$('.task-retry-form-item').hide();
			}
			let task_retry_object = '';
			if (1 == data.retry_strategy.task_retry_object) {
				task_retry_object = LANG.UI_RETRY_FAILED_OBJS_IN_TASK;
			} else if (2 == data.retry_strategy.task_retry_object) {
				task_retry_object = LANG.UI_RETRY_ALL_OBJS_IN_TASK;
			}
			$('#task_retry_object').html(task_retry_object);
			$('#task_retry_times').html(data.retry_strategy.task_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
			$('#task_retry_interval').html(data.retry_strategy.task_retry_interval / 60 + LANG.UI_PUBLIC_MINUTE);
			$('.retryDiv').show();
		}

		// 过载保护
		$('#ignore_resource_limit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));

		// 传输策略
		let transportStrategy = data.transportStrategy;
		// 合并data中的部分参数
		transportStrategy = $.extend(transportStrategy, {
			thread_num: data.thread_num,
			applianceDes: data.applianceDes,
			agent_pool_info: data.agent_pool_info,
			transport_ip_segment: data.transport_ip_segment,
			backupNodeIp: data.backupNodeIp
		});
		$.fn.showVmJobTransferStrategy(transportStrategy, data.taskTypeFlag);

		var hypervisor = data.transportStrategy.hypervisor;
		$('#createTime').html(data.createTime);
		if(1 == data.taskTypeFlag){
			//备份策略
			$('#nextTime').html(data.nextTime);
			var timeStrategy = getTimeStrategy(data.timeStrategy, data.timeStrategyBackupType);
			$('#fulldes').html(timeStrategy.full);
			$('#incdes').html(timeStrategy.inc);
			$('#diffdes').html(timeStrategy.diff);
			$('#pincrdes').html(timeStrategy.pincr);
			$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
			$('.fullDiv').show();
			$('.incrDiv').show();
			$('.diffDiv').show();
			$('.pincrDiv').show();
			$('.baknodeIpDiv').hide();
			switch (data.timeStrategyBackupType) {
				case 'strategy':
					$('#timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);
					break;
				case 'oncetime':
					$('#timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);
					break;
				default:
					$('#timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);
					$('.fullDiv').hide();
					$('.incrDiv').hide();
					$('.diffDiv').hide();
					$('.pincrDiv').hide();
					break;
			}
			// 在编排中的任务，显示按编排策略执行（原时间策略不生效）
			if (data.task_orchestration_plan_flag) {
				$('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
			}


			//默认隐藏增量模式
			$('.incLeveldiv').hide();
			// 默认隐藏重置CBT
			$('.resetCbtDiv').hide();
			// 隐藏高速磁盘CBT
			$('.highspeedDiskCbtDiv').hide();

			//隐藏部分虚拟化没有的配置项
			switch(hypervisor){
				case CONF.VM_TYPE.VMWARE:
				case CONF.VM_TYPE.CLOUDVIEW:
				case CONF.VM_TYPE.CLOUDVIEWSVM:
					//显示增量模式
					$('.incLeveldiv').show();
					if (data.modeStrategy.cbtMode) {
						$('.resetCbtDiv').show();
					} else {
						$('.resetCbtDiv').hide();
					}
					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					break;
				case CONF.VM_TYPE.HYPERV:
					$('.silentSnapshotdiv').hide(); //隐藏静默快照
					$('.blocksizediv').hide();//隐藏数据块大小
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}
					$('.encryptStoragediv').show(); //显示数据加密
					//显示增量模式
					$('.incLeveldiv').show();
					break;
				case CONF.VM_TYPE.CITRIX:
				case CONF.VM_TYPE.XCPNG:
					//隐藏数据块大小和传输模式
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}
					//显示增量模式
					$('.incLeveldiv').show();
					if(data.nosnapshot){
						$('.silentSnapshotdiv ').hide();		//隐藏静默快照
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					break;
				case CONF.VM_TYPE.INCLOUD:
				case CONF.VM_TYPE.VGATE:
				case CONF.VM_TYPE.WINSERVER:
				case CONF.VM_TYPE.DSERVER:
					//隐藏数据块大小
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					break;
				case CONF.VM_TYPE.FUSIONXEN:
					$('.transportEncryptdiv').hide();
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					break;
				case CONF.VM_TYPE.FUSIONKVM:
				case CONF.VM_TYPE.XFUSIONKVM:
					//隐藏块大小
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示增量模式
					$('.incLeveldiv').show();
					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					//描述为一致性快照
					$('.silentSnapshotdiv .strategy-group__form__item__label').html(LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT);
					break;
				case CONF.VM_TYPE.RHV:
				case CONF.VM_TYPE.OVIRT:
				case CONF.VM_TYPE.OLVM:
				case CONF.VM_TYPE.ZVIRT:
				case CONF.VM_TYPE.HOSTVM:
				case CONF.VM_TYPE.REDVIRT:
				case CONF.VM_TYPE.ROSAVIRT:
					//隐藏数据块大小
					$('.silentSnapshotdiv').hide();
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();

					//显示增量模式
					$('.incLeveldiv').show();
					break;
				case CONF.VM_TYPE.NEOKYLIN:
				case CONF.VM_TYPE.OSEASYVSERVER:
				case CONF.VM_TYPE.WINDIY:
				case CONF.VM_TYPE.ZSTACK:
				case CONF.VM_TYPE.ZSTACKZSPHERE:
				case CONF.VM_TYPE.NEXAVM:
				case CONF.VM_TYPE.NEXAVMNCSSV:
				case CONF.VM_TYPE.XSKY:
				case CONF.VM_TYPE.WINHONGKVM:
				case CONF.VM_TYPE.PROXMOX:
				case CONF.VM_TYPE.CLOUDVIEWKVM:
				case CONF.VM_TYPE.XHERE:
					//隐藏数据块大小
					$('.silentSnapshotdiv').hide();
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();

					//显示增量模式
					$('.incLeveldiv').show();
					break;
				case CONF.VM_TYPE.SMARTX:
				case CONF.VM_TYPE.ARCFRA:
					//描述为一致性快照
					$('.silentSnapshotdiv .strategy-group__form__item__label').html(LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT);
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();

					//显示增量模式
					$('.incLeveldiv').show();
					break;
				case CONF.VM_TYPE.H3C:
				case CONF.VM_TYPE.H3CCASCVD:
					//隐藏数据块大小
					$('.silentSnapshotdiv').hide();
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					if (hypervisor == CONF.VM_TYPE.H3CCASCVD) {
						// 显示高速磁盘CBT
						$('.highspeedDiskCbtDiv').show();
					}
					//显示增量模式
					$('.incLeveldiv').show();
					break;
				case CONF.VM_TYPE.INCLOUDKVM:
					//隐藏数据块大小
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}
					$('.transportModediv').show();
					//显示增量模式
					$('.incLeveldiv').show();

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					break;
				case CONF.VM_TYPE.INSPURVVDK:
				case CONF.VM_TYPE.KSPHERE:
					//隐藏数据块大小
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}
					//显示增量模式
					$('.incLeveldiv').show();

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					$('#startDiff').hide();
					$('#taskStartDiff').hide();
					$('.diffDiv').hide();

					break;
				case CONF.VM_TYPE.FLEXCLOUD:
				case CONF.VM_TYPE.OPENSTACK:
				case CONF.VM_TYPE.FLEXHCS:
				case CONF.VM_TYPE.INCLOUDOPENSTACK:
				case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
				case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
				case CONF.VM_TYPE.EASYSTACK: //36
				case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
				case CONF.VM_TYPE.CTSIOPENSTACK: //38
				case CONF.VM_TYPE.AWCLOUD: //39
				case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
					subModuleType = CONF.VM_SUB_MODULE.PRIVATE_CLOUD;
					//隐藏数据块大小和传输模式
					if (CONF.VM_TYPE.EASYSTACK == hypervisor || CONF.VM_TYPE.HUAWEICLOUDSTACK == hypervisor) {
						// easystack无一致性快照
						$('.silentSnapshotdiv').hide();
					}
					//描述为一致性快照
					$('.silentSnapshotdiv .strategy-group__form__item__label').html(LANG.UI_BACKUP_HUAWEI_KVM_SILENTSNAPSHOT);
//					$('.parsefsDiv').hide();
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					//显示增量模式
					$('.incLeveldiv').show();
					break;
				case CONF.VM_TYPE.SDCOS:
				case CONF.VM_TYPE.SANGFOR:
				case CONF.VM_TYPE.KVM:
					//隐藏数据块大小和传输模式
					$('.silentSnapshotdiv').hide();
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();
					//显示增量模式
					$('.incLeveldiv').show();
					break;
				case CONF.VM_TYPE.SANGFORVVDK:
					//隐藏数据块大小
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();

					//显示增量模式
					$('.incLeveldiv').show();
					//隐藏静默快照
					$('.silentSnapshotdiv').hide();
					break;
				case CONF.VM_TYPE.LENOVOAIO:
					//隐藏数据块大小
					$('.blocksizediv').hide();
					if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
						$(`.storageDiv`).hide();
					}

					//显示数据加密
					$('.encryptStoragediv').show();
					$('.passwordAutodiv').show();

					//显示增量模式
					$('.incLeveldiv').show();
					//隐藏静默快照
					$('.silentSnapshotdiv').hide();

					$('#startDiff').hide();
					$('#taskStartDiff').hide();
					$('.diffDiv').hide();
					break;
			}


			//如果数据加密关闭隐藏自动生成密码开关描述显示
			if(!data.storageInfo.high.encrypt_flag){

				$('.passwordAutodiv').hide();
			}
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				$('.fulldiskDiv').hide();	//隐藏全磁盘恢复
			}
			//如果存储是华为（备份上云） 则隐藏重删 压缩  数据压缩
			if(data.storageInfo.flag && data.storageInfo.storage.type == CONF.BD_STORAGE_TYPE.HUAWEICBR){
				$('.deduplicationdiv').hide();
				$('.compresseddiv').hide();
				$('.encryptStoragediv').hide();
				$('.blocksizediv').show();//显示传输块大小
				$('.transportModediv').show();//显示传输模式
				// $("#transportMode").html("网络传输");
				$('.verifyDiv').show(); //展示完整性校验
				if(data.verifyValue  ==  1){
					$('.verifytypetDiv').show(); //展示校验类型
					$('#verifytype').html(data.verifyType);
				}
			}

			//不管是什么存储  都要展示节点信息
			if(data.storageInfo.flag){
				$("#nodeinfoshow").show();
			}else{
				$("#nodeinfoshow").hide();
			}

			//筛选条件
			showSelectConditions(data.selectConditions, data.autoJoinFlag);

			if (data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
				//磁带获取策略替换保留策略
				$('.reservedDiv .name').html(LANG.UI_GLOBAL_STRATEGY_NAME);
				$('.reservedDiv .value').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
			} else {
				$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
			}

			//磁带隐藏部分信息
			if (data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
				$('.threadCountDiv').hide();
				$('.parallelTransferDiv').hide();
			}

			// <------  BEGIN SAFE STRATEGY  ------>
			$(`.backup-safe-strategy`).show();
			$(`.recovery-safe-strategy`).hide();
			// WORM防护
			let wormCheckFlag = data.safe_strategy.worm_flag;
			$('#backup_worm_flag').html(getFlagLevelInfo(wormCheckFlag));
			if (wormCheckFlag) {
				$('#backup_worm_flag').html(`${data.safe_strategy.worm_protection_time}` + LANG.UI_PUBLIC_UNIT_DAY);
			}
			// 病毒检测
			let virusCheckFlag = data.safe_strategy.virus_scan_flag;
			$(`#virus_check_flag`).html(getFlagLevelInfo(virusCheckFlag));
			if (virusCheckFlag) {
				$(`#virus_check_flag`).html($.fn.getVirusConfigDes(data.safe_strategy, 'backup'));
			}
			// 完整性校验
			$('#integrity_check_flag').html($.fn.getCompleteDetectionBackupDes({
				integrityCheckFlag: data.safe_strategy.integrity_check_flag,
				integrityCheckConfig: data.safe_strategy.integrity_check_config,
				isFullTimepointTitle: true,
				showIncrErrorPolicy: true,
				excludeTitleFlag: true
			}));

			if (!CONF.FUNCTIONS.includes('worm')) {
				// 没有有完整性校验功能
				$('.backup-safe-worm').hide();
			}
			if (!CONF.FUNCTIONS.includes('integrity')) {
				// 没有有完整性校验功能
				$('.backup-safe-integrity').hide();
			}
			if (!CONF.FUNCTIONS.includes('virusKill')) {
				// 没有病毒查杀功能
				$('.backup-safe-virus').hide();
			}
			if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity') && !CONF.FUNCTIONS.includes('virusKill')) {
				// 都没，隐藏安全策略
				$('.safemodeDiv').hide();
			}
			// <------  END SAFE STRATEGY  ------>
		}else{
			$('.filter-div').hide();
			$('#storageli').hide();
			// $('#strategyli').hide();
//			$('#modeli').hide();
			$('#tab_1_2').hide();
			// $('#tab_1_3').hide();
//			$('#tab_1_4').hide();
			//隐藏数据块大小
			$('.blocksizediv').hide();
			if (CONF.RESERVE_STRATEGY_MODE.CHIAN == data.reservedStrategy.strategyMode) {
				$(`.storageDiv`).hide();
			}
			// 时间策略
			let timeDes;
			if (data.timeStrategy.length) {
				let timeStrategy = getTimeStrategy(data.timeStrategy);
				timeDes = LANG.UI_JOB_TIMING_RECOVER + '(' + timeStrategy.full + ')';
			} else {
				timeDes = LANG.UI_JOB_ONCE_TIME_RECOVER;
			}
			$('#recoveryTimeDes').html(timeDes);
			//隐藏部分虚拟化没有的配置项
			switch(hypervisor){
				case CONF.VM_TYPE.HYPERV:
					$('.speedDiv').show();	//显示限速大小
					break;
				case CONF.VM_TYPE.FLEXCLOUD:
				case CONF.VM_TYPE.OPENSTACK:
				case CONF.VM_TYPE.FLEXHCS:
				case CONF.VM_TYPE.INCLOUDOPENSTACK:
				case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
				case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
				case CONF.VM_TYPE.EASYSTACK: //36
				case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
				case CONF.VM_TYPE.CTSIOPENSTACK: //38
				case CONF.VM_TYPE.AWCLOUD: //39
				case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
					subModuleType = CONF.VM_SUB_MODULE.PRIVATE_CLOUD;
					break;
				case CONF.VM_TYPE.SDCOS:
				case CONF.VM_TYPE.SANGFOR:
				case CONF.VM_TYPE.KVM:
					//隐藏传输模式
					$('.recoveryTransportDiv').hide();
					break;
				case CONF.VM_TYPE.RHV:
				case CONF.VM_TYPE.OVIRT:
				case CONF.VM_TYPE.OLVM:
				case CONF.VM_TYPE.ZSTACK:
				case CONF.VM_TYPE.ZSTACKZSPHERE:
				case CONF.VM_TYPE.NEXAVM:
				case CONF.VM_TYPE.NEXAVMNCSSV:
				case CONF.VM_TYPE.XSKY:
				case CONF.VM_TYPE.WINHONGKVM:
				case CONF.VM_TYPE.FUSIONKVM:
				case CONF.VM_TYPE.XFUSIONKVM:
				case CONF.VM_TYPE.PROXMOX:
				case CONF.VM_TYPE.ZVIRT:
				case CONF.VM_TYPE.HOSTVM:
				case CONF.VM_TYPE.CLOUDVIEWKVM:
				case CONF.VM_TYPE.XHERE:
				case CONF.VM_TYPE.REDVIRT:
				case CONF.VM_TYPE.ROSAVIRT:
					//仅网络传输显示加密传输
					if(parseInt(data.transportStrategy.mode_index) != 1){
						$('.recoveryEncryptdiv').hide();
					}else{
						$('.recoveryEncryptdiv').show();
					}
					if (CONF.VM_TYPE.FUSIONKVM == hypervisor || CONF.VM_TYPE.XFUSIONKVM == hypervisor) {
						$('.parallelTransferDiv').show();
						$('.ptVmCountDiv').hide();
						$('.threadCountDiv').hide();
					}
					break;
				case CONF.VM_TYPE.H3C:
				case CONF.VM_TYPE.H3CCASCVD:
					$('.recoveryTransportDiv').hide();
					break;
				case CONF.VM_TYPE.SMARTX:
				case CONF.VM_TYPE.ARCFRA:
				case CONF.VM_TYPE.INSPURVVDK:
				case CONF.VM_TYPE.SANGFORVVDK:
				case CONF.VM_TYPE.KSPHERE:
					// 仅传输代理模式显示加密传输
					if (parseInt(data.transportStrategy.mode_index) == 3) {
						$('.recoveryEncryptdiv').show();
						$('.applianceRecDiv').show(); //显示Appliance
						$('.agentPoolRecDiv').show(); // 显示代理池
					} else {
						$('.recoveryEncryptdiv').hide();
						$('.recovery-transfer-encrypt-method-div').hide();
					}
					break;
			}

			//数据传输网段
			if(data.transport_ip_segment != ""){
				$('.reIpSegmentDiv').show();
				$('#reIpSegment').html(data.transport_ip_segment);
			}else{
				$('.reIpSegmentDiv').hide();
			}
			//展示计算节点
			if (undefined !== data.storageInfo.storage && undefined !== data.storageInfo.storage.storage_type &&
				(CONF.BD_STORAGE_TYPE.CLOUD == data.storageInfo.storage.storage_type || CONF.BD_STORAGE_TYPE.HUAWEICBR == data.storageInfo.storage.storage_type)) {
				$('.nodeDiv').show();
			} else {
				$('.nodeDiv').hide();
			}
			$('#recovernodeinfo').html(data.nodename);
			//校验策略
			// $('.verifyDiv').show(); //展示完整性校验
			// if(data.verifyValue  ==  1){
			// 	$('.verifyalarmDiv').show(); //展示校验告警
			// 	$('#verifyalarm').html('<span class="label ' + getVerifyClass(data.verifyAlarmValue) + '" >' + data.verifyAlarm + '</span>');
			// }

			//隐藏筛选条件
			$('.globalSelectDiv').hide();

			//磁带隐藏部分信息
			if (data.tape_strategy) {
				$('.threadCountDiv').hide();
			}

			// <------  BEGIN SAFE STRATEGY  ------>
			$(`.backup-safe-strategy`).hide();
			$(`.recovery-safe-strategy`).show();
			// 备份点病毒扫描策略
			$(`#virus_scan_way`).html($.fn.getVirusConfigDes(data.safe_strategy, 'recovery'));
			// 完整性校验异常处理
			$(`#integrity_policy`).html(LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_RECOVERY_UNSET);
			if (data.safe_strategy.integrity_check_flag) {
				$(`#integrity_policy`).html(RECOVERY_INTEGRITY_POLICY_DESC_MAP[data.safe_strategy.integrity_check_config.recovery_error_policy]);
			}

			if (!CONF.FUNCTIONS.includes('integrity')) {
				// 没有有完整性校验功能
				$('.recovery-safe-integrity').hide();
			}
			if (!CONF.FUNCTIONS.includes('virusKill')) {
				// 没有病毒查杀功能
				$('.recovery-safe-virus').hide();
			}
			if (!CONF.FUNCTIONS.includes('integrity') && !CONF.FUNCTIONS.includes('virusKill')) {
				// 都没，隐藏安全策略
				$('.safemodeDiv').hide();
			}
			// <------  END SAFE STRATEGY  ------>
		}

		$('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);
		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
			//如果是标准版本,隐藏重复数据删除和深度有效数据提取显示
			$('.deduplicationdiv').hide();
			$('.parseFsdiv').hide();
		};

		//初始化操作按钮
		setBtnStatus(data);

		$('#searchInput').prop('placeholder', CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_TASK_INSTANCE_SEARCH_TIPS : LANG.UI_TASK_VM_SEARCH_TIPS);
	}

	//显示筛选条件
	var showSelectConditions = function (selectConditions, autoJoinFlag) {
		$('#auto_join').html(getFlagLevelInfo(autoJoinFlag));
		var rules = '', value = '';
		if (!selectConditions.select_value.length || 1 == selectConditions.select_value.length && '' == selectConditions.select_value[0]) {
			$('.globalSelectDiv').hide();
			return;
		}
		switch (parseInt(selectConditions.select_type)) {
			case 0:
				//按前缀排除
				rules += CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_BACKUP_INSTANCE_NAME_PREFIX_FILTER : LANG.UI_BACKUP_VM_NAME_PREFIX_FILTER;
				break;
			case 1:
				//按前缀匹配
				rules += CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_BACKUP_INSTANCE_NAME_PREFIX_MATCH : LANG.UI_BACKUP_VM_NAME_PREFIX_MATCH;
				break;
			case 2:
				// 按关键词排除
				rules += CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_BACKUP_INSTANCE_NAME_KEYWORD_FILTER : LANG.UI_BACKUP_VM_NAME_KEYWORD_FILTER;
				break;
			case 3:
				// 按关键词匹配
				rules += CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_BACKUP_INSTANCE_NAME_KEYWORD_MATCH : LANG.UI_BACKUP_VM_NAME_KEYWORD_MATCH;
				break;
			default:
				break;
		}
		value += selectConditions.select_value.join(`<br>`);
		$('.select_conditions').html(rules);
		$('#select_condition').html(value);
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
			case 19: // 挂起
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
		if(!flag){
			html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		}
		return html;
	}

	//得到数据完整性校验的显示类型
	var getVerifyClass  = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-info";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}

	//得到时间策略描述信息
	var getTimeStrategy = function(msg, timeStrategyBackupType){
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
				if (timeStrategyBackupType === 'strategy') { //按策略备份才显示完备补偿
					if (strategy.full_backup_compensation_flag) {
						des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_ON;
					} else {
						des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_OFF;
					}
				}
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
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
			}else{
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + strategy.endTime + LANG.UI_STRATEGY_END;
			}

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
		if(CONF.RESERVE_STRATEGY_MODE.POINT == msg.strategyMode){
			reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
			$(`.mergemodediv`).show();
		}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == msg.strategyMode){
			reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
			$(`.mergemodediv`).hide();
		}
		var methoddes = LANG.UI_STRATEGY_VALUE;
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_NUM + '<br>';
		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_DAY + '<br>';
		}
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == msg.type){
			methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY;
		}
		reservedStr += methoddes + ': ' + msg.value;
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
    	p.job_uuid = $("#task_uuid").val();
        function update(){
        	if(0 == $('#speedchart').size()){
        		clearTimeout(timerTask.VMJobDetails_speed);
        		return;
        	}
			pAjaxRequest(p, "/api/v1/vm/jobs/speed", "GET", function (d) {
            	var dataNow = d.data;
            	initTaskSpeed(dataNow);
                data.shift();
                data.push(dataNow.speed);
                option.series[0].data = data;

                nowTime.shift();
                nowTime.push(dataNow.nowTime);
                option.xAxis.data = nowTime;


            	myChart.setOption(option);
            }, false);
            timerTask.VMJobDetails_speed = setTimeout(update, updateInterval);
        }
        update();

        window.onresize = function(){
        	myChart.resize();
        }

    }

    //初始化日志表格
	const initLogGrid = function () {
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
	}

	//初始化备份任务虚拟机表格
	var initBackupVmTable = function () {
		if (!initVmTableFlag) {
			let options = {
				tableArea: '#vms',
				vin_url: '/api/v1/vm/jobs/vms',
				vin_method: 'GET',
				queryParamsType: 'limit',
				queryParams: function (params) {
					return {
						job_uuid: _jobUuid,
						offset: params.offset,
						limit: params.limit
					}
				},
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 20,
				pageList: [10, 20, 50, 100],
				paginationLoop: false,
				uniqueId: 'no',
				changeHeightBtn: true, //改变高度按钮
				batchOperation: true, // 批量操作
				sortable: false,
				sortName: 'id',
				sortOrder: 'desc',
				resizable: true,
				singleSelect: false,
				detailView: true,
				detailFormatter: function (index, row) {
					return (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_CLOUD_PLATFORM_INSTANCE_PATH : LANG.UI_VCENTER_VM_PATH) + ': ' + row.detail.sPath;
				},
				onRefresh: function (params) {
					$("#vm-table").bootstrapTable('hideLoading');
				},
				onCheck: function (row) {
					vmData.push(row.vm_uuid)
					var index = vmData.indexOf(row.vm_uuid); // 查找元素的索引
					if (index == -1) {
						vmData.push(row.vm_uuid)
					}
				},
				onCheckAll: function (row) {
					for (var i = 0; i < row.length; i++) {
						var index = vmData.indexOf(row[i].vm_uuid); // 查找元素的索引
						if (index == -1) {
							vmData.push(row[i].vm_uuid)
						}
					}
				},
				onUncheckAll: function (a, row) {
					for (var i = 0; i < row.length; i++) {
						var index = vmData.indexOf(row[i].vm_uuid); // 查找元素的索引
						if (index != -1) {
							vmData.splice(index, 1); // 从数组中删除一个元素
						}
					}
				},
				onUncheck: function (row) {
					var index = vmData.indexOf(row.vm_uuid); // 查找元素的索引
					if (index !== -1) {
						vmData.splice(index, 1); // 从数组中删除一个元素
					}
				},
				columns: [ //列定义
					{
						field: 'checkbox',
						checkbox: true,
						sortable: false, //默认可排序，禁用排序才写此项
						formatter: function (index, row) {
							for (var i = 0; i < vmData.length; i++) {
								if (row.vm_uuid == vmData[i]) {
									return true
								}
							}
						}
					},
					{
						field: 'name',
						title: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_JOB_INSTANCE_NAME : LANG.UI_JOB_VM_NAME,
						width: 25,
						widthUnit: '%',
					},
					{
						field: 'task_type',
						title: LANG.UI_FILE_WILDCARD_BAK_MODE,
						// width: '120px',
					},
					{
						field: 'size',
						title: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_PUBLIC_INSTANCE_TOTAL_SIZE : LANG.UI_PUBLIC_VM_TOTAL_SIZE,
						// width: '100px',
					},
					{
						field: 'valid_size',
						title: LANG.UI_PUBLIC_VM_VALID_SIZE,
						// width: '200px',
					},
					{
						field: 'transport_size',
						title: LANG.UI_PUBLIC_TRANSFER_SIZE,
						// width: '200px',
					},
					{
						field: 'write_size',
						title: LANG.UI_PUBLIC_REAL_SIZE,
						// width: '200px',
					},
					{
						field: 'speed',
						title: LANG.UI_TASK_AWS_INSTANCE_SPEED,
						// width: '200px',
					},
					{
						field: 'percent',
						title: LANG.UI_TASK_AWS_TRANS_PROGRESS,
						// width: '200px',
					},
					{
						field: 'status',
						title: LANG.UI_PUBLIC_STATUS,
						// width: '200px',
					},
					{
						field: 'description',
						title:  LANG.UI_PUBLIC_DESCRIPTION,
						// width: '200px',
					}
				]
			}
			$('#vm-table').bootstrapTable('destroy');
			$('#vm-table').baseTableConfig().init(options);
			initVmTableFlag = true;

			timerTask.VMJobDetails_vmGridScroll = setTimeout(() => {
				originalTotal = $('#vm-table').bootstrapTable('getOptions').totalRows;
			}, 500);
		} else {
			$('#vm-table').bootstrapTable('refresh', {query: {job_uuid: _jobUuid}});
		}
		timerTask.VMJobDetails_vmGrid = setTimeout(initBackupVmTable, 5000);
	}

	//初始化恢复任务虚拟机表格
	var initRecoveryVmTable = function () {
		if (!initVmTableFlag) {
			let options = {
				vin_url: '/api/v1/vm/jobs/vms',
				vin_method: 'GET',
				queryParamsType: 'limit',
				queryParams: function (params) {
					return {
						job_uuid: _jobUuid,
						offset: params.offset,
						limit: params.limit
					}
				},
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 20,
				pageList: [10, 20, 50, 100],
				paginationLoop: false,
				uniqueId: 'no',
				changeHeightBtn: true, //改变高度按钮
				batchOperation: true, // 批量操作
				sortable: false,
				sortName: 'id',
				sortOrder: 'desc',
				resizable: true,
				singleSelect: false,
				detailView: true,
				detailFormatter: function (index, row) {
					let str = '<table class="vm-detail-table">';
					str += '<tr><td style="width: 200px">' + LANG.UI_RECOVERY_TIMEPOINT + ': </td><td>' + row.detail.timepoint_des + '</td></tr>';
					str += '<tr><td style="width: 200px">' + (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_RECOVERY_OLD_INSTANCE_SRC : LANG.UI_RECOVERY_OLD_VM_SRC) + ': </td><td>' + row.detail.path + '</td></tr>';
					str += `<tr>
								<td style="width: 200px">${LANG.UI_RECOVERY_GOAL}: </td>
								<td>
									${CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_CLOUD_PLATFORM_VCENTER : LANG.UI_VCENTER_VCENTER}: ${row.detail.dVcenterIP} 
									${CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_CLOUD_PLATFORM_PROJECT : LANG.UI_VCENTER_HOST}: ${row.detail.dHostIP} 
									${CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_CLOUD_PLATFORM_INSTANCE : LANG.UI_VCENTER_VM}: ${row.detail.dName}
								</td>
							</tr>`;
					str += '</table>';
					return str;
				},
				onRefresh: function (params) {
					$("#vm-table").bootstrapTable('hideLoading');
				},
				columns: [ //列定义
					{
						field: 'name',
						title: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_JOB_INSTANCE_NAME : LANG.UI_JOB_VM_NAME,
						width: 25,
						widthUnit: '%',
					},
					{
						field: 'task_type',
						title: LANG.UI_TASK_AWS_RECOVERY_TYPE,
						// width: '120px',
					},
					{
						field: 'size',
						title: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_PUBLIC_INSTANCE_TOTAL_SIZE : LANG.UI_PUBLIC_VM_TOTAL_SIZE,
						// width: '100px',
					},
					{
						field: 'valid_size',
						title: LANG.UI_PUBLIC_VM_VALID_SIZE,
						// width: '200px',
					},
					{
						field: 'transport_size',
						title: LANG.UI_PUBLIC_TRANSFER_SIZE,
						// width: '200px',
					},
					{
						field: 'write_size',
						title:  LANG.UI_PUBLIC_REAL_SIZE,
						// width: '200px',
					},
					{
						field: 'speed',
						title: LANG.UI_TASK_AWS_INSTANCE_SPEED,
						// width: '200px',
					},
					{
						field: 'percent',
						title: LANG.UI_TASK_AWS_TRANS_PROGRESS,
						// width: '200px',
					},
					{
						field: 'status',
						title: LANG.UI_PUBLIC_STATUS,
						// width: '200px',
					},
					{
						field: 'description',
						title: LANG.UI_PUBLIC_DESCRIPTION,
						// width: '200px',
					}
				]
			}
			$('#vm-table').baseTableConfig().init(options);
			initVmTableFlag = true;
		} else {
			$('#vm-table').bootstrapTable('refresh', {query: {job_uuid: _jobUuid}});
		}
		timerTask.VMJobDetails_vmGrid = setTimeout(initRecoveryVmTable, 5000);
	}

    //初始化历史任务表格
	var initHistoryTable = function () {
		if (!initHistoryTableFlag) {
			let options = {
				tableArea: '#history',
				vin_url: '/api/v1/jobs/' + _jobUuid + '/history',
				vin_method: 'GET',
				queryParamsType: 'limit',
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 20,
				pageList: [10, 20, 50, 100],
				paginationLoop: false,
				uniqueId: 'history_uuid',
				changeHeightBtn: true, //改变高度按钮
				batchOperation: false, // 批量操作
				sortable: false,
				sortName: 'start_time',
				sortOrder: 'desc',
				resizable: true,
				singleSelect: false,
				detailView: true,
				detailFormatter: function (index, row) {
					// 开启资源限制显示对应内容
					if (row.detail.list.resource_limiting_node_config) {
						let thead =
							`<tr>
								<th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
								<th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
								<th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
							</tr>`;

						let resourceLimitingNodeConfig = row.detail.list.resource_limiting_node_config;
						let tbody =
							`<tr>
								<td>` + LANG.UI_PUBLIC_ON + `</td>
								<td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
								<td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
							</tr>`;
						return `<table style="width: 100%" class="history-detail-table">${thead}${tbody}</table>`;
					}
					//历史任务详情，区分备份和恢复
					let str = '<table class="history-detail-table">'
					let vms_details = row.detail.list.vms_details;
					if (1 == row.job_type_value) {
						str += '<tr>' +
							'<th width="20%">' + (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_JOB_INSTANCE_NAME : LANG.UI_JOB_VM_NAME) + '</th>' +
							'<th>' + LANG.UI_SEARCH_TASK_TYPE + '</th>' +
							'<th width="20%">' + (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_CLOUD_PLATFORM_INSTANCE_PATH : LANG.UI_VCENTER_VM_PATH) + '</th>' +
							'<th>' + (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_PUBLIC_INSTANCE_TOTAL_SIZE : LANG.UI_PUBLIC_VM_TOTAL_SIZE) + '</th>' +
							'<th>' + LANG.UI_PUBLIC_VM_VALID_SIZE + '</th>' +
							'<th>' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>' +
							'<th>' + LANG.UI_PUBLIC_REAL_SIZE + '</th>' +
							'<th>' + LANG.UI_VISUAL_RESULT + '</th>' +
							'<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>' +
							'<th>' + LANG.UI_PUBLIC_UPDATE_RECORDS + '</th>' +
							'</tr>';
						$.each(vms_details, function (i, v) {
							str += '<tr>' +
								'<td>' + v.vm_name + '</td>' +
								'<td>' + v.backup_mode + '</td>' +
								'<td>' + v.dir_path + '</td>' +
								'<td>' + v.vm_size + '</td>' +
								'<td>' + v.vm_valid_size + '</td>' +
								'<td>' + v.transport_size + '</td>' +
								'<td>' + v.real_size + '</td>';
							if (0 == v.task_status_value || 1 == v.task_status_value || 6 == v.task_status_value) {
								str += '<td><span class="label label-sm label-info status-icon">' + v.task_status + '</span></td>';
							} else if (4 == v.task_status_value) {
								str += '<td><span class="label label-sm label-danger status-icon">' + v.task_status + '</span></td>';
							} else {
								str += '<td><span class="label label-sm label-success status-icon">' + v.task_status + '</span></td>';
							}
							str += '<td>' + v.error_code + '</td>';

							//新增和移除的实例
							if (0 == i && row.detail.list.vms_changed_status) {
								var addvms = row.detail.list.vms_changed_status.add_vms;
								var removevms = row.detail.list.vms_changed_status.remove_vms;
								str += '<td rowspan="' + parseInt(vms_details.length + 1) + '">'
								if (addvms.length) {
									str += (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_VM_ADD_INSTANCE : LANG.UI_VM_ADD_VM) + ': <br>';
									$.each(addvms, function (i, v) {
										str += '&nbsp;&nbsp;' + v + '<br>';
									});
								}
								if (removevms.length) {
									str += (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_VM_REMOVE_INSTANCE : LANG.UI_VM_REMOVE_VM) + ': <br>';
									$.each(removevms, function (i, v) {
										str += '&nbsp;&nbsp;' + v + '<br>';
									});
								}
								str += '</td>';
							}

							str += '</tr>';
						});
					} else {
						str += '<tr>' +
							'<th>' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>' +
							'<th>' + LANG.UI_TASK_AWS_RECOVERY_NAME + '</th>' +
							'<th>' + LANG.UI_JOB_HIS_TOTAL_SIZE + '</th>' +
							'<th>' + LANG.UI_PUBLIC_VM_VALID_SIZE + '</th>' +
							'<th>' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>' +
							'<th>' + LANG.UI_PUBLIC_REAL_SIZE + '</th>' +
							'<th>' + LANG.UI_VISUAL_RESULT + '</th>' +
							'<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>' +
							'</tr>';
						$.each(vms_details, function (i, v) {
							str += '<tr>' +
								'<td>' + v.timepoint + '</td>' +
								'<td>' + v.new_name + '</td>' +
								'<td>' + v.vm_size + '</td>' +
								'<td>' + v.vm_valid_size + '</td>' +
								'<td>' + v.transport_size + '</td>' +
								'<td>' + v.real_size + '</td>';
							if (0 == v.task_status_value || 1 == v.task_status_value || 6 == v.task_status_value) {
								str += '<td><span class="label label-sm label-info status-icon">' + v.task_status + '</span></td>';
							} else if (4 == v.task_status_value) {
								str += '<td><span class="label label-sm label-danger status-icon">' + v.task_status + '</span></td>';
							} else {
								str += '<td><span class="label label-sm label-success status-icon">' + v.task_status + '</span></td>';
							}
							str += '<td>' + v.error_code + '</td>';
							str += '</tr>';
						});
					}
					return str;
				},
				onRefresh: function (params) {
					$("#historytable").bootstrapTable('hideLoading');
				},
				columns: [ //列定义
					{
						field: 'num',
						title: LANG.UI_PUBLIC_TABLE_ID,
					},
					{
						field: 'job_type',
						title:  LANG.UI_SEARCH_TASK_TYPE,
					},
					{
						field: 'job_status',
						title: LANG.UI_VISUAL_RESULT,
						formatter: function (value, data) {
							if (0 == data.job_status_value) {
								return '<span class="label label-sm label-success status-icon">' + value + '</span>';
							} else if (45 == data.job_status_value) {
								return '<span class="label label-sm label-info status-icon">' + value + '</span>';
							} else {
								return '<span class="label label-sm label-danger status-icon">' + value + '</span>';
							}
						}
					},
					{
						field: 'all_size',
						title:  CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_PUBLIC_INSTANCE_TOTAL_SIZE : LANG.UI_PUBLIC_VM_TOTAL_SIZE,
						// width: '100px',
					},
					{
						field: 'validate_size',
						title: LANG.UI_PUBLIC_VM_VALID_SIZE,
						// width: '200px',
					},
					{
						field: 'speed_size',
						title: LANG.UI_PUBLIC_TRANSFER_SIZE,
						// width: '200px',
					},
					{
						field: 'write_size',
						title:  LANG.UI_PUBLIC_REAL_SIZE,
						// width: '200px',
					},
					{
						field: 'start_time',
						title:  LANG.UI_PUBLIC_START_TIME,
						// width: '200px',
					},
					{
						field: 'finish_time',
						title: LANG.UI_PUBLIC_END_TIME,
						// width: '200px',
					},
				]
			}
			$('#historytable').baseTableConfig().init(options);
			initHistoryTableFlag = true;
		} else {
			$('#historytable').bootstrapTable('refresh');
		}
	}

	//版本差异处理,主要是处理标准版本功能限制
	// 中文标准版 1
	// 中文企业版 2
	// 英文免费版 4
	// 英文基础版 9
	// 英文标准版 6
	// 英文企业版 7
	var initSoftwareVersionDiff = function(){
		pAjaxRequest({}, "/api/v1/users/auth_func", "GET", function (d) {
			var data = d.data;
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				authFun = data;
			}
			if(!data.dedupication){
				//设置重删隐藏
				$('.deduplicationdiv').hide();
			}
			if(!data.vcbt){
				//深度有效数据提取结果显示隐藏
				$('.parsefsDiv').hide();
			}
			//中文标准版不支持重删
			if(1 == CONF.SOFTWARE){
				//设置重删为关闭并不可用再加隐藏
				$('.deduplicationdiv').hide();
			}
			if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
				//深度有效数据提取结果显示隐藏
				$('.parsefsDiv').hide();
			} else {
				if (data.vcbt) {
					$('.parsefsDiv').show();
				}
			}
		}, false);
	}

	//根据任务状态设置按钮权限
    var setBtnStatus = function(data){
		if (1 == data.taskTypeFlag) {
			setControlBtn('startFull', true);
			setControlBtn('startIncr', true);
			setControlBtn('startDiff', true);
			setControlBtn('taskStartFull', true);
			setControlBtn('taskStartIncr', true);
			setControlBtn('taskStartDiff', true);
			var timeStrategy = data.timeStrategy;
			switch (_taskStatus) {
				case 2:
				case 5:
				case 7:
				case 10:
				case 19:
					//运行和准备中停止中,禁用运行
					setControlBtn('taskStartFull', false);
					setControlBtn('taskStartIncr', false);
					setControlBtn('taskStartDiff', false);
					setControlBtn('startFull', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
					setControlBtn('deleteVm', true);

					setControlBtn('stoptask', true);
					setControlBtn('deletevm', false);
					if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
						//停止中状态，变为强制停止
						if (CONF.TASK_STATUS.STOPPING == _taskStatus) {
							$('#stoptask').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
						}
					}
					break;
				case 4:
					//停止,禁用停止
					$('#stoptask').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</a>');
					setControlBtn('stoptask', false);
					setControlBtn('deleteVm', true);
					break;
				case 20:
					//删除中，禁用所有操作
					setControlBtn('taskStartFull', false);
					setControlBtn('taskStartIncr', false);
					setControlBtn('taskStartDiff', false);
					setControlBtn('stoptask', false);
					setControlBtn('startFull', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
					setControlBtn('deleteVm', false);
					break;
				default:
					//其他状态,开启控制
					setControlBtn('stoptask', true);
					setControlBtn('deleteVm', true);
					break;
			}
			for (var i = 0; i < timeStrategy.length; i++) {
				if (timeStrategy[i].mode == 2 || timeStrategy[i].mode == 9) {
					setControlBtn('startDiff', false);
					setControlBtn('taskStartDiff', false);
				} else if (timeStrategy[i].mode == 3) {
					setControlBtn('startIncr', false);
					setControlBtn('taskStartIncr', false);
				}
				//如果为一次性备份 ,禁用增量和差异
				if (timeStrategy[i].type == 4) {
					setControlBtn('taskStartIncr', false);
					setControlBtn('taskStartDiff', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
				}

			}
		} else {
			// 等待、暂停、停止、停止中、错误、任务暂停中、已完成、任务成功、任务挂起 可以启动
			var startArr = [
				CONF.TASK_STATUS.WAITTING,
				CONF.TASK_STATUS.PAUSED,
				CONF.TASK_STATUS.STOPPED,
				CONF.TASK_STATUS.STOPPING,
				CONF.TASK_STATUS.ERROR,
				CONF.TASK_STATUS.PAUSING,
				CONF.TASK_STATUS.FINISHED,
				CONF.TASK_STATUS.SUCCESSED,
				CONF.TASK_STATUS.CREATING,
			];
			// 等待、运行、网络故障、任务已经完成但异常、错误、准备中、启动中、已完成、任务成功、任务创建中、任务挂起 可以停止
			var stopArr = [
				CONF.TASK_STATUS.WAITTING,
				CONF.TASK_STATUS.RUNNING,
				CONF.TASK_STATUS.NETWORK_FAULT,
				CONF.TASK_STATUS.ABNORMAL,
				CONF.TASK_STATUS.ERROR,
				CONF.TASK_STATUS.PREPARING,
				CONF.TASK_STATUS.STARTING,
				CONF.TASK_STATUS.FINISHED,
				CONF.TASK_STATUS.SUCCESSED,
				CONF.TASK_STATUS.CREATING,
				CONF.TASK_STATUS.PENDING,
			];

			// 默认都不可操作
			setControlBtn('startJob', false);
			setControlBtn('stopJob', false);
			setControlBtn('delJob', false);
			if (startArr.includes(_taskStatus)) {
				// 可以启动
				setControlBtn('startJob', true);
			}
			if (stopArr.includes(_taskStatus)) {
				// 可以停止
				setControlBtn('stopJob', true);
			}
			//停止中状态，变为强制停止
			if (CONF.TASK_STATUS.STOPPING == _taskStatus) {
				$('#stopJob').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
				setControlBtn('stopJob', true);
			}

			// 任务是停止的，能删除
			if (_taskStatus == CONF.TASK_STATUS.STOPPED) {
				setControlBtn('delJob', true);
			}

			//任务详情按钮
			$('#startJob').unbind().on('click', function(){
				if($(this).find('a').hasClass('disablebtn')){
					return true;
				}
				operateJob(LANG.UI_JOB_START, 1);
			});
			$('#stopJob').unbind().on('click', function(){
				if($(this).find('a').hasClass('disablebtn')){
					return true;
				}
				bootbox.prompt({
					title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
					inputType: 'password',
					callback: debounce(function (r) {
						if (r == null) return;
						// 执行操作验证密码
						Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
						var encrypt = new JSEncrypt();
						encrypt.setPublicKey(CONF.PUBLIC_KEY);
						var password = encrypt.encrypt(r);
						var that = this;
						pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
							Metronic.unblockUI('#jobDetail');
							$(that).modal('hide');
							if (result.code == 0) {
								// 执行停止操作
								operateJob(LANG.UI_PLATFORM_RECOVERY_STOP_JOB, 1, '/api/v1/jobs/stop/'+_jobUuid);
							} else {
								UIToastr.showWarning(result.title, result.message);
							}
						});
					}, 300, false)
				})
			});

			$('#delJob').unbind().on('click', function(){
				if($(this).find('a').hasClass('disablebtn')){
					return true;
				}
				bootbox.confirm({
					title: LANG.UI_JOB_DELETE_JOB,
					message: LANG.UI_JOB_DELETE_JOB_TIPS,
					callback: debounce(function (r) {
						if (!r) return;
						// 执行操作
						operateJob(LANG.UI_JOB_DELETE_JOB, 2, '/api/v1/jobs/'+_jobUuid, 'DELETE');
					}, 300)
				})

			});
		}
    }

	// 操作任务
	var operateJob = function (operate, type, url = '', method = 'POST'){
		if (url == '') {
			url = '/api/v1/jobs/start/' + _jobUuid;
		}
		Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
		pAjaxRequest({start_type: type}, url, method, function (result) {
			Metronic.unblockUI('#jobDetail');
			if (result.code == 0 || result.code == 200) {
				if (type == 2) {
					// 删除成功，要跳转到任务列表去
					cleanTime();
					setTimeout(function (){
						LOCATION('./content/platform/jobs/jobs.php', 'task');
					}, 2000)
				}
				UIToastr.showSuccess(operate, result.message)
			} else {
				UIToastr.showError(operate, result.message);
			}
		});
	}

	// 清理定时
	var cleanTime = function (){
		clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
		clearTimeout(timerTask.VMJobDetails_speed);
		clearTimeout(timerTask.VMJobDetails_logGrid);
		clearTimeout(timerTask.VMJobDetails_historyGrid);
	}

	//设置按钮是否可用
	var setControlBtn = function(id, available){
		if (available) {
			$("#" + id).find('a').removeClass('disablebtn');
			$("#" + id).find('.btn').prop('disabled', false);
		} else {
			$("#" + id).find('a').addClass('disablebtn');
			$("#" + id).find('.btn').prop('disabled', true);
		}
	}

    var initListener = function(){
		$('#stoptask').unbind().on('click', () => {
			checkOperateAuth(checkAuth(1), stopJob);
		});   //终止任务
		$('#taskStartFull').unbind().on('click', () => {
			checkOperateAuth(checkAuth(1), startJob);
		}); //启动完备任务
		$('#taskStartIncr').unbind().on('click', () => {
			checkOperateAuth(checkAuth(1), startIncr);
		});//增量
		$('#taskStartDiff').unbind().on('click', () => {
			checkOperateAuth(checkAuth(1), startDiff);
		});//差异

		$('#startFull').unbind().on('click', function(){
			if($(this).find('.btn').prop('disabled')){
				return true;
			}
			let authParams = checkAuth(2);
			if (authParams) {
				checkOperateAuth(authParams, () => {
					startVmJob(1)
				});
			}
		});
		$('#startIncr').unbind().on('click', function(){
			if($(this).find('.btn').prop('disabled')){
				return true;
			}
			let authParams = checkAuth(2);
			if (authParams) {
				checkOperateAuth(authParams, () => {
					startVmJob(2)
				});
			}
		});
		$('#startDiff').unbind().on('click', function(){
			if($(this).find('.btn').prop('disabled')){
				return true;
			}
			let authParams = checkAuth(2);
			if (authParams) {
				checkOperateAuth(authParams, () => {
					startVmJob(3)
				});
			}
		});
		$('#deleteVm').unbind().on('click', function(){
			if($(this).find('.btn').prop('disabled')){
				return true;
			}
			let authParams = checkAuth(3);
			if (authParams) {
				checkOperateAuth(authParams, deleteVmFromJob);
			}
		});

		var startVmJob = function(mode){
			var selectRows = $('#vm-table').bootstrapTable('getSelections');
			var data = {};
			data.vm_uuids = [];
			for (var i = 0; i < selectRows.length; i++) {
				data.vm_uuids.push(selectRows[i].vm_uuid);
			}
			data.job_uuid = $("#task_uuid").val();
			data.platform_uuids = [];
			for (var i = 0; i < selectRows.length; i++) {
				data.platform_uuids.push(selectRows[i].platform_uuid);
			}
			data.hypervisor_type = selectRows[0].hypervisor_type;
			data.mode = mode;
			pAjaxRequest(data, "/api/v1/vm/jobs/start_select", "POST", function (d) {
				if (operateResponseList(d)) {
					$('#vm-table').bootstrapTable('refresh');
				}
			}, false);
		}

		var deleteVmFromJob = function () {
			var selectRows = $('#vm-table').bootstrapTable('getSelections');
			if (selectRows.length === originalTotal) {
				if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType) {
					return UIToastr.showWarning(LANG.UI_JOB_REMOVE_SELECT_INSTANCE_FROM_JOB, LANG.UI_JOB_REMOVE_ALL_INSTANCE_TIPS);
				} else {
					return UIToastr.showWarning(LANG.UI_JOB_REMOVE_SELECT_VM_FROM_JOB, LANG.UI_JOB_REMOVE_ALL_VM_TIPS);
				}
			}
			bootbox.confirm({
				title: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_VM_REMOVE_INSTANCE : LANG.UI_VM_REMOVE_VM,
				message: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_BACKUP_AWS_REMOVE_INSTANCE_TIPS : LANG.UI_VM_REMOVE_VM_TIPS,
				callback: debounce(function (r) {
					if (!r) return;
					// 执行操作
					var data = {};
					data.vm_uuids = [];
					for (var i = 0; i < selectRows.length; i++) {
						if ((CONF.TASK_STATUS.RUNNING == _taskStatus || CONF.TASK_STATUS.ABNORMAL == _taskStatus) && 4 != selectRows[i].task_status) {
							// 任务为运行中或异常时，存在非错误状态的虚拟机则无法删除
							if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType) {
								UIToastr.showWarning(LANG.UI_JOB_REMOVE_SELECT_INSTANCE_FROM_JOB, LANG.UI_JOB_REMOVE_SELECT_INSTANCE_FROM_JOB_TIPS);
							} else {
								UIToastr.showWarning(LANG.UI_JOB_REMOVE_SELECT_VM_FROM_JOB, LANG.UI_JOB_REMOVE_SELECT_VM_FROM_JOB_TIPS);
							}
							return false;
						}
						data.vm_uuids.push(selectRows[i].vm_uuid);
					}
					data.job_uuid = $("#task_uuid").val();
					data.hypervisor_type = selectRows[0].hypervisor_type;
					pAjaxRequest(data, "/api/v1/vm/jobs/delete_select", "DELETE", function (d) {
						if (operateResponseList(d)) {
							vmData = []; // 清空勾选
							$('#vm-table').bootstrapTable('refresh');
							originalTotal -= selectRows.length;
						}
					}, false);
				}, 300)
			});
		}

		$('#searchbtn').on('click', function () {
			searchVmTable();
		});
		$('#searchInput').keypress(function (e) {
			if (e.which == 13) {
				searchVmTable();
			}
		});
		$('#searchInput').on('blur', function () {
			$(this).prop('placeholder', CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? LANG.UI_TASK_INSTANCE_SEARCH_TIPS : LANG.UI_TASK_VM_SEARCH_TIPS);
		});
		$('#clearSearchBtn').on('click', function () {
			$('#searchInput').val('');
			searchVmTable();
		});

		// 选中对应tab时初始化
		$(`#logli`).on('click', function () {
			if ($(`#logli`).hasClass('active')) {
				return;
			}
			clearTimeout(timerTask.VMJobDetails_logGrid);
			clearTimeout(timerTask.VMJobDetails_vmGrid);
			initLogGrid();
		});
		$(`#vmli`).on('click', function () {
			if ($(`#vmli`).hasClass('active')) {
				return;
			}
			clearTimeout(timerTask.VMJobDetails_logGrid);
			clearTimeout(timerTask.VMJobDetails_vmGrid);
			if (1 == taskTypeFlag) {
				initBackupVmTable();
			} else {
				initRecoveryVmTable();
			}
		});
		$(`#historyli`).on('click', function () {
			if ($(`#historyli`).hasClass('active')) {
				return;
			}
			clearTimeout(timerTask.VMJobDetails_logGrid);
			clearTimeout(timerTask.VMJobDetails_vmGrid);
			_jobUuid = $("#task_uuid").val();
			initHistoryTable();
		});
    }

	const searchVmTable = () => {
		$("#vm-table").bootstrapTable('refreshOptions', {
			queryParams: function (queryParams) {
				return $.extend(queryParams, {job_uuid: _jobUuid, search_name: $.trim($('#searchInput').val())});
			}
		});
	}

    //停止
	var stopJob = function(){
		if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
		let job_uuid = $('#task_uuid').val()
		Metronic.blockUI({target: '#jobDetail',animate: true});
		pAjaxRequest({}, "/api/v1/jobs/stop/" + job_uuid, "POST", function (d) {
			Metronic.unblockUI('#jobDetail');
			if (operateResponseList(d, LANG.UI_COPY_SEND_STOP_JOB_MESSAGE)) {

			}
		}, false);
	}

    //启动完全
	var startJob = function(){
		if($(this).find('a').hasClass('disablebtn')){
			return true;
		}
		startJobUnify('startJob', 1);
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

	//启动任务
	var startJobUnify = function(funName, type){
		let data = {};
		let job_uuid = $('#task_uuid').val();
		data.start_type = type;
		Metronic.blockUI({target: '#jobDetail',animate: true});
		pAjaxRequest(data, "/api/v1/jobs/start/" + job_uuid, "POST", function (d) {
			Metronic.unblockUI('#jobDetail');
			if (operateResponseList(d, LANG.UI_COPY_SEND_START_JOB_MESSAGE)) {

			}
		}, false);
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
	 * 构造权限校验所需参数，type:1-任务操作，2-启动虚拟机备份，3-移除虚拟机
	 */
	const checkAuth = (type) => {
		if (type === 1) {
			// 非分配资源的权限
			return {type: 1, user_uuid: _userUuid, auth: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? 'prcloud_protect' : 'vmprotect'};
		} else if (type === 2) {
			let select = $('#vm-table').bootstrapTable('getSelections');
			if (!select.length) {
				if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType) {
					return UIToastr.showInfo(LANG.UI_JOB_START_INSTANCE_BACKUP_JOB, LANG.UI_JOB_START_INSTANCE_BACKUP_JOB_TIPS);
				} else {
					return UIToastr.showInfo(LANG.UI_JOB_START_VM_BACKUP_JOB, LANG.UI_JOB_START_VM_BACKUP_JOB_TIPS);
				}
			}
			let source_uuids = select.map(item => item.vm_uuid).join(',');
			// 分配资源的权限
			return {type: 2, source_uuid: source_uuids, source_type: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? 61 : 3};
		} else if (type === 3) {
			let select = $('#vm-table').bootstrapTable('getSelections');
			if (!select.length) {
				if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType) {
					UIToastr.showInfo(LANG.UI_JOB_REMOVE_SELECT_INSTANCE_FROM_JOB, LANG.UI_JOB_REMOVE_NO_SELECT_INSTANCE_TIPS);
				} else {
					UIToastr.showInfo(LANG.UI_JOB_REMOVE_SELECT_VM_FROM_JOB, LANG.UI_JOB_REMOVE_NO_SELECT_VM_TIPS);
				}
				return false;
			}
			let source_uuids = select.map(item => item.vm_uuid).join(',');
			// 分配资源的权限
			return {type: 2, source_uuid: source_uuids, source_type: CONF.VM_SUB_MODULE.PRIVATE_CLOUD === subModuleType ? 61 : 3};
		} else {
			return false;
		}
	}

    return {
        //main function to initiate the module
        init: function () {
        	initSoftwareVersionDiff();
        	initBasicInfo();
        	initSpeed();
            initLogGrid();
            initListener();
			watchEchartSizeChange();
        }

    };

}();

jQuery(document).ready(function() {
	VMJobDetails.init();
});
