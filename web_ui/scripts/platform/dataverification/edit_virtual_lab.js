var EditVirtualLab = function(){
	var data = {labInfo:{},hostInfo:{},network_list:{}};
	var hostTree,hostTree2;
	var networkList = [], networkNameList = [];
	var submitForm;
	var SETTINGS = null; //初始化历史配置
	var pageIndex = 0; //轮播索引
	var initStorageFlag = false,initHostFlag = false;
	var _oldNetworkList = [];
	var vcenterNetworkFlag = false;

	var initIsolatedNum;
	var renameFlag = true;
	var oldSettings;
	var currentmax = 0;
	var productNetworkList = [];
	var initNetworkList = [], initNetWorkdLists = [];
	var embNum = 1, embFlag = false;
	var firstFlag = false;
	var embList = [];
	var _REG = new RegExp(" ","g");
	var networkSettings = [];
	//初始化修改虚拟实验室信息
	var initData = function(){
		//setp1
		//虚拟实验室名信息
		data.labInfo = SETTINGS.labInfo;

		//setp2
		//选择宿主机信息
		data.hostInfo = SETTINGS.hostInfo;

		//setp3
		//隔离网络信息
		data.network_list = SETTINGS.networkInfo;

		//虚拟实验室信息
		data.virtual_lab_uuid = SETTINGS.virtual_lab_uuid;

	}

	//初始化回调事件函数
	var initListeners = function(){
		//跳转到添加虚拟化中心
		$('#toaddvcenter').on('click',function(){
	    	LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
		});
		$('#toaddvcenter2').on('click',function(){
			LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
		});

		//修改虚拟实验室名
		$('#labname').on('input propertychange', labnameChange);
		//选择配置隔离网络的生产网络
		$('#productnetwork').on('change', function(){
			var select = $('#productnetwork').selectpicker('val');
			for(var i=0;i<select.length;i++){
				for(var j=0;j<productNetworkList.length;j++){
					//如果不在
					if(productNetworkList[j].uuid == select[i] &&$.inArray(select[i], networkList) == -1){
						networkList.push(select[i]);
						var p = {};
						//增加修改虚拟实验室需要传递参数
						var info = {
							'name' : "",
							'uuid' : "",
							'netmask' : "",
							'gateway' : ""
						}
						var data = {
							'productInfo' : info,
							'isolatedInfo' : info
						}
						p.id = select[i];
						p.name = productNetworkList[j].text;
						//加载隔离网络配置卡
						initIsolatedCard(p, data);
					}
				}
			}

			//检查已经取消的网络
			for(var j=0;j<networkList.length;j++){
				//网络未选中时移除
				if($.inArray(networkList[j], select) == -1){
					var id = networkList[j];
					$('#isolatedDiv #' + id).remove();
					networkList.splice(j,1);
				}
			}
		});
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});

		//生成隔离网络
		$('#getIsolate').on('click', function(){
			var select = $('#productnetwork').selectpicker('val');
			if(embFlag){
				select = embList;
			}
			if(select.length == 0){
				UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_ADD_TIPS);
				return false;
			}

			var list = [];
			var reg = new RegExp(" ","g");
			for(var i=0;i<select.length;i++){
				var info = {};
				var id = select[i].replace(reg, '_');
				//生产网络信息
				info.name = $('#'+ id + " .productname").html();
				if(embFlag){
					info.name = "";
				}
				info.uuid = $('#'+ id).attr("data-id");
				info.netmask = $('#'+ id + " .productnetmask").val();
				info.gateway = $('#'+ id + " .productgateway").val();
				//检查生产网络信息和隔离网络信息是否填全
				if(!ipV4V6(info.netmask) || !ipV4V6(info.gateway) || info.name == ""){
					UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_ISOLATED_NETWORK_INFO, LANG.UI_VIRTUAL_LAB_INPUT_CORRECT_NETWORK);
					return false;
				}

				list.push(info);
			}

			var hypervisor = data.hostInfo.hypervisor;
			//内嵌虚拟化
			if(embFlag){
				hypervisor = 108;
			}
			var p = JSON.stringify({'list': list,'hypervisor': hypervisor, 'proxy_info': data.hostInfo.proxy_info});
			Metronic.blockUI({target: '#labContent',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE, f:"getIsolatedAutoNetwork", p:p}, function(d){
				Metronic.unblockUI('#labContent');
				var isolateList = JSON.parse(d);
				var reg = new RegExp(" ","g");
				for(var i=0;i<isolateList.length;i++){
					var id = isolateList[i].network_uuid;
					$('#'+ id + ' .newnetmask').val(isolateList[i].ip_netmask);
					$('#'+ id + ' .newgateway').val(isolateList[i].ip_gateway);
					$('#'+ id + ' .isolatedname').val(isolateList[i].network_name + " isolate");
				}
			});

		});

	}

	//修改虚拟实验室名同时其他参数名也发生变化
	var labnameChange = function(){
		var name = this.value;
		if(name == "") return;
		$('#proxyname').val(name + "_Proxy");
		$('#resourcepool').html(name + "_Pool");
		$('#folder').html(name + "_Folder");
		$('#virtualswitch').html(name + "_Switch");

	}

	//创建虚拟实验室步骤
	var wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;//总共的步骤数
            var current = index + 1;      //当前步骤
            // set wizard title
