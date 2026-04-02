var addArchive = function () {
	let tapeFlag = false; //磁带存储标志
	let tapeSource = false; //磁带标志
	let SETTINGS = null;
	const MODULE_TYPE = parseInt($('#module_type').val());
	const SUB_MODULE_TYPE = parseInt($('#sub_module_type').val());
	// 策略默认配置
	const defaultConfig = {
		dom: $('#backupStrategyDiv'),
		time: false,
		store: false,
		archive_flag: true,
	};
	// 副本归档任务类型
	const COPY_TASK_TYPE = [CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.BACKUP_COPY_FETCH, CONF.TASK_TYPE.ARCHIVE, CONF.TASK_TYPE.ARCHIVE_FETCH];
	// 时间策略
	const BACKUP_TYPE = {
		STRATEGY: 'strategy',
		ONCE: 'oncetime'
	}
	// 传输加密算法配置
	const ENCRYPT_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA, 2: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM };
	// 压缩等级配置
	const COMPRESS_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST, 2: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL, 3: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER, 4: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST };
	let editFlag = false; // 修改标志
	let strategy_type = 'strategy';
	let data = {
		copyItemInfo: {
			auto_add_flag: false,
			copy_ignore_object_list: [],
		},
		strategyInfo: {
			timeStrategy: {
				type: BACKUP_TYPE.STRATEGY,
				archiveStrategy: {},
				startTime: '',
			},
			reserveStrategy: {
				reserveInfo: {
					enable_flag: true,
					gfs_strategy_item_list: [],
				},
			},
			transferStrategy: {
				transferNet: '',
				compress: false,
				compress_method: 0,
			},
			nodeInfo: {
				storage_uuid: '',
				node_uuid: '',
			},
			highInfo: {},
			storageStrategy: {
				storageInfo: {
					redundant_data_proportion: 50,
					data_container_size: 1073741824,
				}
			},
			copy_gfs_strategy: {
				copy_week_flag: false,
				copy_month_flag: false,
				copy_year_flag: false,
			},
		},
		node_uuid: '',
		storage_uuid: '',
		module_type: MODULE_TYPE,
		sub_module_type: SUB_MODULE_TYPE,
		remoteSource: false,
		copy_last_chain_flag: false,
	};
	let no_transport_flag = false;
	//备份任务步骤
	let wizardInit = function () {
		if (!jQuery().bootstrapWizard) {
			return;
		}
		let form = $('#submit_form');
		let error = $('.alert-danger', form);
		let success = $('.alert-success', form);
		let handleTitle = function (tab, navigation, index) {
			let total = navigation.find('li').length;//总共的步骤数
			let current = index + 1;      //当前步骤
			jQuery('li', $('#archiveContent')).removeClass("done");
			let li_list = navigation.find('li');
			for (let i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}

			//如果第一步 上一步按钮隐藏
			if (current == 1) {
				$('#archiveContent').find('.button-previous').css('visibility', 'hidden');
				$('#archiveContent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#archiveContent').find('.button-previous').css('visibility', 'visible');
				$('#archiveContent').find('.button-next').removeClass('next-btn-margin-left');
			}

			//如果是最后一步
			if (current >= total) {
				$('#archiveContent').find('.button-next').hide();
				$('#archiveContent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#archiveContent').find('.button-next').show();
				$('#archiveContent').find('.button-submit').css('visibility', 'hidden');
			}


			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#archiveContent').bootstrapWizard({
			'nextSelector': '.button-next',
			'previousSelector': '.button-previous',
			onTabClick: function (tab, navigation, index, clickedIndex) {
				return false;
			},

			//下一步
			onNext: function (tab, navigation, index) {
				success.hide();
				error.hide();
				switch (index) {
					case 1:
						if (step1Valid() == false) {
							return false;
						}
						break;
					case 2:
						if (step2Valid() == false) {
							return false;
						}
						break;
					case 3:
						if (step3Valid() == false) {
							return false;
						}
						break;
				}
				handleTitle(tab, navigation, index);
			},

			//上一步
			onPrevious: function (tab, navigation, index) {
				success.hide();
				error.hide();

				handleTitle(tab, navigation, index);
			},

			//进度条显示
			onTabShow: function (tab, navigation, index) {
				let total = navigation.find('li').length;
				let current = index + 1;
				let $percent = (current / total) * 100;
				$('#archiveContent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});

		$('#archiveContent').find('.button-previous').css('visibility', 'hidden');
		$('#archiveContent .button-submit').click(submit).css('visibility', 'hidden');
	};
	//初始化副本每个步骤传参
	let initStepOne = function () {
		// 未授权不显示worm
		if (!CONF.FUNCTIONS.includes('worm')) {
			$('#sateLi').hide();
			$('.safeDiv').hide();
		}
		if ($('#uuid').val()) {
			editFlag = true;
			COPY_TIP = LANG.UI_COPY_MODIFY;
			let task_uuid = $('#uuid').val();
			let taskInfoBack = function (res) {
				if (res.success) {
					SETTINGS = res.data;
					initOldData();
					getOldSourceType(); //初始化副本源
					$('#backupTarget').backupTarget({
						node_uuid: SETTINGS.node.nodeuuid,
						node_pool_uuid: SETTINGS.node.node_pool_uuid,
						storage_uuid: SETTINGS.node.storageuuid,
						storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
						storage_pool_type: SETTINGS.node.storage_pool_type,
					}); //初始化副本目的地
					initStep3Settings();  //初始化副本策略
				}
			};
			pAjaxRequest({ task_uuid: task_uuid }, "/api/v1/copy/resources/task", "GET", taskInfoBack, true);
		} else {
			editFlag = false;
			// 初始化源存储选项
			$('.copy-source-div').mySource({ editFlag: false, module_type: MODULE_TYPE, sub_module_type: SUB_MODULE_TYPE, showDom: $('.copy-source-list-div'), archive_flag: true, editFirstInitFlag: false });; // 初始化归档源
			$('#backupTarget').backupTarget(); //初始化本地存储下拉框
			initTransportConfig(); // 初始化传输策略
			$('#retry_config').retryStrategy({ module_type: 'copy' }); // 初始化重试策略
			$('#backupStrategyDiv').initBackupStrategy(defaultConfig); // 初始化通用策略
			$('#reserveType').find('option[value=2]').remove();
			// 归档没有按链保留
			reserveShowCopy();
		};
	}
	let reserveShowCopy = () => {
		// 归档没有按链保留
		$('#reserveMode option[value=2]').remove();
	}
	// 初始化修改任务的信息
	let initOldData = function () {
		//step1
		data.copyItemInfo.copy_list = SETTINGS.copy_list;
		data.copyItemInfo.copy_mode = SETTINGS.copy_mode;
		data.copyItemInfo.chain_length = SETTINGS.chain_length;
		data.module_type = SETTINGS.module_type;
		//step2
		//备份方式:策略/时间
		data.strategyInfo.timeStrategy.type = SETTINGS.time_strategy.type;
		strategy_type = SETTINGS.time_strategy.type;

		//按时间备份的时间
		if (SETTINGS.time_strategy.type == BACKUP_TYPE.ONCE) {
			data.strategyInfo.timeStrategy.startTime = SETTINGS.time_strategy.data;
		} else {
			let info = {};
			let time_strategy = SETTINGS.time_strategy.data;
			for (let i = 0; i < time_strategy.length; i++) {
				let days = [];
				for (let j = 0; j < time_strategy[i].days.length; j++) {
					if (time_strategy[i].days.length == 1 && time_strategy[i].days[j] == false) {
						days = [];
					} else if (time_strategy[i].days[j] == true) {
						time_strategy[i].days[j] = 1;
						days.push(time_strategy[i].days[j]);
					} else if (time_strategy[i].days[j] == false) {
						time_strategy[i].days[j] = 0;
						days.push(time_strategy[i].days[j]);
					}
				}
				info.days = days;
				info.mode = time_strategy[i].mode;
				info.type = time_strategy[i].strategy_type;
				info.startTime = time_strategy[i].start_time;
				info.rollFlag = time_strategy[i].roll_flag;
				info.rollInterval = time_strategy[i].roll_interval;
				info.endTime = time_strategy[i].roll_end_time;
			}
			data.strategyInfo.timeStrategy.archiveStrategy = info;
		}
		//step3
		//保留策略
		data.strategyInfo.reserveStrategy.reserveInfo.type = SETTINGS.brs.type;
		data.strategyInfo.reserveStrategy.reserveInfo.value = SETTINGS.brs.number;
		//初始化传输信息
		data.strategyInfo.transferStrategy = SETTINGS.bts;
		//初始化节点信息
		data.strategyInfo.nodeInfo.storage_uuid = SETTINGS.node.storage_uuid;
		data.strategyInfo.nodeInfo.real_storage_info = SETTINGS.node.real_storage_info;
		data.strategyInfo.nodeInfo.type = SETTINGS.node.type;
		//限速策略
		data.strategyInfo.speedStrategy = SETTINGS.speedInfo;

		//任务信息
		data.taskName = SETTINGS.task_name;
		data.task_uuid = SETTINGS.task_uuid;
	}
	let initStep3Settings = function () {
		if (SETTINGS.time_strategy.type == BACKUP_TYPE.ONCE) {
			// 没有保留策略
			$('.strategy_reserve-div').hide();
			$('#reserveStrategyDiv').hide();
		}
		// 初始化时间策略
		initStrategy(SETTINGS.time_strategy);
		// 使用策略插件初始化策略
		let strategy = { reserve: {}, speedlimit: SETTINGS.speedInfo };
		strategy.reserve.reserveInfo = SETTINGS.brs;
		defaultConfig.strategy = strategy;
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig); // 带参初始化通用策略
		$('#reserveType').find('option[value=2]').remove(); // 副本只能个数保留
		// 传输策略
		initTransportConfig(SETTINGS); // 带参初始化传输策略
		//重试策略
		$('#retry_config').retryStrategy({ 'retry_strategy': SETTINGS.retry_strategy, module_type: 'copy' }, 'edit');
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.ignore_resource_limiting_flag);
		$(`#data_container_size`).val(SETTINGS.bss.data_container_size);
		// 归档没有按链保留
		reserveShowCopy();
	}
	// 获取副本源类型
	let getOldSourceType = function () {
		let time_point_uuids = SETTINGS.copy_list[0].time_point_uuids;
		let archive_uuid = SETTINGS.copy_list[0].archive_timepoint_uuid;
		let data_type = 1;
		// 带有时间点uuid则为数据源
		if (time_point_uuids.length != 0 || archive_uuid) {
			data_type = 4;
		}
		let taskTypeBack = function (res) {
			if (res.success) {
				let source_task_type = res.data.task_type;
				if (COPY_TASK_TYPE.includes(source_task_type)) {
					if (time_point_uuids.length == 0 && !archive_uuid) {
						data_type = 3;
					} else {
						data_type = 4;
					}
				} else {
					if (time_point_uuids.length != 0 || archive_uuid) {
						data_type = 2;
					}
				}
				$('#dataType').val(data_type).prop("disabled", true);
				$('.copyList').hide();
				Metronic.blockUI({ target: '.tree_div', animate: true });
			}
			// 初始化修改副本源
			$('.copy-source-div').mySource({ editFlag: true, module_type: MODULE_TYPE, sub_module_type: SUB_MODULE_TYPE, oldInfo: SETTINGS, showDom: $('.copy-source-list-div'), archive_flag: true });
		}
		pAjaxRequest({ source_task_uuid: SETTINGS.source_task_uuid, data_type: data_type }, "/api/v1/copy/resources/type", "GET", taskTypeBack, true);

	}

	//初始化监听事件
	let initListener = function () {
		$('#toBackup').on('click', function () { //无归档源，备份引导
			switch (MODULE_TYPE) {
				case CONF.MODULE_TYPE.VM://虚拟机
					LOCATION('./content/vm/vmbackup.php', 'vmprotect');
					break;
				case CONF.MODULE_TYPE.PRIVATE_CLOUD://私有云
					LOCATION('./content/vm/vmbackup.php?sub_module_type=2', 'prcloud_protect');
					break;
				case CONF.MODULE_TYPE.PUBLIC_CLOUD://公有云
					LOCATION('./content/aws/awsbackup.php', 'awsbackup');
					break;
				case CONF.MODULE_TYPE.OS://操作系统
					LOCATION('./content/os/osbackup.php', 'osbackup');
					break;
			}
		});
		$('#backuptype').on('change', backupTypeHandler);//副本方式选择
	}

	// ---------------------归档源--------------------
	let step1Valid = function () {
		data.data_type = parseInt($('#dataType').val());	//选择副本源类型
		// 获取选择的副本源信息
		const plugin = $('.copy-source-div').data('mySource');
		let result = plugin.getCopySource();
		data.copyItemInfo.copy_list = result.copy_list;
		// 异常退出
		if (result.stopFlag) {
			return false;
		}
		tapeSource = result.tapeSource;
		$(".selectCopyTip").hide();
		//如果是选择备份|副本数据做副本 只能做一次性副本 并且屏蔽保留策略
		if (parseInt($('#dataType').val()) == 2 || parseInt($('#dataType').val()) == 4) {
			strategy_type = "oncetime";
			initStrategy();
			$('.setStrategy').hide();
			$('.setOnceTime').show();
			// 没有保留策略
			$('.strategy_reserve-div').hide();
			$('#reserveStrategyDiv').hide();
			$('.strategy_reserve-content').hide();
		} else {
			$('#backuptype').removeAttr('disabled');
			$('.strategy_reserve-content').show();
		}
		//在第四步展示源信息
		showStep1();
		storageLimit();
		initReserveStrategyDes(); //初始化策略描述
		return true;
	}
	// 处理磁带不能到磁带
	let storageLimit = () => {
		let type = $('#storage option:selected').data('type');
		// 构建参数对象
		let param = { exclude_storage_type_list: [CONF.BD_STORAGE_TYPE.REMOTE] };
		// 磁带不能副本到磁带
		if (type === CONF.BD_STORAGE_TYPE.TAPE || tapeSource) {
			param.exclude_storage_type_list = [...[CONF.BD_STORAGE_TYPE.TAPE]];
		}
		if (editFlag) {
			param = {
				...param,
				node_uuid: SETTINGS.node.node_uuid,
				node_pool_uuid: SETTINGS.node.node_pool_uuid,
				storage_uuid: SETTINGS.node.storage_uuid,
				storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
				storage_pool_type: SETTINGS.node.storage_pool_type
			};
		}
		$('#backupTarget').backupTarget(param);
	}

	let showStep1 = function () {
		//任务名获取
		let initName = function (res) {
			if (res.success) {
				$('#jobName').val(res.data);
			};
		};
		if (editFlag) {
			$('#jobName').val(SETTINGS.task_name);
		} else {
			pAjaxRequest({ archive_flag: true }, "/api/v1/copy/jobs/name", "GET", initName, true);
		}
	}

	// ----------------------------目的地----------------

	//选择副本目的地
	let step2Valid = function () {
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		// 时间策略信息
		let strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		// 节点信息
		let results = getNodeInfo();
		if (strategyConfig.type == 'oncetime') {
			$('.strategy_reserve-div').hide();
			$('#reserveStrategyDiv').hide();
		} else {
			$('.strategy_reserve-div').show();
			$('#reserveStrategyDiv').show();
			initReserveStrategyDes();
		}
		if (data.strategyInfo.nodeInfo.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
			tapeFlag = true;
			$('.reserve-strategy-form').hide();
			pAjaxRequest({ group_uuid: data.strategyInfo.nodeInfo.storage_uuid }, '/api/v1/tapes/group/strategy', "GET", (res) => {
				$('.tape-strategy-form .reserveDes').text(LANG.UI_TAPE_USE_GROUP_STRATEGY);
				$('.tape-strategy-form .reserveDes').attr('title', LANG.UI_TAPE_USE_GROUP_STRATEGY);
				$('.tape-strategy-form span.font-green-seagreen').text(`${LANG.UI_GLOBAL_STRATEGY_NAME}`);
				$('.tapeStrategyDiv').show();
				$('.generate').text(res.data.backup_set_strategy_des);
				$('.reserve').text(res.data.reserve_strategy_des);
				$('.tape-strategy-form').show();
				//配置总览页面
				$('#reserveStrategyDiv>.control-label').text(LANG.UI_TAPE_GROUP_STRATEGY);
				$('.reserveStrategyShow').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${res.data.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${res.data.reserve_strategy_des}`);
			}, true);
			// 磁带存储,屏蔽安全策略
			$('#sateLi').hide();
			$('.safeDiv').hide();
		} else {
			tapeFlag = false;
			$('.reserve-strategy-form').show();
			$('.tapeStrategyDiv').hide();
			$('.tape-strategy-form').hide();
			$('.tape-strategy-form span.font-green-seagreen').text(`${LANG.UI_STRATEGY_RESERVE}`);
			$('#reserveStrategyDiv>.control-label').text(LANG.UI_STRATEGY_RESERVE);
			let value = 30;
			if (editFlag) {
				value = SETTINGS.brs.value == 65535 ? 30 : SETTINGS.brs.value;
			}
			$('#spinnerNum').spinner("value", value);
			$('#spinnerDay').spinner("value", value);
		}
		if (tapeFlag || tapeSource) {
			$('#threadNum').val(1);
			$('.threadNum-number-div .spinner-group').spinner('disable');
			$('.threadNum-number-div').hide();
		} else {
			$('.threadNum-number-div .spinner-group').spinner('enable');
			$('.threadNum-number-div').show();
		}
		// 虚拟机、操作系统 非云存储且合并副本才显示按备份点保留
		if (!cloudFlag) {
			$('#reserveMode').val(1).removeAttr('disabled');
		} else {
			// 云存储只能按链保留
			$('#reserveMode').val(2).prop('disabled', 'true');
		}
		// 卷不显示 存储策略
		if (data.module_type == CONF.MODULE_TYPE.OS && data.sub_module_type == 0) {
			$('.merge-mode-tab').hide()
		}
		return results;
	}
	// 保留策略描述
	let initReserveStrategyDes = function () {
		const reserveDes = $('.reserveDes');
		let des = "";
		let type = parseInt($('#reserveType').val());
		let value = 0;
		// 保留值
		let spinnerNumInputVal = $('#spinnerNumInput').val();
		let spinnerDayInputVal = $('#spinnerDayInput').val();
		let label = $('#reserveMode option:selected').text();
		// 保留类型
		des += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + label + ', ';
		// 获取描述信息
		const updateDescription = (reserveType, num, langKey, inputId) => {
			if (CONF.RESERVE_TYPE[reserveType] === type) {
				des += LANG.UI_RESERVE_RETENTION_MODE + ': ' + langKey;
				// num不合法则重置为空
				value = isNaN(parseInt(num, 10)) || !num ? '' : parseInt(num, 10);
				var methoddes = LANG.UI_STRATEGY_VALUE;
				if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && reserveType == 'DAY') {
					methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY
				}
				des += `, ${methoddes}: ${value < 0 ? 1 : value}`;
				// 如果输入不合法则重置为空
				if (isNaN(parseInt(num, 10))) {
					$(`#${inputId}`).val('');
				}
			}
		};
		// 获取按个数保留描述
		if (CONF.RESERVE_TYPE.NUM) {
			updateDescription('NUM', spinnerNumInputVal, LANG.UI_BACKUP_NUM, 'spinnerNumInput');
		}
		// 获取按天数保留描述
		if (CONF.RESERVE_TYPE.DAY) {
			updateDescription('DAY', spinnerDayInputVal, LANG.UI_BACKUP_DAY, 'spinnerDayInput');
		}
		reserveDes.html(des);
		reserveDes.prop('title', des);
	}

	//得到副本目标存储和计算节点信息
	let getNodeInfo = function () {
		let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		data.strategyInfo.nodeInfo.node_check = !backupTargetInfo.node_uuid;
		data.strategyInfo.nodeInfo.storage_check = !backupTargetInfo.storage_uuid;
		data.strategyInfo.nodeInfo.storage_uuid = backupTargetInfo.storage_uuid;
		data.strategyInfo.nodeInfo.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;
		data.strategyInfo.nodeInfo.node_uuid = backupTargetInfo.node_uuid;
		data.strategyInfo.nodeInfo.node_pool_uuid = backupTargetInfo.node_pool_uuid;
		data.strategyInfo.nodeInfo.storage_type = backupTargetInfo.storage_type;
		data.strategyInfo.nodeInfo.storage_worm_config = backupTargetInfo.storage_worm_config;
		initResourceLimit(backupTargetInfo.node_uuid_list);
		// 初始化传输网络
		if (backupTargetInfo.node_uuid != '') {
			$('.transferNet').show();
			if (editFlag) {
				$('#transferNetworkTree').transferNetwork({
					node_uuid: backupTargetInfo.node_uuid,
					network_uuid: SETTINGS.bts.network_uuid,
					network_pool_uuid: SETTINGS.bts.network_pool_uuid,
					storage_uuid: backupTargetInfo.storage_uuid,
					remote_flag: false,
					remote_network_ip: SETTINGS.bts.transferNet,
					remote_network_port: SETTINGS.bts.transferPort,
				});
			} else {
				$('#transferNetworkTree').transferNetwork({ node_uuid: backupTargetInfo.node_uuid });
			}
		} else {
			$('.transferNet').hide();
			no_transport_flag = true;
		}
		// 判断云存储
		if (backupTargetInfo.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
			cloudFlag = true;
		} else {
			cloudFlag = false;
		}
		$('.storage_show').html(backupTargetInfo.storage_text);
		$('.node_show').html(backupTargetInfo.node_text);
		//初始化WORM防护
		if (!backupTargetInfo.storage_uuid) {  // 没有选择存储
			$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_storage);
		} else {
			if (!backupTargetInfo.storage_worm_config.flag) {  // 存储未开启worm
				$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_worm);
			} else {
				if (editFlag) {
					$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal, 'col-md-3', false, SETTINGS.safe_strategy.worm_flag, SETTINGS.safe_strategy.worm_protection_time);
					$('#wormConfig_check').trigger('switchChange.bootstrapSwitch');
				} else {
					$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
				}
			}
		}
		return true;
	}

	// ----------------------------归档策略----------------

	//初始化时间策略
	let initStrategy = function (strategy_config = null) {
		$('.copy-strategy-config').copyTimeStrategy({ strategy_type: strategy_type, showDom: $('.timeStrategyShow'), strategyConfig: strategy_config, archive_flag: true });
	}

	//备份类型
	let backupTypeHandler = function () {
		if (BACKUP_TYPE.STRATEGY == this.value) {
			//按策略备份
			$('.strategy_reserve-div').show(); //显示保留策略对应描述
			$('#reserveStrategyDiv').show();
			$('#spinnerNumInput').val(30); //
			if (tapeFlag) {
				$('.strategy_reserve-div').hide();
			} else {
				$('.strategy_reserve-div').show();
			}
		} else if ('oncetime' == this.value) {
			//一次性备份
			$('.strategy_reserve-div').hide();	//隐藏保留策略
			$('#reserveStrategyDiv').hide();
		}
	}

	//配置副本策略
	let step3Valid = function () {
		$('.transfer-strategy-show').empty();
		$('.high_strategy_show').empty();
		$('.backupTypeShow').empty();
		let configStrategy = $('#backupStrategyDiv').getBackupStrategy();
		//重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({}, 'value');
		if (!data.retry_strategy) {
			return false;
		}
		data.strategyInfo.reserveStrategy = configStrategy.reserve;
		data.strategyInfo.speedStrategy = configStrategy.speedlimit;
		//检查 限速策略 、保留策略 、归档 、传输策略 、高级策略 、副本类型 、副本方式信息
		let result = getTransferInfo() && getCopyTypeInfo() && getTimeInfo();
		if (result) {
			result = result && showStep3();
		}
		//获取WORM配置
		//获取是否打开
		let wormConfig = "";
		if (data.strategyInfo.nodeInfo.storage_worm_config.flag && CONF.FUNCTIONS.includes('worm')) {
			wormConfig = $('#wormConfig').getWormProtectionSettings();
		}
		data.strategyInfo.safe_strategy = safeData(wormConfig, '', '');
		let safeInfo = '';
		safeInfo = LANG.UI_SAFE_STRATEGY_WORM_PROTECT + ": " + getSwitchDes(data.strategyInfo.safe_strategy.worm_flag);
		if (data.strategyInfo.safe_strategy.worm_flag) {
			safeInfo += '<br>' + LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD + ": " + data.strategyInfo.safe_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY;
		}
		$('.safeStrategyShow').html(safeInfo);
		// 获取合并模式配置
		data.strategyInfo.storageStrategy.storageInfo.data_container_size = parseInt($('#data_container_size').val());
		if (data.module_type == CONF.MODULE_TYPE.OS && data.sub_module_type == 0) {
		} else {
			$('.high_strategy_show').append($('.datacontainersizelabel').html() + ": " + $('#data_container_size option:selected').text() + `<br>`);
		}
		return result;
	}

	//得到副本类型和数据链长度
	let getCopyTypeInfo = function () {
		data.copyItemInfo.copy_mode = 2;//副本类型
		data.copyItemInfo.chain_length = 1;//数据链长度
		if (data.copyItemInfo.chainLength == 0) {
			UIToastr.showWarning(LANG.UI_ARCHIVE_CREATE, LANG.UI_COPY_CHAIN_LENGTH_TIP);
			return false;
		}
		return true;
	}
	//得到传输策略
	let getTransferInfo = function () {
		let networkNode = no_transport_flag ? {} : $('#transferNetworkTree').transferNetwork('getSelect');
		if (false === networkNode) {
			return false;
		}
		data.strategyInfo.transferStrategy.network = networkNode.network_uuid ?? '';
		data.strategyInfo.transferStrategy.network_pool_uuid = networkNode.network_pool_uuid ?? '';
		//传输网络
		data.strategyInfo.transferStrategy.transferNet = networkNode.network_ip ?? '';
		//端口
		data.strategyInfo.transferStrategy.transferPort = networkNode.network_port ?? '';
		data.strategyInfo.transferStrategy.inc_mode = 1;//源端增量(哈希计算，仅合并模式副本)
		data.strategyInfo.transferStrategy.wan_accelerate_flag = false;//广域网加速功能  未启用
		const transportPlugin = $('.tabTransferConfig').data('myFormGroup');
		let result = transportPlugin.getFormGroupConfig({ tapeFlag: tapeFlag });
		if (result) {
			data.strategyInfo.transferStrategy = { ...data.strategyInfo.transferStrategy, ...result }
		}
		data.strategyInfo.transferStrategy.encryptData = false;

		// 传输网络
		if (!no_transport_flag) {
			let transFerDes = LANG.UI_NODE_NETWORK_TRANSFER + LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON + (data.strategyInfo.transferStrategy.transferNet == '' ? networkNode.str : networkNode.network_ip) + "<br>";
			$('.transfer-strategy-show').append(transFerDes);
		}
		return true;
	}

	//得到副本方式  时间策略、一次性
	let getTimeInfo = function () {
		const timePlugin = $('.copy-strategy-config').data('copyStrategy');
		let result = timePlugin.getStrategyConfig();
		data.strategyInfo.timeStrategy.type = result.type;
		data.strategyInfo.timeStrategy.copyStrategy = result.strategy_info;
		data.strategyInfo.timeStrategy.startTime = result.start_time;
		return result.flag;
	}

	let showStep3 = function () {
		//保留策略
		// 一次性不显示保留策略
		if (data.strategyInfo.timeStrategy.type == 'oncetime' || tapeFlag) {
			$('#reserveStrategyDiv').hide();
		} else {
			$('#reserveStrategyDiv').show();
		}
		//保留策略		
		if (!tapeFlag) {
			$('.reserveStrategyShow').html(data.strategyInfo.reserveStrategy.des);
		} else {
			data.strategyInfo.reserveStrategy.reserveInfo.enable_flag = false;
		}
		//限速策略
		let speedLimitShow = $('.speed_limit_show');
		let speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		if (data.strategyInfo.speedStrategy.speedInfo.length) {
			speedLimitsStr = '';
			for (let i = 0; i < data.strategyInfo.speedStrategy.speedInfo.length; i++) {
				speedLimitsStr += data.strategyInfo.speedStrategy.speedInfo[i].des + '<br>';
			}
		}
		speedLimitShow.html(speedLimitsStr);
		data.strategyInfo.highInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		$('.high_strategy_show').append($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.strategyInfo.highInfo.ignore_resource_limiting_flag) + '<br>');
	}

	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function (check) {
		if (check) {
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

	// --------------确认提交----------------

	//提交
	let submit = function () {
		let jobName = $('#jobName').val();
		if (!customInputValidate('string', jobName)) {
			return false;
		}
		if ('' == $.trim(jobName)) {
			$('.job_name_tip').html(LANG.UI_MACHINE_OS_JOB_NAME_TIPS).show();
			return;
		}
		$('.job_name_tip').hide();
		data.taskName = $.trim(jobName);
		data.node_uuid = $('#targetNode').val();
		// 一次性不设置保留策略 值为零
		if (data.strategyInfo.timeStrategy.type == 'oncetime') {
			data.strategyInfo.reserveStrategy.value = 0;
		}
		let createJob = function (res) {
			Metronic.unblockUI('#archiveContent');
			if (operateResponseList(res)) {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}
		};
		let submitParams = deepCloneObject(data);
		// 镜像副本传时间点列表
		if (data.copyItemInfo.copy_mode == 1) {
			for (let i = 0; i < submitParams.copyItemInfo.copy_list.length; i++) {
				submitParams.copyItemInfo.copy_list[i].archive_timepoint_uuid = '';
			}
		};
		// 合并副本传时间点
		if (data.copyItemInfo.copy_mode == 2) {
			for (let i = 0; i < submitParams.copyItemInfo.copy_list.length; i++) {
				submitParams.copyItemInfo.copy_list[i].timepoint_uuid_list = [];
			}
		};
		submitParams.archive_flag = true;
		Metronic.blockUI({ target: '#archiveContent', animate: true });
		pAjaxRequest(deepCloneObject(submitParams), "/api/v1/copy", "post", createJob, true);
	}
	// 初始化传输策略配置项
	const initTransportConfig = (data = null) => {
		$('.tabTransferConfig').initFormGroupLine({ showDesDom: $('.transfer-strategy-show') });
		const transportPlugin = $('.tabTransferConfig').data('myFormGroup');
		let transport_config = ENCRYPT_METHOD_CONFIG;
		// 英文版屏蔽SM2加密方式
		if (CONF.LANGUAGE == 'en-us') {
			delete transport_config[2];
		}
		// 传输加密配置
		transportPlugin.initSwitchSelectMixed({
			id: 'encrypt',
			labelSwitch: LANG.UI_COPY_BACK_ENCRYPT,
			labelSelect: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD,
			options: transport_config,
			tips: LANG.UI_COPY_SSL_ENCRYPT_TIPS,
			defaultFlag: data ? data.bts.encrypt : true,
			defaultValue: data ? data.bts.encrypt_method : 1,
		});
		// 传输压缩配置
		// #28678 仅异地存储为目标存储显示压缩  归档都是本地存储所以不支持
		// transportPlugin.initSwitchSelectMixed({
		// 	id: 'compress',
		// 	labelSwitch: LANG.UI_COPY_BACK_COMPRESS,
		// 	labelSelect: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE,
		// 	options: COMPRESS_METHOD_CONFIG,
		// 	tips: LANG.UI_COPY_TRANSPORT_COMPRESS_TIPS,
		// 	defaultFlag: data ? data.bts.compress : true,
		// 	defaultValue: data ? data.bts.compress_method : 1,
		// });
		// 传输线程配置
		transportPlugin.initNumber({
			id: 'threadNum',
			label: LANG.UI_GLOBAL_STRATEGY_THREAD_NUM,
			value: data ? data.bts.thread_num : 3,
			min: 1,
			max: 8,
			step: 1,
			tips: LANG.UI_COPY_TRANSPORT_THREAD_NUM_TIPS,
		});
	}

	return {
		//main function to initiate the module_type
		init: function () {
			initStrategy();       //初始化时间策略
			initStepOne();           //初始化备份流程数据
			wizardInit();         //初始化步骤
			initListener();       //监听事件
		}
	};

}();

jQuery(document).ready(function () {
	addArchive.init();
});