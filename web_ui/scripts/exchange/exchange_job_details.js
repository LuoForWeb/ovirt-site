var ExchangeJobDetails = function () {

    var logGrid;
    var initLogFlag = false;    //任务日志初始化标志
    var initChartFlag = false; //任务曲线图初始化标志
    var passfile_file_path = '';//用于下载跳过文件
    var fsnodeuuid = '';//用于下载跳过文件
    //控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
    var detailsIndex = 0, detailsInfo = null;
    var detailsIndexLog = 0, detailsInfoLog = null;
    var jobParams = {}; //操作需要参数
    var _taskStatus; //监控任务状态
    var taskType;//判断是备份还是恢复
    var initGridFlag = false;
    var  expandIndex = null
    var myChart;
    var firstLocation = true;//恢复任务完成只弹出一次倒计时
    const RECOVERY_INTEGRITY_POLICY_DESC_MAP = {
		0: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY,
		1: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY,
		2: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK,
	}; // 验证策略 - 完整性校验异常处理描述
    let task_user_uuid = '';//任务关联的用户uuid
    //初始化基本信息
    var initBasicInfo = function () {
        var updateInterval = 2000;
        var init = function () {
            if (0 == $('#task_uuid').size()) {
                clearTimeout(timerTask.ExchangeJobDetails_basicInfo);
                return;
            }
            var data = {};
            var jobs_uuid = $("#task_uuid").val();
            pAjaxRequest({}, "/api/v1/exchange/jobs/" + jobs_uuid, "GET", function (result) {
                setBasicInfo(result.data, timerTask.ExchangeJobDetails_basicInfo)
            }, false);
            timerTask.ExchangeJobDetails_basicInfo = setTimeout(init, updateInterval);
        }
        init();
    }

    //设置基本信息
    var setBasicInfo = function (data, timeoutID) {
        //任务完成等待状态
        if (data.job_status != 2) {
            $('#total-progress').css({width: '0%'});
            $('#progressright').html('');
            data.total_size = '--';
            data.complate_size = '--';
        }
        var archiveflag;
        _taskStatus = data.job_status;
        jobParams.status = data.job_status;
        jobParams.taskType = data.job_type;
        jobParams.subModule = 1;
        task_user_uuid = data.task_user_uuid;
        if (!data.flag && firstLocation) {
            firstLocation = false;
            clearTimeout(timeoutID);
            UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
            setTimeout(function () {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }, 5000);
        }
        //初始化操作按钮
        initOpButton(data)
        $('#taskName').html(data.job_name);
        $('#taskName').attr('title',data.job_name);
        $('#taskType').html(data.module_type_des + data.job_type_des);
        if (data.job_status) {
            $('#status').html('<span class="label ' + getStatusLevelClass(data.job_status) + '" >' + getStatusDes(data.job_status) + '</span>');
        }
        $('#task_stage').html(data.current_stage_value);
        $('#currentSize').html(data.complate_size);
        $('#startTime').html(data.start_time);
        $('#intervalTime').html(data.interval_time);

        $('#speed').html(data.speed);
        $('#progress').html(data.progress);
        $('#backup_transfer_thread_num').html(data.thread_num);
        let transfetStrategyShow = false;
        $('.transfet-strategy').hide();
        //传输网络
        if (data.network_flag) {
            let networkDes = ``;
			if (data.transport_strategy.network_pool_uuid) {
				networkDes += data.transport_strategy.network_pool_nickname + '<br>';
			}
			if (data.transport_strategy.network_uuid) {
				networkDes += data.transport_strategy.network + '<br>';
			}
			if (networkDes) {
				$("#backup_transmit_network").html(networkDes);
			} else {
				$("#backup_transmit_network").html(LANG.UI_NODE_NETWORK_MODE_AUTO_MATCH);
			}
            $('.backup-transmit-network-form-item').show();
            transfetStrategyShow = true;
        } else {
            $('.backup-transmit-network-form-item').hide();
        }
        // 加密传输
        if (data.region == 100) {//server
            $('.backup-encrypt-transmit-form-item').show();
            transfetStrategyShow = true;
            $('#backup_encrypt_transmit').html(getFlagLevelInfo(data.transport_strategy.encrypt_flag));
            // 传输加密算法
            if (data.transport_strategy.encrypt_flag_value) {
                $('.backup-transfer-encrypt-method-form-item').show();
                let encryptMethod = data.transport_strategy.encrypt_method;
                let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                if (encryptMethod == 2) {
                    method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                }
                $('#backup_transmit_encrypt_method').html(method);
            } else {
                $('.backup-transfer-encrypt-method-form-item').hide();
            }
        } else {
            $('.backup-encrypt-transmit-form-item').hide();
            $('.backup-transfer-encrypt-method-form-item').hide();
        }
        //重试策略
        $('#network_retry_times').html(data.retry_strategy.network_retry_times);
        $('#network_retry_interval').html(data.retry_strategy.network_retry_interval);
        $('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
        $('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
        if (data.retry_strategy.op_retry_flag) {
            $('#op_retry_times').html(data.retry_strategy.op_retry_times);
            $('#op_retry_interval').html(data.retry_strategy.op_retry_interval);
        } else {
            $('.op-retry-form-item').hide();
        }
        if (data.retry_strategy.task_retry_flag) {
            $('#task_retry_object').html(data.retry_strategy.task_retry_object == 1 ? LANG.UI_RETRY_STRATEGY_OBJECT_OPTION1 : LANG.UI_RETRY_STRATEGY_OBJECT_OPTION2);
            $('#task_retry_times').html(data.retry_strategy.task_retry_times);
            $('#task_retry_interval').html(data.retry_strategy.task_retry_interval / 60);
        } else {
            $('.task-retry-form-item').hide();
        }
       //过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
        taskType = data.job_type;
        //指定客户端
        $('#backup_agent_config').html(getFlagLevelInfo(data.agent_config_flag));
        if (data.agent_config_flag) {
            transfetStrategyShow = true;
            $('.agent-des-div').show();
            $('#backup_agent_des').html(data.agent_des);
        } else {
            $('.agent-des-div').hide();
        }
        if (data.job_type != 2) {//备份
            $('.is-recovery-static-info').hide();
            $('#compress_storage').html(getFlagLevelInfo(data.storage_info.high.compressed));
            // 压缩等级
            if (data.storage_info.high.compressed) {
                var method = '';
                switch (data.storage_info.high.compress_method) {
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
                $('#compress_method').html(method);
            } else {
                $('.compress-method-form-item').hide();
            }
            //如果数据加密关闭隐藏自动生成密码开关描述显示，数据加密算法
            if (data.storage_info.high.encrypt_flag) {
                $('.auto-password-form-item').show();
                $('.encrypt-method-div').show();
                $('.storage-encrypt-method-form-item').show();
                let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                if (data.storage_info.high.encrypt_method == 2) {
                    method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                }
                $('#encrypt_storage_method').html(method);
            } else {
                $('.auto-password-form-item').hide();
                $('.encrypt-method-div').hide();
                $('.storage-encrypt-method-form-item').hide();
            }
            $('#encrypt_storage').html(getFlagLevelInfo(data.storage_info.high.encrypt_flag));
            $('#storage_auto_password').html(getFlagLevelInfo(data.storage_info.high.password_auto_flag));
        }

        if (1 != data.job_type) {//恢复
            $('.is-backup-static-info').hide();
            $('#storageli').hide();
            $('#tab_1_2').hide();
            $('.snapshotDiv').hide();
            $('.archiveDiv').hide();
            $('.startFull').hide();
            $('.startIncr').hide();
            $('.startPincr').hide();
            $('.backup_style').hide();
            $('.createTimeDiv').hide();
            $('.nextTimeDiv').hide();
            $('.total-size-div').show();
            $('#totalSize').html(data.total_size);
            $('#timeStrategyBackupType').parent('.static-info').hide();
            //恢复传输线程-磁带不显示
            if (data.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
                $('.threadDiv').hide();
                $('.safeDiv').hide();
            }
            //恢复时间策略
			if (data.time_strategy[0] && data.time_strategy[0].type == 4) {
				$('#recovery_type').html(LANG.UI_JOB_TIMING_RECOVER);
				$('#start_time').html(data.time_strategy[0].start_time);
			} else {
				$('#recovery_type').html(LANG.UI_JOB_ONCE_TIME_RECOVER);
				$('.start-time-form').hide();
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
        //时间策略
        //备份策略
        $('#createTime').html(data.create_time);
        $('#nextTime').html(data.next_time);
        if (data.job_type!=2) {
            $('.static-info-backup-full').show();
            $('.static-info-backup-increment').show();
            $('.static-info-backup-diff').show();
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
        switch (data.time_strategy_backup_type) {
            case 'strategy':
                $('#timeStrategyBackupType').html(LANG.UI_BACKUP_USE_STRATEGY);
                break;
            case 'oncetime':
                $('#timeStrategyBackupType').html(LANG.UI_BACKUP_ONCE);
                break;
            default:
                $('#timeStrategyBackupType').html(LANG.UI_BACKUP_MANUAL);
                $('.static-info-backup-full').hide();
                $('.static-info-backup-increment').hide();
                $('.static-info-backup-diff').hide();
                break;
        }
        // 在编排中的任务，显示按编排策略执行（原时间策略不生效）
        if (data.task_orchestration_plan_flag) {
            $('#timeStrategyBackupType').html(LANG.UI_JOB_TASK_ORCHESTRATION_JOB_DETAIL_STRATEGY);
        }
        var timeStrategy = getTimeStrategy(data.time_strategy, data.time_strategy_backup_type);
        //完全备份
        $('#fulldes').html(timeStrategy.full);
        //增量备份
        $('#incdes').html(timeStrategy.incr);
        //永久增量
        $('#pincrdes').html(timeStrategy.pincr);
        if (data.reserve_strategy) {
            if (data.storage_info.storage.type == LANG.UI_STORAGE_TYPE_TAPE) {
                //磁带获取策略替换保留策略
                $('.reserve-div .strategy-group__form__item__label').html(LANG.UI_GLOBAL_STRATEGY_NAME);
                $('.reserve-div #reserve_strategy').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${data.tape_strategy.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${data.tape_strategy.reserve_strategy_des}`);
            } else {
                $('#reserve_strategy').html(getReservedStrategy(data.reserve_strategy));
            }
        } else {
            //恢复,隐藏保留策略
            $('.reserve-div').hide();
        }
        //存储信息
        if (data.storage_info.flag) {
            var node = data.storage_info.node;
            var storage = data.storage_info.storage;
            var storage_info = '';
            if (!storage) {
                //没有存储信息,自动选择存储
                storage_info = LANG.UI_JOB_AUTO_SELECT_STORAGE;
            } else {
                storage_info = storage.name + "(" + storage.type + ")<br>";
                if (!storage.quotaFlag) {
                    storage_info += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
                        LANG.UI_JOB_FREE_SIZE + ":" + storage.free_size;
                } else {
                    storage_info += storage.quotades;
                }
            }
            $('#backup_node').html(data.storage_info.node_pool_nickname ?
                                    data.storage_info.node_pool_nickname + ":<br/>" + node.name:
                                    node.name);
            $('#storage_device').html(data.storage_info.storage_pool_nickname ?
                                    data.storage_info.storage_pool_nickname +":<br/>" + storage_info:
                                    storage_info);
            //磁带隐藏部分信息
            if (data.storage_info.storage.type == CONF.BD_STORAGE_TYPE.TAPE) {
                $('.threadDiv').hide();
                $('.safeDiv').hide();
                data.storage_type = 10;
            }
        }
        if (transfetStrategyShow || (CONF.FUNCTIONS.includes('multithread') && data.storage_type != 10)) {
            $('.transfet-strategy').show();
        }
        //限速策略
        $('#speed_limit_backup').html(data.speed_limit.value);
        $('#speed_limit_backup').prop('title', data.speed_limit.des);
        // 任务等级
        var task_priority = data.speed_limit.task_priority;
        if (task_priority) {
            switch (data.speed_limit.task_priority) {
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
            $('#task_priority_backup').html(task_priority);
        } else {
            $('.task-priority-form-item-backup').hide();
        }
        $('#total-progress').css({width: data.total_progress});
        $('#progressright').html(data.progress);

        let reconnect_times = data.transport_strategy.reconnect_times + LANG.UI_VOL_CDP_BACKUP_TIMES;
        let reconnect_interval = data.transport_strategy.reconnect_interval + LANG.UI_PUBLIC_SECOND;
        // 重连次数
        if (parseInt(data.transport_strategy.reconnect_times) == 0) {
            $("#reconnectTimes").html(LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE);
        } else {
            $('#reconnectTimes').html(reconnect_times);
        }
        // 重连时间间隔
        $('#reconnectInterval').html(reconnect_interval);

    }

    var initOpButton = function (data) {
        setBtnStatus(data);
        //任务详情按钮
        $('.start').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startJobUnify(1);
        });
        $('.startFull').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startJobUnify(1);
        });
        $('.startIncr').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
                return true;
            }
            startJobUnify(2);
        });

        $('.stop').unbind().on('click', function () {
            if($(this).find('.btn').prop('disabled')){
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

        var opJob = function (funName) {
            checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
                Metronic.blockUI({target: '#jobDetail', animate: true});
                var job_uuid = $('#task_uuid').val();
                if (funName == 'stopJob') {
                    pAjaxRequest({}, "/api/v1/jobs/stop/" + job_uuid + "", "POST",function (res) {
                        Metronic.unblockUI('#jobDetail');
                        var op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
                        if (operateResponseList(res, op)) {
                        }
                    });
                }
            });
        }

        //启动任务
        var startJobUnify = function (type) {
            checkOperateAuth({
				type: 1,
				user_uuid: task_user_uuid,
				auth: 'current_job'
			},function(){
                var params = {
                    'start_type': type
                };
                var job_uuid = $('#task_uuid').val();
                Metronic.blockUI({target: '#jobDetail', animate: true});
                pAjaxRequest(params, "/api/v1/jobs/start/" + job_uuid + "", 'POST', function (data) {
                    Metronic.unblockUI('#jobDetail');
                    var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
                    if (operateResponseList(data, op)) {
                    }

                });
            });
        }
    }
    //得到开启和关闭的HTML内容
    var getFlagLevelInfo = function (flag) {
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if (!flag || flag == LANG.UI_PUBLIC_OFF_ONE || flag == 2) {
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    //得到开启和关闭内容，无样式
    var getFlagInfo = function (flag) {
        var des = "";
        switch (parseInt(flag)) {
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
    var getStatusLevelClass = function (level) {
        var levelClass = '';
        switch (level) {
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

    //得到任务状态描述
    var getStatusDes = function (status) {
        var des = '--';
        switch (status) {
            case 1:
                des = LANG.UI_VISUAL_WAIT;
                break;
            case 2:
                des = LANG.UI_VISUAL_RUN;
                break;
            case 3:
                des = LANG.UI_JOB_PAUSE;
                break;
            case 4:
                des = LANG.UI_JOB_STOP;
                break;
            case 5:
                des = LANG.UI_PUBLIC_STOPPING;
                break;
            case 7:
                des = LANG.UI_VISUAL_NODE_ABNORMAL;
                break;
            case 8:
                des = LANG.UI_PUBLIC_ERROR;
                break;
            case 12:
                des = LANG.UI_PUBLIC_STARTING;
                break;
            case 19:
                des = LANG.UI_PUBLIC_PENDING;
                break;
            case 21:
                des = LANG.UI_NODE_STATUS_CLEANING;
                break;
        }
        return des;
    }

    //得到时间策略描述信息
    var getTimeStrategy = function (msg, time_strategy_backup_type) {
        var timeInfo = {full:LANG.UI_PUBLIC_NOTHING, incr:LANG.UI_PUBLIC_NOTHING, diff:LANG.UI_PUBLIC_NOTHING, log:LANG.UI_PUBLIC_NOTHING, pincr:LANG.UI_PUBLIC_NOTHING};
        if (!msg) {
            return timeInfo;
        }
        for (var i = 0; i < msg.length; i++) {
            var strategy = msg[i];
            var des = "";
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
            } else {
                des += LANG.UI_PUBLIC_NOTHING;
            }
            if (1 == strategy.mode) {
                if (time_strategy_backup_type == "strategy") { //按策略备份才显示完备补偿
					if(strategy.full_backup_compensation_flag){
						des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_ON;
					} else {
						des += LANG.UI_GLOBAL_STRATEGY_LABEL_FULL_BACKUP_SKIP_BTN + ': ' + LANG.UI_PUBLIC_OFF;
					}
				}
                timeInfo.full = des;
            } else if (2 == strategy.mode) {
                timeInfo.incr = des;
            } else if (9 == strategy.mode) {
                timeInfo.pincr = des;
            }
        }
        return timeInfo;
    }

    //得到备份间隔描述
    var getStrategyFrequency = function (strategy) {
        var frequency = "";
        var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
        for (var i = 1; i <= 52; i++) {
            if (strategy.frequency == "s" + i) {
                if (i == 1) {
                    frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
                } else {
                    frequency = frequencyLang.replace('x', i) + ",";
                }
            }
        }
        return frequency;
    }
    var getStrategyDays = function (days) {
        var desDays = '';
        $.each(days, function (i, d) {
            if (1 == d) {
                var day = i + 1;
                if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                    desDays += day + ", ";
                } else {
                    desDays += "Day" + day + ", ";
                }
            }
        });
        return desDays;
    }
    //获取每周显示日期
    var getStrategyWeek = function (days) {
        var desDays = '';
        $.each(days, function (i, d) {
            if (1 == d) {
                desDays += CONF.WEEK[i] + ", ";
            }
        });
        return desDays;
    }
    var getEachStrategy = function (strategy) {
        var desEach = '';
        desEach += strategy.start_time;
        //如果是英文版 需要加空格
        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
            desEach += " "; //策略开始时间
        }
        desEach += LANG.UI_STRATEGY_START + ", ";
        if (strategy.roll_flag) {
            desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.end_time;
        } else {
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }
        desEach += "<br>";
        return desEach;
    }

    //得到保留策略描述信息
    var getReservedStrategy = function (msg) {
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
        var methoddes = LANG.UI_STRATEGY_VALUE;
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.RESERVE_TYPE.DAY == msg.type){
			methoddes = LANG.UI_GLOBAL_STRATEGY_RESERVE_DAY;
		}
        if (msg.type != CONF.RESERVE_TYPE.PERMANENT) {
            reservedStr += methoddes + ': ' + msg.value;
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
        var data = [], nowTime = [];

        var parseNum = function (num) {
            num = parseInt(num);
            num = num >= 10 ? num : "0" + num;
            return num;
        }

        var getShowTime = function (timeStamp) {
            var myDate = new Date(parseInt(timeStamp));
            var date = myDate.toLocaleDateString();
            var hours = myDate.getHours();
            var minutes = myDate.getMinutes();
            var seconds = myDate.getSeconds();
            return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
        }

        //初始化任务进度曲线图
        var initTaskSpeed = function (d) {

            if (initChartFlag) {
                return; //初始化了就直接返回
            }

            var serverTime = d.t * 1000;
            for (var i = 100; i > 0; i--) {
                data.push(0);
                nowTime.push(getShowTime(serverTime - i * 3000));
            }
            option.series[0].data = data
            option.xAxis.data = nowTime;
            myChart.setOption(option);

            initChartFlag = true;
        }
        var p = {};
        p.jobs_uuid = $("#task_uuid").val();

        function update()
        {
            if (0 == $('#speedchart').size()) {
                clearTimeout(timerTask.ExchangeJobDetails_speed);
                return;
            }
            pAjaxRequest(p, "/api/v1/exchange/jobs/speed", "GET", function (result) {
                initTaskSpeed(result.data);
                data.shift();
                data.push(result.data.speed);
                option.series[0].data = data;

                nowTime.shift();
                nowTime.push(result.data.nowTime);
                option.xAxis.data = nowTime;


                myChart.setOption(option);
            }, true)
            // .complete(function () {
            timerTask.ExchangeJobDetails_speed = setTimeout(update, updateInterval);
        }

        update();

        window.onresize = function () {
            myChart.resize();
        }

    }
    //根据任务状态设置按钮权限
    var setBtnStatus = function (data) {
        //exchange恢复
        if (2 == data.job_type) {
            var button = "";
            button += '<li class="start"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_JOB_START + '</button></li>';
            button += '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>';
            $('#exchangeOpList').html(button);
        }
        setControlBtn('start', true);
        setControlBtn('startFull', true);
        setControlBtn('startIncr', true);
        setControlBtn('startDiff', true);
        var timeStrategy = data.time_strategy;
        switch (_taskStatus) {
            case 5:
                //停止中
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
        for (var i = 0; i < timeStrategy.length; i++) {
            if (timeStrategy[i].mode == 2) {
                setControlBtn('startDiff', false);
            } else if (timeStrategy[i].mode == 3) {
                setControlBtn('startIncr', false);
            }
            //如果为一次性备份 ,禁用增量和差异
            if (timeStrategy[i].type == 4) {
                setControlBtn('startIncr', false);
                setControlBtn('startDiff', false);
            }
        }
    }
    //设置按钮是否可用
    var setControlBtn = function (id, available) {
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

    //组织列表展开展示的恢复列表
    var showSrcData = function (showArr,hisGridFlag) {
        var organizationNum = 0;
        var userNum = 0;
        var groupNum = 0;
        var dirNum = 0;
        var emailNum = 0;
        var calenderNum = 0;
        var contactNum = 0;
        var taskNum = 0;
        var num = '';
        showArr.forEach(item => {
            if (!hisGridFlag) {
                num = parseInt(item.recovery_object_type);
            } else {
                num = parseInt(item);
            }
            switch (num) {
                case 10000:
                    organizationNum++;
                    break;
                case 1000:
                    userNum++;
                    break;
                case 1001:
                    groupNum++;
                    break;
                case 100:
                    dirNum++;
                    break;
                case 0:
                    emailNum++;
                    break;
                case 1:
                    calenderNum++;
                    break;
                case 2:
                    contactNum++;
                    break;
                case 3:
                    taskNum++;
                    break;
            }
        });
        var des = '';
        if (organizationNum != 0) {
            des += LANG.UI_MICROSOFT365_ORGANIZATION + organizationNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (userNum != 0) {
            des += LANG.UI_MICROSOFT365_USER + userNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (groupNum != 0) {
            des += LANG.UI_MICROSOFT365_USER_GROUP + groupNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (dirNum != 0) {
            des += LANG.UI_MICROSOFT365_DIR + dirNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (emailNum != 0) {
            des += LANG.UI_MICROSOFT365_EMAIL + emailNum +LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (calenderNum != 0) {
            des += LANG.UI_MICROSOFT365_CALENDAR + calenderNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (contactNum != 0) {
            des += LANG.UI_MICROSOFT365_CONTACTS + contactNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (taskNum != 0) {
            des += LANG.UI_MICROSOFT365_TASK + taskNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        return des;
    }

    var getExcludeDir = function (excludeDir) {
        var des = '';
        if (excludeDir == LANG.UI_PUBLIC_NOTHING) {
            return excludeDir;
        }
        excludeDir.forEach(item => {
            switch (parseInt(item)) {
                case 1:
                    des += LANG.UI_MICROSOFT365_INBOX + ';';
                    break;
                case 2:
                    des += LANG.UI_MICROSOFT365_DRAFT + ';';
                    break;
                case 3:
                    des += LANG.UI_MICROSOFT365_SENT_EMAIL + ';';
                    break;
                case 4:
                    des += LANG.UI_MICROSOFT365_DELETED_EMAIL + ';';
                    break;
                case 5:
                    des += LANG.UI_MICROSOFT365_SPAM + ';';
                    break;
                case 6:
                    des += LANG.UI_MICROSOFT365_FILE + ';';
                    break;
                case 7:
                    des += LANG.UI_MICROSOFT365_DIALOGUE_HISTORY + ';';
                    break;
                case 100:
                    des += LANG.UI_MICROSOFT365_CALENDAR + ';';
                    break;
                case 200:
                    des += LANG.UI_MICROSOFT365_CONTACTS + ';';
                    break;
                case 300:
                    des += LANG.UI_MICROSOFT365_TASK + ';';
                    break;
            }
        });
        return des;
    }

    //初始化主机列表表格
    var initExchangeGrid = function () {
        //表格option

        var jobs_uuid = $("#task_uuid").val();
        var dataNum = LANG.UI_MICROSOFT365_BACKUP_DATA_ITEMS;
        if (taskType == 2) {//恢复
            dataNum = LANG.UI_MICROSOFT365_RECOVERY_DATA_ITEMS;
        }
        var options = {
            detailFormatter: function (row, data, div) {
                var span = '';
                var des = '';
                if (taskType == 1) {//备份
                    for (var i = 0; i < data.src_data_list.length; i++) {
                        span += '<span style="white-space: nowrap;">' + data.src_data_list[i] + ';</span>' + '\n';
                    }
                    des += '<div class="col-md-4 detail-padding">' +
                        '<div style="overflow: auto;max-height: 150px;">'+ LANG.UI_MICROSOFT365_BACKUP_DATA_LIST + '：' + span + '</div>' +
                        ' </div>';
                    des += '<div class="col-md-4 detail-padding">' +
                        '<div>'+ LANG.UI_MICROSOFT365_EXCLUDE_DIR + '：' + getExcludeDir(data.exclude_dir) + '</div>' +
                        '</div>';
                } else {//恢复
                    span = showSrcData(data.recovery_object_info,false);
                    des += '<div class="col-md-4 detail-padding">' +
                        '<div style="overflow: auto;max-height: 150px;">'+ LANG.UI_MICROSOFT365_SOURCE_ORGANIZATION + '：' + data.organization_name + '</div>' +
                        '<div style="overflow: auto;max-height: 150px;">'+ LANG.UI_MICROSOFT365_TARGET_ORGANIZATION + '：' + data.des_organization_name + '</div>' +
                        ' </div>';
                    des += '<div class="col-md-4 detail-padding">' +
                        '<div style="white-space: nowrap;">'+ LANG.UI_MICROSOFT365_TARGET_USER + '：' + data.des_user_name + '</div>' +
                        '<div>' + LANG.UI_MICROSOFT365_RECOVERY_TYPE + '：'+ data.recovery_type + '</div>' +
                        ' </div>';
                    des += '<div class="col-md-4 detail-padding">' +
                        '<div>'+ LANG.UI_MICROSOFT365_RECOVERY_DATA_LIST + '：' + span + '</div>' +
                        '</div>';
                }

                return des;
            },
            searchInput: true,
            pagination: true,
            pageList: [5, 10, 25, 50],
            detailView: true,
            // pa:{},
            vin_url: "/api/v1/exchange/jobs/" + jobs_uuid + "/detail",
            vin_method: "GET",
            onRefresh: function () {
                $("#exchangetable").bootstrapTable('hideLoading');
                scroll = $("#exchangetable").bootstrapTable('getScrollPosition');
            },
            onPostBody: (data) => {
                if (null !== expandIndex) {
                    $("#exchangetable").bootstrapTable('expandRow', expandIndex);
                }
                $("#exchangetable").bootstrapTable('scrollTo', scroll);
            },
            onExpandRow: (index) => {
                if (null === expandIndex) {
                    expandIndex = index;
                } else if (index !== expandIndex) {
                    $("#exchangetable").bootstrapTable('collapseRow', expandIndex);
                    expandIndex = index;
                }
            },
            onCollapseRow: () => {
                expandIndex = null;
            },
            columns: [{
                checkbox: true,
                sortable: false,
            },
                {
                    field: 'organization_name',
                    title: LANG.UI_MICROSOFT365_ORGANIZATION_NAME,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'jobs_status',
                    title: LANG.UI_SEARCH_TASK_TYPE,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'user_num',
                    title: LANG.UI_MICROSOFT365_USER_NUM,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'completed_num',
                    title: LANG.UI_MICROSOFT365_COMPLETED_NUM,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'src_data_num',
                    title: dataNum,
                    sortable: false,
                    align: 'center',
            },
                {
                    field: 'status',
                    title:  LANG.UI_VISUAL_RESULT,
                    sortable: false,
                    align: 'center',
                    formatter: function (value, data, row) {
                        return '<span>' + getStatusDes(value) + '</span>';
                    }
            },
            ],
        }
        if (0 == $('#task_uuid').size()) {
            clearTimeout(timerTask.ExchangeJobDetails_exchangeGrid);
            return;
        }
        if (!initGridFlag) {
            $('#exchangetable').baseTableConfig().init(options);
            initGridFlag = true;
        } else {
            $('#exchangetable').bootstrapTable('refresh');
        }
        timerTask.ExchangeJobDetails_exchangeGrid = setTimeout(initExchangeGrid, 2000);
    }


    //初始化历史任务表格
    var initHistoryGrid = function () {
        var initFlag = false;
        //表格option
        var jobs_uuid = $("#task_uuid").val();
        var lastIndex = [-1, -1];
        var options = {
            detailFormatter: function (row, data, div) {
                if (row != lastIndex[1]) {//只展开一行
                    lastIndex.push(row);
                    $('#exchangeHistoryTable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
                    lastIndex.splice(0, 1);
                }
                Metronic.blockUI({
                    target: div,
                    animate: true
                });
                pAjaxRequest({}, '/api/v1/jobs/history/' + data.job_id + '', 'GET', function (res) {
                    $('.exchange-his-detail').html(getHisDetailsDes(res.data))
                    //下载跳过数据列表
                    $('.downloadPassData').click(function () {
                        var node_uuid = res.data.list.node_uuid;
                        var storage_uuid = res.data.list.storage_uuid;
                        var read_file_name = res.data.list.pass_item_path;
                        var pass_item_file_size =  res.data.list.pass_item_file_size;
                        if (pass_item_file_size == 0) {
                            UIToastr.showWarning(LANG.UI_MICROSOFT365_SKIP_FILE_DOWNLOAD,LANG.UI_MICROSOFT365_FILE_NOT_EXIST);
                            return;
                        }
                        window.location.href = '/api/v1/exchange/jobs/download' + '?node_uuid=' + node_uuid + '&read_file_name=' + read_file_name + '&storage_uuid=' + storage_uuid + '&pass_item_file_size=' + pass_item_file_size + '&x-api-version=1.0-rev0';
                    });
                    Metronic.unblockUI(div);
                });
                return '<div class="exchange-his-detail"></div>';
            },
            searchInput: true,
            pagination: true,
            pageList: [5, 10, 25, 50],
            sortName:'start_time',
            sortOrder:'desc',
            detailView: true,
            // pa:{},
            vin_url: '/api/v1/jobs/' + jobs_uuid + '/history',
            vin_method: "GET",
            columns: [{
                checkbox: true,
                sortable: false,
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
                    formatter: function (value, data, row) {
                        switch (data.job_status_value) {
                            case 0: //成功
                                return '<span class="label label-sm label-success">' + data.job_status + '</span>';
                            case 2: //中止
                            case 45:
                                return '<span class="label label-sm label-info">' + data.job_status + '</span>';
                            case 3: //异常
                            case 47:
                                return '<span class="label label-sm label-warning">' + data.job_status + '</span>';
                            case 1: //失败
                                return '<span class="label label-sm label-danger">' + data.job_status + '</span>';
                            default:
                                return '<span class="label label-sm label-danger">' + data.job_status + '</span>';
                        }
                    }
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

        var getHisDetailsDes = function (data) {
            var html = '';
            var pass_item_file_size = 0;
            var pass_list_display = 'display-none';
            if (data.list.pass_item_flag != 2) {
                pass_item_file_size = data.list.total_pass_item_count;
                pass_list_display = '';
            }
            if (data.list.resource_limiting_node_config) { // 如果开启了资源限制，只显示资源限制内容详情
                let thead = `<tr>
                            <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                            <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                            <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th>
                        </tr>`;

                let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
                let tbody =
                    `<tr>
                        <td>` + LANG.UI_PUBLIC_ON + `</td>
                        <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                        <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                    </tr>`;
                return `<table>${thead}${tbody}</table>`;

            } else {//未开启资源限制
                if (data.info.job_type == 1) { //备份
                    if (!data.list.backup_m365_object_info_list) {
                        return '--';
                    }
                    //排除目录
                    var exclude_folders = getExcludeDirDes(data.list.exclude_folders);
                    html += '<div class="col-md-3 detail-padding">' +
                        '<div>' + LANG.UI_MICROSOFT365_SOURCE_ORGANIZATION_NAME + data.list.organization_name + '</div>' +
                        '<div>' + LANG.UI_MICROSOFT365_BACKUP_DATA_NUM + data.list.current_complete_item_count + '</div>' +
                        '<div>'+ LANG.UI_MICROSOFT365_EXCLUDE_DIR_NEW + exclude_folders + '</div>' +
                        ' </div>';
                    html += '<div class="col-md-3 detail-padding">' +
                        '<div>'+ LANG.UI_MICROSOFT365_BACKUP_DATA_LIST +'：<textarea class="detail-textarea">' + data.list.backup_m365_object_info_list.join("\n") + '</textarea></div>' +
                        '</div>';
                    html += '<div class="col-md-4 detail-padding">' +
                        '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_NUM + pass_item_file_size + '</div>' +
                        '<div class="' + pass_list_display + '">' +
                        '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_LIST+ '<span class="downloadPassData exchange" href="#" name = "' +
                        data.list.pass_item_path + '" node_uuid = "' + data.list.node_uuid + '">' + LANG.UI_MICROSOFT365_VIEW_DETAILS + '</span></div></div>' +
                        '<div>'+ LANG.UI_PUBLIC_DESCRIPTION+'：' + data.list.error_code + '</div>' +
                        ' </div>';
                } else {//恢复
                    var recovert_list = showSrcData(data.list.recovery_m365_object_info_list, true);
                    var overwrite = data.list.overwrite == 0 ? LANG.UI_MICROSOFT365_NEW_RECOVERY : LANG.UI_MICROSOFT365_COVER_RECOVERY;
                    var destination_mail = data.list.destination_mail == "" ? LANG.UI_MICROSOFT365_NOT_SPECIFY_USER : data.list.destination_mail;
                    html += '<div class="col-md-3 detail-padding">' +
                        '<div>'+ LANG.UI_MICROSOFT365_BACKUP_TIMEPOINT + data.list.timepoint + '(' + data.list.backup_mode + ')' + '</div>' +
                        '<div>'+ LANG.UI_MICROSOFT365_SOURCE_ORGANIZATION_NAME_NEW + data.list.organization_name + '</div>' +
                        '<div>' + LANG.UI_MICROSOFT365_TARGET_ORGANIZATION + '：' + data.list.destination_organization_name + '</div>' +
                        ' </div>';
                    html += '<div class="col-md-2 detail-padding">' +
                        '<div>'+ LANG.UI_MICROSOFT365_RECOVERY_DATA_LIST +'：' + recovert_list + '</div>' +
                        '<div>' + LANG.UI_MICROSOFT365_RECOVERY_DATA_ITEMS+ '：' + data.list.current_complete_item_count + '</div>' +
                        '<div>' + LANG.UI_MICROSOFT365_RECOVERY_TYPE+'：' + overwrite + '</div>' +
                        '</div>';
                    html += '<div class="col-md-2 detail-padding">' +
                        '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_NUM + pass_item_file_size + '</div>' +
                        '<div class="' + pass_list_display + '">' +
                        '<div>'+ LANG.UI_MICROSOFT365_SKIP_DATA_LIST+'<span class="downloadPassData exchange" href="#" name = "' +
                        data.list.pass_item_path + '" node_uuid = "' + data.list.node_uuid + '">' + LANG.UI_MICROSOFT365_VIEW_DETAILS + '</span></div></div>' +
                        '<div>'+ LANG.UI_MICROSOFT365_SPECIFY_USER +'：' + destination_mail + '</div>' +
                        ' </div>';
                    html += '<div class="col-md-2 detail-padding">' +
                        '<div>'+ LANG.UI_PUBLIC_DESCRIPTION+'：' + data.list.error_code + '</div>' +
                        '</div>';
                }
            }
            return html;
        }

        var getExcludeDirDes = function (excludeDir) {
            if (!excludeDir || excludeDir.length == 0) {
                return LANG.UI_PUBLIC_NOTHING;
            }
            var des = '';
            excludeDir.forEach(item => {
                switch (parseInt(item)) {
                    case 1:
                        des += LANG.UI_MICROSOFT365_INBOX + ';';
                        break;
                    case 2:
                        des += LANG.UI_MICROSOFT365_DRAFT + ';';
                        break;
                    case 3:
                        des += LANG.UI_MICROSOFT365_SENT_EMAIL + ';';
                        break;
                    case 4:
                        des += LANG.UI_MICROSOFT365_DELETED_EMAIL + ';';
                        break;
                    case 5:
                        des += LANG.UI_MICROSOFT365_SPAM + ';';
                        break;
                    case 6:
                        des +=  LANG.UI_MICROSOFT365_FILE + ';';
                        break;
                    case 7:
                        des +=  LANG.UI_MICROSOFT365_DIALOGUE_HISTORY + ';';
                        break;
                    case 100:
                        des += LANG.UI_MICROSOFT365_CALENDAR + ';';
                        break;
                    case 200:
                        des += LANG.UI_MICROSOFT365_CONTACTS + ';';
                        break;
                    case 300:
                        des += LANG.UI_MICROSOFT365_TASK + ';';
                        break;
                };
            });
            return des;
        }

        var getLevelClass = function (level) {
            var levelClass = '';
            switch (level) {
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
        var setLevel = function (div, data) {
            if (!data) {
                return;
            }
            var labelClass = getLevelClass(data[8].level);
            var content = '<span class="label ' + labelClass + '">' +
                '<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="right" data-content="' +
                data[8].popover + '" >' + data[2] + '</a></span>';
            $(div).html(content);
        }
        var init = function () {
            if (!initFlag) {
                $('#exchangeHistoryTable').baseTableConfig().init(options);
                initFlag = true;
            } else {
                $('#exchangeHistoryTable').bootstrapTable('refresh');
            }
        }

        //添加详情信息
        var addHisDetails = function (nTr, data) {
            if (!data) {
                return;
            }
            var sOut = '<tr class="details"><td class="details" colspan="13">';
            sOut += '<table>';
            sOut += getHisDetails(data[9]);
            sOut += '</table></td></tr>';
            $(nTr).after(sOut);
        }
        var getHisDetails = function (data) {
            if (1 == data.taskType) {
                //文件备份
                return getBackupHisDetails(data.info);
            } else if (2 == data.taskType) {
                //文件恢复
                return getRecoveryHisDetails(data.info);
            }
        }

        //备份数据库详情
        var getBackupHisDetails = function (data) {
            var dataSet = [], passFile = [];
            var tbodyContent = "", details = "";
            if (data.agent_info_list.length != 0) {
                data.agent_info_list.forEach(item => {
                    fsnodeuuid = item.node_uuid;
                    //通配符
                    if (item.wildcard_list == "") {
                        wildcard = LANG.UI_PUBLIC_NOTHING;
                        wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                    } else {
                        var wildcard_list = JSON.parse(item.wildcard_list);
                        if (wildcard_list.wildcard_mode == 0) {
                            wildcard = LANG.UI_PUBLIC_NOTHING;
                            wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                        } else {
                            var wildcard_mode, wildcard = [];
                            if (wildcard_list.wildcard_mode != null) {
                                wildcard = wildcard_list.wildcard.join('<br>');
                                wildcard_list.wildcard_mode == 1 ? wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_FILTER : wildcard_mode = LANG.UI_FILE_WILDCARD_BAK_SELECT;
                            } else {
                                wildcard = LANG.UI_PUBLIC_NOTHING;
                                wildcard_mode = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
                            }
                        }
                    }
                    dataSet.push([item.src_agent_ip + '(' + item.src_agent_name + ')',
                        item.total_pass_number,
                        filterSize(item.total_size),
                        filterSize(item.current_size),
                        filterSize(item.write_size),//去掉显示
                        wildcard_mode,
                        wildcard,
                        item.passfile_file_path,
                        item.total_pass_dir_number,
                        item.description,
                        item.backup_mode,
                    ]);
                });
                dataSet.forEach(item => {
                    var elecontent = '', style = '';
                    if (item[1] != 0) {
                        elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
                    } else {
                        elecontent = LANG.UI_PUBLIC_NOTHING;
                        style = 'style="color: black;pointer-events:none;"'
                    }
                    tbodyContent += '<tr>' +
                        '<td style="padding-top: 25px;">' + item[0] + '</td>' +
                        '<td style="padding-top: 25px;">' + item[11] + '</td>' +
                        '<td style="padding-top: 25px;">' + item[5] + '</td>' +
                        '<td style="padding-top: 25px;">' + item[6] + '</td>' +
                        // '<td style="padding-top: 25px;">'+ item[10]+'</td>'+
                        '<td style="padding-top: 25px;">' + item[1] + '</td>' +
                        '<td style="padding-top: 25px;">' + item[8] + '</td>' +
                        '<td style="padding-top: 25px;">' + '<a class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + item[7] + '</span></a>' + '</td>' + //跳过文件下载
                        '<td style="padding-top: 25px;">' + item[2] + '</td>' +
                        '<td style="padding-top: 25px;">' + item[3] + '</td>' +
                        '<td style="padding-top: 25px;">' + item[9] + '</td>' +
                        // '<td><textarea style="outline: none;" cols="50" rows="3">'+item[2]+'</textarea></td>'+
                        '</tr>';
                });
                var details = '<div class="table-container" style="max-height: 250px;overflow: auto;"><table style="width: 100%;" class="table" id="nasHistoryDetailtable"><thead><tr role="row"class="heading"><th width="15%">' + LANG.UI_NAS_BAK_DETAIL_HIS + '</th><th width="8%">' + LANG.UI_SEARCH_TASK_TYPE + '</th><th style="width: 10%;">' + LANG.UI_FILE_COUNT_WILDCARD_MODE + '</th><th style="width: 10%;">' + LANG.UI_FILE_WILDCARD + '</th>' +
                    // '<th style="width: 5%;">'+LANG.UI_ARCHIVE+'</th>'+
                    '<th width="10%">' + LANG.UI_FILE_COUNT_PASS + '</th><th width="10%">' + LANG.UI_FILE_COUNT_PASS_DIR + '</th><th width="10%">' + LANG.UI_FILE_PASSFILE_LISTS + '</th><th width="7%">'
                    + LANG.UI_OS_PLUG_TOTAL_SIZE + '</th><th width="10%">' + LANG.UI_FILE_PROCESSED_CAPACITY + '</th><th width="5%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th></tr></thead><tbody>' + tbodyContent + '</tbody></table></div>';
            } else {
                return LANG.UI_PUBLIC_NOTHING;
            }
            return details;
        }

        //转换文件大小
        const filterSize = (size) => {
            if (!size) {
                return '';
            }
            return size < 1024 ? size + ' B' :
                size < pow1024(2) ? (size / 1024).toFixed(2) + ' KB' :
                    size < pow1024(3) ? (size / pow1024(2)).toFixed(2) + ' MB' :
                        size < pow1024(4) ? (size / pow1024(3)).toFixed(2) + ' GB' :
                            (size / pow1024(4)).toFixed(2) + ' TB'
        }

        // 求次幂
        function pow1024(num)
        {
            return Math.pow(1024, num)
        }

        //恢复数据库详情
        var getRecoveryHisDetails = function (data) {
            var tbodyContent = "", details = "", elecontent = '', style = '';
            if (data.agent_info_list[0].passfile_exist_flag == 1) {
                elecontent = LANG.UI_FILE_PASSFILE_DETAILS;
            } else {
                elecontent = LANG.UI_PUBLIC_NOTHING;
                style = 'style="color: black;pointer-events:none;"'
            }
            passfile_file_path = data.agent_info_list[0].passfile_file_path;
            fsnodeuuid = data.agent_info_list[0].node_uuid;
            tbodyContent += '<tr>' +
                '<td style="padding-top: 25px;">' + data.agent_info_list[0].src_agent_ip + '(' + data.agent_info_list[0].src_agent_name + ')' + '</td>' +
                '<td style="padding-top: 25px;">' + data.des_agent_ip + '(' + data.des_agent_name + ')' + '</td>' +
                '<td style="padding-top: 25px;"><textarea style="outline: none;" cols="40" rows="2">' + data.file_list.join('\n') + '</textarea></td>' +
                '<td style="padding-top: 25px;">' + data.file_count + '</td>' +
                '<td style="padding-top: 25px;">' + data.agent_info_list[0].total_pass_number + '</td>' +
                '<td style="padding-top: 25px;">' + data.agent_info_list[0].total_pass_dir_number + '</td>' +
                '<td style="padding-top: 25px;"><a class="downloadPassFile" ' + style + '>' + elecontent + '<span class="display-none">' + passfile_file_path + '</span></a></td>' +
                '</tr>';
            details = '<div class="table-container" style="max-height: 250px;overflow: auto;"><table style="width: 100%;" class="table" id="nasRecoveryDetailtable"><thead><tr role="row"class="heading"><th width="15%">' + LANG.UI_FILE_SOURCE_CLIENT_IP + '</th><th width="15%">' + LANG.UI_FILE_DES_CLIENT_IP + '</th><th width="15%">' + LANG.UI_FILE_RECOVER_PATH + '</th><th width="10%">' + LANG.UI_FILE_COUNT_TOTAL + '</th><th width="10%">' + LANG.UI_FILE_COUNT_PASS + '</th><th width="10%">' + LANG.UI_FILE_COUNT_PASS_DIR + '</th>  <th width="15%">' + LANG.UI_FILE_PASSFILE_LISTS + '</th></tr></thead><tbody>' + tbodyContent + '</tbody></table></div>';

            return details;
        }


        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            e.target // newly activated tab
            e.relatedTarget // previous active tab
            if ("#exchangeHistory" == e.target.hash) {
                init();
            }
        })
    }
    var watchEchartSizeChange = function () {
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

    return {
        //main function to initiate the module
        init: function () {
            // initSwiper();
            initBasicInfo();
            initSpeed();
            initLogGrid();
            initHistoryGrid();
            initExchangeGrid();
            watchEchartSizeChange();
        }
    };
}();

jQuery(document).ready(function () {
    ExchangeJobDetails.init();
});