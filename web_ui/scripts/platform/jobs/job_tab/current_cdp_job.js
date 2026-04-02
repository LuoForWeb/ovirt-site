var CurrentCDPJob = function () {
	
	var grid, gridInitFlag, searchFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	var searchParams;
	var initCdp = false;
	//时间选择器全局变量,方便提交搜索的时候直接使用
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	//添加按钮操作，展开项目
	var addOpButton = function(d){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('#cdpJobTable tbody > tr').find('td:eq(7)');
		var nameDiv = $('#cdpJobTable tbody > tr').find('td:eq(1)');
		var levelDiv = $('#cdpJobTable tbody > tr').find('td:eq(5)');
		
		//有几列 添加 几次
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i][6], i, data[i][9], data[i][7], data[i][8]);
			nameHref(nameDiv[i], data[i]);
			setLevel(levelDiv[i], data[i]);
		}
		//opButton 对应点击操作
		addOpButtonListener();
		for(var i=0; i<opDiv.length; i++){
			var uuid = data[i][7].uuid;
			var moduletype = data[i][7].module;
			var taskType = data[i][7].taskType;
			var strategy = data[i][10];
			var status = parseInt(data[i][9]);
			switch(status){
				case 1:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					break;
				case 2:
				case 12:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "start");
					break;
				case 3:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					break;
				case 4:
					addForbidButton(uuid, "stop");
					break;
				case 5:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "edit");
					break;
				case 6:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					break;
				case 7:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "edit");
					break;
				case 8:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					break;
				case 9:
				
					break;
				case 10:
					
					break;
				case 11:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					break;
				case 13:
					addForbidButton(uuid, "stop");
					break;
				case 14:
					//接管
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "stop");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "delete");
//					addForbidButton(uuid, "takeover");
					break;
			}
			if(14 != status){
				//如果不是正在接管,禁用停止接管
//				addForbidButton(uuid, "stoptakeover");
			}
			
		}
		//添加展开项目
		addDetailsInfo();
	}
	
	//添加禁止点击的按钮样式
	var addForbidButton = function(uuid, option){
		$('#cdpJobTable #' + uuid + ' .' +  option).unbind();
		$('#cdpJobTable #' + uuid + ' .' +  option + ' a').css("opacity",".4");
		$('#cdpJobTable #' + uuid + ' .' +  option + ' a').css("cursor","default");
		$('#cdpJobTable #' + uuid).on("click", "." +  option + " a", function(e) {
	        e.stopPropagation();
	    });
	}
	
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('#current_cdp_job .pagination-panel-input').val());
		if(currentPage != pageIndex){
			return true;
		}
		if(detailsInfo){
			var tr = $('#cdpJobTable tbody tr');
			var data = grid.getDataTable().data();
        	addDetails(tr[detailsIndex], data[detailsIndex]);
			var openTr = $('#cdpJobTable tbody > tr')[detailsIndex];
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
	}
	
	var nameHref = function(div, data){
		var url = getDetailsUrl(data[7].module, data[7].taskType);
		var nameStr = '<a href="' + url + '?type=' + data[7].taskType + '&uuid=' + data[7].uuid + 
			  '" class="ajaxify" name="task">' + data[0] + '</a>';
		$(div).html(nameStr);
	}
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[7].status);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[4] + '</span>';
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
		if(10000 == module){
			//数据库CDP
			if(21 == taskType){
				//实时备份
				url = "./content/db/db_cdp_job_details.php";
			}
			if(22 == taskType){
				//数据恢复
				url = "./content/db/db_recovery_job_details.php";
			}
		}else if(10001 == module){
			//文件CDP
			if(24 == taskType){
				//实时备份
				url = "./content/fs/fs_cdp_job_details.php";
			}
			if(25 == taskType){
				//数据恢复
				url = "./content/fs/fs_recovery_job_details.php";
			}
		}
		return url;
	}
	
	//添加操作按钮
	var opButton = function(div, opCode, rowNum, status, data, otherInfo){
		//全局观察者只读不能操作
		if($.inArray('global_write', CONF.PERMISSION) == -1 && $.inArray('global_observer', CONF.PERMISSION) != -1){
			$(div).html('---');
			return;
		}
		var vmuuid = data.uuid;
		var button = '<div class="btn-group  positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group  positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu" id="'+ vmuuid +'">';
		
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
					if($.inArray('global_observer', CONF.PERMISSION) != -1) break;
					button += '<li class="takeover"><a href="javascript:;" ><i class="viconfont vicon-vol_cdp_takeover"></i> ' + LANG.UI_JOB_START_TAKEOVER + '</a></li>';
					break;
				case 12:
					if($.inArray('global_observer', CONF.PERMISSION) != -1) break;
					button += '<li class="stoptakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
					break;
				case 13:
					break;
					
			}
		});
		button += '</ul></div>';
		$(div).html(button);
		
	}
	//启动完全
	var startJob = function(){
		startJobUnify(this, 'startJob', 1);
	}
	
	
	//暂停
	var pauseJob = function(){
		opJob(this, 'pauseJob');
	}
	//停止
	var stopJob = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		var button = this;
		opJob(button, 'stopJob');
		
	}
	//删除
	var deleteJob = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var message = LANG.UI_JOB_DELETE_JOB_TIPS;
		if(21 == taskType){
			//如果是数据库实时任务,换一下提示语TODO
			message = LANG.UI_JOB_DELETE_JOB_TIPS + LANG.UI_JOB_DEL_RTTASK_DEL_BAKDATA;
		}
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
		var params = data[row][7];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var uuid = params.uuid;
		var tenantuuid = params.tenantuuid;
		switch(module){
			case 10000:
				//数据库CDP
				editDbCDPJob(taskType, uuid);
				break;
			case 10001:
				//文件CDP
				editFsCDPJob(taskType, uuid);
				break;
		}
	}
	
	//修改数据库CDP任务
	var editDbCDPJob = function(taskType, uuid){
		var url = '';
		switch(taskType){
			case 21:
				//实时备份
				url = './content/db/dbcdpedit.php?uuid=' + uuid;
				break;
		}
		LOCATION(url);
	}
	
	//修改文件CDP任务
	var editFsCDPJob = function(taskType, uuid){
		var url = '';
		switch(taskType){
			case 24:
				//实时备份
				url = './content/fs/filecdpedit.php?uuid=' + uuid;
				break;
		}
		LOCATION(url);
	}
	
	
	var opJob = function(button, funName){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		params.dbcdp = data[row][8].dbCDPDetail;
		params.fscdp = data[row][8].fileCDPInfo;
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_cdp_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_cdp_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
    		}
    	});
	}
	//接管
	var takeover = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][9];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var takethis = this;
		bootbox.confirm({
            title: LANG.UI_JOB_START_TAKEOVER,
            message: LANG.UI_JOB_START_TAKEOVER_TIPS1 + '<br>' + 
					 LANG.UI_JOB_START_TAKEOVER_TIPS2 + '<br>' + 
					 LANG.UI_JOB_START_TAKEOVER_TIPS3,
            callback: function(r) {
                if(!r) return;
                opJob(takethis, 'takeover');
            }
        });
	}
	//停止接管
	var stoptakeover = function(){
		opJob(this, 'stopTakeover');
	}
	
	var dbCDPOpJob = function(button, funName){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		params.dbcdp = data[row][8].dbCDPDetail;
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_cdp_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_cdp_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
    		}
    	});
	}
	
	//启动任务	
	var startJobUnify = function(button, funName, type){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][7];
		params.startType = type;
		params.dbcdp = data[row][8].dbCDPDetail;
		params.fscdp = data[row][8].fileCDPInfo;
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_cdp_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_cdp_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
    		}
    	});
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('#current_cdp_job .start').unbind().on('click', startJob); //启动完备任务
		$('#current_cdp_job .stop').unbind().on('click', stopJob);   //终止任务
		$('#current_cdp_job .edit').unbind().on('click', editJob);   //编辑任务
		$('#current_cdp_job .delete').unbind().on('click', deleteJob);//删除任务
		$('#current_cdp_job .takeover').unbind().on('click', takeover);      //接管
		$('#current_cdp_job .stoptakeover').unbind().on('click', stoptakeover);  //停止接管
	}
	
	
	var searchCurrentJob = function(){
		//清除高级筛选显示内容
		$('#cdp_searchDiv .searchContent').text('');
		$('#cdp_searchDiv').hide();
		var name = $.trim($('.cdpjob_search').val());
		var p = {start:0, length:10, search:{name:name}};
		var data = {m:CONF.M.JOB,f:'getCurrentCDPJobs',p:p};
		grid.setAjaxParam(data);
		grid.getRefresh(p, undefined, true);
		searchFlag = true;
	}
	
	//初始化事件
	var addListeners = function(){
		//初始化列表接口
		// $('#cdpLi').on('click', function(){
			if(!initCdp){
	        	handleRecords();//初始化表格
	        	initCdp = true;
			}
		// });
		//切换页数保存到cookie
		$('select[name=currentTable_length]').on('change', function(){
			pageLength.current = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		
		$('#cdpjob_searchbtn').on('click', searchCurrentJob);
		
		$('.cdpjob_search').keypress(function (e) {
            if (e.which == 13) {
            	searchCurrentJob();
            }
        });

		
		//弹出高级搜索模态框
		$('#cdpjob_searchAll').on('click', function(){
			$('#cdpJobModal').modal({'width':'851px', 'height':'400px'});
		});
		
		//高级搜索发送请求到服务端
		$('#cdp_serach_submit').on('click', function(){
			$('.cdpjob_search').val('');
			var p = {};
			p.startTime = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.taskType = $('#cdpJobModal #cdp_tasktype').val();
			
			p.taskStatus = $('#cdpJobModal #cdp_status').val();
			p.taskName = $('#cdpJobModal #cdp_taskName').val();
			searchParams = p;
			var params = {start:0, length:10, search: p, accurateFlag: true};
			var data = {m:CONF.M.JOB,f:'getCurrentCDPJobs',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#cdpJobModal').modal('hide');
			grid.getRefresh(params, undefined, true);
			clearTimeout(timerTask.CurrentCDPJob_data);
		});
		
		
	}
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('#cdp_searchDiv .searchContent').text('');
		var cdp_taskName = xssEncode($('#cdpJobModal #cdp_taskName').val());
		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> ' + LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
		if(p.taskType !="0"){
			info += '<span id="cdp_tasktype" title="' + $('#cdpJobModal #cdp_tasktype').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_TYPE +': <i>' + $('#cdpJobModal #cdp_tasktype').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.taskStatus != "0"){
			info += '<span id="cdp_status" title="' + $('#cdpJobModal #cdp_status').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_STATUS +': <i>' + $('#cdpJobModal #cdp_status').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.taskName){
			info += '<span id="cdp_taskName" style="position: relative"> <span id="cdp_taskNameDetail" style="display: none;position: absolute;bottom: -40px;left: 0px;background: white"> ' + cdp_taskName + '</span>'+ LANG.UI_SEARCH_TASK_NAME +': <i>' + cdp_taskName + '</i><em>X</em></span>&nbsp;';		}
		
		$('#cdp_searchDiv .searchContent').append(info);
		$('#cdp_searchDiv').show();
		$('#cdp_searchDiv .searchContent .searchContent #cdp_taskName').mouseenter(function(e){
			$('#cdp_taskNameDetail').show()
		}).mouseleave(function(){
			$('#cdp_taskNameDetail').hide()
		});
		$('#cdp_searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('#cdp_searchDiv .searchContent');
			if(searchContent[0].children.length == 0){
				$('#cdp_searchDiv').hide();
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
			var data = {m:CONF.M.JOB,f:'getCurrentCDPJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		
		$('#cdp_searchDiv .clearSearch').on('click',function(){
			$('#cdp_searchDiv .searchContent').text('');
			$('#cdp_searchDiv').hide();
			var params = {start:0, length:10, search: {}, accurateFlag: false};
			var data = {m:CONF.M.JOB,f:'getCurrentCDPJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#cdp_searchDiv').hide();
		}
	}
	
	//得到参数
	var getParams = function(){
		//页码
		var page = parseInt($('.pagination-panel-input').val());
		//每页条数
		var size = $('select[name=datatable_length]').val();
		//搜索项
		var name = $.trim($('.cdpjob_search').val());
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
        sOut += getDbCDPDetail(data[8]);
        sOut += getFsCDPDetail(data[8]);
        if(data[7].taskType == 21){
        	 sOut += getReserveStrategy(data[12]);
        }
        sOut += getCreateTime(data[11]);
        sOut += '</table></td></tr>';
		$(nTr).after(sOut);
	}
	
	var getReserveStrategy = function(msg){
		var str = "<tr><td>" + LANG.UI_JOB_LOG_CONFIG + ":</td><td>" + getLogSettingDes(msg) + "</td></tr>";
		return str;
	}
	
	//得到日志保留配置描述
	var getLogSettingDes = function(logSetting){
		var logshow = "";
		var logmode = parseInt(logSetting.logmode);
		switch(logmode){
			case 0:
				logshow = LANG.UI_JOB_KEEP_LOG_BY_RECOV_NUM +  logSetting.maxstep;
				break;
			case 1:
				logshow = LANG.UI_JOB_KEEP_LOG_BY_SIZE +  logSetting.maxsize + " " + getLogUnits(logSetting.sizeunits);
				break;
			case 2:
				logshow = LANG.UI_JOB_KEEP_LOG_BY_SIZE_ADAPT;
				break;
			case 3:
				logshow = LANG.UI_JOB_KEEP_LOG_BY_TIME + logSetting.maxhour + LANG.UI_PUBLIC_HOUR;
				break;
		}
		return logshow;
	}
	
	//得到创建任务时间
	var getCreateTime = function(createTime){
		var str = "<tr><td>" + LANG.UI_JOB_CREATE_OR_MODIFI_TIME + ":</td><td>" + createTime + "</td></tr>";
		return str;
	}
	
	///得到数据库CDP任务展开详情
	var getDbCDPDetail = function(msg){
		var detail = '';
		var dbCDPDetail = msg.dbCDPDetail;
		if(!dbCDPDetail){
			return detail;
		}
		return getCDPDetail(dbCDPDetail);
	}
	
	//得到文件CDP任务展开详情
	var getFsCDPDetail = function(msg){
		var detail = '';
		var fsCDPDetail = msg.fileCDPInfo;
		if(!fsCDPDetail){
			return detail;
		}
		return getCDPDetail(fsCDPDetail);
	}
	
	//得到CDP任务详情,文件和数据库实时通用
	var getCDPDetail = function(details){
		var detail = '';
		if(22 == details.tasktype || 25 == details.tasktype){
			firstName = LANG.UI_RECOVERY_RESOURSE;
			secondName = LANG.UI_RECOVERY_GOAL;
			detail = "";
			detail += "<tr><td>" + firstName + ":</td><td>" + details.standbydes + "</td></tr>";
			detail += "<tr><td>" + secondName + ":</td><td>" + details.productdes + "</td></tr>";
		}
		detail += "</td></tr>";
		return detail;
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
		reservedStr += "</td></tr>";
		return reservedStr;
	}
	
	//得到副本回传传输策略描述信息
	var getTransportStrategy = function(msg){
		var transportStr = '';
		if(!msg.transportStrategy){
			return transportStr;
		}
		transportStr += "<tr><td>" + LANG.UI_STRATEGY_TRANSFER + ":</td><td>" + LANG.UI_COPY_BACK_ENCRYPT + ": " + msg.transportStrategy.encrypt_flag;
		if(msg.transportStrategy.compress_flag){
			transportStr += " , " + LANG.UI_COPY_BACK_COMPRESS + ": " +  msg.transportStrategy.compress_flag;
		}
		transportStr += "</td></tr>";
		return transportStr;
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
	                'targets': [2, 3, 5, 6]
    			}],
    			"order": [
    				[0, "desc"]
                ],
                'pageLength': parseInt(length)
    	};
    	var initGrid = function(){
    		if(0 == $('#current_cdp_job').size()){
        		clearTimeout(timerTask.CurrentCDPJob_data);
        		return;
        	}
    		if(!gridInitFlag){
    			//加载显示页数分页条
    			grid = new Datatable();
        		var data = {m:CONF.M.JOB,f:'getCurrentCDPJobs',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#cdpJobTable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
            	
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(expandDiv == 0 && bootboxConfirm == 0){
    				grid.getRefresh(getParams());
    			}
    		}
    		timerTask.CurrentCDPJob_data = setTimeout(initGrid, updateInterval);
    	}
    	initGrid();
    	
    	
    	$('#cdpJobTable').on('click', ' tbody td .row-details', function () {
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
		$('#daterangepickerCurrentCdp').daterangepicker({
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
		$('#daterangepickerCurrentCdp').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerCurrentCdp').on('cancel.daterangepicker', function(ev, picker) {
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
    
    
    

    return {
        //main function to initiate the module
        init: function () {
        	inintDatatimePicker(); //初始化日期选择
            addListeners(); //初始化监听事件
        }

    };

}();

jQuery(document).ready(function() {    
	CurrentCDPJob.init();
});