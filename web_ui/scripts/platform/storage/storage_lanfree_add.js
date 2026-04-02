var StorageLanfreeAdd = function () {
	var initResourcetableFlag = false;
	var initWwntableFlag = false;
	var initLuntableFlag = false;
	var zTree, currnettreeNode,targetIqn;
	var allIscsiList = []; // 缓存所有扫描出来的二级列表
	var scanTargetFlag = false;	//扫描iscsi target
	var resultData = {};
	var iscsiData = {}; // 存储选中的iscsi
	var i = 0;
	var chatArr = [];
	
	//初始化节点选择
	var initNodeSelect = function(){
		pAjaxRequest({offset:0,limit:100}, "/api/v1/nodes/", "GET", function (result) {
			if(0 == result.data.total){
				$('select[name=storagetype]').prop('disabled', true);
				return;
			}
			var data = result.data.rows;
			var nodeselect = $('select[name=nodeselect]');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var name = data[i].host_name + '('+ data[i].ip +')';
				var option = $("<option>").text(name).val(data[i].node_uuid);
				nodeselect.append(option);
			}
		});
	}
	
	//添加一个ISCSI地址,多路径
	var moreiscsi = function(){
		i++;
		var html = '<div class="form-group" id="target'+ i +'">' + 
			'<div class="col-md-offset-3 col-md-3">' + 
				'<div class="input-icon right">' + 
					'<i class="fa"></i>' + 
					'<input type="text" maxlength="15" class="form-control" name="iscsiip" placeholder="192.168.1.10"/>' + 
				'</div>' + 
			'</div>' + 
			'<div class="col-md-1">' + 
				'<div class="input-icon right">' + 
					'<i class="fa"></i>' + 
					'<input type="text" maxlength="5" class="form-control" value="3260" name="iscsiport" placeholder="3260"/>' + 
				'</div>' + 
			'</div>' + 
			'<button type="button" style="margin-top: 3px;" id="delete'+ i +'" class="btn btn-sm green-haze">' +
			'<i class="viconfont vicon-ge_delete"></i> '+ LANG.UI_PUBLIC_DELETE +'</button>' +
		'</div>';
		$(this).closest('.form-group').after(html);
		$('#delete' + i).on('click', {id: i}, deleteTarget);

	}
	
	//删除iscsi target
	var deleteTarget = function(e){
	$('#target' + e.data.id).remove();
	}
	
	var morenfsConfig = function(){
		$('.nfsConfigDiv').show();
	}
	
	var morecifsConfig = function(){
		$('.cifsConfigDiv').show();
	}
	
	var addListeners = function(){
		$('select[name=nodeselect]').on('change', nodeChange);
		$('select[name=storagetype]').on('change', storageTypeChange);
		$('#cancelBut').on('click', function(){
			LOCATION('./content/platform/storage/storage_lanfree.php', 'infrastructure');
		});
		$('#addsubmit').on('click', addStorageModal);
		$('#iscsiscan').on('click', scanISCSI);
		$('#moreiscsi').on('click', moreiscsi);
		
		globalCheck();
		nfsDivCheck();
		cifsDivCheck();
		scanISCSICheck();
		
		$('#morenfsparams').on('click', morenfsConfig);
		$('#morecifsparams').on('click', morecifsConfig);
	}
	
	var nodeChange = function(){
		$('select[name=storagetype]').val('0');
		$('.asdiv').hide();
		$('.wwndiv').hide();
	}
	
	//添加lanfree存储
	var addLanfreeStorage = function(){
		Metronic.blockUI({target: '#addcontent',animate: true, cenrerY: true,});
		pAjaxRequest(resultData, "/api/v1/storages/lanfree", "POST", function (result) {
			Metronic.unblockUI('#addcontent');
			if (result.code == 200) {
				UIToastr.showSuccess(LANG.UI_STORAGE_ADD, result.message);
				LOCATION('./content/platform/storage/storage_lanfree.php', 'infrastructure');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);}
			}
		);
	}
	
	//添加存储按钮事件
	var addStorageModal = function(){
		var checkGlobalResult = $('#addnodeform').validate().form();
		if(!checkGlobalResult) return false;
		var storageType = parseInt($('select[name=storagetype]').val());
		resultData.node_uuid = $('select[name=nodeselect]').val();
		resultData.storagetype = storageType;
		resultData.rname = $('input[name=rname]').val();
		switch(storageType){
			case 4:
				if(!moreDivCheck()) return;
				var select = $('#resourcetable').bootstrapTable('getData');
				resultData.storagename = select[0]['storage_name'];
				resultData.pathlist = [select[0]['storage_name']];
				break;
			case 5:
				if(!iscsiDivCheck()) return;
				if(!checkAllIscsiIpPort()) return;
				var serverList = [];
				var ipInput = $('input[name=iscsiip]');
				var portInput = $('input[name=iscsiport]');
				for(var i=0; i<ipInput.length; i++){
					serverList[i] = {ip:$(ipInput[i]).val(), port:$(portInput[i]).val()};
				}
				resultData.serverlist = serverList;
				resultData.lun = iscsiData.id;
				var pathlist = [];
				pathlist.push(iscsiData.id)
				resultData.pathlist = pathlist;
				// 这里处理下门户chap认证和chap认证填写的信息
				if (chatArr[iscsiData.iscsi_target] != undefined) {
					resultData.discover_chap_username = chatArr[iscsiData.iscsi_target]['username'];
					resultData.discover_chap_password = chatArr[iscsiData.iscsi_target]['password'];
				}

				if (chatArr[iscsiData.pid] != undefined) {
					resultData.chap_username = chatArr[iscsiData.pid]['username'];
					resultData.chap_password = chatArr[iscsiData.pid]['password'];
				}
				// 取出所有未选择的iqn 列表
				for (var j in allIscsiList) {
					if (allIscsiList[j].target_iqn == iscsiData.pid) {
						// 删除选中的iqn
						allIscsiList.splice(j, 1);
					}
				}
				resultData.logout_iscsi_list = allIscsiList;
				resultData.iscsi_target = iscsiData.iscsi_target;
				resultData.target_iqn = iscsiData.pid;
				break;
			case 6:
				if(!$('#nfsdiv').validate().form()) return;
				resultData.host = $('#nfsdiv').find('input[name=host]').val();
				resultData.mountparams = $('#nfsConfig').val();
				break;
			case 7:
				if(!$('#cifsdiv').validate().form()) return;
				resultData.host = $('#cifsdiv').find('input[name=host]').val();
				resultData.mountparams = $('#cifsConfig').val();
				resultData.username = $('#cifsdiv').find('input[name=username]').val();
				resultData.password = $('#cifsdiv').find('input[name=password]').val();
				break;
			default:
				return;
		}
		addLanfreeStorage();
	}
	
	//全局输入验证
	var globalCheck = function(){
		$('#addnodeform').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	nodeselect: {
                    required: true,
                },
                storagetype:{
                	required: true,
                	storageType: true,
                }
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

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            }
        });
	}
	
	//通用存储输入验证
	var moreDivCheck = function(){
		var select = $('#resourcetable').bootstrapTable('getData');
		if(!select.length){
			UIToastr.showInfo(LANG.UI_STORAGE_SELECT_RESOURCE, LANG.UI_STORAGE_SELECT_RESOURCE_TIPS);
			return false;
		}
		return true;
	}
	
	//ISCSI输入验证
	var iscsiDivCheck = function(){
		if(!$('#iscsidiv').validate().form()) return false;
		if(!scanTargetFlag){
			//没有扫描
			UIToastr.showInfo(LANG.UI_STORAGE_ADD_ISCSI_TARGET, LANG.UI_STORAGE_ADD_TARGET_IQN);
			return false;
		}

		if(!iscsiData.id){
			UIToastr.showInfo(LANG.UI_STORAGE_ADD_TARGET_LUN, LANG.UI_STORAGE_ADD_TARGET_SELECT);
			return false;
		}
		resultData.lun = iscsiData.id;
		return true;
	}
	
	//nfs输入验证
	var nfsDivCheck = function(){
		$('#nfsdiv').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	host: {
                    required: true,
                    nfspath: true,
                }
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

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            }
        });
		
		$.validator.addMethod("nfspath", function(value, element) {
			var re1='((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])';	// IPv4 IP Address 1
			var re2='(:)';	// Any Single Character 1
			var re3='((?:\\/[\\w\\.\\-]+)+)';	// Unix Path 1
			var re4 = '(.*)';
			var p1 = new RegExp(re1+re2+re3,["i"]);
			var p2 = new RegExp(re4+re2+re3, ["i"]);
			var a = !!p2.exec(value);
		    return !!p1.exec(value) || !!p2.exec(value);
	    }, LANG.UI_STORAGE_ADD_PATH_TIPS);
	}
	
	//cifs输入验证
	var cifsDivCheck = function(){
		$('#cifsdiv').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	host: {
                    required: true,
                    cifspath: true,
                }
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

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            }
        });
		$.validator.addMethod("cifspath", function(value, element) {
			var re1='(\\/)';	// Any Single Character 1
			var re2='(\\/)';	// Any Single Character 2
			var re3='((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])';	// IPv4 IP Address 1
			var re4='((?:\\/[\\w\\.\\-]+)+)';	// Unix Path 1
			var p = new RegExp(re1+re2+re3+re4,["i"]);
		    return !!p.exec(value);
	    }, LANG.UI_STORAGE_ADD_PATH_TIPS);
	}
	
	//选择存储类型事件
	var storageTypeChange = function(){
		var value = parseInt($(this).val());
		$('.wwndiv').hide();
		$('#selecttips').hide();
		
		$('.nfsConfigDiv').hide();
		$('.cifsConfigDiv').hide();
		switch(value){
			case 0:
				UIToastr.showInfo(LANG.UI_STORAGE_SELECT_TYPE, LANG.UI_STORAGE_SELECT_TYPE_TIPS);
				return;
			case 4:
				initWwnNum();
				initMoreStorageDiv();
				initResourcetable();
				$('#selecttips').show();
				break;
			case 5:
				initISCSIDiv();
				break;
			case 6:
				$('#addsubmit').prop('disabled', false);
				initNFSDiv();
				break;
			case 7:
				initCIFSDiv();
				break;
		}
		getStorageName();
	}
	
	//得到存储的名字
	var getStorageName = function(){
		var data = {storage_type:$('select[name=storagetype]').val()};
		pAjaxRequest(data, "/api/v1/storages/name", "GET", function (result) {
			if (result.code == 0) {
				$('input[name=rname]').val(result.data.name);
			} else {
				UIToastr.showError(LANG.UI_STORAGE_ADD, result.message);
			}
		});
	}
	
	//初始化节点WWN号
	var initWwnNum = function(){
		$('.wwndiv').show();
		var table = $("#wwntable");
		if(!initWwntableFlag){
			initWwntableFlag = true;
			var options = {
				pagination:false,
				resizable: false,
				singleSelect:true,
				vin_url:"/api/v1/storages/wwn",
				vin_method:"GET",
				vin_params: function () {
					let params = {};
					params.node_uuid = $('select[name=nodeselect]').val();
					return params;
				},
				columns:[{
					checkbox:true,
					sortable: false,
				},
					{
						field: 'num',
						title: LANG.UI_PUBLIC_TABLE_ID,
						sortable: false,
						align: 'center',
					},
					{
						field: 'host_name',
						title: LANG.UI_STORAGE_CHANNEL,
						sortable: false,
						align: 'center',
					},
					{
						field: 'wwnn',
						title: LANG.UI_STORAGE_FC_WWNN,
						sortable: false,
						align: 'center',
					},
					{
						field: 'wwpn',
						title: LANG.UI_STORAGE_FC_WWPN,
						sortable: false,
						align: 'center',
					},
					{
						field: 'speed',
						title: LANG.UI_STORAGE_FC_SPEED,
						sortable: false,
						align: 'center',
					},
					{
						field: 'status',
						title: LANG.UI_PUBLIC_STATUS,
						sortable: false,
						formatter: function (index, row) {
							var levelClass = '';
							switch (row.status) {
								case 1:
									levelClass = "label-success";
									break;
								case 2:
									levelClass = "label-warning";
									break;
								default:
									levelClass = "label-info";
									break;
							}

							return '<span class="label label-sm '+ levelClass +' "> ' + row.status_des + ' </span>';
						}
					},
				],
			}
			table.baseTableConfig().init(options);
		}else{
			table.bootstrapTable('refresh');
		}
	}
	
	//初始化和更新存储资源表格
	var initResourcetable = function(){
		$('#addsubmit').prop('disabled', true);
		var table = $("#resourcetable");
		if(!initResourcetableFlag){
			initResourcetableFlag = true;
			var options = {
				pagination:false,
				resizable: false,
				singleSelect:true,
				vin_url:"/api/v1/storages/table",
				vin_method:"GET",
				vin_params: function () {
					let params = {};
					params.node_uuid = $('select[name=nodeselect]').val();
					params.storage_type = $('select[name=storagetype]').val();
					return params;
				},
				columns:[{
					checkbox:true,
					sortable: false,
				},
					{
						field: 'storage_name',
						title: LANG.UI_STORAGE_NAME,
						sortable: false,
						align: 'center',
					},
					{
						field: 'description',
						title: LANG.UI_STORAGE_TYPE,
						sortable: false,
						align: 'center',
					},
					{
						field: 'size',
						title: LANG.UI_STORAGE_SIZE,
						sortable: false,
						align: 'center',
					},
				],
				onPostBody: function () {
					var tableData = table.bootstrapTable('getData');
					if (tableData.length > 0) {
						$('#addsubmit').prop('disabled', false);
					}
				},
			}
			table.baseTableConfig().init(options);
		}else{
			table.bootstrapTable('refresh');
		}
	}
	
	//检查所有的iscsi ip和端口是否符合规则
	var checkAllIscsiIpPort = function(){
		var ipInput = $('input[name=iscsiip]');
		var portInput = $('input[name=iscsiport]');
		var ip = [];
		var result = true;
		for(var i=0; i<ipInput.length; i++){
			result = result && $(ipInput[i]).valid();
			result = result && $(portInput[i]).valid();
			if(!result) return false;
			if(-1 == $.inArray($(ipInput[i]).val(), ip)){
				ip.push($(ipInput[i]).val());
			}else{
				//如果IP有重复的,提示并退出
				UIToastr.showWarning(LANG.UI_STORAGE_ISCSI_IP_TITLE, LANG.UI_STORAGE_ISCSI_IP_TIPS);
				return false;
			}
		}
		return result;
	}
	
	//初始化和更新lun表格
	var initTargetLuntable = function(){
		if(!checkAllIscsiIpPort()) return false;
		var p = {};
		p.server_list = [];
		var ipInput = $('input[name=iscsiip]');
		var portInput = $('input[name=iscsiport]');
		for(var i=0; i<ipInput.length; i++){
			p.server_list[i] = {ip:$(ipInput[i]).val(), port:$(portInput[i]).val()};
		}
		p.node_uuid = $('select[name=nodeselect]').val();
		p.type = $('select[name=storagetype]').val();
		//$('#iscsiscan').prop('disabled', true);
		Metronic.blockUI({
			target: '#addcontent',
			animate: true,
			cenrerY: true,
		});
		initLuntableFlag = false;

		pAjaxRequest(p, "/api/v1/storages/lun", "POST", function (result) {
			if (result.code == 0) {
				initUserTree(result.data);
			} else {
				Metronic.unblockUI('#addcontent');
				UIToastr.showError(LANG.UI_STORAGE_TYPE_ISCSI, result.message);
			}
		});
	}

	//初始化权限树
	var initUserTree = function(userInfo){
		Metronic.unblockUI('#addcontent');
		if(!initLuntableFlag){
			var setting = {
				check: {
					enable: true,
					chkStyle: "radio",
					radioType: "all",
					nocheckInherit: false,
					chkboxType: {
						"Y": "",
						"N": ""
					}
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
						pIdKey: "pid",
						rootPId: 0
					},
					key: {
						title: "name"
					}
				},
				callback: {
					beforeClick: nodeSelect,
					onCheck: vmOnCheck,
					beforeExpand: nodeExpand
				}
			};
			var nodes = userInfo.rows;
			for (var j in nodes) {
				nodes[j]['isParent'] = nodes[j]['is_parent'];
			}
			$('.target').show();
			if (userInfo.total > 0) {
				$("#permissionTree").html('');

				zTree = $.fn.zTree.init($("#permissionTree"), setting, nodes);
				initLuntableFlag = true;
				// 这里处理下所有的 desc下面的iscsi_target 和 pid
				for (var j in nodes) {
					if (nodes[j].pid == 0) {
						let temp = {
							target_iqn: nodes[j].id,
							iscsi_target: nodes[j].iscsi_target
						};
						allIscsiList.push(temp);
					}
				}
				$('#addsubmit').prop('disabled', false);
			} else {
				$("#permissionTree").html('<li style="\n' +
					'    line-height: 30px;\n' +
					'    padding-left: 10px;\n' +
					'">'+LANG.UI_TOOLS_NO_DATA +'</li>');

				$('#iscsiscan').prop('disabled', false);
				return UIToastr.showInfo(LANG.UI_STORAGE_TYPE_ISCSI, LANG.UI_SYSTEM_MONITOR_NULL_DATA);
			}

		}
		$('.target').show();
		scanTargetFlag = true;
	}

	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow && !treeNode.children){
			// 异步更新子节点
			// 显示chap认证
			currnettreeNode = treeNode;
			$('#modal-iscsi-chap-div').modal();
			if (currnettreeNode.pid == 0) {
				// 第一次认证
				$('#modal-iscsi-chap-div .modal-header h4').html(LANG.UI_STORAGE_CHAP_AUTH_SET);
			} else {
				$('#modal-iscsi-chap-div .modal-header h4').html(LANG.UI_STORAGE_CHAP_AUTH);
			}
		}else{
			return true;
		}

	}
	$('#iscsi-chap-submit').on('click',function (){
		var username = $('#iscsi-chap-username').val();
		var passwd = $('#iscsi-chap-userpwd').val();
		if (username == '') {
			return UIToastr.showInfo(LANG.UI_ISCSI_CHAT_AUTH, LANG.UI_ISCSI_CHAT_USERNAME_TIPS);
		}
		if (passwd == '') {
			return UIToastr.showInfo(LANG.UI_ISCSI_CHAT_AUTH, LANG.UI_ISCSI_CHAT_PASSWORD_TIPS);
		}

		var p = {};
		p.username = username;
		p.passwd = passwd;
		p.iscsi_target = currnettreeNode.iscsi_target;
		p.target_iqn = currnettreeNode.id;
		p.node_uuid = $('select[name=nodeselect]').val();
		getChap(p);
	});
	var getChap = function (p){
		Metronic.blockUI({
			target: '#modal-iscsi-chap-div',
			animate: true,
			cenrerY: true,
		});
		// 存储下本次输入的信息，后续需要传给后台
		var message = {
			'username': $('#iscsi-chap-username').val(),
			'password': $('#iscsi-chap-userpwd').val()
		};
		var func = 'chap_auth_again';
		var keys = currnettreeNode.id;
		if (currnettreeNode.pid == 0) {
			// 第一次认证
			func = 'chap_auth';
			keys = currnettreeNode.iscsi_target;
		}
		chatArr[keys] = message;

		pAjaxRequest(p, "/api/v1/storages/"+func, "GET", function (result) {
			Metronic.unblockUI('#modal-iscsi-chap-div');
			if (result.code == 0) {
				targetIqn = currnettreeNode.id; // 记住当前请求的iqn
				currnettreeNode.clickshow  = false;
				var nodes = result.data.rows;
				for(var k in nodes){
					nodes[k].isParent = nodes[k].is_parent;
				}
				zTree.addNodes(currnettreeNode, nodes, true);
				zTree.expandNode(currnettreeNode, true);
				for (var j in nodes) {
					if (nodes[j].pid != 0 && nodes[j].is_parent == true) {
						let temp = {
							target_iqn: nodes[j].id,
							iscsi_target: nodes[j].iscsi_target
						};
						allIscsiList.push(temp);
					}
				}
				// 清空信息
				$('#iscsi-chap-username').val('');
				$('#iscsi-chap-userpwd').val('');
				$('#modal-iscsi-chap-div').modal('hide');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_CHAP_AUTH, result.message);
			}
		});
	}

	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(!treeNode.isParent) return;//不存在子节点不展开
		nodeExpand(treeId, treeNode);
	}
	var vmOnCheck = function(e, id, node){
		iscsiData = node; // 存储选中的iscsi信息
	}

	//初始化iscsi名称
	var initISCSIName = function(){
		var data = {node_uuid:$('select[name=nodeselect]').val(), type:$('select[name=storagetype]').val()};
		pAjaxRequest(data, "/api/v1/storages/iscsi", "GET", function (result) {
			if (result.code == 0) {
				$('#iscsiname').html(result.data.name);
			} else {
				UIToastr.showError(LANG.UI_VOL_CDP_BACKUP_TIPS, result.message);
			}
		});
	}
	
	//扫描ISCSI检查
	var scanISCSICheck = function(){
		$('#iscsidiv').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	iscsiip: {
                    required: true,
                    ipv4Ordomain: true,
                },
                iscsiport:{
                	required: true,
                	iscsiport: true,
                }
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

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            }
        });
		
		$.validator.addMethod("ipv4Ordomain", function(value, element) {
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4;
	    }, LANG.UI_TOOLS_IP_OR_DOMAIN);
			
		$.validator.addMethod("iscsiport", function(value, element) {
			if(!$(element).closest('.form-group').hasClass('has-success'))return false;
			if(value >= 0 && value <= 65535){
				return true;
			}
			return false;
	    }, LANG.UI_NODE_PORT_TIPS);
	}

	//扫描ISCSI
	var scanISCSI = function(){
		if(!$('#iscsidiv').validate().form()) return;
		initTargetLuntable()
	}
	
	//初始化通用存储DIV
	var initMoreStorageDiv = function(){
		$('.asdiv').hide();
		$('#morestoragediv').show();
	}
	
	//初始化ISCSI DIV
	var initISCSIDiv = function(){
		initISCSIName();
		$('.asdiv').hide();
		$('#iscsidiv').show();
		
		$('#iscsiip').val('').prop('disabled', false);
		$('#iscsiport').val('').prop('disabled', false);
		$('.target').hide();
		scanTargetFlag = false;
	}
	
	//初始化NFS DIV
	var initNFSDiv = function(){
		$('.asdiv').hide();
		$('#nfsdiv').show();
	}
	
	//初始化CIFS DIV
	var initCIFSDiv = function(){
		$('.asdiv').hide();
		$('#cifsdiv').show();
	}
	
	$.validator.addMethod("storageType", function(value, element) {
		if(value > 0){
			return true;
		}
		return false;
    }, LANG.UI_STORAGE_SELECT_TYPE);
	
    return {
        //main function to initiate the module
        init: function () {
        	initNodeSelect();
        	addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
	StorageLanfreeAdd.init();
});