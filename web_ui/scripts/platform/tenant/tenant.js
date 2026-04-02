//租户概览页面
var Tenant = function(){
	var userGrid, userGroupGrid;
	var initUserFlag = false, initUserGroupFlag = false;
	var fileGrid, dbGrid, cdpGrid, vmGrid, ApplianceGrid, nodeGrid, storageGrid;	//定义文件代理|数据库定时主机|数据库实时主机|虚拟机|备份代理|节点|存储|资源组
	
	var hostTree; 
	var nodeSelectFlag = false, initHostFlag = false;
	var oldHost,oldVcenter, oldDesStorage, oldDesNetwork;
	
	var oldJsonData;
	var initBackupFlag = false;
	var initRecoverFlag = false;
	
	var initListeners = function(){
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
		
		
		//保存通用配置
		$('#common_submit').on('click', function(){
			commonSubmit();
		});
		
//		//保存备份配置
//		$('#backup_submit').on('click', function(){
//			backupSubmit();
//		});
		//保存恢复配置
		$('#recover_submit').on('click', function(){
			recoverSubmit();
		});
//		//保存计费配置
//		$('#billing_submit').on('click', function(){
//			billingSubmit();
//		});
		
		//是否配置限速
//		$('#speedlimitCheck').on('switchChange.bootstrapSwitch', speedTypeChange);
		
		//是否设置配额
		$('#quotaSelect').on('change', quotaChange);
		
//		//备份节点类型切换
//		$('#nodeType').on('change', nodeChange);
		
//		//备份存储类型切换
//		$('#storageType').on('change', storageChange);
		
		//宿主机类型切换
		$('#hostType').on('change', hostChange);
		//目标存储
		$('#desStorageType').on('change', desStorageChange);
		//目标网络
		$('#networkType').on('change', networkChange);
		
//		//切换备份节点
//		$('#nodeSelect').on('change', nodeSelectChange);

		//跳转到添加虚拟化中心
		$('#toaddvcenter').on('click',function(){
	    	LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
		});
		
		//切换授权方式
		$('#authSelect').on('change', authChange);
		
		//初始化恢复配置点击
		$('#recovertab').on('click', function(){
			if(!initRecoverFlag && oldJsonData.recover){
				initRecoverSettings(oldJsonData.recover);
			}
			initRecoverFlag = true;
		});
	}
	
	var authChange = function(){
		var value = this.value;
		if(value == 1){
			$('.quotaInfoDiv').show();
			$('.authNumDiv').hide();
		}else{
			$('.quotaInfoDiv').hide();
			$('.quotaDiv').hide();
			$('#quotaSelect').val(0);
			$('.authNumDiv').show();
		}
	}
	
//	var nodeSelectChange = function(){
//		initBackupStorage();
//	}
	
	var quotaChange = function(){
		if(this.value == 1){
			$('.quotaDiv').show();
		}else{
			$('.quotaDiv').hide();
		}
	}
	
	var speedTypeChange = function(){
		if(this.checked){
			$('.speedDiv').show();
		}else{
			$('.speedDiv').hide();
		}
	}
	
//	var nodeChange = function(){
//		if(this.value == 1){
//			$('.nodeDiv').show();
//		}else{
//			$('.nodeDiv').hide();
//		}
//		if(!nodeSelectFlag){	//加载一次
//			initBackupNode();
//		} 
//	}
//	
//	var storageChange = function(){
//		if(this.value == 1){
//			$('.storageDiv').show();
//		}else{
//			$('.storageDiv').hide();
//		}
//	}
	
	var hostChange = function(){
		//指定宿主机
		$('.desStorageDiv').hide();
		$('.networkDiv').hide();
		if(this.value == 1){
			$('.hostDiv').show();
//			$('#networkType').prop('disabled', false);
//			$('#desStorageType').prop('disabled', false);
			if(oldDesStorage){
				$('.desStorageDiv').show();
				$('#desStorageType').prop('disabled', false);
			}else{
				$('#desStorageType').val(0).prop('disabled', false);
			}
			if(oldDesNetwork){
				$('.networkDiv').show();
				$('#networkType').prop('disabled', false);
			}else{
				$('#networkType').val(0).prop('disabled', false);
			}
		}else{
			$('.hostDiv').hide();
			$('#networkType').val(0).prop('disabled', true);
			$('#desStorageType').val(0).prop('disabled', true);
		}
		
		if(!initHostFlag){
			initHostSelect();
		}
	}
	
	var desStorageChange = function(){
		if(this.value == 1){
			$('.desStorageDiv').show();
		}else{
			$('.desStorageDiv').hide();
		}
	}
	
	var networkChange = function(){
		if(this.value == 1){
			$('.networkDiv').show();
		}else{
			$('.networkDiv').hide();
		}
	}
	
	
	
	//初始化用户列表
	var initUserTable = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [1, 2]
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		var tenantuuid = $('#tenantuuid').val();
		var params = JSON.stringify({tenantuuid: tenantuuid});
		userGrid = new Datatable();
		var data = {m:CONF.M.TENANT,f:'getTenantUserList',p:{tenantuuid: tenantuuid}};
		userGrid.setAjaxParam(data);
		userGrid.init({src: $("#usertable"), dataTable:dataTableOpt});
		
//		//添加用户与租户关联
//		$('#user_add').on('click', function(){
//			addTenantUser();
//		});
//		
//		//添加用户确认
//		$('#adduser_submit').on('click', function(){
//			addUserSubmit();
//		});
//		
//		//删除用户与租户关联
//		$('#user_delete').on('click', function(){
//			deleteTenantUser();
//		});
	}
	
