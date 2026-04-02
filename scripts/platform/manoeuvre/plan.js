var OrchPlan = function(){
	var grid, gridInitFlag = false;
	var zTree;
	var _VMNAMEREG = new RegExp("[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？ ]");
	
	var setTree = function(zNodes){
		if(zNodes == "[]"){
			return;
		}
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
					onCheck: nodeOnCheck,
				}
			};
		zTree = $.fn.zTree.init($("#plan_tree"), setting, JSON.parse(zNodes));
	};
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		zTree.checkNode(treeNode, !treeNode.checked, false, true);
		zTree.expandNode(treeNode, true);
		var planuuid, groupuuid, childuuid;
		if(1 == treeNode.type){
			planuuid = treeNode.id;
		}else if(2 == treeNode.type){
			planuuid = treeNode.pId;
			groupuuid = treeNode.id;
		}else if(3 == treeNode.type){
			planuuid = treeNode.planuuid;
			groupuuid = treeNode.groupuuid;
			childuuid = treeNode.id
		}
		handleRecords(treeNode.type, planuuid, groupuuid, childuuid);
	}
	
	var nodeOnCheck = function(e, id, node){
		var allNodes = zTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			zTree.checkNode(allNodes[i], false, false, false);	
		}
		zTree.checkNode(node, true, false, false);
	}
	
	//初始化预案树
	var initTree = function() {
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getPlanTree',p:{}}, setTree);
	};
	
	//添加表格每行数据
	var addGridRow = function(){
		var data = grid.getDataTable().data();
		var tr = $('#vmdatatable').find('tbody > tr');
		var name = tr.find('td:eq(1)');
		var setting = tr.find('td:eq(2)');
		var recType = tr.find('td:eq(3)');
		var others = tr.find('td:eq(4)');
		for(var i=0; i<data.length; i++){
			addVMTableName(name[i], data[i]);
			addVMTableSetting(setting[i], data[i]);
//			addVMTableRecType(recType[i], data[i]);
			addOthersRecType(others[i], data[i]);
		}
	}
	
	//表格虚拟机列
	var addVMTableName = function(div, data){
		var status = '<a title="' + data[5].dirpath + '" class="colordark">' + data[1] + '</a>';
		$(div).html(status);
	}
	//表格配置列
	var addVMTableSetting = function(div, data){
		var settings = '';
		settings += LANG.UI_EMERGENCY_RECOVERY_CPU + data[5].cpunum + "<br>";
		settings += LANG.UI_EMERGENCY_RECOVERY_CPU_CORE + data[5].cpucore + "<br>";
		settings += LANG.UI_EMERGENCY_RECOVERY_MEMORY_SIZE + data[5].memory + "MB"+ "<br>";
		
		$(div).html(settings);
	}
	//表格恢复类型列
//	var addVMTableRecType = function(div, data){
//		
//	}
	//表格目的其他列
	var addOthersRecType = function(div, data){
		var info = '';
		var powerDes = data[5].power ? LANG.UI_PUBLIC_YES : LANG.UI_PUBLIC_NO;
		info += LANG.UI_EMERGENCY_RECOVERY_TIMEPOINT + data[4] + "<br>";
		info += LANG.UI_EMERGENCY_RECOVERY_DESTINATION_HOST + data[5].hostname + "<br>";
		info += LANG.UI_EMERGENCY_RECOVERY_POWER_ON + powerDes + "<br>";
		
		$(div).html(info);
	}
	
	//初始化虚拟机表格
	var handleRecords = function (type, planuuid, groupuuid, childuuid) {
		if(!gridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0, 1, 2, 3, 4]
	    			}],
