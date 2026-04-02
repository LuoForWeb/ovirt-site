rebuildPartSwitch
var VolCDPRecover =  function () {
	var grid, dataSourceGrid,dataSourceFlag,gridInitFlag,tagGrid,volGrid,tagGridFlag = false,eventGrid,eventGridFlag=false,gridInitFlag = false;
	var dataSourceHostUuid = '';
	var dataStorageType = "";
	var checkedVoluuid = ""; //配置时间点选择的卷uuid
	var _recoverTargetVolDatatable,_recoverDataSourceConf;
	var _createTaskMsgdata = {timeInfo:{}, highInfo:{}};
	var _recoveryVolDatatable,_recoveryTargetHostDisk;
	var _recoveryTargetHostVol = [];
	var _catBackVolSet = [];
	var _recoverTargetData = [];
	var _hostVolInfo = []; //已配置需要恢复的卷
	var _re_target_vol_map_set =[]; //恢复目标卷关系集
	var _selectMapVol = [];	//记录已选择的卷
	var allpointlist = [];  //保存一条链的时间点
	var _node_uuid = "";
	var initSpeedFlag = false;  //是否启用限速
	var networkFlag = false;  //是否显示传输网络

	var speedList = [];
	var _recoverDataConfList = [];  //恢复任务卷配置信息
	var _recoverVolDataSourceTab,_recoverVolDataTargetTab;

	var _latestTimePoint="";
	var _client_vol_info=[];
	var _timeInterval =2; //时间轴默认加载的间隔
	var _chartStartPercent =70; //dataZoom的起始百分比,此处暂定70%,根据实际效果调整.(可以根据加载的时间区间范围确定起始百分比)
	var _chartEndPercent =100;  //dataZoom的结束百分比,此处暂定100%,根据实际效果调整.
	var _timeList = [];
	var _timePointValidityValue = 0; //时间点有效性校验，默认0
	var _backupSetIsLock = 0;  //备份集是否加密， 0：未加密或自动生成密码，1：加密切解密，2：加密未解锁/密码错误
	var _recoveryTargetData = [];
	var _timepointType = 1; //时间点类型，0：无效时间点，1：任意时间点；2：标签点；3：事件点；4：标签点和事件点
	var _eventNavTabsType = 1;  //时间点类型tab  1：任意时间点；2：标签点；3：事件点； 
	var myChart;
	var _hisPortletBodyWidth = 0;
	var _hisportletBodyHeight = 0;
	/**
	 * 绑定自动监听事件
	 */
	var initListener = function (){
		// 跳转复制页面
		$('#tobackup').on('click', function () {
			LOCATION('./content/volcdp/vol_cdp_backup.php', 'vol_cdp_copy');
		});

		$('#changeTimeInterval').on('change', changeTimeIntervalType); //时间轴上时间间隔类型
		$('#recoveryDataSourceSelect').on('change', storageObjectChange);

		$('#refreshEventInfo').unbind('click').click(refreshEventInfo);
		$('#getAllEventInfo').unbind('click').click(getAllEventInfo);
		$('#refreshLabelPointInfo').unbind('click').click(refreshLabelPointInfo);
		$('#getAllLabelPointInfo').unbind('click').click(getAllLabelPointInfo);
		$('#rebuildPartSwitch').on('switchChange.bootstrapSwitch', rebuildPartSwitchChnage);

		resetCheckTimepoint();  //重置选择的时间
		initTabShowEvent();  //绑定时间选项卡
		$('#timepointType li').unbind('click').click(getTabInfo);
//		$('#timePointSubmit').unbind('click').click(submitTimepointConf);
		//选择恢复方式
		$('#recovertype').on('change',changeRecoveryType);
		$('#addSpeedlimit').unbind('click').click(addSpeedlimitModal);
		//切换限速模式
		$('#speedModeType').on('change', speedModeHandler);
		//添加限速策略确定
		$('#speed_submit').on('click', speedSubmit);
		$('#recoveryThreadNum').on('input propertychange', function(){
			initHighStrategyDes();
		});
		$('.spinner-up').on('click', function(){
			initHighStrategyDes();
		});
		$('.spinner-down').on('click', function(){
			initHighStrategyDes();
		});
		$('#volcdpThreadNum').blur(verifyThreadNum);
		$('#inputRecoveryTimepoint').blur(function () {
			checkdate()
		});
		// 压缩传输
		$('#tran_compress_switch').on('switchChange.bootstrapSwitch', transferCompressChange);
	}
	// 显示压缩等级
	var transferCompressChange = function () {
		if (this.checked) {
			$('.transferCompressGradeDiv').show();
		} else {
			$('.transferCompressGradeDiv').hide();
		}
	};
	/**
	 * @function 获取所有节点信息
	 * @description:获取所有存储节点信息,选择节点加载指定节点关联的数据源客户端
	 */
		//TODO 需要删除
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
				loadRecoveryDataSourceHost();
			});
			$('#nodeselect').on('change', nodeselectChange);
		}
	//选择存储节点
	//TODO 需要删除
	var nodeselectChange = function (){
		var node_uuid = $('#nodeselect').val();
		_createTaskMsgdata.node_uuid = node_uuid;
		loadRecoveryDataSourceHost();
	};



	var tabSingleSelection = function (){
		tabSingleSelectionListener('recoveryDatasourceHostTable');
	}
	/**
	 * 获取可供接管的恢复数据源客户端
	 */
	var loadRecoveryDataSourceHost = function (){
		var node_uuid = $("#nodeselect").val();
		if(!dataSourceFlag){	//初始化表格
			var dataTableOpt = {
				'columnDefs': [{
					'orderable': false,
					'targets': [1,2,3]
				}],
				"order": [
					[1, "desc"]
				],
			};
			dataSourceGrid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPRECOVER,f:'getDataSourceHostInfo',p:{node_uuid:node_uuid}};
			dataSourceGrid.setAjaxParam(dataPar);
			dataSourceGrid.init({src: $("#recoveryDatasourceHostTable"), checkbox:false, dataTable:dataTableOpt, onDataLoad:tabSingleSelection});
			dataSourceFlag = true;

		}else{
			dataSourceGrid.getRefresh({node_uuid:node_uuid}, undefined, true);//刷新表格
		}
	}
	//TODO 需要删除
	//初始化存储下拉框
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
	//TODO 需要删除
	//选择目标存储
	var selectStorageChange = function (){
		var storageUuid =$("#selectstorage").val(); //目标存储uuid
		_createTaskMsgdata.storage_uuid = storageUuid;
	}

	/**
	 * @function 选择接管存储对象
	 * @description 选择接管数据的存储对象,当选择备机时默认选中最新时间点,且时间点不可配置,更新所选客户端所有卷的对应时间点;
	 */
	var storageObjectChange = function(){
		var dataSource = $("#recoveryDataSourceSelect").val();
		getClientAnyTimePointInfo();
		if(dataSource==2){  //数据来源于备机
			$('.timerange').html(_agentTimeRange);
			$(".recoveryTimepointDes").html(_latestTimePoint);
			loadHostVolInfoTab(_client_vol_info);
			$('.recoverytimecollapseview').addClass("collapsed");
			$(".recoverytimecollapseview").attr({'data-toggle':'','aria-expanded':'false'});
			$('#recovery_timepoint_view').removeClass("in");
		}else{
			$(".recoverytimecollapseview").attr({'data-toggle':'collapse','aria-expanded':'true'});
			$('.recoverytimecollapseview').removeClass("collapsed");
			$('#recovery_timepoint_view').addClass("in");
		}
	}

	/**
	 * @function 获取客户端任意时间点信息
	 * @description  通过选择的客户端uuid获取其对应的备份集
	 */
	var getClientAnyTimePointInfo = function (){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		var dataSource = $("#recoveryDataSourceSelect").val();
		var prams = JSON.stringify({
			agent_uuid:agent_uuid,
			node_uuid:_createTaskMsgdata.node_uuid,
			data_source:dataSource,
			task_type:CONF.TASK_TYPE.VOL_CDP_RECOVERY,
			task_uuid:_createTaskMsgdata.task_uuid
		});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPTAKEOVER,f:'getClientBackupSetInfo',p:prams}, function(d){
			var data = JSON.parse(d);
			_latestTimePoint = data[0].new_timestamp;
			_agentTimeRange = data[0].time_range;
			_timePointValidityValue = 1; //最新时间点可以确保是有效时间点 
			_timepointType = 1; //任意时间点
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

			if(_latestTimePoint.indexOf("1970") >= 0 ) {
				_timePointValidityValue = 0; //时间点无效
			}
			var verify_result = data[0].vol_set[0].verify_result;
			if(verify_result!=""){
				_timePointValidityValue = 2;   //时间点校验未通过
				$('#timePointValidity').css('color', "#F3565D");
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT_MESSAGE);
				$('#timePointValidity').attr("title",verify_result);
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CHECK_TIME, LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT+","+verify_result);
				_client_vol_info = [];
			}else{
				$('#inputRecoveryTimepoindes').html(LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME);
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
				$('#timePointValidity').css('color', "#45B6AF");

				$('.timerange').html(_agentTimeRange);
				$('.recoveryTimepointDes').html(_latestTimePoint);
				$('#inputRecoveryTimepoint').val(_latestTimePoint);
				_createTaskMsgdata.backup_time = _latestTimePoint;
				_createTaskMsgdata.storage_uuid = data[0].storage_uuid;
				_client_vol_info = data[0].vol_set;
			}
			_createTaskMsgdata.node_uuid = data[0].node_uuid;
			loadHostVolInfoTab(_client_vol_info); //装载可供恢复的卷信息 

		});
	}
	/**
	 * 加载已获取的主机卷信息,写入需要接管的tab
	 */
	var loadHostVolInfoTab = function(client_vol_info){
		var volumeInfo = client_vol_info;
		if(!gridInitFlag){	//初始化表格
			var dataTableOpt = {
				'showLoading':false,
				'columnDefs' : [{
					'orderable': false,
					'targets': [0]
				}],
				"pageLength": 25,
				"aLengthMenu": [25, 50, 100,200,500],
				"order": [],
			};
			volGrid = new Datatable();
			var dataPar = {m:CONF.M.VOLCDPRECOVER,f:'getDataSourceVolInfo',p:{volinfo:volumeInfo}};
			volGrid.setAjaxParam(dataPar);
			volGrid.init({src: $("#recoveryVolTable"),checkbox:true,dataTable:dataTableOpt,onDataLoad:tabClickSelection});
			gridInitFlag = true;
		}else{	//刷新表格
			volGrid.getRefresh({volinfo:volumeInfo}, undefined, true);
		}
		_recoveryVolDatatable =  $('#recoveryVolTable').DataTable();
	}
	/**
	 * 捕捉表格点击事件
	 */
	var tabClickSelection = function () {
		$("#recoveryVolTable td").click(function () {
			var tdSeq = $(this).parent().find("td").index($(this)[0]);  //获取当前点击的表格列
			if (tdSeq != 0) {  //用以处理限制某些列的选中，只允许第一列可以选中
				return false;
			}
		});
	}
	/**
	 * @function 获取恢复目标主机
	 */
	var getRecoveryTargetHost = function(){
		_recoveryTargetData = [];
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPRECOVER,f:'getRecoveryTargetHost',p:JSON.stringify({node_uuid:_createTaskMsgdata.node_uuid,master_uuid:_createTaskMsgdata.master_agent_uuid,master_os_type:_createTaskMsgdata.master_os_type,standby_uuid:""})}, function(d){
			var data = JSON.parse(d);
			if(!data.length) return;
			_recoveryTargetData = data;
			var hostSelect = $('#recoverTargetHost');
			hostSelect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid)
					.attr('data-agenttype', data[i].agent_type)
					.attr('data-ostype',data[i].os_type)
					.attr('data-network',data[i].net_model)
					.attr('title',data[i].title);
				hostSelect.append(option);
			}
		});
		$("#recoverTargetHost").unbind('change').bind('change',(recoveryTargetSelectChange));
	}
	/**
	 * 切换恢复目标机器，重置对应配置
	 */
	var resetRecoverTargetConf = function(){
		_re_target_vol_map_set = [];
		_createTaskMsgdata.recovery_target_agent_uuid = "";
		$('#rebuildPartSwitch').bootstrapSwitch('state', false);
	}
	//重新加载选定更新客户端的卷信息
	var reloadHostVolTargetInfo = function(standbyHostValue){
		_recoveryTargetData = [];
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPRECOVER,f:'getRecoveryTargetHost',
			p:JSON.stringify(
				{	node_uuid:_createTaskMsgdata.node_uuid,
					master_uuid:_createTaskMsgdata.master_agent_uuid,
					master_os_type:_createTaskMsgdata.master_os_type,
					standby_uuid:standbyHostValue
				}
			)}, function(d){
			var data = JSON.parse(d);
			if(!data.length) return;
			_recoveryTargetData = data;
			loadStandbyVolTargetInfo(standbyHostValue);
		});
	}

	//填充选择目标机的卷信息
	var loadStandbyVolTargetInfo = function(tgHostUuid){
		_createTaskMsgdata.recovery_os_type = $("#recoverTargetHost").find("option:selected").attr("data-ostype");
		_createTaskMsgdata.recover_target_host = $("#recoverTargetHost").find("option:selected").attr("data-agenttype");
		var netModel = $("#recoverTargetHost").find("option:selected").attr("data-network");  //传输网络
		networkFlag = false;
		if(netModel==2){
			networkFlag = true;
		}
		// loadHostVolInfoTab(_client_vol_info); //装载可供恢复的卷信息

		for(var i=0;i<_recoveryTargetData.length;i++){
			if(tgHostUuid ==_recoveryTargetData[i].uuid){
				var hostVol= _recoveryTargetData[i].vol_info;  //分区信息 
				var hostDisk = _recoveryTargetData[i].disk_info;  //磁盘信息
				var agentType = _recoveryTargetData[i].agent_type;
				var inTask = _recoveryTargetData[i].in_task;
				var taskName = _recoveryTargetData[i].task_name;
				if(!!inTask){  //判断目标节主机是否已配置作业
					UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_TARGET_HOST, LANG.UI_VOL_CDP_RECOVER_TARGET_HOST_MESSAGE2 +taskName+LANG.UI_VOL_CDP_RECOVER_TARGET_HOST_MESSAGE3);
					$("#recoverTargetHost").find("option").eq(0).prop("selected",true);
				}

				if(hostVol.length==0 && agentType!=2 &tgHostUuid!=""){
					UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TARGET, LANG.UI_VOL_CDP_BACKUP_CONFIGURE_MAP_VOL_MESSAGE);
				}else{
					_recoveryTargetHostVol = hostVol;
				}

				_recoveryTargetHostDisk = hostDisk;
				paddingRecoveryTargetVol();

				if(hostVol.length==0 && agentType==2){
					$('.diskgenView').show();
					_createTaskMsgdata.rebuild_partition_flag = 1;
					$('#rebuildPartSwitch').bootstrapSwitch('state', true);
				}

				_createTaskMsgdata.recovery_target_agent_uuid = tgHostUuid;
				if(agentType==2 && !inTask){  // 此处需要添加agentType的定义
					$('.diskgenView').show();
					$('.diskgenviewdiv').show();
				}
			}
		}
	}
	/**
	 * 选择接管目标机器
	 */
	var recoveryTargetSelectChange = function(){
		if(_recoveryTargetData.length<2){
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_TARGET_HOST, LANG.UI_VOL_CDP_RECOVER_TARGET_HOST_MESSAGE);
			return false;
		}
		resetRecoverTargetConf();
		for(var j=0;j<_recoveryTargetData.length;j++){
			$('#target_volume_'+j+' option').not('option:first').remove();
			$("#target_volume_"+j).unbind('change').bind('change',(recoverVolChange));
		}
		$('.diskgenView').hide(); //选择事情触发时先隐藏重建引导分区的配置
		$('.diskgenviewdiv').hide();
		_createTaskMsgdata.rebuild_partition_flag = 0; //默认不重建分区，数据恢复，过滤系统卷
		var tgHostUuid = $('#recoverTargetHost').val();
		var data = JSON.stringify({agent_uuid:tgHostUuid});
		if(tgHostUuid==0){
			loadStandbyVolTargetInfo();
		}else{
			Metronic.blockUI({target: "#tab2",animate: true});
			//更新客户端信息
			$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUP,f:'updateAgentInfo',p:data}, function(d){
				Metronic.unblockUI("#tab2");
				if(OPREL(d)){
					reloadHostVolTargetInfo(tgHostUuid);
				}
			});
		}
	}

	/**
	 * 根据的条件填充恢复目标目标卷
	 * 0. 数据恢复：对应select数据为volume信息且需要过滤系统分区
	 * 1. 系统恢复重建分区：对应select数据为disk信息
	 * 2. 系统恢复不重建分区:对应select数据为volume信息
	 */
	var paddingRecoveryTargetVol = function(){
		var hostVol = _recoveryTargetHostVol;
		var hostDisk = _recoveryTargetHostDisk;
		var recoverVolumeSelect = $("select[name='targetvol']");
		var diskgenFlag = _createTaskMsgdata.rebuild_partition_flag;
		for(var j=0;j<_client_vol_info.length;j++){
			$('#target_volume_'+j+' option').not('option:first').remove();
			$("#target_volume_"+j).unbind('change').bind('change',(recoverVolChange));
		}
		switch(diskgenFlag){
			case 0: //数据恢复：对应select数据为volume信息且需要过滤系统分区 
				for(var j=0;j<hostVol.length;j++){
					// if(hostVol[j].is_boot==1  && hostVol[j].agent_type !=2){
					// 	continue;
					// }
					var opText = hostVol[j].display_name+LANG.UI_VOL_CDP_RECOVER_TOTAL_CAPACITY+hostVol[j].capacity+")";
					if(!hostVol[j].can_select){
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-option','1')
							.attr('disabled',"disabled");
					}else{
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-option','1');
					}
					recoverVolumeSelect.append(option);
				}
				break;
			case 1: //系统恢复重建分区：对应select数据为disk信息
				for(var j=0;j<hostDisk.length;j++){
					var opText = hostDisk[j].display_name+LANG.UI_VOL_CDP_RECOVER_TOTAL_CAPACITY+hostDisk[j].capacity+")";
					var option = $("<option>").text(opText).val(hostDisk[j].uuid).attr('data-capacity', hostDisk[j].capacity_value).attr('data-option','1');
					recoverVolumeSelect.append(option);
				}
				break;
			case 2: //系统恢复不重建分区:对应select数据为volume信息
				for(var j=0;j<hostVol.length;j++){
					if(hostVol[j].is_boot==1 && hostVol[j].agent_type !=2){
						continue;
					}
					var opText = hostVol[j].display_name+LANG.UI_VOL_CDP_RECOVER_TOTAL_CAPACITY+hostVol[j].capacity+")";
					if(!hostVol[j].can_select){
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-option','1')
							.attr('disabled',"disabled");
					}else{
						var option = $("<option>").text(opText).val(hostVol[j].uuid)
							.attr('data-capacity', hostVol[j].capacity_value)
							.attr('data-option','1');
					}
					recoverVolumeSelect.append(option);
				}

				break;
		}
	}


	//重建系统引导分区选择函数
	var rebuildPartSwitchChnage = function (){
		if(this.checked && !_createTaskMsgdata.recovery_target_agent_uuid && _createTaskMsgdata.rebuild_partition_flag!=1){
			$('#rebuildPartSwitch').bootstrapSwitch('state', false);
			UIToastr.showWarning(LANG.UI_OS_PLUG_REBUILT_VOL, LANG.UI_VOL_CDP_RECOVER_REBUILT_VOL_TIPS);
			return false;
		}else{
			_re_target_vol_map_set =[];
			if($('#rebuildPartSwitch').is(':checked')) {
				_createTaskMsgdata.rebuild_partition_flag = 1;
			}else{
				_createTaskMsgdata.rebuild_partition_flag = 2;
			}
			paddingRecoveryTargetVol();
		}
	}

	var checkdate = function () {
		var reg = /^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/
		var str1 = $('#inputRecoveryTimepoint').val();
		if (!reg.test(str1)) {
			$('#inputRecoveryTimepoint').val('');
			_timepointType = 0;
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
		}
	}
	/**
	 * 校验任务线程数是否超出限制范围
	 */
	var verifyThreadNum = function(){
		var volcdpThreadNum =  $('#volcdpThreadNum').val();
		if(volcdpThreadNum>4){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE1);
			$("#volcdpThreadNum").val(1);
			return false;
		}
		if(volcdpThreadNum<1){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
			$("#volcdpThreadNum").val(1);
			return false;
		}
		initHighStrategyDes();
		return true;
	}
	/**
	 * 加载策略对应描述
	 */
	var initStrategyDes = function(){
		// initTimeStrategyDes();
		initSpeedStrategyDes();
		initHighStrategyDes();
	}
	//初始化时间策略描述
	var initTimeStrategyDes = function(){
		var des = "";
		//备份
		var recoverytype = $('#recovertype').val();
		var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();

		if(!strategyConfig.recInfo) return;
		if(recoverytype == 1){
			des += LANG.UI_VM_MANUAL_START;
		}else if(recoverytype == 2){
			des += strategyConfig.recInfo.des;
		}
		var strategyIndex = $('#strategySelect').val();
		if(strategyIndex && strategyIndex != "" && editFlag){
			var oldDes = globalStrategy[strategyIndex].time.des;
			initStrategyDesStyle($('.recoveryTimeDes'), des, oldDes);
		}else{
			$('.recoveryTimeDes').removeClass('font-green-seagreen');
		}
		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').prop('title', des);
	}

	//初始化时间策略
	var initStrategy = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.JOB, f:"getTimeCrowdList", p:{}}, function(d){
			var jsonData = JSON.parse(d);
			if(jsonData.timeList.length != 0){
				$('#backupCrowd').taskCrowd({timeList:jsonData.timeList, showFlag: jsonData.showFlag});
			}
			var suggestInfo = jsonData.suggestTime;
			var strategy = [];
			strategy[0] = {
				mode: 4,
				strategy_type: 2,
				days: [0, 0, 0, 0, 1, 0, 0],
				start_time: suggestInfo.start_time,
				roll_flag: false,
				roll_interval: '01:00:00',
				roll_end_time: suggestInfo.roll_end_time
			};
			//延迟设置,因为这里icheck会默认修改里面的选中事件
			setTimeout(function(){
				$('#recoveryTimestrategy').strategy({dom: $('#recoveryTimestrategy'), config: strategy, backup_flag: 2});
				$('.rollDiv').hide(); //隐藏滚动执行
			}, 2000);

		});

	}
	/**
	 * 生成限速策略UUID
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
	//初始化下对应控件下拉取值范围,默认值
	var initSpinner = function(){
		$('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
		$('#transferThreadNum').spinner({value:1, step: 1, min: 1, max: 4});

	}
	//初始化高级策略
	var initHighStrategyDes = function(){
		des = "";
		var volcdpThreadNum = $("#volcdpThreadNum").val();
		des +=LANG.UI_VOL_CDP_RECOVER_THREAD_NUMBERS + volcdpThreadNum
		$('.highDes').html(des);
		$('.highDes').prop('title', des);
	}
	/**
	 * 选择恢复启动方式
	 */
	var changeRecoveryType = function (){
		if('1' == this.value){
			$('#setstrategy').hide();
			$('.backupCrowd').hide();
		}else if("2" == this.value){
			$('#setstrategy').show();
			$('.backupCrowd').show();
		}
		initTimeStrategyDes();
	}

	/**
	 * @function Open限速策略模态窗口
	 */
	var addSpeedlimitModal = function(){
		$('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
		if(!initSpeedFlag){
			initSpeedTimeStrategy();
		}
	}
	//获取速度单位换算大小
	var getSpeedUnit = function(){
		var type = parseInt($('#unit').val());
		var unit;
		switch(type){
			case 1:
				unit = 1024;
				break;
			case 2:
				unit = 1024 * 1024;
				break;
			case 3:
				unit = 1024 * 1024 * 1024;
				break;
		}
		return unit;
	}
	//初始化限速策略时间控件
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
	//限速策略title描述
	var initSpeedStrategyDes = function(){
		var titleDes = "";
		var des = "";
		if(speedList.length !=0){
			des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
		}
		for(var i=0;i<speedList.length;i++){
			titleDes += speedList[i].des + '. \n';
		}
		$('.speedlimitDes').html(des);
		$('.speedlimitDes').prop('title', titleDes);
	}

	var checkSimpleForever = function(mode){
		for(var i=0;i<speedList.length;i++){
			if(mode == speedList[i].mode){
				return false;
			}
		}
		return true;
	}
	//切换限速模式
	var speedModeHandler = function(){
		if(this.value == 2){
			$('.setSpeedStrategy').hide();
		}else{
			$('.setSpeedStrategy').show();
		}
	}
	//添加限速策略
	var speedSubmit = function(){
		var info = {};
		var des = '';
		info.mode = $('#speedModeType').val();
		var speedUnit = getSpeedUnit();
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
			initSpeedStrategyDes();
		});
		speedList.push(info);
		$('#speedlimitModal').modal('hide');
		initSpeedStrategyDes();
	}

	//初始化时间控件
	var loadDatatimePicker = function(){
		$(".recoverytimepointview").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		});
		$('#resetRecoveryTimepoint').on('click',function(){
			$('#inputRecoveryTimepoint').val('');
			_timepointType = 0;
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
			return false
		});
	}
	/**
	 * ========================================================================================================
	 * ===============================================时间轴相关函数================================================
	 */
		//获取需要加载的时间间隔类型
	var changeTimeIntervalType = function(){
			var timeIntervalType = $("#changeTimeInterval").val();
			$('.selectrecoverytimeview').hide();
			if(timeIntervalType==7){
				$('.selectrecoverytimeview').show();
				$('#selectRecoveryStartTime').val('');
				$('#selectRecoveryEndTime').val('');
				loadIntervalTimePicker(); //自定义区间时间控件
				$('#resetSelectTimePoint').unbind('click').click(function(){
					$('#selectRecoveryStartTime').val('');
					$('#selectRecoveryEndTime').val('');
				});
				$('#confirmSelectTimePoint').unbind('click').click(getInputEndTimeValidity);
				var time_data = [];
				_chartStartPercent = 0;
				loadTimelineChart(time_data);
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
		var startTime = $("#selectRecoveryStartTime").val();
		var endTime =$('#selectRecoveryEndTime').val();
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
		$(".recoverytimeview").datetimepicker({
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
		// 动态设置echart图高度和宽度以适应不同分辨率
		var portletBodyWidth = $('#tabPortletBody').width();
		var portletBodyHeight = $('#tabPortletBody').height();

		if(_hisPortletBodyWidth != 0){  //非首次加载，chart坐标取历史配置
			portletBodyWidth = _hisPortletBodyWidth;
			portletBodyHeight = _hisportletBodyHeight;
		}else{  //首次加载获取当前body尺寸，赋值历史配置
			portletBodyWidth = $('#tabPortletBody').width();
			portletBodyHeight = $('#tabPortletBody').height();
			_hisPortletBodyWidth = $('#tabPortletBody').width();
			_hisportletBodyHeight = $('#tabPortletBody').height();
		}

		$('#volCdpRecoveryTimeline').css({"width": portletBodyWidth + 'px', "height": (portletBodyHeight - 34) + 'px'});

		echarts.init(document.getElementById('volCdpRecoveryTimeline')).dispose(); //销毁chart
		var chartDom = document.getElementById('volCdpRecoveryTimeline');
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
				$('#inputRecoveryTimepoint').val(checkTime);
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
				verifyTimepointisValid(checkTime);
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
		var checkpoint = document.getElementById('inputRecoveryTimepoint');
		var eConsole = function(param){
			_timepointType = 0;
			if(param.value > 0) {
				checkpoint.innerText = param.value;
				if($.isPlainObject(param.data)){
					var markPointNumber = param.data.coord[3];
					var lablePointNumber = param.data.coord[2];
					var checkTime = param.data.coord[0];
					$('#inputRecoveryTimepoint').val(checkTime);
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
		//myChart.on('click',eConsole);
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
		var confTime = $('#inputRecoveryTimepoint').val();
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		_timeList = [];
		var confInfo = {};
		confInfo.confTime = confTime;
		confInfo.timeInterval = _timeInterval;
		_timeList.push(confInfo);
		$("#recoveryAnytimeLi").removeClass("active");
		$("#agentEventInfoLi").attr("class","active");
		$('#agentEventInfo').css('visibility','initial');

		$('#recoveryAnytime').hide();
		$('#recoveryTagPoint').hide();
		$('#agentEventInfo').show();

		$('#getAllEventInfo').show();
		$('#refreshEventInfo').hide();
		loadCheckAgentEventInfo(agent_uuid,refreshEvent=false);	//获取客户端对应的事件信息
	};

	//解析选中时间的标签点信息
	var parseLablePointInfo = function(){
		var confTime = $('#inputRecoveryTimepoint').val();
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		_timeList = [];
		var confInfo = {};
		confInfo.confTime = confTime;
		confInfo.timeInterval = _timeInterval;
		_timeList.push(confInfo);

		$("#recoveryAnytimeLi").removeClass("active");
		$("#recoveryTagPointLi").attr("class","active");
		$('#recoveryTagPoint').css('visibility','initial');

		$('#recoveryAnytime').hide();
		$('#recoveryTagPoint').show();
		$('#agentEventInfo').hide();

		$('#getAllLabelPointInfo').show();
		$('#refreshLabelPointInfo').hide();
		loadCheckVolTagPoint(agent_uuid,isRefreshLabel= false);	//获取客户端对应的标签点提前
	}
	/**
	 *@function 重置重建分区配置
	 */
	var resetRebuildPartSwitch = function (){
		$('.diskgenView').hide();
		$('.diskgenviewdiv').hide();
		$('#rebuildPartSwitch').bootstrapSwitch('state', false);
		_createTaskMsgdata.rebuild_partition_flag = 0;
	}
	/**
	 * @function 获取时间轴data
	 * @desc 通过选定数据源UUID获取客户端备份时间轴对应的数据，并加载和渲染时间轴chart
	 */
	var getAgentTimelineData = function(){
		var time_data = [];
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		Metronic.blockUI({target: '#recoveryAnytime',animate: true});
		$.post(CONF.AJAXPATH, {
			m:CONF.M.VOLCDPBACKUPSET,
			f:'getAgentBkTimelineData',
			p:JSON.stringify({
				node_uuid:_createTaskMsgdata.node_uuid,
				startTime:$("#selectRecoveryStartTime").val() || '',
				endTime:$('#selectRecoveryEndTime').val() || '',
				agent_uuid:agent_uuid,
				time_interval:parseInt(_timeInterval),
				vol_uuid:"",
				task_type:CONF.TASK_TYPE.VOL_CDP_RECOVERY,
				task_uuid:_createTaskMsgdata.task_uuid
			})
		}, function(d){
			Metronic.unblockUI('#recoveryAnytime');
			var data = JSON.parse(d);
			var dataLength = data['time_data'].length;
			_chartStartPercent = dataZoomUnit(dataLength);
			if(dataLength>0){
				time_data = data['time_data'];
				loadTimelineChart(time_data);
			}else{
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA, LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA_MESSAGE);

				loadTimelineChart(time_data);
			}
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

	var initTimeListeners = function(){
		$('#inputRecoveryTimepoint').on('change', function(){
			var restoreTimepoint = $('#inputRecoveryTimepoint').val();
			verifyTimepointisValid(restoreTimepoint);
		});

		$('#selectRecoveryStartTime').on('change',function(){
			var startTime = $('#selectRecoveryStartTime').val();
			if(startTime> _latestTimePoint){
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_TIME_FRAME_SELECT,LANG.UI_VOL_CDP_RECOVER_CUSTOM_START_TIME_MESSAGE);
				setTimeout(function(){
					$('#selectRecoveryStartTime').val("");  //时间控件赋值延后赋值，此处需要特殊处理
				},100);
				return;
			}
		});
	}
	/**
	 * @function 校验时间有效性
	 * @description 校验输入时间是否在有效的时间数据集范围内
	 * @return Boolean
	 */
	var verifyTimepointisValid = function(timepoint){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		var params = JSON.stringify({
			agent_uuid:agent_uuid,
			node_uuid:_createTaskMsgdata.node_uuid,
			timepoint:timepoint,
			vol_uuid:'',
			task_type:CONF.TASK_TYPE.VOL_CDP_RECOVERY,
			task_uuid:_createTaskMsgdata.task_uuid
		});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPBACKUPSET,f:'verifyTimepointisValid',p:params}, function(d){
			var data = JSON.parse(d);
			var verify_result = "";
			if(data.length==0){
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
				$('#timePointValidity').css('color', "#F3565D");
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
				//_timepointType = 0;
				return false;
			}
			getRecoveryTargetHost(); // 重新加载可供恢复的目标主机；
			clearTable(_recoveryVolDatatable,"recoveryVolTable");  //移除已填充的卷信息

			var volInfo = data.time_vol_info;
			var encryptedFlag = data.encrypted_flag;
			var passwordAutoFlag = data.password_auto_flag;
			var timePointuuid =  data.timepoint_uuid;
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

		$('.recoveryTimepointDes').html (timepoint);
		$("#inputRecoveryTimepoint").val(timepoint);
		_createTaskMsgdata.takeover_timestamp = timepoint;
	}

	/**
	 * 填充可供恢复的卷
	 */
	var loadRestoreVol = function(data){
		if(data.length>0){
			_createTaskMsgdata.node_uuid = data[0].node_uuid;
			_createTaskMsgdata.storage_uuid = data[0].storage_uuid;
			_timePointValidityValue = 1;
			verify_result = data[0].verify_result;
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_EFFECTIVE_TIME);
			$('#timePointValidity').css('color', "#45B6AF");
			if(_timepointType!=2 &&_timepointType!=3 && _timepointType!=4 && _timepointType!=0){
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
			$('#timePointValidity').css('color', "#F3565D");
			$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT_MESSAGE);
			$('#timePointValidity').attr("title",verify_result);
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CHECK_TIME,  LANG.UI_VOL_CDP_RECOVER_TIME_LIMIT+","+verify_result);
			_client_vol_info = [];
			_timepointType = 0;
		}else{
			_client_vol_info = data;
			if(_timepointType!=2 && _timepointType!=3 && _timepointType!=4 && _timepointType!=0){
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
		backupSetLockStyle(); //备份集锁定样式
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
				loadRestoreVol(volInfo);  //填充可供恢复的卷
			}
		}else{
			_backupSetIsLock = 0;  //未加密备份点或自动生成密码备份点，无需解密
			$('.backupsetlockdiv').hide();
			backupSetUnlockStyle();   //解锁样式
			loadRestoreVol(volInfo);  //填充可供恢复的卷
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
				timePointDesc = LANG.UI_VOL_CDP_RECOVER_ANY_POINT_TIME;
				$('#inputRecoveryTimepoindes').html(timePointDesc);
				break;
			case 2:
				timePointDesc = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT;
				title = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT_TITLE;
				$('#inputRecoveryTimepoindes').html('<a id ="lablePointDetail" title = "'+title+'">'+timePointDesc+'</a>');
				$('#lablePointDetail').click(parseLablePointInfo);
				break;
			case 3:
				timePointDesc = LANG.UI_VOL_CDP_RECOVER_MARK_POINT;
				title = LANG.UI_VOL_CDP_RECOVER_TIME_POINT_TITLE;
				$('#inputRecoveryTimepoindes').html('<a id ="markPointDetail" title = "'+title+'">'+timePointDesc+'</a>');
				$('#markPointDetail').unbind('click').click(parseMarkPointInfo);
				break;
			case 4:
				var lablePointDes = LANG.UI_VOL_CDP_RECOVER_LABEL_POINT;
				var markPointDes = LANG.UI_VOL_CDP_RECOVER_MARK_POINT;
				title = LANG.UI_VOL_CDP_RECOVER_MARK_POINT_TITLE;
				$('#inputRecoveryTimepoindes').html('<a id ="lablePointDetail" title = "'+title+'">'+lablePointDes+'</a>&nbsp;&nbsp;\
	        			<a id ="markPointDetail" title = "'+title+'">'+markPointDes+'</a>');
				$('#markPointDetail').unbind('click').click(parseMarkPointInfo);
				$('#lablePointDetail').unbind('click').click(parseLablePointInfo);
				break;
			default:
				$('#inputRecoveryTimepoindes').html(timePointDesc);
				$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
				$('#timePointValidity').css('color', "#F3565D");
				_timepointType = 0;
				break;
		}
	}
	//重置选择的时间
	var resetCheckTimepoint = function(){
		$('#resetTimepoint').unbind('click').on('click',function(){
			$('#inputRecoveryTimepoint').val('');
		});
	}
	var tabTagSingleSelection = function(){
		tabSingleSelectionListener('recoveryTagpointTable');
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
			var dataPar = {
				m:CONF.M.VOLCDPRECOVER,
				f:'getVolTagPointInfo',
				p:{
					host_uuid:host_uuid,
					time_list:_timeList,
					task_uuid:_createTaskMsgdata.task_uuid
				}
			};
			tagGrid.setAjaxParam(dataPar);
			tagGrid.init({src: $("#recoveryTagpointTable"), checkbox:false, dataTable:dataTableOpt, onDataLoad:tabTagSingleSelection});
			tagGridFlag = true;
		}else{
			tagGrid.getRefresh({host_uuid:host_uuid,time_list:_timeList}, undefined, true);//刷新表格
			if(isRefreshLabel){
				UIToastr.showSuccess(LANG.UI_VOL_CDP_JOB_UPDATE_TAG_POING, LANG.UI_VOL_CDP_JOB_UPDATE_TAG_POING + LANG.UI_VOL_CDP_OPERATION_SUCCESS);
			}
		}
	}

	//table single select
	var tabEventSingleSelection = function(){
		tabSingleSelectionListener("agentEventInfoTable");
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
			var dataPar = {
				m:CONF.M.VOLCDPBACKUPSET,
				f:'getAgentEventInfo',
				p:{
					host_uuid:host_uuid,
					vol_uuid:"",
					time_list:_timeList,
					task_uuid:_createTaskMsgdata.task_uuid
				}
			};
			eventGrid.setAjaxParam(dataPar);
			eventGrid.init({src: $("#agentEventInfoTable"), checkbox:false, dataTable:dataOpt, onDataLoad:tabEventSingleSelection});
			eventGridFlag = true;
		}else{
			eventGrid.getRefresh({host_uuid:host_uuid,vol_uuid:"",time_list:_timeList}, undefined, true);  //刷新表格
			if(refreshEvent){
				UIToastr.showSuccess(LANG.UI_VOL_CDP_JOB_UPDATE_EVENT_INFO, LANG.UI_VOL_CDP_JOB_UPDATE_EVENT_INFO + LANG.UI_VOL_CDP_OPERATION_SUCCESS);
			}

		}
	}
	/**
	 * @function 实现表格checkbox 单选
	 */
	var tabSingleSelectionListener  = function (tableID){
		$('#'+tableID +' tbody').unbind('click').on('click', 'tr', function () {
			//1.取消所有选中项
			$("#"+tableID).parent().find('input').prop("checked", false);
			//2.选中本行
			$("#"+tableID).find('input').prop("checked", true);
			//得到这个点的备份信息
			var value = $(this).find('input').val();
			var selectTaskuuid = $(this).find('input').attr("data-taskuuid");
			if(tableID=="recoveryDatasourceHostTable"){
				_createTaskMsgdata.master_agent_uuid = value;  //选中恢复数据源uuid
				_createTaskMsgdata.task_uuid = selectTaskuuid;
			}else if(tableID == 'recoveryTagpointTable'){
				_eventNavTabsType = 2;
				_timepointType = 2;  //标签点
				if(!value){  //避免无记录时加载空数据
					return;
				}
				verifyTimepointisValid(value);
			}else if(tableID == 'agentEventInfoTable'){
				_eventNavTabsType = 3;
				_timepointType = 3;  //事件点
				var eventTime = recombinationEventTime(value);
				verifyTimepointisValid(eventTime);
			}
		});

		$('#'+tableID).find('tbody > tr').find('input[type="checkbox"]').off().on('click', function(){
			//取消所有其他选中的
			var checkBoxs = $('#' + tableID).find('tbody > tr').find('input[type="checkbox"]');
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
	//绑定选项卡，设置切换隐藏和显示的View
	var initTabShowEvent = function(){
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var tab = e.target;
			if(tab.hash == "#recoveryAnytime"){
				$('#recoveryAnytime').show();
				$('#recoveryTagPoint').hide();
				$('#agentEventInfo').hide();
			}else if(tab.hash == "#recoveryTagPoint"){
				$('#recoveryAnytime').hide();
				$('#recoveryTagPoint').show();
				$('#agentEventInfo').hide();

				$('#refreshLabelPointInfo').show();
				$('#getAllLabelPointInfo').hide();
			}else if(tab.hash == "#agentEventInfo"){
				$('#recoveryAnytime').hide();
				$('#recoveryTagPoint').hide();
				$('#agentEventInfo').show();

				$('#refreshEventInfo').show();
				$('#getAllEventInfo').hide();
			}
		});
	}
	//刷新标签点信息
	var refreshLabelPointInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		var vol_uuid = _createTaskMsgdata.checkVoluuid;
		loadCheckVolTagPoint(agent_uuid,isRefreshLabel = true);
		$('#inputRecoveryTimepoint').val("");
		_timepointType=0;
		$('.backupsetlockdiv').hide();
		$('#timePointValidity').css('color', "#F3565D");
		$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);

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
	//刷新客户端事件信息
	var refreshEventInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		loadCheckAgentEventInfo(agent_uuid,refreshEvent = true);
		$('#inputRecoveryTimepoint').val("");
		_timepointType=0;
		$('.backupsetlockdiv').hide();
		$('#timePointValidity').css('color', "#F3565D");
		$('#timePointValidity').html(LANG.UI_VOL_CDP_RECOVER_INVALID_TIME);
	}
	//图表上点击事件点
	var chartCheckEventInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		loadCheckAgentEventInfo(agent_uuid,refreshEvent = false);
	}

	//从过滤掉的事件信息切换到所有事件信息列表
	var getAllEventInfo = function(){
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		_timeList = [];
		loadCheckAgentEventInfo(agent_uuid,refreshEvent=false);
	}
	//点击nav触发事件
	var getTabInfo = function() {
		var attrId = $(this).attr("id")
		var agent_uuid = _createTaskMsgdata.master_agent_uuid;
		switch(attrId){
			case "recoveryTagPointLi":
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
	 /**
	 * @function 提交选择的时间点
	 * @description 获取当前选中的时间类型,判断配置时间有效性,提交配置信息并修改选择卷时间信息;
	 */
	var submitTimepointConf = function (){
		if(_timePointValidityValue!=CONF.FLAG.SET){
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TIME_MESSAGE);
			return false;
		}
		var timepointType = 0;
		for (var i = 0; i < 5; i++) {
			if ($("#timepointType").children('li')[i].className == "active") {
				timepointType = i+1;
				break;
			}
		}
		if(timepointType==1){ //任意时间点
			timepointInfo = $("#inputRecoveryTimepoint").val();
		}else{ //标签点
			var tagTable = $('#recoverTagpointTable').dataTable();
			var selectNodes = tagGrid.getSelectedRows();
			if(selectNodes.length==0){
				UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_RECOVER_TIPS_MESSAGE);
				return false;
			}
			var timepointInfo = selectNodes[0];
		}
		$('#selectBackupPoint').modal('hide');
	}

	/*** 表格的配置***/
	var gettableDefaultsOpt = function(){
		var defaultsOpt = {
			"searching": false,
			"ordering": false,
			"paging":false,
			"info":false,
			"bAutoWidth":false,
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
	/**
	 * @function 恢复目标卷change事件
	 * @description 为选中的恢复数据卷配置恢复目标映射关系，需要监测当前选中的分区是否已经配置映射关系
	 * @return volid
	 */
	var recoverVolChange = function(){
		var isSwitch = _createTaskMsgdata.rebuild_partition_flag
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
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TARGET, LANG.UI_VOL_CDP_RECOVER_TARGET_VOL_SIZE_TIPS);
				// UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_DISK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL_MESSAGE);
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
				UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TARGET, LANG.UI_VOL_CDP_RECOVER_TARGET_VOL_SIZE_TIPS);
				// UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL_MESSAGE);
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
						UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TARGET, LANG.UI_VOL_CDP_RECOVER_TARGET_VOL_SIZE_TIPS);
						// UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_VOL_MESSAGE2);
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
							_backupSetIsLock = 1;
							backupSetUnlockStyle();  //备份集解锁定样式
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
	 * @description 校验step1数据合法性，封装备份数据源
	 * @return true or false
	 */
	var step1Valid = function(){
		_createTaskMsgdata.recover_target_host = 1; //NORMAL
		_timePointValidityValue = 0; //默认时间点有效，后根据状态赋值

		allpointlist = []; //初始化时间集UUID
		var selectNodes = dataSourceGrid.getSelectedRows();
		if(selectNodes.length==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_RECOVER_DATA_SOURCE_SELECT);
			return false;
		}
		preLoadStep2Show();
	}
	/**
	 * @function 预加载加载step2
	 * @description 通过step1获取的数据加载step2需要显示的内容
	 */
	var preLoadStep2Show = function (){
		var agentUuid = _createTaskMsgdata.master_agent_uuid;
		var dataSourceInfo = dataSourceGrid.getDataTable().data();
		var dataSourceStr = "";
		var osType = "";
		for(var i=0;i<dataSourceInfo.length;i++){
			if(agentUuid == dataSourceInfo[i][5]){  //客户端uuid
				dataSourceStr+=dataSourceInfo[i][1]+"("+dataSourceInfo[i][2]+")";
				osType = dataSourceInfo[i][7];
			}
			_createTaskMsgdata.data_source_str = dataSourceStr;  //选中的数据源主机主机名及IP信息
			_createTaskMsgdata.master_os_type = osType;  //选择数据源主机系统类型
		}
		_timeList = [];
		getClientAnyTimePointInfo();  //获取选择客户端数据集
		getRecoveryTargetHost();  //加载可供恢复的目标主机

		loadCheckVolTagPoint(agentUuid,isRefreshLabel = false);	//加载选定卷的标签点信息
		loadCheckAgentEventInfo(agentUuid,refreshEvent=false);  //获取选择客户端生产的事件信息
		loadDatatimePicker();

		initTimeListeners();
		getAgentTimelineData();  //加载选定客户端对应的备份数据
		resetRebuildPartSwitch(); //重置重建分区开关
		backupSetLockStyle(); //重置备份集锁定样式
	}
	/**
	 * @function 创建恢复任务step
	 * @description 校验step2输入配置项是否合法，封装配置信息.
	 * @return true or false
	 */
	var step2Valid = function(){
		if(_timepointType==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_RECOVER_CONFIGURE_TAKEOVER_TIME_MESSAGE);
			return false;
		}
		if(_backupSetIsLock==2){
			var unLock= unlockBackupTimepoint();
			return false;
		}
		var recoverTargetHost = $('#recoverTargetHost').val();
		if(recoverTargetHost==0 && _backupSetIsLock==0){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_RECOVER_TARGET_SELECT);
			return false;
		}
		_createTaskMsgdata.recovery_target_agent_uuid = $('#recoverTargetHost').val();
		var recovery_object = {};
		var relation_set = [];
		var tab = $('#recoveryVolTable');
		var table = tab.dataTable();
		var rows = table.fnGetNodes();
		_recoverDataConfList = [];
		var isChkVol = false;
		var chekVolList = [];

		//暂时保留冗余的处理方式，因为不确定后期是否允许一个恢复任务恢复不同时间点的卷
		for (var i = 0; i < rows.length; i++) {
			var dataSourceList = {};
			var recover_obj = {};
			var row = table.fnGetData(rows[i]);
			isChkVol=$("#recovery_vol_id_"+i).is(':checked');
			if(!isChkVol){
				continue;
			}
			chekVolList.push(row);
			var selectId = "target_volume_"+i;
			var selectText = $("#"+selectId).find("option:selected").text();
			var selectOption = $("#"+selectId).find("option:selected").attr("data-option");  //主机对应卷uuid
			if(selectOption!=0){ //已选择恢复目标卷
				var host_uuid = $("#"+selectId).find("option").attr("data-hostvoluuid");  //主机对应卷uuid
				var target_uuid = $("#"+selectId).val();
				var target_disk_uuid = '';
				var target_vol_uuid = target_uuid;

				if(_createTaskMsgdata.rebuild_partition_flag==1){ //重建分区，select value 为disk_uuid，对应vol
					target_disk_uuid = target_uuid;
					target_vol_uuid = "";
				}
				var backup_time = $("#"+selectId).find("option").attr("data-time");//恢复卷配置的恢复时间点

				recover_obj.vol_uuid = host_uuid;
				recover_obj.target_vol_uuid = target_vol_uuid;
				recover_obj.target_disk_uuid = target_disk_uuid
				recover_obj.backup_time = backup_time;
				relation_set.push(recover_obj);

				dataSourceList.vol_name = row[7];
				dataSourceList.vol_size = row[2];
				dataSourceList.time_point = row[5];
				dataSourceList.target_vol = selectText;
				_recoverDataConfList.push(dataSourceList);
			}
		}

		if(chekVolList.length==0 ){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_RECOVER_CHOOSE_RECOVERY_MESSAGE);
			return false;
		}
		if(relation_set.length==0 ){
			UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS, LANG.UI_VOL_CDP_RECOVER_TARGET_NO_NULL);
			return false;
		}
		recovery_object.relation_set = relation_set;
		_createTaskMsgdata.recovery_object = recovery_object;

		if($('#rebuildPartSwitch').is(':checked')) {
			_createTaskMsgdata.rebuild_partition_flag = 1;
		}else{
			_createTaskMsgdata.rebuild_partition_flag = 2;
		}
		_createTaskMsgdata.rescover_target = $('#recoverTargetHost').find("option:selected").text();
		preLoadStep3Show();
	}

	/**
	 * @function 预加载加载step3
	 * @description 加载step3需要显示的内容
	 */
	var preLoadStep3Show = function (){
		initStrategyDes();
		getTaskName();
		if(networkFlag){
			$('.transfernetworkview').show();
			initNetworkList();
		}else{
			$('.transfernetworkview').hide();
			tranportStrategyConfInfo();
		}
		$("#transferNetwork").change(transferNetworkChange);
		$('#encrypttransfer').on('switchChange.bootstrapSwitch', encrypttransferChange);
		$('#tran_compress_switch').on('switchChange.bootstrapSwitch', tranCompressSwitchChange);
		$('#transfer_datapackage_size').change(transferdatapackageChange);
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.transferthreadview').hide();
		}else{
			$('.transferthreadview').show();
		}
	}
	/**
	 * 传输数据包大小
	 */
	var transferdatapackageChange = function(){
		tranportStrategyConfInfo();
	}
	/**
	 * 传输压缩切换
	 */
	var tranCompressSwitchChange = function(){
		tranportStrategyConfInfo();
	}
	/**
	 * 加密传输切换
	 */
	var encrypttransferChange = function (){
		if(this.checked){
			// $('.transfer-encrypt-method-form').show();
			$('.transfer-encrypt-method-form').hide();  //因bug #14005 暂时屏蔽该配置的显示
		}else{
			$('.transfer-encrypt-method-form').hide();
		}
		tranportStrategyConfInfo();
	}
	/**
	 * 传输网络切换
	 */
	var transferNetworkChange = function(){
		tranportStrategyConfInfo();
	}
	/*
	 * 传输策略配置信息
	 */
	var tranportStrategyConfInfo = function(){
		var transFerNework = $('#transferNetwork').find("option:selected").text();
		var encryptFlag = CONF.FLAG.UNSET;
		if($('#encrypttransfer').is(':checked')) {
			encryptFlag = CONF.FLAG.SET;
		}

		var compressFlag = CONF.FLAG.UNSET;
		if($('#tran_compress_switch').is(':checked')){
			compressFlag = CONF.FLAG.SET;
		}
		var blockSize = $("#transfer_datapackage_size").find("option:selected").text();;
		transportStrategyConfDesc(transFerNework,encryptFlag,compressFlag,blockSize);
	}

	/**
	 * @function 默认传输策略配置
	 */
	var transportStrategyConfDesc = function(transportNetworkName,encryptFlag,compressFlag,blockSize){
		var transportDefaultConf = "";
		if(networkFlag){
			transportDefaultConf = LANG.UI_VOL_CDP_RECOVER_TRANSPORT_NET+":"+transportNetworkName;
		}
		var transportDefaultConf = LANG.UI_VOL_CDP_RECOVER_TRANSPORT_NET+":"+transportNetworkName;
		var encryptFlagDesc  = LANG.UI_PUBLIC_OFF;
		if(encryptFlag==CONF.FLAG.SET){
			encryptFlagDesc = LANG.UI_PUBLIC_ON;
		}
		var compressFlagDesc = LANG.UI_PUBLIC_OFF;
		if(compressFlag==CONF.FLAG.SET){
			compressFlagDesc = LANG.UI_PUBLIC_ON;
		}

		transportDefaultConf += " "+LANG.UI_COPY_BACK_ENCRYPT+":"+encryptFlagDesc;
		transportDefaultConf += " "+LANG.UI_VOL_CDP_RECOVER_COMPRESS_TRANS+":"+compressFlagDesc;
		transportDefaultConf += " "+LANG.UI_VOL_CDP_RECOVER_TRANS_PACKAGE_SIZE+":"+blockSize;
		$(".transfernetStrategyDes").html(transportDefaultConf);
	}
	//初始化时间控件
	var loadStartTimePicker = function(){
		$(".recoverStartTimeControl").datetimepicker({
			language:  'zh-CN',
			autoclose: true,
			isRTL: Metronic.isRTL(),
			todayBtn: true,
			format: "yyyy-MM-dd hh:ii:ss",
			pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			startDate: new Date()
		});

	}
	/**
	 * @function 创建恢复任务step
	 * @description 校验step3输入配置项是否合法，封装配置信息.
	 * @return true or false
	 */
	var step3Valid = function(){
		var selectNodes = dataSourceGrid.getSelectedRows();
		var dataSourceInfo = dataSourceGrid.getDataTable().data();
		var dataSourceStr = "";

		var verifyThread = verifyThreadNum();
		if(!verifyThread){
			return false;
		}
		for(var i=0;i<dataSourceInfo.length;i++){
			if(_createTaskMsgdata.master_agent_uuid == dataSourceInfo[i][5]){  //客户端uuid
				dataSourceStr+=dataSourceInfo[i][1]+"("+dataSourceInfo[i][2]+")"
				break;
			}
		}
		$('.datasourcehostshow').html(dataSourceStr);

		var recoverTargethost = $('#recoverTargetHost').find("option:selected").text();
		$('.recovertargethostshow').html(recoverTargethost);


		_createTaskMsgdata.timeInfo.type = $('#recovertype').val();
		if('1' == _createTaskMsgdata.timeInfo.type){	//得到立即恢复描述
			$(".cdprecoverymode").html(LANG.UI_VM_MANUAL_START);
		}else{
			var strategyConfig = $('#recoveryTimestrategy').getStrategyConfig();
			_createTaskMsgdata.timeInfo.strategy = strategyConfig.recInfo;
			$(".cdprecoverymode").html(strategyConfig.recInfo.des);
		}
		//重建分区开关
		if($('#rebuildPartSwitch').is(':checked')){
			var isChecked = 1;
		}else{
			var isChecked = 0;
		}
		var diskgenSwitch = getSwitchDes(isChecked);
		$('.diskgenviewshow').html(diskgenSwitch);

		recoverVolTab(); //恢复数据卷
		recoverVolTargetMap(); //恢复卷与目标卷对应关系

		_createTaskMsgdata.speedInfo = speedList;//封装限速策略
		//限速策略描述
		var speedlimitshow = $('.speedlimitstrategy');
		var speedlimitsStr = '';
		speedlimitsStr = $('.speedlimitDes').prop('title');
		if (speedlimitsStr == '') {
			speedlimitsStr = LANG.UI_PUBLIC_NOTHING;
		}
		speedlimitshow.html(speedlimitsStr);

		getTransferStr();
		if (!CONF.FUNCTIONS.includes('multithread')) {
			$('.threadNum-number-div').hide();
			_createTaskMsgdata.highInfo.threadnum = 1;//获取任务线程配置数量
			$('.threadnumdiv').hide();
		}else{
			var volcdpThreadNum =  $('#volcdpThreadNum').val();
			_createTaskMsgdata.highInfo.threadnum = volcdpThreadNum;//获取任务线程配置数量
			$('.threadnumdiv').show();
		}

		if(volcdpThreadNum<1 ||volcdpThreadNum >16){
			UIToastr.showWarning(LANG.UI_VOL_CDP_RECOVER_TASK_THREAD, LANG.UI_VOL_CDP_RECOVER_TASK_THREAD_MESSAGE);
			return false;
		}
		$('.threadNumshow').html(LANG.UI_VOL_CDP_RECOVER_TASK_CONFIGURE_EACH+volcdpThreadNum+LANG.UI_VOL_CDP_RECOVER_THREAD);

		return true;
	}
	/**
	 * @function 获取传输策略相关配置参数
	 */
	var getTransferStr = function(){
		_createTaskMsgdata.highInfo.transfer = {};
		_createTaskMsgdata.highInfo.transfer.network_uuid = $("#transferNetwork").val();
		if(networkFlag){
			$('.transferNetworkdesdiv').show();
			var transferNetwork = $("#transferNetwork").find("option:selected").text();
			$('.transferNetworkdes').html(transferNetwork);
		}else{
			$('.transferNetworkdesdiv').hide();
		}

		_createTaskMsgdata.highInfo.transfer.compress_flag = 0;
		_createTaskMsgdata.highInfo.transfer.encrypt_flag = 0;
		_createTaskMsgdata.highInfo.transfer.compress_method = 0;
		if($('#tran_compress_switch').is(':checked')){  //传输压缩
			_createTaskMsgdata.highInfo.transfer.compress_flag = CONF.FLAG.SET;
			_createTaskMsgdata.highInfo.transfer.compress_method = $('#transferCompressGrade').val();  //压缩等级
			var compressPriorityDes = $('#transferCompressGrade').find("option:selected").text();
			$('.compresstransferdes').html(LANG.UI_PUBLIC_ON);
			$('.compresspriority').show();
			$('.compressprioritydes').html(compressPriorityDes);
		}else{
			_createTaskMsgdata.highInfo.transfer.compress_flag = CONF.FLAG.UNSET;
			$('.compresstransferdes').html(LANG.UI_PUBLIC_OFF);
			$('.compresspriority').hide();
		}
		if($('#encrypttransfer').is(':checked')){  //传输加密;
			_createTaskMsgdata.highInfo.transfer.encrypt_flag = CONF.FLAG.SET;
			_createTaskMsgdata.highInfo.transfer.encrypt_method = parseInt($('#transferEncryptMethod option:first').val());
			var transferEncryptMethod = $('#transferEncryptMethod').find("option:selected").text();
			var encrypttransDes = LANG.UI_PUBLIC_ON;

			$('.encrypttransferdes').html(LANG.UI_PUBLIC_ON);

			// $('.transferencryptmethod').show();
			// $('.transferencryptmethoddes').html(transferEncryptMethod);
		}else{
			_createTaskMsgdata.highInfo.transfer.encrypt_flag = CONF.FLAG.UNSET;
			$('.encrypttransferdes').html(LANG.UI_PUBLIC_OFF);
			$('.transferencryptmethod').hide();
		}

		var datapackageSize = $('#transfer_datapackage_size').val(); //传输数据包大小
		var datapackageSizeText = $('#transfer_datapackage_size').find("option:selected").text();

		$('.transferdatapackagesizedes').html(datapackageSizeText);
		var reconnectTime = $('#reconnect_time').val();
		var reconnectInterval = $('#reconnect_interval').val();

		$('.recoveryreconnecttimesdes').html(reconnectTime);
		$('.recoveryreconnectintervaldes').html(reconnectInterval);

		var transportBlockSize = datapackageSize * 1024*1024;
		_createTaskMsgdata.transport_block_size = transportBlockSize; //传输数据包大小
		_createTaskMsgdata.highInfo.transfer.block_size = transportBlockSize; //传输数据包大小
		_createTaskMsgdata.highInfo.transfer.reconnect_times =  reconnectTime;	//重连次数
		_createTaskMsgdata.highInfo.transfer.reconnect_interval = reconnectInterval;  //重连间隔
	}
	/**
	 * 获取配置的恢复数据列表,填充表格
	 */
	var recoverVolTab = function (){

		if(_recoverVolDataSourceTab!=undefined){
			clearTable(_recoverVolDataSourceTab,"recoverDataTable");
		}else{
			_recoverVolDataSourceTab =  $('#recoverDataTable').DataTable(gettableDefaultsOpt());
		}
		var recoverVolList = _recoverDataConfList;
		for(var i=0;i<recoverVolList.length;i++){
			_recoverVolDataSourceTab.row.add([
				recoverVolList[i].vol_name,
				recoverVolList[i].time_point
			]).draw();
		}
	}
	/**
	 * 获取配置的恢复数据与目标卷对应关系列表,填充表格
	 */
	var recoverVolTargetMap = function(){
		if(_recoverVolDataTargetTab!=undefined){
			clearTable(_recoverVolDataTargetTab,"recoverTargetConfTable");
		}else{
			_recoverVolDataTargetTab =  $('#recoverTargetConfTable').DataTable(gettableDefaultsOpt());
		}
		var targetVolList = _recoverDataConfList;
		for(var i=0;i<targetVolList.length;i++){
			_recoverVolDataTargetTab.row.add([
				targetVolList[i].vol_name+"(" +LANG.UI_VOL_CDP_BACKUP_ALL_CAPACITY +targetVolList[i].vol_size+")",
				targetVolList[i].target_vol
			]).draw();
		}
	}
	//得到任务名,这里需要注意的是在跨虚拟化平台恢复的时候需要用目的地的名字,
	//这里是需要在选择目的地节点后再初始化.
	var getTaskName = function(){
		var info = {};
		info.task_name = LANG.UI_VOL_CDP_RECOVERY_DEFAULT_TASK_NAME;
		info = JSON.stringify(info);
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPRECOVER,f:'getVolCdpRecoverTaskName',p:info}, function(d){
			$('#volCdpRecoverName').val(d);
		});
	}
	/**
	 * @function 得到开关的结果描述
	 * @return 开启/关闭
	 */
	var getSwitchDes = function(check){
		if(check){
			return LANG.UI_PUBLIC_ON;
		}
		return LANG.UI_PUBLIC_OFF;
	}
	/**
	 * @function 提交创建任务请求
	 */
	var submit = function(){
		if('' == $.trim($("#volCdpRecoverName").val())){
			$('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
			return;
		}
		$('.jobnametip').hide();
        let jobName = $.trim($("#volCdpRecoverName").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
		$(".button-submit").addClass('disabled');  //添加button禁用样式
		$(".button-submit").css("pointer-events", "none");  //添加button不可点击样式

		_createTaskMsgdata.taskName = $.trim($("#volCdpRecoverName").val());
		_createTaskMsgdata.restore_data_source = 1;  //恢复任务暂时不支持从备机获取数据源，批注时间：2022-8-10 17:18:21
		if($('#strategySelect option:selected').val()){
			_createTaskMsgdata.strategygroupuuid = $('#strategySelect option:selected').val();
		}else{
			_createTaskMsgdata.strategygroupuuid = "";
		}
		_createTaskMsgdata.speedLimit = speedSubmitInfo();
		var jsonData = JSON.stringify(_createTaskMsgdata);
		Metronic.blockUI({target: '#volcdprecovercontent',animate: true,cenrerY: true,});
		$.post(CONF.AJAXPATH, {m:CONF.M.VOLCDPRECOVER,f:'createRecoverJob',p:jsonData}, function(data){
			Metronic.unblockUI('#volcdprecovercontent');
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
			info['speed'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
		}
		return info;
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
			jQuery('li', $('#volcdprecovercontent')).removeClass("done");
			var li_list = navigation.find('li');
			for (var i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}

			if (current == 1) {
				$('#volcdprecovercontent').find('.button-previous').css('visibility', 'hidden');
			} else {
				$('#volcdprecovercontent').find('.button-previous').css('visibility', 'visible');
			}

			if (current >= total) {
				$('#volcdprecovercontent').find('.button-next').hide();
				$('#volcdprecovercontent').find('.button-submit').css('visibility', 'visible');
			} else {
				$('#volcdprecovercontent').find('.button-next').show();
				$('#volcdprecovercontent').find('.button-submit').css('visibility', 'hidden');
			}
			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		$('#volcdprecovercontent').bootstrapWizard({
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
				$('#volcdprecovercontent').find('.progress-bar').css({
					width: $percent + '%'
				});
			}
		});

		$('#volcdprecovercontent').find('.button-previous').css('visibility', 'hidden');
		$('#volcdprecovercontent .button-submit').click(submit).css('visibility', 'hidden');
	}
	//初始化节点传输网络列表
	var initNetworkList = function(){
		var data = {};
		data.nodeuuid = _createTaskMsgdata.node_uuid;
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
			var transferNetworkName = data[0].ip + ":" + data[0].port;
			transportStrategyConfDesc(transferNetworkName,encryptFlag=2,compressFlag=2,blockSize="4 MB");
		});
	}

	var watchEchartSizeChange = function() {
		window.onresize = function() {
			var portletBodyWidth = $('#tabPortletBody').width();
			var portletBodyHeight = $('#tabPortletBody').height();
			$('#volCdpRecoveryTimeline').css({"width": portletBodyWidth + 'px', "height": (portletBodyHeight - 34) + 'px'});
			myChart.resize();
		}
	}

	return {
		init: function () {
			wizardInit();
			//initLoadStroageNodeInfo();
			loadRecoveryDataSourceHost();
			initListener();
			initSpinner();
			// initStrategy();
			watchEchartSizeChange();
		}
	};
}();

jQuery(document).ready(function() {
	VolCDPRecover.init();
});