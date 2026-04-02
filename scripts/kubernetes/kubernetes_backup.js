var K8sBackup = function () {
	var resultData = {};
    var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
    var last_cluster_uuid = "";
    var EditFlag = false; //是否是修改
	var oldInfo = {};
	var zTreeAgent = null;
	var pvc_all_data = []; //存储PVC状态
	var has_network = true; //默认是有传输网络的
	
	var defaultConfig = {
        dom: $('#backupStrategyDiv'),
        mode: [1, 2],
		reserve_mode_chain_flag: true,
    };

	//K8s备份类型
	 //任务备份类型
	 var K8S_BY_TYPE ={
		UNKNOWN: 0,
		NAMESPACE: 1,
		APPLICATION: 2
	 };
	 //k8s时间点type类型, 这个是由前端定义的展开层级的type类型,和后端没关系
	 var K8S_TYPE ={
		//集群
		CLUSTER: 0,
		//命名空间
		NAMESPACE: 1,
		//app
		APPLICATION: 2,
		//大的分组
		BIG_GROUP: 3,
		//小分组
		SMALL_GROUP: 4,
		//资源
		RESOURCE: 5,
		//详请
		RESOURCE_DETAIL: 6

	 }


    var getOldInfo = function(){
		//需要判断是否是修改,如果是修改则需要初始化数据
		//获取uuid是否有值,如果有值则表示是修改
		var task_uuid = $("#uuid").val();
		// var task_uuid = '60ef9cb1-16e1-47c4-99d3-4a47ebd024f0';
		if(task_uuid != "" && task_uuid != undefined && task_uuid != null){
			EditFlag = true;
			//获取备份任务的信息
			var requestBackupInfo = function(d){
				console.log(d);
				oldInfo = d.data;
				//新建备份任务
				initInfo(oldInfo);
			}
			pAjaxRequest({job_uuid:task_uuid}, "/api/v1/kubernetes/jobs/"+task_uuid+"/backupinfo", "GET", requestBackupInfo,true);
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
		// //初始化第三步
		initThird(oldInfo);
		// //初始化第四步
		initFourth(oldInfo);

	}

//--------------------------------------初始化第一步内容----------------------------------------------------------------------
var initFirst = function(oldInfo){
	



    var initFisrtListener = function(oldInfo){
		if(EditFlag){
			//初始化备份方式
			$("#k8s_backup_type").val(oldInfo.other.by_type);
		}
		//备份模式改变事件
		$("#k8s_backup_type").on('change',function (){
			initAgentTree(oldInfo);
		});

        //查看已选择持久卷事件
		$("#pvcSelected").on('click',function (){
			//获取表格所有数据, 这里的数据需要深拷贝
			pvc_all_data = JSON.parse(JSON.stringify($("#pvcs_table").bootstrapTable('getData')));
			console.log(pvc_all_data);
			$('#pvcsmodal').modal({'width':'800px', 'height':'512px'});
		})

		$("#cancel_PVC,#close_pvc_table").on('click',function (){
			
			//获取表格所有数据
			//根据pvc_all_data的数据更新表格勾选状态
			//将所有表格数据勾选状态
			$('#pvcs_table').bootstrapTable('checkAll');
			for(var i = 0; i < pvc_all_data.length; i++){
				//根据uuid判断是否选中
				if(!pvc_all_data[i].checkbox){
					//选中
					$('#pvcs_table').bootstrapTable('uncheckBy', {field: 'id', values: [pvc_all_data[i].id]});
				}
			}
			updatePvcTableNum();
		})

		//搜索可选内容
		$('#searchKube').on('input propertychange',function(){
			searchAgent();
		})


    }

	var searchAgent = function() {
		var value = $('#searchKube').val(); // 获取输入值并转换为小写
		// 输入验证
		var nodes = zTreeAgent.getNodes();
		if (!nodes || nodes.length == 0) return;
	
		// 隐藏所有节点
		zTreeAgent.hideNodes(nodes);
	
		// 找到匹配的节点集合
		let nodeParamList = zTreeAgent.getNodesByParamFuzzy('name', value);
		//如果没搜索到则给出提示
		if(nodeParamList.length == 0){
			$('#agent_div').hide();
			$('#noKube').show();
			return;
		}else{
			$('#agent_div').show();
			$('#noKube').hide();
		}
		// 找到父节点并添加到展示中，但不添加父节点的其他子节点
		var findParent = function(treeObj, node) {
			var pNode = node.getParentNode();
			if (pNode != null) {
				nodeParamList.push(pNode);
				// 隐藏该父节点下除当前节点外的其他子节点
				if (pNode.children && pNode.children.length > 0) {
					for (var i = 0; i < pNode.children.length; i++) {
						if (pNode.children[i] !== node) {
							zTreeAgent.hideNode(pNode.children[i]);
						}
					}
				}
		
				// 继续向上查找
				findParent(treeObj, pNode);
			}
		};
	
		for (var n in nodeParamList) {
			findParent(zTreeAgent, nodeParamList[n]);
		}
		// 排序
		nodeParamList = $.unique(nodeParamList.sort());
		// 展示找到的节点
		zTreeAgent.showNodes(nodeParamList);

	};


    



    //初始化备份源树形结构
	var initAgentTree = function(){
		var requestClusterZtree = function(d){
			//初始化树
			setTree(d.data);
		}
		//初始化为按应用备份
		pAjaxRequest({}, "/api/v1/kubernetes/cluster/ztree", "GET", requestClusterZtree,true);
		//清除内容已选中的所有内容
		$("#allK8sTree").empty();
		//清除表格所有数据
		$('#pvcs_table').bootstrapTable('load', []);
		//清除已选择持久卷
		$("#pvc_num").text("0/0");
		

	};



    var setTree = function(zNodes){
		var setting = {
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
			view: {
				nameIsHTML: true
			},
			callback: {
				beforeClick: beforeClickListener,
				beforeExpand: beforeExpandListener,
				onCheck: onCheckListener,
				// onExpand: beforeClickListener,
			}
		};
		zTreeAgent = $.fn.zTree.init($("#agent_tree"), setting,zNodes);
		
		if(EditFlag){
			//展开集群----
			//找到cluster_uuid为cluster_uuid的节点
			var cluster_nodes = zTreeAgent.getNodesByParam("id", oldInfo.other.cluster_uuid);
			//找到唯一的节点后展开该节点
			zTreeAgent.expandNode(cluster_nodes[0], true, false, false,true);
			



			var expandNamespace = function(){
				//展开命名空间----
				//获取命名空间的集合
				var namespace_list = oldInfo.other.namespace_list;
				//循环命名空间
				for(var i=0; i<namespace_list.length; i++){
					//找到cluster_uuid为cluster_uuid的节点
					var namespace_nodes = zTreeAgent.getNodesByParam("id", namespace_list[i], cluster_nodes[0]);
					//找到唯一的节点后展开该节点
					zTreeAgent.expandNode(namespace_nodes[0], true, false, false, true);
				}

			}
		}
	};

    //tree点击事件
	var beforeClickListener = function (treeId, treeNode, clickFlag){
		//如果当前没有子级则不能展开
		if(treeNode.children == undefined && treeNode.type !=K8S_TYPE.RESOURCE){
			return;
		}
		nodeRequest(treeId, treeNode);
		//获取展示应用类型
		var get_namespace_type = $("#k8s_backup_type").val();
		//判断是否有子节点 false 没有 true表示有
		var havechildren = false;
		if(treeNode.children == undefined || treeNode.children.length == 0){
			havechildren = false;
		}else{
			havechildren = true;
		}
		//获取树对象
		var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
		//展开
		if((get_namespace_type == K8S_BY_TYPE.NAMESPACE && treeNode.type == K8S_TYPE.NAMESPACE) || (get_namespace_type == K8S_BY_TYPE.APPLICATION && treeNode.type == K8S_TYPE.APPLICATION)){
			treeObj.expandNode(treeNode);
		}
	}

    //tree勾选事件
	var beforeExpandListener = function (treeId,treeNode){
		nodeRequest(treeId, treeNode);
		$(".vm_tree_div").show();
	}

    //勾选事件
	var onCheckListener = function(event, treeId, treeNode){
		//获取树对象
		var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
		//获取展示应用类型
		var get_namespace_type = $("#k8s_backup_type").val();
		if((get_namespace_type == K8S_BY_TYPE.NAMESPACE && treeNode.type ==  K8S_TYPE.NAMESPACE) || 
		(get_namespace_type == K8S_BY_TYPE.APPLICATION && treeNode.type ==  K8S_TYPE.APPLICATION) ||
		(treeNode.type ==  K8S_TYPE.BIG_GROUP  && treeNode.category_description ==  "CLUSTER")){
			//在勾选前判断是否勾选到其他集群了,如果勾选到其他集群了则清空所有勾选项
			//这里和最后一个勾选的集群做对比,如果集群uuid不一样就表示换了集群了
			if(last_cluster_uuid != "" && last_cluster_uuid != treeNode.cluster_uuid){
				//清除内容已选中的所有内容
				$("#allK8sTree").empty();
				//清除表格所有数据
				$('#pvcs_table').bootstrapTable('load', []);
				//清楚所有集群勾选状态,该方法不会触发回调事件
				treeObj.checkAllNodes(false);
				treeObj.checkNode(treeNode, true, false);
			}
			//获取节点勾选状态
			addResourceList(treeId,treeNode);
		}
		
	}

    
	//展开事件
	var expandListener = function(){
	}

    //所有节点相关异步请求事件
	var nodeRequest = function (treeId,treeNode){
		//获取展示应用类型
		var get_namespace_type = $("#k8s_backup_type").val();
		//判断是否有子节点 false 没有 true表示有
		var havechildren = false;
		if(treeNode.children == undefined || treeNode.children.length == 0){
			havechildren = false;
		}else{
			havechildren = true;
		}
		//获取树对象
		var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
		//type定义
		// 0为集群
		// 1为命名空间
		// 2为app
		// 3为大的资源分类
		// 4为大的资源分类下面的小分类
		// 5资源
		//get_namespace_type 
		//获取命名空间-----第二层
		if(treeNode.type == K8S_TYPE.CLUSTER && !havechildren){
			Metronic.blockUI({target: '.lockBackupDiv',animate: true,cenrerY: true,});
			//如果是集群层级,则调用接口获取命名空间
			var cluster_uuid = treeNode.cluster_uuid;
			var requestClusterNameSpace = function(d){
				treeObj.addNodes(treeNode, d.data, false);
				if(EditFlag){
					//展开命名空间----
					//找到cluster_uuid为cluster_uuid的节点
					var cluster_nodes = zTreeAgent.getNodesByParam("id", oldInfo.other.cluster_uuid);
					//如果没找到则不执行后续内容
					if(cluster_nodes.length == 0) return;
					var namespace_list = oldInfo.other.namespace_list;
					//循环命名空间
					for(var i=0; i<namespace_list.length; i++){
						//找到cluster_uuid为cluster_uuid的节点
						var namespace_nodes = zTreeAgent.getNodesByParam("namespace", namespace_list[i], cluster_nodes[0]);
						//如果没找到则不执行后续内容
						if(namespace_nodes.length == 0) continue;
						if(oldInfo.other.by_type == K8S_BY_TYPE.NAMESPACE){
							//如果是命名空间备份则勾选该命名空间并触发勾选动作
							zTreeAgent.checkNode(namespace_nodes[0], true, true, true);
						}else{
							//按应用备份则展开该命名空间
							//找到唯一的节点后展开该节点
							zTreeAgent.expandNode(namespace_nodes[0], true, false, true, true);
						}
					}
					//需要初始化集群资源的勾选选项
					if(oldInfo.other.resource_list && oldInfo.other.resource_list == 'CLUSTER'){
						//如果就集群资源被勾选
						//先找到集群资源
						var cluster_resource_nodes = zTreeAgent.getNodesByParam("id", "Cluster_Resources", cluster_nodes[0]);
						//如果没找到则不执行后续内容
						if(cluster_resource_nodes.length == 0){
							//为0不执行
						}else{
							//直接勾选该节点
							zTreeAgent.checkNode(cluster_resource_nodes[0], true, true, true);
						}
					}



					//勾选完了命名空间后需要处理pvc持久卷的勾选状态
					if(oldInfo.other.by_type == K8S_BY_TYPE.NAMESPACE){
						//处理持久卷勾选操作
						//将表格所有勾选状态取消
						$('#pvcs_table').bootstrapTable('uncheckAll');
						let ids = [];
						for(let i=0;i<oldInfo.other.pvc_list.length;i++){
							ids.push(oldInfo.other.pvc_list[i].pvc+"_"+oldInfo.other.pvc_list[i].namespace);
						}
						//勾选表格中的id
						$('#pvcs_table').bootstrapTable('checkBy', {field: 'id', values: ids});
						updatePvcTableNum();
					}
				}
				// if(!EditFlag){
					Metronic.unblockUI('.lockBackupDiv');
				// }
				
			}
			let dataList = {};
			dataList.cluster_uuid = cluster_uuid;
			dataList.get_namespace_type = get_namespace_type;

			pAjaxRequest(dataList, "/api/v1/kubernetes/cluster/"+cluster_uuid+"/namespaces", "GET", requestClusterNameSpace,true);
		}
		//获取app-----第二(额外)层(app层)
		if(get_namespace_type == K8S_BY_TYPE.APPLICATION && treeNode.type == K8S_TYPE.NAMESPACE && !havechildren){
			Metronic.blockUI({target: '.lockBackupDiv',animate: true,cenrerY: true,});
			//如果是集群层级,则调用接口获取命名空间
			var cluster_uuid = treeNode.cluster_uuid;
			//是否异步
			//默认异步
			var sync_flag = true;
			var requestClusterApps = function(d){
				treeObj.addNodes(treeNode, d.data);
				Metronic.unblockUI('.lockBackupDiv');
				if(EditFlag){
					
					//勾选命名空间
					var app_list = oldInfo.other.app_list;
					//获取集群
					var cluster_nodes = zTreeAgent.getNodesByParam("id", oldInfo.other.cluster_uuid);
					//如果没找到则不执行后续内容
					if(cluster_nodes.length == 0) return;
					//循环遍历app_list
					for(var i=0; i<app_list.length; i++){
						//获取命名空间节点
						var namespace_nodes = zTreeAgent.getNodesByParam("namespace", app_list[i].namespace, cluster_nodes[0]);
						//如果没找到则不执行后续内容
						if(namespace_nodes.length == 0) continue;
						//找到cluster_uuid为cluster_uuid的节点
						var app_nodes = zTreeAgent.getNodesByParam("app", app_list[i].app, namespace_nodes[0]);
						//如果没找到则不执行后续内容
						if(app_nodes.length == 0) continue;
						//找到唯一的节点后勾选该节点
						zTreeAgent.checkNode(app_nodes[0], true, false, true);
					}
					//勾选完了命名空间后需要处理pvc持久卷的勾选状态
					if(oldInfo.other.by_type == K8S_BY_TYPE.APPLICATION){
						//处理持久卷勾选操作
						//将表格所有勾选状态取消
						$('#pvcs_table').bootstrapTable('uncheckAll');
						let ids = [];
						for(let i=0;i<oldInfo.other.pvc_list.length;i++){
							ids.push(oldInfo.other.pvc_list[i].pvc+"_"+oldInfo.other.pvc_list[i].namespace);
						}
						//勾选表格中的id
						$('#pvcs_table').bootstrapTable('checkBy', {field: 'id', values: ids});
						updatePvcTableNum();
					}


				}
			}
			let dataList = {};
			dataList.cluster_uuid = cluster_uuid;
			dataList.get_namespace_type = get_namespace_type;
			dataList.namespace = treeNode.namespace;
			pAjaxRequest(dataList, "/api/v1/kubernetes/cluster/"+cluster_uuid+"/applications", "GET", requestClusterApps,sync_flag);
		}
		//获取命名空间下资源分类-----第三层
		if(((treeNode.type == K8S_TYPE.NAMESPACE && get_namespace_type == K8S_BY_TYPE.NAMESPACE) || (treeNode.type == K8S_TYPE.APPLICATION && get_namespace_type == K8S_BY_TYPE.APPLICATION)) && !havechildren){
			Metronic.blockUI({target: '.lockBackupDiv',animate: true,cenrerY: true,});
			//如果是集群层级,则调用接口获取命名空间
			var cluster_uuid = treeNode.cluster_uuid;
			var requestClusterBig = function(d){
				treeObj.addNodes(treeNode, d.data, false);
				if(EditFlag){
					//展开命名空间----
					//找到cluster_uuid为cluster_uuid的节点
					var cluster_nodes = zTreeAgent.getNodesByParam("id", oldInfo.other.cluster_uuid);
					//如果没找到则不执行后续内容
					if(cluster_nodes.length == 0) return;
					var namespace_list = oldInfo.other.namespace_list;
					//循环命名空间
					for(var i=0; i<namespace_list.length; i++){
						//找到cluster_uuid为cluster_uuid的节点
						var namespace_nodes = zTreeAgent.getNodesByParam("id", namespace_list[i], cluster_nodes[0]);
						//如果没找到则不执行后续内容
						if(namespace_nodes.length == 0) continue;
						//找到唯一的节点后展开该节点
						zTreeAgent.expandNode(namespace_nodes[0], true, false, false, true);
					}
				}
				Metronic.unblockUI('.lockBackupDiv');
			}
			let dataList = {};
			dataList.cluster_uuid = cluster_uuid;
			dataList.namespace = treeNode.namespace;
			dataList.get_namespace_type = get_namespace_type;
			dataList.app = treeNode.app ?? "";
			dataList.app_type = treeNode.app_type ?? 0;

			pAjaxRequest(dataList, "/api/v1/kubernetes/cluster/"+cluster_uuid+"/resource_big_group", "GET", requestClusterBig,true);

		}
		//获取大分组下小分组-----第四层
		if(treeNode.type == K8S_TYPE.BIG_GROUP && !havechildren){
			Metronic.blockUI({target: '.lockBackupDiv',animate: true,cenrerY: true,});
			//如果是集群层级,则调用接口获取命名空间
			var cluster_uuid = treeNode.cluster_uuid;
			var requestClusterGroup = function(d){
				treeObj.addNodes(treeNode, d.data);
				Metronic.unblockUI('.lockBackupDiv');
			}
			let dataList = {};
			//获取集群uuid
			dataList.cluster_uuid = cluster_uuid;
			//获取备份方式
			dataList.get_namespace_type = get_namespace_type;
			//获取命名空间
			dataList.namespace = treeNode.namespace;
			//获取app名称
			dataList.app = treeNode.app ?? "";
			//获取应用类型
			dataList.app_type = treeNode.app_type ?? 0;
			//获取组类别
			dataList.category = treeNode.category_description;
			pAjaxRequest(dataList, "/api/v1/kubernetes/cluster/"+cluster_uuid+"/resource_group", "GET", requestClusterGroup,true);
		}

		//获取小分组下资源-----第五层
		if(treeNode.type == K8S_TYPE.SMALL_GROUP && !havechildren){
			Metronic.blockUI({target: '.lockBackupDiv',animate: true,cenrerY: true,});
			//如果是集群层级,则调用接口获取命名空间
			var cluster_uuid = treeNode.cluster_uuid;
			var requestClusterresource = function(d){
				treeObj.addNodes(treeNode, d.data);
				Metronic.unblockUI('.lockBackupDiv');
			}
			let dataList = {};
			//获取集群uuid
			dataList.cluster_uuid = cluster_uuid;
			//获取备份方式
			dataList.get_namespace_type = get_namespace_type;
			//获取命名空间
			dataList.namespace = treeNode.namespace;
			//获取app名称
			dataList.app = treeNode.app;
			//获取app类型
			dataList.app_type = treeNode.app_type;
			//获取大分组
			dataList.category = treeNode.category;
			//获取分组名称
			dataList.group = treeNode.group;
			//获取小分组所属分组
			dataList.version = treeNode.version;
			//获取小分组所属版本
			dataList.kind = treeNode.kind_name;
			pAjaxRequest(dataList, "/api/v1/kubernetes/cluster/"+cluster_uuid+"/resource", "GET", requestClusterresource,true);
		}

		//请求资源内容-----第六层
		if(treeNode.type == K8S_TYPE.RESOURCE){
			Metronic.blockUI({target: '.lockBackupDiv',animate: true,cenrerY: true,});
			//如果是集群层级,则调用接口获取命名空间
			var cluster_uuid = treeNode.cluster_uuid;
			var requestClusterresourceDetails = function(d){
				//获取主题内容高度
				var body_height = $("#resourceDetailsDrawer .drawer-body").height();
				//得到代码框的高度
				var code_msg_height = body_height-10*2;
				$("#resourceDetails").css('height',code_msg_height+"px");
				$("#resourceDetails").val(d.data);
				$("#resourceDetailsDrawer").drawer('show');
				Metronic.unblockUI('.lockBackupDiv');
			}
			let dataList = {};
			//获取集群uuid
			dataList.cluster_uuid = cluster_uuid;
			//获取备份方式
			dataList.get_namespace_type = get_namespace_type;
			//获取命名空间
			dataList.namespace = treeNode.namespace;
			//获取group
			dataList.group = treeNode.group;
			//获取版本信息
			dataList.version = treeNode.version;
			//获取类型
			dataList.kind = treeNode.kind;
			//获取名字
			dataList.name = treeNode.name;
			pAjaxRequest(dataList, "/api/v1/kubernetes/cluster/"+cluster_uuid+"/resource/details", "GET", requestClusterresourceDetails,true);
		}
	}

    //勾选添加虚拟机显示列表
	var addResourceList = function(id,node){
		$("#step1tips").hide();
		//每次勾选都把集群uuid存入变量中
		last_cluster_uuid = node.cluster_uuid;
		var info = "";
		var diskinfo = "";
		//先获取相关数据
		//获取集群uuid
		var cluster_uuid = node.cluster_uuid;
		//获取命名空间
		var namespace = node.namespace;
		//获取app名称
		var app = node.app;
		//获取node的pvcs数据
		var pvcs = node.pvcs;
		//获取lid
		var lid = cluster_uuid+"_"+namespace;
		if(app != ""){
			lid += "_"+app;
		}
		var liId = clearString(lid); //添加虚拟机每列ID
		if(node.checked){
			info +=
				'<li class="list-group-item popovers VMTips list-group-item__vm" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.name +'">' +
				'<a href="#disk' + liId + '" data-toggle="collapse" aria-expanded="true" id="disHref' + liId + '" style="display:flex;" class="accordion-toggle collapsed">' +
				'<div class="col1">' +
				'<div class="cont vmDetail">' +
				'<div class="cont-col1" style="padding-top: 4px;">' +
				'<div class="'+node.iconSkin+'" style="margin-top: 3px;"></div>' +
				'</div>' +
				'<div class="cont-col2">' +
				'<div class="desc list-one">' + node.name + '</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'<div class="col2  pull-right delete-list" style="position:absolute;right:0;width:30px;padding-top: 8px;">' +
				'<span class="del'+liId+'" >' +
				'<span class="col2-i">' +
				'<i class="viconfont vicon-guanbi"></i>' +
				'</span>' +
				'</span>' +
				'</div>' +
				'</a>' +
				'</li>';
			//把勾选的主机添加到右边去
			$('#allK8sTree').append(info);
			$('#' + escapeJquery(liId)).popover();	   //初始化tips

			//移除虚拟机显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				var treeObj = $.fn.zTree.getZTreeObj(id);
				$('.popover.in').remove();
				treeObj.checkNode(node,false,true);
				$('#' + escapeJquery(liId)).remove();
				$('#disk' + escapeJquery(liId)).remove();
				selectPvcFun(pvcs,false);
			});
			selectPvcFun(pvcs,true);
		}else{
			//检查node.id是否在虚拟化里面，如果在就要把对应的li移除
			$('#disk' + escapeJquery(liId)).remove();
			$('#' + escapeJquery(liId)).remove();
			selectPvcFun(pvcs,false);
		}
	}

    //添加或移除一条表格数据
	//flag为true是添加  为false为移除
	var selectPvcFun = function (pvcs,flag){
		if(flag){
			for (let i=0;i<pvcs.length;i++){
				let addTableIndex = {};
				addTableIndex.id = pvcs[i].name+"_"+pvcs[i].namespace;
				addTableIndex.pvc_namespace = pvcs[i].name+"_"+pvcs[i].namespace;
				addTableIndex.pvc = pvcs[i].name;
				addTableIndex.namespace = pvcs[i].namespace;
				addTableIndex.size = pvcs[i].size;
				addTableIndex.storage_class = pvcs[i].storage_class;
				addTableIndex.volume_mode = pvcs[i].volume_mode;
				addTableIndex.checkbox = true;
				$('#pvcs_table').bootstrapTable('append', addTableIndex);
			}
		}else{
			//移除表格内容
			var removeTableList = [];
			for (let i=0;i<pvcs.length;i++){
				removeTableList.push(pvcs[i].name+"_"+pvcs[i].namespace);
			}
			$('#pvcs_table').bootstrapTable('remove',{field: 'pvc_namespace', values: removeTableList});
		}
		updatePvcTableNum();
	}

	//更新表格数据
	var updatePvcTableNum = function(){
		//获取总的数量并更新数量
		var tableTotalRows = $('#pvcs_table').bootstrapTable('getData');
		//获取所有表格数目
		var num_all = parseInt(tableTotalRows.length);
		//获取已经勾选的数目
		var tableSelectRows = $('#pvcs_table').bootstrapTable('getSelections');
		var num_select = parseInt(tableSelectRows.length);
		var num_string = num_select +"/"+num_all;
		$("#pvc_num").text(num_string);
	}

    var initTablePvcs = function (){
		var options = {
			data: {rows:[],total:0},
			// idField: 'id',
			showColumns: false,
			pagination: false,
			resizable: false,
			// pageList:[5,10,25,50],
			showExport:false,
			onPostBody:function (){
				btnDisplayClass();
			},
			columns:[
				{
					checkbox:true,
					field: 'checkbox',
					sortable: false,
					formatter:function (value, row, index){
						if(value == true){
							return true;
						}else{
							return false;
						}
					}
				},
				{
					field: 'pvc',
					title: LANG.UI_KUBE_PVC_NAME,
					sortable: false,
					align: 'center',

				},
				{
					field: 'namespace',
					title: LANG.UI_KUBE_NAMESPACE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'storage_class',
					title: LANG.UI_KUBE_STORAGE_CLASS,
					sortable: false,
					align: 'center',
				},
				{
					field: 'size',
					title: LANG.UI_KUBE_CAPACITY_SIZE,
					sortable: false,
					align: 'center',
				},
			],
		};
		$('#pvcs_table').baseTableConfig().init(options);
	}

	var btnDisplayClass = function(){
		$("#pvcs_table input[name='btSelectItem'], input[name='btSelectAll']").on('change',function (){
			updatePvcTableNum();
		})
	}


	

	//初始化监听事件
    initFisrtListener(oldInfo);
	//初始化树
    initAgentTree(oldInfo);
	//初始化pvcs,只是初始化一个空表格
    initTablePvcs(oldInfo);
};

