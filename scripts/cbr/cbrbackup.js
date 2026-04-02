var CBRBackup =  function(){
	var data = {srcInfo:{},backupInfo:{},highInfo:{}, speedInfo:{},verifyInfo:{},detail:{}};
	//定义三棵树:按存储库同步,按虚拟机同步,按时间点同步,搜索到的树节点
	var zTreeMP, zTreeVM, zTreeTP, nodeParamList;
	var currentTree, zTreeMPLoad = zTreeVMLoad = zTreeTPLoad = false;	//当前显示的树,三棵树是否已经加载过的标志
	var zTreeMPInit = false;var zTreeVMInit =  false;var zTreeTPInit  = false; //是否初始化三棵树
	var currentTreeDivId;	//用于搜索时候定位
	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag =  false //自定义节点类型选择加载
	var defaultStrategy = [];
	var initSpeedFlag = false;
	var speedList = [];
	var mplimit = 2; //存储库每页显示的数量
	var tplimit  = 2 ; //时间点每页显示的数量
	var vmExpandAllFlag =  false; //虚拟机是否全部展开
	var expandallflag  = false; //是否点击了全部展开
	var initgfsstrategy = false;
	var lastsynctype  =  ""; //上一次同步类型 在进入第三步时记录
	var instep3flag  =  false;
	var _oldPassword = '';
	var mpsearchflag = false;
	var vmsearchflag = false;
	var tpsearchflag =  false; //设置存储库、虚拟机、时间点搜索显示的标志
	//初始化备份流程数据
	var initData = function(){
		//step1 
		data.srcInfo.vminfo = [];
		data.srcInfo.uuid_list = [];
		data.srcInfo.backup_vm_uuids = [];
		//step2
		//备份方式:策略/时间
		data.backupInfo.type = 'strategy';
		data.backupInfo.fullInfo = {};
		//step3 
		//保留策略
		data.highInfo.node = {};
		data.highInfo.reserve = {};
		data.highInfo.reserve.type = 1;
		data.highInfo.mode = {};
		data.highInfo.transfer = {};//传输策略
		data.highInfo.store  = {};//存储策略
	}
	//备份任务步骤
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
			jQuery('li', $('#cbrbackupcontent')).removeClass("done");
			var li_list = navigation.find('li');
			for (var i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}
			//如果第一步 上一步按钮隐藏
			if (current == 1) {
				$('#cbrbackupcontent').find('.button-previous').css('visibility', 'hidden');
				$('#cbrbackupcontent').find('.button-next').addClass('next-btn-margin-left');
			} else {
				$('#cbrbackupcontent').find('.button-previous').css('visibility', 'visible');
				$('#cbrbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
			}
			
			//如果是最后一步
			if (current >= total) {
				$('#cbrbackupcontent').find('.button-next').hide();
				$('#cbrbackupcontent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#cbrbackupcontent').find('.button-next').show();
				$('#cbrbackupcontent').find('.button-submit').css('visibility', 'hidden');
			}
			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#cbrbackupcontent').bootstrapWizard({
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
			
			//上一步
			onPrevious: function (tab, navigation, index) {
				success.hide();
				error.hide();
				// 还原tab-pane的高度
				$(".tab-pane__row").css('height', '100%');
				handleTitle(tab, navigation, index);
			},
			
			//进度条显示
			onTabShow: function (tab, navigation, index) {
				var total = navigation.find('li').length;
				var current = index + 1;
				var $percent = (current / total) * 100;
				$('#cbrbackupcontent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});

		$('#cbrbackupcontent').find('.button-previous').css('visibility', 'hidden');
		$('#cbrbackupcontent .button-submit').click(submit).css('visibility', 'hidden');
	};
	//提交
	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		data.taskName = $.trim($("#jobname").val());
		var grain_type = 0
		switch(currentTreeDivId){
			case "cbr_tree_mp":
				grain_type = 1; 
				break;
			case "cbr_tree_vm":
				grain_type = 2; 
				break;
			case "cbr_tree_tp":
				grain_type = 3;
				break;
		}
		data.detail.sync_grain_type  = grain_type;
		if( data.verifyInfo.ischeck){
			data.detail.data_check_flag  =  1;
		}else{
			data.detail.data_check_flag  =  2;
		}
		//同步时间点个数
		data.detail.sync_backup_count = parseInt($('#backupSyncTimeNum').val()) ;
		//TODO提交
    	var jsonData = JSON.stringify(data);
    	Metronic.blockUI({target: '#cbrbackupcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'createCBRSyncJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#cbrbackupcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}
	//初始化监听
	var initListener =  function(){
		//节点改变
		$('#selectnode').on('change', initStorageSelect);
		// //目标存储改变
		// $('#selectstorage').on('change', initNodeSelect);
		//同步类型改变
		$('#backuptype').on('change', backupTypeHandler);
		//初始化添加限速策略模态框
		$('#addSpeedlimit').on('click', function(){
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}
		});
		//切换限速模式
		$('#speedModeType').on('change', speedModeHandler);

		//切换存储加密开关
		$('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);

		//切换自动选择存储加密密码
		$('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
		
		//数据加密密码确认
		$('#repassword').on('input propertychange', function(){
			checkPassword();
		});
		//数据加密密码输入
		$('#password').on('input propertychange', function(){
			checkPassword();
		});

		//切换GFS保留策略开关
		$('#GFSflag').on('switchChange.bootstrapSwitch', GFSChange);
		$('#toaddcbr').on('click',function(){
	    	LOCATION('./content/platform/storage/storage_add.php','storage_manager');
		});
		// 传输策略---加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);
	}
	// 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').show();
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}
	//初始化树
	var initMyTree =  function(){
		if(!zTreeMPInit){
			initTree(1);
			zTreeMPInit  = true;
		}
	}
	var initTree =  function(type){
		var data  = {};
		data.type =  type;
		data.backupflag  =  true;
		data.editflag =  false;
		data =  JSON.stringify(data);
		switch (type){
			case 1:
				$.post(CONF.AJAXPATH,{m:CONF.M.VM,f:'getCBRDetailsTreeNew',p:data},setMPTree);
				break;
			case 2:
				$.post(CONF.AJAXPATH,{m:CONF.M.VM,f:'getCBRDetailsTreeNew',p:data},setVMTree);
				break;
			case 3:
				$.post(CONF.AJAXPATH,{m:CONF.M.VM,f:'getCBRDetailsTreeNew',p:data},setTPTree);
				break;
		}
	}
	var inintDatatimePicker = function(){
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
	}
	//设置存储库的树
	var setMPTree =  function(zNodes){
		//如果
		if(zNodes == "[]"){
			$("#nodatatips").show();
			$('.VMList-div').hide();
			$(".three_tree").hide();
			$(".cbr_tree_div").hide();
			return;
		}else{
			//
			$("#nodatatips").hide();
			$(".three_tree").show();
			$(".cbr_tree_div").show();
		}
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
					title: "name"
				}

			},
			view: {
				fontCss: getFontCss,
				addDiyDom: addHoverDom,
			},
			callback:{
				beforeClick: nodeSelect, //根据返回值是否允许单机操作
				onCheck: mpOnCheck, //勾选或者取消勾选的事件回调函数
				onExpand:nodeExpand //捕获节点展开事件的回调函数 (展开之后的事件)
			}
		}
		//拿到备份节点
		var nodes = JSON.parse(zNodes);//拿到备份节点
		zTreeMP = $.fn.zTree.init($("#cbr_tree_mp"), setting, nodes);//第一种
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				var checkNode = zTreeMP.getNodesByParam("id", nodes[i].id, null);
				addMPList('cbr_tree_mp',checkNode[0]);
			}
		}
		currentTree = zTreeMP;
		currentTreeDivId = 'cbr_tree_mp';
	}
	//设置虚拟机的树
	var setVMTree =  function(zNodes){
		if(zNodes == "[]"){
			$("#nodatatips").show();
			$('.VMList-div').hide();
			$(".three_tree").hide();
			$(".cbr_tree_div").hide();
			return;
		}else{
			$("#nodatatips").hide();
			$(".three_tree").show();
			$(".cbr_tree_div").show();
		}
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
					title: "name"
				}
			},
			view: {
				fontCss: getFontCss,
				addDiyDom: addHoverDom,
			},
			callback:{
				beforeClick: nodeSelect, //根据返回值是否允许单机操作
				onCheck: mpOnCheck, //勾选或者取消勾选的事件回调函数
				onExpand:nodeExpand //捕获节点展开事件的回调函数
			}
		}
		//拿到备份节点
		var nodes = JSON.parse(zNodes);//拿到备份节点
		zTreeVM = $.fn.zTree.init($("#cbr_tree_vm"), setting, nodes);//第一种
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				var checkNode = zTreeVM.getNodesByParam("id", nodes[i].id, null);
				addMPList('cbr_tree_vm',checkNode[0]);
			}
		}
		currentTree = zTreeVM;
		currentTreeDivId = 'cbr_tree_vm';
	}
	//设置时间点的树
	var setTPTree =  function(zNodes){
		if(zNodes == "[]"){
			$("#nodatatips").show();
			$('.VMList-div').hide();
			$(".three_tree").hide();
			$(".cbr_tree_div").hide();
			return;
		}else{
			$("#nodatatips").hide();
			$(".three_tree").show();
			$(".cbr_tree_div").show();
		}
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
					title: "name"
				}
			},
			view: {
				fontCss: getFontCss,
				addDiyDom: addHoverDom,
			},
			callback:{
				beforeClick: nodeSelect, //根据返回值是否允许单机操作
				onCheck: mpOnCheck, //勾选或者取消勾选的事件回调函数
				onExpand:nodeExpand //捕获节点展开事件的回调函数
			}
		}
		//拿到备份节点
		var nodes = JSON.parse(zNodes);//拿到备份节点
		zTreeTP = $.fn.zTree.init($("#cbr_tree_tp"), setting, nodes);//第一种
		for(var i=0;i<nodes.length;i++){
			if(nodes[i].checked){
				var checkNode = zTreeTP.getNodesByParam("id", nodes[i].id, null);
				addMPList('cbr_tree_tp',checkNode[0]);
			}
		}
		currentTree = zTreeTP;
		currentTreeDivId = 'cbr_tree_tp';
	}

	//设置节点样式 
	var getFontCss = function(treeId, treeNode) {
		var css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.highlight){
			//搜索使用的样式
			css = {color:"#A60000", "font-weight":"bold"};
		}
		return css;
	}
	//添加云上数据鼠标指上去事件
	var addHoverDom  = function(treeId, treeNode){
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
				//全部展开下面一层（区域）都是有孩子
				if(treeId == "cbr_tree_vm"){
					vmExpandAllFlag  = true;
				}
				expandallflag =  true;
				nodeExpand(event,treeId,treeNode,true);
			});
			//点击收起
			if (hrefCollapse) hrefCollapse.bind("click", function(event){
				if(treeId == "cbr_tree_vm"){
					vmExpandAllFlag  = false;
				}
				expandallflag =  false;
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, false, true, true);
				
			});
		}	
	}
	//异步获取CBR的信息  refreshFlag是否重新刷新
	var getSyncCBRInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var div = ".three_tree";
		var data = {
			"storage_uuid_list":[{
				"storage_uuid":treeNode.id
			}],
			"type":parseInt($('#asyncshowtype').val()),
			"backupflag":true,
			"editflag":false
		}
		var datastr =  JSON.stringify(data);
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type:"post",
			url: CONF.AJAXPATH, 
	        async:true,
			data:{m:CONF.M.VM,f:'getSyncCBR',p:datastr},
			success: function(d){ 
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
							$('.addMPList').empty();
							currentTree.checkAllNodes(false);
						}
						var index = treeNode.getIndex();
						var storagenode =  treeNode.getParentNode();
						currentTree.removeNode(treeNode);
						currentTree.addNodes(storagenode,index,data,true);
						currentTree.expandNode(treeNode,true);
						setTreeLoadFlag();
						if(expendFlag == true){
							currentTree.expandNode(treeNode, true, true, true);
						}
					}
				}
				else{
					OPREL(d);
				}
			}
		});

	}
	//设置树的加载标志
	var setTreeLoadFlag = function(){
		if(currentTree == zTreeMP){
			zTreeMPLoad = true;
		}else if(currentTree == zTreeVM){
			zTreeVMLoad = true;
		}else if(currentTree == zTreeTP){
			zTreeTPLoad = true;
		}
		showSearchInput(true);
	}
	//刷新清空对应同步类型的列表
	var refreshCBRList  = function(id){
		getCurrentTree(id);
		var allNodes =  currentTree.getCheckedNodes();
		if (allNodes.length ==  0) return;
		if(id  ==  "cbr_tree_mp"){
			$('#addMPList li').remove();
		}
		if(id == "cbr_tree_vm"){
			$('#addVMList li').remove();
		}
		if(id == "cbr_tree_tp"){
			$('#addTPList li').remove();
		}
	}
	//得到当前树
	var  getCurrentTree  =  function(id){
		if(id == "cbr_tree_mp"){
			currentTree = zTreeMP;
		}
		if(id == "cbr_tree_vm"){
			currentTree  = zTreeVM;
		}
		if(id ==  "cbr_tree_tp"){
			currentTree =  zTreeTP;
		}
	}
	
	//选择节点
	var nodeSelect = function(treeId, treeNode, clickFlag){
		//如果是是名字 存储区域 直接展开
		//如果是项目 异步获取存储库那一层 
		//存储库 获取虚拟机
		//虚拟机 获取时间点
		//如果是是存储库 获取虚拟机
		if('project' ==  treeNode.eventtype){
			var children = treeNode.children;
			if(!children || !clickFlag){ //没有孩子或者是没勾选
				if(treeId == "cbr_tree_mp"){
					treeNode.nocheck = false;
					getMPSync(treeId,treeNode,false); //不需要获取虚拟机那一层
				}else if(treeId == "cbr_tree_vm"){
					treeNode.nocheck =  false;
					getMPSync(treeId,treeNode,true); //需要获取虚拟机那一层
				}else{ 
					//如果当前树是时间点 则不需要获取虚拟机
					getMPSync(treeId,treeNode,false);
				}
				// getMPSync(treeId,treeNode);
			}else{
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
			}
		}else if ('mp' == treeNode.eventtype || 'vm' == treeNode.eventtype || 'tp' ==  treeNode.eventtype){
			if('mp' == treeNode.eventtype){
				var children = treeNode.children;
				if(!children || !clickFlag){
					//如果是时间点 需要获取时间点
					if(treeId == "cbr_tree_tp" ){ 
						//存储库和项目都可以勾选
						treeNode.nocheck = false;
						var projectnode  = treeNode.getParentNode(); 
						projectnode.nocheck  =  false;
							getTPSync(treeId,treeNode);
					}
					else{
						//勾选上一层
						$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
					}
				}else{
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
				}
			}
			if('vm' == treeNode.eventtype ){
				var children = treeNode.children;
				if(!children || !clickFlag){
					//如果当前是时间点 则异步加载
					if('cbr_tree_tp' ==  treeId){
						treeNode.nocheck = false; //虚拟机
						var storagenode  = treeNode.getParentNode();//存储库
						var projectnode  = storagenode.getParentNode();//项目
						storagenode.nocheck  =  false;
						projectnode.nocheck =  false
						getTPSync(treeId,treeNode);
					}else{
						//勾选上一层
						$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
					}
				}else{
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开
				}
			}
			if('tp' == treeNode.eventtype){
				$.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, true, true);
			}
		}else if ('mploadmore' == treeNode.eventtype){
			//获取项目node
			var projectnode =  treeNode.getParentNode();
			// //存储库加载更多
			if(treeId == "cbr_tree_vm"){
				getMPSync(treeId,projectnode,true); //需要获取虚拟机那一层
			}else{
				//存储库和时间点不要
				getMPSync(treeId,projectnode,false); //不需要获取虚拟机那一层
			}
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
	//勾选存储库
	var mpOnCheck = function(e, id, node){
		var tree = $.fn.zTree.getZTreeObj(id);
		var allNodes = tree.getCheckedNodes(true);
		//判断是否在同一个存储
		var flag  = false;
		if(allNodes.length != 0){
			flag = existstorage(allNodes);
			if(flag){
				tree.checkAllNodes(false);
				tree.checkNode(node, !node.checked, true, false);
				UIToastr.showInfo(LANG.UI_VM_SELECT_STORAGE_DIFFERENT,LANG.UI_SYNC_CBR_SELECT_STORAGE_DIFFERENT_TIPS);
				moveRightAll(id); //清除右边所有的
			}
			
		}
		if((node.eventtype == "mp" && id == "cbr_tree_mp")||(node.eventtype == "vm" && id == "cbr_tree_vm")||(node.eventtype == "tp" && id == "cbr_tree_tp")){
			addMPList(id,node);
		}else{
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
	var moveRightAll = function(id){
		if(id=="cbr_tree_mp"){
			$('#addMPList li').remove();
			$('.diskList').remove();

		}
		if(id=="cbr_tree_vm"){
			$('#addVMList li').remove();
			$('.diskList').remove();
		}
		
		if(id=="cbr_tree_tp"){
			$('#addTPList li').remove();
			$('.diskList').remove();
		}
	}
	//节点展开 
	//flag为true表示点击了展开所有
	var nodeExpand = function(event,treeId, treeNode,flag=false){
		if(!treeNode.eventtype){ //如果是前面几层 不存在子节点不展开
			if(!treeNode.isParent) return; 
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
			if(treeNode.children && flag){
				var nodelist =  treeNode.children;
				for (let index = 0; index < nodelist.length; index++) {
					// $.fn.zTree.getZTreeObj(treeId).expandNode(nodelist[index], true,true,true,true,flag);
					nodeExpand(event,treeId,nodelist[index],true); //展开所有递归此方法
				}
			}
		}
		else if(treeNode.eventtype  == "project"){
			//如果不存在子节点 则获取存储库
			var children = treeNode.children;
			if(!children){
				// 如果树的类型是存储库或者虚拟机的话 则项目那一层加可选框
				if(treeId == "cbr_tree_mp" || treeId == "cbr_tree_vm"){
					treeNode.nocheck = false;
				}
				//如果是虚拟机获取存储库
				if(treeId == "cbr_tree_vm"){
					getMPSync(treeId,treeNode,true);
				}else{
					getMPSync(treeId,treeNode,false);
				}
			}
			if(children && flag){
				//得到存储节点

				//flag为true表示点击了展开全部
				// $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,true,true,true,flag);
				//如果是当前树是时间点 则如果能勾选就展开存储库 否则不展开
				if(treeId == "cbr_tree_tp"){
					if(treeNode.nocheck == false){
						$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,true,true,true,flag);
					}else{
						$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,false,true,true,flag);
					}
				}else{
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true,true,true,true,flag);
				}
				
			}
		}
		else if(treeNode.eventtype  == "mp"){
			//如果不存在子节点 则获取存储库
			var children = treeNode.children;
			if(!children){
				treeNode.nocheck = false;
				var projectnode  = treeNode.getParentNode();
				projectnode.nocheck  =  false;
				//如果是时间点类型，则获取时间点数据 则存储库那一层加可选框 项目那一层也需要加勾选
				if(treeId == "cbr_tree_tp"){
					getTPSync(treeId,treeNode);
				}
			}
			if(children && flag){
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
		if(treeNode.eventtype == "mp" && treeId == "cbr_tree_tp"){
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
		var div = ".three_tree";
		var showType = parseInt($('#asyncshowtype').val());
		var region_node =  treeNode.getParentNode(); //区域node
		// var region_id =  region_node.id;
		var storage_node = region_node.getParentNode(); //存储node
		var storage_id = storage_node.id;
		var storageregionid = region_node.id.split("_");
		var region_id  = storageregionid[1];
		
		var storageprojectid = treeNode.id.split("_");
		var project_id  = storageprojectid[1];
		var nextpage = getcurrentpage(treeId,treeNode); //获取即将要查询的页序号
		
		var jsondata =  {
			"storage_uuid":storage_id,
			"region_id":region_id,
			"project_id":project_id,
			"current_page":nextpage,//即将查询的页序号
			"limit":mplimit, //每页显示的数量
			"showtype":showType,
			"need_resource":needresource,
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
					//当树是虚拟机需要判断下一层级是否需要展开
					if(vmExpandAllFlag && treeId  == "cbr_tree_vm"){
						// $.fn.zTree.getZTreeObj(treeId).expandAll(true);
						$.fn.zTree.getZTreeObj(treeId).expandNode(storage_node, true,true);
					}
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
							"title":LANG.UI_VM_LOAD_MORE
						}
						$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, morenode, true);
					}
					//如果是存储库或者虚拟机  就展示搜索框
					if(treeId == "cbr_tree_mp"){
						mpsearchflag =  true
					}if(treeId == "cbr_tree_vm"){
						vmsearchflag =  true
					}
					if((mpsearchflag || vmsearchflag) && (treeId == "cbr_tree_mp"||treeId == "cbr_tree_vm" )){
						showSearchInput(true);
					}else{
						$('#searchcbr').val('').hide();
					}
				}else if(OPREL(d)){
					return;
				}
	        } 
		});
	}
	//异步获取时间点
	var getTPSync =  function(treeId,treeNode){
		var div = ".three_tree";
		//异步获取虚拟机时间点参数
		// //存储库
		// var storagenode = treeNode.getParentNode();
		// var storage_id = storagenode.id;
		//项目id
		var showType = parseInt($('#asyncshowtype').val());
		var project_node =  treeNode.getParentNode();
		// var project_id = project_node.id;

		var storageprojectid = project_node.id.split("_");
		var project_id  = storageprojectid[1];

		var region_node =  project_node.getParentNode(); //区域node
		// var region_id =  region_node.id;
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
				    // mpcurpage =  data.current_page;
					// mptotalpage = data.total_pages;
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
							"title":LANG.UI_VM_LOAD_MORE
						}
						$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, morenode, true);
					}
					//如果是时间点  就展示搜索框
					if(treeId == "cbr_tree_tp"){
						tpsearchflag =  true;
					}
					if(tpsearchflag){
						showSearchInput(true)
					}else{
						$('#searchcbr').val('').hide();
					}
				}else if(OPREL(d)){
					return;
				}
	        } 
		});
	}
	//添加存储库到右边列表
	var addChirdMPList =  function(id,children){
		if(!children && children.length == 0) return;
		for (var i = 0; i < children.length; i++) {
			if((children[i].eventtype == "mp" && id == "cbr_tree_mp")||(children[i].eventtype == "vm" && id == "cbr_tree_vm")||(children[i].eventtype == "tp" && id == "cbr_tree_tp")){
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
			var treeObj = $.fn.zTree.getZTreeObj(id);
			var nodes = treeObj.getCheckedNodes(true);
			//检查是否在同一个存储上
			// var sameflag  = checkSelectInSameStorage(treeObj,node,nodes);
			// if(sameflag){
			// 	//弹出弹框
			// }
			//根据CBR同步类型添加每一列到列表				
			if(id=="cbr_tree_mp"){
				info += 
				'<li class="list-group-item popovers VMTips list-group-item__vm" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +'">' + 
					'<a href="#disk' + liId + '" data-toggle="collapse" aria-expanded="true" id="disHref' + liId + '" style="display:flex;" class="accordion-toggle collapsed">' + 
						'<div class="col1" style="padding-left:0px;">' + 
							'<div class="cont">' + 
								'<div class="cont-col1" style="padding-top: 5px;">' + 
									'<div class="'+node.iconSkin+'"></div>' + 
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
				'</li>' + 
				'<ul id="disk' + liId + '" class="feeds collapse diskList" style="list-style:none;"></ul>';

				$('#addMPList').append(info);
				}else if(id=="cbr_tree_vm"){
					info += 
					'<li class="list-group-item popovers VMTips list-group-item__vm" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +'">' + 
						'<a href="#disk' + liId + '" data-toggle="collapse" aria-expanded="true" id="disHref' + liId + '" style="display:flex;" class="accordion-toggle collapsed">' + 
							'<div class="col1" style="padding-left:0px;">' + 
								'<div class="cont">' + 
									'<div class="cont-col1" style="padding-top: 5px;">' + 
										'<div class="'+node.iconSkin+'"></div>' + 
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
					'</li>' + 
					'<ul id="disk' + liId + '" class="feeds collapse diskList" style="list-style:none;"></ul>';

					$('#addVMList').append(info);
				}else if(id=="cbr_tree_tp"){
					info += 
					'<li class="list-group-item popovers VMTips list-group-item__recoverlist" id="' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ node.path +'">' + 
						'<div class="col1">' + 
							'<div class="cont vmDetail">' + 
								'<div class="cont-col1">' + 
									'<div style="width:20px;height:20px;background: url(./img/vm//Huawei_CBR/vm.png) 0 no-repeat;"></div>' + 
									// '<div class="'+node.iconSkin+'"></div>' + 
								'</div>' + 
								'<div class="cont-col2">' + 
									'<div class="desc list-one" style="font-size: 14px;color: #333;padding: 10px 4px 0px 4px">' + node.parentname + '</div>' + 
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

					$('#addTPList').append(info);
					var length = $('#addTPList>li').size();
					var timestr = LANG.UI_SYNC_CBR_SELECT_TIME_POINT+length+LANG.UI_SYNC_CBR_SELECT_TIME_POINT_NUM;
					$('.addTitle > span').html(timestr);
			}
			$('#' + escapeJquery(liId)).popover();	   //初始化tips
			//移除存储池显示
			$('.del'+escapeJquery(liId)).on('click', function(){
				var treeObj = $.fn.zTree.getZTreeObj(id);
				$('.popover.in').remove();
				treeObj.checkNode(node,false,true);
				$('#' + escapeJquery(liId)).remove();
				if(id=="cbr_tree_tp"){
					var length = $('#addTPList>li').size();
					var timestr = LANG.UI_SYNC_CBR_SELECT_TIME_POINT+length+LANG.UI_SYNC_CBR_SELECT_TIME_POINT_NUM;
					$('.addTitle > span').html(timestr);
				}
			});
		}else{
			//检查node.id是否在虚拟化里面，如果在就要把对应的li移除
			$('#' + escapeJquery(liId)).remove();
			if(id=="cbr_tree_tp"){
				var length = $('#addTPList>li').size();
				var timestr = LANG.UI_SYNC_CBR_SELECT_TIME_POINT+length+LANG.UI_SYNC_CBR_SELECT_TIME_POINT_NUM;
				$('.addTitle > span').html(timestr);
			}
		}
	}
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
	//转义字符串
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
	//初始化树形展示方式和事件
	var initSelectShowType = function(){
		//初始化BS select控件
		$('#asyncshowtype').selectpicker({
			iconBase: 'fa',
			tickIcon: 'fa-check'
		});
		//绑定事件
		$('#asyncshowtype').on('change', asyncShowTypeChange);
		$('#searchcbr').on('propertychange', searchCBR).on('input', searchCBR);
	}
	//显示搜索框
	var showSearchInput = function(flag){
		if(!flag) return;
		$('#nosearchtips').hide();
		$('.three_tree').show();
		$('#searchcbr').val('').show();
		$('.src-wrap__content .src-wrap__content__ztree').css('height', 'calc(100% - 93px)');
	}
		
	//虚拟机树显示模式发生变化
	var asyncShowTypeChange = function(){
		var showType = parseInt($('#asyncshowtype').val());
		$('.tree_div').hide();
		$('.addVMList').hide();
		$('#searchcbr').val('').hide();
		$('#nosearchtips').hide();
		switch (showType){
			case 1:
				$('#cbr_tree_mp').show();
				$('#addMPList').show();
				$('.addTitle > span').text(LANG.UI_SYNC_CBR_CHOOSED_STORAGE);
				currentTree = zTreeMP;
				currentTreeDivId = 'cbr_tree_mp';
				showSearchInput(mpsearchflag);
				//同步时间点显示
				$('.syncTimeDiv').show();
				// $('.backupSyncTimeDiv').spinner({value:50, step: 1, min: 1, max: 10000000000});//存储库默认50
				//设置默认值 存储库50
				$('.backupSyncTimeDiv').spinner("value", 50);
				break;
			case 2:
				if(!zTreeVMInit){
					initTree(2);
					zTreeVMInit  = true;
				}
				$('#cbr_tree_vm').show();
				$('#addVMList').show();
				$('.addTitle > span').text(LANG.UI_SYNC_CBR_CHOOSED_RESOURCE);
				currentTree = zTreeVM;
				currentTreeDivId = 'cbr_tree_vm';
				showSearchInput(vmsearchflag);
				//同步时间点显示
				$('.syncTimeDiv').show();
				$('.backupSyncTimeDiv').spinner("value", 3);
				break;
			case 3:
				if(!zTreeTPInit){
					initTree(3);
					zTreeTPInit  = true;
				}
				$('#cbr_tree_tp').show();
				$('#addTPList').show();
				var length = $('#addTPList>li').size();
				var timestr = LANG.UI_SYNC_CBR_SELECT_TIME_POINT+length+LANG.UI_SYNC_CBR_SELECT_TIME_POINT_NUM;
				$('.addTitle > span').html(timestr);
				currentTree =  zTreeTP;
				currentTreeDivId = 'cbr_tree_tp';
				showSearchInput(tpsearchflag);
				//同步时间点不显示
				$('.syncTimeDiv').hide();
				$('.backupSyncTimeDiv').spinner("value", 1);
				break;
		}
		$("#" + currentTreeDivId).scrollTop(0);
	}
	//搜索虚拟机
	var searchCBR = function(){
		var value = $('#searchcbr').val();
		var nodes =  currentTree.getNodes();
		if(!nodes  ||  nodes.length == 0){
			return;
		}
		var checkNodes  = currentTree.getCheckedNodes();
		var allNodes  =  currentTree.transformToArray(currentTree.getNodes());
		nodeParamList =  currentTree.getNodesByParamFuzzy('name', value); //根据节点数据的属性搜索，获取条件模糊匹配的节点数据json集合
	
		if(nodeParamList.length != 0){
			$('.three_tree').show();
			currentTree.hideNodes(allNodes)
			$('#nosearchtips').hide();
		}else{
			$('.three_tree').hide();
			$('#nosearchtips').show();
		}
		//连接搜索和勾选的
		nodeParamList = nodeParamList.concat(checkNodes);

		var nodeParamList1 =   currentTree.transformToArray(nodeParamList);
		for(var i in nodeParamList1){
			findParent(currentTree,nodeParamList1[i]);
		}
		currentTree.showNodes(nodeParamList);
	}	
	//找到父节点
	var findParent = function(treeObj,node){
		currentTree.expandNode(node,true,false,false);
		if(!node.children){
			nodeParamList.push(node);
			currentTree.expandNode(node,false,false,false)
		}
		var pNode =  node.getParentNode();
		if(pNode != null){
			nodeParamList.push(pNode)
			findParent(currentTree,pNode);
		}
	}
	//判断是否在同一个存储库上
	var checkSelectInSameStorage =  function(tree, node, allNodes){
		//判断当前节点的父节点是否和所有节点的父节点一样 如果一样 则需要去掉当前节点下的所有子节点勾选 再勾选上当前节点
		//是否存在于不同存储库
		var existflag =  false;
		// var parentnode =  node.getParentNode();
		for(var i  = 0;i < allNodes.length;i++){
			var storagenode = getstoragenode(node);
			if(allNodes[i].pid ==  "1" && allNodes[i].id == storagenode.id){
				tree.checkNode(allNodes[i],false,true,false);
				var liId =  tree.id + allNodes[i].id;
			    $('#' + escapeJquery(liId)).remove();
				existflag =  true;
			}
		}
		return existflag;

		//判断当前节点的存储是否和已经勾选的节点的存储是否一样  如果不一样 去掉之前存储下所有的 勾选当前存储下的  如果一样 1、如果是时间点 需要判断是否在同一个虚拟机下 2、如果不是 则直接添加 

	}
	//获取当前节点的存储节点
	var getstoragenode =   function(node){
		if(currentTreeDivId == "cbr_tree_mp"){
			var projectnode = node.getParentNode();
		}
		if(currentTreeDivId == "cbr_tree_vm"){
			var vaultnode =  node.getParentNode();
			var projectnode  =  vaultnode.getParentNode();
		}if(currentTreeDivId == "cbr_tree_tp"){
			var vaultnode =  node.getParentNode();
			var projectnode  =  vaultnode.getParentNode();
		}
		var areanode =  projectnode.getParentNode();
		var storagenode = areanode.getParentNode();
		return storagenode;
	}
	//获取每个节点的resource信息 用于存放数据库中
	var getresourceinfo  =  function(node){
		var detaildata = {};
		var projectnode = null;
		var regionnode =  null;
		var storagenode =  null;
		if(currentTreeDivId == "cbr_tree_mp"){
			projectnode = node.getParentNode();
			detaildata.vault_id = node.id;
		}
		if(currentTreeDivId == "cbr_tree_vm"){
			var vaultnode =  node.getParentNode();
			projectnode  =  vaultnode.getParentNode();
			detaildata.vault_id = vaultnode.id;
			detaildata.resource_id =  node.id;
		}if(currentTreeDivId == "cbr_tree_tp"){
			var vaultnode =  node.getParentNode();
			projectnode  =  vaultnode.getParentNode();
			detaildata.vault_id = vaultnode.id;
			detaildata.resource_id =  node.parentid;
			detaildata.backup_id = node.id;
		}
	    regionnode =  projectnode.getParentNode();
		storagenode = regionnode.getParentNode();
		detaildata.storage_uuid  =  storagenode.id;
		//由于regionid中存的是存储和区域 所以这里需要隔开 
		var storageregionid = regionnode.id.split("_");
		//detaildata.region_id =  regionnode.id;
		detaildata.region_id =  storageregionid[1];
		//detaildata.project_id = projectnode.id;
		var storageprojectid = projectnode.id.split("_");
		detaildata.project_id = storageprojectid[1];
		return detaildata;
	}
		
	//初始化时间计时器
	var initServerTime = function(data){
		var getDate = function(unix){ 
			var polishing = function(d){
				return d < 10 ? '0' + d : d;
			}
			var date = new Date(parseInt(unix));
			Y = date.getFullYear() + '-';
			M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
			D = polishing(date.getDate()) + ' ';
			h = polishing(date.getHours()) + ':';
			m = polishing(date.getMinutes()) + ':';
			s = polishing(date.getSeconds());
			return Y+M+D+h+m+s;
		} 
		var servertime = $('#servertime');
		var updateInterval = 1000;
		var startClock = function(){
			if(0 == $('#servertime').size()){
        		clearTimeout(timerTask.VMBackup_serverTime);
        		return;
        	}
			servertime.html(getDate(_timeStamp));
			_timeStamp += 1000;
			timerTask.VMBackup_serverTime = setTimeout(startClock, updateInterval);
		}
		if(_timeStamp) return;
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemTimeJSFormat',p:{}}, function(data){
			_timeStamp = new Date(data).getTime();
			startClock();
		});
	}
	var step1Valid = function(){
		var selectNodes = currentTree.getCheckedNodes();
		var nodes = [];
		var showStr = '';
		if(currentTree == zTreeMP){
			for(var i = 0;i<selectNodes.length;i++){
				if(selectNodes[i].eventtype == "mp"){
					nodes.push(selectNodes[i]);
				}
			}
		}else if(currentTree ==  zTreeVM){
			for(var i = 0;i<selectNodes.length;i++){
				if(selectNodes[i].eventtype == "vm"){
					nodes.push(selectNodes[i]);
				}
			}
		}else if(currentTree == zTreeTP){
			for(var i = 0;i<selectNodes.length;i++){
				if(selectNodes[i].eventtype == "tp"){
					nodes.push(selectNodes[i]);
				}
			}
		}
		if(nodes.length == 0){
			switch (currentTree){
				case zTreeMP:
					$(".selectvmtip").html(LANG.UI_SYNC_CBR_CHOOSE_SYNC_STORAGE).show();
					// 动态设置tab-pane的高度
					$(".tab-pane__row").css('height', 'calc(100% - 80px)');
				break;
				case zTreeVM:
					$(".selectvmtip").html(LANG.UI_SYNC_CBR_CHOOSE_SYNC_RESOURCE).show();
					// 动态设置tab-pane的高度
					$(".tab-pane__row").css('height', 'calc(100% - 80px)');
				break;
				case zTreeTP:
					$(".selectvmtip").html(LANG.UI_SYNC_CBR_CHOOSE_SYNC_TIMEPOINT).show();
					// 动态设置tab-pane的高度
					$(".tab-pane__row").css('height', 'calc(100% - 80px)');
				break;
			}
			return false;
		}
		data.srcInfo.vminfo = [];
		var storagenode  = null;
		data.detail.cbr_sync_list = [];
		$.each(nodes, function(i, d){
			showStr += d.path+ "<br>";
			storagenode =  getstoragenode(d);
			//获取当前节点的存储id
			var useinfo =  {
				// "eventtype":d.eventtype,
				"object_uuid":d.id, //根据粒度，这里可以为存储库id，资源id，备份点id
				"object_name":d.name,
				"vcenter_uuid":storagenode.id,
				"type": parseInt($('#asyncshowtype').val()),
				"dir_path":d.path,
				"vm_config":"",     
				"log_cache_storage_uuid":"",
				"log_cache_size": 0,
				"exclude_vm_list": []       // 【不使用】排除的虚拟机列表，默认为[]       
			}
			data.srcInfo.vminfo.push(useinfo);
			var detailinfo =  getresourceinfo(d);
			data.detail.cbr_sync_list.push(detailinfo);
		});
		data.storage_uuid  =  storagenode.id;
		showStep1(showStr);

	}
	var showStep1 =  function(showStr){
		initNodeSelect();
		//获取同步任务名
		$.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getCBRSyncTaskName',p:{}}, function(d){
			$('#jobname').val(d);
		});
		initServerTime();
		$('.vmshow').html(showStr);
	}
	var step2Valid = function(){
		var results = getNodeStr();
		if(results){
			showStep2();
			
		}
		
		return results;
	}
	var showStep2 =  function(){
		var storeInfo  ='';
		var nodeInfo  ='';
		storeInfo =  $('#selectstorage option:selected').text();
		nodeInfo = $('#selectnode option:selected').text();
		$('.storeinfoshow').html(storeInfo);
		$('.nodeinfoshow').html(nodeInfo);
		setStrategyMode(); //设置默认策略模式
		// initStrategyData(); //初始化策略数据
		lastsynctype = currentTreeDivId;
		//这里第一次进入第三步
		instep3flag  = true;
		initTimeStrategyDes();
		initHighStrategyDes();//初始化高级策略描述信息
		initVerifyStrategyDes();
		initStoreStrategyDes();//初始化存储策略描述信息
		
		
	}
	var step3Valid = function(){
		var result = getTimeStr();
		if(!result) return false;
		var result = getSpeedStr() & getStoreStr() & getReserveStr() & getHighStr() & getVerifyStr() & getTransferStr()  ;
		if(result){
			result = result && showStep3();
		}
		return result;
	}
	var showStep3  = function(){
		var backuptypeshow = $('.backuptypeshow'), backuptypeinfoshow = $('.backuptypeinfoshow');
		var backuptypeshowStr = '', backuptypeinfoshowStr = '';
		if("strategy" == data.backupInfo.type){
			backuptypeshowStr =LANG.UI_SYNC_CBR_SYNC_BY_STARTEGY;
			backuptypeinfoshowStr += data.backupInfo.fullInfo.des + "<br>";
		}else if("oncetime" == data.backupInfo.type){
			backuptypeshowStr =LANG.UI_SYNC_CBR_SYNC_ONCE_TIME;
			backuptypeinfoshowStr = LANG.UI_PUBLIC_START_TIME + ": " + data.backupInfo.datetime;
		}
		//同步方式和时间策略展示
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(backuptypeinfoshowStr);
		//限速策略展示
		var speedlimitshow =  $('.speedlimitshow');
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
		//存储策略展示 
		var deduplicationlabel = $('.deduplicationlabel').html();
		var compresslabel = $('.compresslabel').html();
		var storeInfo = "";
		storeInfo += deduplicationlabel + ": " + getSwitchDes(data.highInfo.store.deduplication) + "<br>";
		storeInfo += compresslabel + ": " + getSwitchDes(data.highInfo.store.compress) + "<br>";
		// 压缩等级
		if(data.highInfo.store.compress){
			let gradeValue = $('#compressGrade').val();
			let grade = '';
			switch (parseInt(gradeValue,10)) {
				case 1: 
					grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
					break;
				case 2: 
					grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
					break;
				case 3: 
					grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
					break;
				case 4: 
					grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
					break;
			};
			storeInfo += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + "<br>";
		}
		//数据加密配置描述
		var encryptStoragelabel = $('.encryptStoragelabel').html();
		var passwordAutolabel = $('.passwordAutolabel').html();

		storeInfo += "<br>" + encryptStoragelabel + ": " + getSwitchDes(data.highInfo.store.encrypt);
		if(data.highInfo.store.encrypt){
			storeInfo += "<br>" + passwordAutolabel + ": " + getSwitchDes(data.highInfo.store.password_auto_flag);
		}

		
		// 存储加密
		if($('#encryptStorageCheck').get(0).checked){
			let encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            let grade = '';
            switch (parseInt(method)) {
                case 1: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            };
            storeInfo += "<br>" + encryptedMethodLabel + ": " + grade + "<br>";
		}

	    $('.storageinfoshow').html(storeInfo);
		//保留策略展示
		var reservetypeshow = $('.reservetypeshow');
		var reservetypeStr = '';
		// 保留类型
		if(CONF.RESERVE_STRATEGY_MODE.POINT == data.highInfo.reserve.strategyMode){
			reservetypeStr = LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
		}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == data.highInfo.reserve.strategyMode){
			reservetypeStr = LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
		}
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			reservetypeStr += LANG.UI_STRATEGY_RESERVE_NUM;
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				//英文独有的
				reservetypeStr += LANG.UI_STRATEGY_RESERVE_NUM_EN;
			}
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			reservetypeStr += LANG.UI_STRATEGY_RESERVE_DAY;
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				//英文独有的
				reservetypeStr += LANG.UI_STRATEGY_RESERVE_DAY_EN;
			}
		}
		if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
			reservetypeStr += "," + LANG.UI_STRATEGY_RESERVE_VALUE + data.highInfo.reserve.value;
			//确认页面GFS描述
			var GFSstr = $(".GFSLable").html();
			reservetypeStr +="</br>"+ GFSstr + getSwitchDes($('#GFSflag').get(0).checked);
			if($('#GFSflag').get(0).checked){
				reservetypeStr +="</br>"+ LANG.UI_JOB_GFS_CONFIGURATION + $("#GFSDiv").getGFSDes();
			}
		}else{
			reservetypeStr = data.highInfo.reserve.value + LANG.UI_STRATEGY_RESERVE_VALUE + reservetypeStr;
			//确认页面GFS描述
			var GFSstr = $(".GFSLable").html()+':';
			if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
				 GFSstr = $(".GFSLable").html();
			}
			reservetypeStr +="</br>"+ GFSstr + getSwitchDes($('#GFSflag').get(0).checked);
			if($('#GFSflag').get(0).checked){
				reservetypeStr +="</br>"+ LANG.UI_JOB_GFS_CONFIGURATION + $("#GFSDiv").getGFSDes();
			}
		}
		reservetypeshow.html(reservetypeStr);
		//高级配置展示（线程数量 / 同步时间点个数）
		var threadnumStr = $('.threadnumlabel').html() + ": " + $('#backupThreadNum').val();
		var showType = parseInt($('#asyncshowtype').val());
		if(showType != 3){
			threadnumStr += "</br>" + $('.syncnumlabel').html() + ": " + $('#backupSyncTimeNum').val();
		}
		$('.threadnumshow').html(threadnumStr);
		//校验策略展示
		var verifymodeshow  = $('.verifymodeshow');
		var verifymodeshowStr = $('.verifyLable').html() + ": " +  getSwitchDes($('#verifyflag').get(0).checked);
		verifymodeshow.html(verifymodeshowStr);
		//传输策略展示
		var transferlabel = $('.transferlabel').html();
		
		var transportinfoshowStr  = transferlabel + ": " +  $('#transport_mode').find("option:selected").text();
		//var encryptlabel = $('.encrypttransferlabel').html();
		// transportinfoshowStr += "<br>" + encryptlabel + ": " + getSwitchDes(data.highInfo.transfer.encrypt) + "  ";
		// // 传输加密算法
		// if($('#encrypttransfer').get(0).checked){
		// 	let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
        //     let method = parseInt($('#transferEncryptMethod').val());
        //     let grade = '';
		// 	switch (method) {
		// 		case 1:
		// 			grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
		// 			break;
		// 		case 2:
		// 			grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
		// 			break;
		// 	};
        //     transportinfoshowStr += "<br>" + encryptedMethodLabel + ": " + grade;
		// }
		//传输策略信息展示
		$('.transportinfoshow').html(transportinfoshowStr);
	}
	//获取同步节点信息
	var getNodeStr =  function(){
		data.highInfo.node.nodecheck = true;
		data.highInfo.node.nodeuuid = '';
		data.highInfo.node.storagecheck = true;
		data.highInfo.node.storageuuid = '';
		data.highInfo.node.nodecheck = false;
		data.highInfo.node.nodeuuid = $('#selectnode').val();
		if(!data.highInfo.node.nodeuuid){
			UIToastr.showWarning(LANG.UI_SYNC_CBR_SYNC_SELECT_COMPUTE_NODE, LANG.UI_SYNC_CBR_SYNC_SELECT_COMPUTE_NODE_TIPS);
			return false;
		}
		data.highInfo.node.storagecheck = false;
		data.highInfo.node.storageuuid = $('#selectstorage').val();
		if(!data.highInfo.node.storageuuid){
			UIToastr.showWarning(LANG.UI_SYNC_CBR_SYNC_SELECT_TARGET_STORAGE,LANG.UI_SYNC_CBR_SYNC_SELECT_TARGET_STORAGE_TIPS);
			return false;
		}
		// 云存储只能按链保留
		let storageType = parseInt($('#selectstorage option:selected').attr('data-type'));
		if(storageType == 9){
			$('#reserveMode').val(2).prop('disabled',true);
		}else{
			$('#reserveMode').val(1).removeAttr('disabled');
		}
		return true;
	}

	//初始化存储类型下拉框
	var initStorageSelect = function(){
		var data = {};
		data.nodeuuid = $('#selectnode').val();
		console.log("nodeuuid---",$('#selectnode').val());
		var jsonData = JSON.stringify(data);

		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
		// $.post(CONF.AJAXPATH, {m:CONF.M.VM,f:'getAddStorageTypeSelect',p:{}}, function(d){
			var data = JSON.parse(d);
			var softselect = $('#selectstorage');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				//下云同步需要屏蔽CBR的
				if(data[i].type != CONF.BD_STORAGE_TYPE.HUAWEICBR){
					// var option = $("<option>").text(data[i].text).val(data[i].uuid).attr("storagetype",data[i].type);
					var option = '<option data-type="' + data[i].type + '" data-name="' + data[i].name + '" value="' + data[i].uuid + '">' + data[i].text + '</option>';
					softselect.append(option);
				}
				
			}
		});
	}
	//初始化spinner微调器
	var initSpinner = function(){
		$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000}); //设置限速策略--限速大小
		$('#spinnerNum').spinner({value:30, step: 5, min: 1, max: 999}); //设置保留策略--保留个数
		$('#spinnerDay').spinner({value:30, step: 5, min: 1, max: 999}); //设置保留策略--保留天数
		$('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 8});//设置高级策略--传输线程
		$('.backupSyncTimeDiv').spinner({value:50, step: 1, min: 1, max: 1000});//设置高级策略--同步时间点个数
	}
	//初始化节点下拉框
	var initNodeSelect =  function(){
		if(nodeSelectFlag) return; //加载一次
			$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
    		var data = JSON.parse(d);
    		var nodeselect = $('#selectnode');
    		nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
			nodeSelectFlag = true;
			initStorageSelect();
    	});
	}
	//初始化时间策略
	var initStrategy = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:"getTimeCrowdList", p:{}}, function(d){
			var jsonData = JSON.parse(d);
			if(jsonData.timeList.length != 0){
				$('#backupCrowd').taskCrowd({timeList:jsonData.timeList, showFlag: jsonData.showFlag});
			}
			var suggestInfo = jsonData.suggestTime;
			defaultStrategy[0] = {
				mode: 1,
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				frequency: '',
				start_time: suggestInfo.start_time,
				end_time: suggestInfo.end_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time,
				strategyDes:'cbr'
			};
			//延迟设置,因为这里icheck会默认修改里面的选中事件
			setTimeout(function(){
				$('#backupTimestrategy').strategy({dom: $('#backupTimestrategy'), config: defaultStrategy, display:['display-none', 'display-none', 'display-none', 'display-none'], backup_flag: 1});
			}, 2000);
		});
	}
	//设置默认策略模式
	var setStrategyMode = function(){
		data.backupInfo.type = $('#backuptype').val();
		if(initgfsstrategy ==  false){
			$("#GFSDiv").initGFSPlug();
			initgfsstrategy =  true;
		}
		if(currentTreeDivId == "cbr_tree_mp" || currentTreeDivId == "cbr_tree_vm"){
			$('#backuptype').find('option[value="strategy"]').show();
			$('#backuptype option[value="oncetime"]').attr("selected",false);
			$("#backuptype").val("strategy");
			$('#stragegyaccordion').find('.strategy-panel[data-mode=1]').show();
			$('.setStrategy').show();
			$('.setOnceTime').hide();
			if(data.backupInfo.type!="oncetime"){ //默认及按策略同步
				$('#reservetype').removeAttr("disabled");
				$('#spinnerNum').spinner('enable');
				$('#spinnerDay').spinner('enable');
				$('#GFSflag').bootstrapSwitch("disabled", false);
				//保留策略设置默认值 资源粒度30个，存储库粒度150个
				if(currentTreeDivId == "cbr_tree_mp"){
					//这次是存储库
					//第一次进来 或者不是第一次进来 但是上次类型跟这次不一样
					if((instep3flag ==  false) || (lastsynctype != "cbr_tree_mp") ){
						$('#spinnerNum').spinner('value', 150);
						initReserveStrategyDes();
					}
				}else{
					//虚拟机 
					//如果是第一次进来 或者不是第一次进来 但是上次不是虚拟机
					if((instep3flag ==  false) || (lastsynctype != "cbr_tree_vm")){
						$('#spinnerNum').spinner('value', 30);
						initReserveStrategyDes();
					}
				}
			}else{
				$('#backuptype').find('option[value="strategy"]').show();
				$('#backuptype option[value="strategy"]').attr("selected",false);
				$('#backuptype option[value="oncetime"]').attr("selected","selected");
				$("#backuptype").val("oncetime");
				// $('#backuptype').find('option[value="strategy"]').hide();
				$('.setStrategy').hide();
				$('.setOnceTime').show();
			}
		}
		if((currentTreeDivId == "cbr_tree_tp")){ 
			$('#backuptype option[value="strategy"]').attr("selected",false);
			$('#backuptype option[value="oncetime"]').attr("selected","selected");
			$("#backuptype").val("oncetime")
			$('#backuptype').find('option[value="strategy"]').hide();
			$('.setStrategy').hide();
			$('.setOnceTime').show();
			$('#GFSflag').bootstrapSwitch("state", false); //关闭
			$('#GFSflag').bootstrapSwitch("disabled", true);//禁用
			$('#reservetype').prop("disabled","disabled");
			$('#reservetype').val(1);
			//一次性同步只能按照个数保留
			$('.reserveDay').hide();
			$('.reserveNum').show();
			$('#spinnerNum').spinner('disable');
			$('#spinnerDay').spinner('disable');
			$("#GFSDiv").hide();
			$('#spinnerNum').spinner('value', 1);
			initReserveStrategyDes();
			
		}
		//如果选择的存储是华为CBR 则存储策略隐藏
		var selectype  = $('#selectstorage').find('option:selected').attr("data-type");
		if(selectype == 12){
			$(".storageDiv").hide();
			$("#storagemodeshowdiv").hide();
			
		}else{
			$(".storageDiv").show();
			$("#storagemodeshowdiv").show();
		}


		
	}
	//初始化策略数据
	var initStrategyData =  function(){
		$("#GFSDiv").initGFSPlug();
		initStrategyDes();//初始化策略描述信息
	}
	//初始化策略描述信息
	var initStrategyDes =  function(){
		initTimeStrategyDes();//初始化时间策略描述信息
		initSpeedStrategyDes();//初始化限速策略描述信息
		initStoreStrategyDes();//初始化存储策略描述信息
		initReserveStrategyDes();//初始化保留策略描述信息
		initHighStrategyDes();//初始化高级策略描述信息
		initVerifyStrategyDes();//初始化校验策略描述信息
	}
	//初始化时间策略描述信息
	var initTimeStrategyDes =  function(){
		var des = "";
		var backuptype = $('#backuptype').val();
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		if(!strategyConfig.fullInfo) return;
		//按策略同步
		if('strategy' == backuptype){
			des += strategyConfig.fullInfo.des + ". ";
		}else if('oncetime' == backuptype){
			des += LANG.UI_SYNC_CBR_SYNC_ONCE_TIME_START_TIME + ": " + $('#oncetime').val();
		}
		$('.backupTimeDes').html(des);
		$('.backupTimeDes').prop('title', des);
	}
	//初始化限速策略描述信息
	var initSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if (speedList.length == 0) {
			return
		}

		$('#tasklevelselect').val(speedList.level);
		$('#speedtypeselect').val(speedList.type);
		if (speedList.type == 1) {
			$('#show_type_1').show();
			$('#show_type_2').hide();
			$('#task_type_global_speed_strategy').show();
		} else {
			$('#show_type_2').show();
			$('#show_type_1').hide();
			$('#task_type_global_speed_strategy').hide();
		}

		// 如果是之前的自定义的 方式不变 但是如果是选择的全局限速策略的话，那么需要读取出所有的全局限速策略列表，然后根据列表的id取出限速信息
		if (speedList.type == 1) {
			var global_speed_limit = speedList.uuid
			setTimeout(function (){
				$(".strategy-table #table").bootstrapTable('checkBy', {
					field: 'uuid',
					values: [global_speed_limit]
				})
				// 下面的需要初始化全局限速策略表格并携带参数
				let rowData = $(".strategy-table #table").bootstrapTable("getData");

				var datas = [];
				for(var j in rowData) {
					if (rowData[j].uuid == global_speed_limit) {
						datas = rowData[j].detail;
						datas = datas.split('</br>');
					}
				}
				if(datas.length !=0){
					des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + datas.length;
				}

				for(var i in datas){
					titleDes += datas[i] + '. ';
				}

				$('.speedlimitDes').html(des);
				$('.speedlimitDes').prop('title', titleDes);
			},1500);
		} else {
			var speedInfo = speedList.speedInfo;
			// 自定义
			if(speedInfo.length > 0){
				des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedInfo.length;
			}

			var strategy_type = speedList.strategy_type;
			for(var i in speedInfo){
				titleDes += speedInfo[i].des + '. ';
			}
			addGlobalStrategy.init({'strategy_type':strategy_type,speedInfo:speedInfo,initSpeedFlag:1});

			setTimeout(function (){
				$('.speedlimitDes').html(des);
				$('.speedlimitDes').prop('title', titleDes);
			}, 1000);
		}
	}
	//初始化存储策略描述信息
	var initStoreStrategyDes = function(){
		var des = "";
		var diffDes = "";
		des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationcheck').get(0).checked) + ", ";
		diffDes += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationcheck').get(0).checked) + "<br>";
		des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
		diffDes += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
		if($('#compressCheck').get(0).checked){
            var gradeValue = $('#compressGrade').val();
            var grade = '';
            switch (parseInt(gradeValue,10)) {
                case 1: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
                    break;
                case 2: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
                    break;
                case 3: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
                    break;
                case 4: 
                    grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
                    break;
            };
            des += "," + LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
            diffDes += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade;
        }
		//除了hyper-v，其余虚拟化都有数据加密
		des += ","+LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
		diffDes += LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
        // 存储加密算法
        if($('#encryptStorageCheck').get(0).checked){
            var encryptedMethodLabel = $('.storage-encrypt-label').html();
            let method = $('#storageEncryptMethod').val();
            var grade = '';
            switch (parseInt(method)) {
                case 1: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
                    break;
                case 2: 
                    grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
                    break;
            };
            des += "," + encryptedMethodLabel + ": " + grade;
            diffDes += encryptedMethodLabel + ": " + grade;
        }
		var strategyIndex = $('#strategySelect').val();
		// if(strategyIndex && strategyIndex != "" && editFlag){
		// 	var oldDes = globalStrategy[strategyIndex].store.des;
		// 	initStrategyDesStyle($('.storeDes'), diffDes, oldDes);
		// }else{
		// 	$('.storeDes').removeClass('font-green-seagreen');
		// }
		$('.storeDes').removeClass('font-green-seagreen');
		$('.storeDes').html(des);
        $('.storeDes').prop('title', des);
    }
	//初始化保留策略描述信息
	var initReserveStrategyDes = function(){
        var des = "";
        var type = $('#reservetype').val();
        var value = 0;
        if(CONF.RESERVE_TYPE.NUM == type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += LANG.UI_STRATEGY_RESERVE_NUM
			}else{
				des += LANG.UI_STRATEGY_RESERVE_NUM_EN;
			}
            value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += LANG.UI_STRATEGY_RESERVE_DAY;
			}else{
				des += LANG.UI_STRATEGY_RESERVE_DAY_EN;
			}
            value = $('#spinnerDayInput').val();
        }
        value = parseInt(value);
		if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
			des += ", " + LANG.UI_STRATEGY_RESERVE_VALUE + value;
		}else{
			des += ":" + LANG.UI_STRATEGY_RESERVE_VALUE + value;
		}
		$('.reserveDes').removeClass('font-green-seagreen');
		var GFSstr = $(".GFSLable").html();
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			GFSstr = $(".GFSLable").html();
		}
		des +=", "+GFSstr+getSwitchDes($('#GFSflag').get(0).checked);
		$('.reserveDes').html(des);
        $('.reserveDes').prop('title', des);
    }
	//初始化高级策略描述信息
	var initHighStrategyDes = function(){
		var des = "";
		des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#backupThreadNum').val() ;
		var showType = parseInt($('#asyncshowtype').val());
		if(showType != 3){
			des += ", " + LANG.UI_SYNC_CBR_SYNC_TIMEPOINT+ ": " + $('#backupSyncTimeNum').val();	
		}
		$('.backupHighDes').html(des);
		$('.backupHighDes').prop('title', des);
	}
	//初始化校验策略描述信息
	var initVerifyStrategyDes = function(){
		var des = "";
		var verifystr = $('.verifyLable').html();
		des +=verifystr+": "+getSwitchDes($('#verifyflag').get(0).checked);
		$('.backupVerifyDes').html(des);
		$('.backupVerifyDes').prop('title', des);
	}
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	//同步类型改变 
	var backupTypeHandler = function(){
		if('strategy' == this.value){
			//GFS
			$('#GFSflag').bootstrapSwitch("disabled", false);
			$('.setStrategy').show();
			$('.setOnceTime').hide();
			$('#reservetype').removeAttr("disabled");
			data.highInfo.reserve.type = $('#reservetype').val();
			$('#spinnerNum').spinner('enable');
			$('#spinnerDay').spinner('enable');
			if(currentTreeDivId == "cbr_tree_mp"){
				$('#spinnerNum').spinner('value', 150);
			}else{
				$('#spinnerNum').spinner('value', 30);
			}
			
			$('#spinnerDay').spinner('value', 30);

		}else if('oncetime' == this.value){
			//GFS
			$('#GFSflag').bootstrapSwitch("state",false); //关闭
			$('#GFSflag').bootstrapSwitch("disabled",true);//禁用
			$('.setStrategy').hide();
			$('.setOnceTime').show();
			//一次性备份只是按个数保留
			$('#reservetype').prop("disabled","disabled");
			$('#reservetype').val(1);
			data.highInfo.reserve.type = $('#reservetype').val();
			$('.reserveDay').hide();
			$('.reserveNum').show();
			$('#spinnerNum').spinner('disable');
			$('#spinnerDay').spinner('disable');
			$('#spinnerNum').spinner('value', 1);
			$('#spinnerDay').spinner('value', 1);
		}
		data.backupInfo.type = this.value;
		$('.settimetip').hide();
		//切换了类型需要再次初始化时间策略描述信息
		initTimeStrategyDes();
		initReserveStrategyDes();
	}
	//初始化策略事件
	var initDataChangeListeners  = function(){
		initTimeListeners(); //初始化时间策略模块的监听
		initSpeedListeners(); //初始化限速策略模块的监听
		initStoreListeners();//初始化存储策略模块的监听
		initReserveListeners(); //初始化保留策略模块的监听
		initHighListeners(); //初始化高级策略模块的监听
		initVerifyListeners(); //初始化校验策略模块的监听
		

	}
	//初始化时间策略模块的监听
	var initTimeListeners =  function(){
		$('#oncetime').on('change', function(){
            initTimeStrategyDes();
		});
	}
	//初始化限速策略模块的监听
	var initSpeedListeners =  function(){
		//添加限速策略确定
		$('#speed_submit').on('click', speedSubmit);
	}
	//初始化存储策略模块的监听
	var initStoreListeners = function(){
        $('#deduplicationcheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
			if ($('#strategymode').find('input[data-mode=9]').prop('checked') && $('#deduplicationcheck').get(0).checked) {
				//永久增量建议关闭提示
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_STORE, LANG.UI_BACKUP_VM_PERMANENT_INCREMENT_TIPS);
			}
        });
        $('#compressCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
			if ($('#strategymode').find('input[data-mode=9]').prop('checked') && $('#compressCheck').get(0).checked) {
				//永久增量建议关闭提示
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_STORE, LANG.UI_BACKUP_VM_PERMANENT_INCREMENT_TIPS);
			}
			if (this.checked) {
				$('.CompressGradeDiv').show();
			} else {
				$('.CompressGradeDiv').hide();
			}

        });
        $('#encryptStorageCheck').on("switchChange.bootstrapSwitch",function(){
            initStoreStrategyDes();
        });
        // 存储加密算法
        $('#storageEncryptMethod').on("change", function () {
            initStoreStrategyDes();
        });
		// 压缩等级改变
        $('#compressGrade').on('change', initStoreStrategyDes);

    }
	//初始化保留策略模块的监听
	var initReserveListeners =  function(){
		//保留类型切换
		$('#reservetype').on('change', reserveTypeHandler);

		$('#spinnerNumInput').on('input propertychange', function(){
			initReserveStrategyDes();
		});
		$('#spinnerDayInput').on('input propertychange', function(){
			initReserveStrategyDes();
		});
		$('.spinner-up').on('click', function(){
			initReserveStrategyDes();
			initHighStrategyDes(); 
		});
		$('.spinner-down').on('click', function(){
			initReserveStrategyDes();
			initHighStrategyDes();
		});
		//GFS保留策略切换
		$('#GFSflag').on('switchChange.bootstrapSwitch', function(){
			initReserveStrategyDes();
		});
	}
	//初始化高级策略模块的监听
	var initHighListeners = function(){
		$('#backupThreadNum').on('input propertychange', function(){
            initHighStrategyDes();
        });
		$('#backupSyncTimeNum').on('input propertychange', function(){
            initHighStrategyDes();
        });
	}
	//初始化校验策略模块的监听
	var initVerifyListeners =  function(){
		$('#verifyflag').on('switchChange.bootstrapSwitch',function(){
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
							'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>'+ 
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
							'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap"> '+ info.des +  '</div>' + 
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
			initSpeedStrategyDes();
		});
		speedList.push(info);
		$('#speedlimitModal').modal('hide');
		initSpeedStrategyDes();
	}

	// 获取设置的所有策略配置信息
	var speedSubmitInfo = function (){
		let info = {};
		info['level'] = $('#tasklevelselect').val();
		info['type'] = $('#speedtypeselect').val();
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
	//获取限速策略中的UUid
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
	//检查是否已经存在永久限速策略
	var checkSimpleForever = function(mode){
        for(var i=0;i<speedList.length;i++){
            if(mode == speedList[i].mode){
                return false;
            }
        }

        return true;
	}
	//检测结束时间是否大于开始时间
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
	//初始化限速策略模态框
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
	//限速模式改变
	var speedModeHandler = function(){
        if(this.value == 2){
            $('.setSpeedStrategy').hide();
        }else{
            $('.setSpeedStrategy').show();
        }
	}
	//存储加密切换
	var encryptChange = function(){
		if(this.checked){
			$('#passwordAutocheck').bootstrapSwitch('state', true);  
			$('#password').empty();
			$('#repassword').empty();
			$('.passwordModeDiv').show();
            $('.storage-encrypt-div').show();
		}else{
			$('.passwordModeDiv').hide();
			$('.passwordDiv').hide();
            $('.storage-encrypt-div').hide();
		}
	}
	
	//切换自动选择存储加密密码
	var passwordModeChange = function(){
		if(this.checked){
			$('#password').val('');
			$('#repassword').val('');
			$('.passwordDiv').hide();
		}else{
			$('.passwordDiv').show();
		}
	}

	//数据加密密码确认检测
	var checkPassword = function(){
		var password = $('#password').val();
		var repassword = $('#repassword').val();
		if(password != repassword){
			$('.passwordTips').show();
		}else{
			$('.passwordTips').hide();
		}
	}
	//保留策略改变事件
	var GFSChange =  function(){
		//如果勾选
		if(this.checked){
			$("#GFSDiv").show();
		}else{
			$("#GFSDiv").hide();
		}
	}
	//保留类型改变
	var reserveTypeHandler = function(){
		if(CONF.RESERVE_TYPE.NUM == this.value){
			$('.reserveNum').show();
			$('.reserveDay').hide();
		}else if(CONF.RESERVE_TYPE.DAY == this.value){
			$('.reserveNum').hide();
			$('.reserveDay').show();
		}
		data.highInfo.reserve.type = parseInt(this.value);
		initReserveStrategyDes();
	}
	//第三步获取时间策略信息
	var getTimeStr = function(){
		var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		data.backupInfo.type = $('#backuptype').val();
		// var strategyConfig = $('#backupTimestrategy').getStrategyConfig();
		// data.backupInfo.type = $('#backuptype').val();
		if("strategy" == data.backupInfo.type){
			data.backupInfo.fullInfo = strategyConfig.fullInfo;
			if(strategyConfig.fullInfo.rollFlag && !checkTime(strategyConfig.fullInfo.startTime,strategyConfig.fullInfo.endTime)) return;
			return true;
		}else if("oncetime" == data.backupInfo.type){
			var onceTime = $('#oncetime').val();
			if("" != onceTime){
				var systemTime = $('#servertime').text();
				var onceTimeSize = new Date(onceTime).getTime();
				var systemTimeSize = new Date(systemTime).getTime();
				if(onceTimeSize <= systemTimeSize){
					UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_CORRENT_TIME_TIPS);
					return false;
				}
				data.backupInfo.datetime = onceTime;
				return true;
			}else{
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
				return false;
			}

		}
	}
	//第三步获取限速策略信息
	var getSpeedStr =  function(){
		data.speedLimit = speedSubmitInfo();
		return true;
	}
	//第三步获取保留策略信息
	var getReserveStr = function(){
		// 保留类型
		data.highInfo.reserve.strategyMode = parseInt($('#reserveMode').val());
		data.highInfo.reserve.type = $('#reservetype').val();
		if(CONF.RESERVE_TYPE.NUM == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == data.highInfo.reserve.type){
			data.highInfo.reserve.value = $('#spinnerDayInput').val();
		}
		if(!(data.highInfo.reserve.value > 0)){
			UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
			return false;
		}
		//得到完全备份的勾选类型
		var checkInfo = "";
		if(data.backupInfo.fullInfo){
			checkInfo = data.backupInfo.fullInfo.type;
		}else{
			checkInfo = false;
		}
		//如果开启GFS保留策略
		if($('#GFSflag').get(0).checked){
			data.highInfo.reserve.gfs_strategy_item_list = $("#GFSDiv").getGFSData(checkInfo);
			//如果返回是false  则退出
	        if(!data.highInfo.reserve.gfs_strategy_item_list){
	    	   return false;
	        }
		}else{
			data.highInfo.reserve.gfs_strategy_item_list = {};
		}
        return true;
	}
	//第三步获取高级策略信息
	var getHighStr = function(){
		data.highInfo.mode.threadnum = $('#backupThreadNum').val();
		var thread = $('#backupThreadNum').val();
		if(thread == "" || thread > 8 || thread <= 0){
			//重置为默认值
			$('.backupThreadDiv').spinner("value", 3);
			initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_BACKUP_THREAD_NUM_TIPS);
			return false;
		}

		var syncnum = $('#backupSyncTimeNum').val();
		if(syncnum == "" || syncnum > 1000 || syncnum <= 0){
			//重置为默认值
			var showType = parseInt($('#asyncshowtype').val());
			switch(showType){
				case 1:
					$('.backupSyncTimeDiv').spinner("value", 50);
					break;
				case 2:
					$('.backupSyncTimeDiv').spinner("value", 3);
					break;
				case 3:
					$('.backupSyncTimeDiv').spinner("value", 1);
					break;	
			}
			initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_SYNC_CBR_SYNC_TIMEPOINT_NUM_CONFIGURE, LANG.UI_SYNC_CBR_SYNC_TIMEPOINT_NUM_CONFIGURE_TIPS);
			return false;
		}
		return true;
	}
	//第三步获取校验策略信息
	var getVerifyStr =  function(){
		//如果开启校验策略
		if($('#verifyflag').get(0).checked){
			data.verifyInfo.ischeck =  true;
		}else{
			data.verifyInfo.ischeck =  false;
		}
		return true;
	}
	//第三步获取传输策略
	var getTransferStr = function(){
		// data.highInfo.transfer.encrypt =  $('#encrypttransfer').get(0).checked;
		// 传输加密算法
		//data.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
		data.highInfo.transfer.mode = $('#transport_mode').val();
		return true;
	}
	//第三步获取存储策略
	var getStoreStr = function(){
		data.highInfo.store.compress_method = 0;
		data.highInfo.store.deduplication = $('#deduplicationcheck').get(0).checked;
		data.highInfo.store.blocksize = $('#blocksize').val();
		data.highInfo.store.compress = $('#compressCheck').get(0).checked;
		// 压缩等级
        if($('#compressCheck').get(0).checked){
            data.highInfo.store.compress_method = parseInt($('#compressGrade').val());
        }

		data.highInfo.store.encrypt = $('#encryptStorageCheck').get(0).checked;
        // 存储加密
        data.highInfo.store.encrypt_method = parseInt($('#storageEncryptMethod').val());
		data.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;
		data.highInfo.store.password = btoa(checkpassword());
		var repassword = $.trim($('#repassword').val());
		if(data.highInfo.store.password_auto_flag){
			data.highInfo.store.password = "";
		}else if(data.highInfo.store.encrypt && data.highInfo.store.password == ""){
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
			return false;
		}else if(_oldPassword =='' && data.highInfo.store.encrypt && data.highInfo.store.password != btoa(repassword)){//没有选择策略管理
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
			return false;
		}else if(_oldPassword !='' && data.highInfo.store.encrypt && data.highInfo.store.password != repassword){//选择了策略管理
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
			return false;
		}
		return true;
	}
	//存储之前检查是否有修改过密码
	var checkpassword = function(){
		var now_password = $.trim($('#password').val());
		//把现在的密码和读取到的密码做对比
		//先检查旧密码是否存在
		if(_oldPassword != ''){
			if(now_password == _oldPassword){  //没有修改密码
				return atob(now_password);
			}
		}
		return now_password;
		
	}
	return {
		init:function(){
			wizardInit();
			initMyTree();  //初始化树
			initSelectShowType(); //初始化下拉框选择类型
			initListener();  //初始化监听
			inintDatatimePicker();//初始化日期选择器
			initData(); //初始化备份流程数据    
			initSpinner();        //初始化Spinner 微调器
			initStrategy();       //初始化时间策略
			initDataChangeListeners(); //初始化策略事件
		}
	}
}();
jQuery(document).ready(function(){
	CBRBackup.init();
})