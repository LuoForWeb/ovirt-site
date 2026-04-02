var archiveBack = function () {
	let tapeFlag = false;
	const MODULE_TYPE = parseInt($('#module_type').val());
	const SUB_MODULE_TYPE = parseInt($('#sub_module_type').val());
	const defaultConfig = {
		time: false,
		store: false,
		reserve: false,
	};
	let data = {
		copyItemInfo: {
			auto_add_flag: false,
			copy_ignore_object_list: [],
		},
		strategyInfo: {
			timeStrategy: {
				type: 'oncetime',
				archiveStrategy: {},
				startTime: '',
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
				compress: false,
				compress_method: 0,
			},
			nodeInfo: {},
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
		module_type: MODULE_TYPE,
		sub_module_type: SUB_MODULE_TYPE,
		copy_last_chain_flag: false,
	};
	// 传输加密算法配置
	const ENCRYPT_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA, 2: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM };
	// 压缩等级配置
	const COMPRESS_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST, 2: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL, 3: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER, 4: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST };
	let no_transport_flag = false;

	let initData = function () {
		// 隐藏gfs
		$('.GFSdiv').hide();
		$('#reserveType').prop('disabled', true);
		// 重试策略
		$('#retry_config').retryStrategy({ module_type: 'copy' });
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
	}

	// ---------------回传源-------------

	let step1Valid = function () {
		// 获取选择的副本源信息
		const plugin = $('.copy-source-div').data('mySource');
		let result = plugin.getCopySource();
		data.copyItemInfo.copy_list = result.copy_list;
		data.data_type = parseInt($('#dataType').val());	//选择副本数据类型
		// 异常退出
		if (result.stopFlag) {
			return false;
		}
		$(".selectCopyTip").hide();
		//在第四步展示源信息
		showStep1();
		$('#backupTarget').backupTarget({ exclude_storage_type_list: [CONF.BD_STORAGE_TYPE.CLOUD, CONF.BD_STORAGE_TYPE.REMOTE] });
		return true;
	}

	let showStep1 = function () {
		//任务名获取
		let initName = function (res) {
			if (res.success) {
				$('#jobName').val(res.data);
			};
		};
		pAjaxRequest({ archive_back_flag: true }, "/api/v1/copy/jobs/name", "GET", initName, true);
	}

	// ------------------------归档目的地--------------

	let step2Valid = function () {
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		// 节点信息
		let results = getNodeInfo();
		let mode = $('#targetStorage option:selected').data('type');//本地存储不显示目标节点
		if (mode == CONF.BD_STORAGE_TYPE.REMOTE || mode == CONF.BD_STORAGE_TYPE.CLOUD || mode == CONF.BD_STORAGE_TYPE.HUAWEI_CBR) {
			$('.targetNodeDiv').show();
			$('.calculateNodeShowDiv').show();
		} else {
			$('.targetNodeDiv').hide();
			$('.calculateNodeShowDiv').hide();
		}
		if (mode == CONF.BD_STORAGE_TYPE.TAPE) {
			tapeFlag = true;
			$('.threadNum-number-div').hide();
		} else {
			$('.threadNum-number-div').show();
			tapeFlag = false;
		}
		// 卷不显示 存储策略
		if (data.module_type == CONF.MODULE_TYPE.OS && data.sub_module_type == 0) {
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
			$('#transferNetworkTree').transferNetwork({ node_uuid: backupTargetInfo.node_uuid, storage_uuid: backupTargetInfo.storage_uuid, remote_flag: false });
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

	// --------------归档方式-------------

	// ------------确认配置----------------	
	let step3Valid = function () {
		//重试策略
		let configStrategy = $('#backupStrategyDiv').getBackupStrategy();
		data.strategyInfo.speedStrategy = configStrategy.speedlimit;
		data.copyItemInfo.copy_mode = 1;//副本类型
		data.copyItemInfo.chain_length = 3;//数据链长度
		data.retry_strategy = $('#retry_config').retryStrategy({}, 'value');
		if (!data.retry_strategy) {
			return false;
		}
		//检查 限速策略 、保留策略 、归档 、传输策略 、高级策略 、副本类型 、副本方式信息
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
		let safeInfo = '';
		safeInfo = LANG.UI_SAFE_STRATEGY_WORM_PROTECT + ": " + getSwitchDes(data.strategyInfo.safe_strategy.worm_flag);
		if (data.strategyInfo.safe_strategy.worm_flag) {
			safeInfo += '<br>' + LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD + ": " + data.strategyInfo.safe_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY;
		}
		$('.safeStrategyShow').html(safeInfo);
		// 获取合并模式配置
		data.strategyInfo.storageStrategy.storageInfo.redundant_data_proportion = 50;
		data.strategyInfo.storageStrategy.storageInfo.data_container_size = parseInt($('#data_container_size').val());
		if (data.module_type == CONF.MODULE_TYPE.OS && data.sub_module_type == 0) {
		} else {
			$('.high_strategy_show').append($('.datacontainersizelabel').html() + ": " + $('#data_container_size option:selected').text() + `<br>`);
		}
		return result;
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
		if (!no_transport_flag) {
			let transFerDes = LANG.UI_NODE_NETWORK_TRANSFER + LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON + (data.strategyInfo.transferStrategy.transferNet == '' ? networkNode.str : networkNode.network_ip) + "<br>";
			$('.transfer-strategy-show').append(transFerDes);
		}
		return true;
	}

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

	let wizardInit = function () {
		if (!jQuery().bootstrapWizard) {
			return;
		}
		let form = $('#submit_form');
		let error = $('.alert-danger', form);
		let success = $('.alert-success', form);
		let handleTitle = function (tab, navigation, index) {
			let total = navigation.find('li').length;
			let current = index + 1;
			// set wizard title
			//            $('.step-title', $('#archiveBackContent')).text('Step ' + (index + 1) + ' of ' + total);
			// set done steps
			jQuery('li', $('#archiveBackContent')).removeClass("done");
			let li_list = navigation.find('li');
			for (let i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}

			if (current == 1) {
				$('#archiveBackContent').find('.button-previous').css('visibility', 'hidden');
				$('#archiveBackContent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#archiveBackContent').find('.button-previous').css('visibility', 'visible');
				$('#archiveBackContent').find('.button-next').removeClass('next-btn-margin-left');
			}

			if (current >= total) {
				$('#archiveBackContent').find('.button-next').hide();
				$('#archiveBackContent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#archiveBackContent').find('.button-next').show();
				$('#archiveBackContent').find('.button-submit').css('visibility', 'hidden');
			}
			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#archiveBackContent').bootstrapWizard({
			'nextSelector': '.button-next',
			'previousSelector': '.button-previous',
			onTabClick: function (tab, navigation, index, clickedIndex) {
				return false;
			},
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
				$('#archiveBackContent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});

		$('#archiveBackContent').find('.button-previous').css('visibility', 'hidden');
		$('#archiveBackContent .button-submit').click(submit).css('visibility', 'hidden');
	};

	//得到开关的结果描述   开启/关闭
	let getSwitchDes = function (check) {
		if (check) {
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

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
		Metronic.blockUI({ target: '#archiveBackContent', animate: true, cenrerY: true, });
		//TODO提交
		let archiveBackJob = function (res) {
			Metronic.unblockUI('#archiveBackContent');
			if (operateResponseList(res)) {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}
		};
		data.archive_flag = true;
		pAjaxRequest(deepCloneObject(data), "/api/v1/copy/back", "POST", archiveBackJob, true);
	}
	// 初始化副本源
	let initCopySource = function () {
		// 未授权不显示worm
		if (!CONF.FUNCTIONS.includes('worm')) {
			$('#sateLi').hide();
			$('.safeDiv').hide();
		}
		// 初始化传输策略
		initTransportConfig();
		// 初始化副本源
		$('.copy-source-div').mySource({ module_type: MODULE_TYPE, sub_module_type: SUB_MODULE_TYPE, archive_back_flag: true, showDom: $('.copy-source-list-div') });
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
		// #28678 仅异地存储为目标存储显示压缩 归档回传也都是本地存储所以不支持
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

	// 初始化监听事件
	let initListener = function () {
		// 跳转到其他副本创建页面
		$('#toBackup').on('click', function () {
			LOCATION(`./content/archive/addarchive.php?module_type=${MODULE_TYPE}&sub_module_type=${SUB_MODULE_TYPE}`, 'archive_new');
		});
	}
	return {
		//main function to initiate the module_type
		init: function () {
			wizardInit();
			initCopySource();
			initData();
			initListener();
		},
	};
}();

jQuery(document).ready(function () {
	archiveBack.init();
});