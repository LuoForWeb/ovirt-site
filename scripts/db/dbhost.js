var DbHost = function () {
	
	var grid;
	
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var moduleDiv = $('#datatable tbody > tr').find('td:eq(5)');
		var statusDiv = $('#datatable tbody > tr').find('td:eq(7)');
		for(var i=0; i<statusDiv.length; i++){
			addModule(moduleDiv[i], data[i]);
			hostStatus(statusDiv[i], data[i]);
		}
	}
	
	//添加模块信息
	var addModule = function(div, data){
		var html = "";
		if(!data[5]){
			$(div).html(html);
			return;
		} 
		if(data[5].file){
			html += '<a title="' + "文件实时同步" + '"><i class="fa fa-file"></i></a>&nbsp;&nbsp;';
		}
		if(data[5].database){
			html += '<a title="' + "数据库CDP" + '"><i class="fa fa-database"></i></a>';
		}
		$(div).html(html);
	}
	
	//主机状态
	var hostStatus = function(div, data){
		var thisClass = "label-info";
		if(1 == data[9]){
			thisClass = "label-success";
		}else if(2 == data[9]){
			thisClass = "label-default";
		}else if(3 == data[9]){
			thisClass = "label-warning";
		}
		var statusDes = '<span class="label label-sm ' + thisClass + '">' + data[7] + '</span>';
		$(div).html(statusDes);
	}
	
    var handleRecords = function () {
    	//读取cookie里的保存列表状态
    	var length = "";
    	if($.cookie('pageLength')){
    		var pageList = JSON.parse($.cookie('pageLength'));
    		if(pageList.dbhost){
        		length = pageList.dbhost;
        	}
    	}
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': []
    			}],
    			"order": [
                    [6, "desc"]
                ],
                'pageLength': parseInt(length)
    	};
    	grid = new Datatable();
		var data = {m:CONF.M.DBCDP,f:'getDbHostInfo',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#datatable"), dataTable:dataTableOpt, onDataLoad:addOpButton});
    	
    	$("#add").on('click', function(){
    		addHost();
    	});
    	
    	$("#edit").on('click', function(){
    		editHost(grid);
    	});
    	
    	$("#delete").on('click', function(){
    		deleteHost(grid);
    	});
    	
    	$("#download").on('click', function(){
    		downloadHostLog(grid);
    	});
    	return;
    }
    
    var addHost = function(){
    	$('#modaldivadd').modal();
    	$('#modaldivadd input').iCheck({
    	    checkboxClass: 'icheckbox_square-blue',
    	    radioClass: 'iradio_square-blue',
    	    increaseArea: '20%' // optional
    	});
    }
    
    //添加主机
    var addHostClick = function(){
    	var hosttype = $('#hosttype').val();
    	var hostip = $('#hostip').val();
    	var file = $('#file').is(':checked');
    	var database = $('#database').is(':checked');
//    	var hostusername = $('#hostusername').val();
//    	var hostpassword = $('#hostpassword').val();
    	if(!isValidIP(hostip)){
    		UIToastr.showWarning('请检查您的输入', '请按提示填写主机信息');
    		return false;
    	}
		var params = {hosttype:hosttype, hostip:hostip, file:file, database:database};
    	params = JSON.stringify(params);
		Metronic.blockUI({target: '#modaldivadd',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'addHost',p:params}, function(data){
    		Metronic.unblockUI('#modaldivadd');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			$('#modaldivadd').modal('hide');
    		}
    	});
    } 
    
    //ip地址验证
    var isValidIP = function(ip){
    	var reg = /^(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[1-9])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/;
    	return reg.test(ip);
    }
    
    //修改主机
    var editHostClick = function(){
    	var hostuuid = $('#ehostuuid').val();
    	var hosttype = $('#ehosttype').val();
    	var hostip = $('#ehostip').val();
    	var file = $('#efile').is(':checked');
    	var database = $('#edatabase').is(':checked');
    	var hostname = $('#ehostname').val();
    	if(!isValidIP(hostip) || $.trim(hostname) == ''){
    		UIToastr.showWarning('请检查您的输入', '请按提示填写主机信息');
    		return false;
    	}
    	
    	var params = {hostuuid:hostuuid, hosttype:hosttype, hostip:hostip, 
    			hostname:hostname, file:file, database:database
    	};
    	
    	params = JSON.stringify(params);
		Metronic.blockUI({target: '#modaldivedit',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'editHost',p:params}, function(data){
    		Metronic.unblockUI('#modaldivedit');
    		if(OPREL(data)){
    			grid.getRefresh({});
    			$('#modaldivedit').modal('hide');
    		}
    	});
    }
    
    //修改主机
    var editHost = function(grid){
    	var select = grid.getSelectedRows();
		if(1 != select.length){
			return UIToastr.showInfo('修改主机', '请选择一个您需要修改的主机');
		}
		var params = {hostuuid:select[0]};
    	params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getEditHostInfo',p:params}, function(data){
			var d = JSON.parse(data);
			$('#ehostuuid').val(d.hostuuid);
			$('#ehosttype').val(d.hosttype);
			$('#ehostip').val(d.ip);
			$('#ehostname').val(d.hostname);
			$('#modaldivedit').modal();
			$('#modaldivedit .icheck').iCheck({
	    	    checkboxClass: 'icheckbox_square-blue',
	    	    radioClass: 'iradio_square-blue',
	    	    increaseArea: '20%' // optional
	    	});
			
			//授权初始化
			if(d.license.file){
				$('#efile').iCheck('check');
			}
			if(d.license.database){
				$('#edatabase').iCheck('check');
			}
    	});
    }
    
    //删除主机
    var deleteHost = function(grid){
    	var select = grid.getSelectedRows();
		if(!select.length){
			return tipDelete();
		}
		if(select.length > 1){
			return UIToastr.showInfo('删除主机', '您选择的主机过多,请选择其中一个进行删除');
		}
		bootbox.confirm({
            title: '删除主机',
            message: '您确定要删除主机吗?',
            callback: function(r) {
                if(!r) return;
                submitDelete(select, grid);
            }
        });
    }
    
    //下载日志
    var downloadHostLog = function(grid){
    	var select = grid.getSelectedRows();
		if(1 != select.length){
			return UIToastr.showInfo('下载日志', '请选择一个您需要下载日志的主机');
		}
		
		var params = {hostuuid:select[0]};
    	params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'checkHostLog',p:params}, function(d){
			var data = JSON.parse(d);
			//失败提示,成功直接下载,不提示
			if(!data.re){
				OPREL(d);
			}else{
				window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.DBCDP + '&f=getHostLogFile&p=' + params;
			}
		});
    }
    
    //提交删除消息
    var submitDelete = function(select, grid){
    	var data = {};
		data.host = select;
		data = JSON.stringify(data);
		var grid = grid;
		Metronic.blockUI({target: '#hostcontent',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'deleteHost',p:data}, function(data){
    		Metronic.unblockUI('#hostcontent');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
    }
    
    
    var addListeners = function(){
    	//切换页数保存到cookie
		$('select[name=datatable_length]').on('change', function(){
			pageLength.dbhost = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
    	
		$('#addsubmit').on('click', addHostClick);
		$('#editsubmit').on('click', editHostClick);
		
		$('#searchbtn').on('click',function(){
			var name = $.trim($('.searchinput').val());
			var p = {start:0, length:10, search:{name:name}};
			var data = {m:CONF.M.DBCDP,f:'getDbHostInfo',p:p};
    		grid.setAjaxParam(data);
			grid.getRefresh(p, undefined, true);
			
		});
		
		$('.searchinput').keypress(function (e) {
            if (e.which == 13) {
            	var name = $.trim($('.searchinput').val());
    			var p = {start:0, length:10, search:{name:name}};
    			grid.getRefresh(p, undefined, true);
            }
        });
		
    }
    
    return {
        //main function to initiate the module
        init: function () {
            handleRecords();
            addListeners();
        }

    };

}();


jQuery(document).ready(function() {    
	DbHost.init();
});