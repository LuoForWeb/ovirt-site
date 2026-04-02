var NewVmdataExport = function () {
	var pointtypetree, pointtypetreeInitFlag = false, vmtypetree, vmTypetreeInitFlag = false;
	var currentTree; //当前展示的树
	
	var submitCheck = function(){
		//检测是否选择备份时间点
		var timepointNode = currentTree.getCheckedNodes(true);
		if(0 == timepointNode.length){
			return UIToastr.showWarning(LANG.UI_INSTANT_SELECT_TIMEPOINT, LANG.UI_BACKUP_EXPORT_TIMEPOINT_TREE_TIPS);
		}
		
		var data = {};
		data.nodeuuid = $('#nodeselect').val();
		data.storageuuid = $('#storageselect').val();
		data.hypervisor = timepointNode[0].hypervisor;
		
		
		var timepointuuids = [];
		for(var i=0; i<timepointNode.length; i++){
			timepointuuids[i] = timepointNode[i].timepointuuid;
		}
		data.timepointuuids = timepointuuids;
		//任务名
		data.taskName = $('input[name=taskname]').val();
		//检查目标存储
		if(null == data.storageuuid){
			return UIToastr.showWarning(LANG.UI_BACKUP_EXPORT_SELECT_STORAGE, LANG.UI_BACKUP_EXPORT_SELECT_STORAGE_TIPS);
		}
		//检查任务名
		if('' == $.trim(data.taskName)){
			return UIToastr.showWarning(LANG.UI_BACKUP_EXPORT_ENTER_TASKNAME, LANG.UI_BACKUP_EXPORT_ENTER_TASKNAME_TIPS);
		}
		data = JSON.stringify(data);
		Metronic.blockUI({target: '#exportcontent',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'createDataExportJob',p:data}, function(d){
			Metronic.unblockUI('#exportcontent');
    		if(OPREL(d)){
    	    	LOCATION('./content/vm/vmdata_export.php', 'vmdata');
        	}
		});
	}
	
	//初始化时间点展示方式和事件
	var initPointShowType = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getTimepointAllNode',p:JSON.stringify({truenode:true, moduleType : CONF.MODULE_TYPE.VM})}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('#nodeselect');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
			//初始化BS select控件
			$('#nodeselect').selectpicker({
	            iconBase: 'fa',
	            tickIcon: 'fa-check'
	        });
			
			initDesStorage($('#nodeselect').val());
			initPointTree();
    	});
		
		$('#pointshowtype').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
		//绑定事件
		$('#pointshowtype').on('change', pointShowTypeChange);
		$('#nodeselect').on('change', nodeselectChange);
	}
	
	//初始化目的存储
	var initDesStorage = function(nodeuuid){
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:JSON.stringify({nodeuuid:nodeuuid})}, function(d){
			var data = JSON.parse(d);
			var storageselect = $('#storageselect');
			storageselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				storageselect.append(option);
			}
    	});
	}
	
	//时间点展示方式改变事件
	var pointShowTypeChange = function(){
		if(1 == this.value){
			$('#pointtypetree').show();
			$('#vmtypetree').hide();
			currentTree = pointtypetree;
			if(!pointtypetreeInitFlag){
				//未初始化第一棵树的时候需要初始化
				initPointTree();
			}
		}else if(2 == this.value){
			$('#pointtypetree').hide();
			$('#vmtypetree').show();
			if(!vmTypetreeInitFlag){
				//未初始化第二棵树的时候需要初始化
				initPointTree();
			}
			if(vmtypetree){
				currentTree = vmtypetree;
			}
		}
	}
	
	//节点选择改变事件
	var nodeselectChange = function(){
		pointtypetreeInitFlag = false;
		vmTypetreeInitFlag = false;
		initPointTree();
		initDesStorage($('#nodeselect').val());
	}
	
	//事件监听
	var initListener = function(){
		$('#submitbtn').on('click', function(){
			submitCheck();
		});
		//取消按钮事件
		$('#cancelbtn').on('click',function(){
	    	LOCATION('./content/vm/vmdata_export.php', 'vmdata');
		});
	}
	
	//初始化任务名
	var initTaskName = function(hypervisor){
		//设置任务名
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getDataExportTaskName',p:{}}, function(d){
			$('input[name=taskname]').val(d);
		});
	}
	
	
	//初始化备份时间点树
	var initPointTree = function() {
		var data = {};
		data.showtype = $('#pointshowtype').val();
		data.node = $('#nodeselect').val();
		data.exportflag = true;		//备份数据导出标志,用于后台识别,来过滤不支持的虚拟化
		data.instantflag = true;	//按时间点显示不需要时间点复选框
		var setFunction = setPointTree;
		if(2 == data.showtype){
			setFunction = setPointTreetype2;
		}
		data = JSON.stringify(data);
		Metronic.blockUI({target: '.two_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getTimepoint',p:data}, setFunction);
	};
	
	var checkTreeNodeInfo = function(zNodes){
		if(zNodes == "[]"){
			$("#nopointtips").show();
			$('#vmtypetree').hide();
			$('#pointtypetree').hide();
			$('#pointshowtype').prop('disabled', true);
			$('#pointshowtype').selectpicker('refresh');
			return false;
		}else{
			$("#nopointtips").hide();
			if($('#pointshowtype').val() == 1){
				$('#pointtypetree').show();
				$('#vmtypetree').hide();
			}else{
				$('#pointtypetree').hide();
				$('#vmtypetree').show();
			}
			$("#two_tree").show();
			$('#pointshowtype').prop('disabled', false);
			$('#pointshowtype').selectpicker('refresh');
			return true;
		}
	}
	
	var setPointTree = function(zNodes){
		Metronic.unblockUI('.two_tree');
		if(!checkTreeNodeInfo(zNodes)) return;
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false,
					chkboxType:  { "Y": "", "N": "" }
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
					onCheck: timepointOnCheck,
				},
				view: {
					showTitle: true
				}
			};
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, JSON.parse(zNodes));
		currentTree = pointtypetree;
		pointtypetreeInitFlag = true;
	};
	
	//设置第二棵树
	var setPointTreetype2 = function(zNodes){
		Metronic.unblockUI('.two_tree');
		if(!checkTreeNodeInfo(zNodes)) return;
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
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
					beforeClick: nodeSelectType2,
					onCheck: timepointOnCheckType2,
				},
				view: {
					showTitle: true
				}
			};
		vmtypetree = $.fn.zTree.init($("#vmtypetree"), setting, JSON.parse(zNodes));
		vmTypetreeInitFlag = true;
		currentTree = vmtypetree;
	}
	
	//选择第二课树
	var nodeSelectType2 = function(treeId, treeNode, clickFlag){
		vmtypetree.checkNode(treeNode, !treeNode.checked, true, true);
		vmtypetree.expandNode(treeNode, true)
	}
	
	//时间点分组
	var timepointOnCheckType2 = function(e, id, node){
	}
	
	//选择时间点节点事件绑定
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(4 == treeNode.type){
			pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
		}else if(3 == treeNode.type){
			pointtypetree.expandNode(treeNode, true)
		}else{
			pointtypetree.expandNode(treeNode, true)
		}
	}
	//时间点选中事件绑定
	var timepointOnCheck = function(e, id, node){
		var flag = node.checked;
		var allNodes = pointtypetree.getCheckedNodes(true);
		if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, false)) return; 
		
//		for(var i = 0; i < allNodes.length; i++){
//			if(allNodes[i].vmuuid == node.vmuuid && allNodes[i].vcenteruuid == node.vcenteruuid){
//				//如果虚拟机一样的话,就要取消之前所有的
//				pointtypetree.checkNode(allNodes[i], false, false, false);		
//			}
//		}
		pointtypetree.checkNode(node, flag, false, false);
	}
	
	//判断是否在一个备份节点上
	var checkSelectInOneNode = function(flag, tree, node, allNodes, checkTypeFlag){
		if(!flag) return true;
		for(var i = 0; i< allNodes.length; i++){
			if(allNodes[i].nodeuuid != node.nodeuuid){
				UIToastr.showInfo(LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE, LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE_TIPS);
				for(var i = 0; i < allNodes.length; i++){
					tree.checkNode(allNodes[i], false, false, false);		
				}
				tree.checkNode(node, flag, checkTypeFlag, false);
				return false;
			}
		}
		return true;
	}
    return {
        //main function to initiate the module
        init: function () {
        	initPointShowType();
//        	initPointTree();
        	initListener();
        	initTaskName();
        },
    };
}();

jQuery(document).ready(function() {   
	NewVmdataExport.init();
});