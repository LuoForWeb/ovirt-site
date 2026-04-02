var TaskAlarm = function () {
	let searchParams = {};
	let table = $('#task_alarm_table');
	//时间选择器全局变量,方便提交搜索的时候直接使用
	let _dateRangePicker_startTime, _dateRangePicker_endTime;
	let ADVANCED_SEARCH_PARAMS = {};
	let ADVANCED_SEARCH_MODULE_TASK = [];
	let initItemListFlag = false; // 初始化对象信息表格标志
	let initLogListFlag = false;
	let taskAlarmParams = {};

	// 手动推动告警
	let alarm_Id = 0;
	let alarmLevel = 0;
	let taskUuid = "";
	let alarmTime = "";
	let alarmContent = "";
	let selectAlarmData = {};
	let selectRow = {};

	// ---------------任务告警------------------
	/**
	 * 初始化任务告警列表
	 */
	let initTaskAlarmListTable = function () {
		let option = {
			tableArea: '#task_alarm_div',
			toolbarId: '#vin_task_alarm_toolbar',
			buttonsToolbar: '#vin_task_alarm_toolbar .vin_btnToolbar',
			searchClass: 'job-alarm-search',
			placeholder: `<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>`,
			vin_url: '/api/v1/alarm/job',
			vin_method: 'GET',
			fullPage: true,
			searchDiv: '#task_searchDiv',
			showExport: true, //是否开启导出按钮
			showColumns: true, //是否开启列选择按钮
			exportSettings: {
				showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
				custom: [
					{
						label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
						class: 'export-all-excel'
					}
				]
			},
			vin_params: function () {
				let params = {};
				$.extend(params, searchParams);
				if ($('#vin_task_alarm_toolbar .searchInput').val() !== '') {
					params.search = $('#vin_task_alarm_toolbar .searchInput').val();
				}
				if ($('#taskAlarmLevelSelect option:selected').val()) {
					params.alarm_level = $('#taskAlarmLevelSelect option:selected').val();
				}
				return { ...params, ...ADVANCED_SEARCH_PARAMS };
			},
			sortName: 'alarm_time',
			sortOrder: 'desc',
			placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
			onRefresh: function () {
				table.bootstrapTable('hideLoading');
			},
			onCheck: function () {
				modifyDelStyle('task_alarm_table', 'deleteTaskAlarm');
			},
			onCheckAll: function () {
				modifyDelStyle('task_alarm_table', 'deleteTaskAlarm');
			},
			onUncheck: function () {
				modifyDelStyle('task_alarm_table', 'deleteTaskAlarm');
			},
			onUncheckAll: function () {
				modifyDelStyle('task_alarm_table', 'deleteTaskAlarm');
			},
			PostBody: function () {
				modifyDelStyle('task_alarm_table', 'deleteTaskAlarm');
			},
			LoadSuccess: function () {
				if (initLogListFlag) {
					$('#task_alarm_table th[data-field="id"]').css('width', '3%');
					$('#task_alarm_table th[data-field="alarm_id"]').css('width', '4%');
					$('#task_alarm_table th[data-field="job_name"]').css('width', '10%');
					$('#task_alarm_table th[data-field="user_name"]').css('width', '5%');
					$('#task_alarm_table th[data-field="module_type_value"]').css('width', '7.5%');
					$('#task_alarm_table th[data-field="job_type_value"]').css('width', '5%');
					$('#task_alarm_table th[data-field="alarm_time"]').css('width', '10%');
					$('#task_alarm_table th[data-field="status"]').css('width', '5%');
					$('#task_alarm_table th[data-field="solved_flag"]').css('width', '9%');
					$('#task_alarm_table th[data-field="description"]').css('width', '25%');
					$('#task_alarm_table th[data-field="alarm_details"]').css('width', '5%');
					initLogListFlag = false;
				}
			},
			PostBody: function () {
				const exportOptions = {
					toolbarId: 'vin_task_alarm_toolbar',
					url: '/api/v1/alarm/job',
					fileName: LANG.UI_REPORT_TASK_ALARM
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
					field: 'job_name',
					title: LANG.UI_SEARCH_TASK_NAME,
				},
				{
					field: 'user_name',
					title: LANG.UI_MICROSOFT365_USER,
				},
				{
					field: 'module_type_value',
					title: LANG.UI_SEARCH_OBJ_TYPE,
				},
				{
					field: 'job_type_value',
					title: LANG.UI_SEARCH_TASK_TYPE,
					formatter: function (index, row) {
						if (row.job_type_value == CONF.TASK_TYPE.VOL_CDP_TAKEOVER) {
							return `<span title="${LANG.UI_VOL_CDP_TAKEOVER_AND_VERIFY_DESC}">${LANG.UI_VOL_CDP_TAKEOVER_AND_VERIFY_DESC}</span>`
						}
						return `<span title="${row.job_type}">${row.job_type}</span>`
					}
				},
				{
					field: 'status',
					title: LANG.UI_ALARM_WRITE_ALARM_LEVEL,
					formatter: function (index, row) {
						let labelClass = getLevelClass(row.status);
						let content = '<span class="label label-sm ' + labelClass + '">' + row.status_des + '</span>';
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
							label = `<span class="label label-sm label-success">${row.solved_flag_des}</span>`
						} else {
							label = `<span class="label label-sm label-default">${row.solved_flag_des}</span>`
						}
						return label;
					}
				},
				{
					field: 'alarm_details',
					title: LANG.UI_ALARM_WRITE_ALARM_DETAILS,
					formatter: function (index, row) {
						return `<a class="taskAlarmDetails" id="${row.alarm_id}">` + LANG.UI_ALARM_DETAILS + `</a>`;
					},
					sortable: false, //默认可排序，禁用排序才写此项
					clickToSelect: false, //不可通过点击行选中
					events: detailEvents,
				},
			]
		}
		table.baseTableConfig().init(deepCloneObject(option));
	}

	/**
	 * 初始化表格事件
	 */
	let detailEvents = {
		//设置详情事件
		'click .taskAlarmDetails': function (event, value, row, index) {
			Metronic.blockUI({ target: '#taskAlarm', animate: true });
			initModalDetails(row);
		}
	}

	/**
	 * 初始化详情模态框
	 * @param {*} row 
	 */
	let initModalDetails = function (row) {
		selectRow = row;
		// 每次点开详情需要重置tabs，显示详情tab
		$('#taskAlarmModalDiv .nav-tabs li').removeClass('active');
		$('#taskAlarmModalDiv .alarmDetails .tab-pane').removeClass('active');
		$('#baseInfoTabLi').addClass('active');
		$('#baseInfo_tab').addClass('active');
		let getItemInfo = function (res) {
			// 处理不同语言的弹窗大小
			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				$('#taskAlarmModalDiv').modal({ 'width': '680px' });
			} else {
				$('#taskAlarmModalDiv').modal({ 'width': '715px' });
			}
			$('.solved_div').show();
			// 初始化告警的基本信息
			let taskModal = $('#taskAlarmModalDiv');
			let data = res.data;
			taskModal.find('#alarm_id').html(data.alarm_id);
			taskModal.find('#alarm_level').html(`<span class="label label-sm ${getLevelClass(data.alarm_level_flag)}">${data.alarm_level}</span>`);
			taskModal.find('#alarm_time').html(data.alarm_time);
			taskModal.find('#alarm_content').html(data.description);
			taskModal.find('#alarm_solved_des').html(getFlagClass(data.solved_flag, data.solved_flag_des));
			taskModal.find('#alarm_solved_user').html(data.solved_user_name);
			taskModal.find('#alarm_solved_time').html(data.solved_time);
			taskModal.find('#alarm_email').html(getFlagClass(data.email_flag, data.email_flag_des));
			taskModal.find('#alarm_sms').html(getFlagClass(data.sms_flag, data.sms_flag_des));
			taskModal.find('#alarm_weChat').html(getFlagClass(data.wx_flag, data.wx_flag_des));
			taskModal.find('#alarm_weChat2').html(getFlagClass(data.wx2_flag, data.wx2_flag_des));
			taskModal.find('#task_name').html(data.task_name);
			taskModal.find('#module_type').html(data.module_type_des);
			taskModal.find('#task_type').html(data.task_type_des);
			taskModal.find('#storage').html(data.storage);
			taskModal.find('#node').html(data.node);
			taskModal.find('#task_log').html(data.task_log);
			// 日志信息为空不显示对应tab
			if (data.task_log == '') {
				$('#logTabLi').hide();
			} else {
				$('#logTabLi').show();
			}
			// 没有history_uuid无法获取对象信息，不显示响应tab
			if (data.history_uuid == '' || (data.module_type == CONF.MODULE_TYPE.M365 && [CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.BACKUP_COPY_FETCH].includes(data.task_type))) {
				$('#itemInfoTabLi').hide();
			} else {
				initItemInfo(data);
				selectAlarmData = { ...data };
				$('#itemInfoTabLi').show();
			}
			row.solved_flag = 1;
			row.solved_flag_des = LANG.UI_ALARM_LABEL_STATUS_RESPONSED;
			table.bootstrapTable("updateByUniqueId", {
				id: row.alarm_id,
				row: row
			});
		}

		pAjaxRequest({ alarm_id: row.alarm_id }, "/api/v1/alarm/job", "GET", getItemInfo, true);
	}
	/**
	 * 初始化对象信息
	 */
	const initItemInfo = function (data) {
		let { module_type, alarm_id, task_type } = data;
		if (task_type == CONF.TASK_TYPE.ARCHIVE || task_type == CONF.TASK_TYPE.ARCHIVE_FETCH) {
			$('#itemInfoTabLi').hide();
			return;
		} else {
			$('#itemInfoTabLi').show();
		}
		taskAlarmParams.alarm_id = alarm_id;
		taskAlarmParams.module_type = module_type;
		// 每个模块的对象名称不一样
		let itemTab = $('#itemInfoTabLi a');
		let itemTabText = '';
		switch (module_type) {
			case CONF.MODULE_TYPE.VM:
				itemTabText = `<i class="viconfont vicon-ge_vm"></i> ${LANG.UI_JOB_VIRTUAL_MACHINE_INFO}`;
				break;
			case CONF.MODULE_TYPE.FS:
				itemTabText = `<i class="viconfont vicon-ge_backup_file"></i> ${LANG.UI_ALARM_ITEM_LABEL_FS}`;
				break;
			case CONF.MODULE_TYPE.DB:
				itemTabText = `<i class="viconfont vicon-db_protect"></i> ${LANG.UI_ALARM_ITEM_LABEL_DB}`;
				break;
			case CONF.MODULE_TYPE.OS:
				itemTabText = `<i class="viconfont vicon-zhuji"></i> ${LANG.UI_JOB_HOST_INFO}`;
				break;
			case CONF.MODULE_TYPE.NAS:
				itemTabText = `<i class="viconfont vicon-ge_backup_file"></i> ${LANG.UI_ALARM_ITEM_LABEL_FS}`;
				break;
			case CONF.MODULE_TYPE.M365:
				itemTabText = `<i class="viconfont vicon-vicon-dbhost"></i> ${LANG.UI_ALARM_ITEM_LABEL_M365}`;
				break;
			case CONF.MODULE_TYPE.KUBERNETES:
				itemTabText = `<i class="viconfont vicon-zhujiming"></i> ${LANG.UI_ALARM_ITEM_LABEL_SOURCE_LIST}`;
				break;
			case CONF.MODULE_TYPE.FILE_COPY:
				itemTabText = `<i class="viconfont vicon-ge_backup_file"></i> ${LANG.UI_JOB_FILE_COPY_INFO}`;
				break;
		}
		itemTab.html(itemTabText);
		// 文件模块和365显示路径信息
		if ([CONF.MODULE_TYPE.FS, CONF.MODULE_TYPE.M365].includes(module_type)) {
			$('.itemInfo_text').show();
			$('#itemInfo_tab .table-container').hide();
			// 文件复制显示复制来源和目的
		} else if (CONF.MODULE_TYPE.FILE_COPY == module_type) {
			$('#itemInfo_tab .table-container').hide();
			$('.itemInfo_text').show();
		} else {
			$('.itemInfo_text').hide();
			$('#itemInfo_tab .table-container').show();
		}
	}
	// 初始化对象表格
	const initItemTable = function () {
		let options = {
			vin_url: "/api/v1/alarm/job/item",
			vin_method: "GET",
			vin_params: function () {
				return taskAlarmParams;
			},
			sortName: 'index',
			sortOrder: 'desc',
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			PostBody: function () {
				let title = LANG.UI_JOB_VM_NAME;
				switch (taskAlarmParams.module_type) {
					case CONF.MODULE_TYPE.FS:
					case CONF.MODULE_TYPE.OS:
					case CONF.MODULE_TYPE.VOL_CDP:
						title = LANG.UI_BACKUP_FILE_HOSTNAME;
						break;
					case CONF.MODULE_TYPE.NAS:
						title = LANG.UI_VISUAL_NAS_DEVICE;
						break;
					case CONF.MODULE_TYPE.DB:
						title = LANG.UI_COPY_DETAIL_DB_NAME;
						break;
					case CONF.MODULE_TYPE.M365:
						title = LANG.UI_MICROSOFT365_ORGANIZATION_NAME;
						break;
				}
				if ([CONF.MODULE_TYPE.FS, CONF.MODULE_TYPE.NAS, CONF.MODULE_TYPE.M365].includes(taskAlarmParams.module_type)) {
					$('#itemInfo_table').bootstrapTable('hideColumn', 'status');
					$('#itemInfo_table').bootstrapTable('showColumn', 'source_list');
				} else {
					$('#itemInfo_table').bootstrapTable('showColumn', 'status');
					$('#itemInfo_table').bootstrapTable('hideColumn', 'source_list');
				}
				$('#itemInfo_table').bootstrapTable('updateColumnTitle', {
					field: 'item_name',
					title: title
				});
				$('#itemInfo_table th[data-field="index"]').css('width', '2.5%');
			},
			columns: [
				{
					field: 'index',
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: false,
				},
				{
					field: 'item_name',
					title: LANG.UI_JOB_VM_NAME,
					sortable: false,
				},
				{
					field: 'source_list',
					title: LANG.UI_ALARM_ITEM_LABEL_SOURCE_LIST,
					sortable: false,
				},
				{
					field: 'status',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: false,
					formatter: function (value, row, index) {
						return row.status_des;
					},
				},
			],
		}
		if (!initItemListFlag) {
			$('#itemInfo_table').baseTableConfig().init(options);
			initItemListFlag = true;
		} else {
			$('#itemInfo_table').bootstrapTable("refresh", { query: taskAlarmParams });
		}
	};
	const initItemText = function () {
		$('.itemInfo_text').empty();
		let getItemText = function (res) {
			if (res.success) {
				if (res.data && res.data.rows.length > 0) {
					for (let i = 0; i < res.data.rows.length; i++) {
						let data = res.data.rows[i];
						let title = LANG.UI_JOB_COPY_SRC_AGENT_NAME;
						switch (data.module_type) {
							case CONF.MODULE_TYPE.M365:
								title = LANG.UI_MICROSOFT365_ORGANIZATION_NAME;
								break;
							case CONF.MODULE_TYPE.FS:
								if (data.sub_module_type == 2) {
									title = LANG.UI_VISUAL_NAS_DEVICE;
								}
								if (data.sub_module_type == 3) {
									title = LANG.UI_HADOOP_CLUSTER_NAME;
								}
								if (data.sub_module_type == 4) {
									title = LANG.UI_PLATFORM_DES_OBS_NAME;
								}
								break;
						}
						let html = `<div class="row">
										<div class="col-md-3 mb10 task-alarm_en" id="item_name_label" style="font-weight:700">${title}:</div>
										<div class="col-md-8 pl0 task-alarm_content_en" id="item_name">${data.item_name}</div>
									</div>
									<div class="row">
										<div class="col-md-3 mb10 task-alarm_en" id="source_list_label" style="font-weight:700">${LANG.UI_ALARM_ITEM_LABEL_SOURCE_LIST}:</div>
										<div class="col-md-8 pl0 task-alarm_content_en" id="source_list">
											<textarea style="outline: none;" cols="60" rows="3">${data.source_list.replace(/;/g, '\n')}</textarea>
										</div>
									</div>`;
						$('.itemInfo_text').append(html);
					}
					$('#itemInfoTabLi').show();
				} else {
					$('#itemInfoTabLi').hide();
				}
			}
		}
		pAjaxRequest(taskAlarmParams, "/api/v1/alarm/job/item", "GET", getItemText, true);
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

	//下载任务告警日志
	let downloadTaskLog = function () {
		let data = {};
		data.id = $('#taskAlarmModalDiv #alarm_id').html();
		data = JSON.stringify(data);
		window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.ALARM + '&f=downLoadTaskLog&p=' + data;
	}

	//设置未响应告警
	let solvedAlarm = function () {
		let data = {};
		data.id = [$('#taskAlarmModalDiv #alarm_id').html()];
		data.solved_flag = false;
		Metronic.blockUI({
			target: '#taskAlarmModalDiv',
			animate: true
		});
		let setNotSolvedFlag = function (res) {
			Metronic.unblockUI('#taskAlarmModalDiv');
			if (operateResponseList(res)) {
				$('#taskAlarmModalDiv #alarm_solved_des').html(getFlagClass(false, LANG.UI_ALARM_NOT_RESPONSE));
				$('.solved_div').hide();
				selectRow.solved_flag = 2;
				selectRow.task_name = 'hiodahwod';
				selectRow.solved_flag_des = LANG.UI_ALARM_NOT_RESPONSE;
				table.bootstrapTable("updateByUniqueId", {
					id: selectRow.alarm_id,
					row: selectRow
				});
			}
		}
		pAjaxRequest(data, "/api/v1/alarm/job/response", "POST", setNotSolvedFlag, true);
	}

	//设置响应(批量)
	let solveAlarm = function () {
		let select = table.bootstrapTable('getSelections');
		let selectId = [];
		if (!select.length) {
			UIToastr.showInfo(LANG.UI_ALARM_SELECT, LANG.UI_ALARM_SELECT_TIPS);
			return false;
		}
		let data = {};
		for (let i = 0; i < select.length; i++) {
			selectId.push(select[i].alarm_id);
		}
		data.id = selectId;
		data.resolved_flag = true;
		Metronic.blockUI({
			target: '#task_alarm_div',
			animate: true
		});
		let setSolvedFlag = function (res) {
			Metronic.unblockUI('#task_alarm_div');
			if (operateResponseList(res)) {
				DataBackupCenter.updateTopAlarmTips();
				table.bootstrapTable('refresh');
			}
		}
		pAjaxRequest(data, "/api/v1/alarm/job/response", "POST", setSolvedFlag, true);
	}

	//删除
	let deleteAlarm = function () {
		let select = table.bootstrapTable('getSelections');
		if (!select.length) {
			UIToastr.showInfo(LANG.UI_ALARM_DELETE_SELECT, LANG.UI_ALARM_DELETE_SELECT_TIPS);
			return false;
		}
		bootbox.confirm({
			title: LANG.UI_ALARM_DELETE,
			message: LANG.UI_ALARM_DELETE_TIPS,
			callback: function (r) {
				if (!r) return;
				let data = {};
				let selectId = [];
				for (let i = 0; i < select.length; i++) {
					selectId.push(select[i].alarm_id);
				}
				data.id = selectId;
				Metronic.blockUI({ target: '#taskAlarm', animate: true });
				let deleteAlarmRes = function (res) {
					Metronic.unblockUI('#taskAlarm');
					if (operateResponseList(res)) {
						DataBackupCenter.updateTopAlarmTips();
						table.bootstrapTable('refresh');
					}
				};
				pAjaxRequest(data, "/api/v1/alarm/job", "DELETE", deleteAlarmRes, true);
			}
		});
	}

	//普通搜索任务名和告警等级
	let searchTaskJob = function () {
		//清除高级筛选显示内容
		// $('#task_searchDiv .searchContent').text('');
		// $('#task_searchDiv').hide();
		table.bootstrapTable('refresh');
	}

	//初始化事件
	let addListeners = function () {
		$('#task_alarm_details_tab').on('click', function (event) {
			if (event.target.tagName === 'A') { // 检查点击的元素是否是一个 <a> 标签
				// 获取父元素 li.nav-item
				let clickedItem = event.target.parentElement;
				let navType = clickedItem.getAttribute('data-type');

				if (navType === 'item-info') {
					let { module_type, alarm_id, task_uuid } = selectAlarmData;
					// 文件模块和365显示路径信息
					if ([CONF.MODULE_TYPE.FS, CONF.MODULE_TYPE.M365].includes(module_type)) {
						initItemText();
						// 文件复制显示复制来源和目的
					} else if (CONF.MODULE_TYPE.FILE_COPY == module_type) {
						initFileCopyInfoTable(task_uuid, alarm_id);
					} else {
						// 其他模块显示对象信息
						initItemTable();
					}
				}
			}
		});
		//标记响应
		$('#solveTaskAlarm').on('click', function () {
			solveAlarm();
		})

		//点击搜索图标搜索
		$('#vin_task_alarm_toolbar').on('click', '.search-btn', function () {
			table.bootstrapTable('refresh');
		})

		//删除
		$("#deleteTaskAlarm").on('click', function () {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			let user_uuid_arr = $.map(table.bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'alarm' }, () => {
				deleteAlarm();
			});
		});

		$("#taskSolvedBtn").on('click', function () {
			solvedAlarm();
		});

		$('#taskDownloadBtn').on('click', downloadTaskLog);
		//切换页数保存到cookie
		$('select[name=taskAlarm_length]').on('change', function () {
			pageLength.taskalarm = this.value;
			let data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		$('#task_searchBtn').on('click', function () {
			table.bootstrapTable('refresh');
		});

		$('.searchInput').keypress(function (e) {
			if (e.which == 13) {
				searchTaskJob();
			}
		});

		$("#taskAlarmLevelSelect").on('change', function (e) {
			let params = {
				alarm_level: this.value
			}
			addSearchContent({ ...params, ...ADVANCED_SEARCH_PARAMS });
			searchTaskJob();
		});

		//弹出高级搜索模态框
		$('#job_alarm_advanced_search_btn').on('click', function () {
			$('#job_alarm_advanced_search_modal').modal({
				'width': '800px',
				'height': '350px'
			});
			// 重置表单
			$('#advanced_search_vm_name').val('');
			$('#advanced_search_time_range').val('');
			$('#advanced_search_alarm_id').val('');
			$('#advanced_search_vm_type').val('0');
			$('#advanced_search_log_status').val('0');
			$('#advanced_search_db_type').val('0');
			$('#advanced_search_current_nodes').val('0');
			$('#advanced_search_current_job_select').val('0');
			$('#advanced_search_current_job_select').val('0');
			$('.advanced-search-vm-name').addClass('display-none');
			$('.advanced-search-vm-type').addClass('display-none');
			$('.advanced-search-db-type').addClass('display-none');
			ADVANCED_SEARCH_PARAMS = {};
			_dateRangePicker_startTime = '';
			_dateRangePicker_endTime = '';
			ADVANCED_SEARCH_MODULE_TASK = [];
			initCascader();
		});
		$('#job_alarm_advanced_search_submit').on('click', () => {
			// 获取最终的高级搜索参数
			ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, {
				start_time: _dateRangePicker_startTime,
				end_time: _dateRangePicker_endTime,
				vm_type: $('#advanced_search_vm_type').val(),
				db_type: $('#advanced_search_db_type').val(),
				node_uuid: $('#advanced_search_current_nodes').val(),
				node_name: $('#advanced_search_current_nodes option:selected').text(),
				task_alarm_id: $('#advanced_search_alarm_id').val(),
				current_job_flag: $('#advanced_search_current_job_select').val(),
			});
			table.bootstrapTable('refresh', { query: { ...ADVANCED_SEARCH_PARAMS } });
			$('#job_alarm_advanced_search_modal').modal('hide');
			addSearchContent(ADVANCED_SEARCH_PARAMS);
		});
		// 手动推送告警
		$('#task_alarmPush_btn').off().on('click', function () {
			alarmPush();
		});
		// 手动推送响应
		$('#task_responsePush_btn').off().on('click', function () {
			responsePush();
		});
	}

	//显示搜索内容
	let addSearchContent = function (p) {
		let info = "";
		if (p.start_time && p.end_time && p.start_time != '' && p.end_time != '') {
			$('#task_searchDiv .searchContent #time').remove();
			info += '<span id="time" title="' + p.start_time + "~" + p.end_time + '"> ' + LANG.UI_SEARCH_TIME_RANGE + ': <i>' + p.start_time + "~" + p.end_time + '</i><em>X</em></span>';
		}
		//虚拟化类型
		if (p.module_type && p.vm_type && p.module_type == "2" && p.vm_type != "0") {
			$('#task_searchDiv .searchContent #vm_type').remove();
			info += '<span id="vm_type" title="' + $('#job_alarm_advanced_search_modal #advanced_search_vm_type').find("option:selected").text() + '"> ' + LANG.UI_REPORT_PLATFORM + ': <i>' + $('#job_alarm_advanced_search_modal #advanced_search_vm_type').find("option:selected").text() + '</i><em>X</em></span>';
		}
		//数据库类型
		if (p.module_type && p.db_type && p.module_type == "4" && p.db_type != "0") {
			$('#task_searchDiv .searchContent #db_type').remove();
			info += '<span id="db_type" title="' + $('#job_alarm_advanced_search_modal #advanced_search_db_type').find("option:selected").text() + '"> ' + LANG.UI_DB_TYPE + ': <i>' + $('#job_alarm_advanced_search_modal #advanced_search_db_type').find("option:selected").text() + '</i><em>X</em></span>';
		}
		// 模块、任务
		if (ADVANCED_SEARCH_MODULE_TASK.length !== 0) {
			if (ADVANCED_SEARCH_MODULE_TASK[1]) {
				$('#task_searchDiv .searchContent #module_type').remove();
				info += `<span id="module_type" title="${ADVANCED_SEARCH_MODULE_TASK[1].label}">${LANG.UI_SEARCH_MODE_TYPE}: <i>${ADVANCED_SEARCH_MODULE_TASK[1].label}</i><em>X</em></span>`;
			}
			if (ADVANCED_SEARCH_MODULE_TASK[2] && !ADVANCED_SEARCH_MODULE_TASK[2].value.endsWith('-0')) {
				$('#task_searchDiv .searchContent #job_type').remove();
				info += `<span id="job_type" title="${ADVANCED_SEARCH_MODULE_TASK[2].label}">${LANG.UI_SEARCH_TASK_TYPE}: <i>${ADVANCED_SEARCH_MODULE_TASK[2].label}</i><em>X</em></span>`;
			}
		}
		// 任务名
		if (p.job_name) {
			$('#task_searchDiv .searchContent #job_name').remove();
			info += `<span id="job_name" title="${p.job_name}" style="position: relative">${LANG.UI_SEARCH_TASK_NAME}: <i>${p.job_name}</i><em>X</em></span>`;
		}
		// 告警等级
		if (p.alarm_level && p.alarm_level != "0") {
			let level_des = LANG.UI_PLATFORM_DES_WARNING;
			if (p.alarm_level == "3") {
				level_des = LANG.UI_PLATFORM_DES_ERROR;
			}
			$('#task_searchDiv .searchContent #alarm_level').remove();
			info += `<span id="alarm_level" title="${LANG.UI_SEARCH_ALARM_LEVEL}">${LANG.UI_SEARCH_ALARM_LEVEL}: <i>${level_des}</i><em>X</em></span>`;
		}
		// 节点uuid
		if (p.node_uuid && p.node_uuid != "0" && p.node_uuid != null) {
			$('#task_searchDiv .searchContent #node_uuid').remove();
			info += `<span id="node_uuid" title="${p.node_uuid}"> ${LANG.UI_SEARCH_SELET_NODE}: <i>${p.node_name}</i><em>X</em></span>`;
		}
		// 告警id
		if (p.task_alarm_id && p.task_alarm_id != '') {
			$('#task_searchDiv .searchContent #task_alarm_id').remove();
			info += `<span id="task_alarm_id" title="${p.task_alarm_id}"> ${LANG.UI_ALARM_ID}: <i>${p.task_alarm_id}</i><em>X</em></span>`;
		}
		// 当前任务
		if (p.current_job_flag && p.current_job_flag != '0') {
			$('#task_searchDiv .searchContent #current_job_flag').remove();
			info += `<span id="current_job_flag" title="${$('#advanced_search_current_job_select').find("option:selected").text()}">${LANG.UI_JOB_CURRENT_TASK}: <i>${$('#advanced_search_current_job_select').find("option:selected").text()}</i><em>X</em></span>`;
		}

		$('#task_searchDiv .searchContent').append(info);
		$('#task_searchDiv').show();
		$('#task_searchDiv .searchContent #taskName').mouseenter(function (e) {
			$('#taskNameDetail').show()
		}).mouseleave(function () {
			$('#taskNameDetail').hide()
		});
		$('#task_searchDiv .searchContent em').on('click', function () {
			$(this).parent().remove();
			let parent = $(this).parent();
			let id = parent[0].id;
			if (id == "time") {
				p.start_time = "";
				p.end_time = "";
				ADVANCED_SEARCH_PARAMS.start_time = '';
				ADVANCED_SEARCH_PARAMS.end_time = '';
			} else if (id == "module_type") {
				p.module_type = "";
				p.sub_module_type = "";
				p.vm_type = '';
				p.db_type = '';
				$('.searchContent #vm_type').remove();
				$('.searchContent #db_type').remove();
				$('#task_searchDiv .searchContent #module_type').remove();
				$('#task_searchDiv .searchContent #job_type').remove();
				ADVANCED_SEARCH_MODULE_TASK = [];
			} else if (id == "job_type") {
				p.job_type = '';
				$('#task_searchDiv .searchContent #job_type').remove();
				// 去掉数组的第二个元素
				ADVANCED_SEARCH_MODULE_TASK.splice(2, 1);
			} else if (id == 'alarm_level') {
				p[id] = "";
				$('#taskAlarmLevelSelect').val(0)
			} else {
				p[id] = "";
				ADVANCED_SEARCH_PARAMS[id] = '';
			}
			let searchContent = $('#task_searchDiv .searchContent');
			if (searchContent[0].children.length == 0) {
				$('#task_searchDiv').hide();
			}
			searchParams = p;
			table.bootstrapTable('refresh', { query: { ...searchParams } });
		});
		$('#task_searchDiv .clearSearch').on('click', function () {
			$('#task_searchDiv .searchContent').text('');
			$('#task_searchDiv').hide();
			$('#taskAlarmLevelSelect option:selected').val(0)
			searchParams = {};
			ADVANCED_SEARCH_PARAMS = {};
			table.bootstrapTable('refresh');
		});
		//如果没搜索条件，先隐藏div
		if (!info) {
			$('#task_searchDiv').hide();
		}
	}

	//初始化日期选择插件
	let initDataTimePicker = function () {
		//初始化日期时间选择控件
		$('#advanced_search_time_range').daterangepicker({
			"autoUpdateInput": false, //是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'), //默认开始时间
			"endDate": moment({
				hour: 23,
				minute: 59
			}), //默认结束时间
			"maxDate": moment({
				hour: 23,
				minute: 59
			}), //最大可用时间
			"timePicker": true, //是否显示时间,时分
			"timePicker24Hour": true, //是否是24小时制
			"alwaysShowCalendars": true, //是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE), //根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE), //根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function (start, end, label) {
		});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#advanced_search_time_range').on('apply.daterangepicker', function (ev, picker) {
			//给全局变量赋值,然后设置input
			_dateRangePicker_startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_dateRangePicker_endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_dateRangePicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#advanced_search_time_range').on('cancel.daterangepicker', function (ev, picker) {
			//清除全局变量,然后设置input
			_dateRangePicker_startTime = "";
			_dateRangePicker_endTime = "";
			_dateRangePicker_range = "";
			$(this).val('');
		});

		//input右侧的图标事件
		$('.daterangepickerdiv i').click(function () {
			$(this).parent().find('input').click();
		});
	}

	/**
	 * 手动推送任务告警
	 */
	let alarmPush = function () {
		// 从本地存储中获取 pushFlag 的值，如果不存在则默认为 false
		let pushAlarmFlag = localStorage.getItem('pushAlarmFlag') === 'true' ? true : false;
		let alarmIdArrData = JSON.parse(localStorage.getItem('alarmIdArr')) ?? [];
		if (pushAlarmFlag && alarmIdArrData.includes(alarm_Id)) {
			operateResponseList({ message: LANG.UI_PLATFORM_THIRD_MONITOR_SEND_SUCCESS }, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_TASK_ALARM);
			return;
		}
		let data = {};
		data.alarm_id = alarm_Id;
		data.alarm_type = CONF.FLAG.SET;
		data.alarm_level = alarmLevel;
		data.task_uuid = taskUuid;
		data.alarm_time = alarmTime;
		data.alarm_content = alarmContent;
		Metronic.blockUI({
			target: '#taskAlarmModalDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data, '/api/v1/system/message/push_alarm', 'POST', function (res) {
			Metronic.unblockUI('#taskAlarmModalDiv');
			if (res.success) {
				pushAlarmFlag = true;
				let alarmIdArr = JSON.parse(localStorage.getItem('alarmIdArr')) || []; // 从 localStorage 中获取 alarmIdArr，如果不存在则初始化为空数组
				alarmIdArr.push(alarm_Id);
				localStorage.setItem('alarmIdArr', JSON.stringify(alarmIdArr));
				localStorage.setItem('pushAlarmFlag', pushAlarmFlag);
				$('#task_alarmPush_btn').css({ 'color': '#28B4F0' });
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_SUCCESS);
			} else {
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_FAILED);
			}
		});
	}

	/**
	 * 手动推送响应
	 */
	let responsePush = function () {
		// 从本地存储中获取 pushResponseFlag 的值，如果不存在则默认为 false
		let pushResponseFlag = localStorage.getItem('pushResponseFlag') === 'true' ? true : false;
		let responseIdArrData = JSON.parse(localStorage.getItem('responseIdArr')) ?? [];
		if (pushResponseFlag && responseIdArrData.includes(alarm_Id)) {
			operateResponseList({ message: LANG.UI_PLATFORM_THIRD_MONITOR_SEND_SUCCESS }, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_TASK_RESPONSE);
			return;
		}
		let data = {};
		data.alarm_id = alarm_Id;
		data.alarm_type = CONF.FLAG.SET;
		data.alarm_level = alarmLevel;
		data.alarm_time = alarmTime;
		data.alarm_content = alarmContent;
		Metronic.blockUI({
			target: '#taskAlarmModalDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data, '/api/v1/system/message/push_response', 'POST', function (res) {
			Metronic.unblockUI('#taskAlarmModalDiv');
			if (res.success) {
				pushResponseFlag = true;
				let responseIdArr = JSON.parse(localStorage.getItem('responseIdArr')) || []; // 从 localStorage 中获取 responseIdArr，如果不存在则初始化为空数组
				responseIdArr.push(alarm_Id);
				localStorage.setItem('responseIdArr', JSON.stringify(responseIdArr));
				localStorage.setItem('pushResponseFlag', pushResponseFlag);
				$('#task_responsePush_btn').css({ 'color': '#28B4F0' });
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_SUCCESS);
			} else {
				operateResponseList(res, LANG.UI_PLATFORM_THIRD_MONITOR_MANUAL_PUSH_FAILED);
			}
		});
	}
	/**
	 * 初始化存储选择框
	 */
	const initStorage = function () {
		pAjaxRequest({
			'offset': 0,
			'limit': 100,
			'source_type': 1
		}, '/api/v1/storages', 'GET', function (d) {
			let data = d;
			let storageSelect = $('#advanced_search_current_storages');
			storageSelect.empty();
			let option = $("<option>").text(LANG.UI_STORAGE_ALL).val('0');
			storageSelect.append(option);
			for (let i = 0; i < data.data.rows.length; i++) {
				option = $("<option>").text(data.data.rows[i].storage_nickname).val(data.data.rows[i].storage_uuid).attr('type', data.data.rows[i].storage_type);
				storageSelect.append(option);
			}
			storageSelect.val('0');
		});
	}

	/**
	 * 处理业务类型选所有场景
	 * @param {*} businessType 
	 */
	const handleSelectedBusinessType = (businessType) => {
		switch (Number(businessType)) {
			case CONF.SYSTEM_BUSINESS_TYPE.DATA_BACKUP: // 数据备份所有的模块类型
				return { module_type: '2,5,3,4,11,14,28', sub_module_type: '', job_type: '' };
			case CONF.SYSTEM_BUSINESS_TYPE.CONTINUOUS_DATA_PROTECT: // 持续数据保护所有的模块类型
				return { module_type: '10', sub_module_type: '', job_type: '' };
			case CONF.SYSTEM_BUSINESS_TYPE.DATA_COPY: // 数据复制所有的模块类型
				return { module_type: '10,12,26', sub_module_type: '', job_type: '' };
		}
	}

	/**
	 * 处理选择的任务类型参数
	 * @param {*} taskType 
	 */
	const handleSelectedTaskType = (taskType) => {
		let moduleTaskData = taskType.value.split('-');

		switch (moduleTaskData.length) {
			case 2: // 不包含子模块的模块类型：数据备份 - 数据库、数据复制 - 数据库，数据复制 - 文件
				$('.advanced-search-vm-type').addClass('display-none');

				if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB || Number(moduleTaskData[0]) === CONF.MODULE_TYPE.DB_CDP) {
					$('.advanced-search-db-type').removeClass('display-none');
				} else {
					$('.advanced-search-db-type').addClass('display-none');
				}


				return { module_type: moduleTaskData[0], sub_module_type: '', job_type: moduleTaskData[1] };
			case 3: // 包含子模块的模块类型（但不包括持续数据保护和数据复制的整机模块）：数据备份 - 虚拟化、私有云、公有云、整机、卷、文件、NAS、Hadoop、对象存储
				$('.advanced-search-db-type').addClass('display-none');

				if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.VM) {
					$('.advanced-search-vm-name').removeClass('display-none');
					$('.advanced-search-vm-type').removeClass('display-none');
				} else {
					$('.advanced-search-vm-name').addClass('display-none');
					$('.advanced-search-vm-type').addClass('display-none');
				}

				return { module_type: moduleTaskData[0], sub_module_type: moduleTaskData[1], job_type: moduleTaskData[2] };
			case 4: // 包含子模块的模块类型（并包括持续数据保护和数据复制的整机模块）：连续数据保护 - 整机、卷，数据复制 - 整机、卷
				$('.advanced-search-vm-type').addClass('display-none');
				$('.advanced-search-db-type').addClass('display-none');

				return { module_type: moduleTaskData[0], storage_location: moduleTaskData[1], dev_type: moduleTaskData[2], job_type: moduleTaskData[3] };
			default:
				return;
		}
	}

	/**
	 * 初始化任务类型级联下拉框
	 */
	const initCascader = () => {
		let permissions = [];
		// 获取权限数组，并合并抽象出的第一层业务类型的id数组，即：备份、实时保护和复制
		if (CONF.PERMISSION.includes('global_observer')) {
			// 获取权限数组，并合并抽象出的第一层业务类型的id数组，即：备份、实时保护和复制
			permissions = CONF.GLOBAL_OBSERVER_CONFIG.concat(['timing_backup', 'data_copy', 'real_time_protect']);
		} else {
			// 获取权限数组，并合并抽象出的第一层业务类型的id数组，即：备份、实时保护和复制
			permissions = CONF.PERMISSION.concat(['timing_backup', 'data_copy', 'real_time_protect']);
		}
		let treeData = filterMenuTree(MODULE_TASK_TYPE_CASCADER_TREE_DATA, permissions);
		const cascader = new Cascader({
			container: "#current_job_advanced_search_cascader",
			data: treeData,
			placeholder: LANG.UI_CASCADER_PLACEHOLDER,
			selectFn: (val) => {
				let len = val.length;
				let taskTypeParams = {};
				ADVANCED_SEARCH_MODULE_TASK = val;
				switch (len) {
					case 2: // 模块类型选的所有
						taskTypeParams = handleSelectedBusinessType(val[1].value.split('-')[0]);
						ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, taskTypeParams);
						break;
					case 3: // 模块类型非所有
						taskTypeParams = handleSelectedTaskType(val[2]);
						ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, taskTypeParams);
						break;
					default:
						break;
				}
			}
		});
	}

	const initVMType = function () {
		pAjaxRequest({
			'offset': 0,
			'limit': 5
		}, '/api/v1/vm/platforms/hypervisors', 'GET', function (d) {
			let data = d;
			let vmSelect = $('#advanced_search_vm_type');
			vmSelect.empty();
			let option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
			vmSelect.append(option);
			for (let i = 0; i < data.data.hypervisors.length; i++) {
				option = $("<option>").text(data.data.hypervisors[i].text).val(data.data.hypervisors[i].value);
				vmSelect.append(option);
			}
			vmSelect.val('0');
		});
	}

	/**
	 * 获取数据库类型下拉列表
	 */
	const initDbType = () => {
		if (CONF.AUTH_DB_TYPE && CONF.AUTH_DB_TYPE.length > 0) {
			let authedDbTypeData = CONF.AUTH_DB_TYPE.map(item => {
				return {
					value: item,
					label: CONF.DB_TYPE_MAP[item]
				}
			});

			let dbTypeSelect = $('#advanced_search_db_type');
			dbTypeSelect.empty();

			let option = $("<option>").text(LANG.UI_DB_RECOVERY_ALL_DB_TYPE).val('0');
			dbTypeSelect.append(option);

			authedDbTypeData.forEach(item => {
				option = $("<option>").text(item.label).val(item.value);
				dbTypeSelect.append(option);
			});
			dbTypeSelect.val('0');
		}
	}
	/**
	 * 初始化节点选择框
	 */
	const initNodeSelect = function () {
		pAjaxRequest({
			'offset': 0,
			'limit': 5
		}, '/api/v1/nodes', 'GET', function (d) {
			let data = d;
			let nodeSelect = $('#advanced_search_current_nodes');
			nodeSelect.empty();
			let option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
			nodeSelect.append(option);
			for (let i = 0; i < data.data.rows.length; i++) {
				option = $("<option>").text(data.data.rows[i].ip).val(data.data.rows[i].node_uuid);
				nodeSelect.append(option);
			}
			nodeSelect.val('0');
		});
	}

	/**
	 * 初始化高级搜索表单
	 */
	const initAdvancedSearchForm = () => {
		initNodeSelect(); //初始化高级搜索节点下拉列表

		initStorage();  // 初始化高级搜索存储下拉列表

		initCascader(); // 初始化高级搜索任务类型级联下拉列表

		initVMType(); // 初始化高级搜索虚拟化类型下拉列表

		initDbType(); // 初始化高级搜索数据库类型下拉列表

	};
	//得到告警的显示类型
	const getLevelClass = function (level) {
		var levelClass = '';
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
	let initFileCopyInfoTable = function (task_uuid, alarm_id) {
		pAjaxRequest({
			'uuid': task_uuid,
			'alarm_id': alarm_id
		}, '/api/v1/filecopy/job/alarm', 'GET', function (res) {
			if (res.success) {
				var des = '';
				var copyList = '';
				var label_des = LANG.UI_HISTORY_JOB_DETAIL_COPY_DATA_LIST;
				for (var i = 0; i < res.data.copy_list.length; i++) {
					copyList += res.data.copy_list[i].source + '->' + res.data.copy_list[i].target + ';\n'
				}
				des += `<div class="row">
							<div class="col-md-2 mb10 task-alarm_en">
								<strong>` + LANG.UI_FILE_DETAIL_SRC_NAME + `：</strong>
							</div>
							<div class="col-md-9 pl0 task-alarm_content_en">` + res.data.source_name + `</div>
						</div>`;
				des += `<div class="row">
							<div class="col-md-2 mb10 task-alarm_en">
								<strong>` + LANG.UI_FILE_DETAIL_DES_NAME + `：</strong>
							</div>
							<div class="col-md-9 pl0 task-alarm_content_en">` + res.data.target_name + `</div>
						</div>`;
				des += `<div class="row">
							<div class="col-md-2 task-alarm_en">
								<strong>` + label_des + `：</strong>
							</div>
							<div class="col-md-9 pl0 task-alarm_content_en">
								<textarea style="outline: none;" cols="60" rows="3">` + copyList + `</textarea>
							</div>
						</div><br>`;
				$('.itemInfo_text').html(des);
			} else {
				$('.itemInfo_text').html(LANG.UI_PUBLIC_NOTHING);
				UIToastr.showWarning(LANG.UI_MICROSOFT365_GET_ALARM_INFO, res.message);
			}
		});
	}

	return {
		//main function to initiate the module
		init: function () {
			initDataTimePicker(); //初始化日期选择
			initTaskAlarmListTable();
			// initTaskType();
			addListeners();
			initAdvancedSearchForm();
		}

	};

}();

jQuery(document).ready(function () {
	TaskAlarm.init();
});