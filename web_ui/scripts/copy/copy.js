var DataCopy = function () {
	const MODULE_TYPE = parseInt($('#module_type').val());
	const SUB_MODULE_TYPE = parseInt($('#sub_module_type').val());
	let cloudFlag = false; // 云存储标志
	let SETTINGS = null;
	let tapeFlag = false; //磁带存储标志
	let tapeSource = false; //磁带标志
	let showIncModeFlag = false; // 显示对比增量标志
	let customSourceShowFlag = false; // 自定义副本源标志
	let p_incr_flag = false; // 永久增量标志
	let specil_sub_type_flag = false; // #28339 取消永久增量+镜像副本上云组合 特殊子类型标志，目前用去区分mongodb
	// 策略默认配置
	const defaultConfig = {
		time: false,
		store: false,
		reserveMode: true,
		reserve_num_only_flag: true,
		copy_flag: true,
	};
	// 数据链类型枚举
	const CHAIN_TYPE = {
		NUM: 'num',
		UNLIMITED: 'unlimited'
	};
	// 副本源类型枚举
	const COPY_SOURCE = {
		BACKUP_TASK: 1,
		BACKUP_DATA: 2,
		COPY_TASK: 3,
		COPY_DATA: 4,
	}
	// 合并副本的类型
	const SHOW_COPY_MODE_COMB = [CONF.MODULE_TYPE.OS, CONF.MODULE_TYPE.VM, CONF.MODULE_TYPE.PUBLIC_CLOUD, CONF.MODULE_TYPE.PRIVATE_CLOUD];
	// 保留类型枚举
	const RESERVE_MODE = {
		POINT: 1,
		CHIAN: 2
	}
	// 副本类型枚举
	const COPY_MODE = {
		MIRROR: 1,
		COMB: 2
	}
	// 时间策略
	const BACKUP_TYPE = {
		STRATEGY: 'strategy',
		ONCE: 'oncetime'
	}
	// 95535表示无限制
	const UNLIMITED_NUM = 65535;
	// 副本归档任务类型
	const COPY_TASK_TYPE = [CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.BACKUP_COPY_FETCH, CONF.TASK_TYPE.ARCHIVE, CONF.TASK_TYPE.ARCHIVE_FETCH];
	// 修改副本任务标志
	let editFlag = false;
	// 初始化创建消息结构
	let data = {
		copyItemInfo: {
			auto_add_flag: false,
			copy_ignore_object_list: [],
		},
		strategyInfo: {
			timeStrategy: {
				type: BACKUP_TYPE.STRATEGY,
				copyStrategy: {},
				startTime: ''
			},
			reserveStrategy: {
				reserveInfo: {}
			},
			transferStrategy: {
				transferNet: '',
			},
			nodeInfo: {
				storage_uuid: '',
				node_uuid: '',
				real_storage_info: {},
			},
			highInfo: {},
			storageStrategy: {
				storageInfo: {
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
	// 传输加密算法配置
	const ENCRYPT_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA, 2: LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM };
	// 对比增量配置
	const INC_MODE_CONFIG = { 1: LANG.UI_COPY_TARGET_INC_MODE1, 2: LANG.UI_COPY_TARGET_INC_MODE2 };
	// 压缩等级配置
	const COMPRESS_METHOD_CONFIG = { 1: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST, 2: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL, 3: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER, 4: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST };
	// 副本类型配置
	const COPY_MODE_CONFIG = { 1: LANG.UI_COPY_MODE_MIRROR, 2: LANG.UI_COPY_MODE_COMBINATION };
	// 数据链长度配置
	const CHAIN_TYPE_CONFIG = { 'num': LANG.UI_COPY_CHAIN_TYPE_NUM, 'unlimited': LANG.UI_COPY_CHAIN_TYPE_UNLIMITED };
	let strategy_type = 'strategy';
	let no_transport_flag = false;

	// 修改先暂存旧的数据
	let initOldData = () => {
		// 副本对象列表
		data.copyItemInfo.copy_list = SETTINGS.copy_list;
		// 副本类型
		data.copyItemInfo.copy_mode = SETTINGS.copy_mode;
		// 数据链长度
		data.copyItemInfo.chain_length = SETTINGS.chain_length;
		data.module_type = SETTINGS.module_type;
		//备份方式:策略/时间
		data.strategyInfo.timeStrategy.type = SETTINGS.time_strategy.type;
		strategy_type = SETTINGS.time_strategy.type;
		data.copy_last_chain_flag = SETTINGS.copy_last_chain_flag;

		//按时间备份的时间
		if (SETTINGS.time_strategy.type == BACKUP_TYPE.ONCE) {
			data.strategyInfo.timeStrategy.startTime = SETTINGS.time_strategy.data;
		} else {
			var info = {};
			var time_strategy = SETTINGS.time_strategy.data;
			for (var i = 0; i < time_strategy.length; i++) {
				var days = [];
				for (var j = 0; j < time_strategy[i].days.length; j++) {
					if (time_strategy[i].days.length == 1 && time_strategy[i].days[j] == false) {
						days = [];
					} else if (time_strategy[i].days[j] == true) {
						days = [...days, ...[1]]
					} else if (time_strategy[i].days[j] == false) {
						days = [...days, ...[0]]
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
			data.strategyInfo.timeStrategy.copyStrategy = info;
		}
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

		// 判断原来的存储是否为云存储
		if (SETTINGS.node.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
			cloudFlag = true;
		}
	}

	//初始化第一步任务信息
	let initStep1Settings = function () {
		Metronic.blockUI({ target: '.src-wrap__content', animate: true });

		// 判断副本源是本地还是异地  根据源任务存储判断
		// 1.异地
		if (SETTINGS.node.source_storage_type == CONF.BD_STORAGE_TYPE.REMOTE && (SETTINGS.copy_list[0].time_point_uuids.length != 0 || SETTINGS.copy_list[0].archive_timepoint_uuid)) {
			$('#dataType').val(COPY_SOURCE.COPY_DATA).prop('disabled', true);
			initCopySource(true)
		} else {
			// 2.本地
			// 初始化原始副本源树
			setLocalOldInfo();
		}
	}

	// 初始化原始副本源树
	let setLocalOldInfo = function () {
		// 判断是否是以时间点副本
		let time_point_uuids = SETTINGS.copy_list[0].time_point_uuids;
		let archive_uuid = SETTINGS.copy_list[0].archive_timepoint_uuid;
		let data_type = COPY_SOURCE.BACKUP_TASK;
		if (time_point_uuids.length != 0 || archive_uuid) {
			data_type = COPY_SOURCE.COPY_DATA;
		}
		// 获取副本源类型并初始化
		let taskTypeBack = function (res) {
			if (res.success) {
				let source_task_type = res.data.task_type;
				if (COPY_TASK_TYPE.includes(source_task_type)) {
					if (time_point_uuids.length == 0 && !archive_uuid) {
						data_type = COPY_SOURCE.COPY_TASK;
					} else {
						data_type = COPY_SOURCE.COPY_DATA;
					}
				} else {
					if (time_point_uuids.length != 0 || archive_uuid) {
						data_type = COPY_SOURCE.BACKUP_DATA;
					}
				}
				$('#dataType').val(data_type).prop("disabled", true);;
				$('#copySourceTree').hide();
				$('.copyList').hide();
			}
			initCopySource();
		}
		pAjaxRequest({ source_task_uuid: SETTINGS.source_task_uuid, data_type: data_type }, "/api/v1/copy/resources/type", "GET", taskTypeBack, true);

	}
	// 初始化目标存储和计算节点
	let initStep2Settings = () => {
		$('#backupTarget').backupTarget({
			node_uuid: SETTINGS.node.nodeuuid,
			node_pool_uuid: SETTINGS.node.node_pool_uuid,
			storage_uuid: SETTINGS.node.storageuuid,
			storage_pool_uuid: SETTINGS.node.storage_pool_uuid,
			storage_pool_type: SETTINGS.node.storage_pool_type,
		});
	};

	let initStep3Settings = function () {
		initTransportConfig(SETTINGS); // 初始化传输策略
		$('#copy_mode').val(SETTINGS.copy_mode).prop('disabled', true);// #28220 修改副本任务禁止修改副本类型
		// 根据副本类型显示数据链长度
		if (SETTINGS.copy_mode == COPY_MODE.MIRROR) {
			$('.chain_type-select-div').hide();
			$('.chain_length-number-div').hide();
			$('.inc_mode-select-div').hide();
		} else if (SETTINGS.copy_mode == COPY_MODE.COMB) {
			// 合并副本显示副本链长度
			$('.chain_type-select-div').show();
			$('.inc_mode-select-div').show();
			if (SETTINGS.chain_length == UNLIMITED_NUM) { //无限制
				$('#chain_type').val(CHAIN_TYPE.UNLIMITED);
			} else {
				$('#chain_type').val(CHAIN_TYPE.NUM);
				$('#chain_length').val(SETTINGS.chain_length);
				$('.chain_length-number-div').show();
			}
		}
		// 初始化时间策略
		initStrategy(SETTINGS.time_strategy);
		initHighConfig(SETTINGS); // 初始化高级配置
		//任务名
		$('#jobName').val(SETTINGS.task_name);
		//重试策略
		$('#retry_config').retryStrategy({ 'retry_strategy': SETTINGS.retry_strategy, module_type: 'copy' }, 'edit');
		// 副本gfs保留策略
		if (SETTINGS.copy_gfs_strategy) {
			if (SETTINGS.copy_gfs_strategy.copy_week_flag) {
				$('#copy_week_flag').iCheck('check');
			}
			if (SETTINGS.copy_gfs_strategy.copy_month_flag) {
				$('#copy_month_flag').iCheck('check');
			}
			if (SETTINGS.copy_gfs_strategy.copy_year_flag) {
				$('#copy_year_flag').iCheck('check');
			}
			if (SETTINGS.copy_gfs_strategy.copy_year_flag || SETTINGS.copy_gfs_strategy.copy_month_flag || SETTINGS.copy_gfs_strategy.copy_week_flag) {
				$('#copyGFSflag').iCheck('check');
			}
		}
		// 副本最新链
		if (SETTINGS.copy_last_chain_flag) {
			$('#copyNewestChain').iCheck('check');
		}
		if (SETTINGS.copy_gfs_strategy.copy_year_flag || SETTINGS.copy_gfs_strategy.copy_month_flag || SETTINGS.copy_gfs_strategy.copy_week_flag || SETTINGS.copy_last_chain_flag) {
			$('#customSource').bootstrapSwitch('state', true);
			customSourceShowFlag = true;
		}
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.ignore_resource_limiting_flag);
	}

	//备份任务步骤
	let wizardInit = function () {
		if (!jQuery().bootstrapWizard) {
			return;
		}
		let form = $('#submitForm');
		let error = $('.alert-danger', form);
		let success = $('.alert-success', form);
		let handleTitle = function (tab, navigation, index) {
			let total = navigation.find('li').length;//总共的步骤数
			let current = index + 1;      //当前步骤
			jQuery('li', $('#copyContent')).removeClass("done");
			let li_list = navigation.find('li');
			for (let i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}
			//如果第一步 上一步按钮隐藏
			if (current == 1) {
				$('#copyContent').find('.button-previous').css('visibility', 'hidden');
				$('#copyContent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#copyContent').find('.button-previous').css('visibility', 'visible');
				$('#copyContent').find('.button-next').removeClass('next-btn-margin-left');
			}
			//如果是最后一步 隐藏下一步
			if (current >= total) {
				$('#copyContent').find('.button-next').hide();
				$('#copyContent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#copyContent').find('.button-next').show();
				$('#copyContent').find('.button-submit').css('visibility', 'hidden');
			}
			Metronic.scrollTo($('.page-title'));
		}
		// default form wizard
		$('#copyContent').bootstrapWizard({
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
				$('#copyContent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});
		// 上一步，下一步
		$('#copyContent').find('.button-previous').css('visibility', 'hidden');
		$('#copyContent .button-submit').click(submit).css('visibility', 'hidden');
	};

	//初始化监听事件
	let initListener = function () {
		$('#toBackup').on('click', redirectToBackup);//跳转备份页面
		// 副本策略
		$('#backuptype').on('change', backupTypeHandler);//副本方式选择
		$("#copy_mode").on('change', copyModeChange);// 副本模式切换
		// 按点保留可以设置合并模式
		$('#reserveMode').on('change', function () {
			if (parseInt($(this).val()) == RESERVE_MODE.POINT && SHOW_COPY_MODE_COMB.includes(data.module_type)) {
				$('.merge-mode-tab').show();
			} else {
				$('.merge-mode-tab').hide();
				$('.advance-config-wrap .nav-tabs>li').removeClass('active');
				$('.advance-config-wrap .tab-content>.tab-pane').removeClass('active');
				$('.retry_config-tab').addClass('active');
				$('.retry_config_pane').addClass('active');
			}
		});
		$("#chain_type").on('change', chainTypeChange);// 数据链类型切换
		$('#chain_length').on('change', initCopyModeDes);
		$('#customSource').on('switchChange.bootstrapSwitch', customSourceChange);
		// 监听自定义副本源模式的radio变化
		$('.customSourceModeDiv .icheck').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
		});
		$('input[name="customSourceMode"]').on('ifChanged ifUnChanged', sourceModeClick);
	}
	// gfs改变事件
	let customSourceChange = function () {
		//如果勾选
		if (this.checked) {
			$(".source-conf-div").show();
		} else {
			$(".source-conf-div").hide();
		}
		initCopyModeDes();
	}
	// 自定义副本源配置
	let sourceModeClick = function (event) {
		let selectedValue = $(this).val();
		if (selectedValue == '1') {
			// 选中了"副本最新链"
			$('.copyGFSDiv').hide();
			// 执行相应逻辑
		} else if (selectedValue == '2') {
			// 选中了"GFS"
			if (event.target.checked) {
				$('.copyGFSDiv').show();
			} else {
				$('.copyGFSDiv').hide();
			}
		}
		initCopyModeDes();
	}
	/**
	 * 处理链类型变化的事件响应函数。
	 * 该函数根据选择的链类型，控制“数据链长度”部分的显示与隐藏，并初始化描述。
	 * 该函数不接受参数且没有返回值。
	 */
	let chainTypeChange = () => {
		const chainTypeDiv = $('#chain_type');
		const copyChainLengthDiv = $('.chain_length-number-div');
		if (chainTypeDiv.length && copyChainLengthDiv.length) {
			// 获取当前选中的链类型值
			let chain_type = chainTypeDiv.find(':selected').val();
			// 根据链类型的不同，显示或隐藏“复制链长”部分
			if (CHAIN_TYPE.NUM === chain_type) {
				copyChainLengthDiv.show();
			} else if (CHAIN_TYPE.UNLIMITED === chain_type) {
				copyChainLengthDiv.hide();
			}
			// 初始化复制模式的描述
			initCopyModeDes();
		}
		// 最后再次初始化描述，确保状态更新
		initCopyModeDes();
	};

	//第一步选择副本源
	let step1Valid = function () {
		let result = true;
		data.module_type = MODULE_TYPE;
		data.storage_uuid = $('#storage').val();//选择源存储
		data.data_type = parseInt($('#dataType').val());	//选择副本源类型
		//默认副本模块选择虚拟机
		if (Number.isNaN(data.module_type)) {
			data.module_type = CONF.BACKUP_COPY_MODULE.VM;
		}
		result = getCopyInfo();
		storageLimit();
		// 得到副本源
		return result;
	}
	// 处理磁带不能到磁带，异地不能到异地
	let storageLimit = () => {
		let type = $('#storage option:selected').data('type');
		// 构建参数对象
		let param = { exclude_storage_type_list: [] };
		// 磁带不能副本到磁带
		if (type === CONF.BD_STORAGE_TYPE.TAPE || tapeSource) {
			param.exclude_storage_type_list = [CONF.BD_STORAGE_TYPE.TAPE];
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

	// 得到副本源
	let getCopyInfo = function () {
		// 获取副本源的信息
		const plugin = $('.copy-source-div').data('mySource');
		let result = plugin.getCopySource();
		p_incr_flag = result.p_incr_flag;
		specil_sub_type_flag = result.specil_sub_type_flag;
		data.copyItemInfo.copy_list = result.copy_list;
		data.copyItemInfo.auto_add_flag = result.auto_add_flag;
		data.copyItemInfo.copy_ignore_object_list = result.copy_ignore_object_list;
		if (result.stopFlag) {
			return false;
		}
		tapeSource = result.tapeSource;
		// 未勾选副本源提示
		$(".selectCopyTip").hide();
		//如果是选择备份|副本数据做副本 只能做一次性副本 并且屏蔽保留策略
		if (parseInt($('#dataType').val()) == 2 || parseInt($('#dataType').val()) == 4) {
			strategy_type = "oncetime";
			if (editFlag) {
				initStrategy(SETTINGS.time_strategy);
			} else {
				initStrategy();
			}
			// 不显示保留策略配置和保留策略信息
			$('.reserve-strategy-form').hide();
			$('#reserveStrategyDiv').hide();
		} else {
			$('#reserveStrategyDiv').show();
			$('.reserve-strategy-form').show();
			$('#backuptype').removeAttr('disabled');
			if (editFlag) {
				if (SETTINGS.time_strategy.type == BACKUP_TYPE.ONCE) {
					$('.reserve-strategy-form').hide();
				}
			}
		}
		initReserveStrategyDes(); //初始化策略描述
		return true;
	}

	//没有副本源跳转到指定备份页面
	let redirectToBackup = function () {
		switch (MODULE_TYPE) {
			case CONF.MODULE_TYPE.VM://虚拟机
			case CONF.MODULE_TYPE.PRIVATE_CLOUD://虚拟机
			case CONF.MODULE_TYPE.PUBLIC_CLOUD://exchange
				if (SUB_MODULE_TYPE == CONF.VM_SUB_MODULE.VM) {
					LOCATION('./content/vm/vmbackup.php', 'vmprotect');
				}
				if (SUB_MODULE_TYPE == CONF.VM_SUB_MODULE.PRIVATE_CLOUD) {
					LOCATION('./content/vm/vmbackup.php?sub_module_type=2', 'prcloud_protect');
				}
				if (SUB_MODULE_TYPE == CONF.VM_SUB_MODULE.PUBLIC_CLOUD) {
					LOCATION('./content/aws/awsbackup.php', 'awsbackup');
				}
				break;
			case CONF.MODULE_TYPE.FS://文件
			case CONF.MODULE_TYPE.OBS://exchange
			case CONF.MODULE_TYPE.HADOOP://exchange
			case CONF.MODULE_TYPE.NAS://nas
				if (SUB_MODULE_TYPE == CONF.SUBMODULE_TYPE.FS) {
					LOCATION('./content/fs/filebackup.php', 'filebackup');
				}
				if (SUB_MODULE_TYPE == CONF.SUBMODULE_TYPE.NAS) {
					LOCATION('./content/nas/nasbackup.php', 'nas_protect');
				}
				if (SUB_MODULE_TYPE == CONF.SUBMODULE_TYPE.HADOOP) {
					LOCATION('./content/hadoop/hadoop_backup.php', 'hadoop_backup');
				}
				if (SUB_MODULE_TYPE == CONF.SUBMODULE_TYPE.OBS) {
					LOCATION('./content/s3/obsbackup.php', 'obs_protect');
				}
				break;
			case CONF.MODULE_TYPE.DB://数据库
				LOCATION('./content/dbprotect/dbbackup.php', 'db_backup');
				break;
			case CONF.MODULE_TYPE.OS://操作系统
				if (SUB_MODULE_TYPE == 1) { // 磁盘
					LOCATION('./content/complete_machine_os/machine_os_backup.php', 'complete_machine');

				} else {// 卷
					LOCATION('./content/os/osbackup.php', 'osbackup');
				}
				break;
			case CONF.MODULE_TYPE.M365://exchange
				LOCATION('./content/exchange/exchange_backup.php', 'office365_protect');
				break;
			case CONF.MODULE_TYPE.KUBERNETES:
				LOCATION('./content/kubernetes/kubernetes_backup.php', 'k8s_protect');
				break;
		}
	}

	// --------------副本目的地-------------
	//选择副本目的地
	let step2Valid = function () {
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		const copy_mode = $('#copy_mode');
		const threadNum = $('#threadNum');
		const threadDiv = $('.threadNum-number-div .spinner-group');
		// 节点信息
		let results = getNodeInfo();
		if (results) {
			initCopyModeDes();
		}
		// 目标为磁带存储，保留策略需要显示磁带的保留策略
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
			$('#reserveStrategyDiv>.control-label').text(LANG.UI_STRATEGY_RESERVE + ":");
			let value = 30;
			if (editFlag) {
				value = SETTINGS.brs.number == 65535 ? 30 : SETTINGS.brs.number
			}
			$('#spinnerNum').spinner("value", value);
			$('#spinnerDay').spinner("value", value);
		}
		if (editFlag) {
			// 使用策略插件初始化策略
			defaultConfig.strategy = { reserve: { reserveInfo: SETTINGS.brs }, speedlimit: SETTINGS.speedInfo };
		}
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig, function () {
			let reserveMode = $('#reserveMode');
			// 虚拟机、操作系统 非云存储且合并副本才显示按备份点保留
			if (parseInt(copy_mode.val()) == COPY_MODE.COMB) {
				if (SHOW_COPY_MODE_COMB.includes(data.module_type) && !cloudFlag) {
					reserveMode.val(RESERVE_MODE.POINT).removeAttr('disabled');
				} else {
					// 云存储只能按链保留
					reserveMode.val(RESERVE_MODE.CHIAN).prop('disabled', 'true');
				}
			} else {
				if (SHOW_COPY_MODE_COMB.includes(data.module_type) && !cloudFlag) {
					reserveMode.removeAttr('disabled');
				} else {
					reserveMode.val(RESERVE_MODE.CHIAN).prop('disabled', 'true');
				}
			}
			// 文件保留策略不受云存储影响 #24882
			if (data.module_type == CONF.MODULE_TYPE.FS || data.module_type == CONF.MODULE_TYPE.NAS) {
				reserveMode.val(RESERVE_MODE.POINT).removeAttr('disabled');
			}
		});
		// 磁带源和磁带存储只能镜像副本，且传输线程只能为1
		if (tapeFlag || tapeSource) {
			copy_mode.val(COPY_MODE.MIRROR).prop('disabled', 'true');
			threadNum.val(1);
			threadDiv.spinner('disable');
			$('.threadNum-number-div').hide();
		} else {
			$('.threadNum-number-div .spinner-group').spinner('enable');
			$('.threadNum-number-div').show();
			// 虚拟机和操作系统可以合并副本，其他定时模块不能
			if (!SHOW_COPY_MODE_COMB.includes(data.module_type)) {
				$('.chain_type-select-div').hide();
				$('.chain_length-number-div').hide();
				$('.merge-mode-tab').hide();
				$('.advance-config-wrap .nav-tabs>li').removeClass('active');
				$('.advance-config-wrap .tab-content>.tab-pane').removeClass('active');
				$('.retry_config-tab').addClass('active');
				$('.retry_config_pane').addClass('active');
				copy_mode.val(COPY_MODE.MIRROR).prop('disabled', true);
			} else {
				copy_mode.removeAttr('disabled');
				// 卷不显示 存储策略
				if (data.module_type == CONF.MODULE_TYPE.OS && data.sub_module_type == 0) {
					$('.merge-mode-tab').hide()
				}
			}
		}
		if ($('#backuptype').val() == BACKUP_TYPE.ONCE) {
			$('.reserve-strategy-form').hide();
			$('#reserveStrategyDiv').hide();
		} else {
			$('.reserve-strategy-form').hide();
			initReserveStrategyDes();
			if (!tapeFlag) {
				$('.reserve-strategy-form').show();
			}
		}
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.threadNum-number-div').hide();
		}
		// 当副本源是任务 且 目标是磁带 且是镜像副本时才允许显示自定义源
		if ([1, 3].includes(data.data_type) && tapeFlag && parseInt(copy_mode.val()) == 1) {
			customSourceShowFlag = true;
			showCustomSource(true);
			$('.copyNewestChianBtn').show();
			// 虚拟化才显示副本GFS备份点
			if (CONF.MODULE_TYPE.VM == MODULE_TYPE) {
				$('.copyGFSFlagBtn').show();
				if ($('#copyGFSflag').get(0).checked) {
					$('.copyGFSDiv').show();
				} else {
					$('.copyGFSDiv').hide();
				}
			} else {
				$('.copyGFSDiv').hide();
				$('.copyGFSFlagBtn').hide();
			}
		} else {
			customSourceShowFlag = false;
			showCustomSource();
			$('.copyNewestChianBtn').hide();
		}
		// #28220 修改副本任务禁止修改副本类型
		if (editFlag) {
			$('#copy_mode').val(SETTINGS.copy_mode).prop('disabled', true);// #28220 修改副本任务禁止修改副本类型
		}
		if ($('#copy_mode').val() == COPY_MODE.COMB) {
			// 镜像副本要屏蔽分片，副本回传屏蔽分片+冗余比列 #28357
			$('.datacontainersizediv').show();
		} else {
			// 镜像副本要屏蔽分片，副本回传屏蔽分片+冗余比列 #28357
			$('.datacontainersizediv').hide();
		}
		// #28678 仅异地存储为目标存储显示压缩
		if(data.strategyInfo.nodeInfo.storage_type !== CONF.BD_STORAGE_TYPE.REMOTE){
			$('#compress').bootstrapSwitch('state', false);
			$('.custom-form-group__compress').hide();
		} else {
			$('.custom-form-group__compress').show();
		}
        // #28339 取消永久增量+镜像副本上云组合   虚拟化永久增量为副本源+目标为云存储，不能镜像副本
		if(data.strategyInfo.nodeInfo.storage_type == CONF.BD_STORAGE_TYPE.CLOUD && p_incr_flag && [2,5].includes(MODULE_TYPE)){
			$('#copy_mode').val(2).prop('disabled', 'true');
			$('.copy-mode-tip').show();
			copyModeChange();
		} else {
			$('.copy-mode-tip').hide();
		}
        // #28339 取消永久增量+镜像副本上云组合   数据库永久增量为副本源，不能选择云存储
		if(p_incr_flag && MODULE_TYPE == CONF.MODULE_TYPE.DB && specil_sub_type_flag && (data.strategyInfo.nodeInfo.storage_type == CONF.BD_STORAGE_TYPE.CLOUD || data.strategyInfo.nodeInfo.storage_pool_type == 3)){
			UIToastr.showWarning(LANG.UI_COPY_TIPS_PINCR_CLOUD_UNSUPPORT);
			return false;
		}
		initCopyModeDes();
		return results;
	}
	// 控制自定义副本源配置显示
	let showCustomSource = function (flag = false) {
		if (flag && customSourceShowFlag) {
			$('.conf-btn-div').show();
		} else {
			$('.conf-btn-div').hide();
		}
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
		// 对比增量配置
		transportPlugin.initSelect({
			id: 'inc_mode',
			label: LANG.UI_COPY_TRANSPORT_INC_MODE,
			options: INC_MODE_CONFIG,
			tips: LANG.UI_COPY_TRANSPORT_INC_MODE_TIPS,
			defaultValue: data ? data.inc_mode : 1,
		});
		// 传输压缩配置
		transportPlugin.initSwitchSelectMixed({
			id: 'compress',
			labelSwitch: LANG.UI_COPY_BACK_COMPRESS,
			labelSelect: LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE,
			options: COMPRESS_METHOD_CONFIG,
			tips: LANG.UI_COPY_TRANSPORT_COMPRESS_TIPS,
			defaultFlag: data ? data.bts.compress : true,
			defaultValue: data ? data.bts.compress_method : 1,
		});
		// 传输线程配置
		transportPlugin.initNumber({
			id: 'threadNum',
			label: LANG.UI_GLOBAL_STRATEGY_THREAD_NUM,
			value: data ? data.bts.thread_num : (CONF.FUNCTIONS.includes('multithread') ? 3 : 1),
			min: 1,
			max: 8,
			step: 1,
			tips: LANG.UI_COPY_TRANSPORT_THREAD_NUM_TIPS,
		});
		$('.inc_mode-select-div').hide();
	}
	const initHighConfig = (data = null) => {
		// 合并模式
		if (data) {
			$(`#redundant_data_proportion`).val(data.bss.redundant_data_proportiont);
			$(`#data_container_size`).val(data.bss.data_container_size);
		}
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
		data.strategyInfo.nodeInfo.storage_pool_type = backupTargetInfo.storage_pool_type;
		data.strategyInfo.nodeInfo.storage_worm_config = backupTargetInfo.storage_worm_config;
		initResourceLimit(backupTargetInfo.node_uuid_list);
		// 判断云存储
		if (backupTargetInfo.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
			cloudFlag = true;
		} else {
			cloudFlag = false;
		}
		let remote_flag = false;
		if (backupTargetInfo.storage_type == CONF.BD_STORAGE_TYPE.REMOTE) {
			remote_flag = true;
		}
		// 初始化传输网络
		if (backupTargetInfo.node_uuid != '') {
			$('.transferNet').show();
			if (editFlag) {
				$('#transferNetworkTree').transferNetwork({
					node_uuid: backupTargetInfo.node_uuid,
					network_uuid: SETTINGS.bts.network_uuid,
					network_pool_uuid: SETTINGS.bts.network_pool_uuid,
					storage_uuid: backupTargetInfo.storage_uuid,
					remote_flag: remote_flag,
					remote_network_ip: SETTINGS.bts.transferNet,
					remote_network_port: SETTINGS.bts.transferPort,
				});
			} else {
				$('#transferNetworkTree').transferNetwork({ node_uuid: backupTargetInfo.node_uuid, storage_uuid: backupTargetInfo.storage_uuid, remote_flag: remote_flag });
			}
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

	// --------------副本策略--------------
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

	//副本备份方式（按策略、一次性）
	let backupTypeHandler = function () {
		if (BACKUP_TYPE.STRATEGY == this.value) {
			if (tapeFlag) {
				$('.reserve-strategy-form').hide();
				$('#reserveStrategyDiv').hide();
			} else {
				$('.reserve-strategy-form').show();
				$('#reserveStrategyDiv').show();
			}
		} else if (BACKUP_TYPE.ONCE == this.value) {
			$('.reserve-strategy-form').hide();	//隐藏保留策略
			$('#reserveStrategyDiv').hide();
		}
		data.strategyInfo.type = this.value; //
	}

	//初始化时间策略
	let initStrategy = function (strategy_config = null) {
		$('.copy-strategy-config').copyTimeStrategy({ strategy_type: strategy_type, showDom: $('.timeStrategyShow'), strategyConfig: strategy_config });
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
		//检查 传输策略、副本类型、时间策略 、高级配置 
		let result = getTransferInfo() && getCopyTypeInfo() && getTimeInfo() && getHighInfo();
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
		return result;
	}

	const getHighInfo = () => {
		// 获取合并模式配置
		data.strategyInfo.storageStrategy.storageInfo.redundant_data_proportion = parseInt($('#redundant_data_proportion').val());
		data.strategyInfo.storageStrategy.storageInfo.data_container_size = parseInt($('#data_container_size').val());
		if (SHOW_COPY_MODE_COMB.includes(data.module_type)) {
			$('.high_strategy_show').append($('.mergemodelabel').html() + ": " + $(`#redundant_data_proportion option:selected`).text() + `<br>`);
			// 镜像副本要屏蔽分片，副本回传屏蔽分片+冗余比列 #28357
			if ($('#copy_mode').val() == COPY_MODE.COMB) {
				$('.high_strategy_show').append($('.datacontainersizelabel').html() + ": " + $('#data_container_size option:selected').text() + `<br>`);
			}
		}
		return true;
	}
	//得到传输策略
	let getTransferInfo = function () {
		let strategyInfo = data.strategyInfo.transferStrategy;
		//传输网络
		let networkNode = no_transport_flag ? {} : $('#transferNetworkTree').transferNetwork('getSelect');
		if (false === networkNode) {
			return false;
		}
		strategyInfo.transferNet = networkNode.network_ip ?? '';
		//端口
		strategyInfo.transferPort = networkNode.network_port ?? '';
		strategyInfo.network = networkNode.network_uuid ?? '';
		strategyInfo.network_pool_uuid = networkNode.network_pool_uuid ?? '';
		strategyInfo.wan_accelerate_flag = false;//广域网加速功能  未启用
		// 获取传输策略配置
		const transportPlugin = $('.tabTransferConfig').data('myFormGroup');
		showIncModeFlag = parseInt($('#copy_mode').val()) == COPY_MODE.COMB ? true : false;
		// #28678 仅异地存储为目标存储显示压缩
		let showCompressFlag = data.strategyInfo.nodeInfo.storage_type !== CONF.BD_STORAGE_TYPE.REMOTE ? false : true;
		let result = transportPlugin.getFormGroupConfig({ tapeFlag: tapeFlag, showIncModeFlag: showIncModeFlag, showCompressFlag: showCompressFlag });
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

	//得到副本类型和数据链长度
	let getCopyTypeInfo = function () {
		data.copyItemInfo.copy_mode = parseInt($('#copy_mode').val());//副本类型
		let chianType = $('#chain_type').val();
		if (chianType == CHAIN_TYPE.NUM) {
			data.copyItemInfo.chain_length = parseInt($('#chain_length').val());//按个数
		} else if (chianType == CHAIN_TYPE.UNLIMITED) {
			data.copyItemInfo.chain_length = UNLIMITED_NUM;//无限制
		}
		if (!(data.copyItemInfo.chain_length > 0)) {
			UIToastr.showWarning(LANG.UI_COPY_CHAIN_LENGTH_TIP);
			return false;
		}
		let highInfo = '';
		// 副本类型
		if (data.copyItemInfo.copy_mode == COPY_MODE.MIRROR) {
			highInfo += LANG.UI_COPY_MODE_MIRROR + "<br>";
		} else if (data.copyItemInfo.copy_mode == COPY_MODE.COMB) {
			highInfo += LANG.UI_COPY_MODE_COMBINATION + "<br>";
			let chianType = $('#chain_type').val();
			if (chianType == 'num') {
				highInfo += LANG.UI_COPY_CHAIN_LENGTH + LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON + parseInt($('#chain_length').val()) + "<br>";
			} else if (chianType == 'unlimited') {
				highInfo += LANG.UI_COPY_CHAIN_LENGTH + LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON + LANG.UI_COPY_CHAIN_LENGTH_UNLIMITED + "<br>";
			}
		}
		$('.backupTypeShow').append(highInfo);
		if ($('#copyGFSflag').get(0).checked) {
			data.strategyInfo.copy_gfs_strategy = {
				copy_week_flag: $('#copy_week_flag').is(':checked'),
				copy_month_flag: $('#copy_month_flag').is(':checked'),
				copy_year_flag: $('#copy_year_flag').is(':checked'),
			};
			// #26536 开启gfs必须配置一项
			if (!data.strategyInfo.copy_gfs_strategy.copy_week_flag && !data.strategyInfo.copy_gfs_strategy.copy_month_flag && !data.strategyInfo.copy_gfs_strategy.copy_year_flag) {
				UIToastr.showWarning(LANG.UI_COPY_STRATEGY, LANG.UI_GLOBAL_STRATEGY_COPY_GFS_EMPTY);
				return false;
			}
		} else {
			data.strategyInfo.copy_gfs_strategy = {
				copy_week_flag: false,
				copy_month_flag: false,
				copy_year_flag: false,
			};
			data.copy_last_chain_flag = false;
		}
		// 开启自定义副本源必须配置一项
		if (customSourceShowFlag && $('#customSource').get(0).checked) {
			data.copy_last_chain_flag = $('#copyNewestChain').get(0).checked;
			if (!data.copy_last_chain_flag && !$('#copyGFSflag').get(0).checked) {
				UIToastr.showWarning(LANG.UI_COPY_STRATEGY, LANG.UI_COPY_STRATEGY_CUSTOM_SOURCE_EMPTY);
				return false;
			}
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

	//添加副本策略信息到确认配置页面
	let showStep3 = function () {
		//保留策略		
		if (!tapeFlag) {
			$('.reserveStrategyShow').html(data.strategyInfo.reserveStrategy.des);
		} else {
			data.strategyInfo.reserveStrategy.reserveInfo.enable_flag = false;
		}
		// 一次性 不显示保留策略
		if (data.strategyInfo.timeStrategy.type == BACKUP_TYPE.ONCE) {
			$('#reserveStrategyDiv').hide();
		} else {
			$('#reserveStrategyDiv').show();
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
		let copyStrategyDes = '';
		// 副本策略
		if (data.copyItemInfo.copy_mode == 1) {
			copyStrategyDes += LANG.UI_COPY_MODE + "：" + LANG.UI_COPY_MODE_MIRROR + "<br>";
			if (customSourceShowFlag) {
				copyStrategyDes += LANG.UI_COPY_STRATEGY_LABEL_CUSTOM_SOURCE + "：" + getSwitchDes($('#customSource').get(0).checked) + "<br>";
			}
		} else if (data.copyItemInfo.copy_mode == 2) {
			copyStrategyDes += LANG.UI_COPY_MODE + "：" + LANG.UI_COPY_MODE_COMBINATION + "<br>";
			let chianType = $('#chain_type').val();
			if (chianType == 'num') {
				copyStrategyDes += LANG.UI_COPY_CHAIN_LENGTH + "：" + parseInt($('#chain_length').val()) + "<br>";
			} else if (chianType == 'unlimited') {
				copyStrategyDes += LANG.UI_COPY_CHAIN_LENGTH + "：" + LANG.UI_COPY_CHAIN_LENGTH_UNLIMITED + "<br>";
			}
		}
		$('.copyStrategyDesc').html(copyStrategyDes);
	}

	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function (check) {
		if (check) {
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

	//提交副本任务
	let submit = function () {
		const nameTip = $('.job_name_tip');
		const jobName = $("#jobName").val();
		if (!customInputValidate('string', jobName)) {
			return false;
		}
		if ('' == $.trim(jobName)) {
			nameTip.html(LANG.UI_MACHINE_OS_JOB_NAME_TIPS).show();
			return;
		}
		nameTip.hide();
		data.taskName = $.trim(jobName);
		data.node_uuid = $('#targetNode').val();
		// 一次性  不设置保留策略  值为零
		if (data.strategyInfo.timeStrategy.type == BACKUP_TYPE.ONCE) {
			data.strategyInfo.reserveStrategy.value = 0;
		}
		Metronic.blockUI({ target: '#copyContent', animate: true });
		let newJobBack = function (res) {
			Metronic.unblockUI('#copyContent');
			if (operateResponseList(res)) {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}
		};
		//TODO提交
		let submitParams = deepCloneObject(data);
		// 镜像副本传时间点列表
		if (data.copyItemInfo.copy_mode == COPY_MODE.MIRROR) {
			for (let i = 0; i < submitParams.copyItemInfo.copy_list.length; i++) {
				submitParams.copyItemInfo.copy_list[i].archive_timepoint_uuid = '';
			}
		};
		// 合并副本传时间点
		if (data.copyItemInfo.copy_mode == COPY_MODE.COMB) {
			for (let i = 0; i < submitParams.copyItemInfo.copy_list.length; i++) {
				submitParams.copyItemInfo.copy_list[i].timepoint_uuid_list = [];
			}
		};
		pAjaxRequest(deepCloneObject(submitParams), "/api/v1/copy", "POST", newJobBack, true);
	}

	//显示|隐藏 数据链长度
	let copyModeChange = function () {
		let copy_mode = $('#copy_mode option:selected').val();
		if (copy_mode == COPY_MODE.MIRROR) {//镜像副本隐层数据链长度
			$('.chain_type-select-div').hide();
			$('.inc_mode-select-div').hide();
			$('#reserveMode').val(RESERVE_MODE.CHIAN).prop('disabled', 'true');
			$('.chain_length-number-div').hide();
			if (SHOW_COPY_MODE_COMB.includes(data.module_type) && !cloudFlag) {
				$('#reserveMode').removeAttr('disabled');
			}
			// 镜像副本要屏蔽分片，副本回传屏蔽分片+冗余比列 #28357
			$('.datacontainersizediv').hide();
			showCustomSource(true);
		} else if (copy_mode == COPY_MODE.COMB) {
			$('.chain_type-select-div').show();
			$('.inc_mode-select-div').show();
			// 虚拟机、操作系统 非云存储且合并副本才显示按备份点保留
			if (SHOW_COPY_MODE_COMB.includes(data.module_type) && !cloudFlag) {
				$('#reserveMode').val(RESERVE_MODE.POINT).removeAttr('disabled');
			} else {
				$('#reserveMode').val(RESERVE_MODE.CHIAN).prop('disabled', 'true');
			}
			// 镜像副本要屏蔽分片，副本回传屏蔽分片+冗余比列 #28357
			$('.datacontainersizediv').show();
			chainTypeChange();
			showCustomSource();
		}
		// 文件保留策略不受云存储影响 #24882
		if (data.module_type == CONF.MODULE_TYPE.FS || data.module_type == CONF.MODULE_TYPE.NAS) {
			$('#reserveMode').val(RESERVE_MODE.POINT).removeAttr('disabled');
		}
		initCopyModeDes();
		initReserveStrategyDes();
	}

	let initCopyModeDes = () => {
		let des = '';
		let copy_mode = $('#copy_mode').val();
		if (copy_mode == COPY_MODE.MIRROR) {
			des += LANG.UI_COPY_MODE + ": " + LANG.UI_COPY_MODE_MIRROR;
			if (customSourceShowFlag) {
				des += ", " + LANG.UI_COPY_STRATEGY_LABEL_CUSTOM_SOURCE + "：" + getSwitchDes($('#customSource').get(0).checked);
			}
		} else if (copy_mode == COPY_MODE.COMB) {
			des += LANG.UI_COPY_MODE + ": " + LANG.UI_COPY_MODE_COMBINATION;
			let chain_type = $('#chain_type').val();
			if (chain_type == CHAIN_TYPE.NUM) {
				des += ", " + LANG.UI_COPY_CHAIN_LENGTH + "：" + parseInt($('#chain_length').val()) + "<br>";
			} else if (chain_type == CHAIN_TYPE.UNLIMITED) {
				des += ", " + LANG.UI_COPY_CHAIN_LENGTH + "：" + LANG.UI_COPY_CHAIN_LENGTH_UNLIMITED + "<br>";
			}
		}
		$('.copyModeDes').html(des);
	}
	// 1.初始化副本源存储
	const initCopySource = function (remoteFlag = false) {
		$('.copy-source-div').mySource({ editFlag: editFlag, module_type: MODULE_TYPE, sub_module_type: SUB_MODULE_TYPE, oldInfo: SETTINGS, remoteFlag: remoteFlag, showDom: $('.copy-source-list-div') });
	}
	const initEditInfo = () => {
		let task_uuid = $('#uuid').val();
		let taskInfoBack = function (res) {
			if (res.success) {
				SETTINGS = res.data;
				initOldData();
				initStep1Settings(); //初始化副本源
				initStep2Settings(); //初始化副本目的地
				initStep3Settings();  //初始化副本策略
			}
		};
		pAjaxRequest({ task_uuid: task_uuid }, "/api/v1/copy/resources/task", "GET", taskInfoBack, true);
	}
	const initStepOne = () => {
		// 判断是修改还是创建页面
		if ($('#uuid').val()) {
			editFlag = true;
			COPY_TIP = LANG.UI_COPY_MODIFY;
			initEditInfo();
		} else {
			// 创建副本任务
			editFlag = false;
			// 1.初始化存储
			initCopySource();
			// 3.初始化策略
			initStrategy();       //初始化时间策略
			initTransportConfig(); // 初始化传输策略
			initHighConfig(); // 初始化高级配置
			// 重试策略
			$('#retry_config').retryStrategy({ module_type: 'copy' });
			//任务名获取
			let initName = function (res) {
				if (res.success) {
					$('#jobName').val(res.data);
				};
			};
			pAjaxRequest({}, "/api/v1/copy/jobs/name", "GET", initName, true);
		};
	}
	const initDefaultConfig = () => {
		$('.copy-mode-config').initFormGroupLine();
		const copyModePlugin = $('.copy-mode-config').data('myFormGroup');
		copyModePlugin.initSelect({
			id: 'copy_mode',
			label: LANG.UI_COPY_MODE,
			options: COPY_MODE_CONFIG,
			tips: LANG.UI_COPY_MODE_TIPS,
			defaultValue: 1,
		});
		copyModePlugin.initSelect({
			id: 'chain_type',
			label: LANG.UI_COPY_CHAIN_LENGTH,
			options: CHAIN_TYPE_CONFIG,
			tips: LANG.UI_COPY_CHAIN_LENGTH_TIPS,
			defaultValue: 'num',
		});
		copyModePlugin.initNumber({
			id: 'chain_length',
			label: LANG.UI_BACKUP_NUM,
			tips: LANG.UI_COPY_CHAIN_LENGTH_TIPS,
			value: 3,
			min: 1,
			max: 999,
			step: 5,
		});
		// 默认隐藏链长度
		$('.chain_length-number-div').hide();
		$('.chain_type-select-div').hide();
		// 未授权不显示worm
		if (!CONF.FUNCTIONS.includes('worm')) {
			$('#sateLi').hide();
			$('.safeDiv').hide();
		}
	}

	return {
		//main function to initiate the module_type
		init: function () {
			// 初始化副本策略
			initDefaultConfig();
			initStepOne();
			wizardInit();  //副本步骤
			initListener(); // 事件监听
		}
	};

}();

jQuery(document).ready(function () {
	DataCopy.init();
});