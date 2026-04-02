var System = function(){
	//时间选择器全局变量,方便提交搜索的时候直接使用
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	var intervalFlag = false;  //对整个页面而言,用于控制是否进行动态刷新数据 false表示进行动态刷新 true表示静态展示
	var ALARMVALUE; //告警值
	var myChart1,myChart2,myChart3,myChart4,myChart5,myChart6; //系统监控实例化
	var myChart1_details,myChart2_details,myChart3_details,myChart4_details,myChart5_details,myChart6_details; //系统监控实例化详情
	var NEWDATA; //存放最新的6个可视化的数据
	var INITCHARTS = false; //控制绘制可视化
	let CURRENT_TAB_ID = 'system_monitor_tab'; // 当前选中的tab
	
	var initListener = function(){
		$("#setAlarm").on('click',function(){
			$('#setAlarmModal').modal({"width": "600px", "height": "400px"});
		})
		$("#time_range").on('change',function(){
			var time_range = $("#time_range").val();
			//当切换时间时,如果切换成其他则表示静态 如果为空则表示动态
			if(time_range != ""){
				intervalFlag = true;
			}else{
				intervalFlag = false;
			}
			//如果选择自定义则出现时间选择栏;
			if(time_range == "self_time"){
				//清空时间选择
				$("#daterangepicker").val("");
				$(".daterangepickerdiv").show();
				return;
			}else{
				$(".daterangepickerdiv").hide();
			}
			INITCHARTS = true;
			initData();
		});
		$("#node_uuid").on('change',function(){
			INITCHARTS = true;
			initData();
		});
		$("#msg_node_uuid").on('change',function(){
			getBasicMsg();
		});
		//切换标签页时再初始化echarts  避免因为窗口变化导致不显示的问题
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			CURRENT_TAB_ID = e.target.id;
			if(e.target.id =="system_monitor_tab"){
				let chartWidth = $('.card-echart').width();
				let chartHeight = $('.card-echart').height();

				// 切换tab之前导航栏有可能已经伸缩/展开，所以需要重新获取宽高
				$('#chart_cpu').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
				$('#chart_ram').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
				$('#chart_load').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

				$('#chart_network').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
				$('#chart_bps').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
				$('#chart_iops').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

				myChart1.resize();
				myChart2.resize();
				myChart3.resize();
				myChart4.resize();
				myChart5.resize();
				myChart6.resize();
			}
      })
		//设置告警是否启用的切换
		$('#alarm_flag').on("switchChange.bootstrapSwitch",function(){
			//得到是否开启告警
            var enable_flag = $('#alarm_flag').get(0).checked;
            if(enable_flag){
            	$("#noClickDiv").css("pointer-events","auto");
            	$("#noClickDiv").css("opacity","1");
            }else{
            	$("#noClickDiv").css("pointer-events","none");
            	$("#noClickDiv").css("opacity","0.5");
            }
        });
		
		//设置告警
		$("#alarm_submit").on('click',function(){
			submitAlarm();
		})
		
		//取消告警
		$("#cancel_alarm").on('click',function(){
			setAlarmVal(ALARMVALUE);
		})
		$("#closeSetAlarm").on('click',function(){
			setAlarmVal(ALARMVALUE);
		});
		$("#downloadLogBtn").on('click', downloadLog);
		// 下载系统日志
		$('#downloadPackage').on('click', downloadPackage);

		// 日志节点切换
		$('#nodeSelect').on('change', function () {
			$('#downloadLogTable').bootstrapTable('refresh');
		});
		
		
	}
	//初始化模态下载系统日志
	let downloadLog = function () {
		let option = {
			pageList: [25, 50, 100],
			vin_url: '/api/v1/logs/system/get_download_system_log',
			vin_method: 'POST',
			vin_params: function () {
				let params = {};
				params.node_uuid = $('#nodeSelect').val();
				return params;
			},
			sortName: 'pack_name',
			sortOrder: 'desc',

			columns: [
				{
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'pack_name',
					title: LANG.UI_LOG_GET_DOWNLOAD_LOG_LIST_PACK_NAME,
				},
				{
					field: 'pack_size',
					title: LANG.UI_LOG_GET_DOWNLOAD_LOG_LIST_PACK_SIZE,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'last_modify_time',
					title: LANG.UI_LOG_GET_DOWNLOAD_LOG_LIST_LAST_MODIFY_TIME,
					sortable: true, //默认可排序，禁用排序才写此项
				}
			]
		}
		$('#downloadLogTable').baseTableConfig().init(option);

		$('#downloadLogModal').modal({ 'width': '700px', 'height': '450px' });
		$('#downloadLogModal').on('shown.bs.modal', function () {
			$('#downloadLogTable').bootstrapTable('refresh');
		});
	}

	let downloadPackage = function () {
		let select = $('#downloadLogTable').bootstrapTable('getSelections');
		if (!select.length) {
			return UIToastr.showInfo(LANG.UI_LOG_DOWNLOAD_SELECT, LANG.UI_LOG_DOWNLOAD_SELECT_TIPS);
		}
		let data = {};
		data.node_uuid = $('#nodeSelect').val();
		data.pack_list = select.map(function (item) { return item.pack_name });
		data.id = select.map(function (item) { return item.id })
		Metronic.blockUI({ target: '#downloadLogModal', animate: true });
		pAjaxRequest(data, '/api/v1/logs/system/download_system_log', 'POST', (res) => {
			Metronic.unblockUI('#downloadLogModal');
			if (!res.success) {
				return operateResponseList(res);
			}
			window.location.href = res.data.info;
		});
	}

	//初始化节点选择
	let initNodeSelect = function () {
		pAjaxRequest({
			'offset': 0,
			'limit': 500
		}, '/api/v1/nodes', 'GET', function (d) {
			if (d.data.rows.length == 0) {
				return;
			}
			let data = d;
			let nodeSelect = $('#nodeSelect');
			nodeSelect.empty();
			for (let i = 0; i < data.data.rows.length; i++) {
				// let option = $("<option>").text(data.data.rows[i].ip).val(data.data.rows[i].node_uuid);
				let option = `<option value="${data.data.rows[i].node_uuid}">${data.data.rows[i].ip}</option>`;
				nodeSelect.append(option);
			}
		});
	}
	//初始化节点信息
	var initNodeUUID = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEMMONITOR, f:'getNodeUUid', p:{}}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('#node_uuid');
			var msg_nodeselect = $('#msg_node_uuid');
			nodeselect.empty();
			msg_nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].name).val(data[i].node_uuid);
				nodeselect.append(option);
			}
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].name).val(data[i].node_uuid);
				msg_nodeselect.append(option);
			}
			
			//初始chart所用的dom高度
			initChartHeight();
			//初始化基本信息
			getBasicMsg();
		});
		
	}
	
	//初始化容器的高度
	var initChartHeight = function(){
		var DomIdList = ['chart_cpu','chart_ram','chart_load','chart_network','chart_bps','chart_iops'];
		for(var i=0;i<DomIdList.length; i++){
			$("#"+DomIdList[i]).css('height','350px');
		};
		//第一次加载
		INITCHARTS = true;
		initData();
		resizemyCharts();
	}
	
	var resizemyCharts = function(){
		window.onresize = function(){
            let chartWidth = $('.card-echart').width();
            let chartHeight = $('.card-echart').height();

            $('#chart_cpu').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            $('#chart_ram').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            $('#chart_load').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

            $('#chart_network').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            $('#chart_bps').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
            $('#chart_iops').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

            myChart1.resize();
            myChart2.resize();
            myChart3.resize();
            myChart4.resize();
            myChart5.resize();
            myChart6.resize();
        }
	};
	
	//初始化数据
	var initData = function(){
		//执行加载动画
		showLoading()
		//检测是否离开当前页面
		if($('.systemMonitorPage').length <= 0){
			return;
		}
		//获取节点
		var node_uuid = $("#node_uuid").val();
	    //获取时间范围
		var time_range = $("#time_range").val();
		var range_time_start_end = getStartEndTime();
		var range_start_time = range_time_start_end.range_start_time;
		var range_end_time = range_time_start_end.range_end_time;
		//获取cpu告警值
		var cpu_alarm = $("#cpu_alarm").val();
		//获取内存告警值
		var ram_alarm = $("#ram_alarm").val();
		//获取根分区告警值
		var root_alarm = $("#root_alarm").val();
		var enable_flag = $('#alarm_flag').get(0).checked;
		if(!enable_flag){
			cpu_alarm = "";
			ram_alarm = "";
			root_alarm = "";
		}
		
		var info = {};
		info.node_uuid = node_uuid;
		info.time_range = time_range;
		info.range_start_time = range_start_time;
		info.range_end_time = range_end_time;
		info.cpu_alarm = cpu_alarm;
		info.ram_alarm = ram_alarm;
		info.root_alarm = root_alarm;
		
		info = JSON.stringify(info);
		var updateInterval = 2500; //2.5秒
		//同步执行ajax
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.SYSTEMMONITOR,f:'initDataFunc',p:info},
	        success: function(d){ 
	        	var data = JSON.parse(d);
	        	NEWDATA = data;
	        	initEcharts(data);
	        } 
		});
	};
	
	
	var initEcharts = function(data){
		clearTimeout(timerTask.initEcharts);
		var updateInterval = 2500; //2.5秒
    	function update(){
    		if(intervalFlag){ //如果是静态则不执行计时器
    			clearTimeout(timerTask.initEcharts);
    			initCpuChart(data['cpuMsg'],0,INITCHARTS);
    		    initRamChart(data['ramMsg'],0,INITCHARTS);
    		    initLoadChart(data['loadMsg'],0,INITCHARTS);
    		    initNetworkChart(data['netWorkMsg'],0,INITCHARTS);
    		    initBpsChart(data['bpsMsg'],0,INITCHARTS);
    		    initIopsChart(data['iopsMsg'],0,INITCHARTS);
    		    INITCHARTS = false
    		    hideLoading()
		    	return;
		    }
    		initCpuChart(data['cpuMsg'],0,INITCHARTS);
		    initRamChart(data['ramMsg'],0,INITCHARTS);
		    initLoadChart(data['loadMsg'],0,INITCHARTS);
		    initNetworkChart(data['netWorkMsg'],0,INITCHARTS);
		    initBpsChart(data['bpsMsg'],0,INITCHARTS);
		    initIopsChart(data['iopsMsg'],0,INITCHARTS);
		    INITCHARTS = false
		    myChart1.hideLoading();
			timerTask.initEcharts = setTimeout(initData, updateInterval);
        }
    	update();
	};
	
	//执行改函数必须有echarts实例化对象才可以,也就是echarts必须被初始化一遍,如果报错 检测执行顺序
	var showLoading = function(){
		if(intervalFlag){
			myChart1.showLoading();
			myChart2.showLoading();
			myChart3.showLoading();
			myChart4.showLoading();
			myChart5.showLoading();
			myChart6.showLoading();
		}
	}
	
	var hideLoading = function(){
		myChart1.hideLoading();
		myChart2.hideLoading();
		myChart3.hideLoading();
		myChart4.hideLoading();
		myChart5.hideLoading();
		myChart6.hideLoading();
	}
	
	var getStartEndTime = function(){
		var range_start_time = "";
		var range_end_time = "";
		var time_range = $("#time_range").val();
		switch(time_range){
			case "1":
				range_start_time = moment().subtract(1, 'hour').format('YYYY-MM-DD HH:mm:ss');
				range_end_time = moment().format('YYYY-MM-DD HH:mm:ss');
				break;
			case "2":
				range_start_time = moment().subtract(3, 'hour').format('YYYY-MM-DD HH:mm:ss');
				range_end_time = moment().format('YYYY-MM-DD HH:mm:ss');
				break;
			case "3":
				range_start_time = moment().subtract(6, 'hour').format('YYYY-MM-DD HH:mm:ss');
				range_end_time = moment().format('YYYY-MM-DD HH:mm:ss');
				break;
			case "4":
				range_start_time = moment().subtract(1, 'day').format('YYYY-MM-DD HH:mm:ss');
				range_end_time = moment().format('YYYY-MM-DD HH:mm:ss');
				break;
			case "5":
				range_start_time = moment().subtract(7, 'day').format('YYYY-MM-DD HH:mm:ss');
				range_end_time = moment().format('YYYY-MM-DD HH:mm:ss');
				break;
			case "6":
				range_start_time = moment().subtract(1, 'month').format('YYYY-MM-DD HH:mm:ss');
				range_end_time = moment().format('YYYY-MM-DD HH:mm:ss');
				break;
			case "self_time":
				range_start_time = _daterangepicker_starttime;
				range_end_time = _daterangepicker_endtime;
				if(range_start_time =="" || range_start_time == undefined || range_end_time == "" || range_end_time == undefined){
					range_start_time = moment().subtract(10, 'minutes').format('YYYY-MM-DD HH:mm:ss');
					range_end_time = moment().format('YYYY-MM-DD HH:mm:ss');
				}
				break;
			default:
				range_start_time = "";
				range_end_time = "";
		};
		return {'range_start_time':range_start_time,'range_end_time':range_end_time};
		
	}
	
	
	
	//初始化日期选择插件
    var inintDatatimePicker = function(){
		//初始化日期时间选择控件
		$('#daterangepicker').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
			"minDate": moment().subtract(1, 'month'), //最早可以选的日期  
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
		    "timePicker": true,													//是否显示时间,时分
		    "timePicker24Hour": true,											//是否是24小时制
		    "alwaysShowCalendars": true,										//是否总是显示日期选择
		    "ranges": getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		    "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
		});
		
		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#daterangepicker').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_range = picker.chosenLabel;
			$(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
			//初始化数据
			INITCHARTS = true;
			initData();
			
		});

        $('#daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
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
    
    var getRangesConfig = function(LANGUAGE){
		var ranges = {
	       	[`${LANG.UI_CALENDAR_TODAY}`]: [moment().startOf('day'), moment()],
	        [`${LANG.UI_CALENDAR_YESTERDAY}`]: [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
	        [`${LANG.UI_CALENDAR_LAST_WEEK}`]: [moment().subtract(6, 'days').startOf('day'), moment()],
	        [`${LANG.UI_CALENDAR_LAST_MONTH}`]: [moment().subtract(29, 'days').startOf('day'), moment()],
	        [`${LANG.UI_CALENDAR_THIS_MONTH}`]: [moment().startOf('month'), moment().endOf('month')],
	    };
		return ranges;
	};
	
	
	//初始化告警规则的值
	var initAlarmRuleVal = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEMMONITOR, f:'getAlarmVal', p:{}}, function(d){
			var data = JSON.parse(d);
			ALARMVALUE = data;
			//得到告警的值则开始设置其值
			setAlarmVal(data);
		});
	};
	
	
	var setAlarmVal = function(alarmVal){
		//设置启用
		$('#alarm_flag').bootstrapSwitch('state', alarmVal.enable_flag);
		//设置CPU告警阈值
		$("#cpu_alarm").val(alarmVal.cpu_val);
		//设置内存告警阈值
		$("#ram_alarm").val(alarmVal.ram_val);
		//设置根分区告警阈值
		$("#root_alarm").val(alarmVal.root_val);
		//设置统计周期
		$("#item_period").val(alarmVal.item_period);
		//设置通道沉默周期
		$("#silence_time").val(alarmVal.silence_time);
		//得到是否开启告警
        var enable_flag = $('#alarm_flag').get(0).checked;
        if(enable_flag){
        	$("#noClickDiv").css("pointer-events","auto");
        	$("#noClickDiv").css("opacity","1");
        }else{
        	$("#noClickDiv").css("pointer-events","none");
        	$("#noClickDiv").css("opacity","0.5");
        }
	}
	
	
	/**
	 * 提交告警规则
	 */
	var submitAlarm = function(){
		var info = {};
		//设置启用
		info.alarm_flag = $('#alarm_flag').get(0).checked;
		//设置CPU告警阈值
		info.cpu_alarm = $("#cpu_alarm").val();
		//设置内存告警阈值
		info.ram_alarm = $("#ram_alarm").val();
		//设置根分区告警阈值
		info.root_alarm = $("#root_alarm").val();
		//设置统计周期
		info.item_period = $("#item_period").val();
		//设置通道沉默周期
		info.silence_time = $("#silence_time").val();
		
		info = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEMMONITOR, f:'setAlarmVal', p:info}, function(d){
			var data = JSON.parse(d);
			if(data.flag){
				ALARMVALUE = data;
			}
			$('#setAlarmModal').modal("hide");
		});
		INITCHARTS = true;
		initData();
		
		
	}
	
	/**
	 * 初始化echarts表格
	 */
	var echartDetails = function(chartInt){
		switch(chartInt){
			case 1:
				initCpuChart(NEWDATA['cpuMsg'],chartInt,false);
				$("#chart_cpu_details").show();
				$(".chart_details_class").not('#chart_cpu_details').hide();
				$("#detailsEchart .modal-title").text(LANG.UI_SYSTEM_MONITOR_CPU_PERCENTAGE)
				break
			case 2:
				initRamChart(NEWDATA['ramMsg'],chartInt,false);
				$("#chart_ram_details").show();
				$(".chart_details_class").not('#chart_ram_details').hide();
				$("#detailsEchart .modal-title").text(LANG.UI_SYSTEM_MONITOR_RAM_PERCENTAGE)
				break
			case 3:
				initLoadChart(NEWDATA['loadMsg'],chartInt,false);
				$("#chart_load_details").show();
				$(".chart_details_class").not('#chart_load_details').hide();
				$("#detailsEchart .modal-title").text(LANG.UI_SYSTEM_MONITOR_LOAD_PERCENTAGE)
				break
			case 4:
				initNetworkChart(NEWDATA['netWorkMsg'],chartInt,false);
				$("#chart_network_details").show();
				$(".chart_details_class").not('#chart_network_details').hide();
				$("#detailsEchart .modal-title").text(LANG.UI_SYSTEM_MONITOR_NET_PERCENTAGE)
				break
			case 5:
				initBpsChart(NEWDATA['bpsMsg'],chartInt,false);
				$("#chart_bps_details").show();
				$(".chart_details_class").not('#chart_bps_details').hide();
				$("#detailsEchart .modal-title").text(LANG.UI_SYSTEM_MONITOR_BPS_PERCENTAGE)
				break
			case 6:
				initIopsChart(NEWDATA['iopsMsg'],chartInt,false);
				$("#chart_iops_details").show();
				$(".chart_details_class").not('#chart_iops_details').hide();
				$("#detailsEchart .modal-title").text(LANG.UI_SYSTEM_MONITOR_IOPS_PERCENTAGE)
				break
		};
		$('#detailsEchart').modal({"width": "1104px", "height": "600px"});
	}
	
    
