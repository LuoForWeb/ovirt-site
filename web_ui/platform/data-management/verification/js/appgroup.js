var AppGroup = function(){
	let grid;
	let appGroupData = [];
	let cpuArch = [
		'Unknow',
		'x86',
		'x86_64',
		'ARM',
		'ARM64'];


	//加载回调事件函数
	let initListeners = function(){
		//新建应用组
		$('#addAppGroup').on('click', function(){
			let url = "/module/verification/html/add_appgroup.php";
			LOCATION(url)
		});

		//修改应用组
		$('#editAppGroup').on('click', function(){
			//获取选中项
			let select = $('#appgroup_table').bootstrapTable('getSelections');
			if(select.length == 0){
				return UIToastr.showInfo(LANG.UI_VERIFY_EDIT_APPGROUP, LANG.UI_VERIFY_EDIT_APPGROUP_NOT_SELECT_TIPS);
			}
			if(select.length > 1){
				return UIToastr.showInfo(LANG.UI_VERIFY_EDIT_APPGROUP, LANG.UI_VERIFY_EDIT_APPGROUP_SELECT_ONE_TIPS);
			}

			let ID = select[0]['appgroup_uuid'];
			//组合修改的的url链接
			let url = "/module/verification/html/add_appgroup.php?uuid=" + ID;
			LOCATION(url);

		});

		//删除应用组
		$('#deleteAppGroup').on('click', function(){
			//获取选中项
			let select = $('#appgroup_table').bootstrapTable('getSelections');
			let ids = [];
			let nodeuuid;
			for (let i = 0; i < select.length; i++) {
				ids.push(select[i].appgroup_uuid);
				nodeuuid = select[i].node_uuid;
			}
			if(select.length == 0){
				return UIToastr.showInfo(LANG.UI_VERIFY_DELETE_APPGROUP, LANG.UI_VERIFY_DELETE_APPGROUP_NOT_SELECT_TIPS);
			}


		    //初始化删除提示框
			bootbox.confirm({
	            title: LANG.UI_VERIFY_DELETE_APPGROUP,
	            message: LANG.UI_VERIFY_DELETE_APPGROUP_CONFIRM_TIPS,
	            callback: function(r) {
	                if(!r) return;
	                submitDelete(ids,nodeuuid);
	            }
	        });
		});
	}

	//删除应用组确认
	let submitDelete = function(select, nodeuuid){
		let params = {};
		params.appgroup_list = select;
		params.node_uuid = nodeuuid;
		pAjaxRequest(params, '/api/v1/verification/app_group/', 'DELETE', (d) => {
			var op = LANG.UI_VERIFY_DELETE_APPGROUP;
			if (operateResponseList(d, op)) {
				$('#appgroup_table').bootstrapTable("refresh");
			}
		});
	}


	//勾选树节点添加右侧虚拟机列表
	let addObjectList = function(data){
		let info = "";
		let general = data.general_config;
		let verify = data.verify_config;
		let network = data.network_config;
		let liID = general.item_uuid;
		let timepointDes = [LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST,LANG.UI_VERIFY_SELECT_TIMEPOINT_SELECT,LANG.UI_VERIFY_SELECT_TIMEPOINT_ALL];
		let displayHide = "";
		if(CONF.VENDOR == 'vdms'){
			displayHide = "display-hide";
		}
		//head
		info +=
			'<li class="panel panel-default mb10" id="object'+liID+'">' +
			'<div class="panel-heading">' +
			'<h4 class="panel-title">' +
			'<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" href="#config'+liID+'" aria-expanded="true" data-parent="#objectList">'+
			'<span class="font-green-seagreen">'+general.item_name+'('+ CONF.MODULE_TYPE_DES[general.module_type] +')'+'</span>' +
			'</a>' +
			'</h4>' +
			'</div>';
		//content
		info +=
			'<div class="panel-collapse collapse" id="config'+liID+'">' +
			'<div class="panel-body" style="padding: 16px"><div class="tabs">' +
			'<ul class="nav nav-tabs nav-line-tabs">' +
			'<li class="nav-item active">' +
			'<a class="nav-link" href="#common_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+LANG.UI_VM_SETTING_V2_GENERAL+'</a>' +
			'</li>'+
			'<li class="nav-item">' +
			'<a class="nav-link" href="#verify_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+LANG.UI_VERIFY_VERIFY_CONFIG+'</a>' +
			'</li>';

		//检查如果有网卡信息
		if(network.length != 0){
			info += '<li class="nav-item">' +
				'<a class="nav-link" href="#network_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+LANG.UI_VERIFY_NETWORK_CONFIG+'</a>' +
				'</li>';
		}
		info += '</ul><div class="tab-content" style="height:260px;">';
		//通用配置
		info +=
			'<div class="tab-pane active" id="common_tab'+liID+'"><div class="row">' +
			'<div class="row static-info"><label class="col-md-4 name textalignr">'+LANG.UI_JOB_TIMEPOINT_INFO+'</label><div class="col-md-6 value"><span>'+timepointDes[verify.timepoint_uuid_list.timepoint_range]+'</span></div></div>' +
			'<div class="row static-info"><label class="col-md-4 name textalignr">'+LANG.UI_VM_SETTING_CPU_ARCH+'</label><div class="col-md-6 value"><span>'+cpuArch[general.cpu_arch]+'</span></div></div>' +
			'<div class="row static-info"><label class="col-md-4 name textalignr">'+LANG.UI_VERIFY_CPU_NUM+'</label><div class="col-md-6 value"><span>'+general.cpu_num+'</span></div></div>' +
			'<div class="row static-info"><label class="col-md-4 name textalignr" style="word-break: normal;">'+LANG.UI_VERIFY_CPU_EVERY_CORE+'</label><div class="col-md-6 value"><span>'+general.core_num+'</span></div></div>' +
			'<div class="row static-info"><label class="col-md-4 name textalignr">'+LANG.UI_VERIFY_MEMORY_SIZE+'</label><div class="col-md-6 value"><span>'+general.memory_size_int + general.memory_size_unit +'</span></div></div>' +
			'<div class="row static-info"><label class="col-md-4 name textalignr" style="word-break: normal;">'+LANG.UI_VM_SELECT_OS_TYPE+'</label><div class="col-md-6 value"><span>'+general.os_type+'</span></div></div>'+
			'<div class="row static-info"><label class="col-md-4 name textalignr" style="word-break: normal;">'+LANG.UI_VM_OS_VERSION+'</label><div class="col-md-6 value"><span>'+general.os_version_name+'</span></div></div>' +
			'<div class="row static-info"><label class="col-md-4 name textalignr" style="word-break: normal;">'+LANG.UI_VERIFY_OBJECT_DISK_TARGET_BUS+'</label><div class="col-md-6 value"><span>'+general.disk_target_bus_des+'</span></div></div>' +
			'<div class="row static-info"><label class="col-md-4 name textalignr" style="word-break: normal;">'+LANG.UI_VERIFY_OBJECT_NETCARD_TARGET_BUS+'</label><div class="col-md-6 value"><span>'+general.netcard_target_bus_des+'</span></div></div>';


		info += '</div></div>';
		let timepoint = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST;
		if(verify.timepoint_uuid != ""){
			timepoint = verify.timepoint_uuid;
		}
		//验证配置
		info +=
			'<div class="tab-pane" id="verify_tab'+liID+'">' +
			'<div class="row">' +
			'<div class="row static-info">' +
			'<label class="col-md-4 name textalignr">'+LANG.UI_VERIFY_PING_TEST+'</label>' +
			'<div class="col-md-8 value">' +
			'<span>'+getFlagLevelInfo(verify.ping_test_flag)+'</span>' +
			'</div>' +
			'</div>' +
			'<div class="row static-info">' +
			'<label class="col-md-4 name textalignr">'+LANG.UI_VERIFY_HEARTBEAT+'</label>' +
			'<div class="col-md-8 value">' +
			'<span>'+getFlagLevelInfo(verify.heartbeat_flag)+'</span>' +
			'</div>' +
			'</div>' +
			'<div class="row static-info">' +
			'<label class="col-md-4 name textalignr">'+LANG.UI_VERIFY_SCREEN+'</label>' +
			'<div class="col-md-8 value">' +
			'<span>'+getFlagLevelInfo(verify.print_screen_flag)+'</span>' +
			'</div>' +
			'</div>' +
			'<div class="row static-info '+displayHide+'">' +
			'<label class="col-md-4 name textalignr">'+LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS+'</label>' +
			'<div class="col-md-8 value">' +
			'<span>'+getFlagLevelInfo(verify.vir_det_kill_flag)+'</span>' +
			'</div>' +
			'</div>' +
			'<div class="row static-info">' +
			'<label class="col-md-4 name textalignr">'+LANG.UI_VM_INTEGRITY_VERIFY+'</label>' +
			'<div class="col-md-8 value">' +
			'<span>'+getFlagLevelInfo(verify.integrity_check_flag)+'</span>' +
			'</div>' +
			'</div>' +
			'<div class="row static-info">' +
			'<label class="col-md-4 name textalignr">'+LANG.UI_VERIFY_OBJECT_PING_WAIT_TIME+'</label>' +
			'<div class="col-md-8 value">' +
			'<span>'+verify.max_ping_wait_time+'</span>' +
			'</div>' +
			'</div>' +
			'<div class="row static-info">' +
			'<label class="col-md-4 name textalignr">'+LANG.UI_VERIFY_EFFECTIVE_TIME+'</label>' +
			'<div class="col-md-8 value">' +
			'<span>'+verify.max_boot_time+'</span>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'</div>';

		//网络配置
		info +=
			'<div class="tab-pane" id="network_tab'+liID+'">' +
			'<div class="row">' +
			'<table id="network_table"></table>' +
			'</div>' +
			'</div>';
		//foot
		info += '</div></div></div></div></li>';

		$('#objectList').append(info);
		if(network.length !=0){
			initNetworkTable(network, '#network_table')
		}
	}

	//初始化网络信息表格
	let initNetworkTable = function(data, div){
		//表格初始化配置项
		let options = {
			data: data,
			pagination: false,
			columns: [
				{
					field: 'ip',
					sortable: false,
					title: LANG.UI_CLIENT_IP_ADDRESS,
				},
				{
					field: 'netmask',
					sortable: false,
					title: LANG.UI_PUBLIC_IP_NETMASK,
				},
				{
					field: 'gateway',
					sortable: false,
					title: LANG.UI_PUBLIC_IP_GATEWAY,
				}]
		}
		$(div).bootstrapTable('destroy');
		$(div).baseTableConfig().init(options);
	}


	//初始化应用组详情
	let initAppgroupDetails = function(row){
		let ID = row.appgroup_uuid;
		pAjaxRequest({}, '/api/v1/verification/app_group/' + ID, 'GET', (result) => {
			if (result.success) {
				$('#appgroupname').html(result.data.appgroup_name);
				$('#objectList').empty();
				for (let i=0; i<result.data.object_list.length; i++){
					addObjectList(result.data.object_list[i]);
				}
				$('#appgroup_detail_drawer').drawer('show');
			}
		});
	}

	//初始化表格
	let handleRecords = function(){
		// 根据授权来控制按钮的显示和隐藏
		let beforeInput = '';
		// 删除
		if ($.inArray('p_app_group_delete', CONF.PERMISSION_ARR) !== -1) {
			beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteAppGroup"></button></div>`;

		}

		let afterInput = '';
		// 新建
		if ($.inArray('p_app_groupb_add', CONF.PERMISSION_ARR) !== -1) {
			afterInput += `<div><button type="button" class="btn table-toolbar-btn" id="addAppGroup" aria-haspopup="true" aria-expanded="false">
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>`+LANG.UI_PUBLIC_ADD+`</span>
                            </button>`;
		}

		if ($.inArray('p_app_group_edit', CONF.PERMISSION_ARR) !== -1) {
			// 修改
			afterInput += ` <button type="button" class="btn table-toolbar-btn" id="editAppGroup" aria-haspopup="true" aria-expanded="false">
                                <i class="viconfont vicon-xiugai mr4"></i>
                                <span>`+LANG.UI_PUBLIC_EDIT+`</span>
                            </button>`;
		}

		if (afterInput != '') {
			afterInput = '<div>' + afterInput + '</div>';
		}

		let operates = {
			'click .appgroupDetails':function(event, value, row, index){
				initAppgroupDetails(row);
			}
		}

		//表格初始化配置项
		let options = {
			toolbarId: '#vin_appgroup_toolbar',
			buttonsToolbar: '#vin_appgroup_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/verification/app_group',
			vin_method: 'GET',
			placeholder: LANG.UI_VERIFY_SEARCH_BY_APPGROUP_NAME, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'appGroupSearch', //自定义的搜索框类名
			searchSelector: '.appGroupSearch', //选择使用自定义搜索框
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			sortName: 'sag.create_time',
			sortOrder: 'desc',
			onCheck: function (row) {
				modifyDelStyle('appgroup_table', 'deleteAppGroup');
				appGroupData.push(row.appgroup_uuid);
			},
			onUncheck: function (row) {
				modifyDelStyle('appgroup_table', 'deleteAppGroup');
				let index = appGroupData.indexOf(row.appgroup_uuid); // 查找元素的索引
				if (index !== -1) {
					appGroupData.splice(index, 1); // 从数组中删除一个元素
				}
			},
			onCheckAll:function (row) {
				modifyDelStyle('appgroup_table', 'deleteAppGroup');
				for (let i = 0; i < row.length; i++) {
					let index = appGroupData.indexOf(row[i].appgroup_uuid); // 查找元素的索引
					if (index == -1) {
						appGroupData.push(row[i].appgroup_uuid);
					}
				}
			},
			onUncheckAll: function (row) {
				modifyDelStyle('appgroup_table', 'deleteAppGroup');
				for (let i = 0; i < row.length; i++) {
					let index = appGroupData.indexOf(row[i].appgroup_uuid); // 查找元素的索引
					if (index != -1) {
						appGroupData.splice(index, 1); // 从数组中删除一个元素
					}
				}
			},
			customTool: {
				beforeInput: beforeInput,
				afterInput: afterInput,
			},
			fileName: LANG.UI_VERIFY_APPGROUP_LIST,
			columns:[
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
					field: '',
					title: LANG.UI_VERIFY_APPGROUP_NAME,
					formatter: function (value, row, index, field) {

						return row.appgroup_name;
					}
				},
				// {
				// 	field: 'sag.description',
				// 	title: LANG.UI_PUBLIC_DESCRIPTION,
				// 	formatter: function (value, row, index, field) {
				// 		return row.description;
				// 	}
				// },
				{
					field: '',
					title: LANG.UI_VERIFY_APPGROUP_HOST_NUM,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (value, row, index, field) {
						return row.object_num;
					}

				},
				{
					field: 'sag.create_time',
					title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
					formatter: function (value, row, index, field) {
						return row.create_time;
					}
				},
				{
					title: LANG.UI_ALARM_DETAILS,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter:function (value, row, index, field){
						let nameStr = '<a class="appgroupDetails"  id="' + row.appgroup_uuid + '" >' + LANG.UI_ALARM_DETAILS + '</a>';
						return nameStr;
					},
					events:operates
				},
			],
		}

		$('#appgroup_table').baseTableConfig().init(options);
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
		init: function(){
			handleRecords();
			initListeners();
		}
	}
}();

jQuery(document).ready(function(){
	AppGroup.init();
});