//            $('.step-title', $('#vmbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
			//把目前最大步骤存于内存中，用于判断提交按钮是否显示
			if(current >=  currentmax){
				currentmax =  current;
			}
            jQuery('li', $('#labContent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            //如果第一步 上一步按钮隐藏
            if (current == 1) {
                $('#labContent').find('.button-previous').css('visibility', 'hidden');
                $('#labContent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#labContent').find('.button-previous').css('visibility', 'visible');
                $('#labContent').find('.button-next').removeClass('next-btn-margin-left');
            }

            //如果是最后一步
            if (current >= total) {
                $('#labContent').find('.button-next').hide();
            } else {
                $('#labContent').find('.button-next').show();
            }
			//判断是否展示提交按钮
			if(current < currentmax){
				$('#labContent').find('.button-submit').css('visibility', 'hidden');
			}else{
				$('#labContent').find('.button-submit').css('visibility', 'visible');
			}
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#labContent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },

            //下一步
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
	                case 1:
	            		if(step1Valid() == false){
	            			return false;
	            		}
	            		pageIndex = 1;
	            		break;
	            	case 2:
	            		if(step2Valid() == false){
	            			return false;
	            		}
	            		pageIndex = 2;
	            		break;
	            	case 3:
	            		if(step3Valid() == false){
	            			return false;
	            		}
	            		pageIndex = 3;
	            		break;
                }
                handleTitle(tab, navigation, index);
            },

            //上一步
            onPrevious: function (tab, navigation, index) {
            	pageIndex = index;
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },

            //进度条显示
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#labContent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#labContent').find('.button-previous').css('visibility', 'hidden');
        $('#labContent .button-submit').click(submit).css('visibility', 'visible');
	};

	//检查没有宿主机切换提示信息
	var checkHostNodeInfo = function(zNodes, id){
		if(zNodes == "[]"){
			$("#nohosttips").show();
			$('.'+id).hide();
			return false;
		}else{
			$("#nohosttips").hide();
			$('.'+id).show();
			return true;
		}
	}
	//检查没有宿主机切换提示信息
	var checkHostNodeInfo2 = function(zNodes, id){
		if(zNodes == "[]"){
			$("#nohosttips2").show();
			$('.'+id).hide();
			return false;
		}else{
			$("#nohosttips2").hide();
			$('.'+id).show();
			return true;
		}
	}

	//初始化宿主机树
	var setHostTree = function(zNodes){
		// if ($('#data_source_type').val() == 2) {
		// 	setHostTree2(zNodes);
		// 	return;
		// }

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
					removeHoverDom: removeHoverDom,
				},
				callback: {
					beforeClick: hostNodeSelect,
					onCheck: hostOnCheck,
					beforeExpand: hostNodeExpand,
				}
			};
		var nodes = JSON.parse(zNodes);
		hostTree = $.fn.zTree.init($("#host_tree"), setting, nodes);
		for(var i=0;i<nodes.length;i++){
			//如果是选中的虚拟化中心，直接加载对应的宿主机树
			if(nodes[i].vcenteruuid == SETTINGS.hostInfo.vcenter_uuid){
				var selectNode = hostTree.getNodesByParam("id", nodes[i].id, null);
				hostNodeSelect('host_tree', selectNode[0], true);
			}
		}
		if ($('#data_source_type').val() == 1) {
			// vm
			$('.data-resource1').show();
			$('.data-resource2').hide();
			$('#nohosttips').hide();
		} else {
			// 内嵌
			$('.data-resource2').show();
			$('.data-resource1').hide();
		}
	};

	function setHostTree2(zNodes){
		if(!checkHostNodeInfo2(zNodes, 'host_tree_div2')) return;
		var setting = {
			check: {
				enable: true,
				chkStyle: "checkbox", // 使用复选框样式
				chkboxType: {"Y": "✓", "N": "✖"}, // 显示勾选框的样式
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true
				}
			},
			view: {
				removeHoverDom: removeHoverDom,
			},
			callback: {
				beforeClick: hostNodeSelect2,
				onCheck: hostOnCheck2,
				beforeExpand: hostNodeExpand2,
			}
		};
		var nodes = JSON.parse(zNodes);
		hostTree2 = $.fn.zTree.init($("#host_tree2"), setting, nodes);
		for(var i=0;i<nodes.length;i++){
			//如果是选中的虚拟化中心，直接加载对应的宿主机树
			if(nodes[i].vcenteruuid == SETTINGS.hostInfo.vcenter_uuid){
				var selectNode = hostTree2.getNodesByParam("id", nodes[i].id, null);
				hostNodeSelect2('host_tree2', selectNode[0], true);
			}
		}
	};

	//恢复目的分组展开
	var vcenterRefresh = function(treeId, treeNode){
		if(1 == treeNode.type){
			var data = JSON.stringify({vcenteruuid:treeNode.id, hypervisor:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:true});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({
    			type: "post",
    	        url: CONF.AJAXPATH,
    	        async:true,
    	        data:{m:CONF.M.VCENTER,f:'getSyncVcenterGroup',p:data},
    	        success: function(data){
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
    	        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
    	        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        }
    		});
		}else{
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
			str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_BACKUP_TREE_REFRESH_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>';
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

	//选择宿主机节点事件绑定
	var hostNodeSelect = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type){
			hostTree.checkNode(treeNode, !treeNode.checked, false, true);
		}else{
			hostNodeExpand(treeId, treeNode);
			hostTree.expandNode(treeNode, true);
		}
		addHoverDom(treeId, treeNode);
	}
	var hostNodeSelect2 = function(treeId, treeNode, clickFlag){
		if(2 == treeNode.type){
			hostTree2.checkNode(treeNode, !treeNode.checked, false, true);
		}else{
			hostNodeExpand2(treeId, treeNode);
			hostTree2.expandNode(treeNode, true);
		}
		addHoverDom(treeId, treeNode);
	}

	//恢复目标树统一展开
	var hostNodeExpand = function(treeId, treeNode){
		var hypervisor = treeNode.hypervisor;
		//按宿主机显示
		return getSyncHostNode(treeId, treeNode);
	}
	var hostNodeExpand2 = function(treeId, treeNode){
		//按宿主机显示
		return getSyncHostNode2(treeId, treeNode);
	}


	var hostOnCheck = function(e, id, node){
		var allNodes = hostTree.getCheckedNodes(true);
		for(var i = 0; i < allNodes.length; i++){
			hostTree.checkNode(allNodes[i], false, false, false);
		}
		hostTree.checkNode(node, true, false, false);
		//显示隐藏项目
		$('.proxySettingsDiv').show();
		//初始化宿主机存储和网络
		initNetworkAndStore(node, false);
		vcenterNetworkFlag = false;
	}

	var hostOnCheck2 = function(e, id, node){
		getHoxInfo();
		var allNodes = hostTree2.getCheckedNodes(true);
		if (allNodes.length > 0) {
			showStep2(allNodes);
		}
		if (allNodes.length == 0) {
			networkList = [];
			networkNameList = [];
			productNetworkList = [];
			initNetworkList = [];
			$('#productnetwork').empty();
			//清空选中的网络列表及配置
			$('#isolatedDiv').empty();
			$('#productnetwork').selectpicker('refresh');
			return;
		}
		if (node.checked == true) {
			// 表示选中
			if ($.inArray(node.vcuuid, initNetworkList) == -1) {
				initNetworkList.push(node.vcuuid)
				// 请求接口增加组装数据
				getNetworkList(node);
			}
		} else {
			// 取消选中
			var tempUuid = [];
			for (var j in allNodes) {
				if ($.inArray(allNodes[j].vcuuid, tempUuid) == -1) {
					tempUuid.push(allNodes[j].vcuuid);
				}
			}
			// 需要去计算下已经选中了的数组
			var realArray = arrayCommon(tempUuid, initNetworkList);
			if (realArray.length != initNetworkList.length) {
				// 那么重新初始化下
				var nowTempArr = [];
				for (var z in realArray) {
					nowTempArr = nowTempArr.concat(initNetWorkdLists[realArray[z]]);
				}
				initNetworkList = realArray;
				//清空选中的网络列表及配置
				$('#isolatedDiv').empty();
				networkList = [];
				networkNameList = [];
				productNetworkList = [];
				//记录初始化的生产网卡列表
				var productnetwork = $('#productnetwork');
				productnetwork.empty();
				for(var i=0; i<nowTempArr.length; i++){
					productNetworkList.push(nowTempArr[i]);
					if(nowTempArr[i].uuid == 0) continue;
					var option = $("<option>").text(nowTempArr[i].text).val(nowTempArr[i].uuid);
					productnetwork.append(option);
					networkNameList.push(nowTempArr[i].text);
				}
				productnetwork.selectpicker('refresh');
			}
		}
		// isExpend = true;
	}

	// 获取内嵌的主机信息那些
	function getHoxInfo() {
		var allNodes = hostTree2.getCheckedNodes(true);
		if (allNodes.length == 0) {
			//虚拟化类型
			data.hostInfo.hypervisor = '';
			//主机所在虚拟化中心uuid
			data.hostInfo.vcenter_uuid = '';
			//主机uuid
			data.hostInfo.host_uuid = '';
			return;
		}
		//虚拟化类型
		data.hostInfo.hypervisor = allNodes[0].hypervisor;
		//主机所在虚拟化中心uuid
		data.hostInfo.vcenter_uuid = allNodes[0].vcuuid;
		//主机uuid
		data.hostInfo.host_uuid = allNodes[0].id;
	}

	function arrayCommon(a, b) {
		return a.filter(function(item) {
			return b.includes(item);
		});
	}

	//选择宿主机节点展开事件绑定
	var hostRefresh = function(treeId, treeNode){
		if(1 == treeNode.type){
			var data = JSON.stringify({sid:treeNode.sid,id:treeNode.id,pid:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:true, hostuuid:SETTINGS.hostInfo.host_uuid});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({
    			type: "post",
    	        url: CONF.AJAXPATH,
    	        async:true,
    	        data:{m:CONF.M.VCENTER,f:'getSyncRecoveryVcenter',p:data},
    	        success: function(data){
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
    	        		$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
    	        		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        }
    		});
		}else{
			return true;
		}
	}

	//选择宿主机节点展开事件绑定
	var getSyncHostNode = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			//修改增加选中宿主机uuid参数
			var data = JSON.stringify({sid:treeNode.sid,id:treeNode.id,pid:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:false, hostuuid:SETTINGS.hostInfo.host_uuid});
			Metronic.blockUI({target: '#host_tree',animate: true});
			$.ajax({
    			type: "post",
    	        url: CONF.AJAXPATH,
    	        async:true,
    	        data:{m:CONF.M.VCENTER,f:'getSyncRecoveryVcenter',p:data},
    	        success: function(data){
    	        	Metronic.unblockUI('#host_tree');
    	        	result = JSON.parse(data);
    	        	if(result.re){
    	        		//success
    	        		hostTree.addNodes(treeNode, result.msg, true);

    	        		//加载存储和网络
    	        		var info = {
    	        			'hypervisor' : SETTINGS.hostInfo.hypervisor,
    	        			'id' : SETTINGS.hostInfo.host_uuid,
    	        			'vcuuid' : SETTINGS.hostInfo.vcenter_uuid
    	        		}
    	        		initNetworkAndStore(info, true);
    	        	}else{
    	        		OPREL(data);
    	        	}
    	        }
    		});
		}else{
			return true;
		}
	}

	var getSyncHostNode2 = function(treeId, treeNode){
		if(1 == treeNode.type){
			if(treeNode.children) return true;
			var data = JSON.stringify({sid:treeNode.sid,id:treeNode.id,pid:treeNode.hypervisor,
				nocheck:false,hideoffline:true,refresh:false});
			Metronic.blockUI({target: '#host_tree2',animate: true});
			$.ajax({
				type: "post",
				url: CONF.AJAXPATH,
				async:true,
				data:{m:CONF.M.VCENTER,f:'getSyncRecoveryVcenter',p:data},
				success: function(data){
					Metronic.unblockUI('#host_tree2');
					result = JSON.parse(data);
					if(result.re){
						//success
						hostTree2.addNodes(treeNode, result.msg, true);
					}else{
						OPREL(data);
					}
				}
			});
		}else{
			return true;
		}
	}
	//清空网络列表及配置
	var cleanNetworkConfig = function(){
		networkList = [];
		$('#isolatedDiv').empty();
		data.networkInfo = [];
	}

	//初始化网络和存储(一般情况)
	var initNetworkAndStore = function(node, flag){
		var p = {};
		p.hypervisor = node.hypervisor;
		p.vcenteruuid = node.vcuuid;
		p.hostuuid = node.id;
		var jsonData = JSON.stringify(p);
		Metronic.blockUI({target: '#labContent',animate: true});
		$('select[name=hoststorage]').empty();
		$('select[name=hostnetwork]').empty();
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getNetworkAndStorage',p:jsonData}, function(d){
			Metronic.unblockUI('#labContent');
			var data = JSON.parse(d);
			networkSettings = data.network;
			var hoststorage = $('select[name=hoststorage]');
			var hostnetwork = $('select[name=hostnetwork]');
			//目标存储和网卡数组
			var storageList = [], networkList = [];
			hoststorage.empty();
			hostnetwork.empty();

			for(var i=0; i<data.storage.length; i++){
				if(data.storage[i].uuid == 0) continue;
				var option = $("<option>").text(data.storage[i].text).val(data.storage[i].uuid);
				hoststorage.append(option);
				storageList.push(data.storage[i].uuid);
			}

			for(var i=0; i<data.network.length; i++){
				if(data.network[i].uuid == 0) continue;
				var option = $("<option>").text(data.network[i].text).val(data.network[i].uuid);
				hostnetwork.append(option);
				networkList.push(data.network[i].uuid);
			}

			//第一次初始化存储和网络显示
			if(!initStorageFlag){
				_oldNetworkList = [];
				var networkInfo = SETTINGS.networkInfo;
				//循环获取选中配隔离网络的网卡名
				for(var i=0;i<networkInfo.length;i++){
					_oldNetworkList.push(networkInfo[i].productInfo.name);
				}
				//初始化代理挂载的目标存储
				if($.inArray(SETTINGS.hostInfo.storage_uuid,storageList) != -1){
					hoststorage.val(SETTINGS.hostInfo.storage_uuid);
				}
				//初始化代理使用的生产网络
				if($.inArray(SETTINGS.hostInfo.proxy_info.network_uuid,networkList) != -1){
					hostnetwork.val(SETTINGS.hostInfo.proxy_info.network_uuid);
				}
				//重新加载才更新加载存储标记，否则为初始化标记
				if(!flag){
					initStorageFlag = true;
				}
			}
			$('#labContent').find('.button-next').prop('disabled', false);
    	});
	}

	//获取虚拟化中心的网络列表
	var initVcenterNetworkList = function(){
		$('#productnetwork').empty();
		//清空选中的网络列表及配置
		cleanNetworkConfig();
		//记录初始化的生产网卡列表
		productNetworkList = networkSettings;
		var productnetwork = $('#productnetwork');
		productnetwork.empty();
		networkNameList = [];
		for(var i=0; i<networkSettings.length; i++){
			if(networkSettings[i].uuid == 0) continue;
			var option = $("<option>").text(networkSettings[i].text).val(networkSettings[i].uuid);
			productnetwork.append(option);
			networkNameList.push(networkSettings[i].text);
		}
		if(!vcenterNetworkFlag){
			_oldNetworkList = [];
			var networkInfo = SETTINGS.networkInfo;
			//循环获取选中配隔离网络的网卡名
			for(var i=0;i<networkInfo.length;i++){
				_oldNetworkList.push(networkInfo[i].productInfo.uuid);
			}
			productnetwork.selectpicker('val', _oldNetworkList);
			initIsolatedNum = networkInfo.length;
		}
		productnetwork.selectpicker('refresh');
		var networkInfo = SETTINGS.networkInfo;
		for(var i=0;i<networkInfo.length;i++){
			var productNetwork = networkInfo[i].productInfo.uuid;
			networkList.push(productNetwork);
			var p = {};
			p.id = productNetwork
			p.name = networkInfo[i].productInfo.name;
			//加载隔离网络配置卡
			initIsolatedCard(p, networkInfo[i]);
		}
		vcenterNetworkFlag = true;
	}


	// 再次封装下获取网络列表的接口 这个是内嵌选择宿主机的时候调用
	function getNetworkList(node) {
		var p = {};
		p.hypervisor = node.hypervisor;
		p.vcenteruuid = node.vcuuid;
		var jsonData = JSON.stringify(p);
		Metronic.blockUI({target: '#labContent',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getVcenterNetworkList',p:jsonData}, function(d){
			Metronic.unblockUI('#labContent');
			var data = JSON.parse(d);
			initNetWorkdLists[node.vcuuid] = data.network
			//记录初始化的生产网卡列表
			var productnetwork = $('#productnetwork');
			for(var i=0; i<data.network.length; i++){
				productNetworkList.push(data.network[i]);
				if(data.network[i].uuid == 0) continue;
				var option = $("<option>").text(data.network[i].text).val(data.network[i].uuid);
				productnetwork.append(option);
				networkNameList.push(data.network[i].text);
			}
			productnetwork.selectpicker('refresh');
			vcenterNetworkFlag = true;
		});
	}

	//初始化宿主机树
	function initHostTree(){
		var data = JSON.stringify({editFlag: true, vcenteruuid: SETTINGS.hostInfo.vcenter_uuid});
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVirtualLabHost',p:data}, setHostTree);
		if ($('#data_source_type').val() == 1) {
			// vm
			$('.data-resource1').show();
			$('.data-resource2').hide();
			$('#nohosttips').hide();

		} else {
			// 内嵌
			$('.data-resource2').show();
			$('.data-resource1').hide();
		}
//		//隐藏代理网关配置
//		$('.proxySettingsDiv').hide();
	}

	//第一步
	var step1Valid = function(){
		//演练室部署位置
		let labType = $('#data_source_type').val();
		if(!labType){
			UIToastr.showWarning(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_SELECT_LAB_TYPE_TIPS);
			return false;
		}
		//虚拟实验室
		data.labInfo.virtual_lab_name = $.trim($('#labname').val());
		if(data.labInfo.virtual_lab_name == ""){
			UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_INPUT_NAME);
			return false;
		}
		//不允许重名
		if(!renameFlag){
			return false;
		}
		//代理网关
		data.labInfo.proxy_name = $.trim($('#proxyname').val());
		if(data.labInfo.proxy_name == ""){
			UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_INPUT_PROXY_NAME);
			return false;
		}

		// 虚拟演练室名 和 代理网关 的名称不能有空格
		if(/\s/.test(data.labInfo.virtual_lab_name)){
			// 使用正则表达式检查是否有空格
			UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_ADD,  LANG.UI_VIRTUAL_LAB_CAN_NOT_CONTAIN_SPACE);
			return false;
		}
		if(/\s/.test(data.labInfo.proxy_name)){
			// 使用正则表达式检查是否有空格
			UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_ADD, LANG.UI_VIRTUAL_LAB_CAN_NOT_CONTAIN_SPACE);
			return false;
		}

		//资源池
		data.labInfo.resource_pool_name = $('#resourcepool').html();
		//文件夹
		data.labInfo.folder_name = $('#folder').html();
		//虚拟交换机
		data.labInfo.isolated_vswitch_name = $('#virtualswitch').html();

		$('#labContent').find('.button-next').prop('disabled', true);
		//第一次下一步初始化
		if(!initHostFlag && !embFlag){
			initHostTree();		//初始化宿主机树
			initHostFlag = true;
		}
		if($('#data_source_type').val() == 2){
			//IP地址
			$('#ipaddress').val('192.168.55.55');
			//子网掩码
			$('#subnetmask').val('255.255.255.0');
			//网关
			$('#gateway').val('192.168.1.1');
		}
		//添加第一步信息确认描述
		showStep1();
		return true;
	}

	//第一步信息确认
	var showStep1 = function(){
		var des = "";
		des += $('.hosttypelable').html() + ": " + $('#data_source_type').find('option:selected').text() + "<br>";
		des += $('.lablabel').html() + ": " + $('#labname').val() + "<br>";

		$('.virtuallabshow').html(des);
		//展示代理网关名称
		$('#proxydes').html($('#proxyname').val());

		return true;
	}

	//第二步
	var step2Valid = function(){
		var dataSourceType = $('#data_source_type').val();

		data.hostInfo.source_type = dataSourceType;

		if (dataSourceType == 1) {
			//获取选中的宿主机
			if(!hostTree) return false;
			var node = hostTree.getCheckedNodes();
			if(node.length == 0 && dataSourceType == 1){
				UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_ADD, LANG.UI_VIRTUAL_LAB_SELECT_HOST);
				return false;
			}
			$('.data-resource2').hide();
			// 第三方vm
			//虚拟化类型
			data.hostInfo.hypervisor = node[0].hypervisor;
			//主机所在虚拟化中心uuid
			data.hostInfo.vcenter_uuid = node[0].vcuuid;
			//主机uuid
			data.hostInfo.host_uuid = node[0].id;
			//主机存储
			data.hostInfo.storage_uuid = $('#hoststorage').val();
			var proxyInfo = {};
			//网卡名称
			proxyInfo.network_uuid = $('#hostnetwork option:selected').val();
			proxyInfo.network_name = $('#hostnetwork option:selected').text();
			if(!data.hostInfo.storage_uuid || !proxyInfo.network_name){
				UIToastr.showWarning(LANG.UI_VIRTUAL_LAB_ADD, LANG.UI_VIRTUAL_LAB_NOT_FOUND_STORAGE_AND_NETWORK);
				return false;
			}
		} else {
			// 内嵌
			//获取选中的节点
			var nodeuuid = $('#nodeSelect').val();
			if(nodeuuid == 0){
				UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_ADD, LANG.UI_VM_MACHINE_NETWORK_CHOOSE_NODE);
				return false;
			}
			data.hostInfo.nodeuuid = nodeuuid;

			data.hostInfo.hypervisor = 108;
			//主机所在虚拟化中心uuid
			data.hostInfo.vcenter_uuid = "";
			//主机uuid
			data.hostInfo.host_uuid = "";
			//主机存储
			data.hostInfo.storage_uuid = $('#hoststorage2').val();
			var proxyInfo = {};
			//网卡名称
			proxyInfo.network_uuid = "";
			proxyInfo.network_name = "";
			if(!data.hostInfo.storage_uuid){
				UIToastr.showWarning(LANG.UI_VIRTUAL_LAB_ADD, LANG.UI_VIRTUAL_LAB_NOT_FOUND_STORAGE_AND_NETWORK);
				return false;
			}
		}

		//IP地址
		proxyInfo.ip_address = $('#ipaddress').val();
		//子网掩码
		proxyInfo.netmask = $('#subnetmask').val();
		//网关
		proxyInfo.gateway = $('#gateway').val();
		if (embFlag){
			//IP地址
			proxyInfo.ip_address = "";
			//子网掩码
			proxyInfo.netmask = "";
			//网关
			proxyInfo.gateway = "";
		}

		data.hostInfo.proxy_info = proxyInfo;
		//第二步信息确认
		if (!submitForm.validate().form()) {
    		return false;
        }
		//初始化虚拟化中心所有网络
		if(!vcenterNetworkFlag && !embFlag){
			initVcenterNetworkList();
		}
		if(!vcenterNetworkFlag && embFlag){
			var networkInfo = SETTINGS.networkInfo;
			initIsolatedNum = networkInfo.length;
			for(var i=0;i<networkInfo.length;i++){
				var productNetwork = networkInfo[i].productInfo.uuid;
				var p = {};
				p.id = productNetwork
				p.name = networkInfo[i].productInfo.name;
				//加载隔离网络配置卡
				initIsolatedCard(p, networkInfo[i]);
				embList.push(productNetwork);
				embNum ++;
			}
			vcenterNetworkFlag = true;
		}
		showStep2(node);
		return true;
	}

	//第二步信息确认
	var showStep2 = function(node){
		//目标宿主机
		if(!embFlag){
			var hostdes = "";
			hostdes += LANG.UI_VCENTER_HOST + ": " + node[0].name + "<br>";
			hostdes += $('.storagelabel2').html() + ": " + $('#hoststorage2').find('option:selected').text();
			$('.hostshow').html(hostdes);
			$('.hostshowDiv').show();
			//代理网关
			var proxydes = "";
			if (data.hostInfo.source_type == 1) {
				proxydes += $('.proxynetworklabel2').html() + ": " + $('#hostnetwork2').find('option:selected').text() + "<br>";
			}

			proxydes += $('.iplabel').html() + ": " + $('#ipaddress').val() + "<br>";
			proxydes += $('.netmasklabel').html() + ": " + $('#subnetmask').val() + "<br>";
			proxydes += $('.gatewaylabel').html() + ": " + $('#gateway').val();
			$('.proxyshow').html(proxydes);
		}else{
			//计算节点
			var nodeDes = $('#nodeSelect').find('option:selected').text();
			$('.nodeshow').html(nodeDes);
			//目标存储
			var storageDes = $('#hoststorage2').find('option:selected').text();
			$('.storageshow').html(storageDes);

			//屏蔽第三方主机信息
			$('.hostshowDiv').hide();


		}
	}

	//第三步
	var step3Valid = function(){
		var select = $('#productnetwork').selectpicker('val');
		if(embFlag){
			select = embList;
		}
		//没选择生产网络，返回错误提示
		if(select.length == 0){
			UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_EDIT, LANG.UI_VIRTUAL_LAB_ADD_TIPS);
			return false;
		}
		var list = [];
		for(var i=0;i<select.length;i++){
			var info = {}, productInfo = {}, isolatedInfo = {};
			var id = select[i].replace(_REG, '_');
			//生产网络信息
			productInfo.name = $('#'+ id + " .productname").html();
			if (embFlag){
				productInfo.name = "";
			}
			productInfo.uuid = $('#'+ id).attr("data-id");
			productInfo.netmask = $('#'+ id + " .productnetmask").val();
			productInfo.gateway = $('#'+ id + " .productgateway").val();
			info.productInfo = productInfo;

			//隔离网络信息
			isolatedInfo.name = "";
			isolatedInfo.uuid = "";
			isolatedInfo.netmask = "";
			isolatedInfo.gateway = "";
			if(!embFlag){
				isolatedInfo.name = $('#'+ id + " .isolatedname").val();
				isolatedInfo.netmask = $('#'+ id + " .newnetmask").val();
				isolatedInfo.gateway = $('#'+ id + " .newgateway").val();
			}
			info.isolatedInfo = isolatedInfo;
			//检查生产网络信息和隔离网络信息是否填全
			var checkQues = !ipV4V6(productInfo.netmask) || !ipV4V6(productInfo.gateway) ||  !ipV4V6(isolatedInfo.netmask) || !ipV4V6(isolatedInfo.gateway);
			if (embFlag){
				checkQues = !ipV4V6(productInfo.netmask) || !ipV4V6(productInfo.gateway);
			}
			if(checkQues){
				UIToastr.showInfo(LANG.UI_VIRTUAL_LAB_ISOLATED_NETWORK_INFO, LANG.UI_VIRTUAL_LAB_ISOLATED_NETWORK_INFO_INPUT_TIPS);
				return false;
			}

			list.push(info);
		}

		data.network_list = list;

		//显示第三步信息
		showStep3(list);
		return true;
	}

	//显示第三步信息
	var showStep3 = function(list){
		var des = '';
		if (embFlag){
			des += '<table border="1" class="isolatedTable"><thead><tr><th width="100%">'+LANG.UI_DRILLS_PRODUCT_NETWORK+'</th></tr></thead><tbody>';
		}else{
			des += '<table border="1" class="isolatedTable"><thead><tr><th width="50%">'+LANG.UI_DRILLS_PRODUCT_NETWORK+'</th><th width="50%">'+LANG.UI_DRILLS_ISOLATED_NETWORK+'</th></tr></thead><tbody>';
		}
		for(var i =0;i<list.length; i++){
			var productInfo = list[i].productInfo;
			var isolatedInfo = list[i].isolatedInfo;
			if (!embFlag){
				var productDes = LANG.UI_VIRTUAL_LAB_NETWORK_NAME + ": " + productInfo.name + "<br>" + LANG.UI_DRILLS_NETMASK +
					": " + productInfo.netmask + "<br>" + LANG.UI_DRILLS_GATEWAY +
					": " + productInfo.gateway;
				var isolatedDes = LANG.UI_VIRTUAL_LAB_NETWORK_NAME + ": " + isolatedInfo.name + "<br>" + LANG.UI_DRILLS_NETMASK +
					": " + isolatedInfo.netmask + "<br>" + LANG.UI_DRILLS_GATEWAY +
					": " + isolatedInfo.gateway;

				des += '<tr><td>' + productDes + '</td><td>' + isolatedDes +"</td></tr>";
			}else{
				var productDes = LANG.UI_DRILLS_NETMASK +
					": " + productInfo.netmask + "<br>" + LANG.UI_DRILLS_GATEWAY +
					": " + productInfo.gateway;
				des += '<tr><td>' + productDes + '</td></tr>';
			}
		}

		des +='</tbody></table>';

		$('.isoladinfoshow').html(des);
	}

	//初始化虚拟实验室名
	var initLabName = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:"getCreateLabName",p:{}}, function(d){
			//初始化加载实验室、代理网关、资源池、文件夹、虚拟交换机
			$('#labname').val(d);
			$('#proxyname').val(d + " Proxy");
			$('#resourcepool').html(d + " Pool");
			$('#folder').html(d + " Folder");
			$('#virtualswitch').html(d + " Switch");
		});
	}

	//提交创建流程
	var submit = function(){
		//修改可以只修改部分提前提交
		if(pageIndex == 0){
			var step1 = step1Valid();
			if(!step1) return false;
		}else if(pageIndex == 1){
			var step2 = step2Valid();
			if(!step2) return false;
		}else if(pageIndex == 2){
			var step3 = step3Valid();
			if(!step3) return false;
		}
		//用于检测是否未修改信息
		data.oldSettings = oldSettings;
		Metronic.blockUI({target: '#labContent',animate: true});
		pAjaxRequest(data, '/api/v1/verification/lab', 'PUT', (result) => {
			Metronic.unblockUI('#labContent');
			if (result.success) {
				UIToastr.showSuccess(result.title, result.message);
				LOCATION('./content/platform/dataverification/virtual_lab_manager.php', 'virtual_lab_manager');
			}else{
				UIToastr.showWarning(LANG.UI_VERIFY_EDIT_VIRTUAL_LAB, LANG.UI_VERIFY_EDIT_VIRTUAL_LAB_FAILD);
			}

		});
	}

	//初始化加载隔离网络框
	var initIsolatedCard = function(data, info){
		var dataSourceType = $('#data_source_type').val();
		var productInfo = info.productInfo;
		var isolatedInfo = info.isolatedInfo;
		var table = '';
		var collapsed = "collapsed";
		var collapsedIn = "";
		if(!firstFlag){
			collapsed = "";
			collapsedIn = "in";
			firstFlag = true;
		}

		var productnameDom = '<p data-id="'+data.id+'" class="productname mt-5">'+ data.name +'</p>';
		var isolatedName = data.name + ' isolate';
		if(embFlag){
			isolatedName = "";
			productnameDom = '<div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="" class="form-control required productname ignore" value="" data-id="'+data.id+'" /></div>';
		}

		//头部
		table += '<div style="position: relative;" class="panel panel-default isolateNetwork" id="'+data.id+'" data-id="'+data.id+'"><div class="panel-heading"><h4 class="panel-title"><a class="accordion-toggle accordion-toggle-styled popovers '+collapsed+'"'+
			'data-container="body" data-trigger="hover" data-parent="#isolatedDiv" data-placement="top" data-toggle="collapse"  href="#isolated'+data.id+'"  ><span class="font-green-seagreen">'+data.name+'</span></a></h4></div>' +
			'<div class="panel-collapse collapse '+collapsedIn+'" id="isolated'+data.id+'"><div class="panel-body"><div class="form-group col-md-12 ">';

		if (dataSourceType == 2){
			//容灾演练平台只需要输入生产网络信息
			//网络配置部分
			table += '<form action="#" class="form-horizontal networkconfig" ><div class="form-body"><table style="border: 1px solid #EBEEF0;" class="table table-bordered table-striped table-condensed flip-content  tdvalignm"><thead class="flip-content"><tr><th width="20%">'+LANG.UI_DRILLS_OPTION+'</th><th width="80%">'+LANG.UI_DRILLS_PRODUCT_NETWORK+'</th></tr></thead><tbody id="segmenttbody">'+
				'<tr class="first"><td>'+LANG.UI_DRILLS_NETMASK+'</td><td><div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="255.255.255.0" class="form-control required  netmask productnetmask ignore" /></div></td></tr>'+
				'<tr><td>'+LANG.UI_DRILLS_GATEWAY+'</td><td><div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="30.30.1.1" class="form-control required ipv4 productgateway ignore" /></div></td>'+
				'</tr></tbody></table></div></form>';
		}else{
			//第三方虚拟化
			table += '<form action="#" class="form-horizontal networkconfig" ><div class="form-body"><table style="border: 1px solid #EBEEF0;" class="table table-bordered table-striped table-condensed flip-content  tdvalignm"><thead class="flip-content"><tr><th width="20%">'+LANG.UI_DRILLS_OPTION+'</th><th width="40%">'+LANG.UI_DRILLS_PRODUCT_NETWORK+'</th><th width="40%">'+LANG.UI_DRILLS_ISOLATED_NETWORK+'</th></tr></thead><tbody id="segmenttbody">'+
				'<tr class="first"><td>'+LANG.UI_VIRTUAL_LAB_NETWORK_NAME+'</td><td>'+productnameDom+'</td><td><div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="isolated network" class="form-control required isolatedname ignore" value=""/></div></td></tr>' +
				'<tr><td>'+LANG.UI_DRILLS_NETMASK+'</td><td><div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="255.255.255.0" class="form-control required  netmask productnetmask ignore" /></div></td><td><div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="'+LANG.UI_VIRTUAL_LAB_INPUT_NETWORK_AUTO+'" class="form-control required  netmask newnetmask ignore" /></div></td></tr>'+
				'<tr><td>'+LANG.UI_DRILLS_GATEWAY+'</td><td><div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="30.30.1.1" class="form-control required ipv4 productgateway ignore" /></div></td><td><div class="input-icon right"><i class="fa"></i><input type="text" maxlength="128" placeholder="'+LANG.UI_VIRTUAL_LAB_INPUT_NETWORK_AUTO+'" class="form-control required ipv4 newgateway ignore" /></div></td>'+
				'</tr></tbody></table></div></form>';
		}



		//结尾拼接
		table += '</div></div></div>';
		//内嵌添加删除按钮
		if(embFlag){
			table += '<button type="button" class="btn viconfont vicon-a-Deleteshanchu1 green-haze b-btn" id="delete_' + data.id + '" style="position: absolute;right: -45px;top:0;"></button>';
		}
		table += '</div>';
	    //添加到对应页面
		$('#isolatedDiv').append(table);
		$('#delete_' + data.id).on('click', function(){
			$('#' + data.id).remove();
			embNum --;
			//检查已经取消的网络
			for(var i=0;i<embList.length;i++){
				//网络未选中时移除
				if(data.id == embList[i]){
					embList.splice(i,1);
				}
			}

		});
		if(initIsolatedNum > 0){
			//生产网络信息
			if(embFlag){
				$('#'+ data.id + " .productname").val(productInfo.name)
			}else{
				$('#'+ data.id + " .productname").html(productInfo.name);
			}
			$('#'+ data.id + " .productnetmask").val(productInfo.netmask);
			$('#'+ data.id + " .productgateway").val(productInfo.gateway);
			//隔离网络信息
			$('#'+ data.id + " .isolatedname").val(isolatedInfo.name);
			$('#'+ data.id + " .newnetmask").val(isolatedInfo.netmask);
			$('#'+ data.id + " .newgateway").val(isolatedInfo.gateway);
			initIsolatedNum --;
			//将已命名的隔离网卡名从数组移除
			var index = networkNameList.indexOf(isolatedInfo.name);
			if (index > -1) {
				networkNameList.splice(index, 1);
			}
		}
	}

	//增加代理网关网络信息检查
	var handleValidation = function() {
		submitForm = $('#submit_form');

		submitForm.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: ".ignore",  // validate all fields including form hidden input
            rules: {
            	labname:{
            		required: true,
            		labnameAvailable: true,
            	},
            	proxyname:{
            		required: true
            	},
            	ipaddress: {
                    required: true,
                    ipv4: true,
                },
                subnetmask: {
                	required: true,
                	vNetmask: true,
                },
                gateway: {
                    required: true,
                    vGateway: true,
                }
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

        });

        //IP验证格式
        $.validator.addMethod("ipv4", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_IP);

        //子网掩码
        $.validator.addMethod("vNetmask", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_NETMASK);

        //网关
        $.validator.addMethod("vGateway", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_GATEWAY);

        $.validator.addMethod(
            	"labnameAvailable",
            	function(value, element, param) {
            		var data = JSON.stringify({labname:value, oldname:SETTINGS.labInfo.virtual_lab_name});
            		var result = false;
            		$.ajax({
            			type: "post",
            	        url: CONF.AJAXPATH,
            	        async:false,
            	        data:{m:CONF.M.MANOEUVRE,f:'labnameAvailable',p:data},
            	        success: function(data){
            	        	result = JSON.parse(data);
            	        }
            		});
            		renameFlag = result;
        	    	return result;
        	    },
            	LANG.UI_VIRTUAL_LAB_EXIST_TIPS
            );

	}

	//初始化历史任务信息
	var initOldSettings = function(){
		var data = {};
		data.labuuid = $('#lab_uuid').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getLabEditAllInfo',p:data}, function(d){
			SETTINGS = JSON.parse(d);
			oldSettings = JSON.parse(d);
			initData();
			initStep1Settings();	//初始化话第一步信息
			initStep2Settings();	//初始化第二步信息
			initStep3Settings();	//初始化第三步信息
		});
	}

	//初始化第一步配置
	var initStep1Settings = function(){
		var labInfo = SETTINGS.labInfo;
		//虚拟实验室
		$('#labname').val(labInfo.virtual_lab_name);
		//代理网关
		$('#proxyname').val(labInfo.proxy_name);
		//资源池
		$('#resourcepool').html(labInfo.resource_pool_name);
		//文件夹
		$('#folder').html(labInfo.folder_name);
		//虚拟交换机
		$('#virtualswitch').html(labInfo.isolated_vswitch_name);
	}

	//初始化第二步配置
	var initStep2Settings = function(){
		var proxyInfo = SETTINGS.hostInfo.proxy_info;
		//网卡名称
		$('#hostnetwork').val(proxyInfo.network_name);
		//IP地址
		$('#ipaddress').val(proxyInfo.ip_address);
		//子网掩码
		$('#subnetmask').val(proxyInfo.netmask);
		//网关
		$('#gateway').val(proxyInfo.gateway);

		//显示代理网关网络配置
		$('.proxySettingsDiv').show();

		$('#data_source_type').val(SETTINGS.hostInfo.source_type);
		if(SETTINGS.hostInfo.source_type == 2){
			embFlag = true;
		}
		if (SETTINGS.hostInfo.source_type == 2) {
			// 内嵌虚拟化
			$('.data-resource1').hide();
			$('.data-resource2').show();
			$('#nodeSelect').val(SETTINGS.hostInfo.node_uuid).attr('disabled', true);
			initStorageList(SETTINGS.hostInfo.storage_uuid);
			// 隐藏 资源池 和 文件夹
			$('.poollabel').parent().hide();
			$('.folderlabel').parent().hide();
		} else {
			// 第三方虚拟化 vm
			$('.data-resource1').show();
			$('.data-resource2').hide();
			$('.poollabel').parent().show();
			$('.folderlabel').parent().show();
			$('#nohosttips').hide();
		}
		$('#data_source_type').prop('disabled', true);
	}

	//初始化第三步配置
	var initStep3Settings = function(){
//		initVcenterNetworkList();
	}
	//初始化所有备份节点
	var initNodeSelect = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeList',p:{}}, function(d){
			var data = JSON.parse(d);
			var nodeSelect = $('#nodeSelect');
			nodeSelect.empty();
			//var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
			//nodeSelect.append(option);
			var option = '';
			for(var i=0; i<data.length; i++){
				option = $("<option>").text(data[i].node_name).val(data[i].node_uuid);
				nodeSelect.append(option);
			}
			initOldSettings();//初始化历史信息
			// nodeSelect.val('0');
		});

		// 监听改变
		$('#data_source_type').on('change', function (){
			var value = $('#data_source_type').val();
			$('.proxySettingsDiv').show();
			embNum = 1;
			firstFlag = false;
			$('#isolatedDiv').empty();
			if (value == 2) {
				// 内嵌虚拟化
				$('.data-resource1').hide();
				$('.data-resource2').show();
				// 隐藏 资源池 和 文件夹
				$('.poollabel').parent().hide();
				$('.folderlabel').parent().hide();
				$('#nodeSelect').change();
				if (hostTree2 == undefined) {
					checkHostNodeInfo2('[]', 'host_tree_div2')
				}
				$('#nohosttips').hide();
				embFlag = true;
				// addNetwork();
			} else {
				$('#nohosttips2').hide();
				// 第三方虚拟化 vm
				$('.data-resource1').show();
				$('.data-resource2').hide();
				$('.poollabel').parent().show();
				$('.folderlabel').parent().show();
				if (hostTree == undefined) {
					checkHostNodeInfo('[]', 'host_tree_div')
					$('.proxySettingsDiv').hide();
				}
				embFlag = false;
			}
		})

		$('#nodeSelect').on('change', function (){
			initStorageList();
		})
		//添加生产网络
		$('#addNetwork').on('click', function(){
			addNetwork();
		});

	}

	//初始化存储
	var initStorageList = function(uuid = ""){
		var value = $('#nodeSelect').val();
		//置空
		var hoststorage = $('select[name=hoststorage2]');
		var hostnetwork = $('select[name=hostnetwork2]');
		hoststorage.empty();
		hostnetwork.empty();
		if (value != 0) {
			var p = {};
			p.nodeuuid = value;
			p.labFlag = true;
			var jsonData = JSON.stringify(p);
			// 加载本地存储和网卡列表
			$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
				var data = JSON.parse(d);
				for(var i=0; i<data.length; i++){
					if(data[i].uuid == 0) continue;
					var option = $("<option>").text(data[i].text).val(data[i].uuid);
					hoststorage.append(option);
				}
				if(uuid != ""){
					hoststorage.val(uuid);
				}
			});
			p.setipflag = true;
			p = JSON.stringify(p);
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getNetworkCardList',p:p}, function(d){
				var data = JSON.parse(d);
				for(var i=0; i<data.length; i++){
					if(data[i].value == 0) continue;
					var option = $("<option>").text(data[i].name).val(data[i].value);
					hostnetwork.append(option);
				}
			});
		}
	}

	var addNetwork = function(){
		var p = {};
		p.id = "Emb_Network" + embNum;
		p.name = LANG.UI_DRILLS_PRODUCT_NETWORK + embNum;
		//加载隔离网络配置卡
		//增加修改虚拟实验室需要传递参数
		var info = {
			'name' : "",
			'uuid' : "",
			'netmask' : "",
			'gateway' : ""
		}
		var data = {
			'productInfo' : info,
			'isolatedInfo' : info
		}
		initIsolatedCard(p, data);
		embNum ++;
		embList.push(p.id);
	}

	return {
		init: function(){
			initNodeSelect();
//		    initLabName();	      //初始化虚拟实验室名
			wizardInit();         //初始化执行步骤插件
			initListeners();
			handleValidation();
		}
	}
}();

jQuery(document).ready(function(){
	EditVirtualLab.init();
});