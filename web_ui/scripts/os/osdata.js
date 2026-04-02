var OsData = function () {
	var grid, gridInitFlag = false,nodeTypetreeInitFlag = false;
	var zTree,nodeParamList,timepointList, oslist; //节点树，节点搜索列表，时间点存放列表，数据库列表
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	var db_uuid,agent_uuid, task_uuid;
	var _UserPassword;
	var _Remark = ""; //GFS标记用备注
	var initErrorFlag = false;
	var removeList = [];
	
	var setTree = function(zNodes){
		$("#os_tree_data").show();
		$("#nopointtips").hide();
		Metronic.unblockUI('#os_tree_data');
		if(zNodes == "[]"){
			$("#os_tree_data").hide();
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
					onCheck: nodeCheck,
					beforeExpand: nodeExpand
				},
				view: {
					showTitle: showTitleForTree,
					nameIsHTML: true,
				}
			};
		zTree = $.fn.zTree.init($("#os_tree_data"), setting, JSON.parse(zNodes));
		nodeTypetreeInitFlag = true;
	};
	
	//点击节点，选中并展开
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.type == 1){
			$('#dbdataUrl').text('');
			var parent = treeNode.getParentNode();
			var info = parent.name + "---" + treeNode.name;
			$('#dbdataUrl').append(info);
			handleRecords(treeNode.taskuuid, treeNode.agentuuid); //录入备份数据列表
		}
