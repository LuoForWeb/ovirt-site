var OSInstantRecover = function () {
    var pointtypetree, pointtypetreeInitFlag = false;
    var currentTree; //当前展示的树
    var _SHOWFALG = false; //选择数组及下面的内容是否显示的标志
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
    var encry_flag  = false; //是否需要校验密码
    var data ={};
    //初始化备份时间点树
	var initPointTree = function() {
		var data = {};
		data.storageuuid = $('#storageselect').val();
        data.recoverflag = true;
		data.dataflag = false;
		var setFunction = setPointTree;
		data.instantflag = true;
		data = JSON.stringify(data);
		Metronic.blockUI({target: '.os_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:34,f:'getTimepointTree',p:data}, setFunction);
	};
    var checkTreeNodeInfo = function(zNodes){
		if(zNodes == "[]"){
			$("#nopointtips").show();
			$('#pointtypetree').hide();
			$("#os_tree").hide();
			return false;
		}else{
			$("#nopointtips").hide();
			$('#pointtypetree').show();
			$("#os_tree").show();
			return true;
		}
	}
    var setPointTree = function(zNodes){
		Metronic.unblockUI('.os_tree');
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
					showTitle: true,
					nameIsHTML:true
				}
			};
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, JSON.parse(zNodes));
		currentTree = pointtypetree;
		pointtypetreeInitFlag = true;
	};
    //选择时间点节点事件绑定
	var nodeSelect = function(treeId, treeNode, clickFlag){
        if(treeNode.agentuuidInTask == true){
			UIToastr.showWarning(LANG.UI_OS_RECOVERY_CHOOSE_BACKUP_TIMEPOINT,LANG.UI_OS_RECOVERY_CHOOSE_BACKUP_TIMEPOINT_ERROR);
			return;
		}
		if(treeNode.type != 0 || treeNode.type != 1){ //如果是时间点 点击时如果为勾选就变为勾选状态  反之
			if(treeNode.checked){
				pointtypetree.checkNode(treeNode, false, false, true);
			}else{
				pointtypetree.checkNode(treeNode, true, false, true);
			}
		}
		pointtypetree.expandNode(treeNode, true)  //展开节点
		nodeExpand(treeId, treeNode);
	}
    //时间点选中事件绑定
	var timepointOnCheck = function(e, id, node){
		var flag =  node.checked;
        var allNodes = pointtypetree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			pointtypetree.checkNode(allNodes[i], false, false, false);	
		}
		// pointtypetree.checkNode(node, true, false, false);	
		pointtypetree.checkNode(node,flag,false,false);
		allNodes = pointtypetree.getCheckedNodes(true);
        getOptionIP(allNodes[0]);
		//如果是取消选择  那么之前的隐藏 并return
		if(flag ==  false){
			return;
		}
		initStorageSelect(allNodes[0]);
        $('.passdiv').show();
		//是否手动加密
		if(node.encrypted_flag && !node.password_auto_flag ){
			encry_flag = true;
			$('.passdiv').show();
		}else{
			//隐藏密码框
            encry_flag = false;
            $('.passdiv').hide();
		}

		//初始化节点传输网络
		$('.transfernetworkDiv').show();
		initNetworkList(allNodes[0]);
        //设置瞬时恢复操作系统名字
        if(!_SHOWFALG){
            //显示更多的内容
            _SHOWFALG = true;
            $('.ipdiv').show(); //ip地址
            $('.storagediv').show(); //存储
            $('.dndiv').show(); //任务名
            //设置确定按钮可用
			$('#submitbtn').prop('disabled', false);
			
        }
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
		var storageuuid = $('#storageselect').val();
		var div = "#pointtypetree";
		var p = {taskuuid:treeNode.taskuuid, agentuuid:treeNode.agentuuid,recoverflag:true,dataflag:false,
				refresh:refreshFlag, storageuuid: storageuuid,instantflag:true};
		var data = JSON.stringify(p);
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:34,f:"getSyncTimepoint",p:data},
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
    //节点选择改变事件
	var storageselectChange = function(){
		pointtypetreeInitFlag = false;
		initPointTree();
	}
	
	// //初始化时间点展示方式和事件
	// var initPointShowType = function(){
	// 	var params = JSON.stringify({moduleType: CONF.MODULE_TYPE.OS,dataFlag: false});
	// 	$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getTimepointAllNode',p:params}, function(d){
	// 		var data = JSON.parse(d);
	// 		var nodeselect = $('#nodeselect');
	// 		nodeselect.empty();
	// 		for(var i=0; i<data.length; i++){
	// 			var option = $("<option>").text(data[i].text).val(data[i].uuid);
	// 			nodeselect.append(option);
	// 		}
    // 	});
	// 	//绑定事件
	// 	$('#nodeselect').on('change', nodeselectChange);
	// 	$('#searchos').on('propertychange', searchOS).on('input', searchOS);
	// }


	//初始化存储类型展示方式和事件
	var initStorageShowType =  function(){
		var data = {
			instantRecoverFlag :true,
			osinstantModuleFlag:true
		}
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getStorageType',p:JSON.stringify(data)}, function(d){
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
		$('#searchos').on('propertychange', searchOS).on('input', searchOS);
	};
	//初始化监听
	var initListener =  function(){
		$('#submitbtn').on('click',function(){
        	submitCheck();
		});
	}
    //获取目标主机IP地址
    var getOptionIP = function(node){
		var data = {};
		data.agent_uuid = node.agentuuid;
		data.os_type = node.ostype;
		data.timepoint_uuid = node.timepointuuid;
		data.instantflag =  true;
		data =JSON.stringify(data);
		//得到所有可用代理主机IP
		$.post(CONF.AJAXPATH, {m:34,f:'getRecoverHostIP',p:data}, function(datas){
			if(datas == "" || datas == null || datas == "[]"){
				UIToastr.showInfo(LANG.UI_OS_RECOVERY_HOST_NULL, LANG.UI_OS_RECOVERY_HOST_NULL_ERROR);
			}
			var p = JSON.parse(datas);
			//先清空option
			$("#IPlistSelect").empty();
			var authorizationGroup = "";
			var NoAuthorizationGroup = "";
			//------
			//添加授权分组
			for(var i=0; i<p.length; i++){
				if(p[i].type == 2){
					authorizationGroup += "<option value='"+p[i].agent_uuid+"'>"+p[i].agent_name+"</option>";
				}
			}
			for(var i=0; i<p.length; i++){
				//如果是授权
				if(p[i].type == 2){
					$("#IPlistSelect").append('<optgroup label='+LANG.UI_OS_AUTHORIZE_HOST+'>'+authorizationGroup+'</optgroup>');
					break;
				}
			}
			//------
			//添加未授权分组
			for(var i=0; i<p.length; i++){
				if(p[i].type == 0 || p[i].type == 1){
					NoAuthorizationGroup += "<option value='"+p[i].agent_uuid+"'>"+p[i].agent_name+"</option>";
				}
			}
			for(var i=0; i<p.length; i++){
				if(p[i].type == 0 || p[i].type == 1){
					$("#IPlistSelect").append('<optgroup label='+LANG.UI_OS_UNAUTHORIZE_HOST+'>'+NoAuthorizationGroup+'</optgroup>');
					break
				}
			}
			//------
			//初始化插件
			initSelectIp();
			$(".selectpicker").selectpicker('refresh');
		});
		
	}
    //初始化存储下拉框
	var initStorageSelect = function(node){
		var data = {};
		data.nodeuuid = node.nodeuuid;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getOsCatchStorage',p:jsonData}, function(d){
    		var data = JSON.parse(d);
    		var softselect = $('#selectstorage');
    		softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
    	});
	}
	//初始化节点传输网络
	var initNetworkList =  function(node){
		var data = {};
		data.nodeuuid = node.nodeuuid;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeNetworkList',p:p}, function(d){
    		var data = JSON.parse(d);
    		var transferNetwork = $('#transferNetwork');
    		transferNetwork.empty();
			for(var i=0; i<data.length; i++){
				var name = data[i].ip;
				if(data[i].alias_name != ""){
    				name += "(" + data[i].alias_name +")";
    			}
				var option = '<option  value="' + data[i].network_uuid + '">' + name + '</option>';
				transferNetwork.append(option);
			}
			
    	});

	}
    //初始化任务名称
    var getTaskName =  function(){
        $.post(CONF.AJAXPATH, {m:34,f:'getOSInstantRecoverTaskName',p:{}}, function(d){
			$('#taskname').val(d);
		});
    }
	//搜索虚拟机
	var searchOS = function(){
		var value = $('#searchos').val();
		var checkNode =currentTree.getCheckedNodes();
		var allNode = currentTree.transformToArray(currentTree.getNodes());
		nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
		currentTree.hideNodes(allNode);
		if(nodeParamList.length == 0){
			$('.two_tree').hide();
			$('#nosearchtips').show();
		}else{
			$('.two_tree').show();
			$("#nosearchtips").hide();
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
    var initSelectIp = function(){
		//初始化下拉框
		$(".selectpicker").selectpicker({
			liveSearch: true, //是否显示搜索框,
			actionsBox: false,//是否显示全部
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
	}
    //立即恢复 
    var handleValidation = function() {
        var instantrecoverForm = $('#instantrecoverform');
        instantrecoverForm.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                taskname: {
                	required: true,
                },
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                icon.removeClass('fa-check').addClass("fa-warning");  
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                // $(element)
                //     .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
            },

            unhighlight: function (element) { // revert the change done by hightlight
                
            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {
                
            }
            
        });
        
        $('#submitbtn').on('click',function(){
        	if (instantrecoverForm.validate().form()) {
        		submitCheck();
            }
		});
        
        
        
	
		//取消按钮事件
		// $('#cancelbtn').on('click',function(){
	    // 	LOCATION('./content/vm/vminstantrecover.php', 'vminstantrecover');
		// });
        
	};
    var submitCheck = function(){
		if('' == $.trim($("#taskname").val())){
			return UIToastr.showWarning(LANG.UI_INSTANT_NAME, LANG.UI_RECOVERY_INSTANT_RENAME);
		}
        //检测是否选择备份时间点
        var timepointNode = currentTree.getCheckedNodes(true);
		if(0 == timepointNode.length){
			return UIToastr.showWarning(LANG.UI_INSTANT_SELECT_TIMEPOINT, LANG.UI_INSTANT_SELECT_TIMEPOINT_TIPS);
		}
		// 非法字符串校验
		let password = $.trim($("#encryptVal").val());
		if (isLatinCode(password)) {
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_OS_PASSWORD_ERROR_TIP);
			return false;
		}

		//非空校验
		if(encry_flag == true){
			var encyptyVal = $.trim($("#encryptVal").val());
			if(encyptyVal == null || encyptyVal == "" || encyptyVal == undefined){
				UIToastr.showWarning(LANG.UI_MOTION_VALIDATE_ADMIN_NOT_NULL_TIPS);
				return false;
			}
		}
        //先进行密码验证
		var encrypt_Verify = verifyEncrpty();
		if(!encrypt_Verify){
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_OS_PASSWORD_ERROR_TIP);
			return false;
		}
        //验证目标主机是否为空
        //获取选中的agent_uuid
		var agent_uuid_list = $('#IPlistSelect').selectpicker('val');
		if(agent_uuid_list.length == 0){
			UIToastr.showInfo(LANG.UI_OS_RECOVERY_HOST_NULL, LANG.UI_OS_RECOVERY_LINK_TEST_ERROR);
			return;
		}
        data.agent_uuid =  agent_uuid_list[0]
        // data.recovery_timepoint_uuid  = timepointNode[0].timepointuuid;
		// data.recovery_timepoint_uuid = [];
		// timepointNode.forEach(timepointNode => {
		// 	data.recovery_timepoint_uuid.push(timepointNode.timepointuuid);
			
		// });
		data.recovery_timepoint_uuid = timepointNode[0].timepointuuid;
        data.task_name = $('input[name=taskname]').val();
        data.cache_target  = $('#selectstorage').val();
	   	data.node_uuid = timepointNode[0].nodeuuid;
		data.recovery_timepoint_info  = [
			[data.recovery_timepoint_uuid,data.timepoint_pwd,data.agent_uuid]
		]
		data.transport_ip = $('#transferNetwork').val();
        var p = JSON.stringify(data);
		//验证数据加密密码正确性
        Metronic.blockUI({target: '#recovercontent',animate: true, cenrerY: true,});
		$.post(CONF.AJAXPATH,{m:34, f:"createInstantOSRecoverJob", p:p}, function(d){
			// var jsonData = JSON.parse(d);
            Metronic.unblockUI('#recovercontent');
            if(OPREL(d)){
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }else{
                return;
            }
		});
        
    }
	//验证密码
	//返回bool成功或失败
	//由于目前时间点只能单选 所以timepoint_pwd这个数组只有一个密码 后续改成可以放置多个密码 与时间点相对应
	var verifyEncrpty = function(){
		//如果不需要输入密码 则直接返回true
		if(encry_flag == false){
            data.timepoint_pwd =  '';
			return true;
		}
		//得到用户输入的密码
		var encyptyVal = $.trim($("#encryptVal").val());
		data.timepoint_pwd = encyptyVal;
		//开始传送到后台验证密码的正确性
		var info = {};
		info.timepoint_uuid = getAlltimepoint();
		info.encryptVal = btoa(encyptyVal);
		info = JSON.stringify(info);
		var result;
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:false, 
	        data:{m:34,f:"osVerifyEncry",p:info},
	        success: function(data){
	        	result = JSON.parse(data);
	        } 
		});
		return result;
		
	}
    //获取所有选中的timepointUUid的集合
	var getAlltimepoint = function(){
		//获取所有选中的时间点
		var allNode = currentTree.getCheckedNodes();
		var thisList = [];
		for(var i=0;i<allNode.length;i++){
			thisList.push(allNode[i].timepointuuid);
		}
		return thisList;
		
	}
    //获取的所有的recoverinfo
    var getRecoverInfo =  function(node){
        //获取选中的agent_uuid
		$agent_uuid_list = $('#IPlistSelect').selectpicker('val');
		if($agent_uuid_list.length == 0){
			UIToastr.showInfo(LANG.UI_OS_RECOVERY_LINK_TEST, LANG.UI_OS_RECOVERY_LINK_TEST_ERROR);
			return;
		}
		var data = {};
		data.agentList = $agent_uuid_list;
		data.timepointList = getAlltimepoint();
		data = JSON.stringify(data);
        // Metronic.blockUI({target: '#tab2',animate: true});
		$.post(CONF.AJAXPATH, {m:34,f:'getInstantRecoverInfo',p:data}, function(datas){
			var p = JSON.parse(datas);
			linkInfo = p;
			if(p.re){
				encry_flag = p.encrypt_flag;
                
				// UIToastr.showSuccess(LANG.UI_OS_RECOVERY_LINK_TEST, LANG.UI_OS_RECOVERY_LINK_TEST_SUCCES);
				// linkFlag = true; 
				// //是否需要显示传输网络标记
				// networkFlag = false;
				// networkFlag = getNetworkFlag(p.newList);
				// //初始化传输网络
				// if(networkFlag){
				// 	$('.transfernetworkDiv').show();
				// 	initNetworkList();
				// }else{
				// 	$('.transfernetworkDiv').hide();
				// }
				// //
				// $("#osTable").osRecoveryConfig(p);
				// $("#zoneInfo").show();
				
				// Metronic.unblockUI('#tab2');
                //组装recoverinfo
                getDataRecovery(p);
			}else{
				UIToastr.showWarning(p.title, p.msg);
				// Metronic.unblockUI('#tab2');
			}
		});
    }
	function isLatinCode(string) {
		var latin1Regex = /[^\x00-\xFF]/;
		if(latin1Regex.test(string)){
			return true;
		}
		return false;
	}

    return {
        init:function(){
            // initPointShowType();//初始化可选节点
			initStorageShowType(); //初始化存储类型
			initListener();
            initPointTree();
            getTaskName();//获取任务名称
            // handleValidation();
        }
    }

}();
jQuery(document).ready(function(){
    OSInstantRecover.init();
});