//	    			"order": [
//	                    [1, "asc"]
//	                ],
	    	};
			grid = new Datatable();
			var data = {m:CONF.M.MANOEUVRE,f:'getPlanVMGrid',p:{type:type, planuuid:planuuid, groupuuid:groupuuid, childuuid: childuuid}};
			grid.setAjaxParam(data);
			grid.init({src: $("#vmdatatable"), onDataLoad:addGridRow, dataTable:dataTableOpt});
	    	gridInitFlag = true;
			$('#planvmtable').show();
		}else{
			//刷新表格
			grid.getRefresh({type:type, planuuid:planuuid, groupuuid:groupuuid, childuuid: childuuid});
		}
    	return;
    }
	
	var addListeners = function(){
		//预案操作事件
		$('#addplan').on('click', addPlan);
		$('#addgroup').on('click', addGroup);
		$('#addchild').on('click', addChild);
		$('#editorch').on('click', editOrch);
		$('#deleteorch').on('click', deleteOrch);
		//添加预案模态事件
		$('#plans').on('change', initModalGroupList);
		$('#submitorch').on('click', submitorch);
		//修改预案模态事件
		$('#submitedit').on('click', submitorchedit);
		//删除预案模态事件
		$('#submitdelete').on('click', submitdelete);
		//虚拟机管理
		$('#addvm').on('click', addvm);
		$('#editvm').on('click', editvm);
		$('#deletevm').on('click', deletevm);
		
	}
	
	var addvm = function(){
		var allNodes = zTree.getCheckedNodes(true);
		if(0 == allNodes || allNodes[0].type != 3){
			UIToastr.showInfo(LANG.UI_EMERGENCY_RECOVERY_SELECT_ADD_VM, LANG.UI_EMERGENCY_RECOVERY_SELECT_ADD_VM_TIPS);
			return;
		}
		
		var vmTree;
		var _STEP = 1, _VMSETTINGS = {};
		var setVMTree = function(zNodes){
			if(zNodes == "[]"){
				return;
			}
			function showTitleForTree(treeId, treeNode) {
				return treeNode.type != 1;
			};
			var vmNodeSelect = function(treeId, treeNode, clickFlag){
				if(treeNode.chkDisabled){
					UIToastr.showInfo(LANG.UI_EMERGENCY_RECOVERY_VM_ALREADY_EXIST, LANG.UI_EMERGENCY_RECOVERY_VM + treeNode.name + LANG.UI_EMERGENCY_RECOVERY_VM_ALREADY_ADD);
					return true;
				}
				vmTree.checkNode(treeNode, !treeNode.checked, false, true);
				vmTree.expandNode(treeNode, true);
			}
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
						beforeClick: vmNodeSelect,
//						onCheck: vmNodeOnCheck,
					},
					view: {
						showTitle: showTitleForTree
					}
				};
			vmTree = $.fn.zTree.init($("#vmtree"), setting, JSON.parse(zNodes));
		};
		
		//初始化模态事件
		var initaddModalListeners = function(){
			//禁用step超链接
			$('.stepvm').unbind().on('click', function(){
				event.preventDefault();
				return false;
			})
			
			$('#prevstep').unbind().on('click', prevStep);
			$('#nextstep').unbind().on('click', nextStep);
			$('#submitaddvm').unbind().on('click', submitAddVM);
		}
		
		//统一控制上一步下一步(上一步,下一步)
		var stepCtlShow = function(prev, next){
			var leftStep = _STEP - 1;
			var rightStep = _STEP - 2;
			if(next){
				//下一步
				var rightStep = _STEP;
			}
			var steps = $('#steps').find('li');
			$(steps[leftStep]).removeClass('active');
			$(steps[rightStep]).addClass('active');
			var stepscontent = $('#stepscontent').find('.contentpane');
			$(stepscontent[leftStep]).removeClass('active').addClass('fade');
			$(stepscontent[rightStep]).removeClass('fade').addClass('active');
		}
		
		//上一步
		var prevStep = function(){
			stepCtlShow(true, false);
			_STEP--;
			if(1 == _STEP){
				$('#prevstep').hide();
			}
			$('#submitaddvm').hide();
			$('#nextstep').show();
			return;
		}
		
		//替换特殊字符
		var clearString = function (s){ 
		    var rs = ""; 
		    for (var i = 0; i < s.length; i++) { 
		        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_'); 
		    } 
		    return rs;  
		}
		
		//初始化虚拟机配置
		var initVMConfigSettings = function(nodes){
			_VMSETTINGS = {};
			//创建参数
			var selectVM = [];
			//请求虚拟机配置需要的参数
			var vms = [];
			for(var i=0; i<nodes.length; i++){
				var p = {}, addP = {};
				p.vmname = clearString(nodes[i].name);
				p.vmuuid = nodes[i].vmuuid;
				p.vcenteruuid = nodes[i].vcenteruuid;
				p.hypervisor = nodes[i].hypervisor;
				p.childuuid = $('#childuuid').val();
				vms.push(p);
				
				addP.vmuuid = nodes[i].vmuuid;
				addP.vcenteruuid = nodes[i].vcenteruuid;
				addP.hypervisor = nodes[i].hypervisor;
				addP.dirpath = nodes[i].dirpath;
				selectVM.push(addP);
			}
			_VMSETTINGS.selectvm = selectVM;
			
			vms = JSON.stringify(vms);
			$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVMConfigInfoOrch',p:vms}, function(d){
				var data = JSON.parse(d);
				$('#vmconfig').vmConfigOrch({'config':data, 'hide':['storage', 'network', 'host']});
			});
			return;
		}
		
		//下一步
		var nextStep = function(){
			switch(_STEP){
				case 1:
					var vmNodes = vmTree.getCheckedNodes(true);
					if(0 == vmNodes.length){
						UIToastr.showInfo(LANG.UI_EMERGENCY_RECOVERY_SELECT_VM, LANG.UI_EMERGENCY_RECOVERY_SELECT_VM_TIPS1);
						return false;
					}
					initVMConfigSettings(vmNodes);
					break;
				case 2:
					if(!checkVMConfigs()) return;
					break;
				case 3:
					_VMSETTINGS.timepointtype = $('#timepointtype').val();
					break;
			}
			stepCtlShow(false, true);
			_STEP++;
			if(4 == _STEP){
				$('#nextstep').hide();
				$('#submitaddvm').show();
			}
			$('#prevstep').show();
			return;
		}
		
		//检查虚拟机的配置信息是否合法
		var checkVMConfigs = function(){
			_VMSETTINGS.vmconfig = $('#vmconfig').getvmConfigOrch({'hide':['storage', 'network']});
			for(var i=0; i<_VMSETTINGS.vmconfig.length; i++){
				if(_VMSETTINGS.vmconfig[i].memory <= 0 || 
				   _VMSETTINGS.vmconfig[i].cpu.cpunum <=0 || 
				   _VMSETTINGS.vmconfig[i].cpu.cpucore <=0 || 
				   _VMSETTINGS.vmconfig[i].vmname == ''){
					UIToastr.showWarning(LANG.UI_EMERGENCY_RECOVERY_CHECK_VM_CONFIG, LANG.UI_EMERGENCY_RECOVERY_THE + (i+1) + LANG.UI_EMERGENCY_RECOVERY_THE_TIPS );
					return false;
				}
			}
			return true;
		}
		
		//提交添加
		var submitAddVM = function(){
			var host = {};
			host.proxyuuid = $('#hostsetting').val();
			_VMSETTINGS.host = host;
			var plan = {};
			plan.childuuid = $('#childuuid').val();
			plan.groupuuid = $('#groupuuid').val();
			plan.planuuid = $('#planuuid').val();
			_VMSETTINGS.plan = plan;
			
			var p = {}
			p = JSON.stringify(_VMSETTINGS);
			$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'addPlanVM',p:p}, function(data){
				if(OPREL(data)){
					grid.getRefresh({});
					$('#modaladdvm').modal('hide');
				}
			});
		}
		
		//初始化恢复目的地宿主机
		var initDesHostSelect = function(hosts){
			hosts = JSON.parse(hosts);
			var hostsetting = $('#hostsetting');
			hostsetting.empty();
			var option = $("<option>").text(LANG.UI_EMERGENCY_RECOVERY_NOT_SET).val(0);
			hostsetting.append(option);
			if(hosts == "[]"){
				return;
			}
			for(var i=1; i<hosts.length; i++){
				var option = $("<option>").text(hosts[i].text).val(hosts[i].uuid);
				hostsetting.append(option);
			}
		}
		
		//初始化添加虚拟机模态
		var initAddVMmodal = function(){
			//初始化各项参数和默认显示
			_STEP = 1;
			//内容
			$('#steps').find('li').removeClass('active');
			$('#steps').find('li').first().addClass('active');
			$('#stepscontent').find('.contentpane').removeClass('active').addClass('fade');
			$('#stepscontent').find('.contentpane').first().removeClass('fade').addClass('active');
			$('#prevstep').hide();
			$('#nextstep').show();
			$('#submitaddvm').hide();
			initaddModalListeners();
			
			
			var allNodes = zTree.getCheckedNodes(true);
			$('#childuuid').val(allNodes[0].id);
			$('#groupuuid').val(allNodes[0].groupuuid);
			$('#planuuid').val(allNodes[0].planuuid);
			
			//初始化备份虚拟机树
			var p = {};
			p.vmcheck = true;
			p.childuuid = allNodes[0].id;
			p = JSON.stringify(p);
			$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVmDataTree',p:p}, setVMTree);
			$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getOrchProxyHostSelect',p:p}, initDesHostSelect);
			
			$('#modaladdvm').modal({'width':'800px', 'height':'500px'});
		}
		
		initAddVMmodal();
	}
	
	//修改虚拟机
	var editvm = function(){
		alert('还没写,这里涉及到修改的时候这个虚拟机已经没有备份点的问题,讨论下再写');
	}
	
	//删除虚拟机
	var deletevm = function(){
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_EMERGENCY_RECOVERY_SELECT_VM, LANG.UI_EMERGENCY_RECOVERY_SELECT_VM_TIPS2);
		}
		bootbox.confirm({
            title: LANG.UI_EMERGENCY_RECOVERY_DELETE_VM,
            message: LANG.UI_EMERGENCY_RECOVERY_DELETE_VM_TIPS,
            callback: function(r) {
                if(!r) return;
                var data = {};
        		data.id = select;
        		data = JSON.stringify(data);
        		Metronic.blockUI({target: '#fatherdiv',animate: true});
            	$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'deletePlanVM',p:data}, function(data){
            		Metronic.unblockUI('#fatherdiv');
            		if(OPREL(data)){
            			grid.getRefresh({});
            		}
            	});
            }
        });
	}
	
	//提交删除
	var submitdelete = function(){
		var p = {};
		p.orchtype = $('#deleteorchtype').val();
		p.uuid = $('#deleteorchuuid').val();
		p = JSON.stringify(p);
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'deletePlans',p:p}, function(data){
			if(OPREL(data)){
				var allNodes = zTree.getCheckedNodes(true);
				zTree.removeNode(allNodes[0]);
    			$('#modaldivplandelete').modal('hide');
    		}
		});
	}
	
	//提交修改
	var submitorchedit = function(){
		var name = $.trim($('#editname').val());
		var remark = $.trim($('#editremark').val());
		if(name.length == 0){
			UIToastr.showWarning(LANG.UI_EMERGENCY_RECOVERY_PLAN_NAME_NOT_NULL, LANG.UI_EMERGENCY_RECOVERY_PLAN_NAME_NOT_NULL_TIPS);
			return;
		}
		var p = {};
		p.orchtype = $('#editorchtype').val();
		p.name = name;
		p.remark = remark;
		p.uuid = $('#orchuuid').val();
		p = JSON.stringify(p);
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'editPlans',p:p}, function(data){
			if(OPREL(data)){
				var allNodes = zTree.getCheckedNodes(true);
				allNodes[0].name = name;
				allNodes[0].remark = remark;
				zTree.updateNode(allNodes[0]);
//				initTree();
    			$('#modaldivplanedit').modal('hide');
    		}
		});
	}
	
	//提交添加预案
	var submitorch = function(){
		var name = $.trim($('#diyname').val());
		if(name.length == 0){
			UIToastr.showWarning(LANG.UI_EMERGENCY_RECOVERY_PLAN_NAME_NOT_NULL, LANG.UI_EMERGENCY_RECOVERY_PLAN_NAME_NOT_NULL_TIPS);
			return;
		}
		var p = {};
		p.orchtype = $('#orchtype').val();
		p.name = name;
		p.remark = $.trim($('#diyremark').val());
		p.planuuid = $('#plans').val();
		p.groupuuid = $('#groups').val();
		if("2" == p.orchtype && null == p.planuuid){
			//如果是添加分组,需要检测是否选择了总预案
			UIToastr.showWarning(LANG.UI_EMERGENCY_RECOVERY_SELECT_CREATE_GROUP_PLAN, LANG.UI_EMERGENCY_RECOVERY_SELECT_CREATE_GROUP_PLAN_TIPS);
			return;
		}
		if("3" == p.orchtype && (null == p.planuuid || null == p.groupuuid)){
			//如果是添加子预案,需要检测是否选择了总预案和分组
			UIToastr.showWarning(LANG.UI_EMERGENCY_RECOVERY_SELECT_CREATE_CHILD_PLAN, LANG.UI_EMERGENCY_RECOVERY_SELECT_CREATE_CHILD_PLAN_TIPS);
			return;
		}
		p = JSON.stringify(p);
		Metronic.blockUI({target: '#modaldivplan',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'addPlans',p:p}, function(data){
			Metronic.unblockUI('#modaldivplan');
			if(OPREL(data)){
				var d = JSON.parse(data);
				var newNode = d.ext;
				var parentNode = zTree.getNodeByParam("id", newNode.pId, null);
				zTree.addNodes(parentNode, newNode);
    			$('#modaldivplan').modal('hide');
    		}
		});
		
	}
	
	//初始化模态总预案列表
	var initModalPlanList = function(flag){
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getPlanList',p:{}}, function(data){
			var plan = JSON.parse(data);
			var select = $('#plans');
			if(plan.length == 0) {
				select.val("");
				select.empty();
				return;
			}
			var option = "";
			for(var i=0; i<plan.length; i++){
				option += '<option value="' + plan[i].uuid + '">' + plan[i].name + '</option>';
			}
			select.val("");
			select.empty();
			select.append(option);
			if(flag){
				//如果是创建子预案,需要同时初始化分组预案出来
				initModalGroupList();
			}
			
    	});
	}
	
	var initModalGroupList = function(){
		var params = {};
		params.planuuid = $('#plans').val();
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getGroupList',p:params}, function(data){
			var group = JSON.parse(data);
			var select = $('#groups');
			if(group.length == 0) {
				select.val("");
				select.empty();
				return;
			}
			var option = "";
			for(var i=0; i<group.length; i++){
				option += '<option value="' + group[i].uuid + '">' + group[i].name + '</option>';
			}
			select.val("");
			select.empty();
			select.append(option);
    	});
	}
	
	var initModal = function(type){
		$("#orchtype").val(type);
		$("#diyname").val('');
		$("#diyremark").val('');
		$('#modaldivplan').modal();
	}
	
	//添加总预案
	var addPlan = function(){
		$('#plansdiv').hide();
		$('#groupsdiv').hide();
		initModal(1);
	}
	//添加分组预案
	var addGroup = function(){
		$('#plansdiv').show();
		$('#groupsdiv').hide();
		initModalPlanList(false);
		initModal(2);
	}
	//添加子预案
	var addChild = function(){
		$('#plansdiv').show();
		$('#groupsdiv').show();
		initModalPlanList(true);
		initModal(3);
	}
	//编辑预案,现在只有名字和说明,后期会增加验证脚本等,所以不在树上直接修改,用模态方式修改
	var editOrch = function(){
		var allNodes = zTree.getCheckedNodes(true);
		if(0 == allNodes){
			UIToastr.showWarning(LANG.UI_EMERGENCY_RECOVERY_EDIT_PLAN, LANG.UI_EMERGENCY_RECOVERY_EDIT_PLAN_TIPS);
			return;
		}
		$("#orchuuid").val(allNodes[0].id);
		$("#editorchtype").val(allNodes[0].type);
		$("#editname").val(allNodes[0].name);
		$("#editremark").val(allNodes[0].remark);
		
		$('#modaldivplanedit').modal();
	}
	//删除预案
	var deleteOrch = function(){
		var allNodes = zTree.getCheckedNodes(true);
		if(0 == allNodes){
			UIToastr.showWarning(LANG.UI_EMERGENCY_RECOVERY_EDIT_PLAN, LANG.UI_EMERGENCY_RECOVERY_EDIT_PLAN_TIPS);
			return;
		}
		var p = {};
		p.orchtype = allNodes[0].type;
		p.uuid = allNodes[0].id;
		p.name = allNodes[0].name;
		p = JSON.stringify(p);
		Metronic.blockUI({target: '#fatherdiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getDeletePlanInfo',p:p}, function(data){
			Metronic.unblockUI('#fatherdiv');
			var d = JSON.parse(data);
			if(d.hasOwnProperty("deleteflag")){
				//如果有子预案,确认后再删除
				showDeleteModal(d);
			}else{
				//直接删除
				if(OPREL(data)){
					zTree.removeNode(allNodes[0]);
//					initTree();
				}
			}
		});
	}
	
	//显示删除预案确认
	var showDeleteModal = function(d){
		$("#deleteorchuuid").val(d.uuid);
		$("#deleteorchtype").val(d.orchtype);
		$("#deleteplanname").html(d.name);
		$("#groupnum").html(d.groupnum);
		$("#childnum").html(d.childnum);
		$("#vmnum").html(d.vmnum);
		
		$("#dgroupdiv").show();
		$("#dchilddiv").show();
		
		if(2 == d.orchtype){
			//分组预案
			$("#dgroupdiv").hide();
		}else if(3 == d.orchtype){
			//子预案
			$("#dgroupdiv").hide();
			$("#dchilddiv").hide();
		}
		
		$('#modaldivplandelete').modal();
	}
	
	
    return {
        init: function () {
        	initTree();
        	addListeners();
        }

    };
}();

jQuery(document).ready(function() {   
	OrchPlan.init();
});