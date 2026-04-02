var CurrentOsJob = function () {
	var grid, gridInitFlag, searchFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	var searchParams;
	var initVm = false;
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	//添加按钮操作，展开项目
	var addOpButton = function(d){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('#osJobTable tbody > tr').find('td:eq(10)');
		var nameDiv = $('#osJobTable tbody > tr').find('td:eq(1)');
		var levelDiv = $('#osJobTable tbody > tr').find('td:eq(6)');
		var agentInfo = $('#osJobTable tbody > tr').find('td:eq(3)');
		
		//有几列 添加 几次
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i][9], i, data[i][12], data[i][10], data[i][11]);
			nameHref(nameDiv[i], data[i]);
			setLevel(levelDiv[i], data[i]);
			agentInfoStr(agentInfo[i], data[i][2]);
		}
		//opButton 对应点击操作
		addOpButtonListener();
		for(var i=0; i<opDiv.length; i++){
			var osuuid = data[i][10].uuid;
			var moduletype = data[i][10].module;
			var taskType = data[i][10].taskType;
			var strategy = data[i][13];
			var status = parseInt(data[i][12]);
			switch(status){
			case 1://等待
				addForbidButton(osuuid, "pause");
				addForbidButton(osuuid, "delete");
				addForbidButton(osuuid, "edit");
				if(taskType == 35){
					addForbidButton(osuuid, "startStra");
				}
				break;
			case 2://运行
			case 12://开始
				addForbidButton(osuuid, "delete");
				addForbidButton(osuuid, "edit");
				addForbidButton(osuuid, "start");
				if(taskType == 35){
					addForbidButton(osuuid, "startStra");
				}
				if(taskType == 35){
					addForbidButton(osuuid, "startDiff");
					addForbidButton(osuuid, "startIncr");
					
				}
				break;
			case 3://暂停
				addForbidButton(osuuid, "pause");
				addForbidButton(osuuid, "delete");
				break;
			case 4:
				addForbidButton(osuuid, "pause");
				addForbidButton(osuuid, "stop");
				break;
			case 5://停止中
				addForbidButton(osuuid, "pause");
				addForbidButton(osuuid, "delete");
				addForbidButton(osuuid, "start");
				if(taskType == 35){
					addForbidButton(osuuid, "startDiff");
					addForbidButton(osuuid, "startIncr");
					addForbidButton(osuuid, "startStra");
				}
				if(taskType == 35 ){
					addForbidButton(osuuid, "edit");
				}
				//停止中状态，变为强制停止
				$('#osJobTable #' +osuuid + ' .stop').html('<a href="javascript:;"><i class="glyphicon glyphicon-stop"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
				break;
			case 6:
				addForbidButton(osuuid, "pause");
				addForbidButton(osuuid, "delete");
				break;
			case 7://异常
				addForbidButton(osuuid, "pause");
				addForbidButton(osuuid, "delete");
				addForbidButton(osuuid, "start");
				if(taskType == 35 ){
					addForbidButton(osuuid, "startDiff");
					addForbidButton(osuuid, "startIncr");
					addForbidButton(osuuid, "startStra");
				}
				if(taskType == 35 ){
					addForbidButton(osuuid, "edit");
				}
				break;
			case 8://错误
				addForbidButton(osuuid, "pause");
				addForbidButton(osuuid, "delete");
				if(taskType == 35){
					addForbidButton(osuuid, "edit");
				}

				if(taskType == 35){

					addForbidButton(osuuid, "startStra");
				}
				break;
			case 9:
				break;
			case 10:
				break;
			case 11:
				addForbidButton(osuuid, "delete");
				if(taskType == 35){
					addForbidButton(osuuid, "edit");
					addForbidButton(osuuid, "startStra");
				}
				break;
			case 13:
				addForbidButton(osuuid, "stop");
				break;
			case 14:
				//接管
				addForbidButton(osuuid, "start");
				addForbidButton(osuuid, "stop");
				addForbidButton(osuuid, "edit");
				addForbidButton(osuuid, "delete");
//				addForbidButton(osuuid, "takeover");
				break;
		}
		if(taskType == 35){
			if(strategy.type == 4){
				addForbidButton(osuuid, "startDiff");
				addForbidButton(osuuid, "startIncr");
				if(strategy.timeout){
					addForbidButton(osuuid, "startStra");
				}
				
//				addForbidButton(osuuid, "start");
			}else if($.inArray(2, strategy.modeList) != -1 || $.inArray(9, strategy.modeList) != -1){
				addForbidButton(osuuid, "startDiff");
			}else if($.inArray(3, strategy.modeList) != -1){
				addForbidButton(osuuid, "startIncr");
			}
		}
			
		}
		
		//添加展开项目
		addDetailsInfo();
	}
	
	//添加禁止点击的按钮样式
	var addForbidButton = function(osuuid, option){
		$('#osJobTable #' + osuuid + ' .' +  option).unbind();
		$('#osJobTable #' + osuuid + ' .' +  option + ' a').css("opacity",".4");
		$('#osJobTable #' + osuuid + ' .' +  option + ' a').css("cursor","default");
		$('#osJobTable #' + osuuid).on("click", "." +  option + " a", function(e) {
	        e.stopPropagation();
	    });
	}
	
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('#current_os_job .pagination-panel-input').val());
		if(currentPage != pageIndex){
			return true;
		}
		if(detailsInfo){
			var tr = $('#osJobTable tbody tr');
			var data = grid.getDataTable().data();
        	addDetails(tr[detailsIndex], data[detailsIndex]);
			var openTr = $('#osJobTable tbody > tr')[detailsIndex];
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
	}
	
	var agentInfoStr = function(div,data){
		if(data == "" || data == null || data.length == 0){
			$(div).html("");
		}
		var agent_str = "";
		var agent_title = "";
		//获取title;
		for(let i = 0; i<data.length;i++){
			agent_title += data[i].agent_name+"("+data[i].agent_ip+")&#10;"
			if(i>29){
				if(data.length>30){
					agent_title += "..."
				}
				break;
			}
		}
		//获取内容
		agent_str += "<p title = '"+agent_title+"'>";
		for(let i = 0; i<data.length;i++){
			agent_str += data[i].agent_name+"("+data[i].agent_ip+")<br>"
			if(i>1){
				if(data.length>3){
					agent_str += "..."
				}
				break;
			}
		}
		agent_str +="</p>";
		
		
		
		$(div).html(agent_str);
	}
	
	
	
	
	var nameHref = function(div, data){
		var url = getDetailsUrl(data[10].module, data[10].taskType);
		if(data[1] == LANG.UI_INSTANT_NAME){
			var nameStr = '<a href="' + url + '?type=' + data[10].taskType + '&uuid=' + data[10].uuid + 
			  '" class="ajaxify" name="task">' + data[0] + '</a>';
		}else{
			var nameStr = '<a href="' + url + '?type=' + data[10].taskType + '&uuid=' + data[10].uuid + 
			  '" class="ajaxify" name="task">' + data[0] + '</a>';
		}
		$(div).html(nameStr);
	}
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[10].status);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[5] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
			case 5:
			case 10:
				levelClass = "label-info";
				break;
			case 2:
			case 14:
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
	
	//根据模块类型得到任务详情的页面地址
	var getDetailsUrl = function(module, taskType){
		var url = '';
		//虚拟机
		url = './content/os/os_job_details.php';
		return url;
	}
	
	//添加操作按钮
	var opButton = function(div, opCode, rowNum, status, data, otherInfo){
		//全局观察者只读不能操作
		if($.inArray('global_write', CONF.PERMISSION) == -1 && $.inArray('global_observer', CONF.PERMISSION) != -1){
			$(div).html('---');
			return;
		}
		var osuuid = data.uuid;
		var button = '<div class="btn-group  positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group  positionabs dropup">';
		}
		
		button += '<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu" id="'+ osuuid +'">';
		
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
					break;
				case 2:
					button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
					break;
				case 3:
					if($.inArray('global_observer', CONF.PERMISSION) != -1) break;
					button += '<li class="divider"></li><li class="edit"><a href="javascript:;"><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_JOB_MODIFY + '</a></li>';
					break;
				case 4:
					button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
					break;
				case 5:
					button += '<li class="pause"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_PAUSE + '</a></li>';
					break;
				case 6:
					button += '<li class="startDiff"><a href="javascript:;" ><i class="viconfont vicon-ge_differentia_backup"></i> ' + LANG.UI_JOB_START_DIFFRENCE + '</a></li>';
					break;
				case 7:
					button += '<li class="startIncr"><a href="javascript:;" ><i class="viconfont vicon-ge_increment"></i> ' + LANG.UI_JOB_START_INCREMENT + '</a></li>';
					break;
				case 8:
					button += '<li class="startStra"><a href="javascript:;" ><i class="viconfont vicon-ge_time_point"></i> ' + LANG.UI_JOB_START_STRATEGY + '</a></li>';
					break;
				case 9:
					if(4 == CONF.SOFTWARE) break;
					button += '<li class="motion"><a href="javascript:;" ><i class="fa fa-share"></i> ' + LANG.UI_MOTION_NAME + '</a></li>';
					break;
				case 10:
					button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START_FULL + '</a></li>';
					break;
				case 11:
					button += '<li class="takeover"><a href="javascript:;" ><i class="viconfont vicon-vol_cdp_takeover"></i> ' + LANG.UI_JOB_START_TAKEOVER + '</a></li>';
					break;
				case 12:
					button += '<li class="stoptakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
					break;
				case 13:
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
		var params = data[row][10];
		var button = this;
		if(7 == params.taskType){
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
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][10];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var message = LANG.UI_JOB_DELETE_JOB_TIPS;
		var button = this;
		var deleteFlag = false;
		bootbox.confirm({
            title: LANG.UI_JOB_DELETE_JOB,
            message: message,
            callback: function(r) {
                if(!r) return;
                if(!deleteFlag){
                	opJob(button, 'deleteJob');
                	deleteFlag = true;
                	detailsInfo = null;	//清空展开任务详情信息
                }
            }
        });
	}
	//修改
	var editJob = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][10];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var uuid = params.uuid;
		url = './content/os/osbackupedit.php?uuid=' + uuid;
		LOCATION(url);
	}
	
	
	//启动策略
	var startStra = function(){
		opJob(this, 'startStra');
	}
	
	var opJob = function(button, funName){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][10];
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_os_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_os_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
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
	//启动日志
	var startLog = function(){
		startJobUnify(this, 'startJob', 4);
	}
	
	
	
	//启动任务	
	var startJobUnify = function(button, funName, type){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][10];
		params.startType = type;
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_os_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_os_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
    		}
    	});
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('#current_os_job .start').unbind().on('click', startJob); //启动完备任务
		$('#current_os_job .pause').unbind().on('click', pauseJob); //暂停任务
		$('#current_os_job .stop').unbind().on('click', stopJob);   //终止任务
		$('#current_os_job .edit').unbind().on('click', editJob);   //编辑任务
		$('#current_os_job .delete').unbind().on('click', deleteJob);//删除任务
		$('#current_os_job .startDiff').unbind().on('click', startDiff);//差异
		$('#current_os_job .startIncr').unbind().on('click', startIncr);//增量
		$('#current_os_job .startLog').unbind().on('click', startLog);//日志
		$('#current_os_job .startStra').unbind().on('click', startStra);//启动策略
	}
	
	var searchCurrentJob = function(){
		//清除高级筛选显示内容
		$('#os_searchDiv .searchContent').text('');
		$('#os_searchDiv').hide();
		var name = $.trim($('.osjob_search').val());
		var p = {start:0, length:10, search:{name:name}};
		var data = {m:CONF.M.JOB,f:'getCurrentOsJobs',p:p};
		grid.setAjaxParam(data);
		grid.getRefresh(p, undefined, true);
		searchFlag = true;
	}
	
	//初始化事件
	var addListeners = function(){
		//初始化列表接口
		// $('#osLi').on('click', function(){
			if(!initVm){
				initNodeSelect(); //初始化所有备份节点
	            handleRecords();//初始化表格
				initVm = true;
			}
		// });
		
		//切换页数保存到cookie
		$('select[name=currentTable_length]').on('change', function(){
			pageLength.current = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		
		$('#os_searchbtn').on('click', searchCurrentJob);
		
		$('.os_searchbtn').keypress(function (e) {
            if (e.which == 13) {
            	searchCurrentJob();
            }
        });
		
		

		
		//弹出高级搜索模态框
		$('#osjob_searchAll').on('click', function(){
			$('#osJobModal').modal({'width':'851px', 'height':'400px'});
		});
		
		//高级搜索发送请求到服务端
		$('#os_serach_submit').on('click', function(){
			$('.osjob_search').val('');
			var p = {};
			p.startTime = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.os_hypervisor = $('#osJobModal #os_hypervisor').val();
			p.os_taskType = $('#osJobModal #os_tasktype').val();
			
			p.osjob_status = $('#osJobModal #osjob_status').val();
			p.os_taskName = $('#osJobModal #os_taskName').val();
			p.os_node = $('#osJobModal #os_node').val();
			searchParams = p;
			var params = {start:0, length:10, search: p, accurateFlag: true};
			var data = {m:CONF.M.JOB,f:'getCurrentOsJobs',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#osJobModal').modal('hide');
			grid.getRefresh(params, undefined, true);
			clearTimeout(timerTask.CurrentVMJob_data);
		});
		
		
	}
	
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('#os_searchDiv .searchContent').text('');
		var os_taskName = xssEncode($('#osJobModal #os_taskName').val());
		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> ' + LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
//		if(p.os_hypervisor != "0"){
//			info += '<span id="os_hypervisor" title="' + $('#osJobModal #os_hypervisor').find("option:selected").text() + '"> ' + LANG.UI_REPORT_PLATFORM +': <i>' + $('#osJobModal #os_hypervisor').find("option:selected").text() + '</i><em>X</em></span>';
//		}
		if(p.os_taskType !="0"){
			info += '<span id="os_tasktype" title="' + $('#osJobModal #os_tasktype').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_TYPE +': <i>' + $('#osJobModal #os_tasktype').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.osjob_status != "0"){
			info += '<span id="osjob_status" title="' + $('#osJobModal #osjob_status').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_STATUS +': <i>' + $('#osJobModal #osjob_status').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.os_taskName){
			info += '<span id="os_taskName" style="position: relative"><span id="os_taskNameDetail" style="display: none;position: absolute;bottom: -40px;left: 0px;background: white"> ' + os_taskName + '</span> '+ LANG.UI_SEARCH_TASK_NAME +': <i>' + os_taskName + '</i><em>X</em></span>&nbsp;';		}
		if(p.os_node != "0"){
			info += '<span id="os_node" title="' + $('#osJobModal #os_node').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_SELET_NODE +': <i>' + $('#osJobModal #os_node').find("option:selected").text() + '</i><em>X</em></span>';
		}
		$('#os_searchDiv .searchContent').append(info);
		$('#os_searchDiv').show();
		$('#os_searchDiv .searchContent #os_taskName').mouseenter(function(e){
			$('#os_taskNameDetail').show()
		}).mouseleave(function(){
			$('#os_taskNameDetail').hide()
		});
		$('#os_searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('#os_searchDiv .searchContent');
			if(searchContent[0].children.length == 0){
				$('#os_searchDiv').hide();
			}
			var parent  = $(this).parent();
			var id = parent[0].id;
			if(id == "time"){
				p.startTime = "";
				p.endTime = "";
			}else{
				p[id] = "";
			}
			var params = {start:0, length:10, search: p, accurateFlag: true};
			var data = {m:CONF.M.JOB,f:'getCurrentOsJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		
		$('#os_searchDiv .clearSearch').on('click',function(){
			$('#os_searchDiv .searchContent').text('');
			$('#os_searchDiv').hide();
			var params = {start:0, length:10, search: {}, accurateFlag: false};
			var data = {m:CONF.M.JOB,f:'getCurrentOsJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#os_searchDiv').hide();
		}
	}
	
	//得到参数
	var getParams = function(){
		//页码
		var page = parseInt($('.pagination-panel-input').val());
		//每页条数
		var size = $('select[name=datatable_length]').val();
		//搜索项
		var name = $.trim($('.osjob_search').val());
		if(!searchFlag){
			name = '';
		}
		var p = {start:0, length:10, search:{name:''}};
		if(undefined != page && page > 0){
			p.start = page - 1;
			p.length = 10;
			p.search = {name: name};
		}
		if(searchParams){
			p = {start:0, length:10, search:searchParams};
		}
		return p;
	}
	
	//添加详情信息 
	var addDetails = function(nTr, data){
		if(!data){
			return;
		}
    	var sOut = '<tr class="details"><td class="details" colspan="11">';
    	sOut += '<table>';
        sOut += getTimeStrategy(data[10].taskType, data[11]);
        sOut += getReservedStrategy(data[11]);
        sOut += getInstantDetail(data[11]);
        sOut += getMotionDetail(data[11]);
        sOut += getGrainDetail(data[11]);
        sOut += getCreateTime(data[14]);
        sOut += '</table></td></tr>';
		$(nTr).after(sOut);
	}
	
	
	//得到细粒度任务展开详情
	var getGrainDetail = function(msg){
		var detail = '';
		var grainDetail = msg.grainDetail;
		if(!grainDetail){
			return detail;
		}
		detail += "<tr><td>" + LANG.UI_PUBLIC_VM + ":</td><td>" + grainDetail.osname + "</td></tr>";
		detail += "<tr><td>" + LANG.UI_PUBLIC_TIMEPOINT + ":</td><td>" + grainDetail.timepoint + "</td></tr>";
		
		detail += "</td></tr>";
		return detail;
	}
	
	//得到当前任务迁移展开详情
	var getMotionDetail = function(msg){
		var detail = '';
		var motionDetail = msg.motionDetail;
		if(!motionDetail){
			return detail;
		}
		detail += "<tr><td>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ":</td><td>" + motionDetail.timepoint + "</td></tr>";
		detail += "<tr><td>" + LANG.UI_JOB_OLD_NAME + ":</td><td>" + motionDetail.oldname + "</td></tr>";
		detail += "<tr><td>" + LANG.UI_JOB_MOTION_NEW_NAME + ":</td><td>" + motionDetail.newname;
		
		detail += "</td></tr>";
		return detail;
	}
	
	
	//得到当前任务瞬时恢复展开详情
	var getInstantDetail = function(msg){
		var detail = '';
		var instantDetail = msg.instantDetail;
		if(!instantDetail){
			return detail;
		}
		detail += "<tr><td>" + LANG.UI_JOB_HIS_BAK_TIMEPOINT + ":</td><td>" + instantDetail.timepoint + "</td></tr>";
		detail += "<tr><td>" + LANG.UI_JOB_OLD_NAME + ":</td><td>" + instantDetail.oldname + "</td></tr>";
		detail += "<tr><td>" + LANG.UI_JOB_INSTANT_NEW_NAME + ":</td><td>" + instantDetail.newname;
		
		detail += "</td></tr>";
		return detail;
	}
	
	//得到时间策略描述信息
	var getTimeStrategy = function(taskType, msg){
		var timeStr = '';
		if(!msg.timeStrategy.length){
			if(taskType == 2 || taskType == 29){
				//虚拟机恢复|数据库恢复
				timeStr = "<tr><td>" + LANG.UI_STRATEGY_TIME + ":</td><td> " + LANG.UI_JOB_ONCE_TIME_RECOVER + "</td></tr>";
			}else if(taskType == 37){
				//数据验证
				timeStr = "<tr><td>" + LANG.UI_STRATEGY_TIME + ":</td><td> " + LANG.UI_JOB_VERTIFY_DATA_IMMEDIATE + "</td></tr>";
			}
			//恢复立即执行方式直接返回
			return timeStr;
		}
		
		timeStr += "<tr><td>" + LANG.UI_STRATEGY_TIME + ":</td><td>";
		for(var i=0; i<msg.timeStrategy.length; i++){
			var strategy = msg.timeStrategy[i];
			var typeDes = getModeDes(strategy.mode, msg.dbProtectDetail, taskType);
			var des = typeDes + ": ";
			if(strategy.mode == 5 || strategy.mode == 6){
				des = "";
			}
			if(CONF.STRATEGY_TYPE.DAY == strategy.type){
				des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy, taskType); 
			}else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
				des += getStrategyFrequency(strategy);
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy,taskType);
				}else{
					des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy,taskType);
				}
			}else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
				des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy, taskType);
			}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
				des = LANG.UI_PUBLIC_START_TIME +': '+ strategy.startTime; 
			}else{
				des += LANG.UI_PUBLIC_NOTHING + "<br>";
			}
			timeStr += des;
			
		}
		
		timeStr += "</td></tr>";
		return timeStr;
	}
	
	
	//得到创建任务时间
	var getCreateTime = function(createTime){
		var str = "<tr><td>" + LANG.UI_JOB_CREATE_OR_MODIFI_TIME + ":</td><td>" + createTime + "</td></tr>";
		return str;
	}
	
	
	//得到备份间隔描述
	var getStrategyFrequency = function(strategy){
		var frequency = "";
		var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
		for(var i=1;i<=20;i++){
			if(strategy.frequency == "s" +i){
				if(i == 1){
					frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
				}
				else{
					frequency = frequencyLang.replace('x', i) + ",";
				}
			}
		}
		return frequency;
	}
	
	//策略模式选择
	var getModeDes = function(mode,dbDetail,taskType){
		var des = '';
		if(1 == mode){
			des = LANG.UI_STRATEGY_FULL;
			//恢复
			if(taskType == 2 || taskType == 33){
				des = LANG.UI_STRATEGY_RECOVERY;
			}else if(taskType == 37){
			//数据验证
				des = LANG.UI_STRATEGY_VERTIFY;
			}
		}else if(2 == mode){
			des = LANG.UI_STRATEGY_INCREMENT;
		}else if(3 == mode){
			des = LANG.UI_STRATEGY_DIFFRENCE;
		}else if(4 == mode){
			if(dbDetail){
				if(dbDetail.dbtype == CONF.DB_TYPE.ORACLE || dbDetail.dbtype == CONF.DB_TYPE.DM || 
				dbDetail.dbtype == CONF.DB_TYPE.POSTGRE || dbDetail.dbtype == CONF.DB_TYPE.KINGBASE || dbDetail.dbtype == CONF.DB_TYPE.ANTDB ||
				dbDetail.dbtype == CONF.DB_TYPE.UXDB || dbDetail.dbtype == CONF.DB_TYPE.HIGHGO){
					des = LANG.UI_STRATEGY_ARCHIVE_LOG;
				}else{
					des = LANG.UI_STRATEGY_LOG;
				}
			}else{
				des = LANG.UI_STRATEGY_LOG;
			}
		}else if(9 == mode){
			des = LANG.UI_STRATEGY_PERMANENT_INCREMENT;
		}
		return des;
	}
	
	//每个策略详细信息
	var getEachStrategy = function(strategy, taskType){
		var desEach = '';
		desEach += strategy.startTime; //策略开始时间
		if(taskType == 2){
			//如果是恢复
			desEach += LANG.UI_STRATEGY_START;
		}else{
			desEach += LANG.UI_STRATEGY_START + ", ";
			if(strategy.rollFlag){
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
			}else{
				desEach += LANG.UI_STRATEGY_ROLL_NO;
			}
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
	//得到保留策略描述信息
	var getReservedStrategy = function(msg){
		var reservedStr = '';
		if(!msg.reservedStrategy){
			return reservedStr;
		}
		reservedStr += "<tr><td>" + LANG.UI_STRATEGY_RESERVE + ":</td><td>";
		if(CONF.RESERVE_TYPE.NUM == msg.reservedStrategy.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_NUM;
		}else if(CONF.RESERVE_TYPE.DAY == msg.reservedStrategy.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_DAY;
		}
		reservedStr += "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.reservedStrategy.value;

//		//先得到GFS策略信息
//		var GFSinfo = msg.reservedStrategy.GFSinfo;
//		if(GFSinfo.length != 0){
//			reservedStr += "</br>"+" GFS策略: 开启";
//			reservedStr += "</br>"+" GFS配置:"+ $("#currentTable").getGFSStr(GFSinfo);
//		}else{
//			reservedStr += "</br>"+" GFS策略: 关闭";
//		}
		reservedStr += "</td></tr>";
		return reservedStr;
	}
	
	
	//初始化表格
    var handleRecords = function () {
    	//读取cookie里的保存列表状态
    	var length = "";
    	if($.cookie('pageLength')){
			var pageList = JSON.parse($.cookie('pageLength'));
			if(pageList.current){
	    		length = pageList.current;
	    	}
	    	
		}
    	var updateInterval = 5000;
    	var dataTableOpt = {
    			'showLoading':false,
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [2,4,6,9]
    			}],
    			"order": [
                    [0, "desc"]
                ],
                'pageLength': parseInt(length)
    	};
    	var initGrid = function(){
    		if(0 == $('#current_os_job').size()){
        		clearTimeout(timerTask.CurrentVMJob_data);
        		return;
        	}
    		if(!gridInitFlag){
    			//加载显示页数分页条
    			grid = new Datatable();
        		var data = {m:CONF.M.JOB,f:'getCurrentOsJobs',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#osJobTable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
            	
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(expandDiv == 0 && bootboxConfirm == 0){
    				grid.getRefresh(getParams());
    			}
    		}
    		timerTask.CurrentVMJob_data = setTimeout(initGrid, updateInterval);
    	}
    	initGrid();
    	
    	
    	$('#osJobTable').on('click', ' tbody td .row-details', function () {
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
    	
    	return;
    }
    
	
    //初始化日期选择插件
    var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepickerCurrentOsJob').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),												//默认结束时间
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,										//是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#daterangepickerCurrentOsJob').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerCurrentOsJob').on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			_daterangepicker_range = "";
			$(this).val('');
		});

		//input右侧的图标事件
		$('.daterangepickerdiv i').click(function() {
			$(this).parent().find('input').click();
		});
	}
    
    //初始化所有备份节点
    var initNodeSelect = function(){
    	$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeList',p:{}}, function(d){
			var data = JSON.parse(d);
			var nodeSelect = $('#osJobModal #os_node');
			nodeSelect.empty();
			var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
			nodeSelect.append(option);
			for(var i=0; i<data.length; i++){
				option = $("<option>").text(data[i].node_name).val(data[i].node_uuid);
				nodeSelect.append(option);
			}
			nodeSelect.val('0');
		});
    }
    
    //时间范围判断,开始时间不得大于结束事假
    var timeCheck = function(startTime,endTime){
    	var ST = new Date(Date.parse(startTime.replace("-", "/")));
    	var ET = new Date(Date.parse(endTime.replace("-", "/")));
    	if(ST>ET){
    		 UIToastr.showInfo(LANG.UI_JOB_SEARCH_EXP,LANG.UI_JOB_SEARCH_TIME_HITE);
    		 return false;
    	}
    	return true;
    }

	//模块类型选择
	var moduleHandler = function(){
		//虚拟机
		var module = 5;
		var datalist = {};
		datalist.module_type = module;
		datalist = JSON.stringify(datalist);
		var select_html = '<option value = "0">' + LANG.UI_JOB_SELECT + '</option>';
		$.post(CONF.AJAXPATH, {m:CONF.M.ROLE,f:'getTaskTypeByPermission',p:datalist}, function(d){
			var task_type_list = JSON.parse(d);
			for (var key in task_type_list) {
				if (task_type_list.hasOwnProperty(key)) {
					var value = task_type_list[key];
					var key_des = "";
					switch (key){
						case "1":
						case "28":
						case "35":
						case "32":
							key_des = LANG.UI_VISUAL_BACKUP;
							break;
						case "2":
						case "29":
						case "36":
						case "33":
						case "22":
							key_des = LANG.UI_VISUAL_RECOVERY;
							break;
						case "7":
							key_des = LANG.UI_VISUAL_INSTANT_NAME;
							break;
						case "8":
							key_des = LANG.UI_VISUAL_MOTION_NAME;
							break;
						case "6":
							key_des = LANG.UI_VISUAL_RECOVERY_GRAIN;
							break;
						case "37":
							key_des = LANG.UI_VISUAL_DATA_VERTIFY;
							break;
						case "17":
							key_des = LANG.UI_VISUAL_DRILLS_VM_COPY;
							break;
						case "18":
							key_des = LANG.UI_VISUAL_DRILLS_VM_COPY_CALLBACK;
							break;
						case "26":
							key_des = LANG.UI_VISUAL_FILE_COPY;
							break;
						case "27":
							key_des = LANG.UI_VISUAL_FILE_COPY_CALLBACK;
							break;
						case "30":
							key_des = LANG.UI_VIRTUAL_DB_COPY;
							break;
						case "31":
							key_des = LANG.UI_VIRTUAL_DB_COPY_BACK;
							break;
						case "19":
							key_des = LANG.UI_VISUAL_ARCHIVE;
							break;
						case "20":
							key_des = LANG.UI_VISUAL_ARCHIVE_CALLBACK;
							break;
						case "34":
							key_des = LANG.UI_VISUAL_TAKEOVER;
							break;
						case "21":
							key_des = LANG.UI_VISUAL_CDP;
							break;
					}
					select_html += '<option value = "'+key+'">'+key_des+'</option>';
				}
			}
			$('#os_tasktype').empty().html(select_html);
		});
	}
    
    
    

    return {
        //main function to initiate the module
        init: function () {
        	inintDatatimePicker(); //初始化日期选择
            addListeners(); //初始化监听事件
			moduleHandler();
        }

    };

}();

jQuery(document).ready(function() {    
	CurrentOsJob.init();
});