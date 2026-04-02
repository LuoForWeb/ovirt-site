var VMRecover = function () {
	var sub_module_type = $('#subModuleType').val();
	var data = {
		pointInfo: {},
		recoverInfo: {},
		time_strategy: {
			time_type: 1, //恢复方式：1立即，4定时
			timing_time: ''
		},
		speedInfo: {},
		highInfo: {},
		verifyInfo: {is_integrity_check: '', integrity_error_handle: ''},
		typeInfo: {high: {trasfer: {}}},
		timetaskName: '',
		strategygroupuuid: ''
	};
	var storagetypetree, storagetypetreeInitFlag = false;
	var currentTree; //当前展示的树
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	var _VMNAMETIPS = LANG.UI_TOOLS_VMNAME_TIPS;
	var _HOSTSTORAGE = [];	//目的宿主机存储信息
	var _vcenterType;
	var _hypervisor;		//恢复目标虚拟化类型
	var vmuuidList = [];
	var nodeParamList;
	var initStrategyFlag =false;
	var initSpeedFlag = false;
	var speedList = [];
	var globalStrategy = [];
	var editFlag = false;
	var selectIPFlag = true;
	var vmPlugnInfoData;
	var vmNameLimit = {}; //虚拟机名称长度限制
	var mplimit = 2; //存储库每页显示的数量
	var tplimit  = 2 ; //时间点每页显示的数量
	var cbrselectnode  = []; //华为CBR选择的数据
	var treeshowType  = null;
	var policy_list; //xhere块存储策略
	var storageType; //用于磁带类型判断
	var hostCpuArch = 0; // 恢复目标主机的cpu架构


	var showType = 1; //展示方式
	var authFun = [];
	var newtimepoint = ""; //后端返回的假的时间点
	var currentPlatformUuid = ""; //当前恢复目标虚拟化中心
	var windowsFlag = false;
	var storage_type; //恢复源存储类型
	var hostTree;
	var currentHostNode;
	var targetWay = 1; // 恢复方式
	// 从备份数据跳转恢复页面
	const externalPointUuid = $('#externalPointUuid').val();
	const externalTaskUuid = $('#externalTaskUuid').val();
	const externalItemUuid = $('#externalItemUuid').val();
	const externalSubType = $('#externalSubType').val();

	// 病毒扫描状态
	const VIRUS_NO_SCAN = 0;
	const VIRUS_HEALTH = 2;
	const VIRUS_INFECT = 3;
	const VIRUS_INFECT_BUT_UNFINISHED = 4;
	var isNoScan = false;
	var isHeathy = false;
	var isInfect = false;
	var isInfectButUnfinished = false;

	// 恢复方式
	const VM_RECOVERY_WAY_NEW = 1;
	const VM_RECOVERY_WAY_SPECIFIC = 2;

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

		//选择批量配置虚拟机
		$('#vmssetting').on('switchChange.bootstrapSwitch',function(){
			if(this.checked){
				$(".vmssettingdiv").show();
				$(`.configsdiv`).find(`select[name=hoststorage]`).val($(`#vmsstorage`).val()).selectpicker('refresh');
				$(`.configsdiv`).find(`select[name=hoststorage]`).trigger('change');
				$(`.configsdiv`).find(`select[name=hostnetwork]`).val($(`#vmsnetwork`).val()).selectpicker('refresh');
				$(`.configsdiv`).find(`select[name=hostnetwork]`).trigger('change');
			}else{
				$(".vmssettingdiv").hide();
			}
		})

		//批量配置虚拟机选择存储
		$('#vmsstorage').on('change', function(){
			$(`.configsdiv`).find(`select[name=hoststorage]`).val($(this).val()).selectpicker('refresh');
			$(`.configsdiv`).find(`select[name=hoststorage]`).trigger('change');
		})

		//批量配置虚拟机选择网络
		$('#vmsnetwork').on('change', function(){
			$('select[name=hostnetwork]').val($(this).val()).selectpicker('refresh');
		})

		//批量配置虚拟机选择可用域
		$('#vmUsedomain').on('change', function(){
			$('select[name=usedomain]').val($(this).val());
		})

		//批量配置虚拟机恢复完成后开机
		$('#powercheck').on('switchChange.bootstrapSwitch',function(){
			$('input[name=vmpower]').bootstrapSwitch('state', this.checked);
		})

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
		//切换完整性校验开关
		$('#verifyflag').on('switchChange.bootstrapSwitch', verifyHandler);

		// 存储高负载时停止任务
		$('#storageHighLoad').on('switchChange.bootstrapSwitch', storageHighLoadHandler);

		$('#tobackup').on('click',function(){
			if (2 == sub_module_type) {
				LOCATION('./content/vm/vmbackup.php?sub_module_type=2', 'vmprotect');
			} else {
				LOCATION('./content/vm/vmbackup.php', 'vmprotect');
			}
		});

		//跳转到添加虚拟化中心
		$('#toaddvcenter').on('click',function(){
			if (2 == sub_module_type) {
				LOCATION('./content/vm/cloudplatform/add_cloud_platform.php?cloudType=private', 'infrastructure');
			} else {
				LOCATION('./content/vm/add_vcenter.php', 'infrastructure');
			}
		});





		//自动选择IP
		$('#diysystemip').on('click', function(){
			$('.selectipdiv').hide();
			$('.inputipdiv').show();
			selectIPFlag = false;
		})

		//手动输入IP
		$('#selectsystemip').on('click', function(){
			$('.selectipdiv').show();
			$('.inputipdiv').hide();
			selectIPFlag = true;
		})

		$('#verifyuser').on('click', function(){
			event.preventDefault();
			var selectNode = usergroupTree.getCheckedNodes(true);
			var vcenteruuid = selectNode[0].vcuuid;
			var hypervisor = selectNode[0].hypervisor;
			var groupname = selectNode[0].groupname;
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

		//输入线程数量检测
		// $('#recoveryThreadNum').blur(threadChange);

		//按原机恢复开关
		$('.original_recovery').on('switchChange.bootstrapSwitch',function(){
			$(this).bootstrapSwitch('state', this.checked);
		})

		$('#available_domain').on('change', loadHuaweiCloudStackVmConfig);

		$('#driverCheck_vm_button_checkDriver').on('click', function (){
			// 绑定驱动检测事件
			$('#driverCheck').hide();
			$('#driverCheckFailContinue').hide();

			var title = LANG.UI_DRIVER_CHECK;
			let msg1 = LANG.UI_PLATFORM_RECOVERY_CPU_TIPS; // 请选择宿主机：s%对应的CPU架构
			let msg2 = LANG.UI_PLATFORM_RECOVERY_OS_TYPE_TIPS; // 请选择宿主机：s%对应的操作系统类型
			let msg3 = LANG.UI_PLATFORM_RECOVERY_OS_VSERSION_TIPS; // 请选择宿主机：s%对应的操作系统版本
			// 获取时间点信息
			let timepointConfig = data.pointInfo.points;
			var checkss = true;
			for (var j in timepointConfig) {
				if (!checkss) {
					return;
				}
				var os_arch = getVmConfig(timepointConfig[j].timepointuuid,'cpu_type');
				if (parseInt(os_arch) == 0) {
					let newString = msg1.replace(/s%/, timepointConfig[j].vmname);
					UIToastr.showWarning(title, newString);
					checkss = false;
					break;
				}
				var os_type = getVmConfig(timepointConfig[j].timepointuuid,'os_type');
				if (parseInt(os_type) == 0) {
					let newString = msg2.replace(/s%/, timepointConfig[j].vmname);
					UIToastr.showWarning(title, newString);
					checkss = false;
					break;
				}
				var os_version = getVmConfig(timepointConfig[j].timepointuuid,'os_version');
				if (parseInt(os_version) == 0) {
					let newString = msg3.replace(/s%/, timepointConfig[j].vmname);
					UIToastr.showWarning(title, newString);
					checkss = false;
					break;
				}
			}

			if (checkss) {
				$('#driverCheck').show();
				$.fn.driverCheck.check($('#driverCheck'));
			}
		});

		// 驱动检测失败后继续恢复
		$('#continue_driver_fail').find('.icheck').on('ifClicked', dirverContinueClick);

		// 切换恢复方式
		$('#recovery_way_radio_group').on('change', function (e, oldValue, newValue) {
			targetWay = parseInt(newValue);
			if (1 == targetWay) {
				$(`#selectHost`).show();
				$(`#domainDiv`).hide();
				$('#unifysettingdiv').hide();
				$('#vmconfigs').hide();
				if ('undefined' !== typeof hostTree) {
					hostTree.checkAllNodes(false);
				}
			} else {
				// 指定虚拟机恢复，直接初始化每个虚拟机的配置为时间点的
				$(`#selectHost`).hide();
				//显示隐藏项目
				$('.dndiv').hide();
				$('#vmconfigs').show();
				// 隐藏驱动检测
				$('#driverCheck').hide();
				// if (CONF.VM_TYPE.HUAWEICLOUDSTACK == _hypervisor) {
				// 	$(`#domainDiv`).show();
				// }
				// initTimepointConfig(data.pointInfo.points);
				Metronic.blockUI({target: '#tab2',animate: true});
				// 初始化前没选择目标所以node传空对象
				initVMconfigV2({}, '', true);
			}
		});
	}

	// 获取虚拟机的一些配置信息
	const getVmConfig = function (timepoint_uuid, field) {
		return $(`.configsdiv[data-timepointuuid=${timepoint_uuid}]`).find(`select[name=${field}]`).val();
	}

	var dirverContinueClick = function (event) {
		var mode = $(this).data('mode');
		if (event.target.checked) {
			//如果是取消选中
			$('#continue_driver_fail').find('input').iCheck("uncheck");
		} else {
			$('#continue_driver_fail').find('input').iCheck("uncheck");
			$('#continue_driver_fail').find('input[data-mode=' + mode + ']').iCheck("check");
		}
	}

	//初始化appliance下拉框
	var initApplianceSelect = function(){
		$.fn.initApplianceSelect(currentPlatformUuid);
	}

	var speedModeHandler = function(){
		if(this.value == 2){
			$('.setSpeedStrategy').hide();
		}else{
			$('.setSpeedStrategy').show();
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

	//检查节点信息
	var checkTreeNodeInfo = function(zNodes){
		if(zNodes == "[]" || zNodes.length == 0){
			$("#nopointtips").show();
			$('.vcenter-tree').hide();
			$('#vmtypetree').hide();
			$('#storagetypetree').hide();
			// $('#pointshowtype').prop('disabled', true);
			return false;
		}else{
			$("#nopointtips").hide();
			$('.vcenter-tree').show();
			$('#storagetypetree').show();
			$('#vmtypetree').hide();
			$("#two_tree").show();
			// $('#pointshowtype').prop('disabled', false);
			return true;
		}
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
//		if(hypervisor == CONF.VM_TYPE.FLEXCLOUD || hypervisor == CONF.VM_TYPE.OPENSTACK || hypervisor == CONF.VM_TYPE.FLEXHCS){
//			//如果是类OpenStack,不展示刷新选项,因为OpenStack都是实时获取的,不需要再刷新
//			str = "";
//		}else{
		str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_VCENTER_SYNC + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>';
//		}

		str += '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
			'<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
		aObj.after(str);

		var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
		var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
		var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");

		if (hrefRefresh) hrefRefresh.bind("click", function(){
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(treeNode.hypervisor)){
				vcenterRefresh(treeId, treeNode);
			}else{
				hostRefresh(treeId, treeNode);
			}
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
		$("#diyHref_" + nodeTID + nodeID+ "_2").unbind().remove();
		$("#diyHref_" + nodeTID + nodeID + "_3").unbind().remove();
		$("#diyBtn_space_" + escapeJquery(treeNode.id)).unbind().remove();
	};

	//检查没有宿主机切换提示信息
	var checkHostNodeInfo = function(zNodes, id){
		if(!zNodes.length){
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
//					addHoverDom: addHoverDom,
// 					removeHoverDom: removeHoverDom,
			},
			callback: {
				beforeClick: hostNodeSelect,
				onCheck: hostOnCheck,
				beforeExpand: recoveryNodeExpand,
			}
		};
		hostTree = $.fn.zTree.init($("#host_tree"), setting, zNodes);
	};


	//选择时间点节点事件绑定
	var nodeSelect = function(treeId, treeNode, clickFlag){
		if(4 == treeNode.type){
			storagetypetree.checkNode(treeNode, !treeNode.checked, false, true);
		}else if(3 == treeNode.type){
			storagetypetree.checkNode(treeNode, !treeNode.checked, false, true);
			storagetypetree.expandNode(treeNode, true)
		}else{
			if (1 === treeNode.moreType) {
				// 加载更多虚拟机
				initStorageTree(true, treeNode.nextOffset, treeNode.pId, treeNode.task_uuid);
			} else if (3 === treeNode.moreType || 4 === treeNode.moreType) {
				// 加载更多时间点
				let parentNode = currentTree.getNodeByParam('id', treeNode.pId);
				let parentUuid = 3 === treeNode.moreType ? treeNode.vm_uuid : treeNode.pId;
				getSyncVcenterInfo(treeId, parentNode, false, false, false, true, treeNode.nextOffset, parentUuid);
			} else {
				storagetypetree.expandNode(treeNode, true)
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
		var nodeuuid = $('#nodeselect').val();
		var storageuuid = $('#storagetypeselect').val();
		if (null === nodeuuid) {
			nodeuuid = '';
		}
		var div = ".src-wrap__content";
		let p = {
			task_uuid: treeNode.task_uuid,
			vm_uuid: treeNode.vm_uuid,
			hypervisor_type: treeNode.hypervisor_type,
			disabled_flag: false, //备份数据禁用勾选增量差异标志
			manage_flag: false,
			vm_check: treeNode.checked,
			node_uuid: nodeuuid,
			storage_uuid: storageuuid,
			parent_uuid: parentUuid,
		};
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
			if (d.success) {
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
					$.fn.zTree.getZTreeObj(treeId).checkNode(targetNode, true, false, false);
					// 添加到缓存
					selectedTimepoint.push(targetNode);
					// 时间点备份的展开文件列表
					addPointList(treeId, targetNode);
				}
			} else {
				operateResponseList(d);
			}
		}, true);
	}
	//选择宿主机节点事件绑定
	var hostNodeSelect = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type || 3 == treeNode.type){
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
		addHoverDom(treeId, treeNode);
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

	//初始化目的宿主机树
	var initHostTree = function(){
		var p = {};
		p.sub_module_type = sub_module_type;
		p.one_hypervisor_flag = true;
		if(data.pointInfo.type == CONF.VM_TYPE.HUAWEICBR){
			//如果是存在于华为CBR的时间点 则虚拟化类型改成vmware
			p.hypervisor_type = 1;
		}else{
			p.hypervisor_type = data.pointInfo.type;
		}
		pAjaxRequest(p, "/api/v1/vm/jobs/restore/platforms", "GET", function (d) {
			setHostTree(d.data.rows);
		}, false);

		$('.dndiv').hide();
	}



	//替换特殊字符为下划线
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
						addPointList(treeId, node);
						$(this).modal('hide');
						selectedTimepoint.push(node);
						return true;
					} else {
						// 用户点击了“取消”
						tree.checkNode(node, false, false, false);
						addPointList(treeId, node);
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

	//勾选添加虚拟机显示列表
	var addPointList = function(id,node){
		var info = "";
		var liId = clearString(id + node.platform_uuid + node.id + node.vm_uuid); //添加虚拟机每列ID
		//路径再加上时间点
		node.timepath = node.path +"/"+node.name;
		if(node.checked){
			info +=
				'<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ decodeURIComponent(node.path) +'">' +
				'<div class="col1">' +
				'<div class="cont vmDetail">' +
				'<div class="cont-col1">' +
				'<div class="'+node.iconSkin+'"></div>' +
				'<div style="width:20px;height:20px;background: url(./img/vm/vm.png) 0 no-repeat;"></div>' +
				'</div>' +
				'<div class="cont-col2">' +
				'<div class="desc list-one" style="font-size: 14px;color: #333;padding: 9px 4px 0px 4px">' + node.vm_name + '</div>' +
				'<div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px">' +  node.name + '</div>' +
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
			//根据虚拟机树类型添加每一列到列表
			$('#VMGroupList').append(info);
			$('#' + escapeJquery(liId)).popover();	   //初始化tips
			//移除虚拟机显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				var treeObj = $.fn.zTree.getZTreeObj(id);
				$('.popover.in').remove();
				treeObj.checkNode(node,false,false);
				$('#' + escapeJquery(liId)).remove();
				selectedTimepoint = selectedTimepoint.filter(item => item.timepoint_uuid !== node.timepoint_uuid);
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
		let allNodes = storagetypetree.getCheckedNodes(true);
		for (var i = 0; i < allNodes.length; i++) {
			if (allNodes[i].vm_uuid == node.vm_uuid && allNodes[i].platform_uuid == node.platform_uuid && allNodes[i].timepoint_uuid != node.timepoint_uuid) {
				//如果虚拟机一样的话,就要取消其他的时间点
				storagetypetree.checkNode(allNodes[i], false, false, false);
				var liId = clearString(id + allNodes[i].platform_uuid + allNodes[i].id + allNodes[i].vm_uuid); //添加虚拟机每列ID
				$('#' + escapeJquery(liId)).remove();
			}
		}
		for (let i = 0; i < selectedTimepoint.length; i++) {
			if (selectedTimepoint[i].vm_uuid == node.vm_uuid && selectedTimepoint[i].platform_uuid == node.platform_uuid && selectedTimepoint[i].timepoint_uuid != node.timepoint_uuid) {
				storagetypetree.checkNode(selectedTimepoint[i], false, false, false);
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

	//虚拟机分组
	var timepointOnCheck = function(e, id, node){
		storage_type = node.storage_type;
		var type  = $('#storagetypeselect option:selected').attr('type');
		var flag = node.checked;
		checkHypervisorPoint(id, node);
		if (type != 12) {
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
		}

		checkStorageTypePoint(id, node); //磁带需求，磁带备份点和非磁带备份点互斥，不可同时选择

		var allNodes = storagetypetree.getCheckedNodes(true);
		if(type != 12){
			allNodes = selectedTimepoint;
			if(!checkSelectInOneNode(flag, storagetypetree, node, allNodes, false)){
				$('#VMGroupList li').remove();
				$('#cbrTimeGroupList li').remove();
				addPointList(id, node, showType);
				return;
			}
			storagetypetree.checkNode(node, flag, false, false);
			addPointList(id, node, showType);
		}else{
			//华为CBR
			if(!checkSelectInOneNode(flag, storagetypetree, node, allNodes, true)){
				$('#VMGroupList li').remove();
				$('#cbrTimeGroupList li').remove();
				if(node.type == 3 && node.checked){
					addPointList(id, node);
				}
				return;
			}
			//如果选中时间点 取消他的所有邻居节点
			if(flag){
				var parentNode = node.getParentNode();
				var broNodes = storagetypetree.getNodesByParam('type', 3, parentNode);
				for(var i=0; i<broNodes.length; i++){
					if(broNodes[i].vmuuid == node.vmuuid){
						storagetypetree.checkNode(broNodes[i], false, false, false);
						var liId = clearString(id+broNodes[i].vcenteruuid + broNodes[i].id + broNodes[i].vmuuid); //添加虚拟机每列ID
						$('#' + escapeJquery(liId)).remove();
					}
				}
			}
			storagetypetree.checkNode(node, flag, false, false);
			addPointList(id, node);
		}
	}
	var hostOnCheck = function(e, id, node){
		currentHostNode = node;
		var allNodes = hostTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			hostTree.checkNode(allNodes[i], false, false, false);
		}
		hostTree.checkNode(node, true, false, false);
		//显示隐藏项目
		$('#vmconfigs').show();
		$('.dndiv').show();
		// 隐藏驱动检测
		$('#driverCheck').hide();
		//如果是跨平台到openstack(暂时屏蔽)
//		if(data.pointInfo.type != node.hypervisor && (node.hypervisor  == CONF.VM_TYPE.FLEXCLOUD || node.hypervisor  == CONF.VM_TYPE.OPENSTACK || 
//				node.hypervisor  == CONF.VM_TYPE.FLEXHCS || node.hypervisor  == CONF.VM_TYPE.INCLOUDOPENSTACK)){
//			$('.applianceDiv').show();
//		}else{
//			$('.applianceDiv').hide();
//		}

		// //如果虚拟化是SCP，禁止选择网络，暂不支持
		// if(node.hypervisor == CONF.VM_TYPE.SANGFORVVDK){
		// 	$('select[name="hostnetwork"]').prop('disabled', true);
		// }else{
		// 	$('select[name="hostnetwork"]').prop('disabled', false);
		// }

		if(treeshowType != 12){
			Metronic.blockUI({target: '#tab2',animate: true});
			$('#nodeconfigs').hide();
			getHostCommonInfo(node);
			if (CONF.VM_TYPE.HUAWEICLOUDSTACK == node.hypervisor) {
				$('.domainDiv').show();
				$('#unifysettingdiv').hide();
				$('#vmconfigs').hide();
				// 华为云stack需要先初始化可用域，选择可用域后再获取虚拟机配置
				initAvailableDomain(node);
			} else {
				$('.domainDiv').hide();
				$('#unifysettingdiv').show();
				$('#vmconfigs').show();
				initVMconfigV2(node);
			}
			initApplianceSelect();
		}else{
			initCBRnodeconfig();
			initCBRVMconfigV2(node);
		}

	}

	// 获取恢复目标主机信息：cpu架构
	var getHostCommonInfo = function (node) {
		if (CONF.VM_TYPE.HYPERV == node.hypervisor) {
			// hyperv获取时会超时报错暂不处理
			return;
		}
		let p = {
			hypervisor_type: node.hypervisor,
			platform_uuid: node.vcuuid,
			host_uuid: node.groupuuid ?? node.id,
			region: CONF.VMTYPE_GROUP.OPENSTACK.includes(node.hypervisor) ? node.name : '', // 区域名
		}
		pAjaxRequest(p, "/api/v1/vm/host/common_info", "GET", function (d) {
			if (d.success) {
				hostCpuArch = d.message.cpu_arch;
			}
		});
	}

	// 初始化华为云stack可用域 todo 改为新接口
	var initAvailableDomain = function (node) {
		var p = JSON.stringify({
			vcenteruuid: node.vcuuid, hypervisor: node.hypervisor, groupname: node.groupname,
			groupuuid: node.groupuuid, username: node.username, password: node.password,
			timepointuuid: data.pointInfo.points[0].timepointuuid,
			region: node.name
		});
		Metronic.blockUI({target: '#tab2', animate: true});
		$.post(CONF.AJAXPATH, {m: CONF.M.VM, f: 'getOpenStackAvailableDomain', p: p}, function (d) {
			Metronic.unblockUI('#tab2');
			let data = JSON.parse(d);
			let domain = data.domain;
			let thisDomain = data.thisDomain;
			$('#available_domain').empty();
			$.each(domain, function (i, v) {
				let option = $("<option>").text(v.text).val(v.uuid);
				$('#available_domain').append(option);
			});
			if (thisDomain) {
				$('#available_domain').val(thisDomain);
				if ($('#available_domain').val()) {
					// 如果匹配到则直接获取配置
					loadHuaweiCloudStackVmConfig();
				}
			}
		});
	}

	// 异步加载华为云stack的虚拟机配置
	var loadHuaweiCloudStackVmConfig = function () {
		if (0 == $('#available_domain').val()) {
			return;
		}
		$('#unifysettingdiv').show();
		$('#vmconfigs').show();
		initVMconfigV2(currentHostNode, $('#available_domain').val());
	}

	//初始化xhere块存储策略
	var initXhereVolumePolicyList = function (node) {
		let p = {platform_uuid: node.vcuuid};
		pAjaxRequest(p, "/api/v1/vm/xhere/volume_policy", "GET", function (d) {
			policy_list = d.data.rows;
		}, false);
	}

	//初始化网络和存储(flexcloud,openstack)
	var initNetworkAndStoreGroupUser = function(node, zoneName = ''){
		let p = {
			platform_uuid: node.vcuuid,
			hypervisor_type: node.hypervisor,
			group_name: node.groupname,
			group_uuid: node.groupuuid,
			username: node.username,
			password: node.password,
			zonename: zoneName,
			region: node.name,
		};
		pAjaxRequest(p, "/api/v1/vm/openstack/configs", "GET", function (d) {
			Metronic.unblockUI('#tab2');
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
			var storage = [];
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
			if (CONF.VM_TYPE.HUAWEICLOUDSTACK == _hypervisor) {
				// 可用域和外部一致
				let domain = $('#available_domain').val();
				useDomain.val(domain);
				diskDomain.val(domain);
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

			// 网络统一初始化
			for (let i = 0; i < data.network.length; i++) {
				hostnetwork.append($("<option>").text(data.network[i].text).val(data.network[i].uuid));
			}

			//选择虚拟机加载原来选择的网络
			var vmList = vmPlugnInfoData.config;
			for(var i=0;i<vmList.length;i++){
				var uuid = vmList[i].vm_uuid;
				var netList = vmList[i].net_list;
				//加载原虚拟机实例类型
				var diskList = vmList[i].disk_list;
				var rootDisk = parseInt(diskList[0].virtual_size_unit);
				//实例类型匹配规则：实例类型id相等
				$.each(instanceList, function (idx, v) {
					if (vmList[i].flavor_id == v.flavor_id) {
						$('.'+clearString(uuid) + " select[name=instance_socket]").val(v.uuid);
						return true;
					}
				})
				for (let j = 0; j < diskList.length; j++) {
					hoststorage = $('#accordionvm').find('.panel').eq(i).find('select[name=hoststorage]').eq(j);
					hoststorage.empty();
					for (let k = 0; k < data.storage.length; k++) {
						let selected = '';
						let oriStorageDes = '';
						// 当前磁盘是原存储时显示提示
						if (diskList[j].src_storage_uuid == data.storage[k].uuid) {
							selected = 'selected';
							oriStorageDes = `(${LANG.UI_RECOVERY_ORIGINAL_STORAGE})`;
						}
						let option = $(`<option ${selected}>`).text(data.storage[k].text + oriStorageDes).val(data.storage[k].uuid);
						hoststorage.append(option);
						storage.push(data.storage[k].uuid);
					}
					if ($.inArray(diskList[j].src_storage_uuid, storage) != -1) {
						//如果原来存储在当前宿主机里则复用原来的存储
						hoststorage.val(diskList[j].src_storage_uuid);
					}
					// 资源池的存储
					if (diskList[j].storage_pool.pool_uuid && storage.includes(diskList[j].storage_pool.pool_uuid)) {
						hoststorage.val(diskList[j].storage_pool.pool_uuid);
					}
				}
				for(var j=0;j<netList.length;j++){
					// hostnetwork = $('.'+clearString(uuid + netList[j].network_name) + " select[name=hostnetwork]");
					hostnetwork = $('#accordionvm').find('.panel').eq(i).find('select[name=hostnetwork]').eq(j);
					hostnetwork.empty();
					for (var k = 0; k < data.network.length; k++) {
						// 有多个网卡且当前网卡是原网卡时显示提示
						let selected = '';
						let oriNetworkDes = '';
						if (netList[j].src_network_uuid == data.network[k].uuid) {
							selected = 'selected';
							oriNetworkDes = `(${LANG.UI_RECOVERY_ORIGINAL_NETWORK})`;
						}
						var option = $(`<option ${selected}>`).text(data.network[k].text + oriNetworkDes).val(data.network[k].uuid);
						hostnetwork.append(option);
						network.push(data.network[k].uuid);
					}
					if($.inArray(netList[j].src_network_uuid, network) != -1){
						//如果原来网络在当前宿主机里则复用原来的网络
						hostnetwork.val(netList[j].src_network_uuid);
					}
				}
			}
			//显示隐藏项目
			$('#vmconfigs').show();
			$('.dndiv').show();
			$('#vmrecovercontent').find('.button-next').prop('disabled', false);
			$('#nodeconfigs').hide();
			$(`select[name=instance_socket]`).selectpicker('refresh');

			$('select[name=hoststorage]').selectpicker('refresh');
			$('select[name=hostnetwork]').selectpicker('refresh');
		}, false);
	}

	//初始化网络和存储(一般情况)
	var initNetworkAndStore = function(node){
		var p = {};
		p.hypervisor_type = node.hypervisor;
		p.platform_uuid = node.vcuuid;
		p.host_uuid = node.id;
		_HOSTSTORAGE = [];
		$('select[name=hoststorage]').empty();
		$('select[name=hostnetwork]').empty();
		pAjaxRequest(p, "/api/v1/vm/host/network_storage", "GET", function (d) {
			Metronic.unblockUI('#tab2');
			var data = d.data;
			_vcenterType = data.vcenter_type;
			_HOSTSTORAGE = data;

			// 目标存储
			var hoststorage = $('select[name=hoststorage]');
			hoststorage.empty();
			for(var i=0; i<data.storage.length; i++){
				var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
				hoststorage.append(option);
			}

			// 统一配置处的目标网络
			var hostnetworkUnify = $('#vmsnetwork');
			hostnetworkUnify.empty();
			for(var i=0; i<data.network.length; i++){
				var option = $("<option>").text(data.network[i].text);
				if (CONF.VM_TYPE.VMWARE == _hypervisor) {
					//vmware传网卡名称
					if (0 == data.network[i].uuid) {
						option.val('');
					} else {
						option.val(data.network[i].text);
					}
				} else {
					option.val(data.network[i].uuid);
				}
				hostnetworkUnify.append(option);
			}
			hostnetworkUnify.selectpicker('refresh');

			// 每个虚拟机的目标存储和目标网络
			var vmList = vmPlugnInfoData.config;
			$.each(vmList, function (idx, val) {
				$.each(val.disk_list,function (idxDisk, valDisk) {
					let hoststorage = $('#accordionvm').find('.panel').eq(idx).find('select[name=hoststorage]').eq(idxDisk);
					hoststorage.empty();
					let storage = [];
					for (let i = 0; i < data.storage.length; i++) {
						let oriStorageDes = '';
						let option = $(`<option data-type="${data.storage[i].driver_type}">`);
						option.val(data.storage[i].uuid);
						storage.push(data.storage[i].uuid);
						// 当前磁盘是原存储时显示提示
						if (valDisk.src_storage_uuid == data.storage[i].uuid) {
							oriStorageDes = `(${LANG.UI_RECOVERY_ORIGINAL_STORAGE})`;
							option.attr('selected', true);
						}
						option.text(data.storage[i].text + oriStorageDes);
						hoststorage.append(option);
					}
					// 资源池的存储
					if (valDisk.storage_pool.pool_uuid && storage.includes(valDisk.storage_pool.pool_uuid)) {
						hoststorage.val(valDisk.storage_pool.pool_uuid);
					}

					if (CONF.VMTYPE_GROUP.HUAWEIKVM.includes(node.hypervisor)) {
						// 恢复到华为kvm，目标存储为nfs时置备模式锁定为【精简】
						if (hoststorage.find(`option:selected`).data('type') === 'netfs') {
							hoststorage.closest('tbody').find('select[name=setting_mode]').val(1).prop('disabled', true);
						} else {
							hoststorage.closest('tbody').find('select[name=setting_mode]').prop('disabled', false);
						}
					}
				});
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
						} else {
							option.val(data.network[i].uuid);
						}
						if (valNet.src_network_uuid == data.network[i].uuid) {
							oriNetworkDes = `(${LANG.UI_RECOVERY_ORIGINAL_NETWORK})`;
							option.attr('selected', true);
						}
						option.text(data.network[i].text + oriNetworkDes);
						hostnetwork.append(option);
					}
					hostnetwork.selectpicker('refresh');
				})
			});

			//批量配置虚拟机选择存储
			for(var i=0;i<vmuuidList.length;i++){
				var id = escapeJquery(vmuuidList[i]);
				$('#vmstorage'+id+' select[name=hoststorage]').on('change', {id: id},function(e){
					if(_hypervisor == CONF.VM_TYPE.HYPERV && _vcenterType == 1){
						$('#vmstorage'+ e.data.id+' select[name=hoststorage]').val($(this).val());
					}
				});
			}

			//如果虚拟化是SCP，禁止选择网络，暂不支持
			// if(node.hypervisor == CONF.VM_TYPE.SANGFORVVDK){
			// 	$('select[name="hostnetwork"]').prop('disabled', true);
			// }else{
			// 	$('select[name="hostnetwork"]').prop('disabled', false);
			// }

//			$('.button-next').prop('disabled', false);
			$('#vmrecovercontent').find('.button-next').prop('disabled', false);
			hoststorage.selectpicker('refresh');
		}, true);
	}



	//初始化虚拟机配置V2,跨平台
	var initVMconfigV2 = function(node, zoneName = '', specificFlag = false){
		$(`#vmconfigs`).hide();
		currentPlatformUuid = node.vcuuid;
		var p = {};
		p.hypervisor_type = node.hypervisor;
		p.platform_uuid = node.vcuuid;
		p.host_uuid = node.id;
		if (2 == sub_module_type) {
			p.host_uuid = node.groupuuid ?? node.id;
		}
		var allPoints = data.pointInfo.points;
		var srcVmType = data.pointInfo.type;
		var points = [];
		var pointsDetail = [];
		for(var i=0; i<allPoints.length; i++){
			var point = {};
			point.hypervisor = allPoints[i]['hypervisor'];
			point.timepointuuid = allPoints[i]['timepointuuid'];
			point.vcenteruuid = allPoints[i]['vcenteruuid'];
			point.vmuuid = allPoints[i]['vmuuid'];
			point.vmname = vmOldName[i][0] + "_" + clearStringEmpty(vmOldName[i][1]);
			point.oldname = vmOldName[i][0];
			point.timepoint = vmOldName[i][1];
			point.nodeuuid = allPoints[i]['nodeuuid'];
			point.config = allPoints[i]['config'];
			points.push(allPoints[i]['timepointuuid']);
			pointsDetail.push(point);
		}
		p.points = points;
		p.points_detail = pointsDetail;
		p.instant_flag = false;

		// 指定实例恢复的参数处理
		if (specificFlag) {
			p.hypervisor_type = pointsDetail[0].hypervisor;
			p.platform_uuid = '';
			p.host_uuid = '';
		}

		//xhere块存储策略
		if (CONF.VM_TYPE.XHERE == node.hypervisor) {
			initXhereVolumePolicyList(node);
		}
		pAjaxRequest(p, "/api/v1/vm/timepoints/config", "POST", function (d) {
			$(`#vmconfigs`).show();
			var data = d.data;
			if(!data.flag){
				//时间点不存在/磁带被占用
				if (50 == data.errorCode || 14025 == data.errorCode) {
					return UIToastr.showWarning(sub_module_type == 2 ? LANG.UI_INSTANCE_GET_VIRTUAL_CONFIG_INFO : LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO, data.errorMsg);
				}
				//如果获取失败
				return UIToastr.showWarning(sub_module_type == 2 ? LANG.UI_INSTANCE_GET_VIRTUAL_CONFIG_INFO : LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO,
					sub_module_type == 2 ? LANG.UI_INSTANCE_GETINFO_FAIL_TRY_AGIN : LANG.UI_VM_GETINFO_FAIL_TRY_AGIN);
			}

			// 遍历时间点检测目标主机cpu架构和时间点的是否一致，不一致提示无法恢复
			let x86Arch = [1, 2];
			let armArch = [3, 4];
			for (let i in data.source_config) {
				if (data.source_config[i].cpu_arch != 0 && hostCpuArch != 0
					&& (x86Arch.includes(data.source_config[i].cpu_arch) && !x86Arch.includes(hostCpuArch)
						|| armArch.includes(data.source_config[i].cpu_arch) && !armArch.includes(hostCpuArch))
				) {
					return UIToastr.showWarning(LANG.UI_VM_CHECK_CPU_ARCH, LANG.UI_VM_TIMEPOINT_CPU_ARCH_DIFF_WITH_HOST);
				}
			}

			vmPlugnInfoData = data;
			vmNameLimit = data.vmNameLimit;
			windowsFlag = data.windows_flag;

			//xhere块存储策略
			data.volume_policy = policy_list;

			data.platform_uuid = p.platform_uuid;
			data.host_uuid = p.host_uuid;
			data.region = CONF.VMTYPE_GROUP.OPENSTACK.includes(node.hypervisor) ? node.name : '';
			data.recoveryWay = specificFlag ? VM_RECOVERY_WAY_SPECIFIC : VM_RECOVERY_WAY_NEW;
			$('#accordionvm').vmRecoveryConfig(data, pointsDetail);

			if (specificFlag) {
				_hypervisor = pointsDetail[0].hypervisor;
				Metronic.unblockUI('#tab2');
			} else {
				if(_hypervisor != node.hypervisor){
					$.fn.vmRecoveryResetOptions(); //切换虚拟化类型重置高级配置默认选项
				}
				_hypervisor = node.hypervisor;
				//初始化网络和存储
				if(CONF.VMTYPE_GROUP.OPENSTACK.includes(_hypervisor)){
					initNetworkAndStoreGroupUser(node, zoneName);
					//其他虚拟化恢复到openstack虚拟化 隐藏删除实例删除卷选项，openstack到openstack才显示该选项
					if (!CONF.VMTYPE_GROUP.OPENSTACK.includes(srcVmType)) {
						$('.delDiskDiv').hide();
					}
				}else{
					initNetworkAndStore(node);
				}
			}

			if (CONF.VM_TYPE.INCLOUDKVM == _hypervisor || CONF.VM_TYPE.INSPURVVDK == _hypervisor || CONF.VM_TYPE.KSPHERE == _hypervisor) {
				_VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\]<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+]");
				_VMNAMETIPS = LANG.UI_TOOLS_VMNAME_ICS_TIPS;
			}

			// 统计时间点病毒扫描状态，确定扫描策略的显示
			$.each(data.config, function (i, v) {
				switch (v.virus_scan_status) {
					case VIRUS_NO_SCAN:
						isNoScan = true;
						break;
					case VIRUS_HEALTH:
						isHeathy = true;
						break;
					case VIRUS_INFECT:
						isInfect = true;
						break;
					case VIRUS_INFECT_BUT_UNFINISHED:
						isInfect = true;
						isInfectButUnfinished = true;
						break;
					default:
						break;
				}
			});
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
				$('#vmrecovercontent').find('.button-previous').css('visibility', 'hidden');
				$('#vmrecovercontent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#vmrecovercontent').find('.button-previous').css('visibility', 'visible');
				$('#vmrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
			}

			if (current >= total) {
				$('#vmrecovercontent').find('.button-next').hide();
				$('#vmrecovercontent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#vmrecovercontent').find('.button-next').show();
				$('#vmrecovercontent').find('.button-submit').css('visibility', 'hidden');
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
				// 还原tab-pane的高度
				$(".tab-pane__row").css('height', '100%');
				$('#vmrecovercontent').find('.button-next').prop('disabled', false);
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

		$('#vmrecovercontent').find('.button-previous').css('visibility', 'hidden');
		$('#vmrecovercontent .button-submit').click(submit).css('visibility', 'hidden');
	};

	var step1Valid = function(){
		$('.groupdiv').hide();

		var nodes = currentTree.getCheckedNodes();
		if (12 != treeshowType) {
			nodes = selectedTimepoint;
		}
		if(!nodes.length){
			$(".selecttimepointtip").html(LANG.UI_RECOVERY_SELECT_POINT).show();
			// 动态设置tab-pane的高度
			$(".tab-pane__row").css('height', 'calc(100% - 80px)');
			return false;
		}

		data.pointInfo.points = [];
		var showStr = [];
		var pointshowtype = treeshowType;
		vmOldName = [];
		if(12 == pointshowtype){
			$.each(nodes, function(i, d){
				//华为CBR
				if(d.eventtype == "tp"){
					var vaultnode = d.getParentNode();
					var projectnode =vaultnode.getParentNode();
					var storageprojectid = projectnode.id.split("_");
					var project_id  = storageprojectid[1];
					var regionnode = projectnode.getParentNode();
					var storageregionid = regionnode.id.split("_");
					var region_id  = storageregionid[1];
					var  jsondata  = {
						vmuuid:d.parentid,
						timepointuuid:newtimepoint, //真实数据
						vcenteruuid:getstoragenode(d).id, //存储id
						//hypervisor使用vmware的
						hypervisor:1,
						nodeuuid:d.nodeuuid,
						projectid:project_id,
						regionid:region_id,
						path:d.path,
					}
					data.pointInfo.points.push(jsondata);
					vmOldName.push([d.parentname,d.name]);
					//如果是华为CBR 直接展示路径
					showStr.push(d.path);
					data.pointInfo.type  = 1;
				}
			})
		}else{
			$.each(nodes, function(i, d){
				//按虚拟机分组
				if(3 == d.type || 4 == d.type){
					var jsondata = {
						vmuuid: d.vm_uuid,
						vmname: d.vm_name,
						timepointuuid: d.timepoint_uuid,
						vcenteruuid: d.platform_uuid,
						hypervisor: d.hypervisor_type,
						nodeuuid: d.node_uuid,
						config: d.config,
						integrity_check_flag: d.integrity_check_flag,
					};
					data.pointInfo.points.push(jsondata);
					vmOldName.push([d.vm_name, d.point_name]);
					showStr.push(d.path + "(" + d.point_name + ")" + "<br>");
				}
				data.pointInfo.type = d.hypervisor_type;
			});
		}
		//设置storageuuid
		var storageuuid = $("#storagetypeselect").val();
		// if(storageuuid == null  || storageuuid == ""){
		// 	data.pointInfo.storageuuid =  nodes[0].storageuuid;
		// }else{
		// 	data.pointInfo.storageuuid =  storageuuid;
		// }
		data.pointInfo.storageuuid =  storageuuid;
		//初始华为CBR虚拟机配置信息
		//如果 是华为 需要获取虚拟机配置信息
		if(treeshowType == 12){
			cbrselectnode =  nodes;
			initHuaWeiCBRconfig();
		}
		initHostTree();
		// 先隐藏宿主机树
		$(`#selectHost`).hide();
		// 隐藏驱动检测
		$('#driverCheck').hide();
		$('#driverCheck_vm_button').hide();
		$('#driverCheckFailContinue').hide();

		if (CONF.VMTYPE_GROUP.OPENSTACK.includes(data.pointInfo.type)) {
			// todo 暂时仅openstack支持指定实例恢复
			$(`#recovery_way`).show();
		} else {
			$(`#recovery_way`).hide();
		}
		// 默认选中新建vm的恢复方式
		$(`#recovery_way_radio_group`).find(`label[value=1]`).trigger('click');
		$(`#selectHost`).show();
		$(`#domainDiv`).hide();
		$('#unifysettingdiv').hide();
		$('#vmconfigs').hide();
		$.fn.specificVmRecovery.cleanCache();

		showStep1(showStr);
		$('#vmrecovercontent').find('.button-next').prop('disabled', true);
		// 根据时间点列表获取可用的节点
		let nodeUuid = getAvailableNodeByTimepoints(data.pointInfo.points);
		initBackupServerAddr(nodeUuid);	//初始化备份系统节点IP
		//设置重连时间和次数的默认值
		$('#reconnect_time').val(5);
		$('#reconnect_interval').val(5);
		return true;
	}

	//第三步恢复方式的显示
	var recoveryTypeShow = function(){
		$.fn.vmRecoveryStep3Show({
			_sourceHypervisor: data.pointInfo.type,
			_hypervisor: _hypervisor,
			authFun: authFun,
			storage_type: storage_type,
			windowsFlag: windowsFlag,
			specificFlag: targetWay === VM_RECOVERY_WAY_SPECIFIC
		});
	}


	//得到任务名,这里需要注意的是在跨虚拟化平台恢复的时候需要用目的地的名字,
	//这里是需要在选择目的地节点后再初始化.
	var getTaskName = function(hypervisor){
		pAjaxRequest({hypervisor_type: hypervisor}, "/api/v1/vm/jobs/restore/job_name", "GET", function (d) {
			$('#jobname').val(d.data.info);
		}, false);
	}

	var showStep1 = function(nodes){
		var jobName = CONF.VM_DES[parseInt(data.pointInfo.type)] + LANG.UI_PUBLIC_RECOVERY;
		var str = jobName + "<br>";
		$.each(nodes, function(i, d){
			str += d;
		});
		$('.vmtypeshow').html(str);
		initHighStrategyDes();
	}

	var step2Valid = function(){
		//未初始化恢复目的宿主机
		if(!hostTree){
			$(".setrecover2tip").html(LANG.UI_RECOVERY_SELECT_HOST).show();
			return false;
		}
		var vm_memory = $("input[name=vm_memory]").val();
		if(vm_memory == 0){
			UIToastr.showInfo(LANG.UI_RECOVERY_MEMORY_SIZE,LANG.UI_RECOVERY_MEMORY_SIZE_HITE);
			return false;
		}
		var showStr = '';
		//恢复后开机
		data.recoverInfo.recover2 = 2;	//异机恢复,原机恢复去掉
		data.cross_platform_appliance_uuid = '';
		if (VM_RECOVERY_WAY_NEW == targetWay) {
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(_hypervisor)){
				//如果是openstack,设置所选项目信息
				var nodes = hostTree.getCheckedNodes();
				if(!nodes.length){
					UIToastr.showWarning(LANG.UI_MOTION_RECOVERY_HOST, LANG.UI_MOTION_RECOVERY_HOST_TIPS);
					return false;
				}
				if(!nodes[0].username){
					UIToastr.showWarning(LANG.UI_MOTION_VALIDATE_ADMIN,LANG.UI_MOTION_VALIDATE_ADMIN_TIPS);
					return false;
				}
				data.recoverInfo.vcenteruuid = nodes[0].vcuuid;
				data.recoverInfo.groupname = nodes[0].groupname;
				data.recoverInfo.region = nodes[0].name;
				data.recoverInfo.groupuuid = nodes[0].groupuuid;
				data.recoverInfo.username = nodes[0].username;
				data.recoverInfo.password = nodes[0].password;
				data.recoverInfo.hypervisor = nodes[0].hypervisor;

				data.cross_platform_appliance_uuid = "";
			}else{
				//其他虚拟化,设置目的宿主机
				var nodes = hostTree.getCheckedNodes();
				if(!nodes.length){
					$(".setrecover2tip").html(LANG.UI_RECOVERY_SELECT_HOST).show();
					return false;
				}
				//恢复到宿主机
				data.recoverInfo.vcenteruuid = nodes[0].vcuuid;
				data.recoverInfo.hostuuid = nodes[0].id;
				data.recoverInfo.hypervisor = nodes[0].hypervisor;
			}


			showStr += LANG.UI_RECOVERY_TO + nodes[0].getParentNode().name + " -> " + nodes[0].name + "<br><br>";
			showStr += (sub_module_type == 2 ? LANG.UI_RECOVERY_NEW_INSTANCE_NAMES : LANG.UI_RECOVERY_NEW_NAMES) + ": <br>";
			data.recoverInfo.vmconfigs = $('#accordionvm').getvmRecoveryConfig(vmPlugnInfoData);
			if(!data.recoverInfo.vmconfigs){
				return false;
			}

			//xhere块存储策略必选
			if (CONF.VM_TYPE.XHERE == _hypervisor) {
				var validFlag = true;
				let policy_id_list = policy_list.map(item => parseInt(item.uuid));
				$.each(data.recoverInfo.vmconfigs, function (i, v) {
					$.each(v.storage, function (idx, val) {
						// 没有块存储策略或原有策略不在现有策略中
						if (!val.policy_id || !policy_id_list.includes(parseInt(val.policy_id))) {
							validFlag = false;
							UIToastr.showWarning(LANG.UI_VM_SETTING_V2_SELECT_BLOCK_POLICY, LANG.UI_VM_SETTING_V2_SELECT_BLOCK_POLICY_TIPS);
							return false;
						}
					});
					if (!validFlag) return false;
				});
				if (!validFlag) return false;
			}

			//同步ajax校验密码
			var checkflag = true;
			var params = {};
			params.vms = data.recoverInfo.vmconfigs;
			pAjaxRequest(params, "/api/v1/vm/jobs/check_encrypt_password", "POST", function (d) {
				if (!d.success) {
					operateResponseList(d, LANG.UI_VM_DB_ENCRY_PWD_VERIFI);
					checkflag = false;
				}
			}, false);

			if(!checkflag) return false;
			data.recoverInfo.names = [];
			var flag = true;
			var vmnameFlag = true; //检查恢复后虚拟机名是否规范
			var vmNameLengthFlag = true; //检查恢复后虚拟机名长度是否规范
			var reg = new RegExp(vmNameLimit.limit);
			var diskReg = new RegExp(vmNameLimit.disk_limit);
			//得到虚拟机恢复名
			let warningNameTitle = sub_module_type == 2 ? LANG.UI_VM_RESTORE_INSTANCE_NAME_TITLE : LANG.UI_VM_RESTORE_NAME_TITLE;
			let warningNameNull = sub_module_type == 2 ? LANG.UI_RECOVERY_INSTANCE_NAME_NOT_NULL : LANG.UI_RECOVERY_NAME_NOT_NULL;
			let warningNameInvalid = sub_module_type == 2 ? LANG.UI_TOOLS_INSTANCE_NAME_TIPS : LANG.UI_TOOLS_VMNAME_TIPS;
			let warningNameRecovered = sub_module_type == 2 ? LANG.UI_VM_SETTING_REC_INSTANCE_NAME : LANG.UI_VM_SETTING_REC_NAME;
			let warningDiskTitle = sub_module_type == 2 ? LANG.UI_INSTANCE_RESTORE_DISK_NAME_TITLE : LANG.UI_VM_RESTORE_DISK_NAME_TITLE;
			$.each(data.recoverInfo.vmconfigs, function(i, d){
				var value = $.trim(d.vmname);
				if("" == value){
					//$(".setrecover2tip").html(LANG.UI_RECOVERY_NAME_NOT_NULL).show();
					UIToastr.showWarning(warningNameTitle, warningNameNull);
					flag = false;
					return false;
				}
				if ('' == value || !reg.test(value)) {
					//$(".setrecover2tip").html(_VMNAMETIPS).show();
					UIToastr.showWarning(warningNameTitle, warningNameInvalid);
					vmnameFlag = false;
					return false;
				}
				if (value.length > vmNameLimit.len) {
					//$(".setrecover2tip").html(LANG.UI_VM_SETTING_REC_NAME + vmNameLimit.msg).show();
					UIToastr.showWarning(warningNameTitle, warningNameRecovered + vmNameLimit.msg);
					vmNameLengthFlag = false;
					return false;
				}

				data.recoverInfo.names[i] = value;
				showStr += value + "<br>";

				// 校验磁盘名称
				$.each(d.storage, function (idx, disk) {
					let diskName = $.trim(disk.disk_name);
					if ('' == diskName || !diskReg.test(diskName)) {
						UIToastr.showWarning(warningDiskTitle, LANG.UI_TOOLS_DISK_NAME_TIPS);
						flag = false;
						return false;
					}
				});
				if (!flag) {
					return false;
				}
			});
			if(!flag) return false;
			if (!vmnameFlag) return false;
			if (!vmNameLengthFlag) return false;
			showStr = showStr.substr(0, showStr.length-2) + "<br><br>";

			if (CONF.BD_STORAGE_TYPE.HUAWEICBR == treeshowType) {
				//新增计算节点
				showStr += LANG.UI_VM_COMPUTE_NODE +": "+$('#nodeconfig option:selected').text()+"<br>";
			}

			//验证虚拟机配置,主要是存储使用
			var validate = $('#accordionvm').vmConfigValidate({vmconfig:data.recoverInfo.vmconfigs, hoststorage:_HOSTSTORAGE, hypervisor: data.recoverInfo.hypervisor, nameLimit:vmNameLimit});
			if(!validate){
				return false;
			}
		} else {
			data.recoverInfo.hypervisor = _hypervisor;
			data.recoverInfo.vmconfigs = $('#accordionvm').getvmRecoveryConfig(vmPlugnInfoData);
			if(!data.recoverInfo.vmconfigs){
				return false;
			}
			let flag = true;
			$.each(data.recoverInfo.vmconfigs, (i, v) => {
				showStr += v.target_vm_path + "<br>";
				if (!v.target_vm_uuid) {
					UIToastr.showWarning(LANG.UI_RECOVERY_PLEASE_SELECT_GOAL, v.timepoint_des);
					flag = false;
					return false;
				}
			});
			if (!flag) {
				return false;
			}
		}

		// 驱动检测
		data.recoverInfo.drivers = [];
		let driverInfo = $.fn.getVmRecoveryDriverCheckInfo();
		if (driverInfo.showDriver) {
			data.recoverInfo.drivers = $.fn.driverCheck.getCheckResult($(`#driverCheck`));
			if (!driverInfo.isDriverChecked) {
				// 驱动检测失败且checkbox的值不是继续恢复，那么就阻止并给出提示
				UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_COMPLETE_DRIVER);
				return false;
			} else {
				if (!driverInfo.driverCheckResult && !$('#continueCheck').is(':checked')) {
					UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_PLATFORM_RECOVERY_DRIVER_NO_CONTINUE_TIPS);
					return false;
				}
			}
		}

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
		getTaskName(data.recoverInfo.hypervisor);
		showStep2(showStr);
		initStrategyDes();
		return true;
	}

	var showStep2 = function(str){
		$('.recovershow').html(str);
		recoveryTypeShow();
		initResourceLimit([data.pointInfo.points[0].nodeuuid]);
	}

	var step3Valid = function(){
		data.typeInfo.type = $('#recovertype').val();
		data.verifyInfo.is_integrity_check = $('#verifyflag').get(0).checked;
		data.verifyInfo.integrity_error_handle = $('#verifyalarmflag').get(0).checked;
		data.highInfo.storageHighLoadStopTask = $('#storageHighLoad').get(0).checked;
		data.highInfo.ignore_resource_limiting_flag = $('#ignore_resource_limit').get(0).checked;
		data.speedInfo = speedList;

		// 从插件获取传输策略参数
		let transferData = $.fn.getVmRecoveryTransferData(data, {
			_hypervisor: _hypervisor,
			selectIPFlag: selectIPFlag
		});
		if (false === transferData) {
			return false;
		}
		data.typeInfo.high.trasfer.mode = transferData.mode;
		data.typeInfo.high.trasfer.encrypt = transferData.encrypt;
		data.typeInfo.high.trasfer.encrypt_method = transferData.encrypt_method;
		data.typeInfo.high.trasfer.reconnect_times = transferData.reconnect_times;
		data.typeInfo.high.trasfer.reconnect_interval = transferData.reconnect_interval;
		data.typeInfo.high.trasfer.transfer_compress = transferData.transfer_compress;
		data.typeInfo.high.trasfer.async_transfer = transferData.async_transfer;
		data.appliancecheck = transferData.appliancecheck;
		data.highInfo.threadnum = transferData.threadnum;
		data.agent_uuid = transferData.agent_uuid;
		data.agent_pool_uuid = transferData.agent_pool_uuid;
		data.backup_server_ip = transferData.backup_server_ip;
		data.transport_ip_segment = transferData.transport_ip_segment;
		data.typeInfo.high.trasfer.single_vm_parallel_disk_transfer_count = transferData.single_vm_parallel_disk_transfer_count;
		data.typeInfo.high.trasfer.vm_single_disk_parallel_transfer_count = transferData.vm_single_disk_parallel_transfer_count;
		data.keep_recovery_volumes = transferData.keep_recovery_volumes;
		var thread = $('#recoveryThreadNum').val();
		if (thread == "" || thread > 8 || thread <= 0) {
			$('#recoveryThreadDiv').spinner("value", 3);
			initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}

		if (!getSafeStr()) {
			return false;
		}
		if (!getRetryStr()) {
			return false;
		}
		if('1' == data.typeInfo.type){
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
		showStep3();
		return true;
	}

	//组合时间策略
	var groupTimeStrategy = function (strategyConfigItem) {
		return {
			type: strategyConfigItem.type,
			start_time: strategyConfigItem.startTime,
			roll_flag: strategyConfigItem.rollFlag,
			roll_interval: strategyConfigItem.rollInterval,
			roll_end_time: strategyConfigItem.endTime,
			days: strategyConfigItem.days,
			des: strategyConfigItem.des
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
		var showStr1 = '', showStr2 = '';
		showStr1 = $('#recovertype').find("option:selected").text();
		if(!$.isEmptyObject(data.time_strategy)){
			if (4 == $('#recovertype').val()) {
				showStr1 +=  "(" + data.time_strategy.timing_time + ")";
			}
		}
		$('.reservetypeshow').html(showStr1);
		//高级策略
		// 忽略资源限制
		let highstrategystr = $('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes($('#ignore_resource_limit').get(0).checked);
		if (CONF.VM_TYPE.INSPURCLOUDPLATFORM == _hypervisor) {
			highstrategystr += '<br>' + $('.storageHighLoadLabel').html() + ": " + getSwitchDes($('#storageHighLoad').get(0).checked);
		}
		$('.highstrategyshow').html(highstrategystr);

		//限速策略
		data.speedLimit = getSpeedStrategyInfo();
		var speedlimitshow = $('.speedlimitshow');
		var speedLimitsStr = '';
		if (data.speedLimit.speedInfo.length != 0) {
			speedLimitsStr = '';
			for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
				speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
			}
		}

		if (speedLimitsStr == '') {
			speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedLimitsStr);

		// 传输策略
		$.fn.getVmRecoveryTransferDes(data, CONF.BD_STORAGE_TYPE.TAPE == storage_type);

//		if(type ==  CONF.VM_TYPE.VMWARE){
//			$('.applianceshow').show();
//		}else{
//			$('.applianceshow').hide();
//		}
		//校验策略
		var verifymodeshowStr = $('.verifyLable').html() + ": " +  getSwitchDes($('#verifyflag').get(0).checked);
		if($('#verifyflag').get(0).checked){
			verifymodeshowStr += "<br>"+ $('.verifyalarmLable').html()+": "+ getSwitchDes($('#verifyalarmflag').get(0).checked);
		}
		//显示校验策略
		$('.verifymodeshow').html(verifymodeshowStr);
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
		let jobName = $.trim($("#jobname").val());
		// 输入验证
		if(!customInputValidate('string',jobName)){
			return false;
		}
		data.taskName = $.trim($("#jobname").val());
		if($('#strategySelect option:selected').val()){
			data.strategygroupuuid = $('#strategySelect option:selected').val();
		}
		data.pointInfo.storagepath  = [];
		if(treeshowType == null || treeshowType == ""){
			//所有存储
			data.pointInfo.storage_media = data.pointInfo.points[0].storagetype;
		}else{
			//华为CBR或者其他特定存储类型
			data.pointInfo.storage_media  = treeshowType ;
		}
		for(var i = 0; i< data.pointInfo.points.length;i++){
			var nodeparam = {
				path:data.pointInfo.points[i].path,
				vmid:data.pointInfo.points[i].vmuuid
			}
			data.pointInfo.storagepath.push(nodeparam);
		}
		if(treeshowType == 12){
			data.pointInfo.points[0].timepointuuid =  newtimepoint;
		}
		data.recoverInfo.target_way = targetWay;
		submittedFlag = true;
		Metronic.blockUI({target: '#vmrecovercontent',animate: true, cenrerY: true,});
		pAjaxRequest(data, "/api/v1/vm/jobs/restore", "POST", function (d) {
			Metronic.unblockUI('#vmrecovercontent');
			if (operateResponseList(d)) {
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			} else {
				submittedFlag = false; // 创建失败则重置
			}
		}, true);
	}

	//存储类型改变事件
	var storageShowTypeChange =  function(){
		$('#searchvm').val('');
		var type  = $('#storagetypeselect option:selected').attr('type');
		treeshowType  = type;
		//如果是华为CBR 则初始化华为CBR的树
		if(12 == type){
			//隐藏虚拟机的
			//显示CBR的
			$('#VMGroupList').hide();
			$('#cbrTimeGroupList').show();
			initStoragedCBRTree();
			//清空已经选择的网络传输模式
			$('#transport_mode').val('nbd');
		}else{
			$('#VMGroupList').show();
			$('#cbrTimeGroupList').hide();
			initStorageTree();
			newtimepoint = ""; //创建的新的时间点清空
		}
		currentTree.checkAllNodes(false); // 清空树的已选时间点
		$('#VMGroupList li').remove();
		$('#cbrTimeGroupList li').remove();
		if (12 == type) {
			var length = $('#cbrTimeGroupList>li').size();
			var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
			$('.addTitle > span').html(timestr);
		}else{
			var timestr = LANG.UI_VM_SELECTED_TIME_POINT;
			$('.addTitle > span').html(timestr);
		}


	};
	//初始化存储类型展示方式和事件
	var initStorageShowType =  function(){
		pAjaxRequest({}, "/api/v1/storages/type", "GET", function (d) {
			var data = d.data;
			var storagetypeselect = $('#storagetypeselect');
			storagetypeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].storageid).attr("type",data[i].storagetype);
				storagetypeselect.append(option);
			}
		}, false);
		//绑定事件
		treeshowType = $('#storagetypeselect option:selected').attr('type');
		$('#storagetypeselect').on('change', storageShowTypeChange); //存储类型改变事件
		$('#searchvm').on('keydown', (event) => {
			if (event.key === 'Enter' || event.keyCode === 13) {
				searchVM();
			}
		});
	}
	//搜索虚拟机
	var searchVM = function(){

		var value = $('#searchvm').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		keywordCache = value;
		// 通过接口搜索
		initStorageTree(false, 0, '', '', value);
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

	//初始化时间策略
	var initStrategy = function(){
		pAjaxRequest({}, "/api/v1/jobs/time_crow_list", "GET", (d) => {
			var jsonData = d.data;
			if(jsonData.time_list.length != 0){
				$('#backupCrowd').taskCrowd({timeList:jsonData.time_list, showFlag: jsonData.show_flag});
			}
			var suggestInfo = jsonData.suggest_time;
			var strategy = [];
			strategy[0] = {
				mode: 7,
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				start_time: suggestInfo.start_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time,
				frequency: '',
			};
			//延迟设置,因为这里icheck会默认修改里面的选中事件
			setTimeout(function(){
				$('#recoveryTimestrategy').strategy({dom: $('#recoveryTimestrategy'), config: strategy, backup_flag: 2});
				$('.rollDiv').hide(); //隐藏滚动执行
			}, 2000);

		}, false);
	}

	//版本差异处理,主要是处理标准版本功能限制
	// 中文标准版 1
	// 中文企业版 2
	// 英文免费版 4
	// 英文基础版 9
	// 英文标准版 6
	// 英文企业版 7
	var initSoftwareVersionDiff = function(){
		pAjaxRequest({}, "/api/v1/users/auth_func", "GET", function (d) {
			let data = d.data;
			authFun = data;

			if(!data.lanfree){
				//传输模式只支持网络传输
				//删除transport_mode不可用项
				$("#transport_mode option[value='san']").remove();
				//删除xentransmode不可用项
				$("#xentransmode option[value='2']").remove();
				$("#xentransmode option[value='4']").remove();

				//删除类xen不可用项
				$("#leixentransmode option[value='2']").remove();

				//删除华为传输模式不可用项
				$("#huaweitransport_mode option[value='san']").remove();

				//华为KVM
				$("#huaweikvmtransport_mode option[value='2']").remove();
				//红帽传输模式
				$("#redhattransmode option[value='2']").remove();
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




	//初始化存储树
	var  initStorageTree =  function(loadMoreFlag = false, offset = 0, taskNodeId = '', taskUuid = '', keyword = '', externalFlag = false){
		let p = {};
		p._rows = true;
		p.show_type = 1;//按虚拟机
		p.storage_uuid = $('#storagetypeselect').val(); // 按存储筛选
		p.manage_flag = false; //得到备份数据管理,获取checkbox
		p.data_flag = false; //备份数据标志
		p.sub_module_type = sub_module_type;
		p.keyword = keyword;
		if (!externalFlag) {
			p.loadmore_offset = offset;
			p.loadmore_limit = limit;
		}
		if (loadMoreFlag) {
			p.job_uuid = taskUuid;
		}
		let div = ".src-wrap__content";
		Metronic.blockUI({target: div,animate: true});
		pAjaxRequest(p, "/api/v1/vm/restore_data", "GET", function (d) {
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
				setStorageTree(d.data.rows);
			}
		}, true);
	};
	//初始化华为CBR存储树
	var initStoragedCBRTree =  function(){
		var data  = {};
		data.type =  3;
		data.backupflag  =  true;
		data.editflag =  false;
		data.storageuuid =  $("#storagetypeselect").val();
		data =  JSON.stringify(data);
		Metronic.blockUI({target: '.two_tree',animate: true});
		$.post(CONF.AJAXPATH,{m:CONF.M.VM,f:'getCBRDetailsTreeNew',p:data},setCBRTree);
	}
	//设置存储的树
	var setStorageTree = function(zNodes){
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
			},
		};
		storagetypetree = $.fn.zTree.init($("#storagetypetree"), setting, zNodes);
		// 从备份数据跳转恢复页面，展开对象下的时间点
		let targetNode = storagetypetree.getNodeByParam('id', externalSubType + externalItemUuid + externalTaskUuid);
		// 第一次初始化，且有目标节点
		if(targetNode && !storagetypetreeInitFlag){
			// 异步获取时间点
			getSyncVcenterInfo('storagetypetree',targetNode, true, true, true)
		}
		storagetypetreeInitFlag = true;
		currentTree = storagetypetree;
	};
	//设置CBR的树
	var setCBRTree =  function(zNodes){
		Metronic.unblockUI('.two_tree');
		if(!checkTreeNodeInfo(zNodes)) return;
		var setting = {
			check:{
				enable:true,
				nocheckInherit:false //自动继承父节点 nocheck = true 的属性。
			},
			data:{
				simpleData:{
					enable:true,
					idKey:"id",
					pIdKey:"pid",
					rootPId: 0
				},
				key: {
					title: "title"
				}
			},
			view: {
				// fontCss: getFontCss,
				addDiyDom: addcbrHoverDom,
			},
			callback:{
				beforeClick: cbrnodeSelect, //根据返回值是否允许单机操作
				onCheck: cbrOnCheck, //勾选或者取消勾选的事件回调函数
				onExpand:cbrnodeExpand //捕获节点展开事件的回调函数
			}
		};
		var nodes = JSON.parse(zNodes);//拿到备份节点
		storagetypetree = $.fn.zTree.init($("#storagetypetree"), setting, nodes);
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				var checkNode = zTreeTP.getNodesByParam("id", nodes[i].id, null);
				addMPList('cbr_tree_tp',checkNode[0]);
			}
		}
		currentTree = storagetypetree;
	}
	//CBR节点选择
	var cbrnodeSelect = function(treeId, treeNode, clickFlag){
		//如果是是名字 存储区域 直接展开
		//如果是项目 异步获取存储库那一层 
		//存储库 获取虚拟机
		//虚拟机 获取时间点
		//如果是是存储库 获取虚拟机
		if('project' ==  treeNode.eventtype){
			var children = treeNode.children;
			if(!children || !clickFlag){ //没有孩子或者是没勾选
				//如果当前树是时间点 则不需要获取虚拟机
				getMPSync(treeId,treeNode,false);
			}else{
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
			}
		}else if ('mp' == treeNode.eventtype || 'vm' == treeNode.eventtype || 'tp' ==  treeNode.eventtype){
			if('mp' == treeNode.eventtype){
				// console.log("mp select",clickFlag);
				var children = treeNode.children;
				if(!children || !clickFlag){
					//如果是时间点 需要获取时间点
					//存储库和项目都可以勾选
					treeNode.nocheck = false;
					var projectnode  = treeNode.getParentNode();
					projectnode.nocheck  =  false;
					getTPSync(treeId,treeNode);
				}else{
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
				}
			}
			if('vm' == treeNode.eventtype ){
				var children = treeNode.children;
				if(!children || !clickFlag){
					//如果当前是时间点 则异步加载
					treeNode.nocheck = false; //虚拟机
					var storagenode  = treeNode.getParentNode();//存储库
					var projectnode  = storagenode.getParentNode();//项目
					storagenode.nocheck  =  false;
					projectnode.nocheck =  false
					getTPSync(treeId,treeNode);
				}else{
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
				}
			}
			if('tp' == treeNode.eventtype){
				//这里需要判断
				//  if(treeNode.checked){
				// 	var liId = treeId+ treeNode.id;
				// 	$('#' + escapeJquery(liId)).remove();
				// 	var length = $('#cbrTimeGroupList>li').size();
				// 	var timestr = "已选择时间点（"+length+"个）";
				// 	$('.addTitle > span').html(timestr);
				//  }
				var  flag  = nodeInSameVm(treeId,treeNode);
				if(flag && treeNode.getCheckStatus()){
					UIToastr.showInfo(LANG.UI_VM_TIME_POINT_SOURCE_SAME, LANG.UI_VM_TIME_POINT_SOURCE_SAME_TIPS);
				}
				$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
			}
		}else if ('mploadmore' == treeNode.eventtype){
			//获取项目node
			var projectnode =  treeNode.getParentNode();
			getMPSync(treeId,projectnode,false); //不需要获取虚拟机那一层
		}else if('tploadmore'== treeNode.eventtype){
			//获取存储库node
			var storagenode =  treeNode.getParentNode()
			getTPSync(treeId,storagenode); //需要获取时间点那一层
		}

		else{
			//前面几层只展开
			if(!treeNode.isParent) return;//不存在子节点不展开
			// nodeExpand(treeId, treeNode);
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		}
	}
	//CBR节点勾选
	var cbrOnCheck = function(e, id, node){
		var tree = $.fn.zTree.getZTreeObj(id);
		var allNodes = tree.getCheckedNodes(true);
		var flag  = false;
		var flag1 = false;
		var flagall =  false; //判断所有时间点是否有相同的虚拟机标志
		if(allNodes.length != 0){
			flag = existstorage(allNodes);
			if(flag){
				tree.checkAllNodes(false);
				tree.checkNode(node, !node.checked, true, false);
				UIToastr.showInfo(LANG.UI_VM_SELECT_STORAGE_DIFFERENT,LANG.UI_VM_SELECT_STORAGE_DIFFERENT_TIPS);
				moveRightAll(id); //清除右边所有的
			}
			if(node.eventtype == "project" || node.eventtype == "mp"){
				//需要判断该项目或者存储下的时间点是否都是同一个虚拟机
				flagall =  existsamevm(id,allNodes);
				if(flagall){
					UIToastr.showInfo(LANG.UI_VM_SELECT_TIME_SOURCE_SAME,LANG.UI_VM_TIME_POINT_SOURCE_SAME_TIPS);
					return
				}
			}
			if(node.eventtype == "tp"){
				flag1  = nodeInSameVm(id,node);
				if(flag1){
					if(!flagall){
						UIToastr.showInfo(LANG.UI_VM_TIME_POINT_SOURCE_SAME,LANG.UI_VM_TIME_POINT_SOURCE_SAME_TIPS);
					}

				}
				addMPList(id,node);
			}
		}
		else{
			$('.addVMList').empty();
			var length = $('#cbrTimeGroupList>li').size();
			var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
			$('.addTitle > span').html(timestr);
			var children = node.children;
			if(children){
				addChirdMPList(id, children);
			}
		}
	}
	//是否存在同一个存储下
	var existstorage =  function(nodes){
		var  existflag =  false;
		var  storagelist = [nodes[0].vcenteruuid];
		for (var i  = 1;i < nodes.length ; i ++) {
			if(nodes[i].eventtype == "project"){
				if(storagelist.indexOf(nodes[i].vcenteruuid) == -1){
					existflag =  true;
					break;
				}
			}
		}
		return existflag;
	}
	var nodeInSameVm =  function(id,node){
		var existflag  = false;
		var treeObj = $.fn.zTree.getZTreeObj(id);
		var allNodes = treeObj.getCheckedNodes(true);
		for (let i = 0; i < allNodes.length; i++) {
			if(allNodes[i].eventtype == "tp" && allNodes[i].parentid == node.parentid && allNodes[i].id != node.id){
				treeObj.checkNode(allNodes[i],false,true,false);
				var liId =  id + allNodes[i].id;
				$('#' + escapeJquery(liId)).remove();

				existflag =  true;
			}
		}
		return existflag;
	}
	var existsamevm =  function(id,nodes){
		var tree = $.fn.zTree.getZTreeObj(id);
		var flag =  false;
		var  residlist = [];
		nodes.forEach(node => {
			if(node.eventtype == "tp"){
				if(residlist.indexOf(node.parentid) == -1){
					//把当前节点勾选上
					tree.checkNode(node,true, true, false);
					addMPList(id,node);
					residlist.push(node.parentid);
				}else{
					flag =  true;
					tree.checkNode(node,false, true, false);
				}
			}
		});
		return flag;
	}
	var moveRightAll = function(id){
		$('#cbrTimeGroupList li').remove();
		// $('.diskList').remove();
	}
	//CBR节点展开 
	//flag为true表示点击了展开所有
	var cbrnodeExpand = function(event,treeId, treeNode,flag  =  false){
		// console.log("come in",flag,treeNode);
		if(!treeNode.eventtype){ //如果是前面几层 不存在子节点不展开
			if(!treeNode.isParent) return;
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
			if(treeNode.children && flag){
				var nodelist =  treeNode.children;
				for (let index = 0; index < nodelist.length; index++) {
					cbrnodeExpand(event,treeId,nodelist[index],true); //展开所有递归此方法
				}
			}
			// nodeExpand
		}else if(treeNode.eventtype  == "project"){
			//如果不存在子节点 则获取存储库
			var children = treeNode.children;
			if(!children){
				getMPSync(treeId,treeNode,false);
			}
			if(children && flag){
				if(treeNode.nocheck == false){
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,true,true,true,flag);
				}else{
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,false,true,true,flag);
				}
			}
		}else if(treeNode.eventtype  == "mp"){
			//如果不存在子节点 则获取存储库
			var children = treeNode.children;
			if(!children){
				treeNode.nocheck = false;
				var projectnode  = treeNode.getParentNode();
				projectnode.nocheck  =  false;
				//如果是时间点类型，则获取时间点数据 则存储库那一层加可选框 项目那一层也需要加勾选
				getTPSync(treeId,treeNode);
			}
			if(children && flag){
				// console.log("存储库有孩子---");
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,true,true,true,flag);
			}
		}
	}
	//获取即将要查询的页序号
	var getcurrentpage =  function(treeId,treeNode){
		var nextpage = 0;
		//查询的是存储库
		if(treeNode.eventtype  == "project"){
			if(treeNode.children != null){
				//因为最后一个节点是加载更多 所以需要先将长度减1  然后向下取整 （为了修改的时候使用）
				nextpage = Math.floor((treeNode.children.length-1) / mplimit) + 1;
			}
			else{
				nextpage = 1;
			}
		}
		//查询的是时间点
		if(treeNode.eventtype == "mp"){
			if(treeNode.children != null){
				nextpage = Math.floor((treeNode.children.length-1) / mplimit) + 1;
			}
			else{
				nextpage = 1;
			}
		}
		return nextpage;
	}
	//异步获取存储库
	var getMPSync =  function(treeId, treeNode,needresource){
		var div = ".two_tree";
		// var showType = parseInt($('#asyncshowtype').val());
		// var data =  JSON.stringify({id:treeNode.id,type:showType,refresh:true});
		var showType = parseInt(3); //按照时间点方式进行展示
		var region_node =  treeNode.getParentNode(); //区域node
		//var region_id =  region_node.id;
		var storage_node = region_node.getParentNode(); //存储node
		var storage_id = storage_node.id;

		var storageregionid = region_node.id.split("_");
		var region_id  = storageregionid[1];

		var storageprojectid = treeNode.id.split("_");
		var project_id  = storageprojectid[1];

		var nextpage = getcurrentpage(treeId,treeNode); //获取即将要查询的页序号
		// var nextpage =  mpcurpage + 1;
		var jsondata =  {
			"storage_uuid":storage_id,
			"region_id":region_id,
			"project_id":project_id,
			"current_page":nextpage,//即将查询的页序号
			"limit":mplimit, //每页显示的数量
			"showtype":showType,
			"need_resource":needresource
		}
		var datastring  = JSON.stringify(jsondata);
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.VM,f:"getCBRMP",p:datastring},
			success: function(d){
				Metronic.unblockUI(div);
				var data  =  JSON.parse(d);
				if(!data.re && data.re != false){
					// mpcurpage =  data.current_page;
					// mptotalpage = data.total_pages;
					$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, data.tree, true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
					//如果有加载更多 则需要删除加载更多 再判断是否需要新增加载更多节点
					//如果没有加载更多 则需要判断是否需要显示加载更多
					var treeObj = $.fn.zTree.getZTreeObj(treeId);
					var nodes = treeObj.getNodesByParam("eventtype", "mploadmore", treeNode);
					if(nodes.length > 0){
						//需要删除加载更多
						treeObj.removeNode(nodes[0]);
					}
					if( data.current_page < data.total_pages){
						var morenode = {
							"id":treeNode.id+"_"+"loadmore",
							"pid":treeNode.id,
							"name":LANG.UI_VM_LOAD_MORE,
							"clickshow":false,
							"eventtype":"mploadmore",
							"iconSkin":"loadmore",
							"isParent":false,
							"nocheck":true,
							"checked":false,
							"type":4,
							"title":LANG.UI_VM_LOAD_MORE,
						}
						$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, morenode, true);
					}
				}else if(OPREL(d)){
					return;
				}
			}
		});
	}
	//异步获取时间点
	var getTPSync =  function(treeId,treeNode){
		var div = ".two_tree";
		var showType = parseInt(3);
		var project_node =  treeNode.getParentNode();
		//var project_id = project_node.id;
		var storageprojectid = project_node.id.split("_");
		var project_id  = storageprojectid[1];
		var region_node =  project_node.getParentNode(); //区域node
		//var region_id =  region_node.id;
		var storageregionid = region_node.id.split("_");
		var region_id  = storageregionid[1];
		var storage_node = region_node.getParentNode(); //存储node
		var storage_id = storage_node.id;
		var nextpage = getcurrentpage(treeId,treeNode); //获取即将要查询的页序号
		var jsondata =  {
			"storage_uuid":storage_id,
			"region_id":region_id,
			"project_id":project_id,
			"vault_id":treeNode.id,
			"current_page":nextpage,//当前查询的页序号 获取存储库现在不做分页
			"limit":tplimit, //每页显示的数量
			"showtype":showType,
		}
		var datastring =  JSON.stringify(jsondata);
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.VM,f:"getCBRTP",p:datastring},
			success: function(d){
				Metronic.unblockUI(div);
				var data  =  JSON.parse(d);
				if(!data.re && data.re != false){
					data.tree.map((item)=>{
						if(item.eventtype == "tp"){
							//先获取存储库path
							var vaultpath =   getnodepath(treeNode,"");
							item.title = LANG.UI_VM_SOURCE_PATH + ":"+vaultpath + "/" + item.parentname;
						}
					})
					$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, data.tree, true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
					var treeObj = $.fn.zTree.getZTreeObj(treeId);
					var nodes = treeObj.getNodesByParam("eventtype", "tploadmore", treeNode);
					if(nodes.length > 0){
						//需要删除加载更多
						treeObj.removeNode(nodes[0]);
					}

					if( data.current_page < data.total_pages){
						var morenode = {
							"id":treeNode.id+"_"+"loadmore",
							"pid":treeNode.id,
							"name":LANG.UI_VM_LOAD_MORE,
							"clickshow":false,
							"eventtype":"tploadmore",
							"iconSkin":"loadmore",
							"isParent":false,
							"nocheck":true,
							"checked":false,
							"type":6,
							"title":LANG.UI_VM_LOAD_MORE,
						}
						$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, morenode, true);
					}
				}else if(OPREL(d)){
					return;
				}
			}
		});
	}
	//添加cbr时间点到右边列表
	var addChirdMPList =  function(id,children){
		if(!children && children.length == 0) return;
		for (var i = 0; i < children.length; i++) {
			if(children[i].eventtype == "tp"){
				addMPList(id,children[i]);
			}else{
				var childList =  children[i].children;
				if(!childList)  continue;
				addChirdMPList(id,childList);
			}
		}
		return;
	}
	//勾选添加存储库显示列表
	var addMPList = function(id,node){
		var info = "";
		var liId = id+node.id;
		//需要获取node的路径
		node.path = getnodepath(node,"");
		if(node.checked){
			//同一个存储库下的时间点只能选择一个
			//根据CBR同步类型添加每一列到列表				
			//如果是选择时间点 需要展示两行
			info +=
				'<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ decodeURIComponent(node.path) +'">' +
				'<div class="col1">' +
				'<div class="cont vmDetail">' +
				'<div class="cont-col1">' +
				'<div style="width:20px;height:20px;background: url(./img/vm/vm.png) 0 no-repeat;"></div>' +
				'</div>' +
				'<div class="cont-col2">' +
				'<div class="desc list-one" style="font-size: 14px;color: #333;padding: 9px 4px 0px 4px">' + node.parentname + '</div>' +
				'<div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px">' +  node.name + '</div>' +
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
			$('#cbrTimeGroupList').append(info);
			var length = $('#cbrTimeGroupList>li').size();
			var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
			$('.addTitle > span').html(timestr);
			$('#' + escapeJquery(liId)).popover();	   //初始化tips
			//移除存储池显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				var treeObj = $.fn.zTree.getZTreeObj(id);
				$('.popover.in').remove();
				treeObj.checkNode(node,false,true);
				$('#' + escapeJquery(liId)).remove();
				var length = $('#cbrTimeGroupList>li').size();
				var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
				$('.addTitle > span').html(timestr);
			});
		}else{
			//检查node.id是否在虚拟化里面，如果在就要把对应的li移除
			$('#' + escapeJquery(liId)).remove();
			var length = $('#cbrTimeGroupList>li').size();
			var timestr = LANG.UI_VM_SELECTED_TIME_POINT + "（"+length+ LANG.UI_VM_SELECTED_TIME_POINT_UNIT + "）";
			$('.addTitle > span').html(timestr);
		}
	}



	//添加云上数据鼠标指上去事件
	var addcbrHoverDom  = function(treeId, treeNode){
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		if(treeNode.pid == 1){
			var aObj = $("#" + nodeTID + "_a"); //获取节点DOM
			var expandedstr  =
				'<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>'+
				'<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
				'<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
			aObj.after(expandedstr);
			var hrefRefresh = $("#diyHref_" + nodeTID + nodeID + "_1");
			var hrefExpand = $("#diyHref_" + nodeTID + nodeID + "_2");
			var hrefCollapse = $("#diyHref_" + nodeTID + nodeID + "_3");
			//点击刷新
			if (hrefRefresh) hrefRefresh.bind("click", function(){
				getSyncCBRInfo(treeId, treeNode, true, false);
			});
			//点击全部展开
			if (hrefExpand) hrefExpand.bind("click", function(){
				cbrnodeExpand(event,treeId,treeNode,true);
			});
			//点击收起
			if (hrefCollapse) hrefCollapse.bind("click", function(event){
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true);
			});
		}
	}
	//异步获取CBR的信息  refreshFlag是否重新刷新
	var getSyncCBRInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var div = ".two_tree";
		var data = {
			"storage_uuid_list":[{
				"storage_uuid":treeNode.id
			}],
			"type":parseInt($('#asyncshowtype').val()),
			"backupflag":true,
			"editflag":false
		}
		//var showType = parseInt($('#asyncshowtype').val());
		var datastr =  JSON.stringify(data);
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type:"post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.VM,f:'getSyncCBR',p:datastr},
			success:function(d){
				Metronic.unblockUI(div);
				var data  =  JSON.parse(d);
				if(!data.re && data.re != false){
					// result = JSON.parse(data);
					//刷新CBRlist
					refreshCBRList(treeId);
					//检测是否为同一事件返回
					if(currentTree != $.fn.zTree.getZTreeObj(treeId)) return;
					if(data){
						//success
						var allNodes = currentTree.getCheckedNodes(true);
						if(refreshFlag && allNodes!= 0){
							$('.popover.in').remove();
							$('.cbrTimeGroupList').empty();
							currentTree.checkAllNodes(false);
						}
						var index = treeNode.getIndex();
						var storagenode =  treeNode.getParentNode();
						currentTree.removeNode(treeNode);
						currentTree.addNodes(storagenode,index,data,true);
						currentTree.expandNode(treeNode,true);
						if(expendFlag == true){
							currentTree.expandNode(treeNode, true, true, true);
						}
					}
				}
				else{
					OPREL(d);
				}
			}
		})

	}
	//刷新清空右边列表
	var refreshCBRList  = function(){
		var allNodes =  currentTree.getCheckedNodes();
		if (allNodes.length ==  0) return;
		$('#cbrTimeGroupList li').remove();
	}






	//恢复目的分组展开
	var vcenterRefresh = function(treeId, treeNode){
		if(1 == treeNode.type){
			let p = {
				platform_uuid: treeNode.id,
				hypervisor_type: treeNode.hypervisor,
			}
			Metronic.blockUI({target: '#host_tree',animate: true});
			pAjaxRequest(p, "/api/v1/vm/platforms/groups", "GET", function (d) {
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




	//初始化华为CBR虚拟机配置信息
	//这里的node是宿主机信息
	var  initHuaWeiCBRconfig =  function(){
		var p = {};
		var nodelist  = [];
		for (var i = 0; i < cbrselectnode.length; i++) {
			if(cbrselectnode[i].eventtype == "tp"){
				var vaultnode =  cbrselectnode[i].getParentNode();
				var projectnode = vaultnode.getParentNode();
				var regionnode =   projectnode.getParentNode()
				var storageregionid = regionnode.id.split("_");
				var region_id  = storageregionid[1];
				var storageprojectid = projectnode.id.split("_");
				var project_id  = storageprojectid[1];
				var nodeparam ={
					"region_id":region_id,
					"project_id":project_id,
					"resource_id":cbrselectnode[i].parentid,
					"backup_id":cbrselectnode[i].id,
				}
				nodelist.push(nodeparam);
			}
		}
		// var data =  JSON.stringify(nodelist);
		var cbrinfo = {
			"huawei_cbr_uuid":$('#storagetypeselect option:selected').val(),
			"region_id":nodelist[0].region_id,
			"project_id":nodelist[0].project_id,
			"resource_id":nodelist[0].resource_id,
			"backup_id":nodelist[0].backup_id,
		}
		p.cbrinfo  = cbrinfo;
		var datastr  = JSON.stringify(p);
		// $.ajaxSettings.async = false;
		//锁整个tabcontent界面  请求到数据之后 到第二步解开 
		Metronic.blockUI({target: '#vmrecovercontent',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getCBRVMConfig',p:datastr}, function(d){
			Metronic.unblockUI('#vmrecovercontent');
			OPREL(d);
			var data = JSON.parse(d);
			if(data.re){
				//返回成功
				newtimepoint =  data.ext
			}
		});
	}

	//初始化虚拟机配置V2 
	var initCBRVMconfigV2  = function(node){
		//获取node的时间点
		var backuupid = "";
		var vmuuid  =""
		for (var i = 0; i < cbrselectnode.length; i++) {
			if(cbrselectnode[i].eventtype == "tp"){
				//只选择一个时间点即可
				// backuupid  = cbrselectnode[i].id;
				backuupid = newtimepoint;
				vmuuid  =  cbrselectnode[i].parentid;
				break;
			}
		}
		//设置时间点详情
		var pointsDetail = [{
			"vmname":vmOldName[0][0] + "_" + clearStringEmpty(vmOldName[0][1]),
			"oldname":vmOldName[0][0],
			"config":{},
			"timepointuuid":backuupid,
			"hypervisor":CONF.VM_TYPE.HUAWEICBR
		}];
		var data = {
			"backup_id":backuupid,
			"hypervisor":node.hypervisor,
			"vcenteruuid":node.vcuuid,
			"hostuuid":node.id,
			"pointsDetail":pointsDetail,
			"vm_uuid":vmuuid
		}
		var datastr =  JSON.stringify(data);
		$.ajaxSettings.async = false;
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getCBRVMConfigInfoV2',p:datastr}, function(d){
			$.ajaxSettings.async = true;
			//获取配置信息
			//var data =  JSON.parse(d);
			// //设置新的虚拟机名称
			// data.new_vm_name  = vmOldName[i][0] + "_" + clearStringEmpty(vmOldName[i][1]);
			// var configlist = [];
			// configlist[0] = data;
			// var info  ={
			// 	config:configlist
			// }
			// $('#accordionvm').vmRecoveryConfig(info);
			var data = JSON.parse(d);
			if(!data.flag){
				//如果获取失败
				return UIToastr.showWarning(LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO, LANG.UI_VM_GETINFO_FAIL_TRY_AGIN);
			}
			vmPlugnInfoData = data;
			vmNameLimit = data.vmNameLimit;
			$('#accordionvm').vmRecoveryConfig(data);
			if(_hypervisor != node.hypervisor){
				$.fn.vmRecoveryResetOptions();	//切换虚拟化类型重置高级配置默认选项
			}
			_hypervisor = node.hypervisor;
			if (CONF.VM_TYPE.INCLOUDKVM == _hypervisor || CONF.VM_TYPE.INSPURVVDK == _hypervisor || CONF.VM_TYPE.KSPHERE == _hypervisor) {
				_VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\]<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+]");
				_VMNAMETIPS = LANG.UI_TOOLS_VMNAME_ICS_TIPS;
			}
			initNetworkAndStore(node);
		});


	}

	//获取华为CBR节点展示（展示主节点）
	var  initCBRnodeconfig =  function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getCBRNode',p:{}}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('#nodeconfig');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
				//默认设置节点不能选择  华为CBR可以选择节点
				option.attr("disabled",false);
			}
		});
		$('#nodeconfigs').show();
	}




	var strategyHandler = function(){
		editFlag = false;
		initStrategyData();
	}
	var verifyHandler  = function(){
		//如果勾选
		if(this.checked){
			$(".verifyalarmDiv").show();
		}else{
			$(".verifyalarmDiv").hide();
		}
		initVerifyStrategyDes();
	}

	var storageHighLoadHandler = function () {
		initHighStrategyDes();
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
				roll_end_time: '23:59:59',
				frequency: '',
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
				roll_end_time: timeInfo.recInfo.endTime,
				frequency: '',
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
		$('#recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});
	}
	//加载策略对应描述
	var initStrategyDes = function(){
		initTimeStrategyDes();
		// initSpeedStrategyDes();
		initHighStrategyDes();
		initVerifyStrategyDes();//初始化校验策略描述信息
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
			des += LANG.UI_JOB_TIMING_RECOVER + ': ' + $('#oncetime').val();
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
	//初始化限速策略描述信息
	var initSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if(speedList.length !=0){
			des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
		}
		for(var i=0;i<speedList.length;i++){
			titleDes += speedList[i].des + '. ';
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].speedlimit.des;
			initStrategyDesStyle($('.speedlimitDes'), des, oldDes);
		}else{
			$('.speedlimitDes').removeClass('font-green-seagreen');
		}
		$('.speedlimitDes').html(des);
		$('.speedlimitDes').prop('title', titleDes);

	}
	//初始化高级策略描述信息
	var initHighStrategyDes = function(){
		var des = "";
		if (CONF.VM_TYPE.INSPURCLOUDPLATFORM == _hypervisor) {
			des += ' ' + LANG.UI_GLOBAL_STRATEGY_STORAGE_HL_STOP_TASK + ": " + getSwitchDes($('#storageHighLoad').get(0).checked);
		}
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
	//初始化校验策略描述信息
	var initVerifyStrategyDes = function(){
		var des = "";
		var verifystr = $('.verifyLable').html();
		des +=verifystr+": "+getSwitchDes($('#verifyflag').get(0).checked);
		if($('#verifyflag').get(0).checked){
			des += ","+ $('.verifyalarmLable').html()+": "+getSwitchDes($('#verifyalarmflag').get(0).checked);
		}
		$('.verifyDes').html(des);
		$('.verifyDes').prop('title', des);
	}
	//初始化数据改变监听事件
	var initDataChangeListeners = function(){
		initSpeedListeners();
		initHighListeners();
		initVerifyListeners(); //初始化校验策略模块的监听

	}
	//初始化限速策略监听
	var initSpeedListeners = function(){
		//添加限速策略确定
		$('#speed_submit').on('click', speedSubmit);
	}
	//初始化高级策略监听
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
	//初始化校验策略模块的监听
	var initVerifyListeners =  function(){
		$('#verifyflag').on('switchChange.bootstrapSwitch',function(){
			initVerifyStrategyDes();
		})
		$('#verifyalarmflag').on("switchChange.bootstrapSwitch",function(){
			initVerifyStrategyDes();
		})
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
			des +=
				'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ liId +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ info.des + '">' +
				'<div class="col1">' +
				'<div class="cont">' +
				'<div class="cont-col1"></div>' +
				'<div class="cont-col2">' +
				'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'<div class="col2  pull-right delete-list">' +
				'<a class="del'+ liId +'" >' +
				'<div class="label label-sm label-danger" style="padding:0;">' +
				'<i class="viconfont vicon-cuowu"></i>' +
				'</div>' +
				'</a>' +
				'</div>' +
				'</li>';
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
			des +=
				'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ liId+'"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' +info.des + '">' +
				'<div class="col1">' +
				'<div class="cont">' +
				'<div class="cont-col1"></div>' +
				'<div class="cont-col2">' +
				'<div class="desc list-one"> '+ info.des +  '</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'<div class="col2  pull-right delete-list">' +
				'<a class="del'+ liId +'" >' +
				'<div class="label label-sm label-danger" style="padding:0;">' +
				'<i class="viconfont vicon-cuowu"></i>' +
				'</div>' +
				'</a>' +
				'</div>' +
				'</li>';
		}
		//检测结束时间是否大于开始时间
		if(!checkTime(info.startTime,info.endTime)) return;
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
			// initSpeedStrategyDes();
		});
		speedList.push(info);
		$('#speedlimitModal').modal('hide');
		// initSpeedStrategyDes();
	}

	var checkTime = function (start,end) {
		var startnum = new Date("1970-01-01" + " " + start).getTime();
		var endnum = new Date("1970-01-01" + " " + end).getTime();
		if(endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
			UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
			return false;
		}else {
			return true;
		}
	}

	//初始化策略选择
	var initStrategySelect = function(){
		if(initStrategyFlag) return; //加载一次
		function initStrategyList(res){
			if(!res.success) return;
			if(res.data.length > 0) {
				// 清空策略列表，再插入新的策略列表
				var data = res.data;
				var strategyselect = $('#strategySelect');
				strategyselect.empty();
				for (var i = 0; i < data.length; i++) {
					var option = '<option  value="' + data[i].uuid + '">' + data[i].text + '</option>';
					globalStrategy[data[i].uuid] = data[i].strategy;
					strategyselect.append(option);
				}
				if (!initStrategyFlag) {
					//初始化前先清除一遍
					$('.searchable-select').remove();
					$('#strategySelect').searchableSelect();
					$('.searchable-select-item').on('click', strategyHandler);
					$(document).keyup(function (event) {
						if (event.keyCode == 13) {
							strategyHandler();
						}
					});
					initStrategyFlag = true;
				}
			}
		};
		pAjaxRequest({type: CONF.MODULE_TYPE.VM}, '/api/v1/strategies/select', "GET", initStrategyList, true);
	}
	//获取当前节点的存储节点
	var getstoragenode =   function(node){
		var vaultnode =  node.getParentNode();
		var projectnode  =  vaultnode.getParentNode();
		var areanode =  projectnode.getParentNode();
		var storagenode = areanode.getParentNode();
		return storagenode;
	}
	//获取节点路径 从存储开始
	var getnodepath =  function(node,nodepath){;
		var nodename =  node.name;
		var parentNode =  node.getParentNode();
		if(node.pid !=  1){
			nodepath = "/" + nodename + nodepath;
			//如果是时间点 需要加上虚拟机这一层
			if(node.eventtype == "tp"){
				nodepath  = "/" + node.parentname + nodepath;
			}
			return getnodepath(parentNode,nodepath);
		}else{
			nodepath = nodename + nodepath;
			return nodepath;
		}
	}

	const getAvailableNodeByTimepoints = timepoints => {
		let nodeUuid = '';
		let timepointUuids = timepoints.map(item => {return item.timepointuuid});
		pAjaxRequest({timepoints_uuid: timepointUuids}, "api/v1/nodes/timepoints/node", "GET", function (d) {
			nodeUuid = d.data.node_uuid;
		}, false);
		return nodeUuid;
	}

	//初始化备份系统节点IP
	var initBackupServerAddr = function(nodeuuid){
		var data = {};
		data.node_uuid = $('#nodeselect').val();
		//未获取到节点信息，直接获取时间点所在的节点
		if(data.node_uuid == "" || data.node_uuid == undefined){
			data.node_uuid = nodeuuid;
		}
		pAjaxRequest(data, "/api/v1/nodes/all_ip", "GET", function (d) {
			var data = d.data.rows;
			var serveripaddr = $('#systemIp');
			serveripaddr.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i]).val(data[i]);
				serveripaddr.append(option);
			}
		}, false);
		$('#inputIp').val(window.location.host);
	}

	var handleValidation = function() {
		var recoverForm = $('#submit_form');

		recoverForm.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "#searchvm, #inputIp, #jobname",  // validate all fields including form hidden input
			rules: {
				vmname: {
					required: true,
					newvmname: true,
				},
				diskname: {
					required: true,
					newdiskname: true
				},
				reset_hostname: {
					required: true,
					newhostname: true
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

		$.validator.addMethod("newhostname", function(value, element) {
			if ('' == $.trim(value)) {
				return false;
			}
			return true;
		});
	};

	var initSafeStrategy = function () {
		// 获取操作系统，单个vm或多个vm能统一时传入os_type
		let osTypeArr = []; // 记录有几种os_type
		$.each(data.recoverInfo.vmconfigs, function (i, v) {
			if ('Linux' === v.os_type) {
				if ($.inArray('Linux', osTypeArr) === -1) {
					osTypeArr.push('Linux');
				}
			} else if ('Windows' === v.os_type) {
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

		// 是否有恢复到无网络环境选项
		let notNetFlag = false;
		if (CONF.VM_TYPE.SANGFOR == _hypervisor || CONF.VM_TYPE.SANGFORVVDK == _hypervisor) {
			// true隐藏，false不隐藏
			notNetFlag = true;
		}
		// 根据所有时间点的状态初始化安全策略
		$('#virusConfig').virusDetectionCover(isNoScan, isHeathy, isInfect, notNetFlag, {
			no_scan: {
				os_type: os_type
			},
			healthy: {
				os_type: os_type,
			},
			reflected: {
				virus_scan_status: isInfectButUnfinished ? $.fn.virusDefine.virus_scan_status.infected_but_unfinished : $.fn.virusDefine.virus_scan_status.infected, // 已感染但未完成扫描时状态改为4
				os_type: os_type,
			}
		});
		// 时间点若有一个启用了完整性校验则恢复任务也启用
		let backup_disable_flag = true;
		$.each(data.pointInfo.points, function (i, d) {
			if (d.integrity_check_flag) {
				backup_disable_flag = false;
				return true;
			}
		});
		$('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.VM, 0, backup_disable_flag, notNetFlag);
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

	return {
		//main function to initiate the module
		init: function () {
			handleValidation();
			initSoftwareVersionDiff();
			wizardInit();
			initStorageShowType(); //初始化存储类型展示方式和事件
			// initPointShowType(); //暂时使用，目前方便前端拿到数据  以后需要去掉
			if (externalItemUuid) {
				// 备份数据页面跳转的
				initStorageTree(false, 0, '', '', '', true);
			} else {
				initStorageTree();//初始化树
			}
			initListener(); //初始化监听
			initSpinner();
			inintDatatimePicker();
			initStrategySelect(); //初始化策略选择
			initDataChangeListeners();
			initStrategy();
			initApplianceSelect();

		},
	};
}();

jQuery(document).ready(function() {
	Metronic.blockUI({target: '.src-wrap__content', animate: true});
	setTimeout(() => {
		VMRecover.init();
	}, 200);
});