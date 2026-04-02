var DbAgentManager = function () {

	var grid;
	var agentUUID;
	var initAgentModalFlag = false;
	var _licenseType;
	
    var handleRecords = function () {
    	//读取cookie里的保存列表状态
    	var length = "";
    	if($.cookie('pageLength')){
    		var pageList = JSON.parse($.cookie('pageLength'));
    		if(pageList.dbagent){
        		length = pageList.dbagent;
        	}
    	}
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 6, 9]
    			}],
    			"order": [
                    [5, "desc"]
                ],
                'pageLength': parseInt(length)
    	};
    	grid = new Datatable();
		var data = {m:CONF.M.DBPROTECT,f:'getDbAgent',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#agentTable"), dataTable:dataTableOpt, onDataLoad:addTableInfo});
    	
    	$("#delete").on('click', function(){
    		deleteAgent(grid);
    	});
    	
		
    	return;
    }
    
   var initAgentModal = function(div){
	   var data = grid.getDataTable().data();
	   var row = $(div).parents('tr').get(0)._DT_RowIndex;
	   var agentuuid = data[row][11];
	   var p = JSON.stringify({agentuuid: agentuuid});
	   $.post(CONF.AJAXPATH, {m:CONF.M.DBPROTECT,f:'getDBAgentInfo',p: p}, function(d){
		   var data = JSON.parse(d);
		   if(data.length != 0){
			   $('#agentIp').val(data.ip);
			   $('#agentName').val(data.agentName);
			   $('#port').val(data.managePort);
			   $('#transferport').val(data.transferPort);
			   $('#clientport').val(data.clientPort);
			   $('#agentIp').prop("disabled", true);
		   }
		   $('#addAgentModal').modal({'width':'500px', 'height':'360px'});
		   initAgentModalFlag =true;
	   });
   }
    
  //添加表格信息
	var addTableInfo = function(){
		var data = grid.getDataTable().data();
		var moduleDiv = $('#agentTable tbody > tr').find('td:eq(7)');
		var statusDiv = $('#agentTable tbody > tr').find('td:eq(8)');
		var opDiv = $('#agentTable tbody > tr').find('td:eq(9)');
		for(var i=0; i<moduleDiv.length; i++){
			opButton(opDiv[i], data[i][9], i, data[i][11]);
			addModule(moduleDiv[i], data[i]);
			addStatus(statusDiv[i], data[i]);
		}
		
		//opButton 对应点击操作
		addOpButtonListener();
	}
	
	var addOpButtonListener = function(){
		$('.auth').unbind().on('click', authAgent);	//代理授权
		$('.confirm').unbind().on('click', confirmAgent); //实例认证
		$('.modify').unbind().on('click', modifyAgent);	//修改代理
		
	}
	
	var modifyAgent = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
    	agentUUID = data[row][11];
		var des = '<i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_DB_MODIFY_AGENT_TITLE;
		$('.agentTitle').html(des);
		$('#addagent_submit').hide();
		$('#modify_submit').show();
		initAgentModal(this);
	}
	
	//添加操作按钮
	var opButton = function(div, opCode, rowNum, agentuuid){
		var button = '<div class="btn-group  positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group  positionabs dropup">';
		}
		
		button += '<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu" id="'+ agentuuid +'">';
		
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="auth"><a href="javascript:;" ><i class="viconfont vicon-ge_authorization2"></i> ' + LANG.UI_DB_AUTH_AGENT + '</a></li>';
					break;
				case 2:
					button += '<li class="confirm"><a href="javascript:;"><i class="iconfont icon-yanzheng"></i> ' + LANG.UI_DB_AGENT_INSTANCE_AUTH + '</a></li>';
					break;
				case 3:
					button += '<li class="modify"><a href="javascript:;"><i class="fa fa-pencil"></i> ' + LANG.UI_DB_MODIFY_AGENT + '</a></li>';
					break;
			}
		});
		button += '</ul></div>';
		$(div).html(button);
		
	}
	
	//添加模块信息
	var addModule = function(div, data){
		var module = data[div.cellIndex];
		var html = "";
		for(var i=0; i<module.length; i++){
			if(!module[i]){
				continue;
			}
			if(module[i] && i == 5){
				html += '&nbsp;&nbsp;<a title="' + LANG.UI_AGENT_MODULE_DB + '"><i class="fa fa-database"></i></a>';
			}
		}
		$(div).html(html);
	}
	
	//添加状态信息
	var addStatus = function(div, data){
		var labelClass = getStatusClass(data[10]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[8] + '</span>';
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
				levelClass = "label-default";
				break;
			default:
				levelClass = "label-default";
				break;
		}
		return levelClass;
	}
	
    
    var authAgent = function(){
    	var row = $(this).parents('tr').get(0)._DT_RowIndex;
    	initModal(row);
    	
    }
    
  //获取列表勾选行索引
	var getTableIndex = function(){
		var index = 0;
		var table = $('#agentTable').dataTable();
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
	
    
    var initModal = function(index){
    	var gridData = grid.getDataTable().data();
    	var data = gridData[index];
		var hostName = data[1];	//主机名
		var nickName = data[2];	//主机名
		var modules = data[7];	//模块授权
		var agentuuid = data[11];	//主机UUID;
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getManagerUsers',p:{}}, function(data){
			var users = JSON.parse(data);
			if(users.length == 0) return;
			var select = $('#user');
			var option = "";
			for(var i=0; i<users.length; i++){
				option += '<option value="' + users[i].uuid + '">' + users[i].name + '</option>';
			}
			select.empty();
			select.append(option);
    	});
		$('#agentname').html(hostName);
		$('#agentNickname').val(nickName);
		
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getDBLisenceInfo',p:{}}, function(data){
			var d = JSON.parse(data);
			var html = "";
			html = LANG.UI_DB_DATABASE_AUTH + ': ' + LANG.UI_VCENTER_AUTH_TOTAL + ' ' + 
					d.total + ', ' + LANG.UI_SETTING_USED_NUM + ' ' + d.used + ', ' + LANG.UI_SETTING_VALID_NUM + ' ' + d.valid;
			$('#dblisence').html(html);
			
			$('#dblisence').show();
			//容量授权
			if(CONF.TENANTUUID ==""){
				if(_licenseType == 3){
		    		$('#dblisence').hide();
		    	}
			}else if(CONF.TENANTUUID !="" && !d.showflag){
				//租户内按容量授权
				$('#dblisence').hide();
			}
    	});
		
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getModulesLisence',p:{}}, function(data){
			var lisence = JSON.parse(data);
			$('#database').iCheck('uncheck');
			if(modules && modules.length != 0 ){
				for(var i=0; i<modules.length; i++){
					//数据库授权
					if(5 == i && modules[i]){
						$('#database').iCheck('check');
					}
				}
			}
			//租户外数据库授权显示处理
			if(CONF.TENANTUUID ==""){
				if(_licenseType == 3){
		    		$('#database').iCheck('enable');
		    		$('#database').iCheck('check');
		    	}else{
		    		if(lisence.database <= 0){
			    		$('#database').iCheck('uncheck');
			    		$('#database').iCheck('disable');
						$('#auth_submit').prop('disabled', true);
					}
		    	}
			}
			
    	});
		//给全局变量赋值,代理端UUID
		agentUUID = agentuuid;
    	$('#authAgentModal').modal();
    	//定义iCheck样式
    	$('.authCheck').iCheck({
    	    checkboxClass: 'icheckbox_square-blue',
    	    radioClass: 'iradio_square-blue',
    	    increaseArea: '20%' // optional
    	});
		
    }
    
	var confirmAgent = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var module = data[row][7];
		var agentuuid = data[row][11];
		
    	var url = './content/dbprotect/add_agent.php?uuid=' + agentuuid;
    	LOCATION(url);
		
	}
	
	var deleteAgent = function(grid){
		var select = grid.getSelectedRows();
    	if(select.length == 0){
    		return UIToastr.showInfo(LANG.UI_DB_AGENT_DELETE, LANG.UI_DB_AGENT_DELETE_NO_SELECT_TIPS);
    	}
    	if(select.length >= 2){
    		return UIToastr.showInfo(LANG.UI_DB_AGENT_DELETE, LANG.UI_DB_AGENT_SELECT_ONE_DELETE);
    	}
    	
    	bootbox.confirm({
            title: LANG.UI_DB_AGENT_DELETE,
            message: LANG.UI_DB_AGENT_DELETE_CONFIRM_TIPS,
            callback: function(r) {
                if(!r) return;
                submitDelete(select, grid);
            }
        });
	}
	
	var submitDelete = function(select, grid){
    	var data = {};
		data.uuids = select;
		data = JSON.stringify(data);
		var grid = grid;
		Metronic.blockUI({target: '#dbagentcontent',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBPROTECT,f:'deleteAgent',p:data}, function(data){
    		Metronic.unblockUI('#dbagentcontent');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
    }
    

    var addListeners = function(){
    	//切换页数保存到cookie
		$('select[name=datatable_length]').on('change', function(){
			pageLength.dbagent = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
    	
    	//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'260px'});
		});
		
		//添加客户端模态框
		$('#add').on('click', function(){
			var des = '<i class="glyphicon glyphicon-plus"></i> ' + LANG.UI_DB_ADD_AGENT_TITLE;
			$('.agentTitle').html(des);
			$('#modify_submit').hide();
			$('#addagent_submit').show();
			$('#agentIp').prop("disabled", false);
			$('#agentIp').val('');
			$('#agentName').val('');
		    $('#port').val("20200");
		    $('#transferport').val("20300");
		    $('#clientport').val("20400");
			$('#addAgentModal').modal({'width':'500px', 'height':'360px'});
		});
		
		
		//注册客户端
		$('#auth_submit').on('click', authSubmit);
		
		//实例认证
		$('#verify_submit').on('click', function(){
			var agentuuid = $('#agentUUID').val();
			var dbType = $('#dbType').val();
	    	var url = './content/dbprotect/add_agent.php?uuid=' + agentuuid + "&dbtype=" + dbType;
	    	LOCATION(url);
		});
		
		$('#agentIp').on('change',function(){
	    	$('#agentName').val(this.value);
	    });
    }
    
    //授权提交
    var authSubmit = function(){
    	var hostName = $('#agentNickname').val();
    	var user = $('#user').val();
    	var file = false;
    	var sqlserver = false;
    	var oracle = false;
    	var mysql = false;
    	var dmsql = false;
    	var database = $('#database').is(':checked');
    	if($.trim(hostName) == '' || user == ''){
    		$('#modaltips').show();
    		return;
    	}
    	$('#modaltips').hide();
    	var module = {file:file, sqlserver:sqlserver, oracle: oracle, mysql: mysql, dm:dmsql, database: database};
    	var params = {hostName:hostName, userUUID:user, agentUUID:agentUUID, module:module};
    	params = JSON.stringify(params);
    	
    	Metronic.blockUI({target: '#authAgentModal',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'registDbAgent',p:params}, function(data){
    		Metronic.unblockUI('#authAgentModal');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			$('#authAgentModal').modal('hide');
    		}
    	});
    }
    
    var checkIp = function(value){
    	var ipv4 = ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    	//http|https
    	var ipv4HTTP = /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    	
    	return ipv4 || ipv4HTTP;
    }


    var addDbAgent = function(){
    	var data = {};
    	data.ip = $('#agentIp').val();
    	data.port = $('#port').val();
    	data.agentname = $('#agentName').val();
    	data.transport_port = $('#transferport').val();
    	data.client_port = $('#clientport').val();
    	var p = JSON.stringify(data);
    	Metronic.blockUI({target: '#addAgentModal',animate: true});
    	$.post(CONF.AJAXPATH, {m: CONF.M.DBPROTECT, f: "addDBAgent", p:p}, function(d){
    		Metronic.unblockUI('#addAgentModal');
    		if(OPREL(d)){
    			$('#addAgentModal').modal('hide');
    			grid.getRefresh({});
    		}
    	});
    }
    
    
    var editDbAgent = function(){
    	var data = {};
    	data.agentuuid = agentUUID;
    	data.ip = $('#agentIp').val();
    	data.agentname = $('#agentName').val();
    	data.port = $('#port').val();
    	data.transport_port = $('#transferport').val();
    	data.client_port = $('#clientport').val();
    	var p = JSON.stringify(data);
    	Metronic.blockUI({target: '#addAgentModal',animate: true});
    	$.post(CONF.AJAXPATH, {m: CONF.M.DBPROTECT, f: "editDBAgent", p:p}, function(d){
    		Metronic.unblockUI('#addAgentModal');
    		if(OPREL(d)){
    			$('#addAgentModal').modal('hide');
    			grid.getRefresh({});
    		}
    	});
    }
    
    var handleValidation = function() {
        var form2 = $('#agentForm');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);

        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
                agentip: {
                    required: true,
                    ipv4: true,
                },
                nickname:{
                	required: true
                },
                manageport:{
                	required: true
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
                success2.hide();
//                error2.show();
//                Metronic.scrollTo(error2, -200);
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

            submitHandler: function (form) {
                success2.show();
//                error2.hide();
                
            }
            
        });
        
        //添加客户端
        $("#addagent_submit").click(function(){
        	if (form2.validate().form()) {
				addDbAgent();
            }
        });
        
      //修改客户端确认
		$('#modify_submit').on('click', function(){
			if (form2.validate().form()) {
				editDbAgent();
            }
		});
    }
    
    $.validator.addMethod("ipv4", function(value, element) {
		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		//http|https
		var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		return ipv4 || ipv4HTTP;
    }, LANG.UI_TOOLS_IP);
    
    
    var initLicenseType = function(){
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemLicenseType',p:{}}, function(d){
			var data = JSON.parse(d);
			_licenseType = data.licensetype;
		});
    }
    
    return {
        //main function to initiate the module
        init: function () {
        	initLicenseType();
        	handleValidation();
            handleRecords();
            addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
	DbAgentManager.init();
});