//		if(treeNode.type !=  4){
//			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_TIMEPOINT,LANG.UI_DATA_BATCH_DELETE_TIMEPOINT_TIPS);
//		}
//		zTree.checkNode(treeNode, !treeNode.checked, true, true);
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
	}
	
	
	//节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncDBInfo(treeId, treeNode, false, false);
		}else{
			return true;
		}
		
	}
	
	//异步获取数据库的时间点信息  refreshFlag是否重新刷新
	var getSyncDBInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var storageuuid = $('#storageselect').val();
		var data = JSON.stringify({taskuuid:treeNode.taskuuid, agentuuid:treeNode.agentuuid,recoverflag:false,dataflag:true,
			refresh:refreshFlag, storageuuid: storageuuid, oschecked:treeNode.checked});
		var div = ".os_center_tree";
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:34,f:'getSyncTimepoint',p:data},
	        success: function(data){ 
	        	Metronic.unblockUI(div);
	        	result = JSON.parse(data);
	        	if(result.re){
	        		//success
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
	
	//数据库类型不同时，删除之前选择禁用的增量和差异备份时间点
	var nodeCancel = function(id,node){
		var allNodes = zTree.getCheckedNodes(true);
		if(!node.checked){
			for(var i = 0; i < allNodes.length; i++){
				if(allNodes[i].type == 3 && allNodes[i].isParent && allNodes[i].type != node.type){
					var children = allNodes[i].children;
					for(var l=0;l<children.length;l++){
						zTree.setChkDisabled(children[l], false);
			            zTree.checkNode(children[l],false,true);
			            zTree.setChkDisabled(children[l],true);
					}
				}
			}
		}
	}
	
	//得到当前节点的所有子节点
	var getAllChildren = function(node, allNode){
		
		if(node.isParent){
			var children = node.children;
			if(children && children.length != 0){
				for(var i=0;i<children.length;i++){
					allNode.push(children[i]);
					if(children[i].isParent){
						getAllChildren(children[i], allNode);
					}
				}	
			}
			
		}else{
			allNode.push(node);
		}
		return allNode;
	}
	
	//选择备份节点
	var nodeCheck = function (e, id, node){
		var allNodes = zTree.getCheckedNodes(true);
		//如果选中的是完备点,勾选和未勾选时同步处理其子节点
		if(node.type <= 3 && node.checked ){
			for(var j=0;j<allNodes.length;j++){
				if(allNodes[j].type == 2 &&allNodes[j].isParent && allNodes[j].checked){
					var children = allNodes[j].children;
				    for(var i=0;i<children.length;i++){
				    	zTree.setChkDisabled(children[i], false);
				    	zTree.checkNode(children[i], true, true);
				    	zTree.setChkDisabled(children[i], true);
				    }
				}
			}
		}else if(node.type <= 3){
			var allChildren = getAllChildren(node,[]);
			for(var j=0;j<allChildren.length;j++){
				if(allChildren[j].type == 3){
			        zTree.setChkDisabled(allChildren[j], false);
			        zTree.checkNode(allChildren[j],false,true);
			        zTree.setChkDisabled(allChildren[j],true);
			    }
		    }
		}
	}
	
	//初始化节点树
	var initTree = function() {
		var data = {}
		data.storageuuid = $('#storageselect').val();
		data.recoverflag = false;
		data.dataflag = true;
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#os_tree_data',animate: true});
		$.post(CONF.AJAXPATH, {m:34,f:'getTimepointTree',p:data}, setTree);
	}
	
	//初始化时间点展示方式和事件
	var initPointShowType = function(){
		var params = JSON.stringify({moduleType: CONF.MODULE_TYPE.OS,dataFlag: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getTimepointAllNode',p:params}, function(d){
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

	//初始化存储类型展示方式和事件
	var initStorageShowType =  function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getStorageType',p:JSON.stringify({backupDataFlag: true})}, function(d){
			var data = JSON.parse(d);
			var storageselect = $('#storageselect');
			storageselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].storageid);
				storageselect.append(option);
			}
		});
		//绑定事件
		$('#storageselect').on('change', storageselectChange); //存储类型改变事件
	};

	
	//节点选择改变事件
	var storageselectChange = function(){
		nodeTypetreeInitFlag = false;
		$("#markStr").empty();
		initTree();
	}
	
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		var opDiv = $('tbody > tr').find('td:eq(7)');
		var starDiv = $('tbody > tr').find('td:eq(7)');
		var partDiv = $('tbody > tr').find('td:eq(5)');
		var storageDiv = $('tbody > tr').find('td:eq(4)');
		for(var i=0; i<data.length; i++){
			opButton(opDiv[i], data[i][7], i);
			partInfoStr(partDiv[i], data[i][5]);
			// opStorage(storageDiv[i], data[i][5], i);
//			starButton(starDiv[i], data[i],data[i][9]);
		}
		$('.remarktips').popover();	   //初始化tips
	}
	var partInfoStr = function(div,data){
		//得到数据
		var list = data;
		//得到数据显示内容，如果超过三个则只显示3个后面显示省略号
		var datalist = "";
		for(let i = 0; i<list.length;i++){
			if(i<3){
				datalist += list[i] + "<br>";
			}else{
				datalist += "...";
				break;
			}
		}
		
		var titlelist = "";
		for(let i = 0; i<list.length;i++){
			if(i<30){
				titlelist += list[i] + "&#10;";
			}else{
				titlelist += "...";
				break;
			}
		}
		
		var htmllist = "<p title = '"+titlelist+"'>"+datalist+"</p>"
		
		$(div).html(htmllist);
		
	}
	
	var starButton = function(div, data, otherMsg){
		var htmlStr = '';
		if("2" == data[8]){
			htmlStr = '<a title="' + LANG.UI_DATA_MARK_STAR + '"><i class="viconfont vicon-ge_sign"></i></a>';
		}else if(1 == data[8]){
			htmlStr = '<a title="' + LANG.UI_DATA_UNMARK_STAR + '"><i class="viconfont vicon-ge_sign"></i></a>';
		}
		$(div).html(htmlStr);
		$('.fa-star-o').unbind().on('click', addStar);
		$('.fa-star').unbind().on('click', deleteStar);
	}

	/**
	 * 添加 所在存储 插槽样式
	 * @param {*} div 插槽div
	 * @param {*} data 插槽数据
	 * @param {*} rowNum 插槽所在行
	 */
	var opStorage = function(div, data, rowNum) {
		//得到存储
		let storage = data.split('\n')[0] || '';
		// 节点ip
		let nodeIp = data.split('\n')[1] || '';
		// 节点
		let node = nodeIp.substring(nodeIp.indexOf('(') + 1 ,nodeIp.lastIndexOf('('));
		// ip
		let ip = nodeIp.substring(nodeIp.lastIndexOf('(') + 1 ,nodeIp.indexOf(')')).trim();
		// ip数组
		let ips = ip.split(/\s+/);
		let tdStorage = '';
		if(node != '('){
			tdStorage = '<div class="td-storage">' + 
						'<div class="td-storage__alias">' + storage + '</div>' + 
						'<div class="td-storage__node">(' + node + traverseStorageNodes(ips) + ')</div>' + 
					'</div>';
		}else{
			tdStorage = '<div class="td-storage">' + 
						'<div class="td-storage__alias">' + storage + '</div>' + 
						'<div class="td-storage__node">' + traverseStorageNodes(ips) + '</div>' + 
					'</div>';
		}
		$(div).html(tdStorage);
	};

	/**
	 * 遍历所在节点和IP地址
	 * @param {*} arr 
	 * @returns 
	 */
	var traverseStorageNodes = function (arr) {
		if(arr.length === 0) {
			return;
		} else {
			let result = '';
			result = arr.map((node, i) => {
				if(arr.length != 1){
					if(i === arr.length - 1){
						return `<span class="td-storage__node__item" title="${node}">${node})</span>`;
					}else if(i == 0){
						return `<div class="td-storage__node__item" title="${node}">(${node}</div>`;
					}else{
						return `<div class="td-storage__node__item" title="${node}">${node}</div>`;
					}
				}else{
					return `<div class="td-storage__node__item" title="${node}">(${node})</div>`;
				}
			})
			return result.join('');
		}
	}
	
	//添加操作按钮
	var opButton = function(div, data, rowNum){
		var button = '<div class="btn-group positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu">';
		
		var opCode = data.operate;
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="remark"><a href="javascript:;" ><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_PUBLIC_REMARK + '</a></li>';
					break;
				case 2:
					button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_PUBLIC_DELETE + '</a></li>';
					break;
				case 3:
					button += '<li class="setmark"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> ' + LANG.UI_OS_DATA_SET_MARK + '</a></li>';
					break;
			}
		});
		$(div).html(button);
		addOpButtonListener();
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('.remark').unbind().on('click', remarkPoint);
		$('.delete').unbind().on('click', deletePoint);
		$('.setmark').unbind().on('click', setMark);
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
			if(params['hypervisor'] == 2){
				//如果为hyper-v的增备差异也不能设置永久标记点
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
		var week = $("input[name=weekCheck]").get(0).checked;
		var month = $("input[name=monthCheck]").get(0).checked;
		var year = $("input[name=yearCheck]").get(0).checked;
		var forever = $("input[name=foreverCheck]").get(0).checked;
		//得到信息后先判断是否有做修改 如果没有做修改则不提交后台
		if(gfsList.week == week && gfsList.month == month && gfsList.year == year && gfsList.forever == forever){
			$('#setAllMark').modal('hide');
			gfsList = {};
			return;
		}
		//时间点uuid
		var uuid = $("#Marktimepoint_uuid").val();
		//GFS和F标记点是分开发信息的 所以要发送2个信息
		//先组装数据
		var item_list = [];
		//周保留标记
		if(gfsList.week != week){
			var info = {
			    'level1_type': 1,
			    'flag': boolToInt(week)
			
			};
			item_list.push(info);
		}
		//月保留标记
		if(gfsList.month != month){
			var info = {
			    'level1_type': 2,
			    'flag': boolToInt(month)
			
			};
			item_list.push(info);
		}
		//年保留标记
		if(gfsList.year != year){
			var info = {
			    'level1_type': 3,
			    'flag': boolToInt(year)
			
			};
			item_list.push(info);
		}
		
		//设置永久标记
		if(gfsList.forever != forever){
			if(forever){
				var params = {};
				params.uuid = uuid;
				starHandler(params,'addStar',forever)
			}else{
				var params = {};
				params.uuid = uuid;
				starHandler(params,'deleteStar',forever)
			}
		}
		

		
		
		//一下是GFS相关 目前没有
		
//		//组装参数发往后台
//		var paramsMark = {};
//		paramsMark.timepoint_uuid = uuid;
//		paramsMark.item_list = item_list;
//		paramsMark.nodeuuid = $("#MarktimepointNodeUUID").val();
//		paramsMark.submode_type = $("#Marksubmode_type").val();
//		var p = JSON.stringify(paramsMark);
//		
//		//设置GFS保留标记
//		//发送到数据管理统一处理
//		Metronic.blockUI({target: '#setAllMark',animate: true});
//    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:'setGFSMark',p:p}, function(data){
//    		Metronic.unblockUI('#setAllMark');
//    		if(OPREL(data)){
//    			$('#setAllMark').modal('hide');
//    			grid.getRefresh({});
//    			editZtreeName(uuid,week,month,year,forever,_Remark);
//    		}
//    		
//    	});
		
		
	}
	
	
	
