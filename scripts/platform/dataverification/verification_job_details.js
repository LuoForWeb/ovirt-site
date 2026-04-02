var VerifyJobDetails = function () {

	let objectGrid, historyGrid;
	let _scrollHeight = 0; //任务日志全局高度
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	let detailsIndex = 0,detailsInfo = null;

	let vmSelect = []; //虚拟机选择

	let _taskStatus; //监控任务状态

	let jobParams = {}; //操作需要参数

	let runvmPie;
	let verifyType, verifyMode;
	let _reportContent, _selectHistory;
	let initObjectGridFlag,changeHeightFlag,initObjectFlag;
	let queryParams = {};
	const _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");


	// 调整表格高度大小
	const ROW_HIGH_PX = '15.25px';
	const ROW_LOW_PX = '4.25px';
	let user_uuid = '';

	//初始化基本信息
	let initBasicInfo = function(){

		let updateInterval = 5000;
		let init = function(){

			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
        		return;
        	}
			let taskuuid = $("#task_uuid").val();
			pAjaxRequest({}, "/api/v1/verification/job/"+taskuuid, "GET", function (res){
					setBasicInfo(res.data, timerTask.VMJobDetails_taskRunningInfo);
			});
			timerTask.VMJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	//设置基本信息
	let setBasicInfo = function(data, timeoutID){
		if(data.info && data.info.length == 0){
			clearTimeout(timeoutID);
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function(){
				LOCATION('./content/platform/jobs/jobs.php','task');
			}, 5000);
		}

		//基本信息
		user_uuid = data.user_uuid;
		$('#taskName').html(data.basic_info.task_name);
		$('#taskType').html(data.basic_info.task_type_des);
		if(data.basic_info.task_status){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.basic_info.task_status) + '" >' + data.basic_info.task_status_des + '</span>');
		}

		//保存获取到的任务参数
		_taskStatus = data.basic_info.task_status;
		jobParams.status = data.basic_info.task_status;
		jobParams.taskType = data.basic_info.task_type;
		jobParams.subModule = 0;

		//概要
		$('#startTime').html(data.basic_info.start_time);
		$('#intervalTime').html(data.basic_info.run_time);
		$('#taskStage').html(data.basic_info.taskStage);
		//策略
		$('#createTime').html(data.basic_info.create_time);
		//下次运行时间
		let nextTime =  data.time_strategy.next_time;
		if(data.time_strategy.type == 1){
			//立即验证不显示下次运行时间
			nextTime = '--';
		}
		$('#nextTime').html(nextTime);
		let timeStrategy = getTimeStrategy(data.time_strategy.info);
		$('#timeStrategy').html(timeStrategy.verify);

		//验证方式
		$('#verifyMode').html(data.basic_info.verify_mode_des);
		//验证类型
		$('#verifyType').html(data.basic_info.verify_type_des);
		//同期启动虚拟机个数
		$('#deal_vm_num').html(data.high_strategy.limit_boot_vm_num);
		//NFS服务IP
		$('#serverIP').html(data.high_strategy.nfs_server_ip);
		//选择第三方虚拟化支持存储挂载IP地址
		if (data.high_strategy.hypervisor_type != 108){
			$('.mountDiv').show();
		}else{
			$('.mountDiv').hide();
		}
		//备份服务器IP
		$('#backupServerIP').html(data.high_strategy.backup_server_ip);
		// 过载保护
		$('#ignore_resource_limit').html(getFlagLevelInfo(data.high_strategy.ignore_resource_limiting_flag));
		//虚拟实验室
		$('#labname').html(data.high_strategy.virtual_lab_name);
		//应用组
		$('#appgroupname').html(data.high_strategy.appgroup_name);

		verifyType = data.basic_info.automatic_verifitied_flag;
		verifyMode = data.basic_info.verify_mode;
		if(verifyMode == 2){
			$('.labshowDiv').show();
			$('.verifyTypeDiv').show();
		}else{
			$('.labshowDiv').hide();
			$('.verifyTypeDiv').hide();
		}

		if(verifyType == 1){
			//手动验证没有告警配置
			$('.alarmDiv').hide();
			$('.nextTimeDiv').hide();//下次运行时间
			$('.threadNumDiv').hide();
		}else{
			$('.alarmDiv').show();
			$('.nextTimeDiv').show();//下次运行时间
			$('.threadNumDiv').show();
		}

		//初始化对象列表
		if(!initObjectGridFlag){
			initObjectGrid();
		}else{
			queryParams.search = $('.objectSearch').val();
			$('#objectTable').bootstrapTable('refresh',{query:queryParams});
		}

		//初始化操作按钮
		setBtnStatus(data);


	}



	//得到开关的结果描述   开启/关闭
	let getSwitchDes = function(check){
		if(check == 1){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

	//根据任务状态设置按钮权限
    let setBtnStatus = function(data){
    	setControlBtn('startJobStra', true);
		setControlBtn('startJob', true);
		setControlBtn('stopJob', true);
    	switch(_taskStatus){
	    	case 2:
	    	case 5:
	    	case 10:
	    		//运行和准备中停止中,禁用运行
	    		setControlBtn('startJobStra', false);
	    		setControlBtn('startJob', false);
	    		if (CONF.TASK_STATUS.STOPPING == _taskStatus) {
					//停止中状态，变为强制停止
					$('#stopJob').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
				}
	    		break;
	    	case 4:
	    		//停止,禁用停止
				$('#stopJob').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a>');
	    		setControlBtn('stopJob', false);
	    		break;
    		default:
    			//其他状态,开启控制
    			setControlBtn('stopJob', true);
    			break;
    	}
    }

	//设置按钮是否可用
	let setControlBtn = function(id, available){
    	if(available){
    		$("#" + id).find('a').removeClass('disablebtn');
    	}else{
    		$("#" + id).find('a').addClass('disablebtn');
    	}
    }


	//初始化隔离网络显示
	let initLabNetwork = function(list){
		let des = '';
		des += '<thead><tr style="border-bottom: 0px"><th width="50%">'+LANG.UI_DRILLS_PRODUCT_NETWORK+'</th><th width="50%">'+LANG.UI_DRILLS_ISOLATED_NETWORK+'</th></tr></thead><tbody>';
		for(let i =0;i<list.length; i++){
			let productInfo = list[i].productInfo;
			let isolatedInfo = list[i].isolatedInfo;
			let productDes = LANG.UI_DRILLS_PRODUCT_NETWORK + ": " + productInfo.name + "<br>" + LANG.UI_DRILLS_NETMASK +
			": " + productInfo.netmask + "<br>" + LANG.UI_DRILLS_GATEWAY +
			": " + productInfo.gateway;
			let isolatedDes = LANG.UI_DRILLS_ISOLATED_NETWORK + ": " + isolatedInfo.name + "<br>" + LANG.UI_DRILLS_NETMASK +
			": " + isolatedInfo.netmask + "<br>" + LANG.UI_DRILLS_GATEWAY +
			": " + isolatedInfo.gateway;

			des += '<tr style="color: #818C96"><td>' + productDes + '</td><td>' + isolatedDes +"</td></tr>";
		}

		des +='</tbody>';

		$('#networkList').html(des);

	}

	//得到时间策略描述信息
	var getTimeStrategy = function(msg){
		var timeInfo = {verify:LANG.UI_PUBLIC_NOTHING};
		if(!msg){
			return timeInfo;
		}
		var strategy =msg;
		var des = "";
		if(CONF.STRATEGY_TYPE.DAY == strategy.strategy_type){
			des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
		}else if(CONF.STRATEGY_TYPE.WEEK == strategy.strategy_type){
			des += getStrategyFrequency(strategy);
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
			}else{
				des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
			}
		}else if(CONF.STRATEGY_TYPE.MONTH == strategy.strategy_type){
			des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
		}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.strategy_type){
			des += strategy.start_time;
		}else{
			des += LANG.UI_PUBLIC_NOTHING;
		}
		timeInfo.verify = des;
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
		desEach += strategy.start_time;
		//如果是英文版 需要加空格
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			desEach += " "; //策略开始时间
		}
		desEach += LANG.UI_STRATEGY_START;
		desEach += "<br>";
		return desEach;
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


    //初始化运行日志
    let initLogGrid = function(){
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
    }

	let initReportDetails = function(row){
		JobReportDetail.init({'uuid': $('#task_uuid').val(), 'agent_uuid': row.object_uuid, 'pre': 2})
	}

    //初始化对象列表
    let initObjectGrid = function(){

		let operates = {
			'click .objectDetails':function (event, value, row, index){
				initObjectDetails(row);
			}
		};

		let operates2 = {
			'click .reportDetails':function (event, value, row, index){
				initReportDetails(row);
			}
		};
		//根据创建任务选择展示对应验证功能
		let colums = [];
		colums.push(
		{
			checkbox: true,
				sortable: false, //默认可排序，禁用排序才写此项
			formatter: function (value, row, index, field) {
				if (row.checked === false) {
					return {
						disabled: true
					};
				}
			}
		},
		{
			field: 'ori_name',
				title: LANG.UI_VERIFY_OBJECT_NAME,
			formatter: function (value, row, index, field) {
				return '<span title="' + row.object_name + '">' + row.object_name + '</span>';
			}
		},
		{
			field: 'module_type',
				title: LANG.UI_SEARCH_MODE_TYPE,
			formatter: function (value, row, index, field) {
				return row.module_type_des;
			}
		},
		{
			field: 'item_status',
				title: LANG.UI_PUBLIC_STATUS,
				formatter: function (value, row, index, field) {
					if (_taskStatus == 2 && row.status == 2){
						let labelClass = setStatusDes(row.status);
						return '<span class="label label-sm status-icon ' + labelClass + '">' + row.status_des + '</span>';
					}else{
						return '--';
					}
			}
		},
		{
			field: 'current_timepoint',
			title: LANG.UI_SUREBACKUP_AFTER_BACKUP_TIMEPOINT_RANGE_CURRENT,
		});

		if(verifyMode == 1 || verifyType == 1){
			if ($('#oem_version').val() != CONF.VENDOR_LIST.gmp){
				colums.push({
						field: 'integrity_check_status',
						title: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK,
						formatter: function (value, row, index, field) {
							if (_taskStatus == 2 && row.status == 2){
								let labelClass = setStatusDes(row.integrity_check_flag);
								return '<span class="label label-sm status-icon ' + labelClass + '">' + row.integrity_check_flag_des + '</span>';
							}else{
								return '--';
							}
						}
					},
					{
						field: 'vir_det_kill_status',
						title: LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS,
						formatter: function (value, row, index, field) {
							if (_taskStatus == 2 && row.status == 2){
								let labelClass = setStatusDes(row.vir_det_kill_flag);
								return '<span class="label label-sm status-icon ' + labelClass + '">' + row.vir_det_kill_flag_des + '</span>';
							}else{
								return '--';
							}
						}
					});
			}
		}else{
			if ($('#oem_version').val() != CONF.VENDOR_LIST.gmp){
				colums.push({
						field: 'integrity_check_status',
						title: LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK,
						formatter: function (value, row, index, field) {
							if (_taskStatus == 2 && row.status == 2){
								let labelClass = setStatusDes(row.integrity_check_flag);
								return '<span class="label label-sm status-icon ' + labelClass + '">' + row.integrity_check_flag_des + '</span>';
							}else{
								return '--';
							}
						}
					},
					{
						field: 'vir_det_kill_status',
						title: LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS,
						formatter: function (value, row, index, field) {
							if (_taskStatus == 2 && row.status == 2){
								let labelClass = setStatusDes(row.vir_det_kill_flag);
								return '<span class="label label-sm status-icon ' + labelClass + '">' + row.vir_det_kill_flag_des + '</span>';
							}else{
								return '--';
							}
						}
					},
					{
						field: 'ping_status',
						title: LANG.UI_VERIFY_PING_TEST,
						formatter: function (value, row, index, field) {
							if (_taskStatus == 2 && row.status == 2){
								let labelClass = setStatusDes(row.ping_flag);
								return '<span class="label label-sm status-icon ' + labelClass + '">' + row.ping_flag_des + '</span>';
							}else{
								return '--';
							}
						}
					},
					{
						field: 'heartbeat_status',
						title: LANG.UI_VERIFY_HEARTBEAT,
						formatter: function (value, row, index, field) {
							if (_taskStatus == 2 && row.status == 2){
								let labelClass = setStatusDes(row.heartbeat_flag);
								return '<span class="label label-sm status-icon ' + labelClass + '">' + row.heartbeat_flag_des + '</span>';
							}else{
								return '--';
							}
						}
					});
			}
			colums.push(
				{
					field: 'screen_status',
					title: LANG.UI_VERIFY_SCREEN,
					formatter: function (value, row, index, field) {
						if (_taskStatus == 2 && row.status == 2){
							let labelClass = setStatusDes(row.screen_flag);
							return '<span class="label label-sm status-icon ' + labelClass + '">' + row.screen_flag_des + '</span>';
						}else{
							return '--';
						}
					}
				});
		}

		//GMP增加文件对比分析
		if($('#oem_version').val() == CONF.VENDOR_LIST.gmp){
			colums.push({
				title: LANG.UI_FILE_COPY_COMPARE,
				sortable: false, //默认可排序，禁用排序才写此项
				formatter:function (value, row, index, field){

					let nameStr = '<a class="reportDetails" id="report' + row.obejct_uuid + '">' + LANG.UI_ALARM_DETAILS + '</a>';
					return nameStr;
				},
				events:operates2,
			});
		}

		colums.push(
			{
				field: 'max_boot_time',
				title: LANG.UI_VERIFY_WAIT_TIME,
				formatter: function (value, row, index, field) {
					return row.max_boot_time;
				}
			},
			{
				title: LANG.UI_ALARM_DETAILS,
				sortable: false, //默认可排序，禁用排序才写此项
				formatter:function (value, row, index, field){
					let nameStr = '<a class="objectDetails" id="' + row.object_uuid + '">' + LANG.UI_ALARM_DETAILS + '</a>';
					return nameStr;
				},
				events:operates,
			}
		);

		let beforeInput = "";
		let afterInput = `<button type="button" class="btn table-toolbar-btn" id="verify" aria-haspopup="true" aria-expanded="false">
                                        <i class="viconfont vicon-Frame-15 mr4"></i>
                                        <span>${LANG.UI_JOB_START_VERIF}</span>
                                    </button>`;

		//操作事件回调函数
		let options = {
			vin_url: "/api/v1/verification/job/"+$('#task_uuid').val()+"/object_list",
			vin_method: "GET",
			pagination: true, //分页
			resizable: true, //可变宽度
			toolbarId: '#object_detail_toolbar',
			buttonsToolbar: '#object_detail_toolbar .vin_btnToolbar',
			placeholder: LANG.UI_VERIFY_SEARCH_BY_OBJECT_NAME, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'objectSearch', //自定义的搜索框类名
			searchSelector: '.objectSearch', //选择使用自定义搜索框
			customTool: {
				beforeInput: beforeInput,
				afterInput: afterInput,
			},
			onPostBody: function(data){
				// 初始化验证示意图
				initObjectList(data);
			},
			onRefresh: function () {
				$("#objectTable").bootstrapTable('hideLoading');
			},
			columns: colums
		}

		if (!initObjectGridFlag) {
			$('#objectTable').baseTableConfig().init(options);
			// 点击搜索图标搜索逻辑
			$('.search-btn').on('click', function () {
				queryParams.search = $('.objectSearch ').val();
				$('#objectTable').bootstrapTable('refresh', {
					query: queryParams
				});
			})
			//开始验证回调函数初始化
			$('#verify').on('click', function(){
				//运行中不允许再次启动
				if($.inArray(_taskStatus,[2,5,10]) != -1){
					return UIToastr.showInfo(LANG.UI_VERIFY_START_JOB, LANG.UI_VERIFY_START_JOB_IS_RUNNING_TIPS);
				}
				//获取选中项
				let select = $('#objectTable').bootstrapTable('getSelections');
				if(select.length == 0){
					return UIToastr.showInfo(LANG.UI_VERIFY_START_JOB, LANG.UI_VERIFY_START_JOB_NOT_SELECT_TIPS);
				}
				checkOperateAuth({ type: 1, user_uuid: user_uuid, auth: 'data_manager' }, function(){
					let params = {};
					params.item_uuids = [];
					for(let i=0;i<select.length;i++){
						params.item_uuids.push(select[i].object_uuid);
					}
					params.task_uuid = $("#task_uuid").val();
					params.backup_mode =  1;
					Metronic.blockUI({target: '#objectTable',animate: true});
					pAjaxRequest(params, "/api/v1/verification/jobs/start_select", "POST", function (d) {
						Metronic.unblockUI('#objectTable');
						let op = LANG.UI_VERIFY_START_JOB;
						if (operateResponseList(d, op)) {
							$("#objectTable").bootstrapTable('refresh');
							$("#objectTable").bootstrapTable('hideLoading');
						}
					}, false);
				});

			});
			initObjectGridFlag = true;
		}



    }

	//初始化对象详情
	let initObjectDetails = function(row){
		let params = {};
		params.task_uuid = $('#task_uuid').val();
		params.object_uuid = row.object_uuid;
		//非整机模块，屏蔽主机配置显示
		if(verifyMode == 2){
			$('.hostSetDiv').show();
		}else{
			$('.hostSetDiv').hide();
		}
		//整机的备份数据验证及安全扫描需要显示操作系统类型
		if($.inArray(row.module_type, [3,4,11,14]) == -1 && verifyMode != 4){
			$('.osTypeDiv').show();
		}
		pAjaxRequest(params, '/api/v1/verification/object_info', 'GET', (result) => {
			if (result.success){
				let general_config = result.data.general_config;
				let network_config = result.data.network_config;
				let verify_config = result.data.verify_config;
				$('#objectname').html(result.data.object_name);
				//基本信息
				
				$('#timepoint').html(verify_config.timepoint_des);
				if(verify_config.timepoint_range == 3){
					let des = "";
					for (let i=0;i<verify_config.timepoint_list.length;i++){
						des += verify_config.timepoint_list[i] + "<br>";
					}
					$('#timepointInfo').html(des);
					$('.timepointDiv').show();
				}else{
					$('.timepointDiv').hide();
				}

				//仅整机支持病毒查杀、通用配置、驱动检测
				if($.inArray(row.module_type, [3,4,11,14,28]) != -1){
					$('.virusDiv').hide();
				}else{
					//病毒检测
					$('.virusDiv').show();
					let virusCheckFlag = verify_config.vir_det_kill_flag;
					$(`#virus_check_flag`).html(getFlagLevelInfo(virusCheckFlag));
					if (virusCheckFlag) {
						let virusInfo = {
							virus_scan_flag: virusCheckFlag,
							virus_scan_config_list: verify_config.virus_scan_config_list
						}
						$(`#virus_check_flag`).html($.fn.getVirusConfigDes(virusInfo, 'apply'));
					}
				}
				

				//主机配置
				var cpuModeDes = [LANG.UI_PUBLIC_DEFAULT, "custom","host-passthrough", "host-model", 'EPYC', 'CORTEX'];
				$('#cpuMode').html(cpuModeDes[general_config.cpu_mode]);
				$('#cpuNum').html(general_config.cpu_num);
				$('#coreNum').html(general_config.core_num);
				$('#memorySize').html(general_config.memory_size + "GB");
				$('#osType').html(general_config.os_type);
				$('#osVersion').html(general_config.os_version_name);
				$('#disk_target_bus').html(general_config.disk_target_bus);
				$('#netcard_target_bus').html(general_config.netcard_target_bus);
				$('#max_ping_wait_time').html(verify_config.max_ping_wait_time);
				//网络信息
				if(!network_config){
					$('#networkInfo').html('--');
				}else{
					let options = {
						data: network_config.netcard_list,
						pagination: false, //分页
						columns: [
							// {
							// 	field: '',
							// 	title: "网卡名",
							// 	formatter: function (value, row, index, field) {
							// 		return row.network_name;
							// 	}
							// },
							{
								field: '',
								title: LANG.UI_PUBLIC_IP_ADDRESS,
								formatter: function (value, row, index, field) {
									return row.ip_address;
								}
							},
							{
								field: '',
								title: LANG.UI_PUBLIC_IP_GATEWAY,
								formatter: function (value, row, index, field) {
									return row.gateway;
								}
							},
							{
								field: '',
								title: LANG.UI_PUBLIC_IP_NETMASK,
								formatter: function (value, row, index, field) {
									return row.netmask;
								}
							},
						],
					}
					$('#networkTable').bootstrapTable('destroy');
					$('#networkTable').baseTableConfig().init(options);
				}
				$('#object_detail_drawer').drawer('show');
			}
		});
	}

	//初始化状态值显示
	const setStatusDes = function(status){
		let labelClass = "label-info";
		switch(status){
			case 0:	//未知
				labelClass = "label-default";
				break;
			case 1:	//等待
				labelClass = "label-info";
				break;
			case 2:	//运行
				labelClass = "label-success";
				break;
			case 3:	//跳过
				labelClass = "label-default";
				break;
			case 4:	//错误
				labelClass = "label-danger";
				break;
			case 5:	//成功
				labelClass = "label-success";
				break;
			case 6:	//完成
				labelClass = "label-success";
				break;
			case 7:	//异常
				labelClass = "label-warning";
				break;
		}
		return labelClass;
	}



	//获取当前验证功能
	let getVerifyStepDes = function(info){
		let des = '--';
		if(info.ping_flag == 2){
			//网络验证
			des = LANG.UI_VERIFY_PING_TEST;
		}else if(info.heartbeat_flag == 2){
			//心跳验证
			des = LANG.UI_VERIFY_HEARTBEAT;
		}else if(info.screen_flag == 2){
			//截屏验证
			des = LANG.UI_VERIFY_SCREEN;
		}else if(info.integrity_check_flag == 2){
			//完整性校验
			des = LANG.UI_VM_INTEGRITY_VERIFY;
		}else if(info.vir_det_kill_flag == 2){
			//病毒扫描
			des = LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS;
		}
		return des;
	}

	//初始化状态值显示
	let getVerifyClass = function(status){
		let labelClass = "verify-info";
		switch(status){
			case 0: //未验证
				labelClass = "verify-default";
				break;
			case 1:	//等待
				labelClass = "verify-info";
				break;
			case 2:	//运行中
				labelClass = "verify-success";
				break;
			case 3:	//跳过
				labelClass = "verify-default";
				break;
			case 4:	//错误
				labelClass = "verify-danger";
				break;
			case 5:	//成功
				labelClass = "verify-success";
				break;
			case 6:	//完成
				labelClass = "verify-success";
				break;
			case 7:	//警告
				labelClass = "verify-warning";
				break;
		}
		return labelClass;
	}

	//获取验证步骤进度图信息
	let getVerifyStepInfo = function(info){
		let des = '';
		if(verifyMode == 1 || verifyType == 1){
			//手动验证 或者 备份数据验证及安全扫描
			des += `<li title="`+LANG.UI_VM_INTEGRITY_VERIFY+`" class="oblique-right  integrity_check_flag `+ getVerifyClass(info.integrity_check_flag)+`" ></li>
					<li title="`+LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS+`" class="oblique-left vir_det_kill_flag `+getVerifyClass(info.vir_det_kill_flag)+`" ></li>`;
		}else{
			//完全可恢复性验证
			des += `<li title="`+LANG.UI_VM_INTEGRITY_VERIFY+`" class=" oblique-right integrity_check_flag `+ getVerifyClass(info.integrity_check_flag)+`" ></li>
					<li title="`+LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS+`" class="oblique-all vir_det_kill_flag `+getVerifyClass(info.vir_det_kill_flag)+`" ></li>
					<li title="`+LANG.UI_VERIFY_PING_TEST+`" class="oblique-all ping_flag `+getVerifyClass(info.ping_flag)+`" ></li>
					<li title="`+LANG.UI_VERIFY_HEARTBEAT+`" class="oblique-all heartbeat_flag `+getVerifyClass(info.heartbeat_flag)+`" ></li>
					<li title="`+LANG.UI_VERIFY_SCREEN+`" class="oblique-left screen_flag `+getVerifyClass(info.screen_flag)+`" ></li>
					`;
		}
		return des;
	}

	//替换特殊字符
	let clearString = function (s){
		let rs = "";
		for (let i = 0; i < s.length; i++) {
			rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
		}
		return rs;
	}

	//初始化验证对象示意图显示
	let initObjectList = function(objects){
		if (objects.length == 0) return;
		$('#objectList').empty();
		let displayHide = "";
		if($('#oem_version').val() == CONF.VENDOR_LIST.gmp){
			displayHide = "display-hide";
		}
		for (let i=0;i<objects.length;i++){
			let labelClass = setStatusDes(objects[i].status);
			let liID = clearString(objects[i].new_uuid);
			let statusDes = '<span class="label label-sm status-icon ' + labelClass + '">' + objects[i].status_des + '</span>';
			if(!initObjectFlag) {
				let info = '';
				let vm = '<span class="objectname">'+objects[i].object_name+'</span>';
				if (objects[i].console_url != '') {
					let new_uuid = objects[i].new_uuid;
					vm = '<a href="javascript:void(0)" data-uuid="'+objects[i].new_uuid+'" data-url="'+objects[i].console_url +'" class="jumpVm"><i class="viconfont vicon-web-console fs18 clickicon"></i><span class="font-blue">' + objects[i].object_name + '</span></a>';
				}
				info += `<li id="object_`+liID+`">
					<div class="inline-block objectImg">
						<img src="./img/verifyobject.png">

					</div>
					<div class="inline-block">
						<p>
							<span>`+LANG.UI_VERIFY_OBJECT+`：</span>
							`+vm+`
							<span class="status">`+statusDes+`</span>
						</p>
						<p>
							<span>`+LANG.UI_VERIFY_CURRENT+`：</span>
							<img src="">
								<span class="verifyFun">`+getVerifyStepDes(objects[i])+`</span>
								<span class="verifytime"></span>
						</p>
						<ul class="verifyStep `+displayHide+`">
							`+ getVerifyStepInfo(objects[i]) +`
						</ul>
					</div>
				</li>`;

				$('#objectList').append(info);
			}else{
				$('#object_'+liID + ' status').html(statusDes);
				$('#object_'+liID + ' verifyFun').html(getVerifyStepDes(objects[i]));
				$('#object_'+liID + ' verifyStep').html( getVerifyStepInfo(objects[i]));

			}
			// 点击容灾演练主机进行跳转
			$('#object_'+liID + ' .jumpVm').on('click', function (){
				var vmUuid = $(this).attr('data-uuid');
				var url = $(this).attr('data-url');
				Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
				pAjaxRequest({type:'look'}, "/api/v1/virtual/operate/"+vmUuid, "POST", function (result) {
					Metronic.unblockUI('#jobDetail');
					if (result.code == 0) {
						window.open(url, '_blank');
						return;
					} else {
						UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
					}
				});
			});
		}

	}


	var escapeJquery = function(srcString){
        // 转义之后的结果
        var escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
                "]", "|", "{", "}"];
        // jquery中的特殊字符,不是正则表达式中的特殊字符
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
                ":", ";", "<", ">", ",", "/"];
        for (var i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\"
                                    + jsSpecialChars[i], "g"), "\\"
                            + jsSpecialChars[i]);
        }
        for (var i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                            "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }

    //初始化历史任务表格
    var initHistoryGrid = function(){
		let initFlag = false;
		let task_uuid = $("#task_uuid").val();
		let operates = {
			'click .historyDetails':function (event, value, row, index){
				initHistoryDetails(row);
			}
		};
		let columns = [{
			field: 'job_type',
			title: LANG.UI_PUBLIC_TASK_TYPE,
			formatter: function (value, row, index, field) {
				return row.job_type;
			}
		},
			{
				field: 'job_status_value',
				title: LANG.UI_PUBLIC_STATUS,
				sortable: true,
				align: 'center',
				formatter: function (value, row, index, field) {
					if (0 == row.job_status_value) {
						return '<span class="label label-sm label-success status-icon">' + row.job_status + '</span>';
					} else if (45 == row.job_status_value) {
						return '<span class="label label-sm label-info status-icon">' + row.job_status + '</span>';
					} else {
						return '<span class="label label-sm label-danger status-icon">' + row.job_status + '</span>';
					}
				}
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
			}];

		//gmp不支持历史任务查看报告
		if($('#oem_version').val() != CONF.VENDOR_LIST.gmp){
			columns.push({
				title: LANG.UI_ALARM_DETAILS,
				sortable: false, //默认可排序，禁用排序才写此项
				formatter:function (value, row, index, field){
					let nameStr = "--";
					if(row.detail && row.detail.info.verify_type != 1){
						nameStr = '<a class="historyDetails" id="' + row.job_id + '">' + LANG.UI_JOB_VERTIFY_REPORT + '</a>';
					}
					
					return nameStr;
				},
				events:operates,
			});
		}
		let options = {
			vin_url: '/api/v1/jobs/' + task_uuid + '/history',
			vin_method: "GET",
			sortName: 'start_time',
			sortOrder: 'desc',
			tableArea: '#history',
			// changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			toolbarId: '#history_toolbar',
			buttonsToolbar: '#history_toolbar .vin_btnToolbar',
			onRefresh: function (params) {
				$("#historyTable").bootstrapTable('hideLoading');
			},
			columns: columns,
		}

		let init = function () {
			if (!initFlag) {
				$('#historyTable').baseTableConfig().init(options);
				initFlag = true;
			} else {
				$('#historyTable').bootstrapTable('refresh');
			}
		}

		//切换到历史任务tab时初始化
		$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
			e.target // newly activated tab
			e.relatedTarget // previous active tab
			if ("#history" == e.target.hash) {
				init();
			}
		});
    }

    //初始化历史任务验证报告信息
    let initHistoryDetails = function(row){
		getReport(row.job_id);
	}

	//下载报告
	$('#downloadReport').on('click', function(){
		downloadReport();
	});

	//发送报告到配置邮箱
	$('#reportEmail').on('click', function(){
		sendReportEmail();
	});
	//获取历史任务报告
	var getReport = function(id){
		var p = {history_id:id, task_uuid: $("#task_uuid").val()};
		pAjaxRequest(p, "/api/v1/verification/job/report" , "GET", function (d) {
			var data = d.data;
			_reportContent = data.report;
			_selectHistory = id;
			$('#reportContent').html(data.report);
			//使用本地图片链接，解决跨域图片获取的问题
			$('.verify-logo').attr("src", "/img/platform/logo-mini.png");
			$('.verify-clock').attr("src", "/img/platform/clock.png");
			$('#reportModal').modal({'width':'1050px', 'height':'600px'});
		});
	}

	//下载报告
	var downloadReport = function(){
		var element = $('#reportContent');
		var w = element.width();    // 获得该容器的宽
		var h = element.height();    // 获得该容器的高
		var offsetTop = element.offset().top;    // 获得该容器到文档顶部的距离
		var offsetLeft = element.offset().left;    // 获得该容器到文档最左的距离
		var canvas = document.createElement("canvas");
		var abs = 0;
		var win_i = $(window).width();    // 获得当前可视窗口的宽度（不包含滚动条）
		var win_o = window.innerWidth;    // 获得当前窗口的宽度（包含滚动条）
		if(win_o>win_i){
			abs = (win_o - win_i)/2;    // 获得滚动条长度的一半
		}
		canvas.width = w;    // 将画布宽&&高放大
		canvas.height = h;
		var context = canvas.getContext("2d");
		context.scale(1, 1);
		context.translate(-offsetLeft-abs,-offsetTop);

		element.css('background', "#fff");

		html2canvas(element, {
			onrendered:function(canvas) {
				var contentWidth = canvas.width;
				var contentHeight = canvas.height;

				//一页pdf显示html页面生成的canvas高度;
				var pageHeight = contentWidth / 592.28 * 841.89;
				//未生成pdf的html页面高度
				var leftHeight = contentHeight;
				//pdf页面偏移
				var position = 0;
				//a4纸的尺寸[595.28,841.89]，html页面生成的canvas在pdf中图片的宽高
				var imgWidth = 595.28;
				var imgHeight = 592.28/contentWidth * contentHeight;

				var pageData = canvas.toDataURL('image/jpeg', 1.0);

				var pdf = new jsPDF('', 'pt', 'a4');
				//有两个高度需要区分，一个是html页面的实际高度，和生成pdf的页面高度(841.89)
				//当内容未超过pdf一页显示的范围，无需分页
				if (leftHeight < pageHeight) {
					pdf.addImage(pageData, 'JPEG', 0, 0, imgWidth, imgHeight );
				} else {
					while(leftHeight > 0) {
						pdf.addImage(pageData, 'JPEG', 0, position, imgWidth, imgHeight)
						leftHeight -= pageHeight;
						position -= 841.89;
						//避免添加空白页
						if(leftHeight > 0) {
							pdf.addPage();
						}
					}
				}

				pdf.save('verify_report.pdf');
			},
			allowTaint: true,
			taintTest: false,
			canvas: canvas,
			dpi: 172,//导出pdf清晰度

		});
	}

	//发送邮件
	var sendReportEmail = function(){
		var p = {history_id:_selectHistory};
		Metronic.blockUI({target: '#reportModal',animate: true});
		pAjaxRequest(p, "/api/v1/verification/job/report" , "POST", function (d) {
			Metronic.unblockUI('#reportModal');
			var op = LANG.UI_VERIFY_SEND_REPORT_EMAIL;
			operateResponseList(d, op);
		});
	}



    //初始化控件回调函数
    var initListener = function(){

		//终止任务
    	$('#stopJob').unbind().on('click', function(){
			checkOperateAuth({ type: 1, user_uuid: user_uuid, auth: 'data_manager' }, stopJob);
		});

		//启动任务
    	$('#startJob').unbind().on('click', function (){
			checkOperateAuth({ type: 1, user_uuid: user_uuid, auth: 'data_manager' }, startJob);
		});
    }






    //停止
	var stopJob = function(){
		if($('#stopJob').find('a').hasClass('disablebtn')){
			return true;
		}
		Metronic.blockUI({target: '#jobDetail',animate: true});
		pAjaxRequest({}, "/api/v1/jobs/stop/" + $('#task_uuid').val() , "POST", function (d) {
			Metronic.unblockUI('#jobDetail');
			var op = LANG.UI_JOB_STOP_DATA_VERTIFY_TASK;
			operateResponseList(d, op);

		}, false);
	}


    //启动任务
	var startJob = function(){
		if($('#startJob').find('a').hasClass('disablebtn')){
			return true;
		}
		let data = {};
		data.job_uuid = $('#task_uuid').val();
		data.start_type = 1;
		Metronic.blockUI({target: '#jobDetail',animate: true});
		pAjaxRequest(data, "/api/v1/jobs/start/" + $('#task_uuid').val(), "POST", function (d) {
			Metronic.unblockUI('#jobDetail');
			var op = LANG.UI_JOB_START_DATA_VERTIFY_TASK;
			operateResponseList(d, op);
		});
	}

	//得到开启和关闭的HTML内容
	var getFlagLevelInfo = function(flag){
		var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
		if(!flag){
			html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		}
		return html;
	}

    return {
        //main function to initiate the module
        init: function () {
        	initBasicInfo();
            initLogGrid();
            initHistoryGrid();
    		initListener();
        }

    };

}();

jQuery(document).ready(function() {
	VerifyJobDetails.init();
});
