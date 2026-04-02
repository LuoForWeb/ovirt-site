var DbData = function () {
	var grid, gridInitFlag = false,nodeTypetreeInitFlag = false;
	var zTree,nodeParamList,timepointList, dblist; //节点树，节点搜索列表，时间点存放列表，数据库列表
	var db_uuid,agent_uuid, task_uuid, db_type;
	var _UserPassword;
	var initErrorFlag = false;
	var gfsList = {};//主要用于判断是否修改了GFS,如果没修改则不提交后台
	var _Remark = ""; //GFS标记用备注
	var showNode;//搜索到的时间点
	//时间选择器全局变量,方便提交搜索的时候直接使用
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	var setTree = function(zNodes){
		$("#database_tree").show();
		$("#nopointtips").hide();
		Metronic.unblockUI('#database_tree');
		if(zNodes == "[]"){
			$("#database_tree").hide();
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
					nameIsHTML:true
				}
			};
		zTree = $.fn.zTree.init($("#database_tree"), setting, JSON.parse(zNodes));
		nodeTypetreeInitFlag = true;
	};
	
	//点击节点，选中并展开
	var nodeSelect = function(treeId, treeNode, clickFlag){
		let dbType = parseInt(treeNode.dbtype);
		if(treeNode.type == 1){
			$('#dbdataUrl').text('');
			var parent = treeNode.getParentNode();
			var info = parent.name + "---" + treeNode.name;
			$('#dbdataUrl').append(info);
			handleRecords(treeNode.dbuuid, treeNode.agentuuid, treeNode.taskuuid, treeNode.createtime, dbType); //录入备份数据列表
		}
		if(treeNode.type ==  4){
			if (
				CONF.DB_TYPE.MONGODB !== dbType ||
				CONF.DB_TYPE.CACHE !== dbType ||
				CONF.DB_TYPE.IRIS !== dbType
			) {
				UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_TIMEPOINT,LANG.UI_DATA_BATCH_DELETE_TIMEPOINT_TIPS);
			}
		}
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
		var nodeuuid = $('#nodeselect').val();
		var data = JSON.stringify({taskuuid:treeNode.taskuuid, dbuuid: treeNode.dbuuid, dbtype: treeNode.dbtype, agentuuid: treeNode.agentuuid,
			refresh:refreshFlag,disabledflag: true,manageflag: true, dbcheck: treeNode.checked, nodeuuid: nodeuuid});
		var div = ".vcenter-tree";
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.DBPROTECT,f:'getSyncTimepoint',p:data},
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
				if(allNodes[j].type == 3 && allNodes[j].isParent && allNodes[j].checked){
					var children = allNodes[j].children;
				    for(var i=0;i<children.length;i++){
						let dbType = parseInt(children[i].dbtype);
						zTree.setChkDisabled(children[i], false);  // 这里设置为false是为了勾选
				    	if (children[i].dbtype == node.dbtype) {
				    		zTree.checkNode(children[i], true, true);
				    	} else {
				    		zTree.checkNode(children[i], false, true);
				    	}
						if (
							CONF.DB_TYPE.MONGODB !== dbType &&  // 不可勾选
							CONF.DB_TYPE.CACHE !== dbType &&
							CONF.DB_TYPE.IRIS !== dbType
						) {
							zTree.setChkDisabled(children[i], true);
						}
				    }
				}
			}
			for(var j=0;j<allNodes.length;j++){  // 取消勾选其他类别的数据库
				if(allNodes[j].dbtype != node.dbtype){
					//清空所有时间点
					zTree.checkAllNodes(false);
					zTree.checkNode(node, false, true, false);
					UIToastr.showInfo(LANG.UI_DB_DATA_SELECT_DIFF_TYPE, LANG.UI_DB_DATA_SELECT_DIFF_TYPE_TIPS);
					return;
				}
			}
		}else if(node.type <= 3){
			var allChildren = getAllChildren(node,[]);
			for(var j=0;j<allChildren.length;j++){
				if(allChildren[j].type == 4){
					let dbType = parseInt(allChildren[j].dbtype);
					zTree.setChkDisabled(allChildren[j], false);
					zTree.checkNode(allChildren[j],false,true);
					if (
						CONF.DB_TYPE.MONGODB !== dbType &&  // 不可勾选
						CONF.DB_TYPE.CACHE !== dbType &&
						CONF.DB_TYPE.IRIS !== dbType
					) {
						zTree.setChkDisabled(allChildren[j],true)
					}
				}
		    }
		}
	}
	
	//初始化数据库节点树
	var initTree = function() {	
		var data = {}
		data.node = $('#nodeselect').val();
		data.showtype = 1;
		data.manageflag = true;  //得到备份数据管理,获取checkbox
		data.disabledflag = true; //禁用增量和差异勾选
		data.dataflag = true; //备份数据标志
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#database_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.DBPROTECT,f:'getTimepointTree',p:data}, setTree);
	}
	
	//初始化时间点展示方式和事件
	var initPointShowType = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getDBTimepointAllNode',p:{}}, function(d){
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
	
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		var opDiv = $('tbody > tr').find('td:eq(7)');
		// var starDiv = $('tbody > tr').find('td:eq(8)');
		for(var i=0; i<data.length; i++){
			opButton(opDiv[i], data[i][7], i);
		}
		
		$('.remarktips').popover();	   //初始化tips
	}
	
	
	//添加操作按钮
	var opButton = function(div, opCode, rowNum,otherMsg){
		// var mode = otherMsg['mode'];
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
					case 3:
						button += '<li class="setmark"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> ' + LANG.UI_OS_DATA_SET_MARK + '</a></li>';
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
		$('.setmark').unbind().on('click', setMark);
	}

	var setMark = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		_Remark = params.remark;

		///得到GFS保留标记
		gfsList = {};
		// gfsList.week = params.weekly_flag;	//周
		// gfsList.month = params.monthly_flag;//月
		// gfsList.year = params.yearly_flag;	//年
		gfsList.forever = params.importance_flag;//永久保留
		//初始化清空所有勾选项
		// $("input[name=weekCheck]").iCheck('uncheck');
		// $("input[name=monthCheck]").iCheck('uncheck');
		// $("input[name=yearCheck]").iCheck('uncheck');
		$("input[name=foreverCheck]").iCheck('uncheck');
		//设置为只有完全备份才可GFS
		// if(params['mode'] != 1){
		// 	$("input[name=weekCheck]").iCheck('disable');
		// 	$("input[name=monthCheck]").iCheck('disable');
		// 	$("input[name=yearCheck]").iCheck('disable');
		// 	if(params['hypervisor'] == 2){
		// 		//如果为hyper-v的增备差异也不能设置永久标记点
		// 		$("input[name=foreverCheck]").iCheck('disable');
		// 	}else{
		// 		$("input[name=foreverCheck]").iCheck('enable');
		// 	}
		// }else{
		// 	$("input[name=weekCheck]").iCheck('enable');
		// 	$("input[name=monthCheck]").iCheck('enable');
		// 	$("input[name=yearCheck]").iCheck('enable');
		// 	$("input[name=foreverCheck]").iCheck('enable');
		// }
		$("input[name=foreverCheck]").iCheck('enable');


		$("#Marktimepoint_uuid").val("");
		$("#MarktimepointNodeUUID").val("");
		$("#Marksubmode_type").val("");
		//设置值
		$("#Marktimepoint_uuid").val(params['uuid']);
		$("#MarktimepointNodeUUID").val(params['nodeuuid']);
		$("#Marksubmode_type").val(params['hypervisor']);
		//设置勾选
		// if(week){
		// 	$("input[name=weekCheck]").iCheck('check');
		// }
		// if(month){
		// 	$("input[name=monthCheck]").iCheck('check');
		// }
		// if(year){
		// 	$("input[name=yearCheck]").iCheck('check');
		// }
		if(gfsList.forever){
			$("input[name=foreverCheck]").iCheck('check');
		}

		$('#setAllMark').modal('show');
	}
	
	//添加备注
	var remarkPoint = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		var value = params['remark'];
		bootbox.prompt({
            title: LANG.UI_DATA_ADD_REMARK,
            value:value,
            callback: function(result) {
				if(!reXssEncode(result)) {
					return false;
				}
            	if(result == null || $.trim(result) == value) return;
        		submitRemark($.trim(result), params);
            }
        });
	}
	
	var submitRemark = function(remark, params){
		params.remark = remark;
		var data = JSON.stringify(params);
		Metronic.blockUI({target: '#datatable',animate: true});
		//发送到数据管理统一处理
    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:'remarkTimepoint',p:data}, function(d){
    		Metronic.unblockUI('#datatable');
    		if(OPREL(d)){
    			grid.getRefresh({});
				//更新树节点
				var treeObj = $.fn.zTree.getZTreeObj("database_tree");
				//根据timeUUID得到ztree的node数据，没有搜到返回null
				var node = treeObj.getNodeByParam('timepointuuid', params.uuid, null);
				editZtreeName(params.uuid,params.importance_flag,remark);
    		}
    	});
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

	/**
	 * NOTE: 封装getDbTimepointGrid参数，有三个地方复用
	 * @param {object} search - 搜索内容
	 * @param {boolean} accurateFlag - 是否为高级搜索
	 * @param {string} node_uuid - 节点uuid
	 * @returns {{search, nodeuuid, accurateFlag, start: number, length: number, dbtype, dbuuid, agentuuid, taskuuid}}
	 */
	var getDbTimepointGridParams = function (search, accurateFlag, node_uuid) {
		return {
			start: 0,
			length: 10,
			search: search,
			accurateFlag: accurateFlag,
			dbuuid:db_uuid,
			agentuuid:agent_uuid,
			taskuuid:task_uuid,
			nodeuuid: node_uuid,
			dbtype: db_type,
		};
	}
	
	//初始化监听事件
	var initListener = function(){
		$('#allDelete').on('click', deleteSelectPoint);
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
		$("#mark_submit").on('click',function(){
			markSubmit();
		});
		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'300px'});
		});

		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			var p = {};
			p.startTime = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.timepointType = $('#searchmodal #timepointType').val();
			p.backupType = $('#searchmodal #timepointType').find(':selected').attr('data-type');  // 追加备份类型, 因为日志备份和归档日志备份的值是一样的
			// p.starFlag = $('#searchmodal #starFlag').get(0).checked;

			// p.week = boolToInt($("input[name=weekCheck1]").get(0).checked);
			// p.month = boolToInt($("input[name=monthCheck1]").get(0).checked);
			// p.year = boolToInt($("input[name=yearCheck1]").get(0).checked);
			// p.forever = boolToInt($("input[name=foreverCheck1]").get(0).checked);



			var nodeuuid = $('#nodeselect').val();
			var params = getDbTimepointGridParams(p, true, nodeuuid);
			var data = {m:CONF.M.DBPROTECT,f:'getDbTimepointGrid',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			grid.getRefresh(params, undefined, true);
		});
		
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
		// if(p.week != "2"){
		// 	info += '<span id="week" title="' + 'GFS保留周标记点' + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + 'GFS保留周标记点' + '</i><em>X</em></span>';
		// }
		// if(p.month != "2"){
		// 	info += '<span id="month" title="' + 'GFS保留月标记点' + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + 'GFS保留月标记点' + '</i><em>X</em></span>';
		// }
		// if(p.year != "2"){
		// 	info += '<span id="year" title="' + 'GFS保留年标记点' + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + 'GFS保留年标记点' + '</i><em>X</em></span>';
		// }
		// if(p.forever != "2"){
		// 	info += '<span id="forever" title="' + '永久标记点' + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + '永久标记点' + '</i><em>X</em></span>';
		// }

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
			var params = getDbTimepointGridParams(p, true, nodeuuid);
			// var params = {start:0, length:10, search: p, accurateFlag: true, vmuuid:vm_uuid, vcenteruuid:vcenter_uuid, taskuuid:task_uuid, nodeuuid: nodeuuid};
			var data = {m:CONF.M.DBPROTECT,f:'getDbTimepointGrid',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);

		});
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			var nodeuuid = $('#nodeselect').val();
			var params = getDbTimepointGridParams(p, false, nodeuuid);
			// var params = {start:0, length:10, search: {}, accurateFlag: false, vmuuid:vm_uuid, vcenteruuid:vcenter_uuid, taskuuid:task_uuid, nodeuuid: nodeuuid};
			var data = {m:CONF.M.DBPROTECT,f:'getDbTimepointGrid',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();
		}
	}

	var markSubmit = function(){
		//先得到所有信息
		// var week = $("input[name=weekCheck]").get(0).checked;
		// var month = $("input[name=monthCheck]").get(0).checked;
		// var year = $("input[name=yearCheck]").get(0).checked;
		var forever = $("input[name=foreverCheck]").get(0).checked;
		//得到信息后先判断是否有做修改 如果没有做修改则不提交后台
		if( gfsList.forever == forever){
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
		// if(gfsList.week != week){
		// 	var info = {
		// 		'level1_type': 1,
		// 		'flag': boolToInt(week)
		//
		// 	};
		// 	item_list.push(info);
		// }
		// //月保留标记
		// if(gfsList.month != month){
		// 	var info = {
		// 		'level1_type': 2,
		// 		'flag': boolToInt(month)
		//
		// 	};
		// 	item_list.push(info);
		// }
		// //年保留标记
		// if(gfsList.year != year){
		// 	var info = {
		// 		'level1_type': 3,
		// 		'flag': boolToInt(year)
		//
		// 	};
		// 	item_list.push(info);
		// }

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

		//组装参数发往后台
		// var paramsMark = {};
		// paramsMark.timepoint_uuid = uuid;
		// paramsMark.item_list = item_list;
		// paramsMark.nodeuuid = $("#MarktimepointNodeUUID").val();
		// paramsMark.submode_type = $("#Marksubmode_type").val();
		// var p = JSON.stringify(paramsMark);
		//
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
		//
		// });


	}

	//静态修改Ztree的名称
	var editZtreeName = function(timepointUUID,$Fflag, remark){
		var treeObj = $.fn.zTree.getZTreeObj("database_tree");
		//根据timeUUID得到ztree的node数据，没有搜到返回null
		var node = treeObj.getNodeByParam('timepointuuid', timepointUUID, null);
		//获得原来的name
		var old_name = node.oldname;
		var markStr = '';
		// if($Wflag){
		// 	markStr +='<span title="'+LANG.UI_SETTING_VM_GFS_WEEK_POINT+'" class="viconfont vicon-remark-week"></span>';
		// }
		// if($Mflag){
		// 	markStr +='<span title="'+LANG.UI_SETTING_VM_GFS_MONTH_POINT+'" class="viconfont vicon-remark-month"></span>';
		// }
		// if($Yflag){
		// 	markStr +='<span title="'+LANG.UI_SETTING_VM_GFS_YEAR_POINT+'" class="viconfont vicon-remark-year"></span>';
		// }
		if($Fflag){
			markStr +='<span title="'+LANG.UI_SETTING_VM_GFS_FOREVER_POINT+'" class="viconfont vicon-remark-forever"></span>';
		}
		var new_name = old_name+" "+markStr;

		node.name = new_name;
		treeObj.updateNode(node);
		//清除已存在的备注
		$('#remark_'+timepointUUID).remove();
	}
	
	//批量删除所选时间点
	var deleteSelectPoint = function(){
		var node = zTree.getCheckedNodes(true);
		if(node.length == 0){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
			return;
		}
		timepointList = [];             //每次删除先清空时间点列表
		dblist = [];					//列表
		for(var i=0; i<node.length; i++){
			if(node[i].type == 1){
				if(!node[i].children || !node[i].children.length){
					var dbdata = {};
					dbdata.dbuuid = node[i].dbuuid;
					dbdata.nodeuuid = node[i].nodeuuid;
					dbdata.dbtype = node[i].dbtype;
					dbdata.taskuuid = node[i].taskuuid;
					dblist.push(dbdata);
				}
			}else if(node[i].type == 3){
				var data = {};
			    data.timepointuuid = node[i].timepointuuid;
			    data.nodeuuid = node[i].nodeuuid;
			    data.dbtype = node[i].dbtype;
			    timepointList.push(data);
			    if(node[i].isParent){
			    	var children = node[i].children;
			    	for(var j=0;j<children.length;j++){
			    		var data = {};
			    		data.timepointuuid = children[j].timepointuuid;
			    		data.nodeuuid = children[j].nodeuuid;
			    		data.dbtype = children[j].dbtype;
			    		timepointList.push(data);
			    	}
			    }
			}
		}
		var timepointCount = timepointList.length;
		var dbCount = dblist.length;
		
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
		var nodeuuid = $('#nodeselect').val();
		var params = JSON.stringify({timepointlist:timepointList,dblist: dblist, nodeuuid: nodeuuid});	
		Metronic.blockUI({target: '.batchDeleteDiv',animate: true});
	    $.post(CONF.AJAXPATH,{m:CONF.M.DBPROTECT,f:'deleteDBSelectTimepoint',p:params},function(data){
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
		var params = data[row][8];
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#datatable',animate: true});
		//发送到自己模块
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBPROTECT,f:'deleteDBTimepoint',p:params}, function(data){
    		Metronic.unblockUI('#datatable');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			var d = JSON.parse(data);
    			if(0 == d.ext.count){
    				refreshTree(d.ext.id);
    			}
    		}
    	});
	}

	//定位搜索时间节点
	var searchVM = function(){
		if($.trim($('#searchvm').val()) == "" && !$("input[name=foreverFilter]").get(0).checked) {
			$("#markStr").empty(); // 隐藏筛选条件
			$('#nosearchtips').hide(); // 隐藏没有搜索搭配结果的提示
			zTree.destroy(); // 清空树
			// 重新加载树
			initTree();
			return;
		}
		nodeParamList = "";
		nodeParamList = zTree.getNodesByFilter(filterZtree);
		//搜索时间点
		var data = {}
		data.node = $('#nodeselect').val();
		data.search = $('#searchvm').val();
		data.forever = $("input[name=foreverFilter]").get(0).checked;
		data = JSON.stringify(data);
		Metronic.blockUI({target:'#database_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.DBPROTECT,f:'searchTimepoint',p:data}, function(d) {
			Metronic.unblockUI('#database_tree');
			var pointNode = JSON.parse(d);
			showNode = [];

            // 隐藏错误提示
            $('#nosearchtips').hide();
            // 在搜索之前将所有时间点节点移除
            var allNodes = zTree.transformToArray(zTree.getNodes());
            for (const node of allNodes) {
                if (node.timepointuuid !== undefined && node.timepointuuid) {
                    zTree.removeNode(node);
                }
            }
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
						}
					});
					showNode.forEach(item => {
						if(item.type == 4) {//增量差异等
							var parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
							zTree.addNodes(parentNode, item, true);
						}
					});
				}
			}
			searchByTree();
		});
	}

	// 树模糊搜索, 去除html标签, 返回所有子树
	var fuzzySearch = function (key, value, tree) {
		value = value.toLowerCase();
		var allNodes = tree.transformToArray(tree.getNodes());
		var retNodes = [];
		var nodeIds = [];
		for (const node of allNodes) {
			if (nodeIds.includes(node.id)) {
				continue;
			}
			var match_value = node[key].replace(/<.*?>/ig, '').toLowerCase();  // 去除html标签
			if (match_value.indexOf(value) > -1) {
				retNodes.push(node);
				nodeIds.push(node.id);
				// 这里需要将子树加入到列表里面, 因为永久备份点是在时间点节点上筛选的
                var children = tree.getNodesByFilter(() => {
					return true;
				}, false, node);
				for (const child of children) {
					if (nodeIds.includes(child.id)) {
						continue;
					}
					nodeIds.push(child.id);
					retNodes.push(child);
				}
			}
		}
		return retNodes;
	}

	//在当前树用ztree方法搜索
	var searchByTree = function() {
		var keyword = $.trim($('#searchvm').val());
		var allNodes = zTree.transformToArray(zTree.getNodes());
		zTree.hideNodes(allNodes);    //当开始搜索时，先将所有节点隐藏
		var nodeList = fuzzySearch('name', keyword, zTree); // 模糊搜索
		//搜gfs标记点
		if($("input[name=foreverFilter]").get(0).checked) {
			keyword = "viconfont vicon-remark-forever";
			var nodeList_mark = zTree.getNodesByParamFuzzy('name', keyword, 0);
			// 取两个数组的交集
			nodeList = nodeList_mark.filter(item => nodeList.includes(item));
		}
		if(nodeList.length == 0) {
			$('#nosearchtips').show();
			return;
		}
		var arr = [];
		for(var i=0; i<nodeList.length; i++){
			arr = $.merge(arr,nodeList[i].getPath());    //找出节点的所有父节点（包括自己）
			if(nodeList[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr,nodeList[i].children);
			}
		}
		zTree.showNodes($.unique(arr));    //显示所有要求的节点及其路径节点
		zTree.checkAllNodes(false);  // 取消选中
		//展开显示的所有节点
		arr.forEach(item => {
			if(item.children) {
				if (item.timepointuuid !== undefined && item.timepointuuid) {
					// 完备点的折叠和展开处理
					if (!keyword) {
						return;
					}
					item.children.map(child => {
						let match_value = child.name.replace(/<.*?>/ig, '').toLowerCase()
						if (match_value.indexOf(keyword.toLowerCase()) > -1) { // 如果搜索到完备点下面的节点, 需要展开完备点
							zTree.expandNode(item, true);
						}
					});
					return;
				}
				zTree.expandNode(item, true);
			}
		});
		$('#database_tree')[0].scrollTop = '0px'; //滚动条保持在顶部
	}

	//如果把一个虚拟机下所有时间点删除后,需要把这个树上的虚拟机移除
	var refreshTree = function(nodeid){
		var nodes = zTree.getNodesByParam("id", nodeid, null);
		if(0 == nodes.length){
			return;
		}
		//要删除的节点
		var vmNode = nodes[0];
		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		var parent = vmNode.getParentNode();
		if(!parent) return;
		var childrenNum = parent.children.length;
		if(1 == childrenNum){
			zTree.removeNode(parent);
			removeParentNode(parent);
		}else{
			zTree.removeNode(vmNode);
		}
		
		
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
	
	//添加星标
	// var addStar = function(){
	// 	starHandler(this, 'addStar');
	// }
	// //取消星标
	// var deleteStar = function(){
	// 	starHandler(this, 'deleteStar');
	// }
	
	//标星统一处理
	var starHandler = function(params, funName){
		var uuid = $("#Marktimepoint_uuid").val();
		var forever = $("input[name=foreverCheck]").get(0).checked;
		Metronic.blockUI({target: '#setAllMark',animate: true});
		//发送到数据管理统一处理
		$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:funName,p:params}, function(data){
			Metronic.unblockUI('#setAllMark');
			var d = JSON.parse(data)
			//如果失败
			if(!d.re){
				UIToastr.showInfo(LANG.UI_OS_DATA_SET_MARK, LANG.UI_SETTING_VM_MARK_POINT_FAIL);
			}else{
				OPREL(data);
				$('#setAllMark').modal('hide');
				grid.getRefresh({});
				editZtreeName(uuid,forever,_Remark);
				// editZtreeName(uuid,params,_Remark);
			}

		});
	}
	
	
	
    var handleRecords = function (dbuuid, agentuuid, taskuuid, createtime, dbtype) {
    	var nodeuuid = $('#nodeselect').val();
		db_uuid = dbuuid;
		agent_uuid = agentuuid;
		task_uuid = taskuuid;
		db_type = dbtype;

		var params = {
			dbuuid: dbuuid,
			agentuuid: agentuuid,
			taskuuid: taskuuid,
			createtime: createtime,
			nodeuuid: nodeuuid,
			dbtype: db_type,
		};
		if(!gridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 5, 6, 7]
	    			}],
	    			"order": [
	                    [1, "desc"]
	                ],
	    	};
			grid = new Datatable();
			var data = {m:CONF.M.DBPROTECT,f:'getDbTimepointGrid',p:params};
			grid.setAjaxParam(data);
			grid.init({src: $("#datatable"), onDataLoad:addOpButton, dataTable:dataTableOpt});
	    	gridInitFlag = true;
	    	$('#tabletips').hide();
			$('#dbtable').show();
			$('#marktips').show();
		}else{
			//刷新表格
			grid.getRefresh(params, undefined, true);
		}
    	return;
    }

    //初始化日期选择插件
    var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepickerDbData').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),												//默认结束时间
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
		$('#daterangepickerDbData').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerDbData').on('cancel.daterangepicker', function(ev, picker) {
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
    
    //初始化当前用户密码用于删除二次确认
	var initUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
	
	//ztree的复杂过滤
	var filterZtree = function(node){
		var markStr = LANG.UI_SETTING_VM_DATA_SCREEN+": ";
		var resultVal = false;
		var resultForever = false;
		
		//获得数据
		var value = $('#searchvm').val();
		var forever = $("input[name=foreverFilter]").get(0).checked;
		var ThisName = node.name;
		if(value == "" && !forever){
			$("#markStr").empty();
			return true;
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
			markStr += value;
		}
		markStr += '<a id="clearGFS" style="text-decoration: underline;margin-left: 10px;">'+LANG.UI_SEARCH_CLEAR+'</a>'
		$("#markStr").html(markStr);
		//添加清除点击事件
		$("#clearGFS").on('click',function(){
			$("#markStr").empty(); // 隐藏筛选条件
			$('#nosearchtips').hide(); // 隐藏没有搜索搭配结果的提示
			zTree.destroy(); // 清空树
			// 重新加载树
			initTree();
		})
	    if(resultVal || resultForever){
	    	return true;
	    }else{
	    	return false;
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
	DbData.init();
});