var VMInstantRecover = function () {
	var pointtypetree, pointtypetreeInitFlag = false, vmtypetree, vmTypetreeInitFlag = false, hostTree, usergroupTree;
	var currentTree; //当前展示的树
	var selectIPFlag = true,networkflag = false,onlineflag = false;
	var _SHOWFALG = false; //选择数组及下面的内容是否显示的标志
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	var _VMNAMETIPS = LANG.UI_TOOLS_VMNAME_TIPS;
	var nodeParamList;
    var vmPlugnInfoData;
	var vmNameLimit = {}; //虚拟机名称长度限制
	
	var submitCheck = function(){
		//检测是否选择备份时间点
		var timepointNode = currentTree.getCheckedNodes(true);
		if(0 == timepointNode.length){
			return UIToastr.showWarning(LANG.UI_INSTANT_SELECT_TIMEPOINT, LANG.UI_INSTANT_SELECT_TIMEPOINT_TIPS);
		}
		
		var data = {pointInfo:{},recoverInfo:{},taskName:''};
		
		//时间点信息
		var d = timepointNode[0];
		data.pointInfo.points = {vmuuid:d.vm_uuid, uuid:d.timepoint_uuid, version:d.version, vmname:d.vm_name};
		
		//其他检测是否选择目标宿主机
		var hostNode = hostTree.getCheckedNodes(true);
		if(0 == hostNode.length){
			return UIToastr.showWarning(LANG.UI_INSTANT_SELECT_HOST, LANG.UI_INSTANT_SELECT_HOST_TIPS);
		}
		var hypervisor = parseInt(hostNode[0].hypervisor);
		if(CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)){
			//如果是openstack
			var nodes = hostNode;
			data.recoverInfo.vcenteruuid = hostNode[0].vcuuid;
			data.recoverInfo.groupname = hostNode[0].name;
			data.recoverInfo.groupuuid = hostNode[0].groupuuid;
			data.recoverInfo.username = hostNode[0].username;
			data.recoverInfo.password = hostNode[0].password;
			data.recoverInfo.hypervisor = hostNode[0].hypervisor;
			data.recoverInfo.controllerip = $('input[name=openstackIP]').val();
		}else{
			//宿主机信息
			data.recoverInfo.vcenteruuid = hostNode[0].vcuuid;
			data.recoverInfo.hostuuid = hostNode[0].id;
			data.recoverInfo.hypervisor = hostNode[0].hypervisor;
		}
		
		data.recoverInfo.newname = $('input[name=vmname]').val();
		//存储挂载点IP或域名
		data.recoverInfo.addr = $('input[name=backupserveraddr]').val();
		if(selectIPFlag){
			//自动选择
			data.recoverInfo.addr = $('#serveripaddr').val();
		}else{
			//自定义
			data.recoverInfo.addr = $('input[name=backupserveraddr]').val();
		}
		//检查挂载点IP
		if(null == data.recoverInfo.addr){
			return UIToastr.showWarning(LANG.UI_INSTANT_NFS_NO_IP, LANG.UI_INSTANT_NFS_NO_IP_TIPS);
		}
		//虚拟机配置
		data.recoverInfo.vmconfigs = $('#accordionvm').getvmRecoveryConfig(vmPlugnInfoData);
		if(!data.recoverInfo.vmconfigs){
			return false;
		}
		var params = {};
		params.vms = data.recoverInfo.vmconfigs;
		//验证数据加密密码正确性
		pAjaxRequest(params, "/api/v1/vm/jobs/check_encrypt_password", "POST", function (d) {
			if (d.success) {
				//恢复后开机
				data.recoverInfo.startvm = $('input[name=vmpower]').bootstrapSwitch('state');
				//任务名
				data.taskName = $('input[name=taskname]').val();
				Metronic.blockUI({target: '#recovercontent',animate: true, cenrerY: true,});
				pAjaxRequest(data, "/api/v1/vm/jobs/instant_restore", "POST", function (res) {
					Metronic.unblockUI('#recovercontent');
					if (operateResponseList(res)) {
						LOCATION('./content/platform/jobs/jobs.php', 'task');
					}
				}, false);
			} else {
				operateResponseList(d, LANG.UI_VM_DB_ENCRY_PWD_VERIFI);
			}
		}, true);
	}
	
	var handleValidation = function() {
        var instantrecoverForm = $('#instantrecoverform');

        instantrecoverForm.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                backupserveraddr:{
                	required: true,
                	ipv4Ordomain: true,
                },
                vmname: {
                    required: true,
                    newvmname: true,
                },
                taskname: {
                	required: true,
					tasknameblank: true
                },
				diskname: {
					required: true,
					newdiskname: true
				}
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

		$.validator.messages.required = LANG.UI_PUBLIC_EMPTY_TIPS;
        
        $.validator.addMethod("ipv4Ordomain", function(value, element) {
        	if(selectIPFlag) return true;
    		var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
    		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    		return domain || ipv4;
        }, LANG.UI_TOOLS_IP_OR_DOMAIN);

		$.validator.addMethod("newvmname", function(value, element) {
			$.validator.messages.newvmname = vmNameLimit.msg;
			if (vmNameLimit.len) {
				if (vmNameLimit.vmware_flag && value.replace(/[\\%/]/g, 'xxx').length > vmNameLimit.len) {
					//vmware平台%/\算3个字符，其他算一个
					return false;
				}
				if (vmNameLimit.scp_flag && value.replace(/[\u4e00-\u9fa5（）【】]/g, 'xxx').length > vmNameLimit.len) {
					//scp平台中文和中文括号算3个字符，其他算一个
					return false;
				}
				if (value.length > vmNameLimit.len) {
					return false;
				}
			}
			value = $.trim(value);
			if ('' == value) {
				return false;
			}
			if (vmNameLimit.limit) {
				let reg = new RegExp(vmNameLimit.limit);
				return reg.test(value);
			}
			return true;
		});

		$.validator.addMethod("newdiskname", function(value, element) {
			$.validator.messages.newdiskname = vmNameLimit.disk_msg;
			if (vmNameLimit.disk_len && value.length > vmNameLimit.disk_len) {
				return false;
			}
			value = $.trim(value);
			if ('' == value) {
				return false;
			}
			if (vmNameLimit.disk_limit) {
				let reg = new RegExp(vmNameLimit.disk_limit)
				return reg.test(value);
			}
			return true;
		});

		//验证任务名是否全部空格
		$.validator.addMethod("tasknameblank", function(value, element) {
			$.validator.messages.tasknameblank = LANG.UI_TOOLS_TASKNAME_BLANK_TIPS;
			return '' !== $.trim(value);
		});
        
        $('#submitbtn').on('click',function(){
			//任务名
			if ('' === $.trim($('input[name=taskname]').val())) {
				UIToastr.showWarning(LANG.UI_INSTANT_NAME, LANG.UI_RECOVERY_INSTANT_RENAME);
				return false;
			}
        	if (instantrecoverForm.validate().form()) {
        		if(networkflag && onlineflag){
        		    submitCheck();
        		}else if(!networkflag){
        			UIToastr.showWarning(LANG.UI_HOST_SETTING_GET_NETWORK_FAILURE,LANG.UI_HOST_SETTING_GET_NETWORK_FAILURE_TIPS);
        		}else if(!onlineflag){
        			UIToastr.showWarning(LANG.UI_HOST_SETTING_GET_NETWORK_FAILURE,LANG.UI_HOST_SETTING_GET_NETWORK_NOT_ONLINE_TIPS);
        		} else {
					let vmnameMsg = instantrecoverForm.validate().invalid.vmname;
					let disknameMsg = instantrecoverForm.validate().invalid.diskname;
					if (vmnameMsg) {
						UIToastr.showWarning(LANG.UI_VM_RESTORE_NAME_TITLE, vmnameMsg);
					} else if (disknameMsg) {
						UIToastr.showWarning(LANG.UI_VM_RESTORE_DISK_NAME_TITLE, disknameMsg);
					}
				}
            }
		});
        
        //测试租户控制IP连接
		$('#testIP').on('click', function(){
			var ip = $('input[name=openstackIP]').val();
			if (ip) {
				testCheck();
            }else{
            	UIToastr.showWarning(LANG.UI_VM_CONTROLLER_IP_INPUT,LANG.UI_VM_CONTROLLER_IP_INPUT_TIPS);
            }
		});
		
		var testCheck = function(){
			var selectNode =  currentTree.getCheckedNodes(true);
			var ip = $('input[name=openstackIP]').val();
			var hypervisor = selectNode[0].hypervisor;
			var data = {
				'controller_ip': ip,
				'hypervisor_type': hypervisor
			};
			Metronic.blockUI({target: '.ipDiv',animate: true});
			pAjaxRequest(data, "/api/v1/vm/test_controller_ip", "POST", function (d) {
				Metronic.unblockUI('.ipDiv');
				if (operateResponseList(d)) {
					$('#submitbtn').prop('disabled', false);
				}
			})
		}
		
		//取消按钮事件
		$('#cancelbtn').on('click',function(){
	    	LOCATION('./content/vm/vminstantrecover.php', 'vminstantrecover');
		});
        
	};
	
	
	
	//初始化时间点展示方式和事件
	var initPointShowType = function(){
		pAjaxRequest({module_type : CONF.MODULE_TYPE.VM}, "/api/v1/nodes/get_timepoint", "GET", function (d) {
			var data = d.data;
			var nodeselect = $('#nodeselect');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].node_uuid);
				nodeselect.append(option);
			}
		}, false);
		
		$('#pointshowtype').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
		//隐藏选择框
		$(".pointshowtype-display").css("display","none")
		//绑定事件
		$('#pointshowtype').on('change', pointShowTypeChange);
		// $('#nodeselect').on('change', nodeselectChange);
		$('#storageselect').on('change', storageselectChange);
		$('#searchvm').on('propertychange', searchVM).on('input', searchVM);
		
	}

	//初始化存储
	var initStorage =  function(){
		var data = {
			instantRecoverFlag :true
		}
		pAjaxRequest(data, "/api/v1/storages/type", "GET", function (d) {
			var data = d.data;
			var storageselect = $('#storageselect');
			storageselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].storageid).attr("type",data[i].storagetype);
				storageselect.append(option);
			}
		}, false);
	};

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
	
	//时间点展示方式改变事件
	var pointShowTypeChange = function(){
		$('#searchvm').val('').hide();
		if(1 == this.value){
			$('#pointtypetree').show();
			$('#vmtypetree').hide();
			currentTree = pointtypetree;
			if(!pointtypetreeInitFlag){
				//未初始化第一棵树的时候需要初始化
				initPointTree();
			}
			$('#searchvm').val('').show();
		}else if(2 == this.value){
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
	}

	var storageselectChange =  function(){
		pointtypetreeInitFlag = false;
		vmTypetreeInitFlag = false;
		$('#searchvm').val('');
		initPointTree();
	};
	
	//事件监听
	var initListener = function(){
		//自动选择IP
		$('#diyserverip').on('click', function(){
			$('.selectipdiv').hide();
			$('.inputipdiv').show();
			selectIPFlag = false;
		})
		
		//手动输入IP
		$('#selectserverip').on('click', function(){
			$('.selectipdiv').show();
			$('.inputipdiv').hide();
			selectIPFlag = true;
			$('.dndiv').removeClass('has-error');
			$('.inputipdiv .fa').removeClass('fa-warning').removeAttr('data-original-title');
			$('input[name=backupserveraddr]').val(window.location.host);
		})
		
		//跳转到虚拟机备份任务
		$('#tobackup').on('click',function(){
			// if(CONF.TENANTUUID && CONF.TENANTUUID != ""){
			// 	LOCATION('./content/vm/vm_backup.php', 'vmbackup');
			// }else{
			// 	LOCATION('./content/vm/vmbackup.php', 'vmbackup');
			// }
			LOCATION('./content/vm/vmbackup.php', 'vmbackup');
		});
		
		//跳转到添加虚拟化中心
		$('#toaddvcenter').on('click',function(){
	    	LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
		});
		
		$('#verifyuser').on('click', function(){
			event.preventDefault();
			if(!usergroupTree) return;
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
			let p = {
				platform_uuid: vcenteruuid,
				hypervisor_type: hypervisor,
				group_uuid: groupuuid,
				group_name: groupname,
				username: username,
				password: password
			}
			Metronic.blockUI({target: '.groupdiv',animate: true});
			pAjaxRequest(p, "/api/v1/vm/platforms/group_user/verify", "POST", function (d) {
				Metronic.unblockUI('.groupdiv');
				if (operateResponseList(d)) {
					//初始化虚拟机信息
					//显示隐藏项目
					$('#vmconfigs').show();
					$('.dndiv').show();
					//更新节点账号密码信息,再次点击就不需要再输入了
					selectNode[0].username = username;
					selectNode[0].password = password;
					usergroupTree.updateNode(selectNode[0]);
					//初始化网络和存储
					initNetworkAndStoreGroupUser(selectNode[0]);
				}
			}, false);
		});
	}
	
	//初始化任务名
	var initTaskName = function(hypervisor){
		//设置任务名
		var data = {};
		data.hypervisor_type = hypervisor;
		data.instant_flag = true;
		pAjaxRequest(data, "/api/v1/vm/jobs/restore/job_name", "GET", function (d) {
			$('input[name=taskname]').val(d.data.info);
		}, false);
	}
	
	//初始化存储挂载点IP或域名
	var initBackupServerAddr = function(nodeuuid){
		var data = {};
		data.node_uuid = nodeuuid;
		pAjaxRequest(data, "/api/v1/nodes/all_ip", "GET", function (d) {
			var data = d.data.rows;
			var serveripaddr = $('#serveripaddr');
			serveripaddr.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i]).val(data[i]);
				serveripaddr.append(option);
			}
		}, false);
		$('input[name=backupserveraddr]').val(window.location.host);
	}
	
	//初始化备份时间点树
	var initPointTree = function() {
		let p = {};
		p.show_type = 1;//按虚拟机
		p.storage_uuid = $('#storageselect').val();
		p.instant_flag = true;
		p.manage_flag = false; //得到备份数据管理,获取checkbox
		p.data_flag = false; //备份数据标志
		Metronic.blockUI({target: '.two_tree',animate: true});
		pAjaxRequest(p, "/api/v1/vm/restore_data", "GET", function (d) {
			setPointTree(d.data.info);
		}, false);
	};
	
	var checkTreeNodeInfo = function(zNodes){
		if(zNodes.length == 0){
			$("#nopointtips").show();
			$('#vmtypetree').hide();
			$('#pointtypetree').hide();
			$('#pointshowtype').prop('disabled', true);
			$('#pointshowtype').selectpicker('refresh');
			$(".two_tree").hide();
			return false;
		}else{
			$("#nopointtips").hide();
			if($('#pointshowtype').val() == 1){
				$('#pointtypetree').show();
				$('#vmtypetree').hide();
			}else{
				$('#pointtypetree').hide();
				$('#vmtypetree').show();
			}
			$(".two_tree").show();
			$('#pointshowtype').prop('disabled', false);
			$('#pointshowtype').selectpicker('refresh');
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
					// beforeClick: nodeSelect,
					beforeClick: storageSelect,
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
	
	//初始化目的地树
	var initDesTree = function(hypervisor){
		$('.groupdiv').hide();
		var hypervisor = parseInt(hypervisor);
		//其他初始化目的宿主机树
		initHostTree(hypervisor);
		$('#selectHost').show();
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
	
	//替换特殊字符为空
	var clearStringEmpty = function (s){ 
	    var rs = ""; 
	    for (var i = 0; i < s.length; i++) { 
	        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, ''); 
	    } 
	    return rs;  
	}

	var storageSelect = function (treeId, treeNode, clickFlag) {
		if (4 == treeNode.type) {
			pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
		} else if (3 == treeNode.type) {
			pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
			pointtypetree.expandNode(treeNode, true);
		} else {
			pointtypetree.expandNode(treeNode, true);
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
		var storageuuid = $('#storageselect').val();
		if (null === nodeuuid) {
			nodeuuid = '';
		}
		var div = "#pointtypetree";
		let p = {
			task_uuid: treeNode.task_uuid,
			vm_uuid: treeNode.vm_uuid,
			hypervisor_type: treeNode.hypervisor_type,
			disabled_flag: false, //备份数据禁用勾选增量差异标志
			manage_flag: false,
			vm_check: treeNode.checked,
			node_uuid: nodeuuid,
			storage_uuid: storageuuid
		};
		Metronic.blockUI({target: div,animate: true});
		pAjaxRequest(p, "/api/v1/vm/restore_data/restore_points", "GET", function (d) {
			Metronic.unblockUI(div);
			if (d.success) {
				//success
				$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
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
		$('#vmconfigs').hide();
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
				//设置确定按钮可用
//				$('#submitbtn').prop('disabled', false);
			}
			initBackupServerAddr(allNodes[0].node_uuid);
		}

		//openstack虚拟化恢复提示
		if (CONF.VMTYPE_GROUP.OPENSTACK.includes(node.hypervisor_type)) {
			$('#selectOpenstackTip').show();
		} else {
			$('#selectOpenstackTip').hide();
		}

		initDesTree(node.hypervisor_type);
		initTaskName(node.hypervisor_type);
	}
	
	
	//初始化虚拟机配置V2,跨平台
	var initVMconfigV2 = function(node){
		var p = {};
		p.hypervisor_type = node.hypervisor;
		p.platform_uuid = node.vcuuid;
		p.host_uuid = node.id;

		var allPoints = currentTree.getCheckedNodes(true);
		var points = [];
		var pointsDetail = [];
		
		for(var i=0; i<allPoints.length; i++){
			var point = {};
			point.hypervisor = allPoints[i]['hypervisor_type'];
			point.timepointuuid = allPoints[i]['timepoint_uuid'];
			point.vcenteruuid = allPoints[i]['platform_uuid'];
			point.vmuuid = allPoints[i]['vm_uuid'];
			point.vmname = allPoints[i]['vm_name'] + "_" + clearStringEmpty(allPoints[i]['point_name']) + "_Instant_Restore";
			point.oldname = allPoints[i]['vm_name'];
			point.timepoint = allPoints[i]['create_time'];
			point.nodeuuid = allPoints[i]['node_uuid'];
			point.config = allPoints[i]['config'];
			points.push(allPoints[i]['timepoint_uuid']);
			pointsDetail.push(point);
		}
		p.points = points;
		p.points_detail = pointsDetail;
		p.instant_flag = true;
//		$('select[name=disktype]').empty();
		pAjaxRequest(p, "/api/v1/vm/timepoints/config", "POST", function (d) {
			var data = d.data;
			if(!data.flag){
				//时间点不存在
				if (50 == data.errorCode) {
					return UIToastr.showWarning(LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO, data.errorMsg);
				}
				//如果获取失败
				return UIToastr.showWarning(LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO, LANG.UI_VM_GETINFO_FAIL_TRY_AGIN);
			}
			vmPlugnInfoData = data;
			vmNameLimit = data.vmNameLimit;
			$('#accordionvm').vmRecoveryConfig(data);
			$('#vm0').collapse('toggle');	//展开
			
			//初始化网络和存储
			var _hypervisor = node.hypervisor_type;
			if (CONF.VM_TYPE.INCLOUDKVM == _hypervisor || CONF.VM_TYPE.INSPURVVDK == _hypervisor) {
				_VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\]<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+]");
				_VMNAMETIPS = LANG.UI_TOOLS_VMNAME_ICS_TIPS;
			}
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(_hypervisor)){
				initNetworkAndStoreGroupUser(node);
				$('.ipDiv').show();
			}else{
				initNetworkAndStore(node);
				$('.ipDiv').hide();
			}
			//启动方式默认卷启动不可选
			$('select[name=start_mode]').val(1).prop("disabled",true);
    	});
	}
	
	//初始化宿主机树
	var initHostTree = function(hypervisor){
		var p = {};
		p.hypervisor_type = hypervisor;
		p.instant_flag = true;	//瞬时恢复标志
		pAjaxRequest(p, "/api/v1/vm/jobs/restore/platforms", "GET", function (d) {
			setHostTree(d.data.rows);
		}, false);
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
	
	//添加虚拟化中心鼠标指上去事件
	var addHoverDom = function(treeId, treeNode) {
		if(1 != treeNode.type) return;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		var aObj = $("#" + nodeTID + "_a");
		if ($("#diyHref_" + nodeTID + nodeID + "_2").length>0) return;
		
		var hypervisor = treeNode.hypervisor;
		var str = "";
		if(CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)){
			//如果是类OpenStack,不展示刷新选项,因为OpenStack都是实时获取的,不需要再刷新
			str = "";
		}else{
			str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_VCENTER_SYNC + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>';
		}
		
		str += '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' + 
				  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
		aObj.after(str);
		
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
		if(1 != treeNode.type) return ;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		$("#diyHref_" + nodeTID + nodeID + "_1").unbind().remove();
		$("#diyHref_" + nodeTID + nodeID + "_2").unbind().remove();
		$("#diyHref_" + nodeTID + nodeID + "_3").unbind().remove();
		$("#diyBtn_space_" + escapeJquery(treeNode.id)).unbind().remove();
	};

	
	//检查没有宿主机切换提示信息
	var checkHostNodeInfo = function(zNodes, id){
		if(zNodes.length == 0){
			$("#nohosttips").show();
			$('.'+id).hide();
			return false;
		}else{
			$("#nohosttips").hide();
			$('.'+id).show();
			return true;
		}
	}
	
	//恢复目标树统一展开
	var recoveryNodeExpand = function(treeId, treeNode){
		var hypervisor = treeNode.hypervisor;
		if(CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)){
			//openstack按租户显示恢复目标
			return vcenterNodeExpand(treeId, treeNode);
		}else{
			//其他按宿主机显示
			return hostNodeExpand(treeId, treeNode);
		}
		
	}
	
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
					beforeExpand: recoveryNodeExpand,
				}
			};
		hostTree = $.fn.zTree.init($("#host_tree"), setting, zNodes);
	};
	
	//选择宿主机节点事件绑定
	var hostNodeSelect = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type){
			//不在线的宿主机，选中直接返回提示信息
			if(treeNode.online_flag == 2){
				UIToastr.showWarning(LANG.UI_RECOVERY_SELECT_HOST_TITLE, LANG.UI_RECOVERY_SELECT_HOST_TIPS);
				return;
			}
			hostTree.checkNode(treeNode, !treeNode.checked, false, true);
		}else{
			recoveryNodeExpand(treeId, treeNode);
			hostTree.expandNode(treeNode, true);
		}
	}

	//恢复目的分组展开
	var vcenterNodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			let p = {
				hypervisor_type: treeNode.hypervisor,
				platform_uuid: treeNode.id
			}
			Metronic.blockUI({target: '#host_tree',animate: true});
			pAjaxRequest(p, "/api/v1/vm/platforms/groups", "GET", function (d) {
				Metronic.unblockUI('#host_tree');
				if (d.success) {
					//success
					hostTree.addNodes(treeNode, d.data.rows, true);
				} else {
					operateResponseList(d);
				}
			})
		}else{
			return true;
		}
	}
	
	//选中宿主机节点事件绑定
	var hostOnCheck = function(e, id, node){
		var allNodes = hostTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			hostTree.checkNode(allNodes[i], false, false, false);	
		}
		hostTree.checkNode(node, true, false, false);
		
		//显示隐藏项目
		//显示隐藏项目
		$('#vmconfigs').show();
		$('.dndiv').show();
		
		$('#submitbtn').prop('disabled', true);
		
		//初始化虚拟机配置
		initVMconfigV2(node);
		
		//如果目的地虚拟化类型和时间点不一样,重新初始化任务名为目的虚拟化类型任务名
		initTaskName(node.hypervisor);
	}
	
	//初始化网络和存储
	var initNetworkAndStore = function(node){
		var p = {};
		p.hypervisor_type = node.hypervisor;
		p.platform_uuid = node.vcuuid;
		p.host_uuid = node.id;
		networkflag = false;
		onlineflag = false;
		Metronic.blockUI({target: '#accordionvmdiv',animate: true, cenrerY:true});
		_HOSTSTORAGE = [];
		$('select[name=hoststorage]').empty();
		$('select[name=hostnetwork]').empty();
		pAjaxRequest(p, "/api/v1/vm/host/network_storage", "GET", function (d) {
			Metronic.unblockUI('#accordionvmdiv');
			var data = d.data;
			var hoststorage = $('select[name=hoststorage]');
			hoststorage.empty();
			for(var i=0; i<data.storage.length; i++){
				var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
				hoststorage.append(option);
			}

			var vmList = vmPlugnInfoData.config;
			let _hypervisor = p.hypervisor_type;
			$.each(vmList, function (idx, val) {
				$.each(val.net_list, function (idxNet, valNet) {
					let hostnetwork = $('#accordionvm').find('.panel').eq(idx).find('select[name=hostnetwork]').eq(idxNet);
					hostnetwork.empty();
					for (var i = 0; i < data.network.length; i++) {
						// 有多个网卡且当前网卡是原网卡时显示提示
						let oriNetworkDes = '';
						var option = $("<option>");
						if (CONF.VM_TYPE.VMWARE == _hypervisor) {
							//vmware传网卡名称
							if (0 == data.network[i].uuid) {
								option.val('');
							} else {
								option.val(data.network[i].text);
							}
							if (data.network.length > 1 && valNet.network_name == data.network[i].text) {
								oriNetworkDes = `(${LANG.UI_RECOVERY_ORIGINAL_NETWORK})`;
								option.attr('selected', true);
							}
						} else {
							option.val(data.network[i].uuid);
							if (data.network.length > 1 && valNet.network_uuid == data.network[i].uuid) {
								oriNetworkDes = `(${LANG.UI_RECOVERY_ORIGINAL_NETWORK})`;
								option.attr('selected', true);
							}
						}
						option.text(data.network[i].text + oriNetworkDes);
						hostnetwork.append(option);
					}
				})
			});
			networkflag = true;
			if(data.network.length > 1){
				onlineflag = true;
			}

			// //如果虚拟化是SCP，禁止选择网络，暂不支持
			// if(node.hypervisor == CONF.VM_TYPE.SANGFORVVDK){
			// 	$('select[name="hostnetwork"]').prop('disabled', true);
			// }else{
			// 	$('select[name="hostnetwork"]').prop('disabled', false);
			// }
			
			$('#submitbtn').prop('disabled', false);
    	});
	}
	
	
	//初始化网络和存储(flexcloud,openstack)
	var initNetworkAndStoreGroupUser = function(node){
		let p = {
			platform_uuid: node.vcuuid,
			hypervisor_type: node.hypervisor_type,
			group_name: node.name,
			group_uuid: node.groupuuid,
			username: node.username,
			password: node.password
		};
		networkflag = false;
		onlineflag = false;
		Metronic.blockUI({target: '#accordionvmdiv',animate: true, cenrerY:true});
		pAjaxRequest(p, "/api/v1/vm/openstack/configs", "GET", function (d) {
			Metronic.unblockUI('#accordionvmdiv');
			var data = d.data;
			_HOSTSTORAGE = data;
			var hoststorage = $('select[name=hoststorage]');
			var hostnetwork = $('select[name=hostnetwork]');
			var useDomain = $('select[name=available_domain_select]');
			var diskDomain = $('select[name=disk_available_domain_select]');
			var instance = $('select[name=instance_socket]');
			hoststorage.empty();
			hostnetwork.empty();
			useDomain.empty();
			diskDomain.empty();
			instance.empty();
			var network = [];
			var instanceList = [];
			for(var i=0; i<data.storage.length; i++){
				var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
				hoststorage.append(option);
			}
			
			//虚拟机可用域
			for(var i=0; i<data.domain.length; i++){
				var option = $("<option>").text(data.domain[i].text).val(data.domain[i].uuid);
				useDomain.append(option);
			}
			
			//磁盘可用域
			for(var i=0; i<data.domain.length; i++){
				var option = $("<option>").text(data.domain[i].text).val(data.domain[i].uuid);
				diskDomain.append(option);
			}
			//实例
			for(var i=0; i<data.instance.length; i++){
				var option = $("<option>").text(data.instance[i].text).val(data.instance[i].uuid);
				var instanceInfo = data.instance[i].uuid.split('_');
				var instanceRootDisk = instanceInfo[2];
				instance.append(option);
				instanceList.push({
					uuid: data.instance[i].uuid,
					root_disk: instanceRootDisk,
					flavor_id: data.instance[i].flavor_id
				});
			}
			
			//选择虚拟机加载原来选择的网络
			var vmList = vmPlugnInfoData.config;
			for(var i=0;i<vmList.length;i++){
				var uuid = vmList[i].vm_uuid;
				var netList = vmList[i].net_list;
				//加载原虚拟机实例类型
				var diskList = vmList[i].disk_list;
				var rootDisk = parseInt(diskList[i].virtual_size_unit);
				//实例类型匹配规则：实例类型id相等
				$.each(instanceList, function (idx, v) {
					if (vmList[i].flavor_id == v.flavor_id) {
						$('.'+clearString(uuid) + " select[name=instance_socket]").val(v.uuid);
						return true;
					}
				})
				for(var j=0;j<netList.length;j++){
					hostnetwork = $('#accordionvm').find('.panel').eq(i).find('select[name=hostnetwork]').eq(j);
					hostnetwork.empty();
					for (var k = 0; k < data.network.length; k++) {
						// 有多个网卡且当前网卡是原网卡时显示提示
						let selected = '';
						let oriNetworkDes = '';
						if (data.network.length > 1 && netList[j].network_uuid == data.network[k].uuid) {
							selected = 'selected';
							oriNetworkDes = `(${LANG.UI_RECOVERY_ORIGINAL_NETWORK})`;
						}
						var option = $(`<option ${selected}>`).text(data.network[k].text + oriNetworkDes).val(data.network[k].uuid);
						hostnetwork.append(option);
						network.push(data.network[k].uuid);
					}
					if($.inArray(netList[j].network_uuid, network) != -1){
						//如果原来网络在当前宿主机里则复用原来的网络
						hostnetwork.val(netList[j].network_uuid);
					}
				}
			}
			
			//显示隐藏项目
			$('#vmconfigs').show();
			$('.dndiv').show();
			networkflag = true;
			if(data.network.length > 1){
				onlineflag =true;
			}
			
//			$('#submitbtn').prop('disabled', false);
    	}, false);
	 }
	
	//选择宿主机节点展开事件绑定
	var hostNodeExpand = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			let p = {
				platform_uuid: treeNode.id,
				pid: treeNode.hypervisor,
				nocheck_flag: false,
				refresh_flag: false
			}
			Metronic.blockUI({target: '#host_tree',animate: true});
			pAjaxRequest(p, "/api/v1/vm/jobs/restore/hosts", "GET", function (d) {
				Metronic.unblockUI('#host_tree');
				if (d.success) {
					hostTree.addNodes(treeNode, d.data.rows, true);
				} else {
					operateResponseList(d);
				}
			}, false);
		}else{
			return true;
		}
	}
	
	//选择宿主机节点展开事件绑定
	var hostRefresh = function(treeId, treeNode){
		if(1 == treeNode.type){
			let p = {
				platform_uuid: treeNode.id,
				pid: treeNode.hypervisor,
				nocheck_flag: false,
				refresh_flag: true
			}
			Metronic.blockUI({target: '#host_tree',animate: true});
			pAjaxRequest(p, "/api/v1/vm/jobs/restore/hosts", "GET", function (d) {
				Metronic.unblockUI('#host_tree');
				if (d.success) {
					$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
					$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
				} else {
					operateResponseList(d);
				}
			}, true);
		}else{
			return true;
		}
	}
	
    return {
        //main function to initiate the module
        init: function () {
			initStorage(); //初始化存储
        	initPointShowType();
        	initPointTree();
        	initListener();
        	handleValidation();
        },
    };
}();

jQuery(document).ready(function() {   
	VMInstantRecover.init();
});