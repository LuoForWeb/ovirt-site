//部署软件
var DeploySoft = function () {
	
	//添加事件监控
	var addListeners = function(){
		$('#deploycancel').on('click', function(){
			LOCATION('./content/platform/node/node_manager.php','node_manager');
		});
		$('#deploysubmit').on('click', deploySubmit);
	}
	
	//提交部署
	var deploySubmit = function(){
		var data = {};
    	data.uuids = $('#uuids').val();
    	data.softname = $('select[name=softselect]').val();
    	if(!data.softname){
    		UIToastr.showInfo(LANG.UI_NODE_DEPLOY, LANG.UI_NODE_DEPLOY_TIPS3);
    		return;
    	}
    	data = JSON.stringify(data);
    	Metronic.blockUI({target: '#deploy_soft',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'startDeployNodeSoft',p:data}, function(d){
    		Metronic.unblockUI('#deploy_soft');
    		OPREL(d);
    	});
	}
	
	var initData = function(){
		initNodeInfo();	
		initSoftSelect();
	}
	
	//初始化节点信息
	var initNodeInfo = function(){
		var data = {};
    	data.uuids = $('#uuids').val();
    	data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getDeployNodeInfo',p:data}, function(d){
    		var data = JSON.parse(d);
    		var node = '';
    		for(var i=0; i<data.length; i++){
    			node += '<span class="form-control-static"> ' + data[i].name + ' </span><br>';
    		}
    		$('#nodenamediv').html(node);
    	});
	}
	
	//初始化软件下拉框
	var initSoftSelect = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getSoftSelectInfo',p:{}}, function(d){
    		var data = JSON.parse(d);
    		var softselect = $('select[name=softselect]');
    		softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].name);
				softselect.append(option);
			}
    	});
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	initData();
        	addListeners();
        },
        
        //提供给上传软件后刷新下拉列表
    	refreshSelect: function(){
    		initSoftSelect();
    	}

    };

}();

//软件包管理
var DeploySoftManager = function(){
	var grid;
	var handleRecords = function(){
		grid = new Datatable();
		var data = {m:CONF.M.NODE,f:'getSoftInfo',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#softtable")});
    	
		$('#downloadsoft').on('click', downloadSoft);
		$('#deletesoft').on('click', deleteSoft);
	}
	
	//下载软件
	var downloadSoft =function(){
		var select = grid.getSelectedRows();
		if(select.length != 1){
			UIToastr.showInfo(LANG.UI_NODE_DOWNLOAD_SOFT, LANG.UI_NODE_DOWNLOAD_SOFT_TIPS);
			return;
		}
		window.location.href = select[0];
	}
	
	//删除软件
	var deleteSoft = function(){
		var select = grid.getSelectedRows();
		if(select.length == 0){
			UIToastr.showInfo(LANG.UI_NODE_DELETE_SOFT, LANG.UI_NODE_DELETE_SOFT_TIPS);
			return;
		}
		var data = {};
		data.paths = select;
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'deleteNodeSoft',p:data}, function(d){
			if(OPREL(d)){
				grid.getRefresh({});
        		DeploySoft.refreshSelect();
    		}
    	});
	}
	
	//上传软件
	var handleUploadSoft = function(){
    	var url = CONF.AJAXPATH + '?m=' + CONF.M.NODE + '&f=uploadNodeSoft';
	    $('#uploadsoft').fileupload({
	        url: url,
	        dataType: 'json',
	        done: function (e, data){
	        	OPREL(JSON.stringify(data.result));
	        	if(data.result.re){
	        		//如果上传成功,刷新表格,刷新软件选择下拉框
	        		grid.getRefresh({});
	        		DeploySoft.refreshSelect();
	        	}
	        },
	        start: function (e){
	        	Metronic.blockUI({target: '#soft_manager',animate: true});
	        },
	        stop: function (e) {
	            Metronic.unblockUI('#soft_manager');
	        },
	        fail: function (e, data) {
	        	var data = {lev:'error', re:false, title:LANG.UI_SETTING_UPLOAD_FILE, msg:LANG.UI_SETTING_UPLOAD_FILE_FAILURE + ',' + data.errorThrown};
	        	data = JSON.stringify(data);
	        	OPREL(data);
	        },
	    }).prop('disabled', !$.support.fileInput)
	        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	}
	
	return {
        //main function to initiate the module
        init: function () {
        	handleRecords();
        	handleUploadSoft();
        }

    };
}();

jQuery(document).ready(function() {    
	DeploySoft.init();
	DeploySoftManager.init();
});