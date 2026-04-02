var MachineOsBackup = function () {
	var resultData = {};
	var zTreeAgent; //初始化备份源树形实例
	var selectedAgent = []; //第一步已经勾选的agent集合
	var defaultConfig = {
        mode: [1, 2, 3, 9],
		deduplication: true,
    };
	var EditFlag = false; //是否是修改
	var _OLDINFO = {};
	var AUTH_SUCCESS = false; //默认授权不通过
	var AUTH_SUCCESS_NUM = 0;
	var worm_show_flag = false;

	//是否有传输网络
	var has_network = true; //默认是有的
	var has_network_init = true; //是否初始化传输网络

	var mergemodedivFlag = false; //是否显示合并数据冗余


	//删除数组CONF.FUNCTIONS中的一个元素virusKill
	

	




	var getOldInfo = function(){
		//需要判断是否是修改,如果是修改则需要初始化数据
		//获取uuid是否有值,如果有值则表示是修改
		var uuid = $("#uuid").val();
		let oldInfo = {};
		if(uuid != "" && uuid != undefined && uuid != null){
			EditFlag = true;
			//修改备份任务
			var requestData  = function(data){
				if(data.success){
					oldInfo = data.data;
					_OLDINFO = data.data;
					//只有修改任务有task_uuid
					resultData.task_uuid = oldInfo.taskuuid;
					//新建备份任务
					initInfo(oldInfo);
				}else{
					UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_BACKUP_INFO, data.message);
				}
			}
			//初始化备份源
			pAjaxRequest({job_uuid:uuid}, '/api/v1/complete_machine_os/get_job_info', "GET", requestData, true);

		}else{
			//新建备份任务
			initInfo(oldInfo);
		}
	}



	//初始化数据
	var initInfo = function(oldInfo){
		//初始化第一步
		initFirst(oldInfo);
		//初始化第二步
		initSecond(oldInfo);
		//初始化第三步
		initThird(oldInfo);
		//初始化第四步
		initFourth(oldInfo);

	}







	//--------------------------------------初始化第一步内容----------------------------------------------------------------------
	var initFirst = function(oldInfo){

		var initBackupDisk = function(thisNode){
			//获取勾选状态
			var checked = thisNode.checked;
			if(checked){
				//如果勾选了则初始化备份源磁盘
				var initDatas = [{
					agent_uuid: thisNode.uuid, // 代理uuid(必传)
					agent_name: thisNode.name, //代理名称
					check_flag: true, // 勾选状态,勾选还是取消勾选
					relation_flag: true, // 关联选择
					show_collapse: true, //是否展开
					show_title: true, // 是否显示标题
					blockUI: '#submit_form',
				}]
				$("#checkedSourceDiv").initBackupSource(initDatas);
			}else{
				//如果勾选了则初始化备份源磁盘
				var initDatas = [{
					agent_uuid: thisNode.uuid, // 代理uuid(必传)
					agent_name: thisNode.name, //代理名称
					check_flag: false, // 勾选状态,勾选还是取消勾选
					blockUI: '#submit_form',
				}]
				$("#checkedSourceDiv").initBackupSource(initDatas);
			}
			

		}
		//初始化选择源主机树
		var initBackupSourceZtree = function(zNodes){
			var agentnodeSelect = function(treeId, treeNode){
				if(treeNode.type == 1){
					if(treeNode.agentuuidInTask){
						//获取任务中数据
						var task_agent_uuid_exist_list = treeNode.agentuuidInTaskList;
						var task_name = task_agent_uuid_exist_list.task_name;
						var ip = treeNode.ip;
						UIToastr.showWarning(LANG.UI_MACHINE_OS_SELECT_SOURCE_HOST, ip+"("+task_name+")"+LANG.UI_MACHINE_OS_IP_EXICST);
						return false;
					} 
					if(!treeNode.auth_flag){
						UIToastr.showWarning(LANG.UI_MACHINE_OS_SELECT_SOURCE_HOST, LANG.UI_MACHINE_OS_HOST_NOT_AUTH_TIPS);
						return false;
					}
					if(!treeNode.online_flag){
						UIToastr.showWarning(LANG.UI_MACHINE_OS_SELECT_SOURCE_HOST, LANG.UI_MACHINE_OS_HOST_OFF_LINE_TIPS);
						return false;
					}
				}
				
				
			}
			//单击展开
			var zTreeOnClick = function(event, treeId, treeNode) {
				var treeObj = $.fn.zTree.getZTreeObj(treeId);
				if (treeNode.open) {
					treeObj.expandNode(treeNode, false, false, true,true); // 收拢节点
					
				} else {
					treeObj.expandNode(treeNode, true, false, true,true); // 展开节点
				}
				//判断当前节点是否被勾选
				if(treeNode.type != 0){
					if(treeNode.checked){
						//勾选当前节点
						treeObj.checkNode(treeNode, false, true, true); 
					}else{
						//勾选当前节点
						treeObj.checkNode(treeNode, true, true, true); 
					}
				}
			};
			var OsOnCheck = function(event, treeId, treeNode){
				//获取脚本配置的select对象
				let script_select = $("#script_host");
				$(".scriptBox").empty();
				script_select.empty();
				//获取已经勾选的所有值
				var treeObj = $.fn.zTree.getZTreeObj(treeId);
				var checkedNodes = treeObj.getCheckedNodes(true);
				//获取勾选的数量
				var checkedNum = 0;
				for(var i=0;i<checkedNodes.length;i++){
					if(checkedNodes[i].type == 0){
						continue;
					}
					checkedNum ++;
				}
				if(!AUTH_SUCCESS){
					UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
					//阻止勾选当前节点
					if(treeNode.type == 0){
						treeObj.checkAllNodes(false);
					}else{
						treeObj.checkNode(treeNode, false, false);
					}
					return;
				}
				if(AUTH_SUCCESS_NUM >= 0 && checkedNum > AUTH_SUCCESS_NUM){
					UIToastr.showWarning(LANG.UI_MACHINE_OS_LICENSE_CHECK, LANG.UI_MACHINE_OS_LICENSE_SHORTAGE);
					//阻止勾选当前节点
					if(treeNode.type == 0){
						treeObj.checkAllNodes(false);
					}else{
						treeObj.checkNode(treeNode, false, false);
					}
					return;
				}
				if(treeNode.type == 0){
					//如果是分组层级,则获取所有子级并循环
					var nodes = treeNode.children;
					for(var i=0; i<nodes.length; i++){
						initBackupDisk(nodes[i]);
					}
				}else{
					initBackupDisk(treeNode);
				}
				
			}
			//如果返回的值为空,则增加提示
			if(zNodes.length === 0){
				$('#noagent').show();
				$('#step1tips').show();
				$('.VMList-div').hide();
				$(".vm_tree_div").hide();
				return;
			}else{
				$('#noagent').hide();
				$('#step1tips').hide();
				$('.VMList-div').show();
				$(".vm_tree_div").show();
			}
			var setting = {
					view: {
						nameIsHTML: true
					},
					check: {
						enable: true,
						nocheckInherit: false//true 表示 新加入子节点时，自动继承父节点 nocheck = true 的属性。false 表示 新加入子节点时，不继承父节点 nocheck 的属性。
					},
					data: {
						simpleData: {
							enable: true
						},
						key:{
							title: "title"
						}
					},
					callback: {
						beforeClick: agentnodeSelect, //点击事件
						onCheck: OsOnCheck, //勾选事件
						onClick: zTreeOnClick
					}
				};
			zTreeAgent = $.fn.zTree.init($("#machine_os_tree"), setting, zNodes);
			//获取zNodes一共有几个分组
			var big_group_num = 0;
			for(var i=0; i<zNodes.length; i++){
				if(zNodes[i].type == 0){
					big_group_num ++;
				}
			}
			if(big_group_num == 1){
				//则展开所有
				zTreeAgent.expandAll(true);
			}
			//初始化完成后如果是修改还要执行修改的程序
			if(EditFlag){
				var nodes_list = zTreeAgent.transformToArray(zTreeAgent.getNodes());
				for(var j=0; j<oldInfo.backup_oss_info.length; j++){
					//获取历史agent_uuid
					var old_agent_uuid = oldInfo.backup_oss_info[j].agent_uuid;
					//获取排除磁盘列表
					var exclude_uuid_list = [];
					for (let index = 0; index < oldInfo.backup_oss_info[j].exclude_devices_list.length; index++) {
						exclude_uuid_list.push(oldInfo.backup_oss_info[j].exclude_devices_list[index].dev_node_uuid)
					}
					//查找data.data中的uuid
					for(var i=0; i<nodes_list.length; i++){
						var agent_uuid = nodes_list[i].uuid;
						if(old_agent_uuid == agent_uuid){
							//先展开所有
							zTreeAgent.expandAll(true);
							//先解禁
							zTreeAgent.setChkDisabled(nodes_list[i], false);
							//勾选主机
							zTreeAgent.checkNode(nodes_list[i], true, false);
							var checkOldResource = function(nodeInfo){
								//如果勾选了则初始化备份源磁盘
								let initDatas = [{
									agent_uuid: nodes_list[i].uuid, // 代理uuid(必传)
									agent_name: nodes_list[i].name, //代理名称
									check_flag: true, // 勾选状态,勾选还是取消勾选
									relation_flag: oldInfo.backup_oss_info[j].disk_struct_backup_mode, // 关联选择
									show_collapse: true, //是否展开
									show_title: true, // 是否显示标题
									exclude_uuid_list: exclude_uuid_list, // 排除的uuid列表
									blockUI: '#submit_form',
								}]
								$("#checkedSourceDiv").initBackupSource(initDatas);
							}
							//触发勾选事件
							if(nodes_list[i].type == 0){
								//如果是分组层级,则获取所有子级并循环
								var nodes = nodes_list[i].children;
								for(var x=0; x<nodes.length; x++){
									checkOldResource(nodes[x]);
								}
							}else{
								checkOldResource(nodes_list[i]);
							}
						}
					}
				}
			}
		


			var searchAgent = function() {
				var value = $('#searchAgent').val().toLowerCase(); // 获取输入值并转换为小写
				// 输入验证
				if(!customInputValidate('string',value)){
					return false;
				}
				var nodes = zTreeAgent.getNodes();
				if (!nodes || nodes.length == 0) return;
			
				// 隐藏所有节点
				zTreeAgent.hideNodes(nodes);
			
				// 找到匹配的节点集合
				let nodeParamList = zTreeAgent.getNodesByParamFuzzy('name', value);
				//如果没搜索到则给出提示
				if(nodeParamList.length == 0){
					$('#nosearchtips').show();
					$('.vcenter-tree').hide();
					return;
				}else{
					$('#nosearchtips').hide();
					$('.vcenter-tree').show();
				}
				// 找到父节点并添加到展示中，但不添加父节点的其他子节点
				var findParent = function(treeObj, node) {
					var pNode = node.getParentNode();
					if (pNode != null) {
						nodeParamList.push(pNode);
						// 确保父节点的其他子节点不被显示
						var siblings = pNode.children;
						for (var i = 0; i < siblings.length; i++) {
							if (siblings[i] !== node) {
								zTreeAgent.hideNode(siblings[i]);
							}
						}
					}
				};
			
				for (var n in nodeParamList) {
					findParent(zTreeAgent, nodeParamList[n]);
				}
			
				// 排序
				nodeParamList = $.unique(nodeParamList.sort());
				// 展示找到的节点
				zTreeAgent.showNodes(nodeParamList);
			
				// 展开节点
				for (var n in nodeParamList) {
					zTreeAgent.expandNode(nodeParamList[n], true, false, true);
				}
			};
			//搜索
			$('#searchAgent').on('input propertychange',function(){
				searchAgent();
			})
			

		}

		var requestData  = function(data){
			if(data.success){
				initBackupSourceZtree(data.data);
			}else{
				UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_SOURCE_HOST, data.message);
			}
		}
		

		//获取授权
		var getAuth = function(){
			var requestList = {};
			requestList.type = 'a';
			requestList.module = 'os';  //整机定时和卷都使用操作系统模块的授权
			if(EditFlag){
				requestList.task_uuid = _OLDINFO.taskuuid;
				requestList.uuids = [];
				for(var i=0;i<_OLDINFO.backup_oss_info.length;i++){
					requestList.uuids.push(_OLDINFO.backup_oss_info[i].agent_uuid);
				}
			}

			pAjaxRequest(requestList, '/api/v1/system/auth/base_info', "GET", function(data){
				if(data.success){
					//获取授权后判断授权
					var auth = data.data;
					//获取授权方式	
					if(auth.auth_type == 1){
						//如果数量授权
						//得到剩余数量
						var remaining_quantity = parseInt(auth.total) - parseInt(auth.used);
						if(EditFlag){
							//如果是修改则增加一个授权
							remaining_quantity = parseInt(remaining_quantity) + requestList.uuids.length;
						}
						//判断授权,如果授权数量大于0则表示可以进行备份
						if(remaining_quantity > 0){
							AUTH_SUCCESS_NUM = remaining_quantity;
							AUTH_SUCCESS = true;
						}
					}else{
						//容量授权则不判断
						AUTH_SUCCESS_NUM = -1
						AUTH_SUCCESS = true;
						
					}
					//授权是否到期
					if(!auth.license_flag){
						AUTH_SUCCESS = false;
					}
					//初始化备份源
					pAjaxRequest({}, '/api/v1/complete_machine_os/backup_tree', "GET", requestData, true);
				}else{
					UIToastr.showWarning(LANG.UI_MACHINE_OS_LICENSE_CHECK, LANG.UI_MACHINE_OS_LICENSE_GET_FAILED);
				}
			})
		}
		getAuth();
		$('#toAdd').on('click',function(){
	    	LOCATION('./content/client/client.php', 'infrastructure');
		});


	};

