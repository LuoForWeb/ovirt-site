var UserGroup = function(){
	var grid;
	var permissionTree;
	var usergrid, rolegrid, resourceGroupgrid;
	var initGridFlag = false;
	var initRoleTableFlag = false; //任务表格初始化标志
	let initUserTableFlag = false;
	let initResourceGroupTableFlag = false;

	//添加监听事件
	var addListeners = function(){
		$('#add_usergroup').on('click',function(){
			clickEffect(this);
			LOCATION('./content/platform/users/add_usergroup.php','safety');
		});

		// 新表格
		// 删除
		$('#vin_usergroup_toolbar #delete_usergroup').on('click', function (){
			if (checkAuth('delete')) {
				checkOperateAuth(checkAuth('delete'), delUserGroup)
			}
		});
		// 启用
		$('#vin_usergroup_toolbar #unlock').on('click', function (){
			if (checkAuth('unlock')) {
				checkOperateAuth(checkAuth('unlock'), unlockUserGroup)
			}
		});
		// 禁用
		$('#vin_usergroup_toolbar #lock').on('click', function (){
			if (checkAuth('lock')) {
				checkOperateAuth(checkAuth('lock'), lockUserGroup)
			}
		});
		// 修改
		$('#vin_usergroup_toolbar #edit_usergroup').on('click', function (){
			if (checkAuth('edit')) {
				checkOperateAuth(checkAuth('edit'), editUsersGroup)
			}
		});
	};

	// 操作权限校验
	var checkAuth = function(operationType) {
		var select = $('#usergroup_table').bootstrapTable('getSelections');
		if (!select.length) {
			switch (operationType){
				case 'delete':
					tipDeleteUserGroup();
					break;
				case 'unlock':
					tipunLockUserGroup();
					break;
				case 'lock':
					tipunLockUserGroup();
					break;
				case 'edit':
					tipeEditUserGroup();
					break;
			}
			return false;
		}

		return {
			type: 1,
			user_uuid: [...new Set(select.map(item => item.user_uuid))].join(','),
			auth: ''
		};
	}

	var editUsersGroup = function(){
		clickEffect(this);
		let select = $('#usergroup_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].user_group_uuid);
		}
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_USER_GROUP_MODIFY, LANG.UI_USER_GROUP_MODIFY_NO_SELECT);
		}
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_USER_GROUP_MODIFY, LANG.UI_USER_GROUP_MODIFY_SELECT_ONE);
		}
		var url = './content/platform/users/edit_usergroup.php?usergroupuuid=' + select[0].user_group_uuid;
		LOCATION(url,'safety');
	}

	var initPermissionTree = function(p){
		pAjaxRequest(p,'/api/v1/usergroups/usergroup/permissiontree','GET',function (d){
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
	var initUserTable = function(p){
		var options = {
			vin_url: '/api/v1/usergroups/user',
			vin_method: 'GET',
			vin_params: function () {
				var param = {};
				param.usergroup_uuid = p;
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
					title: LANG.UI_CLOUD_PLATFORM_USERNAME,
					sortable: false
				}
			]
		}
		if(!initUserTableFlag){
			$('#user_table').baseTableConfig().init(options);
			initUserTableFlag = true;
		}else{
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#user_table').bootstrapTable('destroy');
			$('#user_table').baseTableConfig().init(options);
		}

	}

	//加载关联角色
	var initRoleTable = function(p){
		var options = {
			vin_url: '/api/v1/usergroups/role',
			vin_method: 'GET',
			vin_params: function () {
				var param = {};
				param.usergroup_uuid = p;
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
					field: 'rolename',
					title: LANG.UI_ROLE_NAME,
					sortable: false
				}
			]
		}
		if (!initRoleTableFlag) {
			$('#role_table').baseTableConfig().init(options);
			initRoleTableFlag = true;
		}else{
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#role_table').bootstrapTable('destroy');
			$('#role_table').baseTableConfig().init(options);
		}
	}

	//加载关联资源组
	var initResourcegroupTable = function(p){
		var options = {
			vin_url: '/api/v1/usergroups/resource',
			vin_method: 'GET',
			vin_params: function () {
				var param = {};
				param.usergroup_uuid = p;
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
					field: 'resource_group_name',
					title: LANG.UI_RESOURCE_GROUP_NAME,
					sortable: false
				}
			]
		}
		if(!initResourceGroupTableFlag){
			$('#resourcegroup_table').baseTableConfig().init(options);
			initResourceGroupTableFlag = true;
		}else{
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#resourcegroup_table').bootstrapTable('destroy');
			$('#resourcegroup_table').baseTableConfig().init(options);
		}
	}

	//初始化详情模态框
	var initModalDetails = function(row){
		var data = {};
		data.usergroupuuid = row.user_group_uuid;
		initPermissionTree(data);
		initUserTable(data.usergroupuuid);
		initRoleTable(data.usergroupuuid);
		initResourcegroupTable(data.usergroupuuid);
		initGridFlag = true;
		$('#userPermissionDiv').modal({'width': '600px', height: '100%'});
	}


	/**
	 * 	初始化用户组表格
	 */
	var handleRecords = function(){
		var operates = {
			'click .userGroupDetails' :function (event, value, row, index){
				initModalDetails(row);
			}
		};
		//表格初始化配置项
		let options = {
			toolbarId: '#vin_usergroup_toolbar',
			buttonsToolbar: '#vin_usergroup_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/usergroups',
			vin_method: 'GET',
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			fullPage:true,
			onResetView: initTableHeight,
			onCheck: function () {
				modifyDelStyle('usergroup_table', 'delete_usergroup');
			},
			onUncheck: function () {
				modifyDelStyle('usergroup_table', 'delete_usergroup');
			},
			onCheckAll: function () {
				modifyDelStyle('usergroup_table', 'delete_usergroup');
			},
			onUncheckAll: function () {
				modifyDelStyle('usergroup_table', 'delete_usergroup');
			},
			fileName:LANG.UI_USER_GROUP_LIST,
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
					field: 'user_group_name',
					title: LANG.UI_MICROSOFT365_USER_GROUP,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter:function (value, row, index, field){
						var nameStr = "<a class='userGroupDetails' id='" + row.user_group_uuid + "'>" + row.user_group_name + "</a>";
						return nameStr;
					},
					events:operates,
				},
				{
					field: 'user_group_type',
					title: LANG.UI_STORAGE_TYPE,
					sortable: false, //默认可排序，禁用排序才写此项
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
					field: 'description',
					title: LANG.UI_PUBLIC_DESCRIPTION,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'tenant_name',
					title: LANG.UI_TENANT_NAME,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'create_user_name',
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
		sessionStorage.removeItem('usergroup_table_pageRecord');
		$('#usergroup_table').baseTableConfig().init(options);

	};


	//分配资源给资源组
	var allocationResource = function(){
		var select = grid.getSelectedRows();
		var data = grid.getDataTable().data();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_USER_GROUP_RESOURCE_ALLOCATION, LANG.UI_USER_GROUP_RESOURCE_ALLOCATION_NO_SELECT);
		}

		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_USER_GROUP_RESOURCE_ALLOCATION, LANG.UI_USER_GROUP_RESOURCE_ALLOCATION_SELECT_ONE);
		}

		var userGroupName = "";
		if(data.length !=0){
			for(var i=0;i<data.length;i++){
				if(data[i][8] == select[0]){
					userGroupName = data[i][1];
				}
			}
		}


		var url = "./content/platform/users/usergroup_resource.php?usergroupuuid=" + select[0];
		if(userGroupName != ""){
			url += "&usergroupname=" + userGroupName;
		}

		//跳转到资源分配
		LOCATION(url,'safety');
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
	var tipeEditUserGroup = function(){
		UIToastr.showInfo(LANG.UI_USER_GROUP_MODIFY, LANG.UI_USER_GROUP_MODIFY_NO_SELECT);
	}

	var tipDeleteUserGroup = function () {
		UIToastr.showInfo(LANG.UI_USER_DELETE_SELECT, LANG.UI_USER_DELETE_SELECT_TIPS);
	}

	var tipunLockUserGroup = function () {
		UIToastr.showInfo(LANG.UI_USER_GROUP_ENABLE, LANG.UI_USER_GROUP_ENABLE_NO_SELECT);
	}

	var tipLockUserGroup = function () {
		UIToastr.showInfo(LANG.UI_USER_GROUP_DISABLE, LANG.UI_USER_GROUP_DISABLE_NO_SELECT);
	}

	/**
	 * 删除用户组
	 */
	var delUserGroup = function (){
		clickEffect(this);
		let select = $('#usergroup_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].user_group_uuid);
		}
		bootbox.confirm({
			title: LANG.UI_USER_GROUP_DELETE,
			message: LANG.UI_USER_GROUP_DELETE_CONFIRM,
			callback: debounce(function (r) {
				if (!r) return;
				var data = {};
				data.usergroups_uuid = ids;
				Metronic.blockUI({
					target: '#authdiv',
					animate: true,
					cenrerY: true,
				});
				pAjaxRequest(data,'/api/v1/usergroups','DELETE',function (res){
					Metronic.unblockUI('#authdiv');
					if (operateResponseList(res, LANG.UI_USER_GROUP_DELETE)) {
						$('#usergroup_table').bootstrapTable('refresh');
					}
				});
			},300)
		})
	}

	/**
	 * 启用用户组
	 */
	var unlockUserGroup = function (){
		clickEffect(this);
		let select = $('#usergroup_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].user_group_uuid);
		}
		if (!select.length) {
			return tipunLockUserGroup();
		}
		var selectArr = select;
		var count =0;
		for (let i = 0; i < selectArr.length; i++) {
			if (selectArr[i].lock_flag == CONF.FLAG.SET) {
				count++;
			}
			if(count == selectArr.length){
				return UIToastr.showInfo(LANG.UI_USER_GROUP_ENABLE, LANG.UI_USER_GROUP_UNLOCKED);
			}
		}

		var data = {};
		data.usergroups_uuid = ids;
		Metronic.blockUI({
			target: '#authdiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/usergroups/unlock','POST',function (res){
			Metronic.unblockUI('#authdiv');
			if (operateResponseList(res, LANG.UI_USER_GROUP_ENABLE)) {
				$('#usergroup_table').bootstrapTable('refresh');
			}
		});
	}

	/**
	 * 禁用用户组
	 */
	var lockUserGroup = function (){
		clickEffect(this);
		let select = $('#usergroup_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].user_group_uuid);
		}
		if (!select.length) {
			return tipLockUserGroup();
		}

		var selectArr = select;
		var count =0;
		for (let i = 0; i < selectArr.length; i++) {
			if (selectArr[i].lock_flag == CONF.FLAG.UNSET) {
				count++;
			}
			if(count == selectArr.length){
				return UIToastr.showInfo(LANG.UI_USER_GROUP_DISABLE, LANG.UI_USER_GROUP_LOCKED);
			}
		}

		bootbox.confirm({
			title: LANG.UI_USER_GROUP_DISABLE,
			message: LANG.UI_USER_GROUP_CONFIRM_DISABLE,
			callback: debounce(function (r) {
				if (!r) return;
				var data = {};
				data.usergroups_uuid = ids;
				Metronic.blockUI({
					target: '#authdiv',
					animate: true,
					cenrerY: true,
				});
				pAjaxRequest(data,'/api/v1/usergroups/lock','POST',function (res){
					Metronic.unblockUI('#authdiv');
					if (operateResponseList(res, LANG.UI_USER_GROUP_DISABLE)) {
						$('#usergroup_table').bootstrapTable('refresh');
					}
				});
			},300)
		})
	}

	return{
		init: function(){
			addListeners();
			handleRecords();
			initTableHeight();
		}
	};
}();
jQuery(document).ready(function() {

	UserGroup.init();
});