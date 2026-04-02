var VMRecover = function () {
	var data = {pointInfo:{},recoverInfo:{},typeInfo:{high:{trasfer:{}}},taskName:''};
	var pointtypetree, hostTree;
	var selectIPFlag = true;
	var flagDay = false;
	var _VMNAMEREG = new RegExp("[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？ ]");
	var initListener = function(){
		//选择恢复方式
		$('#recovertype').on('change',function(){
			if('1' == this.value){
				data.typeInfo.strategy = {};
				$('#setstrategy').hide();
			}else if("2" == this.value){
				$('#setstrategy').show();
			}
		});
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
		})
		//时间策略确定
		$('.each-button').on("click",eachButtonClick);
		
		$('#tobackup').on('click',function(){
	    	LOCATION('./content/vm/vmbackup.php', 'vmbackup');
		});
		
	}
	
	var setStrategyInfo = function(info){
		data.typeInfo.strategy = info;
		return true;
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
	//全局
	var globalSelect = function(tabpane, type){
		
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
			i.attr('class', 'fa fa-check');
			$('#recover').collapse('hide');
		}else{
			i.attr('class', '');
		}
	}
	
	var checkTreeNodeInfo = function(zNodes){
		if(zNodes == "[]"){
			$("#nopointtips").show();
			$('#pointtypetree').hide();
			return false;
		}else{
			$("#nopointtips").hide();
			$('#pointtypetree').show();
			$("#two_tree").show();
			initHostTree();
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
				},
				view: {
					showTitle: true
				}
			};
		pointtypetree = $.fn.zTree.init($("#pointtypetree"), setting, JSON.parse(zNodes));
	};
	
	var initPointTree = function() {
		var data = {};
		data.node = $('#nodeselect').val();
		data = JSON.stringify(data);
		Metronic.blockUI({target: '.two_tree',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getPlanVMTimepointTree',p:data}, setPointTree);
	};
	
	var setHostTree = function(zNodes){
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
				callback: {
					beforeClick: hostNodeSelect,
					onCheck: hostOnCheck,
				}
			};
		hostTree = $.fn.zTree.init($("#host_tree"), setting, JSON.parse(zNodes));
	};
	
	//选择时间点节点事件绑定
	var nodeSelect = function(treeId, treeNode, clickFlag){
		pointtypetree.checkNode(treeNode, !treeNode.checked, false, true);
		pointtypetree.expandNode(treeNode, true);
		return;
	}
	//选择宿主机节点事件绑定
	var hostNodeSelect = function(treeId, treeNode, clickFlag){
		if(1 == treeNode.type){
			hostTree.checkNode(treeNode, !treeNode.checked, false, true);
		}else{
			hostTree.expandNode(treeNode, true);
		}
	}
	
	var initHostTree = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getProxyHost',p:{}}, setHostTree);
	}
	
	
	//选中,取消某个节点的子节点,如果子节点还有子节点,再取消/选中
	var checkNodeChilds = function(node, checked){
		if(!node.isParent) return true;
		for(var i=0; i<node.children.length; i++){
			pointtypetree.checkNode(node.children[i], checked, false, true);
			
			if(!node.checked && node.children[i].isParent && node.children[i].type == 5){
				var timepointnode = node.children[i];
				checkNodeChilds(timepointnode, checked);
			}
		}
	}
	
	//取消,取消某个节点的子节点
	var uncheckNodeChilds = function(node){
		if(!node.isParent) return true;
		for(var i=0; i<node.children.length; i++){
			//取消所有完备点
			pointtypetree.checkNode(node.children[i], false, false, false);
		}
	}
	
	//虚拟机选中的时候,根据虚拟机的配置,选中一个(暂时选择最后一个)时间点
	var checkOneTimepoint = function(node){
		//如果完备的时候,最后一个就是最后一个完备点,
		//如果有增备和差备,最后一个需要是最后一个完备点展开后的最后一个孩子节点
		if(!node.isParent) return true;
		var chNode = node.children[node.children.length - 1];
		if(chNode.isParent){
			chNode = chNode.children[chNode.children.length - 1];
		}
		pointtypetree.checkNode(chNode, true, false, true);
	}
	
	//虚拟机分组
	var timepointOnCheck = function(e, id, node){
		switch(node.type){
			case 1:
				//plan
				if(node.checked){
					//如果是选中了plan,首先取消所有选中的,然后再选择当前的子节点
					var allNodes = pointtypetree.getCheckedNodes(true);
					for(var i = 0; i < allNodes.length; i++){
						pointtypetree.checkNode(allNodes[i], false, false, false);		
					}
					pointtypetree.checkNode(node, true, false, false);
					checkNodeChilds(node, true);
				}else{
					//如果是取消了plan
					var allNodes = pointtypetree.getCheckedNodes(true);
					for(var i = 0; i < allNodes.length; i++){
						pointtypetree.checkNode(allNodes[i], false, false, false);		
					}
					checkNodeChilds(node, false);
				}
				
				pointtypetree.expandNode(node, true);
				break;
			case 2:
			case 3:
				//group
				//child
				checkNodeChilds(node, false);
				if(node.checked){
					//如果是选中了plan,首先取消所有选中的,然后再选择当前的子节点
					pointtypetree.checkNode(node, true, false, true);
					checkNodeChilds(node, true);
				}
				pointtypetree.expandNode(node, true);
				break;
			case 4:
				//vm
				checkNodeChilds(node, false);
				if(node.checked){
					//选中的时候
					checkOneTimepoint(node);
				}
				break;
			case 5:
				//timepoint 完备点
				if(node.checked){
					var vmNode = node.getParentNode();
					for(var i=0; i<vmNode.children.length; i++){
						//取消所有完备点
						pointtypetree.checkNode(vmNode.children[i], false, false, false);
						//如果有增备和差备点,取消选中
						uncheckNodeChilds(vmNode.children[i]);
					}
					pointtypetree.checkNode(node, true, false, false);
				}
				break;
			case 6:
				//timepoint 增备点/差备点
				if(node.checked){
					uncheckNodeChilds(node.getParentNode().getParentNode());
					uncheckNodeChilds(node.getParentNode());
					pointtypetree.checkNode(node, true, false, false);
				}
				break;
		}
			
		return;
	}
	
	
	var hostOnCheck = function(e, id, node){
		if(node.checked){
			//如果选中了某个节点,需要把另一个虚拟化环境下的点取消
			var allNodes = hostTree.getCheckedNodes(true);
			for(var i=0; i<allNodes.length; i++){
				if(allNodes[i].hypervisor != node.hypervisor){
					hostTree.checkNode(allNodes[i], false, false, false);
				}
			}
		}
		hostTree.checkNode(node, node.checked, false, false);	
		if(node.primaryflag){
			//主服务器
			if(!node.checked){
				var slaverNode = hostTree.getNodesByParam("primaryuuid", node.primaryuuid, null);
				for(var i=0; i<slaverNode.length; i++){
					hostTree.checkNode(slaverNode[i], false, false, false);
				}
			}
		}else{
			//从服务器
			var primaryNode = hostTree.getNodeByParam("id", node.primaryuuid, null);
			hostTree.checkNode(primaryNode, false, false, false);
		}
		
		//初始化网络和目的宿主机
		initNetworkAndDesHost();
	}
	
	//初始化网络和目的宿主机
	var initNetworkAndDesHost = function(){
		var hostnetwork = $('select[name=hostnetwork]');
		var orchdeshost = $('select[name=orchdeshost]');
		hostnetwork.empty();
		orchdeshost.empty();
		//网络,自动选择
		var option = $("<option>").text(LANG.UI_DRILLS_AUTO_SELECT).val("0");
		hostnetwork.append(option);
		//宿主机
		var nodes = hostTree.getCheckedNodes();
		
		if(!nodes.length) return;
		
//		var option = $("<option>").text('自动选择').val("0");
//		orchdeshost.append(option);
		
		
		for(var i=0; i<nodes.length; i++){
			var option = $("<option>").text(nodes[i].name).val(nodes[i].proxyuuid);
			orchdeshost.append(option);
		}
		
		//把虚拟机平均分配到宿主机去.
		var points = data.pointInfo.points;
		//每个分组个数
		var groupVmNum = Math.floor(points.length/nodes.length);
		var groupStart = 0;
		var currentGroupIndex = 0;
		for(var i=0; i<points.length; i++){
			$(orchdeshost[i]).find("option[value='" + nodes[currentGroupIndex].proxyuuid + "']").attr("selected",true);
			groupStart++;
			if(groupStart >= groupVmNum){
				currentGroupIndex++;
				groupStart = 0;
				if(currentGroupIndex >= groupVmNum){
					currentGroupIndex--;
				}
			}
			if(currentGroupIndex >= nodes.length){
				currentGroupIndex = nodes.length - 1;
			}
		}
	}
	
	//初始化虚拟机配置
	var initVMConfig = function(){
		var jsonData = JSON.stringify(data.pointInfo.points);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVMPlanConfigInfo',p:jsonData}, function(d){
			var data = JSON.parse(d);
			$('#accordionvm').vmConfigOrch({'config':data, 'hide':['storage']});
			$("#accordionvm").sortable();
			
			initNetworkAndDesHost();
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
                $('#vmrecovercontent').find('.button-previous').hide();
                $('#vmrecovercontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#vmrecovercontent').find('.button-previous').show();
                $('#vmrecovercontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#vmrecovercontent').find('.button-next').hide();
                $('#vmrecovercontent').find('.button-submit').show();
            } else {
                $('#vmrecovercontent').find('.button-next').show();
                $('#vmrecovercontent').find('.button-submit').hide();
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

        $('#vmrecovercontent').find('.button-previous').hide();
        $('#vmrecovercontent .button-submit').click(submit).hide();
	};
	
	//得到某个时间点的全路径
	var getTimepointPath = function(node, path){
		var parent = node.getParentNode();
		if(!parent) return path;
		path = parent.name + " > "  + path;
		return getTimepointPath(parent, path);
	}
	
	//替换特殊字符
	var clearString = function (s){ 
	    var rs = ""; 
	    for (var i = 0; i < s.length; i++) { 
	        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_'); 
	    } 
	    return rs;  
	}
	
	var step1Valid = function(){
		var nodes = pointtypetree.getCheckedNodes();
		data.pointInfo.points = [];
		data.pointInfo.type = "";
		data.pointInfo.planuuid = "";
		data.pointInfo.nodeuuid = "";
		
		var desStr = "";
		$.each(nodes, function(i, d){
			//只要备份时间点
			if(5 == d.type || 6 == d.type){
				var jsondata = {
					vmuuid: d.vmuuid,
					vmname: clearString(d.vmname),
					vcenteruuid: d.vcenteruuid,
					timepointuuid: d.timepointuuid,
					childuuid: d.childuuid,
					nodeuuid: d.nodeuuid,
					dirpath: d.path
//					hypervisor: d.hypervisor
				};
				data.pointInfo.points.push(jsondata);
				data.pointInfo.type = d.hypervisor;
				data.pointInfo.nodeuuid = d.nodeuuid;
				data.pointInfo.planuuid = d.planuuid;
				
				desStr += getTimepointPath(d, d.name) + "<br>";
			}
		});
		if(!data.pointInfo.points.length){
			UIToastr.showInfo(LANG.UI_DRILLS_VM_SELECT_TIMEPOINT_TITLE, LANG.UI_DRILLS_VM_SELECT_TIMEPOINT_TIPS);
			return false;
		}
		
		initVMConfig();
		showStep1(desStr);
		return;
	}
	
	var showStep1 = function(desStr){
		$('.vmtypeshow').html(desStr);
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getVMOrchTaskName',p:{}}, function(d){
			$('#jobname').val(d);
		});
		//重新初始化一下时间控件
		StrategyEach.initDatePickerTwo();
	}
	
	var step2Valid = function(){
		var showStr = LANG.UI_DRILLS_HOST_VM;
		var nodes = hostTree.getCheckedNodes();
		if(!nodes.length){
			UIToastr.showInfo(LANG.UI_DRILLS_SELECT_DESTINATION_HOST, LANG.UI_DRILLS_SELECT_DESTINATION_HOST_TIPS);
			return false;
		}
		data.recoverInfo.hosts = [];
		for(var i=0; i<nodes.length; i++){
			data.recoverInfo.hosts.push(nodes[i].proxyuuid);
			showStr += nodes[i].name + "<br>";
		}
		
		if(selectIPFlag){
			//自动选择
			data.recoverInfo.addr = $('#serveripaddr').val();
		}else{
			//自定义
			data.recoverInfo.addr = $('input[name=backupserveraddr]').val();
			var value = data.recoverInfo.addr;
			var domain = /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
            var ipv4 = ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			if(!domain && !ipv4){
				UIToastr.showInfo(LANG.UI_DRILLS_INPUT_BACKUP_NODE_ADDRESS, LANG.UI_DRILLS_INPUT_BACKUP_NODE_ADDRESS_TIPS);
				return false;
			}
		}
		
		showStr += LANG.UI_DRILLS_VM;
		
		data.recoverInfo.vmconfigs = $('#accordionvm').getvmConfigOrch({'hide':['storage']});
		
		var deshosts = $('select[name=orchdeshost] option:selected');
		var flag = true;
		//得到虚拟机恢复名
		$.each(data.recoverInfo.vmconfigs, function(i, d){
			var value = $.trim(d.vmname);
			if("" == value){
				UIToastr.showInfo(LANG.UI_DRILLS_CHECK_VM_NAME, LANG.UI_DRILLS_CHECK_VM_NAME_TIPS1);
				flag = false;
				return false;
			}
			//检测是否含有特殊字符
			if(_VMNAMEREG.test(value)){
				flag = false;
				$(".setrecover2tip").html(LANG.UI_TOOLS_VMNAME_TIPS).show();
				UIToastr.showInfo(LANG.UI_DRILLS_CHECK_VM_NAME, LANG.UI_DRILLS_CHECK_VM_NAME_TIPS2);
				return false;
			}
			showStr += value + " => " + deshosts[i].text + "<br>";
		});
		
		showStep2(showStr);
		
		return true;
	}
	var showStep2 = function(str){
		$('.recovershow').html(str);
	}
	
	var step3Valid = function(){
		data.typeInfo.type = $('#recovertype').val();
		if('1' == data.typeInfo.type){
			//立即恢复
			showStep3();
			return true;
		}
		if($.isEmptyObject(data.typeInfo.strategy)){
			$('.setstrategytip').html(LANG.UI_STRATEGY_PLEASE_SET).show();
			Metronic.scrollTo($('.setstrategytip'));
			return false;
		}
		showStep3();
		return true;
	}
	
	var showStep3 = function(){
		var showStr1 = '', showStr2 = '';
		showStr1 = $('#recovertype').find("option:selected").text();
		if(!$.isEmptyObject(data.typeInfo.strategy)){
			showStr1 +=  ', ' + getStrategyDes(data.typeInfo.strategy);
		}
		
		if(data.typeInfo.high.trasfer.encrypt){
			showStr2 = LANG.UI_STRATEGY_TRANSFER + ": " + LANG.UI_STRATEGY_TRANSFER_ON;
		}else{
			showStr2 = LANG.UI_STRATEGY_TRANSFER + ": " + LANG.UI_STRATEGY_TRANSFER_OFF;
		}
		$('.reservetypeshow').html(showStr1);
		//传输策略
		var transferlabel = $('.transferlabel').html();
		$('.transportinfoshow').html(transferlabel + ": " + $('#transport_mode').find("option:selected").text());
	}
	
	var getStrategyDes = function(strategy){
		//每天12:12:12开始,不滚动
		//每天12:12:12开始,滚动间隔01:11:11,滚动结束时间23:11:11
		//每周1,2,3,4,5,6,
		var des = "";
		if(CONF.STRATEGY_TYPE.DAY == strategy.type){
			des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy); 
		}else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
			}else{
				des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
			}
		}else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
			des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
		}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
			des += LANG.UI_STRATEGY_GLOBAL;
		}else{
			des += LANG.UI_PUBLIC_NOTHING + "<br><br>";
		}
		return des;
	}
	
	var getEachStrategy = function(strategy){
		var desEach = '';
		desEach += strategy.startTime;
		desEach += LANG.UI_STRATEGY_START + ", ";
		if(strategy.rollFlag){
			desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
		}else{
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br><br>";
		return desEach;
	}
	
	var getStrategyDays = function(days){
		var desDays = '';
		$.each(days, function(i,d){
			if(1 == d){
				var day = i+1;
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					desDays += day + ", ";
				}else{
					desDays += "Day" + day + ", ";
				}
				
			}
		});
		return desDays;
	}
	//获取每周显示日期
	var getStrategyWeek = function(days){
		var desDays = '';
		$.each(days, function(i,d){
			if(1 == d){
				desDays += CONF.WEEK[i] + ", ";
			}
		});
		return desDays;
	}
	
	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
			return;
		}
		$('.jobnametip').hide();
		data.taskName = $.trim($("#jobname").val());
		//TODO提交
		var jsonData = JSON.stringify(data);
		Metronic.blockUI({target: '#vmrecovercontent',animate: true, cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'createOrchTaskInstant',p:jsonData}, function(d){
    		Metronic.unblockUI('#vmrecovercontent');
    		if(OPREL(d)){
    			LOCATION('./content/platform/manoeuvre/task.php', 'orch_task');
        	}
    	});
	}
	
	
	//节点选择改变事件
	var nodeselectChange = function(){
		initPointTree();
	}
	
	//初始化时间点展示方式和事件
	var initPointShowType = function(){
		var data = {};
		data.truenode = true;
		data.moduleType = CONF.MODULE_TYPE.VM;
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getTimepointAllNode',p:data}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('#nodeselect');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
			//初始化BS select控件
			$('#nodeselect').selectpicker({
	            iconBase: 'fa',
	            tickIcon: 'fa-check'
	        });
			
			initPointTree();
			initBackupServerAddr();
    	});
		
		//绑定事件
		$('#nodeselect').on('change', nodeselectChange);
	}
	
	//初始化存储挂载点IP或域名
	var initBackupServerAddr = function(nodeuuid){
		var data = {};
		data.nodeuuid = $('#nodeselect').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeIPAddr',p:data}, function(d){
			var data = JSON.parse(d);
			var serveripaddr = $('#serveripaddr');
			serveripaddr.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i]).val(data[i]);
				serveripaddr.append(option);
			}
		});
		$('input[name=backupserveraddr]').val(window.location.host);
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	wizardInit();
        	initPointShowType();
        	initListener();
        	
        },
    };
}();

jQuery(document).ready(function() {   
	VMRecover.init();
});