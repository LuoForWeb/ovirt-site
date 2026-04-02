var FileCDPData = function () {
	
	var zTree, _selectNode, _selectNodeParent;
	
	var initListener = function(){
		$('#downloadFile').on('click', downloadFile);
		$('#searchfs').on('propertychange', searchFS).on('input', searchFS);
	}
	
	//下载文件
	var downloadFile = function(){
		var url = CONF.AJAXPATH + '?m=' + CONF.M.FILECDP + '&f=downloadFile';
		url += "&size=" + _selectNode.size;
		url += "&path=" + _selectNode.dir;
		url += "&hostuuid=" + _selectNodeParent[0].id;
		window.location.href = encodeURI(url);
	}
	
	//搜索文件
	var searchFS = function(){
		var value = $('#searchfs').val();
		var allNode = zTree.transformToArray(zTree.getNodes());
		nodeParamList = zTree.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			zTree.hideNodes(allNode);
		}
		var nodeParamList1 = zTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(zTree,nodeParamList1[n]);
        }
        zTree.showNodes(nodeParamList);
	}
	
	//找到父节点
	var findParent = function(treeObj,node){
		 zTree.expandNode(node,true,false,false);
		 if(!node.children){
			 zTree.expandNode(node,false,false,false);
		 }
		 if(!node.isParent){
			nodeParamList.push(node);
		 }
		 var pNode = node.getParentNode();
		 if(pNode != null){
			 nodeParamList.push(pNode);
			 findParent(zTree,pNode);
		 }
	}
	
	//初始化树
	var initTree = function() {
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getCDPFSTree',p: {}}, setTree);
	};
	
	//设置数据库树
	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$('#cdpfstree').hide();
			$('#nopointtips').show();
			return;
		}
		var setting = {
				check: {
					enable: false,
					nocheckInherit: false
				},
				view: {
//					addDiyDom: addDiyDom
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
						pIdKey: "pid",
						rootPId: 0
					},
					key: {
						title: "title"
					}
				},
				callback: {
//					onCheck: pointOnCheck,
					beforeClick: nodeSelect,
					beforeExpand: nodeExpand
				}
			};
		zTree = $.fn.zTree.init($("#cdpfstree"), setting, JSON.parse(zNodes));
	};
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.more){
			// 显示更多
			var p = {hostuuid:treeNode.hostuuid, page:treeNode.page, limit:20, dir:treeNode.dir, pid:treeNode.pid};
			p = JSON.stringify(p);
			Metronic.blockUI({target: '#cdpfstree',animate: true});
			$.ajax({ 
				type: "post", 
		        url: CONF.AJAXPATH, 
		        async:true, 
		        data:{m:CONF.M.FILECDP,f:'getHostFileTree',p:p},
		        success: function(data){ 
		        	Metronic.unblockUI('#cdpfstree');
		        	result = JSON.parse(data);
		        	if(result.re){
		        		//success
		        		$.fn.zTree.getZTreeObj(treeId).removeNode(treeNode);
		        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode.getParentNode(), result.tree, true);
		        	}else{
		        		OPREL(data);
		        	}
		        } 
			});
		}
		if('file' == treeNode.type){
			
			_selectNode = treeNode;
			
			//得到节点的所有父节点,包括自己,按顺序,分别是从站,主站,任务名,备份/历史数据,目录
			var parentNodes = treeNode.getPath();
			_selectNodeParent = parentNodes;
			
			$('#standbyhostName').html(parentNodes[0].name);
			$('#producthostName').html(parentNodes[1].name);
			$('#taskName').html(parentNodes[2].name);
			
			$('#fileName').html(treeNode.name);
			$('#fileSize').html(treeNode.sizedes);
			$('#modifyTime').html(treeNode.chgtime);
			$('#fullPath').html(treeNode.dir);
			
			$('#tabletips').hide();
			$('#fileinfodiv').show();
			
			//如果是备份系统,隐藏全路径
			if(parentNodes[0].baksys){
				$('#fullPathDiv').hide();
			}else{
				$('#fullPathDiv').show();
			}
			
		}
		
		//异步加载文件信息
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
	}
	
	
	//节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(0 == treeNode.type){
			//展开根目录
			if(treeNode.children) return true;
			getSyncFileRootInfo(treeId, treeNode);
		}else if(3 == treeNode.type || "dir" == treeNode.type){
			//展开备份目录
			if(treeNode.children) return true;
			getSyncFileBackupDirInfo(treeId, treeNode);
		}else{
			return true;
		}
		
	}
	
	//异步加载树,根目录
	var getSyncFileRootInfo = function(treeId, treeNode){
		var data = {}
		data.hostuuid = treeNode.id;
		data.pid = treeNode.id;
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#cdpfstree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getRecoveryTaskTree',p:data}, function(d){
			Metronic.unblockUI('#cdpfstree');
        	var result = JSON.parse(d);
        	if(result.re){
        		//success
        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.tree, true);
        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
        	}else{
        		OPREL(d);
        	}
		});
	}
	
	//异步加载树,备份目录
	var getSyncFileBackupDirInfo = function(treeId, treeNode){
		var data = {hostuuid:treeNode.hostuuid, page:treeNode.page, limit:20, dir:treeNode.dir, pid:treeNode.id};
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#cdpfstree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getHostFileTree',p:data}, function(d){
			Metronic.unblockUI('#cdpfstree');
        	var result = JSON.parse(d);
        	if(result.re){
        		//success
        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.tree, true);
        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
        	}else{
        		OPREL(d);
        	}
		});
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	initTree();
        	initListener();
        	
        }

    };

}();


jQuery(document).ready(function() {    
	FileCDPData.init();
});