//------------------------------------CPU Chart---------------------------------------------------------	
	var initCpuChart = function(cpuMsg,TypeInt,initflag){
		//组合消息体
		var seriesList = [];
		var alarmLineFlag = false; //告警线 可为每个线设置个告警线,这里只设置一条线即可
		for(let i in cpuMsg['y_percent']){
			let oneLine = {};
			oneLine.name = i;
			oneLine.type = "line";
			oneLine.showSymbol = false,
			oneLine.data = cpuMsg['y_percent'][i];
			if(!alarmLineFlag && cpuMsg['alarm_val'] != ""){
				oneLine.markLine = { 
				        name:LANG.UI_SYSTEM_MONITOR_ALARM_LINE,
				        symbol: "none",
				        silent:true,
				        lineStyle:{color: 'red'},
				        //告警数值
				        data:[{yAxis: cpuMsg['alarm_val']}],
				        };
			}else{
				oneLine.markLine = { 
				        name:LANG.UI_SYSTEM_MONITOR_ALARM_LINE,
				        symbol: "none",
				        silent:true,
				        lineStyle:{color: 'red'},
				        //告警数值
				        data:[{yAxis: ""}],
				        };
			}
			seriesList.push(oneLine);
		}
		
		var selectedList = {};
		//设置成只有cpu中开启,其他cpu置灰的情况
		for(let x in cpuMsg['name']){
			let name = cpuMsg['name'][x];
			if(name == "cpu"+LANG.UI_SYSTEM_MONITOR_PERCENTAGE_SYSTEM){
				selectedList[name] = true;
			}else{
				selectedList[name] = false;
			}
		}
		if(!initflag){ //只有初始化的时候执行,其他时候传入空值
			selectedList = {}
		}
		
		
		var option = {
				  //名称
				  title: {
				    left: '2%',
				    text: LANG.UI_SYSTEM_MONITOR_CPU_PERCENTAGE
				  },
				  //指示后是否显示提示信息
				  tooltip: {
				    trigger: 'axis',
				    formatter: function (params, ticket, callback) {
				    	var des = params[0].axisValue + "<br/>";
				    	for(var i=0; i<params.length; i++){
				    		let dataVal = params[i].data ? params[i].data : "0";
				    		
			    			des += params[i].marker + params[i].seriesName +": "+ dataVal + " % ";
			    			des += "<br/>";
				    	}
				    	return des;
			        }
				  },
				  //是否显示每条线的名称
				  legend: {
				    bottom:'3%',
				    type: 'scroll',//是否滚动
				    itemGap : 15, //间距
				    data: cpuMsg['name'],
				    selected: selectedList,
				    
				  },
				  //整体位置
				  grid: {
				    left: '3%',
				    right: '4%',
				    bottom: '12%',
				    containLabel: true
				  },
				  //工具栏
				  toolbox: {
					orient: 'vertical',
				    feature: {
				     //自定义图标
				      myTool1: {
				        show: true,
				        title: LANG.UI_SYSTEM_MONITOR_DETAILSE,
				        icon: 'path d="M23 5.99966H8C6.89543 5.99966 6 6.89509 6 7.99966V40C6 41.1046 6.89543 42 8 42H40C41.1046 42 42 41.1046 42 40V25 M24 15.9998V23.9998 M42 5.99951V13.9995 M32 23.9998H24 M42 5.99966L24 23.9997 M42 5.99966H34',
				        onclick: function (){
				            echartDetails(1);
				        }
				    },
				    },
				   
				  },
				  //x轴
				  xAxis: {
				    type: 'category',
//				    boundaryGap: false,
				    data: cpuMsg['x_time'],
				  },
				  //轴
				  yAxis: {
				    type: 'value',
				    //最小值
				    min:0,
				    //最大值
				    max:100,
				    axisLabel: {
				      //显示名称
				      formatter: '{value} %'
				    }
				  },
				  //数据
				  series: seriesList,
				};
		//如果内容为空 则显示暂无数据
		if(cpuMsg['y_percent'].length == 0 || cpuMsg['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "black",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(myChart1 != undefined){
				myChart1.clear(); //清空实例 否则会与上一个合并 数据残留
			}
		}else{
			delete option['title'];
			option['title'] = {
			          text: LANG.UI_SYSTEM_MONITOR_CPU_PERCENTAGE,
			          x: "",
			          y: "",
			          left: '2%',
			          textStyle: {
			            color: "#333",
			            fontWeight: "bolder",
			            fontSize: 18,
			          },
			        };
		}
		
		
		if(TypeInt == 0){
			myChart1 = echarts.init(document.getElementById('chart_cpu'));
			myChart1.setOption(option,initflag);
		}else{
			myChart1_details = echarts.init(document.getElementById('chart_cpu_details'));
			option['toolbox']['feature']['dataZoom'] = {yAxisIndex: 'none',title:{zoom:LANG.UI_SYSTEM_MONITOR_ZOOM,back:LANG.UI_SYSTEM_MONITOR_ZOOM_BACK}};
			option['toolbox']['feature']['restore'] = {title:LANG.UI_SYSTEM_MONITOR_RESTORE_TITLE};
			option['toolbox']['feature']['magicType'] = { type: ['line', 'bar'],title:{line:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_LINE_TITLE,bar:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_BAR_TITLE} };
			option['toolbox']['feature']['saveAsImage'] = {title:LANG.UI_SYSTEM_MONITOR_SAVEIMG_TITLE};
			option['dataZoom']= [{bottom: '10%',}];
			option['grid']['bottom']= '20%';
			delete option['toolbox']['feature']['myTool1'];
			myChart1_details.setOption(option,initflag);
		}
	};
	
