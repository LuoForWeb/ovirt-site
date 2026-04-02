	var VMMotion = function () {
		var hostTree, usergroupTree;
		var _HOSTSTORAGE = [];	//目的宿主机存储信息
		var networkflag = false, onlineflag = false;
		var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
		var applianceFlag = false;
		var selectIPFlag = true;
		var vmPlugnInfoData;

		var authFun = [];
		var vmNameLimit = {}; //虚拟机名称长度限制
		var windowsFlag = false;
		var platformUuid = '';

		//初始化宿主机树
		var initHostTree = function(hypervisor){
			var p = {};
			p.hypervisor_type = hypervisor;
			p.one_hypersior_flag = true;
			//ics/ics-vvdk可相互迁移
			if (CONF.VM_TYPE.INCLOUDKVM == hypervisor || CONF.VM_TYPE.INSPURVVDK == hypervisor) {
				p.one_hypersior_flag = false;
			}
			pAjaxRequest(p, "/api/v1/vm/jobs/restore/platforms", "GET", function (d) {
				setHostTree(d.data.rows);
			}, false);
		}

		//初始化用户组树
		var initUsergroupTree = function(hypervisor){
			var p = {};
			p.hypervisor_type = hypervisor;
			pAjaxRequest(p, "/api/v1/vm/jobs/restore/user_group", "GET", function (d) {
				if (d.success) {
					//success
					setUsergroupTree(d.data);
				} else {
					operateResponseList(d);
				}
			})
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
			if ($("#diyHref_" + nodeTID + nodeID + "_1").length>0) return;
			var str = '<a id="diyHref_' + treeNode.tId + treeNode.id + '_1" title="' + LANG.UI_VCENTER_SYNC + '" class="treehref">' + LANG.UI_VCENTER_SYNC + '</a>' +
					  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_2" title="' + LANG.UI_BACKUP_TREE_EXPAND_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_EXPAND_ALL + '</a>' +
					  '<a id="diyHref_' + treeNode.tId + treeNode.id + '_3" title="' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL_DES + '" class="treehref">' + LANG.UI_BACKUP_TREE_COLLAPSE_ALL + '</a>';
			aObj.after(str);
//			aObj.append(str);
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

		var setHostTree = function(zNodes){
			if(!checkHostNodeInfo(zNodes, 'host_tree_div')) return;
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
					hostNodeExpand(treeId, treeNode);
					hostTree.expandNode(treeNode, true);
				}
			}

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
						beforeExpand: hostNodeExpand,
					}
				};
			hostTree = $.fn.zTree.init($("#host_tree"), setting, zNodes);
		};

		//设置用户分组树
		var setUsergroupTree = function(zNodes){
			if(!checkHostNodeInfo(zNodes, 'usergroup_tree_div')) return;
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
						beforeClick: vcenterNodeSelect,
						onCheck: vcenterOnCheck,
						beforeExpand: vcenterNodeExpand,
					}
				};
			usergroupTree = $.fn.zTree.init($("#usergroup_tree"), setting, zNodes);
		};

		//选择宿主机节点事件绑定
		var hostNodeSelect = function(treeId, treeNode, clickFlag){
			if(2 == treeNode.type){
				hostTree.checkNode(treeNode, !treeNode.checked, false, true);
			}else{
				hostNodeExpand(treeId, treeNode);
				hostTree.expandNode(treeNode, true);
			}
		}

		//恢复目的分组选择
		var vcenterNodeSelect = function(treeId, treeNode, clickFlag){
			if(2 == treeNode.type){
				usergroupTree.checkNode(treeNode, !treeNode.checked, false, true);
			}else{
				vcenterNodeExpand(treeId, treeNode);
				usergroupTree.expandNode(treeNode, true);
			}
		}
		//恢复目的分组被选中
		var vcenterOnCheck = function(e, id, node){
			var allNodes = usergroupTree.getCheckedNodes(true);
			$(".leixentransdiv").show();
			$('#leixentransmode').val(2);						//默认lanfree传输
			$(".threadnumdiv").show();				 //显示线程数量
//			$('.appliancediv').show();			     //显示proxy代理模块
			for(var i = 0; i < allNodes.length; i++){
				usergroupTree.checkNode(allNodes[i], false, false, false);
			}
			usergroupTree.checkNode(node, true, false, false);

			var selectNode = usergroupTree.getCheckedNodes(true);
			if("" == selectNode[0].username && "" == selectNode[0].password){
				//显示隐藏项目
				$('.vmdiv').hide();

				var groupusers = selectNode[0].groupusers;
				var groupusername = $("#groupusername");
				groupusername.empty();
				for(var i=0; i<groupusers.length; i++){
					var option = $("<option>").text(groupusers[i]).val(groupusers[i]);
					groupusername.append(option);
				}
				$('#grouppassword').val("");
				$(".groupdiv").show();
			}else{
				$(".groupdiv").hide();
				//显示隐藏项目
				$('.vmdiv').show();

				var data = JSON.stringify({vcenteruuid:selectNode[0].vcuuid, hypervisor:selectNode[0].hypervisor, groupname:selectNode[0].name,
					groupuuid:selectNode[0].groupuuid, username:selectNode[0].username, password:selectNode[0].password});
				initVMSettings(node);
				//初始化网络和存储
				initNetworkAndStoreGroupUser(selectNode[0]);
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
				Metronic.blockUI({target: '#usergroup_tree',animate: true});
				pAjaxRequest(p, "/api/v1/vm/platforms/groups", "GET", function (d) {
					Metronic.unblockUI('#usergroup_tree');
					if (d.success) {
						//success
						usergroupTree.addNodes(treeNode, d.data.rows, true);
					} else {
						operateResponseList(d);
					}
				});
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
			var hypervisor = parseInt($('#hypervisor').val());
			if(1 == hypervisor || 21 == hypervisor){
				$(".transportdiv").show();
			}
			$('#leixentransmode').prop('disabled', false);     //初始化传输模式状态
			// 传输压缩
			$('.transferCompressDiv').hide();
			$('#transferCompress').val('unzip');
			$(".threadnumdiv").show();
			$('.appliancediv').hide();			     			//隐藏proxy代理模块
			$('.applianceselectdiv').hide();					//隐藏选择proxy代理
			$('#appliancecheck').bootstrapSwitch('state', false);  //proxy默认关闭
			$('.intDiv').hide();	//隐藏数据传输网段
			$('#ipSegment').val('');
			$('#threadNum').prop('disabled', false); //启用线程数选择
			$('#threadNumDiv button').prop('disabled', false);
			switch(hypervisor){
				case CONF.VM_TYPE.VMWARE:
				case CONF.VM_TYPE.CLOUDVIEW:
				case CONF.VM_TYPE.CLOUDVIEWSVM:
					$('.appliancediv').show();			     	//隐藏proxy代理模块
					// 传输压缩
					$('.transferCompressDiv').show();
					$('#transferCompress').val('fastlz');
					break;
				case CONF.VM_TYPE.CITRIX:
				case CONF.VM_TYPE.XCPNG:
					$(".xentransdiv").show();
					$('.ipSegmentDiv').show();	//显示数据传输网段
					break;
				case CONF.VM_TYPE.RHV:
				case CONF.VM_TYPE.OVIRT:
				case CONF.VM_TYPE.ZVIRT:
				case CONF.VM_TYPE.OLVM:
				case CONF.VM_TYPE.HOSTVM:
				case CONF.VM_TYPE.REDVIRT:
				case CONF.VM_TYPE.ROSAVIRT:
					$(".redhattransdiv").show();
					$('.ipSegmentDiv').show();	//显示数据传输网段
					var  mode = $('#redhattransmode').val();
					if ((CONF.VM_TYPE.RHV == hypervisor
						|| CONF.VM_TYPE.HOSTVM == hypervisor
						|| CONF.VM_TYPE.REDVIRT == hypervisor
						|| CONF.VM_TYPE.ROSAVIRT == hypervisor
						|| CONF.VM_TYPE.OVIRT == hypervisor
					) && 2 == mode) {
						//ovirt SAN传输隐藏数据传输网段
						$('.ipSegmentDiv').hide();
					}
					break;
				case CONF.VM_TYPE.INCLOUD:
				case CONF.VM_TYPE.VGATE:
				case CONF.VM_TYPE.WINSERVER:
				case CONF.VM_TYPE.WINDIY:
				case CONF.VM_TYPE.DSERVER:
				case CONF.VM_TYPE.NEOKYLIN:
				case CONF.VM_TYPE.EASTEDVSERVER:
					$(".leixentransdiv").show();
					$('.ipSegmentDiv').show();	//显示数据传输网段
					break;
				case CONF.VM_TYPE.ZSTACK:
				case CONF.VM_TYPE.ZSTACKZSPHERE:
				case CONF.VM_TYPE.XSKY:
				case CONF.VM_TYPE.CLOUDVIEWKVM:
					$(".leixentransdiv").show();
					$('#leixentransmode').val(2);
					$('.ipSegmentDiv').hide();	//显示数据传输网段
					$('.encrypttransferdiv').hide();			//加密传输
					break;
				case CONF.VM_TYPE.OSEASYVSERVER:
				case CONF.VM_TYPE.WINHONGKVM:
				case CONF.VM_TYPE.SMARTX:
				case CONF.VM_TYPE.ARCFRA:
				case CONF.VM_TYPE.PROXMOX:
				case CONF.VM_TYPE.LENOVOAIO:
					$(".leixentransdiv").show();
					$('.ipSegmentDiv').hide();	//隐藏数据传输网段
					$("#leixentransmode").val(2);	//默认SAN传输
					//smartX暂时只支持lan-free传输
					if(hypervisor == CONF.VM_TYPE.SMARTX || hypervisor == CONF.VM_TYPE.ARCFRA){
						$("#leixentransmode").val(2).prop("disabled", true);
					}
					break;
				case CONF.VM_TYPE.FUSIONKVM:
				case CONF.VM_TYPE.XFUSIONKVM:
					$('.huaweikvmtransportdiv').show();					//华为kvm传输模式
					$('#huaweikvmtransport_mode').val('5').prop('disabled', true);			//默认AP_LAN
					//NBD传输显示备份系统IP参数
					var  mode = $('#huaweikvmtransport_mode').val();
					if(mode == 5){
						$('.systemIpdiv').show();	//备份系统IP
						$('.ipSegmentDiv').hide();	//隐藏数据传输网段
						$('#ipSegment').val('');
					}else{
						$('.systemIpdiv').hide();	//备份系统IP
						$('.ipSegmentDiv').show();	//显示数据传输网段
					}
					break;
				case CONF.VM_TYPE.FLEXCLOUD:
				case CONF.VM_TYPE.OPENSTACK:
				case CONF.VM_TYPE.FLEXHCS:
				case CONF.VM_TYPE.INCLOUDOPENSTACK:
				case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
				case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
				case CONF.VM_TYPE.EASYSTACK: //36
				case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
				case CONF.VM_TYPE.CTSIOPENSTACK: //38
				case CONF.VM_TYPE.AWCLOUD: //39
				case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
					$(".oepnstacktransdiv").show();
					$('#openstacktransmode').val(4);						//默认lanfree传输
					$('.ipSegmentDiv').hide();
//					$('.appliancediv').show();			     //显示proxy代理模块
					break;
				case CONF.VM_TYPE.HYPERV:
					$('#leixentransmode').prop('disabled', true);       //只支持网络传输
					$(".leixentransdiv").show();
					$(".threadnumdiv").hide();
					$('.ipSegmentDiv').show();
					break;
				case CONF.VM_TYPE.INCLOUDKVM:
				case CONF.VM_TYPE.H3C:
				case CONF.VM_TYPE.H3CCASCVD:
					$(".leixentransdiv").hide();
					$('.ipSegmentDiv').show();	//显示数据传输网段
					if (CONF.VM_TYPE.H3CCASCVD == hypervisor) {
						// cas_cvd 隐藏数据传输网段
						$('.ipSegmentDiv').hide();
					}
					$("#leixentransmode").val(1).prop('disabled', true);
					break;
				case CONF.VM_TYPE.FUSIONXEN:
					$("#huaweitransport_mode option[value='san']").remove();
					$(".huaweitransportdiv").show();
					$(".threadnumdiv").hide();
					break;
				case CONF.VM_TYPE.KVM:
				case CONF.VM_TYPE.SANGFOR:
				case CONF.VM_TYPE.SANGFORVVDK:
				case CONF.VM_TYPE.SDCOS:
					$('.ipSegmentDiv').show();
					break;
				case CONF.VM_TYPE.INSPURVVDK:
				case CONF.VM_TYPE.KSPHERE:
					$(".leixentransdiv").hide();
					$('.ipSegmentDiv').hide();	//隐藏数据传输网段
					$("#leixentransmode").val(1).prop('disabled', true);
			}
			$(".vmdiv").show();
			initVMSettings(node);
			//英文版本判断是否有多线程授权
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				if(!authFun.multithread){
					$('#threadNum').val(1).attr("disabled", true); //单线程
				}
			}


		}

		//初始化网络和存储
		var initNetworkAndStore = function(node){
			var p = {};
			p.hypervisor_type = $('#hypervisor').val();
			p.platform_uuid = node.vcuuid;
			p.host_uuid = node.id;
			networkflag = false;
			onlineflag = false;
			_HOSTSTORAGE = [];
			$('select[name=hoststorage]').empty();
			$('select[name=hostnetwork]').empty();
			$('select[name=disktype]').empty();
			Metronic.blockUI({target: '#accordionvmdiv',animate: true});
			pAjaxRequest(p, "/api/v1/vm/host/network_storage", "GET", function (d) {
				Metronic.unblockUI('#accordionvmdiv');
				var data = d.data;
				_HOSTSTORAGE = data;
				var hoststorage = $('select[name=hoststorage]');
				var disktype = $('select[name=disktype]');
				hoststorage.empty();
				disktype.empty();
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
				var typeInfo = '<option value="1">' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE + '</option><option value="2">' + LANG.UI_SETTING_DISK_TYPE_THICK_LAZY + '</option>'+
				'<option value="3">' + LANG.UI_SETTING_DISK_TYPE_THICK + '</option><option value="4">' + LANG.UI_SETTING_DISK_TYPE_THIN + '</option>';
				if(p.hypervisor == CONF.VM_TYPE.FUSIONXEN){
					typeInfo = '<option value="1">' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE + '</option><option value="2">' + LANG.UI_SETTING_DISK_TYPE_ORDINARY_LAZY + '</option>'+
					'<option value="3">' + LANG.UI_SETTING_DISK_TYPE_ORDINARY + '</option><option value="4">' + LANG.UI_SETTING_DISK_TYPE_HUAWEI_THIN + '</option>';
				}
				disktype.append(typeInfo);
				networkflag = true;
				if(data.network.length >1){
					onlineflag = true;
				}
	    	});
		}

		//替换特殊字符为下划线
		var clearString = function (s){
		    var rs = "";
		    for (var i = 0; i < s.length; i++) {
		        rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
		    }
		    return rs;
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
			onlineflag = false;
			networkflag = false;
			Metronic.blockUI({target: '#accordionvmdiv',animate: true});
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
					instance.append(option);
					instanceList.push(data.instance[i].uuid);
				}

				//选择虚拟机加载原来选择的网络
				var vmList = vmPlugnInfoData.config;
				for(var i=0;i<vmList.length;i++){
					var uuid = vmList[i].vm_uuid;
					var netList = vmList[i].net_list;
					//加载原虚拟机实例类型
					var diskList = vmList[i].disk_list;
					var cpuSocket = vmList[i].sockets;
					var memory = parseInt(vmList[i].memory_array.num);
					var rootDisk = parseInt(diskList[i].virtual_size_unit);
					var value = cpuSocket + "_" + memory + "_" + rootDisk + "_" + vmList[i].flavor_id;
					if($.inArray(value, instanceList) != -1){
						$('.'+clearString(uuid) + " select[name=instance_socket]").val(value);
					}
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
				$('.vmdiv').show();
				networkflag = true;
				if(data.network.length > 1){
					onlineflag = true;
				}
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
                        //success
                        hostTree.addNodes(treeNode, d.data.rows, true);
                    } else {
                        operateResponseList(d);
                    }
                });
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
						//success
						$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
						$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, d.data.rows, true);
						$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
					} else {
						operateResponseList(d);
					}
	    		});
			}else{
				return true;
			}
		}


		//检测用户输入
		var checkInfo = function(){
			var data = {};
			//宿主机信息
			data.hostInfo = {};

			var hypervisor = parseInt($('#hypervisor').val());
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)){
						//如果是openstack,检查项目组树
						var nodes = usergroupTree.getCheckedNodes(true);
						if(!nodes.length){
							UIToastr.showWarning(LANG.UI_MOTION_RECOVERY_HOST, LANG.UI_MOTION_RECOVERY_HOST_TIPS);
							return false;
						}
						if(!nodes[0].username){
							UIToastr.showWarning(LANG.UI_MOTION_VALIDATE_ADMIN, LANG.UI_MOTION_VALIDATE_ADMIN_TIPS);
							return false;
						}
						data.hostInfo.vcenteruuid = nodes[0].vcuuid;
						data.hostInfo.groupname = nodes[0].name;
						data.hostInfo.groupuuid = nodes[0].groupuuid;
						data.hostInfo.username = nodes[0].username;
						data.hostInfo.password = nodes[0].password;
						data.hostInfo.hypervisor = nodes[0].hypervisor;
					}else{
						//其他检测是否选择宿主机
						var hostNode = hostTree.getCheckedNodes(true);
						if(0 == hostNode.length){
							return UIToastr.showWarning(LANG.UI_INSTANT_SELECT_HOST, LANG.UI_INSTANT_SELECT_HOST_TIPS);
						}
						//宿主机信息
						data.hostInfo.vcenteruuid = hostNode[0].vcuuid;
						data.hostInfo.hostuuid = hostNode[0].id;
						data.hostInfo.hypervisor = hostNode[0].hypervisor;
					}
			//检测虚拟机名字
			data.vmName = $.trim($('input[name=vmname]').val());
			var flag = true;
			if('' == data.vmName){
				$('.setmotionVMname').hide();
				UIToastr.showWarning(LANG.UI_MOTION_INPUT_VMNAEM, LANG.UI_MOTION_INPUT_VMNAEM_TIPS);
				return;
			}
