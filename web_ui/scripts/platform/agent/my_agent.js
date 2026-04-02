var MyAgent = function () {
	
	var grid;
	var handleRecords = function () {
    	grid = new Datatable();
		var updateInterval = 5000;
		
		var initGrid = function () { 
			var data = {m:CONF.M.AGENT,f:'getMyAgentInfo',p:{}};
			grid.setAjaxParam(data);
			grid.init({src: $("#agentdatatable"), onDataLoad:addTableInfo});
			$('#delete').on('click', deleteAgent);
			grid.getRefresh();
			timerTask.my_agent_data = setTimeout(initGrid, updateInterval);
    		// return;
			clearTimeout(timerTask.my_agent_data);
		}
		initGrid();
	}
	
	var deleteAgent = function(){
		var select = grid.getSelectedRows();
		if(select == 0){
			UIToastr.showInfo(LANG.UI_AGENT_DELETE_OFFLINE, LANG.UI_AGENT_DELETE_OFFLINE_NO_SELECT);
			return;
		}
		bootbox.confirm({
            title: LANG.UI_AGENT_DELETE_OFFLINE,
            message: LANG.UI_AGENT_DELETE_OFFLINE_TIPS,
            callback: function(r) {
                if(!r) return;
            	var data = {};
        		data.uuids = select;
        		data = JSON.stringify(data);
        		Metronic.blockUI({target: '#datatable',animate: true});
        		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'deleteOfflineAgent',p:data}, function(d){
        			Metronic.unblockUI('#datatable');
        			if(OPREL(d)){
            			grid.getRefresh({});
            		}
            	});
            }
        });
	}
	
	//添加表格信息
	var addTableInfo = function(){
		var data = grid.getDataTable().data();
		var moduleDiv = $('#myagentlist tbody > tr').find('td:eq(5)');
		var statusDiv = $('#myagentlist tbody > tr').find('td:eq(6)');
		for(var i=0; i<moduleDiv.length; i++){
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
		var labelClass = getStatusClass(data[7].onlineFlag);
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
				levelClass = "label-default";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}

    return {
        //main function to initiate the module
        init: function () {
            handleRecords();
        }

    };

}();

jQuery(document).ready(function() {    
    MyAgent.init();
});