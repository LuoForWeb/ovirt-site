var VolCDPTakeover =  function () {
	var volGrid,dataSourceGrid,dataSourceFlag,tagGrid,tagGridFlag =false,eventGrid,eventGridFlag=false,gridInitFlag=false;
	var _createTaskMsgdata = {takeover_object:{}};
	var _takeoverVolDatatable,_appTypeConfStr;
	var _latestTimePoint="";
	var _timeInterval =2;  //时间轴默认加载的间隔
	var _client_vol_info=[];
	var _chartStartPercent =70;  //dataZoom的起始百分比,此处暂定70%,根据实际效果调整.(可以根据加载的时间区间范围确定起始百分比)
	var _chartEndPercent =100;  //dataZoom的结束百分比,此处暂定100%,根据实际效果调整.
	var _timeList = [];
	var _timePointValidityValue = 0;  //时间点有效性校验，默认0
	var _takeoverTargetData = [];
	var _takeoverInTask = false;  //接管目标是否已被配置接管任务
	var _takeoverTaskName = '';  //接管目标是否已被配置接管任务其对应任务名
	var _standbyHostMountPoint = '';  //接管备机已占用的挂载点
	var _userIsVerify = false,initErrorFlag=false;
	var _scriptSet = [];
	var _networkFlag = false;  //用于比对加载传输网络的节点
	var _timepointType = 1;  //时间点类型，0：无效时间点，1：任意时间点；2：标签点；3：事件点；4：标签点和事件点
	var _backupSetIsLock = 0;  //备份集加密属性，0：未加密或自动生成密码，1：加密已解密，2：加密未解锁/密码错误
	var allpointlist = [];  //保存一条链的时间点
	var _eventNavTabsType = 1;  //时间点类型tab  1：任意时间点；2：标签点；3：事件点
	_requestIng = false;

	var _agentNetworkInfo = [];  //主机客户端对应的网卡信息
	var _standbyNetworkInfo = []; //备机客户端对应的网卡信息
	var _takeoverIpMapList =[];
	var _takeoverIpMapListView = [];
	var _standbyGatewayConf = [];
	var ipMapInfoList = [];
	var osTypeTarget = '';
	var myChart;
	var _hisPortletBodyWidth = 0;
	var _hisportletBodyHeight = 0;
	var _hisChangeTimeInterval = 0;
	var _vmTmpAgentConf = {};
	var _standbyIsConf = false; //接管备机是否已配置
	var _standbyHostHisConf = 0; //备机类型历史配置
	var _hisAppScenedValue = 99; //应用场景类型历史配置
	var _isDataSourceHostMountAndVerify = false;  //允许配置目标主机与数据源主机同设备的主机作为目标主机，且应用场景为验证，验证方式为挂载时允许选择主机

	var _allSysVolInfoArray = [];  //选中数据源所有的系统和引导分区卷集合
	var _checkedVolInfo = [];  //选中需要备份的所有系统和引导分区卷集合
	var _volIsUefi = false;  //选中卷是否包含UEFI
	var _isVmMachineManager = false; //是否授权内嵌虚拟化，默认否

	/**
	 * @function 获取所有节点信息
	 * @description:获取所有存储节点信息,选择节点加载指定节点关联的数据源客户端
	 */
	//TODO 调试完成，需要删除
	var initLoadStroageNodeInfo = function (){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getVolCdpTimepointAllNode',p:JSON.stringify({moduleType : CONF.MODULE_TYPE.VOL_CDP})}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('#nodeselect');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
			initStorageSelect();
			initLoadTakeoverDataHost();
    	});
		$('#nodeselect').on('change', nodeselectChange);
	}
	//初始化存储下拉框
	//TODO 调试完成，需要删除
	var initStorageSelect = function(){
		var data = {};
		data.nodeuuid = $('#nodeselect').val();
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getBackupStorageList',p:jsonData}, function(d){
    		var data = JSON.parse(d);
    		var softselect = $('#selectstorage');
    		softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
    	});
		$('#selectstorage').on('change', selectStorageChange);
	}
	//选择目标存储
	//TODO 调试完成，需要删除
	var selectStorageChange = function (){
		var storageUuid =$("#selectstorage").val(); //目标存储uuid
		_createTaskMsgdata.storageUuid = storageUuid;
	}
	//选择存储节点
	//TODO 调试完成，需要删除
	var nodeselectChange = function (){
		var node_uuid = $('#nodeselect').val();
		_createTaskMsgdata.node = node_uuid;
		initLoadTakeoverDataHost();
	};

	/**
	 * 获取授权信息
	 */

	var initGetSysAuthFunc = function (){
		debugger;
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getSysAuthFunc',p:{}}, function(d){
			var data = JSON.parse(d);
			authFun = data.auth_function;
			var authPage = data.auth_page;
			_isVmMachineManager =  authPage.includes('vm_machine_manager');
		});
	}
	/**
	 * 获取可供接管的恢复数据源客户端
	 */
	var initLoadTakeoverDataHost = function (){
		var node_uuid = $("#nodeselect").val();
		if(!dataSourceFlag){	//初始化表格
			var dataTableOpt = {
				'columnDefs': [{
	                'orderable': false,
	                'targets': [0,1,2,3]
				}],
				"order": [
	                [1, "desc"]
	            ],
	    	};
			dataSourceGrid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPTAKEOVER,f:'getDataSourceHostInfo',p:{node_uuid:node_uuid}};
			dataSourceGrid.setAjaxParam(dataPar);
			dataSourceGrid.init({src: $("#takeoverDatasourceHostTable"), checkbox:false, dataTable:dataTableOpt, onDataLoad:tabSingleSelection});
			dataSourceFlag = true;
		}else{
			dataSourceGrid.getRefresh({node_uuid:$node_uuid}, undefined, true);//刷新表格
		}
	}
	/**
	 * 绑定自动监听事件
	 */
	var initListener = function(){
		InitTabShowEvent();
		$('#takeoverDataSourceSelect').on('change', storageObjectChange);
		$('#changeTimeInterval').on('change', changeTimeIntervalType); //时间轴
		$('#refreshEventInfo').unbind('click').click(refreshEventInfo);
		$('#getAllEventInfo').unbind('click').click(getAllEventInfo);
		$('#timepointType li').unbind('click').click(getTabInfo);
		$('#refreshLabelPointInfo').unbind('click').click(refreshLabelPointInfo);
		$('#getAllLabelPointInfo').unbind('click').click(getAllLabelPointInfo);
		$('#handover_script_switch').on('switchChange.bootstrapSwitch', handoverScriptChnage);
		$('#takeoverStandbyHostMode').unbind('click').click(takeoverStandbyModeChange);
		$('#changeAppScened').unbind('click').click(changeAppScenedChange);
		$('#standbyMapConf').on('click',standbyMapConf);
		$('#inputTakeoverTimepoint').unbind('blur').blur(function () {
			checkdate();
		});
	}
	/**
	 * 切换接管备机
	 */
	var takeoverStandbyModeChange = function (){
		var standby_mode = $('#takeoverStandbyHostMode').val();
		var appScenedValue = $('#changeAppScened').val();
		
		if(standby_mode == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT && !_isVmMachineManager){
			UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_TYPE,LANG.UI_VOL_CDP_VM_TEMP_LICENSE_CONF + ","+LANG.UI_VOL_CDP_TAKEOVER_TYPE_TIPS);
			$("#takeoverStandbyHostMode").val("3");
			return false;
		}

		if(standby_mode != _standbyHostHisConf){
			$('.targetHostUsedPartition').css("display","none");
			$('.hm_takeover_ip_map_list li').remove();  //重置网卡配置view
			$('.takeoverAppDes').html("--");
			ipMapInfoList = [];  //重置主机业务IP映射配置
			_takeoverIpMapListView = [];  //重置主机业务IP映射view配置
			$('#takeoverTargetHostSelect option:first').prop('selected', true);
			$('#takeoverBuiltInVmConfDes').html('');
			
			$('.targetHostUsedPartition').css("display","none");
			_vmTmpAgentConf = {};
			_createTaskMsgdata.takeover_object.takeover_vm = {};			
			_standbyNetworkInfo = [];
			_takeoverInTask = false;
			$('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示 
		}
		if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){  //数据验证
			$('.applianceselectview').hide();
		}
		$('.takeoverscriptdiv').show();
		$('.handoverscriptviewdiv').show();
		$('.takeovertips-two').show();
		if(standby_mode==CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT){
			$('.takeoverTargetView').show();
			$('.takeoverStandbyconfdiv').hide();
			$('input[name="mount_target"]').prop('readonly', false);
		}else if(standby_mode ==CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
			$('.takeoverTargetView').hide();
			$('.takeoverStandbyconfdiv').show();
			$('.applianceselectview').hide();
			$('input[name="mount_target"]').prop('readonly', true);
			$("[name='mount_target']").val(LANG.UI_VOL_CDP_TAKEOVER_AUTO_ALLOCATE);
			$('.takeoverscriptdiv').hide();
			$('.handoverscriptviewdiv').hide();
			$('.takeovertips-two').hide();
		}
		_standbyHostHisConf = standby_mode;
	}
	/**
	 * 应用场景切换
	 */
	var changeAppScenedChange = function (){
		var appScenedValue = $('#changeAppScened').val();
		var standbyHostMode = $('#takeoverStandbyHostMode').val();
		$('.applianceselectview').hide();
		
		if(_hisAppScenedValue !=appScenedValue){
			delVmConfDescription();
			$('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示
		}
		//2024-09-23 17:15:21 根据bug #19561 【实时容灾保护】任务运行中，也可以创建接管任务。
		// //如果选择应用场景为接管，需要判断选择数据源客户端是否有备份任务正在运行，有则不能进行接管操作
		// if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER && _createTaskMsgdata.agent_running_backup_task == CONF.TASK_STATUS.RUNNING){
		// 	UIToastr.showWarning(LANG.UI_VM_JOB_TYPE,LANG.UI_VOL_CDP_TAKEOVER_DATA_SOURCE_AGENT +" " +_createTaskMsgdata.data_source_info+" " + LANG.UI_VOL_CDP_TAKEOVER_SCENARIO_CHANGE_TIPS);
		// 	$('#changeAppScened').val(CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL);
		// 	return false;
		// }
		
		if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){
			$('.takeovernetworkconf').unbind('click').click(takeoverNetworkConfModal);
			$('.applianceselectview').show();
		}
		if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER && _takeoverIpMapListView.length!=0){
			$('.takeoveripdriftview').show();
		}
		//standbyHostMode：1 代理客户端，3 容灾演练平台
		if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER && _standbyIsConf==true && standbyHostMode == 1){
			$('.applianceselectview').show();
		}else{
			$('.applianceselectview').hide();
		}
		$('.targetHostUsedPartition').html("");
		debugger;
		//根据应用场景的选择改变接管类型的描述信息，若选择验证，则将接管类型改为整机验证、挂载验证
		if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){  //验证
			// $('#takeoverStandbyHostMode option[value="1"]').text(LANG.UI_VOL_CDP_MOUNT_VERIFY_DESC);  //挂载验证 
			// $('#takeoverStandbyHostMode option[value="3"]').text(LANG.UI_VOL_CDP_COMPLETE_MACHINE_VERIFY_DESC);  //整机验证
			$('.changestandbytype').html(LANG.UI_VOL_CDP_VERIFY_TYPE);  //验证类型
			$('.standbyTypeLabel').html(LANG.UI_VOL_CDP_VERIFY_TYPE + ":");
			$('.takeovervollabel').html(LANG.UI_VOL_CDP_VERIFY_VOL_DESC); //验证卷
			$('.standbyconflabel').html(LANG.UI_VOL_CDP_VERIFY_STANDBY_CONF); //验证备机配置
			$('.takeoverhostlabel').html(LANG.UI_VOL_CDP_VERIFY_STANDBY_CONF);//接管备机配置
			$('.takeovertimepoint').html(LANG.UI_VOL_CDP_JOB_DETAILS_VERIF_TIME_POINT);
			$('.takeovertargetlabel').html(LANG.UI_VOL_CDP_VERIFY_STANDBY_CONF+ ":");  //验证备机配置
			$('.standbyconflabelsummary').html(LANG.UI_VOL_CDP_VERIFY_STANDBY_CONF+ ":");
			_networkFlag = false;
		}else{
			// $('#takeoverStandbyHostMode option[value="1"]').text(LANG.UI_VOL_CDP_MOUNT_TAKEOVER_DESC); //挂载接管 
			// $('#takeoverStandbyHostMode option[value="3"]').text(LANG.UI_VOL_CDP_COMPLETE_MACHINE_TAKEOVER_DESC);  //整机接管
			$('.changestandbytype').html(LANG.UI_VOL_CDP_TAKEOVER_TYPE);  //接管类型
			$('.standbyTypeLabel').html(LANG.UI_VOL_CDP_TAKEOVER_TYPE + ":");
			$('.takeovervollabel').html(LANG.UI_VOL_CDP_TAKEOVER_VOL_DESC); //接管卷
			$('.standbyconflabel').html(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_CONF); //接管备机配置
			$('.takeoverhostlabel').html(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_CONF);//接管备机配置
			$('.takeovertimepoint').html(LANG.UI_VOL_CDP_JOB_DETAILS_TAKEOVER_TIME_POINT);
			$('.takeovertargetlabel').html(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_CONF+ ":");  //接管备机配置
			$('.standbyconflabelsummary').html(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_CONF+ ":");
			_networkFlag = true;
		}
		//挂载接管或验证
		if(standbyHostMode == 1){
			_networkFlag = true;
		}
		
	}
	/**
	 * 内嵌虚拟机相关映射配置，需要根据备份模式判断加载的内容
	 */
	var standbyMapConf = function (){
		$('#vm_machine_modal').modal({'width':'800px', 'height':'380px'});
		let node_uuid = _createTaskMsgdata.node_uuid;
		var vmTempNetworkInfo  =new Array();
		var appScenedValue = $('#changeAppScened').val();
		for (var i=0;i<_agentNetworkInfo.length;i++){
			var ipSet = _agentNetworkInfo[i].ip_set;
			vmTempNetworkInfo.push(ipSet);
		}

		vm_Mchine.init({'network_num': _agentNetworkInfo.length,'node_uuid':node_uuid,'task_type':CONF.TASK_TYPE.VOL_CDP_TAKEOVER,'network_info':vmTempNetworkInfo,'app_scened':appScenedValue});
		$('.show_ip_v4_items').hide();
		$('.show_ip_v6_items').hide();
		$('.show_gateway_address').hide();  //默认网关
		$('#networkInfo .left_btn').hide();  //一件部署原机配置
		$('#builtInVmSubmit').unbind('click').click(builtInVmSubmitInfo);
		$('#modal_boot_mode option[value="1"]').prop('disabled', true);
		
		
		// setTimeout(function(){
		// 	if(appScenedValue== CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){  //接管
		// 		$('select[name="modal_network_list"]').val('product_network'); 
		// 	}else{
		// 		$('select[name="modal_network_list"]').val('isolate_network'); 
		// 	}
		// },500);
		if(_volIsUefi){
			$('#modal_boot_firmware').val(2);  //选择的数据卷包含UEFI时，内嵌虚拟机的引导方式需要默认选中UEFI，无则保持默认
		}

	}

	/**
	 * 配置的内嵌虚拟主机提交
	 */
	var builtInVmSubmitInfo = function(){
		var info = vm_Mchine.getMachineInfo();
		var verifyNet = verifyVmhostNetCard(info);
		if (info === false || verifyNet ===false) {
			return;
		}
		$('#vm_machine_modal').modal('hide');
		vmStandbyConfDes(info);
		let nowTimestamp = new Date().getTime();
		let createAgentuuid = hex_md5(nowTimestamp);
		var appScenedValue = $('#changeAppScened').val();
		_standbyIsConf = true;

	}
	/**
	 * 内嵌虚拟机配置汇总
	 */
	var vmStandbyConfDes = function (info){
		debugger;
		var tmpAgentConf = info.temp_agent.config;
		_vmTmpAgentConf = tmpAgentConf;
		$('#takeoverBuiltInVmConfDes').html('');
		var memsConf = tmpAgentConf.mems_str;
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

			var vmNetCardConf =   LANG.UI_VOL_CDP_HOST_IP_SERVICE + "("+dataSourceIp+"); "+ LANG.UI_VM_MACHINE_NETWORK_SET +"("+netWorkName + "); " + LANG.UI_CLIENT_NETWORK_NIC_NAME +"("+netCardName +");" + LANG.UI_VOL_CDP_VM_MACHINE_NETWORK_INTETFACE +"("+modelTypeName +")";
			vmNetCardStr += "<span class ='ml10 ml-0_en textwrap_en' title = '"+vmNetCardConf+"' >" +vmNetCardConf+"</span><br>";
		}
		var des = '<li style="margin-top:15px;" class="list-group-item popovers vmConfTips" id="vmConfDesTips" '
			+'data-container="body" data-trigger="hover" data-placement="top" data-html="true" >'
			+'<div class="col1"><div class="cont ">'
			+'<div class="cont-col1"></div><div class="cont-col2">'
			+'<div class="desc list-one" id = "vmDetailConfigInfo" style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
			+'<sapn>'+LANG.UI_PUBLIC_STORAGE_IN_NODE+'</sapn>:<span class ="ml10" title="'+hostMachine+'">'+ hostMachine+'</span><br>'
			+'<sapn>'+LANG.UI_VOL_CDP_TAKEOVER_BACKUP_MACHINE_NAME+'</sapn>:<span class ="ml10">'+ tmpAgentConf.vm_name +'</span><br>'
			+'<sapn>'+LANG.UI_VM_MACHINE_CPU_USE+'</sapn>:<span class ="ml10">'+ cpuConf+'</span><br>'
			+'<sapn>'+LANG.UI_PUBLIC_MEMORY_SIZE+'</sapn>:<span class ="ml10">'+ memsConf+'</span><br>'
			+'<sapn>'+LANG.UI_VM_MACHINE_BOOT_FIREWARE+'</sapn>:<span class ="ml10">'+ bootFirmware+'</span><br>'
			+'<sapn>'+LANG.UI_VERIFY_CPU_MODE +'</sapn>:<span class ="ml10">'+ cpuMode+'</span><br>'
			+'<div><div style="float: left">'
			+'<sapn>'+LANG.UI_VOL_CDP_SETTING_NETWORK+'</sapn>:</div> <div style="float: left" >'+vmNetCardStr+'</div>'+'</div><br>'
			// + isBr +LANG.UI_VM_MACHINE_BOOT_FIREWARE+'</sapn>:<span class ="ml10">'+ bootFirmware+'</span><br>'
			// +'<sapn>'+LANG.UI_VM_MACHINE_BOOT_MODE+'</sapn>:<span class ="ml10">'+ bootMode+ LANG.UI_VCENTER_VM_START+'</span></div><br>'
			+'</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="vmConfDesDel" >'
			+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
		$('#takeoverBuiltInVmConfDes').append(des);
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
		// 	if (indexA < vmBusinessNicSet.length) {
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
		var appScened = $('#changeAppScened').val();
		if(appScened==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){
			$('.takeoveripdriftview').show(); 	//隐藏ip漂移提示 
		}else{
			$('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示 
		}
		
	}
	/**
	 * 校验虚拟机网卡个数是否与主机配置
	 */
	var verifyVmhostNetCard = function (info){
		let tmpAgentConf = info.temp_agent.config;
		let backupAgentNetCard = _agentNetworkInfo.length;
		let standbyNetCardConf = tmpAgentConf.interfaces.length;
		if(standbyNetCardConf<backupAgentNetCard){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VM_MACHINE_NETCARD_CONF_TIPS +backupAgentNetCard+LANG.UI_PUBLIC_NUM);
			return false;
		}
		_createTaskMsgdata.standby_target_agent_uuid = tmpAgentConf.vm_uuid; //临时代理则由web直接生成"
		return true;
	}
	/**
	 * 删除内嵌虚拟机配置描述
	 */
	var delVmConfDescription = function(){
		$('.popover.in').remove();
		$('#vmConfDesTips').remove();
		$('#takeoverBuiltInVmConfDes').html('');
		_vmTmpAgentConf = {};

		$('.hm_takeover_ip_map_list li').remove();  //重置网卡配置view
		$('.takeoverAppDes').html("--");
		ipMapInfoList = [];  //重置主机业务IP映射配置
		_takeoverIpMapListView = [];  //重置主机业务IP映射view配置
		$('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示 
	}
	/**
	 * @function 实现表格checkbox 单选
	 */
	var tabSingleSelection  = function (){
		$('#takeoverDatasourceHostTable').find('tbody > tr').off().on('click', function(){
			//1.取消所有选中项
			$(this).parent().find('input').prop("checked", false);
			//2.选中本行
			$(this).find('input').prop("checked", true);
			//得到这个点的备份信息
			var uuid = $(this).find('input').val();
			var hostIp = $(this).find("input").attr("data-hostip");
			var selectTaskuuid = $(this).find("input").attr("data-taskuuid");
			_createTaskMsgdata.master_agent_uuid = uuid;  //选中数据源uuid
			_createTaskMsgdata.host_ip = hostIp;
			_createTaskMsgdata.task_uuid = selectTaskuuid;

		});
		$('#takeoverDatasourceHostTable').find('tbody > tr').find('input[type="checkbox"]').off().on('click', function(){
			var checkBoxs = $('#takeoverDatasourceHostTable').find('tbody > tr').find('input[type="checkbox"]');	//取消所有其他选中的
			for(var i=0; i<checkBoxs.length; i++){
				var span = checkBoxs[i].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBoxs[i]).prop("checked", false);
			}
			var checkBox = $(this);
	        var checked = checkBox[0].checked;
	        if(checked){
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBox).prop("checked", false);
	    	}else{
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "checked");
		        $(checkBox).prop("checked", true);
	    	}
		});
	}
	//初始化时间控件
	var loadDatatimePicker = function(){
		$(".takevoertimepointview").datetimepicker({
			language:  'zh-CN',
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
        });
	}
	var initTimeListeners = function(){
		$('#inputTakeoverTimepoint').on('change', function(){
			var takeoverTimepoint = $('#inputTakeoverTimepoint').val()
			verifyTimepointisValid(takeoverTimepoint);
		});
		resetCheckTimepoint();
	}
	/**
	 * @function 选择接管存储对象
	 * @description 选择接管数据的存储对象,当选择备机时默认选中最新时间点,且时间点不可配置,更新所选客户端所有卷的对应时间点;
	 */
	var storageObjectChange = function(){
		var dataSource = $("#takeoverDataSourceSelect").val();
		getClientAnyTimePointInfo(dataSource);
		//数据源有正在运行的备份任务，且备份任务模式【主备复制】+【实时备份】时不能接管备机数据
		if(_createTaskMsgdata.data_source_task_backup_mode ==CONF.CDP_BACKUP_MODE.REALTIME_SYNC_OR_COPY 
			&& _createTaskMsgdata.agent_running_backup_task == CONF.TASK_STATUS.RUNNING){
				$('#takeoverDataSourceSelect').val('1'); 
				UIToastr.showWarning(LANG.UI_ADD_TASK_TYPE_TAKEOVER, LANG.UI_VOL_CDP_TAKEOVER_STANDBY_DATA_LIMIT_PROMPT);
				return;	
		}

		if(dataSource==2){  //数据来源于备机
			$('.timerange').html(_agentTimeRange);
			$(".takeoverTimepointDes").html(_latestTimePoint);
			loadHostVolInfoTab(_client_vol_info);
			$('.takeovertimecollapseview').addClass("collapsed");
			$(".takevoertimeview").attr({'data-toggle':'','aria-expanded':'false'});

			$('#takeover_timepoint_view').removeClass("in");
			$('#volTargetMountPoint').text(LANG.UI_VOL_CDP_TAKEOVER_TARGET_VOL);
			$('#takevoerTimepointDiv').css("pointer-events","none");
		}else{
			$('#volTargetMountPoint').text(LANG.UI_VOL_CDP_TAKEOVER_TARGET_MOUNT);

			$(".takevoertimeview").attr({'data-toggle':'collapse','aria-expanded':'true'});
			$('.takeovertimecollapseview').removeClass("collapsed");
			$('#takeover_timepoint_view').addClass("in");
			$('#takevoerTimepointDiv').css("pointer-events","auto")
		}
		getTakeOverTargetHost(dataSource);  //加载可供接管的备机
	}
	/**
	 * @function 校验时间有效性
	 * @description 校验输入时间是否在有效的时间数据集范围内
	 * @return Boolean
	 */
	var checkdate = function () {
		var reg = /^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/
		var str1 = $('#inputTakeoverTimepoint').val();
		if (!reg.test(str1)) {
			_timepointType = 0;   //时间点校验未通过
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
			$('#inputTakeoverTimepoint').val('');
			return false
		} else {
			verifyTimepointisValid($('#inputTakeoverTimepoint').val())
		}
	}
	//校验选择时间的有效性
	var verifyTimepointisValid = function(timepoint){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		var params = JSON.stringify({
			agent_uuid:agent_uuid,
			node_uuid:_createTaskMsgdata.node,
			timepoint:timepoint,
			vol_uuid:'',
			task_type:CONF.TASK_TYPE['VOL_CDP_TAKEOVER'],
			task_uuid:_createTaskMsgdata.task_uuid
		});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'verifyTimepointisValid',p:params}, function(d){
			var data = JSON.parse(d);
			var verify_result = "";
			if(data.length==0){
                UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
                $('#timePointValidity').css('color', "#F3565D");
                $('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
                clearTable(_takeoverVolDatatable,"recoveryVolTable");  //移除已填充的卷信息
                _timepointType = 0;
                return false;
			}
			if(data.time_vol_info.length==0){
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
				$('#timePointValidity').css('color', "#F3565D");
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
				clearTable(_takeoverVolDatatable,"recoveryVolTable");  //移除已填充的卷信息
				_timepointType = 0;
				return false;
			}

			var volInfo = data.time_vol_info;
			var encryptedFlag = data.encrypted_flag;
			var passwordAutoFlag = data.password_auto_flag;
			var timePointuuid =  data.timepoint_uuid;
			var osType = data.os_os_type;
			var newTimestr = data.new_time_str;
			var storageLocation = data.storage_location;
			var dataSource = $("#takeoverDataSourceSelect").val();
			if(dataSource==2 && storageLocation==3){  //手动选择查看备机，如果范围的数据是备机和备份服务器都存在，那么此时切换到备机
				storageLocation = 2;
			}
			if(storageLocation==2 || dataSource ==0){
				$('#takeoverDataSourceSelect').val('2');
				$('.takeovertimecollapseview').addClass("collapsed");
				$(".takevoertimeview").attr({'data-toggle':'','aria-expanded':'false'});
				$('#takeover_timepoint_view').removeClass("in");
				$('#takevoerTimepointDiv').css("pointer-events","none");
				$('#inputTakeoverTimepoint').val(newTimestr);
				getTakeOverTargetHost(storageLocation);  //加载可供接管的备机
			}else if(storageLocation ==1 || storageLocation ==3 || dataSource ==1){
				$('#takeoverDataSourceSelect').val('1');

				$("#takeoverDataSourceSelect option[value='1']").attr("selected", true);
				$("#takeoverDataSourceSelect").val("1");
				// $('#takeoverDataSourceSelect').attr("disabled",false);
				if(storageLocation==3){
					$('#takeoverDataSourceSelect').attr("disabled",false);
				}
				$('#takevoerTimepointDiv').css("pointer-events","auto");
				getAgentTimelineData();

			}
			isEncrypted(encryptedFlag,passwordAutoFlag,volInfo,timePointuuid);   //校验密码，填充数据

			switch(_eventNavTabsType){  //1：任意时间点；2：标签点；3：事件点；5：标签点和事件点
			case 1:
				chartCheckLabelPointInfo();
				chartCheckEventInfo();
				break;
			case 2:
				chartCheckEventInfo();
				break;
			case 3:
				chartCheckLabelPointInfo();
				break;
			}
    	});
		$('.takeoverTimepointDes').html (timepoint);
		$("#inputTakeoverTimepoint").val(timepoint);
		_createTaskMsgdata.takeover_timestamp = timepoint;
	}

	/**
	 * 填充可供接管的卷
	 */
	var loadRestoreVol = function(data){
		if(data.length>0){
			_createTaskMsgdata.node = data[0].node_uuid;
			_createTaskMsgdata.storage_uuid = data[0].storage_uuid;
			_timePointValidityValue = 1;
			verify_result = data[0].verify_result;
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
			$('#timePointValidity').css('color', "#45B6AF");
			$('#timeCheck').css('background-color','#27c3a4');

			if(_timepointType!=2 &&_timepointType!=3 && _timepointType!=4){
				_timepointType =1;
			}
		}else{
			_timePointValidityValue = 0;
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
			_timepointType = 0;
		}
		if(verify_result!=""){
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT_MESSAGE);
			$('#timePointValidity').attr("title",verify_result);
			$('#timePointValidity').css('color', "#F3565D");
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CHECK_TIME,  LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT+","+verify_result);
			_client_vol_info = [];
			_timepointType = 0;
		}else{
			_client_vol_info = data;
			if(_timepointType!=2 && _timepointType!=3 && _timepointType!=4){
				_timepointType =1;
			}
		}
		parseTimepointType();
		loadHostVolInfoTab(_client_vol_info); //装载可供恢复的卷信息
	}
	/**
	 * 判断是否加密，加密未解密设置关键参数，不能继续执行下一步操作
	 * 加密解密后给出界面图示
	 */
	var isEncrypted = function(encryptedFlag,passwordAutoFlag,volInfo,timePointuuid){
		backupSetLockStyle();  //备份集锁定样式
		if(encryptedFlag && !passwordAutoFlag){
			$('.backupsetlockdiv').show();
			if($.inArray(timePointuuid,allpointlist)==-1) {//判断是否和之前点击过的是一条链的
				bootbox.prompt({
					title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
					inputType: 'password',
					callback: function (result) {
						//同步ajax校验密码
						if(result == null) return;
						var checkflag = true;
						var params = {};
						params.timepointuuid = timePointuuid;
						params.inputpass =  $.trim(result);
						var p = JSON.stringify(params);
						$.ajax({
							type: "post",
							url: CONF.AJAXPATH,
							async: false,
							data:{m:CONF.M.FILE,f:'checkFSEncryptPass',p:p},
							success: function(d){
								var result = JSON.parse(d);
								if(result.flag){
									allpointlist.push(timePointuuid);
									loadRestoreVol(volInfo);  //填充可供恢复的卷
									backupSetUnlockStyle();   //解锁样式
									_backupSetIsLock = 1;
								}else{
									OPREL(d);
									checkflag = false;
									_backupSetIsLock = 2;
								}
							}
						});
						if(!checkflag) return false;
							return true;
					}
				});
			}else{
				_backupSetIsLock = 1;   //之前点击过的是一条链的备份点，无需解密
				backupSetUnlockStyle();   //解锁样式
				loadRestoreVol(volInfo);  //填充可供接管的卷
			}
		}else{
			_backupSetIsLock = 0;  //未加密备份点或自动生成密码备份点，无需解密
			$('.backupsetlockdiv').hide();
			backupSetUnlockStyle();   //解锁样式
			loadRestoreVol(volInfo);  //填充可供接管的卷
		}
	}

	/**
	 * 备份集锁样式
	 */
	var backupSetLockStyle = function(){
		$('.backupsetlockview').removeClass("fa-unlock");
		$('.backupsetlockview').addClass("fa-lock");
		$('.backupsetlockview').removeClass("colorgreen");
	}
	/**
	 * 备份集解锁样式
	 */
	var backupSetUnlockStyle = function(){
		$('.backupsetlockview').removeClass("fa-lock");
		$('.backupsetlockview').addClass("fa-unlock");
		$('.backupsetlockview').addClass("colorgreen");
	}
	/**
	 * 根据时间点的选择途径解析时间点类型
	 */
	var parseTimepointType = function(){
		var type = _timepointType;
		var timePointDesc = "--";
		var title = '';
		switch(type){
		case 1:
			timePointDesc = LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME; //任意时间点
			$('#inputTakeoverTimepoindes').html(timePointDesc);
			break;
		case 2:
			timePointDesc = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT; //标签点
        	title = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT_TITLE;
        	$('#inputTakeoverTimepoindes').html('<a id ="lablePointDetail" title = "'+title+'">'+timePointDesc+'</a>');
            $('#lablePointDetail').click(parseLablePointInfo);
			break;
		case 3:
			timePointDesc = LANG.UI_VOL_CDP_RECOVER_MARK_POINT; //事件点
        	title = LANG.UI_VOL_CDP_RECOVER_TIME_POINT_TITLE;
        	$('#inputTakeoverTimepoindes').html('<a id ="markPointDetail" title = "'+title+'">'+timePointDesc+'</a>');
            $('#markPointDetail').click(parseMarkPointInfo);
			break;
		case 4:
			var lablePointDes = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT;
        	var markPointDes = LANG.UI_VOL_CDP_RECOVER_MARK_POINT;
        	title = LANG.UI_VOL_CDP_RECOVER_MARK_POINT_TITLE;
        	$('#inputTakeoverTimepoindes').html('<a id ="lablePointDetail" title = "'+title+'">'+lablePointDes+'</a>&nbsp;&nbsp;\
        			<a id ="markPointDetail" title = "'+title+'">'+markPointDes+'</a>');
        	$('#markPointDetail').click(parseMarkPointInfo);
        	$('#lablePointDetail').click(parseLablePointInfo);
        	break;
        default:
        	$('#inputTakeoverTimepoindes').html(timePointDesc);

	    	$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
	    	$('#timePointValidity').css('color', "#F3565D");
	    	_timepointType = 0;
			break;
		}
	}
	//重置选择的时间
	var resetCheckTimepoint = function(){
		$('#closetakeoverTimepoint').on('click',function(){
			$('#inputTakeoverTimepoint').val('');
			_timepointType = 0;   //时间点校验未通过
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
			return false
		});
	}
	//判断值是否存在数据中
	var valueExistsInTheArray = function(arr,value){
	    if($.inArray(value,arr) >= 0){
	        return true;
	    }else{
	        false;
	    }
	}
	/**
	 * @function 根据参数渲染存储对象
	 * @description 渲染存储对象，根据参数控制存储对象可供配置项
	 */
	var applyStorageLocation = function(dataLocation,osType){
		var dataSourceBkServer = 1;  //备份服务器
		var dataSourceStandby = 2;  //备机
		var dataSourceBoth = 3;  //备份服务器和备机均有存放
		if(osType){
			_createTaskMsgdata.master_os_type = osType;  //选择数据源主机系统类型
		}

		var dataLocationValue = dataLocation[0];
		if(dataLocation.length>1){
			var bkServerValueExists = valueExistsInTheArray(dataLocation,dataSourceBkServer);
			var standbyValueExists = valueExistsInTheArray(dataLocation,dataSourceStandby);
			var BothValueExists = valueExistsInTheArray(dataLocation,dataSourceBoth);

			if(bkServerValueExists && !standbyValueExists && !BothValueExists){ //只有备份服务器
				dataLocationValue = 1;
			}else if(standbyValueExists && !bkServerValueExists && !BothValueExists){
				dataLocationValue = 2;
			}else if((bkServerValueExists && standbyValueExists) || BothValueExists){
				dataLocationValue = 3;
			}
		}
		var location = Number(dataLocationValue);
		var storageLocation = 1;
		switch(dataLocationValue){
    		case 1:
    			$("#takeoverDataSourceSelect option[value='2']").attr("selected", false);
    			$("#takeoverDataSourceSelect option[value='1']").attr("selected", true);
    			$("#takeoverDataSourceSelect").val("1");
    			$('#takeoverDataSourceSelect').attr("disabled",true);				
    			// 手动触发点击事件来展开collapse
				$("#takeover_timepoint_view").collapse('show');
				// 更新aria-expanded属性
				$('a[href="#takeover_timepoint_view"]').attr('aria-expanded', 'true').removeClass('collapsed');
    			$('#takevoerTimepointDiv').css("pointer-events","auto");
    			break;
    		case 2:
    			$("#takeoverDataSourceSelect option[value='1']").attr("selected", false);
    			$("#takeoverDataSourceSelect option[value='2']").attr("selected", true);
    			$("#takeoverDataSourceSelect").val("2");
    			$('#takeoverDataSourceSelect').attr("disabled",true);
    			$('.takeovertimecollapseview').addClass("collapsed");
    			$(".takevoertimeview").attr({'data-toggle':'','aria-expanded':'false'});
    			$('#takeover_timepoint_view').removeClass("in");
    			$('#takevoerTimepointDiv').css("pointer-events","none");
    			storageLocation = 2;
    			break;
    		case 3:
    			$("#takeoverDataSourceSelect option[value='1']").attr("selected", true);
    			$("#takeoverDataSourceSelect").val("1");
    			$('#takeoverDataSourceSelect').attr("disabled",false);

    			$(".takevoertimeview").attr({'data-toggle':'collapse','aria-expanded':'true'});
    			$('.takeovertimecollapseview').removeClass("collapsed");
    			$('#takeover_timepoint_view').addClass("in");
    			$('#takevoerTimepointDiv').css("pointer-events","auto");
    			break;
		}
		getTakeOverTargetHost(storageLocation);  //加载可供接管的备机
	}
	/**
	 * @function 获取客户端任意时间点信息
	 * @description  通过选择的客户端uuid获取其对应的备份集
	 */
	var getClientAnyTimePointInfo = function (dataSource){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		var prams = JSON.stringify({
			agent_uuid:agent_uuid,
			node_uuid:_createTaskMsgdata.node,
			data_source:dataSource,
			task_type:CONF.TASK_TYPE.VOL_CDP_TAKEOVER,
			task_uuid:_createTaskMsgdata.task_uuid
		});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getClientBackupSetInfo',p:prams}, function(d){
			var data = JSON.parse(d);
			_latestTimePoint = data[0].new_timestamp;
			_agentTimeRange = data[0].time_range;
			_timePointValidityValue = 1; //最新时间点可以确保是有效时间点
			var storageLocation = data[0].storage_location;
			if(dataSource==2 && storageLocation==3){  //手动选择查看备机，如果范围的数据是备机和备份服务器都存在，那么此时切换到备机
				storageLocation = 2;
			}
			if(storageLocation==2 && dataSource ==0){
				$('#takeoverDataSourceSelect').val('2');
				$("#takeoverDataSourceSelect option[value='1']").attr("selected", false);
				$("#takeoverDataSourceSelect option[value='2']").attr("selected", true);
				$('.takeovertimecollapseview').addClass("collapsed");
				$(".takevoertimeview").attr({'data-toggle':'','aria-expanded':'false'});
				$('#takeover_timepoint_view').removeClass("in");
				$('#takevoerTimepointDiv').css("pointer-events","none");
				getTakeOverTargetHost(storageLocation);  //加载可供接管的备机，
			}else if(storageLocation ==1 || storageLocation ==3 || dataSource ==1){
				$('#takeoverDataSourceSelect').val('1');
				$("#takeoverDataSourceSelect option[value='1']").attr("selected", true);
				$("#takeoverDataSourceSelect").val("1");
				// $('#takeoverDataSourceSelect').attr("disabled",false);
				if(storageLocation==3){  //数据存在备份服务器和备机
					$('#takeoverDataSourceSelect').attr("disabled",false);
				}

				// 手动触发点击事件来展开collapse
				$("#takeover_timepoint_view").collapse('show');
				// 更新aria-expanded属性
				$('a[href="#takeover_timepoint_view"]').attr('aria-expanded', 'true').removeClass('collapsed');
				$('#takevoerTimepointDiv').css("pointer-events","auto");
				getAgentTimelineData();
			}

			var encryptedFlag = data[0].encrypted_flag;
			var passwordAutoFlag = data[0].password_auto_flag;
			var timePointuuid =  data[0].timepoint_uuid;
			_createTaskMsgdata.timepoint_uuid = timePointuuid;
			if(encryptedFlag && !passwordAutoFlag){
				_backupSetIsLock = 2;  //备份集加密码且未解密
				$('.backupsetlockdiv').show();
			}else{
				_backupSetIsLock = 0;  //未加密备份点或自动生成密码备份点，无需解密
				$('.backupsetlockdiv').hide();
			}
			var verify_result = data[0].vol_set[0].verify_result;
			if(verify_result!=""){
				_timePointValidityValue = 0;   //时间点校验未通过
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT_MESSAGE);
				$('#timePointValidity').attr("title",verify_result);
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CHECK_TIME, LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT+","+verify_result);
				_client_vol_info = [];
			}else{
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
				$('#timePointValidity').css('color', "#45B6AF");

				$('.timerange').html(_agentTimeRange);
				$('.takeoverTimepointDes').html(_latestTimePoint);
				$('#inputTakeoverTimepoint').val(_latestTimePoint);
				_createTaskMsgdata.takeover_timestamp = _latestTimePoint;

				_createTaskMsgdata.node = data[0].node_uuid;
				_createTaskMsgdata.storage_uuid = data[0].storage_uuid;

				_client_vol_info = data[0].vol_set;
			}
			_createTaskMsgdata.node_uuid = data[0].node_uuid;
			loadHostVolInfoTab(_client_vol_info); //装载可供接管的卷信息

    	});
	}
	/**
	 * 加载已获取的主机卷信息,写入需要接管的tab
	 */
	var loadHostVolInfoTab = function(client_vol_info){
		debugger;
		var volumeInfo = client_vol_info;
		_allSysVolInfoArray = [];  //系统相关卷信息 
		_volIsUefi = false;
		for(var i=0;i<volumeInfo.length;i++){
			if(volumeInfo[i].is_boot == CONF.FLAG.SET || volumeInfo[i].system_volume ==true ){
				_allSysVolInfoArray.push(volumeInfo[i].vol_name);
			}
			if(volumeInfo[i].is_uefi){
				_volIsUefi = true;
			}
		}
		if(!gridInitFlag){	//初始化表格
			var dataTableOpt = {
				'showLoading':false,
				'columnDefs' : [{
					'orderable': false,
					'targets': [0,1,2,3,4],
				}],
				"pageLength": 25,
				"aLengthMenu": [25, 50, 100,200,500],
				"order": [
					[1, "desc"]
				],
			};
			volGrid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPTAKEOVER,f:'getDataSourceVolInfo',p:{volinfo:volumeInfo}};
			volGrid.setAjaxParam(dataPar);
			volGrid.init({src: $("#takeOverVolTable"),checkbox:true,dataTable:dataTableOpt,onDataLoad:''});
			gridInitFlag = true;
		}else{	//刷新表格
			volGrid.getRefresh({volinfo:volumeInfo}, undefined, true);
		}
		_takeoverVolDatatable =  $('#takeOverVolTable').DataTable();
	}

	/**
	 * @function 获取目标主机卷对应的标签点信息
	 * @description 通过选择的目标主机UUID及卷UUID获取其对应的标签点信息
	 */
	var loadCheckVolTagPoint = function (host_uuid,isRefreshLabel){
		if(!tagGridFlag){		//初始化表格
			var dataTableOpt = {
				'columnDefs' : [{
	                'orderable': false,
	                'targets': [0,1,3]
				}],
				"order": [
	                [2, "desc"]
	            ],
	    	};
			tagGrid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPRECOVER,f:'getVolTagPointInfo',p:{host_uuid:host_uuid,vol_uuid:"",time_list:_timeList,task_uuid:_createTaskMsgdata.task_uuid}};
			tagGrid.setAjaxParam(dataPar);
			tagGrid.init({src: $("#takeoverTagpointTable"), checkbox:false, dataTable:dataTableOpt, onDataLoad:takeoverTabSingleSelect});
			tagGridFlag = true;
		}else{
			tagGrid.getRefresh({host_uuid:host_uuid,vol_uuid:"",time_list:_timeList}, undefined, true);//刷新表格
			if(isRefreshLabel){
				UIToastr.showSuccess(LANG.UI_VOL_CDP_JOB_UPDATE_TAG_POING, LANG.UI_VOL_CDP_JOB_UPDATE_TAG_POING + LANG.UI_VOL_CDP_OPERATION_SUCCESS);
			}
		}
	}

	/**
	 * @function 获取目标主机卷对应的事件信息
	 * @description 通过选择的目标主机UUID及卷UUID获取其对应的事件信息
	 */
	var loadCheckAgentEventInfo = function(host_uuid,refreshEvent){
		if(!eventGridFlag){		//初始化表格
			var dataOpt = {
				'columnDefs' : [{
	                'orderable': false,
	                'targets': [0,2,3,4]
				}],
				"order": [
	                [1, "desc"]
	            ],
	    	};
			eventGrid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPBACKUPSET,f:'getAgentEventInfo',p:{host_uuid:host_uuid,vol_uuid:"",time_list:_timeList,task_uuid:_createTaskMsgdata.task_uuid}};
			eventGrid.setAjaxParam(dataPar);
			eventGrid.init({src: $("#agentEventInfoTable"), checkbox:false, dataTable:dataOpt, onDataLoad:eventInfoTabSingleSelect});
			eventGridFlag = true;
		}else{
			eventGrid.getRefresh({host_uuid:host_uuid,vol_uuid:"",time_list:_timeList}, undefined, true);  //刷新表格
			if(refreshEvent){
				UIToastr.showSuccess(LANG.UI_VOL_CDP_JOB_UPDATE_EVENT_INFO, LANG.UI_VOL_CDP_JOB_UPDATE_EVENT_INFO + LANG.UI_VOL_CDP_OPERATION_SUCCESS);
			}
		}
	}
	//绑定选项卡，设置切换隐藏和显示的View
	var InitTabShowEvent = function(){
	    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	        var tab = e.target;
	        if(tab.hash == "#takeoverAnytime"){
	        	$('#takeoverAnytime').show();
	        	$('#takeoverTagPoint').hide();
	        	$('#agentEventInfo').hide();
	        }else if(tab.hash == "#takeoverTagPoint"){
	        	$('#takeoverAnytime').hide();
	        	$('#takeoverTagPoint').show();
	        	$('#agentEventInfo').hide();
	        	$('#refreshLabelPointInfo').show();
	        	$('#getAllLabelPointInfo').hide();
	        }else if(tab.hash == "#agentEventInfo"){
	        	$('#refreshEventInfo').show();
	        	$('#getAllEventInfo').hide();
	        	$('#takeoverAnytime').hide();
	        	$('#takeoverTagPoint').hide();
	        	$('#agentEventInfo').show();
	        }
	    });
	}

	//刷新标签点信息
	var refreshLabelPointInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		loadCheckVolTagPoint(agent_uuid,isRefreshLabel = true);
		$('#inputTakeoverTimepoint').val("");
		_timepointType=0;
		$('#timePointValidity').css('color', "#F3565D");
		$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
		$('.backupsetlockdiv').hide();
	}

	//图表上点击标签点
	var chartCheckLabelPointInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		loadCheckVolTagPoint(agent_uuid,isRefreshLabel = false);
	}

	//从过滤的标签点信息切换到所有标签点列表
	var getAllLabelPointInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		_timeList = [];
		loadCheckVolTagPoint(agent_uuid,isRefreshLabel = false);
	}

	//图表上点击事件点
	var chartCheckEventInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		loadCheckAgentEventInfo(agent_uuid,refreshEvent = false);
	}
	//刷新客户端事件信息
	var refreshEventInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		loadCheckAgentEventInfo(agent_uuid,refreshEvent = true);
		$('#inputTakeoverTimepoint').val("");
		_timepointType=0;
		$('.backupsetlockdiv').hide();
		$('#timePointValidity').css('color', "#F3565D");
		$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
	}
	//从过滤掉的事件信息切换到所有事件信息列表
	var getAllEventInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		_timeList = [];
		loadCheckAgentEventInfo(agent_uuid,refreshEvent = false);
	}
	var getTabInfo = function() {
		var attrId = $(this).attr("id")
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		switch(attrId){
			case "takeoverTagPointLi":
				if(_timepointType != 2 && _timepointType !=4) {
					_timeList = []
					loadCheckVolTagPoint(agent_uuid,isRefreshLabel = false);
				}
				break;
			case "agentEventInfoLi":
				if(_timepointType != 3 && _timepointType !=4) {
					_timeList = []
					loadCheckAgentEventInfo(agent_uuid,refreshEvent = false);
				}
				break;
		}
	}
	/**
	 * @function 实现标签点表格checkbox 单选
	 */
	var takeoverTabSingleSelect  = function (){
		$('#takeoverTagpointTable' +' tbody').unbind('click').on('click', 'tr', function () {
			//1.取消所有选中项
			$("#takeoverTagpointTable").parent().find('input').prop("checked", false);
			//2.选中本行
			$("#takeoverTagpointTable").find('input').prop("checked", true);
			//得到这个点的备份信息
			var value = $(this).find('input').val();
			if(!value){
				return;
			}
			_eventNavTabsType = 2;
			verifyTimepointisValid(value);
		});

		$('#takeoverTagpointTable').find('tbody > tr').find('input[type="checkbox"]').off().on('click', function(){
			//取消所有其他选中的
			var checkBoxs = $('#takeoverTagpointTable').find('tbody > tr').find('input[type="checkbox"]');
			for(var i=0; i<checkBoxs.length; i++){
				var span = checkBoxs[i].parentNode;
				$(span).prop("class", "");
				$(checkBoxs[i]).prop("checked", false);
			}
			var checkBox = $(this);
			var checked = checkBox[0].checked;
			if(checked){
				var span = checkBox[0].parentNode;
				$(span).prop("class", "");
				$(checkBox).prop("checked", false);
			}else{
				var span = checkBox[0].parentNode;
				$(span).prop("class", "checked");
				$(checkBox).prop("checked", true);
			}
		})
		//var table = $('#takeoverTagpointTable').DataTable();
		//实现选中行时选中选中复选框

		// $('#takeoverTagpointTable').find('tbody > tr').unbind('click').on('click', function(){
		// 	var checkBoxs = $('#takeoverTagpointTable').find('tbody > tr').find('input[type="checkbox"]');	//取消所有其他选中的
		// 	for(var i=0; i<checkBoxs.length; i++){
		// 		var span = checkBoxs[i].parentNode;
	    // 	    $(span).prop("class", "");
		//         $(checkBoxs[i]).prop("checked", false);
		// 	}
		// 	var checkBox = $(this);
	    //     var checked = checkBox[0].checked;
	    //     if(checked){
	    // 		var span = checkBox[0].parentNode;
	    // 	    $(span).prop("class", "");
		//         $(checkBox).prop("checked", false);
	    // 	}else{
	    // 		var span = checkBox[0].parentNode;
	    // 	    $(span).prop("class", "checked");
		//         $(checkBox).prop("checked", true);
	    // 	};
	    // 	var selectNodes = tagGrid.getSelectedRows();
	    // 	if(selectNodes.length!=0){
	    // 		_eventNavTabsType = 2;  //1：任意时间点；2：标签点；3：事件点
	    // 		verifyTimepointisValid(selectNodes[0]);
	    // 	}
		// });
	}
	/**
	 * @function 实现事件信息表格checkbox 单选
	 */
	var eventInfoTabSingleSelect  = function (){
		$('#agentEventInfoTable' +' tbody').unbind('click').on('click', 'tr', function () {
			//1.取消所有选中项
			$("#agentEventInfoTable").parent().find('input').prop("checked", false);
			//2.选中本行
			$("#agentEventInfoTable").find('input').prop("checked", true);
			//得到这个点的备份信息
			var value = $(this).find('input').val();
			if(!value){
				return;
			}
			_eventNavTabsType = 3;
			verifyTimepointisValid(value);
		});
		$('#agentEventInfoTable').find('tbody > tr').find('input[type="checkbox"]').off().unbind('click').on('click', function(){
			var checkBoxs = $('#agentEventInfoTable').find('tbody > tr').find('input[type="checkbox"]');	//取消所有其他选中的
			for(var i=0; i<checkBoxs.length; i++){
				var span = checkBoxs[i].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBoxs[i]).prop("checked", false);
			}
			var checkBox = $(this);
	        var checked = checkBox[0].checked;
	        if(checked){
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "");
		        $(checkBox).prop("checked", false);
	    	}else{
	    		var span = checkBox[0].parentNode;
	    	    $(span).prop("class", "checked");
		        $(checkBox).prop("checked", true);
	    	};
	    	// var selectNodes = eventGrid.getSelectedRows();
	    	// if(selectNodes.length!=0){
	    	// 	_eventNavTabsType=3;  //1：任意时间点；2：标签点；3：事件点
	    	// 	var eventTime = recombinationEventTime(selectNodes[0]);
	    	// 	verifyTimepointisValid(eventTime);
	    	// }
		});
	}

	/**
	 * 重组事件时间
	 * 处理事件时间超过备份集时间范围时，赋值备份集最新时间
	 */
	var recombinationEventTime = function(value){
		var eventTime = value;
		var eventTimeValue = new Date(eventTime.replace("-", "/").replace("-", "/"));  //选择的事件时间
		var latestTimeValue = new Date(_latestTimePoint.replace("-", "/").replace("-", "/"));   //客户端备份集最新时间
		if(eventTimeValue>latestTimeValue){
			eventTime = _latestTimePoint;
		}
		return eventTime;
	}

	/**
	 * @function 获取接管目标主机
	 */
	var getTakeOverTargetHost = function(dataSource){
		_takeoverTargetData = [];
		var params = JSON.stringify({
			node_uuid:_createTaskMsgdata.node,
			master_uuid:_createTaskMsgdata.master_agent_uuid,
			master_os_type:_createTaskMsgdata.master_os_type,
			task_uuid:_createTaskMsgdata.task_uuid,
			data_source:dataSource
		});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getTakeoverTargetHost',p:params}, function(d){
			var data = JSON.parse(d);
			if(!data.length) return;
			_takeoverTargetData = data;
			var current = $('#takeoverTargetHostSelect').children("option:selected").val()
			var hostSelect = $('#takeoverTargetHostSelect');
			hostSelect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid).attr('data-mountpoint', data[i].mount_info).attr('data-netmodel',data[i].net_model).attr('data-hostonlineflag',data[i].online_flag).attr('os-type',data[i].os_type);
				hostSelect.append(option);
			}
			$('#takeoverTargetHostSelect').val(current )
    	});

		$('#takeoverTargetHostSelect').unbind('change').bind('change', takeoverSelectChange);
		$('#takeoverTargetHostSelect').unbind('click').click(function(){
			if(_takeoverTargetData.length<2){
				UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE, LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE_MESSAGE);
				return false;
			}
		});
	}
	/**
	 * 选择接管目标机器
	 */
	var takeoverSelectChange = function(){
		_standbyNetworkInfo = [];
		var tgHostUuid = $('#takeoverTargetHostSelect').val();
		var hostMountpointStr = $(this).find("option:selected").attr("data-mountpoint");
		var appScened = $("#changeAppScened").val();  //应用场景
		var standbyHostMode = $('#takeoverStandbyHostMode').val();  //接管类型
		_isDataSourceHostMountAndVerify = false; 

		if(tgHostUuid=="" || tgHostUuid==0){
			return;
		}
		for(var i=0;i<_takeoverTargetData.length;i++){
			if(tgHostUuid ==_takeoverTargetData[i].uuid){
				_takeoverInTask = _takeoverTargetData[i].in_task;
				_takeoverTaskName = _takeoverTargetData[i].task_name;
				var inTaskuuid = _takeoverTargetData[i].task_uuid;
				var inTaskType = _takeoverTargetData[i].task_type;
				var takeoverAgentRole = _takeoverTargetData[i].takeover_agent_role;
				//判断当前应用场景，接管类型和选中客户端
				if(!!_takeoverInTask){  //判断目标节主机是否已配置作业
					if(appScened == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL && standbyHostMode ==  CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT && takeoverAgentRole != CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){
						_isDataSourceHostMountAndVerify = true;  //允许配置目标主机与数据源主机同设备的主机作为目标主机，且应用场景为验证，验证方式为挂载时允许选择主机
						continue;
					}else{
						$('#takeoverTargetHostSelect option:first').prop('selected', true);
						$('.targetHostUsedPartition').html("");
						$('.targetHostUsedPartition').css("display","none");
						UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE, LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE_MESSAGE2+_takeoverTaskName+LANG.UI_VOL_CDP_RECOVER_TARGET_HOST_MESSAGE3);
						return false;
					}
					
				}
			}
		}
		_networkFlag = false;
		var netModel =  $(this).find("option:selected").attr("data-netmodel");
		
		osTypeTarget = $(this).find("option:selected").attr("os-type");
		//需求修改：接管或验证类型为挂载时，备机为代理客户端时，需要配置网络映射
		if(netModel == 2 && standbyHostMode ==1){  
			_networkFlag = true;
		}
		var targetHost = $("#takeoverTargetHostSelect").find("option:selected").text();
		$('.targetHostUsedPartition').html(LANG.UI_VOL_CDP_TAKEOVER_TARGET_HOST + targetHost + ",  "+ LANG.UI_VOL_CDP_TAKEOVER_MOUNT_HAS_BEEN_USED +": "+hostMountpointStr);
		updateAgentInfo(tgHostUuid);
	}

	/**
	 * 更新客户端信息，包括客户端对应的卷信息和应用信息
	 */
	var updateAgentInfo = function(agent_uuid){
		Metronic.blockUI({target: "#voltakeovercontent",animate: true});
		var data = JSON.stringify({agent_uuid:agent_uuid});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'updateAgentInfo',p:data}, function(d){
			Metronic.unblockUI("#voltakeovercontent");
			if(OPREL(d)){
				var appScenedValue = $('#changeAppScened').val();
				if(appScenedValue== CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){
					$('.applianceselectview').show();
				}else{
					$('.applianceselectview').hide();
				}

				_standbyIsConf = true;
				_standbyHostMountPoint = $("#takeoverTargetHostSelect").find("option:selected").attr("data-mountpoint");  //备机已占用的挂载点
				_createTaskMsgdata.takeover_object.takeover_standby_agent_uuid = agent_uuid;

				$('.targetHostUsedPartition').css("display","block");

				loadStandbyNetworkInfo(agent_uuid);
				//TODO 此处应该需要加载默认可供选择的select,并且可编辑.此处留在后续完善期间处理.备注时间:2021-12-23 11:04:50;
    		}
			var data = JSON.parse(d);
			if(!data.re){
				$('#takeoverTargetHostSelect option:first').prop('selected', true);
				// $('.targetHostUsedPartition').html("");
				$('.targetHostUsedPartition').css("display","none");
				_standbyIsConf = false;
				return false;
			}else{
				getTakeOverTargetHost();
			}
		})
	}

	/**
	 * 获取主机网卡信息
	 */
	var loadAgentNetworkInfo = function (agent_uuid){
		var data = {};
		data.agent_uuid = agent_uuid;
		data.task_uuid = _createTaskMsgdata.task_uuid;
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'getBackupAgentNetworkInfo',p:jsonData}, function(d){
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


	/**
	 * ========================================================================================================
	 * ===============================================时间轴相关函数================================================
	 */
	//获取需要加载的时间间隔类型
	var changeTimeIntervalType = function(){
		var timeIntervalType = $("#changeTimeInterval").val();
		$('.selecttakeovertimeview').hide();
		if(timeIntervalType==7){
			$('.selecttakeovertimeview').show();
			$('#selectTakeoverStartTime').val('');
			$('#selectTakeoverEndTime').val('');
			loadIntervalTimePicker(); //自定义区间时间控件
			$('#resetSelectTimePoint').on('click',function(){
				$('#selectTakeoverStartTime').val('');
				$('#selectTakeoverEndTime').val('');
			});
			$('#confirmSelectTimePoint').unbind('click').on('click',getInputEndTimeValidity);
			// var time_data = [];
			_chartStartPercent = 0;
			// loadTimelineChart(time_data);
		}else{
			_chartStartPercent = 70;
			_timeInterval = timeIntervalType;
			getAgentTimelineData();
		}
	}

	/**
	 * 获取结束时间有效性.
	 * @content 获取结束时间,判断结束时间是否大于起始时间.校验结束,计算时间区间类型,根据类型获取对应数据;
	 */
	var getInputEndTimeValidity = function(){
		var startTime = $("#selectTakeoverStartTime").val();
		var endTime =$('#selectTakeoverEndTime').val();
		if (startTime.length > 0 && endTime.length > 0) {
			var start=new Date(startTime.replace("-", "/").replace("-", "/"));
			var end=new Date(endTime.replace("-", "/").replace("-", "/"));
			var timeDiff = end-start; //单位毫秒

			if (start  > end || timeDiff==0) {
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_TIME_FRAME_SELECT,LANG.UI_VOL_CDP_RECOVER_TIME_FRAME_SELECT_MESSAGE);
				return;
			}
			_timeInterval = timeIntervalUnit(timeDiff);
			getAgentTimelineData();
		}else{
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_TIME_FRAME_SELECT,LANG.UI_VOL_CDP_RECOVER_TIME_FRAME_SELECT_MESSAGE2);
			return;
		}

	}
	/**
	 * 通过时间间隔计算时间区间类型
	 */
	var timeIntervalUnit = function(value){
		var secondTimeDiff = value/1000; //单位秒
		var unit;
		if(secondTimeDiff<=600){ //10分钟内
			unit =1;
		}else if(secondTimeDiff>600 && secondTimeDiff<=3600){ //1小时内
			unit =2;
		}else if(secondTimeDiff>3600 && secondTimeDiff<=86400){ //最近1天
			unit =3;
		}else if(secondTimeDiff>86400 && secondTimeDiff<=604800){ //最近7天
			unit =4;
		}else if(secondTimeDiff>604800 && secondTimeDiff <=2592000){ //最近1个月
			unit =5;
		}else if(secondTimeDiff>2592000 && secondTimeDiff <=7776000){ //最近3个月
			unit =6;
		}else if(secondTimeDiff >7776000){
			unit =6;
		}else {
			unit = 1;
		}
		return unit;
	}

	//初始化时间控件
	var loadIntervalTimePicker = function(){
		$(".takeovertimeview").datetimepicker({
			language:  'zh-CN',
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
        });
	}
	/**
	 * 渲染时间轴chart
	 */
	var loadTimelineChart = function(rawData){
		// 动态设置echart图高度和宽度以适应分辨率
		var portletBodyWidth = 0;  //chart 宽度
		var portletBodyHeight = 0; //chart 高度
		var changeTimeInterval = $('#changeTimeInterval').val();  //获取当前时间范围类型
		var takeoverDataSourceSelect = $('#takeoverDataSourceSelect').val();  //备份对象
		if(takeoverDataSourceSelect==2){  //备份对象为备机,不需要加载时间轴chart信息，避免切换到主机时出现chart渲染异常的问题
			return;
		}
		if(_hisPortletBodyWidth != 0){  //非首次加载，chart坐标取历史配置
			portletBodyWidth = _hisPortletBodyWidth;
			portletBodyHeight = _hisportletBodyHeight;
		}else{  //首次加载获取当前body尺寸，赋值历史配置
			portletBodyWidth = $('#tabPortletBody').width();
			portletBodyHeight = $('#tabPortletBody').height();
			_hisPortletBodyWidth = $('#tabPortletBody').width();
			_hisportletBodyHeight = $('#tabPortletBody').height();
		}

		if(_hisChangeTimeInterval!= changeTimeInterval){
			echarts.init(document.getElementById('volCdpTimeline')).dispose(); //销毁chart
			_hisChangeTimeInterval = changeTimeInterval;
		}else{ 
			return;
		}
		if(_hisChangeTimeInterval==0){  //历史间隔未配置，首次加载，赋值当前选中时间间隔，重置chart，清空历史配置
			_hisChangeTimeInterval = changeTimeInterval;
			echarts.init(document.getElementById('volCdpTimeline')).dispose(); //销毁chart
		}
		$('#volCdpTimeline').css({"width": portletBodyWidth + 'px', "height": (portletBodyHeight - 34) + 'px'});
		var chartDom = document.getElementById('volCdpTimeline');
		myChart = echarts.init(chartDom);
		var option;
		var dates = rawData.map(function (item) {
			var xDataInfo = item[0];
			return item[0];
		});
		var data = rawData.map(function (item) {
			return [item[0],item[1],item[2],item[3]];
		});
		var size = [];
		var lablePointNumber = 0;
		for (var i=0;i<rawData.length;i++){
			if(rawData[i][2]!=0){
				size.push(7);  	//圆点大小
				lablePointNumber = rawData[i][2];
			}else{
				size.push(0);
			}
		}
		var start = rawData.length - 500;
		var end = rawData.length-1;
		var option = {
			legend: {
				data: [LANG.UI_VOL_CDP_RECOVER_IO_FLOW],
				inactiveColor: '#777',
			},
			tooltip: {
				trigger: 'axis',
				axisPointer: {
					animation: false,
					type: 'cross',
					lineStyle: {
						color: '#8e8e8e',
						width: 1,
						opacity: 1
					}
				},
				formatter: function (params, ticket, callback) {
    	        	var value = params[0].data;
					var flowValue = flowChartUnitStr(value);
					return flowValue;
    	        }

			},
			xAxis: {
				type: 'category',
				boundaryGap : false,
				data: dates,
				axisLine: { lineStyle: { color: '#8e8e8e' } }
			},
			yAxis: {
				scale: true,
				type : 'value',
				axisTick: {
			        show: true
			    },
			    axisLine: {
		              show: true
		        },
				//axisLine: { lineStyle: { color: '#8e8e8e' } },
				splitLine: { show: true },
				axisLabel : {
					lineStyle: { color: '#8e8e8e' },
	                formatter: function(value, index){
						var valueStr = flowChartUnitStr(value);
						return valueStr;
	                }
	            },
				name: LANG.UI_VOL_CDP_RECOVER_DATA_FLOW
			},
			grid: {
				bottom: 80
			},

			dataZoom: [
				{
					height: 18,//滚动条高度
					moveHandleSize: 3, //滚动Handle条高度
					textStyle: {
						color: '#8392A5'
					},
					fillerColor:"rgba(51, 175, 125,0.1)",
					dataBackground: {
						areaStyle: {
							color: '#86dac2'
						},
						lineStyle: {
							opacity: 0.8,
							color: '#86dac2'
						}
					},
					brushSelect: true,
					start:_chartStartPercent, //dataZoom的起始百分比,此处暂定70%,根据实际效果调整
                    end:_chartEndPercent, //dataZoom的结束位置,此处暂定100%
				},{
					type: 'inside',
				}
			],
			series: [
				{
					name: LANG.UI_VOL_CDP_TAKEOVER_IO_FLOW,
					type: 'line',
					data: calculateMA(1, data),
					smooth: true,
					showSymbol: true,
					symbol: 'circle',     //设定为实心点
					symbolSize: 10,       //设定实心点的大小
					itemStyle: {
    	                normal: {
    	                    lineStyle: {
    	                      width:1
    	                    }
    	                }
    	            },
					areaStyle: {normal: {
    	            	 color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
    	                     offset: 0,
    	                     color: '#86dac2'
    	                 }, {
    	                     offset: 1,
    	                     color: '#fff'
    	                 }])
    	            }},
					//使用回调函数,重绘圆点(可根据自己逻辑添加相应的判断，让指定的坐标显示/隐藏圆点)
					symbolSize:(rawValue, params) => {
			            params.symbolSize = size[params.dataIndex];
			            return params.symbolSize;
				    },
					markPoint: {
						symbolSize: 18,
						data: markPointData(data),
						label:{
							formatter:'' 	//formatter:'{c}Mb/s'
						}
					}
				}
			],
			color: ['#86dac2']
		};
		myChart.setOption(option);

		//时间轴流量范围内的点击事件
		myChart.getZr().on("click", params => {	// 获取点击位置
			_timepointType = 0;
			const pointInPixel = [params.offsetX, params.offsetY];
			if (myChart.containPixel("grid", pointInPixel)) {	// 获取点击位置的坐标系[x，y]
		        const xIndex = myChart.convertFromPixel({seriesIndex: 0}, [params.offsetX, params.offsetY])[0];
				this.menJinTableIndex = xIndex;
			    var checkTime = rawData[xIndex][0];
			    //写入input框
		    	$('#inputTakeoverTimepoint').val(checkTime);
		    	_eventNavTabsType = 1;  //任意时间点
				if(rawData[xIndex][2]!=0 && rawData[xIndex][3]!=0) {
					_timepointType = 4
				} else if(rawData[xIndex][2]!=0 && rawData[xIndex][3]==0) {
					_timepointType = 2
				} else if(rawData[xIndex][2]==0 && rawData[xIndex][3]!=0) {
					_timepointType = 3
				} else {
					_timepointType = 1
				}
		    	$('#inputTakeoverTimepoindes').html(LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME); //任意时间点
				verifyTimepointisValid(checkTime);
		    }
			else {
				//checkpoint.innerText = '--';
			}
	  	});
		//组装标事件点坐标信息
		function markPointData(data){
			var itemStyle = {color: '#2ea1fc'};
			var result = [];
			for(var i =0;i<data.length;i++){
				var coord = data[i][0];
				var isMarkPoint = data[i][3];
				if(isMarkPoint>0){
					var obj = {};
					var coordArray = [];
					coordArray.push(coord);
					coordArray.push(data[i][1]);
					coordArray.push(data[i][2]);
					coordArray.push(data[i][3]);
					obj.value = data[i][1];
					obj.coord = coordArray;
					obj.itemStyle = itemStyle;
					result.push(obj);
				}
			}
			return result;
		}
		function calculateMA(dayCount, data){
			var result = [];
			var len = data.length;
			for (var i = 0; i < len; i++) {
				var sum = 0;
				for (var j = 0; j < dayCount; j++) {
					sum += data[i - j][1];
				}
				result.push(sum / dayCount);
			}
			return result;
		}
		//获取三个DOM元素
        var checkpoint = document.getElementById('inputTakeoverTimepoint');
        var eConsole = function(param){
        	_timepointType = 0;
            if(param.value > 0) {
                checkpoint.innerText = param.value;
                if($.isPlainObject(param.data)){
                	var markPointNumber = param.data.coord[3];
                	var lablePointNumber = param.data.coord[2];
                	var checkTime = param.data.coord[0];
                	$('#inputTakeoverTimepoint').val(checkTime);
                }else{
                	var markPointNumber =0;
                }
                var timepointDes = LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME;
                var title = LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME_TITLE;

                if(lablePointNumber!=0 && markPointNumber!=0){
               	 	_timepointType=4;  //标签点和事件点
                }else if(lablePointNumber==0 && markPointNumber!=0){
                	_timepointType = 3; //事件点
                }else if(lablePointNumber!=0 && markPointNumber==0){
                	_timepointType = 2; //标签点
                }else{
                	_timepointType = 1; //任意时间点
                }
            }else {
                checkpoint.innerText = '--';
            }
        }
        //在这里做一个点击事件的监听，绑定的是eConsole方法
        myChart.on('click',eConsole);

	}

	/**
	 * 流量图单位换算
	 * @param value
	 */
	var flowChartUnitStr = function(value){
		var timeUnit = parseInt(_timeInterval);
		if(value >=1024*1024){
			var timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB;
			switch (timeUnit){
				case 1:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB;
					break;
				case 2:
				case 3:
				case 4:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB_MIN;
					break;
				case 5:
				case 6:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_GB_HOUR;
					break;
			}
			return Math.round(value /1024/1024) +timeUnitStr;
		}else if(value >= 1024){
			var timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB;
			switch (timeUnit){
				case 1:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB;
					break;
				case 2:
				case 3:
				case 4:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB_MIN;
					break;
				case 5:
				case 6:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_MB_HOUR;
					break;
			}
			return Math.round(value / 1024) +timeUnitStr;
		}else{
			var timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB;
			switch (timeUnit){
				case 1:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB;
					break;
				case 2:
				case 3:
				case 4:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB_MIN;
					break;
				case 5:
				case 6:
					timeUnitStr = LANG.UI_VOL_CDP_RECOVER_DATA_FLOW_UNIT_KB_HOUR;
					break;
			}
			return value + timeUnitStr;  //返回数据流量最小单位为KB
		}
	}


	//解析选中时间的事件信息
	var parseMarkPointInfo = function(){
		var confTime = $('#inputTakeoverTimepoint').val();
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		_timeList = [];
		var confInfo = {};
		confInfo.confTime = confTime;
		confInfo.timeInterval = _timeInterval;
		_timeList.push(confInfo);

		$("#takeoverAnytimeLi").removeClass("active");
		$("#agentEventInfoLi").attr("class","active");
		$('#agentEventInfo').css('visibility','initial');

		$('#takeoverAnytime').hide();
    	$('#takeoverTagPoint').hide();
    	$('#agentEventInfo').show();

		$('#getAllEventInfo').show();
		$('#refreshEventInfo').hide();
		loadCheckAgentEventInfo(agent_uuid,refreshEvent = false);	//获取客户端对应的事件信息

	};
	//解析选中时间的标签点信息
	var parseLablePointInfo = function(){
		var confTime = $('#inputTakeoverTimepoint').val();
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		_timeList = [];
		var confInfo = {};
		confInfo.confTime = confTime;
		confInfo.timeInterval = _timeInterval;
		_timeList.push(confInfo);

		$("#takeoverAnytimeLi").removeClass("active");
		$("#takeoverTagPointLi").attr("class","active");
		$('#takeoverTagPoint').css('visibility','initial');

		$('#takeoverAnytime').hide();
    	$('#takeoverTagPoint').show();
    	$('#agentEventInfo').hide();

		$('#getAllLabelPointInfo').show();
		$('#refreshLabelPointInfo').hide();
		loadCheckVolTagPoint(agent_uuid,isRefreshLabel = false);	//获取客户端对应的标签点提前
	}
	/**
	 * @function 获取时间轴data
	 * @desc 通过选定数据源UUID获取客户端备份时间轴对应的数据，并加载和渲染时间轴chart
	 */
	var getAgentTimelineData = function(){
		var dataSource = $("#takeoverDataSourceSelect").val();
		if(dataSource==2){ //数据源来在于备机
			return true;
		}
		var time_data = [];
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		Metronic.blockUI({target: '#takeoverAnytime',animate: true});
		$.post(CONF.AJAXPATH, {
			m:CONF.M.VOLCDPBACKUPSET,
			f:'getAgentBkTimelineData',
			p:JSON.stringify({
				node_uuid:_createTaskMsgdata.node,
				startTime:$("#selectTakeoverStartTime").val() || '',
				endTime:$('#selectTakeoverEndTime').val() || '',
				agent_uuid:agent_uuid,
				time_interval:parseInt(_timeInterval),
				vol_uuid:"",
				task_type:CONF.TASK_TYPE.VOL_CDP_TAKEOVER,
				task_uuid:_createTaskMsgdata.task_uuid
			})
		}, function(d){
			Metronic.unblockUI('#takeoverAnytime');
			var data = JSON.parse(d);
			//避免出现异步接口返回延迟的问题
			setTimeout(function (){
				var dataLength = data['time_data'].length;
				_chartStartPercent = dataZoomUnit(dataLength);
				var dataSource = $("#takeoverDataSourceSelect").val();
				if(dataSource!=2){
					if(dataLength>0){
						time_data = data['time_data'];
						loadTimelineChart(time_data);
					}else{
						_timePointValidityValue = 2;  //时间点无效
						UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA, LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA_MESSAGE);
						loadTimelineChart(time_data);
					}
				}
			},500);


		})
    };

    /**
	 * 通过获取的数据量,设置dataZoom的加载百分比
	 */
	var dataZoomUnit = function(value){
		//dataZoom的起始百分比,默认值为70%
		var startPercent = 70;
		if(value<100){
			startPercent =0;
		}else if(value>100 && value<300){
			startPercent =500;
		}else if(value>300 && value<600){
			startPercent =70;
		}else if(value>600 && value<1000){
			startPercent =80;
		}else if(value>100 && value<2000){
			startPercent =85;
		}else if(value>2000 && value<5000){
			startPercent =90;
		}else if(value>5000 && value<8000){
			startPercent =95;
		}else if(value>8000){
			startPercent =95;
		}else{
			startPercent = 98;
		}
		return startPercent;
	}

	/**
	 * @function 自定义脚本配置开关change
	 * @desc 判断是否启用自定义脚本配置，启用需要进行登录密码校验。
	 */
	var handoverScriptChnage = function(){
		_scriptSet = [];
		$('.handscriptTips').remove();
		if(this.checked && !_userIsVerify){
			verifyLoginUserInfo();
			$('#addHandoverScriptBut').unbind('click').click(addTakeoverScript);
		}else{
			_userIsVerify = false;
			$('.handover_script_conf_view').hide();

		}
	}
	//考虑到脚本对客户端的安全影响，需要校验登录用户信息.
	var verifyLoginUserInfo = function(){
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
			        	$('#handover_script_switch').bootstrapSwitch('state', true);
						$('.handover_script_conf_view').show();

			        }else{
			        	$('.bootbox-input').css('border-color', "#a94442");
			        	if(!initErrorFlag){
			        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
			        		$('.bootbox-input').after(des);
			        		initErrorFlag = true;
			        	}
			        	$('#handover_script_switch').bootstrapSwitch('state', false);
						$('.handover_script_conf_view').hide();
						return false;
		        	}
		        }
			});
			$('#handover_script_switch').bootstrapSwitch('state', false);
			$('.handover_script_conf_view').hide();
		}
	}
	/**
	 * 添加自定义脚本
	 */
	var addTakeoverScript = function(){
		var scriptInfo = {};
		var script_path = $("#handoverScriptPath").val();
		var des =LANG.UI_VOL_CDP_JOB_DETAILS_SCRIPT_PATH+"&nbsp;"+script_path;
		if(script_path==""){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE2);
			return false;
		};
		if(!checkFilePath(script_path,osTypeTarget)){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
			return false;
		}

		for (var i=0;i<_scriptSet.length;i++){
			if(_scriptSet[i].script_path==script_path){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE7);
				return false;
			}
		}
		var liId = getUuid();
		scriptInfo.script_path = script_path;
		scriptInfo.des = des;
		//下面4个参数为手动接管脚本配置默认值
		scriptInfo.script_type = 1;
		scriptInfo.exec_type = 2;
		scriptInfo.exec_interval = 0;
		scriptInfo.trigger_fail_num = 0;

		scriptInfo.uuid = liId;
		_scriptSet.push(scriptInfo);

		var des = '';
		des +=
		'<li class="list-group-item popovers handscriptTips list-group-item__speed" id="handscript'+ liId
			+'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
			+ scriptInfo.des + '">' +
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

		$('#handoverScriptList').append(des);
		$('.handscriptTips').popover();	   //初始化tips

		//移除已添加脚本信息，需要移除popover和_scriptSet中对应的配置信息
		$('.del'+ liId).unbind('click').on('click', function(){
            $('.popover.in').remove();
            $('#handscript' + liId).remove();
            for(var i=0;i<_scriptSet.length; i++){
                if(liId == _scriptSet[i].uuid){
                	_scriptSet.splice($.inArray(_scriptSet[i],_scriptSet),1);
                }
            }
        });
		$("#handoverScriptPath").val("");

	}

	//初始化当前用户密码用于删除二次确认
	var getUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
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
	 * 解锁加密备份集
	 * 备注：选中客户端获取的最新时间点是有效且默认会获取的卷信息，跳过了选择时间判断加密熟悉的判断。在对step2执行下一步操作时判断时最新时间点的加密属性，及解密状态
	 * ****未解密需要进行解密操作才能继续继续任务配置
	 */
	var unlockBackupTimepoint = function(){
		backupSetLockStyle();  //备份集锁定样式
		bootbox.prompt({
			title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
			inputType: 'password',
			callback: function (result) {
				//同步ajax校验密码
				if(result == null) return;
				var checkflag = true;
				var params = {};
				params.timepointuuid = _createTaskMsgdata.timepoint_uuid;
				params.inputpass =  $.trim(result);
				var p = JSON.stringify(params);
				$.ajax({
					type: "post",
					url: CONF.AJAXPATH,
					async: false,
					data:{m:CONF.M.FILE,f:'checkFSEncryptPass',p:p},
					success: function(d){
						var result = JSON.parse(d);
						if(result.flag){
							backupSetUnlockStyle();  //备份集解锁定样式
							_backupSetIsLock = 1;
						}else{
							OPREL(d);
							checkflag = false;
							_backupSetIsLock = 2;
						}
					}
				});
				if(!checkflag) return false;
					return true;
			}
		});
	}
	/**
	 * @function 创建任务step
	 * @description 校验step1必须配置的相关数据是否配置，封装备份数据源
	 * @return true or false
	 */
	var step1Valid = function(){
		allpointlist = []; //初始化时间集UUID
		var selectNodes = dataSourceGrid.getSelectedRows();
		if(selectNodes.length==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_TAKEOVER_CLIENT_SELECT);
			return false;
		}
		var dataSourceInfo = dataSourceGrid.getDataTable().data();
		var dataSourceStr = "";
		var dataSourceTaskBackupMode = 0;
		for(var i=0;i<dataSourceInfo.length;i++){
			if(selectNodes[0] == dataSourceInfo[i][5] && _createTaskMsgdata.task_uuid == dataSourceInfo[i][8] ){ //客户端UUID
				dataSourceStr+=dataSourceInfo[i][1]+"("+dataSourceInfo[i][2]+")"
				dataSourceTaskBackupMode = dataSourceInfo[i][10];
				break;
			}
		}
		_createTaskMsgdata.data_source_info = dataSourceStr;
		_createTaskMsgdata.data_source_task_backup_mode = dataSourceTaskBackupMode;
		preLoadStep2Show(); //获取Step2 需要展示的内容
		delVmConfDescription();
		return true;
	}
	/**
	 * @function 预加载加载step2
	 * @description 通过step1获取的数据加载step2需要显示的内容
	 */
	var preLoadStep2Show = function (){
		var agentUuid = _createTaskMsgdata.master_agent_uuid;
		var agentIp = _createTaskMsgdata.host_ip;
		var dataSourceInfo = dataSourceGrid.getDataTable().data();
		var osType = "";
		var agentIsRunningBackupTask = 0;
		$('.takeoverTargetView').show();
		$('.takeoverStandbyconfdiv').hide();
		//判断是否授权容灾演练平台，如果未授权，需要屏蔽整机模式
		if(!_isVmMachineManager){
			// $("#takeoverStandbyHostMode option[value = "+CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT +"]").remove();
			$("#takeoverStandbyHostMode option").eq("+ CONF.CDP_STANDBY_HOST_MODE.PROXY_CLIENT +").prop('selected', true);
			$('.takeoverTargetView').show();
			$('.takeoverStandbyconfdiv').hide();
			$('input[name="mount_target"]').prop('readonly', false);
		}
		
		for(var i=0;i<dataSourceInfo.length;i++){
			if(agentUuid == dataSourceInfo[i][5] && agentIp == dataSourceInfo[i][2] ){
				osType = dataSourceInfo[i][7];
				var masterAgentStorageLocation = dataSourceInfo[i][6];
				applyStorageLocation(masterAgentStorageLocation,osType);  //渲染存储对象，控制存储对象select可供选择项
				agentIsRunningBackupTask = dataSourceInfo[i][9];
			}
			_createTaskMsgdata.master_os_type = osType;  //选择数据源主机系统类型
			_createTaskMsgdata.agent_running_backup_task = agentIsRunningBackupTask;
		}
		var dataSource = 0;
		getClientAnyTimePointInfo(dataSource);	//获取选择客户端数据集
		_timeList = [];
		_hisChangeTimeInterval = 0; //还原历史配置
		loadCheckVolTagPoint(agentUuid,isRefreshLabel = false);	//获取客户端对应的标签点
		loadCheckAgentEventInfo(agentUuid,refreshEvent = false);  //获取选择客户端生产的事件信息
		loadDatatimePicker();
		initTimeListeners();
		getAgentTimelineData();  //加载选定客户端对应的备份数据

		//TODO 备注：当前阶段先从任务客户端表中获取，暂不考虑离线客户端，后续需要和后台讨论添加

		loadAgentNetworkInfo(agentUuid); //获取选中客户端的网卡信息
		$('.targetHostUsedPartition').css("display","none");
		$('.storageObjectView').hide(); //需求调整隐藏存储对象的配置
		$('.applianceselectview').hide();
		$('#takeoverTargetHostSelect option:first').prop('selected', true);
		// $('.targetHostUsedPartition').html("");
		$('.targetHostUsedPartition').css("display","none");
		$('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示
		_standbyIsConf = false;
		ipMapInfoList = [];  //重置主机业务IP映射配置
		_takeoverIpMapListView = [];  //重置主机业务IP映射view配置

		$('.standbyconflabel').html(LANG.UI_VOL_CDP_VERIFY_STANDBY_CONF);
		$('.takeovertimepoint').html(LANG.UI_VOL_CDP_JOB_DETAILS_VERIF_TIME_POINT);
	}

	var step2Valid = function(){
		var dataSourceType = $('#takeoverDataSourceSelect').val();
		if(_timepointType==0 && dataSourceType!=2){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
			return false;
		}
		if(_backupSetIsLock==2){
			var unLock= unlockBackupTimepoint();
			return false;
		}
		if(_timePointValidityValue!=CONF.FLAG.SET){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
			return false;
		}

		if(!!_takeoverInTask && (!_isDataSourceHostMountAndVerify )){  //判断目标节主机是否已配置作业
			UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE, LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE_MESSAGE2+_takeoverTaskName+LANG.UI_VOL_CDP_TAKEOVER_CONFIGURE_SELECT);
			return false;
		}
		var targetHost = $('#takeoverTargetHostSelect').val();
		var hostOnlineFlag = $("#takeoverTargetHostSelect").find("option:selected").attr("data-hostonlineflag");
		var standbyHostMode = $('#takeoverStandbyHostMode').val(); //1:代理客户端；3：内嵌虚拟化
		if((!targetHost && standbyHostMode==1) || (standbyHostMode ==3 && objctIsEmpty(_vmTmpAgentConf))){
			UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE, LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE_MESSAGE3);
			return false;
		}

		//当前选中客户端状态离线或未知
		if(hostOnlineFlag==CONF.FLAG.UNSET || hostOnlineFlag==CONF.FLAG.UNKNOW){
			UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE, LANG.UI_VOL_CDP_TAKEOVER_TARGET_OFFLINE_TIPS);
			return false;
		}

		var volTable = $('#takeOverVolTable').dataTable();
		var selectNodes = volTable.fnGetNodes();
		var mount_point_set = [];
		var vol_uuid_set = [];
		// var volTypeArray = [];
		_checkedVolInfo = [];
		debugger;
		for (var r = 0; r < selectNodes.length; r++) {    //遍历行
			var mountObj = {};
			var myRow = $('#takeOverVolTable').dataTable().fnGetData(selectNodes[r]);
			var vol_id = "takeover_id_"+r;
			if($("#"+vol_id+"").get(0).checked) {
				var target_input_id = "mount_target_"+myRow[6];
				var targetMountPoint = $("#"+target_input_id).val();
				var takeover_vol = myRow[7];
				var osType = myRow[8];
				if(targetMountPoint==LANG.UI_VOL_CDP_TAKEOVER_AUTO_ALLOCATE || targetMountPoint==""){
					targetMountPoint = ""; //获取选中备份集对应的挂载点
				}
				if (targetMountPoint!=LANG.UI_VOL_CDP_TAKEOVER_AUTO_ALLOCATE && targetMountPoint!=""){
					var pattern = /^[A-Za-z]:\\$/;
					if (!pattern.test(targetMountPoint) && osType=="Windows") {
						UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS2);
						return false;
					}
				}
				var isUsed = verdictVolIsUsed(targetMountPoint);  //判断输入的挂载点是否被占用
				var hostMountpointStr = $("#takeoverTargetHostSelect").find("option:selected").attr("data-mountpoint");
				if(!isUsed && osType==LANG.UI_VOL_CDP_TAKEOVER_OS_TYPE){
					UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS+"( "+LANG.UI_VOL_CDP_TAKEOVER_MOUNT_HAS_BEEN_USED+hostMountpointStr+" )");
					return false;
				}

				if(targetMountPoint!=""){ //需要根据系统类型校验输入挂载点是否合法；
					let regex = /^[A-B]:\\/i;
					if (regex.test(targetMountPoint) && osType=="Windows") {
						UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_AB);
						return false;
					}else if(checkPath(targetMountPoint,osType)){
						targetMountPoint = targetMountPoint.replace(/\\/g, '');  //去除反斜杠
					}else{
						UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_MOUNT_CONFIGURE_TIPS2);
						return false;
					}
				}
				if(myRow[9]== CONF.FLAG.SET || myRow[10] == true) //判断是否为boot 分区
				{
					myRow[10] = true; //引导分区在接管备机时也作为系统分区来处理
					_checkedVolInfo.push(myRow[6]);
				}
				mountObj.vol_uuid = myRow[6];
				mountObj.target_mount_point = targetMountPoint;
				mountObj.takeover_vol = takeover_vol;
				mount_point_set.push(mountObj);
				vol_uuid_set.push(myRow[6]);
				// volTypeArray.push(myRow[10]);
			}
		}

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

		if(vol_uuid_set.length==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_TAKEOVER_VOL);
			return false;
		}
		debugger; 
		//判断选择的系统卷是否包含选中数据源对应的所有卷，如果没有包含所有的系统卷和引导分区，则不能使用容灾演练平台做为备机；
		//未选择系统卷时，容灾演练平台不能作为备机使用
		if((_checkedVolInfo.length <_allSysVolInfoArray.length && standbyHostMode == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT)
			|| (_allSysVolInfoArray.length ==0 && standbyHostMode == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT)){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CONFIGURE_STANDBY_MACHINE,LANG.UI_VOL_CDP_TAKEOVER_VM_TEMP_SYS_VOL_TIPS);
			return false;
		}


		if(!objctIsEmpty(_vmTmpAgentConf)){
			_createTaskMsgdata.takeover_object.takeover_vm = {};
			_createTaskMsgdata.takeover_object.takeover_vm.config = _vmTmpAgentConf;
			_createTaskMsgdata.takeover_object.takeover_vm.uuid = _createTaskMsgdata.standby_target_agent_uuid;
			_createTaskMsgdata.takeover_object.takeover_vm.hypervisor_type = 108; //内嵌为kvm  对应后台虚拟机类型：VmHypervisorType,对应键:VM_HYPERVISOR_TYPE_EMBED_OEMU_KVM
			_createTaskMsgdata.takeover_object.takeover_vm.node_uuid = _createTaskMsgdata.node_uuid;

			// _createTaskMsgdata.takeover_object.takeover_vm.config.special.role = CONF.TEMP_AGENT_ROLE.TEMP_AGENT_ROLE_TAKEOVER;
			// _createTaskMsgdata.takeover_object.takeover_vm.config.special.cross_platform_flag = CONF.FLAG.UNSET;

		}
		_createTaskMsgdata.vol_uuid_set = vol_uuid_set;
		_createTaskMsgdata.takeover_object.mount_point_set = mount_point_set;
		_createTaskMsgdata.takeover_standby_agent_uuid = targetHost;
		_createTaskMsgdata.takeover_data_source = $('#takeoverDataSourceSelect').val();
		var appScened = $('#changeAppScened').val();
		var takeoverBusinessIpMap = groupTakeoverBusinessIpMap();
		var masterAgentIpSwitchFlag = CONF.FLAG.UNSET;
		var standbyHostMode = $('#takeoverStandbyHostMode').val();
		if(appScened==CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){
			takeoverBusinessIpMap = [];
		}else if(appScened==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER && (takeoverBusinessIpMap.length>0 || standbyHostMode == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT)){
			masterAgentIpSwitchFlag = CONF.FLAG.SET;
		}
		_createTaskMsgdata.takeover_object.master_agent_ip_switch_flag = masterAgentIpSwitchFlag;
		_createTaskMsgdata.takeover_object.takeover_business_ip_map = takeoverBusinessIpMap;
		_createTaskMsgdata.takeover_object.failback_business_ip_map = [];

		if(appScened==CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL && standbyHostMode !=1){  //实时验证，验证类型为挂载验证
			_networkFlag = false; 
		}

		preLoadStep3Show(); //获取Step3 需要展示的内容
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

				var vmNicSet = _vmTmpAgentConf.vm_interfaces_nic_set;
				var ipSet = [];
				for(var d =0;d<vmNicSet.length;d++){
					if(vmNicSet[d].name ==targetNicName){
						var ipSetObject = {};
						ipSetObject.ip_addr = nicIpInfo[n].ip;
						ipSetObject.ip_type = 1;
						ipSetObject.netmask = nicIpInfo[n].netmask;
						ipSet.push(ipSetObject)
						_vmTmpAgentConf.vm_interfaces_nic_set[d].ip_set = ipSet;
					}

				}

			}
		}
		_vmTmpAgentConf.special.role = CONF.TEMP_AGENT_ROLE.TEMP_AGENT_ROLE_TAKEOVER;
		_vmTmpAgentConf.special.cross_platform_flag = CONF.FLAG.UNSET;
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
	//校验输入盘符是否已被占用
	var verdictVolIsUsed = function(targetMountPoint){
		if(targetMountPoint!=""){
			var mountPointStr = _standbyHostMountPoint.replace(/\\/g,"/");
			var mountPointList =  mountPointStr.split(",");
			var targetMountStr = targetMountPoint.replace(/\\/g,"/");
			targetMountStr = targetMountStr.toLocaleUpperCase();
			if ($.inArray(targetMountStr, mountPointList) == -1) {
				return true;
			}
			return false;
		}else{
			return true;
		}

	}
	/**
	 * @function 预加载加载step2
	 * @description 通过step1,step2相关数据获取并展示step3需要显示的内容
	 */
	var preLoadStep3Show = function(){
		_createTaskMsgdata.takeover_object.app_set = [];
		_createTaskMsgdata.takeover_object.app_takeover_flag = 2;
		// $('.hm_takeover_ip_map_list li').remove();  //重置网卡配置view
		$('.takeoverAppDes').html("--");
		loadTakeoverHostAppInfo();
		//重置自定义脚本配置
		$('#handover_script_switch').bootstrapSwitch('state', false);
		$('#handoverScriptPath').val("");


		if(_networkFlag){
			$('.transfernetworkDiv').show();
			$('.transfernetworkdiv').show();
			initNetworkList();
		}else{
			$('.transfernetworkDiv').hide();
			$('.transfernetworkdiv').hide();
			//清空传输网络配置
			var transferNetwork = $('#transferNetwork');
    		transferNetwork.empty();
		}
		var standbyHostMode = $('#takeoverStandbyHostMode').val();
		//允许配置目标主机与数据源主机同设备的主机作为目标主机，且应用场景为验证，验证方式为挂载时允许选择主机,隐藏应用配置
		if(_isDataSourceHostMountAndVerify && standbyHostMode == 1){  
			$('.takeoverappdiv').hide();
		}else{
			$('.takeoverappdiv').show();
		}

		$('#hostIpCutSwitch').on('switchChange.bootstrapSwitch', hostIpCutSwitchChange);
		getTaskName();
		//下面的请求暂时弃用，后台暂不支持；
		$('#takeoverCutBackIp').blur(function(){
			var ip = $("#takeoverCutBackIp").val();
		    if((!checkIP(ip))&& ip.length>0||ip == ""){
		        UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_TAKEOVER_IP_MESSAGE);
		        $('#takeoverCutBackIp').val('');
		        $('#takeoverCutBackIp').focus();
		        return false;
		    }
		    var params = {};
		    params.ip = ip;
		    var params = JSON.stringify(params);
		    $.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'checkIpExists',p:params}, function(data){
		    	if(data==true){
		    		UIToastr.showWarning(LANG.UI_DB_BACKUP_SELECT_DATABASE, LANG.UI_VOL_CDP_TAKEOVER_SELECT_DATABASE_MESSAGE);
		    		$('#takeoverCutBackIp').val('');
		 	        $('#takeoverCutBackIp').focus();
		    		return false;
		    	}
		    	return true;
		    })
		});
	}
	/**
	 * 初始化接管網絡配置
	 */
	var takeoverNetworkConfModal = function(){
		var standbyHostuuid = $('#takeoverTargetHostSelect').val();
		var standbyHostMode = $('#takeoverStandbyHostMode').val();
		if(standbyHostuuid == "" && standbyHostMode==1){ //备机类型为代理客户端，备机uuid为空时
			UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION, LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION_TIPS);
			return false;
		}
		if(_standbyNetworkInfo.length==0 ){
			UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_NETWORK_CONFIGURATION, LANG.UI_VOL_CDP_TAKEOVER_NETWORK_NULL_TIPS);
			return false;
		}

		$('#hmTakeoverNetworkConfModal').modal({'width':'800px', 'height':'380px'});
		$('#hm_takeover_ip_server_conf').takeoverIpServerMapConfig({
			  agentNetwork:_agentNetworkInfo,
			  standbyNetwork: _standbyNetworkInfo,
			  takeoverHisNetwork:[],
			  failbackHisNetwork:[],
		});

		$('#hm_standby_gateway_conf').takeoverStandbyGatewayConfig({
			  agentNetwork:_agentNetworkInfo,
			  standbyNetwork: _standbyNetworkInfo,
			  takeoverHisNetwork:[],
			  failbackHisNetwork:[],

		});
		$("#submit_hm_takeover_network_conf").unbind('click').click(submitHostNetworkConf);
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
	var submitHostNetworkConf = function(){
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
			$('.hm_takeover_ip_map_list li').remove();
			hostGatewayConfDes();
		}

		$('#hmTakeoverNetworkConfModal').modal('hide');
	}
	/**
	 * 获取配置网卡描述信息，并渲染
	 */
	var hostGatewayConfDes = function(){
		var des = "";
		for (var i = 0; i < _takeoverIpMapList.length; i++) {
			var conf_id = _takeoverIpMapList[i].conf_id;
			var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ _takeoverIpMapList[i].conf_id
				+'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
				+ _takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 100%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
				+ _takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  _takeoverIpMapList[i].conf_id +'" >'
				+'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
			$('.hm_takeover_ip_map_list').append(des);
			$('.takeoverNetcardTips').popover();	   //初始化tips
			$('.takeover_netcard_del'+ conf_id ).bind("click",{index:i},clickHandler);
		}
		var appScened = $('#changeAppScened').val();
		if(appScened==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){
			$('.takeoveripdriftview').show(); 	//隐藏ip漂移提示 
		}else{
			$('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示 
		}

	}
	/**
	 * 为每一个<li>绑定点击事件，并处理移除数组操作
	 */
	var clickHandler = function(event){
		var className = this.className;
		var splitClassName = className.split('takeover_netcard_del');
		var eventConfID = splitClassName[1];
		$('.popover.in').remove();
		var index = event.data.index;
		var confId;
		var list = _takeoverIpMapListView;
		$('.takeoveripdriftview').hide(); 	//隐藏ip漂移提示  

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
			var hostGateway = $('#standby_netcard_'+hostMac).val()
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
	 * 开启主机ip漂移需要判断数据源主机IP是否在线，在线则不能开启
	 */
	var hostIpCutSwitchChange = function(){
		if($('#hostIpCutSwitch').is(':checked')){  //打开主机IP漂移
			$('.hostipcutinfo').hide();
			var masterAgentUuid = _createTaskMsgdata.master_agent_uuid;
			var info = {};
			info.agent_uuid = masterAgentUuid;
			var params = JSON.stringify(info);
			Metronic.blockUI({target: '#voltakeovercontent',animate: true,cenrerY: true,});
			$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'checkIpIsOnline',p:params}, function(d){
				Metronic.unblockUI('#voltakeovercontent');
				var data = JSON.parse(d);
				var hostIp = data['ip'];
				var exists = data['exists'];
				var hostName = data['host_name'];
				var appScenedValue = $('#changeAppScened').val();
				if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER){//应用场景接管
					$(".applianceselectview").show();
				}
				if(!exists){
					$(".applianceselectview").hide();
					UIToastr.showWarning(LANG.UI_VOL_CDP_TAKEOVER_HOST_IP_SHIFT,LANG.UI_VOL_CDP_HOST_IP+":"+hostIp+LANG.UI_VOL_CDP_HOST_IP_SHIFT_DESC);
					$('#hostIpCutSwitch').bootstrapSwitch('state', false);
					return false;
				}
			});
		}else{
			$('.hostipcutinfo').hide();
			$(".applianceselectview").hide();
		}
	}

	//得到任务名
	var getTaskName = function(){
		var info = {};
		var changeAppScened = $("#changeAppScened").val();
		var taskTypeStr = LANG.UI_VOL_CDP_TAKEOVER_DEAULT_TASK_NAME;
		if(changeAppScened == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){
			taskTypeStr = LANG.UI_VOL_CDP_VERIF_TASK;
		}
		info.task_type = taskTypeStr;
		info = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getVolCdpTakeoverTaskName',p:info}, function(d){
			$('#volCdpTakeoverName').val(d);
		});
	}

	/**
	 * @function 加载接管应用
	 * @description 通过选定的客户端主机uuid及卷uuid获取可供接管的应用信息
	 */
	var loadTakeoverHostAppInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		var vol_set = _createTaskMsgdata.vol_uuid_set;
		var take_over_time = _createTaskMsgdata.takeover_timestamp;
		var info = {};
		info.agent_uuid = agent_uuid;
		info.vol_set = vol_set;
		info.take_over_time = take_over_time;
		info.standby_host_uuid = _createTaskMsgdata.takeover_standby_agent_uuid;
		info.task_uuid = _createTaskMsgdata.task_uuid;
		var jsonData = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getClientTakeoverAppInfo',p:jsonData}, setTree);
	}

	var setTree = function(zNodes){
		if(zNodes == "[]"){
			$("#noTakeoverApp").show();
			$("#takeover_app_tree").hide();
			return;
		}else{
			$("#noTakeoverApp").hide();
			$("#takeover_app_tree").show();
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
				change:changeClick, //选择节点事件
				onCheck:takeoverAppSelect,
			}
		};

		zTreeAgent = $.fn.zTree.init($("#takeover_app_tree"), setting, JSON.parse(zNodes));
		//备份为容灾演练平台时，默认选中所有应用
		let standbyMode = $('#takeoverStandbyHostMode').val();
		if(standbyMode == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
			let tree = $.fn.zTree.getZTreeObj("takeover_app_tree");
			tree.checkAllNodes(true); // 传入true将会选中所有节点
			takeoverAppSelect("","takeover_app_tree","");
		}
	};

	var changeClick = function(event, treeId, treeNode){
		//TODO 暂时未使用

	}
	/**
	 * @function 获取选中节点,封装节点对应的应用ID及封装消息结构
	 */
	var takeoverAppSelect = function(e, id, node){
		_createTaskMsgdata.takeover_object.app_takeover_flag = CONF.FLAG.UNSET;
		var tree = $.fn.zTree.getZTreeObj(id);
	    var checkedNodes = tree.getCheckedNodes(true);
		_createTaskMsgdata.takeover_object.app_set = [];
	    var app_set = [];
		if(checkedNodes.length >2){
			$('.takeoverAppDes').html("--");
		}
		// if(!node.checked){
		// 	return;
		// }
	    if(checkedNodes.length>0){
	    	// var app_type_str = checkedNodes[0].name;
	    	// var app_type_title = checkedNodes[0].title;
	    	var check_app_str= "";
	    	var app_str = "";
			var standby_mode = $('#takeoverStandbyHostMode').val();
		    for(var i=0;i<checkedNodes.length;i++){
		    	if(checkedNodes[i].isApp){
					if(checkedNodes[i].isApp){
						var app_type_str = checkedNodes[i-1].name;
						var app_type_title = checkedNodes[i-1].title;
						
						var app_set_obj = {};
						var app_uuid = checkedNodes[i].uuid;
						var app_type = checkedNodes[i].app_type;
						var app_name = checkedNodes[i].name;
						var standby_app_name = checkedNodes[i].standby_app_name;
						var standby_app_uuid = checkedNodes[i].standby_app_uuid;
						if(standby_app_uuid=='' && standby_mode != CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
							tree.checkNode(checkedNodes[i]); //取消当前选中节点
							UIToastr.showWarning(LANG.UI_CLIENT_APP_CONFIG, LANG.UI_VOL_CDP_TAKEOVER_CLIENT_APP_CONFIG_MESSAGE);
							return false;
						}
						if(standby_app_name!=app_name && standby_mode != CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
							tree.checkNode(checkedNodes[i]); //取消当前选中节点
							UIToastr.showWarning(LANG.UI_CLIENT_APP_CONFIG, LANG.UI_VOL_CDP_TAKEOVER_CLIENT_APP_CONFIG_MESSAGE2);
							return false;
						}
						if( standby_mode == CONF.CDP_STANDBY_HOST_MODE.DISASTER_RECOVERY_DRILL_PLAT){
							standby_app_uuid = app_uuid;
						}
						var takeoverTargetuuid = $('#takeoverTargetHostSelect').val();
	
						app_str = app_name+"   ";
						app_set_obj.master_app_uuid = app_uuid;
						app_set_obj.standby_app_uuid = standby_app_uuid;
						app_set.push(app_set_obj);
						check_app_str += app_type_title+":"+app_type_str+">" + app_str + ";  ";
					}
		    	}
		    }

			if(_createTaskMsgdata.master_agent_uuid == takeoverTargetuuid  && node.checked){
				bootbox.confirm({
					title: LANG.UI_VOL_CDP_CONFIGURE_TAKEOVER_APPLICATION,
					message: LANG.UI_VOL_CDP_TAKEOVER_TARGET_APP_TIPS,
					callback: function(r) {
						if(!r){
							loadTakeoverHostAppInfo();  //重新加载备份集对应应用
							_createTaskMsgdata.takeover_object.app_set = [];
							$('.takeoverAppDes').html("--");
							_createTaskMsgdata.takeover_object.app_takeover_flag = CONF.FLAG.UNSET
						}
					}
				});
			}


		    // check_app_str = check_app_str+app_str;
		    check_app_str = check_app_str.slice(0,-1);
		    $('.takeoverAppDes').html(check_app_str);
		    $('.takeoverAppDes').prop('title', check_app_str);
		    _appTypeConfStr = check_app_str;
	    }else{
	    	$('.takeoverAppDes').html("--");
	    	var app_set = [];
	    }
	    if(app_set.length>0){
	    	_createTaskMsgdata.takeover_object.app_takeover_flag = CONF.FLAG.SET;
	    }
	    _createTaskMsgdata.takeover_object.app_set = app_set;
	}

	var step3Valid = function(){
		_createTaskMsgdata.takeover_object.takeover_standby_agent_uuid = $('#takeoverTargetHostSelect').val();
		//手动接管一下参数默认为0
		_createTaskMsgdata.takeover_object.auto_takeover_flag =CONF.FLAG.UNSET; //自动接管状态
		_createTaskMsgdata.takeover_object.app_consecutive_failure_num = 0; //App连续失败次数
		_createTaskMsgdata.takeover_object.app_fault_detection_interval = 0 ;//App故障检测时间间隔
		_createTaskMsgdata.takeover_object.script_set = []; //自定义监测脚本
		if($('#handover_script_switch').is(':checked')){  //开启接管自定义脚本功能
			if(_scriptSet.length==0){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_CUSTOM_SCRIPT_CONFIGURE_MESSAGE6);
				return false;
			}
			_createTaskMsgdata.takeover_object.script_set = _scriptSet;
		}
		//开启主机IP漂移到备机
		var master_agent_ip_switch_flag = 2
		if($('#hostIpCutSwitch').is(':checked')){
			master_agent_ip_switch_flag = 1;
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
		if(!objctIsEmpty(_vmTmpAgentConf)){
			// conformityVmConfInfo(takeoverIpMap);
			var businessIpMap = paddingVmtempBusinessIpMap();
			_createTaskMsgdata.takeover_object.takeover_business_ip_map = businessIpMap;
		}
		_createTaskMsgdata.transfer = {};
		_createTaskMsgdata.transfer.network = $("#transferNetwork").val();
		_createTaskMsgdata.transfer.strategy_group_uuid = "";

		preLoadStep4Show() // 加载第四部需要显示的内容
		return true;
	}
	/**
	 * 接管备机为容灾演练平台（内嵌虚拟机）时，动态填充网卡主备映射关系
	 */
	var paddingVmtempBusinessIpMap = function (){
		var businessIpMap = [];
		var vmBusinessNicSet = _vmTmpAgentConf.vm_interfaces_nic_set;
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
	/**
	 * 组合接管业务网络
	 */
	var groupTakeoverBusinessIpMap = function(){
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
		return takeoverIpMap;
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

	/**
	 * 加载第四部需要显示的内容,汇总配置信息
	 */
	var preLoadStep4Show = function (){
		debugger;
		var selectNodes = dataSourceGrid.getSelectedRows();
		var dataSourceInfo = dataSourceGrid.getDataTable().data();
		var dataSourceStr = "";
		for(var i=0;i<dataSourceInfo.length;i++){
			if(selectNodes[0] == dataSourceInfo[i][5]){  //agent uuid
				dataSourceStr+=dataSourceInfo[i][1]+"("+dataSourceInfo[i][2]+")";
				break;
			}
		}
		$('.datasourcehostshow').html(dataSourceStr);
		//获取配置接管数据
		var takeoverTimepointDes = $('.takeoverTimepointDes').text();
		$('.takeoverTimePointShow').html(takeoverTimepointDes);

		var appScened = $("#changeAppScened").val();  //应用场景
		var standbyType = $('#takeoverStandbyHostMode').val();
		$('.standbyTypeShow').html($('#takeoverStandbyHostMode option:selected').text());
		$('#vmTargetShow').html("");
		$('.appsceneview').show();
		$('.appsceneconftext').html($('#changeAppScened option:selected').text());
		switch (parseInt(standbyType)){
			case 1:  //代理客户端
				var takeoverTargetHostSelect = $('#takeoverTargetHostSelect').find("option:selected").text();
				$('.takeoverTargetShow').html(takeoverTargetHostSelect);
				$(".takeoverTargetdiv").show();
				$('.vmTakeoverTargetDiv').hide();

				
				break;
			case 2:  //第三方虚拟化
				break;
			case 3:  //内嵌虚拟机
				var takeoverTargetHostSelect = $('#takeoverTargetHostSelect').find("option:selected").text();
				$('.takeoverTargetShow').html(takeoverTargetHostSelect);
				var sourceDiv = document.getElementById('vmDetailConfigInfo'); // 获取源div和目标div的引用
				var destinationDiv = document.getElementById('vmTargetShow');
				var clone = sourceDiv.cloneNode(true);
				destinationDiv.appendChild(clone);
				$("#tab4 #vmDetailConfigInfo").css("width", "120%");
				$("#tab4 #vmDetailConfigInfo .textwrap_en").removeClass('textwrap_en');
				$(".takeoverTargetdiv").hide();
				$('.vmTakeoverTargetDiv').show();

				_createTaskMsgdata.takeover_object.takeover_standby_agent_uuid =_createTaskMsgdata.standby_target_agent_uuid;
				_createTaskMsgdata.takeover_standby_agent_uuid = _createTaskMsgdata.standby_target_agent_uuid;
				// _createTaskMsgdata.takeover_object.takeover_agent_role = CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_TEMP_AGENT;
				break;
		}
		_createTaskMsgdata.takeover_object.takeover_agent_role = appScened; // 应用场景
		var volStrLang = LANG.UI_VOL_CDP_TAKEOVER_VOL_DESC;  //接管卷
		var taskSummaryEventVolumeDesc = LANG.UI_VOL_CDP_TAKEOVER_VOL_DESC;

		if(appScened == CONF.EMD_VM_ROLE.EMD_VM_ROLE_DRILL){  
			volStrLang = LANG.UI_VOL_CDP_VERIFY_VOL_DESC;  //验证卷	
			taskSummaryEventVolumeDesc = LANG.UI_VOL_CDP_VERIFY_VOL_DESC;
		}
		var volConf = _createTaskMsgdata.takeover_object.mount_point_set;
		var takeoverTargetStr = "";
		var takeoverVolStr = "";
		for(var i=0;i<volConf.length;i++){
			var volStr = volConf[i].takeover_vol;
			var mountPint = volConf[i].target_mount_point;
			if(mountPint==""){
				mountPint = LANG.UI_VOL_CDP_TAKEOVER_AUTO_ALLOCATE;
			}
			var volumeStr = volStrLang + ":" + volStr;
			var mountPint = LANG.UI_VOL_CDP_BACKUP_MOUNT_POINT + mountPint;
			takeoverTargetStr += volumeStr +" <span style ='font-weight:Bold;font-size:15px;'> &nbsp;>&nbsp;</span>"+mountPint+"<br>";
		}
		$('.takeoverVolInfoShow').html(takeoverTargetStr);
		$('.taskSummaryEventVolumeDesc').html(taskSummaryEventVolumeDesc + ":");
		//获取应用及其他接管参数
		$('.handoverScriptShow').html(LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE);
		$('.handoverscriptconfdiv').hide();
		if($('#handover_script_switch').is(':checked')){  //开启接管自定义脚本功能
			$('.handoverScriptShow').html(LANG.UI_RECOVERY_AWS_ENABLE);
			$('.handoverscriptconfdiv').show();
			var scriptList = _createTaskMsgdata.takeover_object.script_set;
			var scriptConfStr = '';
			if(scriptList.length !=0){
				for(var i=0;i<scriptList.length;i++){
					scriptConfStr += scriptList[i].des + '<br>';
				}
			}else{
				scriptConfStr = LANG.UI_PUBLIC_NOTHING;
			}
			$('.handoverscriptconfinfo').html(scriptConfStr);
		}

		$('.takeoverbusinessipmapview').hide(); //业务IP映射
		var appScenedValue = $('#changeAppScened').val();
		var standbyHostMode = $("#takeoverStandbyHostMode").val();
		//应用场景为接管，备机正常配置，备机类型为代理客户端时
		
		if(appScenedValue==CONF.EMD_VM_ROLE.EMD_VM_ROLE_TAKEOVER && _standbyIsConf==true && standbyHostMode == 1){
			$('.takeoverbusinessipmapview').show(); //业务IP映射
		}

		var transferNetwork = $("#transferNetwork").find("option:selected").text();
		$('.transferNetwork').html(transferNetwork);

		var takeoverCutBackIp = $("#takeoverCutBackIp").val();
		$('.takeoverServerIpShow').html(takeoverCutBackIp);

	    var takeoverAppDes = $(".takeoverAppDes").text();
	    $('.takeoverAppShow').html(takeoverAppDes);

		var takeoverIpserviceMapStr = LANG.UI_VOL_CDP_BACKUP_NO_CONFIGURE;
		if(_takeoverIpMapListView.length>0){
			takeoverIpserviceMapStr = "";
		}
	    for(var i=0;i<_takeoverIpMapListView.length;i++){
			takeoverIpserviceMapStr += " <span style ='font-weight:Bold;font-size:15px;' &nbsp;>&nbsp;</span>"+_takeoverIpMapListView[i].conf_des+"<br>"
		}
		$('.takeoverbusinessipmapshow').html(takeoverIpserviceMapStr);
	}
	/**
	 * @function 校验IP
	 * @description 校验IP格式是否合法
	 * @return  true or false
	 */
	var checkIP = function (value){
		 return /^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/.test(value);
	}
	/**
	 * @function 提交任务配置信息
	 * @description 封装任务配置信息,创建手动接管任务信息
	 */
	var submit = function(){
		if('' == $.trim($("#volCdpTakeoverName").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$(".button-submit").addClass('disabled');  //添加button禁用样式
		$(".button-submit").css("pointer-events", "none");  //添加button不可点击样式
		$('.jobnametip').hide();
        let jobName = $.trim($("#volCdpTakeoverName").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
		_createTaskMsgdata.taskName = $.trim($("#volCdpTakeoverName").val());

		var jsonData = JSON.stringify(_createTaskMsgdata);
    	Metronic.blockUI({target: '#voltakeovercontent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'createTakeoverJob',p:jsonData}, function(data){
    		Metronic.unblockUI('#voltakeovercontent');
			$(".button-submit").removeClass('disabled');  //移除button禁用样式
			$(".button-submit").css("pointer-events", "auto"); //移除button不可点击样式
    		if(OPREL(data)){
    			LOCATION('./content/platform/jobs/jobs.php', 'task');
        	}
    	});

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
            jQuery('li', $('#voltakeovercontent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#voltakeovercontent').find('.button-previous').css('visibility', 'hidden');
            } else {
                $('#voltakeovercontent').find('.button-previous').css('visibility', 'visible');
            }

            if (current >= total) {
                $('#voltakeovercontent').find('.button-next').hide();
                $('#voltakeovercontent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#voltakeovercontent').find('.button-next').show();
                $('#voltakeovercontent').find('.button-submit').css('visibility', 'hidden');
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#voltakeovercontent').bootstrapWizard({
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
                $('#voltakeovercontent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#voltakeovercontent').find('.button-previous').css('visibility', 'hidden');
        $('#voltakeovercontent .button-submit').click(submit).css('visibility', 'hidden');
	};
	/*** 表格的配置***/
	var gettableDefaultsOpt = function(){
		var defaultsOpt = {
			"searching": false,
			"ordering": false,
			"paging":false,
			"info":false,
			"checkbox":true,
			"bAutoWidth":false,
			'targets': [1],
			"language": { // language settings
                "emptyTable": LANG.UI_TOOLS_NO_DATA,
                "zeroRecords": LANG.UI_TOOLS_NO_DATA,
            },
            "order": [
                [1, "desc"]
            ],
		};
		return defaultsOpt;
	}
	//清除表格数据
	var clearTable = function(table, id){
		var tr = $('#' + id + ' tbody tr');
		for(var i=0; i<tr.length; i++){
			table.row().remove();
		}
		table.row().draw();
	}
	//初始化节点传输网络列表
	var initNetworkList = function(){
		var data = {};
		data.nodeuuid = _createTaskMsgdata.node;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeNetworkList',p:p}, function(d){
    		var data = JSON.parse(d);
    		var transferNetwork = $('#transferNetwork');
    		transferNetwork.empty();
			for(var i=0; i<data.length; i++){
				var name = data[i].ip + ":" + data[i].port;
				var defaultName = data[0].ip + ":" + data[0].port;
				$(".transfernetworkDes").html(defaultName);
				if(data[i].alias_name != ""){
    				name += "(" + data[i].alias_name +")";
    			}
				var option = '<option  value="' + data[i].network_uuid + '">' + name + '</option>';
				transferNetwork.append(option);
			}
    	});
	}

	var watchEchartSizeChange = function() {
		window.onresize = function() {
			var portletBodyWidth = $('#tabPortletBody').width();
			var portletBodyHeight = $('#tabPortletBody').height();
			$('#volCdpTimeline').css({"width": portletBodyWidth + 'px', "height": (portletBodyHeight - 34) + 'px'});
			myChart.resize();
		}
	}

	return {
        init: function () {
        	wizardInit();
        	initLoadTakeoverDataHost();
			initGetSysAuthFunc();  //获取授权信息
        	//initLoadStroageNodeInfo();
        	initListener();
			watchEchartSizeChange();
        }

    };
}();

jQuery(document).ready(function() {
	VolCDPTakeover.init();
});