//			if(_VMNAMEREG.test(data.vmName)){
//				flag = false;
//				UIToastr.showWarning(LANG.UI_TOOLS_VMNAME_TIPS);
//				return false;
//			}
			data.startVMFlag = $('input[name=vmpower]').bootstrapSwitch('state');
			data.instantTaskUUID = $('#task_uuid').val();
			data.hypervisor = $('#hypervisor').val();
			//虚拟机配置
			data.vmconfigs = $('#accordionvm').getvmRecoveryConfig(vmPlugnInfoData);
			if(!data.vmconfigs){
				return false;
			}
			//线程数量
			data.threadNum = $('#threadNum').val();
			var thread = $('#threadNum').val();
			if(thread == "" || thread > 8 || thread <= 0){
				UIToastr.showWarning(LANG.UI_BACKUP_THREAD_NUM_TIPS);
				return false;
			}
			//传输模式
			data.transportmode = $('#transport_mode').val();
			// 传输压缩
			data.transfer_compress = $('#transferCompress').find('option:selected').val();
			// 传输加密
			data.encrypt_flag = $('#encrypttransfer').get(0).checked;
			// 传输加密算法
			data.encrypt_method = parseInt($('#transferEncryptMethod').val());

			data.appliancecheck = $('#appliancecheck').get(0).checked;
			var hypervisor = parseInt($('#hypervisor').val());
			switch(hypervisor){
				case CONF.VM_TYPE.CITRIX:
				case CONF.VM_TYPE.XCPNG:
					//如果是XenServer
					data.transportmode = parseInt($('#xentransmode').val());
					break;
				case CONF.VM_TYPE.INCLOUD:
				case CONF.VM_TYPE.VGATE:
				case CONF.VM_TYPE.WINSERVER:
				case CONF.VM_TYPE.WINDIY:
				case CONF.VM_TYPE.DSERVER:
					data.transportmode = parseInt($('#leixentransmode').val());
					break;
				case CONF.VM_TYPE.OLVM:
				case CONF.VM_TYPE.RHV:
				case CONF.VM_TYPE.OVIRT:
				case CONF.VM_TYPE.ZVIRT:
				case CONF.VM_TYPE.HOSTVM:
				case CONF.VM_TYPE.REDVIRT:
				case CONF.VM_TYPE.ROSAVIRT:
					data.transportmode = parseInt($('#redhattransmode').val());
					break;
				case CONF.VM_TYPE.NEOKYLIN:
				case CONF.VM_TYPE.OSEASYVSERVER:
				case CONF.VM_TYPE.INCLOUDKVM:
				case CONF.VM_TYPE.INSPURVVDK:
				case CONF.VM_TYPE.H3C:
				case CONF.VM_TYPE.H3CCASCVD:
				case CONF.VM_TYPE.ZSTACK:
				case CONF.VM_TYPE.ZSTACKZSPHERE:
				case CONF.VM_TYPE.EASTEDVSERVER:
				case CONF.VM_TYPE.XSKY:
				case CONF.VM_TYPE.WINHONGKVM:
				case CONF.VM_TYPE.SMARTX:
				case CONF.VM_TYPE.ARCFRA:
				case CONF.VM_TYPE.PROXMOX:
				case CONF.VM_TYPE.CLOUDVIEWKVM:
				case CONF.VM_TYPE.LENOVOAIO:
				case CONF.VM_TYPE.KSPHERE:
					data.transportmode = parseInt($('#leixentransmode').val());
					break;
				case CONF.VM_TYPE.FUSIONKVM:
				case CONF.VM_TYPE.XFUSIONKVM:
					data.transportmode = parseInt($('#huaweikvmtransport_mode').val());
					break;
				case CONF.VM_TYPE.FLEXCLOUD:
				case CONF.VM_TYPE.OPENSTACK:
				case CONF.VM_TYPE.FLEXHCS:
				case CONF.VM_TYPE.INCLOUDOPENSTACK:
				case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
				case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
				case CONF.VM_TYPE.EASYSTACK: //36
				case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
				case CONF.VM_TYPE.CTSIOPENSTACK: //38
				case CONF.VM_TYPE.AWCLOUD: //39
				case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
					data.transportmode = parseInt($('#leixentransmode').val());
					if(data.transportmode == 3){
						data.appliancecheck = true;
					}
					break;
				case CONF.VM_TYPE.FUSIONXEN:
					data.transportmode = $('#huaweitransport_mode').val();
					break;
			}
			//appliance
			data.applianceuuid = $('#applianceSelect').find('option:selected').val();
			if(!data.appliancecheck){
				data.applianceuuid = "";
			}else if(data.appliancecheck){
				if(!data.applianceuuid){
					UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
					return false;
				}
			}

			//华为KVM的NBD传输模式
			if((hypervisor == CONF.VM_TYPE.FUSIONKVM || hypervisor == CONF.VM_TYPE.XFUSIONKVM) && data.transportmode == 5){
				if(selectIPFlag){
					//自动选择
					data.backup_server_ip = $('#systemIp').val();
				}else{
					//自定义
					data.backup_server_ip = $('#inputIp').val();
				}
				//检查备份系统节点IP
				if("" == data.backup_server_ip){
					UIToastr.showWarning(LANG.UI_INSTANT_NFS_NO_IP, LANG.UI_INSTANT_NFS_NO_IP_TIPS);
					return false;
				}
			}else{
				//其余备份系统IP设为空
				data.backup_server_ip = "";
			}
			//数据传输网段
			data.transport_ip_segment = $('#ipSegment').val();
			if(!(data.transport_ip_segment =='' || data.transport_ip_segment == null || data.transport_ip_segment ==undefined)){
				var ipv4CheckRul = new RegExp(/((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3}\/\d{1,2}/);
				if(!ipv4CheckRul.test(data.transport_ip_segment)){
					UIToastr.showInfo(LANG.UI_STRATEGY_TRANSFER,LANG.UI_BACKUP_IPV4_TIPS);
					return false;
				}
			}

			//验证虚拟机配置,主要是存储使用
			var validate = $('#accordionvm').vmConfigValidate({vmconfig:data.vmconfigs, hoststorage:_HOSTSTORAGE, hypervisor: hypervisor, nameLimit:vmNameLimit});
			if(!validate){
				return false;
			}
			submit(data);
		}

		//提交信息
		var submit = function(info){
			Metronic.blockUI({target: '#motioncontent',animate: true, cenrerY: true,});
			pAjaxRequest(info, "/api/v1/vm/jobs/motion", "POST", function (d) {
				Metronic.unblockUI('#motioncontent');
				if (operateResponseList(d)) {
					LOCATION('./content/platform/jobs/jobs.php', 'task');
				}
			}, true);
		}

		var initListener = function(){
			//提交
			$('#submitbtn').on('click', function(){
				if(networkflag && onlineflag){
					checkInfo();
				}else if(!networkflag){
        			UIToastr.showWarning(LANG.UI_HOST_SETTING_GET_NETWORK_FAILURE,LANG.UI_HOST_SETTING_GET_NETWORK_FAILURE_TIPS);
        		}else if(!onlineflag){
        			UIToastr.showWarning(LANG.UI_HOST_SETTING_GET_NETWORK_FAILURE,LANG.UI_HOST_SETTING_GET_NETWORK_NOT_ONLINE_TIPS);
        		}
			});
			//取消
			$('#cancelbtn').on('click', function(){
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			});
			//跳转到添加虚拟化中心
			$('#toaddvcenter').on('click',function(){
		    	LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
			});

			$('#verifyuser').on('click', function(){
				event.preventDefault();
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
		    		if(operateResponseList(d)){
		    			//初始化虚拟机信息
		    			//显示隐藏项目
		    			$('.vmdiv').show();
		    			//更新节点账号密码信息,再次点击就不需要再输入了
		    			selectNode[0].username = username;
		    			selectNode[0].password = password;
		    			usergroupTree.updateNode(selectNode[0]);
		    			//初始化网络和存储
		    			initNetworkAndStoreGroupUser(selectNode[0]);
		    		}
		    	}, false);
			});

			//appliance开关切换回调
			$('#appliancecheck').on('switchChange.bootstrapSwitch', applianceChange);

			//openstack传输模式切换显示appliance
			$('#openstacktransmode').on('change', openstackModeChange);

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

			//华为KVM传输模式切换
			$('#huaweikvmtransport_mode').on('change', huaweikvmChange);

			//红帽传输模式切换
			$('#redhattransmode').on('change', redhatTransChange);

			//切换类xen传输模式
			$('#leixentransmode').on('change', leixentransChange);

			//切换vmware传输模式
			$('#transport_mode').on('change', vmwareTransChange);

			//输入线程数量检测
			$('#threadNum').blur(threadChange);

			// 传输策略---加密传输
			$('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);
		}

		var threadChange = function(){
			var num = this.value;
			if(num > 8 || num < 1 || num == ""){
				//还原默认值并给出提示
				$('#threadNum').val(3);
				UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			}
		}

		// 显示加密算法
		var transferEncryptChange = function(){
			if(this.checked){
				$('.transfer-encrypt-method-form').show();
			}else{
				$('.transfer-encrypt-method-form').hide();
			}
		}

		var leixentransChange = function(){
			var value = parseInt(this.value);
			var hypervisor = parseInt($('#hypervisor').val());
			//网络传输支持支持数据传输网段和加密传输
			if(value == 1){
				//云宏kvm展示没有数据传输网段配置
				if(hypervisor != CONF.VM_TYPE.WINHONGKVM || hypervisor != CONF.VM_TYPE.SMARTX || hypervisor != CONF.VM_TYPE.ARCFRA || hypervisor != CONF.VM_TYPE.PROXMOX){
					$('.ipSegmentDiv').show();	//显示数据传输网段
				}
				$('.encrypttransferdiv').show();	//加密传输
			}else{
				$('.ipSegmentDiv').hide();	//隐藏数据传输网段
				$('#ipSegment').val('');
				$('.encrypttransferdiv').hide();	//加密传输
			}
			switch(hypervisor){
				case CONF.VM_TYPE.FLEXCLOUD:
				case CONF.VM_TYPE.OPENSTACK:
				case CONF.VM_TYPE.FLEXHCS:
				case CONF.VM_TYPE.INCLOUDOPENSTACK:
				case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
				case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
				case CONF.VM_TYPE.EASYSTACK: //36
				case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
				case CONF.VM_TYPE.CTSIOPENSTACK: //38
				case CONF.VM_TYPE.AWCLOUD: //39
				case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
					$('.ipSegmentDiv').hide();	//显示数据传输网段
					break
			}

			//ICSlanfree恢复只支持单线程数恢复
			if(value == 2 && hypervisor == CONF.VM_TYPE.INCLOUDKVM){
				$('#threadNum').val(1).prop('disabled', true);
				$('#threadNumDiv button').prop('disabled', true);
			}else{
				$('#threadNum').prop('disabled', false);
				$('#threadNumDiv button').prop('disabled', false);
			}
		}

		var redhatTransChange = function(){
			var value = parseInt(this.value);
			//SAN传输不显示传输网段
			if(value == 2){
				$('.ipSegmentDiv').hide();	//隐藏数据传输网段
				$('#ipSegment').val('');
			} else {
				$('.ipSegmentDiv').show();
			}
		}

		var huaweikvmChange = function(){
			var value = parseInt(this.value);
			$('.systemIpdiv').hide();	//备份系统IP
			//API LAN无加密传输
			switch(value){
				case 1:
					$('.ipSegmentDiv').show();				//显示传输网段
					break;
				case 5:
					$('.systemIpdiv').show();	//备份系统IP
					$('.ipSegmentDiv').hide();				//隐藏传输网段
					$('#ipSegment').val('');
					break;
			}
		}

		//openstack传输模式改变
		var openstackModeChange = function(){
			var value = $(this).val();
			if(3 == value){
				$('.applianceselectdiv').show();
				if(!applianceFlag){
					initApplianceSelect();
				}
			}else{
				$('.applianceselectdiv').hide();
			}
			//网络传输支持支持数据传输网段和加密传输
			if(value == 1){
				$('.ipSegmentDiv').show();	//显示数据传输网段
				$('.encrypttransferdiv').show();	//加密传输
			}else{
				$('.ipSegmentDiv').hide();	//隐藏数据传输网段
				$('#ipSegment').val('');
				$('.encrypttransferdiv').hide();	//加密传输
			}
		}

		var vmwareTransChange = function () {
			var value = $(this).val();
			if ('hotadd' == value && windowsFlag) {
				$('#vmware_hotadd_tips').show();
			} else {
				$('#vmware_hotadd_tips').hide();
			}
			if (['nbd', 'nbdssl'].includes(this.value)) {
				// nbd模式显示传输压缩并开启异步传输
				$('.transferCompressDiv').show();
				$('#transferCompress').val('fastlz');
			} else {
				$('.transferCompressDiv').hide();
				$('#transferCompress').val('unzip');
			}
		}

		var applianceChange = function(){
			let _hypervisor = parseInt($('#hypervisor').val());
			if(this.checked){
				initApplianceSelect();
				$('.applianceselectdiv').show();
				if (CONF.VM_TYPE.VMWARE == _hypervisor) {
					// vmware开启传输代理可配置加密传输
					$('.encrypttransferdiv').show();	//加密传输
				}
			}else{
				$('.applianceselectdiv').hide();
				if (CONF.VM_TYPE.VMWARE == _hypervisor) {
					$('#encrypttransfer').bootstrapSwitch('state', false);
					$('.encrypttransferdiv').hide();	//加密传输
					$('.transfer-encrypt-method-form').hide();	//加密传输算法
				}
			}
		}
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			if(4 == CONF.SOFTWARE) {
				    $('.ipSegmentDiv').hide();  //隐藏数据传输网段
				}
		}

		//初始化appliance下拉框
		var initApplianceSelect = function(){
			pAjaxRequest({platform_uuid: platformUuid}, "/api/v1/nodes/appliance", "GET", function (d) {
				let data = d.data.appliance;
				let applianceSelect = $('#applianceSelect');
				applianceSelect.empty();
				if (!data.length) return;
				for (let i = 0; i < data.length; i++) {
					let option = '<option value="' + data[i].value + '">' + data[i].text + '</option>';
					applianceSelect.append(option);
				}
			});
		}

		//初始化虚拟机名字和任务名
		var initVMSettings = function(node){
			var hypervisor = parseInt($('#hypervisor').val());
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)){
				//如果是openstack,检查项目组树
				var nodes = usergroupTree.getCheckedNodes(true);
			}else{
				var nodes = hostTree.getCheckedNodes(true);
			}
			var data = {};
			data.hypervisor_type = parseInt($('#hypervisor').val());
			data.platform_uuid = nodes[0].vcuuid;
			data.host_uuid = nodes[0].id;
			data.job_uuid = $('#task_uuid').val();
			pAjaxRequest(data, "/api/v1/vm/jobs/motion/config", "GET", function (d) {
				var data = d.data;
				if(!data.flag){
					//如果获取失败
					return UIToastr.showWarning(LANG.UI_VM_GET_VIRTUAL_CONFIG_INFO, LANG.UI_VM_GETINFO_FAIL_TRY_AGIN);
				}
				vmPlugnInfoData = data;
				vmNameLimit = data.vmNameLimit;
				windowsFlag = data.windows_flag;
				$('#accordionvm').vmRecoveryConfig(data);
				$('#vm0').collapse('toggle');	//展开

				if(CONF.VMTYPE_GROUP.OPENSTACK.includes(data.hypervisor)){
					 $('.diskth').removeClass("display-none");
					 $('.vmdisktype').removeClass("display-none");
					 $('.vmstorage').removeClass("storage-active");
				 }

				//初始化网络和存储
				if(CONF.VMTYPE_GROUP.OPENSTACK.includes(data.hypervisor)){
					initNetworkAndStoreGroupUser(node);
					$('.ipDiv').show();
				}else{
					initNetworkAndStore(node);
					$('.ipDiv').hide();
				}
	    	}, false);

			// 初始化传输代理
			platformUuid = data.vcenteruuid;
			initApplianceSelect();
		}

		//初始化目的地树
		var initDesTree = function(){
			var hypervisor = parseInt($('#hypervisor').val());
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)){
				//如果是openstack,初始化项目组树
				initUsergroupTree(hypervisor);
				$('#selectHost').hide();
				$('#selectUsergroup').show();
			}else{
				//其他初始化宿主机树
				initHostTree(hypervisor);
				$('#selectHost').show();
				$('#selectUsergroup').hide();
			}

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
				if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
					authFun = data;
				}
				if(!data.lanfree){
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
			}, false);
		}

		var initSpinner = function(){
	        $('#threadNumDiv').spinner({value:3, step: 1, min: 1, max: 8});
		}

		//初始化备份系统节点IP
		var initBackupServerAddr = function(){
			var data = {};
			data.node_uuid = '';
			data.job_uuid = $('#task_uuid').val();
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
			var motionForm = $('#motionform');

			motionForm.validate({
				errorElement: 'span', //default input error message container
				errorClass: 'help-block help-block-error', // default input error message class
				focusInvalid: false, // do not focus the last invalid input
				ignore: "",  // validate all fields including form hidden input
				rules: {
					vmname: {
						required: true,
						newvmname: true,
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
		};

	    return {
	        //main function to initiate the module
	        init: function () {
	        	initSoftwareVersionDiff();
	        	initDesTree();
	        	initListener();
	        	initSpinner();
	        	initBackupServerAddr();	//初始化备份系统节点IP
				handleValidation();
	        }

	    };

	}();

	jQuery(document).ready(function() {
		VMMotion.init();
	});