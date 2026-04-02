var NasManager = function () {
	var grid,ipGrid;//nas设备表格,ip表格
	var detailsIndexLog = 0,detailsInfo = null;
	var edituuid;
	var selectVersionFlag = false;	//选择版本标志
	var nasData = [];//定义勾选资源存放
	var _licenseType;
	let EDIT_NAS_FLAG = false;

	var initDataTable = function () {
		var lastIndex = [-1, -1];
		var options = {
			detailFormatter:function (row,data,div) {
				if (row != lastIndex[1]) {//只展开一行
					lastIndex.push(row);
					$('#table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
					lastIndex.splice(0, 1);
				}
				var allnode = LANG.UI_PUBLIC_NOTHING,
					mount_params = LANG.UI_PUBLIC_NOTHING,
					user_name = LANG.UI_PUBLIC_NOTHING;
				if(data.detail.mount_list_name.length!=0) {
					allnode = data.detail.mount_list_name.join('\n');
				}
				if(data.detail.mount_params!="") {
					mount_params = data.detail.mount_params;
				}
				if(data.detail.user_name!="") {
					user_name = data.detail.user_name;
				}
				var html = '';
                //协议版本
				var version = 'V' + data.detail.nas_version;
				if (data.detail.nas_version =="") {
					version = LANG.UI_NAS_DEFAULT_VERSION;
				}
				html += '<div class="col-md-1 detail-padding">' +
					'<div>'+ LANG.UI_STORAGE_DETAIL_USERNAME +'：' + user_name + '</div>' +
					' </div>';
				html += '<div class="col-md-2 detail-padding">' +
					'<div>'+ LANG.UI_NAS_AGREEMENT_VERSION +'：' +  version + '</div>' +
					' </div>';
				if (data.vendor == 1) {//华为dorado
					html += '<div class="col-md-2 detail-padding">' +
					'<div>'+ LANG.UI_NAS_MANAGE_DEVICE_MANAGER_CONFIG +'：' + '<br>'  
						+ 'ip：' + data.detail.detail.rest_ip + '<br>' 
						+ LANG.UI_MICROSOFT365_USER_NAME + '：' + data.detail.detail.rest_username+ '</div>' 
						+ LANG.UI_NAS_MANAGE_TENANT_ID + data.detail.detail.rest_vstore_name + '(' + data.detail.detail.rest_vstoreid + ')' + '</div>' +
					' </div>';
				}
				html += '<div class="col-md-2 detail-padding">' +
					'<div>'+ LANG.UI_NAS_MANAGE_READ_WRITE_PERMISSION +'：' + data.permission_flag + '</div>' +
					' </div>';
				html += '<div class="col-md-2 detail-padding">' +
					'<div>'+ LANG.UI_NAS_MANAGE_MOUNT_PARAMS +'：' + mount_params + '</div>' +
					' </div>';
				html += '<div class="col-md-3 detail-padding">' +
					'<div>'+ LANG.UI_NAS_MANAGE_MOUNT_SUCCESS_NODE +'：'  +  '<textarea readonly style="outline: none;vertical-align: top;" cols="30" rows="2">' + allnode + '</textarea></div>' +
					' </div>';
				return html;
			},
			toolbarId: '#vin_nas_toolbar',
			buttonsToolbar: '#vin_nas_toolbar .vin_btnToolbar',
			placeholder: LANG.UI_NAS_SEARCH_BY_NAME, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'nasSearch', //自定义的搜索框类名
			searchSelector: '.nasSearch', //选择使用自定义搜索框
			pagination:true,
			pageList:[10,20,50,100,150,200],
			detailView:true,
			sortName: 'create_time',
			sortOrder: 'desc',
			// pa:{},
			vin_url:"/api/v1/nas",
			vin_method:"GET",
            fullPage:true, //全屏表格高度适配
			customTool: {
				beforeInput: toAddTableBtn('beforeDiv'),
				afterInput: toAddTableBtn('afterDiv'),
			},
			columns:[{
				checkbox:true,
				sortable: false,
				formatter: function (value, row, index, field) {
					if (row.op_flag === false) {//创建者等不是当前用户，不能操作
						return {
							disabled: true
						};
					}
					for(var i=0; i<nasData.length; i++) {
						if(row.nas_uuid == nasData[i]){
							return true;
						}
					}
				}
			},
				{
					field: 'vendor_des',
					title: LANG.UI_VIRUS_VENDOR,
					sortable: true,
					align: 'center',
				},
				{
					field: 'ip',
					title: LANG.UI_CLOUD_PLATFORM_IP_ADDRESS,
					sortable: true,
					align: 'center',
				},
				{
					field: 'share_path',
					title: LANG.UI_REPORT_SHARE_PATH,
					sortable: true,
					align: 'center',
				},
				{
					field: 'nickname',
					title: LANG.UI_REPORT_DEVICE_NAME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'nas_type',
					title: LANG.UI_SEARCH_TYPE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'create_time',
					title: LANG.UI_PUBLIC_ADD_TIME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'status',
					title: LANG.UI_REPORT_DEVICE_STATUS,
					sortable: true,
					align: 'center',
					formatter: function (value,data,row) {
						var thisClass = "label-info";
						if(1 == value){
							thisClass = "label-success";
						}else if(2 == value){
							thisClass = "label-warning";
						}else if(3 == value){
							thisClass = "label-danger";
						}
						return '<span class="label label-sm ' + thisClass + '">' + data.detail.nas_status + '</span>';
					}
				},
			                                                            
				{
					field: 'creator',
					title: LANG.UI_CLOUD_PLATFORM_CREATER,
					sortable: false,
					align: 'center',
				},
				{
					field: 'owner',
					title: LANG.UI_CLIENT_OWNER,
					sortable: false,
					align: 'center',
				},
				{
					field: 'operate',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					align: 'center',
					events: opEvent,
					clickToSelect: false, //不可通过点击行选中
					// type: "operation",
					formatter: function (value,data,row) {
						//没有操作权限、创建者不是当前用户才显示操作
						if (!data.op_flag || !($.inArray('p_nasmanager_device_mount', CONF.PERMISSION_ARR) !== -1 && $.inArray('p_nasmanager_device_unmount', CONF.PERMISSION_ARR) !== -1)) {
							// 都没得 那么就不显示操作按钮了
							return '-';
						}
						//1 挂载  2 解挂
						var button = '';
						button += `<div class="btn-group"><button style="line-height: 16px;" type="button" class="btn btn-primary brr2 p-lr8 btn-sm dropdown-toggle" data-toggle="dropdown"
						data-hover="dropdown" aria-haspopup="true" data-delay="1000"><i class="glyphicon glyphicon-hand-up"></i>
						${LANG.UI_PUBLIC_OPERATION}<i class="fa fa-angle-down ml10"></i></button><ul class="dropdown-menu min-width100">`;
						if ($.inArray('p_nasmanager_device_mount', CONF.PERMISSION_ARR) !== -1) {
							button += '<li class="eachMount"><a href="javascript:;" ><i class="fa fa-chain (alias)"></i> ' + LANG.UI_NAS_MANAGE_MOUNT + '</a></li>';
						}
						if ($.inArray('p_nasmanager_device_unmount', CONF.PERMISSION_ARR) !== -1) {
							button += '<li class="eachUnmount"><a href="javascript:;"><i class="fa fa-chain-broken"></i> ' + LANG.UI_NAS_MANAGE_UMOUNT + '</a></li>';
						}
						button += '</ul></div>';
						$('[data-hover="dropdown"]').dropdownHover();
						return button;
					}
				},
			],
			onPostBody: function () {
				//加载完毕
				$('#table tr td:last-child').each(function() {
					// 移除overflow属性
					$(this).css('overflow', '');
				});
				modifyDelStyle('table', 'deleteNas');
				//授权表格
				var ipdataTableOpt = {
					'pageLength': parseInt(length),
					'columnDefs' : [{
						'orderable': false,
						'targets': [0]
					}],
					"order": [
						[2, "desc"]
					],
				};
				ipGrid = new Datatable();
				var ipData = {m:CONF.M.NAS,f:'getLicenseIp',p:{}};
				ipGrid.setAjaxParam(ipData);
				ipGrid.init({src: $("#nasIpDatatable"), dataTable:ipdataTableOpt});
			},
			onCheck: function (row) {
				modifyDelStyle('table', 'deleteNas');
				nasData.push(row.nas_uuid)
			},
			onUncheck: function (row) {
				modifyDelStyle('table', 'deleteNas');
				var index = nasData.indexOf(row.nas_uuid); // 查找元素的索引
				if (index !== -1) {
					nasData.splice(index, 1); // 从数组中删除一个元素
				}
			},
			onCheckAll:function (row) {
				modifyDelStyle('table', 'deleteNas');
				for (var i = 0; i < row.length; i++) {
					var index = nasData.indexOf(row[i].user_uuid); // 查找元素的索引
					if (index == -1) {
						nasData.push(row[i].user_uuid)
					}
				}
			},
			onUncheckAll: function (row) {
				modifyDelStyle('table', 'deleteNas');
				for (var i = 0; i < row.length; i++) {
					var index = nasData.indexOf(row[i].user_uuid); // 查找元素的索引
					if (index != -1) {
						nasData.splice(index, 1); // 从数组中删除一个元素
					}
				}
			},

		}
		sessionStorage.removeItem("table_pageRecord");
		$('#table').baseTableConfig().init(options);
		initTableListener();
	}

	var opEvent = {
		'click .eachMount': function (event, value, row, index) {
			checkOperateAuth({
				type: 2,
				source_uuid: row.nas_uuid,
				source_type: 57
			},function(){
				$('#nasMountModal').modal({'width':"500px", 'height': "150px"});
				$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"getAllNodes",p:{}},function(d){
					var data = JSON.parse(d);
					var option='';
					var mount_list = [];
					row.mount_list.forEach(item => {
						mount_list.push(item.uuid);
					});
					$('#nodesMountSelect').html('');
					for(var i=0;i<data.length;i++){
						var disableFlag = "";//判断节点是否已经挂载，已经挂载禁用
						if($.inArray(data[i].value,mount_list) != -1) {
							disableFlag = "disabled";
						}
						option = '<option '+ disableFlag +' value="'+ data[i].value +'">'+ data[i].name +'</option>'
						$('#nodesMountSelect').append(option);
					}
					//初始化BS select控件
					$('#nodesMountSelect').selectpicker({
						iconBase: 'fa',
						tickIcon: 'fa-check',
						noneSelectedText: LANG.BILLING_PLEASE_SELECT,
						deselectAllText: LANG.BILLING_DESELECT_ALL,
						selectAllText: LANG.BILLING_SELECT_ALL,
						liveSearchPlaceholder: LANG.BILLING_SEARCH,
					});
					$('#nodesMountSelect').selectpicker('refresh');
					$('#mountsubmit').off().on('click', function (){
						mountsubmit(row)
					});
				});
			});
		},
		'click .eachUnmount': function (event, value, row, index) {
			checkOperateAuth({
				type: 2,
				source_uuid: row.nas_uuid,
				source_type: 57
			},function(){
				$('#nasUmountModal').modal({'width':"500px", 'height': "150px"});
				$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"getAllNodes",p:{}},function(d){
					var data = JSON.parse(d);
					var option='';
					var mount_list = [];
					row.mount_list.forEach(item => {
						mount_list.push(item.uuid);
					});
					$('#nodesUmountSelect').html('');
					for(var i=0;i<data.length;i++){
						var disableFlag = "";//判断节点是否已经解挂，已经解挂禁用
						if($.inArray(data[i].value,mount_list) == -1) {
							disableFlag = "disabled";
						}
						option = '<option '+ disableFlag +' value="'+ data[i].value +'">'+ data[i].name +'</option>'
						$('#nodesUmountSelect').append(option);
					}
					//初始化BS select控件
					$('#nodesUmountSelect').selectpicker({
						iconBase: 'fa',
						tickIcon: 'fa-check',
						noneSelectedText: LANG.BILLING_PLEASE_SELECT,
						deselectAllText: LANG.BILLING_DESELECT_ALL,
						selectAllText: LANG.BILLING_SELECT_ALL,
						liveSearchPlaceholder: LANG.BILLING_SEARCH,
					});
					$('#nodesUmountSelect').selectpicker('refresh');
					$('#umountsubmit').click(function() {
						umountsubmit(row)
					});
				});
				$('#nodesUmountSelect').val("");
			});
			
		},
	}

	var initTableListener = function () {
		// $('#nasAuth').on('click',function(){
		// 	var data = ipGrid.getDataTable().data();
		// 	if(data.length == 0) {
		// 		UIToastr.showWarning(LANG.UI_NAS_MANAGE_NOT_ADD_DEVICE, LANG.UI_NAS_MANAGE_ADD_DEVICE_TIP);
		// 		return;
		// 	}
		// 	initNasInfo(data);//初始化已用未用总数
		// 	$('#nasAuthModal').modal({'width':"750px", 'height': "350px"});
		// });
		$('#vin_nas_toolbar .b-btn.search-btn').on('click',function(){
			$('#table').bootstrapTable('refresh', {
				query: {
					"search":$.trim($('#vin_nas_toolbar .nasSearch').val())
				}
			});
		});
		$('#vin_nas_toolbar .tableclear').on('click',function(){
			$('#vin_nas_toolbar .nasSearch').val('');
			$('#table').bootstrapTable('refresh', {
				query: {
					"search": ''
				}
			});
		});

		//添加nas设备
		$('#addNasDevice').on('click', function(){
			EDIT_NAS_FLAG = false;
			$('#addsubmit').show();
			$('#editsubmit').hide();
			initModal();
		});
		//修改nas设备
		$('#editNas').on('click', function(){
			let select = $('#table').bootstrapTable('getSelections');
			if(select.length != 1){
				UIToastr.showInfo(LANG.UI_NAS_MANAGE_MODIFY_DEVICE,LANG.UI_NAS_MANAGE_SELECT_TO_MODIFY);
				return false;
			}
			checkOperateAuth({
				type: 2,
				source_uuid: select.map(row => row.nas_uuid).join(','),
				source_type: 57
			},function(){
				EDIT_NAS_FLAG = true;
				$('#addsubmit').hide();
				$('#editsubmit').show();
				initModal(select[0].nas_uuid); 
			});
			
		});
		//删除nas设备
		$('#deleteNas').on('click', function(){
			var select = $('#table').bootstrapTable('getSelections');
			if(select.length == 0){
				UIToastr.showInfo(LANG.UI_NAS_MANAGE_DELETE,LANG.UI_NAS_MANAGE_SELECT_TO_DELETE);
				return false;
			}
			checkOperateAuth({
				type: 2,
				source_uuid: select.map(row => row.nas_uuid).join(','),
				source_type: 57
			},function(){
				bootbox.dialog({
					title: LANG.UI_NAS_MANAGE_DELETE,
					message: LANG.UI_NAS_MANAGE_DELETE_CONFIRE,
					buttons: {
						cancel: {
							label: LANG.UI_GLOBAL_STRATEGY_DISPENSE_CANCEL,
							className: 'btn-default',
							callback: function(){
							}
						},
						ok: {
							label: LANG.UI_PUBLIC_CONFIRM,
							className: 'btn-primary',
							callback: debounce(function(){
								deleteSubmit(select);
							}, 300),
						}
					}
				});
			});
		});

	}

	var mountsubmit = function (row) {
		//选中之前挂载成功的
		var params = {info:{}};
		params.info.nas_uuid = [];
		params.node_mount_list = [];
		params.info.nas_uuid.push(row.nas_uuid);
		params.info.editFlag = 2,
			params.node_mount_list = $('#nodesMountSelect').val();
		if(params.node_mount_list.length == 0) {
			UIToastr.showWarning(LANG.UI_NAS_MANAGE_DEVICE_MOUNT,LANG.UI_NAS_MANAGE_DEVICE_MOUNT_TIPS);
			return;
		}
		Metronic.blockUI({target: '#nasMountModal',animate: true});
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"mountNas",p:params},function(d){
			if(OPREL(d)){
				//成功模态框消失，显示列表
				$('#nasMountModal').modal('hide');
				$('#table').bootstrapTable('refresh');
			}
			Metronic.unblockUI('#nasMountModal');
		});
	}

	var umountsubmit = function (row) {
		//选中之前挂载成功的
		var params = {info:{}};
		params.info.nas_uuid = [];
		params.node_mount_list = [];
		params.info.nas_uuid.push(row.nas_uuid);
		params.node_mount_list = $('#nodesUmountSelect').val();
		if(params.node_mount_list.length == 0) {
			UIToastr.showWarning(LANG.UI_NAS_MANAGE_DEVICE_UMOUNT,LANG.UI_NAS_MANAGE_DEVICE_UMOUNT_TIPS);
			return;
		}
		Metronic.blockUI({target: '#nasUmountModal',animate: true});
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"umountNas",p:params},function(d){
			if(OPREL(d)){
				//成功模态框消失，显示列表
				$('#nasUmountModal').modal('hide');
				$('#table').bootstrapTable('refresh');
			}
			Metronic.unblockUI('#nasUmountModal');
		});
	}
	//删除nas设备
	var deleteSubmit = function(select){
		 var data = {};
		data.nas_uuid = [];
		 select.forEach(item => {
			 data.nas_uuid.push(item.nas_uuid);
		 });
         var p = JSON.stringify(data);
         Metronic.blockUI({target: '.nas-manager-table-container',animate: true});
     	$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"delNasDevice",p:p},function(d){
     		Metronic.unblockUI('.nas-manager-table-container');
     		if(OPREL(d)){
     			//删除成功删除列表
     			$('#table').bootstrapTable('refresh');
				ipGrid.getRefresh({});
     		}
     	});
	}
	//授权
	// $('#authClient').on('click', function(){
	// 	$('#nasAuthModal').modal({'width':"750px", 'height': "350px"});
	// 	initNasAuth('1');
		
	// });
	// $('#authClientRemove').on('click', function(){
	// 	$('#nasAuthModal').modal({'width':"750px", 'height': "350px"});
	// 	initNasAuth('2');
	// });


	//按ip授权
	// var initNasAuth = function(flag){
	// 	var params = {info:{}};
	// 	params.info.nas_ip_list = [];
	// 	params.info.nas_auth_flag = flag;
	// 	var data = ipGrid.getSelectedRows();
	// 	if(data.length == 0 && flag == "1"){
	// 		UIToastr.showInfo(LANG.UI_CLIENT_NAS_AUTH,LANG.UI_CLIENT_CANCLE_AUTH_IP_TIPS);
	// 		return false;
	// 	} else if (data.length == 0 && flag == "2"){
	// 		UIToastr.showInfo(LANG.UI_CLIENT_NAS_AUTH,LANG.UI_CLIENT_CANCLE_AUTH_TIPS);
	// 		return false;
	// 	}
		
	// 	for(var i=0; i<data.length;i++){
	// 		params.info.nas_ip_list.push(data[i]);
	// 	}
	// 	var p = JSON.stringify(params);
	// 	Metronic.blockUI({target: '#nasIpDatatable',animate: true});
    //  	$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"nasLisence",p:p},function(d){
	// 		Metronic.unblockUI('#nasIpDatatable');
    //  		if(OPREL(d)){
	// 			$('#nasAuthModal').modal('hide');
    //  			$('#table').bootstrapTable('refresh');
	// 			ipGrid.getRefresh({});
    //  		}
    //  	});
	// }




    //添加事件
    var addListeners = function(){
    	//切换页数保存到cookie
		$('select[name=datatable_length]').on('change', function(){
			pageLength.nas = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
    	//定义iCheck样式
    	$('#addNasModal input[type=checkbox]').iCheck({
    	    checkboxClass: 'icheckbox_square-blue',
    	    radioClass: 'iradio_square-blue',
    	    increaseArea: '20%' // optional
    	});
		$(' input[type=radio]').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
    	
    	
    	$('#ipaddress').on('change',function(){
	    	$('#nickname').val(this.value);
	    });
		//选择协议类型复选框
		$('#useMode').find('input').on('ifClicked', useModeClick);
		$('#diynasversion').on('click',changeToInput);
		$('#selectversion').on('click',changeToSelect);
		initSystemLicense();//获取系统授权类型

		// nas_manager_tip 关闭时动态设置表格高度
		$('#nas_manager_tip_close').on('click', () => {
			$('.resource-manager-wrap__content').css('padding-bottom', 0);

			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.nas-manager-table-container').css('height', 'calc(100% - 46px)');
		});
		$('#vendor').on('change', vendorChange);
    }

	const vendorChange = function(){ 
		let vendor = $('#vendor').val();
		if (vendor != 0) {
			$('.vendor-info').show();
		} else {
			$('.vendor-info').hide();
		}
	}
	var initSystemLicense = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemLicenseType',p:{}}, function(d){
			var data = JSON.parse(d);
			_licenseType = data.licensetype;
			initDataTable();
		});
	}
	var toAddTableBtn = function (flag) {
		var afterDiv = '';
		var beforeDiv = '';
		if (flag == 'beforeDiv') {
			if ($.inArray('p_nasmanager_delete', CONF.PERMISSION_ARR) !== -1) {
				// 删除
				beforeDiv = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteNas"></button></div>`
			}
			return beforeDiv;
		} else {
			if ($.inArray('p_nasmanager_add', CONF.PERMISSION_ARR) !== -1) {
				// 新建
				afterDiv += `<button class="btn table-toolbar-btn" id="addNasDevice" data-toggle="drawer" data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false" >
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
			}
			if ($.inArray('p_nasmanager_edit', CONF.PERMISSION_ARR) !== -1) {
				// 修改
				afterDiv += `<button class="btn table-toolbar-btn" id="editNas" >
                                <i class="viconfont vicon-xiugai mr4"></i>
                                <span>` + LANG.UI_JOB_MODIFY + `</span>
                            </button>`;
			}
		
			return afterDiv != '' ? '<div>' + afterDiv + '</div>' : '';
		}
	}

	var changeToInput = function () {
		$('.selecversiondiv').hide();
		$('#inputversiondiv').show();
		$('#inputversion').val('');
		$('#nasversion').val('');
		selectVersionFlag = true;
	}
	var changeToSelect = function () {
		$('.selecversiondiv').show();
		$('#inputversiondiv').hide();
		$('#inputversion').val('');
		$('#nasversion').val('');
		selectVersionFlag = false;
	}
	var useModeClick = function(usemode){
		var mode = $(this).data('mode') ?? usemode;
		if(mode==1) {
			$('.cifsdiv').show();
			$('.nfsdiv').hide();
			// cifs协议展示的协议版本有2.0，3.0
			$('#nasversion').html('<option value="">'+ LANG.UI_NAS_DEFAULT_VERSION +'</option><option value="2.0">v2.0</option><option value="3.0">v3.0</option>');
		}else{
			$('.cifsdiv').hide();
			$('.nfsdiv').show();
			// nfs协议展示的协议版本有3.0，4.0，4.1
			$('#nasversion').html('<option value="">'+ LANG.UI_NAS_DEFAULT_VERSION +'</option><option value="3">v3</option><option value="4.0">v4.0</option><option value="4.1">v4.1</option>');
		}
	}

    var initNasType = function () {
		$('#useMode').find('input[data-mode=1]').iCheck("check");//默认选中cifs
		$('#writereadMode').find('input[data-mode=1]').iCheck("check");//默认选中读写
		$('#authways').find('input[data-mode=1]').iCheck("check");//默认选中按个数
	 }
	
    
    
    
    //初始化添加模态框
    var initModal = function(edituuid=''){
		selectVersionFlag = false;
    	//初始化表单数据，清除上一次验证结果
		$('#useMode input[name="radiobox"][data-mode="1"]').iCheck('check');
		$('#useMode input[name="radiobox"]').attr("disabled",false);
		$('#vendor').attr("disabled",false);
		$('.cifsdiv').show();
		$('.nfsdiv').hide();
		// cifs协议展示的协议版本有2.0，3.0
		$('#nasversion').html('<option value="">'+ LANG.UI_NAS_DEFAULT_VERSION +'</option><option value="2.0">v2.0</option><option value="3.0">v3.0</option>');
		$('#writereadMode input[name="icheckbox"][data-mode="1"]').iCheck('check');
		$('#ipaddress').val("").attr("disabled",false).siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#adminname').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#rest_ip').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#rest_username').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#rest_password').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#rest_vstoreid').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#password').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#naspath').val("").attr("disabled",false).siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#config').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#inputversion').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$('#nickname').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
    	$('#nasport').val("").siblings("i").removeClass().addClass("fa").parents(".form-group.simpleDiv").removeClass().addClass("form-group simpleDiv");
		$("#nasversion").val("");
		$('#addNasModal').modal({'width': "785px", 'height': "500px"});
		if(EDIT_NAS_FLAG){ 
			initEditModal(edituuid);
		} else { 
			changeToSelect();
			initAllnodeSelect();
		}
    }
	//初始化修改模态框
    var initEditModal = function(edituuid){
		var title = `<i class="viconfont vicon-xiugai mr5"></i><span>` + LANG.UI_NAS_MANAGE_MODIFY_DEVICE + `</span>`;
		var data = $('#table').bootstrapTable('getData');
    	for(var i=0;i<data.length;i++){
    		//找到对应nas设备显示相应配置信息
    		if(edituuid == data[i].nas_uuid){
    			var info = data[i];
				let mode = info.nas_type_value == 6 ? 2 : 1;//nfs6  cifs7
				let permission_flag = info.permission_flag == LANG.UI_NAS_MANAGE_WRITE_AND_READ ? 1 : 2;
    			$('#edit_nas_uuid').val(info.nas_uuid);
				$('#useMode input[name="radiobox"][data-mode="'+ mode +'"]').iCheck('check');
				$('#useMode input[name="radiobox"]').attr("disabled",true);
				useModeClick(mode);
				$('#vendor').attr("disabled",true);
				initAllnodeSelect(info.detail.mount_list);
				$('#ipaddress').val(info.ip).attr("disabled",true);
				$('#naspath').val(info.share_path).attr("disabled",true);
    			$('#nickname').val(info.nickname);
				$("#nasport").val(info.detail.port > 0 ? info.detail.port : "");
				$("#config").val(info.mount_params);
				$('#vendor').val(info.vendor);
				vendorChange();
				if (info.vendor != 0 && info.detail.detail) {
					$('#rest_ip').val(info.detail.detail.rest_ip);
					$('#rest_username').val(info.detail.detail.rest_username);
					$('#rest_password').val(info.detail.detail.rest_password);
					$('#rest_vstoreid').val(info.detail.detail.rest_vstoreid);
				}
				if(info.nas_type_value== 6) {//nfs
					$('.nfsdiv').show();
					$('.cifsdiv').hide();
				}else{//cifs
					$('.nfsdiv').hide();
					$('.cifsdiv').show();
					$("#adminname").val(info.username);
					$("#password").val(info.detail.password);
				}
				$('#nasversion').val(info.detail.nas_version);
				if (info.detail.nas_version != "" && !$('#nasversion').val()) {
					changeToInput();
					$('#inputversion').val(info.detail.nas_version);
				}
				//读写权限
				$('#writereadMode').find('input[data-mode="'+ permission_flag +'"]').iCheck("check");
				$('#addTitle').html(title);
    		}
    	}
    }
	var initAllnodeSelect = function (mount_list = []) { 
		$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"getAllNodes",p:{}},function(d){
			var data = JSON.parse(d);
			var option='';
			$('#nodesSelect').html('');
			for(var i=0;i<data.length;i++){
                option = '<option value="'+ data[i].value +'">'+ data[i].name +'</option>'
				$('#nodesSelect').append(option);
			}
			//初始化BS select控件
		    $('#nodesSelect').selectpicker({
			    iconBase: 'fa',
			    tickIcon: 'fa-check',
				noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			    deselectAllText: LANG.BILLING_DESELECT_ALL,
			    selectAllText: LANG.BILLING_SELECT_ALL,
			    liveSearchPlaceholder: LANG.BILLING_SEARCH,
		    });
			//默认选中第一个
			if (!EDIT_NAS_FLAG) {
				document.getElementById("nodesSelect").options.selectedIndex = 0;
				$('#nodesSelect').attr("disabled",false);  
			} else { 
				$('#nodesSelect').selectpicker('val', mount_list);
				$('#nodesSelect').attr("disabled",true);
			}
			$('#nodesSelect').selectpicker('refresh');
		});
	 }
    
    //添加nas设备确认
    var addNasDeviceSubmit = function(){
    	var data = {info:{}};
		data.info.port = $('#nasport').val();
		data.info.username = $('#adminname').val();
    	data.info.passwd = btoa($('#password').val());
		if($('#cifsCheck').is(':checked')){
			data.info.nas_type = 7;
		}else if($('#nfsCheck').is(':checked')){
			data.info.nas_type = 6;
			data.info.username = '';
			data.info.passwd = '';
		}else {
			UIToastr.showWarning(LANG.UI_NAS_MANAGE_SELECT_PROTOCOL_TYPE, LANG.UI_NAS_MANAGE_SELECT_PROTOCOL_TYPE_ONE);
			return false;
		}
		if($('#writereadCheck').is(':checked')){
			data.info.permission_flag = 1;//读写
		}else if($('#readCheck').is(':checked')){
			data.info.permission_flag = 2;//只读
		}
		data.info.version = $('#nasversion').val();
		if(selectVersionFlag) {
			data.info.version = $('#inputversion').val();
		}
		
		data.info.ip = $('#ipaddress').val();
		data.info.nickname = $('#nickname').val();
		data.info.share_path = $('#naspath').val();
		data.info.mount_params = $('#config').val();
		// data.info.nardir = $('#nardir').val();
		data.node_mount_list = $('#nodesSelect').val();
		data.info.vendor = $('#vendor').val();
		if (data.info.vendor != 0) {
			data.info.rest_ip = $('#rest_ip').val();
			data.info.rest_username = $('#rest_username').val();
			data.info.rest_password = btoa($('#rest_password').val());
			data.info.rest_vstoreid = $('#rest_vstoreid').val();
		} else {
			data.info.rest_ip = ""; 
			data.info.rest_username = ""; 
			data.info.rest_password = "";  
			data.info.rest_vstoreid = ""; 
		}
		
		if(data.node_mount_list.length==0) {
			UIToastr.showWarning(LANG.UI_NAS_MANAGE_ADD_DEVICE+LANG.UI_DATACENTER_FAILURE, LANG.UI_NAS_MANAGE_AT_LEAST_ONE_NODE);
			return;
		}
    	var p = JSON.stringify(data);
    	Metronic.blockUI({target: '#addNasModal',animate: true});
    	$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"addNasDevice",p:p},function(d){
    		Metronic.unblockUI('#addNasModal');
			var d = JSON.parse(d);
			if(d.re) {
				$('#addNasModal').modal('hide');
				UIToastr.showSuccess(LANG.UI_NAS_MANAGE_ADD_DEVICE_NEW,LANG.UI_NAS_MANAGE_ADD_DEVICE_SUCCESS);
			}else {
				if(CONF.TENANTUUID) {
					UIToastr.showWarning(LANG.UI_NAS_MANAGE_ADD_DEVICE_NEW,d.msg);
					return;
				}
				UIToastr.showWarning(LANG.UI_NAS_MANAGE_ADD_DEVICE_NEW,LANG.UI_NAS_MANAGE_ADD_DEVICE_FAIL+ ', ' + d.msg +LANG.UI_NAS_MANAGE_ADD_DEVICE_FAIL_TIPS);
			}
			$('#table').bootstrapTable('refresh');
			ipGrid.getRefresh({});
    	})
    }
    
    //修改nas设备确认
    var editNasSubmit = function(){
		var edituuid = $('#edit_nas_uuid').val();
    	var params = {info:{}};
		var data = $('#table').bootstrapTable('getData');
		for(var i=0;i<data.length;i++) {
			if(data[i].nas_uuid==edituuid) {
				var row = i;
			}
		}
		if($('#writereadCheck').is(':checked')){
			params.info.permission_flag = 1;//读写
		}else if($('#readCheck').is(':checked')){
			params.info.permission_flag = 2;//只读
		}
    	params.info.nas_uuid = edituuid;
    	params.info.nickname = $('#nickname').val();
		params.mount_list = data[row].detail.mount_list
		params.nas_type = data[row].nas_type_value;
		params.info.username = $('#adminname').val();
		if($('#password').val() == data[row].detail.password) {
			//未修改密码,传空，空值后台不会做修改
			params.info.passwd = "";
		} else {
			params.info.passwd = btoa($.trim($('#password').val()));
		}
		params.info.version = $('#nasversion').val();
		if(selectVersionFlag) {
			params.info.version = $('#inputversion').val();
		}
		params.info.port = $('#nasport').val();
		params.info.mount_params = $('#config').val();
		params.info.vendor = $('#vendor').val();
		if (params.info.vendor != 0) {
			params.info.rest_ip = $('#rest_ip').val();
			params.info.rest_username = $('#rest_username').val();
			if (data[row].detail.detail && $('#rest_password').val() == data[row].detail.detail.rest_password) {
				//未修改密码,传空，空值后台不会做修改
				params.info.rest_password = $('#rest_password').val();
			} else {
				params.info.rest_password = btoa($('#rest_password').val());
			}
			params.info.rest_vstoreid = $('#rest_vstoreid').val();
		} else {
			params.info.rest_ip = ""; 
			params.info.rest_username = ""; 
			params.info.rest_password = "";  
			params.info.rest_vstoreid = ""; 
		}
    	var p = JSON.stringify(params);
		//检测是否修改了除了别名之外的信息
		Metronic.blockUI({target: '#addNasModal',animate: true});
		$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"checkEditNasParams",p:p},function(flag){
			var info = JSON.parse(flag)
			if(info.flag){//修改了其他信息提示会重新挂载
				bootbox.confirm({
					title: LANG.UI_NAS_MANAGE_MODIFY_DEVICE,
					message: LANG.UI_NAS_MANAGE_DEVICE_REMOUNT,
					callback: debounce(function(r) {
						if(!r) {
							Metronic.unblockUI('#addNasModal');
							return;
						}
						$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"editNasDevice",p:p},function(d){
							if(OPREL(d)){
								//成功模态框消失，显示列表
								$('#addNasModal').modal('hide');
								$('#table').bootstrapTable('refresh');
							}
							Metronic.unblockUI('#addNasModal');
						});
					}, 300),
				});
			}else {//只修改了别名  不提示
				$.post(CONF.AJAXPATH,{m: CONF.M.NAS,f:"editNasDevice",p:p},function(d){
					if(OPREL(d)){
						Metronic.unblockUI('#addNasModal');
						//成功模态框消失，显示列表
						$('#addNasModal').modal('hide');
						$('#table').bootstrapTable('refresh');
					}
				});
			}
    	});
    }

    //添加/修改nas设备数据格式校验
    var nastValidata = function() {
        var nasForm = $('#nasForm');
        nasForm.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
				rest_ip: {
            		rest_ip_input: true
            	},
				rest_username: {
            		rest_ip_input: true
            	},
				rest_password: {
            		rest_ip_input: true
            	},
				rest_vstoreid: {
            		rest_ip_input: true
            	},
            	ipaddress: {
            		required: true,
            		// ipv4: true
            	},
				naspath: {
					required: true,
					naspath: true,
				},
                nickname: {
                	required: false,
                },
				nasport: {
					nasport: true
				},
				inputversion: {
					inputversion:true
				},
				nodesSelect: {
					required: true,
				}
                
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");  
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group   
            },

            unhighlight: function (element) { // revert the change done by hightlight
                
            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {
                
            }
            
        });
	
        //路径格式检测
		$.validator.addMethod("naspath", function(value, element) {
			value = value.replace(/\s+/g, '');
			return this.optional(element) || /^\/[^ ]*\/?$/.test(value);
		}, LANG.UI_NAS_MANAGE_INPUT_CORRECT_PATH);
        //IP验证格式
        $.validator.addMethod("ipv4", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_IP);
        
        //端口验证格式
        $.validator.addMethod("nasport", function(value, element) {
//			if(!$(element).closest('.form-group').hasClass('has-success'))return false;
			if(value >= 0 && value <= 65535){
				return true;
			}
			return false;
	    }, LANG.UI_NODE_PORT_TIPS);
        //协议版本只能输入数字
    	//IP验证格式
        $.validator.addMethod("inputversion", function(value, element) {
        	return this.optional(element) || /^\d+(\.\d{1,1})?$/.test(value);
        }, LANG.UI_NAS_MANAGE_INPUT_NUM_RULE);
		//选择了厂商，判断ip 账号 密码不为空
		$.validator.addMethod("rest_ip_input", function(value, element) {
        	if ($('#vendor').val() != 0) {
				value = value.replace(/\s+/g, '');
				return value != "";
			} else {
				return true;
			}
        }, LANG.UI_NAS_MANAGE_INPUT_NOT_NULL);
      //添加nas设备确认
    	$('#addsubmit').on('click', function(){
    		if (nasForm.validate().form()) {
    			addNasDeviceSubmit();
            }
    	});	
		//修改nas设备确认
    	$('#editsubmit').on('click', function(){
    		if (nasForm.validate().form()) {
    			editNasSubmit();
            }
    	});
        
	};
    return {
        //main function to initiate the module
        init: function () {
            addListeners();
            nastValidata();	//添加nas设备表单数据格式验证
			initNasType();
        }

    };

}();

jQuery(document).ready(function() {   
	NasManager.init();
});