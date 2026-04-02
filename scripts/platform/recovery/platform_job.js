var VMJobDetails = function () {

	var initChartFlag = false; //任务曲线图初始化标志
	var initTableFlag = false; //对象列表初始化标志
	var initHistoryTableFlag = false; //历史任务列表初始化标志
	var isLoading = false; // 标记是否已经加载过策略信息
	var expandIndex = null;
	var myChart;
	var pointType = 1; // 时间点类型 1虚拟化 2整机 3实时
	var devColums = [];
	var moduleType = 2;
	let queryParams = {};

	// 存储ztree配置和数据
	let ztreeDetails, ztreeId, zTreeSetting, zTreeData;

	var _jobUuid = $("#task_uuid").val();
	var taskType = $("#task_type").val();
	var userUuid = '';

	//初始化基本信息
	var initBasicInfo = function(){

		var updateInterval = 5000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
				clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
				return;
			}
			var parmas = {};
			if (!isLoading) {
				parmas.simple = 2;
			}
			pAjaxRequest(parmas, "/api/v1/recovery/detail/"+_jobUuid, "GET", function (d) {
				isLoading = true;
				if (d.code == -1) {
					setTimeout(function(){
						LOCATION('./content/platform/jobs/jobs.php?uuid=' + _jobUuid, 'task');
					}, 1000);
					// 任务不存在
					clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
					return;
				}
				setBasicInfo(d.data, timerTask.VMJobDetails_taskRunningInfo);
				if (parmas.simple == 2) {
					// 初始化速度
					initSpeed(d.data);
					// 初始化策略信息
					initStragetgy(d.data);
				}
			}, false);
			timerTask.VMJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		//恢复任务的虚拟机列表表格信息处理
		if (data.point_type != undefined) {
			pointType = data.point_type;
		}
		moduleType = data.module_type;
		userUuid = data.user_uuid;
		//初始化虚拟机列表
		initRecoveryTable(data.module_type); // 初始化对象列表

		//基本信息
		$('#taskName').html(data.job_name);
		$('#taskName').attr('title', data.job_name);
		$('#taskType').html(data.job_type_des);
		$('#taskStage').html(data.current_stage_value);
		if(data.job_status_value){
			$('#status').html('<span class="label ' + getStatusLevelClass(data.job_status) + '" >' + data.job_status_value + '</span>');
		}
		$('#totalSize').html(data.total_size);
		$('#currentSize').html(data.current_size);
		$('#startTime').html(data.start_time);
		$('#intervalTime').html(data.interval_time);
		$('#endTime').html(data.end_time);
		$('#total-progress').css({width: data.total_progress});
		$('#progressright').html(data.progress);

		if (!CONF.FUNCTIONS.includes('integrity') || pointType == 3) {
			// 没有有完整性校验功能.或者是实时的点
			$('.completeConfig').hide();
		}
		if (!CONF.FUNCTIONS.includes('virusKill')) {
			// 没有病毒查杀功能
			$('.virusConfig').hide();
		}
		if (!CONF.FUNCTIONS.includes('integrity') && !CONF.FUNCTIONS.includes('virusKill')) {
			// 都没，隐藏安全策略
			$('.safe-and-complete').hide();
		}

		// 设置按钮事件
		setBtnStatus(data.job_status);
	}

	// 设置按钮事件
	var setBtnStatus = function (status) {
		// 等待、暂停、停止、错误、任务暂停中、已完成、任务成功、任务挂起 可以启动
		var startArr = [
			CONF.TASK_STATUS.WAITTING,
			CONF.TASK_STATUS.PAUSED,
			CONF.TASK_STATUS.STOPPED,
			CONF.TASK_STATUS.ERROR,
			CONF.TASK_STATUS.PAUSING,
			CONF.TASK_STATUS.FINISHED,
			CONF.TASK_STATUS.SUCCESSED,
			CONF.TASK_STATUS.CREATING,
		];
		// 等待、运行、网络故障、任务已经完成但异常、错误、准备中、启动中、已完成、任务成功、任务创建中、任务挂起 可以停止
		var stopArr = [
			CONF.TASK_STATUS.WAITTING,
			CONF.TASK_STATUS.RUNNING,
			CONF.TASK_STATUS.NETWORK_FAULT,
			CONF.TASK_STATUS.ABNORMAL,
			CONF.TASK_STATUS.ERROR,
			CONF.TASK_STATUS.PREPARING,
			CONF.TASK_STATUS.STARTING,
			CONF.TASK_STATUS.FINISHED,
			CONF.TASK_STATUS.SUCCESSED,
			CONF.TASK_STATUS.CREATING,
			CONF.TASK_STATUS.PENDING,
		];

		// 默认都不可操作
		setControlBtn('startJob', false);
		setControlBtn('stopJob', false);
		setControlBtn('delJob', false);

		if (startArr.includes(status)) {
			// 可以启动
			setControlBtn('startJob', true);
		}
		if (stopArr.includes(status)) {
			// 可以停止
			setControlBtn('stopJob', true);
		}

		// 任务是停止的，能删除
		if (status == CONF.TASK_STATUS.STOPPED) {
			setControlBtn('delJob', true);
		}
		// 如果是实时的，并且是错误的
		if (status == CONF.TASK_STATUS.ERROR && moduleType == CONF.MODULE_TYPE.VOL_CDP) {
			// 可以删除，禁用停止
			setControlBtn('delJob', true);
			setControlBtn('stopJob', false);
		}

		//任务详情按钮
		$('#startJob').unbind().on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			checkOperateAuth(
				{
					type: 1,
					user_uuid: userUuid,
					auth: 'current_job'
				},
				function (){
					operateJob(LANG.UI_JOB_START, 1)
				}
			);
		});
		$('#stopJob').unbind().on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			checkOperateAuth(
				{
					type: 1,
					user_uuid: userUuid,
					auth: 'current_job'
				},
				function (){
					bootbox.prompt({
						title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
						inputType: 'password',
						callback: debounce(function (r) {
							if (r == null) return;
							// 执行操作验证密码
							Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
							var encrypt = new JSEncrypt();
							encrypt.setPublicKey(CONF.PUBLIC_KEY);
							var password = encrypt.encrypt(r);
							var that = this;
							pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
								Metronic.unblockUI('#jobDetail');
								if (result.code == 0) {
									$(that).modal('hide');
									// 执行停止操作
									operateJob(LANG.UI_PLATFORM_RECOVERY_STOP_JOB, 1, '/api/v1/jobs/stop/'+_jobUuid);
								} else {
									UIToastr.showWarning(result.title, result.message);
								}
							});
						}, 300, false)
					})
				}
			);
		});

		$('#delJob').unbind().on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			checkOperateAuth(
				{
					type: 1,
					user_uuid: userUuid,
					auth: 'current_job'
				},
				function (){
					bootbox.confirm({
						title: LANG.UI_JOB_DELETE_JOB,
						message: LANG.UI_JOB_DELETE_JOB_TIPS,
						callback: debounce(function (r) {
							if (!r) return;
							// 执行操作
							operateJob(LANG.UI_JOB_DELETE_JOB, 2, '/api/v1/jobs/'+_jobUuid, 'DELETE');
						}, 300)
					})
				}
			);
		});
	}

	// 操作任务
	var operateJob = function (operate, type, url = '', method = 'POST'){
		if (url == '') {
			url = '/api/v1/jobs/start/' + _jobUuid;
		}
		Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
		pAjaxRequest({start_type: type}, url, method, function (result) {
			Metronic.unblockUI('#jobDetail');
			if (result.code == 0 || result.code == 200) {
				if (type == 2) {
					// 删除成功，要跳转到任务列表去
					cleanTime();
					setTimeout(function (){
						LOCATION('./content/platform/jobs/jobs.php', 'task');
					}, 2000)
				}
				UIToastr.showSuccess(operate, result.message)
			} else {
				UIToastr.showError(operate, result.message);
			}
		});
	}

	// 清理定时
	var cleanTime = function (){
		clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
		clearTimeout(timerTask.VMJobDetails_speed);
		clearTimeout(timerTask.VMJobDetails_logGrid);
		clearTimeout(timerTask.VMJobDetails_historyGrid);
	}

	//设置按钮是否可用
	var setControlBtn = function(id, available){
		if(available){
			$("#" + id).find('a').removeClass('disablebtn');
		}else{
			$("#" + id).find('a').addClass('disablebtn');
		}
	}

	//得到状态的显示类型
	var getStatusLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-info";
				break;
			case 12:
			case 2:
			case 3:
				levelClass = "label-success";
				break;
			case 4:
				levelClass = "label-default";
				break;
			case 7:
			case 19:
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

	//初始化流量
	var initSpeed = function (data) {
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
			xAxis :
				{
					type : 'category',
					boundaryGap : false,
					splitLine: {
						show: false
					},
					axisLabel : {
						show:true,
						interval: 12
					},
					data:[]
				},
			yAxis :
				{
					type : 'value',
					splitLine: {
						show: true
					},
					axisLabel : {
						formatter: function(value, index){
							if(value >= 1024){
								return Math.round(value * 10 / 1024) / 10 + "MB/s";
							}else{
								return value + "KB/s";
							}
						}
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
							color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? '#2A87C8':'#44b6ae',
							opacity: 0.2
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
		p.job_uuid = $("#task_uuid").val();
		function update(){
			if(0 == $('#speedchart').size()){
				clearTimeout(timerTask.VMJobDetails_speed);
				return;
			}
			var url = '/api/v1/vm/jobs/speed';
			if (data.module_type == CONF.MODULE_TYPE.VOL_CDP) {
				url = '/api/v1/complete_machine_volcdp/jobs/speed';
			}
			pAjaxRequest(p, url, "GET", function (d) {
				var dataNow = d.data;
				initTaskSpeed(dataNow);
				data.shift();
				data.push(dataNow.speed);
				option.series[0].data = data;

				nowTime.shift();
				nowTime.push(dataNow.nowTime);
				option.xAxis.data = nowTime;


				myChart.setOption(option);
			}, false);
			timerTask.VMJobDetails_speed = setTimeout(update, updateInterval);
		}
		update();

		window.onresize = function(){
			myChart.resize();
		}

	}

	//初始化日志表格
	const initLogGrid = function(){
		$('#runninglog').runningLog({
			job_uuid: $('#task_uuid').val()
		});
	}

	/**
	 * 设置脚本抽屉内容
	 * @param {string} title
	 * @param {string} scriptType
	 * @param {string} scriptContent
	 */
	const setScriptDrawerContent = (title, scriptType, scriptContent) => {
		$('#scriptContentDrawer .drawer-title .name').html(title);
		$('#scriptContentType').html(getScriptDesByType(scriptType));
		$('#scriptContent').html(scriptContent);
		$('#scriptContentDrawer').drawer('show');
	};
	/**
	 * 获取脚本描述
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

	//初始化恢复任务对象表格
	var initRecoveryTable = function (module_type) {
		if (module_type == CONF.MODULE_TYPE.VOL_CDP) {
			// 实时的
			initMonitorDeviceGrid();
		}
		if (!initTableFlag) {
			// 需要根据 module_type 来决定 下面的展开详情的 恢复目标显示情况
			let options = {
				vin_url: '/api/v1/recovery/job/'+_jobUuid+'/object',
				vin_method: 'GET',
				pagination: true,
				uniqueId: 'no',
				changeHeightBtn: true, //改变高度按钮
				sortable: false,
				resizable: true,
				singleSelect: false,
				detailView: true,
				vin_params: function () {
					// 所有自定义携带参数，必须return
					var params = {};
					params['search'] = $('#searchVal').val();
					return params;
				},
				detailFormatter: function (index, row, element) {
					let treeId = 'job_tree_id' + row.uuid;
					//初始化为一个树形结构
					ztreeId = treeId;
					zTreeData = row.detail.disk;
					let str = '<table class="vm-detail-table">';
					str += '<tr><td style="width: 200px">' + LANG.UI_RECOVERY_TIMEPOINT + ': </td><td>' + row.detail.timepoint_des + '</td></tr>';

					let after_script_des = '';
					var after_task_script = row.script_list.after_task_script;
					if(after_task_script.length == 0){
						after_script_des += LANG.UI_PUBLIC_NOTHING;
					}else{
						for(let i = 0; i < after_task_script.length; i++){
							let info = 'value_agent_uuid="'+row.uuid+'"'+'value_script_name="'+after_task_script[i]+'"';
							after_script_des += '<a class="script_show" '+info+' style="margin-right: 10px;">' + after_task_script[i] + '</a>';
						}
					}
					str += '<tr><td style="width: 200px">' + LANG.UI_VM_AFTER_RECOVERY_SCRIPT + ': </td><td>' +after_script_des + '</td></tr>';

					if (module_type == CONF.MODULE_TYPE.VM) {
						if (pointType != 3) {
							str += '<tr><td style="width: 200px">'+LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT+': </td><td>' + row.detail.path + '</td></tr>';
						}
						str += `<tr>
								<td style="width: 200px">${LANG.UI_RECOVERY_GOAL}: </td>
								<td>${LANG.UI_VCENTER_VCENTER}: ${row.detail.d_vcenter_iP}</br> ${LANG.UI_VCENTER_HOST}: ${row.detail.d_host_op} </br>${LANG.UI_VCENTER_VM}: ${row.detail.d_name}</td>
							</tr>
							 <tr><td style="width: 200px">${LANG.UI_PLATFORM_RECOVERY_JOB_DISK_COMPARE}: </td><td>` + row.detail.disk + `</td></tr>`;
					} else {
						// 整机
						if (pointType != 3) {
							str += '<tr><td style="width: 200px">'+LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT+': </td><td>' + row.source_name + '</td></tr>';
						}
						str += `<tr>
								<td style="width: 200px">${LANG.UI_RECOVERY_GOAL}: </td>
								<td> ${LANG.UI_PUBLIC_HOST}: ${row.detail.d_name}</td>
							</tr>
							<tr>
							<td style="width: 200px;position: absolute;">${LANG.UI_PLATFORM_RECOVERY_JOB_DISK_COMPARE}: </td>
							<td> <ul id="`+treeId+`" class="ztree" style="display:inline-block"></ul></td>
							</tr>`;
					}
					str += '</table>';
					$(element).append(str);
					if (module_type != CONF.MODULE_TYPE.VM) {
						$('#'+treeId).html('');
						setTaskDevTree(row.detail.disk, treeId);
					}
				},
				onRefresh: function (params) {
					$("#vm-table").bootstrapTable('hideLoading');
				},
				columns: [ //列定义
					{
						field: 'no',
						title: LANG.UI_PUBLIC_TABLE_ID,
					},
					{
						field: 'source_name',
						title: LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT_NAME,
						// width: '300px',
					},
					{
						field: 'name',
						title: LANG.UI_PLATFORM_RECOVERY_TARGET_OBJECT_NAME,
						formatter: (value, row) => {
							return row.detail.d_name;
						},
						// width: '300px',
					},
					{
						field: 'size',
						title: LANG.UI_PLATFORM_RECOVERY_TARGET_SIZE,
						// width: '100px',
					},
					{
						field: 'valid_size',
						title: LANG.UI_PUBLIC_VM_VALID_SIZE,
						// width: '200px',
					},
					{
						field: 'transport_size',
						title: LANG.UI_PUBLIC_TRANSFER_SIZE,
						// width: '200px',
					},
					{
						field: 'write_size',
						title:  LANG.UI_PUBLIC_REAL_SIZE,
						// width: '200px',
					},
					{
						field: 'speed',
						title: LANG.UI_TASK_AWS_INSTANCE_SPEED,
						// width: '200px',
					},
					{
						field: 'percent',
						title: LANG.UI_TASK_AWS_TRANS_PROGRESS,
						// width: '200px',
					},
					{
						field: 'status',
						title: LANG.UI_PUBLIC_STATUS,
						// width: '200px',
					},
					{
						field: 'description',
						title: LANG.UI_PUBLIC_DESCRIPTION,
						// width: '200px',
					}
				],
				onPostBody: (data) => {
					if (ztreeDetails) {
						var expandedNodeIds = [];
						var allNodes = ztreeDetails.transformToArray(ztreeDetails.getNodes());
						// 获取之前的展开节点
						allNodes.forEach(function(node) {
							if (node.open) {
								expandedNodeIds.push(node.id); // 保存展开状态的节点 ID
							}
						});
						// 给节点对象重新配置open属性
						zTreeData.forEach(function(node) {
							if ($.inArray(node.id, expandedNodeIds) != -1) {
								node.open = true;
							} else {
								node.open = false;
							}
						});
						ztreeDetails = $.fn.zTree.destroy(ztreeId);
						ztreeDetails = $.fn.zTree.init($("#"+ztreeId), zTreeSetting, zTreeData);
					}

					//先解除绑定再初始化点击事件
					$(".script_show").off("click").on("click",function(){
						Metronic.blockUI({target: '#vm-table', animate: true});
						let requestData = {};
						//获取当前点击的a标签的value_agent_uuid值
						requestData.uuid = $(this).attr('value_agent_uuid');
						//value_script_name
						requestData.name = $(this).attr('value_script_name');
						//获取任务uuid
						requestData.job_uuid = $('#task_uuid').val();
						pAjaxRequest(requestData, "/api/v1/recovery/job/script", "GET",function (res) {
							Metronic.unblockUI('#vm-table');
							if (res.code == 0) {
								setScriptDrawerContent(res.data.script_name, res.data.script_type,res.data.script_content)
							} else {
								UIToastr.showError(LANG.UI_PUBLIC_TIPS, res.message);
							}
						});
					});
				}
			}
			$('#vm-table').baseTableConfig().init(options);
			initTableFlag = true;
		} else {
			$('#vm-table').bootstrapTable('refresh', {query: {job_uuid: _jobUuid}});
		}
	}

	let initMonitorDeviceGrid = function(){
		let taskUuid = $('#task_uuid').val();
		let columns = tableColums();
		var options = {
			searchInput: true,
			pagination: true,
			pageList: [5, 10, 25, 50],
			sortName:'start_time',
			sortOrder:'desc',
			detailView: true,
			detailFormatter: cmCdpDevDetail,
			uniqueId: 'dev_uuid',
			vin_url: '/api/v1/complete_machine_volcdp/jobs/monitor_device_info',
			vin_method: "GET",
			vin_params: function (params) {
				let  info = {};
				info.task_uuid = taskUuid;
				info.task_type = taskType;
				return info;
			},
			onRefresh: function (params) {
				$("#vm-table").bootstrapTable('hideLoading');
			},
			onPostBody: function () {
				$('#vm-table th[data-field="num"]').css('width','3%');
				$('#vm-table th[data-field="host_name"]').css('width','20%');
				$('#vm-table th[data-field="backup_disk"]').css('width','10%');
				$('#vm-table th[data-field="data_size"]').css('width','8%');
				$('#vm-table th[data-field="sync_data_size"]').css('width','8%');
				$('#vm-table th[data-field="transfer_size"]').css('width','8%');
				$('#vm-table th[data-field="write_size"]').css('width','10%');
				$('#vm-table th[data-field="standby"]').css('width','20%');
				$('#vm-table th[data-field="mapping_disk"]').css('width','10%');


				$('#vm-table th[data-field="data_source_host"]').css('width','20%');
				$('#vm-table th[data-field="valid_data_size"]').css('width','8%');
				$('#vm-table th[data-field="app_is_conf"]').css('width','8%');
				$('#vm-table th[data-field="takeover_standby_host"]').css('width','20%');

				clearTimeout(timerTask.cm_dev_timetask);
				timerTask.cm_dev_timetask = setTimeout(function(){
					// $('#cmMonitorDeviceTable').bootstrapTable('refreshOptions', {columns: columns});
					$('#vm-table').bootstrapTable('refresh');
					$("#vm-table").bootstrapTable('hideLoading');
				}, 5000);
				if (ztreeDetails) {
					var expandedNodeIds = [];
					var allNodes = ztreeDetails.transformToArray(ztreeDetails.getNodes());
					// 获取之前的展开节点
					allNodes.forEach(function(node) {
						if (node.open) {
							expandedNodeIds.push(node.id); // 保存展开状态的节点 ID
						}
					});
					// 给节点对象重新配置open属性
					zTreeData.forEach(function(node) {
						if ($.inArray(node.id, expandedNodeIds) != -1) {
							node.open = true;
						} else {
							node.open = false;
						}
					});
					ztreeDetails = $.fn.zTree.destroy(ztreeId);
					ztreeDetails = $.fn.zTree.init($("#"+ztreeId), zTreeSetting, zTreeData);
				}
			},
			columns:columns,
		}
		if (0 == $('#task_uuid').size()) {
			clearTimeout(timerTask.cm_dev_timetask);
			return;
		}
		if (!initTableFlag) {
			$('#vm-table').baseTableConfig().init(options);
			initTableFlag = true;
		} else {
			$('#vm-table').bootstrapTable('refresh');
		}

	}

	//监控设备列表
	var tableColums = function(){
		devColums = [
			{field: 'num',title: LANG.UI_PUBLIC_TABLE_ID,sortable: false,width: "10px"},
			{field: 'data_source_host',title: LANG.UI_CM_CDP_SOURCE_HOST,sortable: true},
			{field: 'recovery_dev',title: LANG.UI_CM_CDP_RECOVERT_DISK, sortable: false },
			{field: 'data_size',title: LANG.UI_CM_CDP_DEVICE_CAPACITY_TITLE, sortable: false},
			{field: 'sync_data_size',title: LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME, sortable: false},
			{field: 'transfer_size',title: LANG.UI_PUBLIC_TRANSFER_SIZE,sortable: false},
			{field: 'recovery_time_point',title: LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIME_POINT, sortable: false},
			{field: 'recovery_target_host', title: LANG.UI_VOL_CDP_RECOVER_TARGET_HOST,sortable: false },
			{field: 'recovery_target_dev', title: LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_DISK,sortable: false}
		];


		return devColums;
	}

	/**
	 * 获取监控磁盘对应详细信息
	 */
	var lastIndex = [-1, -1];
	var cmCdpDevDetail = function(index,row, element){
		//行展开互斥操作
		if (index != lastIndex[1]) {
			lastIndex.push(index);
			$('#vm-table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
			lastIndex.splice(0, 1);
		}
		element = '.detail-view td';
		let html = ' <div class = "d-flex mt-5">'
		// Metronic.blockUI({target: element, animate: true});
		Metronic.blockUI({target:  $(element),animate: true,timeout: 100,allowMultiple: false});

		var treeId = "taskDevTree"+row.dev_uuid;
		html += ' <div style = "flex: 1; box-sizing: border-box;" ><b>'+LANG.UI_CM_CDP_MONITOR_VOLUMES_IFNO+'</b>:</div>'
			+' <div style = "flex: 9; box-sizing: border-box;margin-top:-8px;"> '
			+' <ul id="'+treeId+'" class="ztree" style="display:inline-block"></ul>'
			+' </div>';
		html += ' </div>';
		$(element).append(html);
		let info = {};
		info.task_uuid = $('#task_uuid').val();
		info.dev_uuid = row.dev_uuid;
		info.task_type = taskType;
		pAjaxRequest(info, '/api/v1/complete_machine_volcdp/jobs/monitor_device_details', 'GET', function (res) {
			var data = res.data;
			setTaskDevTree(data,treeId);
			setTimeout(Metronic.unblockUI($(element)), 10);
		});
	}

	//渲染设备树形结构
	var setTaskDevTree = function (zNodes, treeId) {
		ztreeId = treeId;
		zTreeData = zNodes;
		var setting = {
			check: {
				enable: false,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					rootPId: 0
				},key: {
					title: "name"
				}
			},
			view: {
				fontCss: getFontCss,
				showLine: false,
				showIcon: false,
				nameIsHTML: true
			},
		};
		zTreeSetting = setting;
		ztreeDetails = $.fn.zTree.init($("#"+treeId), setting, zNodes);
		ztreeDetails.expandAll(true);
	}
	var getFontCss = function(treeId, treeNode) {
		var css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.inbackup){
			css = {color:"green", "font-weight":"bold"};
		}
		if(!!treeNode.highlight){
			//搜索使用的样式
			css = {color:"#A60000", "font-weight":"bold"};
		}
		return css;
	}

	//初始化历史任务表格
	var initHistoryTable = function () {
		if (!initHistoryTableFlag) {
			let options = {
				tableArea: '#history',
				vin_url: '/api/v1/jobs/' + _jobUuid + '/history',
				vin_method: 'GET',
				queryParamsType: 'limit',
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 20,
				pageList: [10, 20, 50, 100],
				paginationLoop: false,
				uniqueId: 'no',
				changeHeightBtn: true, //改变高度按钮
				batchOperation: false, // 批量操作
				sortable: false,
				sortName: 'start_time',
				sortOrder: 'desc',
				resizable: true,
				singleSelect: false,
				detailView: true,
				detailFormatter: historyDetails,
				onRefresh: function (params) {
					$("#historytable").bootstrapTable('hideLoading');
				},
				onPostBody: function () {
					var tableData = $('#historytable').bootstrapTable('getData');
					if (tableData.length == 0)return;
					if (null !== expandIndex) {
						$('#historytable').bootstrapTable('expandRow', expandIndex);
					}
				},
				onExpandRow: (index) => {
					if (null === expandIndex) {
						expandIndex = index;
					} else if (index !== expandIndex) {
						$('#historytable').bootstrapTable('collapseRow', expandIndex);
						expandIndex = index;
					}
				},
				onCollapseRow: () => {
					expandIndex = null;
				},
				columns: [ //列定义
					{
						field: 'num',
						title: LANG.UI_PUBLIC_TABLE_ID,
					},
					{
						field: 'job_status',
						title: LANG.UI_VISUAL_RESULT,
						formatter: function (value, data) {
							if (0 == data.job_status_value) {
								return '<span class="label label-sm label-success status-icon">' + value + '</span>';
							} else if (45 == data.job_status_value) {
								return '<span class="label label-sm label-info status-icon">' + value + '</span>';
							} else {
								return '<span class="label label-sm label-danger status-icon">' + value + '</span>';
							}
						}
					},
					{
						field: 'all_size',
						title:  LANG.UI_PLATFORM_RECOVERY_TARGET_SIZE,
						// width: '100px',
					},
					{
						field: 'validate_size',
						title: LANG.UI_PUBLIC_VM_VALID_SIZE,
						// width: '200px',
					},
					{
						field: 'speed_size',
						title: LANG.UI_PUBLIC_TRANSFER_SIZE,
						// width: '200px',
					},
					{
						field: 'write_size',
						title:  LANG.UI_PUBLIC_REAL_SIZE,
						// width: '200px',
					},
					{
						field: 'start_time',
						title:  LANG.UI_PUBLIC_START_TIME,
						// width: '200px',
					},
					{
						field: 'finish_time',
						title: LANG.UI_PUBLIC_END_TIME,
						// width: '200px',
					},
				]
			}
			$('#historytable').baseTableConfig().init(options);
			initHistoryTableFlag = true;
		} else {
			$('#historytable').bootstrapTable('refresh');
		}
		timerTask.VMJobDetails_historyGrid = setTimeout(initHistoryTable, 10000);
	}
	let historyDetails = function (index, row, element) {
		if (moduleType == CONF.MODULE_TYPE.VOL_CDP) {
			// 实时的
			historyDetail(index, row, element);
		} else if (moduleType == CONF.MODULE_TYPE.VM) {
			// 虚拟化的
			historyDetailvm(index, row, element);
		} else {
			// 整机的
			historyDetailos(index, row, element);
		}
	}
	let historyDetailos = function (index, row, element){
		let str = '<table class="history-detail-table">';
		if (row.detail.list.resource_limiting_node_config) {
			// 如果开启了资源限制，只显示资源限制内容详情
			str += `<tr>
                    <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th></tr>`;

			let resourceLimitingNodeConfig = row.detail.list.resource_limiting_node_config;
			str +=
				`<tr>
                    <td>` + LANG.UI_PUBLIC_ON + `</td>
                    <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                    <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                </tr>`;
		} else {
			let vms_details = row.detail.list;
			str += '<tr>' +
				'<th>' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>' +
				'<th>' + LANG.UI_TASK_AWS_RECOVERY_NAME + '</th>' +
				'<th>' + LANG.UI_JOB_HIS_TOTAL_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_VM_VALID_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_REAL_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_STATUS + '</th>' +
				'<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>' +
				'</tr>';
			$.each(vms_details, function (i, v) {
				str += '<tr>' +
					'<td>' + v.timepoint + '</td>' +
					'<td>' + v.new_os_name + '</td>' +
					'<td>' + v.os_size + '</td>' +
					'<td>' + v.os_valid_size + '</td>' +
					'<td>' + v.transport_size + '</td>' +
					'<td>' + v.write_size + '</td>' +
					'<td>' + v.task_status + '</td>' +
					'<td>' + v.error_code_des + '</td>';
				str += '</tr>';
			});
		}
		$(element).append(str);
	}

	let historyDetailvm = function (index, row, element){
		let str = '<table class="history-detail-table">'
		if (row.detail.list.resource_limiting_node_config) {
			// 如果开启了资源限制，只显示资源限制内容详情
			str += `<tr>
                    <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th></tr>`;

			let resourceLimitingNodeConfig = row.detail.list.resource_limiting_node_config;
			str +=
				`<tr>
                    <td>` + LANG.UI_PUBLIC_ON + `</td>
                    <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                    <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                </tr>`;
		} else {
			let vms_details = row.detail.list.vms_details;
			str += '<tr>' +
				'<th>' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th>' +
				'<th>' + LANG.UI_TASK_AWS_RECOVERY_NAME + '</th>' +
				'<th>' + LANG.UI_JOB_HIS_TOTAL_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_VM_VALID_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_TRANSFER_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_REAL_SIZE + '</th>' +
				'<th>' + LANG.UI_PUBLIC_STATUS + '</th>' +
				'<th>' + LANG.UI_PUBLIC_DESCRIPTION + '</th>' +
				'</tr>';
			$.each(vms_details, function (i, v) {
				str += '<tr>' +
					'<td>' + v.timepoint + '</td>' +
					'<td>' + v.new_name + '</td>' +
					'<td>' + v.vm_size + '</td>' +
					'<td>' + v.vm_valid_size + '</td>' +
					'<td>' + v.transport_size + '</td>' +
					'<td>' + v.real_size + '</td>';
				if (0 == v.task_status_value || 1 == v.task_status_value || 6 == v.task_status_value) {
					str += '<td><span class="label label-sm label-info status-icon">' + v.task_status + '</span></td>';
				} else if (4 == v.task_status_value) {
					str += '<td><span class="label label-sm label-danger status-icon">' + v.task_status + '</span></td>';
				} else {
					str += '<td><span class="label label-sm label-success status-icon">' + v.task_status + '</span></td>';
				}
				str += '<td>' + v.error_code + '</td>';
				str += '</tr>';
			});
		}
		$(element).append(str);
	}

	//获取并解析历史任务详情
	let historyDetail = function (index, row, element){
		let html = '<table id = "historyDetailtab">'
		Metronic.blockUI({target: element,animate: true});
		$(element).append(html);
		pAjaxRequest({}, '/api/v1/jobs/history/' + row.job_id + '', 'GET', function (res) {
			Metronic.unblockUI(element);
			var data = res.data;
			if (!res.success) {
				$(element).append(LANG.UI_PUBLIC_NOTHING);
				return;
			}
			if (data.list.resource_limiting_node_config) {
				// 如果开启了资源限制，只显示资源限制内容详情
				html += `<tr>
                    <th>` + LANG.UI_RESOURCE_LIMIT_CONFIG + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT + `</th>
                    <th>` + LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD + `</th></tr>`;

				let resourceLimitingNodeConfig = data.list.resource_limiting_node_config;
				html +=
					`<tr>
                    <td>` + LANG.UI_PUBLIC_ON + `</td>
                    <td>` + resourceLimitingNodeConfig[0].max_task_running_num + `</td>
                    <td>` + getResourceLimitDesHtml(resourceLimitingNodeConfig[0].prohibit_time_type, resourceLimitingNodeConfig[0].prohibit_time_vec) + `</td>
                </tr>`;
			} else {
				html += `
				<th width="10%">` + LANG.UI_JOB_HIS_BAK_TIMEPOINT + `</th>
				<th width="15%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_CLIENT + `</th>  
				<th width="10%">` + LANG.UI_MACHINE_OS_RECOVERT_DISK + `</th>
				<th width="10%">` + LANG.UI_VOL_CDP_JOB_DETAILS_VOL_CAPACITY + `</th>
				<th width="10%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_DATA_VOLUME + `</th>
				<th width="15%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_SERVER + `</th>
				<th width="10%">` + LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_VOL + `</th>
				<th width="10%">` + LANG.UI_PUBLIC_STATUS + `</th>
				<th width="15%">` + LANG.UI_PUBLIC_DESCRIPTION + `</th>
                `

				//组装内容
				for (let i = 0; i < data.list.length; i++) {
					html += `<tr>
				   <td>` + data.list[i].recovery_target_time + `</td>
					<td>` + data.list[i].agent_name + `</td>
					<td>` + data.list[i].vol_display_name + `</td>
					<td>` + data.list[i].vol_complete_size +" /" + data.list[i].vol_size + `</td>
					<td>` + data.list[i].real_complete_size + " /"+ data.list[i].real_size + `</td>
					<td>` + data.list[i].recovery_host_name + `</td>
					<td>` + data.list[i].recovery_target_mount_point + `</td>
					<td>` + data.list[i].task_status + `</td>
					<td>` + data.list[i].description + `</td>
				</tr>`
				}
			}


			html += '</table>'
			$(element).append(html);
		});
	}

	var watchEchartSizeChange = function() {
		window.onresize = function() {
			var chartWidth = $('.portlet-charts__body__speedchart').width();
			var chartHeight = $('.portlet-charts__body__speedchart').height();
			$('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
			myChart.resize();
		}
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

	// 初始化策略信息
	var initStragetgy = function (data) {
		$('#taskDirection').html(data.direction);
		$('#threadCount').html(data.thread_num);
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

		// 初始化安全策略信息
		$('#virusConfig').html($.fn.getVirusConfigDes(data.safe_strategy, 'recovery'));
		var complete = '';
		switch (data.safe_strategy.integrity_check_config.recovery_error_policy) {
			case 0: // 中断恢复
				complete = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY;
				break;
			case 1: // 继续恢复
				complete = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY;
				break;
			case 2: // 恢复到无网络环境
				complete = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK;
				break;
		}
		if (!data.safe_strategy.integrity_check_flag) {
			complete = LANG.UI_BACKUP_DATA_POINT_DES_NOT_CONFIGED;
		}
		$('#completeConfig').html(LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_SETTING + complete);

		//数据传输网段
		if(data.transport_ip_segment != ""){
			$('.ipSegmentDiv').show();
			$('#ipSegment').html(data.transport_ip_segment);
		}

		if ((data.point_type == 3 && data.module_type == CONF.MODULE_TYPE.VOL_CDP) || data.storage_type == 10) {
			// 源是实时的并且恢复到实时整机的，或者存储是磁带的，没有安全策略,
			$('.safe-and-complete').hide();
		}

		// 重试策略
		if (Array.isArray(data.retry_strategy) && !data.retry_strategy.length) {
			$('.retryDiv').hide();
		} else {
			$('#network_retry_times').html(data.retry_strategy.network_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
			$('#network_retry_interval').html(data.retry_strategy.network_retry_interval + LANG.UI_PUBLIC_SECOND);
			$('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
			if (data.retry_strategy.op_retry_flag) {
				$('.op_retry_div').show();
			} else {
				$('.op_retry_div').hide();
			}
			$('#op_retry_times').html(data.retry_strategy.op_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
			$('#op_retry_interval').html(data.retry_strategy.op_retry_interval + LANG.UI_PUBLIC_SECOND);
			$('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
			if (data.retry_strategy.task_retry_flag) {
				$('.task_retry_div').show();
			} else {
				$('.task_retry_div').hide();
			}
			let task_retry_object = '';
			if (1 == data.retry_strategy.task_retry_object) {
				task_retry_object = LANG.UI_RETRY_FAILED_OBJS_IN_TASK;
			} else if (2 == data.retry_strategy.task_retry_object) {
				task_retry_object = LANG.UI_RETRY_ALL_OBJS_IN_TASK;
			}
			$('#task_retry_object').html(task_retry_object);
			$('#task_retry_times').html(data.retry_strategy.task_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
			var minutes =  Math.floor(parseInt(data.retry_strategy.task_retry_interval) / 60);
			$('#task_retry_interval').html(minutes + LANG.UI_PUBLIC_MINUTE);
			$('.retryDiv').show();
		}

		if (data.point_type == 3 && data.module_type == CONF.MODULE_TYPE.VOL_CDP) {
			// 源是实时，并且目标是整机的，那么没得重试策略
			$('.retryDiv').hide();
		}

		// 过载保护
		$('#ignore_resource_limit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));

		// 重置主机名
		if (data.sub_module_type == 3 && data.module_type == CONF.MODULE_TYPE.VM) {
			// 公有云没得重置主机名
			$('.reset_host_names').hide();
		} else {
			var resetName = '';
			for (var z in data.reset_host_name) {
				if (data.reset_host_name[z].source != '') {
					resetName += data.reset_host_name[z].source + '->';
				}
				resetName += data.reset_host_name[z].value + '</br>';
			}
			$('#reset_host_name').html(resetName);
		}

		$('.recoveryDiv').show();	//限速恢复模块

		// 时间策略
		let timeDes;
		if (data.time_strategy[0] && data.time_strategy[0].type == 4) {
			timeDes = LANG.UI_RECOVERY_START_TYPE_TIMING
			$('#start_time').html(data.time_strategy[0].start_time);
		} else {
			timeDes = LANG.UI_RECOVERY_START_TYPE_NOW;
			$('.start-time-form').hide();
		}
		$('#recoveryTimeDes').html(timeDes);
		if (moduleType == CONF.MODULE_TYPE.VOL_CDP) {
			initVolStrategy(data);
		} else if (moduleType == CONF.MODULE_TYPE.OS) {
			initOsStrategy(data);
		} else {
			initVmStrategy(data);
		}
	}

	// 初始化虚拟机的策略
	var initVmStrategy = function (data){
		$('.vm_strategy').show();

		// 传输策略
		let transportStrategy = data.transport_strategy;
		// 合并参数
		transportStrategy = $.extend(transportStrategy, {
			thread_num: data.thread_num,
			applianceFlag: data.appliance_flag,
			applianceDes: data.appliance_des,
			agent_pool_info: data.agent_pool_info,
			transport_ip_segment: data.transport_ip_segment
		});
		$.fn.showVmJobTransferStrategy(transportStrategy, 2);

		//隐藏数据块大小
		$('.blocksizediv').hide();

		//磁带隐藏部分信息
		if (data.tape_strategy) {
			$('.threadCountDiv').hide();
		}
	}

	// 初始化整机的策略
	var initOsStrategy = function (data){
		$('.machine_strategy').show();
		var taskConfInfo = data;
		$("#transportEncrypt1").html(getFlagLevelInfo(taskConfInfo.transport_strategy.encrypt));  //传输加密
		if(taskConfInfo.transport_strategy.encrypt){
			$('.transfer-encrypt-method-div1').show();
			let encryptMethod = taskConfInfo.transport_strategy.encrypt;
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if(encryptMethod == 2){
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$('#transferEncryptMethod1').html(method);
		}else{
			$('.transfer-encrypt-method-div1').hide();
		}

		if (CONF.FUNCTIONS.includes('multithread')) {
			// 有多线程
			$('#transportThreadNum1').html(taskConfInfo.thread_num + LANG.UI_PUBLIC_NUM);  //传输线程个数
		} else {
			$('#transportThreadNum1').parent().hide();
		}
	}

	// 初始化实时的策略
	var initVolStrategy = function (data){
		$('.vol_cdp_strategy').show();
		var taskConfInfo = data;
		$("#transportEncrypt").html(getFlagLevelInfo(taskConfInfo.transport_strategy.encrypt));  //传输加密
		if(taskConfInfo.transport_strategy.encrypt){
			$('.transfer-encrypt-method-div').show();
			let encryptMethod = taskConfInfo.transport_strategy.encrypt;
			let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
			if(encryptMethod == 2){
				method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
			}
			$('#transferEncryptMethod').html(method);
		}else{
			$('.transfer-encrypt-method-div').hide();
		}
		// 实时的都不显示加密算法
		$('.transfer-encrypt-method-div').hide();

		if (CONF.FUNCTIONS.includes('multithread')) {
			// 有多线程
			$('#transportThreadNum').html(taskConfInfo.thread_num + LANG.UI_PUBLIC_NUM);  //传输线程个数
		} else {
			$('#transportThreadNum').parent().hide();
		}

		$('#transportPacketSize').html(taskConfInfo.transport_strategy.transport_block_size + " MB");  //传输数据包大小

		$('#transportNetworkMode').html(taskConfInfo.transport_strategy.mode);  //传输模式
		var ip = taskConfInfo.transport_strategy.transport_ip;
		if (ip == null) {
			ip = LANG.UI_NODE_NETWORK_MODE_AUTO_MATCH;
		}

		$('#transportNetworkInfo').html(ip);  //传输网络IP

		$('#transportCompress').html(getFlagLevelInfo(taskConfInfo.transport_strategy.compress));  //传输压缩开关

		// 压缩等级
		if(taskConfInfo.transport_strategy.compress){
			var method = '';
			switch(taskConfInfo.transport_strategy.compress_method) {
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
			$('#transportCompressMethod').html(method);
			$('.transportCompressMethodDiv').show();
		}else{
			$('.transportCompressMethodDiv').hide();
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

	//得到时间策略描述信息
	var getTimeStrategy = function(msg){
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
				des += strategy.startTime;
			}else{
				des += LANG.UI_PUBLIC_NOTHING;
			}

			if(1 == strategy.mode){
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
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.end_time;
			}else{
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.roll_interval + ", " + strategy.end_time + LANG.UI_STRATEGY_END;
			}

		}else{
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br>";
		return desEach;
	}

	//得到间隔描述
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

	var initListener = function (){
		//搜索确认事件
		$('#searchSubmit').on('click',function (){
			queryParams.search = $('#searchVal').val();
			$("#vm-table").bootstrapTable('refresh',{query:queryParams});
		})
		$('#searchVal').on('keyup',function (event) {
			if (event.key === 'Enter') {
				queryParams.search = $('#searchVal').val();
				$("#vm-table").bootstrapTable('refresh',{query:queryParams});
			}
		});

		$('#searchVal').on('focus', () => {
			$('#clearSearchBtn').removeClass('hide');
		})
		$('#clearSearchBtn').on('click', function () {
			$('#searchVal').val('');
			$('#clearSearchBtn').addClass('hide');
			queryParams.search = $('#searchVal').val();
			$("#vm-table").bootstrapTable('refresh',{query:queryParams});
		});
	}
	return {
		//main function to initiate the module
		init: function () {
			//initSwiper();
			initBasicInfo();
			initLogGrid();
			initHistoryTable();
			watchEchartSizeChange();
			initListener();
		}
	};

}();

jQuery(document).ready(function() {
	VMJobDetails.init();
});
