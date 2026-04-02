var ApplianceManager = function(){
    var grid, gridInitFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;

    var initListeners = function(){
		//切换页数保存到cookie
		$('select[name=appliance_datatable_length]').on('change', function(){
			pageLength.appliance = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});

        $('#appliance_datatable').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();
            var nTr = $(this).parents('tr')[0];
            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfo = null;
            }else{
            	//如果是收起的
            	$('tr .details').parent().remove();
            	$('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('tbody tr .details').parents('tr')[0];
            	pageIndex = parseInt($('.pagination-panel-input').val());
            	
            }
            return;
		});
		
    }

    var initRow = function(){
		var data = grid.getDataTable().data();
		if(!data || 0 == data.length) return;
		var levelDiv = $('#appliance_datatable tbody > tr').find('td:eq(6)');
		for(var i=0; i<levelDiv.length; i++){
			setLevel(levelDiv[i], data[i]);
		}
		addDetailsInfo();
		
		initGridRowSelectListener();	//初始化成单选
	}
	
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('.pagination-panel-input').val());
		if(currentPage != pageIndex){
			return true;
		}
		if(detailsInfo){
			var tr = $('tbody tr');
			var data = grid.getDataTable().data();
        	addDetails(tr[detailsIndex], data[detailsIndex]);
			var openTr = $('tbody > tr')[detailsIndex];
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
	}
	
	
	//在线/离线(appliance状态)
	var setLevel = function(div, data){
		if(!data[8]) return;
		var labelClass = getApplianceStatusClass(data[8]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[5] + '</span>';
		$(div).html(content);
	}
	
	//得到存储状态类型
	var getApplianceStatusClass = function(status){
		var levelClass = '';
		switch(status){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			default:
				levelClass = "label-warning";
				break;
		}
		return levelClass;
	}
	

	//添加详情信息 
	var addDetails = function(nTr, data){
		if(!data){
			return;
		}
    	var sOut = '<tr class="details"><td class="details" colspan="12">';
    	sOut += '<table>';
        sOut += getApplianceDetails(data[9]);
//        sOut += '<tr id="detail'+data[8]['config'].storageuuid+'"><td></td><tr>';
        sOut += '</table></td></tr>';
		$(nTr).after(sOut);
    }
    
    var getApplianceDetails = function(portList){
		var details = '';
		var progressDetail = portList['progress_server_listen_port'];
		var progressRangeDetail =  portList['progress_server_start_port'] + " - " + portList['progress_server_end_port'];
		// var cdpDetail = portList['cdp_client_listen_port'] + ", CDP客户端日志监听端口: " + portList['cdp_client_log_listen_port'] + ", CDP服务端日志监听端口: " + portList['log_server_listen_port']; 
		details += getEachRowHtml(LANG.UI_APPLIANCE_PROGRESS_SERVER_PORT, progressDetail);
		details += getEachRowHtml(LANG.UI_APPLIANCE_PROGRESS_SERVER_PORT_RANGE, progressRangeDetail);
        // details += getEachRowHtml('CDP客户端监听端口', cdpDetail);

        return details;
    }

    //得到每行的HTML
	var getEachRowHtml = function(key, value){
		var html = "<tr><td>" + key + ":</td><td colspan='10'>";
		html += value;
		html += "</td></tr>";
		return html;
	}
	

    var handleRecords = function () {
		//读取cookie里的保存列表状态
    	var length = "";
    	if($.cookie('pageLength')){
    		var pageList = JSON.parse($.cookie('pageLength'));
    		if(pageList.appliance){
        		length = pageList.appliance;
        	}
    	}
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 1]
    			}],
    			"order": [
                    [5, "desc"]
                ],
                'pageLength': parseInt(length)
    	};
    	grid = new Datatable();
		var data = {m:CONF.M.NODE,f:'getAppliances',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#appliance_datatable"), showDetail:true, checkbox: false, onDataLoad:initRow, dataTable:dataTableOpt});
    	$("#editAppliance").on('click', function(){
    		editAppliance(grid);
    	});
    	$("#addAppliance").on('click', function(){
    		addAppliance();
    	});
    	$("#deleteAppliance").on('click', function(){
    		deleteAppliance(grid);
    	});
    	return;
    }
    
    //初始化成单选
    var initGridRowSelectListener = function(){
		//这里说明一下,因为表格是动态生成的需要用到LIVE方法,但是需要注意的是表格的ID号不要和所有的ID号冲突
		$('#appliance_datatable').find('tbody > tr').off().on('click', function(){
			//1.取消所有选中项
			$(this).parent().find('input').prop("checked", false);
			//2.选中本行
			$(this).find('input').prop("checked", true);
		});
		
		$('#appliance_datatable').find('tbody > tr').find('input[type="checkbox"]').off().on('click', function(){
			//取消所有其他选中的
			var checkBoxs = $('#appliance_datatable').find('tbody > tr').find('input[type="checkbox"]');
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
		});
	}
    
    var addAppliance = function(){
    	LOCATION('./content/appliance/add_appliance.php');
    }
    
    var editAppliance = function(grid){
    	var select = grid.getSelectedRows();
		if(!select.length){
			return tipEdit();
		}
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_APPLIANCE_EDIT_SELECT, LANG.UI_APPLIANCE_EDIT_SELECT_MORE_TIPS);
		}
		var url = './content/appliance/edit_appliance.php?uuid=' + select[0];
    	LOCATION(url);
    }

	
	var tipEdit = function(){
		UIToastr.showInfo(LANG.UI_APPLIANCE_EDIT_SELECT, LANG.UI_APPLIANCE_EDIT_SELECT_TIPS);
	}
	
	var tipDelete = function(){
		UIToastr.showInfo(LANG.UI_APPLIANCE_DELETE_SELECT, LANG.UI_APPLIANCE_DELETE_SELECT_TIPS);
	}
    
    
    var deleteAppliance = function(grid){
    	var select = grid.getSelectedRows();
		if(!select.length){
			return tipDelete();
		}
		
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_APPLIANCE_DELETE_SELECT, LANG.UI_APPLIANCE_DELETE_SELECT_MORE_TIPS);
		}
		bootbox.confirm({
            title: LANG.UI_APPLIANCE_DELETE_SELECT,
            message: LANG.UI_APPLIANCE_DELETE_TIPS,
            callback: function(r) {
                if(!r) return;
                submitDelete(select);
            }
        });
    }
    
    var submitDelete = function(select){
        var data = {};
		data.list = select;
		data = JSON.stringify(data);
		
		Metronic.blockUI({target: '#applianceContent',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'deleteAppliance',p:data}, function(d){
			Metronic.unblockUI('#applianceContent');
    		if(OPREL(d)){
    			grid.getRefresh({});
    		}
    	});
    }

    return {
        init: function(){
			handleRecords();
			initListeners();
        }
    }
}();

jQuery(document).ready(function(){
    ApplianceManager.init();
});