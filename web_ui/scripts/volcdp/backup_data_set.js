var BackupDataSet = function(){
	var grid, gridInitFlag = false,nodeTypetreeInitFlag = false,taggrid,tagGridInitFlag=false,eventgrid,eventGridInitFlag;
	var zTree,nodeParamList,timepointList, vollist; //节点树，节点搜索列表，时间点存放列表，卷列表
	var vol_uuid,agent_uuid, task_uuid,vol_name,_delObj,_delete_level,_node_uuid,_agentUuid,_agentVolName,_host_ip;
	var _UserPassword;
	var initErrorFlag = false;
	var _checkTagLable = 0;
	var delLevel = {
		'unknown':0,	//未知删除类型
		'singleBackupSet':1,	//删除选定卷的单个备份集
		'allVolBackupSet':2,	//删除整个卷备份集
		'agentBackupSet':3	//删除整个客户端备份集
	};
	
	/**
	 * 初始化备份集管理监听事件
	 */
	var initListener = function(){
		$('#allDelete').on('click', deleteSelectPoint);
		$('#searchAgent').on('click', searchAgent);
		$('#delCheckedEventInfo').unbind('click').click(deleteCheckedEventInfo);
		//筛选指定时间范围的卷级时间集
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'200px'});
		});
		$('#searchAllTagPoint').on('click',function(){
			$('#searchmodal').modal({'width':'800px', 'height':'200px'});
		})
		//高级搜索发送请求到服务端
		$('#serach_submit').unbind('click').click(searchBackupSet);
		$('#resetStartTime').on('click',function(){$('#startTime').val('');});
		$('#resetEndTime').on('click',function(){$('#endTime').val('');});
		tabChange();
		
	}
	
	/**
	 * 提交备份集搜索条件,筛选备份集:起始时间,备份集是否标星
	 */
	var searchBackupSet = function(){
		var startTime = $('#searchmodal #startTime').val(); //开始时间
		var endTime = $('#searchmodal #endTime').val(); //结束时间
		if (startTime.length > 0 && endTime.length > 0) {
			var start=new Date(startTime.replace("-", "/").replace("-", "/"));
			var end=new Date(endTime.replace("-", "/").replace("-", "/"));
			if (start  > end) {
				UIToastr.showInfo(LANG.UI_VOL_CDP_BACKUP_SET_SEARCH_DATA,LANG.UI_VOL_CDP_BACKUP_SET_TIME_SELECT);
				return;
			}
		}
		var p = {};
		p.startTime = startTime;
		p.endTime =endTime;
		p.starFlag = $('#searchmodal #starFlag').val();
		var nodeuuid = $('#nodeselect').val();
		if(_checkTagLable==0){ //备份集
			var params = {start:0, length:10, search: p, accurateFlag: true,vol_uuid:vol_uuid, agentuuid:agent_uuid, taskuuid:task_uuid,nodeuuid: nodeuuid,vol_name:vol_name,host_ip:_host_ip};
			var data = {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupSetGrid',p:params};
			grid.setAjaxParam(data);
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			grid.getRefresh(params, undefined, true);
		}else if(_checkTagLable==1){
			var params = {start:0, length:10, search: p, accurateFlag: true,agentuuid:agent_uuid,taskuuid:task_uuid};
			var data = {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupTagPointGrid',p:params};
			taggrid.setAjaxParam(data);
			taggrid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			taggrid.getRefresh(params, undefined, true);
		}
		$('#searchmodal').modal('hide');
	}
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> '+ LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
		
		if(p.starFlag != "0"){
			info += '<span id="starFlag" title="' + $('#starFlag').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + $('#starFlag').find("option:selected").text() + '</i><em>X</em></span>';
		}
		
		$('.searchContent').append(info);
		$('#searchDiv').show();
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
			var nodeuuid = $('#nodeselect').val();
			var params = {start:0, length:10, search: p, accurateFlag: true, vol_uuid:vol_uuid, agentuuid:agent_uuid,taskuuid:task_uuid, nodeuuid:nodeuuid, vol_name: vol_name,host_ip:_host_ip};
			var data = {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupSetGrid',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			var nodeuuid = $('#nodeselect').val();
			var params = {start:0, length:10, search: {}, accurateFlag: false, vol_uuid:vol_uuid, agentuuid:agent_uuid, taskuuid:task_uuid, nodeuuid: nodeuuid,vol_name: vol_name,host_ip:_host_ip};
			var data = {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupSetGrid',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();
		}
	}
	
	/**
	 * 批量删除所选备份数据集卷
	 */
	var deleteSelectPoint = function(){
		var node = zTree.getCheckedNodes(true);
		if(node.length == 0){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
			return;
		}
		vollist = [];
		var NodeLevel = 3;
		var delMsg = LANG.UI_VOL_CDP_BACKUP_SET_VOL_TIME_DATA_DELETE_MESSAGE;
		for(var i=0; i<node.length; i++){
			var data = {};
			let halfCheck = node[i].getCheckStatus().half;
			if(halfCheck){
				continue;
			}
			NodeLevel = node[i].level;

			data.agent_uuid = node[0].agent_uuid;
			data.vol_uuid = node[i].uuid;
			data.vol_backup_set_id = 0;
			data.task_uuid = node[0].task_uuid;

			if(NodeLevel==2){
				data.delete_level = delLevel.agentBackupSet;
				_delete_level = delLevel.agentBackupSet;
				delMsg = LANG.UI_VOL_CDP_BACKUP_SET_HOST_TIME_DATA_DELETE_MESSAGE;
				_delObj = node[0].name;
				vollist.push(data);
				break;
			}else{
				data.delete_level = delLevel.allVolBackupSet;
				_delete_level = delLevel.allVolBackupSet;
				_delObj = node[0].vol_name;
				vollist.push(data);
			}
			vollist.push(data);
		}
		_node_uuid = $('#nodeselect').val();
		//鉴于目前消息结构和判的备份集是否使用的局限性，不支持多个卷同时删除，可以删除整个客户端对应的备份数据
		if(_delete_level == delLevel.allVolBackupSet && vollist.length>1){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_SET_DATA_DELETE,LANG.UI_VOL_CDP_BACKUP_SET_DATA_DELETE_TIPS);
			return;
		}
		//如果未选中，提示用户未勾选节点
		bootbox.confirm({
            title: LANG.UI_VOL_CDP_BACKUP_SET_DATA_DELETE,
            message: delMsg ,
            callback: function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({ 
				    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES, 
				    inputType: 'password',
				    callback: function (result) {
				    	if(result == null) return;
				        if(hex_md5(result) == _UserPassword){
				        	submitSelectDelete();
				        	return true;
				        }else{
				        	$('.bootbox-input').css('border-color', "#a94442");
				        	if(!initErrorFlag){
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
					        	$('.bootbox-input').after(des);
				        		initErrorFlag = true;
				        	}
				        	return false;
				        }
				    }
				});
            }
        });
	}
	/**
	 * @function:删除选定卷的所有备份集
	 * @params:objcet{'delete_level':0,"vol_backup_set_id":1,"vol_uuid":'',"agent_uuid":"xx","node_uuid":""}
	 */
	var submitSelectDelete = function(){
		var node = zTree.getCheckedNodes(true);
		var agentUuid = node[0]['agent_uuid'];
		var params = JSON.stringify({
			backupsetList:vollist[0],
			delObj:_delObj,
			delete_level:_delete_level,
			node_uuid:_node_uuid,
			agent_uuid:agentUuid,
			task_uuid:node[0]['task_uuid'],
		});
		Metronic.blockUI({target: '.backupsetmanager',animate: true});
	    $.post(CONF.AJAXPATH,{m:CONF.M.VOLCDPBACKUPSET,f:'deleteSelectBackupSet',p:params},function(data){
	    	Metronic.unblockUI('.backupsetmanager');
	        if(OPREL(data)){
    			for(var i=0;i<node.length;i++){
    				var halfCheck = node[i].getCheckStatus();
    				if(!halfCheck.half){
    					initTree();
    				}else{
    					zTree.checkNode(node[i],!node[i].checked,false,false);
    				}
    			}
    			grid.getRefresh({});
    			var gridData = grid.getDataTable().data();
    			if(gridData.length<=1){
    				initTree();
    			}
    		}

	    });
	}
	/**
	 * 定位搜索已备份客户端
	 */
	var searchAgent = function(){
		var searchValue = $("#searchHost").val();
		if(searchValue==""){
			UIToastr.showInfo(LANG.UI_VOL_CDP_BACKUP_SET_SEARCH_CLIENT,LANG.UI_VOL_CDP_BACKUP_SET_INPUT_MESSAGE);
		}
		var data = {}
		data.node = $('#nodeselect').val();
		data.search_info = searchValue;
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#cdp_backup_set_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupSetTree',p:data}, setTree);
	}
	
	//初始化时间点展示方式和事件  
	var initPointShowType = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getTimepointAllNode',p:JSON.stringify({moduleType : CONF.MODULE_TYPE.VOL_CDP})}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('#nodeselect');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
    	});
		//绑定事件
		$('#nodeselect').on('change', nodeselectChange);
	}
	//节点选择改变事件
	var nodeselectChange = function(){
		nodeTypetreeInitFlag = false;
		initTree();
	}
	
	var setTree = function(zNodes){
		$("#cdp_backup_set_tree").show();
		$("#nopointtips").hide();
		Metronic.unblockUI('#cdp_backup_set_tree');
		if(zNodes == "[]"){
			$("#cdp_backup_set_tree").hide();
			$("#nopointtips").show();
			return;
		}
		function showTitleForTree(treeId, treeNode) {
			return treeNode.type != 1;
		};
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false,
				},
				data: {
					simpleData: {
						enable: true
					},
					key: {
						title: "title"
					}
				},
				callback: {
					beforeClick: nodeSelect,
					onCheck: zTreeOnCheck,//勾选事件回调函数

				},
				view: {
					showTitle: showTitleForTree,
					nameIsHTML:true
				}
			};
		zTree = $.fn.zTree.init($("#cdp_backup_set_tree"), setting, JSON.parse(zNodes));
		nodeTypetreeInitFlag = true;
	};
	//单击展开和收起节点
	var nodeSelect = function(treeId,treeNode,clickFlag){
		var treeObj = $.fn.zTree.getZTreeObj(treeId);  
		treeObj.expandNode(treeNode, !treeNode.open, false, true, true);  
		nodeCheck('',treeId,treeNode);
	}
	//节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getVolBackupTimeInfo(treeId, treeNode, false, false);
		}else{
			return true;
		}
	}

	/**
	 * 排查同级节点的多选
	 * @param {*} event 
	 * @param {*} treeId 
	 * @param {*} treeNode 
	 */
	var _hisParentNodeId = "";  // 历史选中节点父节点ID
	var zTreeOnCheck = function(event, treeId, treeNode) { 
		var treeObj = $.fn.zTree.getZTreeObj(treeId);
		var parentNode = treeNode.getParentNode();  
		var parentNodeId = parentNode.id;
        if(treeNode.checked ){    //注意，这里的树节点的checked状态表示勾选之后的状态
			if(treeNode.isParent){
				treeObj.checkAllNodes(false);  //取消所有节点的选中状态
			}else if(_hisParentNodeId!=parentNodeId){
					treeObj.checkAllNodes(false);  //取消所有节点的选中状态
			}
			treeObj.checkNode(treeNode,true,true,false);  //重新选中被勾选的节点
		}
		_hisParentNodeId = parentNodeId;
	};
	
	/**
	 * 选中客户端对应的卷,获取卷对应的备机时间集
	 */
	var nodeCheck = function(e, id, node){
		_host_ip = "";
		// var tree = $.fn.zTree.getZTreeObj(id);
		// if(node.checked){    //注意，这里的树节点的checked状态表示勾选之后的状态
		// 	tree.checkAllNodes(false);//取消所有节点的选中状态
		// 	tree.checkNode(node,true,false,false);//重新选中被勾选的节点
		// }
		vol_uuid = node.uuid;
		vol_name = node.vol_name;
		var agentUUID = node.agent_uuid;
		var volName = node.vol_name;
		var level = node.level;
		_agentUuid = agentUUID;
		_agentVolName = volName;
		_node_uuid = $('#nodeselect').val();
		_host_ip = node.host_ip;
		var taskuuid = node.task_uuid;
		task_uuid = taskuuid;
		checkBackupClientVol(vol_uuid, agentUUID,volName,_host_ip,taskuuid); //录入备份数据列表
	}
	
	//初始化客户端树
	var initTree = function() {	
		var data = {}
		data.node = $('#nodeselect').val();
		data.search_info = "";
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#cdp_backup_set_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupSetTree',p:data}, setTree);
	}
	
	//添加星标
	var addStar = function(){
		starHandler(this, 'addStar');
	}
	//取消星标
	var deleteStar = function(){
		starHandler(this, 'deleteStar');
	}
	
	//标星统一处理
	var starHandler = function(button, funName){
		if(_checkTagLable==0){ //backup set
			var data = grid.getDataTable().data();
			var row = $(button).parents('tr').get(0)._DT_RowIndex;
			var params = data[row][10];
			params = JSON.stringify(params);
			Metronic.blockUI({target: '#datatable',animate: true});
			//发送到数据管理统一处理
	    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:funName,p:params}, function(data){
	    		Metronic.unblockUI('#datatable');
	    		if(OPREL(data)){
	    			grid.getRefresh({});
	    		}
	    	});
		}else{
			var data = taggrid.getDataTable().data();
			var row = $(button).parents('tr').get(0)._DT_RowIndex;
			var params = data[row][6];
			params = JSON.stringify(params);
			Metronic.blockUI({target: '#tagPointDatatable',animate: true});
			//发送到数据管理统一处理
	    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:funName,p:params}, function(data){
	    		Metronic.unblockUI('#tagPointDatatable');
	    		if(OPREL(data)){
	    			taggrid.getRefresh({});
	    		}
	    	});
		}
		
		
	}
	
	var starButton = function(div, data){
		if(_checkTagLable==0){ //backup set
			var hypervisor = parseInt(data[9].hypervisor);
			var htmlStr = '';
			if("2" == data[10]){
				htmlStr = '<a title="' + LANG.UI_DATA_MARK_STAR + '"><i class="fa fa-star-o star"></i></a>';
			}else if(1 == data[10]){
				htmlStr = '<a title="' + LANG.UI_DATA_UNMARK_STAR + '"><i class="fa fa-star star"></i></a>';
			}
		}else{
			var hypervisor = parseInt(data[6].hypervisor);
			var htmlStr = '';
			if("2" == data[6]){
				htmlStr = '<a title="' + LANG.UI_DATA_MARK_STAR + '"><i class="fa fa-star-o star"></i></a>';
			}else if(1 == data[6]){
				htmlStr = '<a title="' + LANG.UI_DATA_UNMARK_STAR + '"><i class="fa fa-star star"></i></a>';
			}
		}
		
		$(div).html(htmlStr);
		$('.fa-star-o').unbind().on('click', addStar);
		$('.fa-star').unbind().on('click', deleteStar);
	}
	
	//新增操作按钮  
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		var opDiv = $('tbody > tr').find('td:eq(9)');
		var starDiv = $('tbody > tr').find('td:eq(10)');
		for(var i=0; i<data.length; i++){
			opButton(opDiv[i], data[i][9], i);
			starButton(starDiv[i], data[i]);
		}
	}
	//添加操作按钮
	var opButton = function(div, opCode, rowNum){
		var button = '<div class="btn-group positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu">';
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="remark"><a href="javascript:;" ><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_PUBLIC_REMARK + '</a></li>';
					break;
				case 2:
					button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_PUBLIC_DELETE + '</a></li>';
					break;
			}
		});
		button += '</ul></div>';
		$(div).html(button);
		addOpButtonListener();
	}
	//添加按钮事件
	var addOpButtonListener = function(){
		$('.remark').unbind().on('click', remarkPoint);
		$('.delete').unbind().on('click', deletePoint);
	}
	
	//新增操作按钮  
	var tagOpButton = function(){
		var data = taggrid.getDataTable().data();
		var opDiv = $('#tagPointDatatable').find('tbody > tr').find('td:eq(5)'); //获取指定表格tbody 对应的行,列
		var starDiv = $('#tagPointDatatable').find('tbody > tr').find('td:eq(6)');
		
		for(var i=0; i<data.length; i++){
			opTagButton(opDiv[i], data[i][5], i);
			starButton(starDiv[i], data[i]);
		}
	}
	//添加标签点操作按钮
	var opTagButton = function(tagdiv, opCode, rowNum){
		var button = '<div class="btn-group positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu">';
		
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="tagremark"><a href="javascript:;" ><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_PUBLIC_REMARK + '</a></li>';
					break;
				case 2:
					button += '<li class="tagdelete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_PUBLIC_DELETE + '</a></li>';
					break;
			}
		});
		button += '</ul></div>';
		$(tagdiv).html(button);
		addTagOpButton();
	}
	//添加标签按钮事件
	var addTagOpButton = function(){
		$('.tagremark').unbind().on('click', remarkPoint);
		$('.tagdelete').unbind().on('click', deleteTagPoint);
	}
	//添加备注
	var remarkPoint = function(){
		if(_checkTagLable==0){ //backup set
			var data = grid.getDataTable().data();
			var row = $(this).parents('tr').get(0)._DT_RowIndex;
			var params = data[row][10];
			var value = data[row][8];
		}else{
			var data = taggrid.getDataTable().data();
			var row = $(this).parents('tr').get(0)._DT_RowIndex;
			var lableId = data[row][6]['lable_id'];
			var backupAgentId = data[row][7];
			var value = data[row][4];
		}
		bootbox.prompt({
            title: LANG.UI_DATA_ADD_REMARK,
            value:value,
            callback: function(result) {
            	if(result == null || $.trim(result) == value) return;
            	if(_checkTagLable==0){
            		submitRemark($.trim(result), params);
            	}else{
            		submitTagPointRemark($.trim(result), lableId,backupAgentId);
            	}
        		
            }
        });
	}
	//提交修改标签点备注
	var submitTagPointRemark = function(remark,lableId,backupAgentId){
		var byteLength = remark.replace(/[^\u0000-\u00ff]/g,"aa").length;
    	if(byteLength>256){
    		UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABELNODE, LANG.UI_VOL_CDP_JOB_DETAILS_LABELNODE_MESSAGE);
			return false;
    	}
		var info = {};
		info.lable_id = lableId;
		info.backup_agent_id = backupAgentId;
		info.remark = remark;
		var params = JSON.stringify(info);
		Metronic.blockUI({target: '#tagPointDatatable',animate: true});
		//发送到数据管理统一处理
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'remarkTagPoint',p:params}, function(data){
    		Metronic.unblockUI('#tagPointDatatable');
    		if(OPREL(data)){
    			taggrid.getRefresh({});
    		}
    	});
	}
	// 提交修改备份集备注
	var submitRemark = function(remark, params){
		var byteLength = remark.replace(/[^\u0000-\u00ff]/g,"aa").length;
    	if(byteLength>256){
    		UIToastr.showWarning(LANG.UI_DATA_ADD_REMARK, LANG.UI_VOL_CDP_BACKUP_DATA_REMARK_LENGTH_MESSAGE);
			return false;
    	}
		params.remark = remark;
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#datatable',animate: true});
		//发送到数据管理统一处理
    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:'remarkTimepoint',p:params}, function(data){
    		Metronic.unblockUI('#datatable');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
	}
	
	//删除选中的事件信息
	var deleteCheckedEventInfo = function(){
		var selectNodes = eventgrid.getSelectedRows();
		var eventData = eventgrid.getDataTable().data();//获取配置的恢复卷信息
		var checkedEventInfo = [];
		if(selectNodes.length>0){
			for(var j=0;j<selectNodes.length;j++){
				for(var i=0;i<eventData.length;i++){
					var timepoint = eventData[i][5];
					if(timepoint==selectNodes[j]){
						var checkedEventId = eventData[i][6];
						checkedEventInfo.push(checkedEventId);
					}
				}
			}
			var params = JSON.stringify({checkList:checkedEventInfo,node_uuid:_node_uuid});
			Metronic.blockUI({target: '#eventPointDatatable',animate: true});
			//发送到对应模块
	    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'deleteSelectEventInfo',p:params}, function(data){
	    		Metronic.unblockUI('#eventPointDatatable');
	    		if(OPREL(data)){
	    			eventgrid.getRefresh({});
	    			var d = JSON.parse(data);
	    		}
	    	});
		}else{
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_SET_INFO_DELETE, LANG.UI_VOL_CDP_BACKUP_SET_DELETE_INFO_SELECT_MESSAGE);
			return false;
		}
	}
	//删除标签点
	var deleteTagPoint = function(){
		var button = this;
		bootbox.confirm({
			title: LANG.UI_VOL_CDP_BACKUP_SET_LABEL_POINT_DELETE,
            message: LANG.UI_VOL_CDP_BACKUP_SET_LABEL_POINT_DELETE_MESSAGE,
            callback: function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({ 
				    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES, 
				    inputType: 'password',
				    callback: function (result) {
				    	if(result == null) return;
				        if(hex_md5(result) == _UserPassword){
				        	_delete_level  = delLevel.singleBackupSet;
				        	submitTagDelete(button);
				        	return true;
				        }else{
				        	$('.bootbox-input').css('border-color', "#a94442");
				        	if(!initErrorFlag){
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
					        	$('.bootbox-input').after(des);
				        		initErrorFlag = true;
				        	}
				        	return false;
				        }
				    }
                });
            }
		});
	}
	//提交删除标签点请求
	var submitTagDelete = function(button){
		var data = taggrid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var lebelList = {};
		lebelList.backup_agent_id = data[row][7];
		lebelList.label_timestamp = data[row][8];
		var params = JSON.stringify({lebelList:lebelList,node_uuid:_node_uuid});
		Metronic.blockUI({target: '#tagPointDatatable',animate: true});
		//发送到对应模块
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'deleteSelectLablePoint',p:params}, function(data){
    		Metronic.unblockUI('#tagPointDatatable');
    		if(OPREL(data)){
    			taggrid.getRefresh({});
    			var d = JSON.parse(data);
    		}
    	});
	}
	
	//删除备份集
	var deletePoint = function(){
		var button = this;
		bootbox.confirm({
            title: LANG.UI_VOL_CDP_BACKUP_SET_DATA_DELETE,
            message: LANG.UI_VOL_CDP_BACKUP_SET_DATA_DELETE_MESSAGE,
            callback: function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({ 
				    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES, 
				    inputType: 'password',
				    callback: function (result) {
				    	if(result == null) return;
				        if(hex_md5(result) == _UserPassword){
				        	_delete_level  = delLevel.singleBackupSet;
				        	submitDelete(button);
				        	return true;
				        }else{
				        	$('.bootbox-input').css('border-color', "#a94442");
				        	if(!initErrorFlag){
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
					        	$('.bootbox-input').after(des);
				        		initErrorFlag = true;
				        	}
				        	return false;
				        }
				    }
				});
            }
        });
	}
	//提交删除备份集请求
	var submitDelete = function(button){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var checkRow = data[row][10];
		_delObj = checkRow['vol_name']+"("+checkRow['time_point']+")";
		var node_uuid = checkRow.node_uuid;
		var task_uuid = checkRow.task_uuid;  //所在备份集对应的任务
		var backupsetList = {};
		backupsetList.vol_uuid = checkRow['vol_uuid'];
		backupsetList.agent_uuid = checkRow['agentuuid'];
		backupsetList.vol_backup_set_id = checkRow['vol_id'];
		backupsetList.task_delete = checkRow['task_delete']; //对应任务是否删除
		backupsetList.task_uuid = task_uuid;
		backupsetList.delete_level = _delete_level;
		var params = JSON.stringify({
			backupsetList:backupsetList,
			delObj:_delObj,
			delete_level:_delete_level,
			node_uuid:node_uuid
		});
		Metronic.blockUI({target: '#datatable',animate: true});
		//发送到对应模块
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'deleteSelectBackupSet',p:params}, function(data){
    		Metronic.unblockUI('#datatable');
    		deleteResult(data);
    	});
	}
	/**
	 * 删除后執行，判斷選中节点是否还有备份集，无择更新树形菜单；
	 */
	
	var deleteResult = function(data){
		if(OPREL(data)){ 
			grid.getRefresh({});
			var d = JSON.parse(data);
			var gridData = grid.getDataTable().data();
			if(gridData.length<=1){
				initTree();
			}
		}
	}
	
	var tabChange = function (){
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var tab = e.target;
            if(tab.hash == "#tab_backup_lable_point"){
            	_checkTagLable = 1;
            	backupTagPoint(_agentUuid);
            }else if(tab.hash == "#tab_backup_event_info"){
            	_checkTagLable = 2;
            	backupEventInfo(vol_uuid,_agentUuid);
            }else{
            	_checkTagLable = 0;
            	backupSetRecords(vol_uuid, _agentUuid,_agentVolName, '',task_uuid); //录入备份数据列表
            }
            
        });
		
	}
	/**
	 * @function:选择客户端及对应的卷
	 */
	var checkBackupClientVol = function (volUUID, agentUUID,volName,host_ip,taskuuid){
		if(_checkTagLable==1){  //tag point 
			backupTagPoint(agentUUID);
		}else if(_checkTagLable==2){
			backupEventInfo(volUUID,agentUUID,volName,host_ip);
		}else{
			backupSetRecords(volUUID, agentUUID,volName,host_ip,taskuuid);  //backup set
		}
	}
	/**
	 * @function: 获取当前选中客户端及卷生成的事件信息
	 */
	var backupEventInfo = function(volUUID, agentUUID,volName){
		var nodeuuid = $('#nodeselect').val();
		if(!eventGridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0,2,3,4]
	    			}],
	    			"order": [
	                    [1, "desc"]
	                ],
	    	};
			eventgrid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPBACKUPSET,f:'getAgentEventInfo',p:{host_uuid:agentUUID,vol_uuid:"",time_list:[],nodeuuid:nodeuuid,taskuuid:task_uuid}};
			eventgrid.setAjaxParam(dataPar);
			eventgrid.init({src: $("#eventPointDatatable"), dataTable:dataTableOpt});
			eventGridInitFlag = true;
		}else{
			eventgrid.getRefresh({host_uuid:agentUUID,vol_uuid:"",time_list:[],nodeuuid:nodeuuid,taskuuid:task_uuid}, undefined, true);
		}
	}
	/**
	 *@function:获取当前选择卷对应客户端生成的标签点信息
	 *@params:agent_uuid:客户端uuid
	 */
	var backupTagPoint = function (agentUUID){
		var nodeuuid = $('#nodeselect').val();
		if(!tagGridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0,2,3,4,5]
	    			}],
	    			"order": [
	                    [1, "desc"]
	                ],
	    	};
			taggrid = new Datatable();
			var data = {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupTagPointGrid',p:{agentuuid:agentUUID, nodeuuid: nodeuuid,taskuuid:task_uuid}};
			taggrid.setAjaxParam(data);
			taggrid.init({src: $("#tagPointDatatable"), onDataLoad:tagOpButton, dataTable:dataTableOpt});
			tagGridInitFlag = true;
		}else{
			taggrid.getRefresh({agentuuid:agentUUID,nodeuuid: nodeuuid,taskuuid:task_uuid}, undefined, true);
		}
	}
	
	/**
	 * @function:获取选择卷对应的备份集信息
	 * @param:volUUID:选择卷uuid,agentUUID:卷对应客户端UUID
	 */
    var backupSetRecords = function (volUUID, agentUUID,volName,host_ip,taskuuid) {
    	var nodeuuid = $('#nodeselect').val();
    	agent_uuid = agentUUID;
		task_uuid = taskuuid;
		if(!gridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [2,3,4,5,6,7,8,9,10]
	    			}],
	    			"order": [
	                    [1, "desc"]
	                ],
	    	};
			grid = new Datatable();
			vol_uuid = volUUID;
			vol_name = volName;
			if(!vol_uuid){
				return;
			}
			var data = {m:CONF.M.VOLCDPBACKUPSET,f:'getBackupSetGrid',p:{vol_uuid:vol_uuid, agentuuid:agent_uuid, nodeuuid: nodeuuid,vol_name:vol_name,host_ip:_host_ip,taskuuid:taskuuid}};
			grid.setAjaxParam(data);
			grid.init({src: $("#datatable"), onDataLoad:addOpButton, dataTable:dataTableOpt});
	    	gridInitFlag = true;
	    	$('#tabletips').hide();
			$('#backupSetTable').show();
			$('#marktips').show();
		}else{
			if(!vol_uuid){
				return;
			}
			//刷新表格
			grid.getRefresh({vol_uuid:vol_uuid, agentuuid:agent_uuid,nodeuuid: nodeuuid,vol_name:vol_name,host_ip:_host_ip,taskuuid:taskuuid}, undefined, true);
		}
    	return;
    }
    
  //初始化当前用户密码用于删除二次确认
	var initUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
	
	//初始化日期选择插件
	var inintDatatimePicker = function(){
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			//英文独有的
			$(".form_datetime").datetimepicker({
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-mm-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			});
		}else{
			$(".form_datetime").datetimepicker({
				language:  'zh-CN', 
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-MM-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			});
		}
	}
   
	return {
        //main function to initiate the module
        init: function () {
        	inintDatatimePicker();    //初始化时间
        	initPointShowType();
        	initTree();        	
        	initListener();
        	initUserPassword();
        }

    };

}();

jQuery(document).ready(function() {    
    BackupDataSet.init();
});