var SystemLog = function () {
	//时间选择器全局变量,方便提交搜索的时候直接使用
	let _dateRangePicker_startTime, _dateRangePicker_endTime;
	let table = $('#systemLogTable');
	let searchParams;
	//得到日志的显示类型
	let getLevelClass = function (level) {
		let levelClass = '';
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

	let handleRecords = function () {
		let showExportFlag = true;
		if ($.inArray('p_system_log_export', CONF.PERMISSION_ARR) == -1) {
			showExportFlag = false;
		}
		let option = {
			tableArea: '#systemLogDiv',
			toolbarId: '#system_log_toolbar',
			buttonsToolbar: '#system_log_toolbar .vin_btnToolbar',
			searchClass: 'searchSystemLog',
			placeholder: LANG.UI_USER_SEARCH,
			fullPage: true,
			searchInput: true, //搜索框
			searchSelector: '.searchSystemLog',
			vin_url: '/api/v1/logs/system',
			vin_method: 'GET',
			vin_params: function () {
				let params = {};
				if ($('#system_searchInput').val() !== '') {
					params.search = $('#system_searchInput').val();
				}
				if (searchParams) { //如果有高级搜索参数，添加accurateFlag
					params.accurate_flag = true;
				}
				$.extend(params, searchParams);
				return params;
			},
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
			sortName: 'op_time',
			sortOrder: 'desc',
			placeholder: LANG.UI_SEARCH_BY_TASK_NAME,
			showExport: showExportFlag,
			onRefresh: function () {
				table.bootstrapTable('hideLoading');
			},
			onCheck: function () {
				modifyDelStyle('systemLogTable', 'systemLogDelete');
			},
			onCheckAll: function () {
				modifyDelStyle('systemLogTable', 'systemLogDelete');
			},
			onUncheck: function () {
				modifyDelStyle('systemLogTable', 'systemLogDelete');
			},
			onUncheckAll: function () {
				modifyDelStyle('systemLogTable', 'systemLogDelete');
			},
			LoadSuccess: function () {
				$('#systemLogTable th[data-field="checkbox"]').css('width', '1%');
				$('#systemLogTable th[data-field="id"]').css('width', '3%');
				$('#systemLogTable th[data-field="description"]').css('width', '45%');
			},
			PostBody: function() {
				const exportOptions = {
                    toolbarId: 'system_log_toolbar',
                    url: '/api/v1/logs/system',
                    fileName: LANG.UI_LOG_SYSTEM_LOG
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
			},
			{
				field: 'id', //字段名
				title: LANG.UI_PUBLIC_TABLE_ID,
				sortable: false, //默认可排序，禁用排序才写此项
			},
			{
				field: 'user_name',
				title: LANG.UI_MICROSOFT365_USER,
			},
			{
				field: 'op_type',
				title: LANG.UI_LOG_OP_TYPE,
				sortable: false, //默认可排序，禁用排序才写此项
				formatter: function (index, row) {
					return row.op_type_des;
				}
			},
			{
				field: 'op_time',
				title: LANG.UI_LOG_TIME,
			},
			{
				field: 'log_level',
				title: LANG.UI_PUBLIC_STATUS,
				formatter: function (index, row) {
					let labelClass = getLevelClass(row.log_level);
					let content = '<span class="label label-sm ' + labelClass + '">' + row.log_level_des + '</span>';
					return content;
				}
			},
			{
				field: 'description',
				title: LANG.UI_PUBLIC_DESCRIPTION,
				formatter: function (value, row) {
					// 其他正常语言包就剔除span标签加入title
					const newRegex = /<span[^>]*>|<\/span>/gi;
					let title = value.replace(newRegex, '');
					return `<span title = "${title}">${value}</span>`; //避免走入插件拼接title，导致显示不全
				}
			},
			]
		}
		//如果是GMP 屏蔽了删除按钮 那么这个勾选框就没用了 需要一并删除
		if (CONF.ENTERPRISE == "vdms_enterprise") {
			option.columns.shift();
		}


		table.baseTableConfig().init(option);
	}

	let deleteLog = function () {
		let select = table.bootstrapTable('getSelections');
		if (!select.length) {
			return UIToastr.showInfo(LANG.UI_LOG_SELECT, LANG.UI_LOG_SELECT_TIPS);
		}
		bootbox.confirm({
			title: LANG.UI_LOG_DELETE,
			message: LANG.UI_LOG_DELETE_TIPS,
			callback: function (r) {
				if (!r) return;
				let selectId = [];
				for (let i = 0; i < select.length; i++) {
					selectId.push(select[i].log_id);
				}
				Metronic.blockUI({ target: '#systemLogTable', animate: true });
				pAjaxRequest({ id: selectId }, '/api/v1/logs/system', 'DELETE', (res) => {
					Metronic.unblockUI('#systemLogTable');
					if (operateResponseList(res)) {
						table.bootstrapTable('refresh');
					}
				});
			}
		});
	}

	let searchSystemLog = function () {
		//清除高级筛选显示内容
		$('#system_searchDiv .searchContent').text('');
		$('#system_searchDiv').hide();
		table.bootstrapTable('refresh');
	}

	let addListeners = function () {
        $('#logs_nav').on('click', function(event) {
            if (event.target.tagName === 'A') { // 检查点击的元素是否是一个 <a> 标签
                // 获取父元素 li.nav-item
                let clickedItem = event.target.parentElement;
                let navType = clickedItem.getAttribute('data-type');
                if (navType === 'system_log') {
					handleRecords();
                }
            }
        });
		//点击搜索图标搜索
		$('#system_log_toolbar .search-btn').on('click', function () {
			table.bootstrapTable('refresh');
		})

		$("#systemLogDelete").on('click', function () {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			let user_uuid_arr = $.map(table.bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'log' }, () => {
				deleteLog();
			});
		});

		//切换页数保存到cookie
		$('select[name=systemLog_length]').on('change', function () {
			pageLength.systemlog = this.value;
			let data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		
		$(`.systemLogclear`).on('click', (e) => {
			$('#system_searchInput').val('');
			searchSystemLog();
		});

		$('#system_searchInput').keypress(function (e) {
			if (e.which == 13) {
				searchSystemLog();
			}
		});

		//弹出高级搜索模态框
		$('#system_searchAll').on('click', function () {
			$('#system_searchModal').modal({ 'width': '800px', 'height': '350px' });
		});

		//高级搜索发送请求到服务端
		$('#system_search_submit').on('click', function () {
			$('#system_searchInput').val('');
			// 进行xss 过滤
			let reg = /\~|\！|\!|\@|\#|\$|\^|\￥|\%|\…|\&|\*|\(|\)|\—|\+|\{|\}|\“|\”|\《|\》|\?|\？|\<|\>|\'|\"/g;
			$('#system_searchModal #system_user').val($('#system_searchModal #system_user').val().replace(reg, ""));
			let p = {};
			p.start_time = _dateRangePicker_startTime; //日志时间查询范围开头
			p.end_time = _dateRangePicker_endTime;		//日志始时间查询范围结尾
			p.log_status = $('#system_searchModal #logStatus').val();
			p.op_type = $('#system_searchModal #opType').val();
			p.create_user = $('#system_searchModal #system_user').val();
			searchParams = p;
			//添加搜索条件显示
			addSearchContent(p);
			$('#system_searchModal').modal('hide');
			table.bootstrapTable('refresh');
		});
	}

	//显示搜索内容
	let addSearchContent = function (p) {
		let info = "";
		$('#system_searchDiv .searchContent').text('');
		let createUser = xssEncode($('#system_searchModal #user').val());
		if (p.start_time && p.end_time) {
			info += '<span id="time" title="' + p.start_time + "~" + p.end_time + '"> ' + LANG.UI_SEARCH_TIME_RANGE + ': <i>' + p.start_time + "~" + p.end_time + '</i><em>X</em></span>';
		}
		if (p.log_status != "0") {
			info += '<span id="log_status" title="' + $('#system_searchModal #logStatus').find("option:selected").text() + '"> ' + LANG.UI_SEARCH_LOG_STATUS + ': <i>' + $('#system_searchModal #logStatus').find("option:selected").text() + '</i><em>X</em></span>';
		}

		if (p.op_type != "") {
			info += '<span id="op_type" title="' + $('#system_searchModal #opType').find("option:selected").text() + '"> ' + LANG.UI_LOG_OP_TYPE + ': <i>' + $('#system_searchModal #opType').find("option:selected").text() + '</i><em>X</em></span>';
		}

		if (p.create_user) {
			info += '<span id="create_user" title="' + p.create_user + '"> ' + LANG.UI_STORAGE_DETAIL_USERNAME + ': <i>' + p.create_user + '</i><em>X</em></span>';
		}

		//判断搜索条件是一行或两行
		if ($('#system_searchDiv').height() > 32) {
			$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-148px');
		} else if ($('#system_searchDiv').height() > 0) {
			$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-116px');
		} else {
			$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
		}

		$('#system_searchDiv .searchContent').append(info);
		$('#system_searchDiv').show();

		$('#system_searchDiv .searchContent em').on('click', function () {
			$(this).parent().remove();
			let searchContent = $('#system_searchDiv .searchContent');
			if (searchContent[0].children.length == 0) {
				$('#system_searchDiv').hide();
				$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
			}
			if ($('#system_searchDiv').height() > 32) {
				$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-148px');
			} else if ($('#system_searchDiv').height() > 0 && searchContent[0].children.length > 0) {
				$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-116px');
			} else {
				$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
			}
			let parent = $(this).parent();
			let id = parent[0].id;
			if (id == "time") {
				p.start_time = "";
				p.end_time = "";
			} else {
				p[id] = "";
			}
			searchParams = p;
			table.bootstrapTable('refresh');
		});
		$('#system_searchDiv .clearSearch').on('click', function () {
			$('#system_searchDiv .searchContent').text('');
			$('#system_searchDiv').hide();
			searchParams = {};
			table.bootstrapTable('refresh');
			$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
			$('#advanced_search_time_range_system').val('');
		});
		//如果没搜索条件，先隐藏div
		if (!info) {
			$('#system_searchDiv').hide();
			$('#systemLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
		}
	}

	//初始化日期选择插件
	let initDataTimePicker = function () {
		//初始化日期时间选择控件
		$('#advanced_search_time_range_system').daterangepicker({
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
		$('#advanced_search_time_range_system').on('apply.daterangepicker', function (ev, picker) {
			//给全局变量赋值,然后设置input
			_dateRangePicker_startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_dateRangePicker_endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#advanced_search_time_range_system').on('cancel.daterangepicker', function (ev, picker) {
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

	return {
		//main function to initiate the module
		init: function () {
			initDataTimePicker(); //初始化日期选择
			addListeners();
		}

	};

}();

jQuery(document).ready(function () {
	SystemLog.init();
});