var VMRecover = function () {
	var data = {pointInfo:{},recoverInfo:{},speedInfo:{}, highInfo:{}, typeInfo:{high:{trasfer:{}}},taskName:'', strategygroupuuid: ''};
	var pointtypetree, pointtypetreeInitFlag = false, vmtypetree, vmTypetreeInitFlag = false, 
		hostTree, usergroupTree;
	var currentTree; //当前展示的树
	var grid = new Datatable({"paging":   false,});
	var vmOldName = [];
	var flagDay = false;
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	var _HOSTSTORAGE = [];	//目的宿主机存储信息
	var _vcenterType;
	var _hypervisor;
	var vmuuidList = [];
	var nodeParamList;
	var initStrategyFlag =false;
	var initSpeedFlag = false;
	var speedList = [];
	var globalStrategy = [];
	var applianceFlag = false;
	var editFlag = false;
	
	var showType = 1; //展示方式

	var initListener = function(){
		
		//选择批量配置虚拟机
		$('#vmssetting').on('change.bootstrapSwitch',function(){
			if(this.checked){
				$(".vmssettingdiv").show();
			}else{
				$(".vmssettingdiv").hide();
			}
		})
		
		//批量配置虚拟机选择存储
		$('#vmsstorage').on('change', function(){
			$('select[name=hoststorage]').val($(this).val());
		})
		
		//appliance开关切换回调
		$('#appliancecheck').on('change.bootstrapSwitch', applianceChange);
		
		//批量配置虚拟机选择网络
		$('#vmsnetwork').on('change', function(){
			$('select[name=hostnetwork]').val($(this).val());
		})
		
		//批量配置虚拟机选择可用域
		$('#vmUsedomain').on('change', function(){
			$('select[name=usedomain]').val($(this).val());
		})
		
		//批量配置虚拟机恢复完成后开机
		$('#powercheck').on('change.bootstrapSwitch',function(){
			$('input[name=vmpower]').bootstrapSwitch('state', this.checked); 
		})
		
		//选择恢复方式
		$('#recovertype').on('change',function(){
			if('1' == this.value){
				data.typeInfo.strategy = {};
				$('#setstrategy').hide();
//				$('#highset').hide();
			}else if("2" == this.value){
				$('#setstrategy').show();
//				$('#highset').show();
			}
			initTimeStrategyDes();
		});

		//初始化添加限速策略模态框
		$('#addSpeedlimit').on('click', function(){
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}
			
		});
		//切换限速模式
        $('#speedModeType').on('change', speedModeHandler);
        

		//策略类型选择
		$('#strategySelect').on('change', strategyHandler);
		
		$('#tobackup').on('click',function(){
	    	LOCATION('./content/vm/vmbackup.php', 'vmbackup');
		});
		
		//跳转到添加虚拟化中心
		$('#toaddvcenter').on('click',function(){
	    	LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
		});
		
		$('#verifyuser').on('click', function(){
			event.preventDefault();
			var selectNode = usergroupTree.getCheckedNodes(true);
			var vcenteruuid = selectNode[0].vcuuid;
			var hypervisor = selectNode[0].hypervisor;
			var groupname = selectNode[0].name;
			var groupuuid = selectNode[0].groupuuid;
			var username = $('#groupusername').val();
			var password = btoa($('#grouppassword').val());
			if('' == password){
				UIToastr.showWarning(LANG.UI_MOTION_VALIDATE_ADMIN_NOT_NULL, LANG.UI_MOTION_VALIDATE_ADMIN_NOT_NULL_TIPS);
				return;
			}
			var data = JSON.stringify({vcenteruuid:vcenteruuid, hypervisor:hypervisor, groupname:groupname, 
				groupuuid:groupuuid, username:username, password:password});
			Metronic.blockUI({target: '.groupdiv',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'veryfyVcenterGroupUser',p:data}, function(d){
	    		Metronic.unblockUI('.groupdiv');
	    		if(OPREL(d)){
	    			//初始化虚拟机信息
	    			//显示隐藏项目
	    			$('#vmconfigs').show();
	    			$('.dndiv').show();
	    			//更新节点账号密码信息,再次点击就不需要再输入了
	    			selectNode[0].username = username;
	    			selectNode[0].password = password;
	    			usergroupTree.updateNode(selectNode[0]);
	    			//初始化网络和存储
	    			initNetworkAndStoreGroupUser(data);
	    		}
	    	});
		});
		
	}
	

	var applianceChange = function(){
		if(this.checked){
			initApplianceSelect();
			$('.applianceselectdiv').show();
		}else{
			$('.applianceselectdiv').hide();
		}
	}

	//初始化appliance下拉框
	var initApplianceSelect = function(){
		if(applianceFlag) return; //只加载一次
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getApplianceSelect',p:{}}, function(d){
			var data = JSON.parse(d);
			if(!data.length) return;
			var applianceSelect = $('#applianceSelect');
			applianceSelect.empty();
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].text).val(data[i].value);
				applianceSelect.append(option);
			}
			applianceFlag = true;
		});
	}
	

	var speedModeHandler = function(){
        if(this.value == 2){
            $('.setStrategy').hide();
        }else{
            $('.setStrategy').show();
        }
	}
	
	var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].mode){
                return false;
            }
        }

        return true;
	}

	var initSpeedTimeStrategy = function(){
		var strategy = [];
		strategy[0] = {
			mode: 1,
			strategy_type: 2,
			days: [0, 0, 0, 0, 1, 0, 0],
			start_time: '23:00:00',
			end_time: '23:30:00',
		};
		//延迟设置,因为这里icheck会默认修改里面的选中事件
        $('#speedstrategy').speedstrategy({config: strategy});
        initSpeedFlag = true;
	}

	var checkTreeNodeInfo = function(zNodes){
		if(zNodes == "[]"){
			$("#nopointtips").show();
			$('#vmtypetree').hide();
			$('#pointtypetree').hide();
			$('#pointshowtype').attr('disabled', true);
//			$('#pointshowtype').selectpicker('refresh');
			return false;
		}else{
			$("#nopointtips").hide();
//			if($('#pointshowtype').val() == 1){
				$('#pointtypetree').show();
				$('#vmtypetree').hide();
//			}else{
//				$('#pointtypetree').hide();
//				$('#vmtypetree').show();
//			}
			$("#two_tree").show();
			$('#pointshowtype').attr('disabled', false);
//			$('#pointshowtype').selectpicker('refresh');
			return true;
		}
	}
	
	//初始化时间点树
	var setPointTree = function(zNodes){
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
					beforeClick: nodeSelect,
					onCheck: timepointOnCheck,
					beforeExpand: nodeExpand
				},
				view: {
					showTitle: true
				}
			};
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, JSON.parse(zNodes));
		pointtypetreeInitFlag = true;
		currentTree = pointtypetree;
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
					beforeExpand: nodeExpand
				},
				view: {
					showTitle: true
				}
			};
		vmtypetree = $.fn.zTree.init($("#vmtypetree"), setting, JSON.parse(zNodes));
		vmTypetreeInitFlag = true;
		currentTree = vmtypetree;
	}
	
	var initPointTree = function() {
		var data = {};
		data.showtype = 1;
		data.node = $('#nodeselect').val();
		var setFunction = setPointTree;
		if(2 == data.showtype){
			setFunction = setPointTreetype2;
		}
		data = JSON.stringify(data);
		Metronic.blockUI({target: '.two_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getTimepointTree',p:data}, setFunction);
	};
	
	//添加虚拟化中心鼠标指上去事件
	var addHoverDom = function(treeId, treeNode) {
		if(1 != treeNode.type) return;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		var aObj = $("#" + nodeTID + "_a");
		if ($("#diyHref_" + nodeTID + nodeID + "_1").length>0) return;
		var str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>' + 
				  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' + 
				  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
		aObj.after(str);
//		aObj.append(str);
		var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
		var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
		var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");
		
		if (hrefRefresh) hrefRefresh.bind("click", function(){
			hostRefresh(treeId, treeNode);
		});
		if (hrefExpand) hrefExpand.bind("click", function(){
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
		});
		if (hrefCollapse) hrefCollapse.bind("click", function(event){
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true, false);
		});
	};
	
	//添加虚拟化中心鼠标移除事件
	var removeHoverDom = function(treeId, treeNode) {
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		if(1 != treeNode.type) return ;
		$("#diyHref_" + nodeTID + nodeID + "_1").unbind().remove();
		$("#diyHref_" + nodeTID + nodeID+ "_2").unbind().remove();
		$("#diyHref_" + nodeTID + nodeID + "_3").unbind().remove();
		$("#diyBtn_space_" + escapeJquery(treeNode.id)).unbind().remove();
	};
	
	//检查没有宿主机切换提示信息
	var checkHostNodeInfo = function(zNodes, id){
		if(zNodes == "[]"){
			$("#nohosttips").show();
			$('.'+id).hide();
			return false;
		}else{
			$("#nohosttips").hide();
			$('.'+id).show();
			return true;
		}
	}
	
	//初始化宿主机树
	var setHostTree = function(zNodes){
		if(!checkHostNodeInfo(zNodes, 'host_tree_div')) return;
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true
					}
				},
				view: {
					addHoverDom: addHoverDom,
					removeHoverDom: removeHoverDom,
				},
				callback: {
					beforeClick: hostNodeSelect,
					onCheck: hostOnCheck,
					beforeExpand: hostNodeExpand,
				}
			};
		hostTree = $.fn.zTree.init($("#host_tree"), setting, JSON.parse(zNodes));
	};
	
	//设置用户分组树
	var setUsergroupTree = function(zNodes){
		if(!checkHostNodeInfo(zNodes, 'usergroup_tree_div')) return;
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true
					}
				},
				callback: {
					beforeClick: vcenterNodeSelect,
					onCheck: vcenterOnCheck,
					beforeExpand: vcenterNodeExpand,
				}
			};
		usergroupTree = $.fn.zTree.init($("#usergroup_tree"), setting, JSON.parse(zNodes));
	};
	
	//选择时间点节点事件绑定
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(4 == treeNode.type){
			pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
		}else if(3 == treeNode.type){
			pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
			pointtypetree.expandNode(treeNode, true)
		}else{
			pointtypetree.expandNode(treeNode, true)
		}
		nodeExpand(treeId, treeNode);
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
		var funName = "getSyncTimepoint";
		var div = "#pointtypetree";
		var p = {taskuuid:treeNode.taskuuid, vmuuid: treeNode.vmuuid, hypervisor:treeNode.hypervisor, 
				refresh:refreshFlag, instantflag:true, vmcheck: false, nodeuuid: nodeuuid};
		if(showType == 2){
			funName = "getSyncTimepointTimeGroup";
			div = "#vmtypetree";
			p = {id:treeNode.taskuuid, hypervisor:treeNode.hypervisor, 
					refresh:refreshFlag, nodeuuid: nodeuuid};
		}
		var data = JSON.stringify(p);
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.VM,f:funName,p:data},
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
	
	//选择第二课树
	var nodeSelectType2 = function(treeId, treeNode, clickFlag){
		vmtypetree.checkNode(treeNode, !treeNode.checked, true, true);
		vmtypetree.expandNode(treeNode, true);
		nodeExpand(treeId, treeNode);
	}
	//选择宿主机节点事件绑定
	var hostNodeSelect = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type){
			hostTree.checkNode(treeNode, !treeNode.checked, false, true);
		}else{
			hostNodeExpand(treeId, treeNode);
			hostTree.expandNode(treeNode, true);
		}
	}
	
	//恢复目的分组选择
	var vcenterNodeSelect = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type){
			usergroupTree.checkNode(treeNode, !treeNode.checked, false, true);
		}else{
			vcenterNodeExpand(treeId, treeNode);
			usergroupTree.expandNode(treeNode, true);
		}
	}
	//恢复目的分组被选中
	var vcenterOnCheck = function(e, id, node){
		var allNodes = usergroupTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			usergroupTree.checkNode(allNodes[i], false, false, false);	
		}
		usergroupTree.checkNode(node, true, false, false);
		
		var selectNode = usergroupTree.getCheckedNodes(true);
		if("" == selectNode[0].username && "" == selectNode[0].password){
			//显示隐藏项目
			$('#vmconfigs').hide();
			$('.dndiv').hide();
			
			var groupusers = selectNode[0].groupusers;
			var groupusername = $("#groupusername");
			groupusername.empty();
			for(var i=0; i<groupusers.length; i++){
				var option = $("<option>").text(groupusers[i]).val(groupusers[i]);
				groupusername.append(option);
			}
			$('#grouppassword').val("");
			$(".groupdiv").show();
		}else{
			$(".groupdiv").hide();
			//显示隐藏项目
			$('#vmconfigs').show();
			$('.dndiv').show();
			
			var data = JSON.stringify({vcenteruuid:selectNode[0].vcuuid, hypervisor:selectNode[0].hypervisor, groupname:selectNode[0].name, 
				groupuuid:selectNode[0].groupuuid, username:selectNode[0].username, password:selectNode[0].password});
			//初始化网络和存储
			initNetworkAndStoreGroupUser(data);
		}
	}
	//恢复目的分组展开
	var vcenterNodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			var data = JSON.stringify({vcenteruuid:treeNode.id, hypervisor:treeNode.hypervisor});
			Metronic.blockUI({target: '#usergroup_tree',animate: true});
			$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:true, 
    	        data:{m:CONF.M.VCENTER,f:'getSyncVcenterGroup',p:data},
    	        success: function(data){ 
    	        	Metronic.unblockUI('#usergroup_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		usergroupTree.addNodes(treeNode, result.msg, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        } 
    		});
		}else{
			return true;
		}
	}
	
	//选择宿主机节点展开事件绑定
	var hostNodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			var data = JSON.stringify({sid:treeNode.sid,id:treeNode.id,pid:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:false});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:true, 
    	        data:{m:CONF.M.VCENTER,f:'getSyncRecoveryVcenter',p:data},
    	        success: function(data){ 
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		hostTree.addNodes(treeNode, result.msg, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        } 
    		});
		}else{
			return true;
		}
	}
	
	//选择宿主机节点展开事件绑定
	var hostRefresh = function(treeId, treeNode){
		if(1 == treeNode.type){
			var data = JSON.stringify({sid:treeNode.sid,id:treeNode.id,pid:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:true});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:true, 
    	        data:{m:CONF.M.VCENTER,f:'getSyncRecoveryVcenter',p:data},
    	        success: function(data){ 
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
    	        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
    	        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        } 
    		});
		}else{
			return true;
		}
	}
	
	//初始化目的宿主机树
	var initHostTree = function(){
		var p = {};
		p.type = data.pointInfo.type;
		p = JSON.stringify(p);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getRecoverHost',p:p}, setHostTree);
		
		$('.dndiv').hide();
	}
	
	//初始化用户组树
	var initUsergroupTree = function(){
		var p = {};
		p.type = data.pointInfo.type;
		p = JSON.stringify(p);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getRecoverUserGroup',p:p}, setUsergroupTree);
		
		$('.dndiv').hide();
	}
	
	
	//替换特殊字符
	var clearString = function (s){ 
	    var rs = ""; 
	    for (var i = 0; i < s.length; i++) { 
	        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_'); 
	    } 
	    return rs;  
	}
	
	var getRnameDivs = function(value){
		var divStr = "<div class=\"input-icon right mb15\">" + 
						"<i class=\"fa\"></i>" + 
						"<input type=\"text\" class=\"form-control rnames\" value=\"" + clearString(value) + "\" name=\"vmname\"/>" + 
					 "</div>";
		return divStr;
	}
	
	//检测选择的点时候是同一个虚拟化中心,如果不是,需要把之前的所有点都取消选择
	var checkHypervisorPoint = function(treeId, node){
		var tree = $.fn.zTree.getZTreeObj(treeId);
		var allNodes = tree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].hypervisor != node.hypervisor){
				tree.checkAllNodes(false);
				tree.checkNode(node, !node.checked, false, false);
				if(treeId == "pointtypetree"){
					$('#VMGroupList li').remove();
				}else if(treeId == "vmtypetree"){
					$('#timepointGroupList li').remove();
				}
				return;
			}
		}
	}
	
	//判断是否在一个备份节点上
	var checkSelectInOneNode = function(flag, tree, node, allNodes, checkTypeFlag){
		if(!flag) return true;
//		var showType = showType;
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
	
	//勾选添加虚拟机显示列表
	var addPointList = function(id,node, showType){
		var info = "";
		var liId = clearString(id + node.vcenteruuid + node.id + node.vmuuid); //添加虚拟机每列ID
		if(node.checked){
			if(showType == 1){
				info += '<li class="list-group-item popovers VMTips" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +
				'"><div class="col1"><div class="cont vmDetail"><div class="cont-col1"><div style="width:30px;height:30px;background: url('+node.icon+') 0 no-repeat;">'
				+'</div><div style="width:30px;height:30px;background: url(./img/vm/vm.png) 0 no-repeat;"></div></div><div class="cont-col2"><div class="desc list-one">' + node.name + '</div><div class="desc list-one">' + node.vmname + '</div></div></div></div><div class="col2  pull-right delete-list" style="margin-left:-35px;width:35px;padding-top: 7px;"><a class="del'+liId+'" >'
				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
			}else if(showType == 2){
				var parentNode = node.getParentNode();
				info += '<li class="list-group-item popovers VMTips" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +
				'"><div class="col1"><div class="cont vmDetail"><div class="cont-col1"><div style="width:30px;height:30px;background: url('+parentNode.icon+') 0 no-repeat;">'
				+'</div><div style="width:30px;height:30px;background: url(./img/vm/vm.png) 0 no-repeat;"></div></div><div class="cont-col2"><div class="desc list-one">' + parentNode.name + '</div><div class="desc list-one">' + node.vmname + '</div></div></div></div><div class="col2  pull-right delete-list" style="margin-left:-35px;width:35px;padding-top: 7px;"><a class="del'+liId+'" >'
				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
			}
			//根据虚拟机树类型添加每一列到列表
			if(id == "pointtypetree"){
				$('#VMGroupList').append(info);
			}else if(id == "vmtypetree"){
				$('#timepointGroupList').append(info);
			}
			$('#' + escapeJquery(liId)).popover();	   //初始化tips
			//移除虚拟机显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				var treeObj = $.fn.zTree.getZTreeObj(id);
				$('.popover.in').remove();
	    		treeObj.checkNode(node,false,false);
	    		$('#' + escapeJquery(liId)).remove();
//	    		var showType = showType;
	    		if(showType == 2){
	    			var parentNode = node.getParentNode();
	    			var children = parentNode.children;
		    		for(var i=0;i<children.length;i++){
		    			if(children[i].checked) return;
		    		}
	    			treeObj.checkNode(parentNode,false,false);
	    		}
			});
		}else{
			//检查node.id是否在虚拟化里面，如果在就要把对应的li移除
			$('#' + escapeJquery(liId)).remove();
		}
		
	}
	
	var escapeJquery = function(srcString){  
        // 转义之后的结果  
        var escapseResult = srcString.toString();  
        // javascript正则表达式中的特殊字符  
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",  
                "]", "|", "{", "}"];  
        // jquery中的特殊字符,不是正则表达式中的特殊字符  
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",  
                ":", ";", "<", ">", ",", "/"];  
        for (var i = 0; i < jsSpecialChars.length; i++) {  
            escapseResult = escapseResult.replace(new RegExp("\\"  
                                    + jsSpecialChars[i], "g"), "\\"  
                            + jsSpecialChars[i]);  
        }  
        for (var i = 0; i < jquerySpecialChars.length; i++) {  
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],  
                            "g"), "\\" + jquerySpecialChars[i]);  
        }  
        return escapseResult;  
    } 
	
	//虚拟机分组
	var timepointOnCheck = function(e, id, node){
		checkHypervisorPoint(id, node);
//		var showType = $('#pointshowtype').val();
		var flag = node.checked;
		var allNodes = pointtypetree.getCheckedNodes(true);
		if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, false)){
			$('#VMGroupList li').remove();
			$('#timepointGroupList li').remove();
			addPointList(id, node, showType);
			return; 
		}
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].vmuuid == node.vmuuid && allNodes[i].vcenteruuid == node.vcenteruuid){
				var liId = clearString(id+allNodes[i].vcenteruuid + allNodes[i].id + allNodes[i].vmuuid); //添加虚拟机每列ID
				//如果虚拟机一样的话,就要取消之前所有的
				pointtypetree.checkNode(allNodes[i], false, false, false);
				$('#' + escapeJquery(liId)).remove();
			}
		}
		pointtypetree.checkNode(node, flag, false, false);
		addPointList(id, node, showType);
	}
	
	//时间点分组
	var timepointOnCheckType2 = function(e, id, node){
		checkHypervisorPoint(id, node);
		//备份点展示方式
//		var showType = $('#pointshowtype').val();
		var flag = node.checked;
		var allNodes = vmtypetree.getCheckedNodes(true);
		if(!checkSelectInOneNode(flag, vmtypetree, node, allNodes, true)){
			$('#VMGroupList li').remove();
			$('#timepointGroupList li').remove();
			if(1 == node.type){
				var children = node.children;
				for(var i=0; i<children.length; i++){
					addPointList(id, children[i], showType);
				}
			}else if(node.type == 2 && node.checked){
				addPointList(id, node, showType);
			}
			return; 
		}
		
		if(1 == node.type){
			//选中时间点
			var children = node.children;
			if(flag){
				//如果选中时间点,取消它的所有邻居节点
				var parentNode = node.getParentNode();
				var broNodes = vmtypetree.getNodesByParam('type', 1, parentNode);
				for(var i=0; i<broNodes.length; i++){
					if(broNodes[i].vmuuid == node.vmuuid){
						vmtypetree.checkNode(broNodes[i], false, true, false);
						var children = broNodes[i].children;
						for(var j=0;j<children.length;j++){
							var liId = clearString(id+children[j].vcenteruuid + children[j].id + children[j].vmuuid); //添加虚拟机每列ID
							$('#' + escapeJquery(liId)).remove();
						}
					}
				}
				for(var j=0;j<allNodes.length;j++){
					var children = node.children;
					for(var k=0;k<children.length;k++){
						if(allNodes[j].type==2 && allNodes[j].vmuuid == children[k].vmuuid){
							vmtypetree.checkNode(allNodes[j], false, false, false);
							var liId = clearString(id+allNodes[j].vcenteruuid + allNodes[j].id + allNodes[j].vmuuid); //添加虚拟机每列ID
							$('#' + escapeJquery(liId)).remove();
						}
					}
				}
				
			}
			
			for(var i=0; i<children.length; i++){
				vmtypetree.checkNode(node, flag, true, false);
				addPointList(id, children[i], showType);
//				$('#timepointGroupList li').remove();
			}
			return;
		}
		
		//同一个虚拟机只能勾选一个对应的时间点
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].type == 2 && allNodes[i].vmuuid == node.vmuuid){
				vmtypetree.checkNode(allNodes[i], false, true, false);
				var liId = clearString(id+allNodes[i].vcenteruuid + allNodes[i].id + allNodes[i].vmuuid); //添加虚拟机每列ID
				$('#' + escapeJquery(liId)).remove();
			}
		}
		vmtypetree.checkNode(node, flag, false, false);
		var parentNode = node.getParentNode();
		if(node.type == 2 && node.checked){
			vmtypetree.checkNode(parentNode, true, false, false);
			addPointList(id, node, showType);
		}else if(node.type == 2 && !node.checked){
			var liId = clearString(id+ node.vcenteruuid + node.id + node.vmuuid); //添加虚拟机每列ID
			$('#' + escapeJquery(liId)).remove();
		}
	}
	
	var hostOnCheck = function(e, id, node){
		var allNodes = hostTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			hostTree.checkNode(allNodes[i], false, false, false);	
		}
		hostTree.checkNode(node, true, false, false);
		
		//显示隐藏项目
		$('#vmconfigs').show();
		$('.dndiv').show();
		//初始化网络和存储
		initNetworkAndStore(node);
	}
	
	//初始化网络和存储(flexcloud,openstack)
	var initNetworkAndStoreGroupUser = function(data){
		Metronic.blockUI({target: '#tab2',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getOpenStackNetworkAndStorage',p:data}, function(d){
			Metronic.unblockUI('#tab2');
			var data = JSON.parse(d);
			_HOSTSTORAGE = data;
			var hoststorage = $('select[name=hoststorage]');
			var hostnetwork = $('select[name=hostnetwork]');
			var useDomain = $('select[name=usedomain]');
			hoststorage.empty();
			hostnetwork.empty();
			useDomain.empty();
			for(var i=0; i<data.storage.length; i++){
				var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
				hoststorage.append(option);
			}
			for(var i=0; i<data.network.length; i++){
				var option = $("<option>").text(data.network[i].text).val(data.network[i].uuid);
				hostnetwork.append(option);
			}
			//可用域
			for(var i=0; i<data.domain.length; i++){
				var option = $("<option>").text(data.domain[i].text).val(data.domain[i].uuid);
				useDomain.append(option);
			}
			//显示隐藏项目
			$('#vmconfigs').show();
			$('.dndiv').show();
			$('#vmrecovercontent').find('.button-next').attr('disabled', false);
    	});
	}
	
	//初始化网络和存储(一般情况)
	var initNetworkAndStore = function(node){
		var p = {};
		p.hypervisor = data.pointInfo.type;
		p.vcenteruuid = node.vcuuid;
		p.hostuuid = node.id;
		var jsonData = JSON.stringify(p);
		Metronic.blockUI({target: '#tab2',animate: true});
		_HOSTSTORAGE = [];
		$('select[name=hoststorage]').empty();
		$('select[name=hostnetwork]').empty();
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getNetworkAndStorage',p:jsonData}, function(d){
			Metronic.unblockUI('#tab2');
			var data = JSON.parse(d);
			_vcenterType = data.vcenter_type;
			_HOSTSTORAGE = data;
			var hoststorage = $('select[name=hoststorage]');
			var hostnetwork = $('select[name=hostnetwork]');
			hoststorage.empty();
			hostnetwork.empty();
			
			
			for(var i=0; i<data.storage.length; i++){
				var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
				hoststorage.append(option);
			}
			for(var i=0; i<data.network.length; i++){
				var option = $("<option>").text(data.network[i].text).val(data.network[i].uuid);
				hostnetwork.append(option);
			}
			
			
			//批量配置虚拟机选择存储
			for(var i=0;i<vmuuidList.length;i++){
				var id = escapeJquery(vmuuidList[i]);
				$('#vmstorage'+id+' select[name=hoststorage]').on('change', {id: id},function(e){
					if(_hypervisor == CONF.VM_TYPE.HYPERV && _vcenterType == 1){
						$('#vmstorage'+ e.data.id+' select[name=hoststorage]').val($(this).val());
					}
				});
			}

//			$('.button-next').attr('disabled', false);
			$('#vmrecovercontent').find('.button-next').attr('disabled', false);
    	});
	}
	
	//初始化虚拟机配置
	var initVMConfig = function(){
		var allPoints = data.pointInfo.points;
		var points = [];
		for(var i=0; i<allPoints.length; i++){
			var point = {};
			point.timepointuuid = allPoints[i]['timepointuuid'];
			point.vcenteruuid = allPoints[i]['vcenteruuid'];
			point.vmuuid = allPoints[i]['vmuuid'];
			point.vmname = clearString(vmOldName[i][0] + "_" + vmOldName[i][1]);
			point.oldname = vmOldName[i][0];
			point.timepoint = vmOldName[i][1];
			points.push(point);
		}
		var jsonData = JSON.stringify(points);
		$('select[name=disktype]').empty();
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVMConfigInfo',p:jsonData}, function(d){
			var data = JSON.parse(d);
			_hypervisor = data[0].hypervisor;
			$('#accordionvm').vmConfig({'config':data});
			
			
			for(var j = 0; j<data.length;j++){
				var storage = data[j].storage;
				for(var i=0; i<storage.length; i++){
					var info = '';
					var disktype = $('.'+ escapeJquery(storage[i].vdi_uuid));
					if(data[0].hypervisor == CONF.VM_TYPE.HYPERV){
						info = '<option value="0">' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE + '</option><option value="1">' + 'VHD' + LANG.UI_SETTING_DISK_TYPE_FIXED + '</option>'+
						'<option value="2">' + 'VHD' + LANG.UI_SETTING_DISK_TYPE_DYNAMIC + '</option><option value="3">' + 'VHDX' + LANG.UI_SETTING_DISK_TYPE_FIXED + '</option>' + '</option><option value="4">' + 'VHDX' + LANG.UI_SETTING_DISK_TYPE_DYNAMIC + '</option>';
						var size = storage[i].size / 1024 / 1024 / 1024 / 1024;
						if(size > 1){
							info = '<option value="0">' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE + '</option><option value="3">' + 'VHDX' + LANG.UI_SETTING_DISK_TYPE_FIXED + '</option>' + '</option><option value="4">' + 'VHDX' + LANG.UI_SETTING_DISK_TYPE_DYNAMIC + '</option>';
						}
					}else if (data[0].hypervisor == CONF.VM_TYPE.VMWARE || data[0].hypervisor == CONF.VM_TYPE.CLOUDVIEW || data[0].hypervisor == CONF.VM_TYPE.CLOUDVIEWSVM){
						info = '<option value="1">' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE + '</option><option value="2">' + LANG.UI_SETTING_DISK_TYPE_THICK_LAZY + '</option>'+
						'<option value="3">' + LANG.UI_SETTING_DISK_TYPE_THICK + '</option><option value="4">' + LANG.UI_SETTING_DISK_TYPE_THIN + '</option>';
					}else if(data[0].hypervisor == CONF.VM_TYPE.FUSIONXEN || data[0].hypervisor == CONF.VM_TYPE.FUSIONKVM){
						info = '<option value="1">' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE + '</option><option value="2">' + LANG.UI_SETTING_DISK_TYPE_ORDINARY_LAZY + '</option>'+
						'<option value="3">' + LANG.UI_SETTING_DISK_TYPE_ORDINARY + '</option><option value="4">' + LANG.UI_SETTING_DISK_TYPE_HUAWEI_THIN + '</option>';
					}
					disktype.html(info);
					
					
				}
			}
			if(data[0].hypervisor == CONF.VM_TYPE.VMWARE || data[0].hypervisor == CONF.VM_TYPE.CLOUDVIEW || data[0].hypervisor == CONF.VM_TYPE.CLOUDVIEWSVM || data[0].hypervisor == CONF.VM_TYPE.FUSIONXEN || data[0].hypervisor == CONF.VM_TYPE.FUSIONKVM){
				 $('.diskth').removeClass("display-none");
				 $('.vmdisktype').removeClass("display-none");
				 $('.vmstorage').removeClass("storage-active");
			 }else if(data[0].hypervisor == CONF.VM_TYPE.HYPERV){
				 $('.diskth').removeClass("display-none");
				 $('.vmdisktype').removeClass("display-none");
				 
			 }
			vmuuidList = [];
			for(var i=0;i<data.length;i++){
				vmuuidList.push(data[i].vmuuid);
			}
			
    	});
	}
	
	var wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            // set wizard title
