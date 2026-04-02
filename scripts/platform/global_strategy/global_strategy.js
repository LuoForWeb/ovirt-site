var globalStrategy = function () {
	let changeHeightFlag = false;
	let selectRowsEdit = [];
	let nowUuid = ''; // 全局定义分发的这个选择的策略uuid
	var initTaskTableFlag = false; //任务表格初始化标志
	let task_uuid_list = {};
	let daterangepicker_start_time = "";
	let daterangepicker_end_time = "";

	var initListener = function () {

		var des = '';
		if ($.inArray('p_global_speed_strategy_delete', CONF.PERMISSION_ARR) !== -1) {
			des += `<div class="customBtn1">
            <button type="button" id="delete_strategy" class="b-btn brr2 mr12 table-toolbar-btn"
                data-toggle="tooltip" data-placement="bottom" data-html="true" title="${LANG.UI_PUBLIC_DELETE}">
                <i class="icon-gray-delete"></i>
            </button></div>
            `;
		}

		des += $('#left_btn').html();
		if ($.inArray('p_global_speed_strategy_add', CONF.PERMISSION_ARR) !== -1) {
			des += '<div class="btn-group">' +
				'<label class="">' +
				'<button class="btn table-toolbar-btn" id="add-strategy" style="">' +
				'<i class="viconfont vicon-biaogetianjia"></i><span>' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + '</span>' +
				'</button>' +
				'</label>' +
				'</div>';
		}
		if ($.inArray('p_global_speed_strategy_edit', CONF.PERMISSION_ARR) !== -1) {
			des += '<div class="btn-group">' +
				'<label class="">' +
				'<button class="btn table-toolbar-btn" id="edit-strategy" style="">' +
				'<i class="viconfont vicon-xiugai"></i><span>' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY + '</span>' +
				'</button>' +
				'</label>' +
				'</div>';
		}
		if ($.inArray('p_global_speed_strategy_send', CONF.PERMISSION_ARR) !== -1) {
			des += '<div class="btn-group">' +
				'<label class="">' +
				'<button class="btn table-toolbar-btn" id="send-strategy" style="">' +
				'<i class="viconfont vicon-a-Efferent-threechuanchu3"></i><span>' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEND + '</span>' +
				'</button>' +
				'</label>' +
				'</div>';
		}
		$('#left_btn').html(des);
		//绑定事件
		toBindEvent();

	}

	// 高度改变
	var change_height = function () {
		if (changeHeightFlag == false) {
			changeHeightFlag = true;
			$('#strategy_speed_table>tbody>tr').css(
				'cssText', 'height: 60px!important',
			)
			$('#global_strategy_toolbar .change_height i').addClass('icon-auto-height2');
		} else if (changeHeightFlag == true) {
			changeHeightFlag = false
			$('#strategy_speed_table>tbody>tr').css(
				'cssText', 'height: 40px!important',
			)
			$('#global_strategy_toolbar .change_height i').removeClass('icon-auto-height2');
		}
	}
	//初始化表格
	var initDataTable = function () {
		const options = {
			toolbarId: '#global_strategy_toolbar',
			buttonsToolbar: '#global_strategy_toolbar .vin_btnToolbar',
			vin_url: "/api/v1/storages/global_speed",
			vin_method: "GET",
			detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
			detailFormatter: current_detail, //详情展开
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			changeHeightBtn: true, //改变高度按钮
			showExport: true, //是否开启导出按钮
			showColumns: true, //是否开启列选择按钮
			columns: [
				{
					checkbox: true,
					sortable: false,
					forceHide: true, //隐藏掉导出该列数据
					formatter: checkFormatter
				},
				{
					field: 'name',
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'strategy_type',
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE,
					align: 'center',
				},
				{
					field: 'user_name',
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_USER,
					align: 'center',
				},
				{
					field: 'create_time',
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TIME,
					align: 'center',
				},
			],
			sortName: 'create_time',
			sortOrder: 'desc',
			onCheck: function (rowdata) {
				modifyDelStyles();
			},
			onUncheck: function (rowdata) {
				modifyDelStyles();
			},
			onCheckAll: function (alldata) {
				modifyDelStyles();
			},
			onUncheckAll: function (alldata) {
				modifyDelStyles();
			},
		};
		function checkFormatter(value, row, index) {
			return {
				disabled: false, // 设置是否可用
				checked: false // 设置选中
			};
		}

		$('#strategy_speed_table').baseTableConfig().init(options);

		// 改变表格高度
		$('#change-height').on('click', function () {
			// 点击事件处理函数
			change_height();
		})
	}

	var modifyDelStyles = function () {
		var selectedRow = $('#strategy_speed_table').bootstrapTable('getSelections');
		var btnId = '#delete_strategy';
		if (selectedRow.length == 0) {
			$('' + btnId + ' i').addClass('icon-gray-delete');
			$('' + btnId + ' i').removeClass('icon-white-delete');
			$('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
			$('' + btnId).css('cursor', 'not-allowed');
		} else {
			$('' + btnId + ' i').removeClass('icon-gray-delete');
			$('' + btnId + ' i').addClass('icon-white-delete');
			$('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
			$('' + btnId).css('cursor', 'pointer');
		}
	}

	// 策略管理详情显示
	var lastIndex = [-1, -1];
	var current_detail = function (index, data, element) {
		// 控制只显示一个
		if (index != lastIndex[1]) {
			lastIndex.push(index);
			$('#strategy_speed_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
			lastIndex.splice(0, 1);
		}
		var content = '<table><tbody>';
		content += '<tr><td style="width:100px;">' + LANG.UI_PLATFORM_ASSOCIA_TASK + '：</td><td>' + data.job_list + '</td></tr>';
		content += '<tr><td style="width:100px;">' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE + '：</td><td>' + data.strategy_type + '</td></tr>';
		content += '<tr><td style="width:100px;">' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SETTING + '：</td><td>' + data.detail + '</td></tr>';
		content += '</tbody></table>';
		$(element).append(content);
	}

	var toBindEvent = function () {
		//添加
		$('#add-strategy').on('click', function () {
			addStrategy();
		});
		//修改
		$('#edit-strategy').on('click', function () {
			if (checkAuth(1)) {
				checkOperateAuth(checkAuth(1), editStrategy)
			}
		});
		//删除
		$('#delete_strategy').on('click', function () {
			if (checkAuth(2)) {
				checkOperateAuth(checkAuth(2), deleteStrategy)
			}
		});
		// 分发
		$('#send-strategy').on('click', function () {
			if (checkAuth(3)) {
				checkOperateAuth(checkAuth(3), sendStrategy)
			}
		});
		$('#dispense_speed_submit').on('click', function () {
			var selectedRow = $('#speed_task_table').bootstrapTable('getSelections');
			if (selectedRow.length == 0) {
				UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEND_TITLE + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, LANG.UI_STORAGE_DATA_TASK_TIPS3);
				return;
			}
			let uuid = [];
			for (var j in selectedRow) {
				uuid.push(selectedRow[j]['user_uuid']);
			}
			checkOperateAuth(
				{
					type: 1,
					user_uuid: uuid.join(','),
					auth: 'current_job'
				},
				sendStrategyStep2()
			)
		});
		//添加限速策略确定
		$('#add_submit').on('click', debounce(submitStrategy,300));
	}
	//添加
	var addStrategy = function () {
		$('#mAddDrawer .add-title .viconfont').addClass('vicon-ge_add_task');
		$('#mAddDrawer .add-title .viconfont').removeClass('vicon-xiugai');
		$('#strategy_uuid').val(0);
		initStrategyName();
		$('#global_strategy_show_title').html(LANG.UI_BACKUP_FILE_ADD);
		$('#mAddDrawer').drawer('show');
		// 初始化设置策略并且携带初始数据
		let speedInfo = [];
		addGlobalStrategy.init({ 'strategy_type': 1, speedInfo: speedInfo, initSpeedFlag: 1 });
	}

	// 提交数据
	var submitStrategy = function () {
		var info = speedInfoSet(); // 读取限速组件的配置
		let name = $.trim($('#name').val());
		if (!customInputValidate('string', name)) {
			return false;
		}
		if (name == '') {
			UIToastr.showWarning(LANG.UI_STRATEGY_INPUT_NAME);
			return false;
		}
		if (info.length <= 0) {
			UIToastr.showWarning(LANG.UI_STRATEGY_VALID);
			return false;
		}
		/*for(var i=0;i<info.length;i++){
			info[i].des = LANG.UI_TENANT_GLOBAL + info[i]['des'];
		}*/
		let data = {
			name: name,
			speed_type: info[0]['type'],
			speed_info: info,
		};

		var uuid = $('#strategy_uuid').val();
		let url = "/api/v1/storages/global_speed_add";
		var msg = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD;
		if (uuid && uuid != 0) {
			url = "/api/v1/storages/global_speed_edit/" + uuid;
			msg = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY;
			// 判读下是否有关联任务，有的话提示下
			if (selectRowsEdit['has_job']) {
				//删除策略二次确认框
				bootbox.confirm({
					title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE,
					message: LANG.UI_GLOBAL_STRATEGY_SPEED_EDIT_CONFIRM + selectRowsEdit['job_list'],
					callback: function (r) {
						if (!r) return;
						submitConfirm(data, url, msg);
					}
				});
				return;
			}
		}
		submitConfirm(data, url, msg);
	}

	// 修改限速策略提交
	var submitConfirm = function (data, url, msg) {
		Metronic.blockUI({ target: '#mAddDrawer', animate: true, cenrerY: true, });
		pAjaxRequest(data, url, "POST", function (result) {
			Metronic.unblockUI('#mAddDrawer');
			if (result.code == 0) {
				// 关闭弹窗 然后请求接口完成配置
				$('#mAddDrawer').drawer('hide');
				// 刷新列表
				$('#strategy_speed_table').bootstrapTable('refresh', {
					query: { offset: 0 }
				});
				setTimeout(function () {
					$("#strategy_speed_table").bootstrapTable('uncheckAll');
				}, 1000);

				UIToastr.showSuccess(msg + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, msg + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE + result.message);
			} else {
				UIToastr.showError(msg + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE, result.message);
			}
		});
	}

	// 获取设置的所有策略配置信息
	var speedInfoSet = function () {
		let info = $('#speedstrategy').getSpeedStrategyConfigFinal();
		// console.log('最终的配置如下：');
		// console.log(info);
		return info;
	}

	// 操作权限校验
	var checkAuth = function(num = 1) {
		var selectedRow = $('#strategy_speed_table').bootstrapTable('getSelections');
		if (selectedRow.length == 0 || (num != 2 && selectedRow.length > 1)) {
			let arr = {
				1: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY, // edit
				2: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_DEL, // del
				3: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEND // send
			};
			UIToastr.showWarning(arr[num] + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ONE);
			return false;
		}
		var uuid = [];
		for (var j in selectedRow) {
			uuid.push(selectedRow[j].user_uuid);
		}

		return {
			type: 1,
			user_uuid: uuid.join(','),
			auth: 'resmanagement'
		};
	}

	//修改
	var editStrategy = function () {
		var selectedRow = $('#strategy_speed_table').bootstrapTable('getSelections');
		if (selectedRow.length > 1 || selectedRow.length == 0) {
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ONE);
			return;
		}
		Metronic.blockUI({ target: '#strategy_speed_table', animate: true, cenrerY: true, });
		var uuid = selectedRow[0].uuid;
		selectRowsEdit = selectedRow[0];
		pAjaxRequest({ uuid: uuid }, "/api/v1/storages/global_speed_detail/" + uuid, "GET", function (result) {
			$('#mAddDrawer .add-title .viconfont').removeClass('vicon-ge_add_task');
			$('#mAddDrawer .add-title .viconfont').addClass('vicon-xiugai');
			Metronic.unblockUI('#strategy_speed_table');
			if (result.code == 0) {
				$('#strategy_uuid').val(uuid);
				$('#name').val(decodeURIComponent(result.data.strategy_name));
				$('#global_strategy_show_title').html(LANG.UI_JOB_MODIFY);
				// 打开模态
				$('#mAddDrawer').drawer('show');
				// 初始化设置策略并且携带初始数据
				let speedInfo = result.data.speedInfo;
				addGlobalStrategy.init({ 'strategy_type': result.data.strategy_type, speedInfo: speedInfo, initSpeedFlag: 1 });
			} else {
				UIToastr.showError(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_MODIFY + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, result.message);
				return;
			}
		});
	}

	// 初始化分发表格
	var sendStrategy = function () {
		var selectedRow = $('#strategy_speed_table').bootstrapTable('getSelections');
		if (selectedRow.length > 1 || selectedRow.length == 0) {
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEND_TITLE + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ONE);
			return;
		}
		nowUuid = selectedRow[0].uuid;
		//分发选择任务模态框
		$('#distributeDrawerDiv').drawer('show');
		$(".running-log-search").html(LANG.UI_JOB_CREATE_OR_MODIFI_TIME);
		// 初始化任务表格
		initTaskTable();
		$('.speed-job-search').keypress(function (e) {
			if (e.which == 13) {
				var params = {};
				params['search'] = $('.speed-job-search').val();
				$('#speed_task_table').bootstrapTable('refresh', {
					query: params,
					offset: 0
				});
			}
		});
		//搜索
		$('#vin_speed_task_toolbar .positionL0').on('click', function (){
			var params = {};
			params['search'] = $('.speed-job-search').val();
			$('#speed_task_table').bootstrapTable('refresh', {
				query: params,
				offset: 0
			});
		});
	}
	var opEvent = function (){

	}

	// 初始化分发策略任务选择表格
	var initTaskTable = function () {
		var options = {
			toolbarId: '#vin_speed_task_toolbar',
			vin_url: "/api/v1/storages/global_speed_job",
			vin_method: "GET",
			vin_params: function () {
				// 所有自定义携带参数，必须return
				var params = {};
				params['search'] = $('.speed-job-search').val();
				params['start_time'] = daterangepicker_start_time;
				params['end_time'] = daterangepicker_end_time;
				return params;
			},
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: true, //搜索框
			searchClass: 'speed-job-search',
			searchSelector: '.speed-job-search', //使用哪个搜索框
			searchOnEnterKey: true, //回车搜索
			placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
			columns: [
				{
					checkbox: true,
					sortable: false,
				},
				{
					field: 'job_name',
					title: LANG.UI_GLOBAL_STRATEGY_TASK_NAME,
					sortable: false,
					align: 'center',
				},
				{
					field: 'module_type',
					title: LANG.UI_SEARCH_MODE_TYPE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'job_type',
					title: LANG.UI_SEARCH_TASK_TYPE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'create_time',
					title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
					sortable: false,
					align: 'center',
				},
				{
					field: 'operate',
					title: LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL2,
					sortable: false,
					align: 'center',
					events: opEvent,
					clickToSelect: false, //不可通过点击行选中
					formatter: function (value, row, index) {
						const selectedLevel = row.taskLevel || 1;
						const task_uuid = row.job_uuid || row.uuid || row.id;

						return `
        <select class="task-level-select form-control"
                style="height:28px;font-size:14px;padding-block:0px;" data-task_uuid="${task_uuid}">  
            <option value="1" ${selectedLevel == 1 ? 'selected' : ''}>`+LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL_NORMAL+`</option>
            <option value="2" ${selectedLevel == 2 ? 'selected' : ''}>`+LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL_FIRST+`</option>
            <option value="3" ${selectedLevel == 3 ? 'selected' : ''}>`+LANG.UI_GLOBAL_STRATEGY_TASK_LEVEL_HIGH+`</option>
        </select>
    `;
					}
				},
			],
			onPostBody: function () {
				// 表格渲染完成后的操作
				$('.speed-job-search').blur();
				task_uuid_list = {};
				$('.task-level-select').on('change', function (){
					var task_uuid = $(this).attr('data-task_uuid');
					var val = $(this).val();
					task_uuid_list[task_uuid] = val;
				})
			},
		}
		// 未初始化过则初始一个新表
		if (!initTaskTableFlag) {
			$('#speed_task_table').baseTableConfig().init(options);
			initTaskTableFlag = true;
		} else {
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#speed_task_table').bootstrapTable('destroy');
			$('#speed_task_table').baseTableConfig().init(options);
		}
		setTimeout(function () {
			$('.speed-job-search').blur();
		}, 500);
	};

	// 开始分发策略
	var sendStrategyStep2 = function () {
		var selectedRow = $('#speed_task_table').bootstrapTable('getSelections');
		if (selectedRow.length == 0) {
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEND_TITLE + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, LANG.UI_STORAGE_DATA_TASK_TIPS3);
			return;
		}
		let uuid = [];
		for (var j in selectedRow) {
			var job_uuid = selectedRow[j]['job_uuid'];
			var taskPriority = 1;
			if (task_uuid_list[job_uuid] !== undefined) {
				taskPriority = task_uuid_list[job_uuid];
			}
			uuid.push({job_uuid:job_uuid,task_priority:taskPriority});
		}

		Metronic.blockUI({ target: '#speed_task_table', animate: true, cenrerY: true, });
		pAjaxRequest({ uuids: uuid  }, "/api/v1/storages/global_speed_send/" + nowUuid, "POST", function (result) {
			if (result.code == 0) {
				// 关闭modal
				$('#distributeDrawerDiv').drawer('hide');
				// 刷新列表
				$('#strategy_speed_table').bootstrapTable('refresh', {
					query: { offset: 0 }
				});
				UIToastr.showSuccess(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEND_TITLE + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, result.message);
			}
			Metronic.unblockUI('#speed_task_table');
		});
	}

	//删除
	var deleteStrategy = function () {
		var selectedRow = $('#strategy_speed_table').bootstrapTable('getSelections');
		if (selectedRow.length == 0) {
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_DEL + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ONE);
			return;
		}

		//删除策略二次确认框
		bootbox.confirm({
			title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_DEL + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE,
			message: LANG.UI_GLOBAL_STRATEGY_SPEED_DELETE_CONFIRM,
			callback: function (r) {
				if (!r) return;
				var uuids = [];
				var check = false;
				var checkJob = [];
				for (var i in selectedRow) {
					uuids.push(selectedRow[i].uuid);
					if (selectedRow[i].has_job) {
						check = true;
						checkJob.push(selectedRow[i].job_list);
					}
				}

				if (check) {
					checkJob = $.unique(checkJob);
					checkJob = checkJob.join('<br>');
				}

				// 判读下是否有关联任务，有的话提示下
				if (check) {
					//删除策略二次确认框
					bootbox.confirm({
						title: LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_DEL + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE,
						message: LANG.UI_GLOBAL_STRATEGY_SPEED_EDIT_CONFIRM + checkJob,
						callback: function (r) {
							if (!r) return;
							submitDel(uuids);
						}
					});
					return;
				}
				submitDel(uuids);
			}
		});
	}

	var submitDel = function (uuids) {
		Metronic.blockUI({ target: '#strategy_speed_table', animate: true, cenrerY: true, });
		pAjaxRequest({ uuids: uuids }, "/api/v1/storages/global_speed_del/", "DELETE", function (result) {
			if (result.code == 0) {
				$('#delete_strategy').addClass('exch-forbid-event');
				$('#strategy_speed_table').bootstrapTable('refresh', {
					query: { offset: 0 }
				});
				setTimeout(function () {
					$("#strategy_speed_table").bootstrapTable('uncheckAll');
				}, 1000);
				UIToastr.showSuccess(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_DEL + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, result.message);
			} else {
				UIToastr.showError(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_DEL + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_TITLE_SPACE, result.message);
			}
			Metronic.unblockUI('#strategy_speed_table');
		});
	}
	function initTableHeight() {
		//拿到父窗口的高度
		var height;
		var panelH = window.innerHeight;

		height = panelH - 381;

		$("#show-global-speed .fixed-table-body").css({
			"height": height
		});
	}
	/**
	 * 自动生成限速策略名称
	 */
	const initStrategyName = () => {
		let initName = function (res) {
			if (res.data) {
				$('#name').val(res.data);
			}
		}
		pAjaxRequest({}, "/api/v1/storages/global_speed_name", "get", initName)
	}

	function initRunningLogDaterangePicker () {
		//初始化日期时间选择控件
		$('#distinct_daterangepicker_wrapper').daterangepicker({
			"autoUpdateInput": false,		 //是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,
			'drops': 'down',
			'opens': 'left',
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {
		});
		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#distinct_daterangepicker_wrapper').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			daterangepicker_start_time = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			daterangepicker_end_time = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			$(".running-log-search").html(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
			// 加载筛选后的数据（重置偏移量）
			$('#speed_task_table').bootstrapTable('refresh', {
				query: {
					'offset': 0,
					'start_time': daterangepicker_start_time,
					'end_time': daterangepicker_end_time,
				}
			});
		});

		$('#distinct_daterangepicker_wrapper').on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			daterangepicker_start_time = "";
			daterangepicker_end_time = "";
			$(".running-log-search").html(LANG.UI_JOB_CREATE_OR_MODIFI_TIME);
			//取消筛选时
			$('#speed_task_table').bootstrapTable('refresh', {
				query: {
					'offset': 0,
					'start_time': '',
					'end_time': '',
				}
			});
		});
		//展开
		$('#distinct_daterangepicker_wrapper').on('show.daterangepicker', function(ev, picker) {
			$(this).css({'background-color':'rgba(15, 191, 152, .1)'});
		});

		//收起
		$('#distinct_daterangepicker_wrapper').on('hide.daterangepicker', function(ev, picker) {
			$(this).css({'background-color':'white'})
		});

		//设置时间
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			//英文独有的
			$(".form_datetime").datetimepicker({
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-mm-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
				startDate: new Date()
			});
		}else{
			$(".form_datetime").datetimepicker({
				language:  'zh-CN',
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-MM-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
				startDate: new Date()
			});
		}
	}

	return {
		//main function to initiate the module
		init: function () {
			initDataTable();
			initListener();
			initTableHeight();
			initRunningLogDaterangePicker();
		}
	};

}();

jQuery(document).ready(function () {
	globalStrategy.init();
});
