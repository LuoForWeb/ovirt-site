var OrchReport = function () {
	
	var grid;
	var tipSelect = function(){
		UIToastr.showInfo(LANG.UI_DRILLS_REPORT_DELETE, LANG.UI_DRILLS_REPORT_DELETE_TIPS);
	}
	
	var initRow = function(){
		var data = grid.getDataTable().data();
		var levelDiv = $('tbody > tr').find('td:eq(11)');
		var hrefDiv = $('tbody > tr').find('td:eq(12)');
		for(var i=0; i<levelDiv.length; i++){
			setLevel(levelDiv[i], data[i]);
			setHref(hrefDiv[i], data[i]);
		}
		$(".popovers").popover();
	}
	
	var setHref = function(div, data){
		var url = './content/platform/manoeuvre/report_details.php?uuid=' + data[11];
		var html = '<a class="ajaxify" href="' + url + '">' + LANG.UI_ALARM_DETAILS + '</a>';
		$(div).html(html);
	}
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[12].level);
		var content = '<span class="label label-sm ' + labelClass + '">' + 
			'<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="left" data-content="' + 
			data[12].popover + '" >'+ data[10] + '</a></span>';
		$(div).html(content);
	}
	//得到日志的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-warning";
				break;
			case 3:
				levelClass = "label-danger";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
	//初始化事件
	var addListeners = function(){
		$('#moduletype').on('change',function(){
			var module = $(this).val();
			var p = {start:0, length:10, search:{module:module}};
			var data = {m:CONF.M.JOB,f:'getOrchHistoryJobs',p:p};
    		grid.setAjaxParam(data);
			grid.getRefresh(p, undefined, true);
		})
	}
	
    var handleRecords = function () {
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 1, 11]
    			}],
    			"order": [
                    [9, "desc"]
                ],
    	};
    	grid = new Datatable();
		var data = {m:CONF.M.JOB,f:'getOrchHistoryJobs',p:{}};
		grid.setAjaxParam(data);
    	grid.init({src: $("#datatable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:initRow});
    	
    	$("#delete").on('click', function(){
    		deleteHistoryTask(grid);
    	});
    	
    	$('#datatable').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();
    		
            var nTr = $(this).parents('tr')[0];
            
            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            }else{
            	//如果是收起的
            	$('tr .details').parent().remove();
            	$('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            }
            return;
        });
    	
    	//添加详情信息 
    	var addDetails = function(nTr, data){
    		if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="13">';
        	sOut += '<table class="detailstable">';
            sOut += getVMDetailsInfo(data[13]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}
    	
    	//得到虚拟机详情
    	var getVMDetailsInfo = function(data){
    		if(!data.info){
    			return LANG.UI_PUBLIC_NOTHING;
    		}
    		
    		var getInstantDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
        		thead += '<th width="10%">' + LANG.UI_JOB_VM_NAME + '</th>';
        		thead += '<th width="15%">' + LANG.UI_VCENTER_VM_PATH + '</th>';
        		thead += '<th width="15%">' + LANG.UI_JOB_DST_HOST + '</th>';
        		thead += '<th width="15%">' + LANG.UI_DRILLS_REPORT_PLAN + '</th>';
        		thead += '<th width="15%">' + LANG.UI_RECOVERY_TIMEPOINT + '</th>';
        		thead += '<th width="10%">' + LANG.UI_PUBLIC_TOTAL_SIZE + '</th>';
        		thead += '<th width="10%">' + LANG.UI_PUBLIC_STATUS + '</th>';
        		thead += '<th width="20%">' + LANG.UI_PUBLIC_DESCRIPTION + '</th>';
        		thead += '</tr></thead>';
        		
        		var tbody = '<tbody>';
        		for(var i=0; i<data.info.length; i++){
        			if(i%2 == 0){
        				tbody += '<tr role="row" class="odd">';
        			}else{
        				tbody += '<tr role="row" class="even">';
        			}
        			tbody += '<td>' + data.info[i].vmname + '</td>';
        			tbody += '<td>' + data.info[i].dirpath + '</td>';
        			tbody += '<td>' + data.info[i].host + '</td>';
        			tbody += '<td>' + data.info[i].plans + '</td>';
        			tbody += '<td>' + data.info[i].timepoint + '</td>';
        			tbody += '<td>' + data.info[i].totalsize + '</td>';
        			tbody += '<td>' + data.info[i].status + '</td>';
        			tbody += '<td>' + data.info[i].des + '</td>';
        			
        			
        			tbody += '</tr>';
        		}
        		tbody += '<tbody>';
        		var details = thead + tbody;
        		return details;
    		}
    		
    		var getRecoveryDetails = function(data){
    		}
    		
    		if(1 == data.recoveryMode){
    			//恢复
    			return getRecoveryDetails(data);
    		}else if(2 == data.recoveryMode){
    			//瞬时恢复
    			return getInstantDetails(data);
    		}
    	}
    	
    	return;
    }
    
    var deleteHistoryTask = function(grid){
    	var select = grid.getSelectedRows();
		if(!select.length){
			return tipSelect();
		}
		var data = {};
		data.uuid = select;
		data = JSON.stringify(data);
		var grid = grid;
		Metronic.blockUI({target: '#historycontent',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'deleteOrchHistoryJobs',p:data}, function(data){
    		Metronic.unblockUI('#historycontent');
    		if(OPREL(data)){
    			grid.getRefresh({});
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
    OrchReport.init();
});