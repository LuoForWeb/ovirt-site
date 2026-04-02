var DataBackupCenter = function () {
	var timerTask = {};
	var showTime;//系统时间
    var grid, gridInitFlag, searchFlag = false;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	var searchParams;
	var hostpie,hostpiecopy,naspie,productpie,backuppie,alarmPie,cpuChart,memeryChart,netChart,diskChart,awsplatformpie,instancepie,m365pie,hadooppie,obspie;
	var initCpuFlag = true,initMemeryFlag = true,initNetFlag = true,initDiskFlag = true;
	var cpudataArr = [],cputime = [];
	var memerydataArr = [],memerytime = [];
	var netdataArr = [],nettime = [];
	var diskdataArr = [],disktime = [];
	_TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
	var initConfig = function(){
		  pAjaxRequest({}, '/api/v1/system/config/base_info', "GET", function (result) {
			var config = result.data;
			CONF.SYSTEMNAME = config.system_name;
			CONF.VM_TYPE = config.vm_type;
			CONF.VM_DES = config.vm_des;
			CONF.SOFTWARE = config.software;
			CONF.DB_TYPE = config.db_type;
			CONF.DB_DES = config.db_des;
			CONF.IDLETIMEOUT = config.idletime_out;
			UIIdleTimeout.init(); // 初始化超时时间
			CONF.PASS_LENGTH = config.pass_length;
			CONF.PASS_COMPLEXITY = config.pass_complexity;
			CONF.HOST = config.host_name;
			//权限是object，需要转为array
			var permission = config.permission;
			var list = [];
			for(var i in permission){
				list.push(permission[i]);
			}
			CONF.PERMISSION = list;
			CONF.PERMISSION_ARR = Object.values(config.permission_arr);
			CONF.TASK_TYPE = config.task_type;
			CONF.TASK_TYPE_DES = config.task_type_des;
			CONF.TASK_STATUS = config.task_status;
			CONF.TASK_STATUS_DES = config.task_status_des;
			CONF.MODULE_TYPE = config.module_type;
			CONF.MODULE_TYPE_DES = config.module_type_des;
			CONF.FS_SUBMODULE_TYPE_DES = config.fs_submodule_type_des;
			CONF.VM_SUBMODULE_TYPE_DES = config.vm_submodule_type_des;
			CONF.STORAGE_TYPE_DES = config.storage_type_des;
			CONF.ENTERPRISE = config.enterprise;
			CONF.VENDOR = config.vendor; // oem版本
			CONF.REAL_PROTECT_STAGE_LIST = config.real_protect_stage_list;
			CONF.COMMON_STAGE_LIST = config.common_stage_list;
			CONF.AUTH_DB_TYPE = config.auth_db_type;

			var lang = CONF.LANG_CONF[config.language];
			bootbox.setLocale(lang);
			CONF.TENANTUUID = config.tenant_uuid;
			CONF.LANGUAGE = config.language;
			CONF.FUNCTIONS = config.function;
			CONF.PREFIX_STATUS = config.prefix_status;
			CONF.USER_LEVEL = config.user_level;
			CONF.PRODUCT_TYPE = config.product_type;
			CONF.IS_THREE_POWERS = config.is_three_powers;
			CONF.CHANGE_OTHER_PASSWD = config.change_other_passwd;
			CONF.VENDOR_LIST = config.vendor_list;
		},false);
		localStorage.setItem("sangfor_enterprise","sangfor_enterprise");
		
	}
	//检查系统授权
	var checkLisence = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.PLATFORM,f:'getLisenceInfo'}, function(data){
			var survey = JSON.parse(data);
			if(!survey.status){
				UIToastr.showWarning(survey.title, survey.info);
			}
		});
	}

	//初始化告警
	var initAlarmTips = function(){
		var getAlarmInfo = function(){
			$.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
				setAlarmInfo(d);
			})
				.complete(function() {
					setTimeout(getAlarmInfo, _TASKINTERVAL);});
		}
		getAlarmInfo();

	}
	var initListener = function () {
		initSwiper();
		$("#curtask").click(function () {
			LOCATION('./content/platform/jobs/jobs.php', 'task');
		});
		$("#histask").click(function () {
			LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'historyLi' } );
		});
		
		$("#storage_manager").click(function() {
			LOCATION('./content/platform/storage/storage.php','storage_manager');
		});
		$("#node_manager").click(function() {
			LOCATION('./content/platform/node/node.php','backup_manager');
		});
		$("#systemAlarm").click(function() {
			LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm', { tabId: 'system_alarm' });
		});
		$('#storageName').on('change', intStorageData);
		$("#planTaskStatus").on('change',searchPlanGird);
		$("#planTaskType").on('change',searchPlanGird);
		$('#node_uuid').on('change',function () {
			initCpuFlag = true;
			initMemeryFlag = true;
			initNetFlag = true;
			initDiskFlag = true;
			initData();
		  });
		window.onresize = function(){
			hostpie.resize();
			hostpiecopy.resize();
			naspie.resize();
			productpie.resize();
			backuppie.resize();
			alarmPie.resize();
			cpuChart.resize();
			memeryChart.resize();
			netChart.resize();
			diskChart.resize();
			awsplatformpie.resize();
			instancepie.resize()
			m365pie.resize();
			hadooppie.resize();
			obspie.resize();
			showPageBtn($("#dataTabs li.active a").attr("id"));
        }
	}

	//初始化swiper
	var initSwiper = function() {
		var swiperdiv = ["backup","cdp","copy"];
		for(var i=0; i<swiperdiv.length;i++) {
			var swipername = swiperdiv[i] + "Swiper";
			swipername = new Swiper("#"+ swiperdiv[i] + "Swiper", {
				//observer和observeParents解决一个页面多个swiper时，只有一个翻页按钮能正常使用问题
				observer: true,//开启动态检查器，监测swiper和slide
				observeParents: true,//监测Swiper 的祖/父元素
				autoplay: true,//可选选项，自动滑动
				autoplay: {
					delay: 30000,//30秒切换一次
				},
				slidesPerView : 'auto',  
				navigation: {
					nextEl: '.swiper-button-next.' + swiperdiv[i],
					prevEl: '.swiper-button-prev.' + swiperdiv[i],
				},
			});
		}
	}
	
	var searchPlanGird = function() {
		var p = {};
		p.taskType = $("#planTaskType").val();
		p.taskStatus = $("#planTaskStatus").val();
		searchParams = p;
		var params = {start:0, length:10, search: p};
		var data = {m:CONF.M.HOMEPAGE,f:'getBackupPlan',p:params};
		grid.setAjaxParam(data);
		grid.getRefresh(params, undefined, true);
		clearTimeout(timerTask.CurrentJob_data);
	}

	//存储资源池
	var intStorageName = function () {
		$.post(CONF.AJAXPATH, {m:CONF.M.HOMEPAGE,f:'getStorageName',p:{}}, function(d){
			var nameopt = JSON.parse(d);
			$("#storageName").html(nameopt);
			intStorageData();
		});
	}
	var intStorageData = function () { 
		var params = {};
		params.storageuuid = $("#storageName").val();
		params.use_mode = $("#storageName").find('option:selected').attr("mode");
		params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.HOMEPAGE,f:'getStorageInfo',p:params}, function(d){
			var data = JSON.parse(d);
			if(data.totalCapacity.size == 0) {
				var freeper = 0 + "%";
				var usedper = 0 + "%";
			}else {
				var freeper = Math.round(data.freeCapacity.size / (data.totalCapacity.size) * 10000) / 100 + "%";
				var usedper = Math.round(data.usedCapacity.size / (data.totalCapacity.size) * 10000) / 100 + "%";
			}
			$(".storage-resource .totalstor").html(data.totalCapacity.des);
			$(".storage-resource .usedtor").html(data.usedCapacity.des);
			$(".storage-resource .usedper").html(usedper);
			$(".storage-resource .freestor").html(data.freeCapacity.des);
			$(".storage-resource .freeper").html(freeper);
		});
	}

	//页面一开始获取不到隐藏的标签页下的dom宽度,切换时重新设置宽度
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		showPageBtn(e.target.id);
		$("#" + e.target.id + "Swiper .swiper-wrapper").css("transform","translate3d(0px, 0px, 0px)");
  	}) ;
	//对翻页按钮显示隐藏做判断
	var showPageBtn = function (target) { 
		if(target == "rt") {
			var allWidth1 = getAllWidth($("#copy_pane .swiper-slide"));
			if($("#copy_pane" ).width() < allWidth1) {
				$("#copy_pane .swiper-button-prev img").css("display","block");
				$("#copy_pane .swiper-button-next img").css("display","block");
			}else {
				$("#copy_pane .swiper-button-prev img").css("display","none");
				$("#copy_pane .swiper-button-next img").css("display","none");
			}
		} else if(target == "nas") {
			var allWidth2 = getAllWidth($("#nasProtect .swiper-slide"));
			if($("#nasProtect" ).width() < allWidth2) {
				$("#nasProtect .swiper-button-prev img").css("display","block");
				$("#nasProtect .swiper-button-next img").css("display","block");
			}else {
				$("#nasProtect .swiper-button-prev img").css("display","none");
				$("#nasProtect .swiper-button-next img").css("display","none");
			}
		
		} else if(target == "db") {
			var allWidth3 = getAllWidth($("#dbrealTime .swiper-slide"));
			if($("#dbrealTime" ).width() < allWidth3) {
				$("#dbrealTime .swiper-button-prev img").css("display","block");
				$("#dbrealTime .swiper-button-next img").css("display","block");
			}else {
				$("#dbrealTime .swiper-button-prev img").css("display","none");
				$("#dbrealTime .swiper-button-next img").css("display","none");
			}
		}
		else if(target == "aws") {
			var allWidth3 = getAllWidth($("#awsProtect .swiper-slide"));
			if($("#awsProtect" ).width() < allWidth3) {
				$("#awsProtect .swiper-button-prev img").css("display","block");
				$("#awsProtect .swiper-button-next img").css("display","block");
			}else {
				$("#awsProtect .swiper-button-prev img").css("display","none");
				$("#awsProtect .swiper-button-next img").css("display","none");
			}
		}
		else if(target == "m365") {
			var allWidth3 = getAllWidth($("#m365Protect .swiper-slide"));
			if($("#m365Protect" ).width() < allWidth3) {
				$("#m365Protect .swiper-button-prev img").css("display","block");
				$("#m365Protect .swiper-button-next img").css("display","block");
			}else {
				$("#m365Protect .swiper-button-prev img").css("display","none");
				$("#m365Protect .swiper-button-next img").css("display","none");
			}
		}
		else if(target == "hadoop") {
			var allWidth3 = getAllWidth($("#hadoopProtect .swiper-slide"));
			if($("#hadoopProtect" ).width() < allWidth3) {
				$("#hadoopProtect .swiper-button-prev img").css("display","block");
				$("#hadoopProtect .swiper-button-next img").css("display","block");
			}else {
				$("#hadoopProtect .swiper-button-prev img").css("display","none");
				$("#hadoopProtect .swiper-button-next img").css("display","none");
			}
		}
		else if(target == "obs") {
			var allWidth3 = getAllWidth($("#obsProtect .swiper-slide"));
			if($("#obsProtect" ).width() < allWidth3) {
				$("#obsProtect .swiper-button-prev img").css("display","block");
				$("#obsProtect .swiper-button-next img").css("display","block");
			}else {
				$("#obsProtect .swiper-button-prev img").css("display","none");
				$("#obsProtect .swiper-button-next img").css("display","none");
			}
		}
	}
	//获取所有div的宽度之和
	var getAllWidth = function (divs) { 
		var width = 0;
		for(var i = 0; i < divs.length; i++) {
			width += $(divs[i]).width();
		}
		return width;
	 }  















	var initAlarmData = function () { 
		$.post(CONF.AJAXPATH, {m:CONF.M.HOMEPAGE,f:'getAlarmData',p:{}}, function(d){
			var data = JSON.parse(d);
			initAlarmchart(data);
		});
	}
    //告警统计
    var initAlarmchart = function(data) {
		alarmPie = echarts.init(document.getElementById('alarmPie'));
        var option;
		var total = data.errorNum + data.warnNum;
        var option = {
            title: [
				{
					text: `{val|${total}}\n{name|统计总数}`,
					top: '31%',
					left: '23%',
					textAlign: 'center',
					textStyle: {
						rich: {
							name: {
								fontSize: "12px",
								color: '#C1C1C1',
								padding: [5, 0],
							},
							val: {
								fontSize: "30px",
								color: '#fff',
                                // left: '20%',
							}
						}
					}
				},
			],
            tooltip: {
                trigger: "item",
  				backgroundColor: "rgba(0,0,0,0.5)",
  				borderWidth: "0", //边框宽度设置
  				textStyle: {
  				  color: "white" //设置文字颜色
  				},
            },
            legend: {
                icon: "circle",
                orient: 'vertical',//设置图例的方向
                right: 30,
                itemWidth: 8,
				itemHeight:8,
                top: 48,
                itemGap:20,//设置图例的间距
                //自定义图例后面的数字样式
				formatter: function (name) {
					let target;
					if (name == "错误") {
						target = data.errorNum;
					}else{
						target = data.warnNum;
					}
					let arr = `{b|${name}}` + `{a|${target}}`
					return arr;
				},
                textStyle: {
					rich: {
						a: {
							fontSize: 14,
							color: "#fff",
							padding: 10,
							// fontWeight: 700
						},
						b: {
							color: "#A9ADAF",
							fontSize: "14",
						}
					},
				}
            },
            series: [
                {
                    name: '告警统计',
                    type: 'pie',
                    radius: ['55%', '73%'],
                    center: ['25%', '54%'],
                    color: ["#FC4850","#FCD200"],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        // borderRadius: 10,
                        borderColor: '#283234',
                        borderWidth: 1
                    },
                    label: {
                        show: false,
                        position: 'center'
                    },
                    labelLine: {
                        show: false
                    },
                    data: [
                        {value: data.errorNum, name: '错误'},
                        {value: data.warnNum, name: '警告'},
                    ]
                }
            ]
       };
    

        option && alarmPie.setOption(option);
    }



    //备份计划表格
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		if(0 == data.length) {
			$('.backup-plan .page-right').hide();
			$(".backup-plan table tbody tr td").css("cssText","background-color: #f5f5f500 !important;")
			return;
		} else {
			$('.backup-plan .page-right').show();
		}
		var tasknameDiv = $('#planTable tbody > tr').find('td:eq(0)');
		var statusDiv = $('#planTable tbody > tr').find('td:eq(1)');
		var progressDiv = $('#planTable tbody > tr').find('td:eq(2)');
		
		for(var i=0; i<statusDiv.length; i++){
			taskname(tasknameDiv[i],data[i][0])
			taskStatus(statusDiv[i], data[i][1],data[i][5]);
			addProgress(progressDiv[i], data[i][2]);
		}
		$('.sangfor-content-bottom .page-right div:nth-child(5)').html("页");
	}
	//任务名
	var taskname = function (div,name) {
		var des = "";
		if(name.length > 12) {
			des = '<td title="'+ name +'">'+ name.substr(0,12)+ '...</td>';
		}else {
			des = "<td>" + name +"</td>";
		}
		$(div).html(des);
	}
	// 任务状态
	var taskStatus = function (div,status,text) {
		var des = "";
		switch(status) {
			//等待中
			case 1:
				des = '<img src="/img/platform/sangfor/wait.png" alt="">' + text;
				break;
			//运行
			case 2:
				des = '<img src="/img/platform/sangfor/run.png" alt="">' + text;
				break;
			//停止
			case 4:
				des = '<img src="/img/platform/sangfor/stop.png" alt="">' + text;
				break;
			//错误
			case 8:
				des = '<img src="/img/platform/sangfor/error.png" alt="">' + text;
				break;
			//停止中
			case 5:
				des =  '<img src="/img/platform/sangfor/stopping.png" alt="">' + text;
				break;
			default:
				des = '<img src="/img/platform/sangfor/wait.png" alt="">' + text;
				break;
		}
		$(div).html(des);
	}
	//进度条
	var addProgress = function (div,progress) {
		var des = "";
		des = '<div class="bg-grey"><div class="progess"></div></div>' + progress;
		$(div).html(des);
		if(progress == "0%" || progress == "--") {
			// $(div).find(".progess").width(0);
			$(div).find(".progess").hide();
		}else {
			$(div).find(".progess").show();
			$(div).find(".progess").width(parseInt(progress)*70/100);
		}
		if(progress == "100%") {
			$(div).find(".progess").attr("style","border-radius: 4px !important; boder-right: none;width: 70px;");
		}
		
	}
    var initPlanTable = function () {
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
	                'targets': [0, 4]
    			}],
				"ordering":false,//不要表头的默认排序
                'pageLength': parseInt(length)
    	};
    	var initGrid = function(){
    		if(0 == $('#planTable').size()){
        		clearTimeout(timerTask.CurrentJob_data);
        		return;
        	}
    		if(!gridInitFlag){
    			//加载显示页数分页条
    			grid = new Datatable();
        		var data = {m:CONF.M.HOMEPAGE,f:'getBackupPlan',p:getParams()};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#planTable"), showDetail:false, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(expandDiv == 0 && bootboxConfirm == 0){
    				grid.getRefresh(getParams());
    			}
    		}
    		timerTask.CurrentJob_data = setTimeout(initGrid, updateInterval);
    	}
    	initGrid();
    	return;
    }
    //得到参数
	var getParams = function(){
		//搜索项
		var name = $.trim($('.current_searchinput').val());
		if(!searchFlag){
			name = '';
		}
		var p = {start:0, length:10, search:{name:name}};
		if(searchParams){
			p = {start:0, length:10, search:searchParams};
		}
		return p;
	}

	//初始化节点信息
	var initNodeUUID = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEMMONITOR, f:'getNodeUUid', p:{}}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('#node_uuid');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].name).val(data[i].node_uuid);
				nodeselect.append(option);
			}
			initData();
		});
	}

	//硬件设备状态
	var initData = function(){
		var info = {};
		if(initCpuFlag) {
			info.count = 100;
		}else {
			info.count = 1;
		}
		info.node_uuid = $("#node_uuid").val();
		info = JSON.stringify(info);
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.HOMEPAGE,f:'initDataFunc',p:info},
	        success: function(d){ 
	        	var data = JSON.parse(d);
	        	initEcharts(data);
	        } 
		});
	};
	
	
	var initEcharts = function(data){
		clearTimeout(timerTask.initEcharts);
		var updateInterval = 2500; 
    	function update(data){
    		initCpuChart(data['cpuMsg']);
			initMemeryChart(data['ramMsg']);
			initNetChart(data['netWorkMsg']);
			initDiskChart(data['bpsMsg'],data['iopsMsg']);
			timerTask.initEcharts = setTimeout(initData, updateInterval);
        }
    	update(data);
	};
	// cpu使用率
	var initCpuChart = function(cpudata){
		cpuChart = echarts.init(document.getElementById('cpuChart'));
		if(cpudata['y_percent'].length == 0 || cpudata['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "white",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(cpuChart != undefined){
				cpuChart.clear(); 
			}
		}else {
			var eachseries = [];
			var color = ["#56FF3E","#00FFFF","#FFC000","#9400D3","#FF69B4"];
			var j = 0;
			var selectedList = {};
			for(var i in cpudata['y_percent']) {
				if(!initCpuFlag){
					cpudataArr[i].shift();
					cpudataArr[i].push(cpudata['y_percent'][i][0]);
				}else{
					cpudataArr[i] = [];
					cpudataArr[i] = cpudata['y_percent'][i].reverse();
				}
				//只有第一个cpu开启,其他cpu置灰
				if(initCpuFlag) {//初始化时才设置
					if(i == "cpu"+LANG.UI_SYSTEM_MONITOR_PERCENTAGE_SYSTEM){
						selectedList[i] = true;
					}else{
						selectedList[i] = false;
					}
				}
				let oneLine = {};
				oneLine.name = i;
				oneLine.type = "line";
				oneLine.showSymbol = false,
				oneLine.symbol = 'none';
				oneLine.smooth = true;
				oneLine.color = color[j%5];
				oneLine.data = cpudataArr[i];
				eachseries.push(oneLine);
				j++;
			}
			if(!initCpuFlag){
				cputime.shift();
				cputime.push(cpudata['x_time'][0]);
			}else {
				cputime = [];
				cputime = cpudata['x_time'].reverse();
			}
			initCpuFlag = false;
			option = {
				title: {
					text: 'CPU使用率',
					left: 5,
					textStyle: {
					  //字体粗细 'normal','bold','bolder','lighter',100 | 200 | 300 | 400...
					  fontWeight: 'normal',
					  color: '#4DCDCD',
					  //字体大小
					  fontSize: 14
					},
					subtext: "单位（%）",
					  subtextStyle:{ // 设置二级标题的样式
						color:"#A9ADAF",
					  fontSize: 12
					  }
				},
				tooltip: {
				  trigger: 'axis',
				  icon: "circle", 
				  backgroundColor:'rgba(0, 0, 0, 0.5)',
				  textStyle: {
					color: 'white',
				  },
				  formatter: function (params) {
					var relVal = params[0].name;
					for (var i = 0, l = params.length; i < l; i++) {
						var pvalue = params[i].value + '%';
						relVal += '<br/>' + ' <span style="display:none;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' +'  '+ params[i].seriesName +':  '+ pvalue;
					}
					return relVal;
				},
				  borderWidth:'0',
				  axisPointer: {
					  lineStyle: {
						color: "#10999B",
						type: "solid",
					},
				  },
				},
				legend: {
					type: 'scroll',
					pageIconColor:"white",
					orient: 'horizontal',
					bottom: 0,
					x:"center",
					icon: 'rect',
					itemWidth: 10,  // 设置宽度
					itemHeight: 2, // 设置高度
					itemGap: 20, // 设置间距
					textStyle: {
						color: "#E4E5E5",
						fontSize: "12",
					},
					selected:selectedList,
				},
				grid: {
					top:70,
					  left: 10,
					  right: 10,
					  bottom: 35,
					 containLabel: true
				},
				xAxis: {
					type: 'category',
					boundaryGap: false,
					  axisLine: {
						//坐标轴轴线相关设置
						onZero: ' ', //X 轴或者 Y 轴的轴线是否在另一个轴的 0 刻度
						onZeroAxisIndex: '12', //当有双轴时，可以用这个属性手动指定，在哪个轴的 0 刻度上
						// symbol: ['none', 'path://M250 150 L150 350 L350 350 Z'], //轴线两边的箭头
						lineStyle: {
							  color: '#3E6B6A', //坐标轴线线的颜色
							 width: '1' //坐标轴线线宽
						},
					  },
				  axisLabel: {
					//x轴刻度不展示最大和最小
					fontSize: '12',
					showMaxLabel: false,
					showMinLabel:false,
					color: '#939799',
					interval: 18,
				  },
				  data: cputime,
				},
				yAxis: {
					type: 'value',
					//设置最小值，最大值，间隔
					min: 0,
					max: 100,
					interval: 25,
					splitLine: {
					//网格线
						lineStyle: {
							color: '#2F4547', //坐标轴线线的颜色
							width: '1' //坐标轴线线宽
						  }
					},
					  axisLabel: {
						fontSize: '12', //文字的字体大小
						color: '#A9ADAF',
						margin: 20, //刻度标签与轴线之间的距离。
						
					},
				},
				series: eachseries,
			};
		}


		
		cpuChart.setOption(option);
	}
	//内存使用率
	var initMemeryChart = function(memerydata){
		memeryChart = echarts.init(document.getElementById('memeryChart'));
		if(memerydata['y_percent'].length == 0 || memerydata['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "white",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(memeryChart != undefined){
				memeryChart.clear(); 
			}
		}else {
			if(!initMemeryFlag){
				memerydataArr.shift();
				memerydataArr.push(memerydata['y_percent'][0]);
				memerytime.shift();
				memerytime.push(memerydata['x_time'][0]);
			}else{
				memerydataArr = [];
				memerydataArr = memerydata['y_percent'].reverse();
				memerytime = [];
				memerytime = memerydata['x_time'].reverse();
			}
			initMemeryFlag = false;
			option = {
				title: {
					  text: '内存使用率',
					left: 5,
					textStyle: {
					  //字体粗细 'normal','bold','bolder','lighter',100 | 200 | 300 | 400...
					  fontWeight: 'normal',
					  color: '#4DCDCD',
					  //字体大小
					  fontSize: 14
					},
					subtext: "单位（%）",
					  subtextStyle:{ // 设置二级标题的样式
						color:"#A9ADAF",
						fontSize: 12
					  }
				},
				tooltip: {
					trigger: 'axis',
					icon: "circle", 
					backgroundColor:'rgba(0, 0, 0, 0.5)',
					textStyle: {
					  color: 'white',
					},
					formatter: function (params) {
					  var relVal = params[0].name
					  for (var i = 0, l = params.length; i < l; i++) {
						  var pvalue = params[i].value + '%';
						  relVal += '<br/>' + ' <span style="display:none;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' +'  '+ params[i].seriesName +':  '+ pvalue;
					  }
					  return relVal;
				  },
					borderWidth:'0',
					axisPointer: {
						lineStyle: {
						  color: "#10999B",
						  type: "solid",
					  },
					},
				},
				legend: {
					  bottom: 0,
					  x:"center",
					  icon: 'rect',
					itemWidth: 10,  // 设置宽度
					itemHeight: 2, // 设置高度
					itemGap: 20, // 设置间距
					textStyle: {
						color: "#E4E5E5",
						fontSize: "12",
					}
				},
				grid: {
					top:70,
					  left: 10,
					  right: 10,
					  bottom: 35,
					 containLabel: true
				},
				xAxis: {
					type: 'category',
					boundaryGap: false,
					  axisLine: {
						//坐标轴轴线相关设置
						onZero: ' ', //X 轴或者 Y 轴的轴线是否在另一个轴的 0 刻度
						onZeroAxisIndex: '12', //当有双轴时，可以用这个属性手动指定，在哪个轴的 0 刻度上
						// symbol: ['none', 'path://M250 150 L150 350 L350 350 Z'], //轴线两边的箭头
						lineStyle: {
							  color: '#3E6B6A', //坐标轴线线的颜色
							 width: '1' //坐标轴线线宽
						},
					  },
				  axisLabel: {
					//x轴刻度不展示最大和最小
					fontSize: '12',
					showMaxLabel: false,
					showMinLabel:false,
					color: '#939799',
					interval: 18,
				  },
				  data: memerytime,
				},
				yAxis: {
					type: 'value',
					//设置最小值，最大值，间隔
					min: 0,
					max: 100,
					interval: 25,
					splitLine: {
					//网格线
						lineStyle: {
							color: '#2F4547', //坐标轴线线的颜色
							width: '1' //坐标轴线线宽
						  }
					},
					  axisLabel: {
						fontSize: '12', //文字的字体大小
						color: '#A9ADAF',
						margin: 20, //刻度标签与轴线之间的距离。
					  },
				},
				series: [
					  {
						name: '内存使用率',
						type: 'line',
						stack: 'Total',
						color: '#00FFFF',
						smooth: true,
						data: memerydataArr,
						symbol: 'none',
					  },
				]
			};
		}
		
		memeryChart.setOption(option);
	}
	//网络流量
	var initNetChart = function(netdata){
		netChart = echarts.init(document.getElementById('netChart'));
		if(netdata['y_val'].length == 0 || netdata['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "white",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(netChart != undefined){
				netChart.clear(); 
			}
		}else {
			var eachseries = [];
			var color = ["#56FF3E","#00FFFF","#FFC000","#9400D3","#FF69B4"];
			var j = 0;
			for(var i in netdata['y_val']) {
				if(!initNetFlag){
					netdataArr[i].shift();
					netdataArr[i].push(netdata['y_val'][i][0]);
				}else{
					netdataArr[i] = [];
					netdataArr[i] = netdata['y_val'][i].reverse();
				}
				let oneLine = {};
				oneLine.name = i;
				oneLine.type = "line";
				oneLine.showSymbol = false,
				oneLine.symbol = 'none';
				oneLine.smooth = true;
				oneLine.color = color[j%5];
				oneLine.data = netdataArr[i];
				eachseries.push(oneLine);
				j++;
			}
			if(!initNetFlag){
				nettime.shift();
				nettime.push(netdata['x_time'][0]);
			}else {
				nettime = [];
				nettime = netdata['x_time'].reverse();
			}
			initNetFlag = false;
			option = {
				title: {
					  text: '网络流量',
					left: 5,
					textStyle: {
					  //字体粗细 'normal','bold','bolder','lighter',100 | 200 | 300 | 400...
					  fontWeight: 'normal',
					  color: '#4DCDCD',
					  //字体大小
					  fontSize: 14
					},
					subtext: "单位（MB/s）",
					  subtextStyle:{ // 设置二级标题的样式
						color:"#A9ADAF",
						fontSize: 12
					  }
				},
				tooltip: {
					trigger: 'axis',
					icon: "circle", 
					backgroundColor:'rgba(0, 0, 0, 0.5)',
					textStyle: {
					  color: 'white',
					},
					formatter: function (params) {
					  var relVal = params[0].name;
					  var pvalue;
					  for (var i = 0, l = params.length; i < l; i++) {
						if(params[i].value == 0) {
							pvalue =  0 + "MB/s"
						}else {
							pvalue =  (params[i].value / 1024).toFixed(2) + "MB/s";
						} 
						  relVal += '<br/>' + ' <span style="display:none;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' +'  '+ params[i].seriesName +':  '+ pvalue;
					  }
					  return relVal;
				  },
					borderWidth:'0',
					axisPointer: {
						lineStyle: {
						  color: "#10999B",
						  type: "solid",
					  },
					},
				},
				legend: {
					type: 'scroll',
					pageIconColor:"white",
					orient: 'horizontal',
					bottom: 0,
					x:"center",
					icon: 'rect',
					itemWidth: 10,  // 设置宽度
					itemHeight: 2, // 设置高度
					itemGap: 20, // 设置间距
					textStyle: {
						color: "#E4E5E5",
						fontSize: "12",
					}
				},
				grid: {
					top:70,
					  left: 0,
					  right: 10,
					  bottom: 35,
					 containLabel: true
				},
				xAxis: {
					type: 'category',
					boundaryGap: false,
					  axisLine: {
						//坐标轴轴线相关设置
						onZero: ' ', //X 轴或者 Y 轴的轴线是否在另一个轴的 0 刻度
						onZeroAxisIndex: '12', //当有双轴时，可以用这个属性手动指定，在哪个轴的 0 刻度上
						// symbol: ['none', 'path://M250 150 L150 350 L350 350 Z'], //轴线两边的箭头
						lineStyle: {
							  color: '#3E6B6A', //坐标轴线线的颜色
							 width: '1' //坐标轴线线宽
						},
					  },
				  axisLabel: {
					//x轴刻度不展示最大和最小
					fontSize: '12',
					color: '#939799',
					showMaxLabel: false,
					showMinLabel:false,
					interval: 18,
				  },
				  data: nettime,
				},
				yAxis: {
					  type: 'value',
					  splitLine: {
					//网格线
						lineStyle: {
							color: '#2F4547', //坐标轴线线的颜色
							width: '1' //坐标轴线线宽
						  }
					  },
					  axisLabel: {
						fontSize: '12', //文字的字体大小
						color: '#A9ADAF',
						margin: 20, //刻度标签与轴线之间的距离。
						formatter: function(value, index){
							if(value > 1024) {
								return Math.round(value / 1024) + "MB/s";
							} else {
								if(value == 0) {
									return 0 + "MB/s"
								}else {
									return (value / 1024).toFixed(2) + "MB/s";
								}
							}
						},
					  },
				},
				series: eachseries
			};
		}
		
		netChart.setOption(option);
	}
	//磁盘IO
	var initDiskChart = function(bpsdata,iosdata){
		diskChart = echarts.init(document.getElementById('diskChart'));
		if(bpsdata['y_val'].length == 0 || bpsdata['x_time'].length == 0){
			option = {
					title: {
				          text: LANG.UI_SYSTEM_MONITOR_NULL_DATA,
				          x: "center",
				          y: "center",
				          textStyle: {
				            color: "white",
				            fontWeight: "normal",
				            fontSize: 16,
				          },
				        },
			}
			if(diskChart != undefined){
				diskChart.clear(); 
			}
		}else {
			var iodata = [];
			iodata['y_val'] = [];
			for(var i in iosdata['y_val']) {
				iodata['y_val'][i] = iosdata['y_val'][i]
			}
			var eachseries = [];
			var color = ["#56FF3E","#00FFFF","#FFC000","#63C38C"];
			var j = 0;
			for(var i in iodata['y_val']) {
				if(!initDiskFlag){
					diskdataArr[i].shift();
					diskdataArr[i].push(iodata['y_val'][i][0]);
				}else{
					diskdataArr[i] = [];
					diskdataArr[i] = iodata['y_val'][i].reverse();
				}
				let oneLine = {};
				oneLine.name = i;
				oneLine.type = "line";
				oneLine.showSymbol = false,
				oneLine.symbol = 'none',
				oneLine.smooth = true,
				oneLine.color = color[j%4];
				oneLine.data = diskdataArr[i];
				eachseries.push(oneLine);
				j++;
			}
			if(!initDiskFlag){
				disktime.shift();
				disktime.push(bpsdata['x_time'][0]);
			}else {
				disktime = [];
				disktime = bpsdata['x_time'].reverse();
			}
			initDiskFlag = false;
			
			option = {
				title: {
					  text: '磁盘IO',
					left: 5,
					textStyle: {
					  //字体粗细 'normal','bold','bolder','lighter',100 | 200 | 300 | 400...
					  fontWeight: 'normal',
					  color: '#4DCDCD',
					  //字体大小
					  fontSize: 14
					},
					subtext: "单位（次/秒）",
					  subtextStyle:{ // 设置二级标题的样式
						color:"#A9ADAF",
						fontSize: 12
					  }
				},
				tooltip: {
					trigger: 'axis',
					icon: "circle", 
					backgroundColor:'rgba(0, 0, 0, 0.5)',
					textStyle: {
					  color: 'white',
					},
					formatter: function (params) {
					  var relVal = params[0].name
					  for (var i = 0, l = params.length; i < l; i++) {
						  var pvalue = params[i].value + '次/秒';
						  relVal += '<br/>' + ' <span style="display:none;margin-right:5px;width:10px;height:10px;border-radius: 50%!important;background-color:'+ params[i].color +';"></span>' +'  '+ params[i].seriesName +':  '+ pvalue;
					  }
					  return relVal;
				  },
					borderWidth:'0',
					axisPointer: {
						lineStyle: {
						  color: "#10999B",
						  type: "solid",
					  },
					},
				},
				legend: {
					type: 'scroll',
					pageIconColor:"white",
					orient: 'horizontal',
					bottom: 0,
					x:"center",
					icon: 'rect',
					itemWidth: 10,  // 设置宽度
					itemHeight: 2, // 设置高度
					itemGap: 20, // 设置间距
					textStyle: {
						color: "#E4E5E5",
						fontSize: "12",
					}
				},
				grid: {
					top:70,
					left: 10,
					right: 10,
					bottom: 35,
					containLabel: true
				},
				xAxis: {
					type: 'category',
					boundaryGap: false,
					  axisLine: {
						//坐标轴轴线相关设置
						onZero: ' ', //X 轴或者 Y 轴的轴线是否在另一个轴的 0 刻度
						onZeroAxisIndex: '12', //当有双轴时，可以用这个属性手动指定，在哪个轴的 0 刻度上
						// symbol: ['none', 'path://M250 150 L150 350 L350 350 Z'], //轴线两边的箭头
						lineStyle: {
							  color: '#3E6B6A', //坐标轴线线的颜色
							 width: '1' //坐标轴线线宽
						},
					  },
				  axisLabel: {
					//x轴刻度不展示最大和最小
					fontSize: '12',
					showMaxLabel: false,
					showMinLabel:false,
					color: '#939799',
					interval: 18,
				  },
				  data: disktime,
				},
				yAxis: {
					  type: 'value',
					  splitLine: {
					//网格线
						lineStyle: {
							color: '#2F4547', //坐标轴线线的颜色
							width: '1' //坐标轴线线宽
						  }
					  },
					  axisLabel: {
						fontSize: '12', //文字的字体大小
						color: '#A9ADAF',
						margin: 20, //刻度标签与轴线之间的距离。
					  },
				},
				series: eachseries,
			};
		}
		
		diskChart.setOption(option);
	}

	

	//更新告警信息
	var updateAlarmTips = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
			setAlarmInfo(d);
		});
	}
	/**
	 * 设置顶部告警ICON的样式
	 * 无告警:	label-info
	 * 有警告告警:	label-warning
	 * 有错误告警:	label-danger
	 */
	var setAlarmLabelIconClour = function(id, data){
		var clourSytel = "label-info";
		if(data.warn > 0){
			clourSytel = "label-warning";
		}
		if(data.error > 0){
			clourSytel = "label-danger";
		}
		$(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
	}
	//设置告警信息
	var setAlarmInfo = function(d){
		var d = JSON.parse(d);
		$('.badgemark').remove();
		//初始化告警提示
		$('#alarmtask').html(d.alarm.task.error + d.alarm.task.warn);
		$('#alarmsystem').html(d.alarm.system.error + d.alarm.system.warn);

		var total = d.alarm.task.error + d.alarm.task.warn + d.alarm.system.error + d.alarm.system.warn;
		var errorTotal = d.alarm.task.error + d.alarm.system.error;
		var badgeType = "badge-warning";
		var tips = "";
		if(errorTotal > 0){
			badgeType = "badge-danger";
		}
		if(total > 0){
			var tips = '<span class="badge ' + badgeType + ' badgemark">' + total + '</span>';
		}
		$('#alarmtotal').find('span').remove();
		$('#alarmtotal').append(tips);

		setAlarmLabelIconClour(".alarmhreftask", d.alarm.task);
		setAlarmLabelIconClour(".alarmhrefsystem", d.alarm.system);


		//初始化任务提示
		$('#topcurrenttask').html(d.task.current);
		$('#tophistorytask').html(d.task.history);

		//初始化菜单提示
		var storage_manager = $('.page-sidebar-menu').find('a[name=storage_manager]');
		var authorization_module = $('.page-sidebar-menu').find('a[name=authorization_module]');
		var resource_manager = $('.page-sidebar-menu').find('a[name=resmanagement]');
		var system_manager = $('.page-sidebar-menu').find('a[name=sysmanagement]');

		if(d.storage){
			//管理备份存储
			var tips = '<span class="badge badge-warning badgemark">' + d.storage + '</span>';
			storage_manager.append(tips);
			//父级-系统管理
			resource_manager.append(tips);
		}
		if(d.lisence){
			//系统授权
			var tips = '<span class="badge badge-danger badgemark">' + d.lisence + '</span>';
			authorization_module.append(tips);
			//父级-系统管理
			system_manager.append(tips);
		}

	}
	/**
	 * 设置顶部告警ICON的样式
	 * 无告警:	label-info
	 * 有警告告警:	label-warning
	 * 有错误告警:	label-danger
	 */
	var setAlarmLabelIconClour = function(id, data){
		var clourSytel = "label-info";
		if(data.warn > 0){
			clourSytel = "label-warning";
		}
		if(data.error > 0){
			clourSytel = "label-danger";
		}
		$(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
	}
	//密码即将过期提示
	var initLoginHistory = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getLoginHistory'}, function(d){
			var data = JSON.parse(d);
			if(data.tips !=""){
				UIToastr.showWarning(LANG.UI_TENANT_HOME_PASSWORD_EXPIRE, data.tips);
			}
		});
	}
	//初始化是否为租户内部,并设置任务窗口事件
	var initUserType = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getUserExtendInfo',p:{}}, function(d){
			var data = JSON.parse(d);
			if(data.tenantuuid == "" && data.username == "admin"){
				addManagerListener();
			}else if(data.tenantuuid == ""){
				addOperatorListener();
			}
		});

	}
	//添加管理员事件
	var addManagerListener = function(){
		$('#moretaskinfo').on('click', function(){
			CTLHORMENU('bakandrec');
			var url = './content/platform/jobs/history_job.php';
			var urlMark = '?history_job';
			if($('#currenttaskli').hasClass("active")){
				url = './content/platform/jobs/jobs.php';
				urlMark = '?current_job';
			}
			LOCATION(url,'task');
			History.pushState({url:url}, "", urlMark);
		});
		$('.currentjob').off().on('click', function(){
			CTLHORMENU('bakandrec');
		})
	}

	//添加操作员事件
	var addOperatorListener = function(){
		$('#moretaskinfo').on('click', function(){
			CTLHORMENU('bakandrec');
			var url = './content/platform/jobs/history_job.php';
			var urlMark = '?history_job';
			if($('#currenttaskli').hasClass("active")){
				url = './content/platform/jobs/jobs.php';
				urlMark = '?current_job';
			}
			LOCATION(url,'task');
			History.pushState({url:url}, "", urlMark);
		});
		$('.currentjob').off().on('click', function(){
			CTLHORMENU('bakandrec');
		})
	}
	//获取中间模块备份数据
	const getProtectDataNew = () => {
		pAjaxRequest({}, "/api/v1/homepage/pretected/data", "GET", function (result) {
            if (result.success) {
                //设置受保护数据
                initializeItems(result.data);
            }

        });
	}
	//初始化中间模块数据
	const initializeItems = (data) => {
		const backupSwiperWrapper = $('#backup_pane .swiper-wrapper');
        const cdpSwiperWrapper = $('#cdp_pane .swiper-wrapper');
        const copySwiperWrapper = $('#copy_pane .swiper-wrapper');
        backupSwiperWrapper.empty();
        cdpSwiperWrapper.empty();
        copySwiperWrapper.empty();
		let allinfo = {};
		//统计cdpmodule模块showflag的个数 如果为1 则需要多显示任务总数和备份集个数
		let count = Object.values(data.cdpmodule).filter(item => item.show == true).length;;
		for (let modulekey in data) {
			allinfo[modulekey] = {
				'protected': 0,
				'notprotected': 0,
				'modulehtml': ''
			}
            for (let key in data[modulekey]) {
				
                if (!data[modulekey][key].show) {
                    continue;
                }
				// 这里要加上总数
				allinfo[modulekey]['protected'] += data[modulekey][key].protected;
				allinfo[modulekey]['notprotected'] += data[modulekey][key].total - data[modulekey][key].protected;
				let itemhtml  = ``;
				itemhtml =  `<div class="swiper-slide">
										<div class="small-label">${setItemName(key)}</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/protect-${key}.svg" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">${data[modulekey][key].protected}</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">${data[modulekey][key].protectData.value}</span>
                                						<span class="item-text-unit">${data[modulekey][key].protectData.unit}</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>`
				if(modulekey == 'cdpmodule'){
					//实时模块HTML结构要调整
					
					itemhtml =  `<div class="swiper-slide">
										<div class="small-label">${setItemName(key)}</div>
										<li class="d-flex">
											<div class="cdp-box">
												<div class="box-img">
													<img src="/img/platform/sangfor/protect-${key}.svg" alt="">
												</div>
												<div class="box-info">
													<div>
														<div class="top-num">
															<span class="item-text-num">${data[modulekey][key].protected}</span>
														</div>
														<span>受保护设备</span>
													</div>
												</div>
											</div>
											${count != 1 ? '' : `
												<div class="cdp-box">
												<div class="box-img">
													<img src="/img/platform/sangfor/protect-task.svg" alt="">
												</div>
												<div class="box-info">
													<div>
														<div class="top-num">
															<span class="item-text-num">${data[modulekey][key].taskNum}</span>
														</div>
														<span>任务总数</span>
													</div>
												</div>
											</div>`}
											${count != 1 ? '' : `
												<div class="cdp-box">
												<div class="box-img">
													<img src="/img/platform/sangfor/protect-backup-set.svg" alt="">
												</div>
												<div class="box-info">
													<div>
														<div class="top-num">
															<span class="item-text-num">${data[modulekey][key].backupSet}</span>
														</div>
														<span>备份集个数</span>
													</div>
												</div>
											</div>`}
											<div class="cdp-box">
												<div class="box-img">
													<img src="/img/platform/sangfor/protect-backup-data.svg" alt="">
												</div>
												<div class="box-info">
													<div>
														<div class="top-num">
															<span class="item-text-num">${data[modulekey][key].protectData.value}</span>
															<span class="item-text-unit">${data[modulekey][key].protectData.unit}</span>
														</div>
														<span>备份数据</span>
													</div>
												</div>
											</div>	
										</li>
									</div>`
				}
				allinfo[modulekey]['modulehtml'] += itemhtml
			}
			
			
		}
		//设置每个模块显示
		for (let key in allinfo) {
			var firsthtml = `<div class="swiper-slide first-slide">
								<li class="device-box">
									<div class="small-label">设备概览</div>
									<div id="${key}_devicePie" class="pie-chart"></div>
								</li>
							</div>`;
			var allshowhtml = firsthtml + allinfo[key]['modulehtml']						
			if (key == "timemodule") {
				backupSwiperWrapper.append(allshowhtml)
			} else if (key == "cdpmodule") {
				cdpSwiperWrapper.append(allshowhtml)
			} else if (key == "copymodule") {
				copySwiperWrapper.append(allshowhtml)
			}
		}
		//初始化各个大模块第一个echart
		var backupPie = echarts.init(document.getElementById("timemodule_devicePie"));
		var cdpPie = echarts.init(document.getElementById("cdpmodule_devicePie"));
		var copyPie = echarts.init(document.getElementById("copymodule_devicePie"))
		for (let key in allinfo) {
			var	option = {
				tooltip: {
					show:false,
				},
				legend: {
					icon: "none",
					orient: 'vertical',//设置图例的方向
					right: 15,
					top: 0,
					bottom: 0,
					itemGap:17,//设置图例的间距
					//自定义图例后面的数字样式
					formatter: function (name) {
						if(name == LANG.UI_CLIENT_PROTECTED){
							let targetProtected = allinfo[key]['protected'];
							let targetNotProtected = allinfo[key]['notprotected'];
							
							let arr = `{a|${targetProtected}}{b|${LANG.UI_CLIENT_PROTECTED}}\n{c|${targetNotProtected}}{b|${LANG.UI_VM_REPORT_NOIN_BACKUP}}`;
							return arr;
						}else{
							return '';
						}
					},
					textStyle: {
						rich: {
							a: {
								fontSize: 24,
								color: "#64C2F8",
								padding:[0,3,12,10],
								fontWeight:"500",
								align:"right",
							},
							b: {
								color: "#A9ACAD",
								fontSize: 14,
								padding: [0, 0, 12, 0], // 右边距3px，下边距12px
								fontWeight:"500",
								align:"right",
							},
							c: {
								fontSize: 24,
								color: "#E9ECEF",
								padding: [0, 3, 12, 10], // 右边距3px，下边距12px
								align:"right",
							},
						},
					}
				},
				series: [
					{
						name: LANG.UI_PUBLIC_HOST,
						type: "pie",
						center: ["20%", "50%"],
						radius: ["60%", "80%"],
						color: ["#64C2F8","#E9ECEF"],
						label: { show: false },
						hoverAnimation: false,
						labelLine: { show: false },
						data: [
							{ value: allinfo[key]['protected'], name: LANG.UI_CLIENT_PROTECTED },
							{ value: allinfo[key]['notprotected'], name: LANG.UI_VM_REPORT_NOIN_BACKUP },
						]
					},
				]
			};
			switch (key) {
				case 'timemodule':
					backupPie.setOption(option);
				break;
				case 'cdpmodule':
					cdpPie.setOption(option);
				break;
				case 'copymodule':
					copyPie.setOption(option);
				break;
			}
		}
		if(count ==  1){
			//重新设置实时模块宽度
			$('#cdpSwiper .swiper-wrapper .swiper-slide:not(.first-slide) ').css('width', 'calc(100% - 189px)');
		}


		 //这里需要统计各模块showflag的个数 如果为0则隐藏该模块
        let backupnoauthflag =  Object.values(data["timemodule"]).every(item => item.show == false); //如果为true 代表没有授权
        let cdpnoauthflag =  Object.values(data["cdpmodule"]).every(item => item.show == false); //如果为true 代表没有授权
        for (let modulekey in data) {
            let allshowflag =  true;
            allshowflag = Object.values(data[modulekey]).every(item => item.show == false);
            //这里需要动态加上active
            if(allshowflag == false){ //代表授权了 就直接显示
                if (modulekey == "timemodule") {
                    $(".dataprotectdiv #dataTabs .backup").addClass("active").attr("show","true").show();
                    $("#backup_pane").addClass("active");
                } else if (modulekey == "cdpmodule") {
                    $(".dataprotectdiv #dataTabs .cdp").attr("show","true").show();
                    if(backupnoauthflag){
                        $(".dataprotectdiv #dataTabs .cdp").addClass("active");
                        $("#cdp_pane").addClass("active");
                    }
                    //如果定时模块没授权 则加上active
                } else if (modulekey == "copymodule") {
                    $(".dataprotectdiv #dataTabs .copy").attr("show","true").show();
                    if(backupnoauthflag && cdpnoauthflag){
                        $(".dataprotectdiv #dataTabs .copy").addClass("active");
                        $("#copy_pane").addClass("active");
                    }
                    
                }
            }
        }

		//给显示出来的第一个li加上active样式
		var allLi = $('.dataprotectdiv .nav-tabs li[show="true"]');
		var index= allLi.length - 1;
		$(allLi).eq(0).addClass("active");
		// 首尾加圆角
		$(allLi).eq(0).css("cssText","border-radius: 4px 0 0 4px !important;");
		$(allLi).eq(0).find("a").css("cssText","border-radius: 4px 0 0 4px !important;");
		$(allLi).eq(index).css("cssText","border-radius: 0 4px 4px 0 !important;");
		$(allLi).eq(index).find("a").css("cssText","border-radius: 0 4px 4px 0 !important;");



	}

	    //设置数据保护每个item的名字
    const setItemName = (key) => {
        let itemname = '';
        switch (key) {
            case 'vm':
                itemname = LANG.UI_BACKUP_DATA_MODULE_VM;
                break;
            case 'private_cloud':
                itemname = LANG.UI_BACKUP_DATA_MODULE_PRIVATE_CLOUD;
                break;
            case 'public_cloud':
                itemname = LANG.UI_BACKUP_DATA_MODULE_PUBLIC_CLOUD;
                break;
            case 'file':
                itemname = LANG.UI_FILE_FILE;
                break;
            case 'nas':
                itemname = 'NAS';
                break;
            case 'hadoop':
                itemname = 'Hadoop';
                break;
            case 'obs':
                itemname = LANG.UI_VISUAL_OBS;
                break;
            case 'machine':
                itemname = LANG.UI_BACKUP_DATA_MODULE_OS;
                break;
            case 'vol':
                itemname = LANG.UI_VOL_CDP_RECOVER_VOL;
                break;
            case 'k8s':
                itemname = 'Kubernetes';
                break;
            case 'db':
                itemname = LANG.UI_AGENT_MODULE_DB;
                break;
            case 'm365':
                itemname = 'Microsoft 365';
                break;
        }
        return itemname;

    }
	return {
		//main function to initiate the module
		init: function () {
			initConfig();
			checkLisence(); //检查系统授权
			initUserType();
			initListener();
			initAlarmTips();
			initLoginHistory();
			

			initNodeUUID();
			// initLisenceInfo();
            intStorageName();
			getProtectDataNew();
			initAlarmData();
            initPlanTable();
			History.pushState({url:"./content/platform/databackup_center.php"}, document.title, "?homepage");;
        },
		//定义更新告警信息接口,添加虚拟化中心成功,添加存储成功后调用
		// updateTopAlarmTips: function(){
		// 	updateAlarmTips();
		// }
	};

}();

jQuery(document).ready(function() {
	if($(".storageresourcediv").length==0) return;
	DataBackupCenter.init();
});
