var VolCDPBackup =  function () {
	var zTreeAgent, zTreeCdpApp,_HostConfigVolDatatable,_StandbyMappingDatatable,_takeoverVolMountPointTab;
	var grid, gridInitFlag = false;
	var _inTask = false,_isHaStandby = false,_isRecoveryTarget= false,_isFbTarget=false,_isTakeoverStandby=false;
    var _optionType;
	var _userIsVerify = false,initErrorFlag=false,_failbackIpVerify = false;
	var _existTtaskName = "";  //受保护任务名
	var _UserPassword;
	var nodeSelectFlag = false;  //自定义节点选择加载标志
	var _createMsgdata = {backupInfo:{},highInfo:{}, speedInfo:{},cache_config:{}};
	var _hostInfoShow = [];
	var _agentNetworkInfo = [];  //主机客户端对应的网卡信息
	var _standbyNetworkInfo = []; //备机客户端对应的网卡信息
	var _updateAgentFlag = false;  //
	var _standbyMappingConfigList = [];  //双机镜像配置view 
	var _scriptSet = []; //自定义脚本
	var _hostVolInfo = [];
	var _hostAppInfo = [];
	var _catBackVolSet = [];
	var _takeoverAppList = [],_mountPointMapSet = [];  //自动接管应用,自动接管卷与挂载点映射关系
	var _agentName, _agentIP,_agentuuid,_agentType,_agentNicInfo,_agentDefaultCachePath;
	var _selectAppType =0;
	var _perSelectVol = [];
	var _appMountList = [];  //选中应用对应的挂载卷uuid列表，用于操作卷反向控制应用是否被监控
	var _StandbyHostData = [];  //满足双机镜像的备机原始数据
	var _standbyHostMountPoint = '';  //自动接管备机已占用的挂载点
	var _selectMapVol = [];  //记录已选择的备机
	var _standbyVolMapSet = [];  //备用卷关系集
	var initSpeedFlag = false;
	var initTagPointStrategyFlag = false;
	var speedList = [];  //限速策略设置
	var tagList = [];  //标签策略设置
	var globalStrategy = [];
	var authFun = [];
	var _oldPassword = '',_ioReplicationMode =1;
	var networkFlag = false;  //用于比对加载传输网络的节点
	var _standbyVolMappingRelation ="";  //双机镜像主备机卷映射关心
	var _standbyTargetHostVol = [];
	var _standbyTargetHostDisk =[];

	var _takeoverIpMapList =[];
	var _takeoverIpMapListView = [];
	var _standbyGatewayConf = [];
	var ipMapInfoList = [];
	var _backupModeHistoryConf = 0;
	var _vmTmpAgentConf = {};
	var _autoTakeoverAuth = false;
	var _autoDoubleHostAuth = false;
	var _standbyHostMountPoint = '';  //备机已占用的挂载点
	
	var _allSysVolInfoArray = [];  //选中客户所有的系统和引导分区卷集合
	var _checkedVolInfo = [];  //选中需要备份的所有系统和引导分区卷集合
	var _volIsUefi = false;  //选中卷是否包含UEFI
	var _isVmMachineManager = false; //是否授权内嵌虚拟化，默认否
	var _backupTaskType = "";  //备份任务类型
	var authInfo = {
		capacityOrQuantity :[],   //授权方式arry：1：数量授权，2：容量授权
		function : [],  //功能授权info
	};
	//测试代码
	var test = function (){
		var data = {};
		data.taskName ="Test architecture process";
		var params = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'createVolCDPBackupJob',p:params}, function(data){
			var msg = data;
			$("#cdp_test").text(msg);
		});
	}
	/**
	 * 绑定自动监听事件
	 */
	var initListener = function(){
		$('#toAdd').on('click',function(){
			LOCATION('./content/client/client.php', 'infrastructure');
		});
		$('#searchAgent').on('propertychange', searchAgent).on('input', searchAgent);
		//切换是否启用镜像传输
		$('#double_host_mirror').on('switchChange.bootstrapSwitch', doubleHostMirrorChange);
		//切换是否启用自动接管
		$('#takeover_config_switch').on('switchChange.bootstrapSwitch', takeoverConfigChnage);

		//重构部分
		$('#real_sync_takeover_config_switch').on('switchChange.bootstrapSwitch', realSyncTakeoverSwitch);
		$('#backup_mode').on('change', backupModeChangeEvent);  //切换备份模式事件
		$('#task_exec_mode').on('click', taskExecModeDetail);
		$('#standbyMapConf').on('click',standbyMapConf);
		$('#proxyClientSubmit').on('click',proxyClientConfSubmit);
		$('#standby_host_mode').on('change',standbyHostModeChange);
		backupModeTaskStatusDesc(); //不同备份模式下任务状态数据流向描述示意图

		$('#rebuildPartSwitch').on('switchChange.bootstrapSwitch', rebuildPartSwitchChnage);  //重建分区点击事件

		//校验接管恢复IP是否可用
		$('#takeover_restore_ip').off().blur(takeoverRestoreIpChange);
		$('#linkSelectIP').unbind('click').click(checkTakeoverResIp);
		$('#addButton').unbind('click').click(plusButton);
		$('#reduceButton').unbind('click').click(minusButton);
		//切换应用故障监测
		$('#app_takeover_switch').on('switchChange.bootstrapSwitch', appTakeoverChnage);
		$('#app_monitor_switch').on('switchChange.bootstrapSwitch',appMonitorSwitch);
		$('#script_takeover_switch').on('switchChange.bootstrapSwitch', scriptTakeoverChnage);

		$('#takeover_network_conf').unbind('click').click(takeoverNetworkConfModal);
		$('.addTakeover').unbind('click').click(plusTakeover);
		$('#reduceTakeover').unbind('click').click(minusTakeover);
		//检验自动接管故障监测间隔配置是否合法
		$('#takeover_app_interval').blur(takeoverAppIntervalChange);
		$('#heartBeatspinnerUp').click(heartBeatspinnerUpChange);
		//检验连续故障次数配置是否合法
		$('#app_failure_number').blur(appFailureNumberChange);
		$('#addFailureNumber').unbind('click').click(addFailure);
		$('#reduceFailureNumber').unbind('click').click(reduceFailure);
		$('#agent_heartbeat_failure_time').blur(agentHeartbeatFailureNumber);

		$('#memory_cache_switch').on('switchChange.bootstrapSwitch', memoryCacheChnage);
		if (CONF.FUNCTIONS.includes('multithread')) {
			// $('.threadNum-number-div').hide();
			$('#transfer_thread_number').off().blur(checkTransferThreadNum);
		}
		
		

		$('#resetstartdate').on('click',function(){
			$('#timing_start').val('');
			$('.backupTimeDes').html('');
		});
        // 压缩存储
        // $('#compressCheck').on('switchChange.bootstrapSwitch', compressChange);
		// 压缩传输
        $('#tran_compress_switch').on('switchChange.bootstrapSwitch', transferCompressChange);
		// 压缩等级改变
        // $('#compressGrade').on('change', initStoreStrategyDes);
		// 传输策略---加密传输
        $('#encrypttransfer').on('switchChange.bootstrapSwitch', transferEncryptChange);

	}
	/**
	 *
	 */
	var realSyncTakeoverSwitch = function (){
		debugger;
		if(!_autoTakeoverAuth){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_AUTO_TAKEOVER_TIP);
			return false;
		}
		//判断自动授权功能是否开启
		if(authInfo.function.cdpAutoTakeover){
			takeoverConfigChnage();
		}else{
			$('#takeover_config_switch').bootstrapSwitch('state', false); //关闭自动接管开关
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_AUTO_TAKEOVER_TIP);
			return false;
		}
		// $.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'verifyVolCdpTakeoverAuthNum',p:''}, function(d){  //校验卷CDP接管授权数是否充足
		// 	var data = JSON.parse(d);
		// 	if(data['result']){
		// 		takeoverConfigChnage();
		// 	}else{
		// 		$('#takeover_config_switch').bootstrapSwitch('state', false); //关闭自动接管开关
		// 		UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_AUTO_TAKEOVER_TIP);
		// 		return false;
		// 	}
		// });

		if(this.checked){
			$('.dualmachineimageswitchdiv').hide();
			$('.standbyconfdiv').show();
			
			//判断是否授权内嵌虚拟化，如果未授权需要手动选中接管方式中的挂载接管
			if(!_isVmMachineManager){
				$("#standby_host_mode option").eq(1).prop('selected', true);
			}
		}else{
			$('.dualmachineimageswitchdiv').hide();
			$('.standbyconfdiv').hide();
			_createMsgdata.standby_target_agent_uuid = "";
			_createMsgdata.takeover_object = {};
			$("#standby_host_mode option").eq(0).prop('selected', true);

		}

	}
	// 按钮点击效果
	var clickEffect = function (element) {
		$(element).addClass('btn-hover');
		setTimeout(function() {
			$(element).removeClass('btn-hover');
		}, 300); // 0.3秒后恢复原样
	}
	/**
	 * 任務執行模式描述信息
	 */
	var taskExecModeDetail = function (){
		clickEffect(this);
		var backupModeValue = $('#backup_mode').val();  //备份模式
		$('.volcdp-backupmode-catback-data').hide();
		$('.realtimesyncoverview').hide();
		$('.realTimeSyncTakeoverView').hide();
		$('.realReplicationNotakeoverView').hide();
		$('.realReplicationTakeoverView').hide();
		$('.realtimereplicationoverview').hide();
		$('.autotakeoververview').hide();

		$('.realtime-sync-host-to-server').hide();
		$('.realtime-sync-host-to-server').hide();
		$('.volcdp-host-server').hide();


		$('.volcdp-host-server-nostatus').show();
		$('.volcdp-backupserver-to-nostatus').show();

		$('.realtimesyncandautotakeover').show();
		$('.realtimesyncmap').hide();

		var backupModeStr = $('#backup_mode option:selected').text();
		var backupModeVal = $('#backup_mode option:selected').val();
		var autoTakeoverFlag = false;
		var autoTakeoverStr = '';
		if(backupModeVal == CONF.FLAG.SET){
			// 实时备份
			autoTakeoverFlag = $('#real_sync_takeover_config_switch').is(':checked');
			autoTakeoverStr = autoTakeoverFlag? '+'+ LANG.UI_VOL_CDP_JOB_DETAILS_AUTO_TAKEOVER:'';
		}else if(backupModeVal == CONF.FLAG.UNSET){
			// 主备复制
			autoTakeoverFlag = $('#takeover_config_switch').is(':checked');
			autoTakeoverStr = autoTakeoverFlag? '+'+LANG.UI_VOL_CDP_JOB_DETAILS_AUTO_TAKEOVER:'';
		}
		$('.volcdp-backupmode-overview-text').text(backupModeStr + autoTakeoverStr + LANG.UI_VOL_CDP_MODE_DESC);

		switch (parseInt(backupModeValue)){
			case 1:
				//实时同步开启自动接管
				if($('#real_sync_takeover_config_switch').is(':checked')){
					$('.realTimeSyncTakeoverView').show();
				}else { //实时同步未开启自动接管
					$('.realtimesyncandautotakeover').hide();
					$('.realtimesyncmap').show();
					$('.realtimesyncoverview').show();
					$('#volcdphosttobackupserver-connectednostate').show();
					$('#volcdphosttobackupserver').hide();
				}
				break;
			case 2:  //实时复制
				if($('#takeover_config_switch').is(':checked')){
					$('.realReplicationTakeoverView').show();
				}else { //实时同步未开启自动接管
					$('.realReplicationNotakeoverView').show();
				}
				break;
			case 3:	 //实时复制+实时同步
				if($('#takeover_config_switch').is(':checked')){
					$('.autotakeoververview').show();
				}else { //实时同步未开启自动接管
					$('.realtimereplicationoverview').show();  //实时复制/双机镜像未开启自动接管描述
				}
				break;
		}
	}
	/**
	 * 切换备机类型
	 */
	var standbyHostModeChange = function (){
		debugger;
		var standbyHostType = $('#standby_host_mode').val();  //备机类型
		
		//选择容灾演练平台时，需要判断是否授权
		if(!_isVmMachineManager && standbyHostType == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VOL_CDP_VM_TEMP_LICENSE_CONF);
			$('#standby_host_mode option:first').prop('selected', true);
			clearStandbyConf(); //清空备机历史配置
			return false;
		}
		//判断选择的系统卷是否包含选中数据源对应的所有卷，如果没有包含所有的系统卷和引导分区，则不能使用容灾演练平台做为备机；
		//未选择系统卷时，容灾演练平台不能作为备机使用
		if((_checkedVolInfo.length < _allSysVolInfoArray.length && standbyHostType == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT) 
			|| (_checkedVolInfo < 0  && standbyHostType == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT)){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VOL_CDP_TAKEOVER_VM_TEMP_SYS_VOL_TIPS);
				$('#standby_host_mode option:first').prop('selected', true);
				clearStandbyConf(); //清空备机历史配置
				return false;
		}
		
		$('.takeoverNetworkConf').show();
		$('.takeoverrestoreipview').show();
		$('.takeoverrestoreipdiv').show();
		switch (parseInt(standbyHostType)){
			case 0: //未选择
				$('#standbyConfDes').hide();
				$('#builtInVmConfDes').hide();
				$('#otherVMConfDes').hide();
				break;
			case 1:  //代理客户端
				$('#standbyConfDes').show();
				$('#builtInVmConfDes').hide();
				$('#otherVMConfDes').hide();
				break;
			case 2:  //第三方虚拟化
				$('#standbyConfDes').hide();
				$('#builtInVmConfDes').hide();
				$('#otherVMConfDes').show();
				break;
			case 3:  //内嵌虚拟化
				$('#standbyConfDes').hide();
				$('#builtInVmConfDes').show();
				$('#otherVMConfDes').hide();

				$('.takeoverrestoreipview').hide();
				$('.takeoverNetworkConf').hide();
				$('.takeoverrestoreipdiv').hide();
				break;
		}
		clearStandbyConf(); //清空备机历史配置
	}
	/**
	 * 不同任务状态下任务数据流向描述示意图
	 */
	var backupModeTaskStatusDesc = function (){
		$('.volcdp-backupmode-catback-data').hide();
		$('.initSyncHerf').click(function (){  //初始同步
			initSyncDataToStandbyFlow();
		});
		$('.realTimeRepliModeInitSyncherf').click(function (){  //实时复制初始同步
			reailTimeSyncDataToStandbyFlow();
		});

		$('.realTtimeSyncHerf').click(function (){
			reailTimeSyncDataToStandbyFlow();
		});
		$('.realTimeRepliRealTtimeSyncherf').click(function (){
			reailTimeSyncDataToStandbyFlow();
		});
		//接管启动中
		$('.takeoverStartingHerf').click(function (){
			takeoverDataFlow();
		});
		//接管中
		$('.takingoverHerf').click(function (){
			takeoverDataFlow();
		});
		//回切中
		$('.taskBackcuttingHerf').click(function (){
			$('.volcdp-host-server-nostatus').hide();
			$('.volcdp-backupserver-to-standby').hide();
			$('.volcdp-host-to-sever-heart').show();
			$('.volcdp-host-to-sever-faild').hide();
			$('.connected-no-state').hide();
			$('.volcdp-backupserver-to-standby').hide();
			$('.volcdp-backupserver-to-nostatus').hide();
			$('.volcdp-host-server').hide();
			$('.volcdp-host-to-sever-heart').show();
			$('.volcdp-backupmode-catback-data').show();

		});

	}
	//初始同步，有备份数据流向示意图
	var initSyncDataToStandbyFlow = function(){
		var backupMode = $('#backup_mode').val();
		$(".volcdp-backupserver-to-standby").show();
		$('.volcdp-host-to-sever-heart').hide();
		$('.volcdp-host-to-sever-faild').hide();
		$('.volcdp-backupmode-catback-data').hide();
		$('.volcdp-host-server').show();
		$('.volcdp-host-to-backupserver').show();

		$('.volcdp-backupserver-to-standby').hide();
		$('.volcdp-host-server-nostatus').hide();
		$('.volcdp-backupserver-to-nostatus').show();

		$('#volcdphosttobackupserver-connectednostate').hide();
		if(backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY || backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY ){  //实时复制，实时复制+实时同步
			$('.volcdp-backupserver-to-standby').show();
			$('.volcdp-backupserver-to-nostatus').hide();
			$('.volcdp-host-to-sever-heart').hide();
			$('.vconnected-no-state').hide();
		}
	}
	//实时同步，有备机数据流向示意图
	var reailTimeSyncDataToStandbyFlow = function (){

		var backupMode = $('#backup_mode').val();
		$(".volcdp-backupserver-to-standby").show();
		$('.volcdp-host-to-sever-heart').hide();
		$('.volcdp-host-to-sever-faild').hide();
		$('.volcdp-backupmode-catback-data').hide();
		$('.volcdp-host-server').show();
		$('.volcdp-host-to-backupserver').show();

		$('.volcdp-backupserver-to-standby').show();
		$('.volcdp-host-server-nostatus').hide();
		$('.volcdp-backupserver-to-nostatus').hide();

		if(backupMode== CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY || backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY){  //实时复制，实时复制+实时同步
			$('.volcdp-backupserver-to-standby').show();
			$('.volcdp-host-to-sever-heart').hide();
			$('.vconnected-no-state').hide();
		}
	}
	//接管的数据流向示意图
	var takeoverDataFlow = function (){
		var backupMode = $('#backup_mode').val();
		$(".volcdp-backupserver-to-standby").show();
		$('.volcdp-host-to-sever-heart').hide();
		$('.volcdp-backupmode-catback-data').hide();
		$('.volcdp-backupserver-to-nostatus').hide();
		var realSyncTakeoverFlag = CONF.FLAG.UNSET;
        var realSyncTakeoverConfig = $('#real_sync_takeover_config_switch').is(':checked');
		if($('#real_sync_takeover_config_switch').is(':checked')) {
			realSyncTakeoverFlag = CONF.FLAG.SET;
		}
		//实时复制，实时复制+实时同步,实时同步开启自动接管
		if(backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY
			|| backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY
			|| realSyncTakeoverFlag == CONF.FLAG.SET){
			$('.volcdp-host-server-nostatus').show();
			$('.volcdp-host-server').hide();
			$('.volcdp-backupserver-to-standby').show();
			$('.volcdp-host-to-sever-heart').hide();
		}
	}
	/**
	 * 清空备机的相关配置
	 */
	var clearStandbyConf = function (){
		ipMapInfoList = []; //置空接管主机IP映射
		_takeoverIpMapListView = []; //置空接管主机IP映射view
		$('#takeover_ip_map_list div').remove();  //清空网络配置view
		_vmTmpAgentConf = {};
		$('.builtInVmConfDes').html('')
		$('#standbyConfDes').html('');
		$('#takeover_ip_map_list').html('');
		_createMsgdata.standby_target_agent_uuid = "";
		_takeoverIpMapList = [];
		_takeoverIpMapListView = [];
		delVmConfDescription();
	}
	/**
	 * 切换备份模式对应事件
	 */
	var backupModeChangeEvent = function (){
		debugger;
		var backupModeValue = $('#backup_mode').val();
		//判断实时复制是否授权
		if((backupModeValue==CONF.CDP_BACKUP_MODE.REALTIME_COPY
            || backupModeValue == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY)
			&& _autoDoubleHostAuth ==false ){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_REALTIME_COPY);
			return false;
		}

		$('.takeoverconfigveiw').hide();
		$('.takeovercollapseview').removeClass("in");
		$('#takeover_config_view').hide();
		$('#takeover_config_switch').bootstrapSwitch('state', false); //自动接管默认关闭
		$('#real_sync_takeover_config_switch').bootstrapSwitch('state', false); //实时同步-自动接管默认关闭
		$('#standby_host_mode').val(0);
		$('.takeoverrestoreipview').show();
		_createMsgdata.backup_object.mirror_backup_flag = 2;  //数据镜像，未备份数据到备份服务器
		if(backupModeValue!=_backupModeHistoryConf){
			delStandbyHostConf();
			clearStandbyConf();
		}
		switch (parseInt(backupModeValue)){
			case 1:  //实时同步
				if(_autoTakeoverAuth){
					$('.realsyncautotakeoverdiv').show();
				}
				$('.cdpautotakeoverswitchdiv').hide();
				$('.dualmachineimageswitchdiv').hide();
				$('.standbyconfdiv').hide();
				$('#standby_host_mode option:eq(2)').prop('disabled', false);
				$('#standby_host_mode option:eq(3)').prop('disabled', false);
				$('.bkimagedatatoserver').hide();
				break;
			case 2:  //实时复制
				$('.realsyncautotakeoverdiv').hide();
				if(_autoTakeoverAuth){
					$('.cdpautotakeoverswitchdiv').show();
				}
				// $('.dualmachineimageswitchdiv').show();
				$('.standbyconfdiv').show();
				$('.bkimagedatatoserver').show();
				$('#standby_host_mode option:eq(2)').prop('disabled', true);
				$('#standby_host_mode option:eq(3)').prop('disabled', true);
				break;
			case 3:  //实时同步 + 实时复制
				$('.realsyncautotakeoverdiv').hide();
				if(_autoTakeoverAuth){
					$('.cdpautotakeoverswitchdiv').show();
				}

				// $('.dualmachineimageswitchdiv').show();
				$('.standbyconfdiv').show();
				$('#standby_host_mode option:eq(2)').prop('disabled', true);
				$('#standby_host_mode option:eq(3)').prop('disabled', true);
                _createMsgdata.backup_object.mirror_backup_flag = 1;  //备份数据到备份服务器
				break;
			default:
				if(_autoTakeoverAuth){
					$('.realsyncautotakeoverdiv').show();
				}
				$('.cdpautotakeoverswitchdiv').hide();
				$('.dualmachineimageswitchdiv').hide();
				$('.standbyconfdiv').hide();
				$('.bkimagedatatoserver').hide();
				break;
		}
		_backupModeHistoryConf = backupModeValue;
	}
	/**
	 * 备机相关映射配置，需要根据备份模式判断加载的内容
	 */
	var standbyMapConf = function (){
		var backupModeValue = $('#backup_mode').val();  //备份模式
		var standbyHostType = $('#standby_host_mode').val();  //备机类型
		if(standbyHostType==0 || standbyHostType==''){
			standbyHostType = CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT;  //bug 24998 需求
		}
		$('#rebuildPartSwitch').bootstrapSwitch('state', false);
		$('.standbyconfdiv').show();	
		if(backupModeValue == CONF.CDP_BACKUP_MODE.REALTIME_COPY  || backupModeValue == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY){
			$('.standbyconfdiv').show();
			$('#standbyConfDes').show();	
			standbyHostType = CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT;
			
		}

		if(standbyHostType== CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT){  //代理客户端
			delStandbyHostConf();
			proxyClientConf();  //代理客户端配置
			loadSHForTakeover();
		}else if(standbyHostType ==CONF.CDP_STANDBY_HOST_MODE.OTHER_VM_MACHINE){  //第三方虚拟化
			
		}else if(standbyHostType == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){  //容灾演练平台
			builtInVmConf();
		}else{
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_VOL_CDP_STANDBY_TYPE_CHANGE_TIPS);
			return;
		}
	}

	/**
	 * 备机为内嵌虚拟机配置
	 */
	var builtInVmConf = function (){
		var node_uuid = $('#selectnode').val();
		$('#vm_machine_modal').modal({'width':'800px', 'height':'380px'});
		var vmTempNetworkInfo  =new Array();
		for (var i=0;i<_agentNetworkInfo.length;i++){
			var ipSet = _agentNetworkInfo[i].ip_set;
			vmTempNetworkInfo.push(ipSet);
		}
		// vm_Mchine.init({});
		vm_Mchine.init({'network_num': _agentNetworkInfo.length,'node_uuid':node_uuid,'task_type':CONF.TASK_TYPE.VOL_CDP_BACKUP,'network_info':vmTempNetworkInfo});
		$('.show_ip_v4_items').hide();
		$('.show_ip_v6_items').hide();
		$('.show_gateway_address').hide();  //默认网关
		$('#networkInfo .left_btn').hide();  //一件部署原机配置
		$('#builtInVmSubmit').unbind('click').click(builtInVmSubmitInfo);
        $('#modal_boot_mode option[value="1"]').prop('disabled', true);
		//选择的数据卷包含UEFI时，内嵌虚拟机的引导方式需要默认选中UEFI，反之默认选中BIOS

		if(_volIsUefi){
			$('#modal_boot_firmware').val(2);  
		}else{
			$('#modal_boot_firmware').val(1); 
		}
    }
	/**
	 * 配置的内嵌虚拟主机提交
	 */
	var builtInVmSubmitInfo = function(){
		delStandbyHostConf();
		var info = vm_Mchine.getMachineInfo();
		var verifyNet = verifyVmhostNetCard(info);
		if (info === false || verifyNet ===false) {
			return;
		}
		$('#vm_machine_modal').modal('hide');
		vmStandbyConfDes(info);
		_createMsgdata.standby_target_agent_uuid = info.temp_agent.uuid; //临时代理则由web直接生成"
		_createMsgdata.backup_object.standby_agent_uuid = info.temp_agent.uuid; //方便后台解析
		$('.takeovercollapseview').addClass("in");
		$('.autotakeoverconfdiv ').removeClass("collapsed");
		$('#takeover_config_view').show();
	}
	/**
	 * 校验虚拟机网卡个数是否与主机配置
	 */
	var verifyVmhostNetCard = function (info){
		let tmpAgentConf = info.temp_agent.config;
		let backupAgentNetCard = _agentNetworkInfo.length;
		let standbyNetCardConf = tmpAgentConf.interfaces.length;
		if(standbyNetCardConf<backupAgentNetCard){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VM_MACHINE_NETCARD_CONF_TIPS+backupAgentNetCard+LANG.UI_PUBLIC_NUM);
			return false;
		}
		return true;
	}
	/**
	 * 内嵌虚拟机配置汇总
	 */
	var vmStandbyConfDes = function (info){
		var tmpAgentConf = info.temp_agent.config;
		_vmTmpAgentConf = tmpAgentConf;
		$('#builtInVmConfDes').html('');

		var memsConf =  tmpAgentConf.mems_str;
		var cpuConf = $('#modal_v_cpu').val()+" "+LANG.UI_VM_MACHINE_CPU_UNIT;
		var hostMachine = $("#nodeSelect_vm").find("option:selected").text();
		var bootFirmware = $("#modal_boot_firmware").find("option:selected").text();
		var bootMode = $("#modal_boot_mode").find("option:selected").text(); //启动方式
		var cpuMode = $("#modal_cpu_mode").find("option:selected").text(); //CPU模式
		$('.takeoverhostDes').text(tmpAgentConf.vm_name);
		var vmNetCardInfo = tmpAgentConf.interfaces;
		var vmNetCardStr = "";
		for(var i=0;i<vmNetCardInfo.length;i++){
			var netCardTypeStr = "";

			var netWorkName = vmNetCardInfo[i].network_name;  // 通信网络名称
			var netCardName = vmNetCardInfo[i].name;  //网卡名称
			var modelTypeName = vmNetCardInfo[i].model_type_name;  // 网络接口
			if(vmNetCardInfo[i].type==1){  //隔离网络
				netCardTypeStr = LANG.UI_VM_MACHINE_NETWORK_TYPE_BRIDGE;
			}else if(vmNetCardInfo[i].type==2){  //桥接网络
				netCardTypeStr = LANG.UI_VM_MACHINE_NETWORK_TYPE_DIVIDE;
			}
			var dataSourceIp = "";
			var vmNetCard = vmNetCardInfo[i].source_ip_info;
			for(var j=0;j<vmNetCard.length;j++){
				var ip_addr = vmNetCard[j].ip_addr;
				if(j !=0){ip_addr = ", "+ ip_addr;}
				dataSourceIp += ip_addr;
			}
			var vmNetCardConf =  LANG.UI_VOL_CDP_HOST_IP_SERVICE + "("+dataSourceIp+"); "+LANG.UI_VM_MACHINE_NETWORK_SET +"("+netWorkName + "); " + LANG.UI_CLIENT_NETWORK_NIC_NAME +"("+netCardName +");" + LANG.UI_VOL_CDP_VM_MACHINE_NETWORK_INTETFACE +"("+modelTypeName +")";
			vmNetCardStr += "<span class ='ml10 ml-0_en textwrap_en' title = '"+vmNetCardConf+"' >" +vmNetCardConf+"</span><br>";
		}
		var des = '<li style="margin-top:15px;" class="list-group-item popovers vmConfTips" id="vmConfDesTips" '
			+'data-container="body" data-trigger="hover" data-placement="top" data-html="true" >'
			+'<div class="col1"><div class="cont ">'
			+'<div class="cont-col1"></div><div class="cont-col2">'
			+'<div class="desc list-one" id = "vmDetailConfigInfo" style="width: 100%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
			+'<sapn>'+LANG.UI_PUBLIC_STORAGE_IN_NODE+'</sapn>:<span class ="ml10" title = "'+hostMachine+'">'+ hostMachine+'</span><br>'
			+'<sapn>'+LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE_NAME+'</sapn>:<span class ="ml10">'+ tmpAgentConf.vm_name +'</span><br>'
			+'<sapn>'+LANG.UI_VM_MACHINE_CPU_USE+'</sapn>:<span class ="ml10">'+ cpuConf+'</span><br>'
			+'<sapn>'+LANG.UI_PUBLIC_MEMORY_SIZE+'</sapn>:<span class ="ml10">'+ memsConf+'</span><br>'
			+'<sapn>'+LANG.UI_VM_MACHINE_BOOT_FIREWARE+'</sapn>:<span class ="ml10">'+ bootFirmware+'</span><br>'
			+'<sapn>'+LANG.UI_VERIFY_CPU_MODE +'</sapn>:<span class ="ml10">'+ cpuMode+'</span><br>'
			+'<div><div style="float: left">'
			+'<sapn>'+LANG.UI_VOL_CDP_SETTING_NETWORK+'</sapn>:</div> <div style="float: left">'+vmNetCardStr+'</div>'+'</div><br>'
			// + isBr +LANG.UI_VM_MACHINE_BOOT_FIREWARE+'</sapn>:<span class ="ml10">'+ bootFirmware+'</span><br>'
			// +'<sapn>'+LANG.UI_VM_MACHINE_BOOT_MODE+'</sapn>:<span class ="ml10">'+ bootMode+ LANG.UI_VCENTER_VM_START+'</span></div><br>'
			+'</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="vmConfDesDel" >'
			+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
		$('#builtInVmConfDes').append(des);
		$('.vmConfTips').popover();	   //初始化tips
		$('.vmConfDesDel').bind("click",delVmConfDescription);
		//填充网卡信息
		var vmInterfaces = tmpAgentConf.interfaces;
		var vmBusinessNicSet = [];
		for(var i = 0;i<vmInterfaces.length;i++){
			var info = {};
			info.name = vmInterfaces[i].name;
			info.uuid = vmInterfaces[i].uuid;
			info.mac_address = vmInterfaces[i].mac_address;
			info.source_ip_info = vmInterfaces[i].source_ip_info;  //数据源主机IP信息
			info.gateway_address = "";
			info.ip_set = [];
			vmBusinessNicSet.push(info);
		}

		vmBusinessNicSet.forEach(function(vmBusinessNicSet) {  
			// 遍历数组vmBusinessNicSet  
			_agentNetworkInfo.forEach(function(_agentNetworkInfo) {  
				// 如果找到相等的对象  
				if (_agentNetworkInfo.ip_set === vmBusinessNicSet.source_ip_info) {
					vmBusinessNicSet.ip_set = _agentNetworkInfo.ip_set;
					vmBusinessNicSet.gateway_address = _agentNetworkInfo.gateway_address;
				}
			});
		});

		// _agentNetworkInfo.forEach(function(itemA, indexA) {
		// 	// 为数组 B 中的当前索引赋值
		// 	if(vmBusinessNicSet[indexA].source_ip_info ==  itemA.ip_set){
		// 		vmBusinessNicSet[indexA].ip_set = itemA.ip_set;
		// 		vmBusinessNicSet[indexA].gateway_address = itemA.gateway_address;
		// 	}else if (indexA < vmBusinessNicSet.length) {
		// 		vmBusinessNicSet[indexA].gateway_address = itemA.gateway_address;
		// 		vmBusinessNicSet[indexA].ip_set = itemA.ip_set;
		// 	}
		// });
		// 如果虚拟机主机网卡数大于主机网卡个数，则使用数组主机网卡最后一个网卡的网关值来填充剩余部分
		var lastValueOfA = _agentNetworkInfo.length > 0 ? _agentNetworkInfo[_agentNetworkInfo.length - 1].gateway_address : null;
		for (var i = _agentNetworkInfo.length; i < vmBusinessNicSet.length; i++) {
			vmBusinessNicSet[i].gateway_address = lastValueOfA;
		}
		_vmTmpAgentConf.vm_interfaces_nic_set = vmBusinessNicSet;
		_standbyNetworkInfo = vmBusinessNicSet;

	}
	/**
	 * 删除内嵌虚拟机配置描述
	 */
	var delVmConfDescription = function(){
		$('.popover.in').remove();
		$('#vmConfDesTips').remove();
		$('#builtInVmConfDes').html('');
		$('.takeoverhostDes').html('');
		_createMsgdata.proxy_agent_object = [];
		_createMsgdata.standby_target_agent_uuid = 0;
		$('.takeoverhostDes').text(LANG.UI_VOL_CDP_BACKUP_STANDBY_NOT_CONF_DESC);
		_vmTmpAgentConf = {};
	}
	/**
	 * 代理客户端配置
	 */
	var proxyClientConf = function (){
		if(authInfo.capacityOrQuantity.total == -1 || authInfo.capacityOrQuantity.used ==-1 || authInfo.capacityOrQuantity.total > authInfo.capacityOrQuantity.used) {  //无限数量授权 或授权充足
			$('#proxyClientModal').modal({'width':'800px', 'height':'380px'});
			if(_HostConfigVolDatatable!=undefined){
				clearTable(_HostConfigVolDatatable,"backup_host_vol_table");
			}
			if($("#backup_mode").val()!=CONF.CDP_BACKUP_MODE.REALTIME_SYNC){
				$('#auto_takeover_target_view').hide();
			}
			var backupMode = $("#backup_mode").val();
			//实时复制,实时同步+实时复制
			$('#takeoverStandbyMountpointTips').show();
			if(backupMode==CONF.CDP_BACKUP_MODE.REALTIME_COPY || backupMode==CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY){
				$('#autoTakeoverHostTips').show();
				$('#takeoverStandbyMountpointTips').hide();
			}
			writeInBackupHostVol(); //将已配置的主机卷信息写入到表格中
			$('.standbyhostDes').text(_agentName);
			$('.doublehostmirrordiv').show();  //显示双机镜像相关view
			$('.doublehostmirrorvoldiv').show(); //配置主备卷映射关系
			$('.stdmapvolume').addClass("in");
			$('.stdmapvolume').css('height','100%');
			$('.mapvolumeview').click(mapVolumeCollapseEvent);
			$('#map_volume_view').show();
			$("#sh_for_takeover").attr("disabled","disabled");
		}else{
			$('#real_sync_takeover_config_switch').bootstrapSwitch('state', false); //授权不足，关闭自动接管
			$('.takeoverconfigveiw').hide();
			$('.takeovercollapseview').removeClass("in");
			$('#takeover_config_view').hide();

			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_DOUBLE_HOST_MIRROR_TIP);
			return false;
		}
		
		// $.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'verifyVolCdpTakeoverAuthNum',p:''}, function(d){  //校验卷CDP接管授权数是否充足
		// 	var data = JSON.parse(d);
		// 	if(data['result']){  //授权充足
		// 		if(_HostConfigVolDatatable!=undefined){
		// 			clearTable(_HostConfigVolDatatable,"backup_host_vol_table");
		// 		}
		// 		if($("#backup_mode").val()!=CONF.CDP_BACKUP_MODE.REALTIME_SYNC){
		// 			$('#auto_takeover_target_view').hide();
		// 		}
		// 		var backupMode = $("#backup_mode").val();
		// 		//实时复制,实时同步+实时复制
		// 		$('#takeoverStandbyMountpointTips').show();
		// 		if(backupMode==CONF.CDP_BACKUP_MODE.REALTIME_COPY || backupMode==CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY){
		// 			$('#autoTakeoverHostTips').show();
		// 			$('#takeoverStandbyMountpointTips').hide();
		// 		}
		// 		writeInBackupHostVol(); //将已配置的主机卷信息写入到表格中
		// 		$('.standbyhostDes').text(_agentName);
		// 		$('.doublehostmirrordiv').show();  //显示双机镜像相关view
		// 		$('.doublehostmirrorvoldiv').show(); //配置主备卷映射关系
		// 		$('.stdmapvolume').addClass("in");
		// 		$('.stdmapvolume').css('height','100%');
		// 		$('.mapvolumeview').click(mapVolumeCollapseEvent);
		// 		$('#map_volume_view').show();
		// 		$("#sh_for_takeover").attr("disabled","disabled");
		// 		// $('.bkimagedatatoserver').show();
		// 	}else{
		// 		// $('#double_host_mirror').bootstrapSwitch('state', false); //关闭双机镜像
		// 		$('#real_sync_takeover_config_switch').bootstrapSwitch('state', false); //授权不足，关闭自动接管
		// 		$('.takeoverconfigveiw').hide();
		// 		$('.takeovercollapseview').removeClass("in");
		// 		$('#takeover_config_view').hide();

		// 		UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_DOUBLE_HOST_MIRROR_TIP);
		// 		return false;
		// 	}
		// });
		$('#proxyClientClose').unbind('click').click(CloseProxyClient);
	}
	/**
	 * 关闭代理客户端配置
	 * @constructor
	 */
	var CloseProxyClient = function (){
		$('.takeoverhostDes').text(LANG.UI_VOL_CDP_BACKUP_STANDBY_NOT_CONF_DESC);
	}
	/**
	 * 提交代理客户端配置信息
	 */
	var proxyClientConfSubmit = function (){
		_standbyMappingConfigList = [];
		var standbySelectValue = $('#standbyHostSelect').val();
		var standbyOsType = $('#standbyHostSelect').find("option:selected").attr('data-ostype');
		var standbyType = $('#standbyHostSelect').find("option:selected").attr('agent-type');
		_createMsgdata.standby_agent_type = standbyType;
		if(standbySelectValue==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_VOL_CDP_SELECT_MAP_TARGET_TIPS);
			return false;
		}
		var tab = $('#backup_host_vol_table');
		var table = tab.dataTable();
		var rows = table.fnGetNodes();
		var proxyAgentConfObject = [];
        var backupMode = $('#backup_mode').val();
		for (var i = 0; i < rows.length; i++) {
			var proxyMachineObj = {};
			var volMappingObj = {};
			var row = table.fnGetData(rows[i]);
			var selectId = "standby_volume_"+i;
			var standbyVolume = $("#"+selectId).find("option:selected").text();
			var standbyVolPath = $("#"+selectId).find("option:selected").attr("data-mount_point");
			var isDefaultOpt = $("#"+selectId).find("option:selected").attr("data-op-default");//默认select op
			var hostVoluuid = row[3];

			if(backupMode==CONF.CDP_BACKUP_MODE.REALTIME_SYNC){
				standbyVolPath = $('#standby_mountpoint_'+i).val();
				standbyVolume = standbyVolPath;
			}

			if(standbyVolPath ===LANG.UI_VOL_CDP_TAKEOVER_AUTO_ALLOCATE){
				standbyVolPath  = "";
				standbyVolume = LANG.UI_VOL_CDP_TAKEOVER_AUTO_ALLOCATE;
			}
			if(standbyVolPath!="" && standbyType == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_NORMAL && backupMode==CONF.CDP_BACKUP_MODE.REALTIME_SYNC){ //需要根据系统类型校验输入挂载点是否合法；
				var pattern = /^[A-Za-z]:\\$/;
				if (!pattern.test(standbyVolPath) && standbyOsType=="Windows") {
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS2);
					return false;
				}
				var isUsed = verdictVolIsUsed(standbyVolPath);  //判断输入的挂载点是否被占用
				var hostMountpointStr = $("#takeoverTargetHostSelect").find("option:selected").attr("data-mountpoint");
				if(!isUsed && standbyOsType==LANG.UI_VOL_CDP_TAKEOVER_OS_TYPE){
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS+"( "+LANG.UI_VOL_CDP_TAKEOVER_MOUNT_HAS_BEEN_USED+hostMountpointStr+" )");
					return false;
				}

				var  regex = /^[A-B]:\\/i;
				if (regex.test(standbyVolPath) && standbyOsType=="Windows") {
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_AB);
					return false;
				}else if(checkPath(standbyVolPath,standbyOsType)){
					standbyVolPath = standbyVolPath.replace(/\\/g, '');  //去除反斜杠
				}else{
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS2);
					return false;
				}
			}
			$('#mount_target_'+hostVoluuid+"").val(standbyVolPath);//填充接管备机目标挂载点

			if(isDefaultOpt != CONF.FLAG.SET){  //非默认选中值
				var shTargetVoluuid = $("#"+selectId).val(); //备机 当前选中的映射卷
				var hostVoluuid = $("#"+selectId).find("option").attr("data-hostvoluuid");//主机对应卷uuid
				if(_createMsgdata.rebuild_partition_flag==1){
					proxyMachineObj.target_disk_uuid = shTargetVoluuid;
					proxyMachineObj.target_vol_uuid = "";
				}else{
					proxyMachineObj.target_disk_uuid = "";
					proxyMachineObj.target_vol_uuid = shTargetVoluuid;
				}
				proxyMachineObj.vol_uuid = hostVoluuid;
				proxyAgentConfObject.push(proxyMachineObj);

				volMappingObj.host_volume = row[0]; //主机卷名
				volMappingObj.host_volume_size = row[1]; //主机卷容量
				volMappingObj.standby_volume = standbyVolume; //备机卷名
				volMappingObj.standby_vol_mount_point = standbyVolPath; //选中映射卷挂载点
				_standbyMappingConfigList.push(volMappingObj);
			}
		}
		if(proxyAgentConfObject.length==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_VOL_CDP_SELECT_HOST_MAP_TARGET_TIPS);
			return false;
		}
		//判断备机映射卷配置是否与备份监控卷匹配
		if(_createMsgdata.backup_object.vol_uuid_set.length > proxyAgentConfObject.length){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_VOL_CDP_SELECT_VOL_MAP_TARGET_TIPS);
			return false;
		}
		if($('#real_sync_takeover_config_switch').is(':checked') || $('#takeover_config_switch').is(':checked')) {
			$('.takeovercollapseview').addClass("in");
			$('.autotakeoverconfdiv ').removeClass("collapsed");
			$('#takeover_config_view').show();
		}
		_createMsgdata.proxy_agent_object = proxyAgentConfObject;
		_createMsgdata.standby_host = $('#standbyHostSelect option:selected').text();

		$('#proxyClientModal').modal('hide');
		standbyConfDes();
	}

	/**
	 * 备机配置描述
	 */
	var standbyConfDes = function (){
		$('#standbyConfDes').html('');
		var tipsDes = LANG.UI_PUBLIC_HOST+ ": " + _createMsgdata.getNmae + " > " +LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE+": "+_createMsgdata.standby_host;
		var volMapDes = "";
		for (var i = 0;i<_standbyMappingConfigList.length;i++){
			volMapDes += _standbyMappingConfigList[i].host_volume+"("+_standbyMappingConfigList[i].host_volume_size+")" + " > " + _standbyMappingConfigList[i].standby_volume + "; ";
		}
		tipsDes = tipsDes + volMapDes;
		var des = '<li style="margin-top:15px;" class="list-group-item popovers standbyHostConfTips" id="standbyHostConfDesTips" '
			+'data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
			+ tipsDes+ '"><div class="col1"><div class="cont ">'
            +'<div class="cont-col1"></div><div class="cont-col2">'
			+'<div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
			+'<sapn>'+LANG.UI_PUBLIC_HOST+'</sapn>:<span>'+ _createMsgdata.getNmae+'</span><br>'
			+'<sapn>'+LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE+'</sapn>:<span>'+ _createMsgdata.standby_host +'</span><br>'
			+'<sapn>'+LANG.UI_VOL_CDP_JOB_DETAILS_MAP_VOL+'</sapn>:<span>'+ volMapDes+'</span><br>'
            +'</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="standbyHostConfDesDel" >'
			+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
		$('#standbyConfDes').append(des);
		$('.standbyHostConfTips').popover();	   //初始化tips
		$('.standbyHostConfDesDel').bind("click",delStandbyHostConf);
	}
	/**
	 * 清除备机历史配置
	 */
	var delStandbyHostConf = function (){
		$('.popover.in').remove();
		$('#standbyHostConfDesTips').remove();
		$('#standbyConfDes').html('');
		_createMsgdata.proxy_agent_object = [];
		_createMsgdata.standby_target_agent_uuid = 0;
		$('.takeoverhostDes').text(LANG.UI_VOL_CDP_BACKUP_STANDBY_NOT_CONF_DESC);
		//内置虚拟机部分
		_vmTmpAgentConf = {};
		$('.builtInVmConfDes').html('')

		ipMapInfoList = []; //置空接管主机IP映射
		_takeoverIpMapListView = []; //置空接管主机IP映射view
		$('#takeover_ip_map_list div').remove();  //清空网络配置view
		$('.targetHostUsedPartition').hide();
		$('.targetHostUsedPartition').html("");
		$('#automatic_fault_recovery_switch').bootstrapSwitch('state', true); //故障自动恢复配置
	}
	// 显示加密算法
	var transferEncryptChange = function(){
		if(this.checked){
			$('.transfer-encrypt-method-form').hide();  //bug 14005需求调整，暂时屏蔽传输算法的配置
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
	}
    
    // 显示压缩等级
    // var compressChange = function () {
    //    if (this.checked) {
    //        $('.compressGradeDiv').show();
    //    } else {
    //        $('.compressGradeDiv').hide();
    //    }
    // };
    // 显示压缩等级
    var transferCompressChange = function () {
        if (this.checked) {
            $('.transferCompressGradeDiv').show();
        } else {
            $('.transferCompressGradeDiv').hide();
        }
    };
	/**
	 * 初始化时间策略相关事件
	 */
	var initDataChangeListeners = function (){
		initTagPointListeners();
		inintDatatimePicker();
		initSpeedListeners();
		initTimeListeners();
		initStoreListeners();  	//储存策略
		initReserveListeners(); //保留策略
		initStrategyDes();
	}

	var initStrategyDes = function(){
		initStoreStrategyDes();
		initReserveStrategyDes();
	}
	//保留策略配置信息
	var initReserveListeners = function (){
		$('#spinnerDayInput').on('input propertychange', function(){
			initReserveStrategyDes();
		});
		$('.spinner-up').on('click', function(){
			initReserveStrategyDes();
			//initHighStrategyDes();
		});
		$('.spinner-down').on('click', function(){
			initReserveStrategyDes();
			//initHighStrategyDes();
		});
		$('#spinnerDayInput').blur(function(){
			initReserveStrategyDes();
		})
	}
	var initReserveStrategyDes = function(){
		var des = "";
		var value = 0;
		des += LANG.UI_STRATEGY_RESERVE_DAY;
		value = $('#spinnerDayInput').val();
		value = parseInt(value);
		des += ", " + LANG.UI_STRATEGY_RESERVE_VALUE + value;
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].reserve.des;
			initStrategyDesStyle($('.reserveDes'), des, oldDes);
		}else{
			$('.reserveDes').removeClass('font-green-seagreen');
		}
		$('.reserveDes').html(des);
		$('.reserveDes').prop('title', des);
	}


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
	var initTimeListeners = function(){
		$('#timing_start').on('change', function(){
			initTimeStrategyDes();
		});
	}

	var initTimeStrategyDes = function(){
		var des = "";
		des += LANG.UI_VOL_CDP_BACKUP_TASK_START_TIME + ": " + $('#timing_start').val();
		$('.backupTimeDes').html(des);
		$('.backupTimeDes').prop('title', des);
	};


	/**
	 * @function 初始化定时标签策略
	 * @description 为标签策略各项操作邦迪初始事件函数
	 * @return volid
	 */
	var initTagPointListeners = function (){
		$('#tagpoint_submit').unbind('click').click(tagPointStrategySubmit);//添加限速策略确定

		$('#add_tagpoint_limit').on('click', function(){ //初始化添加标签策略模态框
			if (CONF.LANGUAGE == "zh-cn" && CONF.LANGUAGE == "zh-tw") {
				$('#tagPointLimitModal').modal({'width':'800px', 'height':'380px'});
			}else{
				//英文版弹窗宽度需要长一些
				$('#tagPointLimitModal').modal({'width':'920px', 'height':'380px'});
			}
			
			if(!initTagPointStrategyFlag){
				initTagPointStrategy();
			}
		});
	}

	/**
	 * @function 初始化限速策略函数
	 * @description 为限速策略各操作事件绑定事件函数
	 * @return void
	 */
	var initSpeedListeners = function(){
		$('#speed_submit').unbind('click').click(speedSubmit);//添加限速策略确定

		$('#addSpeedlimit').on('click', function(){ //初始化添加限速策略模态框
			$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
			if(!initSpeedFlag){
				initSpeedTimeStrategy();
			}
		});
		$('#speedModeType').unbind('change').bind('change', speedModeHandler);//切换限速模式
	}
	/**
	 *@function 初始化存储策略各项操作描述
	 */
	var initStoreListeners = function(){
		//切换存储加密开关
		$('#encryptStorageCheck').on('switchChange.bootstrapSwitch', encryptChange);
		// $('.passwordModeDiv').show();
		//切换自动选择存储加密密码
		$('#passwordAutocheck').on('switchChange.bootstrapSwitch', passwordModeChange);
		//数据加密密码确认
		$('#repassword').on('input propertychange', function(){
			var password = $('#password').val();
			var repassword = $('#repassword').val();
			if(password != repassword){
				$('.passwordTips').show();
			}else{
				$('.passwordTips').hide();
			}
		});


		$('#deduplicationCheck').on("switchChange.bootstrapSwitch",function(){
			initStoreStrategyDes();
		});
		$('#compressCheck').on("switchChange.bootstrapSwitch",function(){
			initStoreStrategyDes();
		});
		$('#encryptStorageCheck').on("switchChange.bootstrapSwitch",function(){
			initStoreStrategyDes();
		});
        // 存储加密算法
        // $('#storageEncryptMethod').on("change", function () {
        //     initStoreStrategyDes();
        // });
	}

	//声明和定义Spinner控件
	var initSpinner = function(){
		$('.takeover_heartbeat_interval_div').spinner({value:60, step: 1, min: 1, max: 3600});
		$('.takeover_app_interval_div').spinner({value:30, step: 1, min:5, max: 3600});
		$('.app_failure_number_div').spinner({value:3, step: 1, min: 1, max: 100});

		$('.exec_script_interval_div').spinner({value:30, step: 1, min: 1, max: 3600});
		$('.script_failure_number_div').spinner({value:1, step: 1, min: 1, max: 100});

		$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('#spinnerDay').spinner({value:7, step: 1, min: 1, max: 9999});
		$('#transfer_thread_div').spinner({value:1, step: 1, min: 1, max: 4});
		$('#transfer_datapackage_div').spinner({value:4, step: 1, min: 1, max: 1024});
		$('#memory_cache_div').spinner({value:512, step: 1, min: 1, max: 10000000000});
		$('.deduplicationDiv').hide();
		// 默认关闭压缩等级
		$('#compressCheck').bootstrapSwitch('state', false);
		// $('.compressGradeDiv').hide();

	}
	var plusButton = function(){
			var inputValue = parseInt($('#agent_heartbeat_failure_time').val());
			inputValue += 1;
		    if(inputValue>3600) {
				inputValue = 30;
			    return false
		    }
			$('#agent_heartbeat_failure_time').val(inputValue);

	}
	var minusButton = function(){
		var inputValue = parseInt($('#agent_heartbeat_failure_time').val());
		inputValue -= 1;
		if(inputValue<5) {
			inputValue = 30;
			return false
		}
		$('#agent_heartbeat_failure_time').val(inputValue);
	}
	var addFailure = function(){
		var inputValue = parseInt($('#app_failure_number').val());
		inputValue += 1;
		if(inputValue>100) {
			inputValue = 3;
			return false
		}
		$('#app_failure_number').val(inputValue);
	}
	var reduceFailure = function(){
		var inputValue = parseInt($('#app_failure_number').val());
		inputValue -= 1;
		if(inputValue<1) {
			inputValue = 3;
			return false
		}
		$('#app_failure_number').val(inputValue);
	}
	var minusTakeover = function() {
		var inputValue = parseInt($('#takeover_app_interval').val());
		inputValue -= 1;
		if(inputValue<5) {
			inputValue = 30;
			return false
		}
		$('#takeover_app_interval').val(inputValue);
	}
	var plusTakeover = function(){
		var inputValue = parseInt($('#takeover_app_interval').val());
		inputValue += 1;
		if(inputValue>3600) {
			inputValue = 30;
			return false
		}
		$('#takeover_app_interval').val(inputValue);

	}
	/**
	 * 表格的配置
	 */
	var gettableDefaultsOpt = function(){
		var defaultsOpt = {
			"searching": false,
			"ordering": false,
			"paging":false,
			"info":false,
			"language": { // language settings
				"emptyTable": LANG.UI_TOOLS_NO_DATA,
				"zeroRecords": LANG.UI_TOOLS_NO_DATA,
			},
		};
		return defaultsOpt;
	}
	/**
	 *@function 重置重建分区配置
	 */
	var resetRebuildPartSwitch = function (){
		$('.diskgenView').hide();
		$('.diskgenviewdiv').hide();
		$('#rebuildPartSwitch').bootstrapSwitch('state', false);
		_createMsgdata.rebuild_partition_flag = 0;
	}
	/**
	 * 重置任务配置，还原默认配置项
	 */
	var resetConfig = function (){
		restDoubleHostMirror();
	}
	/**
	 * 重置双机镜像配置
	 */
	var restDoubleHostMirror = function(){
		$('#double_host_mirror').bootstrapSwitch('state', false); //双机镜像默认关闭
		$('.takeoverhostDes').text(LANG.UI_VOL_CDP_BACKUP_STANDBY_NOT_CONF_DESC);
		if(_backupTaskType == "backup"){ //备份
			$('.doublehostmirrordiv').hide(); //显示双机镜像相关view
			$('.doublehostmirrorvoldiv').hide(); //配置主备卷映射关系
			if(_autoTakeoverAuth){
				$('.realsyncautotakeoverdiv').show();
			}
			$('.cdpautotakeoverswitchdiv').hide();
			$('.dualmachineimageswitchdiv').hide();
			$('.standbyconfdiv').hide();
			$('#standby_host_mode option:eq(2)').prop('disabled', false);
			$('#standby_host_mode option:eq(3)').prop('disabled', false);
			$('.bkimagedatatoserver').hide();
			$('#backup_mode').val(1);
		}else if(_backupTaskType == "copy"){  //复制
			$('.realsyncautotakeoverdiv').hide();
			if(_autoTakeoverAuth){
				$('.cdpautotakeoverswitchdiv').show();
			}
			$('.dualmachineimageswitchdiv').hide();
			// $('.dualmachineimageswitchdiv').show();
			$('.standbyconfdiv').show();
			$('.bkimagedatatoserver').show();
			$('#standby_host_mode option:eq(2)').prop('disabled', true);
			$('#standby_host_mode option:eq(3)').prop('disabled', true);
			$('#backup_mode').val(2);
		}
		

	}
	/**
	 * 重置备份目的地配置
	 */
	var resetBackupDestination = function (){
		$('#real_sync_takeover_config_switch').bootstrapSwitch('state', false); //实时同步-自动接管默认关闭
		// $('#backup_mode').val(1); //备份模式
		$('#standby_host_mode').val(1); //备机类型
		//备机映射配置
		$('#standbyConfDes').show();
		$('#builtInVmConfDes').hide();
		$('#otherVMConfDes').hide();
		$('.popover.in').remove();

		// $('.cdpautotakeoverswitchdiv').hide();
		// $('.dualmachineimageswitchdiv').hide();
		// $('.standbyconfdiv').hide();

	}
	/**
	 * 重置接管配置
	 */
	var restTakeoverConf = function(){
		$('#takeover_config_switch').bootstrapSwitch('state', false); //自动接管默认关闭
		$('.standbyhostDes').text("");
		$('#app_takeover_switch').bootstrapSwitch('state', false); //应用接管默认关闭

		$('.takeoverconfigveiw').hide();
		$('.takeovercollapseview').removeClass("in");
		$('#takeover_config_view').hide();

	}
	//清除表格数据
	var clearTable = function(table, id){
		var tr = $('#' + id + ' tbody tr');
		for(var i=0; i<tr.length; i++){
			table.row().remove();
		}
		table.row().draw();
	}

	/**
	 * 搜索客户端，隐藏非检索节点
	 */
	var searchAgent = function(){
		var value = $('#searchAgent').val();
		// 输入验证
		if(!customInputValidate('string',value)){
			return false;
		}
		var nodes = zTreeAgent.getNodes();
		if(!nodes || nodes.length == 0) return;
		var allNode = zTreeAgent.transformToArray(zTreeAgent.getNodes());
		var nodeParamList = zTreeAgent.getNodesByParamFuzzy('name', value);
		if(nodeParamList.length!=0){
			zTreeAgent.hideNodes(allNode); //需要引入ztree的扩展库
			$('.vcenter-tree').show();
			$('#agent_tree').show();
			$('#nosearchtips').hide();
		}else{
			$('.vcenter-tree').hide();
			$('#agent_tree').hide();
			$('#nosearchtips').show();
		}
		zTreeAgent.showNodes(nodeParamList);
	}

	//选择节点事件
	var agentnodeSelect = function(treeId, treeNode, clickFlag){
		_hostVolInfo = [],_hostAppInfo = [],_perSelectVol = [];
		_existTtaskName = "";
		_optionType = treeNode.osType;
		_inTask = false,_isHaStandby = false,_isRecoveryTarget= false,_isFbTarget=false,_isTakeoverStandby=false;
		if(treeNode.onlineFlag == CONF.FLAG.UNSET){
			_existTtaskName = treeNode.title;
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_BACKUP_CLIENT_OFF_LINE_TIPS);
			$('#hostinfo').hide();
			$('#step1tips').show();
			return false;
		}

		if(!!treeNode.inTask){
			_inTask = treeNode.inTask;  //判断是否有作业
			_existTtaskName = treeNode.title;
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+"," +LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
			$('#hostinfo').hide();
			$('#step1tips').show();
			return false;
		}
		if(!!treeNode.isHaStandby){  //判断客户端是否作为接管备机
			_isHaStandby = treeNode.isHaStandby;
			_existTtaskName = treeNode.title;
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_STANDBY+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
			$('#hostinfo').hide();
			$('#step1tips').show();
			return false;
		}

		if(!!treeNode.isRecoveryTarget){  //判断客户端是否作为恢复目标机器
			_isRecoveryTarget = treeNode.isRecoveryTarget;
			_existTtaskName = treeNode.title;
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_RECOVERY_TARGET+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
			$('#hostinfo').hide();
			$('#step1tips').show();
			return false;
		}

		if(!!treeNode.isFbTarget){  //判断客户端是否作为回切目标机器
			_isFbTarget = treeNode.isFbTarget;
			_existTtaskName = treeNode.title;
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_FAILBACK_TARGET+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
			$('#hostinfo').hide();
			$('#step1tips').show();
			return false;
		}

		if(!!treeNode.isTakeoverStandby){  //判断客户端是否作接管备机
			_isTakeoverStandby = treeNode.isTakeoverStandby;
			_existTtaskName = treeNode.title;
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_HOST_IS_AVAILABLE_TAKEOVER_TARGET+"," + LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
			$('#hostinfo').hide();
			$('#step1tips').show();
			return false;
		}


		if(_agentuuid!=treeNode.uuid){ //切换host 需要清空后续配置
			restDoubleHostMirror();//重置双机镜像配置;
			restTakeoverConf();//重置自动接管配置;
		}
		var treeObj = $.fn.zTree.getZTreeObj(treeId);
		treeObj.checkNode(treeNode, !treeNode.checked, true);
		//客户端连接服务端
		if(treeNode.net_model == 2){
			networkFlag = true;
		}

		_agentuuid = treeNode.uuid;
		_agentDefaultCachePath = treeNode.agent_default_cache_path;  //客户端文件缓存
		_agentName = treeNode.name;
		_agentIP = treeNode.title;
		_agentType = treeNode.osType;

		_createMsgdata.agentUUID = treeNode.uuid;
		_createMsgdata.getNmae = treeNode.name;
		verifyingVolCdpAuthNum();  //校验卷CDP客户端授权数是否充足 

	}
	/**
	 * 校验模块授权数是否充足，是择执行更新操作，否则提示错误信息
	 */
	var verifyingVolCdpAuthNum = function (){ 
		if(authInfo.capacityOrQuantity.auth_type ==1){ //按数量授权
			if(authInfo.capacityOrQuantity.total == -1 || authInfo.capacityOrQuantity.used ==-1 || authInfo.capacityOrQuantity.total > authInfo.capacityOrQuantity.used) {  //无限数量授权 或授权充足
				$('#step1tips').hide();
				$('#hostinfo').show();
				if(!_updateAgentFlag){
					updateAgentInfo(_agentuuid);  // 自动刷新客户端，获取到返回之后再更新应用和卷芯
				}
			}else{
				if(_backupTaskType=="backup"){
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_CANNOT_CONFIG_TIP);
				}else{
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_COPY_AUTH_NUM_CANNOT_CONFIG_TIP);
				}
				return;
			}
		}else{  //按容量授权
			$('#step1tips').hide();
			$('#hostinfo').show();
			updateAgentInfo(_agentuuid);  // 自动刷新客户端，获取到返回之后再更新应用和卷芯
		}

		// $.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'verifyingVolCdpAuthNum',p:''}, function(d){
		// 	var data = JSON.parse(d);
		// 	if(data['result']){
		// 		$('#step1tips').hide();
		// 		$('#hostinfo').show();
		// 		if(!_updateAgentFlag){
		// 			updateAgentInfo(_agentuuid);  // 自动刷新客户端，获取到返回之后再更新应用和卷芯
		// 		}

		// 	}else{
		// 		UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_CANNOT_CONFIG_TIP);
		// 		return;
		// 	}
		// });
	}
	/**
	 * 更新客户端信息，包括客户端对应的卷信息和应用信息
	 */
	var updateAgentInfo = function(agent_uuid){
		_updateAgentFlag = true;
		Metronic.blockUI({target: "#hostinfo",animate: true});
		var data = JSON.stringify({agent_uuid:agent_uuid});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'updateAgentInfo',p:data}, function(d){
			Metronic.unblockUI("#hostinfo");
			_updateAgentFlag = false;
			var data = JSON.parse(d);
			var re = data['re'];
			var lev = data['warning'];
			var msg = data['msg'];
			var title = data['title'];
			if(re){
				UIToastr.showSuccess(title,msg);
				$('#historyAppData').hide();
				$('#historyVolData').hide();
			}else{
				UIToastr.showWarning(title,msg+"，"+LANG.UI_VOL_CDP_HOST_VOL_HIS_DATA);
				$('#historyAppData').show();
				$('#historyVolData').show();
			}
			initAppTree();
			initLoadVolTab();
			loadAgentNetworkInfo(agent_uuid);
			getAgentCachePath(agent_uuid);
		})
		initAppTree();
		initLoadVolTab();
		loadAgentNetworkInfo(agent_uuid);
	}
	var getAppFontCss = function(treeId, treeNode) {
		var css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.inbackup){
			css = {color:"green", "font-weight":"bold"};
		}
		if(!!treeNode.highlight){
			//搜索使用的样式
			css = {color:"#A60000", "font-weight":"bold"};
		}
		return css;
	}

	var setVolCdpAppTree = function(zNodes){
		Metronic.unblockUI("#hostappdiv");
		if(zNodes == "[]"){
			$("#noappinfo").show();
			$('.vcenter-tree').hide();
			$('#cdp_app_tree').hide();
			$('#checkapptips').hide();
			return;
		}else{
			$("#noappinfo").hide();
			$('.vcenter-tree').show();
			$("#cdp_app_tree").show();
			$('#checkapptips').show();
		}
		var setting = {
			check: {
				enable: true,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					rootPId: 0
				},key: {
					title: "title"
				}
			},
			view: {
				fontCss: getAppFontCss,
				addDiyDom: addHoverDom,
			},
			callback: {
				beforeClick: nodeSelect,
				onCheck: appOnCheck,
			}
		};
		var nodes = JSON.parse(zNodes);  //拿到备份节点
		zTreeCdpApp = $.fn.zTree.init($("#cdp_app_tree"), setting, nodes);
	}

	//添加应用更新按钮
	var addHoverDom = function(treeId, treeNode) {
		var nodeID = escapeJquery(treeNode.id);
		var nodeTID = escapeJquery(treeNode.tId);
		if(treeNode.nodeType=='host'){  //主机
			var aObj = $("#" + nodeTID + "_a");
			var str = '<a id="refresh_' + treeNode.tId + treeNode.id + '_host" title="'+LANG.UI_VOL_CDP_BACKUP_REFRESH_CLIENT_APPLICATION+'" class="apptreehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>';
		}else if(treeNode.nodeType=='app'){  //应用
			var aObj = $("#" + nodeTID + "_a");
			var str = '<a id="refresh_' + treeNode.tId + treeNode.id + '_app" title="'+LANG.UI_VOL_CDP_BACKUP_REFRESH_APPLICATION+'" class="apptreehref">' + LANG.UI_BACKUP_TREE_REFRESH + '</a>';
		}else{ return;}
		aObj.after(str);
		var hrefRefreshHost = $("#refresh_" + nodeTID + nodeID + "_host");
		var hrefRefreshApp = $("#refresh_" + nodeTID + nodeID + "_app");

		if (hrefRefreshHost) hrefRefreshHost.bind("click", function(){
			getSyncHostAppInfo(treeId, treeNode, 'host',true);
		});
		if (hrefRefreshApp) hrefRefreshApp.bind("click", function(){
			getSyncHostAppInfo(treeId, treeNode, 'app',true);
		});
	}

	//节点选中
	var nodeSelect = function(treeId, treeNode, clickFlag){
		var treeObj = $.fn.zTree.getZTreeObj(treeId);
		treeObj.checkNode(treeNode, !treeNode.checked, true);
		var checkedNodes = treeObj.getCheckedNodes(true);
		checkedNodeLoadVol(checkedNodes);
	};

	//勾选应用节点
	var appOnCheck = function(e, id, node){
		var tree = $.fn.zTree.getZTreeObj(id);
		var checkedNodes = tree.getCheckedNodes(true);
		checkedNodeLoadVol(checkedNodes);
	};
	/**
	 * 根据选中节点包含的卷信息加载数据卷
	 */
	var checkedNodeLoadVol = function(checkedNodes){
		var appVol = [];
		var volInfo = [];
		var appMountVol = [];
		var mountPath = [];
		var appMountObj = {};
		var appHisVol = [];
		var appName = "";
		for(var i=0;i<checkedNodes.length;i++){
			if((checkedNodes[i].module_vol_info).length>0){
				var volInfo = checkedNodes[i].module_vol_info;
				appVol.push(volInfo);
				for(var j =0; j<volInfo.length;j++){
					appMountVol.push(volInfo[j].vol_uuid);
					appHisVol.push(volInfo[j].vol_uuid);
					mountPath.push(volInfo[j].mountPath);
				}
			}
			if(checkedNodes[i].isApp){
				appName = checkedNodes[i].name;
			}
		}
		//二维数组去重,过滤掉重复的关联卷信息
		if(appVol.length != 0){
			var volInfo = objArrayDdeweight(appVol);
		}

		appMountObj.app_name = appName;
		appMountObj.vol = appMountVol;
		appMountObj.mount_path = mountPath;
		appMountObj.his_vol = appHisVol;
		_appMountList = appMountObj;
		checkedClientVol(volInfo);
	}

	/**
	 * @function 组装选择应用所在卷信息
	 * @params:list：操作对象
	 */
	var objArrayDdeweight = function(list){
		var listInfo = [];
		if(list.length==1){
			var listInfo = list[0];
			return listInfo;
		}
		//解析多维数组，平铺到listInfo中
		for(var n=0;n<list.length;n++){
			var volInfo = list[n];
			for(var m=0;m<volInfo.length;m++){
				listInfo.push(volInfo[m]);
			}
		}
		var allVolArr = []; //去重后选中节点关联卷
		//对象数组去重
		$.each(listInfo, function(i, v) {
			var flag = true;
			if (allVolArr.length > 0) {
				$.each(allVolArr, function(n, m) {
					if (allVolArr[n].vol_uuid == listInfo[i].vol_uuid) { flag = false; };
				});
			};
			if (flag) {
				allVolArr.push(listInfo[i]);
			};
		});
		return allVolArr;
	}
	//遍历host vol 动态关联app中卷ID
	var checkedClientVol = function(list){
		_perSelectVol = list;
		grid.getRefresh({agentuuid:_agentuuid,agentname:_agentName,agentip:_agentIP,selectvol:_perSelectVol}, undefined, true);
	};
	// 更新客户端下所有应用信息/更新选定应用信息
	var getSyncHostAppInfo = function(treeId, treeNode,refreshType,expendFlag){
		var data = JSON.stringify({id:treeNode.id,uuid:treeNode.uuid,agentUuid:treeNode.agent_uuid, refreshType:refreshType});
		Metronic.blockUI({target: "#hostappdiv",animate: true});// 能够为页面上的任意元素添加遮层,阻止用户操作
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'updateAppInfo',p:data}, function(d){
			Metronic.unblockUI("#hostappdiv");
			if(OPREL(d)){
				initAppTree();
			}
			var data = JSON.parse(d);
			var request = data['re'];
		})
	}

	//获取所选主机应用信息
	var initAppTree = function(){
		var paramsInfo = {};
		paramsInfo.agentuuid = _agentuuid;
		paramsInfo.agentname = _agentName;
		paramsInfo.agentip = _agentIP;
		var p = JSON.stringify(paramsInfo);
		$("#cdp_app_tree").show();
		Metronic.blockUI({target: "#hostappdiv",animate: true});// 能够为页面上的任意元素添加遮层,阻止用户操作
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP, f: 'getVolCdpAppTree', p: p}, setVolCdpAppTree);

	}
	//加载选定客户端对应的卷信息，填充表格
	var initLoadVolTab = function(){
		if(!gridInitFlag){	//初始化表格
			var dataTableOpt = {
				'showLoading':false,
				'columnDefs' : [{
					'orderable': false,
					'targets': [0,1,2,3,4]
				}],
				"pageLength": 25,
				"aLengthMenu": [25, 50, 100,200,500],
				"order": [
					[1, "desc"]
				],
			};
			grid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPBACKUP,f:'getHostVolinfo',p:{agentuuid:_agentuuid,agentname:_agentName,agentip:_agentIP,selectvol:_perSelectVol}};
			grid.setAjaxParam(dataPar);
			grid.init({src: $("#host_vol_table"),checkbox:true,dataTable:dataTableOpt,onDataLoad:tabClickSelection});
			gridInitFlag = true;

		}else{	//刷新表格
			grid.getRefresh({agentuuid:_agentuuid,agentname:_agentName,agentip:_agentIP,selectvol:_perSelectVol}, undefined, true);
		}
		return;
	}
	/**
	 * 捕捉表格点击事件
	 */
	var tabClickSelection = function (){
		$("#host_vol_table td").click(function(){
			var tdSeq = $(this).parent().find("td").index($(this)[0]);  //获取当前点击的表格列
			if(tdSeq!=0){  //用以处理限制某些列的选中，只允许第一列可以选中
				return false;
			}
		});
		$('#host_vol_table tbody').unbind('click').on('click', 'tr', function () {
			var disableTr = false;
			var volData = grid.getDataTable().data();
			var value = $(this).find('input').val();
			for(var i=0;i<volData.length;i++){
				if(volData[i][5]==value && volData[i][10]==true){  //系统分区
					for(var j = 0;j<volData.length;j++){
						if(volData[j][11]){ //引导分区
							var inputId = "isBootPart"+volData[j][5];
							var checkBoxs = $('#host_vol_table').find('tbody > tr').find('input[id="'+inputId+'"]');
							var checked = checkBoxs[0].checked;
							if(!checked){
								var span = $("#"+inputId).parent();
								$(span).prop("class", "checked");
								$(checkBoxs).prop("checked", true);
							}
						}
					}
				}else if(volData[i][5]==value && volData[i][11]==true){
					var checkBox = $(this);
					var checked = checkBox[0].checked;
					var span = checkBox[0].parentNode;
					$(span).prop("class", "");
					break;
				}
				if(volData[i][5]==value){
					var voluuid = volData[i][5];
					var mountPtah = volData[i][2];
					var inputId = "volId" + voluuid;
					var checkBoxs = $('#host_vol_table').find('tbody > tr').find('input[id="'+inputId+'"]');
					var checked = checkBoxs[0].checked;
					if(!checked){	//取消选中
						var hisVol = _appMountList.his_vol;
						var volResult = $.inArray(voluuid,hisVol);
						if(volResult>=0){
							updateAppSelected(voluuid,mountPtah);
						};
					}else{
						var hisVol = _appMountList.his_vol;
						var volResult = $.inArray(voluuid,hisVol);
						if(volResult>=0){
							_appMountList.vol.push(voluuid);
						}
					}
				}
			}
			if(disableTr){ return false; }
		});
	}
	/**
	 * 更新应用选中
	 * @modify time:2023年6月26日11:37:59
	 * @modify content：增加挂载点的校对
	 */
	var updateAppSelected = function(voluuid,mountPtah){
		var appVolList = [];
		_appMountList.vol.splice($.inArray(voluuid, _appMountList.vol),1);
		if(!$.isEmptyObject(_appMountList)){
			appVolList = _appMountList.vol;
		}
		var mountPathList = [];
		if(!$.isEmptyObject(_appMountList)){
			mountPathList = _appMountList.mount_path;
		}
		var mountPathExists = mountPathList.indexOf(mountPtah);
		var appTree = $.fn.zTree.getZTreeObj("cdp_app_tree");
		var checkedNodes = appTree.getCheckedNodes(true);
		var appType = 0;
		if(checkedNodes.length>0){
			for(var i=0;i<checkedNodes.length;i++){
				if(checkedNodes[i].isApp){
					appType = checkedNodes[i].app_type;
				}
			}
		}
		if((appVolList.length >0)&&appType!=1){
			initAppTree();
		}else if(appVolList.length==0){
			initAppTree();
		}
	}
	/**
	 * 初始化熟悉结构及对应的view
	 */
	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$("#novolcdpagent").show();
			$('.vcenter-tree').hide();
			$("#agent_tree").hide();
			$('.searchDiv').hide();
			return;
		}else{
			$("#novolcdpagent").hide();
			$('.vcenter-tree').show();
			$("#agent_tree").show();
			$('.searchDiv').show();
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
			view: {
				fontCss: getFontCss,
			},
			callback: {
				beforeClick: agentnodeSelect,
			}
		};
		zTreeAgent = $.fn.zTree.init($("#agent_tree"), setting, JSON.parse(zNodes));
	};
	//加载客户端树
	var initAgentTree = function(type) {
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getVolCdpBackupAgentTree',p:{}}, setTree);
	};
	/**
	 * 动态调整节点样式
	 */
	var getFontCss = function(treeId, treeNode) {
		var css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.inTask || !!treeNode.isHaStandby || !!treeNode.isRecoveryTarget || !!treeNode.isFbTarget || !!treeNode.isTakeoverStandby){
			css = {color:"green", "font-weight":"bold"};
		}
		if(treeNode.onlineFlag == CONF.FLAG.UNSET){
			css = {color:"gray", "font-weight":"normal"};
		}
		return css;
	}
	/**
	 * 获取客户端缓存文件路径
	 * @param agent_uuid
	 */
	var getAgentCachePath = function (agent_uuid){
		var data = {};
		data.agent_uuid = agent_uuid;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getAgentCachePath',p:jsonData}, function(d){
			var data = JSON.parse(d);
			_agentDefaultCachePath  = data['agent_cache_path'];
		});
	}

	/**
	 * 获取客户端网卡信息
	 */
	var loadAgentNetworkInfo = function (agent_uuid){
		var data = {};
		data.agent_uuid = agent_uuid;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getHostNetworkInfo',p:jsonData}, function(d){
			var data = JSON.parse(d);
			_agentNetworkInfo = data;
		});
	}
	/**
	 * 获取备机网卡信息
	 */
	var loadStandbyNetworkInfo = function(agent_uuid){
		var data = {};
		data.agent_uuid = agent_uuid;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getHostNetworkInfo',p:jsonData}, function(d){
			var data = JSON.parse(d);
			_standbyNetworkInfo = data;
		});
	}
	//初始化存储下拉框
	var initStorageSelect = function(){
		var data = {};
		data.nodeuuid = $('#selectnode').val();
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
			var data = JSON.parse(d);
			var softselect = $('#selectstorage');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				if(data[i].type==CONF.BD_STORAGE_TYPE.CLOUD || data[i].type==CONF.BD_STORAGE_TYPE.TAPE){
					//实时容灾不能选择磁带
					continue;
				}
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
		});
	}

	//监听是否启动双机镜像
	var doubleHostMirrorChange = function(){
		if(this.checked){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_BACKUP_MIRROR_WARNING_TIPS);
			$("#bk_imagedata_to_server").bootstrapSwitch('state', true);
			$('.mapvolumeview').removeClass("collapsed");
			if(authInfo.capacityOrQuantity.total == -1 || authInfo.capacityOrQuantity.used ==-1 || authInfo.capacityOrQuantity.total > authInfo.capacityOrQuantity.used) {  //无限数量授权 或授权充足
				if(_HostConfigVolDatatable!=undefined){
					clearTable(_HostConfigVolDatatable,"backup_host_vol_table");
				}
				writeInBackupHostVol(); //将已配置的主机卷信息写入到表格中

				$('.standbyhostDes').text(_agentName);
				$('.doublehostmirrordiv').show();  //显示双机镜像相关view
				$('.doublehostmirrorvoldiv').show(); //配置主备卷映射关系
				$('.stdmapvolume').addClass("in");
				$('.stdmapvolume').css('height','100%');
				$('.mapvolumeview').click(mapVolumeCollapseEvent);
				$('#map_volume_view').show();
				$("#sh_for_takeover").attr("disabled","disabled");
				$('.bkimagedatatoserver').show();
			}else {
				$('#double_host_mirror').bootstrapSwitch('state', false); //关闭双机镜像
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_DOUBLE_HOST_MIRROR_TIP);
				return false;
			}

			// $.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'verifyVolCdpTakeoverAuthNum',p:''}, function(d){  //校验卷CDP接管授权数是否充足 
			// 	var data = JSON.parse(d);
			// 	if(data['result']){  //授权充足
			// 		if(_HostConfigVolDatatable!=undefined){
			// 			clearTable(_HostConfigVolDatatable,"backup_host_vol_table");
			// 		}
			// 		writeInBackupHostVol(); //将已配置的主机卷信息写入到表格中

			// 		$('.standbyhostDes').text(_agentName);
			// 		$('.doublehostmirrordiv').show();  //显示双机镜像相关view
			// 		$('.doublehostmirrorvoldiv').show(); //配置主备卷映射关系
			// 		$('.stdmapvolume').addClass("in");
			// 		$('.stdmapvolume').css('height','100%');
			// 		$('.mapvolumeview').click(mapVolumeCollapseEvent);
			// 		$('#map_volume_view').show();
			// 		$("#sh_for_takeover").attr("disabled","disabled");
			// 		$('.bkimagedatatoserver').show();
			// 	}else{
			// 		$('#double_host_mirror').bootstrapSwitch('state', false); //关闭双机镜像
			// 		UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_DOUBLE_HOST_MIRROR_TIP);
			// 		return false;
			// 	}
			// });

		}else{
			if($('#takeover_config_switch').is(':checked')) {
				//$('#auto_takeover_target_view').show();
				$('#autoTakeoverHostTips').hide();
				if(_takeoverVolMountPointTab!=undefined){
					clearTable(_takeoverVolMountPointTab,"takeover_vol_target_table");
				}
				loadBackukpVolInTakeoverTab();
			}
			$('.doublehostmirrordiv').hide();  //显示双机镜像相关view
			$('.doublehostmirrorvoldiv').hide();  //配置主备卷映射关系
			$('.stdmapvolume').removeClass("in");
			$('#map_volume_view').hide();
			$("#sh_for_takeover").removeAttr("disabled");
			$('.mapvolumeview').addClass("collapsed");
			resetRebuildPartSwitch();
			$('.bkimagedatatoserver').hide();
			if(_autoTakeoverAuth){
				$('.cdpautotakeoverswitchdiv').show();
			}
			$('#doubleHostMirrorSelect').val('0');
		}
	}
	//启用双机镜像映射卷，动态调整对应panle的高度
	var mapVolumeCollapseEvent = function (){
		var is_expand = $(this).attr("aria-expanded");

//		if(is_expand=="true"){
//			$('.confvolmap').css("height","32px");
//		}else{
//			$('.confvolmap').css("height","100%");
//		}
	}
	/**
	 * 加载选择的备份卷，写入表格，生成配置目标挂载点配置项
	 */
	var loadBackukpVolInTakeoverTab = function(){
		var hostVolConf = _hostVolInfo;
		if(_takeoverVolMountPointTab == undefined){
			_takeoverVolMountPointTab =  $('#takeover_vol_target_table').DataTable(gettableDefaultsOpt());
		}
		for(var i=0;i<hostVolConf.length;i++){
			var volSizeValue = hostVolConf[i]["volSizeValue"];
			var volUuid = hostVolConf[i]["volUuid"];
			var mountInputId = "mount_target_"+volUuid;
			var osType = hostVolConf[i]['osType'];
			var targetInfo = '<input type ="text" class = "form-control input-sm"  id ='+mountInputId+' value = "'+LANG.UI_VOL_CDP_BACKUP_SYSTEM_AUTO_ALLOCATE+'">';
			_takeoverVolMountPointTab.row.add([
				hostVolConf[i]['displayName'],
				hostVolConf[i]['volSize'],
				targetInfo,
				volUuid,
				osType,
			]).draw();
		}
//		shForTakeoverChange();
		putTakeoverHostMountpoint();
	}

	//写入以配置的主机卷信息，加载选择的备用服务器卷信息到表格的select
	var writeInBackupHostVol = function (){
		var hostVolConf = _hostVolInfo;
		if(_HostConfigVolDatatable == undefined){
			_HostConfigVolDatatable =  $('#backup_host_vol_table').DataTable(gettableDefaultsOpt());
		}
		var standbyVolumeName =LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_VOL;
		if(_createMsgdata.rebuild_partition_flag==1){
			standbyVolumeName =LANG.UI_VOL_CDP_BACKUP_CHOOSE_DISK;
		}
		var backupMode = $('#backup_mode').val();
		for(var i=0;i<hostVolConf.length;i++){
			var volSizeValue = hostVolConf[i]["volSizeValue"];
			var volUuid = hostVolConf[i]["volUuid"];
			var standbyVol = '<select class="form-control input-sm" name="standbyvol" id='+"standby_volume_"+i+' >\
				<option value = "'+volSizeValue+'" data-hostvoluuid="'+volUuid+'" data-op-default="1">'+standbyVolumeName+'</option></select>';
			if(backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC){   //实时备份
				standbyVol = '<input class = "form-control input-sm" name = "mount_point" value="'+LANG.UI_VOL_CDP_TAKEOVER_AUTO_ALLOCATE+'" id = '+"standby_mountpoint_"+i+'></input>'
			}
			_HostConfigVolDatatable.row.add([
				hostVolConf[i]['displayName'],
				hostVolConf[i]['volSize'],
				standbyVol,
				hostVolConf[i]['volUuid']
			]).draw();
		}
		loadStandbyHost();
	}
	/**
	 * @function加载双机镜像备用服务器
	 * @description:获取并过滤已选择主机uuid，返回其他卷CDP客户端设备信息及卷信息
	 */
	var loadStandbyHost = function (){  //在用
		var paramsInfo = {};
		paramsInfo.agentuuid = _agentuuid;
		paramsInfo.standbyuuid = "";
		paramsInfo.master_os_type =  _agentType;
		var params = JSON.stringify(paramsInfo);
		_StandbyHostData = []; //重置获取的备机数据
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getStandbyHostInfo',p:params}, function(d){
			var data = JSON.parse(d);

			var hostMirrorSelect = $('#standbyHostSelect');
			hostMirrorSelect.empty();
			var defaultOption = $("<option value = '0'>"+LANG.UI_VOL_CDP_BACKUP_STANDBY_MACHINE_SELECT+" </option>");
			hostMirrorSelect.append(defaultOption);
			if(!data.length) return;
			_StandbyHostData = data;
			for(var i=0;i<data.length;i++){
				var inTask = data[i].in_task;
				var taskName = data[i].task_name;
				var disabled = "";
				var inTaskTitle = "";
				if(inTask){
					disabled = "disabled";
					inTaskTitle = taskName+"("+LANG.UI_VOL_CDP_TASK_PROTECTED_STR+")";
				}
				var option = $("<option "+ disabled +">").text(data[i].text).val(data[i].value)
					.attr('agent-type', data[i].agent_type)
					.attr('data-netmode', data[i].net_mode)
					.attr('data-onlineflag', data[i].online_flag)
					.attr('data-ostype', data[i].os_type)
					.attr('data-mountpoint', data[i].mount_info)
					.attr('title',inTaskTitle);
				hostMirrorSelect.append(option);
			}
		});
		$('#standbyHostSelect').unbind('change').bind('change', standbyHostSelectChange);
	}
	/**
	 * @function  选择备机change事件
	 * @description 将备机的磁盘信息装载到映射卷table中,提供选择配置映射
	 * @return void
	 */
	var standbyHostSelectChange = function (){  //在用
		var standbyHostValue =$('#standbyHostSelect').val(); //双机镜像备机value
		var data = JSON.stringify({agent_uuid:standbyHostValue});
		var agentType = $("#standbyHostSelect").find("option:selected").attr("agent-type");
		_standbyHostMountPoint = $('#standbyHostSelect').find("option:selected").attr("data-mountpoint");
		var backupMode = $('#backup_mode').val();

		if(agentType==CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS && backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC ){
			$('#standbyHostSelect option:first').prop('selected', true);
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VOL_CDP_SELECT_NO_SELECT_TAKEOVER_STANDBY_MACHINE_TIPS);
			$('.targetHostUsedPartition').hide();
			$('.targetHostUsedPartition').html("");
			return false;
		}else if(agentType==CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS && $('#takeover_config_switch').is(':checked')
				&& (backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY || backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY)
		){
			$('#standbyHostSelect option:first').prop('selected', true);
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VOL_CDP_SELECT_NO_SELECT_TAKEOVER_STANDBY_MACHINE_TIPS);
			$('.targetHostUsedPartition').hide();
			$('.targetHostUsedPartition').html("");
			return false;
		}
		var targetHost = $("#standbyHostSelect").find("option:selected").text();
		var backupMode = $('#backup_mode').val();
		if(backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC){
			$('.targetHostUsedPartition').show();
			var hostMountpointStr = $(this).find("option:selected").attr("data-mountpoint");
			$('.targetHostUsedPartition').html(LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE + LANG.UI_VOL_CDP_TAKEOVER_MOUNT_HAS_BEEN_USED +": "+hostMountpointStr);
		}else{
			$('.targetHostUsedPartition').hide();
			$('.targetHostUsedPartition').html("");
		}
		if(agentType==CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){  //内存操作系統
			$('#takeover_config_switch').bootstrapSwitch('state', false); //关闭自动接管开关
			$('.takeoverconfigveiw').hide();
			$('.takeovercollapseview').removeClass("in");
			$('#takeover_config_view').hide();
			for(var j=0;j<_hostVolInfo.length;j++){
				$('#standby_volume_'+j+' option').not('option:first').remove();
				$("#standby_volume_"+j).unbind('change').bind('change',(standbyVolChange));
			}
		}
		if(standbyHostValue==0){
			loadStandbyVolTargetInfo();
		}else{
			Metronic.blockUI({target: "#proxyClientModal",animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'updateAgentInfo',p:data}, function(d){
				if(OPREL(d)){
					setTimeout(function(){
						Metronic.unblockUI("#proxyClientModal");
						reloadHostVolTargetInfo(standbyHostValue);
						_catBackVolSet = [];
					},1000);
					loadStandbyNetworkInfo(standbyHostValue);  //获取备机网卡信息
				}else{
					Metronic.unblockUI("#proxyClientModal");
				}
			});
		}
	}
	/**
	 * 重新加载选中备机的卷信息
	 */
	var reloadHostVolTargetInfo = function(standbyHostuuid){
		var paramsInfo = {};
		paramsInfo.agentuuid = "";
		paramsInfo.standbyuuid = $('#standbyHostSelect').val();
		paramsInfo.master_os_type =  _agentType;
		var params = JSON.stringify(paramsInfo);
		_StandbyHostData = []; //重置获取的备机数据
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getStandbyHostInfo',p:params}, function(d){
			var data = JSON.parse(d);
			if(!data.length) return;
			var mountInfo = data[0]['mount_info'];
			$('.targetHostUsedPartition').html(LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE + LANG.UI_VOL_CDP_TAKEOVER_MOUNT_HAS_BEEN_USED +": "+ mountInfo);
			_StandbyHostData = data;
			loadStandbyVolTargetInfo();
		});
	}
	/**
	 * 填充选中备机的映射目标卷信息
	 */
	var loadStandbyVolTargetInfo = function(){
		_standbyVolMapSet = [];	//备用卷关系集
		_selectMapVol = [];	//记录已选择的备机
		for(var j=0;j<_hostVolInfo.length;j++){
			$('#standby_volume_'+j+' option').not('option:first').remove();
			$("#standby_volume_"+j).unbind('change').bind('change',(standbyVolChange));
		}
		_createMsgdata.rebuild_partition_flag = 0; //默认不重建分区，数据恢复，过滤系统卷
		var sdbHostUuid = $('#standbyHostSelect').val();
		var doubleHostMirrorVal = $('#standbyHostSelect').val();
		var doubleHostMirrorText = $('#standbyHostSelect option:selected').text();
		var onlineFlag = $("#standbyHostSelect").find("option:selected").attr("data-onlineflag");  //双机镜像备机在线状态
		var takeoverHostHisConf = $('#sh_for_takeover').val();
		if(takeoverHostHisConf !=doubleHostMirrorVal){  //历史配置和当前选中的双机备机不匹配，需要重置网络配置
			ipMapInfoList = [];
			_takeoverIpMapList = [];
			_takeoverIpMapListView = [];
			$('#takeover_ip_map_list li').remove();
		}

		$('#sh_for_takeover').val(doubleHostMirrorVal);
		$('.takeoverhostDes').text(doubleHostMirrorText);

		resetRebuildPartSwitch();
		var netMode = $("#standbyHostSelect").find("option:selected").attr("data-netmode");
		if(netMode==2){
			networkFlag = true;
		}
		for(var i=0;i<_StandbyHostData.length;i++){
			if(sdbHostUuid ==_StandbyHostData[i].uuid){
				var checkedHostVol= _StandbyHostData[i].vol_info;
				var hostVol= _StandbyHostData[i].vol_info;  //分区信息 
				var hostDisk = _StandbyHostData[i].disk_info;  //磁盘信息
				var agentType = _StandbyHostData[i].agent_type;

				if(agentType==2){  // 此处需要添加agentType的定义
					$('.diskgenView').show();
					$('.diskgenviewdiv').show();
				}

				if(checkedHostVol.length==0 && hostDisk.length!=0 && agentType==2){
					$('#rebuildPartSwitch').bootstrapSwitch('state', true);
					_standbyTargetHostVol = []; //切换备机时需要清空历史备机目标卷信息
				}else{
					_standbyTargetHostVol = hostVol;
				}
				if(checkedHostVol.length==0 && hostDisk.length==0){
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL_OR_DISK_MESSAGE);
					break;
				}
				_standbyTargetHostDisk = hostDisk;
				_createMsgdata.standby_target_agent_uuid = sdbHostUuid;
				paddingStandbyTargetVol();

			}
		}

	}
	/**
	 * 根据的条件填充镜像目标目标卷
	 * 0. 数据镜像：对应select数据为volume信息且需要过滤系统分区
	 * 1. 镜像备机重建分区：对应select数据为disk信息
	 * 2. 镜像备机不重建分区:对应select数据为volume信息
	 */
	var paddingStandbyTargetVol = function(){
		var hostVol = _standbyTargetHostVol;
		var hostDisk = _standbyTargetHostDisk;
		var standbyVolumeSelect = $("select[name='standbyvol']");
		var diskgenFlag = _createMsgdata.rebuild_partition_flag;
		for(var j=0;j<_hostVolInfo.length;j++){
			$('#standby_volume_'+j+' option').not('option:first').remove();
			$("#standby_volume_"+j).unbind('change').bind('change',(standbyVolChange));
			if(diskgenFlag ==1){
				$('#standby_volume_'+j+' option:first').text(LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_DISK);
			}else{
				$('#standby_volume_'+j+' option:first').text(LANG.UI_VOL_CDP_BACKUP_MAP_SELECT_VOL);
			}
		}

		switch(diskgenFlag){
			case 0: //数据恢复：对应select数据为volume信息且需要过滤系统分区 
				for(var j=0;j<hostVol.length;j++){
					var opText = hostVol[j].display_name+" ("+LANG.UI_VOL_CDP_BACKUP_ALL_CAPACITY+":"+hostVol[j].capacity+")";

					var systemVol = hostVol[j].system_vol;
					if(systemVol){
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-mount_point', hostVol[j].mount_point)
							.attr('data-op-default',2).attr('disabled',"disabled");
					}else{
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-mount_point', hostVol[j].mount_point)
							.attr('data-op-default',2);
					}
					standbyVolumeSelect.append(option);
				}
				break;
			case 1: //系统恢复重建分区：对应select数据为disk信息
				for(var j=0;j<hostDisk.length;j++){
					var opText = hostDisk[j].display_name+" ("+LANG.UI_VOL_CDP_BACKUP_ALL_CAPACITY+":"+hostDisk[j].capacity+")";
					var option = $("<option>").text(opText).val(hostDisk[j].uuid)
                        .attr('data-capacity', hostDisk[j].capacity_value)
						// .attr('data-mount_point', hostVol[j].mount_point)
						.attr('data-op-default',2);
					standbyVolumeSelect.append(option);
				}
				break;
			case 2: //系统镜像不重建分区:对应select数据为volume信息
				for(var j=0;j<hostVol.length;j++){
					var systemVol = hostVol[j].system_vol;
					var opText = hostVol[j].display_name+LANG.UI_VOL_CDP_RECOVER_TOTAL_CAPACITY+hostVol[j].capacity+")";

					if(systemVol){
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-mount_point', hostVol[j].mount_point)
							.attr('data-op-default',2).attr('disabled',"disabled");
					}else{
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-mount_point', hostVol[j].mount_point)
							.attr('data-op-default',2);
					}
					standbyVolumeSelect.append(option);
				}
				break;
		}
	}
	//重建系统引导分区选择函数
	var rebuildPartSwitchChnage = function (){
		var doubleHostuuid = $('#doubleHostMirrorSelect');
		if(this.checked && !doubleHostuuid){
			$('#rebuildPartSwitch').bootstrapSwitch('state', false);
			UIToastr.showWarning(LANG.UI_OS_PLUG_REBUILT_VOL, LANG.UI_VOL_CDP_BACKUP_CHOOSE_IMAGE);
			return false;
		}else{
			_standbyVolMapSet = [];
			_catBackVolSet = [];
			if($('#rebuildPartSwitch').is(':checked')) {
				_createMsgdata.rebuild_partition_flag = 1;
			}else{
				_createMsgdata.rebuild_partition_flag = 2;
			}
			paddingStandbyTargetVol();
		}
	}
	/**
	 * @function 备机卷change事件
	 * @description 为选中的主机卷配置卷映射关系，需要监测当前选中的分区是否已经配置映射关系
	 * @return volid
	 */
	var standbyVolChange = function(){
		var isSwitch = _createMsgdata.rebuild_partition_flag
		if(isSwitch ==1) {
			// 数据源信息
			var hostVolumeSize = parseInt($(this)[0][0].value);
			var hostVolUuid = $(this).find("option").attr("data-hostvoluuid");  // 主机对应卷uuid

			// 回切机信息
			var targetDiskUuid = $(this).val(); // 回切磁盘uuid
			var capacity = parseInt($(this).find("option:selected").attr("data-capacity"));
			var options = $(this).find("option");
			var selectedIndex = parseInt($(this).prop('selectedIndex'));

			_catBackVolSet.some(item => {  // 移除之前选中的磁盘
				if (item.vol_uuid === hostVolUuid) { // 需要将当前卷移除
					const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid === hostVolUuid);
					_catBackVolSet.splice(index, 1);
				}
			});

			if (capacity < hostVolumeSize) {  // 选择的磁盘容量小于当前的卷容量
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_DISK, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL_MESSAGE2);
				options.first().prop("selected", true);
				return;
			}

			if (selectedIndex === 0) {  // 选中了<请选择映射磁盘>
				return;
			}

			// 磁盘可以被多个数据源选中, 因此这里计算已选中磁盘的数据源的总卷容量
			var allVolCapacity = hostVolumeSize;
			_catBackVolSet.some(item => {
				if (item.failback_target_disk_uuid === targetDiskUuid) {  // 选中了这个磁盘
					if (item.vol_uuid !== hostVolUuid) {  // 不能计算当前卷大小，因为上面已经加上了
						allVolCapacity += item.vol_size;
					}
				}
			});
			if (allVolCapacity > capacity) {  // 数据源总大小大于磁盘大小，需要提示错误
				UIToastr.showWarning(
					LANG.UI_VOL_CDP_RECOVER_CONFIGURE_DISK,
					LANG.UI_OS_PLUG_GOAL_RECOVERY + LANG.UI_VOL_CDP_RECOVER_DISK + LANG.UI_VOL_CDP_RECOVER_CAPACITY_GREATER_HOST
				);
				options.first().prop("selected", true);
				return;
			}
			packageFbTargetVol(targetDiskUuid, hostVolUuid, hostVolumeSize, capacity, 2);
		} else {
			var targetVolUuid = $(this).val();
			var capacity = $(this).find("option:selected").attr("data-capacity");
			var hostVolumeSize = $(this)[0][0].value;
			var hostVoluuid = $(this).find("option").attr("data-hostvoluuid");//主机对应卷uuid
			var options = $(this).find("option");
			var selectedIndex = $(this).prop('selectedIndex');
			if(Number(capacity)<Number(hostVolumeSize)){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TARGET_VOLUME, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL_MESSAGE2);
				options.first().prop("selected", true);

				var result = _catBackVolSet.some(item=>{
					if(item.vol_uuid==hostVoluuid){
						const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
						_catBackVolSet.splice(index,1);
					}
				});
				return;
			}
			if (_catBackVolSet.length>0 && selectedIndex!=0){
				var targetVolIsRepeat = true;  //目标卷是否被重复配置
				var result = _catBackVolSet.some(item=>{
					if(item.failback_target_vol_uuid==targetVolUuid){
						const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
						UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TARGET_VOLUME, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL_MESSAGE3);
						$(this).find("option").eq(0).prop("selected",true)
						if(index>=0){
							_catBackVolSet.splice(index, 1);  //根据下标移除指定元素
						}
						targetVolIsRepeat = false;
						return false;
					}
					if(item.vol_uuid==hostVoluuid && targetVolIsRepeat){  //该卷已配置目标卷，为其配置新的目标卷需要更改target_vol_uuid 的值
						const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
						if(selectedIndex!=0){
							_catBackVolSet[index].failback_target_vol_uuid = targetVolUuid;
							targetVolIsRepeat = false;
						}else{
							_catBackVolSet.splice(index, 1);  //根据下标移除指定元素
						}
						return;
					}
				});
				if(selectedIndex!=0 && targetVolIsRepeat){
					packageFbTargetVol(targetVolUuid,hostVoluuid, hostVolumeSize, capacity);
				}
			}else if(selectedIndex==0){
				var result = _catBackVolSet.some(item=>{
					if(item.vol_uuid==hostVoluuid){
						const index = _catBackVolSet.findIndex(_catBackVolSet => _catBackVolSet.vol_uuid===hostVoluuid);
						_catBackVolSet[index].failback_target_vol_uuid = targetVolUuid;
						_catBackVolSet.splice(index,1);
					}
				});
			}else{
				if(selectedIndex!=0){
					packageFbTargetVol(targetVolUuid,hostVoluuid, hostVolumeSize, capacity);
				}
			}
		}
	}


	/**
	 * 封装组合符合条件的回切目标卷
	 */
	var packageFbTargetVol = function(targetVolUuid,hostVoluuid, sourceVolSize, targetSize, agentType = 1){
		var catBackVolSetObj = {};
		_selectMapVol.push(targetVolUuid);
		catBackVolSetObj.vol_uuid = hostVoluuid;
		catBackVolSetObj.vol_size = sourceVolSize;
		if (agentType === 2) {
			catBackVolSetObj.failback_target_vol_uuid = '';
			catBackVolSetObj.failback_target_disk_uuid = targetVolUuid;
		} else {
			catBackVolSetObj.failback_target_vol_uuid = targetVolUuid;
			catBackVolSetObj.failback_target_disk_uuid = '';
		}
		catBackVolSetObj.failback_target_disk_size = targetSize;
		_catBackVolSet.push(catBackVolSetObj);
	}
	/**
	 * @function 监听是否启动双机镜像
	 * @description 根据开关展开和隐藏自动接管配置div
	 * @return volid
	 */
	let isProcessing = false;
	var takeoverConfigChnage = function (){
		var backupMode = $('#backup_mode').val();
		var standbyHostMode = $("#standby_host_mode").val();
		// var proxyClient = $("#standbyHostSelect").find("option:selected").attr("agent-type");
		var proxyClient = _createMsgdata.standby_agent_type;
		$('#takeover_ip_map_list li').remove();
		$('#auto_takeover_target_view').hide();
		$('#autoTakeoverHostTips').hide();
		
		//开启自动接管，并且备机类型为代理客户端，选中的备机为内存操作系统时，阻止操作继续
		if(standbyHostMode == CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT && proxyClient == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){
			isProcessing = true;
			if(isProcessing){
				$('#takeover_config_switch').bootstrapSwitch('state', false); //关闭自动接管开关
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE,LANG.UI_VOL_CDP_AUTO_TAKEOVER_SWITCH_TIPS);
				return false;
			}
			isProcessing = false;
		}

		if(backupMode==CONF.CDP_BACKUP_MODE.REALTIME_SYNC && standbyHostMode ==CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT){
			$('#auto_takeover_target_view').show();
		}

		if($('#takeover_config_switch').is(':checked') || $('#real_sync_takeover_config_switch').is(':checked')) {
			if(_takeoverVolMountPointTab!=undefined){
				clearTable(_takeoverVolMountPointTab,"takeover_vol_target_table");
			}
			if(backupMode==CONF.CDP_BACKUP_MODE.REALTIME_COPY || backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY) {
				$('#autoTakeoverHostTips').show();
			}else{
				$('#autoTakeoverHostTips').hide();
				loadBackukpVolInTakeoverTab();
			}
			loadSHForTakeover();
			if(_standbyMappingConfigList.length>0){  //已配置备机
				$('.takeovercollapseview').addClass("in");
				$('.takeoverconfigveiw').show();
				$('.takeovercollapseview').show();
				$('.autotakeoverconfdiv').removeClass("collapsed");
			}else {
				$('.takeoverconfigveiw').show();
				$(".autotakeoverconfdiv").addClass("collapsed");
			}
			$('#takeover_restore_ip').val('')
			$('#agent_heartbeat_failure_time').val('30')
		}else{
			$('.takeoverconfigveiw').hide();
			$('.takeovercollapseview').removeClass("in");
			$('#takeover_config_view').hide();
		}
	}
	/**
	 * @function 加载自动接管备用服务器
	 * @description 获取并过滤已选择主机uuid，判断是否启用双机镜像。如果启用双机镜像，需要进一步判断是否正确配置备机。
	 * 				获取选中备机IP，自动选中接管服务器对应主机。
	 * @retrun volid
	 */
	var loadSHForTakeover = function (){
		var paramsInfo = {};
		paramsInfo.agentuuid = _agentuuid;
		paramsInfo.standbyuuid = "";
		paramsInfo.master_os_type =  _agentType;
		var params = JSON.stringify(paramsInfo);
		_StandbyHostData = []; //重置获取的备机数据
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getStandbyHostInfo',p:params}, function(d){
			var data = JSON.parse(d);

			var shHostSelect = $('#standbyHostSelect');
			shHostSelect.empty();
			var defaultOption = $("<option value = '0'>"+LANG.UI_VOL_CDP_BACKUP_STANDBY_MACHINE_SELECT+" </option>");
			shHostSelect.append(defaultOption);

			if(!data.length) return;
			_StandbyHostData = data;

			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].text).val(data[i].value)
					.attr('agent-type', data[i].agent_type)
					.attr('data-mountpoint', data[i].mount_info)
					.attr('data-netmode', data[i].net_mode).
					attr('data-onlineflag', data[i].online_flag);
				shHostSelect.append(option);
			}
			if($('#double_host_mirror').is(':checked')) {
				var doubleHostMirrorVal = $('#doubleHostMirrorSelect').val();
				var doubleHostMirrorText = $('#doubleHostMirrorSelect option:selected').text();
				$('#sh_for_takeover').val(doubleHostMirrorVal);
				$('.takeoverhostDes').text(doubleHostMirrorText);
			}
		});

		$('#sh_for_takeover').unbind('change').bind('change', shForTakeoverChange);
	}
	/**
	 * 当接管回切IP获取到焦点时，将接管回切通讯ip的状态设置为false，避免校验成功后又重新选择其他IP
	 */
	var takeoverRestoreIpChange = function(){
		_failbackIpVerify = false;
	}
	/**
	 * @function 校验接管服务ip
	 * @description IP input 失去焦点时判断IP是否合法，并且校验当前输入IP在网络环境内是否可用
	 * @return  true or false
	 */
	var checkTakeoverResIp = function (){
		_failbackIpVerify = false;
		var ip = $("#takeover_restore_ip").val();
		if((!checkIP(ip))&& ip.length>0||ip == ""){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);
			$('#takeover_restore_ip').val('');
			return false;
		}
		var params = {};
		params.ip = ip;
		var params = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'checkIpExists',p:params}, function(data){
			if(data==true){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_MESSAGE);
				$('#takeover_restore_ip').focus();
				$('#takeover_restore_ip').css("border-color","#ff9800")
				_failbackIpVerify = true;  //回切通讯IP是否被占用不用作是否能进行配置任务的必要条件，但需要确保启动回切时对应的IP未被占用；
			}else{
				UIToastr.showSuccess(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_IP_USABLE_MESSAGE);
				_failbackIpVerify = true;
				$('#takeover_restore_ip').css("border-color","#e5e5e5")
			}
			return true;
		})
	}

	/**
	 * @funciton 切换接管备机
	 * @description 切换接管备机，动态变更当前选中备机的描述,处理接管应用开关等事宜
	 * @return volid
	 */
	var shForTakeoverChange = function (){
		var shForTakeoverValue =$('#sh_for_takeover').val(); //接管备机value
		if(shForTakeoverValue==0){
			$('#takeover_ip_map_list li').remove();
			return ;
		}
		Metronic.blockUI({target: "#proxyClientModal",animate: true});

		var data = JSON.stringify({agent_uuid:shForTakeoverValue});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'updateAgentInfo',p:data}, function(d){
			Metronic.unblockUI("#proxyClientModal");
			if(OPREL(d)){
				setTimeout(function(){
					reloadHostVolTargetInfo();
					putTakeoverHostMountpoint();
				},1000);
				loadStandbyNetworkInfo(shForTakeoverValue);  //获取备机网卡信息
			}
			//重置应用故障接管开关
			$('#app_takeover_switch').bootstrapSwitch('state', false);
			$('.apptakeoverconfview').hide();
			//重置自定义脚本配置开关
			$('#script_takeover_switch').bootstrapSwitch('state', false);
			$('.script_takeover_conf_view').hide();
			//重置各种数据
			$('#takeover_restore_ip').val('');
			$('#failback_ip_set_nic').val('');
			parseInt($('#agent_heartbeat_failure_time').val(30));
			$('#takeover_ip_map_list').val('');
			$('#takeover_vol_target_table .row input').val('');
			$('#takeover_vol_target_table .row input').prop('value',LANG.UI_VOL_CDP_BACKUP_SYSTEM_AUTO_ALLOCATE);
			let t = $('#takeover_vol_target_table').dataTable();
			let rows = t.fnGetNodes();
			for (let row of rows) {
				$('#mount_target_' + t.fnGetData(row)[3]).val(LANG.UI_VOL_CDP_BACKUP_SYSTEM_AUTO_ALLOCATE);
			}
			ipMapInfoList = [];
			_takeoverIpMapList = [];
			_takeoverIpMapListView = [];
			$('#takeover_ip_map_list li').remove();
			// var option = $("<option>").text(nicDec).val(nicMac).attr('data-nicname',nicName);
			// nicSelect.append(option);


		});
	}
	//填充接管主机挂载点配置
	var putTakeoverHostMountpoint = function(){
		var shForTakeover = $('#sh_for_takeover option:selected').text();
		// var shForTakeoverValue =$('#sh_for_takeover').val(); //接管备机value
		var standbyHost = _createMsgdata.standby_host;
		$('.takeoverhostDes').text(standbyHost);

		for(var j=0;j<_hostVolInfo.length;j++){
			$('#mount_target_'+j+' option').not('option:first').remove();
		}

		var sdbHostUuid = $('#standbyHostSelect').val();
		var netMode = $("#standbyHostSelect").find("option:selected").attr("data-netmode");
		if(netMode==2){
			networkFlag = true;
		}

		for(var i=0;i<_StandbyHostData.length;i++){
			if(sdbHostUuid ==_StandbyHostData[i].uuid){
				var checkedHostVol= _StandbyHostData[i].vol_info;
				if(checkedHostVol.length==0){
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MOUNT_POINT, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL_MESSAGE);
					return false;
				}else{
					var mountPointVolSelect = $("select[name='takeovermountpoint']");
					for(var j=0;j<checkedHostVol.length;j++){
						var opText = checkedHostVol[j].display_name;
						var option = $("<option>").text(opText).val(checkedHostVol[j].mount_point).attr('data-capacity', checkedHostVol[j].capacity_value);
						mountPointVolSelect.append(option);
					}
				}
			}
		}
		//暂时屏蔽对备机占用卷的取值
		//_standbyHostMountPoint = $("#sh_for_takeover").find("option:selected").attr("data-mountpoint");  //备机已占用的挂载点

		if(standbyHost==0){
			$('#app_takeover_switch').bootstrapSwitch('state', false);
			$('.appmonitorswitchdiv').hide();
			$('.apptakeoverconfview').hide();
		}
	}

	/**
	 *初始化自定义脚本模块窗口
	 */
	var loadTakeoverScriptModal = function (){
		$('#addTakeoverScriptBut').on('click', function(){ //初始化添加自定义监控脚本模态框
			$('#takeoverScriptModal').modal({'width':'800px', 'height':'380px'});
			//重置脚本配置信息；
			$('#takeoverScriptType').val(0);
			$('.execsequenceview').hide();
			$('.execintervalview').hide();
			$('.execfailureview').hide();
			$('#takeoverScriptPath').val('');
			$('#takeoverScriptType').unbind('change').bind('change', takeoverScriptChange);
			$('#submitTakeoverScript').unbind('click').click(submitTakeoverScript);
		});
	}
	/**
	 * 切换接管脚本类型
	 */
	var takeoverScriptChange = function (){
		$("#scriptExecuteFormer").prop("checked","checked");
		$.uniform.update();  //更新uniform
		var scriptType = $('#takeoverScriptType').val();
		switch(Number(scriptType)){
			case 1:
				$('.execsequenceview').show();
				$('.execintervalview').hide();
				$('.execfailureview').hide();
				break;
			case 2:
				$('.execsequenceview').hide();
				$('.execintervalview').show();
				$('.execfailureview').show();
				break;
			default:
				$('.execsequenceview').hide();
				$('.execintervalview').hide();
				$('.execfailureview').hide();
				break;
		}
	}
	/**
	 * 提交配置的脚本信息
	 */
	var submitTakeoverScript = function(){
		var scriptInfo = {};
		var scriptType = $('#takeoverScriptType').val();
		var takeoverScriptTypeStr = $('#takeoverScriptType').text();
		var scriptExecuteOrder = $('input:radio[name="scriptExecuteOrder"]:checked').val();  //执行顺序
		var scriptExecuteOrderStr = "";
		switch(Number(scriptExecuteOrder)){
			case 1:
				scriptExecuteOrderStr = LANG.UI_VOL_CDP_BACKUP_EXECUTE_BEFORE_TAKEOVER;
				break;
			case 2:
				scriptExecuteOrderStr = LANG.UI_VOL_CDP_BACKUP_EXECUTE_AFTER_TAKEOVER;
				break;
		}

		var takeoverScriptPath = $('#takeoverScriptPath').val();  //自定义脚本路径
		var takeoverScriptInterval = $('#takeover_script_interval').val();  //执行间隔,单位秒
		var scriptFailureNumber = $('#script_failure_number').val(); //累计失败次数

		if(scriptType == 0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE);
			return false;
		}
		if(takeoverScriptPath ==""){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE2);
			return false;
		}else {
			if(!checkFilePath(takeoverScriptPath,_optionType)){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
				return false;
			}
		}
		if(takeoverScriptInterval ==0 && scriptType ==2){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE3);
			return false;
		}
		if(scriptFailureNumber ==0 && scriptType ==2){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE4);
			return false;
		}

		var execInterval = 0;
		if(scriptType==2){
			execInterval = takeoverScriptInterval;
		}
		var triggerFailNum =0;
		if(scriptType ==2){
			triggerFailNum = scriptFailureNumber;
		}
		var liId = getUuid();

		var des = LANG.UI_VOL_CDP_BACKUP_SCRIPT_TYPE + ":&nbsp;";
		var execType = 0;
		if(scriptType ==1){  //接管执行脚本
			execType = scriptExecuteOrder;
			des+=LANG.UI_VOL_CDP_BACKUP_TAKEOVER_EXECUTE_SCRIPT+"("+scriptExecuteOrderStr+");&nbsp;";
		}else if(scriptType==2){ //接管监测脚本
			execType = 3;
			des+=LANG.UI_VOL_CDP_BACKUP_TAKEOVER_MONITOR_SCRIPT+"("+LANG.UI_VOL_CDP_BACKUP_EXECUTE_INTERVAL+":"+takeoverScriptInterval+LANG.UI_PUBLIC_SECOND+","+LANG.UI_VOL_CDP_BACKUP_ALL_SIZE+":"+scriptFailureNumber+LANG.UI_VOL_CDP_BACKUP_TIMES+");&nbsp;";
		}
		des+=LANG.UI_VOL_CDP_JOB_DETAILS_SCRIPT_PATH+"&nbsp;"+takeoverScriptPath;

		scriptInfo.script_type = Number(scriptType);
		scriptInfo.exec_type = Number(execType);
		scriptInfo.exec_interval = Number(execInterval);
		scriptInfo.trigger_fail_num = Number(triggerFailNum);
		scriptInfo.script_path = takeoverScriptPath;
		scriptInfo.des = des;
		scriptInfo.uuid = liId;
		_scriptSet.push(scriptInfo);

		var des = '';
		des +=
			'<li class="list-group-item popovers scriptTips list-group-item__speed" id="script'+ liId +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ scriptInfo.des + '">' +
			'<div class="col1">' +
			'<div class="cont ">' +
			'<div class="cont-col1"></div>' +
			'<div class="cont-col2">' +
			'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + scriptInfo.des + '</div>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'<div class="col2  pull-right delete-list">' +
			'<a class="del'+ liId +'" >' +
			'<div class="label label-sm label-danger" style="padding:0;">' +
			'<i class="viconfont vicon-cuowu"></i>' +
			'</div>' +
			'</a>' +
			'</div>' +
			'</li>';
		$('#takeoverScriptList').append(des);
		$('.scriptTips').popover();	   //初始化tips
		$('#takeoverScriptModal').modal('hide');

		//移除已添加脚本信息，需要移除popover和_scriptSet中对应的配置信息
		$('.del'+ liId).on('click', function(){
			$('.popover.in').remove();
			$('#script' + liId).remove();
			for(var i=0;i<_scriptSet.length; i++){
				if(liId == _scriptSet[i].uuid){
					_scriptSet.splice($.inArray(_scriptSet[i],_scriptSet),1);
				}
			}
		});
	}
	/**
	 * @function 自定义脚本配置开关change
	 * @desc 判断是否启用自定义脚本配置，启用需要进行登录密码校验。
	 */
	var scriptTakeoverChnage = function (){
		_scriptSet = [];
		$('.scriptTips').remove();
		if(this.checked && !_userIsVerify){
			verifyTakeoverBasicsConf();
			loadTakeoverScriptModal();
		}else{
			_userIsVerify = false;
			$('.script_takeover_conf_view').hide();

		}
	}

	/**
	 * 初始化接管網絡配置
	 */
	var takeoverNetworkConfModal = function(){
		// var shForTakeoverValue = $('#sh_for_takeover').val(); //接管备机value
		var shForTakeoverValue = _createMsgdata.standby_target_agent_uuid; //接管备机value
		var standbyHostUuid = shForTakeoverValue;
		if(shForTakeoverValue==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION, LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION_TIPS);
			$('#script_takeover_switch').bootstrapSwitch('state', false);
			$('.script_takeover_conf_view').hide();
			return false;
		}

		$('#takeoverNetworkConfModal').modal({'width':'800px', 'height':'380px'});
		$('#host_ip_server_conf').takeoverIpServerMapConfig({
			agentNetwork:_agentNetworkInfo,
			standbyNetwork: _standbyNetworkInfo,
			takeoverHisNetwork:[],
			failbackHisNetwork:[],
		});
		$('#standby_gateway_conf').takeoverStandbyGatewayConfig({
			agentNetwork:_agentNetworkInfo,
			standbyNetwork: _standbyNetworkInfo,
			takeoverHisNetwork:[],
			failbackHisNetwork:[],
		});

		$("#submit_host_network_conf").unbind('click').click(submitHostNetworkConf);
	}
	/**
	 * 校验网关格式
	 */
	var checkIpFormat = function (){
		var ipVerify = true;
		for(var i=0;i<_standbyGatewayConf.length;i++){
			var gateway = _standbyGatewayConf[i].gateway;
			if(gateway=="" && !ipV4V6(gateway)){
				ipVerify = false;
				break;
			}
		}
		return ipVerify;
	}
	/**
	 * 获取配置网卡描述信息，并渲染配置
	 */
	var submitHostNetworkConf = function() {
		var networCardConfInfo = {};
		var gateAwyConfDesList = [];

		_standbyGatewayConf = getStandbyGateWayConf();
		var checkIp = checkIpFormat(_standbyGatewayConf);
		if (!checkIp) {
			_takeoverIpMapList = [];
			UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
			return false;
		}

		var standbyNetCardConfList = standbyNetCardConf();
		var ipMapInfoList = [];
		_takeoverIpMapListView = [];
		var selectedNetCards = 0;  // 新增变量，用于跟踪选中的网卡数量

		for (var i = 0; i < _agentNetworkInfo.length; i++) {
			var ipSetStr = "";
			var ipMapInfo = {};
			var nicIpList = [];
			var agentMapInfo = {};
			ipMapInfo.nic_gateway = _agentNetworkInfo[i].gateway_address;
			ipMapInfo.nic_name = _agentNetworkInfo[i].name;
			ipMapInfo.nic_mac = _agentNetworkInfo[i].mac_address;

			var ipSet = _agentNetworkInfo[i].ip_set;
			if (!ipSet) {
				break;
			}

			var liId = getUuid();
			var hostMac = $("#host_network_mac_" + i).text();
			//当前选中主机网卡名
			var activeNetCardText = $('#hostNetCardTab').find('li.active').filter(function () {
				return $(this).closest('ul').is('ul');
			}).text();

			for (var j = 0; j < ipSet.length; j++) {
				var nicIpInfo = {};
				var standbyNetCardSelect = "standby_network_card_" + i + j;
				var targetHostMacStr = $("#" + standbyNetCardSelect).find("option:selected").attr("host-mac");
				var targetHostMac = $("#" + standbyNetCardSelect).val();

				var hostIp = $("#" + standbyNetCardSelect).find("option:selected").attr("host-ipdrr");
				var netMask = $("#" + standbyNetCardSelect).find("option:selected").attr("agent-netmask");
				var targetGateway = $("#" + standbyNetCardSelect).find("option:selected").attr("standby-gateway");
				var targetNicName = $("#" + standbyNetCardSelect).find("option:selected").text();

				if (typeof (netMask) == 'undefined' && typeof (targetGateway) == "undefined") {
					continue;
				}

				if (hostIp) {
					selectedNetCards++;  // 增加选中的网卡数量
					nicIpInfo.ip = hostIp;
					nicIpInfo.netmask = netMask;
					nicIpInfo.target_nic_name = targetNicName;
					nicIpInfo.target_nic_mac = targetHostMac;
					standbyNetCardConfList.some(item => {
						if (item.selectNicname == targetNicName) {
							targetGateway = item.selectGateway;
						}
					});
					var targetGatewayStr = targetGateway || "--";
					nicIpInfo.target_gateway = targetGateway;
					nicIpList.push(nicIpInfo);
					var gatewayStr = "(" + LANG.UI_DRILLS_NETWORK_WAY + ":" + targetGatewayStr + ")";
					ipSetStr += hostIp + gatewayStr + LANG.UI_VOL_CDP_HOST_IP_MAP_TO_STANDBY + ":" + targetNicName + "  ";
				}
			}

			if (nicIpList.length > 0) {
				ipMapInfo.nic_ip_info = nicIpList;
				agentMapInfo.agent_map_info = ipMapInfo;
				agentMapInfo.conf_id = liId;
				agentMapInfo.conf_des = LANG.UI_VOL_CDP_HOST_NETCARD + ": " + _agentNetworkInfo[i].name + " [" + ipSetStr + "] ";
				ipMapInfoList.push(agentMapInfo);
				_takeoverIpMapListView.push(agentMapInfo);
			}
		}

		if (selectedNetCards == 0) {  // 如果没有选中任何网卡，弹出提示
			UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_CHANGE_MAP_NETCARD)
			return;
		}

		if (ipMapInfoList.length > 0) {
			_takeoverIpMapList = ipMapInfoList;
			$('#takeover_ip_map_list li').remove();
			hostGatewayConfDes();
		}

		$('#takeoverNetworkConfModal').modal('hide');
	}
	/**
	 * 获取配置网卡描述信息，并渲染
	 */
	var hostGatewayConfDes = function(){
		var des = "";
		for(var i=0;i<_takeoverIpMapList.length;i++){
			var conf_id = _takeoverIpMapList[i].conf_id;
			var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ _takeoverIpMapList[i].conf_id
				+'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
				+ _takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
				+ _takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  _takeoverIpMapList[i].conf_id +'" >'
				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
			$('#takeover_ip_map_list').append(des);
			$('.takeoverNetcardTips').popover();	   //初始化tips

			$('.takeover_netcard_del'+ conf_id ).bind("click",{index:i},clickHandler);

		}
	}
	/**
	 * 为每一个<li>绑定点击事件，并处理移除数组操作
	 */
	var clickHandler = function(event) {
		debugger;
		$('.popover.in').remove();
		var index = event.data.index;
		var confId;
		var list = _takeoverIpMapListView;

		// 修正删除最后一个元素的问题
		if (index >= list.length) {
			index = list.length - 1;
		}

		// 查找并删除 _takeoverIpMapListView 中的元素
		if (index >= 0) {
			confId = list[index].conf_id;
			$('#takeover_netcard_' + confId).remove();
			_takeoverIpMapListView.splice(index, 1);
		}

		// 确保 confId 已经被正确获取
		if (confId) {
			// 查找并删除 ipMapInfoList 中的元素
			ipMapInfoList = ipMapInfoList.filter(function(item) {
				return item.conf_id !== confId;
			});

			// 查找并删除 _takeoverIpMapList 中的元素
			_takeoverIpMapList = _takeoverIpMapList.filter(function(item) {
				return item.conf_id !== confId;
			});
		}
	}
	/**
	 * 获取备机网卡配置
	 */
	var getStandbyGateWayConf = function(){
		var list = [];
		for(var i = 0; i<_standbyNetworkInfo.length;i++){
			var gatewayInfo = {};
			var hostMac = _standbyNetworkInfo[i].mac_address
			hostMac = hostMac.replace(/:/g,'');
			gatewayInfo.netcard = _standbyNetworkInfo[i].name;
			var hostGateway = $('#standby_netcard_'+hostMac).val();
			if(hostGateway==""){
				hostGateway = "0.0.0.0";
			}
			gatewayInfo.gateway = hostGateway;
			list.push(gatewayInfo);
		}
		return list;
	}

	//获取备机网卡信息，用于替换主机映射网卡中的备机网关
	var standbyNetCardConf = function(){
		var standbyNetCardInfo = {};
		var standbyNetCardList = [];
		for(var i = 0;i<_standbyNetworkInfo.length;i++){
			var standbyGatewaySelectId = "standby_netcard_macaddress_"+_standbyNetworkInfo[i].name;
			var selectNicname = $("#"+standbyGatewaySelectId).find("option:selected").attr("agent-nicname");
			var selectGateway = $("#"+standbyGatewaySelectId).val();
			standbyNetCardInfo.selectNicname = selectNicname;
			standbyNetCardInfo.selectGateway = selectGateway;
			standbyNetCardList.push(standbyNetCardInfo);
		}
		return standbyNetCardList;
	}


	/**
	 * 校验是否配置接管备机
	 */
	var verifyTakeoverBasicsConf = function(){
		var shForTakeoverValue =$('#sh_for_takeover').val(); //接管备机value
		var standbyHostUuid = shForTakeoverValue;
		var standbyHostName = $('#sh_for_takeover option:selected').text();
		if(shForTakeoverValue==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE5);
			$('#script_takeover_switch').bootstrapSwitch('state', false);
			$('.script_takeover_conf_view').hide();
			return;
		}
		getUserPassword(); //获取系统登录密码
		if(!_userIsVerify){
			initErrorFlag = false;
			bootbox.prompt({
				title: LANG.UI_VOL_CDP_BACKUP_INPUT_PASSWORD_TIPS,
				inputType: 'password',
				callback: function (result) {
					if(result == null) return;
					if(hex_md5(result) == _UserPassword){
						_userIsVerify = true;
						$('#script_takeover_switch').bootstrapSwitch('state', true);
						$('.script_takeover_conf_view').show();

					}else{
						$('.bootbox-input').css('border-color', "#a94442");
						if(!initErrorFlag){
							var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
							$('.bootbox-input').after(des);
							initErrorFlag = true;
						}
						$('#script_takeover_switch').bootstrapSwitch('state', false);
						$('.script_takeover_conf_view').hide();
						return false;
					}
				}
			});
			$('#script_takeover_switch').bootstrapSwitch('state', false);
			$('.script_takeover_conf_view').hide();
		}


	}
	/**
	 * @function 应用故障接管change
	 * @description 判断是否启用应用故障接管，启用进行相关配置
	 */
	var appTakeoverChnage = function (){
		if(this.checked){
			verifyTakeoverShConf();
			$('#app_monitor_switch').bootstrapSwitch('state', false);
		}else{
			$('.appmonitorswitchdiv').hide();
			$('.apptakeoverconfview').hide();
		}
	}
	/**
	 * 应用故障监测change
	 */
	var appMonitorSwitch = function(){
		if(this.checked){
			$('.apptakeoverconfview').show();
			$('.takeoverappintervalview').show();
			$('.appfailurenumberview').show();
			$('#app_failure_number').val(3)
			$('#takeover_app_interval').val(30)
		}else{
			$('.takeoverappintervalview').hide();
			$('.appfailurenumberview').hide();
		}
	}
	/**
	 * @function 校验接管备机配置
	 * @description 判断接管备机是否配置，未配置接管进行相关提示，并且重新切换接管应用到关闭状态
	 */
	var verifyTakeoverShConf = function (){
		//var shForTakeoverValue =$('#standbyHostSelect').val(); //接管备机value
		var shForTakeoverValue = _createMsgdata.standby_target_agent_uuid;  //接管备机value
		var standbyHostUuid = shForTakeoverValue;
		var standbyHostName = $('#standbyHostSelect option:selected').text();
        var standbyHostMode = $('#standby_host_mode').val();
		if(shForTakeoverValue==0 && standbyHostMode == CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING_TIPS1);
			$('#app_takeover_switch').bootstrapSwitch('state', false);
			$('.apptakeoverconfview').hide();
			return;
		}
		if(_hostAppInfo.length==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING_TIPS2);
			$('#app_takeover_switch').bootstrapSwitch('state', false);
			$('.apptakeoverconfview').hide();
			return;
		}
		var paramsInfo = {};
		paramsInfo.agentuuid = _agentuuid;
		paramsInfo.applist = _hostAppInfo;
		paramsInfo.agentip = _agentIP;
		paramsInfo.agentname =_agentName;

		if(standbyHostMode== CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){   //备机为容灾演练平台
			paramsInfo.standbyHostUuid = _agentuuid;  //容灾演练平台，应用主机信息，确保流程保持一致
			paramsInfo.standbyHostName = _agentName; //容灾演练平台，应用主机信息，确保流程保持一致
		}else{
			paramsInfo.standbyHostUuid = standbyHostUuid;
			paramsInfo.standbyHostName = standbyHostName;
		}
		paramsInfo.hostSelectedAppType = _selectAppType;//主机选择监控的应用类型

		var p = JSON.stringify(paramsInfo);
		Metronic.blockUI({target: "#takeover_app_tree_div",animate: true});  // 能够为页面上的任意元素添加遮层,阻止用户操作
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP, f: 'getHostConfiguredAppTree', p: p}, setHostConfAppTree);
	}
	//初始化自动接管应用tree
	var setHostConfAppTree = function(zNodes){
		if(zNodes=='[]'){
			$('#app_takeover_switch').bootstrapSwitch('state', false);
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_WARNING, LANG.UI_VOL_CDP_TAKEOVER_CLIENT_APP_CONFIG_MESSAGE);
			return;
		}
		$('.appmonitorswitchdiv').show();
		Metronic.unblockUI("#takeover_app_tree_div");
		var setting = {
			check: {
				enable: true,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					rootPId: 0
				},key: {
					title: "title"
				}
			},
			view: {
				fontCss: getFontCss,
			},
			callback: {
				beforeClick: nodeSelect,
				onCheck: appOnCheck
			}
		};
		var nodes = JSON.parse(zNodes);//拿到备份节点
		var setAppTree = $.fn.zTree.init($("#takeover_app_infotree"), setting, nodes);
	}
	/**
	 * @descript 检验故障监测间隔,判断输入是否合法，还原默认值并给出提示.
	 */
	var takeoverAppIntervalChange = function (){
		var num = this.value;
		if(num > 3600 || num < 5 || num == ""){
			$('#takeover_app_interval').val(30);
			$('.takeover_app_interval_div').spinner({value:30, step: 1, min:5, max: 3600});
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TAKEOVER_APP_INTERVAL, LANG.UI_VOL_CDP_BACKUP_TAKEOVER_APP_INTERVAL_TIPS);
		}
	}
	var heartBeatspinnerUpChange = function(){
		$heartBeatValue= $('#takeover_app_interval').val();
		if($heartBeatValue<5){
			$('#takeover_app_interval').val(30);
		}
	}

	/**
	 * @function 校验故障监测输入
	 * @descript 校验输入是否在允许范围内，超出范围还原默认值并给出提示
	 */
	var appFailureNumberChange = function (){
		var num = this.value;
		if(num > 100 || num < 1 || num == ""){
			$('#app_failure_number').val(3);
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_FAILURE_NUMBER_SET, LANG.UI_VOL_CDP_BACKUP_FAILURE_NUMBERS_TIPS);
			return false;
		}
	}
	/**
	 * @function 检验心跳最大有效时间
	 * @descript 校验输入是否在运行范围内，超出范围还原成默认值并给出提示
	 */
	var agentHeartbeatFailureNumber = function(){
		var num = this.value;
		if(num > 3600 || num < 5 || num == ""){
			$('#agent_heartbeat_failure_time').val(30);
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_HEARTBEAT_FAILURE_TIME, LANG.UI_VOL_CDP_BACKUP_HEARTBEAT_FAILURE_TIME_MESSAGE);
			return false;
		}
	}
	/**
	 * @funciton 添加标签策略
	 * @return tagpointList
	 */
	var tagPointStrategySubmit = function (){
		var info = {};
		var des = '';
		var strategyConfig = $('#tagpointstrategy').getTagPointStrategyConfig();
		info.mode = strategyConfig.tagInfo.mode;
		info.type = strategyConfig.tagInfo.type;
		info.startTime = strategyConfig.tagInfo.startTime;
		info.rollFlag = strategyConfig.tagInfo.rollFlag;
		info.rollInterval = strategyConfig.tagInfo.rollInterval;
		info.endTime = strategyConfig.tagInfo.endTime;
		var liId = getUuid();
		info.uuid = liId;
		info.days = strategyConfig.tagInfo.days;
		info.des = strategyConfig.tagInfo.des;
		des +=
			'<li class="list-group-item popovers tagTips list-group-item__speed" id="tag'+ liId +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ info.des + '">' +
			'<div class="col1">' +
			'<div class="cont ">' +
			'<div class="cont-col1"></div>' +
			'<div class="cont-col2">' +
			'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'<div class="col2  pull-right delete-list">' +
			'<a class="del'+ liId +'" >' +
			'<div class="label label-sm label-danger" style="padding:0;">' +
			'<i class="viconfont vicon-cuowu"></i>' +
			'</div>' +
			'</a>' +
			'</div>' +
			'</li>';
		$('#tagpoint_list').append(des);
		$('.tagTips').popover();	   //初始化tips

		$('.del'+ liId).on('click', function(){
			$('.popover.in').remove();
			$('#tag' + liId).remove();
			for(var i=0;i<tagList.length; i++){
				if(liId == tagList[i].uuid){
					tagList.splice($.inArray(tagList[i],tagList),1);
				}
			}
			addTagStrategyDes();
		});
		tagList.push(info);
		$('#tagPointLimitModal').modal('hide');
		addTagStrategyDes();

	}
	/**
	 * 校验输入的传输策略策略是否符合规则
	 */
	var checkTransferThreadNum = function(){
		var transferThreadNumber = $("#transfer_thread_number").val();
		if(transferThreadNumber>4){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE1);
			$("#transfer_thread_number").val(1);
			return false;
		}
		if(transferThreadNumber<1){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
			$("#transfer_thread_number").val(1);
			return false;
		}
		return true;
	}

	/**
	 * @function 添加并展示标签策略描述信息
	 */
	var addTagStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if(tagList.length !=0){
			des += LANG.UI_VOL_CDP_JOB_DETAILS_LABEL_STRATEGY_NUM + ": " + tagList.length;
		}
		for(var i=0;i<tagList.length;i++){
			if(i>0){
				titleDes += tagList[i].des + '.  ';
			}else{
				titleDes += tagList[i].des + ". ";
			}
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].speedlimit.des;
			initStrategyDesStyle($('.speedlimitDes'), titleDes, oldDes);
		}else{
			$('.speedlimitDes').removeClass('font-green-seagreen');
		}
		$('.backupTimeDes').html(des);
		$('.backupTimeDes').prop('title', titleDes);



	}
	/**
	 * @function 填充标签策略默认值
	 */
	var initTagPointStrategy = function(){
		var strategy = [];
		strategy[0] = {
			mode: 7, //定义为标签点策略
			strategy_type: 2,
			days: [0, 0, 0, 0, 1, 0, 0],
			start_time: '23:00:00',
			end_time: '23:30:00',
			roll_interval:'01:00:00',
		};
		//延迟设置,因为这里icheck会默认修改里面的选中事件
		$('#tagpointstrategy').tagpointstrategy({config: strategy});
		initTagPointStrategyFlag = true;
	}


	/**
	 * 填充限速策略默认值
	 */
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
	 * @function 切换限速模式
	 */
	var speedModeHandler = function(){
		if(this.value == 2){
			$('.setSpeedStrategy').hide();
		}else{
			$('.setSpeedStrategy').show();
		}
	}

	/**
	 * @function 添加限速策略
	 * @return speedList;
	 */
	var speedSubmit = function(){
		var info = {};
		var des = '';
		info.mode = $('#speedModeType').val();
		var speedUnit = getUnit("unit");
		var speedNum = parseInt($('#speedSpinnerNumInput').val());
		if(!speedNum || speedNum<= 0){
			UIToastr.showWarning(LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
			return false;
		}
		var unit = $('#unit').find('option:selected').text();
		var liId = getUuid();
		info.uuid = liId;
		info.value = speedNum* speedUnit;
		info.speednum = speedNum;
		info.unit = unit;

		if(info.mode == 1){
			var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
			info.type = strategyConfig.speedInfo.type;
			info.startTime = strategyConfig.speedInfo.startTime;
			info.endTime = strategyConfig.speedInfo.endTime;
			info.days = strategyConfig.speedInfo.days;
			info.des = strategyConfig.speedInfo.des + ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + speedNum + unit;
			des +=
				'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ liId +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'+ info.des + '">' +
				'<div class="col1">' +
				'<div class="cont ">' +
				'<div class="cont-col1"></div>' +
				'<div class="cont-col2">' +
				'<div class="desc list-one" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'<div class="col2  pull-right delete-list">' +
				'<a class="del'+ liId +'" >' +
				'<div class="label label-sm label-danger" style="padding:0;">' +
				'<i class="viconfont vicon-cuowu"></i>' +
				'</div>' +
				'</a>' +
				'</div>' +
				'</li>';
		}else{
			if(!checkSimpleForever(info.mode)){
				UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_FOREVER_TIPS);
				return false;
			}
			info.type = 4;
			info.startTime = '';
			info.endTime = '';
			info.days = [];
			info.des = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER+', '+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE+':' + speedNum + unit;
			des +=
				'<li class="list-group-item popovers speedTips list-group-item__speed" id="speed'+ liId+'"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' +info.des + '">' +
				'<div class="col1">' +
				'<div class="cont ">' +
				'<div class="cont-col1"></div>' +
				'<div class="cont-col2">' +
				'<div class="desc list-one"> '+ info.des +  '</div>' +
				'</div>' +
				'</div>' +
				'</div>' +
				'<div class="col2  pull-right delete-list">' +
				'<a class="del'+ liId +'" >' +
				'<div class="label label-sm label-danger" style="padding:0;">' +
				'<i class="viconfont vicon-cuowu"></i>' +
				'</div>' +
				'</a>' +
				'</div>' +
				'</li>';
		}
		$('#speedList').append(des);
		$('.speedTips').popover();	   //初始化tips
		$('.del'+ liId).on('click', function(){
			$('.popover.in').remove();
			$('#speed' + liId).remove();
			for(var i=0;i<speedList.length; i++){
				if(liId == speedList[i].uuid){
					speedList.splice($.inArray(speedList[i],speedList),1);
				}
			}
			addSpeedStrategyDes();
		});
		speedList.push(info);
		$('#speedlimitModal').modal('hide');
		addSpeedStrategyDes();
	}

	var checkSimpleForever = function(mode){
		for(var i=0;i<speedList.length;i++){
			if(mode == speedList[i].mode){
				return false;
			}
		}
		return true;
	}
	/**
	 * @function 添加并展示限速策略描述信息
	 */
	var addSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if(speedList.length !=0){
			des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
		}
		for(var i=0;i<speedList.length;i++){
			titleDes += speedList[i].des + '. ';
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].speedlimit.des;
			initStrategyDesStyle($('.speedlimitDes'), titleDes, oldDes);
		}else{
			$('.speedlimitDes').removeClass('font-green-seagreen');
		}
		$('.speedlimitDes').html(des);
		$('.speedlimitDes').prop('title', titleDes);
	}

	/**
	 * @function 初始化存储策略描述
	 */
	var initStoreStrategyDes = function(){
		var des = "";
		var diffDes = "";
		/** 卷CDP目前暂时不支持重复数据删除，此处解析暂时保留；
		 if(authFun.length != 0 && authFun.dedupication){
			  des += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationCheck').get(0).checked) + ", ";
		        diffDes += LANG.UI_GLOBAL_STRATEGY_DEDUPULICATION + ": " + getSwitchDes($('#deduplicationCheck').get(0).checked) + "<br>";
		}
		 */
		des += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
		diffDes += LANG.UI_GLOBAL_STRATEGY_COMPRESS + ": " + getSwitchDes($('#compressCheck').get(0).checked);
		
		//除了hyper-v，其余虚拟化都有数据加密
		des += ","+LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);
		diffDes += LANG.UI_BACKUP_DATA_ENCRYPT + ": " + getSwitchDes($('#encryptStorageCheck').get(0).checked);

        // 存储加密算法
        // if($('#encryptStorageCheck').get(0).checked){
        //     var encryptedMethodLabel = $('.storage-encrypt-label').html();
        //     let method = $('#storageEncryptMethod').val();
        //     var grade = '';
        //     switch (parseInt(method)) {
        //         case 1: 
        //             grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_AES;
        //             break;
        //         case 2: 
        //             grade = LANG.UI_GLOBAL_STRATEGY_STORAGE_ENCRYPT_SM;
        //             break;
        //     };
        //     des += "," + encryptedMethodLabel + ": " + grade;
        //     diffDes += "," + encryptedMethodLabel + ": " + grade;
        // }

		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].store.des;
			initStrategyDesStyle($('.storeDes'), diffDes, oldDes);
		}else{
			$('.storeDes').removeClass('font-green-seagreen');
		}
		$('.storeDes').html(des);
		$('.storeDes').prop('title', des);
	}
	/**
	 * 切换自动选择存储加密密码
	 */
	var passwordModeChange = function(){
		if(this.checked){
			$('#password').val('');
			$('#repassword').val('');
			$('.passwordDiv').hide();
		}else{
			$('.passwordDiv').show();
		}
	}
	/**
	 * 存储加密切换
	 */
	var encryptChange = function(){
		if(this.checked){
			$('#passwordAutocheck').bootstrapSwitch('state', true);
			$('#password').empty();
			$('#repassword').empty();
			$('.passwordModeDiv').show();

		}else{
			$('#passwordAutocheck').bootstrapSwitch('state', false);
			$('.passwordModeDiv').hide();
			$('.passwordDiv').hide();
		}
	}
	/**
	 * @function 内存缓存change
	 * @description 判断是否启用内存缓存配置，默认内存缓存大小512 MB
	 */
	var memoryCacheChnage = function (){
		if(this.checked){
			$('.set_memory_cache_div').show();

		}else{
			$('.set_memory_cache_div').hide();
		}
	}

	//初始化当前用户密码用于删除二次确认
	var getUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
	/**
	 * @function 创建任务step
	 * @description 校验step1数据合法性，封装备份数据源
	 * @return true or false
	 */
	var step1Valid = function(){
		if(!_createMsgdata.agentUUID){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT_SELECT, LANG.UI_VOL_CDP_BACKUP_CLIENT_SELECT_TIPS);
			return false;
		}
		var selectNodes = grid.getSelectedRows();
		if(selectNodes.length==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_VOL_SELECT, LANG.UI_VOL_CDP_BACKUP_VOL_SELECT_TIPS);
			return false;
		}
		if(!!_inTask || !!_isHaStandby || !!_isRecoveryTarget || !!_isFbTarget || !!_isTakeoverStandby){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CLIENT, _existTtaskName+LANG.UI_VOL_CDP_BACKUP_NO_DUPLICATE_CONFIGURE);
			return false;
		}

		var agentNodes = zTreeAgent.getCheckedNodes();
		_createMsgdata.master_agent_uuid = _createMsgdata.agentUUID;
		var volData = grid.getDataTable().data();
		_hostVolInfo = [];//置空配置卷uuid
		ipMapInfoList = []; //置空接管主机IP映射

		_takeoverIpMapListView = []; //置空接管主机IP映射view
		$('#takeover_ip_map_list div').remove();  //清空网络配置view
		var backup_object = {};
		var vol_uuid_set = [];
		var volTypeArray = [];
		_allSysVolInfoArray = [];  //选中客户所有的系统和引导分区卷集合
		_checkedVolInfo = [];  //选中需要备份的所有系统和引导分区卷集合
		_volIsUefi = false; //选中卷是否包含UIEF
		for(var i=0;i<volData.length;i++){
			if(volData[i][10] ==true || volData[i][11] == true){
				_allSysVolInfoArray.push(volData[i][7]);
			}
			for(var j=0;j<selectNodes.length;j++){
				if(selectNodes[j] == volData[i][5]){
					var info = {
						volSize: volData[i][3],
						volMountPath: volData[i][2],
						volUuid: volData[i][5],
						volSizeValue: volData[i][6],
						displayName: volData[i][7],
						osType:volData[i][8]
					};
					//判断是否为boot 分区或系统分区
					if(volData[i][11]== true || volData[i][10] == true){ 
						_checkedVolInfo.push(volData[i][7]);
					}

					if(volData[i][13]== true){
						_volIsUefi = true;
					}
					volTypeArray.push(volData[i][10]);
					vol_uuid_set.push(volData[i][5]);
					_hostVolInfo.push(info);//监控卷详细信息
				}
			}
		}

		backup_object.vol_uuid_set = vol_uuid_set;
		_createMsgdata.backup_object = backup_object; //封装配置的卷uuid 
		//获取以选择的应用信息
		_hostAppInfo = [];//置空配置应用uuid
		var appTree = $.fn.zTree.getZTreeObj("cdp_app_tree");
		var checkedNodes = appTree.getCheckedNodes(true);
		var confAppInfo = [];
		if(checkedNodes.length>0){
			for(var i=0;i<checkedNodes.length;i++){
				if(checkedNodes[i].isApp==true){
					confAppInfo.push( checkedNodes[i].name);
					_hostAppInfo.push(checkedNodes[i].uuid);
					_selectAppType = checkedNodes[i].app_type_value;
				}
			}
		}
		_createMsgdata.appTypeName = confAppInfo;
		_createMsgdata.backup_object.monitor_app_uuid_set = _hostAppInfo; //封装已配置应用uuid
		_createMsgdata.backup_object.host_ha_standby_agent_uuid = "";  //本地主机高可用，暂时不支持

		restDoubleHostMirror();  //重置双机镜像配置
		restTakeoverConf();  //重置接管配置
		resetBackupDestination();  //重置备份目的地配置
		showStep1();  //以完成Step1相关数据的获取的封装，需要预加载Step2的相关默认配置
		delStandbyHostConf();
		return true;
	}

	/**
	 * step1校验结束，加载存储信息
	 */
	var showStep1 = function(){
		//重置第二步配置信息
		// $('.bkimagedatatoserver').hide();
		$('#standby_host_mode option:eq(2)').prop('disabled', false);  
		$('#standby_host_mode option:eq(3)').prop('disabled', false);
		$("#bk_imagedata_to_server").bootstrapSwitch('state', true);
		// if(_autoTakeoverAuth) {
		// 	$('.realsyncautotakeoverdiv').show();
		// }
		if(nodeSelectFlag) return; //加载一次
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
			var data = JSON.parse(d);
			var softselect = $('#selectnode');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
			nodeSelectFlag = true;
			initStorageSelect();
		});
		$('#selectnode').on('change', nodeselectChange);
		resetRebuildPartSwitch();
	}
	//选择存储节点
	var nodeselectChange = function (){
		var node_uuid = $('#selectnode').val();
		initStorageSelect();
	};
	/**
	 * 步骤2的相关校验
	 */
	var step2Verify = function (){
		var verifyResult = true;
		var nodeUuid = $("#selectnode").val();//目标节点uuid
		var storageUuid =$("#selectstorage").val(); //目标存储uuid
		if(!nodeUuid){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_BACKUP_SELECT_NODE);
			verifyResult =  false;
		}
		if(!storageUuid){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_BACKUP_SELECT_STORAGE);
			verifyResult =  false;
		}

		let agentHeartbeatFailureNum = $("#agent_heartbeat_failure_time").val();  //心跳间隔时间
		if(agentHeartbeatFailureNum > 3600 || agentHeartbeatFailureNum < 5 || agentHeartbeatFailureNum == ""){
			$('#agent_heartbeat_failure_time').val(5);
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_HEARTBEAT_FAILURE_TIME, LANG.UI_VOL_CDP_BACKUP_HEARTBEAT_FAILURE_TIME_MESSAGE);
			verifyResult =  false;
		}
		if(($('#takeover_config_switch').is(':checked') || $('#real_sync_takeover_config_switch').is(':checked'))
			&& _createMsgdata.standby_target_agent_uuid == 0) {
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE_TIPS2);
			verifyResult = false;
		}
		return verifyResult;
	}

	/**
	 * @function 创建任务step
	 * @description 校验step2输入配置项是否合法，封装配置信息.
	 * @return true or false
	 */
	var step2Valid = function(){
		var nodeUuid = $("#selectnode").val();//目标节点uuid
		var storageUuid =$("#selectstorage").val(); //目标存储uuid
		var stepVerify = step2Verify();  //步骤2的相关校验
		var backupMode = $('#backup_mode').val(); //备份模式
		if(!stepVerify){ return false; }

		if(networkFlag){  //初始化传输网络
			$('.transfernetworkDiv').show();
			initNetworkList();
		}else{
			$('.transfernetworkDiv').hide();
		}
		$("#file_cache_path").val(_agentDefaultCachePath);  //客户端默认文件缓存路径
		_createMsgdata.node_uuid = nodeUuid;  //封装目标节点uuid
		_createMsgdata.storage_uuid = storageUuid;  //封装目标存储uuid
		_createMsgdata.backup_object.backup_mode = parseInt(backupMode); //备份模式
		_createMsgdata.backup_object.mirror_backup_flag = 2;	//双机镜像标志位
		_createMsgdata.backup_object.rebuild_partition_flag =_createMsgdata.rebuild_partition_flag;  //重建分区 1：set 2：unset
		_createMsgdata.backup_object.standby_vol_relation_set = [];
		_createMsgdata.backup_object.standby_agent_uuid = "";  //备机uuid,用于接管和双机镜像
		_standbyMappingConfigList = []; //双机镜像配置view
		var standbyMappingConfigObj = {};
		standbyMappingConfigObj.host_name = _createMsgdata.getNmae;//已配置主机
		standbyMappingConfigObj.standby_name = _createMsgdata.standby_host; //已配置的备机
		_standbyMappingConfigList.push(standbyMappingConfigObj);
		_createMsgdata.backup_object.standby_flag = CONF.FLAG.UNSET;
		if(backupMode== CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY || backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY){  //实时复制，实时复制+实时同步
			_createMsgdata.backup_object.standby_flag = CONF.FLAG.SET;
			if(_createMsgdata.standby_target_agent_uuid==0 && _autoDoubleHostAuth){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE_TIPS);
				return false;
			}
			_createMsgdata.backup_object.mirror_backup_flag = 1;
			_createMsgdata.backup_object.standby_vol_relation_set = _createMsgdata.proxy_agent_object;
			var tab = $('#backup_host_vol_table');
			var table = tab.dataTable();
			var rows = table.fnGetNodes();
			for (var i = 0; i < rows.length; i++) {
				var row = table.fnGetData(rows[i]);
				var selectId = "standby_volume_"+i;
				var selectText = $("#"+selectId).find("option:selected").text();
				var volMappingObj = {};
				volMappingObj.host_volume = row[0]; //主机卷名
				volMappingObj.host_volume_size = row[1]; //主机卷容量
				volMappingObj.standby_volume = selectText; //备机卷名
				_standbyMappingConfigList.push(volMappingObj);
			}
		}

		var standbyHostMode = $('#standby_host_mode').val();
		//备机类型为容灾演练平台，备份模式为主备复制、实时备份+主备复制
		if(standbyHostMode ==CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT 
			|| backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY 
			|| backupMode ==CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY ){
			_createMsgdata.backup_object.standby_agent_uuid = _createMsgdata.standby_target_agent_uuid;  //备机uuid,用于接管和双机镜像
		}
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.transferDiv').hide();
		}else{
			$('.transferDiv').show();
		}

		var takeoverConfResult = takeoverConfigVerify(); //校验自动接管配置
		if(!takeoverConfResult){
			return false;
		}
		
		if(_createMsgdata.takeover_object.auto_takeover_flag == CONF.FLAG.SET){
			var ipMap = _createMsgdata.takeover_object.takeover_business_ip_map;
			if(ipMap.length==0){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE, LANG.UI_VOL_CDP_AUTO_TAKEOVER_NIC_CONF_TIPS);
				return;
			}
		}
		//预加载项
		fileCachePathChange();
		return true;
	}
	/**
	 * 切换文件缓存路径方式
	 */
	var fileCachePathChange = function(){
		var fileCacheDefaultPath = LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
		$('#customFileCachePath').click(function(){
			$('#defaultFileCachePath').show();
			$('#customFileCachePath').hide();
			$('#file_cache_path').removeAttr("readonly");
			$('#file_cache_path').val("");
		});

		$('#defaultFileCachePath').click(function(){
			$('#defaultFileCachePath').hide();
			$('#customFileCachePath').show();
			$('#file_cache_path').attr("readonly","readonly");
			if(_agentDefaultCachePath!=""){
				fileCacheDefaultPath = _agentDefaultCachePath;
			}
			$('#file_cache_path').val(fileCacheDefaultPath);
		});
	}

	/**
	 * @function 校验自动接管配置
	 * @description 获取各配置项，判断是否符合配置需求，封装消息结构。
	 * @return true or false
	 */
	var takeoverConfigVerify = function (){
		var takeover_object = {};
		takeover_object.script_set = []; //自定义监控脚本配置
		if($('#takeover_config_switch').is(':checked') || $('#real_sync_takeover_config_switch').is(':checked')) {  //自动接管开启
			takeover_object.auto_takeover_flag = CONF.FLAG.SET;  // 0:unknow;1:set;2:unset;
			takeover_object.takeover_standby_agent_uuid = _createMsgdata.standby_target_agent_uuid; //接管备机uuid

			takeover_object.agent_heartbeat_failure_time = parseInt($('#agent_heartbeat_failure_time').val());  //心跳间隔最大时间
			var standbyHostMode = $('#standby_host_mode').val();
			var backupMode = $('#backup_mode').val();  //备份模式1：实时同步、2：实时复制、3：实时同步+实时复制
			takeover_object.takeover_agent_role = CONF.TEMP_AGENT_ROLE.TEMP_AGENT_ROLE_TAKEOVER;
			
			takeover_object.agent_failback_standby_ip = $("#takeover_restore_ip").val(); //failback ip
			
			if(standbyHostMode == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT && backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC) {  //接管备机类型为容灾演练平台并且备份模式为实时备份
				takeover_object.agent_failback_standby_ip = "";
			}

			if(standbyHostMode!=CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT && backupMode!= CONF.CDP_BACKUP_MODE.REALTIME_COPY && backupMode!= CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY ){
				_createMsgdata.backup_object.standby_agent_uuid = "";   //为配合后台逻辑，选择非代理的备机时，开启自动接管此处值为空
			}

			if(takeover_object.agent_failback_standby_ip=="" && standbyHostMode != CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE_TIPS);
				$('#takeover_restore_ip').focus();
				return false;
			}
			if(_createMsgdata.standby_target_agent_uuid == 0){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE_TIPS2);
				return false;
			}
			
			if(backupMode==CONF.CDP_BACKUP_MODE.REALTIME_COPY ||backupMode== CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY) {  //双机镜像打开(2：实时复制，3：实时同步+实时复制)
				takeover_object.mount_point_set = [];  //同双机镜像，该模式下挂载点相关信息未空
			}else{
				var tab = $('#takeover_vol_target_table');
				var table = tab.dataTable();
				var rows = table.fnGetNodes();
				var mount_point_set = [];
				for (var i = 0; i < rows.length; i++) {
					var mountObj = {};
					var row = table.fnGetData(rows[i]);
					var targetInputId = "mount_target_"+row[3];
					var targetMountPoint = $("#"+targetInputId).val();
					var osType = row[4];

					if(targetMountPoint == LANG.UI_VOL_CDP_BACKUP_SYSTEM_AUTO_ALLOCATE || targetMountPoint == ""){
						targetMountPoint = ""; //获取选中备份集对应的挂载点
					}

					var isUsed = verdictVolIsUsed(targetMountPoint);  //判断输入的挂载点是否被占用
					var hostMountpointStr = $("#standbyHostSelect").find("option:selected").attr("data-mountpoint");
					if(!isUsed && osType=="Windows"){
						UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS+"( "+LANG.UI_VOL_CDP_TAKEOVER_MOUNT_HAS_BEEN_USED+hostMountpointStr+" )");
						return false;
					}

					if(targetMountPoint!=""){ //需要根据系统类型校验输入挂载点是否合法；
						let regex = /^[A-B]:\\/i;
						if (regex.test(targetMountPoint) && osType=="Windows") {
							UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_AB);
							return false;
						}
						else if(checkPath(targetMountPoint,osType)){
							targetMountPoint = targetMountPoint.replace(/\\/g, '');  //去除反斜杠
						}else{
							UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS2);
							return false;
						}
					}

					mountObj.display_name = row[0];
					mountObj.vol_uuid = row[3];
					mountObj.target_mount_point = targetMountPoint;
					mount_point_set.push(mountObj);
				}
				//判断目标挂载点是否重复
				if(mount_point_set.length>1){
					var mountPointArray = new Array();
					for(var i=0;i<mount_point_set.length;i++){
						var targetMountPoint = mount_point_set[i].target_mount_point;
						if(targetMountPoint!=""){
							mountPointArray[i] = targetMountPoint;
						}
					}
					var repeatArray = arrayIsRepeat(mountPointArray);
					if(repeatArray) {
						UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_TAKEOVER_VOL_MOUNT_POINT_REPEAT);
						return false;
					}
				}

				takeover_object.mount_point_set = mount_point_set;
			}
			//应用故障监测默认值
			takeover_object.app_consecutive_failure_num = 0; //应用连续故障次数，0表示不检测    
			takeover_object.app_fault_detection_interval = 0; //应用故障检测间隔，单位：秒，0：不检测

			if($('#app_takeover_switch').is(':checked')){ //应用接管
				takeover_object.app_takeover_flag = CONF.FLAG.SET;
				var takeoverAppTree = $.fn.zTree.getZTreeObj("takeover_app_infotree");
				var checkedNodes = takeoverAppTree.getCheckedNodes(true);
				var takeoverAppSet = [];
				_takeoverAppList = [];//存储接管应用名
				if(checkedNodes.length>0){
					for(var i=0;i<checkedNodes.length;i++){
						var appMapObj = {};
						appMapObj.master_app_uuid = checkedNodes[i].host_app_uuid; //主机对应应用uuid
						appMapObj.standby_app_uuid = checkedNodes[i].st_app_uuid; //主机对应应用uuid
						if(appMapObj.master_app_uuid && appMapObj.standby_app_uuid){
							takeoverAppSet.push(appMapObj);
						}
						_takeoverAppList.push(checkedNodes[i].name);
					}
					takeover_object.app_set = takeoverAppSet;
				}else{
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_CONFIGURE_TIPS3);
					return false;
				}

				if($('#app_monitor_switch').is(':checked')){ //应用故障监测
					takeover_object.app_consecutive_failure_num = parseInt($('#app_failure_number').val());  //应用连续故障次数，0表示不检测
					takeover_object.app_fault_detection_interval = parseInt($('#takeover_app_interval').val());  //应用故障检测间隔，单位：秒，0：不检测
				}
			}else {
				takeover_object.app_takeover_flag = CONF.FLAG.UNSET;
				takeover_object.app_set = [];  // if app_takeover_enable=0, this param is empty
			}

			if($('#script_takeover_switch').is(':checked')){  //开启接管自定义脚本功能
				if(_scriptSet.length==0){
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE6);
					return false;
				}
				takeover_object.script_set = _scriptSet;
			}
		}else{
			takeover_object.auto_takeover_flag = CONF.FLAG.UNSET; // 0:unknow;1:set;2:unset;
			takeover_object.takeover_standby_agent_uuid = ""; //接管备机uuid
			takeover_object.agent_failback_standby_ip = "";
			takeover_object.agent_heartbeat_failure_time = 0;
			takeover_object.app_consecutive_failure_num =0;//应用连续故障次数，0表示不检测    
			takeover_object.app_fault_detection_interval = 0; //应用故障检测间隔，单位：秒，0：不检测
			takeover_object.app_set = [];  // if app_takeover_enable=0, this param is empty
		}
		/**
		 * 备机为内嵌虚拟机时，由选择配置IP 映射关系，修改为自动填充；
		 * 但该段代码暂时保留（6.0.4转测前的需求调整）
		 *
		var takeoverIpMap = [];
		for(var i = 0;i<_takeoverIpMapList.length;i++){
			var ipMapConf = _takeoverIpMapList[i].agent_map_info;
			takeoverIpMap.push(ipMapConf);
		}
		for(var j=0;j<takeoverIpMap.length;j++){
			var nicIpInfo =  takeoverIpMap[j].nic_ip_info;
			for(var i =0;i<nicIpInfo.length;i++){
				var nicName = nicIpInfo[i].target_nic_name;
				var standbyGateway = replaceStandbyNetcard(nicName);
				if(standbyGateway!=""){
					nicIpInfo[i].target_gateway = standbyGateway;
				}
			}
		}*/
		var businessIpMap = [];
		if(!objctIsEmpty(_vmTmpAgentConf)){
			// conformityVmConfInfo(takeoverIpMap);
			businessIpMap = paddingVmtempBusinessIpMap();
			takeover_object.takeover_vm = {};
			takeover_object.takeover_vm.config = _vmTmpAgentConf;
			takeover_object.takeover_vm.uuid = _createMsgdata.standby_target_agent_uuid;
			takeover_object.takeover_vm.hypervisor_type = 108; //内嵌为kvm  对应后台虚拟机类型：VmHypervisorType,对应键:VM_HYPERVISOR_TYPE_EMBED_OEMU_KVM
			takeover_object.takeover_vm.node_uuid = _createMsgdata.node_uuid;
		}else {
			var takeoverIpMap = [];
			for(var i = 0;i<_takeoverIpMapList.length;i++){
				var ipMapConf = _takeoverIpMapList[i].agent_map_info;
				takeoverIpMap.push(ipMapConf);
			}
			for(var j=0;j<takeoverIpMap.length;j++){
				var nicIpInfo =  takeoverIpMap[j].nic_ip_info;
				for(var i =0;i<nicIpInfo.length;i++){
					var nicName = nicIpInfo[i].target_nic_name;
					var standbyGateway = replaceStandbyNetcard(nicName);
					if(standbyGateway!=""){
						nicIpInfo[i].target_gateway = standbyGateway;
					}
				}
			}
            businessIpMap = takeoverIpMap;
		}
		//备机为容灾演练平台，组合takeover_business_ip_map 消息结构
		takeover_object.takeover_business_ip_map = businessIpMap;
		takeover_object.failback_business_ip_map = [];
		_createMsgdata.takeover_object = takeover_object;
		return true;
	}
	/**
	 * 接管备机为容灾演练平台（内嵌虚拟机）时，动态填充网卡主备映射关系
	 */
	var paddingVmtempBusinessIpMap = function (){
		var vmBusinessNicSet = _vmTmpAgentConf.vm_interfaces_nic_set;
		var businessIpMap = [];
		_agentNetworkInfo.forEach(function(_agentNetworkInfo) {  
			// _agentNetworkInfo  
			vmBusinessNicSet.forEach(function(vmBusinessNicSet) {  
				// 如果找到相等的对象  
				if (_agentNetworkInfo.ip_set === vmBusinessNicSet.source_ip_info) {
					var nicGateway = _agentNetworkInfo.gateway_address;
					var nicName = _agentNetworkInfo.name;
					var nicMac = _agentNetworkInfo.mac_address;
					var ipSet = _agentNetworkInfo.ip_set;

					var vmTargetNetCard = vmBusinessNicSet.name;
					var vmTargetGateway = vmBusinessNicSet.gateway_address;
					var vmTargetNicMac = vmBusinessNicSet.mac_address;
					var info = {};
					info.nic_gateway = nicGateway;
					info.nic_name = nicName;
					info.nic_mac = nicMac;

					var nicIpInfo = [];
					for(var j = 0;j<ipSet.length;j++){
						var nicIpInfoObj = {};
						nicIpInfoObj.ip = ipSet[j].ip_addr;
						nicIpInfoObj.netmask = ipSet[j].netmask;
						nicIpInfoObj.target_nic_name = vmTargetNetCard;
						nicIpInfoObj.target_nic_mac = vmTargetNicMac;
						nicIpInfoObj.target_gateway = vmTargetGateway;
						nicIpInfo.push(nicIpInfoObj);
					}
					info.nic_ip_info = nicIpInfo;
					businessIpMap.push(info);
				}
			});
		});

		return businessIpMap;
	}

	//判断对象是否为空/是否赋值
	var  objctIsEmpty = function (obj) {
		for (const key in obj) {
			if (Object.prototype.hasOwnProperty.call(obj, key)) {
				return false;
			}
		}
		return true;
	}
	/**
	 * 根据网卡映射配置信息，填充虚拟机网络信息
	 */
	var conformityVmConfInfo = function (takeoverIpMap){
		for(var k =0;k<takeoverIpMap.length;k++){
			var nicIpInfo =takeoverIpMap[k].nic_ip_info;
			for(var n=0;n<nicIpInfo.length;n++){
				var targetNicName =nicIpInfo[n].target_nic_name;
				var ip = nicIpInfo[n].ip;
				var netMask = nicIpInfo[n].netmask;
				var ipSet = [];
				// var vmNicSet = _vmTmpAgentConf.special.business_nic_set;
				var vmNicSet = _vmTmpAgentConf.vm_interfaces_nic_set;
				for(var d =0;d<vmNicSet.length;d++){
					if(vmNicSet[d].name ==targetNicName){
						var ipSetObject = {};
						ipSetObject.ip_addr = nicIpInfo[n].ip;
						ipSetObject.ip_type = 1;
						ipSetObject.netmask = nicIpInfo[n].netmask;
						ipSet.push(ipSetObject)
					}
					// _vmTmpAgentConf.special.business_nic_set[d].ip_set = ipSet;
					_vmTmpAgentConf.vm_interfaces_nic_set[d].ip_set = ipSet;
				}
			}
		}
		// _vmTmpAgentConf.special.role = CONF.TEMP_AGENT_ROLE.TEMP_AGENT_ROLE_TAKEOVER;
		// _vmTmpAgentConf.special.cross_platform_flag = CONF.FLAG.UNSET;
	}
	/**
	 * 因后台调整消息结构困难，这里web将备机配置的网卡对应网关赋值给主机映射的备机网关
	 */
	var replaceStandbyNetcard = function(hostCardName){
		var gateway = "";
		for(var i = 0;i<_standbyGatewayConf.length;i++){
			if(_standbyGatewayConf[i]["netcard"] == hostCardName){
				gateway = _standbyGatewayConf[i]["gateway"];
				break;
			}
		}
		return gateway;
	}

	//校验输入盘符是否已被占用
	var verdictVolIsUsed = function(targetMountPoint){
		if(targetMountPoint!=""){
			var mountPointStr = _standbyHostMountPoint.replace(/\\/g,"/");
			var mountPointList =  mountPointStr.split(",");
			var targetMountStr = targetMountPoint.replace(/\\/g,"/");
			if ($.inArray(targetMountStr, mountPointList) == -1) {
				return true;
			}
			return false;
		}else{
			return true;
		}

	}
	//限速策略
	var getSpeedStr = function(){
		_createMsgdata.speedInfo = speedList;
		return true;
	}

	//封装标签策略
	var getTagPoint = function (){
		_createMsgdata.backupInfo.type = "strategy";
		_createMsgdata.backupInfo.datetime = $("#timing_start").val();
		_createMsgdata.backupInfo.tagInfo = tagList;
		return true;
	}

	//得到归档策略
	var getAchiveStr = function(){return true;}

	//得到保留策略
	var getReserveStr = function(){
		_createMsgdata.highInfo.reserve = {};
		_createMsgdata.highInfo.reserve.type = 2; //按天数保留
		_createMsgdata.highInfo.reserve.value = $('#spinnerDayInput').val();
		if(_createMsgdata.highInfo.reserve.value > 0){
			return true;
		}else{
			UIToastr.showInfo(LANG.UI_STRATEGY_RESERVE, LANG.UI_BACKUP_RESERVE_TIPS);
			return false;
		}
	}
	/**
	 * @function 获取传输策略相关配置参数
	 */
	var getTransferStr = function(){
		_createMsgdata.highInfo.transfer = {};
		_createMsgdata.highInfo.transfer.mode = $('#transport_mode').val();
		_createMsgdata.highInfo.transfer.network = $('#transferNetwork').val();
		_createMsgdata.highInfo.transfer.compress = 0;
		_createMsgdata.highInfo.transfer.encrypt = 0;
		_createMsgdata.highInfo.transfer.compress_method = 0;
		_createMsgdata.highInfo.transfer.encrypt_method = 0;

		if($('#tran_compress_switch').is(':checked')){ //传输压缩
			_createMsgdata.highInfo.transfer.compress = true;  //此处只能传输boole值，因为对应的解析函数通过boole值来转换SET（1） or UNSET（2）
			_createMsgdata.highInfo.transfer.compress_method = $('#transferCompressGrade').val();  //压缩等级
		}else{
			_createMsgdata.highInfo.transfer.compress = false;
		}
		if($('#encrypttransfer').is(':checked')){ //传输加密
			_createMsgdata.highInfo.transfer.encrypt = true;  //此处只能传输boole值，因为对应的解析函数通过boole值来转换SET（1） or UNSET（2）
			// 传输加密算法
			_createMsgdata.highInfo.transfer.encrypt_method = 1;
		}else{
			_createMsgdata.highInfo.transfer.encrypt = false;
		}
		// 传输加密算法
		// _createMsgdata.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val(1));
		// 重连次数
		_createMsgdata.highInfo.transfer.reconnect_times = parseInt($('#reconnect_time').val());
		// 重连间隔时间
		_createMsgdata.highInfo.transfer.reconnect_interval = parseInt($('#reconnect_interval').val());
		// 重连次数数字检测
		if(!Number.isInteger(_createMsgdata.highInfo.transfer.reconnect_times)){
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER,LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_TIPS);
			return false;
		}
		// 重连次数大小1 - 999
		if (_createMsgdata.highInfo.transfer.reconnect_times < 1) {
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_MIN_TIPS);
			return false;
		}
		// 重连间隔时间最小5
		if(_createMsgdata.highInfo.transfer.reconnect_interval < 5){
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER,LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MIN_TIPS);
			return false;
		}
		// 重连间隔时间最大60
		if(_createMsgdata.highInfo.transfer.reconnect_interval > 60){
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER, LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_MAX_TIPS);
			return false;
		}
		// 重连间隔数字检测
		if(!Number.isInteger(_createMsgdata.highInfo.transfer.reconnect_interval)){
			UIToastr.showWarning(LANG.UI_STRATEGY_TRANSFER,LANG.UI_GLOBAL_STRATEGY_RECONNECT_INTERVAL_TIPS);
			return false;
		}
		var verifyThread =  checkTransferThreadNum();
		if(!verifyThread){
			return false;
		}
		if (CONF.FUNCTIONS.includes('multithread')) {
			// $('.threadNum-number-div').hide();
			_createMsgdata.thread_num = $('#transfer_thread_number').val(); //传输线程个数
		}else{
			_createMsgdata.thread_num = 1;
		}
		

		var datapackage_size = $('#transfer_datapackage_size').val(); //传输数据包大小
		var transport_block_size = datapackage_size * 1024*1024;
		_createMsgdata.transport_block_size = transport_block_size; //传输数据包大小
		_createMsgdata.highInfo.transfer.block_size = transport_block_size; //传输数据包大小


		var storage_block_size = $('#storage_block_size').val();
		var storagetUnit = 1024;
		var file_cache_path = $('#file_cache_path').val(); //文件缓存路径
		if(!$('#file_cache_path').readonly ) {
			if(file_cache_path==""){
				UIToastr.showWarning(LANG.UI_VM_SETTING_V2_HIGH_SETTING, LANG.UI_JOB_FILE_CACHE_PATH_NULL_TIPS);
				return false;
			}else {
				if(!checkFilePath(file_cache_path,_optionType)){
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
					return false;
				}
			}
		}

		_createMsgdata.storage_block_size = storage_block_size * storagetUnit; //存储数据块大小
		_createMsgdata.cache_config.file_cache_alloc_path = file_cache_path;

		_createMsgdata.backup_object.monitor_data_io_replication_mode = 1; //default
		_ioReplicationMode = $('input:radio[name=io_replication_modle]:checked').val(); //IO 复制模式
		_createMsgdata.backup_object.monitor_data_io_replication_mode = _ioReplicationMode;

		var file_cache_size = $('#file_cache_size').val();
		var file_cache_block_unit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；
		_createMsgdata.cache_config.file_cache_alloc_size = file_cache_size*file_cache_block_unit; //文件缓存大小

		if($('#memory_cache_switch').is(':checked')){
			var memory_cache_size = $('#memory_cache_size').val();
			var memory_cache_block_unit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；
			_createMsgdata.cache_config.memory_cache_alloc_size = memory_cache_size * memory_cache_block_unit;
		}else{
			_createMsgdata.cache_config.memory_cache_alloc_size = 0;
		}

		return true;
	}

	var step3Valid = function(){
		getTaskName();
		var result = getTagPoint();
		if(!result) return false;
		var result = getSpeedStr() & getReserveStr() & getAchiveStr() & getTransferStr() & getStoreStr();
		if(result){
			result = result && showStep3();
		}
		return result;
	}
	//得到任务名,这里需要注意的是在跨虚拟化平台恢复的时候需要用目的地的名字,
	//这里是需要在选择目的地节点后再初始化.
	var getTaskName = function(){
		var info = {};
		info.task_type = CONF.MODULE_TYPE.VOL_CDP;
		if(_backupTaskType == "backup"){
			info.task_name = LANG.UI_VOL_CDP_BACKUP_DEFAULT_TASK_NAME; 
		}else{
			info.task_name = LANG.UI_VOL_CDP_COPY_DEFAULT_TASK_NAME; 
		}
		
		info = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getVolCdpBackupTaskName',p:info}, function(d){
			$('#volcdpname').val(d);
		});
	}
	/**
	 * 汇总配置信息
	 */
	var showStep3 = function (){
		//备份数据源
		var appTypeName = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
		if(_createMsgdata.appTypeName.length>0){
			var appTypeName = _createMsgdata.appTypeName;
		}
		var backupSource = "";
		backupSource += _createMsgdata.getNmae + "<br>";
		backupSource += LANG.UI_VOL_CDP_BACKUP_CONFIGURE_APPLICATION +"：&nbsp;"+ appTypeName + "<br>";
		backupSource += LANG.UI_VOL_CDP_BACKUP_MONITOR_VOL+"：&nbsp;";
		for(var i=0;i<_hostVolInfo.length;i++){
			if(i>=1){
				backupSource += "&nbsp;,&nbsp;"+_hostVolInfo[i].displayName+"(&nbsp;"+LANG.UI_VOL_CDP_BACKUP_ALL_CAPACITY+":"+_hostVolInfo[i].volSize+"&nbsp;)";
			}else{
				backupSource += _hostVolInfo[i].displayName+"(&nbsp;"+LANG.UI_VOL_CDP_BACKUP_ALL_CAPACITY+":"+_hostVolInfo[i].volSize+"&nbsp;)";
			}
		}
		$('.volcdphostshow').html(backupSource);
		//备份目的地
		var nodeInfo = '';
		var storeInfo = '';
		var nodeInfo = $('#selectnode option:selected').text();
		var storeInfo = $('#selectstorage option:selected').text();
		$('.nodeinfoshow').html(nodeInfo);
		$('.storeinfoshow').html(storeInfo);

		var doubleHostMirrorSwitch = LANG.UI_VOL_CDP_BACKUP_NO_ENABLED;
		var backupMode = _createMsgdata.backup_object.backup_mode;
		$('#stdmapvolumediv').hide();
		$('#dataBackupDiv').hide();
		switch (backupMode){
			case 1:
				$('.backupmodeshow').html(LANG.UI_VOL_CDP_BACKUP_MODE_REAL_TIME_SYNC);
				$('.copy-takeoverstandbyhosttype').hide();
				// $('.takeoverstandbyhosttype').show();
				break;
			case 2:
				$('.backupmodeshow').html(LANG.UI_VOL_CDP_BACKUP_MODE_REAL_TIME_REPLICATION);
				$('#stdmapvolumediv').show();
				$('.copy-takeoverstandbyhosttype').hide();
				$('.takeoverstandbyhosttype').hide();
				$('#dataBackupDiv').show();
				break;
			case 3:
				$('.backupmodeshow').html(LANG.UI_VOL_CDP_BACKUP_MODE_REAL_TIME_SYNC_AND_REPLICATION);
				$('#stdmapvolumediv').show();
				$('.copy-takeoverstandbyhosttype').hide();
				$('.takeoverstandbyhosttype').hide();
				break;
		}
		fillInStandbyMapTabel(); //汇总主备机映射关系view
		if($('#bk_imagedata_to_server').is(':checked') && backupMode ==  CONF.CDP_BACKUP_MODE.REALTIME_COPY ){
			$('.databackupshow').html(LANG.UI_BLACK_WHITE_ENABLE);
			backupMode = CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY;
			_createMsgdata.backup_object.backup_mode = parseInt(backupMode); //备份模式
		}else{
			$('.databackupshow').html(LANG.UI_VOL_CDP_BACKUP_NO_ENABLED);
		}
		//自动接管配置信息汇总
		if($('#real_sync_takeover_config_switch').is(':checked') && backupMode ==  CONF.CDP_BACKUP_MODE.REALTIME_SYNC ){
			var takeoverStandbyTypeStr = $('#standby_host_mode option:selected').text();
			$('.takeoverstandbytypesummary').html(takeoverStandbyTypeStr);
		}


		if(_createMsgdata.takeover_object.auto_takeover_flag==CONF.FLAG.SET){ // 0:unknow;1:set、2:unset;
			$('.autotakeoverswitchshow').html(LANG.UI_BLACK_WHITE_ENABLE);
			$('.autotakeoverstandbydiv').show();
			// $('.takeoverrestoreipdiv').show();
			$('.apptakeoverswitchview').show(); //应用故障接管
			$('.takeovermountpointview').show(); //接管卷挂载点
			$('.takeoveripservicemapdiv').show(); //业务IP映射
			var takeoverStandbyStr = _createMsgdata.standby_host;
			var hostMode = $('#standby_host_mode').val();
			var takeoverStandbyTypeStr = $('#standby_host_mode option:selected').text();
			$('.takeoveripservicemapdiv').show();
			if(hostMode== CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
				takeoverStandbyStr = _vmTmpAgentConf.vm_name;
				$('.takeovermountpointview').hide();
				$('.takeoveripservicemapdiv').hide();
			}
			if(backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC){
				// $('.takeoverstandbyhosttype').show();
				$('.takeoverstandbytypesummary').html(takeoverStandbyTypeStr); 
			}

			$('.takeoverstandbyshow').html(takeoverStandbyStr); //接管备机
			// $('.takeoverstandbytypeshow').html(takeoverStandbyTypeStr);

			var takeoverIpserviceMapStr = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
			if(_takeoverIpMapListView.length>0){
				takeoverIpserviceMapStr = "";
			}
			for(var i=0;i<_takeoverIpMapListView.length;i++){
				takeoverIpserviceMapStr += " <span style ='font-weight:Bold;'></span>"+_takeoverIpMapListView[i].conf_des+"<br>"
			}
			$('.takeoveripservicemapshow').html(takeoverIpserviceMapStr);


			$('.heartbeatfailuretimeview').show();
			var heartbeatMaxTime = _createMsgdata.takeover_object.agent_heartbeat_failure_time;
			$('.heartbeatfailuretimeshow').html(LANG.UI_VOL_CDP_BACKUP_HEARTBEAT_FAILURE_TIME_GREATER+heartbeatMaxTime+LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_SECOND);

			var takeoverRecoveryIp = $('#takeover_restore_ip').val();
			$('.takeoverrestoreipshow').html(takeoverRecoveryIp); //接管恢复IP
			var volConf = _createMsgdata.takeover_object.mount_point_set;
			var takeoverTargetStr = "";
			var takeoverVolStr = "";
			for(var i=0;i<volConf.length;i++){
				var volStr = volConf[i].display_name;
				var mountPint = volConf[i].target_mount_point;
				if(mountPint==""){
					mountPint = LANG.UI_VOL_CDP_BACKUP_SYSTEM_AUTO_ALLOCATE;
				}
				var volStr = LANG.UI_VOL_CDP_BACKUP_TAKEOVER_VOL + volStr;
				var mountPint = LANG.UI_VOL_CDP_BACKUP_MOUNT_POINT + mountPint;
				takeoverTargetStr += volStr +" <span style ='font-weight:Bold;font-size:15px;'> &nbsp;>&nbsp;</span>"+mountPint+"<br>";
			}
			if(volConf.length==0 && (backupMode== CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY || backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY)){
				takeoverTargetStr = _standbyVolMappingRelation;
				// _standbyMappingConfigList
			}
			$('.takeovermountpointinfo').html(takeoverTargetStr);
		}else{
			$('.autotakeoverswitchshow').html(LANG.UI_VOL_CDP_BACKUP_NO_ENABLED);
			$('.autotakeoverstandbydiv').hide();
			$('.takeoverstandbyhosttype').hide();

			$('.takeoverrestoreipdiv').hide();
			$('.apptakeoverswitchview').hide(); //应用故障接管
			$('.heartbeatfailuretimeview').hide();
			$('.takeovermountpointview').hide();
			$('.takeoveripservicemapdiv').hide();

		}
		//应用自动接管配置信息汇总
		if(_createMsgdata.takeover_object.app_takeover_flag ==CONF.FLAG.SET){
			$('.apptakeoverswitchshow').html(LANG.UI_PUBLIC_ON);
			$('.heartbeatfailuretimeview').show();
			$('.apptakeovermonitswitchview').show();
			//$('.takeoverappdiv').show(); //自动接管应该

			var app_consecutive_failure_num = _createMsgdata.takeover_object.app_consecutive_failure_num;
			var app_fault_detection_interval = _createMsgdata.takeover_object.app_fault_detection_interval;

			$('.appfailurenumbershow').html(LANG.UI_VOL_CDP_BACKUP_CONTINUOUS_OCCUR+app_consecutive_failure_num+LANG.UI_VOL_CDP_BACKUP_TAKEOVER_FAILURE);
			$('.takeoverappintervalshow').html(LANG.UI_VOL_CDP_BACKUP_EVERY+app_fault_detection_interval+LANG.UI_VOL_CDP_BACKUP_CHECK_IF_ONLINE);
			var takeoverAppStr = parseTakeoverApp(_takeoverAppList);
			var appTypeStr = LANG.UI_VOL_CDP_BACKUP_OTHER_APPLICATION
			switch(_selectAppType){
				case CONF.DB_TYPE.SQLSERVER:
					appTypeStr = LANG.UI_VOL_CDP_BACKUP_APP_TYPE_DB_SQL;
					break;
				case CONF.DB_TYPE.ORACLE:
					appTypeStr = LANG.UI_VOL_CDP_BACKUP_APP_TYPE_DB_ORA;
					break;
				case CONF.DB_TYPE.MYSQL:
					appTypeStr = LANG.UI_VOL_CDP_BACKUP_APP_TYPE_DB_MYSQL;
					break;
				case CONF.DB_TYPE.DM:
					appTypeStr = LANG.UI_VOL_CDP_BACKUP_APP_TYPE_DB_DM;
					break;
			}
			if($('#app_monitor_switch').is(':checked')){  //开启应用故障监测功能
				$('.apptakeovermonitorswitchshow').html(LANG.UI_PUBLIC_ON);
				$('.appfailurenumberconfview').show();
				$('.takeoverappintervalconfview').show();
			}else{
				$('.apptakeovermonitorswitchshow').html(LANG.UI_PUBLIC_OFF);
				$('.appfailurenumberconfview').hide();
				$('.takeoverappintervalconfview').hide();
			}
			takeoverAppStr = appTypeStr + takeoverAppStr;
			$('.takeoverappshow').html(takeoverAppStr);
		}else{
			$('.apptakeovermonitswitchview').hide();
			$('.apptakeoverswitchshow').html(LANG.UI_PUBLIC_OFF);
			//$('.takeoverappdiv').hide(); //自动接管应该
		}
		//接管自定义脚本配置
		if($('#script_takeover_switch').is(':checked')){  //开启接管自定义脚本功能
			$('.takeoverScriptswitchshow').html(LANG.UI_PUBLIC_ON);
			$('.takeoverscriptconfdiv').show();
			var scriptList = _createMsgdata.takeover_object.script_set;
			var scriptConfStr = '';
			if(scriptList.length !=0){
				for(var i=0;i<scriptList.length;i++){
					scriptConfStr += scriptList[i].des + '<br>';
				}
			}else{
				scriptConfStr = LANG.UI_PUBLIC_NOTHING;
			}
			$('.takeoverScriptConfInfo').html(scriptConfStr);
		}else{
			$('.takeoverScriptswitchshow').html(LANG.UI_PUBLIC_OFF);
			$('.takeoverscriptconfdiv').hide();
		}
		//标签策略信息汇总
		var tagPointList = _createMsgdata.backupInfo.tagInfo;
		var tagPointLimitStr = '';
		if(tagPointList.length !=0){
			for(var i=0;i<tagPointList.length;i++){
				tagPointLimitStr += tagPointList[i].des + '<br>';
			}
		}else{
			tagPointLimitStr = LANG.UI_PUBLIC_NOTHING;
		}
		$('.tagpointinfoshow').html(tagPointLimitStr);
		//定时启动策略
		var timingStart = $("#timing_start").val();
		if(timingStart!="" && timingStart!='undefind'){
			$('.timetostartshow').html(timingStart+LANG.UI_VOL_CDP_BACKUP_START_TASK);
		}else{
			$('.timetostartshow').html(LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE);
		}
		//存储策略
		var storStrategy = _createMsgdata.highInfo.store;
		var storeInfo = "";

		var compresslabel = $('.compressLabel').html();
		var encryptStoragelabel = $('.encryptStorageLabel').html();
		var passwordAutolabel = $('.passwordAutoLabel').html();
		var storageEncryptLabel = $('.storage-encrypt-label').html();

		storeInfo += compresslabel + ": " + getSwitchDes(storStrategy.compress) + "<br>";
		// 压缩等级
		// if(storStrategy.compress){
		// 	let gradeValue = $('#compressGrade').val();
        //     let grade = '';
        //     switch (parseInt(gradeValue,10)) {
        //         case 1: 
        //             grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_FAST;
        //             break;
        //         case 2: 
        //             grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_NORMAL;
        //             break;
        //         case 3: 
        //             grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BETTER;
        //             break;
        //         case 4: 
        //             grade = LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE_BEST;
        //             break;
        //     };
        //     storeInfo += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ": " + grade + "<br>";
		// }
		storeInfo += encryptStoragelabel + ": " + getSwitchDes(storStrategy.encrypt);
		if(storStrategy.encrypt){
			// storeInfo += "<br>"+ storageEncryptLabel+": "+ $('#storageEncryptMethod option:selected').text();
			storeInfo += "<br>" + passwordAutolabel + ": " + getSwitchDes(storStrategy.password_auto_flag);
		}
		$('.storageinfoshow').html(storeInfo);

		if(CONF.RESERVE_TYPE.NUM == _createMsgdata.highInfo.reserve.type){
			reservetypeStr = LANG.UI_STRATEGY_RESERVE_NUM;
		}else if(CONF.RESERVE_TYPE.DAY == _createMsgdata.highInfo.reserve.type){
			reservetypeStr = LANG.UI_STRATEGY_RESERVE_DAY;
		}
		reservetypeStr = reservetypeStr + "," + LANG.UI_STRATEGY_RESERVE_VALUE + _createMsgdata.highInfo.reserve.value;
		$('.reservetypeshow').html(reservetypeStr);
		//传输策略
		var openstacktranslabel = $('.transferlabel').html();
		var encrypttransferlabel = $('.encrypttransferlabel').html();
		var transfernetworklabel = $('.transfernetworklabel').html();

		var transferCompress = $('.transfercompress').html();
		var transferThreadLabel = $('.transferthread').html();
		var transferDatapackageLabel = $('.transferdatapackage').html();

		var transmodeStr = $('#transport_mode option:selected').text();
		var des = openstacktranslabel + ": " +  transmodeStr +"<br>";

		var compressValue = _createMsgdata.highInfo.transfer.compress;
		var encrypttransfer = _createMsgdata.highInfo.transfer.encrypt;
		// 传输加密算法
		if($('#encrypttransfer').get(0).checked){
			let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
            let method = parseInt($('#transferEncryptMethod').val());
            let grade = '';
			switch (method) {
				case 1:
					grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
					break;
				case 2:
					grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
					break;
			};
			// des += encryptedMethodLabel + ": " + grade + '<br>';  //根据 bug #14005需求暂时屏蔽该功能
		}
		if(networkFlag){
			des += transfernetworklabel + ": "+ $('#transferNetwork option:selected').text()+"<br>";
		}
		des += encrypttransferlabel + ": "+getSwitchDes(encrypttransfer) + "<br>";
		des += transferCompress + ": " + getSwitchDes(compressValue) + "<br>";
		if(compressValue){
			let encryptedMethodLabel = $('.transfer-compress-grade-label').html();
			let encryptemethod = parseInt($('#transferCompressGrade').val());
			let encryptegradestr = '';
			switch (encryptemethod) {
				case 1:
					encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_FASTER;
					break;
				case 2:
					encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_NORMAL;
					break;
				case 3:
					encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_BETTER;
					break;
				case 4:
					encryptegradestr = LANG.UI_JOB_COMPRESS_PRIORITY_BEST;
					break;
			};
			des += encryptedMethodLabel + ": " + encryptegradestr + '<br>';  //根据 bug #14005需求暂时屏蔽该功能
		}
		if (CONF.FUNCTIONS.includes('multithread')) {
			des += transferThreadLabel +": " + _createMsgdata.thread_num + LANG.UI_PUBLIC_NUM+"<br>";
		}
		var transferDatapackageValue = $('#transfer_datapackage_size option:selected').text();
		des += transferDatapackageLabel +": " + transferDatapackageValue + "<br>"
		// 重连次数  Bug #14474 需求暂时屏蔽
		// if(parseInt($('#reconnect_time').val()) == 0){
		// 	des += $('.reconnect-times-Label').text() + ": " + LANG.UI_GLOBAL_STRATEGY_RECONNECT_TIMES_INFINITE +"<br>";
		// }else{
		// 	des += $('.reconnect-times-Label').text() + ": " + $('#reconnect_time').val() + LANG.UI_VOL_CDP_BACKUP_TIMES + "<br>";
		// }
		// 重连时间间隔
		// des += $('.reconnect-Interval-Label').text() + ": " + $('#reconnect_interval').val() + LANG.UI_PUBLIC_SECOND + " <br>";
		$('.transportinfoshow').html(des);

		//高级策略
		var storageblocklabel = $('.storageblocklabel').html();
		var filecachepathlabel = $('.filecachepathlabel').html();
		var filecachesizelabel = $('.filecachesizelabel').html();
		var memorycachelable = $('.memorycachelable').html();
		var memorycachesizelabel = $('.memorycachesizelabel').html();
		var ioreplicationlable = $('.ioreplicationlable').html();
		var automaticfaultrecoverylable = $('.automaticfaultrecoverylable').html();

		var AdvancedStrategyDes = "";

		var storageblockSizeStr = $("#storage_block_size option:selected").text();
		AdvancedStrategyDes += storageblocklabel + ": " + storageblockSizeStr + "<br>"; //存储数据块大小

		var filecachepath = $("#file_cache_path").val();
		AdvancedStrategyDes += filecachepathlabel + ": " + filecachepath + "<br>"; //文件缓存路径
		var fileCacheSize = $("#file_cache_size option:selected").text();
		AdvancedStrategyDes += filecachesizelabel + ": " + fileCacheSize  + "<br>"; //文件缓存大小

		//内存缓存
		if($('#memory_cache_switch').is(':checked')){
			var isChecked = 1;
			var memorycacheSwitch = getSwitchDes(isChecked);
			var memoryCacheSize =  $("#memory_cache_size option:selected").text();
			var memorychacheStr = memorycachesizelabel+": "+ memoryCacheSize;
			AdvancedStrategyDes += memorycachelable +": "+memorycacheSwitch +"<br>"+ memorychacheStr + "<br>";
		}else{
			var isChecked = 0;
			var memorycacheSwitch = getSwitchDes(isChecked);
			AdvancedStrategyDes += memorycachelable +": "+memorycacheSwitch+ "<br>"
		}
		var ioReplicationMode = LANG.UI_VOL_CDP_BACKUP_ASY;
		if(_ioReplicationMode==1){
			ioReplicationMode = LANG.UI_VCENTER_SYNC;
		}
		AdvancedStrategyDes += ioreplicationlable +": " + ioReplicationMode + "<br>";  //客户端缓存
		//故障自动恢复
		if($('#automatic_fault_recovery_switch').is(':checked')){
			var isChecked = 1;
			var automaticFaultRecovery = getSwitchDes(isChecked);
			AdvancedStrategyDes += automaticfaultrecoverylable +": "+automaticFaultRecovery+ "<br>"
			_createMsgdata.backup_object.auto_fault_resume_flag = CONF.FLAG.SET;
		}else{
			var isChecked = 0;
			var automaticFaultRecovery = getSwitchDes(isChecked);
			AdvancedStrategyDes += automaticfaultrecoverylable +": "+automaticFaultRecovery+ "<br>"
			_createMsgdata.backup_object.auto_fault_resume_flag = CONF.FLAG.UNKNOW;
		}
		$('.advancedStrategyshow').html(AdvancedStrategyDes);

		//限速策略
		var speedlimitshow = $('.speedlimitshow');
		var speedlimitsStr = '';
		speedlimitsStr = $('.speedlimitDes').prop('title');

		if (speedlimitsStr == '') {
			speedlimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedlimitsStr);

	}
	/**
	 * @function 解析已配置的接管应用
	 * @return 返回以，分割的字符串
	 */
	var parseTakeoverApp = function (list){
		var appStr = "";
		for(var i = 0;i<list.length;i++){
			if(i>0){
				appStr += " ,"+list[i];
			}else{
				appStr = list[0];
			}
		}
		return appStr;
	}

	/**
	 * @funciton 获取并填充主备机卷映射关系
	 */
	var fillInStandbyMapTabel = function (){
		var mappingList = _standbyMappingConfigList;
		if(_StandbyMappingDatatable!=undefined){
			clearTable(_StandbyMappingDatatable,"fillStdMapVolumeTable");
		}
		var standbyHostMode = $("#standby_host_mode").val();
		var backupMode = $('#backup_mode').val();
		//备份任务且未开启自动接管无需组装映射关系
		if(_createMsgdata.backup_object.backup_mode ==1 && _createMsgdata.takeover_object.auto_takeover_flag == CONF.FLAG.UNSET){
			return;
		}
		if(standbyHostMode== CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT || backupMode == CONF.CDP_BACKUP_MODE.REALTIME_COPY ||  backupMode == CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY){  //代理客户端
			writeInMappInfo(mappingList);
		}

	}
	/**
	 * @functon 汇总已配置的主备机卷映射关系
	 */
	var writeInMappInfo = function (mappingList){
		var hostVolConf = _hostVolInfo;
		if(_StandbyMappingDatatable == undefined){
			_StandbyMappingDatatable =  $('#fillStdMapVolumeTable').DataTable(gettableDefaultsOpt());
		}
		_standbyVolMappingRelation = "";
		for(var i=0;i<mappingList.length;i++){
			if(i==0){
				_StandbyMappingDatatable.row.add([
					mappingList[0].host_name,
					mappingList[0].standby_name
				]).draw();
			}else{
				_StandbyMappingDatatable.row.add([
					mappingList[i].host_volume+"("+LANG.UI_VOL_CDP_JOB_DETAILS_VOL_VOLUME+":"+mappingList[i].host_volume_size+")",
					mappingList[i].standby_volume
				]).draw();
				_standbyVolMappingRelation += LANG.UI_VOL_CDP_BACKUP_TAKEOVER_VOL + mappingList[i].host_volume + LANG.UI_VOL_CDP_BACKUP_MOUNT_POINT_SYMBOL+mappingList[i].standby_volume+ '<br>';
			}
		}
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
			jQuery('li', $('#volcdpbackupcontent')).removeClass("done");
			var li_list = navigation.find('li');
			for (var i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}

			if (current == 1) {
				$('#volcdpbackupcontent').find('.button-previous').css('visibility', 'hidden');
			} else {
				$('#volcdpbackupcontent').find('.button-previous').css('visibility', 'visible');
			}

			if (current >= total) {
				$('#volcdpbackupcontent').find('.button-next').hide();
				$('#volcdpbackupcontent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#volcdpbackupcontent').find('.button-next').show();
				$('#volcdpbackupcontent').find('.button-submit').css('visibility', 'hidden');
			}
			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#volcdpbackupcontent').bootstrapWizard({
			'nextSelector': '.button-next',
			'previousSelector': '.button-previous',
			onTabClick: function (tab, navigation, index, clickedIndex) {
				return false;
			},
			onNext: function (tab, navigation, index) {
				success.hide();
				error.hide();
				switch(index){
					case 1:
						if(step1Valid() == false){
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
				$('#dbbackupcontent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});

		$('#volcdpbackupcontent').find('.button-previous').css('visibility', 'hidden');
		$('#volcdpbackupcontent .button-submit').click(submit).css('visibility', 'hidden');

		// 提示框点击关闭时动态设置	cdp_app_tree 的高度
		$('#checkapptips').on('click', function() {
			$('.src-wrap__content__voltree .vcenter-tree').css('height', '100%');
		})
	};
	/**
	 * @function 提交创建任务请求
	 */
	var submit = function(){
		//判断是否开始自动接管或双机镜像，满足之一需要判断接管备机授权数是否充足
		$(".button-submit").addClass('disabled');
		$(".button-submit").css("pointer-events", "none");
		var standbyFlag = _createMsgdata.backup_object.standby_flag;
		var autoTakeoverFlag = _createMsgdata.takeover_object.auto_takeover_flag;
		var standbyHostType = $('#standby_host_mode').val();  //备机类型
		
		//选择容灾演练平台时，需要判断是否有容灾演练平台的授权
		if(standbyHostType == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT && !_isVmMachineManager){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VOL_CDP_VM_TEMP_LICENSE_CONF);
			return false;
		}

		//备机为容灾演练平台时，无需校验接管授权个数
		// if((standbyFlag ==CONF.FLAG.SET || autoTakeoverFlag ==CONF.FLAG.SET ) && standbyHostType != CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
		// 	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'verifyVolCdpTakeoverAuthNum',p:''}, function(d){  //校验卷CDP接管授权数是否充足
		// 		var data = JSON.parse(d);
		// 		if(data['result']){  //授权充足
		// 			verifyVolCdpAuthNum();
		// 		}else{
		// 			$(".button-submit").removeClass('disabled');  //移除button禁用样式
		// 			$(".button-submit").css("pointer-events", "auto"); //移除button不可点击样式
		// 			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_SUBMIT_VERIFY_TIP);
		// 			return false;
		// 		}
		// 	});
		// }else{
			verifyVolCdpAuthNum();
		// }
	}
	//校验cdp授权个数是否充足(补充校验，避免出现同时操作的情况)
	var verifyVolCdpAuthNum = function(){
		if(authInfo.capacityOrQuantity.auth_type ==1){ //按数量授权
			if(authInfo.capacityOrQuantity.total == -1 || authInfo.capacityOrQuantity.used ==-1 || authInfo.capacityOrQuantity.total > authInfo.capacityOrQuantity.used) {  //无限数量授权 或授权充足
				submitCreateBkTask();
			}else{
				$(".button-submit").removeClass('disabled');  //移除button禁用样式
                $(".button-submit").css("pointer-events", "auto"); //移除button不可点击样式
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_GRANT_AUTH_INFO,LANG.UI_VOL_CDP_BACKUP_AUTH_NUM_CANNOT_CREATE_TIP);
				return false;
			}
		}else{
			submitCreateBkTask();	
		}
	}

	var submitCreateBkTask = function(){
		if('' == $.trim($("#volcdpname").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			$(".button-submit").removeClass('disabled');  //移除button禁用样式
			$(".button-submit").css("pointer-events", "auto"); //移除button不可点击样式
			return;
		}
		$('.jobnametip').hide();
        let jobName = $.trim($("#volcdpname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
		_createMsgdata.taskName = $.trim($("#volcdpname").val());
		_createMsgdata.strategygroupuuid = $('#strategySelect option:selected').val();
		_createMsgdata.speedLimit = speedSubmitInfo();
		var jsonData = JSON.stringify(_createMsgdata);
		Metronic.blockUI({target: '#volcdpbackupcontent',animate: true,cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'createBackupJob',p:jsonData}, function(data){
			Metronic.unblockUI('#volcdpbackupcontent');

			$(".button-submit").removeClass('disabled');  //移除button禁用样式
			$(".button-submit").css("pointer-events", "auto"); //移除button不可点击样式

			if(OPREL(data)){
				LOCATION('./content/platform/jobs/jobs.php', 'task');
			}
		});
	}
	
	// 获取设置的所有策略配置信息
	var speedSubmitInfo = function (){
		let info = {};
		info['level'] = $('#tasklevelselect').val();
		info['type'] = $('#speedtypeselect').val();
		if (info['type'] == 1) {
			// 选择策略
			var selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
			if (selectedRow.length == 1) {
				info['uuid'] = selectedRow[0].uuid
				info['name'] = selectedRow[0].name
				info['strategy_type'] = selectedRow[0].type
			}
		} else {
			// 自定义
			info['speedInfo'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
		}
		return info;
	}

	/**
	 * @function 得到开关的结果描述
	 * @return 开启/关闭
	 */
	var getSwitchDes = function(check){
		if(check==CONF.FLAG.SET){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	//存储之前检查是否有修改过密码
	var checkpassword = function(){
		var now_password = $('#password').val();
		//把现在的密码和读取到的密码做对比
		//先检查旧密码是否存在
		if(_oldPassword != ''){
			if(now_password == _oldPassword){  //没有修改密码
				return atob(now_password);
			}
		}
		return now_password;

	}
	/**
	 * @function 获取存储策略配置参数
	 */
	var getStoreStr = function(){
		_createMsgdata.highInfo.store = {};
		_createMsgdata.highInfo.store.compress_method = 1;
		_createMsgdata.highInfo.store.encrypt_method = 1;
		_createMsgdata.highInfo.store.deduplication = $('#deduplicationCheck').get(0).checked;
		var storage_block_size = $('#storage_block_size').val();
		var storagetUnit = 1024;  //传递到后台的单位为字节
		_createMsgdata.highInfo.store.block_size = storage_block_size * storagetUnit; //存储数据块大小

		_createMsgdata.highInfo.store.compress = $('#compressCheck').get(0).checked;
		// 压缩等级
        // if($('#compressCheck').get(0).checked){
        //     _createMsgdata.highInfo.store.compress_method = $('#compressGrade').val();
        // }
		_createMsgdata.highInfo.store.encrypt = $('#encryptStorageCheck').get(0).checked;
		// 存储加密
		if(!_createMsgdata.highInfo.store.encrypt){
			_createMsgdata.highInfo.store.password_auto_flag = false;
		}else{
			// _createMsgdata.highInfo.store.encrypt_method = parseInt($('#storageEncryptMethod option:first').val());
			_createMsgdata.highInfo.store.password_auto_flag = $('#passwordAutocheck').get(0).checked;
		}
		_createMsgdata.highInfo.node = {};
		_createMsgdata.highInfo.node.nodecheck = true;
		_createMsgdata.highInfo.node.nodeuuid = _createMsgdata.node_uuid;
		_createMsgdata.highInfo.node.storagecheck = true;
		_createMsgdata.highInfo.node.storageuuid = _createMsgdata.storage_uuid;

		_createMsgdata.highInfo.store.password = btoa(checkpassword());
		if(_createMsgdata.highInfo.store.password_auto_flag){
			_createMsgdata.highInfo.store.password = "";
		}else if(_createMsgdata.highInfo.store.encrypt && _createMsgdata.highInfo.store.password == ""){
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_NO_PASSWORD_TIPS);
			return false;
		}else if(_oldPassword =='' && _createMsgdata.highInfo.store.encrypt && _createMsgdata.highInfo.store.password != btoa($('#repassword').val())){//没有选择策略管理
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
			return false;
		}else if(_oldPassword !='' && _createMsgdata.highInfo.store.encrypt && _createMsgdata.highInfo.store.password != $('#repassword').val()){//选择了策略管理
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_ENCRYPT, LANG.UI_BACKUP_DATA_ENCRYPT_PASSWORD_CONFIRM_TIPS);
			return false;
		}
		return true;
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
	/**
	 * @function 校验IP
	 * @description 校验IP格式是否合法
	 * @return  true or false
	 */
	var checkIP = function (value){
		return ipV4V6(value);
	}
	/**
	 * @function 获取速度单位换算大小
	 * @descript 根据选择的容量单位大小，输出以字节为单位的数值
	 * @return byte number
	 */
	var getUnit = function(divID){
		var type = parseInt($('#'+divID).val());
		var unit;
		switch(type){
			case 0:
				unit = 1;
				break;
			case 1:
				unit = 1024;
				break;
			case 2:
				unit = 1024 * 1024;
				break;
			case 3:
				unit = 1024 * 1024 * 1024;
				break;
			case 4:
				unit = 1024 * 1024 * 1024*1024;
				break;
		}
		return unit;
	}
	/**
	 * @function 生成uuid
	 */
	var getUuid = function() {
		var len = 36;//36长度
		var radix = 16;//16进制
		var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
		var uuid = [], i;
		radix = radix || chars.length;
		if(len) {
			for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
		} else {
			var r;
			uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
			uuid[14] = '4';
			for(i = 0; i < 36; i++) {
				if(!uuid[i]) {
					r = 0 | Math.random() * 16;
					uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
				}
			}
		}
		return uuid.join('');
	}


	/**
	 * @function 获取授权相关配置信息
	 * @descript 分别获取功能授权和授权类型，再提交时需要二次校验

	 */
	var initSoftwareVersionDiff = function(){
		debugger;
		_backupTaskType = $('#volcdp_backup_task_type').val();  //备份任务类型
		//获取授权方式
		var info = {};
		info.type = "a";  //获取授权类型，p是页面授权，f是功能授权，a是容量/数量授权
		if(_backupTaskType == "backup"){  //备份
			info.module = 'cdp';
			$('.vol_cdp_backup_task_title').html(LANG.UI_VOL_CDP_CREATE_BACKUP_TASK_DESC);
		}else{
			info.module = 'copy_machine';
			$('.vol_cdp_backup_task_title').html(LANG.UI_VOL_CDP_CREATE_COPY_TASK_DESC);
		}
		var moduleAvailable = false;
		pAjaxRequest(info, "/api/v1/system/auth/base_info", "GET", function (res) {  

			authInfo.capacityOrQuantity =  res.data;
			$('#backup_mode').prop('disabled', true);  //禁用备份模式的选择
			
			if(res.data.auth_type ==1){ //数量授权
				if(res.data.total>res.data.used){  //总授权数小于于已用授权数
					moduleAvailable = true;
				}
			}else if(res.data.auth_type ==2){ //容量授权
				moduleAvailable = true;
			}

			if(_backupTaskType == "backup"){  //备份
				$(".doublehostmirrorswitchdiv").hide();
				$('#doubleHostMirrorConfSummary').hide();
				//如果无双机镜像授权，此处直接移除双机镜像相关配置
				$("#backup_mode option[value='2']").remove();
				$("#backup_mode option[value='3']").remove();
				_autoDoubleHostAuth = false;
			}else{  //复制
				if(moduleAvailable){
					_autoDoubleHostAuth = true;
				}else{
					_autoDoubleHostAuth = false;
				}
			}

		});

		//获取功能授权
		var p = {};
		p.type = "f";  //获取授权类型，p是页面授权，f是功能授权，a是容量/数量授权
		pAjaxRequest(p, "/api/v1/system/auth/base_info", "GET", function (res) {  
			authInfo.function =  res.data; 
			var authFun = res.data;
			_isVmMachineManager = authFun.emergencyDR;  //“应急容灾(内嵌)
			if(_isVmMachineManager){
				$('.dualmachineimageswitchdiv').hide();
			}else{
				$('.dualmachineimageswitchdiv').hide();
				$("#standby_host_mode option[value='3']").remove();
			}

			if(!authFun.dedupication){
				//设置重删为关闭并不可用再加隐藏
				$('.deduplicationDiv').hide();
			}

			//卷cdp自动接管
			if(!authFun.cdpAutoTakeover){
				_autoTakeoverAuth = false;
				$('.cdpautotakeoverswitchdiv').hide();
				$('#autoTakeoverConfSummary').hide();
				$('.realsyncautotakeoverdiv').hide();
				$('.cdpautotakeoverswitchdiv').hide();
			}else{
				_autoTakeoverAuth = true;
			}

			if(!authFun.lanfree){
				//删除transport_mode不可用项
				$("#transport_mode option[value='san']").remove();
				$("#transport_mode option[value='hotadd']").remove();
				//删除xentransmode不可用项
				$("#xentransmode option[value='2']").remove();
				$("#xentransmode option[value='4']").remove();

				//删除类xen不可用项
				$("#leixentransmode option[value='2']").remove();

				//删除华为传输模式不可用项
				$("#huaweitransport_mode option[value='san']").remove();

			}

			if(!authFun.vcbt){  //设置有效数据提取关闭并不可用再加隐藏
				$('#parsefscheck').bootstrapSwitch('state', false);
				$('#parsefscheck').bootstrapSwitch('disabled', true);
				$('.parsefsDiv').hide();

				//深度有效数据提取结果显示隐藏
				$('.parsefsmodeshow').hide();
			}

			if(1 == CONF.SOFTWARE || 4 == CONF.SOFTWARE){
				//传输模式只支持网络传输
				//删除transport_mode不可用项
				$("#transport_mode option[value='san']").remove();
				$("#transport_mode option[value='hotadd']").remove();
				//删除xentransmode不可用项
				$("#xentransmode option[value='2']").remove();
				$("#xentransmode option[value='4']").remove();

				//删除类xen不可用项
				$("#leixentransmode option[value='2']").remove();

				//删除华为传输模式不可用项
				$("#huaweitransport_mode option[value='san']").remove();

				//设置重删为关闭并不可用再加隐藏
				$('.deduplicationDiv').hide();
				//设置有效数据提取关闭并不可用再加隐藏
				$('#parsefscheck').bootstrapSwitch('state', false);
				$('#parsefscheck').bootstrapSwitch('disabled', true);
				$('.parsefsDiv').hide();

				//深度有效数据提取结果显示隐藏
				$('.parsefsmodeshow').hide();
			}
			if(4 == CONF.SOFTWARE || 9 == CONF.SOFTWARE){
				$('#snapshotcheck').bootstrapSwitch('state', false);
				$('#snapshotcheck').bootstrapSwitch('disabled', true);
				$('#backupmodediv').hide();							//隐藏备份模式
				$('.backupmodeshow1').hide();						//隐藏高速模式
				$("#incmodel option[value='2']").remove();      //xenserver高速模式
			}


		}); 

	}
	//初始化节点传输网络列表
	var initNetworkList = function(){
		var data = {};
		data.nodeuuid = $("#selectnode").val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeNetworkList',p:p}, function(d){
			var data = JSON.parse(d);
			var transferNetwork = $('#transferNetwork');
			transferNetwork.empty();
			for(var i=0; i<data.length; i++){
				var name = data[i].ip + ":" + data[i].port;
				if(data[i].alias_name != ""){
					name += "(" + data[i].alias_name +")";
				}
				var option = '<option  value="' + data[i].network_uuid + '">' + name + '</option>';
				transferNetwork.append(option);
			}
			oldNode = $('#selectnode').val();

		});
	}

	return {
		init: function () {
			wizardInit();
			initSoftwareVersionDiff(); //版本差异处理
			initAgentTree();
			initListener();
			initSpinner();
			initDataChangeListeners(); //初始化策略事件
		}
	};
}();

jQuery(document).ready(function() {
	VolCDPBackup.init();
});