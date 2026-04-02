//角色管理
var RoleList = function(){
	var grid;
	var user_list =[], user_group_list = [];
	var permissionTree;
	var usergrid, userGroupgrid;
	var initGridFlag = false;
	var initUsergroupTableFlag = false;
	var initUserTableFlag = false;

	var tipEditRole = function(){
		UIToastr.showInfo(LANG.UI_ROLE_MODIFY, LANG.UI_ROLE_MODIFY_NO_SELECT);
	}
	//添加监听事件
	var addListeners = function(){
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH
		});


		//添加角色和用户关联
		$('#userSubmit').on('click', function(){
			allocationSubmit();
		});

		//添加角色和用户组关联
		$('#userGroupSubmit').on('click', function(){
			usergroupSubmit();
		});

		//切换tab事件
		$('.allocationHref').on('click', function(){
			if($('#allousertab').hasClass('active')){
				$('#userGroupSubmit').show();
				$('#userSubmit').hide();
			}else{
				$('#userSubmit').show();
				$('#userGroupSubmit').hide();
			}
		});

		// 	新表格
		// 	删除
		$('#vin_role_toolbar #delete_role').on('click', function (){
			if (checkAuth('delete')) {
				checkOperateAuth(checkAuth('delete'), delRole)
			}
		});

		// 新建
		$('#vin_role_toolbar #add_role').on('click', function (){
			clickEffect(this);
			AddEditRole.init({});
			$('#role_add_edit_drawer').drawer('show');
			// LOCATION('./content/platform/users/add_role.php','safety');
		});
		// 修改
		$('#vin_role_toolbar #edit_role').on('click', function (){
			if (checkAuth('edit')) {
				checkOperateAuth(checkAuth('edit'), modifyRole)
			}
		});
		// 启用
		$('#vin_role_toolbar #unlock').on('click', function (){
			if (checkAuth('unlock')) {
				checkOperateAuth(checkAuth('unlock'), unlockRole)
			}
		});
		// 禁用
		$('#vin_role_toolbar #lock').on('click', function (){
			if (checkAuth('lock')) {
				checkOperateAuth(checkAuth('lock'), lockRole)
			}
		});
		//分配角色
		$('#allocation').on('click', function (){
			if (checkAuth('allocate')) {
				checkOperateAuth(checkAuth('allocate'), allocationRole)
			}
		});
	}

	// 操作权限校验
	var checkAuth = function(operationType) {
		var select = $('#role_table').bootstrapTable('getSelections');
		if (!select.length) {
			switch (operationType){
				case 'edit':
					tipEditRole();
					break;
				case 'unlock':
					tipunlockRole();
					break;
				case 'lock':
					tiplockRole();
					break;
				case 'allocate':
					tipallocateRole();
					break;
				case 'delete':
					tipDeleteRole();
			}
			return false;
		}
		return {
			type: 1,
			user_uuid: [...new Set(select.map(item => item.user_uuid))].join(','),
			auth: ''
		};
	}


	//添加角色和用户关联确认
	var allocationSubmit = function(){
		var data = {};
		data.roleuuid = $('#roleuuid').val();
		data.userList = $('#selectUser').selectpicker('val');
		if(data.userList.length == 0){
			return UIToastr.showInfo(LANG.UI_ROLE_ALLOCATION_TO_USER, LANG.UI_ROLE_ALLOCATION_TO_USER_SELECT);
		}
		pAjaxRequest(data,'/api/v1/roles/user/allocation','POST',function (res){
			if (operateResponseList(res, '')) {
				$('#allocationModal').modal('hide');
			}
		});
	}


	//添加角色和用户组关联确认
	var usergroupSubmit = function(){
		var data = {};
		data.roleuuid = $('#roleuuid').val();
		data.userGroupList = $('#selectUsergroup').selectpicker('val');
		if(data.userGroupList.length == 0){
			return UIToastr.showInfo(LANG.UI_ROLE_ALLOCATION_TO_USER_GROUP, LANG.UI_ROLE_ALLOCATION_TO_USER_GROUP_SELECT);
		}
		pAjaxRequest(data,'/api/v1/roles/usergroup','POST',function (res){
			if (operateResponseList(res, '')) {
				$('#allocationModal').modal('hide');
			}
		});

	}

	var initPermissionTree = function(p){
		pAjaxRequest(p,'/api/v1/roles/permissiontree','GET',function (d){
			var zNodes = d.data;
			if(zNodes == "[]"){
				$('#permission_tree').hide();
				$('#nodatatips').show();
			}else{
				$('#permission_tree').show();
				$('#nodatatips').hide();
			}
			var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						idKey: "id",
						pIdKey: "pid",
						rootPId: 0
					},
					key: {
						title: "title"
					}
				},
				view: {
					showIcon : false,
					nameIsHTML: true
				},
				callback: {
				}
			};
			var nodes = zNodes;
			permissionTree = $.fn.zTree.init($("#permission_tree"), setting, nodes);

		});
	}

	//加载关联用户
	var initUserTable = function(data){
		var options = {
			vin_url: '/api/v1/roles/user',
			vin_method: 'GET',
			vin_params: function () {
				var param = {};
				param.roleuuid = data.roleuuid;
				param.offset = 0;
				return param;
			},
			showExport: false, //是否开启导出按钮
			showColumns: false, //是否开启列选择按钮
			// onResetView: initTableHeight,
			customTool: {

			},
			columns: [ //列定义
				{
					field: 'id', //字段名
					title: LANG.UI_REPORT_NUMBER,
					sortable: false
				},
				{
					field: 'username',
					title: LANG.UI_MICROSOFT365_USER_NAME,
					sortable: false
				}
			]
		}
		if(!initUserTableFlag){
			$('#user_table').baseTableConfig().init(options);
			initUserTableFlag = true;
		}else{
			//如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#user_table').bootstrapTable( 'destroy');
			sessionStorage.removeItem('user_table_pageRecord');
			$('#user_table').baseTableConfig().init(options);
		}
	}

	//加载关联用户组
	var initUsergroupTable = function(data){
		var options = {
			vin_url: '/api/v1/roles/roleusergroup',
			vin_method: 'GET',
			vin_params: function () {
				var param = {};
				param.roleuuid = data.roleuuid;
				param.offset = 0;
				return param;
			},
			showExport: false, //是否开启导出按钮
			showColumns: false, //是否开启列选择按钮
			// onResetView: initTableHeight,
			customTool: {

			},
			columns: [ //列定义
				{
					field: 'id', //字段名
					title: LANG.UI_REPORT_NUMBER,
					sortable: false
				},
				{
					field: 'user_group_name',
					title: LANG.UI_USER_GROUP_NAME,
					sortable: false
				}
			]
		}
		if (!initUsergroupTableFlag) {
			$('#usergroup_table').baseTableConfig().init(options);
			initUsergroupTableFlag = true;
		}else{
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#usergroup_table').bootstrapTable('destroy');
			sessionStorage.removeItem('usergroup_table_pageRecord');
			$('#usergroup_table').baseTableConfig().init(options);
		}
	}

	//初始化详情模态框
	var initModalDetails = function(row){
		var data = {};
		data.roleuuid = row.role_uuid;
		initPermissionTree(data);
		initUserTable(data);
		initUsergroupTable(data);
		initGridFlag = true;
		$('#rolePermissionDiv').modal({'width': '600px', height: '430px'});
	}

	var detailHref = function(div, data){
		var detailshtml = "<a class='roleDetails' id='" + data[6] + "'>" + data[1] + "</a>";
		$(div).html(detailshtml);
	}

	//初始化网页表格数据
	var handleRecords = function(){
		var operates = {
			'click .roleDetails' :function (event, value, row, index){
				initModalDetails(row);
			}
		};
		//表格初始化配置项
		let options = {
			toolbarId: '#vin_usergroup_toolbar',
			buttonsToolbar: '#vin_usergroup_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/roles',
			vin_method: 'GET',
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			fullPage:true,
			onResetView: initTableHeight,
			onCheck: function () {
				modifyDelStyle('role_table', 'delete_role');
			},
			onUncheck: function (){
				modifyDelStyle('role_table', 'delete_role');
			},
			onCheckAll: function () {
				modifyDelStyle('role_table', 'delete_role');
			},
			onUncheckAll: function () {
				modifyDelStyle('role_table', 'delete_role');
			},
			fileName:LANG.UI_ROLE_LIST,
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
					field: 'role_name',
					title: LANG.UI_ROLE_NAME,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter:function (value, row, index, field){
						var nameStr = "<a class='roleDetails' id='" + row.role_uuid + "'>" + row.role_name + "</a>";
						return nameStr;
					},
					events:operates,
				},
				{
					field: 'lock_flag',
					title: LANG.UI_BLACK_WHITE_STATUS,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (value, row, index, field) {
						if (value == 1) {
							return '<span class="label label-sm label-success status-icon">' + LANG.UI_BLACK_WHITE_ENABLE + '</span>'
						} else {
							return '<span class="label label-sm label-danger status-icon"  style="width:auto; min-width:40px">' + LANG.UI_BLACK_WHITE_DISABLE + '</span>';
						}
					}
				},
				{
					field: 'tenant_name',
					title: LANG.UI_TENANT_NAME,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'create_user',
					title: LANG.UI_AGENT_POOL_TABLE_CREATOR,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'create_time',
					title: LANG.UI_GLOBAL_STRATEGY_EDIT_DATE,
					sortable: false, //默认可排序，禁用排序才写此项
				},
			],
		}
		sessionStorage.removeItem('role_table_pageRecord');
		$('#role_table').baseTableConfig().init(options);
	}

	var checkEvent = function (tableId, btnId) {
		let select = $('' + tableId + '').bootstrapTable('getSelections');
		if (select.length == 0) {
			$('' + btnId + '').addClass('icon-gray-delete');
			$('' + btnId + '').removeClass('icon-white-delete');
			$('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
		} else {
			$('' + btnId + '').removeClass('icon-gray-delete');
			$('' + btnId + '').addClass('icon-white-delete');
			$('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
		}
	}

	function initTableHeight() {
		//拿到父窗口的高度
		var height;
		var panelH = window.innerHeight;

		height = panelH - 381;

		$("#userMangerDiv .fixed-table-body").css({
			"height": height
		});
	}

	//检查是否有默认角色
	var checkDefaultRole = function(list){
		var data = grid.getDataTable().data();
		if(data.length !=0){
			for(var i=0;i<list.length;i++){
				for(var j=0;j<data.length;j++){
					if(list[i] == data[j][6] && data[j][8] == "") return false;	//如果检查出默认角色直接返回
				}
			}
		}

		return true;
	}

	//修改角色
	var modifyRole = function(){
		clickEffect(this);
		let select = $('#role_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].role_uuid);
		}

		if(!select.length){
			return UIToastr.showInfo(LANG.UI_ROLE_MODIFY, LANG.UI_ROLE_MODIFY_NO_SELECT);
		}

		if(select.length >1){
			return UIToastr.showInfo(LANG.UI_ROLE_MODIFY,LANG.UI_ROLE_MODIFY_SELECT_ONE);
		}
		AddEditRole.init({role_uuid: select[0].role_uuid});
		$('#role_add_edit_drawer').drawer('show');
		// var url = "./content/platform/users/edit_role.php?roleuuid="+select[0].role_uuid;
		// LOCATION(url,'safety');

	}

	// 按钮点击效果
	var clickEffect = function (element) {
		$(element).addClass('btn-hover');
		setTimeout(function() {
			$(element).removeClass('btn-hover');
		}, 300); // 0.3秒后恢复原样
	}

	/**
	 * 错误提示
	 */
	var tipDeleteRole = function () {
		UIToastr.showInfo(LANG.UI_USER_DELETE_SELECT, LANG.UI_USER_DELETE_SELECT_TIPS);
	}
	var tipunlockRole = function () {
		UIToastr.showInfo(LANG.UI_ROLE_ENABLE, LANG.UI_ROLE_ENABLE_NO_SELECT);
	}
	var tiplockRole = function () {
		UIToastr.showInfo(LANG.UI_ROLE_DISABLE, LANG.UI_ROLE_DISABLE_NO_SELECT);
	}
	var tipallocateRole = function () {
		UIToastr.showInfo(LANG.UI_ROLE_ALLOCATION, LANG.UI_ROLE_ALLOCATION_NO_SELECT);
	}

	//删除角色
	var delRole = function(){
		clickEffect(this);
		let select = $('#role_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].role_uuid);
		}
		bootbox.confirm({
			title: LANG.UI_ROLE_DELETE,
			message: LANG.UI_ROLE_DELETE_CONFIRM,
			callback: debounce(function(r) {
				if(!r) return;
				var data = {};
				data.role_uuid = ids;
				pAjaxRequest(data,'/api/v1/roles','DELETE',function (res){
					Metronic.unblockUI('#roleDiv');
					if (operateResponseList(res, LANG.UI_ROLE_DELETE)) {
						$('#role_table').bootstrapTable('refresh');
					}
				});
			},300)
		});
	}

	//启用角色
	var unlockRole = function(grid){
		clickEffect(this);
		let select = $('#role_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].role_uuid);
		}
		if (!select.length) {
			return tipunlockRole();
		}
		var selectArr = select;
		var count =0;
		for (let i = 0; i < selectArr.length; i++) {
			if (selectArr[i].lock_flag == CONF.FLAG.SET) {
				count++;
			}
			if(count == selectArr.length){
				return UIToastr.showInfo(LANG.UI_ROLE_ENABLE, LANG.UI_ROLE_UNLOCKED);
			}
		}
		var data = {};
		data.role_uuid = ids;
		Metronic.blockUI({
			target: '#roleDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/roles/unlock','POST',function (res){
			Metronic.unblockUI('#roleDiv');
			if (operateResponseList(res, LANG.UI_ROLE_ENABLE)) {
				$('#role_table').bootstrapTable('refresh');
			}
		});
	}

	//禁用角色
	var lockRole = function(grid){
		clickEffect(this);
		let select = $('#role_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].role_uuid);
		}
		if (!select.length) {
			return tiplockRole();
		}
		var selectArr = select;
		var count =0;
		for (let i = 0; i < selectArr.length; i++) {
			if (selectArr[i].lock_flag == CONF.FLAG.UNSET) {
				count++;
			}
			if(count == selectArr.length){
				return UIToastr.showInfo(LANG.UI_ROLE_DISABLE, LANG.UI_ROLE_LOCKED);
			}
		}
		var data = {};
		data.role_uuid = ids;
		Metronic.blockUI({
			target: '#roleDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/roles/lock','POST',function (res){
			Metronic.unblockUI('#roleDiv');
			if (operateResponseList(res, LANG.UI_ROLE_DISABLE)) {
				$('#role_table').bootstrapTable('refresh');
			}
		});
	}

	//分配角色
	var allocationRole = function(grid){
		clickEffect(this);
		let select = $('#role_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].role_uuid);
		}
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_ROLE_ALLOCATION, LANG.UI_ROLE_ALLOCATION_NO_SELECT);
		}

		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_ROLE_ALLOCATION, LANG.UI_ROLE_ALLOCATION_SELECT_ONE);
		}

		var tenantuuid = select[0]['tenant_uuid']; //是否为租户的角色
		var roleName = select[0]['role_name'];	//角色名字
		//初始化分配角色模态框
		initAllocationModal(select[0]['role_uuid'], roleName, tenantuuid);

	}

	var initAllocationModal = function(roleuuid, roleName, tenantuuid){
		//初始化角色关联用户和用户组列表
		initAllocationList(roleuuid, tenantuuid);

		$('#allocationModal').modal({width: 600, height: 400});
		$('#usergroupRoleName').html(roleName);
		$('#userRoleName').html(roleName);
		$('#roleuuid').val(roleuuid);
	}

	//初始化角色关联用户和用户组列表
	var initAllocationList = function(roleuuid, tenantuuid){
		var data = {};
		data.roleuuid = roleuuid;
		pAjaxRequest(data,'/api/v1/roles/allocationList','GET',function (res){
			if (res.code == 0) {
				var data = res.data;
				user_list = data.user_list;
				user_group_list = data.user_group_list;
				var data = {};
				data.tenantuuid = tenantuuid;
				initSelectUser(data);		//初始化用户选择框
				initSelectUsergroup(data);	//初始化用户组选择框
			}
		});

	}

	//初始化用户列表
	var initSelectUser = function(data){
		pAjaxRequest(data,'/api/v1/roles/userlist','GET',function (res){
			if (res.code == 0) {
				var data = res.data;
				var userList = $("#selectUser");
				userList.empty();
				for(var i=0;i<data.length;i++){
					var option = $("<option>").text(data[i].user_name).val(data[i].user_uuid);
					userList.append(option);
				}
				userList.selectpicker('val',user_list);
				userList.selectpicker('refresh');
			}
		});
	}

	//初始化用户组列表
	var initSelectUsergroup = function(data){
		pAjaxRequest(data,'/api/v1/roles/usergrouplist','GET',function (res){
			if (res.code == 0) {
				var data = res.data;
				var usergroupList = $('#selectUsergroup');
				usergroupList.empty();
				for(var i=0;i<data.length;i++){
					var option = $('<option>').text(data[i].usergroup_name).val(data[i].usergroup_uuid);
					usergroupList.append(option);
				}
				usergroupList.selectpicker('val', user_group_list);
				usergroupList.selectpicker('refresh');
			}
		});


	}

	return{
		init: function(){
			handleRecords();
			addListeners();
		}
	};
}();
jQuery(document).ready(function() {
	RoleList.init();
});