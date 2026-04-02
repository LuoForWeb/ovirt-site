var DbCDPJobDetails = function () {
	
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
	var _animation = null, _dataChange = 0;
	var _taskStatus, _taskType, _basicInfo;
	var grid;
	
	//初始化基本信息
	var initBasicInfo = function(){
		var data = {};
		data.uuid = $("#task_uuid").val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getBackupBasicInfo',p:data}, function(d){setBasicInfo(d)});
	}
	
	//设置基本信息
	var setBasicInfo = function(data){
		data = JSON.parse(data);
		_basicInfo = data;
		//设置概要信息
		$('#taskName').html(data.taskName);
		$('#taskType').html(data.taskTypeDes);
		_taskType = data.taskType;
		$('#vendor').html(data.vendor);
		
		var backupTypeDes = "";
		if(0 == data.backupType){
			backupTypeDes = "实时备份";
			$('.hisdiv').show();		//显示历史目录和份数
			$('#takeovertab').hide();	//隐藏接管tab
		}else if(2 == data.backupType){
			backupTypeDes = "业务接管";
			$('.hisdiv').hide();
			$('#takeovertab').show();
		}else if(3 == data.backupType){
			backupTypeDes = "实时备份+业务接管";
			$('.hisdiv').show();
			$('#takeovertab').show();
		}
		$('#backupType').html(backupTypeDes);
		
		//设置高级配置
		$('#productHost').html(data.productHost);
		$('#standbyHost').html(data.standbyHost);
		$('#backupDir').html(data.backupDir);
		$('#historyDir').html(data.historyDir);
		$('#historyCopys').html(data.historyCopys);
		$('#logSetting').html(getLogSettingDes(data.logSetting));
		
		
		
		//设置图表主机IP
		$('#producthostname').html(data.productHost);
		$('#standbyhostname').html(data.standbyHost);
		
		
		setTakeoverSetting(data);
		
	}
	
	//设置接管配置
	var setTakeoverSetting = function(data){
		var takeover = data.takeoverSetting;
		$('#takeoverSetting').html(getTakeoverSettingDes(data.takeoverSetting));
		if(!takeover.check){
			$('.stakeover').hide();
			return;
		}
//		if("0" == takeover.type){
//			var typeDes = "手动接管";
//		}else if("1" == takeover.type){
//			var typeDes = "自动接管";
//		}
//		$('#takeovertype').html(typeDes);
		
		//接管服务
		var serviceHtml = "";
		for(var i=0; i<takeover.service.length; i++){
			serviceHtml += takeover.service[i] + "<br>";
		}
		$('#takeoverService').html(serviceHtml);
		
		//接管网卡
		var takeovercard = [];
		var standbyNetwork = takeover.network.standbyNetwork;
		var productNetwork = takeover.network.productNetwork;
		var cardDes = "";
		
		for(var i=0; i<standbyNetwork.length; i++){
			if(standbyNetwork[i].takeover){
				takeovercard.push(standbyNetwork[i].takeover);
			}
		}
		
		for(var i=0; i<takeovercard.length; i++){
			for(var j=0; j<productNetwork.length; j++){
				if(takeovercard[i] == productNetwork[j].adaptername){
					cardDes += productNetwork[j].ipaddress + "<br>";
				}
			}
		}
		$('#takeoverCard').html(cardDes);
	}
	
	//设置运行状态
	var setRunningInfo = function(data){
		var data = JSON.parse(data);
		if(!data.re) return false;
		//设置主机图片 设置备机图片 生产数据库图片
		var productPNG = "./img/db/task/product-host.png";
		var standbyPNG = "./img/db/task/standby-host.png";
		var productdbPNG = "./img/db/task/db-running.gif";
		var standbydbPNG = "./img/db/task/db-waiting.png";
		if(1 == data.productHostSatus){
			productPNG = "./img/db/task/product-host-warning.gif";
			productdbPNG = "./img/db/task/db-waiting.png";
		}else if(2 == data.productHostSatus){
			productPNG = "./img/db/task/product-host-error.gif";
			productdbPNG = "./img/db/task/db-waiting.png";
		}
		if(1 == data.standbyHostSatus){
			standbyPNG = "./img/db/task/standby-host-warning.gif";
		}else if(2 == data.standbyHostSatus){
			standbyPNG = "./img/db/task/standby-host-error.gif";
		}
		
		//如果接管中,修改图片
		if(14 == data.taskStatus){
			standbyPNG = "./img/db/task/standby-host.png";
			standbydbPNG = "./img/db/task/db-running.gif";
		}
		
		$('#producthost').attr('src', productPNG);
		$('#standbyhost').attr('src', standbyPNG);
		$('#productdb').attr('src', productdbPNG);
		$('#standbydb').attr('src', standbydbPNG);
		
		if(data.dataChange > _dataChange){
			$('.datapng').show();
			_animation.play();
			_dataChange = data.dataChange;
		}else{
			$('.datapng').hide();
			_animation.pause();
		}
		
		if(data.syncFlag){
			//如果在同步,显示动画
			$('.datapng').show();
			_animation.play();
		}
		
		if(_taskStatus != data.taskStatus){
			$('#status').html('<span class="label label-sm ' + getStatusLevelClass(data.taskStatus) + '" >' + data.taskStatusDes + '</span>');
			_taskStatus = data.taskStatus;
		}
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);
		
		//设置接管模式
		if(0 == data.takeoverMode){
			$('#takeovertype').html('手动接管');
		}else if(1 == data.takeoverMode){
			$('#takeovertype').html('自动接管');
		}
		
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
			$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getBackupRunningInfo',p:data}, function(d){setRunningInfo(d)});
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
	
	//得到接管配置描述
	var getTakeoverSettingDes = function(takeoverSetting){
		var takeovershow = "";
		if(takeoverSetting.check){
			takeovershow = "开启" + ", " + takeoverSetting.step + "次失败后,启动接管";
		}else{
			takeovershow = "关闭";
		}
		return takeovershow;
	}
	
	var getLogUnits = function(value){
		var value = parseInt(value);
		var unit = "";
		switch(value){
			case 0:
				unit = "MB";
				break;
			case 1:
				unit = "GB";
				break;
			case 2:
				unit = "TB";
				break;
		}
		return unit;
	}
	
	//得到日志保留配置描述
	var getLogSettingDes = function(logSetting){
		var logshow = "";
		var logmode = parseInt(logSetting.logmode);
		switch(logmode){
			case 0:
				logshow = "按恢复条数保留日志," + " 恢复条数:" +  logSetting.maxstep;
				break;
			case 1:
				logshow = "按容量大小保留日志," + " 日志容量: " +  logSetting.maxsize + " " + getLogUnits(logSetting.sizeunits);
				break;
			case 2:
				logshow = "按容量大小保留日志," + " 日志容量: 自适应";
				break;
			case 3:
				logshow = "按时间保留日志," + " 保留时间: " + logSetting.maxhour + " 小时";
				break;
		}
		return logshow;
	}
	
	//初始化日志表格
    var initLogGrid = function(){
    	var updateInterval = 5000;
    	var getLiInfo = function(d){
    		var d = JSON.parse(d);
    		var info = "";
    		for(var i=0; i<d.length; i++){
				info +=
				'<li class="list-group-item__log">'+
					'<div class="col1">'+
						'<div class="cont contdetail">'+
							'<div class="cont-col1">' + getIcon(d[i][3].level) + '</div>'+
							'<div class="cont-col2">'+
								'<div class="desc">' + d[i][2] + '</div>' + 
							'</div>'+
						'</div>'+
					'</div>'+
					'<div class="col2 logtimecol">'+
						'<div class="date">' + d[i][0] + '</div>'+
					'</div>'+
				'</li>';
    		}
    		if(0 == d.length){
    			info = '<li><div class="col textalignc">' + LANG.UI_TOOLS_NO_DATA + '</div></li>';
    		}
    		$('#runninglog').html(info);
    	}
    	
    	//得到任务ICON CSS
        var getIcon = function(level){
        	if(1 == level){
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
			data.uuid = $("#task_uuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getBackupRunningLog',p:jsonData}, function(d){
				if(0 == $('#runninglog').size()){
            		clearTimeout(timerTask.CDPJobDetails_logGrid);
            		return;
            	}
        		getLiInfo(d);
	    	})
	    	.complete(function() {timerTask.CDPJobDetails_logGrid = setTimeout(getlog, updateInterval);});
		}
		getlog();
    }
    
    //数据库状态
	var dbStatus = function(div, data){
		var thisClass = "label-info";
		//大于0是正常状态,6是停止.
		if(0 <= data[10].bkStatus){
			if(6 == data[10].bkStatus){
				thisClass = "label-default";
			}else{
				thisClass = "label-success";
			}
		}else{
			thisClass = "label-danger";
		}
		var statusDes = '<span class="label label-sm ' + thisClass + '">' + data[9] + '</span>';
		$(div).html(statusDes);
	}
    
    var dbGridLoad = function(){
		if(detailsInfo){
			var openTr = $('#database').find('tbody > tr')[detailsIndex];
			$(openTr).after(detailsInfo);
			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
		}
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var statusDiv = $('#databasetable tbody > tr').find('td:eq(10)');
		for(var i=0; i<statusDiv.length; i++){
			dbStatus(statusDiv[i], data[i]);
		}
    }
    
    //初始化数据库列表
    var initDBGrid = function(){
    	var updateInterval = 10000;
    	var initFlag = false;
    	grid = new Datatable();
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 1, 2, 3, 4, 5, 6]
    			}],
                "paging":false,
                "info":false
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() ){
        		clearTimeout(timerTask.DBJobDetails_dbGrid);
        		return;
        	}
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#task_uuid").val();
    			data = {m:CONF.M.DBCDP,f:'getDetailsDatabase',p:data};
    			grid.setAjaxParam(data);
    			grid.init({src: $("#databasetable"), showDetail:true, onDataLoad:dbGridLoad, dataTable:dataTableOpt});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.DBJobDetails_dbGrid = setTimeout(init, updateInterval);
    	}
    	init();
    	$('#databasetable').on('click', ' tbody td .row-details', function () {
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
            	$('#database').find('tr .details').parent().remove();
            	$('#database').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndex = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfo = $('#database').find('tbody tr .details').parents('tr')[0];
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
        	if(2 == data[10].dbtype){
        		sOut += '<tr><td style="min-width:150px">数据库配置信息:</td>';
                sOut += '<td><div style="border: 1px solid #ddd;max-height: 100px;overflow-y: auto;padding:10px;">' + getDBConfigHtml(data[10].dbconfig) + '</div></td></tr>';
        	}
            sOut += '<tr><td style="min-width:150px">生产主机关联文件:</td>';
            sOut += '<td><div style="border: 1px solid #ddd;max-height: 100px;overflow-y: auto;padding:10px;">' + getFileListHtml(data[10].productFileList) + '</div></td></tr>';
            sOut += '<tr><td style="min-width:150px">备份主机关联文件:</td>';
            sOut += '<td><div style="border: 1px solid #ddd;max-height: 100px;overflow-y: auto;padding:10px;">' + getFileListHtml(data[10].standbyFileList) + '</div></td></tr>';
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}
    	
    	//得到文件列表
    	var getFileListHtml = function(fileList){
    		var fileListHtml = "";
    		for(var i=0; i<fileList.length; i++){
    			fileListHtml += fileList[i] + "<br>";
    		}
    		return fileListHtml;
    	}
    	
    	//得到数据库配置信息,暂时是Oracle用
    	var getDBConfigHtml = function(config){
    		var configHtml = "";
    		configHtml += "版本号: " + config.version + "<br>";
    		configHtml += "SID: " + config.SID + "<br>";
    		configHtml += "安装路径: " + config.hoemdir + "<br>";
    		configHtml += "memory_target: " + config.memory_target + "<br>";
    		configHtml += "pga_aggregate: " + config.pga_aggregate + "<br>";
    		configHtml += "sga_max_size: " + config.sga_max_size + "<br>";
    		configHtml += "sga_target: " + config.sga_target + "<br>";
    		return configHtml;
    	}

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
    	setControlBtn('takeover', true);
    	setControlBtn('stoptakeover', false);
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
				$('#stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button>');
	    		break;
			case 5:
				setControlBtn('start', false);
				setControlBtn('stop', true);
				//停止中状态，变为强制停止
				$('#stop').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
				break;
	    	case 14:
	    		//接管
	    		setControlBtn('start', false);
    			setControlBtn('stop', false);
    			setControlBtn('takeover', false);
    			setControlBtn('stoptakeover', true);
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
    	if (available){
            $("." + id).find('.btn').prop('disabled', false);
        } else {
            $("." + id).find('.btn').prop('disabled', true);
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
			if($(this).find('.btn').prop('disabled')){
                return true;
            }
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, dbcdp: getHostInfo()};
			params = JSON.stringify(params);
			Metronic.blockUI({target: '#tab_1_1',animate: true, cenrerY: true,});
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startJob',p:params}, function(data){
	    		Metronic.unblockUI('#tab_1_1');
	    		if(OPREL(data)){
	    		}
	    	});
		});
		
		$('#stop').on('click', function(){
			if($(this).find('.btn').prop('disabled')){
                return true;
            }
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, dbcdp: getHostInfo()};
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
		
		$('#takeover').on('click', function(){
			if($(this).find('.btn').prop('disabled')){
                return true;
            }
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, dbcdp: getHostInfo()};
			params = JSON.stringify(params);
			Metronic.blockUI({target: '#tab_1_1',animate: true, cenrerY: true,});
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'takeover',p:params}, function(data){
	    		Metronic.unblockUI('#tab_1_1');
	    		if(OPREL(data)){
	    		}
	    	});
		});
		
		$('#stoptakeover').on('click', function(){
			if($(this).find('.btn').prop('disabled')){
                return true;
            }
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, dbcdp: getHostInfo()};
			params = JSON.stringify(params);
			Metronic.blockUI({target: '#tab_1_1',animate: true, cenrerY: true,});
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'stopTakeover',p:params}, function(data){
	    		Metronic.unblockUI('#tab_1_1');
	    		if(OPREL(data)){
	    		}
	    	});
		});
		
		$('#downloadplog').on('click', function(){
			downloadHostLog(_basicInfo.productuuid)
		});
		$('#downloadslog').on('click', function(){
			downloadHostLog(_basicInfo.standbyuuid)
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
            initDBGrid();
            initLogGrid();
            initRunningInfo();
            initListener();
        }

    };

}();

jQuery(document).ready(function() {    
	DbCDPJobDetails.init();
});