//--------------------------------------初始化第二步内容----------------------------------------------------------------------

var initSecond = function(oldInfo){
	if(EditFlag){
		$('#backupTarget').backupTarget({
			node_uuid: oldInfo.node.nodeuuid,
			node_pool_uuid: oldInfo.node.node_pool_uuid,
			storage_uuid: oldInfo.node.storageuuid,
			storage_pool_uuid: oldInfo.node.storage_pool_uuid,
			storage_pool_type: oldInfo.node.storage_pool_type,
			exclude_storage_type_list: [
				CONF.BD_STORAGE_TYPE.REMOTE,  // 备份目标默认不支持远程备份存储
				CONF.BD_STORAGE_TYPE.HUAWEICBR,  // 备份目标默认不支持华为CBR
				CONF.BD_STORAGE_TYPE.TAPE,  // 磁带
			]
		});
	}else{
		$('#backupTarget').backupTarget(
			{exclude_storage_type_list: [
				CONF.BD_STORAGE_TYPE.REMOTE,  // 备份目标默认不支持远程备份存储
				CONF.BD_STORAGE_TYPE.HUAWEICBR,  // 备份目标默认不支持华为CBR
				CONF.BD_STORAGE_TYPE.TAPE,  // 磁带
			]}
		);
	}
	 

}