//------------------------------------内存 Chart---------------------------------------------------------	
	var initRamChart = function(ramMsg,TypeInt,initflag){
		var option = {
					  //名称
					  title: {
						left: '2%',
					    text: LANG.UI_SYSTEM_MONITOR_RAM_PERCENTAGE
					  },
					  //指示后是否显示提示信息
					  tooltip: {
					    trigger: 'axis',
					    formatter: function (params, ticket, callback) {
					    	var des = params[0].axisValue + "<br/>";
					    	for(var i=0; i<params.length; i++){
					    		let dataVal = params[i].data ? params[i].data : "0";
				    			des += params[i].marker + params[i].seriesName +": "+ dataVal + " % ";
				    			des += "<br/>";
					    	}
					    	return des;
				        }
					  },
					  //是否显示每条线的名称
					  legend: {
					    bottom:'3%',
					    type: 'scroll',
					    data: ramMsg['name']
					  },
					  //整体位置
					  grid: {
					    left: '3%',
					    right: '4%',
					    bottom: '12%',
					    containLabel: true
					  },
					  //工具栏
					  toolbox: {
						orient: 'vertical',
					    feature: {
					      //下载
					      // saveAsImage:{},
					     //自定义图标
					      myTool1: {
					        show: true,
					        title: LANG.UI_SYSTEM_MONITOR_DETAILSE,
					        icon: 'path d="M23 5.99966H8C6.89543 5.99966 6 6.89509 6 7.99966V40C6 41.1046 6.89543 42 8 42H40C41.1046 42 42 41.1046 42 40V25 M24 15.9998V23.9998 M42 5.99951V13.9995 M32 23.9998H24 M42 5.99966L24 23.9997 M42 5.99966H34',
					        onclick: function (){
					        	echartDetails(2);
					        }
					    },
					    },
					   
					  },
					  //x轴
					  xAxis: {
					    type: 'category',
//					    boundaryGap: false,
					    data: ramMsg['x_time'],
					  },
					  //轴
					  yAxis: {
					    type: 'value',
					    //最小值
					    min:0,
					    //最大值
					    max:100,
					    axisLabel: {
					      //显示名称
					      formatter: '{value} %'
					    }
					  },
					  //数据
					  series: [
						    {
						      name: ramMsg['name'][0],
						      type: 'line',
						      showSymbol: false,
						      // stack: 'Total',
						      data: ramMsg['y_percent'],
						     //告警线
						      markLine: { 
						        name:LANG.UI_SYSTEM_MONITOR_ALARM_LINE,
						        symbol: "none",
						        silent: true,
						        lineStyle: {
						          color: 'red'
						        },
						        //告警数值
						        data: [
						          {
						            yAxis: ramMsg['alarm_val']
						          },
						    ]},
						      
						      
						    },
						  ],
					};
		
		
		//如果内容为空 则显示暂无数据
		if(ramMsg['y_percent'].length == 0 || ramMsg['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "black",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(myChart2 != undefined){
				myChart2.clear(); //清空实例 否则会与上一个合并 数据残留
			}
		}else{
			delete option['title'];
			option['title'] = {
			          text: LANG.UI_SYSTEM_MONITOR_RAM_PERCENTAGE,
			          x: "",
			          y: "",
			          left: '2%',
			          textStyle: {
			            color: "#333",
			            fontWeight: "bolder",
			            fontSize: 18,
			          },
			        };
		}
		
		if(TypeInt == 0){
			myChart2 = echarts.init(document.getElementById('chart_ram'));
			myChart2.setOption(option,initflag);
		}else{
			myChart2_details = echarts.init(document.getElementById('chart_ram_details'));
			option['toolbox']['feature']['dataZoom'] = {yAxisIndex: 'none',title:{zoom:LANG.UI_SYSTEM_MONITOR_ZOOM,back:LANG.UI_SYSTEM_MONITOR_ZOOM_BACK}};
			option['toolbox']['feature']['restore'] = {title:LANG.UI_SYSTEM_MONITOR_RESTORE_TITLE};
			option['toolbox']['feature']['magicType'] = { type: ['line', 'bar'],title:{line:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_LINE_TITLE,bar:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_BAR_TITLE} };
			option['toolbox']['feature']['saveAsImage'] = {title:LANG.UI_SYSTEM_MONITOR_SAVEIMG_TITLE};
			option['dataZoom']= [{bottom: '10%',}];
			option['grid']['bottom']= '20%';
			delete option['toolbox']['feature']['myTool1'];
			myChart2_details.setOption(option,initflag);
		}
	};
	
	//------------------------------------系统负载 Chart---------------------------------------------------------	
	var initLoadChart = function(loadMsg,TypeInt,initflag){
		//组合消息体
		var seriesList = [];
		for(let i in loadMsg['y_val']){
			let oneLine = {};
			oneLine.name = i;
			oneLine.type = "line";
			oneLine.showSymbol = false,
			oneLine.data = loadMsg['y_val'][i];
			seriesList.push(oneLine);
		}
		var option = {
		  //名称
		  title: {
			  left: '2%',
		    text: LANG.UI_SYSTEM_MONITOR_LOAD_PERCENTAGE
		  },
		  //指示后是否显示提示信息
		  tooltip: {
		    trigger: 'axis',
		    formatter: function (params, ticket, callback) {
		    	var des = params[0].axisValue + "<br/>";
		    	for(var i=0; i<params.length; i++){
		    		let dataVal = params[i].data ? params[i].data : "0";
	    			des += params[i].marker + params[i].seriesName +": "+ dataVal;
	    			des += "<br/>";
		    	}
		    	return des;
	        }
		  },
		  //是否显示每条线的名称
		  legend: {
		    bottom:'3%',
		    type: 'scroll',
		    data: loadMsg['name']
		  },
		  //整体位置
		  grid: {
		    left: '3%',
		    right: '4%',
		    bottom: '12%',
		    containLabel: true
		  },
		  //工具栏
		  toolbox: {
		    orient: 'vertical',
		    feature: {
		      //下载
		      // saveAsImage:{},
		     //自定义图标
		      myTool1: {
		        show: true,
		        title: LANG.UI_SYSTEM_MONITOR_DETAILSE,
		        icon: 'path d="M23 5.99966H8C6.89543 5.99966 6 6.89509 6 7.99966V40C6 41.1046 6.89543 42 8 42H40C41.1046 42 42 41.1046 42 40V25 M24 15.9998V23.9998 M42 5.99951V13.9995 M32 23.9998H24 M42 5.99966L24 23.9997 M42 5.99966H34',
		        onclick: function (){
		        	echartDetails(3);
		        }
		    },
		    },
		   
		  },
		  //x轴
		  xAxis: {
		    type: 'category',
//		    boundaryGap: false,
		    data: loadMsg['x_time'],
		  },
		  //轴
		  yAxis: {
		    type: 'value',
		  },
		  //数据
		  series: seriesList,
		};
		
		
		//如果内容为空 则显示暂无数据
		if(loadMsg['y_val'].length == 0 || loadMsg['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "black",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(myChart3 != undefined){
				myChart3.clear(); //清空实例 否则会与上一个合并 数据残留
			}
			
		}else{
			delete option['title'];
			option['title'] = {
			          text: LANG.UI_SYSTEM_MONITOR_LOAD_PERCENTAGE,
			          x: "",
			          y: "",
			          left: '2%',
			          textStyle: {
			            color: "#333",
			            fontWeight: "bolder",
			            fontSize: 18,
			          },
			        };
		}
		
		if(TypeInt == 0){
			myChart3 = echarts.init(document.getElementById('chart_load'));
			myChart3.setOption(option,initflag);
		}else{
			myChart3_details = echarts.init(document.getElementById('chart_load_details'));
			option['toolbox']['feature']['dataZoom'] = {yAxisIndex: 'none',title:{zoom:LANG.UI_SYSTEM_MONITOR_ZOOM,back:LANG.UI_SYSTEM_MONITOR_ZOOM_BACK}};
			option['toolbox']['feature']['restore'] = {title:LANG.UI_SYSTEM_MONITOR_RESTORE_TITLE};
			option['toolbox']['feature']['magicType'] = { type: ['line', 'bar'],title:{line:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_LINE_TITLE,bar:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_BAR_TITLE} };
			option['toolbox']['feature']['saveAsImage'] = {title:LANG.UI_SYSTEM_MONITOR_SAVEIMG_TITLE};
			option['dataZoom']= [{bottom: '10%',}];
			option['grid']['bottom']= '20%';
			delete option['toolbox']['feature']['myTool1'];
			myChart3_details.setOption(option,initflag);
		}
	};
	
	
