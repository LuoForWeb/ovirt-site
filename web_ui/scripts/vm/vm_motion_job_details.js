var VMMotionJobDetails = function () {
	
	var logGrid;
	var initLogFlag = false;	//任务日志初始化标志
	var _scrollHeight = 0; //任务日志全局高度
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
	//初始化基本信息
	var initBasicInfo = function(){
		
		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#taskuuid').size()){
        		clearTimeout(timerTask.VMJobDetails_taskRunningInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#taskuuid").val();
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getBasicInfo',p:data}, function(d){setBasicInfo(d, timerTask.VMJobDetails_taskRunningInfo)});
			timerTask.VMJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}
	
	//设置任务图示
	var initMotionImg = function(){
		var imgPath = "./img/vm/instant/";
		var nfsStateOld = ['white.png', 'nfs-state-on-m.gif', 'nfs-state-off-m.png'];	//原机
		var nfsStateNew = ['white.png', 'nfs-state-on-s.gif', 'nfs-state-off-s.png'];	//异机
		var dataStateOld = ['white.png', 'dataline-off-s.png', 'dataline-on-s.gif'];	//原机
		var dataStateNew = ['white.png', 'dataline-off-b.png', 'dataline-on-b.gif',];	//异机
		var hostState = ['host.png', 'host-on.png', 'host-off.png'];
		var vmState = ['vm.png', 'vm-off.png', 'vm-on.png', 'vm-suspend.png', 'vm-suspend.png'];
		var data = {};
		data.taskuuid = $("#taskuuid").val();
		var jsonData = JSON.stringify(data);
		var updateInterval = 5000;
		var update = function(){
			if(0 == $('#motionflag').size()){
        		clearTimeout(timerTask.VMMotionJobDetails_img);
        		return;
        	}
			$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getMotionBaseInfo',p:jsonData}, function(d){
				var info = JSON.parse(d);
				if(1 == info.flag){
					//原机迁移
					$('.oldhostmotion').show();
					$('.newhostmotion').hide();
					
					$('#oldserver').html(info.server_ip);												//灾备中心IP
					$('#oldinstanthostip').html(info.instant_host_ip);									//瞬时恢复主机IP
					$('#oldinstantvmname').html(info.instant_vm_name);									//瞬时恢复虚拟机名
					$('#oldmotionvmname').html(info.motion_vm_name);									//迁移虚拟机名
					$('#oldmotion2nfsimg').attr('src', imgPath + nfsStateOld[info.nfs_state]);			//NFS状态
					$('#oldinstanthost').attr('src', imgPath + hostState[info.instant_host_state]);		//瞬时恢复主机状态
					$('#oldinstantvm').attr('src', imgPath + vmState[info.instant_vm_state]);			//瞬时恢复虚拟机状态
					$('#oldmotionvm').attr('src', imgPath + vmState[info.motion_vm_state]);				//迁移虚拟机状态
					$('#oldmotion2dataimg').attr('src', imgPath + dataStateOld[info.datastore_state]);	//数据传输状态
					
				}else if(2 == info.flag){
					//异机迁移
					$('.oldhostmotion').hide();
					$('.newhostmotion').show();
					
					$('#newserver').html(info.server_ip);												//灾备中心IP
					$('#newinstanthostip').html(info.instant_host_ip);									//瞬时恢复主机IP
					$('#newinstantvmname').html(info.instant_vm_name);									//瞬时恢复虚拟机名
					$('#newmotionvmname').html(info.motion_vm_name);									//迁移虚拟机名
					$('#newmotion2nfsimg').attr('src', imgPath + nfsStateNew[info.nfs_state]);			//NFS状态
					$('#newinstanthost').attr('src', imgPath + hostState[info.instant_host_state]);		//瞬时恢复主机状态
					$('#newinstantvm').attr('src', imgPath + vmState[info.instant_vm_state]);			//瞬时恢复虚拟机状态
					$('#newmotionvm').attr('src', imgPath + vmState[info.motion_vm_state]);				//迁移虚拟机状态
					//如果是停止中状态
					if(info.datastore_state == 0){
						$('#newmotion2dataimg').width(2);
					}
					$('#newmotion2dataimg').attr('src', imgPath + dataStateNew[info.datastore_state]);	//数据传输状态
					
					//下面是不样的两项
					$('#newmotionhostip').html(info.motion_host_ip);									//迁移主机IP
					$('#newmotionhost').attr('src', imgPath + hostState[info.instant_host_state]);		//迁移主机状态
					
				}
				timerTask.VMMotionJobDetails_img = setTimeout(update, updateInterval);
	    	});
		}
		update();
	}
	
	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		data = JSON.parse(data);
		if(7 == data.taskTypeFlag){
			clearTimeout(timeoutID);
			$('#total-progress').css({width: '100%'});
			$('#progressright').html('100%');
			// UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_MOTION_OVER_VALUE);
			setTimeout(function(){
				LOCATION('./content/vm/vm_instant_job_details.php?uuid=' + $('#taskuuid').val(),'task');
			}, 5000);
			return;
		}
		$('#taskName').html(data.taskName);
		$('#moduleType').html(data.moduleType);
		$('#taskType').html(data.taskType);
		$('#user').html(data.user);
		$('#threadNum').html(data.thread_num);
		$('#transferMode').html(data.transportStrategy.mode);
		if(data.transportStrategy.hypervisor == 11 || data.transportStrategy.hypervisor == 12){
			$('.transferModeDiv').hide();
		}else{
			$('.transferModeDiv').show();
		}
		if (CONF.VM_TYPE.SANGFORVVDK == data.transportStrategy.hypervisor) {
			//sangfor scp隐藏传输线程
			$('#threadNum').closest('.form-group').hide();
		} else {
			$('#threadNum').closest('.form-group').show();
		}
		//传输网段根据有没值来显示
		if(data.transport_ip_segment != ''){
			$('#transferNet').html(data.transport_ip_segment);
			$('.transferNetDiv').show();
		}else{
			$('.transferNetDiv').hide();
		}
			
		$('#status').html(data.status);
		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
		$('#speed').html(data.speed);
		$('#progress').html(data.progress);
		
		$('#createTime').html(data.createTime);
		$('#startTime').html(data.startTime);
		$('#endTime').html(data.endTime);
		$('#nextTime').html(data.nextTime);
		$('#timeStrategy').html(getTimeStrategy(data.timeStrategy));
		if(data.reservedStrategy){
			$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
		}else{
			//恢复,隐藏保留策略
			$('.reservedDiv').hide();
		}
		
		
		$('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);
		
	}
	
	//得到时间策略描述信息
	var getTimeStrategy = function(msg){
		var timeStr = '';
		if(!msg){
			return timeStr;
		}
		for(var i=0; i<msg.length; i++){
			var strategy = msg[i];
			var typeDes = getModeDes(strategy.mode);
			var des = typeDes + ": ";
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
	
	var getEachStrategy = function(strategy){
		var desEach = '';
		desEach += strategy.startTime;
		//如果是英文版 需要加空格
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			desEach += " "; //策略开始时间
		}
		desEach += LANG.UI_STRATEGY_START + ", ";
		if(strategy.rollFlag){
			desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
		}else{
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br>";
		return desEach;
	}
	
	//得到保留策略描述信息
	var getReservedStrategy = function(msg){
		var reservedStr = '';
		if(!msg){
			reservedStr = LANG.UI_PUBLIC_NOTHING;
			return reservedStr;
		}
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_NUM;
		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			reservedStr += LANG.UI_STRATEGY_RESERVE_DAY;
		}
		reservedStr += "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value;
		return reservedStr;
	}
    
    var initRow = function(){
		var data = logGrid.getDataTable().data();
		var levelDiv = $('#log').find('tr').find('td:eq(2)');
		for(var i=0; i<levelDiv.length; i++){
			setLevel(levelDiv[i], data[i]);
		}
		if(!initLogFlag){
			//初始化直接滚动到底部
			initLogFlag = true;
		}else{
			//滚动到上次位置
			scroll(_scrollHeight);
		}
		
	}
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[4].level);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[2] + '</span>';
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
	
	//日志表格滚动到.. 并重新设置表格样式
	var scroll = function(scrollHeight){
		$('#log').find(".dataTables_scrollBody").scrollTop(scrollHeight);
		$('#logtable').find('tbody > tr > td').css({border: "0px solid #ddd"});
	}
	
	//设置全局的滚动高度
	var setScrollHeight = function(scrollTop){
		_scrollHeight = scrollTop;
	}
	
    //初始化日志表格
    var initLogGrid = function(){
    	var updateInterval = 5000;
    	var tabShowFlag = false;
    	var initFlag = false;
    	logGrid = new Datatable();
    	
    	var init = function(){
    		var getLiInfo = function(d){
        		var d = JSON.parse(d);
        		var info = "";
        		for(var i=0; i<d.length; i++){
    				info += '<li class="list-group-item__log"><div class="col1"><div class="cont contdetail"><div class="cont-col1">' +
    				getIcon(d[i][3].level) + '</div><div class="cont-col2"><div class="desc">' + d[i][2] + '</div>' + 
    				'</div></div></div><div class="col2 logtimecol"><div class="date">' + d[i][0] + '</div></div></li>';
        		}
        		if(0 == d.length){
        			info = '<li><div class="col textalignc">' + LANG.UI_TOOLS_NO_DATA + '</div></li>';
        		}
        		$('#runninglog').html(info);
        	}
        	
        	//得到任务ICON CSS
			var getIcon = function(level){
				if(1 == level){
					//文件
					icon = '<div class="label label-success" style="background-color: transparent"><i class="viconfont vicon-wancheng1"></i></div>';
				}else if(3 == level){
					icon = '<div class="label label-danger" style="background-color: transparent"><i class="viconfont vicon-cuowu"></i></div>';
				}else{
					icon = '<div class="label label-warning" style="background-color: transparent"><i class="viconfont vicon-yichang"></i></div>';
				}
				return icon;
			}
    		
    		var getlog = function(){
    			var data = {};
    			data.uuid = $("#taskuuid").val();
    			var jsonData = JSON.stringify(data);
    			$.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getRunningJobLog',p:jsonData}, function(d){
    				if(0 == $('#runninglog').size()){
                		clearTimeout(timerTask.VMJobDetails_logGrid);
                		return;
                	}
            		getLiInfo(d);
    	    	})
    	    	.complete(function() {timerTask.VMJobDetails_logGrid = setTimeout(getlog, updateInterval);});
    		}
    		getlog(); //得到日志
    	}
    	init();
    }
    //初始化历史任务表格
    var initHistoryGrid = function(){
    	var updateInterval = 10000;
    	var initFlag = false;
    	var grid = new Datatable();
    	var init = function(){
    		if(0 == $('#taskuuid').size()){
        		clearTimeout(timerTask.VMJobDetails_historyGrid);
        		return;
        	}
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#taskuuid").val();
    			data = {m:CONF.M.JOB,f:'getDetailsHistory',p:data};
    			grid.setAjaxParam(data);
    	    	grid.init({src: $("#historytable")});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.VMJobDetails_historyGrid = setTimeout(init, updateInterval);
    	}
    	init();
    }
    
    var vmGridLoad = function(){
		if(detailsInfo){
			var openTr = $('#vms').find('tbody > tr')[detailsIndex];
			$(openTr).after(detailsInfo);
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
    }
    //初始化虚拟机表格
    var initVMGrid = function(){
    	var updateInterval = 10000;
    	var initFlag = false;
    	var grid = new Datatable();
    	var init = function(){
    		if(0 == $('#taskuuid').size() ){
        		clearTimeout(timerTask.VMJobDetails_vmGrid);
        		return;
        	}
    		if(!initFlag){

    	    	var data = {};
    			data.uuid = $("#taskuuid").val();
    			data = {m:CONF.M.JOB,f:'getDetailsVM',p:data};
    			grid.setAjaxParam(data);
    			grid.init({src: $("#vmstable"), showDetail:true, onDataLoad:vmGridLoad, dataTable:{"paging":false,"info":false}});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.VMJobDetails_vmGrid = setTimeout(init, updateInterval);
    	}
    	init();
    	$('#vmstable').on('click', ' tbody td .row-details', function () {
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
            	$('#vms').find('tr .details').parent().remove();
            	$('#vms').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('#vms').find('tbody tr .details').parents('tr')[0];
            }
            return;
        });
    	
    	//添加详情信息 
    	var addDetails = function(nTr, data){
    		if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="12">';
        	sOut += '<table>';
            sOut += getVMDetails(data[11]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}

    	var getVMDetails = function(data){
    		if(!data) return;
             if(8 == data.type){
    			//恢复
    			return getRecoveryVMDetails(data);
    		}
    	}
    	
    	//备份虚拟机详情
    	var getBackupVMDetails = function(data){
    		var details = "<tr><td>" + LANG.UI_VCENTER_VM_PATH + ": " + data.sPath + "</td>";
    		details += "</tr>";
    		return details;
    	}
    	
    	//恢复虚拟机详情
    	var getRecoveryVMDetails = function(data){
    		var details = "<tr><td>" + LANG.UI_MOTION_TIMEPOINT + ":</td><td>";
    		details += data.taskname + " => " + data.timepoint;
    		details += "</td></tr>";
    		
    		details += "<tr><td>" + LANG.UI_MOTION_OLD_VM_SRC + ":</td>";
    		details += "<td>" + data.path + "</td>";
    		details += "</tr>";
    		
    		details += "<tr><td>" + LANG.UI_MOTION_GOAL + ":</td>";
    		details += "<td>" + LANG.UI_VCENTER_VCENTER + ": " + data.dVcenterIP ;
    		details += " " + LANG.UI_VCENTER_HOST + ": " + data.dHostIP ;
    		details += " " + LANG.UI_VCENTER_VM + ": " + data.dName + "</td>";
    		details += "</tr>";
    		
//    		details += "<tr><td>" + LANG.UI_MOTION_TO_STORAGE + ":</td><td>";
//    		details += data.storage;
//    		details += "</td></tr>";
    		return details;
    	}
    	
    }

    return {
        //main function to initiate the module
        init: function () {
        	initBasicInfo();
        	initMotionImg();
        	initLogGrid();
            initVMGrid();
        }

    };

}();

jQuery(document).ready(function() {    
	VMMotionJobDetails.init();
});