//	//添加用户
//	var addTenantUser = function(){
//		if(!initUserFlag){
//			initUserList();
//		}
//		$('#addUserModal').modal({'width': '600px', 'height': '300px'});
//	}
//	
//	//添加用户确认
//	var addUserSubmit = function(){
//		var data = {};
//		data.tenantUUID = $('#tenantuuid').val();
//		data.userList = $('#userList').selectpicker('val');
//		var params = JSON.stringify(data);
//		$.post(CONF.AJAXPATH,{m: CONF.M.TENANT, f: "addTenantUser", p: params}, function(d){
//			if(OPREL(d)){
//				$('#addUserModal').modal('hide');
//				userGrid.getRefresh({});
//				initUserList();
//			}
//		});
//	}
//	
//	//删除用户
//	var deleteTenantUser = function(){
//		var select = userGrid.getSelectedRows();
//		if(select.length == 0){
//			return UIToastr.showInfo("取消用户和租户关联", "请选择需要取消关联的用户");
//		}
//		
//		bootbox.confirm({
//			title: "取消用户与租户关联",
//			message: "您确定要取消选中的用户和租户关联吗？",
//			callback: function(r){
//				if(!r) return;
//				var data = {};
//				data.tenantUUID = $('#tenantuuid').val();
//				data.userList = select;
//				var p = JSON.stringify(data);
//				$.post(CONF.AJAXPATH, {m: CONF.M.TENANT, f: "deleteTenantUser", p: p}, function(d){
//					if(OPREL(d)){
//						userGrid.getRefresh({});
//					}
//				})
//			}
//		})
//	}
//	
//	//初始化用户列表
//	var initUserList = function(){
//		var data = {};
//		data.tenantuuid = $('#tenantuuid').val();
//		var params = JSON.stringify(data);
//		
//		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:'getTenantUserAddList',p:params}, function(p){
//			var data = JSON.parse(p);
//			var userList = $("#userList");
//			userList.empty();
//			for(var i=0;i<data.length;i++){
//				var option = $("<option>").text(data[i].user_name).val(data[i].user_uuid);
//				userList.append(option);
//			}
//			userList.selectpicker('refresh');
//			initUserFlag = true;
//		});
//	}
	
	
	//初始化用户组列表
	var initUserGroupTable = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [1, 2]
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		var tenantuuid = $('#tenantuuid').val();
		var params = JSON.stringify({tenantuuid: tenantuuid});
		userGroupGrid = new Datatable();
		var data = {m:CONF.M.TENANT,f:'getTenantUserGroupList',p:{tenantuuid: tenantuuid}};
		userGroupGrid.setAjaxParam(data);
		userGroupGrid.init({src: $("#usergrouptable"), dataTable:dataTableOpt});
		
//		//添加用户组与租户关联
//		$('#usergroup_add').on('click', function(){
//			addTenantUserGroup();
//		});
//		
//		//添加用户组确认
//		$('#addusergroup_submit').on('click', function(){
//			addUserGroupSubmit();
//		});
//		
//		//删除用户组与租户关联
//		$('#usergroup_delete').on('click', function(){
//			deleteTenantUserGroup();
//		});
	}
	
