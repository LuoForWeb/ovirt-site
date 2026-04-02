var hadoop_job_details = function(){
    var jobParams = {}; //操作需要参数
	var initChartFlag = false; //任务曲线图初始化标志
	var initHadoopFlag =  false;
	var initHistoryFlag =  false;
	var myChart;
	var _taskStatus; //监控任务状态
    var  expandIndex = null
	var source_submodule_type = '';
	var fsnodeuuid = ''; //用于下载跳过文件
	var checkIndex;
	let queryParams = {};
	const TIME_BACKUP_STRATEGY_TYPE = {
        STRATEGY: 1,
        ONCETIME: 2,
        MANUAL: 3
    }
	const RECOVERY_INTEGRITY_POLICY_DESC_MAP = {
		0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY,
		1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY,
		2: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK,
	}; // 验证策略 - 完整性校验异常处理描述
	let task_user_uuid = '';//任务关联的用户uuid
    //初始化基本信息
    var initBasicInfo =  function(){
        var updateInterval = 2000;
        var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.HadoopJobDetails_basicInfo);
        		return;
        	}
            var jobs_uuid = $("#task_uuid").val();
            //请求基本数据
            pAjaxRequest({}, "/api/v1/hadoop/jobs/" + jobs_uuid, "GET", function (result) {
                setBasicInfo(result.data, timerTask.HadoopJobDetails_basicInfo)
            }, false);
			timerTask.HadoopJobDetails_basicInfo = setTimeout(init, updateInterval);
		}
		init();
    };

    //设置基本信息
    var setBasicInfo = function (data, timeoutID) {
		//设置列表展示
		var titleList = '';
		source_submodule_type = data.source_submodule_type;
		if(data.source_submodule_type){
			switch(data.source_submodule_type){
				case 1: // 源端为文件客户端
						titleList = LANG.UI_COPY_DETAIL_FS_LIST; //主机列表
						break;
					case 2: // 源端为nas设备
						titleList = LANG.UI_COPY_DETAIL_NAS_LIST; //nas设备列表
						break;
					case 3: // 源端为hadoop集群
						titleList = LANG.UI_HADOOP_CLUSTER_LIST; //集群列表
						break;
					case 4: // 源端为对象存储
						titleList = LANG.UI_PLATFORM_DES_OBS_list; //对象存储列表
						break;
					default:
						break;
			}
		}else{
		    titleList = LANG.UI_HADOOP_CLUSTER_LIST; //集群列表
		}
		task_user_uuid = data.task_user_uuid;
		$('#list_title').html(titleList);
        //任务完成等待状态
        if (data.job_status != 2) {
            $('#total-progress').css({width: '0%'});
            $('#progressright').html('');
            data.total_size = '--';
            data.complate_size = '--';
        }
        var snapshotflag,archiveflag;
		_taskStatus = data.statusValue;
		jobParams.status = data.statusValue;
		jobParams.taskType = data.taskTypeFlag;
		jobParams.subModule = 3;
        if(!data.flag){
			clearTimeout(timeoutID);
			$('#total-progress').css({width: '100%'});
			$('#progressright').html('100%');
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function(){
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}, 5000);
		}
        //初始化操作按钮
		initOpButton(data)
        $('#taskName').html(data.taskName);
		$('#taskType').html("Hadoop" + data.taskType);
		if(data.status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.statusValue) + '" >' + data.status + '</span>');
		}
		$('#task_stage').html(data.current_stage_value);
		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);

		$('#speed').html(data.speed);
		$('#progress').html(data.progress);
		$('#threadNum').html(data.thread_num);
		$('.transferNetworkdiv').hide();

		//写入重试策略值start
		$("#network_retry_times").html(data.retry_strategy.network_retry_times+LANG.UI_MICROSOFT365_TIME);
		$("#network_retry_interval").html(data.retry_strategy.network_retry_interval+LANG.UI_PUBLIC_SECOND);
		$('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
		if(data.retry_strategy.op_retry_flag){
			$("#opRetryTime").html(data.retry_strategy.op_retry_times+LANG.UI_MICROSOFT365_TIME);
			$("#opRetryInterval").html(data.retry_strategy.op_retry_interval+LANG.UI_PUBLIC_SECOND);
		}else{
			$('.opRetryTimesDiv').hide();
			$('.opRetryIntervalDiv').hide();
		}
		$('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
		if(data.retry_strategy.task_retry_flag){
			$('#task_retry_object').html(data.retry_strategy.task_retry_object == 1 ? LANG.UI_RETRY_FAILED_OBJS_IN_TASK : LANG.UI_RETRY_ALL_OBJS_IN_TASK);
			$('#taskRetryTime').html(data.retry_strategy.task_retry_times + LANG.UI_MICROSOFT365_TIME);
			$('#taskRetryInterval').html(data.retry_strategy.task_retry_interval / 60 + LANG.UI_MICROSOFT365_MINUTE);
		}else{
			$('.task-retry-form-item').hide();
		}
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
		//写入重试策略值end
		var safehideflag =  false;
        if(data.taskTypeFlag!=2) {//备份
			// $('.fsStartDiv').show();
			// $('.search').show()
			$('#vin_hadoop_detail_toolbar').show();
			//恢复方式隐藏
			$('.is-recovery-static-info').hide();
			$('#compressed').html(getFlagLevelInfo(data.storageInfo.high.compressed));
			// 压缩等级
			if(data.storageInfo.high.compressed){
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
				$('#compressMethod').html(method);
			}else{
				$('.compressMethodDiv').hide();
			}
			//如果数据加密关闭隐藏自动生成密码开关描述显示
			if(!data.storageInfo.high.encrypt_flag){
				$('.passwordAutodiv').hide();
			}else {
				$('.passwordAutodiv').show();
			}
			$('#encryptStorage').html(getFlagLevelInfo(data.storageInfo.high.encrypt_flag));
			// 存储加密算法
			if(data.storageInfo.high.encrypt_flag){
				let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
				if(data.storageInfo.high.encrypt_method == 2){
					method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
				}
				$('.encrypt-method-div').show();
				$('#encryptMethod').html(method);
			}else{
				$('.encrypt-method-div').hide();
			}
			$('#passwordAuto').html(getFlagLevelInfo(data.storageInfo.high.password_auto_flag));
			$('#wildcardmode').html(getFlagLevelInfo(data.wildcardMode));
			// 快照
			data.snapShotFlag==1?snapshotflag=true:snapshotflag=false;
			$('#snapshot').html(getFlagLevelInfo(snapshotflag));
            //归档和归档目标屏蔽
            $('.archiveDiv').hide();
            $(".archivedesDiv").hide();
			// 扫描线程
			$('#scanThreadNum').html(data.scan_thread_num);
			if(data.scan_thread_num == 1) {
				// 扫描文件速度
				$('.scanFileDiv').show();
				$('#scanFileNum').html(getScanSpeed(data.scan_file_num));
			}
			$('.file-permission-backup-div').show();
			$('#file_permission').html(getFlagLevelInfo(data.permission_operate_flag));
			//异常处理
			//跳过文件告警智能判断
			$(".passalarmflagdiv").show();
			$('#passalarmflag').html(getFlagLevelInfo(data.skip_file_alarm_flag));
			if(data.skip_file_alarm_flag) {
				$('.passalarmDiv').show();
				$('#passalarmNum').html(data.skip_file_alarm_min_num);
				$('#passalarmPercent').html(data.skip_file_alarm_min_ratio);
			} else {
				$('.passalarmDiv').hide();
			}
			//如果存储类型是磁带，隐藏传输线程
			if((data.storageInfo.storage && data.storageInfo.storage.typenum != CONF.BD_STORAGE_TYPE.TAPE) && CONF.FUNCTIONS.includes('multithread')){
				$('.threadDiv').show();
			}else{
				$('.threadDiv').hide();
			}
			var wormhideflag =  false;
			var completehideflag =  false;
			if (data.storageInfo.storage && data.storageInfo.storage.typenum === CONF.BD_STORAGE_TYPE.TAPE) {
				$('.backup-safety-strategy-group').hide();
			}else{
				$('.backup-safety-strategy-group').show();
				//显示备份安全策略
				$('.backupSafeModeDiv').show();
				$('#backup_worm_flag').html(getFlagLevelInfo(data.safeStrategy.worm_flag));
				// $('#virus_check_flag').html(getFlagLevelInfo(data.safeStrategy.virus_scan_flag));
				$('#integrity_check_flag').html($.fn.getCompleteDetectionBackupDes({
					integrityCheckFlag: data.safeStrategy.integrity_check_flag,
					integrityCheckConfig: data.safeStrategy.integrity_check_config,
					isFullTimepointTitle: false,
					showIncrErrorPolicy: false,
					excludeTitleFlag: true,
				}));
				if (data.safeStrategy.worm_flag) {//WORM防护
					$('.backup_worm_date-form-item').show();
					$('#backup_worm_date').html(data.safeStrategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY);
				} else {
					$('.backup_worm_date-form-item').hide();
				}
				//存储上未开启worm不显示
				if (!data.safeStrategy.worm_storage_flag) {
					$('.worm-form-item').hide();
					wormhideflag =  true;
				}
				if (data.safeStrategy.integrity_check_flag) {//完整性校验
					$('.integrity-check-des-div').show();
					var cycledes = ''
					switch (data.safeStrategy.integrity_check_config.check_strategy) {
						case CONF.CHECK_TIME.DAY:
							cycledes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_DAY;
							break;
						case CONF.CHECK_TIME.WEEK:
							cycledes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_WEEK;
							break;
						case CONF.CHECK_TIME.EVERY:
							cycledes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_PERIOD_OPTION_EVERY_TIME;
							break;
					}
					var abnormaldes = '';
					switch (data.safeStrategy.integrity_check_config.full_error_policy) {
						case CONF.FULL_ABNORAL.REFULL:
							abnormaldes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE2;
							break;
						case CONF.FULL_ABNORAL.STOPBACKUP:
							abnormaldes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE1;
							break;

					}
					// $('#full_point_abnormal').html('--');
					$('#check_cycle').html(cycledes);
					$('#other_point_abnormal').html(abnormaldes);
				} else {
					$('.integrity-check-des-div').hide();
					completehideflag =  true;
				}
				if(wormhideflag && completehideflag){
					safehideflag = true
				}

			}

		}

		if(1 != data.taskTypeFlag){//恢复
			$('.is-backup-static-info').hide();
			$('.scanThreadDiv').hide();
			$('#vin_hadoop_detail_toolbar').hide();
			$('#storageli').hide();
			$('.storagemodelDiv').hide();
			$('.wildcardmodeDiv').hide();
			$('.snapshotDiv').hide();
			$('.startFull').hide();
			$('.startIncr').hide();
			$('.startDiff').hide();

			$('.recoverDiv').show();
			$('#recovery_type').html(data.timeStrategy);
			// 目录树恢复
			if(data.new_root_path == LANG.UI_FILE_RECOVERY_ORIGINAL_PATH) {
				$('.dir-tree-div').hide();
			} else {
				$('.dir-tree-div').show();
				$('#dir_tree').html(getFlagLevelInfo(data.dir_tree_recovery_flag));
			}
			// 同名文件处理
			$('.same-name-div').show();
			$('#same_name').html(getSameNameStrategy(data.same_file_strategy));
			// 文件权限恢复
			//如果源端不是hadoop都需要屏蔽掉文件权限恢复
			if(data.source_submodule_type == 3){
				$('.file-permission-recovery-div').show();
				$('#file_permission_recovery').html(getFlagLevelInfo(data.permission_operate_flag));
			}else{
				$('.permissionDiv').hide();
			}
			// //如果源端是对象存储，需要屏蔽掉无效快捷方式清理
			// if(data.source_submodule_type != 4){
			// 	// 无效快捷方式清理
			// 	$('.no-valid-clear-div').show();
			// 	$('#no_valid_clear').html(getFlagLevelInfo(data.link_file_pass_flag));
			// }
			//如果存储类型是磁带，隐藏传输线程
			if(data.storageType != CONF.BD_STORAGE_TYPE.TAPE && CONF.FUNCTIONS.includes('multithread')){
				$('.threadDiv').show();
			}else{
				$('.threadDiv').hide();
			}
			//文件权限恢复
			// $('.file-permission-recovery-div').show();
			//显示恢复安全策略

			if (data.storageType === CONF.BD_STORAGE_TYPE.TAPE) {
				$('.backup-safety-strategy-group').hide();
			}else{
				$('.backup-safety-strategy-group').show();
				$('.restoreSafeModeDiv').show();
				// 完整性校验异常处理
				if (!data.safeStrategy.integrity_check_flag) {
					$('#integrity_policy').html(LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_RECOVERY_UNSET);
				}else{
					$('#integrity_policy').html(RECOVERY_INTEGRITY_POLICY_DESC_MAP[data.safeStrategy.integrity_check_config.recovery_error_policy]);
				}
			}
			//恢复时间策略
			if(data.timeStrategy[0] && data.timeStrategy[0].type ==  4){
				$('#recovery_type').html(LANG.UI_GLOBAL_STRATEGY_TYPE_START_AT_TIME);
				$('#start_time').html(data.timeStrategy[0].start_time);
			}else{
				$('#recovery_type').html(LANG.UI_GLOBAL_STRATEGY_TYPE_IMMEDIATE);
				$('.start_time_form').hide();
			}

		}
        //时间策略
		//备份策略
		$('#createTime').html(data.createTime);
		$('#nextTime').html(data.nextTime);
		if (data.taskTypeFlag!=2) {
            $('.fullDiv').show();
            $('.incrDiv').show();
            $('.diffDiv').show();
			$('.pincrDiv').show();
        } else{
			$('.fullDiv').hide();
            $('.incrDiv').hide();
            $('.diffDiv').hide();
			$('.pincrDiv').hide();
		}
        switch (data.time_strategy_backup_type) {
            case TIME_BACKUP_STRATEGY_TYPE.STRATEGY:
                $('#timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);
                break;
            case TIME_BACKUP_STRATEGY_TYPE.ONCETIME:
                $('#timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);
                break;
            case TIME_BACKUP_STRATEGY_TYPE.MANUAL:
                $('#timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);
                $('.fullDiv').hide();
                $('.incrDiv').hide();
                $('.diffDiv').hide();
				$('.pincrDiv').hide();
				break;
			default:
				break;
        }
		// 在编排中的任务，显示按编排策略执行（原时间策略不生效）
		if (data.task_orchestration_plan_flag) {
			$('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
		}
		var timeStrategy = getTimeStrategy(data.timeStrategy, data.time_strategy_backup_type);
		$('#fulldes').html(timeStrategy.full);
		$('#incdes').html(timeStrategy.inc);
		$('#diffdes').html(timeStrategy.diff);
		$('#pincrdes').html(timeStrategy.pincr)
        if(data.reservedStrategy){
			//磁带隐藏部分信息
			if (data.storageInfo.storage && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.TAPE) {
				//磁带获取策略替换保留策略
				$('.reserve-strategy-form-item .strategy-group__form__item__label').html(LANG.UI_GLOBAL_STRATEGY_NAME);
				$('.reserve-strategy-form-item #reservedStrategy').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
			} else {
				$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
			}
		}else{
			//恢复,隐藏保留策略
			$('.reservedDiv').hide();
		}
		//传输代理
		$('#appliance_agency_flag').html(getFlagLevelInfo(data.appliance_agency_flag));
		if (data.appliance_agency_flag) {
			$('.appliance-agency-flag-info').hide();
            $('.appliance-agency-info').show();
            $('#appliance_agency').html(data.appliance_agency);
			if(data.agent_pool_info.agent_pool_uuid){
				$('#agentPool').html(data.agent_pool_info.agent_pool_nickname);
			}else{
				$('#agentPool').html(`--`);
			}
        } else {
			$('.appliance-agency-flag-info').show();
            $('.appliance-agency-info').hide();
        }

		//加密传输
		if(data.transportStrategy){
			$('#encryptStrategy').html(getFlagLevelInfo(data.transportStrategy.encrypt));
		}
        //存储信息
        if(data.storageInfo.flag){
            var node = data.storageInfo.node;
            var storage = data.storageInfo.storage;
            var storageInfo = '';
            // if(!storage){
            //     //没有存储信息,自动选择存储
            //     storageInfo = LANG.UI_JOB_AUTO_SELECT_STORAGE;
            // }else{
            //     storageInfo = storage.name + "(" + storage.type + ")<br>";
            //     if(!storage.quotaFlag){
            //         storageInfo += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
            //         LANG.UI_JOB_FREE_SIZE + ":" + storage.freesize;
            //     }else{
            //         storageInfo += storage.quotades;
            //     }
            // }

            // $('#nodeinfo').html(node.name + "<br>" + node.ip);
            // $('#storageinfo').html(storageInfo);
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
			$('#nodeinfo').html(data.storageInfo.node_pool_uuid ?
								data.storageInfo.node_pool_nickname +":<br/>" + node.name + "<br>" + node.ip :
								node.name + "<br>" + node.ip);
			$('#storageinfo').html(data.storageInfo.storage_pool_uuid ?
								   data.storageInfo.storage_pool_nickname +":<br/>" + storageInfo :
								   storageInfo);

        }
        //限速策略
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
		$('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);
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

		// 安全策略未授权隐藏
        if (!CONF.FUNCTIONS.includes('worm')) {
            $('.worm-form-item').hide();
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
			$('.integrity-form-item').hide();
			$('.restoreSafeModeDiv').hide();
			if(1 != data.taskTypeFlag){
				safehideflag = true;
			}
        }
        if ((!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) || safehideflag) {
            $('.backup-safety-strategy-group').hide();
        }
    }

    var getScanSpeed = function (level) {
		var des = "";
		switch(level) {
			case 0:
				des = LANG.UI_FILE_BAK_SCAN_SPEED_FIVE;
				break;
			case 1000:
				des = LANG.UI_FILE_BAK_SCAN_SPEED_FOUR;
				break;
			case 800:
				des = LANG.UI_FILE_BAK_SCAN_SPEED_THREE;
				break;
			case 600:
				des = LANG.UI_FILE_BAK_SCAN_SPEED_TWO;
				break;
			case 400:
				des = LANG.UI_FILE_BAK_SCAN_SPEED_ONE;
				break;
		}
		return des;
	}
	/**
	 * 显示停止任务对话框
	 * @return {Promise<unknown>}
	 */
	const showStopTaskDialog = () => {
		return new Promise((resolve, reject) => {
			let taskType = parseInt($('#task_type').val());
			if (taskType !== CONF.TASK_TYPE.RECOVERY) {  // 恢复任务使用
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
			console.log("comien");

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

    var initOpButton = function(data) {
		setBtnStatus(data);
		//任务详情按钮
		$('.start').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			 startJobUnify('startJob', 1);
    	});
		$('.startFull').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			 startJobUnify('startJob', 1);
    	});
    	$('.startIncr').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			 startJobUnify('startJob', 2);
    	});
    	$('.startDiff').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			 startJobUnify('startJob', 3);
    	});

    	$('.stop').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
			showStopTaskDialog().then(() => {
				opJob('stopJob');
			});
    	});
    	var opJob = function(funName){
			checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
				var data = {};
				data = jobParams;
				data.uuid = $('#task_uuid').val();
				data.module = 3;
				params = JSON.stringify(data);
				Metronic.blockUI({target: '#jobDetail',animate: true});
				$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
					Metronic.unblockUI('#jobDetail');
					if(OPREL(d)){
					}
				});
			})

		}

    	//启动任务
		var startJobUnify = function(funName, type){
			checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
				var params = {
					'start_type': type
				};
				var job_uuid = $('#task_uuid').val();
				Metronic.blockUI({target: '#jobDetail',animate: true});
				pAjaxRequest(params, "/api/v1/jobs/start/" + job_uuid + "", 'POST', function (data) {
					Metronic.unblockUI('#jobDetail');
					var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
					if (operateResponseList(data, op)) {
					}

				});
			});

		}




		//搜索确认事件

       $('#searchSubmit').unbind().on('click',function (){
			queryParams.search = $('#searchVal').val();
            $("#hadooptable").bootstrapTable('refresh',{query:queryParams});
        })
        $('#searchVal').unbind().on('keyup',function (event) {
            if (event.key === 'Enter') {
              	queryParams.search = $('#searchVal').val();
                $("#hadooptable").bootstrapTable('refresh',{query:queryParams});
            }
        });

		$('#searchVal').unbind().on('blur', function () {
			$(this).prop('placeholder',LANG.UI_HADOOP_SEARCH_BY_NAME);
		});

        $('#searchVal').unbind().on('focus', () => {
            $('#clearSearchBtn').removeClass('hide');
        })
		$('#clearSearchBtn').unbind().on('click', function () {
			$('#searchVal').val('');
            $('#clearSearchBtn').addClass('hide');
            queryParams.search = $('#searchVal').val();
            $("#hadooptable").bootstrapTable('refresh',{query:queryParams});
		});

	}

    //得到状态的显示类型
	var getStatusLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case CONF.TASK_STATUS.WAITTING:
			case CONF.TASK_STATUS.STOPPING:
			case CONF.TASK_STATUS.PREPARING:
				levelClass = "label-info";
				break;
			case CONF.TASK_STATUS.RUNNING:
			case CONF.TASK_STATUS.PAUSED:
			case CONF.TASK_STATUS.SUCCESSED:
				levelClass = "label-success";
				break;
			case CONF.TASK_STATUS.STOPPED:
				levelClass = "label-default";
				break;
			case CONF.TASK_STATUS.ABNORMAL:
			case CONF.TASK_STATUS.PENDING:
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

    //得到同名文件处理策略
	var getSameNameStrategy = function (flag) {
		var des = '';
		switch (parseInt(flag)) {
			case 1:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_COVER;
				break;
			case 2:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_KEEP_LATEST;
				break;
			case 3:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_ADD;
				break;
			case 4:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_RENAME;
				break;
			case 5:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_REPLACE;
				break
		}
		return des;
	}

    //得到时间策略描述信息
	var getTimeStrategy = function(msg,timeStrategyBackupType){
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
				des += strategy.start_time;
			}else{
				des += LANG.UI_PUBLIC_NOTHING;
			}

			if(1 == strategy.mode){
				if (timeStrategyBackupType == TIME_BACKUP_STRATEGY_TYPE.STRATEGY) { //按策略备份才显示完备补偿
					if(strategy.full_backup_compensation_flag){
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
		for(var i=1;i<=20;i++){
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
		desEach += strategy.start_time;
		//如果是英文版 需要加空格
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			desEach += " "; //策略开始时间
		}
		desEach += LANG.UI_STRATEGY_START + ", ";
		if(strategy.roll_flag){
			desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.end_time;
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
		if (msg.strategy_mode === CONF.RESERVE_STRATEGY_MODE.POINT) { // 按备份点保留
            reservedStr += LANG.UI_RESERVE_RETENTION_TYPE + ': '+ LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
        } else { // 按备份链保留
            reservedStr +=LANG.UI_RESERVE_RETENTION_TYPE + ': '+  LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
        }
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_NUM + '<br>';
		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr += LANG.UI_RESERVE_RETENTION_MODE + ': ' + LANG.UI_BACKUP_DAY + '<br>';
		}else if(CONF.RESERVE_TYPE.PERMANENT == msg.type){
			reservedStr += LANG.UI_FILE_PERMANENT + '<br>';
		}
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr +=  LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY + ': ' + msg.value;
		}else{
			reservedStr +=  LANG.UI_STRATEGY_VALUE + ': ' + msg.value;
		}
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
    	p.jobs_uuid = $("#task_uuid").val();
        function update(){
        	if(0 == $('#speedchart').size()){
        		clearTimeout(timerTask.HadoopJobDetails_speed);
        		return;
        	}
            pAjaxRequest(p, "/api/v1/hadoop/jobs/speed", "GET", function (result) {
                initTaskSpeed(result.data);
                data.shift();
                data.push(result.data.speed);
                option.series[0].data = data;

                nowTime.shift();
                nowTime.push(result.data.nowTime);
                option.xAxis.data = nowTime;


                myChart.setOption(option);
            }, false)
            timerTask.HadoopJobDetails_speed = setTimeout(update, 3000);
        }
        update();

        window.onresize = function(){
        	myChart.resize();
        }
    }

    //根据任务状态设置按钮权限
    var setBtnStatus = function(data){
        //文件恢复
        if(2 == data.taskTypeFlag){
            var button = "";
            button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
            button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
            $('#fsOpList').html(button);
        }
        setControlBtn('start', true);
        setControlBtn('startFullTable', true);
        setControlBtn('startIncrTable', true);
        setControlBtn('startDiffTable', true);
        setControlBtn('startFull', true);
        setControlBtn('startIncr', true);
        setControlBtn('startDiff', true);
        setControlBtn('deletefs', true);
        switch(_taskStatus){
            case 5:
                //停止中
                setControlBtn('startFullTable', false);
                setControlBtn('startIncrTable', false);
                setControlBtn('startDiffTable', false);
                setControlBtn('start', false);
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('deletefs', false);
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
                break;
            case 2:
            case 10:
            case 12:
			case 19:
                //运行、准备中停止中,禁用运行 12是启动中 19是挂起状态(适配磁带)
                setControlBtn('startFullTable', false);
                setControlBtn('startIncrTable', false);
                setControlBtn('startDiffTable', false);
                setControlBtn('deletefs', false);
                setControlBtn('start', false);
                setControlBtn('startFull', false);
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
                setControlBtn('stop', true);
                break;
			case 20:
				//删除中
				setControlBtn('startFullTable', false);
				setControlBtn('startIncrTable', false);
				setControlBtn('startDiffTable', false);
				setControlBtn('deletefs', false);
				setControlBtn('start', false);
				setControlBtn('startFull', false);
				setControlBtn('startIncr', false);
				setControlBtn('startDiff', false);
				setControlBtn('stop', false);
				break;
            case 4:
                //停止,禁用停止
                $('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
                setControlBtn('start', true);
                setControlBtn('startIncr', true);
                setControlBtn('startDiff', true);
                setControlBtn('stop', false);
                break;
            default:
                //其他状态,开启控制
                setControlBtn('start', true);
                setControlBtn('startIncr', true);
                setControlBtn('startDiff', true);
                setControlBtn('stop', true);
                break;
        }
		var timeStrategy = data.timeStrategy;
		if (timeStrategy && timeStrategy.length > 0) {
			for(var i=0;i<timeStrategy.length; i++){
				if(timeStrategy[i].mode == 2 || timeStrategy[i].mode == 9){  // 增量备份或永久增量禁用差备
					setControlBtn('startDiff', false);
					setControlBtn('startDiffTable', false);
				}else if(timeStrategy[i].mode == 3){
					setControlBtn('startIncr', false);
					setControlBtn('startIncrTable', false);
				}
				//如果为一次性备份 ,禁用增量和差异
				if(timeStrategy[i].type == 4){
					setControlBtn('startIncrTable', false);
					setControlBtn('startDiffTable', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
				}
				//如果开启文件归档，禁用增量和差异
				if(data.archiveflag !=2) {
					setControlBtn('startIncrTable', false);
					setControlBtn('startDiffTable', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
				}
			}
		}

    }

    //设置按钮是否可用
	var setControlBtn = function(id, available){
		if (available){
            $("." + id).find('.btn').prop('disabled', false);
        } else {
            $("." + id).find('.btn').prop('disabled', true);
        }
	}

	//下载跳过文件
	var downloadPassFun = function(history_uuid,agent_uuid){
		window.location.href = '/api/v1/hadoop/download_pass' + '?history_uuid=' + history_uuid + '&fsnodeuuid=' + fsnodeuuid + '&agent_uuid=' + agent_uuid + '&x-api-version=1.0-rev0';
	}

	//记录勾选
	var checkRecord = function () {
		var checkArr = [];
		$.each(checkIndex, function (index) {
			checkArr.push(checkIndex[index].agent_uuid);
		});
		$('#hadooptable').bootstrapTable('checkBy', {
			field: 'agent_uuid',
			values: checkArr
		})
	}

	//初始化历史任务表格
	var initHistoryGrid = function () {
		//表格option
		var jobs_uuid = $("#task_uuid").val();
		var lastIndex = [-1, -1];
		var errorFormatter = function (index, row) {
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

		var options = {
			onRefresh: function (params) {
				$("#historytable").bootstrapTable('hideLoading');
			},
			detailFormatter: function (row, data, div) {
				if (row != lastIndex[1]) {//只展开一行
					lastIndex.push(row);
					$('#historytable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
					lastIndex.splice(0, 1);
				}
				Metronic.blockUI({
					target: div,
					animate: true
				});
				pAjaxRequest({}, '/api/v1/jobs/history/' + data.job_id + '', 'GET', function (res){
					if(res.success){
						$('.hadoop-his-detail').html(getHisDetailsDes(res.data))
						//监听跳过文件详情
						var jobType =  res.data.info.job_type;
						let passFileList = res.data.list.list || [];
						var historyUUID = res.data.list.history_uuid;
						$('.downloadPassFile').off().click(function () {
							if(jobType === 1){ //备份
								$('#passFileModal').modal({'width':"750px", 'height': "300px"});
								var agentUUID = $(this).attr('value');
								var passFileDetailData = [];
								var passFileReasonData = [];
								if (passFileList.length > 0) {
									passFileList.forEach(item => {
										var total = parseInt(item.dir_count) + parseInt(item.all_scan_file_count);

										passFileDetailData.push({
											total_pass_number: item.total_pass_number,
											total_pass_ratio: ((parseInt(item.total_pass_number) / total) * 100).toFixed(2) + '%',
											total_pass_dir_number: parseInt(item.total_pass_dir_number),
											total_pass_dir_ratio: ((parseInt(item.total_pass_dir_number) / total) * 100).toFixed(2) + '%',
											passfile_number_limit: res.data.list.passfile_number_limit,
											passfile_ratio_limit: res.data.list.passfile_ratio_limit
										});

										passFileReasonData.push({
											passfile_file_number_occupy: item.passfile_file_number_occupy,
											passfile_file_number_delete: item.passfile_file_number_delete,
											passfile_file_number_reject: item.passfile_file_number_reject,
											passfile_dir_number_reject: item.passfile_dir_number_reject,
											passfile_dir_number_delete: item.passfile_dir_number_delete,
											passfile_dir_number_other: (parseInt(item.passfile_dir_number_other)+ parseInt(item.passfile_file_number_other))
										})
									})
								}
								let passFilesDetailsOption = {
									columns: [
										{
											field: 'total_pass_number',
											title: LANG.UI_FILE_COUNT_PASS,
											align: 'center',
										},
										{
											field: 'total_pass_ratio',
											title: LANG.UI_HADOOP_SKIP_FILE_RATIO,
											align: 'center',
										},
										{
											field: 'total_pass_dir_number',
											title: LANG.UI_FILE_COUNT_PASS_DIR,
											align: 'center',
										},
										{
											field: 'total_pass_dir_ratio',
											title: LANG.UI_HADOOP_SKIP_DIRECTORY_RATIO,
											align: 'center',
										},
										{
											field: 'passfile_number_limit',
											title: LANG.UI_NAS_SKIP_FILE_ALARM_NUM,
											align: 'center',
										},
										{
											field: 'passfile_ratio_limit',
											title: LANG.UI_NAS_SKIP_FILE_ALARM_RATIO,
											align: 'center',
										}
									],
									data: passFileDetailData
								}

								let passFileReasonsOption = {
									columns: [
										{
											field: 'passfile_file_number_occupy',
											title: LANG.UI_HADOOP_OCCUPIED_FILE_NUM,
											align: 'center',
										},
										{
											field: 'passfile_file_number_delete',
											title: LANG.UI_HADOOP_DELETE_FILE_NUM,
											align: 'center',
										},
										{
											field: 'passfile_file_number_reject',
											title: LANG.UI_HADOOP_NO_PERMISSION_FILE_NUM,
											align: 'center',
										},
										{
											field: 'passfile_dir_number_reject',
											title: LANG.UI_HADOOP_NO_PERMISSION_DIRECTORY_NUM,
											align: 'center',
										},
										{
											field: 'passfile_dir_number_delete',
											title: LANG.UI_HADOOP_DELETE_DIRECTORY_NUM,
											align: 'center',
										},
										{
											field: 'passfile_dir_number_other',
											title: LANG.UI_HADOOP_OTHER_NUM,
											align: 'center',
										}
									],
									data: passFileReasonData
								}

								$('#pass_files_details_table').bootstrapTable(passFilesDetailsOption);

								$('#pass_files_reason_table').bootstrapTable(passFileReasonsOption);


								// 下载按钮click监听
								$('#downloadTxt').off().click(function() {
									downloadPassFun(historyUUID, agentUUID);
								});

							}else if(jobType === 2){ //恢复
								downloadPassFun(historyUUID, '');
							}
						});
					}else {
                        UIToastr.showWarning(LANG.UI_HADOOP_GET_HISTORY_TASK_DETAILS_FAILED, res.message);
                    }
					Metronic.unblockUI(div);
				});
				return '<div class="hadoop-his-detail"></div>';

			},

			searchInput: false,
			pagination: true,
			pageList: [5, 10, 25, 50],
			sortName:'start_time',
			sortOrder:'desc',
			detailView: true,
			vin_url: '/api/v1/jobs/' + jobs_uuid + '/history',
			vin_method: "GET",
			columns: [{
				checkbox: true,
				sortable: false
			},
				{
					field: 'num',
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: false,
					align: 'center',
			},
				{
					field: 'job_type',
					title: LANG.UI_SEARCH_TASK_TYPE,
					sortable: true,
					align: 'center',
			},
				{
					field: 'job_status',
					title: LANG.UI_VISUAL_RESULT,
					sortable: true,
					align: 'center',
					formatter: errorFormatter
			},
				{
					field: 'all_size',
					title: LANG.UI_MICROSOFT365_ALL_SIZE,
					sortable: true,
					align: 'center',
			},
				{
					field: 'speed_size',
					title: LANG.UI_PUBLIC_TRANSFER_SIZE,
					sortable: false,
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

		//历史任务详情
		var getHisDetailsDes = function (data) {
			var html = '<table>'
			if(data.list.resource_limiting_node_config){
				var thead = `<tr>
                        <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                        <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                    </tr>`;
				let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                var tbody =
					`<tr>
						<td>` + LANG.UI_PUBLIC_ON + `</td>
						<td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
						<td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
					</tr>`;
				html += thead + tbody;
			}else{
				if (data.info.job_type == 1) { //备份
					var thead = '';
					thead += '<th>' + LANG.UI_HADOOP_HADOOP_CLUSTER_NAME + '</th>';
					thead += '<th>' + LANG.UI_SEARCH_TASK_TYPE + '</th>';
					thead += '<th>' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th>';
					thead += '<th>' + LANG.UI_FILE_WILDCARD + '</th>';
					thead += '<th> ' + LANG.UI_FILE_COUNT_PASS + ' </th>';
					thead += '<th> ' + LANG.UI_FILE_COUNT_PASS_DIR+ ' </th>';
					thead += '<th> ' + LANG.UI_FILE_PASSFILE_LISTS + ' </th>';
					thead += '<th> ' + LANG.UI_OS_PLUG_TOTAL_SIZE + ' </th>';
					thead += '<th> ' + LANG.UI_FILE_PROCESSED_CAPACITY + ' </th>';
					thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';

					var tbody = '';
					if(data.list.list == undefined) {
						tbody += "";
					}else {
						data.list.list.forEach(item=> {
							//通配符
							if(item.wildcard_list == undefined || item.wildcard_list == "") {
								wildcard = LANG.UI_PUBLIC_NOTHING;
								wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
							} else {
								var wildcard_list = JSON.parse(item.wildcard_list);
								var wildcard_mode,wildcard = [];
								if(wildcard_list.wildcard_mode != null && wildcard_list.wildcard_mode != 0) {
									wildcard = wildcard_list.wildcard.join('<br>');
									wildcard_list.wildcard_mode==1?wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER:wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
								}else {
									wildcard = LANG.UI_PUBLIC_NOTHING;
									wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
								}
							}
							//跳过目录个数
							var passdir = item.total_pass_dir_number == undefined ? 0 : item.total_pass_dir_number;
							tbody += '<tr>';
							tbody += '<td>' + item.src_agent_name+ '('+item.src_agent_ip+')' + '</td>';
							tbody += '<td>' + item.backup_mode + '</td>';
							tbody += '<td>' + wildcard_mode + '</td>';
							tbody += '<td style="width: 8%;">' + wildcard + '</td>';
							tbody += '<td>' + item.total_pass_number + '</td>';
							tbody += '<td>' + passdir + '</td>';
							var elecontent = '',style = '';
							if(item.passfile_exist_flag==1) {
								elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
								fsnodeuuid = item.node_uuid;
							}else {
								elecontent = LANG.UI_PUBLIC_NOTHING;
								style = 'style="color: black;pointer-events:none;"'
							}
							fsnodeuuid = item.node_uuid;
							var agent_uuid = item.agent_uuid
							tbody += '<td>' + '<a value="'+ agent_uuid +'" class="downloadPassFile" '+ style +'>'+ elecontent +'<span class="display-none">'+ item.passfile_file_path +'</span></a>' + '</td>';
							tbody += '<td>' + filterSize(item.total_size) + '</td>';
							tbody += '<td>' + filterSize(item.current_size) + '</td>';
							tbody += '<td style="width: 8%;">' + item.description + '</td>';
							tbody += '</tr>';
						});
					}
					html += thead + tbody;
				}
				if (data.info.job_type == 2) { //恢复
					var thead = '<thead>';
					// //恢复任务需要修改历史列表详情中的title
					// var gridtile = ''
					// if(source_submodule_type){
					// 	switch(source_submodule_type){
					// 		case 1: // 源端为文件客户端
					// 			gridtile = LANG.UI_FILE_SOURCE_CLIENT_IP; //客户端名/IP
					// 				break;
					// 		case 2: // 源端为nas设备
					// 			gridtile = LANG.UI_HADOOP_SOURCE_DEVICE_NAME; //nas设备
					// 			break;
					// 		case 3: // 源端为hadoop集群
					// 			gridtile = LANG.UI_HADOOP_SOURCE_CLUSTER_NAME; //源Hadoop集群名
					// 			break;
					// 		case 4: // 源端为对象存储
					// 			gridtile = LANG.UI_PLATFORM_DES_OBS_NAME; //对象存储名
					// 			break;
					// 		default:
					// 			break;
					// 	}
					// }else{
					// 	gridtile = LANG.UI_HADOOP_HADOOP_CLUSTER_NAME; //集群列表
					// }
					thead += '<th>' + LANG.UI_FILE_DETAIL_SRC_NAME + '</th>';
					thead += '<th>' + LANG.UI_FILE_DETAIL_DES_NAME + '</th>';

					thead += '<th>' + LANG.UI_FILE_RECOVER_PATH + '</th>';
					thead += '<th>' + LANG.UI_FILE_COUNT_TOTAL + '</th>';
					thead += '<th>' + LANG.UI_FILE_COUNT_PASS + '</th>';
					thead += '<th>' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>';
					thead += '<th>' + LANG.UI_FILE_PASSFILE_LISTS + '</th>';
					thead += '<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';

					var tbody = '<tr>';
					// tbody += '<td>' + data.list.list[0].src_agent_name +'</td>';
					var targetstr = data.list.list[0].src_agent_name;
					if(source_submodule_type != 4){
						targetstr += '(' + data.list.list[0].src_agent_ip + ')'
					}
					tbody += '<td>' + targetstr + '</td>';
					tbody += '<td>' + data.list.des_agent_name +'('+data.list.des_agent_ip+')' + '</td>';
					tbody += '<td style="padding-right: 20px;"><div class="historyfilelisttext">';
					var list = '';
					for(var i=0; i<data.list.file_list.length; i++){
						//需要去掉|
						list += replaceBetweenStartEnd(data.list.file_list[i], '|', '/', '').replace('|', '')+ "<br>";
					}
					tbody += list;
					tbody += '</div></td>';
					tbody += '<td>' + data.list.file_count + '</td>';

					tbody += '<td>' + data.list.list[0].total_pass_number + '</td>';
					tbody += '<td>' + data.list.list[0].total_pass_dir_number + '</td>';
					var elecontent = '',style = '';
					if(data.list.list[0].passfile_exist_flag==1) {
						elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
						fsnodeuuid = data.list.list[0].node_uuid;
					}else {
						elecontent = LANG.UI_PUBLIC_NOTHING;
						style = 'style="color: black;pointer-events:none;"'
					}
					fsnodeuuid = data.list.list[0].node_uuid;
					tbody += '<td>' + '<a class="downloadPassFile" '+ style +'>'+ elecontent +'<span class="display-none">'+ data.list.list[0].passfile_file_path +'</span></a>' + '</td>';
					tbody += '<td style="width: 8%;">' + data.list.list[0].description + '</td>';
					tbody += '</tr>';
					html += thead + tbody;
				}
			}

			return html;

		}


		var init = function () {
			if (!initHistoryFlag) {
				$('#historytable').baseTableConfig().init(options);
				initHistoryFlag = true;
			} else {
				$('#historytable').bootstrapTable('refresh');
			}
		}
		$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
			e.target // newly activated tab
			e.relatedTarget // previous active tab
			if ("#history" == e.target.hash) {
				init();
			}
		})
	}

	 //初始化集群列表表格
	var initHadoopGrid =  function(){
		var jobs_uuid = $("#task_uuid").val();
		// var gridtile = ''
		// //这里需要设置集群列表的title
		// if(source_submodule_type){
		// 	switch(source_submodule_type){
		// 		case 1: // 源端为文件客户端
		// 			gridtile = LANG.UI_MICROSOFT365_CLIENT_IP; //客户端名/IP
		// 				break;
		// 		case 2: // 源端为nas设备
		// 			gridtile = LANG.UI_HOMEPAGE_NAS_DEVICE; //nas设备
		// 			break;
		// 		case 3: // 源端为hadoop集群
		// 			gridtile = LANG.UI_HADOOP_HADOOP_CLUSTER_NAME; //Hadoop集群名
		// 			break;
		// 		case 4: // 源端为对象存储
		// 			gridtile = LANG.UI_PLATFORM_DES_OBS_NAME; //对象存储名
		// 			break;
		// 		default:
		// 			break;
		// 	}
		// }else{
		// 	gridtile = LANG.UI_HADOOP_HADOOP_CLUSTER_NAME; //集群列表
		// }
		var options = {
            detailFormatter: function (row, data, div) {
				if(!data){
					return;
				}
				return getHadoopDetails(data);
            },
			onRefresh: function (params) {
                $("#hadooptable").bootstrapTable('hideLoading');
				scroll = $("#hadooptable").bootstrapTable('getScrollPosition');
            },
			onPostBody: (data) => {
                if (null !== expandIndex) {
                    $("#hadooptable").bootstrapTable('expandRow', expandIndex);
                }
                $("#hadooptable").bootstrapTable('scrollTo', scroll);
				$('#hadooptable th[data-field="num"]').css('width', '3%');
				$('#hadooptable th[data-field="host_name"]').css('width', '17%');
				checkRecord(); //检查是否有勾选记录
            },
			onExpandRow: (index) => {
                if (null === expandIndex) {
                    expandIndex = index;
                } else if (index !== expandIndex) {
                    $("#hadooptable").bootstrapTable('collapseRow', expandIndex);
                    expandIndex = index;
                }
            },
			onCollapseRow: () => {
                expandIndex = null;
            },
			onCheck: function (row, $element) {
				var selectedRow = $('#hadooptable').bootstrapTable("getSelections");
                checkIndex = selectedRow;
			},
			onUncheck: function (row, $element) {
				var selectedRow = $('#hadooptable').bootstrapTable("getSelections");
                checkIndex = selectedRow;
			},
			onUncheckAll: function () {
				var selectedRow = $('#hadooptable').bootstrapTable("getSelections");
                checkIndex = selectedRow;
			},
            pagination: true,
            pageList: [5, 10, 25, 50],
            detailView: true,
            vin_url: "/api/v1/hadoop/jobs/" + jobs_uuid + "/detail",
            vin_method: "GET",
            columns: [{
                checkbox: true,
				sortable: false
            },
				{
				field: 'num',
				title: LANG.UI_PUBLIC_TABLE_ID,
				sortable: false,
				align: 'center',
			},
                {
                    field: 'host_name',
                    title: LANG.UI_FILE_DETAIL_SRC_NAME,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'job_type',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'total_num',
                    title: LANG.UI_HADOOP_TOTAL_NUM,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'completed_num',
                    title: LANG.UI_HADOOP_COMPLETE_FILE_NUM,
                    sortable: true,
                    align: 'center',
            },
                {
                    field: 'src_data_num',
                    title: LANG.UI_HADOOP_DIR_NUM,
                    sortable: true,
                    align: 'center',
            },

				{
					field: 'progress',
					title: LANG.UI_PUBLIC_PROGRESS,
					sortable: true,
					align: 'center',
			},
                {
                    field: 'status',
                    title: LANG.UI_VISUAL_RESULT,
                    sortable: true,
                    align: 'center',
            },
            ],
        }
		if (0 == $('#task_uuid').size()) {
			clearTimeout(timerTask.HadoopJobDetails_hadoopGrid);
			return;
		}
		if (!initHadoopFlag) {
			$('#hadooptable').baseTableConfig().init(options);
			initHadoopFlag = true;
		} else {
			queryParams.search = $('#searchVal').val();
			$('#hadooptable').bootstrapTable('refresh',{
				query: queryParams
			});

		}
		timerTask.HadoopJobDetails_hadoopGrid = setTimeout(initHadoopGrid, 5000);
		var getHadoopDetails =  function(data){
			if(CONF.TASK_TYPE.BACKUP == data.tasktype){
				//备份
				return getBackupHadoopDetails(data);
			}else if(CONF.TASK_TYPE.RECOVERY == data.tasktype){
				//恢复
				return getRecoveryHadoopDetails(data);
			}
		}

		//备份时集群详情
		var getBackupHadoopDetails  =  function(data){
			var html = '<table>'
			var allpath = [];
			var wildcardmode,wildcard = [];
			var datalist =  data.list;
			for(var i = 0; i < datalist.length; i++){
			    datalist[i] =  replaceBetweenStartEnd(datalist[i], '|', '/', '').replace('|', '')
			}
			allpath = datalist.join('\n');
			if(data.wildcardinfo.wildcardmode != null && data.wildcardinfo.wildcard != null) {
				wildcard = data.wildcardinfo.wildcard.join('<br>');
				data.wildcardinfo.wildcardmode==1?wildcardmode = LANG.UI_FILE_WILDCARD_BAK_FILTER:wildcardmode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
			}else {
				wildcard = LANG.UI_PUBLIC_NOTHING;
				wildcardmode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
			}

			var thead = '';
				thead += '<th>' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th>';
				thead += '<th>' + LANG.UI_FILE_WILDCARD + '</th>';
				thead += '<th>' + LANG.UI_FILE_BAK_PATH + '</th>';
			var tbody = '';
			tbody += '<tr>';
			tbody += '<td>' + wildcardmode + '</td>';
			tbody += '<td>' + wildcard+ '</td>';
			tbody += '<td>' + '<textarea style="outline: none;" cols="60" rows="3" disabled>' + allpath + '</textarea>' + '</td>';
			tbody += '</tr>';
			html += thead + tbody + '</table>';
			return html;
		}

		//恢复时集群详情
		var getRecoveryHadoopDetails  =  function(data){
			var html = '<table>';
			var datalist =  data.list;
			for(var i = 0; i < datalist.length; i++){
			    datalist[i] =  replaceBetweenStartEnd(datalist[i], '|', '/', '').replace('|', '')
			}
			allpath = datalist.join('\n');
			// var sourceClient = '';
			// switch(data.source_submodule_type){
			// 	case 1: // 源端为文件客户端
			// 		sourceClient = LANG.UI_FILE_HISJOB_SOURCE_NAME_AND_IP;
			// 		break;
			// 	case 2: // 源端为nas设备
			// 		sourceClient = LANG.UI_HADOOP_SOURCE_DEVICE_NAME;
			// 		break;
			// 	case 3: // 源端为hadoop集群
			// 		sourceClient = LANG.UI_HADOOP_SOURCE_CLUSTER_NAME;
			// 		break;
			// 	case 4: // 源端为对象存储
			// 		sourceClient = LANG.UI_HADOOP_SOURCE_S3_NAME;
			// 		break;
			// 	default:
			// 		break;
			// }
			var thead = '';
				thead += '<th>' + LANG.UI_FILE_DETAIL_SRC_NAME + '</th>';
				thead += '<th>' + LANG.UI_FILE_DETAIL_DES_NAME + '</th>';
				if(data.cross_platform_flag){
					thead += '<th>' + LANG.UI_FILE_CROSS_RESTORE + '</th>';
				}
				thead += '<th>' + LANG.UI_FILE_RECOVER_PATH + '</th>';
				thead += '<th>' + LANG.UI_FILE_RECOVERY_FILE_PATH + '</th>';
			var tbody = '';
			tbody += '<tr>';
			tbody += '<td>' + data.host_name + '</td>';
			tbody += '<td>' + data.des_agent+ '</td>';
			if(data.cross_platform_flag){
				tbody += '<td>' + data.cross_platform_des+ '</td>';
			}
			tbody += '<td>' + data.path+ '</td>';
			tbody += '<td>' + '<textarea style="outline: none;" cols="60" rows="3" disabled>' + allpath  + '</textarea>' + '</td>';
			tbody += '</tr>';
			html += thead + tbody + '</table>';
			return html;

		}
	}
	//初始化集群列表操作按钮监听事件
	var initHadoopGridOpBtn =  function(){
		$('.startFullTable').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startHadoopJob(1);
        });

        $('.startIncrTable').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startHadoopJob(2);
        });

        $('.startDiffTable').unbind().on('click', function(){
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startHadoopJob(3);
        });
	}
	//启动hadoop任务
	var startHadoopJob = function(mode){
		var selectedRows = $('#hadooptable').bootstrapTable('getSelections');
        if (selectedRows.length === 0) {
            return UIToastr.showInfo(LANG.UI_HADOOP_SELECT_CULSTER_BACKUP_TIP, LANG.UI_HADOOP_SELECT_CULSTER_BACKUP_TIP);
        }
		checkOperateAuth({
			type: 1,
			user_uuid: task_user_uuid,
			auth: 'current_job'
		}, function(){
			var jobs_uuid = $("#task_uuid").val();
			var params = {
				'task_uuid': jobs_uuid,
				'backup_mode': mode,
				'cluster_uuids': selectedRows.map(i => { return i.agent_uuid})
			}
			Metronic.blockUI({target: '#hadoop_job_details', animate: true});
			pAjaxRequest(params, '/api/v1/hadoop/jobs_start', 'post', (result) => {
				Metronic.unblockUI('#hadoop_job_details');
				var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
				if (operateResponseList(result, op)) {
				}
			})
		});
	}



	//转换文件大小
	const filterSize = (size) => {
		if (!size) return '';
		return size < 1024 ? size + ' B' :
			size < pow1024(2) ? (size / 1024).toFixed(2) + ' KB' :
			size < pow1024(3) ? (size / pow1024(2)).toFixed(2) + ' MB' :
				size < pow1024(4) ? (size / pow1024(3)).toFixed(2) + ' GB' :
				(size / pow1024(4)).toFixed(2) + ' TB'
	}


 	 // 求次幂
 	 function pow1024(num) {
		return Math.pow(1024, num)
 	 }
	//得到任务状态描述
	var getStatusDes = function (status) {
		var des = '--';
		switch (status) {
			case 1:
				des = LANG.UI_PUBLIC_WAIT;
				break;
			case 2:
				des = LANG.UI_VISUAL_RUN;
				break;
			case 3:
				des = LANG.UI_VISUAL_PAUSE;
				break;
			case 4:
				des = LANG.UI_VISUAL_STOP;
				break;
			case 5:
				des = LANG.UI_VISUAL_STOPPING;
				break;
			case 7:
				des = LANG.UI_VISUAL_NODE_ABNORMAL;
				break;
			case 8:
				des = LANG.UI_VISUAL_ERROR;
				break;
			case 12:
				des = LANG.UI_VISUAL_STARTING;
				break;
			case 19:
				des = LANG.UI_PUBLIC_PENDING;
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
	//初始化进度条-多集群状态下不显示进度条
	var initProgress = function(){
		var updateInterval = 5000;
		//重新请求一遍集群列表接口
		if(0 == $('#task_uuid').size()){
			clearTimeout(timerTask.HadoopProgress_basicInfo);
			return;
		}
		var jobs_uuid = $("#task_uuid").val();
		pAjaxRequest({}, "/api/v1/hadoop/jobs/" + jobs_uuid + "/detail", 'get', (result) => {
			if(result.success){
				var data = result.data;
				if(data.total > 1){
					$(".progressDiv").hide();
					$(".multiProgressDiv").show();
					$(".portlet-charts__body .multiProgressDiv").css({
						'height': '20px',
						'margin': '10px 0',
						'display': 'flex',
						'align-items': 'center'
					});
					for(var i=0;i<data.rows.length;i++) {
						if(data.rows[i].taskStatus== 1 || data.rows[i].taskStatus== 4){//整个任务是等待或停止状态
							$(".agentName").text("--");
						}else {
							if(data.rows[i].status== LANG.UI_FILE_DETAILS_JOB_SCANNING) {//扫描中
								$(".agentName").text(data.rows[i].host_name + " ; " + LANG.UI_FILE_DETAILS_JOB_CALCULATING);
							} else if (data.rows[i].status== LANG.UI_FILE_RUNNING) {//传输中
								$(".agentName").text(data.rows[i].host_name + " ; " + LANG.UI_FILE_DETAILS_JOB_TRANSFERING);
							}
						}
					}

				}else{
					$(".multiProgressDiv").hide();
					$(".progressDiv").show();
				}
			}
        },false);
		timerTask.HadoopProgress_basicInfo = setTimeout(initProgress, updateInterval);

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
	const initLogGrid = function(){ 
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
	}

    return {
        init:function(){
			// initSwiper();
            initBasicInfo();
            initSpeed();
			initProgress();
            initLogGrid();
            initHistoryGrid();
			initHadoopGrid();
			initHadoopGridOpBtn();
			watchEchartSizeChange();
        }
    }
}();

jQuery(document).ready(function() {
	hadoop_job_details.init();
});