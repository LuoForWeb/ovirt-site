var Visualscreen = (function(){
	let timers = {};
	const publicCloud_type = {
		100:'aws',
		101:'huawei'
	};

	const vm_type = {
		1:'vmware',
		2:'hyperv',
		3:'citrix',
		4:'KVM',
		6:'oracle',
		11:'hbc',
		12:'sangfor',
		15:'openstack',
		16:'huawei',
		18:'winhong',
		19:'redhat',
		26: 'zstack',
		28:'xcpng',
		30:'xsky',
		31:'openstack',
		32:'winhong',
		33:'smartx',
		35:'inspur',
		42:'proxmox',
		44:'xhere',
		47:'scp',
		51:'h3c',
		53:'aio',
		100:'aws',
		101:'huawei'
	}

	const obsVendorType = {
		0:'aws',
		1:'oss',
		1.1:'oss_en',
		2:'cos',
		2.1:'cos_en',
		3:'obs',
		4:'ceph',
		5:'wasabi',
		6:'minio',
		7:'other',
	}

	const database_type = {
		4:'DM' ,
		8:'HIGHGO',
		6:'KINGBASE',
		9:'MARIA',
		3:'MYSQL',
		2:'ORACLE',
		5:'POSTGRE',
		1:'SQLSERVER',
		7:'UXDB',
		10:'OPENGAUSS',
		11:'VASTBASE',
		12:'ANTDB',
	}
	const privateCloud_type = {
		26:'zstack',
		30:'xsky',
	}


	let currentTaskConf = {
		vm:{
			text_color:"#18FEF0",
		},
		publicCloud:{
			text_color:"#2EAD2B",
		},
		privateCloud:{
			text_color:"#18FEF0",
		},
		completeMachine:{
			text_color:"#FFFFFF",
		},
		os:{
			text_color:"#FFFFFF",
		},
		file:{
			text_color:"#06F7A1",
		},
		nas:{
			text_color:"#B3F9FE",
		},
		obs:{
			text_color:"#FFFFFF",
		},
		hadoop:{
			text_color:"#B3F9FE",
		},
		office365:{
			text_color:"#58AAFF",
		},
		k8s:{
			text_color:"#18FEF0",
		},
		db:{
			text_color:"#4BD7FF",
		},
	}
	let showMoudle = {};
	// 定义全局变量来控制滚动逻辑
	let scrollEnabled_task = true;
	let scrollEnabled_storage = true;
	let initTaskFlag = false; //初始化任务列表滚动条标志
	let initStoreDetailFlag = false;	//初始化存储详情列表滚动条标志
	let dataStatisticBarInstance = echarts.init(document.getElementById('dataStatisticBar'));
	let dataTrendsLineInstance = echarts.init(document.getElementById('backBarTrendsLine'));
	let currentTaskTotalPieInstance = echarts.init(document.getElementById('currentTaskTotalPie'));
	let storageStaticPieInstance =  echarts.init(document.getElementById('storageStaticPie'));
	let timenum = 0; //定义最新从后台取的时间
	let taskTime = 0; //定义查询任务列表的时间间隔
	let taskMinTime = 300000;//任务查询最小时间间隔
	let taskWarnShow =  false;
	let systemWarnShow = false;
	//统一管理全部gsap动画
	// 动画实例存储对象
    let animations = {
		//整机复制
        reMachinosDot1: null,
		reMachinosDot2: null,
		reMachinosDot3: null,
		//卷复制
		reOsDot1: null,
		reOsDot2: null,
		//文件复制
		reFsImg1: null,
		//数据库复制
		reDbDot1: null,
		reDbDot2: null,
		reDbDot3: null,
		//连续数据保护整机
		cdpMachineDot1: null,
		cdpMachineDot2: null,
		//连续数据保护卷
		cdpOsDot1: null,
		cdpOsDot2: null
    };
    let netOutData = [];
    let netInData = [];
	//按下右上角全屏按钮
	const allScreen = () =>{
		$('#f11').click(function(ev){
			var el = document.documentElement;
			var isFullscreen = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen;
			if (!isFullscreen) { //进入全屏,多重短路表达式
				(el.requestFullscreen && el.requestFullscreen()) ||
				(el.mozRequestFullScreen && el.mozRequestFullScreen()) ||
				(el.webkitRequestFullscreen && el.webkitRequestFullscreen()) || (el.msRequestFullscreen && el.msRequestFullscreen());

			} else { //退出全屏,三目运算符
				document.exitFullscreen ? document.exitFullscreen() :
					document.mozCancelFullScreen ? document.mozCancelFullScreen() :
						document.webkitExitFullscreen ? document.webkitExitFullscreen() : '';
			}
		})

	}
	//页面缩放
	jQuery(document).ready(function() {
		//但是这里需要动态获取当前页面大小来计算比例
		var currentWidth =  $(window).width();
		var applyScale = currentWidth/2560; //按照2560来计算
		// 初始化页面缩放为75%
		document.body.style.transform = 'scale('+applyScale+')';
		// 阻止鼠标滚轮+Ctrl缩放
		document.addEventListener('wheel', function(event) {
			if (event.ctrlKey) {
				event.preventDefault();
			}
		}, { passive: false });
	
	});
	//重定向大屏界面
	const initWindowResize = ()=>{
		window.onresize = function(){
			reloadHtml(); //响应式重构界面
		};
	}
	//同比例缩放
	const reloadHtml = ()=>{
		var width = $(window).width();
		var height = $(window).height();
		var scale = height / width ;
		var csswidth = parseInt($('body').css('width'));
		if(scale == 9/16){
			$('body').height(csswidth * 9/16);
		}
		var r = width / csswidth; //计算缩放比例
		$(document.body).css("-webkit-transform","scale(" + r + ")");
	}


	//-------------------获取所有数据的方法start-------------
	//初始化配置信息 初始化一次
	const initconfig = ()=>{
        pAjaxRequest({},'/api/v1/visual/system/config','GET',initConfig_func,true, {},true)
    }
	//获取任务和警告信息
	const initTaskAndWarning_timer = ()=>{
	    pAjaxRequest({},'/api/v1/visual/current/task/warn','GET',initTaskAndWarning_func,true, {},true)
	}
	//获取授权配置 初始化一次
	const initAuthConfig = ()=>{
		pAjaxRequest({},'/api/v1/visual/system/lisence/info','GET',initInfo_func,true, {},true)
	}
	//获取系统时间
	const initTime_timer = ()=>{
		pAjaxRequest({},'/api/v1/system/times/info','GET',getTime_func,true, {},true)
	}
	//获取任务列表
	const initTaskList_timer = ()=>{
		pAjaxRequest({},'/api/v1/visual/current/task/list','GET',initTaskList_func,true, {},true)
	}
	//获取备份数据统计信息
	const initStatisticData_timer = ()=>{
		pAjaxRequest({},'/api/v1/visual/statistic/data','GET',initStatisticData_func,true, {},true)
	}
	//获取中间部分各模块数据
	const initEveryModuleView_timer = () =>{
		pAjaxRequest({},'/api/v1/visual/other/view','GET',initEveryModuleView_func,false, {},true)
	}
	//获取节点监控数据
	const updateMonitorData_timer = ()=>{
		var p = {
			count: 100,
		}
        pAjaxRequest(p,'/api/v1/visual/node/monitor','GET',initMonitorData_func,true, {},true)
	}
	// 滚动任务定时器
	const scrollContent_task_timer = () => {
		if(!scrollEnabled_task){
			return;
		}
		const $self = $('#current-task ul').stop(true, true); // 清除动画队列并立即完成当前动画
		const lineHeight = $self.find("li:first").height();

		$self.animate(
			{ "marginTop": -lineHeight + "px" },
			1500,
			() => {
				$self.css({ marginTop: 0 })
					.find("li:first").appendTo($self);
			}
		);
	}
	//滚动存储定时器
	const scrollContent_storage_timer = () => {
		if(!scrollEnabled_storage){
			return;
		}
		const $self = $('#storage-detail ul').stop(true, true); // 清除动画队列并立即完成当前动画
		const lineHeight = $self.find("li:first").height();
		$self.animate(
			{ "marginTop": -lineHeight + "px" },
			1500,
			() => {
				$self.css({ marginTop: 0 })
					.find("li:first").appendTo($self);
			}
		);
	}
	//初始化一次
	const initNodeMonitor = function(){
		var p = {
			count:100,  //本次取的条数
		}
        pAjaxRequest(p,'/api/v1/visual/node/monitor','GET',initNodeMonitor_func,true, {},true)
    }
	//--------------------获取所有数据的方法end-------------
	//--------------------各模块数据处理start---------------
	// 处理配置信息
	const initConfig_func = (d)=>{
	    let data = d.data;
		// 缓存 DOM 元素
		let $systemName = $('#system-name');
		// const $offsiteName = $('.offsite-name');
		// const $cloudName = $('.cloud-name');
		// const $localName = $('.local-name');
		// let $taskWarning = $('#task-warning');
		// let $systemWarning = $('#system-warning');
		// 更新系统名称
		$systemName.html(data.system_name);
		// 更新告警逻辑
		// if(data.warning.task_show){
		// 	$taskWarning.show();
		// }
		// if(data.warning.system_show){
		// 	$systemWarning.show();
		// }
		taskWarnShow =  data.warning.task_show;
		systemWarnShow =  data.warning.system_show;

	}
	// 处理告警和任务信息
	const initTaskAndWarning_func = (d)=>{
	    let data = d.data;
		// 缓存 DOM 元素
		let $taskWarning = $('#taskWarning');
		let $systemWarning = $('#systemWarning');
		//先暂时处理告警
		let taskNum = data.warning.task_num;
		let systemNum = data.warning.system_num;
		// 更新任务警告
		$taskWarning.html(taskNum);
		if(taskWarnShow){
			$('#task-warning').show();
			if(taskNum === 0 ){
				$taskWarning.css('color', 'white');
				$('#task-warning .warning-dot').hide();
			}else{
				$taskWarning.css('color', '#FF483D');
				$('#task-warning .warning-dot').show();
			}
		}else{
			$('#task-warning').hide();
		}

		// 更新系统警告
		$systemWarning.html(systemNum);
		if(systemWarnShow){
			$('#system-warning').show();
			if(systemNum === 0){
				$systemWarning.css('color', 'white');
				$('#system-warning .warning-dot').hide();
			}else{
				$systemWarning.css('color', '#F7B406');
				$('#system-warning .warning-dot').show();
			}
			if(!taskWarnShow){
				$('#system-warning').css('border-left','none')
			}else{
				$('#system-warning').css('border-left','0.0625rem dashed rgba(255, 255, 255, 1);')
			}
		}else{
			$('#system-warning').hide();
		}

		//处理任务显示
		inittotalGauge(data.current_task);
		initcurrentTaskTotalPie(data.current_task);
	}
	// 处理各模块任务显示
	const inittotalGauge = (data)=>{
		$('#current-num').html(data.total_task_num);  // 当前任务总数
		$('#running-num').html(data.running_num); //运行任务数
		$('#wait-num').html(data.wait_num); // 等待任务
		$('#stop-num').html(data.stop_num); // 停止任务数
		$('#today-complete-num').html(data.complete_num); // 今日完成任务
		$('#vm-task-num').html(data.vm.num); //虚拟机数
		$("#publicCloud-task-num").html(data.publicCloud.num);
		$("#privateCloud-task-num").html(data.privateCloud.num);
		$("#completeMachine-task-num").html(data.completeMachine.num);
		$('#os-task-num').html(data.os.num);
		$('#file-task-num').html(data.file.num);
		$('#nas-task-num').html(data.nas.num);
		$("#obs-task-num").html(data.obs.num);
		$("#hadoop-task-num").html(data.hadoop.num);
		
		$('#office365-task-num').html(data.office365.num);
		$('#k8s-task-num').html(data.k8s.num);
		$('#database-task-num').html(data.db.num);
		//找到最大值的任务显示
		$("#current-most-num").html(data.max_num.num);
		$("#current-most-num").css('color',currentTaskConf[data.max_num.module].text_color);
		$("#current-most-num-des").html(data.max_num.des);
		if ($("#current-most-num-des").text().length >= 4) {
			$("#current-most-num-des").css('font-size', '0.92rem');
		}
	}
	// 处理当前任务echart 
	const initcurrentTaskTotalPie = (data) => {
		// 配置项模板
		const option = {
			series: [
				{
					name: LANG.UI_VISUAL_CURRENT_TASK,
					type: 'pie',
					hoverAnimation: false,
					center: ['50%', '50%'],
					radius: ['72%', '92%'],
					avoidLabelOverlap: false,
					color: ["rgba(24,254,240,1)", "rgba(46, 173, 43, 1)", "rgba(24, 254, 240, 1)", "rgba(255, 255, 255, 1)", "rgba(255, 255, 255, 1))", "rgba(6, 247, 161, 1)", "rgba(179, 249, 254, 1)","rgba(255, 255, 255, 1)","rgba(179, 249, 254, 1)","rgba(88, 170, 255, 1)","rgba(24, 254, 240, 1)","rgba(75, 215, 255, 1)"],
					itemStyle: {
						borderRadius: 10,
						borderColor: 'rgba(0, 0, 0, 1)',
						borderWidth: 0,
						borderCap: 'round',
					},
					label: {
						normal: {
							show: false,
						},
						emphasis: {
							show: false,
						},
					},
					labelLine: {
						normal: {
							show: false,
						},
					},
					data: [
						{ value: data.vm.num, name: '' },
						{ value: data.publicCloud.num, name: '' },
						{ value: data.privateCloud.num, name: '' },
						{ value: data.completeMachine.num, name:''},
						{ value: data.os.num, name: '' },
						{ value: data.file.num, name: '' },
						{ value: data.nas.num, name: '' },
						{ value: data.obs.num, name: '' },
						{ value: data.hadoop.num, name: '' },
						{ value: data.office365.num, name: '' },
						{ value: data.k8s.num, name: '' },
						{ value: data.db.num, name: '' }
					],
				},
				{
					name: 'decorationTwo',
					type: 'pie',
					center: ['50%', '50%'],
					radius: ['99%', '100%'],
					hoverAnimation: false,
					label: {
						normal: {
							show: false,
						},
						emphasis: {
							show: false,
						},
					},
					labelLine: {
						normal: {
							show: false,
						},
					},
					data: [{ value: 335, name: '', itemStyle: { color: "#FFFFFF" } }]
				},
			]
		};
		//获取echarts实例
		currentTaskTotalPieInstance.setOption(option);
	}
	// 处理系统信息
	const initInfo_func = (d)=>{
		var data = d.data;
		//设置showMoudle
		showMoudle =  data;
		//动态设置左上角任务tab
		show_tasktab(data);
		show_swipertab();

	}
	//动态显示 隐藏当前任务item  初始化一次
	const show_tasktab = (data) => {
		let showindex = 0;
		Object.keys(data).forEach((key,index)=>{
			if(currentTaskConf.hasOwnProperty(key)){
				var id  =  `current_${key}_tab`;
				if(data[key] && showindex < 6){ //最多显示6个 并且要隐藏数据最多的模块 因为左边显示了数量最多的模块
					$("#"+id).css("display","flex");
					showindex ++;
				}else{
					$("#"+id).css("display","none");
				}
			}
		});
	}
	//动态显示隐藏中间模块swiper数据  初始化一次
	const show_swipertab = () => {
		// let cdp_replicate_arr = ['cdp_machine_os','cdp_os','replicate_machine_os','replicate_os','replicate_file','replicate_db']
		//需要根据showMoudle removeswiper
		Object.keys(showMoudle).forEach((key)=>{
			if(!showMoudle[key]){
				$("#swiper_"+key).remove();
			}
		});
		initTabSwiper(); //初始化swiper
	}

	//获取当前任务列表
	const initTaskList_func = (d)=>{
		let data = d.data.list;
		$('#current-task-list').empty().off();
		if(data.length == 0) {
			var info = '<li class="nodatali">'+LANG.UI_VIRTUAL_NO_MORE_TASKS+'</li>';
			$('#current-task-list').html(info);
			scrollEnabled_task = false;
			return;
		}
		var info = "";
		for(var i=0;i<data.length;i++){
			info +='<li> <div class="current-task-list-box">';

			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				info += '<p style="width:45%;">'+  //任务名
					'<img src="../../img/visualscreen/icon/'+data[i].task_module+'-white.svg" style="width: 1.1rem;height: 1.5rem;fill:currentColor; color:rgba(223, 223, 223, 1);margin-right: 10px;margin-top: -0.3rem">'+
					data[i].task_name+'</p>';

				// 区别接管和验证
				if(data[i].task_type == CONF.TASK_TYPE.VOL_CDP_TAKEOVER && data[i].takeover_agent_role == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){
					// 验证  验证的cdp_vol_task_takeover_info的takeover_agent_role == 99
					data[i].task_type = 99;
				}

				info+=  '<p style="width:15%;">'+data[i].task_type_des+'</p>'  //类型
				if(data[i].task_status == 13 || data[i].task_status == 17){ //成功状态
					info+= '<p style="width:14%;color:rgba(24, 254, 240, 1)">'+data[i].task_status_des+'</p>';
				}else if(data[i].task_status == 6 || data[i].task_status == 8){  //错误状态
					info+= '<p style="width:14%;color:rgba(226, 46, 0, 1)">'+data[i].task_status_des+'</p>';
				}else{
					// 正常状态
					info+= '<p style="width:14%;color:#18FEF0">'+data[i].task_status_des+'</p>';
				}
				info +='<div  style="width:15%;display:flex">'+
					'<div class="progress task-progress  progress-bar-60-back">'+
					'<div class="progress-bar progress-bar-60 " role="progressbar"'+
					'aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"'+
					'style="width: '+ data[i].task_progress+';">'+
					'</div>'+
					'</div>'+
					'</div>'+
					'<p style="width:11%;">'+ data[i].task_speed+'</p>';   //速度
				info += '</div></li>';
			}
			else{
				info += '<p style="width:45%;">'+  //任务名
					'<img src="../../img/visualscreen/icon/'+data[i].task_module+'-white.svg" style="width: 1.1rem;height: 1.5rem;fill:currentColor; color:rgba(223, 223, 223, 1);margin-right: 10px;margin-top: -0.3rem">'+
					data[i].task_name+'</p>'
				if(data[i].task_status == 13 || data[i].task_status == 17){ //成功状态
					info+= '<p style="width:15%;color:rgba(24, 254, 240, 1)">'+data[i].task_status_des+'</p>';
				}else if(data[i].task_status == 6 || data[i].task_status == 8){  //错误状态
					info+= '<p style="width:15%;color:rgba(226, 46, 0, 1)">'+data[i].task_status_des+'</p>';
				}else{
					// 正常状态
					info+= '<p style="width:15%;color:rgba(6, 247, 161, 1)">'+data[i].task_status_des+'</p>';
				}
				info +='<div  style="width:20%;display:flex">'+
					'<div class="progress task-progress  progress-bar-60-back">'+
					'<div class="progress-bar progress-bar-60 " role="progressbar"'+
					'aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"'+
					'style="width: '+ data[i].task_progress+';">'+
					'</div>'+
					'</div>'+
					'</div>'+
					'<p style="width:20%;">'+ data[i].task_speed+'</p>';   //速度
				info += '</div></li>';
			}

		}
		$('#current-task-list').html(info);
		if(data.length == 1){
			var infoTip = '<div style="width: 100%;height: 75%;background-color: rgba(255, 255, 255, 0.08);display: flex;align-items: center;justify-content: center"><div style="color:rgba(190, 190, 190, 1);text-align: center;font-size: 1.3rem">'+LANG.UI_VIRTUAL_NO_MORE_TASKS+'</div></div>'
			$('.current-task #current-task').append(infoTip);
		}
		if(data.length == 2){
			var infoTip = '<div style="width: 100%;height: 45%;background-color: rgba(255, 255, 255, 0.04);display: flex;align-items: center;justify-content: center"><div style="color: rgba(190, 190, 190, 1);text-align: center;font-size: 1.3rem">'+LANG.UI_VIRTUAL_NO_MORE_TASKS+'</div></div>'
			$('.current-task #current-task').append(infoTip);
		}
		//定时器进来的时候先解绑旧的监听器
		$('#current-task')
        .off('mouseenter', hoverInHandler_scrollEnabled_task)
        .off('mouseleave', hoverOutHandler_scrollEnabled_task);
		// 当长度大于4，初始化滚动条
		if (data.length > 4) {
			if (!initTaskFlag) {
				$('#current-task').
				on('mouseenter', hoverInHandler_scrollEnabled_task).
				on('mouseleave', hoverOutHandler_scrollEnabled_task);
				initTaskFlag = true;
			}
		} else {
			// if (initTaskFlag) {
			// 	$('#current-task').off('mouseenter', hoverInHandler_scrollEnabled_task);
			// 	$('#current-task').off('mouseleave', hoverOutHandler_scrollEnabled_task);
			// 	initTaskFlag = false;
			// }
			scrollEnabled_task = false;
			initTaskFlag = false;
		}
		//这里通过数据长度来获取数据请求时间 假如数据长度*每秒移动时间 小于每次更新数据的时间5分钟 则数据请求时间间隔设置为5分钟 如果超过了五分钟 那么就设置为数据长度*每秒移动时间--至少保证移动完一次新的数据
		var needTime = data.length * 3500;
		taskTime = needTime >  taskMinTime ? needTime : taskMinTime;
		//清空定时器
		timers.timer1.destroy();
		delete timers.timer1;
		//再重新初始化定时器
		const timer1 = createAndManageTimer('timer1', initTaskList_timer, taskTime,false); //获取任务列表 五分钟一次 这次定时器不会立马执行
	}
	const hoverInHandler_scrollEnabled_task = () => {
		scrollEnabled_task = false;
	};
	const hoverOutHandler_scrollEnabled_task = () => {
		scrollEnabled_task = true;
	};
	//处理数据统计模块
	const initStatisticData_func = (d)=>{
        var data = d.data;
		//数据统计顶部
		initdataStatisticBar(data.data_statistics);
		// 底部存储统计
		initstorageStaticPie(data.storing_stastical);  //传入一个数组
		// 存储详情
		initStorageDetails(data.storage_details.list);

    }
	const initdataStatisticBar = (data)=>{
		// 缓存 DOM 元素
		const totalBackupData = $('#total_backup_data');
		const totalBackupDataUnit = $('#total_backup_data_unit');
		const totalCDPData = $('#total_cdp_data');
		const totalCDPDataUnit = $('#total_cdp_data_unit');
		const totalCopyData = $('#total_copy_data');
		const totalCopyDataUnit = $('#total_copy_data_unit');
		// 更新 DOM 元素
		totalBackupData.html(data.backup_data.value);
		totalBackupDataUnit.html(data.backup_data.unit);
		totalCDPData.html(data.cdp_data.value);
		totalCDPDataUnit.html(data.cdp_data.unit);
		totalCopyData.html(data.copy_data.value);
		totalCopyDataUnit.html(data.copy_data.unit);

		// 初始化数据
		var dateArr = data.list.dateArr;
		var backupinfo = data.list.backupinfo;
		var backup_point =  data.list.backuppoint;
		var cdpinfo = data.list.cdpinfo;;
		var cdp_point = data.list.cdppoint;
		var copyinfo = data.list.copyinfo;
		var copy_point = data.list.copypoint;

		// 配置项模板
		var option = {
			title:{
				text:'{a|▶ }{b|'+ LANG.UI_VISUAL_BACKUP +'}',
				left:'0%',
				top:'3%',
				textStyle:{
					fontWeight:'Regular',
					fontSize:14,
					color:"#DFDFDF",
					rich:{
						a:{
							color:'#18FEF0',
							opacity:0.3,
							fontsize:16
						}
					},
				}

			},
			tooltip: {
				trigger: 'axis',
				backgroundColor:'#000000',
				padding: [5, 15, 5, 15],
				borderColor:'rgba(24, 254, 240, 1)',
				formatter: function (params) {
					var str = '';
					params.forEach(function (item,index) {
						const marker = `<span style="display:inline-block;margin-right:6px;
						border-radius:50%;width:6px;height:6px;
						background-color:${item.color.colorStops[0].color}"></span>`;
						str+=`<div style="font-family: Source Han Sans CN, Source Han Sans CN;">${marker}<span style="display:inline-block;width:84px;font-size: 12px;color:#BEBEBE;margin-right: 3px" class="width100_en">${item.seriesName}</span><span style="color: #FFFFFF;font-size: 12px;">${item.data.des}${item.data.unit}</span></div>`;
					})
					return str;
				},
			},
			grid: {
				left: "1%",
				right: "1%",
				bottom: "5%",
				top:"18%",
				width: "auto",
				height: "auto",
				containLabel: true,
			},
			legend: {
				show: true,
				right: "1%",
				top:"2%",
				icon: "circle",
				itemWidth: 6,
				itemHeight: 6,
				textStyle:{
					color:"rgba(255, 255, 255)",
					fontSize:14
				}
			},
			xAxis: [
				{
					type: 'category',
					data: dateArr,
					axisPointer : {
						type: 'shadow',
						shadowStyle: {
							color: 'rgb(56,129,124,30%)',
							width: 'auto'
						},
					},
					axisTick: {
						show: false, // 不显示刻度线
					},
					axisLine: {
						lineStyle: {
							color: 'rgba(60, 103, 94, 1)',//坐标值得具体的颜色
						}
					},
					axisLabel:{
						show: true,
						textStyle:{
							color:'rgba(223, 223, 223, 1)',
							fontSize:'14px'
						}
					}
				}
			],
			yAxis: [
				{
					type: 'value',
					splitLine: {
						show: true,
						lineStyle: {
							color: 'rgba(60, 103, 94, 1)', //分割线的颜色
						}
					},
					axisLine: {
						show: false,
						lineStyle: {
							color: "#DFDFDF"
						}
					},
					axisLabel : {
						color:'rgba(223, 223, 223, 1)',
						textStyle:{
							fontSize:'14px'
						},
						formatter: function(value, index){
							var arrayUnit = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
							var j = 0 ;
							while(value >= 1024 ){
								value = value / 1024;
								j++;
							}
							if(j >= 1){
								return value.toFixed(1) + arrayUnit[j];
							}else{
								return value + arrayUnit[j];
							}
						}
					}
				}
			],
			series: [
				{
					name: LANG.UI_VISUAL_BACKUP,
					type: 'bar',
					itemStyle: {
						color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
							offset: 0,
							color: '#18FEF0'
						}, {
							offset: 1,
							color: 'rgba(24,254,240,0)'
						}]),//柱状图颜色
						borderRadius: [6, 6, 0, 0]
					},
					barWidth:'8px',
					data: backupinfo
				},
				{
					name:  LANG.UI_REPORY_CDP,
					type: 'bar',
					itemStyle: {
						color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
							offset: 0,
							color: '#06F7A1'
						}, {
							offset: 1,
							color: 'rgba(6,247,161,0)'
						}]),//柱状图颜色
						borderRadius: [6, 6, 0, 0]
					},
					barWidth:'8px',
					data: cdpinfo
				},
				{
					name: LANG.UI_CM_CDP_REPLICATION,
					type: 'bar',
					itemStyle: {
						color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
							offset: 0,
							color: '#FFFFFF'
						}, {
							offset: 1,
							color: 'rgba(255,255,255,0)'
						}]),//柱状图颜色
						borderRadius: [6, 6, 0, 0]
					},
					barWidth:'8px',
					data:copyinfo
				},
			
			],
			
		};
		// 设置 ECharts 选项
		dataStatisticBarInstance.setOption(option);

		// 配置折线图
		var lineoption = {
			title:{
				text:'{a|▶ }{b|'+ LANG.UI_VISUAL_RUN_TREND +'}',
				left:'0%',
				top:'3%',
				textStyle:{
					fontWeight:'Regular',
					fontSize:14,
					color:"#DFDFDF",
					rich:{
						a:{
							color:'#18FEF0',
							opacity:0.3,
							fontsize:16
						}
					},
				}

			},
			tooltip: {
				trigger: 'axis',
				backgroundColor:'#000000',
				padding: [5, 15, 5, 15],
				borderColor:'rgba(24, 254, 240, 1)',
				
				formatter: function (params) {
					var str = '';
					params.forEach(function (item,index) {
						const marker = `<span style="display:inline-block;margin-right:6px;
						border-radius:50%;width:6px;height:6px;
						background-color:${item.color}"></span>`;
						str+=`<div style="font-family: Source Han Sans CN, Source Han Sans CN;">${marker}<span style="display:inline-block;width:84px;font-size: 12px;color:#BEBEBE;margin-right: 3px" class="width100_en">${item.seriesName}</span><span style="color: #FFFFFF;font-size: 12px;">${item.data.des}${item.data.unit}</span></div>`;
					})
					return str;
				},
			},
			grid: {
				left: "1%",
				right: "1%",
				bottom: "5%",
				top:"18%",
				width: "auto",
				height: "auto",
				containLabel: true,
			},
			legend: {
				show: true,
				right: "1%",
				top:"3%",
				icon: "rect",
				itemWidth: 15, // 控制横线宽度
				itemHeight: 2, // 控制横线高度（粗细）
				// formatter:function (name) {
				// 	//用来格式化图例文本，支持字符串模板和回调函数两种形式。模板变量为图例名称 {name}
				// 	return  name +LANG.UI_VIRTUAL_DATA;
				// },
				textStyle:{
					color:"rgba(255, 255, 255)",
					fontSize:14
				}
			},
			xAxis: [
				{
					type: 'category',
					data: dateArr,
					axisPointer : {
						type: 'shadow',
						shadowStyle: {
							color: 'rgb(56,129,124,30%)',
							width: 'auto'
						},
					},
					axisTick: {
						show: false, // 不显示刻度线
					},
					axisLine: {
						lineStyle: {
							color: 'rgba(60, 103, 94, 1)',//坐标值得具体的颜色
						}
					},
					axisLabel:{
						show: true,
						textStyle:{
							color:'rgba(223, 223, 223, 1)',
							fontSize:'14px'
						}
					}
				}
			],
			yAxis: [
				{
					type: 'value',
					splitLine: {
						show: true,
						lineStyle: {
							color: 'rgba(60, 103, 94, 1)', //分割线的颜色
						}
					},
					axisLine: {
						show: false,
						lineStyle: {
							color: "#DFDFDF"
						}
					},
					axisLabel : {
						color:'rgba(223, 223, 223, 1)',
						textStyle:{
							fontSize:'14px'
						},
						formatter: function(value, index){
							var arrayUnit = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
							var j = 0 ;
							while(value >= 1024 ){
								value = value / 1024;
								j++;
							}
							if(j >= 1){
								return value.toFixed(1) + arrayUnit[j];
							}else{
								return value + arrayUnit[j];
							}
						}
					}
				}
			],
			series: [
				{
					name: LANG.UI_VISUAL_BACKUP,
					type: 'line',
					symbol: 'none',
					itemStyle: {
						color: "rgba(24, 254, 240, 1)",
						
						width:1
					},
					areaStyle:{
						color: {
							type: 'linear',
							x: 0,
							y: 0,
							x2: 0,
							y2: 1,
							colorStops: [{
								offset: 0,
								color: 'rgba(47, 253, 241, 0.20)' // 起始颜色
							}, {
								offset: 1,
								color: 'rgba(47, 253, 241, 0)' // 结束颜色
							}]
						}
					},
					data: backupinfo
				},
				{
					name:  LANG.UI_REPORY_CDP,
					type: 'line',
					symbol: 'none',
					itemStyle: {
						color: "rgba(6, 247, 161, 1)",
						width:1
					},
					areaStyle:{
						color: {
							type: 'linear',
							x: 0,
							y: 0,
							x2: 0,
							y2: 1,
							colorStops: [{
								offset: 0,
								color: 'rgba(6, 247, 161, 0.20)' // 起始颜色
							}, {
								offset: 1,
								color: 'rgba(6, 247, 161, 0)' // 结束颜色
							}]
						},
					},
					data: cdpinfo
				},
				{
					name: LANG.UI_CM_CDP_REPLICATION,
					type: 'line',
					symbol: 'none',
					itemStyle: {	
						color: "rgba(255, 255, 255, 1)",
						width:1
					},
					areaStyle:{
						color: {
							type: 'linear',
							x: 0,
							y: 0,
							x2: 0,
							y2: 1,
							colorStops: [{
								offset: 0,
								color: 'rgba(255, 255, 255, 0.20)' // 起始颜色
							}, {
								offset: 1,
								color: 'rgba(255, 255, 255, 0)' // 结束颜色
							}]
						},
					},
					data: copyinfo
	
				},
			
			]
		};


		// 设置 ECharts 选项
		dataTrendsLineInstance.setOption(lineoption);
	}
	const initstorageStaticPie = (data) =>{
	    // 缓存 DOM 元素
		const $storageNum = $('#storage-num');
		const $storageTotal = $('#storage-total');
		const $storageUseNum = $('#storage-use-num');
		const $storageUseUnit = $('#storage-use-num-company');
		const $storageFreeNum = $('#storage-free-num');
		const $storageFreeUnit = $('#storage-free-num-company');

		// 更新存储统计数据
		$storageNum.html(data.storage_num || 0);
		$storageTotal.html(data.total_storage_data || 0);
		$storageUseNum.html(data.use_size_des || '');
		$storageUseUnit.html(data.use_size_unit || '');
		$storageFreeNum.html(data.free_size_des || '');
		$storageFreeUnit.html(data.free_size_unit || '');
		// 配置项模板
		const optionTemplate = {
			series: [
				{
					name: LANG.UI_VISUAL_STORAGE_STATISTIC,
					type: 'pie',
					hoverAnimation: false,
					center: ['50%', '50%'],
					radius: ['65%', '85%'],
					avoidLabelOverlap: false,
					label: { show: false },
					labelLine: { show: false },
					data: [
						{ value: 0, name: LANG.UI_VISUAL_STORAGE_USED, itemStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{ offset: 0, color: '#06F7A1' }, { offset: 1, color: '#18FEF0' }]) } },
						{ value: 0, name: LANG.UI_VISUAL_STORAGE_UNUSED, itemStyle: { color: 'rgba(29,58,66,0.6)' } }
					]
				},
				{
					name: 'decorationOne',
					type: 'pie',
					color: ['#FFFFFF'],
					center: ['50%', '50%'],
					radius: ['52%', '51%'],
					hoverAnimation: false,
					label: { show: false },
					labelLine: { show: false },
					data: [{ value: 335, name: '' }]
				},
				{
					name: 'decorationTwo',
					type: 'pie',
					color: ['#FFFFFF', 'rgba(255,255,255,0)'],
					center: ['50%', '50%'],
					radius: ['22%', '23%'],
					hoverAnimation: false,
					label: { show: false },
					labelLine: { show: false },
					data: new Array(41).fill(10).map(() => ({ name: '', value: 20 }))
				},
				{
					name: 'decorationThree',
					type: 'pie',
					color: ['#FFFFFF', 'rgba(0,0,0,0)'],
					center: ['50%', '50%'],
					radius: ['99%', '100%'],
					hoverAnimation: false,
					label: { show: false },
					labelLine: { show: false },
					data: new Array(102).fill(10).map(() => ({ name: '', value: 20 }))
				},
				{
					name: 'decorationFour',
					type: 'pie',
					center: ['50%', '50%'],
					radius: ['52%', '23%'],
					hoverAnimation: false,
					label: { show: false },
					labelLine: { show: false },
					data: [{ value: 25, name: '', itemStyle: { color: 'rgba(24, 254, 240,0.05)' } }]
				},
				{
					name: 'decorationFive',
					type: 'pie',
					center: ['50%', '50%'],
					radius: ['22%', '0%'],
					hoverAnimation: false,
					label: { show: false },
					labelLine: { show: false },
					data: [{ value: 25, name: '', itemStyle: { color: 'rgba(24, 254, 240,0.05)' } }]
				}
			]
		};
		optionTemplate.series[0].data[0].value = data.use_size;
		optionTemplate.series[0].data[1].value = data.free_size;
		storageStaticPieInstance.setOption(optionTemplate);
		
	}
	const initStorageDetails = (data) => {
		// 缓存 DOM 元素
		const $storageDetailList = $('#storage-detail-list');
		
		const $visualBottomContent = $('.bottom-content');
		$('.storage-detail').css('height', '19.75rem');
		$('.bottom-content').css('height','0%');
		// 清空列表
		$storageDetailList.empty().off();
		$visualBottomContent.empty().off();
		if (data.length === 0) {
			const info = '<li style="text-align:center;color: #fff;font-size: 24px;">' + LANG.UI_VIRTUAL_NO_MORE_STORAGE + '</li>';
			$storageDetailList.html(info);
			scrollEnabled_storage = false;
			return;
		}
		// 使用数组拼接字符串以提高性能
		const items = [];
		for (let i = 0; i < data.length; i++) {
			let backgroundColor = '';
			let pointColor = 'rgba(22, 229, 216, 1)';
			if (data[i].store_status === 1) {  // 离线
				backgroundColor = 'rgba(255, 72, 61, 0.3)';
				pointColor = 'rgba(255, 72, 61, 1)';
			} 
			const statusText = data[i].store_status === 0 ? LANG.UI_VISUAL_ONLINE : LANG.UI_VISUAL_OFF_LINE;
			items.push(
				`<li class="storage-detail-item" style="background-color: ${backgroundColor};">
				<div class="storage-detail-list-box">
				<div style="width:25%;">${data[i].store_name }</div>
				<div style="width:20%;">${data[i].store_type_des  }</div>
				<div class="middle-progress" style="width:45%;">
				<div class="storage-detail-progress">
				<div class="storage-detail-progress-light" style="width:${data[i].use_space}%;">
				</div>
					<img src="/img/visualscreen/storage-progress-back.svg"></img>
				</div>
				<span> ${data[i].use_space_des}</span>
				</div>
				<div class="status-box" style="width:10%;">
				<div class="statuspoint" style="background-color: ${pointColor};"></div>
				<span> ${statusText} </span>
				</div>
				</div>
				</li>`
			);
		}

		// 设置 HTML 内容
		$storageDetailList.html(items.join(''));
		// 处理提示信息
		if (data.length === 1) {
			$('.storage-detail').css('height', 'auto');
			$('.bottom-content').css('height','64%');
			const infoTip = '<div style="width: 100%;height: 100%;background-color: rgba(255, 255, 255, 0.08);display: flex;align-items: center;justify-content: center"><div style="color:rgba(190, 190, 190, 1);text-align: center;font-size: 1.3rem">' + LANG.UI_VIRTUAL_NO_MORE_STORAGE + '</div></div>';
			$visualBottomContent.append(infoTip);
		} else if (data.length === 2) {
			$('.storage-detail').css('height', 'auto');
			$('.bottom-content').css('height','42%');
			const infoTip = '<div style="width: 100%;height: 100%;background-color: rgba(255, 255, 255, 0.04);display: flex;align-items: center;justify-content: center"><div style="color: rgba(190, 190, 190, 1);text-align: center;font-size: 1.3rem">' + LANG.UI_VIRTUAL_NO_MORE_STORAGE + '</div></div>';
			$visualBottomContent.append(infoTip);
		}
		$('#storage-detail')
		.off('mouseenter', hoverInHandler_scrollEnabled_storage)
		.off('mouseleave', hoverOutHandler_scrollEnabled_storage);
		// 当长度大于4，初始化滚动条
		if (data.length > 4) {
			if (!initStoreDetailFlag) {
				$('#storage-detail').on('mouseenter', hoverInHandler_scrollEnabled_storage);
				$('#storage-detail').on('mouseleave', hoverOutHandler_scrollEnabled_storage);
				initStoreDetailFlag = true;
			}
		} else {
			scrollEnabled_storage = false;
			initStoreDetailFlag = false;
		}
	}
	// 存储详情表单
	var hoverInHandler_scrollEnabled_storage = function() {
		scrollEnabled_storage = false;
	};

	var hoverOutHandler_scrollEnabled_storage = function() {
		scrollEnabled_storage = true;
	};
	// 处理中间部分各模块信息展示
	const initEveryModuleView_func = (d) =>{
		var data = d.data;
		//处理虚拟机数据
		initVMview(data);
		//处理私有云数据
		initPrivateCloudView(data);
		//处理公有云数据
		initPublicCloudView(data);
		//处理整机数据
		initCompleteMachineView(data);
		//处理卷数据
		initOsView(data);
		//处理文件数据
		initFileView(data);
		//处理NAS数据
		initNASView(data);
		//处理对象存储数据
		initObsView(data);
		//处理hadoop存储数据
		initHadoopView(data);
		//处理M365数据
		initM365View(data);
		//处理k8s数据
		initK8sView(data);
		//处理数据库数据
		initDBView(data);

		//处理整机实时数据
		initCDPCompleteMachineView(data);
		//处理卷实时数据
		initCDPVolView(data);

		//处理整机复制数据
		initReCompleteMachineView(data);
		//处理卷复制数据
		initReOSView(data);
		//处理数据库复制数据
		initReDBView(data);
		//处理文件复制数据
		initReFileView(data);
	}
	//处理虚拟机数据
	const initVMview = (data) => {
		//处理虚拟机数据显示
		const $vmCopy = $('.vm-copy');
		const $vmArchive = $('.vm-archive');
		$('#total_vm_num').html(data.vm_data.total_vm_num);
		$('#protected_vm_num').html(data.vm_data.protected_vm_num);
		$('#vm_backup_data').html(data.vm_data.backup_data);
		$('#vm_backup_data_unit').html(data.vm_data.backup_data_unit);
		$('#vm_protect_data_today').html(data.vm_data.protect_data_today);
		$('#vm_protect_data_today_unit').html(data.vm_data.protect_data_today_unit);
		$('#vm_protect_data').html(data.vm_data.protect_data);
		$('#vm_protect_data_unit').html(data.vm_data.protect_data_unit);
		if(!data.vm_data.copy && !data.vm_data.archive){
			$('.vmdataswiper').hide();
		}else{
			$('.vmdataswiper').show();
			if (!data.vm_data.copy) {
				$vmCopy.hide();
			}else{
				$('#vm_copy_data').html(data.vm_data.copy.copy_data+data.vm_data.copy.copy_data_unit);
				$('#vm_copy_point').html(data.vm_data.copy.copy_point);
				$('#vm_copy_task_num').html(data.vm_data.copy.copy_task_num);
				$vmCopy.show();
			}
			if(!data.vm_data.archive){
				$vmArchive.hide();
			}else{
				$('#vm_archive_data').html(data.vm_data.archive.archive_data+data.vm_data.archive.archive_data_unit);
				$('#vm_archive_point').html(data.vm_data.archive.archive_point);
				$vmArchive.show();
			}
		}
		//处理虚拟机图形摆放
		//设置假数据
		// data.vm_data.vm_type = [1,33,1,28,11,31,51,101];
		const vmTypes = data.vm_data.vm_type;
		const vmTypeCount = vmTypes.length;
		const positions = [
			{ bottom: '24.4rem', left: '20%' },
			{ bottom: '24.4rem', right: '20%' },
			{ bottom: '11.4rem', left: '9%' },
			{ bottom: '11.4rem', right: '9%' },
			{ bottom: '-1.4rem', left: '20%' },
			{ bottom: '-1.4rem',  right: '20%' }
		];
		if(vmTypeCount == 0){
			//只显示那四个
			$('.vmnodataimg').show();
			$('.vmdataimg').hide(); 
		}else{
			$('.vmnodataimg').hide();
			$('.vmdataimg img').hide();
			//控制哪些虚拟化显示
			//虚拟化类型不能显示一样的两个
			const vm_name_arr = [];
			let index = 0;
			data.vm_data.vm_type.forEach((item)=>{
				let name = vm_type[item];
				if(vm_name_arr.indexOf(name) == -1){
				    vm_name_arr.push(name);
					if(vm_name_arr.length <= 6){
						//最多显示6个
						//给每个item设置样式
						$(".vmdataimg #"+name+"_item").css(positions[index]);
						$(".vmdataimg #"+name+"_item").show();
						index++;
					}
				}else{
					return; //跳过本次循环
				}
				
			});
			$('.vmdataimg').show();
		}

	}
	//处理私有云数据
	const initPrivateCloudView = (data) =>{
		const $priCloudCopy = $('.privateCloud-copy');
		const $priCloudArchive = $('.privateCloud-archive');
		$('#total_privateCloud_num').html(data.privateCloud_data.total_privateCloud_num);
		$('#protected_privateCloud_num').html(data.privateCloud_data.protected_privateCloud_num);
		$('#privateCloud_backup_data').html(data.privateCloud_data.backupDataSize.value);
		$('#privateCloud_backup_data_unit').html(data.privateCloud_data.backupDataSize.unit);
		$('#privateCloud_protect_data_today').html(data.privateCloud_data.protect_data_today);
		$('#privateCloud_protect_data_today_unit').html(data.privateCloud_data.protect_data_today_unit);

		if(!data.privateCloud_data.copy && (data.privateCloud_data.archive_data.value == 0 && data.privateCloud_data.archive_num == 0)){
			$('.privateClouddataswiper').hide();
		}else{
			$('.privateClouddataswiper').show();
			if (!data.privateCloud_data.copy) {
				$priCloudCopy.hide();
			}else{
				$('#privateCloud_copy_data').html(data.privateCloud_data.copy.copy_data+data.privateCloud_data.copy.copy_data_unit);
				$('#privateCloud_copy_point').html(data.privateCloud_data.copy.copy_point);
				$('#privateCloud_copy_task_num').html(data.privateCloud_data.copy.copy_task_num);
				$priCloudCopy.show();
			}
			if(data.privateCloud_data.archive_data.value == 0 && data.privateCloud_data.archive_num == 0){
				$priCloudArchive.hide();
			}else{
				$('#privateCloud_archive_data').html(data.privateCloud_data.archive_data.value+data.privateCloud_data.archive_data.unit);
				$('#privateCloud_archive_point').html(data.privateCloud_data.archive_num);
				$priCloudArchive.show();
			}
		}
		//处理图形摆放位置
		const privateCloudTypes = data.privateCloud_data.privateCloud_type;
		const privateCloudTypeCount = privateCloudTypes.length;
		const positions = [
			{ bottom: '10.375rem', left: '13%' },
			{ bottom: '10.375rem', right: '13%' },
			{ bottom: '25rem', left: 'calc(50% - 54px)' },
		];
		if(privateCloudTypeCount == 0){
			//只显示那三个
			$('.privateCloudnodataimg').show();
			$('.privateClouddataimg').hide(); 
		}else{
			$('.privateCloudnodataimg').hide();
			$('.privateClouddataimg img').hide();
			//公有云类型不能显示一样的两个
			const privateCloud_name_arr = [];
			let index = 0;
			data.privateCloud_data.privateCloud_type.forEach((item)=>{
				let name = '';
				if(privateCloud_type.hasOwnProperty(item)){
					name = privateCloud_type[item];
				}else{
					name = 'openstack';
				}
				if(privateCloud_name_arr.indexOf(name) == -1){
					privateCloud_name_arr.push(name);
					if(privateCloud_name_arr.length <= 3){
						//最多显示3个
						//给每个item设置样式
						$(".privateClouddataimg #"+name+"_item").css(positions[index]);
						$(".privateClouddataimg #"+name+"_item").show();
						index++;
					}
				}else{
					return; //跳过本次循环
				}
				
			});
			$('.privateClouddataimg').show();
		}
	}
	//处理公有云数据
	const initPublicCloudView = (data)=>{
		const $pubCloudCopy = $('.publicCloud-copy');
		const $pubCloudArchive = $('.publicCloud-archive');
		$('#total_publicCloud_num').html(data.publicCloud_data.total_publicCloud_num);
		$('#protected_publicCloud_num').html(data.publicCloud_data.protected_publicCloud_num);
		$('#publicCloud_backup_data').html(data.publicCloud_data.backupDataSize.value);
		$('#publicCloud_backup_data_unit').html(data.publicCloud_data.backupDataSize.unit);
		$('#publicCloud_protect_data_today').html(data.publicCloud_data.protect_data_today);
		$('#publicCloud_protect_data_today_unit').html(data.publicCloud_data.protect_data_today_unit);
		
		if(!data.publicCloud_data.copy && (data.publicCloud_data.archive_data.value == 0 && data.publicCloud_data.archive_num == 0)){
			$('.publicClouddataswiper').hide();
		}else{
			$('.publicClouddataswiper').show();
			if (!data.publicCloud_data.copy) {
				$pubCloudCopy.hide();
			}else{
				$('#publicCloud_copy_data').html(data.publicCloud_data.copy.copy_data+data.publicCloud_data.copy.copy_data_unit);
				$('#publicCloud_copy_point').html(data.publicCloud_data.copy.copy_point);
				$('#publicCloud_copy_task_num').html(data.publicCloud_data.copy.copy_task_num);
				$pubCloudCopy.show();
			}
			if(data.publicCloud_data.archive_data.value == 0 && data.publicCloud_data.archive_num == 0){
				$pubCloudArchive.hide();
			}else{
				$('#publicCloud_archive_data').html(data.publicCloud_data.archive_data.value+data.publicCloud_data.archive_data.unit);
				$('#publicCloud_archive_point').html(data.publicCloud_data.archive_num);
				$pubCloudArchive.show();
			}
		}
		//处理图形摆放
		// data.publicCloud_data.publicCloud_type = [100,100,101,101,101,100]
		const publicCloudTypes = data.publicCloud_data.publicCloud_type;
		const publicCloudTypeCount = publicCloudTypes.length;
		const positions = [
			{ bottom: '24.4rem', left: '20%' },
			{ bottom: '24.4rem', right: '20%' },
			{ bottom: '11.4rem', left: '9%' },
			{ bottom: '11.4rem', right: '9%' },
		];
		if(publicCloudTypeCount == 0){
			//只显示那四个
			$('.publicCloudnodataimg').show();
			$('.publicClouddataimg').hide(); 
		}else{
			$('.publicCloudnodataimg').hide();
			$('.publicClouddataimg img').hide();
			//公有云类型不能显示一样的两个
			const publicCloud_name_arr = [];
			let index = 0;
			data.publicCloud_data.publicCloud_type.forEach((item)=>{
				let name = publicCloud_type[item];
				if(publicCloud_name_arr.indexOf(name) == -1){
					publicCloud_name_arr.push(name);
					if(publicCloud_name_arr.length <= 4){
						//最多显示4个
						//给每个item设置样式
						$(".publicClouddataimg #"+name+"_item").css(positions[index]);
						$(".publicClouddataimg #"+name+"_item").show();
						index++;
					}
				}else{
					return; //跳过本次循环
				}
				
			});
			$('.publicClouddataimg').show();
		}
	}
	//处理整机数据
	const initCompleteMachineView = (data)=>{
		const $osCopy = $('.machine-os-copy');
		const $osArchive = $('.machine-os-archive');
		$('#total_machine_os_num').html(data.completeMachine_data.total_os_num);
		$('#protected_machine_os_num').html(data.completeMachine_data.protected_os_num);
		$('#machine_os_backup_data').html(data.completeMachine_data.backup_data);
		$('#machine_os_backup_data_unit').html(data.completeMachine_data.backup_data_unit);
		$('#machine_os_protect_data').html(data.completeMachine_data.protect_data);
		$('#machine_os_protect_data_unit').html(data.completeMachine_data.protect_data_unit);
		$('#machine_os_protect_data_today').html(data.completeMachine_data.protect_data_today);
		$('#machine_os_protect_data_today_unit').html(data.completeMachine_data.protect_data_today_unit);
		//卷只有副本没有归档
		if(!data.completeMachine_data.copy && !data.completeMachine_data.archive){
			$('.machine-os-dataswiper').hide();
		}else{
			$('.machine-os-dataswiper').show();
			if (!data.completeMachine_data.copy) {
				$osCopy.hide();
			}else{
				$('#machine_os_copy_data').html(data.completeMachine_data.copy.copy_data+data.completeMachine_data.copy.copy_data_unit);
				$('#machine_os_copy_point').html(data.completeMachine_data.copy.copy_point);
				$('#machine_os_copy_task_num').html(data.completeMachine_data.copy.copy_task_num);
			}
			if(!data.completeMachine_data.archive){
				$osArchive.hide();
			}else{
				$('#machine_os_archive_data').html(data.completeMachine_data.archive.archive_data+data.completeMachine_data.archive.archive_data_unit);
				$('#machine_os_archive_point').html(data.completeMachine_data.archive_point);
				$osArchive.show();
			}
		}
	}
	//处理卷数据
	const initOsView = (data) => {
		const $osCopy = $('.os-copy');
		const $osArchive = $('.os-archive');
		$('#total_os_num').html(data.os_data.total_os_num);
		$('#protected_os_num').html(data.os_data.protected_os_num);
		$('#os_backup_data').html(data.os_data.backup_data);
		$('#os_backup_data_unit').html(data.os_data.backup_data_unit);
		$('#os_protect_data_today').html(data.os_data.protect_data_today);
		$('#os_protect_data_today_unit').html(data.os_data.protect_data_today_unit);
		$('#os_protect_data').html(data.os_data.protect_data);
		$('#os_protect_data_unit').html(data.os_data.protect_data_unit);
		//卷只有副本没有归档
		if(!data.os_data.copy && !data.os_data.archive){
			$('.os-dataswiper').hide();
		}else{
			$('.os-dataswiper').show();
			if (!data.os_data.copy) {
				$osCopy.hide();
			}else{
				$('#os_copy_data').html(data.os_data.copy.copy_data+data.os_data.copy.copy_data_unit);
				$('#os_copy_point').html(data.os_data.copy.copy_point);
				$('#os_copy_task_num').html(data.os_data.copy.copy_task_num);
			}
			if(!data.os_data.archive){
				$osArchive.hide();
			}else{
				$('#os_archive_data').html(data.os_data.archive.archive_data+data.os_data.archive.archive_data_unit);
				$('#os_archive_point').html(data.os_data.archive_point);
				$osArchive.show();
			}
		}
	}

	const initFileView = (data) => {
		$('#total_file_num').html(data.file_data.total_file_num);
		$('#protected_file_num').html(data.file_data.protected_file_num);
		$('#file_backup_data').html(data.file_data.backup_data);
		$('#file_backup_data_unit').html(data.file_data.backup_data_unit);
		$('#file_protect_data_today').html(data.file_data.protect_data_today);
		$('#file_protect_data_today_unit').html(data.file_data.protect_data_today_unit);
		$('#file_protect_data').html(data.file_data.protect_data);
		$('#file_protect_data_unit').html(data.file_data.protect_data_unit);
		if(!data.file_data.copy){
			$('.filedataswiper').hide();
		}else{
			$('#file_copy_data').html(data.file_data.copy.copy_data+data.file_data.copy.copy_data_unit);
			$('#file_copy_point').html(data.file_data.copy.copy_point);
			$('#file_copy_task_num').html(data.file_data.copy.copy_task_num);
			$('.filedataswiper').show();
		}
	}

	const initNASView = (data) => {
		$('#total_nas_num').html(data.nas_data.total_nas_num);
		$('#protected_nas_num').html(data.nas_data.protected_nas_num);
		$('#nas_backup_data').html(data.nas_data.backup_data);
		$('#nas_backup_data_unit').html(data.nas_data.backup_data_unit);
		$('#nas_protect_data_today').html(data.nas_data.protect_data_today);
		$('#nas_protect_data_today_unit').html(data.nas_data.protect_data_today_unit);
		$('#nas_protect_data').html(data.nas_data.protect_data);
		$('#nas_protect_data_unit').html(data.nas_data.protect_data_unit);
		if(!data.nas_data.copy ){
			$('.nasdataswiper').hide();
		}else{
			$('#nas_copy_data').html(data.nas_data.copy.copy_data+data.nas_data.copy.copy_data_unit);
			$('#nas_copy_point').html(data.nas_data.copy.copy_point);
			$('#nas_copy_task_num').html(data.nas_data.copy.copy_task_num);
			$('.nasdataswiper').show();
		}
	}

	const initObsView = (data)=>{
		$('#total_obs_num').html(data.obs_data.total);
		$('#protected_obs_num').html(data.obs_data.protectedNum);
		$('#obs_backup_data').html(data.obs_data.backupDataSize.value);
		$('#obs_backup_data_unit').html(data.obs_data.backupDataSize.unit);
		$('#obs_protect_data').html(data.obs_data.protect_data);
		$('#obs_protect_data_unit').html(data.obs_data.protect_data_unit);
		$('#obs_protect_data_today').html(data.obs_data.protect_data_today);
		$('#obs_protect_data_today_unit').html(data.obs_data.protect_data_today_unit);
		// 副本
		if (!data.obs_data.copy) {
			$('.obsdataswiper').hide();
		}else{
			$('#obs_copy_data').html(data.obs_data.copy.copy_data+data.obs_data.copy.copy_data_unit);
			$('#obs_copy_point').html(data.obs_data.copy.copy_point);
			$('#obs_copy_task_num').html(data.obs_data.copy.copy_task_num);
			$('.obsdataswiper').show();
		}
		//处理对象存储图形
		if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
			// 英文版
			data.obs_data.vendor.forEach((vendor, index, array) => {
				if (vendor == 1) {
					array[index] = 1.1;
				} else if (vendor == 2) {
					array[index] = 2.1;
				}
			});
		}
		const obsVendors = data.obs_data.vendor;
		const obsVendorCount = obsVendors.length;
		const positions = [
			{ bottom: '24.4rem', left: '20%' },
			{ bottom: '24.4rem', right: '20%' },
			{ bottom: '11.4rem', left: '9%' },
			{ bottom: '11.4rem', right: '9%' },
			{ bottom: '-1.4rem', left: '20%' },
			{ bottom: '-1.4rem',  right: '20%' }
		];
		if(obsVendorCount == 0){
			//只显示那四个
			$('.obsdataimg').hide();
		}else{
			$('.obsdataimg img').hide();
			//控制哪些对象存储显示
			//对象存储类型不能显示一样的两个
			const obs_name_arr = [];
			let index = 0;
			data.obs_data.vendor.forEach((item)=>{
				let name = obsVendorType[item];
				if(obs_name_arr.indexOf(name) == -1){
				    obs_name_arr.push(name);
					if(obs_name_arr.length <= 6){
						//最多显示6个
						//给每个item设置样式
						$(".obsdataimg #"+name+"_item").css(positions[index]);
						$(".obsdataimg #"+name+"_item").show();
						index++;
					}
				}else{
					return; //跳过本次循环
				}
				
			});
			$('.obsdataimg').show();
		}

	}

	const initHadoopView = (data)=>{
		$('#total_hadoop_num').html(data.hadoop_data.total);
		$('#protected_hadoop_num').html(data.hadoop_data.protectedNum);
		$('#hadoop_backup_data').html(data.hadoop_data.backupDataSize.value);
		$('#hadoop_backup_data_unit').html(data.hadoop_data.backupDataSize.unit);
		$('#hadoop_protect_data').html(data.hadoop_data.protect_data);
		$('#hadoop_protect_data_unit').html(data.hadoop_data.protect_data_unit);
		$('#hadoop_protect_data_today').html(data.hadoop_data.protect_data_today);
		$('#hadoop_protect_data_today_unit').html(data.hadoop_data.protect_data_today_unit);
		
		if(!data.hadoop_data.copy){
			$('.hadoopdataswiper').hide();
		}else{
			$('#hadoop_copy_data').html(data.hadoop_data.copy.copy_data+data.hadoop_data.copy.copy_data_unit);
			$('#hadoop_copy_point').html(data.hadoop_data.copy.copy_point);
			$('#hadoop_copy_task_num').html(data.hadoop_data.copy.copy_task_num);
			$('.hadoopdataswiper').show();
		}
	}
	
	const initM365View = (data)=>{
		$('#protected_user_num').html(data.office365_data.protected_user_num);
		$('#office365_backup_host_data').html(data.office365_data.backup_data);
		$('#office365_backup_host_data_unit').html(data.office365_data.backup_data_unit);
		$('#office365_protect_data').html(data.office365_data.protect_data);
		$('#office365_protect_data_unit').html(data.office365_data.protect_data_unit);
		$('#office365_protect_data_today').html(data.office365_data.protect_data_today);
		$('#office365_protect_data_today_unit').html(data.office365_data.protect_data_today_unit);
		if(!data.office365_data.copy){
			$('.m365dataswiper').hide();

		}else{
			$('.m365dataswiper').show();
			$('#office365_copy_data').html(data.office365_data.copy.copy_data+data.office365_data.copy.copy_data_unit);
			$('#office365_copy_point').html(data.office365_data.copy.copy_point);
			$('#office365_copy_task_num').html(data.office365_data.copy.copy_task_num);
			$('.m365dataswiper').show();
		}
	}

	const initK8sView = (data)=>{
		$('#total_k8s_num').html(data.k8s_data.totalNum);
		$('#protected_k8s_num').html(data.k8s_data.protectNum);
		$('#k8s_backup_data').html(data.k8s_data.backupDataSize.value);
		$('#k8s_backup_data_unit').html(data.k8s_data.backupDataSize.unit);
		$('#k8s_protect_data').html(data.k8s_data.protect_data);
		$('#k8s_protect_data_unit').html(data.k8s_data.protect_data_unit);
		$('#k8s_protect_data_today').html(data.k8s_data.protect_data_today);
		$('#k8s_protect_data_today_unit').html(data.k8s_data.protect_data_today_unit);
		if(!data.k8s_data.copy){
			$('.k8sdataswiper').hide();
		}else{
			$('#k8s_copy_data').html(data.k8s_data.copy.copy_data+data.k8s_data.copy.copy_data_unit);
			$('#k8s_copy_point').html(data.k8s_data.copy.copy_point);
			$('#k8s_copy_task_num').html(data.k8s_data.copy.copy_task_num);
			$('.k8sdataswiper').show();
		}
	}

	const initDBView = (data)=>{
		$('#total_database_num').html(data.database_data.total_database_num);
		$('#protected_database_num').html(data.database_data.protected_database_num);
		$('#database_backup_data').html(data.database_data.backup_data);
		$('#database_backup_data_unit').html(data.database_data.backup_data_unit);
		$('#database_protect_data_today').html(data.database_data.protect_data_today);
		$('#database_protect_data_today_unit').html(data.database_data.protect_data_today_unit);
		$('#database_protect_data').html(data.database_data.protect_data);
		$('#database_protect_data_unit').html(data.database_data.protect_data_unit);


		if(!data.database_data.copy){
			$('.dbdataswiper').hide();
		}else{
			$('#database_copy_data').html(data.database_data.copy.copy_data+data.database_data.copy.copy_data_unit);
			$('#database_copy_point').html(data.database_data.copy.copy_point);
			$('#database_copy_task_num').html(data.database_data.copy.copy_task_num);
			$('.dbdataswiper').show();
		}
		//处理数据库图形摆放
		//设置假数据
		// data.database_data.database_type = [4,8,8,6,9];
		const databaseTypes = data.database_data.database_type;
		const databaseTypeCount = databaseTypes.length;
		const positions = [
			{ bottom: '23.4rem', left: '26%' },
			{ bottom: '23.4rem', right: '26%' },
			{ bottom: '5.4rem', left: '10%' },
			{ bottom: '5.4rem', right: '10%' },
			
		];
		if(databaseTypeCount == 0){
			//只显示那四个
			$('.dbnodataimg').show();
			$('.dbdataimg').hide(); 
		}else{
			$('.dbnodataimg').hide();
			$('.dbdataimg img').hide();
			//控制哪些虚拟化显示
			//虚拟化类型不能显示一样的两个
			const db_name_arr = [];
			let index = 0;
			data.database_data.database_type.forEach((item)=>{
				let name = database_type[item];
				if(db_name_arr.indexOf(name) == -1){
				    db_name_arr.push(name);
					if(db_name_arr.length <= 4){
						//最多显示6个
						//给每个item设置样式
						$(".dbdataimg #"+name+"_item").css(positions[index]);
						$(".dbdataimg #"+name+"_item").show();
						index++;
					}
				}else{
					return; //跳过本次循环
				}
				
			});
			$('.dbdataimg').show();
		}

	}
	const initCDPCompleteMachineView = (data)=>{
		$('#total_cdp_machine_os_num').html(data.cdp_completeMachine_data.total_cdp_num);
		$('#protected_cdp_machine_os_num').html(data.cdp_completeMachine_data.protected_cdp_num);
		$('#cdp_machine_os_backup_data').html(data.cdp_completeMachine_data.backup_data);
		$('#cdp_machine_os_backup_data_unit').html(data.cdp_completeMachine_data.backup_data_unit);
		$('#cdp_machine_os_protect_data').html(data.cdp_completeMachine_data.protect_data);
		$('#cdp_machine_os_protect_data_unit').html(data.cdp_completeMachine_data.protect_data_unit);
		$('#cdp_machine_os_protect_data_today').html(data.cdp_completeMachine_data.protect_data_today);
		$('#cdp_machine_os_protect_data_today_unit').html(data.cdp_completeMachine_data.protect_data_today_unit);
	}
	
	const initCDPVolView = (data)=>{
		$('#total_cdp_os_num').html(data.cdp_data.total_cdp_num);
		$('#protected_cdp_os_num').html(data.cdp_data.protected_cdp_num);
		$('#cdp_os_backup_data').html(data.cdp_data.backup_data);
		$('#cdp_os_backup_data_unit').html(data.cdp_data.backup_data_unit);
		$('#cdp_os_protect_data').html(data.cdp_data.protect_data);
		$('#cdp_os_protect_data_unit').html(data.cdp_data.protect_data_unit);
		$('#cdp_os_protect_data_today').html(data.cdp_data.protect_data_today);
		$('#cdp_os_protect_data_today_unit').html(data.cdp_data.protect_data_today_unit);
	}
	const initReDBView = (data)=>{
		$('#total_replicate_db_num').html(data.replication_db_data.protect_host_num);
		$('#replicate_db_task_num').html(data.replication_db_data.task_num);
		$('#replicate_db_protect_data').html(data.replication_db_data.protect_data);
		$('#replicate_db_protect_data_unit').html(data.replication_db_data.protect_data_unit);
		$('#replicate_db_protect_data_today').html(data.replication_db_data.protect_data_today);
		$('#replicate_db_protect_data_today_unit').html(data.replication_db_data.protect_data_today_unit);
	}

	const initReFileView =  (data)=>{
	    $('#total_replicate_file_num').html(data.replication_fs_data.protect_object_num);
		$('#replicate_file_task_num').html(data.replication_fs_data.task_num);
		$('#replicate_file_protect_data').html(data.replication_fs_data.protect_data);
		$('#replicate_file_protect_data_unit').html(data.replication_fs_data.protect_data_unit);
		$('#replicate_file_protect_data_today').html(data.replication_fs_data.protect_data_today);
		$('#replicate_file_protect_data_today_unit').html(data.replication_fs_data.protect_data_today_unit);
	}

	const initReCompleteMachineView = (data)=>{
		$('#total_replicate_machine_os_num').html(data.replication_completeMachine_data.protected_host_num);
		$('#replicate_machine_os_task_num').html(data.replication_completeMachine_data.task_num);
		$('#replicate_machine_os_protect_data').html(data.replication_completeMachine_data.protect_data);
		$('#replicate_machine_os_protect_data_unit').html(data.replication_completeMachine_data.protect_data_unit);
		$('#replicate_machine_os_protect_data_today').html(data.replication_completeMachine_data.protect_data_today);
		$('#replicate_machine_os_protect_data_today_unit').html(data.replication_completeMachine_data.protect_data_today_unit);
	}

	const initReOSView = (data)=>{
		$('#total_replicate_os_num').html(data.replication_os_data.protected_host_num);
		$('#replicate_os_task_num').html(data.replication_os_data.task_num);
		$('#replicate_os_protect_data').html(data.replication_os_data.protect_data);
		$('#replicate_os_protect_data_unit').html(data.replication_os_data.protect_data_unit);
		$('#replicate_os_protect_data_today').html(data.replication_os_data.protect_data_today);
		$('#replicate_os_protect_data_today_unit').html(data.replication_os_data.protect_data_today_unit);

	}
	

	//初始化节点监控数据
	let initNodeMonitor_func = function(d) {
		var data = d.data.list;
		//动态生成html
		$('#nodename-swiper').empty().off();
		$('#node-swiper').empty().off();
		//设置标题HTML
		var nodenameinfo = '';
		nodenameinfo += '<div class="swiper-wrapper">'
		if(data == undefined || data.length == 0){
			var nodatastr = '<div class="monitor-nodata"><div class="nodata-box"><img src="../../img/visualscreen/monitor-nodata.svg"><div>'+LANG.UI_SYSTEM_MONITOR_NULL_DATA+'</div></div></div>';
			$('#node-swiper').append(nodatastr);
		    return;
		}
		data.forEach((item,index) => {
			var statusclass = item.node_status == 0 ? 'green-status' : 'red-status';
			nodenameinfo += `<div class="swiper-slide" data-node="${index}">${item.ip}
								<div class="status ${statusclass}">
								</div>
							</div>`
		});
		nodenameinfo += '</div>';
		$('#nodename-swiper').append(nodenameinfo);
		//设置内容HTML
		var contentinfo = '';
		contentinfo += '<div class="swiper-wrapper">'
		data.forEach((item,index) => {
			contentinfo += `
				<div class="swiper-slide">
                        <div class="visual-bottom-content">
                            <div class="left-content">
                                <div class="node-left">
                                    <div>
                                        <div class="node-left-title" style="color:rgba(24, 254, 240, 1)">
                                            <div class="node-triangle"></div>
                                            <span class="node-item-left-title" style="color: rgba(223, 223, 223, 1)">${LANG.UI_VIRTUAL_NODE_MONITOR_CPU_USAGE}</span>
                                        </div>
                                        <div class="leftechart">
                                            <div id="cpuGauge${index}" class="bottom-left-chart"></div>
                                            <img src="../../img/visualscreen/node-back-circle.svg" style="position: absolute;top: 0;left: 0;bottom: 0;right: 0;margin: auto;z-index: 999;text-align: center">
                                            <span class="node-left-num"  id="node-left-num_cpuGauge${index}" style="color: rgba(24, 254, 240, 1);"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="node-left-title" style="color:rgba(6, 247, 161, 1)">
                                            <div class="node-triangle"></div>
                                            <span class="node-item-left-title" style="color: rgba(223, 223, 223, 1)">${LANG.UI_VIRTUAL_NODE_MONITOR_MEMORY_USAGE}</span>
                                        </div>
                                        <div class="leftechart">
                                            <div id="memoryGauge${index}"  class="bottom-left-chart"></div>
                                            <img src="../../img/visualscreen/node-back-circle.svg" style="position: absolute;top: 0;left: 0;bottom: 0;right: 0;margin: auto;z-index: 999;text-align: center">
                                            <span class="node-left-num"  id="node-left-num_memoryGauge${index}" style="color: rgba(24, 254, 240, 1);"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="node-middle">
                                    <div class="network-bandwidth">
                                        <div class="node-item-left">
                                            <div class="node-item-left-box" style="color: rgba(23, 254, 254, 1);;">
                                                <div class="node-triangle">
                                                </div>
                                                <span class="node-item-left-title">${LANG.UI_VIRTUAL_NODE_MONITOR_NETWORK_BANDWIDTH}</span>
                                            </div>
											<div class="node-speed-des">
												${LANG.UI_VISUAL_REAL_TIME_RATE}
											</div>
                                            <div style="color: rgba(23, 254, 254, 1);margin-bottom:0.6rem;">
                                                <span class="node-content">${LANG.UI_VIRTUAL_NODE_MONITOR_INFLOW}</span>
                                                <span id="inflow${index}" class="node-content-num ml-0_en"></span>
                                                <span id="inflowCompany${index}" class="node-content"></span>
                                            </div>
                                            <div style="color: rgba(6, 247, 161, 1)">
                                                <span class="node-content">${LANG.UI_VIRTUAL_NODE_MONITOR_OUTFLOW}</span>
                                                <span id="outflow${index}" class="node-content-num ml-0_en"></span>
                                                <span id="outflowCompany${index}" class="node-content"></span>
                                            </div>
                                        </div>
                                        <div id="networkBandwidthChart${index}" class="bottom-chart">

                                        </div>

                                    </div>

                                    <div class="system-load">
                                        <div class="node-item-left">
                                            <div class="node-item-left-box" style="color:rgba(179, 249, 254, 1)">
                                                <div class="node-triangle">
                                                </div>
                                                <span class="node-item-left-title">${LANG.UI_VIRTUAL_NODE_MONITOR_SYSTEM_LOAD}</span>
                                            </div>
											<div class="node-speed-des">
												${LANG.UI_VISUAL_REAL_TIME_RATE}
											</div>
                                            <div style="color: rgba(75, 215, 255, 1);margin-bottom: 0.5rem;">
                                                <span class="system-load-line">——</span>
                                                <span id="load1-${index}" class="system-load-num"></span>
                                            </div>
                                            <div style="color: rgba(66, 136, 242, 1);margin-bottom: 0.5rem;">
                                                <span class="system-load-line">——</span>
                                                <span id="load5-${index}" class="system-load-num"></span>
                                            </div>
                                            <div style="color: rgba(179, 249, 254, 1)">
                                                <span class="system-load-line">——</span>
                                                <span id="load15-${index}" class="system-load-num"></span>
                                            </div>
                                        </div>
                                        <div id="systemLoadChart${index}" class="bottom-chart">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="node-right">
                                    <div class="bps">
                                        <div class="node-item-left">
                                            <div class="node-item-left-box" style="color: rgba(139, 196, 255, 1);">
                                                <div class="node-triangle">
                                                </div>
                                                <span class="node-item-left-title">${LANG.UI_VIRTUAL_NODE_MONITOR_DISK_BPS}</span>
                                            </div>
											<div class="node-speed-des">
												${LANG.UI_VISUAL_REAL_TIME_RATE}
											</div>
                                            <div style="color: rgba(139, 196, 255, 1);margin-bottom:0.6rem;">
                                                <span class="node-content">${LANG.UI_VIRTUAL_NODE_MONITOR_READ}</span>
                                                <span id="bpsread${index}" class="node-content-num ml-0_en"></span>
                                                <span id="bpsreadCompany${index}" class="node-content"></span>
                                            </div>
                                            <div style="color: rgba(24, 254, 240, 1)">
                                                <span class="node-content">${LANG.UI_VIRTUAL_NODE_MONITOR_WRITE}</span>
                                               	<span id="bpswrite${index}" class="node-content-num ml-0_en"></span>
                                                <span id="bpswriteCompany${index}" class="node-content"></span>
                                            </div>
                                        </div>
                                        <div id="bpsChart${index}" class="bottom-chart">

                                        </div>

                                    </div>

                                    <div class="iops">
                                        <div class="node-item-left">
                                            <div class="node-item-left-box" style="color: rgba(6, 247, 171, 1);">
                                                <div class="node-triangle">
                                                </div>
                                                <span class="node-item-left-title">${LANG.UI_VIRTUAL_NODE_MONITOR_DISK_IPOS}</span>
                                            </div>
											<div class="node-speed-des">
												${LANG.UI_VISUAL_REAL_TIME_RATE}
											</div>
                                            <div style="color: rgba(6, 247, 171, 1);margin-bottom:0.6rem;">
                                                <span class="node-content">${LANG.UI_VIRTUAL_NODE_MONITOR_READ}</span>
                                                <span id="iopsread${index}" class="node-content-num ml-0_en"></span>
                                                <span id="iopsreadCompany${index}" class="node-content"></span>
                                            </div>
                                            <div style="color: rgba(75, 215, 255, 1)">
                                                <span class="node-content">${LANG.UI_VIRTUAL_NODE_MONITOR_WRITE}</span>
                                                <span id="iopswrite${index}" class="node-content-num ml-0_en"></span>
                                                <span id="iopswriteCompany${index}" class="node-content"></span>
                                            </div>
                                        </div>
                                        <div id="iopsChart${index}" class="bottom-chart">

                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
			`;
		})
		contentinfo += '</div>';
		$('#node-swiper').append(contentinfo);
		//初始化swiper
		initnodeSwiper();
		//初始化echart
		initnodeEchart(data);
	}
	//初始化一次
	const initnodeSwiper = ()=>{
		const tabswiper = new Swiper('.monitor-box .swiper-container', {
			direction: 'horizontal',
			// 循环模式
			loop: false,
			// 每页显示数量
			slidesPerView: 'auto',
			// 间距
			spaceBetween: 50,
		  });
		const contentSwiper = new Swiper('.monitor-box .swiper-contents', {
			slidesPerView: 1,      // 一次显示 1 个 slides
			direction: 'horizontal',
			// 防止弹回的关键设置
			freeMode: false,
			resistance: true,
			resistanceRatio: 0,
			followFinger: false,
			allowTouchMove: false,


		  });
		//初始化完了之后给第一个swiper添加active
		$('.monitor-box .swiper-container .swiper-slide').eq(0).addClass('active');

		 // 点击标题切换整组 slides
		$('.visual-bottom .swiper-container .swiper-slide').on('click', function() {
			const groupIndex = $(this).data('node'); // 获取 data-group 的值
			contentSwiper.slideTo(groupIndex, 10, false);
			// 更新 active 状态
			$('.visual-bottom .swiper-container .swiper-slide').removeClass('active');
			$(this).addClass('active');
		});
	}
	//初始化一次
	const initnodeEchart = (data)=>{
		//设置右边的echart
		initEcharts(data);
		//设置左边的echart
		initUsechart(data);
	}
	const initEcharts = (data)=>{
		var option = {
			tooltip: {
				trigger: 'axis',
			},
			grid: {
				left: 0,
				top: 2,
				right: 0,
				bottom: 2
			},
			xAxis:
				{
					type: 'category',
					boundaryGap: false,
					axisLine: {
						show: true,
						lineStyle: {
							type: 'solid',
							color: 'rgba(213, 213, 213, 0.6)',
							width: 0.3
						}
					},
					splitLine: {
						show: false,
						lineStyle: {
							color: 'rgba(213, 213, 213, 0.6)',
							width: 0.3
						}
					},
					axisTick: {
						show: false
					},
					axisLabel: {
						show: false
					},
					data: [],
					inverse: true // 将 x 轴反向展示
				},
			yAxis:
				{
					type: 'value',
					splitLine: {
						show: true,
						interval: 0,
						lineStyle: {
							color: 'rgba(213, 213, 213, 0.6)',
							width: 0.3
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
						show: false,
					},
				},
			series: [
				// 流出
				{
					type: 'line',
					showSymbol: false,
					hoverAnimation: false,
					// smooth: true,
					// smoothMonotone: 'x',
					animation: false,
					itemStyle: {
						normal: {
							lineStyle: {
								width: 1,
								color: 'rgba(6, 247, 161, 1)'
							}
						}
					},
					data: [],
				},
				// 流入
				{
					type: 'line',
					showSymbol: false,
					hoverAnimation: false,
					// smooth: true,
					// smoothMonotone: 'x',
					animation: false,
					itemStyle: {
						normal: {
							lineStyle: {
								width: 1,
								color: 'rgba(23, 254, 254, 1)'
							}
						}
					},
					data: [],
				}
			],

		};
		//option2只有系统负载在用
		var option2 = {
			tooltip: {
				trigger: 'axis',
			},
			grid: {
				left: 0,
				top: 26,
				right: 10,
				bottom: 2
			},
			color: ['rgba(75, 215, 255, 1)', 'rgba(66, 136, 242, 1)', 'rgba(179, 249, 254, 1)'],
			legend: {
				show: true,
				right: "4%",
				top: "2%",
				bottom: "2%",
				icon: "circle",
				itemWidth: 14,
				itemHeight: 8,
				textStyle: {
					color: "rgba(190, 190, 190, 1)",
					fontSize: 12,
				}
			},
			xAxis:
				{
					type: 'category',
					boundaryGap: false,
					axisLine: {
						show: true,
						lineStyle: {
							type: 'solid',
							color: 'rgba(213, 213, 213, 0.6)',
							width: 0.3
						}
					},
					splitLine: {
						show: false,
						lineStyle: {
							color: 'rgba(213, 213, 213, 0.6)',
							width: 0.3
						}
					},
					axisTick: {
						show: false
					},
					axisLabel: {
						show: false
					},
					data: [],
					inverse: true // 将 x 轴反向展示
				},
			yAxis:
				{
					type: 'value',
					splitLine: {
						show: true,
						interval: 0,
						lineStyle: {
							color: 'rgba(213, 213, 213, 0.6)',
							width: 0.3
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
						show: false,
					},
				},
			series: [
				// load 1
				{
					name: 'Load 1m',
					type: 'line',
					showSymbol: false,
					hoverAnimation: false,
					// smooth: true,
					// smoothMonotone: 'x',
					animation: false,
					itemStyle: {
						normal: {
							lineStyle: {
								width: 1,
								// color:'rgba(75, 215, 255, 1)'
							}
						}
					},
					data: [],
				},
				// load 5
				{
					name: 'Load 5m',
					type: 'line',
					showSymbol: false,
					hoverAnimation: false,
					// smooth: true,
					// smoothMonotone: 'x',
					animation: false,
					itemStyle: {
						normal: {
							lineStyle: {
								width: 1,
								// color:'rgba(66, 136, 242, 1)'
							}
						}
					},
					data: [],
				},
				// load 15
				{
					name: 'Load 15m',
					type: 'line',
					showSymbol: false,
					hoverAnimation: false,
					animation: false,
					itemStyle: {
						normal: {
							lineStyle: {
								width: 1,
							}
						}
					},
					data: [],
				}
			],

		};
		if (data.length == 0) return;
		data.forEach((item,index) => {
			//初始化网络带宽图表
			//改变option 网络带宽
			// 改变颜色
			option.series[0].itemStyle.normal.lineStyle.color = 'rgba(6, 247, 161, 1)';
			option.series[1].itemStyle.normal.lineStyle.color = 'rgba(23, 254, 254, 1)';

			let networkBandwidthChartinfo = document.getElementById('networkBandwidthChart' + index);
			let networkBandwidthChartinfoChart =  echarts.init(networkBandwidthChartinfo);
			networkBandwidthChartinfoChart.setOption(option);
			//初始化bps
			//磁盘BPS
			option.series[0].itemStyle.normal.lineStyle.color = 'rgba(139, 196, 255, 1)';
			option.series[1].itemStyle.normal.lineStyle.color = 'rgba(24, 254, 240, 1)';
			let bpsChartinfo = document.getElementById('bpsChart' + index);
			let bpsChartinfoChart = echarts.init(bpsChartinfo);
			bpsChartinfoChart.setOption(option);
			//初始化iops
			//磁盘
			option.series[0].itemStyle.normal.lineStyle.color = 'rgba(6, 247, 171, 1)'
			option.series[1].itemStyle.normal.lineStyle.color = 'rgba(75, 215, 255, 1)';
			let iopsChartinfo = document.getElementById('iopsChart' + index);
			let iopsChartinfoChart = echarts.init(iopsChartinfo);
			iopsChartinfoChart.setOption(option);
			//初始化系统负载
			let systemLoadChartinfo = document.getElementById('systemLoadChart' + index);
			let systemLoadChartinfoChart = echarts.init(systemLoadChartinfo);
			systemLoadChartinfoChart.setOption(option2);
		});
	}

	const initUsechart = (data) => {
		if (data.length == 0) return;
		data.forEach((item,index) => {
			initnodeGauge('cpuGauge'+index,item.cpu_use,'rgba(23, 254, 254, 1)','rgba(23, 254, 254, 0.6)');
			initnodeGauge('memoryGauge'+index,item.memory_use,'rgba(6, 247, 161, 1)','rgba(6, 247, 161, 0.6)');
		});
	}

	//初始化节点监控echat 
	const initnodeGauge = (className,val,color,color2) => {   //  cpu_use
		var nodeGaugeinfo = document.getElementById(className);
		var nodeGauge = echarts.init(nodeGaugeinfo);
		// 存储实例在 DOM 元素上
		var option = {
			series: [
				{
					type: 'gauge',
					center: ['50%', '50%'],
					zlevel: 20,
					startAngle: 240,
					endAngle: -60,
					min: 0,
					max: 1,
					splitNumber: 100,
					roundCap: true,
					radius: '100%',
					axisTick: {
						show: false
					},
					splitLine: {
						show: false
					},
					axisLabel: {
						show: false
					},
					pointer: {
						show: false
					},
					title: {
						show: false
					},
					progress: {
						// show:false,
						roundCap: true,
						show: true,
						width: 4
					},
					axisLine: {
						roundCap: true,
						lineStyle: {
							width: 4,
							color: [[val / 100, color], [1, '#8A8C90']]
						}
					},
					detail: {
						show: false
					},
					data: []
				},
				{
					name: 'decorationOne',
					type: 'pie',
					zlevel: 10,
					color: ['#7B7D7D'],
					center: ['50%', '50%'],
					radius: ['98%', '94%'],
					hoverAnimation: false,
					lable: {
						normal: {
							show: false,
						},
						emphasis: {
							show: false,
						},
					},
					labelLine: {
						normal: {
							show: false,
						},
					},
					data: [
						{value: 335, name: ''},
					],
				},
				{
					type: 'gauge',
					center: ['50%', '50%'],
					radius: '93%',
					splitNumber: 10,
					zlevel: -1,
					roundCap: true,
					min: 0,
					max: 100,
					startAngle: 240,
					endAngle: -60,
					axisTick: {
						show: false,
	
					}, //刻度样式
					progress: {
						// show:false,
						roundCap: true,
						show: true,
						width: 4
					},
					axisLine: {
						show: true,
						lineStyle: {
							width: 50,
							color: [
								[
									val / 100, new echarts.graphic.LinearGradient(
									0, 1, 0, 0, [{
										offset: 0,
										color: 'rgba(0, 0, 0, 0)',
									},
										{
											offset: 1,
											color: color2,
										}
									]
								)
								],
	
							]
						}
					},
					splitLine: {
						show: false,
	
					}, //分隔线样式
					axisLabel: {
						show: false,
	
					},
					pointer: {
						show: false
					},
					detail: {
						show: false
					}
				},
			]
		}
		if (val == 100) {
			option.series[0].axisLine.lineStyle.color = [[1, color]]
		}
		nodeGauge.setOption(option);
	}
	//更新节点监控数据
	const initMonitorData_func = (d)=>{
		var data = d.data.list;
		if (data == undefined||data.length == 0 ) return;
		data.forEach((item,index) => {
			//初始化网络带宽图表
			let networkBandwidthChartinfo = document.getElementById('networkBandwidthChart' + index);
			//获取ecahrts实例
			let networkBandwidthChartinfoChart = echarts.getInstanceByDom(networkBandwidthChartinfo);
			if (networkBandwidthChartinfoChart) {
				//如果有echarts实例
				//设置网络数据
				// setNetworkData(item.netData, networkBandwidthChartinfoChart, index);
				setNetworkData(item.node_uuid,networkBandwidthChartinfoChart, index);
			} 
			
			//初始化bps
			let bpsChartinfo = document.getElementById('bpsChart' + index);
			let bpsChart = echarts.getInstanceByDom(bpsChartinfo);
			if(bpsChart){
				setBpsData(item.bpsData, bpsChart,index);
			}
			//初始化iops
			let iopsChartinfo = document.getElementById('iopsChart' + index);
			let iopsChart = echarts.getInstanceByDom(iopsChartinfo);
			if(iopsChart){
				setIopsData(item.iopsData, iopsChart,index);
			}
			//初始化系统负载
			let systemLoadChartinfo = document.getElementById('systemLoadChart' + index);
			let systemLoadChart = echarts.getInstanceByDom(systemLoadChartinfo);
			if(systemLoadChart){
				setSystemLoad(item.SystemLoadData, systemLoadChart, index);
			}

			//初始化CPU和内存
			let cpuChartinfo = document.getElementById('cpuGauge' + index);
			let cpuChart = echarts.getInstanceByDom(cpuChartinfo);
			if(cpuChart){
				setcpuData(item.cpu_use, cpuChart, index);
			}
			let memoryChartinfo = document.getElementById('memoryGauge' + index);
			let memoryChart = echarts.getInstanceByDom(memoryChartinfo);
			if(memoryChart){
				setmemoryData(item.memory_use, memoryChart, index);
			}
		});

	}

	//更新节点网络监控数据
	const handleNetWorkData = (data,index)=>{

		if(netOutData[index].length != 0 && netInData[index].length != 0 && data.x_time.length != 0 && data.y_val.length != 0) {
            // netOutData[index].shift();
            // netInData[index].shift();
			let outInData = groupOutInData(data.y_val);
			netOutData[index].pop();
			netOutData[index].unshift(outInData['sumOut'][outInData['sumOut'].length - 1]);
			netInData[index].pop();
			netInData[index].unshift(outInData['sumIn'][outInData['sumIn'].length - 1]);
        }else{
			let outInData = groupOutInData(data.y_val);
			netOutData[index] = [];
            netInData[index] = [];
			if(data.y_val.length == 0) {
                netOutData[index] = [];
                netInData[index] = [];
            } else {
                netOutData[index] = outInData['sumOut'].reverse();
                netInData[index] = outInData['sumIn'].reverse();
            }
		}
	}
	//把所有out、in数据分组，方便后面计算
    const groupOutInData = function (data) {
        const outArrays = [];
        const inArrays = [];
        //将out数据和in的数据分类
        for (const key in data) {
            if (key.endsWith("out")) {//以out结尾的
                outArrays.push(data[key]);
            } else if (key.endsWith("in")) {//以in结尾的
                inArrays.push(data[key]);
            }
        }
        const sumOut = sumOutInData(outArrays);
        const sumIn = sumOutInData(inArrays);
        return {sumOut, sumIn};
    }
	//将输入/输出数据数组转为数字 按列相加 保留两位小数
    const sumOutInData = function (data) {
        if (data.length === 0) return [];
        // 将数组中的字符串转为数字
        const intArrays = data.map(arr => arr.map(Number));
        const minLength = Math.min(...intArrays.map(arr => arr.length));
        const result = new Array(minLength).fill(0);
        // 按列相加
        for (const arr of intArrays) {
            for (let i = 0; i < minLength; i++) {
                result[i] += arr[i];
            }
        }
        //保留两位小数
        return result.map(num => Number(num.toFixed(1)));
    }

	// const setNetworkData = (data, chart, index) => {
	// 	let option = chart.getOption();
	// 	option.series[1].data = data.receive;
	// 	option.series[0].data = data.transmit;;
	// 	chart.setOption(option);
	// 	$("span[id='inflow" + index + "']").html(data.receive_des[data.receive_des.length - 1]);
	// 	$("span[id='inflowCompany" + index + "']").html(data.receive_des_unit[data.receive_des_unit.length - 1]);
	// 	$("span[id='outflow" + index + "']").html(data.transmit_des[data.transmit_des.length - 1]);
	// 	$("span[id='outflowCompany" + index + "']").html(data.transmit_des_unit[data.transmit_des_unit.length - 1]);
	// }
	const setNetworkData = (node_uuid, chart, index) => {
		//设置请求参数
		var info = {};
		info.node_uuid = node_uuid;
		info.time_range = '';
		info.range_start_time = '';
		info.range_end_time = '';
		info.cpu_alarm = '';
		info.ram_alarm = '';
		info.root_alarm = '';
		
		info = JSON.stringify(info);
		//同步执行ajax
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.SYSTEMMONITOR,f:'initDataFunc',p:info},
	        success: function(d){ 
	        	var data = JSON.parse(d);
				//处理网络数据
				if(netInData[index] == null){
					netInData[index] = [];
				}
				if(netOutData[index] == null){
					netOutData[index] = [];
				}
				handleNetWorkData(data['netWorkMsg'],index);
                initNetWorkData_current(chart,index);
	        } 
		});
	}
	const initNetWorkData_current = (chart,index) => { 
		let option = chart.getOption();
		option.series[0].data = netOutData[index];
		option.series[1].data = netInData[index];
		chart.setOption(option);
		let ininfo =  v1_calsize_to_value_and_unit(netInData[index][0]);
		let outinfo = v1_calsize_to_value_and_unit(netOutData[index][0]);

		$("span[id='inflow" + index + "']").html(ininfo['value']);
		$("span[id='inflowCompany" + index + "']").html(ininfo['unit']+"/s");
		$("span[id='outflow" + index + "']").html(outinfo['value']);
		$("span[id='outflowCompany" + index + "']").html(outinfo['unit']+"/s");
	}

	/**
	 * 方法库-字节转换-转换成MB格式等
	 * @param int $num       数值
	 * @return array
	 */
	const v1_calsize_to_value_and_unit = function($num = 0)
	{
		let info = {
			'value': 0,
			'unit' :'',
		}
		let num = parseFloat($num);

		let type = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'BB', 'NB', 'DB'];
		var j = 0;
		while (num >= 1024) {
			if (j >= 11) {
				return $num + type[j];
			}
			num = num / 1024;
			j++;
		}
		num = Number(num.toFixed(0)); // toFixed 返回字符串，用 Number 转回数字;
		info['value'] = num;
		info['unit'] = type[j];
		return info;
	}


	const setBpsData = (data, chart, index) => {
		let option = chart.getOption();
		option.series[0].data = data.read;
		option.series[1].data = data.write;
		chart.setOption(option);
		$("span[id='bpsread" + index + "']").html(data.read_des[data.read_des.length - 1]);
		$("span[id='bpsreadCompany" + index + "']").html(data.read_des_unit[data.read_des_unit.length - 1]);
		$("span[id='bpswrite" + index + "']").html(data.write_des[data.write_des.length - 1]);
		$("span[id='bpswriteCompany" + index + "']").html(data.write_des_unit[data.write_des_unit.length - 1]);
	}
	const setIopsData = (data, chart, index) => {
		let option = chart.getOption();
		option.series[0].data = data.read;
		option.series[1].data = data.write;
		chart.setOption(option);
		$("span[id='iopsread" + index + "']").html(data.read_des[data.read_des.length - 1]);
		$("span[id='iopswrite" + index + "']").html(data.write_des[data.write_des.length - 1]);
		if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
			$("span[id='iopsreadCompany" + index + "']").html(data.read_des_unit);
			$("span[id='iopswriteCompany" + index + "']").html(data.write_des_unit);
		}else{
			$("span[id='iopsreadCompany" + index + "']").html(data.read_des_unit_en);
			$("span[id='iopswriteCompany" + index + "']").html(data.write_des_unit_en);
		}
		
	}
	const setSystemLoad = (data, chart, index) => {
		let option = chart.getOption();
		option.series[0].data = data.load_1;
		option.series[1].data = data.load_5;
		option.series[2].data = data.load_15;
		chart.setOption(option);
		$("span[id='load1-" + index + "']").html(data.load_1[data.load_1.length - 1]);
		$("span[id='load5-" + index + "']").html(data.load_5[data.load_5.length - 1]);
		$("span[id='load15-" + index + "']").html(data.load_15[data.load_15.length - 1]);
	}
	const setcpuData = (data, chart, index) => {
	    let option = chart.getOption();
		option.series[0].axisLine.lineStyle.color[0][0] = data/100
		option.series[2].axisLine.lineStyle.color[0][0] = data/100;
		chart.setOption(option);
		$("span[id='node-left-num_cpuGauge" + index + "']").html(data);
			
	}
	const setmemoryData = (data, chart, index) => {
	    let option = chart.getOption();
		option.series[0].axisLine.lineStyle.color[0][0] = data/100
		option.series[2].axisLine.lineStyle.color[0][0] = data/100;
		chart.setOption(option);
		$("span[id='node-left-num_memoryGauge" + index + "']").html(data);
			
	}
	//处理时间
	const getTime_func = (d)=>{
		var data = d.data.date;
		initSystemTime(data);
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

	//展示系统时间
	const initSystemTime = (myDate)=>{
		var lastTime = myDate;
		var nowtime = new Date(lastTime);
		timenum = nowtime.getTime();
        nowtime = new Date(nowtime.getTime());
		const year = nowtime.getFullYear();
		const month = zero(nowtime.getMonth() + 1);
		const day  = zero(nowtime.getDate());
		const weekIndex = nowtime.getDay();
		const weekList = [
			LANG.UI_VISUAL_SUNDAY,
			LANG.UI_VISUAL_MONDAY,
			LANG.UI_VISUAL_TUESDAY,
			LANG.UI_VISUAL_WEDNESDAY,
			LANG.UI_VISUAL_THURSDAY,
			LANG.UI_VISUAL_FRIDAY,
			LANG.UI_VISUAL_SATURDAY
		];
		const week = weekList[weekIndex];
		const currentDate =  month + "/" + day + "/" + year;

		$("#currentDate").html(currentDate);
		$("#currentWeek").html(week);
	}
	//处理系统时间 每隔一秒处理一次
	const setSecond_timer = ()=>{
		timenum += 1000;
		const date = new Date(timenum);
		const hours = zero(date.getHours());
		const minutes = zero(date.getMinutes());
		const seconds = zero(date.getSeconds());
		$('.time-box #hours').html(hours);
		$('.time-box #minutes').html(minutes);
		$('.time-box #seconds').html(seconds);
	}
	
	//--------------------各模块数据处理end---------------
	//---------------------创建和管理定时器start--------------
	
	//name为定时器名称
    //callback为定时器执行的回调函数
    //interval为定时器执行的间隔时间（毫秒）
	//timerRunFlag为定时器是否立即执行
    // 管理多个定时器的集合,整个页面定时器都在这里面
    class IntervalTimer {
		constructor(name, callback, interval, timerRunFlag = true) {
			this.name = name;
			this.callback = callback;
			this.interval = interval;
			this.timerId = null;
			this.isActive = false;
			this.timerRunFlag = timerRunFlag;
			// 自动启动定时器
			this.start();
		}

		// 启动定时器
		start() {
			//先执行一次回调
			if (this.timerRunFlag) {
				this.callback();
			}
			if (!this.isActive && this.timerId === null) {
				this.isActive = true;
				this.timerId = setInterval(() => {
					try {
						this.callback();
					} catch (error) {
						this.stop(); // 如果回调函数出错，停止定时器
					}
				}, this.interval);
			}
		}

		// 停止定时器
		stop() {
			if (this.timerId !== null) {
				clearInterval(this.timerId);
				this.timerId = null;
				this.isActive = false;
			}
		}

		// 销毁定时器实例，确保完全清除所有引用
		destroy() {
			this.stop();
			this.callback = null; // 清除对回调函数的引用
		}
	}
    // 确保在页面卸载时清理所有定时器
    window.addEventListener('beforeunload', () => {
        Object.values(timers).forEach(timer => timer.destroy());
		 // 清空定时器对象
		Object.keys(timers).forEach(key => delete timers[key]);
		// 清理所有动画实例
        Object.keys(animations).forEach(key => {
            if (animations[key]) {
                animations[key].kill();
                animations[key] = null;
            }
        });
    });
	const createAndManageTimer = (name, callback, interval, timerRunFlag)=>{
		if (!timers[name]) {
			timers[name] = new IntervalTimer(name, callback, interval, timerRunFlag);
		}
		return timers[name];
	}
	//---------------------创建和管理定时器end--------------

	//---------------------初始化动画start--------------
	const initAnimition = ()=>{
		//初始化运行动画
		//数据复制--整机
		gsap.registerPlugin(MotionPathPlugin);
		if (animations.reMachinosDot1== null) {
			animations.reMachinosDot1 = initAnimationfunc("#replicate-machine-os-dot1", "#replicate-machine-os-dot1-line",false)
		}
		if (animations.reMachinosDot2 == null) {
			animations.reMachinosDot2 = initAnimationfunc("#replicate-machine-os-dot2", "#replicate-machine-os-dot2-line",false)
		}
		if (animations.reMachinosDot3 == null) {
			animations.reMachinosDot3 = gsap.to("#replicate-machine-os-dot3", {
				duration: 6,
				repeat: -1,
				repeatDelay: 0,
				ease: "linear",
				motionPath: {
					path: "#replicate-machine-os-dot3-line",
					autoRotate: true,
					alignOrigin: [0.5, 0.5] // 确保这里是一个有效的数组
				}
			});
		}
		//卷复制
		if (animations.reOsDot1 == null) {;
			animations.reOsDot1 = initAnimationfunc("#replicate-os-dot1", "#replicate-os-dot1-line",false)
		}
		if (animations.reOsDot2 == null) {
			animations.reOsDot2 = initAnimationfunc("#replicate-os-dot2", "#replicate-os-dot2-line",false)
		}
		
		if (animations.reFsImg1 == null) {
			animations.reFsImg1 = gsap.to("#replicate-file-img1", {
				repeat: -1,
				repeatDelay: 0,
				x: 175,          // 向右移动 194px
				opacity: 0.3,    // 透明度从 1 渐变到 0.3（变淡）
				duration: 2.5,     // 动画时长 2 秒
			})
		}

		if (animations.reDbDot1 == null) {
			animations.reDbDot1 = initAnimationfunc("#replicate-db-dot1", "#repliacte-db-dot1-line",false)
		}
		
		if (animations.reDbDot2 == null) {
			animations.reDbDot2 = initAnimationfunc("#replicate-db-dot2", "#repliacte-db-dot2-line",false)
		}
		if (animations.reDbDot3 == null) {
		    animations.reDbDot3 = gsap.to("#replicate-db-dot3", {
				duration: 6,
				repeat: -1,
				repeatDelay: 0,
				ease: "linear",
				motionPath: {
					path: "#repliacte-db-dot3-line",
					autoRotate: true,
					alignOrigin: [0.5, 0.5] // 确保这里是一个有效的数组
				}
			});
		}
		if (animations.cdpMachineDot1 == null){
			animations.cdpMachineDot1 = initAnimationfunc("#cdp-machine-os-dot1", "#cdp-machine-os-dot1-line",true)
		}
		if (animations.cdpMachineDot2 == null){
			animations.cdpMachineDot2 = initAnimationfunc("#cdp-machine-os-dot2", "#cdp-machine-os-dot2-line",true)
		}
		
		if (animations.cdpOsDot1 == null){
			animations.cdpOsDot1 = initAnimationfunc("#cdp-os-dot1", "#cdp-os-dot1-line",true)
		}
		if (animations.cdpOsDot2 == null){
			animations.cdpOsDot2 = initAnimationfunc("#cdp-os-dot2", "#cdp-os-dot2-line",true)
		}
	}
	const initAnimationfunc = (dot,line,autoRotate)=>{
		return gsap.to(dot, {
			duration: 6,
			repeat: -1,
			repeatDelay: 0,
			ease: "linear",
			align: "self",
			motionPath: {
				path: line,
				align: "self",
				autoRotate: autoRotate,
				alignOrigin: [0.5, 0.5] // 确保这里是一个有效的数组
			}
		});
	}
	
	//---------------------初始化动画end--------------
	//初始化当前数据
	const initAllData = ()=>{
		initconfig(); //初始化配置
		initAuthConfig(); //获取授权配置
		initNodeMonitor(); //初始化节点监控
		initAnimition(); //初始化动画

	    //创建多个定时器
		const timer1 = createAndManageTimer('timer1', initTaskList_timer, taskMinTime); //获取任务列表 五分钟一次
		const timer2 = createAndManageTimer('timer2', initTime_timer, 60000); //初始化时间 一分钟校对一次 避免时间漂移
		const timer3 = createAndManageTimer('timer3', setSecond_timer, 1000); //前端自己设置时间
		const timer4 = createAndManageTimer('timer4', initTaskAndWarning_timer, 60000); //初始化任务和告警 一分钟一次
		const timer5 = createAndManageTimer('timer5', updateMonitorData_timer, 5000); //初始化左下角节点监控数据 5秒一次
		const timer6 = createAndManageTimer('timer6', initStatisticData_timer, 18000000); //初始化右边备份数据 5小时一次
		const timer7 = createAndManageTimer('timer7', initEveryModuleView_timer, 300000); //初始化中间部分各模块数据 5分钟一次
		const timer8 = createAndManageTimer('timer8', scrollContent_task_timer, 3500); //任务表格滚动定时器
		const timer9 = createAndManageTimer('timer9', scrollContent_storage_timer, 3500); //存储表格滚动定时器
		initDataSwiperMove(); //让中间数据模块的swiper复制一份
	}
	const initGetLang = ()=>{
		let initGetLang_func = function(d){
			var data = d.data;
			CONF.LANGUAGE = data.lang;
		}
		pAjaxRequest({},'/api/v1/visual/lang','GET',initGetLang_func,true,{},true);
	}
	const initDataSwiperMove = ()=>{
		document.querySelectorAll('.dataswiper .swiper-wrapper .content').forEach((scrollContent, index) => {
			// 克隆内容，实现无缝衔接
			scrollContent.innerHTML += scrollContent.innerHTML;
		})
		
	
	}
	//初始化一次
	const initTabSwiper = () => {
		let currentSubIndex = 0;
		let currentIndex = 0;
		// 存储所有子 Swiper 实例
		let subSwipers = [];
		let isAutoSwitchActive = true; // 控制自动切换状态  后续这个看是否要删除 用于解决自动切换时，不能点击的问题
		let autoSwitchInterval = null; // 自动切换定时器
		let mainSwiper = null;
		function initMainSwiper(){
			mainSwiper = new Swiper('.vertical-swiper-container .swiper-container', {
				direction: 'vertical',
				slidesPerView: 1,
				spaceBetween: 20,
				mousewheel: true,
				// 导航控制
				navigation: {
				  nextEl: '.swiper-button-next-vertical',
				  prevEl: '.swiper-button-prev-vertical',
				},
				// 其他配置
				loop: true, // 可根据需求开启循环
				autoplay: false, // 可根据需求开启自动播放
				speed: 600,
				// 循环模式下需要额外的虚拟slide
				loopAdditionalSlides: 3,
				loopSlides: 3, // 如果你的slide数量是3个
				on: {
					init: function() {
						startAutoSwitch();
					},
					slideChange: function() {
						currentIndex = this.activeIndex;
						currentSubIndex = 0
						updateswiper(currentIndex);
					},
					destroy: function() {
						stopAutoSwitch();
					}
				},
				
			});
			currentIndex = mainSwiper.activeIndex; // 当前主 Swiper 的索引
		}
		
		function initSubSwipers(){
			// 初始化每个子 Swiper
			document.querySelectorAll('.sub-swiper').forEach((container, index) => {
				const subSwiper = new Swiper(container, {
					direction: 'horizontal',
					allowTouchMove: true, // 禁用手动滑动
					slidesPerView:'auto',
					initialSlide: 0, // 默认选中第一个 slide
					// 启用进度条分页
					pagination: {
						el: container.querySelector('.swiper-pagination'),
						type: 'progressbar',
					},
					on: {
					    init: function () {
							 setTimeout(() => {
                        		updateProgressBarPosition(this, 0);
							 },0)
						},
						slideChange:function(){
							updateProgressBarPosition(this, 0);
						}
		
						
					}
				});
				subSwipers.push(subSwiper);
			});
			//默认给第一个大的swiper的子swiper添加active
			$(mainSwiper.slides).eq(currentIndex).find('.sub-swiper').find('.swiper-slide').eq(0).addClass('active');
			//默认给第一张图添加active
			var activeSlide = subSwipers[currentIndex].slides[currentSubIndex];
			showGraph($(activeSlide).data('graph'));
		}

		function updateProgressBarPosition(swiper,subindex) {
			const $swiperEl = $(swiper.el);               // 当前 swiper 容器
			const $pagination = $swiperEl.find('.swiper-pagination'); // 找到当前 swiper 下的分页器
			if ($pagination.length === 0) return;
			const progress = subindex / swiper.slides.length;
			const width = 1/swiper.slides.length;
			const $progressbar = $pagination.find('.swiper-pagination-progressbar-fill');
			$progressbar.css('display', 'block');
			if ($progressbar.length > 0) {
				$progressbar.css('transform', `scaleX(${width})`);
				$progressbar.css('left',`${progress*100}%`)
			}
		}
		function updateswiper(index){
			// 在这里更新页面上的其他元素，比如子 Swiper 的重置
			if (subSwipers && subSwipers[index] && !subSwipers[index].destroyed) {
				currentSubIndex = 0;
				subSwipers[index].slideTo(0); // 将对应的子 Swiper 重置到第一个 slide
				$(subSwipers[index].slides).removeClass('active');
				$(subSwipers[index].slides[currentSubIndex]).addClass('active');
				showGraph($(subSwipers[index].slides[currentSubIndex]).data('graph'));
			}
		}
		// 自动切换逻辑
		function startAutoSwitch() {
			stopAutoSwitch();
			autoSwitchInterval =  setInterval(() => {
				if (!mainSwiper || mainSwiper.destroyed) {
					stopAutoSwitch();
					return;
				}
	
				const currentSubSwiper = subSwipers[currentIndex];
				if (!currentSubSwiper || currentSubSwiper.destroyed) {
					stopAutoSwitch();
					return;
				}
				// 如果子 Swiper 还有下一项
				if (currentSubIndex < currentSubSwiper.slides.length - 1) {
					currentSubIndex++;
					
					$(currentSubSwiper.slides).removeClass('active');
					$(currentSubSwiper.slides[currentSubIndex]).addClass('active');
					currentSubSwiper.slideTo(currentSubIndex);
				} else {
					// 子 Swiper 切换完毕，切换到下一个主 Swiper
					if (currentIndex < mainSwiper.slides.length - 1) {
						mainSwiper.slideNext();
					} else {
						// 所有主 Swiper 都切换完毕，回到第一个主 Swiper
						mainSwiper.slideTo(0);
					}
				}
				var activeSlide = subSwipers[currentIndex].slides[currentSubIndex]; //这里再实时获取一次activeSlide
				updateProgressBarPosition(subSwipers[currentIndex],currentSubIndex)
				showGraph($(activeSlide).data('graph'));
			}, 5000); // 每隔 3 秒切换一次
			// $('.sub-swiper-container .swiper-wrapper').removeClass('stop-animating');
		}
		function stopAutoSwitch() {
			if (autoSwitchInterval) {
				clearInterval(autoSwitchInterval);
				autoSwitchInterval = null;
			}
			// $('.sub-swiper-container .swiper-wrapper').addClass('stop-animating');
		}
		initMainSwiper();
		initSubSwipers();
		// 绑定按钮点击事件
		//停止按钮
		$('#stoptab').on('click', function () {
			if (isAutoSwitchActive) {
				$(this).hide();
				$('#starttab').show();
				stopAutoSwitch(); // 停止自动切换
				isAutoSwitchActive =  false;
				// $('.sub-swiper-container .stop-animating').css('overflow-x', 'auto');
			}
		});

		//启动按钮
		$('#starttab').on('click', function () {
			if (!isAutoSwitchActive) {
				$(this).hide();
				$('#stoptab').show();
				startAutoSwitch(); // 开启自动切换
				isAutoSwitchActive =  true;
			}
		});

		$('.sub-swiper .swiper-slide').on('click', function () {
			const graphId = $(this).data('graph'); // 获取数据属性
			// console.log('Clicked slide:', graphId);
			//必须在停止自动切换下才能生效
			// if(!isAutoSwitchActive){
				currentSubIndex = $(this).index();
				const currentSubSwiper = subSwipers[currentIndex];
				$(currentSubSwiper.slides).removeClass('active');
				$(currentSubSwiper.slides[currentSubIndex]).addClass('active');
				currentSubSwiper.slideTo(currentSubIndex);
				showGraph(graphId); // 显示对应的图形
				updateProgressBarPosition(currentSubSwiper,currentSubIndex)
				// console.log("currentIndex",currentIndex,currentSubIndex);
			// }

		});

		$('.sub-swiper-container').hover(
				function() { // mouseenter
				// swiper.autoplay.stop();
				$('.swiper-pagination').stop().fadeTo(200, 1);
				// $('.swiper-pagination-progressbar').css({
				// // 'transition': 'transform 3s linear',
				// 'transform': 'scaleX(1)'
				// });
			},
			function() { // mouseleave
				// swiper.autoplay.start();
				$('.swiper-pagination').stop().fadeTo(200, 0);
				// $('.swiper-pagination-progressbar').css({
				// // 'transition': 'transform 0.3s ease',
				// 'transform': 'scaleX(0)'
				// });
			}
		)

	}
	// 显示对应的图形
	const showGraph = (graphId) => {
		$('.graph-content').hide();
		$('#' + graphId).show();
		$('#' + graphId).find('.vmimg').hide().fadeIn(600);

	}
	return {
		init: function () {
			initGetLang(); //重新获取语言 
			// initDataSwiper();
            initAllData();//初始化所有数据
			allScreen(); //设置全屏按钮点击事件
			initWindowResize(); //响应式重构界面
			
		}
	}
})();


jQuery(document).ready(function(){
	Visualscreen.init();
});