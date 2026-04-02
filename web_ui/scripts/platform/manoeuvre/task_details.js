var OrchTaskDetails = function () {
	//得到虚拟机状态描述
	var getVMInstantDes = function(vmstatus){
		//1等待,2恢复中,3恢复成功,4恢复失败
		var desSpan = '<span class="label label-sm label-default">' + LANG.UI_PUBLIC_WAIT + '</span>';
		switch(parseInt(vmstatus)){
			case 1:
				break;
			case 2:
				desSpan = '<span class="label label-sm label-info">' + LANG.UI_PUBLIC_RUNNING + '</span>';
				break;
			case 3:
				desSpan = '<span class="label label-sm label-success">' + LANG.UI_DATACENTER_SUCCESS + '</span>';
				break;
			case 4:
				desSpan = '<span class="label label-sm label-danger">' + LANG.UI_DATACENTER_FAILURE + '</span>';
				break;
		}
		return desSpan;
	}
	
	//初始化预案统计
	var initPlanInfo = function(){
		var data = {};
		data.uuid = $("#task_uuid").val();
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getVMRunningJobPlanInfo',p:jsonData}, function(d){
			var data = JSON.parse(d);
			$('#taskname').html(data.taskname);
			$('#groupplan').html(data.group);
			$('#childplan').html(data.child);
    	})
	}
	
	//初始化监控
	var initMonitoring = function(){
		//初始化总进度
		var totalprogress = echarts.init(document.getElementById('totalprogress'));
		var initTotalProgress = function(){
			var option = {
				    tooltip : {
				        formatter: "{a} {b} : {c}%"
				    },
				    series: [
				        {
				            name: LANG.UI_PUBLIC_ALL_PROGRESS,
				            type: 'gauge',
				            radius : '95%',
				            center: ['50%', '60%'],
				            axisLine:{
			            		lineStyle:{
			            			color:[[0.2, '#9ba0a7'], [0.8, '#63869e'], [1, '#91c7ae']]
			            		}
			            	},
				            detail: {
				            	formatter:'{value}%',
				            	textStyle:{
				            		fontSize:14
				            	}
				            },
				            data: [{value: _INFO.totalprogress, name: ''}]
				        }
				    ]
				};
			totalprogress.setOption(option, true);
		}
		
		//初始化虚拟机完成状态
		var vmsuccess = echarts.init(document.getElementById('vmsuccess'));
		var initVMSuccess = function(){
			var option = {
				    title : {
				        text: '',
				        subtext: '',
				        x:'center'
				    },
				    tooltip : {
				        trigger: 'item',
				        formatter: "{a} <br/>{b} : {c} ({d}%)"
				    },
				    legend: {
				        x: 'center',
				        y: 'bottom',
				        data: [LANG.UI_DATACENTER_SUCCESS,LANG.UI_DATACENTER_FAILURE,LANG.UI_PUBLIC_NOT_FINISH]
				    },
				    color:['#45B6AF', '#F3565D', '#9e9e9e'],
				    series : [
				        {
				            name: '',
				            type: 'pie',
				            radius : '75%',
				            center: ['50%', '50%'],
				            data:[
				                {value:_INFO.vmsuccess.success, name:LANG.UI_DATACENTER_SUCCESS},
				                {value:_INFO.vmsuccess.failure, name:LANG.UI_DATACENTER_FAILURE},
				                {value:_INFO.vmsuccess.waiting, name:LANG.UI_PUBLIC_NOT_FINISH},
				            ],
				            label:{
	        	            	normal:{
	        	            		show:true,
	        	            		position:"inside",
	        	            		formatter: function (params, ticket, callback) {
	        	            			if(!params.percent) return '';
	        	        	        	return params.value + LANG.UI_PUBLIC_NUMBER;
	        	        	        }
	        	            	}
	        	            },
				            itemStyle: {
				                emphasis: {
				                    shadowBlur: 10,
				                    shadowOffsetX: 0,
				                    shadowColor: 'rgba(0, 0, 0, 0.5)'
				                }
				            }
				        }
				    ]
				};
			vmsuccess.setOption(option, true);
		}
		
		
		var radialObj;
		//初始化当前虚拟机信息
		var initCurrentVMInfo = function(){
			if(!_INITFLAG){
				//Intialiazation 
				radialObj = radialIndicator('#indicatorContainer', {
				    barColor : {
		                0: '#b9f6f7',
		                50: '#82ced0',
		                100: '#45B6AF'
				    },
				    percentage: true,
				    barWidth : 10,
				    displayNumber: false,
				    initValue : 0
				});
			}
			
			var vmindex = _INFO.current.vmindex;
			var vmInfo = _INFO.vms[vmindex];
			
			//虚拟机名称
			$('#cvmname').attr('title', vmInfo.dirpath).html(vmInfo.name);
			//瞬时恢复状态
			$('#cvminstant').html(getVMInstantDes(vmInfo.instant));
			//虚拟机开关机
			$('#cvmpower').html(getVMPowerDes(vmInfo.start));
			//虚拟机popovers
			var pContent = LANG.UI_DRILLS_TASK_CPU_SIZE + vmInfo.cpucore + '/' + vmInfo.cpunum + '<br>' + 
			LANG.UI_DRILLS_TASK_STORAGE + vmInfo.memory + '<br>' + 
						   LANG.UI_DRILLS_TASK_BACKUP_TIMEPOINT + vmInfo.timepoint + '<br>' + 
						   LANG.UI_EMERGENCY_RECOVERY_DESTINATION_HOST + vmInfo.host;
			$('#vmpopovers').popover({
				html:true,
				trigger:'hover',
			});
			//popover bug 无法自动更新内容,需要特殊处理.
			//http://stackoverflow.com/questions/13564782/bootstrap-popover-content-cannot-changed-dynamically
			var popover = $('#vmpopovers').attr('data-content',pContent).attr('data-original-title',vmInfo.name).data('bs.popover');
			popover.setContent();
			popover.$tip.addClass(popover.options.placement);
			//进度
			radialObj.animate(_INFO.vms[vmindex].progress);
			
			//当前虚拟机日志
			var logs = '';
			for(var i=0; i<vmInfo.logs.length; i++){
				logs += '<li><div class="col1"><div class="cont contv"><div class="cont-col2">' + 
						 '<div class="desc descv">' + vmInfo.logs[i].desc + 
						 '</div></div></div></div>' + 
					     '<div class="col2 col2v">' + 
					     getLogLevelClass(vmInfo.logs[i].level) + 
					     '</div></li>';
			}
			if(0 == vmInfo.logs.length){
				logs = '<li><div class="col textalignc">' + LANG.UI_TOOLS_NO_DATA + '</div></li>';
			}
			
			$('#cvmlogs').html(logs);
			$('#vmdetailsdiv').show();
		}
		
		//根据日志类型得到日志展示状态
		var getLogLevelClass = function(loglevel){
			var icon = "fa fa-check";
			var lableClass = "label-success";
			if(2 == loglevel){
				icon = "fa fa-exclamation";
				lableClass = "label-warning";
			}else if(3 == loglevel){
				icon = "fa fa-times";
				lableClass = "label-danger";
			}
			var div = '<div class="label label-sm col2vlabel ' + lableClass + 
	     	  '"><i class="' + icon + '"></i></div>';
			return div;
		}
		
		//得到虚拟机开关机状态描述
		var getVMPowerDes = function(start){
			//1关机,2开机,3挂起,4暂停
			var desSpan = '<span class="label label-sm label-default">'+LANG.UI_VCENTER_VM_SHUTDOWN+'</span>';
			if(1 == parseInt(start)){
				desSpan = '<span class="label label-sm label-default">'+LANG.UI_VCENTER_VM_SHUTDOWN+'</span>';
			}else if(2 == parseInt(start)){
				desSpan = '<span class="label label-sm label-success">'+LANG.UI_VCENTER_VM_START+'</span>';
			}else if(3 == parseInt(start)){
				desSpan = '<span class="label label-sm label-warning">'+LANG.UI_VCENTER_VM_SUSPEND+'</span>';
			}else if(4 == parseInt(start)){
				desSpan = '<span class="label label-sm label-warning">'+LANG.UI_JOB_PAUSE+'</span>';
			}
			return desSpan;
		}
		
		
		var hostListSwiper;
		var cpuandmemory = [];
		var network = [];
		//初始化宿主机监控
		var initHostListInfo = function(){
			if(!_INITFLAG){
				var slides = '';
				for(var i=0; i<_INFO.hosts.length; i++){
					slides += getHostSwiperSlideTpl(_INFO.hosts[i]);
				}
				$('#hostswiper').html(slides);
				var hostSwiperConfig = {
					// 如果需要分页器
					pagination: '',
					paginationClickable: true,
					simulateTouch : false,	
				};
				if(_INFO.hosts.length > 1){
					//需要分页的时候才显示分页器,有多个目的宿主机的时候
					hostSwiperConfig.pagination = '.swiper-pagination-host';
				}
				
				hostListSwiper = new Swiper ('.swiper-container-host', hostSwiperConfig);
				
				//初始化CPU/内存,流量图
				var optionCAM = {
		        	    tooltip : {
		        	        trigger: 'axis',
		        	        formatter: function (params, ticket, callback) {
		        	        	var tips = '';
		        	        	for(var i=0; i<params.length; i++){
		        	        		var value = params[i].data;
		        	        		tips += params[i].seriesName + LANG.UI_DRILLS_TASK_USE_RATE + value + "%<br>";
		        	        	}
		        	        	return tips;
		        	        }
		        	    },
		        	    legend: {
		        	        data:[LANG.UI_PUBLIC_CPU,LANG.UI_PUBLIC_MEMORY]
		        	    },
		        	    grid: {
		        	    	top: 5,
		        	        left: 18,
		        	        right: 25,
		        	        bottom: 15,
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
		    	                	return value + "%";
		    	                }
		    	            },
		    	        },
		        	    series : [
		        	        {
		        	            name:LANG.UI_PUBLIC_CPU,
		        	            type:'line',
		        	            showSymbol: false,
		        	            hoverAnimation: false,
		        	            smoothMonotone: 'x',
		        	            animation: false,
		        	            areaStyle: {normal: {
		        	            	color: '#acd3fb',
		        	            	opacity: 0.3
		        	            }},
		        	            data:[]
		        	        },
		        	        {
		        	            name:LANG.UI_PUBLIC_MEMORY,
		        	            type:'line',
		        	            showSymbol: false,
		        	            hoverAnimation: false,
		        	            smoothMonotone: 'x',
		        	            animation: false,
		        	            areaStyle: {normal: {
		        	            	color: '#CDB79E',
		        	            	opacity: 0.1
		        	            }},
		        	            data:[]
		        	        },
		        	    ],
		        	    color: ['#acd3fb', '#CDB79E']
		        	};
				
				var optionNET = {
		        	    tooltip : {
		        	        trigger: 'axis',
		        	        formatter: function (params, ticket, callback) {
		        	        	var tips = '';
		        	        	for(var i=0; i<params.length; i++){
		        	        		var value = params[i].data;
		        	        		if("undefined" == typeof(value)) {
		        	        			return '';
		        	        		};
		            	        	if(value >= 1024){
		            	        		tips += params[i].seriesName + ": " +  Math.round(value * 100 / 1024) / 100 + " MB/s";
		            	        	}else{
		            	        		tips += params[i].seriesName + ": " + value + " KB/s";
		            	        	}
		            	        	tips += "<br>";
		        	        	}
		        	        	return tips;
		        	        }
		        	    },
		        	    legend: {
		        	        data:[LANG.UI_DRILLS_TASK_NETWORK_LOAD]
		        	    },
		        	    grid: {
		        	    	top: 10,
		        	        left: 5,
		        	        right: 25,
		        	        bottom: 10,
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
		        	            name:LANG.UI_DRILLS_TASK_NETWORK_LOAD,
		        	            type:'line',
		        	            showSymbol: false,
		        	            hoverAnimation: false,
		        	            smoothMonotone: 'x',
		        	            animation: false,
		        	            areaStyle: {normal: {
		        	            	color: '#19bd9b',
		        	            	opacity: 0.3
		        	            }},
		        	            data:[]
		        	        }
		        	    ],
		        	    color: ['#19bd9b']
		        	};
				
	            var hostmemoryandcpu = $('.hostmemoryandcpu');
	        	var hostnetwork = $('.hostnetwork');
	            for(var i=0; i<hostmemoryandcpu.length; i++){
	            	//初始化cpu和内存
	            	cpuandmemory[i] = echarts.init(hostmemoryandcpu[i]);
	            	cpuandmemory[i].setOption(optionCAM);
	            	//初始化网络
	            	network[i] = echarts.init(hostnetwork[i]);
	            	network[i].setOption(optionNET);
	            }
	            
			}
			
			for(var i=0; i<_INFO.hosts.length; i++){
				//更新CPU和内存
				var option = cpuandmemory[i].getOption();
				option.series[0].data = _INFO.hosts[i].cpu;
				option.series[1].data = _INFO.hosts[i].memory;
//            	option.xAxis[0].data = _INFO.hosts[i].cpuAndMemoryX;
				cpuandmemory[i].setOption(option);
				//更新网络
				var option = network[i].getOption();
				option.series[0].data = _INFO.hosts[i].network;
//            	option.xAxis[0].data = _INFO.hosts[i].cpuAndMemoryX;
            	network[i].setOption(option);
			}
			
			hostListSwiper.slideTo(_INFO.current.hostindex, 1000, false);
		}
		
		var parseNum = function(num){
        	num = parseInt(num);
        	num = num >= 10 ? num : "0" + num;
        	return num;
        }
		
		var vmListSwiper;
		//初始化演练虚拟机列表
		var initVMListInfo = function(){
			if(!_INITFLAG){
				var slides = '';
				for(var i=0; i<_INFO.vms.length; i++){
					slides += getVMSwiperSlideTpl(_INFO.vms[i]);
				}
				$('#vmswiper').html(slides);
				
				var column = _INFO.vms.length > 3 ? 2 : 1;	//虚拟机个数大于三个就两行显示
				
				var vmSwiperConfig = {
					pagination: '',
					slidesPerView: 3,
			        slidesPerColumn: column,
			        paginationClickable: true,
			        spaceBetween: 20,	
				};
				if(_INFO.vms.length > 6){
					//需要分页的时候才显示分页器,超过6台演练虚拟机的时候
					vmSwiperConfig.pagination = '.swiper-pagination-vm';
				}
				
				vmListSwiper = new Swiper('.swiper-container-vm', vmSwiperConfig);
				
				$('.hvr-glow').unbind().on('click', function(){
					
					//取消其他所有选中效果
					
//					$(this).toggleClass('vmselect');
					//根据选中效果,锁定宿主机和虚拟机的滚动
					if(!$(this).hasClass("vmselect")){
						//选中
						$('.vmselect').removeClass('vmselect');
						$(this).toggleClass('vmselect');
						
						_VMUUID = this.dataset.vmuuid;
						_HOSTUUID = this.dataset.hostuuid;
						//初始化当前虚拟机
						_INFO.current.vmindex = this.dataset.vmindex;
						_INFO.current.hostindex = this.dataset.hostindex;
						initCurrentVMInfo();
						//宿主机移动到对应的位置
						hostListSwiper.unlockSwipes();
						hostListSwiper.slideTo(_INFO.current.hostindex, 1000, false);
						//锁定不准自动跳
						hostListSwiper.lockSwipes();
						vmListSwiper.lockSwipes();
					}else{
						//取消
						_VMUUID = null;
						_HOSTUUID = null;
						$(this).toggleClass('vmselect');
						hostListSwiper.unlockSwipes();
						vmListSwiper.unlockSwipes();
					}
					
//					console.log(vmListSwiper.activeIndex);
				});
				
			}
			
			var progressbar = $('.progress-bar');
			for(var i=0; i<progressbar.length; i++){
				$(progressbar[i]).css('width', _INFO.vms[i].progress + '%');
			}
			
		}
		
		//得到宿主机切换模块
		var getHostSwiperSlideTpl = function(host){
			var tpl = '<div class="swiper-slide min-height370"><div class="row "><div class="col-md-6">' + 
						'<div class="col-md-4"><i class="iconfont icon-xinicon02 hosticon"></i></div>' + 
						'<div class="col-md-8"><div class="row static-info"><div>' + 
						LANG.UI_DRILLS_TASK_NAME + host.name + '</div><div>' + 
						'IP: ' + host.ip + '</div><div>' + 
						LANG.UI_DRILLS_TASK_AGENT_GATEWAY + host.verifyvm + '</div></div></div></div>';
			tpl += '<div class="col-md-6"><div class="row static-info"><div>' + 
				   LANG.UI_DRILLS_TASK_ISOLATED_NETWORK + '</div><div>' + 
				   host.networkmap[0].verify_segment + '/' + host.networkmap[0].verify_netmask + 
				   '</div></div><div class="row static-info"><div>' + 
				   LANG.UI_DRILLS_TASK_PRODUCT_NETWORK_SEGMENT + '</div><div>' + 
				   host.networkmap[0].old_segment + '/' + host.networkmap[0].old_netmask + 
				   '</div></div></div></div>';
			tpl += '<div class="hostmemoryandcpu height120"></div>';
			tpl += '<div class="hostnetwork height120"></div></div>';
			return tpl;
		}
		
		//得到虚拟机切换模块
		var getVMSwiperSlideTpl = function(vm){
			var facolour = "";
			if(4 == vm.instant){
				facolour = "font-danger";	//错误的虚拟机换个颜色
			}
			var tpl = '<div class="swiper-slide "><a class="hvr-glow" data-vmuuid="' + vm.vmuuid +
					  '" data-hostuuid="' + vm.hostuuid + '" data-vmindex="' + vm.vmindex + 
					  '" data-hostindex="' + vm.hostindex + '"><div class="col-md-12 column bgwitesmoke">' + 
				      '<div class="lh22"><div class="textellipsis width11em" title="' + 
				      vm.dirpath + '">' + vm.name + '</div></div><div class="margintb10">' + 
				      '<div class="floatl vmlists"><i class="fa fa-desktop ' + facolour + '"></i></div><div>' + 
				      '<div>CPU: ' + vm.cpucore + '/' + vm.cpunum + '</div>' + 
				      '<div>' + LANG.UI_PUBLIC_MEMORY + '：' + vm.memory + '</div></div></div>' + 
				      '<div class=""><div class="progress vmprogress" >' + 
		              '<div class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" ' + 
		              'style="width: ' + vm.progress + '%"></div></div></div></div></a></div>';
			return tpl;
		}
		
		//初始化所有
		var CallInitAll = function(){
			initTotalProgress();
			initVMSuccess();
			initCurrentVMInfo();
			initHostListInfo();
			initVMListInfo();	
			_INITFLAG = true;
		}
		
		var _INFO;
		var _INITFLAG = false;
		var _VMUUID, _HOSTUUID;	//当前虚拟机uuid和宿主机uuid,主要用于用户手动控制
		
		var updateAll = function(){
			if(0 == $('#totalprogress').size()){
	    		clearTimeout(timerTask.OrchTaskDetails);
	    		return;
	    	}
			var p = {}
			p.taskuuid = $('#task_uuid').val();
			p.vmuuid = _VMUUID;
			p.hostuuid = _HOSTUUID;
			p = JSON.stringify(p);
			$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getTaskMonitor',p:p}, function(data){
				_INFO = JSON.parse(data);
	    		CallInitAll();
            })
            .complete(function() {timerTask.OrchTaskDetails = setTimeout(updateAll, 5000);});
		}
		
		updateAll();
		
		//窗口调整后界面跟着调整
		var initResizeListener = function(){
			window.onresize = function(){
	        	totalprogress.resize();	//总进度
	        	vmsuccess.resize();		//总状态
	        	hostListSwiper.onResize()	//host
	    		for(var i=0; i<network.length; i++){
	    			cpuandmemory[i].resize();		
	    			network[i].resize();
	    		}
	        }
		}
		
		initResizeListener();
	}
	
	//初始化下面各种列表
	var initGridList = function(){
		//初始化任务日志
		var initTaskLog = function(){
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
				$.post(CONF.AJAXPATH, {m:CONF.M.LOG,f:'getVMRunningJobLog',p:jsonData}, function(d){
					if(0 == $('#runninglog').size()){
	            		clearTimeout(timerTask.TaskDetials_logGrid);
	            		return;
	            	}
	        		getLiInfo(d);
		    	})
		    	.complete(function() {timerTask.TaskDetials_logGrid = setTimeout(getlog, updateInterval);});
			}
			getlog();
		}
		//初始化虚拟机列表
		var initVMList = function(){
			var vmListGrid, gridInitFlag = false;
			var updateInterval = 10000;
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': true,
//		                'targets': [0]
	    			}],
	    			"order": [
	                    [0, "asc"]
	                ],
	    	};
			var addShowType = function(){
				var data = vmListGrid.getDataTable().data();
				if(0 == data.length) return;
				var statusDiv = $('#vms').find('tbody > tr').find('td:eq(6)');
				for(var i=0; i<statusDiv.length; i++){
					$(statusDiv[i]).html(getVMInstantDes(data[i][6]));
				}
			}
			var initGrid = function(){
	    		if(0 == $('#vmstable').size()){
	        		clearTimeout(timerTask.TaskDetials_vmList);
	        		return;
	        	}
	    		var data = {};
				data.uuid = $("#task_uuid").val();
	    		if(!gridInitFlag){
	    			vmListGrid = new Datatable();
	        		var data = {m:CONF.M.MANOEUVRE,f:'getOrchInstanJobVMList',p:data};
	        		vmListGrid.setAjaxParam(data);
	        		vmListGrid.init({src: $("#vmstable"), showDetail:false, dataTable:dataTableOpt, onDataLoad:addShowType});
	            	gridInitFlag = true;
	    		}else{
	    			vmListGrid.getRefresh(data);
	    		}
	    		timerTask.TaskDetials_vmList = setTimeout(initGrid, updateInterval);
	    	}
	    	initGrid();
		}
		//初始化历史任务
		var initHistoryList = function(){
			var HistoryGrid, gridInitFlag = false;
			var updateInterval = 10000;
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0]
	    			}],
	    			"order": [
	                    [6, "desc"]
	                ],
	    	};
			var addShowTypeStatus = function(){
				var data = HistoryGrid.getDataTable().data();
				if(0 == data.length) return;
				var statusDiv = $('#history').find('tbody > tr').find('td:eq(2)');
				for(var i=0; i<statusDiv.length; i++){
					setLevel(statusDiv[i], data[i]);
				}
			}
			var setLevel = function(div, data){
				var labelClass = getLevelClass(data[7].level);
				var content = '<span class="label label-sm ' + labelClass + '">' + data[2] + '</span>';
				$(div).html(content);
			}
			//得到状态的显示类型
			var getLevelClass = function(level){
				var levelClass = '';
				switch(level){
					case 1:
					case 2:
					case 3:
						levelClass = "label-success";
						break;
					case 4:
						levelClass = "label-info";
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
			var initGrid = function(){
	    		if(0 == $('#historytable').size()){
	        		clearTimeout(timerTask.TaskDetials_vmList);
	        		return;
	        	}
	    		var data = {};
				data.uuid = $("#task_uuid").val();
	    		if(!gridInitFlag){
	    			HistoryGrid = new Datatable();
	        		var data = {m:CONF.M.JOB,f:'getOrchInstanJobHistoryList',p:data};
	        		HistoryGrid.setAjaxParam(data);
	        		HistoryGrid.init({src: $("#historytable"), showDetail:false, dataTable:dataTableOpt, onDataLoad:addShowTypeStatus});
	            	gridInitFlag = true;
	    		}else{
	    			HistoryGrid.getRefresh(data);
	    		}
	    		timerTask.TaskDetials_vmList = setTimeout(initGrid, updateInterval);
	    	}
	    	initGrid();
		}
		
		//初始化验证div
		var initVerifyDiv = function(){
			//初始化BS select控件
			$('#verifytype').selectpicker({
	            iconBase: 'fa',
	            tickIcon: 'fa-check'
	        });
			
			
			//验证用户ip地址
			var tryInputIp = function(ip){
				//var ipv4 = /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/;
				//return ipv4.test(ip)
				return ipV4V6(ip);
			}

			
			//初始化
			var initVerifyDiv = function(){
				var ip = $('#verifyip').val();
				if(!tryInputIp(ip)){
					UIToastr.showInfo(LANG.UI_DRILLS_TASK_CHECK_IP, LANG.UI_DRILLS_TASK_CHECK_IP_TIPS);
					return;
				}
				var type = parseInt($('#verifytype').val());
				
				
				var timestamp = new Date().getTime();
				var verifyDes = "";
				switch(type){
					case 1:
						verifyDes = '<div class="col-md-2"><span class="font-green-seagreen"><i class="fa fa-random"></i> '+ LANG.UI_DRILLS_TASK_PING_VALIDATE +'</span></div>';
						verifyDes += '<div class="col-md-3"> ping ' + ip + ' </div>';
						verifyDes += '<div class="col-md-2" id="resDes_' + timestamp + '"> <i class="fa fa-spinner fa-spin"></i></div>';
						verifyDes += '<div class="col-md-4"> ' + new Date().Format("yyyy-MM-dd hh:mm:ss") + ' </div>';
						break;
					case 2:
						verifyDes = '<div class="col-md-2"><span class="font-green-seagreen"><i class="fa fa-globe"></i> '+ LANG.UI_DRILLS_TASK_WEB_VALIDATE +'</span></div>';
						verifyDes += '<div class="col-md-3"> http(s)://' + ip + ' </div>';
						verifyDes += '<div class="col-md-2" id="resDes_' + timestamp + '"> <i class="fa fa-spinner fa-spin"></i></div>';
						verifyDes += '<div class="col-md-4"> ' + new Date().Format("yyyy-MM-dd hh:mm:ss") + ' </div>';
						break;
				}
				$('.verifydiv').append(verifyDes);
				
				var data = {};
				data.type = type;
				data.value = ip;
				data = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.MANOEUVRE,f:'getVerifydiyResult',p:data}, function(d){
					var d = JSON.parse(d);
					var resDes = '';
					if(d.result){
						resDes = '<span class="label label-sm label-success">'+ LANG.UI_DATACENTER_SUCCESS +'</span>';
					}else{
						resDes = '<span class="label label-sm label-danger">'+ LANG.UI_DATACENTER_FAILURE +'</span>';
					}
					$("#resDes_" + timestamp).html(resDes);
				});
				
			}
			//初始化事件
			$('#verifysubmit').on('click', function(){
				initVerifyDiv(); 
				return;
			});
			
			//回车事件
			$('#verifyip').keypress(function (e) {
	            if (e.which == 13) {
	            	initVerifyDiv(); 
	                return false;
	            }
	        });
			
			// 对Date的扩展，将 Date 转化为指定格式的String
			// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符， 
			// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字) 
			// 例子： 
			// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423 
			// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18 
			Date.prototype.Format = function (fmt) { 
			    var o = {
			        "M+": this.getMonth() + 1, //月份 
			        "d+": this.getDate(), //日 
			        "h+": this.getHours(), //小时 
			        "m+": this.getMinutes(), //分 
			        "s+": this.getSeconds(), //秒 
			        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
			        "S": this.getMilliseconds() //毫秒 
			    };
			    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
			    for (var k in o)
			    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
			    return fmt;
			}
		}
		
		var updateAll = function(){
			initTaskLog();
			initVMList();
			initHistoryList();
		}
		initVerifyDiv();
		updateAll();
	}
	
    return {
        //main function to initiate the module
        init: function () {
        	initPlanInfo();
        	initMonitoring();
        	initGridList();
        }

    };

}();

jQuery(document).ready(function() {    
	OrchTaskDetails.init();
});