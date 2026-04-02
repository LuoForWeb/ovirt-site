var AgentGroup = function () {
	
	var grid;
	
	var handleRecords = function () {
		//读取cookie里的保存列表状态
		var length = "";
		if($.cookie('pageLength')){
			var pageList = JSON.parse($.cookie('pageLength'));
			if(pageList.agentGroup){
	    		length = pageList.agentGroup;
	    	}
		}
    	var dataTableOpt = {
			'columnDefs' : [{
                'orderable': false,
                'targets': [0]
			}],
			"order": [
                [4, "desc"]
            ],
    		'pageLength': parseInt(length)
    	};
    	grid = new Datatable();
    	var searchValue = $('#searchName').val();
		var data = {m:CONF.M.AGENT,f:'getAgentGroupInfo',p:{search:{name: searchValue}}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#agentGroupTable"), dataTable:dataTableOpt});
    	
    	$('#addGroup').on('click', addGroup);
    	$('#editGroup').on('click', editGroup);
    	$('#deleteGroup').on('click', deleteGroup);
    	
    	return;
    }
	
	//添加代理分组
	var addGroup = function(){
		var url = './content/platform/agent/add_agent_group.php';
		LOCATION(url,'client');
	}
	
	//修改代理分组
	var editGroup = function(){
		var select = grid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_AGENT_GROUP_EDIT, LANG.UI_AGENT_GROUP_EDIT_NO_SELECT_TIPS);
		}
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_AGENT_GROUP_EDIT, LANG.UI_AGENT_GROUP_EDIT_SELECT_TIPS);
		}
		var url = "./content/platform/agent/edit_agent_group.php?groupuuid="+select[0];
		LOCATION(url,'client');
		
	}
	
	//删除代理分组
	var deleteGroup = function(){
		var select = grid.getSelectedRows();
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_AGENT_GROUP_DELETE, LANG.UI_AGENT_GROUP_DELETE_NO_SELECT_TIPS);
		}
		
		bootbox.confirm({
			title: LANG.UI_AGENT_GROUP_DELETE,
			message: LANG.UI_AGENT_GROUP_DELETE_CONFIRM_TIPS,
			callback: function(r){
				if(!r) return;
				var data = {};
                data.uuids = select;
                data = JSON.stringify(data);
        		Metronic.blockUI({target: '#agentGroupList',animate: true});
        		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'deleteAgentGroup',p:data}, function(d){
        			Metronic.unblockUI('#agentGroupList');
        			if(OPREL(d)){
            			grid.getRefresh({});
            		}
            	});
			}
 		})
		
	}
	
	var searchAgentGroup = function(){
		var searchValue = $.trim($('#searchName').val());
		var data = {m:CONF.M.AGENT,f:'getAgentGroupInfo',p:{search:{name:searchValue}}};
		grid.setAjaxParam(data);
		grid.getRefresh({});
	}
	
    //添加事件
    var addListeners = function(){
    	//切换页数保存到cookie
		$('select[name=datatable_length]').on('change', function(){
			pageLength.agentGroup = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		
		$('#searchNameBtn').on('click', searchAgentGroup);
		$('#searchName').keypress(function (e) {
            if (e.which == 13) {
            	searchAgentGroup();
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
	AgentGroup.init();
});