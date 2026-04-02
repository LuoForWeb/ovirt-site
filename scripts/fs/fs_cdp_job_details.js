var FSCDPJobDetails = function () {
	
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var _animation = null, _dataChange = 0;
	var _taskStatus, _taskType, _basicInfo;
	
	//初始化基本信息
	var initBasicInfo = function(){
		var data = {};
		data.uuid = $("#task_uuid").val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getBackupBasicInfo',p:data}, function(d){setBasicInfo(d)});
	}
	
	//设置基本信息
	var setBasicInfo = function(data){
		data = JSON.parse(data);
		_basicInfo = data;
		
		//设置图表主机IP
		$('#producthostname').html(data.productHost);
		$('#standbyhostname').html(data.standbyHost);
		
		//设置概要信息
		$('#taskName').html(data.taskName);
		$('#taskType').html(data.taskTypeDes);
		_taskType = data.taskType;
		
		var backupTypeDes = "";
		if(0 == data.backupType){
			backupTypeDes = "实时同步";
			$('.hisdiv').show();		//显示历史目录和份数
		}else if(1 == data.backupType){
			backupTypeDes = "实时同步+历史数据";
			$('.hisdiv').hide();
		}
		$('#backupType').html(backupTypeDes);
		
		//设置高级配置
		var highInfo = data.highInfo;
		$('#multithreading').html(getSwtchDes(highInfo.general.multithreading));
		$('#mirrorimage').html(getSwtchDes(highInfo.general.mirrorimage));
		$('#emptydir').html(getSwtchDes(highInfo.general.emptydir));
		
		$('#strategy').html(getStrategyDes(highInfo.strategy));
		$('#timefilter').html(getTimeFilterDes(highInfo.filter));
		$('#namefilter').html(getSwtchDes(highInfo.filter.namefilter));
		$('#ransomwaretype').html(getRansomwareDes(highInfo.ransomware));
		
		
		
		//设置历史数据
		var history = data.highInfo.history;
		$('#historyDir').html(data.historyDir);
		$('#historytime').html(history.historytime + "天");
		$('#historydel').html(history.historydel + "个");
		$('#historymod').html(history.historymod + "个");
		$('#historytimeinterval').html(history.historytimeinterval + "秒");
		
	}
	
	//得到防勒索描述
	var getRansomwareDes = function(d){
		var des = "";
		if("0" == d.ransomwaretype){
			des = getSwtchDes(false);
		}else{
			des = getSwtchDes(true);
		}
		return des;
	}
	
	//得到时间过滤描述
	var getTimeFilterDes = function(d){
		var des = "";
		if(d.timefilter){
			if("1" == d.timefiltertype){
				des = "按天过滤";
			}else if("2" == d.timefiltertype){
				des = "按月过滤";
			}else if("3" == d.timefiltertype){
				des = "按年过滤";
			}
			des += "," + "过滤值" + ":" + d.timefiltervalue;
		}else{
			des = getSwtchDes(false);
		}
		return des;
	}
	
	//得到时间策略描述
	var getStrategyDes = function(d){
		var des = "";
		if("0" == d.strategytype){
			des = "全天同步";
		}else{
			des = "指定时间段同步" + ":" + d.starttime + " - " + d.endtime;
		}
		return des;
	}
	
	
	var getSwtchDes = function(flag){
		if(flag){
			return '<span class="label label-sm label-success" >' + "开启" + '</span>';
		}else{
			return '<span class="label label-sm label-default" >' + "关闭" + '</span>'
		}
	}
	
	
	//设置运行状态
	var setRunningInfo = function(data){
		var data = JSON.parse(data);
		if(!data.re) return false;
		//设置主机图片 设置备机图片 生产数据库图片
		var productPNG = "./img/db/task/product-host.png";
		var standbyPNG = "./img/db/task/standby-host.png";
		var productdbPNG = "./img/db/task/fs-running.gif";
		var standbydbPNG = "./img/db/task/fs-waiting.png";
		if(1 == data.productHostSatus){
//			productPNG = "./img/db/task/product-host-warning.gif";
//			productdbPNG = "./img/db/task/db-waiting.png";
		}else if(2 == data.productHostSatus){
			productPNG = "./img/db/task/product-host-error.gif";
			productdbPNG = "./img/db/task/fs-waiting.png";
		}
		if(1 == data.standbyHostSatus){
//			standbyPNG = "./img/db/task/standby-host-warning.gif";
		}else if(2 == data.standbyHostSatus){
			standbyPNG = "./img/db/task/standby-host-error.gif";
		}
		
		
		$('#producthost').attr('src', productPNG);
		$('#standbyhost').attr('src', standbyPNG);
		$('#productdb').attr('src', productdbPNG);
		$('#standbydb').attr('src', standbydbPNG);
		
		if(data.syncFlag){
			$('.datapng').show();
			_animation.play();
		}else{
			$('.datapng').hide();
			_animation.pause();
		}
		
		
		if(_taskStatus != data.taskStatus){
			$('#status').html('<span class="label label-sm ' + getStatusLevelClass(data.taskStatus) + '" >' + data.taskStatusDes + '</span>');
			_taskStatus = data.taskStatus;
		}
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);
		
		
		setBtnStatus();
	}
	
	//初始化运行详情
	var initRunningInfo = function(){
		var updateInterval = 3000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.CDPJobDetails_RunningInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getBackupRunningInfo',p:data}, function(d){setRunningInfo(d)});
			timerTask.CDPJobDetails_RunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}
	
	//得到状态的显示类型
	var getStatusLevelClass = function(level){
		var levelClass = '';
		switch(level){
			case 1:
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
	
	
	
	
	//初始化日志表格
    var initLogGrid = function(){
    	var updateInterval = 5000;
    	var getLiInfo = function(d){
    		var info = "";
    		for(var i=0; i<d.length; i++){
				info += '<li><div class="col1"><div class="cont contdetail"><div class="cont-col1">' + 
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
        		icon = '<div class="label label-sm label-success"><i class="fa fa-check"></i></div>';
        	}else if(3 == level){
        		icon = '<div class="label label-sm label-danger"><i class="fa fa-times"></i></div>';
        	}else{
        		icon = '<div class="label label-sm label-warning"><i class="fa fa-exclamation"></i></div>';
        	}
        	return icon;
        }
		
		var getlog = function(){
			var data = {};
			data.uuid = $("#task_uuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'getBackupRunningLog',p:jsonData}, function(d){
				if(0 == $('#runninglog').size()){
            		clearTimeout(timerTask.CDPJobDetails_logGrid);
            		return;
            	}
				var d = JSON.parse(d);
        		getLiInfo(d.log);
        		setFileInfo(d.file);
	    	})
	    	.complete(function() {timerTask.CDPJobDetails_logGrid = setTimeout(getlog, updateInterval);});
		}
		getlog();
    }
    
    //设置文件信息
    var setFileInfo = function(d){
    	if(0 == d.length){
    		//没有获取到数据
//    		$('#fileinfos').hide();
//    		setFirstTabActive('runul', 'tabcontentrun');
    		return true;
    	}
    	$('#producthostName').html(d.producthostName);
    	$('#standbyhostName').html(d.standbyhostName);
    	$('#totalSize').html(d.totalSize);
    	$('#dirTotal').html(d.dirTotal);
    	$('#fileTotal').html(d.fileTotal);
    	$('#finishTotal').html(d.finishTotal);
    	$('#successTotal').html(d.successTotal);
    	$('#failureTotal').html(d.failureTotal);
    	$('#backupDir').html(d.backupDir);
    	$('#lastFinishTime').html(d.lastFinishTime);
    	$('#currentFile').html(d.currentFile);
    	
//    	$('#fileinfos').show();
    	
    	var filelistStr = "";
    	$.each(d.filelist, function(i, v){
    		filelistStr += v.path + "<br>";
    	});
    	$('#filelist').html(filelistStr);
    }
    
    //设置第一个tab为active状态
	var setFirstTabActive = function(ulid, tabcontentdiv){
		//移除所有tab的active状态,移除所有tabcontentdiv的active状态
		$('#' + ulid + ">li.active").removeClass("active");
		$('#' + tabcontentdiv).children('.active').removeClass("active");
		
		//给第一个tab加active状态,给第一个tabcontentdiv添加active状态
		$($('#' + ulid).children("li").get(0)).addClass("active");
		$($('#' + tabcontentdiv).children(".tab-pane").get(0)).addClass("active");
	}
    
    
    var dataScroll = function(num){
    	if(!_animation){
    		var datapng = '<img class="datapng" src="./img/db/task/data.png" style="margin-top: 3px;position: absolute;">';
        	$("#dataline").before(datapng);
        	_animation = anime({
    			targets: '.datapng',
    			translateX: 350,
    			loop: true,
    			autoplay: false,
    			easing: 'easeInOutSine',
    			complete: function(anim) {
    				$('.datapng').remove();
    			}
    		});
        	$('.datapng').hide();
    	}
    }
    
    //根据任务状态设置按钮权限
    var setBtnStatus = function(){
    	switch(_taskStatus){
	    	case 2:
	    	case 7:
	    		//运行中,异常,禁用启动
	    		setControlBtn('start', false);
	    		setControlBtn('stop', true);
	    		break;
	    	case 4:
	    		//停止,禁用停止
	    		setControlBtn('start', true);
	    		setControlBtn('stop', false);
				$('#stop').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a>');
	    		break;
			case 5:
				setControlBtn('start', false);
				setControlBtn('stop', true);
				//停止中状态，变为强制停止
				$('#stop').html('<a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_FORCE_STOP + '</a>');
				break;
    		default:
    			//其他状态,开启控制
    			setControlBtn('start', true);
    			setControlBtn('stop', true);
    			break;
    	}
    }
    
    //设置按钮是否可用
    var setControlBtn = function(id, available){
    	if(available){
    		$("#" + id).find('a').removeClass('disablebtn');
    	}else{
    		$("#" + id).find('a').addClass('disablebtn');
    	}
    }
    
    //得到任务主机信息
    var getHostInfo = function(){
    	var info = {};
    	info.productip = _basicInfo.productip;
    	info.standbyip = _basicInfo.standbyip;
    	info.productuuid = _basicInfo.productuuid;
    	info.standbyuuid = _basicInfo.standbyuuid;
    	return info;
    }
    
    //事件监听
	var initListener = function(){
		$('#start').on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, fscdp: getHostInfo()};
			params = JSON.stringify(params);
			Metronic.blockUI({target: '#tab_1_1',animate: true, cenrerY: true,});
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startJob',p:params}, function(data){
	    		Metronic.unblockUI('#tab_1_1');
	    		if(OPREL(data)){
	    		}
	    	});
		});
		
		$('#stop').on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, fscdp: getHostInfo()};
			params = JSON.stringify(params);
			Metronic.blockUI({target: '#tab_1_1',animate: true, cenrerY: true,});
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'stopJob',p:params}, function(data){
	    		Metronic.unblockUI('#tab_1_1');
	    		if(OPREL(data)){
	    			//fix bug,停止任务后,计数还有,会造成再次同步的时候没有小球
	    			_dataChange = 0;
	    		}
	    	});
		});
		
		
		$('#downloadplog').on('click', function(){
			downloadHostLog(_basicInfo.productuuid)
		});
		$('#downloadslog').on('click', function(){
			downloadHostLog(_basicInfo.standbyuuid)
		});
		
		$('.nowscan').off().on('click', nowscan);
	}
	
	//立即扫描
	var nowscan = function(){
		var taskname = $(this).data('taskname');
		var params = {productip: _basicInfo.productip, standbyip:_basicInfo.standbyip, taskname:taskname};
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.FILECDP,f:'nowScanDir',p:params}, function(d){
			OPREL(d);
		});
	}
	
	//下载主机日志
	var downloadHostLog = function(hostuuid){
		var params = {hostuuid: hostuuid};
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'checkHostLog',p:params}, function(d){
			var data = JSON.parse(d);
			//失败提示,成功直接下载,不提示
			if(!data.re){
				OPREL(d);
			}else{
				window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.DBCDP + '&f=getHostLogFile&p=' + params;
			}
		});
	}
	
	
    return {
        init: function () {
        	dataScroll();
        	initBasicInfo();
            initLogGrid();
            initRunningInfo();
            initListener();
        }

    };

}();

jQuery(document).ready(function() {    
	FSCDPJobDetails.init();
});