//            $('.step-title', $('#vmrecovercontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#vmrecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#vmrecovercontent').find('.button-previous').hide();
            } else {
                $('#vmrecovercontent').find('.button-previous').show();
            }

            if (current >= total) {
                $('#vmrecovercontent').find('.button-next').hide();
                $('#vmrecovercontent').find('.button-submit').show();
            } else {
                $('#vmrecovercontent').find('.button-next').show();
                $('#vmrecovercontent').find('.button-submit').hide();
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#vmrecovercontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
                	case 1:
                		if(step1Valid() == false){
                			return false;
                		}
                		break;
                	case 2:
                		if(step2Valid() == false){
                			return false;
                		}
                		break;
                	case 3:
                		if(step3Valid() == false){
                			return false;
                		}
                		break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();
                $('#vmrecovercontent').find('.button-next').attr('disabled', false);
                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#vmrecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#vmrecovercontent').find('.button-previous').hide();
        $('#vmrecovercontent .button-submit').click(submit).hide();
	};
	
	
	var step1Valid = function(){
		$('.groupdiv').hide();
		
		var nodes = currentTree.getCheckedNodes();
		if(!nodes.length){
			$(".selecttimepointtip").html(LANG.UI_RECOVERY_SELECT_POINT).show();
			return false;
		}
		
		data.pointInfo.points = [];
		var showStr = [];
		var pointshowtype = showType;
		vmOldName = [];
		$.each(nodes, function(i, d){
			if(1 == pointshowtype){
				//按虚拟机分组
				if(3 == d.type || 4 == d.type){
					var jsondata = {vmuuid:d.vmuuid, timepointuuid:d.timepointuuid, vcenteruuid:d.vcenteruuid};
					data.pointInfo.points.push(jsondata);
					vmOldName.push([d.vmname, d.pointname]);
					showStr.push(d.path + "(" + d.pointname + ")" + "<br>");
				}
			}else{
				//时间点分组
				if(2 == d.type){
					var jsondata = {vmuuid:d.vmuuid, timepointuuid:d.timepointuuid, vcenteruuid:d.vcenteruuid};
					data.pointInfo.points.push(jsondata);
					vmOldName.push([d.vmname, d.pointname]);
					
					showStr.push(d.path + "(" + d.pointname + ")" + "<br>");
				}
			}
			data.pointInfo.type = d.hypervisor;
		});
		
		var type = parseInt(data.pointInfo.type);
		$('#leixentransmode').attr('disabled', false);     //初始化传输模式状态   
		$('#usedomainDiv').hide(); 							//隐藏统一可用域配置
		$('.appliancediv').hide();			     			//隐藏proxy代理模块
		$('.applianceselectdiv').hide();					//隐藏选择proxy代理
		$('.xentransdiv').hide()                         //XenServer传输模式
		$('.leixentransdiv').hide();                       //类XenServer传输模式
		$('.huaweitransportdiv').hide();					//华为传输模式
		$('#appliancecheck').bootstrapSwitch('state', false);  //proxy默认关闭
		$('.threadDiv').show();
		$('#highstrategyshowdiv').show();
		$('.transferLi').show();
		
		$('.speedlimitDiv').show(); 			//显示限速策略
		$('.threadDiv').show();					//显示多线程
		$('#highstrategyshowdiv').show();		//显示限速策略描述
		$('#speedstrategyshowdiv').show();		//显示多线程描述
		$('.encrypttransferdiv').hide();		//隐藏加密传输
		$('.zerodiv').hide();		//隐藏全磁盘恢复
		$('#zerocheck').bootstrapSwitch('state', false);  //全磁盘恢复默认关闭
		//如果是ZSTACK
		if(type == CONF.VM_TYPE.ZSTACK){
			$('#leixentransmode').val(2);
		}else{
			$('#leixentransmode').val(1);
		}
		switch(type){
			case CONF.VM_TYPE.VMWARE:
			case CONF.VM_TYPE.CLOUDVIEW:
			case CONF.VM_TYPE.CLOUDVIEWSVM:
				$('.transportmodediv').show();						//显示传输策略
				$('#transportmodeshowdiv').show();					//显示传输策略确认
				
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();

				$('.appliancediv').show();			     //显示proxy代理模块
				break;
			case CONF.VM_TYPE.CITRIX:
			case CONF.VM_TYPE.XCPNG:
				$('.xentransdiv').show()                         //XenServer传输模式
				$('.transportmodediv').hide();						//隐藏传输策略
				
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
			case CONF.VM_TYPE.INCLOUD:
			case CONF.VM_TYPE.VGATE:
			case CONF.VM_TYPE.WINSERVER:
			case CONF.VM_TYPE.WINDIY:
			case CONF.VM_TYPE.DSERVER:
				$('.leixentransdiv').show();                       //XenServer传输模式
				$('.transportmodediv').hide();						//隐藏传输策略
				
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
			case CONF.VM_TYPE.HYPERV:
				$('#leixentransmode').attr('disabled', true);     //初始化传输模式状态   
				$('.leixentransdiv').show();                       //XenServer传输模式
				$('.transportmodediv').hide();						//隐藏传输策略
				
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				
				$('.speedlimitDiv').hide(); 			//隐藏限速策略
				$('.threadDiv').hide();					//隐藏多线程
				
				$('#highstrategyshowdiv').show();		//显示限速策略描述
				$('#speedstrategyshowdiv').show();		//显示多线程描述
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
			case CONF.VM_TYPE.FUSIONXEN:
				$('.transportmodediv').hide();                       //隐藏传输策略
				$('.huaweitransportdiv').show();					//华为传输模式
				$("#huaweitransport_mode option[value='san']").remove();
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				$('.threadDiv').hide();
				$('#highstrategyshowdiv').hide();
				break;
			case CONF.VM_TYPE.RHV:
			case CONF.VM_TYPE.OLVM:	
				$('.zerodiv').show();		//显示全磁盘恢复
				$('#zerocheck').bootstrapSwitch('state', true);  //全磁盘恢复默认开启
				$('.transportmodediv').hide();						//隐藏传输策略
				$('.leixentransdiv').show();                         //XenServer传输模式
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
			case CONF.VM_TYPE.NEOKYLIN:
			case CONF.VM_TYPE.OSEASYVSERVER:
			case CONF.VM_TYPE.ZSTACK:
			case CONF.VM_TYPE.FUSIONKVM:
			case CONF.VM_TYPE.EASTEDVSERVER:
				$('.transportmodediv').hide();						//隐藏传输策略
				$('.leixentransdiv').show();                         //XenServer传输模式
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
			case CONF.VM_TYPE.H3C:
			case CONF.VM_TYPE.INCLOUDKVM:
				$('#transportmodeshowdiv').hide();
				$('.transportmodediv').hide();						//隐藏传输策略
				$('.leixentransdiv').hide();                         //XenServer传输模式
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				$('.transferLi').hide();
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
				
			case CONF.VM_TYPE.KVM:
			case CONF.VM_TYPE.SANGFOR:
			case CONF.VM_TYPE.SDCOS:
				$('.transportmodediv').hide();						//隐藏传输策略
				$('#transportmodeshowdiv').hide();					//隐藏传输策略确认
				
				//显示并初始化宿主机树
				initHostTree();
				$('#selectHost').show();
				$('#selectUsergroup').hide();
				$('.transferLi').hide();
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
			case CONF.VM_TYPE.FLEXCLOUD:
			case CONF.VM_TYPE.FLEXHCS:
			case CONF.VM_TYPE.OPENSTACK:
				$('.transportmodediv').hide();						//隐藏传输策略
//				$('#transportmodeshowdiv').hide();					//隐藏传输策略确认
				$('.leixentransdiv').show();                         //XenServer传输模式
				
				//显示并初始化用户组树
				initUsergroupTree();
				$('#selectHost').hide();
				$('#selectUsergroup').show();
				$('#usedomainDiv').show(); 							//显示统一可用域配置
				$('#leixentransmode').val(2);						//默认lanfree传输
				
				$('.encrypttransferdiv').show();					//显示加密传输
				break;
		}
		
		showStep1(showStr);
		initVMConfig();
		
		$('#vmrecovercontent').find('.button-next').attr('disabled', true);
		return true;
	}
	
	//得到任务名,这里需要注意的是在跨虚拟化平台恢复的时候需要用目的地的名字,
	//这里是需要在选择目的地节点后再初始化.
	var getTaskName = function(hypervisor){
		var info = {};
		info.type = hypervisor;
		info = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVMRecoverTaskName',p:info}, function(d){
			$('#jobname').val(d);
		});
	}
	
	var showStep1 = function(nodes){
		var jobName = CONF.VM_DES[parseInt(data.pointInfo.type)] + LANG.UI_PUBLIC_RECOVERY;
		var str = jobName + "<br>";
		$.each(nodes, function(i, d){
			str += d;
		});
		$('.vmtypeshow').html(str);
	}
	
	var step2Valid = function(){
		var showStr = '';
		//恢复后开机
		data.recoverInfo.recover2 = 2;	//异机恢复,原机恢复去掉
		
		var type = parseInt(data.pointInfo.type);
		if(type == CONF.VM_TYPE.FLEXCLOUD || type == CONF.VM_TYPE.OPENSTACK || type == CONF.VM_TYPE.FLEXHCS){
			//如果是openstack,设置所选项目信息
			var nodes = usergroupTree.getCheckedNodes();
			if(!nodes.length){
				UIToastr.showWarning(LANG.UI_MOTION_RECOVERY_HOST, LANG.UI_MOTION_RECOVERY_HOST_TIPS);
				return false;
			}
			if(!nodes[0].username){
				UIToastr.showWarning(LANG.UI_MOTION_VALIDATE_ADMIN,LANG.UI_MOTION_VALIDATE_ADMIN_TIPS);
				return false;
			}
			data.recoverInfo.vcenteruuid = nodes[0].vcuuid;
			data.recoverInfo.groupname = nodes[0].name;
			data.recoverInfo.groupuuid = nodes[0].groupuuid;
			data.recoverInfo.username = nodes[0].username;
			data.recoverInfo.password = nodes[0].password;
			data.recoverInfo.hypervisor = nodes[0].hypervisor;
		}else{
			//其他虚拟化,设置目的宿主机
			var nodes = hostTree.getCheckedNodes();
			if(!nodes.length){
				$(".setrecover2tip").html(LANG.UI_RECOVERY_SELECT_HOST).show();
				return false;
			}
			//恢复到宿主机
			data.recoverInfo.vcenteruuid = nodes[0].getParentNode().id;
			data.recoverInfo.hostuuid = nodes[0].id;
			data.recoverInfo.hypervisor = nodes[0].hypervisor;
		}
		
		
		showStr += LANG.UI_RECOVERY_TO + nodes[0].getParentNode().name + " -> " + nodes[0].name + "<br><br>";
		showStr += LANG.UI_RECOVERY_NEW_NAMES + ": <br>";
		
		data.recoverInfo.vmconfigs = $('#accordionvm').getvmConfig();
		data.recoverInfo.names = [];
		
		var flag = true;
		//得到虚拟机恢复名
		$.each(data.recoverInfo.vmconfigs, function(i, d){
			var value = $.trim(d.vmname);
			if("" == value){
				$(".setrecover2tip").html(LANG.UI_RECOVERY_NAME_NOT_NULL).show();
				flag = false;
				return false;
			}
			//检测是否含有特殊字符
			if(_VMNAMEREG.test(value)){
				flag = false;
				$(".setrecover2tip").html(LANG.UI_TOOLS_VMNAME_TIPS).show();
				return false;
			}
			data.recoverInfo.names[i] = value;
			showStr += value + "<br>";
		});
		if(!flag) return false;
		showStr = showStr.substr(0, showStr.length-2) + "<br><br>";
		
		//验证虚拟机配置,主要是存储使用
		var validate = $('#accordionvm').vmConfigValidate({vmconfig:data.recoverInfo.vmconfigs, hoststorage:_HOSTSTORAGE, hypervisor: data.recoverInfo.hypervisor});
		if(!validate){
			return false;
		}
		getTaskName(data.recoverInfo.hypervisor);
		showStep2(showStr);
		initStrategyDes();
		return true;
	}
	
	var showStep2 = function(str){
		$('.recovershow').html(str);
	}
	
	var step3Valid = function(){
		data.typeInfo.type = $('#recovertype').val();
		data.typeInfo.high.trasfer.mode = $('#transport_mode').val();
		data.typeInfo.high.trasfer.encrypt = $('#encrypttransfer').get(0).checked;
		var type = parseInt(data.pointInfo.type);
		switch(type){
			case CONF.VM_TYPE.CITRIX:
			case CONF.VM_TYPE.XCPNG:
				data.typeInfo.high.trasfer.mode = parseInt($('#xentransmode').val());
				break;
			case CONF.VM_TYPE.INCLOUD:
			case CONF.VM_TYPE.VGATE:
			case CONF.VM_TYPE.WINSERVER:
			case CONF.VM_TYPE.WINDIY:
			case CONF.VM_TYPE.DSERVER:
				
			case CONF.VM_TYPE.RHV:
			case CONF.VM_TYPE.NEOKYLIN:
			case CONF.VM_TYPE.FLEXCLOUD:
			case CONF.VM_TYPE.OPENSTACK:
			case CONF.VM_TYPE.FLEXHCS:
			case CONF.VM_TYPE.OSEASYVSERVER:
			case CONF.VM_TYPE.INCLOUDKVM:
			case CONF.VM_TYPE.H3C:
			case CONF.VM_TYPE.HYPERV:
			case CONF.VM_TYPE.ZSTACK:
			case CONF.VM_TYPE.FUSIONKVM:
			case CONF.VM_TYPE.EASTEDVSERVER:
			case CONF.VM_TYPE.OLVM:
				data.typeInfo.high.trasfer.mode = parseInt($('#leixentransmode').val());
				break;
			case CONF.VM_TYPE.FUSIONXEN:
				data.typeInfo.high.trasfer.mode = $('#huaweitransport_mode').val();
				break;
			
		}
		data.highInfo.threadnum = $('#recoveryThreadNum').val();
		var thread = $('#recoveryThreadNum').val();
		if(thread == "" || thread > 16 || thread <= 0){
			UIToastr.showWarning(LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		data.speedInfo = speedList;
		//全磁盘恢复
		data.recoveryzero = $('#zerocheck').get(0).checked;
		//appliance
		data.appliancecheck = $('#appliancecheck').get(0).checked;
		data.applianceuuid = $('#applianceSelect').find('option:selected').val();
		if(!data.appliancecheck){
			data.applianceuuid = "";
		}else if(data.appliancecheck){
			if(!data.applianceuuid){
				UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
				return false;
			}
		}
		if('1' == data.typeInfo.type){
			//立即恢复
			showStep3();
			return true;
		}else{
			var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();
			data.typeInfo.strategy = strategyConfig.recInfo;
		}
		
		
		showStep3();
		return true;
	}
	
	var showStep3 = function(){
		var showStr1 = '', showStr2 = '';
		showStr1 = $('#recovertype').find("option:selected").text();
		if(!$.isEmptyObject(data.typeInfo.strategy)){
			showStr1 +=  ": " + data.typeInfo.strategy.des;
		}
		
		if(data.typeInfo.high.trasfer.encrypt){
			showStr2 = LANG.UI_STRATEGY_TRANSFER + ": " + LANG.UI_STRATEGY_TRANSFER_ON;
		}else{
			showStr2 = LANG.UI_STRATEGY_TRANSFER + ": " + LANG.UI_STRATEGY_TRANSFER_OFF;
		}
		$('.reservetypeshow').html(showStr1);
		//高级策略
		var threadnumlabel = $('.threadnumlabel').html();
		$('.highstrategyshow').html(threadnumlabel + ": " + $('#recoveryThreadNum').val());
		
		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedlimitsStr = '';
		if(speedList.length !=0){
			for(var i=0;i<speedList.length;i++){
				speedlimitsStr += speedList[i].des + '<br>';
			}
		}else{
			speedlimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedlimitsStr);


		//传输策略
		var transferlabel = $('.transferlabel').html();
		var xentranslabel = $('.xentranslabel').html();
		var leixentranslabel = $('.leixentranslabel').html();
		var huaweitranslabel = $('.huaweitransferlabel').html();
		$('.transportinfoshow').html(transferlabel + ": " + $('#transport_mode').find("option:selected").text());
		var type = parseInt(data.pointInfo.type);
		//加密传输
		var encryptlabel = $('.encrypttransferlabel').html();
		switch(type){
			case CONF.VM_TYPE.CITRIX:
			case CONF.VM_TYPE.XCPNG:
				var des =	encryptlabel + ": " + getSwitchDes(data.typeInfo.high.trasfer.encrypt);
				des += "  " + xentranslabel + ": " +  $('#xentransmode').find("option:selected").text();
				$('.transportinfoshow').html(des);
				break;
			case CONF.VM_TYPE.RHV:
			case CONF.VM_TYPE.OLVM:
				var des =	encryptlabel + ": " + getSwitchDes(data.typeInfo.high.trasfer.encrypt);
				des += "  " + xentranslabel + ": " +  $('#leixentransmode').find("option:selected").text();
				var zerolabel = $('.zerolabel').html();
				des += "  " + zerolabel + ": " + getSwitchDes(data.recoveryzero);
				$('.transportinfoshow').html(des);
				break;
			case CONF.VM_TYPE.INCLOUD:
			case CONF.VM_TYPE.VGATE:
			case CONF.VM_TYPE.WINSERVER:
			case CONF.VM_TYPE.WINDIY:
			case CONF.VM_TYPE.DSERVER:
				
			case CONF.VM_TYPE.NEOKYLIN:
			case CONF.VM_TYPE.FLEXCLOUD:
			case CONF.VM_TYPE.OPENSTACK:
			case CONF.VM_TYPE.FLEXHCS:
			case CONF.VM_TYPE.OSEASYVSERVER:
			case CONF.VM_TYPE.INCLOUDKVM:
			case CONF.VM_TYPE.H3C:
			case CONF.VM_TYPE.ZSTACK:
			case CONF.VM_TYPE.EASTEDVSERVER:
				var des =	encryptlabel + ": " + getSwitchDes(data.typeInfo.high.trasfer.encrypt);
				des += "  " + xentranslabel + ": " +  $('#leixentransmode').find("option:selected").text();
				$('.transportinfoshow').html(des);
				break;
			case CONF.VM_TYPE.FUSIONXEN:
				$('.transportinfoshow').html(huaweitranslabel + ": " + $('#huaweitransport_mode').find("option:selected").text());
				break;
				
		}
		//proxy代理
		var appliancelabel = $('.appliancelabel').html();
		var applianceInfo = appliancelabel + ": " + getSwitchDes(data.appliancecheck);
		if(data.appliancecheck){
			var applianceName = $('#applianceSelect').find('option:selected').text();
			applianceInfo += ', ' + applianceName;
		}
		$('.applianceshow').html(applianceInfo);
		if(type ==  CONF.VM_TYPE.VMWARE){
			$('.applianceshow').show();
		}else{
			$('.applianceshow').hide();
		}
	}
	
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	
	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
			return;
		}
		$('.jobnametip').hide();
		data.taskName = $.trim($("#jobname").val());
		if($('#strategySelect option:selected').val()){
			data.strategygroupuuid = $('#strategySelect option:selected').val();
		}
		//TODO提交
		var jsonData = JSON.stringify(data);
		Metronic.blockUI({target: '#vmrecovercontent',animate: true, cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'createRecoverJob',p:jsonData}, function(d){
    		Metronic.unblockUI('#vmrecovercontent');
    		if(OPREL(d)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}
	
	
	//时间点展示方式改变事件
	var pointShowTypeChange = function(){
		$('#searchvm').val('').hide();
		if(1 == this.value){
			$('#VMGroupList').show();
			$('#timepointGroupList').hide();
			$('#pointtypetree').show();
			$('#vmtypetree').hide();
			currentTree = pointtypetree;
			if(!pointtypetreeInitFlag){
				//未初始化第一棵树的时候需要初始化
				initPointTree();
			}
			$('#searchvm').val('').show();
		}else if(2 == this.value){
			$('#VMGroupList').hide();
			$('#timepointGroupList').show();
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
		$('#VMGroupList li').remove();
		$('#timepointGroupList li').remove();
	}
	
	//初始化时间点展示方式和事件
	var initPointShowType = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getTimepointAllNode',p:{}}, function(d){
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
    	});
		
//		$('#pointshowtype').selectpicker({
//            iconBase: 'fa',
//            tickIcon: 'fa-check'
//        });
		//绑定事件
		$('#pointshowtype').on('change', pointShowTypeChange);
		$('#nodeselect').on('change', nodeselectChange);
		$('#searchvm').on('propertychange', searchVM).on('input', searchVM);
	}

	//搜索虚拟机
	var searchVM = function(){
		var value = $('#searchvm').val();
		var checkNode =currentTree.getCheckedNodes();
		var allNode = currentTree.transformToArray(currentTree.getNodes());
		nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			currentTree.hideNodes(allNode);
		}
		//连接搜索的和所勾选的
		nodeParamList =nodeParamList.concat(checkNode);
		var nodeParamList1 = currentTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(currentTree,nodeParamList1[n]);
        }
            currentTree.showNodes(nodeParamList);
    }
	
	 //找到父节点
	 var findParent = function(treeObj,node){
		 currentTree.expandNode(node,true,false,false);
		 if(!node.children){
			 nodeParamList.push(node);
			 currentTree.expandNode(node,false,false,false);
		 }
		 var pNode = node.getParentNode();
		 if(pNode != null){
			 nodeParamList.push(pNode);
			 findParent(currentTree, pNode);
		 }
	}
	
	//初始化时间策略
	var initStrategy = function(){
		var strategy = [];
		strategy[0] = {
			mode: 4,
			strategy_type: 2,
			days: [0, 0, 0, 0, 1, 0, 0],
			start_time: '23:00:00',
			roll_flag: false,
			roll_interval: '01:00:00',
			roll_end_time: '23:59:59'
		};
		//延迟设置,因为这里icheck会默认修改里面的选中事件
		setTimeout(function(){
			$('#recoveryTimestrategy').strategy({dom: $('#recoveryTimestrategy'), config: strategy, backup_flag: 2});
			$('.rollDiv').hide(); //隐藏滚动执行
		}, 2000);
	}
	
	//版本差异处理,主要是处理标准版本功能限制
	// 中文标准版 1
	// 中文企业版 2
	// 英文免费版 4
	// 英文基础版 9
	// 英文标准版 6
	// 英文企业版 7
	var initSoftwareVersionDiff = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getAuthFunc',p:{}}, function(d){
			var data = JSON.parse(d);
			if(!data.lanfree){
				//传输模式只支持网络传输
				//删除transport_mode不可用项
				$("#transport_mode option[value='san']").remove();
				$("#transport_mode option[value='hotadd']").remove();
				//删除xentransmode不可用项
				$("#xentransmode option[value='2']").remove();
				$("#xentransmode option[value='4']").remove();
				
				//删除类xen不可用项
				$("#leixentransmode option[value='2']").remove();
				
				//删除华为传输模式不可用项
				$("#huaweitransport_mode option[value='san']").remove();
			}
			
			if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
				//传输模式只支持网络传输
				//删除transport_mode不可用项
				$("#transport_mode option[value='san']").remove();
				$("#transport_mode option[value='hotadd']").remove();
				//删除xentransmode不可用项
				$("#xentransmode option[value='2']").remove();
				$("#xentransmode option[value='4']").remove();
				
				//删除类xen不可用项
				$("#leixentransmode option[value='2']").remove();
				
				//删除华为传输模式不可用项
				$("#huaweitransport_mode option[value='san']").remove();
				
			}
		});
	}
	
	var strategyHandler = function(){
		editFlag = false;
		initStrategyData();
	}

	var initStrategyData = function(){
		var index = $('#strategySelect option:selected').val();
		var strategy = globalStrategy[index];
		initOldTimeStrategy(strategy);
		initOldSpeedStrategy(strategy);
		initOldHighStrategy(strategy);
		initStrategyDes();
		editFlag = true;
	}

	var initOldTimeStrategy = function(data){
		$('.recoveryTimeDes').empty();
		if(data.length == 0 || data.time.timeInfo == 0){
			$('#recovertype').val(1);
			var strategy = [];
			strategy[0] = {
				mode: 4,
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				start_time: '23:00:00',
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: '23:59:59'
			};
			$('#recoveryTimestrategy').strategy({dom: $('#recoveryTimestrategy'), config: strategy, backup_flag: 2});
			$('.rollDiv').hide(); //隐藏滚动执行
			$('#setstrategy').hide();
			return;
		} 
        var timeInfo = data.time.timeInfo;
        var check = data.time.check;
        var strategyType = data.strategytype;
        if(!check) return;
		var strategyMode = $('#strategymode').find('icheck');
		//设置时间策略类型
		$('#recovertype').val(timeInfo.type);
		if(timeInfo.type == 2){
			$('#setstrategy').show();
			var recoveryStrategy = [];
			recoveryStrategy[0] = {
				mode: timeInfo.recInfo.mode,
				strategy_type: timeInfo.recInfo.type,
				days: timeInfo.recInfo.days,
				start_time: timeInfo.recInfo.startTime,
				roll_flag: timeInfo.recInfo.rollFlag,
				roll_interval: timeInfo.recInfo.rollInterval,
				roll_end_time: timeInfo.recInfo.endTime
			}
			$('#recoveryTimestrategy').strategy({dom: $('#recoveryTimestrategy'),config: recoveryStrategy,backup_flag: 2});
		}else{
			$('#setstrategy').hide();
		}
    }

    
	var initOldSpeedStrategy = function(data){
		$('.speedlimitDes').empty();
		$('#speedList').empty();
		speedList = [];
		if(data.length == 0) return;
        var speedInfo = data.speedlimit.speedInfo;
        var check = data.speedlimit.check;
        if(!check || !speedInfo) return ;
        for(var i=0;i<speedInfo.length;i++){
			addSpeedList(speedInfo[i]);
			speedList.push(speedInfo[i]);
		}
		$('.speedTips').popover();	   //初始化tips
	}
	
	var addSpeedList = function(list){
        var des = "";
        var uuid = list.uuid;
		des += '<li style="margin-top:15px;" class="list-group-item popovers speedTips" id="speed'+ uuid +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ list.des +
				'"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 94%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + list.des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:10px;"><a class="del'+ uuid +'" >'
				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
        $('#speedList').append(des);
        $('.del'+ uuid).on('click', function(){
            $('.popover.in').remove();
            $('#speed' + uuid).remove();
            for(var j=0;j<speedList.length; j++){
                if(uuid == speedList[j].uuid){
                    speedList.splice($.inArray(speedList[j],speedList),1);
                }
			}
			initSpeedStrategyDes();
        });
    }

    var initOldHighStrategy = function(data){
		if(data.length == 0){
			$('#recoveryThreadNum').val(3);
			return;
		}

        var highInfo = data.high.highInfo;
        var check = data.high.check;
        if(!check) return;
		$('#recoveryThreadNum').val(highInfo.threadnum);
        
	}
	
	var getUuid = function() {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if(len) {
          for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
        } else {
          var r;
          uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
          uuid[14] = '4';
          for(i = 0; i < 36; i++) {
            if(!uuid[i]) {
              r = 0 | Math.random() * 16;
              uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
            }
          }
        }
        return uuid.join('');
      }

    //获取速度单位换算大小
    var getSpeedUnit = function(){
        var type = parseInt($('#unit').val());
        var unit;
        switch(type){
            case 1:
                unit = 1024;
                break;
            case 2:
                unit = 1024 * 1024;
                break;
            case 3:
                unit = 1024 * 1024 * 1024;
                break;
        }
        
        return unit;
	}

	var initSpinner = function(){
		$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('#recoveryThreadDiv').spinner({value:1, step: 1, min: 1, max: 16});
	}

	//加载策略对应描述
	var initStrategyDes = function(){
		initTimeStrategyDes();
		initSpeedStrategyDes();
		initHighStrategyDes();
	}

	//修改颜色
	var initStrategyDesStyle = function(div, des, oldDes){
		if(oldDes != des){
			div.addClass('font-green-seagreen');
		}else{
			div.removeClass('font-green-seagreen');
		}
	}

	var initTimeStrategyDes = function(){
        var des = "";
		//备份
		var recoverytype = $('#recovertype').val();
		var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();
		if(!strategyConfig.recInfo) return;
		if(recoverytype == 1){
			des += LANG.UI_JOB_ONCE_TIME_RECOVER;
		}else if(recoverytype == 2){
			des += strategyConfig.recInfo.des;
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].time.des;
			initStrategyDesStyle($('.recoveryTimeDes'), des, oldDes);
		}else{
			$('.recoveryTimeDes').removeClass('font-green-seagreen');
		}
		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').attr('title', des);
	}

    var initSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if(speedList.length !=0){
			des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
		}
        for(var i=0;i<speedList.length;i++){
            titleDes += speedList[i].des + '. \n';
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].speedlimit.des;
			initStrategyDesStyle($('.speedlimitDes'), des, oldDes);
		}else{
			$('.speedlimitDes').removeClass('font-green-seagreen');
		}
        $('.speedlimitDes').html(des);
        $('.speedlimitDes').attr('title', titleDes);
        
    }

    var initHighStrategyDes = function(){
        var des = "";
		des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#recoveryThreadNum').val();
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].high.des;
			initStrategyDesStyle($('.recoveryHighDes'), des, oldDes);
		}else{
			$('.recoveryHighDes').removeClass('font-green-seagreen');
		}
		$('.recoveryHighDes').html(des);
		$('.recoveryHighDes').attr('title', des);
        
	}

	
	var initDataChangeListeners = function(){
        initSpeedListeners();
		initHighListeners();
    }


    var initSpeedListeners = function(){
        //添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);
    }


    var initHighListeners = function(){
        $('#recoveryThreadNum').on('input propertychange', function(){
            initHighStrategyDes();
		});
		$('.spinner-up').on('click', function(){
			initHighStrategyDes();
        });
        $('.spinner-down').on('click', function(){
			initHighStrategyDes();
        });
	}

	
	//添加限速策略
	var speedSubmit = function(){
        var info = {};
        var des = '';
        info.mode = $('#speedModeType').val();
        var speedUnit = getSpeedUnit();
        var speedNum = parseInt($('#speedSpinnerNumInput').val());
        if(!speedNum || speedNum<= 0){
        	UIToastr.showWarning(LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
        	return false;
        }
        var unit = $('#unit').find('option:selected').text();
        var liId = getUuid();
        info.uuid = liId;
        info.value = speedNum* speedUnit;
        info.speednum = speedNum;
        info.unit = unit;
        if(info.mode == 1){
            var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
            info.type = strategyConfig.speedInfo.type;
            info.startTime = strategyConfig.speedInfo.startTime;
            info.endTime = strategyConfig.speedInfo.endTime;
            info.days = strategyConfig.speedInfo.days;
            info.des = strategyConfig.speedInfo.des + ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + speedNum + unit;
            des += '<li style="margin-top:15px;" class="list-group-item popovers speedTips" id="speed'+ liId +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ info.des + 
    				'"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 94%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:10px;"><a class="del'+ liId +'" >'
    				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
        }else{
            if(!checkSimpleForever(info.mode)){
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_FOREVER_TIPS);
                return false;
            } 
            info.type = 4;
            info.startTime = '';
            info.endTime = '';
            info.days = [];
            info.des = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER+', '+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE+':' + speedNum + unit;
            des += '<li style="margin-top:15px;" class="list-group-item popovers speedTips" id="speed'+ liId+'"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' +info.des + 
            '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one"> '+ info.des +  '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:10px;"><a class="del'+ liId +'" >'
            +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
        }
        $('#speedList').append(des);
        $('.speedTips').popover();	   //初始化tips
        $('.del'+ liId).on('click', function(){
            $('.popover.in').remove();
            $('#speed' + liId).remove();
            for(var i=0;i<speedList.length; i++){
                if(liId == speedList[i].uuid){
                    speedList.splice($.inArray(speedList[i],speedList),1);
                }
            }
            initSpeedStrategyDes();
        });
        speedList.push(info);
        $('#speedlimitModal').modal('hide');
        initSpeedStrategyDes();
    }

	var initStrategySelect = function(){
		if(initStrategyFlag) return; //加载一次
		var p = JSON.stringify({type:2});
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getStrategySelect',p:p}, function(d){
    		var data = JSON.parse(d);
    		var strategyselect = $('#strategySelect');
    		strategyselect.empty();
			for(var i=0; i<data.length; i++){
				var option = '<option  value="' + data[i].uuid + '">' + data[i].text + '</option>';
				globalStrategy[data[i].uuid] = data[i].strategy;
				strategyselect.append(option);
			}
			if(!initStrategyFlag){
				$('#strategySelect').searchableSelect();
				$('.searchable-select-item').on('click', strategyHandler);
				$(document).keyup(function(event){
					if(event.keyCode ==13){
						strategyHandler();
					}
				});
				initStrategyFlag = true;
			}
    	});
	}

    return {
        //main function to initiate the module
        init: function () {
        	initSoftwareVersionDiff();
			wizardInit();
        	initPointShowType();
			initPointTree();
        	initListener();
			initSpinner();
			initStrategySelect(); //初始化策略选择
			initDataChangeListeners();
			initStrategy();
        },
    };
}();

jQuery(document).ready(function() {   
	VMRecover.init();
});