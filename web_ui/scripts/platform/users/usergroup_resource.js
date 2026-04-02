var UserGroupResources = function(){
	var fileGrid, dbGrid, cdpGrid, vmGrid, ApplianceGrid, nodeGrid, storageGrid, resourceGroupGrid;	//定义文件代理|数据库定时主机|数据库实时主机|虚拟机|备份代理|节点|存储|资源组
	var userGroupFileHost, userGroupDbHost, userGroupCDPHost, userGroupVM, userGroupAppliance, userGroupNode, userGroupStorage, userGroupResourceGroup; //定义用户组所有的文件代理|数据库定时主机|数据库实时主机|虚拟机|备份代理|节点|存储|资源组
	
	var initListeners = function(){
		//添加文件代理给用户组
		$('#file_add').on('click', function(){
			addFileHost();
		});
		//删除用户组中的文件代理
		$('#file_delete').on('click', function(){
			deleteFileHost();
		});
		
		//添加数据库定时主机给用户组
		$('#database_add').on('click', function(){
			addDbHost();
		});
		//删除用户组中的数据库定时主机
		$('#database_delete').on('click', function(){
			deleteDbHost();
		});
		
		//添加数据库实时代理给用户组
		$('#cdp_add').on('click', function(){
			addCDPHost();
		});
		//删除用户组中的数据库实时代理
		$('#cdp_delete').on('click', function(){
			deleteCDPHost();
		});
		
		//添加虚拟机给用户组
		$('#vm_add').on('click', function(){
			addVM();
		});
		//删除用户组中的虚拟机
		$('#vm_delete').on('click', function(){
			deleteVM();
		});
		
		//添加虚拟机备份代理给用户组
		$('#appliance_add').on('click', function(){
			addAppliance();
		});
		//删除用户组中的虚拟机备份代理
		$('#appliance_delete').on('click', function(){
			deleteAppliance();
		});
		
		//添加节点给用户组
		$('#node_add').on('click', function(){
			addNode();
		});
		//删除用户组中的节点
		$('#node_delete').on('click', function(){
			deleteNode();
		});
		
		//添加存储给用户组
		$('#storage_add').on('click', function(){
			addStorage();
		});
		//删除用户组中的存储
		$('#storage_delete').on('click', function(){
			deleteStorage();
		});
		
		//添加资源组给用户组
		$('#resourcegroup_add').on('click', function(){
			addResourceGroup();
		});
		//删除用户组中的资源组
		$('#resourcegroup_delete').on('click', function(){
			deleteResourceGroup();
		});
		
		//初始化各种资源搜索功能模块
		initSearchListeners();
		
		
		//切换虚拟化类型
		$('#userHypervisor').on('change', userHypervisorChange);
		
		$('#allHypervisor').on('change', allHypervisorChange);
	}
	
	//切换虚拟化类型
	var userHypervisorChange = function(){
		//搜索项
		var hypervisor = $('#userHypervisor').val();
		
		var p = {start:0, length:10, search:{name:'', hypervisor: hypervisor}};
		vmGrid.getRefresh(p,undefined,true);
	}
	
	var allHypervisorChange = function(){
		//搜索项
		var hypervisor = $('#allHypervisor').val();
		
		var p = {start:0, length:10, search:{name:'', hypervisor: hypervisor}};
		userGroupVM.getRefresh(p,undefined,true);
	}
	
	//初始化各种资源搜索功能模块
	var initSearchListeners = function(){
		//文件代理
		$('#searchFileBtn').on('click', searchAddFile);
		$('#searchFile').keypress(function (e) {
            if (e.which == 13) {
            	searchAddFile();
            }
        });
		//数据库定时主机
		$('#searchDatabaseBtn').on('click', searchAddDb);
		$('#searchDatabase').keypress(function (e) {
            if (e.which == 13) {
            	searchAddDb();
            }
        });
		//数据库实时主机
		$('#searchCdpBtn').on('click', searchAddCdp);
		$('#searchCdp').keypress(function (e) {
            if (e.which == 13) {
            	searchAddCdp();
            }
        });
		//虚拟机
		$('#searchVmBtn').on('click', searchAddVm);
		$('#searchVm').keypress(function (e) {
            if (e.which == 13) {
            	searchAddVm();
            }
        });
		//虚拟机备份代理proxy
		$('#searchApplianceBtn').on('click', searchAddAppliance);
		$('#searchAppliance').keypress(function (e) {
            if (e.which == 13) {
            	searchAddAppliance();
            }
        });
		//节点
		$('#searchNodeBtn').on('click', searchAddNode);
		$('#searchNode').keypress(function (e) {
            if (e.which == 13) {
            	searchAddNode();
            }
        });
		//存储
		$('#searchStorageBtn').on('click', searchAddStorage);
		$('#searchStorage').keypress(function (e) {
            if (e.which == 13) {
            	searchAddRsourceGroup();
            }
        });
		
		//资源组
		$('#searchResourceGroupBtn').on('click', searchAddStorage);
		$('#searchResourceGroup').keypress(function (e) {
            if (e.which == 13) {
            	searchAddRsourceGroup();
            }
        });
	}
	
	//获取搜索参数集合
	var getSearchParams = function(name){
		var userGroupUUID = $('#usergroupuuid').val();
		var info = {
			search: {name:name},
			userGroupUUID: userGroupUUID,
			sourceFrom: CONF.SOURCE_FROM.USER_GROUP
			
		};
		return info;
	}
	
	//获取搜索参数集合
	var getVMSearchParams = function(name, hypervisor){
		var userGroupUUID = $('#usergroupuuid').val();
		var info = {
			search: {name:name, hypervisor:hypervisor},
			userGroupUUID: userGroupUUID,
			sourceFrom: CONF.SOURCE_FROM.USER_GROUP
			
		};
		return info;
	}
	
	
	//搜索添加的文件代理
	var searchAddFile = function(){
		var searchValue = $.trim($('#searchFile').val());
		var searchParams = getSearchParams(searchValue);
		var data = {m:CONF.M.RESOURCE,f:'getUserAllFileHost',p: searchParams};
		userGroupFileHost.setAjaxParam(data);
		userGroupFileHost.getRefresh({},undefined,true);
	}
	
	//搜索添加的数据库定时主机
	var searchAddDb = function(){
		var searchValue = $.trim($('#searchDatabase').val());
		var searchParams = getSearchParams(searchValue);
		var data = {m:CONF.M.RESOURCE,f:'getUserAllDbHost',p:searchParams};
		userGroupDbHost.setAjaxParam(data);
		userGroupDbHost.getRefresh({},undefined,true);
	}
	
	//搜索添加的数据库实时主机
	var searchAddCdp = function(){
		var searchValue = $.trim($('#searchCdp').val());
		
		var searchParams = getSearchParams(searchValue);
		
		var data = {m:CONF.M.RESOURCE,f:'getUserAllCDPHost',p:searchParams};
		userGroupCDPHost.setAjaxParam(data);
		userGroupCDPHost.getRefresh({},undefined,true);
	}
	
	//搜索添加的虚拟机
	var searchAddVm = function(){
		var searchValue = $.trim($('#searchVm').val());
		//搜索项
		var hypervisor = $('#allHypervisor').val();
		var searchParams = getVMSearchParams(searchValue,hypervisor);
		var data = {m:CONF.M.RESOURCE,f:'getUserAllVM',p:searchParams};
		userGroupVM.setAjaxParam(data);
		userGroupVM.getRefresh({},undefined,true);
	}
	
	//搜索添加的appliance
	var searchAddAppliance = function(){
		var searchValue = $.trim($('#searchAppliance').val());
		var searchParams = getSearchParams(searchValue);
		var data = {m:CONF.M.RESOURCE,f:'getUserAllAppliance',p:searchParams};
		userGroupAppliance.setAjaxParam(data);
		userGroupAppliance.getRefresh({},undefined,true);
	}
	
	//搜索添加的节点
	var searchAddNode = function(){
		var searchValue = $.trim($('#searchNode').val());
		var searchParams = getSearchParams(searchValue);
		var data = {m:CONF.M.RESOURCE,f:'getUserAllNode',p:searchParams};
		userGroupNode.setAjaxParam(data);
		userGroupNode.getRefresh({},undefined,true);
	}

	//搜索添加的存储
	var searchAddStorage = function(){
		var searchValue = $.trim($('#searchStorage').val());
		var searchParams = getSearchParams(searchValue);
		var data = {m:CONF.M.RESOURCE,f:'getUserAllStorage',p:searchParams};
		userGroupStorage.setAjaxParam(data);
		userGroupStorage.getRefresh({},undefined,true);
	}
	
	//搜索添加的资源组
	var searchAddRsourceGroup = function(){
		var searchValue = $.trim($('#searchResourceGroup').val());
		var searchParams = getSearchParams(searchValue);
		var data = {m:CONF.M.RESOURCE,f:'getAllResourceGroup',p:searchParams};
		userGroupResourceGroup.setAjaxParam(data);
		userGroupResourceGroup.getRefresh({},undefined,true);
	}
	
	
	
	//初始化资源列表
	var initDatatableList = function(){
		//初始化文件代理主机
		initFlieHostList();
		//初始化数据库定时主机
		initDbHostList();
		//初始化数据库实时主机
		initDbCdpHostList();
		//初始化虚拟机
		initVmList();
		//初始化备份代理
		initApplianceList();
		//初始化节点
		initNodeList();
		//初始化存储
		initStorageList();
		//初始化资源组
		initResourceGroupList();
		
	}
	
	
	
	
	/*-------------------文件代理主机---------------------*/
	//初始化文件代理主机
	var initFlieHostList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		fileGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupFileHostResource',p:{userGroupUUID: userGroupUUID}};
		fileGrid.setAjaxParam(data);
		fileGrid.init({src: $("#fileDatatable"), dataTable:dataTableOpt, onDataLoad: initFileRow});
	}
	
	//初始化加载用户组所有文件代理样式
	var initFileRow = function(){
		var data = fileGrid.getDataTable().data();
		if(!data || data.length == 0) return;
		var statusDiv = $('#fileDatatable tbody > tr').find('td:eq(4)');
		for(var i=0; i<statusDiv.length; i++){
			addStatus(statusDiv[i], data[i]);
		}
	}
	
	//初始化加载用户组所有文件代理样式
	var initUserGroupFileRow = function(){
		var data = userGroupFileHost.getDataTable().data();
		if(!data || data.length == 0) return;
		var statusDiv = $('#userFileHost tbody > tr').find('td:eq(4)');
		for(var i=0; i<statusDiv.length; i++){
			addStatus(statusDiv[i], data[i]);
		}
	}
	
	//添加状态信息
	var addStatus = function(div, data){
		var labelClass = getStatusClass(data[5]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[4] + '</span>';
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
	
	//添加文件代理给用户组
	var addFileHost = function(){
		$('#fileHostModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupFileHost){
			initUserGroupFileHost();
		}
	}
	
	var initUserGroupFileHost = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupFileHost = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserAllFileHost',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupFileHost.setAjaxParam(data);
		userGroupFileHost.init({src: $("#userFileHost"), dataTable:dataTableOpt, onDataLoad: initUserGroupFileRow});
		$('#file_submit').on('click', function(){
			addFileHostSubmit();
		});
	}
	
	//添加文件代理
	var addFileHostSubmit = function(){
		var select = userGroupFileHost.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_FILE_HOST, LANG.UI_RESOURCE_ADD_FILE_HOST_NO_SELECT);
		}
		var data = {};
		var list = [];
		for(var i=0;i<select.length;i++){
			var info = {
				'resourceuuid': select[i],
				'vmuuid': "",
				'vcenteruuid': "",
			} 
			list.push(info);
		}
		data.list = list;
		
		data.userGroupuuid = $('#usergroupuuid').val();

		pAjaxRequest(data,'/api/v1/users/usergroup/filehost','POST',function(d){
			if(operateResponseList(d)){
				userGroupFileHost.getRefresh({});
				fileGrid.getRefresh({});
				$('#fileHostModal').modal('hide');
			}

		})

	}
	
	
	//删除用户组选中的文件代理
	var deleteFileHost = function(){
		var select = fileGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_FILE_HOST, LANG.UI_RESOURCE_CANCEL_FILE_HOST_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_FILE_HOST,
			message: LANG.UI_RESOURCE_CANCEL_FILE_HOST_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupFileHost", p: p}, function(d){
					if(OPREL(d)){
						fileGrid.getRefresh({});
						userGroupFileHost.getRefresh({});
					}
				});
			}
		});
	}
	
	/*-------------------数据库定时主机---------------------*/
	//初始化数据库定时主机
	var initDbHostList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		dbGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupDbHostResource',p:{userGroupUUID: userGroupUUID}};
		dbGrid.setAjaxParam(data);
		dbGrid.init({src: $("#dbDatatable"), dataTable:dataTableOpt, onDataLoad: initDbRow});
	}
	
	//初始化加载用户组所有数据库定时主机样式
	var initDbRow = function(){
		var data = dbGrid.getDataTable().data();
		if(!data || data.length == 0) return;
		var statusDiv = $('#dbDatatable tbody > tr').find('td:eq(4)');
		for(var i=0; i<statusDiv.length; i++){
			addStatus(statusDiv[i], data[i]);
		}
	}
	
	//初始化加载用户组所有数据库主机样式
	var initUserGroupDbRow = function(){
		var data = userGroupDbHost.getDataTable().data();
		if(!data || data.length == 0) return;
		var statusDiv = $('#userDbHost tbody > tr').find('td:eq(4)');
		for(var i=0; i<statusDiv.length; i++){
			addStatus(statusDiv[i], data[i]);
		}
	}
	
	//添加状态信息
	var addStatus = function(div, data){
		var labelClass = getStatusClass(data[5]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[4] + '</span>';
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
	
	//添加数据库定时主机给用户组
	var addDbHost = function(){
		$('#dbHostModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupDbHost){
			initUserGroupDbHost();
		}
	}
	
	var initUserGroupDbHost = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupDbHost = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserAllDbHost',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupDbHost.setAjaxParam(data);
		userGroupDbHost.init({src: $("#userDbHost"), dataTable:dataTableOpt, onDataLoad: initUserGroupDbRow});
		$('#database_submit').on('click', function(){
			addDbHostSubmit();
		});
	}
	
	//添加文件代理
	var addDbHostSubmit = function(){
		var select = userGroupDbHost.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_DB_HOST, LANG.UI_RESOURCE_ADD_DB_HOST_NO_SELECT);
		}
		var data = {};
		var list = [];
		for(var i=0;i<select.length;i++){
			var info = {
				'resourceuuid': select[i],
				'vmuuid': "",
				'vcenteruuid': "",
			} 
			list.push(info);
		}
		data.list = list;
		
		data.userGroupUUID = $('#usergroupuuid').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "addUserGroupDbHost", p: p}, function(d){
			if(OPREL(d)){
				userGroupDbHost.getRefresh({});
				dbGrid.getRefresh({});
				$('#dbHostModal').modal('hide');
			}
			
		})
	}
	
	
	//删除用户组选中的数据库定时主机
	var deleteDbHost = function(){
		var select = dbGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_DB_HOST, LANG.UI_RESOURCE_CANCEL_DB_HOST_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_DB_HOST,
			message: LANG.UI_RESOURCE_CANCEL_DB_HOST_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupDbHost", p: p}, function(d){
					if(OPREL(d)){
						dbGrid.getRefresh({});
						userGroupDbHost.getRefresh({});
					}
				});
			}
		});
	}
	
	
	
	/*-------------------数据库实时主机---------------------*/
	//初始化数据库实时主机
	var initDbCdpHostList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		cdpGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupCDPHostResource',p:{userGroupUUID: userGroupUUID}};
		cdpGrid.setAjaxParam(data);
		cdpGrid.init({src: $("#cdpDatatable"), dataTable:dataTableOpt, onDataLoad:initCDPHostRow});
	}
	
	//初始化数据库实时主机列表样式
	var initCDPHostRow = function(){
		var data = cdpGrid.getDataTable().data();
		if(!data || 0 == data.length) return;
		var statusDiv = $('#cdpDatatable tbody > tr').find('td:eq(5)');
		for(var i=0; i<statusDiv.length; i++){
			hostStatus(statusDiv[i], data[i]);
		}
	}
	

	//初始化用户组拥有的数据库实时主机列表样式
	var initUserGroupCDPHostRow = function(){
		var data = userGroupCDPHost.getDataTable().data();
		if(!data || 0 == data.length) return;
		var statusDiv = $('#userCDPHost tbody > tr').find('td:eq(5)');
		for(var i=0; i<statusDiv.length; i++){
			hostStatus(statusDiv[i], data[i]);
		}
	}
	
	
	//主机状态
	var hostStatus = function(div, data){
		var thisClass = "label-info";
		if(1 == data[6]){
			thisClass = "label-success";
		}else if(2 == data[6]){
			thisClass = "label-default";
		}else if(3 == data[6]){
			thisClass = "label-warning";
		}
		var statusDes = '<span class="label label-sm ' + thisClass + '">' + data[5] + '</span>';
		$(div).html(statusDes);
	}
	
	//添加数据库实时代理与用户组关联
	var addCDPHost = function(){
		$('#cdpHostModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupCDPHost){
			initUserGroupCDPHost();
		}
	}
	
	//初始化用户组数据库实时主机列表
	var initUserGroupCDPHost = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupCDPHost = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserAllCDPHost',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupCDPHost.setAjaxParam(data);
		userGroupCDPHost.init({src: $("#userCDPHost"), dataTable:dataTableOpt, onDataLoad: initUserGroupCDPHostRow});
		
		$('#cdp_submit').on('click', function(){
			addCDPHostSubmit();
		});
	}
	
	
	//添加数据库实时主机
	var addCDPHostSubmit = function(){
		var select = userGroupCDPHost.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_CDP_HOST, LANG.UI_RESOURCE_ADD_CDP_HOST_NO_SELECT);
		}
		var data = {};
		var list = [];
		for(var i=0;i<select.length;i++){
			var info = {
				'resourceuuid': select[i],
				'vmuuid': "",
				'vcenteruuid': "",
			} 
			list.push(info);
		}
		data.list = list;
		data.userGroupUUID = $('#usergroupuuid').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "addUserGroupCDPHost", p: p}, function(d){
			if(OPREL(d)){
				userGroupCDPHost.getRefresh({});
				cdpGrid.getRefresh({});
				$('#cdpHostModal').modal('hide');
			}
			
		})
	}
	
	//取消数据库实时代理与用户组关联
	var deleteCDPHost = function(){
		var select = cdpGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_CDP_HOST, LANG.UI_RESOURCE_CANCEL_CDP_HOST_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_CDP_HOST,
			message: LANG.UI_RESOURCE_CANCEL_CDP_HOST_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupCDPHost", p: p}, function(d){
					if(OPREL(d)){
						cdpGrid.getRefresh({});
						userGroupCDPHost.getRefresh({});
					}
				});
			}
		});
	}
	
	
	
	
	/*-------------------虚拟机---------------------*/
	//初始化虚拟机
	var initVmList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		vmGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupVMResource',p:{userGroupUUID: userGroupUUID}};
		vmGrid.setAjaxParam(data);
		vmGrid.init({src: $("#vmDatatable"), dataTable:dataTableOpt, onDataLoad: initVmRow});
	}
	
	
	//初始化用户组所有虚拟机列表样式
	var initVmRow = function(){
		var data = vmGrid.getDataTable().data();
		if(!data || 0 == data.length) return;
		var statusDiv = $('#vmDatatable tbody > tr').find('td:eq(4)');
		for(var i=0; i<data.length; i++){
			statusDiy(statusDiv[i], data[i]);
		}
	}
	
	//初始化用户组所有虚拟机列表样式
	var initUserGroupVmRow = function(){
		var data = userGroupVM.getDataTable().data();
		if(!data || 0 == data.length) return;
		var statusDiv = $('#userVM tbody > tr').find('td:eq(4)');
		for(var i=0; i<data.length; i++){
			statusDiy(statusDiv[i], data[i]);
		}
	}
	
	//添加虚拟机状态
	var statusDiy = function(div, data){
		var status = '<span class="label label-sm ' + getVmStatusLevel(data[7]) + '">' + data[4] + '</span>';
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
	
	
	
	
	//添加虚拟机与用户组关联
	var addVM = function(){
		$('#vmModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupVM){
			initUserGroupVM();
		}
	}
	
	//初始化用户组虚拟机列表
	var initUserGroupVM = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupVM = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserAllVM',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupVM.setAjaxParam(data);
		userGroupVM.init({src: $("#userVM"), dataTable:dataTableOpt, onDataLoad: initUserGroupVmRow});
		
		$('#vm_submit').on('click', function(){
			addVMSubmit();
		});
	}
	
	//添加虚拟机关联
	var addVMSubmit = function(){
		var select = userGroupVM.getSelectedRows();
		var vmData = userGroupVM.getDataTable().data();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_VM, LANG.UI_RESOURCE_ADD_VM_NO_SELECT);
		}
		var data = {};
		var list = [];
		for(var i=0;i<vmData.length;i++){
			for(var j=0;j<select.length;j++){
				if(vmData[i][5] == select[j]){
					var info = {
						'resourceuuid': select[j],
						'vmuuid': vmData[i][5],
						'vcenteruuid': vmData[i][6],
					} 
					list.push(info);
				}
			}
		}
		data.list = list;
		data.userGroupUUID = $('#usergroupuuid').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "addUserGroupVM", p: p}, function(d){
			if(OPREL(d)){
				userGroupVM.getRefresh({});
				vmGrid.getRefresh({});
				$('#vmModal').modal('hide');
			}
			
		})
	}
	
	//取消虚拟机与用户组关联
	var deleteVM = function(){
		var select = vmGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_VM, LANG.UI_RESOURCE_CANCEL_VM_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_VM,
			message: LANG.UI_RESOURCE_CANCEL_VM_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupVM", p: p}, function(d){
					if(OPREL(d)){
						vmGrid.getRefresh({});
						userGroupVM.getRefresh({});
					}
				});
			}
		});
	}
	
	
	
	
	
	/*-------------------备份代理---------------------*/
	//初始化备份代理
	var initApplianceList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		applianceGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupApplianceResource',p:{userGroupUUID: userGroupUUID}};
		applianceGrid.setAjaxParam(data);
		applianceGrid.init({src: $("#applianceDatatable"), dataTable:dataTableOpt, onDataLoad: initApplianceRow});
	}
	
	//初始化虚拟机备份代理列表样式
    var initApplianceRow = function(){
		var data = applianceGrid.getDataTable().data();
		if(!data || 0 == data.length) return;
		var levelDiv = $('#applianceDatatable tbody > tr').find('td:eq(4)');
		for(var i=0; i<levelDiv.length; i++){
			setApplianceLevel(levelDiv[i], data[i]);
		}
	}
    
  //初始化虚拟机备份代理列表样式
    var initUserGroupApplianceRow = function(){
		var data = userGroupAppliance.getDataTable().data();
		if(!data || 0 == data.length) return;
		var levelDiv = $('#userAppliance tbody > tr').find('td:eq(4)');
		for(var i=0; i<levelDiv.length; i++){
			setApplianceLevel(levelDiv[i], data[i]);
		}
	}
	
    //在线/离线(appliance状态)
	var setApplianceLevel = function(div, data){
		var labelClass = getApplianceStatusClass(data[5]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[4] + '</span>';
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
    
	
	//添加虚拟机备份代理与用户组关联
	var addAppliance = function(){
		$('#applianceModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupAppliance){
			initUserGroupAppliance();
		}
	}
	
	//初始化用户组虚拟机备份代理列表
	var initUserGroupAppliance = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupAppliance = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserAllAppliance',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupAppliance.setAjaxParam(data);
		userGroupAppliance.init({src: $("#userAppliance"), dataTable:dataTableOpt, onDataLoad: initUserGroupApplianceRow});
		
		$('#appliance_submit').on('click', function(){
			addApplianceSubmit();
		});
	}
	
	
	//添加虚拟机备份代理
	var addApplianceSubmit = function(){
		var select = userGroupAppliance.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_APPLIANCE, LANG.UI_RESOURCE_ADD_APPLIANCE_NO_SELECT);
		}
		var data = {};
		var list = [];
		for(var i=0;i<select.length;i++){
			var info = {
				'resourceuuid': select[i],
				'vmuuid': "",
				'vcenteruuid': "",
			} 
			list.push(info);
		}
		data.list = list;
		data.userGroupUUID = $('#usergroupuuid').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "addUserGroupAppliance", p: p}, function(d){
			if(OPREL(d)){
				userGroupAppliance.getRefresh({});
				applianceGrid.getRefresh({});
				$('#applianceModal').modal('hide');
			}
			
		})
	}
	
	//取消虚拟机备份代理与用户组关联
	var deleteAppliance = function(){
		var select = applianceGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_APPLIANCE, LANG.UI_RESOURCE_CANCEL_APPLIANCE_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_APPLIANCE,
			message: LANG.UI_RESOURCE_CANCEL_APPLIANCE_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupAppliance", p: p}, function(d){
					if(OPREL(d)){
						applianceGrid.getRefresh({});
						userGroupAppliance.getRefresh({});
					}
				});
			}
		});
	}
	
	
	
	/*-------------------节点---------------------*/
	//初始化节点
	var initNodeList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		nodeGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupNodeResource',p:{userGroupUUID: userGroupUUID}};
		nodeGrid.setAjaxParam(data);
		nodeGrid.init({src: $("#nodeDatatable"), dataTable:dataTableOpt, onDataLoad: initNodeRow});
	}
	
	//初始化节点列表显示样式
	var initNodeRow = function(){
		var data = nodeGrid.getDataTable().data();
		if(!data || 0 == data.length) return;
		var deployStatusDiv = $('#nodeDatatable tbody > tr').find('td:eq(3)');
		var nodeStatusDiv = $('#nodeDatatable tbody > tr').find('td:eq(4)');
		for(var i=0; i<data.length; i++){
			setDeployStatus(deployStatusDiv[i], data[i]);
			setNodeStatus(nodeStatusDiv[i], data[i]);
		}
	}
	
	
	//初始化加载用户组所有文件代理样式
	var initUserGroupNodeRow = function(){
		var data = userGroupNode.getDataTable().data();
		if(!data || 0 == data.length) return;
		var deployStatusDiv = $('#userNode tbody > tr').find('td:eq(3)');
		var nodeStatusDiv = $('#userNode tbody > tr').find('td:eq(4)');
		for(var i=0; i<data.length; i++){
			setDeployStatus(deployStatusDiv[i], data[i]);
			setNodeStatus(nodeStatusDiv[i], data[i]);
		}
	}
	
	//节点状态
	var setNodeStatus = function(div, data){
		var statusDes = LANG.UI_NODE_ABNORMAL;
		var level = 2;
		if(data[4]){
			//正常
			statusDes = LANG.UI_NODE_NORMAL;
			level = 1;
		}
		var labelClass = getLevelClass(level);
		var content = '<span class="label label-sm ' + labelClass + '">' + 
			statusDes + '</span>';
		if(!data[3]){
			//如果节点未部署
			content = '--';
		}
		$(div).html(content);
	}
	
	//部署状态
	var setDeployStatus = function(div, data){
		var deployDes = LANG.UI_NODE_UNDEPLOY;
		var level = 2;
		if(data && data[3]){
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
	
	//添加节点与用户组关联
	var addNode = function(){
		$('#nodeModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupNode){
			initUserGroupNode();
		}
	}
	
	//初始化用户组节点列表
	var initUserGroupNode = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupNode = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserAllNode',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupNode.setAjaxParam(data);
		userGroupNode.init({src: $("#userNode"), dataTable:dataTableOpt, onDataLoad: initUserGroupNodeRow});
		
		$('#node_submit').on('click', function(){
			addNodeSubmit();
		});
	}
	
	//添加节点
	var addNodeSubmit = function(){
		var select = userGroupNode.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_NODE, LANG.UI_RESOURCE_ADD_NODE_NO_SELECT);
		}
		var data = {};
		var list = [];
		for(var i=0;i<select.length;i++){
			var info = {
				'resourceuuid': select[i],
				'vmuuid': "",
				'vcenteruuid': "",
			} 
			list.push(info);
		}
		data.list = list;
		data.userGroupUUID = $('#usergroupuuid').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "addUserGroupNode", p: p}, function(d){
			if(OPREL(d)){
				userGroupNode.getRefresh({});
				nodeGrid.getRefresh({});
				$('#nodeModal').modal('hide');
			}
			
		})
	}
	
	//取消节点与用户组关联
	var deleteNode = function(){
		var select = nodeGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_NODE, LANG.UI_RESOURCE_CANCEL_NODE_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_NODE,
			message: LANG.UI_RESOURCE_CANCEL_NODE_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupNode", p: p}, function(d){
					if(OPREL(d)){
						nodeGrid.getRefresh({});
						userGroupNode.getRefresh({});
					}
				});
			}
		});
	}
	
	
	
	/*-------------------存储---------------------*/
	//初始化存储
	var initStorageList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		storageGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupStorageResource',p:{userGroupUUID: userGroupUUID}};
		storageGrid.setAjaxParam(data);
		storageGrid.init({src: $("#storageDatatable"), dataTable:dataTableOpt, onDataLoad: initStorageRow});
	}
	
	//初始化用户组所有存储列表样式
	var initStorageRow = function(){
		var data = storageGrid.getDataTable().data();
		if(!data || 0 == data.length) return;
		var levelDiv = $('#storageDatatable tbody > tr').find('td:eq(6)');
		for(var i=0; i<levelDiv.length; i++){
			setStorageLevel(levelDiv[i], data[i]);
		}
	}
	
	//初始化用户组所有存储列表样式
	var initUserGroupStorageRow = function(){
		var data = userGroupStorage.getDataTable().data();
		if(!data || 0 == data.length) return;
		var levelDiv = $('#userStorage tbody > tr').find('td:eq(6)');
		for(var i=0; i<levelDiv.length; i++){
			setStorageLevel(levelDiv[i], data[i]);
		}
	}
	

	//在线/创建中/离线/异常(节点在线状态异常)
	var setStorageLevel = function(div, data){
		var labelClass = getStorageStatusClass(data[6]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[8] + '</span>';
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
	
	//添加存储与用户组关联
	var addStorage = function(){
		$('#storageModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupStorage){
			initUserGroupStorage();
		}
	}
	
	//初始化用户组存储列表
	var initUserGroupStorage = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupStorage = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserAllStorage',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupStorage.setAjaxParam(data);
		userGroupStorage.init({src: $("#userStorage"), dataTable:dataTableOpt, onDataLoad: initUserGroupStorageRow});
		
		$('#storage_submit').on('click', function(){
			addStorageSubmit();
		});
	}
	
	//添加存储关联
	var addStorageSubmit = function(){
		var select = userGroupStorage.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_STORAGE, LANG.UI_RESOURCE_ADD_STORAGE_NO_SELECT);
		}
		var data = {};
		var list = [];
		for(var i=0;i<select.length;i++){
			var info = {
				'resourceuuid': select[i],
				'vmuuid': "",
				'vcenteruuid': "",
			} 
			list.push(info);
		}
		data.list = list;
		data.userGroupUUID = $('#usergroupuuid').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "addUserGroupStorage", p: p}, function(d){
			if(OPREL(d)){
				userGroupStorage.getRefresh({});
				storageGrid.getRefresh({});
				$('#storageModal').modal('hide');
			}
			
		})
	}
	
	//取消存储与用户组关联
	var deleteStorage = function(){
		var select = storageGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_STORAGE, LANG.UI_RESOURCE_CANCEL_STORAGE_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_STORAGE,
			message: LANG.UI_RESOURCE_CANCEL_STORAGE_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupStorage", p: p}, function(d){
					if(OPREL(d)){
						storageGrid.getRefresh({});
						userGroupStorage.getRefresh({});
					}
				});
			}
		});
	}
	
	/*-------------------资源组---------------------*/
	//初始化资源组
	var initResourceGroupList = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		resourceGroupGrid = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getUserGroupResourceGroup',p:{userGroupUUID: userGroupUUID}};
		resourceGroupGrid.setAjaxParam(data);
		resourceGroupGrid.init({src: $("#resourceGroupDatatable"), dataTable:dataTableOpt});
	}
	
	//添加资源组与用户组关联
	var addResourceGroup = function(){
		$('#resourceGroupModal').modal({'width': "1000px", "height": "400px"});
		if(!userGroupResourceGroup){
			initUserGroupResourceGroup();
		}
	}
	
	//初始化用户组资源组列表
	var initUserGroupResourceGroup = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0]
    			}],
    			"order": [
                    [1, "desc"]
                ],
    	};
		userGroupResourceGroup = new Datatable();
		var userGroupUUID = $('#usergroupuuid').val();
		var data = {m:CONF.M.RESOURCE,f:'getAllResourceGroup',p:{userGroupUUID: userGroupUUID, sourceFrom: CONF.SOURCE_FROM.USER_GROUP}};
		userGroupResourceGroup.setAjaxParam(data);
		userGroupResourceGroup.init({src: $("#userResourceGroup"), dataTable:dataTableOpt});
		
		$('#resourcegroup_submit').on('click', function(){
			addResourceGroupSubmit();
		});
	}
	
	//添加资源组
	var addResourceGroupSubmit = function(){
		var select = userGroupResourceGroup.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_ADD_RESOURCE_GROUP, LANG.UI_RESOURCE_ADD_RESOURCE_GROUP_NO_SELECT);
		}
		var data = {};
		data.list = select;
		data.userGroupUUID = $('#usergroupuuid').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "addUserGroupResourceGroup", p: p}, function(d){
			if(OPREL(d)){
				resourceGroupGrid.getRefresh({});
				userGroupResourceGroup.getRefresh({});
				$('#resourceGroupModal').modal('hide');
			}
			
		})
	}
	
	//取消节点与用户组关联
	var deleteResourceGroup = function(){
		var select = resourceGroupGrid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_CANCEL_RESOURCE_GROUP, LANG.UI_RESOURCE_CANCEL_RESOURCE_GROUP_NO_SELECT);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_CANCEL_RESOURCE_GROUP,
			message: LANG.UI_RESOURCE_CANCEL_RESOURCE_GROUP_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
				data.uuids = select;
				data.userGroupUUID = $('#usergroupuuid').val();
				var p = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.USER, f:"deleteUserGroupResourceGroup", p: p}, function(d){
					if(OPREL(d)){
						resourceGroupGrid.getRefresh({});
						userGroupResourceGroup.getRefresh({});
					}
				});
			}
		});
	}
	
	//初始化虚拟化类型
	var initUserVMType = function(){
		var data = {};
		data.userFlag = true;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'getAllHypervisorType',p:p}, function(d){
			var data = JSON.parse(d);
			var usertypeselect = $('#userHypervisor');
			var alltypeselect = $('#allHypervisor');
			usertypeselect.empty();
			alltypeselect.empty();
			usertypeselect.append($("<option>").text(LANG.UI_PUBLIC_ALL).val(''));
			alltypeselect.append($("<option>").text(LANG.UI_PUBLIC_ALL).val(''));
			for(var i=0; i<data.length; i++){
				usertypeselect.append($("<option>").text(data[i].text).val(data[i].value));
				alltypeselect.append($("<option>").text(data[i].text).val(data[i].value));
			}
		});
	}
	
	return {
		init: function(){
			initListeners();
			initDatatableList();
			initUserVMType();
		}
	}
	
}();

jQuery(document).ready(function(){
	UserGroupResources.init();
});