//--------------------------------------初始化第二步内容----------------------------------------------------------------------

var initSecond = function(oldInfo){
	if(EditFlag){
		$('#backupTarget').backupTarget({
			node_uuid: oldInfo.node.node_uuid,
			node_pool_uuid: oldInfo.node.node_pool_uuid,
			storage_uuid: oldInfo.node.storage_uuid,
			storage_pool_uuid: oldInfo.node.storage_pool_uuid,
			storage_pool_type: oldInfo.node.storage_pool_type,
		});
	}else{
		$('#backupTarget').backupTarget();
	}
	
}


//--------------------------------------初始化第三步内容----------------------------------------------------------------------
//初始化通用策略
var initCommonPolicy = function(oldInfo){
	let data = {};
	let strategy = {};
	//获取存储类型
	var storageType = resultData.node_info.storage_type;
		defaultConfig.storage_type = storageType;
		defaultConfig.module_type_des = 'os';

	if(EditFlag){
		data = oldInfo.time_strategy.data;
		// 组合策略信息
		strategy = { store: {}, reserve: {}, time: oldInfo.time_strategy, speedlimit: oldInfo.speed_strategy };
		strategy.store.storeInfo = oldInfo.storage_strategy;
		strategy.store.storeInfo.compress_flag = strategy.store.storeInfo.compress;
		strategy.reserve.reserveInfo = {value: oldInfo.reserve_strategy.number, type: oldInfo.reserve_strategy.type, strategy_mode: oldInfo.reserve_strategy.strategy_mode};
		defaultConfig.strategy = strategy;
	}
	$('.strategy-group-select-wrapper').strategyGroupSelector({module_type: CONF.MODULE_TYPE.OS, defaultConfig: defaultConfig, backupStrategyDom: '#backupStrategyDiv'})
	$('#backupStrategyDiv').initBackupStrategy(defaultConfig);

}
var initThird = function(oldInfo){
	let data = {};
	let strategy = {};
	

	//初始化传输策略
	var initTransferPolicy = function(){
		$('#Socket-thread').spinner({value: 3, step: 1, min: 1,max: 8});//传输线程
		//判断传输线程权限
		
		if(CONF.FUNCTIONS.includes('multithread')){
			//如果授权线程
			$(".transfer_threads_div").show();
		}else{
			//默认值为1
			$('#Socket-thread').spinner({value: 1, step: 1, min: 1,max: 8});//传输线程
			$(".transfer_threads_div").hide();
		}

	
		

		//初始化一些切换事件
		$('#encrypt').on('switchChange.bootstrapSwitch', function(event, state){
			if(state){
				$(".encrypt_method_div").show();
			}else{
				$(".encrypt_method_div").hide();
			}
		});
		$('#SourceEnd_compression').on('switchChange.bootstrapSwitch', function(event, state){
			if(state){
				$(".compression_level_div").show();
			}else{
				$(".compression_level_div").hide();
			}
		});





		if(EditFlag){
			//初始化加密传输
			$("#encrypt").bootstrapSwitch('state', oldInfo.transfer_strategy.encrypt);
			//初始化加密算法
			$("#encrypt_method").val( oldInfo.transfer_strategy.encrypt_method);
			//初始化源端压缩
			$("#SourceEnd_compression").bootstrapSwitch('state', oldInfo.transfer_strategy.original_compress_flag);
			if(oldInfo.transfer_strategy.original_compress_flag){
				//初始化源端压缩等级
				$("#compression_level").val(oldInfo.transfer_strategy.original_compress_method);
			}
			//初始化传输线程
			$('#Socket-thread').spinner('value', oldInfo.transfer_strategy.thread_num);
			$('#transfer_threads').val(oldInfo.transfer_strategy.thread_num);
		
		}

		
	}
	//初始化安全策略
	var initSecurityPolicy = function(){
		



		if(EditFlag){
			// //初始化病毒检测
			// let virus_scan_info = [1,2];
			// //获取病毒检测信息
			// let virusBack = oldInfo.safe_strategy
			// if(virusBack =="" || virusBack == null|| virusBack==undefined){
			// }else{
			// 	virus_scan_info = [];
			// 	virusBack = virusBack.virus_scan_config_list[0];
			// 	if(virusBack.interrupt_policy == 1){
			// 		virus_scan_info.push(1);
			// 	}
			// 	if(virusBack.all_timepoints_flag == 1){
			// 		virus_scan_info.push(2);
			// 	}
			// }
		
			// // if()
			// $('#virusConfig').virusDetectionBackup("col-md-3",oldInfo.safe_strategy.virus_scan_flag,virus_scan_info);
			//完整性校验
			//判断授权
			if(CONF.FUNCTIONS.includes('integrity')){
				//如果有授权
				$('#completionConfig').completeDetectionBackup("col-md-3",
					oldInfo.safe_strategy.integrity_check_flag,
					CONF.MODULE_TYPE.OS,
					true,
					oldInfo.safe_strategy.integrity_info.integrity_check_strategy,
					oldInfo.safe_strategy.integrity_info.backup_integrity_check_full_error_policy,
					oldInfo.safe_strategy.integrity_info.backup_integrity_check_inc_error_policy
					);
			}else{
				//如果没有授权
				$("#completionConfig").hide();

			}
			
			//备份后立即验证
			// $('#sureBackupConfig').initSureBackupConfig({
			// 	colClass: 'col-md-3', // label宽度的css类
			// 	sureBackupFlag: true, // 默认是否开启
			// 	timepointRange: 1, // 时间点范围
			// 	threadNum: 1, // 同时启动验证数量
			// });
		}else{
			//初始化病毒检测
			// $('#virusConfig').virusDetectionBackup("col-md-3",false,[1,2]);
			//完整性校验
			if(CONF.FUNCTIONS.includes('integrity')){
				$('#completionConfig').completeDetectionBackup(precent = 'col-md-3',flag = true,module_type=5,reIncFlag = true, checkDay = CONF.CHECK_TIME.EVERY ,full = CONF.FULL_ABNORAL.REFULL,verification = CONF.OTHER_ABNORAL.REFULL)
			}else{
				//如果没有授权
				$("#completionConfig").hide();
			}
			
			//备份后立即验证
			// $('#sureBackupConfig').initSureBackupConfig();

		}
		

	}
	//初始化高级配置
	var initAdvanceConfig = function(){
		//初始化脚本框架元素
		var initScriptDom = function(script_host_uuid, script_host_name,Edit_backup_oss_info = {}){
			let panel_id = script_host_uuid+"_script_panel_id";
			let panel_delete = script_host_uuid+"_script_panel_delete";
			let panelbox_id = script_host_uuid+"_script_panelbox_id";
			let script_before = script_host_uuid+"_script_before";
			let script_after = script_host_uuid+"_script_after";

			var domHtml = `<div class="panel panel-default panel-file" id="${panel_id}">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a class="accordion-toggle accordion-toggle-styled popovers" data-toggle="collapse" data-parent=".scriptBox" href="#${panelbox_id}" aria-expanded="true" aria-controls="${panelbox_id}">
											<span class="font-green-seagreen">${script_host_name}</span>
										</a>
									</h4>
									<a id="${panel_delete}" value="${panel_id}"><button style="position: relative;left: 57px;float: right;top: -34px;" class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 Vinscript_delete">
										</button>
									</a>
								</div>
								<div id="${panelbox_id}" class="panel-collapse collapse in" aria-labelledby="headingOne" role="tabpanel" aria-expanded="false">
									<div class="panel-body" style="padding:16px">
										<div class="row" style="margin: 0;">
											<div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
												<ul class="nav nav-tabs tabs-left">
													<li class="dwm active" data-type="2"><a href="#${script_before}" data-toggle="tab" aria-expanded="true">
															<div class="iradio_square-blue strategy-radio-custom" style="position: relative;"><input type="radio" data-radio="iradio_square-blue" class="icheck" name="bakradio0"></div> ` + LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT+ `
														</a></li>
													<li class="dwm" data-type="3"><a href="#${script_after}" data-toggle="tab" aria-expanded="true">
															<div class="iradio_square-blue strategy-radio-custom" style="position: relative;"><input type="radio" data-radio="iradio_square-blue" class="icheck" name="bakradio0"></div> ` + LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT+ `
														</a></li>
												</ul>
											</div>
											<div class="col-md-10 col-sm-10 col-xs-10">
												<div class="tab-content">
													<div class="tab-pane fade in active" id="${script_before}">
														<div class="${script_before}"></div>
													</div>
													<div class="tab-pane fade in" id="${script_after}">
														<div class="${script_after}"></div>
													</div>
													
												</div>
											</div>
										</div>

									</div>
								</div>
							</div>`;
			//将dom元素添加到页面中
			$(".scriptBox").append(domHtml);
			//添加完成后初始化插件
			if(EditFlag){
				//得到脚本
				if(Object.keys(Edit_backup_oss_info).length != 0 && Edit_backup_oss_info.before_task_script.length != 0){
					$(".scriptBox").initVinScript({
						class:script_before,
						script_details:Edit_backup_oss_info.before_task_script
					})
				}else{
					$(".scriptBox").initVinScript({
						class:script_before,
					})

				}
				if(Object.keys(Edit_backup_oss_info).length != 0 && Edit_backup_oss_info.after_task_script.length != 0){
					//添加完成后初始化插件
					$(".scriptBox").initVinScript({
						class:script_after,
						script_details:Edit_backup_oss_info.after_task_script
					})
				}else{
					$(".scriptBox").initVinScript({
						class:script_after,
					})
				}
			}else{
				$(".scriptBox").initVinScript({
					class:script_before,
				})
				//添加完成后初始化插件
				$(".scriptBox").initVinScript({
					class:script_after,
				})
			}
			//初始化删除事件
			$("#"+panel_delete).on('click',function(){
				//移除这个panel
				$("#"+panel_id).remove();
				//放开脚本option
				$("#script_host option[value='"+script_host_uuid+"']").prop("disabled", false);
			})
		}
	
		

		//添加配置事件
		$("#addScript").on('click',function(){
			//获取脚本配置的选择项
			let script_host_uuid = $("#script_host").val();
			let script_host_name = $("#script_host option:selected").text();
			if(script_host_uuid == ""|| script_host_uuid == null) return;
			//禁用这个选项
			$("#script_host option[value='"+script_host_uuid+"']").prop("disabled", true);
			//初始化脚本配置
			initScriptDom(script_host_uuid,script_host_name);
		})

		//初始化重试策略
		$('#retry_config').retryStrategy();





		if(EditFlag){
			//静默快照
			$("#silent_snapshot").bootstrapSwitch('state', oldInfo.high_config.silent_snapshot_flag);
			//CBT
			$("#cbt").bootstrapSwitch('state', oldInfo.high_config.cbt_flag);
			//跳过坏快备份
			$("#skip_bad_block").bootstrapSwitch('state', oldInfo.high_config.skip_bad_track_flag);
			//有效数据备份
			$("#each_sector_backup").bootstrapSwitch('state', oldInfo.high_config.full_backup);
			//初始化重试策略
			$('#retry_config').retryStrategy({'retry_strategy': oldInfo.retry_strategy},'edit');
			//初始化数据文件大小
			$("#data_container_size").val(oldInfo.storage_strategy.data_container_size);
			//合并冗余数据比例
			$("#redundant_data_proportion").val(oldInfo.storage_strategy.redundant_data_proportiont);
			
			//忽略节点资源限制
			$("#ignore_resource_limiting_flag").bootstrapSwitch('state', oldInfo.ignore_resource_limiting_flag);

			//初始化脚本
			for(let i=0; i<oldInfo.backup_oss_info.length;i++){
				let agent_uuid = oldInfo.backup_oss_info[i].agent_uuid;
				let agent_name = oldInfo.backup_oss_info[i].agent_name;
				//初始化脚本配置
				initScriptDom(agent_uuid,agent_name,oldInfo.backup_oss_info[i]);

			}

		}
		
	}


	//初始化通用策略
	// initCommonPolicy(oldInfo);
	//初始化传输策略
	initTransferPolicy(oldInfo);
	//初始化安全策略
	initSecurityPolicy(oldInfo);
	//初始化高级配置
	initAdvanceConfig(oldInfo);

}
//--------------------------------------初始化第四步内容----------------------------------------------------------------------
var initFourth = function(oldInfo){
	

	var requestData  = function(data){
		if(data.success){
			//获得任务名
			$("#jobname").val(data.data);
			
		}else{
			UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_BACKUP_JOB_NAME, data.message);
		}
	}
	if(EditFlag){
		$("#jobname").val(oldInfo.task_name);
	}else{
		pAjaxRequest({}, "/api/v1/complete_machine_os/backup_name", "GET", requestData, true);
	}


	


}











