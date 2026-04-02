var VcenterDetails = function () {
	var sub_module_type = 'public' == $('#cloudType').val() ? 3 : 2; // 子模块类型
	var showType = 1;
	var grid = $('#vm_table');
	var gridInitFlag = false;
	var selectNode;
	//定义三棵树:主机和虚拟机,主机和集群,虚拟机和模板,搜索到的树节点
	var zTreeHC;
	var currentTree;	//当前显示的树
	var currentNode;
	var currentNodeArr;
	//时间选择器全局变量,方便提交搜索的时候直接使用
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	
	var setTree = function(nodes){
		var setting = {
			check: {
				enable: true,
				chkStyle: 'radio',
				radioType: 'all',
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					idKey: "treeId",
					pIdKey: "pid",
					rootPId: 0
				}
			},
			view: {
				addDiyDom: addHoverDom,
			},
			callback: {
				beforeClick: nodeSelect,
				onCheck: nodeCheck,
				beforeExpand: nodeExpand,
			}
		};
		zTreeHC = $.fn.zTree.init($("#vm_tree_hc"), setting, nodes);
		
		currentTree = zTreeHC;
		
		initNodeSelect(zTreeHC);
	};
	
	//初始化选中,当从虚拟化中心管理链接过来的时候,选中某个节点
	var initNodeSelect = function(zTree){
		var vcenteruuid = $('#vcuuid').val();
		if(!vcenteruuid) return;
		var treeNode = zTree.getNodeByParam("id", vcenteruuid, null);
		nodeSelect("vm_tree_hc", treeNode);
	}
	
	//添加虚拟化中心鼠标指上去事件
	var addHoverDom = function(treeId, treeNode) {
		if(!treeNode.flush) return;
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		var aObj = $("#" + nodeTID + "_a");
		if ($("#diyHref_" + nodeTID + nodeID + "_1").length>0) return;
		if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD === sub_module_type && CONF.PERMISSION_ARR.includes('p_cloud_platform_private_manager_sync')
			|| CONF.VM_SUB_MODULE.PUBLIC_CLOUD === sub_module_type && CONF.PERMISSION_ARR.includes('p_cloud_platform_manager_sync')) {
			var str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>';
			aObj.after(str);
		}
		var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
		
		if (hrefRefresh) hrefRefresh.bind("click", function(){
			getSyncVcenterInfo(treeId, treeNode, true);
		});
	};
	
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
	
