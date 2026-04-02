var NASJobDetails = function () {

	var logGrid;
	var initLogFlag = false;	//任务日志初始化标志
	var initChartFlag = false; //任务曲线图初始化标志
	var passfile_file_path='';//用于下载跳过文件
	var fsnodeuuid = '';//用于下载跳过文件
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
	var detailsIndexLog = 0,detailsInfoLog = null;
	var jobParams = {}; //操作需要参数
	var _taskStatus; //监控任务状态
	var myChart;
	const RECOVERY_INTEGRITY_POLICY_DESC_MAP = {
		0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY,
		1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY,
		2: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK,
	}; // 验证策略 - 完整性校验异常处理描述
	let task_user_uuid = '';//任务关联的用户uuid
	//初始化基本信息
	var initBasicInfo = function(){

		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.NASJobDetails_basicInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getNasBasicInfo',p:data}, function(d){setBasicInfo(d, timerTask.NASJobDetails_basicInfo)});
			timerTask.NASJobDetails_basicInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		data = JSON.parse(data);
		//任务完成等待状态
		if(data.status!= LANG.UI_FILE_RUNNING){
			$('#total-progress').css({width: '0%'});
			$('#progressright').html('');
			data.totalSize = '--';
			data.currentSize = '--';
		}
		task_user_uuid = data.task_user_uuid;
		var archiveflag;
		_taskStatus = data.statusValue;
		jobParams.status = data.statusValue;
		jobParams.taskType = data.taskTypeFlag;
		jobParams.subModule = data.hypervisor;
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
		$('#taskType').html(data.moduleType + data.taskType);
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
		//写入重试策略值start
		$("#network_retry_times").html(data.retry_strategy.network_retry_times+LANG.UI_MICROSOFT365_TIME);
		$("#network_retry_interval").html(data.retry_strategy.network_retry_interval+LANG.UI_PUBLIC_SECOND);
		$('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
		if(data.retry_strategy.op_retry_flag){
			$("#opRetryTime").html(data.retry_strategy.op_retry_times+LANG.UI_MICROSOFT365_TIME);
			$("#opRetryInterval").html(data.retry_strategy.op_retry_interval+LANG.UI_PUBLIC_SECOND);
		}else{
			$('.op-retry-form-item').hide();
		}
		$('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
		if(data.retry_strategy.task_retry_flag){
			$('#task_retry_object').html(data.retry_strategy.task_retry_object == 1 ? LANG.UI_RETRY_FAILED_OBJS_IN_TASK : LANG.UI_RETRY_ALL_OBJS_IN_TASK);
			$('#taskRetryTime').html(data.retry_strategy.task_retry_times + LANG.UI_MICROSOFT365_TIME);
			$('#taskRetryInterval').html(data.retry_strategy.task_retry_interval / 60 + LANG.UI_MICROSOFT365_MINUTE);
		}else{
			$('.task-retry-form-item').hide();
		}
		$('.network-retry-wrap').hide();
        $('.network_retry_times_show').hide();
        $('.network_retry_interval_show').hide();
		//写入重试策略值end
		if(data.taskTypeFlag!=2) {//备份
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
			$('.wildcardmodeDiv').show();
			if(data.wildcardMode == 0) {
				$('#wildcardmode').html(getFlagLevelInfo(data.wildcardMode));
			}else {
				$('#wildcardmode').html(getWildcardMode(data.wildcardMode));
			}
			// 快照
			if (data.snapshot_flag == 1) {//snapshot_flag是从nas设备表查询的，判断是否显示快照
				data.snapShotFlag == 1 ? snapshotflag = true : snapshotflag = false;
				$('#silentsnapshotcheck').html(getFlagLevelInfo(snapshotflag));
				$('.snapshot-div').show();
			} else {
				$('.snapshot-div').hide();
			}
			
			// 扫描线程
			$('#scanThreadNum').html(data.scan_thread_num);
			if(data.scan_thread_num == 1) {
				// 扫描文件速度
				$('.scanFileDiv').show();
				$('#scanFileNum').html(getScanSpeed(data.scan_file_num));
			}
			// 文件权限备份
			$('#file_permission').html(getFlagLevelInfo(data.permission_operate_flag));
			// 归档
			if($('#archiveflag')!=undefined){
				data.archiveflag!=2?archiveflag=true:archiveflag=false;
				$('#archiveflag').html(getFlagLevelInfo(archiveflag));
				if(archiveflag) {
					$(".archivedesDiv").show();
					$('#archivedes').html(data.archiveflag==1?LANG.UI_FILE_FILE_DIRECTORY:LANG.UI_FILE_FILE);
				}
			}
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
			//磁带隐藏部分信息
			if (data.storageInfo.storage && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.TAPE) {
				$('.transfer-thread-num-form').hide();
				$('.safeDiv').hide();
			}
			//显示备份安全策略
			$('.backupSafeModeDiv').show();
			//安全策略
			$('#backup_worm_flag').html(getFlagLevelInfo(data.safe_strategy.worm_flag));
			$('#integrity_check_flag').html($.fn.getCompleteDetectionBackupDes({
				integrityCheckFlag: data.safe_strategy.integrity_check_flag,
				integrityCheckConfig: data.safe_strategy.integrity_check_config,
				isFullTimepointTitle: false,
				showIncrErrorPolicy: false,
				excludeTitleFlag: true,
			}));
			if (data.safe_strategy.worm_flag) {//WORM防护
				$('.backup_worm_date-form-item').show();
				$('#backup_worm_date').html(data.safe_strategy.worm_protection_time);
			} else {
				$('.backup_worm_date-form-item').hide();
			}
			//完整性校验
			if (data.safe_strategy.integrity_check_flag) {
				$('.integrity-check-des-div').show();
				//校验周期
				var cycledes = ''
				switch (data.safe_strategy.integrity_check_config.check_strategy) {
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
				//备份点异常
				var abnormaldes = '';
				switch (data.safe_strategy.integrity_check_config.full_error_policy) {
					case CONF.FULL_ABNORAL.REFULL:
						abnormaldes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE2;
						break;
					case CONF.FULL_ABNORAL.STOPBACKUP:
						abnormaldes = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_HANDLE_TYPE1;
						break;
				}
				$('#check_cycle').html(cycledes);
				$('#other_point_abnormal').html(abnormaldes);
			} else {
				$('.integrity-check-des-div').hide();
			}
		}

		//判断源列表显示
		getSrcListName(data);
		if(1 != data.taskTypeFlag){//恢复
			$('.is-backup-static-info').hide();
			$('.scanThreadDiv').hide();
			// $('#storageli').hide();
			$('.storagemodelDiv').hide();
			$('.wildcardmodeDiv').hide();
			$('.wildcardTitle').hide();
			$('.snapshotDiv').hide();
			$('.archiveDiv').hide();
			$('.startFull').hide();
			$('.startIncr').hide();
			$('.startDiff').hide();
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
			if (data.src_module_type && data.src_module_type != "OBS" && data.src_module_type != "HADOOP") {
				// 无效快捷方式清理
				$('.no-valid-clear-div').show();
				$('#no_valid_clear').html(getFlagLevelInfo(data.link_file_pass_flag));
			}
			// 跨平台任务不显示文件权限恢复
			if (data.src_sub_module_type == data.des_module_type) {
				$('#file_permission_recovery').html(getFlagLevelInfo(data.permission_operate_flag));
			} else {
				$('.permissionDiv').hide();
			}
			//恢复传输线程-磁带不显示
			if (data.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
				$('.transfer-thread-num-form').hide();
				$('.safeDiv').hide();
			}
			$('.backup-show').hide();
			//显示恢复安全策略
			$('.restoreSafeModeDiv').show();
			// 完整性校验异常处理
			if (!data.safe_strategy.integrity_check_flag) {
				$('#integrity_policy').html(LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_RECOVERY_UNSET);
			}else{
				$('#integrity_policy').html(RECOVERY_INTEGRITY_POLICY_DESC_MAP[data.safe_strategy.integrity_check_config.recovery_error_policy]);
			}
			//恢复时间策略
			if (data.timeStrategy[0] && data.timeStrategy[0].type == 4) {
				$('#recovery_type').html(LANG.UI_JOB_TIMING_RECOVER);
				$('#start_time').html(data.timeStrategy[0].startTime);
			} else {
				$('#recovery_type').html(LANG.UI_JOB_ONCE_TIME_RECOVER);
				$('.start-time-form').hide();
			}
		}
		// 安全策略未授权隐藏
        if (!CONF.FUNCTIONS.includes('worm')) {
            $('.worm-form-item').hide();
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            $('.integrity-form-item').hide();
        }
        if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
            $('.safeDiv').hide();
        }
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
		//时间策略
		//备份策略
		$('#createTime').html(data.createTime);
		$('#nextTime').html(data.nextTime);
		if (data.taskTypeFlag!=2) {
			$('.fullDiv').show();
			$('.incrDiv').show();
			$('.diffDiv').show();
			$('.pincrDiv').show();
		}
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
		var timeStrategy = getTimeStrategy(data.timeStrategy, data.timeStrategyBackupType);
		$('#fulldes').html(timeStrategy.full);
		$('#incdes').html(timeStrategy.incr);
		$('#diffdes').html(timeStrategy.diff);
		//永久增量
		$('#pincrdes').html(timeStrategy.pincr);

		if(data.reservedStrategy){
			if (data.storageInfo.storage && data.storageInfo.storage.typenum == CONF.BD_STORAGE_TYPE.TAPE) {
				//磁带获取策略替换保留策略
				$('.reservedDiv .name').html(LANG.UI_GLOBAL_STRATEGY_NAME);
				$('.reservedDiv .value').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
			} else {
				$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
			}
		}else{
			//恢复,隐藏保留策略
			$('.reservedDiv').hide();
		}

		// //加密传输
		// if(data.transportStrategy){
		// 	$('#encryptStrategy').html(getFlagLevelInfo(data.transportStrategy.encrypt));
		// }

		//存储信息
		if(data.storageInfo.flag){
			var node = data.storageInfo.node;
			var storage = data.storageInfo.storage;
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

			$('#nodeinfo').html(data.storageInfo.node_pool_nickname ?
								data.storageInfo.node_pool_nickname +":<br/>" + node.name + "<br>" + node.ip :
								node.name + "<br>" + node.ip);
			$('#storageinfo').html(data.storageInfo.storage_pool_nickname ?
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
		$('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);

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
	var getWildcardMode = function(flag) {
		var html = '';
		switch(flag){
			case 1:
				html = '<span class="label label-success">'+ LANG.UI_FILE_WILDCARD_BAK_FILTER +'</span>';
				break;
			case 2:
				html = '<span class="label label-success">'+ LANG.UI_FILE_WILDCARD_BAK_SELECT +'</span>';
				break;
			default:
				html = '----';
				break;
		}
		return html;

	}
	var initOpButton = function(data) {
		setBtnStatus(data);
		//任务详情按钮
		$('.start').unbind().on('click', function(){
    		if($(this).find('.btn').hasClass('disablebtn')){
     			return true;
     		}
			 startJobUnify('startJob', 1);
    	});
		$('.startFull').unbind().on('click', function(){
    		if($(this).find('.btn').hasClass('disablebtn')){
     			return true;
     		}
			 startJobUnify('startJob', 1);
    	});
    	$('.startIncr').unbind().on('click', function(){
    		if($(this).find('.btn').hasClass('disablebtn')){
     			return true;
     		}
			 startJobUnify('startJob', 2);
    	});
    	$('.startDiff').unbind().on('click', function(){
    		if($(this).find('.btn').hasClass('disablebtn')){
     			return true;
     		}
			 startJobUnify('startJob', 3);
    	});

    	$('.stop').unbind().on('click', function(){
    		if($(this).find('.btn').hasClass('disablebtn')){
     			return true;
     		}
			showStopTaskDialog().then(() => {
                opJob('stopJob');
            });
    	});
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
    	var opJob = function(funName){
			checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
				var data = {};
				data = jobParams;
				data.uuid = $('#task_uuid').val();
				data.module = 11;
				params = JSON.stringify(data);
				Metronic.blockUI({target: '#jobDetail',animate: true});
				$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
					Metronic.unblockUI('#jobDetail');
					if(OPREL(d)){
					}
				});
			});
		}

    	//启动任务
		var startJobUnify = function(funName, type){
			checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
				var data = {};
				data = jobParams;
				data.uuid = $('#task_uuid').val();
				data.module = 11;
				data.startType = type;
				params = JSON.stringify(data);
				Metronic.blockUI({target: '#jobDetail',animate: true});
				$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
					Metronic.unblockUI('#jobDetail');
					if(OPREL(d)){
					}
				});
			});
			}
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
				des = LANG.UI_FILE_PROCESS_SAME_FILE_ADD
				break;
			case 4:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_RENAME
				break;
			case 5:
				des = LANG.UI_FILE_PROCESS_SAME_FILE_REPLACE;
				break;
		}
		return des;
	}
	//得到开启和关闭内容，无样式
	var getFlagInfo = function(flag){
		var des = "";
		switch(parseInt(flag)) {
			case 1:
			  	des = LANG.UI_PUBLIC_ON;
				break;
			case 2:
				des = LANG.UI_PUBLIC_OFF;
				break;
		}
		return des;
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
			case CONF.TASK_STATUS.CLEANING:
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

	//得到时间策略描述信息
	var getTimeStrategy = function(msg, timeStrategyBackupType){
		var timeInfo = {full:LANG.UI_PUBLIC_NOTHING, incr:LANG.UI_PUBLIC_NOTHING, diff:LANG.UI_PUBLIC_NOTHING, pincr:LANG.UI_PUBLIC_NOTHING};
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
		if (msg.strategyMode === CONF.RESERVE_STRATEGY_MODE.POINT) { // 按备份点保留
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
		reservedStr +=  LANG.UI_STRATEGY_RESERVE_VALUE + ': ' + msg.value;
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
							color: CONF.VENDOR == 'vdms'? '#2A87C8':new echarts.graphic.LinearGradient(0, 0, 0, 1, [
								{
									offset: 0,
									color: 'rgba(59, 179, 70, 0.2)'
								},
								{
									offset: 1,
									color: 'rgba(59, 179, 70,0)'
								}
							]),
							opacity: CONF.VENDOR == 'vdms' ? 0.2 : 1,
						}},
					data:[]
				},
			],
			color: CONF.VENDOR == 'vdms'? ['#2A87C8']:['#44b6ae']
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
		//根据任务状态设置按钮权限
		var setBtnStatus = function(data){
			//nas恢复
			if(2 == data.taskTypeFlag){
				var button = "";
				button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
				button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
				$('#nasOpList').html(button);
			}
			setControlBtn('start', true);
			setControlBtn('startFull', true);
			setControlBtn('startIncr', true);
			setControlBtn('startDiff', true);
			var timeStrategy = data.timeStrategy;
			switch(_taskStatus){
				case 5:
					//停止中
					setControlBtn('start', false);
					setControlBtn('startFull', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
					$('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
					break;
					case 2:
					case 10:
					case 12:
						//运行、准备中停止中,禁用运行 12是启动中
						setControlBtn('startFull', false);
						setControlBtn('startIncr', false);
						setControlBtn('startDiff', false);
						setControlBtn('start', false);
						setControlBtn('stop', true);
						break;
				case 4:
					//停止,禁用停止
					$('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
					setControlBtn('start', true);
					setControlBtn('startIncr', true);
					setControlBtn('startDiff', true);
					setControlBtn('stop', false);
					break;
				case 19:
					//挂起
					$('.stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
					setControlBtn('startFull', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
					setControlBtn('start', false);
					break;
				case 20:
					//删除中
					setControlBtn('startFull', false);
					setControlBtn('startIncr', false);
					setControlBtn('startDiff', false);
					setControlBtn('start', false);
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
			for(var i=0;i<timeStrategy.length; i++){
				if(timeStrategy[i].mode == 2 || timeStrategy[i].mode == 9){
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
			}
			if(data.archiveflag !=2) {
				setControlBtn('startIncr', false);
		    	setControlBtn('startDiff', false);
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


    //初始化日志表格
    var initLogGrid = function () {
        $('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
    }




	var nasGridLoad = function(){
		if(detailsInfo){
			var openTr = $('#nas').find('tbody > tr')[detailsIndex];
			$(openTr).after(detailsInfo);
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
    }

	//初始化主机列表表格
	var initNASGrid = function(){
		var updateInterval = 5000;
    	var initFlag = false;
    	var grid = new Datatable();
    	var dataTableOpt = {
    			"ordering": false,
                "paging":false,
                "info":false
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() ){
        		clearTimeout(timerTask.NASJobDetails_nasGrid);
        		return;
        	}
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#task_uuid").val();
    			data = {m:CONF.M.JOB,f:'getDetailsNas',p:data};
    			grid.setAjaxParam(data);
    			grid.init({src: $("#nastable"), showDetail:true, onDataLoad:nasGridLoad, dataTable:dataTableOpt});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.NASJobDetails_nasGrid = setTimeout(init, updateInterval);
			//扫描中隐藏进度条
			var nastabledata = grid.getDataTable().data();
			if(nastabledata[0] && nastabledata[0][10].popover == LANG.UI_FILE_DETAILS_JOB_SCANNING) {
				$(".progressDiv").hide();
			}else if(nastabledata[0] && nastabledata[0][10].popover == LANG.UI_FILE_RUNNING) {
				$(".progressDiv").show();
			}else {
				$(".progressDiv").hide();
			}
		}
    	init();
    	$('#nastable').on('click', ' tbody td .row-details', function () {
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
            	$('#nas').find('tr .details').parent().remove();
            	$('#nas').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('#nas').find('tbody tr .details').parents('tr')[0];
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
		sOut += getNASDetails(data);
		sOut += '</table></td></tr>';
		$(nTr).after(sOut);
	}

	var getNASDetails = function(data){
		if(LANG.UI_NAS_BACKUP == data.tasktype){
			//文件备份
			return getBackupNASDetails(data);
		}else if(LANG.UI_NAS_RESTORE == data.tasktype){
			//文件恢复
			return getRecoveryNASDetails(data);
		}
	}

	//备份数据库详情
	var getBackupNASDetails = function(data){
		var allpath = [];
		var wildcardmode,wildcard = [];
		allpath = data[9].join('\n');
		if(allpath == "/") {//列表是一整个根目录 显示共享路径
			allpath = data.sharepath;
		}
		if(data[11].wildcardmode != 0) {
			wildcard = data[11].wildcard.join('<br>');
			data[11].wildcardmode==1?wildcardmode = LANG.UI_FILE_WILDCARD_BAK_FILTER:wildcardmode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
		}else {
			wildcard = LANG.UI_PUBLIC_NOTHING;
			wildcardmode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
		}
		var file_archive = data.file_archive_flag !=2 ? LANG.UI_FILE_ENABLE:LANG.UI_FILE_DISABLE;
		var details = '<thead><th style="width: 18%;">'+LANG.UI_FILE_COUNT_WILDCARD_MODE+'</th><th style="width: 18%;">'+LANG.UI_FILE_WILDCARD+'</th>'+
		// '<th style="width: 10%;">'+LANG.UI_ARCHIVE+'</th>'+
		'<th style="width: 18%;">'+ LANG.UI_NAS_BACKUP_PATH +'</th><th style="width: 42%;"></th></thead><tbody><tr><td>'
		+ wildcardmode +'</td><td>'+ wildcard +'</td>'+
		// '<td>'+ file_archive +'</td>'+
		'<td><textarea style="outline: none;" cols="60" rows="4">' + allpath + '</textarea></td></tr></tbody>';
		return details;
	}

	//恢复数据库详情
	var getRecoveryNASDetails = function(data){
		var allpath = [];
		allpath = data[9].join('\n');
		if(allpath == "/") {//列表是一整个根目录 显示共享路径
			allpath = data.sharepath;
		}
		//判断是否是跨平台恢复
		var className = 'display-none';
		if(data.cross_flag) {
			className = '';
		}
		var details = '<thead><th style="width: 18%;">' + LANG.UI_FILE_DETAIL_SRC_NAME +'</th><th style="width: 18%;">'
		+ LANG.UI_FILE_DETAIL_DES_NAME + '</th><th style="width: 18%;" class="'+ className +'">'
			+ LANG.UI_FILE_CROSS_RESTORE + '</th><th style="width: 18%;">'
		+ LANG.UI_FILE_RECOVER_PATH + '</th><th style="width: 18%;"> '
		+ LANG.UI_FILE_RECOVERY_FILE_PATH +' </th></thead><tbody style="text-aligh: left;"><tr><td>'
		+  data.res_nas +'</td><td>'
		+ data.des_nas +'</td><td class="'+ className +'">'
		+ data.cross_des +'</td><td>'
		+ data.path + '</td><td><textarea style="outline: none;" cols="60" rows="3">'
		+ allpath + '</textarea></td></tr></tbody>';
		return details;
	}
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
				addHisDetails(tr[detailsIndexLog], data[detailsIndexLog]);
				var openTr = $('#historytable').find('tbody > tr')[detailsIndexLog];
				$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
			}
		}
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
		var setLevel = function(div, data){
			if(!data) return;
			var labelClass = getLevelClass(data[8].level);
			var content = '<span class="label ' + labelClass + '">' +
				'<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="right" data-content="' +
				data[8].popover + '" >'+ data[2] + '</a></span>';
			$(div).html(content);
		}
		var dataTableOpt = {
				'columnDefs' : [{
					'orderable': false,
					'targets': [0]
				}],
				"order": [
					[6, "desc"]
				],
		};
		var init = function(){
			if(!initFlag){
				var data = {};
				data.uuid = $("#task_uuid").val();
				data = {m:CONF.M.JOB,f:'getNASDetailsHistory',p:data};
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
				addHisDetails(nTr, data[row]);
				detailsIndexLog = $(this).parents('tr')[0].rowIndex - 1;
				detailsInfoLog = $('#historytable').find('tbody tr .details').parents('tr')[0];
			}

			return;
		});

		var downloadPassFun = function(history_uuid,agent_uuid) {
			var data = JSON.stringify({'history_uuid':history_uuid
				,'fsnodeuuid':fsnodeuuid,'agent_uuid':agent_uuid});
						Metronic.blockUI({target: '#historytable',animate: true});
					 $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'downLoadPassFile',p:data}, function(d){
						Metronic.unblockUI('#historytable');
						 var jsonFlag = false;
						 try {
							 $.parseJSON(d);
							jsonFlag = true;
						 } catch (e) {
							 jsonFlag = false;
						 }
						if(jsonFlag && !OPREL(d)){
							return ;
						}else{
							window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.JOB + '&f=downLoadPassFile&p=' + data;
						}

					});
		}

		//添加详情信息
	var addHisDetails = function(nTr, data){
		if(!data){
			return;
		}
		var sOut = '<tr class="details"><td class="details" colspan="13">';
		sOut += '<table>';
		sOut += getHisDetails(data[9]);
		sOut += '</table></td></tr>';
		$(nTr).after(sOut);
		$("a.downloadPassFile").click(function(){
			if(data[9].taskType == 1){//备份
				$('#passFileModal').modal({'width':"750px", 'height': "300px"});
				$('#downloadTxt').off().click(function() {
					downloadPassFun(data.history_uuid,"");
				});
			} else {//恢复
				downloadPassFun(data.history_uuid,"");
			}
		});
	}
	var getHisDetails = function(data){
		if (data.info.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
			let thead = `<tr>
						<th style="background-color: #F1F3F5;">` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
						<th style="background-color: #F1F3F5;">` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
						<th style="background-color: #F1F3F5;">` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
					</tr>`;

			let resourceLimitingNodeConfig = data.info.resource_limiting_node_config;
			let tbody =
				`<tr>
					<td>` + LANG.UI_PUBLIC_ON + `</td>
					<td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
					<td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
				</tr>`;
			return `<div class="table-container" style="max-height: 250px;overflow: auto;"><table style="width: 100%;" class="table" id="fileHistoryDetailtable">${thead}${tbody}</table></div>`;
		}
    	if(1 == data.taskType){
    		//文件备份
    		return getBackupHisDetails(data.info);
    	}else if(2== data.taskType){
    		//文件恢复
    		return getRecoveryHisDetails(data.info);
    	}
    }

    //备份数据库详情
    var getBackupHisDetails = function(data){
		var dataSet = [],passFile = [];
		var tbodyContent = "",details = "";
		if(data.agent_info_list.length != 0) {
			data.agent_info_list.forEach(item=> {
				fsnodeuuid = item.node_uuid;
				//通配符
				if(item.wildcard_list == "") {
					wildcard = LANG.UI_PUBLIC_NOTHING;
					wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
				}else {
					var wildcard_list = JSON.parse(item.wildcard_list);
					if(wildcard_list.wildcard_mode == 0) {
						wildcard = LANG.UI_PUBLIC_NOTHING;
						wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
					} else {
						var wildcard_mode,wildcard = [];
						if(wildcard_list.wildcard_mode != null) {
							wildcard = wildcard_list.wildcard.join('<br>');
							wildcard_list.wildcard_mode==1?wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER:wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
						}else {
							wildcard = LANG.UI_PUBLIC_NOTHING;
							wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
						}
					}
				}
				var file_archive = data.file_archive_flag !=2 ? LANG.UI_FILE_ENABLE:LANG.UI_FILE_DISABLE;
				dataSet.push([item.src_agent_ip+'('+item.src_agent_name+')',
							  item.total_pass_number,
							  filterSize(item.total_size),
							  filterSize(item.current_size),
							  filterSize(item.write_size),//去掉显示
							  wildcard_mode,
							  wildcard,
							  item.passfile_file_path,
							  item.total_pass_dir_number,
							  item.description,
							  file_archive,
							  item.backup_mode,
							]);
				//跳过文件详情模态框表格里面的数据
				if(item.all_scan_file_count == undefined) {
					$('.passdetailTr').html('<td>' + item.total_pass_number +'</td><td>--</td><td>'+ item.total_pass_dir_number +'</td><td>--</td><td>0</td><td>0.00%</td>');
					$('.passreasonTr').html('<td>--</td><td>--</td><td>--</td><td>--</td><td>--</td><td>--</td>')
				} else {
					var total = parseInt(item.dir_count) + parseInt(item.all_scan_file_count);
					$('.passdetailTr').html('<td>' + item.total_pass_number +'</td><td>'
						+ ((item.total_pass_number/total)*100).toFixed(2)+'%</td><td>'
						+ item.total_pass_dir_number +'</td><td>'
						+ ((item.total_pass_dir_number/total)*100).toFixed(2)+'%</td><td>'
						+ data.passfile_number_limit +'</td><td>'
						+ data.passfile_ratio_limit +'%</td>');
					$('.passreasonTr').html('<td>'+ item.passfile_file_number_occupy +'</td><td>'
						+ item.passfile_file_number_delete +'</td><td>'
						+ item.passfile_file_number_reject+'</td><td>'
						+ item.passfile_dir_number_reject +'</td><td>'
						+ item.passfile_dir_number_delete +'</td><td>'
						+ (parseInt(item.passfile_dir_number_other)+ parseInt(item.passfile_file_number_other)) +'</td>');
				}
			});
			dataSet.forEach(item => {
				var elecontent = '',style = '';
				if((parseInt(item[1]) + parseInt(item[8])) != 0) {
					elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
				}else {
					elecontent = LANG.UI_PUBLIC_NOTHING;
					style = 'style="color: black;pointer-events:none;"'
				}
				tbodyContent += '<tr>'+
									'<td style="padding-top: 25px;">'+ item[0]+'</td>'+
									'<td style="padding-top: 25px;">'+ item[11]+'</td>'+
									'<td style="padding-top: 25px;">'+ item[5]+'</td>'+
									'<td style="padding-top: 25px;">'+ item[6]+'</td>'+
									// '<td style="padding-top: 25px;">'+ item[10]+'</td>'+
									'<td style="padding-top: 25px;">'+ item[1]+'</td>'+
									'<td style="padding-top: 25px;">'+ item[8]+'</td>'+
									'<td style="padding-top: 25px;">' + '<a class="downloadPassFile" '+ style +'>'+ elecontent +'<span class="display-none">'+ item[7] +'</span></a>' + '</td>' +//跳过文件下载
									'<td style="padding-top: 25px;">'+ item[2]+'</td>'+
									'<td style="padding-top: 25px;">'+ item[3]+'</td>'+
									'<td style="padding-top: 25px;">'+ item[9]+'</td>'+
									// '<td><textarea style="outline: none;" cols="50" rows="3">'+item[2]+'</textarea></td>'+
								'</tr>';
			});
			var details = '<div class="table-container" style="max-height: 250px;overflow: auto;"><table style="width: 100%;" class="table" id="nasHistoryDetailtable"><thead><tr role="row"class="heading"><th width="15%">'+LANG.UI_NAS_BAK_DETAIL_HIS+'</th><th width="8%">'+LANG.UI_SEARCH_TASK_TYPE+'</th><th style="width: 10%;">'+LANG.UI_FILE_COUNT_WILDCARD_MODE+'</th><th style="width: 10%;">'+LANG.UI_FILE_WILDCARD+'</th>'+
			// '<th style="width: 5%;">'+LANG.UI_ARCHIVE+'</th>'+
			'<th width="10%">'+LANG.UI_FILE_COUNT_PASS+'</th><th width="10%">'+ LANG.UI_FILE_COUNT_PASS_DIR +'</th><th width="10%">' + LANG.UI_FILE_PASSFILE_LISTS + '</th><th width="7%">'
			+LANG.UI_OS_PLUG_TOTAL_SIZE+'</th><th width="10%">'+LANG.UI_FILE_PROCESSED_CAPACITY+'</th><th width="5%">'+LANG.UI_PUBLIC_DESCRIPTION+'</th></tr></thead><tbody>'+tbodyContent+'</tbody></table></div>';
		}else {
			return LANG.UI_PUBLIC_NOTHING;
		}
		return details;
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
    	//恢复数据库详情
    	var getRecoveryHisDetails = function(data){
			var tbodyContent = "",details = "",elecontent = '',style = '';
			if(data.agent_info_list[0].passfile_exist_flag==1) {
				elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
			}else {
				elecontent = LANG.UI_PUBLIC_NOTHING;
				style = 'style="color: black;pointer-events:none;"'
			}
			passfile_file_path = data.agent_info_list[0].passfile_file_path;
			fsnodeuuid = data.agent_info_list[0].node_uuid;
			tbodyContent += '<tr>'+
									'<td style="padding-top: 25px;">'+ data.src_agent +'</td>'+
									'<td style="padding-top: 25px;">'+ data.des_agent_ip+'('+data.des_agent_name+')'+'</td>'+
									'<td style="padding-top: 25px;"><textarea style="outline: none;" cols="40" rows="2">'+ data.file_list.join('\n') +'</textarea></td>'+
									'<td style="padding-top: 25px;">'+ data.file_count +'</td>'+
									'<td style="padding-top: 25px;">'+ data.agent_info_list[0].total_pass_number+'</td>'+
									'<td style="padding-top: 25px;">'+ data.agent_info_list[0].total_pass_dir_number+'</td>'+
									'<td style="padding-top: 25px;"><a class="downloadPassFile" '+ style +'>'+ elecontent +'<span class="display-none">'+ passfile_file_path +'</span></a></td>'+
								'</tr>';
			details = '<div class="table-container" style="max-height: 250px;overflow: auto;"><table style="width: 100%;" class="table" id="nasRecoveryDetailtable"><thead><tr role="row"class="heading"><th width="15%">'+ LANG.UI_FILE_DETAIL_SRC_NAME +'</th><th width="15%">'+ LANG.UI_FILE_DETAIL_DES_NAME +'</th><th width="15%">'+ LANG.UI_FILE_RECOVER_PATH +'</th><th width="10%">'+ LANG.UI_FILE_COUNT_TOTAL +'</th><th width="10%">'+ LANG.UI_FILE_COUNT_PASS +'</th><th width="10%">'+ LANG.UI_FILE_COUNT_PASS_DIR+'</th>  <th width="15%">'+ LANG.UI_FILE_PASSFILE_LISTS +'</th></tr></thead><tbody>'+tbodyContent+'</tbody></table></div>';

    		return details;
    	}



		$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
			e.target // newly activated tab
			e.relatedTarget // previous active tab
			if("#history" == e.target.hash){
				init();
			}
		  })
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

    return {
        //main function to initiate the module
        init: function () {
			// initSwiper();
        	initBasicInfo();
        	initSpeed();
            initLogGrid();
            initHistoryGrid();
			initNASGrid();
			watchEchartSizeChange();
        }
    };
}();

jQuery(document).ready(function() {
	NASJobDetails.init();
});