//--------------------------------------获取第一步消息内容----------------------------------------------------------------------
var getStep1Msg = function(){
	//获取第一步消息内容
	resultData.backup_oss_info = [];
	//获取恢复源主机信息
	let backupinfo = $("#checkedSourceDiv").methodVinBackupSource('getData');
	var NoChecked = false;
	//循环backupinfo对象获取key和value值
	$.each(backupinfo, function(key, value) {
		//获取已经选择的
		var disk_list = value['disk_list'];
		if(disk_list.length == 0){
			NoChecked = true;
			return;
		}
		//初始化一个主机信息
		let eachBackupSourceInfo = {};
		eachBackupSourceInfo.agent_uuid = value['agent_uuid'];
		eachBackupSourceInfo.before_backup_script_location = "";
		eachBackupSourceInfo.after_backup_script_location = "";
		eachBackupSourceInfo.backup_mode = value['backup_relation'];
		eachBackupSourceInfo.os_config = {};
		eachBackupSourceInfo.os_config.excluding_devices = value['excluding_devices'];
		resultData.backup_oss_info.push(eachBackupSourceInfo);
	});
	if(EditFlag){
		//如果是修改则要判断是否有没在线的主机
		var checkedNodes = zTreeAgent.getCheckedNodes(true);
		//循环获取每个agent_uuid
		for(let i = 0; i < checkedNodes.length; i++){
			//如果不在线且不为分组则装填空数据
			if(!checkedNodes[i].online_flag && checkedNodes[i].type != 0){
				let eachBackupSourceInfo = {
					"agent_uuid": checkedNodes[i].uuid,
					"before_backup_script_location": "",
					"after_backup_script_location": "",
					"backup_mode": true,
					"os_config": {
						'excluding_devices':[],
					},
				}
				resultData.backup_oss_info.push(eachBackupSourceInfo);
			}
		}
	}
	if(NoChecked){
		UIToastr.showWarning(LANG.UI_MACHINE_OS_SELECTED_BACKUP_SOURCE, LANG.UI_MACHINE_OS_SELECT_DISK_OR_VOL_REQUIRED);
		return false;
	}
	if(resultData.backup_oss_info.length == 0){
		UIToastr.showWarning(LANG.UI_MACHINE_OS_SELECTED_BACKUP_SOURCE, LANG.UI_MACHINE_OS_SELECT_HOST_TIPS);
		return false;
	}

	//初始化第三步脚本配置
	var initScriptSelect = function(){
		//获取备份源勾选项
		let source_select = zTreeAgent.getCheckedNodes(true);
		//获取脚本配置的select对象
		let script_select = $("#script_host");
		selectedAgent = [];
		//循环勾选项
		for(let i = 0; i < source_select.length; i++){
			//获取type,如果type为0则跳过
			if(source_select[i].type == 0) continue;
			//获取agent_uuid
			let agent_uuid = source_select[i].id;
			//获取主机名称
			let agent_name = source_select[i].name;
			//存储agent_uuid
			selectedAgent.push(agent_uuid);
			//往select对象添加option
			script_select.append(`<option value="${agent_uuid}">${agent_name}</option>`);
		}
	}
	initScriptSelect();

	//检测第一步是否都已经初始化
	var checkInitFisrt = function(){ 
		//获取所有勾选的ztree
		var checkedNodes = zTreeAgent.getCheckedNodes(true);
		//获取勾选的是否超过2个了
		
		if(checkedNodes.length > 2){ 
			has_network_init = false; //没有传输网络
		}else{
			has_network_init = true;
		}
		
		//循环获取每个agent_uuid
		for(let i = 0; i < checkedNodes.length; i++){
			//如果不在线则跳过
			if(!checkedNodes[i].online_flag){
				continue;
			}
			//获取每一个
			var status = $("a[href='#"+checkedNodes[i].uuid+"_collapse_id']").attr('initStatus');
			if(status == "false"){
				UIToastr.showWarning(LANG.UI_MACHINE_OS_BACKUP_SOURCE_SELECTED, LANG.UI_MACHINE_OS_WAIT_LOADING_FINISH);
				return false;
			}
		}
		return true;
	}
	if(!checkInitFisrt()){
		return false;
	}
	
	return true;
}
//--------------------------------------获取第二步消息内容----------------------------------------------------------------------
var get_worm_type = function(storage_worm_config_flag){
	//1为正常配置worm，2为备份存储没有开启worm保护，3为选择的是存储资源池
	//获取存储
	let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
	if (!backupTargetInfo.storage_uuid) {  // 没有选择存储
		return 3;
	} else {
		if (!storage_worm_config_flag) {  // 存储未开启worm
			return 2;
		} else {
			return 1;
		}
	}
}
var getStep2Msg = function(){
	let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
	if(!backupTargetInfo){
		return false
	}
	//获取存储是否是磁带,如果是磁带则屏蔽安全策略
	if(CONF.BD_STORAGE_TYPE.TAPE == backupTargetInfo.storage_type){
		//如果是磁带则屏蔽安全策略
		$(".tab_safety").hide();
	}
	//初始化忽略节点限制
	initResourceLimit(backupTargetInfo.node_uuid_list);
	//初始化传输网络信息
	if(backupTargetInfo.node_uuid != "" && has_network_init){
		if(EditFlag){
			$('#transferNetworkTree').transferNetwork({node_uuid: backupTargetInfo.node_uuid,
				network_uuid: _OLDINFO.transfer_strategy.network,
				network_pool_uuid: _OLDINFO.transfer_strategy.network_pool_uuid,
			});
		}else{
			$('#transferNetworkTree').transferNetwork({node_uuid: backupTargetInfo.node_uuid});
		}
	}else{
		$(".transfernetworkDiv").hide();
		has_network = false; //没有传输网络
	}
	//初始化worm配置
	//初始化WORM防护
	//获取worm配置
	//先判断worm授权
	var initWorm = function(){ 
		let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		if(CONF.FUNCTIONS.includes('worm')){
			//如果已经授权
			let storage_worm_config = backupTargetInfo.storage_worm_config
			//获取是否打开
			let storage_worm_config_flag = storage_worm_config.flag;
			let worm_type = get_worm_type(storage_worm_config_flag);
			worm_show_flag = true;
			//如果存储有配置WORM保护
			if(EditFlag){
				//初始化WORM防护
				$('#wormConfig').wormProtectionBackup(worm_type,"col-md-3", false ,_OLDINFO.safe_strategy.worm_flag, _OLDINFO.safe_strategy.worm_protection_time);
				
			}else{
				$('#wormConfig').wormProtectionBackup(worm_type,"col-md-3",false,false, 7);
			}
			
		}
	}
	initWorm();
	//这里判断下是否3个都没授权
	// if(!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity') && !CONF.FUNCTIONS.includes('worm')){
	// 	//如果三个都没授权
	// 	// $("#tab_safety").hide();
	// 	$(".tab_safety").hide();
	// }else if(!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity') && CONF.FUNCTIONS.includes('worm')){
	// 	//如果授权了没授权, 但是worm授权了
	// 	if(worm_show_flag){
	// 		//如果存储配置了worm
	// 		// $("#tab_safety").show();
	// 		$(".tab_safety").show();
	// 	}else{
	// 		$("#tab_safety").hide();
	// 		$(".tab_safety").hide();
	// 	}
	// }else{
	// 	//其他情况就是不显示
	// 	// $("#tab_safety").show();
	// 	$(".tab_safety").show();
	// }
	
	
	resultData.node_info = backupTargetInfo;
	//判断存储类型
	let selectedStorageType = backupTargetInfo.storage_type;
	if(selectedStorageType == CONF.BD_STORAGE_TYPE.TAPE){
		$('#transfer_threads').val(1).prop('disabled', true); // 传输线程数字输入框禁用并赋值1
		//禁用spainner
		$('#Socket-thread').spinner('disable');
		
	}else{
		$('#transfer_threads').prop('disabled', false);
		$('#Socket-thread').spinner('enable');
		$(".mergemodediv").show();
	}
	initStep3Config();
	//初始化时间策略
	initCommonPolicy(_OLDINFO);
	$("#reserveMode").on('click',function(){
		if($("#reserveMode").val() == "2"){
			$(".mergemodediv").hide();
			mergemodedivFlag = true;
		}else{
			mergemodedivFlag = false;
		}
	})



	//初始化磁带
	//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
	getTapeStrategy(backupTargetInfo.storage_uuid, backupTargetInfo.storage_type,'.threadDiv', '.backupThreadDiv', '#tab_common', CONF.MODULE_TYPE.OS);


	//初始化勾选事件
	$('#strategymode').find('.icheck').on('ifClicked', function(event){
		var mode = $(this).data('mode');
		if(mode == 9){
			if(event.target.checked){
				//重新初始化
				initWorm();
			}else{
				$('#wormConfig').wormProtectionBackup(4,"col-md-3",false,false, 7);
			}
		}else{
			//其他就正常初始化
			initWorm();
		}
	});
	

	return true
}




