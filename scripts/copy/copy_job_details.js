var CopyJobDetails = function () {
	let initChartFlag = false; //任务曲线图初始化标志
	let initGridFlag = false;
	let _taskStatus; //监控任务状态
	let jobParams = {}; //操作需要参数
	let myChart;
	let changeHeightFlag = false;
	let taskType = 17;
	let expandIndex = null;
	let paginationOpenFlag = false; // 记录分页展开状态
	let user_uuid = '';
	//用于记录展开状态
	let lastIndex = [-1, -1];
	let copyIndex = [-1, -1]
	// 默认刷新间隔2秒
	const INTERVAL = 3000;
	// 任务刷新间隔5秒
	const TASK_INTERVAL = 5000;
	// 压缩等级值与对应描述映射
	const COMPRESS_METHOD = {
		1: LANG.UI_JOB_COMPRESS_PRIORITY_FASTER,
		2: LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL,
		3: LANG.UI_JOB_COMPRESS_PRIORITY_BETTER,
		4: LANG.UI_JOB_COMPRESS_PRIORITY_BEST
	}
	// 副本类型
	const COPY_MODE = {
		MIRROR: 1,
		COMB: 2
	}
	// 永久保留值
	const UNLIMITED_NUM = 65535;
	// 任务状态值及对应类映射
	const STATUS_CLASS = {
		1: 'label-info',
		2: 'label-success',
		3: 'label-success',
		4: 'label-default',
		5: 'label-warning',
		6: 'label-danger',
		7: 'label-warning',
		8: 'label-danger',
		21: 'label-warning'
	}
	// 调整表格高度大小
	const ROW_HIGH_PX = '15.25px';
	const ROW_LOW_PX = '4.25px';
	// 历史任务表格展开详情 所有信息字段
	const DETAIL_ROW_TITLE = {
		1: LANG.UI_COPY_DETAILS_TIMEPOINT_NUM,
		2: LANG.UI_PUBLIC_START_TIME,
		3: LANG.UI_PUBLIC_END_TIME,
		4: LANG.UI_DB_AVE_SPEED,
		5: LANG.UI_JOB_HIS_TOTAL_SIZE,
		6: LANG.UI_PUBLIC_TRANSFER_SIZE,
		7: LANG.UI_PUBLIC_REAL_SIZE,
		8: LANG.UI_VISUAL_RESULT,
		9: LANG.UI_PUBLIC_DESCRIPTION,
	}
	// 合并副本的类型
	const SHOW_COPY_MODE_COMB = [CONF.MODULE_TYPE.OS, CONF.MODULE_TYPE.VM, CONF.MODULE_TYPE.PUBLIC_CLOUD, CONF.MODULE_TYPE.PRIVATE_CLOUD];
	let itemParams = {};

	//初始化基本信息
	let initBasicInfo = function () {
		let updateInterval = INTERVAL;
		let init = function () {
			if (0 == $('#task_uuid').size()) {
				clearTimeout(timerTask.copyDetails_taskRunningInfo);
				return;
			}
			let task_uuid = $("#task_uuid").val();
			let jobInfoBack = function (res) {
				setBasicInfo(res.data, timerTask.copyDetails_taskRunningInfo)
			};

			pAjaxRequest({ task_uuid: task_uuid }, '/api/v1/copy/job', "get", jobInfoBack, true);
			timerTask.copyDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}
	const initSummaryInfo = (data) => {
		//基本信息
		// 任务名
		$('#taskName').html(data.job_name);
		// 模块类型
		$('#moduleType').html(data.moduleType);
		// 任务类型
		$('#taskType').html(data.job_type_des);
		if (data.taskType == 17) {
			if (data.copy_mode == COPY_MODE.MIRROR) {
				$('.hashIncDiv').hide();
			} else {
				$('.hashIncDiv').show();
			}
		} else {
			$('.hashIncDiv').hide();
		}
		// 状态
		$('#status').html('<span class="label ' + getStatusLevelClass(data.statusValue) + '" >' + data.status + '</span>');
		// 任务总量
		$('#totalSize').html(data.totalSize);
		// 已处理容量
		$('#currentSize').html(data.currentSize);
		// 任务不在运行中显示---
		if (data.statusValue != 2) {
			$('#totalSize').html('---');
			$('#currentSize').html('---');
		}
		// 开始时间
		$('#startTime').html(data.startTime);
		// 持续时间
		$('#intervalTime').html(data.intervalTime);
		// 自动添加副本对象
		$('#autoJoinCopy').html(getFlagLevelInfo(data.auto_add_flag));
	};
	const initSettingInfo = (data) => {
		// 创建修改时间
		$('#createTime').html(data.createTime);
		// 下次运行时间
		$('#nextTime').html(data.nextTime);
		// 副本类型
		$('#copyType').html(data.copy_mode_des);
		// 时间策略
		// 在编排中的任务，显示按编排策略执行（原时间策略不生效）
		let timeDes = '---'
		if (data.timeStrategy) {
			let timeStrategy = getTimeStrategy(data.timeStrategy);
			timeDes = timeStrategy.copy;
		}
		if (data.task_orchestration_plan_flag) {
			timeDes = LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY;
		}
		$('#timeDes').html(timeDes);
	};
	const initMoreSetting = (data) => {
		//限速策略
		let speedDes = data.speed_limit.des;
		if (data.speed_limit && data.speed_limit.task_priority) {
			speedDes = data.speed_limit.text;
		}
		$('#speedLimit').html(speedDes);
		//存储策略
		if (data.storageInfo) {
			let node = data.storageInfo.node;
			let storage = data.storageInfo.storage;
			let storageInfo = '--';
			if (storage.length != 0) {
				storageInfo = storage.name + "(" + storage.type + ")<br>" + LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
					LANG.UI_JOB_FREE_SIZE + ":" + storage.free_size + "<br>";
			}
			$('#nodeInfo').html(node.name);
			$('#storageInfo').html(storageInfo);

			// 存储资源池
			if (data.storageInfo.node_pool_nickname) {
				$('#nodePool').html(data.storageInfo.node_pool_nickname);
			} else {
				$('#nodePool').html(`--`);
			}
			// 节点资源池
			if (data.storageInfo.storage_pool_nickname) {
				$('#storagePool').html(data.storageInfo.storage_pool_nickname);
			} else {
				$('#storagePool').html(`--`);
			}
		}
		// 保留策略
		// 副本磁带隐藏保留策略
		if (data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
			//磁带获取策略替换保留策略
			$('#reservedStrategy').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
		} else {
			let reserveDes = '--';
			if ([17, 19].includes(data.taskType)) {
				reserveDes = getReservedStrategy(data.reservedStrategy)
			}
			if (19 == data.taskType || 20 == data.taskType) {
				reserveDes = reserveDes.replaceAll(LANG.UI_VISUAL_COPY, LANG.UI_VISUAL_ARCHIVE_CHART);
			}
			$('#reservedStrategy').html(reserveDes);
		}
		if (data.taskType == 17) {
			let gfsHtml = LANG.UI_PUBLIC_OFF;
			if (data.copy_gfs_strategy.copy_week_flag || data.copy_gfs_strategy.copy_month_flag || data.copy_gfs_strategy.copy_year_flag) {
				gfsHtml = '';
				if (data.copy_gfs_strategy.copy_week_flag) {
					gfsHtml += LANG.UI_COPY_STRATEGY_GFS_WEEK + '<br>';
				}
				if (data.copy_gfs_strategy.copy_month_flag) {
					gfsHtml += LANG.UI_COPY_STRATEGY_GFS_MONTH + '<br>';
				}
				if (data.copy_gfs_strategy.copy_year_flag) {
					gfsHtml += LANG.UI_COPY_STRATEGY_GFS_YEAR + '<br> ';
				}
			}
			if (data.copy_last_chain_flag) {
				gfsHtml = LANG.UI_COPY_STRATEGY_LABEL_COPY_NEWEST_CHIAN;
			}
			$('#customSourceDes').html(gfsHtml);
			if (data.task_source_flag && data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
				$('.custom-source-div').show();
			} else {
				$('.custom-source-div').hide();
			}
		} else {
			$('.custom-source-div').hide();
		}
		// 传输策略
		// 传输加密
		if (data.transportStrategy.encrypt_flag_value) {
			let encryptMethod = data.transportStrategy.encrypt_method;
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if (encryptMethod == 2) {
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$("#transportEncrypt").html(method);
		} else {
			$("#transportEncrypt").html(getFlagLevelInfo(data.transportStrategy.encrypt_flag_value));
		}
		// 传输数据压缩
		if (data.taskType == 17 && data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.REMOTE) {
			if (data.transportStrategy.compress_flag_value) {
				let method = '';
				if (COMPRESS_METHOD.hasOwnProperty(data.transportStrategy.compress_method)) {
					method = COMPRESS_METHOD[data.transportStrategy.compress_method];
				}
				$("#transportCompress").html(method);
			} else {
				$("#transportCompress").html(getFlagLevelInfo(data.transportStrategy.compress_flag_value));
			}
			$('.strategy-group__form-compress').show();
		} else {
			$('.strategy-group__form-compress').hide();
		}
		let reconnect_times = data.transportStrategy.reconnect_times + LANG.UI_VOL_CDP_BACKUP_TIMES;
		let reconnect_interval = data.transportStrategy.reconnect_interval + LANG.UI_PUBLIC_SECOND;
		// 重连次数
		if (parseInt(data.transportStrategy.reconnect_times) == 0) {
			$("#reconnectTimes").html(LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE);
		} else {
			$('#reconnectTimes').html(reconnect_times);
		}
		$("#reconnectInterval").html(reconnect_interval);
		// 传输网络
		if (data.transportStrategy.network_pool_uuid) {
			$("#networkPool").html(data.transportStrategy.network_pool_nickname);
		} else {
			$('#networkPool').html(`--`);
		}
		if (data.transport_ip) {
			$('#transferNetwork').html(data.transport_ip);
		} else {
			$('#transferNetwork').html(`--`);
		}

		// 重试策略
		$('#network_retry_times').html(data.retry_strategy.network_retry_times + LANG.UI_MICROSOFT365_TIME);
		$('#network_retry_interval').html(data.retry_strategy.network_retry_interval + LANG.UI_PUBLIC_SECOND);
		$('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
		$('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
		if (data.retry_strategy.op_retry_flag) {
			$('#op_retry_times').html(data.retry_strategy.op_retry_times + LANG.UI_MICROSOFT365_TIME);
			$('#op_retry_interval').html(data.retry_strategy.op_retry_interval + LANG.UI_PUBLIC_SECOND);
		} else {
			$('.opRetryTimesDiv').hide();
			$('.opRetryIntervalDiv').hide();
		}
		if (data.retry_strategy.task_retry_flag) {
			$('#task_retry_object').html(data.retry_strategy.task_retry_object == 1 ? LANG.UI_RETRY_FAILED_OBJS_IN_TASK : LANG.UI_RETRY_ALL_OBJS_IN_TASK);
			$('#task_retry_times').html(data.retry_strategy.task_retry_times + LANG.UI_MICROSOFT365_TIME);
			$('#task_retry_interval').html(data.retry_strategy.task_retry_interval / 60 + LANG.UI_MICROSOFT365_MINUTE);
		} else {
			$('.taskRetryObjectDiv').hide();
			$('.taskRetryTimesDiv').hide();
			$('.taskRetryIntervalDiv').hide();
		}
		// 传输线程
		$('#threadCount').html(data.thread_num);

		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
		// 副本到云存储没有合并模式
		if (data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.CLOUD || !SHOW_COPY_MODE_COMB.includes(data.moduleTypeValue)) {
			$('.merge-mode-div').hide();
		} else {
			$('#redundantDataProportion').html(data.storageInfo.high.redundant_data_proportion);
			$('#data_container_size').html(data.storageInfo.high.data_container_size);
		}
		// 安全策略
		$('#backup_worm_flag').html(getFlagLevelInfo(data.safe_strategy.worm_flag));
		if (data.safe_strategy.worm_flag) {//WORM防护
			$('.backup_worm_date-form-item').show();
			$('#backup_worm_date').html(data.safe_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY);
		} else {
			$('.backup_worm_date-form-item').hide();
		}
		// 磁带存储,屏蔽安全策略
		if (data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE || !CONF.FUNCTIONS.includes('worm')) {
			$('.safe-strategy-group').hide();
		}
		if(!data.task_source_flag){
			$('.auto-join-div').hide();
		}
	};

	//设置基本信息
	let setBasicInfo = function (data, timeoutID) {
		if (!data.flag) {
			clearTimeout(timeoutID);
			$('#total-progress').css({ width: '100%' });
			$('#progressright').html('100%');
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function () {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}, TASK_INTERVAL);
			return false;
		}
		// 显示概要信息
		initSummaryInfo(data);
		// 初始化配置信息
		initSettingInfo(data);
		// 初始化更多配置
		initMoreSetting(data);
		initCopyGrid();

		if (data.status) {
			$('#status').html('<span class="label ' + getStatusLevelClass(data.statusValue) + '" >' + data.status + '</span>');
		}

		//保存获取到的任务参数
		_taskStatus = data.statusValue;
		jobParams.status = data.statusValue;
		jobParams.taskType = data.taskType;
		taskType = data.taskType;
		jobParams.subModule = data.subModule;
		user_uuid = data.user_uuid;

		addOperateButton(data);

		// 副本类型
		if (data.copy_mode == COPY_MODE.MIRROR) { //镜像副本
			$('.chain-length-div').hide();
		} else { //合并副本
			$('#copyType').html(LANG.UI_COPY_MODE_COMBINATION);
			$('.chain-length-div').show();
			// 数据链长度
			if (data.chain_length == UNLIMITED_NUM) { //无限制
				$('#chainLength').html(LANG.UI_COPY_CHAIN_LENGTH_UNLIMITED);
			} else {
				$('#chainLength').html(data.chain_length);
			}
			// 源端增量
			let hashFlag = LANG.UI_COPY_TARGET_INC_MODE1;
			if (data.inc_mode == 2) {
				hashFlag = LANG.UI_COPY_TARGET_INC_MODE2;
			}
			$("#hashIncFlag").html(hashFlag);
		}
		//副本
		if (18 == data.taskType || 20 == data.taskType) {
			$('#historyLi').hide();
			$('#copyHistory').hide();
			$('.speedDiv').show();
			$('.copy-mode-item').hide();
			$('.copy-mode-title').hide();
			$('.chain-length-div').hide();
			$('.next-time-div').hide();
			$('.create-time-div').hide();
			$('.time-strategy-div').hide();
			$('.merge-mode-div').hide();
		}
		if (data.taskType == 19) {
			$('.copy-mode-item').hide();
			$('.copy-mode-title').hide();
			$('.chain-length-div').hide();
			$('.time-strategy-div .strategy-group__form__item__label').html(LANG.UI_ARCHIVE_TIME_STRATEGY);
			$('.merge-mode-div').show();
			$('.redundantDataProportionDiv').hide();
		}
		// 镜像副本要屏蔽分片，副本回传屏蔽分片+冗余比列 #28357
		if (data.taskType == 17) {
			if (SHOW_COPY_MODE_COMB.includes(data.moduleTypeValue)) {
				if (data.moduleTypeValue == CONF.MODULE_TYPE.OS && data.subModuleTypeValue == 0) {
					$('.merge-mode-div').hide()
				} else {
					$('.merge-mode-div').show();
				}
			} else {
				$('.merge-mode-div').hide();
			}
			if (data.copy_mode == COPY_MODE.COMB) {
				$('.dataContainerSizeDiv').show();

			} else {
				$('.dataContainerSizeDiv').hide();
			}
		}
		if (data.storageInfo.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE || data.source_storage_info.storage.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
			$('.threadCountDiv').hide();
		} else {
			if (20 == data.taskType || 18 == data.taskType) {
			} else {
				$('.threadCountDiv').show();
			}
		}
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.threadCountDiv').hide();
		}
		$('#total-progress').css({ width: data.totalprogress });
		$('#progressright').html(data.progress);
	}

	let addOperateButton = function (data) {
		let opCode = data.op_list;
		let button = '';

		$.each(opCode, function (i, d) {
			switch (d) {
				case 1:
					if (data.taskType == 17) {
						if (data.copy_mode == COPY_MODE.MIRROR) {
							button += '<li id="startCopyMirror"><a href="javascript:;" style="width:100%"><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START_MIRROR_COPY + '</a></li>';
						}
					} else {
						button += '<li id="startCopyJob"><a href="javascript:;" style="width:100%"><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
					}
					break;
				case 2:
					button += '<li id="stopTask"><a href="javascript:;" style="width:100%"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
					break;
				case 7:
					button += '<li id="startCopyIncrease"><a href="javascript:;" style="width:100%"><i class="viconfont vicon-ge_increment"></i> ' + LANG.UI_JOB_START_INCREASE_COPY + '</a></li>';
					break;
				case 10:
					button += '<li id="startCopyFull"><a href="javascript:;" style="width:100%"><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START_FULL_COPY + '</a></li>';
					break;
			}
		});
		$('#jobOperationGroup').html(button);

		$('#startCopyMirror').unbind().on('click', startJob); //启动镜像
		$('#startCopyJob').unbind().on('click', startJob); //启动任务
		$('#startCopyFull').unbind().on('click', startJob); //启动完整副本
		$('#startCopyIncrease').unbind().on('click', startIncrJob); //启动增量
		$('#stopTask').unbind().on('click', stopJob);   //终止任务

		//初始化操作按钮
		setBtnStatus(data);
	};

	//得到状态的显示类型
	let getStatusLevelClass = function (level) {
		let levelClass = "label-info";
		if (STATUS_CLASS.hasOwnProperty(level)) {
			levelClass = STATUS_CLASS[level];
		}
		return levelClass;
	}

	//得到开启和关闭的HTML内容
	let getFlagLevelInfo = function (flag) {
		if (!flag) {
			return `<span class="label label-warning">${LANG.UI_PUBLIC_OFF}</span>`;
		}
		return `<span class="label label-success">${LANG.UI_PUBLIC_ON}</span>`;
	}

	//得到时间策略描述信息
	let getTimeStrategy = function (msg) {
		let timeInfo = { copy: LANG.UI_PUBLIC_NOTHING };
		if (!msg) {
			return timeInfo;
		}
		for (let i = 0; i < msg.length; i++) {
			let strategy = msg[i];
			let des = "";
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
				//新加判断是否为一次性备份 ,如果为一次性备份则屏蔽掉保留策略
				$(".reserved_strategy_title").hide();
				$(".reserved_strategy_div").hide();
			} else {
				des += LANG.UI_PUBLIC_NOTHING;
			}

			timeInfo.copy = des;
		}
		return timeInfo;
	}

	//得到备份间隔描述
	let getStrategyFrequency = function (strategy) {
		let frequency = "";
		let frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
		for (let i = 1; i <= 52; i++) {
			if (strategy.frequency == "s" + i) {
				if (i == 1) {
					frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
				}
				else {
					frequency = frequencyLang.replace('x', i) + ",";
				}
			}
		}
		return frequency;
	}

	//获取每周显示日期
	let getStrategyWeek = function (days) {
		let desDays = '';
		$.each(days, function (i, d) {
			if (1 == d) {
				desDays += CONF.WEEK[i] + ", ";
			}
		});
		return desDays;
	}

	let getStrategyDays = function (days) {
		let desDays = '';
		$.each(days, function (i, d) {
			if (1 == d) {
				let day = i + 1;
				desDays += day + ", ";
			}
		});
		return desDays;
	}

	let getEachStrategy = function (strategy) {
		let desEach = '';
		desEach += strategy.start_time;
		//如果是英文版 需要加空格
		if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
			desEach += " "; //策略开始时间
		}
		desEach += LANG.UI_STRATEGY_START + ", ";
		if (strategy.roll_flag) {
			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.end_time;
			} else {
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + strategy.end_time + LANG.UI_STRATEGY_END;
			}

		} else {
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br>";
		return desEach;
	}

	//得到保留策略描述信息
	let getReservedStrategy = function (msg) {
		if (msg.value == 0) {
			$('.reservedStrategyDiv').hide();
		}
		let reservedStr = '';
		if (!msg) {
			reservedStr = LANG.UI_PUBLIC_NOTHING;
			return reservedStr;
		}
		// 保留类型
		if (CONF.RESERVE_STRATEGY_MODE.POINT == msg.strategy_mode) {
			reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_COPY_STRATEGY_RESERVE_MODE_POINT + '<br>';
		} else if (CONF.RESERVE_STRATEGY_MODE.CHIAN == msg.strategy_mode) {
			reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_COPY_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
		}
		if (CONF.RESERVE_TYPE.NUM == msg.type) {
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_STRATEGY_RESERVE_NUM + "<br>" + LANG.UI_STRATEGY_VALUE + ': ' + msg.value + '<br>';
		} else if (CONF.RESERVE_TYPE.DAY == msg.type) {
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_STRATEGY_RESERVE_DAY + "<br>";
			if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
				reservedStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY + ': ' + msg.value + '<br>';
			} else {
				reservedStr += LANG.UI_STRATEGY_VALUE + ': ' + msg.value + '<br>';
			}
		}
		return reservedStr;
	}

	//初始化历史任务表格
	let initHistoryGrid = function () {
		let initFlag = false;
		let task_uuid = $("#task_uuid").val();
		let options = {
			vin_url: "/api/v1/copy/job/history",
			vin_method: "GET",
			sortName: 'end_time',
			sortOrder: 'desc',
			vin_params: function () {
				return {
					task_uuid
				};
			},
			tableArea: '#copyHistory',
			detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
			detailFormatter: history_detail, //详情展开
			changeHeightBtn: true, //改变高度按钮
			resizable: true, //可变宽度
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			toolbarId: '#history_toolbar',
			buttonsToolbar: '#history_toolbar .vin_btnToolbar',
			onRefresh: function (params) {
				$("#historyTable").bootstrapTable('hideLoading');
			},

			columns: [
				{
					field: 'module_type',
					title: LANG.UI_SEARCH_MODE_TYPE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'data_size',
					title: LANG.UI_JOB_HIS_TOTAL_SIZE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'transfer_size',
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
					field: 'end_time',
					title: LANG.UI_PUBLIC_END_TIME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'status',
					title: LANG.UI_VISUAL_RESULT,
					sortable: true,
					align: 'center',
					formatter: errorFormatter,
				},
			],
		}
		let init = function () {
			if (!initFlag) {
				$('#historyTable').baseTableConfig().init(options);
				initFlag = true;
			} else {
				$('#historyTable').bootstrapTable('refresh');
			}
			// 改变表格高度
			$('#history_toolbar .change_height').on('click', changeHeight);
		}
		$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
			e.target // newly activated tab
			e.relatedTarget // previous active tab
			if ("#copyHistory" == e.target.hash) {
				init();
			}
		})
	}

	let errorFormatter = function (index, row) {
		switch (row.status_value) {
			case 0: //成功
				return '<span class="label label-sm label-success  ">' + row.status + '</span>';
			case 2: //中止
			case 45:
				return '<span class="label label-sm label-info  ">' + row.status + '</span>';
			case 3: //异常
			case 47:
				return '<span class="label label-sm label-warning  ">' + row.status + '</span>';
			case 1: //失败
				return '<span class="label label-sm label-danger  ">' + row.status + '</span>';
			default:
				return '<span class="label label-sm label-danger  ">' + row.status + '</span>';
		}
	}

	// 改变表格高度
	let changeHeight = function () {
		const btn = $('#history_toolbar .change_height i');
		const td = $('#historyTable>tbody>tr>td');
		if (changeHeightFlag == false) {
			changeHeightFlag = true;
			td.css({
				'padding-top': ROW_HIGH_PX,
				'padding-bottom': ROW_HIGH_PX
			})
			btn.addClass('icon-auto-height2');
		} else if (changeHeightFlag == true) {
			changeHeightFlag = false
			td.css({
				'padding-top': ROW_LOW_PX,
				'padding-bottom': ROW_LOW_PX
			})
			btn.removeClass('icon-auto-height2');
		}
	}
	let history_detail = function (index, row, element) {
		if (index != lastIndex[1]) {//只展开一行
			lastIndex.push(index);
			$('#historyTable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
			lastIndex.splice(0, 1);
		}
		let details = row.details.info;
		let module_type = row.module_type_value;
		//添加详情信息
		if (!details) {
			return;
		}
		return getCopyDetailsInfo(details, module_type);
	};

	let getCopyDetailsInfo = function (data, module_type) {
		if (typeof data.resource_limiting_node_config !== 'undefined') { // 如果开启了资源限制，只显示资源限制内容详情
			let resourceLimitingNodeConfig = data.resource_limiting_node_config;
			return `<table>
							<thead>
								<th>${LANG.UI_RESOURCE_LIMIT_CONFIG}</th>
								<th>${LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT}</th>
								<th>${LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD}</th>
							</thead>
							<tbody class="db-protected-table-detail">
								<tr>
									<td>${LANG.UI_PUBLIC_ON}</td>
									<td>${resourceLimitingNodeConfig[0].max_task_running_num}</td>
									<td>${getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec)}</td>
								</tr>
							</tbody>
						</table>`;
		}
		let thead = document.createDocumentFragment();
		let subType = parseInt($('#subType').val(), 10);
		let th = document.createElement('th');
		th.setAttribute('width', '10%');
		switch (module_type) {
			case CONF.MODULE_TYPE.VM:
				if (subType === 3) {
					th.textContent = LANG.UI_TASK_AWS_INSTANCE_NAME;
				} else {
					th.textContent = LANG.UI_JOB_VM_NAME;
				}
				break;
			case CONF.MODULE_TYPE.FS:
			case CONF.MODULE_TYPE.NAS:
			case CONF.MODULE_TYPE.OS:
				th.textContent = LANG.UI_COPY_DETAIL_HOST_NAME;
				break;
			case CONF.MODULE_TYPE.DB:
				th.textContent = LANG.UI_COPY_DETAIL_DB_NAME;
				break;
			case CONF.MODULE_TYPE.M365:
				th.textContent = LANG.UI_MICROSOFT365_ORGANIZATION_NAME;
				break;
		}
		thead.appendChild(th);
		for (let key in DETAIL_ROW_TITLE) {
			if (DETAIL_ROW_TITLE.hasOwnProperty(key)) {
				let ths = document.createElement('th');
				ths.setAttribute('width', '10%');
				ths.textContent = DETAIL_ROW_TITLE[key];
				thead.appendChild(ths);
			}
		}

		// 添加固定的表头
		let fixedThead = document.createElement('thead');
		fixedThead.appendChild(thead);
		let tbody = document.createElement('tbody');
		for (let item of data) {
			let tr = document.createElement('tr');
			for (let key in item) {
				if (key == 'valid_size') {
					continue;
				}
				let td = document.createElement('td');
				td.textContent = item[key].toString();
				tr.appendChild(td);
			}
			tbody.appendChild(tr);
		}

		let details = `<table>${fixedThead.innerHTML}${tbody.innerHTML}</table>`;
		return details;
	}

	//初始化表格
	let initCopyGrid = function () {
		// 判断分页展开状态
		if ($('#copyItem .page-list .dropdown').hasClass('open')) {
			paginationOpenFlag = true;
		} else {
			paginationOpenFlag = false;
		}
		let task_uuid = $("#task_uuid").val();
		let moduleType = parseInt($('#module_type').val());
		itemParams.task_uuid = task_uuid;
		itemParams.module_type = moduleType;
		let showDetail = false;
		let title = LANG.UI_JOB_VM_NAME;
		let subType = parseInt($('#subType').val());
		switch (moduleType) {
			case CONF.MODULE_TYPE.VM:
				showDetail = true;
				if (subType == 3) {
					title = LANG.UI_TASK_AWS_INSTANCE_NAME;
				}
				break;
			case CONF.MODULE_TYPE.FS:
			case CONF.MODULE_TYPE.NAS:
				title = LANG.UI_COPY_DETAIL_HOST_NAME;
				showDetail = true;
				// 对象存储
				if (subType == CONF.SUBMODULE_TYPE.OBS) {
					title = LANG.UI_PLATFORM_DES_OBS_NAME;
				} else if (subType == CONF.SUBMODULE_TYPE.HADOOP) {
					title = LANG.UI_HADOOP_CLUSTER_LIST_NAME;
				}
				break;
			case CONF.MODULE_TYPE.DB:
				showDetail = true;
				title = LANG.UI_COPY_DETAIL_DB_NAME;
				break;
			case CONF.MODULE_TYPE.OS:
				showDetail = false;
				title = LANG.UI_COPY_DETAIL_HOST_NAME;
				break;
			case CONF.MODULE_TYPE.M365:
				showDetail = true;
				title = LANG.UI_MICROSOFT365_ORGANIZATION_NAME;
				break;
			case CONF.MODULE_TYPE.KUBERNETES:
				showDetail = false;
				title = LANG.UI_COPY_DETAIL_LABEL_KUBERNETES_NAME;
				break;
		}
		let options = {
			vin_url: "/api/v1/copy/job/details",
			vin_method: "POST",
			vin_params: function () {
				return {
					...itemParams
				};
			},
			tableArea: '#copyItem',
			detailView: showDetail, //需要更新的表格配置项,此项为是否开启展开详情视图
			detailFormatter: copy_detail, //详情展开
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			toolbarId: '#copy_details_toolbar',
			buttonsToolbar: '#copy_details_toolbar .vin_btnToolbar',
			changeHeightBtn: true, //改变高度按钮
			searchInput: true, //搜索框
			searchClass: 'searchItemName', //搜索框类名
			searchSelector: '.searchItemName', //表格选择使用该搜索框
			placeholder: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH,
			onRefresh: function () {
				$("#copyTable").bootstrapTable('hideLoading');
			},
			PostBody: (data) => {
				if (null !== expandIndex) {
					$("#copyTable").bootstrapTable('expandRow', expandIndex);
				}
				// 展开分页选择
				if (paginationOpenFlag) {
					$('#copyItem .page-list .dropdown').addClass('open');
				}
			},
			onExpandRow: (index) => {
				if (null === expandIndex) {
					expandIndex = index;
				} else if (index !== expandIndex) {
					$("#copyTable").bootstrapTable('collapseRow', expandIndex);
					expandIndex = index;
				}
			},
			onCollapseRow: () => {
				expandIndex = null;
			},

			columns: [
				{
					field: 'host_name',
					title: title,
					sortable: false,
					align: 'center',
				},
				{
					field: 'time_point_num',
					title: LANG.UI_COPY_DETAILS_TIMEPOINT_NUM,
					sortable: false,
					align: 'center',
				},
				{
					field: 'data_size',
					title: LANG.UI_JOB_HIS_TOTAL_SIZE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'transfer_size',
					title: LANG.UI_PUBLIC_TRANSFER_SIZE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'speed',
					title: LANG.UI_TASK_AWS_INSTANCE_SPEED,
					sortable: false,
					align: 'center',
				},
				{
					field: 'percent',
					title: LANG.UI_PUBLIC_PROGRESS,
					sortable: false,
					align: 'center',
				},
				{
					field: 'status',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: false,
					align: 'center',
				},
				{
					field: 'description',
					title: LANG.UI_PUBLIC_DESCRIPTION,
					sortable: false,
					align: 'center',
				},
			],
		}
		if (!initGridFlag) {
			$('#copyTable').baseTableConfig().init(options);
			initGridFlag = true;
			// 搜索
			$('#copy_details_toolbar .search-btn').on('click', function (event) {
				let search = $('#copy_details_toolbar .searchItemName').val();
				itemParams.search = search;
				$('#copyTable').bootstrapTable('refresh');
			});

			$('#copy_details_toolbar .searchItemName').keypress(function (e) {
				if (e.which == 13) {
					let search = $('#copy_details_toolbar .searchItemName').val();
					itemParams.search = search;
					$('#copyTable').bootstrapTable('refresh');
				}
			});

			$('#copy_details_toolbar .clear').on('click', function () {
				$('#searchInput').val('');
				itemParams.search = '';
				$('#copyTable').bootstrapTable('refresh');
			});
		} else {
			$('#copyTable').bootstrapTable('refresh');
		}
		// 改变表格高度
		$('#copy_details_toolbar .change_height').on('click', changeDetailHeight);
	}

	// 改变表格高度
	let changeDetailHeight = function () {
		const td = $('#copyTable>tbody>tr>td');
		const btn = $('#copy_details_toolbar .change_height i');
		if (changeHeightFlag == false) {
			changeHeightFlag = true;
			td.css({
				'padding-top': ROW_HIGH_PX,
				'padding-bottom': ROW_HIGH_PX
			})
			btn.addClass('icon-auto-height2');
		} else if (changeHeightFlag == true) {
			changeHeightFlag = false
			td.css({
				'padding-top': ROW_LOW_PX,
				'padding-bottom': ROW_LOW_PX
			})
			btn.removeClass('icon-auto-height2');
		}
	}
	let copy_detail = function (index, row, element) {
		let moduleType = parseInt($('#module_type').val())
		if (index != copyIndex[1]) {//只展开一行
			copyIndex.push(index);
			$('#copyTable').bootstrapTable('collapseRow', copyIndex[copyIndex.length - 2]);
			copyIndex.splice(0, 1);
		}
		let dirPath = '';
		try {
			dirPath = [CONF.MODULE_TYPE.FS, CONF.MODULE_TYPE.NAS, CONF.MODULE_TYPE.M365].includes(moduleType) ? JSON.parse(row.dir_path) : row.dir_path;
		} catch (e) {
			return;
		}
		let content = '<table><tbody>';
		let pathName = LANG.UI_COPY_SOURCE_DIR_PATH;
		if (taskType == 19 || taskType == 20) {
			pathName = LANG.UI_COPY_ARCHIVE_SOURCE_DIR_PATH
		}
		if (moduleType == CONF.MODULE_TYPE.FS || moduleType == CONF.MODULE_TYPE.NAS) {
			content += '<tr><td>' + pathName + '：</td><td><textarea>' + dirPath.join('\n') + '</textarea></td></tr>';
		} else if (moduleType == CONF.MODULE_TYPE.M365) {
			content += '<tr><td>' + LANG.UI_COPY_DETAIL_USER_LIST_LABEL + '：</td><td><div style="width:100%">' + dirPath.join('\n') + '</div></td></tr>';
		} else {
			content += '<tr><td>' + pathName + '：</td><td>' + dirPath + '</td></tr>';
		}
		content += '</tbody></table>';
		$(element).append(content);
	}

	//停止
	let stopJob = function () {
		let task_uuid = $('#task_uuid').val();
		if ($(this).find('a').hasClass('disablebtn')) {
			return true;
		}
		Metronic.blockUI({ target: '#jobDetail', animate: true });
		pAjaxRequest({}, "/api/v1/jobs/stop/" + task_uuid + "", "POST",
			function (res) {
				let op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
				Metronic.unblockUI('#jobDetail');
				if (operateResponseList(res, op)) {
					$('#jobDetail').bootstrapTable('refresh');
					$("#jobDetail").bootstrapTable('hideLoading');
				}
			}
		);
	}

	//启动镜像/完整副本
	let startJob = function () {
		if ($(this).find('a').hasClass('disablebtn')) {
			return true;
		}
		startJobFunc(1);
	}

	// 启动增量副本
	let startIncrJob = function () {
		if ($(this).find('a').hasClass('disablebtn')) {
			return true;
		}
		startJobFunc(2);
	}

	// 启动任务
	let startJobFunc = function (type) {
		// 操作权限判断，需要传归属用户的user_uuid
		checkOperateAuth({ type: 1, user_uuid: user_uuid, auth: 'data_manager' }, () => {
			let task_uuid = $('#task_uuid').val();
			Metronic.blockUI({ target: '#jobDetail', animate: true });
			pAjaxRequest({ start_type: type }, "/api/v1/jobs/start/" + task_uuid + "", 'POST', function (data) {
				Metronic.unblockUI('#jobDetail');
				let op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
				if (operateResponseList(data, op)) {
					$("#jobDetail").bootstrapTable('refresh');
					$("#jobDetail").bootstrapTable('hideLoading');
				}
			});
		});
	}

	//根据任务状态设置按钮权限
	let setBtnStatus = function (data) {
		setControlBtn('startCopyMirror', true);
		setControlBtn('startCopyFull', true);
		setControlBtn('startCopyIncrease', true);
		setControlBtn('stopTask', true);
		setControlBtn('pauseTask', true);
		switch (_taskStatus) {
			case CONF.TASK_STATUS.WAITTING://等待
				setControlBtn('pauseTask', false);
				break;
			case CONF.TASK_STATUS.RUNNING://运行
			case CONF.TASK_STATUS.STARTING://启动中
			case CONF.TASK_STATUS.ABNORMAL://异常
			case CONF.TASK_STATUS.PREPARING: //准备中
				//运行和准备中停止中,禁用运行
				setControlBtn('startCopyMirror', false);
				setControlBtn('startCopyFull', false);
				setControlBtn('startCopyIncrease', false);
				setControlBtn('startCopyJob', false);
				break;
			case CONF.TASK_STATUS.SYNC://任务同步
				break;
			case CONF.TASK_STATUS.PAUSING:	//暂停中
				setControlBtn('pauseTask', false);
				break;
			case CONF.TASK_STATUS.STOPPED:
				//停止,禁用停止
				$('#stopJob').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a>');
				setControlBtn('stopTask', false);
				setControlBtn('pauseTask', false);
				break;
			case CONF.TASK_STATUS.STOPPING://停止中
				//停止中状态，变为强制停止
				$('#stopTask').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
				setControlBtn('pauseTask', false);
				setControlBtn('startCopyMirror', false);
				setControlBtn('startCopyFull', false);
				setControlBtn('startCopyIncrease', false);
				setControlBtn('startCopyJob', false);
				break;
			case CONF.TASK_STATUS.FINISHED:	//已完成
				addForbidButton(uuid, "stop");
				break;
			case CONF.TASK_STATUS.PAUSED: //暂停
				setControlBtn('pauseTask', false);
				break;
			case CONF.TASK_STATUS.NETWORK_FAULT:
				setControlBtn('pauseTask', false);
				setControlBtn('startCopyMirror', false);
				setControlBtn('startCopyFull', false);
				setControlBtn('startCopyIncrease', false);
				setControlBtn('startCopyJob', false);
				break;
			case CONF.TASK_STATUS.ERROR:
				setControlBtn('pauseTask', false);
				break;
		}

		if (data.specified_timepoint_list) {
			setControlBtn('startCopyIncrease', false);
		}
	}

	//设置按钮是否可用
	let setControlBtn = function (id, available) {
		if (available) {
			$("#" + id).find('a').removeClass('disablebtn');
		} else {
			$("#" + id).find('a').addClass('disablebtn');
		}
	}


	//初始化流量
	let initSpeed = function () {
		// 根据不同分辨率动态计算echart的高度和宽度
		let chartWidth = $('.portlet-charts__body__speedchart').width();
		let chartHeight = $('.portlet-charts__body__speedchart').height();
		$('#speedchart').css({ 'width': chartWidth + 'px', 'height': chartHeight + 'px' });

		// 基于准备好的dom，初始化echarts实例
		myChart = echarts.init(document.getElementById('speedchart'));
		// 指定图表的配置项和数据
		let option = {
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
			yAxis:
			{
				type: 'value',
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
				axisLabel: {
					formatter: function (value, index) {
						//向上取整显示纵坐标
						if (value >= 1024) {
							return Math.ceil(value / 1024) + "MB/s";
						} else {
							if (value < 1) {
								return value + "KB/s";
							} else {
								return Math.ceil(value) + "KB/s";
							}
						}
					},
					color: '#86909C',
				},
			},
			series: [
				{
					name: 'net',
					type: 'line',
					stack: 'total',
					showSymbol: false,
					hoverAnimation: false,
					smoothMonotone: 'x',
					animation: false,
					smooth: true,
					areaStyle: {
						normal: {
							color: CONF.VENDOR == CONF.VENDOR_LIST.gmp ? '#2A87C8' : new echarts.graphic.LinearGradient(0, 0, 0, 1, [
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
						}
					},
					data: []
				},
			],
			color: CONF.VENDOR == CONF.VENDOR_LIST.gmp ? ['#2A87C8'] : ['#44b6ae']
		};

		let data = [], nowTime = [];

		let parseNum = function (num) {
			num = parseInt(num);
			num = num >= 10 ? num : "0" + num;
			return num;
		}

		let getShowTime = function (timeStamp) {
			let myDate = new Date(parseInt(timeStamp));
			let date = myDate.toLocaleDateString();
			let hours = myDate.getHours();
			let minutes = myDate.getMinutes();
			let seconds = myDate.getSeconds();
			return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
		}

		//初始化任务进度曲线图
		let initTaskSpeed = function (d) {

			if (initChartFlag) return; //初始化了就直接返回

			let serverTime = d.t * 1000;
			for (let i = 100; i > 0; i--) {
				data.push(0);
				nowTime.push(getShowTime(serverTime - i * 3000));
			}
			option.series[0].data = data
			option.xAxis.data = nowTime;
			myChart.setOption(option);

			initChartFlag = true;
		}
		let task_uuid = $("#task_uuid").val();
		function update() {
			pAjaxRequest({ task_uuid: task_uuid }, "/api/v1/copy/job/speed", "GET", function (res) {
				initTaskSpeed(res.data);
				data.shift();
				data.push(res.data.speed);
				option.series[0].data = data;

				nowTime.shift();
				nowTime.push(res.data.nowTime);
				option.xAxis.data = nowTime;


				myChart.setOption(option);
			})
			timerTask.copyJob_speed = setTimeout(update, INTERVAL);
		}
		update();

		window.onresize = function () {
			myChart.resize();
		}
	}

	let watchEchartSizeChange = function () {
		window.onresize = function () {
			let chartWidth = $('.portlet-charts__body__speedchart').width();
			let chartHeight = $('.portlet-charts__body__speedchart').height();
			$('#speedchart').css({ 'width': chartWidth + 'px', 'height': chartHeight + 'px' });
			myChart.resize();
		}
	}
	let initTableTab = function () {
		const copyTitle = $('#copyLiLabel');
		let module_type = parseInt($('#module_type').val());
		let subType = parseInt($('#subType').val());
		let iconDom = $('#copyLi i');
		switch (module_type) {
			case CONF.MODULE_TYPE.VM:
				if (subType == 3) {
					copyTitle.html(LANG.UI_COPY_DETAIL_PUBLIC_CLOUD_LIST);
				} else {
					copyTitle.html(LANG.UI_COPY_DETAIL_VM_LIST);
				}
				break;
			case CONF.MODULE_TYPE.FS:
				copyTitle.html(LANG.UI_COPY_DETAIL_FS_LIST);
				if (subType == CONF.SUBMODULE_TYPE.OBS) { //对象存储
					copyTitle.html(LANG.UI_PLATFORM_DES_OBS_list);
				} else if (subType == CONF.SUBMODULE_TYPE.HADOOP) { //hadoop
					copyTitle.html(LANG.UI_HADOOP_CLUSTER_LIST);
				}
				iconDom.attr('class', 'viconfont vicon-ge_backup_host');
				break;
			case CONF.MODULE_TYPE.DB:
				copyTitle.html(LANG.UI_COPY_DETAIL_DB_LIST);
				iconDom.attr('class', 'viconfont vicon-ge_database_tasks');
				break;
			case CONF.MODULE_TYPE.OS:
				copyTitle.html(LANG.UI_COPY_DETAIL_OS_LIST);
				iconDom.attr('class', 'viconfont vicon-dbhost');
				break;
			case CONF.MODULE_TYPE.NAS:
				copyTitle.html(LANG.UI_COPY_DETAIL_NAS_LIST);
				iconDom.attr('class', 'viconfont vicon-nasmanager');
				break;
			case CONF.MODULE_TYPE.M365:
				copyTitle.html(LANG.UI_MICROSOFT365_ORGANIZATION_LIST);
				iconDom.attr('class', 'viconfont vicon-zuzhi');
				break;
			case CONF.MODULE_TYPE.KUBERNETES:
				copyTitle.html(LANG.UI_COPY_DETAIL_LABEL_KUBERNETES_LIST);
				iconDom.attr('class', 'viconfont vicon-overciew-k8s');
				break;
		}
	}
	const initLogGrid = function () {
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
	}

	return {
		//main function to initiate the module_type
		init: function () {
			initTableTab();
			initBasicInfo();
			initCopyGrid();
			initLogGrid();
			initSpeed();
			initHistoryGrid();
			watchEchartSizeChange();
		}

	};

}();

jQuery(document).ready(function () {
	CopyJobDetails.init();
});
