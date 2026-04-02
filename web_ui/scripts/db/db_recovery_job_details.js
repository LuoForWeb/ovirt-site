var DBRecoveryJobDetails = function () {

	var initChartFlag = false; //任务曲线图初始化标志
	var myChart, option, data = [], nowTime =[];
	var _taskInfo;
	var _taskStatus, _taskType, _basicInfo;

	var initInfo = function(){
		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.DBRecoveryJobDetails_all);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
			data.taskinfo = _taskInfo;
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getRecoveryAllInfo',p:data}, function(d){
            	var data = JSON.parse(d);
            	_taskInfo = data.jobSpeed;
            	setBasicInfo(data.basicInfo);
            	setSpeed(data.jobSpeed);
            	initLogGrid(data.log);
            })
            .complete(function() {
            	timerTask.DBRecoveryJobDetails_all = setTimeout(init, updateInterval);
            });
		}
		init();
    }

	//初始化基本信息
	var initBasicInfo = function(){
		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.DBRecoveryJobDetails_basicInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.DBCDP,f:'getRecoveryBasicInfo',p:data}, function(d){setBasicInfo(d, timerTask.DBRecoveryJobDetails_basicInfo)});
			timerTask.DBRecoveryJobDetails_basicInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		if(!data.flag){
			clearTimeout(timeoutID);
			$('#total-progress').css({width: '100%'});
			$('#progressright').html('100%');
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function(){
				LOCATION('./content/platform/jobs/jobs.php','task');
			}, 5000);
		}

		_taskStatus = data.status;
		_taskType = data.taskType;
		_basicInfo = data;

		$('#taskName').html(data.taskName);
		$('#taskType').html(data.taskTypeDes);
//		$('#status').html(data.statusDes);
		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);

		$('#standbyHost').html(data.standbyHost);
		$('#productHost').html(data.productHost);
		$('#vendor').html(data.vendor);
		$('#instance').html(data.instance);
		$('#database').html(data.databases);
		$('#timepoint').html(data.timepoint);
		if(data.status){
			$('#status').html('<span class="label label-sm ' + getStatusLevelClass(data.status) + '" >' + data.statusDes + '</span>');
		}

		$('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);

		setBtnStatus();
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


	//初始化流量
    var initSpeedConf = function () {
    	// 基于准备好的dom，初始化echarts实例
        myChart = echarts.init(document.getElementById('speedchart'));
        // 指定图表的配置项和数据
        option = {
        	    tooltip : {
        	        trigger: 'axis',
        	        formatter: function (params, ticket, callback) {
        	        	var value = params[0].data;
        	        	if(value >= 1024){
        	        		//显示保留两位小数
        	        		return Math.round(value / 1024 * 100) / 100 + " MB/s";
        	        	}else{
        	        		return value + " KB/s";
        	        	}
        	        }
        	    },
        	    grid: {
        	    	top: 15,
        	        left: 5,
        	        right: 5,
        	        bottom: 5,
        	        containLabel: true,
        	        show: false,
        	        borderWidth: 0,
        	    },
        	    xAxis :
    	        {
    	            type : 'category',
    	            boundaryGap : false,
    	            splitLine: {
    	                show: false
    	            },
    	            axisLabel : {
    	            	show:true,
    	                interval: 12
    	            },
    	            data:[]
    	        },
        	    yAxis :
    	        {
    	            type : 'value',
    	            splitLine: {
    	                show: true
    	            },
    	            axisLabel : {
    	                formatter: function(value, index){
    	                	if(value >= 1024){
    	                		return Math.round(value * 10 / 1024) / 10 + " MB/s";
    	                	}else{
    	                		return value + "KB/s";
    	                	}
    	                }
    	            },
    	        },
        	    series : [
        	        {
        	            name:'net',
        	            type:'line',
        	            stack: 'total',
        	            showSymbol: false,
        	            hoverAnimation: false,
        	            smoothMonotone: 'x',
        	            animation: false,
        	            smooth: true,
        	            areaStyle: {normal: {
        	            	color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? '#2A87C8':'#44b6ae',
        	            	opacity: 0.2
        	            }},
        	            data:[]
        	        },
        	    ],
				color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? ['#2A87C8']:['#44b6ae']
        	};

        window.onresize = function(){
        	myChart.resize();
        }
    }
    var parseNum = function(num){
    	num = parseInt(num);
    	num = num >= 10 ? num : "0" + num;
    	return num;
    }

    var getShowTime = function(timeStamp){
    	var myDate = new Date(parseInt(timeStamp));
		var date = myDate.toLocaleDateString();
		var hours = myDate.getHours();
		var minutes = myDate.getMinutes();
		var seconds = myDate.getSeconds();
		return parseNum(hours) + ":" + parseNum(minutes) + ":" + parseNum(seconds);
    }

    //初始化任务进度曲线图
    var initTaskSpeed = function(d){

    	if(initChartFlag) return; //初始化了就直接返回

    	var serverTime = d.t * 1000;
    	for (var i = 100; i > 0; i--) {
            data.push(0);
            nowTime.push(getShowTime(serverTime -  i * 3000));
        }
    	option.series[0].data = data
    	option.xAxis.data = nowTime;
    	myChart.setOption(option);

    	initChartFlag = true;
    }

    var setSpeed = function(dataNow){
    	initTaskSpeed(dataNow);
        data.shift();
        data.push(dataNow.speed);
        option.series[0].data = data;

        nowTime.shift();
        nowTime.push(dataNow.nowTime);
        option.xAxis.data = nowTime;
        myChart.setOption(option);
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


    //初始化日志表格
	var initLogGrid = function(d){
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
					'</div>' +
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

    //根据任务状态设置按钮权限
    var setBtnStatus = function(){
    	switch(_taskStatus){
	    	case 2:
	    		//运行中,禁用启动
	    		setControlBtn('start', false);
	    		setControlBtn('stop', true);
	    		break;
	    	case 4:
	    	case 13:
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
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, dbcdp: getHostInfo()};
			params = JSON.stringify(params);
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startJob',p:params}, function(data){
	    		if(OPREL(data)){
	    		}
	    	});
		});

		$('#stop').on('click', function(){
			if($(this).find('a').hasClass('disablebtn')){
				return true;
			}
			var params = {uuid:$('#task_uuid').val(), taskType: _taskType, dbcdp: getHostInfo()};
			params = JSON.stringify(params);
	    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'stopJob',p:params}, function(data){
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
        //main function to initiate the module
        init: function () {
        	initListener();
        	initSpeedConf();
        	initInfo();
        }

    };

}();

jQuery(document).ready(function() {
	DBRecoveryJobDetails.init();
});
