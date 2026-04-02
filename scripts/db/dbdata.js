var DbData = function () {
	
	var zTree, grid, initPointFlag = false, nodeParamList, _selectdb;
	
	var initListener = function(){
		$('#scantimepoint').on('click', scanTimepoint);
		$('#searchdb').on('propertychange', searchDB).on('input', searchDB);
		$('#timeperiod').on('change', timeperiodselectChange);
	}
	
	//搜索数据库
	var searchDB = function(){
		var value = $('#searchdb').val();
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
	
	//初始化数据库树
	var initTree = function() {
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getCDPDBTree',p: {}}, setTree);
	};
	
	//设置数据库树
	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$('#cdpdbtree').hide();
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
		zTree = $.fn.zTree.init($("#cdpdbtree"), setting, JSON.parse(zNodes));
	};
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(5 == treeNode.type){
			
			_selectdb = treeNode;
			
			$('#standbyhostName').html(treeNode.standbyHostName);
			$('#producthostName').html(treeNode.productHostName);
			
			$('#backupsize').html(treeNode.backupsize);
			$('#hissize').html(treeNode.hissize);
			$('#takeoversize').html(treeNode.takeoversize);
			
			$('#timeinput').html(treeNode.endTime);
			
			var timeperiod = $('#timeperiod');
			timeperiod.empty();
			var timeperiodArr = treeNode.timeperiod;
			for(var i=0; i<timeperiodArr.length; i++){
				var option = $("<option>").text(timeperiodArr[i].des).val(timeperiodArr[i].histime);
				timeperiod.append(option);
			}
			
			timeperiodselectChange();
			$('#tabletips').hide();
			$('#databaseinfodiv').show();
			
		}
		
		//异步加载数据库信息
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
	}
	
	var timeperiodselectChange = function(){
		var timeperiodArr = _selectdb.timeperiod;
		var timeperiod = $('#timeperiod').val();
		for(var i=0; i<timeperiodArr.length; i++){
			if(timeperiod == timeperiodArr[i].histime){
				var atime = {
						inputTime:timeperiodArr[i].endtime,
						startTime:timeperiodArr[i].starttime,
						endTime:timeperiodArr[i].endtime,
					}
				initDatetimePicker(atime);
				$('#cdptimepointdiv').hide();
			}
		}
	}
	
	//节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			getSyncDatabaseInfo(treeId, treeNode);
		}else{
			return true;
		}
		
	}
	
	//异步加载树
	var getSyncDatabaseInfo = function(treeId, treeNode){
		var data = {}
		data.id = treeNode.id;
		data.ip = treeNode.ip;
		data.name = treeNode.name;
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#cdpdbtree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getDatabaseSyncTree',p:data}, function(d){
			Metronic.unblockUI('#cdpdbtree');
        	var result = JSON.parse(d);
        	if(result.re){
        		//success
        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
        	}else{
        		OPREL(d);
        	}
		});
	}
	
	//初始化日期控件,按开始和结束时间初始
	var initDatetimePicker = function(atime){
		$('input[name=timeinput]').val(atime.inputTime);
		$(".form_datetime").datetimepicker({
			language:  'zh-CN', 
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
            forceParse:true,
            startDate: atime.startTime,
            endDate: atime.endTime,
        });
		$('.form_datetime').datetimepicker('setStartDate', atime.startTime);
		$('.form_datetime').datetimepicker('setEndDate', atime.endTime);
	}
	
	//扫描时间点详情
	var scanTimepoint = function(){
		event.preventDefault();
		var timepoint = $('input[name=timeinput]').val();
		var data={};
		data.timepoint = timepoint;
		data.hostip = _selectdb.productHostIP;
		data.dbtype = _selectdb.dbtype;
		data.instance = _selectdb.instance;
		data.db = _selectdb.name;
		data.timeperiod = $('#timeperiod').val();
		data.hostuuid = _selectdb.standbyHostUUID;
		data.checkbox = false;
    	initResourcetable(data);
	}
	
	//初始化和更新时间点表格
	var initResourcetable = function(data){
		var data = {m:CONF.M.DBCDP,f:'scanBackupTimepoint',p:data};
		$('#cdptimepointdiv').show();
		if(!initPointFlag){
			$('#cdptimepointdiv').show();
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 1]
	    			}],
	    			"paging":false,
	    			"info":false,
	    			"sScrollY":222,
	    	};
	    	grid = new Datatable();
			grid.setAjaxParam(data);
	    	grid.init({src: $("#timepoint"), checkbox:false,  dataTable:dataTableOpt});
	    	initPointFlag = true;
	    	
		}else{
			grid.setAjaxParam(data);
			grid.getRefresh({});
			//更新后滚动到顶部
			$(".table-scrollable").animate({scrollTop:0},10);
		}
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
	DbData.init();
});