//--------------------------------------初始化第三步内容----------------------------------------------------------------------
var initThird = function(oldInfo){
	let data = {};
	let strategy = {};
	//初始化通用策略
	var initCommonPolicy = function(oldInfo){
		if(EditFlag){
			data = oldInfo.time_strategy.data;
			// 组合策略信息
			strategy = { store: {}, reserve: {}, time: oldInfo.time_strategy, speedlimit: oldInfo.speed_strategy };
			strategy.store.storeInfo = oldInfo.storage_strategy;
			strategy.reserve.reserveInfo = oldInfo.reserved_strategy;
            defaultConfig.strategy = strategy;
			
		}
		//初始化策略选择
		$('.strategy-group-select-wrapper').strategyGroupSelector({module_type: CONF.MODULE_TYPE.KUBERNETES, defaultConfig: defaultConfig, backupStrategyDom: '#backupStrategyDiv'})
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
	}

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
		
		$('#transfer_threads').on('change',function (){
			if(this.value > 8 || this.value == "" || this.value == 0){
				$('#Socket-thread').spinner('value',3);
				$('#transfer_threads').val(3);
			}
		})
		//初始化一些切换事件
		$('#encrypt').on('switchChange.bootstrapSwitch', function(event, state){
			if(state){
				$(".encrypt_method_div").show();
			}else{
				$(".encrypt_method_div").hide();
			}
		});

		if(EditFlag){
			//初始化加密传输
			$("#encrypt").bootstrapSwitch('state', oldInfo.transfer_strategy.encrypt);
			//初始化加密算法
			$("#encrypt_method").val( oldInfo.transfer_strategy.encrypt_method);
			//初始化传输线程
			$('#Socket-thread').spinner('value', oldInfo.high_strategy.threadnum);
			$('#transfer_threads').val(oldInfo.high_strategy.threadnum);


		}

	}
	//初始化安全策略
	var initSecurityPolicy = function(oldInfo){
		if(CONF.FUNCTIONS.includes('integrity')){
			if(EditFlag){
				//完整性校验
				$('#completionConfig').completeDetectionBackup("col-md-3",
					oldInfo.safe_strategy.integrity_check_flag,
					CONF.MODULE_TYPE.KUBE,
					true,
					oldInfo.safe_strategy.integrity_info.integrity_check_strategy,
					oldInfo.safe_strategy.integrity_info.backup_integrity_check_full_error_policy,
					oldInfo.safe_strategy.integrity_info.backup_integrity_check_inc_error_policy
					);
			}else{
				//完整性校验
				$('#completionConfig').completeDetectionBackup(precent = 'col-md-3',flag = true,module_type=CONF.MODULE_TYPE.KUBE,reIncFlag = true, checkDay = CONF.CHECK_TIME.EVERY ,full = CONF.FULL_ABNORAL.REFULL,verification = CONF.OTHER_ABNORAL.REFULL)
			}
		}else{
			$("#completionConfig").hide();
		}
	}
	//初始化高级配置
	var initAdvanceConfig = function(){
		$('.keeplocalsnapshotsDiv').spinner({value: 1, step: 1, min: 0,max: 8});//集群本地保留PVC快照副本数
		$("#keeplocalsnapshots").on('change',function (){
			if(this.value > 8 || this.value == ""){
				$('#keeplocalsnapshotsDiv').spinner('value',1);
				$('#keeplocalsnapshots').val(1);
			}
		})
        	
		//初始化重试策略
		$('#retry_config').retryStrategy();

		if(EditFlag){
			//初始化存储数据块大小
			$('#storage_block_size').val(oldInfo.storage_strategy.blocksize);
			//初始化本地保留快照副本
			$('.keeplocalsnapshotsDiv').spinner('value',oldInfo.other.keep_snapshots);//集群本地保留PVC快照副本数
			$("#keeplocalsnapshots").val(oldInfo.other.keep_snapshots)
			//初始化重试策略
			 $('#retry_config').retryStrategy({'retry_strategy': oldInfo.retry_strategy},'edit');
			//初始化忽略快照异常
			$("#snapshotException").bootstrapSwitch('state', oldInfo.other.ignore_exception);
			//初始化脚本配置并触发change事件
			$("#hookstype").val(oldInfo.other.hook_type);


		}
	}
	//初始化脚本配置
	var initScriptConfig = function(oldInfo){
		//初始化4个插件
		let custom_script = "";
		let before = "";
		let after_failed = "";
		let after = "";
		if(EditFlag){
			custom_script = oldInfo.other.hook_script.custom_script;
			before = oldInfo.other.hook_script.before;
			after_failed = oldInfo.other.hook_script.after_failed;
			after = oldInfo.other.hook_script.after;
		}

		if(EditFlag && custom_script != ""){
			$(".custom_script_ace").initVinScript({
				class:"custom_script_ace",
				include_list:[3],
				script_details: custom_script,
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
		}else{
			$(".custom_script_ace").initVinScript({
				class:"custom_script_ace",
				include_list:[3],
				script_details: [
					{
						"script_method": "2",
						"script_type": 3,
						"script_uuid": "0",
						"script_content": "",
						"script_name":"",
					}
				],
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
			
		}

		if(EditFlag && before != ""){
			$(".before_script_ace").initVinScript({
				class:"before_script_ace",
				include_list:[1],
				script_details: before,
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
		}else{
			$(".before_script_ace").initVinScript({
				class:"before_script_ace",
				include_list:[1],
				script_details: [
					{
						"script_method": "1",
						"script_type": 1,
						"script_uuid": "0",
						"script_content": "",
						"script_name":"",
					}
				],
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
		}

		if(EditFlag && after_failed != ""){
			$(".after_script_failure_ace").initVinScript({
				class:"after_script_failure_ace",
				include_list:[1],
				script_details: after_failed,
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
		}else{
			$(".after_script_failure_ace").initVinScript({
				class:"after_script_failure_ace",
				include_list:[1],
				script_details: [
					{
						"script_method": "1",
						"script_type": 1,
						"script_uuid": "0",
						"script_content": "",
						"script_name":"",
					}
				],
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
		}

		if(EditFlag && after != ""){
			$(".after_script_success_ace").initVinScript({
				class:"after_script_success_ace",
				include_list:[1],
				script_details: after,
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
		}else{
			$(".after_script_success_ace").initVinScript({
				class:"after_script_success_ace",
				include_list:[1],
				script_details: [
					{
						"script_method": "1",
						"script_type": 1,
						"script_uuid": "0",
						"script_content": "",
						"script_name":"",
					}
				],
				display_tab:"display:none;",
				display_script_method:"display:none;",
				display_script_type:"display:none;",
				display_script_content:"display:none;",
				display_script_list:"display:none;",
			});
		}


		//初始化hooks事件
		var initHooksShow = function (){
			//得到Hook的值
			var hooktype = $("#hookstype").val();
			switch (hooktype){
				case "0":
					$(".shDiv").hide();
					$(".csDiv").hide();
					$(".custom_div").hide();
					$(".sh_div").hide();
					break
				case "SH":
					$(".shDiv").show();
					$(".csDiv").hide();
					$(".custom_div").hide();
					$(".sh_div").show();
					break
				case "CUSTOMSCRIPT":
					$(".shDiv").hide();
					$(".csDiv").show();
					$(".custom_div").show();
					$(".sh_div").hide();
					break
			}
		}
		//执行容器环境事件
		var initinPodShow = function (){
			//得到执行容器环境的值
			var inPod = $("#inPod_mode").val();
			switch (inPod){
				case "ALL":
					$(".specified_pod").hide();
					$(".matched_pod").hide();
					$(".image_pod").hide();
					break;
				case "SPECIFIED":
					$(".specified_pod").show();
					$(".matched_pod").hide();
					$(".image_pod").hide();
					break;
				case "MATCHED":
					$(".specified_pod").hide();
					$(".matched_pod").show();
					$(".image_pod").hide();
					break;
				case "CUSTOM_IMAGE":
					$(".specified_pod").hide();
					$(".matched_pod").hide();
					$(".image_pod").show();
					break;
			}
		}
		//初始化hooks脚本
		$("#hookstype").on('change',function (){
			initHooksShow();
		})
		//执行容器事件
		$("#inPod_mode").on('change',function (){
			initinPodShow();
		})
		//初始化HOOK执行环境配置
		if(EditFlag){
			//获取hook类型
			if(oldInfo.other.hook_type == 1){
				$("#hookstype").val("SH");
			}else if(oldInfo.other.hook_type == 2){
				$("#hookstype").val("CUSTOMSCRIPT");
			}else{
				$("#hookstype").val("0");
			}
			initHooksShow();
			//获取执行环境
			let hook_run_in_pods = oldInfo.other.hook_run_in_pods;
			let mode = hook_run_in_pods.mode;
			let mode_params = hook_run_in_pods.params;
			$("#inPod_mode").val(mode);
			initinPodShow();
			switch (mode){
				case "ALL":
					break;
				case "SPECIFIED":
					$("#specified_namespaces").val(mode_params.namespace);
					$("#specified_pod").val(mode_params.pod);
					$("#specified_container").val(mode_params.container);
					break;
				case "MATCHED":
					$("#matched_namespaces").val(mode_params.namespace);
					$("#matched_pod").val(mode_params.pod);
					$("#matched_label").val(mode_params.label);
				case "CUSTOM_IMAGE":
					$("#image_images").val(mode_params.image);
					$("#image_namespaces").val(mode_params.namespace);
					break;
				default:
					//如果没有则初始化为ALL
					$("#inPod_mode").val("ALL");
					break;
			}
		}
	}


	//初始化通用策略
	initCommonPolicy(oldInfo);
	//初始化传输策略
	initTransferPolicy(oldInfo);
	//初始化安全策略
	initSecurityPolicy(oldInfo);
	//初始化高级配置
	initAdvanceConfig(oldInfo);
	//初始化脚本配置
	initScriptConfig(oldInfo);

}

//--------------------------------------初始化第四步内容----------------------------------------------------------------------
var initFourth = function(oldInfo){

	var requestData  = function(data){
		if(data.success){
			//获得任务名
			$("#jobname").val(data.data);
			
		}else{
			UIToastr.showWarning(LANG.UI_KUBE_GET_BACKUP_JOB_NAME, data.message);
		}
	}
	if(EditFlag){
		$("#jobname").val(oldInfo.taskname);
	}else{
		pAjaxRequest({}, "/api/v1/kubernetes/jobs_backup_name", "GET", requestData, true);
	}
	

}

//--------------------------------------获取第一步消息内容----------------------------------------------------------------------
var getStep1Msg = function(){
	
	




		//初始化数据
		resultData.backup_src_info = {};
		//基本数据
		//获取树形结构勾选的数据
		var treeObj = $.fn.zTree.getZTreeObj("agent_tree");
		var selectedNodes = treeObj.getCheckedNodes();
		if(selectedNodes == "" || selectedNodes == null || selectedNodes == undefined || selectedNodes.length == 0){
			UIToastr.showInfo(LANG.UI_KUBE_SELECT_BACKUP_SOURCE, LANG.UI_KUBE_SELECT_AT_LEAST_ONE_RESOURCE);
			return false;
		}
		//得到集群uuid
		var cluster_uuid = selectedNodes[0]['cluster_uuid'];
		//优先判断授权
		var getKubeAuth = function(){
			var requestList = {};
			requestList.type = "a";
			requestList.module = "k8s";
			requestList.uuids = [cluster_uuid]; //当前选择的集群uuid集合
			var  task_uuid = $("#uuid").val();
			if(task_uuid != "" && task_uuid != undefined && task_uuid != null){
				requestList.task_uuid = "";
			}else{
				requestList.task_uuid = task_uuid;
			}
			var checkFlag = false;
			var findRootNode = function(node) {
				while (node && node.type != 0) {
					node = node.getParentNode();
				}
				return node;
			}
			var requestAuth = function(d){
				if(d.success){
					var ayuthData = d.data;
					if(ayuthData.auth_type == 2){
						checkFlag = true; 
						return; //容量授权
					} 
					//获取总共数量
					var total = parseInt(ayuthData.total);
					if(total == -1){
						checkFlag = true; 
						return; //容量授权
					}
					//获取已使用数量
					var used = parseInt(ayuthData.used);
					//获取当前集群节点数
					//获取当前ztree节点
					var Nodes_ztree = selectedNodes[0];
					//获取当前节点的父级节点一直到type=0为止
					var clusterNode = findRootNode(Nodes_ztree);
					var nodes = parseInt(clusterNode['not_master_node']);
					//获取当前一共应该使用的数量
					var total_used = used + nodes;
					if(total_used > total){
						UIToastr.showWarning(LANG.UI_KUBE_GET_KUBERNETES_AUTH, LANG.UI_KUBE_CLUSTER_NODES_EXCEED_LICENSE);
						checkFlag = false;
					}else{
						checkFlag = true;
					}
					//授权是否到期
					if(!ayuthData.license_flag){
						UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
						checkFlag = false;
					}
				}else{
					UIToastr.showWarning(LANG.UI_KUBE_GET_KUBERNETES_AUTH, d.message);
					checkFlag = false;
				}
			}
			//初始化为按应用备份
			pAjaxRequest(requestList, "/api/v1/system/auth/base_info", "GET", requestAuth,false);
			return checkFlag;
		}
		//获取k8s授权数量
		let k8sAuth = getKubeAuth();
		if(!k8sAuth){
			return false;

		}


		//得到备份方式
		var groupType = $("#k8s_backup_type").val();
		//获取资源数据
		var resources = [];
		for(let i=0; i<selectedNodes.length;i++){
			let resources_each = {};
			if(selectedNodes[i].type ==  K8S_TYPE.BIG_GROUP && selectedNodes[i].category_description ==  "CLUSTER"){
				//集群资源单独处理数据
				resources_each.namespace = "";
				resources_each.app = selectedNodes[i].app ?? "";
				resources_each.app_type = selectedNodes[i].app_type ?? 0;
				resources_each.group = selectedNodes[i].group;
				resources_each.version = "";
				resources_each.kind = selectedNodes[i].kind;
				resources_each.name = selectedNodes[i].category_description;
			}else{
				//其他app或者命名空间正常流程走
				resources_each.namespace = selectedNodes[i].namespace;
				if(groupType == K8S_BY_TYPE.APPLICATION){
					//按应用备份则有值
					resources_each.app = selectedNodes[i].app;
					resources_each.app_type = selectedNodes[i].app_type;
				}else{
					//按命名空间备份没有值
					resources_each.app = "";
					resources_each.app_type = 0;
				}

			}
			
			
			resources.push(resources_each);
		}
		//获取pvc数据
		var selectedPvcs = $('#pvcs_table').bootstrapTable('getData');
		//这儿不对勾选进行判定,即使没有勾选也可以
		var pvcs = [];
		if(selectedPvcs.length != 0){
			for (let j=0;j<selectedPvcs.length;j++){
				if(!selectedPvcs[j].checkbox) continue;
				let pvcs_each = {};
				pvcs_each.checkbox = selectedPvcs[j].checkbox;
				pvcs_each.namespace = selectedPvcs[j].namespace;
				pvcs_each.pvc = selectedPvcs[j].pvc;
				pvcs_each.name = selectedPvcs[j].pvc;
				pvcs_each.pvc_namespace = selectedPvcs[j].pvc_namespace;
				pvcs_each.size = selectedPvcs[j].size;
				pvcs_each.storage_class = selectedPvcs[j].storage_class;
				pvcs_each.volume_mode = selectedPvcs[j].volume_mode;
				let meta_list = {};
				meta_list.pvc_storage_class = selectedPvcs[j].storage_class;
				meta_list.pvc_volume_mode = selectedPvcs[j].volume_mode;
				meta_list.pvc_size_with_unit = selectedPvcs[j].size;
				pvcs_each.meta = meta_list;
				pvcs.push(pvcs_each);
			}
		}
		//最终获取
		//获取集群唯一标识
		resultData.backup_src_info.cluster_uuid = cluster_uuid;
		//获取备份方式
		resultData.backup_src_info.by_type = groupType;
		//获取勾选资源
		resultData.backup_src_info.resources = resources;
		//获取PVC数据
		resultData.backup_src_info.pvcs = pvcs;
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
	//初始化忽略节点限制
	initResourceLimit(backupTargetInfo.node_uuid_list);
	//初始化传输网络信息
	$(".transfernetworkDiv").show();
	if(backupTargetInfo.node_uuid != ""){
		if(EditFlag){
			$('#transferNetworkTree').transferNetwork({node_uuid: backupTargetInfo.node_uuid,
				network_uuid: oldInfo.transfer_strategy.network,
				network_pool_uuid: oldInfo.transfer_strategy.network_pool_uuid,
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
	let storage_worm_config = backupTargetInfo.storage_worm_config
	//获取是否打开
	let storage_worm_config_flag = storage_worm_config.flag;
	let worm_type = get_worm_type(storage_worm_config_flag);
	//如果存储有配置WORM保护
	if(EditFlag){
		//初始化WORM防护
		$('#wormConfig').wormProtectionBackup(worm_type,"col-md-3", false ,oldInfo.safe_strategy.worm_flag, oldInfo.safe_strategy.worm_protection_time);
		
	}else{
		$('#wormConfig').wormProtectionBackup(worm_type,"col-md-3",false,false, 7);
	}

	resultData.node_info = backupTargetInfo;
	return true
}
//--------------------------------------获取第三步消息内容----------------------------------------------------------------------

var getStep3Msg = function(){
	//获取通用策略----------------
	resultData.strategyInfo = $('#backupStrategyDiv').getBackupStrategy();
	if(!resultData.strategyInfo){
		return false;
	}
	//获取传输策略----------------
	resultData.transfer_strategy = {};
	
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
	//获取线程数
	resultData.thread_num = $("#transfer_threads").val();
	//安全策略配置--------------
	//获取WORM配置
	//获取是否打开
	let worm = "";
	if(resultData.node_info.storage_worm_config.flag){
		worm = $('#wormConfig').getWormProtectionSettings();
	}
	//获取病毒检测配置
	let virus_detection = "";
	//获取完整性校验配置
	let completion_check = "";
	if(CONF.FUNCTIONS.includes('integrity')){
		completion_check = $('#completionConfig').getCompleteDetectionBackup();
	}
	resultData.safe_config_strategy = safeData(worm,virus_detection,completion_check);
    //获取高级配置--------------
    resultData.advanced_strategy = {};

    //集群本地保留PVC快照副本数
    resultData.advanced_strategy.keep_snapshots = $("#keeplocalsnapshots").val();
    //忽略快照异常
	resultData.blocksize = $("#storage_block_size").val();
    resultData.advanced_strategy.ignore_exception_option = {
		'ignore_snapshot_exception': $("#snapshotException").get(0).checked,
	};
	//重试
	resultData.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
	if(!resultData.retry_strategy){
		return false;
	}
	//过载保护
	//获取过载保护
	resultData.ignore_resource_limiting_flag = $("#ignore_resource_limiting_flag").is(':checked');
	
    //Hooks脚本
    //获取hook脚本的选择值
    var hooksType = $("#hookstype").val();
    resultData.advanced_strategy.hooks = {};
    
    resultData.advanced_strategy.hooks.script_params = {};
    resultData.advanced_strategy.hooks.env = "";
    resultData.advanced_strategy.hooks.run_in_pods = {};
	const invalidWildcard  = /[*?]/;
    switch (hooksType){
        case "0":
			resultData.advanced_strategy.hooks.type = 0;
            break;
        case "SH":
            let inPod_mode = $("#inPod_mode").val();
			resultData.advanced_strategy.hooks.type = 1;
            resultData.advanced_strategy.hooks.run_in_pods.mode = inPod_mode;
            resultData.advanced_strategy.hooks.run_in_pods.params = {};
            switch (inPod_mode){
                case "ALL":
                    
                    break;
                case "SPECIFIED":
                    resultData.advanced_strategy.hooks.run_in_pods.params.namespace = $("#specified_namespaces").val();
                    resultData.advanced_strategy.hooks.run_in_pods.params.pod = $("#specified_pod").val();
					if(resultData.advanced_strategy.hooks.run_in_pods.params.namespace == ""||
						resultData.advanced_strategy.hooks.run_in_pods.params.pod == ""
					){
						UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_SPECIFIED_POD_NAMESPACE_POD_REQUIRED);
						return false;
					}
					//对这两个值进行正则效验不支持通配符*?
					
					if(invalidWildcard.test(resultData.advanced_strategy.hooks.run_in_pods.params.namespace)){
						UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_SPECIFIED_POD_NO_WILDCARD);
						return false;
					}
					if(invalidWildcard.test(resultData.advanced_strategy.hooks.run_in_pods.params.pod)){
						UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_SPECIFIED_POD_NO_WILDCARD);
						return false;
					}
                    resultData.advanced_strategy.hooks.run_in_pods.params.image = null;
                    resultData.advanced_strategy.hooks.run_in_pods.params.label = null;
                    resultData.advanced_strategy.hooks.run_in_pods.params.container = $("#specified_container").val();
                    break;
                case "MATCHED":
                    resultData.advanced_strategy.hooks.run_in_pods.params.namespace = $("#matched_namespaces").val();
                    resultData.advanced_strategy.hooks.run_in_pods.params.pod = $("#matched_pod").val();
					resultData.advanced_strategy.hooks.run_in_pods.params.label = $("#matched_label").val();
					if(resultData.advanced_strategy.hooks.run_in_pods.params.namespace == ""){
						UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_MATCHING_NAMESPACE_REQUIRED);
						return false;
					}
					if(resultData.advanced_strategy.hooks.run_in_pods.params.pod == "" && 
						resultData.advanced_strategy.hooks.run_in_pods.params.label == ""
					){
						UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_MATCHING_POD_OR_LABEL_REQUIRED);
						return false;
					}
                    resultData.advanced_strategy.hooks.run_in_pods.params.image = null;
                    resultData.advanced_strategy.hooks.run_in_pods.params.container = null;
                    break;
                case "CUSTOM_IMAGE":
                    resultData.advanced_strategy.hooks.run_in_pods.params.namespace = $("#image_namespaces").val();
                    resultData.advanced_strategy.hooks.run_in_pods.params.pod = null;
                    resultData.advanced_strategy.hooks.run_in_pods.params.image = $("#image_images").val();
					if(resultData.advanced_strategy.hooks.run_in_pods.params.namespace == ""||
						resultData.advanced_strategy.hooks.run_in_pods.params.image == ""
					){
						UIToastr.showWarning(LANG.UI_KUBE_SCRIPT_CONFIGURATION,LANG.UI_KUBE_EXEC_CONTAINER_CUSTOM_IMAGE_REQUIRED);
						return false;
					}
                    resultData.advanced_strategy.hooks.run_in_pods.params.label = null;
                    resultData.advanced_strategy.hooks.run_in_pods.params.container = null;
                    break;
            }
            resultData.advanced_strategy.hooks.script_params.before = $("#before_script_ace").getVinScript("before_script_ace");
			if(resultData.advanced_strategy.hooks.script_params.before === false) return false;
            resultData.advanced_strategy.hooks.script_params.after = $("#after_script_success_ace").getVinScript("after_script_success_ace");
			if(resultData.advanced_strategy.hooks.script_params.after === false) return false;
            resultData.advanced_strategy.hooks.script_params.after_failed = $("#after_script_failure_ace").getVinScript("after_script_failure_ace");
			if(resultData.advanced_strategy.hooks.script_params.after_failed === false) return false;
            break;
        case "CUSTOMSCRIPT":
			resultData.advanced_strategy.hooks.type = 2;
            resultData.advanced_strategy.hooks.script_params.custom_script = $("#custom_script_ace").getVinScript("custom_script_ace");
			if(resultData.advanced_strategy.hooks.script_params.custom_script === false) return false;
            resultData.advanced_strategy.hooks.env = $("#cs_env").val();
            break;
    }
    return true;
	


}





var getStep4Msg = function(){
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	//-----------------统一处理第四步骤显示,不再分开步骤处理显示,这里处理用新的数据格式去处理数据,抛弃以前老版本
		var dataList = resultData;
		//--------资源列表------------
		let resourceDes = "";
		//得到资源数据
		for(let i=0;i<dataList.backup_src_info.resources.length;i++){
			if(dataList.backup_src_info.resources[i].name == "CLUSTER" ){
				resourceDes += LANG.UI_KUBE_CLUSTER_RESOURCES;
				continue;
			}
			resourceDes += dataList.backup_src_info.resources[i].namespace;
			if(dataList.backup_src_info.resources[i].app != ""){
				resourceDes += "/" + dataList.backup_src_info.resources[i].app;
			}
			resourceDes += "\r\n"
		}
		//--------PVC持久卷------------
		let pvcsDes = "";
		if(dataList.backup_src_info.pvcs.length == 0){
			pvcsDes += LANG.UI_PUBLIC_NOTHING;
		}else{
			for(let i=0;i<dataList.backup_src_info.pvcs.length;i++){
				if(!dataList.backup_src_info.pvcs[i].checkbox) continue;
				pvcsDes += dataList.backup_src_info.pvcs[i].namespace + "/" + dataList.backup_src_info.pvcs[i].name + "\r\n";
			}
		}
		//--------资源过滤器------------
		//无

		//--------备份目的地------------
		//目标节点----------
		let nodeInfoShowHtml = "";
		//获取selectnode选中的option的text值
		nodeInfoShowHtml += dataList.node_info.node_text+"<br>";
		$('.nodeInfoShow').html(nodeInfoShowHtml);
		//目标存储----------
		let storageInfoShowHtml = "";
		//获取selectnode选中的option的text值
		storageInfoShowHtml += dataList.node_info.storage_text+"<br>";
		$('.storageInfoShow').html(storageInfoShowHtml);
		//--------备份策略/时间------------
		let backuptypeinfoDes = "";
		//获取selectnode选中的option的text值
		backuptypeinfoDes += dataList.strategyInfo.time.des;

		//--------存储策略------------
		let storeInfoDes = "";
		storeInfoDes += dataList.strategyInfo.store.des;
		//--------保留策略------------
		let reservetypeDes = '';
		reservetypeDes += dataList.strategyInfo.reserve.des;
		//--------传输策略------------
		let transferDes = "";
		transferDes += LANG.UI_KUBE_ENCRYPTION_TRANSFER + ": " + getSwitchDes(dataList.transfer_strategy.encrypt) + "<br>";
		if(dataList.transfer_strategy.encrypt){
            transferDes += LANG.UI_KUBE_ENCRYPTION_ALGORITHM+": "
            switch(parseInt(dataList.transfer_strategy.encrypt_method)){
                case 1: 
                    transferDes += "RSA";
                    break;
                case 2: 
                    transferDes += "SM2";
                    break;
                  default:
            }
            transferDes += "<br>";
        }
		transferDes += LANG.UI_KUBE_TRANSFER_NETWORK + ": " + dataList.transfer_strategy.network_name + "<br>";
		if(CONF.FUNCTIONS.includes('multithread')){
			transferDes += LANG.UI_KUBE_THREAD_COUNT + ": " +dataList.thread_num + "<br>";
		}
		//--------限速策略------------
		let speedDes = "";
		let speedInfo = dataList.strategyInfo.speedlimit.speedInfo;
		//循环speedInfo
		if(speedInfo.length == 0){
			speedDes = LANG.UI_PUBLIC_NOTHING
		}else{
			for(let i = 0; i < speedInfo.length; i++){
				speedDes += speedInfo[i].des+"<br>";
			}
		}
		//--------脚本配置------------
		let scriptDes = "";
		scriptDes += LANG.UI_K8S_HOOK_SCRIPT + ": " + $("#hookstype").find('option:selected').text();;
		//安全策略----------
		let safetyshowHtml = "";
		safetyshowHtml += LANG.UI_SAFE_STRATEGY_WORM_PROTECT+": "+ getSwitchDes(resultData.safe_config_strategy.worm_flag) +"<br>";
		if(Boolean(resultData.safe_config_strategy.worm_flag)){
			safetyshowHtml += LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD +": "+resultData.safe_config_strategy.worm_protection_time +" "+ LANG.UI_PUBLIC_UNIT_DAY +" <br>";
		}
		if(CONF.FUNCTIONS.includes('integrity')){
			let completion_check = $('#completionConfig').getCompleteDetectionBackup();
			safetyshowHtml += completion_check.des;
		}
		// $('.safetyshow').html(safetyshowHtml)
		// safetyshowHtml += LANG.UI_MACHINE_OS_INTEGRITY_VERIFY+": "+ getSwitchDes(resultData.safe_config_strategy.integrity_check_flag) +"<br>";
		
		//--------高级配置------------
		let highDes = "";
		highDes += LANG.UI_KUBE_BLOCK_SIZE + ": " +dataList.blocksize + "KB" + "<br>";
		highDes += LANG.UI_KUBE_LOCAL_PVC_SNAPSHOT_COPIES + ": " +dataList.advanced_strategy.keep_snapshots + "<br>";
		highDes += LANG.UI_KUBE_IGNORE_SNAPSHOT_ERRORS + ": " + getSwitchDes(dataList.advanced_strategy.ignore_exception_option.ignore_snapshot_exception)+ "<br>";
		//忽略节点资源限制
		highDes += LANG.UI_NODE_RESOURCE_LIMIT_IGNORE +": "+ getSwitchDes(dataList.ignore_resource_limiting_flag);
		//--------Hook------------


		//各种html上对应的对象
		//资源列表
		$("#resourceDesShow").html(resourceDes);
		//PVC持久卷
		$("#pvcsDesShow").html(pvcsDes);
		
		//备份策略/时间
		$("#backuptypeinfoDesShow").html(backuptypeinfoDes);
		//存储策略
		$("#storeInfoDesShow").html(storeInfoDes);
		//保留策略
		$("#reservetypeDesShow").html(reservetypeDes);
		//传输策略
		$("#transferDesShow").html(transferDes);
		//限速策略
		$("#speedDesShow").html(speedDes);
		//安全策略
		$('.safetyshow').html(safetyshowHtml)
		//高级配置
		$("#highDesShow").html(highDes);
		//脚本配置
		$("#scriptDesShow").html(scriptDes);



}


//--------------------------------------提交消息内容----------------------------------------------------------------------
var submit = function(){
	if('' == $.trim($("#jobname").val())){
		UIToastr.showWarning(LANG.UI_KUBE_SUBMIT_TASK, LANG.UI_BACKUP_NAME_TIPS);
		return;
	}
	let jobName = $.trim($("#jobname").val());
	// 输入验证
	if(!customInputValidate('string',jobName)){
		return false;
	}
	resultData.task_name = $.trim($("#jobname").val());
	resultData.strategygroupuuid = "";
	if(EditFlag){

		resultData.task_uuid = $("#uuid").val();
	}
	//TODO提交
	// console.log("最后提交的前端数据(在php还要转一遍):");
	console.log(resultData);
	Metronic.blockUI({target: '#k8sbackupcontent',animate: true,cenrerY: true,});
	var requestBackupJob = function(d){
		Metronic.unblockUI('#k8sbackupcontent');
		if(d.success){
			if(resultData.task_uuid){
				UIToastr.showSuccess(LANG.UI_KUBE_EDIT_CONTAINER_BACKUP_JOB, LANG.UI_KUBE_EDIT_CONTAINER_BACKUP_JOB_SUCCESS);
			}else{
				UIToastr.showSuccess(LANG.UI_KUBE_CREATE_CONTAINER_BACKUP_JOB, d.message);
			}
			LOCATION('./content/platform/jobs/jobs.php', 'task');
		}else{
			if(resultData.task_uuid){
				UIToastr.showWarning(LANG.UI_KUBE_EDIT_CONTAINER_BACKUP_JOB, LANG.UI_KUBE_EDIT_CONTAINER_BACKUP_JOB_FAILED);
			}else{
				UIToastr.showWarning(LANG.UI_KUBE_CREATE_CONTAINER_BACKUP_JOB, d.message);
			}
		}
	}
	pAjaxRequest(resultData, "/api/v1/kubernetes/jobs/backup", "POST", requestBackupJob,true);
}










    var step1Valid = function(){
        $result = getStep1Msg();
    
		return $result;
	}

    var step2Valid = function(){
        $result = getStep2Msg();
		return $result;
	}

    var step3Valid = function(){
        $result = getStep3Msg();
		//获取第四步骤显示
		getStep4Msg();
		return $result;
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
//            $('.step-title', $('#k8sbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
			// set done steps
			jQuery('li', $('#k8sbackupcontent')).removeClass("done");
			var li_list = navigation.find('li');
			for (var i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}
			if (current == 1) {
				$('#k8sbackupcontent').find('.button-previous').css('visibility','hidden');
				$('#k8sbackupcontent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#k8sbackupcontent').find('.button-previous').css('visibility','visible');
				$('#k8sbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
			}
			if (current >= total) {
				$('#k8sbackupcontent').find('.button-next').hide();
				$('#k8sbackupcontent').find('.button-submit').css('visibility','visible');
			} else {
				$('#k8sbackupcontent').find('.button-next').show();
				$('#k8sbackupcontent').find('.button-submit').css('visibility','hidden');
			}
			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#k8sbackupcontent').bootstrapWizard({
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
				$('#k8sbackupcontent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});
		$('#k8sbackupcontent').find('.button-previous').css('visibility','hidden');
		$('#k8sbackupcontent .button-submit').click(submit).css('visibility','hidden');
	};

//------------------其他-----------------
    //替换特殊字符
	var clearString = function (s){
		var rs = "";
		for (var i = 0; i < s.length; i++) {
			rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
		}
		return rs;
	}

    //去掉转义
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




	return {
		//main function to initiate the module
		init: function () {
            getOldInfo(); //初始化历史数据
			
        	wizardInit();
			
		},

	};

}();

jQuery(document).ready(function() {
	K8sBackup.init();
});