//--------------------------------------获取第三步消息内容----------------------------------------------------------------------

var getStep3Msg = function(){
	//获取通用策略----------------
	resultData.strategyInfo = $('#backupStrategyDiv').getBackupStrategy();
	if(!resultData.strategyInfo){
		return false;
	}
	//要往公共组件的里面存储策略加2个参数
	//获取数据文件大小
	resultData.strategyInfo.store.storeInfo.data_container_size = $("#data_container_size").val();
	//获取合并冗余数据比例
	resultData.strategyInfo.store.storeInfo.redundant_data_proportion = $("#redundant_data_proportion").val();
	//获取传输策略----------------
	resultData.transfer_strategy = {};
	resultData.advanced_strategy = {};
	//获取加密传输
	resultData.transfer_strategy.encrypt = $("#encrypt").is(':checked');
	//获取加密算法
	resultData.transfer_strategy.encrypt_method = $("#encrypt_method").val();
	//获取传输网络
	if(!has_network){
		//如果没有传输网络
		//获取传输网络
		resultData.transfer_strategy.network = "";
		//获取传输网络池
		resultData.transfer_strategy.network_pool_uuid = "";
		//获取传输网络名称
		resultData.transfer_strategy.network_name = "";

	}else{
		//有传输网络
		let network = $('#transferNetworkTree').transferNetwork('getSelect');
		if(!network){
			return false;
		}
		//获取传输网络
		resultData.transfer_strategy.network = network.network_uuid;
		//获取传输网络池
		resultData.transfer_strategy.network_pool_uuid = network.network_pool_uuid;
		//获取传输网络名称
		resultData.transfer_strategy.network_name = network.str;

	}
	
	//获取传输线程
	resultData.thread_num = $("#transfer_threads").val();
	if(resultData.thread_num > 8 || resultData.thread_num < 1 || resultData.thread_num == ""){
		//重置为默认值
		$("#Socket-thread").spinner("value", 3);
		UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
		return false;
	}
	resultData.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
	if (!resultData.retry_strategy) {
		return false;
	};
	//获取源端压缩
	resultData.transfer_strategy.compress = $("#SourceEnd_compression").is(':checked');
	//获取压缩等级
	resultData.transfer_strategy.compress_method = $("#compression_level").val();
	
	//获取安全策略----------------
	resultData.safety_strategy = {};
	//获取WORM配置
	//获取是否打开
	let worm = "";
	if(resultData.node_info.storage_worm_config.flag && CONF.FUNCTIONS.includes('worm')){
		worm = $('#wormConfig').getWormProtectionSettings();
	}
	//获取病毒检测配置
	let virus_detection = "";
	if(CONF.FUNCTIONS.includes('virusKill')){
		virus_detection = $('#virusConfig').getVirusDetectionBackup();
		if (virus_detection === false) {  // 验证病毒扫描配置内容是否符合要求
			return false;
		}
	}
	let completion_check = "";
	if(CONF.FUNCTIONS.includes('integrity')){
		completion_check = $('#completionConfig').getCompleteDetectionBackup();
	}
	//获取完整性校验配置
	
	resultData.safe_config_strategy = safeData(worm,virus_detection,completion_check);
	//获取备份后立即验证
	// let surebackupData = $('#sureBackupConfig').getSureBackupData();
	// if(!surebackupData){
	// 	return false;
	// }
	// resultData.safe_config_strategy.create_surebackup_after_backup = surebackupData.create_surebackup_after_backup;
	// resultData.safe_config_strategy.surebackup_timepoint_range = surebackupData.surebackup_timepoint_range;
	// resultData.safe_config_strategy.surebackup_thread_num = surebackupData.surebackup_thread_num;
	//获取高级配置----------------	
	//获取有效数据备份
	resultData.advanced_strategy.full_backup = $("#each_sector_backup").is(':checked');
	//获取静默快照
	resultData.advanced_strategy.silent_snapshot_flag = $("#silent_snapshot").is(':checked');
	//获取CBT
	resultData.advanced_strategy.cbt_flag = $("#cbt").is(':checked');
	//获取跳过坏块备份
	resultData.advanced_strategy.skip_bad_block = $("#skip_bad_block").is(':checked');
	//获取合并模式
	

	//获取过载保护
	resultData.ignore_resource_limiting_flag = $("#ignore_resource_limiting_flag").is(':checked');

	
	
	//获取脚本配置
	//循环selectedAgent查找是否存在id为_script_panel_id的元素
	var getScriptConfig = function(){
		for(let i = 0; i < selectedAgent.length; i++){
			if($("#"+selectedAgent[i]+"_script_panel_id").length > 0){
				//获取脚本配置
				//查找resultData.backup_oss_info每个元素下面的agent_uuid是否和selectedAgent[i]相等
				for(let j = 0; j < resultData.backup_oss_info.length; j++){
					if(resultData.backup_oss_info[j].agent_uuid == selectedAgent[i]){
						//获取备份前脚本配置
						resultData.backup_oss_info[j].before_backup_script_location = $("#"+selectedAgent[i]+"_script_panel_id").getVinScript(selectedAgent[i]+"_script_before");
						if(!resultData.backup_oss_info[j].before_backup_script_location){
							return false;
						}
						//获取备份后脚本配置
						resultData.backup_oss_info[j].after_backup_script_location = $("#"+selectedAgent[i]+"_script_panel_id").getVinScript(selectedAgent[i]+"_script_after");
						if(!resultData.backup_oss_info[j].after_backup_script_location){
							return false;
						}
					}
				}
			}
		}
		return true;
	}
	if(!getScriptConfig()){
		return false;
	}


	

	initShow();
	return true;
	//



}

