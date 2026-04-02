var CBRJobDetails = function () {

	var logGrid;
	var initLogFlag = false;	//任务日志初始化标志
	var _scrollHeight = 0; //任务日志全局高度
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndexLog = 0,detailsInfoLog = null;

	var initChartFlag = false; //任务曲线图初始化标志

	var vmSelect = []; //虚拟机选择

	var _taskStatus; //监控任务状态

	var jobParams = {}; //操作需要参数

	var recoveryConfig = {};

	var initVMGridFlag = false;

	var authFun = [];

	var syncType  =0;//同步粒度


	var myChart;
	//控制虚拟机详情的全局变量  详情插入到第几条后
	var detailsIndexVm = 0;
	//初始化基本信息
	var initBasicInfo = function(){
		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#task_uuid').size()){
        		clearTimeout(timerTask.CBRJobDetails_taskRunningInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
			data = JSON.stringify(data);
			 $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getBasicInfo',p:data}, function(d){setBasicInfo(d, timerTask.CBRJobDetails_taskRunningInfo)});
			timerTask.CBRJobDetails_taskRunningInfo = setTimeout(init, updateInterval);
		}
		init();
	}

	//设置基本信息
	var setBasicInfo = function(data, timeoutID){
		data = JSON.parse(data);

		if(!data.flag){
			clearTimeout(timeoutID);
			$('#total-progress').css({width: '100%'});
			$('#progressright').html('100%');
			UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_OVER_VALUE);
			setTimeout(function(){
				LOCATION('./content/platform/jobs/jobs.php');
			}, 5000);
		}
		//基本信息
		$('#taskName').html(data.taskName);
		$('#taskType').html(data.taskType);
		if(data.status){
			$('#status').html('<span class="label  ' + getStatusLevelClass(data.statusValue) + '" >' + data.status + '</span>');
		}
		//数据完整性校验
		if(data.verify){
			$('#verify').html('<span class="label  ' + getVerifyClass(data.verifyValue) + '" >' + data.verify + '</span>');
		}
		//保存获取到的任务参数
		_taskStatus = data.statusValue;
		jobParams.status = data.statusValue;
		jobParams.taskType = data.taskTypeFlag;
		jobParams.subModule = data.hypervisor;
		$('#totalSize').html(data.totalSize);
		$('#currentSize').html(data.currentSize);
		$('#startTime').html(data.startTime);
		$('#intervalTime').html(data.intervalTime);
		$('#endTime').html(data.endTime);
		//存储信息
		if(data.storageInfo.flag){
			var node = data.storageInfo.node;
			var storage = data.storageInfo.storage;
			var storageInfo = '';
			if(!storage){
				//没有存储信息,自动选择存储
				storageInfo = LANG.UI_JOB_AUTO_SELECT_STORAGE;
			}else{
				storageInfo = storage.name + "(" + storage.type + ")<br>";
				if(!storage.quotaFlag){
					storageInfo += LANG.UI_JOB_TOTAL_SIZE + ":" + storage.size + ", " +
					LANG.UI_JOB_FREE_SIZE + ":" + storage.freesize;
				}else{
					storageInfo += storage.quotades;
				}
			}

			$('#nodeinfo').html(node.name + "<br>" + node.ip);
			$('#storageinfo').html(storageInfo);
			//如果存储类型是不是华为CBR 则显示重删 压缩 加密
			if(storage.typenum != 12){
				$(".deduplicationdiv").show();
				$(".compresseddiv").show();
				$(".encryptStoragediv").show();
				//如果数据加密关闭隐藏自动生成密码开关描述显示
				if(!data.storageInfo.high.encrypt_flag){

					$('.passwordAutodiv').hide();
				}
				$('#deduplication').html(getFlagLevelInfo(data.storageInfo.high.deduplication));
				$('#compressed').html(getFlagLevelInfo(data.storageInfo.high.compressed));
				// 压缩等级
				if(data.storageInfo.high.compressed){
					var method = '';
					switch(data.storageInfo.high.compress_method) {
						case 1:
							method = LANG.UI_JOB_COMPRESS_PRIORITY_FASTER;
							break;
						case 2:
							method = LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL;
							break;
						case 3:
							method = LANG.UI_JOB_COMPRESS_PRIORITY_BETTER;
							break;
						case 4:
							method = LANG.UI_JOB_COMPRESS_PRIORITY_BEST;
							break;
					}
					$('#compressMethod').html(method);
				}else{
					$('.compressMethodDiv').hide();
				}
				$('#encryptStorage').html(getFlagLevelInfo(data.storageInfo.high.encrypt_flag));
				// 存储加密算法
				if(data.storageInfo.high.encrypt_flag){
					let method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
					if(data.storageInfo.high.encrypt_method == 2){
						method = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
					}
					$('.encrypt-method-div').show();
					$('#encryptMethod').html(method);
				}else{
					$('.encrypt-method-div').hide();
				}
				$('#passwordAuto').html(getFlagLevelInfo(data.storageInfo.high.password_auto_flag));
			}

		}
		//高级
		$('#threadNum').html(data.thread_num);
		$('#speedlimit').html(data.speed_limit.value);
		$('#speedlimit').prop('title', data.speed_limit.des);
		//策略
		$('#createTime').html(data.createTime);
		$('#nextTime').html(data.nextTime);
		var timeStrategy = getTimeStrategy(data.timeStrategy);
		$('#strategydes').html(timeStrategy.full); //按策略同步
		$('#reservedStrategy').html(getReservedStrategy(data.reservedStrategy));
		//传输策略,XenServer显示加密传输,VMware显示传输模式
		$("#transportMode").html(data.transportStrategy.mode);
		//$("#transportEncrypt").html(getFlagLevelInfo(data.transportStrategy.encrypt));
		// // 传输加密算法
		// if(data.transportStrategy.encrypt){
		// 	$('.transfer-encrypt-method-div').show();
		// 	let encryptMethod = data.transportStrategy.encrypt_method;
		// 	let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
		// 	if(encryptMethod == 2){
		// 		method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
		// 	}
		// 	$('#transferEncryptMethod').html(method);
		// }else{
		// 	$('.transfer-encrypt-method-div').hide();
		// }
		$('.taskOperateDiv').show();
		$('#total-progress').css({width: data.totalprogress});
		$('#progressright').html(data.progress);
		//初始化操作按钮
		setBtnStatus();
		if(!initVMGridFlag){
			//初始化虚拟机列表
			initVMGrid();
		}
	}

	//获取该任务同步粒度 设置虚拟机表头 设置历史任务表头
	var  initVmHead =  function(){
		var  data = {};
		data.uuid = $("#task_uuid").val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getCBRSyncType',p:data}, function(d){
			var data = JSON.parse(d);
			syncType  = data.synctype
			//按存储库 同步
			if(syncType == 1){
				$('#vmname').html(LANG.UI_SYNC_CBR_STORAGE_NAME);
			}else if (syncType == 2){
				//按资源同步
				$('#vmname').html(LANG.UI_SYNC_CBR_RESOURCE_NAME);
			}else if (syncType == 3){
				$('#vmname').html(LANG.UI_SYNC_CBR_TIMEPOINT_NAME);
			}
			$('#vmsize').html(LANG.UI_SYNC_CBR_TASK_TOTAL_SIZE);
				//设置历史任务表头
			$('#hisvmsize').html(LANG.UI_SYNC_CBR_TASK_TOTAL_SIZE);
		});

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
	//得到数据完整性校验的显示类型
	var getVerifyClass  = function(level){
		var levelClass = '';
		switch(level){
			case 1:
				levelClass = "label-success";
				break;
			case 2:
				levelClass = "label-info";
				break;
			default:
				levelClass = "label-info";
				break;
		}
		return levelClass;
	}


	//得到时间策略描述信息
	var getTimeStrategy = function(msg){
		var timeInfo = {full:LANG.UI_PUBLIC_NOTHING, inc:LANG.UI_PUBLIC_NOTHING, diff:LANG.UI_PUBLIC_NOTHING, pincr:LANG.UI_PUBLIC_NOTHING};
		if(!msg){
			return timeInfo;
		}
		for(var i=0; i<msg.length; i++){
			var strategy = msg[i];
			var des = "";
			if(CONF.STRATEGY_TYPE.DAY == strategy.type){
				des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
			}else if(CONF.STRATEGY_TYPE.WEEK == strategy.type){
				des += getStrategyFrequency(strategy);
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
				}else{
					des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
				}
			}else if(CONF.STRATEGY_TYPE.MONTH == strategy.type){
				des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
			}else if(CONF.STRATEGY_TYPE.GLOBAL == strategy.type){
				des += strategy.startTime;
			}else{
				des += LANG.UI_PUBLIC_NOTHING;
			}

			if(1 == strategy.mode){
				timeInfo.full = des;
			}else if(2 == strategy.mode){
				timeInfo.inc = des;
			}else if(3 == strategy.mode){
				timeInfo.diff = des;
			}else if(9 == strategy.mode){
				timeInfo.pincr = des;
			}
		}
		return timeInfo;
	}

	//得到备份间隔描述
	var getStrategyFrequency = function(strategy){
		var frequency = "";
		var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
		for(var i=1;i<=52;i++){
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
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
			}else{
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + strategy.endTime + LANG.UI_STRATEGY_END;
			}

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
		// 保留类型
		if(CONF.RESERVE_STRATEGY_MODE.POINT == msg.strategyMode){
			reservedStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '<br>';
		}else if(CONF.RESERVE_STRATEGY_MODE.CHIAN == msg.strategyMode){
			reservedStr += LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '<br>';
		}
		if(CONF.RESERVE_TYPE.NUM == msg.type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				reservedStr += LANG.UI_STRATEGY_RESERVE_NUM + "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value + LANG.UI_STRATEGY_RESERVE_NUM_VALUE;
			}else{
				reservedStr += msg.value + LANG.UI_STRATEGY_RESERVE_NUM_EN;
			}

		}else if(CONF.RESERVE_TYPE.DAY == msg.type){
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				reservedStr += LANG.UI_STRATEGY_RESERVE_DAY + "," + LANG.UI_STRATEGY_RESERVE_VALUE + msg.value + LANG.UI_STRATEGY_RESERVE_DAY_VALUE;
			}else{
				reservedStr += msg.value + LANG.UI_STRATEGY_RESERVE_DAY_EN;
			}
		}
		return reservedStr;
	}

	var getSpeedDes = function(value){
		if(value >= 1024){
			return Math.round(value * 100 / 1024) / 100 + " MB/s";
    	}else{
    		return value + "KB/s";
    	}
	}
	//初始化流量
    var initSpeed = function () {
		// 根据不同分辨率动态计算echart的高度和宽度
		var chartWidth = $('.portlet-charts__body__speedchart').width();
		var chartHeight = $('.portlet-charts__body__speedchart').height();
		$('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
    	// 基于准备好的dom，初始化echarts实例
        myChart = echarts.init(document.getElementById('speedchart'));
        // 指定图表的配置项和数据
        var option = {
        	    tooltip : {
        	        trigger: 'axis',
        	        formatter: function (params, ticket, callback) {
        	        	var value = params[0].data;
        	        	if(value >= 1024){
        	        		return Math.round(value * 100 / 1024) / 100 + " MB/s";
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
			xAxis:
				{
					type: 'category',
					boundaryGap: false,
					splitNumber: 6,
					splitLine: {
						show: false
					},
					axisLabel: {
						show: true,
						interval: 12,
						color: '#86909C'
					},
					axisLine: {
						show: true,
						lineStyle: {
							color: '#C9CDD4',
						}
					},
					data: []
				},
			yAxis :
				{
					type : 'value',
					splitLine: {
						show: true,
						lineStyle: {
							type: 'dashed',
						},
					},
					axisLine: {
						show: false,
					},
					axisTick: {
						show: false // Hide y-axis ticks
					},
					axisLabel : {
						formatter: function(value, index){
							//向上取整显示纵坐标
							if(value >= 1024){
								return Math.ceil(value / 1024)+ "MB/s";
							}else{
                                if(value < 1){
                                    return value + "KB/s";
                                }else{
                                    return Math.ceil(value)+ "KB/s";
                                }
							}
						},
						color: '#86909C',
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
							color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? '#2A87C8':new echarts.graphic.LinearGradient(0, 0, 0, 1, [
								{
									offset: 0,
									color: 'rgba(59, 179, 70, 0.2)'
								},
								{
									offset: 1,
									color: 'rgba(59, 179, 70,0)'
								}
							]),
							opacity: CONF.VENDOR == CONF.VENDOR_LIST.gmp ? 0.2 : 1,
						}},
					data:[]
				},
			],
			color: CONF.VENDOR == CONF.VENDOR_LIST.gmp? ['#2A87C8']:['#44b6ae']
		};

        var updateInterval = 2000;
        var data = [], nowTime =[];

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
        var p = {};
    	p.uuid = $("#task_uuid").val();
    	p = JSON.stringify(p);
        function update(){
        	if(0 == $('#speedchart').size()){
        		clearTimeout(timerTask.CBRJobDetails_speed);
        		return;
        	}
        	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'getTaskSpeed',p:p}, function(d){
            	var dataNow = JSON.parse(d);
            	initTaskSpeed(dataNow);
                data.shift();
                data.push(dataNow.speed);
                option.series[0].data = data;

                nowTime.shift();
                nowTime.push(dataNow.nowTime);
                option.xAxis.data = nowTime;


            	myChart.setOption(option);
            })
            .complete(function() {timerTask.CBRJobDetails_speed = setTimeout(update, updateInterval);});
        }
        update();

        window.onresize = function(){
        	myChart.resize();
        }

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
	//得到开启和关闭的HTML内容
	var getFlagLevelInfo = function(flag){
		var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
		if(!flag){
			html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
		}
		return html;
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
    	var getLiInfo = function(d){
    		var d = JSON.parse(d);
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
        		//文件
        		icon = '<div class="label label-success"><i class="fa fa-check"></i></div>';
        	}else if(3 == level){
        		icon = '<div class="label  label-danger"><i class="fa fa-times"></i></div>';
        	}else{
        		icon = '<div class="label  label-warning"><i class="fa fa-exclamation"></i></div>';
        	}
        	return icon;
        }

		var getlog = function(){
			var data = {};
			data.uuid = $("#task_uuid").val();
			var jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getVMRunningJobLog',p:jsonData}, function(d){
				if(0 == $('#runninglog').size()){
            		clearTimeout(timerTask.CBRJobDetails_logGrid);
            		return;
            	}
        		getLiInfo(d);
	    	})
	    	.complete(function() {timerTask.CBRJobDetails_logGrid = setTimeout(getlog, updateInterval);});
		}
		getlog();
    }


    //初始化历史任务表格
    var initHistoryGrid = function(){
    	var updateInterval = 10000;
    	var tabShowFlag = false;
    	var initFlag = false;
    	var grid = new Datatable();
    	var initHistoryRow = function(){
        	var data = grid.getDataTable().data();

        	var levelDiv = $('#historytable').find('tbody > tr');
        	var levelTrueDiv = [];
        	//这里先过滤掉.details
        	for(var i=0; i<levelDiv.length; i++){
        		if(!$(levelDiv[i]).hasClass('details'));
        		levelTrueDiv.push($(levelDiv[i]).find('td:eq(3)'));
        	}
//    		var levelDiv = $('#historytable').find('tbody > tr').find('td:eq(3)');
    		for(var i=0; i<levelTrueDiv.length; i++){
    			setLevel(levelTrueDiv[i], data[i]);
    		}
    		$(".popovers").popover();
    		if(detailsInfoLog){
    			var tr = $('#historytable').find('tbody > tr');
    			var data = grid.getDataTable().data();
            	addDetails(tr[detailsIndexLog], data[detailsIndexLog]);
    			var openTr = $('#historytable').find('tbody > tr')[detailsIndexLog];
    			$(openTr).find('.row-details-close').addClass("row-details-open").removeClass("row-details-close");
    		}
        }

    	var setLevel = function(div, data){
    		if(!data) return;
    		var labelClass = getLevelClass(data[9].level);
    		var content = '<span class="label ' + labelClass + '">' +
    			'<a class="popovers colorwhite" data-container="body" data-trigger="hover" data-placement="right" data-content="' +
    			data[9].popover + '" >'+ data[2] + '</a></span>';
    		$(div).html(content);
    	}
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 4]
    			}],
    			"order": [
                    [7, "desc"]
                ],
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() || !tabShowFlag){
        		clearTimeout(timerTask.CBRJobDetails_historyGrid);
        		return;
        	}
    		if(!initFlag){
    	    	var data = {};
    			data.uuid = $("#task_uuid").val();
    			data = {m:CONF.M.JOB,f:'getDetailsHistory',p:data};
    			grid.setAjaxParam(data);
    	    	grid.init({src: $("#historytable"), showDetail:true, dataTable:dataTableOpt, onDataLoad:initHistoryRow});
    	    	initFlag = true;
    		}else{
    			grid.getRefresh({});
    		}
    		timerTask.CBRJobDetails_historyGrid = setTimeout(init, updateInterval);
    	}

    	$('#historytable').on('click', ' tbody td .row-details', function () {
        	var data = grid.getDataTable().data();

            var nTr = $(this).parents('tr')[0];
            if($(this).hasClass('row-details-open')){
            	//如果是展开的
            	//收起所有展开项
            	$(this).addClass("row-details-close").removeClass("row-details-open");
            	$(this).parent().parent().next().remove();
            	detailsInfoLog = null;
            }else{
            	//如果是收起的
            	$('#historytable').find('tr .details').parent().remove();
            	$('#historytable').find('.row-details-open').addClass("row-details-close").removeClass("row-details-open");
            	$(this).addClass("row-details-open").removeClass("row-details-close");
            	var row = $(this).parent().parent().prevAll().length;
            	addDetails(nTr, data[row]);
            	detailsIndexLog = $(this).parents('tr')[0].rowIndex - 1;
            	detailsInfoLog = $('#historytable').find('tbody tr .details').parents('tr')[0];
            }

            return;
        });

    	//添加详情信息
    	var addDetails = function(nTr, data){
    		if(!data){
    			return;
    		}
        	var sOut = '<tr class="details"><td class="details" colspan="13">';
        	sOut += '<table>';
            sOut += getVMDetailsInfo(data[10]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}
    	//得到虚拟机详情
    	var getVMDetailsInfo = function(data){
    		if(!data.info){
    			return LANG.UI_PUBLIC_NOTHING;
    		}
			var getCBRSyncDetails = function(data){
    			var thead = '<thead><tr role="row" class="heading">';
				//根据粒度不同  detail字段详细信息不同
				if(syncType == 1){
					thead += '<th width="35%">' + LANG.UI_SYNC_CBR_STORAGE_PATH + '</th>';
				}else if(syncType == 2){
					thead += '<th width="35%">' + LANG.UI_SYNC_CBR_RESOURCE_PATH + '</th>';
				}else if(syncType == 3){
					thead += '<th width="35%">' + LANG.UI_SYNC_CBR_TIMEPOINT_PATH + '</th>';
				}
        		thead += '<th width="20%">' + LANG.UI_SYNC_CBR_TIMEPOINT_NUM + '</th>';
        		thead += '<th width="45%">' +LANG.UI_SYNC_CBR_TIMEPOINT_LIST + '</th>';
        		thead += '</tr></thead>';

        		var tbody = '<tbody>';
				var vm_details = data.info;
				for(var i=0; i<vm_details.length; i++){
					var timepoints = "";
					if(vm_details[i].count == 0 && vm_details[i].backups ==  null){
						vm_details[i].count = "--";
						timepoints = "--";
					}else{
						var backuplist = [];
						for (let j = 0; j < vm_details[i].backups.length; j++) {
							var  backupname = vm_details[i].backups[j].backup_name;
							  backuplist.push(backupname);
						}
						 timepoints = backuplist.join('\n');
						timepoints = '<textarea disabled>' + timepoints + '</textarea>'
					}
					if(i%2 == 0){
						tbody += '<tr role="row" class="odd" >';
					}else{
						tbody += '<tr role="row" class="even" >';
					}
					tbody += '<td>' + vm_details[i].path + '</td>';
					tbody += '<td>' +  vm_details[i].count + '</td>';
					tbody += '<td>' + timepoints + '</td>';
					tbody += '</tr>';


				}
				tbody += '<tbody>';
        		var details = thead + tbody;
        		return details;
    		}
			return getCBRSyncDetails(data);
    	}

    	$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
  		  e.target // newly activated tab
  		  e.relatedTarget // previous active tab
  		  if("#history" == e.target.hash){
  			  tabShowFlag = true;
  			  init();
  		  }
  		})
  		$('a[data-toggle="tab"]').on('hide.bs.tab', function (e) {
  			e.target // newly activated tab
  			e.relatedTarget // previous active tab
  			if("#history" == e.target.hash){
  				tabShowFlag = false;
  			}
  		})
    }


    //初始化虚拟机表格
    var initVMGrid = function(){
    	var updateInterval = 15000;
    	var initFlag = false;
    	var grid = new Datatable();
		var vmGridLoad = function(){
			if(detailsInfo){
				var tr = $('#vms').find('tbody > tr');
				var data = grid.getDataTable().data();
				addDetails(tr[detailsIndex], data[detailsIndex]);
			}
		}
    	var dataTableOpt = {
    			'columnDefs' : [recoveryConfig],
    			"ordering": false,
                "paging":false,
                "info":false
    	};
    	var init = function(){
    		if(0 == $('#task_uuid').size() ){
        		clearTimeout(timerTask.CBRJobDetails_vmGrid);
        		return;
        	}
			var data = {};
			data.uuid = $("#task_uuid").val();
    		if(!initFlag){
    			data = {m:CONF.M.JOB,f:'getDetailsVM',p:data};
    			grid.setAjaxParam(data);
    			grid.init({src: $("#vmstable"), showDetail:true, onDataLoad:vmGridLoad, dataTable:dataTableOpt});
    	    	initFlag = true;
    		}else{
    			var select = grid.getSelectedRows();
    			vmSelect = select;
    			var saveCheck = function(){
    				for(var i=0;i<vmSelect.length;i++){
						var inputstr =escapeJquery(vmSelect[i]);
    					$("input[name=id"+escapeJquery(vmSelect[i])+"]").prop('checked', true);
        				$("input[name=id"+escapeJquery(vmSelect[i])+"]").parent().addClass('checked');
        			}

    			}
				grid.getRefresh(data,saveCheck,false);
    		}
    		timerTask.CBRJobDetails_vmGrid = setTimeout(init, updateInterval);
    	}
    	init();
    	initVMGridFlag = true;
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
        	var sOut = '<tr class="details"><td class="details" colspan="13">';
        	sOut += '<table>';
            sOut += getVMDetails(data[12]);
            sOut += '</table></td></tr>';
    		$(nTr).after(sOut);
    	}
		$('#start').unbind().on('click', function(){
    		if($(this).find('.btn').prop('disabled')){
                return true;
            }
    		startVmJob(1);
    	});
		var startVmJob = function(mode){
    		var select = grid.getSelectedRows();
    		if(!select.length){
    			return UIToastr.showInfo(LANG.UI_JOB_START_VM_BACKUP_JOB, LANG.UI_JOB_START_VM_BACKUP_JOB_TIPS);
    		}
    		var datatable = grid.getDataTable().data();
    		var data = {};
    		data.vmuuids = select;
    		data.taskuuid = $("#task_uuid").val();
    		data.vcenteruuid = [];
    		for(var i=0;i<select.length;i++){
    			data.vcenteruuid.push(datatable[i][13]);
    		}
    		data.hypervisor =  datatable[0][14];
    		data.mode = mode;
    		var p = JSON.stringify(data);
    		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startVMJob',p: p}, function(d){
    			if(OPREL(d)){
        			grid.getRefresh({});
        		}
    		});
    	}
    	var getVMDetails = function(data){
			var timepoints  ="";
			var timecount  = "";
			var backuplist = [];
			if(data.backups ==  null){
				timepoints = "--";
				timecount = "--";
			}else{
				for (let i = 0; i < data.backups.length; i++) {
					var  backupname = data.backups[i].backup_name;
					backuplist.push(backupname);
				}
				timepoints = backuplist.join('\n');
				timecount  = data.count;
			}
			// var allpath = data[10].join('\n');
			//修改名字
			var path = "";
			if(syncType == 1){
				path = LANG.UI_SYNC_CBR_STORAGE_PATH;
			}else if (syncType == 2){
				path  = LANG.UI_SYNC_CBR_RESOURCE_PATH;
			}else if (syncType == 3){
				path  = LANG.UI_SYNC_CBR_TIMEPOINT_PATH;
			}
			var details = '<thead><th style="width: 35%;">'+path+'</th><th style="width: 20%;">'+LANG.UI_SYNC_CBR_TIMEPOINT_NUM+'</th><th style="width: 45%;">'+LANG.UI_SYNC_CBR_TIMEPOINT_LIST+'</th></thead><tbody><tr><td>'+ data.path +'</td><td>'+ timecount +'</td><td><textarea style="outline: none;" cols="60" rows="3" disabled>' + timepoints + '</textarea></td></tr></tbody>';
    		return details;
    	}


    	var startVmJob = function(mode){
    		var select = grid.getSelectedRows();
    		if(!select.length){
				return UIToastr.showInfo(LANG.UI_SYNC_CBR_SELECT_SYNC_OBJECT,LANG.UI_SYNC_CBR_SELECT_SYNC_OBJECT_TIPS);

    		}
    		var datatable = grid.getDataTable().data();
    		var data = {};
    		data.vmuuids = select;
    		data.taskuuid = $("#task_uuid").val();
    		data.vcenteruuid = [];
    		for(var i=0;i<select.length;i++){
    			data.vcenteruuid.push(datatable[i][13]);
    		}
    		data.hypervisor =  datatable[0][14];
    		data.mode = mode;
    		var p = JSON.stringify(data);
    		$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:'startVMJob',p: p}, function(d){
    			if(OPREL(d)){
        			grid.getRefresh({});
        		}
    		});
    	}


    	var deleteVms = function(){
        	var select = grid.getSelectedRows();
        	if(!select.length){
    			return UIToastr.showInfo(LANG.UI_JOB_DELETE_SELECT_VM, LANG.UI_JOB_DELETE_NO_SELECT_VM_TIPS);
    		}
        	bootbox.confirm({
                title: LANG.UI_JOB_DELETE_SELECT_VM,
                message: LANG.UI_JOB_DELETE_SELECT_VM_CONFIRM,
                callback: function(r) {
                    if(!r) return;
                    submitDelete(select, grid);
                }
            });
        }

        var submitDelete = function(select, grid){
        	var data = {};
        	data.vmuuids = select;
        	data.taskuuid = $('#task_uuid').val();
        	var p = JSON.stringify(data);

        	$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:'deleteSelectVms', p: p}, function(d){
        		if(OPREL(d)){
        			grid.getRefresh({});
        		}
        	});
        }


    }
	//根据任务状态设置按钮权限
    var setBtnStatus = function(){
		//跟虚拟机一样
    	setControlBtn('starttask', true);
		setControlBtn('start', true);
		// UNKNOWN:0,         		//未知的任务状态
		// WAITTING:1,       		//任务等待运行
		// RUNNING:2,         		//任务正在运行
		// PAUSED:3,          		//任务暂停
		// STOPPED:4,         		//任务停止
		// STOPPING:5,        		//任务停止中
		// NETWORK_FAULT:6,   		//网络故障
		// ABNORMAL:7,        		//任务已完成但异常
		// ERROR:8,           		//错误
		// SYNC:9,					//任务同步
		// PREPARING:10,			//准备中
		// PAUSING:11,	    		//任务暂停中
		// STARTING:12,        	    //启动中
		// FINISHED:13,       		//已完成
		// TAKEOVER:14,       		//接管
		// TAKEOVER_STARTING:15,   //启动接管
		// TAKEOVER_STOPPING:16,   //停止接管
		// SUCCESSED:17,			//任务成功
    	switch(_taskStatus){
	    	case 2:
	    	case 5:
	    	case 7:
	    	case 10:
			case 12:
	    		//运行和准备中停止中启动中,禁用运行
	    		setControlBtn('starttask', false);
	    		setControlBtn('stoptask', true);
				if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
					//停止中状态，变为强制停止
					if (CONF.TASK_STATUS.STOPPING == _taskStatus) {
						$('#stoptask').html('<button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_FORCE_STOP + '</button>');
					}
				}
	    		break;
	    	case 4:
	    		//停止,禁用停止
	    		setControlBtn('stoptask', false);
	    		break;
    		default:
    			//其他状态,开启停止
    			setControlBtn('stoptask', true);
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

    var initListener = function(){
    	$('#stoptask').unbind().on('click', stopJob);   //终止任务
    	$('#starttask').unbind().on('click', startJob); //启动任务
    }

    //停止
	var stopJob = function(){
		if($(this).find('.btn').prop('disabled')){
			return true;
		}
		opJob('stopJob');
	}

	var opJob = function(funName){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = 2;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
	}

    //启动完全
	var startJob = function(){
		if($(this).find('.btn').prop('disabled')){
			return true;
		}
		startJobUnify('startJob', 1);
	}
	//启动任务
	var startJobUnify = function(funName, type){
		var data = {};
		data = jobParams;
		data.uuid = $('#task_uuid').val();
		data.module = 2;
		data.startType = type;
		data.taskType = 51;
		params = JSON.stringify(data);
		Metronic.blockUI({target: '#jobDetail',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:funName,p:params}, function(d){
    		Metronic.unblockUI('#jobDetail');
    		if(OPREL(d)){
    		}
    	});
	}
	var escapeJquery = function(srcString){
        // 转义之后的结果
        var escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
                "]", "|", "{", "}"];
        // jquery中的特殊字符,不是正则表达式中的特殊字符
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
                ":", ";", "<", ">", ",", "/"];
        for (var i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\"
                                    + jsSpecialChars[i], "g"), "\\"
                            + jsSpecialChars[i]);
        }
        for (var i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                            "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }
	var watchEchartSizeChange = function() {
		window.onresize = function() {
			var chartWidth = $('.portlet-charts__body__speedchart').width();
			var chartHeight = $('.portlet-charts__body__speedchart').height();
			$('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
			myChart.resize();
		}

		 //  监听左侧菜单导航伸缩/展开触发的echart-resize事件
        $(window).on('echart-resize', function () {
            // 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.3s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
            setTimeout(() => {
                let chartWidth = $('.portlet-charts__body__speedchart').width();
                let chartHeight = $('.portlet-charts__body__speedchart').height();

                $('#speedchart').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
                myChart.resize();
            }, 300);
        });
	}

    return {
        //main function to initiate the module
        init: function () {
        	initBasicInfo();
			initVmHead(); //初始化虚拟机表头
        	initSpeed();
            initLogGrid();
            initHistoryGrid();
            initListener();
			watchEchartSizeChange();
        }

    };

}();

jQuery(document).ready(function() {
	CBRJobDetails.init();
});
