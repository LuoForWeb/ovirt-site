var resourceGroup = function(){
	var grid;
	var usergrid, userGroupgrid;
	var initGridFlag = false;
	
	
	//初始化列表每项数据
	var initRow = function(){
		var data = grid.getDataTable().data();
		if(!data || data.length == 0) return;
		
		var opDiv = $('#resource_table tbody > tr').find('td:eq(5)');
		var detailDiv = $('#resource_table tbody > tr').find('td:eq(1)');
		for(var i=0;i<data.length;i++){
			opHref(opDiv[i], data[i]);
			detailHref(detailDiv[i], data[i]);
		}
		
		//添加操作按钮点击事件
		addOpButtonListener();
		
		$('.resourceDetails').off().on('click', initModalDetails);
	}
	
	//初始化详情模态框
	var initModalDetails = function(){
		var data = {};
		data.resourcegroupuuid = $(this).get(0).id;
		var p = JSON.stringify(data);
		initUserTable(data);
		initUsergroupTable(data);
		initGridFlag = true;
		$('#resourceGroupDetails').modal({'width': '600px', height: '430px'});
	}
	
	var detailHref = function(div, data){
		var detailshtml = "<a class='resourceDetails' id='" + data[6] + "'>" + data[1] + "</a>";
		$(div).html(detailshtml);
	}
	
	
	
	var addOpButtonListener = function(){
		$('.modify').unbind().on('click', modifyResourceGroup);
	}
	
	
	//初始化修改模态框
	var modifyResourceGroup = function(){
		var resourcegroupuuid = this.name;
		var p = JSON.stringify({uuid:resourcegroupuuid});
		$.post(CONF.AJAXPATH,{m: CONF.M.RESOURCE, f: "getResourceGroupOldInfo", p: p}, function(d){
			var jsonData = JSON.parse(d);
			if(jsonData.length == 0) return;
			$('#modifyModal').modal({'width':'600px', 'height':'300px'});
			$('#resourcegroupname').val(jsonData.name).prop("disabled", true);
			$('#description').val(jsonData.description);
			$('#resourceGroupUUID').val(jsonData.uuid);
			
			$('#modify_submit').unbind().on('click', modifyConfirm);
		});
		
	}
	
	
	//修改资源组确认
	var modifyConfirm = function(){
		var data = {};
		data.resourceGroupUUID = $('#resourceGroupUUID').val();
		data.resourcegroupname = $('#resourcegroupname').val();
		if(data.resourcegroupname == ""){
			return UIToastr.showWarning(LANG.UI_RESOURCE_GROUP_MODIFY, LANG.UI_RESOURCE_GROUP_NAME_MUST);
		}
		data.description = $('#description').val();
		var p = JSON.stringify(data);
		Metronic.blockUI({target: "#modifyModal", animate: true});
		$.post(CONF.AJAXPATH,{m: CONF.M.RESOURCE, f: "editResourceGroup", p: p}, function(d){
			Metronic.unblockUI("#modifyModal");
			if(OPREL(d)){
				$('#modifyModal').modal('hide');
				grid.getRefresh({});
			}
		});
	}
	
	//加载修改按钮
	var opHref = function(div, data){
		var btn = '<button type="button" class="btn green-haze btn-sm modify" name="' + data[6] + '">' + 
		  '<i class="viconfont vicon-ge_modify"></i>'+ LANG.UI_JOB_MODIFY +'</button>';
		$(div).html(btn);
	}
	
	//添加监听事件
	var addListeners = function(){
		
	}
	
	//加载关联用户
	var initUserTable = function(p){
		var dataTableOpt = {
    	}
    	
		if(!initGridFlag){
			usergrid = new Datatable();
			var data = {m:CONF.M.USER,f:'getResourceGroupUser',p:p};
			usergrid.setAjaxParam(data);
			usergrid.init({src: $("#user_table"), dataTable:dataTableOpt});
		}else{
			var params = {start:0, length:10, resourcegroupuuid: p.resourcegroupuuid};
			usergrid.getRefresh(params);
		}
	}
	
	//加载关联用户组
	var initUsergroupTable = function(p){
		var dataTableOpt = {
    	}
    
		if(!initGridFlag){
			userGroupgrid = new Datatable();
			var data = {m:CONF.M.USER,f:'getResourceGroupUserGroup',p:p};
			userGroupgrid.setAjaxParam(data);
			userGroupgrid.init({src: $("#usergroup_table"), dataTable:dataTableOpt});
		}else{
			var params = {start:0, length:10, resourcegroupuuid: p.resourcegroupuuid};
			userGroupgrid.getRefresh(params);
		}
	}
	
	
	//初始资源组列表
	var handleRecords = function(){
		var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 5]
    			}],
    			"order": [
                    [3, "desc"]
                ],
    	};
    	grid = new Datatable();
		var data = {m:CONF.M.RESOURCE,f:'getResourceGroupLists',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#resource_table"), dataTable:dataTableOpt, onDataLoad: initRow});
    	
    	//新增资源组
    	$('#newBut').on('click', function(){
    		addResourceGroup(grid);
    	});
    	
    	
    	//删除资源组
    	$('#deleteBut').on('click', function(){
    		deleteResourceGroup(grid);
    	});
    	
    	//资源分配
		$('#allocation').on('click', function(){
			allocationResources();
		});
    	
	}
	
	//分配资源给资源组
	var allocationResources = function(){
		var select = grid.getSelectedRows();
		var data = grid.getDataTable().data();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_GROUP_ALLOCATION, LANG.UI_RESOURCE_GROUP_ALLOCATION_NO_SELECT);
		}
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_RESOURCE_GROUP_ALLOCATION, LANG.UI_RESOURCE_GROUP_ALLOCATION_SELECT_ONE);
		}
//		
		var resourcegroupname = "";
		if(data.length !=0){
			for(var i=0;i<data.length;i++){
				if(data[i][6] == select[0]){
					resourcegroupname = data[i][1];
				}
			}
		}
		
		var url = "./content/platform/resource/resources.php?resourcegroupuuid=" + select[0]
		if(resourcegroupname != ""){
			url += "&resourcegroupname=" + resourcegroupname; 
		}
//		var url = "./content/platform/resource/resources.php";
		//跳转到资源分配
		LOCATION(url);
	}
	
	//新增资源组
	var addResourceGroup = function(grid){
		LOCATION('./content/platform/resource/add_resource_group.php');
	}
	
	
	//删除资源组
	var deleteResourceGroup = function(grid){
		var select = grid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_RESOURCE_GROUP_DELETE, LANG.UI_RESOURCE_GROUP_DELETE_NO_SELECT);
		}
		
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_RESOURCE_GROUP_DELETE, LANG.UI_RESOURCE_GROUP_DELETE_SELECT_ONE);
		}
		
		bootbox.confirm({
			title: LANG.UI_RESOURCE_GROUP_DELETE,
			message: LANG.UI_RESOURCE_GROUP_DELETE_CONFIRM,
			callback: function(r){
				if(!r) return;
				var data = {};
                data.uuids = select;
                data = JSON.stringify(data);
        		Metronic.blockUI({target: '#resourceGroupContent',animate: true});
        		$.post(CONF.AJAXPATH, {m:CONF.M.RESOURCE,f:'deleteResourceGroup',p:data}, function(d){
        			Metronic.unblockUI('#resourceGroupContent');
        			if(OPREL(d)){
            			grid.getRefresh({});
            		}
            	});
			}
 		})
	}
	
	
	return{
		init: function(){
			handleRecords();
			addListeners();
		}
	}; 
}();
jQuery(document).ready(function() {    
	resourceGroup.init();
});