//--------------------------------------获取第四步消息内容----------------------------------------------------------------------
var initShow = function(){
	
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

	//初始化显示内容
	//已选择备份源显示----------
	let sourceInfoShowHtml = "";
	//循环resultData.backup_oss_info
	for(let i = 0; i < resultData.backup_oss_info.length; i++){
		//获取agent_uuid
		let agent_uuid = resultData.backup_oss_info[i].agent_uuid;
		//在树形结构中查找id为agent_uuid的节点
		let node = zTreeAgent.getNodeByParam('id', agent_uuid);
		if(node != null){
			sourceInfoShowHtml += LANG.UI_BACKUP_FILE_BAKHOST+": "+node.name+"<br>";
		}
		
		
		//获取os_config
		let excluding_devices = resultData.backup_oss_info[i].os_config.excluding_devices;
		//循环excluding_devices
		for(let j = 0; j < excluding_devices.length; j++){
			sourceInfoShowHtml += LANG.UI_MACHINE_OS_EXCLUDE_EQUIPMENT +": "+excluding_devices[j].dev_name+"<br>";
		}


		//获取备份前脚本配置
		if(resultData.backup_oss_info[i].before_backup_script_location != "" && resultData.backup_oss_info[i].before_backup_script_location.script_content != ""){
			sourceInfoShowHtml += LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW+": "+LANG.UI_DB_BACKUP_SET+"<br>";
		}else{
			sourceInfoShowHtml += LANG.UI_PUBLIC_BEFORE_BACKUP_SCRIPT_SHOW+": "+LANG.UI_DB_BACKUP_UNSET+"<br>";
		}

		//获取备份后脚本配置
		if(resultData.backup_oss_info[i].after_backup_script_location != "" && resultData.backup_oss_info[i].after_backup_script_location.script_content != ""){
			sourceInfoShowHtml += LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW+": "+LANG.UI_DB_BACKUP_SET+"<br>";
		}else{
			sourceInfoShowHtml += LANG.UI_PUBLIC_AFTER_BACKUP_SCRIPT_SHOW+": "+LANG.UI_DB_BACKUP_UNSET+"<br>";
		}
	}
	$('.sourceInfoShow').html(sourceInfoShowHtml);
	//目标节点----------
	let nodeInfoShowHtml = "";
	//获取selectnode选中的option的text值
	nodeInfoShowHtml += resultData.node_info.node_text+"<br>";
	$('.nodeInfoShow').html(nodeInfoShowHtml);
	//目标存储----------
	let storageInfoShowHtml = "";
	//获取selectnode选中的option的text值
	storageInfoShowHtml += resultData.node_info.storage_text+"<br>";
	$('.storageInfoShow').html(storageInfoShowHtml);
	//备份方式----------
	//时间策略----------
	let backuptypeinfoshowHtml = "";
	//获取selectnode选中的option的text值
	backuptypeinfoshowHtml += resultData.strategyInfo.time.des;
	$('.backuptypeinfoshow').html(backuptypeinfoshowHtml);
	//限速策略----------
	let speedlimitshowHtml = "";
	//获取selectnode选中的option的text值
	let speedInfo = resultData.strategyInfo.speedlimit.speedInfo;
	//循环speedInfo
	if(speedInfo.length == 0){
		speedlimitshowHtml = LANG.UI_PUBLIC_NOTHING;
	}else{
		for(let i = 0; i < speedInfo.length; i++){
			speedlimitshowHtml += speedInfo[i].des+"<br>";
		}
	}
	//循环
	$('.speedlimitshow').html(speedlimitshowHtml);
	//存储策略----------
	let storageinfoshowHtml = "";
	//获取selectnode选中的option的text值
	storageinfoshowHtml += resultData.strategyInfo.store.des;
	$('.storageinfoshow').html(storageinfoshowHtml);
	//保留策略----------
	let reservetypeshowHtml = "";
	//获取selectnode选中的option的text值
	reservetypeshowHtml += resultData.strategyInfo.reserve.des;
	let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
	if(backupTargetInfo.storage_type != CONF.BD_STORAGE_TYPE.TAPE){
		$('.reservetypeshow').html(reservetypeshowHtml);
	}
	//传输策略----------
	let transforshowHtml = "";
	//获取加密传输
	let encrypt_transfer = $("#encrypt").is(':checked');
	transforshowHtml += LANG.UI_OS_BACKUP_TRANSFER_ENCRY+": "+ getSwitchDes(encrypt_transfer) +"<br>";
	if(encrypt_transfer){
		transforshowHtml += LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD+": "+ $("#encrypt_method option:selected").text() +"<br>";
	}
	//源端压缩
	let SourceEnd_compression = $("#SourceEnd_compression").is(':checked');
	transforshowHtml += LANG.UI_MACHINE_OS_SOURCE_COMPRESS +": "+ getSwitchDes($("#SourceEnd_compression").is(':checked')) +"<br>";
	if(SourceEnd_compression){
		//压缩等级
		transforshowHtml += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE+": "+ $("#compression_level option:selected").text() +"<br>";
	}

	//传输网络
	if(has_network){
		transforshowHtml += LANG.UI_K8S_TRANSFER_NET+": "+resultData.transfer_strategy.network_name+"<br>";
	}
	//传输线程
	if(CONF.FUNCTIONS.includes('multithread')){
		transforshowHtml += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM+": "+ resultData.thread_num +"<br>";
	}
	
	
	$('.transforshow').html(transforshowHtml)
	//安全策略----------
	let safetyshowHtml = "";
	if(worm_show_flag && CONF.FUNCTIONS.includes('worm')){
		safetyshowHtml += LANG.UI_SAFE_STRATEGY_WORM_PROTECT+": "+ getSwitchDes(resultData.safe_config_strategy.worm_flag) +"<br>";
		if(Boolean(resultData.safe_config_strategy.worm_flag)){
			safetyshowHtml += LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD +": "+resultData.safe_config_strategy.worm_protection_time +" "+ LANG.UI_PUBLIC_UNIT_DAY +" <br>";
		}
	}
	if(CONF.FUNCTIONS.includes('virusKill')){
		safetyshowHtml +=$('#virusConfig').getVirusDetectionBackup().str; // 病毒检测的描述
	}
	if(CONF.FUNCTIONS.includes('integrity')){
		let completion_check = $('#completionConfig').getCompleteDetectionBackup();
		safetyshowHtml += completion_check.des;
	}
	$('.safetyshow').html(safetyshowHtml)
	//高级配置----------
	let advancedshowHtml = "";
	//静默快照
	advancedshowHtml += LANG.UI_BACKUP_SILENTSNAPSHOT+": "+ getSwitchDes(resultData.advanced_strategy.silent_snapshot_flag) +"<br>";
	//CBT
	advancedshowHtml += "CBT: "+ getSwitchDes(resultData.advanced_strategy.cbt_flag) +"<br>";
	//跳过坏块备份
	advancedshowHtml += LANG.UI_MACHINE_OS_SKIP_BAD_BLOCK_BACKUP+": "+ getSwitchDes(resultData.advanced_strategy.skip_bad_block) +"<br>";
	//有效数据备份
	advancedshowHtml += LANG.UI_MACHINE_OS_VALID_DATA_BACKUP +": "+ getSwitchDes(resultData.advanced_strategy.full_backup) +"<br>";
	//存储
	//数据文件大小
	//获取select选择的option的text
	var selectedText = $("#data_container_size option:selected").text();
	advancedshowHtml += $(".datacontainersizelabel").text() + ": " + selectedText + "<br>";
	//合并冗余数据比例
	if(!mergemodedivFlag){
		advancedshowHtml += $(".mergemodelabel").text() + ": " + $("#redundant_data_proportion").val()+"%" + "<br>";
	}
	//忽略节点资源限制
	advancedshowHtml += LANG.UI_NODE_RESOURCE_LIMIT_IGNORE +": "+ getSwitchDes(resultData.ignore_resource_limiting_flag);
	
	
	//合并模式
	$('.advancedshow').html(advancedshowHtml)
}



