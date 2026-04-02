var SystemAlarm = function () {
	let searchParams;
	//时间选择器全局变量,方便提交搜索的时候直接使用
	let _dateRangePicker_startTime, _dateRangePicker_endTime;
	let table = $('#systemAlarm');
	let selectedRow;
	// 手动推动告警
	let alarmId = 0;
	let alarmLevel = 0;
	let alarmTime = "";
	let alarmContent = "";
	let initLogListFlag = true;

	//初始化详情模态框
	let initModalDetails = function (event, value, row, index) {
		let initSystemAlarmDetails = function (res) {
			$('#systemAlarmModalDiv').modal({ 'width': '680px' });
			if (res.success) {
				let systemModal = $('#systemAlarmModalDiv');
				let data = res.data;
				systemModal.find('#system_alarm_id').html(data.alarm_id);
				systemModal.find('#system_alarm_level').html('<span class="label label-sm ' + getLevelClass(data.alarm_level_flag) + '">' + data.alarm_level + '</span>');
				systemModal.find('#system_alarm_time').html(data.alarm_time);
				systemModal.find('#system_alarm_content').html(data.alarm_content);
				systemModal.find('#system_alarm_solved_des').html(getFlagClass(data.alarm_solved_flag, data.alarm_solved_des));
				systemModal.find('#system_alarm_solved_user').html(data.alarm_solved_user);
				systemModal.find('#system_alarm_solved_time').html(data.alarm_solved_time);
				systemModal.find('#system_alarm_email').html(getFlagClass(data.alarm_email_flag, data.alarm_email));
				systemModal.find('#system_alarm_sms').html(getFlagClass(data.alarm_sms_flag, data.alarm_sms));
				systemModal.find('#system_alarm_weChat').html(getFlagClass(data.alarm_weChat_flag, data.alarm_weChat));
				systemModal.find('#system_alarm_weChat2').html(getFlagClass(data.alarm_weChat2_flag, data.alarm_weChat2));
				if (data.alarm_solved_flag) {
					$('.system_solved_div').show();
				} else {
					$('.system_solved_div').hide();
				}
				// 手动推送告警的内容
				alarmId = data.alarm_id;
				alarmLevel = data.alarm_level_push;
				alarmTime = data.alarm_time;
				alarmContent = data.alarm_content;
				table.bootstrapTable("refresh");
			}

		}
		pAjaxRequest({}, `/api/v1/alarm/system?system_uuid=${row.system_alarm_id}`, "GET", initSystemAlarmDetails, true);
	}

	//得到几个标识的显示样式
	let getFlagClass = function (flag, des) {
		let html = '';
		if (flag) {
			html = '<span class="label label-sm label-success">' + des + '</span>';
		} else {
			html = '<span class="label label-sm label-default">' + des + '</span>';
		}
		return html;
	}
	//得到告警的显示类型
	let getLevelClass = function (level) {
		let levelClass = '';
		switch (level) {
			case 1:
				levelClass = "label-info";
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

	let handleRecords = function () {
		let option = {
			tableArea: '#system_alarm_div',
			toolbarId: '#system_alarm_toolbar',
			buttonsToolbar: '#system_alarm_toolbar .vin_btnToolbar',
			showExport: true, //是否开启导出按钮
			showColumns: true, //是否开启列选择按钮
			fullPage: true,
			searchDiv: '#system_searchDiv',
			vin_url: '/api/v1/alarm/system',
			vin_method: 'GET',
			vin_params: function () {
				let params = {};
				if ($('#systemAlarmLevelSelect option:selected').val()) {
					params.level = $('#systemAlarmLevelSelect option:selected').val();
				}
				if (searchParams) { //如果有高级搜索参数，添加accurate_flag
					params.accurate_flag = true;
				}
				$.extend(params, searchParams);
				return params;
			},
			exportSettings: {
				showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
				custom: [
					{
						label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
						class: 'export-all-excel'
					}
				]
			},
			sortName: 'alarm_time',
			sortOrder: 'desc',
			onRefresh: function () {
				table.bootstrapTable('hideLoading');
			},
			onCheck: function () {
				modifyDelStyle('systemAlarm', 'deleteSystemAlarm');
			},
			onCheckAll: function () {
				modifyDelStyle('systemAlarm', 'deleteSystemAlarm');
			},
			onUncheck: function () {
				modifyDelStyle('systemAlarm', 'deleteSystemAlarm');
			},
			onUncheckAll: function () {
				modifyDelStyle('systemAlarm', 'deleteSystemAlarm');
			},
			LoadSuccess: function () {
				if (initLogListFlag) {
					$('#systemAlarm th[data-field="id"]').css('width', '3%');
					$('#systemAlarm th[data-field="alarm_id"]').css('width', '4%');
					$('#systemAlarm th[data-field="alarm_level"]').css('width', '5%');
					$('#systemAlarm th[data-field="alarm_time"]').css('width', '10%');
					$('#systemAlarm th[data-field="description"]').css('width', '65%');
					$('#systemAlarm th[data-field="solved_flag"]').css('width', '5%');
					$('#systemAlarm th[data-field="7"]').css('width', '5%');
					initLogListFlag = false;
				}
			},
			PostBody: function () {
				const exportOptions = {
					toolbarId: 'system_alarm_toolbar',
					url: '/api/v1/alarm/system',
					fileName: LANG.UI_REPORT_SYSTEM_ALARM
				}

				// 监听导出全部数据
				exportAllTableData(exportOptions);
			},
			columns: [
				{
					field: 'checkbox',
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					forceHide: true,
					width: 1,
					widthUnit: '%'
				},
				{
					field: 'id', //字段名
					sortable: false, //默认可排序，禁用排序才写此项
					title: LANG.UI_PUBLIC_TABLE_ID,
				},
				{
					field: 'alarm_id', //字段名
					title: LANG.UI_ALARM_ID,
				},
				{
					field: 'alarm_level',
					title: LANG.UI_ALARM_WRITE_ALARM_LEVEL,
					formatter: function (index, row) {
						let labelClass = getLevelClass(row.alarm_level);
						let content = '<span class="label label-sm ' + labelClass + '">' + row.level_des + '</span>';
						return content;
					}
				},

				{
					field: 'alarm_time',
					title: LANG.UI_ALARM_WRITE_ALARM_TIME
				},
				{
					field: 'description',
					title: LANG.UI_ALARM_WRITE_ALARM_CONTENT,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (index, row) {
						return row.description; //避免走入插件拼接title，导致显示不全
					}
				},
				{
					field: 'solved_flag',
					title: LANG.UI_ALARM_WRITE_SOLVED_FLAG,
					formatter: function (index, row) {
						let label;
						if (row.solved_flag == 1) {
							label = `<span class="label label-sm label-success">${row.solved_des}</span>`
						} else {
							label = `<span class="label label-sm label-default">${row.solved_des}</span>`
						}
						return label;
					}
				},
				{
					title: LANG.UI_ALARM_WRITE_ALARM_DETAILS,
					formatter: function (index, row) {
						return `<a class="system_alarm_details" id="${row.system_alarm_id}">` + LANG.UI_ALARM_DETAILS + `</a>`;
					},
					sortable: false, //默认可排序，禁用排序才写此项
					clickToSelect: false, //不可通过点击行选中
					events: detailEvents,
				},
			]
		}

		table.baseTableConfig().init(option);
	}

	let detailEvents = {
		//设置详情事件
		'click .system_alarm_details': function (event, value, row, index) {
			selectedRow = row;
			initModalDetails(event, value, row, index);
		}
	}

	//设置未响应告警
	let solvedAlarm = function () {
		let id = [$('#systemAlarmModalDiv #system_alarm_id').html()];
		let setNotSolvedFlag = function (res) {
			if (operateResponseList(res)) {
				DataBackupCenter.updateTopAlarmTips();
				$('#systemAlarmModalDiv #system_alarm_solved_des').html(getFlagClass(false, LANG.UI_ALARM_NOT_RESPONSE));
				$('.solved_div').hide();
				table.bootstrapTable('refresh');
			}
		}
		pAjaxRequest({ id: id, resolved_flag: false }, "/api/v1/alarm/system/response", "POST", setNotSolvedFlag, true);
	}

	//设置响应(批量)
	let solveAlarm = function () {
		let select = table.bootstrapTable('getSelections');
		if (!select.length) {
			return UIToastr.showInfo(LANG.UI_ALARM_SELECT, LANG.UI_ALARM_SELECT_TIPS);
		}
		let selectId = [];
		for (let i = 0; i < select.length; i++) {
			selectId.push(select[i].system_alarm_id);
		}
		let setSolvedFlag = function (res) {
			if (operateResponseList(res)) {
				DataBackupCenter.updateTopAlarmTips();
				table.bootstrapTable('refresh');
			}
		}
		pAjaxRequest({ id: selectId, resolved_flag: true }, "/api/v1/alarm/system/response", "POST", setSolvedFlag, true);
	}

	//删除
	let deleteAlarm = function () {
		let select = table.bootstrapTable('getSelections');
		if (!select.length) {
			return UIToastr.showInfo(LANG.UI_ALARM_DELETE_SELECT, LANG.UI_ALARM_DELETE_SELECT_TIPS);
		}
		bootbox.confirm({
			title: LANG.UI_ALARM_DELETE,
			message: LANG.UI_ALARM_DELETE_TIPS,
			callback: function (r) {
				if (!r) return;
				let selectId = [];
				for (let i = 0; i < select.length; i++) {
					selectId.push(select[i].system_alarm_id);
				}
				let deleteAlarmBack = function (res) {
					operateResponseList(res);
					DataBackupCenter.updateTopAlarmTips();
					table.bootstrapTable('refresh');
				}
				pAjaxRequest({ id: selectId, solved_flag: true }, "/api/v1/alarm/system", "DELETE", deleteAlarmBack, true);
			}
		});
	}
	//普通搜索告警等级
	let searchSystemJob = function () {
		//清除高级筛选显示内容
		// $('#system_searchDiv .searchContent').text('');
		// $('#system_searchDiv').hide();
		table.bootstrapTable('refresh');
	}
	//初始化事件
	let addListeners = function () {
		$('#alarm_nav').on('click', function (event) {
			if (event.target.tagName === 'A') { // 检查点击的元素是否是一个 <a> 标签
				// 获取父元素 li.nav-item
				let clickedItem = event.target.parentElement;
				let navType = clickedItem.getAttribute('data-type');

				if (navType === 'system_alarm') {
					handleRecords();
				}
			}
		});

		// 监听首页的任务点击
		let route = History.getState();
		let tabId = route.data.tabId || '';
		if (tabId == "system_alarm") {
			handleRecords();
		}
		$("#deleteSystemAlarm").on('click', function () {
			deleteAlarm();
		});

		$('#solveSystemAlarm').on('click', function () {
			solveAlarm();
		})

		$("#systemSolvedBtn").on('click', function () {
			solvedAlarm(selectedRow);
		});

		//切换页数保存到cookie
		$('select[name=systemAlarm_length]').on('change', function () {
			pageLength.systemalarm = this.value;
			let data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		$("#systemAlarmLevelSelect").on('change', function (e) {
			let params = {
				alarm_level: this.value
			}
			addSearchContent({ ...params, ...searchParams });
			searchSystemJob();
		});

		//弹出高级搜索模态框
		$('#system_searchAll').on('click', function () {
			$('#system_search_modal').modal({ 'width': '800px', 'height': '200px' });
		});

		//高级搜索发送请求到服务端
		$('#system_search_submit').on('click', function () {
			let p = {};
			p.startTime = _dateRangePicker_startTime; //日志时间查询范围开头
			p.endTime = _dateRangePicker_endTime;		//日志始时间查询范围结尾
			p.alarm_id = $('#sys_alarm_id').val();
			searchParams = p;
			//添加搜索条件显示
			addSearchContent(p);
			$('#system_search_modal').modal('hide');
			table.bootstrapTable('refresh');
		});

		// 手动推送告警
		$('#system_alarmPush_btn').off().on('click', function () {
			alarmPush();
		});
		// 手动推送响应
		$('#system_responsePush_btn').off().on('click', function () {
			responsePush();
		});

	}

	//显示搜索内容
	let addSearchContent = function (p) {
		let info = "";
		if (p.startTime && p.endTime && p.startTime != '' && p.endTime != '') {
			$('#system_searchDiv .searchContent #time').remove();
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> ' + LANG.UI_SEARCH_TIME_RANGE + ': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
		if (p.alarm_level && p.alarm_level != "0") {
			let level_des = LANG.UI_JOB_TASK_PRIORITY_PRIMARY;
			if (p.alarm_level == "2") {
				level_des = LANG.UI_PLATFORM_DES_WARNING;
			}
			if (p.alarm_level == "3") {
				level_des = LANG.UI_PLATFORM_DES_ERROR;
			}
			$('#system_searchDiv .searchContent #alarm_level').remove();
			info += `<span id="alarm_level" title="${level_des}" >${LANG.UI_SEARCH_ALARM_LEVEL}: <i> ${level_des}</i><em>X</em></span>`;
		}
		if (p.alarm_id && p.alarm_id != '') {
			$('#system_searchDiv .searchContent #alarm_id').remove();
			info += '<span id="alarm_id" title="' + $('#sys_alarm_id').val() + '"> ' + LANG.UI_ALARM_ID + ': <i>' + $('#sys_alarm_id').val() + '</i><em>X</em></span>';
		}
		$('#system_searchDiv .searchContent').append(info);
		$('#system_searchDiv').show();
		$('#system_searchDiv .searchContent em').on('click', function () {
			$(this).parent().remove();
			let searchContent = $('#system_searchDiv .searchContent');
			if (searchContent[0].children.length == 0) {
				$('#system_searchDiv').hide();
			}
			let parent = $(this).parent();
			let id = parent[0].id;
			if (id == "time") {
				p.startTime = "";
				p.endTime = "";
			} else if (id == 'alarm_level') {
				p[id] = "";
				$('#systemAlarmLevelSelect').val(0)
			} else {
				p[id] = "";
			}
			searchParams = p;
			table.bootstrapTable('refresh', { query: { ...searchParams } });
		});
		$('#system_searchDiv .clearSearch').on('click', function () {
			$('#system_searchDiv .searchContent').text('');
			$('#system_searchDiv').hide();
			$('#systemAlarmLevelSelect').val(0)
			searchParams = {};
			table.bootstrapTable('refresh');
		});
		//如果没搜索条件，先隐藏div
		if (!info) {
			$('#system_searchDiv').hide();
		}
	}

	//初始化日期选择插件
	let initDataTimePicker = function () {
		//初始化日期时间选择控件
		$('#dateRangePickerSystemAlarm').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({ hour: 23, minute: 59 }),												//默认结束时间
			"maxDate": moment({ hour: 23, minute: 59 }),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,										//是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function (start, end, label) {
			//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#dateRangePickerSystemAlarm').on('apply.daterangepicker', function (ev, picker) {
			//给全局变量赋值,然后设置input
			_dateRangePicker_startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_dateRangePicker_endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#dateRangePickerSystemAlarm').on('cancel.daterangepicker', function (ev, picker) {
			//清除全局变量,然后设置input
			_dateRangePicker_startTime = "";
			_dateRangePicker_endTime = "";
			$(this).val('');
		});

		//input右侧的图标事件
		$('.daterangepickerdiv i').click(function () {
			$(this).parent().find('input').click();
		});
	}

	/**
	 * 手动推送系统告警
	 */
	let alarmPush = function () {
		// 从本地存储中获取 pushFlag 的值，如果不存在则默认为 false
		let pushSystemAlarmFlag = localStorage.getItem('pushSystemAlarmFlag') === 'true' ? true : false;
		let systemAlarmIdArrData = JSON.parse(localStorage.getItem('systemAlarmIdArr')) ?? [];
		if (pushSystemAlarmFlag && systemAlarmIdArrData.includes(alarmId)) {
			operateResponseList({ message: LANG.UI_PLATFORM_THIRD_MONITOR_SEND_SUCCESS }, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_SYSTEM_ALARM);
			return;
		}
		let data = {};
		data.alarm_id = alarmId;
		data.alarm_type = CONF.FLAG.UNSET;
		data.alarm_level = alarmLevel;
		data.task_uuid = "";
		data.alarm_time = alarmTime;
		data.alarm_content = alarmContent;
		Metronic.blockUI({
			target: '#systemAlarmModalDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data, '/api/v1/system/message/push_alarm', 'POST', function (res) {
			Metronic.unblockUI('#systemAlarmModalDiv');
			if (res.success) {
				pushSystemAlarmFlag = true;
				let systemAlarmIdArr = JSON.parse(localStorage.getItem('systemAlarmIdArr')) || []; // 从 localStorage 中获取 alarmIdArr，如果不存在则初始化为空数组
				systemAlarmIdArr.push(alarmId);
				localStorage.setItem('systemAlarmIdArr', JSON.stringify(systemAlarmIdArr));
				localStorage.setItem('pushSystemAlarmFlag', pushSystemAlarmFlag);
				$('#system_alarmPush_btn').css({ 'color': '#28B4F0' });
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_SUCCESS);
			} else {
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_FAILED);
			}
		});
	}

	/**
	 * 手动推送系统响应
	 */
	let responsePush = function () {
		// 从本地存储中获取 pushFlag 的值，如果不存在则默认为 false
		let pushSystemResponseFlag = localStorage.getItem('pushSystemResponseFlag') === 'true' ? true : false;
		let systemResponseIdArrData = JSON.parse(localStorage.getItem('systemResponseIdArr')) ?? [];
		if (pushSystemResponseFlag && systemResponseIdArrData.includes(alarmId)) {
			operateResponseList({ message: LANG.UI_PLATFORM_THIRD_MONITOR_SEND_SUCCESS }, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_SYSTEM_RESPONSE);
			return;
		}
		let data = {};
		data.alarm_id = alarmId;
		data.alarm_type = CONF.FLAG.UNSET;
		data.alarm_level = alarmLevel;
		data.alarm_time = alarmTime;
		data.task_uuid = "";
		data.alarm_content = alarmContent;
		Metronic.blockUI({
			target: '#systemAlarmModalDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data, '/api/v1/system/message/push_response', 'POST', function (res) {
			Metronic.unblockUI('#systemAlarmModalDiv');
			if (res.success) {
				pushSystemResponseFlag = true;
				let systemResponseIdArr = JSON.parse(localStorage.getItem('systemResponseIdArr')) || []; // 从 localStorage 中获取 alarmIdArr，如果不存在则初始化为空数组
				systemResponseIdArr.push(alarmId);
				localStorage.setItem('systemResponseIdArr', JSON.stringify(systemResponseIdArr));
				localStorage.setItem('pushSystemResponseFlag', pushSystemResponseFlag);
				$('#system_responsePush_btn').css({ 'color': '#28B4F0' });
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_SUCCESS);
			} else {
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_FAILED);
			}
		});
	}

	return {
		//main function to initiate the module
		init: function () {
			initDataTimePicker(); //初始化日期选择
			addListeners();
		}

	};

}();

jQuery(document).ready(function () {
	SystemAlarm.init();
});