//------------------------------------网络流量 Chart---------------------------------------------------------	
	var initNetworkChart = function(netWorkMsg,TypeInt,initflag){
		//组合消息体
		var seriesList = [];
		var x = 0;
		for(let i in netWorkMsg['y_val']){
			let oneLine = {};
			oneLine.name = i;
			oneLine.type = "line";
			oneLine.showSymbol = false,
			oneLine.data = netWorkMsg['y_val'][i];
			seriesList.push(oneLine);
			x++;
		}
		var option = {
		  //名称
		  title: {
			  left: '2%',
		    text: LANG.UI_SYSTEM_MONITOR_NET_PERCENTAGE
		  },
		  //指示后是否显示提示信息
		  tooltip: {
		    trigger: 'axis',
		    formatter: function (params, ticket, callback) {
		    	var des = params[0].axisValue + "<br/>";
		    	for(var i=0; i<params.length; i++){
		    		let dataVal = params[i].data ? params[i].data : "0";
		    		if(dataVal >= 1024){
		    			des += params[i].marker + params[i].seriesName +": "+ Math.round(dataVal / 1024) + " MB/s ";
                	}else{
                		des += params[i].marker + params[i].seriesName +": "+ dataVal + " KB/s ";
                	}
		    		if(i%2 == 1){ //如果是奇数 则加换行符
		    			des += "<br/>";
		    		}
		    		
		    	}
		    	return des;
	        }
		  },
		  //是否显示每条线的名称
		  legend: {
		    bottom:'3%',
		    type: 'scroll',
		    data: netWorkMsg['name']
		  },
		  //整体位置
		  grid: {
		    left: '3%',
		    right: '4%',
		    bottom: '12%',
		    containLabel: true
		  },
		  //工具栏
		  toolbox: {
			orient: 'vertical',
		    feature: {
		      //下载
		      // saveAsImage:{},
		     //自定义图标
		      myTool1: {
		        show: true,
		        title: LANG.UI_SYSTEM_MONITOR_DETAILSE,
		        icon: 'path d="M23 5.99966H8C6.89543 5.99966 6 6.89509 6 7.99966V40C6 41.1046 6.89543 42 8 42H40C41.1046 42 42 41.1046 42 40V25 M24 15.9998V23.9998 M42 5.99951V13.9995 M32 23.9998H24 M42 5.99966L24 23.9997 M42 5.99966H34',
		        onclick: function (){
		        	echartDetails(4);
		        }
		    },
		    },
		   
		  },
		  //x轴
		  xAxis: {
		    type: 'category',
//		    boundaryGap: false,
		    data: netWorkMsg['x_time'],
		  },
		  //轴
		  yAxis: {
		    type: 'value',
		    axisLabel: {
		        // 显示名称
//		        formatter: '{value} k',
		        
		        formatter: function(value, index){
                	if(value >= 1024){
                		return Math.round(value / 1024) + "MB/s";
                	}else{
                		return value + "KB/s";
                	}
                },
		      }
		  },
		  //数据
		  series: seriesList,
		};
		
		//如果内容为空 则显示暂无数据
		if(netWorkMsg['y_val'].length == 0 || netWorkMsg['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "black",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(myChart4 != undefined){
				myChart4.clear(); //清空实例 否则会与上一个合并 数据残留
			}
		}else{
			delete option['title'];
			option['title'] = {
			          text: LANG.UI_SYSTEM_MONITOR_NET_PERCENTAGE,
			          x: "",
			          y: "",
			          left: '2%',
			          textStyle: {
			            color: "#333",
			            fontWeight: "bolder",
			            fontSize: 18,
			          },
			        };
		}
		
		
		if(TypeInt == 0){
			myChart4 = echarts.init(document.getElementById('chart_network'));
			myChart4.setOption(option,initflag);
		}else{
			myChart4_details = echarts.init(document.getElementById('chart_network_details'));
			option['toolbox']['feature']['dataZoom'] = {yAxisIndex: 'none',title:{zoom:LANG.UI_SYSTEM_MONITOR_ZOOM,back:LANG.UI_SYSTEM_MONITOR_ZOOM_BACK}};
			option['toolbox']['feature']['restore'] = {title:LANG.UI_SYSTEM_MONITOR_RESTORE_TITLE};
			option['toolbox']['feature']['magicType'] = { type: ['line', 'bar'],title:{line:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_LINE_TITLE,bar:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_BAR_TITLE} };
			option['toolbox']['feature']['saveAsImage'] = {title:LANG.UI_SYSTEM_MONITOR_SAVEIMG_TITLE};
			option['dataZoom']= [{bottom: '10%',}];
			option['grid']['bottom']= '20%';
			delete option['toolbox']['feature']['myTool1'];
			myChart4_details.setOption(option,initflag);
		}
	};
	
	//------------------------------------磁盘读写BPS Chart---------------------------------------------------------	
	var initBpsChart = function(bpsMsg,TypeInt,initflag){
		myChart5 = echarts.init(document.getElementById('chart_bps'));
		//组合消息体
		var seriesList = [];
		var x = 0;
		for(let i in bpsMsg['y_val']){
			let oneLine = {};
			oneLine.name = i;
			oneLine.type = "line";
			oneLine.showSymbol = false,
			oneLine.data = bpsMsg['y_val'][i];
			seriesList.push(oneLine);
			x++;
		}
		var option = {
		  //名称
		  title: {
			  left: '2%',
		    text: LANG.UI_SYSTEM_MONITOR_BPS_PERCENTAGE
		  },
		  //指示后是否显示提示信息
		  tooltip: {
		    trigger: 'axis',
		    formatter: function (params, ticket, callback) {
		    	var des = params[0].axisValue + "<br/>";
		    	for(var i=0; i<params.length; i++){
		    		let dataVal = params[i].data ? params[i].data : "0";
		    		if(dataVal >= 1024){
		    			des += params[i].marker + params[i].seriesName +": "+ Math.round(dataVal / 1024) + " MB/s ";
                	}else{
                		des += params[i].marker + params[i].seriesName +": "+ dataVal + " KB/s ";
                	}
		    		if(i%2 == 1){ //如果是奇数 则加换行符
		    			des += "<br/>";
		    		}
		    		
		    	}
		    	return des;
		     }
		  },
		  //是否显示每条线的名称
		  legend: {
		    bottom:'3%',
		    type: 'scroll',
		    data: bpsMsg['name']
		  },
		  //整体位置
		  grid: {
		    left: '3%',
		    right: '4%',
		    bottom: '12%',
		    containLabel: true
		  },
		  //工具栏
		  toolbox: {
			orient: 'vertical',
		    feature: {
		      //下载
		      // saveAsImage:{},
		     //自定义图标
		      myTool1: {
		        show: true,
		        title: LANG.UI_SYSTEM_MONITOR_DETAILSE,
		        icon: 'path d="M23 5.99966H8C6.89543 5.99966 6 6.89509 6 7.99966V40C6 41.1046 6.89543 42 8 42H40C41.1046 42 42 41.1046 42 40V25 M24 15.9998V23.9998 M42 5.99951V13.9995 M32 23.9998H24 M42 5.99966L24 23.9997 M42 5.99966H34',
		        onclick: function (){
		        	echartDetails(5);
		        }
		    },
		    },
		   
		  },
		  //x轴
		  xAxis: {
		    type: 'category',
//		    boundaryGap: false,
		    data: bpsMsg['x_time'],
		  },
		  //轴
		  yAxis: {
		    type: 'value',
		    axisLabel: {
		        // 显示名称
//		        formatter: '{value} k',
		        formatter: function(value, index){
                	if(value >= 1024){
                		return Math.round(value / 1024) + "MB/s";
                	}else{
                		return value + "KB/s";
                	}
                },
		      }
		  },
		  //数据
		  series: seriesList,
		};
		
		//如果内容为空 则显示暂无数据
		if(bpsMsg['y_val'].length == 0 || bpsMsg['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "black",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(myChart5 != undefined){
				myChart5.clear(); //清空实例 否则会与上一个合并 数据残留
			}
		}else{
			delete option['title'];
			option['title'] = {
			          text: LANG.UI_SYSTEM_MONITOR_BPS_PERCENTAGE,
			          x: "",
			          y: "",
			          left: '2%',
			          textStyle: {
			            color: "#333",
			            fontWeight: "bolder",
			            fontSize: 18,
			          },
			        };
		}
		
		if(TypeInt == 0){
			myChart5 = echarts.init(document.getElementById('chart_bps'));
			myChart5.setOption(option,initflag);
		}else{
			myChart5_details = echarts.init(document.getElementById('chart_bps_details'));
			option['toolbox']['feature']['dataZoom'] = {yAxisIndex: 'none',title:{zoom:LANG.UI_SYSTEM_MONITOR_ZOOM,back:LANG.UI_SYSTEM_MONITOR_ZOOM_BACK}};
			option['toolbox']['feature']['restore'] = {title:LANG.UI_SYSTEM_MONITOR_RESTORE_TITLE};
			option['toolbox']['feature']['magicType'] = { type: ['line', 'bar'],title:{line:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_LINE_TITLE,bar:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_BAR_TITLE} };
			option['toolbox']['feature']['saveAsImage'] = {title:LANG.UI_SYSTEM_MONITOR_SAVEIMG_TITLE};
			option['dataZoom']= [{bottom: '10%',}];
			option['grid']['bottom']= '20%';
			delete option['toolbox']['feature']['myTool1'];
			myChart5_details.setOption(option,initflag);
		}
	};
	
	
	//------------------------------------每秒读写次数IOPS Chart---------------------------------------------------------	
	var initIopsChart = function(iopsMsg,TypeInt,initflag){
		//组合消息体
		var seriesList = [];
		for(let i in iopsMsg['y_val']){
			let oneLine = {};
			oneLine.name = i;
			oneLine.type = "line";
			oneLine.showSymbol = false,
			oneLine.data = iopsMsg['y_val'][i];
			seriesList.push(oneLine);
		}
		var option = {
		  //名称
		  title: {
			  left: '2%',
		    text: LANG.UI_SYSTEM_MONITOR_IOPS_PERCENTAGE_UNIT
		  },
		  //指示后是否显示提示信息
		  tooltip: {
		    trigger: 'axis',
		    formatter: function (params, ticket, callback) {
		    	var des = params[0].axisValue + "<br/>";
		    	for(var i=0; i<params.length; i++){
		    		let dataVal = params[i].data ? params[i].data : "0";
					des += params[i].marker + params[i].seriesName +": "+ dataVal + LANG.UI_SYSTEM_IOPS_TIME +"/s ";
		    		if(i%2 == 1){ //如果是奇数 则加换行符
		    			des += "<br/>";
		    		}
		    		
		    	}
		    	return des;
	        }
		  },
		  //是否显示每条线的名称
		  legend: {
		    bottom:'3%',
		    type: 'scroll',
		    data: iopsMsg['name']
		  },
		  //整体位置
		  grid: {
		    left: '3%',
		    right: '4%',
		    bottom: '12%',
		    containLabel: true
		  },
		  //工具栏
		  toolbox: {
			orient: 'vertical',
		    feature: {
		      //下载
		      // saveAsImage:{},
		     //自定义图标
		      myTool1: {
		        show: true,
		        title: LANG.UI_SYSTEM_MONITOR_DETAILSE,
		        icon: 'path d="M23 5.99966H8C6.89543 5.99966 6 6.89509 6 7.99966V40C6 41.1046 6.89543 42 8 42H40C41.1046 42 42 41.1046 42 40V25 M24 15.9998V23.9998 M42 5.99951V13.9995 M32 23.9998H24 M42 5.99966L24 23.9997 M42 5.99966H34',
		        onclick: function (){
		        	echartDetails(6);
		        }
		    },
		    },
		   
		  },
		  //x轴
		  xAxis: {
		    type: 'category',
//		    boundaryGap: false,
		    data: iopsMsg['x_time'],
		  },
		  //轴
		  yAxis: {
		    type: 'value',
		    axisLabel: {
			      //显示名称
			     // formatter: '{value}'+' 次'
				  formatter:function (value, index) {
					var result="";
					
					result =  value + LANG.UI_SYSTEM_IOPS_TIME
					
					// switch(value){
					// 	case 1:result="Ⅰ类";break;
					// 	case 2:result="Ⅱ类";break;
					// 	case 3:result="Ⅲ类";break;
					// 	case 4:result="Ⅳ类";break;
					// 	case 5:result="Ⅴ类";break;
					// 	default:"-";
					// }
					return result;
				}
	

			    }
		  },
		  //数据
		  series: seriesList,
		};
		
		//如果内容为空 则显示暂无数据
		if(iopsMsg['y_val'].length == 0 || iopsMsg['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "black",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(myChart6 != undefined){
				myChart6.clear(); //清空实例 否则会与上一个合并 数据残留
			}
		}else{
			delete option['title'];
			option['title'] = {
			          text: LANG.UI_SYSTEM_MONITOR_IOPS_PERCENTAGE_UNIT,
			          x: "",
			          y: "",
			          left: '2%',
			          textStyle: {
			            color: "#333",
			            fontWeight: "bolder",
			            fontSize: 18,
			          },
			        };
		}
		
		if(TypeInt == 0){
			myChart6 = echarts.init(document.getElementById('chart_iops'));
			myChart6.setOption(option,initflag);
		}else{
			myChart6_details = echarts.init(document.getElementById('chart_iops_details'));
			option['toolbox']['feature']['dataZoom'] = {yAxisIndex: 'none',title:{zoom:LANG.UI_SYSTEM_MONITOR_ZOOM,back:LANG.UI_SYSTEM_MONITOR_ZOOM_BACK}};
			option['toolbox']['feature']['restore'] = {title:LANG.UI_SYSTEM_MONITOR_RESTORE_TITLE};
			option['toolbox']['feature']['magicType'] = { type: ['line', 'bar'],title:{line:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_LINE_TITLE,bar:LANG.UI_SYSTEM_MONITOR_MAGICTYPE_BAR_TITLE} };
			option['toolbox']['feature']['saveAsImage'] = {title:LANG.UI_SYSTEM_MONITOR_SAVEIMG_TITLE};
			option['dataZoom']= [{bottom: '10%',}];
			option['grid']['bottom']= '20%';
			delete option['toolbox']['feature']['myTool1'];
			myChart6_details.setOption(option,initflag);
		}
	};
