var machineOSRecover = function () {
	//初始化数据
	var resultData = {};
	//驱动检测暂存值
	var driverData = {};
	var pointtypetree, pointtypetreeInitFlag = false;
	var currentTree; //当前展示的树
	var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	var nodeParamList;
	var initSpeedFlag = false;
	var speedList = [];
	var linkInfo;
	var OSTYPE; //用于存放这次选择的时间点的操作系统类型  ,因为目前只选了一个时间点 暂时用一个变量先存储 后期再修改
	var encry_flag; //是否需要手动输入时间点的密码
	var networkFlag = false; //是否显示传输网络
	var timepointNode;
	var hostNameLimit = {}; //主机名称限制

	var firstSelectTimepoint = []; //第一步获取的勾选值,用于返回上一步又返回下一步避免重复初始化使用
	var clickedTypeValue = "completeMachineRecovery"; //用于避免重复点击恢复方式初始化
	const externalPointUuid = $('#externalPointUuid').val();
	const externalTaskUuid = $('#externalTaskUuid').val();
	const externalItemUuid = $('#externalItemUuid').val();

	//------
	var defaultConfig = {
        dom: $('#backupStrategyDiv'),
        mode: [1, 2, 3],
		time:false,
		store:false,
		reserve:false,
    };

	var passwordList = {}; //时间点密码集合

	var complete_backup = true; //初始化恢复组件新增了一个参数backup_disable_flag，取值true表示备份没有配置完整性策略，false表示备份配置了

	



    //初始化数据
    var initInfo = function(){
        //初始化第一步
		initFirst();
		//初始化第三步
		initThird();
		//初始化第四步
		initFourth();

	}
//部分全局公共函数-----------------------------------------------------------------------------------------------------------------
//替换特殊字符
var clearString = function (s, flag){
    var rs = "";
    var str = '_';
    if(flag){
        str = '';
    }
    for (var i = 0; i < s.length; i++) {
        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, str);
    }
    return rs;
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





//--------------------------------------初始化第一步内容----------------------------------------------------------------------
var initFirst = function(){
	//单击展开
	var zTreeOnClick = function(event, treeId, treeNode) {
		var treeObj = $.fn.zTree.getZTreeObj(treeId);
		if (treeNode.open) {
			treeObj.expandNode(treeNode, false, false, true,true); // 收拢节点
		} else {
			treeObj.expandNode(treeNode, true, false, true,true); // 展开节点
		}
		//判断当前节点是否被勾选
		if(treeNode.checked){
			//勾选当前节点
			treeObj.checkNode(treeNode, false, true, true); 
		}else{
			//勾选当前节点
			treeObj.checkNode(treeNode, true, true, true); 
		}
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



    var timepointOnCheck = function(e, id, node){
		var flag = node.checked;
		var allNodes = pointtypetree.getCheckedNodes(true);
		//判断时间点存储类型是否有磁带, 有磁带的要屏蔽安全策略
		//获取所有勾选的时间点的存储类型
		if(allNodes.length > 0){
			var selected_storage_type_list = [];
			for(var i = 0; i < allNodes.length; i++){
				selected_storage_type_list.push(parseInt(allNodes[i].storage_type))
			}
			//去重
			let uniqueStorageTypes = [...new Set(selected_storage_type_list)];
			//如果selected_storage_type_list里面存在两种不同的元素,并且其中有一个元素的值为10
			if(uniqueStorageTypes.length > 1 && uniqueStorageTypes.indexOf(CONF.BD_STORAGE_TYPE.TAPE) != -1){
				//如果两个类型不同并且其中有一个磁带类型
				//包含有数据加码
				bootbox.confirm({
					title: LANG.UI_MACHINE_OS_CHOOSE_TIMEPOINT,
					message: LANG.UI_MACHINE_OS_NOT_CHOOSE_TAPE_TIMEPOINT, // 例如："该操作需要密码验证，请确认继续？"
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
							pointtypetree.checkAllNodes(false);
							$('#VMGroupList li').remove();
							$('#timepointGroupList li').remove();
							//勾选当前时间点
							pointtypetree.checkNode(node, true, false, false);
							addPointList(id, node);
							$(this).modal('hide');
							return true;
						} else {
							// 用户点击了“取消”
							pointtypetree.checkNode(node, false, false, false);
							addPointList(id, node);
							return true;
						}
					}, 300, false)
				});
			}
		}
		

		//判断勾选的所有节点是否是磁带
		for(var i = 0; i < allNodes.length; i++){
			if(allNodes[i].storage_type == CONF.BD_STORAGE_TYPE.TAPE){
				//如果存在磁带则屏蔽安全策略
				$(".otherLi").hide();
				$('#transfer_threads').val(1).prop('disabled', true); // 传输线程数字输入框禁用并赋值1
				//禁用spainner
				$('#Socket-thread').spinner('disable');

			}
		}

		var checkTimepoint = function(){
			//用于判断是否在同一个节点上
			if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, false)){
				$('#VMGroupList li').remove();
				$('#timepointGroupList li').remove();
				addPointList(id, node);
				return;
			}
			pointtypetree.checkNode(node, flag, false, false);
			addPointList(id, node);
		}
		//获取是否有密码
		var encrypted_flag = node.encrypted_flag;
		if(flag){
			if(encrypted_flag){
				//包含有数据加码
				bootbox.prompt({ 
					title: LANG.UI_VM_INPUT_DB_ENCRY_PWD, 
					inputType: 'password',
					callback: debounce(function (pwdResult) {
						if(pwdResult == ""){
							UIToastr.showWarning(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, LANG.UI_MACHINE_OS_ENTER_PASSWORD_TO_CONFIRM);
							return false;
						}
						if(pwdResult == null) {
							pointtypetree.checkNode(node, false, false, false);
							return;
						};
						//获取密码
						let password = btoa(pwdResult);
						let requestList = {
							'timepoint_uuid' : node.timepointuuid,
							'password': password,
						};
						let checkFlag = false;
						//获取密码是否正确
						var requestData  = function(data){
							if(data.success){
								//密码验证成功不提示
								// $("#"+node.timepoint_uuid+"_password").attr('pass','true');
								checkFlag = true;
								passwordList[node.timepointuuid] = password;
								checkTimepoint();
								UIToastr.showSuccess(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, data.message);
							}else{
								//密码验证失败给出失败的提示
								// $("#"+node.timepoint_uuid+"_password").attr('pass','false');
								UIToastr.showWarning(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, LANG.UI_MACHINE_OS_PASSWORD_VERIFY_FAILED);
							}
						}
						//初始化备份源
						pAjaxRequest(requestList, '/api/v1/jobs/password_check', "GET", requestData, false);
						if(checkFlag){
							$(this).modal('hide');
						}
						return checkFlag
					},300,false)
				});
	
	
	
	
			}else{
				checkTimepoint();
			}
		}else{
			checkTimepoint();
		}
		

		
    }

    //勾选添加主机显示列表
	var addPointList = function(id,node){
		//设置未进行连接测试
		linkFlag = false;

		var info = "";
		var liId = clearString(id + node.agentuuid + node.id + node.dbuuid, false); //添加虚拟机每列ID
		if(node.checked){
			info +=
			'<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +'">' +
				'<div class="col1">' +
					'<div class="cont vmDetail">' +
						'<div class="cont-col1">' +
							'<div class="'+node.iconSkin+'"></div>' +
						'</div>' +
						'<div class="cont-col2">' +
							'<div class="desc list-one" style="font-size: 14px;color: #333;padding: 10px 4px 0px 4px;">' + node.osname + '</div>' +
							'<div class="desc list-one" style="font-size: 12px;color: #666;padding: 4px 4px 0 4px;">' + node.name + '</div>' +
						'</div>' +
					'</div>' +
				'</div>' +
				'<div class="col2  pull-right delete-list" style="position:absolute;right:0;width:30px;padding-top: 8px;">' +
					'<a class="del'+liId+'" >' +
						'<div class="label label-sm label-danger" style="padding:0;">' +
							'<i class="viconfont vicon-guanbi"></i>' +
						'</div>' +
					'</a>' +
				'</div>' +
			'</li>';
			//添加勾选的时间点到其中
			$('#timepointGroupList').append(info);

			$('#' + escapeJquery(liId)).popover();	   //初始化tips
			//移除已选时间点显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				var treeObj = $.fn.zTree.getZTreeObj(id);
				$('.popover.in').remove();
	    		treeObj.checkNode(node,false,false);
	    		$('#' + escapeJquery(liId)).remove();
			});

		}else{
			//检查node.id是否在虚拟化里面，如果在就要把对应的li移除
			$('#' + escapeJquery(liId)).remove();
		}



	}



    //判断是否在一个备份节点上
	var checkSelectInOneNode = function(flag, tree, node, allNodes, checkTypeFlag){
		if(!flag) return true;
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

    //异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag, chooseFlag){
		var storageuuid = $('#storageselect').val();
		var div = "#pointtypetree";
		var p = {taskuuid:treeNode.taskuuid, agentuuid:treeNode.agentuuid,recoverflag:true,dataflag:false,
				refresh:refreshFlag, storageuuid: storageuuid};
		Metronic.blockUI({target: div,animate: true});
        var requestData  = function(data){
			if(data.success){
				Metronic.unblockUI(div);
                //success
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, data.data, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                if(expendFlag == true){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                }
				 // 从备份数据跳转恢复页面，匹配时间点并勾选
                let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
                if(targetNode && chooseFlag){
                    $.fn.zTree.getZTreeObj(treeId).checkNode(targetNode, true, true, true);
                }
		    }else{
                UIToastr.showWarning(LANG.UI_OS_RECOVERY_CHOOSE_BACKUP_TIMEPOINT, data.message);
            }
        }
        //初始化备份源
        pAjaxRequest(p, '/api/v1/complete_machine_os/sync_timepoint_tree', "GET", requestData, true);

	}

	 /**
     * 添加时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
	 const addPointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.level !== 2 && treeNode.level !== 3) {//不是时间点
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
    };

    /**
     * 移除时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
    const removePointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.level !== 2) {//不是时间点
            return;
        }
        $(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).off().remove();
    };



    //初始化时间点树
	var setPointTree = function(zNodes){
		if (zNodes.length == 0) {
			$("#nopointtips").show();
			$('.vcenter-tree').hide();
			$("#pointtypetree").hide();
			return;
		} else {
			$("#nopointtips").hide();
			$('.vcenter-tree').show();
			$("#pointtypetree").show();
		}
		Metronic.unblockUI('.os_tree');
		// if(!checkTreeNodeInfo(zNodes)) return;
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false,
					chkboxType: { "Y" : "", "N" : "" } // 禁用联动勾选
				},
				data: {
					simpleData: {
						enable: true
					},
					key: {
						// title: "title"
					}
				},
				callback: {
					// beforeClick: osnodeSelect,
					onCheck: timepointOnCheck,
					beforeExpand: nodeExpand,
					onClick: zTreeOnClick

				},
				view: {
					showTitle: true,
					nameIsHTML: true,
					fontCss: function(treeId, treeNode) {
						let css = {};
						if (treeNode.point_status == 1) {
							css = { color: "#F19F00 " };
						}
						if (treeNode.point_status == 3) {
							css = { color: "#F1416C " };
						}
						return css;
					},
					addHoverDom: addPointTreeHoverDom,
					removeHoverDom: removePointTreeHoverDom,
				}
			};
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, zNodes);
		// 从备份数据跳转恢复页面，展开对象下的时间点
		let targetNode = pointtypetree.getNodeByParam('id', externalTaskUuid + externalItemUuid);
		// 第一次初始化，且有目标节点
		if(targetNode && !pointtypetreeInitFlag){
			// 异步获取时间点
			getSyncVcenterInfo('pointtypetree',targetNode, true, true, true)
		}
		pointtypetreeInitFlag = true;
		currentTree = pointtypetree;
	};

    //初始化时间点树
    var initTimePointTree = function(){
		//清空已选择时间点
        var requestparams = {};
		requestparams.storageuuid = $('#storageselect').val();
		requestparams.searchVal = $('#searchos').val();
		// 输入验证
		if(!customInputValidate('string',requestparams.searchVal)){
			return false;
		}
		requestparams.recoverflag = true;
		requestparams.dataflag = false;
		var treeObj = $.fn.zTree.getZTreeObj("pointtypetree");
		if(treeObj == null){
			Metronic.blockUI({target: '.os_tree',animate: true});
		}

        var requestData  = function(data){
			if(data.success){
				if(data.data.length == 0){
					$("#nopointtips").hide();
					$('.vcenter-tree').hide();
					$("#pointtypetree").hide();
					$("#nosearchtips").show();
					return;
				}else{
					$("#nopointtips").hide();
					$('.vcenter-tree').show();
					$("#pointtypetree").show();
					$("#nosearchtips").hide();
				}
				if(treeObj == null){
					setPointTree(data.data);
				}else{
					//完成初始化后需要展开所有异步加载操作
					//获取当前ztree对象
					//获取所有节点
					var nodes = treeObj.getNodes();

					//获取已经勾选的节点
					var checkedNodes = treeObj.getCheckedNodes(true);
					//找到所有勾选的节点的父级,一直循环找到所有父级
					var checkedNodes_parent = [];
					// 递归函数，用于找到所有父节点
					var findParentNodes = function(node) {
						if (node.getParentNode()) {
							checkedNodes_parent.push(node.getParentNode());
							findParentNodes(node.getParentNode());
						}
					};

					// 遍历所有勾选的节点，找到它们的所有父节点
					for (var j = 0; j < checkedNodes.length; j++) {
						findParentNodes(checkedNodes[j]);
					}

					// 去重处理，确保父节点不重复
					checkedNodes_parent = checkedNodes_parent.filter((node, index, self) =>
						index === self.findIndex((t) => (
							t.id === node.id
						))
					);
					
					
					

					//隐藏所有节点
					treeObj.hideNodes(nodes);
					//循环数据
					var findNodes = data.data;
					for(let i=0; i<findNodes.length;i++){
						//得到每个id
						var findNodes_id = findNodes[i]['id'];
						var findNodes_nodes = treeObj.getNodesByParam("id", findNodes_id, null);
						//找到后显示该节点
						treeObj.showNodes(findNodes_nodes);
					}

					// 把勾选节点的父节点也展示出来
					for (var k = 0; k < checkedNodes_parent.length; k++) {
						var parentNodeId = checkedNodes_parent[k].id;
						var parentNode = treeObj.getNodeByParam("id", parentNodeId, null);
						if (parentNode) {
							treeObj.showNode(parentNode);
							// treeObj.checkNode(parentNode, true, false); // 勾选父节点但不级联勾选子节点
						}
					}


					// treeObj.showNode(findNodes);
				}

			}else{
				UIToastr.showWarning(LANG.UI_OS_RECOVERY_CHOOSE_BACKUP_TIMEPOINT, data.message);
			}
		}
		//初始化备份源
		pAjaxRequest(requestparams, '/api/v1/complete_machine_os/timepoint_tree', "GET", requestData, true);
    };

	//初始化存储
	var initStorageSelect = function(){
        pAjaxRequest({}, '/api/v1/storages/type', "GET", (result) => {
            if (result.success) {
                let data = result.data;
                let storageSelect = $('#storageselect')
                storageSelect.empty();
                for (let i = 0; i < data.length; i++) {
                    let option = $("<option>").text(data[i].text).val(data[i].storageid);
                    storageSelect.append(option);
                }
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_STORAGE_FAILED, result.message);
            }
        })
        //绑定事件
        $('#storageselect').on('change', initTimePointTree);
    }

	let inputTimeout;

	// $(document).on('keypress', function(event) {
	// 	if (event.which === 13) { // 13 是回车键的 keyCode
	// 		clearTimeout(inputTimeout); // 清除定时器
	// 		initTimePointTree();
	// 		event.preventDefault(); // 防止默认行为
	// 	}
	// });

	$("#searchos").on('input', function() {
		clearTimeout(inputTimeout); // 清除之前的定时器
		inputTimeout = setTimeout(function() {
			initTimePointTree();
		}, 1000); // 1000 毫秒 = 2 秒
	});
	initStorageSelect();
    initTimePointTree();



}

//--------------------------------------初始化第二步内容----------------------------------------------------------------------
var initSecond = function(){
	//初始化点击事件
	//当切换成数据卷恢复或整机恢复的时候清空目标机配置重新初始化
	$('[name="recoverType"]').on('click', function() {
		// 获取被点击的标签的 value 值
		var clickedValue = $(this).attr('value');
		if(clickedTypeValue != "" && clickedTypeValue == clickedValue){
		
		}else{
			clickedTypeValue = $(this).attr('value');
			//清空数据
			$(".targetBox").html("");
			//初始化目标机配置
			getOptionIp();

		}
		
		
	  });




	//初始化目标机配置内容dom元素 // 脚本处bug21809要求改为左右结构
	var initTargetDom = function(timepointInfo, iplist){
		//获取恢复方式
		var recover_type =$('[name="recoverType"]').filter('.active').attr('value');
		//是否显示网络配置
		var isShowNetConfig = "display:block";
		var isShowbootMedia = "display:block";
		var isShowrename = "display:block";
		if(recover_type == "dataVolumeRecovery"){
			isShowNetConfig = "display:none";
			isShowbootMedia = "display:none";
			isShowrename = "display:none";
		}

		

		var DomHtml = `<div class="panel panel-default panel-file" id="${timepointInfo.timepoint_uuid}_target_panel_id">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a class="accordion-toggle accordion-toggle-styled popovers" data-toggle="collapse" data-parent=".targetBox" href="#${timepointInfo.timepoint_uuid}_target_collapse_id" aria-expanded="true" aria-controls="${timepointInfo.timepoint_uuid}_target_collapse_id">
										<span class="font-green-seagreen">`+ LANG.UI_VOL_CDP_JOB_DETAILS_HOST +`: ${timepointInfo.host_name}</span>
										<span class="strategyDes storeDes">`+ LANG.UI_MICROSOFT365_TIME_POINT +`: ${timepointInfo.timepoint_name}</span>
									</a>
								</h4>
							</div>
							<div id="${timepointInfo.timepoint_uuid}_target_collapse_id" class="panel-collapse collapse in" aria-labelledby="headingOne" role="tabpanel" aria-expanded="false">
								<div class="panel-body" style="padding:16px">

									<div class="form-group" style="${isShowbootMedia}">
										<label class="control-label col-md-3 col-md-3_en" style="float: left;width:150px;">
											<span class="required">* </span>
											`+ LANG.UI_MACHINE_OS_BOOT_MEDIA +`
										</label>
										<div class="col-md-4 col-md-5_en">
											<div class="radio-container">
												<input type="radio" name="${timepointInfo.timepoint_uuid}_boot_media" value="1" id="${timepointInfo.timepoint_uuid}_boot_media_manual" checked>
												<label for="${timepointInfo.timepoint_uuid}_boot_media_manual" style="margin-right:16px;">${LANG.UI_MACHINE_OS_MANUAL_DEPLOY}</label>
												<input type="radio" name="${timepointInfo.timepoint_uuid}_boot_media" value="2" id="${timepointInfo.timepoint_uuid}_boot_media_auto">
												<label for="${timepointInfo.timepoint_uuid}_boot_media_auto">${LANG.UI_MACHINE_OS_AUTO_DEPLOY}</label>
											</div>
										</div>
										<div class="col-md-2 mt5">
											<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="${LANG.UI_MACHINE_OS_DEPLOY_DES}">
												<i class="viconfont vicon-tishi"></i>
											</a>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-md-3 col-md-3_en" style="float: left;width:150px;">
											<span class="required">* </span>
											`+ LANG.UI_MACHINE_OS_RECOVERY_TARGET +`
										</label>
										<div class="col-md-4 osselect_en">
										<select class="form-control select2me ipselect" id="${timepointInfo.timepoint_uuid}_ipselect">
											<option value="">`+ LANG.UI_RECOVERY_AWS_PLEASE_SELECT_TIPS +`</option>
										</select>
										</div>
										<button class="btn btn-light-primary me-2 linkChecked" status="false" status_agent_uuid="" type="button" id="${timepointInfo.timepoint_uuid}_linkChecked">`+ LANG.UI_DRIVER_CHECK_BTN +`</button>
									</div>
									<div class="form-group changeDisplay">
											<div id="${timepointInfo.timepoint_uuid}_driverCheck"></div>
											<div id="${timepointInfo.timepoint_uuid}_driverCheckFailContinue" class="display-none">
												<div class="form-group ">
													<div class="col-md-6 driver-config__content">
														<label style="float:left;line-height:32px;margin-right:12px;">${LANG.UI_MACHINE_OS_DRIVER_MATCH_TIP}: </label>
														<div class="input-group " id="${timepointInfo.timepoint_uuid}_continue_driver_fail">
															<label style="padding-right: 20px;">
																<input type="checkbox" id="${timepointInfo.timepoint_uuid}_continueCheck" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck">
															</label>
														</div>
													</div>
												</div>
											</div>
									</div>
									<div class="form-group sourceConfigDiv changeDisplay display-none" id="${timepointInfo.timepoint_uuid}_sourceConfigDiv" style="margin-bottom: 10px;">
										<div class="portlet-body">
											<div class="tabbable tabbable-custom">
												<ul class="nav nav-tabs">
													<li class="active">
														<a href="#${timepointInfo.timepoint_uuid}_tab_1_1" data-toggle="tab" aria-expanded="true">
															<i class="viconfont vicon-a-zancunbaocun"></i> `+ LANG.UI_MACHINE_OS_TARGET_VOLUME_CONFIG +` </a>
													</li>
													<li style="${isShowNetConfig}">
														<a href="#${timepointInfo.timepoint_uuid}_tab_1_2" data-toggle="tab" aria-expanded="false">
															<i class="viconfont vicon-wangluo"></i> `+ LANG.UI_VERIFY_NETWORK_CONFIG +` </a>
													</li>
													<li>
														<a href="#${timepointInfo.timepoint_uuid}_tab_1_3" data-toggle="tab" aria-expanded="false">
															<i class="viconfont vicon-gaojipeizhi"></i> `+ LANG.UI_PUBLIC_ADVANCED_CONFIG +` </a>
													</li>
													<li>
														<a href="#${timepointInfo.timepoint_uuid}_tab_1_4" data-toggle="tab" aria-expanded="false">
															<i class="viconfont vicon-ziyuanxiangqing"></i> `+LANG.UI_MACHINE_OS_SCRIPT_CONFIG+`</a>
													</li>
												</ul>
												<div class="tab-content" style="padding: 12px;">
													<div class="tab-pane active" id="${timepointInfo.timepoint_uuid}_tab_1_1">
														<div class="portlet-body">
															<div id="${timepointInfo.timepoint_uuid}_recover_target_id"></div>
														</div>
													</div>

													<div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_2">
														<div class="portlet-body">
															<div id="${timepointInfo.timepoint_uuid}_ipconfig_id"></div>
														</div>
													</div>

													<div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_3">
														<div class="portlet-body">
															<div class="form-group">
																<label class="control-label col-md-3">`+ LANG.UI_VM_SETTING_V2_BOOT_TYPE +`
																</label>
																<div class="col-md-4">
																	<select class="form-control disabled" id="${timepointInfo.timepoint_uuid}_boot_mode_id">
																		<option value="1">BIOS</option>
																		<option value="2">EFI</option>
																	</select>
																</div>
															</div>
															<div class="form-group" style="${isShowrename}">
																<label class="control-label col-md-3 form-group-label">`+LANG.UI_VM_RESET_HOST_NAME+`</label>
																<div class="col-md-3 form-group-content">
																	<input type="checkbox" id="${timepointInfo.timepoint_uuid}_rename_check" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
																</div>
															</div>
															<div class="form-group reconnect-times-wrapper ${timepointInfo.timepoint_uuid}_rename_id" style="display: none;">
																<label class="control-label col-md-3 reconnect-times-Label"></label>
																<div class="col-md-4">
																	<input type="text" id="${timepointInfo.timepoint_uuid}_rename_id" class="spinner-input form-control input-sm" value="`+ LANG.UI_COPY_DETAIL_HOST_NAME +`">
																</div>
															</div>

															<div class="form-group ${timepointInfo.timepoint_uuid}_transfernetworkDiv">
																<label class="control-label col-md-3 transfernetworklabel">${LANG.UI_NODE_NETWORK_TRANSFER}</label>
																	<div class="col-md-4">
																		<ul class="ztree" id="${timepointInfo.timepoint_uuid}_transferNetworkTree"></ul>
																	</div>
																<div class="col-md-2 mt5">
																	<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="${LANG.UI_PLATFORM_RECOVERY_NETWORK_CONNECT}">
																		<i class="viconfont vicon-tishi"></i>
																	</a>
																</div>
															</div>

														</div>
													</div>

													<div class="tab-pane" id="${timepointInfo.timepoint_uuid}_tab_1_4">
														<div class="row row-stepthree">
															<div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
																<ul class="nav nav-tabs tabs-left min-height400">
																	<li class="commonLi active cm-advanced-public-conf-title">
																		<a href="#tab_script_before" class="popovers pl0_en pr0_en" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
																			${LANG.UI_PLATFORM_RECOVERY_SCRIPTS_AFTER}
																		</a>
																	</li>
																</ul>
															</div>
															<div class="col-md-10 col-sm-9 col-xs-9" style="left: -13px;">
																<div class="tab-content">
																	<div class="${timepointInfo.timepoint_uuid}_script_recover" style="margin-left: 7px;"></div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>`;


		$(".targetBox").append(DomHtml);

		

		//初始化引导模式
		$("#"+timepointInfo.timepoint_uuid+"_boot_mode_id").val(timepointInfo.system_boot_type);
		//初始化单选框
		$('input[name="'+timepointInfo.timepoint_uuid+'_boot_media"]').iCheck({
			radioClass: 'iradio_square-blue'
		});
		// 初始化 icheck 复选框
		$("#" + timepointInfo.timepoint_uuid + "_continueCheck").iCheck({
			checkboxClass: 'icheckbox_square-blue'
		});


		var changeSelectFunc = function(showType){
			//清空
			$("#"+timepointInfo.timepoint_uuid+"_ipselect").empty().off();
			let ipOption = `<option value="">`+ LANG.UI_MACHINE_OS_PLEASE_SELECT +`</option>`
			for (let i = 0; i < iplist.length; i++) {
				let ipInfo = iplist[i];
				//是否是离线状态
				let online_flag = ipInfo.online_flag;
				//是否是在任务中状态
				let in_task = ipInfo.in_task;
				//回去任务中名字
				let task_name = ipInfo.task_name;
				if(ipInfo.agent_type == showType){
					//如果是离线状态不可选
					ipOption += `<option value_task_name="${task_name}" value_online="${online_flag}" value_in_task="${in_task}" value="${ipInfo.agent_uuid}">${ipInfo.agent_name}</option>`;
				}
			}
			$("#"+timepointInfo.timepoint_uuid+"_ipselect").append(ipOption);
			$("#"+timepointInfo.timepoint_uuid+"_ipselect").select2({
				placeholder: LANG.UI_JOB_SELECT,
				language: {
					noResults: function() {
						return LANG.UI_RECOVERY_CLIENT_SERARCH_TIPS; // 自定义提示文本
					},
					// 搜索框的提示文字（新增这个配置）
					searching: function() {
						return LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS; // 您的自定义提示文本
					}
				}
				//allowClear: true // 允许清空选择
			}).on('select2:open', function() {
				// 使用 this 找到当前 select2 实例对应的 dropdown
				const $dropdown = $(this).data('select2').$dropdown;
				// 定位到当前下拉中的搜索框
				const $searchField = $dropdown.find('.select2-search__field');
	
				// 获取搜索框并设置placeholder
				$searchField.attr('placeholder', LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS);
	
				// 延迟聚焦（等待 DOM 更新）
				setTimeout(function () {
					if ($searchField.length) {
						const nativeInput = $searchField[0]; // 获取原生 DOM 元素
						nativeInput.focus();
					}
				}, 200); // 可根据需要调整延迟时间
			});

			//再次初始化change事件
			bindChangeEvents(timepointInfo.timepoint_uuid);

			//切换后需要重新检测
			$("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr('status','false');
			
		}

		// 添加切换事件
		$('input[name="'+timepointInfo.timepoint_uuid+'_boot_media"]').on('ifChecked ', function(event) {
			// 获取当前选中的单选框的值
			var selectedValue = $(this).val();
			if(selectedValue ==2){
				//切换自动部署的时候需要输入密码
				bootbox.prompt({ 
					title: '<span style="color:red;">'+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_1+', <strong>'+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_2+'</strong>, '+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_3+'. </span>'+LANG.UI_MACHINE_OS_AUTO_DEPLOY_WARN_TIP_4, 
					inputType: 'password',
					callback: debounce(function (pwdResult) {
						if(pwdResult == ""){
							UIToastr.showWarning(LANG.UI_MACHINE_OS_TIMEPOINT_PWD, LANG.UI_MACHINE_OS_ENTER_PASSWORD_TO_CONFIRM);
							return false;
						}
						if(pwdResult == null) {
							// 如果用户取消输入密码，恢复之前选中的单选按钮状态
							$('input[name="' + timepointInfo.timepoint_uuid + '_boot_media"][value="' + 1 + '"]').iCheck('check');
							changeSelectFunc(2)
							return;
						};
						//获取密码
						let password = hex_md5(pwdResult);
						let requestList = {};
						let getPassword = "";
						//获取密码是否正确
						var requestData  = function(data){
							if(data.success){
								getPassword = data.data.password;
							}
						}
						//初始化备份源
						pAjaxRequest(requestList, '/api/v1/users/password', "GET", requestData, false);
						if(getPassword == password){
							UIToastr.showSuccess(LANG.UI_MACHINE_OS_AUTO_DEPLOY, LANG.UI_MACHINE_OS_PASSWORD_VERIFY_SUCCESS);
							changeSelectFunc(1)
							$(this).modal('hide');
							return true;
						}else{
							UIToastr.showWarning(LANG.UI_MACHINE_OS_AUTO_DEPLOY, LANG.UI_MACHINE_OS_PASSWORD_VERIFY_FAILED2);
							changeSelectFunc(2)
							return false;
						}
					},300,false)
				});
			}else{
				changeSelectFunc(2)	
			}
			$(".changeDisplay").hide();
		});
	
	


		var bindChangeEvents = function(timepoint_uuid) {
			$("#" + timepoint_uuid + "_ipselect").on('change', function() {
				// 获取几个状态进行判断
				// 获取离线状态
				let online_flag = $("#" + timepoint_uuid + "_ipselect").find("option:selected").attr('value_online');
				// 获取任务中状态
				let in_task = $("#" + timepoint_uuid + "_ipselect").find("option:selected").attr('value_in_task');
				// 获取任务名称
				let task_name = $("#" + timepoint_uuid + "_ipselect").find("option:selected").attr('value_task_name');
				// 如果是离线状态或任务中则禁止选择并给出提示(注意该值非布尔类型)
				if (online_flag == "false") {
					UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_MACHINE_OS_CANNOT_SELECT_OFFLINE_HOST);
					// 取消选择
					$("#" + timepoint_uuid + "_ipselect").val("");
					return;
				}
				if (in_task == "true") {
					UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, LANG.UI_MACHINE_OS_CANNOT_SELECT_HOST_IN_TASK + "'" + task_name + "'" + LANG.UI_MACHINE_OS_TASK);
					// 取消选择
					$("#" + timepoint_uuid + "_ipselect").val("");
					return;
				}
				// 选择后改变检测状态
				// 获取恢复目的地的值
				let ipselectValue = $("#" + timepoint_uuid + "_ipselect").val();
				// 获取检测里面的属性status_agent_uuid的值
				$("#" + timepoint_uuid + "_linkChecked").attr("status_agent_uuid");
				$("#" + timepoint_uuid + "_linkChecked").attr("status", "false");
				// 当切换选项后就取消勾选i标签
				$("#" + timepoint_uuid + "_linkChecked").find("i").remove();
		
				// 当ipselect事件改变后获取所有.ipselect选中的ip然后禁用
				// 获取所有 class 为 ipselect 的选择框
				var ipSelects = $('.ipselect');
				var selectValueList = [];
		
				// 遍历每个选择框,使用for的方式
				for (var i = 0; i < ipSelects.length; i++) {
					// 获取当前选择框的值
					var selectValue = $(ipSelects[i]).val();
					// 将值添加到数组中
					selectValueList.push(selectValue);
				}
				// 遍历所有.ipselect选择框
				ipSelects.each(function() {
					// 获取当前选中的值
					var selectValue = $(this).val();
					// 获取排除当前选中值的所有option对象集合
					var optionList = $(this).find('option').not($(this).find('option[value="' + selectValue + '"]'));
					// 遍历optionList
					optionList.each(function() {
						// 获取当前option的值
						var optionValue = $(this).val();
						if (selectValueList.includes(optionValue) && optionValue != "") {
							$(this).attr('disabled', true);
						} else {
							$(this).attr('disabled', false);
						}
					});
				});
			});
		};



		//初始化恢复目的地
		//循环iplist的数据添加option
		let clickedValue =$('[name="recoverType"]').filter('.active').attr('value');
		for (let i = 0; i < iplist.length; i++) {
			let ipInfo = iplist[i];
			//是否是离线状态
			let online_flag = ipInfo.online_flag;
			//是否是在任务中状态
			let in_task = ipInfo.in_task;
			//网络模式
			let net_model = ipInfo.net_model;
			//回去任务中名字
			let task_name = ipInfo.task_name;
			if(ipInfo.agent_type == 2 && clickedValue == 'completeMachineRecovery'){
				//如果是离线状态不可选
				// 选windows时间点，目标机可选livecd或winpe
				// 选linux时间点，目标机可选livecd
				if(timepointInfo.os_type == 2 && ipInfo.os_type == "Windows"){
					continue;
				}
				let ipOption = `<option value_task_name="${task_name}" value_net_model="${net_model}" value_online="${online_flag}" value_in_task="${in_task}" value="${ipInfo.agent_uuid}">${ipInfo.agent_name}</option>`;
				$("#"+timepointInfo.timepoint_uuid+"_ipselect").append(ipOption);
			}else if(ipInfo.agent_type != 2 && clickedValue == 'dataVolumeRecovery'){
				let ipOption = `<option value_task_name="${task_name}" value_net_model="${net_model}" value_online="${online_flag}" value_in_task="${in_task}" value="${ipInfo.agent_uuid}">${ipInfo.agent_name}</option>`;
				$("#"+timepointInfo.timepoint_uuid+"_ipselect").append(ipOption);
			}
			$("#"+timepointInfo.timepoint_uuid+"_ipselect").select2({
				placeholder: LANG.UI_JOB_SELECT,
				language: {
					noResults: function() {
						return LANG.UI_RECOVERY_CLIENT_SERARCH_TIPS; // 自定义提示文本
					},
					// 搜索框的提示文字（新增这个配置）
					searching: function() {
						return LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS; // 您的自定义提示文本
					}
				}
				//allowClear: true // 允许清空选择
			}).on('select2:open', function() {
				// 使用 this 找到当前 select2 实例对应的 dropdown
				const $dropdown = $(this).data('select2').$dropdown;
				// 定位到当前下拉中的搜索框
				const $searchField = $dropdown.find('.select2-search__field');
	
				// 获取搜索框并设置placeholder
				$searchField.attr('placeholder', LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS);
	
				// 延迟聚焦（等待 DOM 更新）
				setTimeout(function () {
					if ($searchField.length) {
						const nativeInput = $searchField[0]; // 获取原生 DOM 元素
						nativeInput.focus();
					}
				}, 200); // 可根据需要调整延迟时间
			});
			
		}
		//选择恢复目的后初始化选择事件
		//监听所有class为ipselect的change事件
		bindChangeEvents(timepointInfo.timepoint_uuid);
		

		//初始化重置主机名事件
		$("#"+timepointInfo.timepoint_uuid+"_rename_check").on('switchChange.bootstrapSwitch', function(event, state) {
			
			if (state) {
				//显示_rename_id
				$("."+timepointInfo.timepoint_uuid+"_rename_id").show();
			}else{
				$("."+timepointInfo.timepoint_uuid+"_rename_id").hide();
			}
		})

		//初始化poppvers
		$(".targetBox").find('.popovers').popover();
		//初始化checkbox
		$('.make-switch').bootstrapSwitch();




		// 初始化检测插件
		if(clickedValue == 'completeMachineRecovery'){
			//整机恢复要做驱动检测
			$.fn.driverCheck.init($("#"+timepointInfo.timepoint_uuid+"_driverCheck"), {
				width: {
					config_label: '',
					config_content: 'col-md-12',
				},
				getCheckObject: () => {
					return {
						source_list: [{
							name: timepointInfo.host_name,
							timepoint_uuid: timepointInfo.timepoint_uuid,
							module_type: CONF.MODULE_TYPE.OS,
						}],
						target_info: {
							target_type: $.fn.driverCheck.DRIVER_TARGET_TYPE.CLIENT,
							client_info: {
								agent_uuid: $("#"+timepointInfo.timepoint_uuid+"_ipselect").val(),
							},
						}
					};
				},
				afterCheck:function (result, data){
					//这里需要获取驱动检测的返回值传给后端
					driverData[timepointInfo.timepoint_uuid] = data[0].driver_hw_id_map;
					// 根据返回结果为true的时候显示下面的虚拟机信息
					// 根据返回结果为true的时候显示下面的虚拟机信息
					// console.log('driver_data',result, data)
					// 隐藏是否继续恢复
					$("#"+timepointInfo.timepoint_uuid+"_driverCheckFailContinue").hide();
					if (result) {
						initCheckedSuccess();
					} else {
						$("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status", "true");
						// 有个特殊处理，如果是异构平台，用户可决定是否继续恢复
						if (data[0].platform_type == 2) {
							// 显示是否继续恢复
							$("#"+timepointInfo.timepoint_uuid+"_driverCheckFailContinue").show();
							 // 绑定 ifChanged 事件
							$("#" + timepointInfo.timepoint_uuid + "_continueCheck").on('ifChanged', function(event) {
								if ($(this).is(':checked')) {
									initCheckedSuccess();
								} else {
									// 复选框未被勾选时的操作
									//修改检测状态为status为true
									$("#"+timepointInfo.timepoint_uuid+"_sourceConfigDiv").hide();
									return;
								}
							});
							
						}
					}

				
				}
			});
		}

		$("#"+timepointInfo.timepoint_uuid+"_linkChecked").on('click', () => {
			//当检测的时候隐藏检测驱动并且置为不选中
			$("#"+timepointInfo.timepoint_uuid+"_driverCheckFailContinue").hide();
			$("#"+timepointInfo.timepoint_uuid+"_sourceConfigDiv").hide();
			
			$(".changeDisplay").show();
			//取消选中
			$("#"+timepointInfo.timepoint_uuid+"_continueCheck").iCheck('uncheck');


			//初始化传输网络信息
			var nodes_uuid = timepointInfo.real_node_uuid;
			//获取恢复目的地的net_model
			// 获取恢复目的地选择的option的net_model
			let net_model = $("#"+timepointInfo.timepoint_uuid+"_ipselect").find("option:selected").attr("value_net_model");
			// let net_model = $("#"+timepointInfo.timepoint_uuid+"_ipselect").attr("net_model");
			if(net_model == 1){
				//如果为客户端连接服务端, 则没有传输网络
				$("."+timepointInfo.timepoint_uuid+"_transfernetworkDiv").hide();
				//给其赋值为2
				$("#"+timepointInfo.timepoint_uuid+"_transferNetworkTree").attr('net_model',1);
			}else{
				$("."+timepointInfo.timepoint_uuid+"_transfernetworkDiv").show();
				//给其赋值为2
				$("#"+timepointInfo.timepoint_uuid+"_transferNetworkTree").attr('net_model',2);
				//初始化传输网络
				$("#"+timepointInfo.timepoint_uuid+"_transferNetworkTree").transferNetwork({node_uuid: nodes_uuid});
			}

			//获取恢复目的地的值
			let agent_uuid = $("#"+timepointInfo.timepoint_uuid+"_ipselect").val();
			if(agent_uuid == ""){
				UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET,LANG.UI_TENANT_SELECT_RECOVERY_DES);
				return;
			}



			//初始化内容后初始化驱动检测插件
			if(clickedValue == 'completeMachineRecovery'){
				$.fn.driverCheck.check($("#"+timepointInfo.timepoint_uuid+"_driverCheck"));
			}else{
				initCheckedSuccess();
			}
			
		});

		var initCheckedSuccess = () =>{
			var getUuid = function () {
				var len = 36;//36长度
				var radix = 16;//16进制
				var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
				var uuid = [], i;
				radix = radix || chars.length;
				if (len) {
					for (i = 0; i < len; i++) {
						uuid[i] = chars[0 | Math.random() * radix];
					}
				} else {
					var r;
					uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
					uuid[14] = '4';
					for (i = 0; i < 36; i++) {
						if (!uuid[i]) {
							r = 0 | Math.random() * 16;
							uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
						}
					}
				}
				return uuid.join('');
			}
			let agent_uuid = $("#"+timepointInfo.timepoint_uuid+"_ipselect").val();
			//初始化恢复源
			$("#"+timepointInfo.timepoint_uuid+"_recover_target_id").html("");
			// 获取被点击的标签的 value 值
			let clickedValue =$('[name="recoverType"]').filter('.active').attr('value');
			$("#"+timepointInfo.timepoint_uuid+"_recover_target_id").initTargetPlug({
				type: 1, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
				origin_task_uuid: "", //源任务uuid(实时,定时可不传或为空)
				origin_agent_uuid: "", //源代理主机uuid(实时,定时可不传或为空)
				origin_timepoint_uuid: timepointInfo.timepoint_uuid, //源时间点uuid或时间点(实时,定时)
				origin_os_type: timepointInfo.os_type == 1 ? "Windows" : "Linux",
				target_agent_uuid: agent_uuid, //目标代理主机uuid(目标端主机)
				target_visible_flag: true,  //控制恢复目标的列是否显示 true显示,false不显示
				recover_type: clickedValue == 'completeMachineRecovery' ? true : false, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
				target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
				target_input_flag:false, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
				target_input_min: -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
				origin_data_array:[], //前端源信息,当origin_data_flag为true时使用这个数据
				nocheck: false, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
				loading:'.targetBox',
				first_column_name:LANG.UI_MACHINE_OS_SOURCE_DISK_NAME,
				second_column_name:LANG.UI_PUBLIC_TOTAL_SIZE,
				third_column_name:LANG.UI_RECOVERY_GOAL,
			});
			
			var getNetwork = function(){

				var requestData  = function(data){
					if(data.success){
						//获取数据
						var networkData = data.data;
						//获取数据的各个值
						let agent_uuid = networkData.agent_uuid;
						var agent_name = networkData.agent_name;
						var hostname = networkData.hostname;
						var os_type = networkData.os_type;
						var agent_type = networkData.agent_type;
						var ip = networkData.ip;
						var nic_list = networkData.nic_list;
						hostNameLimit =  networkData.host_name_limit;
						

						//填写主机名
						$("#"+timepointInfo.timepoint_uuid+"_rename_id").val(hostname)
						// 初始化网络配置插件结构体
						var networkInit = [];
						
						//遍历nic_list数组
						for (var i = 0; i < nic_list.length; i++) {
							var eachNetwork = {};
							eachNetwork.checked = true;
							eachNetwork.chk_disabled = false;
							eachNetwork.net_name = nic_list[i]['name'];
							eachNetwork.mac_address = nic_list[i]['mac_address'];
							eachNetwork.net_mac = "";
							eachNetwork.expand_advance_flag = false;
							eachNetwork.change_net_flag = false;
							eachNetwork.ipv4_set = {};
							eachNetwork.ipv6_set = {};
							//获取ip类型
							let ip_set = nic_list[i]['ip_set'];
							//ipv4
							eachNetwork.ipv4_set.config_type = $.fn.NetworkConfig.CONFIG_TYPE_ENUM.manual;
							eachNetwork.ipv4_set.ip_set = [];
							eachNetwork.ipv4_set.gateway = nic_list[i]['gateway_address'];
							eachNetwork.ipv4_set.dns1 = [];
							eachNetwork.ipv4_set.dns2 = [];
							//ipv6
							eachNetwork.ipv6_set.config_type = $.fn.NetworkConfig.CONFIG_TYPE_ENUM.auto;
							eachNetwork.ipv6_set.ip_set = [];
							eachNetwork.ipv6_set.gateway = nic_list[i]['ipv6_gateway_address'];
							eachNetwork.ipv6_set.dns1 = [];
							eachNetwork.ipv6_set.dns2 = [];

							for (var j = 0; j < ip_set.length; j++) {
								let eachIp_set = {};
								eachIp_set.ip = ip_set[j].ip_addr;
								eachIp_set.netmask = ip_set[j].netmask;
								if(ip_set[j].ip_type == 1){
									//ipv4
									eachNetwork.ipv4_set.ip_set.push(eachIp_set);
								}else{
									eachNetwork.ipv6_set.ip_set.push(eachIp_set);
								}
							}
							networkInit.push(eachNetwork);
						}
						$.fn.NetworkConfig.init($("#"+timepointInfo.timepoint_uuid+"_ipconfig_id"), {
							client_net_list: networkInit,
							height: {
								table_height: '400px',
							},
							show_advance_flag: true,
						}, $.fn.NetworkConfig.USAGE_TYPE_ENUM.CLIENT);
						
					}else{
						UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_HOST_NETWORK_INFO, data.message);
					}
				}
				let agent_uuid = $("#"+timepointInfo.timepoint_uuid+"_ipselect").val();
				requestparams = {'agent_uuid':agent_uuid}
				//初始化备份源
				pAjaxRequest(requestparams, '/api/v1/complete_machine_os/agent_network', "GET", requestData, true);
			}

			
			getNetwork();
		



			//初始化脚本插件
			$('.'+timepointInfo.timepoint_uuid+"_script_recover").initVinScript({class: timepointInfo.timepoint_uuid+"_script_recover"});
		
			//当检测通过后修改检测的状态
			//获取其状态属性
			$("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status", "true");
			var iTag = '<i class="levelchild viconfont vicon-a-Checkxiaoyan" style="margin-right: 5px;"></i>';
			$("#"+timepointInfo.timepoint_uuid+"_linkChecked").html(iTag + $("#"+timepointInfo.timepoint_uuid+"_linkChecked").text());

			//添加属性
			$("#"+timepointInfo.timepoint_uuid+"_linkChecked").attr("status_agent_uuid", agent_uuid);
			//显示配置项
			$("#"+timepointInfo.timepoint_uuid+"_sourceConfigDiv").show();
		}
		



		
		
	}

	//获取所有可用恢复目的地主机IP地址等等
	var getOptionIp = function(timepointInfo){
		
		Metronic.blockUI({target:$('.waitInitDiv'),animate: true});

		var requestData  = function(data){
			if(data.success){
				initTargetConfig(data.data);
				Metronic.unblockUI('.waitInitDiv');
			}else{
				UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERY_TARGET, data.message);
			}
		}
		//初始化备份源
		pAjaxRequest({}, '/api/v1/complete_machine_os/target_ip', "GET", requestData, true);

	}


	//初始化目标机配置
	var initTargetConfig = function(iplist){
		//获取勾选的时间点主机
		var selectedAgent = pointtypetree.getCheckedNodes(true);
		firstSelectTimepoint = selectedAgent; //存储这一步选择的数据用于返回上一步后再点击下一步判断是否再初始化第二步
		//先清空内容
		$(".targetBox").html("");
		Metronic.blockUI({target:".targetBox",animate: true});
		//根据勾选的时间点初始化第二步内容
		for(var i = 0; i < selectedAgent.length; i++){
			let timepointInfo = {};
			//获取时间点uuid
			timepointInfo.timepoint_uuid = selectedAgent[i].timepointuuid;
			//获取时间点名称
			timepointInfo.timepoint_name = selectedAgent[i].name;
			//获取时间点主机名
			timepointInfo.host_name = selectedAgent[i].osname;
			//获取时间点主机uuid
			timepointInfo.agent_uuid = selectedAgent[i].agentuuid;
			//获取操作系统类型
			timepointInfo.os_type = selectedAgent[i].ostype;
			//获取操作系统节点
			timepointInfo.real_node_uuid = selectedAgent[i].real_node_uuid;
			//获取引导模式
			timepointInfo.system_boot_type = selectedAgent[i].system_boot_type;
			//是否加密
			timepointInfo.encrypted_flag = selectedAgent[i].encrypted_flag;
			initTargetDom(timepointInfo, iplist);

			
			
			Metronic.unblockUI(".targetBox");
		}

	}

	

	getOptionIp();
	

}
//--------------------------------------初始化第三步内容----------------------------------------------------------------------

// 获取时间策略信息描述
var getTimeDes = function () {
	var des = '';
	des = $('#recovertype').find("option:selected").text();
	if ($('#recovertype').val() == 4) {
		des += '，' + LANG.UI_JOB_TIMING_RECOVER_TIME + "：" + $('#oncetime').val();
	}
	$('.recoveryTimeDes').html(des);
	$('.recoveryTimeDes').prop('title', des);
}
var initThird = function(){
	
	//初始化通用策略
	var initCommonStrategy = function(){
		
		var inintDatatimePicker = function () {
			//设置时间
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				//英文独有的
			   $(".form_datetime").datetimepicker({
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-mm-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
				startDate: new Date()
			   });
			}else{
			   $(".form_datetime").datetimepicker({
					 language:  'zh-CN', 
					 autoclose: true,
					 isRTL: Metronic.isRTL(),
					 format: "yyyy-MM-dd hh:ii:ss",
					 pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
					 startDate: new Date()
			   });
			}
			$('#resetdate').on('click', function () {
				$('#oncetime').val('');
				getTimeDes();
			});
			$('#oncetime').on('change', getTimeDes);
		}
	
		
	
		//初始化时间策略
		inintDatatimePicker();
		getTimeDes();
		// 选择恢复方式
		$('#recovertype').on('change',function(){
			if('1' == this.value){
			    resultData.type_info.strategy = {};
				$('.setOnceTime').hide();
			}else if("4" == this.value){
				$('.setOnceTime').show();
			}
			getTimeDes();
		});


		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
	}
	//初始化传输策略
	var initTransferStrategy = function(){
		$('#Socket-thread').spinner({value: 3, step: 1, min: 1,max: 8});//传输线程
		if(CONF.FUNCTIONS.includes('multithread')){
			//如果授权线程
			$(".transfer_threads_div").show();
		}else{
			//默认值为1
			$('#Socket-thread').spinner({value: 1, step: 1, min: 1,max: 8});//传输线程
			$(".transfer_threads_div").hide();
		}

		//加密传输l开关切换事件
		$("#encrypttransfer").on('switchChange.bootstrapSwitch', function(even,state){
			if(state){
				$(".encrypt_method_div").show();
			}else{
				$(".encrypt_method_div").hide();
			}
		})



		//初始化重试策略
		$('#retry_config').retryStrategy();
	}
	
	
	
	initCommonStrategy();
	initTransferStrategy();
	
	//增加是否都没授权的情况
	if(!CONF.FUNCTIONS.includes('virusKill') && !CONF.FUNCTIONS.includes('integrity')){
		$(".otherLi").hide();
	}
}
//--------------------------------------初始化第四步内容----------------------------------------------------------------------
var initFourth = function(){

	var requestData  = function(data){
		if(data.success){
			//获得任务名
			$("#jobname").val(data.data);
			
		}else{
			UIToastr.showWarning(LANG.UI_MACHINE_OS_GET_BACKUP_JOB_NAME, data.message);
		}
	}
	pAjaxRequest({}, "/api/v1/complete_machine_os/recover_name", "GET", requestData, true);


}


//--------------------------------------获取第一步内容----------------------------------------------------------------------
//--------------------------------------获取第二步内容----------------------------------------------------------------------
//这儿第一步和第二步一起获取数据
var getStep2Data = function(){
	//对传输网络进行排除一些不需要的字段
	var initNetworkConfig = function (network_config){
		//对传输网络进行排除一些不需要的字段
		let  network_config_new = [];
		 for(let i = 0; i < network_config.length; i++){
			let each_network_config = {};
			each_network_config.change_net_flag = network_config[i].change_net_flag;
			each_network_config.mac_address = network_config[i].mac_address;
			each_network_config.net_name = network_config[i].net_name;
			each_network_config.net_uuid =  network_config[i].net_uuid;
			each_network_config.ipv4_set =  network_config[i].ipv4_set;
			each_network_config.ipv6_set =  network_config[i].ipv6_set;
			each_network_config.source_ipv4_set =  network_config[i].source_ipv4_set;
			each_network_config.source_ipv6_set =  network_config[i].source_ipv6_set;

			 network_config_new.push(each_network_config);
 		}
		return  network_config_new;
	}



	//获取第一步勾选的数据
	var selectedAgent = pointtypetree.getCheckedNodes(true);
	//获取时间点的节点
	resultData.node_uuid = selectedAgent[0].real_node_uuid;
	//初始化忽略节点限制
	initResourceLimit([resultData.node_uuid]);
	//初始化数据结构体
	resultData.recovery_oss_info = [];
	// 获取被点击的标签的 value 值
	let clickedValue =$('[name="recoverType"]').filter('.active').attr('value');
	let hostReg = new RegExp(hostNameLimit.limit);
	resultData.machine_flag = clickedValue == "completeMachineRecovery" ? 1 :2;
	for (var i = 0; i < selectedAgent.length; i++) {
		
		//获取时间点名称
		let timepointName = selectedAgent[i].name;
		let each_recovery_oss_info = {};
		//获取时间点uuid
		each_recovery_oss_info.recovery_timepoint_uuid = selectedAgent[i].timepointuuid;
		//获取时间点主机uuid
		each_recovery_oss_info.recovery_agent_uuid = selectedAgent[i].agentuuid;
		//获取目标的agent_uuid
		each_recovery_oss_info.destination_agent_uuid = $("#"+selectedAgent[i].timepointuuid+"_ipselect").val();
		//获取目标的agent_name
		each_recovery_oss_info.destination_agent_name = $("#"+selectedAgent[i].timepointuuid+"_ipselect").find("option:selected").text();
		//获取时间点主机名
		each_recovery_oss_info.os_name = selectedAgent[i].osname;
		//获取传输网络
		//先获取其网络方式
		let network_net_model = $("#"+selectedAgent[i].timepointuuid+"_ipselect").find("option:selected").attr("value_net_model");
		// let network_net_model = $("#"+selectedAgent[i].timepointuuid+"_net_model").attr('net_model');
		if(network_net_model == 1){
			//如果为2 则没有网络方式
			each_recovery_oss_info.network_uuid = "";
			each_recovery_oss_info.network_pool_uuid = "";
			each_recovery_oss_info.network_name = "";
		}else{
			let network = $("#"+selectedAgent[i].timepointuuid+"_transferNetworkTree").transferNetwork('getSelect');
			if(!network){
				return false;
			}
			each_recovery_oss_info.network_uuid = network.network_uuid;
			each_recovery_oss_info.network_pool_uuid = network.network_pool_uuid;
			each_recovery_oss_info.network_name = network.str;
		}
		
		//获取驱动检测的值
		each_recovery_oss_info.driver_info = driverData[selectedAgent[i].timepointuuid];
		//获取时间点密码
		each_recovery_oss_info.recovery_timepoint_pwd = "";
			
		//对时间点密码进行加密
		each_recovery_oss_info.recovery_timepoint_pwd = passwordList[selectedAgent[i].timepointuuid] ?? "";
		each_recovery_oss_info.recovery_strategy = [];
		each_recovery_oss_info.exclude_recovery_dev = [];
		//获取每一个时间点目标机配置
		//获取目标卷配置------------------------------
		var eachTargetData = $("#"+selectedAgent[i].timepointuuid+"_recover_target_id").getTargetData('getEachData',selectedAgent[i].timepointuuid);
		if(!eachTargetData){
			return false;
		}
		each_recovery_oss_info.recovery_strategy = eachTargetData.target_strategy;
		each_recovery_oss_info.exclude_recovery_dev = eachTargetData.exclude_dev;

		//获取网络配置------------------------------
		each_recovery_oss_info.network_config = $.fn.NetworkConfig.getData($("#"+selectedAgent[i].timepointuuid+"_ipconfig_id"));
		if(!each_recovery_oss_info.network_config){
			return false;
		}
		each_recovery_oss_info.network_config = initNetworkConfig(each_recovery_oss_info.network_config);

		//获取高级配置------------------------------
		//获取引导模式
		each_recovery_oss_info.grub_method = $("#"+selectedAgent[i].timepointuuid+"_boot_mode_id").val();
		//获取主机重命名开关
		each_recovery_oss_info.rename_host = $("#"+selectedAgent[i].timepointuuid+"_rename_check").is(':checked');
		//获取主机重命名值
		each_recovery_oss_info.new_host_name = $("#"+selectedAgent[i].timepointuuid+"_rename_id").val();
		if (each_recovery_oss_info.rename_host && (each_recovery_oss_info.new_host_name.length > hostNameLimit.len)) {
			UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_OS_DETAILS_HOST_NAME + hostNameLimit.msg);
			return false;
		}
		if(each_recovery_oss_info.rename_host && (each_recovery_oss_info.new_host_name == "" || !hostReg.test(each_recovery_oss_info.new_host_name))){
			UIToastr.showWarning(LANG.UI_OS_DETAILS_HOST_NAME, LANG.UI_MACHINE_OS_HOSTNAME_TIPS);
			return false;
		}
		//获取恢复后执行脚本------------------------------
		each_recovery_oss_info.after_recovery_script_info = $('.'+selectedAgent[i].timepointuuid+"_script_recover").getVinScript(selectedAgent[i].timepointuuid+"_script_recover");
		if(!each_recovery_oss_info.after_recovery_script_info){
			return false;

		}
		resultData.recovery_oss_info.push(each_recovery_oss_info);

	}

	//初始化病毒库
	var initVirus = function(){
		//获取恢复的所有时间点
		var selectedAgent = pointtypetree.getCheckedNodes(true);
		let os_type = "";
		// 统计选中的操作系统类型
		let windowsCount = 0;
		let linuxCount = 0;
		
		// 遍历所有选中的时间点，统计操作系统类型
		for(var i = 0; i < selectedAgent.length; i++){
			if(selectedAgent[i].os_type == "Linux"){ // 假设1为Linux
				linuxCount++;
			} else if(selectedAgent[i].os_type == "Windows"){ // 假设2为Windows
				windowsCount++;
			}
		}
		
		// 如果同时包含Linux和Windows，则os_type为"other"
		if(linuxCount > 0 && windowsCount > 0){
			os_type = "Other";
		} else if(linuxCount > 0){
			os_type = "Linux";
		} else if(windowsCount > 0){
			os_type = "Windows";
		}
		
		//获取两个节点的病毒检测状态
		// （0 未扫描 1扫描中 2健康 3 感染）
		//未扫描状态
		let no_Scan = false;
		//扫描中状态
		let scanning = false;
		//健康
		let healthy = false;
		//感染
		let infected = false;
		let infectedButUnfinished = false;
		for(var i = 0; i < selectedAgent.length; i++){
			if(selectedAgent[i].virus_scan_status == 0){
				no_Scan = true;
			}else if(selectedAgent[i].virus_scan_status == 1){
				scanning = true;
			}else if(selectedAgent[i].virus_scan_status == 2){
				healthy = true;
			}else if(selectedAgent[i].virus_scan_status == 3){
				infected = true;
			}else if(selectedAgent[i].virus_scan_status == 4){  // 已感染但是为扫描完成
				infected = true;
				infectedButUnfinished = true;
			}
		}
		let virus_config = {
			reflected: {
				virus_scan_status: infectedButUnfinished ? $.fn.virusDefine.virus_scan_status.infected_but_unfinished : 0,  // 病毒扫描状态
			},
		};
		if(os_type != ""){
			virus_config.reflected.os_type = os_type;
		}
		$('#virusConfig').virusDetectionCover(no_Scan || scanning,healthy,infected,true,virus_config);
	}
		
	if(CONF.FUNCTIONS.includes('virusKill')){
		initVirus();
	}else{
		$("#virusConfig").hide();
	}
	
	return true;
}



//--------------------------------------获取第三步内容----------------------------------------------------------------------
var 
getStep3Data = function(){
	//获取通用策略----------------
	//恢复方式
	resultData.type_info = {};
	resultData.type_info.strategy = {};
	resultData.type_info.type = parseInt($('#recovertype').val());//恢复方式
	resultData.type_info.strategy.start_time = $('#oncetime').val(); //定时恢复时间
	resultData.type_info.strategy.type = resultData.type_info.type;
	if (resultData.type_info.type == 2 && resultData.type_info.strategy.start_time == '') {//1立即恢复  2定时恢复
		UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
		return false;
	}
	//获取通用策略----------------
	resultData.strategyInfo = $('#backupStrategyDiv').getBackupStrategy();
	//获取传输策略----------------
	resultData.transfer_strategy = {};
	//获取加密传输
	resultData.transfer_strategy.encrypt = $("#encrypttransfer").is(':checked');
	//获取加密算法
	resultData.transfer_strategy.encrypt_method = $("#encrypt_method").val();
	//获取传输线程

	resultData.thread_num = $("#transfer_threads").val();
	//获取安全策略----------------
	resultData.safe_config_strategy = {};
	//获取病毒检扫描策略
	let virus_detection = "";
	if(CONF.FUNCTIONS.includes('virusKill') ){
		virus_detection = $('#virusConfig').getVirusDetectionCover();
		if (virus_detection === false) {  // 验证病毒扫描配置内容是否符合要求
			return false;
		}
	}
	
	//获取完整性校验配置
	let completion_check = "";
	if(CONF.FUNCTIONS.includes('integrity')){
		completion_check = $('#completeConfig').getCompleteStrategyCovery();
	}
	resultData.safe_config_strategy = safeData("",virus_detection,completion_check);

	//获取高级策略----------------
	resultData.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
	if(!resultData.retry_strategy){
		return false;
	}
	//获取忽略节点资源限制
	resultData.ignore_resource_limiting_flag = $("#ignore_resource_limit").is(':checked');
	getStep4Data();
	return true;;
}



//--------------------------------------获取第四步内容----------------------------------------------------------------------

//第四步显示
var getStep4Data = function(){
	 // 恢复方式
	 let reservetypeshow = '';
	 reservetypeshow = $('.recoveryTimeDes').text();
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	//恢复信息----
	let recoveryInfoDes = "";
	//得到恢复源信息
	let recovery_oss_info = resultData.recovery_oss_info;
	for (var i = 0; i < recovery_oss_info.length; i++) {
		//获取时间点名称
		recoveryInfoDes += ">>>"+recovery_oss_info[i].os_name +'<br>';
		//目标主机
		recoveryInfoDes += LANG.UI_OS_TARGET_HOST+": "+recovery_oss_info[i].destination_agent_name+'<br>';
		//恢复磁盘
		recoveryInfoDes += LANG.UI_MACHINE_OS_RECOVERT_DISK+": ";
		for(let j=0; j < recovery_oss_info[i].recovery_strategy.length; j++){
			recoveryInfoDes += recovery_oss_info[i].recovery_strategy[j].source_name+'->'+recovery_oss_info[i].recovery_strategy[j].destination_name+"; ";
		}
		recoveryInfoDes +="<br>";

		// 获取被点击的标签的 value 值
		let clickedValue =$('[name="recoverType"]').filter('.active').attr('value');
		if(clickedValue != 'dataVolumeRecovery'){
			//获取网络名称 
			for(let j=0; j < recovery_oss_info[i].network_config.length; j++){
				recoveryInfoDes += LANG.UI_CLIENT_NETWORK_NIC_NAME+": "+recovery_oss_info[i].network_config[j].net_name+';';
			}
			recoveryInfoDes += "<br>";
		}
		//获取引导模式
		let grub_method = recovery_oss_info[i].grub_method == 1 ? "BIOS" : "EFI";
		recoveryInfoDes += LANG.UI_VM_SETTING_V2_BOOT_TYPE +": "+ grub_method +'<br>';
		//获取主机重命名开关
		let rename_host = getSwitchDes(recovery_oss_info[i].rename_host);
		if(clickedValue != 'dataVolumeRecovery'){
			if(recovery_oss_info[i].rename_host){
				recoveryInfoDes += LANG.UI_MACHINE_OS_RESET_HOSTNAME+": "+recovery_oss_info[i].new_host_name+'<br>';
			}else{
				recoveryInfoDes += LANG.UI_MACHINE_OS_RESET_HOSTNAME+": "+rename_host+'<br>';
			}
		}
		
		if(recovery_oss_info[i].network_name != ""){
			//获取传输网络
			recoveryInfoDes += LANG.UI_NODE_NETWORK_TRANSFER+": "+ recovery_oss_info[i].network_name +'<br>';
		}
		//恢复后执行脚本
		let script_info = recovery_oss_info[i].after_recovery_script_info;
		if(script_info.length == 0){
			recoveryInfoDes += LANG.UI_MACHINE_OS_EXECUTE_SCRIPT_RECOVERY+": "+LANG.UI_DB_BACKUP_UNSET+'<br>';
		}else{
			recoveryInfoDes += LANG.UI_MACHINE_OS_EXECUTE_SCRIPT_RECOVERY+": "+LANG.UI_DB_BACKUP_SET+'<br>';
		}
		$(".recovershow").html(recoveryInfoDes);
	}
	//限速策略----
	let speedlimitshowHtml = "";
	//获取selectnode选中的option的text值
	let speedInfo = resultData.strategyInfo.speedlimit.speedInfo;
	//循环speedInfo
	if(speedInfo.length == 0){
		speedlimitshowHtml = LANG.UI_PUBLIC_NOTHING
	}else{
		for(let i = 0; i < speedInfo.length; i++){
			speedlimitshowHtml += speedInfo[i].des+"<br>";
		}
	}
	$('.speedlimitshow').html(speedlimitshowHtml);
	//传输策略
	let transforshowHtml = "";
	//加密传输
	let encrypt_transfer = $("#encrypttransfer").is(':checked');
	transforshowHtml += LANG.UI_OS_BACKUP_TRANSFER_ENCRY+": "+ getSwitchDes(encrypt_transfer) +"<br>";
	if(encrypt_transfer){
		transforshowHtml += LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD+": "+ $("#encrypt_method option:selected").text() +"<br>";
	}
	//传输线程
	//传输线程
	if(CONF.FUNCTIONS.includes('multithread')){
		transforshowHtml += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM+": "+ resultData.thread_num;
	}
	$(".transfershow").html(transforshowHtml);
	//安全策略----------
	let safetyshowHtml = "";
	if(CONF.FUNCTIONS.includes('virusKill')){
		let virus_detection = $('#virusConfig').getVirusDetectionCover();
		safetyshowHtml = virus_detection.str;
	}
	if(CONF.FUNCTIONS.includes('integrity')){
		let completion_check = $('#completeConfig').getCompleteStrategyCovery();
		safetyshowHtml += completion_check.str;
	}
	$('.safeshow').html(safetyshowHtml);
	//高级配置------
	let highshowHtml = "";
	highshowHtml +=LANG.UI_MACHINE_OS_IGNORE_RESOURCE_LIMIT+": "+ getSwitchDes($("#ignore_resource_limit").is(':checked'));
	$(".highshow").html(highshowHtml);

	//未扫描备份点
	// safetyshowHtml += "未扫描备份点: "+ '???' +"<br>";
	//重试策略





}




























    var step1Valid = function(){
		//获取选中的值,至少选择一个
		var selectedAgent = pointtypetree.getCheckedNodes(true);

		//判断firstSelectTimepoint是否为空数组
		if(firstSelectTimepoint.length != 0){
			//判断是否有改变第一步,如果改变了第一步则重新初始化第一步
			//把firstSelectTimepoint数组转换成字符串
			var firstSelectTimepointStr = JSON.stringify(firstSelectTimepoint);
			//把selectedAgent数组转换成字符串
			var selectedAgentStr = JSON.stringify(selectedAgent);
			//比较两个数组是否相等
			if(firstSelectTimepointStr == selectedAgentStr){
				return true;
			}
			
		}


		
		if(selectedAgent.length == 0){
			UIToastr.showWarning(LANG.UI_RECOVERY_RESOURSE,LANG.UI_MACHINE_OS_SELECT_RECOVERY_SOURCE_TIPS);
			return false;
		}

		//complete_backup
		//循环获取每个时间点的backup_disable_flag值
		for(var i = 0; i < selectedAgent.length; i++){
			//获取每个时间点的backup_disable_flag值
			var integrity_check_flag = selectedAgent[i].integrity_check_flag;
			//判断backup_disable_flag是否为1
			if(integrity_check_flag == 1){
				complete_backup = false;
				break;
			}
		}
		//初始化完整性效验
		//初始化安全策略
		var safetyStrategy = function(){
			//初始化完整性检测
			$('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.OS,0,complete_backup);
		}
		if(CONF.FUNCTIONS.includes('integrity')){
			safetyStrategy();
		}else{
			$("#completeConfig").hide();
		}

		//初始化第二步内容
		initSecond();
		return true;
	}
    var step2Valid = function(){
		//所有时间点都必须选择一个对应的恢复目的地并且配置恢复目标
		//获取所有检测按钮的status属性
		let linkChecked = $(".linkChecked");
		for(var i = 0; i < linkChecked.length; i++){
			if(linkChecked[i].getAttribute("status") == "false"){
				UIToastr.showWarning(LANG.UI_MACHINE_OS_RECOVERT_MACHINE_CONFIG,LANG.UI_MACHINE_OS_RECOVERT_MACHINE_CONFIG_TIPS);
				return false;
			}
		}
		//检测所有的_sourceConfigDiv必须要显示出来
		let sourceConfigDiv = $(".sourceConfigDiv");
		for(var i = 0; i < sourceConfigDiv.length; i++){
			if(sourceConfigDiv[i].style.display == "none"){
				UIToastr.showWarning(LANG.UI_MACHINE_OS_TARGET_CONFIG,LANG.UI_MACHINE_OS_TARGET_CONFIG_NOT_INIT);
				return false;
			}
		}


		let getInfoResult = getStep2Data();
		if(!getInfoResult){
			return false;
		}
		//如果是数据卷恢复给出提示
		let clickedValue =$('[name="recoverType"]').filter('.active').attr('value');
		if(clickedValue == "dataVolumeRecovery"){
			UIToastr.showInfo(LANG.UI_MACHINE_OS_DATA_VOLUME_RECOVERY,LANG.UI_MACHINE_OS_DATA_VOLUME_OVERWRITE_WARN);
		}


		return true;
	}
    var step3Valid = function(){
		//获取时间策略
		



		let transfer_threads = $("#transfer_threads").val();
		//最大输入为8
		if(transfer_threads > 8 || transfer_threads < 1 || transfer_threads == ""){
			//重置为默认值
			$("#Socket-thread").spinner("value", 3);
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		let getInfoResult = getStep3Data();
		if(!getInfoResult){
			return false;
		}
		return true;
	}

	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			UIToastr.showWarning(LANG.UI_REPORT_TASKNAME, LANG.UI_MOTION_INPUT_TASKNAME);
			return;
		}
		let jobName = $.trim($("#jobname").val());
		// 输入验证
		if(!customInputValidate('string',jobName)){
			return false;
		}
		//获取任务名称
		resultData.task_name = $("#jobname").val();
		//提交数据
		Metronic.blockUI({target: "#machineOsrecovercontent",animate: true});
		var requestData  = function(data){
			Metronic.unblockUI("#machineOsrecovercontent");
			if(data.success){
				UIToastr.showSuccess(LANG.UI_MACHINE_OS_CREATE_RECOVERY_JOB, data.message);
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}else{
				UIToastr.showWarning(LANG.UI_MACHINE_OS_CREATE_RECOVERY_JOB, data.message);
			}
		}
		//初始化备份源
		pAjaxRequest(resultData, '/api/v1/complete_machine_os/create_recover_job', "POST", requestData, true);
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
//            $('.step-title', $('#machineOsrecovercontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#machineOsrecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#machineOsrecovercontent').find('.button-previous').css('visibility', 'hidden');
                $('#machineOsrecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#machineOsrecovercontent').find('.button-previous').css('visibility', 'visible');
                $('#machineOsrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#machineOsrecovercontent').find('.button-next').hide();
                $('#machineOsrecovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#machineOsrecovercontent').find('.button-next').show();
                $('#machineOsrecovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#machineOsrecovercontent').bootstrapWizard({
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
                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#machineOsrecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#machineOsrecovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#machineOsrecovercontent .button-submit').click(submit).css('visibility', 'hidden');
	};






    return {
        //main function to initiate the module
        init: function () {
            initInfo();
			wizardInit();
        },
    };
}();

jQuery(document).ready(function() {
	machineOSRecover.init();
});