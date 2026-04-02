var NasBackupEdit = function () {
	var data = {srcInfo:{},backupInfo:{},highInfo:{}};
	var zTree;
	var zTreeFile = [];
	var share_path = ''//共享路径
	var editFlag = true;
	var searchFlag = false;
	var _pageSize = 40; //代理端文件列表每次显示条数;
	var _path = '';		 //当前路径
	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag = false; //自定义节点选择加载标志
	var SETTINGS;
	var pageIndex = 0; //轮播索引
	var speedList = [];
	var initStrategyFlag = false;
	var globalStrategy = [];
	var currentmax = 0;
	var nextFlag = false;//判断修改是否能进入下一步
	var fileArchiveAuth;//文件归档授权
	var firstInitPageFlag = false; // 首次进入页面标记
	var nodeParamList;//用于保存搜索nas的结果
	const defaultConfig = {
		mode: [1, 2, 3, 9],
	};
	const defaultTimeStrategy = [];
	let firstInitStep2 = true;
	let backupTargetInfo = '';
	let isChangeNas = '';//判断是否切换了nas设备，切换了才初始化备份目的地
	let firstInitWorm = true;//是否是修改任务第一次初始化worm
	let showSnapshotFlag = true;//是否显示快照
	var initAuth = function () {
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemLisenceInfo',p:{}}, function (data) { 
			var data = JSON.parse(data);
			// fileArchiveAuth = data.authfun.fileArchiveMode;
			fileArchiveAuth = false;//nas屏蔽归档
		 });
	}
	
	var initData = function(){
		//setp1
		data.srcInfo.fileInfo = [];
		
		for(var i = 0; i<SETTINGS.fileinfo.length; i++){
			data.srcInfo.fileInfo.push([SETTINGS.fileinfo[i].type,
										SETTINGS.fileinfo[i].path,
										SETTINGS.fileinfo[i].name]);
		}
		//setp2
		//备份方式:策略/时间
		data.strategyInfo = {};
		data.strategyInfo.time = {};
		data.strategyInfo.time.timeInfo = {};
		data.strategyInfo.speedlimit = {};
		data.strategyInfo.speedlimit.speed = {};
		data.strategyInfo.store = {};
		data.strategyInfo.store.storeInfo = {};
		data.strategyInfo.reserve = {};
		data.strategyInfo.reserve.reserveInfo = {};
		data.strategyInfo.time.type = SETTINGS.timestrategy.type;
		//完备/增备/差备
		var timestrategy = SETTINGS.timestrategy.data;
		//按时间备份的时间
		data.strategyInfo.time.datetime = null;
		if(SETTINGS.timestrategy.type == "oncetime"){
			data.strategyInfo.time.datetime = SETTINGS.timestrategy.data;
		}else{
			for(var i=0; i<timestrategy.length; i++){
				var info = {};
				var days = [];
				for (var j=0; j<timestrategy[i].days.length;j++){
					if(timestrategy[i].days.length == 1 && timestrategy[i].days[j] == false){
						days = [];
					}else if(timestrategy[i].days[j] == true){
						timestrategy[i].days[j] = 1;
						days.push(timestrategy[i].days[j]);
					}else if(timestrategy[i].days[j] == false){
						timestrategy[i].days[j] = 0;
						days.push(timestrategy[i].days[j]);
					}
				}
				info.days = days;
				info.mode = timestrategy[i].mode;
				info.type = timestrategy[i].strategy_type;
				info.startTime = timestrategy[i].start_time;
				info.rollFlag = timestrategy[i].roll_flag;
				info.rollInterval = timestrategy[i].roll_interval;
				info.endTime = timestrategy[i].roll_end_time;
				info.frequency = timestrategy[i].frequency;
				info.full_backup_compensation_flag = timestrategy[i].full_backup_compensation_flag;
				if(timestrategy[i].mode == '1'){
					data.strategyInfo.time.timeInfo.fullInfo = info;
				}else if(timestrategy[i].mode == '2'){
					//只有一条则是永久增量
					if (1 == timestrategy.length) {
						data.strategyInfo.time.timeInfo.type = '9';
						data.strategyInfo.time.timeInfo.pIncrInfo = info;
					} else {
						data.strategyInfo.time.timeInfo.incrInfo = info;
					}
					
				}else if(timestrategy[i].mode == '3'){
					data.strategyInfo.time.timeInfo.diffInfo = info;
				}
			}
		}
		
		//setp3
		//保留策略
		data.strategyInfo.reserve.reserveInfo.type = SETTINGS.brs.type;
		data.strategyInfo.reserve.reserveInfo.value = SETTINGS.brs.value;
		data.strategyInfo.reserve.reserveInfo.strategy_mode = SETTINGS.brs.strategy_mode;
		
		data.highInfo.transfer = {};
		// data.highInfo.transfer.encrypt = SETTINGS.bts.encrypt;
		// data.highInfo.transfer.mode = SETTINGS.bts.mode;
		
		data.highInfo.store = {};
		data.strategyInfo.store.storeInfo = SETTINGS.bss;
		
		data.highInfo.node = {};
		data.highInfo.node.nodecheck = false;
		data.highInfo.node.nodeuuid = SETTINGS.node.nodeuuid;
		data.highInfo.node.storageuuid = SETTINGS.node.storageuuid;
		if(data.highInfo.node.storageuuid == ""){
			data.highInfo.node.storagecheck = true;
		}else{
			data.highInfo.node.storagecheck = false;
		}

		data.highInfo.newstr = {};
        data.highInfo.newstr.backupThreadNum = SETTINGS.high.thread_num;//线程数量
		data.highInfo.newstr.scanThreadNum = SETTINGS.high.scan_thread_num;//扫描线程
		data.highInfo.newstr.scanFileNum = SETTINGS.high.scan_file_num;//扫描文件速度
		data.highInfo.permission_operate_flag = SETTINGS.high.permission_operate_flag;//文件权限备份
		data.highInfo.skip_file_alarm_flag = SETTINGS.high.skip_file_alarm_flag;//跳过文件告警
		data.highInfo.skip_file_alarm_min_num = SETTINGS.high.skip_file_alarm_min_num;
		data.highInfo.skip_file_alarm_min_ratio = SETTINGS.high.skip_file_alarm_min_ratio;
		//快照
		data.highInfo.snap_shot_flag = SETTINGS.high.snap_shot_flag;
		//通配符
		// data.highInfo.newstr.wildcardmode = SETTINGS.high.wildcard_mode;
        // data.highInfo.newstr.wildcardstr = SETTINGS.high.wildcard;
		firstInitPageFlag = true;
	
		//限速策略
		speedList = SETTINGS.speedInfo;
		data.strategyInfo.speedlimit = SETTINGS.speedInfo;
		data.strategyInfo.speedlimit.speed = SETTINGS.speedInfo.speedInfo;
		
		//任务信息
		data.taskName = SETTINGS.taskname;
		data.taskuuid = SETTINGS.taskuuid;

		//备份策略 - 安全策略
		data.safe_strategy = SETTINGS.safeStrategy;
		data.safe_strategy.integrity_check_flag = SETTINGS.safeStrategy.integrity_check_flag ? 1 : 0;
		data.safe_strategy.virus_scan_flag = SETTINGS.safeStrategy.virus_scan_flag ? 1 : 0;
		data.safe_strategy.worm_flag = SETTINGS.safeStrategy.worm_flag ? 1 : 0;
		data.safe_strategy.virus_scan_config_list = "";
		data.safe_strategy.integrity_check_config.recovery_error_policy = -1;
		//重试策略
		data.retry_strategy = SETTINGS.retry_strategy;
		//过载保护
		data.highInfo.ignore_resource_limiting_flag = SETTINGS.ignore_resource_limiting_flag
	}

	var initListener = function(){
		$('#toAdd').on('click',function(){
	    	LOCATION('./content/nas/nasmanager.php', 'nas');
		});
		$('#allNasTree').on('click','button.addInput',wildInputAdd);//添加通配符输入框
		$('#allNasTree').on('click','.delInput',delInput);
		$('#searchAgent').on('propertychange', debounceFS).on('input', debounceFS);
		$('#allNasTree').on('change','.wildcardmode', wildcardmodeTypeHandler);
		
		//初始化存储策略配置监听
		initStoreListeners();
		//切换自动选择存储加密密码
		$('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
		$('#allNasTree').on('click','input.applyToAllClient',applyToAllClient);
		//扫描文件
		$("#scanFileNum").on('change', function(){
            initHighStrategyDes();
		});
		//跳过文件告警智能判断
		$('#passfilealarmcheck').on('switchChange.bootstrapSwitch', passAlarmChange);
	}

	var strategyModeClick = function(e){
		setTimeout(initWormConfig, 0);
	}

	var passAlarmChange = function() {
		var flag = $('#passfilealarmcheck').get(0).checked;
		if(flag) {
			$('.passfilenumDiv').show();
			$('.warnningdiv').show();
		} else {
			$('.passfilenumDiv').hide();
			$('.warnningdiv').hide();
		}
	}
	var intTransThreadNum = function () {
		var transSpeed = $("#scanThreadNum").val();
		if(transSpeed == 1) {//为1（极慢）时显示文件扫描速度
			$(".scanFileDiv").show();
		}else {
			$(".scanFileDiv").hide();
		}
		initHighStrategyDes();
	}
	//切换自动选择存储加密密码
	var passwordModeChange = function(){
		if($('#passwordAutocheck').bootstrapSwitch('state')){
			$('#password').val('');
			$('#repassword').val('');
			$('.passwordDiv').hide();
		}else{
			if (firstInitPageFlag) {
				passwordChangeFlag = false;
			} else {
				$('#password').attr('placeholder', LANG.UI_PUBLIC_ENTER_PASSWORD);
				$('#repassword').attr('placeholder', LANG.UI_PUBLIC_ENTER_REPASSWORD);
				passwordChangeFlag = true;
			}
			$('.passwordDiv').show();
		}

		firstInitPageFlag = false;
	}
	var wildcardmodeTypeHandler = function() {
		var wildcardmode = $(this).val();
		if(wildcardmode != 0) {
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').show();
		}else {
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').hide();
			$(this).parents('.form-group.wildmode').siblings('.wildcarddiv').find('div.delcontent').remove();
			
		}
	}
	var wildInputAdd = function () {
		var content = $.trim($(this).prev('.wildcardInputdiv').val());
		if(content!='') {
			let html = '<div class="delcontent" title="' + content + '">' + 
							'<span class="wildcardInput">' + content + '</span>' +
							'<span class="delInput">×</span>' +
						'</div>';
			$('.wilcardsList').append(html);
			$(this).prev('.wildcardInputdiv').val('');
		}else {
			UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
			return false;
		}
	}
	var delInput = function() {
		$(this).parents('.delcontent')[0].remove();
		// $(this).remove();
	}

	var checkTime = function (start,end) {
		var startnum = new Date("1970-01-01" + " " + start).getTime();
		var endnum = new Date("1970-01-01" + " " + end).getTime();
		if(endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
			UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
		}else {
			return true;
		}
	}

	/**
	 * 初始化备份目的地
	 */
	const initBackupTarget = () => {
		var p = {};
		pAjaxRequest({'nas_uuid': data.srcInfo.nasuuid}, "/api/v1/nas/mount_node", "GET", function (result) {
			if(result.success) {
				p.allow_node_uuid_list = result.data;
				p.not_allow_node_suffix = LANG.UI_STORAGE_STATUS_UNMOUNT;
				//SETTINGS.nasuuid == data.srcInfo.nasuuid 第一次进入修改页面没有切换nas设备
				if(firstInitStep2 && SETTINGS.nasuuid == data.srcInfo.nasuuid) {
					p.node_uuid = SETTINGS.node.nodeuuid;
					p.node_pool_uuid = SETTINGS.node.node_pool_uuid;
					p.storage_uuid = SETTINGS.node.storageuuid;
					p.storage_pool_uuid = SETTINGS.node.storage_pool_uuid;
					p.storage_pool_type = SETTINGS.node.storage_pool_type;
				};
			   $('#backupTarget').backupTarget(p);
			   firstInitStep2 = false;
			   isChangeNas = data.srcInfo.nasuuid;
		   }
	   }, true);
	};
	
	var initSpinner = function(){
		initReserveSpinner($('#spinnerDay'));
		initReserveSpinner($('#spinnerNum'));
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('.scanThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('#spinnerpercent').spinner({value: 20, step: 5, min: 1,max: 100});//跳过文件告警比例
		$('.passfilenumDiv').spinner({value:10, step: 5, min: 1, max: 999999999});//跳过文件告警个数
        // 时间策略提示
        $('.all-strategy-tips').hide();
        $('.no-pIncr-tips').show();
	}
	
	//初始化时间计时器
	var initServerTime = function(data){
		var getDate = function(unix){ 
			var polishing = function(d){
				return d < 10 ? '0' + d : d;
			}
			var date = new Date(parseInt(unix) * 1000);
			Y = date.getFullYear() + '-';
			M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
			D = polishing(date.getDate()) + ' ';
			h = polishing(date.getHours()) + ':';
			m = polishing(date.getMinutes()) + ':';
			s = polishing(date.getSeconds());
			return Y+M+D+h+m+s;
		} 
		var servertime = $('#servertime');
		var timeStamp = '';
		var updateInterval = 1000;
		var startClock = function(){
			if(0 == $('#servertime').size()){
        		clearTimeout(timerTask.FileBackup_serverTime);
        		return;
        	}
			servertime.html(getDate(timeStamp++));
			timerTask.FileBackup_serverTime = setTimeout(startClock, updateInterval);
		}
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemTime',p:{}}, function(data){
			timeStamp = data;
			startClock();
		});
	}
	
	var step1Valid = function(showTitleCallback){
		//未选择代理返回
		var nodes = zTree.getCheckedNodes(true);
		data.srcInfo.fileInfo = [];
		data.highInfo.newstr.wildcard_list = [];
		var checkoutFlag = true;
		var filecheck = false;
		// nodes.forEach(item => {
			//所有选中的文件（未过滤）
			if(zTreeFile.getCheckedNodes(true).length !=0) {
				data.srcInfo.fileInfo.push(zTreeFile.getCheckedNodes(true));
			}else if(zTreeFile.getCheckedNodes(true).length ==0) {
				filecheck = true;
			}
			//通配符
			// if(item.eventtype == "agent"){
				var wildcardInput = [];//所有通配符
				var wildcardRealLen = [];//所有通配符的长度（用于修改时更新数据库）
				if($('#high_tabagent_tree').find('span.wildcardInput').length==0 && $('#high_tabagent_tree').find('.wildcardmode').val() != 0) {
					UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
						checkoutFlag = false;
						return false;
				}
				for(var i=0;i<$('#high_tabagent_tree').find('span.wildcardInput').length;i++) {
					var str = $.trim($('#high_tabagent_tree').find('span.wildcardInput').eq(i)[0].innerText);
					if(str != "") {
						//通配符输入不能包含特殊符号,不包含  /  :  "  <  >  | \
						var specialchar = ['/', ':','"','<','>','|','\\'];
						for (var key in specialchar) {
							if (str.indexOf(specialchar[key]) != -1) {
								UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS1);
								checkoutFlag = false;
								return false;
							}
						}
						// 不允许*和？相邻时 输入*在前？在后的情况
						if(str.indexOf("*?") != -1){
							UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS2);
							checkoutFlag = false;
							return false;
						}
					}else if($('#high_tabagent_tree').find('.wildcardmode').val()!=0){
						UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS3);
						checkoutFlag = false;
						return false;
					}
					wildcardInput.push(str.replace(/\*+/g,'*')); //把所有多个*的地方替换成1个*
					str = str.replace(/[\?\*]/g, '');//去掉所有问号和星号，用于计算长度
					wildcardRealLen.push(str.length.toString());
				}
				data.highInfo.newstr.wildcard_list.push([nodes[0].uuid,wildcardInput,$('#high_tabagent_tree').find('.wildcardmode').val(),wildcardRealLen]);
			// }
		// });
		if(!checkoutFlag) return false; 
		//判断修改备份源是否加载完成
		if(!nextFlag) {
			UIToastr.showWarning("nas" + LANG.UI_NAS_BACKUP, LANG.UI_NAS_BAK_LOADING);
			return false;
		}
		if(nodes.length == 0){
			UIToastr.showWarning(LANG.UI_DB_NO_CHOOSE_BACKUP_PROXY, LANG.UI_DB_CHOOSE_BACKUP_PROXY_FIRST);
			return false;
		}
		if(filecheck){//检查是否每个已选中客户端都选中了文件
			UIToastr.showWarning(LANG.UI_BACKUP_FILE_NO_SELECT_TITLE1, LANG.UI_BACKUP_FILE_NO_SELECT_VALUE1);
			return false;
		}
		//初始化时间策略模块
		Strategy.initModule(NasBackupEdit);
		showStep1();
		//处理nas未挂载的节点和存储 + 选中备份选择的节点和存储
		if (isChangeNas == '' || data.srcInfo.nasuuid != isChangeNas) {
			initBackupTarget();
		}
		//判断快照是否显示
		showSnapshot(nodes[0].snapshot_flag);
		getFsCurrentUseLicense().then(showTitleCallback);
		return false;
	}
	
	let showSnapshot = function (snapshot_flag) {
		$('.snapshotLi').show();
		$('#snapshot_handle_pane').show();
		$('.snapshotshow').show();
		switch (parseInt(snapshot_flag)) {
			case 1: //显示快照
				break;
			default://不显示快照
				$('.snapshotLi').hide();
				$('#snapshot_handle_pane').hide();
				$('.snapshotshow').hide();
				$('.advance-config-tabs li').removeClass('active').not('.snapshotLi').eq(0).addClass('active');
				$('.advance-config-wrap__row__content .tab-pane').removeClass('active').not('#snapshot_handle_pane').eq(0).addClass('active');
				showSnapshotFlag = false;
				break;
		}
	}

	//初始化时间计时器
	var initServerTime = function(data){
		var getDate = function(unix){ 
			var polishing = function(d){
				return d < 10 ? '0' + d : d;
			}
			var date = new Date(parseInt(unix) * 1000);
			Y = date.getFullYear() + '-';
			M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
			D = polishing(date.getDate()) + ' ';
			h = polishing(date.getHours()) + ':';
			m = polishing(date.getMinutes()) + ':';
			s = polishing(date.getSeconds());
			return Y+M+D+h+m+s;
		} 
		var servertime = $('#servertime');
		var updateInterval = 1000;
		var startClock = function(){
			if(0 == $('#servertime').size()){
        		clearTimeout(timerTask.VMBackup_serverTime);
        		return;
        	}
			servertime.html(getDate(_timeStamp++));
			timerTask.VMBackup_serverTime = setTimeout(startClock, updateInterval);
		}
		if(_timeStamp) return;
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSystemTime',p:{}}, function(data){
			_timeStamp = data;
			startClock();
		});
	}
	
	var showStep1 = function(){
		//初始化计时器
		initServerTime();
		var nodes = zTree.getCheckedNodes(true);
		var agentInfo = '';
		var showStr = '';
		var showFileArr = [];
		var backupmode4Info = '';
		var eachwildcard2 = '';
		var allCheckedNode = [];
		//得到所有勾选状态是全选中的节点
		for(var i=0; i<data.srcInfo.fileInfo.length; i++){
			for(var j=0; j<data.srcInfo.fileInfo[i].length; j++) {
				//check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
				if(data.srcInfo.fileInfo[i][j].check_Child_State == 2 || data.srcInfo.fileInfo[i][j].check_Child_State == -1) {
					allCheckedNode.push(data.srcInfo.fileInfo[i][j]);
				}
			}
		}
		//过滤掉重复的
		for(var m = 0;m<allCheckedNode.length;m++) {
			if(allCheckedNode[m].type != 1) {//磁盘或文件夹
				for(var n = 0;n<allCheckedNode.length;n++) {
					var str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
					if(str.includes(allCheckedNode[m].filepath) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
						allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
						n--;
					}
				}
			}
		}
		
		data.srcInfo.fileInfo = [];
		allCheckedNode.forEach(item=> {
			var tempfilepath = '';//定义临时路径避免深拷贝改到树节点的filepath
			tempfilepath = item.filepath.substr(share_path.length,item.filepath.length-1);
			data.srcInfo.fileInfo.push([item.type,tempfilepath,item.code_type]);//组装发送给后台的文件信息
			showFileArr.push([tempfilepath]);
		})
		//如果是新增的用于支持全选的父节点，只传一个/
		if(allCheckedNode[0].parentFlag != undefined) {
			data.srcInfo.fileInfo = []
			showFileArr = [];
			data.srcInfo.fileInfo.push([2,"/",1]);//组装发送给后台的文件信息
			showFileArr.push([allCheckedNode[0].name]);
		}
		nodes.forEach(item1=> {
			//设置代理端
			if(item1.eventtype == "group") {
				agentInfo +=  item1.name + ':' + '<br>';
				item1.children.forEach(item2=> {
					if(item2.checked) {
						agentInfo +=  item2.name + ';' + '<br>';
					}
				});
			}else {
			//设置文件列表显示
				showStr +='<strong>'+ item1.name+ ':' + '</strong><br>';
				showFileArr.forEach(eachpath=> {
					showStr += eachpath[0]+ ';' + '<br>';
				});
			//设置通配符显示
			backupmode4Info += '<strong>'+ item1.name+ ':' + '</strong><br>';
			data.highInfo.newstr.wildcard_list.forEach(eachwildcard=> {
				if(eachwildcard[0]==item1.uuid) {
					if(eachwildcard[1].length!=0) {
						backupmode4Info += LANG.UI_FILE_WILDCARD +'：'+ eachwildcard[1]+';' + '<br>';
					}
					if(eachwildcard[2]==0) {
						eachwildcard2 = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
					}else if(eachwildcard[2]==1) {
						eachwildcard2 =LANG.UI_FILE_WILDCARD_BAK_FILTER;
					}else {
						eachwildcard2 =LANG.UI_FILE_WILDCARD_BAK_SELECT;
					}
					backupmode4Info += LANG.UI_FILE_WILDCARD_BAK_MODE+'：'+ eachwildcard2 +';' + '<br>';
				}
				

			});
				
			}
		});
		
		
		$('.backupmodeshow4').html($('.wildcardlabel').html()+':');
		$('.backupmodeshow4list').html(backupmode4Info);
		$('.agentshow').html(agentInfo);
		$('#filelist').html(showStr);

	}
	
	var step2Valid = function(){
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		showStep2();
		return true;
	}
	
	var showStep2 = function(strategyMode){
		//备份目的地(节点)
		backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		data.highInfo.node.nodecheck = !backupTargetInfo.node_uuid;
		data.highInfo.node.storagecheck = !backupTargetInfo.storage_uuid;
		data.highInfo.node.storageuuid = backupTargetInfo.storage_uuid;
		data.highInfo.node.storage_pool_uuid = backupTargetInfo.storage_pool_uuid;
		data.highInfo.node.nodeuuid = backupTargetInfo.node_uuid;
		data.highInfo.node.node_pool_uuid = backupTargetInfo.node_pool_uuid;
		data.highInfo.node.storage_type = backupTargetInfo.storage_type;
		initResourceLimit(backupTargetInfo.node_uuid_list);
		$('.nodeInfoShow').html(backupTargetInfo.node_text);
		$('.storageInfoShow').html(backupTargetInfo.storage_text);
		//1、云存储默认备份数据保留类型为按备份链保留 2、磁带不显示永久保留
		defaultConfig.storage_type = data.highInfo.node.storage_type;
		defaultConfig.module_type_des = 'nas';
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig,function() {
			//复写备份策略复选框
			$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);
			initWormConfig();
		});
		//存储为磁带时，屏蔽保留策略，传输线程禁用，默认是1，屏蔽提示信息
		//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(data.highInfo.node.storageuuid, data.highInfo.node.storage_type, '.threadDiv', '.backupThreadDiv');
		if (data.highInfo.node.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
			data.highInfo.newstr.backupThreadNum = 1;
			//磁带屏蔽安全策略
			$('.safeLi').removeClass('active').hide();
            $('#tab_safety').removeClass('active');
			$('.transferLi').removeClass('active');
            $('#tab_transfer').removeClass('active');
            $('.highLi').removeClass('active');
            $('#tab_other').removeClass('active');
		    $('.commonLi').removeClass('active').addClass('active');
            $('#tab_common').removeClass('active').addClass('active');
			$('.safeDiv').hide();
		} else {
			$('.safeLi').show();
			$('.safeDiv').show();
		}
	}
	var initWormConfig = function() {
		// 安全策略未授权隐藏
        if (!CONF.FUNCTIONS.includes('worm')) {
            $('#wormConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            $('#completeConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
            $('.safeLi').hide();
        }
		let pIncrBackup = $('input[data-mode="9"]').prop('checked');  // 永久增量是否选中
        if ($('#backuptype').val() === 'strategy' && pIncrBackup) {  // 按策略备份并选择了永久增量
            $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.p_incr_backup);
            return;
        }
		if (!backupTargetInfo.storage_uuid) {  // 没有选择存储
            $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_storage);
        } else {
            if (!backupTargetInfo.storage_worm_config.flag) {  // 存储未开启worm
                $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.no_worm);
            }else if (firstInitWorm){
				$('#wormConfig').wormProtectionBackup(true,'col-md-3',false, SETTINGS.safeStrategy.worm_flag, SETTINGS.safeStrategy.worm_protection_time);
				firstInitWorm = false;
			} else {
				$('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
			}
        } 
	};
	var step3Valid = function(){
		data.strategyInfo = $('#backupStrategyDiv').getBackupStrategy();
        if (data.strategyInfo === false){
            result = false;
        }
		var result = getAchiveStr() & getTransferStr() & getSafeStr() & getRetryStr();
		if(result){
			let strategyMode = $('#strategymode').find('input:checked');
			return showStep3(strategyMode);
		}
		return result;
	}
	
	var showStep3 = function(){
		$('.backupTypeInfoDiv').show();
		var backuptypeshow = $('.backuptypeshow'), backuptypeinfoshow = $('.backuptypeinfoshow');
		var backuptypeshowStr = '';
		if("strategy" == data.strategyInfo.time.type){
			backuptypeshowStr = LANG.UI_BACKUP_USE_STRATEGY;
		}else if("oncetime" == data.strategyInfo.time.type){
			backuptypeshowStr = LANG.UI_BACKUP_ONCE;
		} else if ('manual' === data.strategyInfo.time.type) {
			backuptypeshowStr = LANG.UI_BACKUP_MANUAL;
			$('.backupTypeInfoDiv').hide();
		}
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(data.strategyInfo.time.des);
		//归档
		if(fileArchiveAuth) {
			$('.archiveshow').html($('.archivelabel').html() + ": " + $('#archivecheck').get(0).checked);
			$('.archiveselectshow').html($('.archiveselectdivlabel').html() + ": " + $("#archiveSelect").find("option:selected").text());
		}
		//高级策略-线程数量、通配符
		var backupmode3Info = '';
		var backupmode5Info = '';
		var backupmode6Info = '';
		if(CONF.FUNCTIONS.includes('multithread')){
			data.highInfo.newstr.backupThreadNum = $('#backupThreadNum').val();//线程数量
			if(data.highInfo.newstr.backupThreadNum == "" || data.highInfo.newstr.backupThreadNum > 32 || data.highInfo.newstr.backupThreadNum <= 0
			|| !/^\d+$/.test(data.highInfo.newstr.backupThreadNum)){
				//重置为默认值
				$('.backupThreadDiv').spinner("value", 3);
				initHighStrategyDes();
				UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
				return false;
			}
			backupmode3Info = $('.threadnumlabel').html() + ": " + data.highInfo.newstr.backupThreadNum;
		} else {
			data.highInfo.newstr.backupThreadNum = 1;
		}
		//扫描线程数量
		data.highInfo.newstr.scanThreadNum = $('#scanThreadNum').val();
		if(data.highInfo.newstr.scanThreadNum == "" || data.highInfo.newstr.scanThreadNum > 32 || data.highInfo.newstr.scanThreadNum <= 0
		|| !/^\d+$/.test(data.highInfo.newstr.scanThreadNum)){
			//重置为默认值
			$('.scanThreadDiv').spinner("value", 3);
			initHighStrategyDes();
			UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
			return false;
		}
		//扫描文件数量--扫描线程数为1时 文件扫描速度可选  其他情况默认文件扫描速度为0（无线）
		if(data.highInfo.newstr.scanThreadNum == 1) {
			data.highInfo.newstr.scanFileNum = $('#scanFileNum').val();
		}else {
			data.highInfo.newstr.scanFileNum = 0;
		}
		backupmode5Info = $('.scanthreadlabel').html() + ": " + data.highInfo.newstr.scanThreadNum;
		if(data.highInfo.newstr.scanThreadNum == 1) {
			$('.backupmodeshow6').show();
			backupmode6Info = $('.scanfilelabel').html() + ": " + getScanSpeed(parseInt(data.highInfo.newstr.scanFileNum)) + '<br>';
		}else {
			$('.backupmodeshow6').hide();
		}
		$('.backupmodeshow5').html(backupmode5Info);
		$('.backupmodeshow6').html(backupmode6Info);
		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedLimitsStr = '';
		if (data.strategyInfo.speedlimit.speedInfo && data.strategyInfo.speedlimit.speedInfo.length != 0) {
			speedLimitsStr = '';
			for (let i = 0; i < data.strategyInfo.speedlimit.speedInfo.length; i++) {
				speedLimitsStr += data.strategyInfo.speedlimit.speedInfo[i].des + '<br>';
			}
		}
		if (speedLimitsStr == '') {
			speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedLimitsStr);
		if(data.highInfo.node.storage_type != CONF.BD_STORAGE_TYPE.TAPE) {
			$('.reservetypeshow').html(data.strategyInfo.reserve.des);
			$('.backupmodeshow3').html(backupmode3Info + '<br>');
		} else {
			$('.backupmodeshow3').hide();
		}
		$('.storageinfoshow').html(data.strategyInfo.store.des);
		//快照
		if (showSnapshotFlag) {
			data.highInfo.snap_shot_flag = $('#silentsnapshotcheck').get(0).checked;
			$('.snapshotshow').html($('.silentsnapshotlabel').html() + ": " + getSwitchDes(data.highInfo.snap_shot_flag));
		} else {
			data.highInfo.snap_shot_flag = false;
			$('.snapshotshow').hide();
		}
		//文件权限备份
		data.highInfo.permission_operate_flag = $('#file-permission').get(0).checked;
		$('.filepermissionshow').html($('.file-permission-label').html() + ": " + getSwitchDes(data.highInfo.permission_operate_flag));
		//跳过文件告警
		data.highInfo.skip_file_alarm_flag = $('#passfilealarmcheck').get(0).checked;
		$('.passalarmshow').html($('#tab_except_handle_conf .passfilealarmlabel').html() + ": " + getSwitchDes(data.highInfo.skip_file_alarm_flag));
		if(data.highInfo.skip_file_alarm_flag) {//开启
			data.highInfo.skip_file_alarm_min_num = $('#passFileNum').val();
			data.highInfo.skip_file_alarm_min_ratio = $('#warningpercent').val();
			$('.passalarmnumshow').show();
			$('.passalarmpercentshow').show();
			$('.passalarmnumshow').html($('.passfilenumlabel').html() + ": " + data.highInfo.skip_file_alarm_min_num);
			$('.passalarmpercentshow').html($('#tab_except_handle_conf .passfilepercentlabel').html() + ": " + data.highInfo.skip_file_alarm_min_ratio + '%');
			// 输入数据检测
			if(data.highInfo.skip_file_alarm_min_num == "" || data.highInfo.skip_file_alarm_min_num > 9999999999 || data.highInfo.skip_file_alarm_min_num <= 0
			|| !/^\d+$/.test(data.highInfo.skip_file_alarm_min_num)){
				//重置为默认值
				$('.passfilenumDiv').spinner('value', 10);
				UIToastr.showWarning(LANG.UI_NAS_SKIP_FILE_ALARM_NUM, LANG.UI_NAS_SKIP_FILE_ALARM_NUM_TIPS);
				return false;
			}
			if(data.highInfo.skip_file_alarm_min_ratio == "" || data.highInfo.skip_file_alarm_min_ratio > 100 || data.highInfo.skip_file_alarm_min_ratio <= 0
			|| !/^\d+$/.test(data.highInfo.skip_file_alarm_min_ratio)){
				//重置为默认值
				$('#spinnerpercent').spinner('value', 20);
				UIToastr.showWarning(LANG.UI_NAS_SKIP_FILE_ALARM_RATIO, LANG.UI_NAS_SKIP_FILE_ALARM_RATIO_TIPS);
				return false;
			}
		} else {
			data.highInfo.skip_file_alarm_min_num = '';
			data.highInfo.skip_file_alarm_min_ratio = '';
			$('.passalarmnumshow').hide();
			$('.passalarmpercentshow').hide();
		}
		// 忽略节点资源限制
		data.highInfo.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		$('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.highInfo.ignore_resource_limiting_flag));
		//----------------安全策略显示start----------------
		let safeInfo = '';
		if (CONF.FUNCTIONS.includes('worm')) {
			safeInfo = LANG.UI_SAFE_STRATEGY_WORM_PROTECT + ": " + getSwitchDes(data.safe_strategy.worm_flag);
			if (data.safe_strategy.worm_flag) {
				safeInfo += '<br>' + LANG.UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD + ": " + data.safe_strategy.worm_protection_time + LANG.UI_PUBLIC_UNIT_DAY;
			}
			safeInfo += '<br>'
		}
		if (CONF.FUNCTIONS.includes('integrity')) {
			let integrityCheck = $('#completeConfig').getCompleteDetectionBackup();
            safeInfo += integrityCheck.des + '<br>';
		}
		if (!CONF.FUNCTIONS.includes('worm') && !CONF.FUNCTIONS.includes('integrity')) {
			$('.safeDiv').hide();
		}
		$('.safeStrategyShow').html(safeInfo);
		//--------------安全策略显示end---------------
		return true;
	}
	//扫描文件速度描述
	var getScanSpeed = function (level) {
		var des = "";
		switch(level) {
			case 0: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_FIVE;
				break;
			case 1000: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_FOUR;
				break;
			case 800: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_THREE;
				break;
			case 600: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_TWO;
				break;
			case 400: 
				des = LANG.UI_FILE_BAK_SCAN_SPEED_ONE;
				break;
		}
		return des;
	  }
	//得到开关的结果描述   开启/关闭
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	//得到归档策略
	var getAchiveStr = function(){
		data.highInfo.file_archive = SETTINGS.high.file_archive_flag;
		return true;
	}
	
	//得到传输策略
	var getTransferStr = function(){
		// data.highInfo.transfer.encrypt = $('#encryptCheck').get(0).checked;
		return true;
	}

	//得到安全策略
	var getSafeStr  = function(){
        let wormConfig = $('#wormConfig').getWormProtectionSettings();
        let completeConfig = $('#completeConfig').getCompleteDetectionBackup();
		// 安全策略未授权隐藏
        if (!CONF.FUNCTIONS.includes('worm')) {
            wormConfig = '';
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            completeConfig = '';
        }
        data.safe_strategy = safeData(wormConfig, '', completeConfig);
        return true;
    }
	//得到重试策略
	var getRetryStr =  function(){
		//重试策略
	   data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
	   $('.network_retry_times_show').hide();
       $('.network_retry_interval_show').hide();
	   if (!data.retry_strategy) {
		   return false;
	   }
	   return true;
   }
	
	
	var wizardInit = function(){
		if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function(tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
			//把最大步骤存入内存中 用于判断提交的按钮显示
            if(current >= currentmax){
            	currentmax = current;
            }
            // set wizard title
//            $('.step-title', $('#nasbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
            // set done steps
            jQuery('li', $('#nasbackupcontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#nasbackupcontent').find('.button-previous').css('visibility', 'hidden');
                $('#nasbackupcontent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#nasbackupcontent').find('.button-previous').css('visibility', 'visible');
                $('#nasbackupcontent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#nasbackupcontent').find('.button-next').hide();
				$('#nasbackupcontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#nasbackupcontent').find('.button-next').show();
				$('#nasbackupcontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#nasbackupcontent').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
                /*
                success.hide();
                error.hide();
                if (form.valid() == false) {
                    return false;
                }
                handleTitle(tab, navigation, clickedIndex);
                */
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch(index){
	                case 1:
	            		case 1:
	            		if(!step1Valid(() => {
                            $('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
                            handleTitle(tab, navigation, index);
							pageIndex = 1;
                        })){
	            			return false;
	            		}
	            		pageIndex = 1;
	            		break;
	            	case 2:
	            		if(step2Valid() == false){
	            			return false;
	            		}
	            		pageIndex = 2;
	            		break;
	            	case 3:
	            		if(step3Valid() == false){
	            			return false;
	            		}
	            		pageIndex = 3;
	            		break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
            	pageIndex = index;
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#nasbackupcontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#nasbackupcontent').find('.button-previous').css('visibility', 'hidden');
		$('#nasbackupcontent .button-submit').click(() => {
            // 授权判断
            getFsCurrentUseLicense().then(() => {
                if (pageIndex === 0) {
                    var step1 = step1Valid(() => {
                        submit();
                    });
                    if (!step1) return false;
                } else if (pageIndex === 1) {
                    var step2 = step2Valid();
                    if (!step2) return false;
                } else if (pageIndex === 2) {
                    var step3 = step3Valid();
                    if (!step3) return false;
                }
                submit();
            });
        }).css('visibility', 'hidden');
	};
	
	var submit = function(){
		if('' == $.trim($("#jobname").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
		let jobName = $.trim($("#jobname").val());
		// 输入验证
		if(!customInputValidate('string',jobName)){
			return false;
		}
		data.taskName = $.trim($("#jobname").val());
		data.strategygroupuuid = $('#strategySelect option:selected').val();
		//提交的时候重新调用下这个方法
		data.speedLimit = getSpeedStrategyInfo();
		//TODO提交
    	var jsonData = JSON.stringify(data);
    	Metronic.blockUI({target: '#nasbackupcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'editFsBackupJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#nasbackupcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}
	
	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$("#noagent").show();
			$('.vcenter-tree').hide();
			$("#nas_tree").hide();
			if(searchFlag) {
				$('#nosearchtips').show();
				$("#noagent").hide();
			}
			return;
		}else{
			$("#noagent").hide();
			$('.vcenter-tree').show();
			$("#nas_tree").show();
			$(".searchDiv").show();
			$('#nosearchtips').hide();
		}
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true
					},
					key:{
						title: "title"
					}
				},
				callback: {
					beforeClick: nodeClick,
					onCheck: nodeCheck,
					beforeExpand: nodeExpand
				},
				view: {
					fontCss: setFontCss,
				}
			};
		var zNodes = JSON.parse(zNodes)
		zTree = $.fn.zTree.init($("#nas_tree"), setting, zNodes);
		
		//初始化时间策略模块
		Strategy.initModule(NasBackupEdit);
    	//初始化原始数据
    	if(editFlag) {
			initOldSettings();
		}
		for(var i=0;i<zNodes.length;i++){
			if(zNodes[i].checked){
				var nodeExist = zTree.getNodesByParam("id", zNodes[i].id, null)[0];
					if(nodeExist) {
						let pNode = nodeExist.getParentNode();
						zTree.expandNode(pNode, true);//展开父节点
					}
				nodeExpand("agent_tree_" + (i+1),zNodes[i]);
			}
		}
		if(searchFlag) {
			zTree.expandAll(true);
		}
		searchFlag = false;
	};
	//设置未授权或者离线节点的样式
	function setFontCss(treeId, treeNode) {
		return treeNode.chkDisabled ? {color:"grey"} : {};
	};
	//勾选代理端节点
	var nodeCheck = function(treeId, id, treeNode){
		//未被禁用，单击选中或取消选中
		if(!treeNode.chkDisabled) {
			//单选
			var allNodes = zTree.getCheckedNodes(true);
			for(var i = 0; i < allNodes.length; i++){
				zTree.checkNode(allNodes[i], false, false, false);	
			}
			zTree.checkNode(treeNode, true, false, false);
		}
		nodeExpand(treeId, treeNode);
	}
	//点击代理端节点
	var nodeClick = function(treeId, treeNode){
		if(treeNode.eventtype == "vendor") {
			$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);//单击展开节点
			return;
		}
		//未被禁用，单击选中或取消选中
		if(!treeNode.chkDisabled) {
			//单选
			var allNodes = zTree.getCheckedNodes(true);
			for(var i = 0; i < allNodes.length; i++){
				zTree.checkNode(allNodes[i], false, false, false);	
			}
			zTree.checkNode(treeNode, true, false, false);
		}
		nodeExpand(treeId, treeNode);
		
	}
	var nodeExpand = function(treeId, treeNode){
		if(treeNode.chkDisabled) {//离线客户端只能取消  不能选中
			treeNode.chkDisabled = false;
			zTree.checkNode(treeNode, false, true);
			treeNode.chkDisabled = true;
			zTree.updateNode(treeNode);
		}
		checkAgentOnlineTips(treeNode);
		var flag = treeNode.checked;
		if(flag){
			//切换节点时取消上一次选中
			if(zTreeFile.length != 0) {
				zTreeFile.checkAllNodes(false);
			}
			//选中代理节点
			addAgentList(treeNode);
			initFileTree(treeId, treeNode,treeNode.uuid);
			
		}
		
	}
	//检查代理是否离线或者未授权
	var checkAgentOnlineTips = function (treeNode) {
		if(treeNode.chkDisabled){
			UIToastr.showWarning(LANG.UI_NAS_OFFLINE_OR_NOAUTHORIZED, LANG.UI_NAS_OFFLINE_OR_NOAUTHORIZED_TIPS);
			return;
		}else {
			return true;
		}
	  }

	
	// 将选中的单个客户端加入到右边
	var addAgentList = function (treeNode) {
		var agentContent = "";
		agentContent = 
		'<div id="'+ treeNode.id +'"class="add-list">' + 
			'<div class="accordion file-accordion">' + 
				'<div class="panel panel-default panel-file">' + 
					'<div class="panel-heading">' + 
						'<h4 class="panel-title">' + 
							'<a class="accordion-toggle accordion-toggle-styled popovers" style="display: inline-block; width: 99%;text-decoration: none;" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".fileAccordion" href="#fileClientInfo_'+ treeNode.id +'" aria-expanded="true">' + 
								'<span class="font-green-seagreen">'+ treeNode.name +'</span>' + 
							'</a>' + 
						'</h4>' + 
					'</div>' + 
					'<div id="fileClientInfo_' + treeNode.id+'"class="panel-collapse collapse in" style="height: calc(100% - 34px);">' + 
						'<div class="panel-body">' + 
							'<div class="nav-tabs-wrapper">' + 
								'<ul class="nav nav-tabs nav-line-tabs">' + 
									'<li id="commontab'+ treeNode.uuid +'"class="active nav-item">' + 
										'<a class="nav-link" href="#common_tabagent_tree_'+ treeNode.uuid +'"data-toggle="tab"aria-expanded="false">'+ LANG.UI_FILE_SELECT_FILE_AND_DIR +'</a>' + 
									'</li>' + 
									'<li class="nav-item">' + 
										'<a class="nav-link" href="#high_tabagent_tree"data-toggle="tab"aria-expanded="false">'+ LANG.UI_PUBLIC_MORE +'</a>' + 
									'</li>' + 
								'</ul>' + 
								'<div class="tab-content hover-scroll-y">' + 
									'<div class="tab-pane active" id="common_tabagent_tree_'+ treeNode.uuid +'">' + 
										'<div class="row" style="margin: 0">' + 
											'<div class="form-group" style="margin: 0">' + 
												'<ul id="fileClientTree' +'"class="ztree"></ul>' + 
											'</div>' + 
										'</div>' + 
									'</div>' + 
									'<div class="tab-pane" id="high_tabagent_tree">' + 
										'<div class="row" style="margin: 0">' + 
											'<div class="form-group" style="margin: 0">' + 
												'<div class="form-group wildmode">' + 
													'<label class="control-label col-md-3 wildcardmodelabel">'+ LANG.UI_FILE_WILDCARD_BAK_WAY +'</label>' + 
													'<div class="col-md-5 pr0">' + 
														'<select class="wildcardmode form-control select2me">' +
															'<option value="0">'+LANG.UI_FILE_WILDCARD_RULES_NO_USE+'</option>' + 
															'<option value="1">'+LANG.UI_FILE_WILDCARD_BAK_FILTER+'</option>' + 
															'<option value="2">'+ LANG.UI_FILE_WILDCARD_BAK_SELECT +'</option>' + 
														'</select>' + 
													'</div>' + 
													'<div class="col-md-1 mt5">' + 
														'<a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="'+ LANG.UI_FILE_WILDCARD_BAK_MODE_TIPS +'" style="line-height: 25px;" data-original-title=""title="">' +
															'<i class="viconfont vicon-tishi"></i>' + 
														'</a>' + 
													'</div>' + 
												'</div>' + 
												'<div class="form-group wildcarddiv display-none"style="margin-bottom: 0;">' + 
													'<label class="control-label col-md-3 wildcardlabel">'+LANG.UI_FILE_WILDCARD+'</label>' + 
													'<div class="col-md-5 allWildcardInput pr0">' + 
														'<input type="text"class="wildcardInputdiv form-control" class="form-control input-sm wildcardInput">' + 
														'<button type="button"class="btn btn-primary addInput" style="height: 33px">'+ LANG.UI_BACKUP_FILE_ADD +'</button>' +
													'</div>' + 
													'<div class="col-md-1 mt5">' + 
														'<a class="popovers"data-container="body"data-trigger="hover"data-placement="right"data-content="'+ LANG.UI_FILE_WILDCARD_RULES_ADD_TIPS +'"style="line-height: 25px;">' +
															'<i class="viconfont vicon-tishi"></i>' + 
														'</a>' + 
													'</div>' + 
												'</div>' + 
												'<div class="form-group">' + 
													'<label class="control-label col-md-3"></label>' + 
													'<div class="col-md-5 pl0">' + 
														'<div class="wilcardsList">' + 
														'</div>' + 
													'</div>' + 
												'</div>' + 
											'</div>' + 
										'</div>' + 
									'</div>' + 
								'</div>' + 
							'</div>' + 
						'</div>' + 
					'</div>' + 
				'</div>' + 
			'</div>' + 
		'</div>';
		$('#allNasTree').html(agentContent);
		$('.popovers').popover({
			html:true
		});
		// 通配符旧信息
		SETTINGS.high.wildcardinfo.forEach((item,j)=> {
			if(item !=null) {
				var des = ''
				$("#high_tabagent_tree").find('.wildcardmode').val(item.wildcard_mode);//方式
				if(item.wildcard_mode != 0) {//选择了通配符过滤方式
					$("#high_tabagent_tree").find('.wildcarddiv').show();
					item.wildcard.forEach(eachwildcard=> {//添加旧通配符规则
						des += '<div class="delcontent" title="' + eachwildcard + '">' + 
							'<span class="wildcardInput">' + eachwildcard + '</span>' +
							'<span class="delInput">×</span>' +
						'</div>';
					});
					$("#high_tabagent_tree").find('.wilcardsList').html(des);
				}
			}
		});
			
	  }
	  var debounceFS = function () {
		var value = $('#searchAgent').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		var nodes = zTree.getNodes();
		if(!nodes || nodes.length == 0) return;
		var checkNode =zTree.getCheckedNodes();
		var checkFsNode = [];
		$.each(checkNode, function (i, v) {
			checkFsNode.push(v);
		});
		var allNode = zTree.transformToArray(zTree.getNodes());
		nodeParamList = zTree.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			zTree.hideNodes(allNode);
			$('.vcenter-tree').show();
			$('#nosearchtips').hide();
		}else{
			$('.vcenter-tree').hide();
			$('#nosearchtips').show();
		}
		//连接搜索的和所勾选的
		nodeParamList =nodeParamList.concat(checkFsNode);
		var nodeParamList1 = zTree.transformToArray(nodeParamList);
        for(var n in nodeParamList1){
            findParent(zTree,nodeParamList1[n]);
        }
        zTree.showNodes(nodeParamList);
		searchFlag = true;
    }
	//找到父节点
	var findParent = function(treeObj,node){
		zTree.expandNode(node,true,false,false);
		if(!node.children){
			nodeParamList.push(node);
			zTree.expandNode(node,false,false,false);
		}
		var pNode = node.getParentNode();
		if(pNode != null){
			nodeParamList.push(pNode);
			findParent(zTree, pNode);
		}
   }
	var initFileTree = function(treeId,treeNode,nasuuid){
		var _path = treeNode.path;
		share_path = treeNode.path.substr(0,treeNode.path.length-1);//去掉最后一个斜杠
		var params = {nasuuid:nasuuid,agentuuid:treeNode.agentuuid, start:0, limit:_pageSize, filename:'', dir:_path,pid:_path, editFlag: true, taskuuid: $('#task_uuid').val(),sharepath:treeNode.sharepath};
		var params = JSON.stringify(params);
		var div = "#fileClientTree";
		Metronic.blockUI({target: div,animate: true});
		nextFlag = false;
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.NAS,f:'getNasSonTree',p:params},
	        success: function(data){
	        	Metronic.unblockUI(div);
	        	result = JSON.parse(data);
	        	if(result.re){
	        		setFileTree(result,nasuuid);
					nextFlag = true;
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
		data.srcInfo.nasuuid = nasuuid;
		$('#step1tips').hide();
		$('#agentfilediv').show();
		$('#filelistdiv').show();
	}
	var setFileTree = function(data,nasuuid){
		var setting = {
			check: {
				enable: true,
				// nocheckInherit: false,
				// chkboxType: { "Y": "s", "N": "s" }
			},
			data: {
				simpleData: {
					enable: true,
				},
				key:{
					title: "title"
				}
			},
			callback: {
				beforeClick: fileNodeClick,
				// onCheck: fileNodeCheck,
				beforeExpand: fileNodeExpand
			},
			view: {
				dblClickExpand: false
			}
		};
		if(data['root_dir_check']) {//根目录选中，用于解决修改根目录全选，加载更多未选中的情况
			data['fileNodes'].forEach(item=>{
				item.checked = true;
			});
		}
		if(nasuuid == data['fileNodes'][0].uuid) {
			zTreeFile = $.fn.zTree.init($('#fileClientTree'), setting, data['fileNodes']);
			nodeSelectFlag = false;//重新选择了nas设备，需重新初始化节点
		}
		
		$('#fileClientTree li').css("background-color","white");
		//检测是不是有子节点  没有不展开
		var allnodes = zTreeFile.transformToArray(zTreeFile.getNodes());
		allnodes.forEach(item => {
			if(item.children == undefined && item.open == true) {
				item.open = false;
				zTreeFile.updateNode(item);
			}
		});
		var rootnode = zTreeFile.getNodes()[0];
		rootnode.halfCheck = false;//恢复自动计算半勾选状态
		zTreeFile.updateNode(rootnode);
	}
	var fileNodeClick = function(treeId, pNode, clickFlag){
		//是否被禁用
		if(pNode.chkDisabled) {
			return;
		}
		//1文件 2 文件夹 3 磁盘
		if(pNode.type == 1){
			return;
		}else {
			//如果不是文件 加载文件/目录树
			if(pNode.more) {//加载更多
				getMoreTree(treeId, pNode);
				return;
			}
			_path = pNode.filepath;
			fileNodeExpand(treeId, pNode);
			$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
		}
	}
	var fileNodeExpand = function (treeId,pNode) { 
		if(pNode.children) return true;
		if(!pNode.parentFlag) {
			getFileSonTree(treeId,pNode,false);
		}
	}
	
	// 获取文件子树
	var getFileSonTree = function (treeId,pNode,expendFlag) {
		var params = {nasuuid:pNode.uuid,agentuuid:pNode.agentuuid, start:0, limit:_pageSize, filename:'', dir:pNode.filepath,pid:pNode.filepath,code_type:pNode.code_type};
		var params = JSON.stringify(params);
		var div = "#allNasTree";
		Metronic.blockUI({target: div,animate: true});
		$.ajax({ 
			type: "post", 
	        url: CONF.AJAXPATH, 
	        async:true, 
	        data:{m:CONF.M.NAS,f:'getNasSonTree',p:params},
	        success: function(data){
	        	Metronic.unblockUI(div);
	        	result = JSON.parse(data);
	        	if(result.re){
	        		//success
	        		$.fn.zTree.getZTreeObj(treeId).removeChildNodes(pNode);
	        		$.fn.zTree.getZTreeObj(treeId).addNodes(pNode, result['fileNodes'], true);
	        		$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
	        		if(expendFlag == true){
	        			$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true, true, true);
	        		}
					if(pNode.checked && pNode.children) {
						pNode.children.forEach(item=>{
							$.fn.zTree.getZTreeObj(treeId).checkNode(item,true);
						});
					}
	        	}else{
	        		OPREL(data);
	        	}
	        } 
		});
	}
	
		
		
	var getMoreTree = function(treeId, pNode){
		// 显示更多
		//判断父节点下的子节点是否全选
		if(pNode.getParentNode() !=null) {
			var checkeFlag = pNode.getParentNode().check_Child_State==2 ? true : false;
		}
		pNode.next_index == undefined ? pNode.next_index = 0 : pNode.next_index;
		pNode.search_file_name == undefined ? pNode.search_file_name = "" : pNode.search_file_name;
		pNode.dir_path == undefined ? _path = pNode.filepath : _path = pNode.dir_path;
		var params = {nasuuid:pNode.uuid,agentuuid:pNode.agentuuid, start:pNode.next_index, limit:_pageSize, filename:pNode.search_file_name, dir:_path,pid:pNode.pId};
		var params = JSON.stringify(params);
		Metronic.blockUI({target: '#allNasTree',animate: true});
		$.ajax({ 
			type: "post", 
		    url: CONF.AJAXPATH, 
		    async:true, 
		    data:{m:CONF.M.NAS,f:'getNasSonTree',p:params},
		    success: function(data){ 
		    	Metronic.unblockUI('#allNasTree');
		    	result = JSON.parse(data);
		    	if(result.re){
		    		//success
					if(checkeFlag) {
						for(var i=0;i<result['fileNodes'].length;i++) {
							result['fileNodes'][i].checked = true;
						}
					}
		    		$.fn.zTree.getZTreeObj(treeId).removeNode(pNode);
		    		$.fn.zTree.getZTreeObj(treeId).addNodes(pNode.getParentNode(), result['fileNodes'], true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
				}else{
		    		OPREL(data);
		    	}
		    } 
		});
	}
	// 应用到其他客户端
	const applyToAllClient = function() {
		var str = $(this).parents('.panel-collapse')[0].id;
		var index=str.lastIndexOf("_");
    	str=str.substring(index+1,str.length);
		var agentNodes = zTree.getCheckedNodes(true);
		agentNodes.forEach(item => {
			//循环分组中选中的子节点，不包括当前应用的文件树
			if(!item.isParent && item.uuid != str) {
				var otherFileNodes = zTreeFile.getNodes();
				checkAllClient(otherFileNodes,str,item.uuid);
			}
		});
	}
	const checkAllClient = function (otherFileNodes,thisuuid,agentuuid) {//参数1：其他树  参数2：当前树agentuuid
		var fileNodes = zTreeFile.getCheckedNodes(true);
		if(fileNodes.length && fileNodes.length != 0) {
			for(var i = 0;i<fileNodes.length;i++) {//选中的文件
				for(var j = 0;j<otherFileNodes.length;j++) {
					if(otherFileNodes[j].filepath==fileNodes[i].filepath) {//下一颗树第一级有被选中的直接选中
						zTreeFile.checkNode(otherFileNodes[j], true);
						if(otherFileNodes[j].type !=3 && otherFileNodes[j].filepath != '/'){
							var parentNode = zTreeFile.getNodeByParam("filepath", otherFileNodes[j].pId, null);
							zTreeFile.removeChildNodes(parentNode);
							zTreeFile.addNodes(parentNode,otherFileNodes);
						}
						if(fileNodes[i+1] && otherFileNodes[j].filepath==fileNodes[i+1].pId){//判断是否应该展开
							//获取下一级子节点数据
							var n = j;
							var params = {nasuuid:otherFileNodes[j].uuid,agentuuid:agentuuid, start:0, limit:_pageSize, filename:'', dir:otherFileNodes[j].filepath,pid:otherFileNodes[j].filepath,groupuuid:otherFileNodes[j].groupuuid};
							var params = JSON.stringify(params);
							var div = "#allNasTree";
							Metronic.blockUI({target: div,animate: true});
							$.ajax({ 
								type: "post", 
								url: CONF.AJAXPATH, 
								async:true, 
								data:{m:CONF.M.NAS,f:'getNasSonTree',p:params},
								success: function(data){
									Metronic.unblockUI(div);
									result = JSON.parse(data);
									if(result.re){
										//success
										// $.fn.zTree.getZTreeObj("fileClientTree_" + agentuuid).removeChildNodes(otherFileNodes[n]);
										// $.fn.zTree.getZTreeObj("fileClientTree_" + agentuuid).addNodes(otherFileNodes[n], result['fileNodes'], true);
										// $.fn.zTree.getZTreeObj("fileClientTree_" + agentuuid).expandNode(otherFileNodes[n], true);
										checkAllClient(result['fileNodes'],thisuuid,agentuuid);
									}else{
										OPREL(data);
									}
								} 
							});
						}
					}
				}
			}
		}else{
			UIToastr.showWarning(LANG.UI_FILE_SELECT_PLEASE, LANG.UI_FILE_NO_FILE_BE_SELECTED);
		}
	  }
	
	var initTree = function(type) {
		$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getNasBackupTree',p:{}}, setTree);
	};
	// var inintDatatimePicker = function(){
	// 	$(".form_datetime").datetimepicker({
	// 		language:  'zh-CN', 
    //         autoclose: true,
    //         isRTL: Metronic.isRTL(),
    //         format: "yyyy-MM-dd hh:ii:ss",
    //         pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
    //         startDate: new Date()
    //     });
	// }
	var inintDatatimePicker = function(){
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
		 //英文独有的
	  $(".form_datetime").datetimepicker({
	   autoclose: true,
	   isRTL: Metronic.isRTL(),
	   format: "yyyy-mm-dd hh:ii:ss",
	   pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
	   startDate: new Date()
	  });
		}else{
		 $(".form_datetime").datetimepicker({
		  language:  'zh-CN', 
		  autoclose: true,
		  isRTL: Metronic.isRTL(),
		  format: "yyyy-MM-dd hh:ii:ss",
		  pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		  startDate: new Date()
		 });
		}
	}
   
	
	//初始化历史任务信息
	var initOldSettings = function(){
		var data = {};
		data.taskuuid = $('#task_uuid').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getBackupTaskAllInfo',p:data}, function(d){
			SETTINGS = JSON.parse(d);
			initData();
			initStrategySelect(); //没有策略选项功能先注释
			initStep1Settings();
			initStep2Settings();
			initStep3Settings();
			initStrateyDes();
			initDataChangeListeners(); //初始化策略事件
			initSpinner();
		});

	}
	
	//初始化第一步任务信息
	var initStep1Settings = function(){
		//生成旧客户端树
		if(editFlag) {
			var data = {};
			data.nasuuid = SETTINGS.nasuuid//客户端列表
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getBackupTreeOldInfo',p:data}, function(d){
				setTree(d);
			});
		}
		editFlag = false;
	}
	// 重新组装恢复路径旧数据的path和pId
	var structFileData = function() {
		var structFileData = [];
		for(var i=0; i < SETTINGS.fileinfo.length; i++) {
			if(SETTINGS.fileinfo[i].path.indexOf('\/')==0) {//以 / 开头的目录是linux的路径，只有文件夹和文件两种
				switch(SETTINGS.fileinfo[i].type){
					case 1:
						var str = '';
						//先把文件截取了,只剩目录
						var fileIndex = SETTINGS.fileinfo[i].path.lastIndexOf("\/");  
						var fileObj = SETTINGS.fileinfo[i].path.substring(fileIndex + 1, SETTINGS.fileinfo[i].path.length);//斜杠之后的
						var dirIndex = SETTINGS.fileinfo[i].path.lastIndexOf("\/");  
						var dir = SETTINGS.fileinfo[i].path.substring(0, dirIndex+1);//斜杠之前的
						var pathArr = dir.split('/');
						pathArr[0] = '/';
						pathArr.forEach(item=> {
							if(item!=""){
								item=='/'?str +=item:str +=item+ '/';
								//得到pid
								var pIdArr = str.split('/');
								var pId = item=='/'?0:pIdArr.slice(0,pIdArr.length-2).join('/')+'/';
								structFileData.push({"nasuuid":SETTINGS.fileinfo[i].nasuuid,"path":str,"pId":pId});
							}
						});
						structFileData.push({"nasuuid":SETTINGS.fileinfo[i].nasuuid,"path":dir+fileObj,"pId":dir});//先把目录存进去，最后放文件
						break;
					case 2:
						var str = '';
						var pathArr = SETTINGS.fileinfo[i].path.split('/');
						pathArr[0] = '/';
						pathArr.forEach(item=> {
							if(item!=""){
								item=='/'?str +=item:str +=item+ '/';
								//得到pid
								var pIdArr = str.split('/');
								var pId = item=='/'?0:pIdArr.slice(0,pIdArr.length-2).join('/')+'/';
								structFileData.push({"nasuuid":SETTINGS.fileinfo[i].nasuuid,"path":str,"pId":pId});
							}
						});
						break;
				}
			}else {//windows路径  3磁盘 2文件夹 1文件
				switch(SETTINGS.fileinfo[i].type) {
					case 1:
						var str = '';
						//先把文件截取了,只剩目录
						var fileIndex = SETTINGS.fileinfo[i].path.lastIndexOf("\/");  
						var fileObj = SETTINGS.fileinfo[i].path.substring(fileIndex + 1, SETTINGS.fileinfo[i].path.length);//斜杠之后的
						var dirIndex = SETTINGS.fileinfo[i].path.lastIndexOf("\/");  
						var dir = SETTINGS.fileinfo[i].path.substring(0, dirIndex+1);//斜杠之前的
						var pathArr = dir.split('/');
						pathArr.forEach(item=> {
							if(item!=""){
								str +=item+ '/';
								//得到pid
								var pIdArr = str.split('/');
								var pId = pIdArr.slice(0,pIdArr.length-2).join('/')==""?0:pIdArr.slice(0,pIdArr.length-2).join('/')+'/';
								structFileData.push({"nasuuid":SETTINGS.fileinfo[i].nasuuid,"path":str,"pId":pId});
							}
						});
						structFileData.push({"nasuuid":SETTINGS.fileinfo[i].nasuuid,"path":dir+fileObj,"pId":dir});//先把目录存进去，最后放文件
						break;
					case 2:
						var str = '';
						var pathArr = SETTINGS.fileinfo[i].path.split('/');
						pathArr.forEach(item=> {
							if(item!=""){
								str +=item+ '/';
								//得到pid
								var pIdArr = str.split('/');
								var pId = pIdArr.slice(0,pIdArr.length-2).join('/')==""?0:pIdArr.slice(0,pIdArr.length-2).join('/')+'/';
								structFileData.push({"nasuuid":SETTINGS.fileinfo[i].nasuuid,"path":str,"pId":pId});
							}
						});
						break;
					case 3:
						structFileData.push({"nasuuid":SETTINGS.fileinfo[i].nasuuid,"path":SETTINGS.fileinfo[i].path,"pId":0});
						break;
	
				}
			}
		}
		return structFileData;
	}
	//初始化文件树以前数据
	var initFsOldTree = function(thisuuid,thisFileData){
		var oldFileData = structFileData();
		//去重
		let noRepeat = [...new Set(oldFileData.map(item=>JSON.stringify(item)))]
		oldFileData = noRepeat.map(item=>JSON.parse(item)).slice(1);
		for(var i=0; i < oldFileData.length; i++) {//循环settings中格式化过的旧数据
			for(var j=0; j<thisFileData.length; j++) {
				//去掉路径中的共享目录
				var thisFilepath = thisFileData[j].filepath
				thisFilepath = thisFilepath.substr(share_path.length,thisFilepath.length-1);
				if(thisFilepath==oldFileData[i].path && oldFileData[i].nasuuid == thisuuid) {
					zTreeFile.checkNode(thisFileData[j], true, false);
					if(thisFileData[j].pId !=null){
						var parentNode = zTreeFile.getNodeByParam("filepath", thisFileData[j].pId, null);
						zTreeFile.removeChildNodes(parentNode);
						zTreeFile.addNodes(parentNode,thisFileData);
					}
					if(oldFileData[i+1] && thisFilepath==oldFileData[i+1].pId){//判断是否应该展开
						//获取下一级子节点数据
						var n = j;
						var params = {nasuuid:thisuuid,agentuuid:thisFileData[j].agentuuid, start:0, limit:_pageSize, filename:'', dir:thisFileData[j].filepath,pid:thisFileData[j].filepath};
						params = JSON.stringify(params);
						var div = "#allNasTree";
						Metronic.blockUI({target: div,animate: true});
						$.ajax({ 
							type: "post", 
							url: CONF.AJAXPATH, 
							async:true, 
							data:{m:CONF.M.NAS,f:'getNasSonTree',p:params},
							success: function(data){
								Metronic.unblockUI(div);
								result = JSON.parse(data);
								if(result.re){
									//success
									$.fn.zTree.getZTreeObj("fileClientTree").removeChildNodes(thisFileData[n]);
									$.fn.zTree.getZTreeObj("fileClientTree").addNodes(thisFileData[n], result['fileNodes'], true);
									$.fn.zTree.getZTreeObj("fileClientTree").expandNode(thisFileData[n], true);
									initFsOldTree(thisuuid,result['fileNodes']);
								}else{
									OPREL(data);
								}
							} 
						});
					}
				}
			}
		}
	}
	
	var setWeekAndMonth = function(strategyTypeID, tabPane, data, navIndex){
		//设置内容显示
		$('#' + strategyTypeID).find('.day').toggleClass('active');
		$(tabPane).toggleClass('active');
		//设置nav样式,红色的那个
		var li = $(tabPane).parent().parent().find('ul li');
		$(li[0]).toggleClass('active');
		$(li[navIndex]).toggleClass('active');
		//设置复选框
		var checks = $(tabPane).find('.icheck');
		for(var i=0; i<checks.length; i++){
			if(data.days[i]){
				//选中
				$(checks[i]).iCheck('check');
			}
		}
	}
	/**
	 * 设置每一个时间策略详细信息
	 * strategyTypeID: fullbak/incrbak/diffbak
	 * data
	 */
	var setEachStrategyInfo = function(strategyTypeID, data){
		if('1' == data.strategytype){
			//每天
			var tabPane = $('#' + strategyTypeID).find('.day');
			setSameStrategyInfo(tabPane, data);
		}else if('2' == data.strategytype){
			//每周
			var tabPane = $('#' + strategyTypeID).find('.week');
			setSameStrategyInfo(tabPane, data);
			setWeekAndMonth(strategyTypeID, tabPane, data, 1);
		}else if('3' == data.strategytype){
			//每月
			var tabPane = $('#' + strategyTypeID).find('.month');
			setSameStrategyInfo(tabPane, data);
			setWeekAndMonth(strategyTypeID, tabPane, data, 2);
		}
		//调用确定按钮单击事件
		$(tabPane).find('.each-button').trigger("click");
	}
	
	//设置相同的策略信息,开始时间,滚动标志,滚动间隔,结束时间
	var setSameStrategyInfo = function(tabPane, data){
		$(tabPane).find('.starttime').val(data.starttime);
		$(tabPane).find('.rollflag').bootstrapSwitch('state', data.rollflag); 
		if(data.rollflag){
			//如果打开滚动执行
			$(tabPane).find('.rolltime').val(data.rollinterval);
			$(tabPane).find('.endtime').val(data.rollendtime);
		}
	}
	
	//初始化第二步任务信息
	var initStep2Settings = function () {
		// 组合策略信息
		defaultConfig.strategy = {
			store: {
				storeInfo: SETTINGS.bss
			},
			reserve: {
				reserveInfo: SETTINGS.brs
			},
			time: {
				...SETTINGS.timestrategy
			},
			speedlimit: SETTINGS.speedInfo
		};
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
	}
	//初始化第三步任务信息
	var initStep3Settings = function(){
		//默认节点选中因为要再节点初始化以后,所以选择事件初始化,移动到第一次点击下一步的位置设置
		
		//传输策略bts
		// $('#transfercheck').bootstrapSwitch('state', SETTINGS.bts.encrypt); 
		//存储策略bss
		$('#compressCheck').bootstrapSwitch('state', SETTINGS.bss.compress); 
		// 压缩等级
		if(SETTINGS.bss.compress){
			$('#compressGrade').val(SETTINGS.bss.compress_method);
		}
		$('#encryptStorageCheck').bootstrapSwitch('state', SETTINGS.bss.encrypt); 
		// 存储加密算法
		if(SETTINGS.bss.encrypt){
            $('.storage-encrypt-div').show();
		}
		$('#storageEncryptMethod').val(SETTINGS.bss.encrypt_method);
		$('#passwordAutocheck').bootstrapSwitch('state', SETTINGS.bss.password_auto_flag);
		if(SETTINGS.bss.encrypt) {
			if (!SETTINGS.bss.password_auto_flag) {
				$('.passwordModeDiv').show();
				$('.passwordDiv').show();
				// 修改任务时密码框和确认密码框先不赋值,提示: 修改时填写
				$('#password').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
				$('#repassword').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
			} else {
				// 自动生成密码默认为checked，首次进入页面不会触发change事件，因此手动触发一次从而改变firstInitPageFlag状态
				passwordModeChange();
			}
		} else {
			$('.passwordDiv').hide();
		}
		//高级策略
		$('#backupThreadNum').val(SETTINGS.high.thread_num);
		$('#silentsnapshotcheck').bootstrapSwitch('state', SETTINGS.high.snap_shot_flag);
		$('#scanThreadNum').val(SETTINGS.high.scan_thread_num);
		if(SETTINGS.high.scan_thread_num == 1) {
			$(".scanFileDiv").show();
			$('#scanFileNum').val(SETTINGS.high.scan_file_num);
		}
		//归档
		if(SETTINGS.high.file_archive_flag != 2) {//开启
			if(fileArchiveAuth) {
				$('#archivecheck').bootstrapSwitch('state', true);
			}
			// 默认保留策略为永久保留
			$('#reserveType option[value="3"]').show();
			$('#reserveType').val(3);
			$('#reserveType').prop("disabled","disabled");
			$('.reserveDay').hide();
			$('.reserveNum').hide();
			$('.reservetip1').hide();
			$('.reservetip2').show();
			$('#incrBackup').iCheck('disable');
			$('#diffBackup').iCheck('disable');
			$(".archiveselectdiv").show();
			$(".archivedes").show();
			$("#archiveSelect").val(SETTINGS.high.file_archive_flag);
			$("#archiveSelect").attr("disabled","disabled");
		} else {
			if(fileArchiveAuth) {
				$('#archivecheck').bootstrapSwitch('state', false);
			}
			//保留策略brs
			$('#reserveType').val(SETTINGS.brs.strategy_mode);
			$('#reserveType').val(SETTINGS.brs.type);
			if(CONF.RESERVE_TYPE.NUM == data.strategyInfo.reserve.reserveInfo.type){
				$('.reserveNum').show();
				$('.reserveDay').hide();
				$('#spinnerNumInput').val(SETTINGS.brs.value);
			}else if(CONF.RESERVE_TYPE.DAY == data.strategyInfo.reserve.reserveInfo.type){
				$('.reserveNum').hide();
				$('.reserveDay').show();
				$('#spinnerDayInput').val(SETTINGS.brs.value);
			}
			$('#spinnerNumInput').val(SETTINGS.brs.value);
		}
		if(fileArchiveAuth) {
			$('#archivecheck').bootstrapSwitch("disabled",true);//归档不允许修改
		}
		//文件权限备份
		$('#file-permission').bootstrapSwitch('state', SETTINGS.high.permission_operate_flag);
		//跳过文件告警
		$('#passfilealarmcheck').bootstrapSwitch('state', SETTINGS.high.skip_file_alarm_flag);
		if(SETTINGS.high.skip_file_alarm_flag) {//开启
			$('#passFileNum').val(SETTINGS.high.skip_file_alarm_min_num);
			$('#warningpercent').val(SETTINGS.high.skip_file_alarm_min_ratio);
		}
		//任务名
		$('#jobname').val(SETTINGS.taskname);
		//任务UUID
		data.taskuuid = SETTINGS.taskuuid;
		
		//限速策略
		$('.speedlimitDes').empty();
		$('#speedList').empty();
		speedList = [];
		speedList = SETTINGS.speedInfo;

		//初始化安全策略
		let complate_info = [];
        complate_info.push(SETTINGS.safeStrategy.integrity_check_config.check_strategy);
        complate_info.push(SETTINGS.safeStrategy.integrity_check_config.full_error_policy);
        complate_info.push(SETTINGS.safeStrategy.integrity_check_config.inc_error_policy);
        
        // 初始化安全策略
		$('#completeConfig').completeDetectionBackup('col-md-3', SETTINGS.safeStrategy.integrity_check_flag,CONF.MODULE_TYPE.NAS, false, complate_info[0], complate_info[1], complate_info[2]);
		//过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').bootstrapSwitch('state', SETTINGS.high.ignore_resource_limiting_flag);
		//初始化重试策略
		$('#retry_config').retryStrategy({'retry_strategy': SETTINGS.retry_strategy},'edit');
		$('.network-retry-wrap').hide();
	}

	// 应用全局策略并初始化策略信息
	let strategyHandler = function () {
		let index = $('#strategySelect option:selected').val();
		let strategy = globalStrategy[index];
		defaultConfig.strategy = strategy;
		defaultConfig.timeFormateFlag = false;
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
	}

	//初始化策略选择列表
	var initStrategySelect = function(){
		$('.strategy-group-select-wrapper').strategyGroupSelector({module_type: CONF.MODULE_TYPE.NAS, defaultConfig: defaultConfig, backupStrategyDom: '#backupStrategyDiv'})
	}
	var initDataChangeListeners = function(){
        initStoreListeners();
        initHighListeners();
    }
	var initHighListeners = function(){
        $('#backupThreadNum').on('input propertychange', function(){
            initHighStrategyDes();
        });
    }

	//初始化策略描述
	var initStrateyDes = function(){
		initHighStrategyDes();//初始化高级策略
   }
	
	//初始化高级策略配置信息
	var initHighStrategyDes = function(){
		var des = "";
		des += $.trim($(".scanthreadlabel").text()) + ": " + $("#scanThreadNum").val();
		if($("#scanThreadNum").val() == 1) {
			des += ", " + $.trim($(".scanfilelabel").text()) + ": " + $("#scanFileNum").find("option:selected").text();
		}
		$('.higeDes').html(des);
		$('.higeDes').attr('title', des);
	}
	//布尔类型转成int1和2
	var boolToInt = function(thisbool){
		if(thisbool){
			return 1;
		}else{
			return 2;
		}
	}
	//存储策略配置监听
	var initStoreListeners = function(){
		$('#scanThreadNum').on('input propertychange', function(){
        	intTransThreadNum();
        });
		$('.scanThreadDiv .spinner-up').on('click', function(){
        	intTransThreadNum();
        });
        $('.scanThreadDiv .spinner-down').on('click', function(){
        	intTransThreadNum();
        });
    }

	//初始化任务拥挤程度区间
	var initTaskCrowd = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:"getTimeCrowdList", p:{}}, function(d){
			var jsonData = JSON.parse(d);
			if(jsonData.timeList.length != 0){
				$('#backupCrowd').taskCrowd({timeList:jsonData.timeList, showFlag: jsonData.showFlag});
			}
			
		});
	}
	var getFsCurrentUseLicense = () => {
		let uuids = [data.srcInfo.nasuuid];
		let currentUse = uuids.length;
        return new Promise((resolve) => {
            let params = {
                module: 'nas',
                currentUse,
                showMetronic: true,
                judge: true,
                showAlertMsg: true,
				uuids: uuids,
				task_uuid: data.taskuuid,
            }
            getModuleAuthInfo(params).then(result => {
                if (result) {
                    resolve();
                }
            });
        });
    };
	return {
        init: function () {
			initAuth();
        	wizardInit();
        	initTree();	//原始数据在加载树后初始化
        	inintDatatimePicker();
			initListener();
			initTaskCrowd();
        }
    };
}();

jQuery(document).ready(function() {   
	NasBackupEdit.init();
});