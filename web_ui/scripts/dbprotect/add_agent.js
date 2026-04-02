var AddAgent = function(){
	var zTree; //初始化树对象
	var grid;
	var initGridFlag = false; //初始化列表标志
	var initTreeFlag = false; //初始化实例树标记
    var zTreeAgent;
    var gridInitFlag = false;//初始化表格标记
    var clusterUUID;	//集群关联uuid
    
    var cluseterFlag;	//初始化集群代理树
    
	var initListener = function(){
		
		//切换认证方式
		$('#dbtypeCheck').on('change', dbTypeHandler);
		
		//切换认证方式
		$('#authtype').on('change', authHandler);
		
		//返回代理列表
		$('#goBack').on('click', toAgentList);
		
		//添加实例认证
		$('#auth_submit').on('click', authInstance);
		
		//开启Oracle集群关联
		$('#clusterCheck').on('switchChange.bootstrapSwitch', clusterChange);
		
		$('#btInstance').on('click', function(){
			//重置数据
			clusterUUID = "";
			$('#authtype').val('1');
			$("#authname").val("");
			$("#password").val("");
			$('#cnfPath').val("");
//			$('#dataPath').val("");
			$('#port').val("3306");
			$('#mysqlname').val("");
			$('#instancename').val("");
	    	$('#mysqlpassword').val("");
	    	$('#clusterCheck').bootstrapSwitch('disabled', false);
			$('#clusterCheck').bootstrapSwitch('state', false);
			
			$('.addTitleDiv').show();
			var dbType = $('#dbtypeCheck').val();
			if(dbType == CONF.DB_TYPE.ORACLE){
				$('.oracleAddDiv').show();
				initAgentTree();
			}else{
				$('.oracleAddDiv').hide();
			}
			$('.otherDiv').hide();
			$('#dbAuthModal').modal({'width':'600px', 'height':'360px'});
		});
		
	}
	
	var dbTypeHandler = function(){
		initGridFlag = false;
		if(grid){
			var oldTable = $('#instance_table').dataTable();
//			oldTable.fnClearTable(); //清空一下table
			oldTable.fnDestroy(); //还原初始化了的dataTable
		}
		initAuthInstance();
	}
	
	var clusterChange = function(){
		if(this.checked){
			$('.oracleTreeDiv').show();
			if(cluseterFlag){
				initAgentTree();
			}
		}else{
			$('.oracleTreeDiv').hide();
		}
	}
	
	//初始化关联集群树
	var initAgentTree = function(){
		cluseterFlag = true;
		var data = {};
		data.authFlag  = true;
		data.agentuuid = $('#agentUUID').val();
		data.clusteruuid = clusterUUID;
		var params = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.DBPROTECT,f:'getDBBackupAgentTree',p:params}, setTree);
		
	}
	
	//初始化代理树
	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$("#noagent").show();
			$("#agent_tree").hide();
			return;
		}else{
			$("#noagent").hide();
			$("#agent_tree").show();
		}
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true,
						idKey: "id",
						pIdKey: "pid",
						rootPId: 0
					},
					key: {
						title: "title"
					}
				},
				callback: {
					beforeExpand: nodeExpand,
					onCheck: agentCheck,
					beforeClick: nodeSelect
				}
			};
		zTreeAgent = $.fn.zTree.init($("#agent_tree"), setting, JSON.parse(zNodes));
	}
	
	var agentCheck = function(e, id, node){
		
	}
	
	var nodeSelect = function(treeId, treeNode, clickFlag){
		zTreeAgent.checkNode(treeNode, !treeNode.checked, false, true);
	}

	var nodeExpand = function(treeId, treeNode){
		if(treeNode.clickshow){
			if(treeNode.children) return true;
			getSyncAgentInfo(treeId, treeNode, false, false);
		}else{
			return true;
		}
		
	}
	
	//异步获取代理的实例列表  refreshFlag是否重新刷新
	var getSyncAgentInfo = function(treeId, treeNode, refreshFlag, expendFlag){
		var div = "#agent_tree";
		var dbType = $('#dbtypeCheck').val();
		var instancename = $('#uuid').val();
		var data = JSON.stringify({dbtype: dbType, agentuuid: treeNode.uuid, instancename: instancename});
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.DBPROTECT,f:'getReletiveInstanceTree',p:data},
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
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
	}
	
	//绑定实例行选定事件
	var initGridRowSelectListener = function(tableID){
		//这里说明一下,因为表格是动态生成的需要用到LIVE方法,但是需要注意的是表格的ID号不要和所有的ID号冲突
		$('#' + tableID).find('tbody > tr').off().on('click', function(){
			//1.取消所有选中项
			$(this).parent().find('input').prop("checked", false);
			//2.选中本行
			$(this).find('input').prop("checked", true);
		});
		
		$('#' + tableID).find('tbody > tr').find('input[type="checkbox"]').off().on('click', function(){
			//取消所有其他选中的
			var checkBoxs = $('#' + tableID).find('tbody > tr').find('input[type="checkbox"]');
			for(var i=0; i<checkBoxs.length; i++){
				var span = checkBoxs[i].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBoxs[i]).prop("checked", false);
			}
			var checkBox = $(this);
	        var checked = checkBox[0].checked;
	        if(checked){
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBox).prop("checked", false);
	    	}else{
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "checked");
		        $(checkBox).prop("checked", true);
	    	}
		})
	}
	
	
	var authInstance = function(){
		var data = {};
		if(grid){
			var select = grid.getSelectedRows();
			data.instancename = select[0];
		}
		data.dbtype = $('#dbtypeCheck').val();
		data.agentuuid = $('#agentUUID').val();
		data.authtype = $('#authtype').val();
		data.username = $('#authname').val();
		data.password = btoa($('#password').val());
		data.installname = $('#installname').val();
		data.verifydetail = "";
		data.clusterflag = $('#clusterCheck').get(0).checked;
		
		switch(parseInt(data.dbtype)){
			case CONF.DB_TYPE.SQLSERVER:
				if(data.authtype == 2){
					if(data.username == "" || data.password == ""){
						return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
					}
				}else{
					data.username = "";
					data.password = "";
				}
				
				break;
			case CONF.DB_TYPE.ORACLE:
				data.instancename = $('#instancename').val();
				if(data.instancename  == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_NAME);
				}
				if(data.insallname  == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_INSTALL_USER_NAME);
				}
				if(data.username == "" || data.password == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
				}
				var clusterInfo = [];
				if(data.clusterflag){
					var disableNodes = zTreeAgent.getNodesByParam("chkDisabled", true);
					var checkNodes = zTreeAgent.getCheckedNodes(true);
					if(disableNodes.length == 0 && checkNodes.length == 0){
						return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_SELECT_CLUSTER_CONNECT);
					}
					for(var i=0;i<disableNodes.length; i++){
						var node = {
								"agent_uuid": disableNodes[i].uuid,
						}
						clusterInfo.push(node);
					}
					for(var j=0;j<checkNodes.length; j++){
						var node = {
								"agent_uuid": checkNodes[j].uuid,
						}
						clusterInfo.push(node);
					}
				}
				data.clusterInfo = clusterInfo;
				break;
			case CONF.DB_TYPE.DM:
				if(data.insallname  == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_INSTALL_USER_NAME);
				}
				if(data.username == "" || data.password == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
				}
				break;
			case CONF.DB_TYPE.MYSQL:
				//mysql认证参数
				data.username = $('#mysqlname').val();
				data.password = btoa($('#mysqlpassword').val());
				var cnfPath = $('#cnfPath').val();
