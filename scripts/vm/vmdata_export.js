var VmDataExport = function () {
	var grid, gridInitFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	
	//初始化事件
	var addListeners = function(){
		$('#add').on('click', function(){
			LOCATION('./content/vm/new_vmdata_export.php');
		});
	}
	
	//添加按钮操作，展开项目
	var addOpButton = function(d){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('tbody > tr').find('td:eq(8)');
		var levelDiv = $('tbody > tr').find('td:eq(6)');
		
		//有几列 添加 几次
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i][7], i);
			setLevel(levelDiv[i], data[i]);
		}
		//opButton 对应点击操作
		addOpButtonListener();
		//添加展开项目
		addDetailsInfo();
	}
	
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('input').val());
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
	
	//添加详情信息 
	var addDetails = function(nTr, data){
		if(!data){
			return;
		}
    	var sOut = '<tr class="details"><td class="details" colspan="9">';
    	sOut += '<table style="width:100%">';
		if(data[10].length == 0){
			sOut += '<div style="text-align:center">' + LANG.UI_JOB_NO_TIMEPOINT_INFO + '</div>';
		}else{
			sOut += getExportVmTable(data[10]);
		}
        sOut += '</table></td></tr>';
		$(nTr).after(sOut);
	}
	
	//得到导出虚拟机列表信息
	var getExportVmTable = function(vms){
		var thead = '<thead><tr role="row" class="heading"><th width="50%">' + LANG.UI_JOB_VM_NAME + '</th><th width="50%">' + LANG.UI_JOB_HIS_BAK_TIMEPOINT + '</th></tr></thead>';
		var tbody = '<tbody>';
		for(var i=0; i<vms.length; i++){
			if(i%2 == 0){
				tbody += '<tr role="row" class="odd">';
			}else{
				tbody += '<tr role="row" class="even">';
			}
			tbody += '<td>' + vms[i].dirpath + '</td>';
			tbody += '<td>' + vms[i].timepoint + '</td>';
			tbody += '</tr>';
		}
		tbody += '<tbody>';
		var details = thead + tbody;
		return details;
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('.start').unbind().on('click', startJob); //启动任务
		$('.stop').unbind().on('click', stopJob);   //停止任务
		$('.delete').unbind().on('click', deleteJob);//删除任务
	}
	
	//启动任务
	var startJob = function(){
		JobUnifyCtl(this, 'startExportJob');
	}
	
	//停止任务
	var stopJob = function(){
		JobUnifyCtl(this, 'stopExportJob');
	}
	
	//删除任务
	var deleteJob = function(){
		JobUnifyCtl(this, 'deleteExportJob');
	}
	
	var JobUnifyCtl = function(button, funName){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_export_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_export_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
    		}
    	});
	}
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[9]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[5] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-info";
				break;
			case 2:
			case 3:
				levelClass = "label-success";
				break;
			case 4:
				levelClass = "label-default";
				break;
			case 7:
				levelClass = "label-warning";
				break;
			case 6:
			case 8:
				levelClass = "label-danger";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	
	//添加操作按钮
	var opButton = function(div, opCode, rowNum){
		var button = '<div class="btn-group  positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group  positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu">';
		
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="start"><a href="javascript:;" ><i class="glyphicon glyphicon-play"></i> ' + LANG.UI_JOB_START + '</a></li>';
					break;
				case 2:
					button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
					break;
				case 3:
					button += '<li class="divider"></li><li class="edit"><a href="javascript:;"><i class="fa fa-pencil"></i> ' + LANG.UI_JOB_MODIFY + '</a></li>';
					break;
				case 4:
					button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
					break;
				case 5:
					button += '<li class="pause"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_PAUSE + '</a></li>';
					break;
				case 6:
					button += '<li class="startDiff"><a href="javascript:;" ><i class="fa fa-sort-amount-asc fa-rotate-270"></i> ' + LANG.UI_JOB_START_DIFFRENCE + '</a></li>';
					break;
				case 7:
					button += '<li class="startIncr"><a href="javascript:;" ><i class="fa fa-sort-amount-desc fa-rotate-270"></i> ' + LANG.UI_JOB_START_INCREMENT + '</a></li>';
					break;
				case 8:
					button += '<li class="startStra"><a href="javascript:;" ><i class="viconfont vicon-ge_time_point"></i> ' + LANG.UI_JOB_START_STRATEGY + '</a></li>';
					break;
				case 9:
					button += '<li class="motion"><a href="javascript:;" ><i class="fa fa-share"></i> ' + LANG.UI_MOTION_NAME + '</a></li>';
					break;
				case 10:
					button += '<li class="start"><a href="javascript:;" ><i class="glyphicon glyphicon-play"></i> ' + LANG.UI_JOB_START_FULL + '</a></li>';
					break;
					
			}
		});
		button += '</ul></div>';
		$(div).html(button);
		
	}
	
	//得到参数
	var getParams = function(){
		//页码
		var page = $('.pagination-panel-input').val();
		//每页条数
		var size = $('select[name=datatable_length]').val();
		//搜索项
		var p ={start:0, length:10};
		if(undefined != page){
			p.start = parseInt(page) - 1;
			p.length = 10;
		}
		return p;
	}
	
	//初始化表格
    var handleRecords = function () {
    	
    	var updateInterval = 5000;
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': true,
	                'targets': [0]
    			}],
    			"order": [
                    [6, "desc"]
                ],
    	};
    	var initGrid = function(){
    		if(0 == $('#current_export_job').size()){
        		clearTimeout(timerTask.CurrentJob_data);
        		return;
        	}
    		if(!gridInitFlag){
    			//加载显示页数分页条
    			grid = new Datatable();
        		var data = {m:CONF.M.JOB,f:'getExportJobs',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#datatable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
            	
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(expandDiv == 0 && bootboxConfirm == 0){
    				grid.getRefresh(getParams());
    			}
    		}
    		timerTask.CurrentJob_data = setTimeout(initGrid, updateInterval);
    	}
    	initGrid();
    	
    	
    	$('#datatable').on('click', ' tbody td .row-details', function () {
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
            	pageIndex = parseInt($('input').val());
            }
            return;
        });
    	
    	return;
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
	VmDataExport.init();
});