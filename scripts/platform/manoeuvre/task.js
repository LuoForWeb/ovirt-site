var OrchTask = function () {
	
	var grid, gridInitFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	
	var addOpButton = function(d){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('tbody > tr').find('td:eq(8)');
		var nameDiv = $('tbody > tr').find('td:eq(1)');
		var levelDiv = $('tbody > tr').find('td:eq(5)');
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i][7], i);
			nameHref(nameDiv[i], data[i]);
			setLevel(levelDiv[i], data[i]);
		}
		//opButton
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
	
	var nameHref = function(div, data){
		var url = getDetailsUrl(data[8].module, data[8].taskType);
		var nameStr = '<a href="' + url + '?type=' + data[8].taskType + '&uuid=' + data[8].uuid + 
					  '&mode=' + data[8].recoveryMode + '" class="ajaxify" name="task">' + data[0] + '</a>';
		$(div).html(nameStr);
	}
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[8].status);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[4] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
			case 2:
			case 3:
				levelClass = "label-success";
				break;
			case 4:
				levelClass = "label-info";
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
	
	//根据模块类型得到任务详情的页面地址
	var getDetailsUrl = function(module, taskType){
		var url = '';
		if(2 == module){
			//虚拟机
			url = './content/platform/manoeuvre/task_details.php';
		}
		return url;
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
	
	//暂停
	var pauseJob = function(){
		opJob(this, 'pauseJob');
	}
	//停止
	var stopJob = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		var button = this;
		if(2 == params.recoveryMode){
			//如果是瞬时恢复任务,停止的时候需要提示
			bootbox.confirm({
	            title: LANG.UI_JOB_STOP_JOB_TITLE,
	            message: LANG.UI_JOB_STOP_JOB_TIPS1 + '<br>' + 
	            		 LANG.UI_JOB_STOP_JOB_TIPS2 + '<br>' + 
	            		 LANG.UI_JOB_STOP_JOB_TIPS3 + '<br>' + 
	            		 LANG.UI_JOB_STOP_JOB_TIPS4 + '<br>' + 
	            		 LANG.UI_JOB_STOP_JOB_TIPS5,
	            		 
	            callback: function(r) {
	                if(!r) return;
	                opJob(button, 'stopJob');
	            }
	        });
		}else{
			opJob(button, 'stopJob');
		}
		
	}
	//删除
	var deleteJob = function(){
		var button = this;
		bootbox.confirm({
            title: LANG.UI_JOB_DELETE_JOB,
            message: LANG.UI_JOB_DELETE_JOB_TIPS,
            callback: function(r) {
                if(!r) return;
                opJob(button, 'deleteJob');
            }
        });
	}
	//修改
	var editJob = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var uuid = params.uuid;
		var recoveryMode = params.recoveryMode; //恢复模式
		switch(module){
			case 1:
				//修改恢复任务
				var url = './content/platform/manoeuvre/recover_edit.php?uuid=' + uuid;
				break;
			case 2:
				//修改瞬时恢复任务
				var url = './content/platform/manoeuvre/instant_recover_edit.php?uuid=' + uuid;
				break;
			default:
				return;
		}
		LOCATION(url);
	}
	
	var startStra = function(){
		opJob(this, 'startStra');
	}
	
	var opJob = function(button, funName){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_job');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
	}
	//启动完全
	var startJob = function(){
		startJobUnify(this, 'startJob', 1);
	}
	
	//启动差异
	var startDiff = function(){
		startJobUnify(this, 'startJob', 3);
	}
	//启动增量
	var startIncr = function(){
		startJobUnify(this, 'startJob', 2);
	}
	//迁移
	var motion = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		var url = './content/vm/vmmotion.php?module=' + params.module + "&submodule=" + params.subModule + 
				  '&tasktype=' + params.taskType + '&uuid=' + params.uuid; 
		LOCATION(url);
	}
	
	var startJobUnify = function(button, funName, type){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][8];
		params.startType = type;
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_job');
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('.start').unbind().on('click', startJob);
		$('.pause').unbind().on('click', pauseJob);
		$('.stop').unbind().on('click', stopJob);
		$('.edit').unbind().on('click', editJob);
		$('.delete').unbind().on('click', deleteJob);
		$('.startDiff').unbind().on('click', startDiff);
		$('.startIncr').unbind().on('click', startIncr);
		$('.startStra').unbind().on('click', startStra);
		$('.motion').unbind().on('click', motion);
	}
	
	//初始化事件
	var addListeners = function(){
		$('#moduletype').on('change',function(){
			var module = $(this).val();
			var p = {start:0, length:10, search:{module:module}};
			var data = {m:CONF.M.JOB,f:'getCurrentOrchJobs',p:p};
    		grid.setAjaxParam(data);
			grid.getRefresh(p, undefined, true);
		});
		
		$('#addtask').on('click', addTask);
		
	}
	
	//得到参数
	var getParams = function(){
		//页码
		var page = $('.pagination-panel-input').val();
		//每页条数
		var size = $('select[name=datatable_length]').val();
		//搜索项
		var search = $('#moduletype').val();
		var p ={start:0, length:10, search:{module:0}};
		if(undefined != page){
			p.start = parseInt(page) - 1;
			p.length = 10;
			p.search = {module: search};
		}
		return p;
	}
	
	//添加详情信息 
	var addDetails = function(nTr, data){
		if(!data){
			return;
		}
    	var sOut = '<tr class="details"><td class="details" colspan="9">';
    	sOut += '<table>';
        sOut += getTimeStrategy(data[9]);
        sOut += '</table></td></tr>';
		$(nTr).after(sOut);
	}
	
	//得到时间策略描述信息
	var getTimeStrategy = function(msg){
		var timeStr = '';
		if(!msg.timeStrategy.length){
//			timeStr = LANG.UI_DRILLS_TASK_TIME_STRATEGY;
			timeStr = LANG.UI_PUBLIC_NOTHING;
			return timeStr;
		}
		timeStr += "<tr><td>" + LANG.UI_STRATEGY_TIME + ":</td><td>";
		for(var i=0; i<msg.timeStrategy.length; i++){
//			timeStr += getEachStrategy(msg.timeStrategy[i]);
			var strategy = msg.timeStrategy[i];
//			var typeDes = getModeDes(strategy.mode);
			var des = "";
			if(CONF.STRATEGY_TYPE.DAY == strategy.type){
				des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy); 
			}else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
				}else{
					des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
				}
			}else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
				des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
			}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
				des += LANG.UI_STRATEGY_GLOBAL;
			}else{
				des += LANG.UI_PUBLIC_NOTHING + "<br>";
			}
			timeStr += des;
			
		}
		
		timeStr += "</td></tr>";
		return timeStr;
	}
	
	var getModeDes = function(mode){
		var des = '';
		if(1 == mode){
			des = LANG.UI_STRATEGY_FULL;
		}else if(2 == mode){
			des = LANG.UI_STRATEGY_INCREMENT;
		}else if(3 == mode){
			des = LANG.UI_STRATEGY_DIFFRENCE;
		}
		return des;
	}
	
	var getEachStrategy = function(strategy){
		var desEach = '';
		desEach += strategy.startTime;
		desEach += LANG.UI_STRATEGY_START + ", ";
		if(strategy.rollFlag){
			desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
		}else{
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br>";
		return desEach;
	}
	var getStrategyDays = function(days){
		var desDays = '';
		$.each(days, function(i,d){
			if(1 == d){
				var day = i+1;
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					desDays += day + ", ";
				}else{
					desDays += "Day" + day + ", ";
				}
				
			}
		});
		return desDays;
	}
	//获取每周显示日期
	var getStrategyWeek = function(days){
		var desDays = '';
		$.each(days, function(i,d){
			if(1 == d){
				desDays += CONF.WEEK[i] + ", ";
			}
		});
		return desDays;
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
                    [3, "desc"]
                ],
    	};
    	var initGrid = function(){
    		if(0 == $('#current_job').size()){
        		clearTimeout(timerTask.CurrentJob_data);
        		return;
        	}
    		if(!gridInitFlag){
    			grid = new Datatable();
        		var data = {m:CONF.M.JOB,f:'getCurrentOrchJobs',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#datatable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			if(expandDiv == 0){
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
    
    
    
    //新建
	var addTask = function(){
		LOCATION('./content/platform/manoeuvre/instant_recover.php');
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
	OrchTask.init();
});