var CopyArchiveJob = function () {
	
	var grid, gridInitFlag, searchFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	var searchParams;
	var initCopy = false;
	//时间选择器全局变量,方便提交搜索的时候直接使用
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	//添加按钮操作，展开项目
	var addOpButton = function(d){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('#copyArchiveTable tbody > tr').find('td:eq(10)');
		var nameDiv = $('#copyArchiveTable tbody > tr').find('td:eq(1)');
		var levelDiv = $('#copyArchiveTable tbody > tr').find('td:eq(6)');
		
		//有几列 添加 几次
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i][9], i, data[i][12], data[i][10], data[i][11]);
			nameHref(nameDiv[i], data[i]);
			setLevel(levelDiv[i], data[i]);
		}
		//opButton 对应点击操作
		addOpButtonListener();
		for(var i=0; i<opDiv.length; i++){
			var uuid = data[i][10].uuid;
			var moduletype = data[i][10].module;
			var taskType = data[i][10].taskType;
			var strategy = data[i][14];
			var status = parseInt(data[i][12]);
			switch(status){
				case CONF.TASK_STATUS.WAITTING://等待
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "motion");
					addForbidButton(uuid, "delete");
					break;
				case CONF.TASK_STATUS.RUNNING://运行
				case CONF.TASK_STATUS.STARTING://启动中
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "startDiff");
					addForbidButton(uuid, "startIncr");
					break;
				case CONF.TASK_STATUS.PAUSED://暂停
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					break;
				case CONF.TASK_STATUS.STOPPED://停止
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "stop");
				    addForbidButton(uuid, "motion");
					break;
				case CONF.TASK_STATUS.STOPPING://停止中
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "startDiff");
					addForbidButton(uuid, "startIncr");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "motion");
					//停止中状态，变为强制停止
					$('#copyArchiveTable #' +uuid + ' .stop').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
					break;
				case CONF.TASK_STATUS.NETWORK_FAULT: //网络故障
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					break;
				case CONF.TASK_STATUS.ABNORMAL://异常
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "startDiff");
					addForbidButton(uuid, "startIncr");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "motion");
					break;
				case CONF.TASK_STATUS.ERROR://错误
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "motion");
					addForbidButton(uuid, "delete");
					break;
				case CONF.TASK_STATUS.SYNC://任务同步
					break;
				case CONF.TASK_STATUS.PREPARING: //准备中
					//细粒度恢复
					if(taskType == CONF.TASK_TYPE.VM_FILE_RECOVERY){
						addForbidButton(uuid, "start");
						addForbidButton(uuid, "delete");
					}
					break;
				case CONF.TASK_STATUS.PAUSING:	//暂停中
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					break;
				case CONF.TASK_STATUS.FINISHED:	//已完成
					addForbidButton(uuid, "stop");
					break;
				case CONF.TASK_STATUS.TAKEOVER: //接管
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "stop");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "delete");
					break;
			}
			
			//一次性备份|恢复操作
			if(strategy.type == 4){
				if(strategy.timeout){
					addForbidButton(uuid, "startStra");
				}
			}
		
		}
		//添加展开项目
		addDetailsInfo();
		//切换页数保存到cookie
		$('select[name=copyArchiveTable_length]').on('change', function(){
			pageLength.archive = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLengthArchive", data);
		});
	}
	
	//添加禁止点击的按钮样式
	var addForbidButton = function(uuid, option){
		$('#copyArchiveTable #' + uuid + ' .' +  option).unbind();
		$('#copyArchiveTable #' + uuid + ' .' +  option + ' a').css("opacity",".4");
		$('#copyArchiveTable #' + uuid + ' .' +  option + ' a').css("cursor","default");
		$('#copyArchiveTable #' + uuid).on("click", "." +  option + " a", function(e) {
	        e.stopPropagation();
	    });
	}
	
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('#copy_archive_job .pagination-panel-input').val());
		if(currentPage != pageIndex){
			return true;
		}
		if(detailsInfo){
			var tr = $('#copyArchiveTable tbody tr');
			var data = grid.getDataTable().data();
        	addDetails(tr[detailsIndex], data[detailsIndex]);
			var openTr = $('#copyArchiveTable tbody > tr')[detailsIndex];
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
	}
	
	var nameHref = function(div, data){
		var url = getDetailsUrl(data[10].taskType);
		var nameStr = '<a href="' + url + '?type=' + data[10].taskType + '&uuid=' + data[10].uuid + 
		  '" class="ajaxify" name="task">' + data[0] + '</a>';
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
			case 3:
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
	var getDetailsUrl = function(taskType){
		var url = '';
		//副本 虚拟机|文件
		if(taskType == 17 || taskType == 18 || 
		   taskType == 26 || taskType == 27 || 
		   taskType == 30 || taskType == 31 ||
		   taskType == 38 || taskType == 39 ||
		   taskType == 44 || taskType == 45){
			url = './content/vm/vm_copy_job_details.php';
		}
		//归档
		if(taskType == 19 || taskType == 20){
			url = './content/platform/archive/archive_job_details.php';
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
		opJob(button, 'stopJob');
		
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
		var tenantuuid = params.tenantuuid;
		switch(module){
			case 9:
				//副本
				if(taskType == 17 || taskType == 18 || taskType == 26 || taskType == 27 || taskType == 30 || taskType == 31 || taskType == 38 || taskType == 44){
					editCopyJob(taskType, uuid);
				}
				//归档
				if(taskType == 19 || taskType == 20){
					editArchiveJob(taskType, uuid);
				}
				break;
		}
	}
	
	
	//修改副本任务
	var editCopyJob = function(taskType, uuid){
		var url = '';
		switch(taskType){
			case CONF.TASK_TYPE.BACKUP_COPY:		//虚拟机
				url = './content/vm/vmcopyedit.php?type=vm&uuid=' + uuid;
				break;
			case CONF.TASK_TYPE.FILE_BACKUP_COPY:	//文件
			case CONF.TASK_TYPE.DB_BACKUP_COPY:		//数据库
			case CONF.TASK_TYPE.OS_BACKUP_COPY:		//操作系统
				url = './content/vm/vmcopyedit.php?uuid=' + uuid;
				break;
			case CONF.TASK_TYPE.NAS_BACKUP_COPY:	//nas
				url = './content/vm/vmcopyedit.php?type=nas&uuid=' + uuid;
				break;
		}
		LOCATION(url); 
	}

	//修改归档任务
	var editArchiveJob = function(taskType, uuid){
		var url = '';
		switch(taskType){
			case 19:
				//归档
				url = './content/platform/archive/editarchive.php?uuid=' + uuid;
				break;
		}
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
		Metronic.blockUI({target: '#copy_archive_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#copy_archive_job');
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
		Metronic.blockUI({target: '#current_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
    		}
    	});
	}
	
	//添加按钮事件
	var addOpButtonListener = function(){
		$('#copy_archive_job .start').unbind().on('click', startJob); //启动完备任务
		$('#copy_archive_job .pause').unbind().on('click', pauseJob); //暂停任务
		$('#copy_archive_job .stop').unbind().on('click', stopJob);   //终止任务
		$('#copy_archive_job .edit').unbind().on('click', editJob);   //编辑任务
		$('#copy_archive_job .delete').unbind().on('click', deleteJob);//删除任务
		$('#copy_archive_job .startDiff').unbind().on('click', startDiff);//差异
		$('#copy_archive_job .startIncr').unbind().on('click', startIncr);//增量
		$('#copy_archive_job .startLog').unbind().on('click', startLog);//日志
		$('#copy_archive_job .startStra').unbind().on('click', startStra);//启动策略
	}
	
	var searchCurrentJob = function(){
		//清除高级筛选显示内容
		$('#copyarchive_searchDiv .searchContent').text('');
		$('#copyarchive_searchDiv').hide();
		var name = $.trim($('.copyarchive_search').val());
		var p = {start:0, length:10, search:{name:name}};
		var data = {m:CONF.M.JOB,f:'getCopyArchiveJobs',p:p};
		grid.setAjaxParam(data);
		grid.getRefresh(p, undefined, true);
		searchFlag = true;
	}
	
	//初始化事件
	var addListeners = function(){
		//初始化列表接口
		// $('#copyLi').on('click', function(){
			if(!initCopy){
	        	handleRecords();//初始化表格
	        	initCopy = true;
			}
		// });
		
		$('#copyarchive_searchbtn').on('click', searchCurrentJob);
		
		$('.copyarchive_search').keypress(function (e) {
            if (e.which == 13) {
            	searchCurrentJob();
            }
        });
		
		

		
		//弹出高级搜索模态框
		$('#copyarchive_searchAll').on('click', function(){
			$('#copyArchiveModal').modal({'width':'851px', 'height':'400px'});
		});
		
		//高级搜索发送请求到服务端
		$('#copyarchive_serach_submit').on('click', function(){
			$('.copyarchive_search').val('');
			var p = {};
			p.startTime = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.endTime = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.copyarchive_tasktype = $('#copyArchiveModal #copyarchive_tasktype').val();
			
			p.taskStatus = $('#copyArchiveModal #taskStatus').val();
			p.copyarchive_taskName = $('#copyArchiveModal #copyarchive_taskName').val();
			searchParams = p;
			var params = {start:0, length:10, search: p, accurateFlag: true};
			var data = {m:CONF.M.JOB,f:'getCopyArchiveJobs',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#copyArchiveModal').modal('hide');
			grid.getRefresh(params, undefined, true);
			clearTimeout(timerTask.CopyArchvieJob_data);
		});
		
	}
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('#copyarchive_searchDiv .searchContent').text('');
		var copyarchive_taskName = xssEncode($('#copyArchiveModal #copyarchive_taskName').val());
		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> ' + LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
		if(p.copyarchive_tasktype !="0"){
			info += '<span id="copyarchive_tasktype" title="' + $('#copyArchiveModal #copyarchive_tasktype').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_TYPE +': <i>' + $('#copyArchiveModal #copyarchive_tasktype').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.taskStatus != "0"){
			info += '<span id="taskStatus" title="' + $('#copyArchiveModal #taskStatus').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_STATUS +': <i>' + $('#copyArchiveModal #taskStatus').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.copyarchive_taskName){
			info += '<span id="copyarchive_taskName" style="position: relative"><span id="copyarchive_taskNameDetail" style="display: none;position: absolute;bottom: -40px;left: 0px;background: white"> ' + copyarchive_taskName + '</span> '+ LANG.UI_SEARCH_TASK_NAME +': <i>' + copyarchive_taskName + '</i><em>X</em></span>&nbsp;';		}
		
		$('#copyarchive_searchDiv .searchContent').append(info);
		$('#copyarchive_searchDiv').show();
		$('#copyarchive_searchDiv .searchContent #copyarchive_taskName').mouseenter(function(e){
			$('#copyarchive_taskNameDetail').show()
		}).mouseleave(function(){
			$('#copyarchive_taskNameDetail').hide()
		});

		$('#copyarchive_searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('#copyarchive_searchDiv .searchContent');
			if(searchContent[0].children.length == 0){
				$('#copyarchive_searchDiv').hide();
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
			var data = {m:CONF.M.JOB,f:'getCopyArchiveJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		
		$('#copyarchive_searchDiv .clearSearch').on('click',function(){
			$('#copyarchive_searchDiv .searchContent').text('');
			$('#copyarchive_searchDiv').hide();
			var params = {start:0, length:10, search: {}, accurateFlag: false};
			var data = {m:CONF.M.JOB,f:'getCopyArchiveJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#copyarchive_searchDiv').hide();
		}
	}
	
	//得到参数
	var getParams = function(){
		//页码
		var page = parseInt($('.pagination-panel-input').val());
		//每页条数
		var size = $('select[name=datatable_length]').val();
		//搜索项
		var name = $.trim($('.copyarchive_search').val());
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
    	var sOut = '<tr class="details"><td class="details" colspan="13">';
    	sOut += '<table>';
        sOut += getTimeStrategy(data[10].taskType, data[11]);
        sOut += getReservedStrategy(data[11]);
        sOut += getTransportStrategy(data[11],data[10].taskType);
        sOut += getCreateTime(data[14]);
        sOut += getCopyInfoDetail(data[11]);
        sOut += '</table></td></tr>';
		$(nTr).after(sOut);
	}

	//副本类型显示 镜像|合并
	var getCopyInfoDetail = function(data) {
		var detail = '';
		var copyMode = data.transportStrategy.copyMode;
		if(copyMode == 1){
			detail += "<tr><td>" + LANG.UI_COPY_MODE + ":</td><td>" + LANG.UI_JOB_START_MIRROR_COPY + "</td></tr>";
		}
		if(copyMode == 2){
			detail += "<tr><td>" + LANG.UI_COPY_MODE + ":</td><td>" + LANG.UI_COPY_MODE_COMBINATION + "</td></tr>";
		}
		return detail;
	}
	
	//得到创建任务时间
	var getCreateTime = function(createTime){
		var str = "<tr><td>" + LANG.UI_JOB_CREATE_OR_MODIFI_TIME + ":</td><td>" + createTime + "</td></tr>";
		return str;
	}
	
	//得到时间策略描述信息
	var getTimeStrategy = function(taskType, msg){
		var timeStr = '';
		if(!msg.timeStrategy.length){
			if(taskType == 2 || taskType == 29){
				timeStr = "<tr><td>" + LANG.UI_STRATEGY_TIME + ": " + LANG.UI_JOB_ONCE_TIME_RECOVER + "</td></tr>";
				return timeStr;
			}
		}
		if(!msg.timeStrategy.length){
			return timeStr;
		}
		timeStr += "<tr><td>" + LANG.UI_STRATEGY_TIME + ":</td><td>";
		for(var i=0; i<msg.timeStrategy.length; i++){
//			timeStr += getEachStrategy(msg.timeStrategy[i]);
			var strategy = msg.timeStrategy[i];
			var typeDes = getModeDes(strategy.mode, msg.dbProtectDdetail);
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
	
	
	//得到下次任务开始时间
	var getNextStartTime = function(nextTime){
		var str = "<tr><td>" + LANG.UI_PUBLIC_NEXT_RUN_TIME + ":</td><td>" + nextTime + "</td></tr>";
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
	var getModeDes = function(mode,dbDetail){
		var des = '';
		if(1 == mode){
			des = LANG.UI_STRATEGY_FULL;
		}else if(2 == mode){
			des = LANG.UI_STRATEGY_INCREMENT;
		}else if(3 == mode){
			des = LANG.UI_STRATEGY_DIFFRENCE;
		}else if(4 == mode){
			if(dbDetail){
				if(dbDetail.dbtype == CONF.DB_TYPE.ORACLE || dbDetail.dbtype == CONF.DB_TYPE.DM){
					des = LANG.UI_STRATEGY_ARCHIVE_LOG;
				}
			}else{
				des = LANG.UI_STRATEGY_LOG;
			}
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
		reservedStr += "</td></tr>";
		return reservedStr;
	}
	
	//得到副本回传传输策略描述信息
	var getTransportStrategy = function(msg,taskType){
		var transportStr = '';
		if(!msg.transportStrategy){
			return transportStr;
		}
		transportStr += "<tr><td>" + LANG.UI_STRATEGY_TRANSFER + ":</td><td>" + LANG.UI_COPY_BACK_ENCRYPT + ": " + msg.transportStrategy.encrypt_flag;
		if(msg.transportStrategy.compress_flag && (taskType == CONF.TASK_TYPE.BACKUP_COPY || 
				taskType ==  CONF.TASK_TYPE.DB_BACKUP_COPY || taskType == CONF.TASK_TYPE.OS_BACKUP_COPY || taskType == CONF.TASK_TYPE.OS_BACKUP_COPY_FETCH)){
			transportStr += " , " + LANG.UI_COPY_BACK_COMPRESS + ": " +  msg.transportStrategy.compress_flag;
		}
		transportStr += "</td></tr>";
		return transportStr;
	}
	
	//初始化表格
    var handleRecords = function () {
    	//读取cookie里的保存列表状态
    	var length = "";
    	if($.cookie('pageLengthArchive')){
			var pageList = JSON.parse($.cookie('pageLengthArchive'));
			if(pageList.archive){
	    		length = pageList.archive;
	    	}
	    	
		}
    	var updateInterval = 5000;
    	var dataTableOpt = {
    			'showLoading':false,
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [4, 6, 9]
    			}],
    			"order": [
    				[0, "desc"]
                ],
                'pageLength': parseInt(length)
    	};
    	var initGrid = function(){
    		if(0 == $('#copy_archive_job').size()){
        		clearTimeout(timerTask.CopyArchvieJob_data);
        		return;
        	}
    		if(!gridInitFlag){
    			//加载显示页数分页条
    			grid = new Datatable();
        		var data = {m:CONF.M.JOB,f:'getCopyArchiveJobs',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#copyArchiveTable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
            	
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(expandDiv == 0 && bootboxConfirm == 0){
    				grid.getRefresh(getParams());
    			}
    		}
    		timerTask.CopyArchvieJob_data = setTimeout(initGrid, updateInterval);
    	}
    	initGrid();
    	
    	
    	$('#copyArchiveTable').on('click', ' tbody td .row-details', function () {
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
		$('#daterangepickerCopyArchive').daterangepicker({
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
		$('#daterangepickerCopyArchive').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerCopyArchive').on('cancel.daterangepicker', function(ev, picker) {
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

	//模块类型选择
	var moduleHandler = function(){
		//虚拟机
		var module = 9;
		var datalist = {};
		datalist.module_type = module;
		datalist = JSON.stringify(datalist);
		var select_html = '<option value = "0">'+LANG.UI_JOB_SELECT+'</option>';
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
							key_des =  LANG.UI_VISUAL_MOTION_NAME;
							break;
						case "6":
							key_des = LANG.UI_VISUAL_RECOVERY_GRAIN;
							break;
						case "37":
							key_des =  LANG.UI_VISUAL_DATA_VERTIFY;
							break;
						case "17":
							key_des = LANG.UI_VISUAL_DRILLS_VM_COPY;
							break;
						case "18":
							key_des = ANG.UI_VISUAL_DRILLS_VM_COPY_CALLBACK;
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
							key_des =  LANG.UI_VISUAL_TAKEOVER;
							break;
						case "21":
							key_des = LANG.UI_VISUAL_CDP;
							break;
					}
					select_html += '<option value = "'+key+'">'+key_des+'</option>';
				}
			}
			$('#copyarchive_tasktype').empty().html(select_html);
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
	CopyArchiveJob.init();
});