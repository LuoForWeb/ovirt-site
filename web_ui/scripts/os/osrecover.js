var OSRecover = function () {
	var data = {recoverInfo:{},typeInfo:{strategy: {}}, highInfo:{}, taskName:'', strategygroupuuid: '',timepoint_password:''};
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
	var storage_type; //恢复源的存储类型

	//------
	var linkFlag = false; //链接测试 只有当为true才能通过下一步
	// 从备份数据跳转恢复页面
	const externalPointUuid = $('#externalPointUuid').val();
	const externalTaskUuid = $('#externalTaskUuid').val();
	const externalItemUuid = $('#externalItemUuid').val();

	var initListener = function(){
		$("#IPlistSelect").on('change',function(){
			linkFlag = false;
			linkInfo = "";
			$("#zoneInfo").hide();

		})
		//链接测试,测试通过了才能进行下一步 选择已有代理
		$("#linkSelectIP").on('click',function(){
			funcInputIP();
		})
		//选择恢复方式
		$('#recovertype').on('change',function(){
			if('1' == this.value){
				data.typeInfo.strategy = {};
				$('.setOnceTime').hide();
			}else if("4" == this.value){
				$('.setOnceTime').show();
			}
			initTimeStrategyDes();
		});
		//时间策略确定
		$('.each-button').on("click",eachButtonClick);

		//初始化添加限速策略模态框
		$('#addSpeedlimit').on('click', function(){
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}

		});
		//切换限速模式
        $('#speedModeType').on('change', speedModeHandler);
        //添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);
		// 传输策略---加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);
		$('#retry_config').retryStrategy();

	}
	// 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').show();
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}
	
	


	var getOptionIP = function(node){
		var data = {};
		data.agent_uuid = node.agentuuid;
		data.os_type = node.ostype;
		data.timepoint_uuid = node.timepointuuid;
		data =JSON.stringify(data);
		//得到所有可用代理主机IP
		$.post(CONF.AJAXPATH, {m:34,f:'getOptionIP',p:data}, function(datas){
			if(datas == "" || datas == null || datas == "[]"){
				UIToastr.showInfo(LANG.UI_OS_RECOVERY_HOST_NULL, LANG.UI_OS_RECOVERY_HOST_NULL_ERROR);
			}
			var p = JSON.parse(datas);
			//先清空option
			$("#IPlistSelect").empty();

			var options = "";
			for(var i=0; i<p.length; i++){
				options += "<option value='"+p[i].agent_uuid+"'>"+p[i].agent_name+"</option>";
			}
			$("#IPlistSelect").append(options);


			// var authorizationGroup = "";
			// var NoAuthorizationGroup = "";
			
			// //------
			// //添加授权分组
			// for(var i=0; i<p.length; i++){
			// 	if(p[i].type == 2){
			// 		authorizationGroup += "<option value='"+p[i].agent_uuid+"'>"+p[i].agent_name+"</option>";
			// 	}
			// }
			// for(var i=0; i<p.length; i++){
			// 	//如果是授权
			// 	if(p[i].type == 2){
			// 		$("#IPlistSelect").append('<optgroup label='+LANG.UI_OS_AUTHORIZE_HOST+'>'+authorizationGroup+'</optgroup>');
			// 		break;
			// 	}
			// }
			// //------
			// //添加未授权分组
			// for(var i=0; i<p.length; i++){
			// 	if(p[i].type == 0 || p[i].type == 1){
			// 		NoAuthorizationGroup += "<option value='"+p[i].agent_uuid+"'>"+p[i].agent_name+"</option>";
			// 	}
			// }
			// for(var i=0; i<p.length; i++){
			// 	if(p[i].type == 0 || p[i].type == 1){
			// 		$("#IPlistSelect").append('<optgroup label='+LANG.UI_OS_UNAUTHORIZE_HOST+'>'+NoAuthorizationGroup+'</optgroup>');
			// 		break
			// 	}
			// }
			//------
			//初始化插件
			initSelectIp();
			$(".selectpicker").selectpicker('refresh');
			$("#zoneInfo").hide();
		});

	}

	 //加载策略对应描述
	var initStrategyDes = function(){
		initTimeStrategyDes();
		// initHighStrategyDes();
	}

	var eachButtonClick = function(){
		var tabpane = $(this).closest('.tab-pane');
		var panelDefault = tabpane.closest('.panel-default');
		var panelHeading = panelDefault.children('.panel-heading');
		var i = panelHeading.find('i');
		//完全/增量/差异
		var setFlag = true;
		if(tabpane.hasClass('day')){
			setFlag = daySelect(tabpane);
		}else if(tabpane.hasClass('week')){
			setFlag = weekSelect(tabpane);
		}else if(tabpane.hasClass('month')){
			setFlag = monthSelect(tabpane);
		}else if(tabpane.hasClass('global')){
			setFlag = globalSelect(tabpane);
		}
		if(setFlag){
			i.prop('class', 'fa fa-check');
			$('#recover').collapse('hide');
		}else{
			i.prop('class', '');
		}
	}

	//每天
	var daySelect = function(tabpane){
		var info = {};
		info = getStrategyEach(CONF.STRATEGY_TYPE.DAY, tabpane);

		return setStrategyInfo(info);
	}
	//每周
	var weekSelect = function(tabpane){
		var info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.WEEK, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.WEEK, tabpane);
		if(!flagDay) return false;
		return setStrategyInfo(info);
	}
	//每月
	var monthSelect = function(tabpane){
		var info = {}, days = [];
		info = getStrategyEach(CONF.STRATEGY_TYPE.MONTH, tabpane);
		info.days = getDays(CONF.STRATEGY_TYPE.MONTH, tabpane);
		if(!flagDay) return false;
		return setStrategyInfo(info);
	}

	//得到strategy_each信息
	var getStrategyEach = function(type, tabpane){
		var info = {};
		//type:每天/每周/每月
		info.type = type;
		info.startTime = tabpane.find('.starttime').val();
		info.rollFlag = tabpane.find('.make-switch').get(0).checked;
		info.rollInterval = tabpane.find('.rolltime').val();
		info.endTime = tabpane.find('.endtime').val();
		
		return info;
	}
	var setStrategyInfo = function(info){
		data.typeInfo.strategy = info;
		return true;
	}
	var getDays = function(type, tabpane){
		var days = [];
		var input;
		if(type == CONF.STRATEGY_TYPE.WEEK){
			input = tabpane.find('.weekcheck').find('input');
		}else if(type == CONF.STRATEGY_TYPE.MONTH){
			input = tabpane.find('.monthcheck').find('input');
		}else{
			return days;
		}
		flagDay = false;
		input.each(function(i, d){
			if(d.checked){
				days[i] = 1;
				flagDay = true;
			}else{
				days[i] = 0;
			}
		});
		
		if(!flagDay){
			if(type == CONF.STRATEGY_TYPE.WEEK){
				$('.selectweektip').html(LANG.UI_STRATEGY_SELECT_WEEK).show();
			}else if(type == CONF.STRATEGY_TYPE.MONTH){
				$('.selectmonthtip').html(LANG.UI_STRATEGY_SELECT_DATE).show();
			}
			setStrategyInfo({});
		}else{
			$('.selectweektip').hide();
			$('.selectmonthtip').hide();
		}
		
		return days;
	}
	//全局
	var globalSelect = function(tabpane, type){
		
	}

	//初始化高级策略
	var initHighStrategyDes = function(){
		des = "";
		var osThreadNum = $("#osThreadNum").val();
		des +=LANG.UI_GLOBAL_STRATEGY_THREAD_NUM+": " + osThreadNum
		$('.highDes').html(des);
		$('.highDes').prop('title', des);
	}

	//初始化时间策略描述
	var initTimeStrategyDes = function(){
        var des = "";
		des = $('#recovertype').find("option:selected").text();
		if ($('#recovertype').val() == 4) {
			des += '，' + LANG.UI_JOB_TIMING_RECOVER_TIME + "：" + $('#oncetime').val();
		}
		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').prop('title', des);
	}

	//当选择添加代理时的链接测试
	//
	var funcInputIP = function(){
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
		Metronic.blockUI({target: '#tab2',animate: true});
		$.post(CONF.AJAXPATH, {m:34,f:'linkTest',p:data}, function(datas){
			var p = JSON.parse(datas);
			linkInfo = p;
			if(p.re){
				encry_flag = p.encrypt_flag;
				if(encry_flag == 1){
					$(".otherLi").show();
				}else{
					$(".otherLi").hide();
				}
				UIToastr.showSuccess(LANG.UI_OS_RECOVERY_LINK_TEST, LANG.UI_OS_RECOVERY_LINK_TEST_SUCCES);
				linkFlag = true;
				//是否需要显示传输网络标记
				networkFlag = false;
				networkFlag = getNetworkFlag(p.newList);
				//初始化传输网络
				if(networkFlag){
					$('.transfernetworkDiv').show();
					initNetworkList();
				}else{
					$('.transfernetworkDiv').hide();
				}
				//
				$("#osTable").osRecoveryConfig(p);
				$("#zoneInfo").show();

				Metronic.unblockUI('#tab2');
			}else{
				UIToastr.showWarning(p.title, p.msg);
				Metronic.unblockUI('#tab2');
			}
		});
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

        });
        speedList.push(info);
        $('#speedlimitModal').modal('hide');

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


	var checkTreeNodeInfo = function(zNodes){
		if(zNodes == "[]"){
			$("#nopointtips").show();
			$('#pointtypetree').hide();
			$(".os_tree").hide();
			return false;
		}else{
			$("#nopointtips").hide();
			$('#pointtypetree').show();
			$(".os_tree").show();
			return true;
		}
	}

	//初始化时间点树
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
					beforeClick: osnodeSelect,
					onCheck: timepointOnCheck,
					beforeExpand: nodeExpand
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
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, JSON.parse(zNodes));
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

	/**
     * 添加时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
	const addPointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.type !== 2 && treeNode.type !== 3) {//不是时间点
            return;
        }
        if ($(`#${treeNode.tId}_${treeNode.timepointuuid}`).length) {
            return;
        }
        let sObj = $(`#${treeNode.tId}_span`);
        sObj.after(`<span id="${treeNode.tId}_${treeNode.timepointuuid}"><i class='viconfont vicon-Frame11'></i></span>`);
        // 注册点击事件
        $(`#${treeNode.tId}_${treeNode.timepointuuid}`).on("click", (ev) => {
            ev.stopPropagation();  // 阻止click事件向上冒泡
            $('.page-content').initPointDetailDrawer({timepoint_uuid: treeNode.timepointuuid});
        });
    };

	/**
     * 移除时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
    const removePointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.type !== 3 && treeNode.type !== 4) {//不是时间点
            return;
        }
        $(`#${treeNode.tId}_${treeNode.point_uuid}`).off().remove();
    };


	var initPointTree = function() {
		var data = {};
		data.storageuuid = $('#storageselect').val();
		data.recoverflag = true;
		data.dataflag = false;
		var setFunction = setPointTree;
		data = JSON.stringify(data);
		Metronic.blockUI({target: '.os_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:34,f:'getTimepointTree',p:data}, setFunction);
	};



	//选择时间点节点事件绑定
	var osnodeSelect = function(treeId, treeNode, clickFlag){
		//先判断是否是在任务中 ,如果在任务中则提示并禁止继续执行
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
	var getSyncVcenterInfo = function(treeId, treeNode, refreshFlag, expendFlag, chooseFlag = false){
		var storageuuid = $('#storageselect').val();
		var div = "#pointtypetree";
		var p = {taskuuid:treeNode.taskuuid, agentuuid:treeNode.agentuuid,recoverflag:true,dataflag:false,
				refresh:refreshFlag, storageuuid: storageuuid};
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
					// 从备份数据跳转恢复页面，匹配时间点并勾选
					let targetNode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam('id', externalPointUuid);
					if(targetNode && chooseFlag){
						$.fn.zTree.getZTreeObj(treeId).checkNode(targetNode, true, false, false);
						// 添加右侧选择列表
						addPointList(treeId, targetNode)
					}
	        	}else{
	        		OPREL(data);
	        	}
	        }
		});
	}




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

	var getRnameDivs = function(value){
		var divStr = "<div class=\"input-icon right mb15\">" +
						"<i class=\"fa\"></i>" +
						"<input type=\"text\" class=\"form-control rnames\" value=\"" + clearString(value, false) + "\" name=\"vmname\"/>" +
					 "</div>";
		return divStr;
	}


	//判断是否在一个备份节点上
	var checkSelectInOneNode = function(flag, tree, node, allNodes, checkTypeFlag){
		if(!flag) return true;
		var showType = 1;
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
			OSTYPE = node.ostype;
			//初始化时间点框
			getOptionIP(node);

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

	//
	var timepointOnCheck = function(e, id, node){
//		checkHypervisorPoint(id, node);
		storage_type = node.storage_type; //磁带判断恢复源存储类型
		var flag = node.checked;
		var allNodes = pointtypetree.getCheckedNodes(true);
		//用于判断是否在同一个节点上
		if(!checkSelectInOneNode(flag, pointtypetree, node, allNodes, false)){
			$('#VMGroupList li').remove();
			$('#timepointGroupList li').remove();
			addPointList(id, node);
			return;
		}

		//清除所有已选择的时间点/如果后面要做成多选注释即可
		for(var i = 0; i < allNodes.length; i++){
			var liId = clearString(id+allNodes[i].agentuuid + allNodes[i].id + allNodes[i].dbuuid, false); //添加每列ID
			pointtypetree.checkNode(allNodes[i], false, false, false);
			$('#' + escapeJquery(liId)).remove();
		}
		pointtypetree.checkNode(node, true, false, false);

		pointtypetree.checkNode(node, flag, false, false);
		addPointList(id, node);

	}


	//判断后台传入过来的时间格式是否为xxxx-xx-xx xx:xx:xx,如果不是就不设置时间开始和结束
	function checkDate(dateStr){
        var a = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/
        if (!a.test(dateStr)) {
            return false;
        }else{
             return true;
        }
	}

	//时间戳转换为年月日,传入的为date()
	var timestampTotime = function(new_date){
		var y = new_date.getFullYear();
		var m = new_date.getMonth()+1;
		var d = new_date.getDate();
		var h = new_date.getHours();
		var mm = new_date.getMinutes();
		var s = new_date.getSeconds();
		return y+'-'+m+'-'+d+' '+h+':'+mm+':'+s;
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
//            $('.step-title', $('#osrecovercontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#osrecovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#osrecovercontent').find('.button-previous').css('visibility', 'hidden');
                $('#osrecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#osrecovercontent').find('.button-previous').css('visibility', 'visible');
                $('#osrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#osrecovercontent').find('.button-next').hide();
                $('#osrecovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#osrecovercontent').find('.button-next').show();
                $('#osrecovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#osrecovercontent').bootstrapWizard({
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
                $('#osrecovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#osrecovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#osrecovercontent .button-submit').click(submit).css('visibility', 'hidden');
	};


	var step1Valid = function(){
		var nodes = currentTree.getCheckedNodes();

		if(!nodes.length){
			$(".selecttimepointtip").html(LANG.UI_RECOVERY_SELECT_POINT).show();
			// 动态设置tab-pane的高度
			$(".tab-pane__row").css('height', 'calc(100% - 80px)');
			return false;
		}

		timepointNode = nodes[0].real_node_uuid ? nodes[0].real_node_uuid: nodes[0].nodeuuid;
		//得到数据
		//得到确认配置描述
		showStep1();

		return true;
	}

//	//这里是需要在选择目的地节点后再初始化.
//	var getTaskName = function(){
//		var info = {};
//		info = JSON.stringify(info);
//		$.post(CONF.AJAXPATH, {m:CONF.M.DBPROTECT,f:'getDBRecoveryTaskName',p:info}, function(d){
//			$('#jobname').val(d);
//		});
//	}

	var showStep1 = function(nodes){
		$.post(CONF.AJAXPATH, {m:34,f:'getOSRecoverTaskName',p:{}}, function(d){
			$('#jobname').val(d);
		});
		
		if (storage_type == CONF.BD_STORAGE_TYPE.TAPE || !CONF.FUNCTIONS.includes('multithread')) {
			//磁带只支持单线程，不显示线程配置
            $('#osThreadNum').val(1);
			$('.threadDiv').hide();
        } else {
			$('#osThreadNum').val(3);
			$('.threadDiv').show();
		}
//		var str = '';
//
//		$('.vmtypeshow').html(str);
	}

	//检查路径格式
	var checkPath = function(value, flag){
		var value = value.replace(/\//gi, "/");//正则替换  把输入的\替换成/

		var re1='((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])';	// IPv4 IP Address 1
		var re2='(:)';	// Any Single Character 1
		var re3='((?:\\/[\\w\\.\\-]+)+)';	// Unix Path 1
		var re4 = '(.*)';
		var path = /^([a-zA-Z]:|\\\\[^\\\\/:*?"<>|]+)(\\[^\\\\/:*?"<>|]+)*\\?$/;
		var linux_path =  '^\\/(\\w+\\/?)+$';//linux路径检测
		var cn_word = '[\u4e00-\u9fa5]';//中文检测
		var p1 = new RegExp(re1+re2+re3,["i"]);
		var p2 = new RegExp(re4+re2+re3, ["i"]);
		var p3 = new RegExp(linux_path);
		var p4 = new RegExp(cn_word,["g"]);
		if(flag == 'Linux'){//如果为linux系统则只能输入linux系统目录
			return !!(p3.exec(value) && !p4.exec(value))
		}else if(flag == 'Windows'){
			return !!(p2.exec(value) && !p4.exec(value))
		}
		 return !!((p2.exec(value) || p3.exec(value)) && !p4.exec(value));

	}



	var step2Valid = function(){
		//检查是否链接测试成功
		if(!linkFlag){
			UIToastr.showWarning(LANG.UI_OS_RECOVERY_LINK_TEST, LANG.UI_OS_TEST_SUCCESS_TIP);
			return false;
		}
		var dataList = $("#osTable").getosRecoveryData(linkInfo);
		if(!dataList){
			return false;
		}
		data.recoverInfo = dataList.recovery_oss_info;
		//初始化第三步恢复方式的一些显示
		initStrategyDes();
		//这个表格暂时不用 后面需要可打印在html上查看
//		showStep2(dataList.TableStr);
		showStep2(data.recoverInfo);
	}

	var showStep2 = function(data){
		var des = "";
		des += LANG.UI_JOB_TIMEPOINT_INFO+':' + data[0].os_timepoint_str + "(" + data[0].os_name + ")" + "<br>";
		des += LANG.UI_OS_TARGET_IP + data[0].destination_agent_ip + "<br>";
		des +=LANG.UI_OS_PLUG_REBUILT_VOL+ ":" + intToStr(data[0].reparted_flag) + "<br>";
		if("1" != data[0].os_type){
			des +=LANG.UI_OS_PLUG_REPAIR_DIFF_MACHINE+ ":" + intToStr(data[0].repair_linux_flag) + "<br>";
		}
		var desdetails = "";
		//得到分区信息
		if(data[0].reparted_flag == 2){
			var allocation_data = data[0].partition_allocation_strategy
		}else{
			var allocation_data = data[0].disk_allocation_strategy
		}
		//循环读取信息
		for(var i in allocation_data){
			desdetails += allocation_data[i].source_name+"("+allocation_data[i].source_sizeStr+")"+"->" + allocation_data[i].target_name+"("+allocation_data[i].target_sizeStr+")" +"<br>";
		}

		des += "" + desdetails + "<br>";
		$('.recovershow').html(des);
	}

	//int类型 1转换成开启,2转化成关闭
	var intToStr = function(data){
		var str = "";
		 if(data ==1){
			 str = LANG.UI_PUBLIC_ON
		 }else{
			 str = LANG.UI_PUBLIC_OFF
		 }
		 return str;
	}

	var step3Valid = function(){
		let password = $.trim($("#encryptVal").val());

		// 非法字符串校验
		if (isLatinCode(password)) {
			UIToastr.showWarning(LANG.UI_OS_DATA_ENCRYPT, LANG.UI_OS_PASSWORD_ERROR_TIP);
			return false;
		}

		// 密码验证
		var encrypt_Verify = verifyEncrpty();
		if(!encrypt_Verify){
			return false;
		}
		//时间策略
		data.typeInfo.type = $('#recovertype').val();//恢复方式
		data.typeInfo.strategy.startTime = $('#oncetime').val(); //定时恢复时间
		data.typeInfo.strategy.type = data.typeInfo.type;
		if (data.typeInfo.type == 4 && data.typeInfo.strategy.startTime == '') {//1立即恢复  4定时恢复
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
			return false;
		}
	    $(".reservetypeshow").html($('.recoveryTimeDes').text());
		// if('1' == data.timeInfo.type){
		// 	//得到立即恢复描述
		// 	$(".reservetypeshow").html(LANG.UI_JOB_ONCE_TIME_RECOVER);
		// }else{
		// 	var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();
		// 	data.timeInfo.strategy = strategyConfig.recInfo;
		// 	$(".reservetypeshow").html(strategyConfig.recInfo.des);
		// }

		//得到线程数量
		data.highInfo.threadnum = $('#osThreadNum').val();
		//校验传输线程
		if(data.highInfo.threadnum > 8 || data.highInfo.threadnum < 1 || data.highInfo.threadnum == ""){
			//还原默认值并给出提示
			$('#backupThreadNum').spinner('value',3);
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		data.speedInfo = speedList;//限速策略
		//得到传输加密
		data.highInfo.transfer = {};
		data.highInfo.transfer.encrypt = $('#encrypttransfer').get(0).checked; //得到传输加密
		// 传输加密算法
		data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		data.highInfo.transfer.network = $('#transferNetwork').val();


		var des = LANG.UI_COPY_BACK_ENCRYPT + ": " + getSwitchDes(data.highInfo.transfer.encrypt) + '<br>';
		// 传输加密算法
		if($('#encrypttransfer').get(0).checked){
			let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
			let method = parseInt($('#transferEncryptMethod').val());
			let grade = '';
			switch (method) {
				case 1:
					grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
					break;
				case 2:
					grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
					break;
			};
			des += encryptedMethodLabel + ": " + grade + "<br>";
		}
		//传输网络
		if(networkFlag){
			var transfernetworklabel = $('.transfernetworklabel').html();
			des += transfernetworklabel + ": " + $('#transferNetwork').find("option:selected").text() + '<br>';
//			//传输网络
//			$('.transportinfoshow').html(transferStr);
		}
		//传输线程
		var backupThreadNum = $("#osThreadNum").val();
		if(CONF.FUNCTIONS.includes('multithread')){
			des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": "+ backupThreadNum
		}
		$('.transfershow').html(des);

		//限速策略
		data.speedLimit = speedSubmitInfo();
		var speedlimitshow = $('.speedlimitshow');
		var speedLimitsStr = '';
		if (data.speedLimit.speed.length != 0) {
			speedLimitsStr = '';
			for (let i = 0; i < data.speedLimit.speed.length; i++) {
				speedLimitsStr += data.speedLimit.speed[i].des + '<br>';
			}
		}
		if (speedLimitsStr == '') {
			speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedLimitsStr);

		// 忽略节点资源限制
		data.highInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		$('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.highInfo.ignore_resource_limiting_flag));
		//重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		return true;
	}

	//验证密码
	//返回bool成功或失败
	var verifyEncrpty = function(){
		//如果不需要输入密码 则直接返回true
		if(encry_flag == 2){
			return true;
		}

		//得到用户输入的密码
		var encyptyVal = $.trim($("#encryptVal").val());
		data.timepoint_password = btoa(encyptyVal);

		// 非空校验
		if(encyptyVal == null || encyptyVal == "" || encyptyVal == undefined){
			UIToastr.showWarning(LANG.UI_MOTION_VALIDATE_ADMIN_NOT_NULL_TIPS);
			return false;
		}

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

		if(!result){
			UIToastr.showWarning(LANG.UI_OS_DATA_ENCRYPT, LANG.UI_OS_PASSWORD_ERROR_TIP);
		}
		return result;
	}

	function isLatinCode(string) {
		var latin1Regex = /[^\x00-\xFF]/;
		if(latin1Regex.test(string)){
			return true;
		}
		return false;
	}

	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}

	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
			return;
		}
		$('.jobnametip').hide();
		// 输入验证
		if(!customInputValidate('string',$("#jobname").val())){
			return false;
		}
		data.taskName = $.trim($("#jobname").val());
		if($('#strategySelect option:selected').val()){
			data.strategygroupuuid = $('#strategySelect option:selected').val();
		}
		//TODO提交
		var jsonData = JSON.stringify(data);
		Metronic.blockUI({target: '#osrecovercontent',animate: true, cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:34,f:'createOSRecoverJob',p:jsonData}, function(d){
    		Metronic.unblockUI('#osrecovercontent');
    		if(OPREL(d)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}

	// 获取设置的所有策略配置信息
	var speedSubmitInfo = function (){
		let info = {};
		info['level'] = $('#tasklevelselect').val();
		info['type'] = $('#speedtypeselect').val();
		info['speed'] = [];
		if (info['type'] == 1) {
			// 选择策略
			var selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
			if (selectedRow.length == 1) {
				info['uuid'] = selectedRow[0].uuid
				info['name'] = selectedRow[0].name
				info['strategy_type'] = selectedRow[0].type
				info['speed'] = [{'des': selectedRow[0].detail}]
			}
		} else {
			// 自定义
			info['speed'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
		}
		return info;
	}

	//节点选择改变事件
	var storageselectChange = function(){
		pointtypetreeInitFlag = false;
		initPointTree();
		$('#timepointGroupList li').remove();
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
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getStorageType',p:{}}, function(d){
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
	

	//搜索虚拟机
	var searchOS = function(){
		var value = $('#searchos').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		var checkNode =currentTree.getCheckedNodes();
		var allNode = currentTree.transformToArray(currentTree.getNodes());
		nodeParamList = currentTree.getNodesByParamFuzzy('name', value);
		currentTree.hideNodes(allNode);
		if(nodeParamList.length == 0){
			$('.os_tree').hide();
			$('#nosearchtips').show();
		}else{
			$('.os_tree').show();
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

	//初始化时间策略
	var initStrategy = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:"getTimeCrowdList", p:{}}, function(d){
			var jsonData = JSON.parse(d);
			if(jsonData.timeList.length != 0){
				$('#backupCrowd').taskCrowd({timeList:jsonData.timeList, showFlag: jsonData.showFlag});
			}
			var suggestInfo = jsonData.suggestTime;
			var strategy = [];
			strategy[0] = {
				mode: 4,
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				start_time: suggestInfo.start_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time
			};
			//延迟设置,因为这里icheck会默认修改里面的选中事件
			setTimeout(function(){
				$('#recoveryTimestrategy').strategy({dom: $('#recoveryTimestrategy'), config: strategy, backup_flag: 2});
				$('.rollDiv').hide(); //隐藏滚动执行
			}, 2000);

		});

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
		$('#backupThreadNum').spinner({value:3, step: 1, min: 1, max: 8});

	}

	// var inintDatatimePicker = function(){
	// 	$(".form_datetime").datetimepicker({
	// 		language:  'zh-CN',
	// 		autoclose: true,
	// 		showSecond: true,
    //         format: "yyyy-MM-dd hh:ii:ss",
    //         pickerPosition:"bottom-right",
	// 		minuteStep: 1
    //     });
	// }
	var inintDatatimePicker = function(){
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
		 //英文独有的
			$(".form_datetime").datetimepicker({
				autoclose: true,
				isRTL: Metronic.isRTL(),
				showSecond: true,
				format: "yyyy-mm-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			});
		}else{
			$(".form_datetime").datetimepicker({
				language:  'zh-CN',
				autoclose: true,
				showSecond: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-MM-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			});
		}
		$('#resetdate').on('click', function () {
			$('#oncetime').val('');
			initTimeStrategyDes();
		});
		$('#oncetime').on('change', initTimeStrategyDes);
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

	var threadChange = function(){
		var num = $("#osThreadNum").val();
		if(num > 8 || num < 1 || num == ""){
			//还原默认值并给出提示
			$('#backupThreadNum').spinner('value',3);
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
		}
		// initHighStrategyDes();
	}

	//获取显示传输网络标志
	var getNetworkFlag = function(agent){

		for(var i=0;i<agent.length;i++){
			//客户端连接服务端
			if(agent[i].net_model == 2){
				networkFlag = true;
			}
		}

		return networkFlag;
	}

	//初始化节点传输网络列表
	var initNetworkList = function(){
		var data = {};
		data.nodeuuid = timepointNode;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeNetworkList',p:p}, function(d){
    		var data = JSON.parse(d);
    		var transferNetwork = $('#transferNetwork');
    		transferNetwork.empty();
			for(var i=0; i<data.length; i++){
				var name = data[i].ip + ":" + data[i].port;
				if(data[i].alias_name != ""){
    				name += "(" + data[i].alias_name +")";
    			}
				var option = '<option  value="' + data[i].network_uuid + '">' + name + '</option>';
				transferNetwork.append(option);
			}

    	});
	}




    return {
        //main function to initiate the module
        init: function () {
//        	initSelectIp();//初始化下拉框
        	inintDatatimePicker();
			wizardInit();
			initStorageShowType(); //初始化存储类型展示方式和事件
        	// initPointShowType();//初始化可选节点
			initPointTree();//初始化树形结构
        	initListener();
			initSpinner();
			// initStrategy();
        },
    };
}();

jQuery(document).ready(function() {
	OSRecover.init();
});