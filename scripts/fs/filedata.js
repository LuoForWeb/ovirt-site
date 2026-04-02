var FileData = function () {
	//时间点树
	var zTree,pointList,agentList,taskuuidList;
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	var agent_uuid, task_uuid;
	var _UserPassword;
	var initErrorFlag = false;
	var grid, gridInitFlag = false
	var gfsList = {};//主要用于判断是否修改了GFS,如果没修改则不提交后台
	var _Remark = ""; //GFS标记用备注
	var showNode;//搜索到的时间点
	var taskKeyWord = ''//暂时解决搜索修改前的任务名的情况
	//事件监听
	var initListener = function(){
		if (CONF.PERMISSION_ARR.includes('p_filedata_detele')) {
			$('#allDelete').show();
			$('#allDelete').on('click', deleteSelectPoint);
		}
		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'230px'});
		});
		//设置GFS标记
		$("#mark_submit").on('click',function(){
			markSubmit();
		});
		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			var p = {};
			p.startTime = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.timepointType = $('#searchmodal #timepointType').val();
			p.forever = boolToInt($("input[name=foreverCheck1]").get(0).checked);
			var storage_uuid = $('#storageSelect').val();
			
			var params = {start:0, length:10, search: p, accurateFlag: true, taskuuid:task_uuid, agentuuid:agent_uuid,storage_uuid: storage_uuid};
			var data = {m:CONF.M.FILE,f:'getFsTimepointGrid',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			grid.getRefresh(params, undefined, true);
		});
		//搜索模态框
		$('#filterSearch').click(function(){
			$('#filterSearch').popModal({
				html : $('#filter-content'),
				placement : 'bottomLeft',
				showCloseBut : true,
				onDocumentClickClose : true,
				onOkBut : searchFS,
				onCancelBut : function(){},
				onLoad : function(){},
				onClose : function(){},
				maxWidth: 300,
				maxHeight: 'auto',
			});
		});
		$('.icheck').iCheck({
    	    checkboxClass: 'icheckbox_square-blue',
    	    radioClass: 'iradio_square-blue',
      });
	}
	//布尔类型转成int1和2
	var boolToInt = function(thisbool){
		if(thisbool){
			return 1;
		}else{
			return 2;
		}
		
	}

	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');

		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> '+ LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}

		if(p.timepointType != "0"){
			info += '<span id="timepointType" title="' + $('#timepointType').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TYPE +': <i>' + $('#timepointType').find("option:selected").text() + '</i><em>X</em></span>';
		}

		if(p.forever != "2"){
			info += '<span id="forever" title="' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '</i><em>X</em></span>';
		} else {
			info += '<span id="impermanence" title="' + LANG.UI_FILE_NOT_PERMANENT_MARK + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_FILE_NOT_PERMANENT_MARK + '</i><em>X</em></span>';
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
				if (id === 'impermanence') { // 非永久标记点的清空
					delete p.forever 
				}
			}
			var storage_uuid = $('#storageSelect').val();
			var params = {start:0, length:10, search: p, accurateFlag: true, taskuuid:task_uuid, agentuuid:agent_uuid,storage_uuid: storage_uuid};
			var data = {m:CONF.M.FILE,f:'getFsTimepointGrid',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			var storage_uuid = $('#storageSelect').val();
			var params = {start:0, length:10, search: {}, accurateFlag: false, taskuuid:task_uuid, agentuuid:agent_uuid,storage_uuid: storage_uuid};
			var data = {m:CONF.M.FILE,f:'getFsTimepointGrid',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();
		}
	}
	
	//初始化日期选择插件
    var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepicker').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
			"minDate": moment().subtract(1, 'month'), //最早可以选的日期  
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
		    "timePicker": true,													//是否显示时间,时分
		    "timePicker24Hour": true,											//是否是24小时制
		    "alwaysShowCalendars": true,										//是否总是显示日期选择
		    "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		    "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});
		
		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#daterangepicker').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
		});

        $('#daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
        	//清除全局变量,然后设置input
        	_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			_daterangepicker_range = "";
            $(this).val('');
        });
		
        //input右侧的图标事件
		$('.daterangepickerdiv i').click(function() {
		    $(this).parent().find('input').click();
		});
		
	}

	var searchFS = function(){
		if($.trim($('#searchfs').val()) == "" && !$("input[name=foreverFilter]").get(0).checked) {
			return;
		}
		//
		var storage_uuid = $('#storageSelect').val();
		var p = JSON.stringify({storage_uuid: storage_uuid, dataflag:true });
		$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'getFileDataTree',p: p}, function (d) {
			setTree(d);
			nodeParamList = "";
			nodeParamList = zTree.getNodesByFilter(filterZtree)
			//异步搜索时间点
			var data = {}
			data.storage = $('#storageSelect').val();
			data.search = $('#searchfs').val();
			data.forever = $("input[name=foreverFilter]").get(0).checked;
			data.dataFlag = true;
			data = JSON.stringify(data);
			Metronic.blockUI({target:'#filetimepointtree',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'searchFsTimepoint',p:data}, function(d) {
				Metronic.unblockUI('#filetimepointtree');
				var pointNode = JSON.parse(d);
				showNode = [];
				if(pointNode != null) {
					pointNode.forEach(item => {
						//在当前树节点中搜索异步请求得到的时间点，如果当前树不存在，加入showNode数组
						var nodeExist = zTree.getNodesByParam("id", item.id, null)[0];
						if(nodeExist == null) {
							showNode.push(item);
						}
					});
					//把showNode中时间点加到对应父节点下
					if(showNode.length != 0) {
						showNode.forEach(item => {
							//先把完备点加进去，再放增量差异等，避免先搜增量差异等搜不到父节点
							if(item.type == 3) {
								var parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
								zTree.addNodes(parentNode, item, true);
								// 找到任务名，如果任务名不同，把关键词改了
								if(parentNode) {
									var taskNode = parentNode.getParentNode();
									if (taskNode.task_name != item.task_name) {
										taskKeyWord = taskNode.task_name;
									}
								}
							}
						});
						showNode.forEach(item => {
							if(item.type == 4) {//增量差异等
								var parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
								zTree.addNodes(parentNode, item, true);
								if(parentNode) {
									var taskNode = parentNode.getParentNode();
									if (taskNode.task_name != item.task_name) {
										taskKeyWord = taskNode.task_name;
									}
								}
							}
						});
					}
				}
				searchByTree();
			});
		});

    }
	var searchByTree = function() {
		if (!taskKeyWord) {
			var keyword = $.trim($('#searchfs').val());
		} else {
			var keyword = taskKeyWord;
			taskKeyWord = '';
		}

		var allNodes = zTree.transformToArray(zTree.getNodes());
		zTree.hideNodes(allNodes);    //当开始搜索时，先将所有节点隐藏
		var nodeList = [];
		if(keyword != '') {
			nodeList = zTree.getNodesByParamFuzzy('name', keyword, 0);    //通过关键字模糊搜索
		}
		//搜gfs标记点
		if($("input[name=foreverFilter]").get(0).checked) {
			nodeList =  zTree.getNodesByParamFuzzy('name', 'viconfont vicon-remark-forever', 0);
		}
 		if(nodeList.length == 0) {
			$('#nosearchtips').show();
			return;
		}
		var arr = new Array();
		for(var i=0; i<nodeList.length; i++){
			arr = $.merge(arr,nodeList[i].getPath());    //找出节点的所有父节点（包括自己）
			if(nodeList[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr,nodeList[i].children);
			}
		}
		var firstlevel = [];
		var otherlevel = [];
		arr.forEach(item=>{
			if(item.level != 0) {
				otherlevel.push(item);
			}else {
				firstlevel.push(item);
			}
		});
		var arr = zTree.transformToArray(otherlevel);//避免获取到第一层下的其他任务
		arr = arr.concat(firstlevel);
		zTree.showNodes($.unique(arr));    //显示所有要求的节点及其路径节点
		//展开显示的所有节点
		arr.forEach(item => {
			if(item.children) {
				zTree.expandNode(item, true);
			}
		});
	}

	//ztree的复杂过滤
	var filterZtree = function(node){
		var markStr = LANG.UI_SETTING_VM_DATA_SCREEN + ": ";
		var resultVal = false;
		var resultForever = false;
		
		//获得数据
		var value = $('#searchfs').val();
		var forever = $("input[name=foreverFilter]").get(0).checked;
		if(node.level==0 || node.level==1) {
			var ThisName = node.name;
		}else {
			var ThisName = node.oldname;
		}
		if(value == "" && !forever){
			$("#markStr").empty();
			return true;
		}
		if(forever){
			var foreverval = "viconfont vicon-remark-forever";
			if(node.gfsforever!=undefined&&node.gfsforever.indexOf(foreverval) > -1){
				resultForever = true;
			}
			markStr += '<i class="viconfont vicon-remark-forever"></i>'
		}
		//检测
		if(value != ""){
			if(ThisName.indexOf(value) > -1){
				resultVal = true;
			}
			markStr += value;
		}
		markStr += '<a id="clearGFS" style="text-decoration: underline;margin-left: 10px;">'+ LANG.UI_SETTING_VM_DATA_SCREEN_CLEAN +'</a>'
		$("#markStr").html(markStr);
		//添加清除点击事件
		$("#clearGFS").on('click',function(){
			//得到所有节点集合
			var Nownodes = zTree.getNodes();
			// 删除所有新搜索出来的时间点，避免异步加载不加载其他节点
			showNode.forEach(item => {
				var delnode = zTree.getNodesByParam("id", item.id, null)[0];
				if(delnode != null && delnode.getParentNode() != null) {
					zTree.removeChildNodes(delnode.getParentNode());
					delnode.getParentNode().isParent = true;
					zTree.updateNode(delnode.getParentNode()); 
				}
			});
			//得到所有隐藏的节点
			var nodes = zTree.getNodesByParam("isHidden", true);
			//显示所有被隐藏的节点
			zTree.showNodes(nodes);
			//显示div
			$("#filetimepointtree").show();
			$("#nopointtips").hide();
			$('#nosearchtips').hide();
			if(Nownodes.length ==0){
				$("#nopointtips").show();
			}
			$("#markStr").empty();
		})
		if(resultVal || resultForever){
			return true;
		}else{
			return false;
		}
	}
	//删除时间点
	var deleteSelectPoint = function(){
		var node = zTree.getCheckedNodes(true);
		if(node.length == 0){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
			return;
		}
		pointList = [];
		agentList = [];
		// taskuuidList = [];
		for(var i=0; i<node.length; i++){
			if(node[i].type == 2){//agent
				if(!node[i].children){
					var agent = {};
					agent.agentuuid = node[i].agentuuid;
					agent.taskuuid = node[i].taskuuid;
					agent.nodeuuid = node[i].nodeuuid;
					agent.type = node[i].type;
					agent.src_data_deleted_flag = node[i].src_data_deleted_flag;//备份时间点的数据是否在生产中被删除（归档）
					agentList.push(agent);
				}
			}else if(node[i].type == 3){//时间点
				var point = {};
			    point.timepointuuid = node[i].point_uuid;
			    point.nodeuuid = node[i].nodeuuid;
			    point.type = node[i].type;
				point.src_data_deleted_flag = node[i].src_data_deleted_flag;//备份时间点的数据是否在生产中被删除（归档）
			    pointList.push(point);
			    if(node[i].isParent){
			    	var children = node[i].children;
			    	for(var j=0;j<children.length;j++){
			    		var point = {};
			    		point.timepointuuid = children[j].point_uuid;
			    		point.nodeuuid = children[j].nodeuuid;
			    		point.type = children[j].type;
						point.src_data_deleted_flag = children[j].src_data_deleted_flag;//备份时间点的数据是否在生产中被删除（归档）
			    		pointList.push(point);
			    	}
			    }
			}
		}
		// 检测要删除的是否有归档数据
		var archiveFlag = checkArchiveData(agentList,pointList);
		bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_DELETE_TIMEPOINT_TIPS,
            callback: debounce(function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({ 
				    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES, 
				    inputType: 'password',
				    callback: debounce(function (result) {
				    	if(result == null) return;
				        if(hex_md5(result) == _UserPassword){
							//如果是归档数据，三次弹出确认框再确认
							if(archiveFlag) {
								bootbox.confirm({
									title: LANG.UI_DATA_DELETE_TIMEPOINT,
									message: LANG.UI_DATA_DELETE_TIMEPOINT_ARCHIVE_TIPS,
									callback: debounce(function(r) {
										if(!r) return;
										submitSelectDelete();
										return true;
									}, 300),
								});
							} else {
								submitSelectDelete();
								return true;
							}
				        }else{
				        	$('.bootbox-input').css('border-color', "#a94442");
				        	if(!initErrorFlag){
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
					        	$('.bootbox-input').after(des);
				        		initErrorFlag = true;
				        	}
				        	return false;
				        }
				    }, 300),
				});
            }, 300),
        });
	}

	var checkArchiveData = function (agentList,pointList) {
		var agentFlag = agentList.some(item =>{
			if(item.src_data_deleted_flag) return true;
		} );
		var pointFlag = pointList.some(item =>{
			if(item.src_data_deleted_flag) return true;
		} );
		if(agentFlag || pointFlag) {
			return true;
		} else {
			return false;
		}
	}
	
	//递归移除节点,如果是增量的话是相互依赖的(都是依赖于上一个备份点)
	var removeNode = function(nodes, treeNode){
		for(var i=0; i<nodes.length; i++){
			if(nodes[i].type == 3){
				if(nodes[i].depend_uuid == treeNode.point_uuid){
					zTree.removeNode(nodes[i]);
					removeNode(nodes, nodes[i]);
				}
			}
		}
	}
	
	//提交删除备份时间点
	var submitSelectDelete = function(){
		var node = zTree.getCheckedNodes(true);
		var storage_uuid = $('#storageSelect').val();
		var params = {pointList:pointList, agentList: agentList, storage_uuid:storage_uuid};
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#timepointdiv',animate: true});
		//发送到自己模块
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'deleteSelectTimepoint',p:params}, function(data){
    		Metronic.unblockUI('#timepointdiv');
			if(OPREL(data)){
				for(var i=0;i<node.length;i++){
					var halfCheck = node[i].getCheckStatus();
					if(!halfCheck.half){
						refreshAllTree(node[i].id);
					}else{
						zTree.checkNode(node[i],!node[i].checked,false,false);
					}
				}
				grid.getRefresh({});
			}
    	});
	}	
	//一一对应timepointuuid进行清除树节点
	var refreshAllTree = function(nodeid){
		var nodes = zTree.getNodesByParam("id", nodeid, null);
		if(0 == nodes.length){
			return;
		}
		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		var parent = nodes[0].getParentNode();
		if(parent !=null){
			var childrenNum = parent.children.length;
		}
		if(1 == childrenNum){
			zTree.removeNode(parent);
		}else{
			zTree.removeNode(nodes[0]);
		}
	}
	
	 
	//如果把一个任务下所有时间点删除后,需要把这个树上的任务移除
	var refreshTree = function(nodeid){
		var nodes = zTree.getNodesByParam("id", nodeid, null);
		if(0 == nodes.length){
			return;
		}
		//要删除的节点
		var fsNode = nodes[0];
		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		var parent = fsNode.getParentNode();
		if(!parent) return;
		var childrenNum = parent.children.length;
		if(1 == childrenNum && fsNode.type == 3){
			zTree.removeNode(parent);
			removeParentNode(parent);
		}else{
			zTree.removeNode(fsNode);
		}
		
		
	}
	
	//删除父节点
	var removeParentNode = function(node){
		var parent = node.getParentNode();
		if(!parent) return;
		var childrenNum = parent.children.length;
		if(0 == childrenNum){
			zTree.removeNode(parent);
			removeParentNode(parent);
		}else{
			return;
		}
		
	}
	
	
	//标星统一处理
	var starHandler = function(params, funName){
		Metronic.blockUI({target: '#setAllMark',animate: true});
		//发送到数据管理统一处理
    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:funName,p:params}, function(data){
			var d = JSON.parse(data);
    		//如果失败
    		if(!d.re){
    			UIToastr.showWarning(LANG.UI_OS_DATA_SET_MARK, LANG.UI_SETTING_VM_MARK_POINT_FAIL);
    		}else {
				UIToastr.showSuccess(LANG.UI_OS_DATA_SET_MARK, LANG.UI_NAS_DATA_SET_MARK_SUCCESS);
				grid.getRefresh({});
    			Metronic.unblockUI('#setAllMark');
				
			}
    	});
	}
	
	//设置时间点树
	var setTree = function(zNodes){
		$('#filetimepointtree').show();
		$("#nopointtips").hide();
		if(zNodes == "[]"){
			$('#filetimepointtree').hide();
			$("#nopointtips").show();
			return;
		}
		function showTitleForTree(treeId, treeNode) {
			return treeNode.type != 1;
		};
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				view: {
					nameIsHTML: true
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
					onCheck: pointOnCheck,
					beforeClick: nodeSelect,
					beforeExpand: nodeExpand
				}
			};
		zTree = $.fn.zTree.init($("#filetimepointtree"), setting, JSON.parse(zNodes));
		initTreeFlag = true;
	};
	
	//选中路径
	var pointOnCheck = function(e, id, node){
		
	}
	//得到当前节点的所有子节点
	var getAllChildren = function(node, allNode){
		
		if(node.isParent){
			var children = node.children;
			for(var i=0;i<children.length;i++){
				allNode.push(children[i]);
				if(children[i].isParent){
					getAllChildren(children[i], allNode);
				}
			}
			
		}else{
			allNode.push(node);
		}
		return allNode;
	}
	
	
	//选择节点事件
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type) {//任务名
			$('#fsdataUrl').text('');
			var parent = treeNode.getParentNode();
			var info = parent.name + "---" + treeNode.name;
			$('#fsdataUrl').append(info);
			// agentuuid, taskuuid, createtime,storage_uuid录入备份数据列表
			var storage_uuid = $('#storageSelect').val();
			fileDataTable(treeNode.agentuuid, treeNode.taskuuid,storage_uuid);
		}
		//异步加载时间点
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
	}
	
	//节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncVcenterInfo(treeId, treeNode, false, false);
		}else{
			return true;
		}
		
	}
	
	//异步获取文件备份数据  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var checkeFlag = treeNode.checked;
		var data = JSON.stringify({taskuuid:treeNode.taskuuid, id: treeNode.id,
			refresh:refreshFlag, storage_uuid: treeNode.storage_uuid, agentuuid: treeNode.agentuuid, dataFlag: true,recoverflag:true});
		var div = ".vcenter-tree";
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.FILE,f:'getSyncFileTimepoint',p:data},
	        success: function(data){ 
	        	Metronic.unblockUI(div);
	        	result = JSON.parse(data);
	        	if(result.re){
	        		//success
					if(checkeFlag) {
						for(var i=0;i<result.msg.length;i++) {
							result.msg[i].checked = true;
						}
					}
	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
	        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
	        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
	        		if(expendFlag == true){
	        			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
	        		}
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
	}
	//初始化时间点树
	var initTree = function() {
		var storage_uuid = $('#storageSelect').val();
		var p = JSON.stringify({storage_uuid: storage_uuid, dataflag:true });
		$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'getFileDataTree',p: p}, setTree);
	};
    //初始化时间点展示方式和事件
	var initStorageSelect = function(){
		pAjaxRequest({backupDataFlag: true}, '/api/v1/storages/type', "GET", (result) => {
			if (result.success) {
				let data = result.data;
				let storageSelect = $('#storageSelect')
				storageSelect.empty();
				for (let i = 0; i < data.length; i++) {
					let option = $("<option>").text(data[i].text).val(data[i].storageid);
					storageSelect.append(option);
				}
			} else {
				UIToastr.showWarning(LANG.UI_OBS_GET_STORAGE_FAILED, res.message);
			}
		})
		//绑定事件
		$('#storageSelect').on('change', storageSelectChange);
	}

	var storageSelectChange = function(){
		initTreeFlag = false;
		initTree();
	}
	//初始化当前用户密码用于删除二次确认
	var initUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		var opDiv = $('tbody > tr').find('td:eq(6)');
		for(var i=0; i<data.length; i++){
			opButton(opDiv[i], data[i][6], i,data[i][7]);
		}
		$('.remarktips').popover();	   //初始化tips
	}
	
	
	//添加操作按钮
	var opButton = function(div, opCode, rowNum,otherMsg){
		var mode = otherMsg['backupmode'];
		var button = '<div class="btn-group dropdown-wrapper positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group dropdown-wrapper positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu">';
		if(mode ==1){
			$.each(opCode, function(i, d){
				switch(d){
					case 1:
						if (CONF.PERMISSION_ARR.includes('p_filedata_remark')) {
							button += '<li class="remark"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>';
                        }
						break;
					case 2:
						if (CONF.PERMISSION_ARR.includes('p_filedata_detele')) {
							button += '<li class="delete"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_delete me-4"></i> ' + LANG.UI_PUBLIC_DELETE + '</button></li>';
                        }
						break;
					case 3:
						if (CONF.PERMISSION_ARR.includes('p_filedata_star')) {
							button += '<li class="setmark"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_sign me-4"></i> ' + LANG.UI_SETTING_VM_DATA_SET_MARK + '</button></li>';
                        }
						break;
				}
			});
		}else{
			$.each(opCode, function(i, d){
				switch(d){
					case 1:
						button += '<li class="remark"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>';
						break;
				}
			});
		}
		button += '</ul></div>';
		
		$(div).html(button);
		addOpButtonListener();
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('.remark').unbind().on('click', remarkPoint);
		$('.delete').unbind().on('click', deletePoint);
		$('.setmark').unbind().on('click', setMark);
	}
	//删除时间点
	var deletePoint = function(){
		var button = this;
		bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_DELETE_TIMEPOINT_TIPS,
            callback: debounce(function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({ 
				    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES, 
				    inputType: 'password',
				    callback: debounce(function (result) {
				    	if(result == null) return;
				        if(hex_md5(result) == _UserPassword){
							var data = grid.getDataTable().data();
							var row = $(button).parents('tr').get(0)._DT_RowIndex;
							// 检测要删除的是否有归档数据
							var archiveFlag = data[row][6].src_data_deleted_flag==1 ? true: false;
							//如果是归档数据，三次弹出确认框再确认
							if(archiveFlag) {
								bootbox.confirm({
									title: LANG.UI_DATA_DELETE_TIMEPOINT,
									message: LANG.UI_DATA_DELETE_TIMEPOINT_ARCHIVE_TIPS,
									callback: debounce(function(r) {
										if(!r) return;
										submitDelete(button);
										return true;
									}, 300),
								});
							} else {
								submitDelete(button);
								return true;
							}
				        }else{
				        	$('.bootbox-input').css('border-color', "#a94442");
				        	if(!initErrorFlag){
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+ LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS +'</p>';
					        	$('.bootbox-input').after(des);
				        		initErrorFlag = true;
				        	}
				        	return false;
				        }
				    }, 300),
				});
            }, 300),
        });
	}
	var submitDelete = function(button){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = {uuid:[data[row][7].uuid], agentuuid: [data[row][7].agentuuid], taskuuid: [data[row][7].taskuuid]};
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#timepointdiv',animate: true});
		//发送到自己模块
    	$.post(CONF.AJAXPATH, {m:CONF.M.FILE,f:'deleteTimepoint',p:params}, function(data){
    		Metronic.unblockUI('#timepointdiv');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			var d = JSON.parse(data);
    			if(0 == d.ext.count){
    				//重新刷新时间点数
    				var info = JSON.parse(params);
    				var idStr = info.uuid;
    				//先刷新界面树
    				refreshTree(d.ext.id);
    				var node = zTree.getNodesByParam("id", idStr, null);
    				//如果还有时间节点执行刷新树操作
    				if(node.length !=0){
    					getSyncVcenterInfo("zTree", node[0], true, true);
    				}
    			}
    		}
    	});
	}
	//设置时间点保留标记
	var setMark = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		_Remark = params.remark;
		//得到GFS保留标记
		gfsList = {};
		gfsList.week = params.weekly_flag;	//周
		gfsList.month = params.monthly_flag;//月
		gfsList.year = params.yearly_flag;	//年
		gfsList.forever = params.importance_flag;//永久保留
		//初始化清空所有勾选项
		$("input[name=weekCheck]").iCheck('uncheck');
		$("input[name=monthCheck]").iCheck('uncheck');
		$("input[name=yearCheck]").iCheck('uncheck');
		$("input[name=foreverCheck]").iCheck('uncheck');
		//设置为只有完全备份才可GFS
		if(params['mode'] != 1){
			$("input[name=weekCheck]").iCheck('disable');
			$("input[name=monthCheck]").iCheck('disable');
			$("input[name=yearCheck]").iCheck('disable');
			if(params['src_data_deleted_flag'] == 1){
				//如果是归档数据，禁用永久标记(默认开启)
				$("input[name=foreverCheck]").iCheck('disable');
			}else{
				$("input[name=foreverCheck]").iCheck('enable');
			}
		}else{
			$("input[name=weekCheck]").iCheck('enable');
			$("input[name=monthCheck]").iCheck('enable');
			$("input[name=yearCheck]").iCheck('enable');
			$("input[name=foreverCheck]").iCheck('enable');
		}
		
		//设置值
		$("#Marktimepoint_uuid").val(params.uuid);
		$("#MarktimepointNodeUUID").val(params.nodeuuid);
		$("#Marksubmode_type").val(params.mode);
		//设置勾选
		if(gfsList.week){
			$("input[name=weekCheck]").iCheck('check');
		}
		if(gfsList.month){
			$("input[name=monthCheck]").iCheck('check');
		}
		if(gfsList.year){
			$("input[name=yearCheck]").iCheck('check');
		}
		if(gfsList.forever){
			$("input[name=foreverCheck]").iCheck('check');
		}
		
		$('#setAllMark').modal('show');
	}
	//设置标记确认
	var markSubmit = function(){
		//先得到所有信息
		var forever = $("input[name=foreverCheck]").get(0).checked;
		//得到信息后先判断是否有做修改 如果没有做修改则不提交后台
		if(gfsList.forever == forever){
			$('#setAllMark').modal('hide');
			gfsList = {};
			return;
		}
		//时间点uuid
		var uuid = $("#Marktimepoint_uuid").val();
		//GFS和F标记点是分开发信息的 所以要发送2个信息
		//先组装数据
		var item_list = [];
		
		//设置永久标记
		if(gfsList.forever != forever){
			if(forever){
				var params = {};
				params.uuid = uuid;
				params = JSON.stringify(params);
				starHandler(params,'addStar')
			}else{
				var params = {};
				params.uuid = uuid;
				params = JSON.stringify(params);
				starHandler(params,'deleteStar')
			}
		}
		$('#setAllMark').modal('hide');
		grid.getRefresh({});
		editZtreeName(uuid,false,false,false,forever,_Remark);
		//gfs相关

		// //组装参数发往后台
		// var paramsMark = {};
		// paramsMark.timepoint_uuid = uuid;
		// paramsMark.item_list = item_list;
		// paramsMark.nodeuuid = $("#MarktimepointNodeUUID").val();
		// paramsMark.submode_type = $("#Marksubmode_type").val();
		// var p = JSON.stringify(paramsMark);
		
		// //设置GFS保留标记
		// //发送到数据管理统一处理
		// Metronic.blockUI({target: '#setAllMark',animate: true});
    	// $.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:'setGFSMark',p:p}, function(data){
    	// 	Metronic.unblockUI('#setAllMark');
    	// 	if(OPREL(data)){
    	// 		$('#setAllMark').modal('hide');
    	// 		grid.getRefresh({});
    	// 		editZtreeName(uuid,week,month,year,forever,_Remark);
    	// 	}
    		
    	// });
		
		
	}
	//静态修改Ztree的名称
	var editZtreeName = function(timepointUUID,$Wflag,$Mflag,$Yflag,$Fflag, remark){
		//根据timeUUID得到ztree的node数据，没有搜到返回null
		var node = zTree.getNodeByParam('timepointuuid', timepointUUID, null);
		if (!node) {
			return;
		}
		//获得原来的name
		var old_name = node.name;
		var old_name = node.oldname;
		
		var markStr = '';
        if($Wflag){
        	markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_WEEK_POINT+'" class="viconfont vicon-remark-week"></i>';
        }
        if($Mflag){
        	markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_MONTH_POINT+'" class="viconfont vicon-remark-month"></i>';
        }
        if($Yflag){
        	markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_YEAR_POINT+'" class="viconfont vicon-remark-year"></i>';
        }
        if($Fflag){
        	markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_FOREVER_POINT+'" class="viconfont vicon-remark-forever"></i>';
        }
		// if(remark && remark != ""){
        // 	markStr += '<a style="display:inline-block;color: #5b9bd1;position: relative;top:5px;left:-4px;" class="popovers remarktips" data-container="body" data-trigger="hover" data-placement="right" data-content="'+ remark +'"><i class="fa fa-info-circle fa-lg" style="font-size: 21px !important; position:relative;top:-4px;"></i></a>';
        // }
        if(node.src_data_deleted_flag) {
			var new_name = old_name+ "("+LANG.UI_VIRTUAL_ARCHIVE_DATA+")" + " "+markStr;
		} else {
			var new_name = old_name+" "+markStr;
		}
		node.name = new_name;
		zTree.updateNode(node);
		//清除已存在的备注
		$('#remark_'+timepointUUID).remove();
	}
	
	//布尔类型转成int1和2
	var boolToInt = function(thisbool){
		if(thisbool){
			return 1;
		}else{
			return 2;
		}
		
	}
	//添加备注
	var remarkPoint = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		var value = params['remark'];
		bootbox.prompt({
            title: LANG.UI_DATA_ADD_REMARK,
            value:value,
            callback: debounce(function(result) {
				if(!reXssEncode(result)) {
					return;
				}
            	if(result == null || $.trim(result) == value) return;
        		submitRemark($.trim(result), params);
            }, 300),
        });
	}
	
	var submitRemark = function(remark, params){
		params.remark = remark;
		p = JSON.stringify(params);
		Metronic.blockUI({target: '#datatable',animate: true});
		//发送到数据管理统一处理
    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:'remarkTimepoint',p:p}, function(data){
    		Metronic.unblockUI('#datatable');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
			editZtreeName(params.uuid,false,false,false,params.importance_flag,remark);
    	});
	}
	
	//初始化备份数据表格
	var fileDataTable = function (agentuuid, taskuuid,storage_uuid) {
		task_uuid = taskuuid;
    	agent_uuid = agentuuid;
		if(!gridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [4, 6]
	    			}],
	    			"order": [
	                    [0, "desc"]
	                ],
	    	};
			grid = new Datatable();
			var data = {m:CONF.M.FILE,f:'getFsTimepointGrid',p:{agentuuid:agentuuid, taskuuid:taskuuid, storage_uuid: storage_uuid}};
			grid.setAjaxParam(data);
			grid.init({src: $("#datatable"), onDataLoad:addOpButton, dataTable:dataTableOpt});
	    	gridInitFlag = true;
	    	$('#tabletips').hide();
			$('#filetablediv').show();
			$('#marktips').show();
		}else{
			//刷新表格
			grid.getRefresh({agentuuid:agentuuid, taskuuid:taskuuid, storage_uuid: storage_uuid}, undefined, true);
		}
    	return;
    }
    return {
        //main function to initiate the module
        init: function () {
			inintDatatimePicker();
			initStorageSelect(); //初始化节点下拉列表
        	initTree();
        	initListener();
        	initUserPassword();
        }

    };

}();

jQuery(document).ready(function() {    
    FileData.init();
});