//	//添加虚拟化中心鼠标移除事件
//	var removeHoverDom = function(treeId, treeNode) {
//		var nodeID = escapeJquery(treeNode.id);
//		$("#diyHref_" + nodeID + "_1").unbind().remove();
//		$("#diyBtn_space_" + nodeID).unbind().remove();
//	};
	
	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag){
		var showType = parseInt($('#vmshowtype').val());
		Metronic.blockUI({target: '.three_tree',animate: true});
		let p = {
			platform_uuid: treeNode.platform_uuid,
			platform_flag: treeNode.platform_flag,
			hypervisor_type: treeNode.hypervisor_type,
			display_mode: showType,
			refresh_flag: refreshFlag,
			show_vm_flag: false,
		};
		pAjaxRequest(p, "/api/v1/vm/platforms/vm_tree", "GET", function (data) {
			Metronic.unblockUI('.three_tree');
			if (data.success) {
				let nodes = data.data.rows;
				$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, nodes, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
				handleRecords(treeId, treeNode);
			}
		}, true);
	}
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		treeNode.checked = true;
		$.fn.zTree.getZTreeObj(treeId).updateNode(treeNode);
		//如果此节点是虚拟化中心或宿主机
		if(!treeNode) return;
		if(treeNode.type == 0){
			return;
		}else if(1 == treeNode.type || 2 == treeNode.type){
			if (!treeNode.children) {
				nodeExpand(treeId, treeNode);
			}
			handleRecords(treeId, treeNode);
		}else{
			//加载表格
			handleRecords(treeId, treeNode);
		}
	}

	const nodeCheck = function (e, treeId, treeNode) {
		if (treeNode.checked) {
			$(".curSelectedNode").removeClass("curSelectedNode");
			nodeSelect(treeId, treeNode);
		}
	}
	
	var nodeExpand = function(treeId, treeNode){
		if(!treeNode.flush || treeNode.children) return true;
		getSyncVcenterInfo(treeId, treeNode, false);
	}
	
	var initTree = function() {
		let p = {};
		p.display_mode = parseInt($('#vmshowtype').val());
		p.platform_uuid = $('#vcuuid').val();
		p.cloud_flag = true;
		p.cloud_type = $('#cloudType').val();
		pAjaxRequest(p, "/api/v1/vm/platforms/tree", "GET", function (data) {
			setTree(data.data.info);
		}, true);
	};
	
	var addToBackupTask = function(row){
		$('#modaltips').hide();
		var params = {};
		params.platform_uuid = row.platform_uuid;
		$('#vmname').html(row.vm_name);
		$('#vmuuid').val(row.vm_uuid);
    	$('#vcenteruuid').val(row.platform_uuid);
    	$('.vmnameDiv').show();
		pAjaxRequest(params, "/api/v1/vm/platforms/current_jobs", "GET", function (data) {
			var task = data.data.rows;
			var select = $('#task');
			if(task.length == 0) {
				select.empty();
				$('#modaltips').show();
				return;
			}
			var option = "";
			for(var i=0; i<task.length; i++){
				option += '<option value="' + task[i].uuid + '">' + task[i].name + '</option>';
			}
			select.empty();
			select.append(option);
			select.selectpicker('refresh');
		}, false);
		$('#modaldiv').modal();
	}

	//单个创建新的备份任务
	var createNewBackupTask = function (row) {
		var vmuuid = row.details.vm_uuid;
		var vcenteruuid = row.details.platform_uuid;
		var vmshowtype = row.details.display_mode;
		var hypervisor = row.details.hypervisor_type;
		let url;
		if (2 == sub_module_type) {
			url = './content/vm/vmbackup.php?sub_module_type=2&showtype=' + vmshowtype + '&vmuuid=' + vmuuid + '&vcenteruuid=' + vcenteruuid + '&hypervisor=' + hypervisor;
			LOCATION(url, 'prcloud_protect');
		} else {
			url = './content/aws/awsbackup.php?vmuuid=' + vmuuid + '&vcenteruuid=' + vcenteruuid + '&hypervisor=' + hypervisor;
			LOCATION(url, 'awsprotect');
		}
	}

	//多个创建新的备份任务
	var createMultNewBackupTask = function(){
		var selectedRow = grid.bootstrapTable('getSelections');
		var vmuuid = [];
		var vcenteruuid;
		var vmshowtype;
		var hypervisor;
		for (var i = 0; i < selectedRow.length; i++) {
			if (vcenteruuid && selectedRow[i].details.platform_uuid != vcenteruuid) {
				return UIToastr.showWarning(LANG.UI_VM_REPORT_CREATE_NEW_TASK, LANG.UI_VM_REPORT_NOT_SAME_VCENTER_INSTANCE_TIPS);
			}
			if (!vcenteruuid) {
				vcenteruuid = selectedRow[i].details.platform_uuid;
				vmshowtype = selectedRow[i].details.display_mode;
				hypervisor = selectedRow[i].details.hypervisor_type;
			}
			vmuuid.push(selectedRow[i].details.vm_uuid);
		}
		let url;
		if (2 == sub_module_type) {
			url = './content/vm/vmbackup.php?sub_module_type=2&showtype=' + vmshowtype + '&vmuuid=' + vmuuid + '&vcenteruuid=' + vcenteruuid + '&hypervisor=' + hypervisor;
			LOCATION(url, 'prcloud_protect');
		} else {
			let url = './content/aws/awsbackup.php?vmuuid=' + vmuuid + '&vcenteruuid=' + vcenteruuid + '&hypervisor=' + hypervisor;
			LOCATION(url, 'awsprotect');
		}
	}
	
    var handleRecords = function (treeId, node) {
    	var allNode = $.fn.zTree.getZTreeObj(treeId).transformToArray(node);
    	var nodeArr = [];
    	for(var i=0; i<allNode.length; i++){
    		nodeArr[i] = allNode[i].id;
    	}

		currentNode = node;
		currentNodeArr = nodeArr;
    	
		if(!gridInitFlag){
			let options = {
				vin_url: '/api/v1/vm/overview/list',
				vin_method: 'POST',
				queryParamsType: 'limit',
				queryParams: function (p) {
					return {
						limit: length ? length : p.limit,
						offset: p.offset,
						order: p.order,
						sort: p.sort,
						sub_module_type: sub_module_type,
						accurate_flag: false,
						search_value: $.trim($('#search').val()),
						platform_uuid : node.platform_uuid,
						hypervisor_type : node.hypervisor_type,
						display_mode : showType,
						node : JSON.stringify(nodeArr),
						platform_flag: node.platform_flag,
					};
				},
				pagination: true,
				sidePagination: 'server',
				pageNumber: 1,
				pageSize: 20,
				pageList: [10, 20, 50, 100, 150, 200],
				paginationLoop: false,
				uniqueId: 'vm_uuid',
				lineHeight: '60px',
				sortable: true,
				sortName: 'vm_name',
				sortOrder: 'asc',
				resizable: true,
				showColumns: true,
				buttonsToolbar: '.vm_report_toolbar',
				showExport: true,// 显示导出按钮
				exportDataType: "basic",// 导出类型
				exportTypes: ['xlsx', 'csv'],// 导出文件类型
				exportOptions: {
					fileName: sub_module_type === 3 ? LANG.UI_OVERVIEW_PUBLIC_CLOUD_EXPORT_FILE_NAME : LANG.UI_OVERVIEW_PRIVATE_CLOUD_EXPORT_FILE_NAME,
					ignoreColumn: ['operations'] , // 排除操作列
				},
				onRefresh: function (params) {
					$("#vm_table").bootstrapTable('hideLoading');
				},
				onCheck: function (res) {
					var selectedRow = grid.bootstrapTable("getSelections");
					$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
					if (selectedRow.length == 0) {
						$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('');
					} else if (selectedRow.length > 0) {
						$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
					}
				},
				onUncheck: function (row, $element) {
					var selectedRow = grid.bootstrapTable("getSelections");
					if (selectedRow.length == 0) {
						$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
						$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('');
					} else if (selectedRow.length > 0) {
						$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
					}
				},
				onCheckAll: function () {
					var selectedRow = grid.bootstrapTable("getSelections");
					$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checked.svg')");
					$('#vm_table .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_JOB_SELECTED_ROWS + selectedRow.length + '');
				},
				onUncheckAll: function () {
					var selectedRow = grid.bootstrapTable("getSelections");
					$('#vm_table thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
				},

				columns: [
					{
						field: 'checkbox',
						checkbox: true,
						sortable: false, //默认可排序，禁用排序才写此项
						width: 3,
						widthUnit: '%',
						formatter: function (value, data) {
							//已在备份任务则禁用
							return {disabled: data.backup_flag};
						}
					},
					{
						field: 'vm_name',
						title: LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME,
						width: 15,
						widthUnit: '%',
						formatter: function (value, data) {
							if (data.backup_flag) {
								return '<a title="' + data.dir_path + '" class="colorgreen"><i class="fa fa-check"></i> ' + value + '</a>';
							}
							return '<a title="' + data.dir_path + '" class="colorgreen">' + value + '</a>';
						}
					},
					{
						field: 'power_state',
						title: LANG.UI_PUBLIC_STATUS,
						width: 5,
						widthUnit: '%',
						formatter: function (value) {
							let power_state = '';
							switch (value) {
								case 1:
									power_state = '<span class="label label-sm label-default">' + LANG.UI_CLOUD_PLATFORM_POWER_OFF + '</span>';
									break;
								case 2:
									power_state = '<span class="label label-sm label-success">' + LANG.UI_CLOUD_PLATFORM_POWER_ON + '</span>';
									break;
								case 3:
									power_state = '<span class="label label-sm label-info">' + LANG.UI_CLOUD_PLATFORM_POWER_SUSPENDED + '</span>';
									break;
								case 4:
									power_state = '<span class="label label-sm label-info">' + LANG.UI_CLOUD_PLATFORM_POWER_PAUSE + '</span>';
									break;
								default:
									power_state = '<span class="label label-sm label-warning">' + LANG.UI_CLOUD_PLATFORM_UNKNOWN + '</span>';
							}
							return power_state;
						}
					},
					{
						field: 'vm_ip',
						title: LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_IP,
						width: 10,
						widthUnit: '%',
					},
					{
						field: 'platform_ip',
						title: LANG.UI_OVERVIEW_PUBLIC_CLOUD_PLATFORM,
						width: 10,
						widthUnit: '%',
					},
					{
						field: 'backup_flag',
						title: LANG.UI_SEARCH_BACKUP_STATUS,
						width: 8,
						widthUnit: '%',
						formatter: function (value, data) {
							if (data.backup_flag) {
								return '<span class="colorgreen"><i class="fa fa-check"></i> ' + LANG.UI_VM_REPORT_IN_BACKUP + '</span>';
							}
							return LANG.UI_VM_REPORT_NOIN_BACKUP;
						}
					},
					{
						field: 'last_backup_time',
						title: LANG.UI_VM_REPORT_LAST_BACKUP_TIME,
						width: 10,
						widthUnit: '%',
					},
					{
						field: 'backup_job',
						title: LANG.UI_SEARCH_BACKUP_TASK,
					},
					{
						field: 'backup_count',
						title: LANG.UI_STORAGE_TIMEPOINT_NUM,
						width: 8,
						widthUnit: '%',
					},
					{
						field: 'backup_storage',
						title: LANG.UI_VM_REPORT_STORAGE_SPACE,
						width: 8,
						widthUnit: '%',
					},
					{
						field: 'user_name',
						title: LANG.UI_VM_REPORT_USERNAME,
						width: 8,
						widthUnit: '%',
					},
					{
						field: 'operations',
						title: LANG.UI_PUBLIC_OPERATION,
						width: 10,
						widthUnit: '%',
						sortable: false,
						clickToSelect: false,
						events: {
							'click .backup': function (event, value, row, index) {
								checkOperateAuth(checkAuth('add', row.user_uuid), () => {
									addToBackupTask(row);
								});
							},
							'click .newbackup': function (event, value, row, index) {
								checkOperateAuth(checkAuth('create', row.user_uuid), () => {
									createNewBackupTask(row);
								});
							},
						},
						formatter: function (value, data, index) {
							if (!value.length
								|| CONF.VM_SUB_MODULE.PRIVATE_CLOUD === sub_module_type && !CONF.PERMISSION_ARR.includes('p_cloud_platform_private_vm_overview_vmoperate')
								|| CONF.VM_SUB_MODULE.PUBLIC_CLOUD === sub_module_type && !CONF.PERMISSION_ARR.includes('p_cloud_platform_vm_overview_vmoperate')
							) {
								return '';
							}
							let button = '<div class="btn-group positionabs" style="margin-top: -15px">';
							if (index > 4) {
								button = '<div class="btn-group positionabs dropup" style="margin-top: -15px">';
							}
							button += '<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" ' +
								'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
								'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
								'</button>' +
								'<ul class="dropdown-menu min-width100" role="menu">';
							$.each(data.operations, function (i, d) {
								switch(d){
									case 4:
										button += '<li class="backup"><a href="javascript:;"><i class="viconfont vicon-ge_add_task"></i> ' + LANG.UI_VCENTER_VM_ADD_TO_TASK + '</a></li>';
										break;
									case 5:
										button += '<li class="newbackup"><a href="javascript:;"><i class="viconfont vicon-backup"></i> ' + LANG.UI_VM_REPORT_CREATE_NEW_TASK + '</a></li>';
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
		}else{
			//刷新表格
			searchVM();
			$('#task').selectpicker('refresh');
		}
		//标记选中的节点
		selectNode = node;
    	return;
    }

	var searchVM = function(){
		grid.bootstrapTable('refreshOptions', {
			queryParams: function (queryParams) {
				return {
					limit: queryParams.limit,
					offset: queryParams.offset,
					order: queryParams.order,
					sort: queryParams.sort,
					sub_module_type: sub_module_type,
					accurate_flag: false,
					search_value: $.trim($('#search').val()),
					node : JSON.stringify(currentNodeArr),
					platform_flag: currentNode.platform_flag ? currentNode.platform_flag : false,
					platform_uuid : currentNode.platform_uuid ? currentNode.platform_uuid : currentNode.vcenteruuid,
					hypervisor_type : currentNode.hypervisor_type ? currentNode.hypervisor_type: currentNode.hypervisor,
					display_mode : showType,
				};
			}
		});
	}
	
	//添加事件
    var addListeners = function(){
    	$('#submit').on('click', addTotaskOpt);
    	//多选虚拟机添加到备份任务
		$('#addtojob').unbind().on('click', () => {
			let authParams = checkAuth('addMulti');
			if (authParams) {
				checkOperateAuth(authParams, addMultToBackupTask);
			}
		});
		//多选虚拟机创建备份任务
		$('#createjob').unbind().on('click', () => {
			let authParams = checkAuth('createMulti');
			if (authParams) {
				checkOperateAuth(authParams, createMultNewBackupTask);
			}
		});

		$('#searchbtn').on('click', searchVM);
		$('#search').on('blur', function () {
			$(this).prop('placeholder', LANG.UI_OVERVIEW_PUBLIC_CLOUD_SEARCH_TIPS);
		});
		$('#search').keypress(function (e) {
			if (e.which == 13) {
				searchVM();
			}
		});
		$('#clearSearchBtn').on('click', function () {
			$('#search').val('');
			$("#vm_table").bootstrapTable("refresh");
		});

		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'300px'});
		});

		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			$('#search').val('');
			var p = {};
			p.startTime = _daterangepicker_starttime; //时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//时间查询范围结尾
			// p.hypervisor = $('#searchmodal #vmtype').val();
			p.backupFlag = $('#searchmodal #backupFlag').val();

			p.taskName = $('#searchmodal #taskName').val();
			p.vmIP = $('#searchmodal #vmIP').val();
			p.vmName = $('#searchmodal #vmName').val();
			p.vmCluster = '';
			// p.vcenteruuid = $('#searchmodal #vcenterSelect').val();
			p.hostName = '';
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort,
						sub_module_type: sub_module_type,
						accurate_flag: true,
						backup_status: p.backupFlag,
						vm_name: p.vmName,
						vm_ip: p.vmIP,
						host_name: p.hostName,
						vm_cluster: p.vmCluster,
						job_name: p.taskName,
						start_time: p.startTime,
						end_time: p.endTime,
						node : JSON.stringify(currentNodeArr),
						platform_flag: currentNode.platform_flag ? currentNode.platform_flag : false,
						platform_uuid : currentNode.platform_uuid ? currentNode.platform_uuid : currentNode.vcenteruuid,
						hypervisor_type : currentNode.hypervisor_type ? currentNode.hypervisor_type: currentNode.hypervisor,
						display_mode : showType,
					};
				}
			});
		});
    }

	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		var vmName = xssEncode($('#vmName').val());
		var vmIP = xssEncode($('#vmIP').val());
		var vmCluster = xssEncode($('#vmCluster').val());
		var taskName = xssEncode($('#taskName').val());
		var hostName = xssEncode($('#hostName').val());
		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> '+LANG.UI_SEARCH_TIME_RANGE+': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
		if(p.vmName){
			info += '<span id="vmName" style="position: relative"><span id="vmNameDetail" style="display: none;position: absolute;bottom: -40px;left: 10px;background: white"> ' + vmName + '</span> '+LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME+': <i>' + vmName + '</i><em>X</em></span>';		}
		if(p.vmIP){
			info += '<span id="vmIP" style="position: relative"><span id="vmIPDetail" style="display: none;position: absolute;bottom: -40px;left: 10px;background: white"> ' + vmIP + '</span> '+LANG.UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_IP+': <i>' + vmIP + '</i><em>X</em></span>';		}
		if(p.backupFlag != "0"){
			info += '<span id="backupFlag" title="' + $('#backupFlag').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_BACKUP_STATUS +': <i>' + $('#backupFlag').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.taskName){
			info += '<span id="taskName" style="position: relative"><span id="taskNameDetail" style="display: none;position: absolute;bottom: -40px;left: 10px;background: white"> ' + taskName + '</span> '+ LANG.UI_SEARCH_BACKUP_TASK +': <i>' + taskName + '</i><em>X</em></span>';		}

		$('.searchContent').append(info);
		$('#searchDiv').show();
		$('#searchDiv .searchContent #taskName').mouseenter(function(e){
			$('#taskNameDetail').show()
		}).mouseleave(function(){
			$('#taskNameDetail').hide()
		});
		$('#searchDiv .searchContent #vmIP').mouseenter(function(e){
			$('#vmIPDetail').show()
		}).mouseleave(function(){
			$('#vmIPDetail').hide()
		});
		$('#searchDiv .searchContent #vmName').mouseenter(function(e){
			$('#vmNameDetail').show()
		}).mouseleave(function(){
			$('#vmNameDetail').hide()
		});
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
			if(id =="vcenterSelect"){
				p.vcenteruuid = "";
			}
			if (id == "backupFlag") {
				p.backupFlag = 0;
			}
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort,
						sub_module_type: sub_module_type,
						accurate_flag: true,
						backup_status: p.backupFlag,
						vm_name: p.vmName,
						vm_ip: p.vmIP,
						host_name: p.hostName,
						vm_cluster: p.vmCluster,
						job_name: p.taskName,
						start_time: p.startTime,
						end_time: p.endTime,
						node : JSON.stringify(currentNodeArr),
						platform_flag: currentNode.platform_flag ? currentNode.platform_flag : false,
						platform_uuid : currentNode.platform_uuid ? currentNode.platform_uuid : currentNode.vcenteruuid,
						hypervisor_type : currentNode.hypervisor_type ? currentNode.hypervisor_type: currentNode.hypervisor,
						display_mode : showType,
					};
				}
			});
		});

		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			grid.bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						limit: queryParams.limit,
						offset: queryParams.offset,
						order: queryParams.order,
						sort: queryParams.sort,
						sub_module_type: sub_module_type,
						accurate_flag: false,
						node : JSON.stringify(currentNodeArr),
						platform_flag: currentNode.platform_flag ? currentNode.platform_flag : false,
						platform_uuid : currentNode.platform_uuid ? currentNode.platform_uuid : currentNode.vcenteruuid,
						hypervisor_type : currentNode.hypervisor_type ? currentNode.hypervisor_type: currentNode.hypervisor,
						display_mode : showType,
					};
				}
			});
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();
		}
	}

    //添加到现有备份任务
	var addMultToBackupTask = function(){
		$('#modaltips').hide();
		//var data = grid.getDataTable().data();
		var selectedRow = grid.bootstrapTable('getSelections');
		var vcenteruuid;
		var vmshowtype;
		var vmnamelist =[];
		var addbackup_vmuuids = [];
		for(var i=0;i<selectedRow.length; i++){
			if(vcenteruuid && selectedRow[i].platform_uuid != vcenteruuid ){
				return UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_VM_ADD_TO_TASK, LANG.UI_VM_REPORT_NOT_SAME_VCENTER_VM_TIPS);
			}

			if(!vcenteruuid){
				vcenteruuid = selectedRow[i].platform_uuid;
				vmshowtype = parseInt($('#vmshowtype').val());
			}
			vmnamelist.push(selectedRow[i].vm_name);
			addbackup_vmuuids.push(selectedRow[i].vm_uuid);
		}
		var params = {};
		params.platform_uuid = vcenteruuid;
		pAjaxRequest(params, "/api/v1/vm/platforms/current_jobs", "GET", function (data) {
			var task = data.data.rows;
			var select = $('#task');
			if(task.length == 0) {
				select.empty();
				$('#modaltips').show();
				return;
			}
			var option = "";
			for(var i=0; i<task.length; i++){
				option += '<option value="' + task[i].uuid + '">' + task[i].name + '</option>';
			}
			select.empty();
			select.append(option);
			select.selectpicker('refresh');
		}, false);
		var vmnamestr = vmnamelist.join(',');
		var vmuuid = addbackup_vmuuids.join(',');
		$('#modaldiv').modal();
		$('#vmname').html(vmnamestr);
		$('.vmnameDiv').hide();
		$('#vmuuid').val(vmuuid);
    	$('#vcenteruuid').val(vcenteruuid);
	}
    
    //添加到任务
    var addTotaskOpt = function(){
    	var vmname = $('#vmname').html();
    	var taskuuid = $('#task').val();
    	var vmuuid = $('#vmuuid').val();
    	var vcenteruuid = $('#vcenteruuid').val();
    	var displaymode = parseInt($('#vmshowtype').val());
    	if($.trim(vmname) == '' || taskuuid == null){
    		$('#modaltips').show();
    		return;
    	}
    	$('#modaltips').hide();
		Metronic.blockUI({target: '#modaldiv',animate: true});
    	let p = {};
    	p.platform_uuid = vcenteruuid;
    	p.vm_uuid = vmuuid; //虚拟机uuid，逗号分隔
    	p.display_mode = displaymode;
    	pAjaxRequest(p, "/api/v1/vm/jobs/" + taskuuid + "/vms", "POST", function (data) {
			Metronic.unblockUI('#modaldiv');
			if (operateResponseList(data)) {
				grid.bootstrapTable('refresh');
				$('#modaldiv').modal('hide');
			}
		}, false);
    }

	//初始化日期选择插件
	var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepickerVmReport').daterangepicker({
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
		$('#daterangepickerVmReport').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			//单击确定按钮时触发
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerVmReport').on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			//单击取消按钮时触发
			_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			_daterangepicker_range = "";
			$(this).val('');
		});

		//input右侧的图标事件
		$('.daterangepickerdiv i').click(function() {
			$(this).parent().find('input').click();
		});

		//初始化多选下拉框
		$("#task").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
	}

	/**
	 * 构造权限校验所需参数
	 * @param type    	操作类型
	 * @param user_uuid 资源所属用户uuid
	 * @returns {{user_uuid: string, auth: (string), type: number}|boolean}
	 */
	const checkAuth = (type, user_uuid = '') => {
		let select = grid.bootstrapTable("getSelections");
		let authKey = CONF.VM_SUB_MODULE.PRIVATE_CLOUD === sub_module_type ? 'prcloud_protect' : 'awsprotect';
		if (type === 'addMulti') {
			if (!select.length) {
				UIToastr.showInfo(LANG.UI_CLOUD_PLATFORM_VM_ADD_TO_TASK, LANG.UI_VM_REPORT_NO_ADD_TO_JOB_INSTANCE_TIPS);
				return false;
			}
			let user_uuids = select.map(item => item.user_uuid).join(',');
			return {type: 1, user_uuid: user_uuids, auth: authKey};
		} else if (type === 'createMulti') {
			if (!select.length) {
				UIToastr.showInfo(LANG.UI_VM_REPORT_CREATE_NEW_TASK, LANG.UI_VM_REPORT_NO_CREATE_JOB_INSTANCE_TIPS);
				return false;
			}
			let user_uuids = select.map(item => item.user_uuid).join(',');
			return {type: 1, user_uuid: user_uuids, auth: authKey};
		} else if (type === 'add' || type === 'create') {
			// 取传进来的用户uuid判断权限
			return {type: 1, user_uuid: user_uuid, auth: authKey};
		} else {
			return false;
		}
	}

    return {
        //main function to initiate the module
        init: function () {
        	initTree();
			inintDatatimePicker(); //初始化日期选择
        	addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
    VcenterDetails.init();
});