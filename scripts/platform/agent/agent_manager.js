var AgentManager = function () {
	
	var grid,moduleFun,agentUUID;
	var licenseType;
	
	var handleRecords = function () {
		//读取cookie里的保存列表状态
		var length = "";
		var updateInterval = 5000;
		if($.cookie('pageLength')){
			var pageList = JSON.parse($.cookie('pageLength'));
			if(pageList.agent){
	    		length = pageList.agent;
	    	}
		}
    	var dataTableOpt = {
    		'pageLength': parseInt(length),
    		'columnDefs' : [{
                'orderable': false,
                'targets': [0, 7]
			}],
			"order": [
                [1, "desc"]
            ],
    	};
	var initGrid = function () { 
    	grid = new Datatable();
		var data = {m:CONF.M.AGENT,f:'getAgentInfo',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#agentmanagerdatatable"), dataTable:dataTableOpt, onDataLoad:addTableInfo});
    	// return;
		grid.getRefresh();
		timerTask.agent_manager_data = setTimeout(initGrid, updateInterval);
		clearTimeout(timerTask.agent_manager_data);
	}
	initGrid();
    }
	
	//添加表格信息
	var addTableInfo = function(){
		var data = grid.getDataTable().data();
		var opDiv = $('#agentlist tbody > tr').find('td:eq(7)');
		var moduleDiv = $('#agentlist tbody > tr').find('td:eq(4)');
		var statusDiv = $('#agentlist tbody > tr').find('td:eq(6)');
		for(var i=0; i<opDiv.length; i++){
			addOpBtn(opDiv[i], data[i], i);
			addModule(moduleDiv[i], data[i]);
			addStatus(statusDiv[i], data[i]);
		}
	}
	
	//添加模块信息
	var addModule = function(div, data){
		var module = data[div.cellIndex];
		var html = "";
		for(var i=0; i<module.length; i++){
			if(!module[i]){
				continue;
			}
			switch(i){
				case 0:
					html += '<a title="' + LANG.UI_AGENT_MODULE_FILE + '"><i class="fa fa-file"></i></a>';
					break;
				case 1:
					html += '&nbsp;&nbsp;<a title="' + LANG.UI_AGENT_MODULE_MYSQL + '"><i class="fa fa-database"></i></a>';
					break;
			}
		}
		$(div).html(html);
	}
	
	//添加状态信息
	var addStatus = function(div, data){
		var labelClass = getStatusClass(data[7]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[6] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getStatusClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			case 3:
				levelClass = "label-default";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
	//添加操作按钮
	var addOpBtn = function(div, data, rowNum){
		var button = '<div class="btn-group positionabs ">';
		if(rowNum > 4){
			button = '<div class="btn-group positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu">';
		
		//1在线注册/2在线未注册/3离线注册
		switch(data[7]){
			case 1:
				button += '<li class="edit"><a href="javascript:;" ><i class="fa fa-pencil"></i> ' + LANG.UI_JOB_MODIFY + '</a></li>';
				button += '<li class="unregist"><a href="javascript:;" ><i class="glyphicon glyphicon-remove"></i> ' + LANG.UI_AGENT_UNREGISTER + '</a></li>';
				break;
			case 2:
				button += '<li class="regist"><a href="javascript:;"><i class="viconfont vicon-ge_add_task"></i> ' + LANG.UI_AGENT_REGISTER + '</a></li>';
				break;
			case 3:
				button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
				break;
				
		}
		button += '</ul></div>';
		$(div).html(button);
		$('[data-hover="dropdown"]').dropdownHover();
		//修正下拉显示不全
//		$('.min-width100').addClass("positionrel");
		addOpButtonListener();
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('.edit').unbind().on('click', editAgent);
		$('.regist').unbind().on('click', registAgent);
		$('.delete').unbind().on('click', deleteAgent);
		$('.unregist').unbind().on('click', unregistAgent);
	}
	
	//修改代理端
	var editAgent = function(){
		initModal(this, 'editAgent');
	}
	
	//注册代理端
    var registAgent = function(){
    	initModal(this, 'registAgent');
    }
    
    //删除代理端
    var deleteAgent = function(){
    	var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var rowData = data[row];	//本行数据
		var uuid = rowData[8].uuid	//主机UUID;
		var params = {agentUUID:uuid};
    	params = JSON.stringify(params);
    	Metronic.blockUI({target: '#agentlist',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'deleteAgent',p:params}, function(data){
			Metronic.unblockUI('#agentlist');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
    }
    
  //取消注册代理端
    var unregistAgent = function(){
    	var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var rowData = data[row];	//本行数据
		var uuid = rowData[8].uuid	//主机UUID;
		var params = {agentUUID:uuid};
    	params = JSON.stringify(params);
    	Metronic.blockUI({target: '#agentlist',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'unregistAgent',p:params}, function(data){
			Metronic.unblockUI('#agentlist');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
    }
    
    //初始化模态页面
    var initModal = function(button, funName){
    	var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var rowData = data[row];	//本行数据
		var hostName = rowData[0];	//主机名
		var modules = rowData[4];	//模块授权
		var uuid = rowData[8].uuid	//主机UUID;
		var useruuid = rowData[8].useruuid //用户UUID
		var p = JSON.stringify({fileFlag: true, useruuid: useruuid});
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getManagerUsers',p:p}, function(data){
			var users = JSON.parse(data);
			if(users.length == 0) return;
			var select = $('#user');
			var option = "";
			for(var i=0; i<users.length; i++){
				option += '<option value="' + users[i].uuid + '">' + users[i].name + '</option>';
			}
			select.empty();
			select.append(option);
			if(useruuid && useruuid != ""){
				select.val(useruuid);
			}
    	});
		$('#agentname').val(hostName);
		
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getFileLisenceInfo',p:{}}, function(data){
			var d = JSON.parse(data);
			var html = "";
			html = LANG.UI_SEARCH_FILE_LICENSE + ': ' + LANG.UI_VCENTER_AUTH_TOTAL + ' ' + 
					d.total + ', ' + LANG.UI_SETTING_USED_NUM + ' ' + d.used + ', ' + LANG.UI_SETTING_VALID_NUM + ' ' + d.valid;
			$('#filelisence').html(html);
			
			$('#filelisence').show();
			//容量授权
			if(CONF.TENANTUUID ==""){
				if(licenseType == 3){
					$('#filelisence').hide();
		    	}
			}else if(CONF.TENANTUUID !="" && !d.showflag){
				//租户内按容量授权
				$('#filelisence').hide();
			}
			
    	});
		
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getModulesLisence',p:{}}, function(data){
			var lisence = JSON.parse(data);
			$('#file').iCheck('check');
			$('#mysql').iCheck('uncheck');
			$('#submit').prop('disabled', false);
			for(var i=0; i<modules.length; i++){
				if(0 == i){
					if(modules[i]){
						$('#file').iCheck('check');
					}else{
						$('#file').iCheck('uncheck');
					}
				}
				if(1 == i && modules[i]){
					$('#mysql').iCheck('check');
				}
			}
			
			if(licenseType == 3){
	    		$('.authcheckDiv').hide();
	    		$('#file').iCheck('enable');
	    		$('#file').iCheck('check');
	    	}else{
	    		$('.authcheckDiv').show();
	    		if(lisence.file <= 0){
		    		$('#file').iCheck('uncheck');
					$('#file').iCheck('disable');
					$('#submit').prop('disabled', true);
				}
	    		if(lisence.mysql <= 0){
					$('#mysql').iCheck('uncheck');
					$('#mysql').iCheck('disable');
				}
	    	}
    	});
		//给全局变量赋值,识别是修改还是注册
		moduleFun = funName;
		//给全局变量赋值,代理端UUID
		agentUUID = uuid;
    	$('#modaldiv').modal();
    	//定义iCheck样式
    	$('#modaldiv input').iCheck({
    	    checkboxClass: 'icheckbox_square-blue',
    	    radioClass: 'iradio_square-blue',
    	    increaseArea: '20%' // optional
    	});
    }
    
    //添加事件
    var addListeners = function(){
    	//切换页数保存到cookie
		$('select[name=datatable_length]').on('change', function(){
			pageLength.agent = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		
		$('#searchbtn').on('click',function(){
			var name = $.trim($('.searchinput').val());
			var p = {start:0, length:10, search:{name:name}};
			var data = {m:CONF.M.AGENT,f:'getAgentInfo',p:p};
    		grid.setAjaxParam(data);
			grid.getRefresh(p, undefined, true);
		});
		
    	$('#submit').on('click', agentOpt);
    	
    	//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'500px'});
		});
		
		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			var p = {};
			p.hostName = $('#hostName').val();
			p.agentIp = $('#agentIp').val();
			p.onlineFlag = $('#onlineFlag').val();
			p.registerFlag = $('#registerFlag').val();
			p.userName = $('#userName').val();
			var params = {start:0, length:10, search: p, accurateFlag: true};
			var data = {m:CONF.M.AGENT,f:'getAgentInfo',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			grid.getRefresh(params, undefined, true);
		});
    }
    //显示搜索内容
	var addSearchContent = function(p){
		var info = ""; 
		$('.searchContent').text('');
		var hostName = xssEncode($('#hostName').val());
		var agentIp = xssEncode($('#agentIp').val());
		var onlineFlag = xssEncode($('#onlineFlag').val());
		var registerFlag = $('#registerFlag').val();
		var userName = $('#userName').val();
		if(p.hostName!=""){
			info += '<span class="hostName" title="' + hostName + '"> '+ LANG.UI_BACKUP_FILE_HOSTNAME +': <i>' + hostName + '</i><em>X</em></span>';
		}
		if(p.agentIp!=""){
			info += '<span class="agentIp" title="' +  agentIp + '"> '+ LANG.UI_CLIENT_IP_ADDRESS +': <i>' + agentIp + '</i><em>X</em></span>';
		}
		if(p.onlineFlag !=0){
			info += '<span class="onlineFlag" title="' + $('#onlineFlag').find("option:selected").text() + '"> '+ LANG.UI_BACKUP_FILE_AGENT_STATUS +': <i>' + $('#onlineFlag').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.registerFlag!=0){
			info += '<span class="registerFlag" title="' +  $('#registerFlag').find("option:selected").text() + '"> '+ LANG.UI_BACKUP_FILE_REGISTER_STATUS +': <i>' + $('#registerFlag').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.userName!=""){
			info += '<span class="userName" title="' + userName + '"> ' + LANG.UI_STORAGE_DETAIL_USERNAME + ': <i>' + userName + '</i><em>X</em></span>';
		}
		$('.searchContent').append(info);
		$('#searchDiv').show();
		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('.searchContent');
			if(searchContent[0].children.length == 0){
				$('#searchDiv').hide();
			}
			var parent  = $(this).parent();
			var id = parent[0].className;
			p[id] = "";
			p.onlineFlag = p.onlineFlag==""?0:p.onlineFlag;
			p.registerFlag = p.registerFlag==""?0:p.registerFlag;
			var params = {start:0, length:10, search: p, accurateFlag: true};
			var data = {m:CONF.M.AGENT,f:'getAgentInfo',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			var params = {start:0, length:10, search: {}, accurateFlag: false};
			var data = {m:CONF.M.AGENT,f:'getAgentInfo',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();
		}
	}
    //注册/修改代理端
    var agentOpt = function(){
    	var hostName = $('#agentname').val();
    	var user = $('#user').val();
    	var file = $('#file').is(':checked');
    	var mysql = $('#mysql').is(':checked');
    	if($.trim(hostName) == '' || user == ''){
    		$('#modaltips').show();
    		return;
    	}
    	$('#modaltips').hide();
    	var module = {file:file, mysql:mysql};
    	var params = {hostName:hostName, userUUID:user, agentUUID:agentUUID, module:module};
    	params = JSON.stringify(params);
    	
    	Metronic.blockUI({target: '#modaldiv',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:moduleFun,p:params}, function(data){
    		Metronic.unblockUI('#modaldiv');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			$('#modaldiv').modal('hide');
    		}
    	});
    }

    var initLicenseType = function(){
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemLicenseType',p:{}}, function(d){
			var data = JSON.parse(d);
			licenseType = data.licensetype;
		});
    }

    return {
        //main function to initiate the module
        init: function () {
            handleRecords();
            addListeners();
            initLicenseType();
        }

    };

}();

jQuery(document).ready(function() {    
    AgentManager.init();
});