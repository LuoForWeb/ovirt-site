var DBJobDetails = function () {

	var logGrid;
	var initLogFlag = false;	//任务日志初始化标志
	var _scrollHeight = 0; //任务日志全局高度
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndexLog = 0,detailsInfoLog = null;

	var initChartFlag = false; //任务曲线图初始化标志

	var vmSelect = []; //虚拟机选择

	var _taskStatus; //监控任务状态

	var jobParams = {}; //操作需要参数

	var initButFlag = false;
	var _data;
	var myChart;
    const TIDB_COMPRESS_METHOD_ENUM = {
        lz4: 1,
        snappy: 2,
        zstd: 3,
    };
	/**
	 * 特殊配置开关
	 */
	const special_config = {
		oracle_kunming_route: false,  // 昆明铁路Oracle配置
	};
	let dbDetailsGrid = null;

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
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getDBBasicInfo',p:data}, function(d){setBasicInfo(d, timerTask.DBJobDetails_taskRunningInfo)});
			timerTask.DBJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	/**
	 * 设置<数据库列表>的<操作>框选项
	 * @param {int} dbType 数据库类别
	 */
	var setDbOperate = function (dbType, agentInfo) {
		// 默认全部展示，不同的数据库隐藏不同的备份类型。show()是一个链式操作，会返回jQuery对象本身的
		let startFull = $('#startFull').show();  // 全量备份
		let startIncr = $('#startIncr').show();  // 增量备份
		let startDiff = $('#startDiff').show();  // 差异备份
		let startLog = $('#startLog').show(); // 日志备份
		let startArchiveLog = $('#startArchiveLog').show(); // 归档日志备份
		switch(dbType){
			case CONF.DB_TYPE.SQLSERVER:
				startIncr.hide();  // 隐藏增量备份
				startArchiveLog.hide();  // 隐藏归档日志备份
				break;
			case CONF.DB_TYPE.ORACLE:
				startLog.hide();  // 隐藏日志备份
				if (!special_config.oracle_kunming_route) {
					$('.oracleStartDiv').css('display', 'inline-block').hide(0);
				} else {
					if (!agentInfo.multi_task_flag) { // 多任务(日志备份)
						$('.oracleStartDiv').css('display', 'inline-block');
					}
				}
				if (agentInfo.multi_task_flag) { // 多任务(日志备份)
					startFull.hide();  // 隐藏的完全备份
					startIncr.hide();  // 隐藏的增量备份
					startDiff.hide();  // 隐藏差异备份
					$('#startCrosscheckFull').hide();
					$('#startCrosscheckIncr').hide();
					$('#startCrosscheckDiff').hide();
				}
				break;
			case CONF.DB_TYPE.MYSQL:
				startDiff.hide();  // 隐藏差异备份
				startArchiveLog.hide();  // 隐藏归档日志备份
				break;
			case CONF.DB_TYPE.DM:
				startLog.hide();  // 隐藏日志备份
				break;
			case CONF.DB_TYPE.MARIA:
				startDiff.hide();  // 隐藏差异备份
				startArchiveLog.hide();  // 隐藏归档日志备份
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.ANTDB:
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.HIGHGO:
			case CONF.DB_TYPE.OPENGAUSS:
			case CONF.DB_TYPE.VASTBASE:
				startLog.hide();  // 隐藏日志备份
				startIncr.hide();  // 隐藏增量备份
				startDiff.hide();  // 隐藏差异备份
				break;
			case CONF.DB_TYPE.MONGODB:
				startArchiveLog.hide();  // 隐藏归档日志备份
				break;
			case CONF.DB_TYPE.TIDB:
				startLog.hide();  // 隐藏日志备份
				startArchiveLog.hide();  // 隐藏归档日志备份
				startIncr.hide();  // 隐藏增量备份
				startDiff.hide();  // 隐藏差异备份
				if (typeof agentInfo.depend_task_uuid === 'string' && agentInfo.depend_task_uuid.length) {
					startFull.hide();
				}
				break;
			case CONF.DB_TYPE.SAPHANA:
				startLog.hide();  // 隐藏日志备份
				startArchiveLog.hide();  // 隐藏归档日志备份
				break;
		}
	}

	const convertSpaceCharToSign = (text) => {
		return text.split('\n')
			.map(line => line.replace(/ /g, ' ').replace(/ /g, '&nbsp;'))
			.join('<br>');
	}

	/**
	 * 设置时间策略配置详情
	 * @param data
	 * @param dbType
	 * @param timeStrategy
	 */
	const setBackupTimeStrategyConfigDetail = (data, dbType, timeStrategy) => {
		/**
		 * sqlserver 完全 差异 日志
		 * oracle 完全 增量 差异 归档日志
		 * mysql/mariadb 完全 增量 日志
		 * dm 完全 增量 差异 归档日志
		 * postgres/antdb/kingbase/uxdb/highgo/opengauss/vastbase 完全 归档日志
		 * MongoDB 完全 增量 差异 永久增量 日志
		 * TiDB 完全 日志
		 * SAP HANA 完全 增量 差异
		 */
		$('#fulldes').html(timeStrategy.full);
		$('#incdes').html(timeStrategy.incr);
		$('#diffdes').html(timeStrategy.diff);
		$('#logdes').html(timeStrategy.log);
		$('#pincrdes').html(timeStrategy.pIncr);
		$('.full-div').show();					// 显示完备
		$('.increment-div').hide();  			// 隐藏增量
		$('.difference-div').hide();  			// 隐藏差异
		$('.permanent-increment-div').hide();  	// 隐藏永久增量
		$('.log-archivelog-div').show();		// 显示日志备份
		$('.backup_time_strategy_archivelog_div').hide();	// 隐藏归档日志备份标题
		$('.backup_time_strategy_log_div').show();			// 默认显示日志备份标题

		switch(dbType){
			case CONF.DB_TYPE.SQLSERVER:
				$('.difference-div').show();
				break;
			case CONF.DB_TYPE.ORACLE:
				$('.increment-div').show();
				$('.difference-div').show();
				$('.backup_time_strategy_archivelog_div').show();
				$('.backup_time_strategy_log_div').hide();
				break;
			case CONF.DB_TYPE.MYSQL:
			case CONF.DB_TYPE.MARIA:
				$('.increment-div').show();
				$('.difference-div').hide();
				break;
			case CONF.DB_TYPE.DM:
				$('.increment-div').show();
				$('.difference-div').show();
				$('.backup_time_strategy_archivelog_div').show();
				$('.backup_time_strategy_log_div').hide();
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.ANTDB:
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.HIGHGO:
			case CONF.DB_TYPE.OPENGAUSS:
			case CONF.DB_TYPE.VASTBASE:
				$('.backup_time_strategy_archivelog_div').show();
				$('.backup_time_strategy_log_div').hide();
				break;
			case CONF.DB_TYPE.MONGODB:
				$('.increment-div').show();
				$('.difference-div').show();
				$('.permanent-increment-div').show();
				break;
			case CONF.DB_TYPE.TIDB:
				$('.log-archivelog-div').hide();
				$('.backup_time_strategy_archivelog_div').hide();
				$('.backup_time_strategy_log_div').hide();

				if (typeof data.agentInfo.depend_task_uuid === 'string' && data.agentInfo.depend_task_uuid.length) {
					$('.time-strategy-backup-type-div').hide();
				}
				break;
			case CONF.DB_TYPE.SAPHANA:
				$('.increment-div').show();
				$('.difference-div').show();
				$('.log-archivelog-div').show();
				$('.backup_time_strategy_archivelog_div').hide();
				$('.backup_time_strategy_log_div').show();
				$('#logdes').html(LANG.UI_GLOBAL_STRATEGY_BACKUP_INTERVAL + ': ' + data.agentInfo.auto_log_backup_interval / 60 + LANG.UI_JOB_MINUTE);
				break;
		}

		switch (data.timeStrategyBackupType) {
			case 'strategy':
				$('#configuration_timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);
				$('#timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);
				break;
			case 'oncetime':
				$('#configuration_timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);
				$('#timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);
				$('.increment-div').hide();
				$('.difference-div').hide();
				$('.log-archivelog-div').hide();
				$('.permanent-increment-div').hide();
				break;
			default:
				$('#configuration_timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);
				$('#timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);
				$('.backup_time_strategy_div').hide();
				if (dbType === CONF.DB_TYPE.SAPHANA) {
					$('.log-archivelog-div').show();
				}
				break;
		}
		// 在编排中的任务，显示按编排策略执行（原时间策略不生效）
		if (data.task_orchestration_plan_flag) {
			$('#configuration_timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
			$('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
		}
	};

	/**
	 * 设置备份通用配置详情
	 */
	const setBackupCommonConfigDetail = data => {
		// 限速
		$('#speed_limit_backup').html(data.speed_limit.text).prop('title', data.speed_limit.text.replaceAll('<br>', '\n'));

		$('.storageStrategyDetailTitle').show();
		$('.storageStrategyDetailContent').show();
		// 存储
		let nodeDes = ``;
		if (data.storageInfo.node_pool_uuid) {
			nodeDes += data.storageInfo.node_pool_nickname + '<br>';
		}
		if (data.storageInfo.node_uuid) {
			nodeDes += data.storageInfo.node.name + '<br>' + data.storageInfo.node.ip;
		}
		if (nodeDes.length) {
			$('#backup_node').html(nodeDes);  // 存储节点
		} else {
			$('#backup_node').html(`--`);
		}
		if (data.storageInfo.storage_uuid) {
			let storageDes = ``;
			if (data.storageInfo.storage_pool_uuid) {
				storageDes += data.storageInfo.storage_pool_nickname + '<br>';
			}
			if (!data.storageInfo.storage) {
				// 没有存储信息,自动选择存储
				storageDes += LANG.UI_JOB_AUTO_SELECT_STORAGE;
			} else {
				storageDes += data.storageInfo.storage.name + '(' + data.storageInfo.storage.type + ')<br>';
				if(!data.storageInfo.storage.quotaFlag){
					storageDes += LANG.UI_JOB_TOTAL_SIZE + ":" + data.storageInfo.storage.size + ", " +
					LANG.UI_JOB_FREE_SIZE + ":" + data.storageInfo.storage.freesize;
				}else{
					storageDes += data.storageInfo.storage.quotades;
				}
			}
			$('#storage_device').html(storageDes);  // 存储设备
		} else {
			$('#storage_device').html(`--`);
		}
		// 存储策略
		// 重复数据删除
		if (CONF.FUNCTIONS.includes('dedupication')) {
			$('.backup-deduplication-form-item').show();
			$('#backup_deduplication').html(getFlagLevelInfo(data.storageInfo.high.deduplication));	// 删除重复数据
		} else {
			$('.backup-deduplication-form-item').hide();
		}
		$('#compress_storage').html(getFlagLevelInfo(data.storageInfo.high.compressed));		// 压缩存储
		// 压缩等级
		if (data.storageInfo.high.compressed) {
			var method = '';
			switch(data.storageInfo.high.compress_method) {
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
			$('.compress-method-form-item').show();
			$('#compress_method').html(method);
		} else {
			$('.compress-method-form-item').hide();
		}
		$('#encrypt_storage').html(getFlagLevelInfo(data.storageInfo.high.encrypt_flag));
		// 存储加密算法
		if (data.storageInfo.high.encrypt_flag) {
			let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
			if (data.storageInfo.high.encrypt_method == 2) {
				method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
			}
			$('.storage-encrypt-method-form-item').show();
			$('#encrypt_storage_method').html(method);
			$('.auto-password-form-item').show();
			$('#storage_auto_password').html(getFlagLevelInfo(data.storageInfo.high.password_auto_flag));
		} else {
			$('.storage-encrypt-method-form-item').hide();
			$('.auto-password-form-item').hide();
		}

		$('.reserveStrategyDetailWrapper').show();
		// 保留
		if (data.storageInfo.storage_uuid && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.TAPE) {
			//磁带获取策略替换保留策略
			$('.reserve-strategy-form-item .name').html(LANG.UI_GLOBAL_STRATEGY_NAME);
			$('#reserve_strategy').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
		} else {
			$('.reserve-strategy-form-item .name').html(LANG.UI_STRATEGY_RESERVE);
			$('#reserve_strategy').html(getReservedStrategy(data.reservedStrategy, data.agentInfo.dbtype));
		}
	};

	/**
	 * 设置备份传输配置详情
	 */
	const setBackupTransferConfigDetail = data => {
		// 加密传输
		$('#backup_encrypt_transmit').html(getFlagLevelInfo(data.transportStrategy.encrypt));
		if (data.transportStrategy.encrypt) {
			$('.backup-transfer-encrypt-method-form-item').show();
			let encryptMethod = parseInt(data.transportStrategy.encrypt_method);
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if (encryptMethod === 2) {
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$('#backup_transmit_encrypt_method').html(method);
		} else {
			$('.backup-transfer-encrypt-method-form-item').hide();
		}
		// 传输模式
		// 传输网络
		if (data.networkFlag) {
			let networkDes = ``;
			if (data.transportStrategy.network_pool_uuid) {
				networkDes += data.transportStrategy.network_pool_nickname + '<br>';
			}
			if (data.transportStrategy.network_uuid) {
				networkDes += data.transportStrategy.network + '<br>';
			}
			if (networkDes.length) {
				$('#backup_transmit_network').html(networkDes);  // 传输网络
			} else {
				$('#backup_transmit_network').html(LANG.UI_NODE_NETWORK_MODE_AUTO_MATCH);
			}
		} else {
			$('.backup-transmit-network-pool-form-item').hide();
			$('.backup-transmit-network-form-item').hide();
		}
		// 通道数
		$('.mongodbThreadNumDiv').hide();
		$('.oracleChannelCountDiv').hide();
		$('.sapHanaChannelCountDiv').hide();
		let dbType = parseInt(data.agentInfo.dbtype);
		switch (dbType) {
			case CONF.DB_TYPE.ORACLE:
				if (!data.storageInfo.storage_uuid || data.storageInfo.storage.typenum != CONF.BD_STORAGE_TYPE.TAPE) {
					$('.oracleChannelCountDiv').show();
					$('#oracleChannelCount').html(data.thread_num);
				}
				break;
			case CONF.DB_TYPE.MONGODB:
				if (!data.storageInfo.storage_uuid || data.storageInfo.storage.typenum != CONF.BD_STORAGE_TYPE.TAPE) {
					$('.mongodbThreadNumDiv').show();
					$('#mongodbThreadNum').html(data.thread_num);
				}
				break;
			case CONF.DB_TYPE.SAPHANA:
				if (!data.storageInfo.storage_uuid || data.storageInfo.storage.typenum != CONF.BD_STORAGE_TYPE.TAPE) {
					$('.sapHanaChannelCountDiv').show();
					$('#sapHanaChannelCount').html(data.agentInfo.channel_count);
				}
				break;
			default:
				break;
		}
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.mongodbThreadNumDiv').hide();
		}
	};

	/**
	 * 设置备份安全配置策略详情
	 */
	const setBackupSafeConfigStrategyDetail = data => {
		$('#safeStrategyDiv').show();
		// 隐藏安全策略
		if (data.storageInfo.storage_uuid && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.TAPE) {
			$('#safeStrategyDiv').hide();
			return;
		}
		if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
			$('#safeStrategyDiv').hide();
			return;
		}
		let safeConfigStrategy = data.safe_config_strategy;
		$('.backupSafeStrategyItem').show();
		$('.recoverySafeStrategyItem').hide();
		// worm保护
		if (CONF.FUNCTIONS.includes('worm')) {
			if (data.storageInfo.worm_flag) {
				$('.wormConfigDiv').show();
				// WORM保护
				$('#backup_worm_flag').html(getFlagLevelInfo(safeConfigStrategy.worm_flag));
				if (safeConfigStrategy.worm_flag) {
					$('.backup_worm_date-form-item').show();
					$('#backup_worm_date').html(safeConfigStrategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY)
				} else {
					$('.backup_worm_date-form-item').hide();
				}
			} else {
				$('.wormConfigDiv').hide();
			}
		} else {
			$('.wormConfigDiv').hide();
		}

		// 完整性配置
		if (CONF.FUNCTIONS.includes('integrity')) {
			$('#integrity_check_flag').html($.fn.getCompleteDetectionBackupDes({
				integrityCheckFlag: safeConfigStrategy.integrity_check_flag,
				integrityCheckConfig: safeConfigStrategy.integrity_check_config,
				isFullTimepointTitle: false,
				showIncrErrorPolicy: false,
				excludeTitleFlag: true,
			}));
		} else {
			$('.integrityCheckItemDiv').hide();
		}
	};

	/**
	 * 设置备份重试策略详情
	 */
	const setBackupRetryStrategyDetail = retryStrategy => {
		$('#network_retry_times').html(retryStrategy.network_retry_times);				// 网络重试次数
		$('#network_retry_interval').html(retryStrategy.network_retry_interval);		// 网络重试间隔
		$('#op_retry_flag').html(getFlagLevelInfo(retryStrategy.op_retry_flag));		// 异常操作重试开关
		if (!retryStrategy.op_retry_flag) {
			$('.op-retry-form-item').hide();
		} else {
			$('.op-retry-form-item').show();
			$('#op_retry_times').html(retryStrategy.op_retry_times);						// 异常操作重试次数
			$('#op_retry_interval').html(retryStrategy.op_retry_interval);					// 异常操作重试间隔
		}
		$('#task_retry_flag').html(getFlagLevelInfo(retryStrategy.task_retry_flag));	// 任务重试开关
		if (!retryStrategy.task_retry_flag) {
			$('.task-retry-form-item').hide();
		} else {
			$('.task-retry-form-item').show();
			// 任务重试对象
			if (parseInt(retryStrategy.task_retry_object) === 1) {  // 仅重试任务中失败的对象
				$('#task_retry_object').html(LANG.UI_RETRY_FAILED_OBJS_IN_TASK);
			} else {  // 重试任务中所有的对象
				$('#task_retry_object').html(LANG.UI_RETRY_ALL_OBJS_IN_TASK);
			}
			$('#task_retry_times').html(retryStrategy.task_retry_times);					// 任务重试次数
			$('#task_retry_interval').html(retryStrategy.task_retry_interval / 60);				// 任务重试间隔
		}
	};

	/**
	 * 设置恢复通用配置详情
	 */
	const setRecoveryCommonConfigDetail = data => {
		$('.storageStrategyDetailTitle').hide();
		$('.storageStrategyDetailContent').hide();
		$('.reserveStrategyDetailWrapper').hide();
		// 限速
		$('#speed_limit_backup').html(data.speed_limit.text).prop('title', data.speed_limit.text.replaceAll('<br>', '\n'));
	};

	/**
	 * 设置恢复传输配置详情
	 */
	const setRecoveryTransferConfigDetail = data => {
		// 加密传输
		$('#backup_encrypt_transmit').html(getFlagLevelInfo(data.transportStrategy.encrypt));
		if (data.transportStrategy.encrypt) {
			$('.backup-transfer-encrypt-method-form-item').show();
			let encryptMethod = parseInt(data.transportStrategy.encrypt_method);
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if (encryptMethod === 2) {
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$('#backup_transmit_encrypt_method').html(method);
		} else {
			$('.backup-transfer-encrypt-method-form-item').hide();
		}
		// 传输模式
		// 传输网络
		if (data.networkFlag) {
			let networkDes = ``;
			if (data.transportStrategy.network_pool_uuid) {
				networkDes += data.transportStrategy.network_pool_nickname + '<br>';
			}
			if (data.transportStrategy.network_uuid) {
				networkDes += data.transportStrategy.network + '<br>';
			}
			if (networkDes.length) {
				$('#backup_transmit_network').html(networkDes);  // 传输网络
			} else {
				$('#backup_transmit_network').html(LANG.UI_NODE_NETWORK_MODE_AUTO_MATCH);
			}
		} else {
			$('.backup-transmit-network-pool-form-item').hide();
			$('.backup-transmit-network-form-item').hide();
		}
		// 通道数
		$('.mongodbThreadNumDiv').hide();
		$('.oracleChannelCountDiv').hide();
		$('.sapHanaChannelCountDiv').hide();
		let dbType = parseInt(data.agentInfo.dbtype);
		switch (dbType) {
			case CONF.DB_TYPE.ORACLE:
				$('.oracleChannelCountDiv').show();
				$('#oracleChannelCount').html(data.thread_num);
				if (data.agentInfo.source_storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
					$('.oracleChannelCountDiv').hide();
				}
				break;
			case CONF.DB_TYPE.MONGODB:
				$('.mongodbThreadNumDiv').show();
				$('#mongodbThreadNum').html(data.thread_num);
				if (data.agentInfo.source_storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
					$('.mongodbThreadNumDiv').hide();
				}
				break;
			default:
				break;
		}
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.mongodbThreadNumDiv').hide();
		}
	};

	/**
	 * 设置恢复安全配置策略详情
	 */
	const setRecoverySafeConfigStrategyDetail = (safeConfigStrategy, data) => {
		$('#safeStrategyDiv').show();
		if (data.agentInfo.source_storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
			$('#safeStrategyDiv').hide();
			return;
		}
		if (!CONF.FUNCTIONS.includes('integrity')) {
			$('#safeStrategyDiv').hide();
			return;
		}
		$('.backupSafeStrategyItem').hide();
		$('.recoverySafeStrategyItem').show();
		if (!safeConfigStrategy.integrity_check_flag) {
			$('#integrity_verification_flag').html(LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_RECOVERY_UNSET);
		} else {
			let recoveryErrorPolicy = parseInt(safeConfigStrategy.integrity_check_config.recovery_error_policy);
			let safeConfigStrategyDes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL + ': ';
			if (recoveryErrorPolicy === 0) {  // 中断恢复
				safeConfigStrategyDes += LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY + '<br>';
			} else if (recoveryErrorPolicy === 1) {  // 继续恢复
				safeConfigStrategyDes += LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY + '<br>';
			} else {  // 恢复到无网环境
				safeConfigStrategyDes += LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK + '<br>';
			}
			$('#integrity_verification_flag').html(safeConfigStrategyDes);
		}
	};

	/**
	 * 设置恢复重试策略详情
	 */
	const setRecoveryRetryStrategyDetail = retryStrategy => {
		$('#network_retry_times').html(retryStrategy.network_retry_times);				// 网络重试次数
		$('#network_retry_interval').html(retryStrategy.network_retry_interval);		// 网络重试间隔
		$('#op_retry_flag').html(getFlagLevelInfo(retryStrategy.op_retry_flag));		// 异常操作重试开关
		if (!retryStrategy.op_retry_flag) {
			$('.op-retry-form-item').hide();
		} else {
			$('.op-retry-form-item').show();
			$('#op_retry_times').html(retryStrategy.op_retry_times);						// 异常操作重试次数
			$('#op_retry_interval').html(retryStrategy.op_retry_interval);					// 异常操作重试间隔
		}
		$('#task_retry_flag').html(getFlagLevelInfo(retryStrategy.task_retry_flag));	// 任务重试开关
		if (!retryStrategy.task_retry_flag) {
			$('.task-retry-form-item').hide();
		} else {
			$('.task-retry-form-item').show();
			// 任务重试对象
			if (parseInt(retryStrategy.task_retry_object) === 1) {  // 仅重试任务中失败的对象
				$('#task_retry_object').html(LANG.UI_RETRY_FAILED_OBJS_IN_TASK);
			} else {  // 重试任务中所有的对象
				$('#task_retry_object').html(LANG.UI_RETRY_ALL_OBJS_IN_TASK);
			}
			$('#task_retry_times').html(retryStrategy.task_retry_times);					// 任务重试次数
			$('#task_retry_interval').html(retryStrategy.task_retry_interval / 60);				// 任务重试间隔
		}
	};

	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		data = JSON.parse(data);
		_data = data;
		if(!data.flag){
			clearTimeout(timeoutID);
			$('#total-progress').css({width: '100%'});
			$('#progressright').html('100%');
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function(){
				$('body > .daterangepicker').remove();  // 移除显示的日期选择组件
				LOCATION('./content/platform/jobs/jobs.php');
			}, 5000);
		}


		//基本信息
		$('#taskName').html(data.taskName);
		$('#taskType').html(data.taskType + "(" + data.db_type_name + ")");
		$('#taskIP').html(data.db_hostname + "/" + data.db_ip);
		if(data.status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.statusValue) + '" >' + data.status + '</span>');
		}

		// 任务阶段
		$('#jobTaskStage').html(data.current_stage)

		//保存获取到的任务参数
		_taskStatus = data.statusValue;
		jobParams.status = data.statusValue;
		jobParams.taskType = data.taskTypeFlag;
		jobParams.subModule = data.hypervisor;
		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);
		$('#endTime').html(data.endTime);
		//存储信息
		if(data.storageInfo.flag){
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
			$('#nodeReInfo').html(node.name + "<br>" + node.ip);	//恢复所用节点
			$('#storageinfo').html(storageInfo);
			if(high){
				$('#deduplication').html(getFlagLevelInfo(high.deduplication));
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
				$('#passwordAuto').html(getFlagLevelInfo(high.password_auto_flag));
			}

		}

		//高级
		$('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);
		var dbType = parseInt(data.agentInfo.dbtype);
		var timeStrategy = getTimeStrategy(data.timeStrategy, parseInt(data.timepoint_recovery_type), data.taskTypeFlag, data.timeStrategyBackupType, dbType);
		if(28 == data.taskTypeFlag){
			// $('#configurationLi').show();
			$('#recoveryConfigLi').hide();
			$('.recovery_div').hide();
			$('#recoveryBackupNodeWrapper').hide();
			// 备份策略
			$('#configuration_createTime').html(data.createTime);
			$('#createTime').html(data.createTime);
			$('#configuration_nextTime').html(data.nextTime);
			$('#nextTime').html(data.nextTime);

			if(1 != CONF.SOFTWARE && 4 != CONF.SOFTWARE){
				$('.parsefsDiv').show();
			}
			$('#dbbackuptips').show();	//选择备份对应描述
			$('.dbStartDiv').show();	//选择数据库操作
			$('.recoveryDiv').hide();	//隐藏恢复模块
			$('.totalsizeDiv').hide();	//隐藏总容量
			$('.progressDiv').hide();	//隐藏进度条

			// 详情
			// 时间策略
			setBackupTimeStrategyConfigDetail(data, dbType, timeStrategy);

			// 通用策略
			setBackupCommonConfigDetail(data);

			// 传输策略
			setBackupTransferConfigDetail(data);

			// 安全策略
			setBackupSafeConfigStrategyDetail(data);

			// 高级配置
			$('#configuration_detail_archivelogDiv').hide();  	// 归档日志
			$('#configuration_detail_archivelogDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_warningDiv').hide();		// 异常处理
			$('#configuration_detail_warningDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_checkDiv').hide();			// 校验
			$('#configuration_detail_checkDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_validateDataDiv').hide();	// 有效数据
			$('#configuration_detail_validateDataDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_snapshotDiv').hide();		// 快照
			$('#configuration_detail_snapshotDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_incrementDiv').hide();		// 增量
			$('#configuration_detail_incrementDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_performanceDiv').hide();	// 性能
			$('#configuration_detail_performanceDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_retryStrategyDiv').show(); // 重试策略
			$('#configuration_detail_overloadStrategyDiv').show(); // 过载保护
			$('#configuration_detail_ignoreResourceLimitDiv').show(); // 忽略节点资源限制
			$('#ignoreResourceLimit').html(getFlagLevelInfo(data.agentInfo.ignore_resource_limiting_flag)); // 忽略节点资源限制

			switch(dbType){
				case CONF.DB_TYPE.SQLSERVER:
					$('#configuration_detail_checkDiv').show();			// 校验
					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_sqlserver_checkDatabaseDiv').show();  	// SQL Server-检查数据库
					$('#configuration_detail_sqlserver_checksumDiv').show();  		// SQL Server-校验
					$('#configuration_detail_sqlserver_compressDiv').show();  		// SQL Server-压缩
					$('#configuration_detail_sqlserver_checkDatabase').html(getFlagLevelInfo(data.agentInfo.checkdbflag));
					$('#configuration_detail_sqlserver_checksum').html(getFlagLevelInfo(data.agentInfo.checksumflag));
					$('#configuration_detail_sqlserver_compress').html(getFlagLevelInfo(data.agentInfo.compressflag));
					break;
				case CONF.DB_TYPE.ORACLE:
					$('#configuration_detail_archivelogDiv').show();  	// 归档日志
					$('#configuration_detail_warningDiv').show();		// 异常处理
					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_oracle_archivelogBackupTimesCheckDiv').show();	// Oracle-归档日志备份次数
					$('#configuration_detail_oracle_archivelogBackupTimesCheck').html(getFlagLevelInfo(data.agentInfo.log_backup_times_flag));
					if (data.agentInfo.log_backup_times_flag) {
						$('#configuration_detail_oracle_archivelogBackupTimesDiv').show();		// Oracle-最大重复备份次数
						$('#configuration_detail_oracle_archivelogBackupTimes').html(data.agentInfo.log_backup_times + LANG.UI_PUBLIC_UNIT_COUNT);
					}
					$('#configuration_detail_oracle_archivelogBackupDaysCheckDiv').show();  // Oracle-归档日志备份天数
					$('#configuration_detail_oracle_archivelogBackupDaysCheck').html(getFlagLevelInfo(data.agentInfo.log_backup_days_flag));
					if (data.agentInfo.log_backup_days_flag) {
						$('#configuration_detail_oracle_archivelogBackupDaysDiv').show();		// Oracle-日志最近备份天数
						$('#configuration_detail_oracle_archivelogBackupDays').html(data.agentInfo.log_backup_days + LANG.UI_PUBLIC_UNIT_DAY);
					}
					$('#configuration_detail_oracle_deleteArchivelogDiv').show();			// Oracle-归档日志保留策略
					let deleteArchivelogValue = parseInt(data.agentInfo.delete_archive_log_flag);
					let deleteArchivelogDes = LANG.UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS1;  // 删除所有已备份的归档日志
					if (3 === deleteArchivelogValue) {  // 保留x天的归档日志不删除
						deleteArchivelogDes = LANG.UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS3_1
							+ data.agentInfo.last_archive_days + LANG.UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS3_2;
					} else if (2 === deleteArchivelogValue) {  // 不删除归档日志
						deleteArchivelogDes = LANG.UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS2;
					}
					$('#configuration_detail_oracle_deleteArchivelog').html(deleteArchivelogDes);
					$('#configuration_detail_oracle_checkArchivelogDiv').show();			// Oracle-检查归档日志文件
					$('#configuration_detail_oracle_checkArchivelog').html(getFlagLevelInfo(data.agentInfo.checkdbflag));
					$('#configuration_detail_oracle_skipInaccessibleFileFlagDiv').show();	// Oracle-跳过不可访问文件
					$('#configuration_detail_oracle_skipInaccessibleFileFlag').html(getFlagLevelInfo(data.agentInfo.skip_inaccessible_file_flag));
					$('#configuration_detail_oracle_skipOfflineFileFlagDiv').show();		// Oracle-跳过脱机文件
					$('#configuration_detail_oracle_skipOfflineFileFlag').html(getFlagLevelInfo(data.agentInfo.skip_offline_file_flag));
					$('#configuration_detail_oracle_compressDiv').show();					// Oracle-压缩
					$('#configuration_detail_oracle_compress').html(getFlagLevelInfo(data.agentInfo.compressflag));
					$('#configuration_detail_oracle_filesPersetDiv').show();				// Oracle-设置filesperset配置
					$('#configuration_detail_oracle_filesPerset').html(getFlagLevelInfo(data.agentInfo.set_filesperset_flag));
					if (data.agentInfo.set_filesperset_flag) {
						$('#configuration_detail_oracle_filesPersetDatafileDiv').show();		// Oracle-数据文件filesperset个数
						$('#configuration_detail_oracle_filesPersetArchivelogDiv').show();		// Oracle-归档日志文件filesperset个数
						$('#configuration_detail_oracle_filesPersetDatafile').html(data.agentInfo.datafile_filesperset_num);
						$('#configuration_detail_oracle_filesPersetArchivelog').html(data.agentInfo.archivelog_filesperset_num);
					}
					$('#configuration_detail_oracle_bctFlagDiv').show();					// Oracle-BCT配置
					$('#configuration_detail_oracle_bctFlag').html(getFlagLevelInfo(data.agentInfo.enable_bct_flag));
					$('#configuration_detail_oracle_setSectionSizeFlagDiv').show();			// Oracle-多段传输
					$('#configuration_detail_oracle_setSectionSizeFlag').html(getFlagLevelInfo(data.agentInfo.set_section_size_flag));
					if (data.agentInfo.set_section_size_flag) {
						$('#configuration_detail_oracle_setSectionSizeDiv').show();				// Oracle-分块大小
						$('#configuration_detail_oracle_setSectionSize').html(data.agentInfo.section_size + 'GB');
					}
					$('#configuration_detail_oracle_customRmanCmdDiv').show();				// Oracle-自定义RMAN命令
					if (data.agentInfo.custom_rman_cmd || data.agentInfo.custom_rman_cmd.trim()) {
						$('#configuration_detail_oracle_customRmanCmd').html(`
							<div style="border: 1px solid #E0E0E0; padding: 12px; white-space: pre; max-height: 200px; overflow: auto">${data.agentInfo.custom_rman_cmd}</div>
						`);
					} else {
						$('#configuration_detail_oracle_customRmanCmd').html(LANG.UI_DB_BACKUP_UNSET);
					}
					$('.reserveStrategyDetailWrapper').show();
					if (data.agentInfo.multi_task_flag) { // 隐藏归档日志备份任务的高级配置
						$('#configuration_detail_oracle_filesPersetDatafileDiv').hide();
						$('#configuration_detail_oracle_skipOfflineFileFlagDiv').hide();
						$('#configuration_detail_oracle_bctFlagDiv').hide();
						$('#configuration_detail_oracle_setSectionSizeFlagDiv').hide();
						$('#configuration_detail_oracle_setSectionSizeDiv').hide();
						$('.reserveStrategyDetailWrapper').hide();  // 归档日志任务隐藏保留策略
					}
					if (!special_config.oracle_kunming_route) {  // 非昆铁项目隐藏检查归档日志
						// 隐藏检查归档日志
						$('#configuration_detail_oracle_checkArchivelogDiv').hide();
					}
					break;
				case CONF.DB_TYPE.MYSQL:
				case CONF.DB_TYPE.MARIA:
					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_mysql_parallelDiv').show();		// MySQL-处理线程
					$('#configuration_detail_mysql_parallel').html(data.agentInfo.channel_count);
					$('#configuration_detail_mysql_srcCompressedDiv').show();	// MySQL-源端压缩
					$('#configuration_detail_mysql_srcCompressed').html(getFlagLevelInfo(data.agentInfo.compressflag))
					break;
				case CONF.DB_TYPE.DM:
					$('#configuration_detail_archivelogDiv').show();  	// 归档日志
					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_dm_deleteArchivelogDiv').show();	// DM-删除归档日志
					$('#configuration_detail_dm_deleteArchivelog').html(getFlagLevelInfo(data.agentInfo.delete_archive_log_flag));
					$('#configuration_detail_dm_compressDiv').show();			// DM-压缩
					$('#configuration_detail_dm_compress').html(getFlagLevelInfo(data.agentInfo.compressflag));
					if (data.agentInfo.compressflag) {
						$('#configuration_detail_dm_compressLevelDiv').show();		// DM-压缩等级
						$('#configuration_detail_dm_compressLevel').html(data.agentInfo.compress_level);
					}
					break;
				case CONF.DB_TYPE.POSTGRE:
				case CONF.DB_TYPE.ANTDB:
				case CONF.DB_TYPE.KINGBASE:
				case CONF.DB_TYPE.UXDB:
				case CONF.DB_TYPE.HIGHGO:
				case CONF.DB_TYPE.OPENGAUSS:
				case CONF.DB_TYPE.VASTBASE:
					$('#configuration_detail_archivelogDiv').show();  	// 归档日志
					$('#configuration_detail_pg_deleteArchivelogDiv').show();	// PG-删除归档日志配置
					$('#configuration_detail_pg_deleteArchivelog').html(data.agentInfo.delete_archive_log_flag);
					$('#configuration_detail_pg_archivelogAlarmDiv').show();	// PG-归档日志告警
					$('#configuration_detail_pg_archivelogAlarm').html(getFlagLevelInfo(data.agentInfo.details.warn_check));
					if (data.agentInfo.details.warn_check) {
						$('#configuration_detail_pg_archivelogAlarmTypeDiv').show();	// PG-告警指标
						if (parseInt(data.agentInfo.details.warn_type) === 1) {  // 百分比
							$('#configuration_detail_pg_archivelogAlarmType').html(LANG.UI_STORAGE_WARNING_TYPE_PERCENT);
						} else {  // 大小
							$('#configuration_detail_pg_archivelogAlarmType').html(LANG.UI_STORAGE_WARNING_TYPE_SIZE);
						}
						$('#configuration_detail_pg_archivelogAlarmThresholdDiv').show();	// PG-告警閾值
						$('#configuration_detail_pg_archivelogAlarmThreshold').html(data.agentInfo.details.warn_value);
					}
					// 目前没有使用pg的源端压缩
					// $('#configuration_detail_pg_srcCompressedDiv').show();	// PG-源端压缩
					break;
				case CONF.DB_TYPE.MONGODB:
					$('.totalsizeDiv').show();	//显示总容量
					$('.progressDiv').show();	//显示进度条

					// $('#configuration_detail_validateDataDiv').show();	// 有效数据
					// $('#configuration_detail_snapshotDiv').show();		// 快照
					// $('#configuration_detail_incrementDiv').show();		// 增量
					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_mongodb_parallelNumDiv').show();		// MongoDB-客户端并行数量
					$('#configuration_detail_mongodb_parallelNum').html(data.agentInfo.max_object_transport_parallel_nums);
					if (1 === parseInt(data.timepoint_recovery_type)) {
						if (data.agentInfo.source_storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
							$('#configuration_detail_performanceDiv').hide();	// 性能
						}
					}
					break;
				case CONF.DB_TYPE.TIDB:
					if (typeof data.agentInfo.depend_task_uuid === 'string' && data.agentInfo.depend_task_uuid.length) {
						$('.dbStartDiv').hide();
					}

					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_tidb_compressMethodDiv').show();	// TIDB-压缩算法
					if (parseInt(data.agentInfo.compress_method) === TIDB_COMPRESS_METHOD_ENUM.zstd) {
						$('#configuration_detail_tidb_compressMethod').html('zstd');
						$('#configuration_detail_tidb_compressLevelDiv').show();	// TIDB-压缩算法
						$('#configuration_detail_tidb_compressLevel').html(data.agentInfo.compress_level);
					} else if (parseInt(data.agentInfo.compress_method) === TIDB_COMPRESS_METHOD_ENUM.lz4) {
						$('#configuration_detail_tidb_compressMethod').html('lz4');
					} else {
						$('#configuration_detail_tidb_compressMethod').html('snappy')
					}
					break;
				case CONF.DB_TYPE.SAPHANA:
					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_saphana_compressDiv').show();	// SAP HANA-压缩
					$('#configuration_detail_saphana_compress').html(getFlagLevelInfo(data.agentInfo.compressflag));
					break;
			}

			// 重试策略
			setBackupRetryStrategyDetail(data.retry_strategy);
			setDbOperate(dbType, data.agentInfo);  // 设置<数据库列表>的<操作>框选项
		}else{
			$('.dbStartDiv').hide();
			// $('#configurationLi').show();
			$('#recoveryConfigLi').show();
			$('.backupDiv').hide();
			$('#recoveryBackupNodeWrapper').show();
//			$('#recoveryTransport').html(data.transportStrategy.mode);	//恢复传输策略
			$('.recoveryDiv').show();	//显示恢复配置
			$('.backup_div').hide();
			$('.next-time-div').hide();
			$('#createTime').html(data.createTime);
			// 下次运行时间
			if (parseInt(data.timepoint_recovery_type) === 2) {
				$('.next-time-div').show();
				$('#nextTime').html(data.nextTime);
				$('.nodeDiv').hide();  // 演练任务隐藏节点
				$('#recoveryBackupNodeWrapper').hide();
			}


			// 详情
			// 通用策略
			setRecoveryCommonConfigDetail(data);

			// 传输策略
			setRecoveryTransferConfigDetail(data);

			// 安全策略
			setRecoverySafeConfigStrategyDetail(data.safe_config_strategy, data);

			// 高级配置
			$('#configuration_detail_archivelogDiv').hide();  	// 归档日志
			$('#configuration_detail_archivelogDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_warningDiv').hide();		// 异常处理
			$('#configuration_detail_warningDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_checkDiv').hide();			// 校验
			$('#configuration_detail_checkDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_validateDataDiv').hide();	// 有效数据
			$('#configuration_detail_validateDataDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_snapshotDiv').hide();		// 快照
			$('#configuration_detail_snapshotDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_incrementDiv').hide();		// 增量
			$('#configuration_detail_incrementDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_performanceDiv').hide();	// 性能
			$('#configuration_detail_performanceDiv .strategy-group__form .strategy-group__form__item').hide();
			$('#configuration_detail_retryStrategyDiv').show(); // 重试策略
			$('#configuration_detail_overloadStrategyDiv').show(); // 过载保护
			$('#configuration_detail_ignoreResourceLimitDiv').show(); // 忽略节点资源限制
			$('#ignoreResourceLimit').html(getFlagLevelInfo(data.agentInfo.ignore_resource_limiting_flag)); // 忽略节点资源限制


			$('.sqlserverReDiv').hide();	//sqlserver
			$('.oracleReDiv').hide();	//oracle
			$('.mysqlRedirectDiv').hide();	//mysql重定向目录
			$('.specifiedPathDiv').hide();	//指定文件夹路径
			$('.mongodbInstancePathDiv').hide();	//MongoDB-实例路径
			$('.logrollDiv').hide();
			$('.archiverollDiv').hide();
			$('.oraclePathDiv').hide();
			$('.exportDiv').hide();
			$('.postgreReDiv').hide();
			$('.mysqlOpenDbDiv').hide();
			$('.mysqlStartTypeDiv').hide();	//重启数据库服务名
			$('.commandDiv').hide();	//重启数据库服务名
			$('.mysqlCustomStartDiv').hide();	//重启数据库服务名
			$('.mysqlCustomStopDiv').hide();	//重启数据库服务名
			$('.openDbDiv').hide();
			$('.dbConfigDiv').hide();  // 数据库配置
			$('.pgCustomArchivelogDiv').hide();
			$('.pgDataFilePathDiv').hide();  // 数据库文件目录
			$('.pgArchivelogFilePathDiv').hide();  // 归档日志文件目录
			// Oracle还原归档日志文件
			$('.oracleRestoreArchivelogPathDiv').hide();
			$('.oracleRestoreArchivelogTypeDiv').hide();
			$('.oracleRestoreArchivelogTimeDiv').hide();
			$('.oracleRestoreArchivelogSCNDiv').hide();
			$('.oracleRecoveryContentDiv').hide();  // Oracle-恢复内容
			$('.sapHanaRecoveryTimeTypeDiv').hide();    // SAP HANA-恢复时间点
			$('.sapHanaRecoveryTimeDiv').hide();    // SAP HANA-恢复时间
			$('.sapHanaRecoveryTimepointDiv').hide();    // SAP HANA-备份点
			$('.oracleRecoveryTimepointDiv').hide();  // Oracle-时间点恢复方式
			$('.oracleRecoveryTimeSelectDiv').hide();  // Oracle-恢复时间
			$('.oracleRecoveryDataPathDiv').hide();  // Oracle-数据恢复路径
			$('.tidbRecoveryTimepointDiv').hide();   // TiDB-时间点恢复方式
			$('.tidbRecoveryTimeSelectDiv').hide();   // TiDB-恢复时间
			$('.oracleCreateNewInstanceDiv').hide();
			$('.totalsizeDiv').show();	//显示总容量
			var agentInfo = data.agentInfo;
			//恢复方式
			$('.recoverTypeDiv').show();
			$('#recoverType').html(agentInfo.recovery_type);
			let recoveryMode = parseInt(agentInfo.recovery_mode);
			switch(dbType){
				case CONF.DB_TYPE.SQLSERVER:
					$('.dbConfigDiv').show();
					$('#dbConfigBtn').unbind('click').on('click', function () {
						$('#dbConfigModal').modal({'width':'850px', 'height':'460px'});
						$('.dbConfigOldName').html(LANG.UI_DB_OLD_DB_NAME);
						$('.dbConfigNewName').html(LANG.UI_DB_NEW_DB_NAME);
						loadDbConfigContent(agentInfo);
					});
					break;
				case CONF.DB_TYPE.ORACLE:
					let dbConfig = JSON.parse(agentInfo.db_config);
					if (
						null === dbConfig ||
						typeof dbConfig.rac_flag === 'undefined' ||
						(parseInt(dbConfig.rac_flag) === 0 && agentInfo.target_cluster_flag) ||
						recoveryMode === 5 ||
						recoveryMode === 7
					) {
						//
					} else {
						$('.openDbDiv').show();
					}
					if (2 === parseInt(data.timepoint_recovery_type)) {  // 定时恢复最新点有打开数据库
						$('.openDbDiv').show();
					}
					// 原始数据库覆盖和指定文件夹恢复才会自定义配置文件
					if (1 === parseInt(data.timepoint_recovery_type) && (
						recoveryMode === 1 || recoveryMode === 3 ||
						recoveryMode === 8 || recoveryMode === 9
					)) {
						// 判断是否勾选恢复这些文件
						$('.oracleRecoveryContentDiv').show();  // Oracle-恢复内容
						$('#oracleRecoveryContentBtn').show().unbind('click').on('click', function () {
							initOracleRecoveryContentTree(agentInfo.oracle_detail.recovery_content);

							// 判断这些文件的内容、目录是否为空
							let showPfileFlag = !!agentInfo.oracle_detail.pfile_info.file_content.length;
							let showListenerFlag = !!agentInfo.oracle_detail.listener_info.file_content.length;
							let showTnsnamesFlag = !!agentInfo.oracle_detail.tnsnames_info.file_content.length;
							let showSqlnetFlag = !!agentInfo.oracle_detail.sqlnet_info.file_content.length;
							$('#oracleRecoveryContentDrawer').drawer('show');
							if (showPfileFlag) {
								$('.pfileDiv').show();
								$('#pfile-content').html(convertSpaceCharToSign(agentInfo.oracle_detail.pfile_info.file_content));
							} else {
								$('.pfileDiv').hide();
							}
							if (showListenerFlag) {
								$('.listenerDiv').show();
								$('#listener-content').html(convertSpaceCharToSign(agentInfo.oracle_detail.listener_info.file_content));
							} else {
								$('.listenerDiv').hide();
							}
							if (showTnsnamesFlag) {
								$('.tnsnamesDiv').show();
								$('#tnsnames-content').html(convertSpaceCharToSign(agentInfo.oracle_detail.tnsnames_info.file_content));
							} else {
								$('.tnsnamesDiv').hide();
							}
							if (showSqlnetFlag) {
								$('.sqlnetDiv').show();
								$('#sqlnet-content').html(convertSpaceCharToSign(agentInfo.oracle_detail.sqlnet_info.file_content));
							} else {
								$('.sqlnetDiv').hide();
							}
						});
					}
					$('#openDb').html(getFlagLevelInfo(agentInfo.open_db_flag));
					$('.oracleReDiv').show();	//oracle
					if (recoveryMode === 5) {
						$('.exportDiv').show();
						$('#exportDir').html(agentInfo.data_file_path);
					} else if (recoveryMode === 7) {
						$('.oracleRestoreArchivelogPathDiv').show();
						$('#restoreArchivelogPath').html(agentInfo.log_file_path);
						$('.oracleRestoreArchivelogTypeDiv').show();
						$('#oracleRestoreArchivelogType').html(LANG.UI_DB_RESTORE_ARCHIVELOG_TYPE_TIME);
						$('.oracleRestoreArchivelogTimeDiv').show();
						$('#oracleRestoreArchivelogTime').html(agentInfo.log_restore_start_time + ' ~ ' + agentInfo.log_restore_end_time);
						$('.archiverollDiv').hide();
					} else if (recoveryMode === 3) {
						if (agentInfo.data_file_path.length) {
							$('#oraclePath').html(agentInfo.data_file_path);
							$('.oraclePathDiv').show();
						}
					} else if (recoveryMode === 8) {  // 完全恢复
						$('.archiverollDiv').hide();
						// $('.oracleRecoveryDataPathDiv').show();
						// if (agentInfo.data_file_path.length) {
						// 	$('#oracleRecoveryDataPath').html(agentInfo.data_file_path);
						// } else {
						// 	$('#oracleRecoveryDataPath').html(LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY);
						// }
					} else if (recoveryMode === 9) {  // 不完全恢复
						if (1 === parseInt(data.timepoint_recovery_type)) {
							$('.archiverollDiv').hide();
							$('.oracleRecoveryTimepointDiv').show();
							$('.oracleRecoveryDataPathDiv').show();
							let oracleRecoveryTimepointFlag = parseInt(agentInfo.recovery_time_flag);
							if (oracleRecoveryTimepointFlag === 2) {  // 时间恢复
								$('.oracleRecoveryTimeSelectDiv').show();  // Oracle-恢复时间
								$('#oracleRecoveryTimeSelect').html(agentInfo.recovery_time);
								$('#oracleRecoveryTimepoint').html(LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_TIMEPOINT_OPTIONS2);
							} else if (oracleRecoveryTimepointFlag === 1) {  // 恢复最新点
								$('#oracleRecoveryTimepoint').html(LANG.UI_DB_RECOVERY_ORACLE_RECOVERY_TIMEPOINT_OPTIONS1);
							}
							if (agentInfo.data_file_path.length) {
								$('#oracleRecoveryDataPath').html(agentInfo.data_file_path);
							} else {
								$('#oracleRecoveryDataPath').html(LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY);
							}
						} else {
							$('.oracleRecoveryDataPathDiv').show();
							if (agentInfo.data_file_path.length) {
								$('#oracleRecoveryDataPath').html(agentInfo.data_file_path);
							} else {
								$('#oracleRecoveryDataPath').html(LANG.UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY);
							}
						}
						if (agentInfo.new_db_name.length) {
							$('.oracleCreateNewInstanceDiv').show();
							$('#oracleCreateNewInstance').off().on('click', () => {
								$('#oracleCreateNewInstanceDrawer').drawer('show');
								$('#oracleCreateNewInstanceName').html(agentInfo.new_db_name);
								$('#oracleCreateNewInstanceSpfile').html(agentInfo.oracle_detail.create_instance_pfile_info);
							});
						} else {
							$('.oracleCreateNewInstanceDiv').hide();
						}
					}
					break;
				case CONF.DB_TYPE.MYSQL:
				case CONF.DB_TYPE.MARIA:
					$('.mysqlRedirectDiv').hide();	//隐藏从定向目录
					$('#command').html(agentInfo.details.command);	//数据库服务名
					if  (2 === parseInt(data.timepoint_recovery_type)) {
						$('.mysqlStartTypeDiv').show();	//重启数据库服务名
						if (parseInt(agentInfo.details.start_type) === 1) {  // 服务启动
							$('#mysqlStartType').html(LANG.UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS1);
							$('.commandDiv').show();
							$('#command').html(agentInfo.details.command);
						} else {
							$('#mysqlStartType').html(LANG.UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS2);
							$('.mysqlCustomStartDiv').show();
							$('.mysqlCustomStopDiv').show();
							$('#mysqlCustomStart').html(agentInfo.details.command);
							$('#mysqlCustomStop').html(agentInfo.details.stop_command);
						}
					}
					//如果是重定向目录恢复
					if(!agentInfo.orginal_flag){
						$('#redirectPath').html(agentInfo.log_file_path);	//重定向目录
						$('.mysqlRedirectDiv').show();	//显示重定向目录
					}
					//日志回滚时间
					if(agentInfo.log_rollback_time != ""){
						$('#logtime').html(agentInfo.log_rollback_time);
						$('.logrollDiv').show();
					}
					//原数据库恢复且时间点为日志备份点
					if(agentInfo.timepoint_type != 1 && agentInfo.timepoint_type != 2 && agentInfo.recovery_mode == 1){
						$('.mysqlStartTypeDiv').show();	//重启数据库服务名
						if (parseInt(agentInfo.details.start_type) === 1) {  // 服务启动
							$('#mysqlStartType').html(LANG.UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS1);
							$('.commandDiv').show();
							$('#command').html(agentInfo.details.command);
						} else {
							$('#mysqlStartType').html(LANG.UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS2);
							$('.mysqlCustomStartDiv').show();
							$('#mysqlCustomStart').html(agentInfo.details.command);
						}
					}
					// 打开数据库
					if (agentInfo.recovery_mode == 1 && 1 === parseInt(data.timepoint_recovery_type)) {  // 指定备份点恢复
						if (agentInfo.timepoint_type == 1 || agentInfo.timepoint_type == 2) {
							$('.mysqlOpenDbDiv').show();
							$('#mysqlOpenDb').html(getFlagLevelInfo(agentInfo.open_db_flag));
							if (agentInfo.open_db_flag) {
								$('.mysqlStartTypeDiv').show();	//重启数据库服务名
								if (parseInt(agentInfo.details.start_type) === 1) {  // 服务启动
									$('#mysqlStartType').html(LANG.UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS1);
									$('.commandDiv').show();
									$('#command').html(agentInfo.details.command);
								} else {
									$('#mysqlStartType').html(LANG.UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS2);
									$('.mysqlCustomStartDiv').show();
									$('#mysqlCustomStart').html(agentInfo.details.command);
								}
							}
						}
					}
					break;
				case CONF.DB_TYPE.DM:
					//如果是指定文件夹恢复
					if(!agentInfo.orginal_flag){
						$('#specifiedPath').html(agentInfo.data_file_path);	//从定性目录
						$('.specifiedPathDiv').show();	//指定文件夹路径
					}
					//日志回滚时间
					if(agentInfo.log_rollback_time != ""){
						$('#archivelogTime').html(agentInfo.log_rollback_time);
						$('.archiverollDiv').show();
					}
					break;
				case CONF.DB_TYPE.POSTGRE:
				case CONF.DB_TYPE.ANTDB:
				case CONF.DB_TYPE.KINGBASE:
				case CONF.DB_TYPE.UXDB:
				case CONF.DB_TYPE.HIGHGO:
				case CONF.DB_TYPE.OPENGAUSS:
				case CONF.DB_TYPE.VASTBASE:
					// 打开数据库
					$('.openDbDiv').show();
					$('#openDb').html(getFlagLevelInfo(agentInfo.open_db_flag));
					//如果是指定文件夹恢复
					if (recoveryMode === 3) {
						$('#specifiedPath').html(agentInfo.data_file_path);
						$('.specifiedPathDiv').show();	//指定文件夹路径
						if (!agentInfo.log_file_path) {
							$('.pgCustomArchivelogDiv').hide();
						} else {
							$('#pgCustomArchivelog').html(agentInfo.log_file_path);
							$('.pgCustomArchivelogDiv').show();
						}
					} else if (recoveryMode === 2) {  // 新建实例恢复
						$('.pgDataFilePathDiv').show();  // 数据库文件目录
						$('.pgArchivelogFilePathDiv').show();  // 归档日志文件目录
						$('#pgDataFilePath').html(agentInfo.data_file_path);
						$('#pgArchivelogFilePath').html(agentInfo.log_file_path);
					}
					//日志回滚时间
					if(agentInfo.log_rollback_time != ""){
						$('#archivelogTime').html(agentInfo.log_rollback_time);
						$('.archiverollDiv').show();
					}
					break;
				case CONF.DB_TYPE.MONGODB:
					$('.openDbDiv').show();
					$('#openDb').html(getFlagLevelInfo(agentInfo.open_db_flag));
					if (3 === parseInt(data.agentInfo.recovery_mode)) {  // 指定文件夹恢复
						$('#mongodbInstancePath').html(agentInfo.data_file_path);	//实例
						$('.mongodbInstancePathDiv').show();	//实例路径
					}
					//日志回滚时间
					if (agentInfo.log_rollback_time) {
						$('#logtime').html(agentInfo.log_rollback_time);
						$('.logrollDiv').show();
					}
					$('#configuration_detail_performanceDiv').show();	// 性能
					$('#configuration_detail_mongodb_parallelNumDiv').show();		// MongoDB-客户端并行数量
					$('#configuration_detail_mongodb_parallelNum').html(data.agentInfo.max_object_transport_parallel_nums);
					if (data.agentInfo.source_storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
						$('#configuration_detail_performanceDiv').hide();	// 性能
					}
					break;
				case CONF.DB_TYPE.TIDB:
					$('.openDbDiv').show();
					$('#openDb').html(getFlagLevelInfo(agentInfo.open_db_flag));
					if  (1 === parseInt(data.timepoint_recovery_type)) {
						$('.tidbRecoveryTimepointDiv').show();   // TiDB-时间点恢复方式
						let tidbRecoveryTimepointFlag = parseInt(agentInfo.recovery_time_flag);
						if (tidbRecoveryTimepointFlag === 2) {  // 时间恢复
							$('.tidbRecoveryTimeSelectDiv').show();  // TiDB-恢复时间
							$('#tidbRecoveryTimeSelect').html(agentInfo.recovery_time);
							$('#tidbRecoveryTimepoint').html(LANG.UI_DB_RECOVERY_TIDB_RECOVERY_TIMEPOINT_OPTIONS2);
						} else if (tidbRecoveryTimepointFlag === 1) {  // 恢复最新点
							$('#tidbRecoveryTimepoint').html(LANG.UI_DB_RECOVERY_TIDB_RECOVERY_TIMEPOINT_OPTIONS1);
						}
					}
					break;
				case CONF.DB_TYPE.SAPHANA:
					$('.progressDiv').hide();	//SAP HANA恢复任务隐藏进度条
					$('.totalsizeDiv').hide();	//SAP HANA恢复任务隐藏总容量
					if (1 === parseInt(data.timepoint_recovery_type)) {
						$('.sapHanaRecoveryTimeTypeDiv').show();  // 恢复时间
						let sapHanaRecoveryTimeFlag = parseInt(agentInfo.recovery_time_flag);
						let msg = LANG.UI_DB_RECOVERY_SAP_HANA_RECOVERY_TIME_OPTIONS3;
						switch (sapHanaRecoveryTimeFlag) {
							case 1:
								msg = LANG.UI_DB_RECOVERY_SAP_HANA_RECOVERY_TIME_OPTIONS1;
								break;
							case 2:
								msg = LANG.UI_DB_RECOVERY_SAP_HANA_RECOVERY_TIME_OPTIONS2;
								$('.sapHanaRecoveryTimepointDiv').show();
								$('#sapHanaRecoveryTimepoint').html(agentInfo.timepoint);
								break;
							case 3:
								$('.sapHanaRecoveryTimeDiv').show();
								$('#sapHanaRecoveryTime').html(agentInfo.recovery_time);
								break;
						}
						$('#sapHanaRecoveryTimeType').html(msg);
						$('.dbConfigDiv').show();
						$('#dbConfigBtn').unbind('click').on('click', function () {
							$('#dbConfigModal').modal({'width': '850px', 'height': '460px'});
							$('.dbConfigNewName').html(LANG.UI_DB_TARGET_DATABASE);
							loadDbConfigContent(agentInfo);
						});
					} else {
						$('.sapHanaRecoveryTimeTypeDiv').show();  // 恢复时间
						$('#sapHanaRecoveryTimeType').html(LANG.UI_DB_RECOVERY_RECOVERY_TIME_OPTIONS1);
						$('.dbConfigDiv').show();
						$('#dbConfigBtn').unbind('click').on('click', function () {
							$('#dbConfigModal').modal({'width':'850px', 'height':'460px'});
							$('.dbConfigNewName').html(LANG.UI_DB_TARGET_DATABASE);
							loadDbConfigContent(agentInfo);
						});
					}
					break;
			}

			// 恢复时间策略
			$('.recovery-time-strategy-div').show();
			$('#recoveryTimeStrategy').html(timeStrategy.recovery);
			// 重试策略
			setRecoveryRetryStrategyDetail(data.retry_strategy);
		}

		if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
			//如果是标准版本,隐藏重复数据删除和深度有效数据提取显示
			$('.deduplicationdiv').hide();
		}

		initOpButton(data.agentInfo.dbtype,data.taskTypeFlag, data.agentInfo);
	}

	/**
	 * 获取Oracle恢复内容树
	 */
	const initOracleRecoveryContentTree = (recoveryContent) => {
		/**
		 * @type {Array<Object>}
		 */
		let nodes = [{
			id: 'oracle',
			pId: 0,
			name: recoveryContent.instance_name,
			title: recoveryContent.instance_name,
			isParent: true,
			open: true,
			nocheck: true,
			eventtype: 'oracle',
			icon: './img/db/oracle.png',
		}];

		// 数据库
		nodes.push({
			id: 'oracle_database',
			pId: 'oracle',
			name: LANG.UI_DB_RECOVERY_CONTENT_DB,
			title: LANG.UI_DB_RECOVERY_CONTENT_DB,
			isParent: true,
			open: true,
			eventtype: 'database',
			icon: './img/vm/host.png',
			nocheck: true,
		});

		// 实例
		nodes.push({
			id: 'oracle_database_instance',
			pId: 'oracle_database',
			name: recoveryContent.instance_name,
			title: recoveryContent.instance_name,
			isParent: true,
			open: true,
			eventtype: 'instance',
			icon: './img/vm/host.png',
			nocheck: true,
		});

		// 控制文件
		nodes.push({
			id: 'oracle_control',
			pId: 'oracle',
			name: LANG.UI_DB_RECOVERY_CONTENT_CONTROL_FILE,
			title: LANG.UI_DB_RECOVERY_CONTENT_CONTROL_FILE,
			isParent: false,
			eventtype: 'control_file',
			icon: './img/fs/wenjianjiaopen.png',
			nocheck: true,
		});

		// 配置文件
		nodes.push({
			id: 'oracle_config',
			pId: 'oracle',
			name: LANG.UI_DB_RECOVERY_CONTENT_CONFIG_FILE,
			title: LANG.UI_DB_RECOVERY_CONTENT_CONFIG_FILE,
			isParent: true,
			open: true,
			eventtype: 'config_file',
			icon: './img/fs/wenjianjiaopen.png',
			nocheck: true,
		});

		// 配置文件
		for (const configFileItem of recoveryContent.config_file_list) {
			let name = configFileItem.name;
			if (configFileItem.path) {
				name += '(' + configFileItem.path + ')';
			}
			nodes.push({
				id: 'oracle_config_' + name,
				pId: 'oracle_config',
				name: name,
				title: name,
				isParent: false,
				eventtype: 'config_file_item',
				icon: './img/fs/wenjian.png',
				nocheck: true,
			});
		}

		// 表空间
		for (const tablespaceInfo of recoveryContent.tablespace_list) {
			nodes.push({
				id: 'oracle_database_instance_' + tablespaceInfo.table_space_name,
				pId: 'oracle_database_instance',
				name: tablespaceInfo.table_space_name,
				title: tablespaceInfo.table_space_name,
				isParent: true,
				open: true,
				eventtype: 'table_space',
				icon: './img/platform/storage.png',
				nocheck: true,
			});
			if (!Array.isArray(tablespaceInfo.data_file_list)) {
				continue;
			}
			for (const datafileName of tablespaceInfo.data_file_list) {
				nodes.push({
					id: 'oracle_database_instance_' + tablespaceInfo.table_space_name + '_data_file_' + datafileName,
					pId: 'oracle_database_instance_' + tablespaceInfo.table_space_name,
					name: datafileName,
					title: datafileName,
					isParent: false,
					eventtype: 'data_file',
					icon: './img/fs/wenjian.png',
					nocheck: true,
				});
			}
		}

		$.fn.zTree.init($('#oracle-recovery-content-tree'), {
			data: {
				simpleData: {
					enable: true,
					idKey: 'id',
					pIdKey: 'pId',
					rootPId: 0,
				},
				key: {
					title: 'title',
				},
			},
		}, nodes);
	};

	var loadDbConfigContent = function (agentInfo) {
		let dbConfigContent = $('#db-config-content');
		if (!dbConfigContent.hasClass('isLoadData')) {
			let recoveryMode = parseInt(agentInfo.recovery_mode);
			let dbType = parseInt(agentInfo.dbtype);
			let newDbVisible = false;
			let datafileVisible = false;
			let logfileVisible = false;
			let logTimeVisible = true;
			let recoveryTimeVisible = false;
			let initLogAreaVisible = false;
			switch (dbType) {
				case CONF.DB_TYPE.SQLSERVER:
					if (recoveryMode === 2) {
						newDbVisible = true;
						datafileVisible = true;
						logfileVisible = true;
					}
					break;
				case CONF.DB_TYPE.SAPHANA:
					logTimeVisible = false;
					recoveryTimeVisible = true;
					initLogAreaVisible = true;
					if (recoveryMode === 2) {
						newDbVisible = true;
					}
					break;
			}
			let dbConfigContentGrid = new Datatable();
			dbConfigContentGrid.setAjaxParam({m: CONF.M.DBPROTECT, f: 'getRecoveryDbConfig', p: {task_uuid: $("#task_uuid").val()}});
			let dataTableOpt = {
				ordering: false,
				paging: false,
				info: false,
				columnDefs: [
					{
						targets: [1],
						visible: newDbVisible,
					}, {
						targets: [2],
						visible: datafileVisible,
					}, {
						targets: [3],
						visible: logfileVisible,
					}, {
						targets: [4],
						visible: logTimeVisible,
					}, {
						targets: [5],
						visible: recoveryTimeVisible,
					}, {
						targets: [6],
						visible: initLogAreaVisible,
					}
				],
			};
			dbConfigContentGrid.init({src: dbConfigContent, dataTable: dataTableOpt})
		}
	}

	//初始化操作按钮
	var initOpButton = function(dbType, taskType, agentInfo){
		if (!initButFlag) {
			/**
			 * 1. 启动任务
			 * 2. 停止
			 * 6. 启动差异
			 * 7. 启动增量
			 * 8. 启用策略
			 * 10. 启动完备
			 * 13. 启动归档日志备份/启动日志备份
			 * @type {*[]}
			 */
			var opCode = [];
			switch (dbType) {
				case CONF.DB_TYPE.SQLSERVER:
					opCode = [10, 6, 13, 2];
					break;
				case CONF.DB_TYPE.ORACLE:
					if (agentInfo.multi_task_flag) {  // Oracle多任务(日志备份)
						opCode = [13, 2];
					} else {
						opCode = [10, 7, 6, 13, 2];
					}
					break;
				case CONF.DB_TYPE.MYSQL:
					opCode = [10, 7, 13, 2];
					break;
				case CONF.DB_TYPE.MARIA:
					opCode = [10, 7, 13, 2];
					break;
				case CONF.DB_TYPE.DM:
					opCode = [10, 7, 6, 13, 2];
					break;
				case CONF.DB_TYPE.POSTGRE:
				case CONF.DB_TYPE.ANTDB:
				case CONF.DB_TYPE.KINGBASE:
				case CONF.DB_TYPE.UXDB:
				case CONF.DB_TYPE.HIGHGO:
				case CONF.DB_TYPE.OPENGAUSS:
				case CONF.DB_TYPE.VASTBASE:
					opCode = [10, 13, 2];
					break;
				case CONF.DB_TYPE.MONGODB:
					opCode = [10, 7, 6, 13, 2];
					break;
				case CONF.DB_TYPE.TIDB:
					opCode = [10, 2];
					if (typeof agentInfo.depend_task_uuid === 'string' && agentInfo.depend_task_uuid.length) {
						opCode = [2];
					}
					break;
				case CONF.DB_TYPE.SAPHANA:
					opCode = [10, 7, 6, 13, 2];
			}

			//数据库恢复
			if(
				taskType == 29 ||
				taskType == 64
			){
				opCode = [1, 2];
			}

			var button = "";
			$.each(opCode, function(i, d){
				switch(d){
					case 1:
						button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
						break;
					case 2:
						button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
						break;
					case 6:
						button += '<li class="startDiff"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_differentia_backup me-4"></i> ' + LANG.UI_JOB_START_DIFFRENCE + '</button></li>';
						break;
					case 7:
						button += '<li class="startIncr"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_increment me-4"></i> ' + LANG.UI_JOB_START_INCREMENT + '</button></li>';
						break;
					case 8:
						button += '<li class="startStra"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_time_point me-4"></i> ' + LANG.UI_JOB_START_STRATEGY + '</button></li>';
						break;
					case 10:
						button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START_FULL + '</button></li>';
						break;
					case 13:
						if (
							dbType == CONF.DB_TYPE.ORACLE ||
							dbType == CONF.DB_TYPE.DM ||
							dbType == CONF.DB_TYPE.POSTGRE ||
							dbType == CONF.DB_TYPE.ANTDB ||
							dbType == CONF.DB_TYPE.KINGBASE ||
							dbType == CONF.DB_TYPE.UXDB ||
							dbType == CONF.DB_TYPE.HIGHGO ||
							dbType == CONF.DB_TYPE.OPENGAUSS ||
							dbType == CONF.DB_TYPE.VASTBASE
						) {
							button += '<li class="startLog"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_log me-4"></i> ' + LANG.UI_JOB_START_ARCHIVE_LOG_BACKUP + '</buttona></li>';
						} else {
							if (dbType !== CONF.DB_TYPE.SAPHANA) {  // SAP HANA不支持手动启动日志备份
								button += '<li class="startLog"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_log me-4"></i> ' + LANG.UI_JOB_START_LOG_BACKUP + '</button></li>';
							}
						}
						break;
				}
			});

			$('#dbOpList').html(button);
			initButFlag = true;
			initListener();
		}
		//初始化操作按钮
		setBtnStatus();
	}

	//得到状态的显示类型
	var getStatusLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case CONF.TASK_STATUS.WAITTING:
				levelClass = "label-info";
				break;
			case CONF.TASK_STATUS.RUNNING:
			case CONF.TASK_STATUS.PAUSED:
				levelClass = "label-success";
				break;
			case CONF.TASK_STATUS.STOPPED:
			case CONF.TASK_STATUS.PENDING:
				levelClass = "label-default";
				break;
			case CONF.TASK_STATUS.ABNORMAL:
				levelClass = "label-warning";
				break;
			case CONF.TASK_STATUS.NETWORK_FAULT:
			case CONF.TASK_STATUS.ERROR:
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

	//得到时间策略描述信息
	var getTimeStrategy = function(msg, sourceRecoveryType, taskType, timeStrategyBackupType, dbType){
		var timeInfo = {
			full: LANG.UI_PUBLIC_NOTHING,
			incr: LANG.UI_PUBLIC_NOTHING,
			diff: LANG.UI_PUBLIC_NOTHING,
			log: LANG.UI_PUBLIC_NOTHING,
			pIncr: LANG.UI_PUBLIC_NOTHING,
			recovery: LANG.UI_PUBLIC_NOTHING,
			recovery_time_strategy_type: 'immediately',
		};
		if(!msg){
			return timeInfo;
		}
		for(var i=0; i<msg.length; i++){
			var strategy = msg[i];
			var des = "";
			if(CONF.STRATEGY_TYPE.DAY == strategy.type){
				des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy, sourceRecoveryType, taskType);
			}else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
				des += getStrategyFrequency(strategy);
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy, sourceRecoveryType, taskType);
				}else{
					des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy, sourceRecoveryType, taskType);
				}

			}else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
				des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy, sourceRecoveryType, taskType);
			}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
				des += strategy.startTime;
			}else{
				des += LANG.UI_PUBLIC_NOTHING;
			}

			if (1 == strategy.mode) {  // 可能为完全备份、一次性备份、演练时间策略
				if (taskType == CONF.TASK_TYPE.DB_BACKUP && timeStrategyBackupType === 'strategy') {
					if (dbType !== CONF.DB_TYPE.TIDB) { // TiDB不支持完全备份补偿
						if (strategy.full_backup_compensation_flag) {
							des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_ON;
						} else {
							des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_OFF;
						}
					}
				}
				timeInfo.full = des;
			}else if(2 == strategy.mode){
				timeInfo.incr = des;
			}else if(3 == strategy.mode){
				timeInfo.diff = des;
			}else if(4 == strategy.mode){
				timeInfo.log = des;
			} else if (9 == strategy.mode) {
				timeInfo.pIncr = des;
			}
			if (sourceRecoveryType === 2) {
				timeInfo.recovery = des;
				timeInfo.recovery = LANG.UI_GLOBAL_STRATEGY_TYPE_BY_STRATEGY + ', ' + des;
				timeInfo.recovery_time_strategy_type = 'strategy'
			}
			if (taskType == 29) {
				timeInfo.recovery = LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME + ', ' + LANG.UI_JOB_TIMING_RECOVER_TIME + ': ' + des;
				timeInfo.recovery_time_strategy_type = 'once_time'
			}
		}
		if (!msg.length && taskType == 29) {  // 立即恢复
			timeInfo.recovery = LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE;
			timeInfo.recovery_time_strategy_type = 'immediately'
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

	var getEachStrategy = function(strategy, sourceRecoveryType, taskType){
		var desEach = '';
		desEach += strategy.startTime;
		//如果是英文版 需要加空格
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			desEach += " "; //策略开始时间
		}
		desEach += LANG.UI_STRATEGY_START;
		if (sourceRecoveryType !== 2 && taskType != 29) {
			if(strategy.rollFlag){
				desEach += ", " + LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
			}else{
				desEach += ", " + LANG.UI_STRATEGY_ROLL_NO;
			}
		}
		desEach += "<br>";
		return desEach;
	}

	//得到保留策略描述信息
	var getReservedStrategy = function(msg, dbType){
		var reservedStr = '';
		if(!msg){
			reservedStr = LANG.UI_PUBLIC_NOTHING;
			return reservedStr;
		}
		if (parseInt(dbType) === CONF.DB_TYPE.MONGODB) {
			if (parseInt(msg.strategyMode) === CONF.RESERVE_STRATEGY_MODE.CHIAN) {  // 备份链
				reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
			} else {
				reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
			}
		} else {
			reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': ' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
		}
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_NUM + '<br>';
		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_DAY + '<br>';
		}
		let methoddes = LANG.UI_STRATEGY_VALUE;
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == msg.type){
			methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY;
		}
		reservedStr += methoddes + ': ' + msg.value + '<br>';
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

	const registerRunningLogListener = () => {
		$('#runninglog').on('click', '.error-detail-link', function () {
			$('#runningLogDetailDrawer').drawer('show');
			Metronic.blockUI({target: '#runningLogDetailDrawer', animate: true});
			$.ajax({
				type: CONF.AJAXMETHOD,
				url: CONF.AJAXPATH,
				data: {m: CONF.M.LOG, f: 'getRunningLogDetails', p: JSON.stringify({log_id: $(this).data('id')})},
				success: result => {
					Metronic.unblockUI('#runningLogDetailDrawer');
					result = JSON.parse(result);
					$('#runningLogDetailDrawer .drawer-header span.text').html(LANG.UI_PUBLIC_ERROR_DETAIL);
					// $('#runningLogDetailDrawer .drawer-header span.text').html(result.title + ' ------ ' + result.object_name);
					let content = ``;
					for (const logError of result.info_list) {
						content += `${logError}<br>`;
					}
					$('#runningLogDetailDrawer .drawer-body .portlet-body').html(content);
				}
			});
		}).on('click', '.script-detail-link', function () {
			$('#scriptContentDrawer').drawer('show');
			Metronic.blockUI({target: '#scriptContentDrawer', animate: true});
			$.ajax({
				type: CONF.AJAXMETHOD,
				url: CONF.AJAXPATH,
				data: {m: CONF.M.LOG, f: 'getRunningLogScriptDetails', p: JSON.stringify({log_id: $(this).data('id')})},
				success: result => {
					Metronic.unblockUI('#scriptContentDrawer');
					result = JSON.parse(result);
					setScriptDrawerContent(result.script_name, result.script_type, result.script_content);
				}
			});
		});
		$('#scriptContentDrawer .drawer-footer button.cancel').off('click').on('click', () => {
			$('#scriptContentDrawer').drawer('hide');
		});
	}

    //初始化日志表格
    var initLogGrid = function(){
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
		registerRunningLogListener();
    }


    //初始化历史任务表格
    var initHistoryGrid = function(){
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
    		var content = '<span class="label label-sm ' + labelClass + '">' +
    			'<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="right" data-content="' +
    			data[7].popover + '" >'+ data[2] + '</a></span>';
    		$(div).html(content);
    	}
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [5, "desc"]
                ],
    	};
    	var init = function(){
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#task_uuid").val();
    			data = {m:CONF.M.JOB,f:'getDBDetailsHistory',p:data};
    			grid.setAjaxParam(data);
    	    	grid.init({src: $("#historytable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:initHistoryRow});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
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
            sOut += getDBDetailsInfo(data[8]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
			// 注册事件
			registerErrorDetail(data);
    	}

		var registerErrorDetail = (data) => {
			// 注册打开错误详情
			$('#historytable .error-detail-link').off().on('click', function() {
				$('#runningLogDetailDrawer .drawer-body .portlet-body').html(``);
                let index = $(this).data('index');
                let agentUuid = $(this).data('agent-uuid');
                let instanceName = $(this).data('instance-name');
				let errorDetails = JSON.parse(data[8].error_details);
				$('#runningLogDetailDrawer').drawer('show');
				$('#runningLogDetailDrawer .drawer-header span.text').html(LANG.UI_PUBLIC_ERROR_DETAIL);
				let errorDetail = null;
				for (const key in errorDetails) {
					let _errorDetail = errorDetails[key];
                    if (_errorDetail.agent_uuid === agentUuid && _errorDetail.object_name === instanceName) {
                        errorDetail = _errorDetail;
                        break;
                    }
				}
				let msg = ``;
				// let title = ``;
                if (errorDetail) {
					for (const logError of errorDetail.info_list) {
						msg += `${logError}<br>`;
					}
					// title = errorDetail.title + ' ------ ' + errorDetail.object_name
				}
				// $('#runningLogDetailDrawer .drawer-header span.text').html(title);
				$('#runningLogDetailDrawer .drawer-body .portlet-body').html(msg);
			});
			// 恢复验证报告
			$('.db-validate-report-chk').on('click', function () {
				let dbIndex = $(this).data('db-index');
				let dbInfo = data[8].info[dbIndex];
				let reqData = {
					job_uuid: data[8].history_id,
					instance_name: dbInfo.instance_name,
					db_name: dbInfo.db_name,
					db_type: dbInfo.db_type,
				};
				Metronic.blockUI({target: '#dbValidateReportDrawer', animate: true});
				pAjaxRequest(reqData, `/api/v1/db/jobs/validate_report`, 'GET', res => {
					Metronic.unblockUI('#dbValidateReportDrawer');
					if (!res.success) {
						UIToastr.showError(LANG.UI_PUBLIC_ERROR, res.msg);
						return;
					}
					$('#dbValidateReportDrawer').data('db-index', dbIndex).drawer('show');
					$('#dbValidateReport').html(res.data.content);
				});
			});
            // 注册数据库验证报告下载事件
            $('#dbValidateReportDrawer .drawer-footer button.download').off('click').on('click', () => {
                let dbIndex = $('#dbValidateReportDrawer').data('db-index');
                let dbInfo = data[8].info[dbIndex];
                let reqData = {
                    job_uuid: data[8].history_id,
                    instance_name: dbInfo.instance_name,
                    db_name: dbInfo.db_name,
                    db_type: dbInfo.db_type,
                };
                Metronic.blockUI({target: '#dbValidateReportDrawer', animate: true});
                pAjaxRequest(reqData, `/api/v1/db/jobs/validate_report`, 'POST', res => {
                    Metronic.unblockUI('#dbValidateReportDrawer');
                    if (!res.success) {
                        UIToastr.showError(LANG.UI_PUBLIC_ERROR, res.msg);
                        return;
                    }
                    window.location.href = res.data.report_url;
                });
            });
            // 注册数据库验证报告发送事件
            $('#dbValidateReportDrawer .drawer-footer button.send-email').off('click').on('click', () => {
                let dbIndex = $('#dbValidateReportDrawer').data('db-index');
                let dbInfo = data[8].info[dbIndex];
                let reqData = {
                    job_uuid: data[8].history_id,
                    instance_name: dbInfo.instance_name,
                    db_name: dbInfo.db_name,
                    db_type: dbInfo.db_type,
                };
                Metronic.blockUI({target: '#dbValidateReportDrawer', animate: true});
                pAjaxRequest(reqData, `/api/v1/db/jobs/validate_report/email`, 'POST', res => {
                    Metronic.unblockUI('#dbValidateReportDrawer');
                    if (!res.success) {
						if (!res.message) {
							UIToastr.showError(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL_ERROR);
						} else {
							UIToastr.showError(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, res.message);
						}
						return;
                    }
                    UIToastr.showSuccess(LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL, LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SEND_EMAIL_SUCCESS);
                });
            });
			// 注册数据库脚本内容
            for (const dbIndex in data[8].info) {
                let dbInfo = data[8].info[dbIndex];
                if (28 === parseInt(data[8].taskType)) {
                    let scriptContentList = [{
                        title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
                        script_list: dbInfo.before_task_script,
                    }, {
                        title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
                        script_list: dbInfo.after_task_script,
                    }];
                    registerScriptEvent(dbIndex, scriptContentList);
                } else {
                    let scriptContentList = [{
                        title: LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT_SHOW,
                        script_list: dbInfo.before_task_script
                    }, {
                        title: LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT_SHOW,
                        script_list: dbInfo.after_task_script
                    }];
                    if (2 === parseInt(dbInfo.timepoint_recovery_type)) {
                        scriptContentList.push({
                            title: LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SHOW,
                            script_list: dbInfo.verification_script,
                        });
                    }
                    registerScriptEvent(dbIndex, scriptContentList);
                }
            }
		};

    	//得到数据库详情
    	var getDBDetailsInfo = function(data){
    		if(!data.info){
    			return LANG.UI_PUBLIC_NOTHING;
    		}

    		var getBackupDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
        		thead += '<th width="12%">' + LANG.UI_DB_NAME + '</th>';
        		thead += '<th width="10%">' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
        		thead += '<th width="14%">' + LANG.UI_DB_PATH + '</th>';
        		thead += '<th width="10%">' + LANG.UI_DB_AVE_SPEED + '</th>';
        		thead += '<th width="12%">' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>';
        		thead += '<th width="12%">' + LANG.UI_PUBLIC_REAL_SIZE + '</th>';
				thead += `<th width="10%">${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</th>`;
        		thead += '<th width="10%">' + LANG.UI_VISUAL_RESULT + '</th>';
        		thead += '<th width="10%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';

        		var tbody = '<tbody>';
        		for (var i = 0; i < data.info.length; i++) {
        			if (!i % 2) {
        				tbody += '<tr role="row" class="odd">';
        			} else {
        				tbody += '<tr role="row" class="even">';
        			}
        			tbody += '<td>' + data.info[i].db_name + '</td>';
        			tbody += '<td>' + data.info[i].backup_mode + '</td>';
        			tbody += '<td>' + data.info[i].dir_path + '</td>';
        			tbody += '<td>' + data.info[i].transfer_speed + '</td>';
        			tbody += '<td>' + data.info[i].transport_size + '</td>';
        			tbody += '<td>' + data.info[i].real_size + '</td>';
					// 脚本配置
					if (
						(Array.isArray(data.info[i].before_task_script) && data.info[i].before_task_script.length > 0) ||
						(Array.isArray(data.info[i].after_task_script) && data.info[i].after_task_script.length > 0)
					) {
						let beforeTaskScript = [];
						let afterTaskScript = [];
						if (Array.isArray(data.info[i].before_task_script) && data.info[i].before_task_script.length > 0) {
							beforeTaskScript = data.info[i].before_task_script;
						}
						if (Array.isArray(data.info[i].after_task_script) && data.info[i].after_task_script.length > 0) {
							afterTaskScript = data.info[i].after_task_script;
						}
						let scriptContentList = [{
							title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
							script_list: beforeTaskScript
						}, {
							title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
							script_list: afterTaskScript
						}];
						tbody += `<td>${renderScript(i, scriptContentList)}</td>`;
					} else {
						tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
					}
        			tbody += '<td>' + data.info[i].task_status + '</td>';
					if (data.error_details) {
						try {
							let errorDetails = JSON.parse(data.error_details);
							if (Array.isArray(errorDetails) && errorDetails.length > 0) {
								let errorDetailFlag = false;
								for (const errorDetail of errorDetails) {
									if (errorDetail.agent_uuid == data.info[i].agent_uuid && errorDetail.object_name == data.info[i].instance_name) {
										errorDetailFlag = true;
										break;
									}
								}
								if (errorDetailFlag) {
									tbody += `<td>${data.info[i].error_code}, 
										<u class="text-success error-detail-link" style="cursor: pointer" data-index="${i}"
											data-agent-uuid="${data.info[i].agent_uuid}" data-instance-name="${data.info[i].instance_name}">
											${LANG.UI_PUBLIC_ERROR_DETAIL_LINK}
										</u>
									</td>`;
								} else {
									tbody += `<td>${data.info[i].error_code}</td>`;
								}
							} else {
								tbody += `<td>${data.info[i].error_code}</td>`;
							}
						} catch (e) {
							tbody += `<td>${data.info[i].error_code}</td>`;
						}
					} else {
						tbody += `<td>${data.info[i].error_code}</td>`;
					}

        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		return thead + tbody;
    		}

    		var getRecoveryDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
				thead += '<th width="10%">' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>';
        		thead += '<th width="12%">' + LANG.UI_JOB_HIS_SRC_PATH + '</th>';
        		thead += '<th width="12%">' + LANG.UI_JOB_HIS_DES_PATH + '</th>';
        		thead += '<th width="8%">' + LANG.UI_JOB_HIS_TOTAL_SIZE + '</th>';
        		thead += '<th width="8%">' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>';
        		thead += '<th width="8%">' + LANG.UI_JOB_HIS_REAL_SIZE + '</th>';
				thead += `<th width="6%">${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}</th>`;
				if (2 === parseInt(data.info[0].timepoint_recovery_type)) {  // 定时恢复最新备份点的验证报告
					thead += '<th width="8%">' + LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT + '</th>';
				}
        		thead += '<th width="6%">' + LANG.UI_VISUAL_RESULT + '</th>';
        		thead += '<th width="12%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';
        		var tbody = '<tbody>';
				let recoveryMode = parseInt(data.info[0].recovery_mode);
        		for (var i = 0; i < data.info.length; i++) {
					let dbType = parseInt(data.info[i].db_type);
        			if (!i % 2) {
        				tbody += '<tr role="row" class="odd">';
        			} else {
        				tbody += '<tr role="row" class="even">';
        			}
					tbody += '<td>' + data.info[i].timepoint_des + '</td>';
        			tbody += '<td>' + data.info[i].dir_path + '</td>';
					tbody += '<td>' + data.info[i].des_dir_path + '</td>';
					if (dbType === CONF.DB_TYPE.SAPHANA) {  // SAP HANA总大写设置传输大小
						tbody += '<td>' + data.info[i].real_size + '</td>';
					} else {
						tbody += '<td>' + data.info[i].db_size + '</td>';
					}
        			tbody += '<td>' + data.info[i].transport_size + '</td>';
        			tbody += '<td>' + data.info[i].real_size + '</td>';
					// 脚本配置
					if (
						(Array.isArray(data.info[i].before_task_script) && data.info[i].before_task_script.length > 0) ||
						(Array.isArray(data.info[i].after_task_script) && data.info[i].after_task_script.length > 0) ||
						(Array.isArray(data.info[i].verification_script) && data.info[i].verification_script.length > 0)
					) {
						let beforeTaskScript = [];
						let afterTaskScript = [];
						if (Array.isArray(data.info[i].before_task_script) && data.info[i].before_task_script.length > 0) {
							beforeTaskScript = data.info[i].before_task_script;
						}
						if (Array.isArray(data.info[i].after_task_script) && data.info[i].after_task_script.length > 0) {
							afterTaskScript = data.info[i].after_task_script;
						}
						let scriptContentList = [{
							title: LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT_SHOW,
							script_list: beforeTaskScript
						}, {
							title: LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT_SHOW,
							script_list: afterTaskScript
						}];
						if (2 === parseInt(data.info[i].timepoint_recovery_type)) {
							let verificationScript = [];
							if (Array.isArray(data.info[i].verification_script) && data.info[i].verification_script.length > 0) {
								verificationScript = data.info[i].verification_script;
							}
							scriptContentList.push({
								title: LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SHOW,
								script_list: verificationScript,
							});
						}
						tbody += `<td>${renderScript(i, scriptContentList)}</td>`;
					} else {
						tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
					}
					if (2 === parseInt(data.info[i].timepoint_recovery_type)) {  // 定时恢复最新备份点的验证报告
						if (!data.info[i].error_code) {
							tbody += `<td>
								<a class="colorgreen db-validate-report-chk" data-db-index="${i}">${LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_VIEW}</a>
							</td>`;
						} else {
							tbody += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
						}
					}
        			tbody += '<td>' + data.info[i].task_status + '</td>';
					if (data.error_details) {
						try {
							let errorDetails = JSON.parse(data.error_details);
							if (Array.isArray(errorDetails) && errorDetails.length > 0) {
								let errorDetailFlag = false;
								for (const errorDetail of errorDetails) {
									if (errorDetail.agent_uuid == data.info[i].agent_uuid && errorDetail.object_name == data.info[i].instance_name) {
										errorDetailFlag = true;
										break;
									}
								}
								if (errorDetailFlag) {
									tbody += `<td>${data.info[i].error_code}, 
										<u class="text-success error-detail-link" style="cursor: pointer" data-index="${i}"
											data-agent-uuid="${errorDetails[0].agent_uuid}" data-instance-name="${errorDetails[0].object_name}">
											${LANG.UI_PUBLIC_ERROR_DETAIL_LINK}
										</u>
									</td>`;
								} else {
									tbody += `<td>${data.info[i].error_code}</td>`;
								}
							} else {
								tbody += `<td>${data.info[i].error_code}</td>`;
							}
						} catch (e) {
							tbody += `<td>${data.info[i].error_code}</td>`;
						}
					} else {
						tbody += `<td>${data.info[i].error_code}</td>`;
					}
        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		return thead + tbody;
    		}

			if (typeof data.info.resource_limiting_node_config !== 'undefined') { // 存在 resource_limiting_node_config 属性，表示由资源限制触发过任务失败
				let resourceLimitingNodeConfig = data.info.resource_limiting_node_config;
				return `
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
            	`;
			} else {
				if (28 == data.taskType) {
					//备份
					return getBackupDetails(data);
				} else if (
					29 == data.taskType ||
					64 == data.taskType
				) {
					//恢复
					return getRecoveryDetails(data);
				}
			}
    	}

    	$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
  		  e.target // newly activated tab
  		  e.relatedTarget // previous active tab
  		  if("#history" === e.target.hash){
  			  init();
  		  }
  		})
    }

	const registerBadBlockEvent = (data) => {
		$('.bad-block-config-view').off().on('click', function () {
			$('#skipDatafileBadBlockModal').drawer('show');
			let settings = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true,
						idKey: 'id',
						pIdKey: 'pId',
						rootPId: 0,
					},
					key: {
						title: 'title',
					}
				},
			};
			let nodes = {
				instance: {
					id: 'instance',
					pId: '',
					name: data.sPath,
					title: data.sPath,
					eventtype: 'instance',
					icon: data.cluster_flag ? './img/platform/db-cluster.png' : './img/platform/storage.png',
					nocheck: true,
					isParent: true,
					open: true,
				},
			};
			for (const datafileInfo of data.detail.skip_datafiles_bad_block_info) {
				let tableSapceKey = datafileInfo.instance_name + '_' + datafileInfo.table_space_name;
				nodes[tableSapceKey] = {
					id: 'instance_table_space' + datafileInfo.table_space_name,
					pId: 'instance',
					name: datafileInfo.table_space_name,
					title: datafileInfo.table_space_name,
					eventtype: 'table space',
					icon: './img/platform/storage.png',
					nocheck: true,
					isParent: true,
					open: true,
				};
				let datafileKey = tableSapceKey + '_' + datafileInfo.file_id;
				let datafileName = datafileInfo.file_name + ', ' + LANG.UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_TABLE_FILE_SKIP + ': ' + datafileInfo.bad_block_num;
				nodes[datafileKey] = {
					id: 'instance_table_space' + datafileInfo.table_space_name + '_datafile' + datafileInfo.file_id,
					pId: 'instance_table_space' + datafileInfo.table_space_name,
					name: datafileName,
					title: datafileName,
					eventtype: 'tdatafile',
					icon: './img/fs/wenjian.png',
					nocheck: true,
					isParent: false,
				};
			}
			$.fn.zTree.init($('#skipDatafileBadBlockTree'), settings, Object.values(nodes));
		});
	}

	/**
	 * 设置脚本内容
	 * @param {array} scriptContentList
	 */
	const renderScript = (sequence, scriptContentList) => {
		// 页面渲染
		let scriptContentHtml = `<div>`;
		let scriptContentHtmlList = [];
		for (const index in scriptContentList) {
			let scriptContentInfo = scriptContentList[index];
			let scriptHtmlList = [];
			if (Array.isArray(scriptContentInfo.script_list) && scriptContentInfo.script_list.length > 0) {
				for (const key in scriptContentInfo.script_list) {
					let scriptInfo = scriptContentInfo.script_list[key];
					scriptHtmlList.push(`<a class="scriptItem" data-sequence="${sequence}" data-index="${index}" data-key="${key}">${scriptInfo.script_name}</a>`);
				}
				scriptContentHtmlList.push(`${scriptContentInfo.title}: ` + scriptHtmlList.join('、'));
			} else {
				scriptContentHtmlList.push(`${scriptContentInfo.title}: ${LANG.UI_PUBLIC_NOTHING}`);
			}
		}
		scriptContentHtml += scriptContentHtmlList.join('<br>');
		scriptContentHtml += '</div>';
		return scriptContentHtml;
	};

	/**
	 * 注册脚本事件
	 */
	const registerScriptEvent = (sequence, scriptContentList) => {
		// 页面事件
		$(`.scriptItem[data-sequence="${sequence}"]`).off().on('click', function () {
			let index = $(this).attr('data-index');
			let key = $(this).attr('data-key');
			let scriptInfo = scriptContentList[index].script_list[key];
			setScriptDrawerContent(scriptInfo.script_name, scriptInfo.script_type, scriptInfo.script_content);
		});
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
		$('#scriptContent').text(scriptContent);
		$('#scriptContentDrawer').drawer('show');
	};

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
	 * 获取数据库列表的搜索条件
	 */
	const getDetailsDBParams = () => {
		let params = {
			uuid: $("#task_uuid").val(),
		};
		let searchValue = $('#detailSearch').val().trim();
		if (searchValue) {
			params.search_value = searchValue;
		} else {
			params.search_value = '';
		}
		return params;
	};

    //初始化虚拟机表格
    var initDBGrid = function(){
    	var updateInterval = 10000;
    	var initFlag = false;
		dbDetailsGrid = new Datatable();
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
    			let data = {m:CONF.M.JOB,f:'getDetailsDB',p:getDetailsDBParams()};
				dbDetailsGrid.setAjaxParam(data);
				dbDetailsGrid.init({src: $("#dbstable"), showDetail:true, onDataLoad:dbGridLoad, dataTable:dataTableOpt});
    	    	initFlag = true;
    		}else{
				dbDetailsGrid.getRefresh(getDetailsDBParams());
    		}
    		timerTask.DBJobDetails_vmGrid = setTimeout(init, updateInterval);
    	}

		var dbGridLoad = function(){
			let data = dbDetailsGrid.getDataTable().data();
			if (!data.length || detailsIndex >= data.length) {
				detailsInfo = null;
				detailsIndex = 0;
			}
			if(detailsInfo){
				let openTr = $('#dbs').find('tbody > tr')[detailsIndex];
				addDetails(openTr, data[detailsIndex], detailsIndex)
				$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
			}
		}

    	init();
    	$('#dbstable').on('click', ' tbody td .row-details', function () {
        	var data = dbDetailsGrid.getDataTable().data();

            var nTr = $(this).parents('tr')[0];

            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfo = null;
            }else{
            	//如果是收起的
            	$('#dbs').find('tr .details').parent().remove();
            	$('#dbs').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row], row);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('#dbs').find('tbody tr .details').parents('tr')[0];
            }
            return;
        });

    	//添加详情信息
    	var addDetails = function(nTr, data, rowIndex){
    		if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="13">';
        	sOut += '<table>';
            sOut += getDBDetails(data[10], rowIndex);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
			// 事件注册
			if (28 == parseInt(data[10].type)) {
				let scriptContentList = [{
					title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
					script_list: data[10].before_task_script,
				}, {
					title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
					script_list: data[10].after_task_script,
				}];
				registerScriptEvent(rowIndex, scriptContentList);
				registerBadBlockEvent(data[10]);
			} else {
				let scriptContentList = [{
					title: LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT_SHOW,
					script_list: data[10].before_task_script
				}, {
					title: LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT_SHOW,
					script_list: data[10].after_task_script
				}];
				if (2 === parseInt(data[10].timepoint_recovery_type)) {
					scriptContentList.push({
						title: LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SHOW,
						script_list: data[10].verification_script,
					});
				}
				registerScriptEvent(rowIndex, scriptContentList);
			}
    	}

    	var getDBDetails = function(data, rowIndex){
    		if(28 == data.type){
    			//数据库备份
    			return getBackupDBDetails(data, rowIndex);
    		}else if(
				29 == data.type ||
				64 == data.type
			){
    			//数据库恢复
    			return getRecoveryDBDetails(data, rowIndex);
    		}
    	}

    	//备份数据库详情
    	var getBackupDBDetails = function(data, rowIndex){
    		var details = "<tr><td>" + LANG.UI_DB_PATH + ": </td><td>" + data.sPath + "</td></tr>";
    		details += `<tr><td>${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}: </td>`;
			// 脚本配置
			if (
				(Array.isArray(data.before_task_script) && data.before_task_script.length > 0) ||
				(Array.isArray(data.after_task_script) && data.after_task_script.length > 0)
			) {
				let beforeTaskScript = [];
				let afterTaskScript = [];
				if (Array.isArray(data.before_task_script) && data.before_task_script.length > 0) {
					beforeTaskScript = data.before_task_script;
				}
				if (Array.isArray(data.after_task_script) && data.after_task_script.length > 0) {
					afterTaskScript = data.after_task_script;
				}
				let scriptContentList = [{
					title: LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW,
					script_list: beforeTaskScript
				}, {
					title: LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW,
					script_list: afterTaskScript
				}];
				details += `<td>${renderScript(rowIndex, scriptContentList)}</td>`;
			} else {
				details += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
			}
			details += `</tr>`;
			if (parseInt(data.dbtype) === CONF.DB_TYPE.ORACLE) {
				if (!data.multi_task_flag) {
					details += `<tr>`;
					details += `<td>${LANG.UI_DB_BACKUP_ORACLE_SKIP_DATAFILE_BAD_BLOCK}: </td>`;
					if (typeof data.detail === 'undefined' || typeof data.detail.skip_datafiles_bad_block_info === 'undefined' || !data.detail.skip_datafiles_bad_block_info.length) {
						details += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
					} else {
						details += `<td><a class="bad-block-config-view">${LANG.UI_DB_BACKUP_ORACLE_SKIP_DATAFILE_BAD_BLOCK_VIEW}</a></td>`;
					}
					details += `</tr>`;
				}
			}
    		return details;
    	}

    	//恢复数据库详情
    	var getRecoveryDBDetails = function(data, rowIndex){
			var details = '';
			if (data.timepoint) {
				details += "<tr><td>" + LANG.UI_RECOVERY_TIMEPOINT + ":</td><td>";
				details += data.taskname + " => " + data.timepoint;
				details += "</td></tr>";
			}

    		if(data.log_rollbackup_time && data.log_rollbackup_time != ""){
    			details += "<tr><td>" + LANG.UI_RECOVERY_TIMEPOINT + ":</td><td>";
        		details += data.log_rollback_time;
        		details += "</td></tr>";
    		}

    		details += "<tr><td>" + LANG.UI_DB_RECOVERY_SRC_PATH + ":</td>";
    		details += "<td>" + data.path + "</td>";
    		details += "</tr>";


			details += "<tr><td>" + LANG.UI_RECOVERY_GOAL + ":</td>";
			switch (data.db_type) {
				case CONF.DB_TYPE.ORACLE:
				case CONF.DB_TYPE.DM:
				case CONF.DB_TYPE.TIDB:
				case CONF.DB_TYPE.MONGODB:
				case CONF.DB_TYPE.POSTGRE:
				case CONF.DB_TYPE.ANTDB:
				case CONF.DB_TYPE.KINGBASE:
				case CONF.DB_TYPE.UXDB:
				case CONF.DB_TYPE.HIGHGO:
				case CONF.DB_TYPE.OPENGAUSS:
				case CONF.DB_TYPE.VASTBASE:
				case CONF.DB_TYPE.MYSQL:
				case CONF.DB_TYPE.MARIA:
					details += `<td>${data.des_path}</td>`;
					break;
				case CONF.DB_TYPE.SQLSERVER:
				case CONF.DB_TYPE.SAPHANA:
					details += `<td>${data.des_path}/${data.dName}</td>`;
					break;
				default:
					break;
			}
			details += "</tr>";
    		details += `<tr><td>${LANG.UI_PUBLIC_SCRIPT_CONFIGURE}: </td>`;
			// 脚本配置
			if (
				(Array.isArray(data.before_task_script) && data.before_task_script.length > 0) ||
				(Array.isArray(data.after_task_script) && data.after_task_script.length > 0) ||
				(Array.isArray(data.verification_script) && data.verification_script.length > 0)
			) {
				let beforeTaskScript = [];
				let afterTaskScript = [];
				if (Array.isArray(data.before_task_script) && data.before_task_script.length > 0) {
					beforeTaskScript = data.before_task_script;
				}
				if (Array.isArray(data.after_task_script) && data.after_task_script.length > 0) {
					afterTaskScript = data.after_task_script;
				}
				let scriptContentList = [{
					title: LANG.UI_PUBLIC_BEFORE_RECOVERY_SCRIPT_SHOW,
					script_list: beforeTaskScript
				}, {
					title: LANG.UI_PUBLIC_AFTER_RECOVERY_SCRIPT_SHOW,
					script_list: afterTaskScript
				}];
				if (2 === parseInt(data.timepoint_recovery_type)) {
					let verificationScript = [];
					if (Array.isArray(data.verification_script) && data.verification_script.length > 0) {
						verificationScript = data.verification_script;
					}
					scriptContentList.push({
						title: LANG.UI_DB_RECOVERY_VALIDATE_SCRIPT_SHOW,
						script_list: verificationScript,
					});
				}
				details += `<td>${renderScript(rowIndex, scriptContentList)}</td>`;
			} else {
				details += `<td>${LANG.UI_PUBLIC_NOTHING}</td>`;
			}
    		details += "</tr>";

    		return details;
    	}


    	$('#startFull').unbind().on('click', function(){
    		if($(this).find('.btn').attr('disabled')){
     			return true;
     		}
    		startDbJob(1);
    	});
    	$('#startIncr').unbind().on('click', function(){
    		if($(this).find('.btn').attr('disabled')){
     			return true;
     		}
    		startDbJob(2);
    	});
    	$('#startDiff').unbind().on('click', function(){
    		if($(this).find('.btn').attr('disabled')){
     			return true;
     		}
    		startDbJob(3);
    	});

    	$('#startLog').unbind().on('click', function(){
    		if($(this).find('.btn').attr('disabled')){
     			return true;
     		}
    		startDbJob(4);
    	});

    	$('#startArchiveLog').unbind().on('click', function(){
    		if($(this).find('.btn').attr('disabled')){
     			return true;
     		}
    		startDbJob(4);
    	});

		// Oracle - crosscheck
		$('#startCrosscheckFull').unbind().on('click', function(){
			if($(this).find('.btn').attr('disabled')){
				return true;
			}
			startDbJob(1, true);
		});
		$('#startCrosscheckIncr').unbind().on('click', function(){
			if($(this).find('.btn').attr('disabled')){
				return true;
			}
			startDbJob(2, true);
		});
		$('#startCrosscheckDiff').unbind().on('click', function(){
			if($(this).find('.btn').attr('disabled')){
				return true;
			}
			startDbJob(3, true);
		});
		// END Oracle - crosscheck

    	$('#deleteDb').unbind().on('click', function(){
    		if($(this).find('.btn').attr('disabled')){
     			return true;
     		}
    		deleteDbs();
    	});


    	var startDbJob = function(mode, crosscheck = false){
    		var select = dbDetailsGrid.getSelectedRows();
    		if(!select.length){
    			return UIToastr.showInfo(LANG.UI_DB_SELECT_BACKUP, LANG.UI_DB_SELECT_BACKUP_TIPS);
    		}
    		var datatable = dbDetailsGrid.getDataTable().data();
    		var data = {};
    		data.dbList = [];
			data.crosscheck = !!crosscheck;
    		data.taskuuid = $("#task_uuid").val();
    		for(var i=0;i<datatable.length;i++){
    			if($.inArray(datatable[i][10].dbuuid,select) != -1){
    				var info = {};
    				info.db_uuid = datatable[i][10].dbuuid;
    				info.agent_uuid = datatable[i][10].agentuuid;
    				data.dbList.push(info);
    			}
    		}
    		data.dbType =  datatable[0][10].dbtype;
    		data.mode = mode;
    		var p = JSON.stringify(data);
    		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startDBJob',p: p}, function(d){
    			if(OPREL(d)){
					dbDetailsGrid.getRefresh(getDetailsDBParams());
        		}
    		});
    	}

    	var deleteDbs = function(){
        	var select = dbDetailsGrid.getSelectedRows();
        	if(!select.length){
    			return UIToastr.showInfo(LANG.UI_DB_SELECT_DELETE, LANG.UI_DB_SELECT_DELETE_TIPS);
    		}
        	bootbox.confirm({
                title: LANG.UI_JOB_DELETE_SELECT_VM,
                message: LANG.UI_JOB_DELETE_SELECT_VM_CONFIRM,
                callback: debounce(function(r) {
                    if(!r) return;
                    submitDelete(select, dbDetailsGrid);
                }, 300)
            });
        }

        var submitDelete = function(select, dbDetailsGrid){
        	var data = {};
        	var datatable = dbDetailsGrid.getDataTable().data();
        	data.dbList = [];
    		data.taskuuid = $("#task_uuid").val();
    		for(var i=0;i<datatable.length;i++){
    			if($.inArray(datatable[i][10].dbuuid,select) != -1){
    				data.dbList.push(id);
    			}
    		}
        	var p = JSON.stringify(data);

        	$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:'deleteSelectDbs', p: p}, function(d){
        		if(OPREL(d)){
					dbDetailsGrid.getRefresh(getDetailsDBParams());
        		}
        	});
        }


    }

	//根据任务状态设置按钮权限
    var setBtnStatus = function(){
    	//停止中状态，变为强制停止
    	switch(_taskStatus){
	    	case CONF.TASK_STATUS.RUNNING:
	    	case CONF.TASK_STATUS.PREPARING:
	    		//运行和准备中,禁用运行
	    		setControlBtn('start', false);
	    		setControlBtn('startFull', false);
	    		setControlBtn('startIncr', false);
	    		setControlBtn('startDiff', false);
	    		setControlBtn('startArchiveLog', false);
	    		setControlBtn('startLog', false);
				setControlBtn('startCrosscheckFull', false);
				setControlBtn('startCrosscheckIncr', false);
				setControlBtn('startCrosscheckDiff', false);
	    		setControlBtn('stop', true);
	    		break;
	    	case CONF.TASK_STATUS.STOPPED:
	    		//停止,禁用停止
	    		setControlBtn('start', true);
	    		setControlBtn('startFull', true);
	    		setControlBtn('startIncr', true);
	    		setControlBtn('startDiff', true);
	    		setControlBtn('startLog', true);
	    		setControlBtn('startArchiveLog', true);
				setControlBtn('startCrosscheckFull', true);
				setControlBtn('startCrosscheckIncr', true);
				setControlBtn('startCrosscheckDiff', true);
				$('#dbOpList .stop').html('<a class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a>');
	    		setControlBtn('stop', false);
	    		break;
	    	case CONF.TASK_STATUS.STOPPING:  // 停止中
	    		setControlBtn('start', false);
	    		setControlBtn('startFull', false);
	    		setControlBtn('startIncr', false);
	    		setControlBtn('startDiff', false);
	    		setControlBtn('startLog', false);
	    		setControlBtn('startArchiveLog', false);
				setControlBtn('startCrosscheckFull', false);
				setControlBtn('startCrosscheckIncr', false);
				setControlBtn('startCrosscheckDiff', false);
	    		setControlBtn('stop', true);
				//停止中状态，变为强制停止
				$('#dbOpList .stop').html('<a class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
				break;
			case CONF.TASK_STATUS.PENDING:  // 挂起状态只有停止操作
				setControlBtn('start', false);
				setControlBtn('startFull', false);
				setControlBtn('startIncr', false);
				setControlBtn('startDiff', false);
				setControlBtn('startLog', false);
				setControlBtn('startArchiveLog', false);
				setControlBtn('startCrosscheckFull', false);
				setControlBtn('startCrosscheckIncr', false);
				setControlBtn('startCrosscheckDiff', false);
				setControlBtn('stop', true);
				$('#dbOpList .stop').html('<a class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a>');
				break;
			case 20:  // 停止状态不能做任何操作
				setControlBtn('start', false);
				setControlBtn('startFull', false);
				setControlBtn('startIncr', false);
				setControlBtn('startDiff', false);
				setControlBtn('startLog', false);
				setControlBtn('startArchiveLog', false);
				setControlBtn('startCrosscheckFull', false);
				setControlBtn('startCrosscheckIncr', false);
				setControlBtn('startCrosscheckDiff', false);
				setControlBtn('stop', false);
				break;
    		default:
    			//其他状态,开启控制
    			setControlBtn('start', true);
	    		setControlBtn('startIncr', true);
	    		setControlBtn('startDiff', true);
	    		setControlBtn('startLog', true);
	    		setControlBtn('startArchiveLog', true);
	    		setControlBtn('stop', true);
	    		setControlBtn('startFull', true);
				setControlBtn('startCrosscheckFull', true);
				setControlBtn('startCrosscheckIncr', true);
				setControlBtn('startCrosscheckDiff', true);
    			break;
    	}
    	var timeStrategy = _data.timeStrategy;
    	for(var i=0;i<timeStrategy.length; i++){
			//如果为一次性备份 ,禁用增量和差异
    		if(timeStrategy[i].type == 4){
    			setControlBtn('startIncr', false);
	    		setControlBtn('startDiff', false);
	    		setControlBtn('startLog', false);
	    		setControlBtn('startArchiveLog', false);
				setControlBtn('startCrosscheckIncr', false);
				setControlBtn('startCrosscheckDiff', false);
    		}
		}
    }

	//设置按钮是否可用
    var setControlBtn = function(id, available){
    	//任务
    	if(available){
    		$("." + id).find('.btn').removeAttr('disabled');
    	}else{
    		$("." + id).find('.btn').attr('disabled', 'disabled');
    	}
    	//列表
    	if(available){
    		$("#" + id).find('.btn').removeAttr('disabled');
    	}else{
    		$("#" + id).find('.btn').attr('disabled', 'disabled');
    	}
    }

    var initListener = function(){
    	$('.stop').unbind().on('click', stopJob);   //终止任务
    	$('.start').unbind().on('click', startJob); //启动完备任务
    	$('.startIncr').unbind().on('click', startIncr);//增量
		$('.startDiff').unbind().on('click', startDiff);//差异
		$('.startLog').unbind().on('click', startLog);//日志
    }

	/**
	 * 显示停止任务对话框
	 * @return {Promise<unknown>}
	 */
	const showStopTaskDialog = () => {
		return new Promise((resolve, reject) => {
			let taskType = parseInt($('#task_type').val());
			if (taskType !== CONF.TASK_TYPE.DB_RECOVERY) {  // 恢复任务使用
				resolve();
				return;
			}
			let message = `
            <div>
                <div class="bootbox-input-wrapper" style="position: relative">
                    <input class="bootbox-input bootbox-input-password" type="password" autocomplete="off" style="border: 1px solid #E6E6E6;border-radius: 2px !important;height: 34px;width:100%;background-color: #FFFFFF;padding: 6px 12px">
                    <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                        <i class="viconfont vicon-a-lujing8232"></i>
                    </button>
                </div>
                <div class="font-danger error-msg"></div>
            </div>
            `;
			bootbox.dialog({
				title: LANG.UI_PUBLIC_RECOVERY_STOP_TASK_TITLE,
				message,
				buttons: {
					cancel: {
						label: LANG.UI_PUBLIC_CANCEL,
						className: 'btn btn-default',
						callback: function () {
						}
					},
					confirm: {
						label: LANG.UI_PUBLIC_CONFIRM,
						className: 'btn btn-primary',
						callback: debounce(function () {
							let $input = $(this).find('.bootbox-input-password');
							let result = $input.val();
							if (!result) {
								return false;
							}
							Metronic.blockUI({target: $(this).find('.modal-content'),animate: true});
							let encrypt = new JSEncrypt();
							encrypt.setPublicKey(CONF.PUBLIC_KEY);
							pAjaxRequest({password: encrypt.encrypt(result)}, `/api/v1/users/check/password`, `POST`, res => {
								Metronic.unblockUI($(this).find('.modal-content'));
								if (res.success) {
									$(this).modal('hide');
									resolve();
								} else {
									$(this).find('.error-msg').text(res.message);
								}
							});
							return false;
						}, 300, false),
					}
				}
			}).on('shown.bs.modal', function () {
				// 获取输入框和按钮
				let $input = $(this).find('.bootbox-input-password');
				let $btn = $(this).find('.show-password-btn');

				// 添加点击事件监听器
				$btn.on('click', function () {
					let inputType = $input.attr('type');
					if (inputType === 'password') {
						$input.attr('type', 'text');
						$btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
					} else {
						$input.attr('type', 'password');
						$btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
					}
				});
			});
		});
	};

    //停止
	var stopJob = function(){
		if($(this).find('.btn').attr('disabled')){
			return true;
		}
		showStopTaskDialog().then(() => {
			opJob('stopJob');
		});
	}

	var opJob = function(funName){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = 4;
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
		if($(this).find('.btn').attr('disabled')){
			return true;
		}
		startJobUnify('startJob', 1);
	}

	//启动差异
	var startDiff = function(){
		if($(this).find('.btn').attr('disabled')){
			return true;
		}
		startJobUnify('startJob', 3);
	}
	//启动增量
	var startIncr = function(){
		if($(this).find('.btn').attr('disabled')){
			return true;
		}
		startJobUnify('startJob', 2);
	}

	//启动日志
	var startLog = function(){
		if($(this).find('.btn').attr('disabled')){
			return true;
		}
		startJobUnify('startJob', 4);
	}

	//启动任务
	var startJobUnify = function(funName, type){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = 4;
		data.startType = type;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
	}

	//获取源端压缩的文本信息
	var getSrcCompressInfo = function(type){
		var des = "";
		switch(type){
			case 0:	//关闭
				des = LANG.UI_PUBLIC_OFF;
				break;
			case 1: //Gzip
				des = "Gzip";
				break;
			case 2: //LZMA2
				des = "LZMA2";
				break;
			default:
				des = LANG.UI_PUBLIC_OFF;
				break;

		}

		return des;
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

	/**
	 * 初始化事件
	 */
	const initGlobalListener = () => {
		$('#dbValidateReportDrawer .drawer-footer button.cancel').off('click').on('click', () => {
			$('#dbValidateReportDrawer').drawer('hide');
		});
		$('#detailSearchBtn').on('click', () => {
			dbDetailsGrid.getRefresh(getDetailsDBParams());
		});
		$('#detailSearch').keyup((event) => {
			if (event.key === 'Enter') {
				dbDetailsGrid.getRefresh(getDetailsDBParams());
			}
		});
		$('#detailClearSearch').on('click', () => {
			$('#detailSearch').val('');
			dbDetailsGrid.getRefresh(getDetailsDBParams());
		});
	};

	/**
	 * 初始化任务关联表
	 */
	const initTaskAssociationTable = (dbType) => {
   		/**
   		 * 格式化存储设备名称
   		 * @param storageName
   		 * @param storageType
   		 * @param freeSize
   		 * @param totalSize
   		 */
   		const formatStorageName = (storageName, storageType, freeSize, totalSize) => {
   		    storageName = storageName + '(' + CONF.STORAGE_TYPE_DES[storageType] + ', ';
   		    storageName += LANG.UI_PUBLIC_TOTAL_SIZE2 + ': ' + storageCalculateSize(totalSize) + ', ';
   		    storageName += LANG.UI_PUBLIC_FREE_SIZE + ': ' + storageCalculateSize(freeSize) + ')';
   		    return storageName;
   		};

		/**
		 * 获取任务关联列
		 */
		const getTaskAssociationColumns = () => {
			return [{
				title: LANG.UI_PUBLIC_TASK_NAME,
				field: 'job_name',
				width: '10',
				widthUnit: '%',
				formatter: (jobName, row) => {
					let detailUrl = `./content/dbprotect/db_job_details.php?type=28&uuid=${row.job_uuid}`;
					return `
					<a href="${detailUrl}" class="ajaxify" name="task" title="${jobName}">${jobName}</a>
					`
				},
			}, {
				title: LANG.UI_DB_DETAIL_TASK_CATEGORY,
				field: 'depend_job_uuid',
				width: '10',
				widthUnit: '%',
				formatter: dependTaskUuid => {
					let text = LANG.UI_DB_DETAIL_TIDB_FULL_TASK;
					if (typeof dependTaskUuid !== 'undefined' && dependTaskUuid.length) {
						text = LANG.UI_DB_DETAIL_TIDB_LOG_TASK;
					}
					return `<span title="${text}">${text}</span>`
				},
			}, {
				title: LANG.UI_PUBLIC_TASK_TYPE,
				field: 'task_type',
				width: '10',
				widthUnit: '%',
				formatter: taskType => {
					let taskTypeDes = CONF.TASK_TYPE_DES[parseInt(taskType)];
					return `<span title="${taskTypeDes}">${taskTypeDes}</span>`;
				},
			}, {
				title: LANG.UI_PUBLIC_TASK_STATUS,
				field: 'task_status',
				width: '5',
				widthUnit: '%',
				formatter: taskStatus => {
					let taskStatusDes = CONF.TASK_STATUS_DES[parseInt(taskStatus)];
					let labelClass = getStatusLevelClass(parseInt(taskStatus));
					return `<span class="label label-sm ${labelClass}" title="${taskStatusDes}">
						${taskStatusDes}
					</span>`;
				},
			}, {
				title: LANG.UI_BACKUP_NODE,
				field: 'node_info',
				width: '22',
				widthUnit: '%',
				formatter: nodeInfo => {
					let showName = nodeInfo.node_hostname + '(' + nodeInfo.node_ip + ')';
					if (nodeInfo.node_ip !== nodeInfo.node_nickname && nodeInfo.node_nickname) {
						showName = nodeInfo.node_nickname + '(' + nodeInfo.node_ip + ')';
					}
					return `<span title="${showName}">${showName}</span>`;
				},
			}, {
				title: LANG.UI_STORAGE,
				field: 'storage_info',
				width: '25',
				widthUnit: '%',
				formatter: storageInfo => {
					let storageDes = formatStorageName(storageInfo.storage_nickname, storageInfo.storage_type, storageInfo.free_size, storageInfo.total_size)
					return `<span title="${storageDes}">${storageDes}</span>`;
				},
			}, {
				title: LANG.UI_PUBLIC_CREATE_TIME,
				field: 'create_time',
				width: '15',
				widthUnit: '%',
			}];
		};

		/**
		 * 格式化任务关联详情
		 */
		const formatterTaskAssociationDetail = (index, row, element, dbType) => {
			let html = `<table>`;
			html += `
			<thead>
				<tr>
					<th>${LANG.UI_DB_PATH}</th>
					<th>${LANG.UI_DB_TYPE}</th>
				</tr>
			</thead>
			`;
			html += `<tbody>`;
			let clusterData = {};
			let agentData = {};
			for (const backupBbInfo of row.backup_db_list) {
				if (backupBbInfo.cluster_flag) {  // 集群
					if (typeof clusterData[backupBbInfo.cluster_uuid] === 'undefined') {
						clusterData[backupBbInfo.cluster_uuid] = [backupBbInfo];
					} else {
						clusterData[backupBbInfo.cluster_uuid].push(backupBbInfo);
					}
				} else {  // 客户端
					agentData[backupBbInfo.agent_uuid] = backupBbInfo;
				}
			}
			for (const clusterUuid in clusterData) {
				let agentList = clusterData[clusterUuid];
				let dbPath = ``;
				let agentDesList = [];
				for (const agentInfo of agentList) {
					switch (dbType) {
						case CONF.DB_TYPE.ORACLE:
							dbPath = agentInfo.app_service_name;
							agentDesList.push(agentInfo.agent_ip + '/' + agentInfo.instance_name)
							break;
						case CONF.DB_TYPE.TIDB:
							dbPath = agentInfo.instance_name;
							agentDesList.push(agentInfo.agent_ip + '/' + agentInfo.cluster_role)
							break;
					}
				}
				dbPath += `(` + agentDesList.join(', ') + `)`;
				html += `<tr>`;
				html += `<td>${dbPath}</td>`;
				html += `<td>${CONF.DB_DES[dbType]}</td>`;
				html += `</tr>`;
			}
			for (const agentUuid in agentData) {
				let agentInfo = agentData[agentUuid];
				html += `<tr>`;
				html += `<td>${agentInfo.agent_ip}/${agentInfo.instance_name}</td>`;
				html += `<td>${CONF.DB_DES[dbType]}</td>`;
				html += `</tr>`;
			}
			html += `</tbody>`;
			html += `</table>`;
			$(element).html(html);
		};

		/**
		 * 任务关联表刷新间隔
		 */
		const TASK_ASSOCIATION_TABLE_INTERVAL = 5000;
		let expandIndex = null;

		/**
		 * 刷新任务关联表
		 */
		const refreshTaskAssociationTable = () => {
			if (timerTask.TaskAssociationTableTimer) {
				clearTimeout(timerTask.TaskAssociationTableTimer);
				timerTask.TaskAssociationTableTimer = null;
			}
			timerTask.TaskAssociationTableTimer = setTimeout(() => {
				$('#associatedTaskTable').bootstrapTable('refresh');
			}, TASK_ASSOCIATION_TABLE_INTERVAL);
		};

		$('#associatedTaskTable').bootstrapTable('destroy').baseTableConfig().init({
            vin_url: `/api/v1/db/jobs/${$("#task_uuid").val()}/association`,
            vin_method: 'GET',
			search: false,
			sortable: false,
			clickToSelect: true,
			pagination: true,
			detailView: true,
			detailFormatter: (index, row, element) => {
				formatterTaskAssociationDetail(index, row, element, dbType);
			},
			onResetView: () => {
				$("#associatedTask .fixed-table-body").css({
					"height": 200,
				});
			},
            onRefresh: () => {
                $('#associatedTaskTable').bootstrapTable('hideLoading');
            },
			onPostBody: () => {
                if (null !== expandIndex) {
                    $('#associatedTaskTable').bootstrapTable('expandRow', expandIndex);
                }
                refreshTaskAssociationTable();
			},
            onExpandRow: (index) => {
                if (null === expandIndex) {
                    expandIndex = index;
                } else if (index !== expandIndex) {
                    $('#associatedTaskTable').bootstrapTable('collapseRow', expandIndex);
                    expandIndex = index;
                }
            },
            onCollapseRow: () => {
                expandIndex = null;
            },
			columns: getTaskAssociationColumns(),
		});
	};

	/**
	 * 初始化备份任务关联
	 */
	const initBackupTaskAssociation = () => {
		$('#associatedTaskLi').hide();
		Metronic.blockUI({target: '#associatedTask',animate: true});
		pAjaxRequest({}, `/api/v1/db/jobs/${$("#task_uuid").val()}/association`, 'GET', res => {
            Metronic.unblockUI('#associatedTask');
			if (!res.success) {
				return;
			}
			let dbType = parseInt(res.data.db_type);
			if (parseInt(res.data.task_type) !== CONF.TASK_TYPE.DB_BACKUP) {
				return;
			}
			if (dbType !== CONF.DB_TYPE.TIDB) {  // 目前仅支持TIDB
				return;
			}
			$('#associatedTaskLi').show();
			initTaskAssociationTable(dbType);
		});
	};

	/**
	 * 设置页面的布局
	 */
	const setPageLayout = () => {
		let taskType = parseInt($('#task_type').val());
		if (taskType === CONF.TASK_TYPE.DB_BACKUP) {
			$('#backupSummaryTitle').show();
			$('#recoverySummaryTitle').hide();
			$('#backupRecoverySummaryContent').removeClass('bdt1e6').addClass('ndnone');
		} else {
			$('#backupSummaryTitle').hide();
			$('#recoverySummaryTitle').show();
			$('#backupRecoverySummaryContent').addClass('bdt1e6').removeClass('ndnone');
		}
	};

    return {
        //main function to initiate the module
        init: function () {
            console.log('db-job-detail');  // 这里打印是为了方便debug
			// initSwiper();
        	initBasicInfo();
        	initSpeed();
            initDBGrid();
            initLogGrid();
            initHistoryGrid();
			initBackupTaskAssociation();
			watchEchartSizeChange();
			initGlobalListener();
			setPageLayout();
        }

    };

}();

jQuery(document).ready(function() {
	DBJobDetails.init();
});
