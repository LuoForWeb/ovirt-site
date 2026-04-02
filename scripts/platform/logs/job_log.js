var JobLog = function () {

	let searchParams;
	let table = $('#taskLog');
	//时间选择器全局变量,方便提交搜索的时候直接使用
	let _dateRangePicker_startTime, _dateRangePicker_endTime;
    let ADVANCED_SEARCH_PARAMS = {}, ADVANCED_SEARCH_MODULE_TASK = []; // 高级搜索查询参数
	//得到日志的显示类型
	let getLevelClass = function(level){
		let levelClass = '';
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

	let searchTaskLog = function(){
		//清除高级筛选显示内容
		$('#job_searchDiv .searchContent').text('');
		$('#job_searchDiv').hide();
		table.bootstrapTable('refresh');
	}

	//初始化事件
	let addListeners = function(){
		//点击搜索图标搜索
		$('#job_log_toolbar').on('click', '.search-btn', function () {
            table.bootstrapTable('refresh');
        })

		$("#jobLogDelete").on('click', function(){
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			let user_uuid_arr = $.map(table.bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'log' }, () => {
				deleteLog();
			});
		});

		//切换页数保存到cookie
		$('select[name=taskLog_length]').on('change', function(){
			pageLength.tasklog = this.value;
			let data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});

		$('#job_searchBtn').on('click',searchTaskLog);


		$('#task_searchInput').keypress(function (e) {
			if (e.which == 13) {
				searchTaskLog();
			}
		});

		//弹出高级搜索模态框
		$('#job_log_advanced_search_btn').on('click', function(){
			$('#job_log_advanced_search_modal').modal({'width':'800px', 'height':'350px'});
            // 重置表单
            $('#advanced_search_task_name').val('');
            $('#advanced_search_user_name').val('');
            $('#advanced_search_host_name').val('');
            $('#advanced_search_vm_name').val('');
            $('#advanced_search_time_range').val('');
            $('#advanced_search_vm_type').val('0');
            $('#advanced_search_log_status').val('0');
            $('#advanced_search_db_type').val('0');
            $('.advanced-search-vm-name').addClass('display-none');
            $('.advanced-search-vm-type').addClass('display-none');
            $('.advanced-search-db-type').addClass('display-none');
            ADVANCED_SEARCH_PARAMS = {};
			_dateRangePicker_startTime = '';
			_dateRangePicker_endTime = '';
			ADVANCED_SEARCH_MODULE_TASK = [];
            initCascader();
		});

        $('#job_log_advanced_search_submit').on('click', () => {
            // 获取最终的高级搜索参数
            ADVANCED_SEARCH_PARAMS = Object.assign(ADVANCED_SEARCH_PARAMS, {
                job_name: $('#advanced_search_task_name').val(),
                user_name: $('#advanced_search_user_name').val(),
                log_status: $('#advanced_search_log_status').val(),
                start_time: _dateRangePicker_startTime,
                end_time: _dateRangePicker_endTime,
                vm_type: $('#advanced_search_vm_type').val(),
                db_type: $('#advanced_search_db_type').val(),
            });
            table.bootstrapTable('refresh', { query: { ...ADVANCED_SEARCH_PARAMS } });
            $('#job_log_advanced_search_modal').modal('hide');
			addSearchContent(ADVANCED_SEARCH_PARAMS);
        });
	}

	//显示搜索内容
	let addSearchContent = function(p){
		let info = "";
		$('.searchContent').html('');
		if(p.start_time && p.end_time){
			info += `<span id="time" title="${p.start_time}~${p.end_time}">${LANG.UI_SEARCH_TIME_RANGE}: <i>${p.start_time }~${p.end_time}</i><em>X</em></span>`;
		}
		//虚拟化类型
		if (p.module_type == "2" && p.vm_type != "0") {
			info += '<span id="vm_type" title="' + $('#job_alarm_advanced_search_modal #advanced_search_vm_type').find("option:selected").text() + '"> ' + LANG.UI_REPORT_PLATFORM + ': <i>' + $('#job_alarm_advanced_search_modal #advanced_search_vm_type').find("option:selected").text() + '</i><em>X</em></span>';
		}
		//数据库类型
		if (p.module_type == "4" && p.db_type != "0") {
			info += '<span id="db_type" title="' + $('#job_alarm_advanced_search_modal #advanced_search_db_type').find("option:selected").text() + '"> ' + LANG.UI_DB_TYPE + ': <i>' + $('#job_alarm_advanced_search_modal #advanced_search_db_type').find("option:selected").text() + '</i><em>X</em></span>';
		}
		// 模块、任务
		if(ADVANCED_SEARCH_MODULE_TASK.length !== 0){
			if(ADVANCED_SEARCH_MODULE_TASK[1]){
				info += `<span id="module_type" title="${ADVANCED_SEARCH_MODULE_TASK[1].label}">${LANG.UI_SEARCH_OBJ_TYPE}: <i>${ADVANCED_SEARCH_MODULE_TASK[1].label}</i><em>X</em></span>`;

			}
			if(ADVANCED_SEARCH_MODULE_TASK[2] && !ADVANCED_SEARCH_MODULE_TASK[2].value.endsWith('-0')){
				info += `<span id="job_type" title="${ADVANCED_SEARCH_MODULE_TASK[2].label}">${LANG.UI_SEARCH_TASK_TYPE}: <i>${ADVANCED_SEARCH_MODULE_TASK[2].label}</i><em>X</em></span>`;
			}
		}
		// 任务名
		if (p.job_name) {
			info += `<span id="job_name" title="${p.job_name}" style="position: relative">${LANG.UI_SEARCH_TASK_NAME}: <i>${p.job_name}</i><em>X</em></span>`;
		}
		if(p.user_name){
			info += `<span id="user_name" title="${p.user_name}" style="position: relative">${LANG.UI_STORAGE_DETAIL_USERNAME}: <i>${p.user_name}</i><em>X</em></span>`;
		}
		// 日志状态
		if(p.log_status != "0"){
			info += `<span id="log_status" title="${$('#job_log_advanced_search_modal #advanced_search_log_status').find('option:selected').text()}" style="position: relative">${LANG.UI_PUBLIC_STATUS}: <i>${$('#job_log_advanced_search_modal #advanced_search_log_status').find('option:selected').text()}</i><em>X</em></span>`;
		}

		$('#job_searchDiv .searchContent').append(info);
		$('#job_searchDiv').show();

		$('#job_searchDiv .searchContent #taskName').mouseenter(function(e){
			$('#taskNameDetail').show()
		}).mouseleave(function(){
			$('#taskNameDetail').hide()
		});

		//判断搜索条件是一行或两行
		if ($('#job_searchDiv').height() > 32) {
			$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-148px');
		} else if ($('#job_searchDiv').height() > 0){
			$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-116px');
		}else{
			$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
		}

		$('#job_searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			let searchContent = $('#job_searchDiv .searchContent');

			if ($('#job_searchDiv').height() > 32) {
				$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-148px');
			} else if ($('#job_searchDiv').height() > 0 && searchContent[0].children.length > 0){
				$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-116px');
			}else{
				$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
			}
			let parent  = $(this).parent();
			let id = parent[0].id;
			if(id == "time"){
				p.start_time = "";
				p.end_time = "";
			} else if (id == "moduleType") {
				p.module_type = "";
				p.sub_module_type = "";
				$('.searchContent #hypervisor').remove();
				$('.searchContent #dbtype').remove();
			} else{
				p[id] = "";
			}
			if(searchContent[0].children.length == 0){
				$('#job_searchDiv').hide();
				$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
			}
			searchParams = p;
			table.bootstrapTable('refresh', {query:{ ...searchParams }});
		});
		$('#job_searchDiv .clearSearch').on('click',function(){
			$('#job_searchDiv .searchContent').text('');
			$('#job_searchDiv').hide();
			searchParams = {};
			ADVANCED_SEARCH_PARAMS = {};
			table.bootstrapTable('refresh');
			$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#job_searchDiv').hide();
			$('#jobLogDiv .dataTables_wrapper .dt-buttons').css('margin-top', '-75px');
		}
	}

	let handleRecords = function () {
		let showExportFlag = true;
		console.log(111,$.inArray('p_job_log_export', CONF.PERMISSION_ARR));
		if ($.inArray('p_job_log_export', CONF.PERMISSION_ARR) == -1) {
			showExportFlag = false;
		}
		let option = {
			tableArea: '#taskLogDiv',
			toolbarId: '#job_log_toolbar',
			buttonsToolbar: '#job_log_toolbar .vin_btnToolbar',
			searchClass: 'job-log-search',
			placeholder: `<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>`,
			fullPage: true,
			searchDiv: '#job_searchDiv',
			vin_url: '/api/v1/logs/job',
			vin_method: 'GET',
			vin_params: function () {
				let params = {};
				if ($('#task_searchInput').val() !== '') {
					params.search = $('#task_searchInput').val();
				}
				if (searchParams) {
					params.accurate_flag = true;
				}

				$.extend(params, searchParams)
				return {...params, ...ADVANCED_SEARCH_PARAMS};
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
				modifyDelStyle('taskLog', 'jobLogDelete');
			},
			onCheckAll: function () {
				modifyDelStyle('taskLog', 'jobLogDelete');
			},
			onUncheck: function () {
				modifyDelStyle('taskLog', 'jobLogDelete');
			},
			onUncheckAll: function () {
				modifyDelStyle('taskLog', 'jobLogDelete');
			},
			LoadSuccess: function () {
				$('#taskLog th[data-field="checkbox"]').css('width', '1%');
				$('#taskLog th[data-field="id"]').css('width', '3%');
				$('#taskLog th[data-field="num"]').css('width', '3%');
				$('#taskLog th[data-field="module_type"]').css('width', '5%');
				$('#taskLog th[data-field="job_type_value"]').css('width', '5%');
				$('#taskLog th[data-field="user_name"]').css('width', '5%');
				$('#taskLog th[data-field="job_name"]').css('width', '15%');
				$('#taskLog th[data-field="op_time"]').css('width', '10%');
				$('#taskLog th[data-field="log_level"]').css('width', '5%');
				$('#taskLog th[data-field="log_description"]').css('width', '25%');
			},
			PostBody: function() {
				const exportOptions = {
                    toolbarId: 'job_log_toolbar',
                    url: '/api/v1/logs/job',
                    fileName: LANG.UI_LOG_LABEL_TASK_LOG
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
					field: 'num', //字段名
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'job_name',
					title: LANG.UI_SEARCH_TASK_NAME,
				},

				{
					field: 'module_type',
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
					field: 'user_name',
					title: LANG.UI_MICROSOFT365_USER,
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
					field: 'log_description',
					title: LANG.UI_PUBLIC_DESCRIPTION,
					formatter: function (index, row) {
						return row.log_description; //避免走入插件拼接title，导致显示不全
					}
				},
			]
		}
		//如果是GMP 屏蔽了删除按钮 那么这个勾选框就没用了 需要一并删除
		if(CONF.ENTERPRISE == "vdms_enterprise"){
			option.columns.shift();
		}

		table.baseTableConfig().init(option);
	}

	let deleteLog = function(){
		let select = table.bootstrapTable('getSelections');
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_LOG_SELECT, LANG.UI_LOG_SELECT_TIPS);
		}
		bootbox.confirm({
			title: LANG.UI_LOG_DELETE,
			message: LANG.UI_LOG_DELETE_TIPS,
			callback: function(r) {
				if(!r) return;
				let selectId = [];
				for (let i = 0; i < select.length; i++) {
					selectId.push(select[i].id);
				}
				pAjaxRequest({id:selectId}, "/api/v1/logs/job", "DELETE", function(res){
					operateResponseList(res);
					table.bootstrapTable('refresh');
				}, true);
			}
		});
	}

	//初始化日期选择插件
	let initDataTimePicker = function() {
		//初始化日期时间选择控件
		$('#advanced_search_time_range').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),												//默认结束时间
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,										//是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function (start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#advanced_search_time_range').on('apply.daterangepicker', function (ev, picker) {
			//给全局变量赋值,然后设置input
			_dateRangePicker_startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_dateRangePicker_endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#advanced_search_time_range').on('cancel.daterangepicker', function (ev, picker) {
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


                return {  module_type: moduleTaskData[0], sub_module_type: '',  job_type: moduleTaskData[1] };
            case 3: // 包含子模块的模块类型（但不包括持续数据保护和数据复制的整机模块）：数据备份 - 虚拟化、私有云、公有云、整机、卷、文件、NAS、Hadoop、对象存储
                $('.advanced-search-db-type').addClass('display-none');

                if (Number(moduleTaskData[0]) === CONF.MODULE_TYPE.VM) {
                    $('.advanced-search-vm-name').removeClass('display-none');
                    $('.advanced-search-vm-type').removeClass('display-none');
                } else {
                    $('.advanced-search-vm-name').addClass('display-none');
                    $('.advanced-search-vm-type').addClass('display-none');
                }

                return {  module_type: moduleTaskData[0], sub_module_type: moduleTaskData[1],  job_type: moduleTaskData[2] };
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
     * 初始化高级搜索表单
     */
    const initAdvancedSearchForm = () => {

        initCascader(); // 初始化高级搜索任务类型级联下拉列表

        initVMType(); // 初始化高级搜索虚拟化类型下拉列表

        initDbType(); // 初始化高级搜索数据库类型下拉列表

    };
	return {
		//main function to initiate the module
		init: function () {
			initDataTimePicker(); //初始化日期选择
			handleRecords();
			addListeners();
			initAdvancedSearchForm();
		}

	};

}();

jQuery(document).ready(function() {
	JobLog.init();
});