//---------------------------------------以下系统信息-------------------------------------------------------------		
	
	var getBasicMsg = function(){
		
		Metronic.blockUI({target:".blockUI",animate: true});
		var node_uuid = $("#msg_node_uuid").val();
		var info = {};
		info.node_uuid = node_uuid;
		
		info = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEMMONITOR, f:'getBasicInfo', p:info}, function(d){
			var data = JSON.parse(d);
			//初始化系统信息数据
			if(data == "" || data.length == 0){
				Metronic.unblockUI('.blockUI');
				return;
			}
			$("#cpu_cores").text(data.cpu_cores);
			$("#cpu_count").text(data.cpu_count);
			$("#cpu_name").text(data.cpu_name);
			$("#cpu_processor").text(data.cpu_processor);
			$("#hardware_platform").text(data.hardware_platform);
			$("#kernel_name").text(data.kernel_name);
			$("#kernel_release").text(data.kernel_release);
			$("#kernel_version").text(data.kernel_version);
			$("#machine").text(data.machine);
			$("#menTotal").text(data.menTotal);
			$("#nodename").text(data.nodename);
			$("#operating_system").text(data.operating_system);
			$("#processor").text(data.processor);
			$("#root_percentage").text(data.root_percentage);
			$("#root_total").text(data.root_total);
			$("#root_used").text(data.root_used);
			$("#storage_adapter_IQN").text(data.storage_adapter_IQN);
			$("#AIO_version").text(data.AIO_version);
			if(data.AIO_version == "" || data.AIO_version == "--"){
				$("#AIO_div").hide();
			}else{
				$("#AIO_div").show();
			}
			initDiskMsg(data.disk_info);
			initNetworkMsg(data.network_info);
			initHBAMsg(data.HBA_info);
			Metronic.unblockUI('.blockUI');
		});
	}
	
	/**
	 * 初始化磁盘信息表格
	 */
	var initDiskMsg = function(disk_info){
		$("#disk_table").empty();
		var disk_info_html = '<tr class="msg_table_title">' +
                                '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_DEVICE_TYPE+'</th>' +
                                '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_DEVICE_FIRM+'</th>' +
                                '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_DEVICE_MODE+'</th>' +
                                '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_DEVICE_VERSION+'</th>' +
                                '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_DEVICE_NODE_NAMR+'</th>' +
                                '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_MBIT+'</th>' +
                              '</tr>';
		if(disk_info.length == 0 || disk_info == ""){
			$("#disk_table").append(disk_info_html);
			return;
		}
		var diskList = Object.values(disk_info);
		for(let i=0;i<diskList.length;i++){
			disk_info_html += '<tr class="msg_table_content">' +
			'<td>'+diskList[i].type+'</td>' +
			'<td>'+diskList[i].vendor+'</td>' +
			'<td>'+diskList[i].model+'</td>' +
			'<td>'+diskList[i].rev+'</td>' +
			'<td>'+diskList[i].name+'</td>' +
			'<td>'+diskList[i].size+'</td>' +
			'</tr>';
		}
		$("#disk_table").append(disk_info_html);
	}
	
	/**
	 * 初始化网卡表格
	 */
	var initNetworkMsg = function(network_info){
		$("#network_table").empty();
		var network_info_html = '<tr class="msg_table_title">' +
                                    '<th class="col-md-7">'+LANG.UI_SYSTEM_MONITOR_NET_MSG+'</th>' +
                                    '<th class="col-md-1">'+LANG.UI_SYSTEM_MONITOR_NET_NAME+'</th>' +
                                    '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_MAC+'</th>' +
                                    '<th class="col-md-1">'+LANG.UI_SYSTEM_MONITOR_NET_SPEED+'</th>' +
                                    '<th class="col-md-1">'+LANG.UI_SYSTEM_MONITOR_NET_STATUS+'</th>' +
                                 ' </tr>';
		if(network_info.length == 0 || network_info == ""){
			$("#network_table").append(network_info_html);
			return;
		}
		var networkList = Object.values(network_info);
		for(let i=0;i<networkList.length;i++){
			var classStatus = "warning_status";
			if(networkList[i].operstate == "up" || networkList[i].operstate == "Online"){
				classStatus = "success_status";
			}
			
			network_info_html += '<tr class="msg_table_content">' +
			'<td>'+networkList[i].vender+'</td>' +
			'<td>'+networkList[i].name+'</td>' +
			'<td>'+networkList[i].address+'</td>' +
			'<td>'+networkList[i].speed+'</td>' +
			'<td><div class="'+classStatus+'">'+networkList[i].operstate+'</div></td>' +
			'</tr>';
		}
		$("#network_table").append(network_info_html);
	}
	
	/**
	 * 初始化HBA卡表格
	 */
	var initHBAMsg = function(HBA_info){
		$("#HBA_table").empty();
		var HBA_info_html = '<tr class="msg_table_title">' +
						        '<th class="col-md-6">'+LANG.UI_SYSTEM_MONITOR_HBA_MSG+'</th>' +
						        '<th class="col-md-1">'+LANG.UI_SYSTEM_MONITOR_HBA_NAME+'</th>' +
						        '<th class="col-md-1">'+LANG.UI_SYSTEM_MONITOR_HBA_MODE+'</th>' +
						        '<th class="col-md-2">'+LANG.UI_SYSTEM_MONITOR_HBA_WWN+'</th>' +
						        '<th class="col-md-1">'+LANG.UI_SYSTEM_MONITOR_HBA_SPEED+'</th>' +
						        '<th class="col-md-1">'+LANG.UI_SYSTEM_MONITOR_HBA_STATUS+'</th>' +
						      '</tr>';
		if(HBA_info.length == 0 || HBA_info == ""){
			$("#HBA_div").hide();
			$("#network_HBA_title").text(LANG.UI_SYSTEM_MONITOR_NET_MSG);
			$("#HBA_table").append(HBA_info_html);
			return;
		}else{
			$("#HBA_div").show();
			$("#network_HBA_title").text(LANG.UI_SYSTEM_MONITOR_NET_HBA);
		}
		var HBAList = Object.values(HBA_info);
		for(let i=0;i<HBAList.length;i++){
			var classStatus = "warning_status";
			if(HBAList[i].port_state == "up" || HBAList[i].port_state == "Online"){
				classStatus = "success_status";
			}
			HBA_info_html += '<tr class="msg_table_content">' +
			'<td>'+HBAList[i].vender+'</td>' +
			'<td>'+HBAList[i].name+'</td>' +
			'<td>'+HBAList[i].model_name+'</td>' +
			'<td>'+HBAList[i].port_name+'</td>' +
			'<td>'+HBAList[i].speed+'</td>' +
			'<td><div class="'+classStatus+'">'+HBAList[i].port_state+'</div></td>' +
			'</tr>';
		}
		$("#HBA_table").append(HBA_info_html);
	}
	
	/**
     * echart图自适应 
     */
	const watchEchartSizeChange = () => {
		$(window).on('echart-resize', function() {
			if (CURRENT_TAB_ID === 'system_monitor_tab') { // 只在当前tab是系统监控页时做重绘，在系统信息tab，由于card-echart被隐藏拿不到 DOM元素
				// 设置300毫秒延迟后再重绘是考虑导航栏折叠或展开场景，其page-content-wrapper过渡时间设置的ransition: margin 0.5s ease;，因此要等300毫秒后拿到展开/缩放后的宽高再重绘
				setTimeout(() => {
					let chartWidth = $('.card-echart').width();
					let chartHeight = $('.card-echart').height();

					$('#chart_cpu').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
					$('#chart_ram').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
					$('#chart_load').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

					$('#chart_network').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
					$('#chart_bps').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});
					$('#chart_iops').css({'width': chartWidth + 'px', 'height': chartHeight + 'px'});

					myChart1.resize();
					myChart2.resize();
					myChart3.resize();
					myChart4.resize();
					myChart5.resize();
					myChart6.resize();
				}, 300);
			}
		});
	}
	
	return	{
		init: function(){
			//初始化告警规则值
			initAlarmRuleVal();
			//初始化节点
			initNodeUUID();
			//初始化时间插件
			inintDatatimePicker();
			
			//事件监听
			initListener();
			watchEchartSizeChange();
			initNodeSelect();
		}
	}
}();

jQuery(document).ready(function(){
	System.init();
});