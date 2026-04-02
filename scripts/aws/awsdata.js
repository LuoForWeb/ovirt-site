var AWSData = function () {
	var grid = $('#datatable'), gridInitFlag = false,nodeTypetreeInitFlag = false;
	var zTree,nodeParamList,timepointList, vmlist; //节点树，节点搜索列表，时间点存放列表，虚拟机列表
	var vm_uuid,vcenter_uuid, task_uuid;
	var _UserPassword;
	var initErrorFlag = false;
	var gfsList = {};//主要用于判断是否修改了GFS,如果没修改则不提交后台
	var _Remark = ""; //GFS标记用备注
	var timepointFlag = false;//是否搜到过时间点
	var removeList = [];
	//时间选择器全局变量,方便提交搜索的时候直接使用
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	var showNode;//搜索到的时间点
	var setTree = function(zNodes){
		$("#vcenter_tree").show();
		$("#nopointtips").hide();
		Metronic.unblockUI('#vcenter_tree');
		if(!zNodes.length){
			$("#vcenter_tree").hide();
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
		zTree = $.fn.zTree.init($("#vcenter_tree"), setting, zNodes);
		nodeTypetreeInitFlag = true;
	};
	
	//点击节点，选中并展开
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(treeNode.type == 1){
			$('#vmdataUrl').text('');
			$('.searchContent').text('');
			var parent = treeNode.getParentNode();
			var info = parent.name + "---" + treeNode.name;
			$('#vmdataUrl').append(info);
			let storage_uuid = $('#storageselect').val(); // 按存储筛选
			handleRecords(treeNode.vm_uuid, treeNode.platform_uuid, treeNode.task_uuid, storage_uuid); //录入备份数据列表
		}
		if(treeNode.type ==  4){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_TIMEPOINT,LANG.UI_DATA_BATCH_DELETE_TIMEPOINT_TIPS);
		}
		//展开不需要选中
//		zTree.checkNode(treeNode, !treeNode.checked, true, true);
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
	
	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var nodeuuid = $('#nodeselect').val();
		var storageuuid = $('#storageselect').val();
		if (null === nodeuuid) {
			nodeuuid = '';
		}
		var div = ".vcenter-tree";
		Metronic.blockUI({target: div,animate: true});
		let p = {
			task_uuid: treeNode.task_uuid,
			vm_uuid: treeNode.vm_uuid,
			hypervisor_type: treeNode.hypervisor_type,
			disabled_flag: true, //备份数据禁用勾选增量差异标志
			manage_flag: true,
			vm_check: treeNode.checked,
			node_uuid: nodeuuid,
			storage_uuid: storageuuid
		};
		pAjaxRequest(p, "/api/v1/vm/restore_data/restore_points", "GET", function (d) {
			Metronic.unblockUI(div);
			if(d.success){
				//success
				$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
				if(expendFlag == true){
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
				}
			}
		}, false);
	}
	
	//得到当前节点的所有子节点
	var getAllChildren = function(node, allNode){
		
		if(node.isParent){
			var children = node.children;
			if(children){
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
				if(allNodes[j].type == 3 &&allNodes[j].isParent && allNodes[j].checked){
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
				if(allChildren[j].type == 4){
			        zTree.setChkDisabled(allChildren[j], false);
			        zTree.checkNode(allChildren[j],false,true);
			        zTree.setChkDisabled(allChildren[j],true);
			    }
		    }
		}
	}
	
	//初始化虚拟机节点树
	var initTree = function() {
		let p = {};
		p.show_type = 1;//按实例
		// p.node_uuid = $('#nodeselect').val(); //按节点筛选
		p.storage_uuid = $('#storageselect').val(); // 按存储筛选
		if (null === p.node_uuid) {
			p.node_uuid = '';
		}
		p.manage_flag = true; //得到备份数据管理,获取checkbox
		p.data_flag = true; //备份数据标志
		p.sub_module_type = 3; // 公有云获取标志
		pAjaxRequest(p, "/api/v1/vm/restore_data", "GET", function (d) {
			setTree(d.data.info);
		}, false);
	}
	
	//初始化时间点展示方式和事件
	var initStorageShowType =  function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getStorageType',p:JSON.stringify({backupDataFlag: true})}, function(d){
			var data = JSON.parse(d);
			// console.log("data----",data);
			var stroageselect = $('#storageselect')
			stroageselect.empty();
			for(var i  = 0;i<data.length;i++){
				var option =  $("<option>").text(data[i].text).val(data[i].storageid);
				stroageselect.append(option);
			}
		});
		//绑定事件
		$('#storageselect').on('change', storageselectChange);
	}
	//存储选择改变事件
	var storageselectChange  = function(){
		$("#markStr").empty();
		$('#nosearchtips').hide();
		nodeTypetreeInitFlag = false;
		initTree();
	}
	
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		var opDiv = $('tbody > tr').find('td:eq(6)');
		var storageDiv = $('tbody > tr').find('td:eq(5)');
		for(var i=0; i<data.length; i++){
			opButton(opDiv[i], data[i][6], i);
			if (undefined == data[i][7].hypervisor) {
				//虚拟机不使用插槽样式
				// opStorage(storageDiv[i], data[i][5], i);
			}
		}
		$('.remarktips').popover();	   //初始化tips
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
	
	//设置时间点保留标记
	var setMark = function(rowData){
		var params = rowData.detail;
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
		if(params.backup_mode != 1){
			$("input[name=weekCheck]").iCheck('disable');
			$("input[name=monthCheck]").iCheck('disable');
			$("input[name=yearCheck]").iCheck('disable');
			$("input[name=foreverCheck]").iCheck('enable');
		}else{
			$("input[name=weekCheck]").iCheck('enable');
			$("input[name=monthCheck]").iCheck('enable');
			$("input[name=yearCheck]").iCheck('enable');
			$("input[name=foreverCheck]").iCheck('enable');
		}
		
		//设置值
		$("#Marktimepoint_uuid").val(params.timepoint_uuid);
		$("#MarktimepointNodeUUID").val(params.node_uuid);
		$("#Marksubmode_type").val(params.hypervisor_type);
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
		var paramsMark = {};
		paramsMark.timepoint_uuid = uuid;
		paramsMark.item_list = item_list;
		paramsMark.node_uuid = $("#MarktimepointNodeUUID").val();
		paramsMark.hypervisor_type = $("#Marksubmode_type").val();
		
		//设置GFS保留标记
		//发送到数据管理统一处理
		Metronic.blockUI({target: '#setAllMark',animate: true});
    	pAjaxRequest(paramsMark, "/api/v1/vm/restore_points/gfs_mark", "POST", function (d) {
			Metronic.unblockUI('#setAllMark');
			if (d.success) {
				UIToastr.showSuccess(LANG.UI_DATA_AWS_SET_MARK, LANG.UI_DATA_AWS_SET_MARK_SUCCESS);
				$('#setAllMark').modal('hide');
				grid.bootstrapTable('refresh');
				editZtreeName(uuid,week,month,year,forever,_Remark);
			} else {
				UIToastr.showWarning(LANG.UI_DATA_AWS_SET_MARK, LANG.UI_DATA_AWS_SET_MARK_FAILED);
			}
		}, true);
		
	}
	
	//静态修改Ztree的名称
	var editZtreeName = function(timepointUUID,$Wflag,$Mflag,$Yflag,$Fflag, remark){
		var treeObj = $.fn.zTree.getZTreeObj("vcenter_tree");
		//根据timeUUID得到ztree的node数据，没有搜到返回null
		var node = treeObj.getNodeByParam('timepoint_uuid', timepointUUID, null);
		//获得原来的name
		var old_name = node.old_name;
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
	
	//布尔类型转成int1和2
	var boolToInt = function(thisbool){
		if(thisbool){
			return 1;
		}else{
			return 2;
		}
		
	}
	
	//添加备注 
	var remarkPoint = function(rowData){
		var params = {timepoint_uuid: rowData.timepoint_uuid};
		var value = rowData.detail.remark;
		bootbox.prompt({
            title: LANG.UI_DATA_ADD_REMARK,
            value:value,
            callback: function(result) {
            	if(result == null || $.trim(result) == value) return;
                //if (result !== null) {
//            	if($.trim(result)){
        		submitRemark($.trim(result), params, rowData.detail);
                //}
            }
        });
	}
	
	var submitRemark = function(remark, params, detail){
		params.remark = remark;
		Metronic.blockUI({target: '#datatable',animate: true});
    	pAjaxRequest(params, "/api/v1/vm/restore_points/remark", "POST", function (d) {
			Metronic.unblockUI('#datatable');
			if (operateResponseList(d, LANG.UI_DATA_ADD_REMARK)) {
				grid.bootstrapTable('refresh');
				//更新树节点
				// var treeObj = $.fn.zTree.getZTreeObj("vcenter_tree");
				// //根据timeUUID得到ztree的node数据，没有搜到返回null
				// var node = treeObj.getNodeByParam('timepointuuid', detail.vm_uuid, null);
				editZtreeName(params.timepoint_uuid, detail.weekly_flag, detail.monthly_flag, detail.yearly_flag, detail.importance_flag, remark);
			}
		}, true);
	}
	
	//删除时间点
	var deletePoint = function(rowData){
		if (CONF.BD_STORAGE_TYPE.TAPE === parseInt(rowData.storage_type)) {
			// 时间点所在存储是磁带则无法删除
			UIToastr.showWarning(LANG.UI_DATA_DELETE_TIMEPOINT, LANG.UI_TAPE_DELETE_BACKUP_POINT_TIPS);
			return;
		}
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
				        	submitDelete(rowData);
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
	
	//初始化监听事件
	var initListener = function(){
		$('#allDelete').on('click', deleteSelectPoint);
		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				$('#searchmodal').modal({'width':'800px', 'height':'300px'});
			}else{
				$('#searchmodal').modal({'width':'830px', 'height':'300px'});
			}
		});
		
		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			var nodeuuid = $('#nodeselect').val();
			var p = {};
			p.accurate_flag = true;
			p.start_time = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.end_time = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.backup_mode = $('#searchmodal #timepointType').val();
			
			p.week_flag = boolToInt($("input[name=weekCheck1]").get(0).checked);
			p.month_flag = boolToInt($("input[name=monthCheck1]").get(0).checked);
			p.year_flag = boolToInt($("input[name=yearCheck1]").get(0).checked);
			p.forever_flag = boolToInt($("input[name=foreverCheck1]").get(0).checked);

			p.offset = 0;
			p.limit = 20;
			p.sort = 'timepoint';
			p.order = 'desc';
			p.vm_uuid = vm_uuid;
			p.platform_uuid = vcenter_uuid;
			p.task_uuid = task_uuid;

			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return $.extend(queryParams, p);
				}
			});
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
		});
		$('.icheck').iCheck({
    	    checkboxClass: 'icheckbox_square-blue',
    	    radioClass: 'iradio_square-blue'
      });
		
		//设置GFS标记
		$("#mark_submit").on('click',function(){
			markSubmit();
		})
		
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

		// 关闭右侧提示
		$('#mark_tips_close').on('click', () => {
			$('.tabledata-wrapper__content__table .table-container').css('height', 'calc(100% - 60px)');
		})
	}
	
	//筛选清空
	var initSearch = function(){
		//清空筛选项
		$('#searchvm').val("");
		$("input[name=weekFilter]").iCheck('uncheck');
		$("input[name=monthFilter]").iCheck('uncheck');
		$("input[name=yearFilter]").iCheck('uncheck');
		$("input[name=foreverFilter]").iCheck('uncheck');
		searchVM();
	}
	
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		if(p.start_time && p.end_time){
			info += '<span id="time" title="' + p.start_time + "~" + p.end_time + '"> '+ LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.start_time + "~" + p.end_time + '</i><em>X</em></span>';
		}
		if(p.backup_mode != "0"){
			info += '<span id="backup_mode" title="' + $('#timepointType').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TYPE +': <i>' + $('#timepointType').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.week_flag != "2"){
			info += '<span id="week_flag" title="' + LANG.UI_SETTING_VM_GFS_WEEK_POINT + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_SETTING_VM_GFS_WEEK_POINT + '</i><em>X</em></span>';
		}
		if(p.month_flag != "2"){
			info += '<span id="month_flag" title="' + LANG.UI_SETTING_VM_GFS_MONTH_POINT + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_SETTING_VM_GFS_MONTH_POINT + '</i><em>X</em></span>';
		}
		if(p.year_flag != "2"){
			info += '<span id="year_flag" title="' + LANG.UI_SETTING_VM_GFS_YEAR_POINT+ '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_SETTING_VM_GFS_YEAR_POINT + '</i><em>X</em></span>';
		}
		if(p.forever_flag != "2"){
			info += '<span id="forever_flag" title="' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '</i><em>X</em></span>';
		}
		
		$('.searchContent').append(info);
		$('#searchDiv').removeClass('opacity-0');
		$('#searchDiv').addClass('opacity-100');
		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('#searchDiv .searchContent');
			if(searchContent[0].children.length == 0){
				$('#searchDiv').removeClass('opacity-100');
				$('#searchDiv').addClass('opacity-0');
			}
			var parent  = $(this).parent();
			var id = parent[0].id;
			if(id == "time"){
				p.start_time = "";
				p.end_time = "";
			}else{
				p[id] = "";
			}
			grid.bootstrapTable('refresh', {query: p});
			
		});
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').removeClass('opacity-100');
			$('#searchDiv').addClass('opacity-0');
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						offset: 0,
						limit: 20,
						sort: 'timepoint',
						order: 'desc',
						vm_uuid: vm_uuid,
						platform_uuid: vcenter_uuid,
						task_uuid: task_uuid
					}
				}
			});
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').removeClass('opacity-100');
			$('#searchDiv').addClass('opacity-0');
		}
	}
	
	//批量删除所选时间点
	var deleteSelectPoint = function(){
		var node = zTree.getCheckedNodes(true);
		if(node.length == 0){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
			return;
		}
		timepointList = [];             //每次删除先清空时间点列表
		vmlist = [];					//虚拟机列表
 		
		for(var i=0; i<node.length; i++){
			if(node[i].type == 1){
				if(!node[i].children){
					var vmdata = {};
					vmdata.vm_uuid = node[i].vm_uuid;
					vmdata.node_uuid = node[i].node_uuid;
					vmdata.hypervisor_type = node[i].hypervisor_type;
					vmdata.task_uuid = node[i].task_uuid;
					vmlist.push(vmdata);
				}
			}else if(node[i].type == 3){
				if (CONF.BD_STORAGE_TYPE.TAPE === parseInt(node[i].storage_type)) {
					// 时间点所在存储是磁带则无法删除
					UIToastr.showWarning(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK, LANG.UI_TAPE_DELETE_BACKUP_POINT_TIPS);
					return;
				}

				var data = {};
			    data.timepoint_uuid = node[i].timepoint_uuid;
			    data.node_uuid = node[i].node_uuid;
			    data.hypervisor_type = node[i].hypervisor_type;
			    timepointList.push(data);
			    if(node[i].isParent){
			    	var children = node[i].children;
			    	for(var j=0;j<children.length;j++){
			    		var data = {};
			    		data.timepoint_uuid = children[j].timepoint_uuid;
			    		data.node_uuid = children[j].node_uuid;
			    		data.hypervisor_type = children[j].hypervisor_type;
			    		timepointList.push(data);
			    	}
			    }
			}
		}
		
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
	};
	
	//左侧批量删除所选择的
	var submitSelectDelete = function(){
		var node = zTree.getCheckedNodes(true);
		var params = {timepoint_list:timepointList, vm_list: vmlist};
		Metronic.blockUI({target: '.timepoint-wrapper__content',animate: true});
	    pAjaxRequest(params, "/api/v1/vm/restore_data", "DELETE", function (d) {
			Metronic.unblockUI('.timepoint-wrapper__content');
			if (operateResponseList(d)) {
				for(var i=0;i<node.length;i++){
					var halfCheck = node[i].getCheckStatus();
					if(!halfCheck.half){
						refreshAllTree(node[i].id);
					}else{
						zTree.checkNode(node[i],!node[i].checked,false,false);
					}
				}
				grid.bootstrapTable('refresh');
			}
		}, true);
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
			if (nodes[0].children) {
				getSyncVcenterInfo("vcenter_tree", parent, true, true);
			} else {
				zTree.removeNode(parent);
			}
		}else{
			zTree.removeNode(nodes[0]);
		}
	}
	
	//单个删除
	var submitDelete = function(rowData){
		var params = {timepoint_uuid: rowData.timepoint_uuid};
		Metronic.blockUI({target: '#datatable',animate: true});
    	pAjaxRequest(params, "/api/v1/vm/restore_data_one", "DELETE", function (d) {
			Metronic.unblockUI('#datatable');
			if (operateResponseList(d, LANG.UI_DATA_DELETE_TIMEPOINT)) {
				grid.bootstrapTable('refresh');
				var idStr = rowData.hypervisor_type + rowData.detail.vm_uuid + rowData.detail.task_uuid;
				//先刷新界面树
				refreshTree(rowData.timepoint_uuid);
				var node = zTree.getNodesByParam("id", idStr, null);
				//如果还有时间节点执行刷新树操作
				if(node.length !=0){
					getSyncVcenterInfo("vcenter_tree", node[0], true, true);
				}
			}
		}, true);
	}
	
	//定位搜索时间节点
	var searchVM = function(){
		var allNode = zTree.transformToArray(zTree.getNodes());
		nodeParamList = "";
		nodeParamList = zTree.getNodesByFilter(filterZtree)
		// if(nodeParamList.length == 0){
			//如果没搜到节点需要异步加载
			searchSyncPoint(allNode);
		// }else{
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
			$('#vcenter_tree').show();
			zTree.hideNodes(allNode);
			
			//连接搜索的和所勾选的
			var nodeParamList1 = zTree.transformToArray(nodeParamList);
	        for(var n in nodeParamList1){
	            findParent(zTree,nodeParamList1[n]);
	        }
	        zTree.showNodes(nodeParamList);
		// }
		
		
        
    }
	
	//异步搜索时间点
	var searchSyncPoint = function(allNode){
		// 任务和虚拟机中没搜索到 去搜时间点
		var data = {}
		data.sub_moduletype = 3;
		data.manage_flag = true;
		data.filter_flag = true;
		data.disabled_flag = true;
		data.node_uuid = $('#nodeselect').val();
		data.storage_uuid = $('#storageselect').val();
		data.keyword = $('#searchvm').val();
		data.week_flag = $("input[name=weekFilter]").get(0).checked ? 1 : 2;
		data.month_flag = $("input[name=monthFilter]").get(0).checked ? 1 : 2;
		data.year_flag = $("input[name=yearFilter]").get(0).checked ? 1 : 2;
		data.forever_flag = $("input[name=foreverFilter]").get(0).checked ? 1 : 2;
		Metronic.blockUI({target:'#vcenter_tree',animate: true});
		pAjaxRequest(data, "/api/v1/vm/restore_data/restore_points", "GET", function (d) {
			Metronic.unblockUI('#vcenter_tree');
			var pointNode = d.data.rows;
			showNode = [];

			// 隐藏错误提示
			$('#nosearchtips').hide();
			// 在搜索之前将所有时间点节点移除
			var allNodes = zTree.transformToArray(zTree.getNodes());
			for (const node of allNodes) {
				if (node.timepoint_uuid !== undefined && node.timepoint_uuid) {
					zTree.removeNode(node);
				}
			}
			if(pointNode != null) {
				timePointFlag = true;
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
			// 在搜索之前将非时间空节点移除
			allNodes = zTree.transformToArray(zTree.getNodes());
			for (const node of allNodes) {
				if (node.timepoint_uuid !== undefined && node.timepoint_uuid) {
					continue;
				}
				if (!node.children) {
					zTree.removeNode(node)
				}
			}
			searchByTree(data);
		}, true);
	}

	//在当前树用ztree方法搜索
	var searchByTree = function (data) {
		var keyword = $.trim($('#searchvm').val());
		var allNodes = zTree.transformToArray(zTree.getNodes());
		zTree.hideNodes(allNodes);    //当开始搜索时，先将所有节点隐藏
		var nodeList = fuzzySearch('name', keyword, zTree); // 模糊搜索
		// var nodeList = zTree.getNodesByParamFuzzy('name', keyword); // 模糊搜索
		//搜gfs标记点
		if (data.week_flag == 1) {
			var nodeList_mark = zTree.getNodesByParamFuzzy('name', 'viconfont vicon-remark-week', 0);
			nodeList = nodeList_mark.filter(item => nodeList.includes(item));
		}
		if (data.month_flag == 1) {
			var nodeList_mark = zTree.getNodesByParamFuzzy('name', 'viconfont vicon-remark-month', 0);
			nodeList = nodeList_mark.filter(item => nodeList.includes(item));
		}
		if (data.year_flag == 1) {
			var nodeList_mark = zTree.getNodesByParamFuzzy('name', 'viconfont vicon-remark-year', 0);
			nodeList = nodeList_mark.filter(item => nodeList.includes(item));
		}
		if (data.forever_flag == 1) {
			var nodeList_mark = zTree.getNodesByParamFuzzy('name', 'viconfont vicon-remark-forever', 0);
			nodeList = nodeList_mark.filter(item => nodeList.includes(item));
		}
		if (nodeList.length == 0) {
			$('#vcenter_tree').hide();
			$('#nosearchtips').show();
			return;
		}
		var arr = [];
		for (var i = 0; i < nodeList.length; i++) {
			arr = $.merge(arr, nodeList[i].getPath());    //找出节点的所有父节点（包括自己）
			if (nodeList[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr, nodeList[i].children);
			}
		}
		zTree.showNodes($.unique(arr));    //显示所有要求的节点及其路径节点
		zTree.checkAllNodes(false);  // 取消选中
		//展开显示的所有节点
		arr.forEach(item => {
			if (item.children) {
				if (item.timepoint_uuid !== undefined && item.timepoint_uuid) {
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
	}

	// 树模糊搜索, 去除html标签, 返回所有子树
	var fuzzySearch = function (key, value, tree) {
		value = value.toLowerCase();
		var allNodes = tree.transformToArray(tree.getNodes());
		var retNodes = [];
		var nodeIds = [];
		let excludeSearch = [2, 3];
		for (const node of allNodes) {
			if (nodeIds.includes(node.id)) {
				continue;
			}
			if (excludeSearch.includes(node.backup_mode)) {
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
	
	//找到父节点
	 var findParent = function(treeObj,node){
		 zTree.expandNode(node,true,false,false);
		 if(!node.children){
			 nodeParamList.push(node);
			 zTree.expandNode(node,false,false,false);
		 }
		 var pNode = node.getParentNode();
		 if(pNode != null){
			 nodeParamList.push(pNode);
			 findParent(zTree, pNode);
		 }
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
			$('#nosearchtips').hide();
			if (timePointFlag) {
				initTree();
			}
			$("#markStr").empty();
		})
	    if(resultVal || resultWeek || resultMonth || resultYear || resultForever){
	    	return true;
	    }else{
	    	return false;
	    }
		
		
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
		if(1 == childrenNum && vmNode.type == 3){
			if (vmNode.children) {
				//完备点下还有其他时间点则刷新
				getSyncVcenterInfo("vcenter_tree", parent, true, true);
			} else {
				zTree.removeNode(parent);
				removeParentNode(parent);
			}
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
	
	
	//标星统一处理
	var starHandler = function(params, funName){
		Metronic.blockUI({target: '#setAllMark',animate: true});
		//发送到数据管理统一处理
    	$.post(CONF.AJAXPATH, {m:CONF.M.DATA,f:funName,p:params}, function(data){
    		var d = JSON.parse(data)
    		//如果失败
    		if(!d.re){
    			UIToastr.showInfo(LANG.UI_OS_DATA_SET_MARK, LANG.UI_SETTING_VM_MARK_POINT_FAIL);
    		}
    	});
	}
	
	
	
    var handleRecords = function (vmuuid, vcenteruuid, taskuuid, storage_uuid) {
		vm_uuid = vmuuid;
		vcenter_uuid = vcenteruuid;
		task_uuid = taskuuid;
		let params = {
			vm_uuid : vmuuid,
			platform_uuid : vcenteruuid,
			task_uuid: taskuuid,
			storage_uuid: storage_uuid
		};
		if(!gridInitFlag){
			//初始化表格
			let options = {
				vin_url: '/api/v1/vm/restore_data/restore_points',
				vin_method: 'GET',
				queryParamsType: 'limit',
				queryParams: function (p) {
					return {
						limit: p.limit,
						offset: p.offset,
						order: p.order,
						sort: p.sort,
						platform_uuid: params.platform_uuid,
						task_uuid: params.task_uuid,
						vm_uuid: params.vm_uuid,
						storage_uuid: params.storage_uuid
					}
				},
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 20,
				pageList: [10, 20, 50, 100],
				paginationLoop: false,
				uniqueId: 'timepoint_uuid',
				lineHeight: '60px',
				changeHeightBtn: true, //改变高度按钮
				batchOperation: true, // 批量操作
				sortable: true,
				sortName: 'timepoint',
				sortOrder: 'desc',
				resizable: true,
				onRefresh: function (params) {
					grid.bootstrapTable('hideLoading');
				},

				columns: [
					{
						field: 'timepoint',
						title: LANG.UI_COPY_TIMEPOINT,
						width: 25,
						widthUnit: '%',
						formatter: function (value, data) {
							return `<span class="flex-inline flex-direction-column" title="${data.timepoint_uuid}">${data.timepoint_html}</span>`;
						},
					},
					{
						field: 'backup_mode',
						title :LANG.UI_COPY_TYPE,
						width: 10,
						widthUnit: '%',
						formatter: function (value, data) {
							return data.backup_mode_des;
						}
					},
					{
						field: 'data_size',
						title: LANG.UI_COPY_DATA_SIZE,
						width: 10,
						widthUnit: '%',
					},
					{
						field: 'write_size',
						title: LANG.UI_PUBLIC_REAL_SIZE,
						width: 10,
						widthUnit: '%',
					},
					{
						field: 'storage',
						title:  LANG.UI_COPY_DATA_STORAGE,
						width: 25,
						widthUnit: '%',
						formatter: function (value, data) {
							return data.storage_des;
						}
					},
					{
						field: 'user_name',
						title: LANG.UI_DATA_AWS_USER_NAME,
						width: 10,
						widthUnit: '%',
						formatter: function (value, data) {
							return data.user_name;
						}
					},
					{
						field: 'operations',
						title: LANG.UI_PUBLIC_OPERATION,
						sortable: false,
						clickToSelect: false,
						width: 10,
						widthUnit: '%',
						events: {
							'click .remark': function (event, value, row, index) {
								remarkPoint(row);
							},
							'click .delete': function (event, value, row, index) {
								deletePoint(row);
							},
							'click .setmark': function (event, value, row, index) {
								setMark(row);
							},
						},
						formatter: function (value, data, index) {
							let button = '<div class="btn-group positionabs" style="margin-top: -14px">';
							if (index > 4) {
								button = '<div class="btn-group positionabs dropup" style="margin-top: -14px">';
							}
							button += '<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" ' +
								'data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">' +
								'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
								'</button>' +
								'<ul class="dropdown-menu" role="menu">';
							$.each(data.operations, function (i, d) {
								switch (d) {
									case 1:
										button += '<li class="remark"><a href="javascript:;" ><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_PUBLIC_REMARK + '</a></li>';
										break;
									case 2:
										button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_PUBLIC_DELETE + '</a></li>';
										break;
									case 3:
										button += '<li class="setmark"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> ' + LANG.UI_SETTING_VM_DATA_SET_MARK + '</a></li>';
										break;
								}
							});
							button += '</ul></div>';
							return button;
						},
					}
				]
			};
			grid.baseTableConfig().init(options);
	    	gridInitFlag = true;
	    	$('#tabletips').hide();
			$('#vmtable').show();
			$('.remarktips').popover();	   //初始化tips
			$('#marktips').show();
		}else{
			//刷新表格
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (p) {
					return {
						limit: p.limit,
						offset: p.offset,
						order: p.order,
						sort: p.sort,
						platform_uuid: params.platform_uuid,
						task_uuid: params.task_uuid,
						vm_uuid: params.vm_uuid,
						storage_uuid: params.storage_uuid
					}
				}
			});
		}
    }

    //初始化日期选择插件
    var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepickerVmData').daterangepicker({
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
		$('#daterangepickerVmData').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerVmData').on('cancel.daterangepicker', function(ev, picker) {
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
    
    return {
        //main function to initiate the module
        init: function () {
			initStorageShowType(); //初始化存储展示方式
        	inintDatatimePicker();    //初始化时间
        	initTree();        	
        	initListener();
        	initUserPassword();
        }

    };

}();

jQuery(document).ready(function() {
	AWSData.init();
});