//	//添加用户组和租户关联
//	var addTenantUserGroup = function(){
//		if(!initUserGroupFlag){
//			initUserGroupList();
//		}
//		$('#addUserGroupModal').modal({'width': '600px', 'height': '300px'});
//	}
//	
//	//添加用户组确认
//	var addUserGroupSubmit = function(){
//		var data = {};
//		data.tenantUUID = $('#tenantuuid').val();
//		data.userGroupList = $('#userGroupList').selectpicker('val');
//		var params = JSON.stringify(data);
//		$.post(CONF.AJAXPATH,{m: CONF.M.TENANT, f: "addTenantUserGroup", p: params}, function(d){
//			if(OPREL(d)){
//				$('#addUserGroupModal').modal('hide');
//				userGroupGrid.getRefresh({});
//				initUserGroupList();
//			}
//		});
//	}
//	
//	//删除用户
//	var deleteTenantUserGroup = function(){
//		var select = userGroupGrid.getSelectedRows();
//		if(select.length == 0){
//			return UIToastr.showInfo("取消用户组和租户关联", "请选择需要取消关联的用户组");
//		}
//		
//		bootbox.confirm({
//			title: "取消用户组与租户关联",
//			message: "您确定要取消选中的用户组和租户关联吗？",
//			callback: function(r){
//				if(!r) return;
//				var data = {};
//				data.tenantUUID = $('#tenantuuid').val();
//				data.userGroupList = select;
//				var p = JSON.stringify(data);
//				$.post(CONF.AJAXPATH, {m: CONF.M.TENANT, f: "deleteTenantUserGroup", p: p}, function(d){
//					if(OPREL(d)){
//						userGroupGrid.getRefresh({});
//					}
//				})
//			}
//		})
//	}
//	
//	//初始化用户组列表
//	var initUserGroupList = function(){
//		var data = {};
//		data.tenantuuid = $('#tenantuuid').val();
//		var params = JSON.stringify(data);
//		
//		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:'getTenantUserGroupAddList',p:params}, function(p){
//			var data = JSON.parse(p);
//			var userGroupList = $("#userGroupList");
//			userGroupList.empty();
//			for(var i=0;i<data.length;i++){
//				var option = $("<option>").text(data[i].user_group_name).val(data[i].user_group_uuid);
//				userGroupList.append(option);
//			}
//			userGroupList.selectpicker('refresh');
//			initUserGroupFlag = true;
//		});
//	}
	
	
	
	//初始化加载当前租户基本信息
	var initTenantBasicInfo = function(){
		var tenantuuid = $('#tenantuuid').val();
		var params = JSON.stringify({tenantuuid: tenantuuid});
		$.post(CONF.AJAXPATH, {m: CONF.M.TENANT, f: "getTenantBasicInfo", p: params}, function(d){
			var data = JSON.parse(d);
			setBasicInfo(data);
		})
	}
	
	//初始化基本信息
	var setBasicInfo = function(data){
		$('#companyName').html(data.nick_name);
		$('#tenantName').html(data.tenant_name);
		$('#adminName').html(data.admin_name);
		$('#adminEmail').html(data.admin_email);
		$('#createTime').html(data.create_time);
		$('#userNum').html(data.user_num);
		$('#userGroupNum').html(data.user_group_num);
	}
	
	
	//初始化租户资源
	var initTenantResource = function(){
		initTenantFileHost();	//文件
		initTenantDbHost();		//数据库定时主机
		initTenantCDPHost();	//数据库实时主机
		initTenantVM();			//虚拟机
		initTenantAppliance();	//虚拟机备份代理
		initTenantNode();		//节点
		initTenantStorage();	//存储
	}
	
	/*-------------------文件代理主机---------------------*/
	//初始化文件代理主机
	var initTenantFileHost = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		fileGrid = new Datatable();
		var tenantUUID = $('#tenantuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getTenantFileHostResource',p:{tenantUUID: tenantUUID}};
		fileGrid.setAjaxParam(data);
		fileGrid.init({src: $("#fileDatatable"), dataTable:dataTableOpt, onDataLoad: initFileRow});
	}
	
	//初始化加载用户所有文件代理样式
	var initFileRow = function(){
		var data = fileGrid.getDataTable().data();
		if(!data || data.length == 0) return;
		var statusDiv = $('#fileDatatable tbody > tr').find('td:eq(3)');
		for(var i=0; i<statusDiv.length; i++){
			addStatus(statusDiv[i], data[i]);
		}
	}
	
	
	//添加状态信息
	var addStatus = function(div, data){
		var labelClass = getStatusClass(data[4]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[3] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getStatusClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			case 3:
				levelClass = "label-default";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
	/*-------------------数据库定时主机---------------------*/
	//初始化数据库定时主机
	var initTenantDbHost = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		dbGrid = new Datatable();
		var tenantUUID = $('#tenantuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getTenantDbHostResource',p:{tenantUUID: tenantUUID}};
		dbGrid.setAjaxParam(data);
		dbGrid.init({src: $("#dbDatatable"), dataTable:dataTableOpt, onDataLoad: initDbRow});
	}
	
	//初始化加载用户所有数据库定时主机样式
	var initDbRow = function(){
		var data = dbGrid.getDataTable().data();
		if(!data || data.length == 0) return;
		var statusDiv = $('#dbDatatable tbody > tr').find('td:eq(3)');
		for(var i=0; i<statusDiv.length; i++){
			addStatus(statusDiv[i], data[i]);
		}
	}
	
	
	//添加状态信息
	var addStatus = function(div, data){
		var labelClass = getStatusClass(data[4]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[3] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getStatusClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			case 3:
				levelClass = "label-default";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
	/*-------------------数据库实时主机---------------------*/
	//初始化数据库实时主机
	var initTenantCDPHost = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		cdpGrid = new Datatable();
		var tenantUUID = $('#tenantuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getTenantCDPHostResource',p:{tenantUUID: tenantUUID}};
		cdpGrid.setAjaxParam(data);
		cdpGrid.init({src: $("#cdpDatatable"), dataTable:dataTableOpt, onDataLoad:initCDPHostRow});
	}
	
	//初始化数据库实时主机列表样式
	var initCDPHostRow = function(){
		var data = cdpGrid.getDataTable().data();
		if(0 == data.length) return;
		var statusDiv = $('#cdpDatatable tbody > tr').find('td:eq(4)');
		for(var i=0; i<statusDiv.length; i++){
			hostStatus(statusDiv[i], data[i]);
		}
	}
	
	
	
	//主机状态
	var hostStatus = function(div, data){
		var thisClass = "label-info";
		if(1 == data[5]){
			thisClass = "label-success";
		}else if(2 == data[5]){
			thisClass = "label-default";
		}else if(3 == data[5]){
			thisClass = "label-warning";
		}
		var statusDes = '<span class="label label-sm ' + thisClass + '">' + data[4] + '</span>';
		$(div).html(statusDes);
	}
	
	
	
	/*-------------------虚拟机---------------------*/
	//初始化虚拟机
	var initTenantVM = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		vmGrid = new Datatable();
		var tenantUUID = $('#tenantuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getTenantVMResource',p:{tenantUUID: tenantUUID}};
		vmGrid.setAjaxParam(data);
		vmGrid.init({src: $("#vmDatatable"), dataTable:dataTableOpt, onDataLoad: initVmRow});
	}
	
	
	//初始化用户所有虚拟机列表样式
	var initVmRow = function(){
		var data = vmGrid.getDataTable().data();
		if(0 == data.length) return;
		var statusDiv = $('#vmDatatable tbody > tr').find('td:eq(3)');
		for(var i=0; i<data.length; i++){
			statusDiy(statusDiv[i], data[i]);
		}
	}
	
	//添加虚拟机状态
	var statusDiy = function(div, data){
		var status = '<span class="label label-sm ' + getVmStatusLevel(data[6]) + '">' + data[3] + '</span>';
		$(div).html(status);
	}
	
	//得到虚拟机状态显示样式
	var getVmStatusLevel = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-default";
				break;
			case 2:
				levelClass = "label-success";
				break;
			case 3:
				levelClass = "label-warning";
				break;
			case 4:
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
	
	
	
	/*-------------------备份代理---------------------*/
	//初始化备份代理
	var initTenantAppliance = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		applianceGrid = new Datatable();
		var tenantUUID = $('#tenantuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getTenantApplianceResource',p:{tenantUUID: tenantUUID}};
		applianceGrid.setAjaxParam(data);
		applianceGrid.init({src: $("#applianceDatatable"), dataTable:dataTableOpt, onDataLoad: initApplianceRow});
	}
	
	//初始化虚拟机备份代理列表样式
    var initApplianceRow = function(){
		var data = applianceGrid.getDataTable().data();
		if(0 == data.length) return;
		var levelDiv = $('#applianceDatatable tbody > tr').find('td:eq(3)');
		for(var i=0; i<levelDiv.length; i++){
			setApplianceLevel(levelDiv[i], data[i]);
		}
	}
    
	
    //在线/离线(appliance状态)
	var setApplianceLevel = function(div, data){
		var labelClass = getApplianceStatusClass(data[4]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[3] + '</span>';
		$(div).html(content);
	}
	
	//得到存储状态类型
	var getApplianceStatusClass = function(status){
		var levelClass = '';
		switch(status){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			default:
				levelClass = "label-warning";
				break;
		}
		return levelClass;
	}
    
	
	/*-------------------节点---------------------*/
	//初始化节点
	var initTenantNode = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		nodeGrid = new Datatable();
		var tenantUUID = $('#tenantuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getTenantNodeResource',p:{tenantUUID: tenantUUID}};
		nodeGrid.setAjaxParam(data);
		nodeGrid.init({src: $("#nodeDatatable"), dataTable:dataTableOpt, onDataLoad: initNodeRow});
	}
	
	//初始化节点列表显示样式
	var initNodeRow = function(){
		var data = nodeGrid.getDataTable().data();
		if(0 == data.length) return;
		var deployStatusDiv = $('#nodeDatatable tbody > tr').find('td:eq(2)');
		var nodeStatusDiv = $('#nodeDatatable tbody > tr').find('td:eq(3)');
		for(var i=0; i<data.length; i++){
			setDeployStatus(deployStatusDiv[i], data[i]);
			setNodeStatus(nodeStatusDiv[i], data[i]);
		}
	}
	
	
	//节点状态
	var setNodeStatus = function(div, data){
		var statusDes = LANG.UI_NODE_ABNORMAL;
		var level = 2;
		if(data[3]){
			//正常
			statusDes = LANG.UI_NODE_NORMAL;
			level = 1;
		}
		var labelClass = getLevelClass(level);
		var content = '<span class="label label-sm ' + labelClass + '">' + 
			statusDes + '</span>';
		if(!data[2]){
			//如果节点未部署
			content = '--';
		}
		$(div).html(content);
	}
	
	//部署状态
	var setDeployStatus = function(div, data){
		var deployDes = LANG.UI_NODE_UNDEPLOY;
		var level = 2;
		if(data && data[2]){
			//已部署
			deployDes = LANG.UI_NODE_DEPLOYED;
			level = 1;
		}
		var labelClass = getLevelClass(level);
		var content = '<span class="label label-sm ' + labelClass + '">' + deployDes + '</span>';
		$(div).html(content);
	}
	
	
	//得到日志的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
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
	
	
	/*-------------------存储---------------------*/
	//初始化存储
	var initTenantStorage = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [0, "desc"]
                ],
    	};
		storageGrid = new Datatable();
		var tenantUUID = $('#tenantuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getTenantStorageResource',p:{tenantUUID: tenantUUID}};
		storageGrid.setAjaxParam(data);
		storageGrid.init({src: $("#storageDatatable"), dataTable:dataTableOpt, onDataLoad: initStorageRow});
	}
	
	//初始化用户所有存储列表样式
	var initStorageRow = function(){
		var data = storageGrid.getDataTable().data();
		if(0 == data.length) return;
		var levelDiv = $('#storageDatatable tbody > tr').find('td:eq(5)');
		for(var i=0; i<levelDiv.length; i++){
			setStorageLevel(levelDiv[i], data[i]);
		}
	}
	

	//在线/创建中/离线/异常(节点在线状态异常)
	var setStorageLevel = function(div, data){
		var labelClass = getStorageStatusClass(data[5]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[7] + '</span>';
		$(div).html(content);
	}
	
	//得到存储状态类型
	var getStorageStatusClass = function(status){
		var levelClass = '';
		switch(status){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			case 3:
				levelClass = "label-default";
				break;
			default:
				levelClass = "label-warning";
				break;
		}
		return levelClass;
	}
	
	//初始化租户资源个数统计
	var initTenantResourceNum = function(){
		var data = {};
		data.tenantUUID = $('#tenantuuid').val();
		var params = JSON.stringify(data);
		$.post(CONF.AJAXPATH,{m: CONF.M.TENANT, f: "getTenantResourceTotal", p: params}, function(d){
			var jsonData = JSON.parse(d);
			var fileDes = '<p class="counter" data-counter="counterup" data-value="'+ jsonData.fileNum +'">'+ jsonData.fileNum +'</p>';
			var dbDes = '<p class="counter" data-counter="counterup" data-value="'+ jsonData.dbNum +'">'+ jsonData.dbNum +'</p>';
			var cdpDes = '<p class="counter" data-counter="counterup" data-value="'+ jsonData.cdpNum +'">'+ jsonData.cdpNum +'</p>';
			var vmDes = '<p class="counter" data-counter="counterup" data-value="'+ jsonData.vmNum +'">'+ jsonData.vmNum +'</p>';
			var applianceDes = '<p class="counter" data-counter="counterup" data-value="'+ jsonData.applianceNum +'">'+ jsonData.applianceNum +'</p>';
			var nodeDes = '<p class="counter" data-counter="counterup" data-value="'+ jsonData.nodeNum +'">'+ jsonData.nodeNum +'</p>';
			var storageDes = '<p class="counter" data-counter="counterup" data-value="'+ jsonData.storageNum +'">'+ jsonData.storageNum +'</p>';
			
			$('#fileNum').html(fileDes);
			$('#dbNum').html(dbDes);
			$('#cdpNum').html(cdpDes);
			$('#vmNum').html(vmDes);
			$('#applianceNum').html(applianceDes);
			$('#nodeNum').html(nodeDes);
			$('#storageNum').html(storageDes);
			
			$(".tenant-resource [data-counter='counterup']").counterUp({
                delay: 10,
                time: 1000
            });
		
		});
	}
	
	
	//初始化租户高级选项
	var initTenantHighConfig = function(){
		var data = {};
		data.tenantuuid = $('#tenantuuid').val();
		
		//如果还未选择恢复宿主机，目标存储和网络没法切换成指定
		$('#desStorageType').prop('disabled', true);
		$('#networkType').prop('disabled', true);
		var params = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:"getTenantHighSettings", p: params}, function(d){
			oldJsonData = JSON.parse(d);
			if(!oldJsonData) return;
			if(oldJsonData.common){
				initCommonSettings(oldJsonData.common);
			}
			
//			if(oldJsonData.backup){
//				initBackupNode();
//			}
			
			if(oldJsonData.recover){
				initTenantRecoverSettings(oldJsonData.recover);
			}
			
			if(oldJsonData.billing){
				initBillingSettings(oldJsonData.billing);
			}
			
			
		});
	}
	

	//得到开启和关闭的HTML内容
	var getFlagLevelInfo = function(flag){
		var html = '<span class="label label-sm label-success">' + LANG.UI_PUBLIC_ON + '</span>';
		if(!flag){
			html = '<span class="label label-sm label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		}
		return html;
	}
	
	//初始化通用配置
	var initCommonSettings = function(data){
		if(!data|| data.length == 0) return;
//		$('#infrastructureCheck').bootstrapSwitch('state', data.infrastructure); 
//		$('#hostCheck').bootstrapSwitch('state', data.hostcheck);
		
		//系统管理员登录系统高级配置显示
		$('#manageDataCheck').bootstrapSwitch('state', data.datamanage);	//用于租户管理员管理所有数据
//		$('#speedlimitCheck').bootstrapSwitch('state', data.speedlimit);
		//租户登录系统
		 $('#backupDataManage').html(getFlagLevelInfo(data.datamanage));
		$('#resourceShareSelect').val(data.resourceshare);
		
		//授权初始化
		if(data.authtype){
			$('#authSelect').val(data.authtype);
			$('#authTypeDes').html(data.authtypedes);
		}
		if(data.authtype == 1){
			$('#quotaSelect').val(data.quotatype);
			$('#quotaInput').val(data.quota);
			$('#quotaUnit').val(data.quotaunit);
			$('#quota').html(data.quotatypedes);
			var quotaSize = data.quota.toString() + data.quotaunit;
			if(data.quotatype != 1){
				quotaSize = "---";
			}
			$('#quotaSize').html(data.storageDes);
			
			$('.quotaInfoDiv').show();
			$('.authNumDiv').hide();
			//指定配额
			if(data.quotatype == 1){
				$('.quotaDiv').show();
			}else{
				$('.quotaDiv').hide();
			}
		}else if(data.authtype == 2){
			$('#quotaSelect').val(0);
			$('.quotaInfoDiv').hide();
			$('.authNumDiv').show();
			$('#vmNumInput').val(data.vm);
			$('#fsNumInput').val(data.fs);
			$('#dbNumInput').val(data.db);
			$('#vmNumDes').html(data.vmDes);
			$('#fsNumDes').html(data.fsDes);
			$('#dbNumDes').html(data.dbDes);
			
		}
		
		//租户用户登录系统高级配置显示
//		if(data.speed){
//			$('#speedSpinnerNumInput').val(data.speed);
//			$('#unit').val(data.speedunit);
//			$('#speedlimit').html(getFlagLevelInfo(data.speedlimit));
//			var speedSize = data.speed.toString() + data.speedunit;
//			if(!data.speedlimit){
//				speedSize = "---";
//			}
//			$('#speedSize').html(speedSize);
//		}
		$('#resource_share').html(data.resourcesharedes);
		
		
		//开启限速
//		if(data.speedlimit){
//			$('.speedDiv').show();
//		}
		
	}
	
	//初始化备份配置
	var initBackupSettings= function(data){
		if(!data|| data.length == 0) return;
		
		//系统管理员登录系统高级配置显示
		$('#nodeType').val(data.nodetype);
		$('#nodeSelect').val(data.node);
		$('#storageType').val(data.storagetype);
		$('#storageSelect').val(data.storage);
		
		//租户用户登录系统高级配置显示
		$('#nodeTypeDes').html(data.nodetypedes);
		$('#nodeName').html(data.nodename);
		$('#storageTypeDes').html(data.storagetypedes);
		$('#storageName').html(data.storagename);
		
		//指定节点
		if(data.nodetype == 1){
			$('.nodeDiv').show();
		}
		//指定存储
		if(data.storagetype == 1){
			$('.storageDiv').show();
		}
		initBackupFlag = true;
	}
	
	//初始化租户内配置显示
	var initTenantRecoverSettings = function(data){
		if(!data|| data.length == 0) return;
		
		//租户 用户登录系统租户高级配置显示
		$('#hostTypeDes').html(data.hosttypedes);
		$('#hostName').html(data.hostname);
		$('#desStorageTypeDes').html(data.desstoragedes);
		$('#desStorageName').html(data.desstoragename);
		$('#networkTypeDes').html(data.networktypedes);
		$('#networkName').html(data.networkname);
		
		//选中了指定宿主机类型，展示宿主机列表
		if(data.hosttype == 1){
			if(data.vcenter != ""){
				$('.hostDiv').show();
			}else{
				$('#hostTypeDes').html('---');
			}
		}
		
		//指定存储
		if(data.desstoragetype == 1){
			$('.desStorageDiv').show();
		}
		//指定网络
		if(data.networktype == 1){
			$('.networkDiv').show();
		}
	}
	
	//初始化恢复配置
	var initRecoverSettings = function(data){
		if(!data|| data.length == 0) return;
		$('#hostType').val(data.hosttype);
		if(data.hosttype == 1){
			//指定宿主机
			$('#networkType').prop('disabled', false);
			$('#desStorageType').prop('disabled', false);
		}
		//系统管理员登录系统租户高级配置显示
		oldHost = data.host;
		oldVcenter = data.vcenter
		oldDesStorage = data.desstorage;
		oldDesNetwork = data.network;
		$('#desStorageType').val(data.desstoragetype);
		$('#diskSelect').val(data.disktype);
		$('#macSelect').val(data.mactype);
		$('#networkType').val(data.networktype);
		
		//选中了指定宿主机类型，展示宿主机列表
		if(data.hosttype == 1){
			if(data.vcenter != ""){
				$('.hostDiv').show();
			}else{
				$('#hostTypeDes').html('---');
			}
		}
		//指定宿主机
		if(CONF.TENANTUUID == "" && data.hosttype == 1){
			initHostSelect();
		}
		
		//指定存储
		if(data.desstoragetype == 1){
			$('.desStorageDiv').show();
		}
		//指定网络
		if(data.networktype == 1){
			$('.networkDiv').show();
		}
	}
	
	//初始化计费配置
	var initBillingSettings = function(data){
		if(!data|| data.length == 0) return;
		//系统管理员登录系统租户高级配置显示
		$('#billingCheck').html(getFlagLevelInfo(data.flag));
		var tenantuuid = $('#tenantuuid').val();
		var href = "./content/platform/billing/details_tenant_billing.php?tenant_uuid=" + tenantuuid+"&billing_uuid="+data.billinguuid;
		var url = '<a class="billingHref ajaxify" name="p_tenant_view" href="'+href+'" >'+data.name+'</a>';
		$('#billingSelect').html(url);
		
		//租户用户登录系统租户高级配置显示
		$('#billing').html(getFlagLevelInfo(data.flag));
		$('#billingStrategy').html(url);
		
		//指定计费配置
		if(data.flag){
			$('.billingDiv').show();
		}
	}
	
	//计算配额大小转换int
	var calSize = function(num, unit){
		var size = 0;
		switch(unit){
			case "MB":
				size = num * 1024 * 1024;
				break;
			case "GB":
				size = num * 1024 * 1024 * 1024;
				break;
				
			case "TB":
				size = num * 1024 * 1024 * 1024 * 1024;
				break;
				
			case "PB":
				size = num * 1024 * 1024 * 1024 * 1024 * 1024;
				break;
		}
		
		return size;
		
	}
	
	//计算限速大小转换int
	var calSpeedSize = function(num, unit){
		var size = 0;
		switch(unit){
			case "KB/s":
				size = num * 1024;
				break;
			case "MB/s":
				size = num * 1024 * 1024;
				break;
			case "GB/s":
				size = num * 1024 * 1024 * 1024;
				break;
		}
		
		return size;
		
	}
	
	
	//通用配置提交
	var commonSubmit = function(){
		var settings = {};
		var data = {};
		data.datamanage = $('#manageDataCheck').get(0).checked;
//		data.speedlimit = $('#speedlimitCheck').get(0).checked;
//		data.speed = $('#speedSpinnerNumInput').val();
//		data.speedunit = $('#unit').val();
//		data.speedSize = calSpeedSize(data.speed, data.speedunit);
//		if(!data.speedlimit){
//			data.speedSize = 0;
//		}
		//授权
		data.authtype = $('#authSelect').val();
		data.authtypedes = $('#authSelect').find('option:selected').text();
		if(data.authtype == 1){
			//按容量
			data.vm = 999;
			data.fs = 999;
			data.db = 999;
			data.quotatype = $('#quotaSelect').val();
			data.quotatypedes = $('#quotaSelect').find('option:selected').text();
			data.quota = $('#quotaInput').val();
			data.quotaunit = $('#quotaUnit').val();
			data.quotaSize = calSize(data.quota, data.quotaunit);
			if(data.quotatype == 0){
				data.quotaSize = -1;
			}
		}else{
			//按个数
			data.vm = parseInt($('#vmNumInput').val());
			data.fs = parseInt($('#fsNumInput').val());
			data.db = parseInt($('#dbNumInput').val());
			data.quotaSize = -1;
		}
		settings.common = data;
		settings.tenantuuid = $('#tenantuuid').val();
		bootbox.confirm({
            title: LANG.UI_TENANT_KEEP_PUB_CONFIG,
            message: LANG.UI_TENANT_KEEP_PUB_CONFIG_TIPS1 +"<br>" + 
            		 LANG.UI_TENANT_KEEP_PUB_CONFIG_TIPS2 + "<br>" + "<br>" +
            		 LANG.UI_TENANT_OPERATE_TIPS,
            callback: function(r) {
                if(!r) return;
                var params = JSON.stringify(settings);
                Metronic.blockUI({target:".highSettingsDiv",animate: true});
        		$.post(CONF.AJAXPATH,{m:CONF.M.TENANT, f:"addTenantHighSettings",p:params},function(d){
        			Metronic.unblockUI('.highSettingsDiv');
        			if(OPREL(d)){
        				
        			}
        		});
            }
		});
		
		
	}
	
	//备份配置提交
//	var backupSubmit = function(){
//		var settings = {};
//		var data = {};
//		data.nodetype = $('#nodeType').val();
//		data.nodetypedes = $('#nodeType').find('option:selected').text();
//		data.node = $('#nodeSelect').val();
//		data.nodename = $('#nodeSelect').find('option:selected').text();
//		
//		data.storagetype = $('#storageType').val();
//		data.storagetypedes = $('#storageType').find('option:selected').text();
//		
//		data.storage = $('#storageSelect').val();
//		data.storagename = $('#storageSelect').find('option:selected').text();
//		
//		settings.backup = data;
//		settings.tenantuuid = $('#tenantuuid').val();
//		var params = JSON.stringify(settings);
//		settings.tenantuuid = $('#tenantuuid').val();
//		$.post(CONF.AJAXPATH,{m:CONF.M.TENANT, f:"addTenantHighSettings",p:params},function(d){
//			if(OPREL(d)){
//				
//			}
//		});
//	}
	
	//恢复配置提交
	var recoverSubmit = function(){
		var settings = {};
		var data = {};
		data.hosttype = $('#hostType').val();
//		if(data.hosttype == ""){
//			UIToastr.showInfo(LANG.UI_TENANT_RECOV_CONFIG, LANG.UI_TENANT_CHOOSE_HOST_TYPE_FIRST);
//			return false;
//		}
		data.hosttypedes = $('#hostType').find('option:selected').text();
		data.host = "";
		data.vcenter = "";
		data.desstoragetype = $('#desStorageType').val();
		data.desstoragedes = $('#desStorageType').find('option:selected').text();
		data.hostname = "---";
		//指定恢复宿主机
		if(data.hosttype == 1 && hostTree){
			var checkNode = hostTree.getCheckedNodes(true);
			if(checkNode.length != 0){
				data.host = checkNode[0].id;
				data.vcenter = checkNode[0].vcuuid;
				data.hostname = checkNode[0].name +"("+ checkNode[0].hypervisor_des + ")";
			}
		}
		data.desstorage = $('#desStorageSelect').val();
		data.desstoragename = $('#desStorageSelect').find('option:selected').text();
//		data.disktype = $('#diskSelect').val();
//		data.mactype = $('#macSelect').val();
		data.networktype = $('#networkType').val();
		data.networktypedes = $('#networkType').find('option:selected').text();
		data.network = $('#networkSelect').val();
		data.networkname = $('#networkSelect').find('option:selected').text();
		
		settings.recover = data;
		settings.tenantuuid = $('#tenantuuid').val();
		var params = JSON.stringify(settings);
		$.post(CONF.AJAXPATH,{m:CONF.M.TENANT, f:"addTenantHighSettings",p:params},function(d){
			if(OPREL(d)){
				
			}
		});
	}
	
//	//计费配置提交
//	var billingSubmit = function(){
//		var settings = {};
//		var data = {};
//		data.billingcheck = $('#billingCheck').get(0).checked; 
//		data.billing = $('#billingSelect').val();
//		data.billingstrategy = $('#billingSelect').find('option:selected').text();
//		
//		
//		settings.billing = data;
//		settings.tenantuuid = $('#tenantuuid').val();
//		var params = JSON.stringify(settings);
//		$.post(CONF.AJAXPATH,{m:CONF.M.TENANT, f:"addTenantHighSettings",p:params},function(d){
//			if(OPREL(d)){
//				
//			}
//		});
//	}
	
	
	var initSpinner = function(){
		//初始化容量单位
    	$('#spinnerNum').spinner({value: 20, step: 5, min: 1,max: 9999});
    	//容量单位默认GB
    	$('#quotaUnit').val('GB');
    	
    	//初始化限速单位
    	$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 99999999});
    	
    	//虚拟机、文件代理、数据库代理个数
    	$('#vmNumDiv').spinner({value: 10, step: 5, min: 1,max: 99999});
    	$('#fsNumDiv').spinner({value: 10, step: 5, min: 1,max: 99999});
    	$('#dbNumDiv').spinner({value: 10, step: 5, min: 1,max: 99999});
	}
	
