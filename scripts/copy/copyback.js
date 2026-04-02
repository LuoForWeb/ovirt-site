var CopyBack = function () {
	let tapeFlag = false; //磁带标志
	let remoteSource = false;
	const MODULE_TYPE = parseInt($('#module_type').val());
	const SUB_MODULE_TYPE = parseInt($('#sub_module_type').val());
	// 异地存储类型数组
	const REMOTE_STORAGE_TYPE = [CONF.BD_STORAGE_TYPE.REMOTE];
	// 树节点类型
	const TREE_NODE_TYPE = {
		TASK: 'task',
		HOST: 'host',
		POINT: 'point',
		INSTANCE: 'instance',
		DB_COPY: 'db_copy',
		TIME_POINT: 'time_point',
	}
	// 传输加密算法配置
	const ENCRYPT_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA, 2: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM };
	// 压缩等级配置
	const COMPRESS_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST, 2: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL, 3: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER, 4: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST };
	// 初始化回传消息结构
	let data = {
		copyItemInfo: {
			auto_add_flag: false,
			copy_ignore_object_list: [],
		},
		strategyInfo: {
			timeStrategy: {
				type: 'oncetime',
				copyStrategy: {},
				startTime: ''
			},
			reserveStrategy: {
				reserveInfo: {
					type: 1,
					value: 65535,
					enable_flag: false,
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
				real_storage_info: {},
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
		copy_last_chain_flag: false,
	}; //定义传递到服务端需要的参数对象

	// 策略默认配置
	const defaultConfig = {
		time: false,
		store: false,
		reserve: false,
	};
	let no_transport_flag = false;
	// 合并副本的类型
	const SHOW_COPY_MODE_COMB = [CONF.MODULE_TYPE.OS, CONF.MODULE_TYPE.VM, CONF.MODULE_TYPE.PUBLIC_CLOUD, CONF.MODULE_TYPE.PRIVATE_CLOUD];
	// --------归档回传源-------------

	//初始化副本回传源
	let initCopyBack = function () {
		// 重试策略
		$('#retry_config').retryStrategy({ module_type: 'copy' });
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
		// 初始化传输策略
		initTransportConfig();
	}


	// 初始化时间点展示方式和事件
	let initCopySource = function () {
		let data_type = $('#dataType').val(); //数据类型 任务|时间点
		let remoteFlag = false;
		if (data_type == 4 && REMOTE_STORAGE_TYPE.includes(storageType)) {
			remoteFlag = true;
		}
		$('.copy-source-div').mySource({ module_type: MODULE_TYPE, sub_module_type: SUB_MODULE_TYPE, remoteFlag: remoteFlag, copy_back: true, showDom: $('.copy-source-list-div') });
		getTaskName();
		// 未授权不显示worm
		if (!CONF.FUNCTIONS.includes('worm')) {
			$('#sateLi').hide();
			$('.safeDiv').hide();
		}
	}

	let step1Valid = function () {
		data.data_type = parseInt($('#dataType').val());	//选择副本数据类型
		if (Number.isNaN(data.module_type)) {
			data.module_type = MODULE_TYPE;
		}
		let type = $('#storage option:selected').data('type');
		// 构建参数对象
		let param = { exclude_storage_type_list: [CONF.BD_STORAGE_TYPE.CLOUD, CONF.BD_STORAGE_TYPE.REMOTE] };
		// 磁带不能副本到磁带
		if (type === CONF.BD_STORAGE_TYPE.TAPE) {
			param.exclude_storage_type_list = [CONF.BD_STORAGE_TYPE.CLOUD, CONF.BD_STORAGE_TYPE.REMOTE, CONF.BD_STORAGE_TYPE.TAPE];
		}
		$('#backupTarget').backupTarget(param); // 初始化目标存储
		return setCopyBackInfo();
	}
	// 获取回传源
	let setCopyBackInfo = function () {
		// 获取选择回传源的信息
		const plugin = $('.copy-source-div').data('mySource');
		let result = plugin.getCopySource();
		data.copyItemInfo.copy_list = result.copy_list;
		// 异常退出
		if (result.stopFlag) {
			return false;
		}
		// 判断是否是异地源
		let type = $('#storage option:selected').data('type');
		if (type == CONF.BD_STORAGE_TYPE.REMOTE) {
			remoteSource = true;
		} else {
			remoteSource = false;
		}
		return true;
	}

	// -----------归档回传目标--------------
	//选择副本目的地
	let step2Valid = function () {
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		// 节点信息
		let results = getNodeInfo();
		let mode = $('#targetStorage option:selected').data('type');//本地存储不显示目标节点
		let mode2 = $('#storage option:selected').data('type');//本地存储不显示目标节点
		if (mode == CONF.BD_STORAGE_TYPE.TAPE || mode2 == CONF.BD_STORAGE_TYPE.TAPE) {
			tapeFlag = true;
			$('.threadNum-number-div').hide();
			$('#threadNum').val(1);
		} else {
			$('.threadNum-number-div').show();
			tapeFlag = false;
		}
		// 卷不显示 存储策略
		if ((data.module_type == CONF.MODULE_TYPE.OS && data.sub_module_type == 0) || !SHOW_COPY_MODE_COMB.includes(data.module_type)) {
			$('.merge-mode-tab').hide()
		}
		return results;
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
			if (remoteSource) {
				// 初始化传输网络
				$('#transferNetworkTree').transferNetwork({ node_uuid: backupTargetInfo.node_uuid, storage_uuid: $('#storage').val(), remote_flag: remoteSource });
			} else {
				// 初始化传输网络
				$('#transferNetworkTree').transferNetwork({ node_uuid: backupTargetInfo.node_uuid, storage_uuid: backupTargetInfo.storage_uuid, remote_flag: remoteSource });
			}
			$('.transferNet').show();
		} else {
			$('.transferNet').hide();
			no_transport_flag = true;
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
				$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
			}
		}
		// 磁带存储,屏蔽安全策略
		if (backupTargetInfo.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
			$('#sateLi').hide();
			$('.safeDiv').hide();
		}
		return true;
	}

	// 获取默认任务名
	let getTaskName = function () {
		let initName = function (res) {
			$('#jobName').val(res.data);
		};
		pAjaxRequest({ copy_back_flag: true }, "/api/v1/copy/jobs/name", "GET", initName, true);
	}

	// ----------副本回传方式-------------

	//配置副本策略
	let step3Valid = function () {
		$('.transfer-strategy-show').empty();
		let configStrategy = $('#backupStrategyDiv').getBackupStrategy();
		data.strategyInfo.speedStrategy = configStrategy.speedlimit;
		//重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({}, 'value');
		if (!data.retry_strategy) {
			return false;
		}
		data.copyItemInfo.copy_mode = 1;//副本类型
		data.copyItemInfo.chain_length = 3;//数据链长度
		//检查传输策略 副本类型 副本方式信息
		let result = getTransferInfo();
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
		if (result) {
			result = result && showStep3();
		}

		let safeInfo = '';
		safeInfo = LANG.UI_SAFE_STRATEGY_WORM_PROTECT + ": " + getSwitchDes(data.strategyInfo.safe_strategy.worm_flag);
		if (data.strategyInfo.safe_strategy.worm_flag) {
			safeInfo += '<br>' + LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD + ": " + data.strategyInfo.safe_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY;
		}
		$('.safeStrategyShow').html(safeInfo);
		return result;
	}

	//得到传输策略
	let getTransferInfo = function () {
		let strategyInfo = data.strategyInfo.transferStrategy;
		let networkNode = no_transport_flag ? {} : $('#transferNetworkTree').transferNetwork('getSelect');
		if (false === networkNode) {
			return false;
		}
		strategyInfo.network = networkNode.network_uuid ?? '';
		strategyInfo.network_pool_uuid = networkNode.network_pool_uuid ?? '';
		//传输网络
		strategyInfo.transferNet = networkNode.network_ip ?? '';
		//端口
		strategyInfo.transferPort = networkNode.network_port ?? '';
		strategyInfo.inc_mode = 1;//源端增量(哈希计算，仅合并模式副本)
		strategyInfo.wan_accelerate_flag = false;//广域网加速功能  未启用
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

	//添加副本策略信息到确认配置页面
	let showStep3 = function () {
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
		// 高级配置
		let highInfo = '';
		// 过载保护
		data.strategyInfo.highInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		highInfo += $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.strategyInfo.highInfo.ignore_resource_limiting_flag) + '<br>';
		$('.high_strategy_show').html(highInfo);
	}

	//得到开关的结果描述   开启/关闭
	let getSwitchDes = function (check) {
		if (check) {
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	// new----------------------------------------------------------------

	//创建任务步骤
	let wizardInit = function () {
		if (!jQuery().bootstrapWizard) {
			return;
		}
		let form = $('#submitForm');
		let error = $('.alert-danger', form);
		let success = $('.alert-success', form);
		let handleTitle = function (tab, navigation, index) {
			let total = navigation.find('li').length;
			let current = index + 1;
			jQuery('li', $('#copyBackContent')).removeClass("done");
			let li_list = navigation.find('li');
			for (let i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}
			// 第一步不现实上一步
			if (current == 1) {
				$('#copyBackContent').find('.button-previous').css('visibility', 'hidden');
				$('#copyBackContent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#copyBackContent').find('.button-previous').css('visibility', 'visible');
				$('#copyBackContent').find('.button-next').removeClass('next-btn-margin-left');
			}

			// 最后一步不现实下一步
			if (current >= total) {
				$('#copyBackContent').find('.button-next').hide();
				$('#copyBackContent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#copyBackContent').find('.button-next').show();
				$('#copyBackContent').find('.button-submit').css('visibility', 'hidden');
			}
			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#copyBackContent').bootstrapWizard({
			'nextSelector': '.button-next',
			'previousSelector': '.button-previous',
			onTabClick: function (tab, navigation, index, clickedIndex) {
				return false;
			},
			//点击下一步检查信息
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
			onPrevious: function (tab, navigation, index) {
				success.hide();
				error.hide();

				handleTitle(tab, navigation, index);
			},
			onTabShow: function (tab, navigation, index) {
				let total = navigation.find('li').length;
				let current = index + 1;
				let $percent = (current / total) * 100;
				$('#copyBackContent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});

		$('#copyBackContent').find('.button-previous').css('visibility', 'hidden');
		$('#copyBackContent .button-submit').click(submit).css('visibility', 'hidden');
	};

	// 初始化监听事件
	let initListener = function () {
		// 跳转到其他副本创建页面
		$('#toBackup').on('click', function () {
			LOCATION(`./content/copy/copy.php?module_type=${MODULE_TYPE}&sub_module_type=${SUB_MODULE_TYPE}`, 'copy_protect');
		});
	}

	// 提交任务创建
	let submit = function () {
		let jobName = $('#jobName').val();
		// 检查名字
		if (!customInputValidate('string', jobName)) {
			return false
		}
		if ('' == $.trim(jobName)) {
			$('.job_name_tip').html(LANG.UI_MACHINE_OS_JOB_NAME_TIPS).show();
			return;
		}
		$('.job_name_tip').hide();
		data.remoteSource = remoteSource;
		data.taskName = $.trim(jobName);
		Metronic.blockUI({ target: '#copyBackContent', animate: true, cenrerY: true, });
		let newJobBack = function (res) {
			Metronic.unblockUI('#copyBackContent');
			if (operateResponseList(res)) {
				LOCATION('./content/platform/jobs/jobs.php', TREE_NODE_TYPE.TASK);
			}
		};
		//TODO提交
		pAjaxRequest(deepCloneObject(data), "/api/v1/copy/back", "POST", newJobBack, true);
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
		// #28678 回传不支持压缩
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
			wizardInit();
			initCopyBack();
			initCopySource();
			initListener();
		},
	};
}();

jQuery(document).ready(function () {
	CopyBack.init();
});