//				var dataPath = $('#dataPath').val();
				var port = $('#port').val();
				data.instancename = "127.0.0.1:" + port;
				data.verifydetail = JSON.stringify({
					'cnf_path': cnfPath,
//					'data_dir': dataPath,
					'port': port
				});
				if(cnfPath == "" || port == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_INPUT_CONFIG_INFO);
				}
				if(data.username == "" || data.password == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
				}
				
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.HIGHGO:
			case CONF.DB_TYPE.OPENGAUSS:
				data.installname = "";
				var databaseName = $('#installname').val();
				var installPath = $('#installpath').val();
				if(installPath == ""){
					return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_INPUT_CONFIG_INFO);
				}
				data.verifydetail = JSON.stringify({
					'database_name': databaseName,
					'install_db_path': installPath
				});
				break;
		}
		var p = JSON.stringify(data);
		Metronic.blockUI({target: '#dbAuthModal',animate: true});
		$.post(CONF.AJAXPATH, {m: CONF.M.DBPROTECT, f: 'AuthDBInstance', p: p}, function(d){
			Metronic.unblockUI('#dbAuthModal');
			if(OPREL(d)){
				$('#dbAuthModal').modal('hide');
				grid.getRefresh({});
			}
		});
	}
	
	var toAgentList = function(){
		LOCATION('./content/dbprotect/db_agent_manager.php', "db_agent_manager");
	}
	
	
	var authHandler = function(){
    	if(this.value == 2){
    		$('.userDiv').show();
    	}else{
    		$('.userDiv').hide();
    	}
    	
    }
	
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		
		initGridRowSelectListener('instance_table');
	}
	
	
	var handleRecords = function () {
		var type = parseInt($('#dbtypeCheck').val());
		var visible = true;
		var postvisible = false;
		if(type != CONF.DB_TYPE.SQLSERVER){
			visible = false;
		}
		
		//显示实例集簇路径
		if(type == CONF.DB_TYPE.POSTGRE || type == CONF.DB_TYPE.KINGBASE || type == CONF.DB_TYPE.UXDB || type == CONF.DB_TYPE.HIGHGO || type == CONF.DB_TYPE.OPENGAUSS){
			postvisible = true;
			$('#instanceTitle').html(LANG.UI_DB_INSTANCE_PORT);
		}else{
			postvisible = false;
			$('#instanceTitle').html(LANG.UI_DB_INSTANCE_NAME);
		}
		var dataTableOpt = {
			"ordering": false,
			"columnDefs": [    
		        {    
		              "targets": [5], //如果是oracle隐藏第五列，从第0列开始   
		              "visible": visible    
		        },
		        {    
		              "targets": [7], //如果是oracle隐藏第五列，从第0列开始   
		              "visible": postvisible    
		        }
		    ]   
                
    	};
    	grid = new Datatable();
    	var agentuuid = $('#agentUUID').val();
		var data = {m:CONF.M.DBPROTECT,f:'getDBInstance',p:{agentuuid: agentuuid, type: type}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#instance_table"), checkbox:false, dataTable:dataTableOpt, onDataLoad:addOpButton});
    	
    	if(!gridInitFlag){
    		$('#addsubmit').off().on('click', function(){
        		var select = grid.getSelectedRows();
        		var dbType = $('#dbtypeCheck').val();
        		//如果不是mysql
    			if(select.length == 0){
        			return UIToastr.showInfo(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_AUTH_NO_SELECT_TIPS);
        		}
        		
        		if(select.length > 1){
        			return UIToastr.showInfo(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_AUTH_SELECT_ONE_TIPS);
        		}
        		$('.addTitleDiv').hide();
    			$('.otherDiv').show();
        		$('#dbAuthModal').modal({'width':'600px', 'height':'360px'});
        		initOldAuthInfo(select);
    		});
    		gridInitFlag = true;
    	}
    	return;
	}
	
	
	//获取列表勾选行索引
	var getTableIndex = function(){
		var index = 0;
		var table = $('#instance_table').dataTable();
		var nTrs = table.fnGetNodes();//fnGetNodes获取表格所有行，nTrs[i]表示第i行tr对象
		if(nTrs.length != 0){
			for(var i = 0; i < nTrs.length; i++){
	 		   if($(nTrs[i]).find('input').is(":checked")){
	 		   		//获取选中行的索引
	 			   index = i;
	 			   return index;
	 		   }
	 	   	}
		}
 	   	return index;
	}
	
	var initOldAuthInfo = function(uuid){
		$('#uuid').val(uuid);
		var dbType = $('#dbtypeCheck').val();
		var index = getTableIndex();
		var data = grid.getDataTable().data();
 	   	var rawData = data[index];
		var authInfo = rawData[8];
		var instancename = rawData[1];
		var detail = authInfo.other_info;
		//实例数据库名描述修改
		$('.installLabel').html(LANG.UI_DB_INSTALLATION_DATABASE_NAME);
		$('.installTips').html(LANG.UI_DB_INSTALLATION_DATABASE_USER);
		$('.installpathDiv').hide();
		switch(parseInt(dbType)){
		    case CONF.DB_TYPE.SQLSERVER:
		    	$('.authtypeDiv').show();
		    	if(authInfo.authFlag){
		    		$('#authtype').val(authInfo.auth_type);
			    	$('#authname').val(authInfo.user_name);
			    	$('#password').val(atob(authInfo.password));
		    	}
		    	if(authInfo.auth_type == 2){
					$('.userDiv').show();
				}else{
					$('.userDiv').hide();
				}
		    	$('.installnameDiv').hide();
		    	break;
			case CONF.DB_TYPE.ORACLE:
				$('.userDiv').show();
				$('.oracleDiv').show();
				$('#installname').val("oracle");
				$('.oracleAddDiv').hide();
				$('#instancename').val(instancename);
				if(authInfo.authFlag){
					$('#installname').val(authInfo.install_db_username);
			    	$('#authname').val(authInfo.user_name);
			    	$('#password').val(atob(authInfo.password));
			    	$('#clusterCheck').bootstrapSwitch('state', authInfo.clusterFlag);
			    	if(authInfo.clusterFlag){
			    		$('.oracleTreeDiv').show();
			    		clusterUUID = authInfo.clusteruuid;
			    		$('#clusterCheck').bootstrapSwitch('disabled', true);
			    	}else{
			    		$('.oracleTreeDiv').hide();
			    		clusterUUID = "";
			    	}
		    	}
				initAgentTree();
				break;
			case CONF.DB_TYPE.MYSQL:
				$('.mysqlDiv').show();
				if(authInfo.authFlag){
					var mysqlInfo = authInfo.other_info;
					$('#cnfPath').val(mysqlInfo.cnf_path);
//					$('#dataPath').val(mysqlInfo.data_dir);
					$('#port').val(mysqlInfo.port);
			    	$('#mysqlname').val(authInfo.user_name);
			    	$('#mysqlpassword').val(atob(authInfo.password));
		    	}
				break;
			case CONF.DB_TYPE.DM:
				$('.userDiv').show();
				$('#installname').val("dmdba");
				
				if(authInfo.authFlag){
					$('#installname').val(authInfo.install_db_username);
			    	$('#authname').val(authInfo.user_name);
			    	$('#password').val(atob(authInfo.password));
		    	}
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.OPENGAUSS:
				//实例数据库名描述修改
				$('.installLabel').html(LANG.UI_DB_NAME);
				$('.installTips').html(LANG.UI_DB_INSTANCE_ANY_DATABASE);
				$('.userDiv').show();
				$('#installname').val("postgres");
				
				if(authInfo.authFlag){
					if(detail && detail.database_name){
						$('#installname').val(detail.database_name);
					}
					if(detail && detail.install_db_path){
						$('#installpath').val(detail.install_db_path);
					}
			    	$('#authname').val(authInfo.user_name);
			    	$('#password').val(atob(authInfo.password));
		    	}
				$('.installpathDiv').show();
				break;
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.HIGHGO:
				//实例数据库名描述修改
				$('.installLabel').html(LANG.UI_DB_NAME);
				$('.installTips').html(LANG.UI_DB_INSTANCE_ANY_DATABASE);
				$('.userDiv').show();
				$('#installname').val("test");
				
				if(authInfo.authFlag){
					if(detail && detail.database_name){
						$('#installname').val(detail.database_name);
					}
					if(detail && detail.install_db_path){
						$('#installpath').val(detail.install_db_path);
					}
			    	$('#authname').val(authInfo.user_name);
			    	$('#password').val(atob(authInfo.password));
		    	}
				$('.installpathDiv').show();
				break;
		    
 		}
		//重置数据
		if(!authInfo.authFlag){
			$('#authtype').val('1');
			$("#authname").val("");
			$("#password").val("");
			$('#mysqlname').val("");
	    	$('#mysqlpassword').val("");
			$('#cnfPath').val("");
//			$('#dataPath').val("");
			$('#port').val("3306");
			$('#clusterCheck').bootstrapSwitch('state', false);
		}
		
	}
	
	var initAuthInstance = function(){
		var data = {};
		data.dbtype = $('#dbtypeCheck').val();
		$('#dbtypeCheck').val(data.dbtype);
		data.agentuuid = $('#agentUUID').val();
		//切换数据库类型
		$('.authtypeDiv').hide();
		$('.userDiv').hide();
		$('.mysqlDiv').hide();
		$('.oracleDiv').hide();
		$('.oracleTreeDiv').hide();
		$('.addInstance').hide();
		$('.oracleAddDiv').hide();
		var type = parseInt($('#dbtypeCheck').val());
		var clusterCheck = $('#clusterCheck').get(0).checked;
		switch(type){
			case CONF.DB_TYPE.SQLSERVER:
				$('.authtypeDiv').show();
				break;
			case CONF.DB_TYPE.ORACLE:
				$('.userDiv').show();
				$('.oracleDiv').show();
				$('.addInstance').show();
				$('#installname').val("oracle");
				$('.oracleAddDiv').show();
				if(!clusterCheck){
					$('.oracleTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.MYSQL:
				$('.authtypeDiv').hide();
				$('.userDiv').hide();
				$('.mysqlDiv').show();
				$('.addInstance').show();
				break;
			case CONF.DB_TYPE.DM:
				$('.userDiv').show();
				$('#installname').val("dmdba");
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.OPENGAUSS:
				$('.userDiv').show();
				$('#installname').val("postgres");
				break;
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.HIGHGO:
				$('.userDiv').show();
				$('#installname').val("test");
				break;
				
				
		}
		
		if(!initGridFlag){
			handleRecords();
			initGridFlag = true;
		}else{
			$('.authtypeDiv').hide();
			var agentuuid = $('#agentUUID').val();
			var type = $('#dbtypeCheck').val();
			var p = {start:0, length:10, search:{}, agentuuid: agentuuid, type: type};
			var data = {m:CONF.M.DBPROTECT,f:'getDBInstance',p:p};
			grid.setAjaxParam(data);
			grid.getRefresh(p, undefined, true);
		}
		
	}
	
	
	//初始化数据库类型选择
	var initDbTypeSelect = function(){
		var data = {};
		data.agentuuid = $('#agentUUID').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH,{m:CONF.M.DBPROTECT,f:"getDbTypeList",p:p}, function(d){
			var jsonData = JSON.parse(d);
			var list = jsonData.list;
			var select = jsonData.select;
			var dbType = $('#dbtypeCheck');
			dbType.empty();
			for(var i=0; i<list.length; i++){
				var option = $("<option>").text(list[i].text).val(list[i].value);
				dbType.append(option);
			}
			if(select.length != 0){
				dbType.val(select[0]);
			}
			initAuthInstance();
		});
	}
	
	//初始化数据库代理名
	var initAgentName = function(){
		var data = {};
		data.agentuuid = $('#agentUUID').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH,{m:CONF.M.DBPROTECT,f:"getAgentNameByUUID",p:p}, function(d){
			var jsonData = JSON.parse(d);
			$('#agentName').html(jsonData.agentName);
		});
	}
	
	//检查路径格式
	var checkPath = function(value, flag){
		var value = value.replace(/\//gi, "/");//正则替换  把输入的\替换成/
		
		var re1='((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])';	// IPv4 IP Address 1
		var re2='(:)';	// Any Single Character 1
		var re3='((?:\\/[\\w\\.\\-]+)+)';	// Unix Path 1
		var re4 = '(.*)';
		var path = /^([a-zA-Z]:|\\\\[^\\\\/:*?"<>|]+)(\\[^\\\\/:*?"<>|]+)*\\?$/;
		var linux_path =  '^\\/(\\w+\\/\\-?)+$';//linux路径检测
		var cn_word = '[\u4e00-\u9fa5]';//中文检测
		var p1 = new RegExp(re1+re2+re3,["i"]);
		var p2 = new RegExp(re4+re2+re3, ["i"]);
		var p3 = new RegExp(linux_path);
		var p4 = new RegExp(cn_word,["g"]);
		if(flag == 'Linux'){//如果为linux系统则只能输入linux系统目录
			var a = p3.exec(value);
			return !!(p3.exec(value) && !p4.exec(value))
		}else if(flag == 'Windows'){
			return !!(p2.exec(value) && !p4.exec(value))
		}
		 return !!((p2.exec(value) || p3.exec(value)) && !p4.exec(value));
		
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	initAgentName();
        	initDbTypeSelect();
        	initListener();
//        	initAgentTree();
        }

    };
}();

jQuery(document).ready(function() {   
	AddAgent.init();
});