//	//初始化备份节点
//	var initBackupNode = function(){
//		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
//    		var data = JSON.parse(d);
//    		var softselect = $('#nodeSelect');
//    		softselect.empty();
//			for(var i=0; i<data.length; i++){
//				var option = $("<option>").text(data[i].text).val(data[i].uuid);
//				softselect.append(option);
//			}
//			nodeSelectFlag = true;
//			initBackupStorage();  //初始化存储下拉框
//    	});
//	}
//	
//	//初始化备份存储
//	var initBackupStorage = function(){
//		var data = {};
//		data.nodeuuid = $('#nodeSelect').val();
//		var jsonData = JSON.stringify(data);
//		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
//    		var data = JSON.parse(d);
//    		var softselect = $('#storageSelect');
//    		softselect.empty();
//			for(var i=0; i<data.length; i++){
//				var option = $("<option>").text(data[i].text).val(data[i].uuid);
//				softselect.append(option);
//			}
//			if(!initBackupFlag){
//				initBackupSettings(oldJsonData.backup);
//			}
//    	});
//	}
	
	//初始化宿主机
	var initHostSelect = function(){
		var data = {};
		data.tenantFlag = true;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getRecoverHost',p:p}, setHostTree);
	}
	
	//检查没有宿主机切换提示信息
	var checkHostNodeInfo = function(zNodes, id){
		if(zNodes == "[]"){
			$("#nohosttips").show();
			$('.'+id).hide();
			return false;
		}else{
			$("#nohosttips").hide();
			$('.'+id).show();
			return true;
		}
	}
	
	//初始化宿主机树
	var setHostTree = function(zNodes){
		if(!checkHostNodeInfo(zNodes, 'tree_div2')) return;
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true
					}
				},
				view: {
					// addHoverDom: addHoverDom,
					// removeHoverDom: removeHoverDom,
				},
				callback: {
					beforeClick: hostNodeSelect,
					onCheck: hostOnCheck,
					beforeExpand: recoveryNodeExpand,
				}
			};
		hostTree = $.fn.zTree.init($("#host_tree"), setting, JSON.parse(zNodes));
		initHostFlag = true;
		var allNodes = $.fn.zTree.getZTreeObj('host_tree').getNodes();
		//勾选之前选中宿主机
		for (var i = 0;i<allNodes.length;i++) {
			if(allNodes[i].id == oldVcenter){
				hostNodeSelect('host_tree', allNodes[i], false);
			}
		}
		
	}
	
	//选择宿主机节点事件绑定
	var hostNodeSelect = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type){
			hostTree.checkNode(treeNode, !treeNode.checked, false, true);
		}else{
			recoveryNodeExpand(treeId, treeNode);
			hostTree.expandNode(treeNode, true);
		}
		addHoverDom(treeId, treeNode);
	}
	
	var hostOnCheck = function(e, id, node){
		var allNodes = hostTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			hostTree.checkNode(allNodes[i], false, false, false);	
		}
		hostTree.checkNode(node, true, false, false);
		oldDesStorage = "";
		oldDesNetwork = "";
		var _hypervisor = node.hypervisor;
		if(CONF.VMTYPE_GROUP.OPENSTACK.includes(_hypervisor)){
			initNetworkAndStoreGroupUser(node);
		}else{
			initNetworkAndStore(node);
		}
	}
	
	//恢复目标树统一展开
	var recoveryNodeExpand = function(treeId, treeNode){
		var hypervisor = treeNode.hypervisor;
		if(CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)){
			//openstack按租户显示恢复目标
			return vcenterNodeExpand(treeId, treeNode);
		}else{
			//其他按宿主机显示
			return hostNodeExpand(treeId, treeNode);
		}
		
	}
	
	//恢复目的分组展开
	var vcenterNodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			var data = JSON.stringify({vcenteruuid:treeNode.id, hypervisor:treeNode.hypervisor});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:true, 
    	        data:{m:CONF.M.VCENTER,f:'getSyncVcenterGroup',p:data},
    	        success: function(data){ 
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		//勾选之前选中宿主机
    	        		for (var i = 0;i<result.msg.length;i++) {
    	        			if(result.msg[i].id == oldHost && result.msg[i].type == 2){
    	        				result.msg[i].checked = true;
    	        				initNetworkAndStoreGroupUser(result.msg[i]);
    	        			}
    	        		}
    	        		hostTree.addNodes(treeNode, result.msg, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        } 
    		});
		}else{
			return true;
		}
	}
	
	//选择宿主机节点展开事件绑定
	var hostNodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			var data = JSON.stringify({sid:treeNode.sid,id:treeNode.id,pid:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:false});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:true, 
    	        data:{m:CONF.M.VCENTER,f:'getSyncRecoveryVcenter',p:data},
    	        success: function(data){ 
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//勾选之前选中宿主机
    	        		for (var i = 0;i<result.msg.length;i++) {
    	        			if(result.msg[i].id == oldHost && result.msg[i].type == 2){
    	        				result.msg[i].checked = true;
    	        				initNetworkAndStore(result.msg[i]);
    	        			}
    	        		}
    	        		//success
    	        		hostTree.addNodes(treeNode, result.msg, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        } 
    		});
		}else{
			return true;
		}
	}
	
	//添加虚拟化中心鼠标指上去事件
	var addHoverDom = function(treeId, treeNode) {
		if(1 != treeNode.type) return;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		var aObj = $("#" + nodeTID + "_a");
		if ($("#diyHref_" + nodeTID + nodeID + "_1").length>0) return;
		var str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>' + 
				  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' + 
				  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
		aObj.after(str);
//		aObj.append(str);
		var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
		var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
		var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");
		
		if (hrefRefresh) hrefRefresh.bind("click", function(){
			hostRefresh(treeId, treeNode);
		});
		if (hrefExpand) hrefExpand.bind("click", function(){
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
		});
		if (hrefCollapse) hrefCollapse.bind("click", function(event){
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true, false);
		});
	}
	

	//选择宿主机节点展开事件绑定
	var hostRefresh = function(treeId, treeNode){
		if(1 == treeNode.type){
			var data = JSON.stringify({sid:treeNode.sid,id:treeNode.id,pid:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:true});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:true, 
    	        data:{m:CONF.M.VCENTER,f:'getSyncRecoveryVcenter',p:data},
    	        success: function(data){ 
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
    	        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
    	        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        } 
    		});
		}else{
			return true;
		}
	}
	
	//添加虚拟化中心鼠标移除事件
	var removeHoverDom = function(treeId, treeNode) {
		if(1 != treeNode.type) return ;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		if(1 != treeNode.type) return ;
		$("#diyHref_" + nodeTID + nodeID + "_1").unbind().remove();
		$("#diyHref_" + nodeTID + nodeID+ "_2").unbind().remove();
		$("#diyHref_" + nodeTID + nodeID + "_3").unbind().remove();
		$("#diyBtn_space_" + escapeJquery(treeNode.id)).unbind().remove();
	}
	
	//初始化网络和存储(一般情况)
	var initNetworkAndStore = function(node){
		var p = {};
		p.hypervisor = node.hypervisor;
		p.vcenteruuid = node.vcuuid;
		p.hostuuid = node.id;
		var jsonData = JSON.stringify(p);
		Metronic.blockUI({target: '.highSettingsDiv',animate: true});
		$('#desStorageSelect').empty();
		$('#networkSelect').empty();
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getNetworkAndStorage',p:jsonData}, function(d){
			Metronic.unblockUI('.highSettingsDiv');
			var data = JSON.parse(d);
			
			initDesStroage(data.storage);
			initDesNetwork(data.network);
			
			//指定宿主机
			$('#networkType').prop('disabled', false);
			$('#desStorageType').prop('disabled', false);
    	});
	}
	
	//初始化网络和存储(flexcloud,openstack)
	var initNetworkAndStoreGroupUser = function(node){
		var data = JSON.stringify({vcenteruuid:node.vcuuid, hypervisor:node.hypervisor, groupname:node.name, 
			groupuuid:node.groupuuid, username:node.username, password:node.password});
		Metronic.blockUI({target: '.highSettingsDiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getOpenStackNetworkAndStorage',p:data}, function(d){
			Metronic.unblockUI('.highSettingsDiv');
			var data = JSON.parse(d);
			initDesStroage(data.storage);
			initDesNetwork(data.network);
			
			//指定宿主机
			$('#networkType').prop('disabled', false);
			$('#desStorageType').prop('disabled', false);
    	});
	}
	
	//初始化目的存储
	var initDesStroage = function(data){
		var hoststorage = $('#desStorageSelect');
		hoststorage.empty();
		for(var i=0; i<data.length; i++){
			if(data[i].uuid == "0") continue;
			var option = $("<option>").text(data[i].text).val(data[i].uuid);
			hoststorage.append(option);
		}
		if(oldDesStorage){
			hoststorage.val(oldDesStorage);
		}
	}
	
	//初始化目的网络
	var initDesNetwork = function(data){
		var hostnetwork = $('#networkSelect');
		hostnetwork.empty();
		for(var i=0; i<data.length; i++){
			if(data[i].uuid == "0") continue;
			var option = $("<option>").text(data[i].text).val(data[i].uuid);
			hostnetwork.append(option);
		}
		
		if(oldDesNetwork){
			hostnetwork.val(oldDesNetwork);
		}
	}
	
	var escapeJquery = function(srcString){  
        // 转义之后的结果  
        var escapseResult = srcString.toString();  
        // javascript正则表达式中的特殊字符  
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",  
                "]", "|", "{", "}"];  
        // jquery中的特殊字符,不是正则表达式中的特殊字符  
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",  
                ":", ";", "<", ">", ",", "/"];  
        for (var i = 0; i < jsSpecialChars.length; i++) {  
            escapseResult = escapseResult.replace(new RegExp("\\"  
                                    + jsSpecialChars[i], "g"), "\\"  
                            + jsSpecialChars[i]);  
        }  
        for (var i = 0; i < jquerySpecialChars.length; i++) {  
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],  
                            "g"), "\\" + jquerySpecialChars[i]);  
        }  
        return escapseResult;  
    } 
	
	//初始化可用配额显示
	var initQuota = function(){
    	$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:'getSystemFreeStorage',p:{}}, function(d){
    		var data = JSON.parse(d);
    		var des = LANG.UI_TENANT_ALLO_CAPACITY + data.freeDes;
        	$('#maxNum').html(des);
        	//如果不是容量授权
        	if(data.licenseType != 3){
        		$('.maxNum').hide();
        	}
    	});
    }
	
	return {
		init: function(){
			initListeners();
			initSpinner();
			initTenantBasicInfo();
			initTenantHighConfig();
			initTenantResource();
			initUserTable();
			initUserGroupTable();
			initTenantResourceNum();
			initQuota();
			
		}
	}
	
}();

jQuery(document).ready(function(){
	Tenant.init();
});