const initStep3Config = () => {
	// 初始化安全策略
	
		//获取备份源勾选项
		let source_select = zTreeAgent.getCheckedNodes(true);
		let sourceAgentUuid = '';
		let backupHostCount = 0;
		let excludeMountPointList = [];
		//循环勾选项
		for(let i = 0; i < source_select.length; i++){
			//获取type,如果type为0则跳过
			if(source_select[i].type == 0) continue;
			backupHostCount++;
			if (sourceAgentUuid === '') {
				sourceAgentUuid = source_select[i].id;
				for (const excludeDevice of resultData.backup_oss_info[0].os_config.excluding_devices) {
					excludeMountPointList.push(excludeDevice.mount_point);
				}
			}
		}
		if (backupHostCount > 1) {  // 多主机备份不显示选择路径
			sourceAgentUuid = '';
		}
		//仅仅当backupHostCount为1, 即备份的时候只勾选了一个主机时才进行路径效验
		let os_type = "";
		if(backupHostCount == 1){
			for(let i = 0; i < source_select.length; i++){
				//获取type,如果type为0则跳过
				if(source_select[i].type == 0) continue;
				os_type = source_select[i].os_type;
			}
		}



		//初始化病毒检测
		//判断是否有授权
		if(CONF.FUNCTIONS.includes('virusKill')){
			//有授权就初始化
			let virus_config = {
				agent_uuid: sourceAgentUuid,
				exclude_mount_point_list: excludeMountPointList,
			}
			if(os_type != ""){
				virus_config.os_type = os_type;
			}
			$('#virusConfig').virusDetectionBackup("col-md-3", false, true, true, virus_config);

		}else{
			//没有授权
			$("#virusConfig").hide()
		}
		
	
		if(EditFlag){
			//获取病毒检测信息
			let interruptPolicy = false;
			let allTimepointsFlag = false;
			let virusBack = {
				virus_thread_num: 2,
				scan_entire_system_flag: 1,
				specific_scan_target_map: [],
			};
			if(Array.isArray(_OLDINFO.safe_strategy.virus_scan_config_list) && _OLDINFO.safe_strategy.virus_scan_config_list.length > 0) {
				virusBack = _OLDINFO.safe_strategy.virus_scan_config_list[0];
				if(virusBack.interrupt_policy == 1){
					interruptPolicy = true;
				}
				if(virusBack.all_timepoints_flag == 1){
					allTimepointsFlag = true;
				}
			}

			//获取备份源勾选项
			let source_select = zTreeAgent.getCheckedNodes(true);
			let sourceAgentUuid = '';
			let backupHostCount = 0;
			//循环勾选项
			for(let i = 0; i < source_select.length; i++){
				//获取type,如果type为0则跳过
				if(source_select[i].type == 0) continue;
				backupHostCount++;
				if (sourceAgentUuid === '') {
					sourceAgentUuid = source_select[i].id;
					for (const excludeDevice of resultData.backup_oss_info[0].os_config.excluding_devices) {
						excludeMountPointList.push(excludeDevice.mount_point);
					}
				}
			}
			if (backupHostCount > 1) {  // 多主机备份不显示选择路径
				sourceAgentUuid = '';
			}
			//仅仅当backupHostCount为1, 即备份的时候只勾选了一个主机时才进行路径效验
			let os_type = "";
			if(backupHostCount == 1){
				for(let i = 0; i < source_select.length; i++){
					//获取type,如果type为0则跳过
					if(source_select[i].type == 0) continue;
					os_type = source_select[i].os_type;
				}
			}
			if(os_type != ""){
				virusBack.os_type = os_type;
			}
			virusBack.agent_uuid = sourceAgentUuid;
			virusBack.exclude_mount_point_list = excludeMountPointList;
			// 判断授权
			if(CONF.FUNCTIONS.includes('virusKill')){
				$('#virusConfig').virusDetectionBackup("col-md-3",_OLDINFO.safe_strategy.virus_scan_flag,interruptPolicy,allTimepointsFlag, virusBack);
			}else{
				//没有授权
				$("#virusConfig").hide()
			}
			
			
		}
		
	
};




	
	var step1Valid = function(){
		return getStep1Msg();
	}
	var step2Valid = function(){
		if (!getStep2Msg()) {
			return false;
		}
		return true;
	}
	var step3Valid = function(){
		return getStep3Msg();
	}

	var submit = function(){
		let jobName = $.trim($("#jobname").val());
		if('' == jobName){
			UIToastr.showWarning(LANG.UI_REPORT_TASKNAME, LANG.UI_MACHINE_OS_JOB_NAME_TIPS);
			return;
		}
		// 输入验证
		if(!customInputValidate('string',jobName)){
			return false;
		}
		//获取任务名称
		resultData.task_name = jobName;
		Metronic.blockUI({target: "#machineOsBackupContent",animate: true});
		//提交数据
		var requestData  = function(data){
			Metronic.unblockUI("#machineOsBackupContent");
			if(data.success){
				if(EditFlag){
					UIToastr.showSuccess(LANG.UI_MACHINE_OS_EDIT_SCHEDULED_MACHINE_BACKUP_TASK, data.message);
				}else{
					UIToastr.showSuccess(LANG.UI_MACHINE_OS_CREATE_BACKUP_JOB, data.message);
				}
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}else{
				if(EditFlag){
					UIToastr.showWarning(LANG.UI_MACHINE_OS_EDIT_SCHEDULED_MACHINE_BACKUP_TASK, data.message);
				}else{
					UIToastr.showWarning(LANG.UI_MACHINE_OS_CREATE_BACKUP_JOB, data.message);
				}
			}
		}
		//初始化备份源
		pAjaxRequest(resultData, '/api/v1/complete_machine_os/create_backup_job', "POST", requestData, true);
		
		

	};
	
	
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
//            $('.step-title', $('#machineOsBackupContent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#machineOsBackupContent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#machineOsBackupContent').find('.button-previous').css('visibility', 'hidden');
                $('#machineOsBackupContent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#machineOsBackupContent').find('.button-previous').css('visibility', 'visible');
                $('#machineOsBackupContent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#machineOsBackupContent').find('.button-next').hide();
                $('#machineOsBackupContent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#machineOsBackupContent').find('.button-next').show();
                $('#machineOsBackupContent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#machineOsBackupContent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
                /*
                success.hide();
                error.hide();
                if (form.valid() == false) {
                    return false;
                }
                handleTitle(tab, navigation, clickedIndex);
                */
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

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#machineOsBackupContent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#machineOsBackupContent').find('.button-previous').css('visibility', 'hidden');
        $('#machineOsBackupContent .button-submit').click(submit).css('visibility', 'hidden');
	};
	


    return {
        //main function to initiate the module
        init: function () {
			getOldInfo(); //初始化历史数据
			
        	wizardInit();
        	
        },
        
    };
}();

jQuery(document).ready(function() {   
	MachineOsBackup.init();
});