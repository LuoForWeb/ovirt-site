var AWSRecover = function () {
	var data = {
		point_info: {},
		recover_info: {
			platform_uuid: '',
			platform: '',
			recover_type: 1, //默认实例恢复
			hypervisor_type: 0,
			instance_configs: [],
			volume_configs: [],
		},
		time_strategy: {
			time_type: 1, //恢复方式：1立即，4定时
			timing_time: ''
		},
		speed_strategy: [],
		advanced_strategy: {
			thread_num: 3,
			priority_snapshot_flag: true
		},
		transport_strategy: {
			mode: '',
			appliance_uuid: '',
			appliance_type: '',
		},
		job_name: '',
		strategy_group_uuid: ''
	};
	var _platformUuid = '', _platformName; //目标云平台uuid
	var _platformData;
	var timepoint_uuids = []; //已选时间点uuid
	//实例配置选择器
	var _IName = $('#drawer-1').find('[name=instance_name]');
	var _IProject = $('#drawer-1').find('[name=project]');
	//卷配置选择器
	var _VName = $('#drawer-2').find('[name=vol_name]');

	var _crossFlag = false; //是否跨平台，即虚拟机恢复到公有云
	var _timeStamp = '';	//时钟时间戳,全局
	var _time = '';	//服务器时间，格式：20230101120000
	var pointtypetree, pointtypetreeInitFlag = false, vmtypetree, vmTypetreeInitFlag = false;
	var currentTree; //当前展示的树
	var vmOldName = [];
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	var _VMNAMETIPS = LANG.UI_TOOLS_VMNAME_TIPS;
	var nodeParamList;
	var initSpeedFlag = false;
	var speedList = [];
	var globalStrategy = [];
	var applianceRegionUuid = '';
	var editFlag = false;
	var initHighFlag =false;
	var vmNameLimit = {}; //实例名称长度限制
	
	var showType = 1; //展示方式
	var DEFAULT_ARCHITECTURE = 'x86';
	var storage_type; //恢复源存储类型
	var authFun = {};

	// 从备份数据跳转恢复页面
	const externalPointUuid = $('#externalPointUuid').val();
	const externalTaskUuid = $('#externalTaskUuid').val();
	const externalItemUuid = $('#externalItemUuid').val();
	const externalSubType = $('#externalSubType').val();

	const limit = 20; // 每次加载的条数
	var keywordCache; // 缓存搜索关键词
	var selectedTimepoint = []; // 保存所有已选的时间点
	var submittedFlag = false; // 是否已提交

	var initListener = function(){
		//选择恢复方式：立即恢复/定时恢复
		$('#recovertype').on('change',function(){
			if('1' == this.value){
				data.time_strategy.time_type = 1;
				data.time_strategy.timing_time = '';
				$('.setOnceTime').hide();
			}else if("4" == this.value){
				data.time_strategy.time_type = 4;
				$('.setOnceTime').show();
			}
			initTimeStrategyDes();
		});

		//修改定时恢复时间
		$('#oncetime').on('change', function(){
			initTimeStrategyDes();
		});

		//清空时间选择
		$('#resetdate').on('click',function(){
			$('#oncetime').val('');
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

		//跳转创建备份任务
		$('#tobackup').on('click',function(){
			LOCATION('./content/aws/awsbackup.php', 'awsprotect');
		});
		
		//跳转到添加虚拟化中心
		$('#toaddvcenter').on('click',function(){
	    	LOCATION('./content/vm/cloudplatform/add_cloud_platform.php?cloudType=public', 'infrastructure');
		});
		
		//输入线程数量检测
		// $('#awsRecoveryThreadNum').blur(threadChange);
	}

	var inintDatatimePicker = function () {
		if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
			//英文独有的
			$(".form_datetime").datetimepicker({
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-mm-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
				startDate: new Date()
			});
		} else {
			$(".form_datetime").datetimepicker({
				language: 'zh-CN',
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-MM-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
				startDate: new Date()
			});
		}
	}

	var speedModeHandler = function(){
        if(this.value == 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
        }
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
		if(!zNodes.length){
			$("#nopointtips").show();
			$('.vcenter-tree').hide();
			$('#vmtypetree').hide();
			$('#pointtypetree').hide();
			$('#pointshowtype').prop('disabled', true);
//			$('#pointshowtype').selectpicker('refresh');
			return false;
		}else{
			$("#nopointtips").hide();
			$('.vcenter-tree').show();
//			if($('#pointshowtype').val() == 1){
				$('#pointtypetree').show();
				$('#vmtypetree').hide();
//			}else{
//				$('#pointtypetree').hide();
//				$('#vmtypetree').show();
//			}
			$("#two_tree").show();
			$('#pointshowtype').prop('disabled', false);
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
					onCheck: timepointOnCheckValidate,
					beforeExpand: nodeExpand
				},
				view: {
					showTitle: true,
					nameIsHTML:true,
					fontCss: (treeId, treeNode) => {
						let style = {};
						if (treeNode.type === 3 || treeNode.type === 4) {
							if (!treeNode.timepoint_status.avaliable_flag) {  // 操作中
								style = {'color': '#F19F00!important'};
							} else if (treeNode.timepoint_status.avaliable_flag) {
								if (parseInt(treeNode.timepoint_status.status) === 3) {
									style = {'color': '#F1416C!important'};
								}
							}
						}
						return style;
					},
					addHoverDom: (treeId, treeNode) => {
						if (treeNode.type !== 3 && treeNode.type !== 4) {
							// 不是时间点
							return;
						}
						if ($(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).length) {
							return;
						}
						let sObj = $(`#${treeNode.tId}_span`);
						sObj.after(`<span id="${treeNode.tId}_${treeNode.timepoint_uuid}"><i class='viconfont vicon-Frame11'></i></span>`);
						// 注册点击事件
						$(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).on("click", (ev) => {
							ev.stopPropagation();  // 阻止click事件向上冒泡
							$('.page-content').initPointDetailDrawer({timepoint_uuid: treeNode.timepoint_uuid});
						});
					},
					removeHoverDom: (treeId, treeNode) => {
						if (treeNode.type !== 3 && treeNode.type !== 4) {
							// 不是时间点
							return;
						}
						$(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).off().remove();
					}
				}
			};
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, zNodes);
		// 从备份数据跳转恢复页面，展开对象下的时间点
		let targetNode = pointtypetree.getNodeByParam('id', externalSubType + externalItemUuid + externalTaskUuid);
		// 第一次初始化，且有目标节点
		if(targetNode && !pointtypetreeInitFlag){
			// 异步获取时间点
			getSyncVcenterInfo('pointtypetree',targetNode, true, true, true)
		}
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
		vmtypetree = $.fn.zTree.init($("#vmtypetree"), setting, zNodes);
		vmTypetreeInitFlag = true;
		currentTree = vmtypetree;
	}

	// 对应vmrecover.js的initStorageTree
	var initPointTree = function(loadMoreFlag = false, offset = 0, taskNodeId = '', taskUuid = '', keyword = '') {
		let data = {};
		data._rows = true;
		data.show_type = 1;
		data.manage_flag = false;
		data.data_flag = false;
		data.sub_module_type = 3; //公有云获取标志
		data.storage_uuid = $('#storageselect').val();
		var setFunction = setPointTree;
		if(2 == data.showtype){
			setFunction = setPointTreetype2;
		}
		data.keyword = keyword;
		data.loadmore_offset = offset;
		data.loadmore_limit = limit;
		if (loadMoreFlag) {
			data.job_uuid = taskUuid;
		}
		let div = ".src-wrap__content";
		Metronic.blockUI({target: div,animate: true});
		pAjaxRequest(data, "/api/v1/vm/restore_data", "GET", function (d) {
			Metronic.unblockUI(div);
			if (loadMoreFlag) {
				// 获取任务所在树节点
				let treeNode = currentTree.getNodeByParam('id', taskNodeId);
				// 删除已有的【加载更多】节点
				currentTree.removeNode(currentTree.getNodeByParam('id', `${taskNodeId}_loadMore`));
				// 添加虚拟机的节点
				currentTree.addNodes(treeNode, d.data.rows, true);
			} else {
				// 初次加载
				setFunction(d.data.rows);
			}
		}, true);
	};
	
	//检查没有云平台切换提示信息
	var checkPlatformInfo = function (rows, id) {
		if (rows.length == 0) {
			$("#platform-div").hide();
			$("#noplatformtips").show();
			$('.' + id).hide();
			return false;
		} else {
			$("#platform-div").show();
			$("#noplatformtips").hide();
			$('.' + id).show();
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
			if (1 === treeNode.moreType) {
				// 加载更多虚拟机
				initPointTree(true, treeNode.nextOffset, treeNode.pId, treeNode.task_uuid);
			} else if (3 === treeNode.moreType || 4 === treeNode.moreType) {
				// 加载更多时间点
				let parentNode = currentTree.getNodeByParam('id', treeNode.pId);
				let parentUuid = 3 === treeNode.moreType ? treeNode.vm_uuid : treeNode.pId;
				getSyncVcenterInfo(treeId, parentNode, false, false, false, true, treeNode.nextOffset, parentUuid);
			} else {
				pointtypetree.expandNode(treeNode, true)
			}
		}
		nodeExpand(treeId, treeNode);
	}
	
	//节点展开异步添加时间点
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncVcenterInfo(treeId, treeNode, false, false, false, true, 0, treeNode.vm_uuid);
		}else{
			if (3 === treeNode.type && !treeNode.loadChildren && !keywordCache) {
				// 加载完备点下的其他点
				getSyncVcenterInfo(treeId, treeNode, false, false, false, true, 0, treeNode.id);
				treeNode.loadChildren = true;
			} else {
				return true;
			}
		}
		
	}
	
	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag, chooseFlag = false, loadMoreFlag = false, offset = 0, parentUuid = ''){
		var storageuuid = $('#storageselect').val();
		var div = ".src-wrap__content";
		let p = {
			task_uuid: treeNode.task_uuid,
			vm_uuid: treeNode.vm_uuid,
			hypervisor_type: treeNode.hypervisor_type,
			disabled_flag: false, //备份数据禁用勾选增量差异标志
			manage_flag: false,
			vm_check: treeNode.checked,
			storage_uuid: storageuuid,
			parent_uuid: parentUuid,
		};
		if (showType == 2) {
			div = "#vmtypetree";
			p = {
				task_uuid: treeNode.task_uuid,
				hypervisor_type: treeNode.hypervisor_type,
				storage_uuid: storageuuid,
				parent_uuid: parentUuid,
			};
		}
		if (keywordCache) {
			p.keyword = keywordCache;
			// 按关键词搜索时一次性加载
			loadMoreFlag = false;
		}
		if (loadMoreFlag) {
			p.loadmore_offset = offset;
			p.loadmore_limit = limit;
		}
		Metronic.blockUI({target: div,animate: true});
		pAjaxRequest(p, "/api/v1/vm/restore_data/restore_points", "GET", function (d) {
			Metronic.unblockUI(div);
			if(d.success){
				//success
				if (0 === offset) {
					// 初次展开获取时需先清空
					$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				}
				if (loadMoreFlag) {
					// 删除已有的【加载更多】节点
					currentTree.removeNode(currentTree.getNodeByParam('id', `${treeNode.id}_loadMore`));
					// 在每个时间点节点的父节点下面添加
					d.data.rows.forEach(newNode => {
						// 如果是已选的要勾选上
						selectedTimepoint.forEach(item => {
							if (item.timepoint_uuid === newNode.timepoint_uuid) {
								newNode.checked = true;
							}
						});
						let parentNode = currentTree.getNodeByParam('id', newNode.pId);
						currentTree.addNodes(parentNode, newNode, true);
						if (expendFlag === true) {
							currentTree.expandNode(parentNode, true, true, true);
						}
					});
				} else {
					// 原有的一次性加载
					d.data.rows.forEach(newNode => {
						// 如果是已选的要勾选上
						selectedTimepoint.forEach(item => {
							if (item.timepoint_uuid === newNode.timepoint_uuid) {
								newNode.checked = true;
							}
						});
					});
					$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
					if (expendFlag === true) {
						$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
					}
				}
				// 从备份数据跳转恢复页面，匹配时间点并勾选
				let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
				if(targetNode && chooseFlag){
					$.fn.zTree.getZTreeObj(treeId).checkNode(targetNode, true, true, true);
				}
			}
		}, true);
	}
	
	//选择第二课树
	var nodeSelectType2 = function(treeId, treeNode, clickFlag){
		vmtypetree.checkNode(treeNode, !treeNode.checked, true, true);
		vmtypetree.expandNode(treeNode, true);
		nodeExpand(treeId, treeNode);
	}

	//初始化目标云平台选择框
	var initPlatform = function (platform_uuid) {
		pAjaxRequest({hypervisor_type: data.point_info.points[0].hypervisor_type}, "/api/v1/cloud/all_platforms", "GET", function (d) {
			let rows = _platformData = d.data.rows;
			if (!checkPlatformInfo(rows, 'platform-div')) {
				return;
			}
			$('#platform-select').empty();
			let options = '';
			$.each(rows, function (i, v) {
				options += '<option value="' + v.platform_uuid + '" data-hypervisor="' + v.hypervisor_type + '">' + v.nickname + '</option>';
				if (platform_uuid == v.platform_uuid && CONF.VM_TYPE.HUAWEICLOUD == v.hypervisor_type) {
					loadEnterpriseProjectList(v.enterprise_projects);
				}
			})
			$('#platform-select').append(options);
			$('#platform-select').val(platform_uuid);
			_platformUuid = $('#platform-select').val(); // 此处可能为空
			_platformName = $('#platform-select option:selected').text();
			data.recover_info.platform_uuid = _platformUuid;
			data.recover_info.platform = _platformName;
		}, false);
	}

	// 企业项目下拉框加载
	var loadEnterpriseProjectList = function (data) {
		_IProject.empty();
		let options = '';
		$.each(data, function (i, v) {
			let _currentInstanceConfig = $.fn.getAwsRecoveryCurrentInstanceConfig();
			let selected = v.project_id == _currentInstanceConfig.enterprise_project_id ? 'selected' : '';
			options += `<option value="${v.project_id}" ${selected}>${v.project_name}</option>`;
		});
		_IProject.append(options);
	}
	
	//替换特殊字符为下划线
	var clearString = function (s){ 
	    var rs = ""; 
	    for (var i = 0; i < s.length; i++) { 
	        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_'); 
	    } 
	    return rs;  
	}
	
	//检测选择的点时候是同一个虚拟化中心,如果不是,需要把之前的所有点都取消选择
	var checkHypervisorPoint = function(treeId, node){
		var tree = $.fn.zTree.getZTreeObj(treeId);
		// var allNodes = tree.getCheckedNodes(true);
		var allNodes = selectedTimepoint;
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].hypervisor_type != node.hypervisor_type){
				tree.checkAllNodes(false);
				tree.checkNode(node, !node.checked, false, false);
				$('#VMGroupList li').remove();
				let timepointUuid = allNodes[i].timepoint_uuid;
				selectedTimepoint.splice(selectedTimepoint.findIndex(item => item.timepoint_uuid === timepointUuid));
				return;
			}
		}
	}

	//检测选择的点是否都是磁带备份点或非磁带备份点（互斥）,如果不是,需要把之前的所有点都取消选择
	var checkStorageTypePoint = function(treeId, node){
		var tree = $.fn.zTree.getZTreeObj(treeId);
		// var allNodes = tree.getCheckedNodes(true);
		var allNodes = selectedTimepoint;
		var selected_storage_type_list = [];
		for(var i = 0; i < allNodes.length; i++){
			selected_storage_type_list.push(parseInt(allNodes[i].storage_type))
		}
		let uniqueStorageTypes = [...new Set(selected_storage_type_list)];
		if(uniqueStorageTypes.length > 1 && uniqueStorageTypes.indexOf(CONF.BD_STORAGE_TYPE.TAPE) != -1){
			bootbox.confirm({
				title: LANG.UI_VERIFY_SELECT_TIMEPOINT,
				message: LANG.UI_CHOOSE_TIMEPOINT_TAPE_TIPS, // 例如："该操作需要密码验证，请确认继续？"
				buttons: {
					confirm: {
						label: LANG.UI_PUBLIC_CONFIRM,
						className: 'btn-primary'
					},
					cancel: {
						label: LANG.UI_PUBLIC_CANCEL,
						className: 'btn-secondary'
					}
				},
				callback: debounce(function (result) {
					if (result) {
						// 用户点击了“确定”, 取消之前勾选的时间点
						tree.checkAllNodes(false);
						$('#VMGroupList li').remove();
						selectedTimepoint = [];
						//勾选当前时间点
						tree.checkNode(node, true, false, false);
						addPointList(treeId, node, showType);
						$(this).modal('hide');
						selectedTimepoint.push(node);
						return true;
					} else {
						// 用户点击了“取消”
						tree.checkNode(node, false, false, false);
						addPointList(treeId, node, showType);
						return false;
					}
				}, 300, false)
			});
		}
	}
	
	//判断是否在一个备份节点上
	var checkSelectInOneNode = function(flag, tree, node, allNodes, checkTypeFlag){
		if(!flag) return true;
//		var showType = showType;
		for (var i = 0; i < allNodes.length; i++) {
			if (allNodes[i].node_uuid != node.node_uuid) {
				UIToastr.showInfo(LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE, LANG.UI_RECOVERY_POINT_NOT_IN_ONE_NODE_TIPS);
				for (var j = 0; j < allNodes.length; j++) {
					tree.checkNode(allNodes[j], false, false, false);
					selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== allNodes[j].timepoint_uuid);
				}
				tree.checkNode(node, flag, checkTypeFlag, false);
				selectedTimepoint.push(node);
				return false;
			}
		}
		return true;
	}

	//勾选添加实例显示列表
	var addPointList = function(id,node, showType){
		var info = "";
		var liId = clearString(id + node.platform_uuid + node.id + node.vm_uuid); //添加实例每列ID
		if(node.checked){
			if(showType == 1){
				info +=
					'<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +'">' +
						'<div class="col1">' +
							'<div class="cont vmDetail">' +
								'<div class="cont-col1">' +
									'<div style="background: url(./img/vm/AWS/aws-instance.png) 0 no-repeat;"></div>' +
								'</div>' +
								'<div class="cont-col2">' +
									'<div>' + node.vm_name +
									'<span>' +  node.name + '</span></div>' +
								'</div>' +
							'</div>' +
						'</div>' +
						'<div class="col2  pull-right delete-list">' +
							'<a class="del'+liId+'" >' +
								'<div class="label label-sm label-danger" style="padding:0;">' +
									'<i class="viconfont vicon-guanbi"></i>' +
								'</div>' +
							'</a>' +
						'</div>' +
					'</li>';
			}else if(showType == 2){
				var parentNode = node.getParentNode();
				info += 
				'<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path + '">' +
					'<div class="col1">' + 
						'<div class="cont vmDetail">' + 
							'<div class="cont-col1">' + 
								'<div class="'+parentNode.iconSkin+'"></div>' + 
								'<div style="width:20px;height:20px;background: url(./img/vm/AWS/aws-instance.png) 0 no-repeat;"></div>' +
							'</div>' + 
							'<div class="cont-col2">' + 
								'<div class="desc list-one" style="font-size: 14px;color: #333;padding: 9px 4px 0px 4px;">' + node.vmname + '</div>' +
								'<div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px;">' + parentNode.name + '</div>' + 
							'</div>' + 
						'</div>' + 
					'</div>' + 
					'<div class="col2  pull-right delete-list">' + 
						'<a class="del'+liId+'" >' + 
							'<div class="label label-sm label-danger" style="padding:0;">' + 
								'<i class="viconfont vicon-guanbi"></i>' + 
							'</div>' + 
						'</a>' + 
					'</div>' + 
				'</li>';
			}
			//根据实例树类型添加每一列到列表
			if(id == "pointtypetree"){
				$('#VMGroupList').append(info);
			}else if(id == "vmtypetree"){
				$('#timepointGroupList').append(info);
			}
			$('#' + escapeJquery(liId)).popover();	   //初始化tips
			//移除实例显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				var treeObj = $.fn.zTree.getZTreeObj(id);
				$('.popover.in').remove();
	    		treeObj.checkNode(node,false,false);
	    		$('#' + escapeJquery(liId)).remove();
				selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== node.timepoint_uuid);
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

	const timepointOnCheckValidate = function (e, id, node) {
		let allNodes = pointtypetree.getCheckedNodes(true);
		for (var i = 0; i < allNodes.length; i++) {
			if (allNodes[i].vm_uuid == node.vm_uuid && allNodes[i].platform_uuid == node.platform_uuid && allNodes[i].timepoint_uuid != node.timepoint_uuid) {
				//如果虚拟机一样的话,就要取消其他的时间点
				pointtypetree.checkNode(allNodes[i], false, false, false);
				var liId = clearString(id + allNodes[i].platform_uuid + allNodes[i].id + allNodes[i].vm_uuid); //添加虚拟机每列ID
				$('#' + escapeJquery(liId)).remove();
			}
		}
		for (let i = 0; i < selectedTimepoint.length; i++) {
			if (selectedTimepoint[i].vm_uuid == node.vm_uuid && selectedTimepoint[i].platform_uuid == node.platform_uuid && selectedTimepoint[i].timepoint_uuid != node.timepoint_uuid) {
				pointtypetree.checkNode(selectedTimepoint[i], false, false, false);
				let liId = clearString(id + selectedTimepoint[i].platform_uuid + selectedTimepoint[i].id + selectedTimepoint[i].vm_uuid);
				$('#' + escapeJquery(liId)).remove();
				selectedTimepoint.splice(i, 1);
			}
		}
		if (node.checked && node.config.password && 2 == node.config.password_auto_flag) {
			// 加密的时间点需弹窗输入密码
			bootbox.prompt({
				title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
				inputType: 'password',
				callback: function (pwdResult) {
					if (pwdResult == null) {
						// 取消输入则取消勾选时间点
						$.fn.zTree.getZTreeObj(id).checkNode(node, false, false, false);
						return;
					}
					//获取密码
					let requestList = {
						'timepoint_uuid': node.timepoint_uuid,
						'password': btoa(pwdResult),
					};
					let checkFlag = false;
					//获取密码是否正确
					var requestData = function (data) {
						if (data.success) {
							checkFlag = true;
							timepointOnCheck(e, id, node);
							UIToastr.showSuccess(LANG.UI_PLATFORM_RECOVERY_TIMEPOINT_PASS, data.message);
						} else {
							UIToastr.showWarning(LANG.UI_PLATFORM_RECOVERY_TIMEPOINT_PASS, data.message);
						}
					}
					pAjaxRequest(requestList, '/api/v1/jobs/password_check', "GET", requestData, false);
					return checkFlag;
				}
			});
		} else {
			timepointOnCheck(e, id, node);
		}
	}
	
	//实例分组
	var timepointOnCheck = function(e, id, node){
		var flag = node.checked;
		checkHypervisorPoint(id, node);
		// 先统一移除再添加，相同虚拟机的不重复添加
		selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== node.timepoint_uuid);
		if (flag) {
			let vmFlag = false;
			selectedTimepoint.map(item => {
				if (item.platform_uuid == node.platform_uuid && item.vm_uuid == node.vm_uuid) {
					vmFlag = true;
				}
			});
			if (!vmFlag) {
				selectedTimepoint.push(node);
			}
		}

		storage_type = node.storage_type;

		checkStorageTypePoint(id, node); //磁带需求，磁带备份点和非磁带备份点互斥，不可同时选择
//		var showType = $('#pointshowtype').val();

		// var allNodes = pointtypetree.getCheckedNodes(true);
		var allNodes = selectedTimepoint;
		if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, false)){
			$('#VMGroupList li').remove();
			$('#timepointGroupList li').remove();
			addPointList(id, node, showType);
			return; 
		}
		pointtypetree.checkNode(node, flag, false, false);
		addPointList(id, node, showType);
	}
	
	//时间点分组
	var timepointOnCheckType2 = function(e, id, node){
		checkHypervisorPoint(id, node);
		checkStorageTypePoint(id, node); //磁带需求，磁带备份点和非磁带备份点互斥，不可同时选择
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
					if(broNodes[i].vm_uuid == node.vm_uuid){
						vmtypetree.checkNode(broNodes[i], false, true, false);
						var children = broNodes[i].children;
						for(var j=0;j<children.length;j++){
							var liId = clearString(id+children[j].platform_uuid + children[j].id + children[j].vm_uuid); //添加实例每列ID
							$('#' + escapeJquery(liId)).remove();
						}
					}
				}
				for(var j=0;j<allNodes.length;j++){
					var children = node.children;
					for(var k=0;k<children.length;k++){
						if(allNodes[j].type==2 && allNodes[j].vm_uuid == children[k].vm_uuid){
							vmtypetree.checkNode(allNodes[j], false, false, false);
							var liId = clearString(id+allNodes[j].platform_uuid + allNodes[j].id + allNodes[j].vm_uuid); //添加实例每列ID
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
		
		//同一个实例只能勾选一个对应的时间点
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].type == 2 && allNodes[i].vm_uuid == node.vm_uuid){
				vmtypetree.checkNode(allNodes[i], false, true, false);
				var liId = clearString(id+allNodes[i].platform_uuid + allNodes[i].id + allNodes[i].vm_uuid); //添加实例每列ID
				$('#' + escapeJquery(liId)).remove();
			}
		}
		vmtypetree.checkNode(node, flag, false, false);
		var parentNode = node.getParentNode();
		if(node.type == 2 && node.checked){
			vmtypetree.checkNode(parentNode, true, false, false);
			addPointList(id, node, showType);
		}else if(node.type == 2 && !node.checked){
			var liId = clearString(id+ node.platform_uuid + node.id + node.vm_uuid); //添加实例每列ID
			$('#' + escapeJquery(liId)).remove();
		}
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
//            $('.step-title', $('#awsrecovercontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#awsrecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#awsrecovercontent').find('.button-previous').css('visibility', 'hidden');
                $('#awsrecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#awsrecovercontent').find('.button-previous').css('visibility', 'visible');
                $('#awsrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#awsrecovercontent').find('.button-next').hide();
                $('#awsrecovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#awsrecovercontent').find('.button-next').show();
                $('#awsrecovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#awsrecovercontent').bootstrapWizard({
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
				// 还原tab-pane的高度
				$(".tab-pane__row").css('height', '100%');
                $('#awsrecovercontent').find('.button-next').prop('disabled', false);
                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#awsrecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#awsrecovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#awsrecovercontent .button-submit').click(submit).css('visibility', 'hidden');
	};
	
	
	var step1Valid = function(){
		// var nodes = currentTree.getCheckedNodes();
		var nodes = selectedTimepoint;
		if(!nodes.length){
			$(".selecttimepointtip").html(LANG.UI_RECOVERY_SELECT_POINT).show();
			// 动态设置tab-pane的高度
			$(".tab-pane__row").css('height', 'calc(100% - 80px)');
			return false;
		}
		
		data.point_info.points = [];
		var showStr = [];
		var pointshowtype = showType;
		vmOldName = [];
		data.recover_info.hypervisor_type = nodes[0].hypervisor_type;
		switch (data.recover_info.hypervisor_type) {
			case CONF.VM_TYPE.AWS:
				DEFAULT_ARCHITECTURE = 'x86_64';
				break;
			case CONF.VM_TYPE.HUAWEICLOUD:
				DEFAULT_ARCHITECTURE = 'x86';
				break;
			default:
				break;
		}
		if (CONF.VM_TYPE.HUAWEICLOUD == data.recover_info.hypervisor_type) {
			$(`.huweicloud-vol-tips`).show();
			_IName.attr('maxlength', 64);
			_VName.attr('maxlength', 64);
		} else {
			$(`.huweicloud-vol-tips`).hide();
			_IName.attr('maxlength', 256);
			_VName.attr('maxlength', 256);
		}
		$.each(nodes, function(i, d){
			var jsondata = {
				instance_uuid: d.vm_uuid,
				timepoint_uuid: d.timepoint_uuid,
				platform_uuid: d.platform_uuid,
				hypervisor_type: d.hypervisor_type,
				node_uuid: d.node_uuid,
				storage_uuid: d.storage_uuid,
				config: d.config,
				integrity_check_flag: d.integrity_check_flag,
			};
			let path = d.path.replace(d.vm_uuid, d.vm_name);
			if(1 == pointshowtype){
				//按实例分组
				if(3 == d.type || 4 == d.type){
					data.point_info.points.push(jsondata);
					vmOldName.push([d.vm_name, d.point_name]);
					showStr.push(path + "(" + d.point_name + ")" + "<br>");
				}
			}else{
				//时间点分组
				if(2 == d.type){
					data.point_info.points.push(jsondata);
					vmOldName.push([d.vm_name, d.point_name]);
					
					showStr.push(path + "(" + d.point_name + ")" + "<br>");
				}
			}
		});

		timepoint_uuids = [];
		$.each(data.point_info.points, function (i, v) {
			timepoint_uuids.push(v.timepoint_uuid);
		});

		showStep1(showStr);
		$('#awsrecovercontent').find('.button-next').prop('disabled', true);

		// 初始化变量
 		data.recover_info.instance_configs = [];
 		data.recover_info.volume_configs = [];

		initPlatform(nodes[0].platform_uuid); //初始化目标云平台选择框
		// 插件初始化表格和抽屉
		$.fn.initAwsRecoveryConfig({
			source_hypervisor: nodes[0].hypervisor_type,
			target_hypervisor: data.recover_info.hypervisor_type,
			timepoint_uuids: timepoint_uuids,
			platform_uuid: _platformUuid,
			recover_type: $('#recovertype-select').val(),
			platform_data: _platformData
		});
		return true;
	}
	
	//第三步恢复方式的显示
	var recoveryTypeShow = function(){
		// if (storage_type == 10) {
		// 	//磁带只支持单线程，不显示线程配置
		// 	$('#awsRecoveryThreadNum').val(1);
		// 	$('.threadDiv').hide();
		// } else {
		// 	$('#awsRecoveryThreadNum').val(3);
		// 	$('.threadDiv').show();
		// }
		$('#highstrategyshowdiv').show();
		$('.transferLi').show();
		
		$('.speedlimitDiv').show(); 			//显示限速策略
		$('#highstrategyshowdiv').show();		//显示限速策略描述
		$('#speedstrategyshowdiv').show();		//显示多线程描述
		//初始化高级配置tab
		$('.commonLi').removeClass('active').addClass('active');
		$('.transferLi').removeClass('active');
		$('.safeLi').removeClass('active');
		$('.highLi').removeClass('active');

		$('#tab_common').removeClass('active').addClass('active');
		$('#tab_transfer').removeClass('active');
		$('#tab_safe').removeClass('active');
		$('#transportmodeshowdiv').show();					//显示传输策略确认
		
		$('#awsRecoveryThreadNum').prop('disabled', false); //启用线程数选择
		$('#awsRecoveryThreadDiv button').prop('disabled', false);
		if(!initHighFlag){
			$('.ipSegmentDiv').hide();	//隐藏数据传输网段
			$('#ipSegment').val('');
		}
		//初始化优先快照恢复开关
		$('#prisnapshot').bootstrapSwitch('state', true);
		data.advanced_strategy.priority_snapshot_flag = true;

		if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
			initHighFlag = true;
		}
	}
	
	//得到任务名,这里需要注意的是在跨虚拟化平台恢复的时候需要用目的地的名字,
	//这里是需要在选择目的地节点后再初始化.
	var getTaskName = function(hypervisor){
		pAjaxRequest({hypervisor_type: hypervisor}, "/api/v1/vm/jobs/restore/job_name", "GET", function (d) {
			$('#jobname').val(d.data.info);
		}, false);
	}

	//初始化时间计时器
	var initServerTime = function () {
		var getDate = function (unix) {
			var polishing = function (d) {
				return d < 10 ? '0' + d : d;
			}
			var date = new Date(parseInt(unix));
			Y = date.getFullYear();
			M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
			D = polishing(date.getDate());
			h = polishing(date.getHours());
			m = polishing(date.getMinutes());
			s = polishing(date.getSeconds());
			_time = Y + M + D + h + m + s;
			return Y + '-' + M + '-' + D + ' ' + h + ':' + m + ':' + s;
		}
		var servertime = $('#servertime');
		var updateInterval = 1000;
		var startClock = function () {
			if (0 == $('#servertime').size()) {
				return;
			}
			servertime.html(getDate(_timeStamp));
			_timeStamp += 1000;
			setTimeout(startClock, updateInterval);
		}
		if (_timeStamp) return;
		$.post(CONF.AJAXPATH, {m: CONF.M.SYSTEM, f: 'getSystemTimeJSFormat', p: {}}, function (data) {
			_timeStamp = new Date(data).getTime();
			startClock();
		});
	}
	
	var showStep1 = function(nodes){
		//初始化计时器
		initServerTime();
		var str = '';
		$.each(nodes, function(i, d){
			str += d;
		});
		$('.vmtypeshow').html(str);

		storage_type = pointtypetree.getCheckedNodes()[0].storage_type;
		initHighStrategyDes();
	}
	
	var step2Valid = function(){
		data.recover_info = $.fn.getAwsRecoveryConfig();
		if (!data.recover_info) {
			return false;
		}
		_platformName = data.recover_info.platform;
		//未初始化恢复目的云平台
		if (!$('#platform-select').val() || '' == data.recover_info.platform_uuid) {
			$(".setrecover2tip").html(LANG.UI_RECOVERY_SELECT_CLOUD_PLATFORM).show();
			return false;
		}
		var showStr = '';

		data.recover_info.names = [];
		var flag = true;
		var vmnameFlag = true; //检查恢复后实例名是否规范
		var vmNameLengthFlag = true; //检查恢复后实例名长度是否规范
		var reg = new RegExp(vmNameLimit.limit);
		if (1 == data.recover_info.recover_type) {
			//得到实例恢复名
			$.each(data.recover_info.instance_configs, function(i, d){
				var value = $.trim(d.instance_name);
				showStr += _platformName + "/" + d.region + "/" + value + "<br>";
			});
		} else {
			$.each(data.recover_info.volume_configs, function (i, d) {
				showStr += _platformName + "/" + d.region + "/" + d.available_zone + "/" + d.vol_new_name + "<br>";
			});
		}
		if(!flag) return false;
		if (!vmnameFlag) return false;
		if (!vmNameLengthFlag) return false;

		//同步ajax校验密码
		var checkflag = true;
		if(!checkflag) return false;

		showStr = showStr.substr(0, showStr.length-2) + "<br><br>";
		initSafeStrategy();
		if (CONF.BD_STORAGE_TYPE.TAPE == storage_type) {
			// 时间点存储为磁带时隐藏安全策略
			$('.safeLi').hide();
			$('.safeDiv').hide();
		}
		// 初始化重试策略
		$('#retry_config').retryStrategy();
		// 忽略节点资源限制默认开
		$('#ignore_resource_limit').bootstrapSwitch('state', true);
		getTaskName(data.recover_info.hypervisor_type);
		// 初始化传输策略
		$.fn.initAwsRecoveryTransferStrategy({
			target_hypervisor: data.recover_info.hypervisor_type,
			instance_configs: data.recover_info.instance_configs,
			platform_uuid: data.recover_info.platform_uuid,
		})
		showStep2(showStr);
		initStrategyDes();
		return true;
	}
	
	var showStep2 = function(str){
		$('.recovershow').html(str);
		recoveryTypeShow();
		initResourceLimit([data.point_info.points[0].node_uuid]);
	}
	
	var step3Valid = function(){
		data.time_strategy.time_type = parseInt($('#recovertype').val());

		// 从插件获取传输策略
		data.transport_strategy = $.fn.getAwsRecoveryTransferData();
		if (!data.transport_strategy) {
			return false;
		}

		data.advanced_strategy.thread_num = parseInt($('#awsRecoveryThreadNum').val());
		data.advanced_strategy.priority_snapshot_flag = $('#prisnapshot').bootstrapSwitch('state');
		data.advanced_strategy.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;
		var thread = $('#awsRecoveryThreadNum').val();
		if(thread == "" || thread > 8 || thread <= 0){
			$('#awsRecoveryThreadDiv').spinner("value", 3);
			initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}

		data.speed_strategy = getSpeedStrategyInfo();
		if (!getSafeStr()) {
			return false;
		}
		if (!getRetryStr()) {
			return false;
		}
		if('1' == data.time_strategy.time_type){
			//立即恢复
			showStep3();
			return true;
		}else{
			var onceTime = $('#oncetime').val();
			if ("" != onceTime) {
				var systemTime = $('#servertime').text();
				var onceTimeSize = new Date(onceTime).getTime();
				var systemTimeSize = new Date(systemTime).getTime();
				if (onceTimeSize <= systemTimeSize) {
					UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_CORRENT_TIME_TIPS);
					return false;
				}
				data.time_strategy.timing_time = onceTime;
				showStep3();
				return true;
			} else {
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME, LANG.UI_BACKUP_SET_TIME_TIPS);
				return false;
			}
		}
	}

	// 得到安全策略
	var getSafeStr = function () {
		let virusConfig = $('#virusConfig').getVirusDetectionCover();
		if (virusConfig === false) {  // 验证病毒扫描配置内容是否符合要求
			return false;
		}
		let completeConfig = $('#completeConfig').getCompleteStrategyCovery();
		data.safe_strategy = safeData('', virusConfig, completeConfig);

		// 安全策略描述
		let safeInfo = '';
		if (CONF.FUNCTIONS.includes('virusKill')) {
			safeInfo += virusConfig.str;
			safeInfo += '<br>';
		}
		if (authFun.integrity) {
			safeInfo += completeConfig.str;
		}
		$('.safeStrategyShow').html(safeInfo);
		return true;
	}

	// 得到重试策略
	var getRetryStr = function () {
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		return false !== data.retry_strategy;
	}
	
	var showStep3 = function(){
		//恢复方式
		var showStr1 = $('#recovertype').find("option:selected").text();
		if(!$.isEmptyObject(data.time_strategy)){
			if (4 == $('#recovertype').val()) {
				showStr1 +=  "(" + data.time_strategy.timing_time + ")";
			}
		}
		$('.reservetypeshow').html(showStr1);

		//传输策略
		$('.transportinfoshow').html($.fn.getAwsRecoveryTransferDes({storage_type: storage_type}));

		//高级策略
		var showStr4 = $('.prisnapshotlabel').text(); //优先快照
		showStr4 += ": " + getSwitchDes(data.advanced_strategy.priority_snapshot_flag);
		showStr4 += '<br>' + $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes($('#ignore_resource_limit').get(0).checked);
		$('.prisnapshotshow').html(showStr4);
		
		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedLimitsStr = '';
		if (data.speed_strategy.speedInfo.length != 0) {
			speedLimitsStr = '';
			for (let i = 0; i < data.speed_strategy.speedInfo.length; i++) {
				speedLimitsStr += data.speed_strategy.speedInfo[i].des + '<br>';
			}
		}
		if (speedLimitsStr == '') {
			speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedLimitsStr);
	}
	
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	
	var submit = function(){
		if (submittedFlag) {
			return; // 防止重复提交
		}

		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
			return;
		}
		$('.jobnametip').hide();
		// 输入验证
		if(!customInputValidate('string', $("#jobname").val())){
			return false;
		}
		data.job_name = $.trim($("#jobname").val());
		if($('#strategySelect option:selected').val()){
			data.strategy_group_uuid = $('#strategySelect option:selected').val();
		}
		if (!_crossFlag && 1 == data.recover_info.recover_type) {
			// handleRetainVols();
		}
		submittedFlag = true;
		Metronic.blockUI({target: '#awsrecovercontent',animate: true, cenrerY: true,});
    	pAjaxRequest(data, "/api/v1/cloud/jobs/restore", "POST", function (d) {
			Metronic.unblockUI('#awsrecovercontent');
			if (operateResponseList(d)) {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			} else {
				submittedFlag = false; // 创建失败则重置
			}
		}, true);
	}

	//存储选择改变事件
	var storageselectChange = function () {
		pointtypetreeInitFlag = false;
		vmTypetreeInitFlag = false;
		initPointTree();
		currentTree.checkAllNodes(false); // 清空树的已选时间点
		$('#VMGroupList li').remove();
		$('#timepointGroupList li').remove();
	}
	
	//初始化时间点展示方式和事件
	var initPointShowType = function () {
		pAjaxRequest({}, "/api/v1/cloud/storages", "GET", function (d) {
			var storageselect = $('#storageselect');
			storageselect.empty();
			storageselect.append('<option value="">' + LANG.UI_STORAGE_ALL + '</option>');
			let data = d.data.rows;
			for (var i = 0; i < data.length; i++) {
				var option = $("<option>").text(data[i].name).val(data[i].uuid);
				storageselect.append(option);
			}
		}, false);
		$('#storageselect').on('change', storageselectChange);
		$('#searchvm').on('keydown', (event) => {
			if (event.key === 'Enter' || event.keyCode === 13) {
				searchVM();
			}
		});
	}

	//搜索实例
	var searchVM = function(){
		
		var value = $('#searchvm').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		keywordCache = value;
		// 通过接口搜索
		initPointTree(false, 0, '', '', value);
		// // 前端搜索
		// var nodes = currentTree.getNodes();
		// if(!nodes || nodes.length == 0) return;
		// var checkNode =currentTree.getCheckedNodes();
		// var allNode = currentTree.transformToArray(currentTree.getNodes());
		// nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
		// if(nodeParamList.length!=0){
		// 	currentTree.hideNodes(allNode);
		// 	$('.two_tree').show();
		// 	$('#nosearchtips').hide();
		// }else{
		// 	$('.two_tree').hide();
		// 	$('#nosearchtips').show();
		// }
		// //连接搜索的和所勾选的
		// nodeParamList =nodeParamList.concat(checkNode);
		// var nodeParamList1 = currentTree.transformToArray(nodeParamList);
        // for(var n in nodeParamList1){
        // 	findParent(currentTree,nodeParamList1[n], value);
        // }
		// currentTree.showNodes(nodeParamList);
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
		if(timeInfo.type == 4){
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
		des += 
		'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ uuid +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ list.des +'">' + 
			'<div class="col1">' + 
				'<div class="cont">' + 
					'<div class="cont-col1"></div>' + 
					'<div class="cont-col2">' + 
						'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + list.des + '</div>' + 
					'</div>' + 
				'</div>' + 
			'</div>' + 
			'<div class="col2  pull-right delete-list">' + 
				'<a class="del'+ uuid +'" >' + 
					'<div class="label label-sm label-danger" style="padding:0;">' + 
						'<i class="viconfont vicon-cuowu"></i>' + 
					'</div>' + 
				'</a>' + 
			'</div>' + 
		'</li>';
        $('#speedList').append(des);
        $('.del'+ uuid).on('click', function(){
            $('.popover.in').remove();
            $('#speed' + uuid).remove();
            for(var j=0;j<speedList.length; j++){
                if(uuid == speedList[j].uuid){
                    speedList.splice($.inArray(speedList[j],speedList),1);
                }
			}
			// initSpeedStrategyDes();
        });
    }

    var initOldHighStrategy = function(data){
		if(data.length == 0){
			$('#awsRecoveryThreadNum').val(3);
			return;
		}

        var highInfo = data.high.highInfo;
        var check = data.high.check;
        if(!check) return;
		$('#awsRecoveryThreadNum').val(highInfo.threadnum);
        
	}

	var initSpinner = function(){
		$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('#awsRecoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
	}

	//加载策略对应描述
	var initStrategyDes = function(){
		initTimeStrategyDes();
		// initSpeedStrategyDes();
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
		if(recoverytype == 1){
			des += LANG.UI_JOB_ONCE_TIME_RECOVER;
		}else if(recoverytype == 4){
			des += LANG.UI_JOB_TIMING_RECOVER_TIME + ': ' + $('#oncetime').val();
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].time.des;
			initStrategyDesStyle($('.recoveryTimeDes'), des, oldDes);
		}else{
			$('.recoveryTimeDes').removeClass('font-green-seagreen');
		}
		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').prop('title', des);
	}

    var initHighStrategyDes = function(){
        var des = "";
		des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#awsRecoveryThreadNum').val();
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].high.des;
			initStrategyDesStyle($('.recoveryHighDes'), des, oldDes);
		}else{
			$('.recoveryHighDes').removeClass('font-green-seagreen');
		}
		$('.recoveryHighDes').html(des);
		$('.recoveryHighDes').prop('title', des);
        
	}

	
	var initDataChangeListeners = function(){
        initSpeedListeners();
		initHighListeners();
    }


    var initSpeedListeners = function(){
        //添加限速策略确定
        //$('#speed_submit').on('click', speedSubmit);
    }


    var initHighListeners = function(){
        $('#awsRecoveryThreadNum').on('input propertychange', function(){
            initHighStrategyDes();
		});
		$('.spinner-up').on('click', function(){
			initHighStrategyDes();
        });
        $('.spinner-down').on('click', function(){
			initHighStrategyDes();
        });
	}

	var handleValidation = function() {
		var recoverForm = $('#submit_form');

		recoverForm.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "#searchvm, #jobname",  // validate all fields including form hidden input
			rules: {
				vmname: {
					required: true,
					newvmname: true,
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

		$.validator.addMethod("newvmname", function(value, element) {
			$.validator.messages.newvmname = _VMNAMETIPS;
			value = $.trim(value);
			var reg = new RegExp(vmNameLimit.limit);
			return !_VMNAMEREG.test(value) && reg.test(value);
		});

		$.validator.addMethod("namelength", function(value, element) {
			if (vmNameLimit.disk_len && value.length > vmNameLimit.disk_len) {
				$.validator.messages.namelength = vmNameLimit.disk_msg;
				return false;
			}
			return true;
		});
	};

	var initSafeStrategy = function () {
		// 获取操作系统，单个vm或多个vm能统一时传入os_type
		let osTypeArr = []; // 记录有几种os_type
		$.each(data.recover_info.instance_configs, function (i, v) {
			if ('linux' === v.os_type) {
				if ($.inArray('Linux', osTypeArr) === -1) {
					osTypeArr.push('Linux');
				}
			} else if ('windows' === v.os_type) {
				if ($.inArray('Windows', osTypeArr) === -1) {
					osTypeArr.push('Windows');
				}
			} else {
				if ($.inArray('Other', osTypeArr) === -1) {
					osTypeArr.push('Other');
				}
			}
		});
		// osTypeArr只有Linux则os_type=Linux，只有Windows则=Windows，其余情况=Other
		let os_type;
		if (osTypeArr.includes('Linux') && !osTypeArr.includes('Windows') && !osTypeArr.includes('Other')) {
			os_type = 'Linux';
		} else if (osTypeArr.includes('Windows') && !osTypeArr.includes('Linux') && !osTypeArr.includes('Other')) {
			os_type = 'Windows';
		} else {
			os_type = 'Other';
		}
		// 根据所有时间点的状态初始化安全策略
		let virusData = $.fn.getAwsRecoveryVirusScanStatus();
		$('#virusConfig').virusDetectionCover(virusData.isNoScan, virusData.isHeathy, virusData.isInfect, false, {
			no_scan: {
				os_type: os_type
			},
			healthy: {
				os_type: os_type,
			},
			reflected: {
				virus_scan_status: virusData.isInfectButUnfinished ? $.fn.virusDefine.virus_scan_status.infected_but_unfinished : $.fn.virusDefine.virus_scan_status.infected, // 已感染但未完成扫描时状态改为4
				os_type: os_type
			}
		});
		// 时间点若有一个启用了完整性校验则恢复任务也启用
		let backup_disable_flag = true;
		$.each(data.point_info.points, function (i, d) {
			if (d.integrity_check_flag) {
				backup_disable_flag = false;
				return true;
			}
		});
		$('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.VM, 0, backup_disable_flag);
	}

	var initSoftwareVersionDiff = function(){
		pAjaxRequest({}, "/api/v1/users/auth_func", "GET", function (d) {
			let data = d.data;
			authFun = data;
			// 隐藏安全策略配置和描述
			if (!authFun.integrity) {
				$(`#completeConfig`).hide();
			}
			if (!CONF.FUNCTIONS.includes('virusKill')) {
				$(`#virusConfig`).hide();
			}
			if (!authFun.integrity && !CONF.FUNCTIONS.includes('virusKill')) {
				// 全部隐藏
				$(`.safeLi`).hide();
				$(`.safeDiv`).hide();
			}
		}, false);

	}

    return {
        //main function to initiate the module
        init: function () {
			handleValidation();
			initSoftwareVersionDiff();
			wizardInit();
        	initPointShowType();
			initPointTree();
        	initListener();
			inintDatatimePicker();
			initSpinner();
			initDataChangeListeners();
        },
    };
}();

jQuery(document).ready(function() {
	Metronic.blockUI({target: '.src-wrap__content', animate: true});
	setTimeout(() => {
		AWSRecover.init();
	}, 200);
});