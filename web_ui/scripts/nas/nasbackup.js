var NasBackup = function () {
	var data = {srcInfo:{},backupInfo:{},highInfo:{}};
	var zTree;
	var zTreeFile = [];
	var share_path = ''//共享路径
	//用于STEP1:代理端UUID,
	var _pageSize = 40; //代理端文件列表每次显示条数;
	var _path = '';		 //当前路径
	var searchFlag = false;
	var _timeStamp = '';	//时钟时间戳,全局
	var nodeSelectFlag = false; //自定义节点选择加载标志
	var initSpeedFlag = false;
	var speedList = [];
	
	var initStrategyFlag = false;
	var globalStrategy = [];
	var editFlag = false;
	var defaultStrategy = [];
	var initErrorFlag = false;
	var fileArchiveAuth;//nas归档授权
	var nodeParamList;//用于保存搜索nas的结果
	var _oldPassword = '';
	var passwordChangeFlag = false; // 是否改变了密码框内容标记
	const defaultConfig = {
		dom: $('#backupStrategyDiv'),
		mode: [1, 2, 3, 9],
	};
	const defaultTimeStrategy = [];
	let backupTargetInfo = '';
	let isChangeNas = '';//判断是否切换了nas设备，切换了才初始化备份目的地
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
		//setp2
		//按时间备份的时间
		//setp3
		data.highInfo.transfer = {};
		data.highInfo.node = {};
		data.highInfo.newstr = {wildcard_list:[]};
		initStrategy()
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
		//扫描文件
		$("#scanFileNum").on('change', function(){
            initHighStrategyDes();
		});
		//归档开启--保留策略默认永久
		$('#archivecheck').on('switchChange.bootstrapSwitch', archiveChange);
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
	var archiveChange = function () {
		var flag = $('#archivecheck').get(0).checked;
		if(flag) {
			data.strategyInfo.reserve.reserveInfo.type = 3;
			//归档开启二次确认+输入密码，下面显示红色提示
				bootbox.confirm({
					title: LANG.UI_NAS_IF_OPEN_ARCHIVE,
					message: LANG.UI_NAS_IF_OPEN_ARCHIVE_TIPS,
					callback: debounce(function(r) {
						if(!r) {
							$('#archivecheck').bootstrapSwitch('state', false);  //归档关闭
							return true;
						} 
						initErrorFlag = false;
						$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
							var data = JSON.parse(d);
							var userPassword = data.password;
							bootbox.prompt({ 
								title: LANG.UI_NAS_OPEN_ARCHIVE_CONFIRM_TIPS, 
								inputType: 'password',
								callback: debounce(function (result) {
									if(result == null) {
										$('#archivecheck').bootstrapSwitch('state', false);  //归档关闭
										initReserveStrategyDes();
										return true;
									} 
									if(hex_md5(result) == userPassword){
										//密码正确
										$('#archivecheck').bootstrapSwitch('state', true);  //归档开启
										$(".archivedes").show();
										// 默认保留策略为永久保留
										$('#reserveType option[value="3"]').show();
										$('#reserveType').val(3);
										$('#reserveType').prop("disabled","disabled");
										$('.reserveDay').hide();
										$('.reserveNum').hide();
										$('.reservetip1').hide();
										$('.reservetip2').show();
										$('.archiveselectdiv').show();//归档目标
										//禁用增备和差异
										$('#incrBackup').iCheck('uncheck');
										$('#incrBackup').iCheck('disable');
										$('#diffBackup').iCheck('uncheck');
										$('#diffBackup').iCheck('disable');
										$('#stragegyaccordion').find('.strategy-panel[data-mode=2]').hide();
										$('#stragegyaccordion').find('.strategy-panel[data-mode=3]').hide();
										initReserveStrategyDes();
				        				return true;
									}else{
										$(".archivedes").hide();
										$('#reserveType').removeAttr("disabled");
										$('#reserveType').val(1);
										$('.reserveNum').show();
										$('#reserveType option[value="3"]').hide();
										$('.reservetip2').hide();
										$('.reservetip1').show();
										$('.archiveselectdiv').hide();//归档目标
										//启用增备和差异
										$('#incrBackup').iCheck('enable');
										$('#diffBackup').iCheck('enable');
										$('.bootbox-input').css('border-color', "#a94442");
										if(!initErrorFlag){
											var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
											$('.bootbox-input').after(des);
											initErrorFlag = true;
										}
										initReserveStrategyDes();
										return false;
									}
									
								}, 300),
							});
						});
					}, 300),
				});
		}else {
			data.strategyInfo.reserve.reserveInfo.type = 1;
			$(".archivedes").hide();
			$('#reserveType').removeAttr("disabled");
			$('#reserveType').val(1);
			$('.reserveNum').show();
			$('#reserveType option[value="3"]').hide();
			$('.reservetip2').hide();
			$('.reservetip1').show();
			$('.archiveselectdiv').hide();//归档目标
			//启用增备和差异
			$('#incrBackup').iCheck('enable');
			$('#diffBackup').iCheck('enable');
			initReserveStrategyDes();
			return true;
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

	var initSpeedTimeStrategy = function(){
		var strategy = [];
		strategy[0] = {
			mode: 1,
			strategy_type: 2,
			days: [0, 0, 0, 0, 1, 0, 0],
			start_time: '23:00:00',
			end_time: '23:30:00',
		};
		//延迟设置,因为这里icheck会默认修改里面的选中事件
        $('#speedstrategy').speedstrategy({config: strategy});
        initSpeedFlag = true;
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
			   $('#backupTarget').backupTarget(p);
		   }
	   }, true);
	};
	
	var initSpinner = function(){
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('.scanThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
		$('#spinnerpercent').spinner({value: 20, step: 5, min: 1,max: 100});//跳过文件告警比例
		$('.passfilenumDiv').spinner({value:10, step: 5, min: 1, max: 999999999});//跳过文件告警
        // 时间策略提示
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
	
	var resetBackupStrategyInfo = function(name){
		var info = {};
		if('full' == name){
			data.backupInfo.fullInfo = info;
		}else if('incr' == name){
			data.backupInfo.incrInfo = info;
		}else if('diff' == name){
			data.backupInfo.diffInfo = info;
		}else{
			return false;
		}
		return true;
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
	
	var step1Valid = function(showTitleCallback){
		//未选择代理返回
		var nodes = zTree.getCheckedNodes(true);
		data.srcInfo.fileInfo = [];
		data.highInfo.newstr.wildcard_list = [];
		var checkoutFlag = true;
		var filecheck = false;
		nodes.forEach(item => {
			//所有选中的文件（未过滤）
			if(zTreeFile.getCheckedNodes(true).length !=0) {
				data.srcInfo.fileInfo.push(zTreeFile.getCheckedNodes(true));
			}else if(zTreeFile.getCheckedNodes(true).length ==0) {
				filecheck = true;
			}
			//通配符
			// if(item.eventtype == "agent"){
			var wildcardInput = [];//所有通配符
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
				wildcardInput.push(str); 
			}
			data.highInfo.newstr.wildcard_list.push([item.uuid,wildcardInput,$('#high_tabagent_tree').find('.wildcardmode').val()]);
			// }
		});
		
		if(!checkoutFlag) return false; 
		if(nodes.length == 0){
			UIToastr.showWarning(LANG.UI_DB_NO_CHOOSE_BACKUP_PROXY, LANG.UI_DB_CHOOSE_BACKUP_PROXY_FIRST);
			return false;
		}
		if(filecheck){//检查是否每个已选中客户端都选中了文件
			UIToastr.showWarning(LANG.UI_BACKUP_FILE_NO_SELECT_TITLE1, LANG.UI_BACKUP_FILE_NO_SELECT_VALUE1);
			return false;
		}
		//初始化时间策略模块
		Strategy.initModule(NasBackup);
		$('#encryptCheck').bootstrapSwitch('state', false);//加密传输默认关
		// $('#archivecheck').bootstrapSwitch('state', false);  //归档默认关闭
		$('#compressioncheck').bootstrapSwitch('state', true);//压缩存储默认开启
		//初始化策略描述
		initStrateyDes();
		showStep1();
		//处理nas未挂载的节点和存储
		if (isChangeNas == '' || data.srcInfo.nasuuid != isChangeNas) {
			initBackupTarget();
			isChangeNas = data.srcInfo.nasuuid;
		}
		//判断快照是否显示
		showSnapshot(nodes[0].snapshot_flag);
		getNasCurrentUseLicense().then(showTitleCallback);
		return false;
	}
	let showSnapshot = function (snapshot_flag) {
		$('.snapshotLi').show();
		$('#snapshot_handle_pane').show();
		$('.snapshotshow').show();
		$('.vendor-config').hide();
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
	//初始化策略描述
	var initStrateyDes = function(){
		// initStoreStrategyDes();
		// initReserveStrategyDes();
		initHighStrategyDes();//初始化高级策略
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
		//任务名
		if ($.trim($('#jobname').val()) == "") {
			$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getNasBackupTaskName',p:{}}, function(data){
				$('#jobname').val(data);
			});
		}
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
					if(eachwildcard[1]!="") {
						backupmode4Info += LANG.UI_FILE_WILDCARD +'：'+ eachwildcard[1]+';' + '<br>';
					}
					if(eachwildcard[2]==0) {
						eachwildcard2 = LANG.UI_FILE_WILDCARD_RULES_NO_USE;
					}else if(eachwildcard[2]==1) {
						eachwildcard2 = LANG.UI_FILE_WILDCARD_BAK_FILTER;
					}else {
						eachwildcard2 =LANG.UI_FILE_WILDCARD_BAK_SELECT;
					}
					backupmode4Info += LANG.UI_FILE_WILDCARD_BAK_MODE +'：'+ eachwildcard2 +';' + '<br>';
				}
				

			});
				
			}
		});
		
		
		$('.backupmodeshow4').html($('.wildcardlabel').html()+':');
		$('.backupmodeshow4list').html(backupmode4Info);
		// $('.agentshow').html(agentInfo);
		$('#filelist').html(showStr);
		
	}
	
	var step2Valid = function(){
		if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
		showStep2();
		return true;
	}
	
	var showStep2 = function(){
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
		//存储为磁带时，屏蔽保留策略，传输线程禁用，默认是1，屏蔽提示信息
		var storage_type = backupTargetInfo.storage_type;
		var storage_uuid = backupTargetInfo.storage_uuid;
		//1、云存储默认备份数据保留类型为按备份链保留 2、磁带不显示永久保留
		defaultConfig.storage_type = storage_type;
		defaultConfig.module_type_des = 'nas';
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig,function() {
			//复写备份策略复选框
			$('#strategymode').find('.icheck').on('ifClicked', strategyModeClick);
			initWormConfig();
		});
		//判断是否是磁带，参数：存储uuid，存储类型，线程配置项div控制显示隐藏，线程输入框初始化spinner时的dom，初始化备份策略时的dom，模块类型
		getTapeStrategy(storage_uuid, storage_type, '.threadDiv', '.backupThreadDiv', '#tab_common', CONF.MODULE_TYPE.NAS);
		initHighStrategyDes();
		//初始化安全策略
		initSecurityStrategy();
		//初始化重试策略
		initRetryStrategy();
		if(storage_type != CONF.BD_STORAGE_TYPE.TAPE) {
			$('.safeLi').show();
			$('.safeDiv').show();
		} else {
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
            }else {
                $('#wormConfig').wormProtectionBackup($.fn.wormDefine.worm_type.normal);
            }
        } 
	}
	
	var step3Valid = function(){
		data.strategyInfo = $('#backupStrategyDiv').getBackupStrategy();
        if (data.strategyInfo === false){
            result = false;
        }
		var result = getAchiveStr() & getTransferStr() & getSafeStr() & getRetryStr();
		if(result){
			var strategyMode = $('#strategymode').find('input:checked');
			return showStep3(strategyMode);
		}
		return result;
	}
	
	var showStep3 = function(strategyMode){
		//高级策略-快照、线程数量、通配符
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
		//归档  归档目标选择文件3  选择文件和目录1  关闭是2
		if(fileArchiveAuth && $('#archivecheck').get(0).checked) {
			data.highInfo.file_archive = parseInt($("#archiveSelect").val());
		}else {
			$(".archiveselectshow").hide();
			data.highInfo.file_archive = 2
		}
		$('.storageinfoshow').html(data.strategyInfo.store.des);
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
		//时间策略
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
		if(fileArchiveAuth) {
			var archiveshowInfo = $('.archivelabel').html() + ": " + getSwitchDes($('#archivecheck').get(0).checked) ;
			var archiveselectInfo = $('.archiveselectdivlabel').html() + ": " + $("#archiveSelect").find("option:selected").text() ;
			$('.archiveshow').html(archiveshowInfo);//归档
			$('.archiveselectshow').html(archiveselectInfo);//归档目标
		}
		backuptypeshow.html(backuptypeshowStr);
		backuptypeinfoshow.html(data.strategyInfo.time.des);
		if(data.highInfo.node.storage_type != CONF.BD_STORAGE_TYPE.TAPE) {
			$('.reservetypeshow').html(data.strategyInfo.reserve.des);
			$('.backupmodeshow3').html(backupmode3Info + '<br>');
		} else {
			$('.backupmodeshow3').hide();
		}
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
		$('.passalarmshow').html($('.passfilealarmlabel').html() + ": " + getSwitchDes(data.highInfo.skip_file_alarm_flag));
		if(data.highInfo.skip_file_alarm_flag) {//开启
			data.highInfo.skip_file_alarm_min_num = $('#passFileNum').val();
			data.highInfo.skip_file_alarm_min_ratio = $('#warningpercent').val();
			$('.passalarmnumshow').show();
			$('.passalarmpercentshow').show();
			$('.passalarmnumshow').html($('.passfilenumlabel').html() + ": " + data.highInfo.skip_file_alarm_min_num);
			$('.passalarmpercentshow').html($('.passfilepercentlabel').html() + ": " + data.highInfo.skip_file_alarm_min_ratio + '%');
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
	var getAchiveStr = function(){return true;}
	
	//得到传输策略
	var getTransferStr = function(){
		data.srcInfo.transport_priority = $('#transport_mode').val(); //得到传输加密
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
                		if(step1Valid(
							() => {
								$('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
								handleTitle(tab, navigation, index);
								}
						) == false){
                			return false;
                		}
                		break;
                	case 2:
                		if(step2Valid() == false){
                			return false;
                		}
                		break;
                	case 3:
                		if(step3Valid() == false){
                			return false;
                		}
                		break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
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
            getNasCurrentUseLicense().then(submit);
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
		//TODO提交
    	var jsonData = JSON.stringify(data);
    	Metronic.blockUI({target: '#nasbackupcontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'createFsBackupJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#nasbackupcontent');
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});
	}

	var initTree = function() {
		$.post(CONF.AJAXPATH, {m:CONF.M.NAS,f:'getNasBackupTree',p:{}}, setTree);
	};
	//nas设备树
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
						enable: true,
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
		zTree = $.fn.zTree.init($("#nas_tree"), setting, JSON.parse(zNodes));
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
		checkAgentOnlineTips(treeNode);
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
		checkAgentOnlineTips(treeNode);
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
						'<div id="fileClientInfo_' + treeNode.id+'"class="panel-collapse collapse in">' + 
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
		var params = {nasuuid:nasuuid,agentuuid:treeNode.agentuuid, start:0, limit:_pageSize, filename:'', dir:_path,pid:_path};
		var params = JSON.stringify(params);
		var div = "#fileClientTree";
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
					//第一次请求目录时加一层父节点 可支持全选
					result.fileNodes.push({
						"id":  _path,
						"pId": 0,
						"name": treeNode.sharepath,
						"title": treeNode.sharepath,
						"isParent": true,
						"open": true,
						"uuid": treeNode.uuid,
						"nocheck": false,
						"type": "2",
						"icon": "./img/fs/wenjianjia.png",
						"iconOpen": "./img/fs/wenjianjiaopen.png",
						"iconClose": "./img/fs/wenjianjia.png",
						"filepath": _path,
						"more": false,
						"checked": false,
						"parentFlag":true,
					});
	        		setFileTree(result)
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
	var setFileTree = function(data){
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
		zTreeFile = $.fn.zTree.init($('#fileClientTree'), setting, data['fileNodes']);
		$('#fileClientTree li').css("background-color","white");
		nodeSelectFlag = false;//重新选择了nas设备，需重新初始化节点
	}
	var fileNodeClick = function(treeId, pNode,clickshow){
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

	//初始化策略选择列表
	var initStrategySelect = function(){
		$('.strategy-group-select-wrapper').strategyGroupSelector({module_type: CONF.MODULE_TYPE.NAS, defaultConfig: defaultConfig, backupStrategyDom: '#backupStrategyDiv'})
	}
	
	// 应用全局策略并初始化策略信息
	let strategyHandler = function () {
		editFlag = false;
		let index = $('#strategySelect option:selected').val();
		if(!index){
			return;
		}
		let strategy = globalStrategy[index];
		defaultConfig.strategy = strategy;
		defaultConfig.timeFormateFlag = false;
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
	}
    
    
    //初始化保留策略
	var initReserveStrategyDes = function(){
		var des = "";
		var reserveModeType = parseInt($('#reserveMode').val());
        var type = $('#reserveType').val();
        var value = 0;
		if (reserveModeType === CONF.RESERVE_STRATEGY_MODE.POINT){ // 按备份点保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT + '；';
        } else { // 按备份链保留
            des += LANG.UI_RESERVE_RETENTION_TYPE +'：' + LANG.UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN + '；';
        }
		des += LANG.UI_RESERVE_RETENTION_MODE + '：';
        if(CONF.RESERVE_TYPE.NUM == type){
            des += LANG.UI_STRATEGY_RESERVE_NUM;
            value = $('#spinnerNumInput').val();
		}else if(CONF.RESERVE_TYPE.DAY == type){
            des += LANG.UI_STRATEGY_RESERVE_DAY;
            value = $('#spinnerDayInput').val();
        }else if(CONF.RESERVE_TYPE.PERMANENT == type) {
			des += LANG.UI_FILE_PERMANENT;
			value = '';
		}
		if($('#reserveType').val() !== "3") { //不是永久保留
			if(CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw"){
				des += ", " + LANG.UI_STRATEGY_RESERVE_VALUE + value;
			}else{
				des += ": " + LANG.UI_STRATEGY_RESERVE_VALUE + value;
			}
		}

		$('.reserveDes').html(des);
        $('.reserveDes').attr('title', des);
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
	
	//初始化安全策略配置信息
	var initSecurityStrategy  = function(){
		//完整性校验
		$('#completeConfig').completeDetectionBackup('col-md-3', true,CONF.MODULE_TYPE.NAS);
	}

	//初始化重试策略
	var initRetryStrategy = function(){
        //重试策略
        $('#retry_config').retryStrategy();
		$('.network-retry-wrap').hide();
    }

	let initStrategy = () => {
		$('#backupStrategyDiv').initBackupStrategy(defaultConfig);
    };

	var getNasCurrentUseLicense = () => {
        return new Promise((resolve) => {
			let uuids = [data.srcInfo.nasuuid];
			let currentUse = uuids.length;
            let params = {
                module: 'nas',
                currentUse,
                showMetronic: true,
                judge: true,
                showAlertMsg: true,
				uuids: uuids,
            }
            getModuleAuthInfo(params).then(result => {
                if (result) {
                    resolve();
                }
            });
        });
    };
    return {
        //main function to initiate the module
        init: function () {
			initAuth();
        	wizardInit();
        	initTree();
        	inintDatatimePicker();
        	initData();
        	initSpinner();
			initListener();
			initStrategySelect(); //初始化策略选择
        },
        
    };
}();

jQuery(document).ready(function() {   
	NasBackup.init();
});