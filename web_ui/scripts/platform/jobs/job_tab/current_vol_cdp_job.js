var CurrentVolCdpJob = function () {
	var grid, gridInitFlag, searchFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	var searchParams;
	var initVolCdp = false;
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	//卷CDP任务运行中的任务控制
	var volCdpTaskRunningControlButton = function(uuid,taskType,taskCurrentStage){
		switch(taskType){
		case CONF.TASK_TYPE.VOL_CDP_BACKUP:
			switch(taskCurrentStage){
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
			case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
			case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				//暂停备份，暂时不支持，支持后需要添加；
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
			case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "startfailback");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "startfailback");
				addForbidButton(uuid, "stoptakeover");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //逆向实时同步
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:	//回切启动中
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "startfailback");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:  //逆向初始同步
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "volstop");
//				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				break;
			}
			break;
		case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
			break;
		case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
			addForbidButton(uuid,'takeover');
			addForbidButton(uuid,"startfailback");
			addForbidButton(uuid, "edit");
			switch (taskCurrentStage){
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "startfailback");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "startfailback");
				addForbidButton(uuid, "stoptakeover");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "takeover");
//				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
//				addForbidButton(uuid, "stopfailback");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "takeover");
//				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				break;
			}
			break;
		}
	}
	//卷CDP任务停止任务控制
	var volcdpTaskStopControlButton = function(uuid,taskType,taskCurrentStage){
		switch(taskType){
			case CONF.TASK_TYPE.VOL_CDP_BACKUP:
				switch(taskCurrentStage){
				case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
				case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
					addForbidButton(uuid, "takeover");
					addForbidButton(uuid, "stoptakeover");
					addForbidButton(uuid, "startfailback");
					break;
				case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
					addForbidButton(uuid, "takeover");
					addForbidButton(uuid, "stoptakeover");
					addForbidButton(uuid, "startfailback");
					break;
				}
				break;
			case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				break;
			case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
				switch(taskCurrentStage){
				case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
				case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
					addForbidButton(uuid, "stoptakeover");
					addForbidButton(uuid, "startfailback");
					break;
				case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
					addForbidButton(uuid, "stoptakeover");
					addForbidButton(uuid, "startfailback");
					addForbidButton(uuid, "edit");
					break;
				}	
				break;
		}
	}
		//卷CDP任务错误状态下的任务控制
	var volcdpTaskErrorControlButton = function(uuid,taskType,taskCurrentStage){
		switch(taskType){
		case CONF.TASK_TYPE.VOL_CDP_BACKUP:
			switch(taskCurrentStage){
			case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:  //初始化同步
			case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:  //备份实时同步
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:  //服务端的数据一致性校验
			case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:  //备机的数据一致性校验
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				addForbidButton(uuid, "volstop");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:  //接管中
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:  //接管启动中
				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				addForbidButton(uuid, "volstop");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOFAILBACK_INIT_SYNCVER_STARTING:  //逆向初始同步
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //逆向实时同步
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:  //回切启动中
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "volstop");
				addForbidButton(uuid, "start");
				addForbidButton(uuid, "edit");
				addForbidButton(uuid, "delete");
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC: //等待执行
			case CONF.CDP_TASK_RUNNING_STAGE.UNKNOWN: //未知状态
				addForbidButton(uuid, "takeover");
				addForbidButton(uuid, "stoptakeover");
				addForbidButton(uuid, "startfailback");
				addForbidButton(uuid, "volstop");
				break;
			}
			break;
		case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
			addForbidButton(uuid, "volstop");
			break;
		case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
			switch(taskCurrentStage){
				case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:  //接管中
				case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:  //接管启动中
					addForbidButton(uuid, "stoptakeover");
					addForbidButton(uuid, "startfailback");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "volstop");
					break;
				case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOFAILBACK_INIT_SYNCVER_STARTING:  //逆向初始同步
				case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:  //逆向实时同步
				case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:  //回切启动中
					addForbidButton(uuid, "takeover");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "delete");
					break;
			}
		}
	}
	//添加按钮操作，展开项目
	var addOpButton = function(d){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('#vol_cdp_job_table tbody > tr').find('td:eq(12)');
		var nameDiv = $('#vol_cdp_job_table tbody > tr').find('td:eq(1)');
		var levelDiv = $('#vol_cdp_job_table tbody > tr').find('td:eq(6)');
		var taskRunningStageDive = $('#vol_cdp_job_table tbody > tr').find('td:eq(7)')
		//有几列 添加 几次
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i][11], i, data[i][14], data[i][12], data[i][13]);
			nameHref(nameDiv[i], data[i]);
			setLevel(levelDiv[i], data[i]);
			setTaskRunningStage(taskRunningStageDive[i],data[i]);
		}
		//opButton 对应点击操作
		addOpButtonListener();
		for(var i=0; i<opDiv.length; i++){
			var uuid = 'vol'+data[i][12].uuid;
			var moduletype = data[i][12].module;
			var taskType = data[i][12].taskType;
			var strategy = data[i][13];
			var status = parseInt(data[i][14]);
			var taskCurrentStage = data[i][19];
			switch(status){
				case CONF.TASK_STATUS.WAITTING:
					addForbidButton(uuid, "volstop");
					addForbidButton(uuid, "stoptakeover");
					addForbidButton(uuid, "startfailback");
					addForbidButton(uuid, "pause");
					switch(taskType){
					case CONF.TASK_TYPE.VOL_CDP_BACKUP:
					case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
						addForbidButton(uuid, "takeover");
						break;
					}
					break;
				case CONF.TASK_STATUS.RUNNING: //运行
				case CONF.TASK_STATUS.STARTING: //启动中
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "startDiff");
					addForbidButton(uuid, "startIncr");
					addForbidButton(uuid, "startLog");
					volCdpTaskRunningControlButton(uuid,taskType,taskCurrentStage);  //卷cdp任务在运行状态下的控制操作
					break;
				case CONF.TASK_STATUS.PAUSED:
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					
					addForbidButton(uuid, "takeover");  //启动接管
					addForbidButton(uuid, "stoptakeover");  //停止接管 
					addForbidButton(uuid, "startfailback"); //启动回切
					addForbidButton(uuid, "createlable");  //创建标签点
					if(taskType == CONF.TASK_TYPE.BACKUP ){
						addForbidButton(uuid, "edit");
						addForbidButton(uuid, "startStra");
					}
					break;
				case CONF.TASK_STATUS.STOPPED:
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "volstop");
				    addForbidButton(uuid, "motion");
				    volcdpTaskStopControlButton(uuid,taskType,taskCurrentStage);
					break;
				case CONF.TASK_STATUS.STOPPING:
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "startDiff");
					addForbidButton(uuid, "startIncr");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "startLog");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "motion");
					if(moduletype==CONF.MODULE_TYPE.VOL_CDP){
						addForbidButton(uuid,"takeover");
						addForbidButton(uuid,"stoptakeover");
						addForbidButton(uuid, "startfailback");
					}
					//停止中状态，变为强制停止
					$('#vol_cdp_job_table #' +uuid + ' .volstop').html('<a href="javascript:;"><i class="glyphicon glyphicon-stop"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
					break;
				case CONF.TASK_STATUS.NETWORK_FAULT:
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					
					addForbidButton(uuid,"takeover");
					addForbidButton(uuid,"stoptakeover");
					addForbidButton(uuid, "startfailback");
					if(taskType!=CONF.TASK_TYPE.VOL_CDP_BACKUP){
						addForbidButton(uuid, "start");
					}
					break;
					break;
				case CONF.TASK_STATUS.ABNORMAL:
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "startDiff");
					addForbidButton(uuid, "startIncr");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "startLog");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "motion");
					
					addForbidButton(uuid,"takeover");  //启动接管
					addForbidButton(uuid,"stoptakeover");  //停止接管
					addForbidButton(uuid, "startfailback");  //启动回切
					break;
				case CONF.TASK_STATUS.ERROR:
					addForbidButton(uuid, "pause");
					addForbidButton(uuid, "startStra");
					addForbidButton(uuid, "motion");
					if(taskType == CONF.TASK_TYPE.BACKUP ){
						addForbidButton(uuid, "edit");
					}
					if(taskType == CONF.TASK_TYPE.BACKUP ){
						addForbidButton(uuid, "startStra");
					}
					volcdpTaskErrorControlButton(uuid,taskType,taskCurrentStage);
					break;
				case CONF.TASK_STATUS.SYNC:
					break;
				case CONF.TASK_STATUS.PREPARING:
					break;
				case CONF.TASK_STATUS.PAUSING:
					addForbidButton(uuid, "delete");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "startStra");
					
					if(moduletype!=CONF.MODULE_TYPE.VOL_CDP){
						addForbidButton(uuid,"takeover");  //启动接管
						addForbidButton(uuid,"stoptakeover");  //停止接管
						addForbidButton(uuid, "startfailback");  //启动回切
					}
					break;
				case CONF.TASK_STATUS.FINISHED:
					addForbidButton(uuid, "volstop");
					break;
				case CONF.TASK_STATUS.TAKEOVER:
					//接管
					addForbidButton(uuid, "start");
					addForbidButton(uuid, "volstop");
					addForbidButton(uuid, "edit");
					addForbidButton(uuid, "delete");
					break;
				case CONF.TASK_STATUS.SUCCESSED:
					if(taskType ==CONF.TASK_TYPE.VOL_CDP_TAKEOVER || taskType ==CONF.TASK_TYPE.VOL_CDP_BACKUP){
						switch(taskCurrentStage){
						case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
						case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
							addForbidButton(uuid, "start");
							addForbidButton(uuid, "volstop");
							
							addForbidButton(uuid,"takeover");
							addForbidButton(uuid, "delete");
							addForbidButton(uuid, "edit");
							break;
						}
					}
					break;
			}
		}
		//添加展开项目
		addDetailsInfo();
		//切换页数保存到cookie
		$('select[name=vol_cdp_job_table_length]').on('change', function(){
			pageLength.vol_cdp = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLengthVolCdp", data);
		});
	}
	
	//添加禁止点击的按钮样式
	var addForbidButton = function(uuid, option){
		$('#vol_cdp_job_table #' + uuid + ' .' +  option).unbind();
		$('#vol_cdp_job_table #' + uuid + ' .' +  option + ' a').css("opacity",".4");
		$('#vol_cdp_job_table #' + uuid + ' .' +  option + ' a').css("cursor","default");
		$('#vol_cdp_job_table #' + uuid).on("click", "." +  option + " a", function(e) {
	        e.stopPropagation();
	    });
	}
	//检测刷新的时候是否有展开项目，如果有的话就添加
	var addDetailsInfo = function(){
		var currentPage = parseInt($('#current_vol_cdp_job .pagination-panel-input').val());
		if(currentPage != pageIndex){
			return true;
		}
		if(detailsInfo){
			var tr = $('#vol_cdp_job_table tbody tr');
			var data = grid.getDataTable().data();
        	addDetails(tr[detailsIndex], data[detailsIndex]);
			var openTr = $('#vol_cdp_job_table tbody > tr')[detailsIndex];
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
	}
	//组装查看详情URL
	var nameHref = function(div, data){
		var url = getDetailsUrl(data[11].taskType);
		var nameStr = '<a href="' + url + '?type=' + data[12].taskType + '&uuid=' + data[12].uuid + 
		  '" class="ajaxify" name="task">' + data[0] + '</a>';
		$(div).html(nameStr);
	}
	//设置任务的阶段样式
	var setTaskRunningStage = function(div,data){
		var labelClass = getTaskRunningStageClass(data[12].runningStage,data[12].status);
		var currentTaskStage = data[12].runningStage;
		var statusValue = data[12].status;
		var taskType = data[12].taskType;
		
		if(currentTaskStage!=0 && statusValue!=CONF.TASK_STATUS.STOPPED && taskType!=CONF.TASK_TYPE.VOL_CDP_RECOVERY){
			var content = '<span class = "label label-sm '+labelClass+'">' + data[6] + '</span>';
		}else{
			var content = '<span class = "label label-sm '+labelClass+'">' + data[1] + '</span>';
		}
		$(div).html(content);
	}
	//得到运行阶段显示类型
	var getTaskRunningStageClass = function(runningStage,taskStatus){
		var levelClass = '';
		switch(runningStage){
			case CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC:
				levelClass = "label-info";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
				levelClass = "label-success";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
				levelClass = "label-success";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
				levelClass = "label-primary";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
				levelClass = "label-primary";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
				levelClass = "label-green";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
				levelClass = "label-blue-madison";
				break;
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
			case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
				levelClass = "label-blue-madison";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}
	//设置任务状态样式
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[12].status);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[5] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case CONF.TASK_STATUS.STOPPED:
				levelClass = "label-default";
				break;
			case CONF.TASK_STATUS.ABNORMAL:
				levelClass = "label-warning";
				break;
			case CONF.TASK_STATUS.ERROR:
			case CONF.TASK_STATUS.NETWORK_FAULT:
				levelClass = "label-danger";
				break;
			case CONF.TASK_STATUS.PREPARING:
			case CONF.TASK_STATUS.WAITTING:
			case CONF.TASK_STATUS.STOPPING:
				levelClass = "label-info";
				break;
			case CONF.TASK_STATUS.TAKEOVER:
				levelClass = "label-success";
				break;
			case CONF.TASK_STATUS.SUCCESSED:
			case CONF.TASK_STATUS.RUNNING:
			case CONF.TASK_STATUS.PAUSED:
				levelClass = "label-success";
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
		//文件
		url = './content/volcdp/cdp_job_details.php';
		return url;
	}
	
	//添加操作按钮
	var opButton = function(div, opCode, rowNum, status, data, otherInfo){
		//全局观察者只读不能操作
		if($.inArray('global_write', CONF.PERMISSION) == -1 && $.inArray('global_observer', CONF.PERMISSION) != -1){
			$(div).html('---');
			return;
		}
		var uuid = data.uuid;
		var button = '<div class="btn-group  positionabs">';
		if(rowNum > 4){
			button = '<div class="btn-group  positionabs dropup">';
		}
		button += '<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu" id="vol'+ uuid +'">';
		
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
					break;
				case 2:
					button += '<li class="volstop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
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
				case 14:
					button += '<li class="startfailback"><a href="javascript:;" ><i class="viconfont vicon-ge_cutback"></i>'+LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK+' </a></li>';
					break;
//				case 15:
//					button += '<li class="stopfailback"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i>' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
//					break;
				case 16:
					button += '<li class="createlable"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> '+LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABEL+' </a></li>';
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
		var params = data[row][12];
		var button = this;
		if(params.taskType==CONF.TASK_TYPE.VOL_CDP_BACKUP){
			var initErrorFlag = false;
			getUserPassword();
			bootbox.prompt({ 
			    title: LANG.UI_VOL_CDP_JOB_STOP_BACKUP_CONFIRM,
			    inputType: 'password',
			    callback: function (result) {
			    	if(result == null) return;
			        if(hex_md5(result) == _UserPassword){
			        	_userIsVerify = true;
			        	opJob(button, 'stopJob');
			        }else{
			        	$('.bootbox-input').css('border-color', "#a94442");
			        	if(!initErrorFlag){
			        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
			        		$('.bootbox-input').after(des);
			        		initErrorFlag = true;
			        	}
						return false;
		        	}
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
		var params = data[row][12];
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
		var params = data[row][12];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var uuid = params.uuid;
		var tenantuuid = params.tenantuuid;
		switch(module){
			case 3:
				editVolCdpJob(taskType, uuid);
				break;
		}
	}

	//修改任务
	var editVolCdpJob = function(taskType, uuid){
		var url = '';
		switch(taskType){
			case 1:
				//备份
				url = './content/volcdp/vol_cdp_backupedit.php?uuid=' + uuid;
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
		var params = data[row][12];
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#current_vol_cdp_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_vol_cdp_job');
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
	
	//初始化当前用户密码用于删除二次确认
	var getUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
	//启动任务	
	var startJobUnify = function(button, funName, type){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][12];
		var taskType = params.taskType;	//任务类型
		params.startType = type;
		params = JSON.stringify(params);
		if(taskType==CONF.TASK_TYPE.VOL_CDP_RECOVERY){
			var initErrorFlag = false;
			getUserPassword();
			bootbox.prompt({ 
			    title: LANG.UI_VOL_CDP_JOB_START_RECOVERY_CONFIRM,
			    inputType: 'password',
			    callback: function (result) {
			    	if(result == null) return;
			        if(hex_md5(result) == _UserPassword){
			        	_userIsVerify = true;
			        	startJobFunc(funName,params);
			        }else{
			        	$('.bootbox-input').css('border-color', "#a94442");
			        	if(!initErrorFlag){
			        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
			        		$('.bootbox-input').after(des);
			        		initErrorFlag = true;
			        	}
						return false;
		        	}
		        }
			});
		}else{
			startJobFunc(funName,params);
		}
	}
	
	var startJobFunc = function(funName,params){
		Metronic.blockUI({target: '#current_vol_cdp_job',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#current_vol_cdp_job');
    		if(OPREL(data)){
    			grid.getRefresh(getParams());
    		}
    	});
	}
	//接管
	var starttakeover = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var params = data[row][9];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型
		var takethis = this;
		startJobUnify(this, 'startJob', 11); //启动函数,模块类型
	}
	//点击停止接管操作
	var stoptakeover = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var bootBoxText = LANG.UI_VOL_CDP_JOB_STOP_TAKEOVER_CONFIRM;
		//回切阶段下停止接管
		var currentState = data[row][19];
		if(currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
			bootBoxText = LANG.UI_VOL_CDP_JOB_FAILBACK_STOP_TAKEOVER_CONFIRM;	
		}
		var initErrorFlag = false;
		getUserPassword();
		bootbox.prompt({ 
		    title: bootBoxText,
		    inputType: 'password',
		    callback: function (result) {
		    	if(result == null) return;
		        if(hex_md5(result) == _UserPassword){
		        	_userIsVerify = true;
		        	stopTakeoverJob(data,row);
		        }else{
		        	$('.bootbox-input').css('border-color', "#a94442");
		        	if(!initErrorFlag){
		        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
		        		$('.bootbox-input').after(des);
		        		initErrorFlag = true;
		        	}
					return false;
	        	}
	        }
		});
	}
	
	//发送停止接管控制
	var stopTakeoverJob = function(data,row){
		var params = data[row][12];
		var module = params.module;		//模块类型
		var taskType = params.taskType;	//任务类型

		var info = {};
		info.uuid = params.uuid;
		info.module = CONF.MODULE_TYPE.VOL_CDP;
		info.taskType = taskType;
		info.status = params.status;
		params = JSON.stringify(info);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'stopVolCdpTakeover',p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
	}
	//启动回切
	var startfailback = function(){
		var data = grid.getDataTable().data();
		var row = $(this).parents('tr').get(0)._DT_RowIndex;
		var initErrorFlag = false;
		getUserPassword();
		bootbox.prompt({ 
		    title: LANG.UI_VOL_CDP_JOB_START_FAILBACK_CONFIRM,
		    inputType: 'password',
		    callback: function (result) {
		    	if(result == null) return;
		        if(hex_md5(result) == _UserPassword){
		        	_userIsVerify = true;
		        	startFailbackJob(data,row);
		        }else{
		        	$('.bootbox-input').css('border-color', "#a94442");
		        	if(!initErrorFlag){
		        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
		        		$('.bootbox-input').after(des);
		        		initErrorFlag = true;
		        	}
					return false;
	        	}
	        }
		});
	}
	/**
	 * 启动回切任务执行函数
	 */
	var startFailbackJob = function(data,row){
		var params = data[row][12];
		var info = {};
		info.uuid = params.uuid;
		info.taskType = params.taskType;	//任务类型
		params = JSON.stringify(info);
    	Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getTaskFailbackInfo',p:params}, function(data){
    		Metronic.unblockUI('#jobDetail');
    		var data = JSON.parse(data);
    		if(data.length>0){
    			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startVolCdpTaskCatback',p:params}, function(d){
            		if(OPREL(d)){}
            	});
    		}else{		
    			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK, LANG.UI_VOL_CDP_JOB_DEFAILS_FAILBACK_CONFIG_MESSAGE);
    			return;
    		}
    	})
	}
	// 停止回切，发送停止接管控制码
	var stopfailback = function(){
		stoptakeover();
	}
	//添加按钮事件
	var addOpButtonListener = function(){
		$('#current_vol_cdp_job .start').unbind().on('click', startJob); //启动完备任务
		$('#current_vol_cdp_job .pause').unbind().on('click', pauseJob); //暂停任务
		$('#current_vol_cdp_job .volstop').unbind().on('click', stopJob);   //终止任务
		$('#current_vol_cdp_job .edit').unbind().on('click', editJob);   //编辑任务
		$('#current_vol_cdp_job .delete').unbind().on('click', deleteJob);//删除任务
		$('#current_vol_cdp_job .startDiff').unbind().on('click', startDiff);//差异
		$('#current_vol_cdp_job .startIncr').unbind().on('click', startIncr);//增量
		$('#current_vol_cdp_job .startLog').unbind().on('click', startLog);//日志
		$('#current_vol_cdp_job .startStra').unbind().on('click', startStra);//启动策略
		
		$('#current_vol_cdp_job .takeover').unbind().on('click', starttakeover);      //接管
		$('#current_vol_cdp_job .stoptakeover').unbind().on('click', stoptakeover);  //停止接管
		$('#current_vol_cdp_job .startfailback').unbind().on('click', startfailback);  //启动回切
	}
	
	var searchCurrentJob = function(){
		//清除高级筛选显示内容
		$('#volcdp_search_div .searchContent').text('');
		$('#volcdp_search_div').hide();
		var name = $.trim($('.vol_cdp_job_search').val());
		var p = {start:0, length:10, search:{name:name}};
		var data = {m:CONF.M.JOB,f:'getCurrentVolcdpJobs',p:p};
		grid.setAjaxParam(data);
		grid.getRefresh(p, undefined, true);
		searchFlag = true;
	}
	
	//初始化事件
	var addListeners = function(){
		//初始化列表接口
		// $('#volCdpLi').on('click', function(){
			if(!initVolCdp){
				initNodeSelect(); //初始化所有备份节点
	        	handleRecords();//初始化表格
	        	initVolCdp = true;
			}
		// });

		$('#vol_cdp_job_searchbtn').on('click', searchCurrentJob);
		
		$('.vol_cdp_job_search').keypress(function (e) {
            if (e.which == 13) {
            	searchCurrentJob();
            }
        });

		
		//弹出高级搜索模态框
		$('#vol_cdp_job_searchAll').on('click', function(){
			$('#vol_cdp_job_modal').modal({'width':'851px', 'height':'400px'});
		});
		
		//高级搜索发送请求到服务端
		$('#vol_cdp_serach_submit').on('click', function(){
			$('.vol_cdp_job_search').val('');
			var p = {};
			p.startTime = _daterangepicker_starttime;  //任务开始时间查询范围开头
			p.endTime = _daterangepicker_endtime;  //任务开始时间查询范围结尾
			p.volcdpTasktype = $('#vol_cdp_job_modal #volcdp_tasktype').val();
			
			p.volcdpStatus = $('#vol_cdp_job_modal #volcdp_status').val();
			p.volCdpTaskName = $('#vol_cdp_job_modal #vol_cdp_task_name').val();
			p.volCdpNode = $('#vol_cdp_job_modal #volcdp_node').val();
			searchParams = p;
			var params = {start:0, length:10, search: p, accurateFlag: true};
			var data = {m:CONF.M.JOB,f:'getCurrentVolcdpJobs',p:params};
			grid.setAjaxParam(data);
			//添加搜索条件显示
			addSearchContent(p);
			$('#vol_cdp_job_modal').modal('hide');
			grid.getRefresh(params, undefined, true);
			clearTimeout(timerTask.CurrentVolcdpJobData);
		});
	}
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('#volcdp_search_div .searchContent').text('');
		var volCdpTaskName = xssEncode($('#vol_cdp_job_modal #vol_cdp_task_name').val());
		if(p.startTime && p.endTime){
			info += '<span id="time" title="' + p.startTime + "~" + p.endTime + '"> ' + LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.startTime + "~" + p.endTime + '</i><em>X</em></span>';
		}
		if(p.volcdpTasktype !="0"){
			info += '<span id="volcdp_tasktype" title="' + $('#vol_cdp_job_modal #volcdp_tasktype').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_TYPE +': <i>' + $('#vol_cdp_job_modal #volcdp_tasktype').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.volcdpStatus != "0"){
			info += '<span id="volcdp_status" title="' + $('#vol_cdp_job_modal #volcdp_status').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TASK_STATUS +': <i>' + $('#vol_cdp_job_modal #volcdp_status').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.volCdpTaskName){
			info += '<span id="vol_cdp_task_name" title="' + volCdpTaskName + '"> '+ LANG.UI_SEARCH_TASK_NAME +': <i>' + volCdpTaskName + '</i><em>X</em></span>&nbsp;';
		}
		if(p.volcdpNode != "0"){
			info += '<span id="volcdp_node" title="' + $('#vol_cdp_job_modal #volcdp_node').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_SELET_NODE +': <i>' + $('#vol_cdp_job_modal #volcdp_node').find("option:selected").text() + '</i><em>X</em></span>';
		}
		
		$('#volcdp_search_div .searchContent').append(info);
		$('#volcdp_search_div').show();
		$('#volcdp_search_div .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('#volcdp_search_div .searchContent');
			if(searchContent[0].children.length == 0){
				$('#volcdp_search_div').hide();
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
			var data = {m:CONF.M.JOB,f:'getCurrentVolcdpJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
			
		});
		
		$('#volcdp_search_div .clearSearch').on('click',function(){
			$('#volcdp_search_div .searchContent').text('');
			$('#volcdp_search_div').hide();
			var params = {start:0, length:10, search: {}, accurateFlag: false};
			var data = {m:CONF.M.JOB,f:'getCurrentVolcdpJobs',p:params};
			grid.setAjaxParam(data);
			grid.getRefresh(params, undefined, true);
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#volcdp_search_div').hide();
		}
	}
	
	//得到参数
	var getParams = function(){
		//页码
		var page = parseInt($('.pagination-panel-input').val());
		//每页条数
		var size = $('select[name=datatable_length]').val();
		//搜索项
		var name = $.trim($('.vol_cdp_job_search').val());
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
       // sOut += getReservedStrategy(data[11]);
    	sOut += getTaskAgentInfo(data[13]);
    	sOut += getCurrentTaskDetailsInfo(data);
    	sOut += getCreateTime(data[4]);
        sOut += '</table></td></tr>';
		$(nTr).after(sOut);
	}
	//解析任务配置的客户端信息
	var getTaskAgentInfo = function($data){
		var str = "<tr><td>" + LANG.UI_PUBLIC_CLIENT + ":</td><td>" + $data.agentInfo + "</td></tr>";
		return str;
	}
	//得到创建任务时间
	var getCreateTime = function(createTime){
		var str = "<tr><td>" + LANG.UI_JOB_CREATE_OR_MODIFI_TIME + ":</td><td>" + createTime + "</td></tr>";
		return str;
	}
	//根据任务类型获取任务操作对象详情
	var getCurrentTaskDetailsInfo = function(data){
		var taskType = data[16];
		var operationObject = LANG.UI_JOB_OPT_OBJ;
		switch(taskType){
			case CONF.TASK_TYPE.VOL_CDP_BACKUP:
				operationObject = LANG.UI_JOB_BACKUP_INFO;
				var backupDetail = data[13].backupDetail;
				if(backupDetail.length>0){
					var str = "<tr><td>"+operationObject+"</td><td>";
					for(var i = 0;i<backupDetail.length;i++){
						str +="<span>"+LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_VOL+": "+backupDetail[i].display_name
							+",<span style='margin-left:10px;'>"+LANG.UI_VOL_CDP_JOB_STORAGE+"： </span>"+backupDetail[i].capacity+"</span><br>";
					}
					str +="</td><tr>";
				}
				break;
			case CONF.TASK_TYPE.VOL_CDP_RECOVERY:
				operationObject = LANG.UI_VOL_CDP_JOB_RECOVERY_INFO+"：";
				var recoveryDetail = data[13].recoveryDetail;
				if(recoveryDetail.length>0){
					var str = "<tr><td>"+operationObject+"</td><td>";
					for(var i = 0;i<recoveryDetail.length;i++){
						var recoveryTargetVol = recoveryDetail[i].recovery_target_vol;
						var recoveryTargetDesc = LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_VOL;
						if(recoveryDetail[i].rebuild_partition_flag== CONF.FLAG.SET){
							recoveryTargetDesc = LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TARGET_DISK;
						}
						if(recoveryTargetVol==""){
							recoveryTargetVol = "--";
						}
						str +="<span>"+LANG.UI_VOL_CDP_JOB_DATA_SOURCE+":"+recoveryDetail[i].display_name
							+",<span style='margin-left:5px;'> "+LANG.UI_VISUAL_SIZE+"： </span>"+recoveryDetail[i].capacity
							+",<span style='margin-left:5px;'>"+LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_TIME_POINT+"： </span>"+recoveryDetail[i].recovery_target_timestamp
							+",<span style='margin-left:5px;'>"+recoveryTargetDesc+": </span>"+recoveryTargetVol+"</span><br>";
					}
					str +="</td><tr>";
				}
				break;
			case CONF.TASK_TYPE.VOL_CDP_TAKEOVER:
				operationObject =LANG.UI_VOL_CDP_JOB_TAKEOVER_INFO + "：";
				var takeoverDetail = data[13].takeoverDetail;
				if(takeoverDetail.length>0){
					var str = "<tr><td>"+operationObject+"</td><td>";
					for(var i=0;i<takeoverDetail.length;i++){
						var real_mount_point = takeoverDetail[i].real_mount_point;
						if(!real_mount_point ){
							real_mount_point = "--";
						}
						str +="<span>"+LANG.UI_VOL_CDP_JOB_DATA_SOURCE+":"+takeoverDetail[i].display_name
						+",<span style='margin-left:5px;'>"+LANG.UI_VISUAL_SIZE+": </span>"+takeoverDetail[i].capacity
						+",<span style='margin-left:5px;'>"+LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME_POINT+": </span>"+takeoverDetail[i].takeover_timestamp
						+",<span style='margin-left:5px;'>"+LANG.UI_VOL_CDP_JOB_TARGET_MOUNT+": </span>"+real_mount_point+"</span><br>";
					}
					str +="</td><tr>";
				}
				break;
			default:
				var str = "<tr><td>" +operationObject+ LANG.UI_PUBLIC_NOTHING+"</td></tr>";
		}
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
	
	
	//初始化表格
    var handleRecords = function () {
    	//读取cookie里的保存列表状态
    	var length = "";
    	if($.cookie('pageLengthVolCdp')){
			var pageList = JSON.parse($.cookie('pageLengthVolCdp'));
			if(pageList.vol_cdp){
	    		length = pageList.vol_cdp;
	    	}
		}
    	var updateInterval = 5000;
    	
    	var initGrid = function(){
    		if(0 == $('#current_vol_cdp_job').size()){
        		clearTimeout(timerTask.CurrentVolcdpJobData);
        		return;
        	}
    		var dataTableOpt = {
        			'showLoading':false,
        			'columnDefs' : [{
    	                'orderable': false,
    	                'targets': [7,9,10,11]
        			}],
        			"order": [
        				[4, "desc"]
                    ],
                    'pageLength': parseInt(length)
        	};
    		if(!gridInitFlag){
    			//加载显示页数分页条
    			grid = new Datatable();
        		var data = {m:CONF.M.JOB,f:'getCurrentVolcdpJobs',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#vol_cdp_job_table"), showDetail:true, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(expandDiv == 0 && bootboxConfirm == 0){
    				grid.getRefresh(getParams());
    			}
    		}
    		timerTask.CurrentVolcdpJobData = setTimeout(initGrid, updateInterval);
    	}
    	
    	initGrid();
    	
    	$('#vol_cdp_job_table').on('click', ' tbody td .row-details', function () {
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
		$('#daterangepickerCurrentVolCdp').daterangepicker({
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
		$('#daterangepickerCurrentVolCdp').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
		});

		$('#daterangepickerCurrentVolCdp').on('cancel.daterangepicker', function(ev, picker) {
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
			var nodeSelect = $('#vol_cdp_job_modal #volcdp_node');
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
		var module = 10;
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
			$('#volcdp_tasktype').empty().html(select_html);
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
	CurrentVolCdpJob.init();
});