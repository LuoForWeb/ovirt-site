var AWSGrainRecover = function () {
	var pointtypetree, pointtypetreeInitFlag = false, vmtypetree, vmTypetreeInitFlag = false;
	var currentTree; //当前展示的树
	var _SHOWFALG = false; //选择数组及下面的内容是否显示的标志
	var nodeParamList;
	var showType = 1; //展示方式

	var submitCheck = function(){
		//检测是否选择备份时间点
		var timepointNode = currentTree.getCheckedNodes(true);
		if(0 == timepointNode.length){
			return UIToastr.showWarning(LANG.UI_INSTANT_SELECT_TIMEPOINT, LANG.UI_INSTANT_SELECT_TIMEPOINT_TIPS);
		}
		//时间点信息
		var d = timepointNode[0];
		data.point_info.hypervisor_type = d.hypervisor;
		data.point_info.points = {vm_uuid:d.vm_uuid, timepoint_uuid:d.timepoint_uuid, vm_name:d.vm_name};
		//任务名
		data.job_name = $('input[name=taskname]').val();
		Metronic.blockUI({target: '#recovercontent',animate: true, cenrerY: true,});
		pAjaxRequest(data, "/api/v1/vm/jobs/granular_restore", "POST", function (d) {
			Metronic.unblockUI('#recovercontent');
			if (operateResponseList(d)) {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}
		}, false);
	}
	
	var handleValidation = function() {
        var instantrecoverForm = $('#grainrecoverform');

        instantrecoverForm.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                taskname: {
                	required: true,
					tasknameblank: true
                },
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");  
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group   
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

		//验证任务名是否全部空格
		$.validator.addMethod("tasknameblank", function(value, element) {
			$.validator.messages.tasknameblank = LANG.UI_TOOLS_TASKNAME_BLANK_TIPS;
			return '' !== $.trim(value);
		});
        
        $('#submitbtn').on('click',function(){
			if ('' === $.trim($('input[name=taskname]').val())) {
				UIToastr.showWarning(LANG.UI_RECOVERY_GRAIN, LANG.UI_RECOVERY_GRAIN_RENAME);
				return false;
			}
        	if (instantrecoverForm.validate().form()) {
        		submitCheck();
            }
		});
		
		//取消按钮事件
		$('#cancelbtn').on('click',function(){
	    	LOCATION('./content/vm/vmgrainrecover.php', 'vmrecovera');
		});
        
	};
	
	//初始化时间点展示方式和事件
	var initPointShowType = function(){
		pAjaxRequest({}, "/api/v1/cloud/storages", "GET", function (d) {
			var storageselect = $('#storageselect');
			storageselect.empty();
			storageselect.append('<option value="">' + LANG.UI_STORAGE_ALL + '</option>');
			let data = d.data.rows;
			for (var i = 0; i < data.length; i++) {
				if (CONF.BD_STORAGE_TYPE.CLOUD == data[i].type) {
					//屏蔽云存储
					continue;
				}
				var option = $("<option>").text(data[i].name).val(data[i].uuid);
				storageselect.append(option);
			}
		}, false);
		$('#storageselect').on('change', storageselectChange);
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
			$('.two_tree').show();
			$('#nosearchtips').hide();
		} else {
			$('.two_tree').hide();
			$('#nosearchtips').show();
		}
		//连接搜索的和所勾选的
		nodeParamList =nodeParamList.concat(checkNode);
		var nodeParamList1 = currentTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
        	findParent(currentTree,nodeParamList1[n],value);
        }
            currentTree.showNodes(nodeParamList);
    }
	
	 //找到父节点
	var findParent = function(treeObj,node, value){
		 if(value == "" && node.type != -1){
			 currentTree.expandNode(node,false,false,false); 
		 }else{
			 currentTree.expandNode(node,true,false,false);
		 }
		 if(!node.children){
			 currentTree.expandNode(node,false,false,false);
		 }
		 if(!node.isParent || node.type == 1){
			nodeParamList.push(node);
		 }
		 var pNode = node.getParentNode();
		 if(pNode != null){
			 nodeParamList.push(pNode);
			 findParent(currentTree,pNode, value);
		 }
	}

	//存储选择改变事件
	var storageselectChange = function () {
		pointtypetreeInitFlag = false;
		vmTypetreeInitFlag = false;
		initPointTree();
	}
	
	//事件监听
	var initListener = function(){
		//跳转到虚拟机备份任务
		$('#tobackup').on('click',function(){
			if(CONF.TENANTUUID && CONF.TENANTUUID != ""){
				LOCATION('./content/aws/awsbackup.php', 'awsbackup');
			}else{
				LOCATION('./content/aws/awsbackup.php', 'awsbackup');
			}
		});
		
		//验证数据加密密码
		$('#verifyPassword').on('click', verifyPassowrd);
	}
	
	//验证数据加密密码
	var verifyPassowrd = function(){
		var allNodes = pointtypetree.getCheckedNodes(true);
		var data = {};
		var vms = [];
		data.passFlag = true;
		data.timepointuuid = allNodes[0].timepointuuid;
		data.encrypt_pass = btoa($('#encyptPassword').val());
		//未填写密码
		if(data.passFlag && data.encrypt_pass == ""){
			UIToastr.showWarning(LANG.UI_VM_DB_ENCRY_PWD_VERIFI, LANG.UI_VM_INPUT_DB_ENCRY_PWD);
			return false;
		}
		vms.push(data);
		var params = {vms:vms};
		pAjaxRequest(params, "/api/v1/vm/jobs/check_encrypt_password", "POST", function (d) {
			if (d.success) {
				UIToastr.showSuccess(LANG.UI_VM_DB_ENCRY_PWD_VERIFI, LANG.UI_VM_PWD_VERIFI_SUCCESS);
				//启用确认按钮
				$('#submitbtn').prop('disabled', false);
			} else {
				operateResponseList(d, LANG.UI_VM_DB_ENCRY_PWD_VERIFI);
			}
		});
	}
	
	//初始化任务名
	var initTaskName = function(hypervisor_type){
		//设置任务名
		var data = {};
		data.hypervisor_type = hypervisor_type;
		data.grain_flag = true;
		pAjaxRequest(data, "/api/v1/vm/jobs/restore/job_name", "GET", function (d) {
			$('input[name=taskname]').val(d.data.info);
		}, false);
	}
	
	
	//初始化备份时间点树
	var initPointTree = function() {
		var data = {};
		data.show_type = 1;
		data.manage_flag = false;
		data.data_flag = false;
		data.sub_module_type = 3; //公有云获取标志
		data.storage_uuid = $('#storageselect').val();
		data.grain_flag = true;
		var setFunction = setPointTree;
		Metronic.blockUI({target: '.two_tree',animate: true});
		pAjaxRequest(data, "/api/v1/vm/restore_data", "GET", function (d) {
			setFunction(d.data.info);
		}, false);
	};
	
	var checkTreeNodeInfo = function(zNodes){
		if(!zNodes.length){
			$("#nopointtips").show();
			$('#vmtypetree').hide();
			$('#pointtypetree').hide();
			$('#pointshowtype').prop('disabled', true);
			// $('#pointshowtype').selectpicker('refresh');
			return false;
		}else{
			$("#nopointtips").hide();
			// if($('#pointshowtype').val() == 1){
				$('#pointtypetree').show();
				$('#vmtypetree').hide();
			// }else{
			// 	$('#pointtypetree').hide();
			// 	$('#vmtypetree').show();
			// }
			$("#two_tree").show();
			$('#pointshowtype').prop('disabled', false);
			// $('#pointshowtype').selectpicker('refresh');
			return true;
		}
	}
	
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
					showTitle: true,
					nameIsHTML:true
				}
			};
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, zNodes);
		currentTree = pointtypetree;
		pointtypetreeInitFlag = true;
	};
	
	//节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncVcenterInfo(treeId, treeNode, false, false);
		}else{
			return true;
		}
		
	}
	
	
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
	
	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var storageuuid = $('#storageselect').val();
		var div = "#pointtypetree";
		let p = {
			task_uuid: treeNode.task_uuid,
			vm_uuid: treeNode.vm_uuid,
			hypervisor_type: treeNode.hypervisor_type,
			disabled_flag: false, //备份数据禁用勾选增量差异标志
			manage_flag: false,
			vm_check: treeNode.checked,
			storage_uuid: storageuuid,
			grain_flag: true,
		};
		Metronic.blockUI({target: div,animate: true});
		pAjaxRequest(p, "/api/v1/vm/restore_data/restore_points", "GET", function (d) {
			Metronic.unblockUI(div);
			if (d.success) {
				//success
				$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
				if (expendFlag == true) {
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
				}
			} else {
				operateResponseList(d);
			}
		}, false);
	}
	
	
	//时间点选中事件绑定
	var timepointOnCheck = function(e, id, node){
		var allNodes = pointtypetree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			pointtypetree.checkNode(allNodes[i], false, false, false);	
		}
		pointtypetree.checkNode(node, true, false, false);	
		allNodes = pointtypetree.getCheckedNodes(true);
		var pointshowtype = $('#pointshowtype').val();
		if(allNodes.length > 0){
			if(1 == pointshowtype){
				var newName = allNodes[0].vmname + "_" + allNodes[0].pointname;
			}else{
				var newName = allNodes[0].name + "_" + allNodes[0].pointname;
			}
			if(!_SHOWFALG){
				//显示更多的内容
				_SHOWFALG = true;
				$('.dndiv').show();
			}
			//需要验证加密密码
			if(node.config && node.config.password_auto_flag == 2 && node.config.password != ""){
				$('.encryptpassdiv').show();
				$('#submitbtn').prop('disabled', true);
			}else{
				$('.encryptpassdiv').hide();
				//设置确定按钮可用
				$('#submitbtn').prop('disabled', false);
				
			}
		}
		initTaskName(node.hypervisor_type);
	}
	
	
    return {
        //main function to initiate the module
        init: function () {
        	initPointShowType();
        	initPointTree();
        	initListener();
        	handleValidation();
        },
    };
}();

jQuery(document).ready(function() {
	AWSGrainRecover.init();
});