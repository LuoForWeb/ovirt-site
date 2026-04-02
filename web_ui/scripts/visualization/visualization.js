var Visualization = function(){
	"use strict";
	var jobTimer, vmTimer;
	var vcenterChart, fsChart, dbChart, storageChart, netInflowChart, netOutflowChart, dailyStorageChart, cpuChart, memoryChart, dailyVmChart, jobPieChart ;
	var initNetworkFlag = false,netLineInitFlag = false, cpuLineInitFlag = false,initFlag = false, initSpeedChartFlag = false, initProgressFlag = false,initStoragePieFlag = false,initDailyVmFlag = false;
	var timerTask = {};
	var _taskuuid = null;
	var ball1, ball2, ball3, ball4, ball5, ball6, ball7, ball8;//定义中间任务运行动画的小球
	var initAnime = false;
	var _nextTime = 0;
	var taskList = [];
	var speedList = [];
	var initLunFlag = false;
	var chartList = [], storagePieChartList = [];
	var initBackup = false, initRecover = false,initProtectNum = false,initProtectStorage = false;
	var _typeList = [];	//
	var initTimeflag = false;	//初始化系统时间标志
	var initJobFlag = false, initVmFlag = false;	//初始化最近任务和虚拟机列表滚动条标志
	var logoutFlag = false; //登出标志
	
	//初始化图表统计数据，5分钟一次
	var initDataSurvery = function(){
		function init(){
			$.post(CONF.AJAXPATH, {m:CONF.M.VISUAL,f:'getDataSurvery',p:{}}, function(d){
				var data = JSON.parse(d);
				initVcenterChart(data.vcenter_data);		//初始化虚拟化中心数据
				initFsChart(data.fs_data);				//初始化文件代理数据
				initDbChart(data.db_data);				//初始化数据库代理数据
				initStorageChart(data.storage_data);		//初始化存储统计数据
				initDailyStorageChart(data.daily_storages);	//初始化每日存储用量统计
				initDailyVmChart(data.daily_vms);			//初始化每日备份虚拟机统计
				initJobPieChart(data.today_jobs);			//初始化当日任务等待和完成次数统计
				initLatestJob(data.latest_jobs);			//初始化最近完成任务列表
				initLatestVms(data.latest_vms);				//初始化最近备份虚拟机信息列表
			}).complete(function() {timerTask.visualSurvery = setTimeout(function(){init();}, 300000);});
		}
		init();
	}
	
	
	//判断当日任务界面显示 status 1 等待界面 2 运行界面 3 无任务界面
	var initJobUI = function(status){
		switch(status){
			case 1:							//等待界面
				$('.backup-ball').hide();
				$('#backupDiv').empty();
				$('.recover-ball').hide();
				$('#recoveryDiv').empty();
				$('#no-job').css('display', 'none');
				$('#job-run').css('display','none');
				$('#job-wait').css('display','flex');
				initBackup = false;
				initRecover = false;
				initLunFlag = false;
				$('#myCarousel').carousel('pause');
				break;
			case 2:							//运行界面
				$('#job-run').css('display','block');
				$('#job-wait').css('display','none');
				$('#no-job').css('display', 'none');
				break;
			case 3:							//无任务界面
				$('.recover-ball').hide();
				$('.backup-ball').hide();
				$('#backupDiv').empty();
				$('#recoveryDiv').empty();
				$('#no-job').css('display', 'block');
				$('#job-run').css('display','none');
				$('#job-wait').css('display','none');
				initBackup = false;
				initRecover = false;
				initLunFlag = false;
				$('#myCarousel').carousel('pause');
				break;
			default:
				$('.recover-ball').hide();
				$('.backup-ball').hide();
				$('#backupDiv').empty();
				$('#recoveryDiv').empty();
				$('#no-job').css('display', 'block');
				$('#job-run').css('display','none');
				$('#job-wait').css('display','none');
				initBackup = false;
				initRecover = false;
				initLunFlag = false;
				$('#myCarousel').carousel('pause');
				_taskuuid = null;
				break;
		}
	}
	
	//加载等待任务信息
	var initWaitJob = function(){
		
		function init(){
			$.post(CONF.AJAXPATH, {m:CONF.M.VISUAL,f:'getWaitJobData',p:{}}, function(d){
				var data = JSON.parse(d);
				initJobUI(data.ui_status);
				if(!data.taskuuid){
					_nextTime = 0;  //如果没有任务，清空下一次开始时间
					$(".timecountdown").empty();
					return;
				}
				var des = LANG.UI_PUBLIC_VM + ': ' + data.vm_num + LANG.UI_PUBLIC_NUM;
				if(data.agentuuid != ""){
					des = LANG.UI_VISUAL_APPLIANCE + ': ' + data.agent_num + LANG.UI_PUBLIC_NUM;
				}
				$('#wait-vms').html(des);
				var des = LANG.UI_SEARCH_TASK_NAME + ': ' + data.task_name;
				$('#wait-task').html(des);
				
//				var time = new Date;
//			    var timeZone = -time.getTimezoneOffset() / 60; //获取时区
				var timeConfig = {
					timeText: data.wait_time_des, //倒计时时间
			        timeZone: 8, //时区 默认东8区
			        style: "slide", //显示的样式，可选值有flip,slide,metal,crystal
			        color: "black", //显示的颜色，可选值white,black
			        width: 0, //倒计时宽度
			        textGroupSpace: 15, //天、时、分、秒之间间距
			        textSpace: 0, //数字之间间距
			        reflection: 0, //是否显示倒影
			        reflectionOpacity: 10, //倒影透明度
			        reflectionBlur: 0, //倒影模糊程度
			        dayTextNumber: 3, //倒计时天数数字个数
			        displayDay: 0, //是否显示天数
			        displayHour: !0, //是否显示小时数
			        displayMinute: !0, //是否显示分钟数
			        displaySecond: !0, //是否显示秒数
			        displayLabel: 0, //是否显示倒计时底部label
			        onFinish: function() {}
				};
				//初始化倒计时
				if(_nextTime == 0){
					 $(".timecountdown").jCountdown(timeConfig); //任务倒计时
					_nextTime = data.next_time;
				}
				//最近一个任务改变时或任务下次运行时间提前时刷新倒计时时间
				if(_taskuuid != data.taskuuid || _nextTime > data.next_time){
					$(".timecountdown").jCountdown(timeConfig);
					_nextTime = data.next_time;
				}
				_taskuuid = data.taskuuid;
				data = null;
				timeConfig = null;
			}).complete(function() {
				timerTask.waitJob = setTimeout(function(){init();}, 2000);
				});
		}
		
		init();
		
	}
	
	//初始化运行任务实时监控
	var initRealtimeData = function(){
		function init(){
			$.post(CONF.AJAXPATH, {m:CONF.M.VISUAL,f:'getRealtimeData',p:{}}, function(d){
				var data = JSON.parse(d);
				if(data.length == 0 ){
					taskList = [];
					$('#myCarousel .carousel-inner').empty();
					return;
				}  
				_typeList = [];
				//移除不在任务列表的列表元素
				if(taskList.length !=0){
					var dataTask = data[0].task_list;
					for(var j=0;j<taskList.length;j++){
						if($.inArray(taskList[j], dataTask) == -1){
							if($('#' + taskList[j]).hasClass('active')){
								$('#' + taskList[j]).removeClass('active');
							}
							$('#' + taskList[j]).remove();
							delete speedList[taskList[j]];
							delete chartList[taskList[j]];
							delete storagePieChartList[taskList[j]];
							taskList.splice($.inArray(taskList[j],taskList),1)
							$('#' + taskList[0]).addClass('active');
						}
					}
				}
				for(var i=0;i<data.length; i++){
					var html = '';
					if($.inArray(data[i].task_uuid, taskList) == -1){
						if(i==0){
							html += '<div id="'+data[i].task_uuid+'" class="item active">';
						}else{
							html += '<div id="'+data[i].task_uuid+'" class="item">';
						}
						html +='<div class="bgline" style="width: 24%;height:230px;">'+
							'<div style="border-radius:10px;position:relative;width: 148px;height: 145px;margin: 15px auto;padding-top:10px;border: 1px solid #275376;">'+
						'<div class="image-border image-border1"></div><div class="image-border image-border2"></div>'+
						'<div class="image-border image-border3"></div><div class="image-border image-border4"></div>'+
						'<div class="runVmChart" style="width:148px;height: 120px;margin: 0 auto;"></div></div><div class="run-details"></div></div>'+
						'<div style="width: 76%;padding:0 10px;"><p class="run-vmname" ></p><div style="display:flex;"><div style="width:1%;"></div>'+
						'<div style="border-radius:10px;position:relative;width: 32%;border: 1px solid #275376;height: 155px;">'+
							'<div class="image-border image-border1"></div><div class="image-border image-border2"></div>'+
							'<div class="image-border image-border3"></div><div class="image-border image-border4"></div>'+
							'<p style="margin: 0 10px 15px;font-size:14px;">'+LANG.UI_VISUAL_SPEED+'</p>'+
							'<div class="speedLineChart" style="width:162px;margin:0 auto;height:55px;"></div><p class="vm-speed"></p></div>'+
						'<div style="width:1%;"></div>'+
						'<div class="run-progress" style="border-radius:10px;position:relative;width: 32%;border: 1px solid #275376;height: 155px;position:relative;">'+
							'<div class="image-border image-border1"></div>'+
							'<div class="image-border image-border2"></div>'+
							'<div class="image-border image-border3"></div>'+
							'<div class="image-border image-border4"></div>'+
							'<p style="font-size: 14px;position:absolute; left:5px;top:0;">'+LANG.UI_VISUAL_PROGRESS+'</p>'+
							'<div class="progressPieChart" style="width:160px;margin: 0 auto;"></div>'+
							'<p class="vm-progress" ></p></div><div style="width:1%;"></div>'+
						'<div style="border-radius:10px;position:relative;width: 32%;border: 1px solid #275376;height: 155px;position:relative;">'+
							'<div class="image-border image-border1"></div><div class="image-border image-border2"></div>'+
							'<div class="image-border image-border3"></div><div class="image-border image-border4"></div>'+
							'<p style="font-size: 14px;position:absolute; left:5px;top:0;">'+LANG.UI_VISUAL_SIZE+'</p><div class="storagePieChart" style="height:120px;"></div>'+
							'<p class="run-capacity"></p></div><div style="width:1%;"></div></div></div>';
						html += '</div>';
						$('#myCarousel .carousel-inner').append(html);
						taskList.push(data[i].task_uuid);
						initSpeedChartFlag = false;
						initProgressFlag = false;
						$('#myCarousel').carousel({
							interval: 30000,
						});
						if(!initLunFlag){
							
							$('#myCarousel').on('slid.bs.carousel', function (event) {
								var $items = $(event.relatedTarget);
								var taskuuid = $items[0].id;
								storagePieChartList[taskuuid].resize();
							});
						    initLunFlag = true;
						}
						
						if(!initBackup && (data[i].task_type == 1 || data[i].task_type == 28)){
							runningBackupJob();//任务运行时动画
							initBackup = true;
						}
						if(!initRecover){
							if(data[i].task_type == 2 || data[i].task_type == 8 || data[i].task_type == 29){
								runningRecoverJob();//任务运行时动画
								initRecover = true;
							}
						}
					}
					var name = LANG.UI_VISUAL_VM_NAME;
					//代理名
					if(data[i].module_type == 3){
						name = LANG.UI_VISUAL_APPLIANCE_NAME;
					}else if(data[i].module_type == 4){
						name = LANG.UI_DB_NAME;
					}
					var des = name +': '+ data[i].vm_name +'<span style="color:#DCB2FF;font-size:14px;margin-left:5px;">---------'+data[i].task_type_des+'</span>'
					$('#'+data[i].task_uuid +' .run-vmname').html(des);
					var html = '<p style="overflow: hidden;white-space: nowrap;text-overflow:ellipsis;" title="'+data[i].task_name+'">'+LANG.UI_SEARCH_TASK_NAME+': '+data[i].task_name+'</p>';
					//虚拟机
					if(data[i].module_type == 2){
						html += '<p>'+LANG.UI_VCENTER_VM+': '+data[i].vm_num+ LANG.UI_PUBLIC_NUM+'</p>';
					}else if(data[i].module_type == 3){
						//文件
						html += '<p>'+LANG.UI_AGENT_MODULE_FILE+': '+data[i].vm_num+ LANG.UI_PUBLIC_NUM+'</p>';
					}else if(data[i].module_type == 4){
						//数据库
						html += '<p>'+LANG.UI_AGENT_MODULE_DB+': '+data[i].vm_num+ LANG.UI_PUBLIC_NUM+'</p>';
					}
					
					
					$('#'+data[i].task_uuid +' .run-details').html(html);
					initRunVmChart(data[i]);
					initspeedLineChart(data[i]);
					//数据库任务没有进度
					if(data[i].module_type != 4){
						initProgressPieChart(data[i]);
					}else{
						$('#'+data[i].task_uuid +' .run-progress').hide();
					}
					initStoragePieChart(data[i]);
					_typeList.push(data[i].task_type);
					
				}
				if($.inArray(1, _typeList) == -1 && $.inArray(28, _typeList) == -1){
					$('.backup-ball').hide();
					$('#backupDiv').empty();
					initBackup = false;
				}
				if($.inArray(2, _typeList) == -1 && $.inArray(8, _typeList) == -1 && $.inArray(29, _typeList) == -1){
					$('.recover-ball').hide();
					$('#recoveryDiv').empty();
					initRecover = false;
				}
				if(data.length == 1){
					$('#myCarousel').hover(function(){
						$('#myCarousel a').css('display', 'none');	
					});
				}else{
					$('#myCarousel').hover(function(){
						$('#myCarousel a').css('display', 'block');	
					},function(){
						$('#myCarousel a').css('display', 'none');	
					});
				}
			}).complete(function() {timerTask.visualSurvery = setTimeout(function(){init();}, 2000);});
		}
		init();
	}
	
	
	
	//初始化最近备份虚拟机
	var initLatestVms = function(data){
		$('#latestvm-list').empty();
		if(data.length == 0) {
			var info = '<li style="text-align:center;color: #fff;font-size: 24px;">'+LANG.UI_VISUAL_NO_BACKUP_VM+'</li>';
			$('#latestvm-list').html(info);
			return;
		}
		var info = "";
		for(var i=0;i<data.length;i++){
			if(i%2 == 0){
				info+='<li>';
			}else{
				info +='<li style="color:#00ff84;">';
			}
			info += '<div>' +
						'<p style="text-align: center;width:10%;">'+data[i].id+'</p>' +
						'<p title="'+data[i].name+'" style="text-align: center;width:36%;overflow: hidden;white-space: nowrap;text-overflow:ellipsis;">'+data[i].name+'</p>' +
						'<p style="text-align: center;width:24%;">'+data[i].count+'</p>' +
						'<p style="text-align: center;width:30%;">'+data[i].last_time +'</p>' +
						'</div></li>';
		}
		$('#latestvm-list').html(info);
		//当长度大于3，初始化滚动条
		if(data.length > 3 && !initVmFlag){
			$('#latestVms').hover(clearTime(vmTimer), scrollTimer($('#latestVms'), vmTimer)).trigger("mouseleave");
			initVmFlag = true;
		}
	}
	
	//最近完成任务
	var initLatestJob = function(data){
		$('#latestjob-list').empty();
		if(data.length == 0) {
			var info = '<li style="text-align:center;color:#fff; font-size: 24px;">'+LANG.UI_VISUAL_NO_FINISH_JOB+'</li>';
			$('#latestjob-list').html(info);
			return;
		}
		var info = "";
		for(var i=0;i<data.length;i++){
			if(i%2 == 0){
				info+='<li>';
			}else{
				info +='<li style="color:#00ff84;">';
			}
			info += '<div>' +
						'<p style="text-align: center;width:10%;">'+data[i].id+'</p>' +
						'<p style="text-align: center;width:38%;overflow: hidden;white-space: nowrap;text-overflow:ellipsis;" title="'+data[i].task_name+'">'+data[i].task_name+'</p>' +
						'<p style="text-align: center;width:20%;">'+data[i].total_size+'</p>' +
						'<p style="text-align: center;width:32%;">'+data[i].resultdes + '/'+ data[i].finish_time +'</p>' +
						'</div></li>';
		}
		$('#latestjob-list').html(info);
		
		//当长度大于3，初始化滚动条
		if(data.length > 3 && !initJobFlag){
			$('#latestJobs').hover(clearTime(jobTimer), scrollTimer($('#latestJobs'), jobTimer)).trigger("mouseleave");
			initJobFlag = true;
		}
		
	}
	
	//计算时间描述
	var zero = function(n){
		if(n<10){
			n = '0' + n;
		}
		else{
		    n = '' + n;
		}
		return n;
	}
	var getSystemTime = function () {
		//获取系统时间
		$.post(CONF.AJAXPATH, {m:CONF.M.PLATFORM,f:'getDataSurvey'}, function(data){
			var data = JSON.parse(data);
			initSystemTime(data.survery.systemTime);
		});
	  }
	//初始化系统时间
    var initSystemTime = function(systime){
		if(initTimeflag) return;
    	clearTimeout(timerTask.DataBackupDataPicker);
    	var getTime = function(myDate){
    		var myDate = new Date(myDate);
    		var date = myDate.toLocaleDateString();
    		var year = myDate.getFullYear();
    		var month = zero(myDate.getMonth()+1); 
    		var ri = zero(myDate.getDate());
    		var hours = zero(myDate.getHours()); 
    		var minutes = zero(myDate.getMinutes());
    		var seconds = zero(myDate.getSeconds());
    		var weekIndex = myDate.getDay();
    		var weekList = [LANG.UI_VISUAL_SUNDAY,LANG.UI_VISUAL_MONDAY,LANG.UI_VISUAL_TUESDAY,LANG.UI_VISUAL_WEDNESDAY,LANG.UI_VISUAL_THURSDAY,LANG.UI_VISUAL_FRIDAY,LANG.UI_VISUAL_SATURDAY];
    		var week = weekList[weekIndex];
    		var time = hours + ":" + minutes + ":" + seconds;
    		var currentDate = year + "-" + month + "-" + ri;
    		var html = '<span style="font-size:37px;margin-right:3px;">'+time+'</span><span style="font-size:12px;">'+currentDate+'</span><p style="position: absolute; right: 29px;top:10px;font-size:12px;color:#fff;">'+week+'</p>';
    		$("#systemTime").html(html);
    		
    		//每天需要刷新时间
    		var refreshTime = ['00','04','08','12','16','20'];
    		//一天刷新六次
    		if(minutes =="00" && seconds == "00"){
    			if($.inArray(hours, refreshTime) != -1){
    				//刷新成功时间记录写入日志文件refresh_time.log
    				$.post(CONF.AJAXPATH, {m:CONF.M.VISUAL,f:'writeRefreshLog',p:{}}, function(d){
    				});
    				window.location.reload(true); //刷新当前页面
    			}
    		}
    		myDate = null; //清空对象
    	}
    	var updateDatePicker = function(mytime){
			var thistime = parseInt(mytime);
    		if(0 == $('#systemTime').size()){
        		clearTimeout(timerTask.DataBackupDataPicker);
        		return;
        	}
			getTime(thistime);
    		timerTask.DataBackupDataPicker = setTimeout(function(){
    			updateDatePicker(thistime + 1000)
    		}, 1000);
    	}
    	updateDatePicker(new Date(systime).getTime())
    	initTimeflag = true;
    }
	
	
	//滚动条
	var scrollContent = function (obj, timer) {
        var $self = obj.find("ul");
        var lineHeight = $self.find("li:first").height(); 
        $self.animate({
            "marginTop": -lineHeight + "px"
        }, 1000, function() {
            $self.css({
                marginTop: 0,
               
            }).find("li:first").appendTo($self);
        });
        
        timer = setTimeout(function() {
			scrollContent(obj, timer);
        }, 2000);
    }
	
	//清除计时器
	var clearTime = function(timer){
		clearTimeout(timer);
	}
	
	//滚动条计时器
	var scrollTimer = function(obj,timer){
		scrollContent(obj,timer);
	}
	
	//初始化中心圆球转动动画
	var initCenterAnime = function(){
		if(timerTask.cicle2){
			clearTimeout(timerTask.cicle2); //停掉上一次还未运行的定时器
		}
		$('#runvm-circle2').empty();
		if(initAnime){
			$('#runvm-circle').empty();
			var info = '<img style="width:70px;"  src="../img/visualization/visual-circle.png"><svg  style="display:block;" width="525" height="225" >' +
			'<ellipse cx="525" cy="225" rx="262" ry="112" style="fill: none;"/></svg>';
			$('#runvm-circle').html(info);
		}
		var path = anime.path('#runvm-circle ellipse');
		var motionPath = anime({
			  targets: '#runvm-circle img',
			  translateX: path('x'),
			  translateY: path('y'),
			  easing: 'linear',
			  duration: 12000,
			  direction: 'reverse',
			  loop: true
		});
		
		//延迟加载第二个圆球
		timerTask.cicle2 = setTimeout(function(){
			var info2 = '<img style="width:70px;" src="../img/visualization/visual-circle.png"><svg style="display:block;" width="525" height="225" >'+
	     	'<ellipse cx="525" cy="225" rx="262" ry="112" style="fill:none;"/></svg>';
			$('#runvm-circle2').html(info2);
			$('#runvm-circle2').show();
			var path1 = anime.path('#runvm-circle2 ellipse');
			var motionPath2 = anime({
				  targets: '#runvm-circle2 img',
				  translateX: path1('x'),
				  translateY: path1('y'),
				  easing: 'linear',
				  duration: 12000,
				  direction: 'reverse',
				  loop: true
				});
		},5000);
		
		initAnime = true;
	}
	
	//初始化监听事件
	var initListener = function (){
		//虚拟机和存储使用情况统计渐隐动画效果
		setTimeout(function(){ 
			$("#unprotectDes").css('animation', 'fade 20000ms infinite');
			$("#unprotectDes").css('-webkit-animation', 'fade 20000ms infinite');
			$("#fsunprotectDes").css('animation', 'fade 20000ms infinite');
			$("#fsunprotectDes").css('-webkit-animation', 'fade 20000ms infinite');
			$("#dbunprotectDes").css('animation', 'fade 20000ms infinite');
			$("#dbunprotectDes").css('-webkit-animation', 'fade 20000ms infinite');
			
			
			$("#usedDes").css('animation', 'fade 20000ms infinite');
			$("#usedDes").css('-webkit-animation', 'fade 20000ms infinite');
		},12000);
		//对应的浏览器和版本不支持动画效果
		if($.browser.msie || ($.browser.mozilla && $.browser.version == "11.0")){
			$('#runvm-circle').hide();
			$('#runvm-circle2').hide();
		}else{
			//中心动画
			initCenterAnime();
		}
		//canvas定时器动态时钟
		paintClock(); 
		
		//初始化保护主机和代理滚动动画
    	setTimeout(function(){
    		//如果只有虚拟机|文件|数据库 其中一个
    		if(($('.vcenterDiv').hasClass('swiper-slide') && !$('.fsHostDiv').hasClass('swiper-slide') && !$('.dbHostDiv').hasClass('swiper-slide')) || 
			($('.fsHostDiv').hasClass('swiper-slide') && !$('.vcenterDiv').hasClass('swiper-slide') && !$('.dbHostDiv').hasClass('swiper-slide')) || 
			($('.dbHostDiv').hasClass('swiper-slide') && !$('.fsHostDiv').hasClass('swiper-slide') && !$('.vcenterDiv').hasClass('swiper-slide')) ){
    			$('.swiper-pagination').hide();	//隐藏切换图标
    		}else{
    			var swiper = new Swiper('.swiper-container', {
            		pagination: {
            		    el: '.swiper-pagination',
            		    clickable : true
            		},
        			autoplay: {             //自动切换
        				delay: 5000,
        				disableOnInteraction: false
        			},
        			speed: 500,
        			on:{
    			    slideChange: function(){
    			    	var index = this.activeIndex;
    			    	//如果重新轮到虚拟机canvas显示时，重新渲染canvas，解决canvas未显示问题
    			    	if(index == 4 && !initCanvas){
    			    		var vcenter = document.getElementsByClassName('vcenterChart');
    			    		vcenterChart = echarts.init(vcenter[1]);
    			    		vcenterChart.setOption(vcenterOption);
    			    		initCanvas = true;
    			    	}
    			    	
    			    },
    			  },
        		});
            	$('.swiper-container').hover(function(){
            		//鼠标移上去停止播放
            		swiper.autoplay.stop();
            	},function(){
            		//鼠标移开开始播放
            		swiper.autoplay.start();

            	});
    		}
		},4000);
		
	}
	
	//canvas动态时钟
	var paintClock = function(){
	    var circleX = 17;
	    var circleY = 17;
	    var circleR = 15;
	    var oPI = Math.PI/180;
	    var oc = document.getElementById("myCanvas");
	    var ogc = oc.getContext("2d");
		ogc.strokeStyle="#00d2ff";
	    ogc.clearRect(0, 0, circleX, circleY);//清空画布
	    ogc.beginPath();
	    /*画分针刻度*/		
	    for(var i=0; i<60; i++){
			ogc.moveTo(circleX,circleY);
			ogc.arc(circleX,circleY,circleR,6*oPI*i,6*oPI*(i+1),false);
	    }
	    ogc.closePath();
	    ogc.stroke();
	    //画一个白色实心圆盖住其他的线，使其出来分针刻度线
	    ogc.beginPath();
	    ogc.fillStyle = "#09091d";
	    ogc.arc(circleX,circleY,circleR * 19,0,360*oPI,false);
	    ogc.closePath();
	    ogc.fill();
	    //ogc.stroke();//不画，会出现黑框
				
	    /*画时针刻度*/
	    ogc.beginPath();
	    ogc.lineWidth = 2;
	    for(var i=0; i<4; i++){
	        ogc.moveTo(circleX,circleY);
	        ogc.arc(circleX,circleY,16,90*oPI*i,90*oPI*(i+1),false);
	    }
	    ogc.closePath();
	    ogc.stroke();
	    //画一个白色实心圆盖住其他的线，使其出来时针刻度线
	    ogc.beginPath();
	    ogc.fillStyle = "#09091d";
	    ogc.arc(circleX,circleY,circleR*18/20,0,360*oPI,false);
	    ogc.closePath();
	    ogc.fill();
				
	    /*求当前时间*/
	    var oDate = new Date();
	    var oHours = oDate.getHours();
	    var oMin = oDate.getMinutes();
	    var oSec = oDate.getSeconds();

	    /*把时间转换成弧度*/
	    var oHoursValues = (-90 + oHours * 30)*oPI;
	    var oMinValues = (-90 + oMin * 6)*oPI;
	    var oSecValues = (-90 + oSec * 6)*oPI;
	    /*画时针*/
	    ogc.beginPath();
	    ogc.lineWidth=2;
	    ogc.moveTo(circleX,circleY);
	    ogc.arc(circleX,circleY,circleR*8/18,oHoursValues,oHoursValues,false);
	    ogc.closePath();
	    ogc.fill();
	    ogc.stroke();
				
	    /*画分针*/
	    ogc.beginPath();
	    ogc.lineWidth = 1;
	    ogc.moveTo(circleX,circleY);
	    ogc.arc(circleX,circleY,circleR*12/18,oMinValues,oMinValues,false);
	    ogc.closePath();
	    ogc.fill();
	    ogc.stroke();
		
		  /*画秒针*/
	    ogc.beginPath();
	    ogc.lineWidth = 8/10;
	    ogc.moveTo(circleX,circleY);
	    ogc.arc(circleX,circleY,circleR*15/18,oSecValues,oSecValues,false);
	    ogc.closePath();
	    ogc.fill();
	    ogc.stroke();

	    ogc.closePath();
	    ogc.restore();
	    timerTask.SystemTime = setTimeout(function(){paintClock();}, 1000);//毫秒
	}
	
	//运行恢复或在线迁移任务中心动画效果初始化
	var runningRecoverJob = function(){
		var info = '<div style="top: 520px; left:630px;" class="recover-ball ball5"></div><div style="top: 520px; left:630px;" class="recover-ball ball6"></div>' +
		'<div style="top: 490px; left:720px; " class="recover-ball ball7"></div><div style="top: 405px; left:865px; " class="recover-ball ball8"></div>';
		$('#recoveryDiv').html(info);
		$('.recover-ball').show();
		ball5 = anime({
			targets: '.ball5',
			keyframes: [
		     {translateX: -165, translateY: 65},
		     {translateX: -210, translateY: 45},
			 ],
			 duration: 4000,
			 easing: 'linear',
			 loop: true,
		});
		
		setTimeout(function(){
			ball6 = anime({
				targets: '.ball6',
				keyframes: [
			     {translateX: -165, translateY: 65},
			     {translateX: -210, translateY: 45},
				 ],
				 duration: 4000,
				 easing: 'linear',
				 loop: true,
			});
		},2000);
		
		ball7 = anime({
			targets: '.ball7',
			keyframes: [
		     {translateX: 112, translateY: -48},
			 ],
			 duration: 2000,
			 easing: 'linear',
			 loop: true,
		});
		
		ball8 = anime({
			targets: '.ball8',
			keyframes: [
		     {translateX: -232, translateY: -100},
		     {translateX: -350, translateY: -48},
		     {translateX: -541, translateY: -140},
		     {translateX: -839, translateY: -2},
		     {translateX: -541, translateY: 134},
			 ],
			 duration: 8000,
			 easing: 'linear',
			 loop: true,
		});
	}
	
	//备份任务运行时中心动画
	var runningBackupJob = function(){
		//添加动画信息
		var info = '<div  style="top:345px;left:150px;" class="backup-ball ball1"></div><div  style="top:200px;left:475px;" class="backup-ball ball2"></div>'+
			'<div  style="top:555px;left:350px;" class="backup-ball ball3"></div><div  style="top:555px;left:350px;" class="backup-ball ball4"></div>';
		$('#backupDiv').html(info);
		$('.backup-ball').show();
		ball1 = anime({
			targets: '.ball1',
			keyframes: [
			   {translateX: 175, translateY: -80},
			   {translateY: 125, translateX: 637},
			   {translateX: 570,translateY: 153},
		  ],
		  duration: 8000,
		  easing: 'linear',
		  loop: true,
		});
		
		ball2 = anime({
				targets: '.ball2',
				keyframes: [
			     {translateX: -150, translateY: 64},
			     {translateY: 269, translateX: 312},
				 {translateX: 380,translateY: 238},
				 ],
				 duration: 8000,
				 easing: 'linear',
				 loop: true,
			});
		
		ball3 = anime({
				targets: '.ball3',
				keyframes: [
			     {translateX: -344, translateY: -151},
			     {translateY: -291, translateX: -26},
				 {translateX: 437,translateY: -86},
				 {translateX: 370,translateY: -58},
				 ],
				 duration: 10000,
				 easing: 'linear',
				 loop: true,
			});
		ball4 = anime({
				targets: '.ball4',
				keyframes: [
			     {translateX: -344, translateY: -151},
			     {translateY: -291, translateX: -26},
				 {translateX: 437,translateY: -86},
				 {translateX: 500,translateY: -115},
				 ],
				 duration: 10000,
				 easing: 'linear',
				 loop: true,
			});
	}
	
	//初始化图标
	var initChart = function(){
		initNetworkChart();		//初始化网络流量监控
		initCpuAndMemoryChart();//初始化cpu和内存监控
	}
	
	
	//虚拟化中心数据
	var initVcenterChart = function(data){
		//虚拟机
		var des = '<p><span class="protectNum">'+data.protect_vm_num +'</span><span>'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#all_protect_num').html(des);
		//没有受保护的虚拟机
		$('.vmProtectDiv').show();
//		if(data.protect_vm_num == 0){
//			$('.vmProtectDiv').hide();
//		}else{
//			$('.vmProtectDiv').show();
//		}
		//初次刷新数据生成数字动画
		if(!initProtectNum){
			var version = $.browser.version.substring(0, 2);
			var version = parseInt(version);
			if($.browser.webkit && version <76){
				var anime1 = anime({
					targets: '#all_protect_num .protectNum',
					innerHTML: [0, data.protect_vm_num],
					easing: 'linear',
					round: 1 
				});
			}
			initProtectNum = true;
		}
//		if(data.all_vm_num != 0){
//			//如果有虚拟机
		
			$('.vcenterDiv').addClass('swiper-slide');
			$('.vcenterDiv').show();
//		}else{
//			$('.vcenterDiv').hide();
//			return;
//		}
		$('#protect-rote').html(data.percent);
		var html = '<p>'+LANG.UI_VCENTER_VM+': '+data.all_vm_num+ LANG.UI_PUBLIC_NUM +'</p><p>'+LANG.UI_VCENTER_HOST+': '+data.host_num+ LANG.UI_PUBLIC_NUMBER +'</p>';
		$('#vcenterData').html(html);
		
		var protectDes = '<p style="text-align:center;font-size:13px;color:#00ffff;margin:0;" ><i style="font-size:10px; margin-right:2px;" class="fa fa-circle"></i>'+
					LANG.UI_VISUAL_PROTECT_VM+'</p><p style="font-size:34px;text-align:center;">'+data.protect_vm_num+'<span style="font-size: 12px;">'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#protectDes').html(protectDes);
		var unprotectDes = '<p style="text-align:center;font-size:13px;color:#ff9c00;margin:0;" ><i style="font-size:10px;margin-right:2px;" class="fa fa-circle"></i>'+LANG.UI_VISUAL_UNPROTECT_VM+'</p>'+
			'<p style="font-size:34px;text-align:center;">'+data.unprotect_vm_num+'<span style="font-size: 12px;">'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#unprotectDes').html(unprotectDes);
		
		//初始化虚拟机分布饼状图dom
		if(vcenterChart === undefined){
			vcenterChart = echarts.init(document.getElementById('vcenterPieChart'));
		}
		
		var color = ['#DCB2FF','#B577FB'];
		var num = 0;
		var postionList = ['50%', '60%'];
		//所有代理都受到保护调整饼图样式
		if(data.all_vm_num == data.protect_vm_num){
			postionList = ['52%', '58%'];
		}
		var option = {
			tooltip : {
		        trigger: 'item',
		        formatter: function (params, ticket, callback) {
        			return params.data.name + "<br>" +params.data.value;
        		}
		    },
    	    series: [
					 {
					    type:'pie',
					    radius: ['68%', '62%'],
					    center: ['50%','60%'],
					    selectedOffset: 5,
					    itemStyle:{
					    	normal:{
			            		color: function(){
				            		return '#443167';
				            	},
				            	opacity:0.7
			            	}
					    },
					    silent: true,          //饼图不响应鼠标事件
					 // 设置值域的那指向线
 	                     labelLine: {
 	                        normal: {
 	                          show: false   // show设置线是否显示，默认为true，可选值：true ¦ false
 	                        }
 	                      },
					    data:["0"]
					},
	 	            {
                      name: LANG.UI_VCENTER_VCENTER,
                      type: 'pie',
                      radius: ['40%', '60%'],  // 设置环形饼状图， 第一个百分数设置内圈大小，第二个百分数设置外圈大小
                      center: postionList,  // 设置饼状图位置，第一个百分数调水平位置，第二个百分数调垂直位置
                      data: [
                          {
                        	  value: data.protect_vm_num, 
                        	  name:LANG.UI_VISUAL_PROTECT_VM,
                        	  selected: true,
                        	 itemStyle: {
                                normal: {//颜色渐变
                                	 color: {
                                        type: 'linear',
                                        x: 0,
                                        y: 0,
                                        x2: 0,
                                        y2: 1,
                                        colorStops: [{
                                            offset: 0, color: '#eedeff' // 0% 处的颜色
                                        }, {
                                            offset: 1, color: '#c996fd' // 100% 处的颜色
                                        }],
                                        global: false // 缺省为 false
                                    }
                                }
                            }
                          },
                          {
                        	  value:data.unprotect_vm_num, 
                        	  name:LANG.UI_VISUAL_UNPROTECT_VM,
                        	  itemStyle: {
                                normal: {//颜色渐变
		 	                         color: {
		 	                            type: 'linear',
		 	                            x: 0,
		 	                            y: 0,
		 	                            x2: 0,
		 	                            y2: 1,
		 	                            colorStops: [{
		 	                                offset: 0, color: '#892de9' // 0% 处的颜色
		 	                            }, {
		 	                                offset: 1, color: '#ad64f8' // 100% 处的颜色
		 	                            }],
		 	                            global: false // 缺省为 false
		 	                        }
                                }
                            }
                          },
                      ],
                    hoverAnimation: false,
                    animation: true,
                    startAngle: 30,
                    itemStyle:{
                        normal:{
		            		color: function(){
		            			if(num > color.length - 1){
			            			num = 0;
			            		}
			            		return color[num ++];
			            	}
		            	}
                      },
                      
                      labelLine:{
                    	normal:{
                    		show:false
                    	}  
                      },
                      label:{
                    	show: false,
                        normal:{
                        	formatter: '',
                            rich:{
                                a: {
                                    color: '#00ffff',
                                    fontSize: 28,
                                    align: 'center',
                                },
                            }
                        
                        }  
                      },
                    }
                  ],
		}
		
		vcenterChart.setOption(option);
		
	}
	
	//文件代理数据
	var initFsChart = function(data){
		//文件代理
		var des = '<p><span class="protectNum">'+data.protect_num +'</span><span>'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#all_protect_fs').html(des);
		//没有受保护的代理
		if(data.protect_num == 0){
			$('.fsProtectDiv').hide();
		}else{
			$('.fsProtectDiv').show();
		}
		if(data.host_num != 0){
			//如果有文件代理
			$('.fsHostDiv').addClass('swiper-slide');
			$('.fsHostDiv').show();
		}else{
			$('.fsHostDiv').hide();
			return;
		}
		$('#fs-rote').html(data.percent);
		var html = '<p>'+LANG.UI_PLATFORM_USR_TOTAL_NUM+': '+data.host_num +'</p>';
		$('#fsData').html(html);
		
		var protectDes = '<p style="text-align:center;font-size:13px;color:#00ffff;margin:0;" ><i style="font-size:10px; margin-right:2px;" class="fa fa-circle"></i>'+
					LANG.UI_VISUAL_PROTECTED_AGENT+'</p><p style="font-size:34px;text-align:center;">'+data.protect_num+'<span style="font-size: 12px;">'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#fsprotectDes').html(protectDes);
		var unprotectDes = '<p style="text-align:center;font-size:13px;color:#ff9c00;margin:0;" ><i style="font-size:10px;margin-right:2px;" class="fa fa-circle"></i>'+LANG.UI_VISUAL_NO_PROTECTED_AGENT+'</p>'+
			'<p style="font-size:34px;text-align:center;">'+data.unprotect_num+'<span style="font-size: 12px;">'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#fsunprotectDes').html(unprotectDes);
		//初始化文件代理分布饼状图dom
		if(fsChart === undefined){
			fsChart = echarts.init(document.getElementById('fsPieChart'));
		}
		
		var color = ['#DCB2FF','#B577FB'];
		var num = 0;
		var postionList = ['50%', '60%'];
		//所有代理都受到保护调整饼图样式
		if(data.host_num == data.protect_num){
			postionList = ['52%', '58%'];
		}
		var option = {
			tooltip : {
		        trigger: 'item',
		        formatter: function (params, ticket, callback) {
        			return params.data.name + "<br>" +params.data.value;
        		}
		    },
    	    series: [
					 {
					    type:'pie',
					    radius: ['68%', '62%'],
					    center: ['50%','60%'],
					    selectedOffset: 5,
					    itemStyle:{
					    	normal:{
			            		color: function(){
				            		return '#443167';
				            	},
				            	opacity:0.7
			            	}
					    },
					    silent: true,          //饼图不响应鼠标事件
					 // 设置值域的那指向线
 	                     labelLine: {
 	                        normal: {
 	                          show: false   // show设置线是否显示，默认为true，可选值：true ¦ false
 	                        }
 	                      },
					    data:["0"]
					},
	 	            {
                      name: LANG.UI_VISUAL_FILE_AGENT,
                      type: 'pie',
                      radius: ['40%', '60%'],  // 设置环形饼状图， 第一个百分数设置内圈大小，第二个百分数设置外圈大小
                      center: postionList,  // 设置饼状图位置，第一个百分数调水平位置，第二个百分数调垂直位置
                      data: [
                          {
                        	  value: data.protect_num, 
                        	  name:LANG.UI_VISUAL_PROTECTED_AGENT,
                        	  selected: true,
                        	  itemStyle: {
                                normal: {//颜色渐变
                                	 color: {
                                        type: 'linear',
                                        x: 0,
                                        y: 0,
                                        x2: 0,
                                        y2: 1,
                                        colorStops: [{
                                            offset: 0, color: '#eedeff' // 0% 处的颜色
                                        }, {
                                            offset: 1, color: '#c996fd' // 100% 处的颜色
                                        }],
                                        global: false // 缺省为 false
                                    }
                                }
                            }
                          },
                          {
                        	  value:data.unprotect_num, 
                        	  name:LANG.UI_VISUAL_NO_PROTECTED_AGENT,
                        	  itemStyle: {
                                normal: {//颜色渐变
		 	                         color: {
		 	                            type: 'linear',
		 	                            x: 0,
		 	                            y: 0,
		 	                            x2: 0,
		 	                            y2: 1,
		 	                            colorStops: [{
		 	                                offset: 0, color: '#892de9' // 0% 处的颜色
		 	                            }, {
		 	                                offset: 1, color: '#ad64f8' // 100% 处的颜色
		 	                            }],
		 	                            global: false // 缺省为 false
		 	                        }
                                }
                            }
                          },
                      ],
                    hoverAnimation: false,
                    animation: true,
                    startAngle: 30,
                    itemStyle:{
                        normal:{
		            		color: function(){
		            			if(num > color.length - 1){
			            			num = 0;
			            		}
			            		return color[num ++];
			            	}
		            	}
                      },
                      
                      labelLine:{
                    	normal:{
                    		show:false
                    	}  
                      },
                      label:{
                    	show: false,
                        normal:{
                        	formatter: '',
                            rich:{
                                a: {
                                    color: '#00ffff',
                                    fontSize: 28,
                                    align: 'center',
                                },
                            }
                        
                        }  
                      },
                    }
                  ],
		}
		fsChart.setOption(option);
		
	}
	
	//数据库代理数据
	var initDbChart = function(data){
		//数据库代理
		var des = '<p><span class="protectNum">'+data.protect_num +'</span><span>'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#all_protect_db').html(des);
		//没有受保护的代理
		if(data.protect_num == 0){
			$('.dbProtectDiv').hide();
		}else{
			$('.dbProtectDiv').show();
		}
		if(data.host_num != 0){
			//如果有数据库代理
			$('.dbHostDiv').addClass('swiper-slide');
			$('.dbHostDiv').show();
		}else{
			$('.dbHostDiv').hide();
			return;
		}
		$('#db-rote').html(data.percent);
		var html = '<p>'+LANG.UI_PLATFORM_USR_TOTAL_NUM+': '+data.host_num +'</p>';
		$('#dbData').html(html);
		
		var protectDes = '<p style="text-align:center;font-size:13px;color:#00ffff;margin:0;" ><i style="font-size:10px; margin-right:2px;" class="fa fa-circle"></i>'+
					LANG.UI_VISUAL_PROTECTED_AGENT+'</p><p style="font-size:34px;text-align:center;">'+data.protect_num+'<span style="font-size: 12px;">'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#dbprotectDes').html(protectDes);
		var unprotectDes = '<p style="text-align:center;font-size:13px;color:#ff9c00;margin:0;" ><i style="font-size:10px;margin-right:2px;" class="fa fa-circle"></i>'+LANG.UI_VISUAL_NO_PROTECTED_AGENT+'</p>'+
			'<p style="font-size:34px;text-align:center;">'+data.unprotect_num+'<span style="font-size: 12px;">'+ LANG.UI_PUBLIC_NUM +'</span></p>';
		$('#dbunprotectDes').html(unprotectDes);
		//初始化文件代理分布饼状图dom
		if(dbChart === undefined){
			dbChart = echarts.init(document.getElementById('dbPieChart'));
		}
		
		var color = ['#DCB2FF','#B577FB'];
		var num = 0;
		var postionList = ['50%', '60%'];
		//所有代理都受到保护调整饼图样式
		if(data.host_num == data.protect_num){
			postionList = ['52%', '58%'];
		}
		var option = {
			tooltip : {
		        trigger: 'item',
		        formatter: function (params, ticket, callback) {
        			return params.data.name + "<br>" +params.data.value;
        		}
		    },
    	    series: [
					 {
					    type:'pie',
					    radius: ['68%', '62%'],
					    center: ['50%','60%'],
					    selectedOffset: 5,
					    itemStyle:{
					    	normal:{
			            		color: function(){
				            		return '#443167';
				            	},
				            	opacity:0.7
			            	}
					    },
					    silent: true,          //饼图不响应鼠标事件
					 // 设置值域的那指向线
 	                     labelLine: {
 	                        normal: {
 	                          show: false   // show设置线是否显示，默认为true，可选值：true ¦ false
 	                        }
 	                      },
					    data:["0"]
					},
	 	            {
                      name: LANG.UI_DB_AGENT,
                      type: 'pie',
                      radius: ['40%', '60%'],  // 设置环形饼状图， 第一个百分数设置内圈大小，第二个百分数设置外圈大小
                      center: postionList,  // 设置饼状图位置，第一个百分数调水平位置，第二个百分数调垂直位置
                      data: [
                          {
                        	  value: data.protect_num, 
                        	  name:LANG.UI_VISUAL_PROTECTED_AGENT,
                        	  selected: true,
                        	  itemStyle: {
                                normal: {//颜色渐变
                                	 color: {
                                        type: 'linear',
                                        x: 0,
                                        y: 0,
                                        x2: 0,
                                        y2: 1,
                                        colorStops: [{
                                            offset: 0, color: '#eedeff' // 0% 处的颜色
                                        }, {
                                            offset: 1, color: '#c996fd' // 100% 处的颜色
                                        }],
                                        global: false // 缺省为 false
                                    }
                                }
                            }
                          },
                          {
                        	  value:data.unprotect_num, 
                        	  name:LANG.UI_VISUAL_NO_PROTECTED_AGENT,
                        	  itemStyle: {
                                normal: {//颜色渐变
		 	                         color: {
		 	                            type: 'linear',
		 	                            x: 0,
		 	                            y: 0,
		 	                            x2: 0,
		 	                            y2: 1,
		 	                            colorStops: [{
		 	                                offset: 0, color: '#892de9' // 0% 处的颜色
		 	                            }, {
		 	                                offset: 1, color: '#ad64f8' // 100% 处的颜色
		 	                            }],
		 	                            global: false // 缺省为 false
		 	                        }
                                }
                            }
                          },
                      ],
                    hoverAnimation: false,
                    animation: true,
                    startAngle: 30,
                    itemStyle:{
                        normal:{
		            		color: function(){
		            			if(num > color.length - 1){
			            			num = 0;
			            		}
			            		return color[num ++];
			            	}
		            	}
                      },
                      
                      labelLine:{
                    	normal:{
                    		show:false
                    	}  
                      },
                      label:{
                    	show: false,
                        normal:{
                        	formatter: '',
                            rich:{
                                a: {
                                    color: '#00ffff',
                                    fontSize: 28,
                                    align: 'center',
                                },
                            }
                        
                        }  
                      },
                    }
                  ],
		}
		dbChart.setOption(option);
		
	}
	
	//转换大小和单位
	var sizeList = function(size){
		if(size == 0){
			var info = {
				'size': 0,
	   			'des' : ""	
			};
			return info;
		}
		var type = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
		var j = 0;
		while(size >= 1024) {
    		if( j >= 11 ) return type[10];
    		size = size / 1024;
    		j++;
   		}
   		size = size.toFixed(2);
   		var info = {
   			'size': size,
   			'des' : type[j]
   		}
   		return info;
	}
	
	//初始化存储数据
	var initStorageChart = function(data){
		var useinfo = sizeList(data.use_size);
		var freeinfo = sizeList(data.free_size);
		var des = '<p><span class="protectNum">'+data.total_protect_size+'</span><span>'+data.total_protect_des+'</span></p>';
		$('#all_backup_data').html(des);
		var value = parseFloat(data.total_protect_size);
		if(!initProtectStorage){
			var version = $.browser.version.substring(0, 2);
			var version = parseInt(version);
			if($.browser.webkit && version <76){
				var anime2 = anime({
					targets: '#all_backup_data .protectNum',
					innerHTML: [0, value],
					easing: 'easeInOutExpo',
					round: 100 
				});
			}
			initProtectStorage = true;
		}
		var html = '<p>'+LANG.UI_VISUAL_STORAGE_NUM+': '+data.storage_num+LANG.UI_PUBLIC_NUM+'</p><p>'+LANG.UI_VISUAL_STORAGE_TOTAL+': '+data.total_size+'</p>';
		$('#storageData').html(html);
		
		var freeDes = '<p style="font-size:13px;color:#ff9c00;margin:0;" ><i style="font-size:10px;margin-right:2px;" class="fa fa-circle"></i>'+LANG.UI_VISUAL_STORAGE_UNUSED+'</p>'+
			'<p style="font-size:28px;">'+freeinfo.size+'</p><p style="font-size: 13px;position:absolute; top: 62px; left:10px;">'+freeinfo.des+'</p>';
		$('#freeDes').html(freeDes);
		var usedDes = '<p style="font-size:13px;color:#00ff60;margin:0;" ><i style="font-size:10px;margin-right:2px;" class="fa fa-circle"></i>'+LANG.UI_VISUAL_STORAGE_USED+'</p>'+
			'<p style="font-size:28px;">'+useinfo.size+'</p><p style="font-size: 13px;position:absolute; top: 62px; left:10px;">'+useinfo.des+'</p>';
		$('#usedDes').html(usedDes);
		
		//初始化存储统计饼状图dom
		if(storageChart === undefined){
			storageChart = echarts.init(document.getElementById('storageChart'));
		}
		
		var option = {
			tooltip : {
				trigger: 'item',
			    formatter: function (params, ticket, callback) {
			    	return params.data.name + "<br>" +params.data.des;
	        	}
			},
			color:['#06c2f0','#383a6a'],
			series : [
			    {
			    	name: LANG.UI_VISUAL_STORAGE_USED,
			        type: 'pie',
			        radius : '60%',
			        center: ['50%', '60%'],
			        selectedOffset: 5,
			        data:[
			            {value: data.use_size, name:LANG.UI_VISUAL_STORAGE_USED,des: data.use_size_des, selected: true,
			                itemStyle: {
	                            normal: {//颜色渐变
	                            	color: {
	                            		type: 'linear',
			 	                       	x: 0,
			 	                        y: 0,
			 	                        x2: 0,
			 	                        y2: 1,
			 	                        colorStops: [{
			 	                            offset: 0, color: '#00d2ff' // 0% 处的颜色
			 	                        }, {
			 	                            offset: 1, color: '#17a0d3' // 100% 处的颜色
			 	                        }],
			 	                        global: false // 缺省为 false
			 	                    }
	                            }
			                },
			            },
			            {value: data.free_size, name:LANG.UI_VISUAL_STORAGE_UNUSED, des: data.free_size_des,
			                itemStyle: {
			                	normal: {//颜色渐变
			 	                    color: {
			 	                        type: 'linear',
			 	                        x: 0,
			 	                        y: 0,
			 	                        x2: 0,
			 	                        y2: 1,
			 	                        colorStops: [{
			 	                        	offset: 0, color: '#1e1738' // 0% 处的颜色
			 	                        },{
			 	                        	offset: 1, color: '#31275d' // 100% 处的颜色
			 	                        }],
			 	                        global: false // 缺省为 false
			 	                    }
	                            }
			                }
			            }
			        ],
			        hoverAnimation: false,
			        startAngle: 30,
			        itemStyle: {
			            emphasis: {
			                shadowBlur: 10,
			                shadowOffsetX: 0,
			                shadowColor: 'rgba(0, 0, 0, 0.5)'
			            }
			        },
			        labelLine:{  
	                    normal:{  
	                        show:false
	                    },
	                },
			        label:{
			        	show: false,
			        	normal:{
			        		formatter:'',
	                        
	                    }  
	                },
			    }
			]
		}
		
		storageChart.setOption(option);
	}
	
	//初始化网络流量监控
	var initNetworkChart = function(){
		netInflowChart = echarts.init(document.getElementById('netInflowChart'));
		netOutflowChart = echarts.init(document.getElementById('netOutflowChart'));
    	// 指定图表的配置项和数据
        var netOption = {
        	tooltip:{
        		trigger: 'axis',
    	        formatter: function (params, ticket, callback) {
    	        	
    	        	return params[0].seriesName + ": " +calSize(params[0].data, 0);
    	        }
        	},
    		grid:{
    		    left:0,
    		    top:10,
    		    right:5,
    		    bottom:10
    		},
    	    xAxis : 
	        {
	            type : 'category',
	            boundaryGap : false,
	            splitLine: {
	                show: false
	            },
	            axisTick: {
	                show: false
	            },
	            axisLabel: {
	                show: false
	            },

	            axisLine: {
	                show: true,
	                lineStyle:{
	                	type: 'solid',
	                	color: '#333',
	                	width: 1
	                }
	            },
	            data:[]
	        },
    	    yAxis : 
	        {
	            type : 'value',
	            splitLine: {
	                show: true,
	                interval: 0,
	                lineStyle:{
	                   color: ['#333333'],
	                   width: 0.5
	               }
	            },
	            min: 0,
	            axisTick: {
	                show: false
	            },
	            axisLabel: {
	                show: false
	            },

	            axisLine: {
	                show: false
	            },
	        },
    	    series : [
	              {
	            	  type:'line',
	    	            showSymbol: false,
	    	            hoverAnimation: false,
	    	            smooth: true,
	    	            smoothMonotone: 'x',
	    	            animation: false,
	    	            itemStyle: {
	    	                normal: {
	    	                    lineStyle: {
	    	                      width:1
	    	                    }
	    	                }
	    	            },
	    	            data:[],
	    	            
	              }
    	    ],
    	    color: []
    	};
        
        var inflow = [], outflow = [];
        
       //初始化网络配置
        var initNetnetOption = function(data, timeInterval){
        	if(netLineInitFlag) return;
    		for (var j = 100; j > 0; j--) {
    			inflow.push(0);
    			outflow.push(0);
            }
        	var color = ['#1eff00'];
        	netOption.color = color;
        	netOption.series[0].data = inflow;
        	netOption.series[0].name = LANG.UI_VISUAL_NETWORK_INFLOW;
        	netInflowChart.setOption(netOption);
        	var color = ['#00ffff'];
        	netOption.color = color;
        	netOption.series[0].data = outflow;
        	netOption.series[0].name = LANG.UI_VISUAL_NETWORK_OUTFLOW;
        	netOutflowChart.setOption(netOption);
        	
        	netLineInitFlag = true;
        }
        
        //设置网络数据
        var setNetworkData = function(data, currentTime){
        	for(var i=0; i<data.length; i++){
        		var network = data[i].network;
        		inflow.shift();
        		inflow.push(network.receive);
        		
        		outflow.shift();
        		outflow.push(network.transmit);
        		
        	}
        	var color = ['#1eff00'];
        	netOption.color = color;
        	netOption.series[0].data = inflow;
        	netOption.series[0].name = LANG.UI_VISUAL_NETWORK_INFLOW;
        	netInflowChart.setOption(netOption);
        	var inflowSize = calSize(inflow[99], 0);
        	$('#inflow').html(inflowSize);
        	var color = ['#00ffff'];
        	netOption.color = color;
        	netOption.series[0].data = outflow;
        	netOption.series[0].name = LANG.UI_VISUAL_NETWORK_OUTFLOW;
        	netOutflowChart.setOption(netOption);
        	var outflowSize = calSize(outflow[99], 0);
        	$('#outflow').html(outflowSize);
        }
        
        var updateInterval = 2000;	//更新时间2s
        netInflowChart.setOption(netOption);
        netOutflowChart.setOption(netOption);
        function update(){
        	if(0 == $('#netInflowChart').size()){
        		clearTimeout(timerTask.DataBackupNetwork);
        		return;
        	}
        	var data = {};
        	data.flag = initNetworkFlag;
        	if(!initNetworkFlag){
        		data.count = 100;
        	}else{
        		data.count = 1;
        	}
        	var p = JSON.stringify(data); 
        	$.post(CONF.AJAXPATH, {m:CONF.M.VISUAL,f:'getNetworkFlows',p:p}, function(d){
        		initNetworkFlag = true;
            	var dataNow = JSON.parse(d);
            	var networkData = dataNow.netData;
            	if(networkData.length == 0) return;
            	initNetnetOption(networkData, dataNow.timeInterval);
                setNetworkData(networkData, dataNow.currentTime);
            })
            .complete(function() {timerTask.DataBackupNetwork = setTimeout(function(){update()}, updateInterval);});
        }
        update();
        
	}
	
	//转换速度大小描述
	var calSize = function(size, i){
		var type = ["KB/s", "MB/s", "GB/s", "TB/s"];
		var j = 0;
		while(size >= 1024) {
    		if( j >= 4 ) return size + type[j];
    		size = size / 1024;
    		j++;
   		}
		size = size.toFixed(2);
   		return size + type[j-i];
	}
	
    //每日存储使用统计
	var initDailyStorageChart = function(data){
		if(data.length == 0) {
			$('#dailyStorageDiv').hide();
			$('#no-dailyStorage').show();
			return;
		}
		$('#no-dailyStorage').hide();
		$('#dailyStorageDiv').show();
		var dateList = [], desList = [], sizeList = [];
		for(var i=0;i<data.length;i++){
			dateList.push(data[i].date);
			desList.push(data[i].storage_size_des);
			sizeList.push(data[i].storage_size);
		}
		if(dailyStorageChart === undefined){
			dailyStorageChart = echarts.init(document.getElementById('dailyStorageChart'));
		}
		var dailyOption = {
				grid:{
					top: 40,
					right:90,
					bottom:10,
					left:50
				},
			    xAxis:
					 {
			    	   type: 'value',
			    	   boundaryGap : [0, 0.01],
					   axisTick: {
				           show: false
				       },
				       axisLabel: {
				           show: false
				       },
				       splitLine: {
		                   show: false
		               },
				       axisLine: {
				           show: false
				       }
					},
					 
			    yAxis: [{
			            type: 'category',
			            data: dateList,
			            axisTick: {
			                show: false
			            },
			            axisLabel: {
			            	show: true,
			            	interval: 0,
		            		textStyle:{
		            			color: '#fff',
		            			fontSize: 14
		            		}
			            },
			            splitLine: {
	                        show: false
	                    },
			            axisLine: {
			                show: false
			            }
			        },
			        {
			        	type: 'category',
			            data: desList,
			            axisTick: {
			                show: false
			            },
			            axisLabel: {
			            	show: true,
			            	interval: 0,
		            		textStyle:{
		            			color: '#fff',
		            			fontSize: 14
		            		}
			            },
			             splitLine: {
	                        show: false
	                    },
			            axisLine: {
			                show: false
			            }
			        },
			        ],
			    series: [
			        {	
			            type: 'bar',
			            data: sizeList,
			            itemStyle:{
			            	normal:{
			            		color: '#00ffff'
			            	}
			            },
			            barWidth: 20
			            
			        },
			        {
			            type: 'bar',
			            data: [],
			            itemStyle:{
			            	normal:{
			            		color: '#00ffff'
			            	}
			            },
			            yAxisIndex:1,
			            barWidth: 15
			        },
			    ]
			};
		dailyStorageChart.setOption(dailyOption);
	}
	
	//初始化cpu和内存监控
	var initCpuAndMemoryChart = function(){
		cpuChart = echarts.init(document.getElementById('cpuChart'));
		memoryChart = echarts.init(document.getElementById('memoryChart'));
		// 指定图表的配置项和数据
        var cpuOption = {
    	    tooltip : {
    	        trigger: 'axis',
    	        formatter: function (params, ticket, callback) {
    	        	var tips = '';
    	        	for(var i=0; i<params.length; i++){
    	        		if(!params[i].data)continue;
    	        		var value = params[i].data.value;
    	        		if("undefined" == typeof(value)) {
    	        			return '';
    	        		};
        	        	tips += params[i].seriesName + ": " + value + " %";
    	        	}
    	        	return tips;
    	        }
    	    },
    	    grid:{
    		    left:0,
    		    top:10,
    		    right:10,
    		    bottom:10
    		},
    	    xAxis : 
	        {
	            type : 'category',
	            boundaryGap : false,
	            splitLine: {
	                show: false,
	            },
	            axisTick: {
	                show: false
	            },
	            axisLabel: {
	                show: false
	            },

	            axisLine: {
	                show: true,
	                lineStyle:{
	                	color: '#333333',
		                width: 0.5
	                }
	            },
	            data:[]
	        },
    	    yAxis : 
	        {
	            type : 'value',
	            splitLine: {
	                show: true,
	                lineStyle:{
	                	color: ['#333333'],
		                   width: 0.5
	               }
	            },
	            max: 100,
	            axisTick: {
	                show: false
	            },
	            axisLabel: {
	                show: false
	            },

	            axisLine: {
	                show: false
	            },
	        },
    	    series : [
    	        {
    	            name:LANG.UI_DATACENTER_CPU_USED,
    	            type:'line',
    	            showSymbol: false,
    	            hoverAnimation: false,
    	            smoothMonotone: 'x',
    	            animation: false,
    	            smooth: true,
    	            lineStyle: {
    	            	width:1
    	            },
    	            itemStyle: {
    	                normal: {
    	                    lineStyle: {
    	                      width:1
    	                    }
    	                }
    	            },
    	            data:[]
    	        }
    	    ],
    	    color: ['#ff9c00']
    	};
        
        
        //内存使用率
        var storeOption = {
        	    tooltip : {
        	        trigger: 'axis',
        	        formatter: function (params, ticket, callback) {
        	        	var tips = '';
        	        	for(var i=0; i<params.length; i++){
        	        		if(!params[i].data)continue;
        	        		var value = params[i].data.value;
        	        		if("undefined" == typeof(value)) {
        	        			return '';
        	        		};
            	        	tips += params[i].seriesName + ": " + value + " %";
            	        	tips += "<br>"+LANG.UI_PUBLIC_FREE_SPACE+": " + params[i].data.free;
            	        	tips += "<br>"+LANG.UI_PUBLIC_TOTAL_MEMORY+": " + params[i].data.total;
        	        	}
        	        	return tips;
        	        }
        	    },
        	    grid:{
        		    left:0,
        		    top:10,
        		    right:10,
        		    bottom:10
        		},
        	    xAxis : 
    	        {
    	            type : 'category',
    	            boundaryGap : false,
    	            splitLine: {
    	                show: false,
    	            },
    	            axisTick: {
    	                show: false
    	            },
    	            axisLabel: {
    	                show: false
    	            },

    	            axisLine: {
    	                show: true,
    	                lineStyle:{
    	                	color: '#333333',
    		                width: 0.5
    	                }
    	            },
    	            data:[],
    	            
    	        },
        	    yAxis : 
    	        {
    	            type : 'value',
    	            splitLine: {	
    	                show: true,
    	                lineStyle:{
    	                	color: ['#333333'],
    	                	width: 0.5
    	               }
    	            },
    	            max: 100,
    	            axisTick: {
    	                show: false
    	            },
    	            axisLabel: {
    	                show: false
    	            },

    	            axisLine: {
    	                show: false
    	            },
    	        },
        	    series : [
        	        {
        	            name:LANG.UI_PUBLIC_MEMORY_RATE,
        	            type:'line',
        	            showSymbol: false,
        	            hoverAnimation: false,
        	            smoothMonotone: 'x',
        	            animation: false,
        	            smooth: true,
        	            lineStyle: {
        	            	width:1
        	            },
        	            itemStyle: {
        	                normal: {
        	                    lineStyle: {
        	                      width:1
        	                    }
        	                }
        	            },
        	            data:[]
        	        }
        	    ],
        	    color: ['#ffeb3b']
        	};

        var updateInterval = 2000;
        var dataCpu = [], dataMem = []
        
		
        cpuChart.setOption(cpuOption);
        memoryChart.setOption(storeOption);
        
        var initCpuOption = function(timeInterval){
			if(cpuLineInitFlag) return;
			for (var i = 100; i > 0; i--) {
	        	dataCpu.push(0);
	        	dataMem.push(0);
	        }
	    	cpuOption.series[0].data = dataCpu;
	    	
	    	storeOption.series[0].data = dataMem;
	        
	    	cpuChart.setOption(cpuOption);  
	    	memoryChart.setOption(storeOption);  
	        cpuLineInitFlag = true;
		}
        
        function update(){
        	if(0 == $('#cpuChart').size()){
        		clearTimeout(timerTask.DataBackupNetflow);
        		return;
        	}
        	var data = {};
        	data.initFlag = initFlag;
        	if(!initFlag){
        		data.count = 100;
        	}else{
        		data.count = 1;
        	}
        	var p = JSON.stringify(data); 
        	$.post(CONF.AJAXPATH, {m:CONF.M.VISUAL,f:'getCpuandMemory',p:p}, function(d){
            	var dataNow = JSON.parse(d);
            	var cpuData = dataNow.cpuData;
            	var memoryData = dataNow.memData;
            	if(cpuData.length == 0) return;
            	dataCpu.shift();
            	for(var i=0; i<cpuData.length;i++){
            		var cpu = {value: cpuData[i].cpuRate, list: cpuData[i].cpuList}
            		dataCpu.push(cpu);
            	}
            	cpuOption.series[0].data = dataCpu;
            	cpuChart.setOption(cpuOption);
            	var cpuDes = '';
            	if(!initFlag){
            		cpuDes  = calCpu(cpuData[99].cpu_total, cpuData[99].cpuRate);
            	}else{
            		cpuDes = calCpu(cpuData[0].cpu_total, cpuData[0].cpuRate);
            	}
            	$('#cpu-data').html(cpuDes);
            		
            	if(memoryData.length == 0) return;
            	dataMem.shift();
            	for(var j=0; j<memoryData.length;j++){
            		var memory = {value: memoryData[j].memoryRate, total: memoryData[j].total, free: memoryData[j].free};
            		dataMem.push(memory);
            	}
            	storeOption.series[0].data = dataMem;
            	
                memoryChart.setOption(storeOption); 
                var memoryDes = '';
                if(!initFlag){
                	memoryDes  = memoryData[99].total + '/' + memoryData[99].memoryRate + "%";
            	}else{
            		memoryDes = memoryData[0].total + '/' + memoryData[0].memoryRate + "%";
            	}
                $('#memory-data').html(memoryDes);
                initFlag = true;
            })
            .complete(function() {timerTask.DataBackupNetflow = setTimeout(function(){update()}, updateInterval);});
        }
        update();
		
	}
	
	//计算cpu数值大小
	var calCpu = function(size, rate){
		var des = " MHz";
		if(Math.floor(size) > 1024){
			des = " GHz";
			size = Number(size/1024).toFixed(2);
		}
		return  size + des +"/" + rate + "%";
	}
	
	//初始化每日虚拟机统计图表
	var initDailyVmChart = function(data){
		if(data.length == 0){
			$('#dailyVmDiv').hide();
			$('#no-dailyVm').show();
			return;
		}
		$('#no-dailyVm').hide();
		$('#dailyVmDiv').show();
		var dateList = [],vmList =[], fsList =[], dbList= [], average = [];	
		for(var i=0;i<data.length;i++){
			if($.inArray(data[i].date, dateList) != -1) continue;
			dateList.push(data[i].date);
			vmList.push(data[i].vm);
			fsList.push(data[i].fs);
			dbList.push(data[i].db);
			average.push(data[i].average_num);
		}
		if(!initDailyVmFlag){
			dailyVmChart = echarts.init(document.getElementById('dailyVmChart'));
			initDailyVmFlag = true;
		}
		var dailyOption = {
			 tooltip: {
			        trigger: 'axis',
			        formatter: function(params, ticket, callback){
			        	var tips = "";
			        	tips += params[0].marker + params[0].axisValue + LANG.UI_VISUAL_BACKUP_VM_NUM +": " + params[0].data + "<br>";
			        	tips += params[1].marker + params[1].axisValue + LANG.UI_VISUAL_BAKFILE_AGENT_NUM +": " + params[1].data + "<br>";
			        	tips += params[2].marker + params[2].axisValue + LANG.UI_VISUAL_BAKDB_AGENT_NUM +": " + params[2].data + "<br>";
			        	tips += params[3].marker + LANG.UI_VISUAL_AVERAGE +": " + params[3].data;
			        	return tips;
			        },
			    },
			    legend: {
			        data: [LANG.UI_EN_VM_NUM, LANG.UI_VISUAL_FILE_AGENT, LANG.UI_DB_AGENT],
			        show: false
			    },
			    grid:{
        		    left:27,
        		    top:45,
        		    right:27,
        		    bottom:35
        		},
			    xAxis: [
			        {
			            type: 'category',
			            data: dateList,
			             axisLabel: {
			                show:true,
			                interval: 0,
			                textStyle: {
                                color: '#fff'
                            }
			            },
			            axisTick: {
			                show: false
			            },
			            splitLine: {
	    	                show: false,
	    	            },
			    
			        }
			    ],
			    yAxis: [
			        {
			            type: 'value',
			            axisLabel: {
			                show:false
			            },
			            axisTick: {
			                show: false
			            },
			            splitLine: {
	    	                show: true,
	    	                lineStyle:{
	    	                   color: ['#1eff00'],
	    	                   opacity: 0.15
	    	               }
	    	            },
	    	            axisLine: {
	    	                show: true,
	    	                lineStyle:{
	    	                   color: ['#1eff00'],
	    	                   opacity: 0.15
	    	               }
	    	            },
			        },
			        {
			            type: 'value',
			            axisLabel: {
			                show:false
			            },
			            axisTick: {
			                show: false
			            },
			            interval: 2,
			            splitLine: {
	    	                show: true,
	    	                lineStyle:{
	    	                   color: ['#1eff00'],
	    	                   opacity: 0.15
	    	               }
	    	            },
	    	            axisLine: {
	    	                show: true,
	    	                lineStyle:{
	    	                   color: ['#1eff00'],
	    	                   opacity: 0.15
	    	               }
	    	            },
			            
			        }
			    ],
			    series: [
			        {
			        	name: LANG.UI_VISUAL_VMS,
			            type:'bar',
			            stack: 'backup',
			            data: vmList,
			            dataType: 1,
			            label: {
			                normal: {
			                    show: true,
			                    position: 'top',
			                    textStyle: {
	                                color: '#fff'
	                            },
	                            formatter: function(params, ticket, callback){
//	                            	if(params.data !=0){
//	                            		return params.data + LANG.UI_PUBLIC_NUM;
//	                            	}else{
//	                            		return '';
//	                            	}
	                            	return '';
	                            }
	                            
			                }
			            },
			            itemStyle: {
			            	normal:{
			            		color: 'rgba(113,251,106,0.67)',
//			            		barBorderRadius:[10, 10, 0, 0]
			            	}
			            },
			            barWidth: 6
			        },
			        {
			        	name: LANG.UI_VISUAL_FILE_AGENT,
			            type:'bar',
			            stack: 'backup',
			            data: fsList,
			            dataType: 2,
			            label: {
			                normal: {
			                    show: true,
			                    position: 'top',
			                    textStyle: {
	                                color: '#fff'
	                            },
	                            formatter: function(params, ticket, callback){
//	                            	if(params.data !=0){
//	                            		return params.data + LANG.UI_PUBLIC_NUM;
//	                            	}else{
//	                            		return '';
//	                            	}
	                            	return '';
	                            }
	                            
			                }
			            },
			            itemStyle: {
			            	normal:{
			            		color: 'rgba(30,144,255,0.67)',
//			            		barBorderRadius:[10, 10, 0, 0]
			            	}
			            },
			            barWidth: 6
			        },
			        {
			        	name: LANG.UI_DB_AGENT,
			            type:'bar',
			            stack: 'backup',
			            data: dbList,
			            dataType: 3,
			            label: {
			                normal: {
			                    show: true,
			                    position: 'top',
			                    textStyle: {
	                                color: '#fff'
	                            },
	                            formatter: function(params, ticket, callback){
//	                            	if(params.data !=0){
//	                            		return params.data + LANG.UI_PUBLIC_NUM;
//	                            	}else{
//	                            		return '';
//	                            	}
	                            	return '';
	                            }
	                            
			                }
			            },
			            itemStyle: {
			            	normal:{
			            		color: 'rgba(210,105,30,0.67)',
//			            		barBorderRadius:[10, 10, 0, 0]
			            	}
			            },
			            barWidth: 6
			        },
			        {
			            type:'line',
			            data:average,
			            dataType: 4,
			            itemStyle: {
			            	normal:{
			            		color: '#ff9c00',
			            		borderColor:'#ff9c00',
			            	}
			            },
			            color:['#ff9c00'],
	                    symbol:'circle',
	                    symbolSize:6,
			            areaStyle: {normal: {
       	            	 color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
       	                     offset: 0,
       	                     color: 'rgba(255,156,0, 0.47)',
       	                 }, {
       	                     offset: 1,
       	                     color: 'rgba(255,156,0, 0.1)'
       	                 }])
       	            }},
			        }
			    ]
		};
		dailyVmChart.setOption(dailyOption);
	}
	
	//初始化当日任务运行情况统计
	var initJobPieChart = function(data){
		$('#job-rate').html(data.rate);
		var des = '<p style="width:6px;height:6px;border-radius: 6px; background: #3287fe;position:absolute;top:9px;left:25px;"></p><p style="text-align:center;color:#3287fe;">'+LANG.UI_VISUAL_FINISH_JOB+': '+data.finish_num+ LANG.UI_PUBLIC_NUM +'</p><p style="width:6px;height:6px;border-radius: 6px; background: #6cc7ff;position:absolute;top:39px;left:25px;"></p><p style="text-align:center;color:#6cc7ff;">'+LANG.UI_VISUAL_WAIT_JOB+': '+data.wait_num+ LANG.UI_PUBLIC_NUM +'</p>';
		$('#job-num').html(des);
		if(jobPieChart === undefined){
			jobPieChart = echarts.init(document.getElementById('jobPieChart'));
		}
		var option = {
				tooltip:{
					 show: true,
			         formatter: "{b}：{c}"+ LANG.UI_PUBLIC_NUM,
			         position:function(p){
			        	 return [p[0] - 40, p[1] - 10];
			        } 
				},
			    color: ['#3287fe','#6cc7ff'],
			    grid:{
        		    left:5,
        		    top:5,
        		    right:5,
        		    bottom:5
        		},
			    series: [
			        {
			            name:LANG.UI_VISUAL_CURRENT_JOB_DETAIL,
			            type:'pie',
			            radius: ['60%', '100%'],
			            hoverAnimation: false,
			            label: {
			                normal: {
			                    show: false,
			                    position: 'center'
			                },
			            },
			            labelLine: {
			                normal: {
			                    show: false
			                }
			            },
			            data:[
			                {value:data.finish_num, name:LANG.UI_VISUAL_FINISH_JOB},
			                {value:data.wait_num, name:LANG.UI_VISUAL_WAIT_JOB},
			            ]
			        }
			    ]	
		};
		jobPieChart.setOption(option);
	}
	
	//初始化运行任务完成虚拟机个数统计
	var initRunVmChart = function(data){
		var des = LANG.UI_VISUAL_FINISH_VM;
		if(data.module_type == 3){
			//文件
			des = LANG.UI_VISUAL_COMPLETED_FILE;
		}else if (data.module_type == 4){
			//数据库
			des = LANG.UI_VISUAL_COMPLETED_DB;
		}
		var domId = document.getElementById(data.task_uuid).getElementsByClassName('runVmChart');
		var runVmChart = echarts.init(domId[0]);
		var option = {
			
			 tooltip:{
			        show: true,
			        formatter: "{a}：{c}"+LANG.UI_PUBLIC_NUM,
			        position: [30, 40]
			    },
			    series: [
			        {
			            name: des,
			            type: 'gauge',
			            radius: '100%',
			            data: [{value: data.finish_num}],
			            max: data.vm_num,
			            startAngle: 0,  
			            endAngle: 180,
			            clockwise:false,
			            axisLabel:{
			                show: false
			            },
			            axisTick:{
			                show:false
			            },
			             splitLine: {             // 仪表盘轴线(轮廓线)相关配置。
			                show: false,             // 是否显示仪表盘轴线(轮廓线),默认 true。
			            },
			            center:['50%', '70%'],
			            markPoint:{
		                    symbol:'circle',
		                    symbolSize:6,
		                     data:[
		                         //跟你的仪表盘的中心位置对应上，颜色可以和画板底色一样
		                         {x:'center',y:'70%',itemStyle:{color:'#fff'}}
		                     ]
		                },
			            pointer:{
			                length: "45%",
			                width:5,
			                
			            },
			            itemStyle:{
			                normal:{
			                    color: ["#42D8FF"],
			                }  
			              },
			              detail:{
			                  show: true,
			                  textStyle:{
			                      fontSize: 12,
			                      color: '#fff',
			                  },
			                  formatter: function (value) {
			                         return LANG.UI_VISUAL_RUNNING;
                                },
                                
			              },
			             axisLine: {             
			                    show: true,             
			                    lineStyle: {            
			                        color: [
				                                [0.5, 
				                                 new echarts.graphic.LinearGradient(0, 0, 1, 0, [
	                                                 {
			                                             offset: 0,
			                                             color: "#ff00d2"
			                                           },
			                                           {
				                                             offset: 1,
				                                             color: "#a641ed"
				                                           },
			                                           ])
				                                ], 
				                                [0.5,new echarts.graphic.LinearGradient(0, 0, 1, 0, [
			                                         {
			                                             offset: 0,
			                                             color: "#a641ed"
			                                           },
			                                           {
				                                             offset: 1,
				                                             color: "#ff00d2"
				                                           },])
				                                ],[1, new echarts.graphic.LinearGradient(0, 0, 1, 0, [
						                                {
				                                             offset: 0,
				                                             color: "#4bd8e0"
				                                           },
				                                           {
				                                             offset: 1,
				                                             color: "#a641ed"
				                                           }])
				                                ]
			                               ],  
			                        width:15
			                    }
			                },
			            
			        }
			    ]
		};
		runVmChart.setOption(option);

	}
	
	//初始化任务运行速度统计
	var initspeedLineChart = function(data){
		$('#'+data.task_uuid+' .vm-speed').html(data.speedDes);
		var domId = document.getElementById(data.task_uuid).getElementsByClassName('speedLineChart');
		var speedLineChart = echarts.init(domId[0]);
		var option = {
			tooltip:{
        		trigger: 'axis',
    	        formatter: function (params, ticket, callback) {
    	        	
    	        	if(params[0].data != 0){
    	        		return params[0].seriesName + ": " + calSize(params[0].data, 1);
    	        	}
    	        }
        	},
			grid:{
			    show:true, 
			    x:"0%",
			    y:"0%",
			    width: "100%",
			    height: "100%",
			    backgroundColor: new echarts.graphic.LinearGradient(0, 0, 1, 0, [{
	                     offset: 0,
  	                     color: '#c997fc'
  	                 }, 
  	                 {
  	                     offset: 1,
  	                     color: '#b165ff'
  	                 }])
			},
			xAxis:{
				type: 'category',
				data: [],
				axisLine: {
					show: false
				},
				axisTick: {
					show: false
				},
				axisLabel:{
					show:false
				},
				splitLine:{
					show: false
				}
			},
			yAxis:{
				type: 'value',
				axisLine: {
					show: false
				},
				axisTick: {
					show: false
				},
				axisLabel:{
					show:false
				},
				splitLine: {
					show: false
				}
				
			},
			series:[{
				 name: LANG.UI_VISUAL_SPEED,
				 symbol: "none",
			        type: "line",
			        data: [],
			        itemStyle: {
			        	normal:{
			        		color: '#881cf8'
			        	}
			        },
			        areaStyle: {
			        	normal: {
	  	            	 color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
	  	                     offset: 0,
	  	                     color: '#881cf8'
	  	                 }, 
	  	                 {
	  	                     offset: 1,
	  	                     color: '#B770FE'
	  	                 }])
			        	}
			        }
				},
			]
		};
		function init(){
			 initTaskSpeed(data);
		}
	        
        //初始化任务进度曲线图
        var initTaskSpeed = function(d){
        	var chartdata = [];
        	if(initSpeedChartFlag) return; //初始化了就直接返回
        	
        	for (var i = 100; i > 0; i--) {
        		chartdata.push(0);
            }
        	speedList[d.task_uuid] = {
        		'chartdata' : chartdata,
        		'nowTime' : nowTime
        	};
        	initSpeedChartFlag = true;
        }
        if(!initSpeedChartFlag){
        	init();
        }
        var chartdata = speedList[data.task_uuid].chartdata;
        var nowTime = speedList[data.task_uuid].nowTime;
        chartdata.shift();
        chartdata.push(data.speed);
        option.series[0].data = chartdata;
        
        speedLineChart.setOption(option);
	}
	
	//初始化虚拟机运行进度图表
	var initProgressPieChart = function(data){
		$('#'+data.task_uuid+' .vm-progress').html(data.progress);
		var value = parseFloat(data.progress.substr(0, data.progress.length-1));
		var chartdata = [{
		    gender: 'male',
		    value: value
		}];
		if(!initProgressFlag){
			var domId = document.getElementById(data.task_uuid).getElementsByClassName('progressPieChart');
			var progressChart = new G2.Chart({
			    container: domId[0],
			    forceFit: true,
			    height: 120,
			    padding: 0
			});
			progressChart.source(chartdata, {
				value: {
					min: 0,
					max: 100
			    }
			});
			progressChart.tooltip(false);
			progressChart.legend(false);
			progressChart.axis(false);
			progressChart.interval().position('gender*value').color('#00ffff')
			    .shape('liquid-fill-gauge').style({
			      lineWidth: 4,
			      opacity: 0.5,
			    });
			progressChart.render();
			chartList[data.task_uuid] = progressChart;
			initProgressFlag = true;
		}
		chartList[data.task_uuid].source(chartdata);
		chartList[data.task_uuid].repaint();
	}
	
	//初始化存储统计饼状图
	var initStoragePieChart = function(data){
		$('#'+ data.task_uuid+' .run-capacity').html(data.realSize_des);
		var domId = document.getElementById(data.task_uuid).getElementsByClassName('storagePieChart');
		storagePieChartList[data.task_uuid] = echarts.init(domId[0]);
		var option = {
			tooltip : {
		        trigger: 'item',
		        formatter: function(params, ticket, callback){
		        	return params.data.name + ":" + params.data.des;
		        }
		    },
			color: ['#42A4DD','#215491'],
			series: [
		        {
		            name: LANG.UI_VISUAL_SIZE,
		            type:'pie',
		            radius: ['50%', '80%'],	
		            label: {
		                normal: {
		                    show: false,
		                    position: 'center'
		                },
		            },
		            hoverAnimation: false,
		            labelLine: {
		                normal: {
		                    show: false
		                }
		            },
		            data:[
		                {value:data.realSize, name:LANG.UI_VISUAL_ALREADY_FINISH,des: data.realSize_des},
		                {value:data.freeSize, name:LANG.UI_VISUAL_NOT_FINISH,des: data.freeSize_des},
		            ]
		        }
		    ]
			
		};
		storagePieChartList[data.task_uuid].setOption(option);
		storagePieChartList[data.task_uuid].resize(); //未加载出来，刷新图标
	}
	
	//重构界面,同比例缩放
	var reloadHtml = function(){
		var width = $(window).width();
		var height = $(window).height();
		var scale = height / width ;
		var csswidth = parseInt($('body').css('width'));
		if(scale == 10/16){
			$('body').height(csswidth * 10/16);
		}
		var r = width / csswidth; //计算缩放比例 
		$(document.body).css("-webkit-transform","scale(" + r + ")"); 
	}
	
	//重定向大屏界面
	var initWindowResize = function(){
		window.onresize = function(){
			reloadHtml(); //响应式重构界面
			if($.browser.msie || ($.browser.mozilla && $.browser.version == "11.0")){
				$('#runvm-circle').hide();
				$('#runvm-circle2').hide();
			}else{
				initCenterAnime(); //重定向界面，重新加载中心动画效果
			}
		};
	}
	
	//检查是否授权大屏
	var checkVisualAuth = function(){
		function init(){
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemLisenceInfo',p:{}}, function(d){
				var data = JSON.parse(d);
				//未授权大屏,执行关闭倒计时
				if(data.status != 1 || !data.extension || !data.extension.f.visualization){
					if(!logoutFlag){
						setTimeout(function(){noAuthLogout();},300000)		//初始化登出弹出框
						logoutFlag = true;
					}

				}
				
			}).complete(function() {timerTask.visualAuth = setTimeout(function(){init();},300000);});
		}
		init();
	}
	
	//未授权弹出退出大屏提示框
	var noAuthLogout = function(){
		 $('body').append('<div class="modaltimeout fade in" id="idle-timeout-dialog" style="display:block;" data-backdrop="static"><div class="modal-dialog modal-small"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">' + 
	     		LANG.UI_VISUAL_TIMEOUT_TIPS_TITLE + '</h4></div><div class="modal-body"><p><i class="fa fa-warning"></i> ' + 
	     		LANG.UI_VISUAL_TIMEOUT_TIPS1 + ': <span id="idle-timeout-counter-visual"></span> </p><p> ' + 
	     		LANG.UI_VISUAL_TIMEOUT_TIPS2 + '</p></div><div class="modal-footer"><button id="idle-timeout-dialog-logout" type="button" class="btn btn-primary">' + 
	     		LANG.UI_VISUAL_TIMEOUT_TIPS_NO + '</button></div></div></div></div>');
		 
		 $('#idle-timeout-dialog-logout').on('click', function () {
				$('#idle-timeout-dialog').modal('hide');
				window.location = "./";
			});
		initCountTime(30);
	}
	
	//加载退出倒计时时间
	var initCountTime = function(time){
		function init(t){
			if(t == 0){
				clearTimeout(timerTask.noAuthLogout);
				window.location = "./";
				return;
			}
			$('#idle-timeout-counter-visual').html(t);
			timerTask.noAuthLogout = setTimeout(function(){init(t-1)},1000);
		}
		init(time);
	}
	
	return {
		init: function(){
			reloadHtml(); //重定向窗口大小
			getSystemTime();//获取系统时间
			initWindowResize(); //初始化字体和模块弹性变化
			initListener(); //初始化监听
			initChart(); //初始化实时监控备份系统网络流量、CPU和内存使用情况
			initDataSurvery();//初始化图标数据
			initWaitJob(); //初始化等待任务
			initRealtimeData(); //初始化运行任务
			checkVisualAuth(); //检查大屏授权状态
		}
	}
}();
jQuery(document).ready(function(){
	Visualization.init();
});