//	var setmark = function(){
//		var data = grid.getDataTable().data();
//		var row = $(this).parents('tr').get(0)._DT_RowIndex;
//		var params = data[row][7];
//		var value = params['importance_flag'];
//		//如果永久标记点是开启状态
//		if(value){
//			starHandler(this, 'deleteStar');
//		}else{
//			starHandler(this, 'addStar');
//		}
//	}
	
	//添加备注
	var remarkPoint = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		var value = params['remark'];
		bootbox.prompt({
            title: LANG.UI_DATA_ADD_REMARK,
            value:value,
            callback: function(result) {
            	if(result == null || $.trim(result) == value) return;
        		submitRemark($.trim(result), params);
            }
        });
	}
	
	var submitRemark = function(remark, params){
		params.remark = remark;
		var p = JSON.stringify(params);
		Metronic.blockUI({target: '#datatable',animate: true});
		//发送到数据管理统一处理
    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:'remarkTimepoint',p:p}, function(data){
    		Metronic.unblockUI('#datatable');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			//更新树节点
    			var treeObj = $.fn.zTree.getZTreeObj("os_tree_data");
    			//根据timeUUID得到ztree的node数据，没有搜到返回null
    			var node = treeObj.getNodeByParam('timepointuuid', params.uuid, null);
    			editZtreeName(params.uuid,params.weekly_flag,params.monthly_flag,params.yearly_flag,params.importance_flag,remark);
    		}
    	});
	}
	
	//静态修改Ztree的名称
	var editZtreeName = function(timepointUUID,$Wflag,$Mflag,$Yflag,$Fflag, remark){
		var treeObj = $.fn.zTree.getZTreeObj("os_tree_data");
		//根据timeUUID得到ztree的node数据，没有搜到返回null
		var node = treeObj.getNodeByParam('timepointuuid', timepointUUID, null);
		//获得原来的name
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
        var new_name = old_name+" "+markStr;
	        
		node.name = new_name;
		treeObj.updateNode(node);
		//清除已存在的备注
		$('#remark_'+timepointUUID).remove();
	}
	
	
	
	
	//删除时间点
	var deletePoint = function(){
		var button = this;
		bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_DELETE_TIMEPOINT_TIPS,
            callback: function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({ 
				    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES, 
				    inputType: 'password',
				    callback: function (result) {
				    	if(result == null) return;
				        if(hex_md5(result) == _UserPassword){
				        	submitDelete(button);
				        	return true;
				        }else{
				        	$('.bootbox-input').css('border-color', "#a94442");
				        	if(!initErrorFlag){
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
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
	
	//初始化监听事件
	var initListener = function(){
		$('#allDelete').on('click', deleteSelectPoint);
		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'230px'});
		});
//		$('#searchvm').on('propertychange', searchVM).on('input', searchVM);
		//搜索模态框
		$('#filterSearch').click(function(){
			$('#filterSearch').popModal({
				html : $('#filter-content'),
				placement : 'bottomLeft',
				showCloseBut : true,
				onDocumentClickClose : true,
				onOkBut : searchVM,
				onCancelBut : function(){},
				onLoad : function(){},
				onClose : function(){},
				maxWidth: 300,
				maxHeight: 'auto',
			});
		});
		
		//设置GFS标记
		$("#mark_submit").on('click',function(){
			markSubmit();
		})
		
		
		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			var p = {};
			p.startTime = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.timepointType = $('#searchmodal #timepointType').val();
			p.forever = boolToInt($("input[name=foreverCheck1]").get(0).checked);
			var storageuuid = $('#storageselect').val();
			
			var params = {start:0, length:10, search: p, accurateFlag: true, taskuuid:task_uuid, agentuuid:agent_uuid,storageuuid: storageuuid};
			var data = {m:CONF.M.OS,f:'getOsTimepointGrid',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			grid.getRefresh(params, undefined, true);
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
			var storageuuid = $('#storageselect').val();
			var params = {start:0, length:10, search: p, accurateFlag: true, taskuuid:task_uuid, agentuuid:agent_uuid,storageuuid: storageuuid};
			var data = {m:CONF.M.OS,f:'getOsTimepointGrid',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			var storageuuid = $('#storageselect').val();
			var params = {start:0, length:10, search: {}, accurateFlag: false, taskuuid:task_uuid, agentuuid:agent_uuid,storageuuid: storageuuid};
			var data = {m:CONF.M.OS,f:'getOsTimepointGrid',p:params};
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
	
	
	
	//----------
	//定位搜索时间节点
	var searchVM = function(){
		var checkNode =zTree.getCheckedNodes();
		var allNode = zTree.transformToArray(zTree.getNodes());
		nodeParamList = "";
		nodeParamList = zTree.getNodesByFilter(filterZtree)
//		if(nodeParamList.length == 0){
////			//如果没搜到节点需要异步加载
			searchSyncPoint(allNode);
			
//			$('#nosearchtips').show();
//			$('#os_tree_data').hide();
//		}else{
			//展开
			for(var i=0;i<nodeParamList.length;i++){
				if(nodeParamList[i].open === undefined ){
					continue;
				}
				if(nodeParamList[i].open === false){
					zTree.expandNode(nodeParamList[i],true,false,false);
				}
			}
			
			$('#nosearchtips').hide();
			$('#os_tree_data').show();
			zTree.hideNodes(allNode);
			
			//连接搜索的和所勾选的
			var nodeParamList1 = zTree.transformToArray(nodeParamList);
	        for(var n in nodeParamList1){
	            findParent(zTree,nodeParamList1[n]);
	        }
	        zTree.showNodes(nodeParamList);
//		}
		
		
        
    }
	
	
	//ztree的复杂过滤
	var filterZtree = function(node){
		var markStr = LANG.UI_SETTING_VM_DATA_SCREEN+": ";
//		if(node.type < 3){
//			return false;
//		}
		var resultVal = false;
		var resultWeek = false;
		var resultMonth = false;
		var resultYear = false;
		var resultForever = false;
		
		//获得数据
		var value = $('#searchvm').val();
		var week = $("input[name=weekFilter]").get(0).checked;
		var month = $("input[name=monthFilter]").get(0).checked;
		var year = $("input[name=yearFilter]").get(0).checked;
		var forever = $("input[name=foreverFilter]").get(0).checked;
		var ThisName = node.name;
		if(value == "" && !week && !month && !year && !forever){
			$("#markStr").empty();
			return true;
		}
		if(week){
			var weekval = "viconfont vicon-remark-week";
			if(ThisName.indexOf(weekval) > -1){
				resultWeek = true;
			}
			markStr += '<i class="viconfont vicon-remark-week filter-mark"></i>'
		}
		if(month){
			var monthval = "viconfont vicon-remark-month";
			if(ThisName.indexOf(monthval) > -1){
				resultMonth = true;
			}
			markStr += '<i class="viconfont vicon-remark-month filter-mark"></i>'
		}
		if(year){
			var yearval = "viconfont vicon-remark-year";
			if(ThisName.indexOf(yearval) > -1){
				resultYear = true;
			}
			markStr += '<i class="viconfont vicon-remark-year filter-mark"></i>'
		}
		if(forever){
			var foreverval = "viconfont vicon-remark-forever";
			if(ThisName.indexOf(foreverval) > -1){
				resultForever = true;
			}
			markStr += '<i class="viconfont vicon-remark-forever"></i>'
		}
		//检测
		if(value != ""){
			if(ThisName.indexOf(value) > -1){
				resultVal = true;
			}
			markStr += LANG.UI_SETTING_VM_SEARCH+value;
		}
		markStr += '<a id="clearGFS" style="text-decoration: underline;margin-left: 10px;">'+LANG.UI_SEARCH_CLEAR+'</a>'
		$("#markStr").html(markStr);
		//添加清除点击事件
		$("#clearGFS").on('click',function(){
			var treeObj = $.fn.zTree.getZTreeObj("os_tree_data");
			//得到所有节点集合
			var Nownodes = treeObj.getNodes();
			// 如果搜到的是时间点 删除所有新搜索出来的时间点，避免异步加载不加载其他节点
			if(timepointFlag) {
				for(var i = 0;i<nodeParamList_1.length;i++) {
					if(nodeParamList_1[i].type == 2) {
						var delnode = zTree.getNodesByParam("id", nodeParamList_1[i].id, null)[0];
						if(delnode != null) {
							treeObj.removeChildNodes(delnode.getParentNode());
							delnode.getParentNode().isParent = true;
							zTree.updateNode(delnode.getParentNode()); 
						}
					}
				}
			}
			//得到所有隐藏的节点
			var nodes = treeObj.getNodesByParam("isHidden", true);
			//显示所有被隐藏的节点
			treeObj.showNodes(nodes);
			// treeObj.expandNode(nodes, true, true, true);
			//显示div
			$("#os_tree_data").show();
			$("#nopointtips").hide();
			$('#nosearchtips').hide();
			if(Nownodes.length ==0){
				$("#nopointtips").show();
			}
			$("#markStr").empty();
		})
	    if(resultVal || resultWeek || resultMonth || resultYear || resultForever){
	    	return true;
	    }else{
	    	return false;
	    }
		
		
	}
	
	
	
	//异步搜索时间点
	var searchSyncPoint = function(allNode){
		// 任务和虚拟机中没搜索到 去搜时间点
		var data = {}
		data.storage = $('#storageselect').val();
		data.search = $('#searchvm').val();
		data.week = $("input[name=weekFilter]").get(0).checked;
		data.month = $("input[name=monthFilter]").get(0).checked;
		data.year = $("input[name=yearFilter]").get(0).checked;
		data.forever = $("input[name=foreverFilter]").get(0).checked;
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#os_center_tree',animate: true});
//		getTimepointTree
		$.post(CONF.AJAXPATH, {m:CONF.M.OS,f:'searchTimepoint',p:data}, function(d) {
			Metronic.unblockUI('#os_center_tree');
			var pointNode = JSON.parse(d);
			zTree.hideNodes(allNode);
			var sortTimepoint = [];
			if(pointNode != null) {//时间点中搜到了，把搜到的节点加到nodeParamList中展示
				timepointFlag = true;
				nodeParamList_1=pointNode;
				var currentNode =  zTree.transformToArray(zTree.getNodes());
				var showNodes = [];
				for(var n in nodeParamList_1){
					if(nodeParamList_1[n].type == 2 ) {//3完备点所有父级show  4增量差异点
						sortTimepoint.push(nodeParamList_1[n]);//先把完备点放进去
						//找到父级节点（虚拟机）
						// showNodes.push(nodeParamList_1[n]);
						var vmnode = zTree.getNodesByParam("id", nodeParamList_1[n].pId, null)[0];
						//再找虚拟机父级节点（任务）
						if(vmnode != null){
							showNodes.push(vmnode);
							var tasknode = zTree.getNodesByParam("id", vmnode.pId, null)[0];
							if(tasknode != null) {
								showNodes.push(tasknode);
								var vmptnode = zTree.getNodesByParam("id", tasknode.pId, null)[0];
								if(vmptnode != null) {
									showNodes.push(vmptnode);
								}
							}
						}
					}
				}
				for(var n in nodeParamList_1) {
					if(nodeParamList_1[n].type == 3) {
						sortTimepoint.push(nodeParamList_1[n]);//先把增量点、差异点点放进去
					}
				}
				if(showNodes.length == 0) {
					$('#os_center_tree').hide();
					$('#nosearchtips').show();
					return;
				}
				showNodes = Array.from(new Set(showNodes));
				zTree.showNodes(showNodes);//展示搜索出的时间点的所有父节点
				for(var i =0; i<removeList.length; i++){
					for (let j = 0; j < currentNode.length; j++) {
						if(removeList[i].id == currentNode[j].id){
							zTree.removeNode(currentNode[j]);
						}
					}
				}
				removeList = [];
				//把时间点加到父节点中
				for(var i = 0;i<sortTimepoint.length;i++) {
					for(var j = 0; j<currentNode.length;j++) {
						if(sortTimepoint[i].pId == currentNode[j].id) {
	    					zTree.addNodes(currentNode[j], sortTimepoint[i], true);
	    					zTree.expandNode(currentNode[j], true);
							removeList.push(sortTimepoint[i]);
						}
					}
					currentNode =  zTree.transformToArray(zTree.getNodes());
				}
				$('#os_center_tree').show();
				$('#nosearchtips').hide();
			}else {//时间点中也没搜到
				$('#os_center_tree').hide();
				$('#nosearchtips').show();
				return;
				
			}
		});
	}
	
	
	//-----
	
	
	//批量删除所选时间点
	var deleteSelectPoint = function(){
		var node = zTree.getCheckedNodes(true);
		if(node.length == 0){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
			return;
		}
		// var nodeuuid = $('#nodeselect').val();
		timepointList = [];             //每次删除先清空时间点列表
		oslist = [];					//列表
		for(var i=0; i<node.length; i++){
			if(node[i].type == 1){
				if(!node[i].children){
					var osdata = {};
					osdata.taskuuid = node[i].taskuuid;
					osdata.ostype = node[i].osType;
//					osdata.nodeuuid = node[i].nodeuuid;
					// if(nodeuuid == "" || nodeuuid == undefined){
					// 	osdata.nodeuuid = "";
					// }else{
					// 	//如果选择了节点
					// 	osdata.nodeuuid = nodeuuid;
					// }
					osdata.nodeuuid = "";
					oslist.push(osdata);
				}
			}else if(node[i].type == 2){
				var data = {};
			    data.timepointuuid = node[i].timepointuuid;
			    data.nodeuuid = node[i].nodeuuid;
			    data.ostype = node[i].ostype;
			    timepointList.push(data);
			    if(node[i].isParent){
			    	var children = node[i].children;
			    	for(var j=0;j<children.length;j++){
			    		var data = {};
			    		data.timepointuuid = children[j].timepointuuid;
			    		data.nodeuuid = children[j].nodeuuid;
			    		data.ostype = children[j].ostype;
			    		timepointList.push(data);
			    	}
			    }
			}
		}
//		var timepointCount = timepointList.length;
//		var dbCount = dblist.length;
		
		//如果未选中，提示用户未勾选节点
		bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_CONFIRM_DELETE_TIPS1 + LANG.UI_DATA_CONFIRM_DELETE_TIPS2,
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
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
					        	$('.bootbox-input').after(des);
				        		initErrorFlag = true;
				        	}
				        	return false;
				        }
				    }
				});
            }
        });
	};
	
	//删除所选择的
	var submitSelectDelete = function(){
		var node = zTree.getCheckedNodes(true);
		var params = JSON.stringify({timepointlist:timepointList,oslist: oslist});	
		Metronic.blockUI({target: '.batchDeleteDiv',animate: true});
	    $.post(CONF.AJAXPATH,{m:34,f:'deleteSelectTimepoint',p:params},function(data){
	    	Metronic.unblockUI('.batchDeleteDiv');
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
	
	var submitDelete = function(button){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#datatable',animate: true});
		//发送到自己模块
    	$.post(CONF.AJAXPATH, {m:34,f:'deleteTimepoint',p:params}, function(data){
    		Metronic.unblockUI('#datatable');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			var d = JSON.parse(data);
    			if(0 == d.ext.count){
    				//重新刷新时间点数
    				var info = JSON.parse(params);
    				var idStr = info.timepointuuid;
    				var idStr = info.taskuuid+info.agentuuid;
    				//先刷新界面树
    				refreshTree(idStr);
    				var node = zTree.getNodesByParam("id", idStr, null);
    				//如果还有时间节点执行刷新树操作
    				if(node.length !=0){
    					getSyncVcenterInfo("os_tree_data", node[0], true, true);
    				}
    			}
    			
    		}
    	});
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
	 
	 
	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
		var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag){
			var storageuuid = $('#storageselect').val();
			var data = JSON.stringify({taskuuid:treeNode.taskuuid, agentuuid:treeNode.agentuuid,recoverflag:false,dataflag:true,
				refresh:refreshFlag, storageuuid: storageuuid, oschecked:treeNode.checked});
			var div = ".os_center_tree";
			Metronic.blockUI({target: div,animate: true});
			$.ajax({ 
				type: "post", 
		        url: CONF.AJAXPATH, 
		        async:true, 
		        data:{m:CONF.M.OS,f:'getSyncTimepoint',p:data},
		        success: function(data){ 
		        	Metronic.unblockUI(div);
		        	result = JSON.parse(data);
		        	if(result.re){
		        		//success
//		        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
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
	 
	 
	 
	 
	//如果把一个虚拟机下所有时间点删除后,需要把这个树上的虚拟机移除
	var refreshTree = function(nodeid){
		//获取更新节点
		var nodes = zTree.getNodesByParam("id", nodeid, null);
		nodes = nodes[0];
			zTree.removeChildNodes(nodes);
	}
	
	//删除父节点
	var removeParentNode = function(node){
		var parent = node.getParentNode();
		var childrenNum = parent.children.length;
		if(0 == childrenNum){
			zTree.removeNode(parent);
			removeParentNode(parent);
		}else{
			return;
		}
		
	}
	
	//标星统一处理
	var starHandler = function(params, funName,forever){
		Metronic.blockUI({target: '#setAllMark',animate: true});
		//发送到数据管理统一处理
		var paramsstr = JSON.stringify(params);
    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:funName,p:paramsstr}, function(data){
    		grid.getRefresh({});
    		Metronic.unblockUI('#setAllMark');
    		var d = JSON.parse(data);
    		
    		//如果失败
    		if(!d.re){
    			UIToastr.showInfo(LANG.UI_SETTING_VM_DATA_SET_MARK, LANG.UI_SETTING_VM_MARK_POINT_FAIL);
    		}else{
				$('#setAllMark').modal('hide');
				grid.getRefresh({});
				var row  = {};
				// row.importance_flag  = forever;
				// row.timepoint_uuid = params.uuid;
				editZtreeName(params.uuid,row.week,row.month,row.year,forever,_Remark);
			}
    	});
	}
	
	
	
    var handleRecords = function (taskuuid, agentuuid) {
    	task_uuid = taskuuid;
    	agent_uuid = agentuuid;
    	var storageuuid = $('#storageselect').val();
		if(!gridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [4,5,6,7]
	    			}],
	    			"order": [
	                    [0, "desc"]
	                ],
	    	};
			grid = new Datatable();
			var data = {m:CONF.M.OS,f:'getOsTimepointGrid',p:{taskuuid:taskuuid, agentuuid:agentuuid,storageuuid: storageuuid}};
			grid.setAjaxParam(data);
			grid.init({src: $("#datatable"), onDataLoad:addOpButton, dataTable:dataTableOpt});
	    	gridInitFlag = true;
	    	$('#tabletips').hide();
			$('#dbtable').show();
			$('#marktips').show();
		}else{
			//刷新表格
			grid.getRefresh({taskuuid:taskuuid, agentuuid:agentuuid,storageuuid: storageuuid}, undefined, true);
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
    
    return {
        //main function to initiate the module
        init: function () {
        	//初始化时间插件
			inintDatatimePicker();
        	// initPointShowType();
			initStorageShowType();
        	initTree();        	
        	initListener();
        	initUserPassword();
        }

    };

}();

jQuery(document).ready(function() {    
	OsData.init();
});