var VirtualLab = function(){
	let gridInitFlag = false;
	let grid;
	//控制详情刷新的全局变量 插入到第几条后,插入的信息，详情页面
	let detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	let labData = [];
	var interval = null;
	//加载回调事件函数
	const initListeners = function(){
		//新建虚拟实验室
		$('#addLab').on('click', function(){
			let url = "/module/verification/html/add_virtual_lab.php";
			LOCATION(url)
		});

		//修改虚拟实验室
		$('#editLab').on('click', function(){
			//获取选中项
			let select = $('#lab_table').bootstrapTable('getSelections');
			if(select.length == 0){
				return UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_EDIT_SELECT_TIPS);
			}
			if(select.length > 1){
				return UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_EDIT_SELECT_ONE_TIPS);
			}
				for(var i=0;i<select.length;i++){
					//部署中/修改中中不能修改
					if( (1 == select[i]['status'] || 6 == select[i]['status'])){
						UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_EDIT_DEPLOYING_TIPS);
						return false;
					}
					//虚拟实验室正在使用中
					if(2 == select[i]['status']){
						UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_EDIT_USING_TIPS);
						return false;
					}
				}

				//组合修改的的url链接
				let url = "/module/verification/html/add_virtual_lab.php?uuid=" + select[0]['lab_uuid'];
				LOCATION(url);

			});

		//删除虚拟实验室
		$('#deleteLab').on('click', function(){
			//获取选中项
			let select = $('#lab_table').bootstrapTable('getSelections');
			let ids = [];
			for (let i = 0; i < select.length; i++) {
				ids.push(select[i].lab_uuid);
			}
			if(select.length == 0){
				return UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_DELETE,LANG.UI_VIRTUAL_LAB_DELETE_SELECT_TIPS);
			}
			if(select.length >1){
				return UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_DELETE, LANG.UI_VIRTUAL_LAB_DELETE_SELECT_ONE_TIPS);
			}

				//初始化删除提示框
				bootbox.confirm({
					title: LANG.UI_VIRTUAL_LAB_DELETE,
					message: LANG.UI_VIRTUAL_LAB_DELETE_CONFIRM_TIPS,
					callback: function(r) {
						if(!r) return;
						submitDelete(ids);
					}
				});
			});

		//同步虚拟演练室
		$('#refreshLab').on('click', function(){
			//获取选中项
			let select = $('#lab_table').bootstrapTable('getSelections');
			let ids = [];
			for (let i = 0; i < select.length; i++) {
				ids.push(select[i].lab_uuid);
				if(i>0 && select[0].hypervisor_type != select[i].hypervisor_type){
					UIToastr.showWarning(LANG.UI_VIRTUAL_LAB_UPDATE, LANG.UI_VIRTUAL_SELECT_SAME_HYPERVISOR_TIPS);
					return false;
				}
			}
			if(select.length == 0){
				UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_UPDATE, LANG.UI_VIRTUAL_LAB_UPDATE_SELECT_TIPS);
				return false;
			}
				for(let i=0;i<select.length;i++){
					//只能刷新已部署的虚拟实验室
					if(7 != select[i]['status']){
						UIToastr.showWarning(LANG.UI_VIRTUAL_LAB_UPDATE, LANG.UI_VIRTUAL_LAB_UPDATE_DEPLOY_TIPS);
						return false;
					}
				}

				//初始化同步提示框
				bootbox.confirm({
					title: LANG.UI_VIRTUAL_LAB_UPDATE,
					message: LANG.UI_VIRTUAL_LAB_UPDATE_TIPS,
					callback: function(r) {
						if(!r) return;
						submitRefresh(ids, select[0].hypervisor_type);
					}
				});
			});

	}

	//刷新虚拟实验室确认
	let submitRefresh = function(select, hypervisor){
		let data = {};
		data.lab_list = select;
		data.hypervisor = hypervisor;

		$('#labContent').block();
		pAjaxRequest(data, '/api/v1/verification/lab/refresh', 'POST', (d) => {
			$('#labContent').unblock();
			var op = LANG.UI_VIRTUAL_LAB_UPDATE;
			if (operateResponseList(d, op)) {
				$('#lab_table').bootstrapTable("refresh");
			}

		});
	}


	//删除虚拟实验室确认
	let submitDelete = function(select){
		let data = {};
		data.lab_uuid_list = select;

		$('#labContent').block();
		pAjaxRequest(data, '/api/v1/verification/lab', 'DELETE', (d) => {
			$('#labContent').unblock();
			var op = LANG.UI_VIRTUAL_LAB_DELETE;
			if (operateResponseList(d, op)) {
				$('#lab_table').bootstrapTable("refresh");
			}

		});
	}



	//初始化状态值显示
	const setStatusDes = function(status){
		let labelClass = "label-info";
    	switch(status){
    		case 0:	//未知
    			labelClass = "label-info";
    			break;
    		case 1:	//部署中
    			labelClass = "label-success";
    			break;
    		case 2:	//在线
    			labelClass = "label-success";
    			break;
			case 3:	//离线
				labelClass = "label-default";
				break;
			case 4:	//异常
				labelClass = "label-warning";
				break;
			case 5:	//错误
				labelClass = "label-danger";
				break;
			case 6:	//修改
				labelClass = "label-info";
				break;
			case 7:	//已部署
				labelClass = "label-success";
				break;
			default:
				labelClass ="label-success";
				break;
    	}
		return labelClass;
	}


	//显示代理网关信息
	var getProxyInfo = function(vm){
		var table = '<table class="detailstable"><thead><tr>' +
						'<th width="20%">' + LANG.UI_DRILLS_AGENT_GATEWAY + '</th><th width="10%">' + LANG.UI_DRILLS_IP_ADDRESS + '</th><th width="10%">' + LANG.UI_DRILLS_NETMASK + '</th><th width="10%">' + LANG.UI_DRILLS_GATEWAY + '</th><th width="15%">' + LANG.UI_VM_SETTING_NETWORK + '</th><th width="25%">' + LANG.UI_VM_SETTING_STORAGE + '</th></tr></thead>' +
					'<tbody><tr><td>' + vm.name + '</td><td>' + vm.ip + '</td><td>' + vm.netmask + '</td><td>' + vm.gateway +
					'</td><td>' + vm.network + '</td><td>' + vm.datastore + '</td></tr></tbody></table>';
		return table;
	}

	//组织隔离网络对比详细信息
	var getIsoladNetworkInfo = function(data){
		if(!data) return;
		var list = data.map_list;
		var des = '';
		des += '<table style="padding: 10px;"><thead><tr><th width="30%">'+LANG.UI_DRILLS_PRODUCT_NETWORK+'</th><th width="30%">'+LANG.UI_DRILLS_ISOLATED_NETWORK+'</th></tr></thead><tbody>';
		for(var i =0;i<list.length; i++){
			var productDes = LANG.UI_DRILLS_PRODUCT_NETWORK + ": " + list[i].product_network_network_name + "<br>" + LANG.UI_DRILLS_NETMASK +
			": " + list[i].product_network_netmask + "<br>" + LANG.UI_DRILLS_GATEWAY +
			": " + list[i].product_network_gateway;
			var isolatedDes = LANG.UI_DRILLS_ISOLATED_NETWORK + ": " + list[i].isolate_network_network_name + "<br>" + LANG.UI_DRILLS_NETMASK +
			": " + list[i].isolate_network_netmask + "<br>" + LANG.UI_DRILLS_GATEWAY +
			": " + list[i].isolate_network_gateway;

			des += '<tr><td>' + productDes + '</td><td>' + isolatedDes +"</td></tr>";
		}

		des +='</tbody></table>';

		return des;
	}

	//初始化虚拟演练室详情
	const initLabDetails = function(row){
		let ID = row.lab_uuid;
		let data = {};
		$('#labContent').block();
		pAjaxRequest(data, '/api/v1/verification/lab/'+ID, 'GET', function (result) {
			$('#labContent').unblock();
			let data = result.data;

			//演练室名称
			$('#labName').html(data.lab_name);

			//状态
			let labelClass = setStatusDes(data.status);
			let div = '<span class="label label-sm status-icon ' + labelClass + '">' + data.status_des + '</span>';
			$('#labStatus').html(div);

			//代理网关
			$('#proxy').html(data.proxy_info.proxy_name);

			//IP地址
			$('#labIpaddr').html(data.proxy_info.ip);

			//子网掩码
			$('#labNetmask').html(data.proxy_info.netmask);

			//默认网关
			$('#labGateway').html(data.proxy_info.gateway);

			//网络
			$('#labNetwork').html(data.proxy_info.network_name);

			//存储
			$('#labStorage').html(data.proxy_info.mount_storage);

			//虚拟化中心
			$('#vcenterName').html(data.vcenter_name);

			//宿主机
			$('#hostName').html(data.host_name);

			let productList = [], isolatedList = [];
			for (let i=0;i<data.network_list.length;i++){
				let productname = data.network_list[i].product_network_network_name;
				if (data.network_list[i].product_network_network_name == ""){
					productname = "--";
				}

				let isolatedname = data.network_list[i].isolate_network_network_name;
				if (data.network_list[i].isolate_network_network_name == ""){
					isolatedname = "--";
				}
				let productInfo = {
					'name':  data.network_list[i].product_network_network_name,
					'netmask': data.network_list[i].product_network_netmask,
					'gateway': data.network_list[i].product_network_gateway,
				}
				let isolatedInfo = {
					'name':  data.network_list[i].isolate_network_network_name,
					'netmask': data.network_list[i].isolate_network_netmask,
					'gateway': data.network_list[i].isolate_network_gateway,
				}
				productList.push(productInfo);
				isolatedList.push(isolatedInfo);
			}
			//初始化生产网络表格
			initNetworkTable(productList, '#product_network_table', LANG.UI_DRILLS_PRODUCT_NETWORK);

			//初始化隔离网络表格
			initNetworkTable(isolatedList, '#isolated_network_table', LANG.UI_DRILLS_ISOLATED_NETWORK);

			$('#show_labdetail_drawer').drawer('show');
		});
	}

	//初始化网络信息表格
	let initNetworkTable = function(data, div, des){
		//表格初始化配置项
		let options = {
			data: data,
			pagination: false,
			columns: [
				{
					field: 'name',
					sortable: false,
					title: des,
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

	var initTimer = function () {
		if (interval != null) { //判断计时器是否为空
			clearTimeout(interval);
		}
		interval = setTimeout(update, 5000);
	}
	//更新表格数据
	var update = function () {
		$('#lab_table').bootstrapTable('refresh');
	}

	//初始化表格
	var handleRecords = function(){
		// 根据授权来控制按钮的显示和隐藏
		let beforeInput = '';
		if ($.inArray('p_virtual_lab_delete', CONF.PERMISSION_ARR) !== -1) {
			// 删除
			beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteLab"></button></div>`
		}

		let afterInput = '';
		// 新建
		if ($.inArray('p_virtual_lab_add', CONF.PERMISSION_ARR) !== -1) {
			afterInput += `<div><button type="button" class="btn table-toolbar-btn" id="addLab" aria-haspopup="true" aria-expanded="false">
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>`+LANG.UI_PUBLIC_ADD+`</span>
                            </button>`;
		}

		// 修改
		if ($.inArray('p_virtual_lab_edit', CONF.PERMISSION_ARR) !== -1) {
			afterInput += ` <button type="button" class="btn table-toolbar-btn" id="editLab" aria-haspopup="true" aria-expanded="false">
                                <i class="viconfont vicon-xiugai mr4"></i>
                                <span>`+LANG.UI_PUBLIC_EDIT+`</span>
                            </button>`;
		}

		if ($.inArray('p_virtual_lab_refesh', CONF.PERMISSION_ARR) !== -1) {
			// 刷新
			afterInput += `<button type="button" class="btn table-toolbar-btn" id="refreshLab">
                                <i class="viconfont vicon-ge_refresh mr4"></i>`+LANG.UI_VCENTER_SYNC+`</button>`;
		}

		if (afterInput != '') {
			afterInput = '<div>' + afterInput + '</div>';
		}
		let operates = {
			'click .labDetails' :function (event, value, row, index){
				initLabDetails(row);
			}
		};
		//表格初始化配置项
		let options = {
			toolbarId: '#vin_lab_toolbar',
			buttonsToolbar: '#vin_lab_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/verification/lab',
			vin_method: 'GET',
			placeholder: LANG.UI_VERIFY_SEARCH_BY_LAB_NAME, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'labSearch', //自定义的搜索框类名
			searchSelector: '.labSearch', //选择使用自定义搜索框
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			sortName: 'svl.create_time',
			sortOrder: 'desc',
			onCheck: function (row) {
				modifyDelStyle('lab_table', 'deleteLab');
				labData.push(row.lab_uuid);
			},
			onUncheck: function (row) {
				modifyDelStyle('lab_table', 'deleteLab');
				let index = labData.indexOf(row.lab_uuid); // 查找元素的索引
				if (index !== -1) {
					labData.splice(index, 1); // 从数组中删除一个元素
				}
			},
			onCheckAll:function (row) {
				modifyDelStyle('lab_table', 'deleteLab');
				for (let i = 0; i < row.length; i++) {
					let index = labData.indexOf(row[i].lab_uuid); // 查找元素的索引
					if (index == -1) {
						labData.push(row[i].lab_uuid);
					}
				}
			},
			onUncheckAll: function (row) {
				modifyDelStyle('lab_table', 'deleteLab');
				for (let i = 0; i < row.length; i++) {
					let index = labData.indexOf(row[i].lab_uuid); // 查找元素的索引
					if (index != -1) {
						labData.splice(index, 1); // 从数组中删除一个元素
					}
				}
			},
			onPostBody: function () {
				initTimer();//五秒定时刷新表格
			},
			onRefresh: function (params) {
				$('#lab_table').bootstrapTable('hideLoading');
			},
			customTool: {
				beforeInput: beforeInput,
				afterInput: afterInput,
			},
			fileName:"",
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
					field: 'svl.virtual_lab_name',
					title: LANG.UI_VIRTUAL_LAB_NAME,
					formatter: function (value, row, index, field) {
						return row.lab_name;
					}
				},
				{
					field: 'vv.hypervisor_type',
					title: LANG.UI_VIRTUAL_DEPLOY_LOCATION,
					formatter: function (value, row, index, field) {
						return row.hypervisor_type_des;
					}
				},
				{
					field: 'svl.proxy_name,svl.proxy_ip',
					title: LANG.UI_DRILLS_AGENT_GATEWAY,
					formatter: function (value, row, index, field) {
						return row.proxy_gateway;
					}
				},
				{
					field: 'svl.create_time',
					title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
					formatter: function (value, row, index, field) {
						return row.create_time;
					}
				},
				{
					field: 'status',
					title: LANG.UI_PUBLIC_STATUS,
					formatter: function (value, row, index, field) {
						let labelClass = setStatusDes(row.status);
						return '<span title="'+row.title+'" class="label label-sm status-icon ' + labelClass + '">' + row.status_des + '</span>';
					}
				},
				{
					title: LANG.UI_ALARM_DETAILS,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter:function (value, row, index, field){
						let nameStr = "<a class='labDetails' id='" + row.lab_uuid + "'>" + LANG.UI_ALARM_DETAILS + "</a>";
						return nameStr;
					},
					events:operates,
				},
			],
		}
		$('#lab_table').baseTableConfig().init(options);
	}

	return {
		init: function(){
			handleRecords();
			initListeners();
		}
	}
}();

jQuery(document).ready(function(){
	VirtualLab.init();
});