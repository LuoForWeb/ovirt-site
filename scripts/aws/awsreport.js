var VmReport = function () {
	var sub_module_type = 3;
	var grid = $('#vm_table');
	//时间选择器全局变量,方便提交搜索的时候直接使用
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	
	//添加到现有备份任务
	var addToBackupTask = function (row) {
		$('#modaltips').hide();
		var params = {};
		params.platform_uuid = row.details.platform_uuid;
		$('#vmname').html(row.vm_name);
		$('#vmuuid').val(row.details.vm_uuid);
		$('#vcenteruuid').val(row.details.platform_uuid);
		$('#vmshowtype').val(row.details.display_mode);
		$('.vmnameDiv').show();
		pAjaxRequest(params, "/api/v1/vm/platforms/current_jobs", "GET", function (data) {
			var task = data.data.rows;
			var select = $('#task');
			if (task.length == 0) {
				select.empty();
				$('#modaltips').show();
				return;
			}
			var option = "";
			for (var i = 0; i < task.length; i++) {
				option += '<option value="' + task[i].uuid + '">' + task[i].name + '</option>';
			}
			select.empty();
			select.append(option);
		}, false);
		$('#modaldiv').modal();
	}

	//添加到现有备份任务
	var addMultToBackupTask = function () {
		$('#modaltips').hide();
		var selectedRow = grid.bootstrapTable('getSelections');
		if (selectedRow.length == 0) {
			return UIToastr.showInfo(LANG.UI_VCENTER_VM_ADD_TO_TASK, LANG.UI_OVERVIEW_PUBLIC_CLOUD_NO_ADD_TO_JOB_INSTANCE_TIPS);
		}
		var vcenteruuid;
		var vmshowtype;
		var vmnamelist = [];
		var addbackup_vmuuids = [];
		for (var i = 0; i < selectedRow.length; i++) {
			if (vcenteruuid && selectedRow[i].details.platform_uuid != vcenteruuid) {
				return UIToastr.showWarning(LANG.UI_VCENTER_VM_ADD_TO_TASK, LANG.UI_OVERVIEW_PUBLIC_CLOUD_NOT_SAME_PLATFORM_TIPS);
			}

			if (!vcenteruuid) {
				vcenteruuid = selectedRow[i].details.platform_uuid;
				vmshowtype = selectedRow[i].details.display_mode;
			}
			vmnamelist.push(selectedRow[i].vm_name);
			addbackup_vmuuids.push(selectedRow[i].details.vm_uuid);
		}
		var params = {};
		params.platform_uuid = vcenteruuid;
		Metronic.blockUI({target: '#modaldiv',animate: true});
		pAjaxRequest(params, "/api/v1/vm/platforms/current_jobs", "GET", function (data) {
			Metronic.unblockUI('#modaldiv');
			var task = data.data.rows;
			var select = $('#task');
			if (task.length == 0) {
				select.empty();
				$('#modaltips').show();
				return;
			}
			var option = "";
			for (var i = 0; i < task.length; i++) {
				option += '<option value="' + task[i].uuid + '">' + task[i].name + '</option>';
			}
			select.empty();
			select.append(option);
		}, false);
		var vmnamestr = vmnamelist.join(',');
		var vmuuidstr = addbackup_vmuuids.join(',');
		$('#modaldiv').modal();
		$('#vmname').html(vmnamestr);
		$('.vmnameDiv').hide();
		$('#vmuuid').val(vmuuidstr);
		$('#vcenteruuid').val(vcenteruuid);
		$('#vmshowtype').val(vmshowtype);
	}

	//单个创建新的备份任务
	var createNewBackupTask = function (row) {
		var vmuuid = row.details.vm_uuid;
		var vcenteruuid = row.details.platform_uuid;
		var hypervisor = row.details.hypervisor_type;
		let url = './content/aws/awsbackup.php?vmuuid=' + vmuuid + '&vcenteruuid=' + vcenteruuid + '&hypervisor=' + hypervisor;
		LOCATION(url, 'awsbackup');
	}
	
	//多个创建新的备份任务
	var createMultNewBackupTask = function(){
		var selectedRow = grid.bootstrapTable('getSelections');
		if (selectedRow.length == 0) {
			return UIToastr.showInfo(LANG.UI_VM_REPORT_CREATE_NEW_TASK, LANG.UI_OVERVIEW_PUBLIC_CLOUD_NO_CREATE_JOB_INSTANCE_TIPS);
		}
		var vmuuid = [];
		var vcenteruuid;
		var vmshowtype;
		var hypervisor;
		for (var i = 0; i < selectedRow.length; i++) {
			if (vcenteruuid && selectedRow[i].details.platform_uuid != vcenteruuid) {
				return UIToastr.showWarning(LANG.UI_VM_REPORT_CREATE_NEW_TASK, LANG.UI_OVERVIEW_PUBLIC_CLOUD_NOT_SAME_PLATFORM_TIPS);
			}
			if (!vcenteruuid) {
				vcenteruuid = selectedRow[i].details.platform_uuid;
				vmshowtype = selectedRow[i].details.display_mode;
				hypervisor = selectedRow[i].details.hypervisor_type;
			}
			vmuuid.push(selectedRow[i].details.vm_uuid);
		}
		let url = './content/aws/awsbackup.php?vmuuid=' + vmuuid + '&vcenteruuid=' + vcenteruuid + '&hypervisor=' + hypervisor;
		LOCATION(url, 'awsbackup');
	}
	
    //添加到任务
    var addTotaskOpt = function(){
    	var vmname = $('#vmname').html();
    	var taskuuid = $('#task').val();
    	var vmuuid = $('#vmuuid').val();
    	var vcenteruuid = $('#vcenteruuid').val();
    	var displaymode = parseInt($('#vmshowtype').val());
    	if($.trim(vmname) == '' || taskuuid == null){
    		$('#modaltips').show();
    		return;
    	}
    	$('#modaltips').hide();
		Metronic.blockUI({target: '#modaldiv',animate: true});
		let p = {};
		p.platform_uuid = vcenteruuid;
		p.vm_uuid = vmuuid; //虚拟机uuid，逗号分隔
		p.display_mode = displaymode;
		pAjaxRequest(p, "/api/v1/vm/jobs/" + taskuuid + "/vms", "POST", function (data) {
			Metronic.unblockUI('#modaldiv');
			if (operateResponseList(data)) {
				grid.bootstrapTable('refresh');
				$('#modaldiv').modal('hide');
			}
		}, false);
    }

	var handleRecords = function () {
		//读取cookie里的保存列表状态
		let length = "";
		if ($.cookie('pageLength')) {
			var pageList = JSON.parse($.cookie('pageLength'));
			if (pageList.vmreport) {
				length = pageList.vmreport;
			}
		}

		// 获取总条数
		let maxLength = 0;
		pAjaxRequest({sub_module_type: sub_module_type}, "/api/v1/vm/overview/count", "GET", function (d) {
			maxLength = d.data.vm_length;
		}, false);

		let options = {
			vin_url: '/api/v1/vm/overview/list',
			vin_method: 'GET',
			queryParamsType: 'limit',
			queryParams: function (p) {
				return {
					limit: length ? length : p.limit,
					offset: p.offset,
					order: p.order,
					sort: p.sort,
					sub_module_type: sub_module_type,
					accurate_flag: false,
					search_value: $.trim($('#search').val())
				};
			},
			pagination: true,
			sidePagination: 'server',
			pageNumber: 1,
			pageSize: 20,
			pageList: [10, 20, 50, 100, 150, 200, maxLength],
			paginationLoop: false,
			uniqueId: 'vm_uuid',
			lineHeight: '60px',
			sortable: true,
			sortName: 'vm_name',
			sortOrder: 'asc',
			resizable: true,
			showColumns: false,
			buttonsToolbar: '.vm_report_toolbar',
			showExport: true,// 显示导出按钮
			exportDataType: "basic",// 导出类型
			exportTypes: ['excel', 'csv'],// 导出文件类型
			exportOptions: {
				fileName: LANG.UI_OVERVIEW_PUBLIC_CLOUD_EXPORT_FILE_NAME,
				ignoreColumn: ['operations'] , // 排除操作列
			},
			onRefresh: function (params) {
				$("#vm_table").bootstrapTable('hideLoading');
			},
			onCheck: function (res) {
				var selectedRow = grid.bootstrapTable("getSelections");
				$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
				if (selectedRow.length == 0) {
					$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('');
				} else if (selectedRow.length > 0) {
					$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
				}
			},
			onUncheck: function (row, $element) {
				var selectedRow = grid.bootstrapTable("getSelections");
				if (selectedRow.length == 0) {
					$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
					$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('');
				} else if (selectedRow.length > 0) {
					$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
				}
			},
			onCheckAll: function () {
				var selectedRow = grid.bootstrapTable("getSelections");
				$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checked.svg')");
				$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
			},
			onUncheckAll: function () {
				var selectedRow = grid.bootstrapTable("getSelections");
				$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
			},

			columns: [
				{
					field: 'checkbox',
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (value, data) {
						//已在备份任务则禁用
						return {disabled: data.backup_flag};
					}
				},
				{
					field: 'no',
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: false,
				},
				{
					field: 'vm_name',
					title: LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME,
					formatter: function (value, data) {
						if (data.backup_flag) {
							return '<a title="' + data.dir_path + '" class="colorgreen"><i class="fa fa-check"></i> ' + value + '</a>';
						}
						return '<a title="' + data.dir_path + '" class="colorgreen">' + value + '</a>';
					}
				},
				{
					field: 'vm_ip',
					title: LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_IP,
				},
				{
					field: 'platform_ip',
					title: LANG.UI_OVERVIEW_PUBLIC_CLOUD_PLATFORM,
				},
				{
					field: 'backup_flag',
					title: LANG.UI_SEARCH_BACKUP_STATUS,
					formatter: function (value, data) {
						if (data.backup_flag) {
							return '<span class="colorgreen"><i class="fa fa-check"></i> ' + LANG.UI_VM_REPORT_IN_BACKUP + '</span>';
						}
						return LANG.UI_VM_REPORT_NOIN_BACKUP;
					}
				},
				{
					field: 'last_backup_time',
					title: LANG.UI_VM_REPORT_LAST_BACKUP_TIME,
				},
				{
					field: 'backup_job',
					title: LANG.UI_SEARCH_BACKUP_TASK,
				},
				{
					field: 'backup_count',
					title: LANG.UI_STORAGE_TIMEPOINT_NUM,
				},
				{
					field: 'backup_storage',
					title: LANG.UI_VM_REPORT_STORAGE_SPACE,
				},
				{
					field: 'user_name',
					title: LANG.UI_VM_REPORT_USERNAME,
				},
				{
					field: 'operations',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					clickToSelect: false,
					events: {
						'click .backup': function (event, value, row, index) {
							addToBackupTask(row);
						},
						'click .newbackup': function (event, value, row, index) {
							createNewBackupTask(row);
						},
					},
					formatter: function (value, data, index) {
						if (!value.length) {
							return '';
						}
						let button = '<div class="btn-group positionabs" style="margin-top: -13px">';
						if (index > 4) {
							button = '<div class="btn-group positionabs dropup" style="margin-top: -13px">';
						}
						button += '<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" ' +
							'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
							'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
							'</button>' +
							'<ul class="dropdown-menu min-width100" role="menu">';
						$.each(data.operations, function (i, d) {
							switch(d){
								case 4:
									button += '<li class="backup"><a href="javascript:;"><i class="viconfont vicon-ge_add_task"></i> ' + LANG.UI_VCENTER_VM_ADD_TO_TASK + '</a></li>';
									break;
								case 5:
									button += '<li class="newbackup"><a href="javascript:;"><i class="viconfont vicon-backup"></i> ' + LANG.UI_VM_REPORT_CREATE_NEW_TASK + '</a></li>';
									break;
							}
						});
						button += '</ul></div>';
						return button;
					},
				}
			]
		};
		grid.baseTableConfig().init(options);
		$('#vm_table').show();
	}
	
	var searchVM = function(){
		grid.bootstrapTable('refreshOptions', {
			queryParams: function (queryParams) {
				return {
					limit: queryParams.limit,
					offset: queryParams.offset,
					order: queryParams.order,
					sort: queryParams.sort,
					sub_module_type: sub_module_type,
					accurate_flag: false,
					search_value: $.trim($('#search').val())
				};
			}
		});
	}
	
	//初始化事件
	var addListeners = function(){
		//多选虚拟机添加到备份任务
		$('#addtojob').unbind().on('click', addMultToBackupTask);
		$('#createjob').unbind().on('click', createMultNewBackupTask);


		//切换页数保存到cookie
		$('.page-list .dropdown-menu li').on('change', function(){
			debugger;
			let pageLength = {};
			pageLength.vmreport = $(this).find(a).text();
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		
		$('#submit').on('click', addTotaskOpt);
		$('#searchbtn').on('click', searchVM);
		// $('#search').on('change', setParam);
		$('#search').on('blur', function () {
			$(this).prop('placeholder', LANG.UI_OVERVIEW_PUBLIC_CLOUD_SEARCH_TIPS);
		});
		$('#search').keypress(function (e) {
            if (e.which == 13) {
            	searchVM();
            }
        });
		$('#clearSearchBtn').on('click', function () {
			$('#search').val('');
			$("#vm_table").bootstrapTable("refresh");
		});
		
		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'500px'});
		});
		
		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			$('#search').val('');
			var p = {};
			p.startTime = _daterangepicker_starttime; //时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//时间查询范围结尾
			p.hypervisor = $('#searchmodal #vmtype').val();
			p.backupFlag = $('#searchmodal #backupFlag').val();

			p.taskName = $('#searchmodal #taskName').val();
			p.vmIP = $('#searchmodal #vmIP').val();
			p.vmName = $('#searchmodal #vmName').val();
			p.vcenteruuid = $('#searchmodal #vcenterSelect').val();
			p.hostName = $('#searchmodal #region').val();
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort,
						sub_module_type: sub_module_type,
						accurate_flag: true,
						hypervisor_type: p.hypervisor,
						backup_status: p.backupFlag,
						platform_uuid: (0 == p.vcenteruuid) ? '' : p.vcenteruuid,
						vm_name: p.vmName,
						vm_ip: p.vmIP,
						host_name: p.hostName, // 区域
						job_name: p.taskName,
						start_time: p.startTime,
						end_time: p.endTime
					};
				}
			});
		});


	}
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		var vmName = xssEncode($('#vmName').val());
		var vmIP = xssEncode($('#vmIP').val());
		var taskName = xssEncode($('#taskName').val());
		var hostName = xssEncode($('#region').val());
		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> '+LANG.UI_SEARCH_TIME_RANGE+': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
		if(p.hypervisor != "0"){
			info += '<span id="hypervisor" title="' + $('#vmtype').find("option:selected").text() + '"> '+LANG.UI_REPORT_CLOUD_PLATFORM+': <i>' + $('#vmtype').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.vmName){
			info += '<span id="vmName" style="position: relative"><span id="vmNameDetail" style="display: none;position: absolute;bottom: -40px;left: 10px;background: white"> ' + vmName + '</span> '+LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME+': <i>' + vmName + '</i><em>X</em></span>';		}
		if(p.vmIP){
			info += '<span id="vmIP" style="position: relative"><span id="vmIPDetail" style="display: none;position: absolute;bottom: -40px;left: 10px;background: white"> ' + vmIP + '</span> '+LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_IP+': <i>' + vmIP + '</i><em>X</em></span>';		}
		if(p.hostName){
			info += '<span id="hostName" style="position: relative"><span id="hostNameDetail" style="display: none;position: absolute;bottom: -40px;left: 10px;background: white"> ' + hostName + '</span> '+LANG.UI_OVERVIEW_PUBLIC_CLOUD_REGION+': <i>' + hostName + '</i><em>X</em></span>';		}
		if(p.backupFlag != "0"){
			info += '<span id="backupFlag" title="' + $('#backupFlag').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_BACKUP_STATUS +': <i>' + $('#backupFlag').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.taskName){
			info += '<span id="taskName" style="position: relative"><span id="taskNameDetail" style="display: none;position: absolute;bottom: -40px;left: 10px;background: white"> ' + taskName + '</span> '+ LANG.UI_SEARCH_BACKUP_TASK +': <i>' + taskName + '</i><em>X</em></span>';		}
		if(p.vcenteruuid != "0"){
			info += '<span id="vcenterSelect" title="' + $('#vcenterSelect').find("option:selected").text() + '"> '+LANG.UI_OVERVIEW_PUBLIC_CLOUD_PLATFORM+': <i>' + $('#vcenterSelect').find("option:selected").text() + '</i><em>X</em></span>';
		}
		
		$('.searchContent').append(info);
		$('#searchDiv').show();
		$('#searchDiv .searchContent #taskName').mouseenter(function(e){
			$('#taskNameDetail').show()
		}).mouseleave(function(){
			$('#taskNameDetail').hide()
		});
		$('#searchDiv .searchContent #vmIP').mouseenter(function(e){
			$('#vmIPDetail').show()
		}).mouseleave(function(){
			$('#vmIPDetail').hide()
		});
		$('#searchDiv .searchContent #vmName').mouseenter(function(e){
			$('#vmNameDetail').show()
		}).mouseleave(function(){
			$('#vmNameDetail').hide()
		});
		$('#searchDiv .searchContent #hostName').mouseenter(function(e){
			$('#hostNameDetail').show()
		}).mouseleave(function(){
			$('#hostNameDetail').hide()
		});

		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('#searchDiv .searchContent');
			if(searchContent[0].children.length == 0){
				$('#searchDiv').hide();
			}
			var parent  = $(this).parent();
			var id = parent[0].id;
			if(id == "time"){
				p.startTime = "";
				p.endTime = "";
			}else{
				p[id] = "";
			}
			if(id =="vcenterSelect"){
				p.vcenteruuid = "";
			}
			if (id == "backupFlag") {
				p.backupFlag = 0;
			}
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort,
						sub_module_type: sub_module_type,
						accurate_flag: true,
						hypervisor_type: p.hypervisor,
						backup_status: p.backupFlag,
						platform_uuid: (0 == p.vcenteruuid) ? '' : p.vcenteruuid,
						vm_name: p.vmName,
						vm_ip: p.vmIP,
						host_name: p.hostName,
						vm_cluster: p.vmCluster,
						job_name: p.taskName,
						start_time: p.startTime,
						end_time: p.endTime
					};
				}
			});
		});
		
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort,
						sub_module_type: sub_module_type,
						accurate_flag: false,
					};
				}
			});
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();
		}
	}

	//重新设置每页最大条数
	var resetMaxRowCount = function (total) {
		$('.page-list .dropdown-menu').find('li').eq(6).remove();
		$('.page-list .dropdown-menu').append('<li role="menuitem" class=""><a href="#">' + total + '</a></li>');
	}
	
	//初始化虚拟化类型
	var initVMType = function(){
		let p = {};
		p.cloud_flag = true;
		p.cloud_type = 'public';
		pAjaxRequest(p, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
			let hypervisors = d.data.hypervisors;
			var vmtypeselect = $('#vmtype');
			vmtypeselect.empty();
			var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val(0);
			vmtypeselect.append(option);
			for (var i = 0; i < hypervisors.length; i++) {
				option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
				vmtypeselect.append(option);
			}
			vmtypeselect.val('0');
		}, false);
	}
	
    //初始化日期选择插件
    var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepickerVmReport').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),												//默认结束时间
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,										//是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#daterangepickerVmReport').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			//单击确定按钮时触发
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerVmReport').on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			//单击取消按钮时触发
			_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			_daterangepicker_range = "";
			$(this).val('');
		});

		//input右侧的图标事件
		$('.daterangepickerdiv i').click(function() {
			$(this).parent().find('input').click();
		});
	}
    
  //初始化虚拟化中心列表
    var initVcenterSelect = function(){
		pAjaxRequest({sub_module_type: sub_module_type}, "/api/v1/vm/overview/platforms", "GET", function (d) {
			let data = d.data.rows;
			var vcenterSelect = $('#vcenterSelect');
			vcenterSelect.empty();
			var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUDPLATFORM).val('0');
			vcenterSelect.append(option);
			for(var i=0; i<data.length; i++){
				option = $("<option>").text(data[i].platform_ip).val(data[i].platform_uuid);
				vcenterSelect.append(option);
			}
			vcenterSelect.val('0');
		}, false);
    }
    
    return {
        //main function to initiate the module
        init: function () {
        	initVcenterSelect(); //初始化虚拟化中心列表
        	initVMType(); //初始化虚拟化类型
        	inintDatatimePicker(); //初始化日期选择
        	handleRecords();
        	addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
	VmReport.init();
});